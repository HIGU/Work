<?php
//////////////////////////////////////////////////////////////////////////////
// 納入予定の照会 次工程品(注文書未発行分) 明細をウィンドウ表示 List定義    //
// Copyright (C) 2004-2016 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2004/11/24 Created  order_details_next_List.php                          //
// 2004/11/25 初工程の打切チェックを SQL文に追加 winActiveChk()をTimer解除  //
// 2004/12/01 proc.delivery >= ' . date('Ymd', mktime() - (86400*124)) 追加 //
// 2005/07/26 上記を 124日から200日へ変更 order_schedule_list.phpに合わせる //
// 2006/09/07 呼出もとのＮＫＢ対応に伴い、製品グループにＮＫＢを追加        //
// 2007/02/27 部品番号にリンクを追加して在庫経歴・予定照会POPUP Windowを表示//
// 2007/05/11 ディレクトリを order/ → order/order_details/ へ変更          //
//            $orderbyを追加して納期遅れではない場合のリストを発注先順に表示//
// 2007/12/28 PostgreSQL8.3でINTEGERとTEXTとの自動キャストが無効になったため//
//            数値 NOT LIKE '%0' 又は 数値 LIKE '%0' →                     //
//            to_char(proc.order_no, 'FM9999999') NOT LIKE '%0'             //
// 2016/12/27 納期遅れをすべて表示するよう変更                         大谷 //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮
session_start();                            // ini_set()の次に指定すること Script 最上行
require_once ('../../../function.php');     // define.php と pgsql.php を require_once している
require_once ('../../../MenuHeader.php');   // TNK 全共通 menu class
access_log();                               // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader();                   // 認証チェック0=一般以上 戻り先=セッションより タイトル未設定

////////////// サイト設定
// $menu->set_site(30, 999);                   // site_index=30(生産メニュー) site_id=999(未定)
////////////// target設定
$menu->set_target('_parent');               // フレーム版の戻り先はtarget属性が必須

//////////// 自分をフレーム定義に変える
// $menu->set_self(INDUST . 'order/order_schedule.php');
//////////// 呼出先のaction名とアドレス設定
$menu->set_action('在庫予定',   INDUST . 'parts/parts_stock_plan/parts_stock_plan_Main.php');
$menu->set_action('在庫経歴',   INDUST . 'parts/parts_stock_history/parts_stock_view.php');
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('納入予定 次工程 明細の照会');     // タイトルを入れないとIEの一部のバージョンで表示できない不具合あり

///////// パラメーターチェック(基本的にセッションから取得)
if (isset($_SESSION['div'])) {
    $div = $_SESSION['div'];                // Default(セッションから)
} else {
    $div = 'C';                             // 初期値(カプラ)あまり意味は無い
}
//////// 指定日のパラメータ取得 & 設定
if (isset($_REQUEST['date'])) {
    if ($_REQUEST['date'] == 'OLD') {
        $date = $_REQUEST['date'];
    } else {
        $date = $_REQUEST['date'];              // 明細を表示する指定日付
        $date = ('20' . substr($date, 0, 2) . substr($date, 3, 2) . substr($date, 6, 2));
            // YYYYMMDDの形式に変換
    }
} else {
    $date = date('Ymd');                    // 初期値(当日)例外発生の場合に対応
}
//////// 事業部から共通な where句を設定
switch ($div) {
case 'C':       // C全体
    $where_div = "proc.parts_no LIKE 'C%' AND proc.locate != '52   '";
    break;
case 'SC':      // C特注
    $where_div = "proc.parts_no LIKE 'C%' AND plan.kouji_no LIKE '%SC%' AND proc.locate != '52   '";
    break;
case 'CS':      // C標準
    $where_div = "proc.parts_no LIKE 'C%' AND plan.kouji_no NOT LIKE '%SC%' AND proc.locate != '52   '";
    break;
case 'L':       // L全体
    $where_div = "proc.parts_no LIKE 'L%' AND proc.locate != '52   '";
    break;
case 'T':       // T全体
    $where_div = "proc.parts_no LIKE 'T%' AND proc.locate != '52   '";
    break;
case 'F':       // F全体
    $where_div = "proc.parts_no LIKE 'F%' AND proc.locate != '52   '";
    break;
case 'A':       // TNK全体
    $where_div = "(proc.parts_no LIKE 'C%' or proc.parts_no LIKE 'L%' or proc.parts_no LIKE 'T%' or proc.parts_no LIKE 'F%') AND proc.locate != '52   '";
    break;
case 'N':       // NKカプラ
    $where_div = "(proc.parts_no LIKE 'C%' or proc.parts_no LIKE 'L%' or proc.parts_no LIKE 'T%' or proc.parts_no LIKE 'F%') AND proc.locate = '52   '";
    break;
case 'NKB':
    $where_div = "plan.locate = '14'";
    break;
}
////////// 日付で共通の where句を生成
if ($date == 'OLD') {
    //$where_date = 'proc.delivery <= ' . date('Ymd', mktime() - 86400) . 'AND proc.delivery >= ' . date('Ymd', mktime() - (86400*200));
    $where_date = 'proc.delivery <= ' . date('Ymd', mktime() - 86400) . 'AND proc.delivery >= 0';
    $orderby = 'ORDER BY proc.delivery ASC, data.date_issue ASC';
} else {
    $where_date = "proc.delivery = {$date}";
    $orderby = 'ORDER BY data.vendor ASC, proc.delivery ASC, data.date_issue ASC';
}

$view = 'OK';   // スタートはOKで行う

////////// 共通SQL文を生成
$query = "
    SELECT  proc.order_no           AS 注文番号
            , substr(to_char(proc.order_date, 'FM9999/99/99'), 6, 5)          AS 発注日
            , to_char(proc.sei_no,'FM0000000')        AS 製造番号
            , proc.parts_no           AS 部品番号
            , proc.vendor             AS 発注先コード
            , (SELECT sub_order.order_q - sub_order.cut_siharai FROM order_process AS sub_order WHERE sub_order.sei_no=proc.sei_no AND to_char(sub_order.order_no, 'FM9999999') LIKE '%0' LIMIT 1)
                                      AS 注文数
            , proc.order_price        AS 単価
            , substr(to_char(proc.delivery, 'FM9999/99/99'), 6, 5)            AS 納期
            , plan.kouji_no           AS 工事番号
            , proc.pro_mark           AS 工程
            , proc.mtl_cond           AS 材料条件
            , proc.pro_kubun          AS 工程単価区分
            , proc.order_q            AS 元注文数
            , proc.locate             AS 納入場所
            , proc.kamoku             AS 科目
            , proc.order_ku           AS 発注区分
            , proc.plan_cond          AS 発注計画区分
            , proc.next_pro           AS 次工程
            , trim(substr(mast.name, 1, 8))           AS 発注先名
            , trim(substr(item.midsc, 1, 13))         AS 部品名
            , CASE
                    WHEN trim(item.mzist) = '' THEN '---'         --NULLでなくてスペースで埋まっている場合はこれ！
                    ELSE substr(item.mzist, 1, 8)
              END                     AS 材質
            , CASE
                    WHEN trim(item.mepnt) = '' THEN '---'         --NULLでなくてスペースで埋まっている場合はこれ！
                    ELSE substr(item.mepnt, 1, 8)
              END                     AS 親機種
    FROM
        order_process   AS proc
    LEFT OUTER JOIN
        order_data      AS data
                                using(sei_no, order_no, vendor)
    LEFT OUTER JOIN
        order_plan      AS plan
                                using(sei_no)
    LEFT OUTER JOIN
        vendor_master   AS mast
                                on(proc.vendor = mast.vendor)
    LEFT OUTER JOIN
        miitem          AS item
                                on(proc.parts_no = item.mipn)
    WHERE
        {$where_date}
        AND
        proc.sei_no > 0                 -- 製造用であり
        AND
        to_char(proc.order_no, 'FM9999999') NOT LIKE '%0'     -- 初工程を除外
        AND
        to_char(proc.order_no, 'FM9999999') NOT LIKE '_00000_'    -- 手順書状態の物を除外
        AND
        proc.plan_cond='R'              -- 注文書が予定のもの
        AND
        data.order_no IS NULL           -- 注文書が実際に無い物
        AND
        (SELECT sub_order.order_q - sub_order.cut_siharai FROM order_process AS sub_order WHERE sub_order.sei_no=proc.sei_no AND to_char(sub_order.order_no, 'FM9999999') LIKE '%0' LIMIT 1) > 0
                                        -- 初工程が打切されていない物
        AND
        {$where_div}
    {$orderby}
    OFFSET 0
    LIMIT 1000
";
$res = array();
if (($rows = getResult($query, $res)) < 1) {
    $_SESSION['s_sysmsg'] = '注残データがありません！';
    $view = 'NG';
}

/////////// HTML Header を出力してキャッシュを制御
$menu->out_html_header();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<title><?php echo $menu->out_title() ?></title>
<?php echo $menu->out_css() ?>
<style type='text/css'>
<!--
th {
    font-size:      11.5pt;
    font-weight:    bold;
    font-family:    monospace;
}
table {
    font-size:      11pt;
    font-weight:    normal;
    /* font-family:    monospace; */
}
.item {
    position: absolute;
    /* top:   0px; */
    left:     0px;
}
.msg {
    position: absolute;
    top:  100px;
    left: 350px;
}
.winbox {
    border-style: solid;
    border-width: 1px;
    border-top-color:       #FFFFFF;
    border-left-color:      #FFFFFF;
    border-right-color:     #999999;
    border-bottom-color:    #999999;
}
.winbox_field {
    border-style: solid;
    border-width: 1px;
    border-top-color:       #999999;
    border-left-color:      #999999;
    border-right-color:     #FFFFFF;
    border-bottom-color:    #FFFFFF;
    background-color:#d6d3ce;
}
a {
    color: red;
}
a.link {
    color: blue;
}
a:hover {
    background-color: blue;
    color: white;
}
-->
</style>
<script language='JavaScript'>
<!--
function init() {
}
function winActiveChk() {
    if (document.all) {     // IEなら
        if (document.hasFocus() == false) {     // IE5.5以上で使える
            window.focus();
            return;
        }
        return;
    } else {                // NN ならとワリキッテ
        window.focus();
        return;
    }
    // 使用法 <body onLoad="setInterval('winActiveChk()',100)">
    // <input type='button' value='TEST' onClick="window.opener.location.reload()">
    // parent.Header.関数名() or オブジェクト;
}
function inspection_recourse(order_seq, parts_no, parts_name) {
    if (confirm('部品番号：' + parts_no + '\n\n部品名称：' + parts_name + " の\n\n緊急部品 検査依頼の予約をします。\n\n宜しいですか？")) {
        // 実行します。
        document.inspection_form.order_seq.value = order_seq;
        document.inspection_form.submit();
    } else {
        alert('取消しました。');
    }
}
function order_no_confirm(order_no, parts_no, parts_name) {
    alert('部品番号：' + parts_no + '\n\n部品名称：' + parts_name + " の\n\n注文番号は " + order_no + " です。");
}
function win_open(url) {
    var w = 900;
    var h = 680;
    var left = (screen.availWidth  - w) / 2;
    var top  = (screen.availHeight - h) / 2;
    window.open(url, 'view_win2', 'width='+w+',height='+h+',scrollbars=no,status=no,toolbar=no,location=no,menubar=no,top='+top+',left='+left);
}
// -->
</script>
<form name='inspection_form' method='get' action='inspection_recourse_regist.php' target='_self'>
    <input type='hidden' name='retUrl' value='<?php echo $menu->out_self(), '?' . $_SERVER['QUERY_STRING'] ?>'>
    <input type='hidden' name='order_seq' value=''>
</form>
</head>
<body onLoad='winActiveChk()'>
    <center>
        <?php if ($view != 'OK') { ?>
        <table border='0' class='msg'>
            <tr>
                <td>
                    <b style='color: teal;'>データがありません！</b>
                </td>
            </tr>
        </table>
        <?php } else { ?>
        <!-------------- 詳細データ表示のための表を作成 -------------->
        <table class='item' bgcolor='#d6d3ce'  border='1' cellspacing='0' cellpadding='2'>
           <tr><td> <!-- ダミー(デザイン用) -->
        <table class='winbox_field' width=100% align='center' border='1' cellspacing='0' cellpadding='1'>
            <!--
            <th class='winbox' nowrap width='30'>No</th>
            <?php if ($date == 'OLD') { ?>
            <th class='winbox' nowrap width='45' style='font-size:9.5pt;'>納 期</th>
            <?php } else { ?>
            <th class='winbox' nowrap width='45' style='font-size:9.5pt;'>発注日</th>
            <?php } ?>
            <th class='winbox' nowrap width='60' style='font-size:9.5pt;'>製造番号</th>
            <th class='winbox' nowrap width='80'>部品番号</th>
            <th class='winbox' nowrap width='145'>部品名</th>
            <th class='winbox' nowrap width='85'>材&nbsp;&nbsp;質</th>
            <th class='winbox' nowrap width='90'>親機種</th>
            <th class='winbox' nowrap width='70'>注文数</th>
            <th class='winbox' nowrap width='20' style='font-size:10.5pt;'>工程</th>
            <th class='winbox' nowrap width='130'>発注先名</th>
            -->
        <?php
            $i = 0;
            foreach ($res as $rec) {
                $i++;
                // echo "<tr class='table_font' onDblClick='inspection_recourse(\"{$rec['注文番号']}\",\"{$rec['部品番号']}\",\"{$rec['部品名']}\")'>\n";
                // echo "<tr class='table_font'>\n";
                echo "<tr class='table_font' onDblClick='order_no_confirm(\"{$rec['注文番号']}\",\"{$rec['部品番号']}\",\"{$rec['部品名']}\")'>\n";
                echo "<td class='winbox' align='right'  width='30'  bgcolor='#d6d3ce'>{$i}</td>\n";
                if ($date == 'OLD') {
                    echo "<td class='winbox' align='center' width='45'  bgcolor='#d6d3ce'>{$rec['納期']}</td>\n";
                } else {
                    echo "<td class='winbox' align='center' width='45'  bgcolor='#d6d3ce'>{$rec['発注日']}</td>\n";
                }
                echo "<td class='winbox' align='center' width='60'  bgcolor='#d6d3ce'>{$rec['製造番号']}</td>\n";
                echo "<td class='winbox' align='center' width='80'  bgcolor='#d6d3ce'><a class='link' href='javascript:win_open(\"{$menu->out_action('在庫予定')}?showMenu=CondForm&targetPartsNo=" . urlencode($rec['部品番号']) . "&noMenu=yes\");' target='_self' style='text-decoration:none;'>{$rec['部品番号']}</a></td>\n";
                echo "<td class='winbox' align='left'   width='145' bgcolor='#d6d3ce'>" . mb_convert_kana($rec['部品名'], 'k') . "</td>\n";
                echo "<td class='winbox' align='left'   width='85'  bgcolor='#d6d3ce'>" . mb_convert_kana($rec['材質'], 'k') . "</td>\n";
                echo "<td class='winbox' align='left'   width='90'  bgcolor='#d6d3ce'>" . mb_convert_kana($rec['親機種'], 'k') . "</td>\n";
                echo "<td class='winbox' align='right'  width='70'  bgcolor='#d6d3ce'>" . number_format($rec['注文数'], 0) . "</td>\n";
                echo "<td class='winbox' align='center' width='20'  bgcolor='#d6d3ce'>{$rec['工程']}</td>\n";
                echo "<td class='winbox' align='left'   width='130' bgcolor='#d6d3ce'>{$rec['発注先名']}</td>\n";
                echo "</tr>\n";
            }
        ?>
        </table> <!----- ダミー End ----->
            </td></tr>
        </table>
        <?php } ?>
    </center>
</body>
</html>
<?php echo $menu->out_alert_java()?>
<?php $_SESSION['s_sysmsg'] = ''; ?>
<?php ob_end_flush(); // 出力バッファをgzip圧縮 END ?>
