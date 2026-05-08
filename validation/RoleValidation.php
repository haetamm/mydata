<?php

declare(strict_types=1);

namespace App\Validation;

use Rakit\Validation\Validator;

class RoleValidation
{
    public static function save(array $data, string $formMode): array
    {
        $rules = [
            'name' => 'required',
        ];

        $messages = [
            'name:required' => 'Nama role wajib diisi.',
        ];

        if ($formMode === 'create')
        {
            $rules['id']              = 'required';
            $messages['id:required']  = 'ID role wajib diisi.';
        }

        $v = (new Validator)->make($data, $rules, $messages);
        $v->validate();

        return $v->fails() ? $v->errors()->firstOfAll() : [];
    }
}
