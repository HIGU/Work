<?php
//////////////////////////////////////////////////////////////////////////////
// 月 次 処 理 (売上金額の月計データ作成)                                   //
// Copyright (C) 2002-2005 2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp //
// Changed history                                                          //
// 2002/02/07 Created  system_getuji.php                                    //
// 2002/02/22 phpの最後のタグが抜けている不具合を修正                       //
// 2002/08/08 セッション管理に変更                                          //
// 2002/12/03 サイトメニューに追加                                          //
// 2003/06/12 日程計画(assembly_schedule)より特注を区別するように変更       //
//            DB側で Uround() 会計用四捨五入機能を追加し合計を算出          //
// 2004/06/07 カプラ標準の金額算出方法の変更 完成全体からカプラ特注を減算   //
//            (以前は日程計画マスターから区分=1の標準品を抽出していた)      //
// 2005/03/04 2月分でL部品をTで売上したため(事業部='L' or 事業部='T')を追加 //
// 2007/05/01 $menu->out_alert_java(false)に対応するためs_sysmsg='\n'に変更 //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
session_start();                            // ini_set()の次に指定すること Script 最上行
require_once ('../function.php');
// require_once ('../define.php');
require_once ('../tnk_func.php');
access_log();                               // Script Name は自動取得

$sysmsg = $_SESSION['s_sysmsg'];

$_SESSION['s_sysmsg'] = NULL;
$_SESSION['system_getuji'] = date('H:i');
if($_SESSION['Auth'] <= 2){
    $_SESSION['s_sysmsg'] = 'システム管理メニューは管理者のみ使用できます。';
    header('Location: http:' . WEB_HOST . 'index1.php');
    exit();
}
if(!$_POST['yyyymm']){
    $_SESSION['s_sysmsg'] = '月次年月が入力されていません。';
    header('Location: http:' . WEB_HOST . 'system/system_getuji_select.php');
    exit();
}
$yyyymm = $_POST['yyyymm'];     

$s_date = $yyyymm . '01';
$e_date = $yyyymm . '31';

// C 特注品
$query = "select sum(Uround(数量*単価,0)) as 合計金額 from hiuuri left outer join assembly_schedule on 計画番号=plan_no where 計上日>=$s_date and 計上日<=$e_date and 事業部='C' and note15 like 'SC%'";
if (getUniResult($query, $sp_kin) <= 0) {
    $_SESSION['s_sysmsg'] = "年月:$yyyymm カプラ特注のデータがありません!";
    exit();
} else {
    $_SESSION['s_sysmsg'] = "<font color='white'>年月：$yyyymm<br>C特注金額：　" . number_format($sp_kin) . '<br>\n';
}
/******************** 旧SQL文
$query = "select 数量,単価 from hiuuri h, mipmst m where h.assyno=m.seihin and h.計上日>=$s_date and h.計上日<=$e_date and h.事業部='C' and m.kubun='3' order by h.計上日 asc";
$res = array();
if($rows = getResult($query,$res)){
    for($r=0;$r<$rows;$r++){                // 指定月の合計金額を算出
        $sp_kin += corrc_round($res[$r][0]*$res[$r][1]);
    }
}
**********************/

// C 標準品
$query = "select sum(Uround(数量*単価,0)) as 合計金額 from hiuuri where 計上日>=$s_date and 計上日<=$e_date and 事業部='C' and datatype='1'";   // 完成 売上のみ
// $query = "select sum(Uround(数量*単価,0)) as 合計金額 from hiuuri left outer join assembly_schedule on 計画番号=plan_no where 計上日>=$s_date and 計上日<=$e_date and 事業部='C' and sei_kubun='1'";
if (getUniResult($query, $c_sei_kin) <= 0) {
    $_SESSION['s_sysmsg'] = "年月:$yyyymm カプラ標準のデータがありません!";
    exit();
} else {
    $std_kin = ($c_sei_kin - $sp_kin);      // カラプ標準＝カプラ製品全体−カプラ特注製品
    $_SESSION['s_sysmsg'] .= "<font color='white'>年月：$yyyymm<br>C標準金額：　" . number_format($std_kin) . '</font><br>\n';
}
/******************** 旧SQL文
$query = "select 数量,単価 from hiuuri h, mipmst m where h.assyno=m.seihin and h.計上日>=$s_date and h.計上日<=$e_date and h.事業部='C' and m.kubun='1' order by h.計上日 asc";
$res = array();
if($rows = getResult($query,$res)){
    for($r=0;$r<$rows;$r++){                // 指定月の合計金額を算出
        $std_kin += corrc_round($res[$r][0]*$res[$r][1]);
    }
}
**********************/

// カプラの合計金額を計算
$query = "select sum(Uround(数量*単価,0)) from hiuuri where 計上日>=$s_date and 計上日<=$e_date and 事業部='C'";
if (getUniResult($query, $c_all_kin) <= 0) {
    $_SESSION['s_sysmsg'] = "年月:$yyyymm カプラ合計のデータがありません!";
    exit();
} else {
    $_SESSION['s_sysmsg'] .= "<font color='white'>年月：$yyyymm<br>C合計金額：　" . number_format($c_all_kin) . '</font><br>\n';
}
/******************** 旧SQL文
$query = "select 数量,単価 from hiuuri where 計上日>=$s_date and 計上日<=$e_date and 事業部='C' order by 計上日 asc";
$res = array();
if($rows = getResult($query,$res)){
    for($r=0;$r<$rows;$r++){                // 指定月の合計金額を算出
        $c_all_kin += corrc_round($res[$r][0]*$res[$r][1]);
    }
}
**********************/

// リニアの合計金額を計算
$query = "select sum(Uround(数量*単価,0)) from hiuuri where 計上日>=$s_date and 計上日<=$e_date and (事業部='L' or 事業部='T')";
if (getUniResult($query, $l_all_kin) <= 0) {
    $_SESSION['s_sysmsg'] = "年月:$yyyymm リニア合計のデータがありません!";
    exit();
} else {
    $_SESSION['s_sysmsg'] .= "<font color='white'>年月：$yyyymm<br>L合計金額：　" . number_format($l_all_kin) . '</font><br>\n';
}
/******************** 旧SQL文
$query = "select 数量,単価 from hiuuri where 計上日>=$s_date and 計上日<=$e_date and 事業部='L' order by 計上日 asc";
$res = array();
if($rows = getResult($query,$res)){
    for($r=0;$r<$rows;$r++){                // 指定月の合計金額を算出
        $l_all_kin += corrc_round($res[$r][0]*$res[$r][1]);
    }
}
**********************/

// カプラの製品金額を計算
$query = "select sum(Uround(数量*単価,0)) from hiuuri where 計上日>=$s_date and 計上日<=$e_date and 事業部='C' and datatype='1'";
if (getUniResult($query, $c_sei_kin) <= 0) {
    $_SESSION['s_sysmsg'] = "年月:$yyyymm カプラ製品のデータがありません!";
    exit();
} else {
    $_SESSION['s_sysmsg'] .= "<font color='white'>年月：$yyyymm<br>C製品金額：　" . number_format($c_sei_kin) . '</font><br>\n';
}
/******************** 旧SQL文
$query = "select 数量,単価 from hiuuri where 計上日>=$s_date and 計上日<=$e_date and 事業部='C' and datatype='1' order by 計上日 asc";
$res = array();
if($rows = getResult($query,$res)){
    for($r=0;$r<$rows;$r++){                // 指定月の合計金額を算出
        $c_sei_kin += corrc_round($res[$r][0]*$res[$r][1]);
    }
}
**********************/

// リニアの製品金額を計算
$query = "select sum(Uround(数量*単価,0)) from hiuuri where 計上日>=$s_date and 計上日<=$e_date and (事業部='L' or 事業部='T') and datatype='1'";
if (getUniResult($query, $l_sei_kin) <= 0) {
    $_SESSION['s_sysmsg'] = "年月:$yyyymm リニア製品のデータがありません!";
    exit();
} else {
    $_SESSION['s_sysmsg'] .= "<font color='white'>年月：$yyyymm<br>L製品金額：　" . number_format($l_sei_kin) . '</font><br>\n';
}
/******************** 旧SQL文
$query = "select 数量,単価 from hiuuri where 計上日>=$s_date and 計上日<=$e_date and 事業部='L' and datatype='1' order by 計上日 asc";
$res = array();
if($rows = getResult($query,$res)){
    for($r=0;$r<$rows;$r++){                // 指定月の合計金額を算出
        $l_sei_kin += corrc_round($res[$r][0]*$res[$r][1]);
    }
}
**********************/


$all_kin = $c_all_kin + $l_all_kin;
$query = "select 年月 from wrk_uriage where 年月='$yyyymm'";
$res = array();
if($rows = getResult($query,$res)){
    $update_qry  = "update wrk_uriage set c特注=$sp_kin, c標準=$std_kin, カプラ=$c_all_kin, リニア=$l_all_kin, 全体=$all_kin, c製品=$c_sei_kin, l製品=$l_sei_kin where 年月=$yyyymm";
    if(funcConnect()){
        execQuery('begin');
        if(execQuery($update_qry)>=0){
            execQuery('commit');
            disConnectDB();
            $_SESSION['s_sysmsg'] .= "<font color='white'>売上ワークの UPDATE に成功しました。</font>";
            header('Location: http:' . WEB_HOST . 'system/system_menu.php');
            exit();
        }else{
            execQuery('rollback');
            disConnectDB();
            $_SESSION['s_sysmsg'] .= '売上ワークのUPDATEに失敗しました。データベースロジックを調べて下さい。';
            header('Location: http:' . WEB_HOST . 'system/system_menu.php');
            exit();
        }
    }
}else{
    $insert_qry = "insert into wrk_uriage (年月,c特注,c標準,カプラ,リニア,全体,c製品,l製品) values ($yyyymm,$sp_kin,$std_kin,$c_all_kin,$l_all_kin,$all_kin,$c_sei_kin,$l_sei_kin)";
    if(funcConnect()){
        execQuery('begin');
        if(execQuery($insert_qry)>=0){
            execQuery('commit');
            disConnectDB();
            $_SESSION['s_sysmsg'] .= "<font color='white'>年月:$yyyymm の新規追加成功</font>";
            header('Location: http:' . WEB_HOST . 'system/system_menu.php');
            exit();
        }else{
            execQuery('rollback');
            disConnectDB();
            $_SESSION['s_sysmsg'] .= '売上ワークのINSERTに失敗しました。データベースロジックを調べて下さい。';
            header('Location: http:' . WEB_HOST . 'system/system_menu.php');
            exit();
        }
    }
}
?>
