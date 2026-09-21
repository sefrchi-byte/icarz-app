<?php
require __DIR__.'/_inc.php';
need_login();
$db=pdo();$msg='';$delT=$_GET['t']??'';$delId=(int)($_GET['id']??0);
if(isset($_GET['del'])&&$delT){
  $map=['claims'=>'cmd_claims','owners'=>'cmd_owners','panels'=>'cmd_panels','violations'=>'cmd_violations','vehicles'=>'cmd_vehicles'];
  if(isset($map[$delT])&&$delId){
    if($delT==='vehicles'){
      $db->prepare('DELETE FROM cmd_claims WHERE vehicle_id=?')->execute([$delId]);
      $db->prepare('DELETE FROM cmd_owners WHERE vehicle_id=?')->execute([$delId]);
      $db->prepare('DELETE FROM cmd_panels WHERE vehicle_id=?')->execute([$delId]);
      $db->prepare('DELETE FROM cmd_violations WHERE vehicle_id=?')->execute([$delId]);
      $db->prepare('DELETE FROM cmd_vehicles WHERE id=?')->execute([$delId]);
    }else{
      $db->prepare('DELETE FROM '.$map[$delT].' WHERE id=?')->execute([$delId]);
    }
    header('Location: cmd.php'.(isset($_GET['edit'])?'?edit='.(int)$_GET['edit']:''));exit;
  }
}
if($_SERVER['REQUEST_METHOD']==='POST'){
  $what=$_POST['what']??'';
  if($what==='vehicle'){
    $v=[substr($_POST['plate']??'',0,30),substr($_POST['car']??'',0,120),(int)($_POST['km']??0),
        (int)($_POST['market']??0),(int)($_POST['delta']??0),(int)($_POST['body_score']??0),
        (int)($_POST['last_claim_days']??0),substr($_POST['note']??'',0,255)];
    if(!empty($_POST['id'])){
      $v[]=(int)$_POST['id'];
      $db->prepare('UPDATE cmd_vehicles SET plate=?,car=?,km=?,market=?,delta=?,body_score=?,last_claim_days=?,note=? WHERE id=?')->execute($v);
      $msg='خودرو ویرایش شد ✅';
    }else{
      $db->prepare('INSERT INTO cmd_vehicles (plate,car,km,market,delta,body_score,last_claim_days,note) VALUES (?,?,?,?,?,?,?,?)')->execute($v);
      $msg='خودرو اضافه شد ✅';
    }
  }elseif($what==='claim'){
    $db->prepare('INSERT INTO cmd_claims (vehicle_id,ins_date,insurer,note,amount) VALUES (?,?,?,?,?)')
      ->execute([(int)$_POST['vehicle_id'],substr($_POST['ins_date']??'',0,20),substr($_POST['insurer']??'',0,120),substr($_POST['note']??'',0,255),(int)($_POST['amount']??0)]);
    $msg='قبض اضافه شد ✅';
  }elseif($what==='owner'){
    $db->prepare('INSERT INTO cmd_owners (vehicle_id,seq,own_date,note,km_at,active) VALUES (?,?,?,?,?,?)')
      ->execute([(int)$_POST['vehicle_id'],(int)($_POST['seq']??1),substr($_POST['own_date']??'',0,20),substr($_POST['note']??'',0,255),(int)($_POST['km_at']??0),($_POST['active']??'0')==='1'?1:0]);
    $msg='مالک اضافه شد ✅';
  }elseif($what==='panel'){
    $db->prepare('INSERT INTO cmd_panels (vehicle_id,panel,kind) VALUES (?,?,?)')
      ->execute([(int)$_POST['vehicle_id'],substr($_POST['panel']??'',0,120),in_array($_POST['kind']??'',['orig','paint','repl'])?$_POST['kind']:'orig']);
    $msg='پنل اضافه شد ✅';
  }elseif($what==='violation'){
    $db->prepare('INSERT INTO cmd_violations (vehicle_id,vio_date,type,location,amount,status) VALUES (?,?,?,?,?,?)')
      ->execute([(int)$_POST['vehicle_id'],substr($_POST['vio_date']??'',0,20),substr($_POST['type']??'',0,120),substr($_POST['location']??'',0,120),(int)($_POST['amount']??0),($_POST['status']??'1')==='2'?2:1]);
    $msg='خلافی اضافه شد ✅';
  }
}
head('هوش خودرو');nav('cmd.php');
if($msg) echo '<div class="msg">'.$msg.'</div>';
// ---- list view
$vehicles=$db->query('SELECT v.*,
  (SELECT COUNT(*) FROM cmd_claims c WHERE c.vehicle_id=v.id) claims,
  (SELECT COUNT(*) FROM cmd_owners o WHERE o.vehicle_id=v.id) owners,
  (SELECT COUNT(*) FROM cmd_panels p WHERE p.vehicle_id=v.id) panels,
  (SELECT COUNT(*) FROM cmd_violations x WHERE x.vehicle_id=v.id) vios
  FROM cmd_vehicles v ORDER BY v.id')->fetchAll();
echo '<div class="card"><b>➕ خودرو جدید</b>';
if(!empty($_GET['edit'])){
  $st=$db->prepare('SELECT * FROM cmd_vehicles WHERE id=?');$st->execute([(int)$_GET['edit']]);$e=$st->fetch();
  echo '<form method="post" style="margin-top:10px"><input type="hidden" name="what" value="vehicle"><input type="hidden" name="id" value="'.$e['id'].'">'
   .'<div class="row"><input name="plate" placeholder="پلاک" value="'.h($e['plate']).'" required>'
   .'<input name="car" placeholder="نام خودرو" value="'.h($e['car']).'"></div>'
   .'<div class="row"><input name="km" type="number" placeholder="کیلومتر" value="'.$e['km'].'">'
   .'<input name="market" type="number" placeholder="ارزش بازار (میلیون تومان)" value="'.$e['market'].'">'
   .'<input name="delta" type="number" placeholder="تغییر این هفته (منفی=افت)" value="'.$e['delta'].'">'
   .'<input name="body_score" type="number" placeholder="وضعیت بدنه (٪)" value="'.$e['body_score'].'">'
   .'<input name="last_claim_days" type="number" placeholder="فاصله آخرین قبض (روز)" value="'.$e['last_claim_days'].'"></div>'
   .'<input name="note" placeholder="یادداشت" value="'.h($e['note']).'" style="margin-bottom:10px">'
   .'<button class="btn">ذخیره</button> <a class="btn ghost" href="cmd.php">بازگشت به فهرست</a></form>';
}else{
  echo '<form method="post" style="margin-top:10px"><input type="hidden" name="what" value="vehicle">'
   .'<div class="row"><input name="plate" placeholder="پلاک (مثلاً 12ب 3456)" required>'
   .'<input name="car" placeholder="نام خودرو (مثلاً پژو 206 تیپ 2)"></div>'
   .'<div class="row"><input name="km" type="number" placeholder="کیلومتر">'
   .'<input name="market" type="number" placeholder="ارزش بازار (میلیون تومان)">'
   .'<input name="delta" type="number" placeholder="تغییر این هفته (منفی=افت)">'
   .'<input name="body_score" type="number" placeholder="وضعیت بدنه (٪)">'
   .'<input name="last_claim_days" type="number" placeholder="فاصله آخرین قبض (روز)"></div>'
   .'<button class="btn">ذخیره</button></form>';
}
echo '</div>';
if(!empty($_GET['edit'])){$e2=$db->prepare('SELECT * FROM cmd_vehicles WHERE id=?');$e2->execute([(int)$_GET['edit']]);$e2=$e2->fetch();$vid=(int)$e2['id'];
  $q='?edit='.$vid;
  $claims=$db->prepare('SELECT * FROM cmd_claims WHERE vehicle_id=? ORDER BY id DESC');$claims->execute([$vid]);$claims=$claims->fetchAll();
  $owners=$db->prepare('SELECT * FROM cmd_owners WHERE vehicle_id=? ORDER BY seq');$owners->execute([$vid]);$owners=$owners->fetchAll();
  $panels=$db->prepare('SELECT * FROM cmd_panels WHERE vehicle_id=? ORDER BY id');$panels->execute([$vid]);$panels=$panels->fetchAll();
  $violations=$db->prepare('SELECT * FROM cmd_violations WHERE vehicle_id=? ORDER BY id DESC');$violations->execute([$vid]);$violations=$violations->fetchAll();
  echo '<div class="card"><b>🧾 قبض‌های بیمه و تصادف — '.h($e2['car']).' (پلاک '.h($e2['plate']).')</b>'
   .'<form method="post" style="margin-top:10px"><input type="hidden" name="what" value="claim"><input type="hidden" name="vehicle_id" value="'.$vid.'">'
   .'<div class="row"><input name="ins_date" placeholder="تاریخ (1404/06/12)">'
   .'<input name="insurer" placeholder="شرکت بیمه">'
   .'<input name="note" placeholder="شرح (ضد تصادف • بدنه)">'
   .'<input name="amount" type="number" placeholder="مبلغ (ریال)"></div>'
   .'<button class="btn">افزودن قبض</button></form>'
   .'<table style="margin-top:10px"><tr><th>#</th><th>تاریخ</th><th>بیمه</th><th>شرح</th><th>مبلغ</th><th></th></tr>';
  foreach($claims as $r) echo '<tr><td>'.$r['id'].'</td><td dir="ltr">'.h($r['ins_date']).'</td><td>'.h($r['insurer']).'</td><td>'.h($r['note']).'</td><td>'.number_format((int)$r['amount']).'</td>'
    .'<td><a class="btn small danger" href="?del=1&t=claims&id='.$r['id'].'&edit='.$vid.'" onclick="return confirm(\'حذف شود؟\')">حذف</a></td></tr>';
  echo '</table></div>';
  echo '<div class="card"><b>👤 سابقه مالکیت</b>'
   .'<form method="post" style="margin-top:10px"><input type="hidden" name="what" value="owner"><input type="hidden" name="vehicle_id" value="'.$vid.'">'
   .'<div class="row"><input name="seq" type="number" placeholder="شماره مالک (1=اول)">'
   .'<input name="own_date" placeholder="تاریخ (1405/02)">'
   .'<input name="note" placeholder="شرح (خرید از مالک اول)">'
   .'<input name="km_at" type="number" placeholder="کیلومتر در زمان خرید">'
   .'<select name="active"><option value="0">مالک قدیمی</option><option value="1">مالک فعلی</option></select></div>'
   .'<button class="btn">افزودن مالک</button></form>'
   .'<table style="margin-top:10px"><tr><th>#</th><th>دور</th><th>تاریخ</th><th>شرح</th><th>کیلومتر</th><th>وضعیت</th><th></th></tr>';
  foreach($owners as $r) echo '<tr><td>'.$r['id'].'</td><td>'.$r['seq'].'</td><td dir="ltr">'.h($r['own_date']).'</td><td>'.h($r['note']).'</td><td>'.number_format((int)$r['km_at']).'</td><td>'.($r['active']?'فعلی':'قدیمی').'</td>'
    .'<td><a class="btn small danger" href="?del=1&t=owners&id='.$r['id'].'&edit='.$vid.'" onclick="return confirm(\'حذف شود؟\')">حذف</a></td></tr>';
  echo '</table></div>';
  echo '<div class="card"><b>🛡 کارشناسی بدنه</b>'
   .'<form method="post" style="margin-top:10px"><input type="hidden" name="what" value="panel"><input type="hidden" name="vehicle_id" value="'.$vid.'">'
   .'<div class="row"><input name="panel" placeholder="نام پنل (کاپوت، بامپر جلو…)">'
   .'<select name="kind"><option value="orig">اصلی</option><option value="paint">رنگ‌دار</option><option value="repl">تعویضی</option></select></div>'
   .'<button class="btn">افزودن پنل</button></form>'
   .'<table style="margin-top:10px"><tr><th>#</th><th>پنل</th><th>وضعیت</th><th></th></tr>';
  $kindT=['orig'=>'اصلی','paint'=>'رنگ‌دار','repl'=>'تعویضی'];
  foreach($panels as $r) echo '<tr><td>'.$r['id'].'</td><td>'.h($r['panel']).'</td><td>'.h($kindT[$r['kind']]??$r['kind']).'</td>'
    .'<td><a class="btn small danger" href="?del=1&t=panels&id='.$r['id'].'&edit='.$vid.'" onclick="return confirm(\'حذف شود؟\')">حذف</a></td></tr>';
  echo '</table></div>';
  echo '<div class="card"><b>🚨 خلافی خودرو</b>'
   .'<form method="post" style="margin-top:10px"><input type="hidden" name="what" value="violation"><input type="hidden" name="vehicle_id" value="'.$vid.'">'
   .'<div class="row"><input name="vio_date" placeholder="تاریخ (1405/05/12)">'
   .'<input name="type" placeholder="نوع خلافی (تعدی از سرعت…)">'
   .'<input name="location" placeholder="محل">'
   .'<input name="amount" type="number" placeholder="مبلغ (ریال)">'
   .'<select name="status"><option value="1">پرداخت‌نشده</option><option value="2">پرداخت شده</option></select></div>'
   .'<button class="btn">افزودن خلافی</button></form>'
   .'<table style="margin-top:10px"><tr><th>#</th><th>تاریخ</th><th>نوع</th><th>محل</th><th>مبلغ</th><th>وضعیت</th><th></th></tr>';
  foreach($violations as $r) echo '<tr><td>'.$r['id'].'</td><td dir="ltr">'.h($r['vio_date']).'</td><td>'.h($r['type']).'</td><td>'.h($r['location']).'</td><td>'.number_format((int)$r['amount']).'</td><td>'.($r['status']==1?'پرداخت‌نشده':'پرداخت شده').'</td>'
    .'<td><a class="btn small danger" href="?del=1&t=violations&id='.$r['id'].'&edit='.$vid.'" onclick="return confirm(\'حذف شود؟\')">حذف</a></td></tr>';
  echo '</table></div>';
}else{
  echo '<table><tr><th>#</th><th>پلاک</th><th>خودرو</th><th>کیلومتر</th><th>بازار (میلیون)</th><th>بدنه</th><th>قبض</th><th>مالک</th><th>پنل</th><th>خلافی</th><th></th></tr>';
  foreach($vehicles as $r){
    echo '<tr><td>'.$r['id'].'</td><td dir="ltr">'.h($r['plate']).'</td><td>'.h($r['car']).'</td><td>'.number_format((int)$r['km']).'</td>'
      .'<td>'.number_format((int)$r['market']).'</td><td>'.$r['body_score'].'٪</td><td>'.$r['claims'].'</td><td>'.$r['owners'].'</td><td>'.$r['panels'].'</td><td>'.$r['vios'].'</td>'
      .'<td style="white-space:nowrap"><a class="btn small" href="?edit='.$r['id'].'">مدیریت</a> '
      .'<a class="btn small danger" href="?del=1&t=vehicles&id='.$r['id'].'" onclick="return confirm(\'خودرو و همه داده‌هایش حذف شود؟\')">حذف</a></td></tr>';
  }
  echo '</table>';
}
foot();
