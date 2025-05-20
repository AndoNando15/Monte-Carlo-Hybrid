<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dataset extends Model
{
    // Menentukan nama tabel yang digunakan
    protected $table = 'dataset';

    // Menentukan kolom mana yang bisa diisi (fillable)
    protected $fillable = [

        'tanggal',
        'hari',
        'datang',
        'berangkat',
    ];

    // Jika Anda tidak ingin timestamps otomatis disertakan, Anda bisa menambahkan properti ini
    public $timestamps = true; // default true, bisa diatur false jika tidak diperlukan
}