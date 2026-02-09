<?php

namespace App\Http\Controllers;

use App\Models\Maintenances;
use App\Models\MaintenancesDetails;
use App\Models\Note;
use App\Models\Record;
use App\Models\TypeMaintenances;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Yajra\DataTables\DataTables;

class MaintenancesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');
        $status = $request->query('status');
        $searchValue = $request->query('search_value');

        Carbon::setLocale('id');

        $query = Maintenances::query();

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('date_in', [$startDate, $endDate]);
        }

        if ($request->filled('status')) {
            $query->where('status', $status);
        }

        if ($searchValue) {
            $query->where(function ($q) use ($searchValue) {
                $q->where('no_pol', 'like', '%'.$searchValue.'%');
            });
        }

        if ($request->ajax()) {
            try {
                return DataTables::of($query)
                    ->addIndexColumn()
                    ->addColumn('action', function ($row) {
                        $showRoute = route('maintenances.show', $row->code_mt);

                        $btn = '<a href="'.$showRoute.'" class="btn btn-primary btn-sm d-flex align-items-center justify-content-center" title="Lihat">
                                    <i class="bx bx-show"></i>
                                </a>';

                        return $btn;
                    })
                    ->addColumn('status', function ($row) {
                        switch ($row->status) {
                            case 'DRAFT':
                                return '<span class="badge bg-secondary">DRAFT</span>';
                            case 'IN_PROGRESS':
                                return '<span class="badge bg-warning">SEDANG DIKERJAKAN</span>';
                            case 'COMPLETED':
                                return '<span class="badge bg-success">SELESAI</span>';
                            case 'CANCELLED':
                                return '<span class="badge bg-danger">DIBATALKAN</span>';
                            default:
                                return '<span class="badge bg-secondary">UNKNOWN</span>';
                        }
                    })
                    ->editColumn('date_in', function ($row) {
                        return Carbon::parse($row->date_in)->translatedFormat('d F Y');
                    })
                    ->editColumn('date_out', function ($row) {
                        return $row->date_out ? Carbon::parse($row->date_out)->translatedFormat('d F Y') : 'belum selesai';
                    })
                    ->rawColumns(['action', 'status'])
                    ->make(true);
            } catch (\Exception $e) {
                Log::error('DataTables error: '.$e->getMessage());

                return response()->json(['error' => 'An error occurred while processing your request.'], 500);
            }
        }

        $data = [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'status' => $status,
            'pageTitle' => 'Data Pemeliharaan ',
        ];

        return view('pages.maintenances.index', $data);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $data = [
            'vehicle' => Vehicle::where('status', 'ACTIVE')->where('mtc', 'NO')->get(),
            'pageTitle' => 'Tambah Data Perbaikan',
            'type' => TypeMaintenances::all(),
        ];

        return view('pages.maintenances.create_or_edit', $data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Check if saving as draft
        $saveAsDraft = $request->has('save_as_draft');

        // Validation rules differ for draft vs full save
        // With FilePond, fields[] contains temp file paths (strings), not file uploads
        $rules = [
            'no_pol' => 'required',
            'date_in' => 'required',
            'mileage' => 'required',
            'total_amount' => 'nullable',
            'isServiceRoutine' => 'required',
            'desc' => 'nullable',
            'fields' => $saveAsDraft ? 'nullable|array' : 'required|array|min:1',
            'fields.*' => 'nullable|string', // FilePond sends temp file paths as strings
        ];

        $request->validate($rules, [
            'fields.required' => 'Bukti/nota perbaikan wajib diupload',
            'fields.min' => 'Minimal 1 bukti harus diupload',
        ]);

        $maintenances = Maintenances::create([
            'code_mt' => $this->generateCodeMt(),
            'no_pol' => Str::upper($request->no_pol),
            'date_in' => $request->date_in,
            'date_out' => $request->date_out,
            'mileage' => $request->mileage,
            'description' => $request->desc,
            'total_amount' => 0,
            'isServiceRoutine' => $request->isServiceRoutine,
            'status' => $saveAsDraft ? 'DRAFT' : 'COMPLETED',
            'get_request' => auth()->user()->id,
            'requested_by_name' => auth()->user()->name,
        ]);

        // Handle FilePond temp files - move from local temp to S3
        if ($request->has('fields') && is_array($request->fields)) {
            foreach ($request->fields as $tempPath) {
                // Skip empty values
                if (empty($tempPath)) {
                    continue;
                }

                // Validate that the temp path exists
                if (! Storage::disk('local')->exists($tempPath)) {
                    continue;
                }

                // Get the file extension from the temp path
                $extension = pathinfo($tempPath, PATHINFO_EXTENSION);
                $filename = 'maintenances/'.$maintenances->code_mt.'-'.Str::random(10).'.'.$extension;

                // Get the file content from local storage
                $fileContent = Storage::disk('local')->get($tempPath);

                // Upload to S3 (already compressed by FileUploadController)
                Storage::disk('s3')->put($filename, $fileContent);

                // Get the URL from S3
                $fileUrl = Storage::disk('s3')->url($filename);

                // Create the note record
                Note::create([
                    'maintenances_id' => $maintenances->id,
                    'image' => $filename,
                    'url' => $fileUrl,
                ]);

                // Delete the temp file from local storage
                Storage::disk('local')->delete($tempPath);

                // Try to delete the temp directory if empty
                $dir = dirname($tempPath);
                if (Storage::disk('local')->exists($dir)) {
                    $files = Storage::disk('local')->files($dir);
                    if (empty($files)) {
                        Storage::disk('local')->deleteDirectory($dir);
                    }
                }
            }
        }

        // Handle repeater items - create MaintenancesDetails records
        if ($request->has('items') && is_array($request->items)) {
            foreach ($request->items as $item) {
                // Skip empty items
                if (empty($item['maintenance_name'])) {
                    continue;
                }

                $price = (int) ($item['price'] ?? 0);
                $qty = (int) ($item['qty'] ?? 1);

                MaintenancesDetails::create([
                    'maintenance_id' => $maintenances->id,
                    'maintenance_name' => $item['maintenance_name'],
                    'price' => $price,
                    'qty' => $qty,
                    'subtotal' => $price * $qty,
                    'material' => $item['material'] ?? null,
                ]);
            }

            // Recalculate total
            $maintenances->recalculateTotal();
        }

        $message = $saveAsDraft ? 'Berhasil disimpan sebagai draft' : 'Berhasil disimpan';

        return redirect('/maintenances/'.$maintenances->code_mt)->with('success', $message);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $maintenances = Maintenances::where('code_mt', $id)->first();
        if (! $maintenances) {
            abort(404);
        }
        if (
            $maintenances->status === 'COMPLETED'
            && ! $maintenances->is_confirmed
            && $maintenances->viewed_at === null
        ) {
            $maintenances->update([
                'viewed_at' => now(),
                'viewed_by' => auth()->id(),
            ]);
        }
        $data = [
            'maintenances' => $maintenances,
            'notes' => Note::where('maintenances_id', $maintenances->id)->get(),
            'detail_maintenances' => MaintenancesDetails::where('maintenance_id', $maintenances?->id)->get(),
            'sum_detail_maintenances' => MaintenancesDetails::where('maintenance_id', $maintenances?->id)->sum('subtotal'),
            'vehicle' => Vehicle::where('status', 'ACTIVE')->get(),
            'pageTitle' => 'Tambah Data Perbaikan',
            'type' => TypeMaintenances::all(),
        ];

        return view('pages.maintenances.create_or_edit', $data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'no_pol' => 'required',
            'date_in' => 'required',
            'date_out' => 'nullable',
            'mileage' => 'required',
            'total_amount' => 'nullable',
            'isServiceRoutine' => 'required',
            'desc' => 'nullable',
            'fields' => 'nullable|array',
            'fields.*' => 'nullable|string', // FilePond sends temp file paths as strings
        ]);

        $maintenances = Maintenances::find($id);
        $maintenances->update([
            'code_mt' => $maintenances->code_mt,
            'no_pol' => Str::upper($request->no_pol),
            'date_in' => $request->date_in,
            'date_out' => $request->date_out,
            'mileage' => $request->mileage,
            'description' => $request->desc,
            'total_amount' => $request->total_amount,
            'isServiceRoutine' => $request->isServiceRoutine,
            'get_request' => auth()->user()->id,
            'requested_by_name' => auth()->user()->name,
        ]);

        // Handle FilePond temp files - move from local temp to S3
        if ($request->has('fields') && is_array($request->fields)) {
            foreach ($request->fields as $tempPath) {
                // Skip empty values
                if (empty($tempPath)) {
                    continue;
                }

                // Validate that the temp path exists
                if (! Storage::disk('local')->exists($tempPath)) {
                    continue;
                }

                // Get the file extension from the temp path
                $extension = pathinfo($tempPath, PATHINFO_EXTENSION);
                $filename = 'maintenances/'.$maintenances->code_mt.'-'.Str::random(10).'.'.$extension;

                // Get the file content from local storage
                $fileContent = Storage::disk('local')->get($tempPath);

                // Upload to S3 (already compressed by FileUploadController)
                Storage::disk('s3')->put($filename, $fileContent);

                // Get the URL from S3
                $fileUrl = Storage::disk('s3')->url($filename);

                // Create the note record
                Note::create([
                    'maintenances_id' => $maintenances->id,
                    'image' => $filename,
                    'url' => $fileUrl,
                ]);

                // Delete the temp file from local storage
                Storage::disk('local')->delete($tempPath);

                // Try to delete the temp directory if empty
                $dir = dirname($tempPath);
                if (Storage::disk('local')->exists($dir)) {
                    $files = Storage::disk('local')->files($dir);
                    if (empty($files)) {
                        Storage::disk('local')->deleteDirectory($dir);
                    }
                }
            }
        }

        // Handle repeater items - create MaintenancesDetails records
        if ($request->has('items') && is_array($request->items)) {
            foreach ($request->items as $item) {
                // Skip empty items
                if (empty($item['maintenance_name'])) {
                    continue;
                }

                $price = (int) ($item['price'] ?? 0);
                $qty = (int) ($item['qty'] ?? 1);

                MaintenancesDetails::create([
                    'maintenance_id' => $maintenances->id,
                    'maintenance_name' => $item['maintenance_name'],
                    'price' => $price,
                    'qty' => $qty,
                    'subtotal' => $price * $qty,
                    'material' => $item['material'] ?? null,
                ]);
            }

            // Recalculate total
            $maintenances->recalculateTotal();
        }

        return redirect()->back()->with('success', 'Berhasil diperbarui');
    }

    // Status change method - simplified workflow
    public function status(Request $request, $id)
    {
        $maintenances = Maintenances::find($id);
        $detailMaintenances = MaintenancesDetails::where('maintenance_id', $id)->get();
        $vehicle = Vehicle::getByNoPol($maintenances->no_pol);

        if ($request->status == 'DRAFT') {
            $vehicle->update(['mtc' => 'NO']);
            $status = 'DRAFT';
        } elseif ($request->status == 'IN_PROGRESS') {
            $vehicle->update(['mtc' => 'YES']);
            $status = 'SEDANG DIKERJAKAN';
        } elseif ($request->status == 'COMPLETED') {
            // Validation: check if details exist
            if ($detailMaintenances->count() == 0) {
                return redirect()->back()->with('error', 'Detail perbaikan belum dimasukkan.');
            }

            // Mark as viewed
            $maintenances->update([
                'viewed_at' => now(),
                'viewed_by' => auth()->user()->id,
            ]);

            $vehicle->update(['mtc' => 'NO']);
            $status = 'SELESAI';
        } else { // CANCELLED
            $vehicle->update(['mtc' => 'NO']);
            $status = 'DIBATALKAN';
        }

        $maintenances->update(['status' => $request->status]);

        return redirect()
            ->route('maintenances.index')
            ->with('success', 'Status berhasil diubah ke '.$status);
    }

    /**
     * Confirm a completed maintenance record
     */
    public function confirm(Request $request, $id)
    {
        $maintenance = Maintenances::findOrFail($id);

        // Only allow confirmation for completed maintenance
        if ($maintenance->status !== 'COMPLETED') {
            return redirect()->back()->with('error', 'Hanya pemeliharaan yang selesai yang dapat dikonfirmasi.');
        }

        $validated = $request->validate([
            'confirmation_notes' => 'nullable|string|max:1000',
        ]);

        $maintenance->update([
            'is_confirmed' => true,
            'confirmed_at' => now(),
            'confirmed_by' => auth()->user()->id,
            'confirmation_notes' => $maintenance->confirmation_notes ?? ($validated['confirmation_notes'] ?? null),
        ]);

        return redirect()->back()->with('success', 'Pemeliharaan berhasil dikonfirmasi.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Maintenances $maintanances)
    {
        //
    }

    private function generateCodeMt(): string
    {
        return 'MT-'.now()->timestamp;
    }
}
