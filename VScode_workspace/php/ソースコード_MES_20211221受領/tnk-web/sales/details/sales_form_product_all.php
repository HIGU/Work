<?php
//////////////////////////////////////////////////////////////////////////////
// �������칩�� ��� ���� �Ȳ� ���ʥ��롼���� �������ե�����              //
// Copyright(C) 2010 - 2015 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp   //
// Changed history                                                          //
// 2010/12/14 Created   sales_form_product_all.php                          //
// 2011/01/20 ���դμ����Ϥ������Զ�����������                          //
// 2011/05/31 ���롼�ץ������ѹ���ȼ��SQLʸ���ѹ�                           //
// 2015/03/06 ���������̤ξȲ���б�(���ʥ��롼����ǰ㤤�������)        //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
// ini_set('zend.ze1_compatibility_mode', '1');    // zend 1.X ����ѥ� php4�θߴ��⡼��
// ini_set('implicit_flush', 'off');           // echo print �� flush �����ʤ�(�٤��ʤ뤿��) CLI��
// ini_set('max_execution_time', 1200);        // ����¹Ի���=20ʬ WEB CGI��
ob_start('ob_gzhandler');                   // ���ϥХåե���gzip����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��

require_once ('../../function.php');
require_once ('../../tnk_func.php');
require_once ('../../MenuHeader.php');      // TNK ������ menu class
access_log();                               // Script Name �ϼ�ư����

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader(0);                  // ǧ�ڥ����å�0=���̰ʾ� �����=TOP_MENU �����ȥ�̤����

////////////// ����������
//$menu->set_site( 1, 11);                    // site_index=01(����˥塼) site_id=11(����������)
////////////// �꥿���󥢥ɥ쥹����
// $menu->set_RetUrl(SYS_MENU);                // �̾�ϻ��ꤹ��ɬ�פϤʤ�
//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title('���ʥ��롼���� ��彸�� �������');
//////////// ɽ�������
$menu->set_caption('������ɬ�פʾ��������������򤷤Ʋ�������');
//////////// �ƽ����action̾�ȥ��ɥ쥹����
$menu->set_action('�������',   SALES . 'details/sales_view_product_all.php');

//////////// �֥饦�����Υ���å����к���
$uniq = $menu->set_useNotCache('target');

/////////////// �����Ϥ��ѿ��ν����
if ( isset($_SESSION['s_uri_passwd']) ) {
    $uri_passwd = $_SESSION['s_uri_passwd'];
} else {
    $uri_passwd = '';
}
if ( isset($_SESSION['s_div']) ) {
    $div = $_SESSION['s_div'];
} else {
    if ( isset($_POST['div']) ) {
        $div = $_POST['div'];
    } else {
        $div = '';
    }
}
if ( isset($_SESSION['s_d_start']) ) {
    $d_start = $_SESSION['s_d_start'];
} else {
    if ( isset($_POST['d_start']) ) {
        $d_start = $_POST['d_start'];
    } else {
        $d_start = date_offset(1);
    }
}
if ( isset($_SESSION['s_d_end']) ) {
    $d_end = $_SESSION['s_d_end'];
} else {
    if ( isset($_POST['d_end']) ) {
        $d_end = $_POST['d_end'];
    } else {
        $d_end = date_offset(1);
    }
}
if ( isset($_SESSION['s_kubun']) ) {
    $kubun = $_SESSION['s_kubun'];
} else {
    if ( isset($_POST['kubun']) ) {
        $kubun = $_POST['kubun'];
    } else {
        $kubun = '';
    }
}
if ( isset($_SESSION['s_uri_ritu']) ) {
    $uri_ritu = $_SESSION['s_uri_ritu'];
    $uri_ritu = '52.0';     // �����
} else {
    $uri_ritu = '52.0';     // �����
}

// $_SESSION['s_rec_No'] = 0;  // ɽ���ѥ쥳���ɭ��0�ˤ��롣

// �Ȳ������ʥ��롼�ץ����ɤμ���
$query_s = "
        SELECT  groupm.group_no                AS ���롼���ֹ�     -- 0
            ,   groupm.group_name              AS ���롼��̾       -- 1
        FROM
            product_serchGroup AS groupm
        ORDER BY
            group_name
    ";

    $res_s = array();
    if (($rows_s = getResultWithField2($query_s, $field_s, $res_s)) <= 0) {
        $_SESSION['s_sysmsg'] = "���롼�פ���Ͽ������ޤ���";
        $field[0]   = "���롼���ֹ�";
        $field[1]   = "���롼��̾";
        $_SESSION['s_sysmsg'] = "��Ͽ������ޤ���";
        //$result->add_array2('res_s', '');
        //$result->add_array2('field_s', '');
        //$result->add('num_s', 2);
        //$result->add('rows_s', '');
    } else {
        $num_s = count($field_s);
        //$result->add_array2('res_s', $res_s);
        //$result->add_array2('field_s', $field_s);
        //$result->add('num_s', $num_s);
        //$result->add('rows_s', $rows_s);
    }

////////////// ɽ����(����ɽ)����ʬ�॰�롼��̤��Ͽ������SQL�Ǽ���
$query_num = "
    SELECT  count(*) as num
    FROM
        product_serchGroup
    WHERE 
        top_code = 0
";

$res_num = array();
if (getResult($query_num, $res_num) <= 0) {
    $unreg_num_top = 0;
} else {
    $unreg_num_top = $res_num[0]['num'];
}

////////////// ɽ����(����ɽ)�θ����ѥ��롼��̤��Ͽ������SQL�Ǽ���
$query_num = "
    SELECT  count(*) as num
    FROM
        -- mshgnm
        msshg3
    WHERE 
        mhggp IS NULL
";

$res_num = array();
if (getResult($query_num, $res_num) <= 0) {
    $unreg_num = 0;
} else {
    $unreg_num = $res_num[0]['num'];
}
$query_num = "
    SELECT  count(*) as num
    FROM
        -- mshgnm
        msshg3
    WHERE 
        mhggp = 0
";

$res_num = array();
if (getResult($query_num, $res_num) <= 0) {
} else {
    $unreg_num += $res_num[0]['num'];
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
<script type='text/javascript' src='./sales_form.js?<?php echo $uniq ?>'>
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
<body onLoad='document.uri_form.uri_passwd.focus(); document.uri_form.uri_passwd.select()' style='overflow:hidden;'>
    <center>
<?php echo $menu->out_title_border()?>
        
        <form name='uri_form' action='<?php echo $menu->out_action('�������')?>' method='post' onSubmit='return chk_sales_form_all(this)'>
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
                        �ѥ���ɤ�����Ʋ�����
                    </td>
                    <td class='winbox' align='center'>
                        <input type='password' name='uri_passwd' size='12' value='<?php echo("$uri_passwd"); ?>' maxlength='8'>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='right'>
                        ���ʥ��롼�פ����򤷤Ʋ�����
                    </td>
                    <td class='winbox' align='center'>
                        <select name="div">
                            <option value=" "<?php if($div==" ") echo("selected"); ?>>�����롼��</option>
                            <option value="C"<?php if($div=="C") echo("selected"); ?>>���ץ�����</option>
                            <option value="S"<?php if($div=="S") echo("selected"); ?>>���ץ�����</option>
                            <option value="D"<?php if($div=="D") echo("selected"); ?>>���ץ�ɸ��</option>
                            <option value="L"<?php if($div=="L") echo("selected"); ?>>��˥�����</option>
                            <option value="T"<?php if($div=="T") echo("selected"); ?>>�ġ���</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='right'>
                        ���դ���ꤷ�Ʋ�����(ɬ��)
                    </td>
                    <td class='winbox' align='center'>
                        <input type="text" name="d_start" size="9" value="<?php echo($d_start); ?>" maxlength="8">
                        ��
                        <input type="text" name="d_end" size="9" value="<?php echo($d_end); ?>" maxlength="8">
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='right' width='400'>
                        ����ʬ=
                        ���������Τ�
                        <?php if ($_SESSION['User_ID'] == '300144') { ?>
                        �������ʴ�
                        <?php } ?>
                    </td>
                    <td class='winbox' align='center'>
                        <select name="kubun">
                            <option value="1"<?php if($kubun=="1") echo("selected"); ?>>1����</option>
                            <?php if ($_SESSION['User_ID'] == '300144') { ?>
                            <option value="2"<?php if($kubun=="2") echo("selected"); ?>>2���ʴ�</option>
                            <?php } ?>
                        <select>
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
        </form>
        <BR>
        <?php if ($unreg_num > 0) { ?>
            <?php if ($unreg_num_top > 0) { ?>
                <font color='red'><B>
                �����ѥ��롼�׵ڤӸ�������ʬ�॰�롼�פ�̤��Ͽ�Τ�Τ�����ޤ�����
                <BR>
                ��������̤�ɽ������٤ˡ��ޥ���������Ͽ�򤪴ꤤ���ޤ���
                </font></b>
            <?php } else { ?>
                <font color='red'><B>
                �����ѥ��롼�פ�̤��Ͽ�Τ�Τ�����ޤ�����
                <BR>
                ��������̤�ɽ������٤ˡ��ޥ���������Ͽ�򤪴ꤤ���ޤ���
                </font></b>
            <?php } ?>
        <?php } elseif ($unreg_num_top > 0) { ?>
            <font color='red'><B>
            ��������ʬ�॰�롼�פ�̤��Ͽ�Τ�Τ�����ޤ�����
            <BR>
            ��������̤�ɽ������٤ˡ��ޥ���������Ͽ�򤪴ꤤ���ޤ���
            </font></b>
        <?php } ?>
    </center>
</body>
<?php echo $menu->out_alert_java()?>
</html>
<?php
ob_end_flush();                 // ���ϥХåե���gzip���� END
?>
