<?php
//////////////////////////////////////////////////////////////////////////////
// �»�״ط� �������ɽ �ܷ軻»��ɽ ͽ��̵��Ver(Web�˥ǡ������ʤ���)    //
// Copyright (C) 2012-     Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2012/01/17 Created   profit_loss_pl_act_2ki.php                          //
// 2012/01/20 �ץ����δ��� �����å��� ��ư                              //
// 2012/02/13 �裴��Ⱦ���Τ�ɽ����������äƤ����Τ��б�                    //
// 2012/04/18 �裴��Ⱦ���Τ�ɽ����������äƤ����Τ��б��ʣ����ܡ�          //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);    // E_STRICT=2048(php5) E_ALL=2047 debug ��
// ini_set('error_reporting', E_ALL);       // E_ALL='2047' debug ��
// ini_set('display_errors', '1');          // Error ɽ�� ON debug �� ��꡼���女����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��

require_once ('../function.php');           // define.php �� pgsql.php �� require_once ���Ƥ���
require_once ('../tnk_func.php');           // TNK �˰�¸������ʬ�δؿ��� require_once ���Ƥ���
require_once ('../MenuHeader.php');         // TNK ������ menu class
access_log();                               // Script Name �ϼ�ư����

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader(0);                  // ǧ�ڥ����å�0=���̰ʾ� �����=TOP_MENU �����ȥ�̤����
   // �ºݤ�ǧ�ڤ�profit_loss_submit.php�ǹԤäƤ���account_group_check()�����

///// ����������
// $menu->set_site(10, 7);                  // site_index=10(»�ץ�˥塼) site_id=7(�»��)
///// ɽ�������
$menu->set_caption('�������칩��(��)');

///// �о�����
$yyyymm   = $_SESSION['2ki_ym'];
$ki       = Ym_to_tnk($_SESSION['2ki_ym']);
$b_yyyymm = $yyyymm - 100;
$p1_ki    = Ym_to_tnk($b_yyyymm);
///// ����ǯ��λ���
$yyyy = substr($yyyymm, 0,4);
$mm   = substr($yyyymm, 4,2);
if (($mm >= 1) && ($mm <= 3)) {
    $yyyy = ($yyyy - 1);
}
$str_ym   = $yyyy . "04";   // ���� ����ǯ��
$b_str_ym = $str_ym - 100;  // ���� ����ǯ��

///// ��Ⱦ�� ǯ��λ���(ǯ���ڤ��ؤ�뤳�ȤϤʤ��ΤǤ��Τޤޥޥ��ʥ���OK)
$p1_ym = $yyyymm - 1;
$p2_ym = $yyyymm - 2;

///// ɽ���� �����ǯ��λ���YYMM
$yy     = substr($yyyymm, 2,2);  // ����ǯ��yy��
$b_yy   = $yy - 1;               // ����ǯ��yy��
$b2_yy  = $b_yy - 1;             // ����ǯ��yy���裴��Ⱦ��
$mm     = substr($yyyymm, 4,2);  // �ǽ���(mm)
$p1_mm  = substr($p1_ym, 4,2);   // ��Ⱦ������(mm)
$p2_mm  = substr($p2_ym, 4,2);   // ��Ⱦ��������(mm)

///// ����Ⱦ���μ���
$tuki_chk = substr($_SESSION['2ki_ym'],4,2);
if ($tuki_chk == 3) {
    $hanki = '��';
} elseif ($tuki_chk == 6) {
    $hanki = '��';
} elseif ($tuki_chk == 9) {
    $hanki = '��';
} elseif ($tuki_chk == 12) {
    $hanki = '��';
}
///// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
if ($tuki_chk == 3) {
    $menu->set_title("�� {$ki} ����ͽ�������¡��ӡ��桡�ӡ�ɽ");
} else {
    $menu->set_title("�� {$ki} ������{$hanki}��Ⱦ����ͽ�������¡��ӡ��桡�ӡ�ɽ");
}
///// ɽ��ñ�̤��������
if (isset($_POST['keihi_tani'])) {
    $_SESSION['keihi_tani'] = $_POST['keihi_tani'];
    $tani = $_SESSION['keihi_tani'];
} elseif (isset($_SESSION['keihi_tani'])) {
    $tani = $_SESSION['keihi_tani'];
} else {
    $tani = 1000;           // ����� ɽ��ñ�� ���
    $_SESSION['keihi_tani'] = $tani;
}
///// ɽ�� ��������� �������
if (isset($_POST['keihi_keta'])) {
    $_SESSION['keihi_keta'] = $_POST['keihi_keta'];
    $keta = $_SESSION['keihi_keta'];
} elseif (isset($_SESSION['keihi_keta'])) {
    $keta = $_SESSION['keihi_keta'];
} else {
    $keta = 0;              // ����� �������ʲ����
    $_SESSION['keihi_keta'] = $keta;
}

// �裴��Ⱦ���� ��Ⱦ��ǯ��
if ($tuki_chk == 3) {
    $yyyy   = substr($yyyymm, 0,4);
    $b_yyyy = $yyyy - 1;
    $h1_str = $b_yyyy . '04';
    $h1_end = $b_yyyy . '06';
    $h2_str = $b_yyyy . '07';
    $h2_end = $b_yyyy . '09';
    $h3_str = $b_yyyy . '10';
    $h3_end = $b_yyyy . '12';
    $h4_str = $yyyy . '01';
    $h4_end = $yyyy . '03';
}
/********** ���� **********/
if ($tuki_chk == 3) {
        ///// �裱��Ⱦ��
    $query = sprintf("select sum(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='��������'", $h1_str, $h1_end);
    if (getUniResult($query, $h1_all_uri) < 1) {
        $h1_all_uri = 0;                 // ��������
    }
        ///// �裲��Ⱦ��
    $query = sprintf("select sum(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='��������'", $h2_str, $h2_end);
    if (getUniResult($query, $h2_all_uri) < 1) {
        $h2_all_uri = 0;                 // ��������
    }
        ///// �裳��Ⱦ��
    $query = sprintf("select sum(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='��������'", $h3_str, $h3_end);
    if (getUniResult($query, $h3_all_uri) < 1) {
        $h3_all_uri = 0;                 // ��������
    }
        ///// �裴��Ⱦ��
    $query = sprintf("select sum(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='��������'", $h4_str, $h4_end);
    if (getUniResult($query, $h4_all_uri) < 1) {
        $h4_all_uri = 0;                 // ��������
    }
} else {
        ///// ����
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='��������'", $yyyymm);
    if (getUniResult($query, $all_uri) < 1) {
        $all_uri = 0;                 // ��������
    }
        ///// ����
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='��������'", $p1_ym);
    if (getUniResult($query, $p1_all_uri) < 1) {
        $p1_all_uri = 0;                 // ��������
    }
        ///// ������
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='��������'", $p2_ym);
    if (getUniResult($query, $p2_all_uri) < 1) {
        $p2_all_uri = 0;                 // ��������
    }
}
    ///// ����
$query = sprintf("select sum(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='��������'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_all_uri) < 1) {
    $rui_all_uri = 0;                 // ��������
}
    ///// ����
$query = sprintf("select sum(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='��������'", $b_str_ym, $b_yyyymm);
if (getUniResult($query, $p1_rui_all_uri) < 1) {
    $p1_rui_all_uri = 0;                 // ��������
}

/********** ��帶�� **********/
if ($tuki_chk == 3) {
        ///// �裱��Ⱦ��
    $query = sprintf("select sum(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='������帶��'", $h1_str, $h1_end);
    if (getUniResult($query, $h1_all_urigen) < 1) {
        $h1_all_urigen = 0;                 // ��������
    }
        ///// �裲��Ⱦ��
    $query = sprintf("select sum(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='������帶��'", $h2_str, $h2_end);
    if (getUniResult($query, $h2_all_urigen) < 1) {
        $h2_all_urigen = 0;                 // ��������
    }
        ///// �裳��Ⱦ��
    $query = sprintf("select sum(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='������帶��'", $h3_str, $h3_end);
    if (getUniResult($query, $h3_all_urigen) < 1) {
        $h3_all_urigen = 0;                 // ��������
    }
        ///// �裴��Ⱦ��
    $query = sprintf("select sum(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='������帶��'", $h4_str, $h4_end);
    if (getUniResult($query, $h4_all_urigen) < 1) {
        $h4_all_urigen = 0;                 // ��������
    }
} else {
        ///// ����
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='������帶��'", $yyyymm);
    if (getUniResult($query, $all_urigen) < 1) {
        $all_urigen = 0;                 // ��������
    }
        ///// ����
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='������帶��'", $p1_ym);
    if (getUniResult($query, $p1_all_urigen) < 1) {
        $p1_all_urigen = 0;                 // ��������
    }
        ///// ������
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='������帶��'", $p2_ym);
    if (getUniResult($query, $p2_all_urigen) < 1) {
        $p2_all_urigen = 0;                 // ��������
    }
}
    ///// ����
$query = sprintf("select sum(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='������帶��'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_all_urigen) < 1) {
    $rui_all_urigen = 0;                 // ��������
}
    ///// ����
$query = sprintf("select sum(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='������帶��'", $b_str_ym, $b_yyyymm);
if (getUniResult($query, $p1_rui_all_urigen) < 1) {
    $p1_rui_all_urigen = 0;                 // ��������
}

/********** ��������� **********/
if ($tuki_chk == 3) {
        ///// �裱��Ⱦ��
    $query = sprintf("select sum(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='�������������'", $h1_str, $h1_end);
    if (getUniResult($query, $h1_all_gross_profit) < 1) {
        $h1_all_gross_profit = 0;                 // ��������
    }
        ///// �裲��Ⱦ��
    $query = sprintf("select sum(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='�������������'", $h2_str, $h2_end);
    if (getUniResult($query, $h2_all_gross_profit) < 1) {
        $h2_all_gross_profit = 0;                 // ��������
    }
        ///// �裳��Ⱦ��
    $query = sprintf("select sum(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='�������������'", $h3_str, $h3_end);
    if (getUniResult($query, $h3_all_gross_profit) < 1) {
        $h3_all_gross_profit = 0;                 // ��������
    }
        ///// �裴��Ⱦ��
    $query = sprintf("select sum(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='�������������'", $h4_str, $h4_end);
    if (getUniResult($query, $h4_all_gross_profit) < 1) {
        $h4_all_gross_profit = 0;                 // ��������
    }
} else {
        ///// ����
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='�������������'", $yyyymm);
    if (getUniResult($query, $all_gross_profit) < 1) {
        $all_gross_profit = 0;                 // ��������
    }
        ///// ����
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='�������������'", $p1_ym);
    if (getUniResult($query, $p1_all_gross_profit) < 1) {
        $p1_all_gross_profit = 0;                 // ��������
    }
        ///// ������
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='�������������'", $p2_ym);
    if (getUniResult($query, $p2_all_gross_profit) < 1) {
        $p2_all_gross_profit = 0;                 // ��������
    }
}
    ///// ����
$query = sprintf("select sum(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='�������������'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_all_gross_profit) < 1) {
    $rui_all_gross_profit = 0;                 // ��������
}
    ///// ����
$query = sprintf("select sum(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='�������������'", $b_str_ym, $b_yyyymm);
if (getUniResult($query, $p1_rui_all_gross_profit) < 1) {
    $p1_rui_all_gross_profit = 0;                 // ��������
}

/********** �δ���ι�� **********/
if ($tuki_chk == 3) {
        ///// �裱��Ⱦ��
    $query = sprintf("select sum(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='�����δ���ڤӰ��̴������'", $h1_str, $h1_end);
    if (getUniResult($query, $h1_all_han_all) < 1) {
        $h1_all_han_all = 0;                 // ��������
    }
        ///// �裲��Ⱦ��
    $query = sprintf("select sum(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='�����δ���ڤӰ��̴������'", $h2_str, $h2_end);
    if (getUniResult($query, $h2_all_han_all) < 1) {
        $h2_all_han_all = 0;                 // ��������
    }
        ///// �裳��Ⱦ��
    $query = sprintf("select sum(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='�����δ���ڤӰ��̴������'", $h3_str, $h3_end);
    if (getUniResult($query, $h3_all_han_all) < 1) {
        $h3_all_han_all = 0;                 // ��������
    }
        ///// �裴��Ⱦ��
    $query = sprintf("select sum(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='�����δ���ڤӰ��̴������'", $h4_str, $h4_end);
    if (getUniResult($query, $h4_all_han_all) < 1) {
        $h4_all_han_all = 0;                 // ��������
    }
} else {
        ///// ����
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='�����δ���ڤӰ��̴������'", $yyyymm);
    if (getUniResult($query, $all_han_all) < 1) {
        $all_han_all = 0;                 // ��������
    }
        ///// ����
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='�����δ���ڤӰ��̴������'", $p1_ym);
    if (getUniResult($query, $p1_all_han_all) < 1) {
        $p1_all_han_all = 0;                 // ��������
    }
        ///// ������
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='�����δ���ڤӰ��̴������'", $p2_ym);
    if (getUniResult($query, $p2_all_han_all) < 1) {
        $p2_all_han_all = 0;                 // ��������
    }
}
    ///// ����
$query = sprintf("select sum(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='�����δ���ڤӰ��̴������'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_all_han_all) < 1) {
    $rui_all_han_all = 0;                 // ��������
}
    ///// ����
$query = sprintf("select sum(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='�����δ���ڤӰ��̴������'", $b_str_ym, $b_yyyymm);
if (getUniResult($query, $p1_rui_all_han_all) < 1) {
    $p1_rui_all_han_all = 0;                 // ��������
}

/********** �Ķ����� **********/
if ($tuki_chk == 3) {
        ///// �裱��Ⱦ��
    $query = sprintf("select sum(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���αĶ�����'", $h1_str, $h1_end);
    if (getUniResult($query, $h1_all_ope_profit) < 1) {
        $h1_all_ope_profit = 0;                 // ��������
    }
        ///// �裲��Ⱦ��
    $query = sprintf("select sum(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���αĶ�����'", $h2_str, $h2_end);
    if (getUniResult($query, $h2_all_ope_profit) < 1) {
        $h2_all_ope_profit = 0;                 // ��������
    }
        ///// �裳��Ⱦ��
    $query = sprintf("select sum(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���αĶ�����'", $h3_str, $h3_end);
    if (getUniResult($query, $h3_all_ope_profit) < 1) {
        $h3_all_ope_profit = 0;                 // ��������
    }
        ///// �裴��Ⱦ��
    $query = sprintf("select sum(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���αĶ�����'", $h4_str, $h4_end);
    if (getUniResult($query, $h4_all_ope_profit) < 1) {
        $h4_all_ope_profit = 0;                 // ��������
    }
} else {
        ///// ����
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='���αĶ�����'", $yyyymm);
    if (getUniResult($query, $all_ope_profit) < 1) {
        $all_ope_profit = 0;                 // ��������
    }
        ///// ����
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='���αĶ�����'", $p1_ym);
    if (getUniResult($query, $p1_all_ope_profit) < 1) {
        $p1_all_ope_profit = 0;                 // ��������
    }
        ///// ������
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='���αĶ�����'", $p2_ym);
    if (getUniResult($query, $p2_all_ope_profit) < 1) {
        $p2_all_ope_profit = 0;                 // ��������
    }
}
    ///// ����
$query = sprintf("select sum(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���αĶ�����'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_all_ope_profit) < 1) {
    $rui_all_ope_profit = 0;                 // ��������
}
    ///// ����
$query = sprintf("select sum(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���αĶ�����'", $b_str_ym, $b_yyyymm);
if (getUniResult($query, $p1_rui_all_ope_profit) < 1) {
    $p1_rui_all_ope_profit = 0;                 // ��������
}

/********** �Ķȳ����פι�� **********/
if ($tuki_chk == 3) {
        ///// �裱��Ⱦ��
    $query = sprintf("select sum(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���αĶȳ����׷�'", $h1_str, $h1_end);
    if (getUniResult($query, $h1_all_nonope_profit_sum) < 1) {
        $h1_all_nonope_profit_sum = 0;                 // ��������
    }
        ///// �裲��Ⱦ��
    $query = sprintf("select sum(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���αĶȳ����׷�'", $h2_str, $h2_end);
    if (getUniResult($query, $h2_all_nonope_profit_sum) < 1) {
        $h2_all_nonope_profit_sum = 0;                 // ��������
    }
        ///// �裳��Ⱦ��
    $query = sprintf("select sum(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���αĶȳ����׷�'", $h3_str, $h3_end);
    if (getUniResult($query, $h3_all_nonope_profit_sum) < 1) {
        $h3_all_nonope_profit_sum = 0;                 // ��������
    }
        ///// �裴��Ⱦ��
    $query = sprintf("select sum(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���αĶȳ����׷�'", $h4_str, $h4_end);
    if (getUniResult($query, $h4_all_nonope_profit_sum) < 1) {
        $h4_all_nonope_profit_sum = 0;                 // ��������
    }
} else {
        ///// ����
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='���αĶȳ����׷�'", $yyyymm);
    if (getUniResult($query, $all_nonope_profit_sum) < 1) {
        $all_nonope_profit_sum = 0;                 // ��������
    }
        ///// ����
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='���αĶȳ����׷�'", $p1_ym);
    if (getUniResult($query, $p1_all_nonope_profit_sum) < 1) {
        $p1_all_nonope_profit_sum = 0;                 // ��������
    }
        ///// ������
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='���αĶȳ����׷�'", $p2_ym);
    if (getUniResult($query, $p2_all_nonope_profit_sum) < 1) {
        $p2_all_nonope_profit_sum = 0;                 // ��������
    }
}
    ///// ����
$query = sprintf("select sum(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���αĶȳ����׷�'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_all_nonope_profit_sum) < 1) {
    $rui_all_nonope_profit_sum = 0;                 // ��������
}
    ///// ����
$query = sprintf("select sum(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���αĶȳ����׷�'", $b_str_ym, $b_yyyymm);
if (getUniResult($query, $p1_rui_all_nonope_profit_sum) < 1) {
    $p1_rui_all_nonope_profit_sum = 0;                 // ��������
}

/********** �Ķȳ����Ѥι�� **********/
if ($tuki_chk == 3) {
        ///// �裱��Ⱦ��
    $query = sprintf("select sum(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���αĶȳ����ѷ�'", $h1_str, $h1_end);
    if (getUniResult($query, $h1_all_nonope_loss_sum) < 1) {
        $h1_all_nonope_loss_sum = 0;                 // ��������
    }
        ///// �裲��Ⱦ��
    $query = sprintf("select sum(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���αĶȳ����ѷ�'", $h2_str, $h2_end);
    if (getUniResult($query, $h2_all_nonope_loss_sum) < 1) {
        $h2_all_nonope_loss_sum = 0;                 // ��������
    }
        ///// �裳��Ⱦ��
    $query = sprintf("select sum(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���αĶȳ����ѷ�'", $h3_str, $h3_end);
    if (getUniResult($query, $h3_all_nonope_loss_sum) < 1) {
        $h3_all_nonope_loss_sum = 0;                 // ��������
    }
        ///// �裴��Ⱦ��
    $query = sprintf("select sum(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���αĶȳ����ѷ�'", $h4_str, $h4_end);
    if (getUniResult($query, $h4_all_nonope_loss_sum) < 1) {
        $h4_all_nonope_loss_sum = 0;                 // ��������
    }
} else {
        ///// ����
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='���αĶȳ����ѷ�'", $yyyymm);
    if (getUniResult($query, $all_nonope_loss_sum) < 1) {
        $all_nonope_loss_sum = 0;                 // ��������
    }
        ///// ����
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='���αĶȳ����ѷ�'", $p1_ym);
    if (getUniResult($query, $p1_all_nonope_loss_sum) < 1) {
        $p1_all_nonope_loss_sum = 0;                 // ��������
    }
        ///// ������
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='���αĶȳ����ѷ�'", $p2_ym);
    if (getUniResult($query, $p2_all_nonope_loss_sum) < 1) {
        $p2_all_nonope_loss_sum = 0;                 // ��������
    }
}
    ///// ����
$query = sprintf("select sum(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���αĶȳ����ѷ�'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_all_nonope_loss_sum) < 1) {
    $rui_all_nonope_loss_sum = 0;                 // ��������
}
    ///// ����
$query = sprintf("select sum(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���αĶȳ����ѷ�'", $b_str_ym, $b_yyyymm);
if (getUniResult($query, $p1_rui_all_nonope_loss_sum) < 1) {
    $p1_rui_all_nonope_loss_sum = 0;                 // ��������
}

/********** �о����� **********/
if ($tuki_chk == 3) {
        ///// �裱��Ⱦ��
    $query = sprintf("select sum(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���ηо�����'", $h1_str, $h1_end);
    if (getUniResult($query, $h1_all_current_profit) < 1) {
        $h1_all_current_profit = 0;                 // ��������
    }
        ///// �裲��Ⱦ��
    $query = sprintf("select sum(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���ηо�����'", $h2_str, $h2_end);
    if (getUniResult($query, $h2_all_current_profit) < 1) {
        $h2_all_current_profit = 0;                 // ��������
    }
        ///// �裳��Ⱦ��
    $query = sprintf("select sum(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���ηо�����'", $h3_str, $h3_end);
    if (getUniResult($query, $h3_all_current_profit) < 1) {
        $h3_all_current_profit = 0;                 // ��������
    }
        ///// �裴��Ⱦ��
    $query = sprintf("select sum(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���ηо�����'", $h4_str, $h4_end);
    if (getUniResult($query, $h4_all_current_profit) < 1) {
        $h4_all_current_profit = 0;                 // ��������
    }
} else {
        ///// ����
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='���ηо�����'", $yyyymm);
    if (getUniResult($query, $all_current_profit) < 1) {
        $all_current_profit = 0;                 // ��������
    }
        ///// ����
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='���ηо�����'", $p1_ym);
    if (getUniResult($query, $p1_all_current_profit) < 1) {
        $p1_all_current_profit = 0;                 // ��������
    }
        ///// ������
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='���ηо�����'", $p2_ym);
    if (getUniResult($query, $p2_all_current_profit) < 1) {
        $p2_all_current_profit = 0;                 // ��������
    }
}
    ///// ����
$query = sprintf("select sum(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���ηо�����'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_all_current_profit) < 1) {
    $rui_all_current_profit = 0;                 // ��������
}
    ///// ����
$query = sprintf("select sum(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���ηо�����'", $b_str_ym, $b_yyyymm);
if (getUniResult($query, $p1_rui_all_current_profit) < 1) {
    $p1_rui_all_current_profit = 0;                 // ��������
}

/********** �������� **********/
if ($tuki_chk == 3) {
       ///// �裱��Ⱦ��
    $query = sprintf("select sum(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='������������'", $h1_str, $h1_end);
    if (getUniResult($query, $h1_all_special_profit) < 1) {
        $h1_all_special_profit = 0;                 // ��������
    }
       ///// �裲��Ⱦ��
    $query = sprintf("select sum(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='������������'", $h2_str, $h2_end);
    if (getUniResult($query, $h2_all_special_profit) < 1) {
        $h2_all_special_profit = 0;                 // ��������
    }
       ///// �裳��Ⱦ��
    $query = sprintf("select sum(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='������������'", $h3_str, $h3_end);
    if (getUniResult($query, $h3_all_special_profit) < 1) {
        $h3_all_special_profit = 0;                 // ��������
    }
       ///// �裴��Ⱦ��
    $query = sprintf("select sum(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='������������'", $h4_str, $h4_end);
    if (getUniResult($query, $h4_all_special_profit) < 1) {
        $h4_all_special_profit = 0;                 // ��������
    }
} else {
        ///// ����
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='������������'", $yyyymm);
    if (getUniResult($query, $all_special_profit) < 1) {
        $all_special_profit = 0;                 // ��������
    }
        ///// ����
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='������������'", $p1_ym);
    if (getUniResult($query, $p1_all_special_profit) < 1) {
        $p1_all_special_profit = 0;                 // ��������
    }
        ///// ������
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='������������'", $p2_ym);
    if (getUniResult($query, $p2_all_special_profit) < 1) {
        $p2_all_special_profit = 0;                 // ��������
    }
}
    ///// ����
$query = sprintf("select sum(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='������������'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_all_special_profit) < 1) {
    $rui_all_special_profit = 0;                 // ��������
}
    ///// ����
$query = sprintf("select sum(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='������������'", $b_str_ym, $b_yyyymm);
if (getUniResult($query, $p1_rui_all_special_profit) < 1) {
    $p1_rui_all_special_profit = 0;                 // ��������
}

/********** ����»�� **********/
if ($tuki_chk == 3) {
       ///// �裱��Ⱦ��
    $query = sprintf("select sum(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='��������»��'", $h1_str, $h1_end);
    if (getUniResult($query, $h1_all_special_loss) < 1) {
        $h1_all_special_loss = 0;                 // ��������
    }
       ///// �裲��Ⱦ��
    $query = sprintf("select sum(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='��������»��'", $h2_str, $h2_end);
    if (getUniResult($query, $h2_all_special_loss) < 1) {
        $h2_all_special_loss = 0;                 // ��������
    }
       ///// �裳��Ⱦ��
    $query = sprintf("select sum(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='��������»��'", $h3_str, $h3_end);
    if (getUniResult($query, $h3_all_special_loss) < 1) {
        $h3_all_special_loss = 0;                 // ��������
    }
       ///// �裴��Ⱦ��
    $query = sprintf("select sum(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='��������»��'", $h4_str, $h4_end);
    if (getUniResult($query, $h4_all_special_loss) < 1) {
        $h4_all_special_loss = 0;                 // ��������
    }
} else {
        ///// ����
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='��������»��'", $yyyymm);
    if (getUniResult($query, $all_special_loss) < 1) {
        $all_special_loss = 0;                 // ��������
    }
        ///// ����
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='��������»��'", $p1_ym);
    if (getUniResult($query, $p1_all_special_loss) < 1) {
        $p1_all_special_loss = 0;                 // ��������
    }
        ///// ������
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='��������»��'", $p2_ym);
    if (getUniResult($query, $p2_all_special_loss) < 1) {
        $p2_all_special_loss = 0;                 // ��������
    }
}
    ///// ����
$query = sprintf("select sum(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='��������»��'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_all_special_loss) < 1) {
    $rui_all_special_loss = 0;                 // ��������
}
    ///// ����
$query = sprintf("select sum(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='��������»��'", $b_str_ym, $b_yyyymm);
if (getUniResult($query, $p1_rui_all_special_loss) < 1) {
    $p1_rui_all_special_loss = 0;                 // ��������
}

/********** �ǰ��������׶�� **********/
if ($tuki_chk == 3) {
       ///// �裱��Ⱦ��
    $query = sprintf("select sum(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='�����ǰ��������׶��'", $h1_str, $h1_end);
    if (getUniResult($query, $h1_all_before_tax_net_profit) < 1) {
        $h1_all_before_tax_net_profit = 0;                 // ��������
    }
       ///// �裲��Ⱦ��
    $query = sprintf("select sum(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='�����ǰ��������׶��'", $h2_str, $h2_end);
    if (getUniResult($query, $h2_all_before_tax_net_profit) < 1) {
        $h2_all_before_tax_net_profit = 0;                 // ��������
    }
       ///// �裳��Ⱦ��
    $query = sprintf("select sum(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='�����ǰ��������׶��'", $h3_str, $h3_end);
    if (getUniResult($query, $h3_all_before_tax_net_profit) < 1) {
        $h3_all_before_tax_net_profit = 0;                 // ��������
    }
       ///// �裴��Ⱦ��
    $query = sprintf("select sum(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='�����ǰ��������׶��'", $h4_str, $h4_end);
    if (getUniResult($query, $h4_all_before_tax_net_profit) < 1) {
        $h4_all_before_tax_net_profit = 0;                 // ��������
    }
} else {
        ///// ����
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='�����ǰ��������׶��'", $yyyymm);
    if (getUniResult($query, $all_before_tax_net_profit) < 1) {
        $all_before_tax_net_profit = 0;                 // ��������
    }
        ///// ����
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='�����ǰ��������׶��'", $p1_ym);
    if (getUniResult($query, $p1_all_before_tax_net_profit) < 1) {
        $p1_all_before_tax_net_profit = 0;                 // ��������
    }
        ///// ������
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='�����ǰ��������׶��'", $p2_ym);
    if (getUniResult($query, $p2_all_before_tax_net_profit) < 1) {
        $p2_all_before_tax_net_profit = 0;                 // ��������
    }
}
    ///// ����
$query = sprintf("select sum(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='�����ǰ��������׶��'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_all_before_tax_net_profit) < 1) {
    $rui_all_before_tax_net_profit = 0;                 // ��������
}
    ///// ����
$query = sprintf("select sum(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='�����ǰ��������׶��'", $b_str_ym, $b_yyyymm);
if (getUniResult($query, $p1_rui_all_before_tax_net_profit) < 1) {
    $p1_rui_all_before_tax_net_profit = 0;                 // ��������
}

/********** ˡ�������ι�� **********/
if ($tuki_chk == 3) {
       ///// �裱��Ⱦ��
    $query = sprintf("select sum(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='����ˡ������'", $h1_str, $h1_end);
    if (getUniResult($query, $h1_all_corporation_tax_etc) < 1) {
        $h1_all_corporation_tax_etc = 0;                 // ��������
    }
       ///// �裲��Ⱦ��
    $query = sprintf("select sum(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='����ˡ������'", $h2_str, $h2_end);
    if (getUniResult($query, $h2_all_corporation_tax_etc) < 1) {
        $h2_all_corporation_tax_etc = 0;                 // ��������
    }
       ///// �裳��Ⱦ��
    $query = sprintf("select sum(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='����ˡ������'", $h3_str, $h3_end);
    if (getUniResult($query, $h3_all_corporation_tax_etc) < 1) {
        $h3_all_corporation_tax_etc = 0;                 // ��������
    }
       ///// �裴��Ⱦ��
    $query = sprintf("select sum(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='����ˡ������'", $h4_str, $h4_end);
    if (getUniResult($query, $h4_all_corporation_tax_etc) < 1) {
        $h4_all_corporation_tax_etc = 0;                 // ��������
    }
} else {
        ///// ����
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='����ˡ������'", $yyyymm);
    if (getUniResult($query, $all_corporation_tax_etc) < 1) {
        $all_corporation_tax_etc = 0;                 // ��������
    }
        ///// ����
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='����ˡ������'", $p1_ym);
    if (getUniResult($query, $p1_all_corporation_tax_etc) < 1) {
        $p1_all_corporation_tax_etc = 0;                 // ��������
    }
        ///// ������
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='����ˡ������'", $p2_ym);
    if (getUniResult($query, $p2_all_corporation_tax_etc) < 1) {
        $p2_all_corporation_tax_etc = 0;                 // ��������
    }
}
    ///// ����
$query = sprintf("select sum(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='����ˡ������'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_all_corporation_tax_etc) < 1) {
    $rui_all_corporation_tax_etc = 0;                 // ��������
}
    ///// ����
$query = sprintf("select sum(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='����ˡ������'", $b_str_ym, $b_yyyymm);
if (getUniResult($query, $p1_rui_all_corporation_tax_etc) < 1) {
    $p1_rui_all_corporation_tax_etc = 0;                 // ��������
}

/********** ���������׶�� **********/
if ($tuki_chk == 3) {
       ///// �裱��Ⱦ��
    $query = sprintf("select sum(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='�������������׶��'", $h1_str, $h1_end);
    if (getUniResult($query, $h1_all_pure_profit) < 1) {
        $h1_all_pure_profit = 0;                 // ��������
    }
       ///// �裲��Ⱦ��
    $query = sprintf("select sum(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='�������������׶��'", $h2_str, $h2_end);
    if (getUniResult($query, $h2_all_pure_profit) < 1) {
        $h2_all_pure_profit = 0;                 // ��������
    }
       ///// �裳��Ⱦ��
    $query = sprintf("select sum(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='�������������׶��'", $h3_str, $h3_end);
    if (getUniResult($query, $h3_all_pure_profit) < 1) {
        $h3_all_pure_profit = 0;                 // ��������
    }
       ///// �裴��Ⱦ��
    $query = sprintf("select sum(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='�������������׶��'", $h4_str, $h4_end);
    if (getUniResult($query, $h4_all_pure_profit) < 1) {
        $h4_all_pure_profit = 0;                 // ��������
    }
} else {
        ///// ����
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='�������������׶��'", $yyyymm);
    if (getUniResult($query, $all_pure_profit) < 1) {
        $all_pure_profit = 0;                 // ��������
    }
        ///// ����
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='�������������׶��'", $p1_ym);
    if (getUniResult($query, $p1_all_pure_profit) < 1) {
        $p1_all_pure_profit = 0;                 // ��������
    }
        ///// ������
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='�������������׶��'", $p2_ym);
    if (getUniResult($query, $p2_all_pure_profit) < 1) {
        $p2_all_pure_profit = 0;                 // ��������
    }
}
    ///// ����
$query = sprintf("select sum(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='�������������׶��'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_all_pure_profit) < 1) {
    $rui_all_pure_profit = 0;                 // ��������
}
    ///// ����
$query = sprintf("select sum(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='�������������׶��'", $b_str_ym, $b_yyyymm);
if (getUniResult($query, $p1_rui_all_pure_profit) < 1) {
    $p1_rui_all_pure_profit = 0;                 // ��������
}

    ///// ��Ⱦ����׶�ۤη׻�
if ($tuki_chk != 3) {
    $t_all_uri                   = $p2_all_uri + $p1_all_uri + $all_uri;
    $t_all_urigen                = $p2_all_urigen + $p1_all_urigen + $all_urigen;
    $t_all_gross_profit          = $p2_all_gross_profit + $p1_all_gross_profit + $all_gross_profit;
    $t_all_han_all               = $p2_all_han_all + $p1_all_han_all + $all_han_all;
    $t_all_ope_profit            = $p2_all_ope_profit + $p1_all_ope_profit + $all_ope_profit;
    $t_all_nonope_profit_sum     = $p2_all_nonope_profit_sum + $p1_all_nonope_profit_sum + $all_nonope_profit_sum;
    $t_all_nonope_loss_sum       = $p2_all_nonope_loss_sum + $p1_all_nonope_loss_sum + $all_nonope_loss_sum;
    $t_all_current_profit        = $p2_all_current_profit + $p1_all_current_profit + $all_current_profit;
    $t_all_special_profit        = $p2_all_special_profit + $p1_all_special_profit + $all_special_profit;
    $t_all_special_loss          = $p2_all_special_loss + $p1_all_special_loss + $all_special_loss;
    $t_all_before_tax_net_profit = $p2_all_before_tax_net_profit + $p1_all_before_tax_net_profit + $all_before_tax_net_profit;
    $t_all_corporation_tax_etc   = $p2_all_corporation_tax_etc + $p1_all_corporation_tax_etc + $all_corporation_tax_etc;
    $t_all_pure_profit           = $p2_all_pure_profit + $p1_all_pure_profit + $all_pure_profit;
}

    ///// �����ۤη׻�
$def_all_uri                   = $rui_all_uri - $p1_rui_all_uri;
$def_all_urigen                = $rui_all_urigen - $p1_rui_all_urigen;
$def_all_gross_profit          = $rui_all_gross_profit - $p1_rui_all_gross_profit;
$def_all_han_all               = $rui_all_han_all - $p1_rui_all_han_all;
$def_all_ope_profit            = $rui_all_ope_profit - $p1_rui_all_ope_profit;
$def_all_nonope_profit_sum     = $rui_all_nonope_profit_sum - $p1_rui_all_nonope_profit_sum;
$def_all_nonope_loss_sum       = $rui_all_nonope_loss_sum - $p1_rui_all_nonope_loss_sum;
$def_all_current_profit        = $rui_all_current_profit - $p1_rui_all_current_profit;
$def_all_special_profit        = $rui_all_special_profit - $p1_rui_all_special_profit;
$def_all_special_loss          = $rui_all_special_loss - $p1_rui_all_special_loss;
$def_all_before_tax_net_profit = $rui_all_before_tax_net_profit - $p1_rui_all_before_tax_net_profit;
$def_all_corporation_tax_etc   = $rui_all_corporation_tax_etc - $p1_rui_all_corporation_tax_etc;
$def_all_pure_profit           = $rui_all_pure_profit - $p1_rui_all_pure_profit;

    ///// ����Ψ�η׻�
if ($p1_rui_all_uri < 0) {
    if ($def_all_uri < 0) {
        $def_all_uri_rate = number_format(( -$def_all_uri / $p1_rui_all_uri * 100), 1);
    } else {
        $def_all_uri_rate = number_format(( $def_all_uri / -$p1_rui_all_uri * 100), 1);
    }
} else {
    $def_all_uri_rate = number_format(($def_all_uri / $p1_rui_all_uri * 100), 1);
}
if ($p1_rui_all_urigen < 0) {
    if ($def_all_urigen < 0) {
        $def_all_urigen_rate = number_format(( -$def_all_urigen / $p1_rui_all_urigen * 100), 1);
    } else {
        $def_all_urigen_rate = number_format(( $def_all_urigen / -$p1_rui_all_urigen * 100), 1);
    }
} else {
    $def_all_urigen_rate = number_format(($def_all_urigen / $p1_rui_all_urigen * 100), 1);
}
if ($p1_rui_all_gross_profit < 0) {
    if ($def_all_gross_profit < 0) {
        $def_all_gross_profit_rate = number_format(( -$def_all_gross_profit / $p1_rui_all_gross_profit * 100), 1);
    } else {
        $def_all_gross_profit_rate = number_format(( $def_all_gross_profit / -$p1_rui_all_gross_profit * 100), 1);
    }
} else {
    $def_all_gross_profit_rate = number_format(($def_all_gross_profit / $p1_rui_all_gross_profit * 100), 1);
}
if ($p1_rui_all_han_all < 0) {
    if ($def_all_han_all < 0) {
        $def_all_han_all_rate = number_format(( -$def_all_han_all / $p1_rui_all_han_all * 100), 1);
    } else {
        $def_all_han_all_rate = number_format(( $def_all_han_all / -$p1_rui_all_han_all * 100), 1);
    }
} else {
    $def_all_han_all_rate = number_format(($def_all_han_all / $p1_rui_all_han_all * 100), 1);
}
if ($p1_rui_all_ope_profit < 0) {
    if ($def_all_ope_profit < 0) {
        $def_all_ope_profit_rate = number_format(( -$def_all_ope_profit / $p1_rui_all_ope_profit * 100), 1);
    } else {
        $def_all_ope_profit_rate = number_format(( $def_all_ope_profit / -$p1_rui_all_ope_profit * 100), 1);
    }
} else {
    $def_all_ope_profit_rate = number_format(($def_all_ope_profit / $p1_rui_all_ope_profit * 100), 1);
}
if ($p1_rui_all_nonope_profit_sum < 0) {
    if ($def_all_nonope_profit_sum < 0) {
        $def_all_nonope_profit_sum_rate = number_format(( -$def_all_nonope_profit_sum / $p1_rui_all_nonope_profit_sum * 100), 1);
    } else {
        $def_all_nonope_profit_sum_rate = number_format(( $def_all_nonope_profit_sum / -$p1_rui_all_nonope_profit_sum * 100), 1);
    }
} else {
    $def_all_nonope_profit_sum_rate = number_format(($def_all_nonope_profit_sum / $p1_rui_all_nonope_profit_sum * 100), 1);
}
if ($p1_rui_all_nonope_loss_sum < 0) {
    if ($def_all_nonope_loss_sum < 0) {
        $def_all_nonope_loss_sum_rate = number_format(( -$def_all_nonope_loss_sum / $p1_rui_all_nonope_loss_sum * 100), 1);
    } else {
        $def_all_nonope_loss_sum_rate = number_format(( $def_all_nonope_loss_sum / -$p1_rui_all_nonope_loss_sum * 100), 1);
    }
} else {
    $def_all_nonope_loss_sum_rate = number_format(($def_all_nonope_loss_sum / $p1_rui_all_nonope_loss_sum * 100), 1);
}
if ($p1_rui_all_current_profit < 0) {
    if ($def_all_current_profit < 0) {
        $def_all_current_profit_rate = number_format(( -$def_all_current_profit / $p1_rui_all_current_profit * 100), 1);
    } else {
        $def_all_current_profit_rate = number_format(( $def_all_current_profit / -$p1_rui_all_current_profit * 100), 1);
    }
} else {
    $def_all_current_profit_rate = number_format(($def_all_current_profit / $p1_rui_all_current_profit * 100), 1);
}
if ($p1_rui_all_before_tax_net_profit < 0) {
    if ($def_all_before_tax_net_profit < 0) {
        $def_all_before_tax_net_profit_rate = number_format(( -$def_all_before_tax_net_profit / $p1_rui_all_before_tax_net_profit * 100), 1);
    } else {
        $def_all_before_tax_net_profit_rate = number_format(( $def_all_before_tax_net_profit / -$p1_rui_all_before_tax_net_profit * 100), 1);
    }
} else {
    $def_all_before_tax_net_profit_rate = number_format(($def_all_before_tax_net_profit / $p1_rui_all_before_tax_net_profit * 100), 1);
}
if ($p1_rui_all_corporation_tax_etc < 0) {
    if ($def_all_corporation_tax_etc < 0) {
        $def_all_corporation_tax_etc_rate = number_format(( -$def_all_corporation_tax_etc / $p1_rui_all_corporation_tax_etc * 100), 1);
    } else {
        $def_all_corporation_tax_etc_rate = number_format(( $def_all_corporation_tax_etc / -$p1_rui_all_corporation_tax_etc * 100), 1);
    }
} else {
    $def_all_corporation_tax_etc_rate = number_format(($def_all_corporation_tax_etc / $p1_rui_all_corporation_tax_etc * 100), 1);
}
if ($p1_rui_all_pure_profit < 0) {
    if ($def_all_pure_profit < 0) {
        $def_all_pure_profit_rate = number_format(( -$def_all_pure_profit / $p1_rui_all_pure_profit * 100), 1);
    } else {
        $def_all_pure_profit_rate = number_format(( $def_all_pure_profit / -$p1_rui_all_pure_profit * 100), 1);
    }
} else {
    $def_all_pure_profit_rate = number_format(($def_all_pure_profit / $p1_rui_all_pure_profit * 100), 1);
}

    ///// �Ʒ�Υե����ޥå��ѹ�
if ($tuki_chk == 3) {
    $h1_all_uri                       = number_format(($h1_all_uri / $tani), $keta);
    $h2_all_uri                       = number_format(($h2_all_uri / $tani), $keta);
    $h3_all_uri                       = number_format(($h3_all_uri / $tani), $keta);
    $h4_all_uri                       = number_format(($h4_all_uri / $tani), $keta);
    $rui_all_uri                      = number_format(($rui_all_uri / $tani), $keta);
    $p1_rui_all_uri                   = number_format(($p1_rui_all_uri / $tani), $keta);
    $def_all_uri                      = number_format(($def_all_uri / $tani), $keta);
    $h1_all_urigen                    = number_format(($h1_all_urigen / $tani), $keta);
    $h2_all_urigen                    = number_format(($h2_all_urigen / $tani), $keta);
    $h3_all_urigen                    = number_format(($h3_all_urigen / $tani), $keta);
    $h4_all_urigen                    = number_format(($h4_all_urigen / $tani), $keta);
    $rui_all_urigen                   = number_format(($rui_all_urigen / $tani), $keta);
    $p1_rui_all_urigen                = number_format(($p1_rui_all_urigen / $tani), $keta);
    $def_all_urigen                   = number_format(($def_all_urigen / $tani), $keta);
    $h1_all_gross_profit              = number_format(($h1_all_gross_profit / $tani), $keta);
    $h2_all_gross_profit              = number_format(($h2_all_gross_profit / $tani), $keta);
    $h3_all_gross_profit              = number_format(($h3_all_gross_profit / $tani), $keta);
    $h4_all_gross_profit              = number_format(($h4_all_gross_profit / $tani), $keta);
    $rui_all_gross_profit             = number_format(($rui_all_gross_profit / $tani), $keta);
    $p1_rui_all_gross_profit          = number_format(($p1_rui_all_gross_profit / $tani), $keta);
    $def_all_gross_profit             = number_format(($def_all_gross_profit / $tani), $keta);
    $h1_all_han_all                   = number_format(($h1_all_han_all / $tani), $keta);
    $h2_all_han_all                   = number_format(($h2_all_han_all / $tani), $keta);
    $h3_all_han_all                   = number_format(($h3_all_han_all / $tani), $keta);
    $h4_all_han_all                   = number_format(($h4_all_han_all / $tani), $keta);
    $rui_all_han_all                  = number_format(($rui_all_han_all / $tani), $keta);
    $p1_rui_all_han_all               = number_format(($p1_rui_all_han_all / $tani), $keta);
    $def_all_han_all                  = number_format(($def_all_han_all / $tani), $keta);
    $h1_all_ope_profit                = number_format(($h1_all_ope_profit / $tani), $keta);
    $h2_all_ope_profit                = number_format(($h2_all_ope_profit / $tani), $keta);
    $h3_all_ope_profit                = number_format(($h3_all_ope_profit / $tani), $keta);
    $h4_all_ope_profit                = number_format(($h4_all_ope_profit / $tani), $keta);
    $rui_all_ope_profit               = number_format(($rui_all_ope_profit / $tani), $keta);
    $p1_rui_all_ope_profit            = number_format(($p1_rui_all_ope_profit / $tani), $keta);
    $def_all_ope_profit               = number_format(($def_all_ope_profit / $tani), $keta);
    $h1_all_nonope_profit_sum         = number_format(($h1_all_nonope_profit_sum / $tani), $keta);
    $h2_all_nonope_profit_sum         = number_format(($h2_all_nonope_profit_sum / $tani), $keta);
    $h3_all_nonope_profit_sum         = number_format(($h3_all_nonope_profit_sum / $tani), $keta);
    $h4_all_nonope_profit_sum         = number_format(($h4_all_nonope_profit_sum / $tani), $keta);
    $rui_all_nonope_profit_sum        = number_format(($rui_all_nonope_profit_sum / $tani), $keta);
    $p1_rui_all_nonope_profit_sum     = number_format(($p1_rui_all_nonope_profit_sum / $tani), $keta);
    $def_all_nonope_profit_sum        = number_format(($def_all_nonope_profit_sum / $tani), $keta);
    $h1_all_nonope_loss_sum           = number_format(($h1_all_nonope_loss_sum / $tani), $keta);
    $h2_all_nonope_loss_sum           = number_format(($h2_all_nonope_loss_sum / $tani), $keta);
    $h3_all_nonope_loss_sum           = number_format(($h3_all_nonope_loss_sum / $tani), $keta);
    $h4_all_nonope_loss_sum           = number_format(($h4_all_nonope_loss_sum / $tani), $keta);
    $rui_all_nonope_loss_sum          = number_format(($rui_all_nonope_loss_sum / $tani), $keta);
    $p1_rui_all_nonope_loss_sum       = number_format(($p1_rui_all_nonope_loss_sum / $tani), $keta);
    $def_all_nonope_loss_sum          = number_format(($def_all_nonope_loss_sum / $tani), $keta);
    $h1_all_current_profit            = number_format(($h1_all_current_profit / $tani), $keta);
    $h2_all_current_profit            = number_format(($h2_all_current_profit / $tani), $keta);
    $h3_all_current_profit            = number_format(($h3_all_current_profit / $tani), $keta);
    $h4_all_current_profit            = number_format(($h4_all_current_profit / $tani), $keta);
    $rui_all_current_profit           = number_format(($rui_all_current_profit / $tani), $keta);
    $p1_rui_all_current_profit        = number_format(($p1_rui_all_current_profit / $tani), $keta);
    $def_all_current_profit           = number_format(($def_all_current_profit / $tani), $keta);
    $h1_all_special_profit            = number_format(($h1_all_special_profit / $tani), $keta);
    $h2_all_special_profit            = number_format(($h2_all_special_profit / $tani), $keta);
    $h3_all_special_profit            = number_format(($h3_all_special_profit / $tani), $keta);
    $h4_all_special_profit            = number_format(($h4_all_special_profit / $tani), $keta);
    $rui_all_special_profit           = number_format(($rui_all_special_profit / $tani), $keta);
    $p1_rui_all_special_profit        = number_format(($p1_rui_all_special_profit / $tani), $keta);
    $def_all_special_profit           = number_format(($def_all_special_profit / $tani), $keta);
    $h1_all_special_loss              = number_format(($h1_all_special_loss / $tani), $keta);
    $h2_all_special_loss              = number_format(($h2_all_special_loss / $tani), $keta);
    $h3_all_special_loss              = number_format(($h3_all_special_loss / $tani), $keta);
    $h4_all_special_loss              = number_format(($h4_all_special_loss / $tani), $keta);
    $rui_all_special_loss             = number_format(($rui_all_special_loss / $tani), $keta);
    $p1_rui_all_special_loss          = number_format(($p1_rui_all_special_loss / $tani), $keta);
    $def_all_special_loss             = number_format(($def_all_special_loss / $tani), $keta);
    $h1_all_before_tax_net_profit     = number_format(($h1_all_before_tax_net_profit / $tani), $keta);
    $h2_all_before_tax_net_profit     = number_format(($h2_all_before_tax_net_profit / $tani), $keta);
    $h3_all_before_tax_net_profit     = number_format(($h3_all_before_tax_net_profit / $tani), $keta);
    $h4_all_before_tax_net_profit     = number_format(($h4_all_before_tax_net_profit / $tani), $keta);
    $rui_all_before_tax_net_profit    = number_format(($rui_all_before_tax_net_profit / $tani), $keta);
    $p1_rui_all_before_tax_net_profit = number_format(($p1_rui_all_before_tax_net_profit / $tani), $keta);
    $def_all_before_tax_net_profit    = number_format(($def_all_before_tax_net_profit / $tani), $keta);
    $h1_all_corporation_tax_etc       = number_format(($h1_all_corporation_tax_etc / $tani), $keta);
    $h2_all_corporation_tax_etc       = number_format(($h2_all_corporation_tax_etc / $tani), $keta);
    $h3_all_corporation_tax_etc       = number_format(($h3_all_corporation_tax_etc / $tani), $keta);
    $h4_all_corporation_tax_etc       = number_format(($h4_all_corporation_tax_etc / $tani), $keta);
    $rui_all_corporation_tax_etc      = number_format(($rui_all_corporation_tax_etc / $tani), $keta);
    $p1_rui_all_corporation_tax_etc   = number_format(($p1_rui_all_corporation_tax_etc / $tani), $keta);
    $def_all_corporation_tax_etc      = number_format(($def_all_corporation_tax_etc / $tani), $keta);
    $h1_all_pure_profit               = number_format(($h1_all_pure_profit / $tani), $keta);
    $h2_all_pure_profit               = number_format(($h2_all_pure_profit / $tani), $keta);
    $h3_all_pure_profit               = number_format(($h3_all_pure_profit / $tani), $keta);
    $h4_all_pure_profit               = number_format(($h4_all_pure_profit / $tani), $keta);
    $rui_all_pure_profit              = number_format(($rui_all_pure_profit / $tani), $keta);
    $p1_rui_all_pure_profit           = number_format(($p1_rui_all_pure_profit / $tani), $keta);
    $def_all_pure_profit              = number_format(($def_all_pure_profit / $tani), $keta);
} else{
    $all_uri                          = number_format(($all_uri / $tani), $keta);
    $p1_all_uri                       = number_format(($p1_all_uri / $tani), $keta);
    $p2_all_uri                       = number_format(($p2_all_uri / $tani), $keta);
    $t_all_uri                        = number_format(($t_all_uri / $tani), $keta);
    $rui_all_uri                      = number_format(($rui_all_uri / $tani), $keta);
    $p1_rui_all_uri                   = number_format(($p1_rui_all_uri / $tani), $keta);
    $def_all_uri                      = number_format(($def_all_uri / $tani), $keta);
    $all_urigen                       = number_format(($all_urigen / $tani), $keta);
    $p1_all_urigen                    = number_format(($p1_all_urigen / $tani), $keta);
    $p2_all_urigen                    = number_format(($p2_all_urigen / $tani), $keta);
    $t_all_urigen                     = number_format(($t_all_urigen / $tani), $keta);
    $rui_all_urigen                   = number_format(($rui_all_urigen / $tani), $keta);
    $p1_rui_all_urigen                = number_format(($p1_rui_all_urigen / $tani), $keta);
    $def_all_urigen                   = number_format(($def_all_urigen / $tani), $keta);
    $all_gross_profit                 = number_format(($all_gross_profit / $tani), $keta);
    $p1_all_gross_profit              = number_format(($p1_all_gross_profit / $tani), $keta);
    $p2_all_gross_profit              = number_format(($p2_all_gross_profit / $tani), $keta);
    $t_all_gross_profit               = number_format(($t_all_gross_profit / $tani), $keta);
    $rui_all_gross_profit             = number_format(($rui_all_gross_profit / $tani), $keta);
    $p1_rui_all_gross_profit          = number_format(($p1_rui_all_gross_profit / $tani), $keta);
    $def_all_gross_profit             = number_format(($def_all_gross_profit / $tani), $keta);
    $all_han_all                      = number_format(($all_han_all / $tani), $keta);
    $p1_all_han_all                   = number_format(($p1_all_han_all / $tani), $keta);
    $p2_all_han_all                   = number_format(($p2_all_han_all / $tani), $keta);
    $t_all_han_all                    = number_format(($t_all_han_all / $tani), $keta);
    $rui_all_han_all                  = number_format(($rui_all_han_all / $tani), $keta);
    $p1_rui_all_han_all               = number_format(($p1_rui_all_han_all / $tani), $keta);
    $def_all_han_all                  = number_format(($def_all_han_all / $tani), $keta);
    $all_ope_profit                   = number_format(($all_ope_profit / $tani), $keta);
    $p1_all_ope_profit                = number_format(($p1_all_ope_profit / $tani), $keta);
    $p2_all_ope_profit                = number_format(($p2_all_ope_profit / $tani), $keta);
    $t_all_ope_profit                 = number_format(($t_all_ope_profit / $tani), $keta);
    $rui_all_ope_profit               = number_format(($rui_all_ope_profit / $tani), $keta);
    $p1_rui_all_ope_profit            = number_format(($p1_rui_all_ope_profit / $tani), $keta);
    $def_all_ope_profit               = number_format(($def_all_ope_profit / $tani), $keta);
    $all_nonope_profit_sum            = number_format(($all_nonope_profit_sum / $tani), $keta);
    $p1_all_nonope_profit_sum         = number_format(($p1_all_nonope_profit_sum / $tani), $keta);
    $p2_all_nonope_profit_sum         = number_format(($p2_all_nonope_profit_sum / $tani), $keta);
    $t_all_nonope_profit_sum          = number_format(($t_all_nonope_profit_sum / $tani), $keta);
    $rui_all_nonope_profit_sum        = number_format(($rui_all_nonope_profit_sum / $tani), $keta);
    $p1_rui_all_nonope_profit_sum     = number_format(($p1_rui_all_nonope_profit_sum / $tani), $keta);
    $def_all_nonope_profit_sum        = number_format(($def_all_nonope_profit_sum / $tani), $keta);
    $all_nonope_loss_sum              = number_format(($all_nonope_loss_sum / $tani), $keta);
    $p1_all_nonope_loss_sum           = number_format(($p1_all_nonope_loss_sum / $tani), $keta);
    $p2_all_nonope_loss_sum           = number_format(($p2_all_nonope_loss_sum / $tani), $keta);
    $t_all_nonope_loss_sum            = number_format(($t_all_nonope_loss_sum / $tani), $keta);
    $rui_all_nonope_loss_sum          = number_format(($rui_all_nonope_loss_sum / $tani), $keta);
    $p1_rui_all_nonope_loss_sum       = number_format(($p1_rui_all_nonope_loss_sum / $tani), $keta);
    $def_all_nonope_loss_sum          = number_format(($def_all_nonope_loss_sum / $tani), $keta);
    $all_current_profit               = number_format(($all_current_profit / $tani), $keta);
    $p1_all_current_profit            = number_format(($p1_all_current_profit / $tani), $keta);
    $p2_all_current_profit            = number_format(($p2_all_current_profit / $tani), $keta);
    $t_all_current_profit             = number_format(($t_all_current_profit / $tani), $keta);
    $rui_all_current_profit           = number_format(($rui_all_current_profit / $tani), $keta);
    $p1_rui_all_current_profit        = number_format(($p1_rui_all_current_profit / $tani), $keta);
    $def_all_current_profit           = number_format(($def_all_current_profit / $tani), $keta);
    $all_special_profit               = number_format(($all_special_profit / $tani), $keta);
    $p1_all_special_profit            = number_format(($p1_all_special_profit / $tani), $keta);
    $p2_all_special_profit            = number_format(($p2_all_special_profit / $tani), $keta);
    $t_all_special_profit             = number_format(($t_all_special_profit / $tani), $keta);
    $rui_all_special_profit           = number_format(($rui_all_special_profit / $tani), $keta);
    $p1_rui_all_special_profit        = number_format(($p1_rui_all_special_profit / $tani), $keta);
    $def_all_special_profit           = number_format(($def_all_special_profit / $tani), $keta);
    $all_special_loss                 = number_format(($all_special_loss / $tani), $keta);
    $p1_all_special_loss              = number_format(($p1_all_special_loss / $tani), $keta);
    $p2_all_special_loss              = number_format(($p2_all_special_loss / $tani), $keta);
    $t_all_special_loss               = number_format(($t_all_special_loss / $tani), $keta);
    $rui_all_special_loss             = number_format(($rui_all_special_loss / $tani), $keta);
    $p1_rui_all_special_loss          = number_format(($p1_rui_all_special_loss / $tani), $keta);
    $def_all_special_loss             = number_format(($def_all_special_loss / $tani), $keta);
    $all_before_tax_net_profit        = number_format(($all_before_tax_net_profit / $tani), $keta);
    $p1_all_before_tax_net_profit     = number_format(($p1_all_before_tax_net_profit / $tani), $keta);
    $p2_all_before_tax_net_profit     = number_format(($p2_all_before_tax_net_profit / $tani), $keta);
    $t_all_before_tax_net_profit      = number_format(($t_all_before_tax_net_profit / $tani), $keta);
    $rui_all_before_tax_net_profit    = number_format(($rui_all_before_tax_net_profit / $tani), $keta);
    $p1_rui_all_before_tax_net_profit = number_format(($p1_rui_all_before_tax_net_profit / $tani), $keta);
    $def_all_before_tax_net_profit    = number_format(($def_all_before_tax_net_profit / $tani), $keta);
    $all_corporation_tax_etc          = number_format(($all_corporation_tax_etc / $tani), $keta);
    $p1_all_corporation_tax_etc       = number_format(($p1_all_corporation_tax_etc / $tani), $keta);
    $p2_all_corporation_tax_etc       = number_format(($p2_all_corporation_tax_etc / $tani), $keta);
    $t_all_corporation_tax_etc        = number_format(($t_all_corporation_tax_etc / $tani), $keta);
    $rui_all_corporation_tax_etc      = number_format(($rui_all_corporation_tax_etc / $tani), $keta);
    $p1_rui_all_corporation_tax_etc   = number_format(($p1_rui_all_corporation_tax_etc / $tani), $keta);
    $def_all_corporation_tax_etc      = number_format(($def_all_corporation_tax_etc / $tani), $keta);
    $all_pure_profit                  = number_format(($all_pure_profit / $tani), $keta);
    $p1_all_pure_profit               = number_format(($p1_all_pure_profit / $tani), $keta);
    $p2_all_pure_profit               = number_format(($p2_all_pure_profit / $tani), $keta);
    $t_all_pure_profit                = number_format(($t_all_pure_profit / $tani), $keta);
    $rui_all_pure_profit              = number_format(($rui_all_pure_profit / $tani), $keta);
    $p1_rui_all_pure_profit           = number_format(($p1_rui_all_pure_profit / $tani), $keta);
    $def_all_pure_profit              = number_format(($def_all_pure_profit / $tani), $keta);
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
<title><?= $menu->out_title() ?></title>
<?= $menu->out_site_java() ?>
<?= $menu->out_css() ?>
<?= $menu->out_jsBaseClass() ?>
<script type=text/javascript language='JavaScript'>
<!--
/* ����ʸ�����������ɤ��������å� */
function isDigit(str) {
    var len=str.length;
    var c;
    for (i=0; i<len; i++) {
        c = str.charAt(i);
        if (("0" > c) || (c > "9")) {
            alert("���Ͱʳ������Ͻ���ޤ���");
            return false;
        }
    }
    return true;
}
function isDigitcho(str) {
    var len=str.length;
    var c;
    for (i=0; i<len; i++) {
        c = str.charAt(i);
        if ((i == 0) && (c == "-")) {
            return true;
        }
        if (("0" > c) || (c > "9")) {
            alert("���Ͱʳ������Ͻ���ޤ���");
            return false;
        }
    }
    return true;
}
/* ������ϥ�����Ȥإե������������� */
function set_focus(){
    document.jin.jin_1.focus();
    document.jin.jin_1.select();
}
function data_input_click(obj) {
    return confirm("����Υǡ�������Ͽ���ޤ���\n���˥ǡ�����������Ͼ�񤭤���ޤ���");
}
// -->
</script>
<style type='text/css'>
<!--
select {
    background-color:teal;
    color:white;
}
textarea {
    background-color:black;
    color:white;
}
input.sousin {
    background-color:red;
}
input.text {
    background-color:black;
    color:white;
}
.pt8 {
    font:normal 8pt;
    font-family: monospace;
}
.pt10 {
    font: normal 10pt;
    font-family: monospace;
}
.pt10b {
    font:bold 10pt;
    font-family: monospace;
}
.pt12b {
    font:bold 12pt;
    font-family: monospace;
}
.title_font {
    font:bold 13.5pt;
    font-family: monospace;
}
.today_font {
    font-size: 10.5pt;
    font-family: monospace;
}
.corporate_name {
    font:bold 10pt;
    font-family: monospace;
}
.margin0 {
    margin:0%;
}
ol {
    line-height: normal;
}
pre {
    font-size: 10.0pt;
    font-family: monospace;
}
.OnOff_font {
    font-size:     8.5pt;
    font-family:   monospace;
}
-->
</style>
</head>
<!--  style='overflow-y:hidden;' -->
<body>
    <center>
<?= $menu->out_title_border() ?>
        <table width='100%' border='1' cellspacing='1' cellpadding='1'>
            <tr>
                <td colspan='2' bgcolor='#d6d3ce' align='center' class='corporate_name'>
                    <?=$menu->out_caption(), "\n"?>
                </td>
                <form method='post' action='<?php echo $menu->out_self() ?>'>
                    <td colspan='14' bgcolor='#d6d3ce' align='right' class='pt10'>
                        ñ��
                        <select name='keihi_tani' class='pt10'>
                        <?php
                            if ($tani == 1000)
                                echo "<option value='1000' selected>�����</option>\n";
                            else
                                echo "<option value='1000'>�����</option>\n";
                            if ($tani == 1)
                                echo "<option value='1' selected>������</option>\n";
                            else
                                echo "<option value='1'>������</option>\n";
                            if ($tani == 1000000)
                                echo "<option value='1000000' selected>ɴ����</option>\n";
                            else
                                echo "<option value='1000000'>ɴ����</option>\n";
                            if ($tani == 10000)
                                echo "<option value='10000' selected>������</option>\n";
                            else
                                echo "<option value='10000'>������</option>\n";
                            if($tani == 100000)
                                echo "<option value='100000' selected>������</option>\n";
                            else
                                echo "<option value='100000'>������</option>\n";
                        ?>
                        </select>
                        ������
                        <select name='keihi_keta' class='pt10'>
                        <?php
                            if ($keta == 0)
                                echo "<option value='0' selected>����</option>\n";
                            else
                                echo "<option value='0'>����</option>\n";
                            if ($keta == 3)
                                echo "<option value='3' selected>����</option>\n";
                            else
                                echo "<option value='3'>����</option>\n";
                            if ($keta == 6)
                                echo "<option value='6' selected>����</option>\n";
                            else
                                echo "<option value='6'>����</option>\n";
                            if ($keta == 1)
                                echo "<option value='1' selected>����</option>\n";
                            else
                                echo "<option value='1'>����</option>\n";
                            if ($keta == 2)
                                echo "<option value='2' selected>����</option>\n";
                            else
                                echo "<option value='2'>����</option>\n";
                            if ($keta == 4)
                                echo "<option value='4' selected>����</option>\n";
                            else
                                echo "<option value='4'>����</option>\n";
                            if ($keta == 5)
                                echo "<option value='5' selected>����</option>\n";
                            else
                                echo "<option value='5'>����</option>\n";
                        ?>
                        </select>
                        <input class='pt10b' type='submit' name='return' value='ñ���ѹ�'>
                    </td>
                </form>
            </tr>
        </table>
    <table width='100%' bgcolor='#d6d3ce' align='center' cellspacing="0" cellpadding="3" border='1'>
        <tr>
        <td>
        <table width='100%' bgcolor='#d6d3ce' align='center' cellspacing="0" cellpadding="3" border='1'>
            <TBODY>
                <tr>
                    <td rowspan='3' colspan='2' width='200' align='center' class='pt10b' bgcolor='#ffffc6'>�ࡡ������</td>
                    <?php if($tuki_chk==3) { ?>
                        <td colspan='5' rowspan='2' align='center' class='pt10b' bgcolor='#ffffc6'>�͡�Ⱦ������»����<BR>��<?php echo $b_yy ?>/04��<?php echo $yy ?>/<?php echo $mm ?>��</td>
                    <?php } else { ?>
                        <td colspan='4' rowspan='2' align='center' class='pt10b' bgcolor='#ffffc6'>�衡<?php echo $hanki ?>���͡�Ⱦ������»����<BR>��<?php echo $yy ?>/<?php echo $p2_mm ?>��<?php echo $yy ?>/<?php echo $mm ?>��</td>
                    <?php } ?>
                    <?php if($tuki_chk==3) { ?>
                        <td colspan='4' rowspan='2' align='center' class='pt10b' bgcolor='#ffffc6' style='border-right-style:none;'>�衡<?php echo $ki ?>������»���ס��ߡ���<BR>��<?php echo $b_yy ?>/04��<?php echo $yy ?>/<?php echo $mm ?>��</td>
                    <?php } else { ?>
                        <td colspan='4' rowspan='2' align='center' class='pt10b' bgcolor='#ffffc6' style='border-right-style:none;'>��<?php echo $hanki ?>��Ⱦ���ޤǤ��߷�<BR>��<?php echo $yy ?>/04��<?php echo $yy ?>/<?php echo $mm ?>��</td>
                    <?php } ?>
                    <td colspan='3' align='center' class='pt10b' bgcolor='#ffffc6' style='border-left-style:none;'>��</td>
                </tr>
                <tr>
                    <?php if($tuki_chk==3) { ?>
                        <td colspan='3' nowrap align='center' class='pt10b' bgcolor='#ffffc6'>������ӡ�<?php echo $b2_yy ?>/04��<?php echo $b_yy ?>/<?php echo $mm ?>��</td>
                    <?php } else { ?>
                        <td colspan='3' nowrap align='center' class='pt10b' bgcolor='#ffffc6'>������ӡ�<?php echo $b_yy ?>/04��<?php echo $b_yy ?>/<?php echo $mm ?>��</td>
                    <?php } ?>
                </tr>
                <tr>
                    <?php if($tuki_chk==3) { ?>
                        <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>�裱��Ⱦ��</td>
                        <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>�裲��Ⱦ��</td>
                        <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>�裳��Ⱦ��</td>
                        <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>�裴��Ⱦ��</td>
                    <?php } else { ?>
                        <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'><?php echo $yy ?>/<?php echo $p2_mm ?>��</td>
                        <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'><?php echo $yy ?>/<?php echo $p1_mm ?>��</td>
                        <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'><?php echo $yy ?>/<?php echo $mm ?>��</td>
                    <?php } ?>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>��</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#d6d3ce'>ͽ������</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>�¡�����</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#d6d3ce'>ͽ������</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#d6d3ce'>ã��Ψ</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>��������</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>������</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>����Ψ</td>
                </tr>
                <tr>
                    <td colspan='2' nowrap align='center' class='pt10b' bgcolor='#ceffce'>�䡡�塡��</td>
                    <?php if($tuki_chk==3) { ?>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $h1_all_uri ?></td>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $h2_all_uri ?></td>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $h3_all_uri ?></td>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $h4_all_uri ?></td>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $rui_all_uri ?></td>
                    <?php } else { ?>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p2_all_uri ?></td>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_all_uri ?></td>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $all_uri ?></td>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $t_all_uri ?></td>
                    <?php } ?>
                    <td nowrap align='right' class='pt10b' bgcolor='#d6d3ce'>��</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $rui_all_uri ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#d6d3ce'>��</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#d6d3ce'>��</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_rui_all_uri ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $def_all_uri ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ceffce'><?php echo $def_all_uri_rate ?>%</td>
                </tr>
                <tr>
                    <td nowrap align='center' class='pt10' bgcolor='#ceffce' style='border-bottom-style:none;'>��</td>
                    <td nowrap align='left' class='pt10' bgcolor='white'>���䡡�塡������</td>
                    <?php if($tuki_chk==3) { ?>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $h1_all_urigen ?></td>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $h2_all_urigen ?></td>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $h3_all_urigen ?></td>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $h4_all_urigen ?></td>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_all_urigen ?></td>
                    <?php } else { ?>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_all_urigen ?></td>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_all_urigen ?></td>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $all_urigen ?></td>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $t_all_urigen ?></td>
                    <?php } ?>
                    <td nowrap align='right' class='pt10' bgcolor='#d6d3ce'>��</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_all_urigen ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#d6d3ce'>��</td>
                    <td nowrap align='right' class='pt10' bgcolor='#d6d3ce'>��</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_rui_all_urigen ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $def_all_urigen ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $def_all_urigen_rate ?>%</td>
                </tr>
                <tr>
                    <td nowrap align='center' class='pt10' bgcolor='#ceffce' style='border-top-style:none; border-right-style:none;'>��</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ceffce' style='border-left-style:none;'>�䡡�塡��������</td>
                    <?php if($tuki_chk==3) { ?>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $h1_all_gross_profit ?></td>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $h2_all_gross_profit ?></td>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $h3_all_gross_profit ?></td>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $h4_all_gross_profit ?></td>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $rui_all_gross_profit ?></td>
                    <?php } else { ?>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p2_all_gross_profit ?></td>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_all_gross_profit ?></td>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $all_gross_profit ?></td>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $t_all_gross_profit ?></td>
                    <?php } ?>
                    <td nowrap align='right' class='pt10b' bgcolor='#d6d3ce'>��</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $rui_all_gross_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#d6d3ce'>��</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#d6d3ce'>��</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_rui_all_gross_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $def_all_gross_profit ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ceffce'><?php echo $def_all_gross_profit_rate ?>%</td>
                </tr>
                <tr>
                    <td nowrap align='center' class='pt10' bgcolor='#ceffce' style='border-bottom-style:none;'>��</td>
                    <td nowrap align='center' class='pt10' bgcolor='white'>�δ���ڤӰ��̴������</td>
                    <?php if($tuki_chk==3) { ?>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $h1_all_han_all ?></td>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $h2_all_han_all ?></td>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $h3_all_han_all ?></td>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $h4_all_han_all ?></td>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_all_han_all ?></td>
                    <?php } else { ?>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_all_han_all ?></td>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_all_han_all ?></td>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $all_han_all ?></td>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $t_all_han_all ?></td>
                    <?php } ?>
                    <td nowrap align='right' class='pt10' bgcolor='#d6d3ce'>��</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_all_han_all ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#d6d3ce'>��</td>
                    <td nowrap align='right' class='pt10' bgcolor='#d6d3ce'>��</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_rui_all_han_all ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $def_all_han_all ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $def_all_han_all_rate ?>%</td>
                </tr>
                <tr>
                    <td nowrap align='center' class='pt10' bgcolor='#ceffce' style='border-top-style:none; border-right-style:none;'>��</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ceffce' style='border-left-style:none;'>�ġ��ȡ�������</td>
                    <?php if($tuki_chk==3) { ?>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $h1_all_ope_profit ?></td>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $h2_all_ope_profit ?></td>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $h3_all_ope_profit ?></td>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $h4_all_ope_profit ?></td>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $rui_all_ope_profit ?></td>
                    <?php } else { ?>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p2_all_ope_profit ?></td>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_all_ope_profit ?></td>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $all_ope_profit ?></td>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $t_all_ope_profit ?></td>
                    <?php } ?>
                    <td nowrap align='right' class='pt10b' bgcolor='#d6d3ce'>��</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $rui_all_ope_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#d6d3ce'>��</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#d6d3ce'>��</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_rui_all_ope_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $def_all_ope_profit ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ceffce'><?php echo $def_all_ope_profit_rate ?>%</td>
                </tr>
                <tr>
                    <td nowrap align='center' class='pt10' bgcolor='#ceffce' style='border-bottom-style:none;'>��</td>
                    <td nowrap align='left' class='pt10' bgcolor='white'>���Ķȳ����� ��</td>
                    <?php if($tuki_chk==3) { ?>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $h1_all_nonope_profit_sum ?></td>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $h2_all_nonope_profit_sum ?></td>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $h3_all_nonope_profit_sum ?></td>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $h4_all_nonope_profit_sum ?></td>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_all_nonope_profit_sum ?></td>
                    <?php } else { ?>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_all_nonope_profit_sum ?></td>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_all_nonope_profit_sum ?></td>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $all_nonope_profit_sum ?></td>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $t_all_nonope_profit_sum ?></td>
                    <?php } ?>
                    <td nowrap align='right' class='pt10' bgcolor='#d6d3ce'>��</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_all_nonope_profit_sum ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#d6d3ce'>��</td>
                    <td nowrap align='right' class='pt10' bgcolor='#d6d3ce'>��</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_rui_all_nonope_profit_sum ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $def_all_nonope_profit_sum ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $def_all_nonope_profit_sum_rate ?>%</td>
                </tr>
                <tr>
                    <td nowrap align='center' class='pt10' bgcolor='#ceffce' style='border-bottom-style:none; border-top-style:none;'>��</td>
                    <td nowrap align='left' class='pt10' bgcolor='white'>���Ķȳ����� ��</td>
                    <?php if($tuki_chk==3) { ?>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $h1_all_nonope_loss_sum ?></td>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $h2_all_nonope_loss_sum ?></td>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $h3_all_nonope_loss_sum ?></td>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $h4_all_nonope_loss_sum ?></td>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_all_nonope_loss_sum ?></td>
                    <?php } else { ?>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_all_nonope_loss_sum ?></td>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_all_nonope_loss_sum ?></td>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $all_nonope_loss_sum ?></td>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $t_all_nonope_loss_sum ?></td>
                    <?php } ?>
                    <td nowrap align='right' class='pt10' bgcolor='#d6d3ce'>��</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_all_nonope_loss_sum ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#d6d3ce'>��</td>
                    <td nowrap align='right' class='pt10' bgcolor='#d6d3ce'>��</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_rui_all_nonope_loss_sum ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $def_all_nonope_loss_sum ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $def_all_nonope_loss_sum_rate ?>%</td>
                <tr>
                    <td nowrap align='center' class='pt10' bgcolor='#ceffce' style='border-top-style:none; border-right-style:none;'>��</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ceffce' style='border-left-style:none;'>�С��������</td>
                    <?php if($tuki_chk==3) { ?>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $h1_all_current_profit ?></td>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $h2_all_current_profit ?></td>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $h3_all_current_profit ?></td>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $h4_all_current_profit ?></td>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $rui_all_current_profit ?></td>
                    <?php } else { ?>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p2_all_current_profit ?></td>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_all_current_profit ?></td>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $all_current_profit ?></td>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $t_all_current_profit ?></td>
                    <?php } ?>
                    <td nowrap align='right' class='pt10b' bgcolor='#d6d3ce'>��</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $rui_all_current_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#d6d3ce'>��</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#d6d3ce'>��</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_rui_all_current_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $def_all_current_profit ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ceffce'><?php echo $def_all_current_profit_rate ?>%</td>
                </tr>
                <tr>
                    <td nowrap align='center' class='pt10' bgcolor='#ceffce' style='border-bottom-style:none;'>��</td>
                    <td nowrap align='left' class='pt10' bgcolor='white'>���á��̡�������</td>
                    <?php if($tuki_chk==3) { ?>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $h1_all_special_profit ?></td>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $h2_all_special_profit ?></td>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $h3_all_special_profit ?></td>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $h4_all_special_profit ?></td>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_all_special_profit ?></td>
                    <?php } else { ?>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_all_special_profit ?></td>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_all_special_profit ?></td>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $all_special_profit ?></td>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $t_all_special_profit ?></td>
                    <?php } ?>
                    <td nowrap align='right' class='pt10' bgcolor='#d6d3ce'>��</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_all_special_profit ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#d6d3ce'>��</td>
                    <td nowrap align='right' class='pt10' bgcolor='#d6d3ce'>��</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_rui_all_special_profit ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $def_all_special_profit ?></td>
                    <td nowrap align='center' class='pt10' bgcolor='white'>��</td>
                </tr>
                <tr>
                    <td nowrap align='center' class='pt10' bgcolor='#ceffce' style='border-bottom-style:none; border-top-style:none;'>��</td>
                    <td nowrap align='left' class='pt10' bgcolor='white'>���á��̡�»����</td>
                    <?php if($tuki_chk==3) { ?>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $h1_all_special_loss ?></td>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $h2_all_special_loss ?></td>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $h3_all_special_loss ?></td>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $h4_all_special_loss ?></td>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_all_special_loss ?></td>
                    <?php } else { ?>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_all_special_loss ?></td>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_all_special_loss ?></td>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $all_special_loss ?></td>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $t_all_special_loss ?></td>
                    <?php } ?>
                    <td nowrap align='right' class='pt10' bgcolor='#d6d3ce'>��</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_all_special_loss ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#d6d3ce'>��</td>
                    <td nowrap align='right' class='pt10' bgcolor='#d6d3ce'>��</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_rui_all_special_loss ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $def_all_special_loss ?></td>
                    <td nowrap align='center' class='pt10' bgcolor='white'>��</td>
                </tr>
                <tr>
                    <td nowrap align='center' class='pt10' bgcolor='#ceffce' style='border-top-style:none; border-right-style:none;'>��</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ceffce' style='border-left-style:none;'>�ǰ����������׶��</td>
                    <?php if($tuki_chk==3) { ?>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $h1_all_before_tax_net_profit ?></td>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $h2_all_before_tax_net_profit ?></td>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $h3_all_before_tax_net_profit ?></td>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $h4_all_before_tax_net_profit ?></td>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $rui_all_before_tax_net_profit ?></td>
                    <?php } else { ?>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p2_all_before_tax_net_profit ?></td>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_all_before_tax_net_profit ?></td>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $all_before_tax_net_profit ?></td>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $t_all_before_tax_net_profit ?></td>
                    <?php } ?>
                    <td nowrap align='right' class='pt10b' bgcolor='#d6d3ce'>��</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $rui_all_before_tax_net_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#d6d3ce'>��</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#d6d3ce'>��</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_rui_all_before_tax_net_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $def_all_before_tax_net_profit ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ceffce'><?php echo $def_all_before_tax_net_profit_rate ?>%</td>
                </tr>
                <tr>
                    <td nowrap align='center' class='pt10' bgcolor='#ceffce' style='border-bottom-style:none;'>��</td>
                    <td nowrap align='left' class='pt10' bgcolor='white'>��ˡ���ǡ�������</td>
                    <?php if($tuki_chk==3) { ?>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $h1_all_corporation_tax_etc ?></td>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $h2_all_corporation_tax_etc ?></td>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $h3_all_corporation_tax_etc ?></td>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $h4_all_corporation_tax_etc ?></td>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_all_corporation_tax_etc ?></td>
                    <?php } else { ?>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_all_corporation_tax_etc ?></td>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_all_corporation_tax_etc ?></td>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $all_corporation_tax_etc ?></td>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $t_all_corporation_tax_etc ?></td>
                    <?php } ?>
                    <td nowrap align='right' class='pt10' bgcolor='#d6d3ce'>��</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_all_corporation_tax_etc ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#d6d3ce'>��</td>
                    <td nowrap align='right' class='pt10' bgcolor='#d6d3ce'>��</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_rui_all_corporation_tax_etc ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $def_all_corporation_tax_etc ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $def_all_corporation_tax_etc_rate ?>%</td>
                </tr>
                <tr>
                    <td nowrap align='center' class='pt10' bgcolor='#ceffce' style='border-top-style:none; border-right-style:none;'>��</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ceffce' style='border-left-style:none;'>��������������</td>
                    <?php if($tuki_chk==3) { ?>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $h1_all_pure_profit ?></td>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $h2_all_pure_profit ?></td>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $h3_all_pure_profit ?></td>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $h4_all_pure_profit ?></td>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $rui_all_pure_profit ?></td>
                    <?php } else { ?>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p2_all_pure_profit ?></td>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_all_pure_profit ?></td>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $all_pure_profit ?></td>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $t_all_pure_profit ?></td>
                    <?php } ?>
                    <td nowrap align='right' class='pt10b' bgcolor='#d6d3ce'>��</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $rui_all_pure_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#d6d3ce'>��</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#d6d3ce'>��</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_rui_all_pure_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $def_all_pure_profit ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ceffce'><?php echo $def_all_pure_profit_rate ?>%</td>
                </tr>
            </TBODY>
        </table>
        </td>
        </tr>
    </table>
    </center>
</body>
</html>
