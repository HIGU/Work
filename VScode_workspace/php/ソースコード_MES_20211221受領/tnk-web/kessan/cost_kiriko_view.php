<?php
//////////////////////////////////////////////////////////////////////////////
// ��¤�����׻� ��ʴ ���� �Ȳ�                                              //
// Copyright (C) 2017-2019 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2017/09/11 Created   cost_kiriko_view.php                                //
// 2019/05/17 ���դμ�����ˡ���ѹ�                                          //
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
    $menu->set_title("�� {$ki} �����ܷ軻���ڡ�ʴ");
} else {
    $menu->set_title("�� {$ki} ������{$hanki}��Ⱦ�����ڡ�ʴ");
}

///////////// ��׶�ۤ����
// query���϶���
$query = "select
                SUM(den_kin) as t_kingaku
          from
                manufacture_cost_cal";

// �������ʴ�ι�׶�ۤ����
$t_kiriko_kin = 0;
for ($r=0; $r<$cnum; $r++) {
    // ���դ�����
    $d_start = $cost_ym[$r] . '01';
    $d_end   = $cost_ym[$r] . '99';
    $search = "where den_ki='$nk_ki' and den_ymd>=$d_start and den_ymd<=$d_end and den_cname='��ʴ'";
    $query_s = sprintf("$query %s", $search);     // SQL query ʸ�δ���
    $res_sum = array();
    if ($rows=getResult($query_s, $res_sum) <= 0) {
        $m_kiriko_kin[$r] = 0;
    } else {
        $m_kiriko_kin[$r] = $res_sum[0][0];
        $t_kiriko_kin += $m_kiriko_kin[$r];
    }
}

///////////// ���٤����
// query���϶���
$query = "select
                den_ymd as �׾���,
                den_kin as ���
          from
                manufacture_cost_cal";


$rows_kiriko    = array();
$kiriko_mei_ym  = array();
$kiriko_mei_kin = array();
// �������ʴ�����٤����
for ($r=0; $r<$cnum; $r++) {
    // ���դ�����
    $d_start = $cost_ym[$r] . '01';
    $d_end   = $cost_ym[$r] . '99';
    $search = "where den_ki='$nk_ki' and den_ymd>=$d_start and den_ymd<=$d_end and den_cname='��ʴ'";
    $query_s = sprintf("$query %s", $search);     // SQL query ʸ�δ���
    $res_kiriko = array();
    $field = array();
    if (($rows_kiriko[$r]=getResultWithField2($query_s, $field, $res_kiriko)) <= 0) {
        $kiriko_mei_ym[$r][0]  = '';
        $kiriko_mei_kin[$r][0] = '';
    } else {
        for ($i=0; $i<$rows_kiriko[$r]; $i++) {
            $kiriko_mei_ym[$r][$i]  = $res_kiriko[$i][0];
            $kiriko_mei_kin[$r][$i] = $res_kiriko[$i][1];
        }
    }
}

// ����ο� ǯ��
$max_cols = $cnum;
// �ĹԤο��ʺ����͡�
$max_rows = 0;                          // �Ȥꤢ����0�򥻥å�
for ($r=0; $r<$cnum; $r++) {            // �Ʒ�ιԿ������
    if ($max_rows < $rows_kiriko[$r]) { // �Ʒ�ιԿ��������礭������֤������롣
        $max_rows = $rows_kiriko[$r];
    }
}
// ɽ���ѥǡ����γ�Ǽ
$view_data = array();
for ($r=0; $r<$max_rows; $r++) {        // �ǽ��ԤޤǷ����֤�
    for ($i=0; $i<$max_cols; $i++) {    // �����Ʒ�򥻥å�
        if ($r<$rows_kiriko[$i]) {   // ���顼�к�
            $view_data[$r][$i] = $kiriko_mei_kin[$i][$r];    // �����Ϸ�ȹԤ��դʤΤ����
        } else {
            $view_data[$r][$i] = '��';
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
                    <?php
                    for ($i=0; $i<$cnum; $i++) {
                        echo "<td class='winbox' nowrap bgcolor='white' align='center'><div class='pt10b'>" . format_date6($cost_ym[$i]) . "</div></td>\n";
                    }
                    ?>
                        <td class='winbox' nowrap bgcolor='white' align='center'><div class='pt10b'>���</div></td>
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
                for ($i=0; $i<$max_cols; $i++) {
                    if ($view_data[$r][$i]==0) {
                        echo "  <td class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
                    } else {
                        echo "  <td class='winbox' nowrap align='right'><span class='pt9'>" . number_format($view_data[$r][$i]) . "</span></td>\n";
                    }
                }
                echo "  <td class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
                echo "</tr>\n";
            }
            // ���ɽ��
            echo "<tr>\n";
            for ($r=0; $r<$cnum; $r++) {
                echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($m_kiriko_kin[$r]) . "</span></td>\n";
            }
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($t_kiriko_kin) . "</span></td>\n";
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
