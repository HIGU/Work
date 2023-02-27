<?php
//////////////////////////////////////////////////////////////////////////////
// ñ������Ȳ�(�ǿ�ñ����ޤ�)                                             //
// Copyright (C) 2004-2016 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2004/05/17 Created   parts_cost_view.php                                 //
// 2004/05/24 �ե�����ʳ�����ƤФ줿�����н��cost_page�򥻥å������Ͽ //
// 2004/05/27 ñ����ӤΥ��å���ͭ���ٵ�ˤ�������Ȥι绻���б�        //
// 2004/06/01 GET & POST �ǡ����μ����Υ����å����å��� page_keep ���ɲ�  //
//            �꥿���󥢥ɥ쥹����ˤ� page_keep �Υ����å����ɲ�           //
// 2004/06/03 ORDER BY �� lot_no ASC ���ɲ� lot_cost��tmp1_cost,tmp2_cost�� //
// 2004/12/03 ��Ͽ�ֹ����ϥ�å���˥ȡ�����ñ�����ɲ� �ǥ�������������α //
// 2005/01/11 MenuHeader class ����Ѥ��ƶ��̥�˥塼���ڤ�ǧ���������ѹ�   //
// 2005/01/12 ñ�����򤬤ʤ����������˶���Ū��?material=1�ѥ�᡼���ɲ�   //
// 2005/01/13 ��Ͽ�������������ʲ��ξ���ɲ� cost_page=25��100���ѹ�      //
// 2005/01/14 ȯ���褬��ݤ�ȯ�����Ʊ��������ɲ� Ʊ�������å���绻���ɲ� //
//                                      �绻�λ��ο��� yellow �� #ffffc6 �� //
// 2005/03/03 ��Ͽ�ֹ椬�㤦�������å������ֹ���ղä��� $no�ɲ�            //
// 2006/02/02 ������٤����������ξ��κ�����(����ñ��)�Ȳ���ɲ� ��ǧ�� //
//            ����Ͽ�ֹ椬���ꤵ��ƥ�åȤ�1�ξ��ϥޡ��������դ���       //
// 2006/06/22 $menu->set_retPOST('material', '1')�����ŤˤʤäƤ���Τ��� //
// 2007/06/09 noMenu �б��� php�Υ��硼�ȥ��åȥ�����侩�������ѹ�         //
// 2007/09/03 �Ť�$_SESSION['offset']��¾�ȶ��礹�뤿��$session->add_local  //
//            �Ĥ��Ǥ�$_POST/$_GET �� $_REQUEST�ء����ǤΥޡ����б�&������//
// 2013/04/09 ���Ϲ��������ݽ��פ��б�                               ��ë //
// 2016/01/29 ��ݼ��Ӥ���ξȲ�ǡ��ƹ��ܤ��ݻ�����ʤ��ä��Τ���   ��ë //
//            �����������פ���ξȲ���б�                             ��ë //
// 2016/08/08 mouseover���ɲ�                                          ��ë //
// 2020/07/29 $sei_no GetRegNo()���ɲ�                                 waki //
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
// �ʲ��Ϥޤ����Ѥ��Ƥ��ʤ�
if (isset($_REQUEST['recNo'])) {
    $session->add_local('recNo', $_REQUEST['recNo']);
    exit();
}
access_log();                               // Script Name �ϼ�ư����

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader(0);                  // ǧ�ڥ����å�0=���̰ʾ� �����=TOP_MENU �����ȥ�̤����

////////////// ����������
$menu->set_site(30, 14);                    // site_index=30(������˥塼) site_id=14(ñ������Ȳ�)
////////////// �꥿���󥢥ɥ쥹����
// $menu->set_RetUrl(INDUST_MENU);             // �̾�ϻ��ꤹ��ɬ�פϤʤ�
//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title('ñ �� �� �� �� �� ��');
//////////// �ƽ����action̾�ȥ��ɥ쥹����
// $menu->set_action('ñ������Ȳ�',   INDUST . 'parts/parts_cost_view.php');
//////////// �꥿�������GET�ǡ�������
$menu->set_retGET('page_keep', '1');
//////////// �꥿�������POST�ǡ�������
// if (isset($_REQUEST['material'])) {
//    $menu->set_retPOST('material', $_REQUEST['material']);
//}
//////////// ���ǡ������������뤿��̵�����դ���
$menu->set_retPOST('material', '1');

//////////// noMenu(Windowɽ��)�б��Τ����ɲ�
if (isset($_REQUEST['noMenu'])) $noMenu = $_REQUEST['noMenu']; else $noMenu = '';

//////////// JavaScript Stylesheet File ��ɬ���ɤ߹��ޤ���
$uniq = uniqid('target');

//////////// GET & POST �ǡ����μ���
if ( !(isset($_REQUEST['forward']) || isset($_REQUEST['backward']) || isset($_REQUEST['page_keep'])) ) {   // ���ǡ����ǥܥ���Υ����å�
    if (isset($_REQUEST['parts_no'])) {
        $parts_no = $_REQUEST['parts_no'];
        $_SESSION['cost_parts_no'] = $parts_no;
    } elseif (isset($_REQUEST['parts_no'])) {
        $parts_no = $_REQUEST['parts_no'];
        $_SESSION['cost_parts_no'] = $parts_no;
    } else {
        $_SESSION['s_sysmsg'] .= '���ʤ����ꤵ��Ƥ��ޤ���';
        header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // ľ���θƽи������
        exit();
    }
    if (isset($_REQUEST['lot_cost'])) {
        $lot_cost = $_REQUEST['lot_cost'];
        $_SESSION['cost_lot_cost'] = $lot_cost;
    } elseif (isset($_REQUEST['lot_cost'])) {
        $lot_cost = $_REQUEST['lot_cost'];
        $_SESSION['cost_lot_cost'] = $lot_cost;
    } else {
        $lot_cost = 0;      // ���ꤵ��Ƥ��ʤ����� 0�ǽ����
    }
    ///// ��Ͽ���Υ����å��Ѥ��ɲ�
    if (isset($_REQUEST['uke_date'])) {
        $uke_date = $_REQUEST['uke_date'];
        $_SESSION['cost_uke_date'] = $uke_date;
    } else {
        $uke_date = 99999999;   // ���ꤵ��Ƥ��ʤ����Ϻǿ�ñ���ˤ���
        $_SESSION['cost_uke_date'] = $uke_date;
    }
    ///// ȯ����Υ����å��Ѥ��ɲ�
    if (isset($_REQUEST['vendor'])) {
        $vendor = $_REQUEST['vendor'];
        $_SESSION['cost_vendor'] = $vendor;
    } else {
        $vendor = '';           // ���ꤵ��Ƥ��ʤ����Ϻǿ�ñ���ˤ���
        $_SESSION['cost_vendor'] = $vendor;
    }
    ///// ��¤�ֹ�Υ����å��Ѥ��ɲ�    // 2020.07.29 add waki
    if (isset($_REQUEST['sei_no'])) {
        $sei_no = $_REQUEST['sei_no'];
        $_SESSION['cost_sei_no'] = $sei_no;
    } else {
        $sei_no = 0;   // ���ꤵ��Ƥ��ʤ�����0
        $_SESSION['cost_sei_no'] = $sei_no;
    }
    if (isset($_REQUEST['cost_page'])) {
        $cost_page = $_REQUEST['cost_page'];
        $_SESSION['cost_page'] = $cost_page;
    } elseif (isset($_REQUEST['cost_page'])) {
        $cost_page = $_REQUEST['cost_page'];
        $_SESSION['cost_page'] = $cost_page;
    } else {
        $cost_page = 100;   // ���ꤵ��Ƥ��ʤ����� 25��100(2004/01/13�ѹ�)�ǽ����
        $_SESSION['cost_page'] = $cost_page;    // �ե�����ʳ�����ƤФ줿�����н�
    }
    ///// ��������ñ����Ͽ�Ȳ��Ѥ��ɲ�
    if (isset($_REQUEST['reg_no'])) {
        $reg_no = $_REQUEST['reg_no'];
        $_SESSION['cost_reg_no'] = $reg_no;
    } else {
        $reg_no = (-1);     // ���ꤵ��Ƥ��ʤ�����̵���ˤ���
        $_SESSION['cost_reg_no'] = $reg_no;
    }
} else {
    $parts_no  = $_SESSION['cost_parts_no'];
    $lot_cost  = $_SESSION['cost_lot_cost'];
    $cost_page = $_SESSION['cost_page'];
    $uke_date  = $_SESSION['cost_uke_date'];
    $vendor    = $_SESSION['cost_vendor'];
    $sei_no    = $_SESSION['cost_sei_no']; // 2020.07.29 add waki
    $reg_no    = $_SESSION['cost_reg_no'];
}
if(isset($_REQUEST['paya_code'])) {
    if (isset($_REQUEST['paya_code'])) {
        $paya_code = $_REQUEST['paya_code'];
        $_SESSION['paya_code'] = $paya_code;
    } elseif (isset($_REQUEST['paya_code'])) {
        $paya_code = $_REQUEST['paya_code'];
        $_SESSION['paya_code'] = $paya_code;
    }
    if (isset($_REQUEST['payable_code'])) {
        $payable_code = $_REQUEST['payable_code'];
        $_SESSION['payable_code'] = $payable_code;
    } elseif (isset($_REQUEST['payable_code'])) {
        $payable_code = $_REQUEST['payable_code'];
        $_SESSION['payable_code'] = $payable_code;
    }
    if (isset($_REQUEST['payable_s_ym'])) {
        $payable_s_ym = $_REQUEST['payable_s_ym'];
        $_SESSION['payable_s_ym'] = $payable_s_ym;
    } elseif (isset($_REQUEST['payable_s_ym'])) {
        $payable_s_ym = $_REQUEST['payable_s_ym'];
        $_SESSION['payable_s_ym'] = $payable_s_ym;
    }
    if (isset($_REQUEST['payable_e_ym'])) {
        $payable_e_ym = $_REQUEST['payable_e_ym'];
        $_SESSION['payable_e_ym'] = $payable_e_ym;
    } elseif (isset($_REQUEST['payable_e_ym'])) {
        $payable_e_ym = $_REQUEST['payable_e_ym'];
        $_SESSION['payable_e_ym'] = $payable_e_ym;
    }
    if (isset($_REQUEST['payable_div'])) {
        $payable_div = $_REQUEST['payable_div'];
        $_SESSION['payable_div'] = $payable_div;
    } elseif (isset($_REQUEST['payable_div'])) {
        $payable_div = $_REQUEST['payable_div'];
        $_SESSION['payable_div'] = $payable_div;
    }
    if (isset($_REQUEST['payable_vendor'])) {
        $payable_vendor = $_REQUEST['payable_vendor'];
        $_SESSION['payable_vendor'] = $payable_vendor;
    } elseif (isset($_REQUEST['payable_vendor'])) {
        $payable_vendor = $_REQUEST['payable_vendor'];
        $_SESSION['payable_vendor'] = $payable_vendor;
    }
}
if (isset($_REQUEST['str_date'])) {
    $str_date = $_REQUEST['str_date'];
    $_SESSION['str_date'] = $str_date;
    $_SESSION['paya_strdate'] = $str_date;
    $session->add('str_date', $str_date);
} elseif (isset($_SESSION['paya_strdate'])) {
    $str_date = $_SESSION['paya_strdate'];
    $_SESSION['str_date'] = $str_date;
    $session->add('str_date', $str_date);
} else {
    $year  = date('Y') - 5; // ��ǯ������
    $month = date('m');
    $str_date = $year . $month . '01';
}
if (isset($_REQUEST['end_date'])) {
    $end_date = $_REQUEST['end_date'];
    $_SESSION['end_date'] = $end_date;
    $_SESSION['paya_enddate'] = $end_date;
    $session->add('end_date', $end_date);
} elseif (isset($_SESSION['paya_enddate'])) {
    $end_date = $_SESSION['paya_enddate'];
    $_SESSION['end_date'] = $end_date;
    $session->add('end_date', $end_date);
} else {
    $end_date = '99999999';
}
if (isset($_REQUEST['kamoku'])) {
    $kamoku = $_REQUEST['kamoku'];
    $_SESSION['paya_kamoku'] = $kamoku;
    $session->add('kamoku', $kamoku);
} else {
    if (isset($_SESSION['paya_kamoku'])) {
        $kamoku = $_SESSION['paya_kamoku'];
        $session->add('kamoku', $kamoku);
    } else {
        $kamoku = '';
    }
}
//////////// ɽ�������
$query = "select midsc from miitem where mipn='{$parts_no}'";
if (getUniResult($query, $name) <= 0) {
    $_SESSION['s_sysmsg'] .= '�ޥ�����̤��Ͽ';    // ������parts_cost_form.php�ǥޥ������Υ����å���Ԥ��褦���ѹ�ͽ��
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // ľ���θƽи������
    exit();
}
$menu->set_caption("�����ֹ桧{$parts_no}������̾��{$name}");

//////////// SQL ʸ�� where �����������
$search = sprintf("where parts_no='%s' and vendor!='88888'", $parts_no);
$search2 = sprintf("where p.parts_no='%s' and v.vendor!='88888'", $parts_no);

//////////// ���ǤιԿ�
define('PAGE', $cost_page);

//////////// ��ץ쥳���ɿ�����     (�оݥơ��֥�κ������ڡ�������˻���)
$query = sprintf('select count(*) from parts_cost_history %s', $search);
if ( getUniResult($query, $maxrows) <= 0) {         // $maxrows �μ���
    $_SESSION['s_sysmsg'] .= '��ץ쥳���ɿ��μ����˼���<br>DB����³���ǧ��';  // .= ��å��������ɲä���
}
//////////// �ڡ������ե��å�����(offset�ϻ��Ѥ������̾�����ѹ� �㡧sales_offset)
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
} elseif ( isset($_REQUEST['page_keep']) ) {                // ���ߤΥڡ�����ݻ����� GET�����
    $offset = $offset;
} else {
    $offset = 0;                            // ���ξ��ϣ��ǽ����
}
$session->add_local('offset', $offset);

//////////// ɽ�����Υǡ���ɽ���ѤΥ���ץ� Query & �����
$query = sprintf("
        select
            p.as_regdate            as ��Ͽ��,                  -- 0
            p.reg_no                as \"��ϿNo\",              -- 1
            p.vendor                as ������,                  -- 2
            v.name                  as ȯ����̾,                -- 3
            CASE
                WHEN p.mtl_cond = '1' THEN '����'
                WHEN p.mtl_cond = '2' THEN 'ͭ��'
                WHEN p.mtl_cond = '3' THEN '̵��'
            END                     as ���,                    -- 4
            CASE
                WHEN p.kubun = '1' THEN '��³'
                WHEN p.kubun = '2' THEN '����'
                WHEN p.kubun = '3' THEN '����'
            END                     as ��ʬ,                    -- 5
            p.pro_no                as ����,                    -- 6
            p.pro_mark              as ����,                    -- 7
            p.lot_cost              as ñ��,                    -- 8
            p.lot_str               as \"LOT����\",             -- 9
            p.lot_end               as \"LOT��λ\",             --10
            p.lot_no                as \"LOT�ֹ�\"              --11
        from
            parts_cost_history as p
        left outer join
            vendor_master as v
        using (vendor)
        %s      -- ������ where��� and �������Ǥ���
        ORDER BY as_regdate DESC, reg_no DESC, lot_no ASC, pro_no ASC
        offset %d limit %d
    ", $search2, $offset, PAGE);       // ���� $search �Ǹ���
$res   = array();
$field = array();
if (($rows = getResultWithField2($query, $field, $res)) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("<font color='yellow'>�����ֹ�:%s <br>ñ�����򤬤���ޤ���</font>", $parts_no);
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl() . '?material=1');    // ľ���θƽи������
    exit();
} else {
    $num = count($field);       // �ե�����ɿ�����
    $set_rows  = (-1);          // �����(-1=���åȤ��ʤ�����)
    $set_tanka = (-1);          // ͭ���ˤ��绻���б�
    if ($lot_cost > 0) {        // ���ߤϺǽ���Ǥˤ����б����Ƥ��ʤ���Ƥ�梪�б��Ѥ�(&& $offset == 0���������б�)2007/09/03
        // 2020.07.29 add waki ----------------------------------------------->
        $register_no = GetRegNo($parts_no, $sei_no);
        for( $i=0; $i<$rows; $i++ ) {
            if( $res[$i][1] == $register_no ) {
                break;
            }
        }
        // <-------------------------------------------------------------------
        for (; $i<$rows; $i++) {
//        for ($i=0; $i<$rows; $i++) {
            ///// ñ����Ʊ������ $res[$i][8]����� (��Ͽ�������������ʲ��ξ���ɲ� 2005/01/13)
            if ( ($res[$i][8] == $lot_cost) && ($res[$i][0] <= $uke_date) ) {
                ///// ȯ���褬��äƤ��뤫���ϻ��ꤵ��Ƥ��ʤ����̵�뤹��(2005/01/14)
                if ( ($res[$i][2] == $vendor) || ($vendor == '') ) {
                    $set_rows = $i;     // ���פ����쥳���ɤ򥻥åȤ���
                    break;
                }
            } else {            // ͭ���ٵ�ˤ��绻���б�
                if ($res[$i][7] == 'NK' || $res[$i][7] == 'MT') {
                    if ( (isset($res[$i+1][4])) && $res[$i+1][4] == 'ͭ��') {   // ͭ�������å�(����)
                        ///// ifʸ�η���碌�Τ���ʲ�����ޤ� float�ξ��� Uround()����Ѥ���ɬ�פ�����
                        $tmp1_cost = number_format($res[$i][8] + $res[$i+1][8], 2);
                        $tmp2_cost = number_format($lot_cost, 2);
                            ///// �ʲ��ϲ��٤�äƤ⤦�ޤ������ʤ����ᥳ����(�㡧26.40 NG)
                            // $tmp1_cost = (double)($res[$i][8] + $res[$i+1][8]);
                            // $tmp2_cost = (double)($lot_cost);
                            // var_dump($tmp_cost, $lot_cost);
                        ///// �绻ñ����Ʊ������ (��Ͽ�������������ʲ��ξ���ɲ� 2005/01/13)
                        if ( ($tmp1_cost == $tmp2_cost) && ($res[$i][0] <= $uke_date) ) {
                            ///// ȯ���褬��äƤ��뤫���ϻ��ꤵ��Ƥ��ʤ����̵�뤹��(2005/01/14)
                            if ( ($res[$i+1][2] == $vendor) || ($vendor == '') ) {
                                $set_rows  = ($i+1);    // +1�����
                                $set_tanka = $i;
                                break;
                            }
                        }
                    }
                }
            }
        }
    }
    $res[-1][0] = '';   // ���ߡ�
    $res[-1][1] = '';   // ���ߡ�
}

// ñ���ѹ��ֹ�(��Ͽ�ֹ�) ����
function GetRegNo( $parts_no, $sei_no )
{
    $query = $field = $res = array();

    $query = "
                 SELECT   *
                 FROM     order_plan
                 WHERE    parts_no = '$parts_no' AND sei_no = $sei_no
                 ORDER BY regdate ASC LIMIT 1
             ";
    $res  = array();
    $rows = getResultWithField2( $query, $field, $res );

    if( $rows < 1 ) {
        return "";
    }

    return $res[0][15];
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
<?php echo $menu->out_site_java() ?>
<?php echo $menu->out_css() ?>

<!--    �ե��������ξ��
<script language='JavaScript' src='template.js?<?php echo $uniq ?>'>
</script>
-->

<script language="JavaScript">
<!--
/* ����ʸ�����������ɤ��������å�(ASCII code check) */
function isDigit(str) {
    var len = str.length;
    var c;
    for (i=0; i<len; i++) {
        c = str.charAt(i);
        if ((c < '0') || (c > '9')) {
            return false;
        }
    }
    return true;
}

/* ����ʸ��������ե��٥åȤ��ɤ��������å� isDigit()�ε� */
function isABC(str) {
    // var str = str.toUpperCase();    // ɬ�פ˱�������ʸ�����Ѵ�
    var len = str.length;
    var c;
    for (i=0; i<len; i++) {
        c = str.charAt(i);
        if ((c < 'A') || (c > 'Z')) {
            if (c == ' ') continue; // ���ڡ�����OK
            return false;
        }
    }
    return true;
}

/* ����ʸ�����������ɤ��������å� �������б� */
function isDigitDot(str) {
    var len = str.length;
    var c;
    var cnt_dot = 0;
    for (i=0; i<len; i++) {
        c = str.charAt(i);
        if (c == '.') {
            if (cnt_dot == 0) {     // 1���ܤ������å�
                cnt_dot++;
            } else {
                return false;       // 2���ܤ� false
            }
        } else {
            if (('0' > c) || (c > '9')) {
                return false;
            }
        }
    }
    return true;
}

/* ������ϥե�����Υ�����Ȥ˥ե������������� */
function set_focus(){
    // document.body.focus();   // F2/F12������ͭ���������б�
    // document.mhForm.backwardStack.focus();  // �嵭��IE�ΤߤΤ���NN�б�
    // document.form_name.element_name.focus();      // ������ϥե����ब������ϥ����Ȥ򳰤�
    // document.form_name.element_name.select();
}
// -->
</script>

<!-- �������륷���ȤΥե��������򥳥��� HTML���� �����Ȥ�����Ҥ˽���ʤ��������
<link rel='stylesheet' href='<?php echo MENU_FORM . '?' . $uniq ?>' type='text/css' media='screen'>
 -->

<style type="text/css">
<!--
.pt8 {
    font-size:   8pt;
    font-weight: normal;
    font-family: monospace;
}
.pt9 {
    font-size:   9pt;
    font-weight: normal;
    font-family: monospace;
}
.pt10 {
    font-size:   10pt;
    font-weight: normal;
    font-family: monospace;
}
.pt10b {
    font-size:   10pt;
    font-weight: bold;
    font-family: monospace;
}
.pt11b {
    font-size:   11pt;
    font-weight: bold;
    font-family: monospace;
}
.pt12b {
    font-size:   12pt;
    font-weight: bold;
    font-family: monospace;
}
th {
    background-color: blue;
    color:            yellow;
    font-size:        10pt;
    font-weight:      bold;
    font-family:      monospace;
}
.winbox {
    border-style: solid;
    border-width: 1px;
    border-top-color:       #FFFFFF;
    border-left-color:      #FFFFFF;
    border-right-color:     #999999;
    border-bottom-color:    #999999;
    /* background-color:#d6d3ce; */
}
.winboxr {
    border-style: solid;
    border-width: 1px;
    border-top-color:       #FFFFFF;
    border-left-color:      #FFFFFF;
    border-right-color:     #999999;
    border-bottom-color:    #999999;
    color:                  red;
    /* background-color:#d6d3ce; */
}
.winbox_field {
    border-style: solid;
    border-width: 1px;
    border-top-color:       #999999;
    border-left-color:      #999999;
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
-->
</style>
</head>
<body class='margin0' onLoad='set_focus()'>
    <center>
<?php if ($noMenu != 'yes') { ?>
<?php echo $menu->out_title_border() ?>
        
        <!----------------- ������ ���� ���� �Υե����� ---------------->
        <table width='100%' cellspacing="0" cellpadding="0" border='0'>
            <form name='page_form' method='post' action='<?php echo $menu->out_self(), '#mark' ?>'>
                <tr>
                    <td align='left'>
                        <table align='left' border='3' cellspacing='0' cellpadding='0'>
                            <td align='left'>
                                <input class='pt10b' type='submit' name='backward' value='����'>
                            </td>
                        </table>
                    </td>
                    <td align='center' class='caption_font'>
                        <?php echo $menu->out_caption(), "\n" ?>
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
<?php } else { ?>
        <div>
            <span class='caption_font'><?php echo $menu->out_caption() ?></span>
            &nbsp;
            <input type='button' name='closeButton' value='�Ĥ���' onclick='window.close();' class='pt11b'>
        </div>
<?php } ?>
        
        <!--------------- ����������ʸ��ɽ��ɽ������ -------------------->
        <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
        <table class='winbox_field' width='100%' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='3'>
            <thead>
                <!-- �ơ��֥� �إå�����ɽ�� -->
                <tr>
                    <th class='winbox' nowrap width='10'>No.</th>        <!-- �ԥʥ�С���ɽ�� -->
                <?php
                for ($i=0; $i<$num; $i++) {             // �ե�����ɿ�ʬ���֤�
                ?>
                    <th class='winbox' nowrap><?php echo $field[$i] ?></th>
                <?php
                }
                ?>
                </tr>
            </thead>
            <tfoot>
                <!-- ���ߤϥեå����ϲ���ʤ� -->
            </tfoot>
            <tbody>
                <?php
                $no = 1;    // ��Ͽ�ֹ���ι��ֹ������
                for ($r=0; $r<$rows; $r++) {
                    if ($set_rows == $r) {
                        echo "<tr style='background-color:#ffffc6'><a name='mark'></a>\n";
                    } else {
                        echo "<tr onMouseOver=\"style.background='#ceffce'\" onMouseOut=\"style.background='#d6d3ce'\">\n";
                    }
                    if ($res[$r][1] != $res[$r-1][1]) { // ��Ͽ�ֹ椬�㤦�������å������ֹ���ղä���
                        echo "    <td class='winbox' nowrap align='right'><div class='pt10b'>{$no}</div></td>    <!-- �ԥʥ�С���ɽ�� -->\n";
                        $no += 1;
                    } else {
                        echo "    <td class='winbox' nowrap align='right'><div class='pt10b'>&nbsp;</div></td>    <!-- �ԥʥ�С���ɽ�� -->\n";
                    }
                    for ($i=0; $i<$num; $i++) {         // �쥳���ɿ�ʬ���֤�
                        // <!--  bgcolor='#ffffc6' �������� --> 
                        switch ($i) {
                        case 0:     // ��Ͽ��
                            if ($res[$r][$i] != $res[$r-1][$i]) {
                                echo "<td class='winbox' nowrap align='center'><div class='pt9'>" . format_date($res[$r][$i]) . "</div></td>\n";
                            } else {
                                echo "<td class='winbox' nowrap align='center'><div class='pt9'>��</div></td>\n";
                            }
                            break;
                        case 1:     // ��Ͽ�ֹ�
                            if ($res[$r][$i] != $res[$r-1][$i]) {
                                echo "<td class='winbox' nowrap align='right'><div class='pt9'>" . $res[$r][$i] . "</div></td>\n";
                            } else {
                                echo "<td class='winbox' nowrap align='right'><div class='pt9'>��</div></td>\n";
                            }
                            break;
                        case 3:     // ȯ����̾
                            echo "<td class='winbox' nowrap align='left'><div class='pt9'>" . $res[$r][$i] . "</div></td>\n";
                            break;
                        case 8:     // ñ��
                            if ($set_tanka == $r) {
                                echo "<td class='winbox' bgcolor='#ffffc6' width='70' nowrap align='right'><div class='pt10b'>" . number_format($res[$r][$i], 2) . "</div></td>\n";
                            } else {
                                echo "<td class='winbox' width='70' nowrap align='right'><div class='pt10b'>" . number_format($res[$r][$i], 2) . "</div></td>\n";
                            }
                            break;
                        case  9:    // ��åȳ���
                        case 10:    // ��åȽ�λ
                            echo "<td class='winbox' nowrap align='right'><div class='pt9'>" . number_format($res[$r][$i]) . "</div></td>\n";
                            break;
                        default:
                            echo "<td class='winbox' nowrap align='center'><div class='pt9'>" . $res[$r][$i] . "</div></td>\n";
                        }
                        // <!-- ����ץ�<td rowspan='2' colspan='3' width='200' align='center' class='pt10b' bgcolor='#ffffc6'>  </td> -->
                    }
                    echo "</tr>\n";
                    if (isset($res[$r+1][1])) {
                        // ��Ͽ�ֹ椫��å��ֹ椬�Ѥ�ä����ϥȡ�����ñ�����������
                        if ( ($res[$r][1] != $res[$r+1][1]) || ($res[$r][11] != $res[$r+1][11]) ) {
                            $query = "select sum(lot_cost) from parts_cost_history where reg_no={$res[$r][1]} AND parts_no='{$parts_no}' and lot_no={$res[$r][11]} and vendor!='88888'";
                            $sum_cost = '';     // �ȡ�����ñ���ν����
                            getUniResult($query, $sum_cost);
                            if ($res[$r][1] == $reg_no && $res[$r][11] == 1) {   // ��Ͽ�ֹ椬���ꤵ��ƥ�åȤ�1�ξ��ϥޡ��������դ���
                                echo "<tr style='background-color:#ffffc6'>\n";
                            } else {
                                echo "<tr onMouseOver=\"style.background='#ceffce'\" onMouseOut=\"style.background='#d6d3ce'\">\n";
                            }
                            $query = "select d.rate_sign from parts_rate_history as h left outer join rate_div_master as d ON h.rate_div=d.rate_div where h.reg_no={$res[$r][1]} AND h.parts_no='{$parts_no}'";
                            $rate_div = '';     // �ȡ�����ñ���ν����
                            getUniResult($query, $rate_div);
                            if($rate_div == '') {
                                $rate_div = '\\';       // �졼�ȶ�ʬ��Ͽ���ʤ���б�
                            }
                            echo "    <td class='winbox' nowrap align='right' colspan='9'><div class='pt10b'>�ȡ�����ñ��</div></td>\n";
                            echo "    <td class='winbox' nowrap align='right'><div class='pt10b'>", number_format($sum_cost, 2), "</div></td>\n";
                            if ($rate_div == '\\') {
                                echo "    <td class='winbox' nowrap align='center'><div class='pt10b'>", $rate_div, "</div></td>\n";
                            } else {
                                echo "    <td class='winboxr' nowrap align='center'><div class='pt10b'>", $rate_div, "</div></td>\n";
                            }
                            echo "    <td class='winbox' nowrap align='right' colspan='2'><div class='pt9'>&nbsp;</div></td>\n";
                            echo "</tr>\n";
                        }
                    } else {    // �Ǹ�Υ쥳����
                        $query = "select sum(lot_cost) from parts_cost_history where reg_no={$res[$r][1]} AND parts_no='{$parts_no}' and lot_no={$res[$r][11]} and vendor!='88888'";
                        $sum_cost = '';     // �ȡ�����ñ���ν����
                        getUniResult($query, $sum_cost);
                        if ($res[$r][1] == $reg_no && $res[$r][11] == 1) {   // ��Ͽ�ֹ椬���ꤵ��ƥ�åȤ�1�ξ��ϥޡ��������դ���
                            echo "<tr style='background-color:#ffffc6'>\n";
                        } else {
                            echo "<tr>\n";
                        }
                        $query = "select d.rate_sign from parts_rate_history as h left outer join rate_div_master as d ON h.rate_div=d.rate_div where h.reg_no={$res[$r][1]} AND h.parts_no='{$parts_no}'";
                        $rate_div = '';     // �ȡ�����ñ���ν����
                        getUniResult($query, $rate_div);
                        if($rate_div == '') {
                            $rate_div = '\\';       // �졼�ȶ�ʬ��Ͽ���ʤ���б�
                        }
                        echo "    <td class='winbox' nowrap align='right' colspan='9'><div class='pt10b'>�ȡ�����ñ��</div></td>\n";
                        echo "    <td class='winbox' nowrap align='right'><div class='pt10b'>", number_format($sum_cost, 2), "</div></td>\n";
                        if ($rate_div == '\\') {
                            echo "    <td class='winbox' nowrap align='center'><div class='pt10b'>", $rate_div, "</div></td>\n";
                        } else {
                            echo "    <td class='winboxr' nowrap align='center'><div class='pt10b'>", $rate_div, "</div></td>\n";
                        }
                        echo "    <td class='winbox' nowrap align='right' colspan='2'><div class='pt9'>&nbsp;</div></td>\n";
                        echo "</tr>\n";
                    }
                }
                ?>
            </tbody>
        </table>
            </td></tr>
        </table> <!----------------- ���ߡ�End ------------------>
    </center>
</body>
<?php echo $menu->out_alert_java() ?>
</html>
<?php
ob_end_flush();                 // ���ϥХåե���gzip���� END
?>
