<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PengalokasianRuang extends Model
{
    use HasFactory;

    // Tabel yang digunakan oleh model ini
    protected $table = 'pengalokasianruang';

    public $incrementing = true;

    // Atribut yang bisa diisi (fillable)
    protected $fillable = [
        'kode_ruang',
        'id_programstudi',
        'status',
    ];

    public function ruangperkuliahan()
    {
        return $this->belongsTo(RuangPerkuliahan::class, 'kode_ruang', 'kode_ruang');
    }
    // Relasi dengan Program Studi
    public function programStudi()
    {
        return $this->belongsTo(ProgramStudi::class, 'id_programstudi', 'id_programstudi');
    }

}
