<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;

class ResizeImageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        $rules = [
            'image' => ['required'],
            // 'w' => ['required', 'regax:/^\d+(\.\d+)?%?$/'],
            // 'h' => ['regax:/^\d+(\.\d+)?%?$/'],
            'album_id' => ['exists:\App\Models\Album,id'],
        ];

        $image = $this->all()['image'] ?? false;

        if($image && $image instanceof UploadedFile):       // to check if input is an image path/file or not
            $rules['image'][] = 'image';
        else:
            $rules['image'][] = 'url';
        endif;

        return $rules;

    }
}
