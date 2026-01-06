<?php

namespace App\Http\Requests;

use App\Models\Bug;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBugRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('bug'));
    }

    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'string'],
            'status' => ['sometimes', Rule::in(Bug::STATUSES)],
            'priority' => ['sometimes', Rule::in(Bug::PRIORITIES)],
            'assignee_id' => ['nullable', 'exists:users,id'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['exists:tags,id'],
        ];
    }
}
