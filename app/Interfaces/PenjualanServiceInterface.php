<?php

namespace App\Interfaces;

use App\Models\Penjualan;

/**
 * Interface for the service that manages sales data.
 *
 * This interface defines the contract for the sales service, outlining the
 * methods that must be implemented for handling sales-related business logic,
 * such as creating, updating, deleting, and retrieving sales data.
 *
 * @package App\Interfaces
 */
interface PenjualanServiceInterface
{
  /**
   * Generates the next available proof number using the Query Builder.
   *
   * @return string The next unique proof number.
   */
  public function getNextNoBukti(): string;

  /**
   * Creates a new sales record along with its details.
   *
   * @param array $data The validated data from the request.
   * @return Penjualan The newly created Penjualan model instance.
   * @throws \Exception If a failure occurs while saving to the database.
   */
  public function createPenjualan(array $data): Penjualan;

  /**
   * Retrieves a single sales record for editing purposes and formats it
   * for the frontend.
   *
   * @param int $id The ID of the sale to retrieve.
   * @return array The formatted sales data.
   */
  public function getPenjualanForEdit(int $id): array;

  /**
   * Updates an existing sales record and its details.
   *
   * @param int $id The ID of the sale to update.
   * @param array $data The new, validated data.
   * @return Penjualan The updated Penjualan model instance.
  */
  public function updatePenjualan(int $id, array $data): Penjualan;

  /**
   * Deletes a sales record and its associated details.
   *
   * @param int $id The ID of the sale to delete.
   * @return bool True on success, false on failure.
   */
  public function deletePenjualan(int $id): bool;
}