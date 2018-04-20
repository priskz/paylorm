<?php namespace Priskz\Paylorm\Data\MySQL\Eloquent;

use Config;
use Illuminate\Database\Eloquent\Collection;
use Priskz\Paylorm\Data\MySQL\Eloquent\Model;
use Priskz\Paylorm\Model\EntityInterface;
use Priskz\SORAD\Entity\Domain\Identifier\Data\MySQL\Eloquent\Model as IdentifierModel;
use Priskz\SORAD\Entity\Domain\Reference\Data\MySQL\Eloquent\Model as ReferenceModel;

class Entity extends Model implements EntityInterface
{
	/**
	 * Get this Entity's EntityIdentifier.
	 *
	 * @return EntityIdentifier
	 */
	public function getEntityIdentifier()
	{
		return $this->entityIdentifier;
	}

	/**
	 * Get this Entity's Uuid.
	 *
	 * @return string
	 */
	public function getUuid()
	{
		if( ! $this->getEntityIdentifier()->isEmpty())
		{
			return $this->getEntityIdentifier()->first()->getUuid();
		}

		return null;
	}

	/**
	 * Get this Entity's EntityReference.
	 *
	 * @return Collection of EntityReference(s)
	 */
	public function getEntityReferences()
	{
		return $this->entityReferences;
	}

	/**
	 * Get this Entity in an Array format.
	 *
	 * @return array
	 */
	public function asArray()
	{
		$array = $this->toArray();

		// Add uuid.
		$array['uuid'] = $this->getUuid();

		return $array;
	}

	/**
	 * Get this Entity's references.
	 *
	 * @return Collection of referenced Entity(s)
	 */
	public function getReferences($type)
	{
		return $this->references($type)->get();
	}

	/* =====================================================
	 * Eloquent Relationships
	 * ================================================== */

	/**
	 * An Entity can hasOne EntityIdentifier.
	 *
	 * @return Illuminate\Database\Eloquent\Relations\hasMany
	 */
    public function entityIdentifier()
    {
		return $this->hasMany(IdentifierModel::class, 'entity_id', 'id')
			->where('entity_type', '=', $this->getType());
    }

	/**
	 * An Entity can hasMany EntityReference(s).
	 *
	 * @return Illuminate\Database\Eloquent\Relations\belongsTo
	 */
    public function entityReferences()
    {
		return $this->hasMany(ReferenceModel::class, 'entity_id', 'id')
			->where('entity_type', '=', $this->getType());
    }

	/**
	 * @todo: Not correctly implemented.
	 * 
	 * An Entity can reference many other Entity(s).
	 *
	 * @return Illuminate\Database\Eloquent\Relations\belongsToMany
	 */
    public function references($type)
    {
		// Build and return relation.
		return $this->belongsToMany(Config::get('sorad.entity.aggregates.' . $this->toKebabCase($type)), 'entity_reference', 'entity_id', 'reference_id')
					->withPivot('order')
					->where('entity_type', '=', $this->getType())
					->where('reference_type', '=', $type)
					->whereNull('entity_reference.deleted_at')
					->orderBy('order', 'ASC');
	}
}