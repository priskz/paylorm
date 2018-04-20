<?php namespace Priskz\Paylorm\Data\MySQL\Eloquent;

use DB;
use Priskz\Paylorm\Repository\SearchableRepositoryInterface;

class SearchableCrudRepository extends CurdRepository implements SearchableRepositoryInterface
{
  // @todo: Add documentation
  // @todo: Make $column flexible for multiple columns and dynamic rules.
  // @todo: Expand the $exact/like search a bit.
  /**
   * Filter
   */
  public function filter($column = null, $criteria = null, $range = [], $orderBy = null, $exact = false, $offsetPosition = null, $limit = null, $softDeletes = false)
  {
    // Create the base raw select query.
    $rawQuery = "SELECT * FROM " . $this->models->getTable();

    // Initialize the raw query variable array.
    $filterVariables = [];

    // Flag to determine if one where already exists.
    $whereExists = false;

    // Set the query search criteria parameters.
    if($column && $criteria)
    {
      $rawQuery .= " WHERE ";
      $whereExists = true;

      if($exact)
      {
        array_push( $filterVariables, $criteria );
        $rawQuery .= "$column = ?";
      }
      else
      {
        $criteria = '%'.$criteria.'%';
        array_push( $filterVariables, $criteria );
        $rawQuery .= "$column LIKE ?";
      }
    }

    // Check if we want to include soft deleted models.
    if(! $softDeletes)
    {
      if($whereExists)
      {
        $rawQuery .= " AND deleted_at IS NULL ";
      }
      else
      {
        $whereExists =  true;
        $rawQuery .= " WHERE deleted_at IS NULL ";
      }
    }

    // Set the query range parameters.
    if( ! empty($range['from']) || ! empty($range['to']))
    {
      // Check if a WHERE clause exists in the query yet.
      if($whereExists)
      {
        $rawQuery .= " AND ";
      }
      else
      {
        $rawQuery .= " WHERE ";
      }

      if( ! empty($range['from']) && empty($range['to']) )
      {
        array_push( $filterVariables, $range['from'] );
        $rawQuery .= "created_at < ?";
      }

      if( empty($range['from']) && ! empty($range['to']) )
      {
        array_push( $filterVariables, $range['to'] );
        $rawQuery .= "created_at < ?";
      }

      if($range['from'] && $range['to'])
      {
        array_push( $filterVariables, $range['from'], $range['to'] );
        $rawQuery .= "created_at BETWEEN ? AND ?";
      }
    }

    // Set the order by parameters.
    if($orderBy)
    {
      array_push( $filterVariables, $orderBy );
      $rawQuery .= " ORDER BY ?";
    }

    // Set the order by parameters.
    if($limit !== null && $offsetPosition !== null)
    {
      array_push( $filterVariables, $offsetPosition, $limit );
      $rawQuery .= " LIMIT ? , ?";
    }

    // Perform the raw query.
    $rawResults = DB::select( DB::raw( $rawQuery ), $filterVariables );

    // Hydrate the raw results into actual models.
    $results = $this->models->hydrate( $rawResults );

    // Return the model collection.
    return $results;
  }

  /**
   * Get the total count of this repository.
   *
   * @return int
   */
  public function getTotal()
  {
    $rawQuery = "SELECT count(*) as total FROM orders";

    // Perform the raw query.
    $rawResults = DB::select( DB::raw($rawQuery) );

    // Return the total.
    return $rawResults[0]->total;
  }
}