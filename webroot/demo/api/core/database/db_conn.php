<?php

abstract class DbConn
{
    abstract public function beginTransaction();
    abstract public function commit();
    abstract public function rollback();

    /**
     * Get specific SQL command from doc comment of give method and class object.
     */
    protected function getDocString($classObj, $method, $notation)
    {
        $sqlStr = "";
        $objRef = new ReflectionClass($classObj);
        $method = $objRef->getMethod($method);
        $docStr = $method->getDocComment();
        $matchCount = preg_match_all("/@" . $notation . "\((.*)\)/", $docStr, $matchesAll);
        if ($matchCount < 1) {
            throw new DocStringNotFoundError(); // Will respond 500 error.
        }
        foreach ($matchesAll[1] as $match) {
            $sqlStr .= trim($match, '"');
        }
        // echo $sqlStr."\n";
        return $sqlStr;
    }

    /**
     * Generate UUID v4 string.
     */
    protected function guidv4()
    {
        // Generate 16 bytes (128 bits) of random data or use the data passed into the function.
        $data = random_bytes(16);
        // Set version to 0100
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        // Set bits 6-7 to 10
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
        // Output the 36 character UUID.
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}
