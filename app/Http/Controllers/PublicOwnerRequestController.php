<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PublicOwnerRequestController extends PublicPortalController
{
    private const STATUSES = ['pending', 'approved', 'picked_up', 'returned', 'rejected', 'cancelled'];

    public function index(Request $request): View
    {
        $owner = $this->activeUserOrFail();
        $search = mb_substr(trim((string) $request->query('q', '')), 0, 100);
        $status = (string) $request->query('status', '');
        $status = in_array($status, self::STATUSES, true) ? $status : '';
        $resourceId = max(0, (int) $request->query('resource_id', 0));

        $where = ['br.owner_id = ?', 'r.owner_id = ?', 'br.deleted_at IS NULL'];
        $bindings = [(int) $owner->id, (int) $owner->id];

        if ($status !== '') {
            $where[] = 'br.status = ?';
            $bindings[] = $status;
        }

        if ($resourceId > 0) {
            $where[] = 'br.resource_id = ?';
            $bindings[] = $resourceId;
        }

        if ($search !== '') {
            $where[] = '(r.title LIKE ? OR requester.name LIKE ?)';
            $bindings[] = '%'.$search.'%';
            $bindings[] = '%'.$search.'%';
        }

        $from = ' FROM borrowing_records br
                  INNER JOIN resources r ON r.id = br.resource_id
                  INNER JOIN users requester ON requester.id = br.requester_id';
        $whereSql = implode(' AND ', $where);

        $total = (int) (DB::selectOne(
            'SELECT COUNT(*) AS total'.$from.' WHERE '.$whereSql,
            $bindings
        )->total ?? 0);

        $perPage = 12;
        $lastPage = max(1, (int) ceil($total / $perPage));
        $page = min(max(1, (int) $request->query('page', 1)), $lastPage);
        $offset = ($page - 1) * $perPage;

        $requests = DB::select(
            'SELECT
                br.id,
                br.status,
                br.request_message,
                br.rejection_reason,
                br.pickup_date,
                br.return_date,
                br.created_at,
                br.updated_at,
                r.id AS resource_id,
                r.title AS resource_title,
                r.type AS resource_type,
                r.availability_status,
                r.deleted_at AS resource_deleted_at,
                requester.id AS requester_id,
                requester.name AS requester_name
             '.$from.' WHERE '.$whereSql.'
             ORDER BY br.created_at DESC, br.id DESC
             LIMIT '.$perPage.' OFFSET '.$offset,
            $bindings
        );

        $countRows = DB::select(
            'SELECT br.status, COUNT(*) AS total
             FROM borrowing_records br
             INNER JOIN resources r ON r.id = br.resource_id
             WHERE br.owner_id = ? AND r.owner_id = ? AND br.deleted_at IS NULL
             GROUP BY br.status',
            [(int) $owner->id, (int) $owner->id]
        );

        $counts = array_fill_keys(self::STATUSES, 0);

        foreach ($countRows as $row) {
            $counts[$row->status] = (int) $row->total;
        }

        $myResources = DB::select(
            'SELECT id, title
             FROM resources
             WHERE owner_id = ? AND deleted_at IS NULL
             ORDER BY title ASC',
            [(int) $owner->id]
        );

        return view('public.owner-requests.index', [
            'requests' => new LengthAwarePaginator(
                $requests,
                $total,
                $perPage,
                $page,
                [
                    'path' => route('public.owner-requests.index'),
                    'query' => $request->query(),
                ]
            ),
            'counts' => $counts,
            'myResources' => $myResources,
            'filters' => [
                'q' => $search,
                'status' => $status,
                'resource_id' => $resourceId,
            ],
            'hasFilters' => $search !== '' || $status !== '' || $resourceId > 0,
        ]);
    }

    public function update(Request $request, int $borrowing): RedirectResponse
    {
        $owner = $this->activeUserOrFail();

        $validated = $request->validate([
            'action' => ['required', 'in:approve,reject,picked_up,returned'],
            'remarks' => ['nullable', 'string', 'max:1000'],
        ]);

        $remarks = trim((string) ($validated['remarks'] ?? ''));

        $message = DB::transaction(function () use ($borrowing, $owner, $validated, $remarks): string {
            $record = DB::selectOne(
                'SELECT
                    br.id,
                    br.resource_id,
                    br.requester_id,
                    br.owner_id,
                    br.status,
                    r.owner_id AS current_owner_id,
                    r.title AS resource_title,
                    r.availability_status
                 FROM borrowing_records br
                 INNER JOIN resources r ON r.id = br.resource_id
                 WHERE br.id = ? AND br.deleted_at IS NULL AND r.deleted_at IS NULL
                 LIMIT 1'.$this->lockClause(),
                [$borrowing]
            );

            abort_unless($record, 404);

            abort_unless(
                (int) $record->owner_id === (int) $owner->id
                    && (int) $record->current_owner_id === (int) $owner->id,
                403
            );

            abort_if(
                (int) $record->requester_id === (int) $owner->id,
                403,
                'You cannot manage your own borrowing request.'
            );

            $action = $validated['action'];
            $now = now()->toDateTimeString();
            $notificationMessage = '';
            $notificationType = '';

            if ($action === 'approve') {
                if ($record->status !== 'pending') {
                    throw ValidationException::withMessages([
                        'action' => __('Only pending requests can be approved.'),
                    ]);
                }

                if ($record->availability_status !== 'available') {
                    throw ValidationException::withMessages([
                        'action' => __('This resource is no longer available. Reject or cancel conflicting requests first.'),
                    ]);
                }

                DB::update(
                    'UPDATE borrowing_records SET status = ?, updated_at = ? WHERE id = ?',
                    ['approved', $now, (int) $record->id]
                );

                DB::update(
                    'UPDATE resources SET availability_status = ?, updated_at = ? WHERE id = ?',
                    ['borrowed', $now, (int) $record->resource_id]
                );

                $notificationMessage = 'Your request to borrow "'.$record->resource_title.'" was approved by '.$owner->name.'.';
                $notificationType = 'borrow_approved';
            }

            if ($action === 'reject') {
                if ($record->status !== 'pending') {
                    throw ValidationException::withMessages([
                        'action' => __('Only pending requests can be rejected.'),
                    ]);
                }

                DB::update(
                    'UPDATE borrowing_records SET status = ?, rejection_reason = ?, updated_at = ? WHERE id = ?',
                    ['rejected', $remarks !== '' ? $remarks : null, $now, (int) $record->id]
                );

                $notificationMessage = 'Your request to borrow "'.$record->resource_title.'" was declined by '.$owner->name.'.';

                if ($remarks !== '') {
                    $notificationMessage .= ' Remarks: '.$remarks;
                }

                $notificationType = 'borrow_rejected';
            }

            if ($action === 'picked_up') {
                if ($record->status !== 'approved') {
                    throw ValidationException::withMessages([
                        'action' => __('Only approved requests can be marked as taken.'),
                    ]);
                }

                DB::update(
                    'UPDATE borrowing_records SET status = ?, updated_at = ? WHERE id = ?',
                    ['picked_up', $now, (int) $record->id]
                );

                $notificationMessage = '"'.$record->resource_title.'" was marked as taken. Please return it by the agreed date.';
                $notificationType = 'borrow_picked_up';
            }

            if ($action === 'returned') {
                if ($record->status !== 'picked_up') {
                    throw ValidationException::withMessages([
                        'action' => __('Only resources marked as taken can be returned.'),
                    ]);
                }

                DB::update(
                    'UPDATE borrowing_records SET status = ?, updated_at = ? WHERE id = ?',
                    ['returned', $now, (int) $record->id]
                );

                DB::update(
                    'UPDATE resources SET availability_status = ?, updated_at = ? WHERE id = ?',
                    ['available', $now, (int) $record->resource_id]
                );

                $notificationMessage = '"'.$record->resource_title.'" was marked as returned. Thank you for returning it.';
                $notificationType = 'borrow_returned';
            }

            if ($notificationMessage !== '') {
                $this->createNotification((int) $record->requester_id, $notificationMessage, $notificationType);
            }

            return match ($action) {
                'approve' => __('Request approved. The resource is now marked as borrowed.'),
                'reject' => __('Request rejected.'),
                'picked_up' => __('Request marked as taken.'),
                'returned' => __('Resource marked as returned and available again.'),
            };
        });

        return redirect()
            ->route('public.owner-requests.index')
            ->with('success', $message);
    }
}
