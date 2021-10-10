<?php
/**
 * Data access object to table User.
 */
class UserDAO extends MySQLBaseDAO
{

    /**
     * Create one entity.
     * @CreateSQL("INSERT INTO User (")
     * @CreateSQL(" id, createUserId, createTime, updateUserId, updateTime, ")
     * @CreateSQL(" name, password, remark ")
     * @CreateSQL(") VALUES (")
     * @CreateSQL(" :id, :createUserId, :createTime, :updateUserId, :updateTime, ")
     * @CreateSQL(" :name, :password, :remark ")
     * @CreateSQL(")")
     */
    public function create($insertAry)
    {
        return parent::create($insertAry);
    }

    /**
     * Delete one entity.
     * @DeleteSQL("DELETE FROM User WHERE id = :id ")
     */
    public function deleteById($id)
    {
        parent::deleteById($id);
    }

    /**
     * Update one entity.
     * @UpdateSQL("UPDATE User ")
     * @UpdateSQL("SET remark = :remark, ")
     * @UpdateSQL("    updateUserId = :updateUserId, updateTime = :updateTime ")
     * @UpdateSQL("WHERE id = :id ")
     */
    public function updateById($id, $updateAry)
    {
        $binds = array();
        array_push($binds, array(":id", $id, PDO::PARAM_STR));
        array_push($binds, array(":remark", $updateAry["remark"], PDO::PARAM_STR));
        $this->conn->update($this, __FUNCTION__, $binds);
    }

    /**
     * Update password.
     * @UpdateSQL("UPDATE User ")
     * @UpdateSQL("SET password = :password, ")
     * @UpdateSQL("    updateUserId = :updateUserId, updateTime = :updateTime ")
     * @UpdateSQL("WHERE id = :id ")
     */
    public function updatePasswordById($id, $hashedPassword)
    {
        $binds = array();
        array_push($binds, array(":id", $id, PDO::PARAM_STR));
        array_push($binds, array(":password", $hashedPassword, PDO::PARAM_STR));
        $this->conn->update($this, __FUNCTION__, $binds);
    }

    /**
     * Get one entity by ID.
     * @QuerySQL("SELECT id, name, remark, createTime, updateTime FROM User ")
     * @QuerySQL("WHERE id = :id ")
     */
    public function selectById($id)
    {
        return parent::selectById($id);
    }

    /**
     * Query entities like name.
     * @QuerySQL("SELECT id, name, remark, createTime, updateTime FROM User ")
     */
    public function pageQuery($pageParams)
    {
        $binds = array();
        $clauses = array();
        if (array_key_exists("keyword", $pageParams)) {
            $keyword = $pageParams["keyword"];
            array_push($binds, array(":name", "%" . $keyword . "%", PDO::PARAM_STR));
            array_push($binds, array(":remark", "%" . $keyword . "%", PDO::PARAM_STR));
            array_push($clauses, "(name like :name or remark like :remark)");
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

    /**
     * Get user's id & password by name for login.
     * @QuerySQL("SELECT id, password FROM User ")
     * @QuerySQL("WHERE name = :name ")
     */
    public function getPasswordByName($name)
    {
        $binds = array();
        array_push($binds, array(":name", $name, PDO::PARAM_STR));
        return $this->conn->selectOne($this, __FUNCTION__, $binds);
    }

    /**
     * Get user's id & password by ID for change password.
     * @QuerySQL("SELECT id, password FROM User ")
     * @QuerySQL("WHERE id = :id ")
     */
    public function getPasswordById($id)
    {
        $binds = array();
        array_push($binds, array(":id", $id, PDO::PARAM_STR));
        return $this->conn->selectOne($this, __FUNCTION__, $binds);
    }
}
