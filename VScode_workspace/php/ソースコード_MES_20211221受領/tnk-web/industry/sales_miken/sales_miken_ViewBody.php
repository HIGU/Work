<?php
//////////////////////////////////////////////////////////////////////////////
// ���ʬ�����칩�� ̤����ʬ���������ɤ��Ƥ���ǡ�����Ȳ񤹤�          //
// Copyright (C) 2004-2021 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2004/04/30 Created  sales_miken_view.php                                 //
// 2004/05/12 �����ȥ�˥塼ɽ������ɽ�� �ܥ����ɲ� menu_OnOff($script)�ɲ� //
// 2005/02/08 MenuHeader class ����Ѥ��ƶ��̥�˥塼���ڤ�ǧ���������ѹ�   //
// 2005/08/20 set_focus()�ε�ǽ�� MenuHeader �Ǽ������Ƥ���Τ�̵��������   //
// 2006/12/18 �ײ��ֹ楯��å��ǰ����Ȳ���Ͽ����å�����Ͽ���̤ؤε�ǽ�ɲ�//
// 2006/12/21 ɸ�ࡦ����μ��̤Τ�������ɲ�                                //
// 2007/03/23 sales_miken_view.php �� sales_miken_ViewBody.php��(�ե졼����)//
// 2007/03/24 material/allo_conf_parts_view.php ��                          //
//                           parts/allocate_config/allo_conf_parts_Main.php //
// 2007/03/27 ����̾��ͽ�۰ʾ��Ĺ��̾�Τ����뤿�� nowrap �������ƣ��ʤ�  //
// 2007/06/19 ��Ͽ���̤� material/materialCost_entry.php ��                 //
//                        material_entry/materialCost_entry_main.php ��ë   //
// 2007/09/04 �������η���Ȳ���ɲ�assy_no�����ȼ��recNo�ˤ��ԥޡ�����//
// 2007/09/05 materialCost_view_assy.php�˰���plan_no���ɲ� ����            //
// 2009/08/19 ���AS���Ϥ�ȼ��W#TIUKSL�����ʬ�����äƤ��ޤä���            //
//            ���ȶ�ʬ��C��L��U�ʳ�ɽ�����ʤ��褦���ѹ�                ��ë //
// 2011/07/06 ����ñ���η׻���̤������ۤ�ɽ�����ɲä���               ��ë //
// 2012/09/05 2012/08�ηײ�No.C8385407���ü�ʽ����򤷤�����ǡ�����        //
//            �ĤäƤ��ޤ��Τǡ�PGMŪ�˽���������                      ��ë //
// 2018/08/29 ������˥塼������˥塼��ʬΥ����������ξ��ľ������   ��ë //
// 2021/12/07 ��˥��Ǥ�SC�������äƤ��������ˤʤäƤ��ޤ��Τ���   ��ë //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
// ini_set('implicit_flush', '0');             // echo print �� flush �����ʤ�(�٤��ʤ뤿��) CLI CGI��
// ini_set('max_execution_time', 1200);        // ����¹Ի���=20ʬ CLI CGI��
ob_start('ob_gzhandler');                   // ���ϥХåե���gzip����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��

require_once ('../../function.php');                // define.php �� pgsql.php �� require_once ���Ƥ���
require_once ('../../tnk_func.php');                // TNK �˰�¸������ʬ�δؿ��� require_once ���Ƥ���
require_once ('../../MenuHeader.php');              // TNK ������ menu class
require_once ('../../ControllerHTTP_Class.php');    // TNK ������ MVC Controller Class

///// ���å����Υ��󥹥��󥹤���Ͽ
$session = new Session();
if (isset($_REQUEST['recNo'])) {
    $session->add_local('recNo', $_REQUEST['recNo']);
    exit();
}
// access_log();                               // Script Name �ϼ�ư����

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader(0);                  // ǧ�ڥ����å�0=���̰ʾ� �����=TOP_MENU �����ȥ�̤����

////////////// ����������
$menu->set_site(INDEX_INDUST, 30);             // site_index=30(������˥塼) site_id=30(NK̤��������)
////////////// �꥿���󥢥ɥ쥹����
// $menu->set_RetUrl(INDUST_MENU);             // �̾�ϻ��ꤹ��ɬ�פϤʤ�
//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title('���칩�� ���ʴ���Ǽ��ʬ ̤���� ����');
//////////// ɽ�������
$menu->set_caption('��Ω����ʬ ̤��������ɽ');
////////////// target����
$menu->set_target('_parent');               // �ե졼���Ǥ�������target°����ɬ��
//////////// ��ʬ��ե졼��������Ѥ���
$menu->set_self(INDUST . 'sales_miken/sales_miken_Main.php');
//////////// �ƽ����action̾�ȥ��ɥ쥹����
// $menu->set_action('��������ɽ��ɽ��',   INDUST . 'material/allo_conf_parts_view.php');
$menu->set_action('��������ɽ��ɽ��',   INDUST . 'parts/allocate_config/allo_conf_parts_Main.php');
$menu->set_action('��������Ѱ�������ɽ��ɽ��',   INDUST . 'parts/allocate_config_entry/allo_conf_parts_Main.php');
$menu->set_action('����������Ͽ',     INDUST . 'material/material_entry/materialCost_entry_main.php');
$menu->set_action('������������',     INDUST . 'material/materialCost_view_assy.php');
//////////// �꥿���󥢥ɥ쥹�ؤ�GET�ǡ��������å�
$menu->set_retGET('page_keep', 'on');

//////////// JavaScript Stylesheet File ��ɬ���ɤ߹��ޤ���
$uniq = $menu->set_useNotCache('miken');

$_SESSION['miken_referer'] = H_WEB_HOST . $menu->out_self();     // �ƽФ�Ȥ�URL�򥻥å�������¸

//////////// ���ǤιԿ�
define('PAGE', '200');      // �Ȥꤢ����

//////////// �ƥ����ȥե����뤫�����٤μ����ڤӹ�ץ쥳���ɿ�����(�оݥơ��֥�κ������ڡ�������˻���)
$file_orign    = '../..' . SYS . 'backup/W#TIUKSL.TXT';
$res           = array();
$total_price   = 0;
$total_price_c = 0;
$total_price_l = 0;
$total_price_t = 0;
if (file_exists($file_orign)) {         // �ե������¸�ߥ����å�
    $fp = fopen($file_orign, 'r');
    $rec = 0;       // �쥳���ɭ�
    while (!(feof($fp))) {
        $data = fgetcsv($fp, 130, '_');     // �¥쥳���ɤ�103�Х��ȤʤΤǤ���ä�;͵��ǥ�ߥ���'_'�����
        if (feof($fp)) {
            break;
        }
        $num  = count($data);       // �ե�����ɿ��μ���
        if ($num != 14) {   // AS¦�κ���쥳���ɤ� php-4.3.5��0�֤� php-4.3.6��1���֤����ͤˤʤä���fgetcsv�λ����ѹ��ˤ��
           continue;
        }
        for ($f=0; $f<$num; $f++) {
            $res[$rec][$f] = mb_convert_encoding($data[$f], 'EUC-JP', 'SJIS');       // SJIS��EUC-JP���Ѵ�
            $res[$rec][$f] = addslashes($res[$rec][$f]);    // "'"�����ǡ����ˤ������\�ǥ��������פ���
            // $data_KV[$f] = mb_convert_kana($data[$f]);   // Ⱦ�ѥ��ʤ����ѥ��ʤ��Ѵ�
        }
        if($res[$rec][5] !='C8385407') {
            $query = sprintf("select midsc from miitem where mipn='%s' limit 1", $res[$rec][3]);
            getUniResult($query, $res[$rec][4]);       // ����̾�μ��� (���ʥ����ɤ��񤭤���)
            /******** ����������Ͽ�Ѥߤι����ɲ� *********/
            $sql = "
                SELECT plan_no FROM material_cost_header WHERE plan_no='{$res[$rec][5]}'
            ";
            if (getUniResult($sql, $temp) <= 0) {
                $res[$rec][13] = '��Ͽ';
                $sql_c = "
                    SELECT sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE assy_no = '{$res[$rec][3]}' ORDER BY assy_no DESC, regdate DESC LIMIT 1
                ";
                if (($rows_c = getResultWithField3($sql_c, $field_c, $res_c)) <= 0) {
                } else {
                }
            } else {
                $res[$rec][13] = '��Ͽ��';
                $sql_c = "
                    SELECT sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE plan_no='{$res[$rec][5]}' AND assy_no = '{$res[$rec][3]}' ORDER BY assy_no DESC, regdate DESC LIMIT 1
                ";
                if (($rows_c = getResultWithField3($sql_c, $field_c, $res_c)) <= 0) {
                } else {
                }
            }
            /******** ����ɸ��ι����ɲ� *********/
            if ($res[$rec][0] == 'C') {
                $sql2 = "
                    SELECT substr(note15, 1, 2) FROM assembly_schedule WHERE plan_no='{$res[$rec][5]}'
                ";
                $sc = '';
                getUniResult($sql2, $sc);
                if ($sc == 'SC') {
                    $res[$rec][15] = '����';
                } else {
                    $res[$rec][15] = 'ɸ��';
                }
            } else {
                $res[$rec][15] = 'ɸ��';
            }
            /******** ����ñ�������ǡ����ˤʤ����ξ�񤭽��� *********/
            if ($res[$rec][12] == 0) {                                  // ���ǡ����˻��ڤ����뤫�ɤ���
                $res[$rec][14] = '1';
                $sql = "
                    SELECT price FROM sales_price_nk WHERE parts_no='{$res[$rec][3]}'
                ";
                if (getUniResult($sql, $sales_price) <= 0) {            // �ǿ����ڤ���Ͽ����Ƥ��뤫
                    $sql = "
                        SELECT sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE plan_no='{$res[$rec][5]}' AND assy_no = '{$res[$rec][3]}' ORDER BY assy_no DESC, regdate DESC LIMIT 1
                    ";
                    if (getUniResult($sql, $sales_price) <= 0) {        // �ײ�����������Ͽ����Ƥ��뤫
                        $sql_c = "
                            SELECT sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE assy_no = '{$res[$rec][3]}' ORDER BY assy_no DESC, regdate DESC LIMIT 1
                        ";
                        if (getUniResult($sql, $sales_price) <= 0) {    // ���ʤ����������Ͽ����Ƥ��뤫
                            $res[$rec][12] = 0;
                        } else {
                            if ($res[$rec][15] == '����') {
                                $res[$rec][12] = round(($sales_price * 1.27), 2);   // ����ΤȤ�����Ψ��
                            } else {
                                $res[$rec][12] = round(($sales_price * 1.13), 2);
                            }
                        }
                    } else {
                        if ($res[$rec][15] == '����') {
                            $res[$rec][12] = round(($sales_price * 1.27), 2);       // ����ΤȤ�����Ψ��
                        } else {
                            $res[$rec][12] = round(($sales_price * 1.13), 2);
                        }
                    }
                } else {
                    $res[$rec][12] = $sales_price;
                }
            } else {
                $res[$rec][14] = '0';
            }
            /******** ���� �׻� *********/
            $res[$rec][16] = round(($res[$rec][11] * $res[$rec][12]), 0);
            $total_price  += $res[$rec][16];
            if ($res[$rec][0] == 'C') {
                $total_price_c += $res[$rec][16];
            } elseif ($res[$rec][0] == 'L') {
                $total_price_l += $res[$rec][16];
            } else {
                $total_price_t += $res[$rec][16];
            }
            $rec++;
        }
    }
    $maxrows = $rec;
    $rec    -= 1;
    $rows    = $maxrows;    // ����Ϲ�ץ쥳���ɿ���ɽ���ѥ쥳���ɿ���Ʊ��
    $field   = array(0=>'������', 1=>'������', 3=>'�����ֹ�', 4=>'����̾', 5=>'�ײ��ֹ�', 11=>'������', 12=>'����ñ��');
} else {
    header("Location: $url_referer");                   // ľ���θƽи������
    $_SESSION['s_sysmsg'] .= '̤�������٤Υե����뤬����ޤ���';  // .= ��å��������ɲä���
    exit();
}
//////////// �ڡ������ե��å�����(offset�ϻ��Ѥ������̾�����ѹ� �㡧sales_offset)
$offset = $session->get_local('offset');
if ($offset == '') $offset = 0;         // �����
if ( isset($_REQUEST['forward']) ) {                       // ���Ǥ������줿
    $offset += PAGE;
    if ($offset >= $maxrows) {
        $offset -= PAGE;
        if ($_SESSION['s_sysmsg'] == '') {
            $_SESSION['s_sysmsg'] .= "<font color='yellow'>���ǤϤ���ޤ���</font>";
        } else {
            $_SESSION['s_sysmsg'] .= "<br><font color='yellow'>���ǤϤ���ޤ���</font>";
        }
    }
} elseif ( isset($_REQUEST['backward']) ) {                // ���Ǥ������줿
    $offset -= PAGE;
    if ($offset < 0) {
        $offset = 0;
        if ($_SESSION['s_sysmsg'] == '') {
            $_SESSION['s_sysmsg'] .= "<font color='yellow'>���ǤϤ���ޤ���</font>";
        } else {
            $_SESSION['s_sysmsg'] .= "<br><font color='yellow'>���ǤϤ���ޤ���</font>";
        }
    }
} elseif ( isset($_REQUEST['page_keep']) ) {               // ���ߤΥڡ�����ݻ�����
    $offset = $offset;
} else {
    $offset = 0;                            // ���ξ��ϣ��ǽ����
    $session->add_local('recNo', '-1');     // 0�쥳���ɤǥޡ�����ɽ�����Ƥ��ޤ�������б�
}
$session->add_local('offset', $offset);

///////////// HTML Header ����Ϥ��ƥ���å��������
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
<link rel='stylesheet' href='sales_miken.css?id=<?php echo $uniq ?>' type='text/css' media='screen'>
<style type='text/css'><!-- --></style>
<!-- <script type='text/javascript' src='sales_miken.js?<?php echo $uniq ?>'></script> -->

<script type='text/javascript'>
<!--
/* ����ʸ�����������ɤ��������å�(ASCII code check) */
function isDigit(str) {
    var len = str.length;
    var c;
    for (i=0; i<len; i++) {
        c = str.charAt(i);
        if ((c < '0') || (c > '9')) {
            return false;
        }
    }
    return true;
}

/* ����ʸ��������ե��٥åȤ��ɤ��������å� isDigit()�ε� */
function isABC(str) {
    // var str = str.toUpperCase();    // ɬ�פ˱�������ʸ�����Ѵ�
    var len = str.length;
    var c;
    for (i=0; i<len; i++) {
        c = str.charAt(i);
        if ((c < 'A') || (c > 'Z')) {
            if (c == ' ') continue; // ���ڡ�����OK
            return false;
        }
    }
    return true;
}

/* ����ʸ�����������ɤ��������å� �������б� */
function isDigitDot(str) {
    var len = str.length;
    var c;
    var cnt_dot = 0;
    for (i=0; i<len; i++) {
        c = str.charAt(i);
        if (c == '.') {
            if (cnt_dot == 0) {     // 1���ܤ������å�
                cnt_dot++;
            } else {
                return false;       // 2���ܤ� false
            }
        } else {
            if (('0' > c) || (c > '9')) {
                return false;
            }
        }
    }
    return true;
}

/* ������ϥե�����Υ�����Ȥ˥ե������������� */
function set_focus(){
    // document.body.focus();   // F2/F12������ͭ���������б�
    // document.mhForm.backwardStack.focus();  // �嵭��IE�ΤߤΤ���NN�б�
}
// -->
</script>

<!-- �������륷���ȤΥե��������򥳥��� HTML���� �����Ȥ�����Ҥ˽���ʤ��������
<link rel='stylesheet' href='<?php echo MENU_FORM . '?' . $uniq ?>' type='text/css' media='screen'>
 -->

<style type="text/css">
<!--
body {
    background-image:   none;
    overflow-x:         hidden;
    overflow-y:         scroll;
}
-->
</style>
</head>
<body onLoad='set_focus()'>
    <center>
        <!--------------- ����������ʸ��ɽ��ɽ������ -------------------->
        <table bgcolor='#d6d3ce' width='100%' align='center' border='1' cellspacing='0' cellpadding='1'>
            <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
        <table class='winbox_field' width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <?php
            for ($r=0; $r<$rows; $r++) {
                if (($res[$r][0] == 'C') || ($res[$r][0] == 'L') || ($res[$r][0] == 'T') || ($res[$r][0] == 'U')) {
                    $recNo = ($offset + $r);
                    if ($session->get_local('recNo') == $recNo) {
                        echo "<tr style='background-color:#ffffc6;'>\n";
                        echo "    <td class='winbox' width=' 5%' nowrap align='right'><div class='pt10b'><a name='last' style='color:black;'>", ($r + $offset + 1), "</a></div></td>    <!-- �ԥʥ�С���ɽ�� -->\n";
                    } else {
                        echo "<tr onMouseOver=\"style.background='#ceffce'\" onMouseOut=\"style.background='#d6d3ce'\">\n";
                        echo "    <td class='winbox' width=' 5%' nowrap align='right'><div class='pt10b'>", ($r + $offset + 1), "</div></td>    <!-- �ԥʥ�С���ɽ�� -->\n";
                    }
                    for ($i=0; $i<$num; $i++) {         // �쥳���ɿ�ʬ���֤�
                        // <!--  bgcolor='#ffffc6' �������� --> 
                        switch ($i) {
                        case 0:     // ������
                            echo "<td class='winbox' width=' 7%' nowrap align='center'><div class='pt9'>{$res[$r][$i]}</div></td>\n";
                            break;
                        case 1:     // ������
                            echo "<td class='winbox' width=' 8%' nowrap align='center'><div class='pt9'>", format_date($res[$r][$i]), "</div></td>\n";
                            break;
                        case 3:     // �����ֹ�
                            echo "<td class='winbox' width=' 9%' nowrap align='center'><a class='pt10' href='JavaScript:baseJS.Ajax(\"sales_miken_ViewBody.php?recNo={$recNo}\");location.replace(\"", $menu->out_action('������������'), "?assy=", urlencode($res[$r][$i]), "&material=1&plan_no=", urlencode($res[$r][5]), "\")' target='_parent' style='text-decoration:none;'>{$res[$r][$i]}</a></td>\n";
                            break;
                        case 4:     // ����̾
                            echo "<td class='winbox' width='33%' align='left'><div class='pt9'>{$res[$r][$i]}</div></td>\n";
                            break;
                        case 5:     // �ײ��ֹ�
/*
                            if( $_SESSION['User_ID'] == '300667' || $_SESSION['User_ID'] == '300144' || $_SESSION['User_ID'] == '970352' ) {
                                echo "<td class='winbox' width=' 9%' nowrap align='center'><a class='pt10' href='JavaScript:baseJS.Ajax(\"sales_miken_ViewBody.php?recNo={$recNo}\");location.replace(\"", $menu->out_action('��������Ѱ�������ɽ��ɽ��'), "?plan_no=", urlencode($res[$r][$i]), "&material=1\")' target='_parent' style='text-decoration:none;'>{$res[$r][$i]}</a></td>\n";
                            } else {
                                echo "<td class='winbox' width=' 9%' nowrap align='center'><a class='pt10' href='JavaScript:baseJS.Ajax(\"sales_miken_ViewBody.php?recNo={$recNo}\");location.replace(\"", $menu->out_action('��������ɽ��ɽ��'), "?plan_no=", urlencode($res[$r][$i]), "&material=1\")' target='_parent' style='text-decoration:none;'>{$res[$r][$i]}</a></td>\n";
                            }
*/
                            echo "<td class='winbox' width=' 9%' nowrap align='center'><a class='pt10' href='JavaScript:baseJS.Ajax(\"sales_miken_ViewBody.php?recNo={$recNo}\");location.replace(\"", $menu->out_action('��������Ѱ�������ɽ��ɽ��'), "?plan_no=", urlencode($res[$r][$i]), "&material=1\")' target='_parent' style='text-decoration:none;'>{$res[$r][$i]}</a></td>\n";
                            break;
                        case 11:    // ������
                            echo "<td class='winbox' width=' 7%' nowrap align='right'><div class='pt9'>", number_format($res[$r][$i], 0), "</div></td>\n";
                            break;
                        case 12:    // ����ñ��
                            if ($res[$r][14] == '0') {
                                echo "<td class='winbox' width=' 9%' nowrap align='right'><div class='pt9'>", number_format($res[$r][$i], 2), "</div></td>\n";
                            } else {
                                echo "<td class='winbox' width=' 9%' nowrap align='right' style='color:brown;'><div class='pt9'>", number_format($res[$r][$i], 2), "</div></td>\n";
                            }
                            break;
                        default:
                            break;
                        }
                    // <!-- ����ץ�<td rowspan='2' colspan='3' width='200' align='center' class='pt10b' bgcolor='#ffffc6'>  </td> -->
                    }
                        echo "<td class='winbox' width=' 7%' nowrap align='center'><a class='pt10' href='JavaScript:baseJS.Ajax(\"sales_miken_ViewBody.php?recNo={$recNo}\");location.replace(\"", $menu->out_action('����������Ͽ'), "?plan_no=", urlencode($res[$r][5]), "&assy_no=", urlencode($res[$r][3]), "&miken_referer=", $_SESSION['miken_referer'], "\")' target='_parent' style='text-decoration:none;'>{$res[$r][13]}</a></td>\n";
                        echo "<td class='winbox pt9' width=' 6%' nowrap align='center'>{$res[$r][15]}</td>\n";
                    echo "</tr>\n";
                }
            }
            ?>
        </table>
            </td></tr>
        </table> <!----------------- ���ߡ�End ------------------>
    </center>
</body>
<?php echo $menu->out_alert_java()?>
</html>
<?php
ob_end_flush();                 // ���ϥХåե���gzip���� END
?>
