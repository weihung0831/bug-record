<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBugRequest;
use App\Http\Requests\UpdateBugRequest;
use App\Models\Bug;
use App\Models\Tag;
use App\Models\User;
use App\Services\BugService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BugController extends Controller
{
    public function __construct(
        private BugService $bugService
    ) {}

    public function index(Request $request): Response
    {
        $bugs = Bug::query()
            ->with(['reporter:id,name', 'assignee:id,name', 'tags:id,name,color'])
            ->when($request->status, fn ($q, $status) => $q->status($status))
            ->when($request->priority, fn ($q, $priority) => $q->priority($priority))
            ->when($request->assignee, fn ($q, $assignee) => $q->assignedTo($assignee))
            ->when($request->tag, fn ($q, $tag) => $q->withTag($tag))
            ->when($request->search, fn ($q, $search) => $q->search($search))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Bugs/Index', [
            'bugs' => $bugs,
            'filters' => $request->only(['status', 'priority', 'assignee', 'tag', 'search']),
            'statuses' => Bug::STATUSES,
            'priorities' => Bug::PRIORITIES,
            'tags' => Tag::all(['id', 'name', 'slug', 'color']),
            'users' => User::all(['id', 'name']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Bugs/Create', [
            'priorities' => Bug::PRIORITIES,
            'tags' => Tag::all(['id', 'name', 'color']),
            'users' => User::all(['id', 'name']),
        ]);
    }

    public function store(StoreBugRequest $request): RedirectResponse
    {
        $bug = $this->bugService->create($request->validated());

        return redirect()->route('bugs.show', $bug)
            ->with('success', 'Bug created successfully.');
    }

    public function show(Bug $bug): Response
    {
        $bug->load([
            'reporter:id,name,email',
            'assignee:id,name,email',
            'tags',
            'comments' => fn ($q) => $q->whereNull('parent_id')
                ->with(['user:id,name', 'replies.user:id,name', 'attachments'])
                ->latest(),
            'attachments.user:id,name',
            'activities.user:id,name',
        ]);

        return Inertia::render('Bugs/Show', [
            'bug' => $bug,
            'statuses' => Bug::STATUSES,
            'priorities' => Bug::PRIORITIES,
            'tags' => Tag::all(['id', 'name', 'color']),
            'users' => User::all(['id', 'name']),
            'can' => [
                'update' => request()->user()->can('update', $bug),
                'delete' => request()->user()->can('delete', $bug),
            ],
        ]);
    }

    public function edit(Bug $bug): Response
    {
        $this->authorize('update', $bug);

        $bug->load(['tags']);

        return Inertia::render('Bugs/Edit', [
            'bug' => $bug,
            'priorities' => Bug::PRIORITIES,
            'tags' => Tag::all(['id', 'name', 'color']),
            'users' => User::all(['id', 'name']),
        ]);
    }

    public function update(UpdateBugRequest $request, Bug $bug): RedirectResponse
    {
        $this->bugService->update($bug, $request->validated());

        return redirect()->route('bugs.show', $bug)
            ->with('success', 'Bug updated successfully.');
    }

    public function destroy(Bug $bug): RedirectResponse
    {
        $this->authorize('delete', $bug);

        $bug->delete();

        return redirect()->route('bugs.index')
            ->with('success', 'Bug deleted successfully.');
    }
}
