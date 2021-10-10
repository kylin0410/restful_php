<?php
/**
 * Base CRUD controller.
 */
abstract class BaseCRUDController extends BaseController
{
    /**
     * The one DbConn shares among DAO objects.
     * @var DbConn
     */
    protected $dbConn;

    /**
     * Default DAO object for CRUD operations. Can declare other DAO in upper class.
     */
    protected $dao;

    public function beginTransaction()
    {
        $this->dbConn->beginTransaction();
        Logging::debugSql("Begin transaction.");
    }

    public function commit()
    {
        $this->dbConn->commit();
        Logging::debugSql("Commit transaction.");
    }

    public function rollback()
    {
        $this->dbConn->rollback();
        Logging::debugSql("Rollback transaction.");
    }

    /**
     * Common create function.
     * @return object Created entity.
     */
    public function create($entityDict)
    {
        $id = $this->dao->create($entityDict);
        $entity = $this->dao->selectById($id);
        $this->respondJson($this->makeSingleRespModel($entity));
    }

    /**
     * Common page function.
     */
    public function readPage($paramStr)
    {
        $pageParams = $this->parsePageParams($paramStr);
        list($pageData, $pageNo, $pageSize, $totalCounts) = $this->dao->pageQuery($pageParams);
        $this->respondJson($this->makePageRespModel($pageData, $pageNo, $pageSize, $totalCounts));
    }

    /**
     * Common put function.
     */
    public function update($id, $entityDict)
    {
        $this->dao->updateById($id, $entityDict);
        $entity = $this->dao->selectById($id);
        $this->respondJson($this->makeSingleRespModel($entity));
    }

    /**
     * Common delete function.
     */
    protected function delete($id)
    {
        $this->dao->deleteById($id);
        $this->respondJson();
    }

    /**
     * Common get function.
     */
    protected function read($id)
    {
        $entity = $this->dao->selectById($id);
        $this->respondJson($this->makeSingleRespModel($entity));
    }
}
