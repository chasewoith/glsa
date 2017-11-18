<?php

require_once(dirname(__FILE__).'/SGError.php');

if (!defined('EZSQL_VERSION'))
	define( 'EZSQL_VERSION', 'WP1.25' );

/**
 * @since 0.71
 */
if (!defined('OBJECT'))
	define( 'OBJECT', 'OBJECT' );
if (!defined('object'))
	define( 'object', 'OBJECT' ); // Back compat.

/**
 * @since 2.5.0
 */
if (!defined('OBJECT_K'))
	define( 'OBJECT_K', 'OBJECT_K' );

/**
 * @since 0.71
 */
if (!defined('ARRAY_A'))
	define( 'ARRAY_A', 'ARRAY_A' );

/**
 * @since 0.71
 */
if (!defined('ARRAY_N'))
	define( 'ARRAY_N', 'ARRAY_N' );

class SGDB {

	/**
	 * Whether to show SQL/DB errors.
	 *
	 * Default behavior is to show errors if both WP_DEBUG and WP_DEBUG_DISPLAY
	 * evaluated to true.
	 *
	 * @since 0.71
	 * @access private
	 * @var bool
	 */
	var $show_errors = false;

	/**
	 * Whether to suppress errors during the DB bootstrapping.
	 *
	 * @access private
	 * @since 2.5.0
	 * @var bool
	 */
	var $suppress_errors = false;

	/**
	 * The last error during query.
	 *
	 * @since 2.5.0
	 * @var string
	 */
	public $last_error = '';

	/**
	 * Amount of queries made
	 *
	 * @since 1.2.0
	 * @access public
	 * @var int
	 */
	public $num_queries = 0;

	/**
	 * Count of rows returned by previous query
	 *
	 * @since 0.71
	 * @access public
	 * @var int
	 */
	public $num_rows = 0;

	/**
	 * Count of affected rows by previous query
	 *
	 * @since 0.71
	 * @access private
	 * @var int
	 */
	var $rows_affected = 0;

	/**
	 * The ID generated for an AUTO_INCREMENT column by the previous query (usually INSERT).
	 *
	 * @since 0.71
	 * @access public
	 * @var int
	 */
	public $insert_id = 0;

	/**
	 * Last query made
	 *
	 * @since 0.71
	 * @access private
	 * @var array
	 */
	var $last_query;

	/**
	 * Results of the last query made
	 *
	 * @since 0.71
	 * @access private
	 * @var array|null
	 */
	var $last_result;

	/**
	 * MySQL result, which is either a resource or boolean.
	 *
	 * @since 0.71
	 * @access protected
	 * @var mixed
	 */
	protected $result;

	/**
	 * Cached column info, for sanity checking data before inserting
	 *
	 * @since 4.2.0
	 * @access protected
	 * @var array
	 */
	protected $col_meta = array();

	/**
	 * Calculated character sets on tables
	 *
	 * @since 4.2.0
	 * @access protected
	 * @var array
	 */
	protected $table_charset = array();

	/**
	 * Whether text fields in the current query need to be sanity checked.
	 *
	 * @since 4.2.0
	 * @access protected
	 * @var bool
	 */
	protected $check_current_query = true;

	/**
	 * Flag to ensure we don't run into recursion problems when checking the collation.
	 *
	 * @since 4.2.0
	 * @access private
	 * @see wpdb::check_safe_collation()
	 * @var bool
	 */
	private $checking_collation = false;

	/**
	 * Saved info on the table column
	 *
	 * @since 0.71
	 * @access protected
	 * @var array
	 */
	protected $col_info;

	/**
	 * Saved queries that were executed
	 *
	 * @since 1.5.0
	 * @access private
	 * @var array
	 */
	var $queries;

	/**
	 * Whether the database queries are ready to start executing.
	 *
	 * @since 2.3.2
	 * @access private
	 * @var bool
	 */
	var $ready = false;

	/**
	 * Format specifiers for DB columns. Columns not listed here default to %s. Initialized during WP load.
	 *
	 * Keys are column names, values are format types: 'ID' => '%d'
	 *
	 * @since 2.8.0
	 * @see wpdb::prepare()
	 * @see wpdb::insert()
	 * @see wpdb::update()
	 * @see wpdb::delete()
	 * @see wp_set_wpdb_vars()
	 * @access public
	 * @var array
	 */
	public $field_types = array();

	/**
	 * Database table columns charset
	 *
	 * @since 2.2.0
	 * @access public
	 * @var string
	 */
	public $charset;

	/**
	 * Database table columns collate
	 *
	 * @since 2.2.0
	 * @access public
	 * @var string
	 */
	public $collate;

	/**
	 * Database Username
	 *
	 * @since 2.9.0
	 * @access protected
	 * @var string
	 */
	protected $dbuser;

	/**
	 * Database Password
	 *
	 * @since 3.1.0
	 * @access protected
	 * @var string
	 */
	protected $dbpassword;

	/**
	 * Database Name
	 *
	 * @since 3.1.0
	 * @access protected
	 * @var string
	 */
	protected $dbname;

	/**
	 * Database Host
	 *
	 * @since 3.1.0
	 * @access protected
	 * @var string
	 */
	protected $dbhost;

	/**
	 * Database Handle
	 *
	 * @since 0.71
	 * @access protected
	 * @var string
	 */
	protected $dbh;

	/**
	 * A textual description of the last query/get_row/get_var call
	 *
	 * @since 3.0.0
	 * @access public
	 * @var string
	 */
	public $func_call;

	/**
	 * Whether MySQL is used as the database engine.
	 *
	 * Set in WPDB::db_connect() to true, by default. This is used when checking
	 * against the required MySQL version for WordPress. Normally, a replacement
	 * database drop-in (db.php) will skip these checks, but setting this to true
	 * will force the checks to occur.
	 *
	 * @since 3.3.0
	 * @access public
	 * @var bool
	 */
	public $is_mysql = null;

	/**
	 * Whether to use mysqli over mysql.
	 *
	 * @since 3.9.0
	 * @access private
	 * @var bool
	 */
	private $use_mysqli = false;

	/**
	 * Whether we've managed to successfully connect at some point
	 *
	 * @since 3.9.0
	 * @access private
	 * @var bool
	 */
	private $has_connected = false;

	/**
	 * Connects to the database server and selects a database
	 *
	 * PHP5 style constructor for compatibility with PHP5. Does
	 * the actual setting up of the class properties and connection
	 * to the database.
	 *
	 * @link https://core.trac.wordpress.org/ticket/3354
	 * @since 2.0.8
	 *
	 * @global string $wp_version
	 *
	 * @param string $dbuser     MySQL database user
	 * @param string $dbpassword MySQL database password
	 * @param string $dbname     MySQL database name
	 * @param string $dbhost     MySQL database host
	 */
	public function __construct( $dbuser, $dbpassword, $dbname, $dbhost ) {
		register_shutdown_function( array( $this, '__destruct' ) );

		/* Use ext/mysqli if it exists and:
		 *  - WP_USE_EXT_MYSQL is defined as false, or
		 *  - We are a development version of WordPress, or
		 *  - We are running PHP 5.5 or greater, or
		 *  - ext/mysql is not loaded.
		 */
		if ( function_exists( 'mysqli_connect' ) ) {
			if ( version_compare( phpversion(), '5.5', '>=' ) || ! function_exists( 'mysql_connect' ) ) {
				$this->use_mysqli = true;
			}
		}

		$this->dbuser = $dbuser;
		$this->dbpassword = $dbpassword;
		$this->dbname = $dbname;
		$this->dbhost = $dbhost;

		$this->db_connect();
	}

	/**
	 * PHP5 style destructor and will run when database object is destroyed.
	 *
	 * @see wpdb::__construct()
	 * @since 2.0.8
	 * @return true
	 */
	public function __destruct() {
		return true;
	}

	/**
	 * Set $this->charset and $this->collate
	 *
	 * @since 3.1.0
	 */
	public function init_charset() {
		$this->charset = DB_CHARSET;
		$this->collate = DB_COLLATE;
	}

	/**
	 * Selects a database using the current database connection.
	 *
	 * The database name will be changed based on the current database
	 * connection. On failure, the execution will bail and display an DB error.
	 *
	 * @since 0.71
	 *
	 * @param string        $db  MySQL database name
	 * @param resource|null $dbh Optional link identifier.
	 */
	public function select( $db, $dbh = null ) {
		if ( is_null($dbh) )
			$dbh = $this->dbh;

		if ( $this->use_mysqli ) {
			$success = mysqli_select_db( $dbh, $db );
		} else {
			$success = mysql_select_db( $db, $dbh );
		}
	}

	/**
	 * Real escape, using mysqli_real_escape_string() or mysql_real_escape_string()
	 *
	 * @see mysqli_real_escape_string()
	 * @see mysql_real_escape_string()
	 * @since 2.8.0
	 * @access private
	 *
	 * @param  string $string to escape
	 * @return string escaped
	 */
	function _real_escape( $string ) {
		if ( $this->dbh ) {
			if ( $this->use_mysqli ) {
				return mysqli_real_escape_string( $this->dbh, $string );
			} else {
				return mysql_real_escape_string( $string, $this->dbh );
			}
		}

		return addslashes( $string );
	}

	/**
	 * Escape data. Works on arrays.
	 *
	 * @uses wpdb::_real_escape()
	 * @since  2.8.0
	 * @access public
	 *
	 * @param  string|array $data
	 * @return string|array escaped
	 */
	public function _escape( $data ) {
		if ( is_array( $data ) ) {
			foreach ( $data as $k => $v ) {
				if ( is_array( $v ) ) {
					$data[$k] = $this->_escape( $v );
				} else {
					$data[$k] = $this->_real_escape( $v );
				}
			}
		} else {
			$data = $this->_real_escape( $data );
		}

		return $data;
	}

	/**
	 * Escapes content by reference for insertion into the database, for security
	 *
	 * @uses wpdb::_real_escape()
	 *
	 * @since 2.3.0
	 *
	 * @param string $string to escape
	 */
	public function escape_by_ref( &$string ) {
		if ( ! is_float( $string ) )
			$string = $this->_real_escape( $string );
	}

	/**
	 * Prepares a SQL query for safe execution. Uses sprintf()-like syntax.
	 *
	 * The following directives can be used in the query format string:
	 *   %d (integer)
	 *   %f (float)
	 *   %s (string)
	 *   %% (literal percentage sign - no argument needed)
	 *
	 * All of %d, %f, and %s are to be left unquoted in the query string and they need an argument passed for them.
	 * Literals (%) as parts of the query must be properly written as %%.
	 *
	 * This function only supports a small subset of the sprintf syntax; it only supports %d (integer), %f (float), and %s (string).
	 * Does not support sign, padding, alignment, width or precision specifiers.
	 * Does not support argument numbering/swapping.
	 *
	 * May be called like {@link https://secure.php.net/sprintf sprintf()} or like {@link https://secure.php.net/vsprintf vsprintf()}.
	 *
	 * Both %d and %s should be left unquoted in the query string.
	 *
	 *     $wpdb->prepare( "SELECT * FROM `table` WHERE `column` = %s AND `field` = %d", 'foo', 1337 );
	 *     $wpdb->prepare( "SELECT DATE_FORMAT(`field`, '%%c') FROM `table` WHERE `column` = %s", 'foo' );
	 *
	 * @link https://secure.php.net/sprintf Description of syntax.
	 * @since 2.3.0
	 *
	 * @param string      $query    Query statement with sprintf()-like placeholders
	 * @param array|mixed $args     The array of variables to substitute into the query's placeholders if being called like
	 *                              {@link https://secure.php.net/vsprintf vsprintf()}, or the first variable to substitute into the query's placeholders if
	 *                              being called like {@link https://secure.php.net/sprintf sprintf()}.
	 * @param mixed       $args,... further variables to substitute into the query's placeholders if being called like
	 *                              {@link https://secure.php.net/sprintf sprintf()}.
	 * @return string|void Sanitized query string, if there is a query to prepare.
	 */
	public function prepare( $query, $args ) {
		if ( is_null( $query ) )
			return;

		$args = func_get_args();
		array_shift( $args );

		// If args were passed as an array (as in vsprintf), move them up
		if ( is_array( $args[0] ) && count( $args ) == 1 ) {
			$args = $args[0];
		}

		$query = str_replace( "'%s'", '%s', $query ); // in case someone mistakenly already singlequoted it
		$query = str_replace( '"%s"', '%s', $query ); // doublequote unquoting
		$query = preg_replace( '|(?<!%)%f|' , '%F', $query ); // Force floats to be locale unaware
		$query = preg_replace( '|(?<!%)%s|', "'%s'", $query ); // quote the strings, avoiding escaped strings like %%s
		$query = preg_replace( '/%(?:%|$|([^dsF]))/', '%%\\1', $query ); // escape any unescaped percents
		array_walk( $args, array( $this, 'escape_by_ref' ) );

		return @vsprintf( $query, $args );
	}

	/**
	 * Print SQL/DB error.
	 *
	 * @since 0.71
	 * @global array $EZSQL_ERROR Stores error information of query and error string
	 *
	 * @param string $str The error to display
	 * @return false|void False if the showing of errors is disabled.
	 */
	public function print_error( $str = '' ) {

		if ( !$str ) {
			if ( $this->use_mysqli ) {
				$str = mysqli_error( $this->dbh );
			} else {
				$str = mysql_error( $this->dbh );
			}
		}
		array( 'query' => $this->last_query, 'error_str' => $str );
	}

	/**
	 * Enables showing of database errors.
	 *
	 * This function should be used only to enable showing of errors.
	 * wpdb::hide_errors() should be used instead for hiding of errors. However,
	 * this function can be used to enable and disable showing of database
	 * errors.
	 *
	 * @since 0.71
	 * @see wpdb::hide_errors()
	 *
	 * @param bool $show Whether to show or hide errors
	 * @return bool Old value for showing errors.
	 */
	public function show_errors( $show = true ) {
		$errors = $this->show_errors;
		$this->show_errors = $show;
		return $errors;
	}

	/**
	 * Disables showing of database errors.
	 *
	 * By default database errors are not shown.
	 *
	 * @since 0.71
	 * @see wpdb::show_errors()
	 *
	 * @return bool Whether showing of errors was active
	 */
	public function hide_errors() {
		$show = $this->show_errors;
		$this->show_errors = false;
		return $show;
	}

	/**
	 * Whether to suppress database errors.
	 *
	 * By default database errors are suppressed, with a simple
	 * call to this function they can be enabled.
	 *
	 * @since 2.5.0
	 * @see wpdb::hide_errors()
	 * @param bool $suppress Optional. New value. Defaults to true.
	 * @return bool Old value
	 */
	public function suppress_errors( $suppress = true ) {
		$errors = $this->suppress_errors;
		$this->suppress_errors = (bool) $suppress;
		return $errors;
	}

	/**
	 * Kill cached query results.
	 *
	 * @since 0.71
	 */
	public function flush() {
		$this->last_result = array();
		$this->col_info    = null;
		$this->last_query  = null;
		$this->rows_affected = $this->num_rows = 0;
		$this->last_error  = '';

		if ( $this->use_mysqli && $this->result instanceof mysqli_result ) {
			mysqli_free_result( $this->result );
			$this->result = null;

			// Sanity check before using the handle
			if ( empty( $this->dbh ) || !( $this->dbh instanceof mysqli ) ) {
				return;
			}

			// Clear out any results from a multi-query
			while ( mysqli_more_results( $this->dbh ) ) {
				mysqli_next_result( $this->dbh );
			}
		} elseif ( is_resource( $this->result ) ) {
			mysql_free_result( $this->result );
		}
	}

	/**
	 * Connect to and select database.
	 *
	 * If $allow_bail is false, the lack of database connection will need
	 * to be handled manually.
	 *
	 * @since 3.0.0
	 * @since 3.9.0 $allow_bail parameter added.
	 *
	 * @param bool $allow_bail Optional. Allows the function to bail. Default true.
	 * @return bool True with a successful connection, false on failure.
	 */
	public function db_connect( $allow_bail = true ) {
		$this->is_mysql = true;

		$new_link = true;
		$client_flags = 0;

		if ( $this->use_mysqli ) {
			$this->dbh = mysqli_init();

			// mysqli_real_connect doesn't support the host param including a port or socket
			// like mysql_connect does. This duplicates how mysql_connect detects a port and/or socket file.
			$port = null;
			$socket = null;
			$host = $this->dbhost;
			$port_or_socket = strstr( $host, ':' );
			if ( ! empty( $port_or_socket ) ) {
				$host = substr( $host, 0, strpos( $host, ':' ) );
				$port_or_socket = substr( $port_or_socket, 1 );
				if ( 0 !== strpos( $port_or_socket, '/' ) ) {
					$port = intval( $port_or_socket );
					$maybe_socket = strstr( $port_or_socket, ':' );
					if ( ! empty( $maybe_socket ) ) {
						$socket = substr( $maybe_socket, 1 );
					}
				} else {
					$socket = $port_or_socket;
				}
			}

			mysqli_real_connect( $this->dbh, $host, $this->dbuser, $this->dbpassword, null, $port, $socket, $client_flags );
		}
		else {
			$this->dbh = @mysql_connect( $this->dbhost, $this->dbuser, $this->dbpassword, $new_link, $client_flags );
		}

		if ( $this->dbh ) {
			$this->has_connected = true;
			$this->ready = true;
			$this->select( $this->dbname, $this->dbh );

			return true;
		}

		return false;
	}

	/**
	 * Perform a MySQL database query, using current database connection.
	 *
	 * More information can be found on the codex page.
	 *
	 * @since 0.71
	 *
	 * @param string $query Database query
	 * @return int|false Number of rows affected/selected or false on error
	 */
	public function query( $query ) {

		$this->flush();

		// Log how the function was called
		$this->func_call = "\$db->query(\"$query\")";

		$this->check_current_query = true;

		// Keep track of the last query for debug.
		$this->last_query = $query;

		$this->_do_query( $query );

		// MySQL server has gone away, try to reconnect.
		$mysql_errno = 0;
		if ( ! empty( $this->dbh ) ) {
			if ( $this->use_mysqli ) {
				if ( $this->dbh instanceof mysqli ) {
					$mysql_errno = mysqli_errno( $this->dbh );
				} else {
					// $dbh is defined, but isn't a real connection.
					// Something has gone horribly wrong, let's try a reconnect.
					$mysql_errno = 2006;
				}
			} else {
				if ( is_resource( $this->dbh ) ) {
					$mysql_errno = mysql_errno( $this->dbh );
				} else {
					$mysql_errno = 2006;
				}
			}
		}

		// If there is an error then take note of it.
		if ( $this->use_mysqli ) {
			if ( $this->dbh instanceof mysqli ) {
				$this->last_error = mysqli_error( $this->dbh );
			} else {
				$this->last_error = _backupGuardT( 'Unable to retrieve the error message from MySQL' );
			}
		} else {
			if ( is_resource( $this->dbh ) ) {
				$this->last_error = mysql_error( $this->dbh );
			} else {
				$this->last_error = _backupGuardT( 'Unable to retrieve the error message from MySQL' );
			}
		}

		if ( $this->last_error ) {
			// Clear insert_id on a subsequent failed insert.
			if ( $this->insert_id && preg_match( '/^\s*(insert|replace)\s/i', $query ) )
				$this->insert_id = 0;

			// $this->print_error();
			return false;
		}

		if ( preg_match( '/^\s*(create|alter|truncate|drop)\s/i', $query ) ) {
			$return_val = $this->result;
		} elseif ( preg_match( '/^\s*(insert|delete|update|replace)\s/i', $query ) ) {
			if ( $this->use_mysqli ) {
				$this->rows_affected = mysqli_affected_rows( $this->dbh );
			} else {
				$this->rows_affected = mysql_affected_rows( $this->dbh );
			}
			// Take note of the insert_id
			if ( preg_match( '/^\s*(insert|replace)\s/i', $query ) ) {
				if ( $this->use_mysqli ) {
					$this->insert_id = mysqli_insert_id( $this->dbh );
				} else {
					$this->insert_id = mysql_insert_id( $this->dbh );
				}
			}
			// Return number of rows affected
			$return_val = $this->rows_affected;
		} else {
			$num_rows = 0;
			if ( $this->use_mysqli && $this->result instanceof mysqli_result ) {
				while ( $row = mysqli_fetch_object( $this->result ) ) {
					$this->last_result[$num_rows] = $row;
					$num_rows++;
				}
			} elseif ( is_resource( $this->result ) ) {
				while ( $row = mysql_fetch_object( $this->result ) ) {
					$this->last_result[$num_rows] = $row;
					$num_rows++;
				}
			}

			// Log number of rows the query returned
			// and return number of rows selected
			$this->num_rows = $num_rows;
			$return_val     = $num_rows;
		}

		return $return_val;
	}

	/**
	 * Internal function to perform the mysql_query() call.
	 *
	 * @since 3.9.0
	 *
	 * @access private
	 * @see wpdb::query()
	 *
	 * @param string $query The query to run.
	 */
	private function _do_query( $query ) {
		if ( ! empty( $this->dbh ) && $this->use_mysqli ) {
			$this->result = mysqli_query( $this->dbh, $query );
		} elseif ( ! empty( $this->dbh ) ) {
			$this->result = mysql_query( $query, $this->dbh );
		}
		$this->num_queries++;
	}

	/**
	 * Insert a row into a table.
	 *
	 *     wpdb::insert( 'table', array( 'column' => 'foo', 'field' => 'bar' ) )
	 *     wpdb::insert( 'table', array( 'column' => 'foo', 'field' => 1337 ), array( '%s', '%d' ) )
	 *
	 * @since 2.5.0
	 * @see wpdb::prepare()
	 * @see wpdb::$field_types
	 * @see wp_set_wpdb_vars()
	 *
	 * @param string       $table  Table name
	 * @param array        $data   Data to insert (in column => value pairs).
	 *                             Both $data columns and $data values should be "raw" (neither should be SQL escaped).
	 *                             Sending a null value will cause the column to be set to NULL - the corresponding format is ignored in this case.
	 * @param array|string $format Optional. An array of formats to be mapped to each of the value in $data.
	 *                             If string, that format will be used for all of the values in $data.
	 *                             A format is one of '%d', '%f', '%s' (integer, float, string).
	 *                             If omitted, all values in $data will be treated as strings unless otherwise specified in wpdb::$field_types.
	 * @return int|false The number of rows inserted, or false on error.
	 */
	public function insert( $table, $data, $format = null ) {
		return $this->_insert_replace_helper( $table, $data, $format, 'INSERT' );
	}

	/**
	 * Replace a row into a table.
	 *
	 *     wpdb::replace( 'table', array( 'column' => 'foo', 'field' => 'bar' ) )
	 *     wpdb::replace( 'table', array( 'column' => 'foo', 'field' => 1337 ), array( '%s', '%d' ) )
	 *
	 * @since 3.0.0
	 * @see wpdb::prepare()
	 * @see wpdb::$field_types
	 * @see wp_set_wpdb_vars()
	 *
	 * @param string       $table  Table name
	 * @param array        $data   Data to insert (in column => value pairs).
	 *                             Both $data columns and $data values should be "raw" (neither should be SQL escaped).
	 *                             Sending a null value will cause the column to be set to NULL - the corresponding format is ignored in this case.
	 * @param array|string $format Optional. An array of formats to be mapped to each of the value in $data.
	 *                             If string, that format will be used for all of the values in $data.
	 *                             A format is one of '%d', '%f', '%s' (integer, float, string).
	 *                             If omitted, all values in $data will be treated as strings unless otherwise specified in wpdb::$field_types.
	 * @return int|false The number of rows affected, or false on error.
	 */
	public function replace( $table, $data, $format = null ) {
		return $this->_insert_replace_helper( $table, $data, $format, 'REPLACE' );
	}

	/**
	 * Helper function for insert and replace.
	 *
	 * Runs an insert or replace query based on $type argument.
	 *
	 * @access private
	 * @since 3.0.0
	 * @see wpdb::prepare()
	 * @see wpdb::$field_types
	 * @see wp_set_wpdb_vars()
	 *
	 * @param string       $table  Table name
	 * @param array        $data   Data to insert (in column => value pairs).
	 *                             Both $data columns and $data values should be "raw" (neither should be SQL escaped).
	 *                             Sending a null value will cause the column to be set to NULL - the corresponding format is ignored in this case.
	 * @param array|string $format Optional. An array of formats to be mapped to each of the value in $data.
	 *                             If string, that format will be used for all of the values in $data.
	 *                             A format is one of '%d', '%f', '%s' (integer, float, string).
	 *                             If omitted, all values in $data will be treated as strings unless otherwise specified in wpdb::$field_types.
	 * @param string $type         Optional. What type of operation is this? INSERT or REPLACE. Defaults to INSERT.
	 * @return int|false The number of rows affected, or false on error.
	 */
	function _insert_replace_helper( $table, $data, $format = null, $type = 'INSERT' ) {
		$this->insert_id = 0;

		if ( ! in_array( strtoupper( $type ), array( 'REPLACE', 'INSERT' ) ) ) {
			return false;
		}

		$data = $this->process_fields( $table, $data, $format );
		if ( false === $data ) {
			return false;
		}

		$formats = $values = array();
		foreach ( $data as $value ) {
			if ( is_null( $value['value'] ) ) {
				$formats[] = 'NULL';
				continue;
			}

			$formats[] = $value['format'];
			$values[]  = $value['value'];
		}

		$fields  = '`' . implode( '`, `', array_keys( $data ) ) . '`';
		$formats = implode( ', ', $formats );

		$sql = "$type INTO `$table` ($fields) VALUES ($formats)";

		$this->check_current_query = false;
		return $this->query( $this->prepare( $sql, $values ) );
	}

	/**
	 * Update a row in the table
	 *
	 *     wpdb::update( 'table', array( 'column' => 'foo', 'field' => 'bar' ), array( 'ID' => 1 ) )
	 *     wpdb::update( 'table', array( 'column' => 'foo', 'field' => 1337 ), array( 'ID' => 1 ), array( '%s', '%d' ), array( '%d' ) )
	 *
	 * @since 2.5.0
	 * @see wpdb::prepare()
	 * @see wpdb::$field_types
	 * @see wp_set_wpdb_vars()
	 *
	 * @param string       $table        Table name
	 * @param array        $data         Data to update (in column => value pairs).
	 *                                   Both $data columns and $data values should be "raw" (neither should be SQL escaped).
	 *                                   Sending a null value will cause the column to be set to NULL - the corresponding
	 *                                   format is ignored in this case.
	 * @param array        $where        A named array of WHERE clauses (in column => value pairs).
	 *                                   Multiple clauses will be joined with ANDs.
	 *                                   Both $where columns and $where values should be "raw".
	 *                                   Sending a null value will create an IS NULL comparison - the corresponding format will be ignored in this case.
	 * @param array|string $format       Optional. An array of formats to be mapped to each of the values in $data.
	 *                                   If string, that format will be used for all of the values in $data.
	 *                                   A format is one of '%d', '%f', '%s' (integer, float, string).
	 *                                   If omitted, all values in $data will be treated as strings unless otherwise specified in wpdb::$field_types.
	 * @param array|string $where_format Optional. An array of formats to be mapped to each of the values in $where.
	 *                                   If string, that format will be used for all of the items in $where.
	 *                                   A format is one of '%d', '%f', '%s' (integer, float, string).
	 *                                   If omitted, all values in $where will be treated as strings.
	 * @return int|false The number of rows updated, or false on error.
	 */
	public function update( $table, $data, $where, $format = null, $where_format = null ) {
		if ( ! is_array( $data ) || ! is_array( $where ) ) {
			return false;
		}

		$data = $this->process_fields( $table, $data, $format );
		if ( false === $data ) {
			return false;
		}
		$where = $this->process_fields( $table, $where, $where_format );
		if ( false === $where ) {
			return false;
		}

		$fields = $conditions = $values = array();
		foreach ( $data as $field => $value ) {
			if ( is_null( $value['value'] ) ) {
				$fields[] = "`$field` = NULL";
				continue;
			}

			$fields[] = "`$field` = " . $value['format'];
			$values[] = $value['value'];
		}
		foreach ( $where as $field => $value ) {
			if ( is_null( $value['value'] ) ) {
				$conditions[] = "`$field` IS NULL";
				continue;
			}

			$conditions[] = "`$field` = " . $value['format'];
			$values[] = $value['value'];
		}

		$fields = implode( ', ', $fields );
		$conditions = implode( ' AND ', $conditions );

		$sql = "UPDATE `$table` SET $fields WHERE $conditions";

		$this->check_current_query = false;
		return $this->query( $this->prepare( $sql, $values ) );
	}

	/**
	 * Delete a row in the table
	 *
	 *     wpdb::delete( 'table', array( 'ID' => 1 ) )
	 *     wpdb::delete( 'table', array( 'ID' => 1 ), array( '%d' ) )
	 *
	 * @since 3.4.0
	 * @see wpdb::prepare()
	 * @see wpdb::$field_types
	 * @see wp_set_wpdb_vars()
	 *
	 * @param string       $table        Table name
	 * @param array        $where        A named array of WHERE clauses (in column => value pairs).
	 *                                   Multiple clauses will be joined with ANDs.
	 *                                   Both $where columns and $where values should be "raw".
	 *                                   Sending a null value will create an IS NULL comparison - the corresponding format will be ignored in this case.
	 * @param array|string $where_format Optional. An array of formats to be mapped to each of the values in $where.
	 *                                   If string, that format will be used for all of the items in $where.
	 *                                   A format is one of '%d', '%f', '%s' (integer, float, string).
	 *                                   If omitted, all values in $where will be treated as strings unless otherwise specified in wpdb::$field_types.
	 * @return int|false The number of rows updated, or false on error.
	 */
	public function delete( $table, $where, $where_format = null ) {
		if ( ! is_array( $where ) ) {
			return false;
		}

		$where = $this->process_fields( $table, $where, $where_format );
		if ( false === $where ) {
			return false;
		}

		$conditions = $values = array();
		foreach ( $where as $field => $value ) {
			if ( is_null( $value['value'] ) ) {
				$conditions[] = "`$field` IS NULL";
				continue;
			}

			$conditions[] = "`$field` = " . $value['format'];
			$values[] = $value['value'];
		}

		$conditions = implode( ' AND ', $conditions );

		$sql = "DELETE FROM `$table` WHERE $conditions";

		$this->check_current_query = false;
		return $this->query( $this->prepare( $sql, $values ) );
	}

	/**
	 * Processes arrays of field/value pairs and field formats.
	 *
	 * This is a helper method for wpdb's CRUD methods, which take field/value
	 * pairs for inserts, updates, and where clauses. This method first pairs
	 * each value with a format. Then it determines the charset of that field,
	 * using that to determine if any invalid text would be stripped. If text is
	 * stripped, then field processing is rejected and the query fails.
	 *
	 * @since 4.2.0
	 * @access protected
	 *
	 * @param string $table  Table name.
	 * @param array  $data   Field/value pair.
	 * @param mixed  $format Format for each field.
	 * @return array|false Returns an array of fields that contain paired values
	 *                    and formats. Returns false for invalid values.
	 */
	protected function process_fields( $table, $data, $format ) {
		$data = $this->process_field_formats( $data, $format );
		if ( false === $data ) {
			return false;
		}

		$data = $this->process_field_charsets( $data, $table );
		if ( false === $data ) {
			return false;
		}

		$data = $this->process_field_lengths( $data, $table );
		if ( false === $data ) {
			return false;
		}

		$converted_data = $this->strip_invalid_text( $data );

		if ( $data !== $converted_data ) {
			return false;
		}

		return $data;
	}

	/**
	 * Prepares arrays of value/format pairs as passed to wpdb CRUD methods.
	 *
	 * @since 4.2.0
	 * @access protected
	 *
	 * @param array $data   Array of fields to values.
	 * @param mixed $format Formats to be mapped to the values in $data.
	 * @return array Array, keyed by field names with values being an array
	 *               of 'value' and 'format' keys.
	 */
	protected function process_field_formats( $data, $format ) {
		$formats = $original_formats = (array) $format;

		foreach ( $data as $field => $value ) {
			$value = array(
				'value'  => $value,
				'format' => '%s',
			);

			if ( ! empty( $format ) ) {
				$value['format'] = array_shift( $formats );
				if ( ! $value['format'] ) {
					$value['format'] = reset( $original_formats );
				}
			} elseif ( isset( $this->field_types[ $field ] ) ) {
				$value['format'] = $this->field_types[ $field ];
			}

			$data[ $field ] = $value;
		}

		return $data;
	}

	/**
	 * Adds field charsets to field/value/format arrays generated by
	 * the wpdb::process_field_formats() method.
	 *
	 * @since 4.2.0
	 * @access protected
	 *
	 * @param array  $data  As it comes from the wpdb::process_field_formats() method.
	 * @param string $table Table name.
	 * @return array|false The same array as $data with additional 'charset' keys.
	 */
	protected function process_field_charsets( $data, $table ) {
		foreach ( $data as $field => $value ) {
			if ( '%d' === $value['format'] || '%f' === $value['format'] ) {
				/*
				 * We can skip this field if we know it isn't a string.
				 * This checks %d/%f versus ! %s because its sprintf() could take more.
				 */
				$value['charset'] = false;
			} else {
				$value['charset'] = $this->get_col_charset( $table, $field );
				if ( $this->is_wp_error( $value['charset'] ) ) {
					return false;
				}
			}

			$data[ $field ] = $value;
		}

		return $data;
	}

	/**
	 * For string fields, record the maximum string length that field can safely save.
	 *
	 * @since 4.2.1
	 * @access protected
	 *
	 * @param array  $data  As it comes from the wpdb::process_field_charsets() method.
	 * @param string $table Table name.
	 * @return array|false The same array as $data with additional 'length' keys, or false if
	 *                     any of the values were too long for their corresponding field.
	 */
	protected function process_field_lengths( $data, $table ) {
		foreach ( $data as $field => $value ) {
			if ( '%d' === $value['format'] || '%f' === $value['format'] ) {
				/*
				 * We can skip this field if we know it isn't a string.
				 * This checks %d/%f versus ! %s because its sprintf() could take more.
				 */
				$value['length'] = false;
			} else {
				$value['length'] = $this->get_col_length( $table, $field );
				if ( $this->is_wp_error( $value['length'] ) ) {
					return false;
				}
			}

			$data[ $field ] = $value;
		}

		return $data;
	}

	/**
	 * Retrieve an entire SQL result set from the database (i.e., many rows)
	 *
	 * Executes a SQL query and returns the entire SQL result.
	 *
	 * @since 0.71
	 *
	 * @param string $query  SQL query.
	 * @param string $output Optional. Any of ARRAY_A | ARRAY_N | OBJECT | OBJECT_K constants.
	 *                       With one of the first three, return an array of rows indexed from 0 by SQL result row number.
	 *                       Each row is an associative array (column => value, ...), a numerically indexed array (0 => value, ...), or an object. ( ->column = value ), respectively.
	 *                       With OBJECT_K, return an associative array of row objects keyed by the value of each row's first column's value.
	 *                       Duplicate keys are discarded.
	 * @return array|object|null Database query results
	 */
	public function get_results( $query = null, $output = OBJECT ) {
		$this->func_call = "\$db->get_results(\"$query\", $output)";

		if ( $query ) {
			$this->query( $query );
		} else {
			return null;
		}

		$new_array = array();
		if ( $output == OBJECT ) {
			// Return an integer-keyed array of row objects
			return $this->last_result;
		} elseif ( $output == OBJECT_K ) {
			// Return an array of row objects with keys from column 1
			// (Duplicates are discarded)
			foreach ( $this->last_result as $row ) {
				$var_by_ref = get_object_vars( $row );
				$key = array_shift( $var_by_ref );
				if ( ! isset( $new_array[ $key ] ) )
					$new_array[ $key ] = $row;
			}
			return $new_array;
		} elseif ( $output == ARRAY_A || $output == ARRAY_N ) {
			// Return an integer-keyed array of...
			if ( $this->last_result ) {
				foreach ( (array) $this->last_result as $row ) {
					if ( $output == ARRAY_N ) {
						// ...integer-keyed row arrays
						$new_array[] = array_values( get_object_vars( $row ) );
					} else {
						// ...column name-keyed row arrays
						$new_array[] = get_object_vars( $row );
					}
				}
			}
			return $new_array;
		} elseif ( strtoupper( $output ) === OBJECT ) {
			// Back compat for OBJECT being previously case insensitive.
			return $this->last_result;
		}
		return null;
	}

	/**
	 * Closes the current database connection.
	 *
	 * @since 4.5.0
	 * @access public
	 *
	 * @return bool True if the connection was successfully closed, false if it wasn't,
	 *              or the connection doesn't exist.
	 */
	public function close() {
		if ( ! $this->dbh ) {
			return false;
		}

		if ( $this->use_mysqli ) {
			$closed = mysqli_close( $this->dbh );
		} else {
			$closed = mysql_close( $this->dbh );
		}

		if ( $closed ) {
			$this->dbh = null;
			$this->ready = false;
			$this->has_connected = false;
		}

		return $closed;
	}

	private function is_wp_error( $thing ) {
		return ( $thing instanceof SGError );
	}

	/**
	 * Retrieves the MySQL server version.
	 *
	 * @since 2.7.0
	 *
	 * @return null|string Null on failure, version number on success.
	 */
	public function db_version() {
		if ( $this->use_mysqli ) {
			$server_info = mysqli_get_server_info( $this->dbh );
		} else {
			$server_info = mysql_get_server_info( $this->dbh );
		}
		return preg_replace( '/[^0-9.].*/', '', $server_info );
	}
}
