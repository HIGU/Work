<?php 
//////////////////////////////////////////////////////////////////////////////
// ��Ω������Ư���������ƥ�ε�����ž���� ���󸡺��ե�����                  //
// Copyright (C) 2021-2021      norihisa_ooya@nitto-kohki.co.jp             //
// Original by yamagishi@matehan.co.jp                                      //
// Changed history                                                          //
// 2021/03/26 Created   ReportList.php                                      //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);

require_once ('../../../function.php');     // TNK ������ function
require_once ('../../../MenuHeader.php');   // TNK ������ menu class
require_once ('../com/define.php');
require_once ('../com/function.php');
require_once ('../com/mu_date.php');
require_once ('../com/PageControl.php');

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader(0, EQUIP_MENU2);     // ǧ�ڥ�٥�=0, �꥿���󥢥ɥ쥹, �����ȥ�λ���ʤ�
access_log();                               // Script Name �ϼ�ư����

// �����ԥ⡼��
$AdminUser = AdminUser( FNC_REPORT );
$suUser    = AdminUser( FNC_ACCOUNT );      // �����ѡ��桼����

// �ѥ�᡼���γ�Ǽ
setParameter();

// ��å������Υ��ꥢ
$Message = '';

///// ����μ��
if (isset($_REQUEST['Undecision'])) {
    $work_date = @$_REQUEST['work_date'];
    $mac_no    = @$_REQUEST['mac_no'];
    $plan_no   = @$_REQUEST['plan_no'];
    $koutei    = @$_REQUEST['koutei'];
    // $_REQUEST �� MenuHeader ���饹�ǥ����å��Ѥ�
    $query = "UPDATE equip_work_report_moni SET decision_flg=0 where work_date={$work_date} and mac_no={$mac_no} and plan_no='{$plan_no}' and koutei={$koutei}";
    if (query_affected($query) > 0) {
        $_SESSION['s_sysmsg'] = "������äޤ�����  ��ž��={$work_date}  ����No.={$mac_no}  �ؼ�No.={$plan_no}  ����No.={$koutei}";
    } else {
        $_SESSION['s_sysmsg'] = "����μ�ä�����ޤ���Ǥ�����  ��ž��={$work_date}  ����No.={$mac_no}  �ؼ�No.={$plan_no}  ����No.={$koutei}  ����ô���Ԥ�Ϣ���Ʋ�������";
    }
}

if ($Parameter['ProcCode'] != '') { 
    if ($EntryCheck = EntryCheck()) {
        $con = getConnection();
        $sql = MakeSql();
        if ($rs = pg_query ($con , $sql)) {
            /** �ڡ�������ȥ��� */
            $PageCtl['ViewPage']    = $Parameter['ViewPage'];
            $PageCtl['ListNum']     = $Parameter['ListNum'];
            $PageCtl['RowsNum']     = pg_num_rows ($rs);
            $PageCtl['StartRecNum'] = getStartRecNum($PageCtl['ViewPage'],$PageCtl['ListNum']);
            $PageCtl['EndRecNum']   = getEndRecNum($PageCtl['ViewPage'],$PageCtl['ListNum'],$PageCtl['RowsNum']);
        }
    }
}
// --------------------------------------------------
// �ѥ�᡼���γ�Ǽ         
// --------------------------------------------------
function setParameter()
{
    global $Parameter;
    
    $Parameter['ProcCode']       = @$_REQUEST['ProcCode'];
    $Parameter['ViewPage']       = @$_REQUEST['ViewPage'];
    $Parameter['ListNum']        = @$_REQUEST['ListNum'];
    $Parameter['Type']           = @$_REQUEST['Type'];
    $Parameter['FromYear']       = @$_REQUEST['FromYear'];
    $Parameter['FromMonth']      = @$_REQUEST['FromMonth'];
    $Parameter['FromDay']        = @$_REQUEST['FromDay'];
    $Parameter['ToYear']         = @$_REQUEST['ToYear'];
    $Parameter['ToMonth']        = @$_REQUEST['ToMonth'];
    $Parameter['ToDay']          = @$_REQUEST['ToDay'];
    $Parameter['Decision']       = @$_REQUEST['Decision'];
    $Parameter['Remark']         = @$_REQUEST['Remark'];
    $Parameter['MacNo']          = @$_REQUEST['MacNo'];
    
}
// --------------------------------------------------
// ���Ͼ��Υ����å�
// --------------------------------------------------
function EntryCheck()
{
    global $Message,$Parameter;
    
    // ProcCode���ʤ���� ��ư����
    if ($Parameter['ProcCode'] == '') return false;
    
    $RetVal = true;
    
    // mu_Date�Ǥ�ǯ����Υ����å������꤬���뤿��ʲ����ɲ� 2007/04/04
    $Parameter['FromMonth'] = sprintf('%02s', $Parameter['FromMonth']);
    $Parameter['FromDay']   = sprintf('%02s', $Parameter['FromDay']);
    $Parameter['ToMonth']   = sprintf('%02s', $Parameter['ToMonth']);
    $Parameter['ToDay']     = sprintf('%02s', $Parameter['ToDay']);
    
    // ���դΥ����å�
    $FromDate = $Parameter['FromYear'] . '/' . $Parameter['FromMonth'] . '/' . $Parameter['FromDay'];
    $ToDate   = $Parameter['ToYear'] . '/' . $Parameter['ToMonth'] . '/' . $Parameter['ToDay'];
    
    if ($FromDate != '' && !mu_Date::chkDate ($FromDate)) {
        $Message .= '���ϱ�ž��������������ޤ���\n\n';
    }
    if ($ToDate != '' && !mu_Date::chkDate ($ToDate)) {
        $Message .= '��λ��ž��������������ޤ���\n\n';
    }
    
    if (!is_numeric($Parameter['MacNo']) && $Parameter['MacNo'] != '') {
        $Message .= '����No.�Ͽ��ͤ����Ϥ��Ʋ�����\n\n';
    }
    
    if ($Message != '') $RetVal = false;
    
    return $RetVal;
}
// --------------------------------------------------
// �ӣѣ̤�����
// --------------------------------------------------
function MakeSql()
{
    global $Parameter;

    if (isset($_SESSION['factory'])) {
        $factory = $_SESSION['factory'];
    } else {
        $factory = '';
    }
    $FromDate  = $Parameter['FromYear'] . $Parameter['FromMonth'] . $Parameter['FromDay'];
    $ToDate    = $Parameter['ToYear'] . $Parameter['ToMonth'] . $Parameter['ToDay'];
    $sub_query = "(
                    select work_date, mac_no, plan_no, koutei
                        , min(to_char(from_date,'99999999') || to_char(from_time,'9999')) as datetime
                    from
                        equip_work_report_moni_log
                    where
                        work_date>={$FromDate} and work_date<={$ToDate} -- 2005/02/25 ADD
                    group by
                        work_date, mac_no, plan_no, koutei
                )
    ";
    $sub_query2 = "(
                    select work_date, mac_no, plan_no, koutei, mac_state
                        , min(to_char(from_date,'99999999') || to_char(from_time,'9999')) as datetime
                    from
                        equip_work_report_moni_log
                    where
                        work_date>={$FromDate} and work_date<={$ToDate} -- 2005/02/25 ADD
                        and mac_state= '6'
                    group by
                        work_date, mac_no, plan_no, koutei, mac_state
                )
    ";
    
    $sql = " select                     "
         . "     a.work_date        as work_date,             "
         . "     a.mac_no           as mac_no,                "
         . "     a.plan_no          as plan_no,               "
         . "     a.koutei           as koutei,                "
         . "     a.today            as today,                 "
         . "     a.decision_flg     as decision_flg,          "
         . "     CASE WHEN c.plan_cnt IS NULL THEN '������'     "
         . "     ELSE CAST(c.plan_cnt AS TEXT) END AS plan_cnt, "
         . "     b.parts_no         as parts_no,              "
         . "     d.midsc            as midsc,                 "
         . "     d.mzist            as mzist,                 "
         . "     e.mac_name         as mac_name,              "
         . "     f.datetime         as datetime               "
         . " from equip_work_report_moni a   "
         . " left outer join assembly_schedule b on a.plan_no=b.plan_no "
         . " left outer join equip_work_log2_header_moni c on a.mac_no=c.mac_no and a.plan_no=c.plan_no and a.koutei=c.koutei "
         . " left outer join miitem d on d.mipn=b.parts_no "
         . " left outer join equip_machine_master2 e on a.mac_no=e.mac_no "
         . " left outer join {$sub_query} f on f.work_date=a.work_date and f.mac_no = a.mac_no and f.plan_no=a.plan_no and f.koutei=a.koutei "
         . " left outer join {$sub_query2} r on r.work_date=a.work_date and r.mac_no = a.mac_no and r.plan_no=a.plan_no and r.koutei=a.koutei ";
    if ($factory != '') {
        $sql .= " where e.factory={$factory} ";
    } else {
        $sql .= " where 0=0 ";
    }

    // where������

    // $FromDate = $Parameter['FromYear'] . '/' . $Parameter['FromMonth'] . '/' . $Parameter['FromDay'];
    // $ToDate   = $Parameter['ToYear'] . '/' . $Parameter['ToMonth'] . '/' . $Parameter['ToDay'];
    
    if ($FromDate != '') {
        // $sql .= ' and a.work_date>=' . mu_Date::toString($FromDate,'Ymd');
        $sql .= ' and a.work_date>=' . $FromDate;
    }
    if ($ToDate != '') {
        // $sql .= " and a.work_date<=" . mu_Date::toString($ToDate,'Ymd');
        $sql .= " and a.work_date<=" . $ToDate;
    }
    
    if ($Parameter['Decision'] != 'Z') {
        $sql .= " and decision_flg=".$Parameter['Decision'];
    }
    if ($Parameter['Remark'] == '1A') {
        $sql .= " and memo != ''";
    } elseif ($Parameter['Remark'] == '16') {
        $sql .= " and memo != '' and r.mac_state = '6'";
    } elseif ($Parameter['Remark'] == '1N') {
        $sql .= " and memo != '' and r.mac_state IS NULL";
    } elseif ($Parameter['Remark'] == '0') {
        $sql .= " and memo = ''";
    }
    if ($Parameter['MacNo'] != '') {
        $sql .= "and a.mac_no=" . $Parameter['MacNo'];
    }
    
    $sql .= ' order by a.work_date desc, mac_no, datetime desc, plan_no';
    
    return $sql;
}
ob_start('ob_gzhandler');
SetHttpHeader();
?>
<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.01 Transitional//EN'>
<html>
<head>
<?php require_once ('../com/PageHeader.php'); ?>
<script language='JavaScript'>
function init() {
<?php if ($Message != '') { ?>
    alert("<?php echo $Message?>");
<?php } ?>
}
function doView(Type,WorkDate,PlanNo,MacNo,KouteiNo) {
    document.MainForm.ProcCode.value = 'VIEW';
    document.MainForm.EDIT_MODE.value = '';
    document.MainForm.SummaryType.value = Type;
    document.MainForm.WorkDate.value = WorkDate;
    document.MainForm.PlanNo.value = PlanNo;
    document.MainForm.MacNo.value = MacNo;
    document.MainForm.KouteiNo.value = KouteiNo;
    document.MainForm.action = 'ReportEntry.php';
    document.MainForm.submit();
}
function doEdit(WorkDate,PlanNo,MacNo,KouteiNo) {
    document.MainForm.ProcCode.value = 'EDIT';
    document.MainForm.EDIT_MODE.value = 'UPDATE';
    document.MainForm.SummaryType.value = 1;
    document.MainForm.WorkDate.value = WorkDate;
    document.MainForm.PlanNo.value = PlanNo;
    document.MainForm.MacNo.value = MacNo;
    document.MainForm.KouteiNo.value = KouteiNo;
    document.MainForm.action = 'ReportEntry.php';
    document.MainForm.submit();
}
function MovePage(page) {
    var NextPage = page;
    if (page == 0) {
        var idx = document.MainForm.SelectPage.selectedIndex;
        NextPage = document.MainForm.SelectPage.options[idx].text;
    }
    
    document.MovePageForm.ViewPage.value = NextPage;
    document.MovePageForm.submit();
}
function Undecision(work_date, mac_no, plan_no, koutei) {
    var ok_ng = confirm('����μ�ä򤷤ޤ���\n\n��ž�� = ' + work_date + '\n����No. = ' + mac_no + '\n�ײ�No. = ' + plan_no + '\n����No. = ' + koutei + '\n\n�������Ǥ�����');
    if (!ok_ng) return ok_ng;
    document.CancelForm.work_date.value  = work_date;
    document.CancelForm.mac_no.value     = mac_no;
    document.CancelForm.plan_no.value    = plan_no;
    document.CancelForm.koutei.value     = koutei;
    document.CancelForm.submit();
}
</script>
</head>
<body onLoad='init()'>
<?php if ($Parameter['ProcCode'] == '') { ?>
<table border='0' class='LAYOUT' width='100%' height='100%'>
    <tr class='LAYOUT'>
        <td class='LAYOUT' align='center'>
            ��о������Ϥ��Ʋ�����
        </td>
    </tr>
</table>
<?php } else if ($EntryCheck == false) { ?>
<table border='0' class='LAYOUT' width='100%' height='100%'>
    <tr class='LAYOUT'>
        <td class='LAYOUT' align='center'>
            ��о������������Ϥ��Ʋ�����
        </td>
    </tr>
</table>
<?php } else if ($PageCtl['RowsNum'] == 0) { ?>
<table border='0' class='LAYOUT' width='100%' height='100%'>
    <tr class='LAYOUT'>
        <td class='LAYOUT' align='center'>
            �����ǡ�����¸�ߤ��ޤ���
        </td>
    </tr>
</table>
<?php } else { ?>
<form name='CancelForm' action='ReportList.php' method='post'>
<input type='hidden' name='ProcCode' value='VIEW'>
<input type='hidden' name='FromYear' value='<?php echo outHtml($Parameter['FromYear'])?>'>
<input type='hidden' name='FromMonth' value='<?php echo outHtml($Parameter['FromMonth'])?>'>
<input type='hidden' name='FromDay' value='<?php echo outHtml($Parameter['FromDay'])?>'>
<input type='hidden' name='ToYear' value='<?php echo outHtml($Parameter['ToYear'])?>'>
<input type='hidden' name='ToMonth' value='<?php echo outHtml($Parameter['ToMonth'])?>'>
<input type='hidden' name='ToDay' value='<?php echo outHtml($Parameter['ToDay'])?>'>
<input type='hidden' name='Decision' value='<?php echo outHtml($Parameter['Decision'])?>'>
<input type='hidden' name='Remark' value='<?php echo outHtml($Parameter['Remark'])?>'>
<input type='hidden' name='MacNo' value='<?php echo outHtml($Parameter['MacNo'])?>'>
<input type='hidden' name='ListNum' value='<?php echo outHtml($Parameter['ListNum'])?>'>
<input type='hidden' name='ViewPage' value='<?php echo $Parameter['ViewPage']?>'>
<input type='hidden' name='work_date' value=''>
<input type='hidden' name='mac_no' value=''>
<input type='hidden' name='plan_no' value=''>
<input type='hidden' name='koutei' value=''>
<input type='hidden' name='Undecision' value='GO'>
</form>
<form name='MovePageForm' action='ReportList.php' method='post'>
<input type='hidden' name='ProcCode' value='VIEW'>
<input type='hidden' name='FromYear' value='<?php echo outHtml($Parameter['FromYear'])?>'>
<input type='hidden' name='FromMonth' value='<?php echo outHtml($Parameter['FromMonth'])?>'>
<input type='hidden' name='FromDay' value='<?php echo outHtml($Parameter['FromDay'])?>'>
<input type='hidden' name='ToYear' value='<?php echo outHtml($Parameter['ToYear'])?>'>
<input type='hidden' name='ToMonth' value='<?php echo outHtml($Parameter['ToMonth'])?>'>
<input type='hidden' name='ToDay' value='<?php echo outHtml($Parameter['ToDay'])?>'>
<input type='hidden' name='Decision' value='<?php echo outHtml($Parameter['Decision'])?>'>
<input type='hidden' name='Remark' value='<?php echo outHtml($Parameter['Remark'])?>'>
<input type='hidden' name='MacNo' value='<?php echo outHtml($Parameter['MacNo'])?>'>
<input type='hidden' name='ListNum' value='<?php echo outHtml($Parameter['ListNum'])?>'>
<input type='hidden' name='ViewPage' value=''>
</form>
<form name='MainForm' method='post'>
<input type='hidden' name='RetUrl' value='ReportList.php?ProcCode=VIEW&FromYear=<?php echo outHtml($Parameter['FromYear'])?>&FromMonth=<?php echo outHtml($Parameter['FromMonth'])?>&FromDay=<?php echo outHtml($Parameter['FromDay'])?>&ToYear=<?php echo outHtml($Parameter['ToYear'])?>&ToMonth=<?php echo outHtml($Parameter['ToMonth'])?>&ToDay=<?php echo outHtml($Parameter['ToDay'])?>&Decision=<?php echo outHtml($Parameter['Decision'])?>&Remark=<?php echo outHtml($Parameter['Remark'])?>&MacNo=<?php echo outHtml($Parameter['MacNo'])?>&ViewPage=<?php echo $PageCtl['ViewPage']?>&ListNum=<?php echo $PageCtl['ListNum']?>'>
<input type='hidden' name='ProcCode' value=''>
<input type='hidden' name='EDIT_MODE' value=''>
<input type='hidden' name='SummaryType' value=''>
<input type='hidden' name='WorkDate' value=''>
<input type='hidden' name='PlanNo' value=''>
<input type='hidden' name='MacNo' value=''>
<input type='hidden' name='KouteiNo' value=''>
<input type='hidden' name='ErrorCheckLevel' value='2'>
<center>
        <table border="1" style="width:810px;">
            <tr>
                <td class="HED">
                </td>
                <td class="HED" nowrap>
                    ��ž��
                </td>
                <td class="HED" nowrap>
                    �ײ�No.
                </td>
                <td class="HED" nowrap>
                    ����No.
                </td>
                <td class="HED" nowrap>
                    ����̾
                </td>
                <td class="HED" nowrap>
                    ����No.
                </td>
                <td class="HED" nowrap>
                    ����̾
                </td>
                <td class="HED" nowrap>
                    ����No.
                </td>
                <td class="HED" nowrap>
                    �ײ��
                </td>
                <td class="HED" nowrap>
                    �������ʿ�
                </td>
                <td colspan="2" class="HED" >
                    �������
                </td>
            </tr>
<?php
        for ($i=$PageCtl['StartRecNum'];$i<=$PageCtl['EndRecNum'];$i++) {
            $row = pg_fetch_array ($rs,$i); 
            // ����Ѥߤ�������Խ��Ǥ��ʤ�
            $DECISION = ($row['decision_flg'] == 0) ? '' : ' disabled ';
?>
            <tr>
                <td align='center' nowrap>
                    <input type='button' value='�̾�' onClick="doView(1,'<?php echo outHtml($row['work_date'])?>','<?php echo outHtml($row['plan_no'])?>','<?php echo outHtml($row['mac_no'])?>','<?php echo outHtml($row['koutei'])?>')"><input type="button" value="����" onClick="doView(2,'<?php echo outHtml($row['work_date'])?>','<?php echo outHtml($row['plan_no'])?>','<?php echo outHtml($row['mac_no'])?>','<?php echo outHtml($row['koutei'])?>')"><?php if ($AdminUser) { ?><input type="button" value="����" onClick="doEdit('<?php echo outHtml($row['work_date'])?>','<?php echo outHtml($row['plan_no'])?>','<?php echo outHtml($row['mac_no'])?>','<?php echo outHtml($row['koutei'])?>')"<?php echo $DECISION?>><?php } ?>
                </td>
                <td align="center" nowrap>
                    <?php echo outHtml(mu_Date::toString($row['work_date'],"Y/m/d"))?>
                </td>
                <td align="center" nowrap>
                    <?php echo outHtml($row['plan_no'])?>
                </td>
                <td align="center" nowrap>
                    <?php echo outHtml($row['mac_no'])?>
                </td>
                <td width='65' align="center" nowrap>
                    <?php echo outHtml($row['mac_name'],20)?>
                </td>
                <td align="center" nowrap>
                    <?php echo outHtml($row['parts_no'])?>
                </td>
                <td width='150' nowrap>
                    <?php echo outHtml($row['midsc'], 20)?>
                </td>
                <td align="center" nowrap>
                    <?php echo outHtml($row['koutei'])?>
                </td>
                <td class="NUM" nowrap>
                    <?php echo outHtml($row['plan_cnt'])?>
                </td>
                <td class="NUM" nowrap>
                    <?php echo outHtml($row['today'])?>
                </td>
                <?php if ($row['decision_flg'] == 1) { ?>
                <td align='center'  nowrap>
                    �����
                </td>
                <td nowrap align='center'>
                    <?php if ($suUser) { ?>
                    <input type='button' value='���' onClick="Undecision(<?php echo $row['work_date']?>, <?php echo $row['mac_no']?>, '<?php echo $row['plan_no']?>', <?php echo $row['koutei']?>)">
                    <?php } else { ?>
                    <input type='button' value='���' disabled>
                    <!-- &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; -->
                    <?php } ?>
                </td>
                <? } else { ?>
                <td  nowrap>
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                </td>
                <td  nowrap>
                    ̤����
                </td>
                <? } ?>
            </tr>
    <?php } ?>
        </table>
        <br>
        <?php echo getPageControlHtml($PageCtl['ViewPage'],$PageCtl['RowsNum'],$PageCtl['ListNum']) ?>
    </center>
</form>
<?php } ?>
</body>
<?php echo $menu->out_alert_java()?>
</html>
