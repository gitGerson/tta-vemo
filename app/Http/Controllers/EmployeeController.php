<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class EmployeeController extends Controller
{
    /**
     * Display a listing of the employees.
     */
    public function index()
    {
        return view('employees.index');
    }

    /**
     * Sync employees from JPayroll API
     */
    public function syncJPayroll(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'company' => 'required|in:TTI,CTR',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $company = $validator->validated()['company'];

        try {
            $apiUrl = config('services.jpayroll.api_url', 'http://19.38.40.20/jpayroll/thirdparty/ext/API_View_Master_EmpInfo.php');
            $apiKey = config('services.jpayroll.api_key');
            $authorization = $apiKey ? (Str::startsWith($apiKey, 'Basic ') ? $apiKey : 'Basic ' . $apiKey) : 'Basic UVZCSlBVVjRWQ3MzYjI0Mk4yb3hOek5oT2pkdmJqWTNhakUzTTJGQVNuQTBlWEl3TVRFPQ==';

            // Determine CompanyArea based on company
            $companyArea = $company === 'TTI' ? '10000' : '20000';
            $payload = [
                'NIK' => '',
                'IdFacility' => '',
                'CompanyArea' => $companyArea,
            ];

            // Fetch data from JPayroll API
            $response = Http::withHeaders([
                'Authorization' => $authorization,
                'Accept' => 'application/json',
            ])->timeout(60)->post($apiUrl, $payload);

            if (!$response->successful()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to fetch data from JPayroll API',
                ], 500);
            }

            $employees = $response->json('data', []);

            $synced = 0;
            $created = 0;
            $updated = 0;
            $errors = [];

            DB::beginTransaction();

            foreach ($employees as $employeeData) {
                try {
                    // Map JPayroll fields to our schema
                    $status = strtoupper($employeeData['Status'] ?? '') === 'ACTIVE' ? 'ACTIVE' : 'INACTIVE';
                    $group = 'Staff';
                    if (isset($employeeData['GradeCode'])) {
                        $group = Str::contains(strtoupper($employeeData['GradeCode']), 'NON') ? 'Non Staff' : 'Staff';
                    } elseif (isset($employeeData['CompanyArea'])) {
                        $group = $employeeData['CompanyArea'] === '10000' ? 'Staff' : 'Non Staff';
                    }

                    $data = [
                        'nik' => $employeeData['NIK'] ?? null,
                        'name' => $employeeData['Name'] ?? null,
                        'email' => null,
                        'phone' => $employeeData['mobilePhone'] ?? null,
                        'department' => $employeeData['OrganizationStructure'] ?? 'Unknown',
                        'company' => $company,
                        'group' => $group,
                        'status' => $status,
                        'synced_from_jpayroll' => true,
                        'last_synced_at' => now(),
                    ];

                    // Skip if NIK is empty
                    if (empty($data['nik'])) {
                        continue;
                    }

                    // Update or create employee
                    $employee = Employee::updateOrCreate(
                        ['nik' => $data['nik']],
                        $data
                    );

                    if ($employee->wasRecentlyCreated) {
                        $created++;
                    } else {
                        $updated++;
                    }

                    $synced++;
                } catch (\Exception $e) {
                    $errors[] = [
                        'nik' => $employeeData['nik'] ?? 'unknown',
                        'error' => $e->getMessage(),
                    ];
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Sync completed: {$synced} employees synced ({$created} created, {$updated} updated)",
                'data' => [
                    'synced' => $synced,
                    'created' => $created,
                    'updated' => $updated,
                    'errors' => $errors,
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Sync failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get data for DataTables.
     */
    public function getData(Request $request)
    {
        $employees = Employee::select([
            'id',
            'nik',
            'name',
            'department',
            'company',
            'group',
            'status',
            'last_synced_at',
        ]);

        return datatables()->of($employees)
            ->addIndexColumn()
            ->addColumn('status', function ($employee) {
                $isActive = strtoupper($employee->status ?? '') === 'ACTIVE';
                return $isActive
                    ? '<span class="badge bg-success">Aktif</span>'
                    : '<span class="badge bg-secondary">Nonaktif</span>';
            })
            ->addColumn('last_synced_at', function ($employee) {
                return $employee->last_synced_at
                    ? $employee->last_synced_at->format('Y-m-d H:i')
                    : '-';
            })
            ->addColumn('action', function () {
                return view('datatable.actions.empty')->render();
            })
            ->rawColumns(['status', 'action'])
            ->make(true);
    }
}
