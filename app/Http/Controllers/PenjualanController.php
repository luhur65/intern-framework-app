<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePenjualanRequest;
use App\Interfaces\PenjualanServiceInterface;
use App\Models\Pelanggan;
use App\Models\Penjualan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
// use Illuminate\Support\Facades\DB as MySQLDB;

class PenjualanController extends Controller
{
    private PenjualanServiceInterface $penjualanService;
    protected $gridParams = [];
    protected $filters = [];

    /**
     * Constructor ini SANGAT PENTING.
     * Ia akan otomatis dijalankan oleh Laravel untuk "menyuntikkan"
     * service ke dalam controller.
     * Jika ini tidak ada, maka properti $penjualanService akan kosong.
     */
    public function __construct(PenjualanServiceInterface $penjualanService)
    {

        $this->penjualanService = $penjualanService;
        
        // Inisialisasi parameter grid
        // sidx: field untuk sorting, sord: arah sorting (asc/desc), page: halaman, limit: jumlah data per halaman
        // start: offset untuk pagination, global_search: untuk pencarian global, search: untuk filter pencarian
        $this->gridParams = [
            'sidx'   => \request()->input('sidx', 'id'),
            'sord'   => \request()->input('sord', 'asc'),
            'page'   => (int) \request()->input('page', 1),
            'limit'  => (int) \request()->input('rows', 10),
        ];

        // Hitung start 
        $this->gridParams['start'] = $this->gridParams['limit'] * ($this->gridParams['page'] - 1);

        $this->gridParams['global_search'] = \request()->input('global_search', '');
        $this->gridParams['_search'] = \request()->input('_search', 'false');

        if (request()->input('_search') == 'true') {
            // echo "search true";
            // $this->gridParams['filters'] = \request()->input('filters', []);
            $filterJson = request()->input('filters');
            $decoded = json_decode($filterJson, true);
            if (isset($decoded['rules'])) {
                $this->filters = $decoded['rules'];
            }

            $this->gridParams['filters'] = $this->filters;
        }

        // dd($this->gridParams);

    }

    /**
     * Helper method untuk mengambil data grid dan mengembalikannya sebagai JSON.
     *
     * @param Request $request Request saat ini untuk mengambil parameter grid.
     * @param int|null $focusId ID dari baris yang ingin disorot/difokuskan.
     * @return JsonResponse
     */
    private function getGridResponse(Request $request, ?int $focusId): JsonResponse
    {
        // Ambil parameter untuk sorting dan paging dari request
        $this->gridParams['sidx'] = $request->input('sortname', $this->gridParams['sidx']);
        $this->gridParams['sord'] = $request->input('sortorder', $this->gridParams['sord']);
        // 'rows' adalah nama parameter umum untuk jumlah baris per halaman di jqGrid
        $this->gridParams['limit'] = $request->input('rows', $this->gridParams['limit']);

        // Panggil metode paginasi dengan ID yang akan difokuskan
        $pageData = Penjualan::getPenjualanPagination($this->gridParams, $focusId);

        return response()->json($pageData);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // $urlMaster = route('penjualan.master');
        // $urlDetail = route('penjualan.detail.getDetail');
        // $querySQL = Penjualan::query()->gridMaster($this->gridParams)->toSql();

        $pelanggans = Pelanggan::getDataPelanggan();
        return \view('penjualan.index', [
            'pelanggans' => $pelanggans,
        ]);
    }
    /**
     * Get master data for Penjualan.
     */
    public function master()
    {
        $masterData = Penjualan::getGridMaster($this->gridParams);
        return response()->json($masterData);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Simpan data penjualan baru.
     *
     * @param StorePenjualanRequest $request
     * @return JsonResponse
     */
    public function store(StorePenjualanRequest $request): JsonResponse
    {
        try {
            // Panggil service untuk menjalankan logika bisnis.
            // $request->validated() akan mengembalikan data yang sudah lolos validasi.
            $penjualan = $this->penjualanService->createPenjualan($request->validated());

            // panggil method untuk posisi data
            return $this->getGridResponse($request, $penjualan->id);

        } catch (\Exception $e) {
            // Tangkap exception dari service dan kembalikan response error
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            // Panggil service untuk mengambil dan memformat data
            $data = $this->penjualanService->getPenjualanForEdit($id);

            // Kembalikan data yang sudah diformat sebagai JSON
            return response()->json($data);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            // Tangani jika data tidak ditemukan secara spesifik
            return response()->json(['error' => 'Data tidak ditemukan.'], 404);
        } catch (\Exception $e) {
            // Tangani error umum lainnya
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StorePenjualanRequest $request, string $id): JsonResponse
    {
        try {
            $penjualan = $this->penjualanService->updatePenjualan($id, $request->validated());

            return $this->getGridResponse($request, $penjualan->id);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        // \dd($request->all());
        try {

            // 2. Panggil metode statis LANGSUNG dari Model Penjualan
            //    untuk menentukan ID fokus berikutnya.
            $this->gridParams['sidx'] = $request->input('sortname', $this->gridParams['sidx']);
            $this->gridParams['sord'] = $request->input('sortorder', $this->gridParams['sord']);
            $this->gridParams['limit'] = (int) $request->input('rows', $this->gridParams['limit']);
            $idSelanjutnya = Penjualan::getIdTerdekat($this->gridParams, $id);

            // 3. Lanjutkan proses penghapusan melalui service
            $this->penjualanService->deletePenjualan($id);

            // 4. Kembalikan response grid dengan fokus ke ID yang sudah kita dapatkan
            return $this->getGridResponse($request, $idSelanjutnya);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
