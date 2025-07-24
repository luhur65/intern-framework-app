<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PenjualanDetail extends Model
{
    /** @use HasFactory<\Database\Factories\PenjualanDetailFactory> */
    use HasFactory;

    protected $fillable = ['penjualan_id', 'nama_barang', 'qty', 'harga'];
    protected $primaryKey = 'id_detail';
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
     * Scope a query to get grid data.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array $params
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeGridDetail($query, array $params)
    {
        $sidx = $params['sidx'] ?? 'id_detail';
        $sord = $params['sord'] ?? 'asc';
        $limit = $params['limit'] ?? 10;
        $page = $params['page'] ?? 1;

        $count = $query->where('penjualan_id', $params['penjualan_id'])->count();

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

        $data = $query->where('penjualan_id', $params['penjualan_id'])
                     ->orderBy($sidx, $sord)
                     ->offset($start)
                     ->limit($limit);

        return [
                'page' => $page,
                'total' => $total_pages,
                'records' => $count,
                'rows' => $data->get()->map(function ($detail) {
                    return [
                        'id' => $detail->id_detail,
                        'cell' => [
                            // $detail->id_detail,
                            $detail->nama_barang,
                            $detail->qty,
                            $detail->harga,
                            $detail->getTotalAttribute(), // total = qty * harga 
                        ],
                    ];
                })->toArray(),
            ];
    }
}
