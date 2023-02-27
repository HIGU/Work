<?php
//////////////////////////////////////////////////////////////////////////////
// ������ư���������ƥ�� �ؼ��ѹ��ڤӥ��Խ�  �ꥹ���������              //
// Copyright (C) 2004-2021 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2004/07/28 Created  equip_edit_chartHader.php                            //
// 2004/08/02 ���Υ�åȤ���Ф�(siji_no={$siji_no1} or siji_no={$siji_no2})//
// 2004/08/08 �ե졼���Ǥ�������application��_parent���ѹ�(FRAME̵���б�) //
//            work_log2��UPDATE��equip_index()�ؿ�����Ѥ���褦��SQLʸ�ѹ� //
// 2005/03/03 cnt_chg_time�Ǥ�equip_index()�ؿ�����Ѥ���褦���ѹ�         //
//            ��դ��ʤ���Фʤ�ʤ��ΤϽ����Ľ��date_time�η������㤦���� //
// 2007/06/29 �����󥿡��ޥ������б�                                        //
// 2007/09/18 E_ALL | E_STRICT ���ѹ�                                       //
// 2011/06/23 Ʊ�����֤ˣ��ĥǡ��������äƤ��ơ������ǥ��顼��ȯ������      //
//            �ǡ������������б�                                     ��ë //
// 2021/11/17 ���Ƿײ�ξ���ؼ��ѹ��Ǥ���褦���ѹ�                 ��ë //
//            ���ηײ褬������ξ�硢�ѹ��������ηײ�δ�λ���֤��ѹ����ʤ�//
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL | E_STRICT);
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
ini_set('max_execution_time', 120);         // ����¹Ի���=120�� SAPI��
ob_start('ob_gzhandler');                   // ���ϥХåե���gzip����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��
require_once ('../equip_function.php');     // ������˥塼 ���� function (function.php��ޤ�)
require_once ('../../MenuHeader.php');      // TNK ������ menu class
access_log();                               // Script Name �ϼ�ư����

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader();                   // ǧ�ڥ����å�0=���̰ʾ� �����=���å������ �����ȥ�̤����

////////////// ����������
$menu->set_site(40, 11);                    // site_index=40(������˥塼) site_id=11(�ؼ��ѹ�)
////////////// target����
// $menu->set_target('application');           // �ե졼���Ǥ�������target°����ɬ��
$menu->set_target('_parent');               // �ե졼���Ǥ�������target°����ɬ��

///////// HTML���Ǽ�ʬ���Ȥ�¿�Ѥ��뤿�ᡢ�������ѿ��س�Ǽ
$current_script = $_SERVER['PHP_SELF'];

$offset = 0;        // �ƥ�����
$limit  = 1000;     // �ƥ�����
$genLimit = 100;    // ���߲ù����ɽ���쥳���ɿ�
$chg_time_stop = '';    // �����������ѹ�����
$chg_time_end  = '';    // ���Ƿײ轪λ�������ѹ�����

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
////////// �ꥹ�Ȼؼ����褿
if ($select != 'NG') {      // NN7.1- �к� (frame���ɹ�����֤�����)
    $siji_no1 = $_SESSION['siji_no1'];
    $koutei1  = $_SESSION['koutei1'];
    $siji_no2 = $_SESSION['siji_no2'];
    $koutei2  = $_SESSION['koutei2'];
}

//////////// ���������������ѹ��ؼ����褿
if (isset($_SESSION['chg_time'])) {
    $chg_time = $_SESSION['chg_time'];
    $chg_time_key = $chg_time;
    $chg_time = substr($chg_time, 0, 8). ' ' . substr($chg_time, 8, 6);
    
    /////////// begin �ȥ�󥶥�����󳫻�
    if ($con = db_connect()) {
        query_affected_trans($con, 'begin');
    } else {
        $_SESSION['s_sysmsg'] = '�ǡ����١�������³�Ǥ��ޤ���';
        exit();
    }
    ///// �ޤ��إå����θ��߲ù���Υ��������������ѹ�
    $query = "
        update equip_work_log2_header
        set
            str_timestamp='{$chg_time}'
        where
            mac_no={$mac_no} and siji_no={$siji_no1} and koutei={$koutei1}
    ";
    if (query_affected_trans($con, $query) <= 0) {      // �����ѥ����꡼�μ¹�
        query_affected_trans($con, 'rollback');         // transaction rollback
        $_SESSION['s_sysmsg'] .= "$query";
    }
    ///// ���˥إå��������Υ�åȤδ�λ�������ѹ�����
    if (isset($_SESSION['chg_time_end'])) {
    } else {
    $query = "
        update equip_work_log2_header
        set
            end_timestamp='{$chg_time}'
        where
            mac_no={$mac_no} and siji_no={$siji_no2} and koutei={$koutei2}
    ";
    if (query_affected_trans($con, $query) <= 0) {      // �����ѥ����꡼�μ¹�
        query_affected_trans($con, 'rollback');         // transaction rollback
        $_SESSION['s_sysmsg'] .= "$query";
    }
    }
    ///// ���˥����ѹ� (���������ʾ��ʪ�򸽺ߤΥ�åȤˤ���)
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
    if (query_affected_trans($con, $query) < 0) {       // �����ѥ����꡼�μ¹�(�оݤʤ��⤢��)
        query_affected_trans($con, 'rollback');         // transaction rollback
        $_SESSION['s_sysmsg'] .= "$query";
    }
    ///// ���˥����ѹ� (��������̤����ʪ�����Υ�åȤˤ���)
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
    if (query_affected_trans($con, $query) < 0) {       // �����ѥ����꡼�μ¹�(�оݤʤ��⤢��)
        query_affected_trans($con, 'rollback');         // transaction rollback
        $_SESSION['s_sysmsg'] .= "$query";
    }
    /////////// commit �ȥ�󥶥������λ
    query_affected_trans($con, 'commit');
    
    ///// ������å�����ѿ�����
    unset($_SESSION['chg_time']);
    ///// ��ե�å��夵���뤿��˿ƥե졼������Ф�
    header('Location: ' . H_WEB_HOST . EQUIP2 . 'work_mnt/equip_edit_chart.php');
}
//////////// ���������������ѹ��ؼ����褿����٥��å�������¸����
if (isset($_GET['chg_time'])) {
    $_SESSION['chg_time'] = $_GET['chg_time'];
}

//////////// �ù����Υꥻ�åȻؼ����褿(���å���󤫤�)
if (isset($_SESSION['cnt_chg_time'])) {
    $cnt_chg_time = $_SESSION['cnt_chg_time'];
    
    /////////// begin �ȥ�󥶥�����󳫻�
    if ($con = db_connect()) {
        query_affected_trans($con, 'begin');
    } else {
        $_SESSION['s_sysmsg'] = '�ǡ����١�������³�Ǥ��ޤ���';
        exit();
    }
    ///// �ù����Υꥻ�åȤϥ����ѹ��Τ�
        // �ޤ��ϻ��ꤵ�줿���֤������ʪ�ϣ��ǥꥻ�åȤ���
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
    if (query_affected_trans($con, $query) < 0) {       // �����ѥ����꡼�μ¹�(��������0�ξ��⤢��Τ����)
        query_affected_trans($con, 'rollback');         // transaction rollback
        $_SESSION['s_sysmsg'] .= $query;
    }
        // ���ϻ��ꤵ�줿���֤����ʪ�ϣ����饹�����Ȥ��ƥ��󥯥���Ȥ���
        // 2007/06/29 1��������ȥޥ������ˤ����ѹ�
    // �����ֹ�����
    $query = "SELECT parts_no FROM equip_work_log2_header WHERE mac_no={$mac_no} AND siji_no={$siji_no1} AND koutei={$koutei1}";
    getUniResult($query, $parts_no);
    $cntMulti = getCounterMaster($mac_no, $parts_no);
    $work_cnt = 0;
    $recNo    = 0;
    while (1) {
        ///// ����1����
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
            ///// 2���ܰʹ�
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
        if ( ($rows=getResultTrs($con, $query, $search_res)) < 0) { // �ȥ�󥶥��������Ǥ� �Ȳ����ѥ����꡼
            query_affected_trans($con, 'rollback');         // transaction rollback
            $_SESSION['s_sysmsg'] .= $query;
            break;              // ���顼��λ
        } elseif ($rows == 0) {
            break;              // ������λ
        } else {
            $search_time = $search_res[0][0];
            $mac_state   = $search_res[0][1];
        }
        if ( ($mac_state == 1) || ($mac_state == 8) || ($mac_state == 5) ) { // ��ư��̵�ͤ��ʼ�
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
        if ( ($up_rows=query_affected_trans($con, $query)) < 0) {       // �����ѥ����꡼�μ¹�(��������0�ξ��⤢��Τ����)
            query_affected_trans($con, 'rollback');         // transaction rollback
            $_SESSION['s_sysmsg'] .= $query;
            break;
        } elseif ($up_rows >= 2) {
            query_affected_trans($con, 'rollback');         // transaction rollback
            $_SESSION['s_sysmsg'] .= $query;
            echo "$query <br>\n";
            break;              // ���顼��λ
        }
    }
    /////////// commit �ȥ�󥶥������λ
    query_affected_trans($con, 'commit');
    
    ///// ������å�����ѿ�����
    unset($_SESSION['cnt_chg_time']);
}
//////////// �ù����Υꥻ�åȻؼ����褿����٥��å�������¸����
if (isset($_GET['cnt_chg_time'])) {
    $_SESSION['cnt_chg_time'] = $_GET['cnt_chg_time'];
}

////////// �ꥹ�Ȼؼ����褿
if ($select == 'GO') {
    //////////// �إå�����긽�߲ù����Ƥ����åȤγ��������μ���
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
        $_SESSION['s_sysmsg'] = "{$mac_no}�ϥإå����˼��ӥǡ���������ޤ���";
    } else {
        $str_timestamp1 = $res[0]['str_timestamp'];
        // $end_timestamp1 = $res[0]['end_timestamp'];
        $parts_no = $res[0]['parts_no'];
        $cntMulti = getCounterMaster($mac_no, $parts_no);
    }
    
    //////////// �إå���������Υ�åȤγ��������Ƚ�λ�����μ���
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
        $_SESSION['s_sysmsg'] = "{$mac_no}�ϥإå����˼��ӥǡ���������ޤ���";
    } else {
        $str_timestamp2 = $res[0]['str_timestamp'];
        $end_timestamp2 = $res[0]['end_timestamp'];
        $_SESSION['chg_time_end'] = NULL;
        if ($end_timestamp2 == NULL) {          // ���Υ�åȤ�������ξ���NULL
            //���Ǥξ����ѹ��Ǥ���褦�ˤ�����λ���֤��ѹ����ʤ�
            $end_timestamp2 = $str_timestamp1;  // ���߲ù���Υ�åȤγ����������ִ�����
            //$chg_time_stop = 'on';              // �����������ѹ��Բ�
            $chg_time_end  = 'on';    // ���Ƿײ轪λ�������ѹ�����
            $_SESSION['chg_time_end'] = $chg_time_end;
        }
    }
    /////// ���Υ�åȤν�λ���֤���20��100�쥳����ʬdate_time��������ʤޤ���
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
    if ( ($rows=getResult2($query, $offset_time)) > 0) {    // ()�����
        $end_timestamp2 = $offset_time[$rows-1][0];     // ���20��100�쥳������Ǻ����ͤ���
    }
    
    ////////////// ���٥ǡ����μ���
    //                -- date_time >= (CURRENT_TIMESTAMP - interval '168 hours')      -- �ƥ����Ѥ˻Ĥ�(168=7���˷��Ѵ������)
    //                -- and date_time <= (CURRENT_TIMESTAMP - interval '0 hours')
    // TIMESTAMP���ξ��� CAST ���ʤ��� Seq Scan �Ȥʤ�Τ����  index��Ȥ���������Ū�˷��Ѵ���ɬ��
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
        $_SESSION['s_sysmsg'] = "<font color='yellow'>�����ֹ桧{$mac_no}�ϥ��˼��ӥǡ���������ޤ���</font>";
    } else {
        $num = count($res[0]);
    }
}
///// �����󥿡��ޥ������μ��� �����󥿡���Ψ���֤� 2007/06/29 ADD
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

/////////// HTML Header ����Ϥ��ƥ���å��������
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
    return confirm(  "���������������ѹ����ޤ���\n\n"
                    + "�ѹ�����������" + now_time + "�Ǥ���\n\n"
                    + "�ѹ����������" + chg_time + "�Ǥ���\n\n"
                    + "�������Ǥ�����"
    )
}
function updateCntChk(now_cnt, chg_cnt) {
    return confirm(  "�ù�����ꥻ�åȤ��ޤ���\n\n"
                    + "�ѹ����βù����� " + now_cnt + " �Ǥ���\n\n"
                    + "�ѹ���βù����� " + chg_cnt + " �Ǥ���\n\n"
                    + "�������Ǥ�����"
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
                    <b style='color: teal;'>���δ�λ�ǡ���������ޤ���</b>
                </td>
            </tr>
        </table>
        <?php } elseif ($select == 'OK') { ?>
        <table border='0' class='msg'>
            <tr>
                <td>
                    <b style='color: blue;'>������Ǥ������Ԥ���������</b>
                </td>
            </tr>
        </table>
        <?php } else { ?>
        <!-------------- �ܺ٥ǡ���ɽ���Τ����ɽ����� -------------->
        <table class='item' bgcolor='#d6d3ce'  border='1' cellspacing='0' cellpadding='2'>
           <tr><td> <!-- ���ߡ�(�ǥ�������) -->
        <table width=100% align='center' bordercolordark='white' bordercolorlight='#bdaa90' bgcolor='#d6d3ce' border='1' cellspacing='1' cellpadding='1'>
            <!--
            <th nowrap width='50'>No</th>
            <th nowrap width='100'>ǯ����</th>
            <th nowrap width='100'>��ʬ��</th>
            <th nowrap width='100'>����</th>
            <th nowrap width='80'>�ù���</th>
            <th nowrap width='70'>�ؼ�No</th>
            <th nowrap width='45'>����1</th> <th nowrap>����2</th>
            <th nowrap>����3</th> <th nowrap>����4</th>
            -->
        <?php
            $aSETflg = 0;   // ���󥫡����å��Ѥν������å��ե饰
            for ($i=0; $i<$rows; $i++) {
                if ($res[$i][4] == $siji_no2) {
                    echo "<tr class='pre_font'>\n";
                } else {
                    echo "<tr class='cur_font'>\n";
                }
                if (isset($res[$i+10][4])) {
                    if ($res[$i+10][4] == $siji_no2) {
                        if ($aSETflg == 0) {
                            // ��������򶭳�����10�����ˤ���
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
                    case 0:     // ǯ����
                        print(" <td align='center' nowrap width='100' bgcolor='#d6d3ce'>" . $res[$i][$j] . "</td>\n");
                        break;
                    case 1:     // ��ʬ��
                        print(" <td align='center' nowrap width='100' bgcolor='#d6d3ce'>" . $res[$i][$j] . "</td>\n");
                        break;
                    case 2:     // ����
                        $mac_state_txt = equip_machine_state($mac_no, $res[$i][$j], $bg_color, $txt_color);
                        print(" <td align='center' nowrap width='100' bgcolor='$bg_color'><font color='$txt_color'>" . $mac_state_txt . "</font></td>\n");
                        break;
                    case 3:     // �ù���
                        print(" <td align='right' nowrap width='80' bgcolor='#d6d3ce'>" . number_format($res[$i][$j]) . "</td>\n");
                        break;
                    case 4:     // �ؼ�No
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
                    echo "            ���������饹������</a>\n";
                    echo "    </td>\n";
                } else {
                    echo "    <td align='left' nowrap width='150'>������ϳ����ѹ��Բ�</td>\n";
                }
                if ($res[$i][4] == $siji_no2) {
                    echo "    <td align='left' nowrap width='130'>��</td>\n";
                } else {
                    echo "    <td align='left' nowrap width='130'>\n";
                    // echo "        <a href='{$current_script}?cnt_chg_time={$res[$i][0]} {$res[$i][1]}&select=OK&sort={$sort}'\n";
                    echo "        <a href='{$current_script}?cnt_chg_time={$res[$i][5]}&select=OK&sort={$sort}'\n";
                    echo "            target='List' style='text-decoration:none;'\n";
                    echo "            onClick='return updateCntChk(\"{$res[$i][3]}\", \"{$cntMulti}\")'>\n";
                    echo "            ���ù����ꥻ�å�</a>\n";
                    echo "    </td>\n";
                }
                echo "</tr>\n";
            }
        ?>
        </table> <!----- ���ߡ� End ----->
            </td></tr>
        </table>
        <?php } ?>
    </center>
</body>
</html>
<?php ob_end_flush(); // ���ϥХåե���gzip���� END ?>
