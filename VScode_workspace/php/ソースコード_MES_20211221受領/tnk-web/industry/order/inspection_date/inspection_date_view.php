<?php
//////////////////////////////////////////////////////////////////////////////
// ���������ξȲ�����١˹����� UKWLIB/W#HIBCTR                             //
// Copyright (C) 2016-     Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2016/01/29 Created   inspection_date_view.php                            //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
// ini_set('implicit_flush', 'off');           // echo print �� flush �����ʤ�(�٤��ʤ뤿��) CLI CGI��
// ini_set('max_execution_time', 1200);        // ����¹Ի���=20ʬ CLI CGI��
ob_start('ob_gzhandler');                   // ���ϥХåե���gzip����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��
require_once ('../../../function.php');        // define.php �� pgsql.php �� require_once ���Ƥ���
require_once ('../../../tnk_func.php');        // TNK �˰�¸������ʬ�δؿ��� require_once ���Ƥ���
require_once ('../../../MenuHeader.php');      // TNK ������ menu class
require_once ('../../../ControllerHTTP_Class.php');    // TNK ������ MVC Controller Class
access_log();                               // Script Name �ϼ�ư����

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader(0);                  // ǧ�ڥ����å�0=���̰ʾ� �����=TOP_MENU �����ȥ�̤����

////////////// ����������
$menu->set_site(30, 99);                    // site_index=40(������˥塼) site_id=10(��ݼ���)
////////////// �꥿���󥢥ɥ쥹����
// $menu->set_RetUrl(INDUST_MENU);             // �̾�ϻ��ꤹ��ɬ�פϤʤ�
//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title('�� �� �� �� �� �� �� �� �� �� ��');
//////////// ɽ�������     ���Υ��å��ǽ������뤿�ᤳ���Ǥϻ��Ѥ��ʤ�
// $menu->set_caption('������ɬ�פʾ��������������򤷤Ʋ�������');
//////////// �ƽ����action̾�ȥ��ɥ쥹����
$menu->set_action('ñ������ɽ��',   INDUST . 'parts/parts_cost_view.php');

//////////// ���å����Υ��󥹥��󥹤�����
$session = new Session();

if (isset($_REQUEST['material'])) {
    $menu->set_retGET('material', $_REQUEST['material']);
    if (isset($_REQUEST['uke_no'])) {
        $uke_no = $_REQUEST['uke_no'];
        $_SESSION['uke_no'] = $uke_no;
    } else {
        $uke_no = @$_SESSION['uke_no'];     // @�߸˷����ɽ����������ꤵ�줿�����б�(uke_no�ʤ�)
    }
    $current_script = $menu->out_self() . '?material=1';
    if (isset($_SESSION['paya_kamoku'])) {
        unset($_SESSION['paya_kamoku']);        // ñ�ΤǤβ��ܻ��꤬���ˤ���Ƥ���Х��ꥢ��
    }
    $_SESSION['paya_strdate'] = '20001001';     // ʬ�Ҳ�����
    $_SESSION['paya_enddate'] = '99999999';     // �ǿ��ޤ�
} elseif (isset($_REQUEST['uke_no'])) {     // �߸˷���(ñ�Τ���)�ƽл����б�
    $uke_no = $_REQUEST['uke_no'];
    $current_script = $menu->out_self();
    if (isset($_SESSION['paya_kamoku'])) {
        unset($_SESSION['paya_kamoku']);        // ñ�ΤǤβ��ܻ��꤬���ˤ���Ƥ���Х��ꥢ��
    }
    $_SESSION['paya_strdate'] = '20001001';     // ʬ�Ҳ�����
    $_SESSION['paya_enddate'] = '99999999';     // �ǿ��ޤ�
} else {                                    // �ե�����(ñ�Τ���)�ƽл����б�
    $uke_no = '';
    $current_script = $menu->out_self();
}

//////////// ���칩��ٵ��ʤ��б�
if (isset($_REQUEST['kei_ym'])) {
    $kei_ym = $_REQUEST['kei_ym'];
    $kei_ym = format_date8($kei_ym);
    $_SESSION['kei_ym'] = $kei_ym;
} else {
    $kei_ym = @$_SESSION['kei_ym'];     // @ñ��������������б�(�դξ���̵�뤹��)
}

//////////// JavaScript Stylesheet File ��ɬ���ɤ߹��ޤ���
$uniq = uniqid('target');

//////////// �������ե����फ���POST�ǡ�������
if (isset($_REQUEST['parts_no'])) {
    $parts_no = $_REQUEST['parts_no'];
    $_SESSION['paya_parts_no'] = $parts_no;
} else {
    if (isset($_SESSION['paya_parts_no'])) {
        $parts_no = $_SESSION['paya_parts_no'];
    } else {
        $parts_no = '';
    }
}
if (isset($_REQUEST['div'])) {
    $div = $_REQUEST['div'];
    $_SESSION['payable_div'] = $div;
} else {
    if (isset($_SESSION['payable_div'])) {
        $div = $_SESSION['payable_div'];
    } else {
        $div = ' ';
    }
}
if (isset($_REQUEST['vendor'])) {
    $vendor = $_REQUEST['vendor'];
    $_SESSION['paya_vendor'] = $vendor;
} else {
    if (isset($_SESSION['paya_vendor'])) {
        $vendor = $_SESSION['paya_vendor'];
    } else {
        $vendor = '';
    }
}
if (isset($_REQUEST['kamoku'])) {
    $kamoku = $_REQUEST['kamoku'];
    $_SESSION['paya_kamoku'] = $kamoku;
} else {
    if (isset($_SESSION['paya_kamoku'])) {
        $kamoku = $_SESSION['paya_kamoku'];
    } else {
        $kamoku = '';
    }
}
if (isset($_REQUEST['str_date'])) {
    $str_date = $_REQUEST['str_date'];
    $_SESSION['paya_strdate'] = $str_date;
} elseif (isset($_SESSION['paya_strdate'])) {
    $str_date = $_SESSION['paya_strdate'];
} else {
    $year  = date('Y') - 5; // ��ǯ������
    $month = date('m');
    $str_date = $year . $month . '01';
}
if (isset($_REQUEST['end_date'])) {
    $end_date = $_REQUEST['end_date'];
    $_SESSION['paya_enddate'] = $end_date;
} elseif (isset($_SESSION['paya_enddate'])) {
    $end_date = $_SESSION['paya_enddate'];
} else {
    $end_date = '99999999';
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
if (isset($_REQUEST['ken_num'])) {
    $ken_num = $_REQUEST['ken_num'];
    $_SESSION['paya_ken_num'] = $ken_num;
} else {
    if (isset($_SESSION['paya_ken_num'])) {
        $ken_num = $_SESSION['paya_ken_num'];
    } else {
        $ken_num = '';
    }
}
if ($session->get('str_date') != '') {
    $str_date = $session->get('str_date');
    $_SESSION['str_date'] = $str_date;
}
if ($session->get('end_date') != '') {
    $end_date = $session->get('end_date');
    $_SESSION['end_date'] = $end_date;
}
//////////// ���ǤιԿ�
define('PAGE', $paya_page);

//////////// SQL ʸ�� where ��� ���Ѥ��� 01111=�������칩�� 00222=��¤������ 99999=����
$search = sprintf("where act_date>=%d and act_date<=%d", $str_date, $end_date);

//////////// SQL ʸ�� where ��� ���Ѥ���
if ($parts_no != '') {
    if ($ken_num != '') {
        $search_kin = sprintf("%s and paya.parts_no='%s' and ((to_date(ken_date, 'YYYYMMDD') - to_date(uke_date, 'YYYYMMDD')) - (SELECT count(*) FROM company_calendar WHERE tdate>=to_date(uke_date, 'YYYYMMDD') and tdate<=to_date(ken_date, 'YYYYMMDD') and bd_flg='f'))='%d'", $search, $parts_no, $ken_num);
        $query = "select trim(substr(midsc,1,18)) from miitem where mipn='{$parts_no}' limit 1";
        if (getUniResult($query, $name) <= 0) {
            $name = '�ޥ�����̤��Ͽ';
        }
        $caption_title = "�����ֹ桧{$parts_no}��<font color='blue'>����̾��{$name}</font>��ǯ�" . format_date($str_date) . '��' . format_date($end_date) . "������������{$ken_num}��";
    } else {
        $search_kin = sprintf("%s and paya.parts_no='%s'", $search, $parts_no);
        $query = "select trim(substr(midsc,1,18)) from miitem where mipn='{$parts_no}' limit 1";
        if (getUniResult($query, $name) <= 0) {
            $name = '�ޥ�����̤��Ͽ';
        }
        $caption_title = "�����ֹ桧{$parts_no}��<font color='blue'>����̾��{$name}</font>��ǯ�" . format_date($str_date) . '��' . format_date($end_date) . "���������������٤�";
    }
} elseif ($div != ' ') {
    if ($vendor != '') {
        if ($ken_num != '') {
            if($div == 'D') {
                $search_kin = sprintf("%s and vendor='%s' and paya.div='C' and kouji_no NOT like 'SC%%' and ((to_date(ken_date, 'YYYYMMDD') - to_date(uke_date, 'YYYYMMDD')) - (SELECT count(*) FROM company_calendar WHERE tdate>=to_date(uke_date, 'YYYYMMDD') and tdate<=to_date(ken_date, 'YYYYMMDD') and bd_flg='f'))='%d'", $search, $vendor, $ken_num);
                $query = "select trim(substr(name, 1, 20)) from vendor_master where vendor='{$vendor}' limit 1";
                if (getUniResult($query, $name) <= 0) {
                    $name = '�ޥ�����̤��Ͽ';
                }
                $caption_title = "�����������ץ�ɸ�ࡡǯ�" . format_date($str_date) . '��' . format_date($end_date) . "ȯ���衧" . $name . "������������{$ken_num}��";
            } elseif($div == 'S') {
                $search_kin = sprintf("%s and vendor='%s' and paya.div='C' and kouji_no like 'SC%%' and ((to_date(ken_date, 'YYYYMMDD') - to_date(uke_date, 'YYYYMMDD')) - (SELECT count(*) FROM company_calendar WHERE tdate>=to_date(uke_date, 'YYYYMMDD') and tdate<=to_date(ken_date, 'YYYYMMDD') and bd_flg='f'))='%d'", $search, $vendor, $ken_num);
                $query = "select trim(substr(name, 1, 20)) from vendor_master where vendor='{$vendor}' limit 1";
                if (getUniResult($query, $name) <= 0) {
                    $name = '�ޥ�����̤��Ͽ';
                }
                $caption_title = "�����������ץ�����ǯ�" . format_date($str_date) . '��' . format_date($end_date) . "ȯ���衧" . $name . "������������{$ken_num}��";
            } else {
                $search_kin = sprintf("%s and vendor='%s' and paya.div='%s' and ((to_date(ken_date, 'YYYYMMDD') - to_date(uke_date, 'YYYYMMDD')) - (SELECT count(*) FROM company_calendar WHERE tdate>=to_date(uke_date, 'YYYYMMDD') and tdate<=to_date(ken_date, 'YYYYMMDD') and bd_flg='f'))='%d'", $search, $vendor, $div, $ken_num);
                $query = "select trim(substr(name, 1, 20)) from vendor_master where vendor='{$vendor}' limit 1";
                if (getUniResult($query, $name) <= 0) {
                    $name = '�ޥ�����̤��Ͽ';
                }
                $caption_title = "��������{$div}��ǯ�" . format_date($str_date) . '��' . format_date($end_date) . "ȯ���衧" . $name . "������������{$ken_num}��";
            }
        } else {
            if($div == 'D') {
                $search_kin = sprintf("%s and vendor='%s' and paya.div='C' and kouji_no NOT like 'SC%%'", $search, $vendor);
                $query = "select trim(substr(name, 1, 20)) from vendor_master where vendor='{$vendor}' limit 1";
                if (getUniResult($query, $name) <= 0) {
                    $name = '�ޥ�����̤��Ͽ';
                }
                $caption_title = "�����������ץ�ɸ�ࡡǯ�" . format_date($str_date) . '��' . format_date($end_date) . "ȯ���衧" . $name . "���������������٤�";
            } elseif($div == 'S') {
                $search_kin = sprintf("%s and vendor='%s' and paya.div='C' and kouji_no like 'SC%%'", $search, $vendor);
                $query = "select trim(substr(name, 1, 20)) from vendor_master where vendor='{$vendor}' limit 1";
                if (getUniResult($query, $name) <= 0) {
                    $name = '�ޥ�����̤��Ͽ';
                }
                $caption_title = "�����������ץ�����ǯ�" . format_date($str_date) . '��' . format_date($end_date) . "ȯ���衧" . $name . "���������������٤�";
            } else {
                $search_kin = sprintf("%s and vendor='%s' and paya.div='%s'", $search, $vendor, $div);
                $query = "select trim(substr(name, 1, 20)) from vendor_master where vendor='{$vendor}' limit 1";
                if (getUniResult($query, $name) <= 0) {
                    $name = '�ޥ�����̤��Ͽ';
                }
                $caption_title = "��������{$div}��ǯ�" . format_date($str_date) . '��' . format_date($end_date) . "ȯ���衧" . $name . "���������������٤�";
            }
        }
    } else {
        if ($ken_num != '') {
            if($div == 'D') {
                $search_kin = sprintf("%s and paya.div='C' and kouji_no NOT like 'SC%%' and ((to_date(ken_date, 'YYYYMMDD') - to_date(uke_date, 'YYYYMMDD')) - (SELECT count(*) FROM company_calendar WHERE tdate>=to_date(uke_date, 'YYYYMMDD') and tdate<=to_date(ken_date, 'YYYYMMDD') and bd_flg='f'))='%d'", $search, $ken_num);
                $caption_title = "�����������ץ�ɸ�ࡡǯ�" . format_date($str_date) . '��' . format_date($end_date) . "������������{$ken_num}��";
            } elseif($div == 'S') {
                $search_kin = sprintf("%s and paya.div='C' and kouji_no like 'SC%%' and ((to_date(ken_date, 'YYYYMMDD') - to_date(uke_date, 'YYYYMMDD')) - (SELECT count(*) FROM company_calendar WHERE tdate>=to_date(uke_date, 'YYYYMMDD') and tdate<=to_date(ken_date, 'YYYYMMDD') and bd_flg='f'))='%d'", $search, $ken_num);
                $caption_title = "�����������ץ�����ǯ�" . format_date($str_date) . '��' . format_date($end_date) . "������������{$ken_num}��";
            } else {
                $search_kin = sprintf("%s and paya.div='%s' and ((to_date(ken_date, 'YYYYMMDD') - to_date(uke_date, 'YYYYMMDD')) - (SELECT count(*) FROM company_calendar WHERE tdate>=to_date(uke_date, 'YYYYMMDD') and tdate<=to_date(ken_date, 'YYYYMMDD') and bd_flg='f'))='%d'", $search, $div, $ken_num);
                $caption_title = "��������{$div}��ǯ�" . format_date($str_date) . '��' . format_date($end_date) . "������������{$ken_num}��";
            }
        } else {
            if($div == 'D') {
                $search_kin = sprintf("%s and paya.div='C' and kouji_no NOT like 'SC%%'", $search);
                $caption_title = "�����������ץ�ɸ�ࡡǯ�" . format_date($str_date) . '��' . format_date($end_date) . "���������������٤�";
            } elseif($div == 'S') {
                $search_kin = sprintf("%s and paya.div='C' and kouji_no like 'SC%%'", $search);
                $caption_title = "�����������ץ�����ǯ�" . format_date($str_date) . '��' . format_date($end_date) . "���������������٤�";
            } else {
                $search_kin = sprintf("%s and paya.div='%s'", $search, $div);
                $caption_title = "��������{$div}��ǯ�" . format_date($str_date) . '��' . format_date($end_date) . "���������������٤�";
            }
        }
    }
} else {
    if ($vendor != '') {
        if ($ken_num != '') {
            $search_kin = sprintf("%s and vendor='%s' and ((to_date(ken_date, 'YYYYMMDD') - to_date(uke_date, 'YYYYMMDD')) - (SELECT count(*) FROM company_calendar WHERE tdate>=to_date(uke_date, 'YYYYMMDD') and tdate<=to_date(ken_date, 'YYYYMMDD') and bd_flg='f'))='%d'", $search, $vendor, $ken_num);
            $query = "select trim(substr(name, 1, 20)) from vendor_master where vendor='{$vendor}' limit 1";
            if (getUniResult($query, $name) <= 0) {
                $name = '�ޥ�����̤��Ͽ';
            }
            $caption_title = "�������������硡ǯ�" . format_date($str_date) . '��' . format_date($end_date) . "ȯ���衧" . $name . "������������{$ken_num}��";
        } else {
            $search_kin = sprintf("%s and vendor='%s'", $search, $vendor);
            $query = "select trim(substr(name, 1, 20)) from vendor_master where vendor='{$vendor}' limit 1";
            if (getUniResult($query, $name) <= 0) {
                $name = '�ޥ�����̤��Ͽ';
            }
            $caption_title = "�������������硡ǯ�" . format_date($str_date) . '��' . format_date($end_date) . "ȯ���衧" . $name . "���������������٤�";
        }
    } else {
        if ($ken_num != '') {
            $search_kin = sprintf("%s and ((to_date(ken_date, 'YYYYMMDD') - to_date(uke_date, 'YYYYMMDD')) - (SELECT count(*) FROM company_calendar WHERE tdate>=to_date(uke_date, 'YYYYMMDD') and tdate<=to_date(ken_date, 'YYYYMMDD') and bd_flg='f'))='%d'", $search, $ken_num);
            $caption_title = "�������������硡ǯ�" . format_date($str_date) . '��' . format_date($end_date) . "������������{$ken_num}��";
        } else {
            $search_kin = $search;
            $caption_title = "�������������硡ǯ�" . format_date($str_date) . '��' . format_date($end_date) . "���������������٤�";
        }
    }
}

//////////// ��ץ쥳���ɿ�����     (�оݥǡ����κ������ڡ�������˻���)
$query = sprintf('select count(*), sum(Uround(order_price * siharai,0)), sum(genpin), sum(siharai) from act_payable as paya left outer join order_plan using(sei_no) LEFT OUTER JOIN parts_stock_master AS m ON (m.parts_no=paya.parts_no) %s', $search_kin);
$res_max = array();
if ( getResult2($query, $res_max) <= 0) {         // $maxrows �μ���
    $_SESSION['s_sysmsg'] .= "��ץ쥳���ɿ��μ����˼���";      // .= ��å��������ɲä���
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // ľ���θƽи������
    exit();
} else {
    $maxrows = $res_max[0][0];                  // ��ץ쥳���ɿ��μ���
    // $sum_kin = $res_max[0][1];                  // �����ݶ�ۤμ���
    //$caption_title  .= '����׶�ۡ�' . number_format($res_max[0][1]);   // �����ݶ�ۤ򥭥�ץ���󥿥��ȥ�˥��å�
    //$caption_title  .= '����׷����' . number_format($res_max[0][0]);   // �����ݷ���򥭥�ץ���󥿥��ȥ�˥��å�
    $caption_title2  = '��׶�ۡ�' . number_format($res_max[0][1]);   // �����ݶ�ۤ򥭥�ץ���󥿥��ȥ�˥��å�
    $caption_title2 .= '����׷����' . number_format($res_max[0][0]);   // �����ݷ���򥭥�ץ���󥿥��ȥ�˥��å�
    $caption_title2 .= '�����ʿ��ס�' . number_format($res_max[0][2], 2);   // ��׸��ʿ��򥭥�ץ���󥿥��ȥ�˥��å�
    $caption_title2 .= '����ʧ���ס�' . number_format($res_max[0][3], 2);   // ��׻�ʧ���򥭥�ץ���󥿥��ȥ�˥��å�
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
             (to_date(ken_date, 'YYYYMMDD') - to_date(uke_date, 'YYYYMMDD')) - (SELECT count(*) FROM company_calendar WHERE tdate>=to_date(uke_date, 'YYYYMMDD') and tdate<=to_date(ken_date, 'YYYYMMDD') and bd_flg='f') as ��������,          -- 03
            substr(trim(name), 1, 8)
                        as ȯ����̾,        -- 04
            paya.parts_no    as �����ֹ�,        -- 05
            substr(midsc, 1, 12)
                        AS ����̾,          -- 06
            substr(mepnt, 1, 10)
                        AS �Ƶ���,          -- 07
            koutei      as ����,            -- 08
            mtl_cond    as ��,      -- ���    09
            order_price as ȯ��ñ��,        -- 10
            genpin      as ���ʿ�,          -- 11
            siharai     as ��ʧ��,          -- 12
            Uround(order_price * siharai,0)
                        as ��ݶ��,        -- 13
            sei_no      as ��¤�ֹ�,        -- 14
            paya.div    as ��,              -- 15
            kamoku      as ��,              -- 16
            order_no    as ��ʸ�ֹ�,        -- 17
            vendor      as ȯ����           -- 18
        FROM
            act_payable AS paya
        LEFT OUTER JOIN
            vendor_master USING(vendor)
        left outer join
            order_plan using(sei_no)
        LEFT OUTER JOIN
            miitem ON (paya.parts_no = mipn)
        LEFT OUTER JOIN
            parts_stock_master AS m ON (m.parts_no=paya.parts_no)
        %s 
        ORDER BY �������� DESC, act_date DESC
        OFFSET %d LIMIT %d
    ", $search_kin, $offset, PAGE);       // ���� $search �Ǹ���
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

// ��������CSV�����Ѥν������
// �ե�����̾�����ܸ��Ĥ���ȼ����Ϥ��ǥ��顼�ˤʤ�Τǰ���ѻ����ѹ�
if ($div == " ") $act_name = "ALL";
if ($div == "C") $act_name = "C-all";
if ($div == "D") $act_name = "C-toku";
if ($div == "S") $act_name = "C-hyou";
if ($div == "L") $act_name = "L-all";
if ($div == "T") $act_name = "T-all";
if ($div == "NKCT") $act_name = "NKCT";
if ($div == "NKT") $act_name = "NKT";
///// ������̾��CSV������
/*
if ($customer == " ") $c_name = "T-ALL";
if ($customer == "00001") $c_name = "T-NK";
if ($customer == "00002") $c_name = "T-MEDO";
if ($customer == "00003") $c_name = "T-NKT";
if ($customer == "00004") $c_name = "T-MEDOTEC";
if ($customer == "00005") $c_name = "T-SNK";
if ($customer == "00101") $c_name = "T-NKCT";
if ($customer == "00102") $c_name = "T-BRECO";
if ($customer == "99999") $c_name = "T-SHO";
*/
// SQL�Υ������������ܸ��ѻ����ѹ���'�⥨�顼�ˤʤ�Τ�/�˰���ѹ�
//$csv_search = str_replace('�׾���','keidate',$search);
//$csv_search = str_replace('������','jigyou',$csv_search);
//$csv_search = str_replace('��ɼ�ֹ�','denban',$csv_search);
//$csv_search = str_replace('������','tokui',$csv_search);
$csv_search = str_replace('\'','/',$search);

// CSV�ե�����̾������ʳ���ǯ��-��λǯ��-��������
$outputFile = $str_date . '-' . $end_date . '-' . $act_name;

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
                        <br>
                        <?= $caption_title2 . "\n" ?>
                        <!--
                        <a href='act_payable_csv.php?csvname=<?php echo $outputFile ?>&csvsearch=<?php echo $csv_search ?>&csvvendor=<?php echo $vendor ?>'>
                            CSV����
                        </a>
                        -->
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
                    } else if ($res[$r][18] == '91111' && $kei_ym == $res[$r][2]){  //���칩������ʤؤο��դ�
                        echo "<tr style='background-color:#ffffc6;'>\n";
                    } else {
                        echo "<tr>\n";
                    }
                    echo "    <td class='winbox' nowrap align='right'><span class='pt10b'>", ($r + $offset + 1), "</span></td>    <!-- �ԥʥ�С���ɽ�� -->\n";
                    for ($i=0; $i<$num; $i++) {         // �쥳���ɿ�ʬ���֤�
                        switch ($i) {
                        case  6:        // ����̾
                            $res[$r][$i] = mb_convert_kana($res[$r][$i], 'k');
                            $res[$r][$i] = mb_substr($res[$r][$i], 0, 12);
                        case  4:        // ȯ����̾
                            if ($res[$r][$i] == '') $res[$r][$i] = '&nbsp;';
                            echo "<td class='winbox' nowrap align='left'><span class='pt9'>{$res[$r][$i]}</span></td>\n";
                            break;
                        case  5:        // �����ֹ�
                            if (trim($res[$r][$i]) == '') {
                                echo "<td class='winbox' nowrap align='center'><span class='pt9'>&nbsp;</span></td>\n";
                            } else {
                                echo "<td class='winbox' nowrap align='center'><span class='pt11b'><a href='", $menu->out_action('ñ������ɽ��'), "?parts_no=", urlencode("{$res[$r][$i]}"), "&lot_cost=", urlencode("{$res[$r][10]}"), "&uke_date={$res[$r][1]}&vendor={$res[$r][18]}&material=1&str_date={$str_date}&end_date={$end_date}#mark'>{$res[$r][$i]}</a></span></td>\n";
                            }
                            break;
                        case  7:        // �Ƶ���
                            if ($res[$r][$i] == '') $res[$r][$i] = '&nbsp;';
                            $res[$r][$i] = mb_convert_kana($res[$r][$i], 'k');
                            echo "<td class='winbox' nowrap align='left'><span class='pt9'>{$res[$r][$i]}</span></td>\n";
                            break;
                        case 10:        // ȯ��ñ��
                        case 11:        // ���ʿ�
                        case 12:        // ��ʧ��
                            echo "<td class='winbox' nowrap align='right'><span class='pt9'>" . number_format($res[$r][$i], 2) . "</span></td>\n";
                            break;
                        case 13:
                            echo "<td class='winbox' nowrap align='right'><span class='pt9'>" . number_format($res[$r][$i]) . "</span></td>\n";
                            break;
                        case 3:
                            echo "<td class='winbox' nowrap align='right'><span class='pt10b'>" . number_format($res[$r][$i]) . "</span></td>\n";
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
