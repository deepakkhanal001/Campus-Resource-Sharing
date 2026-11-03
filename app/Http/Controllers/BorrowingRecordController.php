<?php

namespace App\Http\Controllers;

use App\Models\BorrowingRecord;
use App\Models\Notification;
use App\Models\Resource;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class BorrowingRecordController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));
        $status = (string) $request->query('status', '');

        $query = BorrowingRecord::with(['resource', 'requester', 'owner'])
            ->latest('id');

        if (! auth()->user()->hasRole('admin')) {
            $query->where(function ($builder) {
                $builder->where('requester_id', auth()->id())
                    ->orWhere('owner_id', auth()->id());
            });
        }

        $borrowingRecords = $query
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($builder) use ($search) {
                    $builder->whereHas('resource', fn ($resource) => $resource->where('title', 'like', "%{$search}%"))
                        ->orWhereHas('requester', fn ($requester) => $requester->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('owner', fn ($owner) => $owner->where('name', 'like', "%{$search}%"));
                });
            })
            ->when(in_array($status, ['pending', 'approved', 'picked_up', 'rejected', 'returned', 'cancelled'], true), fn ($query) => $query->where('status', $status))
            ->paginate(15)
            ->withQueryString();

        return view('borrowing-records.index', compact('borrowingRecords'));
    }

    public function show(BorrowingRecord $borrowingRecord): View
    {
        $this->authorizeParticipant($borrowingRecord);

        $borrowingRecord->load(['resource', 'requester', 'owner']);

        return view('borrowing-records.show', compact('borrowingRecord'));
    }

    public function approve(BorrowingRecord $borrowingRecord): RedirectResponse
    {
        DB::transaction(function () use ($borrowingRecord): void {
            $record = $this->lockedRecord($borrowingRecord);
            $this->authorizeOwner($record);
            $this->ensureStatus($record, 'pending', __('Only pending requests can be approved.'));

            $resource = Resource::whereKey($record->resource_id)->lockForUpdate()->first();
            abort_unless($resource, 404);

            if ($resource->availability_status !== 'available') {
                throw ValidationException::withMessages([
                    'action' => __('This resource is no longer available. Reject or cancel conflicting requests first.'),
                ]);
            }

            $record->update([
                'status' => 'approved',
                'rejection_reason' => null,
            ]);

            $resource->update(['availability_status' => 'borrowed']);

            $this->notifyRequester(
                $record,
                'Your request to borrow "'.$resource->title.'" was approved by '.auth()->user()->name.'.',
                'borrow_approved'
            );
        });

        return redirect()
            ->route('borrowing-records.show', $borrowingRecord)
            ->with('success', __('Request approved. The resource is now marked as borrowed.'));
    }

    public function reject(Request $request, BorrowingRecord $borrowingRecord): RedirectResponse
    {
        $validated = $request->validate([
            'remarks' => ['nullable', 'string', 'max:1000'],
        ]);

        $remarks = trim((string) ($validated['remarks'] ?? ''));

        DB::transaction(function () use ($borrowingRecord, $remarks): void {
            $record = $this->lockedRecord($borrowingRecord);
            $this->authorizeOwner($record);
            $this->ensureStatus($record, 'pending', __('Only pending requests can be rejected.'));

            $record->update([
                'status' => 'rejected',
                'rejection_reason' => $remarks !== '' ? $remarks : null,
            ]);

            $resourceTitle = $record->resource?->title ?? __('a resource');
            $message = 'Your request to borrow "'.$resourceTitle.'" was rejected by '.auth()->user()->name.'.';

            if ($remarks !== '') {
                $message .= ' Remarks: '.$remarks;
            }

            $this->notifyRequester($record, $message, 'borrow_rejected');
        });

        return redirect()
            ->route('borrowing-records.show', $borrowingRecord)
            ->with('success', __('Request rejected.'));
    }

    public function pickedUp(BorrowingRecord $borrowingRecord): RedirectResponse
    {
        DB::transaction(function () use ($borrowingRecord): void {
            $record = $this->lockedRecord($borrowingRecord);
            $this->authorizeOwner($record);
            $this->ensureStatus($record, 'approved', __('Only approved requests can be marked as taken.'));

            $resource = Resource::whereKey($record->resource_id)->lockForUpdate()->first();
            abort_unless($resource, 404);

            $record->update(['status' => 'picked_up']);

            if ($resource->availability_status !== 'borrowed') {
                $resource->update(['availability_status' => 'borrowed']);
            }

            $this->notifyRequester(
                $record,
                '"'.$resource->title.'" was marked as taken. Please return it by the agreed date.',
                'borrow_picked_up'
            );
        });

        return redirect()
            ->route('borrowing-records.show', $borrowingRecord)
            ->with('success', __('Request marked as taken.'));
    }

    public function returned(BorrowingRecord $borrowingRecord): RedirectResponse
    {
        DB::transaction(function () use ($borrowingRecord): void {
            $record = $this->lockedRecord($borrowingRecord);
            $this->authorizeOwner($record);
            $this->ensureStatus($record, 'picked_up', __('Only resources marked as taken can be returned.'));

            $resource = Resource::whereKey($record->resource_id)->lockForUpdate()->first();
            abort_unless($resource, 404);

            $record->update(['status' => 'returned']);

            $hasOtherActiveLoan = BorrowingRecord::where('resource_id', $record->resource_id)
                ->whereKeyNot($record->getKey())
                ->whereIn('status', ['approved', 'picked_up'])
                ->whereNull('deleted_at')
                ->exists();

            if (! $hasOtherActiveLoan && $resource->availability_status === 'borrowed') {
                $resource->update(['availability_status' => 'available']);
            }

            $this->notifyRequester(
                $record,
                '"'.$resource->title.'" was marked as returned. Thank you for returning it.',
                'borrow_returned'
            );
        });

        return redirect()
            ->route('borrowing-records.show', $borrowingRecord)
            ->with('success', __('Resource marked as returned and available again.'));
    }

    private function lockedRecord(BorrowingRecord $borrowingRecord): BorrowingRecord
    {
        return BorrowingRecord::whereKey($borrowingRecord->getKey())
            ->lockForUpdate()
            ->firstOrFail();
    }

    private function ensureStatus(BorrowingRecord $borrowingRecord, string $status, string $message): void
    {
        if ($borrowingRecord->status !== $status) {
            throw ValidationException::withMessages(['action' => $message]);
        }
    }

    private function notifyRequester(BorrowingRecord $borrowingRecord, string $message, string $type): void
    {
        Notification::create([
            'user_id' => $borrowingRecord->requester_id,
            'message' => $message,
            'type' => $type,
            'is_read' => false,
        ]);
    }

    private function authorizeOwner(BorrowingRecord $borrowingRecord): void
    {
        abort_unless(
            (int) $borrowingRecord->owner_id === (int) auth()->id()
                && (int) $borrowingRecord->requester_id !== (int) auth()->id(),
            403
        );
    }

    private function authorizeParticipant(BorrowingRecord $borrowingRecord): void
    {
        abort_unless(
            auth()->user()->hasRole('admin')
                || $borrowingRecord->requester_id === auth()->id()
                || $borrowingRecord->owner_id === auth()->id(),
            403
        );
    }
}
