<?php
//////////////////////////////////////////////////////////////////////////////
// ��� ���� �Ȳ�  new version  salse_actual_view.php                       //
// Copyright (C) 2020-2020 Ryota.Waki tnksys@nitto-kohki.co.jp              //
// Changed history                                                          //
// 2020/12/24 Created   sales_view.php -> assembly_comp_parts_list_view.php //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL);           // E_ALL='2047' debug ��
// ini_set('display_errors', '1');              // Error ɽ�� ON debug �� ��꡼���女����
// ini_set('zend.ze1_compatibility_mode', '1'); // zend 1.X ����ѥ� php4�θߴ��⡼��
// ini_set('implicit_flush', 'off');            // echo print �� flush �����ʤ�(�٤��ʤ뤿��) CLI CGI��
// ini_set('max_execution_time', 1200);         // ����¹Ի���=20ʬ CLI CGI��
ob_start('ob_gzhandler');                       // ���ϥХåե���gzip����
session_start();                                // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��

require_once ('../../../function.php');            // define.php �� pgsql.php �� require_once ���Ƥ���
require_once ('../../../tnk_func.php');            // TNK �˰�¸������ʬ�δؿ��� require_once ���Ƥ���
require_once ('../../../MenuHeader.php');          // TNK ������ menu class
require_once ('../../../ControllerHTTP_Class.php');// TNK ������ MVC Controller Class
require_once ('assembly_comp_parts_list_func.php');
////////// ���å����Υ��󥹥��󥹤���Ͽ
$session = new Session();

access_log();                               // Script Name �ϼ�ư����

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader(0);                  // ǧ�ڥ����å�0=���̰ʾ� �����=TOP_MENU �����ȥ�̤����

////////// ����������
$menu->set_site( 30, 11);                    // site_index=01(����˥塼) site_id=11(����������)
////////// �꥿���󥢥ɥ쥹����
// $menu->set_RetUrl(SYS_MENU);                // �̾�ϻ��ꤹ��ɬ�פϤʤ�
////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title('��Ω ���� ���� ����');

////////// �֥饦�����Υ���å����к���
$uniq = $menu->set_useNotCache('target');

////////// �����Υ��å����ǡ�����¸
if( ! (isset($_REQUEST['backward']) || isset($_REQUEST['forward'])) ) {
    $_SESSION['s_div']          = $_REQUEST['div'];
    $_SESSION['s_d_start']      = $_REQUEST['d_start'];
    $_SESSION['s_d_end']        = $_REQUEST['d_end'];
    $_SESSION['s_sales_page']   = $_REQUEST['sales_page'];
    $_SESSION['s_maxrows']      = 0;    // 
    unset($_SESSION['s_limitrows']);    // �³��Ͳ��������󤬻ĤäƤ���١�
}

$div        = $_SESSION['s_div'];
$d_start    = $_SESSION['s_d_start'];
$d_end      = $_SESSION['s_d_end'];

////////// ɽ������� 1
// ���ʥ��롼��(������)̾������
if ($div == "A") $div_name = "���٤�";
if ($div == "C") $div_name = "���ץ�����";
if ($div == "D") $div_name = "���ץ�ɸ��";
if ($div == "S") $div_name = "���ץ�����";
if ($div == "L") $div_name = "��˥�";

$f_d_start  = format_date($d_start);        // ���դ� / �ǥե����ޥå�
$f_d_end    = format_date($d_end);          // ���դ� / �ǥե����ޥå�
$menu->set_caption("<u>����=<font color='red'>{$div_name}</font>��{$f_d_start}��{$f_d_end}<u>");

////////// �ڡ�������ν��� 1
// ���ǤιԿ�
define('PAGE', $_SESSION['s_sales_page']);
// ��ץ쥳���ɿ����� (�оݥơ��֥�κ������ڡ�������˻���)
if( isset($_SESSION['s_limitrows']) ) {
    $maxrows = $_SESSION['s_limitrows'];
} else {
    $maxrows = $_SESSION['s_maxrows'];
}
// �ڡ������ե��å�����
if ( isset($_REQUEST['forward']) ) {                    // ���Ǥ������줿
    $_SESSION['sales_offset'] += PAGE;
    if ($_SESSION['sales_offset'] >= $maxrows) {
        $_SESSION['sales_offset'] -= PAGE;
        $_SESSION['s_limitrows'] = $maxrows;
        $_SESSION['s_sysmsg'] .= "���ǤϤ���ޤ���";
    }
} elseif ( isset($_REQUEST['backward']) ) {             // ���Ǥ������줿
    $_SESSION['sales_offset'] -= PAGE;
    if ($_SESSION['sales_offset'] < 0) {
        $_SESSION['sales_offset'] = 0;
        $_SESSION['s_sysmsg'] .= "���ǤϤ���ޤ���";
    }
} else {
    $_SESSION['s_maxrows'] += PAGE + 1; // ���ξ��ϡ�PAGE+1�ǽ����
    $_SESSION['sales_offset'] = 0;      // ���ξ��ϡ����ǽ����
}
$offset = $_SESSION['sales_offset'];

////////// ��λ���پ����������
// SQL WHERE �� ����
$search = sprintf("WHERE a.comp_date>=%d AND a.comp_date<=%d", $d_start, $d_end);
if( $div == "A" ) {
    $search .= " AND SUBSTRING(a.plan_no, 1, 1) != '@'";
} else if( $div == "C" ) {
    $search .= " AND SUBSTRING(a.plan_no, 1, 1) = 'C'";
} else if( $div == "S" ) {
    $search .= " AND SUBSTRING(a.plan_no, 1, 1) = 'C' AND SUBSTRING(sche.note15, 1, 2) = 'SC'";
} else if( $div == "D" ) {
    $search .= " AND SUBSTRING(a.plan_no, 1, 1) = 'C' AND SUBSTRING(sche.note15, 1, 2) != 'SC'";
} else if( $div == "L" ) {
    $search .= " AND SUBSTRING(a.plan_no, 1, 1) = 'L'";
}

// ��λ���پ������
$rows = getKanryouDateView($res, $field, $search, $offset, PAGE);
if( $rows <= 0 ) {
    $_SESSION['s_sysmsg'] .= sprintf("%s��%s ��λ�ǡ���������ޤ���\t\t\t�ޤ��ϡ���λ�ǡ����μ����˼��Ԥ��Ƥ��ޤ�!!", $f_d_start, $f_d_end );
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // ľ���θƽи������
    exit();
}

////////// ɽ������� 2
if( getKanryouAggregate($cap_res, $search) <= 0 ) {
    $menu->set_caption2("<font color='red'>�ײ�� �� ������ �� ���̹�� �μ����˼��Ԥ��ޤ�����</font>");
} else {
    $plan_ken = number_format($cap_res[0][0], 0);
    $comp_ken = number_format($cap_res[0][1], 0);
    $suryou   = trim(number_format($cap_res[0][2], 4), 0);
    $suryou   = trim($suryou, '.');
    $menu->set_caption2("�ײ�� = {$plan_ken} �� �� ������ = {$comp_ken} �� �� ���̹�� = {$suryou} ��");
}

////////// �ڡ�������ν��� 2
// �³���̤����ΤȤ�
if( ! isset($_SESSION['s_limitrows']) ) {
    // ���� ʬ�Υǡ��������뤫�����å�
    $next_rows = getKanryouDateView($next_res, $next_field, $search, $offset+$rows, 1);
    if( $next_rows <= 0 ) {
        $_SESSION['s_limitrows'] = $maxrows + $rows;
    }

    if( isset($_REQUEST['forward']) ) {         // ���Ǥ������줿
        $_SESSION['s_maxrows'] = $maxrows + $rows;
    } else if( isset($_REQUEST['backward']) ) { // ���Ǥ������줿
        if( $offset == 0 ) {
            $_SESSION['s_maxrows'] = PAGE + 1;  // $offset ����ͤΰ١�����ͥ��å�
        } else {
            $_SESSION['s_maxrows'] = $maxrows - $rows;
        }
    }
}

////////// CSV�����Ѥν������
// �ե�����̾�����ܸ��Ĥ���ȼ����Ϥ��ǥ��顼�ˤʤ�Τǰ���ѻ����ѹ�
if ($div == "A") $act_name = "ALL";
if ($div == "C") $act_name = "C-all";
if ($div == "S") $act_name = "C-toku";
if ($div == "D") $act_name = "C-hyou";
if ($div == "L") $act_name = "L-all";

// SQL�Υ������� '�⥨�顼�ˤʤ�Τ�/�˰���ѹ�
$csv_search = str_replace('\'','/',$search);

// CSV�ե�����̾������ʳ���ǯ��-��λǯ��-��������
$outputFile = $d_start . '-' . $d_end . '-' . $act_name;

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
.winboxy {
    border-style: solid;
    border-width: 1px;
    border-top-color:       #FFFFFF;
    border-left-color:      #FFFFFF;
    border-right-color:     #bdaa90;
    border-bottom-color:    #bdaa90;
    background-color:   yellow;
    color:              blue;
    font-size:          12pt;
    font-weight:        bold;
    font-family:        monospace;
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
        <!----------------- ������ ���� ���� �Υե����� ---------------->
        <table width='100%' border='0' cellspacing='0' cellpadding='0'>
            <form name='page_form' method='post' action='<?php echo $menu->out_self() ?>'>
                <tr>
                    <td align='left'>
                        <table align='left' border='3' cellspacing='0' cellpadding='0'>
                            <td align='left'>
                                <input class='pt10b' type='submit' name='backward' value='����'>
                            </td>
                        </table>
                    </td>
                    <td nowrap align='center' class='caption_font'>
                        <BR>
                        <?php echo $menu->out_caption() ?>
                    </td>
                    <td align='right'>
                        <table align='right' border='3' cellspacing='0' cellpadding='0'>
                            <td align='right'>
                                <input class='pt10b' type='submit' name='forward' value='����'>
                            </td>
                        </table>
                    </td>
                </tr>
            </form>
        </table>
        <font class='pt11b'><BR><?php echo $menu->out_caption2() ?><BR><BR></font>
        <a href='assembly_comp_parts_list_csv.php?csvname=<?php echo $outputFile ?>&actname=<?php echo $act_name ?>&csvsearch=<?php echo $csv_search ?>'>CSV����</a>
        <BR><BR>
        <!--------------- ����������ʸ��ɽ��ɽ������ -------------------->
        <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
        <table class='winbox_field' width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <thead>
                <!-- �ơ��֥� �إå�����ɽ�� -->
                <tr>
                    <th class='winbox' nowrap width='10'>No.</th>        <!-- �ԥʥ�С���ɽ�� -->
                <?php
                $num = count($field);
                for ($i=0; $i<$num; $i++) {             // �ե�����ɿ�ʬ���֤�
                ?>
                    <th class='winbox' nowrap><?php echo $field[$i] ?></th>
                <?php
                }
                ?>
                </tr>
            </thead>
            <tfoot>
                <!-- ���ߤϥեå����ϲ���ʤ� -->
            </tfoot>
            <tbody>
                <?php
                for ($r=0; $r<$rows; $r++) {
                    echo "<tr onMouseOver=\"style.background='#ceffce'\" onMouseOut=\"style.background='#d6d3ce'\">\n";
                    echo "    <td class='winbox' nowrap align='right'><div class='pt10b'>" . ($r + $offset + 1) . "</div></td>    <!-- �ԥʥ�С���ɽ�� -->\n";
                    for ($i=0; $i<$num; $i++) {         // �쥳���ɿ�ʬ���֤�
                        switch ($i) {
                        case 0:     // �����ֹ�
//                            echo "<td class='winbox' nowrap align='left'><div class='pt9'>{$res[$r][$i]}</div></td>\n";
/**/
                            if( $r == 0 || $res[$r][$i] != $res[$r-1][$i] ) {
                                echo "<td class='winbox' nowrap align='left'><div class='pt9'>{$res[$r][$i]}</div></td>\n";
                            } else {
                                echo "<td class='winbox' nowrap align='left'><div class='pt9'>��</div></td>\n";
                            }
/**/
                            break;
                        case 1:     // ����̾
//                            echo "<td class='winbox' nowrap align='left'><div class='pt9'>{$res[$r][$i]}</div></td>\n";
/**/
                            if( $r == 0 || $res[$r][0] != $res[$r-1][0] ) {
                                echo "<td class='winbox' nowrap align='left'><div class='pt9'>{$res[$r][$i]}</div></td>\n";
                            } else {
                                echo "<td class='winbox' nowrap align='left'><div class='pt9'>��</div></td>\n";
                            }
/**/
                            break;
                        case 2:     // �ײ��ֹ�
//                            echo "<td class='winbox' nowrap align='left'><div class='pt9'>{$res[$r][$i]}</div></td>\n";
/**/
                            if( $r == 0 || $res[$r][$i] != $res[$r-1][$i] ) {
                                echo "<td class='winbox' nowrap align='left'><div class='pt9'>{$res[$r][$i]}</div></td>\n";
                            } else {
                                echo "<td class='winbox' nowrap align='left'><div class='pt9'>��</div></td>\n";
                            }
/**/
                            break;
                        case 3:     // ��Ω��λ��
//                            echo "<td class='winbox' nowrap align='left'><div class='pt9'>" . format_date($res[$r][$i]) . "</div></td>\n";
/**/
                            if( $r == 0 || $res[$r][$i] != $res[$r-1][$i] || $res[$r][$i-1] != $res[$r-1][$i-1]) {
                                echo "<td class='winbox' nowrap align='left'><div class='pt9'>" . format_date($res[$r][$i]) . "</div></td>\n";
                            } else {
                                echo "<td class='winbox' nowrap align='left'><div class='pt9'>��</div></td>\n";
                            }
/**/
                            break;
                        case 4:     // �����ֹ�
                            echo "<td class='winbox' nowrap align='left'><div class='pt9'>{$res[$r][$i]}</div></td>\n";
                            break;
                        case 5:     // ����̾
                            echo "<td class='winbox' nowrap align='left'><div class='pt9'>{$res[$r][$i]}</div></td>\n";
                            break;
                        case 6:     // ���ѿ�
                            echo "<td class='winbox' nowrap width='45' align='right'><div class='pt9'>" . number_format($res[$r][$i], 4) . "</div></td>\n";
                            break;
                        case 7:     // ������
                            echo "<td class='winbox' nowrap width='45' align='right'><div class='pt9'>" . number_format($res[$r][$i], 0) . "</div></td>\n";
                            break;
                        case 8:     // ���̡ʻ��ѿ��ߴ�������
                            $res[$r][$i] = number_format($res[$r][$i], 4);
                            $res[$r][$i] = rtrim($res[$r][$i], 0);
                            $res[$r][$i] = rtrim($res[$r][$i], '.');
                            echo "<td class='winbox' nowrap width='45' align='right'><div class='pt9'>{$res[$r][$i]}</div></td>\n";
                            break;
                        default:    // ����¾
                            echo "<td class='winbox' nowrap align='right'><div class='pt9'>{$res[$r][$i]}</div></td>\n";
                            break;
                        }
                    }
                    echo "</tr>\n";
                }
                ?>
            </tbody>
        </table>
            </td></tr>
        </table> <!----------------- ���ߡ�End ------------------>

        <!----------------- ������ ���� ���� �Υե����� ---------------->
        <table width='100%' border='0' cellspacing='0' cellpadding='0'>
            <form name='page_form' method='post' action='<?php echo $menu->out_self() ?>'>
                <tr>
                    <td align='left'>
                        <table align='left' border='3' cellspacing='0' cellpadding='0'>
                            <td align='left'>
                                <input class='pt10b' type='submit' name='backward' value='����'>
                            </td>
                        </table>
                    </td>
                    <td align='right'>
                        <table align='right' border='3' cellspacing='0' cellpadding='0'>
                            <td align='right'>
                                <input class='pt10b' type='submit' name='forward' value='����'>
                            </td>
                        </table>
                    </td>
                </tr>
            </form>
        </table>
        <br>

    </center>
</body>
<?php echo $menu->out_alert_java()?>
</html>
<?php
// ob_end_flush();                 // ���ϥХåե���gzip���� END
?>
