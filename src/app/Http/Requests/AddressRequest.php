<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddressRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'postal_code' => ['required', 'regex:/^\d{3}-\d{4}$/'], // ハイフンありの8文字
            'address'     => ['required'],
            'building_name'    => ['required'],
        ];
    }

    public function messages(): array
    {
        return [
            'postal_code.required'  => '郵便番号を入力してください。',
            'postal_code.regex'     => '郵便番号はハイフンありの形式（例：123-4567）で入力してください。',
            'address.required'      => '住所を入力してください。',
            'building.required'     => '建物名を入力してください。',
        ];
    }
}// 入力データで更新


