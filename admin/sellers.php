<?php
require __DIR__.'/_inc.php';
need_login();
$db=pdo();$msg='';
if(isset($_GET['del'])){
  $db->prepare('DELETE FROM sellers WHERE id=?')->execute([(int)$_GET['del']]);
  header('Location: sellers.php');exit;
}
if($_SERVER['REQUEST_METHOD']==='POST'){
  $v=[$_POST['name']??'',$_POST['area']??'',$_POST['dist']??'',$_POST['eta']??'',$_POST['rating']??'',$_POST['jobs']??'',(int)($_POST['fee']??0)];
  if(!empty($_POST['id'])){
    $v[]=(int)$_POST['id'];
    $db->prepare('UPDATE sellers SET name=?,area=?,dist=?,eta=?,rating=?,jobs=?,fee=? WHERE id=?')->execute($v);
    $msg='ویرایش شد ✅';
  }else{
    $db->prepare('INSERT INTO sellers (name,area,dist,eta,rating,jobs,fee) VALUES (?,?,?,?,?,?,?)')->execute($v);
    $msg='اضافه شد ✅';
  }
}
$edit=null;
if(isset($_GET['edit'])){$st=$db->prepare('SELECT * FROM sellers WHERE id=?');$st->execute([(int)$_GET['edit']]);$edit=$st->fetch();}
$rows=$db->query('SELECT * FROM sellers ORDER BY id')->fetchAll();
head('فروشندگان');nav('sellers.php');
if($msg) echo '<div class="msg">'.$msg.'</div>';
echo '<div class="card"><b>'.($edit?'ویرایش فروشنده #'.$edit['id']:'➕ فروشنده جدید').'</b>'
  .'<form method="post" style="margin-top:10px">'.($edit?'<input type="hidden" name="id" value="'.$edit['id'].'">':'')
  .'<div class="row"><input name="name" placeholder="نام *" value="'.h($edit['name']??'').'" required>'
  .'<input name="area" placeholder="منطقه" value="'.h($edit['area']??'').'"></div>'
  .'<div class="row"><input name="dist" placeholder="فاصله (۲ کیلومتر)" value="'.h($edit['dist']??'').'">'
  .'<input name="eta" placeholder="زمان رسیدن (۳۰ دقیقه)" value="'.h($edit['eta']??'').'">'
  .'<input name="rating" placeholder="امتیاز" value="'.h($edit['rating']??'').'">'
  .'<input name="jobs" placeholder="سابقه" value="'.h($edit['jobs']??'').'">'
  .'<input name="fee" type="number" placeholder="هزینه نصب (۰=رایگان)" value="'.h($edit['fee']??'').'"></div>'
  .'<button class="btn">ذخیره</button> '.($edit?'<a class="btn ghost" href="sellers.php">انصراف</a>':'').'</form></div>';
echo '<table><tr><th>#</th><th>نام</th><th>منطقه</th><th>امتیاز</th><th>نصب</th><th></th></tr>';
foreach($rows as $r){
  echo '<tr><td>'.$r['id'].'</td><td>'.h($r['name']).'</td><td>'.h($r['area']).'</td><td>'.$r['rating'].'</td><td>'.($r['fee']?number_format($r['fee']):'رایگان').'</td>'
    .'<td style="white-space:nowrap"><a class="btn small" href="?edit='.$r['id'].'">ویرایش</a> <a class="btn small danger" href="?del='.$r['id'].'" onclick="return confirm(\'حذف شود؟\')">حذف</a></td></tr>';
}
echo '</table>';
foot();
