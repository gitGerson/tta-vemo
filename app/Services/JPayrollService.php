<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;

class JPayrollService
{
    public function __construct(
        protected string $url = '',
        protected string $token = '',
    ) {}

    /**
     * Fetch employee records matching the given NIK from the payroll API.
     * Returns an empty array if NIK is not found.
     *
     * @return array{success: bool, message: string, data?: array<int, mixed>}
     */
    public function fetchEmployeeByNik(string $nik): array
    {
        try {
            $response = $this->makeClient()->request('GET', $this->url, [
                'headers' => $this->headers(),
                'json' => ['NIK' => $nik],
            ]);

            $statusCode = $response->getStatusCode();
            $rawBody = $response->getBody()->getContents();

            Log::info('JPayroll API response', [
                'status_code' => $statusCode,
                'raw_body' => $rawBody,
                'content_type' => $response->getHeader('Content-Type'),
            ]);

            if (empty($rawBody)) {
                Log::warning('JPayroll: empty response from API');

                return ['success' => false, 'message' => 'Empty response from external API'];
            }

            $body = json_decode($rawBody, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('JPayroll: JSON decode error', [
                    'error' => json_last_error_msg(),
                    'raw_response' => $rawBody,
                ]);

                return [
                    'success' => false,
                    'message' => 'Invalid JSON response from external API',
                ];
            }

            if (isset($body['status']) && $body['status'] === '401') {
                Log::error('JPayroll: authentication failed', ['response' => $body]);

                return ['success' => false, 'message' => 'Authentication failed with external API'];
            }

            if (isset($body['error']) || (isset($body['status']) && $body['status'] === 'error')) {
                Log::warning('JPayroll: API returned error', ['response' => $body]);

                return ['success' => false, 'message' => 'API returned error'];
            }

            $filtered = $this->filterByNik($body, $nik);

            if (empty($filtered)) {
                Log::info('JPayroll: no employee found for NIK', ['nik' => $nik]);

                return ['success' => false, 'message' => 'Data Karyawan Tidak dengan NIK tersebut', 'data' => []];
            }

            return ['success' => true, 'data' => $filtered];
        } catch (ConnectException $e) {
            Log::error('JPayroll: connection error', ['message' => $e->getMessage()]);

            return ['success' => false, 'message' => 'Tidak dapat terhubung ke server eksternal'];
        } catch (RequestException $e) {
            $statusCode = $e->getResponse()?->getStatusCode() ?? 0;
            Log::error('JPayroll: HTTP request error', ['status_code' => $statusCode, 'message' => $e->getMessage()]);

            return ['success' => false, 'message' => 'HTTP Error dari server eksternal'];
        } catch (\Exception $e) {
            Log::error('JPayroll: general error', ['message' => $e->getMessage()]);

            return ['success' => false, 'message' => 'Gagal menghubungi server eksternal'];
        }
    }

    /**
     * Returns true when the NIK resolves to at least one employee record.
     */
    public function validateNik(string $nik): bool
    {
        try {
            $response = $this->makeClient()->request('GET', $this->url, [
                'headers' => $this->headers(),
                'json' => ['NIK' => $nik],
            ]);

            $body = json_decode($response->getBody()->getContents(), true);

            if (json_last_error() !== JSON_ERROR_NONE || (isset($body['status']) && $body['status'] === '401')) {
                return false;
            }

            return ! empty($this->filterByNik($body, $nik));
        } catch (\Exception $e) {
            Log::error('JPayroll: NIK validation error', ['message' => $e->getMessage()]);

            return false;
        }
    }

    private function makeClient(): Client
    {
        return new Client(['timeout' => 30, 'connect_timeout' => 10]);
    }

    /**
     * @return array<string, string>
     */
    private function headers(): array
    {
        return [
            'Authorization' => 'Basic '.$this->token,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'User-Agent' => 'Laravel/Application',
        ];
    }

    /**
     * Filter API response records to those matching the given NIK value.
     *
     * @return array<int, mixed>
     */
    private function filterByNik(mixed $apiResponse, string $searchNik): array
    {
        if (! is_array($apiResponse)) {
            return [];
        }

        $employees = $apiResponse['data'] ?? (is_array($apiResponse) ? $apiResponse : []);

        if (empty($employees)) {
            return [];
        }

        $nikFields = ['nik', 'NIK', 'employee_id', 'emp_id', 'IdCardNumber', 'id_card_number', 'emp_nik', 'EmpID', 'EMP_NIK'];

        $filtered = array_filter($employees, function (mixed $employee) use ($searchNik, $nikFields): bool {
            if (is_object($employee)) {
                $employee = (array) $employee;
            }

            if (! is_array($employee)) {
                return false;
            }

            foreach ($nikFields as $field) {
                if (isset($employee[$field]) && (string) $employee[$field] === $searchNik) {
                    return true;
                }
            }

            foreach ($employee as $value) {
                if (is_scalar($value) && (string) $value === $searchNik) {
                    return true;
                }
            }

            return false;
        });

        return array_values($filtered);
    }
}
