<?php

namespace App\Http\Controllers;

use App\DataTables\MaintenanceDataTable;
use App\DataTables\MaintenanceDetailDataTable;
use App\DataTables\MaintenanceTrashDataTable;
use App\Http\Requests\MaintenanceStoreRequest;
use App\Http\Requests\MaintenanceUpdateRequest;
use App\Models\Maintenance;
use App\Models\MaintenanceDetail;
use App\Models\Note;
use App\Models\TypeMaintenances;
use App\Models\Vehicle;
use App\Services\FilePondUploadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MaintenanceController extends Controller
{
    public function __construct(private readonly FilePondUploadService $filePondUploads) {}

    public function index(Request $request)
    {
        [$startDate, $endDate, $status] = $this->resolveFilters($request);

        Carbon::setLocale('id');

        return view('maintenances.index', [
            'table' => new MaintenanceDataTable($startDate, $endDate, $status),
            'startDate' => $startDate,
            'endDate' => $endDate,
            'status' => $status,
        ]);
    }

    public function getData(Request $request): JsonResponse
    {
        [$startDate, $endDate, $status] = $this->resolveFilters($request);

        return (new MaintenanceDataTable($startDate, $endDate, $status))->responseFor($request);
    }

    public function create()
    {
        return view('maintenances.create_or_edit', [
            'detailTable' => $this->detailTable(),
        ] + $this->formData());
    }

    public function store(MaintenanceStoreRequest $request): RedirectResponse
    {
        $isDraft = $request->isDraft();

        $maintenance = Maintenance::create([
            'code_mt' => Maintenance::generateCode(),
            'no_pol' => Str::upper((string) $request->input('no_pol', '')),
            'date_in' => $request->input('date_in') ?: now()->toDateString(),
            'date_out' => $request->input('date_out'),
            'mileage' => $request->input('mileage'),
            'description' => $request->input('desc'),
            'total_amount' => 0,
            'isServiceRoutine' => $request->input('isServiceRoutine', '1') === '0' ? false : true,
            'status' => $isDraft ? Maintenance::STATUS_DRAFT : Maintenance::STATUS_COMPLETED,
            'requested_by_name' => $request->user()?->name,
        ]);

        $this->storeNotes($request, $maintenance);
        $this->storeDetails($request, $maintenance);

        $message = $isDraft ? 'Berhasil disimpan sebagai draft.' : 'Berhasil disimpan.';

        return redirect()->route('maintenances.show', $maintenance)->with('success', $message);
    }

    public function show(Maintenance $maintenance)
    {
        if (
            $maintenance->status === Maintenance::STATUS_COMPLETED
            && ! $maintenance->is_confirmed
            && $maintenance->viewed_at === null
        ) {
            $maintenance->update([
                'viewed_at' => now(),
                'viewed_by' => auth()->id(),
            ]);
        }

        return view('maintenances.create_or_edit', [
            'maintenance' => $maintenance,
            'notes' => $maintenance->notes()->get(),
            'details' => $maintenance->details()->get(),
            'detailTable' => $this->detailTable($maintenance),
        ] + $this->formData());
    }

    public function update(Maintenance $maintenance, MaintenanceUpdateRequest $request): RedirectResponse
    {
        $isDraft = $request->isDraft();

        $maintenance->update([
            'no_pol' => Str::upper((string) $request->input('no_pol', $maintenance->no_pol)),
            'date_in' => $request->input('date_in') ?: $maintenance->date_in,
            'date_out' => $request->input('date_out'),
            'mileage' => $request->input('mileage'),
            'description' => $request->input('desc'),
            'isServiceRoutine' => $request->input('isServiceRoutine', $maintenance->isServiceRoutine ? '1' : '0') === '0' ? false : true,
            'status' => $isDraft ? Maintenance::STATUS_DRAFT : Maintenance::STATUS_COMPLETED,
            'requested_by_name' => $request->user()?->name,
        ]);

        $this->storeNotes($request, $maintenance);

        // The Tabulator detail table is the source of truth: replace the whole set.
        $maintenance->details()->forceDelete();
        $this->storeDetails($request, $maintenance);

        $message = $isDraft ? 'Berhasil disimpan sebagai draft.' : 'Berhasil disimpan.';

        return redirect()->route('maintenances.show', $maintenance)->with('success', $message);
    }

    public function status(Request $request, Maintenance $maintenance): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:DRAFT,COMPLETED,CANCELLED'],
        ]);

        $vehicle = Vehicle::where('plat_new', $maintenance->no_pol)->first();

        if ($validated['status'] === Maintenance::STATUS_COMPLETED) {
            if ($maintenance->details()->count() === 0) {
                return redirect()->back()->with('error', 'Detail perbaikan belum dimasukkan.');
            }

            $maintenance->update([
                'viewed_at' => now(),
                'viewed_by' => auth()->id(),
            ]);
        }

        $vehicle?->update(['mtc' => 'NO']);
        $maintenance->update(['status' => $validated['status']]);

        $label = match ($validated['status']) {
            Maintenance::STATUS_DRAFT => 'DRAFT',
            Maintenance::STATUS_COMPLETED => 'SELESAI',
            default => 'DIBATALKAN',
        };

        return redirect()->route('maintenances.index')->with('success', 'Status berhasil diubah ke '.$label.'.');
    }

    public function confirm(Request $request, Maintenance $maintenance): RedirectResponse
    {
        if ($maintenance->status !== Maintenance::STATUS_COMPLETED) {
            return redirect()->back()->with('error', 'Hanya pemeliharaan yang selesai yang dapat dikonfirmasi.');
        }

        $validated = $request->validate([
            'confirmation_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $maintenance->update([
            'is_confirmed' => true,
            'confirmed_at' => now(),
            'confirmed_by' => auth()->id(),
            'confirmation_notes' => $maintenance->confirmation_notes ?? ($validated['confirmation_notes'] ?? null),
        ]);

        return redirect()->back()->with('success', 'Pemeliharaan berhasil dikonfirmasi.');
    }

    public function destroy(Maintenance $maintenance): RedirectResponse
    {
        $maintenance->delete();

        return redirect()->route('maintenances.index')->with('success', 'Data pemeliharaan berhasil disimpan di sampah.');
    }

    public function trash()
    {
        return view('maintenances.trash', ['table' => new MaintenanceTrashDataTable]);
    }

    public function getTrashData(Request $request): JsonResponse
    {
        return (new MaintenanceTrashDataTable)->responseFor($request);
    }

    public function restore(Maintenance $maintenance): RedirectResponse
    {
        $maintenance->restore();

        return redirect()->route('maintenances.trash')->with('success', 'Data pemeliharaan berhasil dipulihkan.');
    }

    public function forceDelete(Maintenance $maintenance): RedirectResponse
    {
        $maintenance->forceDelete();

        return redirect()->route('maintenances.trash')->with('success', 'Data pemeliharaan berhasil dihapus permanen.');
    }

    public function deleteDetail(MaintenanceDetail $detail): JsonResponse
    {
        $maintenance = $detail->maintenance;
        $detail->delete();
        $maintenance?->recalculateTotal();

        return response()->json(['success' => true]);
    }

    public function deleteNote(Note $note): JsonResponse
    {
        if ($note->image && Storage::disk('s3')->exists($note->image)) {
            Storage::disk('s3')->delete($note->image);
        }

        $note->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Move FilePond temp uploads to S3 and persist as Note records.
     */
    private function storeNotes(Request $request, Maintenance $maintenance): void
    {
        $paths = $this->filePondUploads->storeManyFromRequestField($request, 'fields', 'maintenances', 's3');

        foreach ($paths as $path) {
            Note::create([
                'maintenances_id' => $maintenance->id,
                'image' => $path,
                'url' => Storage::disk('s3')->url($path),
            ]);
        }
    }

    /**
     * Persist repeater line items and recalculate the maintenance total.
     */
    private function storeDetails(Request $request, Maintenance $maintenance): void
    {
        $items = $request->input('items');

        if (is_array($items)) {
            foreach ($items as $item) {
                if (empty($item['maintenance_name'])) {
                    continue;
                }

                $price = (int) ($item['price'] ?? 0);
                $qty = (int) ($item['qty'] ?? 1);

                MaintenanceDetail::create([
                    'maintenance_id' => $maintenance->id,
                    'maintenance_name' => $item['maintenance_name'],
                    'price' => $price,
                    'qty' => $qty,
                    'subtotal' => $price * $qty,
                    'material' => $item['material'] ?? null,
                ]);
            }
        }

        $maintenance->recalculateTotal();
    }

    /**
     * @return array{0: ?string, 1: ?string, 2: ?string}
     */
    private function resolveFilters(Request $request): array
    {
        return [
            $request->query('start_date'),
            $request->query('end_date'),
            $request->query('status'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(): array
    {
        return [
            'vehicles' => Vehicle::where('status', 'ACTIVE')->orderBy('plat_new')->get(['id', 'plat_new', 'mileage']),
            'types' => TypeMaintenances::orderBy('name')->get(['id', 'name']),
        ];
    }

    /**
     * Build the client-side editable detail sub-grid (local Tabulator).
     */
    private function detailTable(?Maintenance $maintenance = null): MaintenanceDetailDataTable
    {
        $typeNames = TypeMaintenances::orderBy('name')->pluck('name')->values()->all();

        $rows = $maintenance
            ? $maintenance->details()->get()->map(fn (MaintenanceDetail $detail): array => [
                'maintenance_name' => $detail->maintenance_name,
                'price' => (float) $detail->price,
                'qty' => (int) ($detail->qty ?? 1),
            ])->all()
            : [];

        return new MaintenanceDetailDataTable($typeNames, $rows, $maintenance?->isReadOnly() ?? false);
    }
}
