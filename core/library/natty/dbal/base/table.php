<?php

namespace Natty\DBAL\Base;

class Table
extends \Natty\StdClass {
    
    /**
     * And array of columns in the table along with their attributes
     * @var array
     */
    protected $columns = array ();
    
    /**
     * Name of the table
     * @var string
     */
    protected $name;
    
    /**
     * An alias for the table to be used in queries
     * @var string
     */
    protected $alias;
    
    /**
     * The database object to which the table belongs
     * @var \Natty\DBAL\Connection
     */
    protected $connection;
    
    /**
     * Table indexes: primary, unique and nonunique
     * @var string
     */
    protected $indexes;
    
    /**
     * A reference to the last Statement handle used
     * @var \Natty\DBAL\Statement
     */
    protected $statement;
    
    /**
     * Creates a Database Table object with the the specified information
     * @param string $tablename Name of the table (with prefix, if any)
     * @param \Natty\DBAL\Connection $connection The database to which the table 
     * belongs
     */
    public function __construct( $tablename, Connection $connection ) {
        
        if ( empty ($tablename) )
            throw new \InvalidArgumentException('Argument 1 expected to be a non-empty string!');
        
        $this->connection = $connection;
        
        // Detect or derive table alias
        $parts = explode(' ', $tablename);
        if ( 1 == sizeof($parts) )
            $parts[] = str_replace(array ('%__', '_'), array ('', ''), $parts[0]);
        
        list ($this->name, $this->alias) = $parts;
        
        if ( !$schema = $this->connection->getSchemaHelper()->readTable($this->name) )
            trigger_error('Definition not found for "' . $tablename . '"', E_USER_ERROR);
        
        $this->setState($schema);
        
    }
    
    /**
     * Deletes records in the table based on primary key specified in the data
     * @param array $data Primary key data to delete by
     * @return bool True or false
     */
    public function deleteByPrimaryKey( array $data ) {
        
        $options = array (
            'conditions' => array (),
            'parameters' => array ()
        );
        
        // See if all necessary keys were specified
        foreach ( $this->getPrimaryIndex() as $column ):
            if ( !isset ($data[$column]) )
                throw new \InvalidArgumentException('Attempt to delete record with improper key data!');
            $options['conditions'][] = array ('AND', array ($column, '=', ':' . $column));
            $options['parameters'][$column] = $data[$column];
        endforeach;
        
        return $this->delete($options);
        
    }
    
    /**
     * Returns a delete statement handle to delete rows from the table which 
     * match the given condition
     * @param string $options Options for deletion
     * @return bool True on success or false on failure
     */
    public function delete($options) {
        
        if ( !isset ($options['conditions']) || empty ($options['conditions']) )
            throw new \InvalidArgumentException('Required option "conditions" not specified');
        if ( !isset ($options['parameters']) )
            $options['parameters'] = array ();
        
        // Build query
        $this->statement = $this->reset()
                ->getQuery('delete')
                ->addComplexCondition($options['conditions'])
                ->prepare();
        
        return $this->statement->execute($options['parameters']);
        
    }

    /**
     * @see \Natty\DBAL\Connection
     */
    public function errorCode() {
        return $this->statement
                ? $this->statement->errorCode() : $this->connection->errorCode();
    }
    
    /**
     * @see \Natty\DBAL\Connection
     */
    public function errorInfo() {
        return $this->statement
                ? $this->statement->errorInfo()
                : $this->connection->errorInfo();
    }
    
    /**
     * Finds and returns records which have matching $keys
     * @param array $data Primary key data to match against
     * @param array $options [optional] Additional options
     * @return array
     */
    public function readByPrimaryKey( array $data, array $options = array () ) {
        
        if ( !isset ($options['conditions']) )
            $options['conditions'] = array ();
        if ( !isset ($options['parameters']) )
            $options['parameters'] = array ();
        
        // See if all necessary keys were specified
        foreach ( $this->getPrimaryIndex() as $column ):
            if ( !isset ($data[$column]) )
                throw new \InvalidArgumentException('Attempt to delete record with improper key data!');
            $options['conditions'][] = array ('AND', array ($this->getAlias() . '.' . $column, '=', ':' . $column));
            $options['parameters'][$column] = $data[$column];
        endforeach;
        
        $results = $this->read($options);
        return sizeof($results) ? $results[0] : false;
        
    }
    
    /**
     * Returns records from the table matching a certain criteria
     * @param array $options An associative array of reading options like:<br />
     * conditions: An array of conditions<br />
     * parameters: Parameters to be bound to the conditions<br />
     * @return \PDOStatement|false
     */
    public function read( array $options = array () ) {
        
        if ( !isset ($options['conditions']) )
            $options['conditions'] = array ();
        if ( !isset ($options['parameters']) )
            $options['parameters'] = array ();
        
        // Determine table alias
        $options['alias'] = $this->getAlias();
        
        // Build query
        $query = $this->reset()
                ->getQuery('select')
                ->addComplexCondition($options['conditions']);
        
        // If columns were specified
        $options['columns'] = isset ($options['columns'])
                ? $options['columns'] 
                : $this->getColumnNames();
        $query->addColumns($options['columns'], $options['alias']);
        
        // Prepare statement
        $this->statement = $query->prepare();
        $options['parameters'] 
                ? $this->statement->execute($options['parameters']) 
                : $this->statement->execute();
        
        // Determine fetching options
        $fetch = isset ($options['fetch'])
                ? $options['fetch'] 
                : \PDO::FETCH_ASSOC;
        if ( !is_array($fetch) )
            $fetch = array ($fetch);
        
        return call_user_func_array(array ($this->statement, 'fetchAll'), $fetch);
        
    }
    
    public function getAlias() {
        return $this->alias;
    }
    
    public function getPrimaryIndex() {
        if ( !isset ($this->indexes['primary']) )
            throw new \BadMethodCallException('Table "' . $this->name . '" does not have a primary index!');
        return $this->indexes['primary']['columns'];
    }
    
    /**
     * Returns an array of fieldnames in the table
     * @return array
     */
    public function getColumnNames() {
        return array_keys($this->columns);
    }
    
    /**
     * @param string $type Type of the query, e.g. select, insert, etc.
     * @return \Natty\DBAL\QueryBuilder Query object
     */
    public function getQuery( $type ) {
        // We can have aliases only in select queries
        $tablename = $this->name;
        if ( 'select' == $type )
            $tablename .= ' ' . $this->getAlias();
        return $this->connection->getQuery($type, $tablename);
    }
    
    /**
     * @see \Natty\DBAL\Connection
     */
    public function lastInsertId( $name = null ) {
        return $this->connection->lastInsertId( $name = null );
    }
    
    /**
     * Creates an Insert PDOStatement Handle for the table
     * @param array $data An array of columns to be inserted
     * @return int|bool Insert ID / True or false
     */
    public function insert( array $data ) {
        
        $data = array_intersect_key($data, $this->columns);
        
        // Prepare the query
        $this->statement = $this->reset()
                ->getConnection()
                ->getQuery('insert', $this->name)
                ->addColumns(array_keys($data))
                ->prepare();
        
        // Return false on failure
        if ( !$this->statement->execute($data) )
            return false;
        
        // Return last insert id
        if ( $lid = $this->connection->lastInsertId() )
            return $lid;
        
        // Return varchar insert id
        $keys = $this->getPrimaryIndex();
        if ( 1 == sizeof($keys) ):
            $pk = $keys[0];
            if ( isset ($data[$pk]) && $data[$pk] )
                return $data[$pk];
        endif;
        
        // If no insert id was found, return a simple "true"
        return TRUE;
        
    }
    
    /**
     * Inserts multiple records from an array of associative arrays of data;
     * All data records must have the same keys because these would be inserted
     * using the same insert query prepared statement
     * @param array $records An array of arrays of records
     */
    public function insertMultiple( array $records ) {
        
        $results = array ();
        $fieldnames = false;
        
        foreach ( $records as $key => $this_record ):
            
            // Validate the record data array
            if ( !is_array($this_record) )
                throw new \InvalidArgumentException('Record data at index "' . $key . '" is not an array!');
            
            // Extract fieldnames from the first record
            if ( false === $fieldnames ) {
                $fieldnames = array_keys($this_record);
            }
            // Other records must have the same keys
            else {
                if ( array_keys($this_record) != $fieldnames )
                    throw new \InvalidArgumentException('Record data key mismatch at index "' . $key . '"!');
            }
            
        endforeach;
        
        // Prepare insertion query
        $query = $this->getQuery('insert')
                ->addColumns($fieldnames);
        $this->statement = $this->connection->prepare($query);
        
        // Execute the insertion
        foreach ( $records as $key => $this_record ):
            $results[$key] = $this->statement->execute($this_record);
            // Replace with Insert ID if available
            if ( $lid = $this->lastInsertId() )
                $results[$key] = $lid;
        endforeach;
        
        return $results;
        
    }
    
    /**
     * Removes traces of previous transactions / database interactions
     * conducted with this Table instance
     */
    public function reset() {
        unset ($this->statement);
        return $this;
    }
    
    /**
     * @see \Natty\DBAL\Statement
     */
    public function rowCount() {
        return $this->statement->rowCount();
    }
    
    public function getConnection() {
        return $this->connection;
    }
    
    public function setConnection( $connection ) {
        if ( !is_a($connection, '\\Natty\\DBAL\\Base\\Connection') )
            throw new \InvalidArgumentException('Required option "connection" must be an instance of Natty.DBAL.Base.Connection!');
        $this->connection = $connection;
    }
    
    /**
     * Checks whether a record has a well-defined value for primary / 
     * composite key
     * @param array $record The record to test
     * @return boolean True if yes or false if no
     */
    public function isIdentifiable( array $record ) {
        
        // See if all keys were specified
        $keys = $this->getPrimaryIndex();
        foreach ( $keys as $t_key ):
            if ( !isset ($record[$t_key]) || '' == $record[$t_key] )
                return false;
        endforeach;
        
        return true;
        
    }
    
    /**
     * Updates records in the table based on primary key specified in the data
     * @param array $record The record to be updated
     * Defaults to primary key specified in schema.
     * @return bool True or false
     */
    public function updateByPrimaryKey( array $record ) {
        
        $options = array (
            'conditions' => array (),
            'parameters' => array ()
        );
        
        // See if all necessary keys were specified
        foreach ( $this->getPrimaryIndex() as $column ):
            if ( !isset ($record[$column]) )
                throw new \InvalidArgumentException('Attempt to update record with improper key data!');
            $options['conditions'][] = array ('AND', array ($column, '=', ':' . $column));
            $key_data[$column] = $record[$column];
        endforeach;
        
        return $this->update($record, $options);
        
    }
    
    /**
     * Updates records the table matching the given conditions
     * @return bool True on success or false on failure
     */
    public function update($record, array $options) {
        
        if ( !isset ($options['parameters']) )
            $options['parameters'] = array ();
        if ( !isset ($options['conditions']) )
            $options['conditions'] = array ();
        
        $record = array_intersect_key($record, $this->columns);
        $options['parameters'] = array_merge($record, $options['parameters']);
        
        $this->statement = $this->reset()
                ->getQuery('update')
                ->addColumns(array_keys($record))
                ->addComplexCondition('AND', $options['conditions'])
                ->prepare();
        
        return $this->statement->execute($options['parameters']);
        
    }
    
    /**
     * Based on the specified keys, creates a new record or updates an existing
     * record in the database
     * @param array $record An array of records to be upserted
     * @param array $keys [optional] If upsertion is to be based on columns
     * other than the primary key, $keys would contain a list of keys which
     * determine a unique record
     * @return bool true on success or false on failure
     */
    public function upsert( array $record, array $keys = array () ) {
        
        // Determine keys
        $keys = $keys ? : $this->getPrimaryIndex();
        if ( empty ($keys) )
            throw new \BadMethodCallException('Could not determine unqiue keys for upsertion');
        
        // Try and fetch the existing record with these keys
        $identifier = array ();
        foreach ( $keys as $column ):
            if ( !isset ($record[$column]) )
                throw new \InvalidArgumentException('Attempt to upsert record with improper key data!');
            $identifier[$column] = $record[$column];
        endforeach;
        $existing_record = $this->readByPrimaryKey($identifier);
        
        // If record does not exist, insert it
        if ( !$existing_record ) {
            $status = $this->insert($record);
        }
        // Otherwise just update the record
        else {
            $status = $this->updateByPrimaryKey($record);
        }
        
        return $status;
        
    }
    
    /**
     * Updates the columns specified by $keys with values provided in $values;
     * $keys must be a part of the composite key and the $values must be for
     * one part of the composite key which is not present in $keys. Taking the
     * values of $keys, the table would be updated such that there are $values
     * number of records in the table against the values of $keys. Useful for 
     * updating relationship maps.
     * @param array $keys Associative array of fieldnames with their static
     * values; i.e. The unchanged keys.
     * @param array $values An array of values for the changed keys.
     * @throws \InvalidArgumentException If arguments are not in order
     */
    public function map( array $keys, array $values ) {
        
        natty_debug();
        
        // Static keys must be specified
        if ( 0 == sizeof($keys) )
            throw new \InvalidArgumentException('Expects argument 1 to have at least 1 value.');
        
        // Ignore keys of the $values array
        $values = array_values($values);
        
        // Verify static keys and build static key condition
        $conditions = array ();
        foreach ( $keys as $fieldname => $value ):
            if ( !in_array($fieldname, $this->primaryKey) )
                throw new \InvalidArgumentException('The key ' . $fieldname . ' does not exist in table "' . $this->name . '"');
            $conditions[] = array ('AND', array ($fieldname, '=', ':' . $fieldname));
        endforeach;
        
        /*
         * Determine the field to update - Which would be the only component
         * of the composite key for which a value is not specified in $keys;
         */
        $f2u = array_diff($this->primaryKey, array_keys($keys));
        
        // There can be only one field to update (f2u)
        if ( 1 != sizeof($f2u) )
            throw new \InvalidArgumentException('Could not determine the key to update!');
        $f2u = array_pop($f2u);
        
        // Delete all assignments which are not present in $values
        $del_conditions = $conditions;
        if ( $values ):
            $del_conditions[] = array ('AND', array ($f2u, 'NOT IN', ':' . $f2u));
            $del_params = $keys;
            $del_params[$f2u] = '';
            foreach ( $values as $key => $this_value ):
                if ( $key )
                    $del_params[$f2u] .= ',';
                $del_params[$f2u] .= $this->connection->quote($this_value);
            endforeach;
        endif;
        
        // Execute the deletion procedure
        $b = $this->deleteWhere($del_conditions, $del_params);
        
        /*
         * Retrieve a list of existing values for the f2u; Compare new values
         * to check against existing values and insert those values which do
         * not exist in the database.
         */
        $existing_values = $this->readWhere($conditions, $keys, array ('fetch' => \PDO::FETCH_COLUMN));
        $new_values = array_diff($values, $existing_values);
        
        // Prepare records 2 insert (r2i)
        $r2i = array ();
        foreach ( $values as $this_value ):
            $r2i[] = array_merge($keys, array ($f2u => $this_value));
        endforeach;
        
        $this->insertMultiple($r2i);
        
    }
    
    /**
     * Truncates the table
     */
    public function truncate() {
        
        $query = 'TRUNCATE TABLE {' . $this->name . '}';
        
        $this->statement = $this->reset()
                ->connection
                ->prepare($query);
        
        return $this->statement->execute();
        
    }
    
}