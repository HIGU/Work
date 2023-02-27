<?php
//////////////////////////////////////////////////////////////////////////////
// 組立機械稼動管理システムの 実績(履歴) グラフ 表示  Headerフレーム        //
// Copyright (C) 2021-2021 norihisa_ooya@nitto-kohki.co.jp                  //
// Changed history                                                          //
// 2021/03/26 Created  equip_hist_graph_moniHeader.php                      //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL | E_STRICT);
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮
session_start();                            // ini_set()の次に指定すること Script 最上行
require_once ('../equip_function.php');     // TNK 全共通 function
require_once ('../../MenuHeader.php');      // TNK 全共通 menu class
require_once ('../EquipGraphClassMoni.php');    // 設備稼働管理 Graph class
access_log();                               // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader();                   // 認証チェック0=一般以上 戻り先=セッションより タイトル未設定

////////////// サイト設定
$menu->set_site(40, 6);                     // site_index=40(設備メニュー) site_id=11(運転グラフ)
////////////// target設定
// $menu->set_target('application');           // フレーム版の戻り先はtarget属性が必須
$menu->set_target('_parent');               // フレーム版の戻り先はtarget属性が必須

///// GET/POSTのチェック&設定
if (isset($_REQUEST['mac_no'])) {
    $mac_no  = $_REQUEST['mac_no'];
    $plan_no = $_REQUEST['plan_no'];
    $koutei  = $_REQUEST['koutei'];
    $_SESSION['mac_no']  = $mac_no;
    $_SESSION['plan_no'] = $plan_no;
    $_SESSION['koutei']  = $koutei;
} else {
    $mac_no  = $_SESSION['mac_no'];
    $plan_no = $_SESSION['plan_no'];
    $koutei  = $_SESSION['koutei'];
}

////////////// 戻先に渡すパラメーター設定
// $menu->set_retGET('page_keep', 'on');   // name value の順で設定
// $menu->set_retGET('mac_no', $mac_no);   // name value の順で設定
$menu->set_retPOST('page_keep', 'on');   // name value の順で設定
$menu->set_retPOST('mac_no', $mac_no);   // name value の順で設定

/////////// グラフのX軸の時間範囲を取得
if (isset($_REQUEST['equip_xtime'])) {
    $_SESSION['equip_xtime'] = $_REQUEST['equip_xtime'];
    // $_SESSION['equip_graph_page'] = 1;  // 初期化
} else {
    if (isset($_SESSION['equip_hist_xtime'])) {
        $_SESSION['equip_xtime'] = $_SESSION['equip_hist_xtime'];
    } else {
        $_SESSION['equip_xtime'] = 24;
    }
}
$equip_xtime = $_SESSION['equip_xtime'];

if (isset($_REQUEST['reset_page'])) {
    @$_SESSION['equip_graph_page'] = 1;     // 初期化
}

///// ローカル変数の初期化
// $mac_no     = '';
$parts_no   = '　';
$parts_name = '　';
$parts_mate = '　';
$plan_cnt   = '　';
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

if ($mac_no != '') {
    //////////////// 機械マスターから機械名を取得
    $query = "select trim(mac_name) as mac_name from equip_machine_master2 where mac_no={$mac_no} limit 1";
    if (getUniResult($query, $mac_name) <= 0) {
        $mac_name = '　';   // error時は機械名をブランク
    }
    //////////// ヘッダーより見出し用の部品番号・計画数を取得
    $query = "select  to_char(str_timestamp AT TIME ZONE 'JST', 'YYYY/MM/DD HH24:MI:SS') as str_timestamp
                    , to_char(end_timestamp AT TIME ZONE 'JST', 'YYYY/MM/DD HH24:MI:SS') as end_timestamp
                    -- , to_char(CURRENT_TIMESTAMP AT TIME ZONE 'JST', 'YYYY/MM/DD HH24:MI:SS') as end_timestamp
                    , plan_no
                    , koutei
                    , parts_no
                    , plan_cnt
            from
                equip_work_log2_header_moni
            where
                mac_no={$mac_no} and plan_no='{$plan_no}' and koutei={$koutei}
                and work_flg IS FALSE and end_timestamp IS NOT NULL
    ";
    $res_head = array();
    if ( getResult($query, $res_head) <= 0) {
        $_SESSION['s_sysmsg'] .= "<font color='yellow'>{$mac_name}：{$mac_no}：{$plan_no}：{$koutei}では実績データがありません！</font>";
        // header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());
        // exit;
    } else {
        $str_timestamp = $res_head[0]['str_timestamp'];
        $end_timestamp = $res_head[0]['end_timestamp'];
        $plan_no   = $res_head[0]['plan_no'];
        $koutei    = $res_head[0]['koutei'];
        $parts_no  = $res_head[0]['parts_no'];
        $plan_cnt  = $res_head[0]['plan_cnt'];
        $query = "select substr(midsc, 1, 12) as midsc, mzist from miitem where mipn='{$parts_no}'";
        $res_mi = array();
        if ( getResult($query, $res_mi) <= 0) {
            $_SESSION['s_sysmsg'] .= "{$parts_no}で部品マスターの取得に失敗！";
        } else {
            $parts_name = $res_mi[0]['midsc'];
            $parts_mate = $res_mi[0]['mzist'];
            $_SESSION['work_mac_no']  = $mac_no;
            $_SESSION['work_plan_no'] = $plan_no;
            $_SESSION['work_koutei']  = $koutei;
            $view = 'OK';
        }
    }
}
if ($view == 'OK') {
    /////////////// ロット全体のデータ取得 開始時の１レコード
    $query = "select mac_no
                    -- , to_char(date_time AT TIME ZONE 'JST', 'YYYY/MM/DD') as date
                    -- , to_char(date_time AT TIME ZONE 'JST', 'HH24:MI:SS') as time
                    ,mac_state
                    ,work_cnt
                from
                    equip_work_log2_moni
                where
                    plan_no='{$plan_no}' and mac_no={$mac_no} and koutei={$koutei}
                order by
                    date_time ASC
                offset 0 limit 1
            ";
    /*
    $query = "select mac_no
                    -- , to_char(date_time AT TIME ZONE 'JST', 'YYYY/MM/DD') as date
                    -- , to_char(date_time AT TIME ZONE 'JST', 'HH24:MI:SS') as time
                    ,mac_state
                    ,work_cnt
                from
                    equip_work_log2_moni
                where
                    equip_index_moni(mac_no, plan_no, koutei, date_time) > '{$mac_no}{$plan_no}{$koutei}00000000000000'
                and
                    equip_index_moni(mac_no, plan_no, koutei, date_time) < '{$mac_no}{$plan_no}{$koutei}99999999999999'
                order by
                    equip_index_moni(mac_no, plan_no, koutei, date_time) ASC
                offset 0 limit 1
            ";
    */
    $res = array();
    if ( ($rows=getResult($query, $res)) <= 0) {
        $_SESSION['s_sysmsg'] = "機械No：$mac_no 指示No：$plan_no 工程：$koutei の明細がありません。";
    } else {
        $str_mac_state = $res[0]['mac_state'];
        $str_work_cnt  = $res[0]['work_cnt'];
    }
    
    /////////////// ロット全体のデータ取得 終了時の１レコード
    $query = "select mac_no
                    ,mac_state
                    ,work_cnt
                from
                    equip_work_log2_moni
                where
                    plan_no='{$plan_no}' and mac_no={$mac_no} and koutei={$koutei}
                order by
                    date_time DESC
                offset 0 limit 1
            ";
    /*
    $query = "select mac_no
                    ,mac_state
                    ,work_cnt
                from
                    equip_work_log2_moni
                where
                    equip_index_moni(mac_no, plan_no, koutei, date_time) > '{$mac_no}{$plan_no}{$koutei}00000000000000'
                and
                    equip_index_moni(mac_no, plan_no, koutei, date_time) < '{$mac_no}{$plan_no}{$koutei}99999999999999'
                order by
                    equip_index_moni(mac_no, plan_no, koutei, date_time) DESC
                offset 0 limit 1
            ";
    */
    $res = array();
    if ( ($rows=getResult($query, $res)) <= 0) {
        $_SESSION['s_sysmsg'] = "機械No：$mac_no 指示No：$plan_no 工程：$koutei の明細がありません。";
    } else {
        $end_mac_state = $res[0]['mac_state'];
        $end_work_cnt  = $res[0]['work_cnt'];
    }
    /////////// 設備管理グラフのインスタンス作成
    $equip_graph = new EquipGraph($mac_no, $plan_no, $koutei);
    $equip_graph->set_xtime($equip_xtime);      // グラフの希望の時間軸を設定
    $equip_xtime = $equip_graph->out_xtime();   // グラフの時間軸のスケール設定値を取得
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
                    equip_work_log2_moni
                where
                    plan_no='{$plan_no}' and mac_no={$mac_no} and koutei={$koutei} and to_char((CAST(date_time AS TIMESTAMP)), 'YYYYMMDDHH24MISS') <={$graph_strTime}
                order by
                    date_time DESC
                offset 0 limit 1
    ";
    /*
    $query = "select  mac_state
                    , work_cnt
                from
                    equip_work_log2_moni
                where
                    equip_index_moni(mac_no, plan_no, koutei, date_time) > '{$mac_no}{$plan_no}{$koutei}00000000000000'
                and
                    equip_index_moni(mac_no, plan_no, koutei, date_time) <= '{$mac_no}{$plan_no}{$koutei}{$graph_strTime}'
                order by
                    equip_index_moni(mac_no, plan_no, koutei, date_time) DESC
                offset 0 limit 1
    ";
    */
    $res = array();
    if ( ($rows=getResult($query, $res)) <= 0) {
        $query = "select  mac_state
                    , work_cnt
                from
                    equip_work_log2_moni
                where
                    plan_no='{$plan_no}' and mac_no={$mac_no} and koutei={$koutei} and to_char((CAST(date_time AS TIMESTAMP)), 'YYYYMMDDHH24MISS') >={$graph_strTime}
                order by
                    date_time ASC
                offset 0 limit 1
        ";
        /*
        $query = "select  mac_state
                    , work_cnt
                from
                    equip_work_log2_moni
                where
                    equip_index_moni(mac_no, plan_no, koutei, date_time) >= '{$mac_no}{$plan_no}{$koutei}{$graph_strTime}'
                and
                    equip_index_moni(mac_no, plan_no, koutei, date_time) < '{$mac_no}{$plan_no}{$koutei}99999999999999'
                order by
                    equip_index_moni(mac_no, plan_no, koutei, date_time) ASC
                offset 0 limit 1
        ";
        */
        if ( ($rows=getResult($query, $res)) <= 0) {    // 開始のデータが無い場合に条件(上記)を変えてトライ
            $_SESSION['s_sysmsg'] = "機械No：$mac_no 指示No：$plan_no 工程：$koutei 開始：{$graph_strTime} のグラフデータがありません。";
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
                    equip_work_log2_moni
                where
                    plan_no='{$plan_no}' and mac_no={$mac_no} and koutei={$koutei} and to_char((CAST(date_time AS TIMESTAMP)), 'YYYYMMDDHH24MISS') <={$graph_endTime}
                order by
                    date_time DESC
                offset 0 limit 1
            ";
    /*
    $query = "select  mac_state
                    , work_cnt
                from
                    equip_work_log2_moni
                where
                    equip_index_moni(mac_no, plan_no, koutei, date_time) > '{$mac_no}{$plan_no}{$koutei}00000000000000'
                and
                    equip_index_moni(mac_no, plan_no, koutei, date_time) <= '{$mac_no}{$plan_no}{$koutei}{$graph_endTime}'
                order by
                    equip_index_moni(mac_no, plan_no, koutei, date_time) DESC
                offset 0 limit 1
            ";
    */
    $res = array();
    if ( ($rows=getResult($query, $res)) <= 0) {
        $_SESSION['s_sysmsg'] = "機械No：$mac_no 指示No：$plan_no 工程：$koutei 終了：{$graph_endTime} のグラフデータがありません。";
    } else {
        $graph_end_mac_state = $res[0]['mac_state'];
        $graph_end_work_cnt  = $res[0]['work_cnt'];
    }
    ///// ロットの終了を超えたら
    if ("{$graphDateTime['endDate']} {$graphDateTime['endTime']}" > "{$lotDateTime['endDate']} {$lotDateTime['endTime']}") {
        $graph_end_mac_state = '';
        $graph_end_work_cnt  = '';
    }
    /*************************
    if ($graph_endTime > date('YmdHis')) {  // 未来だったら
        $graph_end_mac_state = '';
        $graph_end_work_cnt  = '';
    }
    *************************/
}

//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title("{$mac_no}　{$mac_name}　実績 グラフ 表示");

/////////// HTML Header を出力してキャッシュを制御
$menu->out_html_header();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
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
    document.mhForm.backwardStack.focus();  // 上記はIEのみのためNN対応
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
            <tr class='sub_font'>
                <td align='center' nowrap width='65'>製品No</td>
                <td align='center' nowrap width='85'><?= $parts_no ?></td>
                <td align='center' nowrap width='65'>製品名</td>
                <td class='pick_font' align='center' nowrap width='180'><?= $parts_name ?></td>
                <td align='center' nowrap width='50'>材質</td>
                <td class='pick_font' align='center' nowrap width='70'><?= $parts_mate ?></td>
                <td align='center' nowrap width='65'>計画No</td>
                <td align='center' nowrap width='50'><?= $plan_no ?></td>
                <td align='center' nowrap width='40'>工程</td>
                <td align='center' nowrap width='20'><?= $koutei ?></td>
                <td align='center' nowrap width='60'>計画数</td>
                <td align='right'  nowrap width='60'><?= number_format($plan_cnt) ?></td>
            </tr>
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
                        <input type='hidden' name='equip_xtime' value='<?=$equip_xtime?>' >
                    </td>
                </tr>
                </form>
                <form name='xtime_ctl_left' method='post' action='<?=$menu->out_self()?>' target='_self'>
                <tr>
                    <td>
                        <select name='equip_xtime' class='ret_font' onChange='document.xtime_ctl_left.submit()'>
                            <?=$equip_graph->out_select_xtime($equip_xtime)?>
                        </select>
                        <input type='hidden' name='reset_page' value=''>
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
                        <input style='font-size:10pt; color:blue;' type='submit' name='reload' value='再表示'>
                        <input type='hidden' name='equip_xtime' value='<?=$equip_xtime?>' >
                        </form>
                    </th>
                    <th nowrap>　</th>
                    <th nowrap>年月日</th><th nowrap>時分秒</th><th nowrap>状態</th><th nowrap>加工数</th>
                    <th nowrap>　</th>
                    <th nowrap>年月日</th><th nowrap>時分秒</th><th nowrap>状態</th><th nowrap>加工数</th>
                </tr>
                <tr class='table_font'>
                    <td align='center' nowrap>ロット全体</td>
                    <td class='ext_font' align='center' nowrap>開始</td>
                    <td align='center' nowrap><?php echo $lotDateTime['strDate'] ?></td>
                    <td align='center' nowrap><?php echo $lotDateTime['strTime'] ?></td>
                    <?php if ($str_mac_state != '') { $mac_state_txt = equip_machine_state($mac_no, $str_mac_state, $bg_color, $txt_color); ?>
                    <td align='center' nowrap bgcolor='<?=$bg_color?>'><font color='<?=$txt_color?>'><?=$mac_state_txt?></font></td>
                    <?php } else { ?>
                    <td align='center' nowrap>　</td>
                    <?php } ?>
                    <td align='right' nowrap><?php echo number_format($str_work_cnt) ?></td>
                    
                    <td class='ext_font' align='center' nowrap>完了</td>
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
                        <input type='hidden' name='equip_xtime' value='<?=$equip_xtime?>' >
                    </td>
                </tr>
                </form>
                <form name='xtime_ctl_right' method='post' action='<?=$menu->out_self()?>' target='_self'>
                <tr>
                    <td>
                        <select name='equip_xtime' class='ret_font' onChange='document.xtime_ctl_right.submit()'>
                            <?=$equip_graph->out_select_xtime($equip_xtime)?>
                        </select>
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
    document.MainForm.action = 'equip_hist_graph_moniList.php';
    document.MainForm.submit();
</Script>
<?php ob_end_flush(); // 出力バッファをgzip圧縮 END ?>
