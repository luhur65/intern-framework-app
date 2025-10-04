<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Pelanggan
 *
 * Represents the 'pelanggans' table in the database. This model is used to
 * manage customer data and their relationships with sales transactions.
 *
 * @package App\Models
 * @property int $id
 * @property string $nama_pelanggan
 */
class Pelanggan extends Model
{
    /** @use HasFactory<\Database\Factories\PelangganFactory> */
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    // protected $table = 'pelanggans';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = ['nama_pelanggan'];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Get the sales records associated with this customer.
     *
     * This defines a one-to-many relationship where one customer can have
     * multiple sales transactions.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function penjualans()
    {
        return $this->hasMany(Penjualan::class, 'pelanggan_id');
    }

    /**
     * Retrieve a list of all customers, ordered by name.
     *
     * This static method provides a simple way to get a collection of all
     * customers, typically for populating dropdowns or lists in the UI.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getDataPelanggan()
    {
        return self::select('id', 'nama_pelanggan')
            ->orderBy('nama_pelanggan', 'asc')
            ->get();
    }
}