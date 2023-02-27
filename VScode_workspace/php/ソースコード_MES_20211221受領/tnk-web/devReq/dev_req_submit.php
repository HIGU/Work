<?php
//////////////////////////////////////////////////////////////////////////////
// �ץ���೫ȯ����� �����ե�����                                        //
// Copyright(C) 2002-2004 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp       //
// �ѹ�����                                                                 //
// 2002/02/12 �������� dev_req_submit.php                                   //
// 2002/08/09 register_globals = Off �б�                                   //
// 2003/12/12 define���줿����ǥǥ��쥯�ȥ�ȥ�˥塼̾����Ѥ���          //
// 2004/02/24 ��ǧ�Ѥ˼Ұ��ֹ�Τ����Ϥ������Ǥ�¨�Ұ�̾���Ф�褦���ѹ�    //
// 2004/07/17 MenuHeader()���饹�򿷵��������ǥ�����ǧ�����Υ��å�����  //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);      // E_ALL='2047' debug ��
// ini_set('display_errors', '1');         // Error ɽ�� ON debug �� ��꡼���女����
session_start();                        // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��
require_once ('../function.php');       // TNK ������ function
require_once ('../MenuHeader.php');     // TNK ������ menu class
require_once ('../tnk_func.php');
access_log();                           // Script Name �ϼ�ư����

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader();               // ǧ�ڥ����å���ԤäƤ���

////////////// ����������
$menu->set_site(4, 2);                  // site_index=4(�ץ���೫ȯ) site_id=2(��������������)
////////////// �����ȥ�����ץȤΥ��ɥ쥹����
// $menu->set_self($_SERVER['PHP_SELF']);
////////////// �꥿���󥢥ɥ쥹����
// $menu->set_RetUrl(DEV_MENU);         // �꥿���󥢥ɥ쥹��ư�������ѹ�
//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title('�ץ���೫ȯ����� ����������');
//////////// ɽ�������
$menu->set_caption('��ȯ�������� ��˥塼');

if (isset($_POST['iraisya'])) {
    /////// �Ұ���̾������� SQL
    $_SESSION['s_dev_iraisya']    = $_POST['iraisya'];
    $iraisya    = $_POST['iraisya'];
    $query_user = "select name from user_detailes where uid='{$iraisya}'";
    $res_user = array();
    if($rows_user=getResult($query_user,$res_user)) {
        $user_name = rtrim($res_user[0]['name']);
    } else {
        $user_name = '̤��Ͽ';
    }
} else {
    $user_name = '';
    $iraisya   = '';
}

if (isset($_POST['dev_chk_submit'])) {
    $dev_chk_submit = $_POST['dev_chk_submit'];
} else {
    $dev_chk_submit = '';
}

if ($dev_chk_submit == '��ǧ') {
    if ($_POST['mokuteki'] == '') {
        $dev_chk_submit = '';
        $_SESSION['s_sysmsg'] = '��Ū��̤���ϤǤ���';
    }
    if ($_POST['naiyou'] == '') {
        $dev_chk_submit = '';
        $_SESSION['s_sysmsg'] = '���Ƥ�̤���ϤǤ���';
    }
    // session_register('s_dev_iraibusho','s_dev_iraisya','s_dev_mokuteki','s_dev_naiyou');
    // session_register('s_dev_yosoukouka','s_dev_bikou');
    $_SESSION['s_dev_iraibusho']  = $_POST['iraibusho'];
    $_SESSION['s_dev_mokuteki']   = $_POST['mokuteki'];
    $_SESSION['s_dev_naiyou']     = $_POST['naiyou'];
    $_SESSION['s_dev_yosoukouka'] = $_POST['yosoukouka'];
    $_SESSION['s_dev_bikou']      = $_POST['bikou'];
    $iraibusho  = $_POST['iraibusho'];
    $mokuteki   = $_POST['mokuteki'];
    $naiyou     = $_POST['naiyou'];
    $yosoukouka = $_POST['yosoukouka'];
    $bikou      = $_POST['bikou'];
} elseif ($dev_chk_submit == '����') {
    $iraibusho  = $_SESSION['s_dev_iraibusho'];
    $iraisya    = $_SESSION['s_dev_iraisya'];
    $mokuteki   = $_SESSION['s_dev_mokuteki'];
    $naiyou     = $_SESSION['s_dev_naiyou'];
    $yosoukouka = $_SESSION['s_dev_yosoukouka'];
    $bikou      = $_SESSION['s_dev_bikou'];
    // $user_name  = '';
} else {
    // $user_name  = '';
    $iraibusho = '';
    $mokuteki = '';
    $naiyou   = '';
    $yosoukouka = '';
    $bikou    = '';
}

/////////// HTML Header ����Ϥ��ƥ���å��������
$menu->out_html_header();

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<title><?= $menu->out_title() ?></title>
<style type="text/css">
<!--
select {
    background-color:teal;
    color:white;
}
textarea {
    background-color:black;
    color:white;
}
input.sousin {
    background-color:red;
}
input.text {
    background-color:black;
    color:white;
}
.pt {
    font-size:11pt;
}
-->
</style>
<?= $menu->out_site_java() ?>
<?= $menu->out_css() ?>
<script language="JavaScript">
<!--
/* ������ϥե�����Υ�����Ȥ˥ե������������� */
function set_focus(){
    <?php
    if ($user_name == '') {
        echo "document.input_form.iraisya.focus();\n";
        echo "document.input_form.iraisya.select();\n";
    } elseif ($mokuteki == '') {
        echo "document.input_form.mokuteki.focus();\n";
        echo "document.input_form.mokuteki.select();\n";
    } elseif ($naiyou == '') {
        echo "document.input_form.naiyou.focus();\n";
        echo "document.input_form.naiyou.select();\n";
    }
    ?>
}
// -->
</script>
<script language="JavaScript" src="./dev_req.js?id=2">
</script>
</head>
<body onload='set_focus()'>
    <center>
<?= $menu->out_title_border() ?>
        <hr color='navy'>
        <table width=100% border='0'>
            <tr>
            <?php if($dev_chk_submit == '��ǧ') { ?>
                <form action='dev_req_insert.php' method='post'>
                <td align='center'><input class='sousin' type='submit' name='dev_chk_submit' value='����' ></td>
                </form>
                <form action='<?= $menu->out_self() ?>' method='post'>
                <td align='center'><input type='submit' name='dev_chk_submit' value='����' ></td>
            <?php } else { ?>
                <form name='input_form' action='<?= $menu->out_self() ?>' method='post' onSubmit='return chk_dev_req_submit(this)'>
                <td align='center'><input type='submit' name='dev_chk_submit' value='��ǧ' ></td>
            <?php } ?>
            </tr>
        </table>
        <table width='100%' cellspacing='0' cellpadding='2' border='1' bgcolor='#e6e6fa'>
            <tr>
                <td align='center' width='20'>��</td>
                <td align='left'>����No</td>
                <td align='left'>
                    ����No(����No)���������˼�ư�Ǽ���ޤ���
                </td>
            </tr>
            <tr>
                <td align='center' width='20'>��</td>
                <td align='left'>������</td>
                <td align='left'>
                    <?php $iraibi=date("Y-m-d");echo $iraibi; ?>
                </td>
            </tr>
            <tr>
                <td align='center' width='20'>��</td>
                <td align='left'>��������</td>
                <td align="left">
                
                <?php
                if($dev_chk_submit != "��ǧ"){
                    print("<select name='iraibusho'>\n");
                    $query_section="select * from section_master where sflg=1 order by sid asc";
                    $res_section=array();
                    if($rows_section=getResult($query_section,$res_section)){
                        for($i=0;$i<$rows_section;$i++){
                            echo("<option ");
                            if($iraibusho==$res_section[$i][0])    // �ʤ��� sid ���Ȥ��������� 0 �ˤ�����
                                echo("selected ");
                            echo("value='" . $res_section[$i][0] . "'>" . rtrim($res_section[$i]['section_name']) . "</option>\n");
                        }
                    }
                    print("</select>\n");
                }else{
                    $query_section="select * from section_master where sid = $iraibusho ";
                    $res_section=array();
                    if($rows_section=getResult($query_section,$res_section))
                        print(rtrim($res_section[0]['section_name']));
                    else
                        print($iraibusho);
                }
                ?>
                </td>
            </tr>
            <tr>
                <td align='center' width='20'>��</td>
                <td align='left'>�����</td>
                <td align="left">
                    ����ԤμҰ�No
                    <?php
                    if ($dev_chk_submit != '��ǧ') {
                        echo "<input class='text' type='text' name='iraisya' size='7' maxlength='6' value='", ltrim($iraisya), "'>\n";
                    } else {
                        echo "$iraisya\n";
                    }
                    if ($user_name != '') {
                        echo "<font size='3'>{$user_name}</font></td>\n";
                    } else {
                        echo "--------\n";
                    }
                    ?>
                </td>
            </tr>
            <tr>
                <td align='center' width='20'>��</td>
                <td align='left'>��Ū���ϥ����ȥ�</td>
                <td align='left'>
                    <?php
                    if($dev_chk_submit != "��ǧ"){
                        echo("<textarea class='pt' name='mokuteki' cols='50' rows='2' wrap='soft'>" . $mokuteki . "</textarea>\n");
                        echo("<font size='1'>��ư�ǲ��Ԥ��ޤ��Τǲ��ԥ����ϲ����ʤ��ǲ�������</font>\n");
                    }else{
                        print("$mokuteki\n");
                    }
                    ?>
                </td>
            </tr>
            <tr>
                <td align='center' width='20'>��</td>
                <td align='left'>�⡡����</td>
                <td align='left'>
                    <?php
                    if($dev_chk_submit != "��ǧ")
                        echo("<textarea class='pt' name='naiyou' cols='80' rows='6' wrap='soft'>" . $naiyou . "</textarea>\n");
                    else
                        print("$naiyou\n");
                    ?>
                </td>
            </tr>
            <tr>
                <td align='center' width='20'>��</td>
                <td align='left' nowrap>ͽ�۸���</td>
                <td align='left'>
                    <?php
                    if($dev_chk_submit != "��ǧ"){
                        print("<input class='text' type='text' name='yosoukouka' size='11' maxlength='9' value='" . ltrim($yosoukouka) . "'>\n");
                        print("����(ʬ)��ǯ���ʾ�ά��ǽ��\n");
                    }else
                        if($yosoukouka=="")
                            print("-----\n");
                        else
                            print("$yosoukouka ʬ��ǯ\n");
                    ?>
                </td>
            </tr>
            <tr>
                <td align='center' width='20'>��</td>
                <td align='left'>�׻�����������</td>
                <td align='left'>
                    <?php
                    if($dev_chk_submit != "��ǧ"){
                        echo("<textarea class='pt' name='bikou' cols='50' rows='2' wrap='soft'>" . $bikou . "</textarea>\n");
                        echo("<font size='1'>ͽ�۸��̹���(ʬ)�η׻�����������������(��ά��)</font>\n");
                    }else
                        if($bikou=="")
                            print("-----\n");
                        else
                            print("$bikou\n");
                    ?>
                </td>
            </tr>
            </form>
        </table>
        <table width='100%' border='0'>
            <form action='<?= $menu->out_RetUrl() ?>' method='post'>
                <tr><td align='center'><input type='submit' name='dev_chk_submit' value='���' ></td></tr>
            </form>
        </table>
    </center>
</body>
</html>
