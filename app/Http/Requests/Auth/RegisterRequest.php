<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20', 'unique:users,phone'],
            'password' => ['required', 'string', 'min:6'],
            'gender' => ['required', 'in:male,female'],
            'email' => ['nullable', 'email', 'unique:users,email'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'İsim zorunludur.',
            'phone.required' => 'Telefon numarası zorunludur.',
            'phone.unique' => 'Bu telefon numarası zaten kayıtlı.',
            'password.required' => 'Şifre zorunludur.',
            'password.min' => 'Şifre en az 6 karakter olmalıdır.',
            'gender.required' => 'Cinsiyet seçimi zorunludur.',
            'gender.in' => 'Geçersiz cinsiyet değeri.',
            'email.unique' => 'Bu e-posta adresi zaten kayıtlı.',
        ];
    }
}
