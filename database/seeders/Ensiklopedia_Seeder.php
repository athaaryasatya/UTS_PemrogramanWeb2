<?php

namespace Database\Seeders;

use App\Models\Ensiklopedia;
use Illuminate\Database\Seeder;

class Ensiklopedia_Seeder extends Seeder
{
    public function run(): void
    {
        if (Ensiklopedia::whereNull('id_petani')->exists()) {
            return;
        }

        $data = [
            [
                'judul' => 'Padi',
                'kategori' => 'Tanaman',
                'ringkasan' => 'belum ada',
                'isi' => "belum ada",
            ],
            
        ];

        foreach ($data as $row) {
            Ensiklopedia::create($row + ['id_petani' => null]);
        }
    }
}