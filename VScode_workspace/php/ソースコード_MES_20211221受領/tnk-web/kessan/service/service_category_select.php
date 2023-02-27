<?php
//////////////////////////////////////////////////////////////////////////
// �����ӥ���� �������� ��˥塼                                       //
// Copyright(C) 2003 2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp   //
// Changed history                                                      //
// 2003/10/17 Created   service_category_select.php                     //
// 2003/10/24 submit ��Υ�����ץȤ�ʬ������褦���ѹ�(���ϤȾȲ�)     //
// 2007/01/24 MenuHeader���饹�б�                                      //
//////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug ��
ini_set('error_reporting',E_ALL);           // E_ALL='2047' debug ��
// ini_set('display_errors','1');              // Error ɽ�� ON debug �� 
ob_start('ob_gzhandler');                   // ���ϥХåե���gzip����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��

require_once ("../../function.php");
require_once ("../../tnk_func.php");
require_once ('../../MenuHeader.php');      // TNK ������ menu class
access_log();                               // Script Name �ϼ�ư����

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader(0);                  // ǧ�ڥ����å�0=���̰ʾ� �����=TOP_MENU �����ȥ�̤����

////////////// ����������
$menu->set_site(10,  5);                    // site_index=10(»�ץ�˥塼) site_id=5(�����ӥ�����˥塼)
////////////// �꥿���󥢥ɥ쥹����(���л��ꤹ����)
$menu->set_RetUrl($_SESSION['service_referer']);    // ʬ������������¸����Ƥ���ƽи��򥻥åȤ���
//////////// �ƽ����action̾�ȥ��ɥ쥹����
// $menu->set_action('test_template',   SYS . 'log_view/php_error_log.php');

$current_script  = $_SERVER['PHP_SELF'];        // ���߼¹���Υ�����ץ�̾����¸
$url_referer     = $_SESSION['service_referer'];    // ʬ������������¸����Ƥ���ƽи��򥻥åȤ���

//////////// Submit�襹����ץ�̾�μ���
if (isset($_GET['exec'])) {
    $_GET['exec'] = stripslashes($_GET['exec']);
} else {
    $_GET['exec'] = 'view';     // Default
}
if ($_GET['exec'] == 'entry') {
    $script_name = 'service_percentage_input.php';
} elseif ($_GET['exec'] == 'view') {
    $script_name = 'service_percentage_view.php';
} else {
    $script_name = 'service_percentage_view.php';
}

//////////// �о�ǯ��Υ��å����ǡ�������
if (isset($_SESSION['service_ym'])) {
    $service_ym = $_SESSION['service_ym']; 
} else {
    $service_ym = date("Ym");        // ���å����ǡ������ʤ����ν����(����)
    if (substr($service_ym,4,2) != 01) {
        $service_ym--;
    } else {
        $service_ym = $service_ym - 100;
        $service_ym = $service_ym + 11;   // ��ǯ��12��˥��å�
    }
}

//////////// �����ȥ�����ա���������
$today = date("Y/m/d H:i:s");
//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
if (substr($service_ym,6,2) == '32') {
    $view_ym = substr($service_ym,0,6) . '�軻';
} else {
    $view_ym = $service_ym;
}
if ($_GET['exec'] == 'entry') {
    $menu_title = "$view_ym �����ӥ�������� ��������";
} elseif ($_GET['exec'] == 'view') {
    $menu_title = "$view_ym �����ӥ����Ȳ� ��������";
} else {
    $menu_title = "$view_ym �����ӥ����Ȳ� ��������";
}
//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title($menu_title);
//////////// ɽ�������
$menu->set_caption('��������򤷤Ʋ�������');

//////////// �����ȴ�Ф�
$query = "select trim(section_name), sid from section_master left outer join cate_allocation on sid=group_id
            left outer join act_table on dest_id=act_id
            where orign_id=0 and
                    act_flg='f'
            group by section_name, sid
            order by sid";
$res = array();
if ( ($rows=getResult2($query, $res)) <= 0) {
    $_SESSION['s_sysmsg'] = '�������礬�����Ǥ��ޤ���';
    header("Location: $url_referer");                   // ľ���θƽи������
    exit();
} else {
    for ($i=0; $i<$rows; $i++) {
        $section_name[$i] = $res[$i][0];
        $section_id[$i]   = $res[$i][1];
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
<title><?php echo $menu->out_title() ?></title>
<?php echo $menu->out_site_java() ?>
<?php echo $menu->out_css() ?>
<?php echo $menu->out_jsBaseClass() ?>
<style type="text/css">
<!--
select {
    background-color:teal;
    color:white;
}
textarea {
    background-color:black;
    color:white;
}
input.sousin {
    background-color:red;
}
input.text {
    background-color:black;
    color:white;
}
.pt10b {
    font-size:   0.8em;
    font-weight: bold;
    font-family: monospace;
}
.pt11b {
    font-size:   0.9em;
    font-weight: bold;
    font-family: monospace;
}
.pt12b {
    font-size:   1.0em;
    font-weight: bold;
    font-family: monospace;
}
.explain_font {
    font-size: 8.5pt;
    font-family: monospace;
}
.margin0 {
    margin:0%;
}
.menuButton {
    width:          200px;
    font-size:      1.0em; /* 12pt */
    font-weight:    bold;
}
-->
</style>
</head>
<body style='overflow-y:hidden;'>
    <center>
<?php echo $menu->out_title_border() ?>
        <br>
        <br>
        <table bgcolor='#d6d3ce'  border='1' cellspacing='0' cellpadding='2'>
            <tr><td> <!-- ���ߡ�(�ǥ�������) -->
            <table bgcolor='#d6d3ce' cellspacing='1' cellpadding='5' border='1' bordercolordark='white' bordercolorlight='#bdaa90'>
                <tr>
                    <td align='center' colspan='1' class='pt11b'>
                        <?php echo $menu->out_caption(), "\n" ?>
                    </td>
                </tr>
                <?php
            for ($i=0; $i<$rows; $i++) {
                echo "<form action='$script_name' method='post'>\n";
                echo "<tr>\n";
                echo "    <td align='center' bgcolor='#ceffce'>\n";
                echo "        <input class='menuButton' type='submit' name='section_name' value='{$section_name[$i]}'>\n";
                echo "        <input type='hidden' name='section_id' value='{$section_id[$i]}'>\n";
                echo "    </td>\n";
                echo "</tr>\n";
                echo "</form>\n";
            }
                ?>
            </table>
            </td></tr>
        </table> <!-- ���ߡ�End -->
    </center>
</body>
</html>
<?php ob_end_flush(); // ���ϥХåե���gzip���� END ?>
