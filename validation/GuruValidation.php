<?php

declare(strict_types=1);

namespace App\Validation;

use Rakit\Validation\Validator;

class GuruValidation
{
    public static function save(array $data): array
    {
        $tahunSekarang = (int) date('Y');

        $v = (new Validator)->make($data, [
            'nama'           => 'required|min:3|max:100',
            'nik'            => 'nullable|digits:16',
            'nuptk'          => 'nullable|max:20',
            'jenis_kelamin'  => 'required|in:L,P',
            'tempat_lahir'   => 'nullable|max:50',
            'tgl_lahir'      => 'nullable|date',
            'nama_ibu'       => 'nullable|max:100',
            'status_pegawai' => 'nullable|max:50',
            'jenis_gtk'      => 'nullable|max:50',
            'jabatan'        => 'nullable|max:50',
            'tahun_masuk'    => "nullable|numeric|min:1970|max:{$tahunSekarang}",
            'alamat'         => 'nullable',
        ], [
            'nama:required'          => 'Nama lengkap wajib diisi.',
            'nama:min'               => 'Nama minimal 3 karakter.',
            'nama:max'               => 'Nama maksimal 100 karakter.',
            'nik:digits'             => 'NIK harus tepat 16 digit angka.',
            'nuptk:max'              => 'NUPTK maksimal 20 karakter.',
            'jenis_kelamin:required' => 'Jenis kelamin wajib dipilih.',
            'jenis_kelamin:in'       => 'Jenis kelamin tidak valid.',
            'tempat_lahir:max'       => 'Tempat lahir maksimal 50 karakter.',
            'tgl_lahir:date'         => 'Format tanggal lahir tidak valid.',
            'nama_ibu:max'           => 'Nama ibu maksimal 100 karakter.',
            'tahun_masuk:numeric'    => 'Tahun masuk harus berupa angka.',
            'tahun_masuk:min'        => 'Tahun masuk tidak valid.',
            'tahun_masuk:max'        => "Tahun masuk tidak boleh lebih dari {$tahunSekarang}.",
        ]);

        $v->validate();
        return $v->fails() ? $v->errors()->firstOfAll() : [];
    }
}
