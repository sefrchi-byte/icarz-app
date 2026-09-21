<?php
// iCarz JSON API for the front app
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin:*');
header('Access-Control-Allow-Methods:GET,POST,OPTIONS');
header('Access-Control-Allow-Headers:Content-Type');
if(($_SERVER['REQUEST_METHOD']??'')==='OPTIONS'){http_response_code(204);exit;}
require __DIR__.'/config.php';
$action=$_GET['action']??'';
if($action==='vin'){
  $vin=strtoupper(preg_replace('/[^A-Z0-9]/','',substr($_GET['code']??'',0,20)));
  if(strlen($vin)<11){http_response_code(422);echo json_encode(['ok'=>false,'error'=>'bad-vin'],JSON_UNESCAPED_UNICODE);exit;}
  try{$db=pdo();}catch(Exception $e){echo json_encode(['ok'=>false,'error'=>'db'],JSON_UNESCAPED_UNICODE);exit;}
  $st=$db->prepare('SELECT * FROM vin_reports WHERE vin=?');$st->execute([$vin]);$r=$st->fetch();
  if(!$r){echo json_encode(['ok'=>true,'found'=>false],JSON_UNESCAPED_UNICODE);exit;}
  $score=($r['score']===null||$r['score']==='')?null:(int)$r['score'];
  echo json_encode(['ok'=>true,'found'=>true,'report'=>[
    'id'=>(int)$r['id'],'vin'=>$r['vin'],'plate'=>$r['plate'],'car'=>$r['car'],
    'km'=>(int)$r['km'],'model_year'=>(int)$r['model_year'],'owner_count'=>(int)$r['owner_count'],
    'score'=>$score,'paint'=>json_decode($r['paint_json'],true)?:[],
    'repairs'=>json_decode($r['repairs_json'],true)?:[],
    'price'=>(int)$r['price'],'note'=>$r['note'],'src'=>$r['src'],'created_at'=>$r['created_at']
  ]],JSON_UNESCAPED_UNICODE);exit;
}
if($action==='vinAdd'&&$_SERVER['REQUEST_METHOD']==='POST'){
  $in=json_decode(file_get_contents('php://input'),true);
  if(!is_array($in)||!defined('INGEST_KEY')||(string)($in['key']??'')!==INGEST_KEY){http_response_code(403);echo json_encode(['ok'=>false,'error'=>'key'],JSON_UNESCAPED_UNICODE);exit;}
  $vin=strtoupper(preg_replace('/[^A-Z0-9]/','',substr((string)($in['vin']??''),0,20)));
  if(strlen($vin)<11){http_response_code(422);echo json_encode(['ok'=>false,'error'=>'bad-vin'],JSON_UNESCAPED_UNICODE);exit;}
  $cl=function($x,$max){if($x===null||$x==='')return null;$n=(int)$x;return max(0,min($max,$n));};
  $paint=is_array($in['paint']??null)?json_encode(array_slice($in['paint'],0,11),JSON_UNESCAPED_UNICODE):null;
  $repairs=is_array($in['repairs']??null)?json_encode(array_slice($in['repairs'],0,20),JSON_UNESCAPED_UNICODE):null;
  $db=pdo();
  $st=$db->prepare('INSERT INTO vin_reports (vin,plate,car,km,model_year,owner_count,score,paint_json,repairs_json,price,note,src)
    VALUES(?,?,?,?,?,?,?,?,?,?,?,?)
    ON DUPLICATE KEY UPDATE plate=VALUES(plate),car=VALUES(car),km=VALUES(km),model_year=VALUES(model_year),
    owner_count=VALUES(owner_count),score=VALUES(score),paint_json=VALUES(paint_json),repairs_json=VALUES(repairs_json),
    price=VALUES(price),note=VALUES(note),src=VALUES(src)');
  $st->execute([$vin,substr((string)($in['plate']??''),0,20),substr((string)($in['car']??''),0,120),
    max(0,(int)($in['km']??0)),((int)($in['model_year']??0))>0?max(1300,min(2100,(int)$in['model_year'])):0,
    max(0,min(30,(int)($in['owner_count']??0))),$cl($in['score']??null,97),
    $paint,$repairs,max(0,(int)($in['price']??0)),substr((string)($in['note']??''),0,400),substr((string)($in['src']??'site'),0,30)]);
  $id=(int)$db->lastInsertId();
  echo json_encode(['ok'=>true,'id'=>$id?:null],JSON_UNESCAPED_UNICODE);exit;
}
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
  }elseif($action==='cmd'){
    $plate=substr($_GET['plate']??'',0,30);
    if($plate){
      $st=$db->prepare('SELECT * FROM cmd_vehicles WHERE plate=?');$st->execute([$plate]);$v=$st->fetch();
    }else{
      $v=$db->query('SELECT * FROM cmd_vehicles ORDER BY id LIMIT 1')->fetch();
    }
    if(!$v){
      http_response_code(404);
      echo json_encode(['ok'=>false,'error'=>'no vehicle'],JSON_UNESCAPED_UNICODE);
    }else{
      $vid=(int)$v['id'];
      $cl=$db->prepare('SELECT * FROM cmd_claims WHERE vehicle_id=? ORDER BY ins_date DESC, id DESC');$cl->execute([$vid]);
      $clm=$cl->fetchAll();
      $ow=$db->prepare('SELECT * FROM cmd_owners WHERE vehicle_id=? ORDER BY seq DESC');$ow->execute([$vid]);
      $own=$ow->fetchAll();
      $pn=$db->prepare("SELECT kind,COUNT(*) c FROM cmd_panels WHERE vehicle_id=? GROUP BY kind");$pn->execute([$vid]);
      $pc=['orig'=>0,'paint'=>0,'repl'=>0];
      foreach($pn->fetchAll() as $x) $pc[$x['kind']]=(int)$x['c'];
      $pt=(int)$db->query('SELECT COUNT(*) FROM cmd_panels WHERE vehicle_id='.$vid)->fetchColumn();
      $vi=$db->prepare('SELECT * FROM cmd_violations WHERE vehicle_id=? ORDER BY id DESC');$vi->execute([$vid]);
      $vils=[];$vu=0;$va=0;
      foreach($vi->fetchAll() as $r){$stt=(int)$r['status'];$vils[]=['id'=>(int)$r['id'],'date'=>$r['vio_date'],'type'=>$r['type'],'location'=>$r['location'],'amount'=>(int)$r['amount'],'status'=>$stt];if($stt==1){$vu++;$va+=(int)$r['amount'];}}
      echo json_encode(['ok'=>true,
        'vehicle'=>['id'=>$vid,'plate'=>$v['plate'],'car'=>$v['car'],'km'=>(int)$v['km'],
          'market'=>(int)$v['market'],'delta'=>(int)$v['delta'],'body'=>(int)$v['body_score'],
          'lastClaimDays'=>(int)$v['last_claim_days']],
        'claims'=>array_map(function($r){return ['id'=>(int)$r['id'],'date'=>$r['ins_date'],'insurer'=>$r['insurer'],'note'=>$r['note'],'amount'=>(int)$r['amount']];},$clm),
        'owners'=>array_map(function($r){return ['id'=>(int)$r['id'],'seq'=>(int)$r['seq'],'date'=>$r['own_date'],'note'=>$r['note'],'km'=>(int)$r['km_at'],'active'=>(int)$r['active']];},$own),
        'panels'=>['orig'=>$pc['orig'],'paint'=>$pc['paint'],'repl'=>$pc['repl'],'total'=>$pt],
        'violations'=>$vils,'vio_unpaid'=>$vu,'vio_amount'=>$va,
      ],JSON_UNESCAPED_UNICODE);
    }
  }elseif($action==='vioPay'&&$_SERVER['REQUEST_METHOD']==='POST'){
    $in=json_decode(file_get_contents('php://input'),true)?:[];
    $st=$db->prepare('SELECT id FROM cmd_vehicles WHERE plate=?');$st->execute([substr($in['plate']??'',0,30)]);$vid=(int)$st->fetchColumn();
    if(!$vid){http_response_code(404);echo json_encode(['ok'=>false,'error'=>'no vehicle'],JSON_UNESCAPED_UNICODE);}
    else{
      if(!empty($in['all'])){$db->prepare('UPDATE cmd_violations SET status=2 WHERE vehicle_id=? AND status=1')->execute([$vid]);}
      else{$db->prepare('UPDATE cmd_violations SET status=2 WHERE vehicle_id=? AND id=?')->execute([$vid,(int)($in['id']??0)]);}
      echo json_encode(['ok'=>true],JSON_UNESCAPED_UNICODE);
    }
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
