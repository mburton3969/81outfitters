<?php
header('Content-Type: application/json');
error_reporting(0);
include 'connection.php';

//Load Variables...
$api_key = $_REQUEST['api_key'];
$mode = $_REQUEST['mode'];
$oauth_code = "AgAAAA**AQAAAA**aAAAAA**D/r3Xg**nY+sHZ2PrBmdj6wVnY+sEZ2PrA2dj6AHmIagDJaBqAudj6x9nY+seQ**RUIGAA**AAMAAA**Pay1QtzwmyJJlZ8EzTzS+oC1b88ajWDAmybbDfsTPTbg16aKLp0OA7KFamgSXDtLz4qzydvdaGOlm9txp6fbuCPBRhKUtoaEDGNWIbcj+jMpwEgLaIqutwKCtEwUa74bvkrDwoUAZU1Wa7TXJ80ypL+1spMEs5RNZUYRCLzJqDpHuGHXdrPHe6Xe6TENDYSPl7iOsJRfSo2uCDG444nyhf4ZBW2p5Lr960RimsoZ4nenKdkcNra9N5NoZTp0LD/XQ+vDVsDc2TIFxoGIr9ich8iU6YLqweAM6oAC2Od+ojDU88osrruObdQ3gjeEmZY/YwcVJDkU3bpJEcQyt12nZ5wklzRjSXK0I2xD8ZccEA+c81cIJiQX1AlQroyIrLu7QalxMNT4f4PYioJiz6xZqEyDe2DZWRdO9O9xC4njerazPE2RONbDHHFdN5huHscC5d7M00uZ2G75xkfoAwh5P7oiqZbqBZMevzFsdS90zLIJThn/Dn32wuB3fxfuuLN6k7rgZaRo7XKF6nX8mv/CJuJr20BGdXDF3ciDshooTJTIRx+Hc2xRRmx/h7SmDLgXEMygRck6tbEkBnRFdZXFYkHmLEJ5a/vvtfkhj5YVvI2h1kyz6cvv98vqjrxngkivcRL8wfY7BbDBqLYo2rt5Wp1maNsLZsRgL8tDhZ+uRwOZm3mffvqNd2ArzsTO0D70EViw5nNV4nF3dc4Tr+x5Fofm4pLQzpfV9GLqusLz1eN6Db8eQOYt9sufvyW4+MJh";
$env_mode = 'production';
$adder = '?oauth_code=' . $oauth_code . '&env_mode=' . $env_mode;
#Main Functions...

//Loop through items on ebay to update website...
//Initialize cURL connection...
$ch = curl_init();
//Set the URL...
$url = 'http://beta.reseller-solutions.com/assets/ebay/get-all-ebay-current-selling.php' . $adder;
curl_setopt($ch, CURLOPT_URL, $url);
//curl_setopt($ch, CURLOPT_POST, 1);
/*curl_setopt($ch, CURLOPT_POSTFIELDS,
            "postvar1=value1&postvar2=value2&postvar3=value3");*/

// In real life you should use something like:
// curl_setopt($ch, CURLOPT_POSTFIELDS, 
//          http_build_query(array('oauth_code' => $oauth_code)));

// Receive server response ...
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
// Execute cURL Request...
$output = curl_exec($ch);
// Close cURL Connecion...
curl_close ($ch);

// processing response output ...
if ($output == "OK") { 
  //var_dump($output);
  $x->response = 'GOOD';
  $r = json_decode($output);
  $x->items = $r;
} else { 
  $x->response = 'ERROR';
  $x->error_message = 'HTTP Request Failure';
  $x->raw = $output;
}



//Setup Response Output...
$response = json_encode($x,JSON_PRETTY_PRINT);
echo $response;