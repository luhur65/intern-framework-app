<?php

namespace App\Models;

use Illuminate\Support\Facades\DB; // Import DB facade untuk Query Builder
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PenjualanDetail extends Model
{
    /** @use HasFactory<\Database\Factories\PenjualanDetailFactory> */
    use HasFactory;

    protected $fillable = ['penjualan_id', 'nama_barang', 'qty', 'harga'];
    // protected $primaryKey = 'id_detail';
    public $timestamps = false;

    public function penjualan()
    {
        return $this->belongsTo(Penjualan::class, 'penjualan_id');
    }

    public function getTotalAttribute()
    {
        return $this->qty * $this->harga;
    }
    
    /**
     * Static a query to get grid data.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array $params
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function getGridDetail(array $params)
    {
        // Buat query dasar dengan Query Builder
        $baseQuery = DB::table('penjualan_details')
            ->select(
                'penjualan_details.id',
                'penjualan_details.nama_barang',
                'penjualan_details.qty',
                'penjualan_details.harga',
                'penjualan_details.penjualan_id',
                DB::raw('penjualan_details.qty * penjualan_details.harga as total')
            );
            
        // Pagination
        $sidx = $params['sidx'] ?? 'id_detail';
        $sord = $params['sord'] ?? 'asc';
        $limit = $params['limit'] ?? 10;
        $page = $params['page'] ?? 1;

        // Filter berdasarkan penjualan_id
        // --- Pencarian ---
        $globalSearch = $params['global_search'] ?? '';
        $search = $params['_search'] ?? 'false';

        if ($globalSearch) {
            $baseQuery->where(function ($q) use ($globalSearch) {
                $q->where('nama_barang', 'like', "%{$globalSearch}%")
                    ->orWhere('qty', 'like', "%{$globalSearch}%")
                    ->orWhere('harga', 'like', "%{$globalSearch}%");
            });
        }

        if ($search == 'true') {
            $filters = $params['filters'] ?? [];
            // Asumsi $filters adalah array PHP, bukan JSON string
            if (!empty($filters)) {
                $baseQuery->where(function ($q) use ($filters) {
                    foreach ($filters as $filter) {
                        $field = $filter['field'] ?? null;
                        $data = $filter['data'] ?? null;

                        if ($field && $data !== null) {
                            // if ($field == 'tgl_bukti') {
                            //     // Untuk pencarian tgl_bukti, kita bisa gunakan format yang sesuai
                            //     $q->whereRaw("DATE_FORMAT(penjualans.tgl_bukti, '%d-%m-%Y') LIKE ?", ["%$data%"]);
                            //     continue; // Skip ke iterasi berikutnya karena sudah di-handle
                            // }
                            // pencarian berdasarkan field dan data
                            if ($field == 'total') {
                                // Filter langsung ke ekspresi qty * harga
                                // $q->whereRaw('(qty * harga) LIKE ?', ["%$data%"]);
                                // $q->whereRaw("FORMAT(qty * harga, 2, 'id_ID') LIKE ?", ["%$data%"]);
                                // $q->whereRaw("REPLACE(FORMAT(qty * harga, 2), ',', '') LIKE ?", ["%" . str_replace(',', '', $data) . "%"]);
                                $q->whereRaw("FORMAT(qty * harga, 2) LIKE ?", ["%$data%"]);

                            } elseif ($field == 'harga') {
                                $q->whereRaw("FORMAT(harga, 2) LIKE ?", ["%$data%"]);

                            } elseif ($field == 'qty') {
                                $q->whereRaw("FORMAT(qty, 2) LIKE ?", ["%$data%"]);

                            } else {
                                $q->where($field, 'like', "%{$data}%");
                            }
                        }
                    }
                });
            }
        }

        $baseQuery->where('penjualan_id', $params['penjualan_id']);

        // Clone query untuk menghitung total
        $countQuery = clone $baseQuery;
        $count = $countQuery->count();

        if ($count > 0) {
            $total_pages = ceil($count / $limit);
        } else {
            $total_pages = 0;
        }

        if ($page > $total_pages) {
            $page = $total_pages;
        }

        $start = $limit * $page - $limit; 

        if ($start < 0) $start = 0;

        $data = $baseQuery->orderBy($sidx, $sord)
                     ->offset($start)
                     ->limit($limit);

        // format data untuk grid (misalnya jqGrid)
        $rows = $data->get()->map(function ($detail) {
            return [
                'id' => $detail->id,
                'cell' => [
                    // $detail->id_detail,
                    $detail->nama_barang,
                    $detail->qty,
                    $detail->harga, 
                    $detail->total, // Total dihitung dari qty * harga
                ],
            ];
        });

        return [
                'page' => $page,
                'total' => $total_pages,
                'records' => $count,
                'rows' => $rows->toArray(),
            ];
    }
}
