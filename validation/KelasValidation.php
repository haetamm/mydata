<?php

declare(strict_types=1);

namespace App\Validation;

use Rakit\Validation\Validator;

class KelasValidation
{
    public static function save(array $data): array
    {
        $validator = new Validator;

        $v = $validator->make($data, [
            'nama_kelas'   => 'required|max:20',
            'level_id' => 'required|numeric|min:1',
        ], [
            'nama_kelas:required'   => 'Nama Kelas wajib diisi.',
            'nama_kelas:max'        => 'Nama Kelas maksimal 20 karakter.',
            'level_id:required'     => 'Level minimum wajib diisi.',
            'level_id:numeric'      => 'Level minimum harus berupa angka.',
            'level_id:min'          => 'Level minimum minimal 1.',
        ]);

        $v->validate();

        return [];
    }
}
