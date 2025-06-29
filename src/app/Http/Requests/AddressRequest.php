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
            'postal_code' => ['required', 'regex:/^\d{7}$/'], // ハイフンなし7桁に統一
            'address'     => ['required'],
            'building_name' => ['nullable'], // 建物名は任意項目に変更
        ];
    }

    public function messages(): array
    {
        return [
            'postal_code.required'  => '郵便番号を入力してください。',
            'postal_code.regex'     => '郵便番号は7桁の数字で入力してください（例：1234567）。',
            'address.required'      => '住所を入力してください。',
        ];
    }
}