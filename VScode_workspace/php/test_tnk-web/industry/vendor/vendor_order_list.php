<?php
//////////////////////////////////////////////////////////////////////////////
// 協力工場別注残リストの照会 (ポップアップウィンドウ版）                   //
// Copyright (C) 2005-2015 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2005/04/26 Created   vendor_order_list.php                               //
// 2005/05/06 SQLの条件を見直し ヘッダーと明細のwhere条件 ＆ 検査中の追加   //
// 2005/05/09 order_dataの条件にand ken_date<=0を追加 注文数を注残数へ変更  //
// 2005/05/11 menu_form.css を使用しないように変更 メールに添付できるように //
// 2005/05/12 order by plan_cond ASC 最優先に追加 先の納期を153日→200日へ  //
//            ↑ 注文書・内示中・予定→O,R,P → order by 発注計画区分 ASCへ //
// 2005/05/17 sei_no and vendor and ken_data→sei_no and order_no and vendor//
// 2005/05/23 検査中→検中/済に変更SQL文のsubqueryを変更検収入力の時間差対応//
// 2005/05/25 上記を根本的に同期から対応した事により 元に戻した。           //
// 2005/05/26 検査不良に対応するため ken_date を where句に追加              //
//                                          必然的に検査と同期をとる事になる//
// 2007/03/05 項目の製造番号がwrapしてしまうためfont-size:9.5pt;→9.0ptへ   //
//            部品番号クリックで在庫予定照会・経歴照会にリンクを追加        //
// 2015/10/19 製品グループにT=ツールを追加（部品No.１文字目がT）            //
//            生管小松依頼により、LからはTを除外しない(T部品もL事業部) 大谷 //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮
session_start();                            // ini_set()の次に指定すること Script 最上行
require_once ('../../function.php');        // TNK 全共通 function (define.phpを含む)
require_once ('../../MenuHeader.php');      // TNK 全共通 menu class
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

$view = 'OK';   // スタートはOKで行う

///////// パラメーターチェック
if (isset($_REQUEST['vendor'])) {
    $vendor = $_REQUEST['vendor'];
} else {
    $vendor = '00485';                           // Default(全て)ありえないが
    // $view = 'NG';
}
if (isset($_REQUEST['div'])) {
    $div = $_REQUEST['div'];
} else {
    $div = 'L';                              // Default(全て)
}
if (isset($_REQUEST['plan_cond'])) {
    $plan_cond = $_REQUEST['plan_cond'];
} else {
    $plan_cond = '';                        // Default(全て)
}

//////// 協力工場名の取得
$query = "select name from vendor_master where vendor='{$vendor}'";
if (getUniResult($query, $vendor_name) < 1) {
    $_SESSION['s_sysmsg'] = "発注先コードが無効です！";
    $vendor_name = '未登録';
    $view = 'NG';
}

//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title("{$vendor_name} 注残リスト");  // タイトルを入れないとIEの一部のバージョンで表示できない不具合あり

//////// 表題の設定
if ($div == '') $div_name = '全て'; else $div_name = $div;
if ($plan_cond == '') $cond_name = '全て'; else $cond_name = $plan_cond;
$menu->set_caption("コード：{$vendor}　ベンダー名：{$vendor_name}　製品グループ：{$div_name}　発注区分：{$cond_name}");

////////// 日付で共通の where句を生成
// 過去は200日前から153(５ヶ月)→184日(６ヶ月)先まで→200日へ変更
$where_date = 'proc.delivery <= ' . date('Ymd', mktime() + (86400*300)) . ' and proc.delivery >= ' . date('Ymd', mktime() - (86400*300));

//////// 事業部から共通な where句を設定
switch ($div) {
case 'C':       // C全体
    $where_div = "proc.vendor='{$vendor}' and plan.div='C' and proc.locate != '52   '";
    break;
case 'SC':      // C特注
    $where_div = "proc.vendor='{$vendor}' and plan.div='C' and plan.kouji_no like '%SC%' and proc.locate != '52   '";
    break;
case 'CS':      // C標準
    $where_div = "proc.vendor='{$vendor}' and plan.div='C' and plan.kouji_no not like '%SC%' and proc.locate != '52   '";
    break;
case 'L':       // L全体
    $where_div = "proc.vendor='{$vendor}' and plan.div='L' and proc.locate != '52   '";
    break;
case 'T':       // T全体
    $where_div = "proc.vendor='{$vendor}' and proc.parts_no like 'T%' and proc.locate != '52   '";
    break;
case 'F':       // F全体
    $where_div = "proc.vendor='{$vendor}' and plan.div='F' and proc.locate != '52   '";
    break;
case 'A':       // TNK全体
    $where_div = "(proc.vendor='{$vendor}' and plan.div='C' or plan.div='L' or plan.div='T' or plan.div='F') and proc.locate != '52   '";
    break;
case 'N':       // NKカプラ
    $where_div = "(proc.vendor='{$vendor}' and plan.div='C' or plan.div='L' or plan.div='T' or plan.div='F') and proc.locate = '52   '";
    break;
default:        // 全製品グループ '' ' ' の違いがあったため default へ変更
    $where_div = "proc.vendor='{$vendor}' and proc.locate != '52   '";
    break;
}
//////// 発注計画区分から共通な where句を設定
switch ($plan_cond) {
case 'P':       // 予定
case 'R':       // 内示中(リリース)
case 'O':       // 注文書発行済み
    $where_cond = "proc.plan_cond='{$plan_cond}'";
    break;
default:
    $where_cond = "proc.plan_cond != '{$plan_cond}'";
    break;
}

if ($view == 'OK') {
////////// 共通SQL文を生成
$query = "select    to_char(proc.sei_no,'FM0000000')        AS 製造番号
                  , to_char(proc.order_no,'FM000000-0')     AS 注文番号
                  , proc.parts_no                           AS 部品番号
                  , proc.vendor                             AS 発注先コード
                  , CASE
                        WHEN proc.order_q = 0 THEN trim(to_char((plan.order_q - plan.utikiri - plan.nyuko), '9,999,999'))
                        ELSE trim(to_char((proc.order_q - proc.siharai - proc.cut_siharai), '9,999,999'))
                    END                                     AS 注残数
                  , (select CASE
                                WHEN (sum(uke_q)-sum(siharai)) IS NULL THEN '0'
                                ELSE trim(to_char(sum(uke_q)-sum(siharai), '9,999,999'))    --検中
                            END
                        from
                            order_data
                        where sei_no=proc.sei_no and order_no=proc.order_no and vendor=proc.vendor and ken_date<=0
                    )                                       AS 検査中
                  , proc.order_price                        AS 単価
                  , substr(to_char(proc.delivery, 'FM9999/99/99'), 6, 5)
                                                            AS 納期
                  , plan.kouji_no                           AS 工事番号
                  , proc.pro_mark                           AS 工程
                  , proc.mtl_cond                           AS 材料条件
                  , proc.pro_kubun                          AS 工程単価区分
                  , proc.order_date                         AS 発注日
                  , proc.order_q                            AS 元注文数
                  , proc.locate                             AS 納入場所
                  , proc.kamoku                             AS 科目
                  , proc.order_ku                           AS 発注区分
                  , CASE
                        WHEN proc.plan_cond = 'P' THEN '予　定'
                        WHEN proc.plan_cond = 'O' THEN '注文書'
                        WHEN proc.plan_cond = 'R' THEN '内示中'
                        ELSE proc.plan_cond
                    END                                     AS 発注計画区分
                  , proc.next_pro                           AS 次工程
                  , CASE
                        WHEN proc.next_pro != 'END..' THEN
                            (select substr(name, 1, 6) from vendor_master where vendor=proc.next_pro limit 1)
                        ELSE proc.next_pro
                    END                                     AS 次工程名
                  , trim(substr(mast.name, 1, 8))           AS 発注先名
                  , trim(substr(item.midsc, 1, 11))         AS 部品名
                  , CASE
                          WHEN trim(item.mzist) = '' THEN '---'         --NULLでなくてスペースで埋まっている場合はこれ！
                          ELSE substr(item.mzist, 1, 8)
                    END                                     AS 材質
                  , CASE
                          WHEN trim(item.mepnt) = '' THEN '---'         --NULLでなくてスペースで埋まっている場合はこれ！
                          ELSE substr(item.mepnt, 1, 8)
                    END                                     AS 親機種
            from
                order_process   AS proc
            left outer join
                order_plan      AS plan
                                        using(sei_no)
            left outer join
                vendor_master   AS mast
                                        on(proc.vendor = mast.vendor)
            left outer join
                miitem          AS item
                                        on(proc.parts_no = item.mipn)
            where
                {$where_date}
                and
                {$where_div}
                and
                (plan.order_q - plan.utikiri - plan.nyuko) > 0
                    -- ヘッダーに注残がある物で
                and
                ( (proc.order_q = 0) OR ((proc.order_q - proc.siharai - proc.cut_siharai > 0)) )
                    -- 次工程か？ 又は自分の工程に注残がある物
                and
                {$where_cond}
            order by 発注計画区分 ASC, proc.delivery ASC, proc.parts_no ASC
            offset 0
            limit 1000
";
$res = array();
if (($rows = getResult($query, $res)) < 1) {
    $_SESSION['s_sysmsg'] .= '注残データがありません！';
    $view = 'NG';
}
} // if end

/////////// HTML Header を出力してキャッシュを制御
$menu->out_html_header();
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="Content-Style-Type" content="text/css">
<title><?= $menu->out_title() ?></title>
<?php // $menu->out_css() ?>
<style type='text/css'>
<!--
body {
    margin:        0%;
}
form {
    margin:        0%;
}
.caption_font {
    font-size:              11.5pt;
    font-weight:            bold;
}
th {
    font-size:      11.5pt;
    font-weight:    bold;
    font-family:    monospace;
    color:          blue;
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
    left:   0px;
}
.winbox {
    border-style: solid;
    border-width: 1px;
    border-top-color:       #FFFFFF;
    border-left-color:      #FFFFFF;
    border-right-color:     #bdaa90;
    border-bottom-color:    #bdaa90;
}
.winbox_field {
    border-style: solid;
    border-width: 1px;
    border-top-color:       #bdaa90;
    border-left-color:      #bdaa90;
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
    <input type='hidden' name='retUrl' value='<?= $menu->out_self(), '?' . $_SERVER['QUERY_STRING'] ?>'>
    <input type='hidden' name='order_seq' value=''>
</form>
</head>
<body onLoad='winActiveChk()'>
    <center>
        <?php if ($view != 'OK') { ?>
        <table border='0' class='msg' width='100%'>
            <tr>
                <td align='center'>
                    <b style='color: teal;'>データがありません！</b>
                    <br>　<br>
                    発注先コード：<?=$vendor, "　製品グループ：{$div_name}　発注区分：{$cond_name}"?>
                </td>
            </tr>
            <tr>
                <td>　</td>
            </tr>
            <tr>
                <td align='center'>
                    <input type='button' name='close' value='閉じる' onClick='JavaScript:window.close()'>
                </td>
            </tr>
        </table>
        <?php } else { ?>
        <!-------------- 詳細データ表示のための表を作成 -------------->
        <table class='item' bgcolor='#d6d3ce'  border='1' cellspacing='0' cellpadding='2'>
           <tr><td> <!-- ダミー(デザイン用) -->
        <table class='winbox_field' width=100% align='center' border='1' cellspacing='0' cellpadding='1'>
            <div class='caption_font' align='right'><?=$menu->out_caption()?></div>
            <th class='winbox' nowrap width='30'>No</th>
            <th class='winbox' nowrap width='45' style='font-size:9.5pt;'>納 期</th>
            <th class='winbox' nowrap width='60' style='font-size:9.0pt;'>製造番号</th>
            <th class='winbox' nowrap width='80'>部品番号</th>
            <th class='winbox' nowrap width='105'>部品名</th><!-- マイナス40 -->
            <th class='winbox' nowrap width='85'>材&nbsp;&nbsp;質</th>
            <th class='winbox' nowrap width='90'>親機種</th>
            <th class='winbox' nowrap width='60'>注残数</th>
            <th class='winbox' nowrap width='60'>検査中</th>
            <th class='winbox' nowrap width='20' style='font-size:10.5pt;'>工程</th>
            <th class='winbox' nowrap width='50' style='font-size:10.5pt;'>区分</th>
            <th class='winbox' nowrap width='80'>次工程名</th>
        <?php
            $i = 0;
            foreach ($res as $rec) {
                $i++;
                // echo "<tr class='table_font' onDblClick='inspection_recourse(\"{$rec['発行連番']}\",\"{$rec['部品番号']}\",\"{$rec['部品名']}\")'>\n";
                echo "<tr class='table_font'>\n";
                echo "<td class='winbox' align='right'  width='30'  bgcolor='#d6d3ce'>{$i}</td>\n";
                echo "<td class='winbox' align='center' width='45'  bgcolor='#d6d3ce'>{$rec['納期']}</td>\n";
                echo "<td class='winbox' align='center' width='60'  bgcolor='#d6d3ce'>{$rec['製造番号']}</td>\n";
                echo "<td class='winbox' align='center' width='80'  bgcolor='#d6d3ce'><a class='link' href='javascript:void(0)' onClick='win_open(\"{$menu->out_action('在庫予定')}?showMenu=CondForm&targetPartsNo=" . urlencode($rec['部品番号']) . "&noMenu=yes\");' target='_self' style='text-decoration:none;'>{$rec['部品番号']}</a></td>\n";
                echo "<td class='winbox' align='left'   width='105' bgcolor='#d6d3ce'>" . mb_convert_kana($rec['部品名'], 'k') . "</td>\n";
                echo "<td class='winbox' align='left'   width='85'  bgcolor='#d6d3ce'>" . mb_convert_kana($rec['材質'], 'k') . "</td>\n";
                echo "<td class='winbox' align='left'   width='90'  bgcolor='#d6d3ce'>" . mb_convert_kana($rec['親機種'], 'k') . "</td>\n";
                echo "<td class='winbox' align='right'  width='60'  bgcolor='#d6d3ce'>{$rec['注残数']}</td>\n";
                echo "<td class='winbox' align='right'  width='60'  bgcolor='#d6d3ce'>{$rec['検査中']}</td>\n";
                echo "<td class='winbox' align='center' width='20'  bgcolor='#d6d3ce'>{$rec['工程']}</td>\n";
                echo "<td class='winbox' align='center' width='50'  bgcolor='#d6d3ce'>{$rec['発注計画区分']}</td>\n";
                echo "<td class='winbox' align='left'   width='80' bgcolor='#d6d3ce'>{$rec['次工程名']}</td>\n";
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
<?=$menu->out_alert_java()?>
<?php $_SESSION['s_sysmsg'] = ''; ?>
<?php ob_end_flush(); // 出力バッファをgzip圧縮 END ?>
