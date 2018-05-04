<?php

namespace Priskz\Paylorm\Model;

/**
 * A Model is just an object holding on to data for a particular
 * record in a database. Operations related directly to handling
 * a single piece of data for a particular record should be done
 * by the model. These consist mostly of getters and setters.
 */
interface BaseInterface
{
	/**
	 * Get this Model's type.
	 *
	 * @return string
	 */
	public function getType();

	/**
	 * Get the primary key for this Model.
	 *
	 * @return mixed  This Model's primary key
	 */
	public function getKey();

	/**
	 * Get this Model in an Array format.
	 *
	 * @return array
	 */
	public function asArray();
}