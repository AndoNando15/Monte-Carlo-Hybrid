<?php

namespace App\Http\Controllers;
use App\Imports\DatasetImport;
use App\Models\Dataset;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;

class DatasetController extends Controller
{
    // Display dataset
    public function index(Request $request)
    {
        // Get the unique months from the 'tanggal' column
        $months = Dataset::selectRaw('DISTINCT DATE_FORMAT(tanggal, "%Y-%m") as month')->get();

        // Get the datasets based on the filter (if any)
        $datasetsQuery = Dataset::query();

        // Filter by month if provided
        if ($request->has('month') && $request->input('month') != '') {
            $monthFilter = $request->input('month');
            $datasetsQuery->whereMonth('tanggal', '=', Carbon::parse($monthFilter)->month)
                ->whereYear('tanggal', '=', Carbon::parse($monthFilter)->year);
        }

        // Fetch the datasets
        $datasets = $datasetsQuery->orderBy('tanggal')->get();

        // Group datasets by month
        $groupedDatasets = $datasets->groupBy(function ($dataset) {
            return Carbon::parse($dataset->tanggal)->format('Y-m');
        });

        // Format the date and day in Indonesian
        foreach ($datasets as $dataset) {
            $carbonDate = Carbon::parse($dataset->tanggal)->locale('id');
            $dataset->tanggal = $carbonDate->isoFormat('D MMMM YYYY');
            $dataset->hari = $carbonDate->isoFormat('dddd');
        }

        return view('pages.dataset.index', compact('groupedDatasets', 'months'));
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
    // Import Excel
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,csv',
        ]);
        Dataset::truncate();
        // Import the Excel file
        Excel::import(new DatasetImport, $request->file('file'));

        return redirect()->route('dataset.index')->with('success', 'Datasets imported successfully!');
    }

    // DatasetController

    public function deleteAll()
    {
        Dataset::truncate(); // This will delete all rows from the `datasets` table

        return redirect()->route('dataset.index')->with('success', 'All datasets deleted successfully!');
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