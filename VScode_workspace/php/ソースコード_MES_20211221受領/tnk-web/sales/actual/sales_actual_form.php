<?php
//////////////////////////////////////////////////////////////////////////////
// �������칩�� ��� ���� �Ȳ� �������ե�����                             //
// Copyright(C) 2020-2020 Ryota.Waki tnksys@nitto-kohki.co.jp               //
// Changed history                                                          //
// 2020/12/17 Created   sales_form.php �� sales_actual_form.php             //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);              // E_ALL='2047' debug ��
// ini_set('display_errors', '1');              // Error ɽ�� ON debug �� ��꡼���女����
// ini_set('zend.ze1_compatibility_mode', '1'); // zend 1.X ����ѥ� php4�θߴ��⡼��
// ini_set('implicit_flush', 'off');            // echo print �� flush �����ʤ�(�٤��ʤ뤿��) CLI��
// ini_set('max_execution_time', 1200);         // ����¹Ի���=20ʬ WEB CGI��
ob_start('ob_gzhandler');                       // ���ϥХåե���gzip����
session_start();                                // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��

require_once ('../../function.php');
require_once ('../../tnk_func.php');
require_once ('../../MenuHeader.php');      // TNK ������ menu class
access_log();                               // Script Name �ϼ�ư����

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader(0);                  // ǧ�ڥ����å�0=���̰ʾ� �����=TOP_MENU �����ȥ�̤����

////////////// ����������
$menu->set_site( 1, 11);                    // site_index=01(����˥塼) site_id=11(����������)
////////////// �꥿���󥢥ɥ쥹����
// $menu->set_RetUrl(SYS_MENU);                // �̾�ϻ��ꤹ��ɬ�פϤʤ�
//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title('�� �� �� �� �� �� �� ��');
//////////// ɽ�������
$menu->set_caption('������ɬ�פʾ��������������򤷤Ʋ�������');
//////////// �ƽ����action̾�ȥ��ɥ쥹����
if( $_SESSION['User_ID'] == "300667" ) {
//$menu->set_action('������',   SALES . 'actual/sales_actual_set_plan_test.php');    // ���ͽ���ģ¤���¸����
$menu->set_action('������',   SALES . 'actual/sales_actual_view.php');
} else {
$menu->set_action('������',   SALES . 'actual/sales_actual_view.php');
}
//$menu->set_action('������',   SALES . 'actual/sales_actual_set_plan.php');    // ���ͽ���ģ¤���¸����

//////////// �֥饦�����Υ���å����к���
$uniq = $menu->set_useNotCache('target');

/////////////// �����Ϥ��ѿ��ν����
if ( isset($_SESSION['s_div']) ) {
    $div = $_SESSION['s_div'];
} else {
    $div = '';
}

if ( isset($_SESSION['s_target_ym']) ) {
    $target_ym = $_SESSION['s_target_ym'];
} else {
    $target_ym = '';
}

///// �о�ǯ���HTML <select> option �ν���
function getTarget_ymValues($target_ym)
{
    $query = "
                SELECT
                        DISTINCT target_ym
                FROM (
                        SELECT
                                SUBSTRING(kanryou, 0, 7) AS target_ym
                        FROM (
                                SELECT
                                        DISTINCT kanryou
                                FROM
                                        month_first_sales_plan
                              ) AS a -- ��λͽ�����ν�ʣ���
                      ) AS b -- ��λͽ������ǯ��Τߤ����
                ORDER BY
                            target_ym DESC
    ";

    $res = array();
    $rows = getResult2($query, $res);

    // �����
    $option = "";
    $temp_ym = "";
    for ($i=0; $i<$rows; $i++) {
        $next_ym = substr($res[$i][0],0,4) . '/' . substr($res[$i][0],4,2);
        if( $temp_ym == $next_ym ) continue;
        $temp_ym = $next_ym;
        $option .= "<option value='{$temp_ym}'";
        if( $target_ym == $temp_ym ) {
            $option .= " selected>{$temp_ym}</option>\n";
        } else {
            $option .= ">{$temp_ym}</option>\n";
        }
    }
    return $option;
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
<?php echo $menu->out_site_java()?>
<?php echo $menu->out_css()?>
<?php echo $menu->out_jsBaseClass() ?>
<script type='text/javascript' src='./sales_actual_form.js?<?php echo $uniq ?>'>
</script>

<script type='text/javascript' language='JavaScript'>
<!--
/* ������ϥե�����Υ�����Ȥ˥ե������������� */
function set_focus(){
//    document.form_name.element_name.focus();      // ������ϥե����ब������ϥ����Ȥ򳰤�
//    document.form_name.element_name.select();
}
// -->
</script>

<!-- �������륷���ȤΥե��������򥳥��� HTML���� �����Ȥ�����Ҥ˽���ʤ��������
<link rel='stylesheet' href='<?php echo MENU_FORM . '?' . $uniq ?>' type='text/css' media='screen'>
-->

<style type="text/css">
<!--
.pt8 {
    font-size:      8pt;
    font-weight:    normal;
    font-family:    monospace;
}
.pt9 {
    font-size:      9pt;
    font-weight:    normal;
    font-family:    monospace;
}
.pt10 {
    font-size:      10pt;
    font-weight:    normal;
    font-family:    monospace;
}
.pt10b {
    font-size:      10pt;
    font-weight:    bold;
    font-family:    monospace;
}
.pt11b {
    font-size:      11pt;
    font-weight:    bold;
    font-family:    monospace;
}
.pt12b {
    font-size:      12pt;
    font-weight:    bold;
    font-family:    monospace;
}
th {
    background-color:   yellow;
    color:              blue;
    font-size:          10pt;
    font-weight:        bold;
    font-family:        monospace;
}
td {
    font-size: 10pt;
}
.winbox {
    border-style: solid;
    border-width: 1px;
    border-top-color:       #FFFFFF;
    border-left-color:      #FFFFFF;
    border-right-color:     #bdaa90;
    border-bottom-color:    #bdaa90;
    /* background-color:#d6d3ce; */
}
.winbox_field {
    border-style: solid;
    border-width: 1px;
    border-top-color:       #bdaa90;
    border-left-color:      #bdaa90;
    border-right-color:     #FFFFFF;
    border-bottom-color:    #FFFFFF;
    /* background-color:#d6d3ce; */
}
a:hover {
    background-color:   blue;
    color:              white;
}
a {
    color:   blue;
}
body {
    background-image:url(<?php echo IMG ?>t_nitto_logo4.png);
    background-repeat:no-repeat;
    background-attachment:fixed;
    background-position:right bottom;
}
-->
</style>
</head>
</style>
<body onLoad='document.uri_form.div.focus();' style='overflow:hidden;'>
    <center>
<?php echo $menu->out_title_border()?>
        
        <form name='uri_form' action='<?php echo $menu->out_action('������') ?>' method='post' onSubmit='return true;'>
            <!----------------- ������ ��ʸ��ɽ������ ------------------->
            <table bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='5'>
                <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
            <table class='winbox_field' width='100%' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='3'>
                <tr>
                        <!--  bgcolor='#ffffc6' �������� --> 
                    <td class='winbox' style='background-color:yellow; color:blue;' colspan='2' align='center'>
                        <div class='caption_font'><?php echo $menu->out_caption(), "\n"?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='right'>
                        ���ʥ��롼�פ����򤷤Ʋ�������
                    </td>
                    <td class='winbox' align='center'>
                        <select name="div">
                            <option value="S"<?php if($div=="S") echo("selected"); ?>>���ץ�����</option>
                            <option value="D"<?php if($div=="D") echo("selected"); ?>>���ץ�ɸ��</option>
                            <option value="L"<?php if($div=="L") echo("selected"); ?>>��˥�����</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='right'>
                        ǯ������򤷤Ʋ�������
                    </td>
                    <td class='winbox' align='center'>
                        <select name="target_ym">
                            <?php echo getTarget_ymValues($target_ym) ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' colspan='2' align='center'>
                        <input type="submit" name="�Ȳ�" value="�¹�" >
                    </td>
                </tr>
            </table>
                </td></tr>
            </table> <!----------------- ���ߡ�End ------------------>
            <font class='pt9'><br>�� 2021ǯ��������ͽ��μ�ư��¸�򳫻ϡ�<br><br>2021ǯ����η��ͽ�꼫ư��¸�˼���!![��˥�����]�Τ߽����»�</font>
        </form>
    </center>
</body>
<?php echo $menu->out_alert_java()?>
</html>
<?php
ob_end_flush();                 // ���ϥХåե���gzip���� END
?>
