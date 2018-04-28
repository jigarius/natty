<?php

namespace Natty\DBAL\Base;

/**
 * Database Connection Object
 * @author JigaR Mehta | Greenpill Productions
 */
abstract class Connection 
extends \PDO {
    
    /**
     * A prefix to use for tables on this database
     * @var string
     */
    protected $prefix;
    
    /**
     * Unique identification for the database connection
     * @var string
     */
    protected $id;
    
    /**
     * Name of the database associated with this connection
     * @var string
     */
    protected $dbname;
    
    /**
     * A QueryHelper object for this connection
     * @var \Natty\DBAL\Base\QueryHelperAbstract
     */
    protected $queryHelper;
    
    /**
     * A QueryHelper object for this connection
     * @var \Natty\DBAL\Base\SchemaHelperAbstract
     */
    protected $schemaHelper;
    
    /**
     * The last executed statement on this DBO
     * @var Statement
     */
    protected $statement;
    
    /**
     * Constraint violation error
     */
    const ERR_CONSTRAINT = 23000;
    
    /**
     * Creates a database connection from a configuration array
     * @param array $config Array of configuration parameters
     * @throws \PDOException On connection failure
     */
    public function __construct( array $config, $id = 'default' ) {
        
        // Interpret arguments
        $config = (array) $config;
        $defaults = array (
            'host' => 'localhost',
            'driver' => 'mysql',
            'username' => null,
            'password' => null,
            'prefix' => null
        );
        $config = array_merge($defaults, $config);
        extract($config);
        
        // Check if a database name has been specified
        if ( !$dbname )
            trigger_error('No "dbname" specified in config!', E_USER_ERROR);
        
        // Determine whether the driver is supported
        $supported_drivers = $this->getAvailableDrivers();
        if ( !in_array($driver, $supported_drivers) )
            throw new \PDOException('Database driver "' . $driver . '" not is not supported!');
        
        // Connect to the daatabse server
        $dsn = sprintf('%s:host=%s;dbname=%s', $driver, $host, $dbname);
        $options = array (
            static::ATTR_DEFAULT_FETCH_MODE => static::FETCH_ASSOC,
            static::ATTR_STATEMENT_CLASS => array ('Natty\\DBAL\\Base\\Statement'),
            static::ATTR_ERRMODE => static::ERRMODE_EXCEPTION,
        );
        parent::__construct($dsn, $username, $password, $options);
        
        $this->prefix = $prefix;
        $this->dbname = $dbname;
        $this->id = $id;
        
        // Set UTF-8 charset
        $this->exec('SET NAMES UTF8');
        
    }
    
    /**
     * Compiles a Query Object into a syntactically correct Query string 
     * as per the database driver (if an object is passed). For string queries,
     * renders table escapes and prefixes, etc.
     * @param string|Query $query The query to compile
     * @return string|false String statement on success or false on failure
     */
    public function compile($query) {
        return $this->getQueryHelper()->render($query);
    }
    
    public function exec($query) {
        $query = $this->compile($query);
        return parent::exec($query);
    }
    
    /**
     * Executes a query and returns the relevant statement handle
     * @todo Remove method and use ::exec() instead
     * @param mixed $query
     * @return \Natty\DBAL\Base\Statement|false
     */
    public function execute($query) {
        $stmt = $this->prepare($query);
        try {
            return $stmt->execute();
        } catch(Exception $e) {
            die ($e->getMessage());
        }
    }
    
    public function getDbName() {
        return $this->dbname;
    }
    
    public function getId() {
        return $this->id;
    }
    
    /**
     * Returns the connection-specific QueryHelper
     * @return \QueryHelperAbstract
     */
    public function getQueryHelper() {
        if ( !isset ($this->queryHelper) ):
            $driver = $this->getAttribute(self::ATTR_DRIVER_NAME);
            $classname = 'Natty.DBAL.' . ucfirst($driver) . '.QueryHelper';
            $this->queryHelper = \Natty::create($classname, array ($this));
        endif;
        return $this->queryHelper;
    }
    
    /**
     * Returns the connection-specific SchemaHelper
     * @return \Natty\DBAL\Base\SchemaHelperAbstract
     */
    public function getSchemaHelper() {
        if ( !isset ($this->schemaHelper) ):
            $driver = $this->getAttribute(self::ATTR_DRIVER_NAME);
            $classname = 'Natty.DBAL.' . ucfirst($driver) . '.SchemaHelper';
            $this->schemaHelper = \Natty::create($classname, array ($this));
        endif;
        return $this->schemaHelper;
    }
    
    public function getPrefix() {
        return $this->prefix;
    }
    
    /**
     * Creates a Query object of the given type
     * @param string $type Query type - SELECT, UPDATE, INSERT, DELETE, etc.
     * @param string $tablename [Optional] Primary tablename for the query
     * @return \Natty\DBAL\QueryBuilder
     */
    public function getQuery( $type, $tablename = null ) {
        
        // Validate parameters
        if ( !is_string($type) || empty ($type) )
            throw new \InvalidArgumentException('Argument 1 expected to be a string!');
        if ( 'custom' != $type && (!is_string($tablename) || empty ($tablename)) )
            throw new \InvalidArgumentException('Argument 2 expected to be a string!');
        
        $type = ucfirst($type);
        
        $query_class = 'Natty\\DBAL\\' . $type . 'Query';
        if ( class_exists($query_class) )
            return new $query_class($tablename, $this);
        
        throw new \InvalidArgumentException('Invalid query type "' . $type . '" could not be returned!', E_USER_ERROR);
        
    }
    
    /**
     * Returns the relevant database table object
     * @param string $tablename Name of the table to reutrn
     * @return Table The PDO Table object
     */
    public function getTable( $tablename ) {
        
        $caller = debug_backtrace();
        $caller = array_shift($caller);
        \Natty\Console::debug('Deprecated method ' . __METHOD__ . ' was called from ' . $caller['file'] . ':' . $caller['line'] . '.');
        
        $classname = __NAMESPACE__ . '\\Table';
        return new $classname($tablename, $this);
        
    }
    
    /**
     * Performs internal cleanup before beginning a new transaction/query.
     */
    public function reset() {
        if ( isset ($this->statement) ):
            $this->statement->closeCursor();
            unset ($this->statement);
        endif;
        return $this;
    }
    
    /**
     * Pre-processes query options and converts implied conditions into actual
     * Query Builder API friendly conditions.
     * @param array $options
     */
    public function touchOptions(array &$options) {
        
        // Force defaults
        $options = array_merge(array (
            'conditions' => array (),
            'parameters' => array (),
        ), $options);
        
        // Translate key-data into conditions
        if ( isset ($options['key']) ):
            
            // Must be an array of keys
            if ( !is_array($options['key']) )
                $options['key'] = array ($options['key']);
            
            foreach ( $options['key'] as $key_name => $key_value ):
                
                // Is it a NULL condition?
                if ( is_null($key_value) ):
                    $options['conditions'][] = array ('AND', '{' . $key_name . '} IS NULL');
                    continue;
                endif;
                
                $options['conditions'][] = array ('AND', array ($key_name, '=', ':key_' . $key_name));
                $options['parameters']['key_' . $key_name] = $key_value;
                
            endforeach;
            
            unset ($options['key']);
            
        endif;
        
    }
    
    public function prepare( $query, $driver_options = null ) {
        $query = $this->compile($query);
        $driver_options = (array) $driver_options;
        return parent::prepare($query, $driver_options);
    }
    
    public function query( $query ) {
        $query = $this->compile($query);
        return parent::query($query);
    }
    
    /**
     * Inserts a record into a table
     * @param string $tablename Name of the table
     * @param array $record Record to insert as an associative array
     * @param array $options [optional] An array of options
     * @return integer|boolean Insert ID or true on success otherwise, false.
     */
    public function insert($tablename, array $record, array $options = array ()) {
        
        $this->touchOptions($options);
        
        // Prepare the query
        $this->statement = $this->reset()
                ->getQuery('insert', $tablename)
                ->addColumns(array_keys($record))
                ->prepare();
        
        // Return false on failure
        if ( !$this->statement->execute($record) )
            return FALSE;
        
        // Return last insert id
        if ( $lid = $this->lastInsertId() )
            return $lid;
        
        return TRUE;
        
    }
    
    /**
     * 
     * @param type $tablename
     * @param array $record
     * @param array $options
     * @return type
     * @throws \InvalidArgumentException
     */
    public function update($tablename, array $record, array $options = array ()) {
        
        // Update as per keys in the record itself
        if ( isset ($options['keys']) ):
            
            if ( !isset ($options['key']) )
                $options['key'] = array ();
            
            foreach ( $options['keys'] as $key_name ):
                
                if ( !isset ($record[$key_name]) )
                    throw new \InvalidArgumentException('Required index "' . $key_name . '" not specified in record.');
                
                $options['key'][$key_name] = $record[$key_name];
                
            endforeach;
            
        endif;
        
        $this->touchOptions($options);
        
        // Must have conditions
        if ( 0 === sizeof($options['conditions']) )
            throw new \Natty\DBAL\QueryException('Update query must specify conditions.');
        
        // Add data to update
        $options['parameters'] = array_merge($options['parameters'], $record);
        
        $this->statement = $this->reset()
                ->getQuery('update', $tablename)
                ->addColumns(array_keys($record))
                ->addComplexCondition($options['conditions'])
                ->prepare();
        
        return $this->statement->execute($options['parameters']);
        
    }
    
    /**
     * 
     * @param string $tablename
     * @param array $record
     * @param array $options
     * @return mixed Auto insertion ID or a boolean
     * @throws \InvalidArgumentException If "keys" are not provided
     * or the record to be upserted does not have data in these "keys".
     * @throws \RuntimeException
     */
    public function upsert($tablename, array $record, array $options = array ()) {
        
        // Must specify the option "key"
        if ( !isset ($options['keys']) )
            throw new \InvalidArgumentException('Required option "keys" not specified');
        
        // Determine lookup key
        $read_query = $this->reset()
                ->getQuery('select', $tablename);
        $lookup_keys = array ();
        foreach ( $options['keys'] as $key_name ):

            if ( !isset ($record[$key_name]) )
                throw new \InvalidArgumentException('Required index "' . $key_name . '" not specified in record.');

            $read_query->addComplexCondition('AND', array ($key_name, '=', ':' . $key_name));
            $lookup_keys[$key_name] = $record[$key_name];

        endforeach;
        $stmt = $read_query->execute($lookup_keys);
        
        // Record exists? Then update.
        if ( $stmt->rowCount() > 0 ) {
            if ( 1 !== $stmt->rowCount() )
                throw new \RuntimeException('Cannot use upsert with non-unique keys.');
            $output = $this->update($tablename, $record, $options);
        }
        // Insert record
        else {
            $output = $this->insert($tablename, $record);
        }
        
        return $output;
        
    }
    
    /**
     * 
     * @param type $tablename
     * @param array $options
     * @return type
     * @throws \InvalidArgumentException
     */
    public function delete($tablename, array $options = array ()) {
        
        $this->touchOptions($options);
        
        // Must have conditions
        if ( empty ($options['conditions']) )
            throw new \Natty\DBAL\QueryException('Delete query must specify conditions.');
        
        // Build query
        $this->statement = $this->reset()
                ->getQuery('delete', $tablename)
                ->addComplexCondition($options['conditions'])
                ->prepare();
        
        return $this->statement->execute($options['parameters']);
        
    }
    
    /**
     * 
     * @param type $tablename
     * @param array $options An array of options including:<br />
     * conditions: Array of conditions<br />
     * columns: [optional] Array of columns to be read<br />
     * ordering: [optional] The ORDER BY clause as an associative array. The
     * keys of this array are the column names and their values would be the
     * direction of ordering.<br />
     * grouping: [optional] An array of column names for the GROUP BY clause.<br />
     * unique: [optional] Whether a unique record is expected. Returns false if
     * non-unique record is obtained from the query.<br />
     * fetch: Array of arguments for the PDOStatement::fetchAll method.
     * @return boolean
     * @throws \InvalidArgumentException
     */
    public function read($tablename, array $options = array ()) {
        
        $this->touchOptions($options);
        
        // Must have conditions
        if ( empty ($options['conditions']) )
            throw new \InvalidArgumentException('Required option "conditions" not specified.');
        
        // Build query
        $query = $this->reset()
                ->getQuery('select', $tablename)
                ->addComplexCondition($options['conditions']);
        
        // Specify columns, if specified
        if ( isset ($options['columns']) ):
            $query->addColumns($options['columns']);
        endif;
        
        // Specify offset and limit
        if ( isset ($options['offset']) )
        	$query->offset($options['offset']);
        if ( isset ($options['limit']) )
            $query->limit($options['limit']);
        
        // Add ordering
        if ( isset ($options['ordering']) ):
            foreach ( $options['ordering'] as $column => $order ):
                $query->orderBy($column, $order);
            endforeach;
        endif;
        
        // Add grouping
        if ( isset ($options['grouping']) ):
            natty_debug('Grouping code pending.');
        endif;
        
        if ( isset ($options['debug']) )
            $query->preview();
        
        // Prepare statement
        $this->statement = $this->prepare($query);
        
        // Return results
        if ( $options['parameters'] )
            $this->statement->execute($options['parameters']);
        else
            $this->statement->execute();
        
        // See if a unique record was expected
        if ( isset ($options['unique']) && 1 !== $this->statement->rowCount() )
            return FALSE;
        
        // Fetch records
        if ( !isset ($options['fetch']) )
            $output = $this->statement->fetchAll();
        else {
            $output = call_user_func_array (array ($this->statement, 'fetchAll'), $options['fetch']);
        }
        
        return isset ($options['unique'])
            ? $output[0] : $output;
        
    }
    
}