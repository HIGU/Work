<?php
//////////////////////////////////////////////////////////////////////////////
// 売上 状況 照会  profit_loss_sales_view.php                               //
// Copyright (C) 2018-2018 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2018/03/30 Created   profit_loss_sales_view.php（sales_view.php引用）    //
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
// $menu->set_RetUrl(SYS_MENU);                // 通常は指定する必要はない
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
///// 期・月の取得
$ki = Ym_to_tnk($_SESSION['pl_ym']);
$tuki = substr($_SESSION['pl_ym'],4,2);
///// 対象当月
$yyyymm = $_SESSION['pl_ym'];
///// 対象前月
if (substr($yyyymm,4,2)!=01) {
    $p1_ym = $yyyymm - 1;
} else {
    $p1_ym = $yyyymm - 100;
    $p1_ym = $p1_ym + 11;
}
///// 対象前々月
if (substr($p1_ym,4,2)!=01) {
    $p2_ym = $p1_ym - 1;
} else {
    $p2_ym = $p1_ym - 100;
    $p2_ym = $p2_ym + 11;
}
///// 期初年月の算出
$yyyy = substr($yyyymm, 0,4);
$mm   = substr($yyyymm, 4,2);
if (($mm >= 1) && ($mm <= 3)) {
    $yyyy = ($yyyy - 1);
}
$str_ym = $yyyy . "04";     // 期初年月

///// 日付計算
$str_ymd = $yyyymm . "01";
$end_ymd = $yyyymm . "99";

$menu->set_title("第{$ki}期　{$tuki}月度　売 上 状 況 照 会");

//////////// 呼出先のaction名とアドレス設定
$menu->set_action('総材料費照会',   INDUST . 'material/materialCost_view.php');
$menu->set_action('単価登録照会',   INDUST . 'parts/parts_cost_view.php');
$menu->set_action('総材料費履歴',   INDUST . 'material/materialCost_view_assy.php');

//////////// ブラウザーのキャッシュ対策用
$uniq = $menu->set_useNotCache('target');

$current_script  = $_SERVER['PHP_SELF'];    // 現在実行中のスクリプト名を保存

$div        = " ";
$d_start    = $str_ymd;
$d_end      = $end_ymd;
$kubun      = "";
///////////// 合計金額・件数等を取得
if ( ($div != 'S') && ($div != 'D') ) {      // Ｃ特注と標準 以外なら
    $query = "select
                    count(数量) as t_ken,
                    sum(数量) as t_kazu,
                    sum(Uround(数量*単価,0)) as t_kingaku
              from
                    hiuuri
              left outer join
                    product_support_master AS groupm
              on assyno=groupm.assy_no
              left outer join
                    miitem as m
              on assyno=m.mipn";
} else {
    $query = "select
                    count(数量) as t_ken,
                    sum(数量) as t_kazu,
                    sum(Uround(数量*単価,0)) as t_kingaku
              from
                    hiuuri
              left outer join
                    assembly_schedule as a
              on 計画番号=plan_no
              left outer join
                    product_support_master AS groupm
              on assyno=groupm.assy_no
              left outer join
                    miitem as m
              on assyno=m.mipn";
              //left outer join
              //      aden_master as aden
              //on (a.parts_no=aden.parts_no and a.plan_no=aden.plan_no)";
}
//////////// SQL where 句を 共用する
$search = "where 計上日>=$d_start and 計上日<=$d_end";
/*
if ($div == 'S') {    // Ｃ特注なら
    $search .= " and 事業部='C' and note15 like 'SC%%'";
    $search .= " and (assyno not like 'NKB%%')";
    $search .= " and (assyno not like 'SS%%')";
    $search .= " and CASE WHEN 計上日<20130501 THEN groupm.support_group_code IS NULL ELSE 事業部='C' END";
    //$search .= " and groupm.support_group_code IS NULL";
} elseif ($div == 'D') {    // Ｃ標準なら
    $search .= " and 事業部='C' and (note15 NOT like 'SC%%' OR note15 IS NULL)";    // 部品売りを標準へする
    $search .= " and (assyno not like 'NKB%%')";
    $search .= " and (assyno not like 'SS%%')";
    $search .= " and (CASE WHEN 計上日<20130501 THEN groupm.support_group_code IS NULL ELSE 事業部='C' END)";
    //$search .= " and groupm.support_group_code IS NULL";
} elseif ($div == "N") {    // リニアのバイモル・試験修理を除く assyno でチェック
    $search .= " and 事業部='L' and (assyno NOT like 'LC%%' AND assyno NOT like 'LR%%')";
    $search .= " and (assyno not like 'SS%%')";
    $search .= " and CASE WHEN assyno = '' THEN 事業部='L' ELSE CASE WHEN m.midsc IS NULL THEN 事業部='L' ELSE m.midsc not like 'DPE%%' END END";
    $search .= " and CASE WHEN 計上日<20130501 THEN groupm.support_group_code IS NULL ELSE 事業部='L' END";
    //$search .= " and groupm.support_group_code IS NULL";
} elseif ($div == "B") {    // バイモルの場合は assyno でチェック
    //$search .= " and (assyno like 'LC%%' or assyno like 'LR%%')";
    $search .= " and (assyno like 'LC%%' or assyno like 'LR%%' or m.midsc like 'DPE%%')";
    $search .= " and CASE WHEN 計上日<20130501 THEN groupm.support_group_code IS NULL ELSE 事業部='L' END";
    //$search .= " and groupm.support_group_code IS NULL";
} elseif ($div == "SSC") {   // カプラ試験・修理の場合は assyno でチェック
    $search .= " and 事業部='C' and (assyno like 'SS%%')";
} elseif ($div == "SSL") {   // リニア試験・修理の場合は assyno でチェック
    $search .= " and 事業部='L' and (assyno like 'SS%%')";
} elseif ($div == "NKB") {  // 商品管理の場合は assyno でチェック
    $search .= " and (assyno like 'NKB%%')";
} elseif ($div == "TRI") {  // 試作の場合は事業部・売上区分・伝票番号でチェック
    $search .= " and 事業部='C'";
    $search .= " and ( datatype='3' or datatype='7' )";
    $search .= " and 伝票番号='00222'";
} elseif ($div == "NKCT") { // NKCTの場合は支援先コード(1)でチェック
    $search .= " and CASE WHEN 計上日<20130501 THEN groupm.support_group_code=1 END";
    //$search .= " and groupm.support_group_code=1";
} elseif ($div == "NKT") {  // NKTの場合は支援先コード(2)でチェック
    $search .= " and CASE WHEN 計上日<20130501 THEN groupm.support_group_code=2 END";
    //$search .= " and groupm.support_group_code=2";
} elseif ($div == "_") {    // 事業部なし
    $search .= " and 事業部=' '";
} elseif ($div == "C") {
    $search .= " and 事業部='$div'";
    $search .= " and (assyno not like 'NKB%%')";
    $search .= " and (assyno not like 'SS%%')";
} elseif ($div == "L") {
    $search .= " and 事業部='$div'";
    $search .= " and (assyno not like 'SS%%')";
} elseif ($div != " ") {
    $search .= " and 事業部='$div'";
}
*/
$query = sprintf("$query %s", $search);     // SQL query 文の完成

//////////// 表形式のデータ表示用のサンプル Query & 初期化
// 集計金額の取得
$s_div       = array('C','L','T','SSL','NKB','');
$sdiv_num    = count($s_div);
$s_kingaku   = array();
$s_kingaku_t = array();
for ($r=0; $r<$sdiv_num; $r++) {   // 事業部ごとに取得
    $s_kingaku_t[$r] = 0;
    for ($i=1; $i<10; $i++) {   // 売上区分１～９までを取得
        if ($s_div[$r] == "C") {
            $search  = " and 事業部='$s_div[$r]'";
            $search .= " and (assyno not like 'NKB%%')";
            $search .= " and (assyno not like 'SS%%')";
            $search .= " and datatype='$i'";
        } elseif ($s_div[$r] == "L") {
            $search  = " and 事業部='$s_div[$r]'";
            $search .= " and (assyno not like 'SS%%')";
            $search .= " and datatype='$i'";
        } elseif ($s_div[$r] == "SSL") {   // リニア試験・修理の場合は assyno でチェック
            $search  = " and 事業部='L' and (assyno like 'SS%%')";
            $search .= " and datatype='$i'";
        } elseif ($s_div[$r] == "NKB") {  // 商品管理の場合は assyno でチェック
            $search  = " and (assyno like 'NKB%%')";
            $search .= " and datatype='$i'";
        } elseif ($s_div[$r] == "T") {
            $search  = " and 事業部='T'";
            $search .= " and datatype='$i'";
        } else {
            $search  = " and datatype='$i'";
        }
        $query_s  = sprintf("$query %s", $search);     // SQL query 文の完成
        $res_syu  = array();
        if (getResult($query_s, $res_syu) <= 0) {
            $s_kingaku[$r][$i] = 0;
        } else {
            $s_kingaku[$r][$i] = $res_syu[0]['t_kingaku'];
            $s_kingaku_t[$r]  += $s_kingaku[$r][$i];
        }
    }
}
$item = array();
$item[0]   = "売上状況カプラ修理";
$item[1]   = "売上状況新品調整";
$item[2]   = "売上状況商管調整";
$item[3]   = "売上状況カプラ目標";
$item[4]   = "売上状況リニア目標";
$item[5]   = "売上状況ツール目標";
$item[6]   = "売上状況試修目標";
$item[7]   = "売上状況商管目標";
$item[8]   = "売上状況全体目標";
///////// 入力text 変数 初期化
$invent = array();
for ($i = 0; $i < 9; $i++) {
    if (isset($_POST['invent'][$i])) {
        $invent[$i] = $_POST['invent'][$i];
    } else {
        $invent[$i] = 0;
    }
}
if (!isset($_POST['entry'])) {     // データ入力
    ////////// 登録済みならば金額取得
    for ($i = 0; $i < 9; $i++) {
        $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='%s'", $yyyymm, $item[$i]);
        $res = array();
        if (getResult2($query,$res) > 0) {
            $invent[$i] = $res[0][0];
        }
    }
} else {
    // 全体目標計算
    $invent[8] = $invent[3] + $invent[4] + $invent[5] + $invent[6] + $invent[7];
    for ($i = 0; $i < 9; $i++) {
        $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='%s'", $yyyymm, $item[$i]);
        $res = array();
        if (getResult2($query,$res) <= 0) {
            /////////// begin トランザクション開始
            if ($con = db_connect()) {
                query_affected_trans($con, "begin");
            } else {
                $_SESSION["s_sysmsg"] .= "データベースに接続できません";
                header("Location: $current_script");
                exit();
            }
            ////////// Insert Start
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '%s')", $yyyymm, $invent[$i], $item[$i]);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION["s_sysmsg"] .= sprintf("%sの新規登録に失敗<br>第 %d期 %d月", $item[$i], $ki, $tuki);
                query_affected_trans($con, "rollback");     // transaction rollback
                header("Location: $current_script");
                exit();
            }
            /////////// commit トランザクション終了
            query_affected_trans($con, "commit");
            $_SESSION["s_sysmsg"] = sprintf("<font color='yellow'>第%d期 %d月 売上状況照会データ 新規 登録完了</font>",$ki,$tuki);
        } else {
            /////////// begin トランザクション開始
            if ($con = db_connect()) {
                query_affected_trans($con, "begin");
            } else {
                $_SESSION["s_sysmsg"] .= "データベースに接続できません";
                header("Location: $current_script");
                exit();
            }
            ////////// UPDATE Start
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='%s'", $invent[$i], $yyyymm, $item[$i]);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION["s_sysmsg"] .= sprintf("%sのUPDATEに失敗<br>第 %d期 %d月", $item[$i], $ki, $tuki);
                query_affected_trans($con, "rollback");     // transaction rollback
                header("Location: $current_script");
                exit();
            }
            /////////// commit トランザクション終了
            query_affected_trans($con, "commit");
            $_SESSION["s_sysmsg"] = sprintf("<font color='yellow'>第%d期 %d月 売上状況照会データ 変更 完了</font>",$ki,$tuki);
        }
    }
}

// 売上データ計算
// 実手打 手打－修理
$c_jitsute = $s_kingaku[0][3] - $invent[0];
$a_jitsute = $c_jitsute;
// その他売上 カプラ：個別 ＋ 実手打 － 調整（LTは実手打が手打に）
$c_sonota  = $s_kingaku[0][2] + $c_jitsute - $s_kingaku[0][4];
$l_sonota  = $s_kingaku[1][2] + $s_kingaku[1][3] - $s_kingaku[1][4];
$t_sonota  = $s_kingaku[2][2] + $s_kingaku[2][3] - $s_kingaku[2][4];
$a_sonota  = $c_sonota + $l_sonota + $t_sonota;
// 部品売上 移動～受注の合計
$c_buhin   = $s_kingaku[0][5] + $s_kingaku[0][6] + $s_kingaku[0][7] + $s_kingaku[0][8] + $s_kingaku[0][9];
$l_buhin   = $s_kingaku[1][5] + $s_kingaku[1][6] + $s_kingaku[1][7] + $s_kingaku[1][8] + $s_kingaku[1][9];
$t_buhin   = $s_kingaku[2][5] + $s_kingaku[2][6] + $s_kingaku[2][7] + $s_kingaku[2][8] + $s_kingaku[2][9];
$a_buhin   = $c_buhin + $l_buhin + $t_buhin;
// 総売上
$c_souuri  = $s_kingaku_t[0] - $invent[0];
$l_souuri  = $s_kingaku_t[1] - $invent[1];
$t_souuri  = $s_kingaku_t[2];
$s_souuri  = $s_kingaku_t[3] + $invent[0] + $invent[1];
$b_souuri  = $s_kingaku_t[4] + $invent[2];
$a_souuri  = $c_souuri + $l_souuri + $t_souuri + $s_souuri + $b_souuri;

// 千円単位
// 部品売上
$c_buhin_s = $c_buhin / 1000;
$l_buhin_s = $l_buhin / 1000;
$t_buhin_s = $t_buhin / 1000;
$a_buhin_s = $a_buhin / 1000;
// その他売上
$c_sonota_s = $c_sonota / 1000;
$l_sonota_s = $l_sonota / 1000;
$t_sonota_s = $t_sonota / 1000;
$a_sonota_s = $a_sonota / 1000;
// 特注品（カプラのみ）
$query = "select
                    count(数量) as t_ken,
                    sum(数量) as t_kazu,
                    sum(Uround(数量*単価,0)) as t_kingaku
              from
                    hiuuri
              left outer join
                    assembly_schedule as a
              on 計画番号=plan_no
              left outer join
                    product_support_master AS groupm
              on assyno=groupm.assy_no
              left outer join
                    miitem as m
              on assyno=m.mipn";
$search = "where 計上日>=$d_start and 計上日<=$d_end";
$search .= " and 事業部='C' and note15 like 'SC%%'";
$search .= " and (assyno not like 'NKB%%')";
$search .= " and (assyno not like 'SS%%')";
$search .= " and CASE WHEN 計上日<20130501 THEN groupm.support_group_code IS NULL ELSE 事業部='C' END";
$query_s  = sprintf("$query %s", $search);     // SQL query 文の完成
$res_toku  = array();
if (getResult($query_s, $res_toku) <= 0) {
    $c_toku   = 0;
    $c_toku_s = 0;
} else {
    $c_toku   = $res_toku[0]['t_kingaku'];
    $c_toku_s = $c_toku / 1000;
}
// 標準（それぞれ全体から特注・部品その他を除外）
$c_hyo = $c_souuri - $c_toku - $c_buhin - $c_sonota;
$l_hyo = $l_souuri - $l_buhin - $l_sonota;
$t_hyo = $t_souuri - $t_buhin - $t_sonota;
$s_hyo = $s_souuri;
$b_hyo = $b_souuri;

$c_hyo_s = $c_hyo / 1000;
$l_hyo_s = $l_hyo / 1000;
$t_hyo_s = $t_hyo / 1000;
$s_hyo_s = $s_hyo / 1000;
$b_hyo_s = $b_hyo / 1000;

// 製品計 特注＋標準
$c_sei_t = $c_toku_s + $c_hyo_s;
$l_sei_t = $l_hyo_s;
$t_sei_t = $t_hyo_s;

// 部品計 部品＋その他
$c_buhin_t = $c_buhin_s + $c_sonota_s;
$l_buhin_t = $l_buhin_s + $l_sonota_s;
$t_buhin_t = $t_buhin_s + $t_sonota_s;

// 実績 総売上の単位千円
$c_jisseki = $c_souuri / 1000;
$l_jisseki = $l_souuri / 1000;
$t_jisseki = $t_souuri / 1000;
$s_jisseki = $s_souuri / 1000;
$b_jisseki = $b_souuri / 1000;
$a_jisseki = $a_souuri / 1000;

// 達成率 実績 ÷ 目標
if ($invent[3] <> 0) {
    $c_ritsu = $c_jisseki / $invent[3] * 100;
} else {
    $c_ritsu = 0;
}
if ($invent[4] <> 0) {
    $l_ritsu = $l_jisseki / $invent[4] * 100;
} else {
    $l_ritsu = 0;
}
if ($invent[5] <> 0) {
    $t_ritsu = $t_jisseki / $invent[5] * 100;
} else {
    $t_ritsu = 0;
}
if ($invent[6] <> 0) {
    $s_ritsu = $s_jisseki / $invent[6] * 100;
} else {
    $s_ritsu = 0;
}
if ($invent[7] <> 0) {
    $b_ritsu = $b_jisseki / $invent[7] * 100;
} else {
    $b_ritsu = 0;
}
if ($invent[8] <> 0) {
    $a_ritsu = $a_jisseki / $invent[8] * 100;
} else {
    $a_ritsu = 0;
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
.winboxy {
    border-style: solid;
    border-width: 1px;
    border-top-color:       #FFFFFF;
    border-left-color:      #FFFFFF;
    border-right-color:     #bdaa90;
    border-bottom-color:    #bdaa90;
    background-color:   yellow;
    color:              blue;
    font-size:          12pt;
    font-weight:        bold;
    font-family:        monospace;
}
.right{
    text-align:right;
    font:bold 12pt;
    font-family: monospace;
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
        <BR>
        <!--------------- ここから本文の表を表示する -------------------->
        <form name='invent' action='<?php echo $menu->out_self() ?>' method='post'>
        <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <table class='winbox_field' width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <div class='pt11b' align='right'><単位：円></div>
            <thead>
                <!-- テーブル ヘッダーの表示 -->
                <tr>
                    <th class='winbox' nowrap>売上区分</th>
                    <th class='winbox' nowrap>カプラ</th>
                    <th class='winbox' nowrap>リニア</th>
                    <th class='winbox' nowrap>ツール</th>
                    <th class='winbox' nowrap>試験修理</th>
                    <th class='winbox' nowrap>商品管理</th>
                    <th class='winbox' nowrap>合計</th>
                </tr>
            </thead>
            <tfoot>
                <!-- 現在はフッターは何もない -->
            </tfoot>
            <tbody>
                <tr>
                    <th class='winbox' nowrap>完成</th>
                    <?php
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($s_kingaku[0][1], 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($s_kingaku[1][1], 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($s_kingaku[2][1], 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($s_kingaku[3][1], 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($s_kingaku[4][1], 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($s_kingaku[5][1], 0) . "</div></td>\n";
                    ?>
                </tr>
                <tr>
                    <th class='winbox' nowrap>個別</th>
                    <?php
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($s_kingaku[0][2], 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($s_kingaku[1][2], 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($s_kingaku[2][2], 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($s_kingaku[3][2], 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($s_kingaku[4][2], 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($s_kingaku[5][2], 0) . "</div></td>\n";
                    ?>
                </tr>
                <tr>
                    <th class='winbox' nowrap>手打</th>
                    <?php
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($s_kingaku[0][3], 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($s_kingaku[1][3], 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($s_kingaku[2][3], 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($s_kingaku[3][3], 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($s_kingaku[4][3], 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($s_kingaku[5][3], 0) . "</div></td>\n";
                    ?>
                </tr>
                <tr>
                    <th class='winbox' nowrap>調整</th>
                    <?php
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($s_kingaku[0][4], 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($s_kingaku[1][4], 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($s_kingaku[2][4], 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($s_kingaku[3][4], 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($s_kingaku[4][4], 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($s_kingaku[5][4], 0) . "</div></td>\n";
                    ?>
                </tr>
                <tr>
                    <th class='winbox' nowrap>移動</th>
                    <?php
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($s_kingaku[0][5], 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($s_kingaku[1][5], 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($s_kingaku[2][5], 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($s_kingaku[3][5], 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($s_kingaku[4][5], 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($s_kingaku[5][5], 0) . "</div></td>\n";
                    ?>
                </tr>
                <tr>
                    <th class='winbox' nowrap>直納</th>
                    <?php
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($s_kingaku[0][6], 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($s_kingaku[1][6], 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($s_kingaku[2][6], 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($s_kingaku[3][6], 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($s_kingaku[4][6], 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($s_kingaku[5][6], 0) . "</div></td>\n";
                    ?>
                </tr>
                <tr>
                    <th class='winbox' nowrap>売上</th>
                    <?php
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($s_kingaku[0][7], 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($s_kingaku[1][7], 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($s_kingaku[2][7], 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($s_kingaku[3][7], 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($s_kingaku[4][7], 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($s_kingaku[5][7], 0) . "</div></td>\n";
                    ?>
                </tr>
                <tr>
                    <th class='winbox' nowrap>振替</th>
                    <?php
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($s_kingaku[0][8], 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($s_kingaku[1][8], 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($s_kingaku[2][8], 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($s_kingaku[3][8], 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($s_kingaku[4][8], 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($s_kingaku[5][8], 0) . "</div></td>\n";
                    ?>
                </tr>
                <tr>
                    <th class='winbox' nowrap>受注</th>
                    <?php
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($s_kingaku[0][9], 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($s_kingaku[1][9], 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($s_kingaku[2][9], 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($s_kingaku[3][9], 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($s_kingaku[4][9], 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($s_kingaku[5][9], 0) . "</div></td>\n";
                    ?>
                </tr>
                <tr>
                    <td class='winboxy' nowrap align='center'>合計</th>
                    <?php
                    echo "<td class='winboxy' nowrap align='right'>" . number_format($s_kingaku_t[0], 0) . "</td>\n";
                    echo "<td class='winboxy' nowrap align='right'>" . number_format($s_kingaku_t[1], 0) . "</td>\n";
                    echo "<td class='winboxy' nowrap align='right'>" . number_format($s_kingaku_t[2], 0) . "</td>\n";
                    echo "<td class='winboxy' nowrap align='right'>" . number_format($s_kingaku_t[3], 0) . "</td>\n";
                    echo "<td class='winboxy' nowrap align='right'>" . number_format($s_kingaku_t[4], 0) . "</td>\n";
                    echo "<td class='winboxy' nowrap align='right'>" . number_format($s_kingaku_t[5], 0) . "</td>\n";
                    ?>
                </tr>
            </tbody>
        </table>
            </td></tr>
        </table> <!----------------- ダミーEnd ------------------>
        <BR>
        <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <table class='winbox_field' width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <div class='pt11b' align='right'><単位：円></div>
            <thead>
                <!-- テーブル ヘッダーの表示 -->
                <tr>
                    <th class='winbox' nowrap>Ｃ修理</th>
                    <th class='winbox' nowrap>新品調整</th>
                    <th class='winbox' nowrap>商管調整</th>
                    <th class='winbox' nowrap>　</th>
                </tr>
            </thead>
            <tfoot>
                <!-- 現在はフッターは何もない -->
            </tfoot>
            <tbody>
                
                <tr>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='12' maxlength='11' value='<?php echo $invent[0] ?>' class='right'>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='12' maxlength='11' value='<?php echo $invent[1] ?>' class='right'>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='12' maxlength='11' value='<?php echo $invent[2] ?>' class='right'>
                    </td>
                    <td colspan='4' align='center'>
                        <input type='submit' name='entry' value='登録' >
                    </td>
                </tr>
            </tbody>
        </table>
            </td></tr>
        </table> <!----------------- ダミーEnd ------------------>
        <BR>
        <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <table class='winbox_field' width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <div class='pt11b' align='right'><単位：円></div>
            <thead>
                <!-- テーブル ヘッダーの表示 -->
                <tr>
                    <th class='winbox' nowrap>　</th>
                    <th class='winbox' nowrap>カプラ</th>
                    <th class='winbox' nowrap>リニア</th>
                    <th class='winbox' nowrap>ツール</th>
                    <th class='winbox' nowrap>試験修理</th>
                    <th class='winbox' nowrap>商品管理</th>
                    <th class='winbox' nowrap>合計</th>
                </tr>
            </thead>
            <tfoot>
                <!-- 現在はフッターは何もない -->
            </tfoot>
            <tbody>
                <tr>
                    <th class='winbox' nowrap>実手打</th>
                    <?php
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($c_jitsute, 0) . "</div></td>\n";
                    ?>
                    <td class='winbox' nowrap align='right'><div class='pt10'>　</div></td>
                    <td class='winbox' nowrap align='right'><div class='pt10'>　</div></td>
                    <td class='winbox' nowrap align='right'><div class='pt10'>　</div></td>
                    <td class='winbox' nowrap align='right'><div class='pt10'>　</div></td>
                    <?php
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($a_jitsute, 0) . "</div></td>\n";
                    ?>
                </tr>
                <tr>
                    <th class='winbox' nowrap>その他売上</th>
                    <?php
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($c_sonota, 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($l_sonota, 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($t_sonota, 0) . "</div></td>\n";
                    ?>
                    <td class='winbox' nowrap align='right'><div class='pt10'>　</div></td>
                    <td class='winbox' nowrap align='right'><div class='pt10'>　</div></td>
                    <?php
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($a_sonota, 0) . "</div></td>\n";
                    ?>
                </tr>
                <tr>
                    <th class='winbox' nowrap>部品売上</th>
                    <?php
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($c_buhin, 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($l_buhin, 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($t_buhin, 0) . "</div></td>\n";
                    ?>
                    <td class='winbox' nowrap align='right'><div class='pt10'>　</div></td>
                    <td class='winbox' nowrap align='right'><div class='pt10'>　</div></td>
                    <?php
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($a_buhin, 0) . "</div></td>\n";
                    ?>
                </tr>
                <tr>
                    <th class='winbox' nowrap>総売上</th>
                    <?php
                    echo "<td class='winboxy' nowrap align='right'>" . number_format($c_souuri, 0) . "</td>\n";
                    echo "<td class='winboxy' nowrap align='right'>" . number_format($l_souuri, 0) . "</td>\n";
                    echo "<td class='winboxy' nowrap align='right'>" . number_format($t_souuri, 0) . "</td>\n";
                    echo "<td class='winboxy' nowrap align='right'>" . number_format($s_souuri, 0) . "</td>\n";
                    echo "<td class='winboxy' nowrap align='right'>" . number_format($b_souuri, 0) . "</td>\n";
                    echo "<td class='winboxy' nowrap align='right'>" . number_format($a_souuri, 0) . "</td>\n";
                    ?>
                </tr>
            </tbody>
        </table>
            </td></tr>
        </table> <!----------------- ダミーEnd ------------------>
        <BR>
        <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <table class='winbox_field' width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <div class='pt11b' align='right'><単位：千円></div>
            <thead>
                <!-- テーブル ヘッダーの表示 -->
                <tr>
                    <th class='winbox' nowrap>カプラ目標</th>
                    <th class='winbox' nowrap>リニア目標</th>
                    <th class='winbox' nowrap>ツール目標</th>
                    <th class='winbox' nowrap>試修目標</th>
                    <th class='winbox' nowrap>商管目標</th>
                    <th class='winbox' nowrap>全体目標</th>
                    <th class='winbox' nowrap>　</th>
                </tr>
            </thead>
            <tfoot>
                <!-- 現在はフッターは何もない -->
            </tfoot>
            <tbody>
                
                <tr>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='12' maxlength='11' value='<?php echo $invent[3] ?>' class='right'>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='12' maxlength='11' value='<?php echo $invent[4] ?>' class='right'>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='12' maxlength='11' value='<?php echo $invent[5] ?>' class='right'>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='12' maxlength='11' value='<?php echo $invent[6] ?>' class='right'>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='12' maxlength='11' value='<?php echo $invent[7] ?>' class='right'>
                    </td>
                    <?php
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($invent[8], 0) . "</div></td>\n";
                    ?>
                    <td colspan='4' align='center'>
                        <input type='submit' name='entry' value='登録' >
                    </td>
                </tr>
            </tbody>
        </table>
            </td></tr>
        </table> <!----------------- ダミーEnd ------------------>
        <BR>
        <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <table class='winbox_field' width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <div class='pt11b' align='right'><単位：千円></div>
            <thead>
                <!-- テーブル ヘッダーの表示 -->
                <tr>
                    <th class='winbox' nowrap>　</th>
                    <th class='winbox' nowrap>カプラ</th>
                    <th class='winbox' nowrap>リニア</th>
                    <th class='winbox' nowrap>ツール</th>
                    <th class='winbox' nowrap>試験修理</th>
                    <th class='winbox' nowrap>商品管理</th>
                    <th class='winbox' nowrap>合計</th>
                </tr>
            </thead>
            <tfoot>
                <!-- 現在はフッターは何もない -->
            </tfoot>
            <tbody>
                <tr>
                    <th class='winbox' nowrap>標準品</th>
                    <?php
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($c_hyo_s, 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($l_hyo_s, 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($t_hyo_s, 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($s_hyo_s, 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($b_hyo_s, 0) . "</div></td>\n";
                    ?>
                    <td class='winbox' nowrap align='right'><div class='pt10'>　</div></td>
                </tr>
                <tr>
                    <th class='winbox' nowrap>特注品</th>
                    <?php
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($c_toku_s, 0) . "</div></td>\n";
                    ?>
                    <td class='winbox' nowrap align='right'><div class='pt10'>　</div></td>
                    <td class='winbox' nowrap align='right'><div class='pt10'>　</div></td>
                    <td class='winbox' nowrap align='right'><div class='pt10'>　</div></td>
                    <td class='winbox' nowrap align='right'><div class='pt10'>　</div></td>
                    <td class='winbox' nowrap align='right'><div class='pt10'>　</div></td>
                </tr>
                <tr>
                    <th class='winbox' nowrap>製品計</th>
                    <?php
                    echo "<td class='winboxy' nowrap align='right'>" . number_format($c_sei_t, 0) . "</td>\n";
                    echo "<td class='winboxy' nowrap align='right'>" . number_format($l_sei_t, 0) . "</td>\n";
                    echo "<td class='winboxy' nowrap align='right'>" . number_format($t_sei_t, 0) . "</td>\n";
                    echo "<td class='winboxy' nowrap align='right'>　</td>\n";
                    echo "<td class='winboxy' nowrap align='right'>　</td>\n";
                    echo "<td class='winboxy' nowrap align='right'>　</td>\n";
                    ?>
                </tr>
                <tr>
                    <th class='winbox' nowrap>部品</th>
                    <?php
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($c_buhin_s, 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($l_buhin_s, 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($t_buhin_s, 0) . "</div></td>\n";
                    ?>
                    <td class='winbox' nowrap align='right'><div class='pt10'>　</div></td>
                    <td class='winbox' nowrap align='right'><div class='pt10'>　</div></td>
                    <?php
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($a_buhin_s, 0) . "</div></td>\n";
                    ?>
                </tr>
                <tr>
                    <th class='winbox' nowrap>その他</th>
                    <?php
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($c_sonota_s, 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($l_sonota_s, 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($t_sonota_s, 0) . "</div></td>\n";
                    ?>
                    <td class='winbox' nowrap align='right'><div class='pt10'>　</div></td>
                    <td class='winbox' nowrap align='right'><div class='pt10'>　</div></td>
                    <?php
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($a_sonota_s, 0) . "</div></td>\n";
                    ?>
                </tr>
                <tr>
                    <th class='winbox' nowrap>部品・その他計</th>
                    <?php
                    echo "<td class='winboxy' nowrap align='right'>" . number_format($c_buhin_t, 0) . "</td>\n";
                    echo "<td class='winboxy' nowrap align='right'>" . number_format($l_buhin_t, 0) . "</td>\n";
                    echo "<td class='winboxy' nowrap align='right'>" . number_format($t_buhin_t, 0) . "</td>\n";
                    echo "<td class='winboxy' nowrap align='right'>　</td>\n";
                    echo "<td class='winboxy' nowrap align='right'>　</td>\n";
                    echo "<td class='winboxy' nowrap align='right'>　</td>\n";
                    ?>
                </tr>
                <tr>
                    <th class='winbox' nowrap>実績</th>
                    <?php
                    echo "<td class='winboxy' nowrap align='right'>" . number_format($c_jisseki, 0) . "</td>\n";
                    echo "<td class='winboxy' nowrap align='right'>" . number_format($l_jisseki, 0) . "</td>\n";
                    echo "<td class='winboxy' nowrap align='right'>" . number_format($t_jisseki, 0) . "</td>\n";
                    echo "<td class='winboxy' nowrap align='right'>" . number_format($s_jisseki, 0) . "</td>\n";
                    echo "<td class='winboxy' nowrap align='right'>" . number_format($b_jisseki, 0) . "</td>\n";
                    echo "<td class='winboxy' nowrap align='right'>" . number_format($a_jisseki, 0) . "</td>\n";
                    ?>
                </tr>
                <tr>
                    <th class='winbox' nowrap>達成度％</th>
                    <?php
                    echo "<td class='winboxy' nowrap align='right'>" . number_format($c_ritsu, 1) . "</td>\n";
                    echo "<td class='winboxy' nowrap align='right'>" . number_format($l_ritsu, 1) . "</td>\n";
                    echo "<td class='winboxy' nowrap align='right'>" . number_format($t_ritsu, 1) . "</td>\n";
                    echo "<td class='winboxy' nowrap align='right'>" . number_format($s_ritsu, 1) . "</td>\n";
                    echo "<td class='winboxy' nowrap align='right'>" . number_format($b_ritsu, 1) . "</td>\n";
                    echo "<td class='winboxy' nowrap align='right'>" . number_format($a_ritsu, 1) . "</td>\n";
                    ?>
                </tr>
            </tbody>
        </table>
            </td></tr>
        </table> <!----------------- ダミーEnd ------------------>
        
        </form>
    </center>
</body>
<?php echo $menu->out_alert_java()?>
</html>
<?php
// ob_end_flush();                 // 出力バッファをgzip圧縮 END
?>
