<?php
//////////////////////////////////////////////////////////////////////////////
// �Ұ���˥塼�μ��ʾ��� ���� ������ Branch (ʬ��)����                     //
// Copyright(C) 2010 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp            //
// Changed history                                                          //
// 2007/10/15 Created   print_emp_branch_user.php                           //
// 2010/06/16 ����Ū����޼�����970268�ˤ������Ǥ���褦���ѹ�         ��ë //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting',E_ALL);       // E_ALL='2047' debug ��
// ini_set('display_errors','1');          // Error ɽ�� ON debug �� ��꡼���女����
ob_start('ob_gzhandler');               // ���ϥХåե���gzip����
session_start();                        // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��
require_once ('../../function.php');
require_once ('../../tnk_func.php');
access_log();                           // Script Name �ϼ�ư����

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<title>�Ұ���˥塼���ʷ������ ʬ������</title>
<style type='text/css'>
<!--
body {
    margin:     20%;
    font-size:  24pt;
}
-->
</style>
</head>
<body>
    <center>
        �������᡼��(PDF)������Ǥ���<br>
        ���Ԥ���������<br>
        <img src='../../img/tnk-turbine.gif' width=68 height=72>
    </center>

    <script type='text/javascript' language='JavaScript'>
    <!--
        location = '<?php echo H_WEB_HOST ?>/emp/print/print_emp_history_user.php?targetUser=<?php echo $_REQUEST['targetUser']?>';
    // -->
    </script>
</body>
</html>
<?php
ob_end_flush();                 // ���ϥХåե���gzip���� END
?>
