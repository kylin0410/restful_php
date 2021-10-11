<?php
require_once "dao/master_dao.php";

/**
 * @Controller
 * @SwaggerTag
- name: "Master"
  description: "Master management API."
 * @SwaggerTag
 * @SwaggerDefinition
  Master:
    allOf:
      - $ref: "#/definitions/BaseEntity"
      - type: "object"
        properties:
          masterNo:
            type: "string"
          name:
            type: "string"
          description:
            type: "string"
 * @SwaggerDefinition
 */
class MasterController extends BaseCRUDController
{
    private $dictDao;
    public function __construct()
    {
        $this->dbConn = new MySQLConn($this);
        $this->dao = new MasterDAO($this->dbConn);
    }

    public function __destruct()
    {
        $this->dao = null;
        $this->dbConn = null;
    }

    /**
     * @Route("/api/masters")
     * @Method("POST")
     * @Auth
     * @SwaggerPath
  /masters:
    post:
      tags:
      - "Master"
      summary: "Create master."
      operationId: "createMaster"
      security:
      - Bearer: []
      produces:
      - "application/json"
      parameters:
      - in: "body"
        name: "body"
        required: true
        schema:
          type: "object"
          properties:
            masterNo:
              type: "string"
              required: true
              example: "M20210987001"
            name:
              type: "string"
              required: true
              example: "CatelogAAA"
            description:
              type: "string"
              example: "Master detail demo"
      responses:
        "200":
          description: "Successful operation."
          schema:
            allOf:
              - type: "object"
                properties:
                  data:
                    $ref: "#/definitions/Master"
              - $ref: "#/definitions/NormalResponseModel"
        "500":
          $ref: "#/responses/ServerError"
     * @SwaggerPath
     */
    public function post()
    {
        // Validation.
        $jsonDict = $this->getJsonDictFromBody();
        $this->checkLostKeys($jsonDict, array("description"));
        $validAry = $this->checkRequiredKeys($jsonDict, array("masterNo", "name"));
        if (!empty($validAry)) {
            throw new ValidationError($validAry);
        }
        parent::create($jsonDict);
    }

    /**
     * @Route("/api/masters(\?.+)?")
     * @Method("GET")
     * @Auth
     * @SwaggerPath
    get:
      tags:
      - "Master"
      summary: "Page qurey to master."
      operationId: "queryMaster"
      security:
      - Bearer: []
      produces:
      - "application/json"
      parameters:
      - $ref: "#/parameters/PageNo"
      - $ref: "#/parameters/PageSize"
      - $ref: "#/parameters/SortBy"
      - $ref: "#/parameters/Keyword"
      - $ref: "#/parameters/CreateTimeStart"
      - $ref: "#/parameters/CreateTimeEnd"
      - in: "query"
        name: "name"
        type: "string"
        description: "Complete match to name."
      responses:
        "200":
          description: "Successful operation."
          schema:
            allOf:
            - type: "object"
              properties:
                data:
                  type: "array"
                  items:
                    $ref: "#/definitions/Master"
            - $ref: "#/definitions/PageResponseModel"
     * @SwaggerPath
     */
    public function getPage($paramStr="")
    {
        parent::readPage($paramStr);
    }

    /**
     * @Route("/api/masters/([0-9a-f]{8}-(?:[0-9a-f]{4}-){3}[0-9a-f]{12})")
     * @Method("PUT", "PATCH")
     * @Auth
     * @SwaggerPath
  /masters/{id}:
    put:
      tags:
      - "Master"
      summary: "Update master info by ID."
      operationId: "updateMasterById"
      security:
      - Bearer: []
      produces:
      - "application/json"
      parameters:
      - $ref: "#/parameters/Id"
      - in: "body"
        name: "body"
        required: true
        schema:
          type: "object"
          properties:
            name:
              type: "string"
              required: true
            description:
              type: "string"
              required: true
      responses:
        "200":
          description: "Successful operation."
          schema:
            allOf:
              - type: "object"
                properties:
                  data:
                    $ref: "#/definitions/Master"
              - $ref: "#/definitions/NormalResponseModel"
        "500":
          $ref: "#/responses/ServerError"
     * @SwaggerPath
     */
    public function put($id)
    {
        // Validation.
        $jsonDict = $this->getJsonDictFromBody();
        $validAry = $this->checkRequiredKeys($jsonDict, array("name", "description"));
        if (!empty($validAry)) {
            throw new ValidationError($validAry);
        }
        parent::update($id, $jsonDict);
    }

    /**
     * @Route("/api/masters/([0-9a-f]{8}-(?:[0-9a-f]{4}-){3}[0-9a-f]{12})")
     * @Method("DELETE")
     * @Auth
     * @SwaggerPath
    delete:
      tags:
      - "Master"
      summary: "Delete master by ID."
      operationId: "deleteMasterById"
      security:
      - Bearer: []
      produces:
      - "application/json"
      parameters:
      - $ref: "#/parameters/Id"
      responses:
        "200":
          $ref: "#/responses/DeleteResponse"
        "500":
          $ref: "#/responses/ServerError"
     * @SwaggerPath
     */
    public function delete($id)
    {
        parent::delete($id);
    }

    /**
     * @Route("/api/masters/([0-9a-f]{8}-(?:[0-9a-f]{4}-){3}[0-9a-f]{12})")
     * @Method("GET")
     * @Auth
     * @SwaggerPath
    get:
      tags:
      - "Master"
      summary: "Get master info by ID."
      operationId: "findMasterById"
      security:
      - Bearer: []
      produces:
      - "application/json"
      parameters:
      - $ref: "#/parameters/Id"
      responses:
        "200":
          description: "Successful operation."
          schema:
            allOf:
              - type: "object"
                properties:
                  data:
                    $ref: "#/definitions/Master"
              - $ref: "#/definitions/NormalResponseModel"
        "404":
          $ref: "#/responses/NotFound"
     * @SwaggerPath
     */
    public function get($id)
    {
        parent::read($id);
    }
}
