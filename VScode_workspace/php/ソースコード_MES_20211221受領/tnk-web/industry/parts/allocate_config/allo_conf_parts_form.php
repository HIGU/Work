<?php
//////////////////////////////////////////////////////////////////////////////
// �������ʹ���ɽ�ξȲ�  �ײ��ֹ�����ϡ���ǧ form                          //
//                              Allocated Configuration Parts ������������  //
// Copyright (C) 2004-2005 2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp //
// Changed history                                                          //
// 2004/05/28 Created   allo_conf_parts_form.php                            //
// 2004/06/07 ���󥯥���ȥ��������ǽ�ˤ����������ɽ��ǽ���ɲ�          //
// 2004/06/15 �����������о��� (plan-cut_plan)>0 �ײ���������Τ��ɲ� //
// 2004/12/28 MenuHeader class ����Ѥ��ƶ��̥�˥塼���ڤ�ǧ���������ѹ�   //
//                          �ǥ��쥯�ȥ��industry��industry/material���ѹ� //
// 2005/01/12 ��������̤��Ͽ��Ʊ�ͤν����򤵤��뤿��material_plan_no���ɲ�//
// 2005/06/17 JavaScript��select()�򥳥��Ȳ��(��������ɽ���Τ���)        //
// 2007/03/24 allo_conf_parts_view.php �� allo_conf_parts_Main.php ���ѹ�   //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
// ini_set('implicit_flush', '0');             // echo print �� flush �����ʤ�(�٤��ʤ뤿��) CLI CGI��
// ini_set('max_execution_time', 1200);        // ����¹Ի���=20ʬ CLI CGI��
ob_start('ob_gzhandler');                   // ���ϥХåե���gzip����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��

require_once ('../../../function.php');     // define.php �� pgsql.php �� require_once ���Ƥ���
require_once ('../../../tnk_func.php');     // TNK �˰�¸������ʬ�δؿ��� require_once ���Ƥ���
require_once ('../../../MenuHeader.php');   // TNK ������ menu class
access_log();                               // Script Name �ϼ�ư����

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader(0);                  // ǧ�ڥ����å�0=���̰ʾ� �����=TOP_MENU �����ȥ�̤����

////////////// ����������
$menu->set_site(INDEX_INDUST, 26);          // site_index=30(������˥塼) site_id=26(�������ʹ���ɽ�ξȲ�)
////////////// �꥿���󥢥ɥ쥹����
// $menu->set_RetUrl(INDUST_MENU);          // �̾�ϻ��ꤹ��ɬ�פϤʤ�
//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title('���� ���� ����ɽ �� �Ȳ� �ײ��ֹ�����');
//////////// ɽ�������
$menu->set_caption('�ײ��ֹ�');
//////////// �ƽ����action̾�ȥ��ɥ쥹����
$menu->set_action('��������ɽ��ɽ��',   INDUST . 'parts/allocate_config/allo_conf_parts_Main.php');
$menu->set_action('��������Ѱ�������ɽ��ɽ��',   INDUST . 'parts/allocate_config_entry/allo_conf_parts_Main.php');
$menu->set_action('��������Ѱ�������ɽ��TEST',   INDUST . 'parts/allocate_config_test/allo_conf_parts_Main.php');
// $menu->set_action('�߸˷���',   INDUST . 'parts/parts_stock_view.php');

//////////// JavaScript Stylesheet File ��ɬ���ɤ߹��ޤ���
$uniq = uniqid('target');

////////////// ��ʬ�Υݥ��ȥǡ���������å� $_REQUEST��Ȥ�ʤ���ͳ��$_COOKIE��$_FILES��������뤿��
if (isset($_POST['plan'])) {
    $plan = $_POST['plan'];
} elseif (isset($_GET['plan'])) {
    $plan = $_GET['plan'];
}
if (isset($plan)) {
    if (strlen($plan) < 8) {
        ///// ����ꥹ�Ȥ�ɽ��������
        $query = "select plan_no    as �ײ��ֹ�     -- 0
                        , parts_no  as �����ֹ�     -- 1
                        , trim(substr(midsc, 1, 25))
                                    as ����̾       -- 2
                        , chaku     as �����       -- 3
                        , kanryou   as ��λ��       -- 4
                        , plan - cut_plan
                                    as �ײ��       -- 5
                        , kansei    as ������       -- 6
                        , trim(note15)
                                    as ������       -- 7
                    from
                        assembly_schedule
                    left outer join
                        miitem
                    on (parts_no=mipn)
                    where plan_no like '{$plan}%' and (plan-cut_plan)>0 and parts_no!='999999999' and note15 not like '%NKCT%'
                    ORDER BY plan_no DESC, kanryou DESC limit 50";
        $res   = array();
        $field = array();
        if ( ($rows = getResultWithField2($query, $field, $res)) > 0) {
            $num = count($field);       // �ե�����ɿ�����
            $parts_no = '';
        } else {
            $num = 0;
            $parts_no = '';
        }
        $set_view = $plan;   // ����ꥹ�Ȥ�ɽ��
        $_SESSION['plan_cond'] = $set_view;
    } else {
        $query = "select parts_no, midsc, kansei, note15
                    from
                        assembly_schedule
                    left outer join
                        miitem
                    on (parts_no=mipn)
                    where plan_no='{$plan}'";
        $res = array();
        if (getResult2($query, $res) <= 0) {
            $_SESSION['s_sysmsg'] = "{$plan}���Ǥ���Ͽ����Ƥ��ޤ���";
            $parts_no  = '';
            $assy_name = "<font color='red'>̤ �� Ͽ</font>";
            $kansei    = '';
            $kouji_no  = '';
        } else {
            $parts_no  = $res[0][0];
            $assy_name = $res[0][1];
            $kansei    = $res[0][2];
            $kouji_no  = $res[0][3];
            $_SESSION['plan_no']  = $plan;       // �ײ��ֹ�γ���(entry�������줿�餳��ǽ���)
            $_SESSION['assy_no']  = $parts_no;
            $_SESSION['material_plan_no']  = $plan; // ��������̤��Ͽ��Ʊ�ͤν����򤵤��뤿��
        }
    }
} else {
    $plan = '';
}

///// ��������С���ɽ������ɽ��
if (isset($set_view)) {
    $scrollbar = "style='overflow:auto;'";
} else {
    $scrollbar = "style='overflow:hidden;'";
}

////////////// �Ȳ�ܥ��󤬲����줿(entry�ܥ���)
if (isset($_POST['entry'])) {       // �꥿���󥢥ɥ쥹����
    ///// �������ʹ���ɽ��view��
/**/
    if( $_SESSION['User_ID'] == '300667' ) {
//        header('Location: ' . H_WEB_HOST . $menu->out_action('��������Ѱ�������ɽ��ɽ��'));
        header('Location: ' . H_WEB_HOST . $menu->out_action('��������Ѱ�������ɽ��TEST'));
//        header('Location: ' . H_WEB_HOST . $menu->out_action('��������ɽ��ɽ��'));
/**
    } else if( $_SESSION['User_ID'] == '970352' || $_SESSION['User_ID'] == '300144') {
        header('Location: ' . H_WEB_HOST . $menu->out_action('��������Ѱ�������ɽ��ɽ��'));
/**/
    }else{
        header('Location: ' . H_WEB_HOST . $menu->out_action('��������Ѱ�������ɽ��ɽ��'));
//        header('Location: ' . H_WEB_HOST . $menu->out_action('��������ɽ��ɽ��'));
    }
    exit();
} elseif (isset($_GET['entry'])) {  // �꥿���󥢥ɥ쥹����
    ///// �������ʹ���ɽ��view�� (�����������Τ���Ķ�������������)
    if( $_SESSION['User_ID'] == '300667' || $_SESSION['User_ID'] == '970352' || $_SESSION['User_ID'] == '300144' ) {
        header('Location: ' . H_WEB_HOST . $menu->out_action('��������Ѱ�������ɽ��ɽ��'));
    }else{
        header('Location: ' . H_WEB_HOST . $menu->out_action('��������Ѱ�������ɽ��ɽ��'));
//        header('Location: ' . H_WEB_HOST . $menu->out_action('��������ɽ��ɽ��') . "?plan_cond={$_SESSION['plan_cond']}");
    }
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
<title><?= $menu->out_title() ?></title>
<?= $menu->out_site_java() ?>
<?= $menu->out_css() ?>

<!--    �ե��������ξ��
<script language='JavaScript' src='template.js?<?= $uniq ?>'>
</script>
-->

<script language="JavaScript">
<!--
/* ����ʸ�����������ɤ��������å� */
function isDigit(str) {
    var len=str.length;
    var c;
    for (i=1; i<len; i++) {
        c = str.charAt(i);
        if ((c < "0") || (c > "9")) {
            return true;
        }
    }
    return false;
}

function chk_plan_entry(obj) {
    obj.plan.value = obj.plan.value.toUpperCase();
    if (obj.plan.value.length != 0) {
        // if (obj.plan.value.length != 8) {
        if (obj.plan.value.length < 1) {
            // alert("�ײ��ֹ�η���ϣ���Ǥ���");
            alert("�ײ��ֹ�Ͼ��ʤ��Ȥ⣱��ʾ����Ϥ��Ʋ�������");
            obj.plan.focus();
            obj.plan.select();
            return false;
        } else {
            window.location = '<?= H_WEB_HOST . $menu->out_self() ?>?plan=' + obj.plan.value;
            return true;
        }
    }
    alert('�ײ��ֹ椬���Ϥ���Ƥ��ޤ���');
    obj.plan.focus();
    obj.plan.select();
    return false;
}

/* ������ϥե�����Υ�����Ȥ˥ե������������� */
function set_focus(){
    document.entry_form.plan.focus();      // ������ϥե����ब������ϥ����Ȥ򳰤�
    document.entry_form.plan.select();
}
// -->
</script>

<!-- �������륷���ȤΥե��������򥳥��� HTML���� �����Ȥ�����Ҥ˽���ʤ��������
<link rel='stylesheet' href='template.css?<?= $uniq ?>' type='text/css' media='screen'>
-->

<style type="text/css">
<!--
.pt9 {
    font-size:      11pt;
    font-weight:    normal;
    font-family:    monospace;
}
.pt10b {
    font-size:      11pt;
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
.plan_font {
    font-size:      13pt;
    font-weight:    bold;
    text-align:     left;
    font-family:    monospace;
}
.entry_font {
    font-size:      11pt;
    font-weight:    bold;
    color:          red;
}
th {
    background-color:   yellow;
    color:              blue;
    font-size:          11pt;
    font-wieght:        bold;
    font-family:        monospace;
}
a:hover {
    background-color: gold;
}
a {
    font-size:   10pt;
    font-weight: bold;
    color:       blue;
}
.winbox {
    border-style: solid;
    border-width: 1px;
    border-top-color:       #FFFFFF;
    border-left-color:      #FFFFFF;
    border-right-color:     #999999;
    border-bottom-color:    #999999;
    background-color:#d6d3ce;
}
.winbox_field {
    border-style: solid;
    border-width: 1px;
    border-top-color:       #999999;
    border-left-color:      #999999;
    border-right-color:     #FFFFFF;
    border-bottom-color:    #FFFFFF;
    background-color:#d6d3ce;
}
-->
</style>
</head>
<body <?=$scrollbar?> onLoad='set_focus()'>
    <center>
<?= $menu->out_title_border() ?>
        <div style='font-size:11pt'>�ײ��ֹ��1�夫�饤�󥯥���ȥ��������ޤ���(1���ܤϻ����� 2���ܤϷ� 1.2.3...A=10.B=11.C=12 3���ܰʹߤ�Ϣ��</div>
        
        <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
        <table width='100%'class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <form name='entry_form' method='post' action='<?= $menu->out_self() ?>' onSubmit='return chk_plan_entry(this)'>
                <tr>
                    <td class='winbox' nowrap align='center'>
                        <div class='caption_font'><?= $menu->out_caption() . "\n" ?></div>
                    </td>
                    <td class='winbox' width='300' nowrap align='center'>
                        <input class='plan_font' type='text' name='plan' value='<?= $plan ?>' size='8' maxlength='8'>
                        <input class='pt11b' type='submit' name='conf' value='��ǧ'>
                    </td>
                </tr>
                <?php if ($plan != '' && !isset($set_view)) { ?>
                <tr>
                    <td class='winbox' nowrap align='center'>
                        <div class='caption_font'>�����ֹ�</div>
                    </td>
                    <td class='winbox' nowrap align='left'>
                        <div class='pt12b'><?= $parts_no ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap align='center'>
                        <div class='caption_font'>�� �� ̾</div>
                    </td>
                    <td class='winbox' width='300' nowrap align='left'>
                        <div class='pt12b'><?= $assy_name ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap align='center'>
                        <div class='caption_font'>�� �� ��</div>
                    </td>
                    <td class='winbox' nowrap align='left'>
                        <div class='pt12b'><?= $kansei ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap align='center'>
                        <div class='caption_font'>�����ֹ�</div>
                    </td>
                    <td class='winbox' nowrap align='left'>
                        <div class='pt12b'><?= $kouji_no ?></div>
                    </td>
                </tr>
                    <?php if ($parts_no != '') { // ̤��Ͽ�ξ���ɽ�����ʤ� ?>
                    <tr>
                        <td class='winbox' colspan='2' nowrap align='center'>
                            <input class='entry_font' type='submit' name='entry' value='�Ȳ�'>
                        </td>
                    </tr>
                    <?php } ?>
                <?php } ?>
            </form>
        </table>
            </td></tr>
        </table> <!----------------- ���ߡ�End ------------------>
        
        <?php if (isset($set_view)) { ?>
        <!--------------- ����������ʸ��ɽ��ɽ������ -------------------->
        <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
        <table width='100%' class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <thead>
                <!-- �ơ��֥� �إå�����ɽ�� -->
                <tr>
                    <th nowrap width='10'>No.</th>        <!-- �ԥʥ�С���ɽ�� -->
                <?php
                for ($i=0; $i<$num; $i++) {             // �ե�����ɿ�ʬ���֤�
                ?>
                    <th nowrap><?= $field[$i] ?></th>
                <?php
                }
                ?>
                </tr>
            </thead>
            <tbody>
                <?php
                for ($r=0; $r<$rows; $r++) {
                    echo "<tr>\n";
                    echo "    <td class='winbox' nowrap align='right'><div class='pt10b'>", ($r + 1), "</div></td>    <!-- �ԥʥ�С���ɽ�� -->\n";
                    for ($i=0; $i<$num; $i++) {         // �쥳���ɿ�ʬ���֤�
                        // <!--  bgcolor='#ffffc6' �������� --> 
                        switch ($i) {
                        case 0: // �ײ��ֹ�
                            echo "<td class='winbox' nowrap align='center'><a class='pt9' href='{$menu->out_self()}?plan={$res[$r][$i]}&entry'>", $res[$r][$i], "</a></td>\n";
                            break;
                        case 2:
                        case 7:
                            echo "<td class='winbox' nowrap align='left'><div class='pt9'>", $res[$r][$i], "</div></td>\n";
                            break;
                        case 3:
                        case 4:
                            echo "<td class='winbox' nowrap align='center'><div class='pt9'>", format_date($res[$r][$i]), "</div></td>\n";
                            break;
                        case 5:
                        case 6:
                            echo "<td class='winbox' nowrap align='right'><div class='pt9'>", number_format($res[$r][$i]), "</div></td>\n";
                            break;
                        default:
                            echo "<td class='winbox' nowrap align='center'><div class='pt9'>", $res[$r][$i], "</div></td>\n";
                        }
                        // <!-- ����ץ�<td rowspan='2' colspan='3' width='200' align='center' class='pt10b' bgcolor='#ffffc6'>  </td> -->
                    }
                    echo "</tr>\n";
                }
                ?>
            </tbody>
        </table>
            </td></tr>
        </table> <!----------------- ���ߡ�End ------------------>
        <?php } ?>
    </center>
</body>
<?=$menu->out_alert_java()?>
</html>
<?php
ob_end_flush();                 // ���ϥХåե���gzip���� END
?>
