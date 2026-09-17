<?php
require __DIR__.'/_inc.php';
need_login();
$db=pdo();
$np=$db->query('SELECT COUNT(*) c FROM products')->fetch()['c'];
$ns=$db->query('SELECT COUNT(*) c FROM sellers')->fetch()['c'];
$no=$db->query('SELECT COUNT(*) c FROM orders')->fetch()['c'];
$rev=$db->query('SELECT COALESCE(SUM(total),0) s FROM orders')->fetch()['s'];
$last=$db->query('SELECT * FROM orders ORDER BY id DESC LIMIT 5')->fetchAll();
head('داشبورد');nav('dash.php');
echo '<div class="stats">'
  .'<div class="stat">محصولات<b>'.$np.'</b></div>'
  .'<div class="stat">فروشندگان<b>'.$ns.'</b></div>'
  .'<div class="stat">سفارش‌ها<b>'.$no.'</b></div>'
  .'<div class="stat">جمع فروش (تومان)<b>'.number_format($rev).'</b></div></div>';
echo '<div class="card" style="margin-top:12px"><b>آخرین سفارش‌ها</b><table style="margin-top:10px"><tr><th>کد</th><th>عنوان</th><th>مبلغ</th><th>وضعیت</th></tr>';
$stt=[1=>'جدید',2=>'در حال انجام',3=>'تمام‌شده',4=>'لغوشده'];
foreach($last as $r) echo '<tr><td>'.h($r['code']).'</td><td>'.h($r['title']).'</td><td>'.number_format($r['total']).'</td><td>'.($stt[$r['status']]??$r['status']).'</td></tr>';
echo '</table></div>';
foot();
