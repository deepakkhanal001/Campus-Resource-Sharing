<?php

namespace App\Http\Controllers;

use App\Models\DigitalFile;
use App\Models\Resource;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DigitalFileController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));
        $query = DigitalFile::with('resource')->latest('id');

        if (! auth()->user()->hasRole('admin')) {
            $query->whereHas('resource', function ($builder) {
                $builder->where('owner_id', auth()->id());
            });
        }

        $digitalFiles = $query
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($builder) use ($search) {
                    $builder->where('file_name', 'like', "%{$search}%")
                        ->orWhereHas('resource', fn ($resource) => $resource->where('title', 'like', "%{$search}%"));
                });
            })
            ->paginate(15)
            ->withQueryString();

        return view('digital-files.index', compact('digitalFiles'));
    }

    public function create(Request $request): View
    {
        return view('digital-files.create', [
            'digitalFile' => new DigitalFile,
            'resources' => $this->authorizedResources(),
            'selectedResourceId' => $request->integer('resource_id'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'resource_id' => ['required', 'exists:resources,id'],
            'file' => ['required', 'file', 'max:20480'],
        ]);

        $resource = Resource::findOrFail($validated['resource_id']);
        $this->authorizeResource($resource);

        $uploadedFile = $request->file('file');
        $path = $uploadedFile->store('digital-files', 'local');

        DigitalFile::create([
            'resource_id' => $resource->id,
            'file_path' => $path,
            'file_name' => $uploadedFile->getClientOriginalName(),
            'file_type' => $uploadedFile->getClientMimeType(),
            'size' => $uploadedFile->getSize(),
        ]);

        return redirect()
            ->route('resources.show', $resource)
            ->with('success', 'Digital file uploaded successfully.');
    }

    public function show(DigitalFile $digitalFile): View
    {
        $this->authorizeDigitalFile($digitalFile);

        $digitalFile->load('resource');

        return view('digital-files.show', compact('digitalFile'));
    }

    public function edit(DigitalFile $digitalFile): View
    {
        $this->authorizeDigitalFile($digitalFile);

        return view('digital-files.edit', [
            'digitalFile' => $digitalFile,
            'resources' => $this->authorizedResources(),
            'selectedResourceId' => $digitalFile->resource_id,
        ]);
    }

    public function update(Request $request, DigitalFile $digitalFile): RedirectResponse
    {
        $this->authorizeDigitalFile($digitalFile);

        $validated = $request->validate([
            'resource_id' => ['required', 'exists:resources,id'],
            'file' => ['nullable', 'file', 'max:20480'],
        ]);

        $resource = Resource::findOrFail($validated['resource_id']);
        $this->authorizeResource($resource);

        $data = ['resource_id' => $resource->id];

        if ($request->hasFile('file')) {
            Storage::disk('local')->delete($digitalFile->file_path);

            $uploadedFile = $request->file('file');
            $data['file_path'] = $uploadedFile->store('digital-files', 'local');
            $data['file_name'] = $uploadedFile->getClientOriginalName();
            $data['file_type'] = $uploadedFile->getClientMimeType();
            $data['size'] = $uploadedFile->getSize();
        }

        $digitalFile->update($data);

        return redirect()
            ->route('digital-files.show', $digitalFile)
            ->with('success', 'Digital file updated successfully.');
    }

    public function destroy(DigitalFile $digitalFile): RedirectResponse
    {
        $this->authorizeDigitalFile($digitalFile);

        Storage::disk('local')->delete($digitalFile->file_path);
        $digitalFile->delete();

        return redirect()
            ->route('digital-files.index')
            ->with('success', 'Digital file deleted successfully.');
    }

    public function download(DigitalFile $digitalFile): StreamedResponse
    {
        $this->authorizeDigitalFile($digitalFile);

        abort_unless(
            Storage::disk('local')->exists($digitalFile->file_path),
            404,
            'File not found.'
        );

        $digitalFile->increment('download_count');

        return Storage::disk('local')->download(
            $digitalFile->file_path,
            $digitalFile->file_name
        );
    }

    private function authorizedResources()
    {
        $query = Resource::where('type', 'digital')->orderBy('title');

        if (! auth()->user()->hasRole('admin')) {
            $query->where('owner_id', auth()->id());
        }

        return $query->get();
    }

    private function authorizeResource(Resource $resource): void
    {
        abort_unless(
            auth()->user()->hasRole('admin') || $resource->owner_id === auth()->id(),
            403
        );
    }

    private function authorizeDigitalFile(DigitalFile $digitalFile): void
    {
        $this->authorizeResource($digitalFile->resource);
    }
}
