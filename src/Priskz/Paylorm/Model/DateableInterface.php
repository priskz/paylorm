<?php namespace Priskz\Paylorm\Model;

/**
 * Dateable provides an interface for retrieving timestamps.
 *
 * @package Paylorm
 */

interface DateableInterface
{
	/**
	 * Get the created at date/time.
	 *
	 * @return Carbon\Carbon
	 */
	public function getCreatedAt();

	/**
	 * Get the updated at date/time.
	 *
	 * @return Carbon\Carbon
	 */
	public function getUpdatedAt();
}