<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOrderRequest extends FormRequest
{
    /**
     * Xác định người dùng có quyền thực hiện request này không.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Các quy tắc validation cho request tạo Order.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'menu_id'    => ['required', 'integer', 'exists:menus,id'],
            'split_type' => ['required', Rule::in(['none', 'even', 'individual'])],

            // Phí phát sinh (có thể thiết lập ngay khi tạo hoặc cập nhật sau)
            'shipping_fee'    => ['nullable', 'numeric', 'min:0'],
            'tax_amount'      => ['nullable', 'numeric', 'min:0'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],

            // Thông tin ngân hàng Host (snapshot tại thời điểm tạo đơn)
            'bank_name'           => ['nullable', 'string', 'max:50'],
            'bank_account_number' => ['nullable', 'string', 'max:30'],
            'bank_account_name'   => ['nullable', 'string', 'max:100'],
        ];
    }

    /**
     * Tuỳ chỉnh message lỗi.
     */
    public function messages(): array
    {
        return [
            'menu_id.required' => 'Vui lòng chọn một Menu để tạo đơn hàng.',
            'menu_id.exists'   => 'Menu đã chọn không tồn tại trong hệ thống.',
            'split_type.in'    => 'Phương thức chia tiền không hợp lệ.',
        ];
    }
}
