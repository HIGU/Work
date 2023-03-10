<?php
////////////////////////////////////////////////////////////////////////////////////////////
// 集荷納期別納入予定の照会(検査の仕事量把握) 明細をウィンドウ表示   Listフレーム         //
// Copyright (C) 2017-2017 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp                  //
// Changed history                                                                        //
// 2017/07/27 Created  order_collect_Main_Body.php(order_details_Main_Body.phpを改造)     //
////////////////////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
ini_set('max_execution_time', 60);          // 最大実行時間=60秒 WEB CGI版
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
$menu->set_title('集荷納期明細の照会');     // タイトルを入れないとIEの一部のバージョンで表示できない不具合あり

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
/*
switch ($div) {
case 'C':       // C全体
    $where_div = "data.parts_no like 'C%' and proc.locate != '52   '";
    break;
case 'SC':      // C特注
    $where_div = "data.parts_no like 'C%' and data.kouji_no like '%SC%' and proc.locate != '52   '";
    break;
case 'CS':      // C標準
    $where_div = "data.parts_no like 'C%' and data.kouji_no not like '%SC%' and proc.locate != '52   '";
    break;
case 'L':       // L全体
    $where_div = "data.parts_no like 'L%' and proc.locate != '52   '";
    break;
case 'T':       // T全体
    $where_div = "data.parts_no like 'T%' and proc.locate != '52   '";
    break;
case 'F':       // F全体
    $where_div = "data.parts_no like 'F%' and proc.locate != '52   '";
    break;
case 'A':       // TNK全体
    $where_div = "(data.parts_no like 'C%' or data.parts_no like 'L%' or data.parts_no like 'T%' or data.parts_no like 'F%') and proc.locate != '52   '";
    break;
case 'N':       // NKカプラ
    $where_div = "(data.parts_no like 'C%' or data.parts_no like 'L%' or data.parts_no like 'T%' or data.parts_no like 'F%') and proc.locate = '52   '";
    break;
case 'NKB':     // NKB
    $where_div = "plan.locate = '14'";
    break;
}
*/

if ($div == 'C') $where_div = "uke_no > '500000' AND data.parts_no LIKE 'C%'";
if ($div == 'SC') $where_div = "uke_no > '500000' AND data.parts_no LIKE 'C%' AND data.kouji_no LIKE '%SC%'";
if ($div == 'CS') $where_div = "uke_no > '500000' AND data.parts_no LIKE 'C%' AND data.kouji_no NOT LIKE '%SC%'";
if ($div == 'L') $where_div = "uke_no > '500000' AND data.parts_no LIKE 'L%'";
if ($div == 'T') $where_div = "uke_no > '500000' AND data.parts_no LIKE 'T%'";
if ($div == 'F') $where_div = "uke_no > '500000' AND data.parts_no LIKE 'F%'";
if ($div == 'A') $where_div = "uke_no > '500000' AND (data.parts_no LIKE 'C%' or data.parts_no LIKE 'L%' or data.parts_no LIKE 'T%' or data.parts_no LIKE 'F%')";
if ($div == 'N') $where_div = "uke_no <= '500000' AND uke_no >= '400000' AND (data.parts_no LIKE 'C%' or data.parts_no LIKE 'L%' or data.parts_no LIKE 'T%' or data.parts_no LIKE 'F%')";
if ($div == 'NKB') $where_div = "uke_no > '500000' AND plan.locate = '14'";

////////// 日付で共通の where句を生成
if ($date == 'OLD') {
    //$where_date = 'proc.delivery <= ' . date('Ymd', mktime() - 86400) . 'and proc.delivery >= 0';   // 納期遅れ全て表示
    //$where_date = 'proc.delivery <= ' . date('Ymd', mktime() - 86400) . 'and proc.delivery >= ' . date('Ymd', mktime() - (86400*200));
    //$orderby = 'order by proc.delivery ASC, data.date_issue ASC';
    $where_date = 'plan.last_delv <= ' . date('Ymd', time() - (86400)*3);   // ３日前以上全て表示
    $orderby = 'order by plan.last_delv ASC, data.date_issue ASC';
} else {
    //$where_date = "proc.delivery = {$date}";
    //$orderby = 'order by data.vendor ASC, proc.delivery ASC, data.date_issue ASC';
    $where_date = "plan.last_delv = {$date}";
    $orderby = 'order by data.vendor ASC, plan.last_delv ASC, data.date_issue ASC';
}

$view = 'OK';   // スタートはOKで行う

////////// 共通SQL文を生成
$query = "select    data.order_seq          AS 発行連番
                  , substr(to_char(data.date_issue, 'FM9999/99/99'), 6, 5)          AS 発行日
                  , data.pre_seq            AS 前の連番
                  , to_char(data.sei_no,'FM0000000')        AS 製造番号
                  , data.order_no           AS 注文番号
                  , data.parts_no           AS 部品番号
                  , data.vendor             AS 発注先コード
                  , data.order_q            AS 注文数
                  , data.order_price        AS 単価
                  , substr(to_char(plan.last_delv, 'FM9999/99/99'), 6, 5)            AS 納期
                  , data.kouji_no           AS 工事番号
                  , proc.pro_mark           AS 工程
                  , proc.mtl_cond           AS 材料条件
                  , proc.pro_kubun          AS 工程単価区分
                  , proc.order_date         AS 発注日
                  , proc.order_q            AS 元注文数
                  , proc.locate             AS 納入場所
                  , proc.kamoku             AS 科目
                  , proc.order_ku           AS 発注区分
                  , proc.plan_cond          AS 発注計画区分
                  , proc.next_pro           AS 次工程
                  , trim(substr(mast.name, 1, 8))           AS 発注先名
                  , trim(mast.name)                         AS vendor_name
                  , trim(substr(item.midsc, 1, 13))         AS 部品名
                  , CASE
                          WHEN trim(item.mzist) = '' THEN '---'         --NULLでなくてスペースで埋まっている場合はこれ！
                          ELSE substr(item.mzist, 1, 8)
                    END                     AS 材質
                  , CASE
                          WHEN trim(item.mepnt) = '' THEN '---'         --NULLでなくてスペースで埋まっている場合はこれ！
                          ELSE substr(item.mepnt, 1, 8)
                    END                     AS 親機種
            from
                order_data      AS data
            LEFT OUTER JOIN
                    acceptance_kensa    AS ken  on(data.order_seq=ken.order_seq)
            left outer join
                order_process   AS proc
                                        using(sei_no, order_no, vendor)
            LEFT OUTER JOIN
                order_plan      AS plan     USING (sei_no)
            left outer join
                vendor_master   AS mast
                                        on(data.vendor = mast.vendor)
            left outer join
                miitem          AS item
                                        on(data.parts_no = item.mipn)
            where
                {$where_date}
                and
                ken_date <= 0       -- 未検収分
                and
                data.sei_no > 0     -- 製造用であり
                and
                (data.order_q - data.cut_genpin) > 0  -- 打切されていない物
                and
                ( (ken.end_timestamp IS NULL) OR (ken.end_timestamp >= (CURRENT_TIMESTAMP - interval '10 minute')) )
                and
                {$where_div}
            {$orderby}
            offset 0
            limit 1000
";
$res = array();
if (($rows = getResult($query, $res)) < 1) {
    $_SESSION['s_sysmsg'] = '注残データがありません！';
    $view = 'NG';
} else {
    $num_res = count($res);
}

// コメントの登録ロジック
if(isset($_POST['comment_input'])) {
    $comment  = array();                // コメント
    $sei_no   = array();                // 製造No.
    $parts_no = array();                // 部品No.
    $comment  = $_POST['comment'];
    $sei_no   = $_POST['sei_no'];
    $parts_no = $_POST['parts_no'];
    $num = count($sei_no) + 1;
    for($r=1; $r<$num; $r++) {
        $query = sprintf("SELECT comment FROM order_details_comment WHERE sei_no='%s' AND parts_no='%s'", $sei_no[$r], $parts_no[$r]);
        $res_chk = array();
        if ( getResult($query, $res_chk) > 0 ) {    // 登録あり UPDATE 更新
            $query = sprintf("UPDATE order_details_comment SET comment='%s', last_date=CURRENT_TIMESTAMP, last_user='%s' WHERE sei_no='%s' AND parts_no='%s'", $comment[$r], $_SESSION['User_ID'], $sei_no[$r], $parts_no[$r]);
            if (query_affected($query) <= 0) {
                $_SESSION['s_sysmsg'] = "コメントの変更失敗！";      // .= に注意
                $msg_flg = 'alert';
            } else {
                $_SESSION['s_sysmsg'] = "コメントを登録しました"; // .= に注意
            }
        } else {                                    // 登録なし INSERT 新規   
            $query = sprintf("INSERT INTO order_details_comment (sei_no, parts_no, comment, last_date, last_user)
                              VALUES ('%s', '%s', '%s', CURRENT_TIMESTAMP, '%s')",
                                $sei_no[$r], $parts_no[$r], $comment[$r], $_SESSION['User_ID']);
            if (query_affected($query) <= 0) {
                $_SESSION['s_sysmsg'] = "コメントの追加に失敗！";      // .= に注意
                $msg_flg = 'alert';
            } else {
                $_SESSION['s_sysmsg'] = "コメントを追加しました！";    // .= に注意
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
    window.focus();
    return;
    /***** 以下の処理はsetInterval()を使用した場合に使う *****/
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
function vendor_code_view(vendor, vendor_name) {
    alert('発注先コード：' + vendor + '\n\n発注先名：' + vendor_name + '\n\n');
}
function input_details(comment) {
        alert('テスト' + comment + '\n\n');
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
<form name='inspection_form' method='get' action='../inspection_recourse_regist.php' target='_self'>
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
        <table class='item' width=100% bgcolor='#d6d3ce'  border='1' cellspacing='0' cellpadding='1'>
           <tr><td> <!-- ダミー(デザイン用) -->
        <table class='winbox_field' width=100% align='center' border='1' cellspacing='0' cellpadding='3'>
        <form name='comment_form' action="" method="post">
        <?php
            $i = 0;
            foreach ($res as $rec) {
                $i++;
                echo "<tr class='table_font' onDblClick='inspection_recourse(\"{$rec['発行連番']}\",\"{$rec['部品番号']}\",\"{$rec['部品名']}\")'>\n";
                /*
                if ($date == 'OLD') {
                */
                    // 製造No.と部品番号よりコメントを取得
                    $query_c = sprintf("SELECT comment FROM order_details_comment WHERE sei_no='%s' AND parts_no='%s'", $rec['製造番号'], $rec['部品番号']);
                    $res_chk_c = array();
                    if ( $rows_c = getResult($query_c, $res_c) < 1 ) {    // 登録なし
                        $comment = "";
                    } else {
                        $comment = $res_c[0][0];
                    }
                    echo "<td class='winbox' align='right'  width=' 4%'  bgcolor='#d6d3ce'>{$i}</td>\n";
                    echo "<td class='winbox' align='center' width=' 5%'  bgcolor='#d6d3ce'>{$rec['納期']}</td>\n";
                    $query = "SELECT substr(to_char(require_date, 'FM9999/99/99'), 6, 5) AS 必要日 FROM parts_minimum_require_date('{$rec['部品番号']}', '集荷日') LIMIT 1";
                    $require_date = '-';
                    getUniResult($query, $require_date);
                    if ($require_date == '99/99') $require_date = 'OK';
                    /*
                    echo "<td class='winbox' align='center' width=' 6%'  bgcolor='#d6d3ce' onClick='win_open(\"{$menu->out_action('在庫予定')}?showMenu=CondForm&noMenu=yes&requireDate=yes&targetPartsNo=" . urlencode($rec['部品番号']) . "\");'>\n";
                    echo "    <a class='link' href='javascript:void(0);' target='_self' style='text-decoration:none;'>{$require_date}</a></td>\n";
                    */
                    echo "<td class='winbox' align='center' width=' 7%'  bgcolor='#d6d3ce'>{$rec['製造番号']}</td>\n";
                    echo "<td class='winbox' align='center' width=' 8%'  bgcolor='#d6d3ce' onClick='win_open(\"{$menu->out_action('在庫予定')}?showMenu=CondForm&noMenu=yes&targetPartsNo=" . urlencode($rec['部品番号']) . "\");'>\n";
                    echo "    <a class='link' href='javascript:void(0);' target='_self' style='text-decoration:none;'>{$rec['部品番号']}</a></td>\n";
                    echo "<td class='winbox' align='left'   width='13%' bgcolor='#d6d3ce'>" . mb_convert_kana($rec['部品名'], 'k') . "</td>\n";
                    echo "<td class='winbox' align='left'   width=' 8%'  bgcolor='#d6d3ce'>" . mb_convert_kana($rec['材質'], 'k') . "</td>\n";
                    echo "<td class='winbox' align='left'   width=' 8%'  bgcolor='#d6d3ce'>" . mb_convert_kana($rec['親機種'], 'k') . "</td>\n";
                    echo "<td class='winbox' align='right'  width=' 6%'  bgcolor='#d6d3ce'>" . number_format($rec['注文数'], 0) . "</td>\n";
                    echo "<td class='winbox' align='center' width=' 3%'  bgcolor='#d6d3ce' style='font-size:9.5pt;'>{$rec['工程']}</td>\n";
                    echo "<td class='winbox' align='left'   width='13%' bgcolor='#d6d3ce' onClick='vendor_code_view(\"{$rec['発注先コード']}\",\"{$rec['vendor_name']}\")'>{$rec['発注先名']}</td>\n";
                    if ($comment=="") {                                 // コメントの登録がない場合(入力フォームを表示)
                        echo "<td class='winbox' align='left'   width='25%' bgcolor='#d6d3ce'>
                                    <input type='text' name='comment[". $i ."]' size='20' maxlength='10' value='". $comment . "' style='text-align:left; font-size:12pt; font-weight:bold;'>
                                    <input type='hidden' name='sei_no[". $i ."]' value='{$rec['製造番号']}'>
                                    <input type='hidden' name='parts_no[". $i ."]' value='{$rec['部品番号']}'>
                            </td>\n";
                        echo "</tr>\n";
                    } else if (isset($_POST['comment_change'])){        // コメントの修正ボタンが押された時(入力フォームを表示)
                        echo "<td class='winbox' align='left'   width='25%' bgcolor='#d6d3ce'>
                                    <input type='text' name='comment[". $i ."]' size='20' maxlength='10' value='". $comment . "' style='text-align:left; font-size:12pt; font-weight:bold;'>
                                    <input type='hidden' name='sei_no[". $i ."]' value='{$rec['製造番号']}'>
                                    <input type='hidden' name='parts_no[". $i ."]' value='{$rec['部品番号']}'>
                            </td>\n";
                        echo "</tr>\n";
                    } else {                                            // それ以外(コメントがすでに登録されている場合)(入力できないようにする)
                        echo "<td class='winbox' align='left' width='25%' bgcolor='#d6d3ce'>{$comment}</td>
                                    <input type='hidden' name='comment[". $i ."]' value='{$comment}'>
                                    <input type='hidden' name='sei_no[". $i ."]' value='{$rec['製造番号']}'>
                                    <input type='hidden' name='parts_no[". $i ."]' value='{$rec['部品番号']}'>";
                        echo "</tr>\n";
                    }
                /*
                } else {
                    echo "<td class='winbox' align='right'  width=' 5%'  bgcolor='#d6d3ce'>{$i}</td>\n";
                    echo "<td class='winbox' align='center' width=' 7%'  bgcolor='#d6d3ce'>{$rec['発行日']}</td>\n";
                    echo "<td class='winbox' align='center' width='10%'  bgcolor='#d6d3ce'>{$rec['製造番号']}</td>\n";
                    echo "<td class='winbox' align='center' width='13%'  bgcolor='#d6d3ce' onClick='win_open(\"{$menu->out_action('在庫予定')}?showMenu=CondForm&noMenu=yes&targetPartsNo=" . urlencode($rec['部品番号']) . "\");'>\n";
                    echo "    <a class='link' href='javascript:void(0);' target='_self' style='text-decoration:none;'>{$rec['部品番号']}</a></td>\n";
                    echo "<td class='winbox' align='left'   width='16%' bgcolor='#d6d3ce'>" . mb_convert_kana($rec['部品名'], 'k') . "</td>\n";
                    echo "<td class='winbox' align='left'   width='10%'  bgcolor='#d6d3ce'>" . mb_convert_kana($rec['材質'], 'k') . "</td>\n";
                    echo "<td class='winbox' align='left'   width='10%'  bgcolor='#d6d3ce'>" . mb_convert_kana($rec['親機種'], 'k') . "</td>\n";
                    echo "<td class='winbox' align='right'  width='10%'  bgcolor='#d6d3ce'>" . number_format($rec['注文数'], 0) . "</td>\n";
                    echo "<td class='winbox' align='center' width=' 4%'  bgcolor='#d6d3ce'>{$rec['工程']}</td>\n";
                    echo "<td class='winbox' align='left'   width='15%' bgcolor='#d6d3ce' onClick='vendor_code_view(\"{$rec['発注先コード']}\",\"{$rec['vendor_name']}\")'>{$rec['発注先名']}</td>\n";
                    echo "</tr>\n";
                }
                */
            }
        /*
        if ($date == 'OLD') {       // 納期遅れの場合コメント登録・修正ボタンを表示する
        */
        ?>
        <td colspan='10'>　</td>
        <td>
            <input type='submit' class='entry_font' name='comment_input' value='コメント登録'>
            <input type='submit' class='entry_font' name='comment_change' value='コメント修正'>
        </td>
        </form>
        <?php
        /*
        }
        */
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
