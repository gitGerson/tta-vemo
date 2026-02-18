<?php

namespace App\Http\Controllers;

use App\Exports\RecordExport;
use App\Models\Company;
use App\Models\Destination;
use App\Models\Driver;
use App\Models\Maintenances;
use App\Models\NotificationService;
use App\Models\Record;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\DataTables;

class RecordController extends Controller
{
    public function index(Request $request)
    {
        if ($request->has('per_page')) {
            session(['per_page' => $request->query('per_page')]);
        }

        $perPage = session('per_page', 100);

        $startQuery = $request->query('start_date');
        $endQuery = $request->query('end_date');
        $companyQuery = $request->query('company');

        $startDate = $startQuery ? $startQuery : Carbon::now()->startOfMonth()->toDateString();
        $endDate = $endQuery ? $endQuery : Carbon::now()->endOfMonth()->toDateString();

        Carbon::setLocale('id');

        $query = Record::with(['company'])
            ->whereBetween('date', [Carbon::now()->subMonths(3)->startOfMonth(), Carbon::now()->endOfMonth()])
            ->when($companyQuery, function ($q) use ($companyQuery) {
                return $q->where('company_id', $companyQuery);
            })
            ->orderBy('created_at', 'desc') // Use orderBy instead of sortByDesc
            ->paginate($perPage);

        $data = [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'company' => Company::all(),
            'pageTitle' => 'Data Perjalanan',
            'record' => $query,
        ];

        return view('pages.record.index', $data);
    }

    public function create()
    {
        $data = [
            'driver' => Driver::where('status', 'ACTIVE')->get(),
            'vehicles' => Vehicle::where('status', 'ACTIVE')->get(),
            'destination' => Destination::orderBy('id', 'desc')->get(),
            'company' => Company::orderBy('id', 'desc')->get(),
            'pageTitle' => 'Tambah Data Perjalanan',
        ];

        return view('pages.record.create', $data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'company_id' => 'required|exists:company,id',
            'date' => 'required|date',
            'driver_1' => 'required|exists:drivers,id',
            'driver_2' => 'nullable',
            'no_pol' => 'required|exists:vehicles,plat_new',
            'destination' => 'required|exists:destinations,destination',
            'start_km' => 'nullable|string',
            'finish_km' => 'required|string',
            'bbm' => 'required|string',
            'idr' => 'required|string',
        ]);

        if ($request->bbm < 0 || $request->idr < 0) {
            return response()->json(
                [
                    'error' => 404,
                    'message' => 'Nilai tidak boleh lebih kecil dari 0.',
                ],
                400,
            );
        }

        $driver_1 = Driver::find($request?->driver_1);
        $driver_2 = Driver::find($request?->driver_2);
        $vehicle = Vehicle::where('plat_new', $request?->no_pol)->first();

        // handle start km
        if ($request->start_km) {
            $start_mileage = $request->start_km;
        } else {
            $record = Record::where('no_pol', $request->no_pol)
                ->latest()
                ->first();
            if ($record) {
                $start_mileage = $record->finish_km;
            } else {
                $start_mileage = 0;
            }
        }

        $total = $request->finish_km - $start_mileage;
        $scale = $total / $request->bbm;
        $scale_formatted = number_format($scale);
        $replacedScale = Str::replace(',', '.', $scale_formatted);

        Record::create([
            'driver_1' => $driver_1?->name,
            'driver_2' => $driver_2?->name,
            'vehicle_id' => $vehicle?->id,
            'no_pol' => $request->no_pol,
            'destination' => $request->destination,
            'company_id' => $request->company_id,
            'start_km' => Str::of($start_mileage)->remove('-'),
            'finish_km' => Str::of($request->finish_km)->remove('-'),
            'bbm' => $request->bbm,
            'idr' => Str::of($request->idr)->remove('-'),
            'totals' => $total,
            'scale' => $replacedScale,
            'date' => $request->date,
        ]);

        // handle finish km lebih gede
        if ($request->finish_km > $vehicle->mileage) {
            $finish_mileage = $request->finish_km;
        } else {
            $finish_mileage = $vehicle->mileage;
        }

        $vehicle->update([
            'mileage' => Str::of($finish_mileage)->remove('-'),
        ]);

        $maintenances = Maintenances::where('no_pol', $vehicle->plat_new)
            ->orwhere('no_pol', $vehicle->plat_old)
            ->where('isServiceRoutine', '1')
            ->orderBy('created_at', 'desc')
            ->first();

        // Check for service reminder notification (event-based)
        if ($maintenances && $maintenances->status == 'COMPLETED') {
            $currentMileage = $request->finish_km;
            $estimatedMileage = 10000; // interval 10.000km
            $mileageDifference = $currentMileage - $maintenances->mileage;

            if ($mileageDifference >= $estimatedMileage) {
                // Check if notification already exists for this range
                $notification = NotificationService::where('no_pol', $maintenances->no_pol)
                    ->where('read', 0)
                    ->where('from_km', $maintenances->mileage)
                    ->first();

                if (! $notification) {
                    NotificationService::create([
                        'no_pol' => $maintenances->no_pol,
                        'read' => 0,
                        'from_km' => $maintenances->mileage,
                        'to_km' => $currentMileage,
                    ]);

                    // Informational reminder instead of warning
                    return response()->json(
                        [
                            'success' => 200,
                            'message' => 'Catatan perjalanan berhasil dibuat. <span class="text-info">ℹ️ Pengingat: Kendaraan '.$vehicle->plat_new.' sudah menempuh '.number_format($mileageDifference).' km sejak servis terakhir (servis di km '.number_format($maintenances->mileage).'). Pertimbangkan untuk melakukan servis rutin.</span>',
                        ],
                        200,
                    );
                }

                if ((int) $notification->to_km < (int) $currentMileage) {
                    $notification->update(['to_km' => $currentMileage]);
                }
            }
        }

        return response()->json(
            [
                'success' => 200,
                'message' => 'Data catatan perjalanan berhasil dibuat.',
            ],
            200,
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(Record $record)
    {
        $data = [
            'pageTitle' => 'Detail Data Perjalanan',
            'record' => $record,
        ];

        return view('pages.record.show', $data);
    }

    public function edit(Record $record)
    {
        $data = [
            'record' => $record,
            'pageTitle' => 'Edit Data Perjalanan',
            'driver' => Driver::where('status', 'ACTIVE')->get(),
            'vehicles' => Vehicle::where('status', 'ACTIVE')->get(),
            'destination' => Destination::orderBy('id', 'desc')->get(),
            'company' => Company::orderBy('id', 'desc')->get(),
        ];

        return view('pages.record.edit', $data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'driver_1' => 'required',
            'driver_2' => 'nullable',
            'no_pol' => 'required|exists:vehicles,plat_new',
            'destination' => 'required|exists:destinations,destination',
            'company_id' => 'required|exists:company,id',
            'start_km' => 'required|string',
            'finish_km' => 'required|string',
            'bbm' => 'required|string',
            'idr' => 'required|string',
            'date' => 'required|date',
        ]);

        if ($request->start_km < 0 || $request->finish_km < 0 || $request->bbm < 0 || $request->idr < 0) {
            return redirect()->back()->with('error', 'NIlai tidak boleh lebih kecil dari 0.')->withInput();
        }

        if (Str::of($request->start_km)->remove('-') > Str::of($request->finish_km)->remove('-')) {
            return redirect()->back()->with('error', 'NIlai km akhir terlalu kecil dari km awal')->withInput();
        }

        $total = $request->finish_km - $request->start_km;
        $scale = $total / $request->bbm;
        $scale_formatted = number_format($scale);
        $replacedScale = Str::replace(',', '.', $scale_formatted);

        $driver_1 = Driver::find($request?->driver_1);
        $driver_2 = Driver::find($request?->driver_2);
        $record = Record::find($id);
        $vehicle = Vehicle::where('plat_new', $request?->no_pol)->first();

        $record->update([
            'driver_1' => $driver_1?->name ?? $request?->driver_1,
            'driver_2' => $driver_2?->name ?? $request?->driver_2,
            'no_pol' => $request->no_pol,
            'destination' => $request->destination,
            'company_id' => $request->company_id,
            'start_km' => Str::of($request->start_km)->remove('-'),
            'finish_km' => Str::of($request->finish_km)->remove('-'),
            'bbm' => $request->bbm,
            'idr' => Str::of($request->idr)->remove('-'),
            'date' => $request->date,
            'totals' => $total,
            'scale' => $replacedScale,
        ]);

        $vehicle->update([
            'mileage' => Str::of($request->finish_km)->remove('-'),
        ]);

        return redirect()->route('record.index')->with('success', 'Catatan perjalanan berhasil diupdate.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $record = Record::find($id);
        $vehicle = Vehicle::where('plat_new', $record?->no_pol)->first();
        $vehicle->update([
            'mileage' => Str::of($record->start_km)->remove('-'),
        ]);
        $record->delete();

        return redirect()->back()->with('success', 'Catatan perjalanan berhasil dipindahkan disampah.');
    }

    public function record_trash(Request $request)
    {
        if ($request->ajax()) {
            $data = Record::onlyTrashed()->select('*');

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $btn = '';

                    if (auth()->user()->can('activate data nonaktif')) {
                        $btn .= '<button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#restoreDateModal" data-id="'.$row->id.'">Aktifkan</button> ';
                    }
                    if (auth()->user()->can('delete data nonaktif')) {
                        $btn .= '<button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#confirmDeleteModal" data-id="'.$row->id.'">Hapus</button>';
                    }

                    return $btn;
                })
                ->editColumn('date', function ($row) {
                    return Carbon::parse($row->date)->translatedFormat('d F Y');
                })
                ->editColumn('skala', function ($row) {
                    return $row->scale;
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        $datas = [
            'pageTitle' => 'Data  pelacakan BBM  nonaktif',
        ];

        return view('pages.record.trash', $datas);
    }

    public function restore(Request $request, $id)
    {
        $record = Record::withTrashed()->findOrFail($id);
        $record->restore();
        $record->save();

        return redirect()->route('trash.record')->with('success', ' pelacakan BBM  berhasil diaktifkan kembali.');
    }

    public function forceDeleteConfirmed($id)
    {
        $record = Record::withTrashed()->findOrFail($id);
        $record->forceDelete();

        return redirect()->route('trash.record')->with('success', ' pelacakan BBM  berhasil dihapus permanent.');
    }

    public function exportExcel(Request $request)
    {
        abort_if(! auth()->user()->can('view pelacakan bbm'), 403, 'Unauthorized.');
        $startDate = $request->query('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->query('end_date', Carbon::now()->endOfMonth()->toDateString());
        $companyId = $request->query('company');

        $fileName = 'Data_Perjalanan_'.now()->format('Ymd_His').'.xlsx';

        return Excel::download(new RecordExport($startDate, $endDate, $companyId), $fileName);
    }
}
