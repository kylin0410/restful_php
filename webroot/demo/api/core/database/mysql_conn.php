<?php
/**
 * This is fundamental database class to wrap DB conntction.
 * It implements basic and non-functional operations to MySQL database:
 *   1. insert
 *   2. delete
 *   3. update
 *   4. select
 *      a. get one.
 *      b. get many.
 *      c. get page data.
 */
class MySQLConn extends DbConn
{
    private $options = array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4');
    private $pdo;
    private $ctrl;

    public function __construct(iCtrl $ctrl)
    {
        $dbConf = Config::getConfig(Config::$KEY_MYSQL);
        $dsn = "mysql:host=" . $dbConf["host"] . ";dbname=" . $dbConf["dbname"] . ";";
        $username = $dbConf["username"];
        $password = $dbConf["password"];
        $this->pdo = new PDO($dsn, $username, $password, $this->options);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->ctrl = $ctrl;
    }

    public function __destruct()
    {
        $this->pdo = null;
    }

    public function beginTransaction()
    {
        $this->pdo->beginTransaction();
    }

    public function commit()
    {
        $this->pdo->commit();
    }

    public function rollback()
    {
        $this->pdo->rollBack();
    }

    /**
     * Insert entity template.
     * @return string ID of created entity.
     */
    public function insert($clsObj, $method, $entityDict)
    {
        // Fill default value to id if id is not speficied at upper layer.
        if (empty($entityDict["id"])) {
            $id = $this->guidv4();
            $entityDict["id"] = $id;
        } else {
            $id = $entityDict["id"];
        }

        // Fill default value create/update x time/userId.
        $now = gmdate("Y-m-d H:i:s");
        $entityDict["createTime"] = $now;
        $entityDict["updateTime"] = $now;
        if (!isset($entityDict["createUserId"])) {
            $entityDict["createUserId"] = $this->ctrl->getLoginUserId();
        }
        if (!isset($entityDict["updateUserId"])) {
            $entityDict["updateUserId"] = $this->ctrl->getLoginUserId();
        }

        // Get SQL and insert data to DB.
        $createSQL = $this->getDocString($clsObj, $method, "CreateSQL");
        Logging::debugSql($createSQL);
        Logging::debugSql("entityDict:\n" . json_encode($entityDict, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        $prepare = $this->pdo->prepare($createSQL);
        $result = $prepare->execute($entityDict);
        if (!$result) {
            throw new CustomizedDatabaseError($prepare->errorInfo());
        }
        return $id;
    }

    /**
     * Delete entity template.
     */
    public function delete($clsObj, $method, $binds)
    {
        $deleteSQL = $this->getDocString($clsObj, $method, "DeleteSQL");
        Logging::debugSql($deleteSQL);
        $prepare = $this->pdo->prepare($deleteSQL);
        foreach ($binds as $bind) {
            $prepare->bindValue($bind[0], $bind[1], $bind[2]);
        }
        $result = $prepare->execute();
        if (!$result) {
            throw new CustomizedDatabaseError($prepare->errorInfo());
        }
    }

    /**
     * Update entity template.
     */
    public function update($clsObj, $method, $binds)
    {
        array_push($binds, array(":updateTime", gmdate("Y-m-d H:i:s"), PDO::PARAM_STR));
        array_push($binds, array(":updateUserId", $this->ctrl->getLoginUserId(), PDO::PARAM_STR));
        $updateSQL = $this->getDocString($clsObj, $method, "UpdateSQL");
        Logging::debugSql($updateSQL);
        $prepare = $this->pdo->prepare($updateSQL);
        foreach ($binds as $bind) {
            $prepare->bindValue($bind[0], $bind[1], $bind[2]);
        }
        $result = $prepare->execute();
        if (!$result) {
            throw new CustomizedDatabaseError($prepare->errorInfo());
        }
    }

    /**
     * Get one entity template.
     */
    public function selectOne($clsObj, $method, $binds)
    {
        $data = null;
        $querySQL = $this->getDocString($clsObj, $method, "QuerySQL");
        Logging::debugSql($querySQL);
        $prepare = $this->pdo->prepare($querySQL);
        foreach ($binds as $bind) {
            $prepare->bindValue($bind[0], $bind[1], $bind[2]);
        }
        $result = $prepare->execute();
        if (!$result) {
            throw new CustomizedDatabaseError($prepare->errorInfo());
        }

        $rows = $prepare->fetchAll(PDO::FETCH_ASSOC);
        if (!$rows) {
            throw new EntityNotFoundError("Entity is not existed.");
        }

        $data = $rows[0];
        return $data;
    }

    /**
     * Query list data template.
     * @Return Objects in list(array).
     */
    public function selectList($clsObj, $method, $binds, $criteria = "")
    {
        $data = null;
        $querySQL = $this->getDocString($clsObj, $method, "QuerySQL") . " " . $criteria;
        Logging::debugSql($querySQL);
        $prepare = $this->pdo->prepare($querySQL);
        foreach ($binds as $bind) {
            $prepare->bindValue($bind[0], $bind[1], $bind[2]);
        }
        $result = $prepare->execute();
        if (!$result) {
            throw new CustomizedDatabaseError($prepare->errorInfo());
        }

        $data = $prepare->fetchAll(PDO::FETCH_ASSOC);
        return $data;
    }

    /**
     * Query page data template.
     */
    public function selectPage($clsObj, $method, $binds, $pageParams, $criteria = "")
    {
        $baseSQL = $this->getDocString($clsObj, $method, "QuerySQL") . " " . $criteria;
        $conutSQL = "select count(*) from (\n" . $baseSQL . "\n) as tmp";

        // Get total counts.
        Logging::debugSql($conutSQL);
        $prepare = $this->pdo->prepare($conutSQL);
        foreach ($binds as $bind) {
            $prepare->bindValue($bind[0], $bind[1], $bind[2]);
        }
        $result = $prepare->execute();
        if (!$result) {
            throw new CustomizedDatabaseError($prepare->errorInfo());
        }
        $totalCounts = intval($prepare->fetchColumn());

        // Check parameters.
        $pageSize = $pageParams["pageSize"];
        $pageNo = $pageParams["pageNo"];
        $totalPages = ceil($totalCounts / $pageSize);
        if ($pageNo > $totalPages && $totalPages > 0) {
            throw new BusinessLogicError("Outbound: $pageNo > $totalPages.");
        }

        // Append sorting and paging parameters.
        $querySQL = "select * from (\n" . $baseSQL . "\n) as tmp";
        $sortBy = $pageParams["sortBy"];
        $matchCount = preg_match_all("/([^+-,]+)([+-]),?/", $sortBy, $matchesAll);
        $sorts = array();
        for ($i = 0; $i < $matchCount; $i++) {
            $direction = $matchesAll[2][$i] === "-" ? "DESC" : "ASC";
            $sort = $matchesAll[1][$i] . " " . $direction;
            array_push($sorts, $sort);
        }
        $sortClause = join(", ", $sorts);
        if (!empty($sortClause)) {
            $querySQL .= "\norder by " . $sortClause;
        }

        // Query page data.
        $offset = $pageSize * ($pageNo - 1);
        $querySQL .= "\nlimit $pageSize offset $offset ";
        Logging::debugSql($querySQL);
        $prepare = $this->pdo->prepare($querySQL);
        foreach ($binds as $bind) {
            $prepare->bindValue($bind[0], $bind[1], $bind[2]);
        }
        $result = $prepare->execute();
        if (!$result) {
            throw new CustomizedDatabaseError($prepare->errorInfo());
        }

        $pageData = $prepare->fetchAll(PDO::FETCH_ASSOC);
        return array($pageData, $pageNo, $pageSize, $totalCounts);
    }

    /**
     * Get first column value of first row.
     */
    public function selectValue($clsObj, $method, $binds)
    {
        $value = null;
        $querySQL = $this->getDocString($clsObj, $method, "QuerySQL");
        Logging::debugSql($querySQL);
        $prepare = $this->pdo->prepare($querySQL);
        foreach ($binds as $bind) {
            $prepare->bindValue($bind[0], $bind[1], $bind[2]);
        }
        $result = $prepare->execute();
        if (!$result) {
            throw new CustomizedDatabaseError($prepare->errorInfo());
        }
        $value = $prepare->fetchColumn();
        return $value;
    }

    /**
     * Execute SQL command.
     * @param executeSQL SQL gramma to be executed.
     * @param fetch fetch mode, null means no result.
     */
    public function execute($executeSQL, $fetch=null)
    {
        $data = null;
        Logging::debugSql($executeSQL);
        $prepare = $this->pdo->prepare($executeSQL);
        $result = $prepare->execute();
        if (!$result) {
            throw new CustomizedDatabaseError($prepare->errorInfo());
        }
        if ($fetch == null) {
            return;
        }
        $data = $prepare->fetchAll($fetch);
        return $data;
    }
}
