<?php
//////////////////////////////////////////////////////////////////////////////
// 緊急 部品 検査 依頼 照会及びメンテナンス      Listフレーム               //
// Copyright (C) 2004-2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2004/10/19 Created  inspection_recourse_List.php                         //
// 2004/10/20 納入場所の除外 and proc.locate != '52   'を削除               //
// 2004/10/21 mb_substr(mb_convert_kana($rec['parts_name'], 'k'), 0, 14)へ  //
//            PostgreSQL は replace(midsc, ' ', '')のみに変更               //
// 2004/10/22 材質にもカタカナを使用しているものがあるためmb_substr()を適用 //
// 2004/10/29 谷口さんが抜けているのを修正                                  //
// 2004/11/10 NK分だけuke_noの条件を追加 400000～500000 栃木で検査する場合  //
// 2004/11/24 訂正・取消を押した時にorder_schedule_Listから自分に訂正       //
// 2004/12/06 次工程のSQL文に不具合があったのを修正(エラーにならない不具合) //
//(SELECT substr(mast.name, 1, 8) FROM vendor_master WHERE proc.next_pro=vendor)
//               ↓                                                         //
//(SELECT substr(name, 1, 8) FROM vendor_master WHERE vendor=proc.next_pro) //
// 2004/12/29 検査希望日の日付抽出をtnk_funcのday_off()関数を使うように変更 //
// 2005/02/23 検査開始された部品はダブルクリックで検査開始・終了日時を表示  //
// 2005/03/10 菅谷さんを許可 及び退社された人のメンテ                       //
// 2005/05/26 WHERE句に acceptance_kensa の end_timestamp 条件を追加        //
//               発注先名をクリックすると発注先コードを照会できる機能を追加 //
// 2005/07/22 希望日をSELECTした時に変更した order_seqに戻らないのを修正    //
//            user_check()を$order_seqへの代入の後へ変更 マークを表示させる //
//            priority のインクリメント・デクリメント ロジックを変更        //
// 2006/04/13 人の移動に伴う権限変更(深見・菊地・吉成・添田・五十嵐)        //
// 2006/04/20 権限関係を共通 function へ変更 order_function.php             //
// 2006/07/04 検査開始時にuid(社員番号)の登録を追加 acceptance_kensa        //
// 2006/08/02 製品グループにＮＫＢを追加 そのため SQLに order_plan 追加     //
// 2006/08/31 検査希望日の範囲を10日間→17日間へ変更(特注課依頼による)      //
// 2006/10/26 Windows2000とIEの組合せでセレクトボックス内のスクロールバーの //
//            ボタンをDBクリックするとセルをDBクリックした事になる現象へ対応//
//            <tr onDblClick → <tr>へ  <td{$inspec}へ変数を埋め込んで対応  //
// 2007/01/18 検査中断の表示機能追加 hold_flg で検索                        //
// 2007/01/22 検査のキャンセルロジックを order_function.php(共通化)へ変更   //
//            受入検査 開始キャンセル時に中断履歴も削除するため             //
// 2007/02/22 部品番号にリンクを追加して在庫経歴・予定照会POPUP Windowを表示//
// 2007/04/18 <a href='javascript:win_open(..)'→ <a href='javascript:void()//
//            onClick='win_open(...)'の書式へ変更により部品番号の#1等に対応 //
// 2007/10/25 E_STRICT → E_ALL | E_STRICT へ   and → AND へ               //
// 2007/10/26 SQLのWHERE区最適化 以下のように変更                           //
//           (CURRENT_TIMESTAMP - ken.end_timestamp) <= interval '10 minute'//
//                                      ↓                                  //
//          (ken.end_timestamp >= (CURRENT_TIMESTAMP - interval '10 minute')//
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug 用
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮
session_start();                            // ini_set()の次に指定すること Script 最上行
require_once ('../../function.php');        // TNK 全共通 function (define.phpを含む)
require_once ('../../tnk_func.php');        // TNK 専用 function
require_once ('../../MenuHeader.php');      // TNK 全共通 menu class
require_once ('order_function.php');        // order 関係の共通 function
access_log();                               // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader();                   // 認証チェック0=一般以上 戻り先=セッションより タイトル未設定

////////////// サイト設定
$menu->set_site(30, 999);                   // site_index=30(生産メニュー) site_id=999(未定)
////////////// target設定
$menu->set_target('_parent');               // フレーム版の戻り先はtarget属性が必須

//////////// 自分をフレーム定義に変える
// $menu->set_self(INDUST . 'order/order_schedule.php');
//////////// 呼出先のaction名とアドレス設定
$menu->set_action('在庫予定',   INDUST . 'parts/parts_stock_plan/parts_stock_plan_Main.php');
$menu->set_action('在庫経歴',   INDUST . 'parts/parts_stock_history/parts_stock_view.php');
// $menu->set_action('予定明細', INDUST . 'order/order_detailes.php');
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('緊急部品検査依頼の照会');     // タイトルを入れないとIEの一部のバージョンで表示できない不具合あり

///////// パラメーターチェックと設定
$div = $_SESSION['div'];                    // Default(セッションから)
$select = 'inspc';                          // 検査依頼
if (isset($_REQUEST['order_seq'])) {
    $order_seq = $_REQUEST['order_seq'];
} else {
    $order_seq = '';    // 初期化のみ アンカーで使用するため
}

/////////// 画面解像度の取得
if ($_SESSION['site_view'] == 'on') {
    $display = 'normal';
} else {
    $display = 'wide';
}

$uniq = 'id=' . uniqid('order');    // キャッシュ防止用ユニークID
/////////// クライアントのホスト名(又はIP Address)の取得
$hostName = gethostbyaddr($_SERVER['REMOTE_ADDR']);

/////////// 開始日時の登録ロジック
while (isset($_REQUEST['str'])) {
    if (!client_check()) break;
    $order_seq = $_REQUEST['str'];
    acceptanceInspectionStart($order_seq, $hostName);
    break;
}
/////////// 終了日時の登録ロジック
while (isset($_REQUEST['end'])) {
    if (!client_check()) break;
    $order_seq = $_REQUEST['end'];
    acceptanceInspectionEnd($order_seq, $hostName);
    break;
}
/////////// 開始・終了日時のキャンセル ロジック
while (isset($_REQUEST['cancel'])) {
    if (!client_check()) break;
    $order_seq = $_REQUEST['cancel'];
    acceptanceInspectionCancel($order_seq, $hostName);
    break;
}
/////////// 優先度の変更ロジック(インクリメント)
while (isset($_REQUEST['priority_inc'])) {
    $order_seq = $_REQUEST['priority_inc'];
    if (!user_check($_SESSION['User_ID'], 3)) break;
    $ymd       = $_REQUEST['ymd'];
    ////////// maximum check
    $chk_sql = "SELECT max(priority) FROM inspection_recourse WHERE wantdate = '{$ymd} 170000' AND order_seq != {$order_seq}";
    if (getUniResult($chk_sql, $priority_max) > 0) {
        $priority_max++;
        $chk_sql = "SELECT priority FROM inspection_recourse WHERE order_seq = {$order_seq}";
        if (getUniResult($chk_sql, $priority) > 0) {
            $priority++;
            if ($priority > $priority_max) $priority = $priority_max;
            ////////// UPDATE
            $update = "UPDATE inspection_recourse SET priority = {$priority} WHERE order_seq = {$order_seq}";
            if (query_affected($update) <= 0) {
                $_SESSION['s_sysmsg'] = '優先度を下げる事が出来ませんでした。';
            }
        }
    }
    // 自分以外に同じ日付がない場合やエラーの場合は何もしない
    break;
}
/////////// 優先度の変更ロジック(デクリメント)
while (isset($_REQUEST['priority_dec'])) {
    $order_seq = $_REQUEST['priority_dec'];
    if (!user_check($_SESSION['User_ID'], 3)) break;
    $ymd       = $_REQUEST['ymd'];
    ////////// minimum check
    $chk_sql = "SELECT min(priority) FROM inspection_recourse WHERE wantdate = '{$ymd} 170000' AND order_seq != {$order_seq}";
    if (getUniResult($chk_sql, $priority_min) > 0) {
        $priority_min--;
        $chk_sql = "SELECT priority FROM inspection_recourse WHERE order_seq = {$order_seq}";
        if (getUniResult($chk_sql, $priority) > 0) {
            $priority--;
            if ($priority < $priority_min) $priority = $priority_min;
            ////////// UPDATE
            $update = "UPDATE inspection_recourse SET priority = {$priority} WHERE order_seq = {$order_seq}";
            if (query_affected($update) <= 0) {
                $_SESSION['s_sysmsg'] = '優先度を下げる事が出来ませんでした。';
            }
        }
    }
    // 自分以外に同じ日付がない場合やエラーの場合は何もしない
    break;
}
/////////// 希望日の変更ロジック
while (isset($_REQUEST['wantdate'])) {
    $order_seq = $_REQUEST['order_seq'];
    if (!user_check($_SESSION['User_ID'], 3)) break;
    $wantdate  = $_REQUEST['wantdate'];
    if ($wantdate < date('Ymd')) {
        $_SESSION['s_sysmsg'] = '希望日を本日より前にする事は出来ませんでした。';
        break;
    }
    ////////// UPDATE
    $update = "UPDATE inspection_recourse SET wantdate = '{$wantdate} 170000' WHERE order_seq = {$order_seq}";
    if (query_affected($update) <= 0) {
        $_SESSION['s_sysmsg'] = '希望日を変更する事が出来ませんでした。';
    }
    break;
}

if ($select == 'inspc') {
    ////// 納入場所の除外 AND proc.locate != '52   ' の指定を削除 変更で栃木に納入させる場合があるため
    if ($div == 'C') {
        $where_div = "data.parts_no like 'C%'";
    }
    if ($div == 'SC') {
        $where_div = "data.parts_no like 'C%' AND data.kouji_no like '%SC%'";
    }
    if ($div == 'CS') {
        $where_div = "data.parts_no like 'C%' AND data.kouji_no not like '%SC%'";
    }
    if ($div == 'L') {
        $where_div = "data.parts_no like 'L%'";
    }
    if ($div == 'T') {
        $where_div = "data.parts_no like 'T%'";
    }
    if ($div == 'F') {
        $where_div = "data.parts_no like 'F%'";
    }
    if ($div == 'A') {
        $where_div = "(data.parts_no like 'C%' or data.parts_no like 'L%' or data.parts_no like 'T%' or data.parts_no like 'F%')";
    }
    if ($div == 'N') {
        $where_div = "uke_no <= 500000 AND uke_no >= 400000 AND (data.parts_no like 'C%' or data.parts_no like 'L%' or data.parts_no like 'T%' or data.parts_no like 'F%')";
    }
    if ($div == 'NKB') {
        $where_div = "plan.locate = '14'";
    }
    $query = "SELECT  to_char(ins.wantdate, 'MM/DD')                    AS 希望日
                    , to_char(ins.wantdate, 'YYYYMMDD')                 AS ymd
                    , trim(substr(usr.name, 1, 3))                      AS 依頼者
                    , CASE
                            WHEN uke_date = 0 THEN '---'
                            ELSE substr(to_char(uke_date, 'FM9999/99/99'), 6, 5)
                      END                                               AS uke_date
                    , data.order_seq                                    AS order_seq
                    , to_char(data.order_seq,'FM000-0000')              AS 発行連番
                    , CASE
                            WHEN data.uke_no = '' THEN '---'
                            WHEN data.uke_no IS NULL THEN '---'     -- 2005/05/26 ADD
                            ELSE data.uke_no
                      END                                               AS uke_no
                    , data.parts_no                                     AS parts_no
                    , replace(midsc, ' ', '')                           AS parts_name
                    , CASE
                            WHEN trim(mzist) = '' THEN '---'        --NULLでなくてスペースで埋まっている場合はこれ！
                            ELSE mzist
                      END                                               AS parts_zai
                    , CASE
                            WHEN trim(mepnt) = '' THEN '---'        --NULLでなくてスペースで埋まっている場合はこれ！
                            ELSE substr(mepnt, 1, 8)
                      END                                               AS parts_parent
                    , uke_q                                         -- 受付数
                    , pro_mark                                      -- 工程記号
                    , data.vendor               AS vendor           -- 納入先番号
                    , substr(mast.name, 1, 8)   AS vendor_name      -- 納入先名
                    , to_char(data.sei_no,'FM0000000')  AS sei_no   -- 指定桁数での0詰めサンプル
                    , CASE
                            WHEN trim(data.kouji_no) = '' THEN '---'    --NULLでなくてスペースで埋まっている場合はこれ！
                            ELSE trim(data.kouji_no)
                      END                       AS kouji_no
                    , CASE
                            WHEN proc.next_pro = 'END..' THEN proc.next_pro    --NULLでなくてスペースで埋まっている場合はこれ！
                            ELSE (SELECT substr(name, 1, 8) FROM vendor_master WHERE vendor=proc.next_pro)
                      END                       AS 次工程
                    , ken.str_timestamp         AS str_timestamp
                    , ken.end_timestamp         AS end_timestamp
                    , CASE
                            WHEN (SELECT order_seq FROM inspection_holding WHERE order_seq=data.order_seq AND str_timestamp IS NOT NULL AND end_timestamp IS NULL) IS NULL
                            THEN ''
                            ELSE '中断中'
                      END                       AS hold_flg
                FROM
                    inspection_recourse     AS ins
                LEFT OUTER JOIN
                    order_data              AS data  on(data.order_seq=ins.order_seq)
                LEFT OUTER JOIN
                    order_process           AS proc
                                                using(sei_no, order_no, vendor)
                LEFT OUTER JOIN
                    order_plan              AS plan     USING (sei_no)
                LEFT OUTER JOIN
                    vendor_master           AS mast  on(data.vendor=mast.vendor)
                LEFT OUTER JOIN
                    miitem                      on(data.parts_no=mipn)
                LEFT OUTER JOIN
                    acceptance_kensa        AS ken  on(data.order_seq=ken.order_seq)
                LEFT OUTER JOIN
                    user_detailes           AS usr  on(ins.uid=usr.uid)
                WHERE
                    ken_date <= 0       -- 未検収分
                    AND
                    data.sei_no > 0     -- 製造用であり
                    AND
                    (data.order_q - data.cut_genpin) > 0  -- 打切されていない物
                    AND
                    ( (ken.end_timestamp IS NULL) OR (ken.end_timestamp >= (CURRENT_TIMESTAMP - interval '10 minute')) )
                    AND
                    {$where_div}
                ORDER BY
                    ins.wantdate ASC, ins.priority ASC, ins.regdate ASC
                OFFSET 0
                LIMIT 1000
    ";
    $res = array();
    if (($rows = getResult($query, $res)) < 1) {
        $_SESSION['s_sysmsg'] .= "<font color='yellow'>検査依頼データがありません！</font>";
        $view = 'NG';
    } else {
        $view = 'OK';
        $maxDate = 19;                  // 10日間 → 17日間(2006/08/31 change)→19日間(preDateで土日対応にしたため)
        $preDate = 3;                   //  3日前から (2007/01/18 preDateを追加し土日対応へ)
        $timestamp = time();            //  E_STRICTでmktime()→time()の標準に従うようにメッセージが出たため
        for ($i=0; $i<$preDate; $i++) {
            $timestamp -= 86400;
            while (day_off($timestamp)) {
                $timestamp -= 86400;
            }
        }
        $chgdate = array(); $fmtdate = array();
        for ($i=0; $i<$maxDate; $i++) {
            while (day_off($timestamp)) {
                $timestamp += 86400;
            }
            $chgdate[$i] = date('Ymd', $timestamp);
            $fmtdate[$i] = date('m/d', $timestamp);
            $timestamp += 86400;
        }
    }
}

/////////// 自動更新と手動更新の条件切換え
if ($select == 'graph') {
    $auto_reload = 'on';
} elseif ($order_seq != '') {
    $auto_reload = 'on';
} else {
    $auto_reload = 'off';
}

/////////// HTML Header を出力してキャッシュを制御
$menu->out_html_header();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="Content-Style-Type" content="text/css">
<title><?= $menu->out_title() ?></title>
<?php // if ($_SESSION['s_sysmsg'] != '') echo $menu->out_site_java() ?>
<?= $menu->out_css() ?>
<style type='text/css'>
<!--
th {
    font-size:      11.5pt;
    font-weight:    bold;
    font-family:    monospace;
}
.item {
    position: absolute;
    /* top: 100px; */
    left:     1px;
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
    background-color:#d6d3ce;
}
.winbox_gray {
    border-style: solid;
    border-width: 1px;
    border-top-color:       #FFFFFF;
    border-left-color:      #FFFFFF;
    border-right-color:     #999999;
    border-bottom-color:    #999999;
    background-color:#d6d3ce;
    color: gray;
}
.winbox_mark {
    border-style: solid;
    border-width: 1px;
    border-top-color:       #FFFFFF;
    border-left-color:      #FFFFFF;
    border-right-color:     #999999;
    border-bottom-color:    #999999;
    background-color:#eaeaee;
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
     setInterval('document.reload_form.submit()', 30000);   // 30秒
     //  onLoad='init()' ←これを <body>タグへ入れればOK
}
function win_open(url) {
    var w = 900;
    var h = 680;
    var left = (screen.availWidth  - w) / 2;
    var top  = (screen.availHeight - h) / 2;
    window.open(url, 'view_win', 'width='+w+',height='+h+',scrollbars=no,status=no,toolbar=no,location=no,menubar=no,top='+top+',left='+left);
}
function inspection_recourse_del(order_seq, parts_no, parts_name) {
    if (confirm('部品番号：' + parts_no + '\n\n部品名称：' + parts_name + "\n\n検査依頼を「削除」します！\n\n宜しいですか？")) {
        // 実行します。
        document.inspection_form.del_order_seq.value = order_seq;
        document.inspection_form.submit();
    } else {
        alert('取消しました。');
    }
}
function wantdate_chg(order_seq, old_date) {
    if (!(new_date = prompt('希望検査完了日を変更して下さい。', old_date))) {
        return;
    }
}
function inspection_time(parts_no, parts_name, str_timestamp, end_timestamp) {
    alert('部品番号：' + parts_no + '\n\n部品名称：' + parts_name + '\n\n検査開始日時：' + str_timestamp + '\n\n検査終了日時：' + end_timestamp);
}
function vendor_code_view(vendor, vendor_name) {
    alert('発注先コード：' + vendor + '\n\n発注先名：' + vendor_name + '\n\n');
}
// -->
</script>
<form name='inspection_form' method='get' action='inspection_recourse_regist.php' target='_self'>
    <input type='hidden' name='retUrl' value='<?= $menu->out_self() ?>'>
    <input type='hidden' name='del_order_seq' value=''>
</form>
<form name='reload_form' action='inspection_recourse_List.php<?php if ($order_seq != '') echo "#{$order_seq}"; ?>' method='get' target='_self'>
    <input type='hidden' name='order_seq' value='<?=$order_seq?>'>
</form>
</head>
<body <?php if ($auto_reload == 'on') echo "onLoad='init()'"; ?>>
    <center>
        <?php if ($view != 'OK') { ?>
        <table border='0' class='msg'>
            <tr>
                <td>
                    <b style='color: teal;'>データがありません！</b>
                </td>
            </tr>
        </table>
        <?php } elseif ($select == 'inspc') { ?>
        <!-------------- 詳細データ表示のための表を作成 -------------->
        <table class='item' bgcolor='#d6d3ce'  border='1' cellspacing='0' cellpadding='2'>
           <tr><td> <!-- ダミー(デザイン用) -->
        <table class='winbox_field' width=100% align='center' border='1' cellspacing='0' cellpadding='1'>
            <!--
            <th class='winbox' width='30' nowrap>No</th>
            <th class='winbox' width='<?php if ($display=='normal') echo 96;else echo 94;?>' nowrap colspan='2' style='font-size:10pt;'>検査開始終了</th>
            <th class='winbox' width='50' nowrap style='font-size:10pt;'>受付日</th>
            <th class='winbox' width='55' nowrap style='font-size:9.5pt;'>受付No</th>
            <th class='winbox' width='155' nowrap>部品番号・名称</th>
            <th class='winbox' width='90' nowrap style='font-size:11pt;'>材質/親機種</th>
            <th class='winbox' width='70' nowrap>受付数</th>
            <th class='winbox' width='35' nowrap style='font-size:9.5pt;'>工程</th>
            <th class='winbox' width='130' nowrap>納入先</th>
            <th class='winbox' width='37' nowrap style='font-size:8.5pt;'>依頼者</th>
            <th class='winbox' width='90' nowrap style='font-size:11pt;'>希望日</th>
            <?php if ($display == 'wide') { ?>
            <th class='winbox' width='80' nowrap>工事番号</th>
            <th class='winbox' width='78' nowrap>発行連番</th>
            <th class='winbox' width='70' nowrap>製造番号</th>
            <th class='winbox' width='127' nowrap>次工程</th>
            <?php } ?>
            -->
        <?php
            $r = 0;
            foreach ($res as $rec) {
                $r++;
                if ($rec['end_timestamp']) $winbox = 'winbox_gray'; else $winbox = 'winbox';
                if ($rec['order_seq'] == $order_seq) $winbox = 'winbox_mark'; else $winbox = 'winbox';
                if ($rec['hold_flg'] == '中断中') {
                    echo "<tr style='color:gray;'>\n";
                } else {
                    echo "<tr>\n";
                }
                if ($rec['str_timestamp']) { // ダブルクリックで検査開始時間と終了時間を表示 2005/02/21 追加
                    if ($rec['end_timestamp']) {
                        $inspec = " onDblClick='inspection_time(\"{$rec['parts_no']}\",\"{$rec['parts_name']}\",\"{$rec['str_timestamp']}\",\"{$rec['end_timestamp']}\")'";
                    } else {
                        $inspec = " onDblClick='inspection_time(\"{$rec['parts_no']}\",\"{$rec['parts_name']}\",\"{$rec['str_timestamp']}\",\"未終了\")'";
                    }
                } else {
                    ///// ダブルクリックで緊急検査依頼の削除が出来る
                    $inspec = " onDblClick='inspection_recourse_del(\"{$rec['order_seq']}\",\"{$rec['parts_no']}\",\"{$rec['parts_name']}\")'";
                }
                echo "<td{$inspec} class='{$winbox}' style='font-size:16; font-weight:bold;' align='right'  width='30' nowrap><a href='inspection_recourse_List.php?order_seq={$rec['order_seq']}&{$uniq}#{$rec['order_seq']}' target='_self' style='text-decoration:none;'>{$r}</a></td>\n";
                if ($rec['str_timestamp']) {
                    if ($rec['end_timestamp']) {
                        echo "<td class='{$winbox}' style='font-size:16; font-weight:bold; color:gray;' align='center' width='44 nowrap'>検済</td>\n";
                    } else {
                        if ($rec['hold_flg'] == '中断中') {
                            echo "<td class='{$winbox}' style='font-size:16; font-weight:bold; color:gray;' align='center' width='44' nowrap>中断</td>\n";
                        } else {
                            echo "<td class='{$winbox}' style='font-size:16; font-weight:bold; color:blue;' align='center' width='44' nowrap><a href='inspection_recourse_List.php?end={$rec['order_seq']}&{$uniq}#{$rec['order_seq']}' target='_self' style='text-decoration:none; color:yellow;'>検中</a></td>\n";
                        }
                    }
                } else {
                    echo "<td{$inspec} class='{$winbox}' style='font-size:16; font-weight:bold; color:blue;' align='center' width='44' nowrap><a href='inspection_recourse_List.php?str={$rec['order_seq']}&{$uniq}#{$rec['order_seq']}' target='_self' style='text-decoration:none;'>開始</a></td>\n";
                }
                if ( ($rec['str_timestamp']) || ($rec['end_timestamp']) ) {
                    if ( ($rec['str_timestamp']) && ($rec['end_timestamp']) ) {
                        echo "<td class='{$winbox}' style='font-size:16; font-weight:bold; color:red ;' align='center' width='44' nowrap  bgcolor='#d6d3ce'><a href='inspection_recourse_List.php?cancel={$rec['order_seq']}&{$uniq}#{$rec['order_seq']}' target='_self' style='text-decoration:none; color:gray;'>訂正</a></td>\n";
                    } else {
                        if ($rec['hold_flg'] == '中断中') {
                            echo "<td class='{$winbox}' style='font-size:16; font-weight:bold; color:gray;' align='center' width='44' nowrap>取消</td>\n";
                        } else {
                            echo "<td class='{$winbox}' style='font-size:16; font-weight:bold; color:red ;' align='center' width='44' nowrap  bgcolor='#d6d3ce'><a href='inspection_recourse_List.php?cancel={$rec['order_seq']}&{$uniq}#{$rec['order_seq']}' target='_self' style='text-decoration:none; color:red;'>取消</a></td>\n";
                        }
                    }
                } else {
                    echo "<td{$inspec} class='{$winbox}' style='font-size:16; font-weight:bold; color:gray ;' align='center' width='44' nowrap>取消</td>\n";
                }
                echo "<td{$inspec} class='{$winbox}' style='font-size:16; font-weight:bold;' align='center' width='50'  nowrap><a name='{$rec['order_seq']}'>{$rec['uke_date']}</a></td>\n";
                echo "<td{$inspec} class='{$winbox}' style='font-size:16; font-weight:bold;' align='center' width='55'  nowrap>{$rec['uke_no']}</td>\n";
                echo "<td{$inspec} class='{$winbox}' style='font-size:16; font-weight:bold;' align='left'   width='155' nowrap onClick='win_open(\"{$menu->out_action('在庫予定')}?showMenu=CondForm&targetPartsNo=" . urlencode($rec['parts_no']) . "&noMenu=yes\");'>\n";
                echo "    <a class='link' href='javascript:void(0);' target='_self' style='text-decoration:none;'>{$rec['parts_no']}</a><br>" . mb_substr(mb_convert_kana($rec['parts_name'], 'k'), 0, 14) . "</td>\n";
                echo "<td{$inspec} class='{$winbox}' style='font-size:16; font-weight:bold;' align='left'   width='90'  nowrap>" . mb_substr(mb_convert_kana($rec['parts_zai'], 'k'), 0, 8) . '<br>' . mb_convert_kana($rec['parts_parent'], 'k') . "</td>\n";
                echo "<td{$inspec} class='{$winbox}' style='font-size:16; font-weight:bold;' align='right'  width='70'  nowrap>" . number_format($rec['uke_q'], 0) . "</td>\n";
                echo "<td{$inspec} class='{$winbox}' style='font-size:16; font-weight:bold;' align='center' width='35'  nowrap>{$rec['pro_mark']}</td>\n";
                echo "<td{$inspec} class='{$winbox}' style='font-size:14; font-weight:bold;' align='left'   width='130' nowrap onClick='vendor_code_view(\"{$rec['vendor']}\",\"{$rec['vendor_name']}\")'>{$rec['vendor_name']}</td>\n";
                echo "<td{$inspec} class='{$winbox}' style='font-size:14; font-weight:bold;' align='center' width='37'  nowrap>{$rec['依頼者']}</td>\n";
                echo "<td class='{$winbox}' style='font-size:14; font-weight:bold;' align='center' width='90'  nowrap>\n";
                echo "  <form name='wantdate_form{$r}' method='get' action='inspection_recourse_List.php?{$uniq}#{$rec['order_seq']}' target='_self'>\n";
                echo "    <input type='hidden' name='order_seq' value='{$rec['order_seq']}'>\n";
                echo "    <select name='wantdate' style='font-size:11pt; font-weight:bold;' onChange='document.wantdate_form{$r}.submit()'>\n";
                for ($i=0; $i<$maxDate; $i++) {
                    if($fmtdate[$i] == $rec['希望日']) {
                        echo "    <option value='{$chgdate[$i]}' selected>{$rec['希望日']}</option>\n";
                    } else {
                        echo "    <option value='{$chgdate[$i]}'>{$fmtdate[$i]}</option>\n";
                    }
                }
                echo "    </select>\n";
                echo "  </form>\n";
                echo "    <a href='inspection_recourse_List.php?priority_dec={$rec['order_seq']}&ymd={$rec['ymd']}&{$uniq}#{$rec['order_seq']}' target='_self' style='text-decoration:none;'>↑</a>\n";
                echo "    <a href='inspection_recourse_List.php?priority_inc={$rec['order_seq']}&ymd={$rec['ymd']}&{$uniq}#{$rec['order_seq']}' target='_self' style='text-decoration:none;'>↓</a>\n";
                echo "</td>\n";
                if ($display == 'wide') {
                    echo "<td class='{$winbox}' style='font-size:16; font-weight:bold;' align='center' width='80'  nowrap>{$rec['kouji_no']}</td>\n";
                    echo "<td class='{$winbox}' style='font-size:16; font-weight:bold;' align='center' width='78'  nowrap>{$rec['発行連番']}</td>\n";
                    echo "<td class='{$winbox}' style='font-size:16; font-weight:bold;' align='center' width='70'  nowrap>{$rec['sei_no']}</td>\n";
                    echo "<td class='{$winbox}' style='font-size:14; font-weight:bold;' align='left'   width='127' nowrap>{$rec['次工程']}</td>\n";
                }
                echo "</tr>\n";
            }
        ?>
        </table> <!----- ダミー End ----->
            </td></tr>
        </table>
        <?php } ?>
    </center>
</body>
<script type='text/javascript' language='JavaScript'>
<!--
// setTimeout('location.reload(true)',10000);      // リロード用１０秒
// -->
</script>
<?=$menu->out_alert_java()?>
</html>
<?php ob_end_flush(); // 出力バッファをgzip圧縮 END ?>
