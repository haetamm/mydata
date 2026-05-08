<?php

declare(strict_types=1);

namespace App\Validation;

use Rakit\Validation\Validator;

class SiswaValidation
{
    public static function save(array $data): array
    {
        $v = (new Validator)->make($data, [
            'nama'         => 'required|min:3|max:100',
            'nis'          => 'required|max:20',
            'nisn'         => 'required|digits:10',
            'nik'          => 'nullable|digits:16',
            'tempat_lahir' => 'required|max:50',
            'tgl_lahir'    => 'required|date',
            'id_agama'     => 'required',
            'id_kelas'     => 'required',
            'ruang'        => 'required|max:20',
            'tahun_masuk'  => 'nullable|numeric|min:2000',
            'alamat'       => 'required',
            'rt_rw'        => 'required|max:10',
            'dusun'        => 'required|max:50',
            'kelurahan'    => 'required|max:50',
            'kecamatan'    => 'required|max:50',
            'kode_pos'     => 'nullable|digits:5',
            'ayah_nik'         => 'nullable|digits:16',
            'ayah_tahun_lahir' => 'nullable|numeric|min:1940|max:2000',
            'ayah_penghasilan' => 'nullable|numeric',
            'ibu_nik'          => 'nullable|digits:16',
            'ibu_tahun_lahir'  => 'nullable|numeric|min:1940|max:2005',
            'ibu_penghasilan'  => 'nullable|numeric',
            'wali_nik'         => 'nullable|digits:16',
            'wali_tahun_lahir' => 'nullable|numeric|min:1940|max:2000',
            'wali_penghasilan' => 'nullable|numeric',
        ], [
            'nama:required'         => 'Nama lengkap wajib diisi.',
            'nama:min'              => 'Nama minimal 3 karakter.',
            'nis:required'          => 'NIS wajib diisi.',
            'nisn:required'         => 'NISN wajib diisi.',
            'nisn:digits'           => 'NISN harus tepat 10 digit angka.',
            'nik:digits'            => 'NIK harus tepat 16 digit angka.',
            'tempat_lahir:required' => 'Tempat lahir wajib diisi.',
            'tgl_lahir:required'    => 'Tanggal lahir wajib diisi.',
            'tgl_lahir:date'        => 'Format tanggal lahir tidak valid.',
            'id_agama:required'     => 'Agama wajib dipilih.',
            'id_kelas:required'     => 'Kelas wajib dipilih.',
            'ruang:required'        => 'Ruang wajib diisi.',
            'alamat:required'       => 'Alamat wajib diisi.',
            'rt_rw:required'        => 'RT/RW wajib diisi.',
            'dusun:required'        => 'Dusun wajib diisi.',
            'kelurahan:required'    => 'Kelurahan wajib diisi.',
            'kecamatan:required'    => 'Kecamatan wajib diisi.',
            'kode_pos:digits'       => 'Kode pos harus 5 digit angka.',
            'ayah_nik:digits'       => 'NIK Ayah harus 16 digit.',
            'ibu_nik:digits'        => 'NIK Ibu harus 16 digit.',
            'wali_nik:digits'       => 'NIK Wali harus 16 digit.',
        ]);

        $v->validate();
        return $v->fails() ? $v->errors()->firstOfAll() : [];
    }
}
