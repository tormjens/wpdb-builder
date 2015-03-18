<?php namespace WpdbBuilder;

use WpdbBuilder\Translate;

class Wpdb {

	/**
	 * wpdb
	 *
	 * @var \wpdb
	 * */
	private $wpdb;

	/**
	 * prefix
	 *
	 * @var null|string
	 * */
	private $prefix = null;

	/**
	 * statements
	 *
	 * @var array
	 * */
	private $statements = array();

	/**
	 * construct
	 *
	 * @return void
	 * */
	public function __construct() {

		global $wpdb;

		$this->wpdb    = $wpdb;
		$this->tablePrefix  = $wpdb->prefix;

	}

	/**
	 *
	 *
	 * @param unknown $key
	 * @param unknown $value
	 */
	protected function addStatement( $key, $value ) {
		if ( !is_array( $value ) ) {
			$value = array( $value );
		}
		if ( !array_key_exists( $key, $this->statements ) ) {
			$this->statements[$key] = $value;
		} else {
			$this->statements[$key] = array_merge( $this->statements[$key], $value );
		}
	}

	/**
	 * Add table prefix (if given) on given string.
	 *
	 * @param unknown $values
	 * @param bool    $tableFieldMix If we have mixes of field and table names with a "."
	 *
	 * @return array|mixed
	 */
	public function addTablePrefix( $values, $tableFieldMix = true ) {
		if ( is_null( $this->tablePrefix ) ) {
			return $values;
		}
		// $value will be an array and we will add prefix to all table names
		// If supplied value is not an array then make it one
		$single = false;
		if ( !is_array( $values ) ) {
			$values = array( $values );
			// We had single value, so should return a single value
			$single = true;
		}
		$return = array();
		foreach ( $values as $key => $value ) {
			// It's a raw query, just add it to our return array and continue next
			if ( $value instanceof Raw || $value instanceof \Closure ) {
				$return[$key] = $value;
				continue;
			}
			// If key is not integer, it is likely a alias mapping,
			// so we need to change prefix target
			$target = &$value;
			if ( !is_int( $key ) ) {
				$target = &$key;
			}
			if ( !$tableFieldMix || ( $tableFieldMix && strpos( $target, '.' ) !== false ) ) {
				$target = $this->tablePrefix . $target;
			}
			$return[$key] = $value;
		}
		// If we had single value then we should return a single value (end value of the array)
		return $single ? end( $return ) : $return;
	}

	/**
	 *
	 *
	 * @param unknown $tables
	 *
	 * @return static
	 */
	public function table( $tables ) {
		$tables = $this->addTablePrefix( $tables, false );
		$this->addStatement( 'tables', $tables );
		return $this;
	}

	/**
	 *
	 *
	 * @param string  $key
	 * @param string  $operator
	 * @param string  $value
	 *
	 * @return $this
	 */
	public function where( $key, $operator = null, $value = null ) {
		// If two params are given then assume operator is =
		if ( func_num_args() == 2 ) {
			$value = $operator;
			$operator = '=';
		}
		return $this->_where( $key, $operator, $value );
	}

	/**
	 *
	 *
	 * @param string  $key
	 * @param string  $operator
	 * @param string  $value
	 *
	 * @return $this
	 */
	public function orWhere( $key, $operator = null, $value = null ) {
		// If two params are given then assume operator is =
		if ( func_num_args() == 2 ) {
			$value = $operator;
			$operator = '=';
		}
		return $this->_where( $key, $operator, $value, 'OR' );
	}

	/**
	 *
	 *
	 * @param string  $key
	 * @param string  $operator
	 * @param string  $value
	 *
	 * @return $this
	 */
	public function whereNot( $key, $operator = null, $value = null ) {
		// If two params are given then assume operator is =
		if ( func_num_args() == 2 ) {
			$value = $operator;
			$operator = '=';
		}
		return $this->_where( $key, $operator, $value, 'AND NOT' );
	}

	/**
	 *
	 *
	 * @param string  $key
	 * @param string  $operator
	 * @param string  $value
	 *
	 * @return $this
	 */
	public function orWhereNot( $key, $operator = null, $value = null ) {
		// If two params are given then assume operator is =
		if ( func_num_args() == 2 ) {
			$value = $operator;
			$operator = '=';
		}
		return $this->_where( $key, $operator, $value, 'OR NOT' );
	}

	/**
	 *
	 *
	 * @param string  $key
	 * @param array   $values
	 *
	 * @return $this
	 */
	public function whereIn( $key, array $values ) {
		return $this->_where( $key, 'IN', $values, 'AND' );
	}

	/**
	 *
	 *
	 * @param string  $key
	 * @param array   $values
	 *
	 * @return $this
	 */
	public function whereNotIn( $key, array $values ) {
		return $this->_where( $key, 'NOT IN', $values, 'AND' );
	}

	/**
	 *
	 *
	 * @param string  $key
	 * @param array   $values
	 *
	 * @return $this
	 */
	public function orWhereIn( $key, array $values ) {
		return $this->_where( $key, 'IN', $values, 'OR' );
	}

	/**
	 *
	 *
	 * @param string  $key
	 * @param array   $values
	 *
	 * @return $this
	 */
	public function orWhereNotIn( $key, array $values ) {
		return $this->_where( $key, 'NOT IN', $values, 'OR' );
	}

	/**
	 *
	 *
	 * @param string  $key
	 * @param string  $valueFrom
	 * @param string  $valueTo
	 *
	 * @return $this
	 */
	public function whereBetween( $key, $valueFrom, $valueTo ) {
		return $this->_where( $key, 'BETWEEN', array( $valueFrom, $valueTo ), 'AND' );
	}

	/**
	 *
	 *
	 * @param string  $key
	 * @param string  $valueFrom
	 * @param string  $valueTo
	 *
	 * @return $this
	 */
	public function orWhereBetween( $key, $valueFrom, $valueTo ) {
		return $this->_where( $key, 'BETWEEN', array( $valueFrom, $valueTo ), 'OR' );
	}

	/**
	 *
	 *
	 * @param string  $key
	 * @return QueryBuilderHandler
	 */
	public function whereNull( $key ) {
		return $this->_where( $key, 'IS', null );
	}

	/**
	 *
	 *
	 * @param string  $key
	 * @return QueryBuilderHandler
	 */
	public function whereNotNull( $key ) {
		return $this->_where( $key, 'IS NOT', null );
	}

	/**
	 *
	 *
	 * @param string  $key
	 * @return QueryBuilderHandler
	 */
	public function orWhereNull( $key ) {
		return $this->_where( $key, 'IS', null, 'OR' );
	}

	/**
	 *
	 *
	 * @param string  $key
	 * @return QueryBuilderHandler
	 */
	public function orWhereNotNull( $key ) {
		return $this->_where( $key, 'IS NOT', null, 'OR' );
	}

	/**
	 *
	 *
	 * @param string  $key
	 * @param string  $operator
	 * @param string  $value
	 * @param string  $joiner
	 *
	 * @return $this
	 */
	protected function _where( $key, $operator = null, $value = null, $joiner = 'AND' ) {
		$this->statements['wheres'][] = compact( 'key', 'operator', 'value', 'joiner' );
		return $this;
	}

	/**
	 * retrieve results
	 *
	 * @return void
	 * @author
	 * */
	public function get() {

		$sql  = $this->translate();

		$result = $this->query( 'get_results', $sql['sql'], $sql['bindings'] );

		return $result;

	}

    /**
     * Get first row
     *
     * @return \stdClass|null
     */
    public function first() {
        $this->limit(1);
        $result = $this->get();
        return empty($result) ? null : $result[0];
    }

	/**
	 *
	 *
	 * @param unknown $limit
	 *
	 * @return $this
	 */
	public function limit( $limit ) {
		$this->statements['limit'] = $limit;
		return $this;
	}

	/**
	 * translates current query
	 *
	 * @return void
	 * @author
	 * */
	protected function translate() {

		$translator  = new Translate;

		return $translator->select( $this->statements );
	}

	/**
	 * prepare sql
	 *
	 * @param string  $sql
	 *
	 * @return boolean
	 * */
	protected function prepare( $sql, $bindings = array() ) {

		$parameters  = array( $sql );
		$parameters  = array_merge( $parameters, $bindings );

		$result   = call_user_func_array( array( $this->wpdb, 'prepare' ), $parameters );

		return $result;

	}

	/**
	 * execute sql query
	 *
	 * @param string  $sql
	 *
	 * @return boolean
	 * */
	protected function execute( $method, $sql ) {

		if ( method_exists( $this->wpdb, $method ) ) {
			return call_user_func( array( $this->wpdb, $method ), $sql );
		}
		else {
			_doing_it_wrong( 'wpdb::'. $method, sprintf( __( 'The method %s does not exist.' ), 'wpdb::'. $method .'()' ), '1.0' );
		}

	}

	/**
	 * create sql query
	 *
	 * @param string  $sql
	 *
	 * @return boolean
	 * */
	public function query( $method, $sql, $bindings = array() ) {

		$result = $this->execute( $method, $this->prepare( $sql, $bindings ) );

		return $result;

	}



}
