<?php
require __DIR__.'/_inc.php';
need_login();
$db=pdo();
$stt=[1=>'جدید',2=>'در حال انجام',3=>'تمام‌شده',4=>'لغوشده'];
if(isset($_GET['st'],$_GET['id'])){
  $db->prepare('UPDATE orders SET status=? WHERE id=?')->execute([(int)$_GET['st'],(int)$_GET['id']]);
  header('Location: orders.php');exit;
}
if(isset($_GET['del'])){
  $db->prepare('DELETE FROM orders WHERE id=?')->execute([(int)$_GET['del']]);
  header('Location: orders.php');exit;
}
$rows=$db->query('SELECT * FROM orders ORDER BY id DESC LIMIT 200')->fetchAll();
head('سفارش‌ها');nav('orders.php');
echo '<table><tr><th>#</th><th>کد</th><th>عنوان</th><th>آدرس</th><th>زمان</th><th>مبلغ</th><th>وضعیت</th><th>ثبت</th><th></th></tr>';
foreach($rows as $r){
  echo '<tr><td>'.$r['id'].'</td><td>'.h($r['code']).'</td><td>'.h($r['title']).'</td><td>'.h($r['addr']).'</td><td>'.h($r['time']).'</td><td>'.number_format($r['total']).'</td><td><select onchange="location=\'?id='.$r['id'].'&st=\'+this.value">';
  foreach($stt as $k=>$t) echo '<option value="'.$k.'"'.($r['status']==$k?' selected':'').'>'.$t.'</option>';
  echo '</select></td><td style="white-space:nowrap">'.h($r['created_at']).'</td>'
    .'<td><a class="btn small danger" href="?del='.$r['id'].'" onclick="return confirm(\'حذف شود؟\')">حذف</a></td></tr>';
}
echo '</table>';
foot();
