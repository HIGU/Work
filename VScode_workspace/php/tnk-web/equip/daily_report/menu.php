<?php
require_once ('com/define.php');
session_start();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<?php require_once ('com/PageHeader.php'); ?>
<title>栃木日東工器株式会社 機材運転日報管理システム</title>
</head>
<body>
[<?=$_SESSION['User_ID']?>]
<center>
    <table border="0">
        <tr>
            <td align="center">
                ＴＯＰ メニュー
            </td>
        </tr>
        <tr>
            <td>
                <a href="<?=MASTER_PATH?>Account.php?RetUrl=../menu.php">権限マスタ</a><br>
                <a href="<?=MASTER_PATH?>Materials.php?RetUrl=../menu.php">材料マスタ</a><br>
                <a href="<?=MASTER_PATH?>Parts.php?RetUrl=../menu.php">部品マスタ</a><br>
                <br>
                <a href="<?=BUSINESS_PATH?>Report.php?RetUrl=../menu.php">機械運転日報</a><br>
            </td>
        </tr>
    </table>
</center>
</body>
</html>
