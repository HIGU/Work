<?php
//////////////////////////////////////////////////////////////////////////////
// ��¤�����׻� ������ ���� �Ȳ�                                            //
// Copyright (C) 2017-2017 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2017/09/11 Created   cost_material_view.php                              //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
// ini_set('zend.ze1_compatibility_mode', '1');    // zend 1.X ����ѥ� php4�θߴ��⡼��
// ini_set('implicit_flush', 'off');           // echo print �� flush �����ʤ�(�٤��ʤ뤿��) CLI CGI��
// ini_set('max_execution_time', 1200);        // ����¹Ի���=20ʬ CLI CGI��
ob_start('ob_gzhandler');                   // ���ϥХåե���gzip����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��

require_once ('../function.php');            // define.php �� pgsql.php �� require_once ���Ƥ���
require_once ('../tnk_func.php');            // TNK �˰�¸������ʬ�δؿ��� require_once ���Ƥ���
require_once ('../MenuHeader.php');          // TNK ������ menu class
require_once ('../ControllerHTTP_Class.php');// TNK ������ MVC Controller Class
//////////// ���å����Υ��󥹥��󥹤���Ͽ
$session = new Session();
if (isset($_REQUEST['recNo'])) {
    $session->add_local('recNo', $_REQUEST['recNo']);
    exit();
}
access_log();                               // Script Name �ϼ�ư����

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader(0);                  // ǧ�ڥ����å�0=���̰ʾ� �����=TOP_MENU �����ȥ�̤����

////////////// ����������
//$menu->set_site( 1, 11);                    // site_index=01(����˥塼) site_id=11(����������)
////////////// �꥿���󥢥ɥ쥹����
//$menu->set_RetUrl(SYS_MENU);                // �̾�ϻ��ꤹ��ɬ�פϤʤ�
//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
//$menu->set_title('�� �� �� �� �� ��');
//////////// �ƽ����action̾�ȥ��ɥ쥹����

//////////// �֥饦�����Υ���å����к���
$uniq = $menu->set_useNotCache('target');

//////////// POST�ǡ�������
if (isset($_REQUEST['nk_ki'])) {
    $nk_ki = $_REQUEST['nk_ki'];
} else {
    $nk_ki = $_SESSION['nk_ki'];
}
if (isset($_REQUEST['str_ym'])) {
    $str_ym = $_REQUEST['str_ym'];
} else {
    $str_ym = $_SESSION['str_ym'];
}
if (isset($_REQUEST['end_ym'])) {
    $end_ym = $_REQUEST['end_ym'];
} else {
    $end_ym = $_SESSION['end_ym'];
}
if (isset($_REQUEST['2ki_ym'])) {
    $_SESSION['2ki_ym'] = $_REQUEST['2ki_ym'];
    $session->add('2ki_ym', $_SESSION['2ki_ym']);
} elseif (isset($_SESSION['2ki_ym'])) {
    $session->add('2ki_ym', $_SESSION['2ki_ym']);
} elseif ($session->get('2ki_ym') != '') {
    $_SESSION['2ki_ym'] = $session->get('kamoku');
}

// �оݷ�����
$yyyymm   = $_SESSION['2ki_ym'];
$ki       = Ym_to_tnk($_SESSION['2ki_ym']);
$yyyy     = substr($yyyymm, 0,4);
$mm       = substr($yyyymm, 4,2);
if (($mm >= 1) && ($mm <= 3)) {
    $yyyy = ($yyyy - 1);
}

$cost_ym = array();
$tuki_chk = substr($_SESSION['2ki_ym'],4,2);
if ($tuki_chk >= 1 && $tuki_chk <= 3) {           //�裴��Ⱦ��
    $hanki = '��';
    $cost_ym[0]  = $yyyy . '04';
    $cost_ym[1]  = $yyyy . '05';
    $cost_ym[2]  = $yyyy . '06';
    $cost_ym[3]  = $yyyy . '07';
    $cost_ym[4]  = $yyyy . '08';
    $cost_ym[5]  = $yyyy . '09';
    $cost_ym[6]  = $yyyy . '10';
    $cost_ym[7]  = $yyyy . '11';
    $cost_ym[8]  = $yyyy . '12';
    $cost_ym[9]  = $yyyy . '01';
    $cost_ym[10] = $yyyy . '02';
    $cost_ym[11] = $yyyy . '03';
    $cnum        = 12;
} elseif ($tuki_chk >= 4 && $tuki_chk <= 6) {     //�裱��Ⱦ��
    $hanki = '��';
    $cost_ym[0]  = $yyyy . '04';
    $cost_ym[1]  = $yyyy . '05';
    $cost_ym[2]  = $yyyy . '06';
    $cnum        = 3;
} elseif ($tuki_chk >= 7 && $tuki_chk <= 9) {     //�裲��Ⱦ��
    $hanki = '��';
    $cost_ym[0] = $yyyy . '04';
    $cost_ym[1] = $yyyy . '05';
    $cost_ym[2] = $yyyy . '06';
    $cost_ym[3] = $yyyy . '07';
    $cost_ym[4]  = $yyyy . '08';
    $cost_ym[5]  = $yyyy . '09';
    $cnum        = 6;
} elseif ($tuki_chk >= 10) {    //�裳��Ⱦ��
    $hanki = '��';
    $cost_ym[0]  = $yyyy . '04';
    $cost_ym[1]  = $yyyy . '05';
    $cost_ym[2]  = $yyyy . '06';
    $cost_ym[3]  = $yyyy . '07';
    $cost_ym[4]  = $yyyy . '08';
    $cost_ym[5]  = $yyyy . '09';
    $cost_ym[6]  = $yyyy . '10';
    $cost_ym[7]  = $yyyy . '11';
    $cost_ym[8]  = $yyyy . '12';
    $cnum        = 9;
}

//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
if ($tuki_chk == 3) {
    $menu->set_title("�� {$ki} �����ܷ軻�������ࡡ��������������");
} else {
    $menu->set_title("�� {$ki} ������{$hanki}��Ⱦ���������ࡡ��������������");
}

///////////// ��׶�ۤ����
// query���϶���
$query = "select
                SUM(den_kin) as t_kingaku
          from
                manufacture_cost_cal";

// ��������ݤι�׶�ۤ����
// ���դ�����
$d_start = $cost_ym[0] . '01';
$d_end   = $yyyymm . '99';
$search = "where den_ki='$nk_ki' and den_ymd>=$d_start and den_ymd<=$d_end and den_cname='��������'";
$query_s = sprintf("$query %s", $search);     // SQL query ʸ�δ���
$res_sum = array();
if ($rows=getResult($query_s, $res_sum) <= 0) {
    $t_genkai_kin = 0;
} else {
    $t_genkai_kin = $res_sum[0][0];
}

// �����������ι�׶�ۤ����
// ���դ�����
$d_start = $cost_ym[0] . '01';
$d_end   = $yyyymm . '99';
$search = "where den_ki='$nk_ki' and den_ymd>=$d_start and den_ymd<=$d_end and den_cname='����������'";
$query_s = sprintf("$query %s", $search);     // SQL query ʸ�δ���
$res_sum = array();
if ($rows=getResult($query_s, $res_sum) <= 0) {
    $t_genswa_kin = 0;
} else {
    $t_genswa_kin = $res_sum[0][0];
}

// ������ͭ���ٵ�ι�׶�ۤ����
// ���դ�����
$d_start = $cost_ym[0] . '01';
$d_end   = $yyyymm . '99';
$search = "where den_ki='$nk_ki' and den_ymd>=$d_start and den_ymd<=$d_end and den_cname='������ͭ���ٵ�'";
$query_s = sprintf("$query %s", $search);     // SQL query ʸ�δ���
$res_sum = array();
if ($rows=getResult($query_s, $res_sum) <= 0) {
    $t_genyu_kin = 0;
} else {
    $t_genyu_kin = $res_sum[0][0];
}

///////////// ���٤����
// query���϶���
$query = "select
                den_ymd as �׾���,
                den_kin as ���
          from
                manufacture_cost_cal";

// ��������ݤ����٤����
// ���դ�����
$d_start = $cost_ym[0] . '01';
$d_end   = $yyyymm . '99';
$search = "where den_ki='$nk_ki' and den_ymd>=$d_start and den_ymd<=$d_end and den_cname='��������'";
$query_s = sprintf("$query %s", $search);     // SQL query ʸ�δ���
$res_genkai   = array();
$field = array();
if (($rows_genkai = getResultWithField2($query_s, $field, $res_genkai)) <= 0) {
    $rows_genkai      = 0;
    $res_genkai[0][0] = 0;
}

// ���������������٤����
// ���դ�����
$d_start = $cost_ym[0] . '01';
$d_end   = $yyyymm . '99';
$search = "where den_ki='$nk_ki' and den_ymd>=$d_start and den_ymd<=$d_end and den_cname='����������'";
$query_s = sprintf("$query %s", $search);     // SQL query ʸ�δ���
$res_genswa   = array();
$field = array();
if (($rows_genswa = getResultWithField2($query_s, $field, $res_genswa)) <= 0) {
    $rows_genswa      = 0;
    $res_genswa[0][0] = 0;
}

// ������ͭ���ٵ�����٤����
// ���դ�����
$d_start = $cost_ym[0] . '01';
$d_end   = $yyyymm . '99';
$search = "where den_ki='$nk_ki' and den_ymd>=$d_start and den_ymd<=$d_end and den_cname='������ͭ���ٵ�'";
$query_s = sprintf("$query %s", $search);     // SQL query ʸ�δ���
$res_genyu   = array();
$field = array();
if (($rows_genyu = getResultWithField2($query_s, $field, $res_genyu)) <= 0) {
    $rows_genyu      = 0;
    $res_genyu[0][0] = 0;
}

// �ĹԤο��ʺ����͡�
$max_rows = $rows_genkai;        // �Ȥꤢ������������ݤιԿ��򥻥å�
if ($max_rows < $rows_genswa) {  // �������������礭������ӡ�
    $max_rows = $rows_genswa;    // �����������ιԿ��򥻥å�
}
if ($max_rows < $rows_genyu) {   // ������ͭ���ٵ���礭������ӡ�
    $max_rows = $rows_genyu;    // ������ͭ���ٵ�ιԿ��򥻥å�
}

// ɽ���ѥǡ����γ�Ǽ
$view_data = array();
for ($r=0; $r<$max_rows; $r++) {        // �ǽ��ԤޤǷ����֤�
    // ���������
    if ($r<$rows_genkai) {       // ���顼�к�
        $view_data[$r][0] = $res_genkai[$r][1];
    } else {
        $view_data[$r][0] = '��';
    }
    // ����������
    if ($r<$rows_genswa) {   // ���顼�к�
        $view_data[$r][1] = $res_genswa[$r][1];
    } else {
        $view_data[$r][1] = '��';
    }
    // ������ͭ���ٵ�
    if ($r<$rows_genyu) {   // ���顼�к�
        $view_data[$r][2] = $res_genyu[$r][1];
    } else {
        $view_data[$r][2] = '��';
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
<?php echo $menu->out_site_java()?>
<?php echo $menu->out_css()?>
<?php echo $menu->out_jsBaseClass() ?>

<script type='text/javascript' language='JavaScript'>
<!--
/* ������ϥե�����Υ�����Ȥ˥ե������������� */
function set_focus(){
    // document.body.focus();                          // F2/F12��������뤿����б�
    // document.form_name.element_name.select();
}
// -->
</script>

<!-- �������륷���ȤΥե��������򥳥��� HTML���� �����Ȥ�����Ҥ˽���ʤ��������
<link rel='stylesheet' href='<?php echo MENU_FORM . '?' . $uniq ?>' type='text/css' media='screen'>
-->

<style type="text/css">
<!--
.pt8 {
    font-size:      8pt;
    font-family:    monospace;
}
.pt9 {
    font-size:      9pt;
    font-family:    monospace;
}
.pt10 {
    font-size:l     10pt;
    font-family:    monospace;
}
.pt10b {
    font-size:      10pt;
    font-weight:    bold;
    font-family:    monospace;
}
.pt11b {
    font-size:      11pt;
    font-weight:    bold;
    font-family:    monospace;
}
.pt12b {
    font-size:      12pt;
    font-weight:    bold;
    font-family:    monospace;
}
th {
    background-color:   yellow;
    color:              blue;
    font-size:          10pt;
    font-weight:        bold;
    font-family:        monospace;
}
.winbox {
    border-style: solid;
    border-width: 1px;
    border-top-color:       #FFFFFF;
    border-left-color:      #FFFFFF;
    border-right-color:     #bdaa90;
    border-bottom-color:    #bdaa90;
    /* background-color:#d6d3ce; */
}
.winbox_field {
    border-style: solid;
    border-width: 1px;
    border-top-color:       #bdaa90;
    border-left-color:      #bdaa90;
    border-right-color:     #FFFFFF;
    border-bottom-color:    #FFFFFF;
    /* background-color:#d6d3ce; */
}
a:hover {
    background-color:   blue;
    color:              white;
}
a {
    color:   blue;
}
body {
    background-image:url(<?php echo IMG ?>t_nitto_logo4.png);
    background-repeat:no-repeat;
    background-attachment:fixed;
    background-position:right bottom;
}
-->
</style>
</head>
<body onLoad='set_focus()'>
    <center>
<?php echo $menu->out_title_border()?>
        
        <!--------------- ����������ʸ��ɽ��ɽ������ -------------------->
        <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
        <table class='winbox_field' width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <thead>
                <!-- �ơ��֥� �إå�����ɽ�� -->
                <tr>
                    <th class='winbox' nowrap colspan='3'>������������</th>
                </tr>
                <tr>
                    <th class='winbox' nowrap colspan='2'>����</th>
                    <th class='winbox' nowrap>��</th>
                </tr>
                <tr>
                   <td class='winbox' nowrap bgcolor='white' align='center'><div class='pt10b'>��ݶ�</div></td>
                   <td class='winbox' nowrap bgcolor='white' align='center'><div class='pt10b'>����</div></td>
                   <td class='winbox' nowrap bgcolor='white' align='center'><div class='pt10b'>ͭ���ٵ�</div></td>
                </tr>
            </thead>
            <tfoot>
                <!-- ���ߤϥեå����ϲ���ʤ� -->
            </tfoot>
            <tbody>
            <?php
            // ����ɽ��
            for ($r=0; $r<$max_rows; $r++) {
                echo "<tr>\n";
                if ($view_data[$r][0]==0) {
                    echo "  <td class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
                } else {
                    echo "  <td class='winbox' nowrap align='right'><span class='pt9'>" . number_format($view_data[$r][0]) . "</span></td>\n";
                }
                if ($view_data[$r][1]==0) {
                    echo "  <td class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
                } else {
                    echo "  <td class='winbox' nowrap align='right'><span class='pt9'>" . number_format($view_data[$r][1]) . "</span></td>\n";
                }
                if ($view_data[$r][2]==0) {
                    echo "  <td class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
                } else {
                    echo "  <td class='winbox' nowrap align='right'><span class='pt9'>" . number_format($view_data[$r][2]) . "</span></td>\n";
                }
                echo "</tr>\n";
            }
            // ���ɽ��
            echo "<tr>\n";
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($t_genkai_kin) . "</span></td>\n";
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($t_genswa_kin) . "</span></td>\n";
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($t_genyu_kin) . "</span></td>\n";
            echo "</tr>\n";
            ?>
            
            </tbody>
        </table>
            </td></tr>
        </table> <!----------------- ���ߡ�End ------------------>
    </center>
</body>
<?php echo $menu->out_alert_java()?>
</html>
<?php
// ob_end_flush();                 // ���ϥХåե���gzip���� END
?>
