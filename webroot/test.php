<?php

echo "HRU\n";
$files = glob("./*.php");
foreach ($files as $file) {
    echo $file . "<br>";
}