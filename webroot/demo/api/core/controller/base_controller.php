<?php

/**
 * Interface for database conn
 */
interface iCtrl
{
    public function getLoginUserId();
}

/**
 * Base controller is root controller class.
 * Provides common process to HTTP request and response.
 */
abstract class BaseController implements iCtrl
{
    private $startTime;
    private $loginUser;

    public function setLoginUserInfo($loginUser)
    {
        $this->startTime = microtime(true);
        $this->loginUser = $loginUser;
    }

    public function getLoginUserId()
    {
        if (!isset($this->loginUser["id"])) {
            return "system";
        }
        return $this->loginUser["id"];
    }

    public function getLoginUserEmail()
    {
        if (!isset($this->loginUser["email"])) {
            return "";
        }
        return $this->loginUser["email"];
    }

    public function getLoginUserName()
    {
        if (!isset($this->loginUser["name"])) {
            return "";
        }
        return $this->loginUser["name"];
    }

    /**
     * Check lost keys then supply it with null value.
     */
    protected function checkLostKeys(&$jsonDict, $keys)
    {
        foreach ($keys as $key) {
            if (!array_key_exists($key, $jsonDict)) {
                $jsonDict[$key] = null;
            }
        }
    }

    /**
     * Validate must give value for keys.
     */
    protected function checkRequiredKeys($jsonDict, $keys)
    {
        $invalidAry = array();
        foreach ($keys as $key) {
            if (!isset($jsonDict[$key])) {
                array_push($invalidAry, "Parameter [$key] is required.");
            }
        }
        return $invalidAry;
    }

    /**
     * Get parsed key value pair from HTTP request URL parameters.
     */
    protected function parseParams($paramStr)
    {
        // Remove first ? character.
        if (!empty($paramStr)) {
            $first = substr($paramStr, 0, 1);
            if ($first === "?") {
                $paramStr = substr($paramStr, 1);
            }
        }

        // Parse parameters into array.
        $paramStr = urldecode($paramStr);
        $matchCount = preg_match_all("/([^=&]+)=([^=&]+)&?/", $paramStr, $matchesAll);
        $params = array();
        for ($i = 0; $i < $matchCount; $i++) {
            $params[$matchesAll[1][$i]] = $matchesAll[2][$i];
        }
        return $params;
    }

    /**
     * Parse key value pair from HTTP request parameters.
     * Also parse paging related parameters.
     */
    protected function parsePageParams($paramStr)
    {
        // Remove first ? character.
        if (!empty($paramStr)) {
            $first = substr($paramStr, 0, 1);
            if ($first === "?") {
                $paramStr = substr($paramStr, 1);
            }
        }

        // Parse parameters into array.
        $paramStr = urldecode($paramStr);
        $matchCount = preg_match_all("/([^=&]+)=([^=&]+)&?/", $paramStr, $matchesAll);
        $params = array();
        for ($i = 0; $i < $matchCount; $i++) {
            $params[$matchesAll[1][$i]] = $matchesAll[2][$i];
        }

        // Add default paging and sort parameter
        if (!array_key_exists("pageNo", $params)) {
            $params["pageNo"] = "1";
        }
        if (!array_key_exists("pageSize", $params)) {
            $params["pageSize"] = "200";
        }
        if (!array_key_exists("sortBy", $params)) {
            $params["sortBy"] = "updateTime-";
        }

        // urldecode() makes '+' become ' ', so replace it back.
        $params["sortBy"] = str_replace(" ", "+", $params["sortBy"]);

        // Validate paging parameters.
        $validAry = array();
        if (!is_numeric($params["pageNo"])) {
            array_push($validAry, "Parameter [pageNo] must be number.");
        }
        if ($params["pageNo"] < 1) {
            array_push($validAry, "Parameter [pageNo] must be positive number.");
        }
        if (!is_numeric($params["pageSize"])) {
            array_push($validAry, "Parameter [pageNo] must be number.");
        }
        if ($params["pageSize"] < 1 || $params["pageSize"] > 200) {
            array_push($validAry, "Parameter [pageNo] must be between 1 ~ 200.");
        }
        if (!empty($validAry)) {
            throw new ValidationError($validAry);
        }
        return $params;
    }

    /**
     * Get request body.
     */
    protected function getContentFromBody()
    {
        return file_get_contents('php://input');
    }

    /**
     * Get json object as PHP array (dictionary naming) from request body.
     */
    protected function getJsonDictFromBody($assoc = true)
    {
        $jsonStr = file_get_contents('php://input');
        return json_decode($jsonStr, $assoc);
    }

    /**
     * Get request headers in dictionary.
     */
    protected function getRequestHeaders($getKey = null)
    {
        $headers = array();
        foreach ($_SERVER as $key => $value) {
            if (substr($key, 0, 5) <> 'HTTP_') {
                continue;
            }
            $headerKey = str_replace(' ', '-', ucwords(str_replace('_', ' ', strtolower(substr($key, 5)))));
            $headers[$headerKey] = $value;
            if ($headerKey === $getKey) {
                return $value;
            }
        }
        return $headers;
    }

    /**
     * Return authentication data.
     */
    protected function makeAuthRespModel($uid, $token)
    {
        $data = array("uid" => $uid, "token" => "Bearer " . $token);
        $res = array();
        $res["data"] = $data;
        return $res;
    }

    /**
     * Return normal data (usually one entity).
     */
    protected function makeSingleRespModel($data)
    {
        $res = array();
        $res["data"] = $data;
        return $res;
    }

    /**
     * Return list data (data in array).
     */
    protected function makeListRespModel($array)
    {
        if ($array == null) {
            $array = array();
        }
        $res = array();
        $res["data"] = $array;
        $res["totalCounts"] = count($array);
        return $res;
    }

    /**
     * Return page data.
     */
    protected function makePageRespModel($pageData, $pageNo, $pageSize, $totalCounts)
    {
        $res = array();
        $res["data"] = $pageData;
        $res["pageNo"] = $pageNo;
        $res["pageSize"] = $pageSize;
        $res["pageCounts"] = count($pageData);
        $res["totalPages"] = ceil($totalCounts / $pageSize);
        $res["totalCounts"] = $totalCounts;
        return $res;
    }

    /**
     * Respond json data.
     */
    protected function respondJson($data = array())
    {
        $totalTime = round((microtime(true) - $this->startTime) * 100000) / 100;
        header("HTTP/1.1 200 OK");
        header("Content-type: application/json; charset=utf-8");
        $data["message"] = "Success.";
        $data["totalTime"] = $totalTime . " ms";
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Respond file or content as stream by accept header or mime-type.
     * attached file (specify application/octet-stream in Accept of request header).
     * $data needs to have "fileName", "fileSize" and "fileContent" attributes.
     */
    protected function respondFile($data)
    {
        header("HTTP/1.1 200 OK");
        // Client specify "application/octet-stream" in header, or mime type is "application/octet-stream".
        $mime = ApiUtil::mapMime($data["fileName"]);
        $accept = $this->getRequestHeaders("Accept");
        if (!empty(stripos($accept, "application/octet-stream")) || $mime === "application/octet-stream") {
            header("Content-Type: application/octet-stream");
            header('Content-Disposition: attachment; filename="' . $data["fileName"] . '"');
        } else {
            header("Content-Type: " . $mime);
        }
        header("Content-Length: " . $data["fileSize"]);
        header("Expires: 0");
        echo $data["fileContent"];
    }

    /**
     * Respond error.
     */
    public static function respondError(Throwable $e)
    {
        // Respond header by class name.
        $className = get_class($e);
        $code = $e->getCode();
        $message = $e->getMessage();
        if ($className === "Redirect") {
            header("HTTP/1.1 302 Found");
            header("Location: " . $message);
            return;
        } elseif ($className === "ValidationError" || $className === "UnexpectedValueException") {
            header("HTTP/1.1 400 Bad Request");
        } elseif ($className === "UnauthorizedError") {
            header("HTTP/1.1 401 Unauthorized");
        } elseif ($className === "EntityNotFoundError") {
            header("HTTP/1.1 404 Not Found");
        } elseif ($className === "ControllerMethodNotFoundError") {
            header("HTTP/1.1 405 Method Not Allowed");
        } elseif ($className === "BusinessLogicError") {
            header("HTTP/1.1 406 Not Acceptable");
        } elseif ($className === "PDOException" && $code == "23000") {
            header("HTTP/1.1 406 Not Acceptable");
            $message = "Cannot create duplicate data. " . $message;
        } else {
            header("HTTP/1.1 500 Internal Server Error");
        }

        // Respond error in json format.
        header("Content-type: application/json; charset=utf-8");
        $retData["error_code"] = $code;
        $retData["message"] = $message;
        echo json_encode($retData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Save uploaded file from request body.
     */
    public function saveFileBody($filePath)
    {
        $data = $this->getContentFromBody();
        $res = file_put_contents($filePath, $data);
        $headers = $this->getRequestHeaders();
        $contLen = $headers["Content-Length"];
        if ($res != $contLen) {
            throw new Exception("Fail to upload file.");
        }
        $this->respondJson($this->makeSingleRespModel(null));
    }

    /**
     * Save uploaded file from form data.
     */
    public function saveFileForm($filePath)
    {
        if ($_FILES["uploadFile"]["error"] != UPLOAD_ERR_OK) {
            throw new Exception("Fail to upload file.");
        }
        $tmpPath = $_FILES["uploadFile"]["tmp_name"];
        $res = move_uploaded_file($tmpPath, $filePath);
        if (!$res) {
            throw new Exception("Fail to move file.");
        }
        $this->respondJson($this->makeSingleRespModel(null));
    }

    /**
     * Download file
     */
    public function downloadFile($filePath, $filename)
    {
        if (!file_exists($filePath)) {
            throw new EntityNotFoundError("File $filePath not found.");
        }
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filePath));
        readfile($filePath);
    }
}
