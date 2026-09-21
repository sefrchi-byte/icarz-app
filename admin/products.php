<?php
require __DIR__.'/_inc.php';
need_login();
$db=pdo();$msg='';
if(isset($_GET['del'])){
  $db->prepare('DELETE FROM products WHERE id=?')->execute([(int)$_GET['del']]);
  header('Location: products.php');exit;
}
if($_SERVER['REQUEST_METHOD']==='POST'){
  $f=['brand','name','cat','for_car','price','old','stock','pct','rating','sold','warranty','seller_id','hot','grad','label'];
  $v=[];foreach($f as $k) $v[$k]=$_POST[$k]??'';
  foreach(['price','old','stock','pct','seller_id'] as $k) $v[$k]=(int)$v[$k];
  if(!empty($_POST['id'])){
    $db->prepare('UPDATE products SET brand=?,name=?,cat=?,for_car=?,price=?,old=?,stock=?,pct=?,rating=?,sold=?,warranty=?,seller_id=?,hot=?,grad=?,label=? WHERE id=?')
      ->execute([$v['brand'],$v['name'],$v['cat'],$v['for_car'],$v['price'],$v['old'],$v['stock'],$v['pct'],$v['rating'],$v['sold'],$v['warranty'],$v['seller_id'],$v['hot'],$v['grad'],$v['label'],(int)$_POST['id']]);
    $msg='ویرایش شد ✅';
  }else{
    $db->prepare('INSERT INTO products (brand,name,cat,for_car,price,old,stock,pct,rating,sold,warranty,seller_id,hot,grad,label) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)')
      ->execute([$v['brand'],$v['name'],$v['cat'],$v['for_car'],$v['price'],$v['old'],$v['stock'],$v['pct'],$v['rating'],$v['sold'],$v['warranty'],$v['seller_id'],$v['hot'],$v['grad'],$v['label']]);
    $msg='اضافه شد ✅';
  }
}
$edit=null;
if(isset($_GET['edit'])){$st=$db->prepare('SELECT * FROM products WHERE id=?');$st->execute([(int)$_GET['edit']]);$edit=$st->fetch();}
$rows=$db->query('SELECT p.*,s.name sname FROM products p LEFT JOIN sellers s ON s.id=p.seller_id ORDER BY p.id')->fetchAll();
head('محصولات');nav('products.php');
if($msg) echo '<div class="msg">'.$msg.'</div>';
echo '<div class="card"><b>'.($edit?'ویرایش محصول #'.$edit['id']:'➕ محصول جدید').'</b>'
  .'<form method="post" style="margin-top:10px">'.($edit?'<input type="hidden" name="id" value="'.$edit['id'].'">':'')
  .'<div class="row"><input name="brand" placeholder="برند" value="'.h($edit['brand']??'').'">'
  .'<input name="name" placeholder="نام محصول *" value="'.h($edit['name']??'').'" required></div>'
  .'<div class="row"><select name="cat">'
  .'<option value="car"'.(($edit['cat']??'')==='car'?' selected':'').'>سواری</option>'
  .'<option value="moto"'.(($edit['cat']??'')==='moto'?' selected':'').'>موتور</option>'
  .'<option value="truck"'.(($edit['cat']??'')==='truck'?' selected':'').'>سنگین</option></select>'
  .'<input name="for_car" placeholder="مناسب برای" value="'.h($edit['for_car']??'').'"></div>'
  .'<div class="row"><input name="price" type="number" placeholder="قیمت" value="'.h($edit['price']??'').'">'
  .'<input name="old" type="number" placeholder="قیمت قبل (تخفیف)" value="'.h($edit['old']??'').'">'
  .'<input name="stock" type="number" placeholder="موجودی" value="'.h($edit['stock']??10).'">'
  .'<input name="pct" type="number" placeholder="درصد نوار" value="'.h($edit['pct']??'').'"></div>'
  .'<div class="row"><input name="rating" placeholder="امتیاز (۴.۸)" value="'.h($edit['rating']??'').'">'
  .'<input name="sold" placeholder="فروش (۷۲۰)" value="'.h($edit['sold']??'').'">'
  .'<input name="warranty" placeholder="گارانتی" value="'.h($edit['warranty']??'').'">'
  .'<input name="seller_id" type="number" placeholder="ID فروشنده" value="'.h($edit['seller_id']??1).'"></div>'
  .'<div class="row"><select name="hot"><option value="">— برچسب —</option>'
  .'<option'.(($edit['hot']??'')==='hot'?' selected':'').'>hot</option>'
  .'<option'.(($edit['hot']??'')==='amaz'?' selected':'').'>amaz</option></select>'
  .'<input name="grad" placeholder="گرادیان کارت (g)" dir="ltr" value="'.h($edit['grad']??'').'">'
  .'<input name="label" placeholder="گرادیان لیبل (lbl)" dir="ltr" value="'.h($edit['label']??'').'"></div>'
  .'<button class="btn">ذخیره</button> '.($edit?'<a class="btn ghost" href="products.php">انصراف</a>':'').'</form></div>';
echo '<table><tr><th>#</th><th>نام</th><th>قیمت</th><th>موجودی</th><th>فروشنده</th><th></th></tr>';
foreach($rows as $r){
  echo '<tr><td>'.$r['id'].'</td><td>'.h($r['name']).'</td><td>'.number_format($r['price']).'</td><td>'.$r['stock'].'</td><td>'.h($r['sname']??'').'</td>'
    .'<td style="white-space:nowrap"><a class="btn small" href="?edit='.$r['id'].'">ویرایش</a> <a class="btn small danger" href="?del='.$r['id'].'" onclick="return confirm(\'حذف شود؟\')">حذف</a></td></tr>';
}
echo '</table>';
foot();
