<?php
//////////////////////////////////////////////////////////////////////////////
// ������ž(��¤��) �ù����Ӥ������ɽ ɽ��                                 //
// Copyright(C) 2003-2004 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp       //
// �ѹ�����                                                                 //
// 2003/06/27 equip_chart_detail.php ��������                               //
// 2003/07/10 ���ǡ����ǤΥ��å��ѹ�����å���������Ϥ���                //
// 2004/06/21 ���ǥơ��֥�����̲���                                        //
// 2004/06/25 ����쥳���ɿ�[count(*)]��Ȥ鷺�������椹��褦���ѹ�        //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting',E_ALL);       // E_ALL='2047' debug ��
// ini_set('display_errors','1');          // Error ɽ�� ON debug �� ��꡼���女����
session_start();                        // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��
ob_start('ob_gzhandler');               //Warning: Cannot add header ���к��Τ����ɲá�
require_once ("equip_function.php");
// $sysmsg = $_SESSION["s_sysmsg"];
// $_SESSION["s_sysmsg"] = NULL;
access_log();                           // Script Name �ϼ�ư����
$current_script  = $_SERVER['PHP_SELF'];        // ���߼¹���Υ�����ץ�̾����¸
$url_referer     = $_SESSION['equip_referer'];     // ʬ������������¸����Ƥ���ƽи��򥻥åȤ���
// $url_referer     = $_SERVER["HTTP_REFERER"];    // �ƽФ�Ȥ�URL����¸ ���Υ�����ץȤ�ʬ�������򤷤Ƥ�����ϻ��Ѥ��ʤ�

//////////// ǧ�ڥ����å�
if ( !isset($_SESSION["User_ID"]) || !isset($_SESSION["Password"]) || !isset($_SESSION["Auth"]) ) {
    $_SESSION["s_sysmsg"] = "ǧ�ڤ���Ƥ��ʤ���ǧ�ڴ��¤��ڤ�ޤ�����Login���ʤ����Ʋ�������";
    header("Location: http:" . WEB_HOST . "index1.php");
    exit();
}

//////////// ���å�������˻��ꤵ�줿��郎��¸����Ƥ��뤫�����å�
if ( !isset($_SESSION['mac_no']) || !isset($_SESSION['siji_no']) || !isset($_SESSION['koutei']) ) {
    $_SESSION['s_sysmsg'] = "����No/�ؼ�No/���������ꤵ��Ƥ��ޤ���!";
    header("Location: $url_referer");
    exit();
} else {
    $mac_no   = $_SESSION['mac_no'];
    $siji_no  = $_SESSION['siji_no'];
    $parts_no = $_SESSION['parts_no'];
    $koutei   = $_SESSION['koutei'];
    /********* // ���ǡ����Ǥǻ��Ѥ��뤿�ᥳ����
    unset($_SESSION['mac_no']);
    unset($_SESSION['siji_no']);
    unset($_SESSION['parts_no']);
    unset($_SESSION['koutei']);
    *********/
}

/********** Logic Start **********/
//////////// �����ȥ�����ա���������
$today = date('Y/m/d H:i:s');

//////////////// �����ޥ��������鵡��̾�����
$query = "select trim(mac_name) as mac_name from equip_machine_master2 where mac_no={$mac_no} limit 1";
if (getUniResult($query, $mac_name) <= 0) {
    $mac_name = '��';   // error���ϵ���̾��֥��
}

//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu_title = $mac_no . ' ' . $mac_name . " �ù� ���� ���� ɽ����\n";
//////////// ɽ�������
$caption    = '';

////////////// ���ڡ�����ɽ���Կ�
$disp_rows = 24;

//////////// ���ǤιԿ�
// define('PAGE', 24);

//////////// ����쥳���ɿ�����
/********************************
$query  = "select count(*) from equip_work_log2 ";
$query .= "where mac_no=$mac_no and siji_no=$siji_no and koutei=$koutei";
if ( getUniResult($query, $maxrows) <= 0) {
    $_SESSION['s_sysmsg'] = "����쥳���ɿ��μ����˼���";
    header("Location: $url_referer");
    exit();
}
********************************/

//////////// �إå����ե����뤫��ײ������ & ���������μ���
$query = "select plan_cnt
                , jisseki
                , to_char(str_timestamp AT TIME ZONE 'JST', 'YYYY-MM-DD HH24:MI:SS') as str_timestamp
                , to_char(end_timestamp AT TIME ZONE 'JST', 'YYYY-MM-DD HH24:MI:SS') as end_timestamp
            from
                equip_work_log2_header
            where
                mac_no=$mac_no and siji_no=$siji_no and koutei=$koutei
        ";
if ( ($rows=getResult2($query, $res_head)) <= 0) {
    $_SESSION['s_sysmsg'] = '���������μ����˼���';
    $_SESSION['s_sysmsg'] .= "<br>{$query}";    // debug��
    header("Location: $url_referer");
    exit();
} else {
    $plan_cnt = $res_head[0][0];
    $jisseki  = $res_head[0][1];
    $str_timestamp = $res_head[0][2];
    $end_timestamp = $res_head[0][3];
}

$page_up_flg = true;    // ���ǥܥ���������

if (isset($_POST['page_up'])) {
    if (isset($_SESSION['equip_maxrows'])) {
        $maxrows = $_SESSION['equip_maxrows'];
    } else {
        $maxrows = $disp_rows ;    // ���ǤΤ����
    }
    $_SESSION["s_offset"] += $disp_rows;
    if ($_SESSION['s_offset'] > $maxrows) {
        $_SESSION['s_offset'] -= $disp_rows;
        if ($_SESSION['s_sysmsg'] == "") {
            $_SESSION['s_sysmsg'] .= "<font color='yellow'>���ǤϤ���ޤ���!</font>";
        } else {
            $_SESSION['s_sysmsg'] .= "<br><font color='yellow'>���ǤϤ���ޤ���!</font>";
        }
    }
} elseif (isset($_POST['page_down'])) {
    $_SESSION["s_offset"] -= $disp_rows;
    if ($_SESSION["s_offset"] < 0) {
        $_SESSION["s_offset"] = 0;
        if ($_SESSION['s_sysmsg'] == "") {
            $_SESSION['s_sysmsg'] .= "<font color='yellow'>���ǤϤ���ޤ���!</font>";
        } else {
            $_SESSION['s_sysmsg'] .= "<br><font color='yellow'>���ǤϤ���ޤ���!</font>";
        }
    }
} else {
    $_SESSION['s_offset'] = 0;
    $_SESSION['equip_maxrows'] = $disp_rows;  // ���Τ߽����
}
$offset = $_SESSION['s_offset'];

//////////// �����ƥ�ޥ�������������̾����
$query = "select midsc,mzist from miitem where mipn='$parts_no'";
$res = array();
if ( ($rows=getResult2($query,$res)) >= 1) {        // ����̾����
    $buhin_name    = mb_substr($res[0][0],0,10);
    $buhin_zaisitu = mb_substr($res[0][1],0,7);
} else {
    $buhin_name    = '';
    $buhin_zaisitu = '';
}

////////////// ���٥ǡ����μ���
//                -- date_time >= (CURRENT_TIMESTAMP - interval '168 hours')      -- �ƥ����Ѥ˻Ĥ�(168=7���˷��Ѵ������)
//                -- and date_time <= (CURRENT_TIMESTAMP - interval '0 hours')
// TIMESTAMP���ξ��� CAST ���ʤ��� Seq Scan �Ȥʤ�Τ����  index��Ȥ���������Ū�˷��Ѵ���ɬ��
$query = "select to_char(date_time AT TIME ZONE 'JST', 'YYYY/MM/DD')
                ,to_char(date_time AT TIME ZONE 'JST', 'HH24:MI:SS')
                ,mac_state
                ,work_cnt
            from
                equip_work_log2
            where
                date_time >= CAST('$str_timestamp' as TIMESTAMP)
                and date_time <= CAST('$end_timestamp' as TIMESTAMP)
                and mac_no={$mac_no} and siji_no={$siji_no} and koutei={$koutei}
            order by
                date_time ASC
            limit
                $disp_rows
            offset
                $offset
        ";
$res = array();
if ( ($rows=getResult2($query,$res)) <= 0) {
    if (isset($_POST['page_up'])) {
        $_SESSION['equip_maxrows'] -= $disp_rows;  // ���Ǥ�̵������ 1��ʬ�ޥ��ʥ�
        $page_up_flg = false;
        $_SESSION['s_sysmsg'] .= "<font color='yellow'>���ǤϤ���ޤ���!</font>";
    } else {
        $_SESSION['s_sysmsg'] = "equip_work_log2 ������ �����˼���";
        header("Location: $url_referer");
        exit();
    }
} else {
    $num = count($res[0]);
    if ($rows == $disp_rows) {
        $_SESSION['equip_maxrows'] += $rows;  // �����ǤΤ����+
    } else {
        $_SESSION['equip_maxrows'] = ($rows + $offset); // �ºݤΥ쥳���ɿ��������
    }
}


/********** Logic End   **********/
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<HTML>
<HEAD>
<META http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<META http-equiv="Content-Style-Type" content="text/css">
<TITLE>������ž����ɽ��(��¤��)</TITLE>
<script language="JavaScript">
<!--
    parent.menu_site.location = 'http:<?php echo(WEB_HOST) ?>menu_site.php';
// -->
</script>
<style type="text/css">
<!--
.title_font {
    font:bold 13.5pt;
    font-family: monospace;
}
.today_font {
    font-size: 10.5pt;
    font-family: monospace;
}
.sub_font {
    font:bold 11.5pt;
    font-family: monospace;
}
th {
    font:bold 11.5pt;
    font-family: monospace;
}
.table_font {
    font: 11.9pt;
    font-family: monospace;
}
.pick_font {
    font:bold 8.5pt;
    font-family: monospace;
}
th {
    font:bold 12.0pt;
    font-family: monospace;
}
.ext_font {
    background-color:blue;
    color:yellow;
    font:bold 12.0pt;
    font-family: monospace;
}
.pt12b {
    font:bold 12pt;
}
.pt11b {
    font:bold 11pt;
}
.margin0 {
    margin:0%;
}
select      {background-color:teal; color:white;}
textarea        {background-color:black; color:white;}
input.sousin    {background-color:red;}
input.text      {background-color:black; color:white;}
-->
</style>
</HEAD>
<BODY class='margin0'>
    <center>
        <table width='100%' bgcolor='#d6d3ce'  border='1' cellspacing='0' cellpadding='2'>
            <tr><td> <!-- ���ߡ�(�ǥ�������) -->
        <table width='100%' bordercolordark='white' bordercolorlight='#bdaa90' bgcolor='#d6d3ce'  border='1' cellspacing='0' cellpadding='1'>
            <tr>
                <form method='post' action='<?php echo $url_referer ?>'>
                    <td width='60' bgcolor='blue' align='center' valign='center'>
                        <input class='pt12b' type='submit' name='return' value='���'>
                        <input type='hidden' name='mac_no' value='<?php echo $mac_no ?>'>
                        <input type='hidden' name='page_keep' value='�ڡ����ݻ�'>
                    </td>
                </form>
                <td colspan='1' bgcolor='#d6d3ce' align='center' class='title_font'>
                    <?= $menu_title ?>
                </td>
                <td colspan='1' bgcolor='#d6d3ce' align='center' width='140' class='today_font'>
                    <?php echo $today ?>
                </td>
            </tr>
        </table>
            </td></tr>
        </table> <!-- ���ߡ�End -->
        <hr color='797979'>

        <!----------------- ���Ф���ɽ�� ------------------------>
        <table width='100%' bgcolor='#d6d3ce'  border='1' cellspacing='0' cellpadding='1'>
        <tr><td> <!-- ���ߡ�(�ǥ�������) -->
        <table width=100% bordercolordark='white' bordercolorlight='#bdaa90' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='1'>
            <tr class='sub_font'>
                <form method='post' action='<?php echo $current_script ?>'>
                    <td width='52' bgcolor='green'align='center' valign='center'>
                        <input class='pt11b' type='submit' name='page_down' value='����'>
                    </td>
                </form>
                <?php if ($page_up_flg) { ?>
                <form method='post' action='<?php echo $current_script ?>'>
                <?php } ?>
                    <td width='52' bgcolor='green'align='center' valign='center'>
                        <input class='pt11b' type='submit' name='page_up' value='����'>
                    </td>
                <?php if ($page_up_flg) { ?>
                </form>
                <?php } ?>
                <td align='center' nowrap>����No</td>
                <td align='center' nowrap><?php echo $parts_no ?></td>
                <td align='center' nowrap>����̾</td>
                <td class='pick_font' align='center' nowrap><?php echo $buhin_name ?></td>
                <td align='center' nowrap>���</td>
                <td class='pick_font' align='center' nowrap><?php echo $buhin_zaisitu ?></td>
                <td align='center' nowrap>�ؼ�No</td>
                <td align='center' nowrap><?php echo $siji_no ?></td>
                <td align='center' nowrap>����</td>
                <td align='center' nowrap><?php echo $koutei ?></td>
                <td align='center' nowrap>�ײ��</td>
                <td align='center' nowrap><?php echo number_format($plan_cnt) ?></td>
            </tr>
        </table>
            </td></tr>
        </table> <!-- ���ߡ�End -->
        <hr color='797979'>

        <!-------------- �ܺ٥ǡ���ɽ���Τ����ɽ����� -------------->
        <table bgcolor='#d6d3ce'  border='1' cellspacing='0' cellpadding='2'>
           <tr><td> <!-- ���ߡ�(�ǥ�������) -->
        <table width=100% align='center' bordercolordark='white' bordercolorlight='#bdaa90' bgcolor='#d6d3ce' border='1' cellspacing='1' cellpadding='1'>
            <th nowrap>No</th>
                <!-- <th nowrap width='80'>����No</th> -->
            <th nowrap width='100'>ǯ����</th>
            <th nowrap width='100'>��ʬ��</th>
                <!-- <th nowrap width='80'>�� ��</th> -->
            <th nowrap width='100'>����</th>
            <th nowrap width='80'>�ù���</th>
                <!--
                <th nowrap>�ѿ�1</th> <th nowrap>�ѿ�2</th>
                <th nowrap>�ѿ�3</th> <th nowrap>�ѿ�4</th> <th nowrap>�ѿ�5</th>
                -->
<?php
    for ($i=0; $i<$rows; $i++) {
        print("<tr class='table_font'>\n");
        print("<td align='center' nowrap bgcolor='#d6d3ce'>" . ($i+1+$_SESSION["s_offset"]) . "</td>\n");
        for ($j=0; $j<$num; $j++) {
            switch ($j) {
            case 0:
                echo " <td align='center' nowrap bgcolor='#d6d3ce'>", $res[$i][$j], "</td>\n";
                break;
            case 1:
                echo " <td align='center' nowrap bgcolor='#d6d3ce'>", $res[$i][$j], "</td>\n";
                // echo " <td align='center' nowrap bgcolor='#d6d3ce'>-</td>\n";
                break;
            case 2:
                $mac_state_txt = equip_machine_state($mac_no, $res[$i][$j], $bg_color, $txt_color);
                echo " <td align='center' nowrap bgcolor='$bg_color'><font color='$txt_color'>$mac_state_txt</font></td>\n";
                break;
            case 3:
                echo " <td align='right' nowrap bgcolor='#d6d3ce'>", number_format($res[$i][$j]), "</td>\n";
                break;
            default:
                if($res[$i][$j]=='')
                    echo " <td align='center' nowrap bgcolor='#d6d3ce'>-</td>\n";
                else
                    echo " <td align='center' nowrap bgcolor='#d6d3ce'>{$res[$i][$j]}</td>\n";
            }
        }
        echo "</tr>\n";
    }
    echo "</table>\n";
    echo "    </td></tr>\n";
    echo "</table>\n";
?>
        <!--
        <table align='center' with=100% border='2' cellspacing='0' cellpadding='0'>
            <form method='post' action='equipment_working_disp.php'>
                <td>
                    <input type='submit' name='return' value='���'>
                    <input type='hidden' name='mac_no' value='<?php echo $mac_no ?>'>
                    <input type='hidden' name='page_keep' value='�ڡ����ݻ�'>
                </td>
            </form>
        </table>
        -->
    </center>
</BODY>
</HTML>
<?php
ob_end_flush();  //Warning: Cannot add header ���к��Τ����ɲá�
?>
