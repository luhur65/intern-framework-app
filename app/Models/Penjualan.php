<?php

namespace App\Models;

use Illuminate\Support\Facades\DB; // Import DB facade untuk Query Builder
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


/**
 * Class Penjualan
 *
 * Represents the 'penjualans' table. This model is central to managing sales
 * transactions, their details, and related business logic like generating
 * proof numbers and handling data for grids and exports.
 *
 * @package App\Models
 * @property int $id
 * @property string $no_bukti
 * @property \Illuminate\Support\Carbon $tgl_bukti
 * @property int $pelanggan_id
 * @property-read \App\Models\Pelanggan $pelanggan
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\PenjualanDetail[] $details
 * @property-read float $total
 * @property-read string $formatted_date
 */
class Penjualan extends Model
{
    /** @use HasFactory<\Database\Factories\PenjualanFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = ['no_bukti', 'tgl_bukti', 'pelanggan_id'];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'tgl_bukti' => 'date', 
    ];

    /**
     * The prefix for the sales proof number.
     *
     * @var string
     */
    private const NO_BUKTI_PREFIX = 'BRG-NO-';

    /**
     * Get the customer associated with the sale.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function pelanggan()
    {
        return $this->belongsTo(Pelanggan::class, 'pelanggan_id');
    }

    /**
     * Get the details (line items) for the sale.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function details()
    {
        return $this->hasMany(PenjualanDetail::class, 'penjualan_id');
    }

    /**
     * Accessor for the total amount of the sale.
     *
     * @return float The sum of (qty * harga) for all details.
     */
    public function getTotalAttribute()
    {
        return $this->details->sum(function ($detail) {
            return $detail->qty * $detail->harga;
        });
    }

    /**
     * Accessor for the formatted transaction date.
     *
     * @return string The date formatted as 'd-m-Y'.
     */
    public function getFormattedDateAttribute()
    {
        return \Carbon\Carbon::parse($this->tgl_bukti)->format('d-m-Y');
    }

    /**
     * Generates the next available proof number using Query Builder.
     *
     * @return string The next unique proof number.
     */
    public static function getNextNoBukti(): string
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
        $lastNumber = (int) substr($lastPenjualan->no_bukti, strlen(self::NO_BUKTI_PREFIX));

        // Tambah 1
        $newNumber = $lastNumber + 1;

        // Format kembali dengan padding nol di depan (misal: 22 -> '0022')
        return self::NO_BUKTI_PREFIX . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Creates a new sales record with a race-condition-safe proof number.
     *
     * @param array $data The validated data from the request.
     * @return self The newly created Penjualan instance with its details.
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

    /**
     * Updates an existing sales record and its details.
     *
     * @param int $id The ID of the sale to update.
     * @param array $data The new, validated data.
     * @return self The updated Penjualan instance.
     */
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
     * Deletes a sales record and its associated details.
     *
     * @param int $id The ID of the sale to delete.
     * @return bool True on success.
     */
    public static function deletePenjualan(int $id): bool
    {
        $penjualan = self::findOrFail($id);

        return DB::transaction(function () use ($penjualan) {
            $penjualan->details()->delete();
            return $penjualan->delete();
        });
    }

    /**
     * Retrieves a single sale and formats it for the edit form.
     *
     * @param int $id The ID of the sale to retrieve.
     * @return array The formatted sales data.
     */
    public static function getPenjualanForEdit(int $id): array
    {
        $penjualan = self::with('details')->findOrFail($id);

        $barangDetails = [];
        foreach ($penjualan->details as $detail) {
            $barangDetails[] = [
                'nama_barang' => $detail->nama_barang,
                'qty'         => $detail->qty,
                'harga'       => $detail->harga,
            ];
        }

        return [
            'id'             => $penjualan->id,
            'no_bukti'       => $penjualan->no_bukti,
            'tgl_bukti'      => $penjualan->tgl_bukti->format('d-m-Y'),
            'nama_pelanggan' => (string) $penjualan->pelanggan_id,
            'barang'         => $barangDetails,
        ];
    }

    /**
     * Fetches, filters, and paginates data for the master jqGrid using Query Builder.
     *
     * @param array $params An array of parameters for sorting, filtering, and pagination.
     * @param bool $limitOn Whether to apply pagination limits. If false, returns all matching IDs.
     * @return array An array formatted for jqGrid or an array of IDs.
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
            );

        $globalSearch = $params['global_search'] ?? '';
        $search = $params['_search'] ?? 'false';

        if ($globalSearch) {
            $baseQuery->where(function ($q) use ($globalSearch) {
                $q->where('penjualans.no_bukti', 'like', "%{$globalSearch}%")
                    ->orWhereRaw("DATE_FORMAT(penjualans.tgl_bukti, '%d-%m-%Y') LIKE ?", ["%{$globalSearch}%"])
                    ->orWhere('pelanggans.nama_pelanggan', 'like', "%{$globalSearch}%");
            });
        }

        if ($search == 'true') {
            $filters = $params['filters'] ?? [];
            if (!empty($filters)) {
                if (is_string($params['filters'])) {
                    $filtersJSON = \json_decode($params['filters'], true);
                    $filters = $filtersJSON['rules'];
                }
                $baseQuery->where(function ($q) use ($filters) {
                    foreach ($filters as $filter) {
                        $field = $filter['field'] ?? null;
                        $data = $filter['data'] ?? null;
                        if ($field && $data !== null) {
                            if ($field == 'tgl_bukti') {
                                $q->whereRaw("DATE_FORMAT(penjualans.tgl_bukti, '%d-%m-%Y') LIKE ?", ["%$data%"]);
                                continue;
                            }
                            $q->where($field, 'like', "%{$data}%");
                        }
                    }
                });
            }
        }

        $sidx = $params['sidx'] ?? 'penjualans.id';
        $sord = $params['sord'] ?? 'asc';

        if (!$limitOn) {
            return $baseQuery
                ->orderBy($sidx, $sord) 
                ->pluck('penjualans.id')
                ->toArray();
        }

        $limit = (int)($params['limit'] ?? 10);
        $page = (int)($params['page'] ?? 1);
        $start = $params['start'] ?? (($page - 1) * $limit);

        $countQuery = clone $baseQuery;
        $count = $countQuery->count();

        $total_pages = $count > 0 ? ceil($count / $limit) : 0;
        if ($page > $total_pages) $page = $total_pages;
        $start = max(0, ($page - 1) * $limit);

        if (isset($params['start_range']) && isset($params['end_range'])) {
            $startRange = $params['start_range'];
            $endRange = $params['end_range'];
            if ($startRange > 0 && $endRange > 0) {
                $offset = $startRange - 1;
                $countToFetch = $endRange - $startRange + 1;
                return $baseQuery
                    ->orderBy($sidx, $sord)
                    ->offset($offset)
                    ->limit($countToFetch);
            }
        }

        $data = $baseQuery->orderBy($sidx, $sord)
            ->offset($start)
            ->limit($limit)
            ->get();

        $data->transform(function ($item) {
            $item->formatted_tgl_bukti = \Carbon\Carbon::parse($item->tgl_bukti)->format('d-m-Y');
            return $item;
        });

        $rows = $data->map(function ($item) {
            return [
                'id' => $item->id,
                'cell' => [
                    $item->id,
                    $item->no_bukti,
                    $item->formatted_tgl_bukti,
                    $item->nama_pelanggan,
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

    /**
     * Calculates the page number for a given record ID.
     *
     * @param array $params Grid parameters for sorting and filtering.
     * @param int $id The ID of the record to find.
     * @return array An array containing the ID and its calculated page number.
     */
    public static function getPenjualanPagination(array $params, $id = 0)
    {
        $ids = self::getGridMaster($params, false);
        $rowIndex = array_search($id, $ids);
        $rowNumber = $rowIndex !== false ? $rowIndex + 1 : 1;
        $limit = isset($params['limit']) ? intval($params['limit']) : 10;
        $page = ceil($rowNumber / $limit);

        return [
            "id" => $id,
            "page" => $page,
        ];
    }

    /**
     * Finds the ID of the nearest record after a deletion.
     *
     * @param array $params Grid parameters for sorting and filtering.
     * @param int $deletedId The ID of the record that was deleted.
     * @return int|null The ID of the next record to focus on, or null if none exists.
     */
    public static function getIDTerdekat(array $params, $deletedId = 0)
    {
        $ids = self::getGridMaster($params, false);
        $posisiTerhapus = array_search($deletedId, $ids);

        if ($posisiTerhapus === false) {
            return !empty($ids) && $ids[0] != $deletedId ? $ids[0] : (isset($ids[1]) ? $ids[1] : null);
        }

        unset($ids[$posisiTerhapus]);
        $ids = array_values($ids);

        if (isset($ids[$posisiTerhapus])) {
            return $ids[$posisiTerhapus];
        } elseif ($posisiTerhapus > 0 && isset($ids[$posisiTerhapus - 1])) {
            return $ids[$posisiTerhapus - 1];
        } else {
            return !empty($ids) ? $ids[0] : null;
        }
    }

    /**
     * Retrieves and formats data for exporting.
     *
     * @param array $params Parameters for filtering the data, including export range.
     * @return \Illuminate\Support\Collection A collection of sales data with details.
     */
    public static function getDataForExport(array $params)
    {
        $queryBuilder = self::getGridMaster($params);
        $correctPenjualanIds = collect($queryBuilder['rows'])->pluck('id');

        if ($correctPenjualanIds->isEmpty()) {
            return collect();
        }

        $sidx = $params['sidx_detail'] ?? 'nama_barang';
        $sord = $params['sord_detail'] ?? 'desc';

        $allDetails = DB::table('penjualan_details')
            ->whereIn('penjualan_id', $correctPenjualanIds)
            ->orderBy($sidx, $sord)
            ->get();
        $groupedDetails = $allDetails->groupBy('penjualan_id');

        $penjualanMasters = DB::table('penjualans')
            ->join('pelanggans', 'penjualans.pelanggan_id', '=', 'pelanggans.id')
            ->whereIn('penjualans.id', $correctPenjualanIds)
            ->select('penjualans.*', 'pelanggans.nama_pelanggan')
            ->get();

        $data = $penjualanMasters->map(function ($penjualan) use ($groupedDetails) {
            $penjualan->details = $groupedDetails->get($penjualan->id, collect());
            $penjualan->pelanggan = (object)['nama_pelanggan' => $penjualan->nama_pelanggan];
            $penjualan->tgl_bukti = \Carbon\Carbon::parse($penjualan->tgl_bukti);
            return $penjualan;
        });

        $sortedData = $data->sortBy(function ($penjualan) use ($correctPenjualanIds) {
            return array_search($penjualan->id, $correctPenjualanIds->toArray());
        });

        return $sortedData;
    }
}