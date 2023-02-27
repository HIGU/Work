<?php
//////////////////////////////////////////////////////////////////////////////
// 機械稼動管理システムの 運転日報対応 グラフ 表示  Headerフレーム          //
// Copyright (C) 2004-2005 2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp //
// Changed history                                                          //
// 2004/08/25 Created  equip_report_graphHeader.php                         //
// 2004/08/30 未来チェックをやめ($graph_endTime>"$end_date 08:30:00")に変更 //
// 2004/08/31 更新ボタンを再表示に名前を変更(勘違いの防止)                  //
// 2004/11/30 初回の機械で当日のデータ対応のため変更 メッセージ対応         //
// 2005/06/24 F2/F12キーで戻るための対応で JavaScriptの set_focus()を追加   //
// 2005/07/09 上記の JavaScript は中止し MenuHeader Class で対応            //
// 2005/08/30 php5 へ移行  (=& new → = new)                                //
// 2005/09/30 日報用を雛型にした月次グラフ equip_MonthReport_graphHeader.php//
// 2007/09/26 E_ALL → E_ALL | E_STRICT  へ変更                             //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL | E_STRICT);
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮
session_start();                            // ini_set()の次に指定すること Script 最上行
require_once ('../equip_function.php');     // TNK 全共通 function
require_once ('../../MenuHeader.php');      // TNK 全共通 menu class
require_once ('../EquipGraphClass_MonthReport.php');    // 設備稼働管理 Graph class
require_once ('../../tnk_func.php');        // TNK(専用)function(最終日算出用)
access_log();                               // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader();                   // 認証チェック0=一般以上 戻り先=セッションより タイトル未設定

////////////// サイト設定
$menu->set_site(40, 999);                   // site_index=40(設備メニュー2) site_id=999(サイトを開く)
////////////// target設定
// $menu->set_target('application');           // フレーム版の戻り先はtarget属性が必須
$menu->set_target('_parent');               // フレーム版の戻り先はtarget属性が必須

///// GET/POSTのチェック&設定
$mac_no = @$_REQUEST['mac_no'];
if ($mac_no == '') {
    $reload = 'disabled';
} else {
    $reload = '';
    $_SESSION['mac_no'] = $mac_no;
}
if (isset($_REQUEST['str_date'])) {
    $str_date = $_REQUEST['str_date'];
    // $str_dateの最終日にセット last_day()関数を使用
    $end_date = date('Y/m/d', 
        mktime(0, 0, 0, substr($str_date, 5, 2), last_day(substr($str_date, 0, 4), substr($str_date, 5, 2)), substr($str_date, 0, 4))
        );
} else {
    // 当月01日にセット(?月1日)
    $str_date = date('Y/m/') . '01';
    // 当日
    $end_date = date('Y/m/d');
}
$_SESSION['str_date'] = $str_date;
$_SESSION['end_date'] = $end_date;

/////////// さかのぼれる月数の設定
$year  = date('Y');
$month = date('m');
for ($rows_date=0; $rows_date<12; $rows_date++) {
    $set_viewDate[$rows_date] = $year . '/' . $month;
    $set_date[$rows_date] = $year . '/' . $month . '/01';
    $month--;
    if ($month <= 0) {
        $year--;
        $month = 12;
    }
    $month = sprintf('%02d', $month);
}

/////////// グラフのX軸の時間範囲を取得
if (isset($_REQUEST['equip_xtime'])) {
    $_SESSION['equip_xtime'] = $_REQUEST['equip_xtime'];
    // $_SESSION['equip_graph_page'] = 1;  // 初期化
} else {
    // $_SESSION['equip_xtime'] = 744;     // (24時間 X 31日)
    $_SESSION['equip_xtime'] = last_day(substr($str_date, 0, 4), substr($str_date, 5, 2)) * 24;
}
$equip_xtime = $_SESSION['equip_xtime'];

if (isset($_REQUEST['reset_page'])) {
    $_SESSION['equip_graph_page'] = 1;     // 初期化
}

///// ローカル変数の初期化
$mac_name   = '';
$rowspan    = 0;
$view       = 'NG';

$str_mac_state ='';
$str_work_cnt  ='';
$end_mac_state ='';
$end_work_cnt  ='';
$graph_str_mac_state = '';
$graph_str_work_cnt  = '';
$graph_end_mac_state = '';
$graph_end_work_cnt  = '';
$lotDateTime = array('strDate' => '　', 'strTime' => '　', 'endDate' => '　', 'endTime' => '　');
$graphDateTime = array('strDate' => '　', 'strTime' => '　', 'endDate' => '　', 'endTime' => '　');
$page_ctl_left  = 'disabled';
$page_ctl_right = 'disabled';

$factory = @$_SESSION['factory'];
//////////// 機械マスターから設備番号・設備名のリストを取得
if ($factory == '') {
    $query = "select mac_no                 AS mac_no
                    , substr(mac_name,1,7)  AS mac_name
                from
                    equip_machine_master2
                where
                    survey='Y'
                    and
                    mac_no!=9999
                order by mac_no ASC
    ";
} else {
    $query = "select mac_no                 AS mac_no
                    , substr(mac_name,1,7)  AS mac_name
                from
                    equip_machine_master2
                where
                    survey='Y'
                    and
                    mac_no!=9999
                    and
                    factory='{$factory}'
                order by mac_no ASC
    ";
}
$res_sel = array();
if (($rows_sel = getResult($query, $res_sel)) < 1) {
    $_SESSION['s_sysmsg'] .= "<font color='yellow'>機械マスターに登録がありません！</font>";
} else {
    $mac_no_name = array();
    for ($i=0; $i<$rows_sel; $i++) {
        ///// 機械番号と名称の間にスペース追加
        $mac_no_name[$i] = $res_sel[$i]['mac_no'] . " " . trim($res_sel[$i]['mac_name']);
    }
}

while ($mac_no != '') {
    //////////////// 機械マスターから機械名を取得
    $query = "select trim(mac_name) as mac_name from equip_machine_master2 where mac_no={$mac_no} limit 1";
    if (getUniResult($query, $mac_name) <= 0) {
        $mac_name = '　';   // error時は機械名をブランク
    }
    //////////// ログより08:30:00の開始時点(直前)の指示番号・工程と状態・生産数を取得
    $query = "select siji_no
                    ,koutei
                    ,mac_state
                    ,work_cnt
                from
                    equip_work_log2
                where
                    equip_index2(mac_no, date_time) <= '{$mac_no}{$str_date} 08:30:00'
                order by
                    equip_index2(mac_no, date_time) DESC
                offset 0 limit 1
    ";
    $res_str = array();
    if (getResult($query, $res_str) <= 0) {
        $res_str['siji_no']   = '';
        $res_str['koutei']    = '';
    } else {
        $res_str['siji_no']   = $res_str[0]['siji_no'];
        $res_str['koutei']    = $res_str[0]['koutei'];
        $str_mac_state = $res_str[0]['mac_state'];
        $str_work_cnt  = $res_str[0]['work_cnt'];
    }
    //////////// ログより次の日の08:30:00の終了時点の指示番号・工程と状態・生産数を取得
    $query = "select siji_no
                    ,koutei
                    ,mac_state
                    ,work_cnt
                from
                    equip_work_log2
                where
                    equip_index2(mac_no, date_time) <= '{$mac_no}{$end_date} 08:30:00'
                order by
                    equip_index2(mac_no, date_time) DESC
                offset 0 limit 1
    ";
    $res_end = array();
    if (getResult($query, $res_end) <= 0) {
        $res_end['siji_no']   = '';
        $res_end['koutei']    = '';
        // $_SESSION['s_sysmsg'] .= "データなし";
    } else {
        $res_end['siji_no']   = $res_end[0]['siji_no'];
        $res_end['koutei']    = $res_end[0]['koutei'];
        $end_mac_state = $res_end[0]['mac_state'];
        $end_work_cnt  = $res_end[0]['work_cnt'];
    }
    //////////// ログより指示番号と工程のサマリーを取得
    $query = "select siji_no
                    ,koutei
                from
                    equip_work_log2
                where
                    equip_index2(mac_no, date_time) >= '{$mac_no}{$str_date} 08:30:00'
                    and
                    equip_index2(mac_no, date_time) <= '{$mac_no}{$end_date} 08:30:00'
                group by
                    siji_no, koutei
                offset 0
    ";
    $res_log = array();
    if (($rows_log=getResult($query, $res_log)) <= 0) {
        // 日報の１日間でログが無ければ
        // 直前のデータを採用
        if ($res_str['siji_no'] == '') {    // 直前のデータも無ければ 初回品
            $_SESSION['s_sysmsg'] .= "<font color='yellow'>{$mac_no}：{$mac_name} {$str_date}日報日では運転データがありません！</font>";
            break;
        }
        ///// ヘッダーを見て完了しているかチェック
        $query = "select CASE
                            WHEN end_timestamp IS NOT NULL THEN
                                end_timestamp < CAST('{$str_date} 08:30:00' AS TIMESTAMP)
                            ELSE
                                FALSE
                         END
                    from
                        equip_work_log2_header
                    where
                        mac_no = {$mac_no} and siji_no = {$res_str['siji_no']} and koutei = {$res_str['koutei']}
                    offset 0 limit 1
        ";
        if (getUniResult($query, $kanryou) <= 0) {
            $_SESSION['s_sysmsg'] .= "{$mac_no}：{$mac_name} {$res_str['siji_no']} ではヘッダーがありません！";
            break;
        }
        // 直前のデータが完了品の場合はSQLでチェックする
        if ($kanryou == 't') { // FALSE='f'
            $_SESSION['s_sysmsg'] .= "{$mac_no}：{$mac_name} {$str_date}の日報日では運転データがありません！";
            break;
        }
        $res_log[0]['siji_no'] = $res_str['siji_no'];
        $res_log[0]['koutei']  = $res_str['koutei'];
        $rows_log = 1;
    } else {
        if ($res_str['siji_no'] == '') {    // 初回品で当日データに対応
            // 以下を実行するとエラーにはならないが 8:30～スタートしたように見えるためコメントとする
            /***********************************
            //////////// ログより08:30:00の開始時点(直前)のデータは無いため途中～取得 指示番号・工程と状態・生産数を取得
            $query = "select siji_no
                    ,koutei
                    ,mac_state
                    ,work_cnt
                from
                    equip_work_log2
                where
                    equip_index2(mac_no, date_time) >= '{$mac_no}{$str_date} 08:30:00'
                order by
                    equip_index2(mac_no, date_time) ASC
                offset 0 limit 1
            ";
            $res_str = array();
            if (getResult($query, $res_str) <= 0) {
                $res_str['siji_no']   = '';
                $res_str['koutei']    = '';
            } else {
                $res_str['siji_no']   = $res_str[0]['siji_no'];
                $res_str['koutei']    = $res_str[0]['koutei'];
                $str_mac_state = $res_str[0]['mac_state'];
                $str_work_cnt  = $res_str[0]['work_cnt'];
            }
            ***********************************/
            $_SESSION['s_sysmsg'] .= "{$mac_no}：{$mac_name} {$str_date} 初回の機械です。明日から日報開始となります。";
            break;
        }
    }
    
    //////////// ヘッダーより見出し用の部品番号・計画数を取得 開始直前の１件分
    $query = "select  parts_no
                    , plan_cnt
                from
                    equip_work_log2_header
                where
                    mac_no = {$mac_no} and siji_no = {$res_str['siji_no']} and koutei = {$res_str['koutei']}
    ";
    $res_head = array();
    if ( getResult($query, $res_head) <= 0) {
        $_SESSION['s_sysmsg'] .= "{$mac_no}：{$mac_name} {$str_date}日報日ではヘッダーがありません！1";
        break;
    } else {
        $res_str['parts_no'] = $res_head[0]['parts_no'];
        $res_str['plan_cnt'] = $res_head[0]['plan_cnt'];
        $query = "select substr(midsc, 1, 12) as midsc, substr(mzist, 1, 8) as mzist from miitem where mipn='{$res_str['parts_no']}'";
        $res_mi = array();
        if ( getResult($query, $res_mi) <= 0) {
            $_SESSION['s_sysmsg'] .= "{$res_str['parts_no']}で部品マスターの取得に失敗！";
            break;
        } else {
            $res_str['parts_name'] = $res_mi[0]['midsc'];
            $res_str['parts_mate'] = $res_mi[0]['mzist'];
        }
    }
    //////////// ヘッダーより見出し用の部品番号・計画数を取得 ログサマリーの未定件数分
    for ($r=0; $r<$rows_log; $r++) {
        if ( ($res_str['siji_no'] == $res_log[$r]['siji_no']) && ($res_str['koutei'] == $res_log[$r]['koutei']) ) {
            ///// 直前のデータと同じならコピー
            $res_log[$r]['parts_no']   = $res_str['parts_no'];
            $res_log[$r]['plan_cnt']   = $res_str['plan_cnt'];
            $res_log[$r]['parts_name'] = $res_str['parts_name'];
            $res_log[$r]['parts_mate'] = $res_str['parts_mate'];
        } else {
            $rowspan = 1;
            $query = "select  parts_no
                            , plan_cnt
                        from
                            equip_work_log2_header
                        where
                            mac_no = {$mac_no} and siji_no = {$res_log[$r]['siji_no']} and koutei = {$res_log[$r]['koutei']}
            ";
            $res_head = array();
            if ( getResult($query, $res_head) <= 0) {
                $_SESSION['s_sysmsg'] .= "{$mac_no}：{$mac_name} {$str_date}日報日ではヘッダーがありません！2";
                break;
            } else {
                $res_log[$r]['parts_no'] = $res_head[0]['parts_no'];
                $res_log[$r]['plan_cnt'] = $res_head[0]['plan_cnt'];
                $query = "select substr(midsc, 1, 12) as midsc, substr(mzist, 1, 8) as mzist from miitem where mipn='{$res_log[$r]['parts_no']}'";
                $res_mi = array();
                if ( getResult($query, $res_mi) <= 0) {
                    $_SESSION['s_sysmsg'] .= "{$res_log['parts_no']}で部品マスターの取得に失敗！";
                    break;
                } else {
                    $res_log[$r]['parts_name'] = $res_mi[0]['midsc'];
                    $res_log[$r]['parts_mate'] = $res_mi[0]['mzist'];
                }
            }
        }
    }
    $_SESSION['work_mac_no']  = $mac_no;
    $_SESSION['work_sdate']   = $str_date;
    $_SESSION['work_edate']   = $end_date;
    $view = 'OK';
    break;
}
while ($view == 'OK') {
    /////////// 設備管理グラフのインスタンス作成
    $equip_graph = new EquipGraphReport($mac_no, $str_date, $end_date);
    $equip_graph->set_xtime($equip_xtime);
    if (isset($_REQUEST['forward'])) {
        $equip_graph->set_graph_page(+1);
    } elseif (isset($_REQUEST['backward'])) {
        $equip_graph->set_graph_page(-1);
    } else {
        $equip_graph->set_graph_page(0);
    }
    if ($equip_graph->out_page_ctl('backward')) $page_ctl_left = '';
    if ($equip_graph->out_page_ctl('forward')) $page_ctl_right = '';
    // ロット全体の書式付 DATE TIME の取得  配列(strDate, strTime, endDate, endTime)
    $lotDateTime = $equip_graph->out_lot_timestamp();
    // グラフの範囲内の書式付 DATE TIME の取得  配列(strDate, strTime, endDate, endTime)
    $graphDateTime = $equip_graph->out_graph_timestamp();
    // グラフ範囲の開始・終了日時の取得(key field)
    $graph_strTime = $equip_graph->out_graph_strTime();
    $graph_endTime = $equip_graph->out_graph_endTime();
    /////////////// グラフ範囲のデータ取得 開始時の１レコード
    $query = "select  mac_state
                    , work_cnt
                from
                    equip_work_log2
                where
                    equip_index2(mac_no, date_time) <= '{$mac_no}{$graph_strTime}'
                order by
                    equip_index2(mac_no, date_time) DESC
                offset 0 limit 1
    ";
    $res = array();
    if ( ($rows=getResult($query, $res)) <= 0) {
        ///// 開始のデータが無い場合に条件(上記)を変えてトライ
        $query = "select  mac_state
                    , work_cnt
                from
                    equip_work_log2
                where
                    equip_index2(mac_no, date_time) >= '{$mac_no}{$graph_strTime}'
                    and
                    equip_index2(mac_no, date_time) <= '{$mac_no}{$graph_endTime}'
                order by
                    equip_index2(mac_no, date_time) ASC
                offset 0 limit 1
        ";
        if ( ($rows=getResult($query, $res)) <= 0) {
            // 上のロット情報取得でチェックしているためここが実行されることは無い
        } else {
            $graph_str_mac_state = $res[0]['mac_state'];
            $graph_str_work_cnt  = $res[0]['work_cnt'];
        }
    } else {
        $graph_str_mac_state = $res[0]['mac_state'];
        $graph_str_work_cnt  = $res[0]['work_cnt'];
    }
    /////////////// グラフ範囲のデータ取得 終了時の１レコード
    $query = "select  mac_state
                    , work_cnt
                from
                    equip_work_log2
                where
                    equip_index2(mac_no, date_time) <= '{$mac_no}{$graph_endTime}'
                order by
                    equip_index2(mac_no, date_time) DESC
                offset 0 limit 1
            ";
    $res = array();
    if ( ($rows=getResult($query, $res)) <= 0) {
        // 上のロット情報取得でチェックしているためここが実行されることは無い
    } else {
        $graph_end_mac_state = $res[0]['mac_state'];
        $graph_end_work_cnt  = $res[0]['work_cnt'];
    }
    if ($graph_endTime > "$end_date 08:30:00") {
        $graphDateTime['endDate'] = $lotDateTime['endDate'];
        $graphDateTime['endTime'] = $lotDateTime['endTime'];
        $graph_end_mac_state = $end_mac_state;
        $graph_end_work_cnt  = $end_work_cnt;
    }
    /********************   // 上記を追加する事によって未来は無い
    if ($graph_endTime > date('Y/m/d H:i:s')) {  // 未来だったら
        $graph_end_mac_state = '';
        $graph_end_work_cnt  = '';
    }
    ********************/
    break;
}

//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title("{$mac_no}　{$mac_name}　月次 稼動 時間 グラフ");

/////////// HTML Header を出力してキャッシュを制御
$menu->out_html_header();
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="Content-Style-Type" content="text/css">
<title><?= $menu->out_title() ?></title>
<?= $menu->out_css() ?>
<?= $menu->out_site_java() ?>
<style type='text/css'>
<!--
select {
    background-color:   teal;
    color:              white;
}
.sub_font {
    font-size:      11.5pt;
    font-weight:    bold;
    font-family:    monospace;
}
.pick_font {
    font-size:      9.5pt;
    font-weight:    bold;
    font-family:    monospace;
}
th {
    font-size:          10.5pt;
    font-weight:        bold;
    font-family:        monospace;
    color:              blue;
    /* background-color:   yellow; */
}
.item {
    position:       absolute;
    top:            90px;
    left:           90px;
}
.table_font {
    font-size:      11.5pt;
    font-family:    monospace;
}
.ext_font {
    /* background-color:   yellow; */
    color:              blue;
    font-size:          10.5pt;
    font-weight:        bold;
    font-family:        monospace;
}
.winbox {
    border-style: solid;
    border-width: 1px;
    border-top-color: #FFFFFF;
    border-left-color: #FFFFFF;
    border-right-color: #DFDFDF;
    border-bottom-color: #DFDFDF;
}
-->
</style>
<form name='MainForm' method='post'>
    <input type='hidden' name='select' value=''>
</form>
<script language='JavaScript'>
<!--
/* 初期入力フォームのエレメントにフォーカスさせる */
function set_focus() {
    // document.body.focus();   // F2/F12キーを有効化する対応
    // document.mhForm.backwardStack.focus();  // 上記はIEのみのためNN対応
    // document.mac_form.mac_no.focus();  // カーソルキーで機械を選択できるようにする
}
// -->
</script>
</head>
<body onLoad='set_focus()'>
    <center>
<?= $menu->out_title_border() ?>
        
        <!----------------- 見出しを表示 ------------------------>
        <table width='100%' bgcolor='#d6d3ce'  border='1' cellspacing='0' cellpadding='1'>
            <tr><td> <!-- ダミー(デザイン用) -->
        <table width=100% bordercolordark='white' bordercolorlight='#bdaa90' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='1'>
            <?php if ($view != 'OK') {?>
            <tr class='sub_font'>
                <td align='center' width='100'>
                    <form name='mac_form' method='post' action='<?= $menu->out_self() ?>'>
                        <select name='mac_no' class='ret_font' onChange='document.mac_form.submit()'>
                        <?php if ($mac_no == '') echo "<option value=''>機械選択</option>\n" ?>
                        <?php
                        for ($j=0; $j<$rows_sel; $j++) {
                            if ($mac_no == $res_sel[$j]['mac_no']) {
                                printf("<option value='%s' selected>%s</option>\n", $res_sel[$j]['mac_no'], $mac_no_name[$j]);
                            } else {
                                printf("<option value='%s'>%s</option>\n", $res_sel[$j]['mac_no'], $mac_no_name[$j]);
                            }
                        }
                        ?>
                        </select>
                        <input type='hidden' name='reset_page' value=''>
                        <input type='hidden' name='str_date' value='<?=$str_date?>'>
                    </form>
                </td>
            </tr>
            <?php } else {?>
            <?php for ($r=0; $r<$rows_log; $r++) { ?>
            <tr class='sub_font'>
                <?php if ($r == 0) { ?>
                <td rowspan='<?=($rows_log+1-$rowspan)?>' align='center' width='100'>
                    <form name='mac_form' method='post' action='<?= $menu->out_self() ?>'>
                        <select name='mac_no' class='ret_font' onChange='document.mac_form.submit()'>
                        <?php if ($mac_no == '') echo "<option value=''>機械選択</option>\n" ?>
                        <?php
                        for ($j=0; $j<$rows_sel; $j++) {
                            if ($mac_no == $res_sel[$j]['mac_no']) {
                                printf("<option value='%s' selected>%s</option>\n", $res_sel[$j]['mac_no'], $mac_no_name[$j]);
                            } else {
                                printf("<option value='%s'>%s</option>\n", $res_sel[$j]['mac_no'], $mac_no_name[$j]);
                            }
                        }
                        ?>
                        </select>
                        <input type='hidden' name='reset_page' value=''>
                        <input type='hidden' name='str_date' value='<?=$str_date?>'>
                    </form>
                </td>
                <?php } ?>
                <td align='center' nowrap width='65'>部品No</td>
                <td align='center' nowrap width='85'><?= $res_log[$r]['parts_no'] ?></td>
                <td align='center' nowrap width='65'>部品名</td>
                <td class='pick_font' align='center' nowrap width='130'><?= $res_log[$r]['parts_name'] ?></td>
                <td align='center' nowrap width='50'>材質</td>
                <td class='pick_font' align='center' nowrap width='70'><?= $res_log[$r]['parts_mate'] ?></td>
                <td align='center' nowrap width='65'>指示No</td>
                <td align='center' nowrap width='50'><?= $res_log[$r]['siji_no'] ?></td>
                <td align='center' nowrap width='40'>工程</td>
                <td align='center' nowrap width='20'><?= $res_log[$r]['koutei'] ?></td>
                <td align='center' nowrap width='60'>計画数</td>
                <td align='right'  nowrap width='60'><?= number_format($res_log[$r]['plan_cnt']) ?></td>
            </tr>
            <?php } ?>
            <?php } ?>
        </table>
            </td></tr>
        </table> <!-- ダミーEnd -->
        
        <!-- <hr color='797979'> -->
        
        <table width='100%' border='0'>
        <tr>
        <td>
            <!-------------- グラフ表示のページコントロール作成 左 -------------->
            <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
               <tr><td> <!-- ダミー(デザイン用) -->
            <table class='winbox' width=100% bgcolor='#d6d3ce'>
                <form name='page_ctl_left' method='post' action='<?=$menu->out_self()?>' target='_self'>
                <tr>
                    <td>
                        <input class='pt11b' type='submit' name='backward' value='前ページ' <?=$page_ctl_left?>>
                        <input type='hidden' name='mac_no' value='<?=$mac_no?>'>
                        <input type='hidden' name='str_date' value='<?=$str_date?>'>
                    </td>
                </tr>
                </form>
                <form name='xtime_ctl_left' method='post' action='<?=$menu->out_self()?>' target='_self'>
                <tr>
                    <td>
                        <select name='equip_xtime' class='ret_font' onChange='document.xtime_ctl_left.submit()'>
                            <?php // if ($view=='OK') { echo $equip_graph->out_select_xtime($equip_xtime); ?>
                            <?php // } else { ?>
                            <option value='24'  <?php if ($equip_xtime==24) echo 'selected';?>>&nbsp;1日間</option>
                            <option value='48'  <?php if ($equip_xtime==48) echo 'selected';?>>&nbsp;2日間</option>
                            <option value='72'  <?php if ($equip_xtime==72) echo 'selected';?>>&nbsp;3日間</option>
                            <option value='96'  <?php if ($equip_xtime==96) echo 'selected';?>>&nbsp;4日間</option>
                            <option value='120' <?php if ($equip_xtime==120) echo 'selected';?>>&nbsp;5日間</option>
                            <option value='168' <?php if ($equip_xtime==168) echo 'selected';?>>&nbsp;7日間</option>
                            <option value='240' <?php if ($equip_xtime==240) echo 'selected';?>>10日間</option>
                            <option value='360' <?php if ($equip_xtime==360) echo 'selected';?>>15日間</option>
                            <option value='648' <?php if ($equip_xtime==648) echo 'selected';?>>27日間</option>
                            <option value='672' <?php if ($equip_xtime==672) echo 'selected';?>>28日間</option>
                            <option value='696' <?php if ($equip_xtime==696) echo 'selected';?>>29日間</option>
                            <option value='720' <?php if ($equip_xtime==720) echo 'selected';?>>30日間</option>
                            <option value='744' <?php if ($equip_xtime==744) echo 'selected';?>>31日間</option>
                            <?php // } ?>
                        </select>
                        <input type='hidden' name='mac_no' value='<?=$mac_no?>'>
                        <input type='hidden' name='reset_page' value=''>
                        <input type='hidden' name='str_date' value='<?=$str_date?>'>
                    </td>
                </tr>
                </form>
            </table> <!----- ダミー End ----->
                </td></tr>
            </table>
        </td>
        <td>
            <!--------------- ここから見出し表(開始と現在)２行を表示する -------------------->
            <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='1'>
                <tr><td> <!-- ダミー(デザイン用) -->
            <table width=100% bordercolordark='white' bordercolorlight='#bdaa90' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='2'>
                <tr>
                    <th nowrap>
                        <form name='reload_form' method='post' action='<?=$menu->out_self()?>' target='_self'>
                        <input style='font-size:10pt; color:blue;' type='submit' name='reload' value='再表示' <?=$reload?>>
                        <input type='hidden' name='mac_no' value='<?=$mac_no?>'>
                        <input type='hidden' name='str_date' value='<?=$str_date?>'>
                        </form>
                    </th>
                    <th nowrap>　</th>
                    <th nowrap>年月日</th><th nowrap>時分秒</th><th nowrap>状態</th><th nowrap>加工数</th>
                    <th nowrap>　</th>
                    <th nowrap>年月日</th><th nowrap>時分秒</th><th nowrap>状態</th><th nowrap>加工数</th>
                </tr>
                <tr class='table_font'>
                    <td align='center' nowrap>日 報 範囲</td>
                    <td class='ext_font' align='center' nowrap>開始</td>
                    <td align='center' nowrap><?php echo $lotDateTime['strDate'] ?></td>
                    <td align='center' nowrap><?php echo $lotDateTime['strTime'] ?></td>
                    <?php if ($str_mac_state != '') { $mac_state_txt = equip_machine_state($mac_no, $str_mac_state, $bg_color, $txt_color); ?>
                    <td align='center' nowrap bgcolor='<?=$bg_color?>'><font color='<?=$txt_color?>'><?=$mac_state_txt?></font></td>
                    <?php } else { ?>
                    <td align='center' nowrap>　</td>
                    <?php } ?>
                    <td align='right' nowrap><?php echo number_format($str_work_cnt) ?></td>
                    
                    <td class='ext_font' align='center' nowrap>終了</td>
                    <td align='center' nowrap><?php echo $lotDateTime['endDate'] ?></td>
                    <td align='center' nowrap><?php echo $lotDateTime['endTime'] ?></td>
                    <?php if ($end_mac_state != '') { $mac_state_txt = equip_machine_state($mac_no, $end_mac_state, $bg_color, $txt_color); ?>
                    <td align='center' nowrap bgcolor='<?=$bg_color?>'><font color='<?=$txt_color?>'><?=$mac_state_txt?></font></td>
                    <?php } else { ?>
                    <td align='center' nowrap>　</td>
                    <?php } ?>
                    <td align='right' nowrap><?php echo number_format($end_work_cnt) ?></td>
                </tr>
                <tr class='table_font'>
                    <td align='center' nowrap>グラフ範囲</td>
                    <td class='ext_font' align='center' nowrap>開始</td>
                    <td align='center' nowrap><?php echo $graphDateTime['strDate'] ?></td>
                    <td align='center' nowrap><?php echo $graphDateTime['strTime'] ?></td>
                    <?php if ($graph_str_mac_state != '') { $mac_state_txt = equip_machine_state($mac_no, $graph_str_mac_state, $bg_color, $txt_color); ?>
                    <td align='center' nowrap bgcolor='<?=$bg_color?>'><font color='<?=$txt_color?>'><?=$mac_state_txt?></font></td>
                    <?php } else { ?>
                    <td align='center' nowrap>　</td>
                    <?php } ?>
                    <td align='right' nowrap><?php echo number_format($graph_str_work_cnt) ?></td>
                    
                    <td class='ext_font' align='center' nowrap>終了</td>
                    <td align='center' nowrap><?php echo $graphDateTime['endDate'] ?></td>
                    <td align='center' nowrap><?php echo $graphDateTime['endTime'] ?></td>
                    <?php if ($graph_end_mac_state != '') { $mac_state_txt = equip_machine_state($mac_no, $graph_end_mac_state, $bg_color, $txt_color); ?>
                    <td align='center' nowrap bgcolor='<?=$bg_color?>'><font color='<?=$txt_color?>'><?=$mac_state_txt?></font></td>
                    <?php } else { ?>
                    <td align='center' nowrap>　</td>
                    <?php } ?>
                    <td align='right' nowrap><?php if ($graph_end_work_cnt != '') echo number_format($graph_end_work_cnt); else echo '　'; ?></td>
                </tr>
            </table>
                </td></tr>
            </table> <!-- ダミーEnd -->
            
        </td>
        <td>
            <!-------------- グラフ表示のページコントロール作成 右 -------------->
            <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
               <tr><td> <!-- ダミー(デザイン用) -->
            <table class='winbox' width=100% bgcolor='#d6d3ce'>
                <form name='page_ctl_right' method='post' action='<?=$menu->out_self()?>' target='_self'>
                <tr>
                    <td>
                        <input class='pt11b' type='submit' name='forward' value='次ページ' <?=$page_ctl_right?>>
                        <input type='hidden' name='mac_no' value='<?=$mac_no?>'>
                        <input type='hidden' name='str_date' value='<?=$str_date?>'>
                    </td>
                </tr>
                </form>
                <form name='xtime_ctl_right' method='post' action='<?=$menu->out_self()?>' target='_self'>
                <tr>
                    <td>
                        <select name='str_date' class='pt11b' onChange='document.xtime_ctl_right.submit()'>
                            <?php for ($i=0; $i<$rows_date; $i++) { ?>
                            <option value='<?=$set_date[$i]?>'<?php if ($str_date==$set_date[$i]) echo 'selected';?>><?=$set_viewDate[$i]?></option>
                            <?php } ?>
                        </select>
                        <input type='hidden' name='mac_no' value='<?=$mac_no?>'>
                        <input type='hidden' name='reset_page' value=''>
                    </td>
                </tr>
                </form>
            </table> <!----- ダミー End ----->
                </td></tr>
            </table>
        </td>
        </tr>
        </table>
    </center>
</body>
</html>
<Script Language='JavaScript'>
    document.MainForm.select.value = '<?=$view?>';
    document.MainForm.target = 'List';
    document.MainForm.action = 'equip_MonthReport_graphList.php';
    document.MainForm.submit();
</Script>
<?=$menu->out_alert_java()?>
<?php ob_end_flush(); // 出力バッファをgzip圧縮 END ?>
