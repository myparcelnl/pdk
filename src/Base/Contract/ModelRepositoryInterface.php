<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base\Contract;

use MyParcelNL\Pdk\Base\Support\Collection;

/**
 * This is an interface intended for interaction with all PDK models (in the future).
 *
 * It aims to replace overlapping repositories such as PdkOrderRepository and PdkProductRepository with one more tightly coupled to plugin's storage layer.
 *
 * @package MyParcelNL\Pdk\Base\Contract
 */
interface ModelRepositoryInterface
{
    /**
     * Find a model by its *local* plugin identifier.
     *
     * TODO: make $id strictly typed using an int|string union when PHP version allows it.
     *
     * @param int|string $id
     * @return null|ModelInterface
     */
    public function find($id): ?ModelInterface;

    /**
     * Find a collection of models by their *local* plugin identifiers.
     *
     * TODO: make $ids strictly typed using an int|string[] type when PHP version allows it.
     *
     * @param int[]|string[] $ids
     * @return Collection a collection of ModelInterface objects, if none are found, an empty collection is returned.
     */
    public function findAll(array $ids): Collection;

    /**
     * Find a model by its identifier or throw an exception if not found.
     *
     * @param int|string $id
     * @return ModelInterface
     * @throws \MyParcelNL\Pdk\Base\Exception\ModelNotFoundException
     */
    public function findOrFail($id): ModelInterface;

    /**
     * Retrieve all models from the repository.
     *
     * @return Collection
     */
    public function all(): Collection;

    /**
     * Check if a model exists by its identifier.
     *
     * @param int|string $id
     * @return bool
     */
    public function exists($id): bool;

    // TODO: mutation methods are not implemented yet as they conflict with the (cached) storage solution in `RepositoryInterface`.
    // We need to separate caching and database storage first before we can re-introduce these methods.

    // /**
    //  * Save a model to the repository (create or update).
    //  *
    //  * @param ModelInterface $model
    //  * @return ModelInterface The saved model
    //  */
    // public function save(ModelInterface $model): ModelInterface;

    // /**
    //  * Save multiple models to the repository.
    //  *
    //  * @param Collection $models
    //  * @return Collection The saved models
    //  */
    // public function saveMany(Collection $models): Collection;

    // /**
    //  * Delete a model from the repository.
    //  *
    //  * @param ModelInterface $model
    //  * @return bool True if deleted successfully
    //  */
    // public function delete(ModelInterface $model): bool;

    // /**
    //  * Delete a model by its identifier.
    //  *
    //  * @param int|string $id
    //  * @return bool True if deleted successfully
    //  */
    // public function deleteById($id): bool;

    // /**
    //  * Delete multiple models.
    //  *
    //  * @param Collection $models
    //  * @return int Number of models deleted
    //  */
    // public function deleteMany(Collection $models): int;
}
