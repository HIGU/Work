<?php
//////////////////////////////////////////////////////////////////////////////
// ��˥����ڸ�ľ�� ������������ư��Ͽ��ǧ�� �Ȳ��˥塼                 //
// Copyright (C) 2008 Norihisa.Ohya usoumu@nitto-kohki.co.jp                //
// Changed history                                                          //
// 2008/02/12 Created  materialCheckLinear_Main.php                         //
//                     (materialCheck_Main.php���¤                        //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL | E_STRICT)
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
ob_start('ob_gzhandler');                   // ���ϥХåե���gzip����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��

require_once ('../../function.php');                // define.php �� pgsql.php �� require_once ���Ƥ���
require_once ('../../tnk_func.php');                // TNK �˰�¸������ʬ�δؿ��� require_once ���Ƥ���
require_once ('../../MenuHeader.php');              // TNK ������ menu class
access_log();                               // Script Name �ϼ�ư����

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader(-1);                 // ǧ�ڥ����å�0=���̰ʾ� �����=TOP_MENU �����ȥ�̤����

////////////// ����������
$menu->set_site(INDEX_INDUST, 999);         // site_index=30(������˥塼) site_id=999(̤��)
////////////// �꥿���󥢥ɥ쥹����
// $menu->set_RetUrl(INDUST_MENU);             // �̾�ϻ��ꤹ��ɬ�פϤʤ�
//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title('��˥��������θ�ľ����ǧ��');
//////////// ɽ�������
$menu->set_caption('��˥��������θ�ľ����ǧ�ѡ�(��˥���2008ǯ1���������ʤ��о�)<br>(��ư��Ͽ����Ƥ���С�)');
//////////// �ƽ����action̾�ȥ��ɥ쥹����
// $menu->set_action('��������ɽ��ɽ��',   INDUST . 'material/allo_conf_parts_view.php');
// $menu->set_action('����������Ͽ',     INDUST . 'material/materialCost_entry.php');
//////////// �꥿���󥢥ɥ쥹�ؤ�GET�ǡ��������å�
$menu->set_retGET('page_keep', 'on');

//////////// JavaScript Stylesheet File ��ɬ���ɤ߹��ޤ���
$uniq = $menu->set_useNotCache('mtcheck');

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
<link rel='stylesheet' href='materialCheck.css?id=<?php echo $uniq ?>' type='text/css' media='screen'>
<style type='text/css'><!-- --></style>
<!-- <script type='text/javascript' src='materialCheck.js?<?php echo $uniq ?>'></script> -->
</head>
<!-- background-color:#d6d3ce; -->
<body style='overflow-y:hidden;'>
<center>
<?=$menu->out_title_border()?>
        
        <!----------------- ������ ���� ���� �Υե����� ---------------->
        <table width='100%' border='0' cellspacing='0' cellpadding='0'>
            <tr>
                <td align='center' class='caption_font'>
                    <?= $menu->out_caption(), "\n" ?>
                </td>
            </tr>
        </table>
        
<?php
echo "<iframe hspace='0' vspace='0' frameborder='0' scrolling='yes' src='materialCheckLinear_ViewHeader.html?{$uniq}' name='header' align='center' width='98%' height='46' title='����'>\n";
echo "    ���ܤ�ɽ�����Ƥ��ޤ���\n";
echo "</iframe>\n";
echo "<iframe hspace='0' vspace='0' frameborder='0' scrolling='yes' src='materialCheckLinear_ViewBody.php?", $_SERVER['QUERY_STRING'], "&{$uniq}#mark' name='list' align='center' width='98%' height='80%' title='����'>\n";
echo "    ������ɽ�����Ƥ��ޤ���\n";
echo "</iframe>\n";
?>
</center>
</body>
<?php echo $menu->out_alert_java()?>
</html>
