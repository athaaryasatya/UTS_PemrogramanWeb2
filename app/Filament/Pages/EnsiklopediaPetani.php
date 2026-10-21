<?php

namespace App\Filament\Pages;

use App\Models\Ensiklopedia;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class EnsiklopediaPetani extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-book-open';
    protected static ?string $navigationLabel = 'Ensiklopedia Petani';
    protected static ?int $navigationSort = 4;
    protected static ?string $title = 'Ensiklopedia Petani';
    protected static ?string $slug = 'ensiklopedia';
    protected string $view = 'filament.pages.ensiklopedia';

    public ?string $cari = '';

  
    public string $filterKategori = 'Semua';
    public ?string $terbuka = null;

  
    public bool $tampilForm = false;
    public ?string $editId = null;
    public string $judul = '';
    public string $kategori = '';
    public string $ringkasan = '';
    public string $isi = '';

    protected function getViewData(): array
    {
        $query = Ensiklopedia::query()->orderBy('kategori')->orderBy('judul');

        if ($this->filterKategori !== 'Semua') {
            $query->where('kategori', $this->filterKategori);
        }

        if (trim($this->cari) !== '') {
            $kata = '%' . trim($this->cari) . '%';
            $query->where(function ($q) use ($kata) {
                $q->where('judul', 'like', $kata)
                  ->orWhere('ringkasan', 'like', $kata)
                  ->orWhere('isi', 'like', $kata);
            });
        }

        return [
            'entri'        => $query->get(),
            'kategoriList' => Ensiklopedia::KATEGORI,
            'idSaya'       => auth()->id(),
        ];
    }

    public function setKategori(string $kategori): void
    {
        $this->filterKategori = $kategori;
    }

    public function toggle(string $id): void
    {
        $this->terbuka = $this->terbuka === $id ? null : $id;
    }

    public function tambah(): void
    {
        $this->resetForm();
        $this->tampilForm = true;
    }

    public function edit(string $id): void
    {
        $entri = $this->entriMilikSaya($id);
        if (!$entri) {
            return;
        }

        $this->resetValidation();
        $this->editId    = $entri->id_ensiklopedia;
        $this->judul     = $entri->judul;
        $this->kategori  = $entri->kategori;
        $this->ringkasan = $entri->ringkasan;
        $this->isi       = $entri->isi;
        $this->tampilForm = true;
    }

    public function simpan(): void
    {
        $data = $this->validate([
            'judul'     => ['required', 'string', 'min:1', 'max:150'],
            'kategori'  => ['required', 'in:' . implode(',', Ensiklopedia::KATEGORI)],
            'ringkasan' => ['required', 'string', 'min:1', 'max:300'],
            'isi'       => ['required', 'string', 'min:1'],
        ], [
            'judul.required'     => 'Judul wajib diisi.',
            'judul.min'          => 'Judul minimal 1 karakter.',
            'judul.max'          => 'Judul maksimal 150 karakter.',
            'kategori.required'  => 'Pilih kategori.',
            'kategori.in'        => 'Kategori tidak valid.',
            'ringkasan.required' => 'Ringkasan wajib diisi.',
            'ringkasan.min'      => 'Ringkasan minimal 1 karakter.',
            'ringkasan.max'      => 'Ringkasan maksimal 300 karakter.',
            'isi.required'       => 'Isi artikel wajib diisi.',
            'isi.min'            => 'Isi artikel minimal 1 karakter.',
        ]);

        if ($this->editId) {
            $entri = $this->entriMilikSaya($this->editId);
            if (!$entri) {
                return;
            }
            $entri->update($data);
            $pesan = 'Entri berhasil diperbarui.';
        } else {
            Ensiklopedia::create($data + ['id_petani' => auth()->id()]);
            $pesan = 'Entri berhasil ditambahkan.';
        }

        Notification::make()->title($pesan)->success()->send();
        $this->resetForm();
    }

    public function hapus(string $id): void
    {
        $entri = $this->entriMilikSaya($id);
        if (!$entri) {
            return;
        }

        $entri->delete();
        if ($this->terbuka === $id) {
            $this->terbuka = null;
        }

        Notification::make()->title('Entri dihapus.')->success()->send();
    }

    public function batal(): void
    {
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->reset(['editId', 'judul', 'kategori', 'ringkasan', 'isi']);
        $this->tampilForm = false;
        $this->resetValidation();
    }


    private function entriMilikSaya(string $id): ?Ensiklopedia
    {
        $entri = Ensiklopedia::find($id);

        if (!$entri || $entri->id_petani === null || $entri->id_petani !== auth()->id()) {
            Notification::make()
                ->title('Tidak diizinkan')
                ->body('Anda hanya dapat mengubah atau menghapus entri milik sendiri.')
                ->danger()
                ->send();
            return null;
        }

        return $entri;
    }
}