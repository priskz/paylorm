<?php namespace Priskz\Paylorm\Data\MySQL\Eloquent;

use Cache;
use Illuminate\Database\Eloquent\Collection;
use Priskz\Payload\Payload;
use Priskz\Paylorm\Repository\CrudInterface;

class CrudMemcachedRepository implements CrudInterface
{
	protected $model;

	/**
	 * Create a new Repository. In order to perform the "static"
	 * operations of a repository (such as create or delete), an
	 * instance of a Model is required.
	 *
	 * @param  Paylorm\Data\MySQL\Eloquent\Model  $model
	 */
	public function __construct($model)
	{
		$this->model = $model;
	}

	/**
	 * Create a new Model with the given data.
	 *
	 * @param  array  $data
	 * @return Payload
	 */
	public function create(array $data = [])
	{
		$created = $this->model->create($data);

		if( ! $created)
		{
			return new Payload(null, 'not_created');
		}

		// Cache the newly created model and tag it with table's name.
		Cache::tags($this->model->getTable())->forever($created->getKey(), $created);

		return new Payload($created, 'created');
	}

	/**
	 * Update the given Model with the given data
	 *
	 * @param  $model
	 * @param  array  $data
	 * @return Payload
	 */
	public function update($object, array $data)
	{
		$updated = $object->update($data);

		if( ! $updated)
		{
			return new Payload($object , 'not_updated');
		}

		// Replace the existing cached model with the updated model.
		Cache::tags($this->model->getTable())->forever($updated->getKey(), $updated);

		return new Payload($object, 'updated');
	}

	/**
	 * Delete the given Model
	 *
	 * @param  $object
	 * @return Payload
	 */
	public function delete($object)
	{
		$deleted = $object->delete();

		if( ! $deleted)
		{
			return new Payload($object, 'not_deleted');
		}

		// Remove the deleted model from cache.
		Cache::tags($this->model->getTable())->flush($updated->getKey());

		return new Payload(null, 'deleted');
	}
}