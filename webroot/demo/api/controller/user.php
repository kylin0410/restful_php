<?php
require_once "dao/user_dao.php";

/**
 * @Controller
 * @SwaggerTag
- name: "User"
  description: "User management API."
 * @SwaggerTag
 * @SwaggerDefinition
  User:
    allOf:
      - $ref: "#/definitions/BaseEntity"
      - type: "object"
        properties:
          name:
            type: "string"
          remark:
            type: "string"
 * @SwaggerDefinition
 */
class UserController extends BaseCRUDController
{
    private $dictDao;
    public function __construct()
    {
        $this->dbConn = new MySQLConn($this);
        $this->dao = new UserDAO($this->dbConn);
    }

    public function __destruct()
    {
        $this->dao = null;
        $this->dbConn = null;
    }

    /**
     * @Route("/api/users")
     * @Method("POST")
     * @Auth
     * @SwaggerPath
  /users:
    post:
      tags:
      - "User"
      summary: "Create user."
      operationId: "createUser"
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
            name:
              type: "string"
              required: true
              example: "Joseph"
            password:
              type: "string"
              required: true
              format: "password"
              example: "1234"
            remark:
              type: "string"
              example: "0987987987"
      responses:
        "200":
          description: "Successful operation."
          schema:
            allOf:
              - type: "object"
                properties:
                  data:
                    $ref: "#/definitions/User"
              - $ref: "#/definitions/NormalResponseModel"
        "500":
          $ref: "#/responses/ServerError"
     * @SwaggerPath
     */
    public function post()
    {
        // Validation.
        $jsonDict = $this->getJsonDictFromBody();
        $this->checkLostKeys($jsonDict, array("remark"));
        $validAry = $this->checkRequiredKeys($jsonDict, array("name", "password"));
        if (!empty($validAry)) {
            throw new ValidationError($validAry);
        }

        // Create user with salted password.
        $saltedPassword = password_hash($jsonDict["password"], PASSWORD_DEFAULT);
        $jsonDict["password"] = $saltedPassword;
        parent::create($jsonDict);
    }

    /**
     * @Route("/api/users(\?.+)?")
     * @Method("GET")
     * @Auth
     * @SwaggerPath
    get:
      tags:
      - "User"
      summary: "Page qurey to user."
      operationId: "queryUser"
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
                    $ref: "#/definitions/User"
            - $ref: "#/definitions/PageResponseModel"
     * @SwaggerPath
     */
    public function getPage($paramStr="")
    {
        parent::readPage($paramStr);
    }

    /**
     * @Route("/api/users/changePassword")
     * @Method("PUT", "PATCH")
     * @Auth
     * @SwaggerPath
  /users/changePassword:
    put:
      tags:
      - "User"
      summary: "Change user's password."
      operationId: "changePassword"
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
            uid:
              type: "string"
              format: "uuid"
            oldPassword:
              type: "string"
              format: "password"
            newPassword:
              type: "string"
              format: "password"
      responses:
        "200":
          $ref: "#/responses/NormalResponse"
        "500":
          $ref: "#/responses/ServerError"
     * @SwaggerPath
     */
    public function changePassword()
    {
        // Validation.
        $jsonDict = $this->getJsonDictFromBody();
        $validAry = $this->checkRequiredKeys($jsonDict, array("uid", "oldPassword", "newPassword"));
        if (!empty($validAry)) {
            throw new ValidationError($validAry);
        }

        // Verify uid and password.
        $user = $this->dao->getPasswordById($jsonDict["uid"]);
        if (empty($user)) {
            throw new BusinessLogicError("User is absent.");
        }
        if (!password_verify($jsonDict["oldPassword"], $user["password"])) {
            throw new BusinessLogicError("Password is wrong.");
        }

        // Change password.
        $hashedPassword = password_hash($jsonDict["newPassword"], PASSWORD_DEFAULT);
        $this->dao->updatePasswordById($jsonDict["uid"], $hashedPassword);
        $this->respondJson();
    }

    /**
     * @Route("/api/users/resetPassword")
     * @Method("PUT", "PATCH")
     * @Auth
     * @SwaggerPath
  /users/resetPassword:
    put:
      tags:
      - "User"
      summary: "Change user's password for administrator."
      operationId: "resetPassword"
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
            uid:
              type: "string"
              format: "uuid"
            newPassword:
              type: "string"
              format: "password"
      responses:
        "200":
          $ref: "#/responses/NormalResponse"
        "500":
          $ref: "#/responses/ServerError"
     * @SwaggerPath
     */
    public function resetPassword()
    {
        // Validation.
        $jsonDict = $this->getJsonDictFromBody();
        $validAry = $this->checkRequiredKeys($jsonDict, array("uid", "newPassword"));
        if (!empty($validAry)) {
            throw new ValidationError($validAry);
        }

        // Verify uid and password.
        $user = $this->dao->getPasswordById($jsonDict["uid"]);
        if (empty($user)) {
            throw new BusinessLogicError("User is absent.");
        }

        // Change password.
        $hashedPassword = password_hash($jsonDict["newPassword"], PASSWORD_DEFAULT);
        $this->dao->updatePasswordById($jsonDict["uid"], $hashedPassword);
        $this->respondJson();
    }

    /**
     * @Route("/api/users/([0-9a-f]{8}-(?:[0-9a-f]{4}-){3}[0-9a-f]{12})")
     * @Method("PUT", "PATCH")
     * @Auth
     * @SwaggerPath
  /users/{id}:
    put:
      tags:
      - "User"
      summary: "Update user info by ID."
      operationId: "updateUserById"
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
            remark:
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
                    $ref: "#/definitions/User"
              - $ref: "#/definitions/NormalResponseModel"
        "500":
          $ref: "#/responses/ServerError"
     * @SwaggerPath
     */
    public function put($id)
    {
        // Validation.
        $jsonDict = $this->getJsonDictFromBody();
        $validAry = $this->checkRequiredKeys($jsonDict, array("remark"));
        if (!empty($validAry)) {
            throw new ValidationError($validAry);
        }

        // Raise Entity not found.
        $user = $this->dao->selectById($id);
        parent::update($id, $jsonDict);
    }

    /**
     * @Route("/api/users/([0-9a-f]{8}-(?:[0-9a-f]{4}-){3}[0-9a-f]{12})")
     * @Method("DELETE")
     * @Auth
     * @SwaggerPath
    delete:
      tags:
      - "User"
      summary: "Delete user by ID."
      operationId: "deleteUserById"
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
        $user = $this->dao->selectById($id);
        if ($user["name"] === "admin") {
            throw new BusinessLogicError("Can't delete default administrator.");
        }
        parent::delete($id);
    }

    /**
     * @Route("/api/users/([0-9a-f]{8}-(?:[0-9a-f]{4}-){3}[0-9a-f]{12})")
     * @Method("GET")
     * @Auth
     * @SwaggerPath
    get:
      tags:
      - "User"
      summary: "Get user info by ID."
      operationId: "findUserById"
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
                    $ref: "#/definitions/User"
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
