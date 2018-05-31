<?php

namespace Priskz\Paylorm\Data\MySQL\Eloquent;

use Priskz\Payload\Payload;
use Priskz\Paylorm\Repository\CrudInterface;

class CrudRepository implements CrudInterface
{
	/**
	 * Parameter Key Constants 
	 */
	const ORDER_KEY      = 'order';
	const FILTER_KEY     = 'filter';
	const EMBED_KEY      = 'embed';
	const PAGINATION_KEY = 'pagination';
	const PAGE_KEY       = 'page';
	const PER_KEY        = 'per';

	/**
	 * Common / Default Get Parameter(s)
	 */
	protected $parameter = [];

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
	 * Rertrieve the total item count.
	 *
	 * @return integer
	 */
	public function getTotal()
	{
		return $this->model->count();
	}

	/**
	 * Get
	 *
	 * @param  array  $parameter
	 * @return Payload
	 */
	public function get(array $parameter = [])
	{
		// Merge any base/common query parameters with given parameters taking precedence.
		$parameter = array_merge($this->parameter, $parameter);

		// Begin building query.
		$query = $this->model;

		// Add filters to the query.
		if(array_key_exists(self::FILTER_KEY, $parameter))
		{
			foreach($parameter[self::FILTER_KEY] as $field => $filter)
			{
				// Check if the value is an array, if not treat as a standard where equal clause.
				if( ! is_array($filter))
				{
					$query = $query->where($field, '=', $filter);
				}
				else
				{
					// If filter array is an array, utilize expected key/values.
					if(array_key_exists('or', $filter))
					{
						if($filter['or'])
						{
							$query = $query->orWhere($filter['field'], $filter['operator'], $filter['value']);
						}
					}
					else
					{
						$query = $query->where($filter['field'], $filter['operator'], $filter['value']);
					}
				}
			}
		}

		// Add order parameters to the query.
		if(array_key_exists(self::ORDER_KEY, $parameter))
		{
			foreach($parameter[self::ORDER_KEY] as $field => $direction)
			{
				$query = $query->orderBy($field, $direction);
			}
		}

		// Add relationship joins to query.
		if(array_key_exists(self::EMBED_KEY, $parameter))
		{
			foreach($parameter[self::EMBED_KEY] as $relationship)
			{
				$this->eagerLoad($relationship);
			}
		}

		// If pagination is needed, build query, meta data, and 
		if(array_key_exists(self::PAGINATION_KEY, $parameter))
		{
			// Gather pagination variables.
			$result = [
				'items' => null,
				'meta'  => [
					'page'        => key($parameter[self::PAGINATION_KEY]),
					'per'         => reset($parameter[self::PAGINATION_KEY]),
					'total_count' => $query->count()
				]
			];

			// Calculate total pages.
			$result['meta']['total_pages'] = ceil($result['meta']['total_count'] / $result['meta']['per']);

			// Add pagination specific query constraints.
			$query = $query->skip($result['meta']['page' ])->take($result['meta']['per']);

			// Finally, run the query.
			$result['items'] = $this->retrieve($query);

			// Return results.
			return new Payload($result, 'found');
		}

		// Run the query.
		$result = $this->retrieve($query);
		
		// Determine what to return based on count.
		switch($result->count())
		{
			case 0:
				return new Payload($result, 'not_found');
			break;

			default:
				return new Payload($result, 'found');
			break;
		}
	}

	/**
	 * Get a single object based on given parameters.
	 *
	 * @param  array  $parameter
	 * @return Payload
	 */
	public function first(array $parameter = [])
	{
		// Merge any base/common query parameters with given parameters taking precedence.
		$parameter = array_merge($this->parameter, $parameter);

		// Begin building query.
		$query = $this->model;

		// Add filters to the query.
		if(array_key_exists(self::FILTER_KEY, $parameter))
		{
			foreach($parameter[self::FILTER_KEY] as $field => $filter)
			{
				// Check if the value is an array, if not treat as a standard where equal clause.
				if( ! is_array($filter))
				{
					$query = $query->where($field, '=', $filter);
				}
				else
				{
					// If filter array is an array, utilize expected key/values.
					if(array_key_exists('or', $filter))
					{
						if($filter['or'])
						{
							$query = $query->orWhere($filter['field'], $filter['operator'], $filter['value']);
						}
					}
					else
					{
						$query = $query->where($filter['field'], $filter['operator'], $filter['value']);
					}
				}
			}
		}

		// Add order parameters to the query.
		if(array_key_exists(self::ORDER_KEY, $parameter))
		{
			foreach($parameter[self::ORDER_KEY] as $field => $direction)
			{
				$query = $query->orderBy($field, $direction);
			}
		}

		// Add relationship joins to query.
		if(array_key_exists(self::EMBED_KEY, $parameter))
		{
			foreach($parameter[self::EMBED_KEY] as $relationship)
			{
				$this->eagerLoad($relationship);
			}
		}

		// Run the query.
		$result = $this->retrieve($query);

		// Determine what to return based on count.
		switch($result->count())
		{
			case 0:
				return new Payload($result, 'not_found');
			break;

			default:
				return new Payload($result->first(), 'found');
			break;
		}
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
	 * @todo: not sure this is necessary, would prefer to build
	 * 		  functionality into the create method.
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

		if($updated)
		{
			return new Payload($object , 'updated');
		}

		return new Payload($object, 'not_updated');
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
	 * Retrieve the data.
	 *
	 * @param  query  $selectQuery
	 * @return Collection|Single of Domain\Model
	 */
	public function retrieve($selectQuery)
	{
		// Eager load configured relationships.
		$selectQuery = $this->setEagerLoaded($selectQuery);

		// Execute the query.
		$retrieved = $selectQuery->get();

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
		return $this->eagerLoad;
	}

	/**
	 * Get this Repository's Lazy Load property.
	 *
	 * @return array
	 */
	public function getLazyLoad()
	{
		return $this->lazyLoad;
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