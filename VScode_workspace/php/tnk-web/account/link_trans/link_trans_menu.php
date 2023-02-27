<?php
//////////////////////////////////////////////////////////////////////////////
// 連結取引総括表メインメニュー link_trans_menu.php                         //
// Copyright (C) 2017-2017 Norihisa.Ooya usoumu@nitto-kohki.co.jp           //
// Changed history                                                          //
// 2017/10/24 Created  link_trans_menu.php                                  //
//////////////////////////////////////////////////////////////////////////////
//ini_set('error_reporting', E_ALL || E_STRICT);
ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮
session_start();
require_once ('../../function.php');        // define.php と pgsql.php を require_once している
require_once ('../../tnk_func.php');        // TNK に依存する部分の関数を require_once している
require_once ('../../MenuHeader.php');      // TNK 全共通 menu class
require_once ('../../ControllerHTTP_Class.php'); // TNK 全共通 MVC Controller Class
access_log();                               // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader();                  // 認証チェック0=一般上 戻り先=TOP_MENU タイトル未設定
////////////// サイト設定
//$menu->set_site(10, 4);                     // site_index=10(損益メニュー) site_id=4(組立賃率計算表)

//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('連結取引総括表照会');
//////////// 表題の設定
$menu->set_caption('連結取引総括表 照会 メニュー　　対象年月・取引先の指定');
//////////// 呼出先のaction名とアドレス設定
$menu->set_action('債権債務照会',   PL . 'kessan/wage_rate/assemblyRate_groupMaster_Main.php');
$menu->set_action('取引高照会',   PL . 'kessan/wage_rate/assemblyRate_capitalAsset_Main.php');
//////////// 呼出先のaction名とアドレス設定
//$menu->set_action('旧総材料費登録',   INDUST . 'material/materialCost_entry_old.php');

$request = new Request;
$session = new Session;

////////////// リターンアドレス設定
//$menu->set_RetUrl($session->get('wage_referer'));             // 通常は指定する必要はない
//////////// ブラウザーのキャッシュ対策用
$uniq = $menu->set_useNotCache('target');

//////////// メッセージ出力フラグ
$msg_flg = 'site';

//////////// 対象年月のセッションデータ取得
if ($request->get('wage_ym') != '') {
    $wage_ym = $request->get('wage_ym'); 
} elseif(isset($_POST['wage_ym'])) {
    $wage_ym = $_POST['wage_ym'];
} elseif(isset($_SESSION['wage_ym'])) {
    $wage_ym = $_SESSION['wage_ym'];
} else {
    $wage_ym = date('Ym');           // セッションデータがない場合の初期値(当月)
}

//////////// 対象年月のセッションデータ取得
if ($request->get('customer') != '') {
    $customer = $request->get('customer');
} elseif(isset($_POST['customer'])) {
    $customer = $_POST['customer'];
} elseif(isset($_SESSION['customer'])) {
    $customer = $_SESSION['customer'];
} else {
    $customer = '00001';           // セッションデータがない場合の初期値(00001:NK)
}
/////////// HTML Header を出力してキャッシュを制御
$menu->out_html_header();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv='Content-Type' content='text/html; charset=UTF-8'>
<meta http-equiv='Content-Style-Type' content='text/css'>
<meta http-equiv='Content-Script-Type' content='text/javascript'>
<title><?php echo $menu->out_title() ?></title>
<?php echo $menu->out_site_java() ?>
<?php echo $menu->out_css() ?>

<script type='text/javascript'>
<!--
function monthly_send(script_name)
{
    document.monthly_form.action = 'wage_branch.php?wage_name=' + script_name;
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
<form name='MainForm' method='post'>
    <input type='hidden' name='select' value=''>
</form>
</head>
<body>
    <center>
    <?php echo $menu->out_title_border()?>
        <form name='data_input' action='link_trans_branch.php' method='post'>
        <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <table width='100%'class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <tr>
                <td align='center' colspan='5' class='winbox'>
                    <span class='caption_font'>
                    	<?php echo $menu->out_caption() . "\n" ?>
                        <select name='wage_ym'>
                            <?php
                            $ym = date('Ym');
                            while(1) {
                                if ($wage_ym == $ym) {
                                    printf("<option value='%d' selected>%s年%s月</option>\n", $ym, substr($ym, 0, 4),substr($ym, 4, 2));
                                    $init_flg = 0;
                                } else {
                                    printf("<option value='%d'>%s年%s月</option>\n", $ym, substr($ym, 0, 4),substr($ym, 4, 2));
                                }
                                if (substr($ym, 4, 2) != 01) {
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
                        <select name='customer'>
                            <option value="00001"<?php if($customer=="00001") echo("selected"); ?>>日東工器</option>
                            <option value="00004"<?php if($customer=="00004") echo("selected"); ?>>メドテック</option>
                            <option value="00005"<?php if($customer=="00005") echo("selected"); ?>>白河ＮＫ</option>
                            <option value="00101"<?php if($customer=="00101") echo("selected"); ?>>ＮＫＩＴ</option>
                        </select>
                    </span>
                </td>
            </tr>
            <tr>
                <td align='center' bgcolor='#ceffce' class='winbox'>
                	<input class='pt10b' type='submit' name='service_name' value='債権債務照会'>
                </td>
                <td align='center' bgcolor='#ceffce' class='winbox'>
                    <input class='pt10b' type='submit' name='service_name' value='取引高照会'>
                </td>
             </tr>
        </table>
            </td></tr>
        </table> <!-- ダミーEnd -->
        </form>
    </center>
</body>
<?php echo $menu->out_alert_java()?>
</html>
<?php
ob_end_flush();                 // 出力バッファをgzip圧縮 END
?>

