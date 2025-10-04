<?php

namespace App\Http\Controllers\Api;

use App\Models\Penjualan;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;

/**
 * Class ApiController
 *
 * Handles API requests for the application.
 * @package App\Http\Controllers\Api
 */
class ApiController extends Controller
{
    /**
     * Handles the request to retrieve sales data for a jqGrid.
     *
     * This method retrieves and filters sales data based on the parameters
     * provided in the request, such as pagination, sorting, and searching.
     * The response is formatted to be compatible with the jqGrid plugin.
     *
     * @param Request $request The HTTP request object, containing query parameters.
     * @return JsonResponse A JSON response containing the formatted sales data for the jqGrid.
     */
    public function index(Request $request): JsonResponse
    {
        // parameter default untuk jqgrid
        $page  = $request->input('page', 1);
        $limit = $request->input('rows', 10);
        $sidx  = $request->input('sidx', 'penjualan.tbl_penjualan.id_penjualan');
        $sord  = $request->input('sord', 'DESC');

        // global_search
        $globalSearch = $request->input('global_search', '');

        // base query
        $query = DB::table('penjualan.tbl_penjualan')
            ->select(
                'penjualan.tbl_penjualan.id_penjualan',
                'penjualan.tbl_penjualan.no_bukti',
                'penjualan.tbl_penjualan.tgl_bukti',
                'penjualan.tbl_pelanggan.nama_pelanggan'
            )
            ->leftJoin(
                'penjualan.tbl_pelanggan',
                'penjualan.tbl_penjualan.pelanggan_id',
                '=',
                'penjualan.tbl_pelanggan.id'
            );

        // fitur search AND
        if ($request->boolean('_search') && $request->filled('filters')) {
            $filters = json_decode($request->input('filters'), true);

            if (!empty($filters['rules'])) {
                foreach ($filters['rules'] as $rule) {
                    $field = $rule['field'];
                    $data  = $rule['data'];

                    if ($field == 'tgl_bukti' && !empty($data)) {
                        $query->whereRaw("DATE_FORMAT($field, '%d-%m-%Y') LIKE ?", ["%$data%"]);
                    } else {
                        $query->where($field, 'like', "%$data%");
                    }
                }
            }
        }

        // global search
        if (!empty($globalSearch)) {
            $query->where(function ($q) use ($globalSearch) {
                $q->where('no_bukti', 'like', "%$globalSearch%")
                    ->orWhereRaw("DATE_FORMAT(tgl_bukti, '%d-%m-%Y') LIKE ?", ["%$globalSearch%"])
                    ->orWhere('nama_pelanggan', 'like', "%$globalSearch%");
            });
        }

        // total records
        $count = $query->count();

        $total_pages = $count > 0 ? ceil($count / $limit) : 0;
        $start       = $limit * ($page - 1);

        // ambil data dengan pagination
        $penjualans = $query
            ->orderBy($sidx, $sord)
            // ->offset($start)
            // ->limit($limit)
            ->get();

        return response()->json([
            "total"   => $total_pages,
            "page"    => $page,
            "records" => $count,
            "rows"    => $penjualans,
        ]);
    }

    /**
     * Menangani permintaan untuk mengambil data penjualan.
     * Metode ini akan menerima parameter filter, sort, dan paginasi.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function v2(Request $request): JsonResponse
    {
        try {
            // Kita gunakan kembali metode yang sudah ada di Model untuk mengambil
            // data yang sudah difilter dan diurutkan.
            $dataPenjualan = Penjualan::getGridMaster($request->all());

            // Kembalikan data dalam format JSON yang standar
            return response()->json([
                'success' => true,
                'data' => $dataPenjualan,
            ]);
        } catch (Exception $e) {
            // Jika terjadi error, kembalikan respons JSON yang informatif
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data dari server.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
