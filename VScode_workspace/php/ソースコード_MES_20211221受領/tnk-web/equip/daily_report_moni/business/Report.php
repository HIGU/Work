<?php
ini_set('error_reporting', E_ALL);

require_once ('../com/define.php'); 
require_once ('../com/function.php'); 
ob_start('ob_gzhandler');
SetHttpHeader();
?>
<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.01 Transitional//EN'>
<html>
<head>
<?php require_once ('../com/PageHeader.php'); ?>
<title>������ž����</title>
<script language='JavaScript'>
function init() {
    document.MainForm.submit();
}
</script>
</head>
<body onLoad='init()'>
<form name='MainForm' action='ReportMain.php' method='post'>
    <input type='hidden' name='RetUrl' value='<?=@$_REQUEST['RetUrl']?>'>
</form>
<table border='0' class='LAYOUT' width='100%' height='100%'>
    <tr class='LAYOUT'>
        <td class='LAYOUT' align='center'>
            ������ž�������桦����
        </td>
    </tr>
</table>
</body>
</html>
