<?php

namespace App\Models;

use Illuminate\Support\Facades\DB; // Import DB facade untuk Query Builder
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class PenjualanDetail
 *
 * Represents the 'penjualan_details' table. This model stores the individual
 * line items for each sales transaction.
 *
 * @package App\Models
 * @property int $id
 * @property int $penjualan_id
 * @property string $nama_barang
 * @property int $qty
 * @property float $harga
 * @property-read \App\Models\Penjualan $penjualan
 * @property-read float $total
 */
class PenjualanDetail extends Model
{
    /** @use HasFactory<\Database\Factories\PenjualanDetailFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = ['penjualan_id', 'nama_barang', 'qty', 'harga'];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Get the parent sale that this detail belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function penjualan()
    {
        return $this->belongsTo(Penjualan::class, 'penjualan_id');
    }

    /**
     * Accessor for the total price of the line item.
     *
     * @return float The result of quantity multiplied by price.
     */
    public function getTotalAttribute()
    {
        return $this->qty * $this->harga;
    }
    
    /**
     * Fetches, filters, and paginates data for the detail jqGrid.
     *
     * This static method builds a query using the Query Builder to get the
     * line items for a specific sale, applying any sorting, filtering, and
     * pagination parameters provided.
     *
     * @param array $params An array of parameters for the grid, including 'penjualan_id'.
     * @return array An array formatted for consumption by a jqGrid.
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
        $sidx = $params['sidx'] ?? 'nama_barang';
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
                            if ($field == 'total') {
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
                    $detail->nama_barang,
                    $detail->qty,
                    $detail->harga, 
                    $detail->total,
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