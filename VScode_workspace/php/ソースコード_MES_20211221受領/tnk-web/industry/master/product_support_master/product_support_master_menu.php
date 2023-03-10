<?php
//////////////////////////////////////////////////////////////////////////////
// 生産支援品 マスターの照会・登録 メニュー                                 //
// Copyright (C) 2011-     Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2011/11/10 Created   product_support_master_menu.php                     //
//////////////////////////////////////////////////////////////////////////////
//ini_set('error_reporting', E_ALL || E_STRICT);
ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮
session_start();
require_once ('../../../function.php');        // define.php と pgsql.php を require_once している
require_once ('../../../tnk_func.php');        // TNK に依存する部分の関数を require_once している
require_once ('../../../MenuHeader.php');      // TNK 全共通 menu class
require_once ('../../../ControllerHTTP_Class.php'); // TNK 全共通 MVC Controller Class
access_log();                               // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader(0);                  // 認証チェック0=一般以上 戻り先=TOP_MENU タイトル未設定
////////////// サイト設定
//$menu->set_site(10, 4);                     // site_index=10(損益メニュー) site_id=4(組立賃率計算表)
////////////// リターンアドレス設定
//$menu->set_RetUrl(INDUST_MENU);             // 通常は指定する必要はない
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('生産支援品 マスターの照会・登録 メニュー');
//////////// 表題の設定
$menu->set_caption('生産支援品 マスターの照会・登録 処理 メニュー');
//////////// 呼出先のaction名とアドレス設定
$menu->set_action('生産支援品マスターの登録',  'product_supportMaster_Main.php');
//$menu->set_action('製品グループコードの編集',  'product_groupMaster_Main.php');
$menu->set_action('支援先の登録',  'product_support_groupMaster_Main.php');
$menu->set_action('製品グループコードの編集',  'product_groupMaster_Main2.php');
$request = new Request;
$session = new Session;

//////////// ブラウザーのキャッシュ対策用
$uniq = $menu->set_useNotCache('target');

//////////// メッセージ出力フラグ
$msg_flg = 'site';

/////////// HTML Header を出力してキャッシュを制御
$menu->out_html_header();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv='Content-Type' content='text/html; charset=EUC-JP'>
<meta http-equiv='Content-Style-Type' content='text/css'>
<meta http-equiv='Content-Script-Type' content='text/javascript'>
<title><?php echo $menu->out_title() ?></title>
<?php echo $menu->out_site_java() ?>
<?php echo $menu->out_css() ?>

<script type='text/javascript'>
<!--
function monthly_send(script_name)
{
    document.monthly_form.action = 'product_support_master_branch.php?product_master_name=' + script_name;
    document.monthly_form.submit();
}
// -->
</script>

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
<body>
    <center>
    <?php echo $menu->out_title_border() ?>
        <form name='product_master' action='product_support_master_branch.php' method='post'>
        <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <table width='100%'class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <tr>
                <td align='center' colspan='5' class='winbox'>
                    <span class='caption_font'>
                        <?php echo $menu->out_caption() . "\n" ?>
                    </span>
                </td>
            </tr>
            <tr>
                <td align='center' bgcolor='#ceffce' class='winbox'>
                    <input class='pt10b' type='submit' name='product_master_name' value='生産支援品マスターの登録'>
                </td>
                <?php if ($_SESSION['User_ID'] == '300144') { ?>
                <td align='center' bgcolor='#ceffce' class='winbox'>
                    <input class='pt10b' type='submit' name='product_master_name' value='支援先の登録'>
                </td>
                <?php } ?>
                <!--
                <td align='center' bgcolor='#ceffce' class='winbox'>
                    <input class='pt10b' type='submit' name='product_master_name' value='製品グループコードの編集'>
                </td>
                -->
            </tr>
        </table>
            </td></tr>
        </table> <!-- ダミーEnd -->
        </form>
    </center>
</body>
<?php echo $menu->out_alert_java() ?>
</html>
<?php
ob_end_flush();                 // 出力バッファをgzip圧縮 END
?>

