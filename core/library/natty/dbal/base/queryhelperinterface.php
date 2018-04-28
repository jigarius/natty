<?php

namespace Natty\DBAL\Base;

interface QueryHelperInterface {
    
    /**
     * A character(s) to use to indicate the start of a system entity
     * @var string
     */
    const ENTITY_QUOTE = '';

    /**
     * A character(s) to use to indicate the end of a system entity
     * @var string
     */
    const ENTITY_UNQUOTE = '';

    /**
     * A set of characters to define an Alias to a field or table
     * @var string
     */
    const ALIAS_INDICATOR = ' AS ';

    /**
     * Quotes an operand / field mentioned in a query depending on its type,
     * nature and other factors. Override this method for a DriverHelper
     * to support quoting of parameters like true becomes TRUE, etc.
     * @param mixed $operand The query operand to quote - Either a parameter
     * placeholder or a tablename alias specification
     */
    public function quoteOperand($operand);
    
    /**
     * Renders conditions from an array of conditions as collected by
     * Query specifications. In short, an array of arrays of the format:
     * array ($conditions, $optional)
     * @see Query::condition for more array format
     * @param array $condition
     * @return string Condition string
     */
    public function renderConditions($condition);
    
    /**
     * Renders a DELETE type query
     * @param array State of a Query object as an associative array
     * @return string Compiled query
     */
    public function renderDeleteQuery($state);

    /**
     * Renders a SELECT type query
     * @param array State of a Query object as an associative array
     * @return string Compiled query
     */
    public function renderSelectQuery($state);

    /**
     * Renders an INSERT type query
     * @param array State of a Query object as an associative array
     * @return string Compiled query
     */
    public function renderInsertQuery($state);

    /**
     * Renders an UPDATE type query
     * @param array State of a Query object as an associative array
     * @return string Compiled query
     */
    public function renderUpdateQuery($state);
    
}