<?php

namespace Modules\Album\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Album\Enums\AlbumStatus;

class StoreAlbumRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'size:3'],
            'status' => ['nullable', 'in:' . implode(',', array_column(AlbumStatus::cases(), 'value'))],
        ];
    }
}
