<?php

namespace App\Interfaces;

use App\Models\Penjualan;

/**
 * Interface untuk service yang mengelola data Penjualan.
 */
interface PenjualanServiceInterface
{
  /**
   * Membuat data penjualan baru beserta detailnya.
   *
   * @param array $data Data yang sudah divalidasi dari request.
   * @return Penjualan Model Penjualan yang baru dibuat.
   * @throws \Exception Jika terjadi kegagalan saat menyimpan ke database.
   */
  public function createPenjualan(array $data): Penjualan;

  /**
   * Mengambil data penjualan tunggal untuk keperluan edit
   * dan mengubahnya ke format yang dibutuhkan frontend.
   *
   * @param int $id ID Penjualan yang akan diambil.
   * @return array Data penjualan yang sudah diformat.
   */
  public function getPenjualanForEdit(int $id): array;

  /**
   * Memperbarui data penjualan yang ada beserta detailnya.
   * @param int $id ID Penjualan yang akan diupdate.
   * @param array $data Data baru yang sudah divalidasi.
   * @return Penjualan Model Penjualan yang telah diupdate.
  */
  public function updatePenjualan(int $id, array $data): Penjualan;

  /**
   * Menghapus data penjualan beserta detailnya.
   * @param int $id ID Penjualan yang akan dihapus.
   * @return bool True jika berhasil, false jika gagal.
   */
  public function deletePenjualan(int $id): bool;
}
