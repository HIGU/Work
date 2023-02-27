<?php
//////////////////////////////////////////////////////////////////////////////
// ������ž ���� ���� �ե�����2 (����)                                      //
// Copyright (C) 2002-2021 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2002/02/15 Created  equipment_working_disp.php                           //
// 2002/08/08 register_globals = Off �б�                                   //
// 2002/08/27 �ե졼���б�                                                  //
// 2003/02/14 �ե���Ȥ�style������ѹ����֥饦�����ˤ���ѹ��Բ�(IE)�ˤ��� //
// 2003/06/18 �ޥ���������ưŪ�˴ƻ��оݵ������������褦���ѹ�            //
// 2004/03/04 ��ư���Ƥ��ʤ�ʪ��̤��Ͽ��ʪ���submit����ʤ��褦�ˤ�����    //
// 2004/03/05 ���ǥơ��֥�����̲���                                        //
// 2004/07/23 Class MenuHeader �����                                       //
// 2005/06/24 F2/F12��������뤿����б��� JavaScript�� set_focus()���ɲ�   //
// 2018/05/18 ��������ɲá������ɣ�����Ͽ����Ū��7���ѹ�            ��ë //
// 2021/06/22 ������SUS��8����ȤʤäƤ��ޤ��Τ�7�ˤʤ�褦�ѹ�        ��ë //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);              // E_ALL='2047' debug ��
// ini_set('display_errors', '1');                 // Error ɽ�� ON debug �� ��꡼���女����
// ini_set('max_execution_time', 1200);            // ����¹Ի���=20ʬ CLI CGI��
ob_start('ob_gzhandler');                       // ���ϥХåե���gzip����
session_start();                                // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��
require_once ('../equip_function.php');         // ���� ���� function
require_once ('../../MenuHeader.php');          // TNK ������ menu class
access_log();                                   // Script Name �ϼ�ư����

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader(0);                      // ǧ�ڥ����å�1=���̰ʾ� �����=���å������ �����ȥ�̤����

////////////// �꥿���󥢥ɥ쥹����
// $menu->set_RetUrl(SYS_MENU);

if (isset($_REQUEST['status'])) {
    $status = $_REQUEST['status'];
    $_SESSION['equip_work_status'] = $status;
} else {
    $status = $_SESSION['equip_work_status'];
}
////////////// ����������
if ($status == 'graph') {
    $menu->set_site(40, 11);                    // site_index=40(������˥塼) site_id=10(���߲�ư��μ̿�)
} else {
    $menu->set_site(40, 10);                    // site_index=40(������˥塼) site_id=10(���߲�ư��μ̿�)
}

//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
if ($status == 'graph') {
    $menu->set_title('���� ��ž ���� ����� ��������');
} else {
    $menu->set_title('���� ��ž ���� ɽ���� ��������');
}
//////////// ɽ�������
$menu->set_caption('�ֻ��ϸ��߲�ư���Ƥ��ʤ������Ǥ���');
//////////// �ƽ����action̾�ȥ��ɥ쥹����
if ($status == 'graph') {
    $menu->set_action('��ư����',   EQUIP2 . 'work/equip_work_graph.php');
} else {
    $menu->set_action('��ư����',   EQUIP2 . 'work/equip_work_chart.php');
}
$menu->set_action('���߲�ưɽ��', EQUIP2 . 'equip_machine_disp.php');   // �쥿���פ�Ĥ�

$uniq = uniqid('href');         // <link href ���ѿ��ǥ��åȤ�ɬ���ɤ߹��ޤ���褦�ˤ��롣
$_SESSION['s_offset'] = 0;      // postgreSQL�Υ����꡼offset��(�����)
define('MAC_ROW', '10');        // ���᡼��ɽ���ιԿ�
define('MAC_COL',  '5');        // ���᡼��ɽ�������

if (isset($_REQUEST['factory'])) {
    $factory = $_REQUEST['factory'];
} else {
    $factory = '';
}
///// �ꥯ�����Ȥ�̵����Х��å���󤫤鹩���ʬ��������롣(�̾�Ϥ��Υѥ�����)
if ($factory == '') {
    $factory = @$_SESSION['factory'];
}

//////////// �����ޥ��������������ֹ桦����̾�Υꥹ�Ȥ����
if ($factory == '') {
    $query = "select mac_no                 AS mac_no
                    , substr(mac_name,1,7)  AS mac_name
                    , factory               AS factory
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
                    , substr(mac_name,1,7)  AS mac_name
                    , factory               AS factory
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
$mac_res = array();
if ( ($mac_rows=getResult($query, $mac_res)) <= 0) {
    $_SESSION['s_sysmsg'] = "<font color='yellow'>�����ޥ���������Ͽ������ޤ���</font>";
    header('Location: ' . $menu->out_RetUrl());        // ľ���θƽи������
    exit();
} else {
    $mac_no   = array();        // ������
    $mac_name = array();        // ����̾��
    $factory  = array();        // ����̾ �㡧1����
    for ($i=0; $i<$mac_rows; $i++) {
        $mac_no[$i]   = $mac_res[$i][0];
        $mac_name[$i] = $mac_res[$i][0] . ' ' . $mac_res[$i][1];
        if ($mac_res[$i][2]==8) {
            $mac_res[$i][2] = 7;
        }
        $factory[$i]  = $mac_res[$i][2];
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
<link rel='stylesheet' href='../equipment.css?<?php echo $uniq ?>' type='text/css' media='screen'> <!-- �ե��������ξ�� -->
<style type="text/css">
<!--
.pt_small {
    font-size:9pt;
}
.fc_red {
    color:red;
}
.fc_blue {
    color:blue;
}
-->
</style>
<script language='JavaScript'>
<!--
/* ������ϥե�����Υ�����Ȥ˥ե������������� */
function set_focus() {
    // document.body.focus();   // F2/F12������ͭ���������б�
    document.mhForm.backwardStack.focus();  // �嵭��IE�ΤߤΤ���NN�б�
}
// -->
</script>
</head>

<body onLoad='set_focus()' bgcolor='#ffffff' text='#000000'>
    <center>
<?= $menu->out_title_border() ?>
        <!-- bgcolor='#d6d3ce' -->
        <table width='100%' border='0'>
            <tr>
                <td align='center' class='pt11b fc_red'>
                    <?= $menu->out_caption() . "\n" ?>
                </td>
            </tr>
            <tr>
                <td align='center' class='pt10'>
                    �����ޥ���������Ͽ����Ƥ��ơ��ƻ�������ꤵ��Ƥ����Τ�ɽ������ޤ���
                </td>
            </tr>
        </table>
        <table width='100%' cellspacing='2' cellpadding='0' border='0'>
            <?php
                $k = 0;     // mac_no[$k] ����index
                for ($i=0; $i<MAC_ROW; $i++) {        // �Կ�
                    echo "<tr>\n";
                    for ($j=0; $j<MAC_COL; $j++) {    // ���
                        echo "<td align='center'>\n";
                        echo "    <form method='post' action='", $menu->out_action('��ư����'), "'>\n";
                        if (isset($mac_no[$k])) {   // ����No�����åȤ���Ƥ��뤫��
                            $img_file = "../img/{$mac_no[$k]}.jpg";        // image�ե�����̾����
                            if (equip_working_chk($mac_no[$k])) {   // ���߲�ư�椫��
                                echo "<input type='hidden' name='mac_no' value='{$mac_no[$k]}'>\n";
                                if ( file_exists($img_file) ) {             // �ե������¸�ߥ����å�
                                    echo "<input type='image' alt='����No{$mac_name[$k]}' border=0 src='{$img_file}'>\n";
                                } else {
                                    echo "<input type='image' alt='����No{$mac_name[$k]}' border=0 src='../img/other.jpg'>\n";
                                }
                                echo "<br clear='all'><font class='pt_small fc_blue'>{$mac_name[$k]} ", mb_convert_kana($factory[$k], "N"), "����</font>\n";
                            } else {
                                echo "<input type='hidden' name='mac_no' value='{$mac_no[$k]}'>\n";
                                if ( file_exists($img_file) ) {             // �ե������¸�ߥ����å�
                                    echo "<img alt='����No{$mac_name[$k]}' border=0 src='{$img_file}'>\n";
                                } else {
                                    echo "<img alt='����No{$mac_name[$k]}' border=0 src='../img/other.jpg'>\n";
                                }
                                echo "<br clear='all'><font class='pt_small fc_red'>{$mac_name[$k]} ", mb_convert_kana($factory[$k], "N"), "����</font>\n";
                            }
                        } else {
                            // echo "<img alt='---------------' border=0 src='../img/other.jpg'>\n";
                            // echo "<br clear='all'><font class='pt_small'>̤��Ͽ</font>\n";
                            echo "</form>\n";
                            echo "</td>\n";
                            break;
                        }
                        $k++;       // index ��ץ饹
                        echo "    </form>\n";
                        echo "</td>\n";
                    }
                    echo "</tr>\n";
                    if (!isset($mac_no[$k])) {   // ����No�����åȤ���Ƥ��ʤ���С�
                        break;
                    }
                }
            ?>
        </table>
    </center>
</body>
</html>
<?php
ob_end_flush();                 // ���ϥХåե���gzip���� END
?>
