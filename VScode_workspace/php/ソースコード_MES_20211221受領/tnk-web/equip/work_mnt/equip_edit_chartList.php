<?php
//////////////////////////////////////////////////////////////////////////////
// 機械稼動管理システムの 指示変更及びログ編集  リスト部の定義              //
// Copyright (C) 2004-2021 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2004/07/28 Created  equip_edit_chartHader.php                            //
// 2004/08/02 前のロットの抽出に(siji_no={$siji_no1} or siji_no={$siji_no2})//
// 2004/08/08 フレーム版の戻り先をapplication→_parentに変更(FRAME無し対応) //
//            work_log2のUPDATEにequip_index()関数を使用するようにSQL文変更 //
// 2005/03/03 cnt_chg_timeでもequip_index()関数を使用するように変更         //
//            注意しなければならないのは修正個所はdate_timeの形式が違うこと //
// 2007/06/29 カウンターマスター対応                                        //
// 2007/09/18 E_ALL | E_STRICT へ変更                                       //
// 2011/06/23 同じ時間に２つデータが入ってきて、更新でエラーが発生した      //
//            データを削除して対応                                     大谷 //
// 2021/11/17 中断計画の場合も指示変更できるように変更                 大谷 //
//            前の計画が中断中の場合、変更時に前の計画の完了時間を変更しない//
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL | E_STRICT);
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
ini_set('max_execution_time', 120);         // 最大実行時間=120秒 SAPI版
ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮
session_start();                            // ini_set()の次に指定すること Script 最上行
require_once ('../equip_function.php');     // 設備メニュー 共通 function (function.phpを含む)
require_once ('../../MenuHeader.php');      // TNK 全共通 menu class
access_log();                               // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader();                   // 認証チェック0=一般以上 戻り先=セッションより タイトル未設定

////////////// サイト設定
$menu->set_site(40, 11);                    // site_index=40(設備メニュー) site_id=11(指示変更)
////////////// target設定
// $menu->set_target('application');           // フレーム版の戻り先はtarget属性が必須
$menu->set_target('_parent');               // フレーム版の戻り先はtarget属性が必須

///////// HTML部で自分自身を多用するため、ここで変数へ格納
$current_script = $_SERVER['PHP_SELF'];

$offset = 0;        // テスト用
$limit  = 1000;     // テスト用
$genLimit = 100;    // 現在加工中の表示レコード数
$chg_time_stop = '';    // 開始日時の変更可否
$chg_time_end  = '';    // 中断計画終了日時の変更可否

if (isset($_REQUEST['select'])) {
    $select = $_REQUEST['select'];
} else {
    $select = 'NG';
}
if (isset($_REQUEST['sort'])) {
    $sort = $_REQUEST['sort'];
} else {
    $sort = 'DESC';
}
$mac_no   = $_SESSION['mac_no'];
////////// リスト指示が来た
if ($select != 'NG') {      // NN7.1- 対策 (frameの読込む順番の問題)
    $siji_no1 = $_SESSION['siji_no1'];
    $koutei1  = $_SESSION['koutei1'];
    $siji_no2 = $_SESSION['siji_no2'];
    $koutei2  = $_SESSION['koutei2'];
}

//////////// スタート日時の変更指示が来た
if (isset($_SESSION['chg_time'])) {
    $chg_time = $_SESSION['chg_time'];
    $chg_time_key = $chg_time;
    $chg_time = substr($chg_time, 0, 8). ' ' . substr($chg_time, 8, 6);
    
    /////////// begin トランザクション開始
    if ($con = db_connect()) {
        query_affected_trans($con, 'begin');
    } else {
        $_SESSION['s_sysmsg'] = 'データベースに接続できません！';
        exit();
    }
    ///// まずヘッダーの現在加工中のスタート日時を変更
    $query = "
        update equip_work_log2_header
        set
            str_timestamp='{$chg_time}'
        where
            mac_no={$mac_no} and siji_no={$siji_no1} and koutei={$koutei1}
    ";
    if (query_affected_trans($con, $query) <= 0) {      // 更新用クエリーの実行
        query_affected_trans($con, 'rollback');         // transaction rollback
        $_SESSION['s_sysmsg'] .= "$query";
    }
    ///// 次にヘッダーの前のロットの完了日時を変更する
    if (isset($_SESSION['chg_time_end'])) {
    } else {
    $query = "
        update equip_work_log2_header
        set
            end_timestamp='{$chg_time}'
        where
            mac_no={$mac_no} and siji_no={$siji_no2} and koutei={$koutei2}
    ";
    if (query_affected_trans($con, $query) <= 0) {      // 更新用クエリーの実行
        query_affected_trans($con, 'rollback');         // transaction rollback
        $_SESSION['s_sysmsg'] .= "$query";
    }
    }
    ///// 次にログの変更 (指定日時以上の物を現在のロットにする)
    $query = "
        update equip_work_log2
        set
            siji_no={$siji_no1}, koutei={$koutei1}
        where
            equip_index(mac_no, siji_no, koutei, date_time) >= '{$mac_no}{$siji_no2}{$koutei2}{$chg_time_key}'
        and
            equip_index(mac_no, siji_no, koutei, date_time) < '{$mac_no}{$siji_no2}{$koutei2}99999999999999'
            -- date_time>=CAST('{$chg_time}' as TIMESTAMP) and mac_no={$mac_no}
            -- and siji_no={$siji_no2} and koutei={$koutei2}
    ";
    if (query_affected_trans($con, $query) < 0) {       // 更新用クエリーの実行(対象なしもある)
        query_affected_trans($con, 'rollback');         // transaction rollback
        $_SESSION['s_sysmsg'] .= "$query";
    }
    ///// 次にログの変更 (指定日時未満の物を前のロットにする)
    $query = "
        update equip_work_log2
        set
            siji_no={$siji_no2}, koutei={$koutei2}
        where
            equip_index(mac_no, siji_no, koutei, date_time) < '{$mac_no}{$siji_no1}{$koutei1}{$chg_time_key}'
        and
            equip_index(mac_no, siji_no, koutei, date_time) > '{$mac_no}{$siji_no1}{$koutei1}00000000000000'
            -- date_time < CAST('{$chg_time}' as TIMESTAMP) and mac_no={$mac_no}
            -- and siji_no={$siji_no1} and koutei={$koutei1}
    ";
    if (query_affected_trans($con, $query) < 0) {       // 更新用クエリーの実行(対象なしもある)
        query_affected_trans($con, 'rollback');         // transaction rollback
        $_SESSION['s_sysmsg'] .= "$query";
    }
    /////////// commit トランザクション終了
    query_affected_trans($con, 'commit');
    
    ///// 一時セッション変数を削除
    unset($_SESSION['chg_time']);
    ///// リフレッシュさせるために親フレームへ飛ばす
    header('Location: ' . H_WEB_HOST . EQUIP2 . 'work_mnt/equip_edit_chart.php');
}
//////////// スタート日時の変更指示が来たら一度セッションに保存する
if (isset($_GET['chg_time'])) {
    $_SESSION['chg_time'] = $_GET['chg_time'];
}

//////////// 加工数のリセット指示が来た(セッションから)
if (isset($_SESSION['cnt_chg_time'])) {
    $cnt_chg_time = $_SESSION['cnt_chg_time'];
    
    /////////// begin トランザクション開始
    if ($con = db_connect()) {
        query_affected_trans($con, 'begin');
    } else {
        $_SESSION['s_sysmsg'] = 'データベースに接続できません！';
        exit();
    }
    ///// 加工数のリセットはログの変更のみ
        // まずは指定された時間より前の物は０でリセットする
    $query = "
        UPDATE
            equip_work_log2
        SET
            work_cnt=0
        WHERE
            equip_index(mac_no, siji_no, koutei, date_time) < '{$mac_no}{$siji_no1}{$koutei1}{$cnt_chg_time}'
            and
            equip_index(mac_no, siji_no, koutei, date_time) > '{$mac_no}{$siji_no1}{$koutei1}00000000000000'
            -- date_time < CAST('{$cnt_chg_time}' as TIMESTAMP) and mac_no={$mac_no} and siji_no={$siji_no1} and koutei={$koutei1}
    ";
    if (query_affected_trans($con, $query) < 0) {       // 更新用クエリーの実行(更新数が0の場合もあるので注意)
        query_affected_trans($con, 'rollback');         // transaction rollback
        $_SESSION['s_sysmsg'] .= $query;
    }
        // 次は指定された時間より後の物は１からスタートしてインクリメントする
        // 2007/06/29 1→カウントマスターによるに変更
    // 部品番号を取得
    $query = "SELECT parts_no FROM equip_work_log2_header WHERE mac_no={$mac_no} AND siji_no={$siji_no1} AND koutei={$koutei1}";
    getUniResult($query, $parts_no);
    $cntMulti = getCounterMaster($mac_no, $parts_no);
    $work_cnt = 0;
    $recNo    = 0;
    while (1) {
        ///// 初回の1個目
        if ($recNo == 0) {
            $query = "
                select  to_char(date_time AT TIME ZONE 'JST', 'YYYYMMDDHH24MISS')
                      -- date_time
                    , mac_state
                from 
                    equip_work_log2
                where
                    equip_index(mac_no, siji_no, koutei, date_time) >= '{$mac_no}{$siji_no1}{$koutei1}{$cnt_chg_time}'
                    and
                    equip_index(mac_no, siji_no, koutei, date_time) < '{$mac_no}{$siji_no1}{$koutei1}99999999999999'
                    -- date_time>=CAST('{$cnt_chg_time}' as TIMESTAMP) and mac_no={$mac_no} and siji_no={$siji_no1} and koutei={$koutei1}
                order by
                    equip_index(mac_no, siji_no, koutei, date_time) ASC
                    -- date_time ASC
                limit 1 offset 0;
            ";
        } else {
            ///// 2個目以降
            $query = "
                select  to_char(date_time AT TIME ZONE 'JST', 'YYYYMMDDHH24MISS')
                      -- date_time
                    , mac_state
                from 
                    equip_work_log2
                where
                    equip_index(mac_no, siji_no, koutei, date_time) > '{$mac_no}{$siji_no1}{$koutei1}{$search_time}'
                    and
                    equip_index(mac_no, siji_no, koutei, date_time) < '{$mac_no}{$siji_no1}{$koutei1}99999999999999'
                    -- date_time > CAST('{$search_time}' as TIMESTAMP) and mac_no={$mac_no} and siji_no={$siji_no1} and koutei={$koutei1}
                order by
                    equip_index(mac_no, siji_no, koutei, date_time) ASC
                    -- date_time ASC
                limit 1 offset 0;
            ";
        }
        $recNo++;
        if ( ($rows=getResultTrs($con, $query, $search_res)) < 0) { // トランザクション内での 照会専用クエリー
            query_affected_trans($con, 'rollback');         // transaction rollback
            $_SESSION['s_sysmsg'] .= $query;
            break;              // エラー終了
        } elseif ($rows == 0) {
            break;              // 処理終了
        } else {
            $search_time = $search_res[0][0];
            $mac_state   = $search_res[0][1];
        }
        if ( ($mac_state == 1) || ($mac_state == 8) || ($mac_state == 5) ) { // 自動と無人と段取
            // $work_cnt++;
            $work_cnt += $cntMulti;
        }
        $query = "
            update equip_work_log2
            set
                work_cnt={$work_cnt}
            where
                equip_index(mac_no, siji_no, koutei, date_time) = '{$mac_no}{$siji_no1}{$koutei1}{$search_time}'
                -- date_time=CAST('{$search_time}' as TIMESTAMP) and mac_no={$mac_no} and siji_no={$siji_no1} and koutei={$koutei1}
        ";
        if ( ($up_rows=query_affected_trans($con, $query)) < 0) {       // 更新用クエリーの実行(更新数が0の場合もあるので注意)
            query_affected_trans($con, 'rollback');         // transaction rollback
            $_SESSION['s_sysmsg'] .= $query;
            break;
        } elseif ($up_rows >= 2) {
            query_affected_trans($con, 'rollback');         // transaction rollback
            $_SESSION['s_sysmsg'] .= $query;
            echo "$query <br>\n";
            break;              // エラー終了
        }
    }
    /////////// commit トランザクション終了
    query_affected_trans($con, 'commit');
    
    ///// 一時セッション変数を削除
    unset($_SESSION['cnt_chg_time']);
}
//////////// 加工数のリセット指示が来たら一度セッションに保存する
if (isset($_GET['cnt_chg_time'])) {
    $_SESSION['cnt_chg_time'] = $_GET['cnt_chg_time'];
}

////////// リスト指示が来た
if ($select == 'GO') {
    //////////// ヘッダーより現在加工しているロットの開始日時の取得
    $query = "
        select to_char(str_timestamp AT TIME ZONE 'JST', 'YYYY-MM-DD HH24:MI:SS') as str_timestamp
            , to_char(end_timestamp AT TIME ZONE 'JST', 'YYYY-MM-DD HH24:MI:SS') as end_timestamp
            , parts_no
        from
            equip_work_log2_header
        where
            mac_no={$mac_no}
        and
            siji_no={$siji_no1}
        and
            koutei={$koutei1}
    ";
    $res = array();
    if ( getResult($query, $res) <= 0) {
        $_SESSION['s_sysmsg'] = "{$mac_no}はヘッダーに実績データがありません！";
    } else {
        $str_timestamp1 = $res[0]['str_timestamp'];
        // $end_timestamp1 = $res[0]['end_timestamp'];
        $parts_no = $res[0]['parts_no'];
        $cntMulti = getCounterMaster($mac_no, $parts_no);
    }
    
    //////////// ヘッダーより前のロットの開始日時と終了日時の取得
    $query = "
        select to_char(str_timestamp AT TIME ZONE 'JST', 'YYYY-MM-DD HH24:MI:SS') as str_timestamp
            , to_char(end_timestamp AT TIME ZONE 'JST', 'YYYY-MM-DD HH24:MI:SS') as end_timestamp
        from
            equip_work_log2_header
        where
            mac_no={$mac_no} and siji_no={$siji_no2} and koutei={$koutei2}
    ";
    $res = array();
    if ( getResult($query, $res) <= 0) {
        $_SESSION['s_sysmsg'] = "{$mac_no}はヘッダーに実績データがありません！";
    } else {
        $str_timestamp2 = $res[0]['str_timestamp'];
        $end_timestamp2 = $res[0]['end_timestamp'];
        $_SESSION['chg_time_end'] = NULL;
        if ($end_timestamp2 == NULL) {          // 前のロットが中断中の場合はNULL
            //中断の場合も変更できるようにし、終了時間を変更しない
            $end_timestamp2 = $str_timestamp1;  // 現在加工中のロットの開始日時で置換える
            //$chg_time_stop = 'on';              // 開始日時の変更不可
            $chg_time_end  = 'on';    // 中断計画終了日時の変更可否
            $_SESSION['chg_time_end'] = $chg_time_end;
        }
    }
    /////// 前のロットの終了時間から20→100レコード分date_timeの日時を進ませる
    $query = "
        select date_time from equip_work_log2
        where
            date_time >= CAST('{$end_timestamp2}' as TIMESTAMP)
        and
            mac_no={$mac_no}
        order by
            date_time ASC
        limit $genLimit
    ";
    if ( ($rows=getResult2($query, $offset_time)) > 0) {    // ()に注意
        $end_timestamp2 = $offset_time[$rows-1][0];     // 上限20→100レコード内で最大値を取る
    }
    
    ////////////// 明細データの取得
    //                -- date_time >= (CURRENT_TIMESTAMP - interval '168 hours')      -- テスト用に残す(168=7日に型変換される)
    //                -- and date_time <= (CURRENT_TIMESTAMP - interval '0 hours')
    // TIMESTAMP型の場合は CAST しないと Seq Scan となるので注意  indexを使う場合は明示的に型変換が必要
    $query = "
        select to_char(date_time AT TIME ZONE 'JST', 'YYYY/MM/DD') as date
            ,to_char(date_time AT TIME ZONE 'JST', 'HH24:MI:SS') as time
            ,mac_state
            ,work_cnt
            ,siji_no
            ,to_char(date_time AT TIME ZONE 'JST', 'YYYYMMDDHH24MISS') as dateTime -- key data
        from
            equip_work_log2
        where
            date_time >= CAST('$str_timestamp2' as TIMESTAMP)
        and
            date_time <= CAST('$end_timestamp2' as TIMESTAMP)
        and
            mac_no={$mac_no}
        and
            ( (siji_no={$siji_no1} and koutei={$koutei1}) or (siji_no={$siji_no2} and koutei={$koutei2}) )
        order by
            date_time $sort
        limit
            $limit
        offset
            $offset
    ";
    $res = array();
    if ( ($rows=getResult2($query, $res)) <= 0) {
        $_SESSION['s_sysmsg'] = "<font color='yellow'>機械番号：{$mac_no}はログに実績データがありません！</font>";
    } else {
        $num = count($res[0]);
    }
}
///// カウンターマスターの取得 カウンター倍率を返す 2007/06/29 ADD
function getCounterMaster($mac_no, $parts_no='000000000')
{
    $query = "
        SELECT count FROM equip_count_master WHERE mac_no={$mac_no} AND parts_no='{$parts_no}'
    ";
    if (getUniResult($query, $count) > 0) {
        return $count;
    }
    $query = "
        SELECT count FROM equip_count_master WHERE mac_no={$mac_no} AND parts_no='000000000'
    ";
    if (getUniResult($query, $count) > 0) {
        return $count;
    } else {
        return 1;
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
<title><?= $menu->out_title() ?></title>
<?php if ($_SESSION['s_sysmsg'] != '') echo $menu->out_site_java() ?>
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
    left: 40px;
}
.msg {
    position: absolute;
    top:  100px;
    left: 350px;
}
.cur_font {
    font-size:      11pt;
    font-family:    monospace;
    color:          blue;
}
.pre_font {
    font-size:      11pt;
    font-family:    monospace;
    color:          gray;
}
a:hover {
    background-color:   blue;
    color:              white;
}
a {
    color:   blue;
}
-->
</style>
<script language='JavaScript'>
function init() {
<?php if ($select == 'OK') { ?>
    document.MainForm.submit();
<?php } ?>
}

function updateChk(now_time, chg_time) {
    return confirm(  "スタート日時を変更します。\n\n"
                    + "変更前の日時は" + now_time + "です。\n\n"
                    + "変更後の日時は" + chg_time + "です。\n\n"
                    + "宜しいですか？"
    )
}
function updateCntChk(now_cnt, chg_cnt) {
    return confirm(  "加工数をリセットします。\n\n"
                    + "変更前の加工数は " + now_cnt + " です。\n\n"
                    + "変更後の加工数は " + chg_cnt + " です。\n\n"
                    + "宜しいですか？"
    )
}
</script>
<?php if ($select == 'OK') { ?>
<form name='MainForm' action='<?= $current_script ?>#ambit' method='post'>
    <input type='hidden' name='select' value='GO'>
    <input type='hidden' name='sort' value='<?=$sort?>'>
</form>
<?php } ?>
</head>
<body onLoad='init()'>
    <center>
        <?php if ($select == 'NG') { ?>
        <table border='0' class='msg'>
            <tr>
                <td>
                    <b style='color: teal;'>前の完了データがありません！</b>
                </td>
            </tr>
        </table>
        <?php } elseif ($select == 'OK') { ?>
        <table border='0' class='msg'>
            <tr>
                <td>
                    <b style='color: blue;'>処理中です。お待ち下さい。</b>
                </td>
            </tr>
        </table>
        <?php } else { ?>
        <!-------------- 詳細データ表示のための表を作成 -------------->
        <table class='item' bgcolor='#d6d3ce'  border='1' cellspacing='0' cellpadding='2'>
           <tr><td> <!-- ダミー(デザイン用) -->
        <table width=100% align='center' bordercolordark='white' bordercolorlight='#bdaa90' bgcolor='#d6d3ce' border='1' cellspacing='1' cellpadding='1'>
            <!--
            <th nowrap width='50'>No</th>
            <th nowrap width='100'>年月日</th>
            <th nowrap width='100'>時分秒</th>
            <th nowrap width='100'>状態</th>
            <th nowrap width='80'>加工数</th>
            <th nowrap width='70'>指示No</th>
            <th nowrap width='45'>備考1</th> <th nowrap>備考2</th>
            <th nowrap>備考3</th> <th nowrap>備考4</th>
            -->
        <?php
            $aSETflg = 0;   // アンカーセット用の初回チェックフラグ
            for ($i=0; $i<$rows; $i++) {
                if ($res[$i][4] == $siji_no2) {
                    echo "<tr class='pre_font'>\n";
                } else {
                    echo "<tr class='cur_font'>\n";
                }
                if (isset($res[$i+10][4])) {
                    if ($res[$i+10][4] == $siji_no2) {
                        if ($aSETflg == 0) {
                            // ジャンプ先を境界線の10個前にする
                            echo "<td align='center' nowrap width='50' bgcolor='#d6d3ce'><a name='ambit'>", ($i+1+$offset), "</a></td>\n";
                            $aSETflg = 1;
                        } else {
                            echo "<td align='center' nowrap width='50' bgcolor='#d6d3ce'>", ($i+1+$offset), "</td>\n";
                        }
                    } else {
                        echo "<td align='center' nowrap width='50' bgcolor='#d6d3ce'>", ($i+1+$offset), "</td>\n";
                    }
                } else {
                    echo "<td align='center' nowrap width='50' bgcolor='#d6d3ce'>", ($i+1+$offset), "</td>\n";
                }
                for ($j=0; $j<$num; $j++) {
                    switch ($j) {
                    case 0:     // 年月日
                        print(" <td align='center' nowrap width='100' bgcolor='#d6d3ce'>" . $res[$i][$j] . "</td>\n");
                        break;
                    case 1:     // 時分秒
                        print(" <td align='center' nowrap width='100' bgcolor='#d6d3ce'>" . $res[$i][$j] . "</td>\n");
                        break;
                    case 2:     // 状態
                        $mac_state_txt = equip_machine_state($mac_no, $res[$i][$j], $bg_color, $txt_color);
                        print(" <td align='center' nowrap width='100' bgcolor='$bg_color'><font color='$txt_color'>" . $mac_state_txt . "</font></td>\n");
                        break;
                    case 3:     // 加工数
                        print(" <td align='right' nowrap width='80' bgcolor='#d6d3ce'>" . number_format($res[$i][$j]) . "</td>\n");
                        break;
                    case 4:     // 指示No
                        print(" <td align='center' nowrap width='70' bgcolor='#d6d3ce'>{$res[$i][$j]}</td>\n");
                        break;
                    default:
                        break;
                    }
                }
                if ($chg_time_stop == '') {
                    echo "    <td align='left' nowrap width='150'>\n";
                    echo "        <a href='{$current_script}?chg_time={$res[$i][5]}&select=OK&sort={$sort}'\n";
                    echo "            target='application' style='text-decoration:none;'\n";
                    echo "            onClick='return updateChk(\"{$str_timestamp1}\", \"{$res[$i][0]} {$res[$i][1]}\")'>\n";
                    echo "            ←ここからスタート</a>\n";
                    echo "    </td>\n";
                } else {
                    echo "    <td align='left' nowrap width='150'>中断中は開始変更不可</td>\n";
                }
                if ($res[$i][4] == $siji_no2) {
                    echo "    <td align='left' nowrap width='130'>　</td>\n";
                } else {
                    echo "    <td align='left' nowrap width='130'>\n";
                    // echo "        <a href='{$current_script}?cnt_chg_time={$res[$i][0]} {$res[$i][1]}&select=OK&sort={$sort}'\n";
                    echo "        <a href='{$current_script}?cnt_chg_time={$res[$i][5]}&select=OK&sort={$sort}'\n";
                    echo "            target='List' style='text-decoration:none;'\n";
                    echo "            onClick='return updateCntChk(\"{$res[$i][3]}\", \"{$cntMulti}\")'>\n";
                    echo "            ←加工数リセット</a>\n";
                    echo "    </td>\n";
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
</html>
<?php ob_end_flush(); // 出力バッファをgzip圧縮 END ?>
