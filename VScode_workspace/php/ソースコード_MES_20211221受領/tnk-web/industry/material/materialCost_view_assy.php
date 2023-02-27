<?php
//////////////////////////////////////////////////////////////////////////////
// �������ξȲ�  ASSY(����)�ֹ������form������ɽ�Ȳ�                     //
// Copyright (C) 2004-2016 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2004/04/07 Created   metarialCost_view_assy.php                          //
// 2004/05/12 �����ȥ�˥塼ɽ������ɽ�� �ܥ����ɲ� menu_OnOff($script)�ɲ� //
// 2004/06/01 GET�ѥѥ�᡼������#�����ꤨ�뤿�� urlencode() ���ղä�����   //
// 2005/02/08 MenuHeader class ����Ѥ��ƶ��̥�˥塼���ڤ�ǧ���������ѹ�   //
// 2005/06/08 PostgreSQL8.0�� where assy_no like '%{$assy}%'��'{$assy}%'    //
//            ���ѹ������ Index Scan �ˤʤ뤿���ѹ�������                  //
// 2005/09/07 MenuON/Off��$_SESSION['material_max']����Notise�ˤʤ�Τ�@��  //
// 2006/10/06 order by �ײ��� DESC �� ORDER BY assy_no ASC, �ײ��� DESC ��  //
//            �ѹ����ڤӰ����˼�ư����ư��Ͽ�μ��̹����ɲ�                  //
// 2006/12/04 ���܎Î��̎ގ٤�material_cost_header��assembly_completion_history��//
// 2007/03/07 ����������Υ�󥯤򥯥�å�������ä����˹ԥޡ������ɲ� recNo//
//            php�Υ��硼�ȥ��åȤ����                                     //
// 2007/08/31 �����쥯�ȸƽФ��б��Τ���$_POST/$_GET �� $_REQUEST ���ѹ�    //
//            $_SESSION['mate_offset']��¾�ȶ��礹�뤿��$session->add_local //
// 2007/09/05 �ײ��ֹ椬���ꤵ��Ƥ�����˹ԥޡ�����ɽ���ɲ�              //
//            ��Ľ�������Τ�����Ū�� MenuHeader(-1) ���ѹ�              //
// 2007/09/14 �ǿ�����������Ͽ��󥯤��ɲ�                                //
// 2007/09/28 Uround(assy_time * assy_rate, 2) ��    ��ư����Ψ��׻����ɲ� //
//    Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) //
// 2016/08/08 mouseOver���ɲ�                                          ��ë //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL | E_STRICT);  // E_ALL='2047' debug ��
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug ��
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
$menu = new MenuHeader(-1);                  // ǧ�ڥ����å�0=���̰ʾ� �����=TOP_MENU �����ȥ�̤����

////////////// ����������
$menu->set_site(30, 23);                    // site_index=30(������˥塼) site_id=20(�������ξȲ� �ײ��ֹ�)
////////////// �꥿���󥢥ɥ쥹����
// $menu->set_RetUrl(INDUST_MENU);             // �̾�ϻ��ꤹ��ɬ�פϤʤ�
//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title('�� �� �� �� �� �� �� (ASSY�ֹ����)');
//////////// ɽ�������
$menu->set_caption('�����ֹ������');
//////////// �ƽ����action̾�ȥ��ɥ쥹����
$menu->set_action('�����������',   INDUST . 'material/materialCost_view.php');
$menu->set_action('�������assy��Ͽ',   INDUST . 'material/materialCost_entry_assy.php');

if (isset($_REQUEST['material'])) {     // ��������̤��Ͽ����θƽ��б�
    $menu->set_retGET('page_keep', $_REQUEST['material']);
    $material = '?material=1';
} else {
    // $material = ''; ����������Ѥˤ��뤿���˥ڡ��������פ�material=1 2007/08/31
    $menu->set_retGET('page_keep', 'on');
    $material = '?material=1';
}
//////////// JavaScript Stylesheet File ��ɬ���ɤ߹��ޤ���
$uniq = uniqid('target');

///// ¾�Υ��ץ꤫��ײ��ֹ����ǾȲ񤵤줿���˹ԥޡ�����ɽ��
if (isset($_REQUEST['plan_no'])) {
    $plan_no = $_REQUEST['plan_no'];
} else {
    $plan_no = '';
}
//////////// �����Υ��å����ǡ�����¸   ���ǡ����Ǥ�ڤ����뤿��
if (! (isset($_REQUEST['forward']) || isset($_REQUEST['backward']) || isset($_REQUEST['page_keep'])) ) {
    $session->add_local('recNo', '-1');         // 0�쥳���ɤǥޡ�����ɽ�����Ƥ��ޤ�������б�
    if (isset($_REQUEST['assy'])) {
        $assy = $_REQUEST['assy'];
        $query = "select count(*)
                from
                    -- material_cost_header
                    assembly_completion_history
                where assy_no like '{$assy}%'";
        if (getUniResult($query, $maxrows) <= 0) {
            $_SESSION['s_sysmsg'] = '��ץ쥳���ɿ��μ����˼���';
        } else {
            $_SESSION['material_max'] = $maxrows;
        }
        $_SESSION['mate_assy'] = $_REQUEST['assy'];
    }
} else {        // ���ǡ����ǡ�����¸ �λ���
    $maxrows = @$_SESSION['material_max'];       // ��ץ쥳���ɿ�������
    $_REQUEST['assy'] = @$_SESSION['mate_assy'];    // �ݥ��ȥǡ����򥨥ߥ�졼��
}

//////////// ���ǤιԿ�
if (isset($_SESSION['material_page'])) {
    define('PAGE', $_SESSION['material_page']);
} else {
    define('PAGE', 23);
}

//////////// �ڡ������ե��å�����
$offset = $session->get_local('offset');
if ($offset == '') $offset = 0;         // �����
if ( isset($_REQUEST['forward']) ) {                       // ���Ǥ������줿
    $offset += PAGE;
    if ($offset >= $maxrows) {
        $offset -= PAGE;
        if ($_SESSION['s_sysmsg'] == '') {
            $_SESSION['s_sysmsg'] .= "<font color='yellow'>���ǤϤ���ޤ���</font>";
        } else {
            $_SESSION['s_sysmsg'] .= "<br><font color='yellow'>���ǤϤ���ޤ���</font>";
        }
    }
} elseif ( isset($_REQUEST['backward']) ) {                // ���Ǥ������줿
    $offset -= PAGE;
    if ($offset < 0) {
        $offset = 0;
        if ($_SESSION['s_sysmsg'] == '') {
            $_SESSION['s_sysmsg'] .= "<font color='yellow'>���ǤϤ���ޤ���</font>";
        } else {
            $_SESSION['s_sysmsg'] .= "<br><font color='yellow'>���ǤϤ���ޤ���</font>";
        }
    }
} elseif ( isset($_REQUEST['page_keep']) ) {               // ���ߤΥڡ�����ݻ�����
    $offset = $offset;
} elseif ( isset($_REQUEST['page_keep']) ) {                // ���ߤΥڡ�����ݻ�����
    $offset = $offset;
} else {
    $offset = 0;                            // ���ξ��ϣ��ǽ����
}
$session->add_local('offset', $offset);

////////////// ��ʬ�Υݥ��ȥǡ���������å�
if (isset($_REQUEST['assy'])) {
    $assy = $_REQUEST['assy'];
    $query = "SELECT hist.assy_no                                               AS �����ֹ�     -- 0
                    , hist.plan_no                                              AS �ײ��ֹ�     -- 1
                    , trim(substr(item.midsc, 1, 21))                           AS ����̾       -- 2
                    , asse.kanryou                                              AS �ײ���       -- 3
                    , asse.kansei                                               AS ������       -- 4
                    , mate.ext_price                                            AS ������       -- 5
                    , mate.int_price                                            AS �����       -- 6
                    , Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)
                                                                                AS ��Ω��       -- 7
                    , Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) + sum_price
                                                                                AS �������     -- 8
                    , CASE
                        WHEN to_char(mate.regdate, 'HH24:MI:SS') = '00:00:00' THEN '��ư'
                        WHEN mate.plan_no IS NULL THEN '̤��Ͽ'
                        ELSE '��ư'
                      END                                                       AS ��Ͽ         -- 9
                    , to_char(hist.comp_date, 'FM9999/99/99')                   AS ������       -- 10
                    , to_char(hist.comp_pcs, 'FM99,999')                        AS ������       -- 11
                FROM
                    assembly_completion_history AS hist
                LEFT OUTER JOIN
                    material_cost_header AS mate USING(plan_no)
                LEFT OUTER JOIN
                    assembly_schedule AS asse USING(plan_no)
                LEFT OUTER JOIN
                    miitem AS item ON (hist.assy_no=item.mipn)
                WHERE hist.assy_no LIKE '{$assy}%' -- '%{$assy}%'
                ORDER BY hist.assy_no DESC, hist.comp_date DESC --�ײ��� DESC
                OFFSET {$offset} LIMIT " . PAGE;
    $res = array();
    if (($rows = getResultWithField3($query, $field, $res)) <= 0) {
        $_SESSION['s_sysmsg'] = "{$assy} ���Ǥ���Ͽ����Ƥ��ޤ���";
        unset($_REQUEST['assy']);      // �Ȳ�μ¹Ԥ�ꥻ�å�
    } else {
        $num = count($field);       // �ե�����ɿ�����
        for ($r=0; $r<$rows; $r++) {
            $res[$r][2] = mb_convert_kana($res[$r][2], 'ka', 'EUC-JP');   // ���ѥ��ʤ�Ⱦ�ѥ��ʤ�
            $res[$r][2] = mb_substr($res[$r][2], 0, 21);    // �ޥ���Х����б���Ⱦ�ѥ��ʥ١�����21ʸ���ˤ���
        }
    }
} else {
    $assy = '';
}

/////////// HTML Header ����Ϥ��ƥ���å��������
$menu->out_html_header();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv='Content-Style-Type' content='text/css'>
<meta http-equiv='Content-Script-Type' content='text/javascript'>
<title><?php echo $menu->out_title() ?></title>
<?php echo $menu->out_site_java()?>
<?php echo $menu->out_css()?>

<!--    �ե��������ξ��
<script language='JavaScript' src='template.js?<?php echo $uniq ?>'></script>
-->

<script type='text/javascript' language='JavaScript'>
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

function chk_assy_entry(obj) {
    obj.assy.value = obj.assy.value.toUpperCase();
    return true;
    /************************************
    if (obj.assy.value.length != 0) {
        if (obj.assy.value.length != 9) {
            alert("�����ֹ�η���ϣ���Ǥ���");
            obj.assy.focus();
            obj.assy.select();
            return false;
        } else {
            return true;
        }
    }
    alert('�����ֹ椬���Ϥ���Ƥ��ޤ���');
    obj.assy.focus();
    obj.assy.select();
    return false;
    ***********************************/
}

/* ������ϥե�����Υ�����Ȥ˥ե������������� */
function set_focus(){
    document.entry_form.assy.focus();      // ������ϥե����ब������ϥ����Ȥ򳰤�
    // document.entry_form.assy.select();
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
.pt9y {
    font-size:      9pt;
    font-weight:    normal;
    font-family:    monospace;
    color:          teal;
}
.pt9r {
    font-size:      9pt;
    font-weight:    normal;
    font-family:    monospace;
    color:          red;
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
.assy_font {
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
            <form name='entry_form' method='post' action='<?php echo $menu->out_self() ?>' onSubmit='return chk_assy_entry(this)'>
                <tr>
                    <td class='winbox' nowrap align='center'>
                        <div class='caption_font'><?php echo $menu->out_caption() ?></div>
                    </td>
                    <td class='winbox' nowrap align='center'>
                        <input class='assy_font' type='text' name='assy' value='<?php echo $assy ?>' size='9' maxlength='9'>
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
        
        <?php if (isset($_REQUEST['assy'])) { ?>
        
        <!----------------- ������ ���� ���� �Υե����� ---------------->
        <table width='100%' border='0' cellspacing='0' cellpadding='0'>
            <form name='page_form' method='post' action='<?php echo $menu->out_self(), $material ?>'>
                <tr>
                    <td align='left'>
                        <table align='left' border='3' cellspacing='0' cellpadding='0'>
                            <td align='left'>
                                <input class='pt10b' type='submit' name='backward' value='����'>
                            </td>
                        </table>
                    </td>
                    <td nowrap align='center' class='caption_font'>
                        <a href='<?php echo $menu->out_action('�������assy��Ͽ'), '?assy=', urlencode($assy);?>'>�ǿ�����������Ͽ</a>
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
                $res[-1][0] = ''; $res[-1][1] = ''; ///// ���ߡ�
                for ($r=0; $r<$rows; $r++) {
                    $recNo = ($offset + $r);
                    if ($session->get_local('recNo') == $recNo || $plan_no == $res[$r][1]) {
                        echo "<tr style='background-color:#ffffc6;'>\n";
                    } else {
                        echo "<tr onMouseOver=\"style.background='#ceffce'\" onMouseOut=\"style.background='#d6d3ce'\">\n";
                    }
                    echo "    <td class='winbox' nowrap align='right'><div class='pt10b'>", ($r + $offset + 1), "</div></td>    <!-- �ԥʥ�С���ɽ�� -->\n";
                    for ($i=0; $i<$num; $i++) {         // �쥳���ɿ�ʬ���֤�
                        // <!--  bgcolor='#ffffc6' �������� --> 
                        switch ($i) {
                        case 0:     // �����ֹ�
                            if ($res[$r-1][$i] == $res[$r][$i]) {
                                echo "<td class='winbox pt9' nowrap align='center'>��</td>\n";
                            } else {
                                echo "<td class='winbox pt9' nowrap align='center'>{$res[$r][$i]}</td>\n";
                            }
                            break;
                        case 1:     // �ײ��ֹ�
                            if ($res[$r-1][$i] == $res[$r][$i]) {
                                echo "<td class='winbox pt9' nowrap align='center'>��</td>\n";
                            } else {
                                echo "<td class='winbox pt9' nowrap align='center'>{$res[$r][$i]}</td>\n";
                            }
                            break;
                        case 2:     // ����̾
                            if ($res[$r-1][1] == $res[$r][1]) {
                                echo "<td class='winbox pt10' nowrap width='150' align='center'>��</td>\n";
                            } else {
                                echo "<td class='winbox pt10' nowrap width='150' align='left'>{$res[$r][$i]}</td>\n";
                            }
                            break;
                        case 3:     // �ײ���
                            if ($res[$r-1][1] == $res[$r][1]) {
                                echo "<td class='winbox pt9' nowrap align='center'>��</td>\n";
                            } else {
                                echo "<td class='winbox pt9' nowrap align='center'>", format_date($res[$r][$i]), "</td>\n";
                            }
                            break;
                        case 4:     // ������
                            if ($res[$r-1][1] == $res[$r][1]) {
                                echo "<td class='winbox pt9' nowrap width='45' align='center'>��</td>\n";
                            } else {
                                echo "<td class='winbox pt9' nowrap width='45' align='right'>", number_format($res[$r][$i], 0), "</td>\n";
                            }
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
                                echo "<td class='winbox' nowrap width='60' align='right'><div class='pt9'><a href='JavaScript:baseJS.Ajax(\"{$menu->out_self()}?recNo={$recNo}\");location.replace(\"", $menu->out_action('�����������'),
                                        "?plan_no=", urlencode("{$res[$r][1]}"), "&assy_no=", urlencode("{$res[$r][0]}"),
                                        "\")' target='application' style='text-decoration:none;'>", number_format($res[$r][$i], 2), "</a></div></td>\n";
                            }
                            break;
                        case 9:     // ��Ͽ
                            if ($res[$r][$i] == '��ư') {
                                echo "<td class='winbox pt9y' nowrap align='center'>{$res[$r][$i]}</td>\n";
                            } elseif($res[$r][$i] == '̤��Ͽ') {
                                echo "<td class='winbox pt9r' nowrap align='center'>{$res[$r][$i]}</td>\n";
                            } else {
                                echo "<td class='winbox pt9' nowrap align='center'>{$res[$r][$i]}</td>\n";
                            }
                            break;
                        case 10:    // ������
                            echo "<td class='winbox pt9' nowrap align='right'>{$res[$r][$i]}</td>\n";
                            break;
                        case 11:    // ������
                            echo "<td class='winbox pt9' nowrap align='right'>{$res[$r][$i]}</td>\n";
                            break;
                        default:    // ����¾
                            echo "<td class='winbox pt9' nowrap align='center'>{$res[$r][$i]}</td>\n";
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
