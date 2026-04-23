<?php

namespace App\Http\Controllers;

use App\Models\EsbProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ProductEsbController extends Controller
{
    /**
     * Display a listing of ESB products.
     */
    public function index()
    {
        return view('products_esb.index');
    }

    /**
     * Get data for DataTables.
     */
    public function getData(Request $request)
    {
        $products = EsbProduct::select([
            'id',
            'product_id',
            'product_code',
            'product_name',
            'category_name',
            'sub_category_name',
            'requestable',
            'purchasable',
            'saleable',
            'last_synced_at',
        ]);

        return datatables()->of($products)
            ->addIndexColumn()
            ->addColumn('category_info', function ($product) {
                $category = $product->category_name ?: '-';
                $subCategory = $product->sub_category_name ?: '-';
                return $category . ' / ' . $subCategory;
            })
            ->addColumn('requestable_badge', function ($product) {
                return $this->formatYesNoBadge($product->requestable);
            })
            ->addColumn('purchasable_badge', function ($product) {
                return $this->formatYesNoBadge($product->purchasable);
            })
            ->addColumn('saleable_badge', function ($product) {
                return $this->formatYesNoBadge($product->saleable);
            })
            ->addColumn('last_synced_at', function ($product) {
                return $product->last_synced_at
                    ? $product->last_synced_at->format('Y-m-d H:i')
                    : '-';
            })
            ->addColumn('action', function () {
                return view('datatable.actions.empty')->render();
            })
            ->rawColumns(['requestable_badge', 'purchasable_badge', 'saleable_badge', 'action'])
            ->make(true);
    }

    /**
     * Sync a single page of products from ESB.
     */
    public function syncSinglePage(Request $request)
    {
        $request->validate([
            'page' => 'nullable|integer|min:1',
            'statusActive' => 'nullable|in:Yes,No',
        ]);

        $page = (int) $request->input('page', 1);
        $statusActive = $request->input('statusActive', 'Yes');

        try {
            $payload = $this->fetchProductsFromEsb($page, 10, $statusActive);
            $imported = $this->processProductData($payload['data'] ?? []);

            return response()->json([
                'status' => 'success',
                'message' => "Imported {$imported} products from page {$page}",
                'data' => [
                    'imported_count' => $imported,
                    'page' => $page,
                    'total_count' => $payload['count'] ?? 0,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('ESB product sync (single page) failed', [
                'page' => $page,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Sync failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Sync all products from ESB.
     */
    public function syncAll(Request $request)
    {
        $request->validate([
            'statusActive' => 'nullable|in:Yes,No',
        ]);

        $statusActive = $request->input('statusActive', 'Yes');
        $totalImported = 0;
        $page = 1;
        $totalCount = 0;

        try {
            DB::beginTransaction();

            do {
                $payload = $this->fetchProductsFromEsb($page, 50, $statusActive);

                if ($page === 1) {
                    $totalCount = $payload['count'] ?? 0;
                }

                $imported = $this->processProductData($payload['data'] ?? []);
                $totalImported += $imported;

                Log::info("ESB products imported page {$page}: {$imported} (Total: {$totalImported}/{$totalCount})");

                $page++;
                usleep(100000);
            } while (!empty($payload['data']) && $totalImported < $totalCount);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => "Successfully imported {$totalImported} products from " . ($page - 1) . ' pages',
                'data' => [
                    'total_imported' => $totalImported,
                    'total_pages' => $page - 1,
                    'total_available' => $totalCount,
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('ESB product sync (all) failed', [
                'page' => $page,
                'total_imported' => $totalImported,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => "Sync failed on page {$page}. Imported {$totalImported} products before failure. Error: " . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Fetch products payload from ESB API.
     */
    private function fetchProductsFromEsb(int $page, int $limit, string $statusActive): array
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . config('services.product_esb.token'),
            'Content-Type' => 'application/json',
        ])->timeout(60)->get(config('services.product_esb.url'), [
            'statusActive' => $statusActive,
            'page' => $page,
            'limit' => $limit,
        ]);

        if (! $response->successful()) {
            throw new \Exception("API request failed on page {$page}: " . $response->status());
        }

        $data = $response->json();

        if (($data['status'] ?? null) !== 'ok') {
            throw new \Exception('API returned error status: ' . ($data['message'] ?? 'Unknown error'));
        }

        return [
            'data' => $data['result']['data'] ?? [],
            'count' => $data['result']['count'] ?? 0,
        ];
    }

    /**
     * Process and store product data with deadlock retry logic.
     */
    private function processProductData(array $products): int
    {
        $imported = 0;

        foreach ($products as $productData) {
            $success = $this->retryOnDeadlock(function () use ($productData) {
                return $this->processProduct($productData);
            }, 3);

            if ($success) {
                $imported++;
            } else {
                Log::error('Failed to process ESB product after retries', [
                    'product_id' => $productData['productID'] ?? 'unknown',
                ]);
            }
        }

        return $imported;
    }

    /**
     * Process a single product.
     */
    private function processProduct(array $productData): bool
    {
        $productId = $productData['productID'] ?? null;
        if (! $productId) {
            return false;
        }

        $details = $productData['productDetails'] ?? null;
        if (is_string($details)) {
            $details = json_decode($details, true) ?: null;
        }
        if (! is_array($details)) {
            $details = null;
        }

        EsbProduct::updateOrCreate(
            ['product_id' => $productId],
            [
                'product_code' => $productData['productCode'] ?? null,
                'product_name' => $productData['productName'] ?? null,
                'category_id' => $productData['categoryID'] ?? null,
                'category_name' => $productData['categoryName'] ?? null,
                'sub_category_id' => $productData['subCategoryID'] ?? null,
                'sub_category_name' => $productData['subCategoryName'] ?? null,
                'bill_of_material_id' => $productData['billOfMaterialID'] ?? null,
                'bill_of_material_code' => $productData['billOfMaterialCode'] ?? null,
                'bill_of_material_name' => $productData['billOfMaterialName'] ?? null,
                'requestable' => $productData['requestable'] ?? null,
                'purchasable' => $productData['purchasable'] ?? null,
                'vat' => $productData['vat'] ?? null,
                'receipt_tolerance' => $productData['receiptTolerance'] ?? null,
                'saleable' => $productData['saleable'] ?? null,
                'notes' => $productData['notes'] ?? null,
                'created_date' => $productData['createdDate'] ?? null,
                'created_by' => $productData['createdBy'] ?? null,
                'edited_date' => $productData['editedDate'] ?? null,
                'edited_by' => $productData['editedBy'] ?? null,
                'product_details' => $details,
                'last_synced_at' => now(),
            ]
        );

        return true;
    }

    /**
     * Retry operation on deadlock with exponential backoff.
     */
    private function retryOnDeadlock(callable $operation, int $maxAttempts = 3)
    {
        $attempt = 1;

        while ($attempt <= $maxAttempts) {
            try {
                return $operation();
            } catch (\Exception $e) {
                if ($this->isDeadlockException($e) && $attempt < $maxAttempts) {
                    $delay = pow(2, $attempt - 1) * 100000;
                    usleep($delay + random_int(0, 50000));
                    $attempt++;
                    continue;
                }
                throw $e;
            }
        }

        return false;
    }

    /**
     * Check if exception is a deadlock.
     */
    private function isDeadlockException(\Exception $e): bool
    {
        return str_contains($e->getMessage(), '1213 Deadlock found') ||
            str_contains($e->getMessage(), 'Serialization failure') ||
            str_contains($e->getMessage(), 'try restarting transaction');
    }

    /**
     * Render Yes/No as a badge.
     */
    private function formatYesNoBadge(?string $value): string
    {
        if ($value === null || $value === '') {
            return '<span class="text-muted">-</span>';
        }

        $normalized = strtolower((string) $value);
        $isYes = $normalized === 'yes' || $normalized === 'y' || $normalized === '1' || $normalized === 'true';

        return $isYes
            ? '<span class="badge bg-success"><i class="bx bx-check"></i> Yes</span>'
            : '<span class="badge bg-secondary"><i class="bx bx-x"></i> No</span>';
    }
}
