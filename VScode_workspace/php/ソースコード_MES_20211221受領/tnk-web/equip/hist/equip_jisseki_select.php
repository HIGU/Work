<?php
//////////////////////////////////////////////////////////////////////////////
// ������Ư���������ƥ�μ��ӾȲ� �����ֹ� ����ե�����                     //
// Copyright(C) 2003-2021 Kazuhiro.Kobayashi tnksys@nito-kohki.co.jp        //
// Changed history                                                          //
// 2003/05/13 Created   equip_jisseki_select.php                            //
// 2003/05/27 ���Υ�����ץȾ�Ǽ��Ӱ�����ɽ������褦���ɲ�                //
// 2003/07/08 SQL�� where work_flg='f' �� work_flg is FALSE ���ѹ�          //
// 2004/02/07 select��������¨�¹Ԥ���褦��JavaScript�ǥ��å��ɲ�        //
// 2004/06/21 ���ǥơ��֥� ���̲���                                         //
// 2004/11/19 ����̾�򥿥��ȥ���ղ� (2004/09/15�˹������б��Ѥ�)           //
// 2005/06/24 F2/F12��������뤿����б��� JavaScript�� set_focus()���ɲ�   //
// 2006/03/27 �ù���λ������Τ��ä��ܥ���(���å�)���ɲ�                //
// 2007/02/02 PostgreSQL8.2.X �ˤ�� SQLʸ�� '\'YY �� 'YY �������������    //
// 2007/09/26 E_ALL �� E_ALL | E_STRICT  ���ѹ�                             //
// 2018/05/18 ��������ɲá������ɣ�����Ͽ����Ū��7���ѹ�            ��ë //
// 2021/06/22 �������﫤�SUS��ʬΥ                                  ��ë //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL | E_STRICT);
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
ob_start('ob_gzhandler');                   // ���ϥХåե���gzip����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��
require_once ('../../function.php');        // TNK ����ͭ function
require_once ('../../tnk_func.php');        // TNK ��¸ function
require_once ('../../MenuHeader.php');      // TNK ������ menu class
access_log();                               // Script Name �ϼ�ư����

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader(0);                  // ǧ�ڥ����å�0=���̰ʾ� �����=���å������ �����ȥ�̤����

////////////// ����������
$menu->set_site(40, 6);                     // site_index=40(������˥塼) site_id=10(���ӥ�å�����)

if (isset($_REQUEST['factory'])) {
    $factory = $_REQUEST['factory'];
} else {
    $factory = '';
}
///// �ꥯ�����Ȥ�̵����Х��å���󤫤鹩���ʬ��������롣(�̾�Ϥ��Υѥ�����)
if ($factory == '') {
    $factory = @$_SESSION['factory'];
}
switch ($factory) {
case 1:
    $fact_name = '������';
    break;
case 2:
    $fact_name = '������';
    break;
case 4:
    $fact_name = '������';
    break;
case 5:
    $fact_name = '������';
    break;
case 6:
    $fact_name = '������';
    break;
case 7:
    $fact_name = '������(���)';
    break;
case 8:
    $fact_name = '������(SUS)';
    break;
default:
    $fact_name = '������';
    break;
}

////////////// �꥿���󥢥ɥ쥹����
// $menu->set_RetUrl(EQUIP2_MENU);
//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title("�� �� �� �� �� ��&nbsp;&nbsp;{$fact_name}");
//////////// ɽ�������
$menu->set_caption('�ؼ��ֹ��� �ù����Ӱ���');
//////////// �ƽ����action̾�ȥ��ɥ쥹����
$menu->set_action('��ʬ������',   EQUIP2 . 'hist/equip_branch_msg.php');     // �쥿���פ�Ĥ�
$menu->set_action('��ư����ɽ��', EQUIP2 . 'hist/equip_chart_detail.php');   // �쥿���פ�Ĥ�
$menu->set_action('���ӽ���ɽ��', EQUIP2 . 'hist/equip_chart_summary.php');  // �쥿���פ�Ĥ�
// $menu->set_action('���ӥ���յ�', EQUIP2 . 'equip_graph_shiji_no.php'); // �쥿���פ�Ĥ�
$menu->set_action('�����lot',     EQUIP2 . 'hist/equip_hist_graph.php');
// $menu->set_action('�����24��',   EQUIP2 . 'equip_graph_shiji_no.php'); // �쥿���פ�Ĥ�
$menu->set_action('�����24',     EQUIP2 . 'hist/equip_hist_graph.php');
$menu->set_action('��ư����ɽ',   EQUIP2 . 'hist/equip_chart.php');


//////////// JavaScript Stylesheet File ��ɬ���ɤ߹��ޤ���
$uniq = uniqid('target');

//////////// �����ޥ��������������ֹ桦����̾�Υꥹ�Ȥ����
if ($factory == '') {
    $query = "select mac_no                 AS mac_no
                    , mac_name              AS mac_name
                from
                    equip_machine_master2
                where
                    survey='Y'
                    and
                    mac_no!=9999
                order by mac_no ASC
    ";
} else {
    $query = "select mac_no                 AS mac_no
                    , mac_name              AS mac_name
                from
                    equip_machine_master2
                where
                    survey='Y'
                    and
                    mac_no!=9999
                    and
                    factory='{$factory}'
                order by mac_no ASC
    ";
}
$res = array();
if (($rows = getResult($query, $res)) < 1) {
    $_SESSION['s_sysmsg'] .= "<font color='yellow'>�����ޥ���������Ͽ������ޤ���</font>";
} else {
    $mac_no_name = array();
    for ($i=0; $i<$rows; $i++) {
        $mac_no_name[$i] = $res[$i]['mac_no'] . " " . trim($res[$i]['mac_name']);   // �����ֹ��̾�Τδ֤˥��ڡ����ɲ�
    }
}

//////////// ���ǤιԿ�
define('PAGE', '20');

if (isset($_REQUEST['page_keep'])) {
    $_REQUEST['mac_no'] = $_SESSION['mac_no'];
    $page_keep = $_REQUEST['page_keep'];
}

if (isset($_REQUEST['kancancel'])) {
    $update_sql = "UPDATE equip_work_log2_header SET end_timestamp=NULL, work_flg=true WHERE mac_no={$_REQUEST['mac_no']} AND siji_no={$_REQUEST['siji_no']} AND koutei={$_REQUEST['koutei']}";
    if (query_affected($update_sql) >= 1) {
        $_SESSION['s_sysmsg'] = '��λ���ä��ޤ�����';
    } else {
        $_SESSION['s_sysmsg'] = '��λ�μ�ä�����ޤ���Ǥ����� ����ô���Ԥ�Ϣ���Ʋ�������';
    }
}

//////////// POST ����mac_no ����¸
if (isset($_REQUEST['mac_no'])) {
    $mac_no = $_REQUEST['mac_no'];  // ���� if ʸ�ǻ��Ѥ��뤿����¸ �ʲ���ʸ̮��unset�β�ǽ�������뤿��
    
    //////////// ����쥳���ɿ�����
    $query = "select count(*) from equip_work_log2_header where mac_no=$mac_no and work_flg IS FALSE and end_timestamp is not null";
    if ( getUniResult($query, $maxrows) <= 0) {
        $_SESSION['s_sysmsg'] .= '����쥳���ɿ��μ����˼���';
    }
    //////////// �ڡ������ե��å�����
    if ( isset($_POST['forward']) ) {
        $_SESSION['ej_offset'] += PAGE;
        if ($_SESSION['ej_offset'] >= $maxrows) {
            $_SESSION['ej_offset'] -= PAGE;
            if ($_SESSION['s_sysmsg'] == "") {
                $_SESSION['s_sysmsg'] .= "<font color='yellow'>���ǤϤ���ޤ���!</font>";
            } else {
                $_SESSION['s_sysmsg'] .= "<br><font color='yellow'>���ǤϤ���ޤ���!</font>";
            }
        }
    } elseif ( isset($_POST['backward']) ) {
        $_SESSION['ej_offset'] -= PAGE;
        if ($_SESSION['ej_offset'] < 0) {
            $_SESSION['ej_offset'] = 0;
            if ($_SESSION['s_sysmsg'] == "") {
                $_SESSION['s_sysmsg'] .= "<font color='yellow'>���ǤϤ���ޤ���!</font>";
            } else {
                $_SESSION['s_sysmsg'] .= "<br><font color='yellow'>���ǤϤ���ޤ���!</font>";
            }
        }
    } elseif ( isset($page_keep) ) {       // ���ߤΥڡ�����ݻ�����
        $offset = $_SESSION['ej_offset'];
    } else {
        $_SESSION['ej_offset'] = 0;
    }
    $offset = $_SESSION['ej_offset'];
} else {
    $mac_no = "";
}
//////////// �����ֹ椫����Ӽ���
if (isset($_REQUEST['mac_no'])) {
    $query = sprintf("select 
                            siji_no as \"�ؼ�No\",
                            parts_no as �����ֹ�,
                            midsc as ����̾,
                            koutei as ����,
                            plan_cnt as �ײ��,
                            jisseki as ���ӿ�,
                            to_char(str_timestamp, 'YY/MM/DD HH24:MI') as ������,
                            to_char(end_timestamp, 'YY/MM/DD HH24:MI') as ��λ��
                            -- to_char(str_timestamp, '\'YY/MM/DD HH24:MI:SS') as ������,
                            -- to_char(end_timestamp, '\'YY/MM/DD HH24:MI:SS') as ��λ��
                        from
                            equip_work_log2_header
                            left outer join
                            miitem
                            on parts_no=mipn
                        where
                            mac_no=%s and
                            work_flg is FALSE and
                            end_timestamp is not null
                        order by end_timestamp DESC
                        limit %d
                        offset %d", $mac_no, PAGE, $offset);
    $res_j = array();
    $field = array();
    if (($rows_j = getResultWithField2($query, $field, $res_j)) <= 0) {
        $_SESSION['s_sysmsg'] = sprintf("<font color='yellow'>�����ֹ�:%s ��<br>���ӥǡ���������ޤ���</font>", $mac_no);
        unset($_REQUEST['mac_no']);
    } else {
        $num = count($field);       // �ե�����ɿ�����
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
<title><?= $menu->out_title() ?></title>
<?= $menu->out_site_java() ?>
<?= $menu->out_css() ?>
<script language="JavaScript">
<!--
/* ����ʸ�����������ɤ��������å� */
function isDigit(str) {
    var len=str.length;
    var c;
    for (i=1; i<len; i++) {
        c = str.charAt(i);
        if ((c < "0") || (c > "9")) {
            return true;
        }
    }
    return false;
}
/* ������ϥե�����Υ�����Ȥ˥ե������������� */
function set_focus(){
    document.mac_form.mac_no.focus();
//    document.mac_form.mac_no.select();
}

/* select���ѹ������Ȥ���¨�¹� */
function select_send(obj)
{
    // location.href = obj.options[obj.selectedIndex].value;
    // document.mac_form.action = '<?=$menu->out_self()?>';
    document.mac_form.submit();
}
// -->
</script>

    <!-- �ե��������ξ�� -->
<link rel='stylesheet' href='../equipment.css?<?php echo $uniq ?>' type='text/css' media='screen'>

<style type="text/css">
<!--
th {
    background-color:yellow;
    color:          blue;
    font-size:      11pt;
    font-weight:    bold;
    font-family:    monospace;
}
td.gb {
    background-color:#d6d3ce;
    color:black;
}
-->
</style>
</head>
<body onLoad='set_focus()'>
    <center>
<?= $menu->out_title_border() ?>
        
        <table border='0' cellspacing='5' cellpadding='0'>
            <form name='mac_form' method='post' action='<?= $menu->out_self() ?>'>
                <tr>
                    <td class='pt11b'>�����ֹ�����򤷤Ʋ�����</td>
                    <td align='center'>
                        <select name='mac_no' class='pt12b' onChange='document.mac_form.submit()'>
                        <?php
                            for ($j=0; $j<$rows; $j++) {
                                if ($mac_no == $res[$j]['mac_no']) {
                                    printf("<option value='%s' selected>%s</option>\n", $res[$j]['mac_no'], $mac_no_name[$j]);
                                } else {
                                    printf("<option value='%s'>%s</option>\n", $res[$j]['mac_no'], $mac_no_name[$j]);
                                }
                            }
                            if ($rows == 0) {
                                echo "<option value='00000'>��Ͽ�ʤ�</option>\n";
                            }
                        ?>
                        </select>
                        <input type='submit' name='select' value='�¹�'>
                    </td>
                </tr>
            </form>
        </table>
    <?php if (isset($_REQUEST['mac_no'])) { ?>
        <table width='100%' border='0' cellspacing='0' cellpadding='0'>
            <tr>
                <form name='mac_page_form' method='post' action='<?= $menu->out_self() ?>'>
                <td align='left'>
                    <table align='left' border='3' cellspacing='0' cellpadding='0'>
                        <td align='left'>
                            <input class='pt10b' type='submit' name='backward' value='����'>
                            <input type='hidden' name='mac_no' value='<?= $mac_no ?>'>
                        </td>
                    </table>
                </td>
                <td align='center' class='pt12b'>
                    <?= $menu->out_caption() ?>
                </td>
                <td align='right'>
                    <table align='right' border='3' cellspacing='0' cellpadding='0'>
                        <td align='right'>
                            <input class='pt10b' type='submit' name='forward' value='����'>
                            <input type='hidden' name='mac_no' value='<?= $mac_no ?>'>
                        </td>
                    </table>
                </td>
                </form>
            </tr>
        </table>
    <?php } ?>
        <?php
            if (isset($_REQUEST['mac_no'])) {
                echo "<table width='100%' bgcolor='#d6d3ce'  border='1' cellspacing='0' cellpadding='2'>\n";
                echo "    <tr><td> <!-- ���ߡ�(�ǥ�������) -->\n";
                echo "<table width='100%' bordercolordark='white' bordercolorlight='#bdaa90' bgcolor='#d6d3ce'  border='1' cellspacing='0' cellpadding='1'>\n";
                for ($n=0; $n<$num; $n++){
                    if ($n == 0) {
                        echo "<th nowrap>No</th>\n";
                        echo "<th nowrap>Graph</th>\n";
                        echo "<th nowrap>ɽ����</th>\n";
                    }
                    echo "<th nowrap>" . $field[$n] . "</th>\n";
                }
                echo "<th nowrap>��λ</th>\n";
                for ($r=0; $r<$rows_j; $r++){
                    echo "<tr class='pt11'>\n";
                    echo "<td class='gb' nowrap align='center'>" . ($r+1+$offset) . "</td>\n";
                    echo "<form method='post' action='", $menu->out_action('��ʬ������'), "'>\n";
                    echo "<td class='gb' nowrap align='center'>\n";
                        echo "<input type='submit' name='graph_24' value='24Hr'>\n";
                        echo "<input type='submit' name='graph_lot' value='����'>\n";
                        echo "<input type='hidden' name='script_graph_24' value='", $menu->out_action('�����24'), "?hist=24'>\n";
                        echo "<input type='hidden' name='script_graph_lot' value='", $menu->out_action('�����lot'), "?hist=max'>\n";
                        echo "<input type='hidden' name='mac_no' value='{$mac_no}'>\n";
                        echo "<input type='hidden' name='siji_no' value='{$res_j[$r][0]}'>\n";
                        echo "<input type='hidden' name='parts_no' value='{$res_j[$r][1]}'>\n";
                        echo "<input type='hidden' name='koutei' value='{$res_j[$r][3]}'>\n";
                    echo "</td>\n";
                    echo "<td class='gb' nowrap align='center'>\n";
                        echo "<input type='submit' name='detail' value='����'>\n";
                        echo "<input type='submit' name='summary' value='����'>\n";
                        echo "<input type='hidden' name='script_detail' value='", $menu->out_action('��ư����ɽ'), "'>\n";
                        echo "<input type='hidden' name='script_summary' value='", $menu->out_action('���ӽ���ɽ��'), "'>\n";
                    echo "</td>\n";
                    echo "</form>\n";
                    for ($n=0; $n<$num; $n++){
                        if ($res_j[$r][$n] == "")
                            echo "<td class='gb' nowrap align='center'>---</td>\n";
                        elseif ($n == 2)
                            echo "<td class='gb' nowrap>", mb_substr($res_j[$r][$n], 0, 14), "</td>\n";
                        elseif ($n == 4 || ($n == 5))
                            echo "<td class='gb' align='right' nowrap>", number_format($res_j[$r][$n]), "</td>\n";
                        elseif (($n == 6) || ($n == 7))
                            echo "<td class='gb' nowrap align='center'>", $res_j[$r][$n], "</td>\n";
                        else
                            echo "<td class='gb' align='right' nowrap>", $res_j[$r][$n], "</td>\n";
                    }
                    if ($r == 0 && $offset == 0) {
                        $query = "SELECT siji_no FROM equip_work_log2_header WHERE mac_no={$mac_no} AND work_flg IS TRUE";
                        if (getResult2($query, $temp) <= 0) {
                            ///// end_timestamp IS NOT NULL �����Ƿײ���б��Τ���
                            $query = "SELECT siji_no, koutei FROM equip_work_log2_header WHERE mac_no={$mac_no} AND end_timestamp IS NOT NULL ORDER BY mac_no DESC, end_timestamp DESC LIMIT 1";
                            if (getResult2($query, $temp) > 0) {
                                if ($temp[0][0] == $res_j[$r][0] && $temp[0][1] == $res_j[$r][3]) {
                                    ///// ��λ�μ�ä�¹Ԥ���
                                    echo "<td align='center'><input type='button' name='cancel' value='���' onClick='location.replace(\"{$menu->out_self()}?mac_no={$mac_no}&siji_no={$res_j[$r][0]}&koutei={$res_j[$r][3]}&kancancel=go\")'></td>\n";
                                } else {
                                    echo "<td align='center'><input type='button' name='cancel' value='���' disabled></td>\n";
                                }
                            } else {
                                echo "<td align='center'><input type='button' name='cancel' value='���' disabled></td>\n";
                            }
                        } else {
                            echo "<td align='center'><input type='button' name='cancel' value='���' disabled></td>\n";
                        }
                    } else {
                        echo "<td align='center'><input type='button' name='cancel' value='���' disabled></td>\n";
                    }
                    echo("</tr>\n");
                }
                echo "</table>\n";
                echo "    </td></tr>\n";
                echo "</table> <!-- ���ߡ�End -->\n";
            }
        ?>
    </center>
</body>
</html>
<?php
echo $menu->out_alert_java();
ob_end_flush();                 // ���ϥХåե���gzip���� END
?>
