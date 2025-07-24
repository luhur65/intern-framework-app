<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pelanggan extends Model
{
    /** @use HasFactory<\Database\Factories\PelangganFactory> */
    use HasFactory;

    // protected $table = 'pelanggans';
    protected $fillable = ['nama_pelanggan'];
    public $timestamps = false;

    /**
     * Get the penjualans associated with the pelanggan.
     */
    public function penjualans()
    {
        return $this->hasMany(Penjualan::class, 'pelanggan_id');
    }
}
