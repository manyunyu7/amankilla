<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSceneRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'content' => ['nullable', 'string', 'max:100000'],
            'summary' => ['nullable', 'string', 'max:2000'],
            'date' => ['nullable', 'string', 'max:255'],
            'time' => ['nullable', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'mood' => ['nullable', 'string', 'max:100'],
            'pov' => ['nullable', 'string', 'max:100'],
            'is_branch_point' => ['boolean'],
            'branch_question' => ['nullable', 'string', 'max:500'],
            'character_ids' => ['nullable', 'array'],
            'character_ids.*' => ['exists:characters,id'],
            'tag_ids' => ['nullable', 'array'],
            'tag_ids.*' => ['exists:tags,id'],
        ];
    }
}
