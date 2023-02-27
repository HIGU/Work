<?php 
//////////////////////////////////////////////////////////////////////////////
// ������Ư���������ƥ�θ��¥ޥ������ݼ�               Client interface �� //
// Copyright (C) 2004-2006 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2004/07/01 Created   Account.php                                         //
// 2006/04/12 MenuHeader ���饹�б�                                         //
// 2006/04/14 ����ܥ����<td>���� </td> ��ȴ���Ƥ����Τ���               //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug ��
ini_set('error_reporting', E_ALL);          // E_STRICT=2048(php5) E_ALL=2047 debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
ob_start('ob_gzhandler');                   // ���ϥХåե���gzip����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��

require_once ('../../../MenuHeader.php');   // TNK ������ menu class
require_once ('../../../function.php');     // access_log()���ǻ���
require_once ('../com/define.php');
require_once ('../com/function.php');
require_once ('../com/PageControl.php');
access_log();                               // Script Name �ϼ�ư����

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader(0);                  // ǧ�ڥ����å�0=���̰ʾ� �����=TOP_MENU �����ȥ�̤����
//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title('���¥ޥ��������ݼ�');
////////////// target����
// $menu->set_target('_parent');               // �ե졼���Ǥ�������target°����_parent��ɬ��

// ���̥إå��ν���
SetHttpHeader();

// ��å������Υ��ꥢ
$Message = '';

// �����ԥ⡼��
$AdminUser = AdminUser( FNC_ACCOUNT );

if (@$_SESSION['User_ID'] == '$$$') $AdminUser = true;


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
<?php require_once ('../com/PageHeaderOnly.php'); ?>
<title><?php echo $menu->out_title() ?></title>
<?php echo $menu->out_css() ?>
<LINK rel='stylesheet' href='<?=CONTEXT_PATH?>com/cssConversion.css' type='text/css'>
<script language='JavaScript'>
function init() {
<?php if ($Message != '') { ?>
    alert('<?=$Message?>');
<?php } ?>
}
function add() {
    
    if (document.MainForm._Staff.value == '') {
        alert('�Ұ������ɤ����Ϥ��Ʋ�����');
        return;
    }
    
    for (i = 0; i < document.MainForm._Function.length; i++) {
        if (document.MainForm._Function[i].checked) {
            break;
        }
    }
    document.MainForm.ProcCode.value = 'ADD';
    document.MainForm.Function.value = document.MainForm._Function[i].value;
    document.MainForm.Staff.value = document.MainForm._Staff.value;
    document.MainForm.submit();
}
function del(fnc,staff) {
    document.MainForm.ProcCode.value = 'DEL';
    document.MainForm.Function.value = fnc;
    document.MainForm.Staff.value = staff;
    document.MainForm.submit();
}
function doBack() {
    document.MainForm.action = '<?=@$_REQUEST['RetUrl']?>';
    document.MainForm.submit();
}
</script>
</head>
<body onLoad='init();'>
<center>
<?php echo $menu->out_title_border() ?>

    <form name='MainForm' action='<?php echo $menu->out_self()?>' method='post'>
    <input type='hidden' name='RetUrl' value='<?=@$_REQUEST['RetUrl']?>'>
    <input type='hidden' name='ProcCode' value=''>
    <input type='hidden' name='Function' value=''>
    <input type='hidden' name='Staff' value=''>
        <?php if ($AdminUser) { ?>
            <table border='1' class='Conversion'>
                <tr>
                    <td class='HED Conversion'>
                        ����
                    </td>
                    <td class='Conversion'>
                        <table border='0' class='LAYOUT'>
                            <tr class='LAYOUT'>
                                <td class='LAYOUT'>
                                    <input type='radio' name='_Function' value='<?=FNC_MASTER?>' checked id='id1'><label for='id1'>�ޥ�����(<?=FNC_MASTER?>)</label>
                                </td>
                                <td class='LAYOUT'>
                                    <input type='radio' name='_Function' value='<?=FNC_REPORT?>' id='id2'><label for='id2'>��ž������ƥʥ�(<?=FNC_REPORT?>)</label><br>
                                </td>
                            </tr>
                            <tr class='LAYOUT'>
                                <td class='LAYOUT'>
                                    <input type='radio' name='_Function' value='<?=FNC_REPORT_ACCEPT?>' id='id3'><label for='id3'>��ž����ǧ(<?=FNC_REPORT_ACCEPT?>)</label>
                                </td>
                                <td class='LAYOUT'>
                                    <input type='radio' name='_Function' value='<?=FNC_ACCOUNT?>' id='id4'><label for='id4'>��������(<?=FNC_ACCOUNT?>)</label>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr class='Conversion'>
                    <td class='HED Conversion'>
                        �Ұ�������
                    </td>
                    <td class='Conversion'>
                        <input type='text' name='_Staff' value=''>
                        <input type='button' value='�ɲ�' onClick='add()'>
                        <input type='button' value='�ᡡ��' style='width:80;' onClick='doBack()'>

                    </td>
                </tr>
            </table>
            <br>
        <?php } ?>
        <?php if (pg_num_rows ($rs) == 0) { ?>
        <table border='0' class='LAYOUT' width='100%' height='100%'>
            <tr class='LAYOUT'>
                <td class='LAYOUT' align='center'>
                    �����ǡ�����¸�ߤ��ޤ���
                </td>
            </tr>
        </table>
        <?php } else { ?>
            <table border='1' class='Conversion'>
                <tr class='Conversion'>
                    <td class='HED Conversion' width=' 50'></td>
                    <td class='HED Conversion' width='150'>��ǽ������</td>
                    <td class='HED Conversion' width=' 80'>�Ұ�������</td>
                    <td class='HED Conversion' width='100'>�ᡡ��̾</td>
                </tr>
            <?php while ($row = pg_fetch_array ($rs)) { ?>
                <tr class='Conversion'>
                    <td class='Conversion' align='center'><?php if ($AdminUser) { ?><input type='button' value='���' onClick='del("<?=outHtml($row['function'])?>","<?=outHtml($row['staff'])?>")'><?php } ?></td>
                    <td class='Conversion' align='left'  ><?=outHtml($row['function'])?></td>
                    <td class='Conversion' align='center'><?=outHtml($row['staff'])?></td>
                    <td class='Conversion' align='left'  ><?=outHtml($row['name'])?></td>
                </tr>
            <?php } ?>
            </table>
        <?php } ?>
    </form>
</center>
</body>
</html>
<?php ob_end_flush(); ?>
