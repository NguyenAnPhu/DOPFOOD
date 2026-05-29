<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class JoinOrderRequest extends FormRequest
{
    /**
     * Xác định người dùng có quyền thực hiện request này không.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Các quy tắc validation cho request tham gia đơn hàng.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'guest_name'    => ['required', 'string', 'max:100'],
            'guest_phone'   => ['nullable', 'string', 'max:15'],
            // Token định danh cookie – frontend tự tạo UUID và gửi lên
            'session_token' => ['nullable', 'string', 'max:100'],
        ];
    }

    /**
     * Tuỳ chỉnh message lỗi.
     */
    public function messages(): array
    {
        return [
            'guest_name.required' => 'Vui lòng nhập tên của bạn để tham gia đơn hàng.',
        ];
    }
}
