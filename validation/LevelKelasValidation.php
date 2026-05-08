<?php

declare(strict_types=1);

namespace App\Validation;

use Rakit\Validation\Validator;

class LevelKelasValidation
{
    public static function save(array $data): array
    {
        $validator = new Validator;

        $v = $validator->make($data, [
            'jenjang'   => 'required|max:20',
            'level_min' => 'required|numeric|min:1',
            'level_max' => 'required|numeric|min:1',
        ], [
            'jenjang:required'   => 'Jenjang wajib diisi.',
            'jenjang:max'        => 'Jenjang maksimal 20 karakter.',
            'level_min:required' => 'Level minimum wajib diisi.',
            'level_min:numeric'  => 'Level minimum harus berupa angka.',
            'level_min:min'      => 'Level minimum minimal 1.',
            'level_max:required' => 'Level maksimum wajib diisi.',
            'level_max:numeric'  => 'Level maksimum harus berupa angka.',
            'level_max:min'      => 'Level maksimum minimal 1.',
        ]);

        $v->validate();

        // ❌ kalau gagal rule dasar
        if ($v->fails())
        {
            return $v->errors()->firstOfAll();
        }

        // VALIDASI KHUSUS: level_min <= level_max
        if ((int)$data['level_min'] > (int)$data['level_max'])
        {
            return [
                'level_min' => 'Level minimum tidak boleh lebih besar dari level maksimum.',
                'level_max' => 'Level maksimum tidak boleh lebih kecil dari level minimum.',
            ];
        }

        return [];
    }
}
