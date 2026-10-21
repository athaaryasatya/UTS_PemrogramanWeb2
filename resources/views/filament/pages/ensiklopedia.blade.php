<x-filament-panels::page>
    {{-- TOOLBAR: cari, filter, tambah --}}
    <x-filament::section>
        <div style="display:flex; flex-wrap:wrap; gap:0.75rem; align-items:center; justify-content:space-between;">
            <div style="flex:1; min-width:240px; max-width:420px;">
                <x-filament::input.wrapper>
                    <x-filament::input
                        type="text"
                        wire:model.live.debounce.300ms="cari"
                        placeholder="Cari judul, gejala, atau isi..."
                    />
                </x-filament::input.wrapper>
            </div>
            <x-filament::button type="button" color="success" wire:click="tambah">
                + Tambah Entri
            </x-filament::button>
        </div>

        <div style="display:flex; flex-wrap:wrap; gap:0.5rem; margin-top:1rem;">
            @foreach(array_merge(['Semua'], $kategoriList) as $kat)
                <x-filament::button
                    type="button"
                    size="sm"
                    wire:click="setKategori('{{ $kat }}')"
                    :color="$filterKategori === $kat ? 'success' : 'gray'"
                    :outlined="$filterKategori !== $kat">
                    {{ $kat }}
                </x-filament::button>
            @endforeach
        </div>
    </x-filament::section>

    {{-- FORM CREATE / UPDATE --}}
    @if($tampilForm)
        <x-filament::section :heading="$editId ? 'Edit Entri' : 'Tambah Entri Baru'">
            <form wire:submit="simpan" style="display:flex; flex-direction:column; gap:1rem;">
                <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(260px, 1fr)); gap:1rem;">
                    <div>
                        <label style="font-size:0.875rem; font-weight:600; display:block; margin-bottom:0.375rem;">Judul</label>
                        <x-filament::input.wrapper :error="$errors->has('judul')">
                            <x-filament::input type="text" wire:model="judul" placeholder="Contoh: Hama Kepik Hijau" />
                        </x-filament::input.wrapper>
                        @error('judul') <p style="color:#dc2626; font-size:0.8125rem; margin-top:0.25rem;">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label style="font-size:0.875rem; font-weight:600; display:block; margin-bottom:0.375rem;">Kategori</label>
                        <x-filament::input.wrapper :error="$errors->has('kategori')">
                            <x-filament::input.select wire:model="kategori">
                                <option value="">-- Pilih kategori --</option>
                                @foreach($kategoriList as $kat)
                                    <option value="{{ $kat }}">{{ $kat }}</option>
                                @endforeach
                            </x-filament::input.select>
                        </x-filament::input.wrapper>
                        @error('kategori') <p style="color:#dc2626; font-size:0.8125rem; margin-top:0.25rem;">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label style="font-size:0.875rem; font-weight:600; display:block; margin-bottom:0.375rem;">Ringkasan (maks. 300 karakter)</label>
                    <x-filament::input.wrapper :error="$errors->has('ringkasan')">
                        <x-filament::input type="text" wire:model="ringkasan" placeholder="Satu kalimat penjelasan singkat" />
                    </x-filament::input.wrapper>
                    @error('ringkasan') <p style="color:#dc2626; font-size:0.8125rem; margin-top:0.25rem;">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label style="font-size:0.875rem; font-weight:600; display:block; margin-bottom:0.375rem;">Isi Artikel</label>
                    <textarea
                        wire:model="isi"
                        rows="7"
                        placeholder="Jelaskan gejala, penyebab, dan cara penanganan. Gunakan baris baru untuk poin."
                        style="width:100%; border-radius:0.5rem; border:1px solid {{ $errors->has('isi') ? '#dc2626' : '#d1d5db' }}; padding:0.625rem; font-size:0.875rem;"
                    ></textarea>
                    @error('isi') <p style="color:#dc2626; font-size:0.8125rem; margin-top:0.25rem;">{{ $message }}</p> @enderror
                </div>

                <div style="display:flex; justify-content:flex-end; gap:0.5rem;">
                    <x-filament::button type="button" color="gray" wire:click="batal">Batal</x-filament::button>
                    <x-filament::button type="submit" color="success" wire:loading.attr="disabled" wire:target="simpan">
                        {{ $editId ? 'Simpan Perubahan' : 'Simpan Entri' }}
                    </x-filament::button>
                </div>
            </form>
        </x-filament::section>
    @endif

    {{-- DAFTAR ENTRI --}}
    <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(320px, 1fr)); gap:1rem;">
        @forelse($entri as $item)
            @php
                $warna = match($item->kategori) {
                    'Tanaman'         => ['#dcfce7', '#166534'],
                    'Hama'            => ['#fee2e2', '#991b1b'],
                    'Penyakit'        => ['#fef3c7', '#92400e'],
                    'Pupuk & Tanah'   => ['#e0f2fe', '#075985'],
                    default           => ['#ede9fe', '#5b21b6'],
                };
                $milikSaya = $item->id_petani !== null && $item->id_petani === $idSaya;
            @endphp

            <div style="background:#fff; border:1px solid #e5e7eb; border-radius:0.75rem; padding:1rem; display:flex; flex-direction:column; gap:0.625rem;">
                <div style="display:flex; justify-content:space-between; align-items:center; gap:0.5rem;">
                    <div style="display:flex; align-items:center; gap:0.375rem; flex-wrap:wrap;">
                        <span style="background:{{ $warna[0] }}; color:{{ $warna[1] }}; font-size:0.75rem; font-weight:600; padding:0.125rem 0.625rem; border-radius:999px;">
                            {{ $item->kategori }}
                        </span>
                        <span style="background:#fef9c3; color:#854d0e; font-size:0.75rem; font-weight:600; padding:0.125rem 0.625rem; border-radius:999px;">
                            Baru 
                        </span>
                        <span style="background:#fef9c3; color:#854d0e; font-size:0.75rem; font-weight:600; padding:0.125rem 0.625rem; border-radius:999px;">
                            Baru banget
                        </span>
                    </div>
                    @if($milikSaya)
                        <span style="font-size:0.75rem; color:#6b7280;">Entri saya</span>
                    @elseif($item->id_petani === null)
                        <span style="font-size:0.75rem; color:#6b7280;">Bawaan sistem</span>
                    @endif
                </div>

                <h3 style="font-size:1rem; font-weight:700; margin:0;">{{ $item->judul }}</h3>
                <p style="font-size:0.875rem; color:#4b5563; margin:0;">{{ $item->ringkasan }}</p>

                @if($terbuka === $item->id_ensiklopedia)
                    <div style="font-size:0.875rem; color:#374151; white-space:pre-line; border-top:1px solid #f3f4f6; padding-top:0.625rem;">{{ $item->isi }}</div>
                @endif

                <div style="display:flex; gap:0.5rem; flex-wrap:wrap; margin-top:auto;">
                    <x-filament::button type="button" size="xs" color="success" outlined wire:click="toggle('{{ $item->id_ensiklopedia }}')">
                        {{ $terbuka === $item->id_ensiklopedia ? 'Tutup' : 'Baca selengkapnya' }}
                    </x-filament::button>

                    @if($milikSaya)
                        <x-filament::button type="button" size="xs" color="gray" wire:click="edit('{{ $item->id_ensiklopedia }}')">Edit</x-filament::button>
                        <x-filament::button type="button" size="xs" color="danger"
                            wire:click="hapus('{{ $item->id_ensiklopedia }}')"
                            wire:confirm="Hapus entri ini?">Hapus</x-filament::button>
                    @endif
                </div>
            </div>
        @empty
            <div style="grid-column:1/-1; text-align:center; color:#6b7280; padding:2rem;">
                Tidak ada entri yang cocok.
            </div>
        @endforelse
    </div>
</x-filament-panels::page>