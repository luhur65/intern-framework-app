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

    private const NO_BUKTI_PREFIX = 'BRG-NO-';

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
     * Menghasilkan nomor bukti berikutnya yang tersedia menggunakan Query Builder.
     *
     * @return string
     */
    public function getNextNoBukti(): string
    {
        // Cari no_bukti terakhir menggunakan Query Builder dengan filter
        $lastPenjualan = DB::table('penjualans')
            ->where('no_bukti', 'like', self::NO_BUKTI_PREFIX . '%')
            ->orderBy('no_bukti', 'desc')
            ->first();

        if (!$lastPenjualan) {
            // Jika tidak ada data sama sekali, mulai dari 1
            return self::NO_BUKTI_PREFIX . '0001';
        }

        // Ambil bagian angka dari string (misal: 'BRG-NO-0021' -> '0021')
        $lastNumber = (int) substr($lastPenjualan->no_bukti, 7);

        // Tambah 1
        $newNumber = $lastNumber + 1;

        // Format kembali dengan padding nol di depan (misal: 22 -> '0022')
        return self::NO_BUKTI_PREFIX . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Membuat data penjualan baru dengan nomor bukti yang aman.
     *
     * @param array $data Data yang sudah divalidasi.
     * @return self
     */
    public static function createPenjualan(array $data): self
    {
        return DB::transaction(function () use ($data) {
            $lastPenjualan = self::where('no_bukti', 'like', self::NO_BUKTI_PREFIX . '%')
                ->orderBy('no_bukti', 'desc')
                ->lockForUpdate()
                ->first();

            if (!$lastPenjualan) {
                $newNoBukti = self::NO_BUKTI_PREFIX . '0001';
            } else {
                $lastNumber = (int) substr($lastPenjualan->no_bukti, strlen(self::NO_BUKTI_PREFIX));
                $newNumber  = $lastNumber + 1;
                $newNoBukti = self::NO_BUKTI_PREFIX . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
            }

            $penjualan = self::create([
                'no_bukti'     => $newNoBukti,
                'tgl_bukti'    => date('Y-m-d', strtotime($data['tgl_bukti'])),
                'pelanggan_id' => $data['nama_pelanggan'],
            ]);

            foreach ($data['barang'] as $item) {
                $penjualan->details()->create([
                    'nama_barang'  => strtoupper($item['nama_barang']),
                    'qty'          => $item['qty'],
                    'harga'        => $item['harga'],
                ]);
            }

            return $penjualan->load('details');
        });
    }

    public static function updatePenjualan(int $id, array $data): self
    {
        $penjualan = self::findOrFail($id);

        return DB::transaction(function () use ($penjualan, $data) {
            $penjualan->update([
                'tgl_bukti'    => date('Y-m-d', strtotime($data['tgl_bukti'])),
                'pelanggan_id' => $data['nama_pelanggan'],
            ]);

            $penjualan->details()->delete();

            foreach ($data['barang'] as $item) {
                $penjualan->details()->create([
                    'nama_barang' => strtoupper($item['nama_barang']),
                    'qty'         => $item['qty'],
                    'harga'       => $item['harga'],
                ]);
            }

            return $penjualan;
        });
    }

    /**
     * Menghapus data penjualan.
     *
     * @param int $id ID Penjualan.
     * @return bool
     */
    public static function deletePenjualan(int $id): bool
    {
        $penjualan = self::findOrFail($id);

        return DB::transaction(function () use ($penjualan) {
            $penjualan->details()->delete();
            return $penjualan->delete();
        });
    }

    public static function getPenjualanForEdit(int $id): array
    {
        // 1. Ambil data penjualan beserta relasi 'details'-nya.
        // `with('details')` mencegah N+1 query problem (lebih efisien).
        // `findOrFail` akan otomatis melempar error 404 jika data tidak ditemukan.
        $penjualan = self::with('details')->findOrFail($id);

        // 2. Siapkan array barang dengan menghitung totalnya.
        $barangDetails = [];
        // $totalKeseluruhan = 0;
        foreach ($penjualan->details as $detail) {
            // $subtotal = $detail->qty * $detail->harga;
            $barangDetails[] = [
                'nama_barang' => $detail->nama_barang,
                'qty'         => $detail->qty,
                'harga'       => $detail->harga,
                // 'grandtotal'  => $detail->qty * $detail->harga, // Hitung total di sini
            ];

            // $totalKeseluruhan += $subtotal;
        }

        // 3. Susun hasil akhir sesuai struktur yang diminta.
        return [
            'id'             => $penjualan->id,
            'no_bukti'       => $penjualan->no_bukti,
            // Pastikan format tanggal sesuai (Y-m-d)
            'tgl_bukti'      => $penjualan->tgl_bukti->format('d-m-Y'),
            // Ambil hanya ID pelanggan sesuai contoh
            'nama_pelanggan' => (string) $penjualan->pelanggan_id,
            // 'grandTotal'     => $totalKeseluruhan, // total semua barang
            'barang'         => $barangDetails,
        ];
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

                // Jika filter dari url/get
                if (is_string($params['filters'])) {
                    $filtersJSON = \json_decode($params['filters'], true);
                    $filters = $filtersJSON['rules'];
                }

                // $filters = json_decode($params['filters']);
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
                    ->limit($countToFetch);

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
        $data = (object)self::getGridMaster($params);
        $correctPenjualanIds = $data->pluck('penjualans.id');

        if ($correctPenjualanIds->isEmpty()) {
            return collect();
        }

        // Ambil semua data penjualan master yang relevan
        // $penjualanMasters = DB::table('penjualans')
        //     ->join('pelanggans', 'penjualans.pelanggan_id', '=', 'pelanggans.id')
        //     ->whereIn('penjualans.id', $correctPenjualanIds)
        //     ->select('penjualans.*', 'pelanggans.nama_pelanggan')
        //     ->get();

        $sidx = $params['sidx_detail'] ?? 'nama_barang';
        $sord = $params['sord_detail'] ?? 'desc';

        // Ambil semua data detail yang relevan dalam satu query
        $allDetails = DB::table('penjualan_details')
            ->whereIn('penjualan_id', $correctPenjualanIds)
            ->orderBy($sidx, $sord)
            ->get();

        // --- LANGKAH 3: KELOMPOKKAN DETAIL BERDASARKAN ID PENJUALAN ---
        $groupedDetails = $allDetails->groupBy('penjualan_id');

        // --- LANGKAH 4: GABUNGKAN DATA MASTER DENGAN DETAIL SECARA MANUAL ---
        $data = $data->get()->map(function ($penjualan) use ($groupedDetails) {
            // Tambahkan properti 'details' ke setiap objek penjualan
            // Jika tidak ada detail, berikan koleksi kosong
            $penjualan->details = $groupedDetails->get($penjualan->id, collect());

            // Tambahkan properti 'pelanggan' agar strukturnya mirip Eloquent
            $penjualan->pelanggan = (object)['nama_pelanggan' => $penjualan->nama_pelanggan];

            // Ubah string tanggal menjadi objek Carbon agar bisa di-format nanti
            $penjualan->tgl_bukti = \Carbon\Carbon::parse($penjualan->tgl_bukti);

            return $penjualan;
        });

        // Urutkan hasil akhir sesuai dengan urutan ID yang kita dapatkan di Langkah 1
        $sortedData = $data->sortBy(function ($penjualan) use ($correctPenjualanIds) {
            return array_search($penjualan->id, $correctPenjualanIds->toArray());
        });

        return $sortedData;
        // return $data;
        // \dd($data);
    }

}
