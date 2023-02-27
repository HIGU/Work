<?php
//////////////////////////////////////////////////////////////////////////////
// 月次損益関係 製造原価報告書                                              //
// Copyright(C) 2017-2019 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp     //
// Changed history                                                          //
// 2017/09/11 Created   manufacture_cost_total.php                          //
// 2018/06/26 決算書用データ登録を追加                                      //
// 2018/07/05 部品仕掛C 2018/05の原価調整は抜かないよう修正                 //
// 2019/05/17 日付の取得方法の変更                                          //
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

$menu->set_action('部品仕掛Ｃ', PL . 'cost_parts_widget_view.php');
$menu->set_action('原材料', PL . 'cost_material_view.php');
$menu->set_action('部品', PL . 'cost_parts_view.php');
$menu->set_action('切粉', PL . 'cost_kiriko_view.php');

///// 対象当月
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

///// 期・半期の取得
$tuki_chk = 12;
if ($tuki_chk >= 1 && $tuki_chk <= 3) {
    $hanki = '４';
} elseif ($tuki_chk >= 4 && $tuki_chk <= 6) {
    $hanki = '１';
} elseif ($tuki_chk >= 7 && $tuki_chk <= 9) {
    $hanki = '２';
} elseif ($tuki_chk >= 10) {
    $hanki = '３';
}

///// 年月範囲の取得
if ($tuki_chk >= 1 && $tuki_chk <= 3) {
    $str_ym = $yyyy . '0401';
    $end_ym = $yyyymm . '99';
} elseif ($tuki_chk >= 4 && $tuki_chk <= 6) {
    $str_ym = $yyyy . '0401';
    $end_ym = $yyyymm . '99';
} elseif ($tuki_chk >= 7 && $tuki_chk <= 9) {
    $str_ym = $yyyy . '0401';
    $end_ym = $yyyymm . '99';
} elseif ($tuki_chk >= 10) {
    $str_ym = $yyyy . '0401';
    $end_ym = $yyyymm . '99';
}
///// TNK期 → NK期へ変換
$nk_ki = $ki + 44;

//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
if ($tuki_chk == 3) {
    $menu->set_title("第 {$ki} 期　本決算　当　期　材　料　仕　入　高");
} else {
    $menu->set_title("第 {$ki} 期　第{$hanki}四半期　当　期　材　料　仕　入　高");
}

if (isset($_POST['input_data'])) {                        // 当月データの登録
    ///////// 項目とインデックスの関連付け
    $item = array();
    $item[0]   = "買掛伝票計上原材料";
    $item[1]   = "買掛伝票計上部品仕掛C";
    $item[2]   = "仕訳伝票計上部品仕掛C";
    $item[3]   = "仕訳伝票計上原材料";
    $item[4]   = "有償支給減額原材料";
    $item[5]   = "有償支給減額部品";
    $item[6]   = "切粉売却減額";
    $item[7]   = "当期材料仕入高";
    ///////// 各データの保管
    $input_data = array();
    $input_data[0]   = $genka_kin;
    $input_data[1]   = $bukai_kin;
    $input_data[2]   = $buswa_kin;
    $input_data[3]   = $gensw_kin;
    $input_data[4]   = $genyu_kin;
    $input_data[5]   = $buhyu_kin;
    $input_data[6]   = $kiris_kin;
    $input_data[7]   = $total_kin;
    ///////// 各データの登録
    insert_date($item,$yyyymm,$input_data);
}
function insert_date($item,$yyyymm,$input_data) 
{
    for ($i = 0; $i < 8; $i++) {
        $query = sprintf("select rep_kin from financial_report_data where rep_ymd=%d and rep_note='%s'", $yyyymm, $item[$i]);
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
            $query = sprintf("insert into financial_report_data (rep_ymd, rep_kin, rep_note, last_date, last_user) values (%d, %d, '%s', CURRENT_TIMESTAMP, '%s')", $yyyymm, $input_data[$i], $item[$i], $_SESSION['User_ID']);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION["s_sysmsg"] .= sprintf("%sの新規登録に失敗<br> %d", $item[$i], $yyyymm);
                query_affected_trans($con, "rollback");     // transaction rollback
                exit();
            }
            /////////// commit トランザクション終了
            query_affected_trans($con, "commit");
            $_SESSION["s_sysmsg"] = sprintf("<font color='yellow'>%d 決算書データ 新規 登録完了</font>",$yyyymm);
        } else {
            /////////// begin トランザクション開始
            if ($con = db_connect()) {
                query_affected_trans($con, "begin");
            } else {
                $_SESSION["s_sysmsg"] .= "データベースに接続できません";
                exit();
            }
            ////////// UPDATE Start
            $query = sprintf("update financial_report_data set rep_kin=%d, last_date=CURRENT_TIMESTAMP, last_user='%s' where rep_ymd=%d and rep_note='%s'", $input_data[$i], $_SESSION['User_ID'], $yyyymm, $item[$i]);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION["s_sysmsg"] .= sprintf("%sのUPDATEに失敗<br> %d", $item[$i], $yyyymm);
                query_affected_trans($con, "rollback");     // transaction rollback
                exit();
            }
            /////////// commit トランザクション終了
            query_affected_trans($con, "commit");
            $_SESSION["s_sysmsg"] = sprintf("<font color='yellow'>%d 決算書データ 変更 完了</font>",$yyyymm);
        }
    }
    $_SESSION["s_sysmsg"] .= "決算書のデータを登録しました。";
}
/////////// HTML Header を出力してキャッシュを制御
$menu->out_html_header();
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
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
    color:          teal;
}
.pt11b {
    font-size:      12pt;
    font-weight:    bold;
    font-family:    monospace;
}
th {
    background-color:   yellow;
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
        <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='10'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <table class='winbox_field' width='100%' align='center' bgcolor='black' border='1' cellspacing='1' cellpadding='15'>
            <THEAD>
                <!-- テーブル ヘッダーの表示 -->
                <tr>
                    <th class='winbox' nowrap>　</th>
                    <th class='winbox' nowrap>勘定科目</th>
                    <th class='winbox' nowrap>金　　額</th>
                </tr>
            </THEAD>
            <TFOOT>
                <!-- 現在はフッターは何もない -->
            </TFOOT>
            <TBODY>
                <tr>
                    <td class='winbox' nowrap bgcolor='#d6d3ce' rowspan='2'>
                        <div class='pt10b'>買掛伝票計上</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#d6d3ce'>
                        <div class='pt11b'><a href='cost_material_view.php?nk_ki=<?php echo $nk_ki ?>&str_ym=<?php echo $str_ym ?>&end_ym=<?php echo $end_ym ?>&2ki_ym=<?php echo $ki2_ym ?>#mark'>原　材　料</a></div>
                    </td>
                    <td class='winbox' nowrap align='right' bgcolor='#d6d3ce'>
                        <div class='pt11b'><?= 500 ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#d6d3ce'>
                        <div class='pt11b'><a href='cost_parts_widget_view.php?nk_ki=<?php echo $nk_ki ?>&str_ym=<?php echo $str_ym ?>&end_ym=<?php echo $end_ym ?>&2ki_ym=<?php echo $ki2_ym ?>#mark'>部品仕掛Ｃ</a></div>
                    </td>
                    <td class='winbox' nowrap align='right' bgcolor='#d6d3ce'>
                        <div class='pt11b'><?= 500 ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#d6d3ce' rowspan='2'>
                        <div class='pt10b'>仕訳伝票計上</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#d6d3ce'>
                        <div class='pt11b'><a href='cost_parts_widget_view.php?nk_ki=<?php echo $nk_ki ?>&str_ym=<?php echo $str_ym ?>&end_ym=<?php echo $end_ym ?>&2ki_ym=<?php echo $ki2_ym ?>#mark'>部品仕掛Ｃ</a></div>
                    </td>
                    <td class='winbox' nowrap align='right' bgcolor='#d6d3ce'>
                        <div class='pt11b'><?= 500 ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#d6d3ce'>
                        <div class='pt11b'><a href='cost_material_view.php?nk_ki=<?php echo $nk_ki ?>&str_ym=<?php echo $str_ym ?>&end_ym=<?php echo $end_ym ?>&2ki_ym=<?php echo $ki2_ym ?>#mark'>原　材　料</a></div>
                    </td>
                    <td class='winbox' nowrap align='right' bgcolor='#d6d3ce'>
                        <div class='pt11b'><?= 500 ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#d6d3ce' rowspan='2'>
                        <div class='pt10b'>有償支給減額</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#d6d3ce'>
                        <div class='pt11b'><a href='cost_material_view.php?nk_ki=<?php echo $nk_ki ?>&str_ym=<?php echo $str_ym ?>&end_ym=<?php echo $end_ym ?>&2ki_ym=<?php echo $ki2_ym ?>#mark'>原　材　料</a></div>
                    </td>
                    <td class='winbox' nowrap align='right' bgcolor='#d6d3ce'>
                        <div class='pt11b'><?= 500 ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#d6d3ce'>
                        <div class='pt11b'><a href='cost_parts_view.php?nk_ki=<?php echo $nk_ki ?>&str_ym=<?php echo $str_ym ?>&end_ym=<?php echo $end_ym ?>&2ki_ym=<?php echo $ki2_ym ?>#mark'>部　　　品</a></div>
                    </td>
                    <td class='winbox' nowrap align='right' bgcolor='#d6d3ce'>
                        <div class='pt11b'><?= 500 ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#d6d3ce'>
                        <div class='pt10b'>切粉売却減額</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#d6d3ce'>
                        <div class='pt11b'><a href='cost_kiriko_view.php?nk_ki=<?php echo $nk_ki ?>&str_ym=<?php echo $str_ym ?>&end_ym=<?php echo $end_ym ?>&2ki_ym=<?php echo $ki2_ym ?>#mark'>切　　　粉</a></div>
                    </td>
                    <td class='winbox' nowrap align='right' bgcolor='#d6d3ce'>
                        <div class='pt11b'><?= 500 ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap align='center' bgcolor='#d6d3ce' colspan='2'>
                        <div class='pt11b'>合　　　計</div>
                    </td>
                    <td class='winbox' nowrap align='right' bgcolor='#d6d3ce'>
                        <div class='pt11b'><?= 500 ?></div>
                    </td>
                </tr>
            </TBODY>
        </table>
            </td></tr>
        </table> <!----------------- ダミーEnd ------------------>
        <form method='post' action='<?php echo $menu->out_self() ?>'>
            <input class='pt10b' type='submit' name='input_data' value='登録' onClick='return data_input_click(this)'>
        </form>
    </center>
</body>
</html>
