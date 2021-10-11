<?php
require_once "dao/detail_dao.php";

/**
 * @Controller
 * @SwaggerTag
- name: "Detail"
  description: "Detail management API."
 * @SwaggerTag
 * @SwaggerDefinition
  Detail:
    allOf:
      - $ref: "#/definitions/BaseEntity"
      - type: "object"
        properties:
          masterId:
            type: "string"
            format: "uuid"
          masterNo:
            type: "string"
          item:
            type: "string"
 * @SwaggerDefinition
 */
class DetailController extends BaseCRUDController
{
    public function __construct()
    {
        $this->dbConn = new MySQLConn($this);
        $this->dao = new DetailDAO($this->dbConn);
    }

    public function __destruct()
    {
        $this->dao = null;
        $this->dbConn = null;
    }

    /**
     * @Route("/api/details/([0-9a-f]{8}-(?:[0-9a-f]{4}-){3}[0-9a-f]{12})")
     * @Method("PUT", "PATCH")
     * @Auth
     * @SwaggerPath
  /details/{id}:
    put:
      tags:
      - "Detail"
      summary: "Update detail info by ID."
      operationId: "updateDetailById"
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
            item:
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
                    $ref: "#/definitions/Detail"
              - $ref: "#/definitions/NormalResponseModel"
        "500":
          $ref: "#/responses/ServerError"
     * @SwaggerPath
     */
    public function put($id)
    {
        // Validation.
        $jsonDict = $this->getJsonDictFromBody();
        $validAry = $this->checkRequiredKeys($jsonDict, array("item"));
        if (!empty($validAry)) {
            throw new ValidationError($validAry);
        }
        parent::update($id, $jsonDict);
    }

    /**
     * @Route("/api/details/([0-9a-f]{8}-(?:[0-9a-f]{4}-){3}[0-9a-f]{12})")
     * @Method("DELETE")
     * @Auth
     * @SwaggerPath
    delete:
      tags:
      - "Detail"
      summary: "Delete detail by ID."
      operationId: "deleteDetailById"
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
     * @Route("/api/details/([0-9a-f]{8}-(?:[0-9a-f]{4}-){3}[0-9a-f]{12})")
     * @Method("GET")
     * @Auth
     * @SwaggerPath
    get:
      tags:
      - "Detail"
      summary: "Get detail info by ID."
      operationId: "getDetailById"
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
                    $ref: "#/definitions/Detail"
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
