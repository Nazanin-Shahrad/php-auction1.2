<?php
namespace App\Lib;

use App\Exceptions\ClassException;
use PDO;
use ReflectionClass;
use ReflectionException;
use ReflectionProperty;

/**
 * Class Model
 * @package App\Lib
 */
abstract class Model {
	use Helper;

	/**
	 * Searches the table for all records matching an associative array of conditions,
	 * "Group By", "Order By" and "Limit" terms. Returns records as array of objects
	 * @param null   $cond
	 * @param string $groupBy
	 * @param string $orderBy
	 * @param null   $limit
	 * @return array
	 */
	public static function find($cond = null, string $groupBy = null, string $orderBy = null, $limit = null): array {
		$db = Database::getConnection();
		$sql = "SELECT * FROM `" . static::$table_name . "`";
		if(is_array($cond)) {
			$sql .= " WHERE ";
			$bindings = [];
			foreach($cond as $key => $value) {
				$bindings[] = "`$key` = :$key";
			}
			$sql .= implode(" AND ", $bindings);
		} else if($cond != null) {
			$sql .= " WHERE $cond";
		}

		if(isset($groupBy))
			$sql .= " GROUP BY $groupBy";

		if(isset($orderBy))
			$sql .= " ORDER BY $orderBy";

		if(isset($limit))
			$sql .= " LIMIT $limit";

		$results = $db->fetch($sql, get_called_class(), $cond);
		return $results;
	}

	/**
	 * Executes query and returns first record as object
	 * @param null   $cond
	 * @param string $groupBy
	 * @return mixed
	 * @throws ClassException
	 */
	public static function findFirst($cond = null, string $groupBy = null) {
		$objs = static::find($cond, $groupBy, null, 1);
		if(empty($objs)) throw new ClassException("Model not found");
		return array_shift($objs);
	}

	/**
	 * Executes query & return all records as array of objects
	 * @param string $groupBy
	 * @param string $orderBy
	 * @return array
	 * @throws ClassException
	 */
	public static function all(string $groupBy = null, string $orderBy = null): array {
		$objs = static::find(null, $groupBy, $orderBy, null);
		if(empty($objs)) throw new ClassException("Model not found");
		return $objs;
	}

}
