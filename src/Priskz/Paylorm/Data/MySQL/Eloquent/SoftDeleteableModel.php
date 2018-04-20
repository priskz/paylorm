<?php namespace Priskz\Paylorm\Data\MySQL\Eloquent;

use Illuminate\Database\Eloquent\SoftDeletes;
use Priskz\Paylorm\Data\MySQL\Eloquent\Model;
use Priskz\Paylorm\Model\SoftDeleteableInterface;

class SoftDeleteableModel extends Model implements SoftDeleteableInterface
{
	/**
	 * Soft Deletes Trait
	 */
    use SoftDeletes;

	/**
	 * Get this Model's deletion time.
	 *
	 * @return timestamp
	 */
	public function getDeletedAt()
	{
		return $this->deleted_at;
	}

	/**
	 * Check if this model is soft deleted.
	 *
	 * @return boolean
	 */
	public function isDeleted()
	{
		if($this->getDeletedAt())
		{
			return true;
		}

		return false;
	}
}