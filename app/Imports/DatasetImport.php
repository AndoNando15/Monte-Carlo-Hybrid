<?php
namespace App\Imports;

use App\Models\Dataset;
use Maatwebsite\Excel\Concerns\ToModel;
use Carbon\Carbon;

class DatasetImport implements ToModel
{

    public function model(array $row)
    {
        // Cek apakah kolom pertama (Tanggal) adalah angka (serial date Excel)
        if (is_numeric($row[0])) {
            // Jika ya, konversi angka serial Excel menjadi tanggal
            $carbonDate = Carbon::createFromFormat('Y-m-d', Carbon::createFromFormat('Y', '1900')->addDays($row[0] - 2)->toDateString())->locale('id');
        } else {
            // Jika Tanggal sudah dalam format teks, coba parsing langsung
            $carbonDate = Carbon::parse($row[0])->locale('id');
        }

        return new Dataset([
            'tanggal' => $carbonDate->format('Y-m-d'),  // Menyimpan dalam format 'YYYY-MM-DD'
            'hari' => $row[1],  // Kolom hari dalam bahasa Indonesia
            'datang' => $row[2],  // Kolom Datang
            'berangkat' => $row[3],  // Kolom Berangkat
        ]);
    }
}