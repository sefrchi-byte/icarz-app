<?php
// iCarz JSON API for the front app
header('Content-Type: application/json; charset=utf-8');
require __DIR__.'/config.php';
$action=$_GET['action']??'';
try{
  $db=pdo();
  if($action==='products'){
    $rows=$db->query('SELECT * FROM products ORDER BY id')->fetchAll();
    $out=[];
    foreach($rows as $r){
      $out[]=['id'=>(int)$r['id'],'brand'=>$r['brand'],'name'=>$r['name'],'cat'=>$r['cat'],
        'for'=>$r['for_car'],'price'=>(int)$r['price'],'old'=>(int)$r['old'],
        'stock'=>(int)$r['stock'],'pct'=>(int)$r['pct'],'rating'=>$r['rating'],
        'sold'=>$r['sold'],'w'=>$r['warranty'],'sid'=>(int)$r['seller_id'],
        'hot'=>$r['hot'],'g'=>$r['grad'],'lbl'=>$r['label']];
    }
    echo json_encode(['ok'=>true,'products'=>$out],JSON_UNESCAPED_UNICODE);
  }elseif($action==='sellers'){
    $rows=$db->query('SELECT * FROM sellers ORDER BY id')->fetchAll();
    $out=[];
    foreach($rows as $r){
      $out[]=['id'=>(int)$r['id'],'name'=>$r['name'],'area'=>$r['area'],'dist'=>$r['dist'],
        'eta'=>$r['eta'],'rating'=>$r['rating'],'jobs'=>$r['jobs'],'fee'=>(int)$r['fee']];
    }
    echo json_encode(['ok'=>true,'sellers'=>$out],JSON_UNESCAPED_UNICODE);
  }elseif($action==='order'&&$_SERVER['REQUEST_METHOD']==='POST'){
    $in=json_decode(file_get_contents('php://input'),true)?:[];
    $st=$db->prepare('INSERT INTO orders (code,type,title,addr,time,total,status) VALUES (?,?,?,?,?,?,1)');
    $st->execute([
      substr($in['code']??'',0,30),substr($in['type']??'buy',0,20),
      substr($in['title']??'',0,220),substr($in['addr']??'',0,220),
      substr($in['time']??'',0,80),(int)($in['total']??0),
    ]);
    echo json_encode(['ok'=>true,'id'=>$db->lastInsertId()],JSON_UNESCAPED_UNICODE);
  }else{
    http_response_code(404);
    echo json_encode(['ok'=>false,'error'=>'unknown action'],JSON_UNESCAPED_UNICODE);
  }
}catch(Throwable $e){
  http_response_code(500);
  echo json_encode(['ok'=>false,'error'=>'db'],JSON_UNESCAPED_UNICODE);
}
