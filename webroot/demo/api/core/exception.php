<?php

/**
 * Redirect.
 * Respond HTTP 302.
 */
class Redirect extends Error
{
    // Redirect to this url.
    public function __construct($url, $code = 0, Throwable $previous = null)
    {
        parent::__construct($url, $code, $previous);
    }
}

/**
 * Validation error.
 * Respond HTTP 400.
 */
class ValidationError extends Error
{
    public function __construct($data, $code = 0, Throwable $previous = null)
    {
        if (gettype($data) === "array") {
            $message = join(" ", $data);
        }
        if (gettype($data) === "string") {
            $message = $data;
        }
        parent::__construct($message, $code, $previous);
    }
}

/**
 * Unauthorized error.
 * Respond HTTP 401.
 */
class UnauthorizedError extends Error
{
    public function __construct($message = "Unauthorized error.", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}

/**
 * Entity not found.
 * Respond HTTP 404.
 */
class EntityNotFoundError extends Error
{
    public function __construct($message = "Query entity is not existed.", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}

/**
 * Can not find corresponding method in controller classes by request API path.
 * Respond HTTP 405.
 */
class ControllerMethodNotFoundError extends Error
{
    public function __construct($message = "Invalid request URI and method.", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}

/**
 * Business logic error.
 * Respond HTTP 405.
 */
class BusinessLogicError extends Error
{
    public function __construct($message = "Business logic error.", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}

/**
 * Document string not found error.
 * Respond HTTP 500.
 */
class DocStringNotFoundError extends Error
{
    public function __construct($message = "Document string is not defined properly.", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}

/**
 * Customized database error.
 * Respond HTTP 500.
 */
class CustomizedDatabaseError extends Error
{
    public function __construct($errorInfo = array("0", 0, null), $code = 0, Throwable $previous = null)
    {
        $message = $errorInfo[2];
        $code = $errorInfo[1];
        parent::__construct($message, $code, $previous);
    }
}
