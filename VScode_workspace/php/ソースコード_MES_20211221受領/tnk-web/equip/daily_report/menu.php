<?php
require_once ('com/define.php');
session_start();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<?php require_once ('com/PageHeader.php'); ?>
<title>�������칩�������� ���౿ž������������ƥ�</title>
</head>
<body>
[<?=$_SESSION['User_ID']?>]
<center>
    <table border="0">
        <tr>
            <td align="center">
                �ԣϣ� ��˥塼
            </td>
        </tr>
        <tr>
            <td>
                <a href="<?=MASTER_PATH?>Account.php?RetUrl=../menu.php">���¥ޥ���</a><br>
                <a href="<?=MASTER_PATH?>Materials.php?RetUrl=../menu.php">�����ޥ���</a><br>
                <a href="<?=MASTER_PATH?>Parts.php?RetUrl=../menu.php">���ʥޥ���</a><br>
                <br>
                <a href="<?=BUSINESS_PATH?>Report.php?RetUrl=../menu.php">������ž����</a><br>
            </td>
        </tr>
    </table>
</center>
</body>
</html>
