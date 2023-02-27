<?php
//////////////////////////////////////////////////////////////////////////////
// 設備製作部品仕掛C伝票 照会                                               //
// Copyright (C) 2018-2018 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2018/10/29 Created   machine_production_view.php                         //
//            照会した期の年間データを取得、四半期は無視                    //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
// ini_set('zend.ze1_compatibility_mode', '1');    // zend 1.X コンパチ php4の互換モード
// ini_set('implicit_flush', 'off');           // echo print で flush させない(遅くなるため) CLI CGI版
// ini_set('max_execution_time', 1200);        // 最大実行時間=20分 CLI CGI版
ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮
session_start();                            // ini_set()の次に指定すること Script 最上行

require_once ('../function.php');            // define.php と pgsql.php を require_once している
require_once ('../tnk_func.php');            // TNK に依存する部分の関数を require_once している
require_once ('../MenuHeader.php');          // TNK 全共通 menu class
require_once ('../ControllerHTTP_Class.php');// TNK 全共通 MVC Controller Class
//////////// セッションのインスタンスを登録
$session = new Session();
if (isset($_REQUEST['recNo'])) {
    $session->add_local('recNo', $_REQUEST['recNo']);
    exit();
}
access_log();                               // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader(0);                  // 認証チェック0=一般以上 戻り先=TOP_MENU タイトル未設定

////////////// サイト設定
//$menu->set_site( 1, 11);                    // site_index=01(売上メニュー) site_id=11(売上実績明細)
////////////// リターンアドレス設定
//$menu->set_RetUrl(SYS_MENU);                // 通常は指定する必要はない
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
//$menu->set_title('売 上 明 細 照 会');
//////////// 呼出先のaction名とアドレス設定

//////////// ブラウザーのキャッシュ対策用
$uniq = $menu->set_useNotCache('target');

////// 対象当月
$ki2_ym   = 202211;
$yyyymm   = 202211;
$ki       = 22;
$b_yyyymm = $yyyymm - 100;
$p1_ki    = 21;

///// 前期末 年月の算出
$yyyy = substr($yyyymm, 0,4);
$mm   = substr($yyyymm, 4,2);
if (($mm >= 1) && ($mm <= 3)) {
    $yyyy = ($yyyy - 1);
}
$pre_end_ym = $yyyy . "03";     // 前期末年月

///// TNK期 → NK期へ変換
$nk_ki = $ki + 44;

$cost_ym = array();
$cost_ym[0]  = $ki+1999 . '04';
$cost_ym[1]  = $ki+1999 . '05';
$cost_ym[2]  = $ki+1999 . '06';
$cost_ym[3]  = $ki+1999 . '07';
$cost_ym[4]  = $ki+1999 . '08';
$cost_ym[5]  = $ki+1999 . '09';
$cost_ym[6]  = $ki+1999 . '10';
$cost_ym[7]  = $ki+1999 . '11';
$cost_ym[8]  = $ki+1999 . '12';
$cost_ym[9]  = $ki+2000 . '01';
$cost_ym[10] = $ki+2000 . '02';
$cost_ym[11] = $ki+2000 . '03';
$cnum        = 12;

$tuki_chk   = 12;

//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title("第 {$ki} 期　設備製作伝票集計");


/////////// HTML Header を出力してキャッシュを制御
$menu->out_html_header();
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<title><?php echo $menu->out_title() ?></title>
<?php echo $menu->out_site_java()?>
<?php echo $menu->out_css()?>
<?php echo $menu->out_jsBaseClass() ?>

<script type='text/javascript' language='JavaScript'>
<!--
/* 初期入力フォームのエレメントにフォーカスさせる */
function set_focus(){
    // document.body.focus();                          // F2/F12キーで戻るための対応
    // document.form_name.element_name.select();
}
// -->
</script>

<!-- スタイルシートのファイル指定をコメント HTMLタグ コメントは入れ子に出来ない事に注意
<link rel='stylesheet' href='<?php echo MENU_FORM . '?' . $uniq ?>' type='text/css' media='screen'>
-->

<style type="text/css">
<!--
.pt8 {
    font-size:      8pt;
    font-family:    monospace;
}
.pt9 {
    font-size:      9pt;
    font-family:    monospace;
}
.pt10 {
    font-size:l     10pt;
    font-family:    monospace;
}
.pt10b {
    font-size:      10pt;
    font-weight:    bold;
    font-family:    monospace;
}
.pt11b {
    font-size:      11pt;
    font-weight:    bold;
    font-family:    monospace;
}
.pt12b {
    font-size:      12pt;
    font-weight:    bold;
    font-family:    monospace;
}
th {
    background-color:   yellow;
    color:              blue;
    font-size:          10pt;
    font-weight:        bold;
    font-family:        monospace;
}
.winbox {
    border-style: solid;
    border-width: 1px;
    border-top-color:       #FFFFFF;
    border-left-color:      #FFFFFF;
    border-right-color:     #bdaa90;
    border-bottom-color:    #bdaa90;
    /* background-color:#d6d3ce; */
}
.winbox_field {
    border-style: solid;
    border-width: 1px;
    border-top-color:       #bdaa90;
    border-left-color:      #bdaa90;
    border-right-color:     #FFFFFF;
    border-bottom-color:    #FFFFFF;
    /* background-color:#d6d3ce; */
}
a:hover {
    background-color:   blue;
    color:              white;
}
a {
    color:   blue;
}
body {
    background-image:url(<?php echo IMG ?>t_nitto_logo4.png);
    background-repeat:no-repeat;
    background-attachment:fixed;
    background-position:right bottom;
}
-->
</style>
</head>
<body onLoad='set_focus()'>
    <center>
<?php echo $menu->out_title_border()?>
        
        <!--------------- ここから本文の表を表示する -------------------->
        <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <table class='winbox_field' width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <thead>
                <!-- テーブル ヘッダーの表示 -->
                <tr>
                    <td class='winbox' nowrap bgcolor='white' align='center'><div class='pt10b'>管理No.</div></td>
                    <?php
                    for ($i=0; $i<$cnum; $i++) {
                        echo "<td class='winbox' nowrap bgcolor='white' align='center'><div class='pt10b'>" . format_date6($cost_ym[$i]) . "</div></td>\n";
                    }
                    ?>
                    <th class='winbox' nowrap align='center'><span class='pt10b'>合計</span></td>
                </tr>
            </thead>
            <tfoot>
                <!-- 現在はフッターは何もない -->
            </tfoot>
            <tbody>
            <?php
            // 明細表示

            $rows = 12; 
            $res = array(
                [1],
                [2],
                [3],
                [4],
                [5],
                [6],
                [7],
                [8],
                [9],
                [10],
                [12],
                [12]
            );
            $data = array(
                [100 , 100 , 100 , 100 , 100 , 100 , 100 , 100 , 100 , 100, 100 , 100],
                [200 , 200 , 200 , 200 , 200 , 200 , 200 , 200 , 200 , 200, 200 , 200],
                [300 , 300 , 300 , 300 , 300 , 300 , 300 , 300 , 300 , 300, 300 , 300],
                [400 , 400 , 400 , 400 , 400 , 400 , 400 , 400 , 400 , 400, 400 , 400],
                [500 , 500 , 500 , 500 , 500 , 500 , 500 , 500 , 500 , 500, 500 , 500],
                [600 , 600 , 600 , 600 , 600 , 600 , 600 , 600 , 600 , 600, 600 , 600],
                [700 , 700 , 700 , 700 , 700 , 700 , 700 , 700 , 700 , 700, 700 , 700],
                [800 , 800 , 800 , 800 , 800 , 800 , 800 , 800 , 800 , 800, 800 , 800],
                [900 , 900 , 900 , 900 , 900 , 900 , 900 , 900 , 900 , 900, 900 , 900],
                [1000, 1000, 1000, 1000, 1000, 1000, 1000, 1000, 1000, 1000, 1000, 1000],
                [1100, 1100, 1100, 1100, 1100, 1100, 1100, 1100, 1100, 1100, 1100, 1100],
                [1200, 1200, 1200, 1200, 1200, 1200, 1200, 1200, 1200, 1200, 1200, 1200],
            );
            $total_rows = array(1000, 2000, 3000, 4000, 5000, 6000, 7000, 8000, 9000, 10000, 11000, 12000);
            $total_cols = array(78000, 78000, 78000, 78000, 78000, 78000, 78000, 78000, 78000, 78000, 78000, 78000);
            $total_all = 936000;

            for ($r=0; $r<$rows; $r++) {
                echo "<tr>\n";
                echo "  <td class='winbox' nowrap align='right'><span class='pt9'>" . $res[$r][0] . "</span></td>\n";
                for ($i=0; $i<$cnum; $i++) {
                    if ($data[$r][$i]==0) {
                        echo "  <td class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
                    } else {
                        echo "  <td class='winbox' nowrap align='right'><span class='pt9'>" . number_format($data[$r][$i]) . "</span></td>\n";
                    }
                }
                echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($total_rows[$r]) . "</span></td>\n";
                echo "</tr>\n";
            }
            // 合計表示
            echo "<tr>\n";
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>合計</span></td>\n";
            for ($r=0; $r<$cnum; $r++) {
                echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($total_cols[$r]) . "</span></td>\n";
            }
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($total_all) . "</span></td>\n";
            echo "</tr>\n";
            ?>
            
            </tbody>
        </table>
            </td></tr>
        </table> <!----------------- ダミーEnd ------------------>
    </center>
</body>
<?php echo $menu->out_alert_java()?>
</html>
<?php
// ob_end_flush();                 // 出力バッファをgzip圧縮 END
?>
