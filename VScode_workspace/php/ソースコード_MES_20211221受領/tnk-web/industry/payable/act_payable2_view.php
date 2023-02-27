<?php
//////////////////////////////////////////////////////////////////////////////
// ��ݥҥ��ȥ�ξȲ� �� �����å���  ������ UKWLIB/W#HIBCTR                 //
// ���ɰ��� ���Ϲ�����ι�׶�۾Ȳ񤫤�Υ��                            //
// Copyright (C) 2013-2018 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2013/04/09 Created   act_payable2_view.php                               //
// 2018/01/29 ���ץ�����ɸ����ɲ�                                   ��ë //
// 2018/06/29 ¿�����T���ʹ������б�                                  ��ë //
// 2020/12/21 ¿�����T���ʹ������б���λ                              ��ë //
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
access_log();                               // Script Name �ϼ�ư����

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader(0);                  // ǧ�ڥ����å�0=���̰ʾ� �����=TOP_MENU �����ȥ�̤����

////////////// ����������
$menu->set_site(30, 10);                    // site_index=40(������˥塼) site_id=10(��ݼ���)
////////////// �꥿���󥢥ɥ쥹����
// $menu->set_RetUrl(INDUST_MENU);             // �̾�ϻ��ꤹ��ɬ�פϤʤ�
//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title('�� �� �� �� �� �� ��');
//////////// ɽ�������     ���Υ��å��ǽ������뤿�ᤳ���Ǥϻ��Ѥ��ʤ�
// $menu->set_caption('������ɬ�פʾ��������������򤷤Ʋ�������');
//////////// �ƽ����action̾�ȥ��ɥ쥹����
$menu->set_action('ñ������ɽ��',   INDUST . 'parts/parts_cost_view.php');

//////////// JavaScript Stylesheet File ��ɬ���ɤ߹��ޤ���
$uniq = uniqid('target');

$uke_no = '';
$current_script = $menu->out_self();
if($_SESSION['paya_code'] == 'on') {
    if (isset($_SESSION['payable_code'])) {
        $_REQUEST['payable_code'] = $_SESSION['payable_code'];
        $payable_code = $_SESSION['payable_code'];
    }
    if (isset($_SESSION['payable_s_ym'])) {
        $s_ym = $_SESSION['payable_s_ym'];
        $_SESSION['payable_s_ym'] = $s_ym;
    } else {
        $s_ym = date('Ym');
    }
    if (isset($_SESSION['payable_e_ym'])) {
        $e_ym = $_SESSION['payable_e_ym'];
        $_SESSION['payable_e_ym'] = $e_ym;
    } else {
        $e_ym = date('Ym');
    }
    if (isset($_SESSION['payable_div'])) {
        if ($_SESSION['payable_div']=='A') {
            $_SESSION['payable_div'] = ' ';
        }
        $sum_div = $_SESSION['payable_div'];
        $_SESSION['payable_div'] = $sum_div;
    } else {
        if (isset($_SESSION['payable_div'])) {
            if ($_SESSION['payable_div']=='A') {
                $_SESSION['payable_div'] = ' ';
            }
            $sum_div = $_SESSION['payable_div'];
        } else {
            $sum_div = ' ';
        }
    }
    if (isset($_SESSION['payable_vendor'])) {
        $sum_vendor = $_SESSION['payable_vendor'];
        $_SESSION['payable_vendor'] = $sum_vendor;
    } else {
        if (isset($_SESSION['payable_vendor'])) {
            $sum_vendor = $_SESSION['payable_vendor'];
        } else {
            $sum_vendor = '';
        }
    }
    if (isset($_SESSION['paya_page'])) {
        $paya_page = $_SESSION['paya_page'];
        $_SESSION['payable_page'] = $paya_page;
    } else {
        if (isset($_SESSION['payable_page'])) {
            $paya_page = $_SESSION['payable_page'];
        } else {
            $paya_page = 25;
        }
    }
} elseif($_REQUEST['payable_code'] == 'summary1') {
    $payable_code = $_REQUEST['payable_code'];
    /////////// summary_view ���ɲä��줿�ѥ�᡼����
    if (isset($_REQUEST['payable_s_ym'])) {
        $s_ym = $_REQUEST['payable_s_ym'];
        $_SESSION['payable_s_ym'] = $s_ym;
    } else {
        $s_ym = date('Ym');
    }
    if (isset($_REQUEST['payable_e_ym'])) {
        $e_ym = $_REQUEST['payable_e_ym'];
        $_SESSION['payable_e_ym'] = $e_ym;
    } else {
        $e_ym = date('Ym');
    }
    if (isset($_REQUEST['payable_div'])) {
        if ($_REQUEST['payable_div']=='A') {
            $_REQUEST['payable_div'] = ' ';
        }
        $sum_div = $_REQUEST['payable_div'];
        $_SESSION['payable_div'] = $sum_div;
    } else {
        if (isset($_SESSION['payable_div'])) {
            if ($_SESSION['payable_div']=='A') {
                $_SESSION['payable_div'] = ' ';
            }
            $sum_div = $_SESSION['payable_div'];
        } else {
            $sum_div = ' ';
        }
    }
    if (isset($_REQUEST['payable_vendor'])) {
        $sum_vendor = $_REQUEST['payable_vendor'];
        $_SESSION['payable_vendor'] = $sum_vendor;
    } else {
        if (isset($_SESSION['payable_vendor'])) {
            $sum_vendor = $_SESSION['payable_vendor'];
        } else {
            $sum_vendor = '';
        }
    }
    if (isset($_REQUEST['paya_page'])) {
        $paya_page = $_REQUEST['paya_page'];
        $_SESSION['payable_page'] = $paya_page;
    } else {
        if (isset($_SESSION['payable_page'])) {
            $paya_page = $_SESSION['payable_page'];
        } else {
            $paya_page = 25;
        }
    }
} elseif($_REQUEST['payable_code'] == 'summary2') {
    $payable_code = $_REQUEST['payable_code'];
    /////////// summary2_view ���ɲä��줿�ѥ�᡼����
    if (isset($_REQUEST['payable_s2_ym'])) {
        $s_ym = $_REQUEST['payable_s2_ym'];
        $_SESSION['payable_s2_ym'] = $s_ym;
    } else {
        $s_ym = date('Ym');
    }
    if (isset($_REQUEST['payable_e2_ym'])) {
        $e_ym = $_REQUEST['payable_e2_ym'];
        $_SESSION['payable_e2_ym'] = $e_ym;
    } else {
        $e_ym = date('Ym');
    }
    if (isset($_REQUEST['payable2_div'])) {
        if ($_REQUEST['payable2_div']=='A') {
            $_REQUEST['payable2_div'] = ' ';
        }
        $sum_div = $_REQUEST['payable2_div'];
        $_SESSION['payable2_div'] = $sum_div;
    } else {
        if (isset($_SESSION['payable2_div'])) {
            if ($_SESSION['payable2_div']=='A') {
                $_SESSION['payable2_div'] = ' ';
            }
           $sum_div = $_SESSION['payable2_div'];
        } else {
           $sum_div = ' ';
        }
    }
    if (isset($_REQUEST['payable2_vendor'])) {
        $sum_vendor = $_REQUEST['payable2_vendor'];
        $_SESSION['payable2_vendor'] = $sum_vendor;
    } else {
        if (isset($_SESSION['payable2_vendor'])) {
            $sum_vendor = $_SESSION['payable2_vendor'];
        } else {
            $sum_vendor = '';
        }
    }
    if (isset($_REQUEST['paya_page'])) {
        $paya_page = $_REQUEST['paya_page'];
        $_SESSION['payable_page'] = $paya_page;
    } else {
        if (isset($_SESSION['payable_page'])) {
            $paya_page = $_SESSION['payable_page'];
        } else {
            $paya_page = 25;
        }
    }
}
//////////// ���칩��ٵ��ʤ��б�
if (isset($_REQUEST['kei_ym'])) {
    $kei_ym = $_REQUEST['kei_ym'];
    $kei_ym = format_date8($kei_ym);
    $_SESSION['kei_ym'] = $kei_ym;
} else {
    $kei_ym = @$_SESSION['kei_ym'];     // @ñ��������������б�(�դξ���̵�뤹��)
}

$s_ymd = $s_ym . '01';   // ������
$e_ymd = $e_ym + 1;   // ���η�����
$Y4 = substr($e_ymd, 0, 4);
$M2 = substr($e_ymd, 4, 2);
if ($M2 > 12) {
    $Y4 += 1;
    $M2  = 1;
}
$e_ymd = date('Ymd', (mktime(0, 0, 0, $M2, 1, $Y4) - 1));   // ��λǯ����
//////////// ���ǤιԿ�
define('PAGE', $paya_page);


$query = "select trim(substr(name, 1, 20)) from vendor_master where vendor='{$sum_vendor}' limit 1";
if (getUniResult($query, $name) <= 0) {
    $name = '�ޥ�����̤��Ͽ';
}
//////////// SQL ʸ�� where ��� ���Ѥ���
if($payable_code == 'summary1') {
    $search = sprintf("where act_date>=%d and act_date<=%d", $s_ymd, $e_ymd);
    switch ($sum_div) {
    case ' ';    // ����
        $search_kin = sprintf("%s and kamoku<=5", $search);
        $caption_div = '����(������)�����ڤӽ�����ޤ�';
        $caption_title = "�����������Ρ�<font color='blue'>���Ϲ��졧{$name}</font>��ǯ�" . format_date($s_ymd) . '��' . format_date($e_ymd);
        break;
    case 'C';    // ���ץ� ����
        $search_kin = sprintf("%s and kamoku<=5 and a.div='C'", $search);
        $caption_div = '���ץ�����(������)�����ڤӽ�����ޤ�';
        $caption_title = "��������C���Ρ�<font color='blue'>���Ϲ��졧{$name}</font>��ǯ�" . format_date($s_ymd) . '��' . format_date($e_ymd);
        break;
    case 'D';    // ���ץ� ɸ��
        $search_kin = sprintf("%s and kamoku<=5 and a.div='C' and ((kouji_no NOT like 'SC%%') or (kouji_no IS NULL))", $search);
        $caption_div = '���ץ�ɸ����(������)�����ڤӽ�����ޤ�';
        $caption_title = "��������Cɸ�ࡡ<font color='blue'>���Ϲ��졧{$name}</font>��ǯ�" . format_date($s_ymd) . '��' . format_date($e_ymd);
        break;
    case 'S';    // ���ץ� ����
        $search_kin = sprintf("%s and kamoku<=5 and a.div='C' and kouji_no like 'SC%%'", $search);
        $caption_div = '���ץ�������(������)�����ڤӽ�����ޤ�';
        $caption_title = "��������C����<font color='blue'>���Ϲ��졧{$name}</font>��ǯ�" . format_date($s_ymd) . '��' . format_date($e_ymd);
        break;
    case 'L';    // ��˥� ����
        //$search_kin = sprintf("%s and kamoku<=5 and a.div='L' and a.parts_no not like '%s'", $search, 'T%');
        $search_kin = sprintf("%s and kamoku<=5 and a.div='L'", $search);
        $caption_div = '��˥�����(������)�����ڤӽ�����ޤ�';
        $caption_title = "��������L���Ρ�<font color='blue'>���Ϲ��졧{$name}</font>��ǯ�" . format_date($s_ymd) . '��' . format_date($e_ymd);
        break;
    case 'T';    // �ġ��� ����
        //$search_kin = sprintf("%s and kamoku<=5 and (a.div='T' or (a.div<>'T' and a.div<>'C' and a.parts_no like '%s'))", $search, 'T%');
        $search_kin = sprintf("%s and kamoku<=5 and a.div='T'", $search);
        $caption_div = '�ġ�������(������)�����ڤӽ�����ޤ�';
        $caption_title = "��������T���Ρ�<font color='blue'>���Ϲ��졧{$name}</font>��ǯ�" . format_date($s_ymd) . '��' . format_date($e_ymd);
        break;
    case 'NKCT';    // NKCT
        $search_kin = sprintf("%s and kamoku<=5 and a.div='C' AND (m.tnk_tana LIKE '%s' OR uke_no LIKE '%s' OR uke_no LIKE '%s')", $search, '8%', 'Z%', 'H%');
        $caption_div = '�Σˣã�(������)�����ڤӽ�����ޤ�';
        $caption_title = "��������{$sum_div}��<font color='blue'>���Ϲ��졧{$name}</font>��ǯ�" . format_date($s_ymd) . '��' . format_date($e_ymd);
        break;
    case 'NKT';    // NKT
        $search_kin = sprintf("%s and kamoku<=5 and a.div='L' and (m.tnk_tana LIKE '%s' OR uke_no LIKE '%s' OR uke_no LIKE '%s')", $search, '8%', 'Z%', 'H%');
        $caption_div = '�Σˣ�(������)�����ڤӽ�����ޤ�';
        $caption_title = "��������{$sum_div}��<font color='blue'>���Ϲ��졧{$name}</font>��ǯ�" . format_date($s_ymd) . '��' . format_date($e_ymd);
        break;
    }
    $search = sprintf("%s and vendor='%s'", $search_kin,$sum_vendor);
} elseif($payable_code == 'summary2') {
    $search = sprintf("where act_date>=%d and act_date<=%d and order_no !='0000000' and sei_no !=0", $s_ymd, $e_ymd);
    switch ($sum_div) {
    case ' ';    // ����
        $search_kin = sprintf("%s and kamoku>=2 and kamoku<=3", $search);
        $caption_div = '����(������)�����ڤӽ�����ޤ�';
        $caption_title = "�����������Ρ�<font color='blue'>���Ϲ��졧{$name}</font>��ǯ�" . format_date($s_ymd) . '��' . format_date($e_ymd);
        break;
    case 'C';    // ���ץ� ����
        $search_kin = sprintf("%s and kamoku>=2 and kamoku<=3 and a.div='C'", $search);
        $caption_div = '���ץ�����(������)�����ڤӽ�����ޤ�';
        $caption_title = "��������C���Ρ�<font color='blue'>���Ϲ��졧{$name}</font>��ǯ�" . format_date($s_ymd) . '��' . format_date($e_ymd);
        break;
    case 'D';    // ���ץ� ɸ��
        $search_kin = sprintf("%s and kamoku>=2 and kamoku<=3 and a.div='C' and ((kouji_no NOT like 'SC%%') or (kouji_no IS NULL))", $search);
        $caption_div = '���ץ�ɸ����(������)�����ڤӽ�����ޤ�';
        $caption_title = "��������Cɸ�ࡡ<font color='blue'>���Ϲ��졧{$name}</font>��ǯ�" . format_date($s_ymd) . '��' . format_date($e_ymd);
        break;
    case 'S';    // ���ץ� ����
        $search_kin = sprintf("%s and kamoku>=2 and kamoku<=3 and a.div='C' and kouji_no like 'SC%%'", $search);
        $caption_div = '���ץ�������(������)�����ڤӽ�����ޤ�';
        $caption_title = "��������C����<font color='blue'>���Ϲ��졧{$name}</font>��ǯ�" . format_date($s_ymd) . '��' . format_date($e_ymd);
        break;
    case 'L';    // ��˥� ����
        //$search_kin = sprintf("%s and kamoku>=2 and kamoku<=3 and a.div='L' and a.parts_no not like '%s'", $search, 'T%');
        $search_kin = sprintf("%s and kamoku>=2 and kamoku<=3 and a.div='L'", $search);
        $caption_div = '��˥�����(������)�����ڤӽ�����ޤ�';
        $caption_title = "��������L���Ρ�<font color='blue'>���Ϲ��졧{$name}</font>��ǯ�" . format_date($s_ymd) . '��' . format_date($e_ymd);
        break;
    case 'T';    // �ġ��� ����
        //$search_kin = sprintf("%s and kamoku>=2 and kamoku<=3 and (a.div='T' or (a.div<>'T' and a.div<>'C' and a.parts_no like '%s'))", $search, 'T%');
        $search_kin = sprintf("%s and kamoku>=2 and kamoku<=3 and a.div='T'", $search);
        $caption_div = '�ġ�������(������)�����ڤӽ�����ޤ�';
        $caption_title = "��������T���Ρ�<font color='blue'>���Ϲ��졧{$name}</font>��ǯ�" . format_date($s_ymd) . '��' . format_date($e_ymd);
        break;
    case 'NKCT';    // NKCT
        $search_kin = sprintf("%s and kamoku>=2 and kamoku<=3 and a.div='C' AND (m.tnk_tana LIKE '%s' OR uke_no LIKE '%s' OR uke_no LIKE '%s')", $search, '8%', 'Z%', 'H%');
        $caption_div = '�Σˣã�(������)�����ڤӽ�����ޤ�';
        $caption_title = "��������{$sum_div}��<font color='blue'>���Ϲ��졧{$name}</font>��ǯ�" . format_date($s_ymd) . '��' . format_date($e_ymd);
        break;
    case 'NKT';    // NKT
        $search_kin = sprintf("%s and kamoku>=2 and kamoku<=3 and a.div='L' and (m.tnk_tana LIKE '%s' OR uke_no LIKE '%s' OR uke_no LIKE '%s')", $search, '8%', 'Z%', 'H%');
        $caption_div = '�Σˣ�(������)�����ڤӽ�����ޤ�';
        $caption_title = "��������{$sum_div}��<font color='blue'>���Ϲ��졧{$name}</font>��ǯ�" . format_date($s_ymd) . '��' . format_date($e_ymd);
        break;
    }
    $search = sprintf("%s and vendor='%s'", $search_kin,$sum_vendor);
}
//////////// ��ץ쥳���ɿ�����     (�оݥǡ����κ������ڡ�������˻���)
$query = sprintf('select count(*), sum(Uround(order_price * siharai,0)) from act_payable as a LEFT OUTER JOIN parts_stock_master AS m ON (m.parts_no=a.parts_no) left outer join order_plan using(sei_no) %s', $search);
$res_max = array();
if ( getResult2($query, $res_max) <= 0) {         // $maxrows �μ���
    $_SESSION['s_sysmsg'] .= "��ץ쥳���ɿ��μ����˼���";      // .= ��å��������ɲä���
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // ľ���θƽи������
    exit();
} else {
    $maxrows = $res_max[0][0];                  // ��ץ쥳���ɿ��μ���
    // $sum_kin = $res_max[0][1];                  // �����ݶ�ۤμ���
    $caption_title .= '����׶�ۡ�' . number_format($res_max[0][1]);   // �����ݶ�ۤ򥭥�ץ���󥿥��ȥ�˥��å�
    $caption_title .= '����׷����' . number_format($res_max[0][0]);   // �����ݷ���򥭥�ץ���󥿥��ȥ�˥��å�
}

//////////// �ڡ������ե��å�����
if ( isset($_REQUEST['forward']) ) {                       // ���Ǥ������줿
    $_SESSION['paya_offset'] += PAGE;
    if ($_SESSION['paya_offset'] >= $maxrows) {
        $_SESSION['paya_offset'] -= PAGE;
        if ($_SESSION['s_sysmsg'] == "") {
            $_SESSION['s_sysmsg'] .= "<font color='yellow'>���ǤϤ���ޤ���</font>";
        } else {
            $_SESSION['s_sysmsg'] .= "<br><font color='yellow'>���ǤϤ���ޤ���</font>";
        }
    }
} elseif ( isset($_REQUEST['backward']) ) {                // ���Ǥ������줿
    $_SESSION['paya_offset'] -= PAGE;
    if ($_SESSION['paya_offset'] < 0) {
        $_SESSION['paya_offset'] = 0;
        if ($_SESSION['s_sysmsg'] == "") {
            $_SESSION['s_sysmsg'] .= "<font color='yellow'>���ǤϤ���ޤ���</font>";
        } else {
            $_SESSION['s_sysmsg'] .= "<br><font color='yellow'>���ǤϤ���ޤ���</font>";
        }
    }
} elseif ( isset($_REQUEST['page_keep']) ) {               // ���ߤΥڡ�����ݻ�����
    $offset = $_SESSION['paya_offset'];
} else {
    $_SESSION['paya_offset'] = 0;                            // ���ξ��ϣ��ǽ����
}
$offset = $_SESSION['paya_offset'];

//////////// ɽ�����Υǡ���ɽ���ѤΥ���ץ� Query & �����
$query = sprintf("
        SELECT
            -- act_date    as ������,
            -- type_no     as \"T\",
            uke_no      as ������,          -- 00
            uke_date    as ������,          -- 01
            ken_date    as ������,          -- 02
            substr(trim(name), 1, 8)
                        as ȯ����̾,        -- 03
            a.parts_no    as �����ֹ�,        -- 04
            substr(midsc, 1, 12)
                        AS ����̾,          -- 05
            substr(mepnt, 1, 10)
                        AS �Ƶ���,          -- 06
            koutei      as ����,            -- 07
            mtl_cond    as ��,      -- ���    08
            order_price as ȯ��ñ��,        -- 09
            genpin      as ���ʿ�,          -- 10
            siharai     as ��ʧ��,          -- 11
            Uround(order_price * siharai,0)
                        as ��ݶ��,        -- 12
            sei_no      as ��¤�ֹ�,        -- 13
            a.div       as ��,              -- 14
            kamoku      as ��,              -- 15
            order_no    as ��ʸ�ֹ�,        -- 16
            vendor      as ȯ����           -- 17
        FROM
            act_payable AS a
        LEFT OUTER JOIN
            vendor_master USING(vendor)
        left outer join
            order_plan
        using(sei_no)
        LEFT OUTER JOIN
            miitem ON (a.parts_no = mipn)
        LEFT OUTER JOIN
            parts_stock_master AS m ON (m.parts_no=a.parts_no)
        %s 
        ORDER BY act_date DESC
        OFFSET %d LIMIT %d
    ", $search, $offset, PAGE);       // ���� $search �Ǹ���
$res   = array();
$field = array();
if (($rows = getResultWithField2($query, $field, $res)) <= 0) {
    $_SESSION['s_sysmsg'] .= '��ݥǡ���������ޤ���';
    if (isset($_REQUEST['material'])) {
        header('Location: ' . H_WEB_HOST . $menu->out_RetUrl() . '?material=' . $_REQUEST['material']);    // ľ���θƽи������
    } else {
        header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // ľ���θƽи������
    }
    exit();
} else {
    $num = count($field);       // �ե�����ɿ�����
}

// 'YY/MM/DD'�ե����ޥåȤΣ�������դ�YYYYMMDD�Σ���˥ե����ޥåȤ����֤���
function format_date8($date8)
{
    if (0 == $date8) {
        $date8 = '--------';    
    }
    if (8 == strlen($date8)) {
        $nen   = substr($date8,0,2);
        $tsuki = substr($date8,3,2);
        $hi    = substr($date8,6,2);
        return '20' . $nen . $tsuki . $hi;
    } else {
        return FALSE;
    }
}

$paya_code = 'on';
if ($sum_div ==' ') {
    $sum_div = 'A';
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
<?=$menu->out_site_java()?>
<?=$menu->out_css()?>

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
/* ������ϥե�����Υ�����Ȥ˥ե������������� */
function set_focus(){
    // document.body.focus();   // F2/F12������ͭ���������б�
    document.mhForm.backwardStack.focus();  // �嵭��IE�ΤߤΤ���NN�б�
    // document.form_name.element_name.focus();      // ������ϥե����ब������ϥ����Ȥ򳰤�
    // document.form_name.element_name.select();
}
// -->
</script>

<!-- �������륷���ȤΥե��������򥳥��� HTML���� �����Ȥ�����Ҥ˽���ʤ��������
<link rel='stylesheet' href='template.css?<?= $uniq ?>' type='text/css' media='screen'>
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
    background-color: yellow;
    color:            blue;
    font-size:        10pt;
    font-weight:      bold;
    font-family:      monospace;
}
a:hover {
    background-color:   blue;
    color:              white;
}
a {
    color:              blue;
    text-decoration:    none;
}
-->
</style>
</head>
<body onLoad='set_focus()'>
    <center>
<?=$menu->out_title_border()?>
        
        <!----------------- ������ ���� ���� �Υե����� ---------------->
        <table width='100%' cellspacing="0" cellpadding="0" border='0'>
            <form name='page_form' method='post' action='<?= $current_script ?>'>
                <tr>
                    <td align='left'>
                        <table align='left' border='3' cellspacing='0' cellpadding='0'>
                            <td align='left'>
                                <input class='pt10b' type='submit' name='backward' value='����'>
                            </td>
                        </table>
                    </td>
                    <td nowrap align='center' class='caption_font'>
                        <?= $caption_title . "\n" ?>
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
                    <th class='winbox' nowrap width='10'>No</th>        <!-- �ԥʥ�С���ɽ�� -->
                <?php
                for ($i=0; $i<$num; $i++) {             // �ե�����ɿ�ʬ���֤�
                ?>
                    <th class='winbox' nowrap><?= $field[$i] ?></th>
                <?php
                }
                ?>
                </tr>
            </thead>
            <tfoot>
                <!-- ���ߤϥեå����ϲ���ʤ� -->
            </tfoot>
            <tbody>
                        <!--  bgcolor='#ffffc6' �������� -->
                        <!-- ����ץ�<td rowspan='2' colspan='3' width='200' align='center' class='pt10b' bgcolor='#ffffc6'>  </td> -->
                <?php
                for ($r=0; $r<$rows; $r++) {
                    if ($uke_no == $res[$r][0]) {
                        echo "<tr style='background-color:#ffffc6;'>\n";
                    } else if ($res[$r][17] == '91111' && $kei_ym == $res[$r][2]){  //���칩������ʤؤο��դ�
                        echo "<tr style='background-color:#ffffc6;'>\n";
                    } else {
                        echo "<tr>\n";
                    }
                    echo "    <td class='winbox' nowrap align='right'><span class='pt10b'>", ($r + $offset + 1), "</span></td>    <!-- �ԥʥ�С���ɽ�� -->\n";
                    for ($i=0; $i<$num; $i++) {         // �쥳���ɿ�ʬ���֤�
                        switch ($i) {
                        case  5:        // ����̾
                            $res[$r][$i] = mb_convert_kana($res[$r][$i], 'k');
                            $res[$r][$i] = mb_substr($res[$r][$i], 0, 12);
                        case  3:        // ȯ����̾
                            if ($res[$r][$i] == '') $res[$r][$i] = '&nbsp;';
                            echo "<td class='winbox' nowrap align='left'><span class='pt9'>{$res[$r][$i]}</span></td>\n";
                            break;
                        case  4:        // �����ֹ�
                            if (trim($res[$r][$i]) == '') {
                                echo "<td class='winbox' nowrap align='center'>&nbsp;</td>\n";
                            } else {
                                echo "<td class='winbox' nowrap align='center'><span class='pt11b'><a href='", $menu->out_action('ñ������ɽ��'), "?parts_no=", urlencode("{$res[$r][$i]}"), "&lot_cost=", urlencode("{$res[$r][9]}"), "&uke_date={$res[$r][1]}&vendor={$res[$r][17]}&paya_code={$paya_code}&payable_code={$payable_code}&payable_s_ym={$s_ym}&payable_e_ym={$e_ym}&payable_div={$sum_div}&payable_vendor={$sum_vendor}&material=1#mark'>{$res[$r][$i]}</a></span></td>\n";
                            }
                            break;
                        case  6:        // �Ƶ���
                            if ($res[$r][$i] == '') $res[$r][$i] = '&nbsp;';
                            $res[$r][$i] = mb_convert_kana($res[$r][$i], 'k');
                            echo "<td class='winbox' nowrap align='left'><span class='pt9'>{$res[$r][$i]}</span></td>\n";
                            break;
                        case  9:        // ȯ��ñ��
                        case 10:        // ���ʿ�
                        case 11:        // ��ʧ��
                            echo "<td class='winbox' nowrap align='right'><span class='pt9'>" . number_format($res[$r][$i], 2) . "</span></td>\n";
                            break;
                        case 12:
                            echo "<td class='winbox' nowrap align='right'><span class='pt9'>" . number_format($res[$r][$i]) . "</span></td>\n";
                            break;
                        default:
                            if (trim($res[$r][$i]) == '') $res[$r][$i] = '&nbsp;';
                            echo "<td class='winbox' nowrap align='center'><span class='pt9'>{$res[$r][$i]}</span></td>\n";
                        }
                    }
                    echo "</tr>\n";
                }
                ?>
            </tbody>
        </table>
            </td></tr>
        </table> <!----------------- ���ߡ�End ------------------>
    </center>
</body>
<?=$menu->out_alert_java()?>
</html>
<?php
ob_end_flush();                 // ���ϥХåե���gzip���� END
?>
