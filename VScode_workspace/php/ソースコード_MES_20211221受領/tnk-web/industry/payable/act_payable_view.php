<?php
//////////////////////////////////////////////////////////////////////////////
// ��ݥҥ��ȥ�ξȲ� �� �����å���  ������ UKWLIB/W#HIBCTR                 //
// Copyright (C) 2003-2019 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2003/11/18 Created   act_payable_view.php                                //
// 2003/11/19 ��ư������ǧ�ꥹ�Ȥ��͹礻��������ͤ˰ʲ��Υ��å����ɲ�    //
//            ������(1)�����ʻųݣ�(2-5) ����(6)- �ι�׶�� ���������     //
//            ��˥��θ�����1 �����                                        //
// 2004/05/12 �����ȥ�˥塼ɽ������ɽ�� �ܥ����ɲ� menu_OnOff($script)�ɲ� //
// 2004/05/13 �����Ѥ˾�����򤬽�����ͤ��ѹ� (act_payable_form.php����)   //
// 2004/05/17 start���դν����������Σ��������ѹ� ��ݶ�ι�פ�ɽ���ɲ�  //
//            ȯ����(���Ϲ���)�λ�����ɲ�                                  //
// 2004/06/01 �����ֹ��CQ12357-#���ͤ�'#'�����뤿�� urlencode()���ղä�����//
// 2004/06/02 div='%s' and vendor='%s' �� vendor='%s' and div='%s' ���ѹ�   //
// 2004/12/07 �ǥ��쥯�ȥ���ز��� industry/payable ���ѹ�                //
// 2004/12/29 MenuHeader class ����Ѥ��ƶ��̥�˥塼���ڤ�ǧ���������ѹ�   //
// 2005/01/07 $menu->set_retGET('page_keep', $_REQUEST['material']);������  //
// 2005/01/12 �ե�����(ñ�Τ���)�ƽл����б�$_REQUEST['uke_no']���ɲ�       //
// 2005/01/13 GET�ѥ�᡼����uke_date���ɲ� ñ���������Ͽ���Υ����å��˻���//
// 2005/01/14 GET�ѥ�᡼����vendor���ɲ� ñ�������ȯ����Υ����å��˻���  //
// 2005/04/25 ñ�ξȲ�����ǰ�ư�������ϰϤ��ѹ�������Զ�����          //
//                ��    ��� �����ֹ��AND�������ɲ�                        //
// 2006/01/16 if ($kamoku != '') �� if (trim($kamoku) != '') ���ѹ�         //
// 2006/01/24 �ꥹ�Ȥ�����̾���ɲ� ���ɵ�������ΰ���                       //
// 2007/02/23 �ꥹ�Ȥ˿Ƶ�����ɲ� ����Ĺë�����ꡣȼ���쥤�������ѹ�   //
// 2007/05/14 ���칩������ʤؤο��դ�(kei_ym)     ��ë                     //
// 2007/05/17 ���å�����ǯ������̵�������б��ɲ�(�߸˷��򤫤���ݾȲ�)  //
// 2007/05/22 ���칩������ʤؤο��դ�(kei_ym)�ν������̥��å��ذ�ư(����)//
// 2007/09/03 ñ������Ȳ��(#mark)���ɲ� (����)                            //
// 2007/10/01 ��ݥǡ�����̵������������get�᥽�å��ɲ� E_ALL | E_STRICT//
// 2008/06/24 �ʾڰ���ˤ��ȯ������ɽ�����ɲ�                       ��ë //
// 2011/12/27 NKCT�ڤ�NKT�б��ΰ١������ɲ�                               //
//            ��о���ê�֤���Ƭ��'8'�ȼ����ֹ����Ƭ��'Z'                //
//            (�����ֹ����Ƭ��'H'�Τ�Τ�NK��ɼ�ΰٶ��̤Ǥ��ʤ��ä�)  ��ë //
// 2013/04/09 ���Ϲ�����ι�׶�۾Ȳ�β����ɲäΰ���Ĵ��             ��ë //
// 2013/10/12 csv���Ϥ��ɲ�(���ɰ���)                                  ��ë //
// 2015/05/21 �����������б�                                           ��ë //
// 2015/08/26 caption_title�˸��ʿ��פȻ�ʧ���פ��ɲá����ɾ��������       //
//            �ɲäˤ�����caption_title2���ɲä����Ԥ�ʬ��             ��ë //
// 2016/01/29 �ƹ��ܤ��ݻ�����ʤ��ä����ὤ��                         ��ë //
// 2016/08/08 mouseover���ɲ�                                          ��ë //
// 2017/06/30 ���顼�ɻߤΰ١�$act_name�ν�������ɲ�                  ��ë //
// 2018/01/29 ���ץ�����ɸ����ɲ�                                   ��ë //
// 2018/06/29 ¿�����T���ʹ������б�                                  ��ë //
// 2019/05/10 �����ֹ�Ǹ���������硢��������ξ�郎̵�뤵��Ƥ����Τ�    //
//            ���٤Ƥξ����̣����褦�ѹ����ʾ���ë����Ĺ�����     ��ë //
// 2019/05/20 �ƥ��Ȥǽ�λ����99999999�ˤ��Ƥ����Τ���               ��ë //
// 2019/06/25 �������ν���ͤ�7ǯ�����ѹ����������Ǥʤ����Ȥ������  ��ë //
// 2020/07/29 �����ֹ�˰��� &sei_no �ɲ�                              ���� //
// 2020/12/21 ������L������ʬ��ȴ���Ф������                          ��ë //
// 2021/01/20 ������L������ʬ��ȴ���Ф���SQL�����ǺƳ�                 ��ë //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
// ini_set('implicit_flush', 'off');           // echo print �� flush �����ʤ�(�٤��ʤ뤿��) CLI CGI��
// ini_set('max_execution_time', 1200);        // ����¹Ի���=20ʬ CLI CGI��
ob_start('ob_gzhandler');                   // ���ϥХåե���gzip����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��
require_once ('../../function.php');        // define.php �� pgsql.php �� require_once ���Ƥ���
require_once ('../../tnk_func.php');        // TNK �˰�¸������ʬ�δؿ��� require_once ���Ƥ���
require_once ('../../MenuHeader.php');      // TNK ������ menu class
require_once ('../../ControllerHTTP_Class.php');    // TNK ������ MVC Controller Class
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
    $session->add('paya_parts_no', $parts_no);
} else {
    $parts_no = $_SESSION['paya_parts_no'];
    $session->add('paya_parts_no', $parts_no);
    ///// �����ֹ��ɬ��
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
    $session->add('paya_kamoku', $kamoku);
} elseif (isset($_SESSION['paya_kamoku'])) {
    $kamoku = $_SESSION['paya_kamoku'];
    $session->add('paya_kamoku', $kamoku);
} elseif ($session->get('kamoku') != '') {
    $kamoku = $session->get('kamoku');
    $_SESSION['paya_kamoku'] = $kamoku;
} else {
    $kamoku = '';
}
if (isset($_REQUEST['str_date'])) {
    $str_date = $_REQUEST['str_date'];
    $_SESSION['paya_strdate'] = $str_date;
    $session->add('str_date', $str_date);
    $session->add('paya_strdate', $str_date);
} elseif ($session->get('str_date') != '') {
    $str_date = $session->get('str_date');
    $_SESSION['paya_strdate'] = $str_date;
    $session->add('str_date', $str_date);
    $session->add('paya_strdate', $str_date);
} else {
    //$year  = date('Y') - 5; // ��ǯ������
    $year  = date('Y') - 7;  // ��ǯ������
    $year  = date('Y') - 10; // ����ǯ������
    $month = date('m');
    $str_date = $year . $month . '01';
}
if (isset($_REQUEST['end_date'])) {
    $end_date = $_REQUEST['end_date'];
    $_SESSION['paya_enddate'] = $end_date;
    $session->add('end_date', $end_date);
    $session->add('paya_enddate', $end_date);
} elseif ($_SESSION['paya_enddate'] != '') {
    $end_date = $_SESSION['paya_enddate'];
    $session->add('end_date', $end_date);
    $session->add('paya_enddate', $end_date);
} elseif ($session->get('end_date') != '') {
    $end_date = $session->get('end_date');
    $_SESSION['paya_enddate'] = $end_date;
    $session->add('end_date', $end_date);
    $session->add('paya_enddate', $end_date);
} else {
    $end_date = '99999999';
}
//$end_date = '99999999';
if (isset($_REQUEST['paya_page'])) {
    $paya_page = $_REQUEST['paya_page'];
    $_SESSION['payable_page'] = $paya_page;
} else {
    if (isset($_SESSION['payable_page'])) {
        $paya_page = $_SESSION['payable_page'];
    } else {
        $paya_page = 23;
    }
}

//////////// ���ǤιԿ�
define('PAGE', $paya_page);

//////////// SQL ʸ�� where ��� ���Ѥ���
if ($parts_no != '') {
    
    if ($div != ' ') {
        if ($vendor != '') {
            if($div == 'NKCT') {
                $search = sprintf("where a.parts_no='%s' and act_date>=%d and act_date<=%d and vendor='%s' and a.div='C' AND (m.tnk_tana LIKE '%s' OR uke_no LIKE '%s' OR uke_no LIKE '%s')", $parts_no, $str_date, $end_date, $vendor, '8%', 'Z%', 'H%');
            } elseif($div == 'NKT') {
                $search = sprintf("where a.parts_no='%s' and act_date>=%d and act_date<=%d and vendor='%s' and a.div='L' AND (m.tnk_tana LIKE '%s' OR uke_no LIKE '%s' OR uke_no LIKE '%s')", $parts_no, $str_date, $end_date, $vendor, '8%', 'Z%', 'H%');
            } elseif($div == 'D') {
                $search = sprintf("where a.parts_no='%s' and act_date>=%d and act_date<=%d and vendor='%s' and a.div='C' and ((kouji_no NOT like 'SC%%') or (kouji_no IS NULL))", $parts_no, $str_date, $end_date, $vendor, $div);
            } elseif($div == 'S') {
                $search = sprintf("where a.parts_no='%s' and act_date>=%d and act_date<=%d and vendor='%s' and a.div='C' and kouji_no like 'SC%%'", $parts_no, $str_date, $end_date, $vendor, $div);
            } else {
                $search = sprintf("where a.parts_no='%s' and act_date>=%d and act_date<=%d and vendor='%s' and a.div='%s'", $parts_no, $str_date, $end_date, $vendor, $div);
            }
            $query = "select trim(substr(name, 1, 20)) from vendor_master where vendor='{$vendor}' limit 1";
            if (getUniResult($query, $name) <= 0) {
                $name = '�ޥ�����̤��Ͽ';
            }
            $caption_title = "�����ֹ桧{$parts_no}����������{$div}��<font color='blue'>���Ϲ��졧{$name}</font>��ǯ�" . format_date($str_date) . '��' . format_date($end_date);
        } else {
            if($div == 'NKCT') {
                $search = sprintf("where a.parts_no='%s' and act_date>=%d and act_date<=%d and a.div='C' and (m.tnk_tana LIKE '%s' OR uke_no LIKE '%s' OR uke_no LIKE '%s')", $parts_no, $str_date, $end_date, '8%', 'Z%', 'H%');
                $caption_title = "�����ֹ桧{$parts_no}����������{$div}��ǯ�" . format_date($str_date) . '��' . format_date($end_date);
            } elseif($div == 'NKT') {
                $search = sprintf("where a.parts_no='%s' and act_date>=%d and act_date<=%d and a.div='L' and (m.tnk_tana LIKE '%s' OR uke_no LIKE '%s' OR uke_no LIKE '%s')", $parts_no, $str_date, $end_date, '8%', 'Z%', 'H%');
                $caption_title = "�����ֹ桧{$parts_no}����������{$div}��ǯ�" . format_date($str_date) . '��' . format_date($end_date);
            /* CC���ʽ����Ѥ���CC���ʤ�ȴ���Ф��褦 �縵��profit_loss_pl_act_save.php���ѹ�
            } elseif($div == 'T') {
                $search = sprintf("where a.parts_no='%s' and  act_date>=%d and act_date<=%d and (a.div='%s' or (a.div<>'T' and a.div<>'C' and a.parts_no like '%s' and (c.miccc = 'E' or c.miccc IS NULL)))", $parts_no, $str_date, $end_date, $div, 'T%');
                $caption_title = "��������{$div}��ǯ�" . format_date($str_date) . '��' . format_date($end_date);
            */

            } elseif($div == 'T') {
                //$search = sprintf("where a.parts_no='%s' and act_date>=%d and act_date<=%d and (a.div='%s' or (a.div<>'T' and a.div<>'C' and a.parts_no like '%s'))", $parts_no, $str_date, $end_date, $div, 'T%');
                $search = sprintf("where a.parts_no='%s' and act_date>=%d and act_date<=%d and a.div='%s'", $parts_no, $str_date, $end_date, $div);
                $caption_title = "�����ֹ桧{$parts_no}����������{$div}��ǯ�" . format_date($str_date) . '��' . format_date($end_date); 
            } elseif($div == 'L') {
                //$search = sprintf("where a.parts_no='%s' and act_date>=%d and act_date<=%d and a.div='%s' and a.parts_no not like '%s'", $parts_no, $str_date, $end_date, $div, 'T%');
                $search = sprintf("where a.parts_no='%s' and act_date>=%d and act_date<=%d and a.div='%s'", $parts_no, $str_date, $end_date, $div);
                $caption_title = "�����ֹ桧{$parts_no}����������{$div}��ǯ�" . format_date($str_date) . '��' . format_date($end_date);
            } elseif($div == 'D') {
                $search = sprintf("where a.parts_no='%s' and act_date>=%d and act_date<=%d and a.div='C' and ((kouji_no NOT like 'SC%%') or (kouji_no IS NULL))", $parts_no, $str_date, $end_date, $div);
                $caption_title = "�����ֹ桧{$parts_no}����������Cɸ�ࡡǯ�" . format_date($str_date) . '��' . format_date($end_date);
            } elseif($div == 'S') {
                $search = sprintf("where a.parts_no='%s' and act_date>=%d and act_date<=%d and a.div='C' and kouji_no like 'SC%%'", $parts_no, $str_date, $end_date, $div);
                $caption_title = "�����ֹ桧{$parts_no}����������C����ǯ�" . format_date($str_date) . '��' . format_date($end_date);
            } else {
                $search = sprintf("where a.parts_no='%s' and act_date>=%d and act_date<=%d and a.div='%s'", $parts_no, $str_date, $end_date, $div);
                $caption_title = "�����ֹ桧{$parts_no}����������{$div}��ǯ�" . format_date($str_date) . '��' . format_date($end_date);
            }
        }
    } else {
        if ($vendor != '') {
            $search = sprintf("where a.parts_no='%s' and act_date>=%d and act_date<=%d and vendor='%s'", $parts_no, $str_date, $end_date, $vendor);
            $query = "select trim(substr(name, 1, 20)) from vendor_master where vendor='{$vendor}' limit 1";
            if (getUniResult($query, $name) <= 0) {
                $name = '�ޥ�����̤��Ͽ';
            }
            $caption_title = "�����ֹ桧{$parts_no}��<font color='blue'>���Ϲ��졧{$name}</font>��ǯ�" . format_date($str_date) . '��' . format_date($end_date);
        } else {
            $search = sprintf("where a.parts_no='%s' and act_date>=%d and act_date<=%d", $parts_no, $str_date, $end_date);
            $query = "select trim(substr(midsc,1,18)) from miitem where mipn='{$parts_no}' limit 1";
            if (getUniResult($query, $name) <= 0) {
                $name = '�ޥ�����̤��Ͽ';
            }
            $caption_title = "�����ֹ桧{$parts_no}��<font color='blue'>����̾��{$name}</font>��ǯ�" . format_date($str_date) . '��' . format_date($end_date);
        }
    }

    /*
    $search = sprintf("where a.parts_no='%s' and act_date>=%d and act_date<=%d", $parts_no, $str_date, $end_date);
    $query = "select trim(substr(midsc,1,18)) from miitem where mipn='{$parts_no}' limit 1";
    if (getUniResult($query, $name) <= 0) {
        $name = '�ޥ�����̤��Ͽ';
    }
    $caption_title = "�����ֹ桧{$parts_no}��<font color='blue'>����̾��{$name}</font>��ǯ�" . format_date($str_date) . '��' . format_date($end_date);
    */
} elseif ($div != ' ') {
    if ($vendor != '') {
        if($div == 'NKCT') {
            $search = sprintf("where act_date>=%d and act_date<=%d and vendor='%s' and a.div='C' AND (m.tnk_tana LIKE '%s' OR uke_no LIKE '%s' OR uke_no LIKE '%s')", $str_date, $end_date, $vendor, '8%', 'Z%', 'H%');
        } elseif($div == 'NKT') {
            $search = sprintf("where act_date>=%d and act_date<=%d and vendor='%s' and a.div='L' AND (m.tnk_tana LIKE '%s' OR uke_no LIKE '%s' OR uke_no LIKE '%s')", $str_date, $end_date, $vendor, '8%', 'Z%', 'H%');
        } elseif($div == 'D') {
            $search = sprintf("where act_date>=%d and act_date<=%d and vendor='%s' and a.div='C' and ((kouji_no NOT like 'SC%%') or (kouji_no IS NULL))", $str_date, $end_date, $vendor, $div);
        } elseif($div == 'S') {
            $search = sprintf("where act_date>=%d and act_date<=%d and vendor='%s' and a.div='C' and kouji_no like 'SC%%'", $str_date, $end_date, $vendor, $div);
        } else {
            $search = sprintf("where act_date>=%d and act_date<=%d and vendor='%s' and a.div='%s'", $str_date, $end_date, $vendor, $div);
        }
        $query = "select trim(substr(name, 1, 20)) from vendor_master where vendor='{$vendor}' limit 1";
        if (getUniResult($query, $name) <= 0) {
            $name = '�ޥ�����̤��Ͽ';
        }
        $caption_title = "��������{$div}��<font color='blue'>���Ϲ��졧{$name}</font>��ǯ�" . format_date($str_date) . '��' . format_date($end_date);
    } else {
        if($div == 'NKCT') {
            $search = sprintf("where act_date>=%d and act_date<=%d and a.div='C' and (m.tnk_tana LIKE '%s' OR uke_no LIKE '%s' OR uke_no LIKE '%s')", $str_date, $end_date, '8%', 'Z%', 'H%');
            $caption_title = "��������{$div}��ǯ�" . format_date($str_date) . '��' . format_date($end_date);
        } elseif($div == 'NKT') {
            $search = sprintf("where act_date>=%d and act_date<=%d and a.div='L' and (m.tnk_tana LIKE '%s' OR uke_no LIKE '%s' OR uke_no LIKE '%s')", $str_date, $end_date, '8%', 'Z%', 'H%');
            $caption_title = "��������{$div}��ǯ�" . format_date($str_date) . '��' . format_date($end_date);
        /* CC���ʽ����Ѥ���CC���ʤ�ȴ���Ф��褦 �縵��profit_loss_pl_act_save.php���ѹ�
        } elseif($div == 'T') {
            $search = sprintf("where act_date>=%d and act_date<=%d and (a.div='%s' or (a.div<>'T' and a.div<>'C' and a.parts_no like '%s' and (c.miccc = 'E' or c.miccc IS NULL)))", $str_date, $end_date, $div, 'T%');
            $caption_title = "��������{$div}��ǯ�" . format_date($str_date) . '��' . format_date($end_date);
        */
        } elseif($div == 'T') {
            // ��ġ�����
            //$search = sprintf("where act_date>=%d and act_date<=%d and (a.div='%s' or (a.div<>'T' and a.div<>'C' and a.parts_no like '%s'))", $str_date, $end_date, $div, 'T%');
            // �ġ���ʤ���
            //$search = sprintf("where act_date>=%d and act_date<=%d and a.div='%s'", $str_date, $end_date, $div);
            // �����ġ�����
            $search = sprintf("where act_date>=%d and act_date<=%d and (a.div='%s' or (a.div<>'T' and a.div<>'C' and a.parts_no like '%s' and ( mepnt like 'ADR%%' or mepnt like 'L-25%%' )))", $str_date, $end_date, $div, 'T%');
            $caption_title = "��������{$div}��ǯ�" . format_date($str_date) . '��' . format_date($end_date); 
        } elseif($div == 'L') {
            //$search = sprintf("where act_date>=%d and act_date<=%d and a.div='%s' and a.parts_no not like '%s'", $str_date, $end_date, $div, 'T%');
            //$search = sprintf("where act_date>=%d and act_date<=%d and a.div='%s' and (mepnt not like 'ADR%%' or mepnt not like 'L-25%%')", $str_date, $end_date, $div);
            $search = sprintf("where act_date>=%d and act_date<=%d and a.div='%s' and (mepnt is NULL or mepnt not like 'ADR%%' and mepnt not like 'L-25%%')", $str_date, $end_date, $div);
            //$search = sprintf("where act_date>=%d and act_date<=%d and a.div='%s'", $str_date, $end_date, $div);
            $caption_title = "��������{$div}��ǯ�" . format_date($str_date) . '��' . format_date($end_date);
        } elseif($div == 'D') {
            $search = sprintf("where act_date>=%d and act_date<=%d and a.div='C' and ((kouji_no NOT like 'SC%%') or (kouji_no IS NULL))", $str_date, $end_date, $div);
            $caption_title = "��������Cɸ�ࡡǯ�" . format_date($str_date) . '��' . format_date($end_date);
        } elseif($div == 'S') {
            $search = sprintf("where act_date>=%d and act_date<=%d and a.div='C' and kouji_no like 'SC%%'", $str_date, $end_date, $div);
            $caption_title = "��������C����ǯ�" . format_date($str_date) . '��' . format_date($end_date);
        } else {
            $search = sprintf("where act_date>=%d and act_date<=%d and a.div='%s'", $str_date, $end_date, $div);
            $caption_title = "��������{$div}��ǯ�" . format_date($str_date) . '��' . format_date($end_date);
        }
    }
} else {
    if ($vendor != '') {
        $search = sprintf("where act_date>=%d and act_date<=%d and vendor='%s'", $str_date, $end_date, $vendor);
        $query = "select trim(substr(name, 1, 20)) from vendor_master where vendor='{$vendor}' limit 1";
        if (getUniResult($query, $name) <= 0) {
            $name = '�ޥ�����̤��Ͽ';
        }
        $caption_title = "<font color='blue'>���Ϲ��졧{$name}</font>��ǯ�" . format_date($str_date) . '��' . format_date($end_date);
    } else {
        $search = sprintf("where act_date>=%d and act_date<=%d", $str_date, $end_date);
        $caption_title = 'ǯ�' . format_date($str_date) . '��' . format_date($end_date);
    }
}
///// ��� ���� ������ɲ�
if (trim($kamoku) != '') {
    $search .= " and kamoku = {$kamoku}";
}

//////////// ��ץ쥳���ɿ�����     (�оݥǡ����κ������ڡ�������˻���)
//$query = sprintf('select count(*), sum(Uround(order_price * siharai,0)), sum(genpin), sum(siharai) from act_payable as a LEFT OUTER JOIN parts_stock_master AS m ON (m.parts_no=a.parts_no) LEFT OUTER JOIN order_plan AS o USING(sei_no) LEFT OUTER JOIN miccc AS c ON (c.mipn=a.parts_no) %s', $search);
$query = sprintf('select count(*), sum(Uround(order_price * siharai,0)), sum(genpin), sum(siharai) from act_payable as a LEFT OUTER JOIN parts_stock_master AS m ON (m.parts_no=a.parts_no) LEFT OUTER JOIN order_plan AS o USING(sei_no) LEFT OUTER JOIN miitem ON (mipn = a.parts_no) %s', $search);
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
            vendor      as ȯ����,          -- 17
            o.kouji_no  as �����ֹ�         -- 18
        FROM
            act_payable AS a
        LEFT OUTER JOIN
            vendor_master USING(vendor)
        LEFT OUTER JOIN
            miitem ON (parts_no = mipn)
        LEFT OUTER JOIN
            parts_stock_master AS m ON (m.parts_no=a.parts_no)
        LEFT OUTER JOIN
            order_plan AS o USING(sei_no)
        LEFT OUTER JOIN 
            miccc AS c ON (c.mipn=a.parts_no)
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

// ��������CSV�����Ѥν������
// �ե�����̾�����ܸ��Ĥ���ȼ����Ϥ��ǥ��顼�ˤʤ�Τǰ���ѻ����ѹ�
$act_name = "";                             // �����
if ($div == " ") $act_name = "ALL";
if ($div == "") $act_name = "ALL";
if ($div == "C") $act_name = "C-all";
if ($div == "D") $act_name = "C-hyo";
if ($div == "S") $act_name = "C-toku";
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
                        <a href='act_payable_csv.php?csvname=<?php echo $outputFile ?>&csvsearch=<?php echo $csv_search ?>&csvvendor=<?php echo $vendor ?>'>
                            CSV����
                        </a>
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
                        echo "<tr onMouseOver=\"style.background='#ceffce'\" onMouseOut=\"style.background='#d6d3ce'\">\n";
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
                                echo "<td class='winbox' nowrap align='center'><span class='pt11b'><a href='", $menu->out_action('ñ������ɽ��'), "?parts_no=", urlencode("{$res[$r][$i]}"), "&lot_cost=", urlencode("{$res[$r][9]}"), "&uke_date={$res[$r][1]}&vendor={$res[$r][17]}&sei_no={$res[$r][13]}&material=1&str_date={$str_date}&end_date={$end_date}#mark'>{$res[$r][$i]}</a></span></td>\n";
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
