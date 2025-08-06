<?php

namespace App\Http\Controllers;

use App\Models\Pelanggan;
use App\Models\Penjualan;
use Illuminate\Http\Request;

class PenjualanController extends Controller
{
    protected $gridParams = [];
    protected $filters = [];

    public function __construct()
    {
        
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
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validasi input
        $request->validate([
            'no_bukti' => 'required|string|max:255',
            'tgl_bukti' => 'required|date',
            'nama_pelanggan' => 'required|exists:pelanggans,id',
            'barang' => 'required|array',
            'barang.*.nama_barang' => 'required|string|max:255',
            'barang.*.qty' => 'required|integer|min:1',
            'barang.*.harga' => 'required|numeric|min:0',
            'barang.*.total' => 'required|numeric|min:0',
        ]);

        // Simpan data penjualan
        // $penjualan = Penjualan::create($request->all());

        // return response()->json($penjualan, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return response()->json([
            'id' => $id,
            'no_bukti' => 'BKT001',
            'tgl_bukti' => '2023-10-01',
            'nama_pelanggan' => '4', // ID Pelanggan
            'barang' => [
                ['nama_barang' => 'Barang A', 'qty' => 2, 'harga' => 10000, 'total' => 20000],
                ['nama_barang' => 'Barang B', 'qty' => 1, 'harga' => 15000, 'total' => 15000],
            ],
        ])->setStatusCode(200, "OK");
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
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
