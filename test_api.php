<?php
$url = 'http://localhost:8080/food-rescue-api/utils/get_cities.php';
$res = @file_get_contents($url);
if ($res === false) {
    echo "API NOT REACHABLE AT $url";
} else {
    echo $res;
}
