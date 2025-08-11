<?php

namespace App\Services;

use App\Interfaces\PenjualanServiceInterface;
use App\Models\Penjualan;
use Illuminate\Support\Facades\DB;
use Exception;

class PenjualanService implements PenjualanServiceInterface
{

  private $prefix = 'BRG-NO-';

  /**
   * Menghasilkan nomor bukti berikutnya yang tersedia menggunakan Query Builder.
   *
   * @return string
   */
  public function getNextNoBukti(): string
  {
    // Cari no_bukti terakhir menggunakan Query Builder dengan filter
    $lastPenjualan = DB::table('penjualans')
      ->where('no_bukti', 'like', $this->prefix . '%')
      ->orderBy('no_bukti', 'desc')
      ->first();

    if (!$lastPenjualan) {
      // Jika tidak ada data sama sekali, mulai dari 1
      return $this->prefix . '0001';
    }

    // Ambil bagian angka dari string (misal: 'BRG-NO-0021' -> '0021')
    $lastNumber = (int) substr($lastPenjualan->no_bukti, 7);

    // Tambah 1
    $newNumber = $lastNumber + 1;

    // Format kembali dengan padding nol di depan (misal: 22 -> '0022')
    return $this->prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
  }

  /**
   * {@inheritdoc}
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
        // 'no_bukti'     => strtoupper($data['no_bukti']),
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
