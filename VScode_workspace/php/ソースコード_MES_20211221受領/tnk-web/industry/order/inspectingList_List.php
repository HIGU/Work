<?php
//////////////////////////////////////////////////////////////////////////////
// 受入検査中のリスト 照会 及び 中断指示メンテナンス          Listフレーム  //
// Copyright (C) 2007-2018 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2007/01/17 Created  inspectingList_List.php                              //
// 2007/01/19 メッセージの修正 未検収データ → 検査中データ                 //
//            中断(保留)した場合の表示(グレー色)を 検中 → 中断 へ変更      //
// 2007/01/22 検査のキャンセルロジックを order_function.php(共通化)へ変更   //
//            受入検査 開始キャンセル時に中断履歴も削除するため             //
// 2007/01/23 行をダブルクリック時に検査員名と中断時間の表示を追加          //
// 2007/01/24 order_dataのken_dateが検収されても完了入力されていないもの表示//
// 2007/01/25 検済なのに中断が出来てしまう不具合対応 中断を---へ変換        //
// 2007/02/22 部品番号にリンクを追加して在庫経歴・予定照会POPUP Windowを表示//
// 2007/04/18 <a href='javascript:win_open(..)'→ <a href='javascript:void()//
//            onClick='win_open(...)'の書式へ変更により部品番号の#1等に対応 //
// 2007/10/25 E_ALL | E_STRICTへ 事業部のWHERE区をgetDivWhereSQL()より取得  //
// 2007/10/26 SQLのWHERE区最適化 以下のように変更                           //
//           (CURRENT_TIMESTAMP - ken.end_timestamp) <= interval '10 minute'//
//                                      ↓                                  //
//          (ken.end_timestamp >= (CURRENT_TIMESTAMP - interval '10 minute')//
// 2017/07/27 集荷納期グラフを追加                                          //
// 2018/12/14 表示が遅いので検索期間を過去３ヶ月に 検査開始部は保留         //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug 用
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮
session_start();                            // ini_set()の次に指定すること Script 最上行
require_once ('../../function.php');        // TNK 全共通 function (define.phpを含む)
require_once ('../../MenuHeader.php');      // TNK 全共通 menu class
require_once ('order_function.php');        // order 関係の共通 function
access_log();                               // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader();                   // 認証チェック0=一般以上 戻り先=セッションより タイトル未設定

////////////// サイト設定
$menu->set_site(30, 50);                    // site_index=30(生産メニュー) site_id=50(納入検査仕掛) 999(未定)
////////////// target設定
$menu->set_target('_parent');               // フレーム版の戻り先はtarget属性が必須

//////////// 自分をフレーム定義に変える
// $menu->set_self(INDUST . 'order/order_schedule.php');
//////////// 呼出先のaction名とアドレス設定
$menu->set_action('予定明細', INDUST . 'order/order_detailes.php');
$menu->set_action('予定明細次工程', INDUST . 'order/order_detailes_next.php');
$menu->set_action('在庫予定',   INDUST . 'parts/parts_stock_plan/parts_stock_plan_Main.php');
$menu->set_action('在庫経歴',   INDUST . 'parts/parts_stock_history/parts_stock_view.php');
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('受入検査中リスト照会 及び 中断処理');     // タイトルを入れないとIEの一部のバージョンで表示できない不具合あり

///////// パラメーターチェックと設定
if (isset($_REQUEST['div'])) {
    $div = $_REQUEST['div'];                // 事業部
    $_SESSION['div'] = $_REQUEST['div'];    // セッションに保存
} else {
    if (isset($_SESSION['div'])) {
        $div = $_SESSION['div'];            // Default(セッションから)
    } else {
        $div = 'C';                         // 初期値(カプラ)あまり意味は無い
    }
}
if (isset($_REQUEST['miken'])) {
    $select = 'miken';                      // 未検収リスト
    $_SESSION['select'] = 'miken';          // セッションに保存
} elseif (isset($_REQUEST['graph'])) {
    $select = 'graph';                      // 納入予定グラフ
    $_SESSION['select'] = 'graph';          // セッションに保存
} elseif (isset($_REQUEST['sgraph'])) {
    $select = 'sgraph';                     // 集荷納期グラフ
    $_SESSION['select'] = 'sgraph';         // セッションに保存
} else {
    if (isset($_SESSION['select'])) {
        $select = $_SESSION['select'];      // Default(セッションから)
    } else {
        $select = 'graph';                  // 初期値(納入予定グラフ)あまり意味は無い
    }
}
if (isset($_REQUEST['vendor_no'])) {
    $vendor_no = $_REQUEST['vendor_no'];    // 納品先の指定があれば検索する
    $select = 'inspecting';                 // 検査中リスト
    $_SESSION['select'] = 'inspecting';     // セッションに保存
} else {
    $vendor_no = '';                        // 初期化のみ
}
//$vendor_no = str_replace('*', '%', $vendor_no);   // like文に対応させる

if (isset($_REQUEST['parts_no'])) {
    $parts_no = $_REQUEST['parts_no'];      // 部品番号の指定があれば検索する
    $select = 'miken';                      // 未検収リスト
    $_SESSION['select'] = 'miken';          // セッションに保存
} else {
    $parts_no = '';                         // 初期化のみ
}
$parts_no = str_replace('*', '%', $parts_no);   // like文に対応させる

if (isset($_REQUEST['order_seq'])) {
    $order_seq = $_REQUEST['order_seq'];
} else {
    $order_seq = '';    // 初期化のみ アンカーで使用するため
}

/////////// 画面情報の取得
if ($_SESSION['site_view'] == 'on') {
    $display = 'normal';
} else {
    $display = 'wide';
}

$uniq = 'id=' . uniqid('order');    // キャッシュ防止用ユニークID
/////////// クライアントのホスト名(又はIP Address)の取得
$hostName = gethostbyaddr($_SERVER['REMOTE_ADDR']);

/////////// 中断 開始日時の登録ロジック
while (isset($_REQUEST['hold'])) {
    if (!client_check()) break;
    $order_seq = $_REQUEST['hold'];
    acceptanceInspectionHold($order_seq, $hostName);
    break;
}
/////////// 中断 終了日時の登録ロジック
while (isset($_REQUEST['restart'])) {
    if (!client_check()) break;
    $order_seq = $_REQUEST['restart'];
    acceptanceInspectionRestart($order_seq, $hostName);
    break;
}
/////////// 検査開始日時の登録ロジック(実際にはこのロジックが動くことは無い)
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
while (isset($_REQUEST['cancel'])) {        // cancel は使えない事に注意！
    if (!client_check()) break;
    $order_seq = $_REQUEST['cancel'];
    acceptanceInspectionCancel($order_seq, $hostName);
    break;
}

while (1) {
    $where_div = getDivWhereSQL($div);
    if ($vendor_no == '') {
        $where_vendor = '';                                      // 何もしない
    } else {
        $where_vendor = "AND data.vendor = '{$vendor_no}'";     // 納入先番号で検索
    }
    // 重さ対策 受付日を限定して照会 当日から３ヶ月
    $today      = date("Ymd",strtotime("-3 month"));
    $where_date = "AND uke_date >= " . $today;
    // 重さ対策 検査開始日を限定して照会 当日から３ヶ月
    // こちらは保留中 検査終了忘れを防げない あと余計に重くなった気がする？
    $today      = date("Ymd",strtotime("-3 month"));
    $where_ken  = "AND to_char(ken.str_timestamp, 'YYYYMMDD') >= " . $today;
    $query = "
        SELECT  substr(to_char(uke_date, 'FM9999/99/99'), 6, 5) AS uke_date
            , data.order_seq            AS order_seq
            , to_char(data.order_seq,'FM000-0000')            AS 発行連番
            , data.uke_no               AS uke_no
            , data.parts_no             AS parts_no
            , replace(midsc, ' ', '')   AS parts_name
            , CASE
                    WHEN trim(mzist) = '' THEN '---'        --NULLでなくてスペースで埋まっている場合はこれ！
                    ELSE substr(mzist, 1, 8)
              END                       AS parts_zai
            , CASE
                    WHEN trim(mepnt) = '' THEN '---'        --NULLでなくてスペースで埋まっている場合はこれ！
                    ELSE substr(mepnt, 1, 8)
              END                       AS parts_parent
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
            , (SELECT str_timestamp FROM inspection_holding WHERE order_seq=data.order_seq AND str_timestamp IS NOT NULL AND end_timestamp IS NULL LIMIT 1)
                                        AS hold_time
            , ken.uid                   AS uid
            , (SELECT trim(name) FROM user_detailes WHERE uid=ken.uid LIMIT 1)
                                        AS user_name
        FROM
            acceptance_kensa    AS ken
        LEFT OUTER JOIN
            order_data          AS data     USING (order_seq)
        LEFT OUTER JOIN
            order_process       AS proc     USING (sei_no, order_no, vendor)
        LEFT OUTER JOIN
            order_plan          AS plan     USING (sei_no)
        LEFT OUTER JOIN
            vendor_master       AS mast     ON (data.vendor=mast.vendor)
        LEFT OUTER JOIN
            miitem                          ON (data.parts_no=mipn)
        WHERE
            -- これはNG ( (ken.end_timestamp IS NULL) OR ((CURRENT_TIMESTAMP - ken.end_timestamp) <= (interval '10 minute')) )
            ( (ken.end_timestamp IS NULL) OR (ken.end_timestamp >= (CURRENT_TIMESTAMP - interval '10 minute')) )
            AND
            ken.str_timestamp IS NOT NULL   -- 検査中のみ
            AND
            data.sei_no > 0     -- 製造用であり
            AND
            (data.order_q - data.cut_genpin) > 0  -- 打切されていない物
            AND
            {$where_div} {$where_vendor} {$where_date}
        ORDER BY
            uke_date ASC, uke_no ASC
        OFFSET 0
        LIMIT 1000
    ";
    $res = array();
    if (($rows = getResult($query, $res)) < 1) {
        $_SESSION['s_sysmsg'] .= "<font color='yellow'>検査中データがありません！</font>";
        $view = 'NG';
    } else {
        $view = 'OK';
    }
    break;
}

/////////// 自動更新と手動更新の条件切換え
if ($select == 'graph') {
    $auto_reload = 'on';
} elseif ($select == 'sgraph') {
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
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
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
    left:    20px;
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
function inspection_recourse(order_seq, parts_no, parts_name) {
    if (confirm('部品番号：' + parts_no + '\n\n部品名称：' + parts_name + " の\n\n緊急部品 検査依頼をします。\n\n宜しいですか？")) {
        // 実行します。
        document.inspection_form.order_seq.value = order_seq;
        document.inspection_form.retUrl.value = (document.inspection_form.retUrl.value + '#' + order_seq);
        document.inspection_form.submit();
    } else {
        alert('取消しました。');
    }
}
function inspection_time(parts_no, parts_name, str_timestamp, end_timestamp, uid, name, hold_time) {
    if (hold_time == "-") {
        alert('部品番号　：　' + parts_no + '\n\n部品名称　：　' + parts_name + '\n\n検査開始日時　：　' + str_timestamp + '\n\n検査終了日時　：　' + end_timestamp + '\n\n社員番号　：　' + uid + '\n\n検査員名　：　' + name);
    } else {
        alert('部品番号　：　' + parts_no + '\n\n部品名称　：　' + parts_name + '\n\n検査開始日時　：　' + str_timestamp + '\n\n検査終了日時　：　' + end_timestamp + '\n\n社員番号　：　' + uid + '\n\n検査員名　：　' + name + '\n\n中断日時　：　' + hold_time);
    }
}
function miken_submit() {
    document.miken_submit_form.submit();
}
function vendor_code_view(vendor, vendor_name) {
    alert('発注先コード：' + vendor + '\n\n発注先名：' + vendor_name + '\n\n');
}
// -->
</script>
<form name='inspection_form' method='get' action='inspection_recourse_regist.php' target='_self'>
    <input type='hidden' name='retUrl' value='<?= $menu->out_self() ?>'>
    <input type='hidden' name='order_seq' value=''>
</form>
<form name='reload_form' action='inspectingList_List.php<?php if ($order_seq != '') echo "#{$order_seq}"; ?>' method='get' target='_self'>
    <input type='hidden' name='order_seq' value='<?=$order_seq?>'>
</form>
<form name='miken_submit_form' action='<?= $menu->out_parent() ?>' method='get' target='_parent'>
    <input type='hidden' name='miken' value='検査仕掛リスト'>
    <input type='hidden' name='div' value='<?=$div?>'>
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
        <?php } else { ?>
        <!-------------- 詳細データ表示のための表を作成 -------------->
        <table class='item' bgcolor='#d6d3ce'  border='1' cellspacing='0' cellpadding='2'>
           <tr><td> <!-- ダミー(デザイン用) -->
        <table class='winbox_field' width=100% align='center' border='1' cellspacing='0' cellpadding='1'>
            <!--
            <th class='winbox' width='30' nowrap>No</th>
            <th class='winbox' width='98' nowrap colspan='2' style='font-size:14;'>検査開始終了</th>
            <th class='winbox' width='70' nowrap>受付日</th>
            <th class='winbox' width='55' nowrap style='font-size:9.5pt;'>受付No</th>
            <th class='winbox' width='90' nowrap>部品番号</th>
            <th class='winbox' width='150' nowrap>部品名</th>
            <th class='winbox' width='90' nowrap style='font-size:14;'>材質/親機種</th>
            <th class='winbox' width='70' nowrap>受付数</th>
            <th class='winbox' width='35' nowrap style='font-size:9.5pt;'>工程</th>
            <th class='winbox' width='130' nowrap>納入先</th>
            <?php if ($display == 'wide') { ?>
            <th class='winbox' width='80' nowrap>工事番号</th>
            <th class='winbox' width='80' nowrap>発行連番</th>
            <th class='winbox' width='70' nowrap>製造番号</th>
            <th class='winbox' width='130' nowrap>次工程</th>
            <?php } ?>
            -->
        <?php
            $i = 0;
            foreach ($res as $rec) {
                $i++;
                if ($rec['end_timestamp']) $winbox = 'winbox_gray'; else $winbox = 'winbox';
                if ($rec['order_seq'] == $order_seq) $winbox = 'winbox_mark'; else $winbox = 'winbox';
                if ($rec['str_timestamp']) { // ダブルクリックで検査開始時間と終了時間を表示 2005/02/21 追加
                    if ($rec['end_timestamp']) {
                        echo "<tr onDblClick='inspection_time(\"{$rec['parts_no']}\",\"{$rec['parts_name']}\",\"{$rec['str_timestamp']}\",\"{$rec['end_timestamp']}\",\"{$rec['uid']}\",\"{$rec['user_name']}\",\"-\")'>\n";
                    } else {
                        if ($rec['hold_time']) {
                            echo "<tr style='color:gray;' onDblClick='inspection_time(\"{$rec['parts_no']}\",\"{$rec['parts_name']}\",\"{$rec['str_timestamp']}\",\"未終了\",\"{$rec['uid']}\",\"{$rec['user_name']}\",\"{$rec['hold_time']}\")'>\n";
                        } else {
                            echo "<tr onDblClick='inspection_time(\"{$rec['parts_no']}\",\"{$rec['parts_name']}\",\"{$rec['str_timestamp']}\",\"未終了\",\"{$rec['uid']}\",\"{$rec['user_name']}\",\"-\")'>\n";
                        }
                    }
                } else {    // ダブルクリックで緊急検査依頼が出来る
                    echo "<tr onDblClick='inspection_recourse(\"{$rec['order_seq']}\",\"{$rec['parts_no']}\",\"{$rec['parts_name']}\")'>\n";
                }
                echo "<td class='{$winbox}' style='font-size:16; font-weight:bold;' align='right'  width='30' nowrap><a href='inspectingList_List.php?order_seq={$rec['order_seq']}&{$uniq}#{$rec['order_seq']}' target='_self' style='text-decoration:none;'>{$i}</a></td>\n";
                if ($rec['str_timestamp']) {
                    if ($rec['end_timestamp']) {
                        echo "<td class='{$winbox}' style='font-size:16; font-weight:bold; color:gray;' align='center' width='44 nowrap'>検済</td>\n";
                    } else {
                        if ($rec['hold_time']) {
                            echo "<td class='{$winbox}' style='font-size:16; font-weight:bold; color:gray;' align='center' width='44' nowrap>中断</td>\n";
                        } else {
                            echo "<td class='{$winbox}' style='font-size:16; font-weight:bold; color:blue;' align='center' width='44' nowrap><a href='inspectingList_List.php?end={$rec['order_seq']}&{$uniq}#{$rec['order_seq']}' target='_self' style='text-decoration:none; color:yellow;'>検中</a></td>\n";
                        }
                    }
                } else {
                    echo "<td class='{$winbox}' style='font-size:16; font-weight:bold; color:blue;' align='center' width='44' nowrap><a href='inspectingList_List.php?str={$rec['order_seq']}&{$uniq}#{$rec['order_seq']}' target='_self' style='text-decoration:none;'>開始</a></td>\n";
                }
                if ( ($rec['str_timestamp']) || ($rec['end_timestamp']) ) {
                    if ( ($rec['str_timestamp']) && ($rec['end_timestamp']) ) {
                        echo "<td class='{$winbox}' style='font-size:16; font-weight:bold; color:red;' align='center' width='44' nowrap><a href='inspectingList_List.php?cancel={$rec['order_seq']}&{$uniq}#{$rec['order_seq']}' target='_self' style='text-decoration:none; color:gray;'>訂正</a></td>\n";
                    } else {
                        if ($rec['hold_time']) {
                            echo "<td class='{$winbox}' style='font-size:16; font-weight:bold; color:gray;' align='center' width='44' nowrap>取消</td>\n";
                        } else {
                            echo "<td class='{$winbox}' style='font-size:16; font-weight:bold; color:red;' align='center' width='44' nowrap><a href='inspectingList_List.php?cancel={$rec['order_seq']}&{$uniq}#{$rec['order_seq']}' target='_self' style='text-decoration:none; color:red;'>取消</a></td>\n";
                        }
                    }
                } else {
                    echo "<td class='{$winbox}' style='font-size:16; font-weight:bold; color:gray ;' align='center' width='44' nowrap>取消</td>\n";
                }
                echo "<td class='{$winbox}' style='font-size:16; font-weight:bold;' align='center' width='70'  nowrap><a name='{$rec['order_seq']}'>{$rec['uke_date']}</a></td>\n";
                // echo "<td class='{$winbox}' style='font-size:16; font-weight:bold;' align='center' width='55'  nowrap>{$rec['uke_no']}</td>\n";
                if ($rec['hold_time']) {
                    echo "<td class='{$winbox}' style='font-size:16; font-weight:bold;' align='center' width='55'  nowrap><a href='inspectingList_List.php?restart={$rec['order_seq']}&{$uniq}#{$rec['order_seq']}' target='_self' style='text-decoration:none;'>再開</a></td>\n";
                } else {
                    if ($rec['end_timestamp']) {
                        echo "<td class='{$winbox}' style='font-size:16; font-weight:bold; color:gray;' align='center' width='55'  nowrap>---</td>\n";
                    } else {
                        echo "<td class='{$winbox}' style='font-size:16; font-weight:bold;' align='center' width='55'  nowrap><a href='inspectingList_List.php?hold={$rec['order_seq']}&{$uniq}#{$rec['order_seq']}' target='_self' style='text-decoration:none;'>中断</a></td>\n";
                    }
                }
                echo "<td class='{$winbox}' style='font-size:16; font-weight:bold;' align='center' width='91'  nowrap onClick='win_open(\"{$menu->out_action('在庫予定')}?showMenu=CondForm&targetPartsNo=" . urlencode($rec['parts_no']) . "&noMenu=yes\");'>\n";
                echo "    <a class='link' href='javascript:void(0)' target='_self' style='text-decoration:none;'>{$rec['parts_no']}</a></td>\n";
                echo "<td class='{$winbox}' style='font-size:14; font-weight:bold;' align='left'   width='150' nowrap>" . mb_substr(mb_convert_kana($rec['parts_name'], 'k'), 0, 27) . "</td>\n";
                echo "<td class='{$winbox}' style='font-size:16; font-weight:bold;' align='left'   width='90'  nowrap>" . mb_convert_kana($rec['parts_zai'], 'k') . '<br>' . mb_convert_kana($rec['parts_parent'], 'k') . "</td>\n";
                echo "<td class='{$winbox}' style='font-size:16; font-weight:bold;' align='right'  width='70'  nowrap>" . number_format($rec['uke_q'], 0) . "</td>\n";
                echo "<td class='{$winbox}' style='font-size:16; font-weight:bold;' align='center' width='35'  nowrap>{$rec['pro_mark']}</td>\n";
                echo "<td class='{$winbox}' style='font-size:14; font-weight:bold;' align='left'   width='130' nowrap onClick='vendor_code_view(\"{$rec['vendor']}\",\"{$rec['vendor_name']}\")'>{$rec['vendor_name']}</td>\n";
                if ($display == 'wide') {
                    echo "<td class='{$winbox}' style='font-size:16; font-weight:bold;' align='center' width='80'  nowrap>{$rec['kouji_no']}</td>\n";
                    echo "<td class='{$winbox}' style='font-size:16; font-weight:bold;' align='center' width='80'  nowrap>{$rec['発行連番']}</td>\n";
                    echo "<td class='{$winbox}' style='font-size:16; font-weight:bold;' align='center' width='70'  nowrap>{$rec['sei_no']}</td>\n";
                    echo "<td class='{$winbox}' style='font-size:14; font-weight:bold;' align='left'   width='130' nowrap>{$rec['次工程']}</td>\n";
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
<script language='JavaScript'>
<!--
// setTimeout('location.reload(true)',10000);      // リロード用１０秒
// -->
</script>
<?=$menu->out_alert_java()?>
</html>
<?php ob_end_flush(); // 出力バッファをgzip圧縮 END ?>
