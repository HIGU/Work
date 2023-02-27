<?php
//////////////////////////////////////////////////////////////////////////////
// 仕切単価改定処理メニュー                                                 //
// Copyright (C) 2010 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp         //
// Changed history                                                          //
// 2010/05/13 Created   materialNew_menu.php                                //
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
$menu = new MenuHeader(0);                  // 認証チェック0=一般以上 戻り先=TOP_MENU タイトル未設定
////////////// サイト設定
//$menu->set_site(10, 4);                     // site_index=10(損益メニュー) site_id=4(組立賃率計算表)
////////////// リターンアドレス設定
//$menu->set_RetUrl(INDUST_MENU);             // 通常は指定する必要はない
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('仕切単価改定 照会・登録 メニュー');
//////////// 表題の設定
$menu->set_caption('仕切単価改定 照会・登録 メニュー');
//////////// 呼出先のaction名とアドレス設定
$menu->set_action('仕切単価影響額の照会',          'materialNewSales_form.php');
$menu->set_action('仕切掛率の登録',                'materialPartsCredit_Main.php');
$menu->set_action('カプラ仕切登録・照会',  'materialNew_Main.php');
$menu->set_action('リニア仕切登録・照会',  'materialNewLinear_Main.php');
$menu->set_action('ツール仕切登録・照会',  'materialNewTool_Main.php');

$request = new Request;
$session = new Session;

//////////// ブラウザーのキャッシュ対策用
$uniq = $menu->set_useNotCache('target');

//////////// 対象年月のセッションデータ取得
if (isset($_SESSION['ind_ym'])) {
    $ind_ym = $_SESSION['ind_ym']; 
} else {
    $ind_ym = date('Ym');        // セッションデータがない場合の初期値(当月)
}

//////////// メッセージ出力フラグ
$msg_flg = 'site';

/////////// HTML Header を出力してキャッシュを制御
$menu->out_html_header();
?>
<!DOCTYPE html>
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
    document.product_master.action = 'materialNew_branch.php?product_master_name=' + script_name;
    document.product_master.submit();
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
        <form name='product_master' action='materialNew_branch.php' method='post'>
        <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <table width='100%'class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <tr>
                <td align='center' colspan='5' class='winbox'>
                    <span class='caption_font'>
                        <?php echo $menu->out_caption() . "\n" ?>
                    </span>
                    <select name='ind_ym' class='pt11b'>
                    <?php
                        $ym = date("Ym");
                        while(1) {
                            if ($ind_ym == $ym) {
                                printf("<option value='%d' selected>%s年%s月</option>\n",$ym,substr($ym,0,4),substr($ym,4,2));
                                $init_flg = 0;
                            } else
                                printf("<option value='%d'>%s年%s月</option>\n",$ym,substr($ym,0,4),substr($ym,4,2));
                            if ($ym <= 200010)
                                break;
                            if (substr($ym,4,2)!=01) {
                                $ym--;
                            } else {
                                $ym = $ym - 100;
                                $ym = $ym + 11;
                            }
                        }
                    ?>
                    </select>
                    <span class='caption_font'>
                        末時点
                    </span>
                </td>
            </tr>
            <tr>
                <td align='center' bgcolor='#ceffce' class='winbox'>
                    <input class='pt10b' type='submit' name='product_master_name' value='カプラ仕切登録・照会'>
                </td>
                <td align='center' bgcolor='#ceffce' class='winbox'>
                    <input class='pt10b' type='submit' name='product_master_name' value='リニア仕切登録・照会'>
                </td>
                <td align='center' bgcolor='#ceffce' class='winbox'>
                    <input class='pt10b' type='submit' name='product_master_name' value='ツール仕切登録・照会'>
                </td>
            </tr>
            <tr>
                <td align='center' bgcolor='#ceffce' class='winbox'>
                    <input class='pt10b' type='submit' name='product_master_name' value='仕切掛率の登録'>
                </td>
                <td align='center' bgcolor='#ceffce' class='winbox'>
                    <input class='pt10b' type='submit' name='product_master_name' value='仕切単価影響額の照会'>
                </td>
                <td align='center' bgcolor='#ceffce' class='winbox'>
                    　
                </td>
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

