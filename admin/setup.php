<?php
// iCarz one-time setup: creates DB + tables + seeds + admin. DELETE THIS FILE after run.
require __DIR__.'/../config.php';
require __DIR__.'/_seed.php';
$log=[];
try{
  $root=pdo(false);
  $root->exec('CREATE DATABASE IF NOT EXISTS `'.DB_NAME.'` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
  $log[]='دیتابیس ساخته شد';
  $db=pdo();
  $db->exec("CREATE TABLE IF NOT EXISTS admin_users (id INT AUTO_INCREMENT PRIMARY KEY,user VARCHAR(60) NOT NULL UNIQUE,pass VARCHAR(255) NOT NULL,created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
  $db->exec("CREATE TABLE IF NOT EXISTS sellers (id INT AUTO_INCREMENT PRIMARY KEY,name VARCHAR(120) NOT NULL,area VARCHAR(120) NOT NULL DEFAULT '',dist VARCHAR(60) NOT NULL DEFAULT '',eta VARCHAR(60) NOT NULL DEFAULT '',rating VARCHAR(12) NOT NULL DEFAULT '',jobs VARCHAR(60) NOT NULL DEFAULT '',fee INT NOT NULL DEFAULT 0) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
  $db->exec("CREATE TABLE IF NOT EXISTS products (id INT AUTO_INCREMENT PRIMARY KEY,brand VARCHAR(60) NOT NULL DEFAULT '',name VARCHAR(160) NOT NULL,cat VARCHAR(20) NOT NULL DEFAULT 'car',for_car VARCHAR(160) NOT NULL DEFAULT '',price INT NOT NULL DEFAULT 0,old INT NOT NULL DEFAULT 0,stock INT NOT NULL DEFAULT 10,pct INT NOT NULL DEFAULT 0,rating VARCHAR(12) NOT NULL DEFAULT '',sold VARCHAR(20) NOT NULL DEFAULT '',warranty VARCHAR(120) NOT NULL DEFAULT '',seller_id INT NOT NULL DEFAULT 1,hot VARCHAR(12) NOT NULL DEFAULT '',grad VARCHAR(160) NOT NULL DEFAULT '',label VARCHAR(160) NOT NULL DEFAULT '') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
  $db->exec("CREATE TABLE IF NOT EXISTS orders (id INT AUTO_INCREMENT PRIMARY KEY,code VARCHAR(30) NOT NULL,type VARCHAR(20) NOT NULL DEFAULT 'buy',title VARCHAR(220) NOT NULL,addr VARCHAR(220) NOT NULL DEFAULT '',time VARCHAR(80) NOT NULL DEFAULT '',total INT NOT NULL DEFAULT 0,status TINYINT NOT NULL DEFAULT 1,created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,INDEX (status),INDEX (created_at)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
  $log[]='جدول‌ها ساخته شدند';
  if(!$db->query('SELECT COUNT(*) c FROM sellers')->fetch()['c']){
    $st=$db->prepare('INSERT INTO sellers (id,name,area,dist,eta,rating,jobs,fee) VALUES (?,?,?,?,?,?,?,?)');
    foreach($seedSellers as $r) $st->execute([$r['id'],$r['name'],$r['area'],$r['dist'],$r['eta'],$r['rating'],$r['jobs'],$r['fee']]);
    $log[]=count($seedSellers).' فروشنده ثبت شد';
  } else $log[]='فروشندگان قبلاً ثبت شده‌اند';
  if(!$db->query('SELECT COUNT(*) c FROM products')->fetch()['c']){
    $st=$db->prepare('INSERT INTO products (id,brand,name,cat,for_car,price,old,stock,pct,rating,sold,warranty,seller_id,hot,grad,label) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)');
    foreach($seedProducts as $r) $st->execute([$r['id'],$r['brand'],$r['name'],$r['cat'],$r['for_car'],$r['price'],$r['old'],$r['stock'],$r['pct'],$r['rating'],$r['sold'],$r['warranty'],$r['seller_id'],$r['hot'],$r['grad'],$r['label']]);
    $log[]=count($seedProducts).' محصول ثبت شد';
  } else $log[]='محصولات قبلاً ثبت شده‌اند';
  if(!$db->query("SELECT COUNT(*) c FROM admin_users WHERE user='admin'")->fetch()['c']){
    $st=$db->prepare('INSERT INTO admin_users (user,pass) VALUES (?,?)');
    $st->execute(['admin',password_hash('admin123',PASSWORD_DEFAULT)]);
    $log[]='کاربر مدیر ساخته شد (admin / admin123)';
  } else $log[]='کاربر مدیر قبلاً وجود دارد';
  $log[]='تمام شد! حالا این فایل (setup.php) را حذف کنید و وارد <a href="index.php">پنل</a> شوید.';
  $ok=true;
}catch(Throwable $e){$log[]='خطا: '.$e->getMessage();$ok=false;}
?><!DOCTYPE html><html lang="fa" dir="rtl"><head><meta charset="utf-8"><title>نصب آی‌کارز</title>
<style>body{background:#0B0F1A;color:#E8EBF3;font-family:Tahoma,Arial;display:grid;place-items:center;min-height:100vh;margin:0}
.box{background:#141B30;border:1px solid #2A3560;border-radius:14px;padding:26px;max-width:520px}a{color:#FF9D1F}li{margin:7px 0}</style>
</head><body><div class="box"><h2 style="color:#FF9D1F">نصب <?= $ok?'موفق ✅':'ناموفق ❌' ?></h2><ul><?php
foreach($log as $l) echo '<li>'.$l.'</li>';
?></ul></div></body></html>
