<?php
if($_SERVER['HTTP_HOST'] == 'beta.81outfitters.com'){
  
  // HTTP
  define('HTTP_SERVER', 'http://beta.81outfitters.com/');

  // HTTPS
  define('HTTPS_SERVER', 'http://beta.81outfitters.com/');

  // DIR
  define('DIR_APPLICATION', '/home1/outfitters/beta/catalog/');
  define('DIR_SYSTEM', '/home1/outfitters/beta/system/');
  define('DIR_IMAGE', '/home1/outfitters/beta/image/');
  define('DIR_STORAGE', '/home1/outfitters/storage/');
  define('DIR_LANGUAGE', DIR_APPLICATION . 'language/');
  define('DIR_TEMPLATE', DIR_APPLICATION . 'view/theme/');
  define('DIR_CONFIG', DIR_SYSTEM . 'config/');
  define('DIR_CACHE', DIR_STORAGE . 'cache/');
  define('DIR_DOWNLOAD', DIR_STORAGE . 'download/');
  define('DIR_LOGS', DIR_STORAGE . 'logs/');
  define('DIR_MODIFICATION', DIR_STORAGE . 'modification/');
  define('DIR_SESSION', DIR_STORAGE . 'session/');
  define('DIR_UPLOAD', DIR_STORAGE . 'upload/');

  // DB
  define('DB_DRIVER', 'mysqli');
  define('DB_HOSTNAME', 'localhost');
  define('DB_USERNAME', 'outfitte_store');
  define('DB_PASSWORD', '+F%JW[$YDOR(');
  define('DB_DATABASE', 'outfitte_opencart');
  define('DB_PORT', '3306');
  define('DB_PREFIX', 'oc_');
  
}else{
  
  // HTTP
  define('HTTP_SERVER', 'http://81outfitters.com/');

  // HTTPS
  define('HTTPS_SERVER', 'http://81outfitters.com/');

  // DIR
  define('DIR_APPLICATION', '/home1/outfitters/public_html/catalog/');
  define('DIR_SYSTEM', '/home1/outfitters/public_html/system/');
  define('DIR_IMAGE', '/home1/outfitters/public_html/image/');
  define('DIR_STORAGE', '/home1/outfitters/storage/');
  define('DIR_LANGUAGE', DIR_APPLICATION . 'language/');
  define('DIR_TEMPLATE', DIR_APPLICATION . 'view/theme/');
  define('DIR_CONFIG', DIR_SYSTEM . 'config/');
  define('DIR_CACHE', DIR_STORAGE . 'cache/');
  define('DIR_DOWNLOAD', DIR_STORAGE . 'download/');
  define('DIR_LOGS', DIR_STORAGE . 'logs/');
  define('DIR_MODIFICATION', DIR_STORAGE . 'modification/');
  define('DIR_SESSION', DIR_STORAGE . 'session/');
  define('DIR_UPLOAD', DIR_STORAGE . 'upload/');

  // DB
  define('DB_DRIVER', 'mysqli');
  define('DB_HOSTNAME', 'localhost');
  define('DB_USERNAME', 'outfitte_store');
  define('DB_PASSWORD', '+F%JW[$YDOR(');
  define('DB_DATABASE', 'outfitte_opencart');
  define('DB_PORT', '3306');
  define('DB_PREFIX', 'oc_');
  
}