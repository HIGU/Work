<?php
//////////////////////////////////////////////////////////////////////////////
// ������ư���������ƥ�� �ù��ؼ�(�ؼ����ƥʥ�)  �ե졼�� �ꥹ�� ���  //
// Copyright (C) 2004 2006-2018 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp //
// Changed history                                                          //
// 2004/07/27 Created  equip_workMnt_List.php                               //
// 2004/08/03 ������ηײ��ɽ��������ʬ������ �ؼ� ������ �ɲ� EXPLAIN��chk//
// 2004/08/05 �����Ϥ���Ͽ������ͤ��ѹ� ���ʥޥ������Υ����å��Τ�         //
// 2004/08/08 �ե졼���Ǥ�������application��_parent���ѹ�(FRAME̵���б�) //
//            ���ǻؼ������μ�����equip_index()�ؿ�����Ѥ���褦��SQLʸ�ѹ�//
// 2006/03/27 �ù���λ�ؼ����ϸ塢��λ���̤�ݻ�����                        //
// 2007/03/27 set_site()�᥽�åɤ� INDEX_EQUIP ���ѹ�  �嵭�β��̰ݻ������� //
//            ��Ŭ�ѡ���ž���ϻ������Ƿײ褬������ϥ�å���������Ϥ���  //
// 2007/07/27 �ؼ��ֹ�ˤ�빩�������˥塼���ɲá������ϤΥ��å��ѹ�    //
//            $menu->out_retF2Script()�ɲ� �� baseJS.keyInUpper(this) �ɲ�  //
// 2007/09/18 E_ALL | E_STRICT ���ѹ�                                       //
// 2018/05/18 ��������ɲá������ɣ�����Ͽ����Ū��7���ѹ�            ��ë //
// 2018/12/26 �������﫤�SUS��ʬΥ                                  ��ë //
// 2018/12/27 equip_header_to_csv�դ��®�٤��٤��ʤäƤ뵤������Τ�       //
//            ��������Ȳ���netmoni�Ϥʤ��ΤǱƶ���̵��Ȧ            ��ë //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL | E_STRICT);
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
ob_start('ob_gzhandler');                   // ���ϥХåե���gzip����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��
require_once ('../equip_function.php');             // ������˥塼 ���� function (function.php��ޤ�)
require_once ('../EquipControllerHTTP.php');        // TNK ������ MVC Controller Class
require_once ('../../MenuHeader.php');              // TNK ������ menu class
access_log();                                       // Script Name �ϼ�ư����

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader();           // ǧ�ڥ����å�0=���̰ʾ� �����=���å������ �����ȥ�̤����

///// �������ѥ��å���󥯥饹�Υ��󥹥��󥹤����
$equipSession = new equipSession();

$request = new Request();

////////////// ����������
$menu->set_site(INDEX_EQUIP, 23);           // site_index=40(������˥塼) site_id=23(�ؼ����ƥʥ�)
//////////// �ҥե졼����б������뤿�Ἣʬ���Ȥ�ե졼������Υ�����ץ�̾���Ѥ���
$menu->set_self(EQUIP2 . 'work_mnt/equip_workMnt_Main.php');
////////////// target����
// $menu->set_target('application');           // �ե졼���Ǥ�������target°����ɬ��
$menu->set_target('_parent');               // �ե졼���Ǥ�������target°����ɬ��

//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title('������ư���� �ؼ����ƥʥ�');
//////////// ɽ�������
$menu->set_caption('��ȶ�ʬ�����򤷤Ʋ�����');
//////////// �ƽ����action̾�ȥ��ɥ쥹����
$menu->set_action('work_edit',  EQUIP2 . 'work_mnt/equip_edit_chart.php');

///// ��Ǽ�ʬ���Ȥ��Ѥ��Ƥ��뤿�� current_script����ꤷ�Ƥ���
$current_script  = $_SERVER['PHP_SELF'];        // ���߼¹���Υ�����ץ�̾����¸

/////////// �����ʬ���������
$factory = $equipSession->getFactory();
switch ($factory) {
case 1:
case 2:
case 4:
case 5:
case 6:
case 7:
case 8:
    $fact_where = "AND factory = {$factory}";
    break;
default:
    $fact_where = '';
    break;
}

/////////// ��ž�ؼ���˥塼����������
$equipment_select = $request->get('equipment_select');

/////////// POST Data �Υ������ѿ�����Ͽ�������
$mac_no     = $request->get('mac_no');
$siji_no    = $request->get('siji_no');
$parts_no   = $request->get('parts_no');
$koutei     = $request->get('koutei');
$plan_cnt   = $request->get('plan_cnt');

$init_data_input    = $request->get('init_data_input');
$init_data_cut      = $request->get('init_data_cut');
$init_data_edit     = $request->get('init_data_edit');
$init_data_end      = $request->get('init_data_end');
$plan_to_start      = $request->get('plan_to_start');
$break_restart      = $request->get('break_restart');
$break_del          = $request->get('break_del');
$init_data_cancel   = $request->get('init_data_cancel');

/******* IE �к� *********/
if (isset($_POST['init_siji_no'])) {
    $siji_no = $_POST['init_siji_no'];
    $init_data_input = '��ǧ';
}

///////////// POST Data ������������������ѿ�����Ͽ
if (isset($_POST['m_no'])) $m_no = $_POST['m_no'];
if (isset($_POST['s_no'])) $s_no = $_POST['s_no'];
if (isset($_POST['b_no'])) $b_no = $_POST['b_no'];
if (isset($_POST['k_no'])) $k_no = $_POST['k_no'];
if (isset($_POST['p_no'])) $p_no = $_POST['p_no'];

///////////// ��˥塼���إ��å�
/////////////////////////////////////////////////// ��ž���ϻ��� ���
if ($init_data_cancel != '') {
    $equipment_select = 'init_data_input';  // ��ž���� ���̤�
    $init_data_input = '';                  // �ؼ��ֹ����ϲ���
}

/////////////////////////////////////////////////// ��ž����
while ($init_data_input == '��Ͽ') {                   // �����ǡ����ɲ�
    if ($mac_no == '') {
        $_SESSION['s_sysmsg'] = "<font color='yellow'>���������򤷤Ʋ�������</font>";
        break;
    }
    if (!partsNoCheck($parts_no)) {
        $equipment_select = 'init_data_input';  // ��ž���� ���̤�
        $init_data_input = '';                  // �ؼ��ֹ����ϲ���
        break;
    }
    ////////// ��˵���̾�����
    $queryName = "
        SELECT mac_name FROM equip_machine_master2 WHERE mac_no={$mac_no}
    ";
    $mac_name = '';
    getUniResult($queryName, $mac_name);
    ////////// ���Ƿײ������å����ơ�����Х�å����������
    $query = "
        SELECT siji_no
        , to_char(str_timestamp AT TIME ZONE 'JST', 'YYYY/MM/DD HH24:MI') AS str_timestamp
        FROM equip_work_log2_header
        WHERE mac_no={$mac_no} AND end_timestamp IS NULL AND work_flg IS FALSE
        ORDER BY str_timestamp DESC
    ";
    $res = array();
    if (($rows=getResult2($query, $res)) >= 1) {
        $_SESSION['s_sysmsg'] = "[$mac_no] {$mac_name} ��ž���Ϥ��ޤ�����\\n\\n���Ƿײ褬����ޤ��� \\n\\n�ؼ��ֹ桧{$res[0][0]} \\n\\n������{$res[0][1]} \\n\\n������ޤ���\\n\\n��ǧ���Ʋ�������";
    }
    ////////// �إå�����걿ž��ε���������å�
    $query = "
        SELECT mac_no, siji_no, parts_no, koutei, plan_cnt
        FROM
            equip_work_log2_header
        WHERE
            work_flg IS TRUE
            and mac_no='$mac_no' LIMIT 1
    ";
    $res = array();
    if (($rows=getResult($query, $res)) >= 1) {
        $_SESSION['s_sysmsg'] = "<font color='yellow'>[{$mac_no}] {$mac_name} �ϴ��˱�ž���Ϥ���Ƥ��ޤ���</font>";
    } else {
        ////////// �إå��������Υǡ����ν�ʣ�����å�
        $query = "
            SELECT mac_no, siji_no, parts_no, koutei, plan_cnt
            FROM
                equip_work_log2_header
            WHERE
                mac_no={$mac_no}
                and siji_no={$siji_no}
                and koutei={$koutei} limit 1
        ";
        $res = array();
        if (($rows=getResult($query, $res)) >= 1) {
            $_SESSION['s_sysmsg'] = "<font color='yellow'>{$mac_name} �����ֹ�:{$mac_no} �ؼ��ֹ�:$siji_no ����:$koutei �ϲ��Υǡ����Ƚ�ʣ���Ƥ��ޤ�</font>";
        } else {
            ////////// ��Ͽ�¹�
            $str_timestamp = date('Y-m-d H:i:s');
            add_equip_header($mac_no, $siji_no, $parts_no, $koutei, $plan_cnt, $str_timestamp);
            //equip_header_to_csv();
            if ($_SESSION['s_sysmsg'] == '') {
                $_SESSION['s_sysmsg'] = "{$mac_name} �����ֹ� : {$mac_no} \\n\\n�ؼ��ֹ� : {$siji_no} \\n\\n���� : {$koutei}\\n\\n �Ǳ�ž���Ϥ��ޤ�����";
            }
        }
    }
    $equipment_select = 'init_data_input';  // ��ž���� ���̤�
    $init_data_input = '';                  // �ؼ��ֹ����ϲ���
    break;
}

/***** ���ѥ����ƥ�ޥ����������å��ؿ� *****/
function partsNoCheck($parts_no)
{
    $query = "SELECT trim(substr(midsc, 1, 26)) AS name FROM miitem WHERE mipn='{$parts_no}'";
    if (getUniResult($query, $name) <= 0) {
        $_SESSION['s_sysmsg'] = "{$parts_no} �ϥޥ�����̤��Ͽ�Ǥ���";
        return '';
    } else {
        return $name;
    }
}
/////////////////// ��ž���� ��ǧ�ǡ�������
if ($init_data_input == '��ǧ') {                   // �����ǡ����ɲ�
    $equipment_select = 'init_data_input';
    $query = "SELECT inst.inst_no
                , inst.mac_no
                , inst.koutei
                , inst.pro_mark
                , inst.parts_no
                , trim(substr(item.midsc, 1, 26)) as name
                , inst_h.inst_qt
                , mast.mac_name
            FROM
                equip_work_instruction as inst
            LEFT OUTER JOIN
                equip_work_inst_header as inst_h
            USING
                (inst_no)
            LEFT OUTER JOIN
                equip_machine_master2 as mast
            ON
                inst.mac_no = mast.mac_no
            LEFT OUTER JOIN
                miitem as item
            ON
                inst.parts_no = item.mipn
            WHERE
                inst_no={$siji_no} -- 2007/07/17 and koutei=1
        ";
    $res = array();
    if (($rows=getResult2($query, $res)) <= 0) {    // ���ؼ��ι������٤˥ǡ��������뤫�����å�
        $_SESSION['s_sysmsg'] = "<font color='yellow'>�ؼ��ֹ桧 {$siji_no}<br> �ǡ���������ޤ���<br> �����Ϥ��Ʋ�������</font>";
        $inst_no  = $siji_no;
        $mac_no[0]   = '';
        $koutei[0]   = '';
        $pro_mark[0] = '';
        $parts_no[0] = '';
        $name[0]     = '';
        $plan_cnt[0] = '';
        $init_data_input = '������';
    } else {
        for ($i=0; $i<$rows; $i++) {
            $inst_no  = $res[$i][0];
            $mac_no[$i]   = $res[$i][1];
            $koutei[$i]   = $res[$i][2];
            $pro_mark[$i] = $res[$i][3];
            $parts_no[$i] = $res[$i][4];
            $name[$i]     = $res[$i][5];
            $plan_cnt[$i] = $res[$i][6];
        }
    }
}

///////////////////////////////// ��ž���� CSV�Ϻ�����ƥǡ�����ƺ��� equip_work_log2_header��work_flg IS FALSE
if ($init_data_cut != '') {
    $query = "select mac_no, siji_no, koutei, parts_no
                from equip_work_log2_header
                where work_flg IS TRUE and mac_no=$m_no and siji_no=$s_no and koutei=$k_no";
    $res = array();
    if (($rows=getResult($query,$res)) >= 1) {          // �ǡ����١����Υإå�����걿ž��Υǡ���������å�
        break_equip_header($m_no, $s_no, $b_no, $k_no, FALSE);
        //equip_header_to_csv();
    } else {
        $_SESSION['s_sysmsg'] = "�����ֹ�:$m_no �ؼ��ֹ�:$s_no �����ֹ�:$b_no ����:$k_no �Ǥ���Ͽ����Ƥ��ޤ���";
    }
    $equipment_select = 'init_data_cut';    // ��ž���� ���̤�
    $init_data_cut = '';                    // ��ž���Ǥΰ��� ���̤�
}

//////////////////////////////////// �ؼ��ѹ� �������뤿��ǡ�����ƺ���
if ($init_data_edit != '') {
    /************************** ���ߤϻ��Ѥ��Ƥ��ʤ�
    $query = "select mac_no
                    , siji_no
                    , parts_no
                    , koutei
                from
                    equip_work_log2_header
                where
                    work_flg IS TRUE and mac_no={$m_no} and siji_no={$s_no} and koutei={$k_no}
            ";
    $res = array();
    if (($rows=getResult($query,$res)) >= 1) {          // �ǡ����١����Υإå�����걿ž��Υǡ���������å�
        chg_equip_header_work($m_no, $s_no, $b_no, $k_no, $mac_no, $siji_no, $parts_no, $koutei, $plan_cnt);
        equip_header_to_csv();
    } else {
        $_SESSION['s_sysmsg'] = "�����ֹ�:$m_no �ؼ��ֹ�:$s_no ����:$k_no �Ǥ���Ͽ����Ƥ��ޤ���";
    }
    ***************************/
}

//////////////////////////////////// �ù���λ
if ($init_data_end != '') {
    $query = "select mac_no,siji_no,parts_no,koutei from equip_work_log2_header where work_flg=TRUE 
            and mac_no='$m_no' and siji_no='$s_no' and parts_no='$b_no' and koutei='$k_no'";
    $res = array();
    if (($rows=getResult($query, $res)) >= 1) {         // �ǡ����١����Υإå�����걿ž��Υǡ���������å�
        end_equip_header($m_no,$s_no,$b_no,$k_no,$_POST['jisseki']);
        //equip_header_to_csv();
    } else {
        $_SESSION['s_sysmsg'] = "�����ֹ�:$m_no �ؼ��ֹ�:$s_no �����ֹ�:$b_no ����:$k_no �Ǥ���Ͽ����Ƥ��ޤ���";
    }
    $equipment_select = 'init_data_end';    // �ù���λ���̤�ݻ�����
}

//////////////////////////////////// ͽ��ײ��걿ž����
if ($plan_to_start != '') {
    $query_plan = "select mac_no,siji_no,parts_no,koutei from equip_plan where plan_flg=TRUE 
            and mac_no='$m_no' and siji_no='$s_no' and parts_no='$b_no' and koutei='$k_no'";
    $query_header = "select mac_no,siji_no,parts_no,koutei from equip_work_log2_header where 
                mac_no='$m_no' and siji_no='$s_no' and parts_no='$b_no' and koutei='$k_no'";
    $query_header_mac = "select mac_no,siji_no,parts_no,koutei from equip_work_log2_header where 
                work_flg=TRUE and mac_no='$m_no' limit 1";
    $res=array();
    if (($rows=getResult($query_plan, $res)) >= 1) {                // equip_plan �Υǡ���������å�
        if (($rows=getResult($query_header, $res)) == 0) {          // equip_work_log2_header �����ǡ���������å�
            if (($rows=getResult($query_header_mac,$res)) == 0) {   // equip_work_log2_header �α�ž�浡���������å�
                trans_equip_plan_to_start($m_no,$s_no,$b_no,$k_no,$p_no);   // Transaction ����
                //equip_header_to_csv();
            } else {
                $_SESSION['s_sysmsg'] = "�����ֹ�:$m_no �ϸ��߱�ž��Ǥ�";
            }
        } else {
            $_SESSION['s_sysmsg'] = "�����ֹ�:$m_no �ؼ��ֹ�:$s_no �����ֹ�:$b_no ����:$k_no �ϴ��˼��Ӥ�����ޤ�";
        }
    } else {
        $_SESSION['s_sysmsg'] = "�����ֹ�:$m_no �ؼ��ֹ�:$s_no �����ֹ�:$b_no ����:$k_no �Ǥ�ͽ��ײ�˸��Ĥ���ޤ���";
    }
}

////////////////////////////////////// ���ǥǡ����κƳ�
if ($break_restart != '') {
    $query = "select mac_no from equip_work_log2_header where mac_no='$m_no' and work_flg is TRUE and end_timestamp is NULL";
    $res = array();
    if(($rows=getResult($query, $res)) >= 1) {                  // �إå����˴��ˤʤ��������å�
        $_SESSION['s_sysmsg'] = "<font color='yellow'>�����ֹ� = $m_no �ϸ��߲�ư��Ǥ�</font>";
    } else {
        break_equip_header($m_no, $s_no, $b_no, $k_no, TRUE);       // TRUE=�Ƴ�
        //equip_header_to_csv();
    }
    $equipment_select = 'break_data';   // ���Ƿײ� ���̤�ݻ�����
}
////////////////////////////////////// ���ǥǡ����δ������
if ($break_del != '') {
    del_equip_header_work($m_no,$s_no,$b_no,$k_no);     // ���(�ȥ�󥶥���������)
    //equip_header_to_csv();
    $equipment_select = 'break_data';   // ���Ƿײ� ���̤�ݻ�����
}

///////////// ��ž���ϻ��ε����⡦̾�Τ�ޥ������������
if ($equipment_select == 'init_data_input') {
    $query = "select mac_no, mac_name
                from
                    equip_machine_master2
                where
                    survey='Y'
                    {$fact_where}
                order by mac_no ASC
    ";
    $mac_res = array();
    if ( ($mac_rows=getResult($query, $mac_res)) <= 0) {
        $_SESSION['s_sysmsg'] = '�����ޥ������μ����˼���';
    } else {
        $mac_name = array();
        for ($i=0; $i<$mac_rows; $i++) {
            $mac_name[$i] = $mac_res[$i][0] . ' ' . $mac_res[$i][1];
        }
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
<meta http-equiv="Content-Script-Type" content="text/javascript">
<title><?php echo $menu->out_title() ?></title>
<?php echo $menu->out_css() ?>
<?php echo $menu->out_jsBaseClass() ?>
<?php // echo $menu->out_site_java() ?>
<style type="text/css">
<!--
select {
    background-color:   teal;
    color:              white;
}
.pt10 {
    font-size:      0.85em;
}
.pt11b {
    font-size:      0.95em;
    font-weight:    bold;
}
.pt12b {
    font-size:      1.05em;
    font-weight:    bold;
}
.right {
    text-align:right;
}
.center {
    text-align:center;
}
.left {
    text-align:left;
}
.margin1 {
    margin:1%;
}
.margin0 {
    margin:0%;
}
.fc_red {
    color:              red;
    background-color:   blue;
}
.fc_gray {
    background-color:   #d6d3ce;
    border:             0px none #d6d3ce;
}
.fc_yellow {
    color:              yellow;
    background-color:   blue;
}
.fc_white {
    color:              white;
    background-color:   blue;
}
caption {
    font-size:   0.95em; /* 11pt */
    font-weight: bold;
}
th {
    font-size:          0.95em;
    font-weight:        bold;
    color:              blue;
    background-color:   yellow;
}
input.number {
    width:              30px;
    font-size:          0.9em;
    font-weight:        bold;
    color:              blue;
}
input.editButton {
    font-size:          0.9em;
    font-weight:        bold;
    color:              blue;
}
.siji {
    font-size:          1.0em;
    font-weight:        bold;
}
-->
</style>
<script language='JavaScript' src='../equipment.js'></script>
<script language='JavaScript'>
<!--
    <?php
    if ($equipment_select == 'init_data_input') {
        if ($init_data_input == '') {
            echo 'function set_focus() {';
            echo '    document.siji_form.init_siji_no.focus();', "\n";
            echo '    document.siji_form.init_siji_no.select();', "\n";
            echo '}';
        } else if($init_data_input == '��ǧ') {
            echo 'function set_focus() {';
            echo '    document.siji_form.init_data_input.focus();', "\n";
            echo '}';
        } else {
            echo 'function set_focus() {';
            echo '}';
        }
    } else {
        echo 'function set_focus() {';
        echo '}';
    }
    ?>

function selectCopy(obj, obj2)
{
    for (var i=0; i<obj.options.length; i++) {
        if (obj.options[i].selected) {
            obj2.options[i].selected = true;
        } else {
            obj2.options[i].selected = false;
        }
    }
}
// -->
</script>

</head>
<body class='margin0' onLoad='set_focus()'>
    <center>
        <?php
        switch ($equipment_select) {
        case 'init_data_input':     // ���ϡʵ��� ��ž ���ϡ�
            if ($init_data_input == '') {
                echo "<table bgcolor='#d6d3ce' align='center' cellspacing='0' cellpadding='3' border='1'>\n";
                echo "<tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>\n";
                echo "<table bordercolordark='white' bordercolorlight='#bdaa90' bgcolor='#d6d3ce' align='center' cellspacing='0' cellpadding='3' border='1'>\n";
                echo "<form name='siji_form' action='", $current_script, "' method='post' onSubmit='return chk_equip_inst(this)'>\n";
                echo " <tr>\n";
                echo "     <td align='left' nowrap>\n";
                echo "         �ù� �ؼ��ֹ������\n";
                echo "         <input tabindex='1' type='text' class='siji' name='init_siji_no' size='6' value='$siji_no' maxlength='5'>\n";
                echo "     </td>\n";
                echo "     <td align='center' nowrap>\n";
                echo "         <input tabindex='1' type='submit' name='init_data_input' value='��ǧ'>\n";
                echo "     </td>\n";
                echo " </tr>\n";
                echo "</form>\n";
                echo "</table>\n";
                echo "    </td></tr>\n";
                echo "</table> <!----------------- ���ߡ�End ------------------>\n";
            } elseif ($init_data_input == '��ǧ') {
                echo "<table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>\n";
                echo "<tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>\n";
                echo "<table class='winbox_field' align='center' border='1' cellspacing='0' cellpadding='3'>\n";
                echo "<form name='siji_form' action='", $current_script, "' method='post'>\n";
                echo " <tr>\n";
                echo "     <th class='winbox' width='40'>1</th>\n";
                echo "     <td class='winbox' width='120' align='center' nowrap>\n";
                echo "         �ؼ��ֹ�\n";
                echo "     </td>\n";
                echo "     <td class='winbox' width='200' align='center' nowrap>\n";
                echo "         <input type='text' class='siji fc_gray' name='siji_no' size='6' value='{$inst_no}' maxlength='5' style='text-align: center;' readonly>\n";
                echo "     </td>\n";
                echo " </tr>\n";
                echo " <tr>\n";
                echo "     <th class='winbox'>2</th>\n";
                echo "     <td class='winbox' align='center' nowrap>\n";
                echo "         �����ֹ�\n";
                echo "     </td>\n";
                echo "     <td class='winbox' align='center' nowrap>\n";
                echo "         <select name='mac_no' class='siji' onChange='selectCopy(this, document.siji_form.macName);'>\n";
                for ($i=0; $i<$mac_rows; $i++) {
                    if ($i == 0) {
                        echo "         <option value=''>���򤷤Ʋ�����</option>\n";
                    }
                    if ($mac_no[0] == $mac_res[$i][0]) {
                        echo "        <option value='{$mac_res[$i][0]}' selected>{$mac_name[$i]}</option>\n";
                    } else {
                        echo "        <option value='{$mac_res[$i][0]}'>{$mac_name[$i]}</option>\n";
                    }
                }
                echo "         </select>\n";
                echo "     </td>\n";
                echo " </tr>\n";
                echo " <tr>\n";
                echo "     <th class='winbox'>3</th>\n";
                echo "     <td class='winbox' align='center' nowrap>\n";
                echo "         ����̾��\n";
                echo "     </td>\n";
                echo "     <td class='winbox' align='center' nowrap>\n";
                echo "         <select name='macName' class='siji' disabled>\n";
                for ($i=0; $i<$mac_rows; $i++) {
                    if ($i == 0) {
                        echo "         <option value=''>̤����</option>\n";
                    }
                    if ($mac_no[0] == $mac_res[$i][0]) {
                        echo "        <option value='{$mac_res[$i][0]}' selected>{$mac_res[$i][1]}</option>\n";
                    } else {
                        echo "        <option value='{$mac_res[$i][0]}'>{$mac_res[$i][1]}</option>\n";
                    }
                }
                echo "         </select>\n";
                echo "     </td>\n";
                echo " </tr>\n";
                echo " <tr>\n";
                echo "     <th class='winbox'>4</th>\n";
                echo "     <td class='winbox' align='center' nowrap>\n";
                echo "         �����ֹ�\n";
                echo "     </td>\n";
                echo "     <td class='winbox' align='center' nowrap>\n";
                echo "         <input type='text' class='siji fc_gray' name='parts_no' size='11' value='{$parts_no[0]}' maxlength='9' style='text-align: center;' readonly>\n";
                echo "     </td>\n";
                echo " </tr>\n";
                echo " <tr>\n";
                echo "     <th class='winbox'>5</th>\n";
                echo "     <td class='winbox' align='center' nowrap>\n";
                echo "         �� �� ̾\n";
                echo "     </td>\n";
                echo "     <td class='winbox' align='center' class='siji' nowrap>\n";
                echo "         {$name[0]}\n";
                echo "     </td>\n";
                echo " </tr>\n";
                echo " <tr>\n";
                echo "     <th class='winbox'>6</th>\n";
                echo "     <td class='winbox' align='center' nowrap>\n";
                echo "         �����ֹ�\n";
                echo "     </td>\n";
                echo "     <td class='winbox' align='center' nowrap>\n";
                echo "         <select name='koutei' class='siji' onChange='selectCopy(this, document.siji_form.kouteiName);'>\n";
                for ($i=0; $i<$rows; $i++) {
                    echo "             <option value='{$koutei[$i]}'>{$koutei[$i]}</option>\n";
                }
                echo "         </select>\n";
                echo "     </td>\n";
                echo " </tr>\n";
                echo " <tr>\n";
                echo "     <th class='winbox'>7</th>\n";
                echo "     <td class='winbox' align='center' nowrap>\n";
                echo "         ��������\n";
                echo "     </td>\n";
                echo "     <td class='winbox' align='center' nowrap>\n";
                echo "         <select name='kouteiName' class='siji' disabled>\n";
                for ($i=0; $i<$rows; $i++) {
                    echo "             <option value='{$pro_mark[$i]}'>{$pro_mark[$i]}</option>\n";
                }
                echo "         </select>\n";
                echo "     </td>\n";
                echo " </tr>\n";
                echo " <tr>\n";
                echo "     <th class='winbox'>8</th>\n";
                echo "     <td class='winbox' align='center' nowrap>\n";
                echo "         �� �� ��\n";
                echo "     </td>\n";
                echo "     <td class='winbox' align='center' nowrap>\n";
                echo "         <input type='text' class='siji fc_gray' name='plan_cnt' size='6' value='{$plan_cnt[0]}' maxlength='6' style='text-align: right;' readonly>\n";
                echo "     </td>\n";
                echo " </tr>\n";
                echo " <tr>\n";
                echo "     <td class='winbox' align='center' colspan='3'>\n";
                echo "         <input type='submit' name='init_data_input' value='��Ͽ'>\n";
                echo "             &nbsp;&nbsp;\n";
                echo "         <input type='submit' name='init_data_cancel' value='���'>\n";
                echo "     </td>\n";
                echo " </tr>\n";
                echo "</form>\n";
                echo "</table>\n";
                echo "    </td></tr>\n";
                echo "</table> <!----------------- ���ߡ�End ------------------>\n";
            } elseif ($init_data_input == '������') {
                echo "<table bgcolor='#d6d3ce' align='center' cellspacing='0' cellpadding='3' border='1'>\n";
                echo "<tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>\n";
                echo "<table bordercolordark='white' bordercolorlight='#bdaa90' bgcolor='#d6d3ce' align='center' cellspacing='0' cellpadding='3' border='1'>\n";
                echo "<form action='", $current_script, "' method='post' onSubmit='return chk_equipment_nippou(this)'>\n";
                echo " <tr>\n";
                echo "     <th width='40'>1</th>\n";
                echo "     <td width='300' align='left' nowrap>\n";
                echo "         �����ֹ������\n";
                echo "         <select name='mac_no' class='siji'>\n";
                for ($i=0; $i<$mac_rows; $i++) {
                    if ($mac_no == $mac_res[$i][0]) {
                        echo "        <option value='{$mac_res[$i][0]}' selected>{$mac_name[$i]}</option>\n";
                    } else {
                        echo "        <option value='{$mac_res[$i][0]}'>{$mac_name[$i]}</option>\n";
                    }
                }
                echo "         </select>\n";
                echo "     </td>\n";
                echo " </tr>\n";
                echo " <tr>\n";
                echo "     <th>2</th>\n";
                echo "     <td align='left' nowrap>\n";
                echo "         �ù� �ؼ��ֹ桡\n";
                echo "         <input type='text' name='siji_no' class='siji fc_gray' size='6' value='$siji_no' maxlength='5' readonly>\n";
                echo "     </td>\n";
                echo " </tr>\n";
                echo " <tr>\n";
                echo "     <th>3</th>\n";
                echo "     <td align='left' nowrap>\n";
                echo "         �����ֹ������\n";
                echo "         <input type='text' name='parts_no' class='siji' size='11' value='{$parts_no[0]}' maxlength='9' onKeyUp='baseJS.keyInUpper(this);'>\n";
                echo "         {$name[0]}\n";
                echo "     </td>\n";
                echo " </tr>\n";
                echo " <tr>\n";
                echo "     <th>4</th>\n";
                echo "     <td align='left' nowrap>\n";
                echo "         �����ֹ������\n";
                echo "         <input type='text' name='koutei' class='siji' size='3' value='{$koutei[0]}' maxlength='1'>\n";
                echo "     </td>\n";
                echo " </tr>\n";
                echo " <tr>\n";
                echo "     <th>5</th>\n";
                echo "     <td align='left' nowrap>\n";
                echo "         �����ײ��������\n";
                echo "         <input type='text' name='plan_cnt' class='siji' size='8' value='{$plan_cnt[0]}' maxlength='7'>\n";
                echo "     </td>\n";
                echo " </tr>\n";
                echo " <tr>\n";
                echo "     <td align='center' colspan='3'>\n";
                echo "         <input type='submit' name='init_data_input' value='��Ͽ'>\n";
                echo "             &nbsp;&nbsp;\n";
                echo "         <input type='button' name='init_data_cancel' value='���' onClick='location.replace(\"{$current_script}?init_data_cancel=yes\");'>\n";
                echo "     </td>\n";
                echo " </tr>\n";
                echo "</form>\n";
                echo "</table>\n";
                echo "    </td></tr>\n";
                echo "</table> <!----------------- ���ߡ�End ------------------>\n";
            }
            break;
        case 'init_data_cut':       // ��ž���ǡʵ��� ��ž ��ߡ�
            $query = "select mac_no
                            , m.mac_name
                            , siji_no
                            , parts_no
                            , koutei
                            , plan_cnt
                            , to_char(str_timestamp AT TIME ZONE 'JST', 'YYYY/MM/DD HH24:MI:SS') as str_timestamp
                        from
                            equip_work_log2_header
                        left outer join
                            equip_machine_master2 as m
                        using(mac_no)
                        where
                            work_flg IS TRUE and end_timestamp IS NULL
                            {$fact_where}
                        order by
                            str_timestamp DESC
                    ";
            $res = array();
            if ( ($rows = getResult($query, $res)) >= 1) {  // �ǡ����١����Υإå�����걿ž��ǡ��������
                echo "<table bgcolor='#d6d3ce' align='center' cellspacing='0' cellpadding='3' border='1'>\n";
                echo "<tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>\n";
                echo "<table bordercolordark='white' bordercolorlight='#bdaa90' bgcolor='#d6d3ce' align='center' cellspacing='0' cellpadding='3' border='1'>\n";
                // echo "<caption>���� �ù����ʪ�����Ǥ��롣</caption>\n";
                echo " <th width='40' class='fc_white'>����</th>
                        <th width='80'>�����ֹ�</th><th width='80'>����̾</th><th width='80'>�ؼ��ֹ�</th>
                        <th width='80'>�����ֹ�</th><th width='40'>����</th><th width='80'>�ײ��</th>
                        <th nowrap>���� ǯ���� ����</th>\n";
                for ($r=0; $r<$rows; $r++) {
                    echo "<form name='cut_form' action='", $current_script, "' method='post' onSubmit='return chk_cut_form(this)'>\n";
                    echo "<input type='hidden' name='m_no' value='" . $res[$r][0] . "'>\n";
                    echo "<input type='hidden' name='m_name' value='" . $res[$r][1] . "'>\n";
                    echo "<input type='hidden' name='s_no' value='" . $res[$r][2] . "'>\n";
                    echo "<input type='hidden' name='b_no' value='" . $res[$r][3] . "'>\n";
                    echo "<input type='hidden' name='k_no' value='" . $res[$r][4] . "'>\n";
                    echo "<tr>\n";
                    echo " <td align='center'>
                                <input type='submit' class='number' name='init_data_cut' value='" . ($r + 1) . "'>
                            </td>\n";
                    echo " <td align='center' nowrap>" . $res[$r][0] . "</td>\n";
                    echo " <td align='left' nowrap>" . $res[$r][1] . "</td>\n";
                    echo " <td align='center' nowrap>" . $res[$r][2] . "</td>\n";
                    echo " <td align='center' nowrap>" . $res[$r][3] . "</td>\n";
                    echo " <td align='center' nowrap>" . $res[$r][4] . "</td>\n";
                    echo " <td align='right' nowrap>" . number_format($res[$r][5]) . "</td>\n";
                    echo " <td align='center' nowrap>" . $res[$r][6] . "</td>\n";
                    echo "</tr>\n";
                    echo "</form>\n";
                }
                echo "</table>\n";
                echo "    </td></tr>\n";
                echo "</table> <!----------------- ���ߡ�End ------------------>\n";
            }
            break;
        case 'init_data_edit':      // �����ʲù��ؼ��ѹ���
            $query = "select mac_no
                            , m.mac_name
                            , siji_no
                            , parts_no
                            , koutei
                            , plan_cnt
                            , to_char(str_timestamp AT TIME ZONE 'JST', 'YYYY/MM/DD HH24:MI:SS') as str_timestamp
                        from
                            equip_work_log2_header
                        left outer join
                            equip_machine_master2 as m
                        using(mac_no)
                        where
                            work_flg IS TRUE and end_timestamp is NULL
                            {$fact_where}
                        order by
                            str_timestamp DESC
                    ";
            $res = array();
            if (($rows=getResult($query,$res)) >= 1) {  // �ǡ����١����Υإå�����걿ž��ǡ��������
                echo "<table bgcolor='#d6d3ce' align='center' cellspacing='0' cellpadding='3' border='1'>\n";
                echo "<tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>\n";
                echo "<table bordercolordark='white' bordercolorlight='#bdaa90' bgcolor='#d6d3ce' align='center' cellspacing='0' cellpadding='3' border='1'>\n";
                // echo "<caption>���ϥǡ����ν���</caption>\n";
                echo " <th width='20' nowrap>No.</th><th width='40' class='fc_yellow'>�Խ�</th>
                        <th width='80'>�����ֹ�</th><th width='80'>����̾</th><th width='80'>�ؼ��ֹ�</th>
                        <th width='80'>�����ֹ�</th><th width='40'>����</th><th width='80'>�ײ��</th>
                        <th>���� ǯ���� ����</th>\n";
                for ($r=0; $r<$rows; $r++) {
                    echo "<form action='", $menu->out_action('work_edit'), "' method='post' target='application'>\n";
                    echo "<input type='hidden' name='mac_no' value='" . $res[$r][0] . "'>\n";
                    echo "<input type='hidden' name='siji_no' value='" . $res[$r][2] . "'>\n";
                    echo "<input type='hidden' name='koutei' value='" . $res[$r][4] . "'>\n";
                    echo "<tr>\n";
                    $num = $r+1;
                    echo "<td align='center'>$num</td>\n";
                    echo "<td align='center'><input type='submit' class='editButton' name='init_data_edit' value='�ѹ�'></td>\n";
                    echo "<td align='center' nowrap>" . $res[$r][0] . "<input type='hidden' name='mac_no' size='4' value='" . $res[$r][0] . "' maxlength='4' class='center'></td>\n";
                    echo "<td align='left' nowrap>" . $res[$r][1] . "</td>\n";
                    echo " <td align='center' nowrap>" . $res[$r][2] . "</td>\n";
                    echo " <td align='center' nowrap>" . $res[$r][3] . "</td>\n";
                    echo " <td align='center' nowrap>" . $res[$r][4] . "</td>\n";
                    echo " <td align='right' nowrap>" . number_format($res[$r][5]) . "</td>\n";
                    echo " <td align='center' nowrap>" . $res[$r][6] . "</td>\n";
                    echo "</tr>\n";
                    echo "</form>\n";
                }
                echo "</table>\n";
                echo "    </td></tr>\n";
                echo "</table> <!----------------- ���ߡ�End ------------------>\n";
            }
            break;
        case 'init_data_end':       // �ù���λ
            $query = "select mac_no
                            ,m.mac_name
                            ,h.siji_no
                            ,h.parts_no
                            ,h.koutei
                            ,h.plan_cnt
                            , to_char(str_timestamp AT TIME ZONE 'JST', 'YYYY/MM/DD HH24:MI:SS') as str_timestamp
                        from
                            equip_work_log2_header as h
                        left outer join
                            equip_machine_master2 as m
                        using(mac_no)
                        where
                            work_flg IS TRUE and end_timestamp IS NULL
                            {$fact_where}
                        order by
                            str_timestamp DESC
                    ";
            $res = array();
            if (($rows=getResult($query,$res)) >= 1) {  // �ǡ����١����Υإå�����걿ž��ǡ��������
                echo "<table bgcolor='#d6d3ce' align='center' cellspacing='0' cellpadding='3' border='1'>\n";
                echo "<tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>\n";
                echo "<table bordercolordark='white' bordercolorlight='#bdaa90' bgcolor='#d6d3ce' align='center' cellspacing='0' cellpadding='3' border='1'>\n";
                // echo "<caption>�ù� ��λ �ؼ�</caption>\n";
                echo " <th width='40' class='fc_white'>��λ</th><th width='80'>�����ֹ�</th><th width='80'>����̾</th><th width='80'>�ؼ��ֹ�</th><th width='80'>�����ֹ�</th><th width='80'>�����ֹ�</th><th width='80'>�ײ��</th><th width='80'>���ӿ�</th>\n";
                for ($r=0; $r<$rows; $r++) {
                    echo "<form action='", $current_script, "' method='post' onSubmit='return chk_end_inst(this)'>\n";
                    echo "<input type='hidden' name='m_no' value='" . $res[$r][0] . "'>\n";
                    echo "<input type='hidden' name='m_name' value='" . $res[$r][1] . "'>\n";
                    echo "<input type='hidden' name='s_no' value='" . $res[$r][2] . "'>\n";
                    echo "<input type='hidden' name='b_no' value='" . $res[$r][3] . "'>\n";
                    echo "<input type='hidden' name='k_no' value='" . $res[$r][4] . "'>\n";
                    echo "<tr>\n";
                    echo " <td align='center'>
                                <input type='submit' class='number' name='init_data_end' value='" . ($r + 1) . "'>
                            </td>\n";
                    echo " <td align='center' nowrap>" . $res[$r][0] . "</td>\n";
                    echo " <td align='left' nowrap>" . $res[$r][1] . "</td>\n";
                    echo " <td align='center' nowrap>" . $res[$r][2] . "</td>\n";
                    echo " <td align='center' nowrap>" . $res[$r][3] . "</td>\n";
                    echo " <td align='center' nowrap>" . $res[$r][4] . "</td>\n";
                    echo " <td align='right' nowrap>" . number_format($res[$r][5]) . "</td>\n";
                    echo " <td align='center' nowrap><input type='text' name='jisseki' size='8' value='" . $res[$r][5] . "' maxlength='7' class='right'></td>\n";
                    echo "</tr>\n";
                    echo "</form>\n";
                }
                echo "</table>\n";
                echo "    </td></tr>\n";
                echo "</table> <!----------------- ���ߡ�End ------------------>\n";
            }
            break;
        case 'plan_data':       // ͽ��ײ�ϥ������塼�顼�ذܹԤ��뤿��ʲ��ϻȤ�ʤ�
            $query = "select mac_no, siji_no, buhin_no, koutei, plan_su, plan_str, plan_end
                        from
                            equip_plan
                        where
                            plan_flg IS TRUE
                            -- {$fact_where}
                        order by plan_str, plan_end
            ";
            $res = array();
            if (($rows=getResult($query, $res)) >= 1) {     // equip_plan ���ͽ��ײ�����
                echo "<table bgcolor='#d6d3ce' align='center' cellspacing='0' cellpadding='3' border='1'>\n";
                echo "<tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>\n";
                echo "<table bordercolordark='white' bordercolorlight='#bdaa90' bgcolor='#d6d3ce' align='center' cellspacing='0' cellpadding='3' border='1'>\n";
                // echo "<caption>ͽ��ײ��걿ž���ϻؼ�</caption>\n";
                echo " <th width='40' class='fc_white'>����</th><th width='80'>�����ֹ�</th><th width='80'>�ؼ��ֹ�</th><th width='80'>�����ֹ�</th><th width='80'>�����ֹ�</th><th width='80'>�ײ��</th><th nowrap>���� ǯ����</th><th nowrap>��λ ǯ����</th>\n";
                for ($r=0; $r<$rows; $r++) {
                    echo "<form action='", $current_script, "' method='post'>\n";
                    echo "<input type='hidden' name='m_no' value='" . $res[$r][0] . "'>\n";
                    echo "<input type='hidden' name='s_no' value='" . $res[$r][1] . "'>\n";
                    echo "<input type='hidden' name='b_no' value='" . $res[$r][2] . "'>\n";
                    echo "<input type='hidden' name='k_no' value='" . $res[$r][3] . "'>\n";
                    echo "<input type='hidden' name='p_no' value='" . $res[$r][4] . "'>\n";
                    echo "<tr>\n";
                    echo " <td align='center'><input type='submit' class='number' name='plan_to_start' value='" . ($r + 1) . "'></td>\n";
                    echo " <td align='center' nowrap>" . $res[$r][0] . "</td>\n";
                    echo " <td align='center' nowrap>" . $res[$r][1] . "</td>\n";
                    echo " <td align='center' nowrap>" . $res[$r][2] . "</td>\n";
                    echo " <td align='center' nowrap>" . $res[$r][3] . "</td>\n";
                    echo " <td align='right' nowrap>" . $res[$r][4] . "</td>\n";
                    echo " <td align='center' nowrap>" . date("Y/m/d",$res[$r][5]) . "</td>\n";
                    echo " <td align='center' nowrap>" . date("Y/m/d",$res[$r][6]) . "</td>\n";
                    echo "</tr>\n";
                    echo "</form>\n";
                }
                echo "</table>\n";
                echo "    </td></tr>\n";
                echo "</table> <!----------------- ���ߡ�End ------------------>\n";
            }
            break;
        case 'break_data':      // ���Ƿײ�
            $query = "
                SELECT mac_no
                    , m.mac_name
                    , siji_no
                    , parts_no
                    , koutei
                    , plan_cnt
                    , to_char(str_timestamp AT TIME ZONE 'JST', 'YYYY/MM/DD HH24:MI:SS') AS str_timestamp
                    , to_char(str_timestamp AT TIME ZONE 'JST', 'YYYYMMDDHH24MISS') AS searchTime
                FROM
                    equip_work_log2_header AS h
                LEFT OUTER JOIN
                    equip_machine_master2 AS m
                USING(mac_no)
                WHERE
                    work_flg IS FALSE AND end_timestamp IS NULL
                    {$fact_where}
                ORDER BY str_timestamp DESC
            ";
            $res = array();
            if (($rows=getResult($query,$res)) >= 1) {  // �ǡ����١����Υإå������������Ƿײ�����
                echo "<table bgcolor='#d6d3ce' align='center' cellspacing='0' cellpadding='3' border='1'>\n";
                echo "<tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>\n";
                echo "<table bordercolordark='white' bordercolorlight='#bdaa90' bgcolor='#d6d3ce' align='center' cellspacing='0' cellpadding='3' border='1'>\n";
                echo "<caption>���� ���Ǥ���Ƥ���ײ� (����ϴ�������ʤΤ����)</caption>\n";
                echo " <th width='40' class='fc_white'>�Ƴ�</th><th width='40' class='fc_red'>���</th>
                        <th width='70'>�����ֹ�</th><th width='80'>����̾</th><th width='70'>�ؼ��ֹ�</th>
                        <th width='80'>�����ֹ�</th><th width='40'>����</th><th width='80'>�ײ��</th>
                        <th nowrap>���� ǯ���� ����</th>
                        <th nowrap>���� �ؼ� ����</th>\n";
                for ($r=0; $r<$rows; $r++) {
                    ///// ���������ǻ��֤��������
                    $query = "select to_char(date_time AT TIME ZONE 'JST', 'YYYY/MM/DD HH24:MI:SS') as cut_timestamp
                                from
                                    equip_work_log2
                                where
                                    equip_index(mac_no, siji_no, koutei, date_time) >= '{$res[$r][0]}{$res[$r][2]}{$res[$r][4]}{$res[$r][7]}'
                                    -- date_time > '{$res[$r][6]}'
                                and
                                    equip_index(mac_no, siji_no, koutei, date_time) <  '{$res[$r][0]}{$res[$r][2]}{$res[$r][4]}99999999'
                                    -- mac_no={$res[$r][0]} and siji_no={$res[$r][2]} and koutei={$res[$r][4]} and mac_state=9 -- ����
                                order by
                                    equip_index(mac_no, siji_no, koutei, date_time) DESC
                                    -- date_time DESC
                                offset 0 limit 1
                    ";
                    if (getUniResult($query, $cut_timestamp) <= 0) {
                        $cut_timestamp = '��';
                    }
                    echo "<form name='break_form' action='", $current_script, "' method='post'>\n";
                    echo "<input type='hidden' name='m_no' value='" . $res[$r][0] . "'>\n";
                    echo "<input type='hidden' name='m_name' value='" . $res[$r][1] . "'>\n";
                    echo "<input type='hidden' name='s_no' value='" . $res[$r][2] . "'>\n";
                    echo "<input type='hidden' name='b_no' value='" . $res[$r][3] . "'>\n";
                    echo "<input type='hidden' name='k_no' value='" . $res[$r][4] . "'>\n";
                    echo "<input type='hidden' name='p_no' value='" . $res[$r][5] . "'>\n";
                    echo "<tr>\n";
                    echo " <td align='center'>
                                <input type='submit' class='number' name='break_restart' value='" . ($r + 1) . "' onClick='return chk_break_restart(m_no.value, m_name.value, s_no.value, b_no.value)'>
                            </td>\n";
                    echo " <td align='center'>
                                <input type='submit' class='number' name='break_del' value='" . ($r + 1) . "' onClick='return chk_break_del(m_no.value, m_name.value, s_no.value, b_no.value)'>
                            </td>\n";
                    echo " <td align='center' nowrap>" . $res[$r][0] . "</td>\n";
                    echo " <td align='left' nowrap>" . $res[$r][1] . "</td>\n";
                    echo " <td align='center' nowrap>" . $res[$r][2] . "</td>\n";
                    echo " <td align='center' nowrap>" . $res[$r][3] . "</td>\n";
                    echo " <td align='center' nowrap>" . $res[$r][4] . "</td>\n";
                    echo " <td align='right' nowrap>" . number_format($res[$r][5]) . "</td>\n";
                    echo " <td align='center' nowrap>" . $res[$r][6] . "</td>\n";
                    echo " <td align='center' nowrap>{$cut_timestamp}</td>\n";
                    echo "</tr>\n";
                    echo "</form>\n";
                }
                echo "</table>\n";
                echo "    </td></tr>\n";
                echo "</table> <!----------------- ���ߡ�End ------------------>\n";
            }
            break;
        default:            // ���߲ù���ǡ���ɽ��
            echo "<table bgcolor='#d6d3ce' align='center' cellspacing='0' cellpadding='3' border='1'>\n";
            // echo "<caption>���� �ù���</caption>\n";
            echo "<tr><td>\n";  // ���ߡ�
            echo "<table bordercolordark='white' bordercolorlight='#bdaa90' bgcolor='#d6d3ce' align='center' cellspacing='0' cellpadding='3' border='1'>\n";
            echo " <th>No.</th><th>�����ֹ�</th><th>����̾</th><th>�ؼ��ֹ�</th>
                    <th>�����ֹ�</th><th>����̾</th><th>����</th><th>�ײ��</th><th>���� ǯ���� ����</th>
                    <th>CSV</th>\n";
            
            ///////////////////// ��¼α��CSV�ե��������Ѥ���ɽ�����뎡-->CSV�ν��ϥ����å�(*)�Τߤ��ѹ�
            if ( ($fp = fopen(EQUIP_INDEX, 'r')) ) {
                $row_csv = 0;
                while ($csv_data[$row_csv] = fgetcsv ($fp, 100, ",")) {
                    $row_csv++;
                }
                fclose ($fp);
            } else {
                $row_csv = 0;
                $_SESSION['s_sysmsg'] = 'CSV�ե�����򳫤����Ȥ�����ޤ���';
            }
            
            //////////////////// ��¼α �ʳ� �إå����ե��������Ѥ���ɽ�����뎡
            $query = "select mac_no
                            , mac_name
                            , siji_no
                            , parts_no
                            , midsc
                            , koutei
                            , plan_cnt
                            , to_char(str_timestamp AT TIME ZONE 'JST', 'YYYY/MM/DD HH24:MI:SS') as str_timestamp
                    from (
                                equip_work_log2_header
                            left outer join
                                equip_machine_master2
                            using(mac_no)
                        )
                        left outer join
                            miitem
                        on parts_no=mipn
                    where
                        work_flg is TRUE
                        {$fact_where}
                        -- and csv_flg != '1'
                    order by
                        str_timestamp DESC
                    ";
            $res_list = array();
            if ( ($rows_list=getResult2($query, $res_list)) >= 1) {
                $r = 1;     // ���ֹ�
                foreach ($res_list as $row) {
                    echo "<tr>\n";
                    echo "<td align='center' nowrap>$r</td>\n";
                    $r++;
                    $c = 0;
                    foreach ($row as $col) {
                        if ($c == 6) {                  // �ײ��
                            printf("<td width='80' align='right' nowrap>%s</td>\n", number_format($col));
                        } elseif ($c == 4) {            // ����̾��ɽ������
                            if ($col != '') {
                                printf("<td align='left' nowrap>%s</td>\n", mb_substr($col, 0,10) ); // ����̾
                            } else {
                                echo "<td align='center' nowrap>-----</td>\n";
                            }
                        } elseif ($c == 1) {            // ����̾
                            echo "<td align='left' nowrap>$col</td>\n";
                        } elseif ($c == 7) {
                            printf("<td align='center' nowrap>%s</td>\n", $col);
                        } else {
                            printf("<td align='center' nowrap>%s</td>\n", $col);
                            if ($c == 0) {
                                $chk_mac_no = $col;         // �����ֹ�
                            } elseif ($c == 2) {
                                $chk_siji_no = $col;        // �ؼ��ֹ�
                            } elseif ($c == 5) {
                                $chk_koutei = $col;         // �����ֹ�
                            }
                        }
                        $c++;
                    }
                    $csv_chk = FALSE;
                    for ($i=0; $i<$row_csv; $i++) {
                        if ( ((int)$csv_data[$i][0] == (int)$chk_mac_no) && ((int)$csv_data[$i][1] == (int)$chk_siji_no) && ((int)$csv_data[$i][3] == (int)$chk_koutei) ) {
                            $csv_chk = TRUE;
                            break;
                        }
                    }
                    if ($csv_chk) {
                        echo "<td align='center' nowrap>*</td>\n";     // CSV(��α��netmoni)���Ϥ���Ƥ���
                    } else {
                        echo "<td align='center' nowrap>��</td>\n";     // CSV���Ϥ���Ƥ��ʤ�
                    }
                    echo "</tr>\n";
                }
            }
            echo "</table>\n";
            echo "</td></tr>\n";  // ���ߡ�
            echo "</table>\n";
            break;      // ɬ�פʤ������
        }
        ?>
    </center>
</body>
<?php echo $menu->out_retF2Script()?>
<?php echo $menu->out_alert_java(FALSE)?>
</html>
<?php
ob_end_flush();
?>
