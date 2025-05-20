<?php

namespace App\Http\Controllers\MonteCarlo;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DatangController extends Controller
{
    public function index()
    {
        return view('pages.monte-carlo.datang.index');
    }
}