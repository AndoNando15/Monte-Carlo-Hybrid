<?php

namespace App\Http\Controllers;

use App\Models\Dataset;
use Illuminate\Http\Request;

class DatasetController extends Controller
{
    // Display dataset
    public function index()
    {
        $datasets = Dataset::all();
        return view('pages.dataset.index', compact('datasets'));
    }

    // Store new dataset
    public function store(Request $request)
    {
        // Menyimpan data tanpa mengatur no_bulan secara manual, karena sudah dihitung di Blade
        $dataset = new Dataset();
        $dataset->tanggal = $request->tanggal;
        $dataset->hari = date('l', strtotime($request->tanggal)); // Menentukan hari berdasarkan tanggal
        $dataset->datang = $request->datang;
        $dataset->berangkat = $request->berangkat;
        $dataset->save();

        return redirect()->route('dataset.index')->with('success', 'Dataset created successfully!');
    }

    // Update dataset
    public function update(Request $request, $id)
    {
        $dataset = Dataset::find($id);
        $dataset->tanggal = $request->tanggal;
        $dataset->hari = date('l', strtotime($request->tanggal)); // Menentukan hari berdasarkan tanggal
        $dataset->datang = $request->datang;
        $dataset->berangkat = $request->berangkat;
        $dataset->save();

        return redirect()->route('dataset.index')->with('success', 'Dataset updated successfully!');
    }

    // Delete dataset
    public function destroy($id)
    {
        $dataset = Dataset::find($id);
        $dataset->delete();

        return redirect()->route('dataset.index')->with('success', 'Dataset deleted successfully!');
    }
}