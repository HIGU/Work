<?php
//////////////////////////////////////////////////////////////////////////////
// »�״ط��Υ���պ�����˥塼 �������ե�����                            //
// Copyright (C) 2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp           //
// Changed history                                                          //
// 2007/10/04 Created   graphCreate_form.php                                //
// 2007/10/07 ����դ���ɽ������ɽ���ɲá�Y������(����)������(�̡�)���ɲ�   //
// 2007/10/13 X����ǯ���prot1��prot2�̡�������Ǥ��륪�ץ������ɲ�       //
// 2007/11/05 Y���ν����2=�̡���X���ν����on=��ͭ�ؽ���ͤ��ѹ�           //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL | E_STRICT);
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
// ini_set('memory_limit', '64M');             // ���������ɬ�ץ��꡼��­��ʤ����˻���
// ini_set('max_execution_time', 1200);        // ����¹Ի���=20ʬ WEB CGI��
ob_start('ob_gzhandler');                   // ���ϥХåե���gzip����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��

require_once ('../../function.php');        // define.php �� pgsql.php �� require_once ���Ƥ���
require_once ('../../tnk_func.php');        // TNK �˰�¸������ʬ�δؿ��� require_once ���Ƥ���
require_once ('../../MenuHeader.php');      // TNK ������ menu class
require_once ('../../ControllerHTTP_Class.php');// TNK ������ MVC Controller Class
require_once ('graphCreate_Function.php');  // ����պ�����˥塼���Ѵؿ�
access_log();                               // Script Name �ϼ�ư����

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader(0);                  // ǧ�ڥ����å�0=���̰ʾ� �����=TOP_MENU �����ȥ�̤����

////////////// ����������
$menu->set_site(INDEX_PL, 14);              // site_index=(»�ץ�˥塼) site_id=999(̤��)
////////////// �꥿���󥢥ɥ쥹����
// $menu->set_RetUrl(SALES_MENU);              // �̾�ϻ��ꤹ��ɬ�פϤʤ�
//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title('»�� ʬ���� ����պ�����˥塼(������ե�����)');
//////////// ɽ�������
$menu->set_caption('�������륰��դξ�����ꤷ�Ʋ�������');
//////////// �ƽ����action̾�ȥ��ɥ쥹����
$menu->set_action('����պ���',   PL . 'graphCreate/graphCreate_Main.php');

//////////// �ꥯ�����ȤΥ��󥹥��󥹤�����
$request = new Request();
if ($request->get('yaxis') == '') $request->add('yaxis', '2');     // ����ͤ�2���̡����ѹ� 2007/11/05
if ($request->get('dataxFlg') == '') $request->add('dataxFlg', 'on');   // ����ͤ�on���ѹ� 2007/11/05

//////////// �֥饦�����Υ���å����к���
$uniq = $menu->set_useNotCache('dailyGraph');

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
<script type='text/javascript' src='graphCreate.js?<?php echo $uniq ?>'></script>
<style type='text/css'>
<!--
select {
    background-color:   teal;
    color:              white;
    font-size:          1.05em;
    font-weight:        bold;
}
td {
    font-size:          0.85em;
    font-weight:        normal;
    font-family:        monospace;
}
body {
    background-image:       url(<?php echo IMG ?>t_nitto_logo4.png);
    background-repeat:      no-repeat;
    background-attachment:  fixed;
    background-position:    right bottom;
    overflow-y:             hidden;
}
-->
</style>
<script type='text/javascript' language='JavaScript'>
<!--
// -->
</script>
</head>
<body onLoad='GraphCreate.set_focus(document.ConditionForm.yyyymm, "noSelect")'
>
    <center>
<?php echo $menu->out_title_border()?>
        
        <table width='100%' border='0' cellspacing='0' cellpadding='0'>
            <tr><td align='center' valign='top'>
                <table align='center'>
                    <tr><td><p>
                        <img src='<?php echo IMG ?>t_nitto_logo2.gif' width=348 height=83>
                    </p></td></tr>
                </table>
                <table width='100%'>
                    <tr>
                        <td align='center'>
                            <span class='caption_font'><?php echo $menu->out_caption()?></span>
                        </td>
                    </tr>
                    <tr>
                        <td align='center'>
                            <br>
                            <img src='<?php echo IMG ?>tnk-turbine_small.gif'>
                        </td>
                    </tr>
                </table>
                <table border='0' cellspacing='0' cellpadding='5'>
                    <form name='ConditionForm' action='<?php echo $menu->out_action('����պ���')?>' method='get'
                        onSubmit='return GraphCreate.checkConditionForm(this)'
                    >
                        <tr>
                            <td align='left'>
                                ��������գ��λ��ꡡ�ץ�åȣ�<?php echo graphSelectForm('g1plot1', $request->get('g1plot1')) ?>���ץ�åȣ�<?php echo graphSelectForm('g1plot2', $request->get('g1plot2')) ?>
                            </td>
                        </tr>
                        <tr>
                            <td align='left'>
                                ��������գ��λ��ꡡ�ץ�åȣ�<?php echo graphSelectForm('g2plot1', $request->get('g2plot1')) ?>���ץ�åȣ�<?php echo graphSelectForm('g2plot2', $request->get('g2plot2')) ?>
                            </td>
                        </tr>
                        <tr>
                            <td align='left'>
                                ��������գ��λ��ꡡ�ץ�åȣ�<?php echo graphSelectForm('g3plot1', $request->get('g3plot1')) ?>���ץ�åȣ�<?php echo graphSelectForm('g3plot2', $request->get('g3plot2')) ?>
                            </td>
                        </tr>
                        <tr>
                            <td align='left'>
                                ��������դ�Y���λ��ꡡ������
                                <input type='radio' name='yaxis' value='1'<?php echo getRadioChecked($request, 'yaxis', 1)?> id='g11'><label for='g11'>Y������(����)</label>
                                <input type='radio' name='yaxis' value='2'<?php echo getRadioChecked($request, 'yaxis', 2)?> id='g12'><label for='g12'>Y������(�̡�)</label>
                            </td>
                        </tr>
                        <tr>
                            <td align='left'>
                                ������������������������������ 
                                Y�����Ĥϼ�˶�ۺ���ޤ᤿��Ӥ򤹤���˻��Ѥ��ޤ���<br>
                                ������������������������������ 
                                Y�����Ĥϼ�˷�������Ӥ�����˻��Ѥ��ޤ���
                            </td>
                        </tr>
                        <tr>
                            <td align='left'>
                                ��������դν�λǯ��λ��� ��
                                <label for='dataxCheck'>���Ѥξ������å�</label><input type='checkbox' name='dataxFlg' id='dataxCheck'
                                    value='<?php echo $request->get('dataxFlg')?>'<?php if ($request->get('dataxFlg') == 'on') echo ' checked';?>
                                    onClick='GraphCreate.checkboxAction(this);'
                                >
                                �ץ�å�1<?php echo ymFormCreate($request->get('dataxFlg'), $request->get('yyyymm1'), 'yyyymm1', 'onChange="GraphCreate.prot1Action()"') ?>
                                �ץ�å�2<?php echo ymFormCreate($request->get('dataxFlg'), $request->get('yyyymm2'), 'yyyymm2') ?>
                            </td>
                        </tr>
                        <tr>
                            <td align='center'>
                                <input type='submit' name='createGraph' value='�¹�' >
                            </td>
                        </tr>
                    </form>
                </table>
            </td></tr>
        </table>
    </center>
</body>
<?php echo $menu->out_alert_java()?>
</html>
<?php
ob_end_flush();                 // ���ϥХåե���gzip���� END
?>
