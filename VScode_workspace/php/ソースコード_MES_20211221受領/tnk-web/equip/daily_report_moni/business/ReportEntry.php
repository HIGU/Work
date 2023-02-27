<?php 
//////////////////////////////////////////////////////////////////////////////
// ��Ω������Ư���������ƥ�ε�����ž���� ���å��ե�����                  //
// Copyright (C) 2021-2021 norihisa_ooya@nitto-kohki.co.jp                  //
// Original by yamagishi@matehan.co.jp                                      //
// Changed history                                                          //
// 2021/03/26 Created  ReportEntry.php                                      //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);

require_once ('../com/define.php');
require_once ('../com/function.php');
require_once ('../com/mu_date.php');

ob_start('ob_gzhandler');

// ��å������Υ��ꥢ
$Message = '';
// �����ԥ⡼��
$AdminUser = AdminUser( FNC_REPORT );
$AcceptUser = AdminUser( FNC_REPORT_ACCEPT );
// ���������ɤμ���
$ProcCode = @$_REQUEST['ProcCode'];
if (!isset($_REQUEST['ProcCode'])) $ProcCode = 'EDIT';

$con = getConnection();

// �ѥ�᡼���Υ��å�
setParameter();

// --------------------------------------------------
// �����ο���ʬ��
// --------------------------------------------------

if ($ProcCode == 'EDIT') {
    // --------------------------------------------------
    // �Խ�����
    // --------------------------------------------------
    
    // ���������⡼��
    $EDIT_MODE = 'INSERT';
    // �����⡼��
    if (@$_REQUEST['EDIT_MODE'] == 'UPDATE') {
        // �ģ¤���ƤӽФ�
        ReadData();
        // ������ $Report['Type'] ���С���=B, ���Ǻ�=C ����ޤ�
    } else {
        $Report['Type'] = '';   // ���ߡ�
    }
    // �������ƤΥ����å�
    EntryDataCheck();
    // �����ϥХåե���
    $LogNum += 3;
    // Entry����ɽ��
    require_once('ReportEdit.php');
    
} else if ($ProcCode == 'WRITE') {
    // --------------------------------------------------
    // ��¸����
    // --------------------------------------------------

    // �Խ��⡼��
    $EDIT_MODE = @$_REQUEST['EDIT_MODE'];
    // �������ƤΥ����å�
    if (!EntryDataCheck()) {
        // ���顼������Τ����ϲ��̤����
        require_once('ReportEdit.php');
    } else {
        // �ǡ�������¸
        SaveData();
        // ��¸�����ǡ������ɤ߹���
        ReadData();
        // ��Ͽ��λ��å�����
        $Message = '��Ͽ���ޤ�����';
        // ɽ������
        require_once('ReportView.php');
    }
} else if ($ProcCode == 'DELETE') {
    // --------------------------------------------------
    // �������
    // --------------------------------------------------
    
    // �ģº��
    DeleteData();
    // �ƤӽФ����˥�����쥯��
    header("Location: ".@$_REQUEST['RetUrl']);
    
} else if ($ProcCode == 'VIEW') {
    // --------------------------------------------------
    // ɽ������
    // --------------------------------------------------
    
    // ��¸����Ƥ���ǡ������ɤ߹���
    ReadData();
    // ɽ������
    require_once('ReportView.php');
    
} else if ($ProcCode == 'DECISION') {
    // --------------------------------------------------
    // ����������
    // --------------------------------------------------
    
    // ��¸�����ǡ������ɤ߹���
    ReadData();
    // �������
    ExecuteDecision();
    // ���괰λ��å�����
    $Message = '������ꤷ�ޤ�����';
    // ɽ������
    require_once('ReportView.php');
    
} else {
    // --------------------------------------------------
    // �㳰����
    // --------------------------------------------------
    
    // �ɤ��ˤ���ä����餺�������ޤ��褿�饷���ƥ२�顼
    $SYSTEM_MESSAGE = "���������ɤ�����������ޤ���[$ProcCode]";
    require_once('../com/' . ERROR_PAGE);
    exit();
}
// �ѥ�᡼���Υ��å�
function setParameter()
{
    global $con,$Report,$LogNum,$CsvFlg;
    // ��Ǽ
    $Report = Array();
    $Report['SummaryType']      = trim (@$_REQUEST['SummaryType']);
    $Report['WorkDate']         = trim (@$_REQUEST['WorkDate']);
    $Report['WorkYear']         = trim (@$_REQUEST['WorkYear']);
    $Report['WorkMonth']        = trim (@$_REQUEST['WorkMonth']);
    $Report['WorkDay']          = trim (@$_REQUEST['WorkDay']);
    $Report['MacNo']            = trim (@$_REQUEST['MacNo']);
    $Report['PlanNo']           = trim (@$_REQUEST['PlanNo']);
    $Report['KouteiNo']         = trim (@$_REQUEST['KouteiNo']);
    //$Report['KouteiName']       = trim (@$_REQUEST['KouteiName']);
    $Report['Yesterday']        = trim (@$_REQUEST['Yesterday']);
    $Report['Today']            = trim (@$_REQUEST['Today']);
    $Report['Ng']               = trim (@$_REQUEST['Ng']);
    $Report['NgKbn']            = trim (@$_REQUEST['NgKbn']);
    $Report['Plan']             = trim (@$_REQUEST['Plan']);
    $Report['EndFlg']           = trim (@$_REQUEST['EndFlg']);
    $Report['NgKbn']            = trim (@$_REQUEST['NgKbn']);
    $Report['Memo']             = trim (@$_REQUEST['Memo']);
    $Report['Injection']        = trim (@$_REQUEST['Injection']);
    $Report['InjectionItem']    = trim (@$_REQUEST['InjectionItem']);
    $Report['Abandonment']      = trim (@$_REQUEST['Abandonment']);
    $Report['Type']             = trim (@$_REQUEST['Type']);
    
    $LogNum = @$_REQUEST['LogNum'];
    for ($i=0;$i<$LogNum;$i++) {
        $Report['MacState'][$i] = trim (@$_REQUEST['MacState'][$i]);
        $Report['FromDate'][$i] = trim (@$_REQUEST['FromDate'][$i]);
        $Report['FromHH'][$i]   = trim (@$_REQUEST['FromHH'][$i]);
        $Report['FromMM'][$i]   = trim (@$_REQUEST['FromMM'][$i]);
        $Report['ToDate'][$i]   = trim (@$_REQUEST['ToDate'][$i]);
        $Report['ToHH'][$i]     = trim (@$_REQUEST['ToHH'][$i]);
        $Report['ToMM'][$i]     = trim (@$_REQUEST['ToMM'][$i]);
        $Report['CutTime'][$i]  = trim (@$_REQUEST['CutTime'][$i]);
    }
    if ($Report['WorkDate'] != '') {
        // WorkDate�������Ǥ����WorkDate��ʬ�򤷤Ƴ�Ǽ����
        $Report['WorkYear']  = mu_Date::toString($Report['WorkDate'] ,'Y');
        $Report['WorkMonth'] = mu_Date::toString($Report['WorkDate'] ,'m');
        $Report['WorkDay']   = mu_Date::toString($Report['WorkDate'] ,'d');
    } else {
        // WorkDate�������Ǥ��ʤ���С���ž����������
        $Date = $Report['WorkYear'] . '/' . $Report['WorkMonth'] . '/' . $Report['WorkDay'];
        if (mu_Date::chkDate ($Date)) {
            $Report['WorkDate'] = mu_Date::toString($Date,'Ymd');
        } else {
            $Report['WorkDate'] = '';
        }
    }
    
    // --------------------------------------------------
    // ����ʤ�����ϥޥ��������������
    // --------------------------------------------------
    
    $Report['MacName'] = '';
    $CsvFlg            = 0;
    if (is_numeric($Report['MacNo'])) {
        // ����̾�μ���
        $rs = pg_query($con,"select mac_name,csv_flg from equip_machine_master2 where mac_no=" . pg_escape_string ($Report['MacNo']) );
        if ($row = pg_fetch_array ($rs)) {
            $Report['MacName'] = $row['mac_name'];
            $CsvFlg            = $row['csv_flg'];
        }
    }
    
    if (is_numeric($Report['MacNo']) && is_numeric($Report['PlanNo']) && is_numeric($Report['KouteiNo'])) {
        // �ؼ�No.���� ����No. ����̾ ���ʺ�� Ǽ�� �ؼ����̤����
        $sql = " select "
             . "    b.parts_no          as parts_no,        "
             . "    a.delivery          as delivery,        "
             . "    a.inst_qt           as inst_qt,         "
             . "    c.midsc             as midsc,           "
             . "    c.mzist             as mzist            "
             . " FROM equip_work_inst_header a "
             . " LEFT OUTER JOIN equip_work_instruction b USING(inst_no) "
             . " left outer join miitem c on c.mipn=b.parts_no "
             . " WHERE a.inst_no=" . pg_escape_string ($Report['PlanNo']) . " and b.koutei=" . pg_escape_string ($Report['KouteiNo']);

    
        $rs = pg_query($con,$sql);
        if ($row = pg_fetch_array ($rs)) {
            $Report['ItemCode']      = $row['parts_no'];
            $Report['ItemName']      = $row['midsc'];
            $Report['Mzist']         = $row['mzist'];
            //$Report['KouteiName']  = $row['KouteiName']);
            $Report['Delivery']      = $row['delivery'];
            $Report['DeliveryYYYY']  = mu_Date::toString($Report['Delivery'] ,'Y');
            $Report['DeliveryMM']    = mu_Date::toString($Report['Delivery'] ,'m');
            $Report['DeliveryDD']    = mu_Date::toString($Report['Delivery'] ,'d');
            $Report['PlanNum']       = $row['inst_qt'];
        }
    }
    
    // ��������μ����ʲ���
    $Report['KouteiName'] = '';
    if (is_numeric($Report['PlanNo']) && is_numeric($Report['KouteiNo'])) {
        $sql = 'select pro_mark from equip_work_instruction where inst_no=' . pg_escape_string ($Report['PlanNo']) . ' and koutei=' . pg_escape_string ($Report['KouteiNo']);
        $rs = pg_query($con,$sql);
        if ($row = pg_fetch_array ($rs)) {
            $Report['KouteiName'] = $row['pro_mark'];
        }
    }
    
    // ���祳��,�ξ����������
    $Report['Stop'] = $Report['Failure'] = 0;
    for ($i=0;$i<$LogNum;$i++) {
        if ( CheckCount( "Stop"     , $CsvFlg , $Report['MacState'][$i] )) $Report['Stop']++;
        if ( CheckCount( "Failure"  , $CsvFlg , $Report['MacState'][$i] )) $Report['Failure']++;
    }
    // ñ�̽��̤μ���
    $Report['AbandonmentWeight'] = 0;
    if (is_numeric($Report['Injection'])) {
        $sql = " select weight from equip_materials where mtcode='" . pg_escape_string ($Report['InjectionItem']) . "'";
        $rs = pg_query($con,$sql);
        if ($row = pg_fetch_array ($rs)) {
            $Report['AbandonmentWeight'] = $row['weight'] * $Report['Abandonment'];
        }
    }
}
// �������ƤΥ����å�
function EntryDataCheck()
{
    global $con,$Message,$Report,$LogNum;
    
    // ���Ͼ����٥�(1:�������� 2:��������)
    
    if (@$_REQUEST['ErrorCheckLevel'] == 0) {
        // ���Ͼ����٥�(1:�������� 2:��������)
        $Report['ENTRY_LEVEL'] = '1';
    }
    if (@$_REQUEST['ErrorCheckLevel'] == 1) {
        // ���Ͼ����٥�(1:�������� 2:��������)
        $Report['ENTRY_LEVEL'] = '2';
    
        // ��ž��
        // WorkDate�������Ǥ��ʤ���С���ž����������
        $Date = $Report['WorkYear'] . '/' . $Report['WorkMonth'] . '/' . $Report['WorkDay'];
        if (mu_Date::chkDate ($Date)) {
            $Report['WorkDate'] = mu_Date::toString($Date,'Ymd');
            if ($Report['WorkDate'] == '') {
                // ���Ͼ����٥룱�ʥ������ϥ�٥��
                $Report['ENTRY_LEVEL'] = '1';
                $Message .= '��ž����̤���ϤǤ���\n\n';
            }
        } else {
            $Report['WorkDate'] = '';
            // ���Ͼ����٥룱�ʥ������ϥ�٥��
            $Report['ENTRY_LEVEL'] = '1';
            $Message .= '��ž��������������ޤ���\n\n';
        }
        // ����No.
        if ($Report['MacNo'] == '') {
            // ���Ͼ����٥룱�ʥ������ϥ�٥��
            $Report['ENTRY_LEVEL'] = '1';
            $Message .= '����No.��̤���ϤǤ���\n\n';
        } else {
            // ����̾�Τ������Ǥ��Ƥ��ʤ���Хޥ�����¸�ߤ��ʤ�
            if ($Report['MacName'] == '') {
                // ���Ͼ����٥룱�ʥ������ϥ�٥��
                $Report['ENTRY_LEVEL'] = '1';
                $Message .= '����No.'.$Report['MacNo'].'�ϥޥ�������Ͽ����Ƥ��ޤ���\n\n';
            }
        }
        // �ؼ�No.
        if ($Report['PlanNo'] == '') {
            // ���Ͼ����٥룱�ʥ������ϥ�٥��
            $Report['ENTRY_LEVEL'] = '1';
            $Message .= '�ؼ�No.��̤���ϤǤ���\n\n';
        } else {
            if (!is_numeric($Report['PlanNo'])) {
                $Report['ENTRY_LEVEL'] = '1';
                $Message .= '�ؼ�No.�Ͽ��ͤ����Ϥ��Ʋ�������\n\n';
            } else {
                // ����No.�������Ǥ��Ƥ��ʤ���Хޥ�����¸�ߤ��ʤ�
                if (!isset ($Report['ItemCode']) && $Report['PlanNo'] != CUSTOM_MADE_SIJI_NO) {
                    // ���Ͼ����٥룱�ʥ������ϥ�٥��
                    $Report['ENTRY_LEVEL'] = '1';
                    $Message .= '�ؼ�No.'.$Report['PlanNo'].'�ϥޥ�������Ͽ����Ƥ��ޤ���\n\n';
                }
            }
        }
        // �����ֹ�
        if ($Report['KouteiNo'] == '') {
            // ���Ͼ����٥룱�ʥ������ϥ�٥��
            $Report['ENTRY_LEVEL'] = '1';
            $Message .= '����No.��̤���ϤǤ���\n\n';
        } else {
            if (!is_numeric ($Report['KouteiNo'])) {
                // ���Ͼ����٥룱�ʥ������ϥ�٥��
                $Report['ENTRY_LEVEL'] = '1';
                $Message .= '�����Ͽ��ͤ����Ϥ��Ʋ�����\n\n';
            }
        }
        
        // ���Ϲ��ܤ˥��顼���ʤ���С�Ʊ�����󤬤��Ǥ���Ͽ����Ƥ��ʤ��������å�
        if ($Message == '') {
            $sql = "select work_date from equip_work_report where work_date=" . pg_escape_string ($Report['WorkDate']) . " and mac_no=" . pg_escape_string ($Report['MacNo']) . " and plan_no=" . pg_escape_string ($Report['PlanNo']) . " and koutei=" . pg_escape_string ($Report['KouteiNo']);
            $rs  = pg_query ($con,$sql);
            if ($row = pg_fetch_array ($rs)) {
                // ���Ͼ����٥룱�ʥ������ϥ�٥��
                $Report['ENTRY_LEVEL'] = '1';
                $Message .= '���α�ž����Ϥ��Ǥ���Ͽ����Ƥ��ޤ���\n\n';
            }
        }
    }
    
    if (@$_REQUEST['ErrorCheckLevel'] == 2) {
        // ���Ͼ����٥�(1:�������� 2:��������)
        $Report['ENTRY_LEVEL'] = '2';
        // �������ʿ�
        if ($Report['Yesterday'] == '') {
            $Report['Yesterday'] = 0;
        } else {
            if (!is_numeric ($Report['Yesterday'])) {
                $Message .= '�������ʿ��Ͽ��ͤ����Ϥ��Ʋ�������\n\n';
            } else {
                if ($Report['Yesterday'] < 0) {
                    $Message .= '�������ʿ��ϥޥ��ʥ������ϤǤ��ޤ���\n\n';
                }
            }
        }
        // �������ʿ�
        if ($Report['Today'] == '') {
            $Report['Today'] = 0;
        } else {
            if (!is_numeric ($Report['Today'])) {
                $Message .= '�������ʿ��Ͽ��ͤ����Ϥ��Ʋ�������\n\n';
            } else {
                if ($Report['Today'] < 0) {
                    $Message .= '�������ʿ��ϥޥ��ʥ������ϤǤ��ޤ���\n\n';
                }
            }
        }
        // ���ɿ�
        if ($Report['Ng'] == '') {
            $Report['Ng'] = 0;
        } else {
            if (!is_numeric ($Report['Ng'])) {
                $Message .= '���ɿ��Ͽ��ͤ����Ϥ��Ʋ�������\n\n';
            } else {
                if ($Report['Ng'] < 0) {
                    $Message .= '���ɿ��ϥޥ��ʥ������ϤǤ��ޤ���\n\n';
                }
            }
        }
        // �ʼ��
        if ($Report['Plan'] == '') {
            $Report['Plan'] = 0;
        } else {
            if (!is_numeric ($Report['Plan'])) {
                $Message .= '�ʼ���Ͽ��ͤ����Ϥ��Ʋ�������\n\n';
            } else {
                if ($Report['Plan'] < 0) {
                    $Message .= '�ʼ���ϥޥ��ʥ������ϤǤ��ޤ���\n\n';
                }
            }
        }
        // ���������߷׿�
        
        $gokei = 0;
        $gokei = $Report['Yesterday'] + $Report['Today'];
        /*
        if ($gokei > $Report['Plan']) {
            $Message .= '���������߷׿����ؼ�����Ķ���Ƥ��ޤ���\n\n';
        }
        */
        // ��ž���Υ����å�
        for ($i=0;$i<$LogNum;$i++) {
            // �����Ԥ����Ϲ��ܿ�
            $isEntry = 0;
            if ($Report['MacState'][$i] != '')  $isEntry++;
            if ($Report['FromHH'][$i] != '')    $isEntry++;
            if ($Report['FromMM'][$i] != '')    $isEntry++;
            if ($Report['ToHH'][$i] != '')      $isEntry++;
            if ($Report['ToMM'][$i] != '')      $isEntry++;
            if ($Report['CutTime'][$i] == '')   $Report['CutTime'][$i] = 0;
            // ����Ԥ��������Ϥ���Ƥ��ʤ��ä��饨�顼 0:����� 5:�������Ϥ���Ƥ���
            if ($isEntry != 0 && $isEntry != 5) {
                $Message .= $i+1 . '���ܤα�ž��������������Ϥ���Ƥ��ޤ���\n\n';
            }
            // �������Ϥ���Ƥ����顢�ƹ��ܤΥ����å�
            if ($isEntry == 5) {
                // �����ͥ����å�
                if (!is_numeric ($Report['FromHH'][$i]) || 
                    !is_numeric ($Report['FromMM'][$i]) || 
                    !is_numeric ($Report['ToHH'][$i])   || 
                    !is_numeric ($Report['ToMM'][$i])   ||
                    !is_numeric ($Report['CutTime'][$i])) {
                    $Message .= $i+1 . '���ܤλ���Ͽ��ͤ����Ϥ��Ʋ�������\n\n';
                } else {
                    // ��������å�
                    $CheckTime = true;
                    if ($Report['FromHH'][$i] < 0 || $Report['FromHH'][$i] >= 24) $CheckTime = false;
                    if ($Report['FromMM'][$i] < 0 || $Report['FromMM'][$i] >= 60) $CheckTime = false;
                    if ($Report['ToHH'][$i]   < 0 || $Report['ToHH'][$i]   >= 24) $CheckTime = false;
                    if ($Report['ToMM'][$i]   < 0 || $Report['ToMM'][$i]   >= 60) $CheckTime = false;
                    if ($CheckTime == false) {
                        $Message .= $i+1 . '���ܤλ��郎����������ޤ���\n\n';
                    }
                    // ���åȻ��֤Υ����å�
                    if ($Report['CutTime'][$i] < 0) {
                        $Message .= $i+1 . '���åȻ��֤ϥޥ��ʥ����ϤϤǤ��ޤ���\n\n';
                    }
                }
                // ��Ȼ��֤Υޥ��ʥ������å�
                if (!isset($Report['FromTime'][$i])) {
                    $Report['FromTime'][$i] = sprintf('%02d%02d',$Report['FromHH'][$i],$Report['FromMM'][$i]);
                    $Report['ToTime'][$i]   = sprintf('%02d%02d',$Report['ToHH'][$i]  ,$Report['ToMM'][$i]);
                }
                if ( (CalWorkTime($Report['FromDate'][$i], $Report['FromTime'][$i], $Report['ToDate'][$i], $Report['ToTime'][$i]) - $Report['CutTime'][$i]) < 1) {
                    $Message .= $i+1 . '���ܤα�ž��������������꤬�ְ�äƤ��ޤ���\n\n';
                }
            }
        }
        // ����������
        if ($Report['InjectionItem'] == '' && ($Report['Injection'] != '' && $Report['Injection'] != '0')) {
            $Message .= '�������������ɤ����Ϥ��Ʋ�������\n\n';
        }
        if ($Report['InjectionItem'] != '') {
            // �ޥ���¸�ߥ����å�
            $rs = pg_query ($con,"select mtcode,length from equip_materials where mtcode='" . pg_escape_string ($Report['InjectionItem']) . "'");
            if (!$row = pg_fetch_array ($rs)) {
                $Message .= '����������['.$Report['InjectionItem'].']�ϥޥ�������Ͽ����Ƥ��ޤ���\n\n';
            }
        }
        // ������
        if ($Report['Injection'] == '') {
            $Report['Injection'] = 0;
        }
        if ($Report['InjectionItem'] != '') {
            if ($Report['Injection'] == '') {
                $Message .= '�����������Ϥ��Ʋ�������\n\n';
            } else {
                if (!is_numeric ($Report['Injection'])) {
                    $Message .= '�������Ͽ��ͤ����Ϥ��Ʋ�������\n\n';
                } else {
                    if ($Report['Injection'] < 0) {
                        $Message .= '�������ϥޥ��ʥ������ϤǤ��ޤ���\n\n';
                    }
                }
            }
        }
        // ü��Ĺ��ü
        if ($Report['Abandonment'] == '') {
            $Report['Abandonment'] = 0;
        }
        if ($Report['Abandonment'] != '') {
            if ($Report['Abandonment'] == '') {
                $Message .= '����ü��Ĺ�������Ϥ��Ʋ�������\n\n';
            } else {
                if (!is_numeric ($Report['Abandonment'])) {
                    $Message .= '����ü��Ĺ���Ͽ��ͤ����Ϥ��Ʋ�������\n\n';
                } else {
                    if ($Report['Abandonment'] < 0) {
                        $Message .= '����ü��Ĺ���ϥޥ��ʥ������ϤǤ��ޤ���\n\n';
                    } else {
                        // �����ޥ����ɤ�ʤ����ϥ����å��Ǥ��ʤ�
                        if (isset($row)) {
                            if ($row["length"]  <= $Report['Abandonment']) {
                                $Message .= '����ü��Ĺ����������ɸ��Ĺ����Ķ���Ƥ��ޤ�\n\n';
                            }
                        }
                    }
                }
            }
        }
    }
    
    if ($Message == '') return true;
    else                return false;
}
function SaveData()
{
    global $con,$Report,$LogNum;
    // �ȥ�󥶥�����󳫻�
    pg_query ($con , "BEGIN");
    
    if (@$_REQUEST['EDIT_MODE'] == 'UPDATE') {
        // ����إå����
        $sql = "delete from equip_work_report_moni where work_date=" . pg_escape_string ($Report['WorkDate']) . " and mac_no=" . pg_escape_string ($Report['MacNo']) . " and plan_no='" . pg_escape_string ($Report['PlanNo']) . "' and koutei=" . pg_escape_string ($Report['KouteiNo']);
        if (!pg_query ($con , $sql)) {
            pg_query ($con , 'ROLLBACK');
            $SYSTEM_MESSAGE = "�ǡ����١����ι����˼��Ԥ��ޤ���\n$sql";
            require_once ('../com/' . ERROR_PAGE);
            exit();
        }
        // ��ž�����
        $sql = "delete from equip_work_report_moni_log where work_date=" . pg_escape_string ($Report['WorkDate']) . " and mac_no=" . pg_escape_string ($Report['MacNo']) . " and plan_no='" . pg_escape_string ($Report['PlanNo']) . "' and koutei=" . pg_escape_string ($Report['KouteiNo']);
        if (!pg_query ($con , $sql)) {
            pg_query ($con , 'ROLLBACK');
            $SYSTEM_MESSAGE = "�ǡ����١����ι����˼��Ԥ��ޤ���\n$sql";
            require_once ('../com/' . ERROR_PAGE);
            exit();
        }
    }
     
    // ����إå�����
    $sql = "insert into equip_work_report_moni (work_date,mac_no,plan_no,koutei,yesterday,today,end_flg,ng,ng_kbn,plan,memo,injection_item,injection,abandonment,decision_flg,last_user) values ( "
         .    pg_escape_string ($Report['WorkDate'])        ." ,"
         .    pg_escape_string ($Report['MacNo'])           ." ,'"
         .    pg_escape_string ($Report['PlanNo'])          ."' ,"
         .    pg_escape_string ($Report['KouteiNo'])        ." ,"
         .    pg_escape_string ($Report['Yesterday'])       ." ,"
         .    pg_escape_string ($Report['Today'])           ." ,"
         ."'".pg_escape_string ($Report['EndFlg'])          ."',"
         .    pg_escape_string ($Report['Ng'])              ." ,"
         ."'".pg_escape_string ($Report['NgKbn'])           ."',"
         .    pg_escape_string ($Report['Plan'])            ." ,"
         ."'".pg_escape_string ($Report['Memo'])            ."',"
         ."'".pg_escape_string ($Report['InjectionItem'])   ."',"
         .    pg_escape_string ($Report['Injection'])       ." ,"
         .    pg_escape_string ($Report['Abandonment'])     ." ,"
         .    0                                             ." ,"          // ����ե饰
         ."'".pg_escape_string ($_SESSION['User_ID'])       ." ')";
    
    if (!pg_query ($con , $sql)) {
        pg_query ($con , 'ROLLBACK');
        $SYSTEM_MESSAGE = "�ǡ����١����ι����˼��Ԥ��ޤ���\n$sql";
        require_once ('../com/' . ERROR_PAGE);
        exit();
    }

    $sql    = "insert into equip_work_report_moni_log(work_date,mac_no,plan_no,koutei,mac_state,from_date,from_time,to_date,to_time,cut_time,last_user) ";
    for ($i=0;$i<$LogNum;$i++) {
        // �ǡ������ʤ��쥳���ɤ�̵��
        if ($Report['MacState'][$i] == '' ||
            $Report['FromHH'][$i]   == '' || $Report['FromMM'][$i]   == '' ||
            $Report['ToHH'][$i]     == '' || $Report['ToMM'][$i]     == '' ) {
            continue;
        }
        // �����η׻�
        $Report['FromTime'][$i] = sprintf('%02d%02d',$Report['FromHH'][$i],$Report['FromMM'][$i]);
        $Report['ToTime'][$i]   = sprintf('%02d%02d',$Report['ToHH'][$i]  ,$Report['ToMM'][$i]);
        // values�������
        $values = " values ( "
             .      pg_escape_string ($Report['WorkDate'])             . " ,"
             .      pg_escape_string ($Report['MacNo'])                . " ,'"
             .      pg_escape_string ($Report['PlanNo'])               . "' ,"
             .      pg_escape_string ($Report['KouteiNo'])             . " ,"
             . "'" .pg_escape_string ($Report['MacState'][$i])         . "',"
             .      pg_escape_string ($Report['FromDate'][$i])         . " ,"
             .      pg_escape_string ($Report['FromTime'][$i])         . " ,"
             .      pg_escape_string ($Report['ToDate'][$i])           . " ,"
             .      pg_escape_string ($Report['ToTime'][$i])           . " ,"
             .      pg_escape_string ($Report['CutTime'][$i])          . " ,"
             . "'" .pg_escape_string ($_SESSION['User_ID'])            . " ')";
        if (!pg_query ($con , $sql.$values)) {
            pg_query ($con , 'ROLLBACK');
            $SYSTEM_MESSAGE = "�ǡ����١����ι����˼��Ԥ��ޤ���\n$sql";
            require_once ('../com/' . ERROR_PAGE);
            exit();
        }
    }
    
    pg_query ($con , 'COMMIT');
   
}
// --------------------------------------------------
// ����ǡ������ɤ߹���
// --------------------------------------------------
function ReadData()
{
    global $con,$Report,$LogNum,$CsvFlg,$ProcCode;
    
    
    // --------------------------------------------------
    // ����إå��ɤ߹���
    // --------------------------------------------------
    $sql = "select "
         . "    a.work_date         as work_date ,      "
         . "    a.mac_no            as mac_no ,         "
         . "    a.plan_no           as plan_no ,        "
         . "    a.koutei            as koutei,          "
         . "    a.yesterday         as yesterday,       "
         . "    a.today             as today,           "
         . "    a.ng                as ng,              "
         . "    a.plan              as plan,            "
         . "    a.ng_kbn            as ng_kbn,          "
         . "    a.end_flg           as end_flg,         "
         . "    a.memo              as memo,            "
         . "    a.injection_item    as injection_item,  "
         . "    m.type              as type,            "
         . "    m.length            as length,          "
         . "    m.weight            as weight,          "
         . "    a.injection         as injection,       "
         . "    a.abandonment       as abandonment,     "
         . "    a.decision_flg      as decision_flg,    "
         . "    b.mac_name          as mac_name ,       "
         . "    b.csv_flg           as csv_flg ,        "
         . "    c.kanryou           as delivery,        "
         . "    c.plan              as inst_qt,         "
         . "    c.parts_no          as parts_no,        "
         . "    c.plan - c.cut_plan              as plan_cnt,        "
         . "    e.midsc             as midsc,           "
         . "    e.mzist             as mzist            "
         . "from equip_work_report_moni a "
         . "left outer join equip_materials m on a.injection_item=m.mtcode "
         . "left outer join equip_machine_master2 b on a.mac_no=b.mac_no "
         . "left outer join assembly_schedule c on a.plan_no=c.plan_no "
         . "left outer join equip_work_log2_header_moni d on a.mac_no=d.mac_no and a.plan_no=d.plan_no and a.koutei=d.koutei " 
         . "left outer join miitem e on c.parts_no=e.mipn "





         . "where work_date=".pg_escape_string ($Report['WorkDate'])." and a.mac_no=".pg_escape_string ($Report['MacNo'])." and a.plan_no='".pg_escape_string ($Report['PlanNo'])."' and a.koutei=".pg_escape_string ($Report['KouteiNo']);

    $rs = pg_query ($con , $sql);
    
    if ($row = pg_fetch_array ($rs)) {

        $Report['WorkDate']      = trim ( $row['work_date'] );
        $Report['MacNo']         = trim ( $row['mac_no'] );
        $Report['MacName']       = trim ( $row['mac_name'] );
        $Report['PlanNo']        = trim ( $row['plan_no'] );
        $Report['ItemCode']      = trim ( $row['parts_no'] );
        $Report['ItemName']      = trim ( $row['midsc'] );
        $Report['Mzist']         = trim ( $row['mzist'] );
        $Report['KouteiNo']      = trim ( $row['koutei'] );
        //$Report['KouteiName']  = trim ( $row['KouteiName']) );
        $Report['Delivery']      = trim ( $row['delivery'] );
        $Report['DeliveryYYYY']  = trim ( mu_Date::toString($Report['Delivery'] ,'Y') );
        $Report['DeliveryMM']    = trim ( mu_Date::toString($Report['Delivery'] ,'m') );
        $Report['DeliveryDD']    = trim ( mu_Date::toString($Report['Delivery'] ,'d') );
        $Report['PlanNum']       = trim ( $row['plan_cnt'] );
        $Report['Yesterday']     = trim ( $row['yesterday'] );
        $Report['Today']         = trim ( $row['today'] );
        $Report['EndFlg']        = trim ( $row['end_flg'] );
        $Report['Ng']            = $row['ng'];
        $Report['NgKbn']         = trim ( $row['ng_kbn'] );
        $Report['Plan']          = $row['plan'];
        $Report['InjectionItem'] = trim ( $row['injection_item'] );
        $Report['Type']          = trim ( $row['type'] );
        $Report['Length']        = trim ( $row['length'] );
        if ($row['type'] == 'B') {
            $Report['inWeight'] = trim ( round($row['length'] * $row['weight'] * $row['injection'], 2) );
        }
        $Report['Injection']     = trim ( $row['injection'] );
        $query = "SELECT sum(injection) AS sum_injection FROM equip_work_report_moni WHERE work_date<={$row['work_date']} AND mac_no={$row['mac_no']} AND plan_no='{$row['plan_no']}' AND koutei={$row['koutei']}";
        $res = pg_query($con , $query);
        if ($sumRow = pg_fetch_array ($res)) {
            $Report['SUMinjection'] = $sumRow['sum_injection'];
            if ($row['type'] == 'B') {
                $Report['SUMinWeight'] = trim ( round($row['length'] * $row['weight'] * $sumRow['sum_injection'], 2) );
            }
        }
        $Report['Abandonment']   = trim ( $row['abandonment']);
        $Report['Memo']          = trim ( $row['memo'] );
        $Report['DecisionFlg']   = trim ( $row['decision_flg'] );
        
        $CsvFlg = $row['csv_flg'];
        
    } else {
        $SYSTEM_MESSAGE = "�ǡ����μ����˼��Ԥ��ޤ�����\n$sql";
        require_once ('../com/'.ERROR_PAGE);
        exit();
    }
    
    // ñ�̽��̤μ���
    $Report['AbandonmentWeight'] = 0;
    if ($Report['Injection'] != '') {
        $sql = " select weight from equip_materials where mtcode='" . pg_escape_string ($Report['InjectionItem']) . "'";
        $rs = pg_query($con,$sql);
        if ($row = pg_fetch_array ($rs)) {
            $Report['AbandonmentWeight'] = $row['weight'] * $Report['Abandonment'];
        }
    }
    
    // --------------------------------------------------
    // ����ž���ɤ߹���
    // --------------------------------------------------
    $sql = " select work_date,mac_no,plan_no,koutei,mac_state,from_date,from_time,to_date,to_time,cut_time from equip_work_report_moni_log "
         . " where work_date=".pg_escape_string ($Report['WorkDate'])." and mac_no=".pg_escape_string ($Report['MacNo'])." and plan_no='".pg_escape_string ($Report['PlanNo'])."' and koutei=".pg_escape_string ($Report['KouteiNo'])
         . " order by from_date,from_time,to_date,to_time ";
    $rs = pg_query ($con , $sql);

    $Report['Stop']          = 0;   // ���祳���
    $Report['Failure']       = 0;   // �ξ���
    // �̾�⡼��
    if (@$_REQUEST['SummaryType'] == 1) {
        for ($i=0,$LogNum=0;;$i++,$LogNum++) {
            if (!$row = pg_fetch_array ($rs)) break;
            $Report['MacState'][$i]     = $row['mac_state'];
            $Report['MacStateName'][$i] = getMachineStateName($CsvFlg,$row['mac_state']);
            $Report['FromDate'][$i]     = $row['from_date'];
            $Report['FromTime'][$i]     = sprintf('%04d',$row['from_time']);
            $Report['FromHH'][$i]       = sprintf('%02d',(int)($row['from_time'] / 100));
            $Report['FromMM'][$i]       = sprintf('%02d',(int)($row['from_time'] - $Report['FromHH'][$i] * 100));
            $Report['ToDate'][$i]     = $row['to_date'];
            $Report['ToTime'][$i]       = sprintf('%04d',$row['to_time']);
            $Report['ToHH'][$i]         = sprintf('%02d',(int)($row['to_time'] / 100));
            $Report['ToMM'][$i]         = sprintf('%02d',(int)($row['to_time'] - $Report['ToHH'][$i] * 100));
            $Report['CutTime'][$i]      = $row['cut_time'];
            if ( CheckCount( "Stop"     , $CsvFlg , $Report['MacState'][$i] )) $Report['Stop']++;
            if ( CheckCount( "Failure"  , $CsvFlg , $Report['MacState'][$i] )) $Report['Failure']++;
        }
    } else {
        // ���ץ⡼��
        $MaxRec = 0;
        for ($i=0;;$i++) {
            if (!$row = pg_fetch_array ($rs)) break;
            // ���쥳�����ܤ򳫻ϻ���Ȥ���
            if ($i == 0) {
                $StartDate = $row['from_date'];
                $StartTime = $row['from_time'];
            }
            for($k=0;$k<$MaxRec;$k++) {
                // ���Ǥ�Ʊ�����ơ����������ɤ����ä����Ͻ���
                if ($Report['MacState'][$k] == $row['mac_state']) {
                    $Report['WorkTime'][$k] += CalWorkTime($row['from_date'],$row['from_time'],$row['to_date'],$row['to_time']);
                    $Report['CutTime'][$k]  += $row['cut_time'];
                    break;
                }
            }
            // Ʊ�����ơ����������ɤ�¸�ߤ��ʤ��Τǿ����쥳���ɺ���
            if ($k >= $MaxRec) {
                $Report['MacState'][$MaxRec]        = $row['mac_state'];
                $Report['MacStateName'][$MaxRec]    = getMachineStateName($CsvFlg,$row['mac_state']);
                $Report['WorkTime'][$k]             = CalWorkTime($row['from_date'],$row['from_time'],$row['to_date'],$row['to_time']);
                $Report['CutTime'][$k]              = $row['cut_time'];
                $MaxRec++;
            }
            if ( CheckCount( "Stop"     , $CsvFlg , $row['mac_state'] )) $Report['Stop']++;
            if ( CheckCount( "Failure"  , $CsvFlg , $row['mac_state'] )) $Report['Failure']++;
        }
        // ���֤ν��׽���ä��顢���ϻ����λ�����׻����ƥ��å�
        for ($i=0;$i<$MaxRec;$i++) {
            if ($i ==0) {
                $Report['FromDate'][$i] = $StartDate;
                $Report['FromTime'][$i] = $StartTime;
            } else {
                $Report['FromDate'][$i] = $Report['ToDate'][$i-1];
                $Report['FromTime'][$i] = $Report['ToTime'][$i-1];
            }
            $Report['ToDate'][$i]   = CalAddDate($Report['FromDate'][$i] , $Report['FromTime'][$i] , $Report['WorkTime'][$i]);
            $Report['ToTime'][$i]   = CalAddTime($Report['FromTime'][$i] , $Report['WorkTime'][$i]);
            // ʬ��
            $Report['FromHH'][$i]       = sprintf('%02d',(int)($Report['FromTime'][$i] / 100));
            $Report['FromMM'][$i]       = sprintf('%02d',(int)($Report['FromTime'][$i] - $Report['FromHH'][$i] * 100));
            $Report['ToHH'][$i]         = sprintf('%02d',(int)($Report['ToTime'][$i] / 100));
            $Report['ToMM'][$i]         = sprintf('%02d',(int)($Report['ToTime'][$i] - $Report['ToHH'][$i] * 100));
            
        }
        // ɽ���������å�
        $LogNum = $MaxRec;
    }
}
// --------------------------------------------------
// ����������
// --------------------------------------------------
function DeleteData()
{
    global $con,$Report;
    
    // �ȥ�󥶥�����󳫻�
    pg_query ($con , 'BEGIN');
    
    // --------------------------------------------------
    // ����إå��κ��
    // --------------------------------------------------
    $sql = "delete from equip_work_report_moni where work_date=".pg_escape_string ($Report['WorkDate'])." and mac_no=".pg_escape_string ($Report['MacNo'])." and plan_no='".pg_escape_string ($Report['PlanNo'])."' and koutei=".pg_escape_string ($Report['KouteiNo']);
    if (!pg_query ($con , $sql)) {
        pg_query ($con , 'ROLLBACK');
        $SYSTEM_MESSAGE = "�ǡ����١����ι����˼��Ԥ��ޤ���\n$sql";
        require_once ('../com/' . ERROR_PAGE);
        exit();
    }
    // --------------------------------------------------
    // ����ž�����
    // --------------------------------------------------
    $sql = "delete from equip_work_report_moni_log where work_date=".pg_escape_string ($Report['WorkDate'])." and mac_no=".pg_escape_string ($Report['MacNo'])." and plan_no='".pg_escape_string ($Report['PlanNo'])."' and koutei=".pg_escape_string ($Report['KouteiNo']);
    if (!pg_query ($con , $sql)) {
        pg_query ($con , 'ROLLBACK');
        $SYSTEM_MESSAGE = "�ǡ����١����ι����˼��Ԥ��ޤ���\n$sql";
        require_once ('../com/' . ERROR_PAGE);
        exit();
    }
    
    // ���ߥå�
    pg_query ($con , 'COMMIT');
}
// --------------------------------------------------
// ����������
// --------------------------------------------------
function ExecuteDecision()
{
    global $con,$Report,$CsvFlg,$LogNum;
    
    // ��������
    $Year  = date('Y', time()); 
    $Month = date('m', time()); 
    $Day   = date('d', time()); 
    $ProcessingDate = date('Y', time()) . date('m', time()) . date('d', time());
    // �ȥ�󥶥�����󳫻�
    pg_query ($con,'BEGIN');
    
    // --------------------------------------------------
    // ������ž����˳���ե饰���å�
    // --------------------------------------------------
    $sql = " update equip_work_report_moni "
         . "        set decision_flg=1 "
         . " where  work_date=" . pg_escape_string ($Report['WorkDate']) . " and "
         . "        mac_no   =" . pg_escape_string ($Report['MacNo'])    . " and "
         . "        plan_no  ='" . pg_escape_string ($Report['PlanNo'])   . "' and "
         . "        koutei   =" . pg_escape_string ($Report['KouteiNo']);
    
    if (!pg_query ($con,$sql)) {
        pg_query ($con , 'ROLLBACK');
        $SYSTEM_MESSAGE = "�ǡ����١����ι����˼��Ԥ��ޤ���\n$sql";
        require_once ('../com/' . ERROR_PAGE);
        exit();
    }
    
    // �����
    $Summary['stop_time']    = 0;
    $Summary['stop_count']   = 0;
    $Summary['idling_time']  = 0;
    $Summary['plan_time']    = 0;
    $Summary['running_time'] = 0;   // �ܲ�Ư����(��ư��̵��)2007/03/28 ADD
    $Summary['plan_count']   = 0;
    $Summary['repair_time']  = 0;
    $Summary['repair_count'] = 0;
    $Summary['edge_time']    = 0;
    $Summary['auto_time']    = 0;
    $Summary['others_time']  = 0;
    $Summary['plan_num']     = 0;
    for ($i=0;$i<$LogNum;$i++) {
        
        // ��ȶ�ʬ���Ѵ�
        $CMacState = ChangeMacState($Report['MacNo'],$Report['MacState'][$i]);
        
        // AS/400�λ���ɽ����Ĵ��   8:30 �� 32��30(��������8:30)
        if ($Report['FromTime'][$i] < 830) {
            $Report['FromTimeAS'][$i] = $Report['FromTime'][$i] + 2400;
        } else {
            $Report['FromTimeAS'][$i] = $Report['FromTime'][$i];
        }
        if ($Report['ToTime'][$i] <= 830) {
            $Report['ToTimeAS'][$i] = $Report['ToTime'][$i] + 2400;
        } else {
            $Report['ToTimeAS'][$i] = $Report['ToTime'][$i];
        }
        
        // -------------------------------------------------------
        // ���� �Ƴ��� �б��Τ��� ���Τߵ�ǡ������� ���� ���
        // -------------------------------------------------------
        /*
        if ($i == 0) {  // ���Τ�
            $sql = ' DELETE FROM equip_upload '
                 . ' WHERE  work_date=' . pg_escape_string ($Report['WorkDate']) . ' and '
                 . '        mac_no   =' . pg_escape_string ($Report['MacNo'])    . ' and '
                 . '        plan_no  =' . pg_escape_string ($Report['PlanNo'])   . ' and '
                 . '        koutei   =' . pg_escape_string ($Report['KouteiNo']);
            
            if (!pg_query($con, $sql)) {
                pg_query($con , 'ROLLBACK');
                $SYSTEM_MESSAGE = "equip_upload�ε�ǡ�����������ǥ��顼��ȯ�����ޤ�����\n$sql";
                require_once ('../com/' . ERROR_PAGE);
                exit();
            }
        }
        */
        // --------------------------------------------------
        // equip_upload ���åץ��ɥǡ������
        // --------------------------------------------------
        /*
        $sql = ' insert into equip_upload '
             . ' values ('
             . pg_escape_string ($Report['WorkDate'])      . ','
             . pg_escape_string ($Report['MacNo'])         . ','
             . pg_escape_string ($Report['PlanNo'])        . ','
             . pg_escape_string ($Report['KouteiNo'])      . ','
             . pg_escape_string ($Report['FromTimeAS'][$i])  . ','
             . pg_escape_string ($Report['ToTimeAS'][$i])    . ','
             . pg_escape_string ($Report['CutTime'][$i])   . ','
        ."'" . pg_escape_string ($CMacState)  . "'            )";
        if (!pg_query ($con,$sql)) {
            pg_query ($con , 'ROLLBACK');
            $SYSTEM_MESSAGE = "�ǡ����١����ι����˼��Ԥ��ޤ���\n$sql";
            require_once ('../com/' . ERROR_PAGE);
            exit();
        }
        */
        // --------------------------------------------------
        // ���ޥ꡼�Ѥν���
        // --------------------------------------------------
        if ($CsvFlg == 1) {
            switch ($Report['MacState'][$i]) {
                case '3':
                    // ���祳��[�����]
                    $Summary['stop_time'] += CalWorkTime($Report['FromDate'][$i],$Report['FromTime'][$i],$Report['ToDate'][$i],$Report['ToTime'][$i]);
                    $Summary['stop_count']++;
                    break;
                case '10':
                    // �����ɥ�󥰻���[�ȵ���]
                    $Summary['idling_time'] += CalWorkTime($Report['FromDate'][$i],$Report['FromTime'][$i],$Report['ToDate'][$i],$Report['ToTime'][$i]);
                    break;
                case '11':
                    // �ʼ����[�ʼ���]
                    $Summary['plan_time'] += CalWorkTime($Report['FromDate'][$i],$Report['FromTime'][$i],$Report['ToDate'][$i],$Report['ToTime'][$i]);
                    $Summary['plan_count']++;
                    break;
                case '12':
                    // �ξ㽤��[�ξ㽤��]
                    $Summary['repair_time'] += CalWorkTime($Report['FromDate'][$i],$Report['FromTime'][$i],$Report['ToDate'][$i],$Report['ToTime'][$i]);
                    $Summary['repair_count']++;
                    break;
                case '13':
                    // �϶��[�϶��]
                    $Summary['edge_time'] += CalWorkTime($Report['FromDate'][$i],$Report['FromTime'][$i],$Report['ToDate'][$i],$Report['ToTime'][$i]);
                    break;
                case '14':
                    // ̵�Ͳ�Ư����[̵�ͱ�ž]
                    $Summary['auto_time'] += CalWorkTime($Report['FromDate'][$i],$Report['FromTime'][$i],$Report['ToDate'][$i],$Report['ToTime'][$i]);
                case '1':
                    // ��ư��ž
                    // �ʲ���running_time �� ��ư��̵�� ���ܲ�Ư����
                    $Summary['running_time'] += CalWorkTime($Report['FromDate'][$i],$Report['FromTime'][$i],$Report['ToDate'][$i],$Report['ToTime'][$i]);
                    break;
                default :
                    // ����¾���� (�Ÿ�OFF��)
                    $Summary['others_time'] += CalWorkTime($Report['FromDate'][$i],$Report['FromTime'][$i],$Report['ToDate'][$i],$Report['ToTime'][$i]);
                    break;
            }
        } else {
            switch ($Report['MacState'][$i]) {
                case '3':
                    // ���祳��[�����]
                    $Summary['stop_time'] += CalWorkTime($Report['FromDate'][$i],$Report['FromTime'][$i],$Report['ToDate'][$i],$Report['ToTime'][$i]);
                    $Summary['stop_count']++;
                    break;
                case '4':
                    // �����ɥ�󥰻���[�ȵ���]
                    $Summary['idling_time'] += CalWorkTime($Report['FromDate'][$i],$Report['FromTime'][$i],$Report['ToDate'][$i],$Report['ToTime'][$i]);
                    break;
                case '5':
                    // �ʼ����[�ʼ���]
                    $Summary['plan_time'] += CalWorkTime($Report['FromDate'][$i],$Report['FromTime'][$i],$Report['ToDate'][$i],$Report['ToTime'][$i]);
                    $Summary['plan_count']++;
                    break;
                case '6':
                    // �ξ㽤��[�ξ㽤��]
                    $Summary['repair_time'] += CalWorkTime($Report['FromDate'][$i],$Report['FromTime'][$i],$Report['ToDate'][$i],$Report['ToTime'][$i]);
                    $Summary['repair_count']++;
                    break;
                case '7':
                    // �϶��[�϶��]
                    $Summary['edge_time'] += CalWorkTime($Report['FromDate'][$i],$Report['FromTime'][$i],$Report['ToDate'][$i],$Report['ToTime'][$i]);
                    break;
                case '8':
                    // ̵�Ͳ�Ư����[̵�ͱ�ž]
                    $Summary['auto_time'] += CalWorkTime($Report['FromDate'][$i],$Report['FromTime'][$i],$Report['ToDate'][$i],$Report['ToTime'][$i]);
                case '1':
                    // ��ư��ž
                    // �ʲ���running_time �� ��ư��̵�� ���ܲ�Ư����
                    $Summary['running_time'] += CalWorkTime($Report['FromDate'][$i],$Report['FromTime'][$i],$Report['ToDate'][$i],$Report['ToTime'][$i]);
                    break;
                default :
                    // ����¾���� (�Ÿ�OFF��)
                    $Summary['others_time'] += CalWorkTime($Report['FromDate'][$i],$Report['FromTime'][$i],$Report['ToDate'][$i],$Report['ToTime'][$i]);
                    break;
            }
        }
    }
    // -------------------------------------------------------
    // ���� �Ƴ��� �б��Τ��� ��ǡ������� ���� ���
    // -------------------------------------------------------
    /*
    $sql = ' DELETE FROM equip_upload_summary '
         . ' WHERE  work_date=' . pg_escape_string ($Report['WorkDate']) . ' and '
         . '        mac_no   =' . pg_escape_string ($Report['MacNo'])    . ' and '
         . '        plan_no  =' . pg_escape_string ($Report['PlanNo'])   . ' and '
         . '        koutei   =' . pg_escape_string ($Report['KouteiNo']);
    
    if (!pg_query($con, $sql)) {
        pg_query($con , 'ROLLBACK');
        $SYSTEM_MESSAGE = "equip_upload_summary�ε�ǡ�����������ǥ��顼��ȯ�����ޤ�����\n$sql";
        require_once ('../com/' . ERROR_PAGE);
        exit();
    }
    */
    // ---------------------------------------------------
    // equip_upload_summary ���åץ��ɥ��ޥ꡼�ǡ������
    // ---------------------------------------------------
    // ���������Υ����å�
    if ($Report['Type'] == 'B') {
        $Report['injectionAS'] = $Report['inWeight'];           // �С���
    } else {
        $Report['injectionAS'] = $Report['Injection'] . '.00';  // ���Ǻ� AS¦�� numeric(8, 2)
    }
    /*
    $sql = ' insert into equip_upload_summary values('
         . (int)$Report['WorkDate']                                 . ','
         . (int)$Report['MacNo']                                    . ','
         . (int)$Report['PlanNo']                                   . ','
         . (int)$Report['KouteiNo']                                 . ','
         . "'" .pg_escape_string ($Report['ItemCode'])              ."',"
         . (int)$Summary['plan_time']                               . ','
         . (int)$Summary['running_time']                            . ','   // 2007/03/28 ADD(��ư��̵��)
         . (int)$Summary['repair_time']                             . ','
         . (int)$Summary['edge_time']                               . ','
         . (int)$Summary['stop_time']                               . ','
         . (int)$Summary['idling_time']                             . ','
         . (int)$Summary['auto_time']                               . ','   // ��ư��ž�Τ�
         . (int)$Summary['others_time']                             . ','
         . (int)$Report['Today']                                    . ','
         . (int)$Report['Ng']                                       . ','
         . (int)$Report['Plan']                                     . ','
         . "'" .pg_escape_string ($Report['EndFlg'])                ."',"
         . "'" .pg_escape_string ($Report['NgKbn'])                 ."',"
         . (int)$Summary['stop_count']                              . ','
         . (int)$Summary['plan_count']                              . ','
         . (int)$Summary['repair_count']                            . ','
         . "'" .pg_escape_string($Report['InjectionItem'])          ."',"   // 2007/03/28 ADD ��������������
         .      $Report['injectionAS']                              . ','   // 2007/03/28 ADD ��������(�������ϸĿ�)
         . (int)$ProcessingDate                                     . ')';
    if (!pg_query($con,$sql)) {
        MLog($sql);
        pg_query ($con , 'ROLLBACK');
        $SYSTEM_MESSAGE = "�ǡ����١����ι����˼��Ԥ��ޤ���\n$sql".var_dump($Report);
        require_once ('../com/' . ERROR_PAGE);
        exit();
    }
    */
    // ���ߥå�
    pg_query($con,'COMMIT');
    
    // ����ե饰���åȡʲ���ɽ������
    $Report['DecisionFlg'] = 1;
}
function CheckCount($Type,$CsvFlg,$MacState)
{
    if ($CsvFlg == 1) {
        if ($Type == 'Stop'     && $MacState ==  3) return true;
        if ($Type == 'Failure'  && $MacState == 12) return true;
    } else {
        if ($Type == 'Stop'     && $MacState ==  3) return true;
        if ($Type == 'Failure'  && $MacState ==  6) return true;
    }
    
    return false;
}
function CalAddDate($Date,$Time,$AddMinutes)
{
    // ����ʬ��
    $Hour       = (int)($Time/100);
    $Minutes    = (int)(($Time - $Hour * 100));
    // ʬ���Ѵ�
    $TimeSeconds = $Hour * 60 + $Minutes;
    // ʬ��û�
    $TimeSeconds += $AddMinutes;
    
    // ������Ķ�����飰������
    if ($TimeSeconds > 1440) {
        $RetVal = mu_Date::addDay($Date,1);
    } else {
        $RetVal = $Date;
    }
    
    return $RetVal;
}
function CalAddTime($Time,$AddMinutes)
{
    // ����ʬ��
    $Hour       = (int)($Time/100);
    $Minutes    = (int)(($Time - $Hour * 100));
    // ʬ���Ѵ�
    $TimeSeconds = $Hour * 60 + $Minutes;
    // ʬ��û�
    $TimeSeconds += $AddMinutes;
    
    // ������Ķ�����飰������
    if ($TimeSeconds > 1440) $TimeSeconds -= 1440;
    
    // ����ʬ��
    $Hour       = (int)($TimeSeconds / 60);
    $Minutes    = (int)(($TimeSeconds - $Hour * 60));
    
    $retVal = sprintf ("%02d%02d",$Hour,$Minutes);
    
    return $retVal;
    
}
function LogSelectDate($WorkDate,$FromTo,$Val) {
    
    $Select = "<select name='" . $FromTo . "[]'>";
    
    if ($WorkDate == $Val) {
        $Select .= "<option value='$WorkDate' selected>" . mu_Date::toString($WorkDate ,'m/d') . "</option>";
    } else {
        $Select .= "<option value='$WorkDate' >" . mu_Date::toString($WorkDate,'m/d') . "</option>";
    }
    
    $WorkDate = mu_date::addDay($WorkDate,1);
    if ($WorkDate == $Val) {
        $Select .= "<option value='$WorkDate' selected>" . mu_Date::toString($WorkDate ,'m/d') . "</option>";
    } else {
        $Select .= "<option value='$WorkDate' >" . mu_Date::toString($WorkDate ,'m/d') . "</option>";
    }
    
    $Select .= "</select>";
    
    return $Select;
}
function ChangeMacState($MacNo,$MacState) {
    
    global $con;
    
    // �����ޥ����μ���
    $sql = "select csv_flg from equip_machine_master2 where mac_no=$MacNo";
    $rs = pg_query ($con , $sql);
    if (!$row = pg_fetch_array ($rs)) {
        $SYSTEM_MESSAGE = "�ǡ����μ����˼��Ԥ��ޤ�����\n$sql";
        require_once ('../com/'.ERROR_PAGE);
        exit();
    }
    
    $CsvFlg = $row['csv_flg'];
    if ($CsvFlg == '1') {
        switch ($MacState) {
            case '0':
                // �Ÿ�OFF  -> 9:����¾���
                // �Ÿ�OFF  -> ���������ʤ�����֥��
                $retVal = '9';
                //$retVal = '';
                break;
            case '1':
                // ��ư��ž ->  2:�ܲ�Ư
                $retVal = '2';
                break;
            case '2':
                // ���顼�� ->  9:����¾���
                // ���顼�� ->  6:�������϶��Ԥ�
                $retVal = '9';
                //$retVal = '6';
                break;
            case '3':
                // �����   ->  9:����¾���
                // �����   ->  7:���祳��
                $retVal = '9';
                //$retVal = '7';
                break;
            case '4':
                // Net��ư  ->  9:����¾���
                $retVal = '9';
                break;
            case '5':
                // Net��λ  ->  9:����¾���
                $retVal = '9';
                break;
            case '10':
                // �ȵ���   ->  0:�����ɥ��
                // �ȵ���   ->  0:Ω�����
                $retVal = '0';
                break;
            case '11':
                // �ʼ���   ->  1:�ʼ��
                $retVal = '1';
                break;
            case '12':
                // �ξ㽤�� ->  3:�ξ㽤��
                $retVal = '3';
                break;
            case '13':
                // �϶�� ->  8:�϶��
                $retVal = '8';
                break;
            case '14':
                // ̵�ͱ�ž ->  A:̵�Ͳ�Ư
                $retVal = 'A';
                break;
            case '15':
                // ����     ->  9:����¾���
                $retVal = '9';
                break;
            /*
            case '16':
                // 10ͽ���ʼ��Ԥ� ->  4:�ʼ��Ԥ�
                $retVal = '4';
                break;
            case '17':
                // 11ͽ�������Ԥ� ->  5:�����Ԥ�
                $retVal = '5';
                break;
            default :
                // ̤���   ->  9:����¾���
                $retVal = '9';
            */
                break;
        }
    } else {
        switch ($MacState) {
            case '0':
                // �Ÿ�OFF  -> 9:����¾���
                // �Ÿ�OFF  -> ���������ʤ�����֥��
                $retVal = '9';
                //$retVal = '';
                break;
            case '1':
                // ��ư��ž ->  2:�ܲ�Ư
                $retVal = '2';
                break;
            case '2':
                // ���顼�� -> 9:����¾���
                // ���顼�� ->  6:�������϶��Ԥ�
                $retVal = '9';
                //$retVal = '6';
                break;
            case '3':
                // �����    -> 9:����¾���
                // �����   ->  7:���祳��
                $retVal = '9';
                //$retVal = '7';
                break;
            case '4':
                // �ȵ���   ->  0:�����ɥ��
                // �ȵ���   ->  0:Ω�����
                $retVal = '0';
                break;
            case '5':
                // �ʼ���   ->  1:�ʼ��
                $retVal = '1';
                break;
            case '6':
                // �ξ㽤�� ->  3:�ξ㽤��
                $retVal = '3';
                break;
            case '7':
                // �϶�� ->  8:�϶��
                $retVal = '8';
                break;
            case '8':
                // ̵�ͱ�ž ->  A:̵�Ͳ�Ư
                $retVal = 'A';
                break;
            case '9':
                // ����     ->  9:����¾���
                $retVal = '9';
                break;
            case '10':
                // ͽ����   ->  9:����¾���
                // ͽ����   ->  4:�ʼ��Ԥ�
                $retVal = '9';
                //$retVal = '4';
                break;
            case '11':
                // ͽ����   ->  9:����¾���
                // ͽ����   ->  5:�����Ԥ�
                $retVal = '9';
                //$retVal = '5';
                break;
            default :
                // ̤���   ->  9:����¾���
                $retVal = '9';
                break;
        }
    }
    
    return $retVal;
}

ob_end_flush();
