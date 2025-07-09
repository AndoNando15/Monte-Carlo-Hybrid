<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AkurasiMape extends Model
{
    use HasFactory;
    protected $table = 'akurasi_mape_results';

    // Menentukan kolom yang bisa diisi
    protected $fillable = [
        'monte_akurasi_datang',
        'monte_mape_datang',
        'monte_akurasi_berangkat',
        'monte_mape_berangkat',
        'tes_akurasi_datang',
        'tes_mape_datang',
        'tes_akurasi_berangkat',
        'tes_mape_berangkat',
    ];
}