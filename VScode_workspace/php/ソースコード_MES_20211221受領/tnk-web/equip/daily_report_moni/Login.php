<?php
require_once ('com/define.php');

if (@$_REQUEST['LoginUser'] != '') {
    session_start();
    $_SESSION['User_ID'] = $_REQUEST['LoginUser'];
    header("Location: EquipMenu.php");
    exit();
}



?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<?php require_once ('com/PageHeader.php'); ?>
<title>�������칩�������� ������ž������������ƥ�</title>
<script>
function Login(id){
    MainForm.LoginUser.value = id;
    MainForm.submit();
}
</script>
</head>
<body>
<div class="TITLE">������</div>
<form name="MainForm" action="Login.php" method="post">
<center>
    <table border="0">
        <tr>
            <td>
                ID : <input type="text" name="LoginUser" value="00000000">
                <input type="submit" value="������">
                <br>
                <br>
                <br>
                <a href="JavaScript:Login('00000001')">00000001:�ʤ�Ǥ�Ǥ���桼��</a><br>
                <br>
                <a href="JavaScript:Login('00000002')">00000002:�ޥ����Ȥ���桼��</a><br>
                <br>
                <a href="JavaScript:Login('00000003')">00000003:���󥪥ڥ졼��</a><br>
            </td>
        </tr>
    </table>
</center>
</form>
</body>
</html>
