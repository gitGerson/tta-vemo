<?php

namespace App\Http\Controllers;

use App\DataTables\RecordDataTable;
use App\DataTables\RecordSnapshotDataTable;
use App\DataTables\RecordTrashDataTable;
use App\Exports\RecordExport;
use App\Http\Requests\RecordStoreRequest;
use App\Http\Requests\RecordUpdateRequest;
use App\Models\Company;
use App\Models\Destination;
use App\Models\Driver;
use App\Models\FuelType;
use App\Models\Record;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class RecordController extends Controller
{
    public function index(Request $request)
    {
        [$startDate, $endDate, $companyId] = $this->resolveFilters($request);

        return view('records.index', [
            'table' => new RecordDataTable($startDate, $endDate, $companyId),
            'companies' => Company::query()->orderBy('name')->get(['id', 'name', 'code']),
            'startDate' => $startDate,
            'endDate' => $endDate,
            'companyId' => $companyId,
        ]);
    }

    public function create()
    {
        return view('records.create', $this->formData());
    }

    public function store(RecordStoreRequest $request)
    {
        $data = $request->validated();

        $vehicle = Vehicle::where('plat_new', $data['no_pol'])->firstOrFail();
        $startKm = $this->resolveStartKm($data['no_pol'], $data['start_km'] ?? null);
        $totals = (int) $data['finish_km'] - (int) $startKm;

        Record::create([
            'driver_1' => Driver::find($data['driver_1'])?->name,
            'driver_2' => Driver::find($data['driver_2'] ?? null)?->name,
            'vehicle_id' => $vehicle->id,
            'no_pol' => $data['no_pol'],
            'destination' => $data['destination'],
            'company_id' => $data['company_id'],
            'fuel_type_id' => $data['fuel_type_id'],
            'start_km' => $startKm,
            'finish_km' => $data['finish_km'],
            'bbm' => $data['bbm'],
            'idr' => FuelType::find($data['fuel_type_id'])?->price ?? 0,
            'totals' => $totals,
            'scale' => $this->computeScale($totals, $data['bbm']),
            'date' => $data['date'],
        ]);

        $this->syncVehicleMileage($vehicle, max((int) $data['finish_km'], (int) $vehicle->mileage));

        return redirect()->route('records.index')->with('success', 'Catatan perjalanan berhasil dibuat.');
    }

    public function show(Record $record)
    {
        return view('records.show', [
            'record' => $record->load(['company', 'fuelType']),
            'snapshotTable' => new RecordSnapshotDataTable($record),
        ]);
    }

    public function edit(Record $record)
    {
        return view('records.edit', array_merge($this->formData(), ['record' => $record]));
    }

    public function update(Record $record, RecordUpdateRequest $request)
    {
        $data = $request->validated();

        $vehicle = Vehicle::where('plat_new', $data['no_pol'])->firstOrFail();
        $totals = (int) $data['finish_km'] - (int) $data['start_km'];

        $record->update([
            'driver_1' => Driver::find($data['driver_1'])?->name ?? $record->driver_1,
            'driver_2' => Driver::find($data['driver_2'] ?? null)?->name,
            'vehicle_id' => $vehicle->id,
            'no_pol' => $data['no_pol'],
            'destination' => $data['destination'],
            'company_id' => $data['company_id'],
            'fuel_type_id' => $data['fuel_type_id'],
            'start_km' => $data['start_km'],
            'finish_km' => $data['finish_km'],
            'bbm' => $data['bbm'],
            'idr' => FuelType::find($data['fuel_type_id'])?->price ?? $record->idr,
            'totals' => $totals,
            'scale' => $this->computeScale($totals, $data['bbm']),
            'date' => $data['date'],
        ]);

        $this->syncVehicleMileage($vehicle, (int) $data['finish_km']);

        return redirect()->route('records.index')->with('success', 'Catatan perjalanan berhasil diperbarui.');
    }

    public function destroy(Record $record)
    {
        $vehicle = Vehicle::where('plat_new', $record->no_pol)->first();
        $vehicle?->update(['mileage' => (string) Str::of((string) $record->start_km)->remove('-')]);

        $record->delete();

        return redirect()->route('records.index')->with('success', 'Catatan perjalanan berhasil disimpan di sampah.');
    }

    public function trash()
    {
        return view('records.trash', ['table' => new RecordTrashDataTable]);
    }

    public function restore(Record $record)
    {
        $record->restore();

        return redirect()->route('records.trash')->with('success', 'Catatan perjalanan berhasil dipulihkan.');
    }

    public function forceDelete(Record $record)
    {
        $record->forceDelete();

        return redirect()->route('records.trash')->with('success', 'Catatan perjalanan berhasil dihapus permanen.');
    }

    public function getData(Request $request)
    {
        [$startDate, $endDate, $companyId] = $this->resolveFilters($request);

        return (new RecordDataTable($startDate, $endDate, $companyId))->responseFor($request);
    }

    public function getTrashData(Request $request)
    {
        return (new RecordTrashDataTable)->responseFor($request);
    }

    public function getSnapshotData(Record $record, Request $request)
    {
        return (new RecordSnapshotDataTable($record))->responseFor($request);
    }

    public function exportExcel(Request $request)
    {
        [$startDate, $endDate, $companyId] = $this->resolveFilters($request);

        $fileName = 'Data_Perjalanan_'.now()->format('Ymd_His').'.xlsx';

        return Excel::download(new RecordExport($startDate, $endDate, $companyId), $fileName);
    }

    /**
     * @return array{0: string, 1: string, 2: int|null}
     */
    private function resolveFilters(Request $request): array
    {
        $startDate = $request->query('start_date') ?: Carbon::now()->startOfMonth()->toDateString();
        $endDate = $request->query('end_date') ?: Carbon::now()->endOfMonth()->toDateString();
        $companyId = $request->query('company') ? (int) $request->query('company') : null;

        return [$startDate, $endDate, $companyId];
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(): array
    {
        return [
            'drivers' => Driver::where('status', 'ACTIVE')->orderBy('name')->get(['id', 'name']),
            'vehicles' => Vehicle::where('status', 'ACTIVE')->orderBy('plat_new')->get(['id', 'plat_new', 'mileage']),
            'destinations' => Destination::query()->orderBy('name')->get(['id', 'name']),
            'companies' => Company::query()->orderBy('name')->get(['id', 'name', 'code']),
            'fuelTypes' => FuelType::query()->orderBy('name')->get(['id', 'name', 'price']),
        ];
    }

    private function resolveStartKm(string $plat, ?string $submitted): string
    {
        if (filled($submitted)) {
            return (string) Str::of($submitted)->remove('-');
        }

        $last = Record::latestForPlate($plat);

        return (string) ($last?->finish_km ?? 0);
    }

    private function computeScale(int $totals, string $bbm): string
    {
        $bbmValue = (float) $bbm;

        if ($bbmValue <= 0) {
            return '0';
        }

        return Str::replace(',', '.', number_format($totals / $bbmValue));
    }

    private function syncVehicleMileage(Vehicle $vehicle, int $mileage): void
    {
        $vehicle->update(['mileage' => (string) abs($mileage)]);
    }
}
