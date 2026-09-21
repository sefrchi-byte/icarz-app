<?php
// shared admin bootstrap: session + auth guard + layout
session_start();
require __DIR__.'/../config.php';
function need_login(){
  if(empty($_SESSION['admin'])){header('Location: index.php');exit;}
}
function h($s){return htmlspecialchars((string)$s,ENT_QUOTES,'UTF-8');}
function head($title){
  echo '<!DOCTYPE html><html lang="fa" dir="rtl"><head><meta charset="utf-8">'
    .'<meta name="viewport" content="width=device-width,initial-scale=1">'
    .'<title>'.h($title).' — پنل آی‌کارز</title><style>'
    .'* {box-sizing:border-box}body{margin:0;background:#0B0F1A;color:#E8EBF3;font-family:Tahoma,Arial,sans-serif;font-size:14px}'
    .'.top{background:#11182B;border-bottom:2px solid #F2540A;padding:12px 18px;display:flex;gap:14px;align-items:center;flex-wrap:wrap}'
    .'.top b{color:#FF9D1F;font-size:17px;margin-left:auto}'
    .'.top a{color:#C9D2E8;text-decoration:none;padding:6px 12px;border-radius:8px}'
    .'.top a:hover,.top a.on{background:#1E2742;color:#fff}'
    .'.wrap{max-width:1000px;margin:0 auto;padding:18px}'
    .'table{width:100%;border-collapse:collapse;background:#141B30;border-radius:12px;overflow:hidden}'
    .'th,td{padding:9px 10px;border-bottom:1px solid #232C49;text-align:right;font-size:13px}'
    .'th{background:#1A2340;color:#FF9D1F;white-space:nowrap}tr:hover td{background:#182036}'
    .'input,select,textarea{background:#0E1428;border:1px solid #2A3560;color:#fff;border-radius:8px;padding:8px 10px;font-family:inherit;font-size:13px;width:100%}'
    .'.btn{background:linear-gradient(135deg,#FF9D1F,#F2540A);border:none;color:#fff;border-radius:9px;padding:9px 18px;font-family:inherit;font-size:14px;font-weight:700;cursor:pointer}'
    .'.btn.ghost{background:#232C49}.btn.danger{background:#B91C1C}.btn.small{padding:5px 12px;font-size:12px}'
    .'.row{display:flex;gap:10px;flex-wrap:wrap;margin-bottom:10px}.row>*{flex:1;min-width:140px}'
    .'.card{background:#141B30;border:1px solid #232C49;border-radius:12px;padding:14px;margin-bottom:12px}'
    .'.msg{background:#12331F;border:1px solid #1E9E57;color:#7DFFB2;border-radius:9px;padding:9px 12px;margin-bottom:12px}'
    .'.err{background:#3B1010;border:1px solid #B91C1C;color:#FF9B9B;border-radius:9px;padding:9px 12px;margin-bottom:12px}'
    .'.login{max-width:360px;margin:60px auto}.login h2{color:#FF9D1F;text-align:center}'
    .'.stats{display:flex;gap:10px;flex-wrap:wrap}.stat{flex:1;min-width:140px;background:#141B30;border:1px solid #232C49;border-radius:12px;padding:16px;text-align:center}'
    .'.stat b{display:block;font-size:26px;color:#FF9D1F;margin-top:6px}a{color:#FF9D1F}'
    .'</style></head><body>';
}
function nav($on=''){
  $l=['dash.php'=>'داشبورد','products.php'=>'محصولات','sellers.php'=>'فروشندگان','orders.php'=>'سفارش‌ها','cmd.php'=>'هوش خودرو','logout.php'=>'خروج'];
  echo '<div class="top"><b>⚡ پنل آی‌کارز</b>';
  foreach($l as $f=>$t) echo '<a class="'.($on===$f?'on':'').'" href="'.$f.'">'.$t.'</a>';
  echo '</div><div class="wrap">';
}
function foot(){echo '</div></body></html>';}
