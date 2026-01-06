<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAttachmentRequest;
use App\Models\Attachment;
use App\Models\Bug;
use App\Services\ActivityService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttachmentController extends Controller
{
    public function __construct(
        private ActivityService $activityService
    ) {}

    public function store(StoreAttachmentRequest $request, Bug $bug): RedirectResponse
    {
        $file = $request->file('file');
        $filename = Str::uuid().'.'.$file->getClientOriginalExtension();
        $path = $file->storeAs('attachments/'.$bug->id, $filename, 'public');

        $attachment = $bug->attachments()->create([
            'user_id' => auth()->id(),
            'filename' => $filename,
            'original_filename' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'disk' => 'public',
            'path' => $path,
        ]);

        $this->activityService->log($bug, 'attachment_added', metadata: [
            'attachment_id' => $attachment->id,
            'filename' => $attachment->original_filename,
        ]);

        return back()->with('success', 'File uploaded successfully.');
    }

    public function destroy(Attachment $attachment): RedirectResponse
    {
        $this->authorize('delete', $attachment);

        $bug = $attachment->bug;
        $filename = $attachment->original_filename;

        $attachment->deleteFile();
        $attachment->delete();

        $this->activityService->log($bug, 'attachment_removed', metadata: [
            'filename' => $filename,
        ]);

        return back()->with('success', 'Attachment deleted successfully.');
    }

    public function show(Attachment $attachment): StreamedResponse
    {
        $disk = Storage::disk($attachment->disk);

        if (! $disk->exists($attachment->path)) {
            abort(404);
        }

        return response()->streamDownload(function () use ($disk, $attachment) {
            echo $disk->get($attachment->path);
        }, $attachment->original_filename, [
            'Content-Type' => $attachment->mime_type,
            'Content-Disposition' => 'inline; filename="'.$attachment->original_filename.'"',
        ]);
    }

    public function download(Attachment $attachment): StreamedResponse
    {
        return Storage::disk($attachment->disk)->download(
            $attachment->path,
            $attachment->original_filename
        );
    }
}
