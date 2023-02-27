<?php
//////////////////////////////////////////////////////////////////////////////
// ������ ���ʺ߸�ͽ�� �Ȳ� ���ʻ���ե�����                                //
// Copyright(C) 2007      Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp       //
// Changed history                                                          //
// 2007/02/20 Created  parts_stock_plan_form.php                            //
// 2007/05/22 ����ɬ�����ξȲ���ɲ� requireDate�Υꥯ�����ȥ����쥯�Ƚ���  //
// 2007/06/22 noMenu��Ajax���Ϥ�����hidden°���ǥե����������ɲ�            //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);           // E_STRICT=2048(php5) E_ALL=2047 debug ��
ini_set('error_reporting', E_ALL);              // E_ALL='2047' debug ��
ini_set('display_errors', '1');                 // Error ɽ�� ON debug �� ��꡼���女����
// ini_set('zend.ze1_compatibility_mode', '1');    // zend 1.X ����ѥ� php4�θߴ��⡼��
// ini_set('implicit_flush', 'off');               // echo print �� flush �����ʤ�(�٤��ʤ뤿��) CLI CGI��
// ini_set('max_execution_time', 1200);            // ����¹Ի���=20ʬ CLI CGI��
ob_start('ob_gzhandler');                       // ���ϥХåե���gzip����
session_start();                                // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��

require_once ('../../../function.php');                 // define.php �� pgsql.php �� require_once ���Ƥ���
require_once ('../../../tnk_func.php');                 // TNK �˰�¸������ʬ�δؿ��� require_once ���Ƥ���
require_once ('../../../MenuHeader.php');               // TNK ������ menu class
require_once ('../../../ControllerHTTP_Class.php');     // TNK ������ MVC Controller Class
access_log();                                           // Script Name �ϼ�ư����

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader(0);                  // ǧ�ڥ����å�0=���̰ʾ� �����=TOP_MENU �����ȥ�̤����

////////////// ����������
$menu->set_site(INDEX_INDUST, 16);          // site_index=INDEX_INDUST(������˥塼) site_id=16(�߸�ͽ��)999(�����Ȥ򳫤�)
////////////// �꥿���󥢥ɥ쥹����
// $menu->set_RetUrl(SYS_MENU);                // �̾�ϻ��ꤹ��ɬ�פϤʤ�
//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title('���ʺ߸�ͽ��Ȳ�(������ȯ�����)');
//////////// ɽ�������
$menu->set_caption('�����ֹ�');
//////////// �ƽ����action̾�ȥ��ɥ쥹����
$menu->set_action('�߸�ͽ��Ȳ�',   INDUST . 'parts/parts_stock_plan/parts_stock_plan_Main.php');
//////////// �߸˷���Ȳ񤫤�ƽФ���Ƥ��ʤ���Х��������򥻥å�
if (preg_match('/parts_stock_view.php/', $menu->out_RetUrl())) {
    $menu->set_retGet('material', '1');
    $stockViewFlg = false;
} else {
    $menu->set_action('�߸˷���Ȳ�',   INDUST . 'parts/parts_stock_history/parts_stock_view.php');
    $stockViewFlg = true;
}

//////////// �ꥯ�����ȤΥ��󥹥��󥹤���Ͽ
$request = new Request();

//////////// JavaScript Stylesheet File ��ɬ���ɤ߹��ޤ���
$uniq = uniqid('target');

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
<link rel='stylesheet' href='parts_stock_plan.css?id=<?php echo $uniq ?>' type='text/css' media='screen'>
<style type='text/css'><!-- --></style>
<script type='text/javascript' src='parts_stock_plan.js?<?php echo $uniq ?>'></script>
</head>
<body style='overflow-y:hidden;'
    onLoad='
        PartsStockPlan.set_focus(document.ConditionForm.targetPartsNo, "select");
        // PartsStockPlan.intervalID = setInterval("PartsStockPlan.blink_disp(\"blink_item\")", 1300);
        <?php if ($request->get('targetPartsNo') != '') echo 'PartsStockPlan.checkANDexecute(document.ConditionForm, 1)'; ?>
    '
>
<center>
<?php echo $menu->out_title_border() ?>
    
    <form name='ConditionForm' action='<?php echo $menu->out_action('�߸�ͽ��Ȳ�') ?>' method='post'
        onSubmit='return PartsStockPlan.checkConditionForm(this)'
    >
        <!----------------- ������ ��ʸ��ɽ������ ------------------->
        <table bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='3'>
            <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
        <table class='winbox_field' width='100%' border='1' cellspacing='0' cellpadding='1'>
            <tr>
                    <!--  bgcolor='#ffffc6' �������� --> 
                <td colspan='7' align='center' class='winbox caption_color'>
                    <span id='blink_item'>�����ֹ�</span>
                </td>
                <td class='winbox' align='center'>
                    <input type='text' name='targetPartsNo' size='9' class='pt12b' value='<?php echo $request->get('targetPartsNo'); ?>' maxlength='9'
                        onKeyUp='PartsStockPlan.keyInUpper(this);'
                    >
                </td>
                <td class='winbox' align='center'>
                    <input type='submit' name='exec1' value='�¹�' title='Enter �����򲡤������ܥ���򥯥�å�����С��¹Ԥ��ޤ���'>
                    &nbsp;
                    <input type='button' name='exec2' value='����' style='width:54px;' onClick='PartsStockPlan.checkANDexecute(document.ConditionForm, 2);' title='�̥�����ɥ���ɽ�����ޤ���'>
                    &nbsp;
                    <input type='button' name='clear' value='���ꥢ' style='width:54px;' disabled>
                    &nbsp;
                    <!-- <input type='button' name='exec3' value='ɬ����' style='width:54px;' onClick='PartsStockPlan.checkANDexecute(document.ConditionForm, 3);' title='����å�����С����β���ȯ�������������Τߤˤ�ɬ������ɽ�����ޤ���'> -->
                    <input type='button' name='exec3' value='ɬ����' style='width:54px;' disabled>
                    &nbsp;
                    <input type='button' name='exec3' value='ɬ����' style='width:54px;' onClick='PartsStockPlan.checkANDexecute(document.ConditionForm, 4);' title='����å�����С��̥�����ɥ���ȯ�������������Τߤˤ�ɬ������ɽ�����ޤ���'>
                    <input type='hidden' name='material' value='1'>
                </td>
                <td class='winbox' align='center'>
                    &nbsp&nbsp<a href='javascript:void(0);' style='color:gray; text-decoration:none;'>�߸˷���Ȳ�</a>
                </td>
            </tr>
        </table>
            </td></tr>
        </table> <!----------------- ���ߡ�End ------------------>
        <input type='hidden' name='noMenu' value='<?php echo $request->get('noMenu')?>'>
    </form>
    <div id='showAjax'>
    </div>
</center>
</body>
<?php echo $menu->out_alert_java()?>
</html>
<?php
ob_end_flush();                 // ���ϥХåե���gzip���� END
?>
