<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMenuRequest extends FormRequest
{
    /**
     * Xác định người dùng có quyền thực hiện request này không.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Các quy tắc validation cho request tạo Menu.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name'             => ['required', 'string', 'max:150'],
            'description'      => ['nullable', 'string', 'max:500'],
            'phone'            => ['nullable', 'string', 'max:15'],
            'address'          => ['nullable', 'string', 'max:500'],
            'is_temp'          => ['boolean'],

            // Danh sách món ăn đính kèm khi tạo menu
            'items'            => ['nullable', 'array'],
            'items.*.name'     => ['required_with:items', 'string', 'max:150'],
            'items.*.price'    => ['required_with:items', 'numeric', 'min:0'],
            'items.*.image_url'=> ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * Tuỳ chỉnh message lỗi.
     */
    public function messages(): array
    {
        return [
            'name.required'          => 'Tên menu là bắt buộc.',
            'items.*.name.required_with' => 'Mỗi món ăn phải có tên.',
            'items.*.price.required_with' => 'Mỗi món ăn phải có giá.',
            'items.*.price.min'      => 'Giá món ăn không được âm.',
        ];
    }
}
