<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTagRequest;
use App\Models\Tag;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class TagController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Tags/Index', [
            'tags' => Tag::withCount('bugs')->get(),
        ]);
    }

    public function store(StoreTagRequest $request): RedirectResponse
    {
        Tag::create($request->validated());

        return back()->with('success', 'Tag created successfully.');
    }

    public function update(StoreTagRequest $request, Tag $tag): RedirectResponse
    {
        $tag->update($request->validated());

        return back()->with('success', 'Tag updated successfully.');
    }

    public function destroy(Tag $tag): RedirectResponse
    {
        $tag->delete();

        return back()->with('success', 'Tag deleted successfully.');
    }
}
