<?php

namespace App\Models;

use Illuminate\Support\Facades\DB; // Import DB facade untuk Query Builder
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Penjualan extends Model
{
    /** @use HasFactory<\Database\Factories\PenjualanFactory> */
    use HasFactory;

    // protected $table = 'penjualans';
    protected $fillable = ['no_bukti', 'tgl_bukti', 'pelanggan_id'];
    // protected $primaryKey = 'id_penjualan';
    public $timestamps = false;

    protected $casts = [
        'tgl_bukti' => 'date', 
    ];

    public function pelanggan()
    {
        return $this->belongsTo(Pelanggan::class, 'pelanggan_id');
    }

    public function details()
    {
        return $this->hasMany(PenjualanDetail::class, 'penjualan_id');
    }

    public function getTotalAttribute()
    {
        return $this->details->sum(function ($detail) {
            return $detail->qty * $detail->harga;
        });
    }

    public function getFormattedDateAttribute()
    {
        return \Carbon\Carbon::parse($this->tgl_bukti)->format('d-m-Y');
    }

    /**
     * Scope a query to get grid data - versi Query Builder.
     * Tetap mengembalikan data paginasi seperti versi Eloquent.
     * Ini bukan chunking, ini tetap paginasi.
     *
     * @param \Illuminate\Database\Query\Builder $query (sebenarnya tidak digunakan karena kita bikin query baru)
     * @param array $params
     * @return array
     */
    public static function getGridMaster(array $params, $limitOn = true) 
    {
        

        $baseQuery = DB::table('penjualans')
            ->join('pelanggans', 'penjualans.pelanggan_id', '=', 'pelanggans.id')
            ->select(
                'penjualans.id',
                'penjualans.no_bukti',
                'penjualans.tgl_bukti',
                'pelanggans.nama_pelanggan'
                // Total akan dihitung terpisah
            );


        // --- Pencarian ---
        $globalSearch = $params['global_search'] ?? '';
        $search = $params['_search'] ?? 'false';

        if ($globalSearch) {
            $baseQuery->where(function ($q) use ($globalSearch) {
                $q->where('penjualans.no_bukti', 'like', "%{$globalSearch}%")
                    // ->orWhere('penjualans.tgl_bukti', 'like', "%{$globalSearch}%")
                    ->orWhereRaw("DATE_FORMAT(penjualans.tgl_bukti, '%d-%m-%Y') LIKE ?", ["%{$globalSearch}%"])
                    ->orWhere('pelanggans.nama_pelanggan', 'like', "%{$globalSearch}%");
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
                            if ($field == 'tgl_bukti') {
                                // Untuk pencarian tgl_bukti, kita bisa gunakan format yang sesuai
                                $q->whereRaw("DATE_FORMAT(penjualans.tgl_bukti, '%d-%m-%Y') LIKE ?", ["%$data%"]);
                                continue; // Skip ke iterasi berikutnya karena sudah di-handle
                            }
                            // Tambahkan kondisi pencarian lain jika perlu
                            $q->where($field, 'like', "%{$data}%");
                        }
                    }
                });
            }
        }

        // --- Pagination ---
        $sidx = $params['sidx'] ?? 'penjualans.id'; // Default sort
        $sord = $params['sord'] ?? 'asc';

        // Jika limitOn false, kita tidak akan menggunakan limit dan offset
        if (!$limitOn) {
            return $baseQuery
                ->orderBy($sidx, $sord) 
                ->pluck('penjualans.id')
                ->toArray();
        }

        $limit = (int)($params['limit'] ?? 10);
        $page = (int)($params['page'] ?? 1);
        $start = $params['start'] ?? (($page - 1) * $limit);

        // Clone query untuk menghitung total
        $countQuery = clone $baseQuery;
        $count = $countQuery->count();

        $total_pages = $count > 0 ? ceil($count / $limit) : 0;
        if ($page > $total_pages) $page = $total_pages;
        $start = max(0, ($page - 1) * $limit); // Recalculate start

        // Untuk export data
        if (isset($params['start_range']) && isset($params['end_range'])) {

            $startRange = $params['start_range'];
            $endRange = $params['end_range'];

            if ($startRange > 0 && $endRange > 0) {
                $offset = $startRange - 1;
                $countToFetch = $endRange - $startRange + 1;

                // offset() dulu untuk melewati, baru limit() untuk mengambil
                // return $baseQuery->orderBy($sidx, $sord)->offset($offset)->limit($countToFetch)->get();
                return $baseQuery // <-- Muat relasi
                    ->orderBy($sidx, $sord)
                    ->offset($offset)
                    ->limit($countToFetch)
                    ->get();

                // \dd($data);
            }

        }

        // Dapatkan data dengan limit dan offset
        $data = $baseQuery->orderBy($sidx, $sord)
            ->offset($start)
            ->limit($limit)
            ->get();

        // --- Hitung Total untuk setiap Penjualan ---
        // Karena kita tidak pakai Eloquent, kita harus hitung manual
        $data->transform(function ($item) {
            // Query untuk mendapatkan detail dan hitung total
            // $details = DB::table('penjualan_details')
            //     ->where('penjualan_id', $item->id)
            //     ->get();

            // $total = $details->sum(function ($detail) {
            //     return $detail->qty * $detail->harga;
            // });

            // $item->total = $total;
            // Format tanggal jika perlu
            $item->formatted_tgl_bukti = \Carbon\Carbon::parse($item->tgl_bukti)->format('d-m-Y');
            return $item;
        });

        // Format data untuk grid (misalnya jqGrid)
        $rows = $data->map(function ($item) {
            return [
                'id' => $item->id,
                'cell' => [
                    $item->id,
                    $item->no_bukti,
                    $item->formatted_tgl_bukti, // Gunakan tanggal yang diformat
                    $item->nama_pelanggan,
                ],
            ];
        });

        return [
            'page' => $page,
            'total' => $total_pages,
            'records' => $count,
            'rows' => $rows->toArray(), // Konversi ke array
            //'query' => $baseQuery->toSql(), // Untuk debugging, bisa dihapus nanti
            //'bindings' => $baseQuery->getBindings(), // Untuk debugging, bisa dihapus
        ];
    }


    public static function getPenjualanPagination(array $params, $id = 0)
    {
        // Panggil fungsi getGridMaster untuk mendapatkan data 
        $ids = self::getGridMaster($params, false);

        // Cari posisi id
        $rowIndex = array_search($id, $ids);

        // var_dump($rowIndex);
        $rowNumber = $rowIndex !== false ? $rowIndex + 1 : 1;
        // $rowNumber = $rowIndex + 1;
        $limit = isset($params['limit']) ? intval($params['limit']) : 10;
        $page = ceil($rowNumber / $limit);

        return [
            "id" => $id,
            "page" => $page,
        ];
        

    }

    public static function getIDTerdekat(array $params, $deletedId = 0)
    {
        // Panggil fungsi getGridMaster untuk mendapatkan data 
        $ids = self::getGridMaster($params, false);

        // Cari posisi ID yang dihapus
        $posisiTerhapus = array_search($deletedId, $ids);

        // Jika ID tidak ditemukan
        if ($posisiTerhapus === false) {
            return !empty($ids) && $ids[0] != $deletedId ? $ids[0] : (isset($ids[1]) ? $ids[1] : null);
        }

        // Hapus ID dari array dan re-index
        unset($ids[$posisiTerhapus]);
        $ids = array_values($ids);

        // Cari ID terdekat
        if (isset($ids[$posisiTerhapus])) {
            return $ids[$posisiTerhapus]; // Posisi yang sama
        } elseif ($posisiTerhapus > 0 && isset($ids[$posisiTerhapus - 1])) {
            return $ids[$posisiTerhapus - 1]; // Posisi sebelumnya
        } else {
            return !empty($ids) ? $ids[0] : null; // Fallback ke pertama
        }
    }

    public static function getDataForExport(array $params)
    {
        return self::getGridMaster($params);
        // return $data;
        // \dd($data);
    }
}
