<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // ログインユーザーのみ許可
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'postal_code' => 'nullable|string|regex:/^[0-9]+$/|max:8',
            'address' => 'nullable|string|max:255',
            'building_name' => 'nullable|string|max:255',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ];
    }

    /**
     * バリデーションエラーメッセージの日本語化
     * 
     * @return array
     */
    public function messages()
    {
        return [
            'name.required' => 'ユーザー名は必須です',
            'name.max' => 'ユーザー名は255文字以内で入力してください',
            'postal_code.max' => '郵便番号は8文字以内で入力してください',
            'postal_code.regex' => '郵便番号は数字のみで入力してください（ハイフンなし）',
            'address.max' => '住所は255文字以内で入力してください',
            'building_name.max' => '建物名は255文字以内で入力してください',
            'avatar.image' => 'アップロードできるのは画像ファイルのみです',
            'avatar.mimes' => '対応している画像形式はjpeg、png、jpg、gifのみです',
            'avatar.max' => '画像サイズは2MB以下にしてください',
        ];
    }
}