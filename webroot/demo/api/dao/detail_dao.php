<?php
/**
 * Data access object to table Master.
 */
class DetailDAO extends MySQLBaseDAO
{

    /**
     * Create one entity.
     * @CreateSQL("INSERT INTO Detail (")
     * @CreateSQL(" id, createUserId, createTime, updateUserId, updateTime, ")
     * @CreateSQL(" masterId, item ")
     * @CreateSQL(") VALUES (")
     * @CreateSQL(" :id, :createUserId, :createTime, :updateUserId, :updateTime, ")
     * @CreateSQL(" :masterId, :item ")
     * @CreateSQL(")")
     */
    public function create($insertAry)
    {
        return parent::create($insertAry);
    }

    /**
     * Delete one entity.
     * @DeleteSQL("DELETE FROM Detail WHERE id = :id ")
     */
    public function deleteById($id)
    {
        parent::deleteById($id);
    }

    /**
     * Update one entity.
     * @UpdateSQL("UPDATE Detail ")
     * @UpdateSQL("SET item = :item, ")
     * @UpdateSQL("    updateUserId = :updateUserId, updateTime = :updateTime ")
     * @UpdateSQL("WHERE id = :id ")
     */
    public function updateById($id, $updateAry)
    {
        $binds = array();
        array_push($binds, array(":id", $id, PDO::PARAM_STR));
        array_push($binds, array(":item", $updateAry["item"], PDO::PARAM_STR));
        $this->conn->update($this, __FUNCTION__, $binds);
    }

    /**
     * Get one entity by ID.
     * @QuerySQL("SELECT a.id, a.masterId, b.masterNo, a.item, a.createTime, a.updateTime ")
     * @QuerySQL("FROM Detail AS a left join Master AS b on a.masterId = b.id ")
     * @QuerySQL("WHERE a.id = :id ")
     */
    public function selectById($id)
    {
        return parent::selectById($id);
    }

    /**
     * Query entities like name.
     * @QuerySQL("SELECT a.id, a.masterId, b.masterNo, a.item, a.createTime, a.updateTime ")
     * @QuerySQL("FROM Detail AS a left join Master AS b on a.masterId = b.id ")
     */
    public function pageQuery($pageParams, $masterId)
    {
        $binds = array();
        $clauses = array();
        array_push($binds, array(":masterId", $masterId, PDO::PARAM_STR));
        array_push($clauses, "(masterId = :masterId)");
        if (array_key_exists("item", $pageParams)) {
            $item = $pageParams["item"];
            array_push($binds, array(":item", "%" . $item . "%", PDO::PARAM_STR));
            array_push($clauses, "(item like :item)");
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
