<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderItemRequest extends FormRequest
{
    /**
     * Xác định người dùng có quyền thực hiện request này không.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Các quy tắc validation cho request thêm món ăn vào giỏ.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'participant_id' => ['required', 'integer', 'exists:order_participants,id'],
            'menu_item_id'   => ['required', 'integer', 'exists:menu_items,id'],
            'quantity'       => ['required', 'integer', 'min:1', 'max:99'],
            'note'           => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * Tuỳ chỉnh message lỗi.
     */
    public function messages(): array
    {
        return [
            'participant_id.required' => 'Thiếu thông tin người đặt.',
            'participant_id.exists'   => 'Người đặt không tồn tại trong đơn hàng này.',
            'menu_item_id.required'   => 'Vui lòng chọn một món ăn.',
            'menu_item_id.exists'     => 'Món ăn không tồn tại trong menu.',
            'quantity.required'       => 'Vui lòng nhập số lượng.',
            'quantity.min'            => 'Số lượng tối thiểu là 1.',
            'quantity.max'            => 'Số lượng tối đa là 99.',
        ];
    }
}
