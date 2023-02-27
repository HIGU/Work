<?php
//////////////////////////////////////////////////////////////////////////////
// 月次・中間・決算 サービス割合 メニュー                                   //
// Copyright (C) 2003-2005 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2003/10/17 Created   service_percentage_menu.php                         //
//            サービス割合の入力(各部門で)と照会及び製造経費の配賦処理      //
// 2003/10/24 年月の指定に決算月を追加(3月と9月) 例：200309決算             //
// 2003/10/28 当月分から入力出来るように変更                                //
// 2003/10/30 対象年月のセッションデータが無い場合に前月→当月へ変更        //
// 2005/08/25 MenuHeader class を使用して共通メニュー化及び認証方式へ変更   //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 
ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮
session_start();                            // ini_set()の次に指定すること Script 最上行
require_once ('../../function.php');        // define.php と pgsql.php を require_once している
require_once ('../../tnk_func.php');        // TNK に依存する部分の関数を require_once している
require_once ('../../MenuHeader.php');      // TNK 全共通 menu class
access_log();                               // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader(0);                  // 認証チェック0=一般以上 戻り先=TOP_MENU タイトル未設定

////////////// サイト設定
$menu->set_site(10, 5);                     // site_index=10(損益メニュー) site_id=5(サービス割合メニュー)
////////////// リターンアドレス設定(絶対指定する場合)
// $menu->set_RetUrl(SYS_MENU);                // 通常は指定する必要はない
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('サービス割合 処理 メニュー');
//////////// 表題の設定
$menu->set_caption('サービス割合 処理 メニュー　　対象年月の指定');
//////////// 呼出先のaction名とアドレス設定
$menu->set_action('サービス割合入力',   PL . 'service/service_category_select.php?exec=entry');
$menu->set_action('サービス割合照会',   PL . 'service/service_category_select.php?exec=view');
$menu->set_action('割合 全体 照会',     PL . 'service/service_percent_view_total.php');
$menu->set_action('製造経費の配賦',     PL . 'service/service_percent_act_allo.php');
$menu->set_action('マスター編集',       PL . 'service/service_item_master_mnt.php');
$menu->set_action('予測配賦率算定用',   PL . 'service/service_percent_act_allo_plan.php');
$menu->set_action('月次確定処理',       PL . 'service/service_final_set.php?set');
$menu->set_action('月次確定解除',       PL . 'service/service_final_set.php?unset');
$menu->set_action('製造経費配賦仮締',   PL . 'service/service_percent_act_allo_kari.php');

//////////// ブラウザーのキャッシュ対策用
$uniq = $menu->set_useNotCache('target');

//////////// 対象年月のセッションデータ取得
if (isset($_SESSION['service_ym'])) {
    $service_ym = $_SESSION['service_ym']; 
} else {
    $service_ym = date('Ym');           // セッションデータがない場合の初期値(当月)
}

/////////// HTML Header を出力してキャッシュを制御
$menu->out_html_header();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv='Content-Type' content='text/html; charset=EUC-JP'>
<meta http-equiv='Content-Style-Type' content='text/css'>
<meta http-equiv='Content-Script-Type' content='text/javascript'>
<title><?= $menu->out_title() ?></title>
<?= $menu->out_site_java() ?>
<?= $menu->out_css() ?>

<style type='text/css'>
<!--
select {
    background-color:teal;
    color:white;
    font-size:      14pt;
    font-weight:    bold;
    font-family:    monospace;
}
.pt10b {
    font-size:      11pt;
    font-weight:    bold;
}
-->
</style>
</head>
<body onLoad='document.service.service_ym.focus()'>
    <center>
<?= $menu->out_title_border() ?>
        
        <form name='service' action='service_branch.php?id=<%=$uniq%>' method='post'>
        <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <table width='100%'class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <tr>
                <td align='center' colspan='5' class='winbox'>
                    <span class='caption_font'>
                        <?= $menu->out_caption() . "\n" ?>
                        <select name='service_ym'>
                            <?php
                            $ym = date('Ym');
                            while(1) {
                                if ($service_ym == $ym) {
                                    if ( (substr($ym,4,2) == '03') || (substr($ym,4,2) == '09') ) {
                                        printf("<option value='%s'>%s決算</option>\n",$ym . '32', $ym);
                                    }
                                    printf("<option value='%d' selected>%s年%s月</option>\n",$ym,substr($ym,0,4),substr($ym,4,2));
                                    $init_flg = 0;
                                } else {
                                    if ( (substr($ym,4,2) == '03') || (substr($ym,4,2) == '09') ) {
                                        printf("<option value='%s'>%s決算</option>\n",$ym . '32', $ym);
                                    }
                                    printf("<option value='%d'>%s年%s月</option>\n",$ym,substr($ym,0,4),substr($ym,4,2));
                                }
                                if (substr($ym,4,2) != 01) {
                                    $ym--;      // １月でなければ月を一つマイナス
                                } else {        // １月なら
                                    $ym = $ym - 100;    // 年をマイナス
                                    $ym = $ym + 11;     // 月に11をプラス=12月にする
                                }
                                if ($ym < 200010) {
                                    break;
                                }
                            }
                            ?>
                        </select>
                    </span>
                </td>
            </tr>
            <tr>
                <td align='center' bgcolor='#ceffce' class='winbox'>
                    <input class='pt10b' type='submit' name='service_name' value='サービス割合入力'>
                </td>
                <td align='center' bgcolor='#ceffce' class='winbox'>
                    <input class='pt10b' type='submit' name='service_name' value='サービス割合照会'>
                </td>
                <td align='center' bgcolor='#ceffce' class='winbox'>
                    <input class='pt10b' type='submit' name='service_name' value='割合 全体 照会'>
                </td>
                <td align='center' bgcolor='#ceffce' class='winbox'>   <!-- #ffffc6 薄い黄色 -->
                    <input class='pt10b' type='submit' name='service_name' value='製造経費の配賦'>
                </td>
            </tr>
            <tr>
                <td align='center' bgcolor='#ffffc6' class='winbox'>
                    <input class='pt10b' type='submit' name='service_name' value='マスター編集'>
                </td> <!-- 余白 -->
                <td align='center' bgcolor='#ffffc6' class='winbox'>
                    <input class='pt10b' type='submit' name='service_name' value='月次確定処理'>
                </td> <!-- 余白 -->
                <td align='center' bgcolor='#ffffc6' class='winbox'>
                    <input class='pt10b' type='submit' name='service_name' value='月次確定解除'>
                </td> <!-- 余白 -->
                <td align='center' bgcolor='#ffffc6' class='winbox'>
                    <input class='pt10b' type='submit' name='service_name' value='予測配賦率算定用'>
                </td> <!-- 余白 -->
            </tr>
            <tr>
                <td align='center' bgcolor='#d6d3ce' colspan='3' class='winbox'>
                    &nbsp;
                </td>
                <td align='center' bgcolor='#ceffce' class='winbox'>
                    <input class='pt10b' type='submit' name='service_name' value='製造経費配賦仮締'>
                </td>
            </tr>
        </table>
            </td></tr>
        </table> <!-- ダミーEnd -->
        </form>
    </center>
</body>
<?=$menu->out_alert_java()?>
</html>
<?php
ob_end_flush();                 // 出力バッファをgzip圧縮 END
?>
