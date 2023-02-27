<?php
//////////////////////////////////////////////////////////////////////////////
// �����������ʻų�C��ɼ �Ȳ�                                               //
// Copyright (C) 2018-2018 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2018/10/29 Created   machine_production_view.php                         //
//            �Ȳ񤷤�����ǯ�֥ǡ������������Ⱦ����̵��                    //
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

////// �о�����
$ki2_ym   = $_SESSION['2ki_ym'];
$yyyymm   = $_SESSION['2ki_ym'];
$ki       = Ym_to_tnk($_SESSION['2ki_ym']);
$b_yyyymm = $yyyymm - 100;
$p1_ki    = Ym_to_tnk($b_yyyymm);

///// ������ ǯ��λ���
$yyyy = substr($yyyymm, 0,4);
$mm   = substr($yyyymm, 4,2);
if (($mm >= 1) && ($mm <= 3)) {
    $yyyy = ($yyyy - 1);
}
$pre_end_ym = $yyyy . "03";     // ������ǯ��

///// TNK�� �� NK�����Ѵ�
$nk_ki = $ki + 44;

$cost_ym = array();
$cost_ym[0]  = $ki+1999 . '04';
$cost_ym[1]  = $ki+1999 . '05';
$cost_ym[2]  = $ki+1999 . '06';
$cost_ym[3]  = $ki+1999 . '07';
$cost_ym[4]  = $ki+1999 . '08';
$cost_ym[5]  = $ki+1999 . '09';
$cost_ym[6]  = $ki+1999 . '10';
$cost_ym[7]  = $ki+1999 . '11';
$cost_ym[8]  = $ki+1999 . '12';
$cost_ym[9]  = $ki+2000 . '01';
$cost_ym[10] = $ki+2000 . '02';
$cost_ym[11] = $ki+2000 . '03';
$cnum        = 12;

$tuki_chk = substr($_SESSION['2ki_ym'],4,2);

//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title("�� {$ki} ��������������ɼ����");

$res = array();
$query_chk = sprintf("select kanri_no FROM machine_production_master WHERE total_ki=%d ORDER BY kanri_no", $nk_ki);
if (($rows = getResultWithField3($query_chk, $field, $res)) <= 0) {
    ///// �������ʤ��Τǲ��⤷�ʤ�
} else {
    $data = array();
    for ($r=0; $r<$rows; $r++) {
        ///// �Ʒ����ɼ�򽸷�
        for ($i=0; $i<$cnum; $i++) {
            $query_sum = sprintf("select SUM(total_kin) FROM machine_production_total WHERE kanri_no='%s' and total_ym=%d", $res[$r][0], $cost_ym[$i]);
            if (($rows_sum = getResultWithField3($query_sum, $field_sum, $res_sum)) <= 0) {
                $data[$r][$i] = 0;
            } else {
                if($res_sum[0][0] == "") {
                    $data[$r][$i] = 0;
                } else {
                    $data[$r][$i] = $res_sum[0][0];
                }
            }
        }
    }
}

// ����(����No.��)�ι�� rows
$total_rows = array();
for ($r=0; $r<$rows; $r++) {
    $total_rows[$r] = 0;
    ///// �Ʒ����ɼ�򽸷�
    for ($i=0; $i<$cnum; $i++) {
        $query_sum = sprintf("select SUM(total_kin) FROM machine_production_total WHERE kanri_no='%s' and total_ym=%d", $res[$r][0], $cost_ym[$i]);
        if (($rows_sum = getResultWithField3($query_sum, $field_sum, $res_sum)) <= 0) {
            $total_rows[$r] += 0;
        } else {
            if($res_sum[0][0] == "") {
                $total_rows[$r] += 0;
            } else {
                $total_rows[$r] += $res_sum[0][0];
            }
        }
    }
}
// ����(����)�ι�� cols
$total_all = 0;
$total_cols = array();
for ($r=0; $r<$cnum; $r++) {
    $total_cols[$r] = 0;
    ///// �Ʒ����ɼ�򽸷�
    for ($i=0; $i<$rows; $i++) {
        $query_sum = sprintf("select SUM(total_kin) FROM machine_production_total WHERE kanri_no='%s' and total_ym=%d", $res[$i][0], $cost_ym[$r]);
        if (($rows_sum = getResultWithField3($query_sum, $field_sum, $res_sum)) <= 0) {
            $total_cols[$r] += 0;
            $total_all      += 0;
        } else {
            if($res_sum[0][0] == "") {
                $total_cols[$r] += 0;
                $total_all      += 0;
            } else {
                $total_cols[$r] += $res_sum[0][0];
                $total_all      += $res_sum[0][0];
            }
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
                    <td class='winbox' nowrap bgcolor='white' align='center'><div class='pt10b'>����No.</div></td>
                    <?php
                    for ($i=0; $i<$cnum; $i++) {
                        echo "<td class='winbox' nowrap bgcolor='white' align='center'><div class='pt10b'>" . format_date6($cost_ym[$i]) . "</div></td>\n";
                    }
                    ?>
                    <th class='winbox' nowrap align='center'><span class='pt10b'>���</span></td>
                </tr>
            </thead>
            <tfoot>
                <!-- ���ߤϥեå����ϲ���ʤ� -->
            </tfoot>
            <tbody>
            <?php
            // ����ɽ��
            for ($r=0; $r<$rows; $r++) {
                echo "<tr>\n";
                echo "  <td class='winbox' nowrap align='right'><span class='pt9'>" . $res[$r][0] . "</span></td>\n";
                for ($i=0; $i<$cnum; $i++) {
                    if ($data[$r][$i]==0) {
                        echo "  <td class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
                    } else {
                        echo "  <td class='winbox' nowrap align='right'><span class='pt9'>" . number_format($data[$r][$i]) . "</span></td>\n";
                    }
                }
                echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($total_rows[$r]) . "</span></td>\n";
                echo "</tr>\n";
            }
            // ���ɽ��
            echo "<tr>\n";
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>���</span></td>\n";
            for ($r=0; $r<$cnum; $r++) {
                echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($total_cols[$r]) . "</span></td>\n";
            }
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($total_all) . "</span></td>\n";
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
