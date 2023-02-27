<?php
////////////////////////////////////////////////////////////////////////////////
// 機械稼働管理指示メンテナンス                                               //
//                                               MVC View 部 リスト表示(Main) //
// Copyright (C) 2021-2021 Ryota.waki ryota_waki@nitto-kohki.co.jp            //
// Changed history                                                            //
// 2021/03/24 Created monitoring_ViewMain.php                                 //
// 2021/03/24 Release.                                                        //
////////////////////////////////////////////////////////////////////////////////
require_once ('../../function.php');    // TNK 全共通 function
require_once ('../../MenuHeader.php');  // TNK 全共通 menu class

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader();               // 認証チェックも行っている

//////////// フレームの呼出先のアクション(frame)名とアドレス設定
$menu->set_frame('Header', EQUIP2 . 'monitoring/monitoring_ViewHeader.php');
$menu->set_frame('List'  , EQUIP2 . 'monitoring/monitoring_ViewList.php');
// フレーム版は $menu->set_action()ではなく$menu->set_frame()を使用する

/////////// HTML Header を出力してキャッシュを制御
$menu->out_html_header();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN" 
    "http://www.w3.org/TR/html4/frameset.dtd">
<html>
<head>
<title><?php echo $menu->out_title() ?></title>
</head>

<frameset rows='155,*'>
    <frame src= '<?= $menu->out_frame('Header'), "?{$_SERVER['QUERY_STRING']}", "&mode=Header" ?>' name='Header' scrolling='no'>
    <frame src= '<?= $menu->out_frame('List'), "?{$_SERVER['QUERY_STRING']}" ?>' name='List'>
</frameset>
</html>
<?php ob_end_flush();   // 出力バッファをgzip圧縮 END ?>
