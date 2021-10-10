<?php
require_once "core/utility/api_util.php";

// Import require PHP files.
$phpFiles = ApiUtil::listPhpFiles("core");
foreach ($phpFiles as $phpFile) {
    require_once $phpFile;
}

$api = new WebAPI();
$api->process();
