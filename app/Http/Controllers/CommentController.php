<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCommentRequest;
use App\Models\Bug;
use App\Models\Comment;
use App\Services\ActivityService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;

class CommentController extends Controller
{
    public function __construct(
        private ActivityService $activityService
    ) {}

    public function store(StoreCommentRequest $request, Bug $bug): RedirectResponse
    {
        $comment = $bug->comments()->create([
            'user_id' => auth()->id(),
            'body' => $request->body,
            'parent_id' => $request->parent_id,
        ]);

        // 處理附件
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $filename = Str::uuid().'.'.$file->getClientOriginalExtension();
            $path = $file->storeAs('attachments/'.$bug->id, $filename, 'public');

            $comment->attachments()->create([
                'bug_id' => $bug->id,
                'user_id' => auth()->id(),
                'filename' => $filename,
                'original_filename' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
                'disk' => 'public',
                'path' => $path,
            ]);
        }

        $this->activityService->log($bug, 'commented', metadata: [
            'comment_id' => $comment->id,
        ]);

        return back()->with('success', 'Comment added successfully.');
    }

    public function update(StoreCommentRequest $request, Comment $comment): RedirectResponse
    {
        $this->authorize('update', $comment);

        $comment->update(['body' => $request->body]);

        return back()->with('success', 'Comment updated successfully.');
    }

    public function destroy(Comment $comment): RedirectResponse
    {
        $this->authorize('delete', $comment);

        $comment->delete();

        return back()->with('success', 'Comment deleted successfully.');
    }
}
