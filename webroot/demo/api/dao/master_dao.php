<?php
/**
 * Data access object to table Master.
 */
class MasterDAO extends MySQLBaseDAO
{

    /**
     * Create one entity.
     * @CreateSQL("INSERT INTO Master (")
     * @CreateSQL(" id, createUserId, createTime, updateUserId, updateTime, ")
     * @CreateSQL(" masterNo, name, description ")
     * @CreateSQL(") VALUES (")
     * @CreateSQL(" :id, :createUserId, :createTime, :updateUserId, :updateTime, ")
     * @CreateSQL(" :masterNo, :name, :description ")
     * @CreateSQL(")")
     */
    public function create($insertAry)
    {
        return parent::create($insertAry);
    }

    /**
     * Delete one entity.
     * @DeleteSQL("DELETE FROM Master WHERE id = :id ")
     */
    public function deleteById($id)
    {
        parent::deleteById($id);
    }

    /**
     * Update one entity.
     * @UpdateSQL("UPDATE Master ")
     * @UpdateSQL("SET name = :name, description = :description, ")
     * @UpdateSQL("    updateUserId = :updateUserId, updateTime = :updateTime ")
     * @UpdateSQL("WHERE id = :id ")
     */
    public function updateById($id, $updateAry)
    {
        $binds = array();
        array_push($binds, array(":id", $id, PDO::PARAM_STR));
        array_push($binds, array(":name", $updateAry["name"], PDO::PARAM_STR));
        array_push($binds, array(":description", $updateAry["description"], PDO::PARAM_STR));
        $this->conn->update($this, __FUNCTION__, $binds);
    }

    /**
     * Get one entity by ID.
     * @QuerySQL("SELECT id, name, description, createTime, updateTime FROM Master ")
     * @QuerySQL("WHERE id = :id ")
     */
    public function selectById($id)
    {
        return parent::selectById($id);
    }

    /**
     * Query entities like name.
     * @QuerySQL("SELECT id, name, description, createTime, updateTime FROM Master ")
     */
    public function pageQuery($pageParams)
    {
        $binds = array();
        $clauses = array();
        if (array_key_exists("name", $pageParams)) {
            $name = $pageParams["name"];
            array_push($binds, array(":name", $name, PDO::PARAM_STR));
            array_push($clauses, "(name = :name)");
        }
        if (array_key_exists("keyword", $pageParams)) {
            $keyword = $pageParams["keyword"];
            array_push($binds, array(":name", "%" . $keyword . "%", PDO::PARAM_STR));
            array_push($binds, array(":description", "%" . $keyword . "%", PDO::PARAM_STR));
            array_push($clauses, "(name like :name or description like :description)");
        }
        if (array_key_exists("createTimeStart", $pageParams)) {
            $createTimeStart = $pageParams["createTimeStart"];
            array_push($binds, array(":createTimeStart", $createTimeStart, PDO::PARAM_STR));
            array_push($clauses, "createTime >= :createTimeStart");
        }
        if (array_key_exists("createTimeEnd", $pageParams)) {
            $createTimeEnd = $pageParams["createTimeEnd"];
            array_push($binds, array(":createTimeEnd", $createTimeEnd, PDO::PARAM_STR));
            array_push($clauses, "createTime <= :createTimeEnd");
        }
        $criteria = "";
        if (!empty($clauses)) {
            $criteria = "WHERE " . join(" and ", $clauses);
        }
        return $this->conn->selectPage($this, __FUNCTION__, $binds, $pageParams, $criteria);
    }
}
