<?php


namespace bwt\helpers\merchantcircle\Database;

use bwt\App\Libs\Database\Database;

class PDODatabase extends Database
{
    protected $pdo;

    private function generateSql(array $rows, string $table, array $except = [])
    {
        $first = reset($rows);

        $columns = implode( ',',
            array_map( function( $value ) { return "$value"; } , array_keys($first) )
        );

        $values = implode( ',', array_map( function( $row ) {
                return '('.implode( ',',
                        array_map( function( $value ) { return $value === null ? 'null' : $this->pdo->quote($value); } , $row )
                    ).')';
            } , $rows )
        );
        $keys = array_diff(array_keys($first), $except);
        $updates = implode( ',',
            array_map( function( $value ) {
                if ($value )
                return "$value = VALUES($value)";
                } , $keys )
        );

        $sql = "INSERT INTO {$table}({$columns}) VALUES {$values} ON DUPLICATE KEY UPDATE {$updates}";

        return $sql;
    }

    public function generateSqlForIgnoreInsert(array $rows, string $table)
    {
        $first = reset($rows);

        $columns = implode( ',',
            array_map( function( $value ) { return "$value"; } , array_keys($first) )
        );

        $values = implode( ',', array_map( function( $row ) {
                return '('.implode( ',',
                        array_map( function( $value ) { return $value === null ? 'null' : $this->pdo->quote($value); } , $row )
                    ).')';
            } , $rows )
        );
        $sql = "INSERT IGNORE INTO {$table}({$columns}) VALUES {$values}";

        return $sql;
    }

    public function insertOrUpdate(array $rows, string $table, array $except = [])
    {
        $sql = $this->generateSql($rows, $table, $except);
        $this->pdo->query($sql);
    }

    public function insertIgnore(array $rows, string $table)
    {
        $sql = $this->generateSqlForIgnoreInsert($rows, $table);
        $this->pdo->query($sql);
    }

    public function setPdo()
    {
        $this->pdo = $this->getCapsule()->getConnection()->getPdo();
    }

    public function getPdo()
    {
        return $this->getCapsule()->getConnection()->getPdo();
    }

}