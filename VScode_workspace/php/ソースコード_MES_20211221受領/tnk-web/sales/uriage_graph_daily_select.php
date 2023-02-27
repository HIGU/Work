<?php
//////////////////////////////////////////////////////////////////////////////
// ��� ���� ����� (ǯ������)                                              //
// Copyright (C) 2002-2005 2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp //
// Changed history                                                          //
// 2002/04/08 Created   uriage_graph_daily_select.php                       //
// 2002/08/08 ���å������������ؤ�                                        //
// 2002/08/27 �ե졼���б�                                                  //
// 2002/10/05 processing_msg.php ���ɲ�(�׻���)                             //
// 2003/09/06 error_reporting = E_ALL �б��Τ��� �����ѿ��ν�����ɲ�       //
// 2003/12/12 define���줿����ǥǥ��쥯�ȥ�ȥ�˥塼����Ѥ��ƴ�������    //
//            ob_start('ob_gzhandler') ���ɲ�                               //
// 2004/02/09 select��������¨�¹Ԥ���褦��JavaScript�ǥ��å��ɲ�        //
// 2005/02/14 MenuHeader class ����Ѥ��ƶ��̥�˥塼���ڤ�ǧ���������ѹ�   //
// 2007/10/03 �����ɽ��¦��local���å����ذܹԤˤ��  E_ALL | E_STRICT�� //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
// ini_set('zend.ze1_compatibility_mode', '1');    // zend 1.X ����ѥ� php4�θߴ��⡼��
// ini_set('implicit_flush', 'off');           // echo print �� flush �����ʤ�(�٤��ʤ뤿��) CLI��
// ini_set('max_execution_time', 1200);        // ����¹Ի���=20ʬ WEB CGI��
ob_start('ob_gzhandler');                   // ���ϥХåե���gzip����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��

require_once ('../function.php');           // define.php �� pgsql.php �� require_once ���Ƥ���
require_once ('../tnk_func.php');           // TNK �˰�¸������ʬ�δؿ��� require_once ���Ƥ���
require_once ('../MenuHeader.php');         // TNK ������ menu class
access_log();                               // Script Name �ϼ�ư����

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader(0);                  // ǧ�ڥ����å�0=���̰ʾ� �����=TOP_MENU �����ȥ�̤����

////////////// ����������
$menu->set_site( 1,  3);                    // site_index=1(����˥塼) site_id=3(���ץ����)
////////////// �꥿���󥢥ɥ쥹����
// $menu->set_RetUrl(SALES_MENU);              // �̾�ϻ��ꤹ��ɬ�פϤʤ�
//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title('������ץ����(ǯ�����)');
//////////// ɽ�������
$menu->set_caption('���ץ����(ǯ�����)');
//////////// �ƽ����action̾�ȥ��ɥ쥹����
$menu->set_action('���ץ����',   SALES . 'uriage_graph_all_niti.php');

$yyyymm = date('Ym');
if ( isset($_REQUEST['yyyymm']) ) {
    $s_yyyymm = $_REQUEST['yyyymm'];
} else {
    $s_yyyymm = '';
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
<title><?php echo $menu->out_title() ?></title>
<?php echo $menu->out_site_java() ?>
<?php echo $menu->out_css() ?>

<style type='text/css'>
<!--
select {
    background-color:   teal;
    color:              white;
}
td {
    font-size:          0.85em;
    font-weight:        normal;
    font-family:        monospace;
}
-->
</style>
<script language="JavaScript">
<!--
function set_focus()
{
    // document.body.focus();   // F2/F12������ͭ���������б�
    // document.mhForm.backwardStack.focus();  // �嵭��IE�ΤߤΤ���NN�б�
    document.ym_form.yyyymm.focus();
}
// -->
</script>
</head>
<body onLoad='set_focus()' style='overflow-y:hidden;'>
    <center>
<?php echo $menu->out_title_border()?>
        
        <table width='100%' cellspacing='0' cellpadding='0' border='0'>
            <tr><td valign="top">
                <table align='center'>
                    <tr><td><p>
                        <!-- <img src='<?php echo IMG ?>t_nitto_logo3.gif' width=348 height=83> -->
                        <img src='<?php echo IMG ?>t_nitto_logo2.gif' width=348 height=83>
                    </p></td></tr>
                </table>
                <table width='100%'>
                    <tr>
                        <td align='center'>
                            <span class='caption_font'><?php echo $menu->out_caption()?></span>
                        </td>
                    </tr>
                    <tr>
                        <td align='center'>
                            <br>
                            <img src='<?php echo IMG ?>tnk-turbine_small.gif'>
                        </td>
                    </tr>
                </table>
                <table width='100%' cellspacing='0' cellpadding='3'>
                    <form name='ym_form' action='<?php echo $menu->out_action('���ץ����')?>' method='get'>
                        <tr>
                            <td align='center'>
                                ɽ������ǯ�����ꤷ�Ʋ�������
                                <select name='yyyymm' onChange='document.ym_form.submit()'>
                                    <?php
                                    if ($s_yyyymm == $yyyymm) {
                                        echo "<option value='$yyyymm' selected>$yyyymm</option>\n";
                                    } else {
                                        echo "<option value='$yyyymm'>$yyyymm</option>\n";
                                    }
                                        // ����������γ� ǯ��ϥ���ե�����򻲾Ȥ���
                                    $query_wrk = "select ǯ�� from wrk_uriage where ǯ��>=200010 order by ǯ�� desc";
                                    $res_wrk = array();
                                    if ($rows_wrk = getResult($query_wrk,$res_wrk)) {
                                        for ($cnt=0; $cnt<$rows_wrk; $cnt++) {
                                            if ($s_yyyymm == $res_wrk[$cnt][0]) {
                                                echo "<option value=" . $res_wrk[$cnt][0] . " selected>" . $res_wrk[$cnt][0] . "</option>\n";
                                            } else {
                                                echo "<option value=" . $res_wrk[$cnt][0] . ">" . $res_wrk[$cnt][0] . "</option>\n";
                                            }
                                        }
                                    }
                                    ?>
                                </select>
                                <br>�㡧200202 ��2002ǯ02���
                            </td>
                        </tr>
                        <tr>
                            <td align='center'>
                                <input type='submit' name='exec_graph' value='�¹�' >
                            </td>
                        </tr>
                    </form>
                </table>
            </td></tr>
        </table>
    </center>
</body>
<?php echo $menu->out_alert_java()?>
</html>
<?php
ob_end_flush();                 // ���ϥХåե���gzip���� END
?>
