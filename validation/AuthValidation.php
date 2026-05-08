<?php

declare(strict_types=1);

namespace App\Validation;

use Rakit\Validation\Validator;

class AuthValidation
{
    public static function login(array $data): array
    {
        $v = (new Validator)->make($data, [
            'username' => 'required',
            'password' => 'required',
        ], [
            'username:required' => 'Username belum diisi.',
            'password:required' => 'Password belum diisi.',
        ]);

        $v->validate();
        return $v->fails() ? $v->errors()->firstOfAll() : [];
    }
}
