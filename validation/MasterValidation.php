<?php

declare(strict_types=1);

namespace App\Validation;

use Rakit\Validation\Validator;

class MasterValidation
{
    public static function save(array $data): array
    {

        $v = (new Validator)->make($data, [
            'nama'           => 'required|max:100',
        ], [
            'nama:required'          => 'Nama wajib diisi.',
            'nama:max'               => 'Nama maksimal 100 karakter.',
        ]);

        $v->validate();
        return $v->fails() ? $v->errors()->firstOfAll() : [];
    }
}
