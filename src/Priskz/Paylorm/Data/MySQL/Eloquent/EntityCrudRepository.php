<?php

namespace Priskz\Paylorm\Data\MySQL\Eloquent;

use Priskz\Payload\Payload;
use Priskz\Paylorm\Repository\CrudInterface;

class EntityCrudRepository implements CrudInterface
{
	/**
	 * Model
	 */
	protected $model;

	/**
	 * Eager loading configuration. Note: This is good for loading
	 * relationships that are statically assigned.
	 */
	protected $eagerLoad = [];

	/**
	 * Lazy loading configuration. Note: This is good for loading
	 * relationships that are "dynamically typed".
	 */
	protected $lazyLoad = [];

	/**
	 * Core Configuration
	 */
	protected $core = [
		'load' => [
			'eager' => [
				'entityIdentifier'
			],
			'lazy'  => [],
		]
	];

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
	 * Get this Repository's type.
	 *
	 * @return string
	 */
	public function getType()
	{
		return $this->model->getType(true);
	}

	/**
	 * Get
	 *
	 * @return Payload
	 */
	public function get($filters = [], $sorts = [], $fields = [], $embeds = [])
	{
		$query = $this->model;

		foreach($filters as $filter)
		{
			if($filter['or'])
			{
				$query = $query->orWhere($filter['field'], $filter['operator'], $filter['value']);
			}
			else
			{
				$query = $query->where($filter['field'], $filter['operator'], $filter['value']);
			}
		}

		foreach($embeds as $embed)
		{
			$this->eagerLoad($embed);
		}

		foreach($sorts as $key => $sort)
		{
			$query = $query->orderBy($sort['field'], $sort['direction']);
		}

		// @todo: implement fields

		$retrieved = $this->retrieve($query);

		if($retrieved->isEmpty())
		{
			return new Payload($retrieved, 'not_found');
		}

		return new Payload($retrieved, 'found');
	}

	/**
	 * Create a new Model with the given data.
	 *
	 * @param  array  $data
	 * @return Payload
	 */
	public function create($data = [])
	{
		$created = $this->model->create($data);

		if( ! $created)
		{
			return new Payload(null, 'not_created');
		}

		return new Payload($created, 'created');
	}

	/**
	 * Create a new Model with the given data.
	 *
	 * @param  array  $data
	 * @return Payload
	 */
	public function insert($data = [])
	{
		$created = $this->model->insert($data);

		if( ! $created)
		{
			return new Payload(null, 'not_created');
		}

		return new Payload($created, 'created');
	}

	/**
	 * Update the given Model with the given data
	 *
	 * @param  mixed  $object
	 * @param  array  $data
	 * @return Payload
	 */
	public function update($data, $object)
	{
		$updated = $object->update($data);

		// @todo: Double Check that we should be returning updated not object
		if( ! $updated)
		{
			return new Payload($object , 'not_updated');
		}

		return new Payload($object, 'updated');
	}

	/**
	 * Delete
	 *
	 * @param  mixed  $model
	 * @return Payload
	 */
	public function delete($data)
	{
		switch(gettype($data))
		{
			case 'object':
				$deleted = $data->delete();
			break;

			case 'array':
				$query = $this->model;

				// Delete w/ where clauses.
				if($this->isAssociative($data))
				{
					foreach($data as $key => $value)
					{
						$query = $query->where($key, '=', $value);
					}
				}
				// Delete multiple by array of keys.
				else
				{
					$query = $query->whereIn('id', $data);
				}

				$deleted = $query->delete();
			break;

			default:
				$deleted = false;
		}

		if($deleted)
		{
			return new Payload($data, 'deleted');
		}

		return new Payload($data, 'not_deleted');
	}

	/**
	 * Purge
	 *
	 * @param  mixed  $data
	 * @return Payload
	 */
	public function purge($data)
	{
		switch(gettype($data))
		{
			case 'object':
				$purged = $data->forceDelete();
			break;

			case 'array':
				$query = $this->model->withTrashed();

				// Delete w/ where clauses.
				if($this->isAssociative($data))
				{
					foreach($data as $key => $value)
					{
						$query = $query->where($key, '=', $value);
					}
				}
				// Delete multiple by array of keys.
				else
				{
					$query = $query->whereIn('id', $data);
				}

				$purged = $query->forceDelete();
			break;

			default:
				$purged = false;
		}

		if($purged)
		{
			return new Payload($data, 'purged');
		}

		return new Payload($data, 'not_purged');
	}

	/**
	 * Retrieve the
	 *
	 * @param  query  $query
	 * @return Collection|Single of Domain\Model
	 */
	public function retrieve($query)
	{
		// Eager load configured relationships.
		$query = $this->setEagerLoaded($query);

		// Execute the query.
		$retrieved = $query->get();

		// Eager load configured relationships.
		return $this->setLazyLoaded($retrieved);
	}

	/**
	 * Set this query's eager loaded relationships.
	 *
	 * @param  query  $query
	 * @return query
	 */
	public function setEagerLoaded($query)
	{
		if( ! empty($this->getEagerLoad()))
		{
			$query = $query->with($this->getEagerLoad());
		}

		return $query;
	}

	/**
	 * Set this query's lazy loaded relationships.
	 *
	 * @param  query  $query
	 * @return query
	 */
	public function setLazyLoaded($query)
	{
		if( ! empty($this->getLazyLoad()))
		{
			$query = $query->load($this->getLazyLoad());
		}

		return $query;
	}

	/**
	 * 
	 * Get this Repository's Eager Load property.
	 *
	 * @return array
	 */
	public function getEagerLoad()
	{
		return array_merge($this->eagerLoad, $this->core['load']['eager']);
	}

	/**
	 * Get this Repository's Lazy Load property.
	 *
	 * @return array
	 */
	public function getLazyLoad()
	{
		return array_merge($this->lazyLoad, $this->core['load']['lazy']);
	}

	/**
	 *  Eager load the relationship(s) specified.
	 *
	 * @param  string|array  $relationship
	 * @return void
	 */
	public function eagerLoad($relationship)
	{
		if(is_array($relationship))
		{
			foreach($relationship as $key)
			{
				$this->eagerLoad[] = $key;
			}
		}
		else
		{
			$this->eagerLoad[] = $relationship;
		}
	}

	/**
	 *  lazy load the relationship(s) specified.
	 *
	 * @param  string|array  $relationship
	 * @return void
	 */
	public function lazyLoad($relationship)
	{
		if(is_array($relationship))
		{
			foreach($relationship as $key)
			{
				$this->lazyLoad[] = $key;
			}
		}
		else
		{
			$this->lazyLoad[] = $relationship;
		}
	}

	/* =====================================================
	 * Helpers
	 * ================================================== */

    /**
	 * Check if given array is an associative array.
	 *
	 * @param  array  $array
	 * @return bool
	 */
	protected function isAssociative(array $array)
	{
		return count(array_filter(array_keys($array), 'is_string')) > 0;
	}
}