<?php namespace Priskz\Paylorm\Data\Mongo\Eloquent;

use Jenssegers\Mongodb\Eloquent\Model as MongoModel;

abstract class Model extends MongoModel
{
	/**
	 * @var Collection Name
	 */
	protected $collection;

	/**
	 * @var Database Connection Name
	 */
	protected $connection;
}