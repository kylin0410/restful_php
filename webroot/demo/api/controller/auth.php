<?php
require_once "dao/user_dao.php";
/**
 * @SwaggerTag
- name: "Auth"
  description: "Authentication and authorization controller."
 * @SwaggerTag
 * @Controller
 */
class AuthController extends BaseCRUDController
{
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
     * Check identification and permission.
     */
    public function authorize($uid)
    {
        $loginUser = array();
        // $loginUser["id"] = $user["id"];
        $loginUser["name"] = $uid;
        // $loginUser["email"] = $user["email"];
        return $loginUser;
    }

    /**
     * @SwaggerPath
  /auth/login:
    post:
      tags:
      - "Auth"
      summary: "User login to get JWT Bearer token."
      operationId: "authLoginOauth"
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
              example: "admin"
            password:
              type: "string"
              required: true
              format: "password"
              example: "admin"
      responses:
        "200":
          $ref: "#/responses/LoginResponse"
        "400":
          $ref: "#/responses/BadRequest"
        "500":
          $ref: "#/responses/ServerError"
     * @SwaggerPath
     * @Route("/api/auth/login")
     * @Method("POST")
     */
    public function login()
    {
        // Validation.
        $jsonDict = $this->getJsonDictFromBody();
        $validAry = $this->checkRequiredKeys($jsonDict, array("name", "password"));
        if (!empty($validAry)) {
            throw new ValidationError($validAry);
        }
      
        $name = $jsonDict["name"];
        $user = $this->dao->getPasswordByName($name);
        if (empty($user)) {
            throw new BusinessLogicError("Fail to login.");
        }
        if (!password_verify($jsonDict["password"], $user["password"])) {
            throw new BusinessLogicError("Password is wrong.");
        }

        // Pass verification, generate JWT Bearer token to client.
        $uid = $user["id"];
        $payload = JwtUtil::genPayload($uid);
        $token = JwtUtil::encodePayload($payload);
        $this->respondJson($this->makeAuthRespModel($uid, $token));
    }
}
