<?php

namespace App\Http\Controllers;
use App\Models\Dataset;

use Illuminate\Http\Request;

class DashboardController extends Controller
{


    public function index()
    {
        $totalPenerbangan = Dataset::count(); // Total data (baris)
        $totalDatang = Dataset::sum('datang'); // Total jumlah datang
        $totalBerangkat = Dataset::sum('berangkat'); // Total jumlah berangkat

        return view('pages.dashboard.index', compact('totalPenerbangan', 'totalDatang', 'totalBerangkat'));
    }
}