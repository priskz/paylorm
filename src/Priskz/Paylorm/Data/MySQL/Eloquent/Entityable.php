<?php

namespace Priskz\Paylorm\Data\MySQL\Eloquent;

use Domain\EntityAccess\Data\MySQL\Eloquent\Model as EntityAccessModel;
use Domain\Entity\Data\MySQL\Eloquent\Model as EntityModel;

trait Entityable
{
    /**
     * Entity Aggregates
     */
    protected $entityAccessAggregate = 'access';
    protected $entityAggregate       = 'entity';
    protected $entityTypeAggregate   = 'entity.type';

    /**
     * Entity Flag
     */
    protected $entity = true;

    /**
     * An Entity can has many Domain\EntityAccess(s).
     *
     * @return array
     */
    protected function getEmbeddable()
    {
        return [
            $this->entityAccessAggregate,
            $this->entityAggregate,
            $this->entityTypeAggregate
        ];
    }

    /**
     * An Entity can has many Domain\EntityAccess(s).
     *
     * @return Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function access()
    {
        return $this->hasMany(EntityAccessModel::class, 'aggregate_id')
            ->where('type', '=', $this->getTable());
    }

    /**
     * An Entity can has one Domain\Entity.
     *
     * @return Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function entity()
    {
        return $this->hasOne(EntityModel::class, 'aggregate_id')
                    ->join('entity_type', function($join) {
                        $join->on('entity_type.id', '=', 'entity.entity_type_id')
                             ->where('entity_type.name', '=', $this->getTable());
                });
    }
}
