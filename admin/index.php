<?php
// admin login
require __DIR__.'/_inc.php';
if(!empty($_SESSION['admin'])){header('Location: dash.php');exit;}
$err='';
if($_SERVER['REQUEST_METHOD']==='POST'){
  try{
    $st=pdo()->prepare('SELECT * FROM admin_users WHERE user=?');
    $st->execute([$_POST['u']??'']);
    $a=$st->fetch();
    if($a&&password_verify($_POST['p']??'',$a['pass'])){
      $_SESSION['admin']=$a['user'];
      header('Location: dash.php');exit;
    }
    $err='نام کاربری یا رمز اشتباه است';
  }catch(Throwable $e){$err='خطای دیتابیس — اول setup.php را اجرا کنید';}
}
head('ورود');
echo '<div class="wrap"><div class="login card"><h2>⚡ ورود مدیر</h2>';
if($err) echo '<div class="err">'.h($err).'</div>';
echo '<form method="post"><div class="row"><input name="u" placeholder="نام کاربری" required></div>'
  .'<div class="row"><input name="p" type="password" placeholder="رمز عبور" required></div>'
  .'<button class="btn" style="width:100%">ورود</button></form></div></div>';
foot();
