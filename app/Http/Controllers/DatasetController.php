<?php

namespace App\Http\Controllers;

use App\Models\Dataset;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DatasetController extends Controller
{
    // Display dataset
    public function index()
    {
        // Get datasets sorted by date
        $datasets = Dataset::orderBy('tanggal')->get();

        // Group datasets by month
        $groupedDatasets = $datasets->groupBy(function ($dataset) {
            return Carbon::parse($dataset->tanggal)->format('Y-m'); // Group by year and month (e.g., 2025-05)
        });

        // Format the date and day in Indonesian
        foreach ($datasets as $dataset) {
            $carbonDate = Carbon::parse($dataset->tanggal)->locale('id');
            $dataset->tanggal = $carbonDate->isoFormat('D MMMM YYYY'); // Format date to "19 Mei 2025"
            $dataset->hari = $carbonDate->isoFormat('dddd'); // Format day to "Senin", "Selasa", etc.
        }

        return view('pages.dataset.index', compact('groupedDatasets'));
    }


    // Store new dataset
    public function store(Request $request)
    {
        $carbonDate = Carbon::parse($request->tanggal)->locale('id');

        $dataset = new Dataset();
        $dataset->tanggal = $carbonDate->format('Y-m-d'); // Store in 'YYYY-MM-DD'
        $dataset->hari = $carbonDate->isoFormat('dddd');  // Store the day in Indonesian
        $dataset->datang = $request->datang;
        $dataset->berangkat = $request->berangkat;
        $dataset->save();

        return redirect()->route('dataset.index')->with('success', 'Dataset created successfully!');
    }

    public function update(Request $request, $id)
    {
        $dataset = Dataset::find($id);
        $carbonDate = Carbon::parse($request->tanggal)->locale('id');

        $dataset->tanggal = $carbonDate->format('Y-m-d'); // Store in 'YYYY-MM-DD'
        $dataset->hari = $carbonDate->isoFormat('dddd');  // Store the day in Indonesian
        $dataset->datang = $request->datang;
        $dataset->berangkat = $request->berangkat;
        $dataset->save();

        return redirect()->route('dataset.index')->with('success', 'Dataset updated successfully!');
    }

    public function destroy($id)
    {
        $dataset = Dataset::find($id);
        $dataset->delete();

        return redirect()->route('dataset.index')->with('success', 'Dataset deleted successfully!');
    }

}