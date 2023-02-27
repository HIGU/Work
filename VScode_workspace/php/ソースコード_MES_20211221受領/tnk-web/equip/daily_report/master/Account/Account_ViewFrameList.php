<?php
//////////////////////////////////////////////////////////////////////////////
// ������Ư���������ƥ�θ��¥ޥ������ݼ�                       MVC View��  //
// Copyright (C) 2006-2006 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/04/14 Created   Account_ViewFrameList.php                           //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug ��
ini_set('error_reporting', E_ALL);          // E_STRICT=2048(php5) E_ALL=2047 debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
ob_start('ob_gzhandler');                   // ���ϥХåե���gzip����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��

require_once ('../../com/define.php');
require_once ('../../com/function.php');
require_once ('../../com/PageControl.php');

// �����ԥ⡼��
$AdminUser = AdminUser( FNC_ACCOUNT );

// ���������ɤ����
$ProcCode = @$_REQUEST['ProcCode'];

// ���ͥ������μ���
$con = getConnection();

if ($ProcCode == 'ADD') {
    // �ɲå⡼��
    AddUser();
} else if ($ProcCode == 'DEL') {
    // ����⡼��
    DelUser();
}

// ��Ͽ�԰����μ���     2004/07/10 ��̾��(name)���ɲ� TNK kobayashi.
$rs = pg_query($con, "select function, staff, trim(name) as name
                        from
                            equip_account
                        left outer join
                            user_detailes
                        on (staff = uid)
                        order by staff, function");

// --------------------------------------------------
// �����Ԥ��ɲ�
// --------------------------------------------------
function AddUser()
{
    global $con,$Message;
    
    // �ѥ�᡼���μ���
    $fnc   = $_REQUEST['Function'];
    $staff = $_REQUEST['Staff'];
    $user  = $_SESSION['User_ID'];
    
    // ��ʣ��Ͽ�Υ����å�
    $rs = pg_query($con,"select * from equip_account where function='$fnc' and staff='$staff'");
    if ($row = pg_fetch_array ($rs)) {
        $Message .= "�Ұ��ֹ�[$staff]�Ϥ��Ǥ���Ͽ����Ƥ��ޤ���";
        return;
    }
    
    pg_query ($con , 'BEGIN');
    
    // �桼������Ͽ
    if (!pg_query($con,"insert into equip_account (function,staff,last_user) values('$fnc','$staff','$user')")) {
        pg_query ($con , 'ROLLBACK');
        $SYSTEM_MESSAGE = "�ǡ����١����ι����˼��Ԥ��ޤ���\n$sql";
        require_once ('../com/' . ERROR_PAGE);
        exit();
    }
    
    pg_query ($con , 'COMMIT');
}
// --------------------------------------------------
// �����Ԥκ��
// --------------------------------------------------
function DelUser()
{
    global $con;
    
    // �ѥ�᡼���μ���
    $fnc   = @$_REQUEST['Function'];
    $staff = @$_REQUEST['Staff'];

    pg_query ($con , 'BEGIN');
    
    // �桼���κ��
    if (!pg_query($con,"delete from equip_account where function='$fnc' and staff='$staff'")) {
        pg_query ($con , 'ROLLBACK');
        $SYSTEM_MESSAGE = "�ǡ����١����ι����˼��Ԥ��ޤ���\n$sql";
        require_once ('../com/' . ERROR_PAGE);
        exit();
    }
    
    pg_query ($con , 'COMMIT');
}

?>
<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.01 Transitional//EN'>
<html>
<head>
<meta http-equiv='Content-Type' content='text/html; charset=EUC-JP'>
<meta http-equiv='Content-Style-Type' content='text/css'>
<meta http-equiv='Content-Script-Type' content='text/javascript'>
<title>���¥ޥ������Υꥹ��</title>
<script type='text/javascript' src='/base_class.js'></script>
<link rel='stylesheet' href='/menu_form.css' type='text/css' media='screen'>
<LINK rel='stylesheet' href='<?php echo CONTEXT_PATH?>com/cssConversion.css' type='text/css'>
<style type='text/css'>
body {
    background-image:none;
}
</style>
<script type='text/javascript'>
function del(fnc,staff) {
    document.MainForm.ProcCode.value = 'DEL';
    document.MainForm.Function.value = fnc;
    document.MainForm.Staff.value = staff;
    document.MainForm.submit();
}
</script>
<script type='text/javascript' src='Account.js'></script>
</head>
<body>
<center>
    <?php if (pg_num_rows ($rs) == 0) { ?>
    <table border='0' class='LAYOUT' width='99%' height='100%'>
        <tr class='LAYOUT'>
            <td class='LAYOUT' align='center'>
                �����ǡ�����¸�ߤ��ޤ���
            </td>
        </tr>
    </table>
    <?php } else { ?>
    <table border='1' class='Conversion' width='99%'>
        <form name='MainForm' action='Account_ViewFrameList.php' method='post'>
            <input type='hidden' name='ProcCode' value=''>
            <input type='hidden' name='Function' value=''>
            <input type='hidden' name='Staff' value=''>
        <?php $no = 1; ?>
        <?php while ($row = pg_fetch_array ($rs)) { ?>
            <tr class='Conversion'>
                <td class='Conversion' width=' 8%' align='left'  ><?php echo $no ?></td>
                <td class='Conversion' width='12%' align='center'><?php if ($AdminUser) { ?><input type='button' value='���' onClick='del("<?=outHtml($row['function'])?>","<?=outHtml($row['staff'])?>")'><?php } ?></td>
                <td class='Conversion' width='35%' align='left'  ><?=outHtml($row['function'])?></td>
                <td class='Conversion' width='20%' align='center'><?=outHtml($row['staff'])?></td>
                <td class='Conversion' width='25%' align='left'  ><?=outHtml($row['name'])?></td>
            </tr>
            <?php $no++; ?>
        <?php } ?>
        </form>
    </table>
    <?php } ?>
</center>
</body>
</html>
<?php ob_end_flush(); ?>
