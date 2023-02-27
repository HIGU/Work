<?php
//////////////////////////////////////////////////////////////////////////////
// 組立賃率 各種データ入力メインメニュー wage_various_data_input_menu.php   //
// Copyright (C) 2006-2008 Norihisa.Ooya usoumu@nitto-kohki.co.jp           //
// Changed history                                                          //
// 2006/08/22 Created  wage_various_data_input_menu                         //
// 2007/09/25 不要な$Returlを削除                                           //
// 2007/10/05 フォルダooyaを削除した為アドレスを変更                        //
// 2007/10/19 E_ALLをE_STRICTへ→コメント化                                 //
//            ショートタグを標準タグ(推奨値)へ変更                          //
// 2007/10/20 ,の後にスペースがない個所を訂正                               //
// 2007/10/22 <!DOCTYPE HTML の前に改行があるのを削除                       //
// 2007/10/24 ob_end_flush()があるのにob_start()ないので追加                //
// 2007/12/13 対象年月の受け渡し用に$requestを設定                          //
// 2007/12/29 各種メニューを新プログラムへリンク変更                        //
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
$menu = new MenuHeader();                  // 認証チェック0=一般上 戻り先=TOP_MENU タイトル未設定
////////////// サイト設定
//$menu->set_site(10, 4);                     // site_index=10(損益メニュー) site_id=4(組立賃率計算表)

//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('各種データ編集');
//////////// 表題の設定
$menu->set_caption('各種データ 編集 メニュー　　対象年月の指定');
//////////// 呼出先のaction名とアドレス設定
$menu->set_action('グループマスター編集',   PL . 'kessan/wage_rate/assemblyRate_groupMaster_Main.php');
$menu->set_action('固定資産台帳編集',   PL . 'kessan/wage_rate/assemblyRate_capitalAsset_Main.php');
$menu->set_action('リース資産台帳編集',     PL . 'kessan/wage_rate/assemblyRate_leasedAsset_Main.php');
$menu->set_action('機械ワークデータ編集',     PL . 'kessan/wage_rate/assemblyRate_machineWork_Main.php');
$menu->set_action('手作業データ編集',       PL . 'kessan/wage_rate/assemblyRate_manRate_Main.php');
$menu->set_action('配賦率計算データ編集',       PL . 'kessan/wage_rate/assemblyRate_costAllocation_Main.php');
//////////// 呼出先のaction名とアドレス設定
//$menu->set_action('旧総材料費登録',   INDUST . 'material/materialCost_entry_old.php');

$request = new Request;
$session = new Session;

////////////// リターンアドレス設定
$menu->set_RetUrl($session->get('wage_referer'));             // 通常は指定する必要はない
//////////// ブラウザーのキャッシュ対策用
$uniq = $menu->set_useNotCache('target');

//////////// メッセージ出力フラグ
$msg_flg = 'site';

//////////// 対象年月のセッションデータ取得
if ($request->get('wage_ym') != '') {
    $wage_ym = $request->get('wage_ym'); 
} else {
    $wage_ym = date('Ym');           // セッションデータがない場合の初期値(当月)
}

$session->add('wage_ym', $wage_ym);

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
        <form name='data_input' action='various_data_branch.php' method='post'>
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
                    </span>
                </td>
            </tr>
            <tr>
                <td align='center' bgcolor='#ceffce' class='winbox'>
                	<input class='pt10b' type='submit' name='service_name' value='グループマスター編集'>
                </td>
                <td align='center' bgcolor='#ceffce' class='winbox'>
                    <input class='pt10b' type='submit' name='service_name' value='固定資産台帳編集'>
                </td>
                <td align='center' bgcolor='#ceffce' class='winbox'>
                    <input class='pt10b' type='submit' name='service_name' value='リース資産台帳編集'>
                </td>
             </tr>
             <tr>
                <td align='center' bgcolor='#ceffce' class='winbox'>
                    <input class='pt10b' type='submit' name='service_name' value='機械ワークデータ編集'>
                </td>
                <td align='center' bgcolor='#ceffce' class='winbox'>
                    <input class='pt10b' type='submit' name='service_name' value='手作業データ編集'>
                </td>
                <td align='center' bgcolor='#ceffce' class='winbox'>
                    <input class='pt10b' type='submit' name='service_name' value='配賦率計算データ編集'>
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

