<?php

declare(strict_types=1);

namespace App\Validation;

use Rakit\Validation\Validator;

class ProfileValidation
{
    public static function save(array $data, bool $isCreate): array
    {
        $passwordRule = $isCreate ? 'required|min:6' : '';

        // Hanya validasi konfirmasi jika password diisi
        $konfirmasiRule = ($data['password'] ?? '') !== '' ? 'same:password' : '';

        $rules = [
            'nama_lengkap'        => 'required|max:100',
            'username'            => ['required', 'regex:/^[a-zA-Z0-9._-]{3,100}$/'],
            'password'            => $passwordRule,
            'password_konfirmasi' => $konfirmasiRule,
        ];

        $messages = [
            'nama_lengkap:required'    => 'Nama lengkap wajib diisi.',
            'nama_lengkap:max'         => 'Nama lengkap maksimal 100 karakter.',
            'username:required'        => 'Username wajib diisi.',
            'username:regex'           => 'Username hanya boleh huruf, angka, titik, strip, dan underscore (3–100 karakter).',
            'password:required'        => 'Password wajib diisi.',
            'password:min'             => 'Password minimal 6 karakter.',
            'password_konfirmasi:same' => 'Konfirmasi password tidak cocok.',
        ];

        $v = (new Validator)->make($data, $rules, $messages);
        $v->validate();

        return $v->fails() ? $v->errors()->firstOfAll() : [];
    }
}
