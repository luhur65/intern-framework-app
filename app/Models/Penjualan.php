<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Penjualan extends Model
{
    /** @use HasFactory<\Database\Factories\PenjualanFactory> */
    use HasFactory;

    // protected $table = 'penjualans';
    protected $fillable = ['no_bukti', 'tgl_bukti', 'pelanggan_id'];
    protected $primaryKey = 'id_penjualan';
    public $timestamps = false;

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
     * Scope a query to get grid data.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array $params
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeGridMaster($countQuery, array $params)
    {
        $sidx = $params['sidx'] ?? 'id_penjualan';
        $sord = $params['sord'] ?? 'asc';
        $limit = $params['limit'] ?? 10;
        $page = $params['page'] ?? 1;

        // pencarian
        $globalSearch = $params['global_search'] ?? '';
        $search = $params['_search'] ?? 'false';

        // $query->join('pelanggans', 'penjualans.pelanggan_id', '=', 'pelanggans.id');

        $countQuery = Penjualan::query();

        // Terapkan filter saja, tanpa limit & offset
        $countQuery = $countQuery->join('pelanggans', 'penjualans.pelanggan_id', '=', 'pelanggans.id');
        // \dd($query->get());

        if ($globalSearch) {
            $countQuery->where(function ($q) use ($globalSearch) {
                $q->where('no_bukti', 'like', "%{$globalSearch}%")
                  ->orWhere('tgl_bukti', 'like', "%{$globalSearch}%")
                  ->orWhere('nama_pelanggan', 'like', "%{$globalSearch}%");
            });
        }

        // dd($globalSearch);
        if ($search == 'true') {
            // \dd($params);
            $filters = $params['filters'] ?? [];
            // $filters = \json_decode($filters, true);
            $countQuery->where(function ($q) use ($filters) {
                foreach ($filters as $filter) {
                    $field = $filter['field'];
                    $search = $filter['data'];
                    
                    if ($field == 'tgl_bukti') {
                        $q->whereRaw("DATE_FORMAT(tgl_bukti, '%d-%m-%Y') LIKE ?", ["%$search%"]);
                    } else {
                        $q->where($field, 'like', "%{$search}%");
                    }
                }
            });
        }

        // $start = $limit * $page - $limit;
        $start = $params['start'] ?? 0;

        // return $query->with('pelanggan') // tidak bisa melakukan sorting dengan relasi
        //     ->orderBy($sidx, $sord)
        //     ->offset($start)
        //     ->limit($limit);

        $data = $countQuery->select('penjualans.*', 'pelanggans.nama_pelanggan')
            ->orderBy($sidx, $sord)
            ->offset($start)
            ->limit($limit)
            ->get();
        
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

        return [
            'page' => $page,
            'total' => $total_pages,
            'records' => $count,
            'rows' => $data->map(function ($item) {
                return [
                    'id' => $item->id_penjualan,
                    'cell' => [
                        $item->id_penjualan,
                        $item->no_bukti,
                        $item->tgl_bukti,
                        $item->pelanggan->nama_pelanggan ?? '',
                        $item->total,
                    ],
                ];
            }),
        ];
    }
}
