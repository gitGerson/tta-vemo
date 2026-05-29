<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Rules\DifferentPhoneNumbers;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class DriverController extends Controller
{
    public function index(Request $request)
    {
        // View relies on ajax route driver.data for the table contents
        return view('pages.driver.index');
    }

    public function store(Request $request)
    {
        $request->validate(
            [
                'image' => 'nullable',
                'name' => 'required|string|max:255',
                'nik' => ['nullable', 'string', Rule::unique('drivers', 'nik')],
                'address' => 'required|string|max:255',
                'no_identity' => ['required', 'min:16', 'max:16', Rule::unique('drivers', 'no_identity')],
                'residence' => 'required|string|max:255',
                'date_birth' => 'required|date',
                'no_phone_1' => ['required', 'min:5', 'max:15', Rule::unique('drivers', 'no_phone_1')],
                'no_phone_2' => ['required', 'min:5', 'max:15', Rule::unique('drivers', 'no_phone_2'), new DifferentPhoneNumbers($request->no_phone_1)],
                'entry_date' => 'required|date',
                'out_date' => 'nullable|date|after_or_equal:entry_date',
                'status' => ['required', Rule::in(['ACTIVE', 'PASSIVE'])],
            ],
            [
                'no_phone_1.unique' => 'No HP sudah tersedia.',
                'no_phone_2.unique' => 'No HP wali sudah tersedia.',
            ],
        );

        $nik = $request->nik;
        if ($nik) {
            if (! $this->validateNikWithApi($nik)) {
                return back()->withErrors(['nik' => 'NIK tidak valid atau tidak ditemukan.'])->withInput();
            }
        }

        $imageName = $this->handleImageUpload($request);

        Driver::create([
            'image' => $imageName,
            'name' => $request->name,
            'nik' => $nik,
            'address' => $request->address,
            'no_identity' => Str::of($request->no_identity)->remove('-'),
            'residence' => $request->residence,
            'date_birth' => $request->date_birth,
            'no_phone_1' => Str::of($request->no_phone_1)->remove('-'),
            'no_phone_2' => Str::of($request->no_phone_2)->remove('-'),
            'entry_date' => $request->entry_date,
            'out_date' => $request->out_date,
            'status' => $request->status,
        ]);

        return redirect()->route('driver.index')->with('success', 'Driver berhasil ditambahkan.');
    }

    public function update(Request $request, string $id)
    {
        $driver = Driver::findOrFail($id);

        $request->validate(
            [
                'image' => 'nullable',
                'name' => 'required|string|max:255',
                'nik' => ['nullable', 'string', Rule::unique('drivers', 'nik')->ignore($id)],
                'address' => 'required|string|max:255',
                'no_identity' => ['required', 'min:16', 'max:16', Rule::unique('drivers', 'no_identity')->ignore($id)],
                'residence' => 'required|string|max:255',
                'date_birth' => 'required|date',
                'no_phone_1' => ['required', 'min:5', 'max:15', Rule::unique('drivers', 'no_phone_1')->ignore($id)],
                'no_phone_2' => ['required', 'min:5', 'max:15', Rule::unique('drivers', 'no_phone_2')->ignore($id), new DifferentPhoneNumbers($request->no_phone_1)],
                'entry_date' => 'required|date',
                'out_date' => 'nullable|date|after_or_equal:entry_date',
                'status' => ['required', Rule::in(['ACTIVE', 'PASSIVE'])],
            ],
            [
                'no_phone_1.unique' => 'No HP sudah tersedia.',
                'no_phone_2.unique' => 'No HP wali sudah tersedia.',
            ],
        );

        $nik = $request->nik;
        if ($nik) {
            if (! $this->validateNikWithApi($nik)) {
                return back()->withErrors(['nik' => 'NIK tidak valid atau tidak ditemukan.'])->withInput();
            }
        }

        $imageName = $this->handleImageUpload($request, $driver->image);

        $driver->update([
            'image' => $imageName,
            'name' => $request->name,
            'nik' => $nik,
            'address' => $request->address,
            'no_identity' => Str::of($request->no_identity)->remove('-'),
            'residence' => $request->residence,
            'date_birth' => $request->date_birth,
            'no_phone_1' => Str::of($request->no_phone_1)->remove('-'),
            'no_phone_2' => Str::of($request->no_phone_2)->remove('-'),
            'entry_date' => $request->entry_date,
            'out_date' => $request->out_date,
            'status' => $request->status,
        ]);

        return redirect()->route('driver.index')->with('success', 'Driver berhasil diperbarui.');
    }

    public function destroy(Request $request, string $id)
    {
        $driver = Driver::findOrFail($id);
        $driver->out_date = $request->out_date ?? $driver->out_date;
        $driver->status = 'PASSIVE';
        $driver->save();
        $driver->delete();

        return redirect()->route('driver.index')->with('success', 'Driver berhasil disimpan di sampah.');
    }

    public function getData(Request $request)
    {
        $drivers = Driver::select([
            'id',
            'name',
            'address',
            'no_identity',
            'residence',
            'date_birth',
            'no_phone_1',
            'no_phone_2',
            'entry_date',
            'out_date',
            'status',
            'image',
            'nik',
        ]);

        return DataTables::of($drivers)
            ->addIndexColumn()
            ->addColumn('action', function ($driver) {
                $canEdit = auth()->user()->hasPermission('master.drivers.edit');
                $canDelete = auth()->user()->hasPermission('master.drivers.delete');

                $attrs = [
                    'name' => e($driver->name),
                    'address' => e($driver->address),
                    'no_identity' => e($driver->no_identity),
                    'residence' => e($driver->residence),
                    'date_birth' => e($driver->date_birth),
                    'no_phone_1' => e($driver->no_phone_1),
                    'no_phone_2' => e($driver->no_phone_2),
                    'entry_date' => e($driver->entry_date),
                    'out_date' => e($driver->out_date),
                    'status' => e($driver->status),
                    'image' => e($driver->image),
                    'nik' => e($driver->nik),
                ];

                $actions = '<div class="dropdown">
                    <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                        <i class="bx bx-dots-vertical-rounded"></i>
                    </button>
                    <div class="dropdown-menu">';
                if ($canEdit) {
                    $actions .= '<a class="dropdown-item btn-edit-driver"
                            href="#"
                            data-update-url="'.route('driver.update', $driver->id).'"
                            data-name="'.$attrs['name'].'"
                            data-nik="'.$attrs['nik'].'"
                            data-no_identity="'.$attrs['no_identity'].'"
                            data-image="'.$attrs['image'].'"
                            data-address="'.$attrs['address'].'"
                            data-residence="'.$attrs['residence'].'"
                            data-date_birth="'.$attrs['date_birth'].'"
                            data-entry_date="'.$attrs['entry_date'].'"
                            data-out_date="'.$attrs['out_date'].'"
                            data-no_phone_1="'.$attrs['no_phone_1'].'"
                            data-no_phone_2="'.$attrs['no_phone_2'].'"
                            data-status="'.$attrs['status'].'">
                            <i class="bx bx-edit-alt me-1"></i> Edit
                        </a>';
                }
                if ($canDelete) {
                    $actions .= '<form action="'.route('driver.destroy', $driver->id).'" method="POST" style="display: inline;">
                            '.csrf_field().'
                            '.method_field('DELETE').'
                            <button type="submit" class="dropdown-item text-danger" onclick="return confirm(\'Yakin ingin menghapus driver ini?\')">
                                <i class="bx bx-trash me-1"></i> Hapus
                            </button>
                        </form>';
                }
                $actions .= '</div></div>';

                return $actions;
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function checkDriver(Request $request)
    {
        $data = Driver::where('id', '!=', $request->driver_1)->get();

        return response()->json(
            [
                'data' => $data,
            ],
            200,
        );
    }

    public function getEmpInfo(Request $request)
    {
        $request->validate([
            'nik' => 'required|string',
        ]);

        $nik = $request->nik;

        Log::info('NIK Search Request:', ['nik' => $nik]);

        $client = new Client([
            'timeout' => 30,
            'connect_timeout' => 10,
        ]);

        try {
            $response = $client->request('GET', 'http://19.38.40.20/jpayroll/thirdparty/ext/API_View_Master_EmpInfo.php', [
                'headers' => [
                    'Authorization' => 'Basic UVZCSlBVVjRWQ3MzYjI0Mk4yb3hOek5oT2pkdmJqWTNhakUzTTJGQVNuQTBlWEl3TVRFPQ==',
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    'User-Agent' => 'Laravel/Application',
                ],
                'json' => [
                    'NIK' => $nik,
                ],
            ]);

            $statusCode = $response->getStatusCode();
            $rawBody = $response->getBody()->getContents();

            Log::info('API Response Details:', [
                'status_code' => $statusCode,
                'raw_body' => $rawBody,
                'content_type' => $response->getHeader('Content-Type'),
            ]);

            if (empty($rawBody)) {
                Log::warning('Empty response from external API');

                return response()->json([
                    'success' => false,
                    'message' => 'Empty response from external API',
                    'status_code' => $statusCode,
                ], 200);
            }

            $body = json_decode($rawBody, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('JSON Decode Error:', [
                    'error' => json_last_error_msg(),
                    'raw_response' => $rawBody,
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Invalid JSON response from external API',
                    'json_error' => json_last_error_msg(),
                    'raw_response' => $rawBody,
                    'status_code' => $statusCode,
                ], 200);
            }

            if (isset($body['status']) && $body['status'] === '401') {
                Log::error('Authentication failed with external API:', ['response' => $body]);

                return response()->json([
                    'success' => false,
                    'message' => 'Authentication failed with external API',
                    'api_response' => $body,
                ], 200);
            }

            if (isset($body['error']) || (isset($body['status']) && $body['status'] === 'error')) {
                Log::warning('API returned error response:', ['response' => $body]);

                return response()->json([
                    'success' => false,
                    'message' => 'API returned error',
                    'api_response' => $body,
                ], 200);
            }

            $filteredData = $this->filterEmployeeByNIK($body, $nik);

            if (empty($filteredData)) {
                Log::info('No employee found for NIK:', ['nik' => $nik]);

                return response()->json([
                    'success' => false,
                    'message' => 'Data Karyawan Tidak dengan NIK tersebut',
                    'data' => [],
                ], 200);
            }

            return response()->json([
                'success' => true,
                'data' => $filteredData,
                'status_code' => $statusCode,
            ]);
        } catch (ConnectException $e) {
            Log::error('Connection Error:', [
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Tidak dapat terhubung ke server eksternal',
                'details' => $e->getMessage(),
            ], 200);
        } catch (RequestException $e) {
            $statusCode = $e->getResponse() ? $e->getResponse()->getStatusCode() : 0;
            $responseBody = $e->getResponse() ? $e->getResponse()->getBody()->getContents() : '';

            Log::error('HTTP Request Error:', [
                'status_code' => $statusCode,
                'message' => $e->getMessage(),
                'response_body' => $responseBody,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'HTTP Error dari server eksternal',
                'status_code' => $statusCode,
                'response_body' => $responseBody,
            ], 200);
        } catch (\Exception $e) {
            Log::error('General API Error:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal menghubungi server eksternal',
                'error' => $e->getMessage(),
            ], 200);
        }
    }

    private function validateNikWithApi(string $nik): bool
    {
        try {
            $client = new Client([
                'timeout' => 30,
                'connect_timeout' => 10,
            ]);

            $response = $client->request('GET', 'http://19.38.40.20/jpayroll/thirdparty/ext/API_View_Master_EmpInfo.php', [
                'headers' => [
                    'Authorization' => 'Basic UVZCSlBVVjRWQ3MzYjI0Mk4yb3hOek5oT2pkdmJqWTNhakUzTTJGQVNuQTBlWEl3TVRFPQ==',
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    'User-Agent' => 'Laravel/Application',
                ],
                'json' => [
                    'NIK' => $nik,
                ],
            ]);

            $rawBody = $response->getBody()->getContents();
            $body = json_decode($rawBody, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('Error validating NIK via API: '.json_last_error_msg(), ['raw' => $rawBody]);

                return false;
            }

            if (isset($body['status']) && $body['status'] === '401') {
                return false;
            }

            $filtered = $this->filterEmployeeByNIK($body, $nik);

            return ! empty($filtered);
        } catch (\Exception $e) {
            Log::error('Error validating NIK via API: '.$e->getMessage());

            return false;
        }
    }

    private function handleImageUpload(Request $request, ?string $currentImage = null): ?string
    {
        if ($request->hasFile('image')) {
            $imageName = time().'.'.$request->image->extension();
            $request->image->move(public_path('profile_images'), $imageName);

            return $imageName;
        }

        if ($request->filled('image')) {
            return $request->image;
        }

        return $currentImage;
    }

    private function filterEmployeeByNIK($apiResponse, $searchNik)
    {
        $employees = [];

        if (! is_array($apiResponse)) {
            return [];
        }

        if (isset($apiResponse['data']) && is_array($apiResponse['data'])) {
            $employees = $apiResponse['data'];
        } elseif (is_array($apiResponse)) {
            $employees = $apiResponse;
        }

        if (empty($employees)) {
            return [];
        }

        $filteredEmployees = array_filter($employees, function ($employee) use ($searchNik) {
            if (is_object($employee)) {
                $employee = (array) $employee;
            }
            if (! is_array($employee)) {
                return false;
            }
            $nikFields = ['nik', 'NIK', 'employee_id', 'emp_id', 'IdCardNumber', 'id_card_number', 'emp_nik', 'EmpID', 'EMP_NIK'];
            foreach ($nikFields as $field) {
                if (isset($employee[$field]) && (string) $employee[$field] === (string) $searchNik) {
                    return true;
                }
            }

            // Fallback: scan scalar values for an exact match
            foreach ($employee as $value) {
                if (is_scalar($value) && (string) $value === (string) $searchNik) {
                    return true;
                }
            }

            return false;
        });

        return array_values($filteredEmployees);
    }
}
