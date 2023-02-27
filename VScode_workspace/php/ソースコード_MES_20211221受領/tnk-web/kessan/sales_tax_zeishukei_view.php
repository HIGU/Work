<?php
//////////////////////////////////////////////////////////////////////////////
// 月次損益関係 消費税申告書 消費税集計表                                   //
// Copyright(C) 2021-2021 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp     //
// Changed history                                                          //
// 2021/04/22 Created   sales_tax_zeishukei_view.php                        //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug 用
// ini_set('error_reporting',E_ALL);           // E_ALL='2047' debug 用
// ini_set('display_errors','1');              // Error 表示 ON debug 用 リリース後コメント
session_start();                            // ini_set()の次に指定すること Script 最上行

require_once ('../function.php');           // define.php と pgsql.php を require_once している
require_once ('../tnk_func.php');           // TNK に依存する部分の関数を require_once している
require_once ('../MenuHeader.php');         // TNK 全共通 menu class
access_log();                               // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader(0);                  // 認証チェック0=一般以上 戻り先=TOP_MENU タイトル未設定
    // 実際の認証はprofit_loss_submit.phpで行っているaccount_group_check()を使用

////////////// サイト設定
// $menu->set_site(10, 7);                     // site_index=10(損益メニュー) site_id=7(月次損益)
//////////// 表題の設定
$menu->set_caption('栃木日東工器(株)');
//////////// 呼出先のaction名とアドレス設定
// $menu->set_action('抽象化名',   PL . 'address.php');

$url_referer     = $_SESSION['pl_referer'];     // 呼出もとの URL を取得

$menu->set_action('部品仕掛Ｃ', PL . 'cost_parts_widget_view.php');
$menu->set_action('原材料', PL . 'cost_material_view.php');
$menu->set_action('部品', PL . 'cost_parts_view.php');
$menu->set_action('切粉', PL . 'cost_kiriko_view.php');

///// 対象当月
$ki2_ym   = $_SESSION['2ki_ym'];
$yyyymm   = $_SESSION['2ki_ym'];
$ki       = Ym_to_tnk($_SESSION['2ki_ym']);
$b_yyyymm = $yyyymm - 100;
$p1_ki    = Ym_to_tnk($b_yyyymm);

///// 前期末 年月の算出
$yyyy = substr($yyyymm, 0,4);
$mm   = substr($yyyymm, 4,2);
if (($mm >= 1) && ($mm <= 3)) {
    $yyyy = ($yyyy - 1);
}
$pre_end_ym = $yyyy . "03";     // 前期末年月

///// 期・半期の取得
$tuki_chk = substr($_SESSION['2ki_ym'],4,2);
if ($tuki_chk >= 1 && $tuki_chk <= 3) {           //第４四半期
    $hanki = '４';
} elseif ($tuki_chk >= 4 && $tuki_chk <= 6) {     //第１四半期
    $hanki = '１';
} elseif ($tuki_chk >= 7 && $tuki_chk <= 9) {     //第２四半期
    $hanki = '２';
} elseif ($tuki_chk >= 10) {    //第３四半期
    $hanki = '３';
}

///// 年月範囲の取得
if ($tuki_chk >= 1 && $tuki_chk <= 3) {           //第４四半期
    $str_ym = $yyyy . '04';
    $end_ym = $yyyymm;
} elseif ($tuki_chk >= 4 && $tuki_chk <= 6) {     //第１四半期
    $str_ym = $yyyy . '04';
    $end_ym = $yyyymm;
} elseif ($tuki_chk >= 7 && $tuki_chk <= 9) {     //第２四半期
    $str_ym = $yyyy . '04';
    $end_ym = $yyyymm;
} elseif ($tuki_chk >= 10) {    //第３四半期
    $str_ym = $yyyy . '04';
    $end_ym = $yyyymm;
}
///// TNK期 → NK期へ変換
$nk_ki   = $ki + 44;
$nk_p1ki = $p1_ki + 44;

//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
if ($tuki_chk == 3) {
    $menu->set_title("第 {$ki} 期　本決算　消　費　税　集　計　表");
} else {
    $menu->set_title("第 {$ki} 期　第{$hanki}四半期　消　費　税　集　計　表");
}

$cost_ym = array();
$tuki_chk = substr($_SESSION['2ki_ym'],4,2);
if ($tuki_chk >= 1 && $tuki_chk <= 3) {           //第４四半期
    $hanki = '４';
    $yyyy_tou = $yyyy + 1;
    $cost_ym[0]  = $yyyy . '04';
    $cost_ym[1]  = $yyyy . '05';
    $cost_ym[2]  = $yyyy . '06';
    $cost_ym[3]  = $yyyy . '07';
    $cost_ym[4]  = $yyyy . '08';
    $cost_ym[5]  = $yyyy . '09';
    $cost_ym[6]  = $yyyy . '10';
    $cost_ym[7]  = $yyyy . '11';
    $cost_ym[8]  = $yyyy . '12';
    $cost_ym[9]  = $yyyy_tou . '01';
    $cost_ym[10] = $yyyy_tou . '02';
    $cost_ym[11] = $yyyy_tou . '03';
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

// 未払消費税等（中間納付分）
$t_chukan_zei   = 0;

for ($i = 0; $i < $cnum; $i++) {
    $c_mm   = substr($cost_ym[$i], 4,2);
    if ($c_mm == 4) {
        $chukan_zei[$i] = 0;
    } else {
        $item_name = $cost_ym[$i] . "中間納付税額";
        $query = sprintf("select rep_kin from sales_tax_create_data where rep_ki=%d and rep_note='%s'", $nk_ki, $item_name);
        $res_in = array();
        if (getResult2($query,$res_in) <= 0) {
            /////////// begin トランザクション開始
            if ($con = db_connect()) {
                query_affected_trans($con, "begin");
            } else {
                $_SESSION["s_sysmsg"] .= "データベースに接続できません";
                exit();
            }
            /////////// commit トランザクション終了
            query_affected_trans($con, "commit");
            $chukan_zei[$i] = 0;
        } else {
            /////////// begin トランザクション開始
            if ($con = db_connect()) {
                query_affected_trans($con, "begin");
            } else {
                $_SESSION["s_sysmsg"] .= "データベースに接続できません";
                exit();
            }
            /////////// commit トランザクション終了
            query_affected_trans($con, "commit");
            $chukan_zei[$i] = $res_in[0][0];
            $t_chukan_zei  += $chukan_zei[$i];
        }
    }
}

// 未払消費税等（中間納付分）
$t_chukan_zei   = 0;

for ($i = 0; $i < $cnum; $i++) {
    $c_mm   = substr($cost_ym[$i], 4,2);
    if ($c_mm == 4) {
        $chukan_zei[$i] = 0;
    } else {
        $item_name = $cost_ym[$i] . "中間納付税額";
        $query = sprintf("select rep_kin from sales_tax_create_data where rep_ki=%d and rep_note='%s'", $nk_ki, $item_name);
        $res_in = array();
        if (getResult2($query,$res_in) <= 0) {
            /////////// begin トランザクション開始
            if ($con = db_connect()) {
                query_affected_trans($con, "begin");
            } else {
                $_SESSION["s_sysmsg"] .= "データベースに接続できません";
                exit();
            }
            /////////// commit トランザクション終了
            query_affected_trans($con, "commit");
            $chukan_zei[$i] = 0;
        } else {
            /////////// begin トランザクション開始
            if ($con = db_connect()) {
                query_affected_trans($con, "begin");
            } else {
                $_SESSION["s_sysmsg"] .= "データベースに接続できません";
                exit();
            }
            /////////// commit トランザクション終了
            query_affected_trans($con, "commit");
            $chukan_zei[$i] = $res_in[0][0];
            $t_chukan_zei  += $chukan_zei[$i];
        }
    }
}

// 仮払消費税
// query部は共用
$query = "select
                rep_de- rep_cr as t_kin
          from
                financial_report_month";

// 月毎の合計金額を取得
$t_karihara_kin = 0;

// データの取得
for ($r=0; $r<$cnum; $r++) {
    $karihara_temp = 0;
    // 日付の設定
    $d_ym = $cost_ym[$r];
    $c_mm   = substr($d_ym, 4,2);
    if ($c_mm == 9 || $c_mm == 3) {
        $query_c = "select
                        rep_cri as t_kin
                    from
                        financial_report_cal";
        $search = "where rep_ymd=$d_ym and rep_summary1='1508' and rep_summary2='00' and rep_gin='34'";
        $query_c = sprintf("$query_c %s", $search);     // SQL query 文の完成
        $res_c = array();
        if ($rows=getResult($query_c, $res_c) <= 0) {
            $karihara_temp = 0;
        } else {
            $karihara_temp = $res_c[0][0];
        }
    }
    // 仮払消費税
    $search = "where rep_ymd=$d_ym and rep_summary1='1508' and rep_summary2='00'";
    $query_s = sprintf("$query %s", $search);     // SQL query 文の完成
    $res_sum = array();
    if ($rows=getResult($query_s, $res_sum) <= 0) {
        $m_karihara_kin[$r] = 0 + $karihara_temp;
    } else {
        $m_karihara_kin[$r] = $res_sum[0][0] + $karihara_temp;
        $t_karihara_kin += $m_karihara_kin[$r];
    }
    
}

// 仮払消費税 輸入
// query部は共用
$query = "select
                rep_de as t_kin
          from
                financial_report_month";

// 月毎の合計金額を取得
$t_kariharayu_kin = 0;

// データの取得
for ($r=0; $r<$cnum; $r++) {
    $karihara_temp = 0;
    // 日付の設定
    $d_ym = $cost_ym[$r];
    // 仮払消費税輸入
    $search = "where rep_ymd=$d_ym and rep_summary1='1508' and rep_summary2='20'";
    $query_s = sprintf("$query %s", $search);     // SQL query 文の完成
    $res_sum = array();
    if ($rows=getResult($query_s, $res_sum) <= 0) {
        $m_kariharayu_kin[$r] = 0;
    } else {
        $m_kariharayu_kin[$r] = $res_sum[0][0];
        $t_kariharayu_kin += $m_kariharayu_kin[$r];
    }
    
}

// 仮受消費税
// query部は共用
$query = "select
                rep_de- rep_cr as t_kin
          from
                financial_report_month";

// 月毎の合計金額を取得
$t_kariuke_kin = 0;

// データの取得
for ($r=0; $r<$cnum; $r++) {
    $kariuke_temp = 0;
    // 日付の設定
    $d_ym = $cost_ym[$r];
    $c_mm   = substr($d_ym, 4,2);
    if ($c_mm == 9 || $c_mm == 3) {
        $query_c = "select
                        rep_cri as t_kin
                    from
                        financial_report_cal";
        $search = "where rep_ymd=$d_ym and rep_summary1='3227' and rep_summary2='00' and rep_gin='34'";
        $query_c = sprintf("$query_c %s", $search);     // SQL query 文の完成
        $res_c = array();
        if ($rows=getResult($query_c, $res_c) <= 0) {
            $kariuke_temp = 0;
        } else {
            $kariuke_temp = $res_c[0][0];
        }
    }
    // 仮受消費税
    $search = "where rep_ymd=$d_ym and rep_summary1='3227' and rep_summary2='00'";
    $query_s = sprintf("$query %s", $search);     // SQL query 文の完成
    $res_sum = array();
    if ($rows=getResult($query_s, $res_sum) <= 0) {
        $m_kariuke_kin[$r] = 0 + $kariuke_temp;
    } else {
        $m_kariuke_kin[$r] = -$res_sum[0][0] + $kariuke_temp;
        $t_kariuke_kin += $m_kariuke_kin[$r];
    }
    
}

if (isset($_POST['input_data'])) {                        // 当月データの登録
    ///////// 項目とインデックスの関連付け
    $item = array();
    $item[0]   = "仮払消費税等";
    $item[1]   = "仮払消費税等輸入";
    $item[2]   = "仮受消費税等";
    $item[3]   = "未払消費税等中間納付";
    ///////// 各データの保管
    $input_data = array();
    $input_data[0]   = $t_karihara_kin;
    $input_data[1]   = $t_kariharayu_kin;
    $input_data[2]   = $t_kariuke_kin;
    $input_data[3]   = $t_chukan_zei;
    
    insert_date($item,$nk_ki,$input_data);
}

function insert_date($item,$nk_ki,$input_data) 
{
    $num_input = count($input_data);
    for ($i = 0; $i < $num_input; $i++) {
        $query = sprintf("select rep_kin from sales_tax_create_data where rep_ki=%d and rep_note='%s'", $nk_ki, $item[$i]);
        $res_in = array();
        if (getResult2($query,$res_in) <= 0) {
            /////////// begin トランザクション開始
            if ($con = db_connect()) {
                query_affected_trans($con, "begin");
            } else {
                $_SESSION["s_sysmsg"] .= "データベースに接続できません";
                exit();
            }
            ////////// Insert Start
            $query = sprintf("insert into sales_tax_create_data (rep_ki, rep_kin, rep_note, last_date, last_user) values (%d, %d, '%s', CURRENT_TIMESTAMP, '%s')", $nk_ki, $input_data[$i], $item[$i], $_SESSION['User_ID']);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION["s_sysmsg"] .= sprintf("%sの新規登録に失敗<br> %d", $item[$i], $yyyymm);
                query_affected_trans($con, "rollback");     // transaction rollback
                exit();
            }
            /////////// commit トランザクション終了
            query_affected_trans($con, "commit");
            $_SESSION["s_sysmsg"] = sprintf("<font color='yellow'>%d 消費税申告書データ 新規 登録完了</font>",$yyyymm);
        } else {
            /////////// begin トランザクション開始
            if ($con = db_connect()) {
                query_affected_trans($con, "begin");
            } else {
                $_SESSION["s_sysmsg"] .= "データベースに接続できません";
                exit();
            }
            ////////// UPDATE Start
            $query = sprintf("update sales_tax_create_data set rep_kin=%d, last_date=CURRENT_TIMESTAMP, last_user='%s' where rep_ki=%d and rep_note='%s'", $input_data[$i], $_SESSION['User_ID'], $nk_ki, $item[$i]);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION["s_sysmsg"] .= sprintf("%sのUPDATEに失敗<br> %d", $item[$i], $yyyymm);
                query_affected_trans($con, "rollback");     // transaction rollback
                exit();
            }
            /////////// commit トランザクション終了
            query_affected_trans($con, "commit");
            $_SESSION["s_sysmsg"] = sprintf("<font color='yellow'>%d 消費税申告書データ 変更 完了</font>",$yyyymm);
        }
    }
    $_SESSION["s_sysmsg"] .= "消費税申告書のデータを登録しました。";
}

/////////// HTML Header を出力してキャッシュを制御
$menu->out_html_header();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<title><?= $menu->out_title() ?></title>
<?= $menu->out_site_java() ?>
<?= $menu->out_css() ?>
<?= $menu->out_jsBaseClass() ?>
<script type=text/javascript language='JavaScript'>
<!--
function data_input_click(obj) {
    return confirm("当月のデータを登録します。\n既にデータがある場合は上書きされます。");
}
// -->
</script>
<style type='text/css'>
<!--
.pt10b {
    font-size:      11pt;
    font-weight:    bold;
    font-family:    monospace;
    color:          black;
}
.pt11b {
    font-size:      12pt;
    font-weight:    bold;
    font-family:    monospace;
}
th {
    background-color:   #ffffff;
    color:              blue;
    font:bold           12pt;
    font-family:        monospace;
}
-->
</style>
</head>
<body>
    <center>
<?= $menu->out_title_border() ?>
        <?php
            //  bgcolor='#ceffce' 黄緑
            //  bgcolor='#ffffc6' 薄い黄色
            //  bgcolor='#d6d3ce' Win グレイ
        ?>
        <!--------------- ここから本文の表を表示する -------------------->
        <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <table class='winbox_field' width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <thead>
                <!-- テーブル ヘッダーの表示 -->
                <tr>
                    <th class='winbox' nowrap>年月</th>
                    <th class='winbox' nowrap>仮払消費税等</th>
                    <th class='winbox' nowrap>仮払消費税等<BR>（輸入）</th>
                    <th class='winbox' nowrap>仮受消費税等</th>
                    <th class='winbox' nowrap>未払消費税等<BR>（中間納付分）</th>
                </tr>
            </thead>
            <tfoot>
                <!-- 現在はフッターは何もない -->
            </tfoot>
            <tbody>
            <?php
            for ($i=0; $i<$cnum; $i++) {
            
            echo "<tr>\n";
            // 年月
            echo "<td class='winbox' nowrap bgcolor='white' align='center'><div class='pt10b'>" . format_date6($cost_ym[$i]) . "</div></td>\n";
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($m_karihara_kin[$i]) . "</span></td>\n";
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($m_kariharayu_kin[$i]) . "</span></td>\n";
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($m_kariuke_kin[$i]) . "</span></td>\n";
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($chukan_zei[$i]) . "</span></td>\n";
            echo "</tr>\n";
            }
            
            echo "<tr>\n";
            // 年月
            echo "<td class='winbox' nowrap bgcolor='white' align='center'><div class='pt10b'>合計</div></td>\n";
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($t_karihara_kin) . "</span></td>\n";
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($t_kariharayu_kin) . "</span></td>\n";
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($t_kariuke_kin) . "</span></td>\n";
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($t_chukan_zei) . "</span></td>\n";
            echo "</tr>\n";
            ?>
            </tbody>
        </table>
            </td></tr>
        </table> <!----------------- ダミーEnd ------------------>
        
        <form method='post' action='<?php echo $menu->out_self() ?>'>
            <input class='pt10b' type='submit' name='input_data' value='登録' onClick='return data_input_click(this)'>
        </form>
    </center>
</body>
</html>
