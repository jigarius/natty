<?php

namespace Natty\DBAL\Base;

/**
 * Database Driver Query Helper - Helps extend driver-specific query 
 * building, quoting of system objects, etc. For any driver being used, a 
 * DriverHelper implementation would help render driver-specific Queries,
 * thereby providing a uniform Query Building solution throughout the
 * application's framework.
 * @author JigaR Mehta | Greenpill Productions
 */
abstract class QueryHelperAbstract
implements QueryHelperInterface {
    
    /**
     * Associated database connection
     * @var \Natty\DBAL\Base\Connection
     */
    protected $connection;
    
    final public function __construct( $connection ) {
        if ( !is_a($connection, 'Natty\\DBAL\\Base\\Connection') )
            throw new \InvalidArgumentException('Argument 1 expected to be a Database Connection Object!');
        $this->connection = $connection;
    }
    
    /**
     * Processes a query right before execution - conducting tasks like
     * replacing { and } with open and entity escape characters, database
     * specific table prefixes, etc.
     * @param Query|string $query The query to prepare
     * @return string Compiled query
     */
    public function render( $query ) {
        
        // Query builder object? Render it!
        if ( is_a($query, 'Natty\\DBAL\\QueryBuilder') ):
            $data = $query->getState();
            $type = strtolower($data['type']);
            $method = 'render' . ucfirst($type) . 'Query';
            $query = $this->$method( $data );
        endif;
        
        // Replace table quotes and prefix
        $query = preg_replace('/({)([%|_|a-z|0-9]+)(})/i', static::ENTITY_QUOTE . '$2' . static::ENTITY_UNQUOTE, $query);
        $query = str_replace('%__', $this->connection->getPrefix(), $query);
        
        return $query;
        
    }
    
    /**
     * Quotes a system object as per the database driver
     * @param string $entity Entity name in the format: table.column alias
     * return string Quoted entity name
     */
    public function quoteObject( $entity ) {
        
        // If it is a query placeholder, ignore it
        if ( '?' == $entity || 0 === strpos($entity, ':') )
            return $entity;
        
        // Verify entity format
        if (!preg_match('/^([\.]?(%__)?([a-z|0-9|_])+)+( ([a-z|0-9|_])+)?$/i', $entity))
            return $entity;
        
        $parts = explode(' ', $entity);
        $entity = $parts[0];
        $alias = ( isset ($parts[1]) )
            ? $parts[1] : false;
        
        // If all columns are to be selected
        $output = '{' . str_replace('.', '}.{', $entity) . '}';
        
        if ( $alias )
            $output .= static::ALIAS_INDICATOR . '{' . $alias . '}';
        
        return $output;
        
    }

    public function quoteOperand( $operand ) {
        
        // Special operands
        switch ( gettype($operand) ):
            case 'NULL':
                return 'NULL';
                break;
            case 'array':
                return "'" . implode("', '", $operand) . "'";
                break;
            case 'boolean':
                return $operand ? 'TRUE' : 'FALSE';
                break;
            case 'integer':
                return $operand;
                break;
        endswitch;
        
        // Ignore placeholders
        if ('?' == $operand || 0 === strpos($operand, ':'))
            return $operand;
        
        // If it looks like a table, quote it;
        return $this->quoteObject($operand);
        
    }
    
    public function renderConditions( $condition ) {

        // Empty conditions mean nothing
        if ( empty ($condition) )
            return '';
        
        // String conditions don't need rendering
        if ( is_string($condition) )
            return $condition;

        $output = '';

        // For unitary expressions; i.e. condition nodes
        if (is_array($condition) && !is_array($condition[0]) && 3 == sizeof($condition)):
            list ($operand1, $operator, $operand2) = $condition;
            // Join the expression depending on the operator
            switch ($operator):
                case 'IN':
                case 'NOT IN':
                    $output .= $this->quoteOperand($operand1) 
                        . ' ' . $operator 
                        . ' (' . $this->quoteOperand($operand2) . ')'
                    ;
                    break;
                case 'EXISTS':
                case 'NOT EXISTS':
                    $output .= $operator
                            . ' ' . $this->quoteOperand($operand2);
                    break;
                default:
                    $output .= $this->quoteOperand($operand1)
                            . ' ' . $operator
                            . ' ' . $this->quoteOperand($operand2);
                    break;
            endswitch;
            return $output;
        endif;

        // For multiple and nested conditions
        foreach ($condition as $key => $part):
            // Attach the logic operator - AND|OR
            if ($key > 0)
                $output .= ' ' . $part[0] . ' ';
            $output .= '(' . $this->renderConditions($part[1]) . ')';
        endforeach;

        return $output;
    }
    
}