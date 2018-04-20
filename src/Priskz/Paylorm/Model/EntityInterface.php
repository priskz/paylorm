<?php namespace Priskz\Paylorm\Model;

/**
 * A Model can also be used as an "entity" in a nosql data storage format.
 */
interface EntityInterface
{
	/**
	 * Get this Entity's type.
	 *
	 * @param  bool   $kebabCase
	 * @return string
	 */
	public function getType($kebabCase);

	/**
	 * Get this Entity's EntityIdentifier.
	 *
	 * @return EntityIdentifier
	 */
	public function getEntityIdentifier();

	/**
	 * Get this Entity's Uuid.
	 *
	 * @return string
	 */
	public function getUuid();

	/**
	 * Get this Entity's EntityReference.
	 *
	 * @return Collection of EntityReference(s)
	 */
	public function getEntityReferences();

	/**
	 * Get this Entity in an Array format.
	 *
	 * @return array
	 */
	public function asArray();

	/**
	 * Get this Entity's references.
	 *
	 * @return Collection of referenced Entity(s)
	 */
	public function getReferences($type);
}