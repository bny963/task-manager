<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CategoryRequest extends FormRequest
{
    /**
     * リクエストの認可
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * バリデーションルール
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories', 'name')->ignore($this->category),
            ],
        ];
    }

    /**
     * バリデーションメッセージ
     */
    public function messages(): array
    {
        return [
            'name.required' => 'カテゴリー名は必須です。',
            'name.max' => 'カテゴリー名は255文字以内で入力してください。',
            'name.unique' => 'このカテゴリー名は既に使用されています。',
        ];
    }
}
