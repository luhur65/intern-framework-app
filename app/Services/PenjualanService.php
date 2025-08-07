<?php

namespace App\Services;

use App\Interfaces\PenjualanServiceInterface;
use App\Models\Penjualan;
use Illuminate\Support\Facades\DB;
use Exception;

class PenjualanService implements PenjualanServiceInterface
{
  /**
   * {@inheritdoc}
   */
  public function createPenjualan(array $data): Penjualan
  {
    // Gunakan alias DB agar lebih singkat
    DB::beginTransaction();

    try {
      // Buat data master penjualan
      $penjualan = Penjualan::create([
        'no_bukti'     => strtoupper($data['no_bukti']),
        'tgl_bukti'    => date('Y-m-d', strtotime($data['tgl_bukti'])),
        'pelanggan_id' => $data['nama_pelanggan'],
      ]);

      // Loop untuk menyimpan detail barang
      foreach ($data['barang'] as $item) {
        $penjualan->details()->create([
          'nama_barang' => strtoupper($item['nama_barang']),
          'qty'         => $item['qty'],
          'harga'       => $item['harga'],
        ]);
      }

      DB::commit();

      // Kembalikan model Penjualan yang berhasil dibuat
      return $penjualan;
    } catch (Exception $e) {
      // Jika terjadi error, batalkan semua query
      DB::rollBack();

      // Lemparkan kembali exception untuk ditangani oleh controller
      throw new Exception('Gagal menyimpan data penjualan: ' . $e->getMessage());
    }
  }

  public function getPenjualanForEdit(int $id): array
  {
    // 1. Ambil data penjualan beserta relasi 'details'-nya.
    // `with('details')` mencegah N+1 query problem (lebih efisien).
    // `findOrFail` akan otomatis melempar error 404 jika data tidak ditemukan.
    $penjualan = Penjualan::with('details')->findOrFail($id);

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

  public function updatePenjualan(int $id, array $data): Penjualan
  {
    // Cari data penjualan berdasarkan ID, jika tidak ada akan error 404
    $penjualan = Penjualan::findOrFail($id);

    DB::beginTransaction();
    try {
      // 1. Update data master penjualan
      $penjualan->update([
        'no_bukti'     => strtoupper($data['no_bukti']),
        'tgl_bukti'    => date('Y-m-d', strtotime($data['tgl_bukti'])),
        'pelanggan_id' => $data['nama_pelanggan'],
      ]);

      // 2. Hapus semua detail lama
      $penjualan->details()->delete();

      // 3. Buat ulang detail dengan data barang yang baru
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
      throw new Exception('Gagal memperbarui data penjualan: ' . $e->getMessage());
    }
  }

  public function deletePenjualan(int $id): bool
  {
    $penjualan = Penjualan::findOrFail($id);

    DB::beginTransaction();
    try {
      // Penting: Hapus detailnya terlebih dahulu untuk menghindari error foreign key
      $penjualan->details()->delete();
      // Baru hapus data masternya
      $penjualan->delete();

      DB::commit();

      return true;
    } catch (Exception $e) {
      DB::rollBack();
      throw new Exception('Gagal menghapus data penjualan: ' . $e->getMessage());
    }
  }
}
