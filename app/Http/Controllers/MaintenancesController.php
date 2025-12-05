<?php

namespace App\Http\Controllers;

use App\Models\Note;
use App\Models\Record;
use App\Models\Vehicle;
use Illuminate\Support\Str;
use App\Models\Maintenances;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Models\TypeMaintenances;
use Yajra\DataTables\DataTables;
use App\Models\MaintenancesDetails;
use Illuminate\Support\Facades\Log;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class MaintenancesController extends Controller
{

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $startDate = $request->query('start_date') ?? Carbon::now()->startOfMonth()->toDateString();
        $endDate = $request->query('end_date') ?? Carbon::now()->endOfMonth()->toDateString();
        $status = $request->query('status');
        $searchValue = $request->query('search_value');

        Carbon::setLocale('id');

        $query = Maintenances::query();

        if ($request->query('start_date') && $request->query('end_date') && $status) {
            $query->whereDate('created_at', '>=', $startDate)->whereDate('created_at', '<=', $endDate)->where('status', $status);
        } else {
            $query->where('status', 'NEW')->where('get_request', auth()->user()->id);
        }

        if ($searchValue) {
            $query->where(function ($q) use ($searchValue) {
                $q->where('no_pol', 'like', '%' . $searchValue . '%');
            });
        }

        if ($request->ajax()) {
            try {
                return DataTables::of($query)
                    ->addIndexColumn()
                    ->addColumn('action', function ($row) {
                        $showRoute = route('maintenances.show', $row->code_mt);

                        $btn = '<a href="' . $showRoute . '" class="btn btn-info btn-sm"><i class="fas fa-fw fa-eye"></i></a>';

                        return $btn;
                    })
                    ->addColumn('status', function ($row) {
                        switch ($row->status) {
                            case 'NEW':
                                return '<span class="badge bg-secondary">BELUM SELESAI</span>';
                            case 'PENDING':
                                return '<span class="badge bg-warning">BELUM DISETUJUI</span>';
                            case 'APPROVED':
                                return '<span class="badge bg-success">SELESAI</span>';
                            default:
                                return '<span class="badge bg-danger">BATAL</span>';
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
                Log::error('DataTables error: ' . $e->getMessage());
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
        $request->validate([
            'no_pol' => 'required',
            'date_in' => 'required',
            'mileage' => 'required',
            'total_amount' => 'nullable',
            'isServiceRoutine' => 'required',
            'desc' => 'nullable',
            'fields.*' => 'nullable',
        ]);

        $maintenances = Maintenances::create([
            'code_mt' => $this->generateCodeMt(),
            'no_pol' => Str::upper($request->no_pol),
            'date_in' => $request->date_in,
            'date_out' => $request->date_out,
            'mileage' => $request->mileage,
            'description' => $request->desc,
            'total_amount' => $request->total_amount ?? '0',
            'isServiceRoutine' => $request->isServiceRoutine,
            'status' => 'NEW',
            'get_request' => auth()->user()->id,
            'requested_by_name' => auth()->user()->name,
        ]);

        if ($request->hasFile('fields')) {
            $files = $request->file('fields');
            foreach ($files as $file) {
                $filename = $maintenances->code_mt . '-' . Str::random(10) . '.' . $file->getClientOriginalExtension();
                $destinationPath = public_path('notes');

                if (!File::exists($destinationPath)) {
                    File::makeDirectory($destinationPath, 0755, true);
                }

                if (in_array($file->getClientOriginalExtension(), ['jpeg', 'jpg', 'png', 'gif'])) {
                    $tempPath = $destinationPath . '/' . $filename;
                    $file->move($destinationPath, $filename);

                    $image = Image::make($tempPath);
                    $image->resize(800, 800, function ($constraint) {
                        $constraint->aspectRatio();
                    });

                    $image->save($tempPath);
                } else {
                    $file->move($destinationPath, $filename);
                }

                $finalFilename = 'notes/' . $filename;
                $fileUrl = url($finalFilename);

                Note::create([
                    'maintenances_id' => $maintenances->id,
                    'image' => $filename,
                    'url' => $fileUrl,
                ]);
            }
        }
        return redirect('/maintenances/' . $maintenances->code_mt)->with('success', 'Berhasil disimpan');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $maintenances = Maintenances::where('code_mt', $id)->first();
        if (!$maintenances) {
            abort(404);
        }
        $data = [
            'maintenances' => $maintenances,
            'notes' => Note::where('maintenances_id', $maintenances->id)->get(),
            'detail_maintenances' => MaintenancesDetails::where('maintenance_id', $maintenances?->id)->get(),
            'sum_detail_maintenances' => MaintenancesDetails::where('maintenance_id', $maintenances?->id)->sum('price'),
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
            'fields.*' => 'nullable|max:3048',
        ], [
             'fields.max' => 'Bukti tidak boleh melebihi 3 MB'
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

        if ($request->hasFile('fields')) {
            $files = $request->file('fields');
            foreach ($files as $file) {
                $filename = $maintenances->code_mt . '-' . Str::random(10) . '.' . $file->getClientOriginalExtension();
                $destinationPath = public_path('notes');

                if (!File::exists($destinationPath)) {
                    File::makeDirectory($destinationPath, 0755, true);
                }

                if (in_array($file->getClientOriginalExtension(), ['jpeg', 'jpg', 'png', 'gif'])) {
                    $tempPath = $destinationPath . '/' . $filename;
                    $file->move($destinationPath, $filename);

                    $image = Image::make($tempPath);
                    $image->resize(800, 800, function ($constraint) {
                        $constraint->aspectRatio();
                    });

                    $image->save($tempPath);
                } else {
                    $file->move($destinationPath, $filename);
                }

                $finalFilename = 'notes/' . $filename;
                $fileUrl = url($finalFilename);

                Note::create([
                    'maintenances_id' => $maintenances->id,
                    'image' => $filename,
                    'url' => $fileUrl,
                ]);
            }
        }
        return redirect()->back()->with('success', 'Berhasil diperbarui');
    }

    // panding
    public function status(Request $request, $id)
    {
        $maintenances = Maintenances::find($id);
        $detailMaintenances = MaintenancesDetails::where('maintenance_id', $id)->get();
        $vehicle = Vehicle::getByNoPol($maintenances->no_pol);

        if ($request->status == 'NEW') {
            $vehicle->update([
                'mtc' => 'NO',
            ]);
            $status = 'BELUM SELESAI';
        } elseif ($request->status == 'PENDING') {

            if ($maintenances->total_amount == 0) {
                return redirect()->back()->with('error', 'Total Harga tidak boleh kosong.');
            } elseif ($maintenances->date_out == null) {
                return redirect()->back()->with('error', 'Tanggal Keluar tidak boleh kosong.');
            }

            if ($detailMaintenances->count() == 0) {
                return redirect()->back()->with('error', 'Detail perbaikan belum dimasukan.');
            }

            $vehicle->update([
                'mtc' => 'YES',
            ]);
            $status = 'PROSES PERSETUJUAN';
        } elseif ($request->status == 'APPROVED') {
            $vehicle->update([
                'mtc' => 'NO',
            ]);
            $status = 'SUDAH DISETUJUI';
        } else {
            $vehicle->update([
                'mtc' => 'NO',
            ]);
            $status = 'DIBATALKAN';
        }

        if ($request->status == 'APPROVED') {
            $maintenances->update([
                'status' => $request->status,
                'get_approval' => auth()->user()->id,
                'approved_by_name' => auth()->user()->name,
            ]);
        }

        $maintenances->update([
            'status' => $request->status,
        ]);
        return redirect()
            ->route('maintenances.index')
            ->with('success', 'Status Berhasil Diubah Ke ' . Str::title($status));
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
        return 'MT-' . now()->timestamp;
    }
}
