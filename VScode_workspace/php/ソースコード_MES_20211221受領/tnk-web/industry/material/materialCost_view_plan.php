<?php
//////////////////////////////////////////////////////////////////////////////
// �������ξȲ�  �ײ��ֹ�����ϡ���ǧ form                                //
// Copyright (C) 2003-2019 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2003/12/19 Created   metarialCost_view_plan.php                          //
// 2004/04/07 �����ȥ�̾�˷ײ��ֹ����ˤ����ɵ�                          //
// 2004/05/12 �����ȥ�˥塼ɽ������ɽ�� �ܥ����ɲ� menu_OnOff($script)�ɲ� //
// 2005/02/07 MenuHeader class ����Ѥ��ƶ��̥�˥塼���ڤ�ǧ���������ѹ�   //
// 2005/06/08 materialCost_view_assy.php �Υ������򥫥����ޥ���             //
// 2005/06/08 PostgreSQL8.0�� where plan_no like '%{$plan}%'��'{$plan}%'    //
//            ���ѹ������ Index Scan �ˤʤ뤿���ѹ�������                  //
// 2005/09/07 MenuON/Off��$_SESSION['material_max']����Notise�ˤʤ�Τ�@��  //
// 2007/03/07 ����������Υ�󥯤򥯥�å�������ä����˹ԥޡ������ɲ� recNo//
// 2007/03/24 php�Υ��硼�ȥ��åȤ���� Ajax�ˤ�$uniq���ɲ� NN�Ѥ������̤ʤ�//
// 2019/05/31 ��Ω��η׻��������٤Ƥι�����­���Ƽ�����Ψ��ݤ���褦��  //
//            �ʤä������Τ�����(Assy�����Ϥ�����Ʊ��)                 ��ë //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
// ini_set('implicit_flush', 'off');           // echo print �� flush �����ʤ�(�٤��ʤ뤿��) CLI CGI��
// ini_set('max_execution_time', 1200);        // ����¹Ի���=20ʬ CLI CGI��
ob_start('ob_gzhandler');                   // ���ϥХåե���gzip����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��

require_once ('../../function.php');        // define.php �� pgsql.php �� require_once ���Ƥ���
require_once ('../../tnk_func.php');        // TNK �˰�¸������ʬ�δؿ��� require_once ���Ƥ���
require_once ('../../MenuHeader.php');      // TNK ������ menu class
require_once ('../../ControllerHTTP_Class.php');// TNK ������ MVC Controller Class
//////////// ���å����Υ��󥹥��󥹤���Ͽ
$session = new Session();
if (isset($_REQUEST['recNo'])) {
    $session->add_local('recNo', $_REQUEST['recNo']);
    exit();
}
access_log();                               // Script Name �ϼ�ư����

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader(0);                  // ǧ�ڥ����å�0=���̰ʾ� �����=TOP_MENU �����ȥ�̤����

////////////// ����������
$menu->set_site(30, 20);                    // site_index=30(������˥塼) site_id=20(�������ξȲ� �ײ��ֹ�)
////////////// �꥿���󥢥ɥ쥹����
// $menu->set_RetUrl(INDUST_MENU);             // �̾�ϻ��ꤹ��ɬ�פϤʤ�
//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title('�� �� �� �� �� �� �� (�ײ��ֹ����)');
//////////// ɽ�������
$menu->set_caption('�ײ��ֹ������');
//////////// �ƽ����action̾�ȥ��ɥ쥹����
$menu->set_action('�����������',   INDUST . 'material/materialCost_view.php');

//////////// JavaScript Stylesheet File ��ɬ���ɤ߹��ޤ���
$uniq = 'ID=' . uniqid('target');

//////////// �����Υ��å����ǡ�����¸   ���ǡ����Ǥ�ڤ����뤿��
if (! (isset($_POST['forward']) || isset($_POST['backward']) || isset($_GET['page_keep'])) ) {
    $session->add_local('recNo', '-1');         // 0�쥳���ɤǥޡ�����ɽ�����Ƥ��ޤ�������б�
    if (isset($_POST['plan'])) {
        $plan = $_POST['plan'];
        $query = "select count(*)
                from
                    material_cost_header
                where plan_no like '{$plan}%'";
        if (getUniResult($query, $maxrows) <= 0) {
            $_SESSION['s_sysmsg'] = '��ץ쥳���ɿ��μ����˼���';
        } else {
            $_SESSION['material_max'] = $maxrows;
        }
        $_SESSION['mate_plan'] = $_POST['plan'];
    }
} else {        // ���ǡ����ǡ�����¸ �λ���
    $maxrows = @$_SESSION['material_max'];       // ��ץ쥳���ɿ�������
    $_POST['plan'] = @$_SESSION['mate_plan'];    // �ݥ��ȥǡ����򥨥ߥ�졼��
}

//////////// ���ǤιԿ�
if (isset($_SESSION['material_page'])) {
    define('PAGE', $_SESSION['material_page']);
} else {
    define('PAGE', 23);
}

//////////// �ڡ������ե��å�����
if ( isset($_POST['forward']) ) {                       // ���Ǥ������줿
    $_SESSION['mate_offset'] += PAGE;
    if ($_SESSION['mate_offset'] >= $maxrows) {
        $_SESSION['mate_offset'] -= PAGE;
        if ($_SESSION['s_sysmsg'] == '') {
            $_SESSION['s_sysmsg'] .= "<font color='yellow'>���ǤϤ���ޤ���</font>";
        } else {
            $_SESSION['s_sysmsg'] .= "<br><font color='yellow'>���ǤϤ���ޤ���</font>";
        }
    }
} elseif ( isset($_POST['backward']) ) {                // ���Ǥ������줿
    $_SESSION['mate_offset'] -= PAGE;
    if ($_SESSION['mate_offset'] < 0) {
        $_SESSION['mate_offset'] = 0;
        if ($_SESSION['s_sysmsg'] == '') {
            $_SESSION['s_sysmsg'] .= "<font color='yellow'>���ǤϤ���ޤ���</font>";
        } else {
            $_SESSION['s_sysmsg'] .= "<br><font color='yellow'>���ǤϤ���ޤ���</font>";
        }
    }
} elseif ( isset($_POST['page_keep']) ) {               // ���ߤΥڡ�����ݻ�����
    $offset = $_SESSION['mate_offset'];
} elseif ( isset($_GET['page_keep']) ) {                // ���ߤΥڡ�����ݻ�����
    $offset = $_SESSION['mate_offset'];
} else {
    $_SESSION['mate_offset'] = 0;                            // ���ξ��ϣ��ǽ����
}
$offset = $_SESSION['mate_offset'];

////////////// ��ʬ�Υݥ��ȥǡ���������å�
if (isset($_POST['plan'])) {
    $plan = $_POST['plan'];
    $query = "select mate.assy_no                                               as �����ֹ�     -- 0
                    , mate.plan_no                                              as �ײ��ֹ�     -- 1
                    , trim(substr(item.midsc, 1, 32))                           as ����̾       -- 2
                    , asse.kanryou                                              as �ײ���       -- 3
                    , asse.kansei                                               as ������       -- 4
                    , mate.ext_price                                            as ������       -- 5
                    , mate.int_price                                            as �����       -- 6
                    , Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)
                                                                                AS ��Ω��       -- 7
                    , Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) + sum_price
                                                                                AS �������     -- 8
                from
                    material_cost_header as mate
                left outer join
                    assembly_schedule as asse
                using (plan_no)
                left outer join
                    miitem as item
                on (mate.assy_no=item.mipn)
                where plan_no like '{$plan}%' -- '%{$plan}%'
                order by �ײ��� DESC
                offset $offset limit " . PAGE;
    $res = array();
    if (($rows = getResultWithField3($query, $field, $res)) <= 0) {
        $_SESSION['s_sysmsg'] = "{$plan} ���Ǥ���Ͽ����Ƥ��ޤ���";
        unset($_POST['plan']);      // �Ȳ�μ¹Ԥ�ꥻ�å�
    } else {
        $num = count($field);       // �ե�����ɿ�����
        for ($r=0; $r<$rows; $r++) {
            $res[$r][2] = mb_convert_kana($res[$r][2], 'ka', 'EUC-JP');   // ���ѥ��ʤ�Ⱦ�ѥ��ʤإƥ���Ū�˥���С���
        }
    }
} else {
    $plan = '';
}

/////////// HTML Header ����Ϥ��ƥ���å��������
$menu->out_html_header();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<title><?php echo $menu->out_title() ?></title>
<?php echo $menu->out_site_java()?>
<?php echo $menu->out_css()?>

<!--    �ե��������ξ��
<script language='JavaScript' src='template.js?<?php echo $uniq ?>'></script>
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
    return true;
    /************************************
    if (obj.plan.value.length != 0) {
        if (obj.plan.value.length != 9) {
            alert("�����ֹ�η���ϣ���Ǥ���");
            obj.plan.focus();
            obj.plan.select();
            return false;
        } else {
            return true;
        }
    }
    alert('�����ֹ椬���Ϥ���Ƥ��ޤ���');
    obj.plan.focus();
    obj.plan.select();
    return false;
    ***********************************/
}

/* ������ϥե�����Υ�����Ȥ˥ե������������� */
function set_focus(){
    document.entry_form.plan.focus();      // ������ϥե����ब������ϥ����Ȥ򳰤�
    // document.entry_form.plan.select();
}
// -->
</script>

<!-- �������륷���ȤΥե��������򥳥��� HTML���� �����Ȥ�����Ҥ˽���ʤ��������
<link rel='stylesheet' href='template.css?<?php echo $uniq ?>' type='text/css' media='screen'>
-->

<style type="text/css">
<!--
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
.plan_font {
    font-size:      16pt;
    font-weight:    bold;
    text-align:     left;
    font-family:    monospace;
}
th {
    background-color:   blue;
    color:              yellow;
    font-size:          10pt;
    font-weight:        bold;
    font-family:        monospace;
}
a:hover {
    background-color:   blue;
    color:              white;
}
a {
    color:   blue;
}
.winbox {
    border-style:           solid;
    border-width:           1px;
    border-top-color:       #FFFFFF;
    border-left-color:      #FFFFFF;
    border-right-color:     #bdaa90;
    border-bottom-color:    #bdaa90;
    /* background-color:#d6d3ce; */
}
.winbox_field {
    border-style:           solid;
    border-width:           1px;
    border-top-color:       #bdaa90;
    border-left-color:      #bdaa90;
    border-right-color:     #FFFFFF;
    border-bottom-color:    #FFFFFF;
    /* background-color:#d6d3ce; */
}
-->
</style>
</head>
<body onLoad='set_focus()' style='overflow-y:hidden;'>
    <center>
<?php echo $menu->out_title_border()?>
        
        <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
        <table class='winbox_field' width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <form name='entry_form' method='post' action='<?php echo $menu->out_self() ?>' onSubmit='return chk_plan_entry(this)'>
                <tr>
                    <td class='winbox' nowrap align='center'>
                        <div class='caption_font'><?php echo $menu->out_caption() ?></div>
                    </td>
                    <td class='winbox' nowrap align='center'>
                        <input class='plan_font' type='text' name='plan' value='<?php echo $plan ?>' size='8' maxlength='8'>
                    </td>
                    <td class='winbox' nowrap align='center'>
                        <div class='pt10'>
                            <!-- <input class='pt11b' type='submit' name='conf' value='�¹�'> -->
                            �ֹ��ʬ�����ϰϤ����ϸ�Enter�򲡤��ȥ��󥯥��󥿥륵�������ޤ���
                        </div>
                    </td>
                </tr>
            </form>
        </table>
            </td></tr>
        </table> <!----------------- ���ߡ�End ------------------>
        
        <?php if (isset($_POST['plan'])) { ?>
        
        <!----------------- ������ ���� ���� �Υե����� ---------------->
        <table width='100%' border='0' cellspacing='0' cellpadding='0'>
            <form name='page_form' method='post' action='<?php echo $menu->out_self() ?>'>
                <tr>
                    <td align='left'>
                        <table align='left' border='3' cellspacing='0' cellpadding='0'>
                            <td align='left'>
                                <input class='pt10b' type='submit' name='backward' value='����'>
                            </td>
                        </table>
                    </td>
                    <td nowrap align='center' class='caption_font'>
                    </td>
                    <td align='right'>
                        <table align='right' border='3' cellspacing='0' cellpadding='0'>
                            <td align='right'>
                                <input class='pt10b' type='submit' name='forward' value='����'>
                            </td>
                        </table>
                    </td>
                </tr>
            </form>
        </table>
        
        <!--------------- ����������ʸ��ɽ��ɽ������ -------------------->
        <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
        <table class='winbox_field' width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <thead>
                <!-- �ơ��֥� �إå�����ɽ�� -->
                <tr>
                    <th class='winbox' nowrap width='10'>No.</th>        <!-- �ԥʥ�С���ɽ�� -->
                <?php
                for ($i=0; $i<$num; $i++) {             // �ե�����ɿ�ʬ���֤�
                    echo "<th class='winbox' nowrap>{$field[$i]}</th>\n";
                }
                ?>
                </tr>
            </thead>
            <tfoot>
                <!-- ���ߤϥեå����ϲ���ʤ� -->
            </tfoot>
            <tbody>
                <?php
                for ($r=0; $r<$rows; $r++) {
                    $recNo = ($offset + $r);
                    if ($session->get_local('recNo') == $recNo) {
                        echo "<tr style='background-color:#ffffc6;'>\n";
                    } else {
                        echo "<tr>\n";
                    }
                    echo "    <td class='winbox' nowrap align='right'><div class='pt10b'>", ($r + $offset + 1), "</div></td>    <!-- �ԥʥ�С���ɽ�� -->\n";
                    for ($i=0; $i<$num; $i++) {         // �쥳���ɿ�ʬ���֤�
                        // <!--  bgcolor='#ffffc6' �������� --> 
                        switch ($i) {
                        case 0:     // �����ֹ�
                            echo "<td class='winbox' nowrap align='center'><div class='pt9'>{$res[$r][$i]}</div></td>\n";
                            break;
                        case 1:     // �ײ��ֹ�
                            echo "<td class='winbox' nowrap align='center'><div class='pt9'>{$res[$r][$i]}</div></td>\n";
                            break;
                        case 2:     // ����̾
                            echo "<td class='winbox' nowrap width='270' align='left'><div class='pt10'>{$res[$r][$i]}</div></td>\n";
                            break;
                        case 3:     // �ײ���
                            echo "<td class='winbox' nowrap align='center'><div class='pt9'>", format_date($res[$r][$i]), "</div></td>\n";
                            break;
                        case 4:     // ������
                            echo "<td class='winbox' nowrap width='45' align='right'><div class='pt9'>", number_format($res[$r][$i], 0), "</div></td>\n";
                            break;
                        case 5:     // ������
                            echo "<td class='winbox' nowrap width='60' align='right'><div class='pt9'>", number_format($res[$r][$i], 2), "</div></td>\n";
                            break;
                        case 6:     // �����
                            echo "<td class='winbox' nowrap width='60' align='right'><div class='pt9'>", number_format($res[$r][$i], 2), "</div></td>\n";
                            break;
                        case 7:     // ��Ω��
                            echo "<td class='winbox' nowrap width='60' align='right'><div class='pt9'>", number_format($res[$r][$i], 2), "</div></td>\n";
                            break;
                        case 8:     // �������
                            if ($res[$r][$i] == 0) {
                                echo "<td class='winbox' nowrap width='60' align='right'><div class='pt9'>-</div></td>\n";
                            } else {
                                echo "<td class='winbox' nowrap width='60' align='right'><div class='pt9'><a href='JavaScript:baseJS.Ajax(\"{$menu->out_self()}?recNo={$recNo}&{$uniq}\");location.replace(\"", $menu->out_action('�����������'),
                                        "?plan_no=", urlencode("{$res[$r][1]}"), "&assy_no=", urlencode("{$res[$r][0]}"), "&{$uniq}",
                                        "\")' target='application' style='text-decoration:none;'>", number_format($res[$r][$i], 2), "</a></div></td>\n";
                            }
                            break;
                        default:    // ����¾
                            echo "<td class='winbox' nowrap align='center'><div class='pt9'>{$res[$r][$i]}</div></td>\n";
                        }
                        // <!-- ����ץ�<td rowspan='2' colspan='3' width='200' align='center' class='pt10b' bgcolor='#ffffc6'>  </td> -->
                    }
                    echo "</tr>\n";
                }
                ?>
            </TBODY>
        </table>
            </td></tr>
        </table> <!----------------- ���ߡ�End ------------------>
        <?php } ?>
        
    </center>
</body>
<?php echo $menu->out_alert_java()?>
</html>
<?php
ob_end_flush();                 // ���ϥХåե���gzip���� END
?>
