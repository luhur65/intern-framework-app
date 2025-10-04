<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePenjualanRequest;
use App\Models\Pelanggan;
use App\Models\Penjualan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Exception;

/**
 * Class PenjualanController
 *
 * Handles all sales-related requests, including CRUD operations, data export,
 * and providing data for jqGrid.
 *
 * @package App\Http\Controllers
 */
class PenjualanController extends Controller
{
    /**
     * @var array Holds the parameters for the jqGrid.
     */
    protected $gridParams = [];

    /**
     * @var array Holds the filter rules from the request.
     */
    protected $filters = [];

    /**
     * PenjualanController constructor.
     *
     * Initializes grid parameters from the current request. This includes settings for
     * sorting, pagination, and search filters.
     */
    public function __construct()
    {
        // Initialize grid parameters
        // sidx: field for sorting, sord: sorting direction (asc/desc), page: page number, limit: number of records per page
        // start: offset for pagination, global_search: for global search, _search: for filter-based search
        $this->gridParams = [
            'sidx'   => \request()->input('sidx', 'id'),
            'sord'   => \request()->input('sord', 'asc'),
            'page'   => (int) \request()->input('page', 1),
            'limit'  => (int) \request()->input('rows', 10),
        ];

        // Calculate start
        $this->gridParams['start'] = $this->gridParams['limit'] * ($this->gridParams['page'] - 1);

        $this->gridParams['global_search'] = \request()->input('global_search', '');
        $this->gridParams['_search'] = \request()->input('_search', 'false');

        if (request()->input('_search') == 'true') {
            $filterJson = request()->input('filters');
            $decoded = json_decode($filterJson, true);
            if (isset($decoded['rules'])) {
                $this->filters = $decoded['rules'];
            }

            $this->gridParams['filters'] = $this->filters;
        }
    }

    /**
     * Generates a new unique proof number for a sales transaction.
     *
     * This method calls the model to get the next available proof number
     * and returns it as a JSON response.
     *
     * @return JsonResponse A JSON response containing the new proof number or an error message.
     */
    public function generateNoBukti(): JsonResponse
    {
        try {
            // Call the service to get the next number
            $noBukti = Penjualan::getNextNoBukti();

            return response()->json(['no_bukti' => $noBukti]);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to generate proof number.'], 500);
        }
    }

    /**
     * Helper method to retrieve grid data and return it as a JSON response.
     *
     * This method centralizes the logic for fetching paginated and sorted data
     * for the jqGrid, optionally focusing on a specific row ID.
     *
     * @param Request $request The current HTTP request to get grid parameters.
     * @param int|null $focusId The ID of the row to be focused or highlighted.
     * @return JsonResponse A JSON response containing the paginated data for the grid.
     */
    private function getGridResponse(Request $request, ?int $focusId): JsonResponse
    {
        // Get parameters for sorting and paging from the request
        $this->gridParams['sidx'] = $request->input('sortname', $this->gridParams['sidx']);
        $this->gridParams['sord'] = $request->input('sortorder', $this->gridParams['sord']);
        // 'rows' is a common parameter name for rows per page in jqGrid
        $this->gridParams['limit'] = $request->input('rows', $this->gridParams['limit']);

        // Call the pagination method with the ID to focus on
        $pageData = Penjualan::getPenjualanPagination($this->gridParams, $focusId);

        return response()->json($pageData);
    }

    /**
     * Display the main sales management view.
     *
     * This method returns the primary view for sales, which includes the jqGrid
     * for displaying sales data. It also passes necessary data, like the list
     * of customers, to the view.
     *
     * @return \Illuminate\View\View The sales index view.
     */
    public function index()
    {
        $pelanggans = Pelanggan::getDataPelanggan();
        return \view('penjualan.index', \compact('pelanggans'));
    }

    /**
     * Get master data for the Penjualan (Sales) jqGrid.
     *
     * This method fetches the master sales data based on the grid parameters
     * (sorting, filtering, searching) and returns it as a JSON response
     * suitable for populating the jqGrid.
     *
     * @return JsonResponse A JSON response containing the master sales data.
     */
    public function master()
    {
        $this->gridParams['global_search'] = \request()->input('global_search', '');
        $this->gridParams['_search'] = \request()->input('_search', 'false');

        if (request()->input('_search') == 'true') {
            $filterJson = request()->input('filters');
            $decoded = json_decode($filterJson, true);
            if (isset($decoded['rules'])) {
                $this->filters = $decoded['rules'];
            }

            $this->gridParams['filters'] = $this->filters;
        }
        $masterData = Penjualan::getGridMaster($this->gridParams);
        return response()->json($masterData);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return void
     */
    public function create()
    {
        //
    }

    /**
     * Validates and persists a new sale and its line items.
     *
     * This method uses the StorePenjualanRequest for validation, then calls the
     * model to create the sales record. On success, it returns a JSON response
     * containing the updated grid data, with the new record focused.
     *
     * @param StorePenjualanRequest $request The request object containing validated sales data.
     * @return JsonResponse A JSON response with the updated grid data.
     */
    public function store(StorePenjualanRequest $request): JsonResponse
    {
        try {
            // Call business logic from the model.
            // $request->validated() returns the data that has passed validation.
            $penjualan = Penjualan::createPenjualan($request->validated());

            // Call the method to get the position of the data
            return $this->getGridResponse($request, $penjualan->id);

        } catch (\Exception $e) {
            // Catch exceptions from the service and return an error response
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified sales resource.
     *
     * Retrieves and returns the data for a specific sale, formatted for editing
     * in a form.
     *
     * @param string $id The ID of the sales record to retrieve.
     * @return JsonResponse A JSON response containing the sales data or an error.
     */
    public function show(string $id)
    {
        if ($id === null) {
            \abort(403, 'Cannot be accessed');
        }

        try {
            // Call the model to retrieve and format the data
            $data = Penjualan::getPenjualanForEdit($id);

            // Return the formatted data as JSON
            return response()->json($data);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            // Handle if data is not found specifically
            return response()->json(['error' => 'Data not found.'], 404);
        } catch (\Exception $e) {
            // Handle other general errors
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param string $id The ID of the resource to edit.
     * @return void
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified sales resource in storage.
     *
     * This method validates the incoming request data, updates the specified
     * sales record, and returns a JSON response with the updated grid data,
     * focusing on the updated record.
     *
     * @param StorePenjualanRequest $request The request object containing validated sales data.
     * @param string $id The ID of the sales record to update.
     * @return JsonResponse A JSON response with the updated grid data.
     */
    public function update(StorePenjualanRequest $request, string $id): JsonResponse
    {
        try {
            $penjualan = Penjualan::updatePenjualan($id, $request->validated());

            return $this->getGridResponse($request, $penjualan->id);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified sales resource from storage.
     *
     * This method deletes a sales record. Before deletion, it determines the
     * next record to focus on in the grid to provide a smooth user experience.
     * After deletion, it returns the updated grid data.
     *
     * @param Request $request The current HTTP request.
     * @param string $id The ID of the sales record to delete.
     * @return JsonResponse A JSON response with the updated grid data.
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        try {
            // 1. Call the static method DIRECTLY from the Penjualan Model
            //    to determine the next focus ID.
            $this->gridParams['sidx'] = $request->input('sortname', $this->gridParams['sidx']);
            $this->gridParams['sord'] = $request->input('sortorder', $this->gridParams['sord']);
            $this->gridParams['limit'] = (int) $request->input('rows', $this->gridParams['limit']);
            $idSelanjutnya = Penjualan::getIdTerdekat($this->gridParams, $id);

            // 2. Continue the deletion process through the model
            Penjualan::deletePenjualan($id);

            // 3. Return the grid response with focus on the ID we got
            return $this->getGridResponse($request, $idSelanjutnya);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Validates the input for an export request via AJAX.
     *
     * This private helper method checks if the start and end ranges for the export
     * are valid based on the total number of records.
     *
     * @param Request $request The HTTP request containing the export parameters.
     * @return JsonResponse|true Returns a JSON response with validation errors or true if valid.
     */
    private function validateExport(Request $request)
    {
        // Calculate total records based on the filter sent for the 'max' rule
        $totalRecords = $request->input('record', 0);

        // Apply validation rules
        $validator = Validator::make($request->all(), [
            'start_range' => ['required', 'integer', 'min:1', 'lte:end_range'],
            'end_range'   => ['required', 'integer', 'min:1', 'max:' . $totalRecords],
        ], [
            // Custom error messages
            'start_range.required' => 'Start range is required.',
            'start_range.min'      => 'Must start from 1 or more.',
            'start_range.lte'      => 'Start value cannot be greater than end value.',
            'end_range.required'   => 'End range is required.',
            'end_range.max'        => 'The maximum is only up to ' . $totalRecords . ' records.',
        ]);

        // If validation fails, return a 422 JSON response
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // If successful, return true
        return true;
    }

    /**
     * Handles the request to export data to various formats (Excel, PDF, etc.).
     *
     * This method validates the export parameters and then delegates the export
     * process to the appropriate method based on the requested format.
     *
     * @param Request $request The HTTP request containing export parameters.
     * @param string $mode The desired export format ('excel', 'pdf', etc.).
     * @return \Symfony\Component\HttpFoundation\StreamedResponse|\Illuminate\Http\JsonResponse|void
     */
    public function export(Request $request, string $mode)
    {
        // record parameter from the request, same as in the grid
        $totalRecords = $request->json('record', 0);

        // Apply validation rules
        $validator = Validator::make($request->json()->all(), [
            'start_range' => ['required', 'integer', 'min:1', 'lte:end_range'],
            'end_range'   => ['required', 'integer', 'min:1', 'max:' . $totalRecords],
        ], [
            // Custom error messages
            'start_range.required' => 'Start range is required.',
            'start_range.min'      => 'Must start from 1 or more.',
            'start_range.lte'      => 'Start value cannot be greater than end value.',
            'end_range.required'   => 'End range is required.',
            'end_range.max'        => 'The maximum is only up to ' . $totalRecords . ' records.',
        ]);

        // If validation fails, return a 422 JSON response
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $params = $request->json()->all();

        if ($mode === 'excel') {
            return $this->excel($params);

        } else if ($mode === 'pdf') {
            return $this->pdf();
        }
    }

    /**
     * Generates and streams an Excel file of the sales report.
     *
     * This private helper method fetches the sales data based on the provided
     * parameters, creates an Excel spreadsheet using PhpSpreadsheet, and streams
     * the file to the user for download.
     *
     * @param array $params The parameters for filtering the export data.
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    private function excel(array $params)
    {
        // 1. Retrieve data from the database USING THE SAME FILTER LOGIC
        // We will create a new method in the model for this, so there is no pagination
        $dataPenjualan = Penjualan::getDataForExport($params);

        // 2. Create a new Spreadsheet object
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Sales Report');

        // 3. Write Table Header
        $rowNum = 1; // Start from the first row

        // 4. Loop for each SALES transaction
        foreach ($dataPenjualan as $penjualan) {
            // --- WRITE HEADER FOR EACH TRANSACTION ---
            $sheet->mergeCells('A' . $rowNum . ':B' . $rowNum);
            $sheet->setCellValue('A' . $rowNum, 'Proof No.');
            $sheet->setCellValue('C' . $rowNum, $penjualan->no_bukti);
            $sheet->getStyle('A' . $rowNum . ':C' . $rowNum)->getFont()->setBold(true);
            $rowNum++;

            $sheet->mergeCells('A' . $rowNum . ':B' . $rowNum);
            $sheet->setCellValue('A' . $rowNum, 'Date');
            $sheet->setCellValue('C' . $rowNum, $penjualan->tgl_bukti->format('d M Y'));
            $rowNum++;

            $sheet->mergeCells('A' . $rowNum . ':B' . $rowNum);
            $sheet->setCellValue('A' . $rowNum, 'Customer');
            $sheet->setCellValue('C' . $rowNum, $penjualan->pelanggan->nama_pelanggan ?? 'N/A');
            $rowNum++;

            // Add a space before the detail table
            $rowNum++;

            // --- WRITE HEADER FOR DETAIL TABLE ---
            $sheet->setCellValue('B' . $rowNum, 'Item Name');
            $sheet->setCellValue('C' . $rowNum, 'Qty');
            $sheet->setCellValue('D' . $rowNum, 'Price');
            $sheet->setCellValue('E' . $rowNum, 'Total');
            $sheet->getStyle('B' . $rowNum . ':E' . $rowNum)->getFont()->setBold(true);
            $rowNum++;

            $startRowDetail = $rowNum; // Mark the starting row of the detail

            // 5. Loop for each ITEM DETAIL within the sale
            foreach ($penjualan->details as $detail) {
                $sheet->setCellValue('B' . $rowNum, $detail->nama_barang);
                $sheet->setCellValue('C' . $rowNum, $detail->qty);
                $sheet->setCellValue('D' . $rowNum, $detail->harga);
                // Use an Excel formula to calculate the total per row
                $sheet->setCellValue('E' . $rowNum, "=C" . $rowNum . "*D" . $rowNum);

                // Apply number formatting
                $sheet->getStyle('C' . $rowNum)->getNumberFormat()->setFormatCode('#,##0');
                $sheet->getStyle('D' . $rowNum . ':E' . $rowNum)->getNumberFormat()->setFormatCode('"Rp "#,##0.00');

                $rowNum++;
            }
            $endRowDetail = $rowNum - 1; // Mark the ending row of the detail

            // --- WRITE GRAND TOTAL ---
            if ($startRowDetail <= $endRowDetail) {
                $formulaGrandTotal = "=SUM(E" . $startRowDetail . ":E" . $endRowDetail . ")";
                $sheet->mergeCells('B' . $rowNum . ':D' . $rowNum);
                $sheet->setCellValue('B' . $rowNum, 'Grand Total:');
                $sheet->setCellValue('E' . $rowNum, $formulaGrandTotal);
                $sheet->getStyle('B' . $rowNum . ':E' . $rowNum)->getFont()->setBold(true);
                $sheet->getStyle('E' . $rowNum)->getNumberFormat()->setFormatCode('"Rp "#,##0.00');
                $sheet->getStyle('B' . $rowNum)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
            }

            // Add 2 space rows as a separator between sales data
            $rowNum += 2;
        }

        // 6. Set column width automatically
        foreach (range('A', 'H') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        // 7. Prepare Writer and send the file to the browser
        $writer = new Xlsx($spreadsheet);
        $fileName = 'sales-report-' . date('Ymd_His') . '.xlsx';

        // Return as StreamedResponse so it can be caught as a blob
        return response()->stream(
            function () use ($writer) {
                $writer->save('php://output');
            },
            200,
            [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            ]
        );
    }

    /**
     * Displays the PDF report page.
     *
     * This method fetches the necessary data for the PDF report based on request
     * parameters and passes it to a view that renders the Stimulsoft report viewer.
     *
     * @param Request $request The HTTP request containing filter parameters.
     * @return \Illuminate\View\View The view for displaying the PDF report.
     */
    public function showPdfReport(Request $request)
    {
        // if there are no start_range and end_range params, return 403
        if (!$request->filled('start_range') && !$request->filled('end_range')) {
            abort(403, "Page cannot be accessed!");
        }

        // Get all filter parameters from the request
        $params = $request->all();

        // Get the transformed data for Stimulsoft
        $laporanData = $this->getPdfData($params);

        // Return the Blade view and send the report data into it
        return view('report.index', [
            'laporanJSON' => json_encode($laporanData) // Send as a JSON string
        ]);
    }

    /**
     * Helper method to retrieve and format data for the PDF report.
     *
     * This method fetches sales data and transforms it into a "flat" structure
     * suitable for use with the Stimulsoft reporting tool.
     *
     * @param array $params The parameters for filtering the report data.
     * @return array The formatted data for the PDF report.
     */
    private function getPdfData(array $params)
    {
        $dataPenjualan = Penjualan::getDataForExport($params);
        $laporanData = [];
        foreach ($dataPenjualan as $penjualan) {
            if ($penjualan->details->isEmpty()) continue;
            foreach ($penjualan->details as $detail) {
                $laporanData[] = [
                    'id_penjualan'   => $penjualan->id,
                    'no_bukti'       => $penjualan->no_bukti,
                    'tgl_bukti'      => $penjualan->tgl_bukti->format('d-m-Y'),
                    'nama_pelanggan' => $penjualan->pelanggan->nama_pelanggan ?? 'N/A',
                    'nama_barang'    => $detail->nama_barang,
                    'qty'            => (float)$detail->qty,
                    'harga'          => (float)$detail->harga,
                ];
            }
        }
        return ['DataPenjualan' => $laporanData];
    }

    /**
     * Prepares data for PDF export.
     *
     * This method is intended to handle the server-side logic for PDF generation.
     * Currently, it returns a simple success message.
     *
     * @return JsonResponse A JSON response indicating the status.
     */
    private function pdf()
    {
        // The structure is adjusted to match Stimulsoft's needs
        return response()->json([
            'message' => 'Ok'
        ]);
    }
}