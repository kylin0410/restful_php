<?php
/**
 * MySQL base DAO (database access object).
 */
class MySQLBaseDAO
{
    /**
     * Database connection interface get form controller.
     */
    protected $conn;

    public function __construct(DbConn $conn)
    {
        $this->conn = $conn;
    }

    /**
     * Common create DB function.
     * @return string ID of created entity.
     */
    public function create($entityDict)
    {
        return $this->conn->insert($this, __FUNCTION__, $entityDict);
    }

    /**
     * Common delete DB function.
     */
    public function deleteById($id)
    {
        $binds = array();
        array_push($binds, array(":id", $id, PDO::PARAM_STR));
        $this->conn->delete($this, __FUNCTION__, $binds);
    }

    /**
     * Common get DB function.
     * @return object Return object of entity.
     */
    public function selectById($id)
    {
        $binds = array();
        array_push($binds, array(":id", $id, PDO::PARAM_STR));
        return $this->conn->selectOne($this, __FUNCTION__, $binds);
    }
}
