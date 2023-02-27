<?php
//////////////////////////////////////////////////////////////////////////////
// 製造原価計算 部品仕掛Ｃ明細 照会                                         //
// Copyright (C) 2017-2021 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2017/09/11 Created   cost_parts_widget_view.php                          //
// 2018/07/06 原価調整が取得されてしまっているので調整(2018/05分)           //
// 2019/05/17 日付の取得方法の変更                                          //
// 2021/04/08 年月の計算が間違っていたので修正（第４四半期）                //
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

//////////// POSTデータ取得
if (isset($_REQUEST['nk_ki'])) {
    $nk_ki = $_REQUEST['nk_ki'];
} else {
    $nk_ki = $_SESSION['nk_ki'];
}
if (isset($_REQUEST['str_ym'])) {
    $str_ym = $_REQUEST['str_ym'];
} else {
    $str_ym = $_SESSION['str_ym'];
}
if (isset($_REQUEST['end_ym'])) {
    $end_ym = $_REQUEST['end_ym'];
} else {
    $end_ym = $_SESSION['end_ym'];
}
if (isset($_REQUEST['2ki_ym'])) {
    $_SESSION['2ki_ym'] = $_REQUEST['2ki_ym'];
    $session->add('2ki_ym', $_SESSION['2ki_ym']);
} elseif (isset($_SESSION['2ki_ym'])) {
    $session->add('2ki_ym', $_SESSION['2ki_ym']);
} elseif ($session->get('2ki_ym') != '') {
    $_SESSION['2ki_ym'] = $session->get('kamoku');
}

// 対象月を取得
$yyyymm   = $_SESSION['2ki_ym'];
$ki       = Ym_to_tnk($_SESSION['2ki_ym']);
$yyyy     = substr($yyyymm, 0,4);
$mm       = substr($yyyymm, 4,2);
if (($mm >= 1) && ($mm <= 3)) {
    $yyyy_t = $yyyy;
    $yyyy   = ($yyyy - 1);
    
}

$cost_ym = array();
$tuki_chk = substr($_SESSION['2ki_ym'],4,2);
if ($tuki_chk >= 1 && $tuki_chk <= 3) {           //第４四半期
    $hanki = '４';
    $cost_ym[0]  = $yyyy . '04';
    $cost_ym[1]  = $yyyy . '05';
    $cost_ym[2]  = $yyyy . '06';
    $cost_ym[3]  = $yyyy . '07';
    $cost_ym[4]  = $yyyy . '08';
    $cost_ym[5]  = $yyyy . '09';
    $cost_ym[6]  = $yyyy . '10';
    $cost_ym[7]  = $yyyy . '11';
    $cost_ym[8]  = $yyyy . '12';
    $cost_ym[9]  = $yyyy_t . '01';
    $cost_ym[10] = $yyyy_t . '02';
    $cost_ym[11] = $yyyy_t . '03';
    $cnum        = 12;
} elseif ($tuki_chk >= 4 && $tuki_chk <= 6) {     //第１四半期
    $hanki = '１';
    $cost_ym[0]  = $yyyy . '04';
    $cost_ym[1]  = $yyyy . '05';
    $cost_ym[2]  = $yyyy . '06';
    $cnum        = 3;
} elseif ($tuki_chk >= 7 && $tuki_chk <= 9) {     //第２四半期
    $hanki = '２';
    $cost_ym[0] = $yyyy . '04';
    $cost_ym[1] = $yyyy . '05';
    $cost_ym[2] = $yyyy . '06';
    $cost_ym[3] = $yyyy . '07';
    $cost_ym[4]  = $yyyy . '08';
    $cost_ym[5]  = $yyyy . '09';
    $cnum        = 6;
} elseif ($tuki_chk >= 10) {    //第３四半期
    $hanki = '３';
    $cost_ym[0]  = $yyyy . '04';
    $cost_ym[1]  = $yyyy . '05';
    $cost_ym[2]  = $yyyy . '06';
    $cost_ym[3]  = $yyyy . '07';
    $cost_ym[4]  = $yyyy . '08';
    $cost_ym[5]  = $yyyy . '09';
    $cost_ym[6]  = $yyyy . '10';
    $cost_ym[7]  = $yyyy . '11';
    $cost_ym[8]  = $yyyy . '12';
    $cnum        = 9;
}

//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
if ($tuki_chk == 3) {
    $menu->set_title("第 {$ki} 期　本決算　部　品　仕　掛　Ａ　／　Ｃ");
} else {
    $menu->set_title("第 {$ki} 期　第{$hanki}四半期　部　品　仕　掛　Ａ　／　Ｃ");
}

///////////// 合計金額を取得
// query部は共用
$query = "select
                SUM(den_kin) as t_kingaku
          from
                manufacture_cost_cal";

$t_buswa_kin = 0;
// 月毎の部品仕掛仕訳の合計金額を取得
for ($r=0; $r<$cnum; $r++) {
    // 日付の設定
    $d_start = $cost_ym[$r] . '01';
    $d_end   = $cost_ym[$r] . '99';
    $search = "where den_ki='$nk_ki' and den_ymd>=$d_start and den_ymd<=$d_end and den_cname='部品仕仕訳'";
    if ($ki == 19) {
        $search .= " and den_kin <> -19064868";
    }
    $query_s = sprintf("$query %s", $search);     // SQL query 文の完成
    $res_sum = array();
    if ($rows=getResult($query_s, $res_sum) <= 0) {
        $m_buswa_kin[$r] = 0;
    } else {
        $m_buswa_kin[$r] = $res_sum[0][0];
        $t_buswa_kin += $m_buswa_kin[$r];
    }
}

// 部品仕掛買掛の合計金額を取得
// 日付の設定
$d_start = $cost_ym[0] . '01';
$d_end   = $yyyymm . '99';
$search = "where den_ki='$nk_ki' and den_ymd>=$d_start and den_ymd<=$d_end and den_cname='部品仕買'";
$query_s = sprintf("$query %s", $search);     // SQL query 文の完成
$res_sum = array();
if ($rows=getResult($query_s, $res_sum) <= 0) {
    $t_bukai_kin = 0;
} else {
    $t_bukai_kin = $res_sum[0][0];
}

///////////// 明細を取得
// query部は共用
$query = "select
                den_ymd as 計上日,
                den_kin as 金額
          from
                manufacture_cost_cal";
                
// 部品仕掛買掛の明細を取得
// 日付の設定
$d_start = $cost_ym[0] . '01';
$d_end   = $yyyymm . '99';
$search = "where den_ki='$nk_ki' and den_ymd>=$d_start and den_ymd<=$d_end and den_cname='部品仕買'";
$query_s = sprintf("$query %s", $search);     // SQL query 文の完成
$res_bukai   = array();
$field = array();
if (($rows_bukai = getResultWithField2($query_s, $field, $res_bukai)) <= 0) {
    $rows_bukai      = 0;
    $res_bukai[0][0] = 0;
}

$rows_buswa    = array();
$buswa_mei_ym  = array();
$buswa_mei_kin = array();
// 月毎の部品仕掛仕訳の明細を取得
for ($r=0; $r<$cnum; $r++) {
    // 日付の設定
    $d_start = $cost_ym[$r] . '01';
    $d_end   = $cost_ym[$r] . '99';
    $search = "where den_ki='$nk_ki' and den_ymd>=$d_start and den_ymd<=$d_end and den_cname='部品仕仕訳'";
    if ($ki == 19) {
        $search .= " and den_kin <> -19064868";
    }
    $query_s = sprintf("$query %s", $search);     // SQL query 文の完成
    $res_buswa = array();
    $field = array();
    if (($rows_buswa[$r]=getResultWithField2($query_s, $field, $res_buswa)) <= 0) {
        $buswa_mei_ym[$r][0]  = '';
        $buswa_mei_kin[$r][0] = '';
    } else {
        for ($i=0; $i<$rows_buswa[$r]; $i++) {
            $buswa_mei_ym[$r][$i]  = $res_buswa[$i][0];
            $buswa_mei_kin[$r][$i] = $res_buswa[$i][1];
        }
    }
}

// 横列の数 年月＋買掛金
$max_cols = $cnum + 1;
// 縦行の数（最大値）
$max_rows = $rows_bukai;        // とりあえず買掛金の行数をセット
for ($r=0; $r<$cnum; $r++) {    // 各月の行数と比較
    if ($max_rows < $rows_buswa[$r]) {  // 各月の行数の方が大きければ置き換える。
        $max_rows = $rows_buswa[$r];
    }
}
// 表示用データの格納
$view_data = array();
for ($r=0; $r<$max_rows; $r++) {        // 最終行まで繰り返し
    for ($i=0; $i<$max_cols; $i++) {    // 買掛・仕訳各月をセット
        if ($i == 0) {                  // 買掛の場合
            if ($r<$rows_bukai) {       // エラー対策
                $view_data[$r][$i] = $res_bukai[$r][1];
            } else {
                $view_data[$r][$i] = '　';
            }
        } else {                        // 仕訳の場合
            if ($r<$rows_buswa[$i-1]) {   // エラー対策
                $view_data[$r][$i] = $buswa_mei_kin[$i-1][$r];    // 仕訳は月と行が逆なので注意
            } else {
                $view_data[$r][$i] = '　';
            }
        }
    }
}
/////////// HTML Header を出力してキャッシュを制御
$menu->out_html_header();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
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
                    <th class='winbox' nowrap rowspan='2'>買掛金</th>
                    <?php
                    for ($i=0; $i<$cnum; $i++) {
                    ?>
                        <th class='winbox' nowrap>仕訳</th>
                    <?php
                    }
                    ?>
                    <th class='winbox' nowrap>仕訳</th>
                </tr>
                <tr>
                    <?php
                    for ($i=0; $i<$cnum; $i++) {
                        echo "<td class='winbox' nowrap bgcolor='white' align='center'><div class='pt10b'>" . format_date6($cost_ym[$i]) . "</div></td>\n";
                    }
                    ?>
                        <td class='winbox' nowrap bgcolor='white' align='center'><div class='pt10b'>合計</div></td>
                </tr>
                <!--
                <tr>
                    <td class='winbox' nowrap bgcolor='white'><div class='pt10b'>計上日</div></td>
                    <td class='winbox' nowrap bgcolor='white'><div class='pt10b'>金額</div></td>
                    <?php
                    for ($i=0; $i<$cnum; $i++) {
                        echo "<td class='winbox' bgcolor='white'><div class='pt10b'>計上日</div></td>\n";
                        echo "<td class='winbox' bgcolor='white'><div class='pt10b'>金額</div></td>\n";
                    }
                    ?>
                    <td class='winbox' nowrap bgcolor='white'><div class='pt10b'>金額</div></td>
                </tr>
               -->
            </thead>
            <tfoot>
                <!-- 現在はフッターは何もない -->
            </tfoot>
            <tbody>
            <?php
            // 明細表示
            for ($r=0; $r<$max_rows; $r++) {
                echo "<tr>\n";
                for ($i=0; $i<$max_cols; $i++) {
                    if ($view_data[$r][$i]==0) {
                        echo "  <td class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
                    } else {
                        echo "  <td class='winbox' nowrap align='right'><span class='pt9'>" . number_format($view_data[$r][$i]) . "</span></td>\n";
                    }
                }
                echo "  <td class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
                echo "</tr>\n";
            }
            // 合計表示
            echo "<tr>\n";
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($t_bukai_kin) . "</span></td>\n";
            for ($r=0; $r<$cnum; $r++) {
                echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($m_buswa_kin[$r]) . "</span></td>\n";
            }
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($t_buswa_kin) . "</span></td>\n";
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
