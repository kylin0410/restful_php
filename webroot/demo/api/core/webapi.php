<?php
require_once "controller/auth.php";

/**
 * Web API main implementation.
 */
class WebAPI
{
    /**
     * WebAPI constructor. Initialize configuration.
     */
    public function __construct()
    {
        Config::initConfig();
    }

    /**
     * Main procedure of web api.
     */
    public function process()
    {
        $transaction = false;
        try {

            // Check api document generation.
            $reqRoute = ApiUtil::getApiPath();
            if ($reqRoute === "/api/swagger" && Config::getConfig(Config::$KEY_APIDOC)) {
                SwaggerUtil::genYaml();
                return;
            }

            // Search for API method.
            $phpFiles = ApiUtil::listPhpFiles("controller");
            list($matchClass, $matchMethod, $pathParams, $docStr) = $this->route($phpFiles);

            // Do auth. Set login user to BaseController. Use for creating or updating.
            $user = $this->auth($docStr);
            $matchClass->setLoginUserInfo($user);

            // Process request and response.
            if (preg_match("/@Transaction/", $docStr, $matches) > 0) {
                $transaction = true;
                $matchClass->beginTransaction();
            }
            $matchMethod(...$pathParams);
            if ($transaction) {
                $matchClass->commit();
            }
        } catch (Throwable $e) {
            if ($transaction) {
                $matchClass->rollback();
            }
            $msg = "In " . $e->getFile() . ":[" . $e->getLine() . "]:\n"  . $e->getMessage();
            Logging::error($msg);
            return BaseController::respondError($e);
        }
    }

    /**
     * Do authentication and authorization.
     */
    private function auth($docStr)
    {
        // Get necessary info.
        $authEnable = Config::getConfig(Config::$KEY_AUTH)["enable"];
        $authFlag = preg_match("/@Auth/", $docStr, $matches) > 0 ? true : false;
        $header = $_SERVER["HTTP_AUTHORIZATION"];

        // Error handling.
        if ($authEnable && $authFlag && empty($header)) {
            throw new UnauthorizedError("Authentication fails due to authorization is not in header.");
        }
        if (empty($header)) {
            return null;
        }

        // Do authentication.
        $matches = array();
        if (preg_match("/Bearer (.+)/", $_SERVER["HTTP_AUTHORIZATION"], $matches) <= 0) {
            throw new UnauthorizedError("Wrong authorization header format.");
        }
        $token = $matches[1];
        $payload = JwtUtil::decodePayload($token);
        if (isset($payload['exp']) && $payload['exp'] < $_SERVER['REQUEST_TIME']) {
            throw new UnauthorizedError("Authentication time is expire.");
        }

        // Do authorization.
        $uid = $payload["uid"];
        $class = "AuthController";
        $authCtrl = new $class;
        $user = $authCtrl->authorize($uid);
        return $user;
    }

    /**
     * Route the API path, search and match the request method and uri from available controllers.
     */
    private function route($phpFiles)
    {
        $reqMethod = $_SERVER['REQUEST_METHOD'];
        $reqRoute = ApiUtil::getApiPath();

        $matchClass = null;
        $matchMethod = null;
        $pathParams = array();
        $docStr = "";
        foreach ($phpFiles as $phpFile) {
            // Load controller classes.
            require_once $phpFile;
            $class = str_replace("_", "", basename($phpFile, '.php')) . "Controller";
            if (!class_exists($class)) {
                continue;
            }
            $obj = new $class;
            $objRef = new ReflectionClass($obj);
            if (preg_match("/@Controller/", $objRef->getDocComment()) <= 0) {
                continue; // Skip non-controller class.
            }

            // Search matched method by API route and http method.
            $objMethods = $objRef->getMethods();
            foreach ($objMethods as $method) {
                // Get route setting and support HTTP methods from controll's method.
                $supportRoute = "";
                $supportMethods = array();
                $docStr = $method->getDocComment();
                if (preg_match("/@Route\((.*)\)/", $docStr, $matches) > 0) {
                    $supportRoute = trim($matches[1], '"');
                }
                if (preg_match("/@Method\((.*)\)/", $docStr, $matches) > 0) {
                    $meth = trim($matches[1], '"');
                    $supportMethods = explode("\", \"", $meth);
                }
                // See if request HTTP method matches or not.
                if (!in_array($reqMethod, $supportMethods)) {
                    continue;
                }
                // See if request API path matches or not.
                $pattern = "/^" . str_replace("/", "\/", $supportRoute) . "$/";
                //if ($reqRoute != $supportRoute) { // Complete match
                if (preg_match($pattern, $reqRoute, $matches) <= 0) { // Regular expression match.
                    continue;
                }
                $pathParams = $matches;
                // Matched.
                $matchClass = $obj;
                $matchMethod = array($obj, $method->name);
                break;
            }
            if ($matchMethod) {
                break;
            }
        }
        if (!$matchMethod) {
            throw new ControllerMethodNotFoundError("Invalid request route path ($reqRoute) and method($reqMethod).");
        }
        array_shift($pathParams);
        return array($matchClass, $matchMethod, $pathParams, $docStr);
    }
}
