<?php
//////////////////////////////////////////////////////////////////////////////
// �»�״ط� ����ɸ��»�׷׻��������õ�����(������)���ϵڤ���Ͽ      //
// Copyright(C) 2009 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp          //
// Changed history                                                          //
// 2003/03/03 Created   profit_loss_comment_put_ctoku.php                   //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting',  E_ALL);         // E_ALL='2047' debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
ob_start('ob_gzhandler');                   // ���ϥХåե���gzip����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��

require_once ('../function.php');           // define.php �� pgsql.php �� require_once ���Ƥ���
require_once ('../tnk_func.php');           // TNK �˰�¸������ʬ�δؿ��� require_once ���Ƥ���
require_once ('../MenuHeader.php');         // TNK ������ menu class
access_log();                               // Script Name �ϼ�ư����

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader(0);                  // ǧ�ڥ����å�0=���̰ʾ� �����=TOP_MENU �����ȥ�̤����

////////////// ����������
// $menu->set_site(10, 7);                     // site_index=10(»�ץ�˥塼) site_id=7(�»��)
//////////// ɽ�������
$menu->set_caption('�������칩��(��)');
//////////// �ƽ����action̾�ȥ��ɥ쥹����
// $menu->set_action('��ݲ�̾',   PL . 'address.php');

if (account_group_check() == FALSE) {
    $_SESSION['s_sysmsg'] = '���ʤ��ϵ��Ĥ���Ƥ��ޤ���<br>�����Ԥ�Ϣ���Ʋ�������';
    header('Location: http:' . WEB_HOST . 'menu.php');
    exit();
}

///// ������μ���
$ki = Ym_to_tnk($_SESSION['pl_ym']);
$tuki = substr($_SESSION['pl_ym'],4,2);

//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title("��{$ki}����{$tuki}���١��»�׷׻�����õ����������");

///// �о�����
$yyyymm = $_SESSION['pl_ym'];
///// �о�����
if (substr($yyyymm,4,2)!=01) {
    $p1_ym = $yyyymm - 1;
} else {
    $p1_ym = $yyyymm - 100;
    $p1_ym = $p1_ym + 11;
}

if (!isset($_POST['touroku'])) {     // �ǡ�������
    ////////// ��Ͽ�Ѥߤʤ���õ����� ����
    $query = sprintf("select comment from act_comment_history where pl_bs_ym=%d and item='���ץ�ɸ��»�׷׻���'", $yyyymm);
    if (getUniResult($query,$comment_c) <= 0) {
        $comment_c = "";
    }
    $query = sprintf("select comment from act_comment_history where pl_bs_ym=%d and item='���ץ�����»�׷׻���'", $yyyymm);
    if (getUniResult($query,$comment_ctoku) <= 0) {
        $comment_ctoku = "";
    }
    $query = sprintf("select comment from act_comment_history where pl_bs_ym=%d and item='����ctoku»�׷׻���'", $yyyymm);
    if (getUniResult($query,$comment_all) <= 0) {
        $comment_all = "";
    }
    $query = sprintf("select comment from act_comment_history where pl_bs_ym=%d and item='����¾ctoku»�׷׻���'", $yyyymm);
    if (getUniResult($query,$comment_other) <= 0) {
        $comment_other = "";
    }
} else {
    $query = sprintf("select comment from act_comment_history where pl_bs_ym=%d and item='���ץ�ɸ��»�׷׻���'", $yyyymm);
    if (getUniResult($query,$comment_c) <= 0) {
        $query = sprintf("insert into act_comment_history (pl_bs_ym, item, comment) values (%d, '���ץ�ɸ��»�׷׻���', '%s')", $yyyymm, $_POST['comment_c']);
        query_affected($query);
    } else {
        $query = sprintf("update act_comment_history set comment='%s' where pl_bs_ym=%d and item='���ץ�ɸ��»�׷׻���'", $_POST['comment_c'], $yyyymm);
        query_affected($query);
    }
    $query = sprintf("select comment from act_comment_history where pl_bs_ym=%d and item='���ץ�����»�׷׻���'", $yyyymm);
    if (getUniResult($query,$comment_ctoku) <= 0) {
        $query = sprintf("insert into act_comment_history (pl_bs_ym, item, comment) values (%d, '���ץ�����»�׷׻���', '%s')", $yyyymm, $_POST['comment_ctoku']);
        query_affected($query);
    } else {
        $query = sprintf("update act_comment_history set comment='%s' where pl_bs_ym=%d and item='���ץ�����»�׷׻���'", $_POST['comment_ctoku'], $yyyymm);
        query_affected($query);
    }
    $query = sprintf("select comment from act_comment_history where pl_bs_ym=%d and item='����ctoku»�׷׻���'", $yyyymm);
    if (getUniResult($query,$comment_all) <= 0) {
        $query = sprintf("insert into act_comment_history (pl_bs_ym, item, comment) values (%d, '����ctoku»�׷׻���', '%s')", $yyyymm, $_POST['comment_all']);
        query_affected($query);
    } else {
        $query = sprintf("update act_comment_history set comment='%s' where pl_bs_ym=%d and item='����ctoku»�׷׻���'", $_POST['comment_all'], $yyyymm);
        query_affected($query);
    }
    $query = sprintf("select comment from act_comment_history where pl_bs_ym=%d and item='����¾ctoku»�׷׻���'", $yyyymm);
    if (getUniResult($query,$comment_other) <= 0) {
        $query = sprintf("insert into act_comment_history (pl_bs_ym, item, comment) values (%d, '����¾ctoku»�׷׻���', '%s')", $yyyymm, $_POST['comment_other']);
        query_affected($query);
    } else {
        $query = sprintf("update act_comment_history set comment='%s' where pl_bs_ym=%d and item='����¾ctoku»�׷׻���'", $_POST['comment_other'], $yyyymm);
        query_affected($query);
    }
    $_SESSION["s_sysmsg"] .= sprintf("<font color='yellow'>»�׷׻��� �õ��������ϴ�λ<br>�� %d�� %d��</font>",$ki,$tuki);
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_pl_act_ctoku.php");
    exit();
}

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
<?= $menu->out_site_java() ?>
<?= $menu->out_css() ?>
<?= $menu->out_jsBaseClass() ?>

<script type='text/javascript' language='JavaScript'>
<!--
function set_focus(){
    document.comment_form.comment_c.focus();
    // document.comment_form.comment_c.select();
}
// -->
</script>
<style type='text/css'>
<!--
select {
    background-color:teal;
    color:white;
}
textarea {
    background-color:white;
    color:black;
}
input.sousin {
    background-color:red;
}
input.text {
    background-color:black;
    color:white;
}
.pt9 {
    font-size: 9pt;
    font-family: monospace;
}
.pt11 {
    font-size: 11pt;
    font-family: monospace;
}
.pt11b {
    font:bold 11pt;
    font-family: monospace;
}
.pt12b {
    font:bold 12pt;
    font-family: monospace;
}
.title-font {
    font:bold 16.5pt;
    font-family: monospace;
}
.today-font {
    font-size: 10.5pt;
    font-family: monospace;
}
.right{
    text-align:right;
    font:bold 12pt;
    font-family: monospace;
}
.margin0 {
    margin:0%;
}
textarea {
    font-size: 10.0pt;
    font-family: monospace;
}
.save_button {
    font:bold 12pt;
    font-family: monospace;
    color:red;
}
-->
</style>
</head>
<body onLoad='set_focus();'>
    <center>
<?= $menu->out_title_border() ?>
        
        <form name='comment_form' action='profit_loss_comment_put_ctoku.php' method='post'>
            <table bgcolor='#d6d3ce' cellpadding='5' border='1'>
                <tr>
                    <td align='center' bgcolor='#e6e6e6' class='pt12b'>
                        ���ץ�ɸ���õ�����
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#e6e6e6' class='pt12b'>
                        <textarea name='comment_c' cols='114' rows='5' wrap='hard'><?php echo $comment_c ?></textarea>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#e6e6e6' class='pt12b'>
                        ���ץ������õ�����
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#e6e6e6' class='pt12b'>
                        <textarea name='comment_ctoku' cols='114' rows='5' wrap='hard'><?php echo $comment_ctoku ?></textarea>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#e6e6e6' class='pt12b'>
                        ���� �õ�����
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#e6e6e6' class='pt12b'>
                        <textarea name='comment_all' cols='114' rows='5' wrap='hard'><?php echo $comment_all ?></textarea>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#e6e6e6' class='pt12b'>
                        ����¾ �õ�����
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#e6e6e6' class='pt12b'>
                        <textarea name='comment_other' cols='114' rows='5' wrap='hard'><?php echo $comment_other ?></textarea>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#e6e6e6'>
                        <input type='submit' name='touroku' value='��¸' class='save_button'>
                    </td>
                </tr>
            </table>
        </form>
    </center>
</body>
<?=$menu->out_alert_java()?>
</html>
<?php
ob_end_flush();                 // ���ϥХåե���gzip���� END
?>
