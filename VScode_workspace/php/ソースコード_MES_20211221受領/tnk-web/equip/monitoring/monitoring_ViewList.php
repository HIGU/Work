<?php
////////////////////////////////////////////////////////////////////////////////
// ������Ư�����ؼ����ƥʥ�                                               //
//                                               MVC View �� �ꥹ��ɽ��(List) //
// Copyright (C) 2021-2021 Ryota.waki ryota_waki@nitto-kohki.co.jp            //
// Changed history                                                            //
// 2021/03/24 Created monitoring_ViewList.php                                 //
// 2021/03/24 Release.                                                        //
// 2021/10/20 ɸ���Ƚ��QC����ɽ��xls�ˤ��ѹ��� 2901��2903��mac_no��        //
//            ���ѡ�(PDF)��mac_no���ѹ�                                  ��ë //
////////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);              // E_ALL='2047' debug ��
// ini_set('display_errors', '1');                 // Error ɽ�� ON debug �� ��꡼���女����
ob_start('ob_gzhandler');                       // ���ϥХåե���gzip����
session_start();                                // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��

require_once ('../../MenuHeader.php');          // TNK ������ menu class
require_once ('../../function.php');            // TNK ������ function
require_once ('../EquipControllerHTTP.php');    // TNK ������ MVC Controller Class
//class monitoring_Model
require_once ('monitoring_Model.php');          // MVC �� Model��
//class monitoring_Controller
require_once ('monitoring_Controller.php');     // MVC �� Controller��
access_log();                                   // Script Name �ϼ�ư����

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader();   // ǧ�ڥ����å�0=���̰ʾ� �����=���å������ �����ȥ�̤����

///// �������ѥ��å���󥯥饹�Υ��󥹥��󥹤����
$equipSession = new equipSession();

$request = new Request();

////////////// target����
// $menu->set_target('application');   // �ե졼���Ǥ�������target°����ɬ��
$menu->set_target('_parent');       // �ե졼���Ǥ�������target°����ɬ��

//////////// �ƽ����action̾�ȥ��ɥ쥹����
$menu->set_action('��ž�����', EQUIP2 . 'work/equip_work_monigraph.php');
$menu->set_action('���߲�ưɽ', EQUIP2 . 'work/equip_work_monichart.php');
$menu->set_action('�������塼��', EQUIP2 . 'plan/equip_plan_monigraph.php');

// ��ž����դȱ�ž���� ��[���]��URL�����ޤ�����ʤ��Τǡ�����Ū�˥��å�
$RetName = EQUIP2 . 'work/equip_work_monichart.php_ret';    // �����Υ��å�����ѿ�̾�������롼��
$_SESSION["$RetName"] = EQUIP2 . 'monitoring/monitoring_Main.php?state=run';    // �����򥻥å�
$RetName = EQUIP2 . 'work/equip_work_monigraph.php_ret';    // �����Υ��å�����ѿ�̾�������롼��
$_SESSION["$RetName"] = EQUIP2 . 'monitoring/monitoring_Main.php?state=run';    // �����򥻥å�

//////////// �ҥե졼����б������뤿�Ἣʬ���Ȥ�ե졼������Υ�����ץ�̾���Ѥ���
//$menu->set_self(EQUIP2 . 'monitoring/monitoring_ViewMain.php');
////////////// �꥿���󥢥ɥ쥹����
//$menu->set_RetUrl(EQUIP2 . 'monitoring/monitoring_ViewMain.php');   // �̾�ϻ��ꤹ��ɬ�פϤʤ�

// ��ž����դȱ�ž���� ������äƤ����ݤˡ�����
if( isset($_SESSION['work_mac_no']) ) {
    $request->add('m_no', $_SESSION['work_mac_no']);
    unset($_SESSION['work_mac_no']);
}
if( isset($_SESSION['work_plan_no']) ) {
    $request->add('plan_no', $_SESSION['work_plan_no']);
    unset($_SESSION['work_plan_no']);
}

//////////// �ӥ��ͥ���ǥ����Υ��󥹥�������
$model = new Monitoring_Model($request);

$current_script  = $_SERVER['PHP_SELF'];        // ���߼¹���Υ�����ץ�̾����¸

if (isset($_REQUEST['factory'])) {
    $factory = $_REQUEST['factory'];
} else {
    ///// �ꥯ�����Ȥ�̵����Х��å���󤫤鹩���ʬ��������롣(�̾�Ϥ��Υѥ�����)
    $factory = @$_SESSION['factory'];
}

$selectMode   = $request->get('select_mode');   // �������åȥ�˥塼�����
$state   = $request->get('state');              // �������åȥ�˥塼�����

if ($selectMode == '' ) {
    if (isset($_REQUEST['selectMode'])) {
        $selectMode = $_REQUEST['selectMode'];
    } else {
        $selectMode = 'start';
    }
}

if ($state == '' ) {
    if (isset($_REQUEST['state'])) {
        $state = $_REQUEST['state'];
    } else {
        $state = 'init';
    }
}

//echo '�ƥ��ȣͣӣǡ�selectMode:��' . $selectMode . '��state:��' . $state . '��' . $current_script;
/////////// HTML Header ����Ϥ��ƥ���å��������
$menu->out_html_header();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>

<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<title><?php echo $menu->out_title() ?></title>
<?php echo $menu->out_site_java() ?>
<?php echo $menu->out_css() ?>
<?php echo $menu->out_jsBaseClass() ?>
<style type="text/css">
<!--
input.number {
    width:              30px;
    font-size:          0.9em;
    font-weight:        bold;
    color:              blue;
}
-->
</style>

<link rel='stylesheet' href='monitoring.css' type='text/css' media='screen'>

<script type='text/javascript' language='JavaScript' src='monitoring.js'>
</script>

</head>

<?php if( $selectMode == 'start' && $state != 'plan_load' && $state != 'delete' && $state != 'end' && $model->GetPlanNo() != '' ) { ?>
<body onLoad='init()'>
<?php } else { ?>
<body onLoad='init2()'>
<?php }?>

<center>

<form name='radioForm' method='post' action='<?php echo $current_script ?>' onSubmit='return true;'>
    <!-- ����������̤���롣 -->
</form>

<?php
switch( $selectMode ) { // ��ȶ�ʬ��Ƚ��
    case 'start':       // ��ž����
        if( $state=='init' ) {
            $menu->set_caption($model->GetCaption('select'));
            
            echo "<table class='pt10' border='1' cellspacing='0'>\n";
            echo "<tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>\n";
            echo "<table width='100%' class='pt20' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>\n";
            echo "  <tr>\n";
            echo "      <!--  bgcolor='#ffffc6' �������� -->\n";
            echo "      <td class='winbox' style='background-color:yellow; color:blue;' colspan='3' align='center'>\n";
            echo "          <div class='caption_font'>{$menu->out_caption()}</div>\n";
            echo "      </td>\n";
            echo "  </tr>\n";
            
            echo "  <form name='select_form' method='post' action='{$current_script}' onSubmit='return setState(this)'>\n";
            echo "      <input type='hidden' name='state' id='id_state'>\n";
            echo "      <input type='hidden' name='m_no' id='id_m_no'>\n";
            echo "      <input type='hidden' name='m_name' id='id_m_name'>\n";
            echo "      <input type='hidden' name='plan_no' id='id_plan_no'>\n";
            
            if( ($rows=$model->GetFactoryMachineInfo($res, 6)) <= 0 ) {
                return ;    // ���깩��ε������󤬤ʤ��Ȥ���
            } else {
                $next = true;   // 3��ɽ�������鼡�ιԤ�
                for( $r=0,$cnt=0; $r<$rows; $r++ ) {
                    if( $next ) {
            echo "      <tr>\n";
                    }
                    if( $res[$r][2] == 'Y' ) {  // ͭ�� ɽ��
            echo "          <td align='center'>\n";
                                $plan_no = $model->GetRunningPlanNo($res[$r][0]);
//            echo "              <input type='submit' value='�����ֹ桧{$res[$r][0]}\n�� �� ̾��{$res[$r][1]}\n�ײ��ֹ桧$plan_no' onClick='setSlectInfo($r)'>\n";
            echo "              <button type='submit' onClick='setSlectInfo($r)'>�����ֹ桧{$res[$r][0]}<BR>�� �� ̾��{$res[$r][1]}<BR>�ײ��ֹ桧$plan_no</button>\n";
            echo "          </td>\n";
            echo "          <input type='hidden' name='m_no$r' id='id_m_no$r' value='{$res[$r][0]}'>\n";
            echo "          <input type='hidden' name='m_name$r' id='id_m_name$r' value='{$res[$r][1]}'>\n";
            echo "          <input type='hidden' name='plan_no$r' id='id_plan_no$r' value='$plan_no'>\n";
                            $cnt++;
                    }
                    if( $cnt >= 3) {    // 3����ɽ�������鼡�ιԤ�
                        $cnt = 0;
                        $next = true;
                    } else { 
                        $next = false;
                    }
                    if( $next ) {
            echo "      </tr>\n";
                    }
                }
                if( !$next ) {  // 3��ʬɽ�����Ƥʤ��Ȥ�
                    for( ; $cnt<3; $cnt++) {
            echo "          <td>��</td>\n";  // �Ĥ�����ɽ��
                    }
            echo "      </tr>\n";
                }
            }
            echo "  </form>\n";
            echo "</table>\n";
            echo "</td></tr>\n";
            echo "</table> <!----------------- ���ߡ�End --------------------->\n";
        } else {    // $state!='init'
//            $menu->set_caption($model->GetCaption());
            $style0 = "style='background-color:White'";
            $style1 = "style='background-color:CornSilk'";
            $style2 = "style='background-color:Gold'";
            $style3 = "style='background-color:LightCyan'";
            $button_style = "style='width:120px;height:50px'";
            if( $model->IsPlanNo() ) {
                $model->GetViewDate($request);
            }
            echo "<table class='pt10' border='1' cellspacing='0'>\n";
            echo "<tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>\n";
            echo "<table width='100%' class='pt20' bgcolor='LightCyan' align='center' border='1' cellspacing='0' cellpadding='3'>\n"; // bgcolor='#d6d3ce'
/*
            echo "  <tr>\n";    // ����ץ����
            echo "      <!--  bgcolor='#ffffc6' �������� -->\n";
            echo "      <td class='winbox' style='background-color:yellow; color:blue;' colspan='3' align='center'>\n";
            echo "          <div class='caption_font'>{$menu->out_caption()}</div>\n";
            echo "      </td>\n";
            echo "  </tr>\n";
/**/
            
            echo "  <form name='main_form' method='post' action='$current_script' onSubmit='return planNoCheck()'>\n";
            echo "      <input type='hidden' name='state' id='id_state'>\n";
            echo "      <tr $style1>\n";    // [���]������̾
            echo "          <td nowrap align='center'>\n";
            echo "              <input type='button' $button_style value='������������' onclick='document.radioForm.submit()'>\n";
            echo "          </td>\n";
            echo "          <input type='hidden' name='m_no' id='id_m_no' value='{$request->get('m_no')}'>\n";
            echo "          <td nowrap align='center' colspan='3'>\n";
                            $request->add('m_name', $model->GetMacName($request->get('m_no')));
                            if( $model->GetRunningPlanNo($request->get('m_no')) != '--------' ) {
            echo "              <a href='{$menu->out_action('��ž�����')}?mac_no={$request->get('m_no')}' target='_parent'>{$request->get('m_name')}</a>\n";
                            } else {
            echo "              {$request->get('m_name')}\n";
                            }
            echo "          </td>\n";
            echo "          <td nowrap align='center' class='pt9'>\n";
                                $w_date = date('Y') . "/" . date('m') . "/" . date('d');
            echo "              �������$w_date\n";
            echo "          </td>\n";
            echo "          <input type='hidden' name='m_name' id='id_m_name' value='{$request->get('m_name')}'>\n";
            echo "      </tr>\n";

            echo "      <tr $style1>\n";    // ���ܥ����ȥ�
            echo "          <td nowrap align='center'>ASSY No.</td>\n";
            echo "          <td nowrap align='center'>����̾</td>\n";
            echo "          <td nowrap align='center'>���κ��</td>\n";
            echo "          <td nowrap align='center'>�ײ�No.</td>\n";
            echo "          <td nowrap align='center'>Ǽ��</td>\n";
            echo "      </tr>\n";
                        if( $model->IsPlanNo() ) {
            echo "      <tr $style1>\n";    // ��������
            echo "          <td nowrap align='center'>\n";
            echo "              {$model->GetPartsNo()}\n";
            echo "              <input type='hidden' name='b_no' value='{$model->GetPartsNo()}'>\n";
            echo "          </td>\n";
            echo "          <td nowrap align='center'>\n";
            echo "              {$model->GetPartsName()}\n";
            echo "          </td>\n";
            echo "          <td nowrap align='center' style='color:red'>����ʤ�</td>\n";
            echo "          <td nowrap align='center'>\n";
            echo "              {$model->GetPlanNo()}\n";
            echo "              <input type='hidden' name='plan_no' id='id_plan_no' value='{$request->get('plan_no')}'>\n";
            echo "          </td>\n";
            echo "          <td nowrap align='center'>{$model->GetDeadLines()}</td>\n";
            echo "      </tr>\n";
                        } else {
            echo "      <tr $style1>\n";
            echo "          <td nowrap align='center'>-----</td>\n";
            echo "          <td nowrap align='center'>-----</td>\n";
            echo "          <td nowrap align='center'>-----</td>\n";
            echo "          <td nowrap align='center'>\n";
            echo "              <input type='text' style='font-size:40px;height:50px' size='9' maxlength='8' name='plan_no' id='id_plan_no' onkeyup='obj_upper(this)'>";
            echo "              <input type='submit' $button_style value='�ɹ���' name='plan_load' id='id_plan_load' onClick='setState(this);'>";
            echo "          </td>\n";
            echo "          <td nowrap align='center'>--��--��</td>\n";
            echo "      </tr>\n";
                        }

            if( $model->IsPlanNo() ) {  // �ײ��ֹ��ɤ߹���OK�ʤ�ɽ�����롣
            echo "      <tr>\n";    // �����ؼ������ʼ��ؼ���
            echo "          <td nowrap align='center' colspan='3' $style1>\n";
            echo "              �����ؼ�����" . number_format($model->GetPlan()) . " ��\n";
            echo "          </td>\n";
            echo "          <td nowrap align='center' colspan='2' $style2>\n";
                                $file_mac = $request->get('m_no');
                                $filename = "pdf/" . $file_mac . ".pdf";
                                if (file_exists($filename)) {
            echo "                  <a href='pdf/download_file.php/{$file_mac}.pdf'>�ʼ��ؼ���</a>\n";
                                } else {
            echo "                  �ʼ��ؼ���\n";
                                }
            echo "          </td>\n";
            echo "      </tr>\n";
/*
            echo "      <tr>\n";    // ����ɸ���Ƚ�
            echo "          <td nowrap align='center' colspan='3' $style0>��</td>\n";
            echo "          <td nowrap align='center' colspan='2' $style2>\n";
                                $file_parts = $model->GetPartsNo();
                                $filename = "pdf/" . $file_parts . "-H.pdf";
                                if (file_exists($filename)) {
            echo "                  <a href='pdf/download_file.php/{$file_parts}-H.pdf'>ɸ���Ƚ�</a>\n";
                                } else {
            echo "                  ɸ���Ƚ�\n";
                                }
            echo "          </td>\n";
            echo "      </tr>\n";
*/
            echo "      <tr>\n";    // ����QC����ɽ
            echo "          <td nowrap align='center' colspan='3' $style0>��</td>\n";
            echo "          <td nowrap align='center' colspan='2' $style2>\n";
                                $file_parts = $model->GetPartsNo();
                                $filename = "pdf/" . $request->get('m_no') . "-Q.pdf";
                                if (file_exists($filename)) {
            echo "                  <a href='pdf/download_file.php/{$request->get('m_no')}-Q.pdf'>QC����ɽ</a>\n";
                                } else {
            echo "                  QC����ɽ\n";
                                }
            echo "          </td>\n";
            echo "      </tr>\n";
            
            echo "      <tr>\n";    // �����������Զ�����ʣ����ܡ�
            echo "          <td nowrap align='center' colspan='3' $style3>\n";
                            if( $model->GetRunningPlanNo($request->get('m_no')) != '--------' ) {
            echo "              <a href='{$menu->out_action('���߲�ưɽ')}?mac_no={$request->get('m_no')}' target='_parent'>������</a>\n";
                            } else {
            echo "              ��������\n";
                            }
                            $jisseki = $model->GetProNum($request->get('plan_no'),$request->get('m_no'));
            echo "          " . number_format($jisseki). "��\n";
            echo "          <input type='hidden' name='jisseki' value='$jisseki'>\n";
            echo "          </td>\n";
            if( $model->GetPlan() < $jisseki ) {
                $_SESSION['s_sysmsg'] = '���������������ؼ�����Ķ���ޤ�������';
            }
/*
            echo "          <td nowrap align='center' colspan='2' rowspan='2' $style2>\n";
                                $file_parts = $model->GetPartsNo();
                                $filename = "pdf/" . $file_parts . "-G.pdf";
                                if (file_exists($filename)) {
            echo "                  <a href='pdf/download_file.php/{$file_parts}-G.pdf'>���ѡ���æȴ������<BR>����Զ�����</a>\n";
                                } else {
            echo "                  ���ѡ���æȴ������<BR>����Զ�����\n";
                                }
            echo "          </td>\n";
*/
/*
                            // ���Ѥ򵡳�No-G���ѹ�
            echo "          <td nowrap align='center' colspan='2' rowspan='2' $style2>\n";
                                $file_parts = $model->GetPartsNo();
                                $filename = "pdf/" . $request->get('m_no') . "-G.pdf";
                                if (file_exists($filename)) {
            echo "                  <a href='pdf/download_file.php/{$request->get('m_no')}-G.pdf'>���ѡ���æȴ������<BR>����Զ�����</a>\n";
                                } else {
            echo "                  ���ѡ���æȴ������<BR>����Զ�����\n";
                                }
            echo "          </td>\n";
*/
                            // ���Ѥ򵡳�No-G���ѹ�
            echo "          <td nowrap align='center'  colspan='2' $style2>\n";
                                $file_parts = $model->GetPartsNo();
                                $filename = "pdf/" . $request->get('m_no') . "-G.pdf";
                                if (file_exists($filename)) {
            echo "                  <a href='pdf/download_file.php/{$request->get('m_no')}-G.pdf'>���ʸ�������</a>\n";
                                } else {
            echo "                  ���ʸ�������\n";
                                }
            echo "          </td>\n";

            echo "      </tr>\n";

            echo "      <tr>\n";    // �����ξ��֡��Զ�����ʣ����ܡ�
                            $m_state = $model->GetRunState($request->get('plan_no'),$request->get('m_no'), $bg_color, $txt_color);
            echo "          <td nowrap align='center' colspan='3' style='color:$txt_color; background-color:$bg_color'>\n";
            echo "              �����ξ��֡�$m_state\n";
            echo "          </td>\n";
                            // ����Զ�����������ֹ�
            echo "          <td nowrap align='center' colspan='2' $style2>\n";
                                $file_parts = $model->GetPartsNo();
                                $filename = "pdf/" . $request->get('m_no') . "-K.pdf";
                                if (file_exists($filename)) {
            echo "                  <a href='pdf/download_file.php/{$request->get('m_no')}-K.pdf'>����Զ�����</a>\n";
                                } else {
            echo "                  ����Զ�����\n";
                                }
            echo "          </td>\n";
            echo "      </tr>\n";
                        if( $model->GetState() != 'start' ) {
            echo "      <tr $style0>\n";    // ���ϡ��ײ��ֹ����Ϥ����
            echo "          <td nowrap align='center' colspan='5'>\n";
            echo "              <input type='submit' $button_style value='����' name='start' id='id_start' onClick='setState(this);'>����\n";
            echo "              <input type='submit' $button_style value='�ײ�No.���Ϥ����' name='reset' id='id_reset' onClick='setState(this);'>\n";
            echo "          </td>\n";
            echo "      </tr>\n";
                        }
            }   // ������ �ײ��ֹ��ɤ߹���OK�ʤ�ɽ�����롣
            
            if( $model->GetState() == 'start' ) {   // ��ž���ϸ塢ɽ�����롣
/**
            echo "      <tr>\n";    // QC����ɽ
            echo "          <td nowrap align='center'>\n";
                            $filename = "pdf/" . $file_parts . "-Q.pdf";
                            if (file_exists($filename)) {
            echo "              <a href='pdf/download_file.php/$file_parts-Q.pdf'>QC����ɽ</a>\n";
                            } else {
            echo "              QC����ɽ\n";
                            }
            echo "          </td>\n";
            echo "      </tr>\n";
/**/
            echo "      <tr $style0>\n";    // ��Ư���֡��ܥ���ɽ���ΰ�
            echo "          <td nowrap align='center' colspan='5'>\n";
                            switch( $model->GetHeaderInfo() ) { // �إå�������
                                case 'run':     // ����
            echo "                  <input type='submit' $button_style value='��λ' name='end' id='id_end' onClick='chk_end_inst(state, m_no.value, m_name.value, plan_no.value, b_no.value);'>����";
            echo "                  <input type='submit' $button_style value='����' name='break' id='id_break' onClick='chk_cut_form(state, m_no.value, m_name.value, plan_no.value, b_no.value);'>";
                                    break;
                                case 'break':   // ����
            echo "                  <input type='submit' $button_style value='�Ƴ�' name='restart' id='id_restart' onClick='chk_break_restart(state, m_no.value, m_name.value, plan_no.value, b_no.value);'>��";
            echo "                  <input type='submit' $button_style value='���' name='delete' id='id_delete' onClick='chk_break_del(state, m_no.value, m_name.value, plan_no.value, b_no.value);'>��";
                                default:        // ����¾
            echo "                  <input type='submit' $button_style value='�ײ�No.���Ϥ����' name='reset' id='id_reset' onClick='setState(this);'>";
                                    break;
                            }
            echo "          </td>\n";
            echo "      </tr>\n";
            }   // ������ ��ž���ϸ塢ɽ�����롣
            
            echo "  </form>\n";
            echo "</table>\n";
            echo "</td></tr>\n";
            echo "</table> <!----------------- ���ߡ�End --------------------->\n";
        }
        break;
    case 'break':   // ���Ƿײ�
        if( $state != 'init' ) {
            $model->GetViewDate($request); // �Ƴ� or ��� ��Ԥä����ɽ�����ܤ���ɤ߹��ߤ��롣
        }
        $query = "
            SELECT mac_no
                , m.mac_name
                , plan_no
                , parts_no
                , koutei
                , plan_cnt
                , to_char(str_timestamp AT TIME ZONE 'JST', 'YYYY/MM/DD HH24:MI:SS') AS str_timestamp
                , to_char(str_timestamp AT TIME ZONE 'JST', 'YYYYMMDDHH24MISS') AS searchTime
            FROM
                equip_work_log2_header_moni AS h
            LEFT OUTER JOIN
                equip_machine_master2 AS m
            USING(mac_no)
            WHERE
                work_flg IS FALSE AND end_timestamp IS NULL
                AND factory = '" . $factory . "'
            ORDER BY str_timestamp DESC
        ";
        $res = array();
        if (($rows=getResult($query,$res)) >= 1) {  // �ǡ����١����Υإå������������Ƿײ�����
            echo "<table bgcolor='#d6d3ce' align='center' cellspacing='0' cellpadding='3' border='1'>\n";
            echo "<tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>\n";
            echo "<table bordercolordark='white' bordercolorlight='#bdaa90' bgcolor='#d6d3ce' align='center' cellspacing='0' cellpadding='3' border='1'>\n";
            $menu->set_caption("���� ���Ǥ���Ƥ���ײ� (����ϴ�������ʤΤ����)");
            echo "  <tr>\n";    // ����ץ����
            echo "      <td class='winbox' style='background-color:yellow; color:blue;' colspan='10' align='center'>\n";
            echo "          <div class='caption_font'>{$menu->out_caption()}</div>\n";
            echo "      </td>\n";
            echo "  </tr>\n";
            
            echo "  <th width='40' class='fc_white'>�Ƴ�</th>
                    <th width='40' class='fc_red'>���</th>
                    <th width='70'>�����ֹ�</th>
                    <th width='80'>����̾</th>
                    <th width='70'>�ײ��ֹ�</th>
                    <th width='80'>�����ֹ�</th>
                    <th width='40'>����</th>
                    <th width='80'>�ײ��</th>
                    <th nowrap>���� ǯ���� ����</th>
                    <th nowrap>���� �ؼ� ����</th>\n";
            for ($r=0; $r<$rows; $r++) {
                ///// ���������ǻ��֤��������
                $query = "select to_char(date_time AT TIME ZONE 'JST', 'YYYY/MM/DD HH24:MI:SS') as cut_timestamp
                            from
                                equip_work_log2_moni
                            where
                                plan_no='{$res[$r][2]}' and mac_no={$res[$r][0]} and koutei={$res[$r][4]} and to_char((CAST(date_time AS TIMESTAMP)), 'YYYYMMDDHH24MISS') >={$res[$r][7]}
                            order by
                                date_time DESC
                            offset 0 limit 1
                ";
                /*
                $query = "select to_char(date_time AT TIME ZONE 'JST', 'YYYY/MM/DD HH24:MI:SS') as cut_timestamp
                            from
                                equip_work_log2_moni
                            where
                                equip_moni_index(mac_no, plan_no, koutei, date_time) >= '{$res[$r][0]}{$res[$r][2]}{$res[$r][4]}{$res[$r][7]}'
                                -- date_time > '{$res[$r][6]}'
                            and
                                equip_moni_index(mac_no, plan_no, koutei, date_time) <  '{$res[$r][0]}{$res[$r][2]}{$res[$r][4]}99999999'
                                -- mac_no={$res[$r][0]} and plan_no={$res[$r][2]} and koutei={$res[$r][4]} and mac_state=9 -- ����
                            order by
                                equip_moni_index(mac_no, plan_no, koutei, date_time) DESC
                                -- date_time DESC
                            offset 0 limit 1
                ";
                */
                if (getUniResult($query, $cut_timestamp) <= 0) {
                    $cut_timestamp = '��';
                }
            echo "  <form name='break_form' action='", $current_script, "?select_mode=break' method='post'>\n";
            echo "      <input type='hidden' name='state'>\n";
            echo "      <input type='hidden' name='m_no' value='" . $res[$r][0] . "'>\n";
            echo "      <input type='hidden' name='m_name' value='" . $res[$r][1] . "'>\n";
            echo "      <input type='hidden' name='plan_no' value='" . $res[$r][2] . "'>\n";
            echo "      <input type='hidden' name='b_no' value='" . $res[$r][3] . "'>\n";
            echo "      <input type='hidden' name='k_no' value='" . $res[$r][4] . "'>\n";
            echo "      <input type='hidden' name='plan' value='" . $res[$r][5] . "'>\n";
            echo "      <tr>\n";    // ���Ƿײ��ɽ��
            echo "          <td align='center'>
                                <input type='submit' class='number' name='break_restart' value='" . ($r + 1) . "' onClick='return chk_break_restart(state, m_no.value, m_name.value, plan_no.value, b_no.value)'>
                            </td>\n";
            echo "          <td align='center'>
                                <input type='submit' class='number' name='break_del' value='" . ($r + 1) . "' onClick='return chk_break_del(state, m_no.value, m_name.value, plan_no.value, b_no.value)'>
                            </td>\n";
            echo "          <td align='center' nowrap>" . $res[$r][0] . "</td>\n";
            echo "          <td align='left' nowrap>" . $res[$r][1] . "</td>\n";
            echo "          <td align='center' nowrap>" . $res[$r][2] . "</td>\n";
            echo "          <td align='center' nowrap>" . $res[$r][3] . "</td>\n";
            echo "          <td align='center' nowrap>" . $res[$r][4] . "</td>\n";
            echo "          <td align='right' nowrap>" . number_format($res[$r][5]) . "</td>\n";
            echo "          <td align='center' nowrap>" . $res[$r][6] . "</td>\n";
            echo "          <td align='center' nowrap>{$cut_timestamp}</td>\n";
            echo "      </tr>\n";
            echo "  </form>\n";
            }
            echo "</table>\n";
            echo "</td></tr>\n";
            echo "</table> <!----------------- ���ߡ�End ------------------>\n";
        } else {
            echo "<BR><font align='center' nowrap>���ߡ����Ǥ���Ƥ���ײ�Ϥ���ޤ���</font>\n";
        }
        break;
    case 'change':  // �ؼ��ѹ�
        $query = "select mac_no
                        , m.mac_name
                        , plan_no
                        , parts_no
                        , koutei
                        , plan_cnt
                        , to_char(str_timestamp AT TIME ZONE 'JST', 'YYYY/MM/DD HH24:MI:SS') as str_timestamp
                    from
                        equip_work_log2_header_moni
                    left outer join
                        equip_machine_master2 as m
                    using(mac_no)
                    where
                        work_flg IS TRUE and end_timestamp is NULL
                        AND factory = '" . $factory . "'
                    order by
                        str_timestamp DESC
                ";
        $res = array();
        if (($rows=getResult($query,$res)) >= 1) {  // �ǡ����١����Υإå�����걿ž��ǡ��������
            echo "<table bgcolor='#d6d3ce' align='center' cellspacing='0' cellpadding='3' border='1'>\n";
            echo "<tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>\n";
            echo "<table bordercolordark='white' bordercolorlight='#bdaa90' bgcolor='#d6d3ce' align='center' cellspacing='0' cellpadding='3' border='1'>\n";
            $menu->set_caption("�ؼ��ѹ��������ײ�� [�ѹ�] �ܥ���򲡤��Ʋ�������");
            echo "  <tr>\n";    // ����ץ����
            echo "      <td class='winbox' style='background-color:yellow; color:blue;' colspan='10' align='center'>\n";
            echo "          <div class='caption_font'>{$menu->out_caption()}</div>\n";
            echo "      </td>\n";
            echo "  </tr>\n";
            
            echo "  <th width='20' nowrap>No.</th>
                    <th width='40' class='fc_yellow'>�Խ�</th>
                    <th width='80'>�����ֹ�</th>
                    <th width='80'>����̾</th>
                    <th width='80'>�ײ��ֹ�</th>
                    <th width='80'>�����ֹ�</th>
                    <th width='40'>����</th>
                    <th width='80'>�ײ��</th>
                    <th>���� ǯ���� ����</th>\n";
            for ($r=0; $r<$rows; $r++) {
            echo "  <form name='change_form' action='monitoring_edit_chart.php?select_mode=change' method='post' target='application'>\n";
            echo "      <input type='hidden' name='state' id='id_state'>\n";
            echo "      <input type='hidden' name='mac_no' value='" . $res[$r][0] . "'>\n";
            echo "      <input type='hidden' name='plan_no' value='" . $res[$r][2] . "'>\n";
            echo "      <input type='hidden' name='koutei' value='" . $res[$r][4] . "'>\n";
            echo "      <tr>\n";
                            $num = $r+1;
            echo "          <td align='center'>$num</td>\n";
            echo "          <td align='center'><input type='submit' class='editButton' name='edit' value='�ѹ�' onClick='setState(this);'></td>\n";
            echo "          <td align='center' nowrap>" . $res[$r][0] . "<input type='hidden' name='mac_no' size='4' value='" . $res[$r][0] . "' maxlength='4' class='center'></td>\n";
            echo "          <td align='left' nowrap>" . $res[$r][1] . "</td>\n";
            echo "          <td align='center' nowrap>" . $res[$r][2] . "</td>\n";
            echo "          <td align='center' nowrap>" . $res[$r][3] . "</td>\n";
            echo "          <td align='center' nowrap>" . $res[$r][4] . "</td>\n";
            echo "          <td align='right' nowrap>" . number_format($res[$r][5]) . "</td>\n";
            echo "          <td align='center' nowrap>" . $res[$r][6] . "</td>\n";
            echo "      </tr>\n";
            echo "  </form>\n";
            }
            echo "</table>\n";
            echo "</td></tr>\n";
            echo "</table> <!----------------- ���ߡ�End ------------------>\n";
        } else {
            echo "<BR><font align='center' nowrap>���ߡ��ؼ��ѹ��Ǥ���ײ�Ϥ���ޤ���</font>\n";
        }
        break;
    default:        // ����¾
            echo "<BR><font align='center' nowrap>�����ƥ�ô���Ԥ�Ϣ���Ʋ�������</font>\n";
        break;
}
?>
</center>
</body>

<!-- ��ư�������˼¹Ԥ���� -->
<form name='reload_form' action='monitoring_ViewList.php' method='get' target='_self'>
    <input type='hidden' name='factory' value='<?php echo $factory?>'>
    <input type='hidden' name='selectMode' value='<?php echo $selectMode?>'>
    <input type='hidden' name='state' value='<?php echo $state?>'>
    <input type='hidden' name='m_no' value='<?php echo $request->get('m_no')?>'>
    <input type='hidden' name='m_name'  value='<?php echo $request->get('m_name')?>'>
    <input type='hidden' name='plan_no'  value='<?php echo $request->get('plan_no')?>'>
</form>

<?php echo $menu->out_alert_java()?>
</html>
