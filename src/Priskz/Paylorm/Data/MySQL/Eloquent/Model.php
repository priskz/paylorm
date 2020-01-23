<?php

namespace Priskz\Paylorm\Data\MySQL\Eloquent;

use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Support\Str;
use Priskz\Paylorm\Model\BaseInterface;
use Priskz\Paylorm\Model\DateableInterface;

class Model extends EloquentModel implements BaseInterface, DateableInterface
{
    /**
     * The relations to eager load and embed on every query.
     *
     * @var array
     */
    protected $embed = [];

    /**
     * Create a new instance.
     *
     * @param  array   $attributes
     * @param  string  $connection
     * @return void
     */
    public function __construct(array $attributes = [], $connection = null)
    {
        parent::__construct($attributes);

        // Initialization.
        $this->init($connection);
    }

    /**
     * Initialization Logic.
     *
     * @param  string  $connection
     * @return void
     */
    protected function init($connection = null)
    {
        // Set connection if given.
        if( ! is_null($connection))
        {
            $this->setConnection($connection);
        }

        // Entity?
        if($this->entity)
        {
            $this->with = array_merge($this->getEmbeddable(), $this->embed);
        }
    }

    /**
     * When automapping the table name, use singular case rather than plural.
     *
     * @return string
     */
    public function getTable()
    {
        // If the table is set manually, return it.
        if(isset($this->table))
        {
            return $this->table;
        }

        // Return only the class name in snake case.
        return snake_case(array_pop( explode('\\', get_class($this))));
    }

    /**
     * Get this Entity's type.
     *
     * @todo: Refactor optional param away, pick snake or upper case to be universal.
     *
     * @param  bool   $kebabCase
     * @return string
     */
    public function getType($kebabCase = false)
    {
        if($kebabCase)
        {
            return strtolower(str_replace('_', '-', $this->getTable()));
        }

        return strtoupper($this->getTable());
    }

    /**
     * Get this Model in an Array format.
     *
     * @return array
     */
    public function asArray()
    {
        return $this->toArray();
    }

    /**
     * Get this Model's creation time.
     *
     * @return timestamp
     */
    public function getCreatedAt()
    {
        return $this->created_at;
    }

    /**
     * Get this Model's last updated time.
     *
     * @return timestamp
     */
    public function getUpdatedAt()
    {
        return $this->updated_at;
    }

    /**
     * Set the model's Table to utilize.
     *
     * @param  string $tableName
     * @return void
     */
    public function setTable($tableName)
    {
        $this->table = $tableName;
    }

    /* =====================================================
     * Helpers
     * ================================================== */

    /**
     * Convert a string to kebab case.
     *
     * @param  string $string
     * @return string
     */
    private function toKebabCase(string $string)
    {
        return strtolower(str_replace(['_', ' '], '-', $string)) ;
    }
}