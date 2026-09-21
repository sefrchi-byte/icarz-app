<?php
require __DIR__.'/_inc.php';
need_login();
$db=pdo();
if(isset($_GET['del'])){
  $db->prepare('DELETE FROM vin_reports WHERE id=?')->execute([(int)$_GET['del']]);
  header('Location: vins.php');exit;
}
$rows=$db->query('SELECT * FROM vin_reports ORDER BY updated_at DESC LIMIT 200')->fetchAll();
head('گزارش‌های VIN');nav('vins.php');
echo '<table><tr><th>#</th><th>VIN</th><th>خودرو</th><th>کارکرد</th><th>امتیاز</th><th>قیمت</th><th>منبع</th><th>به‌روزرسانی</th><th></th></tr>';
foreach($rows as $r){
  echo '<tr><td>'.$r['id'].'</td><td style="direction:ltr;text-align:left">'.h($r['vin']).'</td><td>'.h($r['car']).'</td><td>'.number_format((int)$r['km']).'</td><td>'.($r['score']===null?'—':h($r['score'])).'</td><td>'.($r['price']?number_format((int)$r['price']):'—').'</td><td>'.h($r['src']).'</td><td style="white-space:nowrap">'.h($r['updated_at']).'</td>'
    .'<td><a class="btn small danger" href="?del='.$r['id'].'" onclick="return confirm(\'حذف شود؟\')">حذف</a></td></tr>';
}
if(!$rows) echo '<tr><td colspan="9" style="padding:18px;color:#9AA7C4">هنوز رکوردی ثبت نشده — ابزار سایت با <code>api.php?action=vinAdd</code> و INGEST_KEY وارد می‌کند.</td></tr>';
echo '</table>';
foot();
