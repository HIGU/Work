<?php
//////////////////////////////////////////////////////////////////////////////
// ��Ω������ư���������ƥ�� ���߱�ž���� ɽ��  �ե졼�����               //
// Copyright (C) 2021-2021 norihisa_ooya@nitto-kohki.co.jp                  //
// Changed history                                                          //
// 2021/03/26 Created  equip_work_moni.php                                  //
// 2021/06/22 ��˥塼���㤦�Τ�6����ʳ���ɽ�����ʤ��ͤ��ѹ�          ��ë //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
// ini_set('implicit_flush', 'off');        // echo print �� flush �����ʤ�(�٤��ʤ뤿��) CLI CGI��
// ini_set('max_execution_time', 1200);     // ����¹Ի���=20ʬ CLI CGI��
ob_start('ob_gzhandler');                   // ���ϥХåե���gzip����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��
require_once ('../../function.php');        // define.php �� pgsql.php �� require_once ���Ƥ���
require_once ('../../tnk_func.php');        // TNK �˰�¸������ʬ�δؿ��� require_once ���Ƥ���
require_once ('../../MenuHeader.php');      // TNK ������ menu class
require_once ('../../ControllerHTTP_Class.php');    // TNK ������ MVC Controller Class
require_once ('../equip_function.php');             // �����ط��ζ��� function
access_log();                               // Script Name �ϼ�ư����

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader();                   // ǧ�ڥ����å���ԤäƤ���
////////////// ����������
$menu->set_site(40, 9);                     // site_index=40(������˥塼) site_id=9(��ž�����)

/////////// �����ʬ�ȹ���̾���������
$fact_name = getFactory($factoryList);

////////////// �꥿���󥢥ɥ쥹����
//$menu->set_RetUrl(EQUIP_MENU2);              // �̾�ϻ��ꤹ��ɬ�פϤʤ�
$menu->set_RetUrl(EQUIP_MENU3);              // �̾�ϻ��ꤹ��ɬ�פϤʤ�

//////////// �ե졼��θƽ���Υ��������(frame)̾�ȥ��ɥ쥹����
$menu->set_frame('Header', EQUIP2 . 'work/equip_work_moniHeader.php');
$menu->set_frame('List'  , EQUIP2 . 'work/equip_work_moniList.php');
//////////// �ե졼��θƽ���Υ��������(frame)̾�ȥ��ɥ쥹����
$menu->set_frame('Header2', EQUIP2 . 'work/equip_work_monigraphHeader.php');
$menu->set_frame('List2'  , EQUIP2 . 'work/equip_work_monigraphList.php');
// �ե졼���Ǥ� $menu->set_action()�ǤϤʤ�$menu->set_frame()����Ѥ���

///// GET/POST�Υ����å�&����
if (isset($_REQUEST['factory'])) {
    $parm = '?factory=' . $_REQUEST['factory'];
    $factory = $_REQUEST['factory'];
    $_SESSION['factory'] = $factory;
} else {
    ///// �ꥯ�����Ȥ�̵����Х��å���󤫤鹩���ʬ��������롣(�̾�Ϥ��Υѥ�����)
    $factory = @$_SESSION['factory'];
    $parm = "?factory={$factory}";
}
///// GET/POST�Υ����å�&����
if (isset($_REQUEST['view'])) {
    $parm = '?view=' . $_REQUEST['view'];
    $view = $_REQUEST['view'];
    $_SESSION['view'] = $view;
} elseif (isset($_SESSION['view'])) {
    ///// �ꥯ�����Ȥ�̵����Х��å���󤫤������������롣(�̾�Ϥ��Υѥ�����)
    $view = @$_SESSION['view'];
    $parm = "?view={$view}";
} else {
    $view = "����";
    $parm = "?view={$view}";
}
if($view == '�����') {
    //////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
    $menu->set_title('���߱�ž�� ����ɽ�ʥ���ա�');
} else {
    //////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
    $menu->set_title('���߱�ž�� ����ɽ�ʥꥹ�ȡ�');
}

//////////// �֥饦�����Υ���å����к���
$uniq = $menu->set_useNotCache('alloConf');

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
<?php echo $menu->out_site_java() ?>
<?php echo $menu->out_css() ?>
<style type='text/css'>
<!--
.pt8 {
    font-size:   0.6em;
    font-weight: normal;
    font-family: monospace;
}
.pt9 {
    font-size:   0.7em;
    font-weight: normal;
    font-family: monospace;
}
.pt10 {
    font-size:   0.8em;
    font-weight: normal;
    font-family: monospace;
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
.pt13b {
    font-size:   1.1em;
    font-weight: bold;
    /* font-family: monospace; */
}
.pt14b {
    font-size:   1.2em;
    font-weight: bold;
    /* font-family: monospace; */
}
select {
    background-color:   teal;
    color:              white;
}
.sub_font {
    font-size:      0.95em;
    font-weight:    bold;
    font-family:    monospace;
}
.pick_font {
    font-size:      0.75em;
    font-weight:    bold;
    font-family: monospace;
}
th {
    font-size:      0.95em;
    font-weight:    bold;
    font-family:    monospace;
    color:              blue;
    background-color:   yellow;
}
.item {
    position: absolute;
    top:    90px;
    left:    0px;
}
-->
</style>
<form name='MainForm' method='post'>
    <input type='hidden' name='select' value=''>
</form>
<script language="JavaScript">
<!--
/* ������ϥե�����Υ�����Ȥ˥ե������������� */
function set_focus() {
    // document.mac_form.factory.focus();      // �������륭���ǹ�����ư����褦�ˤ���
}
    function parts_upper(obj) {
    obj.parts_no.value = obj.parts_no.value.toUpperCase();
    return true;
}
function win_open(url) {
    var w = 800;
    var h = 600;
    var left = (screen.availWidth  - w) / 2;
    var top  = (screen.availHeight - h) / 2;
    window.open(url, 'view_win', 'width='+w+',height='+h+',scrollbars=yes,status=no,toolbar=no,location=no,menubar=no,top='+top+',left='+left);
}
// -->
</script>
</head>
<body style='overflow-y:hidden;'>
<center>
<?php echo $menu->out_title_border() ?>
    
    <!----------------- ���Ф���ɽ�� ------------------------>
    <table bgcolor='#d6d3ce'  border='1' cellspacing='0' cellpadding='1'>
        <tr><td> <!-- ���ߡ�(�ǥ�������) -->
    <table class='winbox_field' width='100%' border='0' cellspacing='0' cellpadding='1'>
        <tr class='sub_font'>
            <td class='winbox'>
                <input style='font-size:0.8em; font-weight:bold; color:blue;' type='submit' name='list_help' value='����' onClick='win_open("list_help.html")'>
            </td>
            <td class='winbox' align='center' width='100'>
                <form name='mac_form' method='post' action='<?php echo $menu->out_self() ?>'>
                <select name='factory' class='ret_font' onChange='document.mac_form.submit()'>
                    <!--
                    <option value='' <?php if($factory=='') echo 'selected'; ?>>������</option>
                    <option value='1' <?php if($factory==1) echo 'selected'; ?>>������</option>
                    <option value='2' <?php if($factory==2) echo 'selected'; ?>>������</option>
                    <option value='4' <?php if($factory==4) echo 'selected'; ?>>������</option>
                    <option value='5' <?php if($factory==5) echo 'selected'; ?>>������</option>
                    -->
                    <option value='6' <?php if($factory==6) echo 'selected'; ?>>������</option>
                    <!--
                    <option value='7' <?php if($factory==7) echo 'selected'; ?>>������</option>
                    -->
                </select>
                </form>
            </td>
            <td class='winbox'>
                <form action='<?php echo $menu->out_self()?>' method='get' target='_self'>
                    <input style='font-size:0.8em; color:blue;' type='submit' name='reload' value='��ɽ��'>
                        <input type='hidden' name='factory' value='<?php echo $factory?>'>
                </form>
            </td>
            <td class='winbox'>
                <form action='equip_work_monimap.php' method='get' target='_self'>
                    <input style='font-size:0.8empt; color:blue;' type='submit' name='map_view' value='�쥤������'>
                    <input type='hidden' name='factory' value='<?php echo $factory?>'>
                </form>
            </td>
            <?php
            if ($_SESSION['User_ID'] == '300144') {
                if ($view == '�����') {
            ?>
            <td class='winbox'>
                <form action='<?php echo $menu->out_self()?>' method='get' target='_self'>
                    <input style='font-size:0.8empt; color:blue;' type='submit' name='view' value='����'>
                    <input type='hidden' name='factory' value='<?php echo $factory?>'>
                </form>
            </td>
                <?php
                } else {
                ?>
            <!--
            <td class='winbox'>
                <form action='<?php echo $menu->out_self()?>' method='get' target='_self'>
                    <input style='font-size:0.8empt; color:blue;' type='submit' name='view' value='�����'>
                    <input type='hidden' name='factory' value='<?php echo $factory?>'>
                </form>
            </td>
            -->
                <?php
                }
                ?>
            <?php
            }
            ?>
        </tr>
    </table>
        </td></tr>
    </table> <!-- ���ߡ�End -->
    <?php
    if ($view == '�����') {
    ?>
    <iframe hspace='0' vspace='0' frameborder='0' scrolling='yes' src='<?php echo $menu->out_frame('Header2') . $parm ?>' name='header' align='center' width='100%' height='30' title='����'>;
        ���ܤ�ɽ�����Ƥ��ޤ���
    </iframe>
    <iframe hspace='0' vspace='0' frameborder='0' scrolling='yes' src='<?php echo $menu->out_frame('List2') . $parm ?>' name='list' align='center' width='100%' height='80%' title='����'>
        ������ɽ�����Ƥ��ޤ���
    </iframe>
    <?php
    } else {
    ?>
    <iframe hspace='0' vspace='0' frameborder='0' scrolling='yes' src='<?php echo $menu->out_frame('Header') . $parm ?>' name='header' align='center' width='100%' height='30' title='����'>;
        ���ܤ�ɽ�����Ƥ��ޤ���
    </iframe>
    <iframe hspace='0' vspace='0' frameborder='0' scrolling='yes' src='<?php echo $menu->out_frame('List') . $parm ?>' name='list' align='center' width='100%' height='80%' title='����'>
        ������ɽ�����Ƥ��ޤ���
    </iframe>
    <?php
    }
    ?>
</center>
</body>
<?php echo $menu->out_alert_java()?>
</html>
<?php ob_end_flush(); // ���ϥХåե���gzip���� END ?>
