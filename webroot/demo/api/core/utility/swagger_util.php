<?php

/**
 * @SwaggerHead
swagger: "2.0"
info:
  description: "This is a simple online API document."
  version: "1.0.0"
  title: "Swagger API Document"
  termsOfService: "http://swagger.io/terms/"
  contact:
    email: "nobody@nowork.com.tw"
schemes:
- "http"
externalDocs:
  description: "Find out more about Swagger"
  url: "http://swagger.io"
securityDefinitions:
  Bearer:
    type: apiKey
    name: Authorization
    in: header
parameters:
  Id:
    in: "path"
    name: "id"
    required: true
    type: "string"
    format: "uuid"
    description: "Unique ID of entity."
  PageNo:
    in: "query"
    name: "pageNo"
    type: "integer"
    minimum: 1
    default: 1
    description: "Page number starts from 1."
  PageSize:
    in: "query"
    name: "pageSize"
    type: "integer"
    minimum: 1
    maximum: 200
    default: 20
    description: "How many records per page."
  SortBy:
    in: "query"
    name: "sortBy"
    type: "string"
    description: "Sorting parameters. Support multiple fields and direction. Ex: name+,role-"
  CreateTimeStart:
    in: "query"
    name: "createTimeStart"
    type: "string"
    description: "Search for createTime greater than or equal to createTimeStart."
  CreateTimeEnd:
    in: "query"
    name: "createTimeEnd"
    type: "string"
    description: "Search for createTime smaller than or equal to createTimeEnd."
  Keyword:
    in: "query"
    name: "keyword"
    type: "string"
    description: "Search for multiple fields."
responses:
  LoginResponse:
    description: "Login successfully."
    schema:
      allOf:
        - $ref: "#/definitions/AuthResponseModel"
        - $ref: "#/definitions/NormalResponseModel"
  NormalResponse:
    description: "Operate successfully."
    schema:
      $ref: "#/definitions/NormalResponseModel"
  JpegResponse:
    description: "Download JPEG file."
    content:
      image/jpeg:
        schema:
          type: "string"
          format: "binary"
  TextResponse:
    description: "Output text."
    content:
      text/plain:
        schema:
          type: "string"
  BadRequest:
    description: "Bad request"
    schema:
      $ref: "#/definitions/ErrorResponseModel"
  NotFound:
    description: "The specified resource was not found"
    schema:
      $ref: "#/definitions/ErrorResponseModel"
  Unauthorized:
    description: "Unauthorized"
    schema:
      $ref: "#/definitions/ErrorResponseModel"
  NotAcceptable:
    description: "Business logic failed."
    schema:
      $ref: "#/definitions/ErrorResponseModel"
  ServerError:
    description: "Operation failed."
    schema:
      $ref: "#/definitions/ErrorResponseModel"
 * @SwaggerHead
 * @SwaggerDefinition
  BaseEntity:
    type: "object"
    properties:
      id:
        type: "string"
        format: "uuid"
      createTime:
        type: "string"
        format: "date-time"
      updateTime:
        type: "string"
        format: "date-time"
  AuthResponseModel:
    type: "object"
    properties:
      data:
        type: "object"
        properties:
          uid:
            type: "string"
            format: "uuid"
            description: "User ID."
          token:
            type: "string"
            description: "JWT token."
  NormalResponseModel:
    type: "object"
    properties:
      message:
        type: "string"
      totalTime:
        type: "string"
  ListResponseModel:
    allOf:
      - $ref: "#/definitions/NormalResponseModel"
      - type: "object"
        properties:
          totalCounts:
            type: "integer"
  PageResponseModel:
    allOf:
      - $ref: "#/definitions/NormalResponseModel"
      - type: "object"
        properties:
          pageNo:
            type: "integer"
          pageSize:
            type: "integer"
          pageCounts:
            type: "integer"
          totalPages:
            type: "integer"
          totalCounts:
            type: "integer"
  ErrorResponseModel:
    type: "object"
    properties:
      error_code:
        type: "string"
      message:
        type: "string"
 * @SwaggerDefinition
 */
class SwaggerUtil
{
    // Head is in this class.
    private static $REGEX_SWAGGER_HEAD = "/\s+\*\s+@SwaggerHead\s([\S\s]*)\s+\*\s+@SwaggerHead/";
    // Tag is in controller class.
    private static $REGEX_SWAGGER_TAG = "/\s+\*\s+@SwaggerTag\s([\S\s]*)\s+\*\s+@SwaggerTag/";
    // Definition is in controller class.
    private static $REGEX_SWAGGER_DEFINITION = "/\s+\*\s+@SwaggerDefinition\s([\S\s]*)\s+\*\s+@SwaggerDefinition/";
    // Path is in controller's method.
    private static $REGEX_SWAGGER_PATH = "/\s+\*\s+@SwaggerPath\s([\S\s]*)\s+\*\s+@SwaggerPath/";

    /**
     * Search and match the request method and uri.
     */
    public static function genYaml()
    {
        // Generate header swagger yaml from this class.
        header("Content-type: application/json; charset=utf-8");
        $objRef = new ReflectionClass("SwaggerUtil");
        $docStr = $objRef->getDocComment();
        if (preg_match(self::$REGEX_SWAGGER_HEAD, $docStr, $matches) > 0) {
            echo $matches[1];
            $basePath = "/" . ApiUtil::getAppName() . "/api";
            echo "basePath: \"$basePath\"\n";
        }

        // Collect controller classes and methods.
        $controllers = array();
        $methods = array();
        $phpFiles = ApiUtil::listPhpFiles("controller");
        foreach ($phpFiles as $phpFile) {
            require_once $phpFile;
            $class = str_replace("_", "", basename($phpFile, '.php')) . "Controller";
            if (!class_exists($class)) {
                continue;
            }
            $objRef = new ReflectionClass(new $class);
            $docStr = $objRef->getDocComment();
            if (preg_match("/@Controller/", $objRef->getDocComment()) <= 0) {
                continue; // Skip non-controller class.
            }
            array_push($controllers, $objRef);
            $methods = array_merge($methods, $objRef->getMethods());
        }

        // Generate tag portion from controllers.
        echo "\ntags:\n";
        foreach ($controllers as $objRef) {
            $docStr = $objRef->getDocComment();
            if (preg_match(self::$REGEX_SWAGGER_TAG, $docStr, $matches) > 0) {
                echo $matches[1];
            }
        }

        // Generate api portion from methods of controllers.
        echo "\npaths:\n";
        foreach ($methods as $method) {
            $docStr = $method->getDocComment();
            if (preg_match(self::$REGEX_SWAGGER_PATH, $docStr, $matches) > 0) {
                echo $matches[1];
            }
        }

        // Generate definition portion from controllers.
        echo "\ndefinitions:\n";
        foreach ($controllers as $objRef) {
            $docStr = $objRef->getDocComment();
            if (preg_match(self::$REGEX_SWAGGER_DEFINITION, $docStr, $matches) > 0) {
                echo $matches[1];
            }
        }
        $objRef = new ReflectionClass("SwaggerUtil"); // Plus global definition.
        $docStr = $objRef->getDocComment();
        if (preg_match(self::$REGEX_SWAGGER_DEFINITION, $docStr, $matches) > 0) {
            echo $matches[1];
        }
    }
}
