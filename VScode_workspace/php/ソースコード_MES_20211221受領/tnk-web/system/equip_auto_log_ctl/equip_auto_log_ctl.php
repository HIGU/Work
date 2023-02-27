<?php
//////////////////////////////////////////////////////////////////////////////
// ������Ư�����μ�ư�������򥳥�ȥ��뤹�뤿�������˥塼���ɲ�       //
// Copyright (C) 2007       Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp     //
// Changed history                                                          //
// 2007/06/15 Created  equip_auto_log_ctl.php                               //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug ��
ini_set('error_reporting', E_ALL);          // E_STRICT=2048(php5) E_ALL=2047 debug ��
ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
// ini_set('zend.ze1_compatibility_mode', '1');    // zend 1.X ����ѥ� php4�θߴ��⡼��
// ini_set('implicit_flush', '0');             // echo print �� flush �����ʤ�(�٤��ʤ뤿��) CLI��
    // ���ߤ�CLI�Ǥ�default='1', SAPI�Ǥ�default='0'�ˤʤäƤ��롣CLI�ǤΤߥ�����ץȤ����ѹ�����롣
// ini_set('max_execution_time', 1200);        // ����¹Ի���=20ʬ WEB CGI��
ob_start('ob_gzhandler');                   // ���ϥХåե���gzip����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��

require_once ('../../function.php');        // define.php �� pgsql.php �� require_once ���Ƥ���
require_once ('../../tnk_func.php');        // TNK �˰�¸������ʬ�δؿ��� require_once ���Ƥ���
require_once ('../../MenuHeader.php');      // TNK ������ menu class
require_once ('../../ControllerHTTP_Class.php');    // TNK ������ MVC Controller Class
access_log();                               // Script Name �ϼ�ư����

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader(0);                  // ǧ�ڥ����å�0=���̰ʾ� �����=TOP_MENU �����ȥ�̤����

////////////// ����������
$menu->set_site(99, 999);                   // site_index=99(�����ƥ��˥塼) site_id=999(����̵��)
////////////// �꥿���󥢥ɥ쥹����(���л��ꤹ����)
// $menu->set_RetUrl(SYS_MENU);                // �̾�ϻ��ꤹ��ɬ�פϤʤ�
//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title('������Ư�����μ�ư�������δ���');
//////////// ɽ�������
$menu->set_caption('���ߤμ�ư����������');
//////////// �ƽ����action̾�ȥ��ɥ쥹����
// $menu->set_action('test_template',   SYS . 'log_view/php_error_log.php');

//////////// �֥饦�����Υ���å����к���
$uniq = $menu->set_useNotCache('target');

//////////// �ꥯ�����ȤΥ��󥹥��󥹺���
$request = new Request();

//////////// ����ȥ���ե�����λ���
$check_file    = '/home/www/html/tnk-web/equip/check_file';
$auto_log_stop = '/home/www/html/tnk-web/equip/equip_auto_log_stop';

//////////// ��ư�����ϡ���ߤΥꥯ�����ȼ���
if ($request->get('logStart') == 'yes') {
    unlink($auto_log_stop);
}
if ($request->get('logStop') == 'yes') {
    fopen($auto_log_stop, 'a');
}

//////////// ���ߤξ�������
if (file_exists($check_file)) {
    $status = "<span style='color:blue;'>���ߥ�������¹���Ǥ���</span>";
} elseif (file_exists($auto_log_stop)) {
    $status = "���ߥ������������Ǥ���";
} else {
    $status = "<span style='color:red;'>���ߥ��������Ե���Ǥ���</span>";
}
//////////// ��˥塼�Ѥ���߻ؼ��Υե饰�����
if (file_exists($auto_log_stop)) {
    $logStart = '';
    $logStop  = ' disabled';
} else {
    $logStart = ' disabled';
    $logStop  = '';
}

/////////// HTML Header ����Ϥ��ƥ���å��������
$menu->out_html_header();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<!-- <meta http-equiv="Refresh" content="5;URL=<?php echo $menu->out_self() . "?{$uniq}" ?>"> -->
<title><?php echo $menu->out_title() ?></title>
<?php echo $menu->out_site_java() ?>
<?php echo $menu->out_css() ?>

<!-- JavaScript�Υե���������body�κǸ�ˤ��롣 HTML�����Υ����Ȥ�����Ҥ˽���ʤ��������  
<script type='text/javascript' src='template.js?<?php echo $uniq ?>'></script>
-->

<!-- �������륷���ȤΥե��������򥳥��� HTML�����Υ����Ȥ�����Ҥ˽���ʤ��������  
<link rel='stylesheet' href='template.css?<?php echo $uniq ?>' type='text/css' media='screen'>
-->

<link rel='shortcut icon' href='/favicon.ico?=<?php echo $uniq ?>'>

<style type='text/css'>
<!--
body {
    /* ������body����ꤹ��� HTML������ɬ�פʤ� */
    background-image:       url(/img/t_nitto_logo4.png);
    background-repeat:      no-repeat;
    background-attachment:  fixed;
    background-position:    right bottom;
    overflow-y:             hidden;'
}
-->
</style>
</head>

<body onLoad='setInterval("location.replace(\"<?php echo $menu->out_self() ?>\")", 5000)'>
    <center>
<?php echo $menu->out_title_border() ?>
        <!--
            <div style='position: absolute; top: 80; left: 7; width: 185; height: 31'>
                �����ͤǰ��ֻ���
            </div>
        -->
        
        <table width='100%' cellspacing='0' cellpadding='0' border='0'>
            <tr>
                <td align='center' class='caption_font' id='caption'>
                    <?php echo $menu->out_caption() . "\n" ?>
                </td>
            </tr>
        </table>
        <br>
        <br>
        <div><?php echo $status ?></div>
        <br>
        <br>
        
        <!--------------- ����������ʸ��ɽ��ɽ������ -------------------->
        <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
        <table width='100%'class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <tr>
                <td class='winbox'>
                    <input type='button' name='logStart' value='��ư������' onClick='location.replace("<?php echo $menu->out_self()?>?logStart=yes&<?php echo $uniq ?>");'<?php echo $logStart ?>>
                </td>
                <td class='winbox'>
                    <input type='button' name='logStop'  value='��ư�����' onClick='location.replace("<?php echo $menu->out_self()?>?logStop=yes&<?php echo $uniq ?>");'<?php echo $logStop ?>>
                </td>
            </tr>
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
