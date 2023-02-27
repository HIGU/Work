<?php
//////////////////////////////////////////////////////////////////////////////
// 月次・中間・決算 組立賃率計算 メニュー                                   //
// Copyright (C) 2006-2008 Norihisa.Ohya usoumu@nitto-kohki.co.jp           //
// Changed history                                                          //
// 2006/05/23 Created   wage_rate_menu.php                                  //
// 2007/10/05 フォルダooyaを削除した為アドレスを変更                        //
// 2007/10/19 E_ALLをE_STRICTへ→コメント化                                 //
//            ショートタグを標準タグ(推奨値)へ変更                          //
// 2007/10/20 ,の後にスペースがない個所を訂正                               //
// 2007/10/22 <!DOCTYPE HTML の前に改行があるのを削除                  小林 //
// 2007/10/24 余分なスタイルシートの削除                                    //
// 2007/12/13 対象年月の受け渡し用に$requestを設定                          //
// 2007/12/29 減価償却費・組立賃率・間接費配賦率を新プログラムへリンク変更  //
// 2008/01/09 Sessionを追加戻ってきた時に日付が保持されないのを修正         //
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
$menu->set_site(10, 4);                     // site_index=10(損益メニュー) site_id=4(組立賃率計算表)
////////////// リターンアドレス設定
//$menu->set_RetUrl(INDUST_MENU);             // 通常は指定する必要はない
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('組立賃率 処理 メニュー');
//////////// 表題の設定
$menu->set_caption('組立賃率 処理 メニュー　　対象年月の指定');
//////////// 呼出先のaction名とアドレス設定
$menu->set_action('各種データ入力',  'wage_various_data_input_main.php');
$menu->set_action('減価償却費照会',  'assemblyRate_depreciationCal_Main.php');
$menu->set_action('組立賃率の照会',  'assemblyRate_reference_Main.php');
$menu->set_action('間接費配賦率の照会',  'assemblyRate_actAllocate_Main.php');

$request = new Request;
$session = new Session;

//////////// ブラウザーのキャッシュ対策用
$uniq = $menu->set_useNotCache('target');

//////////// メッセージ出力フラグ
$msg_flg = 'site';

//////////// 対象年月のセッションデータ取得
if ($request->get('wage_ym') != '') {
    $wage_ym = $request->get('wage_ym'); 
} else if ($session->get('wage_ym') != '') {
    $wage_ym = $session->get('wage_ym'); 
} else {
    $wage_ym = date('Ym');           // セッションデータがない場合の初期値(当月)
}

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
</head>
<body onLoad='document.wage_rate.wage_ym.focus()'>
    <center>
    <?php echo $menu->out_title_border() ?>
        <form name='wage_rate' action='wage_branch.php' method='post'>
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
                                    printf("<option value='%d' selected>%s年%s月</option>\n", $ym, substr($ym, 0, 4), substr($ym, 4, 2));
                                    $init_flg = 0;
                                } else {
                                    printf("<option value='%d'>%s年%s月</option>\n", $ym, substr($ym, 0, 4), substr($ym, 4, 2));
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
                    </span>
                </td>
            </tr>
            <tr>
                <td align='center' bgcolor='#ceffce' class='winbox'>
                    <input class='pt10b' type='submit' name='wage_name' value='各種データ入力'>
                </td>
                <td align='center' bgcolor='#ceffce' class='winbox'>
                    <input class='pt10b' type='submit' name='wage_name' value='減価償却費照会'>
                </td>
                <td align='center' bgcolor='#ceffce' class='winbox'>
                    <input class='pt10b' type='submit' name='wage_name' value='組立賃率の照会'>
                </td>
                <td align='center' bgcolor='#ceffce' class='winbox'>
                    <input class='pt10b' type='submit' name='wage_name' value='間接費配賦率の照会'>
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

