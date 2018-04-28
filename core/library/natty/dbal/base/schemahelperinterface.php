<?php

namespace Natty\DBAL\Base;

interface SchemaHelperInterface {

    /**
     * Returns a list of all tables which exist on the associated database.
     * @var bool $in_detail Whether to fetch additional information
     * @return array|false
     */
    public function readDatabase($in_detail = false);

    /**
     * Creates a table in the database. Tablename prefix will NOT
     * be added automatically.
     * @param array $definition Table definition
     * @throws \PDOException
     */
    public function createTable(array $definition);

    /**
     * Returns an array describing the table schema. Array would be in
     * a similar format as in the package schema declaration file.
     * @var string $tablename Name of the table to read
     * @var bool $cache Whether to reutrn output from cache instead of doing
     * an actual reading
     * @return array|false
     */
    public function readTable($tablename, $cache = true);

    /**
     * Renames a table in the database
     * @param string $tablename Existing name
     * @param string $newname New name
     * @throws \PDOException
     */
    public function renameTable($tablename, $newname);

    /**
     * Clears table content.
     * @param string $tablename
     */
    public function truncateTable($tablename);

    /**
     * Drops/deletes the specified table.
     * @var string $tablename Name of the table to drop
     * @throws \PDOException
     */
    public function dropTable($tablename);

    public function createColumn($tablename, array $definition);

    /**
     * @return array|false
     */
    public function readColumn($tablename, $columnname);

    /**
     * Renames a column in the database
     * @param string $tablename Existing name
     * @param string $columnname Name of the column
     * @param array $definition New definition
     * @throws \PDOException
     */
    public function alterColumn($tablename, $columnname, array $definition);

    public function dropColumn($tablename, $columnname);

    public function createIndex($tablename, array $definition);

    public function readIndex($tablename, $indexname);

    public function dropIndex($tablename, $indexname);
}
