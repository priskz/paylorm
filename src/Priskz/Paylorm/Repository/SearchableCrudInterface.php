<?php namespace Priskz\Paylorm\Repository;

/**
 * A repository is an object containing operations for one or more
 * Models of a given type. Any operations that go beyond the scope
 * of simply working with a piece of data for a particular model
 * should go here. This consists of mostly CRUD operations.
 */
interface SearchableCrudInterface extends ReadableInterface, WriteableInterface, DeleteableInterface, SearchableInterface
{
	// @todo
}