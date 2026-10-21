<?php

namespace App\Filament\Pages;

use App\Filament\Pages\RiwayatAnalisis;
use App\Models\Keluhan;
use App\Services\KiraAIService;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Str;
// use Filament\Forms\Components\TextInput;
// use Filament\Forms\Components\Textarea;

class KirimKeluhan extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-plus';
    protected static ?string $navigationLabel = 'Kirim Keluhan';
     protected static ?int $navigationSort = 1;
    protected static ?string $title = 'Kirim Keluhan Tanaman';
    protected string $view = 'filament.pages.kirim-keluhan';

    public string $jenisTanaman = 'misalnya padi';
    public string $lokasiLahan = '';
    public string $urgensi = 'Sedang';
    public string $deskripsi = '';

public function submit()
{
    // 1. Tentukan minimal kata berdasarkan urgensi
    $minWords = match (strtolower($this->urgensi)) {
        'rendah' => 3,
        'sedang' => 5,
        'tinggi' => 50,
        default  => 15,
    };

    // 2. Validasi input kosong standar
    if (empty($this->jenisTanaman) || empty($this->lokasiLahan) || empty($this->deskripsi)) {
        Notification::make()
            ->title('Form Belum Lengkap')
            ->body('Harap isi semua kolom inputan terlebih dahulu.')
            ->warning()
            ->send();
        return;
    }

    // 3. Hitung kata untuk validasi tantangan dosen
    $wordCount = count(array_filter(preg_split('/\s+/', trim($this->deskripsi))));

    if ($wordCount < $minWords) {
        Notification::make()
            ->title('Deskripsi Kurang Detail')
            ->body("Untuk urgensi {$this->urgensi}, deskripsi minimal {$minWords} kata. (Saat ini baru {$wordCount} kata)")
            ->danger()
            ->send();
        return; // Stop proses, gak lanjut panggil AI
    }

    try {
        // 4. Gabung teks keluhan
        $teksKeluhanLengkap = "Jenis Tanaman: {$this->jenisTanaman}\nLokasi: {$this->lokasiLahan}\nUrgensi: {$this->urgensi}\nKeluhan: {$this->deskripsi}";

        // 5. Panggil AI Service
        $hasilAi = KiraAIService::generateSolusi(
            $this->deskripsi,
            $this->lokasiLahan
        );

        // 6. Simpan ke Database
        $keluhan = Keluhan::create([
            'id_keluhan'      => (string) Str::uuid(),
            'id_petani'       => auth()->id() ?? '01a11165-ec48-7296-ab63-1d1a601c3cec',
            'teks_keluhan'    => $teksKeluhanLengkap,
            'tingkat_urgensi' => $this->urgensi,
            'hasil_analisis'  => $hasilAi,
            'tanggal'         => now(),
            'status'          => 'selesai',
        ]);

        Notification::make()
            ->title('Analisis AI Selesai!')
            ->body('Hasil analisis keluhan Anda telah berhasil disimpan.')
            ->success()
            ->send();

        $this->reset(['jenisTanaman', 'lokasiLahan', 'urgensi', 'deskripsi']);

        return $this->redirect(RiwayatAnalisis::getUrl());

    } catch (\Exception $e) {
        Notification::make()
            ->title('Gagal Menyimpan Laporan')
            ->body($e->getMessage())
            ->danger()
            ->send();
    }
}

    public function batal(): void
    {
        $this->reset(['jenisTanaman', 'lokasiLahan', 'urgensi', 'deskripsi']);
    }

    public function setUrgensi(string $level): void
    {
        $this->urgensi = $level;
    }

}