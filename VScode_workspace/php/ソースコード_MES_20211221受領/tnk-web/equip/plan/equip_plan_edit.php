<?php
//////////////////////////////////////////////////////////////////////////////
// ������ư���������ƥ�� �������塼�� �Խ�                                 //
// Copyright (C) 2004-2004 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2004/09/04 Created  equip_plan_edit.php                                  //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
ob_start('ob_gzhandler');                   // ���ϥХåե���gzip����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��
require_once ('../equip_function.php');     // ������˥塼 ���� function (function.php��ޤ�)
require_once ('../../MenuHeader.php');      // TNK ������ menu class
access_log();                               // Script Name �ϼ�ư����

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader();                   // ǧ�ڥ����å�0=���̰ʾ� �����=���å������ �����ȥ�̤����

////////////// ����������
// $menu->set_site(40, 8);                     // site_index=40(������˥塼) site_id=8(�������塼�顼)

if (isset($_REQUEST['inst_no'])) {
    $inst_no = $_REQUEST['inst_no'];
} else {
    $inst_no = '';
}
if (isset($_REQUEST['koutei'])) {
    $koutei = $_REQUEST['koutei'];
} else {
    $koutei = '';
}
if (isset($_REQUEST['mac_no'])) {
    $mac_no_edit = @$_REQUEST['mac_no'];
} else {
    $mac_no_edit = '';
}
if (isset($_REQUEST['yyyy'])) {
    $yyyy_edit = @$_REQUEST['yyyy'];
} else {
    $yyyy_edit = '';
}
if (isset($_REQUEST['mm'])) {
    $mm_edit = @$_REQUEST['mm'];
} else {
    $mm_edit = '';
}
if (isset($_REQUEST['dd'])) {
    $dd_edit = @$_REQUEST['dd'];
} else {
    $dd_edit = '';
}
if (isset($_REQUEST['hh'])) {
    $hh_edit = @$_REQUEST['hh'];
} else {
    $hh_edit = '';
}
if (isset($_REQUEST['ii'])) {
    $ii_edit = @$_REQUEST['ii'];
} else {
    $ii_edit = '';
}
if (isset($_REQUEST['ss'])) {
    $ss_edit = @$_REQUEST['ss'];
} else {
    $ss_edit = '';
}

if ($inst_no != '') {
    //////////// �����ޥ��������������ֹ桦����̾�Υꥹ�Ȥ����
    $query = "select mac_no
                , mac_name
            from
                equip_machine_master2
            where
                mac_no!=9999
            order by mac_no ASC
    ";
    $res_sel = array();
    if (($rows_sel = getResult($query, $res_sel)) < 1) {
        $_SESSION['s_sysmsg'] .= "�����ޥ��������ɹ��˼���";
    } else {
        $mac_no_name = array();
        for ($i=0; $i<$rows_sel; $i++) {
            $mac_no_name[$i] = $res_sel[$i]['mac_no'] . " " . trim($res_sel[$i]['mac_name']);   // �����ֹ��̾�Τδ֤˥��ڡ����ɲ�
        }
    }
    
    /////////// �ù��ؼ��ơ��֥��ꥹ�����塼������
    $query = "select inst_no                AS inst_no
                    ,koutei                 AS koutei
                    ,pro_mark               AS pro_mark
                    ,pro_cost               AS pro_cost
                    ,i.parts_no             AS parts_no
                    ,substr(midsc, 1, 12)   AS parts_name
                    ,to_char(delivery, '9999-99-99') AS delivery
                    ,to_char(str_date, 'YYYY-MM-DD') AS str_date
                    ,to_char(end_date, 'YYYY-MM-DD') AS end_date
                    ,to_char(str_date, 'HH24:MI-SS') AS str_time
                    ,to_char(end_date, 'HH24-MI-SS') AS end_time
                    ,mac_no                 AS mac_no
                from
                    equip_work_instruction AS i
                left outer join
                    miitem
                on
                    (parts_no=mipn)
                left outer join
                    equip_work_inst_header
                using
                    (inst_no)
                where
                    inst_no = {$inst_no}
                    and
                    koutei = {$koutei}
                limit 1
    ";
    $res = array();
    if ( ($rows=getResult($query, $res)) <= 0) {
        $_SESSION['s_sysmsg'] = "{$mac_no}��{$mac_name} �ϥ������塼��ǡ���������ޤ���";
    } else {
        $pro_mark   = $res[0]['pro_mark'];
        $pro_cost   = $res[0]['pro_cost'];
        $parts_no   = $res[0]['parts_no'];
        $parts_name = $res[0]['parts_name'];
        $delivery   = $res[0]['delivery'];
        $str_date   = $res[0]['str_date'];
        $end_date   = $res[0]['end_date'];
        $str_time   = $res[0]['str_time'];
        $end_time   = $res[0]['end_time'];
        $mac_no     = $res[0]['mac_no'];
    }
    
    //////////////// �����ޥ��������鵡��̾�����
    $query = "select mac_name
                from
                    equip_machine_master2
                where
                    mac_no={$mac_no}
                limit 1
    ";
    if (getUniResult($query, $mac_name) <= 0) {
        $mac_name = '��';
    }
    
    if ($mac_no_edit != '') $mac_no = $mac_no_edit;
    if ($str_date == '') {
        $yyyy = date('Y');
        $mm   = date('m');
        $dd   = date('d');
        $hh   = 0;
        $ii   = 0;
        $ss   = 0;
        // $hh   = date('H');
        // $ii   = date('i');
        // $ss   = date('s');
        $str_date = "<font color='red'>̤����</font>";
    } else {
        $yyyy = substr($str_date, 0, 4);
        $mm   = substr($str_date, 5, 2);
        $dd   = substr($str_date, 8, 2);
        $hh   = substr($str_time, 0, 2);
        $ii   = substr($str_time, 3, 2);
        $ss   = substr($str_time, 6, 2);
    }
    if ($yyyy_edit != '') $yyyy = $yyyy_edit;
    if ($mm_edit != '') $mm = $mm_edit;
    if ($dd_edit != '') $dd = $dd_edit;
    if ($hh_edit != '') $hh = $hh_edit;
    if ($ii_edit != '') $ii = $ii_edit;
    if ($ss_edit != '') $ss = $ss_edit;
    if ($end_date == '') {
        $end_date = "<font color='red'>̤�׻�</font>";
    }
} else {
    $pro_mark   = '��';
    $pro_cost   = '��';
    $parts_no   = '��';
    $parts_name = '��';
    $delivery   = '��';
    $str_date   = '��';
    $end_date   = '��';
    $mac_no     = '��';
    $mac_name   = '��';
}

//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title('�������塼����Խ�');

/////////// HTML Header ����Ϥ��ƥ���å��������
$menu->out_html_header();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<title><?= $menu->out_title() ?></title>
<?= $menu->out_css() ?>
<style type='text/css'>
<!--
th {
    font-size:      11.5pt;
    font-weight:    bold;
    font-family:    monospace;
}
.item {
    position: absolute;
    /* top: 100px; */
    left: 90px;
}
.msg {
    position: absolute;
    top:  100px;
    left: 350px;
}
.winbox {
    border-style: solid;
    border-width: 1px;
    border-top-color:       #FFFFFF;
    border-left-color:      #FFFFFF;
    border-right-color:     #999999;
    border-bottom-color:    #999999;
    background-color:#d6d3ce;
}
.winbox_field {
    border-style: solid;
    border-width: 1px;
    border-top-color:       #999999;
    border-left-color:      #999999;
    border-right-color:     #FFFFFF;
    border-bottom-color:    #FFFFFF;
    background-color:#d6d3ce;
}
select {
    font-size:      10pt;
    font-weight:    bold;
}
-->
</style>
<script language='JavaScript'>
function winActiveChk() {
    if (document.all) {     // IE�ʤ�
        if (document.hasFocus() == false) {     // IE5.5�ʾ�ǻȤ���
            window.focus();
            return;
        }
        return;
    } else {                // NN �ʤ�ȥ�ꥭ�å�
        window.focus();
        return;
    }
    // <input type='button' value='TEST' onClick="window.opener.location.reload()">
    // parent.Header.�ؿ�̾() or ���֥�������;
}
function win_open(url) {
    var w = 640;
    var h = 480;
    var left = (screen.availWidth  - w) / 2;
    var top  = (screen.availHeight - h) / 2;
    window.open('<?=$menu->out_self()?>', 'edit_win', 'width='+w+',height='+h+',scrollbars=no,status=no,toolbar=no,location=no,menubar=no,top='+top+',left='+left);
}
</script>
</head>
<body onLoad="setInterval('winActiveChk()',100)" style='margin:7%;'>
    <center>
        <table bgcolor='#d6d3ce'  border='1' cellspacing='0' cellpadding='4'>
           <tr><td> <!-- ���ߡ�(�ǥ�������) -->
        <table class='winbox_field' width='100%' border='0' cellspacing='0' cellpadding='6'>
            <tr>
                <th class='winbox' style='font-size:11pt;' width='100'>
                    �ؼ��ֹ�
                </th>
                <td class='winbox' style='font-size:11pt;' width='500'>
                    <?=$inst_no?>
                </td>
            </tr>
            <tr>
                <th class='winbox' style='font-size:11pt;'>
                    �����ֹ�
                </th>
                <td class='winbox' style='font-size:11pt;'>
                    <?=$koutei?>
                </td>
            </tr>
            <tr>
                <th class='winbox' style='font-size:11pt;'>
                    ��������
                </th>
                <td class='winbox' style='font-size:11pt;'>
                    <?=$pro_mark?>
                </td>
            </tr>
            <tr>
                <th class='winbox' style='font-size:11pt;'>
                    �����ֹ�
                </th>
                <td class='winbox' style='font-size:11pt;'>
                    <?=$parts_no?>
                </td>
            </tr>
            <tr>
                <th class='winbox' style='font-size:11pt;'>
                    ����̾��
                </th>
                <td class='winbox' style='font-size:11pt;'>
                    <?=$parts_name?>
                </td>
            </tr>
            <tr>
                <th class='winbox' style='font-size:11pt;'>
                    �����ֹ�
                </th>
                <td class='winbox' style='font-size:11pt;'>
                    <form name='mac_form' method='post' action='<?= $menu->out_self() ?>' target='_self'>
                        <select name='mac_no' class='ret_font' onChange='document.mac_form.submit()'>
                        <?php
                        if ($mac_no == '') echo "<option value=''>��������</option>\n";
                        for ($j=0; $j<$rows_sel; $j++) {
                            if ($mac_no == $res_sel[$j]['mac_no']) {
                                printf("<option value='%s' selected>%s</option>\n", $res_sel[$j]['mac_no'], $mac_no_name[$j]);
                            } else {
                                printf("<option value='%s'>%s</option>\n", $res_sel[$j]['mac_no'], $mac_no_name[$j]);
                            }
                        }
                        ?>
                        </select>
                        <input type='hidden' name='inst_no' value='<?=$inst_no?>'>
                        <input type='hidden' name='koutei' value='<?=$koutei?>'>
                    </form>
                </td>
            </tr>
            <tr>
                <th class='winbox' style='font-size:11pt;'>
                    ����̾��
                </th>
                <td class='winbox' style='font-size:11pt;'>
                    <?=$mac_name?>
                </td>
            </tr>
            <tr>
                <th class='winbox' style='font-size:11pt;'>
                    ��������
                </th>
                <td class='winbox' style='font-size:11pt;'>
                    <form name='str_form' method='post' action='<?= $menu->out_self() ?>' target='_self'>
                        <select name='yyyy' onChange='document.str_form.submit()'>
                        <?php
                        for ($i=($yyyy-5); $i<($yyyy+5); $i++) {
                            if ($yyyy == $i) {
                                printf("<option value='%1\$d' selected>%1\$d</option>\n", $i);
                            } else {
                                printf("<option value='%1\$d'>%1\$d</option>\n", $i);
                            }
                        }
                        ?>
                        </select>
                        ǯ
                        <select name='mm' onChange='document.str_form.submit()'>
                        <?php
                        for ($i=1; $i<=12; $i++) {
                            if ($mm == $i) {
                                printf("<option value='%1\$02d' selected>%1\$02d</option>\n", $i);
                            } else {
                                printf("<option value='%1\$02d'>%1\$02d</option>\n", $i);
                            }
                        }
                        ?>
                        </select>
                        ��
                        <select name='dd' onChange='document.str_form.submit()'>
                        <?php
                        for ($i=1; $i<=31; $i++) {
                            if ($dd == $i) {
                                printf("<option value='%1\$02d' selected>%1\$02d</option>\n", $i);
                            } else {
                                printf("<option value='%1\$02d'>%1\$02d</option>\n", $i);
                            }
                        }
                        ?>
                        </select>
                        ��
                        <select name='hh' onChange='document.str_form.submit()'>
                        <?php
                        for ($i=0; $i<=23; $i++) {
                            if ($hh == $i) {
                                printf("<option value='%1\$02d' selected>%1\$02d</option>\n", $i);
                            } else {
                                printf("<option value='%1\$02d'>%1\$02d</option>\n", $i);
                            }
                        }
                        ?>
                        </select>
                        ��
                        <select name='ii' onChange='document.str_form.submit()'>
                        <?php
                        for ($i=0; $i<=59; $i++) {
                            if ($ii == $i) {
                                printf("<option value='%1\$02d' selected>%1\$02d</option>\n", $i);
                            } else {
                                printf("<option value='%1\$02d'>%1\$02d</option>\n", $i);
                            }
                        }
                        ?>
                        </select>
                        ʬ
                        <select name='ss' onChange='document.str_form.submit()'>
                        <?php
                        for ($i=0; $i<=59; $i++) {
                            if ($ss == $i) {
                                printf("<option value='%1\$02d' selected>%1\$02d</option>\n", $i);
                            } else {
                                printf("<option value='%1\$02d'>%1\$02d</option>\n", $i);
                            }
                        }
                        ?>
                        </select>
                        ��
                        <input type='hidden' name='inst_no' value='<?=$inst_no?>'>
                        <input type='hidden' name='koutei' value='<?=$koutei?>'>
                    </form>
                </td>
            </tr>
            <tr>
                <th class='winbox' style='font-size:11pt;'>
                    ��λ����
                </th>
                <td class='winbox' style='font-size:11pt;'>
                    <?=$end_date?>
                </td>
            </tr>
            <tr>
                <th class='winbox' style='font-size:11pt;'>
                    ��ʸǼ��
                </th>
                <td class='winbox' style='font-size:11pt;'>
                    <?=$delivery?>
                </td>
            </tr>
            <tr>
                <td class='winbox' colspan='2' align='center'>
                    <input style='font-size:11pt; color:blue;' type='button' name='edit' value='�Ĥ���' onClick='window.close()'>
                    <input type='hidden' name='inst_no' value='<?=$inst_no?>' >
                    <input type='hidden' name='koutei' value='<?=$koutei?>' >
                </td>
            </tr>
        </table> <!----- ���ߡ� End ----->
            </td></tr>
        </table>
    </center>
</body>
</html>
<?php ob_end_flush(); // ���ϥХåե���gzip���� END ?>
