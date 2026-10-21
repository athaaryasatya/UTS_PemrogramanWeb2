<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ensiklopedia extends Model
{
    use HasUuids;

    public const KATEGORI = ['Tanaman', 'Hama', 'Penyakit', 'Pupuk & Tanah', 'Teknik Budidaya'];

    protected $table = 'ensiklopedia';
    protected $primaryKey = 'id_ensiklopedia';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['id_petani', 'judul', 'kategori', 'ringkasan', 'isi'];

    public function petani(): BelongsTo
    {
        return $this->belongsTo(Petani::class, 'id_petani', 'id_petani');
    }
}