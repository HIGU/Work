<?php
//////////////////////////////////////////////////////////////////////////////
// �������ʹ���ɽ�ξȲ�  �ײ��ֹ��ɽ�� view                                //
//                              Allocated Configuration Parts ������������  //
// Copyright (C) 2004-2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2004/05/28 Created  allo_conf_parts_view.php                             //
// 2004/06/07 �꥿���󥢥ɥ쥹�������ƽи����襻�å�������¸���Ƥ���    //
// 2004/12/08 CC���ʤ�TNKCC��ɽ���ɲ�                                       //
// 2004/12/28 MenuHeader class ����Ѥ��ƶ��̥�˥塼���ڤ�ǧ���������ѹ�   //
//    �ǥ��쥯�ȥ��industry��industry/material���ѹ�unregist����θƽ��б� //
// 2005/01/07 $menu->set_retGET('page_keep', $_REQUEST['material']);������  //
// 2005/01/12 ����̾��trim(substr(midsc,1,25))��trim(substr(midsc,1,21))�ѹ�//
// 2005/01/31 �����ֹ椫����ֹ�إޡ����ѹ� &row={$r} ���ɲä��б�         //
// 2005/02/07 $search = sprintf("where plan_no='%s'", $plan_no); �򢭤��ѹ� //
//            where plan_no='%s' and assy_no='%s'", $plan_no, $assy_no);    //
// 2005/05/20 db_connect() �� funcConnect() ���ѹ� pgsql.php������Τ���    //
// 2006/04/13 <a name='mark'�ˤ��ե���������ư�б��ǡ�setTimeout()���ɲ�  //
// 2006/08/01 ��ץ쥳���ɿ� �������˰�����̵����н�λ���ɲ�               //
// 2006/12/01 ���֥륯��å������פʰ����������뵡ǽ���ɲ�delParts����ɬ��//
// 2006/12/18 �嵭�ε�ǽ��Ȥä�����꥿��������ݻ����뤿��$param�ɲ�  //
// 2007/02/20 parts/����parts/parts_stock_history/parts_stock_view.php���ѹ�//
// 2007/02/22 set_caption()�˹����ֹ��ɲá������ֹ�10pt��11pt,�ٵ�����//
// 2007/03/22 parts_stock_view.php �� parts_stock_history_Main.php ���ѹ�   //
// 2007/03/24 �ǥ��쥯�ȥ�material/��parts/allocate_config/ �ե졼���Ǥ��ѹ�//
// 2020/03/10 �������̤��Ͽ�Ȳ�������Υѥ�᡼�����ɲ�             ���� //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
// ini_set('implicit_flush', 'off');           // echo print �� flush �����ʤ�(�٤��ʤ뤿��) CLI CGI��
// ini_set('max_execution_time', 1200);        // ����¹Ի���=20ʬ CLI CGI��
ob_start('ob_gzhandler');                   // ���ϥХåե���gzip����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��

require_once ('../../../function.php');     // define.php �� pgsql.php �� require_once ���Ƥ���
require_once ('../../../tnk_func.php');     // TNK �˰�¸������ʬ�δؿ��� require_once ���Ƥ���
require_once ('../../../MenuHeader.php');   // TNK ������ menu class
access_log();                               // Script Name �ϼ�ư����

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader(0);                  // ǧ�ڥ����å�0=���̰ʾ� �����=TOP_MENU �����ȥ�̤����

////////////// ����������
$menu->set_site(INDEX_INDUST, 26);          // site_index=30(������˥塼) site_id=26(�������ʹ���ɽ�ξȲ�)
////////////// �꥿���󥢥ɥ쥹����
// $menu->set_RetUrl(INDUST_MENU);          // �̾�ϻ��ꤹ��ɬ�פϤʤ�
//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title('���� ���� ����ɽ �� �Ȳ� *** New *** ');
//////////// �ƽ����action̾�ȥ��ɥ쥹����
$menu->set_action('�߸˷���',   INDUST . 'parts/parts_stock_history/parts_stock_history_Main.php');
//////////// �꥿������ξ�������
if (isset($_REQUEST['plan_cond'])) {    // �ײ��ֹ�����Ͼ��֤�����å�(�ե����फ��θƽ��б�)
    $menu->set_retGET('plan', $_REQUEST['plan_cond']);
}
if (isset($_REQUEST['material'])) {     // ��������̤��Ͽ����θƽ��б�
    $menu->set_retGET('page_keep', $_REQUEST['material']);
    $parts_no = @$_SESSION['stock_parts'];
    if (isset($_REQUEST['row'])) {
        $row_no = $_REQUEST['row'];   // ����ƽФ������ֹ�
        $param  = "&material={$_REQUEST['material']}&row={$_REQUEST['row']}";
    } else {
        $row_no = -1;       // ̤��Ͽ�ꥹ�Ȥ���ƤФ줿��
        $param  = "&material={$_REQUEST['material']}";
    }
} else {
    $parts_no = '';
    $row_no   = '-1';       // ñ�ΤǾȲ񤵤줿��
    $param    = '';
}

// �������̤��Ͽ�Ȳ�������Υѥ�᡼�����ɲ�
// �������̤��Ͽ�Ȳ񤫤�ƽС���Ͽ���̤إ��ԡ��򥯥�å�������������Ͽ(��������)����
// ��äƤ����Ȥ�������������������̤��Ͽ�Ȳ�����Ƚ�����̤���äƤ��ޤ������б�
if( isset($_REQUEST['page_keep']) && $_REQUEST['page_keep'] == 'On' ) {
    $menu->set_retGET('page_keep', '2');
}

if (isset($_REQUEST['aden_flg'])) {     // A���ܺپ���ξȲ񤫤�θƽ��б�
    $menu->set_retGET('page_keep', $_REQUEST['sc_no']);
    $menu->set_retGET('page_keep', $_REQUEST['aden_flg']);
    $sc_no = $_REQUEST['sc_no'];
    $aden_flg = $_REQUEST['aden_flg'];
    $param  = "&sc_no={$_REQUEST['sc_no']}&aden_flg=1";
} else {
    $sc_no = '';
    $aden_flg = '';
    $param  = '';
}
//////////// �ײ��ֹ桦�����ֹ��ꥯ�����Ȥ������(�������������Ͽ�ǻ���)
if (isset($_REQUEST['plan_no'])) {
    $plan_no = $_REQUEST['plan_no'];
    $_SESSION['material_plan_no'] = $plan_no;   // ���å�������¸
    $_SESSION['plan_no'] = $plan_no;            // �ե������ѤΥǡ����ˤ���¸
    //////////// �ײ��ֹ桦�����ֹ�򥻥å���󤫤����(�ե����फ��ξȲ�ǻ���)
} elseif (isset($_SESSION['plan_no'])) {
    $plan_no = $_SESSION['plan_no'];
} else {
    $_SESSION['s_sysmsg'] .= '�ײ��ֹ椬���ꤵ��Ƥʤ���';      // .= ��å��������ɲä���
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // ľ���θƽи������
    exit();
}
///// �����ֹ桦�����ֹ�μ���
$query = "SELECT parts_no, note15 from assembly_schedule where plan_no='{$plan_no}'";
if (getResult2($query, $assy_res) <= 0) {
    // .= ��å��������ɲä���
    $_SESSION['s_sysmsg'] .= "�ײ��ֹ桧{$plan_no} �ײ�ǡ������ʤ����� Assy�ֹ���������ޤ���";
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // ľ���θƽи������
    exit();
} else {
    $assy_no = $assy_res[0][0];
    $kouji_no = $assy_res[0][1];
    if (substr($assy_no, 0, 1) == 'C') {    // assy_no��Ƭ����ǻ�������Ƚ��
        define('RATE', 25.60);  // ���ץ�
    } else {
        define('RATE', 37.00);  // ��˥�(����ʳ��ϸ��ߤʤ�)
    }
}

//////////// ����̾�μ���
$query = "select midsc from miitem where mipn='{$assy_no}'";
if ( getUniResult($query, $assy_name) <= 0) {           // ����̾�μ���
    $_SESSION['s_sysmsg'] .= "����̾�μ����˼���";      // .= ��å��������ɲä���
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // ľ���θƽи������
    exit();
}

//////////// ɽ�������
$menu->set_caption("�ײ��ֹ桧{$plan_no}�������ֹ桧{$assy_no}������̾��{$assy_name}��<span style='color:red;'>������{$kouji_no}</span>");

//////////// SQL ʸ�� where ��� ���Ѥ���
$search = sprintf("where plan_no='%s' and assy_no='%s'", $plan_no, $assy_no);
// $search = '';

//////////// ��ץ쥳���ɿ���������ʿ��μ���     (�оݥǡ����κ������ڡ�������˻���)
$query = sprintf("select count(*) from allocated_parts %s", $search);
if ( getUniResult($query, $maxrows) <= 0) {         // $maxrows �μ���
    $_SESSION['s_sysmsg'] .= "������ʿ��μ���������ޤ���Ǥ�����";      // .= ��å��������ɲä���
} else {
    if ($maxrows <= 0) {
        $_SESSION['s_sysmsg'] .= "����������ޤ���";      // .= ��å��������ɲä���
        header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // ľ���θƽи������
        exit();
    }
}


//////////// ���פʰ������ʤκ������ 2006/12/01 ADD
if (isset($_REQUEST['delParts'])) {
    if (getCheckAuthority(23)) {
        $sql = "
            DELETE FROM allocated_parts WHERE plan_no='{$plan_no}' AND parts_no='{$_REQUEST['delParts']}'
        ";
        if (query_affected($sql) <= 0) {
            $_SESSION['s_sysmsg'] = "{$_REQUEST['delParts']} �κ���˼��Ԥ��ޤ�����";
        } else {
            $_SESSION['s_sysmsg'] = "{$_REQUEST['delParts']} �������ޤ�����";
        }
    } else {
        $_SESSION['s_sysmsg'] = '������븢�¤�����ޤ���';
    }
}

//////////// �֥饦�����Υ���å����к���
$uniq = $menu->set_useNotCache('alloConf');

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
<link rel='stylesheet' href='allo_conf_parts.css?id=<?php echo $uniq ?>' type='text/css' media='screen'>
<!-- <script type='text/javascript' src='allo_conf_parts.js?<?php echo $uniq ?>'></script> -->
<script language="JavaScript">
<!--
/* ������ϥե�����Υ�����Ȥ˥ե������������� */
function set_focus(){
    // <a name='mark' �ǥե����������ܤ뤿��0.1�ä��餷�ƥե��������򥻥åȤ��롣
    // �ե졼����ڤäƤ��ʤ�����ե����������Ѥ����mark�ؤ����ʤ����ᥳ����
    // setTimeout("document.mhForm.backwardStack.focus()", 100);  //��������ѹ���NN�б�
}
// -->
</script>

<style type="text/css">
<!--
body {
    overflow-x:         hidden;
    overflow-y:         hidden;
}
-->
</style>
</head>
<body>
<center>
<?php echo $menu->out_title_border() ?>
        
        <!----------------- ������ ���� ���� �Υե����� ---------------->
        <table width='100%' cellspacing='0' cellpadding='0' border='0'>
            <tr>
                <td nowrap align='center' class='caption_font'>
                    <?php echo $menu->out_caption() . "\n" ?>
                </td>
            </tr>
        </table>
        
<?php
if ($aden_flg == '1') {
    echo "<iframe hspace='0' vspace='0' frameborder='0' scrolling='yes' src='allo_conf_parts_ViewHeader_aden.html?{$uniq}' name='header' align='center' width='98%' height='42' title='����'>\n";
    echo "    ���ܤ�ɽ�����Ƥ��ޤ���\n";
    echo "</iframe>\n";
    echo "<iframe hspace='0' vspace='0' frameborder='0' scrolling='yes' src='allo_conf_parts_ViewBody_aden.php?", $_SERVER['QUERY_STRING'], "&{$uniq}#mark' name='list' align='center' width='98%' height='77%' title='����'>\n";
    echo "    ������ɽ�����Ƥ��ޤ���\n";
    echo "</iframe>\n";
} else {
    echo "<iframe hspace='0' vspace='0' frameborder='0' scrolling='yes' src='allo_conf_parts_ViewHeader.html?{$uniq}' name='header' align='center' width='98%' height='42' title='����'>\n";
    echo "    ���ܤ�ɽ�����Ƥ��ޤ���\n";
    echo "</iframe>\n";
    echo "<iframe hspace='0' vspace='0' frameborder='0' scrolling='yes' src='allo_conf_parts_ViewBody.php?", $_SERVER['QUERY_STRING'], "&{$uniq}#mark' name='list' align='center' width='98%' height='77%' title='����'>\n";
    echo "    ������ɽ�����Ƥ��ޤ���\n";
    echo "</iframe>\n";
}
// echo "<iframe hspace='0' vspace='0' frameborder='0' scrolling='yes' src='allo_conf_parts_ViewFooter.php?rows={$maxrows}&{$uniq}' name='footer' align='center' width='100%' height='32' title='�եå���'>\n";
// echo "    �եå�����ɽ�����Ƥ��ޤ���\n";
// echo "</iframe>\n";
?>
        
</center>
</body>
<?php echo $menu->out_alert_java()?>
</html>
<?php
ob_end_flush();                 // ���ϥХåե���gzip���� END
?>
