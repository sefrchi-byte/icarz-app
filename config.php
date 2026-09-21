<?php
// iCarz backend config — XAMPP defaults (host/user/pass را اگر فرق دارد عوض کنید)
define('DB_HOST','localhost');
define('DB_NAME','icarz');
define('DB_USER','root');
define('DB_PASS','');
define('INGEST_KEY','CHANGE-ME-shared-secret'); // vinAdd ingestion key — ستونِ سایت و ابزار تشخیص رنگ باید همین را بفرستند
function pdo($db=true){
  $dsn='mysql:host='.DB_HOST.($db?';dbname='.DB_NAME:'').';charset=utf8mb4';
  return new PDO($dsn,DB_USER,DB_PASS,[
    PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,
  ]);
}
