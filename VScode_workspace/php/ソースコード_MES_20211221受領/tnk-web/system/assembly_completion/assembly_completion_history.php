<?php
//////////////////////////////////////////////////////////////////////////////
// AS/400����Ω���������DB�����С��ع���  �����Υ����ߥ󥰤ϣ���ñ��       //
//   Ʊ���ʤ鲿��Ǥ�����褦�˻��Ȥߤ��äƤ���                         //
// Copyright (C) 2006-2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/03/09 Created  assembly_completion_history.php                      //
// 2007/05/15 �ǥ��쥯�ȥ�� daily/ �� assembly_completion/ ���ѹ�          //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
ini_set('implicit_flush', '0');             // echo print �� flush �����ʤ�(�٤��ʤ뤿��)
ini_set('max_execution_time', 120);          // ����¹Ի��� = 120��(��ʬ) 
$currentFullPathName = realpath(dirname(__FILE__));
require_once ('../../function.php');        // define.php �� pgsql.php �� require_once ���Ƥ���
require_once ('../../MenuHeader.php');      // TNK ������ menu class
access_log();                               // Script Name �ϼ�ư����

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader(3);                  // ǧ�ڥ����å�3=administrator�ʾ� �����=TOP_MENU �����ȥ�̤����

//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title('��Ω ���� ���� AS/400��DB�����С� ���������¹�');
//////////// �꥿���󥢥ɥ쥹����(���л��ꤹ����)
$menu->set_RetUrl(SYS_MENU);                // �̾�ϻ��ꤹ��ɬ�פϤʤ�

$Message  = "������������������������������������������������������������������������\n\n";

/******** ��Ω��������ι��� *********/
$Message .= `{$currentFullPathName}/assembly_completion_history_cli.php`;
$Message .= "������������������������������������������������������������������������\n";
// $Message .= "------------------------------------------------------------------------\n";

///// alert()�����Ѥ˥�å��������Ѵ�
$Message = str_replace("\n", '\\n', $Message);  // "\n"�����

/////////// HTML Header ����Ϥ��ƥ���å��������
$menu->out_html_header();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<title><?= $menu->out_title() ?></title>
<?= $menu->out_css() ?>
<script type='text/javascript'>
function resultMessage()
{
    alert("<?php echo $Message ?>");
    location.replace("<?php echo SYS_MENU ?>");
}
</script>
<body   onLoad='
            resultMessage();
        '
</body>
<html>
