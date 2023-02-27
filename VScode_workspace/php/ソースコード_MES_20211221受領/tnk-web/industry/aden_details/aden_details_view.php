<?php
//////////////////////////////////////////////////////////////////////////////
// A�������ξȲ� �� �����å���  ������ UKWLIB/W#MIADIMDE                    //
// Copyright (C) 2016-2017 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2016/03/25 Created   aden_details_view.php                               //
// 2017/06/14 �ײ�No.�˰������ʹ���ɽ�ؤΥ�󥯤��ɲ�                       //
// 2017/08/10 �ײ贰λ�ѡ�̤��λ�ξ����ɲ�                                //
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
$menu->set_site(30, 99);                    // site_index=40(������˥塼) site_id=10(��ݼ���)
////////////// �꥿���󥢥ɥ쥹����
// $menu->set_RetUrl(INDUST_MENU);             // �̾�ϻ��ꤹ��ɬ�פϤʤ�
//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title('�� �� �� �� �� �� ��');
//////////// ɽ�������     ���Υ��å��ǽ������뤿�ᤳ���Ǥϻ��Ѥ��ʤ�
// $menu->set_caption('������ɬ�פʾ��������������򤷤Ʋ�������');
//////////// �ƽ����action̾�ȥ��ɥ쥹����
$menu->set_action('ñ������ɽ��',   INDUST . 'parts/parts_cost_view.php');
$menu->set_action('�������Ȳ�',   INDUST . 'material/materialCost_view_assy.php');
$menu->set_action('��������ɽ��ɽ��',   INDUST . 'parts/allocate_config/allo_conf_parts_Main.php');

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
if (isset($_REQUEST['finish_del'])) {
    $finish_del = $_REQUEST['finish_del'];
    $_SESSION['payable_finishdel'] = $finish_del;
} else {
    if (isset($_SESSION['payable_finishdel'])) {
        $finish_del = $_SESSION['payable_finishdel'];
    } else {
        $finish_del = ' ';
    }
}
if (isset($_REQUEST['deli_com'])) {
    $deli_com = $_REQUEST['deli_com'];
    $_SESSION['payable_delicom'] = $deli_com;
} else {
    if (isset($_SESSION['payable_delicom'])) {
        $deli_com = $_SESSION['payable_delicom'];
    } else {
        $deli_com = ' ';
    }
}
if (isset($_REQUEST['answer'])) {
    $answer = $_REQUEST['answer'];
    $_SESSION['payable_answer'] = $answer;
} else {
    if (isset($_SESSION['payable_answer'])) {
        $answer = $_SESSION['payable_answer'];
    } else {
        $answer = ' ';
    }
}
if (isset($_REQUEST['finish'])) {
    $finish = $_REQUEST['finish'];
    $_SESSION['payable_finish'] = $finish;
} else {
    if (isset($_SESSION['payable_finish'])) {
        $finish = $_SESSION['payable_finish'];
    } else {
        $finish = ' ';
    }
}
if (isset($_REQUEST['kouji_no'])) {
    $kouji_no = $_REQUEST['kouji_no'];
    $_SESSION['payable_koujino'] = $kouji_no;
} else {
    if (isset($_SESSION['payable_koujino'])) {
        $kouji_no = $_SESSION['payable_koujino'];
    } else {
        $kouji_no = ' ';
    }
}
if (isset($_REQUEST['order'])) {
    $order = $_REQUEST['order'];
    $_SESSION['payable_order'] = $order;
} else {
    if (isset($_SESSION['payable_order'])) {
        $order = $_SESSION['payable_order'];
    } else {
        $order = ' ';
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
if (isset($_REQUEST['lt_str_date'])) {
    $lt_str_date = $_REQUEST['lt_str_date'];
    $_SESSION['paya_ltstrdate'] = $lt_str_date;
    $session->add('lt_str_date', $lt_str_date);
} elseif ($session->get('lt_str_date') != '') {
    $lt_str_date = $session->get('lt_str_date');
    $_SESSION['paya_ltstrdate'] = $lt_str_date;
} else {
    $lt_str_date = '';
}
if (isset($_REQUEST['lt_end_date'])) {
    $lt_end_date = $_REQUEST['lt_end_date'];
    $_SESSION['paya_ltenddate'] = $lt_end_date;
    $session->add('lt_end_date', $lt_end_date);
} elseif ($session->get('lt_end_date') != '') {
    $lt_end_date = $session->get('lt_end_date');
    $_SESSION['paya_ltenddate'] = $lt_end_date;
} else {
    $lt_end_date = '';
}
if (isset($_REQUEST['str_date'])) {
    $str_date = $_REQUEST['str_date'];
    $_SESSION['paya_strdate'] = $str_date;
    $session->add('str_date', $str_date);
//} elseif (isset($_SESSION['paya_strdate'])) {
//    $str_date = $_SESSION['paya_strdate'];
//    $session->add('str_date', $str_date);
//    $str_date = '20150901';
} elseif ($session->get('str_date') != '') {
    $str_date = $session->get('str_date');
    $_SESSION['paya_strdate'] = $str_date;
} else {
    $year  = date('Y') - 5; // ��ǯ������
    $month = date('m');
    $str_date = $year . $month . '01';
}
if (isset($_REQUEST['end_date'])) {
    $end_date = $_REQUEST['end_date'];
    $_SESSION['paya_enddate'] = $end_date;
    $session->add('end_date', $end_date);
//} elseif (isset($_SESSION['paya_enddate'])) {
//    $end_date = $_SESSION['paya_enddate'];
//    $session->add('end_date', $end_date);
} elseif ($session->get('end_date') != '') {
    $end_date = $session->get('end_date');
    $_SESSION['paya_enddate'] = $end_date;
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
        $paya_page = 23;
    }
}

//////////// ���ǤιԿ�
define('PAGE', $paya_page);

// �ǥե���Ȥθ������ȥ����ȥ������
$search        = "where a.receive_day>={$str_date} and a.receive_day<={$end_date}";
$caption_title = 'ǯ�' . format_date($str_date) . '��' . format_date($end_date);
$caption_flg   = 0;             // �����ȥ�β��ԥ����ߥ󥰤�פ�٤Υե饰

// ASSY No.�λ��꤬������
if ($parts_no != '') {
    $search .= " and a.parts_no='{$parts_no}'";
    $query = "select trim(substr(midsc,1,18)) from miitem where mipn='{$parts_no}' limit 1";
    if (getUniResult($query, $name) <= 0) {
        $name = '�ޥ�����̤��Ͽ';
    }
    $caption_title .= "�������ֹ桧{$parts_no}��<font color='blue'>����̾��{$name}</font>";
    $caption_flg    = 1;
}

// A�����������λ��꤬������
if ($answer != ' ') {
    if ($answer == 'Y') {
        $search .= " and answer_day<>0";
        $caption_title .= "������������<font color='blue'>������</font>";
        if ($caption_flg == 1) {
            $caption_flg = 9;
        } else {
            $caption_flg = 2;
        }
    } else {
        $search .= " and answer_day=0";
        $caption_title .= "������������<font color='red'>̤����</font>";
        if ($caption_flg == 1) {
            $caption_flg = 9;
        } else {
            $caption_flg = 2;
        }
    }
}

// �ײ贰λ�����λ��꤬������
if ($finish != ' ') {
    if ($finish == 'Y') {
        $search .= " and (finish_day IS NOT NULL or spare1='U')";
        $caption_title .= "����λ������<font color='blue'>��λ��</font>";
        if ($caption_flg == 1) {
            $caption_flg = 9;
        } else {
            $caption_flg = 2;
        }
    } else {
        $search .= " and finish_day IS NULL and spare1<>'U'";
        $caption_title .= "����λ������<font color='red'>̤��λ</font>";
        if ($caption_flg == 1) {
            $caption_flg = 9;
        } else {
            $caption_flg = 2;
        }
    }
}

// Ǽ�������Ȥλ��꤬������
if ($deli_com != ' ') {
    if ($deli_com == 'Y') {
        $search .= " and espoir_deli=delivery";
        if ($caption_flg == 0) {
            $caption_flg = 2;
            $caption_title .= "��Ǽ�����ᡧ��˾�̤�";
        } elseif ($caption_flg == 1) {
            $caption_flg = 9;
            $caption_title .= "��Ǽ�����ᡧ��˾�̤�";
        } elseif ($caption_flg == 2) {
            $caption_flg = 3;
            $caption_title .= "��Ǽ�����ᡧ��˾�̤�";
        } else {
            $caption_flg = 10;
            $caption_title .= "��<BR>Ǽ�����ᡧ��˾�̤�";
        }
    } elseif ($deli_com == 'N') {
        $search .= " and deli_com = 0 and espoir_deli<>delivery";
        if ($caption_flg == 0) {
            $caption_flg = 2;
            $caption_title .= "��Ǽ�����ᡧ̤����";
        } elseif ($caption_flg == 1) {
            $caption_flg = 9;
            $caption_title .= "��Ǽ�����ᡧ̤����";
        } elseif ($caption_flg == 2) {
            $caption_flg = 3;
            $caption_title .= "��Ǽ�����ᡧ̤����";
        } else {
            $caption_flg = 10;
            $caption_title .= "��<BR>Ǽ�����ᡧ̤����";
        }
    } elseif ($deli_com == '1') {
        $search .= " and deli_com = 1";
        if ($caption_flg == 0) {
            $caption_flg = 2;
            $caption_title .= "��Ǽ�����ᡧ�����٤�";
        } elseif ($caption_flg == 1) {
            $caption_flg = 9;
            $caption_title .= "��Ǽ�����ᡧ�����٤�";
        } elseif ($caption_flg == 2) {
            $caption_flg = 3;
            $caption_title .= "��Ǽ�����ᡧ�����٤�";
        } else {
            $caption_flg = 10;
            $caption_title .= "��<BR>Ǽ�����ᡧ�����٤�";
        }
    } elseif ($deli_com == '2') {
        $search .= " and deli_com = 2";
        if ($caption_flg == 0) {
            $caption_flg = 2;
            $caption_title .= "��Ǽ�����ᡧ�߷��ѹ�";
        } elseif ($caption_flg == 1) {
            $caption_flg = 9;
            $caption_title .= "��Ǽ�����ᡧ�߷��ѹ�";
        } elseif ($caption_flg == 2) {
            $caption_flg = 3;
            $caption_title .= "��Ǽ�����ᡧ�߷��ѹ�";
        } else {
            $caption_flg = 10;
            $caption_title .= "��<BR>Ǽ�����ᡧ�߷��ѹ�";
        }
    } elseif ($deli_com == '3') {
        $search .= " and deli_com = 3";
        if ($caption_flg == 0) {
            $caption_flg = 2;
            $caption_title .= "��Ǽ�����ᡧL/T��­";
        } elseif ($caption_flg == 1) {
            $caption_flg = 9;
            $caption_title .= "��Ǽ�����ᡧL/T��­";
        } elseif ($caption_flg == 2) {
            $caption_flg = 3;
            $caption_title .= "��Ǽ�����ᡧL/T��­";
        } else {
            $caption_flg = 10;
            $caption_title .= "��<BR>Ǽ�����ᡧL/T��­";
        }
    } elseif ($deli_com == '4') {
        $search .= " and deli_com = 4";
        if ($caption_flg == 0) {
            $caption_flg = 2;
            $caption_title .= "��Ǽ�����ᡧ�����٤�";
        } elseif ($caption_flg == 1) {
            $caption_flg = 9;
            $caption_title .= "��Ǽ�����ᡧ�����٤�";
        } elseif ($caption_flg == 2) {
            $caption_flg = 3;
            $caption_title .= "��Ǽ�����ᡧ�����٤�";
        } else {
            $caption_flg = 10;
            $caption_title .= "��<BR>Ǽ�����ᡧ�����٤�";
        }
    } elseif ($deli_com == '5') {
        $search .= " and deli_com = 5";
        if ($caption_flg == 0) {
            $caption_flg = 2;
            $caption_title .= "��Ǽ�����ᡧ����¾";
        } elseif ($caption_flg == 1) {
            $caption_flg = 9;
            $caption_title .= "��Ǽ�����ᡧ����¾";
        } elseif ($caption_flg == 2) {
            $caption_flg = 3;
            $caption_title .= "��Ǽ�����ᡧ����¾";
        } else {
            $caption_flg = 10;
            $caption_title .= "��<BR>Ǽ�����ᡧ����¾";
        }
    }
}

// ���֤λ��꤬������
if ($kouji_no != ' ') {
    if ($kouji_no == 'S') {
        $search .= " and kouji_no LIKE 'SC%'";
        if ($caption_flg == 0) {
            $caption_flg = 2;
            $caption_title .= "�������ֹ桧<font color='blue'>SC�Τ�</font>";
        } elseif ($caption_flg == 1) {
            $caption_flg = 9;
            $caption_title .= "�������ֹ桧<font color='blue'>SC�Τ�</font>";
        } elseif ($caption_flg == 2) {
            $caption_flg = 3;
            $caption_title .= "�������ֹ桧<font color='blue'>SC�Τ�</font>";
        } elseif ($caption_flg == 3) {
            $caption_flg = 4;
            $caption_title .= "�������ֹ桧<font color='blue'>SC�Τ�</font>";
        } elseif ($caption_flg == 9) {
            $caption_flg = 10;
            $caption_title .= "<BR>�����ֹ桧<font color='blue'>SC�Τ�</font>";
        } else {
            $caption_flg = 11;
            $caption_title .= "�������ֹ桧<font color='blue'>SC�Τ�</font>";
        }
    } elseif ($kouji_no == 'C') {
        $search .= " and kouji_no LIKE 'CQ%'";
        if ($caption_flg == 0) {
            $caption_flg = 2;
            $caption_title .= "�������ֹ桧<font color='blue'>CQ�Τ�</font>";
        } elseif ($caption_flg == 1) {
            $caption_flg = 9;
            $caption_title .= "�������ֹ桧<font color='blue'>CQ�Τ�</font>";
        } elseif ($caption_flg == 2) {
            $caption_flg = 3;
            $caption_title .= "�������ֹ桧<font color='blue'>CQ�Τ�</font>";
        } elseif ($caption_flg == 3) {
            $caption_flg = 4;
            $caption_title .= "�������ֹ桧<font color='blue'>CQ�Τ�</font>";
        } elseif ($caption_flg == 9) {
            $caption_flg = 10;
            $caption_title .= "<BR>�����ֹ桧<font color='blue'>CQ�Τ�</font>";
        } else {
            $caption_flg = 11;
            $caption_title .= "�������ֹ桧<font color='blue'>CQ�Τ�</font>";
        }
    } elseif ($kouji_no == 'SCQ') {
        $search .= " and (kouji_no LIKE 'SC%' or kouji_no LIKE 'CQ%')";
        if ($caption_flg == 0) {
            $caption_flg = 2;
            $caption_title .= "�������ֹ桧<font color='blue'>SC+CQ</font>";
        } elseif ($caption_flg == 1) {
            $caption_flg = 9;
            $caption_title .= "�������ֹ桧<font color='blue'>SC+CQ</font>";
        } elseif ($caption_flg == 2) {
            $caption_flg = 3;
            $caption_title .= "�������ֹ桧<font color='blue'>SC+CQ</font>";
        } elseif ($caption_flg == 3) {
            $caption_flg = 4;
            $caption_title .= "�������ֹ桧<font color='blue'>SC+CQ</font>";
        } elseif ($caption_flg == 9) {
            $caption_flg = 10;
            $caption_title .= "<BR>�����ֹ桧<font color='blue'>SC+CQ</font>";
        } else {
            $caption_flg = 11;
            $caption_title .= "�������ֹ桧<font color='blue'>SC+CQ</font>";
        }
    } elseif ($kouji_no == 'N') {
        $search .= " and kouji_no =''";
        if ($caption_flg == 0) {
            $caption_flg = 2;
            $caption_title .= "�������ֹ桧<font color='blue'>���֤ʤ�</font>";
        } elseif ($caption_flg == 1) {
            $caption_flg = 9;
            $caption_title .= "�������ֹ桧<font color='blue'>���֤ʤ�</font>";
        } elseif ($caption_flg == 2) {
            $caption_flg = 3;
            $caption_title .= "�������ֹ桧<font color='blue'>���֤ʤ�</font>";
        } elseif ($caption_flg == 3) {
            $caption_flg = 4;
            $caption_title .= "�������ֹ桧<font color='blue'>���֤ʤ�</font>";
        } elseif ($caption_flg == 9) {
            $caption_flg = 10;
            $caption_title .= "<BR>�����ֹ桧<font color='blue'>���֤ʤ�</font>";
        } else {
            $caption_flg = 11;
            $caption_title .= "�������ֹ桧<font color='blue'>���֤ʤ�</font>";
        }
    }
}

// Ǽ��L/T���λ��꤬������
if ($lt_str_date !='') {
    $search .= " and a.lt_diff>={$lt_str_date} and a.lt_diff<={$lt_end_date}";
    if ($caption_flg == 0) {
        $caption_flg = 2;
        $caption_title .= "��L/T����" . $lt_str_date . '��' . $lt_end_date;
    } elseif ($caption_flg == 1) {
        $caption_flg = 9;
        $caption_title .= "��L/T����" . $lt_str_date . '��' . $lt_end_date;
    } elseif($caption_flg == 2) {
        $caption_flg = 3;
        $caption_title .= "��L/T����" . $lt_str_date . '��' . $lt_end_date;
    } elseif($caption_flg == 3) {
        $caption_flg = 4;
        $caption_title .= "��L/T����" . $lt_str_date . '��' . $lt_end_date;
    } elseif($caption_flg == 4) {
        $caption_flg = 10;
        $caption_title .= "<BR>L/T����" . $lt_str_date . '��' . $lt_end_date;
    } elseif($caption_flg == 9) {
        $caption_flg = 10;
        $caption_title .= "<BR>L/T����" . $lt_str_date . '��' . $lt_end_date;
    } else {
        $caption_flg = 11;
        $caption_title .= "��L/T����" . $lt_str_date . '��' . $lt_end_date;
    }
}

// Ǽ���٤�λ��꤬������
if ($finish_del !='') {
    if ($finish_del == 'D') {
        $search .= " and (finish_del > 0 OR (a.delivery < to_char(current_date,'YYYYMMDD') and spare1 = 'B'))";
        if ($caption_flg == 0) {
            $caption_flg = 2;
            $caption_title .= "��Ǽ���٤졧Ǽ���٤�";
        } elseif ($caption_flg == 1) {
            $caption_flg = 9;
            $caption_title .= "��Ǽ���٤졧Ǽ���٤�";
        } elseif($caption_flg == 2) {
            $caption_flg = 3;
            $caption_title .= "��Ǽ���٤졧Ǽ���٤�";
        } elseif ($caption_flg == 3) {
            $caption_title .= "��Ǽ���٤졧Ǽ���٤�";
            $caption_flg = 4;
        } elseif ($caption_flg == 10) {
            $caption_title .= "��Ǽ���٤졧Ǽ���٤�";
            $caption_flg = 11;
        } elseif ($caption_flg == 11) {
            $caption_title .= "��Ǽ���٤졧Ǽ���٤�";
            $caption_flg = 12;
        } elseif ($caption_flg == 4) {
            $caption_title .= "<BR>Ǽ���٤졧Ǽ���٤�";
            $caption_flg = 10;
        } elseif ($caption_flg == 9) {
            $caption_title .= "<BR>Ǽ���٤졧Ǽ���٤�";
            $caption_flg = 10;
        } else {
            $caption_title .= "��Ǽ���٤졧Ǽ���٤�";
        }
    } elseif ($finish_del == 'Y') {
        $search .= " and finish_del = 0 and spare1 <> 'B'";
        if ($caption_flg == 0) {
            $caption_flg = 2;
            $caption_title .= "��Ǽ���٤졧Ǽ���̤�";
        } elseif ($caption_flg == 1) {
            $caption_flg = 9;
            $caption_title .= "��Ǽ���٤졧Ǽ���̤�";
        } elseif($caption_flg == 2) {
            $caption_flg = 3;
            $caption_title .= "��Ǽ���٤졧Ǽ���̤�";
        } elseif ($caption_flg == 3) {
            $caption_title .= "��Ǽ���٤졧Ǽ���̤�";
            $caption_flg = 4;
        } elseif ($caption_flg == 10) {
            $caption_title .= "��Ǽ���٤졧Ǽ���̤�";
            $caption_flg = 11;
        } elseif ($caption_flg == 11) {
            $caption_title .= "��Ǽ���٤졧Ǽ���̤�";
            $caption_flg = 12;
        } elseif ($caption_flg == 4) {
            $caption_title .= "<BR>Ǽ���٤졧Ǽ���̤�";
            $caption_flg = 10;
        } elseif ($caption_flg == 9) {
            $caption_title .= "<BR>Ǽ���٤졧Ǽ���̤�";
            $caption_flg = 10;
        } else {
            $caption_title .= "��Ǽ���٤졧Ǽ���̤�";
        }
    } elseif ($finish_del == 'A') {
        $search .= " and finish_del < 0 and spare1 <> 'B'";
        if ($caption_flg == 0) {
            $caption_flg = 2;
            $caption_title .= "��Ǽ���٤졧Ǽ������";
        } elseif ($caption_flg == 1) {
            $caption_flg = 9;
            $caption_title .= "��Ǽ���٤졧Ǽ������";
        } elseif($caption_flg == 2) {
            $caption_flg = 3;
            $caption_title .= "��Ǽ���٤졧Ǽ������";
        } elseif ($caption_flg == 3) {
            $caption_title .= "��Ǽ���٤졧Ǽ������";
            $caption_flg = 4;
        } elseif ($caption_flg == 10) {
            $caption_title .= "��Ǽ���٤졧Ǽ������";
            $caption_flg = 11;
        } elseif ($caption_flg == 11) {
            $caption_title .= "��Ǽ���٤졧Ǽ������";
            $caption_flg = 12;
        } elseif ($caption_flg == 4) {
            $caption_title .= "<BR>Ǽ���٤졧Ǽ������";
            $caption_flg = 10;
        } elseif ($caption_flg == 9) {
            $caption_title .= "<BR>Ǽ���٤졧Ǽ������";
            $caption_flg = 10;
        } else {
            $caption_title .= "��Ǽ���٤졧Ǽ������";
        }
    }
}

// ɽ�����֤�����

if ($order == ' ') {            // �ǥե���� A��������(��) �� ASSY No. �� ��˾Ǽ��(��)
    $order = "receive_day ASC, parts_no ASC, espoir_deli ASC";
} elseif ($order == '1') {      // ��˾Ǽ���� ��˾Ǽ��(��) �� A��������(��) �� ASSY No.
    $order = "espoir_deli ASC, receive_day ASC, parts_no ASC";
} elseif ($order == '2') {      // L/T����    L/T��(��) �� A��������(��) �� ASSY No. �� ��˾Ǽ��(��)
    $order = "lt_diff DESC, receive_day ASC, parts_no ASC, espoir_deli ASC";
} elseif ($order == '3') {      // �����٤�� �����٤�(��) �� A��������(��) �� ASSY No. �� ��˾Ǽ��(��)
    $order = "finish_del DESC, receive_day ASC, parts_no ASC, espoir_deli ASC";
}

//////////// ��ץ쥳���ɿ�����     (�оݥǡ����κ������ڡ�������˻���)
$query = sprintf('select count(*) from aden_details_master as a %s', $search);
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
    if ($caption_flg == 4) {
        $caption_title .= '<BR>��׷����' . number_format($res_max[0][0]);   // �����ݷ���򥭥�ץ���󥿥��ȥ�˥��å�
    } elseif ($caption_flg == 9) {
        $caption_title .= '<BR>��׷����' . number_format($res_max[0][0]);   // �����ݷ���򥭥�ץ���󥿥��ȥ�˥��å�
    } else {
        $caption_title .= '����׷����' . number_format($res_max[0][0]);   // �����ݷ���򥭥�ץ���󥿥��ȥ�˥��å�
    }
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
            publish_day AS A��ȯ����,       -- 00
            receive_day AS A��������,       -- 01
            aden_no     AS A��No,           -- 02
            parts_no    AS ASSYNo,          -- 03
            substr(sale_name, 1, 20)
                        AS ����̾,          -- 04
            plan_no     AS �ײ�No,          -- 05
            kouji_no    AS SC����,          -- 06
            order_q     AS ����,            -- 07
            espoir_deli AS ��˾Ǽ��,        -- 08
            answer_day  AS A��������,       -- 09
            ans_day_lt  AS A������LT,       -- 10
            delivery    AS ����Ǽ��,        -- 11
            espoir_lt   AS ��˾LT,          -- 12
            ans_lt      AS Ǽ����LT,        -- 13
            lt_diff     AS LT��,            -- 14
            order_price AS �������,        -- 15
            finish_day  AS �´�����,        -- 16
            finish_del  AS �����٤�,        -- 17
            deli_com    AS Ǽ��������,    -- 18
            comment     AS ����,            -- 19
            spare1      AS ʬǼ��ʬ         -- 20
        FROM
            aden_details_master AS a
        %s 
        ORDER BY %s
        OFFSET %d LIMIT %d
    ", $search, $order, $offset, PAGE);       // ���� $search �Ǹ���
$res   = array();
$field = array();
if (($rows = getResultWithField2($query, $field, $res)) <= 0) {
    $_SESSION['s_sysmsg'] .= 'A���ǡ���������ޤ���';
    if (isset($_REQUEST['material'])) {
        header('Location: ' . H_WEB_HOST . $menu->out_RetUrl() . '?material=' . $_REQUEST['material']);    // ľ���θƽи������
    } else {
        header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // ľ���θƽи������
    }
    exit();
} else {
    $num = count($field);       // �ե�����ɿ�����
    $num = $num - 1;            // ʬǼ��ʬ����ɽ���Τ���
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
// 'YYYYMMDD'�ե����ޥåȤΣ�������դ�YYYY/MM/DD��10��˥ե����ޥåȤ����֤���
function format_date10($date8)
{
    if (0 == $date8) {
        $date8 = '----/--/--';    
    }
    if (8 == strlen($date8)) {
        $nen   = substr($date8,0,4);
        $tsuki = substr($date8,4,2);
        $hi    = substr($date8,6,2);
        return $nen . '/' . $tsuki . '/' . $hi;
    } else {
        return FALSE;
    }
}
// ��������CSV�����Ѥν������
// �ե�����̾�����ܸ��Ĥ���ȼ����Ϥ��ǥ��顼�ˤʤ�Τǰ���ѻ����ѹ�
if ($div == " ") $act_name = "ALL";
if ($div == "C") $act_name = "C-all";
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
$outputFile = $str_date . '-' . $end_date;

/////////// HTML Header ����Ϥ��ƥ���å��������
$menu->out_html_header();


$uid_sid   = $_SESSION['User_ID'];
$query_sid = "SELECT sid FROM user_detailes WHERE uid='$uid_sid'";
$res_sid   = array();
getResult($query_sid,$res_sid);
$sid_sid   = $res_sid[0][0];

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
.pt9bu {
    font-size:   9pt;
    font-weight: normal;
    font-family: monospace;
    font-color:  blue;
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
                        <a href='aden_details_csv.php?csvname=<?php echo $outputFile ?>&csvsearch=<?php echo $csv_search ?>&order=<?php echo $order ?>'>
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
                    if ($i==0) {
                ?>
                        <th class='winbox' nowrap>A��<BR>ȯ����</th>
                <?php
                    } elseif ($i==1) {
                ?>
                        <th class='winbox' nowrap>A��<BR>������</th>
                <?php
                    } elseif ($i==2) {
                ?>
                        <th class='winbox' nowrap>A��No</th>
                <?php
                    } elseif ($i==3) {
                ?>
                        <th class='winbox' nowrap>ASSY No</th>
                <?php
                    } elseif ($i==5) {
                ?>
                        <th class='winbox' nowrap>�ײ�No</th>
                <?php
                    } elseif ($i==6) {
                ?>
                        <th class='winbox' nowrap>SC����</th>
                <?php
                    } elseif ($i==9) {
                ?>
                        <th class='winbox' nowrap>A��<BR>������</th>
                <?php
                    } elseif ($i==10) {
                ?>
                        <th class='winbox' nowrap>A������<BR>L/T</th>
                <?php
                    } elseif ($i==12) {
                ?>
                        <th class='winbox' nowrap>��˾<BR>L/T</th>
                <?php
                    } elseif ($i==13) {
                ?>
                        <th class='winbox' nowrap>Ǽ����<BR>L/T</th>
                <?php
                    } elseif ($i==14) {
                ?>
                        <th class='winbox' nowrap>L/T��</th>
                <?php
                    } elseif ($i==17) {
                ?>
                        <th class='winbox' nowrap>����<BR>�٤�</th>
                <?php
                    } else {
                ?>
                        <th class='winbox' nowrap><?= $field[$i] ?></th>
                <?php
                    }
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
                    echo "    <td class='winbox' nowrap align='right'><span class='pt10b'>", ($r + $offset + 1), "</span></td>    <!-- �ԥʥ�С���ɽ�� -->\n";
                    for ($i=0; $i<$num; $i++) {         // �쥳���ɿ�ʬ���֤�
                        switch ($i) {
                        case  0:        // A��ȯ����
                            echo "<td class='winbox' nowrap align='center'><span class='pt9'>", format_date10($res[$r][$i]), "</span></td>\n";
                            break;
                        case  1:        // A��������
                            echo "<td class='winbox' nowrap align='center'><span class='pt9'>", format_date10($res[$r][$i]), "</span></td>\n";
                            break;
                        case  3:        // �����ֹ�
                            if (trim($res[$r][$i]) == '') {
                                echo "<td class='winbox' nowrap align='center'>��</td>\n";
                            } else {
                                if ($sid_sid != '95') {
                                    echo "<td class='winbox' nowrap align='center'><span class='pt11b'><a href='", $menu->out_action('�������Ȳ�'), "?assy=", urlencode("{$res[$r][$i]}"), "&plan_no=", urlencode("{$res[$r][16]}"), "#mark'>{$res[$r][$i]}</a></span></td>\n";
                                } else {
                                    echo "<td class='winbox' nowrap align='center'><span class='pt11b'>{$res[$r][$i]}</span></td>\n";
                                }
                            }
                            break;
                        case  4:        // ����̾
                            $res[$r][$i] = mb_convert_kana($res[$r][$i], 'k');
                            $res[$r][$i] = mb_substr($res[$r][$i], 0, 20);
                            echo "<td class='winbox' nowrap align='left'><span class='pt9'>{$res[$r][$i]}</span></td>\n";
                            break;
                        case  5:        // �ײ�No.
                            if (trim($res[$r][$i]) == '') {
                                echo "<td class='winbox' nowrap align='center'>��</td>\n";
                            } else {
                                if ($sid_sid != '95') {
                                    if (trim($res[$r][6]) == '') {
                                        echo "<td class='winbox' nowrap align='center'><span class='pt11b'><a href='", $menu->out_action('��������ɽ��ɽ��'), "?plan_no=", urlencode("{$res[$r][$i]}"), "&aden_flg=1#mark'>{$res[$r][$i]}</a></span></td>\n";
                                    } else {
                                        echo "<td class='winbox' nowrap align='center'><span class='pt11b'><a href='", $menu->out_action('��������ɽ��ɽ��'), "?plan_no=", urlencode("{$res[$r][$i]}"), "&sc_no=", urlencode("{$res[$r][6]}"), "&aden_flg=1#mark'>{$res[$r][$i]}</a></span></td>\n";
                                    }
                                } else {
                                    echo "<td class='winbox' nowrap align='center'><span class='pt11b'>{$res[$r][$i]}</span></td>\n";
                                }
                            }
                            break;
                        //case  6:         // SC����
                        case  7:         // ����
                            echo "<td class='winbox' nowrap align='right'><span class='pt9'>" . number_format($res[$r][$i], 0) . "</span></td>\n";
                            break;
                        case  8:        // ��˾Ǽ��
                        case  9:        // A��������
                            if (trim($res[$r][$i]) == 0) {
                                echo "<td class='winbox' nowrap align='center'>��</td>\n";
                            } else {
                                echo "<td class='winbox' nowrap align='center'><span class='pt9'>", format_date10($res[$r][$i]), "</span></td>\n";
                            }
                            break;
                        case 10:        // A������L/T
                            if (trim($res[$r][$i]) == '') {
                                echo "<td class='winbox' nowrap align='right'>��</td>\n";
                            } elseif (trim($res[$r][$i]) == 0) {
                                echo "<td class='winbox' nowrap align='right'><span class='pt9'>0</span></td>\n";
                            } else {
                                echo "<td class='winbox' nowrap align='right'><span class='pt9'>" . number_format($res[$r][$i], 0) . "</span></td>\n";
                            }
                            break;
                        case 11:        // ����Ǽ��
                            if (trim($res[$r][$i]) == 0) {
                                echo "<td class='winbox' nowrap align='center'>��</td>\n";
                            } else {
                                echo "<td class='winbox' nowrap align='center'><span class='pt9'>", format_date10($res[$r][$i]), "</span></td>\n";
                            }
                            break;
                        case 12:        // ��˾L/T
                        case 13:        // ����L/T
                        case 14:        // L/T��
                            if (trim($res[$r][$i]) == '') {
                                echo "<td class='winbox' nowrap align='right'>��</td>\n";
                            } elseif (trim($res[$r][$i]) == 0) {
                                echo "<td class='winbox' nowrap align='right'><span class='pt9'>0</span></td>\n";
                            } else {
                                echo "<td class='winbox' nowrap align='right'><span class='pt9'>" . number_format($res[$r][$i], 0) . "</span></td>\n";
                            }
                            break;
                        case 15:        // �������
                            echo "<td class='winbox' nowrap align='right'><span class='pt9'>" . number_format($res[$r][$i], 2) . "</span></td>\n";
                            break;
                        case 16:        // �´�����
                            if ($res[$r][20] == 'BK' || $res[$r][20] == 'UK' || $res[$r][20] == 'BUK') {
                                echo "<td class='winbox' nowrap align='center'><span class='pt9'><font color='blue'>", format_date10($res[$r][$i]), "</font></span></td>\n";
                            } elseif ($res[$r][20] == 'U') {
                                echo "<td class='winbox' nowrap align='center'><span class='pt9'>����</span></td>\n";
                            } elseif ($res[$r][20] == 'K') {
                                echo "<td class='winbox' nowrap align='center'><span class='pt9'>", format_date10($res[$r][$i]), "</span></td>\n";
                            } elseif ($res[$r][20] == 'B') {
                                echo "<td class='winbox' nowrap align='center'><span class='pt9'><font color='red'>", format_date10($res[$r][$i]), "</font></span></td>\n";
                            } elseif (trim($res[$r][$i]) == 0) {
                                echo "<td class='winbox' nowrap align='center'>��</td>\n";
                            } else {
                                echo "<td class='winbox' nowrap align='center'><span class='pt9'>", format_date10($res[$r][$i]), "</span></td>\n";
                            }
                            break;
                        case 17:        // �����٤�
                            if (trim($res[$r][$i]) == '') {
                                echo "<td class='winbox' nowrap align='right'>��</td>\n";
                            } elseif (trim($res[$r][$i]) == 0) {
                                echo "<td class='winbox' nowrap align='right'><span class='pt9'>0</span></td>\n";
                            } else {
                                echo "<td class='winbox' nowrap align='right'><span class='pt9'>" . number_format($res[$r][$i], 0) . "</span></td>\n";
                            }
                            break;
                        case 18:        // Ǽ��������
                            if ($res[$r][8] == $res[$r][11]) {
                                echo "<td class='winbox' nowrap align='left'><span class='pt9'>��˾�̤�</span></td>\n";
                            } elseif ($res[$r][$i] == 0) {
                                echo "<td class='winbox' nowrap align='left'><span class='pt9'>̤����</span></td>\n";
                            } elseif ($res[$r][$i] == 1) {
                                echo "<td class='winbox' nowrap align='left'><span class='pt9'>�����٤�</span></td>\n";
                            } elseif ($res[$r][$i] == 2) {
                                echo "<td class='winbox' nowrap align='left'><span class='pt9'>�߷��ѹ�</span></td>\n";
                            } elseif ($res[$r][$i] == 3) {
                                echo "<td class='winbox' nowrap align='left'><span class='pt9'>L/T��­</span></td>\n";
                            } elseif ($res[$r][$i] == 4) {
                                echo "<td class='winbox' nowrap align='left'><span class='pt9'>�����٤�</span></td>\n";
                            } elseif ($res[$r][$i] == 5) {
                                echo "<td class='winbox' nowrap align='left'><span class='pt9'>����¾</span></td>\n";
                            } else {
                                echo "<td class='winbox' nowrap align='left'><span class='pt9'>{$res[$r][$i]}</span></td>\n";
                            }
                            break;
                        default:
                            if (trim($res[$r][$i]) == '') $res[$r][$i] = '��';
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
