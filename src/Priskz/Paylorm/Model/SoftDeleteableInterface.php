<?php namespace Priskz\Paylorm\Model;

interface SoftDeleteableInterface
{
	/**
	 * Check if this entity has been soft deleted.
	 *
	 * @return boolean
	 */
	public function isDeleted();

	/**
	 * Get the deleted at date/time.
	 *
	 * @return Carbon\Carbon
	 */
	public function getDeletedAt();

	/**
	 * Restore a soft deleted entity.
	 *
	 * @return void
	 */
	public function restore();

	/**
	 * Force delete an entity permanently.
	 *
	 * @return boolean
	 */
	public function forceDelete();
}