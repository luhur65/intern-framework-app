<?php

namespace App\Services;

use App\Interfaces\PenjualanServiceInterface;
use App\Models\Penjualan;
use Illuminate\Support\Facades\DB;
use Exception;

/**
 * Class PenjualanService
 *
 * This class implements the PenjualanServiceInterface and contains the business
 * logic for managing sales transactions. It handles the creation, updating,
 * deletion, and retrieval of sales data, ensuring data integrity through
 * database transactions.
 *
 * @package App\Services
 */
class PenjualanService implements PenjualanServiceInterface
{

  /**
   * The prefix used for generating sales proof numbers.
   *
   * @var string
   */
  private $prefix = 'BRG-NO-';

  /**
   * Generates the next available proof number using the Query Builder.
   *
   * This method finds the last sales record, extracts the numeric part of its
   * proof number, increments it, and returns the new, formatted proof number.
   *
   * @return string The next unique proof number.
   */
  public function getNextNoBukti(): string
  {
    // Find the last proof number using Query Builder with a filter
    $lastPenjualan = DB::table('penjualans')
      ->where('no_bukti', 'like', $this->prefix . '%')
      ->orderBy('no_bukti', 'desc')
      ->first();

    if (!$lastPenjualan) {
      // If no data exists at all, start from 1
      return $this->prefix . '0001';
    }

    // Get the numeric part from the string (e.g., 'BRG-NO-0021' -> '0021')
    $lastNumber = (int) substr($lastPenjualan->no_bukti, 7);

    // Add 1
    $newNumber = $lastNumber + 1;

    // Format again with leading zeros (e.g., 22 -> '0022')
    return $this->prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
  }

  /**
   * {@inheritdoc}
   *
   * Creates a new sales record and its associated details within a database
   * transaction to ensure atomicity. It also handles the generation of a new,
   * race-condition-safe proof number.
   */
  public function createPenjualan(array $data): Penjualan
  {
    return DB::transaction(function () use ($data) {
      $lastPenjualan = Penjualan::where('no_bukti', 'like', $this->prefix . '%')
        ->orderBy('no_bukti', 'desc')
        ->lockForUpdate()
        ->first();

      if (!$lastPenjualan) {
        $newNoBukti = $this->prefix . '0001';
      } else {
        $lastNumber = (int) substr($lastPenjualan->no_bukti, strlen($this->prefix));
        $newNumber  = $lastNumber + 1;
        $newNoBukti = $this->prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
      }

      $penjualan = Penjualan::create([
        'no_bukti'     => $newNoBukti,
        'tgl_bukti'    => date('Y-m-d', strtotime($data['tgl_bukti'])),
        'pelanggan_id' => $data['nama_pelanggan'],
      ]);

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
   * Retrieves a single sales record and formats it for the edit form.
   *
   * This method fetches a sale by its ID, along with its details, and
   * structures the data into an array suitable for populating an edit form
   * on the frontend.
   *
   * @param int $id The ID of the sale to retrieve.
   * @return array The formatted sales data.
   */
  public function getPenjualanForEdit(int $id): array
  {
    $penjualan = Penjualan::with('details')->findOrFail($id);

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
   * Updates an existing sales record and its details.
   *
   * This method updates the master sales record, deletes all its old details,
   * and creates new ones based on the provided data. The entire operation
   * is wrapped in a database transaction.
   *
   * @param int $id The ID of the sale to update.
   * @param array $data The new, validated data for the sale.
   * @return Penjualan The updated Penjualan model instance.
   * @throws Exception If the update fails.
   */
  public function updatePenjualan(int $id, array $data): Penjualan
  {
    // Find sales data by ID, will throw 404 error if not found
    $penjualan = Penjualan::findOrFail($id);

    DB::beginTransaction();
    try {
      // 1. Update master sales data
      $penjualan->update([
        'tgl_bukti'    => date('Y-m-d', strtotime($data['tgl_bukti'])),
        'pelanggan_id' => $data['nama_pelanggan'],
      ]);

      // 2. Delete all old details
      $penjualan->details()->delete();

      // 3. Recreate details with new item data
      foreach ($data['barang'] as $item) {
        $penjualan->details()->create([
          'nama_barang' => strtoupper($item['nama_barang']),
          'qty'         => $item['qty'],
          'harga'       => $item['harga'],
        ]);
      }

      DB::commit();

      return $penjualan;
    } catch (Exception $e) {
      DB::rollBack();
      throw new Exception('Failed to update sales data: ' . $e->getMessage());
    }
  }

  /**
   * Deletes a sales record and its associated details.
   *
   * This method removes a sales record and all its line items from the database
   * within a transaction to ensure data consistency.
   *
   * @param int $id The ID of the sale to delete.
   * @return bool True on successful deletion.
   * @throws Exception If the deletion fails.
   */
  public function deletePenjualan(int $id): bool
  {
    $penjualan = Penjualan::findOrFail($id);

    DB::beginTransaction();
    try {
      // Important: Delete the details first to avoid foreign key errors
      $penjualan->details()->delete();
      // Then delete the master data
      $penjualan->delete();

      DB::commit();

      return true;
    } catch (Exception $e) {
      DB::rollBack();
      throw new Exception('Failed to delete sales data: ' . $e->getMessage());
    }
  }
}