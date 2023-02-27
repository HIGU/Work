<?php 
//////////////////////////////////////////////////////////////////////////////
// ��Ω������Ư���������ƥ�ε�����ž���� ��������ӥ��ͥ����å�          //
// Copyright (C) 2021-2021 norihisa_ooya@nitto-kohki.co.jp                  //
// Original by yamagishi@matehan.co.jp                                      //
// Changed history                                                          //
// 2021/03/26 Created  MakeReport.php                                       //
// 2021/10/25 ��Ω��ư���Τ�̤��Ưʬ������Ϻ������ʤ��褦���ѹ���          //
//            ����ʬ�ǤϤʤ������ײ�δ�λʬ�ޤ�������������褦�ˤ������//
//            ����ʬ��ƺ����Ǥ��ʤ��١������ᤷ����                        //
//            BUSINESS_DAY_CHANGE_TIME��BUSINESS_DAY_CHANGE_TIME_KUMI�ˤ�   //
//            define��0000������                                            //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);
ini_set('max_execution_time', 2400);    // ����¹Ի���=40ʬ CLI CGI��

require_once ('../com/define.php');
require_once ('../com/function.php');
require_once ('../com/mu_date.php');
// require_once ('../../../function.php');
// access_log();                       // Script Name �ϼ�ư����

// ----------------------------------------------------- //
// -- �ƥ�����  ��꡼�����ˤϺ��                    -- //
// ----------------------------------------------------- //
/*
$con = getConnection();
pg_query ($con , "delete from equip_work_report where work_date=20040715");
pg_query ($con , "delete from equip_work_report_log where work_date=20040715");
MakeMachineReportOneDay(1361,1,20040715);
*/

// ----------------------------------------------------- //

// --------------------------------------------------
// ������ž��������ᥤ�����
// --------------------------------------------------
function MakeReport()
{
    //return;
    global $con;
    
    // �ȥ�󥶥�����󳫻�
    pg_query ($con , 'BEGIN');
    
    // �����ޥ�������
    if (isset($_SESSION['factory'])) {
        $factory = $_SESSION['factory'];
    } else {
        $factory = '';
    }
    
    // ��¾
    $lock = fopen (DOCUMENT_ROOT.BUSINESS_PATH . ".LOCK{$factory}", 'w');
    flock ($lock,LOCK_EX);
    
    if ($factory != '') {
        $sql = "select   mac_no
                        ,csv_flg
                    from
                        equip_machine_master2
                    where
                        survey='Y'
                        and
                        mac_no!=9999
                        and
                        factory='{$factory}'
                    order by
                        mac_no
        ";
    } else {
        $sql = "select   mac_no
                        ,csv_flg
                    from
                        equip_machine_master2
                    where
                        survey='Y'
                        and
                        mac_no!=9999
                    order by
                        mac_no
        ";
    }
    $rs  = pg_query ($con , $sql);
    
    // �����ޥ����ο�����������������
    while ($row = pg_fetch_array ($rs)) {
        MakeMachineReport($row['mac_no'],$row['csv_flg']);
    }
    pg_query ($con , 'COMMIT');
    
    // ��¾���
    flock ($lock,LOCK_UN);

}
// --------------------------------------------------
// ���������������
// --------------------------------------------------
function MakeMachineReport($MacNo,$CsvFlg)
{
    global $con,$Report;
    // ����������ϰϼ���
    $StartDate = getMakeReportStartDate($MacNo);
    $EndDate   = getMakeReportEndDate();
    //2021/10/25 ��Ω������
    //$EndDate   = mu_Date::addDay($EndDate,1);
    
    // �Ǹ�����󤬺������줿�������������ޤǤα�ž���򽸷�
    for ($i=$StartDate;$i<=$EndDate;$i=mu_Date::addDay($i,1)) {
        MakeMachineReportOneDay($MacNo,$CsvFlg,$i);
    }
}
// --------------------------------------------------
// ���ꤵ�줿���������դα�ž��������
// --------------------------------------------------
function MakeMachineReportOneDay($MacNo,$CsvFlg,$Day)
{

    global $con,$Report;
    global $PrevRow,$NowRow,$Log;
    // �������������
    $StartDate = $Day.BUSINESS_DAY_CHANGE_TIME_KUMI;
    $EndDate   = mu_Date::addDay($Day,1).BUSINESS_DAY_CHANGE_TIME_KUMI;
    //////////////////////////////////////////////////////////////
    $StartDate = substr($StartDate, 0, 4) . '/' . substr($StartDate, 4, 2) . '/' . substr($StartDate, 6, 2) . ' ' . substr($StartDate, 8, 2) . ':' . substr($StartDate, 10, 2) . ':00';
    $EndDate   = substr($EndDate, 0, 4) . '/' . substr($EndDate, 4, 2) . '/' . substr($EndDate, 6, 2) . ' ' . substr($EndDate, 8, 2) . ':' . substr($EndDate, 10, 2) . ':00';
    //////////////////////////////////////////////////////////////
    
    // �о����ΰ��ֺǽ�˲�Ư����ؼ�No.�����
    // $sql = "select siji_no from equip_work_log2 "
    //      . "where date_time = (select min(date_time) from equip_work_log2 where mac_no=$MacNo and date_time >= to_timestamp('$StartDate', 'YYYYMMDDHHMI') and date_time < to_timestamp('$EndDate', 'YYYYMMDDHHMI')) "
    //      . "and mac_no=$MacNo ";
    $sql = "select plan_no
                from
                    equip_work_log2_moni
                where
                    equip_index2(mac_no, date_time) >= '{$MacNo}{$StartDate}'
                    and
                    equip_index2(mac_no, date_time) <  '{$MacNo}{$EndDate}'
                order by
                    equip_index2(mac_no, date_time) ASC
                limit 1 offset 0
    ";
    $rs = pg_query ($con , $sql);
    if($Row  = pg_fetch_array ($rs)) {
        $StartSijiNo = $Row['plan_no'];     // �ǡ���ͭ��
    } else {
        $StartSijiNo = 0;                   // �ǡ���̵��
    }
    $rs = pg_query ($con , $sql);
    
    // �о����ΰ��ֺǸ�˲�Ư����ؼ�No.�����
    // $sql = "select siji_no from equip_work_log2 "
    //      . "where date_time = (select max(date_time) from equip_work_log2 where mac_no=$MacNo and date_time >= to_timestamp('$StartDate', 'YYYYMMDDHHMI') and date_time < to_timestamp('$EndDate', 'YYYYMMDDHHMI')) "
    //      . "and mac_no=$MacNo ";
    $sql = "select plan_no
                from
                    equip_work_log2_moni AS a
                where
                    equip_index2(mac_no, date_time) >= '{$MacNo}{$StartDate}'
                    and
                    equip_index2(mac_no, date_time) <  '{$MacNo}{$EndDate}'
                order by
                    equip_index2(mac_no, date_time) DESC
                limit 1 offset 0
    ";
    /* 2021/10/25 ��Ω������
    $sql = "select plan_no
                from
                    equip_work_log2_moni AS a
                where
                    equip_index2(mac_no, date_time) >= '{$MacNo}{$StartDate}'
                    and
                    equip_index2(mac_no, date_time) <  '{$MacNo}{$EndDate}'
                    and 
                      (SELECT h.plan_no
                           FROM
                          equip_work_log2_header_moni AS h
                          WHERE
                         h.mac_no={$MacNo} and
                         h.work_flg=false
                         and h.plan_no=a.plan_no)<>''
                order by
                    equip_index2(mac_no, date_time) DESC
                limit 1 offset 0
    ";
    */
    $rs = pg_query ($con , $sql);
    if($Row  = pg_fetch_array ($rs)) {
        $EndSijiNo = $Row['plan_no'];     // �ǡ���ͭ��
    } else {
        $EndSijiNo = 0;                   // �ǡ���̵��
    }
    
    // ��Уӣѣ�
    //$sql = "select mac_no,to_char(date_time, 'YYYY-MM-DD HH24:MI:SS') as date_time,mac_state,work_cnt,siji_no,koutei from equip_work_log2 "
    //     . "where  mac_no=$MacNo and date_time >= to_timestamp('$StartDate', 'YYYYMMDDHHMI') and date_time < to_timestamp('$EndDate', 'YYYYMMDDHHMI') "
    //     . "order by siji_no,date_time ";
    $sql = " SELECT "
         . "     a.mac_no, "
         . "     to_char(a.date_time, 'YYYY-MM-DD HH24:MI:SS') as date_time, "
         . "     a.mac_state, "
         . "     a.work_cnt, "
         . "     a.plan_no, "
         . "     a.koutei, "
         . "     b.mintime "
         . " FROM "
         . "     equip_work_log2_moni a  "
         . " left outer join (SELECT "
         . "                       mac_no, "
         . "                       plan_no, "
         . "                       min(date_time) as mintime "
         . "                   FROM "
         . "                       equip_work_log2_moni "
         . "                   WHERE "
         // . "                       mac_no=$MacNo AND "
         // . "                       date_time >= to_timestamp('$StartDate', 'YYYYMMDDHHMI') AND "
         // . "                       date_time < to_timestamp('$EndDate', 'YYYYMMDDHHMI') "
         . "                       equip_index2(mac_no, date_time) >= '{$MacNo}{$StartDate}' AND "
         . "                       equip_index2(mac_no, date_time) <  '{$MacNo}{$EndDate}' "
         . "                   GROUP BY "
         . "                       mac_no, "
         . "                       plan_no "
         . "                   ) b on a.mac_no=b.mac_no and a.plan_no=b.plan_no "
         . " WHERE "
         // . "     a.mac_no=$MacNo AND "
         // . "     a.date_time >= to_timestamp('$StartDate', 'YYYYMMDDHHMI') AND "
         // . "     a.date_time < to_timestamp('$EndDate', 'YYYYMMDDHHMI') "
         . "     equip_index2(a.mac_no, a.date_time) >= '{$MacNo}{$StartDate}' AND "
         . "     equip_index2(a.mac_no, a.date_time) <  '{$MacNo}{$EndDate}'"
         //2021/10/25 ��Ω������
         //. "     equip_index2(a.mac_no, a.date_time) <  '{$MacNo}{$EndDate}' AND "
         //. "     (SELECT h.plan_no FROM equip_work_log2_header_moni AS h WHERE h.mac_no={$MacNo} and h.work_flg=false and h.plan_no=a.plan_no)<>''"
         . " ORDER BY "
         . "     b.mintime, "
         . "     a.plan_no, "
         . "     a.date_time ";
    $rs = pg_query ($con , $sql);
    
    // �������Υ쥳����
    $PrevRow = null;
    //  ���ߤΥ쥳����
    $NowRow  = null;
    
    $PrevSet = false;
    // ���򽸷�
    while (true) {
        // ���ߤΥ쥳���ɤ򣱷����Υ쥳���ɤ˥��å�
        $PrevRow = $NowRow;
        // ���Υ쥳�����ɤ߹���
        $NowRow  = pg_fetch_array ($rs);
        // �����֥쥤�������ɤμ���
        $KeyCode = KeyBreakCheck($NowRow,$PrevRow,$CsvFlg);
        // �����ܤλ��ν���
        if (!$PrevRow) {
            // �����ʤ����ϵ������Ÿ������äƤ��ʤ�����
            if (!$NowRow) {
                // ������������������
                // 2021/10/25 ��Ω������
                //MakeZeroReport($MacNo,$Day);
                break;
            }
        }
        // (KeyCode:1) ���ơ��������Ѥ�ä���
        if ($KeyCode >= 1) {
            // (KeyCode:3)�����ܤϥ��롼
            if ($KeyCode != 3) {
                // --------------------------------------------------
                // ���ǡ����Υ��å�
                // --------------------------------------------------
                $Log['work_date']   = getBusinessDay($PrevRow['date_time']);
                $Log['mac_no']      = $MacNo;
                $Log['plan_no']     = $PrevRow['plan_no'];
                $Log['koutei']      = $PrevRow['koutei'];
                $Log['mac_state']   = $PrevRow['mac_state'];
                // ���ơ��������Ѥ�뤫���ؼ��������ֹ椬����ä��鸽�ߤΥ쥳���ɤλ���򥻥å�
                $Log['ToDate']      = mu_Date::toString($NowRow['date_time'],"Ymd");
                $Log['ToTime']      = mu_Date::toString($NowRow['date_time'],"Hi");
                if ($KeyCode == 4) {
                    $Log['ToDate']      = mu_Date::toString($PrevRow['date_time'],"Ymd");
                    $Log['ToTime']      = mu_Date::toString($PrevRow['date_time'],"Hi");
                }
                
                // �����Ѥ��Ȥ�
                if ($KeyCode >= 2) {
                    // �о����ΰ��ֺǸ�˲�Ư����ؼ�No.�ʤ������ޤǤλ���򥻥å�
                    if ($PrevRow['plan_no'] == $EndSijiNo) {
                        // 2021/10/25 ��Ω������
                        //$Log['ToDate']      = mu_Date::addDay($Log['work_date'] ,1);
                        //$Log['ToTime']      = BUSINESS_DAY_CHANGE_TIME_KUMI;
                    }
                    
                    // ��ž���إå��ɤ߹���
                    $sql = "SELECT "
                         . "    to_char(end_timestamp, 'YYYY-MM-DD HH24:MI:SS') as end_timestamp "
                         . "FROM "
                         . "    equip_work_log2_header_moni "
                         . "WHERE "
                         . "    mac_no=$MacNo AND "
                         . "    plan_no='".$PrevRow['plan_no']."' AND "
                         . "    koutei=".$PrevRow['koutei']." AND "
                         . "    work_flg=false ";
                    if (!$rsloghed = pg_query ($con , $sql)) {
                        pg_query ($con , 'ROLLBACK');
                        $SYSTEM_MESSAGE = "�ǡ����١����ι����˼��Ԥ��ޤ���\n$sql";
                        require_once ('../com/' . ERROR_PAGE);
                        exit();
                    }
                    if ($LogHedRow  = pg_fetch_array ($rsloghed)) {
                        // ��λ���դ��о����ʤ� ��λ�����ǽ���λ������Ȥ���
                        if (getBusinessDay($LogHedRow['end_timestamp']) == $Day) {
                            $Log['ToDate']      = mu_Date::toString($LogHedRow['end_timestamp'],"Ymd");
                            $Log['ToTime']      = mu_Date::toString($LogHedRow['end_timestamp'],"Hi");
                        }
                    }
                    
                }
                // ��������
                $sql = "insert into equip_work_report_moni_log(work_date,mac_no,plan_no,koutei,mac_state,from_date,from_time,to_date,to_time,cut_time,last_user) values ( "
                     . $Log['work_date']            . ","
                     . $Log['mac_no']               . ",'"
                     . $Log['plan_no']              . "',"
                     . $Log['koutei']               . ","
                     . $Log['mac_state']            . ","
                     . $Log['FromDate']             . ","
                     . $Log['FromTime']             . ","
                     . $Log['ToDate']               . ","
                     . $Log['ToTime']               . ","
                     . $Log['CutTime']              . ","
                     . "'" . $_SESSION['User_ID']   . "')";
                if (!pg_query ($con , $sql)) {
                    pg_query ($con , 'ROLLBACK');
                    $SYSTEM_MESSAGE = "�ǡ����١����ι����˼��Ԥ��ޤ���\n$sql";
                    require_once ('../com/' . ERROR_PAGE);
                    exit();
                }
                // --------------------------------------------------
                // ���ǡ����Υ��å�
                // --------------------------------------------------
                $Log['FromDate'] = mu_Date::toString($NowRow['date_time'],'Ymd');
                $Log['FromTime'] = mu_Date::toString($NowRow['date_time'],'Hi');
                $Log['CutTime']     = 0;
            }
        }
        // (KeyCode:2) �ؼ������������դ��Ѥ�ä��� �������̤ˤʤ��
        if ($KeyCode >= 2) {
            // (KeyCode:3) �����ܤΥǡ����ǤϤʤ���
            if ($KeyCode != 3) {
                // �إå��쥳���ɤκ��� 
                MakeHeaderRecode($PrevRow);
            }
            // �о������ֺǽ�˻Ϥޤ�ؼ�No.��8:30�Υ��Ǥʤ���С�����ʬ�κǽ����򳫻ϻ���ޤǰ����Ѥ�
            if ($NowRow['plan_no'] == $StartSijiNo && mu_Date::toString($NowRow['date_time'],"Hi") != BUSINESS_DAY_CHANGE_TIME_KUMI) {
                // $sql = " select * from equip_work_log2 where mac_no=$MacNo and date_time = (select max(date_time) from equip_work_log2 where mac_no=$MacNo and date_time < to_timestamp('" . $Day.BUSINESS_DAY_CHANGE_TIME_KUMI . "','YYYYMMDDHHMI'))";
                $basicDate = REPORT_START_DATE . BUSINESS_DAY_CHANGE_TIME_KUMI;
                $basicDate = substr($basicDate, 0, 4) . '/' . substr($basicDate, 4, 2) . '/' . substr($basicDate, 6, 2) . ' ' . substr($basicDate, 8, 2) . ':' . substr($basicDate, 10, 2) . ':00';
                $sql = " select * 
                            from
                                equip_work_log2_moni
                            where
                                equip_index2(mac_no, date_time) < '{$MacNo}{$StartDate}'
                                and
                                equip_index2(mac_no, date_time) >= '{$MacNo}{$basicDate}'
                            order by
                                equip_index2(mac_no, date_time) DESC
                            limit 1 offset 0
                ";
                if (!$logrs = pg_query ($con , $sql)) {
                    pg_query ($con , 'ROLLBACK');
                    $SYSTEM_MESSAGE = "�����ƥ२�顼��ȯ�����ޤ���\n$sql";
                    require_once ('../com/'.ERROR_PAGE);
                    exit();
                }
                // �����ǥ쥳���ɤ������Ǥ��ʤ����������Ư�������Ȥ��ʤ������ʤΤ�
                // ����ϤĤ���ʤ��ʾ�����­�ǤĤ���ʤ��ˡ� �ޥ�������¸�ߤ���Τ��⡣
                if ($logrow =  pg_fetch_array ($logrs)) {
                    // Ʊ��Υ��ơ������ξ��绻
                    if ($logrow['mac_state'] == $NowRow['mac_state']) {
                        $Log['FromDate']            = $Day;
                        $Log['FromTime']            = BUSINESS_DAY_CHANGE_TIME_KUMI;
                        $PrevSet = true;
                    } else {
                        $Log['work_date']           = $Day;
                        $Log['mac_no']              = $MacNo;
                        $Log['plan_no']             = $logrow['plan_no'];
                        $Log['koutei']              = $logrow['koutei'];
                        $Log['mac_state']           = $logrow['mac_state'];
                        $Log['FromDate']            = $Day;
                        $Log['FromTime']            = BUSINESS_DAY_CHANGE_TIME_KUMI;
                        $Log['ToDate']              = mu_Date::toString($NowRow['date_time'],"Ymd");
                        $Log['ToTime']              = mu_Date::toString($NowRow['date_time'],"Hi");
                        $Log['CutTime']             = 0;
                        // ��������
                        $sql = "insert into equip_work_report_moni_log(work_date,mac_no,plan_no,koutei,mac_state,from_date,from_time,to_date,to_time,cut_time,last_user) values ( "
                             . $Log['work_date']            . ","
                             . $Log['mac_no']               . ",'"
                             . $Log['plan_no']              . "',"
                             . $Log['koutei']               . ","
                             . $Log['mac_state']            . ","
                             . $Log['FromDate']             . ","
                             . $Log['FromTime']             . ","
                             . $Log['ToDate']               . ","
                             . $Log['ToTime']               . ","
                             . $Log['CutTime']              . ","
                             . "'" . $_SESSION['User_ID']   . "')";
                        if (!pg_query ($con , $sql)) {
                            pg_query ($con , 'ROLLBACK');
                            $SYSTEM_MESSAGE = "�ǡ����١����ι����˼��Ԥ��ޤ���\n$sql";
                            require_once ('../com/' . ERROR_PAGE);
                            exit();
                        }
                        if ($logrow['plan_no'] != $NowRow['plan_no']) {
                            $Report['mac_no']    = $MacNo;
                            $Report['plan_no']   = $logrow['plan_no'];
                            $Report['koutei']    = $logrow['koutei'];
                            $Report['work_date'] = getBusinessDay($NowRow['date_time']);
                            $Report['siji']      = 0;
                            $Report['ng']        = 0;
                            $Report['plan']      = 0;
                            $Report['injection'] = 0;
                            $Report['end_flg']   = 0;
                            $Report['memo']      = '';
                            $Report['min']       = 0;
                            $Report['max']       = 0;
                            MakeHeaderRecode($logrow);
                        }
                        $PrevSet = false;
                    }
                }
            }
            // --------------------------------------------------
            // ���ǡ����Υ��å�
            // --------------------------------------------------
            $Log['work_date']   = getBusinessDay($NowRow['date_time']);
            $Log['mac_no']      = $MacNo;
            $Log['plan_no']     = $NowRow['plan_no'];
            $Log['koutei']      = $NowRow['koutei'];
            $Log['mac_state']   = $NowRow['mac_state'];
            if ($PrevSet == false) {
                $Log['FromDate']    = mu_Date::toString($NowRow['date_time'],"Ymd");
                $Log['FromTime']    = mu_Date::toString($NowRow['date_time'],"Hi");
            }
            $Log['ToDate']      = mu_Date::toString($NowRow['date_time'],"Ymd");
            $Log['ToTime']      = mu_Date::toString($NowRow['date_time'],"Hi");
            $Log['CutTime']     = 0;
            $PrevSet = false;
            // --------------------------------------------------
            // ����ǡ����ν�����å�
            // --------------------------------------------------
            $Report['mac_no']    = $MacNo;
            $Report['plan_no']   = $NowRow['plan_no'];
            $Report['koutei']    = $NowRow['koutei'];
            $Report['work_date'] = getBusinessDay($NowRow['date_time']);
            $Report['siji']      = 0;
            $Report['ng']        = 0;
            $Report['plan']      = 0;
            $Report['injection'] = 0;
            $Report['end_flg']   = 0;
            $Report['memo']      = '';
            $Report['min']       = 999999999;
            $Report['max']       = 0;
        }
        
        // (KeyCode:4) �ǡ������ɤ�ʤ��ʤä��齪λ
        if ($KeyCode == 4) {
            break;
        }
        
        if ($NowRow['work_cnt'] < $Report['min']) $Report['min'] = $NowRow['work_cnt'];
        if ($NowRow['work_cnt'] > $Report['max']) $Report['max'] = $NowRow['work_cnt'];
        // **************************************** //
        // �����ѹ������ǤϤ��Τޤ޻��� 2004/07/08  //
        // **************************************** //
        // ���åȻ��֤ν���
        //if (($CsvFlg == '1' && $NowRow['mac_state'] == '15') || ($CsvFlg == '2' && $NowRow['mac_state'] ==  '9')) {
        //    $Log['CutTime'] += CalWorkTime(mu_Date::toString($PrevRow['date_time'],"Hi"),mu_Date::toString($NowRow['date_time'],"Hi"));
        //    $NowRow['mac_state'] = $PrevRow['mac_state'];
        //}
    }

}
// --------------------------------------------------
// �����֥쥤����Ƚ��
// --------------------------------------------------
function KeyBreakCheck($NowRow,$PrevRow,$CsvFlg)
{
    // �쥳���ɤ��ɤ�ʤ��ʤä�
    if (!$NowRow) return 4;
    // �����ܤΥ쥳����
    if (!$PrevRow) return 3;
    // �ײ�No.���Ѥ�ä�
    if ($NowRow['plan_no'] != $PrevRow['plan_no']) return 2;
    // ����No.���Ѥ�ä�
    if ($NowRow['koutei'] != $PrevRow['koutei']) return 2;
    // ��̳���դ��Ѥ�ä�
    if (getBusinessDay($NowRow['date_time']) != getBusinessDay($PrevRow['date_time'])) return 2;
    
    // **************************************** //
    // �����ѹ������ǤϤ��Τޤ޻��� 2004/07/08  //
    // **************************************** //
    // ���ơ��������Ǥϥ֥쥤�����ʤ�
    //if (($CsvFlg == '1' && $NowRow['mac_state'] == '15') || ($CsvFlg == '2' && $NowRow['mac_state'] ==  '9')) {
    //    return 0;
    //}
    //if ($CsvFlg == '1' && $NowRow['mac_state'] == '15') return 0;
    //if ($CsvFlg == '2' && $NowRow['mac_state'] ==  '9') return 0;
    
    // ���ơ��������Ѥ�ä�
    if ($NowRow['mac_state'] != $PrevRow['mac_state']) return 1;
    
    // �Ѳ��ʤ�
    return 0;
}
// --------------------------------------------------
// �Ϥ��줿���������̳���դ����
// �����ѹ������ define.php ��
// BUSINESS_DAY_CHANGE_TIME_KUMI �����
//
// (��)
// 2004/06/01 10:00  -> 2004/06/01
// 2004/06/02  3:00  -> 2004/06/01
// --------------------------------------------------
function getBusinessDay($DateTime)
{
    
    $Date = mu_Date::toString($DateTime,"Ymd");
    $Time = mu_Date::toString($DateTime,"Hi");
    
    if ($Time < BUSINESS_DAY_CHANGE_TIME_KUMI) {
        $Date = mu_Date::addDay($Date ,-1);
    }
    
    return $Date;
}
// --------------------------------------------------
// ��ž����إå��쥳���ɺ���
// --------------------------------------------------
function MakeHeaderRecode($row)
{
    global $con,$Report;
    // ���إå��ɤ߹���
    $sql = " select koutei,parts_no,plan_cnt from equip_work_log2_header_moni where mac_no=".$row['mac_no']." and plan_no='".$row['plan_no']."' and koutei=".$row['koutei'];
    $rs = pg_query ($con , $sql);
    if (!$hed =  pg_fetch_array ($rs)) {
        pg_query ($con , 'ROLLBACK');
        $SYSTEM_MESSAGE = "�����ƥ२�顼��ȯ�����ޤ���\n���إå������Ĥ���ޤ���Ǥ���\n\n$sql";
        require_once ('../com/'.ERROR_PAGE);
        exit();
    }
    // �����η׻���mac_no����Ѥ��뤿�ᡢ���������� 2006/06/12
    $MacNo = $row['mac_no'];
    // --------------------------------------------------
    // �������ʿ��μ���
    // --------------------------------------------------
    $sql = " select yesterday + today as num from equip_work_report_moni "
         . " where  work_date=".mu_Date::addDay($Report['work_date'],-1)." and mac_no=".$row['mac_no']." and plan_no='".$row['plan_no']."' and koutei=".$row['koutei'];
         
    if (!$rs = pg_query ($con , $sql)) {
        pg_query ($con , 'ROLLBACK');
        $SYSTEM_MESSAGE = "�����ƥ२�顼��ȯ�����ޤ���\n$sql";
        require_once ('../com/'.ERROR_PAGE);
        exit();
    }
    if ($row =  pg_fetch_array ($rs)) {
        $Report['yesterday'] = $row['num'];
    } else {
        $Report['yesterday'] = 0;
    }
    
    // �������ʿ�
    $Report['today'] = $Report['max'] - $Report['min'];
    
    // --------------------------------------------------
    // ���������η׻�
    // --------------------------------------------------
    if ($row = getPartsMaster($hed['parts_no'], $MacNo)) {
        $JoinChecker = 0;
        // ���ʥޥ��� join ������
        if (isset($row['item_code'])) {
            $Report['injection_item']   = $row['use_item'];        // �����ֹ�
            $JoinChecker++;
        } else {
            // ���������ƥ�
            $Report['injection_item'] = '';
        }
        
        // �����ޥ��� join ������
        if (isset($row['weight'])) {
            $JoinChecker++;
        }
        // ���Ƥξ��󤬼����Ǥ�������������η׻�����ǽ
        if ($JoinChecker == 2) {
            // �С���η׻�
            if ($row['type'] == 'B') {
                // ɬ�פ�Ĺ��
                $NeedLength = $row['size'] / 1000 * $Report['today'];
                
                // �˺ब���뤫
                $sql = " select length,weight from equip_abandonment_item where item_code='" . $row['use_item'] . "'";
                $rs2 = pg_query ($con,$sql);
                if ($row2 = pg_fetch_array($rs2)) {
                    // �Ȥä��˺�
                    if (($row2['length'] - $row['abandonment'] / 1000) < $NeedLength) {
                        // �˺�����Ǥ�­��ʤ�
                        
                        /**********************/
                        /** ü������Ѥη׻� **/
                        /**********************/
                        $CalNeedLength = $NeedLength;
                        // $CalNeedLength -= ($row2['length'] - $row['abandonment'] / 1000);
                        
                        // ������  �˺�Ĥ��ä�­��ʤ�Ĺ�� / �����������Ĺ�� - �˺ॵ����
                        $Report['injection'] = round(($CalNeedLength / ($row['length']-($row['abandonment']/1000))), 2);
                        // �Ĥ��ä��˺�ʬĹ��
                        // $Report['abandonment'] = Mfloor($CalNeedLength - ($row['length']-$row['abandonment']/1000) * $injection_int , 4);
                        $Report['abandonment'] = ($Report['injection'] - ((int)$Report['injection'])) * $row['length'];
                        
                        // ;�ä��˺�
                        if ($Report['abandonment'] != 0) {
                            // �˺�Ф���
                            $AbandonmentLength = $row['length'] - $Report['abandonment'];
                        } else {
                            // �˺�ФƤʤ�
                            $AbandonmentLength = 0;
                        }
                        $AbandonmentWeight = $AbandonmentLength * $row['weight'];
                        
                        // �˺�ޥ������� 
                        $sql = "update equip_abandonment_item set length=$AbandonmentLength , weight=$AbandonmentWeight where item_code='" . $row['use_item'] . "'";
                        pg_query ($con,$sql);
                        
                        /**********************/
                        /** ����ɽ���Ѥη׻� **/
                        /**********************/
                        // $NeedLength -= ($row2['length'] - $row['abandonment'] / 1000);
                        
                        // ������  �˺�Ĥ��ä�­��ʤ�Ĺ�� / �����������Ĺ�� - �˺ॵ����
                        $Report['injection'] = round(($NeedLength / ($row['length']-($row['abandonment']/1000))), 2);
                        // �Ĥ��ä��˺�ʬĹ��
                        // $Report['abandonment'] = Mfloor($NeedLength - ($row['length']-$row['abandonment']/1000) * $injection_int , 4);
                        $Report['abandonment'] = ($Report['injection'] - ((int)$Report['injection'])) * $row['length'];
                        
                    } else {
                        // �˺�����Ǥ��ꤿ
                        $Report['abandonment'] = $NeedLength;
                        // �˺�ޥ������� 
                        $sql = "update equip_abandonment_item set length=" . ($row2['length'] - $NeedLength) ." , weight= " . ($row2['length'] - $NeedLength) * $row['weight'] ." where item_code='" . $row['use_item'] . "'";
                        pg_query ($con,$sql);
                        // ������ �˺�Ȥä��飰�������Ȥ��ƥ�����Ȣ���������ɬ�פ��ѹ�
                        $Report['injection'] = round($NeedLength, 2);
                        // �Ĥ��ä��˺�ʬĹ��
                        $Report['abandonment'] = $NeedLength;
                    }
                } else {
                    // �˺�ޥ���¸�ߤ��ʤ��Ȥ�
                    // ������  �˺�Ĥ��ä�­��ʤ�Ĺ�� / �����������Ĺ�� - �˺ॵ����
                    $Report['injection'] = round(($NeedLength / ($row['length']-($row['abandonment'])/1000)), 2);
                    // �Ĥ��ä��˺�ʬĹ��
                    // $Report['abandonment'] = Mfloor($NeedLength - ($row['length']-$row['abandonment']/1000) * $injection_int , 4);
                    $Report['abandonment'] = ($Report['injection'] - ((int)$Report['injection'])) * $row['length'];
                    
                    // ;�ä��˺�
                    if ($Report['abandonment'] != 0) {
                        // �˺�Ф���
                        $AbandonmentLength = $row['length'] - $Report['abandonment'];
                    } else {
                        // �˺�ФƤʤ�
                        $AbandonmentLength = 0;
                    }
                    $AbandonmentWeight = $AbandonmentLength * $row['weight'];
                    
                    // �˺�ޥ������� 
                    $sql = "insert into equip_abandonment_item(item_code,length,weight) values('" . $row['use_item'] . "',$AbandonmentLength,$AbandonmentWeight)";
                    pg_query ($con,$sql);
                }
            }
            // ���åȺ�η׻�
            if ($row['type'] == 'C') {
                // ������
                $Report['injection'] = $Report['today'];
                // �Ĥ��ä��˺�ʬĹ��
                $Report['abandonment'] = 0;
            }
        } else {
            // �Ĥ��ä��˺�ʬĹ��
            $Report['abandonment'] = 0;
        }
    } else {
        // equip_work_inst_header �ˤʤ�
        
        // ���������ƥ�
        $Report['injection_item'] = '';
        // ������
        $Report['injection'] = 0;
        // �Ĥ��ä��˺�ʬĹ��
        $Report['abandonment'] = 0;
    }
    // �ӣѣ�
    $sql = "insert into equip_work_report_moni (work_date,mac_no,plan_no,koutei,yesterday,today,ng,ng_kbn,plan,end_flg,memo,injection_item,injection,abandonment,decision_flg,last_user) values ( "
         .       $Report['work_date']       . ","
         .       $Report['mac_no']          . ",'"
         .       $Report['plan_no']         . "',"
         .       $Report['koutei']          . ","
         .       $Report['yesterday']       . ","
         .       $Report['today']           . ","
         .       "0,"                                           // ���ɿ�
         .       "'',"                                          // ���ɶ�ʬ
         .       "0,"                                           // �ʼ���
         . "'" . $Report['end_flg']         . "',"
         . "'" . $Report['memo']            . "',"
         . "'" . $Report['injection_item']  . "',"
         .       $Report['injection']       . ","
         .       $Report['abandonment']     . ","
         .       "0,"                                           // ����ե饰��̤�����
         . "'" . $_SESSION['User_ID']   . "')";
    if (!pg_query ($con , $sql)) {
        pg_query ($con , 'ROLLBACK');
        $SYSTEM_MESSAGE = "�ǡ����١����ι����˼��Ԥ��ޤ���\n$sql";
        require_once ('../com/' . ERROR_PAGE);
        exit();
    }
}
// --------------------------------------------------
// ��ž�����ʤ����� ���������κ���
// --------------------------------------------------
function MakeZeroReport($MacNo,$Day)
{

    global $con,$Report;
    // �о����դΰ����κǸ�ˤǤ����������
    $date = $Day.BUSINESS_DAY_CHANGE_TIME_KUMI;
    // $sql = " select * from equip_work_log2 where mac_no=$MacNo and date_time = (select max(date_time) from equip_work_log2 where mac_no=$MacNo and date_time < to_timestamp('" . $Day.BUSINESS_DAY_CHANGE_TIME_KUMI . "','YYYYMMDDHHMI'))";
    $StartDate = substr($date, 0, 4) . '/' . substr($date, 4, 2) . '/' . substr($date, 6, 2) . ' ' . substr($date, 8, 2) . ':' . substr($date, 10, 2) . ':00';
    $basicDate = REPORT_START_DATE . BUSINESS_DAY_CHANGE_TIME_KUMI;
    $basicDate = substr($basicDate, 0, 4) . '/' . substr($basicDate, 4, 2) . '/' . substr($basicDate, 6, 2) . ' ' . substr($basicDate, 8, 2) . ':' . substr($basicDate, 10, 2) . ':00';
    $sql = " select * 
                from
                    equip_work_log2_moni
                where
                    equip_index2(mac_no, date_time) < '{$MacNo}{$StartDate}'
                    and
                    equip_index2(mac_no, date_time) >= '{$MacNo}{$basicDate}'
                order by
                    equip_index2(mac_no, date_time) DESC
                limit 1 offset 0
    ";
    if (!$rs = pg_query ($con , $sql)) {
        pg_query ($con , 'ROLLBACK');
        $SYSTEM_MESSAGE = "�����ƥ२�顼��ȯ�����ޤ���\n$sql";
        require_once ('../com/'.ERROR_PAGE);
        exit();
    }
    if (!$row =  pg_fetch_array ($rs)) {
        // �����ǥ쥳���ɤ������Ǥ��ʤ����������Ư�������Ȥ��ʤ������ʤΤ�
        // ����ϤĤ���ʤ��ʾ�����­�ǤĤ���ʤ��ˡ� �ޥ�������¸�ߤ���Τ��⡣
        return;
    }

    $Report['work_date']        = $Day;
    $Report['mac_no']           = $MacNo;
    $Report['plan_no']          = $row['plan_no'];
    $Report['koutei']           = $row['koutei'];
    $Report['end_flg']          = '';
    $Report['memo']             = '';
    $Report['injection_item']   = '';
    $Report['injection']        = 0;
    $Report['abandonment']      = 0;
    
    $Log['work_date']           = $Day;
    $Log['mac_no']              = $MacNo;
    $Log['plan_no']             = $row['plan_no'];
    $Log['koutei']              = $row['koutei'];
    $Log['mac_state']           = $row['mac_state'];
    $Log['FromDate']            = $Day;
    $Log['FromTime']            = BUSINESS_DAY_CHANGE_TIME_KUMI;
    $Log['ToDate']              = mu_Date::addDay($Day ,1);
    $Log['ToTime']              = BUSINESS_DAY_CHANGE_TIME_KUMI;
    $Log['CutTime']             = 0;


    // ��ž���إå��ɤ߹���
    $sql = "SELECT "
         . "    to_char(end_timestamp, 'YYYY-MM-DD HH24:MI:SS') as end_timestamp "
         . "FROM "
         . "    equip_work_log2_header_moni "
         . "WHERE "
         . "    mac_no=$MacNo AND "
         . "    plan_no='".$row['plan_no']."' AND "
         . "    koutei=".$row['koutei']." AND "
         . "    work_flg=false ";
    if (!$rsloghed = pg_query ($con , $sql)) {
        pg_query ($con , 'ROLLBACK');
        $SYSTEM_MESSAGE = "�ǡ����١����ι����˼��Ԥ��ޤ���\n$sql";
        require_once ('../com/' . ERROR_PAGE);
        exit();
    }
    if ($LogHedRow  = pg_fetch_array ($rsloghed)) {
        // ���������˴�λ���Ƥ���ΤǤ���� ����Ĥ���ʤ�
        if (getBusinessDay($LogHedRow['end_timestamp']) < $Day) {
            return;
        }
        // ��λ���դ��о����ʤ� ��λ�����ǽ���λ������Ȥ���
        if (getBusinessDay($LogHedRow['end_timestamp']) == $Day) {
            $Log['ToDate']      = mu_Date::toString($LogHedRow['end_timestamp'],"Ymd");
            $Log['ToTime']      = mu_Date::toString($LogHedRow['end_timestamp'],"Hi");
        }
    }
    

    // --------------------------------------------------
    // �������ʿ��μ���
    // --------------------------------------------------
    $sql = " select yesterday + today as num from equip_work_report_moni "
         . " where  work_date=".mu_Date::addDay($Report['work_date'],-1)." and mac_no=".$row['mac_no']." and plan_no='".$row['plan_no']."' and koutei=".$row['koutei'];
         
    if (!$rs = pg_query ($con , $sql)) {
        pg_query ($con , 'ROLLBACK');
        $SYSTEM_MESSAGE = "�����ƥ२�顼��ȯ�����ޤ���\n$sql";
        require_once ('../com/'.ERROR_PAGE);
        exit();
    }
    if ($row =  pg_fetch_array ($rs)) {
        $Report['yesterday'] = $row['num'];
    } else {
        $Report['yesterday'] = 0;
    }
    
    // �������ʿ�
    $Report['today'] = 0;
    
    $sql = "
        SELECT
            use_item
        FROM
            equip_work_log2_header_moni a
        LEFT OUTER JOIN  equip_parts b ON (a.parts_no=b.item_code)
        WHERE a.mac_no = {$Report['mac_no']} AND a.plan_no = '{$Report['plan_no']}' AND a.koutei = {$Report['koutei']}
        AND b.mac_no = {$Report['mac_no']}
    ";
    $rs = pg_query ($con , $sql);
    if ($row =  pg_fetch_array ($rs)) {
        $Report['injection_item']   = $row['use_item'];     // �������Ѥ�����������
    } else {
        $sql = "
            SELECT
                use_item
            FROM
                equip_work_log2_header_moni a
            LEFT OUTER JOIN  equip_parts b ON (a.parts_no=b.item_code)
            WHERE a.mac_no = {$Report['mac_no']} AND a.plan_no = '{$Report['plan_no']}' AND a.koutei = {$Report['koutei']}
        ";
        if ($row =  pg_fetch_array ($rs)) {
            $Report['injection_item']   = $row['use_item'];     // �������Ѥ�̵����ж��ѥǡ����������
        }
    }
    
    // �ӣѣ�
    $sql = "insert into equip_work_report_moni (work_date,mac_no,plan_no,koutei,yesterday,today,ng,ng_kbn,plan,end_flg,memo,injection_item,injection,abandonment,decision_flg,last_user) values ( "
         .       $Report['work_date']       . ","
         .       $Report['mac_no']          . ",'"
         .       $Report['plan_no']         . "',"
         .       $Report['koutei']          . ","
         .       $Report['yesterday']       . ","
         .       $Report['today']           . ","
         .       "0,"                                           // ���ɿ�
         .       "'',"                                          // ���ɶ�ʬ
         .       "0,"                                           // �ʼ���
         . "'" . $Report['end_flg']         . "',"
         . "'" . $Report['memo']            . "',"
         . "'" . $Report['injection_item']  . "',"
         .       $Report['injection']       . ","
         .       $Report['abandonment']     . ","
         .       "0,"                                           // ����ե饰��̤�����
         . "'" . $_SESSION['User_ID']   . "')";

    if (!pg_query ($con , $sql)) {
        pg_query ($con , 'ROLLBACK');
        $SYSTEM_MESSAGE = "�ǡ����١����ι����˼��Ԥ��ޤ���\n$sql";
        require_once ('../com/' . ERROR_PAGE);
        exit();
    }
    // ��������
    $sql = "insert into equip_work_report_moni_log(work_date,mac_no,plan_no,koutei,mac_state,from_date,from_time,to_date,to_time,cut_time,last_user) values ( "
         . $Log['work_date']            . ","
         . $Log['mac_no']               . ",'"
         . $Log['plan_no']              . "',"
         . $Log['koutei']               . ","
         . $Log['mac_state']            . ","
         . $Log['FromDate']             . ","
         . $Log['FromTime']             . ","
         . $Log['ToDate']               . ","
         . $Log['ToTime']               . ","
         . $Log['CutTime']              . ","
         . "'" . $_SESSION['User_ID']   . "')";
    if (!pg_query ($con , $sql)) {
        pg_query ($con , 'ROLLBACK');
        $SYSTEM_MESSAGE = "�ǡ����١����ι����˼��Ԥ��ޤ���\n$sql";
        require_once ('../com/' . ERROR_PAGE);
        exit();
    }

}
// --------------------------------------------------
// �Ϥ��줿�����ֹ����������������դ����
// --------------------------------------------------
function getMakeReportStartDate($MacNo)
{

    global $con;
    
    $sql = "select max(work_date) as work_date from equip_work_report_moni where mac_no=$MacNo";
    $rs  = pg_query ($con , $sql);
    if ($row =  pg_fetch_array ($rs)) {
        $WorkDate = $row['work_date'];
        if (isset($WorkDate)) {
            $StartDate = mu_Date::addDay($WorkDate,1);
        } else {
            $StartDate = REPORT_START_DATE;
        }
    } else {
        $SYSTEM_MESSAGE = "�����ƥ२�顼��ȯ�����ޤ���\n$sql";
        require_once ('../com/'.ERROR_PAGE);
        exit();
    }
    
    return $StartDate;

}
// --------------------------------------------------
// ���������λ���դμ���
// --------------------------------------------------
function getMakeReportEndDate()
{
    /*
    $Year       = date('Y', time()); 
    $Month      = date('m', time()); 
    $Day        = date('d', time()); 
    $Hour       = date('H', time()); 
    $Minutes    = date('i', time()); 
    */
    $Date       = date('Y', time()) . date('m', time()) . date('d', time());
    $Time       = date('H', time()) . date('i', time());
    if ($Time < BUSINESS_DAY_CHANGE_TIME_KUMI) {
        $Date = mu_Date::addDay($Date,-1);
    }
    
    $Date = mu_Date::addDay($Date,-1);
    
    return $Date;

}
// --------------------------------------------------
// ���ʥޥ������μ��� ���ʤȵ����ֹ�ǤΥ����б���
// --------------------------------------------------
function getPartsMaster($parts_no, $mac_no)
{
    global $con;
    $sql_basic = "
        SELECT
            item_code       ,
            size            ,
            use_item        ,
            abandonment     ,
            type            ,
            weight          ,
            length
        FROM
            equip_parts
        LEFT OUTER JOIN equip_materials ON (mtcode = use_item)
    ";
    $sql_where = "
        WHERE
            item_code = '{$parts_no}' AND mac_no = {$mac_no}
    ";
    $sql = ($sql_basic . $sql_where);
    if (!$rs = pg_query ($con , $sql)) {
        pg_query ($con , 'ROLLBACK');
        $SYSTEM_MESSAGE = "�����ƥ२�顼��ȯ�����ޤ���\n$sql";
        require_once ('../com/'.ERROR_PAGE);
        exit();
    }
    if ( !($row=pg_fetch_array($rs)) ) {
        $sql_where = "
            WHERE
                item_code = '{$parts_no}' AND mac_no = 0
        ";
        $sql = ($sql_basic . $sql_where);
        if (!$rs = pg_query ($con , $sql)) {
            pg_query ($con , 'ROLLBACK');
            $SYSTEM_MESSAGE = "�����ƥ२�顼��ȯ�����ޤ���\n$sql";
            require_once ('../com/'.ERROR_PAGE);
            exit();
        }
        $row = pg_fetch_array($rs);
    }
    return $row;
}

?>
