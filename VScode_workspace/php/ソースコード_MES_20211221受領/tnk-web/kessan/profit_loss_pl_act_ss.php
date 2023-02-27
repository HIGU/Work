<?php
//////////////////////////////////////////////////////////////////////////////
// �»�״ط� � ����� CL »�׷׻���                                 //
// Copyright (C) 2016 -      Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp  //
// Changed history                                                          //
// 2016/07/25 Created   profit_loss_pl_act_ss.php                           //
// 2016/08/01 �������Ի��˥��顼�ˤʤ�Τ�����0�ˤ����                   //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug ��
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
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
///// �ƽ����action̾�ȥ��ɥ쥹����
$menu->set_action('�õ���������',   PL . 'profit_loss_comment_put_ss.php');

///// ������μ���
$ki = Ym_to_tnk($_SESSION['pl_ym']);
$tuki = substr($_SESSION['pl_ym'],4,2);

///// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title("�� {$ki} ����{$tuki} ���١���������ã� �� �� �� » �� �� �� ��");

///// �о�����
$yyyymm = $_SESSION['pl_ym'];
///// �о�����
if (substr($yyyymm,4,2)!=01) {
    $p1_ym = $yyyymm - 1;
} else {
    $p1_ym = $yyyymm - 100;
    $p1_ym = $p1_ym + 11;
}
///// �о�������
if (substr($p1_ym,4,2)!=01) {
    $p2_ym = $p1_ym - 1;
} else {
    $p2_ym = $p1_ym - 100;
    $p2_ym = $p2_ym + 11;
}
///// ����ǯ��λ���
$yyyy = substr($yyyymm, 0,4);
$mm   = substr($yyyymm, 4,2);
if (($mm >= 1) && ($mm <= 3)) {
    $yyyy = ($yyyy - 1);
}
$str_ym = $yyyy . "04";     // ����ǯ��

//�о�ǯ����
$ymd_str = $yyyymm . "01";
$ymd_end = $yyyymm . "99";
//�о���ǯ����
$p1_ymd_str = $p1_ym . "01";
$p1_ymd_end = $p1_ym . "99";
//�о�����ǯ����
$p2_ymd_str = $p2_ym . "01";
$p2_ymd_end = $p2_ym . "99";
//����ǯ����
$str_ymd = $str_ym . "01";

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

/********** ���� **********/
    ///// ����
$query = sprintf("select sum(Uround(����*ñ��,0)) as t_kingaku from hiuuri where �׾���>=%d and �׾���<=%d and ������='L' and (assyno like 'SS%%')", $ymd_str, $ymd_end);
if (getUniResult($query, $st_uri) < 1) {
    $st_uri        = 0;     // ��������
    $st_uri_sagaku = 0;
    $st_uri_temp   = 0;
} else {
    $st_uri_temp   = $st_uri;
    $st_uri_sagaku = $st_uri;
    $st_uri        = number_format(($st_uri / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�����'", $yyyymm);
if (getUniResult($query, $sc_uri) < 1) {
    $sc_uri        = 0;     // ��������
    $sc_uri_sagaku = 0;
    $sc_uri_temp   = 0;
} else {
    $sc_uri_temp   = $sc_uri;
    $sc_uri_sagaku = $sc_uri;
    $sc_uri        = number_format(($sc_uri / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�����'", $yyyymm);
if (getUniResult($query, $s_uri) < 1) {
    $s_uri        = 0;     // ��������
    $s_uri_sagaku = 0;
    $s_uri_temp   = 0;
    $sl_uri       = 0;     // ��������
    $sl_uri_temp  = 0;
} else {
    $s_uri_temp = $s_uri;
    if ($yyyymm == 200906) {
        $s_uri = $s_uri - 3100900;
    } elseif ($yyyymm == 200905) {
        $s_uri = $s_uri + 1550450;
    } elseif ($yyyymm == 200904) {
        $s_uri = $s_uri + 1550450;
    }
    $s_uri_sagaku = $s_uri;
    $sl_uri       = $s_uri;
    $sl_uri_temp  = $sl_uri;
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='����Ĵ����'", $yyyymm);
if (getUniResult($query, $s_uri_cho) < 1) {
    // ��������
    $s_uri        = number_format(($s_uri / $tani), $keta);
    $sl_uri       = number_format(($sl_uri / $tani), $keta);
    $ss_uri       = 0;
    $ss_uri_temp  = 0;
} else {
    $s_uri_sagaku = $s_uri_sagaku + $s_uri_cho;
    $s_uri_temp   = $s_uri_sagaku;
    $sl_uri       = $s_uri_temp;                             // ��˥���������ݴ�
    $sl_uri_temp  = $sl_uri;                                 // ��˥��������»�׷׻���temp
    $s_uri        = $s_uri_sagaku + $sc_uri_sagaku;          // ���ץ�������̣��temp�β� ��˥�����ޥ��ʥ����Ƥ��ޤ��١�
    
    // �ѵס������׻�(�ѵ�st������ss)
    $ss_uri       = $s_uri - $st_uri_temp;
    $ss_uri_temp  = $ss_uri;
    if ($s_uri <> 0) {
        $st_uri_allo     = Uround(($st_uri_temp / $s_uri), 3);    // �ѵ�����Ψ
        $ss_uri_allo     = 1 - $st_uri_allo;                      // ��������Ψ
    } else {
        $st_uri_allo = 0;
        $st_uri_allo = 0;
    }
    $s_uri        = number_format(($s_uri / $tani), $keta);
    $ss_uri       = number_format(($ss_uri / $tani), $keta);
}
    ///// ����
$query = sprintf("select sum(Uround(����*ñ��,0)) as t_kingaku from hiuuri where �׾���>=%d and �׾���<=%d and ������='L' and (assyno like 'SS%%')", $p1_ymd_str, $p1_ymd_end);
if (getUniResult($query, $p1_st_uri) < 1) {
    $p1_st_uri        = 0;     // ��������
    $p1_st_uri_sagaku = 0;
    $p1_st_uri_temp   = 0;
} else {
    $p1_st_uri_temp   = $p1_st_uri;
    $p1_st_uri_sagaku = $p1_st_uri;
    $p1_st_uri        = number_format(($p1_st_uri / $tani), $keta);
}

$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�����'", $p1_ym);
if (getUniResult($query, $p1_sc_uri) < 1) {
    $p1_sc_uri        = 0;     // ��������
    $p1_sc_uri_sagaku = 0;
    $p1_sc_uri_temp   = 0;
} else {
    $p1_sc_uri_temp   = $p1_sc_uri;
    $p1_sc_uri_sagaku = $p1_sc_uri;
    $p1_sc_uri        = number_format(($p1_sc_uri / $tani), $keta);
}

$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�����'", $p1_ym);
if (getUniResult($query, $p1_s_uri) < 1) {
    $p1_s_uri        = 0;  // ��������
    $p1_s_uri_sagaku = 0;
    $p1_s_uri_temp   = 0;
    $p1_sl_uri       = 0;  // ��������
    $p1_sl_uri_temp  = 0;
} else {
    $p1_s_uri_temp = $p1_s_uri;
    if ($p1_ym == 200906) {
        $p1_s_uri = $p1_s_uri - 3100900;
    } elseif ($p1_ym == 200905) {
        $p1_s_uri = $p1_s_uri + 1550450;
    } elseif ($p1_ym == 200904) {
        $p1_s_uri = $p1_s_uri + 1550450;
    }
    $p1_s_uri_sagaku = $p1_s_uri;
    $p1_sl_uri       = $p1_s_uri;
    $p1_sl_uri_temp  = $p1_sl_uri;
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='����Ĵ����'", $p1_ym);
if (getUniResult($query, $p1_s_uri_cho) < 1) {
    // ��������
    $p1_s_uri  = number_format(($p1_s_uri / $tani), $keta);
    $p1_sl_uri = number_format(($p1_sl_uri / $tani), $keta);
    $p1_ss_uri       = 0;
    $p1_ss_uri_temp  = 0;
} else {
    $p1_s_uri_sagaku = $p1_s_uri_sagaku + $p1_s_uri_cho;
    $p1_s_uri_temp   = $p1_s_uri_sagaku;
    $p1_sl_uri       = $p1_s_uri_temp;                             // ��˥���������ݴ�
    $p1_sl_uri_temp  = $p1_sl_uri;                                 // ��˥��������»�׷׻���temp
    $p1_s_uri        = $p1_s_uri_sagaku + $p1_sc_uri_sagaku;       // ���ץ�������̣��temp�β� ��˥�����ޥ��ʥ����Ƥ��ޤ��١�
    
    // �ѵס������׻�(�ѵ�st������ss)
    $p1_ss_uri       = $p1_s_uri - $p1_st_uri_temp;
    $p1_ss_uri_temp  = $p1_ss_uri;
    if ($p1_s_uri <> 0) {
        $p1_st_uri_allo     = Uround(($p1_st_uri_temp / $p1_s_uri), 3);     // �ѵ�����Ψ
        $p1_ss_uri_allo     = 1 - $p1_st_uri_allo;                          // ��������Ψ
    } else {
        $p1_st_uri_allo = 0;
        $p1_st_uri_allo = 0;
    }
    $p1_s_uri        = number_format(($p1_s_uri / $tani), $keta);
    $p1_ss_uri       = number_format(($p1_ss_uri / $tani), $keta);
}
    ///// ������
$query = sprintf("select sum(Uround(����*ñ��,0)) as t_kingaku from hiuuri where �׾���>=%d and �׾���<=%d and ������='L' and (assyno like 'SS%%')", $p2_ymd_str, $p2_ymd_end);
if (getUniResult($query, $p2_st_uri) < 1) {
    $p2_st_uri        = 0;     // ��������
    $p2_st_uri_sagaku = 0;
    $p2_st_uri_temp   = 0;
} else {
    $p2_st_uri_temp   = $p2_st_uri;
    $p2_st_uri_sagaku = $p2_st_uri;
    $p2_st_uri        = number_format(($p2_st_uri / $tani), $keta);
}

$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�����'", $p2_ym);
if (getUniResult($query, $p2_sc_uri) < 1) {
    $p2_sc_uri        = 0;     // ��������
    $p2_sc_uri_sagaku = 0;
    $p2_sc_uri_temp   = 0;
} else {
    $p2_sc_uri_temp   = $p2_sc_uri;
    $p2_sc_uri_sagaku = $p2_sc_uri;
    $p2_sc_uri        = number_format(($p2_sc_uri / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�����'", $p2_ym);
if (getUniResult($query, $p2_s_uri) < 1) {
    $p2_s_uri        = 0;  // ��������
    $p2_s_uri_sagaku = 0;
    $p2_s_uri_temp   = 0;
    $p2_sl_uri       = 0;  // ��������
    $p2_sl_uri_temp  = 0;
} else {
    $p2_s_uri_temp = $p2_s_uri;
    if ($p2_ym == 200906) {
        $p2_s_uri  = $p2_s_uri - 3100900;
    } elseif ($p2_ym == 200905) {
        $p2_s_uri  = $p2_s_uri + 1550450;
    } elseif ($p2_ym == 200904) {
        $p2_s_uri  = $p2_s_uri + 1550450;
    }
    $p2_s_uri_sagaku = $p2_s_uri;
    $p2_sl_uri       = $p2_s_uri;
    $p2_sl_uri_temp  = $p2_sl_uri;
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='����Ĵ����'", $p2_ym);
if (getUniResult($query, $p2_s_uri_cho) < 1) {
    // ��������
    $p2_s_uri  = number_format(($p2_s_uri / $tani), $keta);
    $p2_sl_uri = number_format(($p2_sl_uri / $tani), $keta);
    $p2_ss_uri       = 0;
    $p2_ss_uri_temp  = 0;
} else {
    $p2_s_uri_sagaku = $p2_s_uri_sagaku + $p2_s_uri_cho;
    $p2_s_uri_temp   = $p2_s_uri_sagaku;
    $p2_sl_uri       = $p2_s_uri_temp;                             // ��˥���������ݴ�
    $p2_sl_uri_temp  = $p2_sl_uri;                                 // ��˥��������»�׷׻���temp
    $p2_s_uri        = $p2_s_uri_sagaku + $p2_sc_uri_sagaku;       // ���ץ�������̣��temp�β� ��˥�����ޥ��ʥ����Ƥ��ޤ��١�
    
    // �ѵס������׻�(�ѵ�st������ss)
    $p2_ss_uri       = $p2_s_uri - $p2_st_uri_temp;
    $p2_ss_uri_temp  = $p2_ss_uri;
    if ($p2_s_uri <> 0) {
        $p2_st_uri_allo     = Uround(($p2_st_uri_temp / $p2_s_uri), 3);     // �ѵ�����Ψ
        $p2_ss_uri_allo     = 1 - $p2_st_uri_allo;                          // ��������Ψ
    } else {
        $p2_st_uri_allo = 0;
        $p2_st_uri_allo = 0;
    }
    $p2_s_uri        = number_format(($p2_s_uri / $tani), $keta);
    $p2_ss_uri       = number_format(($p2_ss_uri / $tani), $keta);
}

    ///// �����߷�
$query = sprintf("select sum(Uround(����*ñ��,0)) as t_kingaku from hiuuri where �׾���>=%d and �׾���<=%d and ������='L' and (assyno like 'SS%%')", $str_ymd, $ymd_end);
if (getUniResult($query, $rui_st_uri) < 1) {
    $rui_st_uri        = 0;     // ��������
    $rui_st_uri_sagaku = 0;
    $rui_st_uri_temp   = 0;
} else {
    $rui_st_uri_temp   = $rui_st_uri;
    $rui_st_uri_sagaku = $rui_st_uri;
    $rui_st_uri        = number_format(($rui_st_uri / $tani), $keta);
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���ץ�����'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_sc_uri) < 1) {
    $rui_sc_uri        = 0;     // ��������
    $rui_sc_uri_sagaku = 0;
    $rui_sc_uri_temp   = 0;
} else {
    $rui_sc_uri_temp   = $rui_sc_uri;
    $rui_sc_uri_sagaku = $rui_sc_uri;
    $rui_sc_uri        = number_format(($rui_sc_uri / $tani), $keta);
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='�����'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_s_uri) < 1) {
    $rui_s_uri        = 0;     // ��������
    $rui_s_uri_sagaku = 0;
    $rui_sl_uri       = 0;     // ��������
    $rui_sl_uri_temp  = 0;
} else {
    $rui_s_uri_sagaku = $rui_s_uri;
    $rui_sl_uri       = $rui_s_uri;
    $rui_sl_uri_temp  = $rui_sl_uri;
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='����Ĵ����'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_s_uri_cho) < 1) {
    // ��������
    $rui_s_uri  = number_format(($rui_s_uri / $tani), $keta);
    $rui_sl_uri = number_format(($rui_sl_uri / $tani), $keta);
    $rui_ss_uri       = 0;
    $rui_ss_uri_temp  = 0;
} else {
    $rui_s_uri_sagaku = $rui_s_uri_sagaku + $rui_s_uri_cho;
    $rui_sl_uri       = $rui_s_uri_sagaku;                           // ��˥���������ݴ�
    $rui_sl_uri_temp  = $rui_sl_uri;                                 // ��˥��������»�׷׻���temp
    $rui_s_uri        = $rui_s_uri_sagaku + $rui_sc_uri_sagaku;      // ���ץ�������̣��temp�β� ��˥�����ޥ��ʥ����Ƥ��ޤ��١�
    
    // �ѵס������׻�(�ѵ�st������ss)
    $rui_ss_uri       = $rui_s_uri - $rui_st_uri_temp;
    $rui_ss_uri_temp  = $rui_ss_uri;
    $rui_s_uri        = number_format(($rui_s_uri / $tani), $keta);
    $rui_ss_uri       = number_format(($rui_ss_uri / $tani), $keta);
}

/********** ��������ų���ê���� **********/
    ///// �������
$p2_s_invent   = 0;
$p1_s_invent   = 0;
$s_invent      = 0;
$rui_s_invent  = 0;
$p2_st_invent  = 0;
$p1_st_invent  = 0;
$st_invent     = 0;
$rui_st_invent = 0;
$p2_ss_invent  = 0;
$p1_ss_invent  = 0;
$ss_invent     = 0;
$rui_ss_invent = 0;

/********** ������(������) **********/
    ///// ����
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�������'", $yyyymm);
if (getUniResult($query, $sc_metarial) < 1) {
    $sc_metarial        = 0;     // ��������
    $sc_metarial_sagaku = 0;
    $sc_metarial_temp   = 0;
} else {
    $sc_metarial_temp   = $sc_metarial;
    $sc_metarial_sagaku = $sc_metarial;
    $sc_metarial        = number_format(($sc_metarial / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�������'", $yyyymm);
if (getUniResult($query, $s_metarial) < 1) {
    $s_metarial        = 0;   // ��������
    $s_metarial_sagaku = 0;
    $sl_metarial       = 0;
    $ss_metarial       = 0;
    $ss_metarial_temp  = 0;
    $st_metarial       = 0;
    $st_metarial_temp  = 0;
} else {
    $s_metarial_sagaku = $s_metarial;
    $sl_metarial       = $s_metarial;                                  // ��˥��������������ݴ�
    $sl_metarial_temp  = $sl_metarial;                                 // ��˥������»�׷׻��ѡ�temp)
    $s_metarial        = $s_metarial + $sc_metarial_sagaku;            // ���ץ���������̣��sagaku�β� ��˥�����ޥ��ʥ����Ƥ��ޤ��١�
    
    // �ѵס������׻�(�ѵ�st������ss) ���ץ�������Ͻ����ʰ١����Τޤްܹ�
    $ss_metarial       = $sc_metarial_temp;
    $st_metarial       = $s_metarial - $ss_metarial;
    $ss_metarial_temp  = $ss_metarial;
    $st_metarial_temp  = $st_metarial;
    $s_metarial        = number_format(($s_metarial / $tani), $keta);
    $ss_metarial       = number_format(($ss_metarial / $tani), $keta);
    $st_metarial       = number_format(($st_metarial / $tani), $keta);
}
    ///// ����
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�������'", $p1_ym);
if (getUniResult($query, $p1_sc_metarial) < 1) {
    $p1_sc_metarial        = 0;     // ��������
    $p1_sc_metarial_sagaku = 0;
    $p1_sc_metarial_temp   = 0;
} else {
    $p1_sc_metarial_temp   = $p1_sc_metarial;
    $p1_sc_metarial_sagaku = $p1_sc_metarial;
    $p1_sc_metarial        = number_format(($p1_sc_metarial / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�������'", $p1_ym);
if (getUniResult($query, $p1_s_metarial) < 1) {
    $p1_s_metarial        = 0;   // ��������
    $p1_s_metarial_sagaku = 0;
    $p1_sl_metarial       = 0;
    $p1_st_metarial       = 0;
    $p1_ss_metarial_temp  = 0;
    $p1_st_metarial_temp  = 0;
} else {
    $p1_s_metarial_sagaku = $p1_s_metarial;
    $p1_sl_metarial       = $p1_s_metarial;                                  // ��˥��������������ݴ�
    $p1_sl_metarial_temp  = $p1_sl_metarial;                                 // ��˥������»�׷׻��ѡ�temp)
    $p1_s_metarial        = $p1_s_metarial + $p1_sc_metarial_sagaku;         // ���ץ���������̣��sagaku�β� ��˥�����ޥ��ʥ����Ƥ��ޤ��١�
    
    // �ѵס������׻�(�ѵ�st������ss) ���ץ�������Ͻ����ʰ١����Τޤްܹ�
    $p1_ss_metarial       = $p1_sc_metarial_temp;
    $p1_st_metarial       = $p1_s_metarial - $p1_ss_metarial;
    $p1_ss_metarial_temp  = $p1_ss_metarial;
    $p1_st_metarial_temp  = $p1_st_metarial;
    $p1_s_metarial        = number_format(($p1_s_metarial / $tani), $keta);
    $p1_ss_metarial       = number_format(($p1_ss_metarial / $tani), $keta);
    $p1_st_metarial       = number_format(($p1_st_metarial / $tani), $keta);
}
    ///// ������
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�������'", $p2_ym);
if (getUniResult($query, $p2_sc_metarial) < 1) {
    $p2_sc_metarial        = 0;     // ��������
    $p2_sc_metarial_sagaku = 0;
    $p2_sc_metarial_temp   = 0;
} else {
    $p2_sc_metarial_temp   = $p2_sc_metarial;
    $p2_sc_metarial_sagaku = $p2_sc_metarial;
    $p2_sc_metarial        = number_format(($p2_sc_metarial / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�������'", $p2_ym);
if (getUniResult($query, $p2_s_metarial) < 1) {
    $p2_s_metarial        = 0;   // ��������
    $p2_s_metarial_sagaku = 0;
    $p2_sl_metarial       = 0;
    $p2_st_metarial       = 0;
    $p2_ss_metarial_temp  = 0;
    $p2_st_metarial_temp  = 0;
} else {
    $p2_s_metarial_sagaku = $p2_s_metarial;
    $p2_sl_metarial       = $p2_s_metarial;                                  // ��˥��������������ݴ�
    $p2_sl_metarial_temp  = $p2_sl_metarial;                                 // ��˥������»�׷׻��ѡ�temp)
    $p2_s_metarial        = $p2_s_metarial + $p2_sc_metarial_sagaku;         // ���ץ���������̣��sagaku�β� ��˥�����ޥ��ʥ����Ƥ��ޤ��١�
    
    // �ѵס������׻�(�ѵ�st������ss) ���ץ�������Ͻ����ʰ١����Τޤްܹ�
    $p2_ss_metarial       = $p2_sc_metarial_temp;
    $p2_st_metarial       = $p2_s_metarial - $p2_ss_metarial;
    $p2_ss_metarial_temp  = $p2_ss_metarial;
    $p2_st_metarial_temp  = $p2_st_metarial;
    $p2_s_metarial        = number_format(($p2_s_metarial / $tani), $keta);
    $p2_ss_metarial       = number_format(($p2_ss_metarial / $tani), $keta);
    $p2_st_metarial       = number_format(($p2_st_metarial / $tani), $keta);
}
    ///// �����߷�
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���ץ�������'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_sc_metarial) < 1) {
    $rui_sc_metarial        = 0;     // ��������
    $rui_sc_metarial_sagaku = 0;
    $rui_sc_metarial_temp   = 0;
} else {
    $rui_sc_metarial_temp   = $rui_sc_metarial;
    $rui_sc_metarial_sagaku = $rui_sc_metarial;
    $rui_sc_metarial        = number_format(($rui_sc_metarial / $tani), $keta);
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='�������'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_s_metarial) < 1) {
    $rui_s_metarial        = 0;   // ��������
    $rui_s_metarial_sagaku = 0;
    $rui_sl_metarial       = 0;
    $rui_st_metarial       = 0;
    $rui_ss_metarial_temp  = 0;
    $rui_st_metarial_temp  = 0;
} else {
    $rui_s_metarial_sagaku = $rui_s_metarial;
    $rui_sl_metarial       = $rui_s_metarial;                                  // ��˥��������������ݴ�
    $rui_sl_metarial_temp  = $rui_sl_metarial;                                 // ��˥������»�׷׻��ѡ�temp)
    $rui_s_metarial        = $rui_s_metarial + $rui_sc_metarial_sagaku;        // ���ץ���������̣��sagaku�β� ��˥�����ޥ��ʥ����Ƥ��ޤ��١�
    
    // �ѵס������׻�(�ѵ�st������ss) ���ץ�������Ͻ����ʰ١����Τޤްܹ�
    $rui_ss_metarial       = $rui_sc_metarial_temp;
    $rui_st_metarial       = $rui_s_metarial - $rui_ss_metarial;
    $rui_ss_metarial_temp  = $rui_ss_metarial;
    $rui_st_metarial_temp  = $rui_st_metarial;
    $rui_s_metarial        = number_format(($rui_s_metarial / $tani), $keta);
    $rui_ss_metarial       = number_format(($rui_ss_metarial / $tani), $keta);
    $rui_st_metarial       = number_format(($rui_st_metarial / $tani), $keta);
}

/********** ϫ̳�� **********/
    ///// ����
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�ϫ̳��'", $yyyymm);
if (getUniResult($query, $sc_roumu) < 1) {
    $sc_roumu        = 0;     // ��������
    $sc_roumu_sagaku = 0;
    $sc_roumu_temp   = 0;
} else {
    $sc_roumu_temp   = $sc_roumu;
    $sc_roumu_sagaku = $sc_roumu;
    if ($yyyymm == 200912) {
        $sc_roumu = $sc_roumu - 213810;
    }
    $sc_roumu        = number_format(($sc_roumu / $tani), $keta);
}
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���Ϳ�����'", $yyyymm);
    if (getUniResult($query, $s_kyu_kei) < 1) {
        $s_kyu_kei = 0;                    // ��������
        $s_kyu_kin = 0;
    } else {
        $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���Ϳ����Ψ'", $yyyymm);
        if (getUniResult($query, $s_kyu_kin) < 1) {
            $s_kyu_kin = 0;
        }
    }
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�ϫ̳��'", $yyyymm);
if (getUniResult($query, $s_roumu) < 1) {
    $s_roumu         = 0;    // ��������
    $s_roumu_sagaku  = 0;
    $sl_roumu        = 0;
    $sl_roumu_temp   = 0;
    $st_roumu        = 0;
    $st_roumu_temp   = 0;
    $ss_roumu        = 0;
    $ss_roumu_temp   = 0;
} else {
    if ($yyyymm >= 201001) {
        $s_roumu = $s_roumu - $s_kyu_kei + $s_kyu_kin;    // ������Ϳ���̣
        //$s_roumu = $s_roumu - 432323 + 129697;  // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
    }
    $s_roumu_sagaku  = $s_roumu;
    $sl_roumu        = $s_roumu - $sc_roumu_temp;                 // ��˥������ϫ̳���׻�
    $sl_roumu_temp   = $sl_roumu;                                 // ��˥������»�׷׻��ѡ�temp)
    if ($yyyymm == 200912) {
        $s_roumu = $s_roumu - 1409708;
    }
    if ($yyyymm == 200912) {
        $sl_roumu = $sl_roumu - 1195898;
    }
    
    // �ѵס������׻�(�ѵ�st������ss)
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='����ϫ̳��'", $yyyymm);
    if (getUniResult($query, $ss_roumu) < 1) {
        $ss_roumu      = 0;     // ��������
    }
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�ѵ�ϫ̳��'", $yyyymm);
    if (getUniResult($query, $st_roumu) < 1) {
        $st_roumu      = 0;     // ��������
    }
    // ����ϫ̳�񺹳۷׻�
    $ss_roumu_temp  = $ss_roumu;
    $st_roumu_temp  = $st_roumu;
    $s_roumu        = number_format(($s_roumu / $tani), $keta);
    $ss_roumu       = number_format(($ss_roumu / $tani), $keta);
    $st_roumu       = number_format(($st_roumu / $tani), $keta);
}
    ///// ����
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�ϫ̳��'", $p1_ym);
if (getUniResult($query, $p1_sc_roumu) < 1) {
    $p1_sc_roumu        = 0;     // ��������
    $p1_sc_roumu_sagaku = 0;
    $p1_sc_roumu_temp   = 0;
} else {
    $p1_sc_roumu_temp   = $p1_sc_roumu;
    $p1_sc_roumu_sagaku = $p1_sc_roumu;
    if ($p1_ym == 200912) {
        $p1_sc_roumu = $p1_sc_roumu - 213810;
    }
    $p1_sc_roumu        = number_format(($p1_sc_roumu / $tani), $keta);
}
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���Ϳ�����'", $p1_ym);
    if (getUniResult($query, $p1_s_kyu_kei) < 1) {
        $p1_s_kyu_kei = 0;                    // ��������
        $p1_s_kyu_kin = 0;
    } else {
        $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���Ϳ����Ψ'", $p1_ym);
        if (getUniResult($query, $p1_s_kyu_kin) < 1) {
            $p1_s_kyu_kin = 0;
        }
    }
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�ϫ̳��'", $p1_ym);
if (getUniResult($query, $p1_s_roumu) < 1) {
    $p1_s_roumu        = 0;    // ��������
    $p1_s_roumu_sagaku = 0;
    $p1_sl_roumu       = 0;
    $p1_sl_roumu_temp  = 0;
    $p1_ss_roumu       = 0;
    $p1_ss_roumu_temp  = 0;
    $p1_st_roumu       = 0;
    $p1_st_roumu_temp  = 0;
} else {
    if ($p1_ym >= 201001) {
        $p1_s_roumu = $p1_s_roumu - $p1_s_kyu_kei + $p1_s_kyu_kin;    // ������Ϳ���̣
        //$p1_s_roumu = $p1_s_roumu - 432323 + 129697;    // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
    }
    $p1_s_roumu_sagaku = $p1_s_roumu;
    $p1_sl_roumu       = $p1_s_roumu - $p1_sc_roumu_temp;              // ��˥�������������׻�
    $p1_sl_roumu_temp  = $p1_sl_roumu;                                 // ��˥������»�׷׻��ѡ�temp)
    if ($p1_ym == 200912) {
        $p1_s_roumu = $p1_s_roumu - 1409708;
    }
    if ($p1_ym == 200912) {
        $p1_sl_roumu = $p1_sl_roumu - 1195898;
    }
    
    // �ѵס������׻�(�ѵ�st������ss)
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='����ϫ̳��'", $p1_ym);
    if (getUniResult($query, $p1_ss_roumu) < 1) {
        $p1_ss_roumu        = 0;     // ��������
    }
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�ѵ�ϫ̳��'", $p1_ym);
    if (getUniResult($query, $p1_st_roumu) < 1) {
        $p1_st_roumu        = 0;     // ��������
    }
    // ����ϫ̳�񺹳۷׻�
    $p1_ss_roumu_temp  = $p1_ss_roumu;
    $p1_st_roumu_temp  = $p1_st_roumu;
    $p1_s_roumu        = number_format(($p1_s_roumu / $tani), $keta);
    $p1_ss_roumu       = number_format(($p1_ss_roumu / $tani), $keta);
    $p1_st_roumu       = number_format(($p1_st_roumu / $tani), $keta);
}
    ///// ������
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�ϫ̳��'", $p2_ym);
if (getUniResult($query, $p2_sc_roumu) < 1) {
    $p2_sc_roumu        = 0;     // ��������
    $p2_sc_roumu_sagaku = 0;
    $p2_sc_roumu_temp   = 0;
} else {
    $p2_sc_roumu_temp   = $p2_sc_roumu;
    $p2_sc_roumu_sagaku = $p2_sc_roumu;
    if ($p2_ym == 200912) {
        $p2_sc_roumu = $p2_sc_roumu - 213810;
    }
    $p2_sc_roumu        = number_format(($p2_sc_roumu / $tani), $keta);
}
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���Ϳ�����'", $p2_ym);
    if (getUniResult($query, $p2_s_kyu_kei) < 1) {
        $p2_s_kyu_kei = 0;                    // ��������
        $p2_s_kyu_kin = 0;
    } else {
        $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���Ϳ����Ψ'", $p2_ym);
        if (getUniResult($query, $p2_s_kyu_kin) < 1) {
            $p2_s_kyu_kin = 0;
        }
    }
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�ϫ̳��'", $p2_ym);
if (getUniResult($query, $p2_s_roumu) < 1) {
    $p2_s_roumu         = 0;    // ��������
    $p2_s_roumu_sagaku  = 0;
    $p2_sl_roumu        = 0;
    $p2_sl_roumu_temp   = 0;
    $p2_ss_roumu        = 0;
    $p2_ss_roumu_temp   = 0;
    $p2_st_roumu        = 0;
    $p2_st_roumu_temp   = 0;
} else {
    if ($p2_ym >= 201001) {
        $p2_s_roumu = $p2_s_roumu - $p2_s_kyu_kei + $p2_s_kyu_kin;    // ������Ϳ���̣
        //$p2_s_roumu = $p2_s_roumu - 432323 + 129697;    // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
    }
    $p2_s_roumu_sagaku  = $p2_s_roumu;
    $p2_sl_roumu        = $p2_s_roumu - $p2_sc_roumu_temp;              // ��˥�������������׻�
    $p2_sl_roumu_temp   = $p2_sl_roumu;                                 // ��˥������»�׷׻��ѡ�temp)
    if ($p2_ym == 200912) {
        $p2_s_roumu = $p2_s_roumu - 1409708;
    }
    if ($p2_ym == 200912) {
        $p2_sl_roumu = $p2_sl_roumu - 1195898;
    }
    
    // �ѵס������׻�(�ѵ�st������ss)
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='����ϫ̳��'", $p2_ym);
    if (getUniResult($query, $p2_ss_roumu) < 1) {
        $p2_ss_roumu        = 0;     // ��������
    }
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�ѵ�ϫ̳��'", $p2_ym);
    if (getUniResult($query, $p2_st_roumu) < 1) {
        $p2_st_roumu        = 0;     // ��������
    }
    // ����ϫ̳�񺹳۷׻�
    $p2_ss_roumu_temp  = $p2_ss_roumu;
    $p2_st_roumu_temp  = $p2_st_roumu;
    $p2_s_roumu        = number_format(($p2_s_roumu / $tani), $keta);
    $p2_ss_roumu       = number_format(($p2_ss_roumu / $tani), $keta);
    $p2_st_roumu       = number_format(($p2_st_roumu / $tani), $keta);
}
    ///// �����߷�
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���ץ�ϫ̳��'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_sc_roumu) < 1) {
    $rui_sc_roumu        = 0;     // ��������
    $rui_sc_roumu_sagaku = 0;
    $rui_sc_roumu_temp   = 0;
} else {
    $rui_sc_roumu_temp   = $rui_sc_roumu;
    $rui_sc_roumu_sagaku = $rui_sc_roumu;
    if ($yyyymm >= 200912 && $yyyymm <= 201003) {
        $rui_sc_roumu = $rui_sc_roumu - 213810;
    }
    $rui_sc_roumu        = number_format(($rui_sc_roumu / $tani), $keta);
}
if ($yyyymm >= 201001) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���Ϳ�����'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_s_kyu_kei) < 1) {
        $rui_s_kyu_kei = 0;                    // ��������
        $rui_s_kyu_kin = 0;
    } else {
        $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���Ϳ����Ψ'", $str_ym, $yyyymm);
        if (getUniResult($query, $rui_s_kyu_kin) < 1) {
            $rui_s_kyu_kin = 0;
        }
    }
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='�ϫ̳��'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_s_roumu) < 1) {
    $rui_s_roumu         = 0;    // ��������
    $rui_s_roumu_sagaku  = 0;
    $rui_sl_roumu        = 0;
    $rui_sl_roumu_temp   = 0;
    $rui_ss_roumu        = 0;
    $rui_ss_roumu_temp   = 0;
    $rui_st_roumu        = 0;
    $rui_st_roumu_temp   = 0;
} else {
    if ($yyyymm >= 201001) {
        $rui_s_roumu = $rui_s_roumu - $rui_s_kyu_kei + $rui_s_kyu_kin;    // ������Ϳ���̣
        //$rui_s_roumu = $rui_s_roumu - 432323 + 129697;  // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
    }
    $rui_s_roumu_sagaku  = $rui_s_roumu;
    $rui_sl_roumu        = $rui_s_roumu - $rui_sc_roumu_temp;             // ��˥�������������׻�
    $rui_sl_roumu_temp   = $rui_sl_roumu;                                 // ��˥������»�׷׻��ѡ�temp)
    if ($yyyymm >= 200912 && $yyyymm <= 201003) {
        $rui_s_roumu = $rui_s_roumu - 1409708;
    }
    if ($yyyymm >= 200912 && $yyyymm <= 201003) {
        $rui_sl_roumu = $rui_sl_roumu - 1195898;
    }
    
    // �ѵס������׻�(�ѵ�st������ss)
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='����ϫ̳��'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_ss_roumu) < 1) {
        $rui_ss_roumu        = 0;     // ��������
    }
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='�ѵ�ϫ̳��'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_st_roumu) < 1) {
        $rui_st_roumu        = 0;     // ��������
    }
    // ����ϫ̳�񺹳۷׻�
    $rui_ss_roumu_temp  = $rui_ss_roumu;
    $rui_st_roumu_temp  = $rui_st_roumu;
    $rui_s_roumu        = number_format(($rui_s_roumu / $tani), $keta);
    $rui_ss_roumu       = number_format(($rui_ss_roumu / $tani), $keta);
    $rui_st_roumu       = number_format(($rui_st_roumu / $tani), $keta);
}

/********** ����(��¤����) **********/
    ///// ����
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ���¤����'", $yyyymm);
if (getUniResult($query, $sc_expense) < 1) {
    $sc_expense        = 0;     // ��������
    $sc_expense_sagaku = 0;
    $sc_expense_temp   = 0;
} else {
    $sc_expense_temp   = $sc_expense;
    $sc_expense_sagaku = $sc_expense;
    $sc_expense        = number_format(($sc_expense / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���¤����'", $yyyymm);
if (getUniResult($query, $s_expense) < 1) {
    $s_expense         = 0;    // ��������
    $s_expense_sagaku  = 0;
    $sl_expense        = 0;
    $sl_expense_temp   = 0;
    $ss_expense        = 0;
    $ss_expense_temp   = 0;
    $st_expense        = 0;
    $st_expense_temp   = 0;
} else {
    $s_expense_sagaku  = $s_expense;
    $sl_expense        = $s_expense - $sc_expense_temp;               // ��˥��������¤�����׻�
    $sl_expense_temp   = $sl_expense;                                 // ��˥������»�׷׻��ѡ�temp)
    
    // �ѵס������׻�(�ѵ�st������ss)
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='������¤����'", $yyyymm);
    if (getUniResult($query, $ss_expense) < 1) {
        $ss_expense        = 0;     // ��������
    }
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�ѵ���¤����'", $yyyymm);
    if (getUniResult($query, $st_expense) < 1) {
        $st_expense        = 0;     // ��������
    }
    // ����ϫ̳�񺹳۷׻�
    $ss_expense_temp  = $ss_expense;
    $st_expense_temp  = $st_expense;
    $s_expense        = number_format(($s_expense / $tani), $keta);
    $ss_expense       = number_format(($ss_expense / $tani), $keta);
    $st_expense       = number_format(($st_expense / $tani), $keta);
}
    ///// ����
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ���¤����'", $p1_ym);
if (getUniResult($query, $p1_sc_expense) < 1) {
    $p1_sc_expense        = 0;     // ��������
    $p1_sc_expense_sagaku = 0;
    $p1_sc_expense_temp   = 0;
} else {
    $p1_sc_expense_temp   = $p1_sc_expense;
    $p1_sc_expense_sagaku = $p1_sc_expense;
    $p1_sc_expense        = number_format(($p1_sc_expense / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���¤����'", $p1_ym);
if (getUniResult($query, $p1_s_expense) < 1) {
    $p1_s_expense         = 0;    // ��������
    $p1_s_expense_sagaku  = 0;
    $p1_sl_expense        = 0;
    $p1_sl_expense_temp   = 0;
    $p1_ss_expense        = 0;
    $p1_ss_expense_temp   = 0;
    $p1_st_expense        = 0;
    $p1_st_expense_temp   = 0;
} else {
    $p1_s_expense_sagaku  = $p1_s_expense;
    $p1_sl_expense        = $p1_s_expense - $p1_sc_expense_temp;            // ��˥��������¤�����׻�
    $p1_sl_expense_temp   = $p1_sl_expense;                                 // ��˥������»�׷׻��ѡ�temp)
    
    // �ѵס������׻�(�ѵ�st������ss)
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='������¤����'", $p1_ym);
    if (getUniResult($query, $p1_ss_expense) < 1) {
        $p1_ss_expense        = 0;     // ��������
    }
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�ѵ���¤����'", $p1_ym);
    if (getUniResult($query, $p1_st_expense) < 1) {
        $p1_st_expense        = 0;     // ��������
    }
    // ����ϫ̳�񺹳۷׻�
    $p1_ss_expense_temp  = $p1_ss_expense;
    $p1_st_expense_temp  = $p1_st_expense;
    $p1_s_expense        = number_format(($p1_s_expense / $tani), $keta);
    $p1_ss_expense       = number_format(($p1_ss_expense / $tani), $keta);
    $p1_st_expense       = number_format(($p1_st_expense / $tani), $keta);
}
    ///// ������
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ���¤����'", $p2_ym);
if (getUniResult($query, $p2_sc_expense) < 1) {
    $p2_sc_expense        = 0;     // ��������
    $p2_sc_expense_sagaku = 0;
    $p2_sc_expense_temp   = 0;
} else {
    $p2_sc_expense_temp   = $p2_sc_expense;
    $p2_sc_expense_sagaku = $p2_sc_expense;
    $p2_sc_expense        = number_format(($p2_sc_expense / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���¤����'", $p2_ym);
if (getUniResult($query, $p2_s_expense) < 1) {
    $p2_s_expense         = 0;    // ��������
    $p2_s_expense_sagaku  = 0;
    $p2_sl_expense        = 0;
    $p2_sl_expense_temp   = 0;
    $p2_ss_expense        = 0;
    $p2_ss_expense_temp   = 0;
    $p2_st_expense        = 0;
    $p2_st_expense_temp   = 0;
} else {
    $p2_s_expense_sagaku  = $p2_s_expense;
    $p2_sl_expense        = $p2_s_expense - $p2_sc_expense_temp;            // ��˥��������¤�����׻�
    $p2_sl_expense_temp   = $p2_sl_expense;                                 // ��˥������»�׷׻��ѡ�temp)
    
    // �ѵס������׻�(�ѵ�st������ss)
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='������¤����'", $p2_ym);
    if (getUniResult($query, $p2_ss_expense) < 1) {
        $p2_ss_expense        = 0;     // ��������
    }
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�ѵ���¤����'", $p2_ym);
    if (getUniResult($query, $p2_st_expense) < 1) {
        $p2_st_expense        = 0;     // ��������
    }
    // ����ϫ̳�񺹳۷׻�
    $p2_ss_expense_temp  = $p2_ss_expense;
    $p2_st_expense_temp  = $p2_st_expense;
    $p2_s_expense        = number_format(($p2_s_expense / $tani), $keta);
    $p2_ss_expense       = number_format(($p2_ss_expense / $tani), $keta);
    $p2_st_expense       = number_format(($p2_st_expense / $tani), $keta);
}
    ///// �����߷�
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���ץ���¤����'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_sc_expense) < 1) {
    $rui_sc_expense        = 0;     // ��������
    $rui_sc_expense_sagaku = 0;
    $rui_sc_expense_temp   = 0;
} else {
    $rui_sc_expense_temp   = $rui_sc_expense;
    $rui_sc_expense_sagaku = $rui_sc_expense;
    $rui_sc_expense        = number_format(($rui_sc_expense / $tani), $keta);
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���¤����'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_s_expense) < 1) {
    $rui_s_expense         = 0;    // ��������
    $rui_s_expense_sagaku  = 0;
    $rui_sl_expense        = 0;
    $rui_sl_expense_temp   = 0;
    $rui_ss_expense        = 0;
    $rui_ss_expense_temp   = 0;
    $rui_st_expense        = 0;
    $rui_st_expense_temp   = 0;
} else {
    $rui_s_expense_sagaku  = $rui_s_expense;
    $rui_sl_expense        = $rui_s_expense - $rui_sc_expense_temp;           // ��˥��������¤�����׻�
    $rui_sl_expense_temp   = $rui_sl_expense;                                 // ��˥������»�׷׻��ѡ�temp)
    
    // �ѵס������׻�(�ѵ�st������ss)
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='������¤����'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_ss_expense) < 1) {
        $rui_ss_expense        = 0;     // ��������
    }
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='�ѵ���¤����'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_st_expense) < 1) {
        $rui_st_expense        = 0;     // ��������
    }
    // ����ϫ̳�񺹳۷׻�
    $rui_ss_expense_temp  = $rui_ss_expense;
    $rui_st_expense_temp  = $rui_st_expense;
    $rui_s_expense        = number_format(($rui_s_expense / $tani), $keta);
    $rui_ss_expense       = number_format(($rui_ss_expense / $tani), $keta);
    $rui_st_expense       = number_format(($rui_st_expense / $tani), $keta);
}

/********** ���������ų���ê���� **********/
    ///// �������
$p2_s_endinv  = 0;
$p1_s_endinv  = 0;
$s_endinv     = 0;
$p2_ss_endinv  = 0;
$p1_ss_endinv  = 0;
$ss_endinv     = 0;
$p2_st_endinv  = 0;
$p1_st_endinv  = 0;
$st_endinv     = 0;

$p2_sc_endinv = 0;
$p1_sc_endinv = 0;
$sc_endinv    = 0;
$p2_sl_endinv = 0;
$p1_sl_endinv = 0;
$sl_endinv    = 0;

/********** ��帶�� **********/
    ///// ����
    ///// �������
    $s_urigen            = $s_invent + $s_metarial_sagaku + $s_roumu_sagaku + $s_expense_sagaku + $s_endinv;
    $s_urigen_sagaku     = $s_urigen;
    $sc_urigen           = $sc_metarial_temp + $sc_roumu_temp + $sc_expense_temp;             // ���ץ�������帶���η׻�
    $sc_urigen_temp      = $sc_urigen;                                                        // ���ץ�����»�׷׻���(temp)
    $sl_urigen           = $s_urigen - $sc_urigen;                                            // ��˥��������帶���η׻�
    $sl_urigen_temp      = $sl_urigen;                                                        // ���ץ�����»�׷׻���(temp)
    $s_urigen            = $s_urigen + $sc_metarial_sagaku;                                   // ���ץ���������̣��sagaku�β� ��˥�����ޥ��ʥ����Ƥ��ޤ��١�
    if ($yyyymm == 200912) {
        $s_urigen = $s_urigen - 1409708;
    }
    if ($yyyymm == 200912) {
        $sc_urigen = $sc_urigen - 213810;
    }
    if ($yyyymm == 200912) {
        $sl_urigen = $sl_urigen - 1195898;
    }
    
    // �ѵס������׻�(�ѵ�st������ss)
    $ss_urigen      = $ss_invent + $ss_metarial_temp + $ss_roumu_temp + $ss_expense_temp + $ss_endinv;
    $ss_urigen_temp = $ss_urigen;
    $st_urigen      = $st_invent + $st_metarial_temp + $st_roumu_temp + $st_expense_temp + $st_endinv;
    $st_urigen_temp = $st_urigen;
        
    $s_urigen       = number_format(($s_urigen / $tani), $keta);
    $ss_urigen      = number_format(($ss_urigen / $tani), $keta);
    $st_urigen      = number_format(($st_urigen / $tani), $keta);
    ///// ����
    ///// �������
    $p1_s_urigen         = $p1_s_invent + $p1_s_metarial_sagaku + $p1_s_roumu_sagaku + $p1_s_expense_sagaku + $p1_s_endinv;
    $p1_s_urigen_sagaku  = $p1_s_urigen;
    $p1_sc_urigen        = $p1_sc_metarial_temp + $p1_sc_roumu_temp + $p1_sc_expense_temp;    // ���ץ�������帶���η׻�
    $p1_sc_urigen_temp   = $p1_sc_urigen;                                                     // ���ץ�����»�׷׻���(temp)
    $p1_sl_urigen        = $p1_s_urigen - $p1_sc_urigen;                                      // ��˥��������帶���η׻�
    $p1_sl_urigen_temp   = $p1_sl_urigen;                                                     // ���ץ�����»�׷׻���(temp)
    $p1_s_urigen         = $p1_s_urigen + $p1_sc_metarial_sagaku;                             // ���ץ���������̣��sagaku�β� ��˥�����ޥ��ʥ����Ƥ��ޤ��١�
    if ($p1_ym == 200912) {
        $p1_s_urigen = $p1_s_urigen - 1409708;
    }
    if ($p1_ym == 200912) {
        $p1_sc_urigen = $p1_sc_urigen - 213810;
    }
    if ($p1_ym == 200912) {
        $p1_sl_urigen = $p1_sl_urigen - 1195898;
    }
    
    // �ѵס������׻�(�ѵ�st������ss)
    $p1_ss_urigen      = $p1_ss_invent + $p1_ss_metarial_temp + $p1_ss_roumu_temp + $p1_ss_expense_temp + $p1_ss_endinv;
    $p1_ss_urigen_temp = $p1_ss_urigen;
    $p1_st_urigen      = $p1_st_invent + $p1_st_metarial_temp + $p1_st_roumu_temp + $p1_st_expense_temp + $p1_st_endinv;
    $p1_st_urigen_temp = $p1_st_urigen;
        
    $p1_s_urigen       = number_format(($p1_s_urigen / $tani), $keta);
    $p1_ss_urigen      = number_format(($p1_ss_urigen / $tani), $keta);
    $p1_st_urigen      = number_format(($p1_st_urigen / $tani), $keta);
    ///// ������
    ///// �������
    $p2_s_urigen         = $p2_s_invent + $p2_s_metarial_sagaku + $p2_s_roumu_sagaku + $p2_s_expense_sagaku + $p2_s_endinv;
    $p2_s_urigen_sagaku  = $p2_s_urigen;
    $p2_sc_urigen        = $p2_sc_metarial_temp + $p2_sc_roumu_temp + $p2_sc_expense_temp;    // ���ץ�������帶���η׻�
    $p2_sc_urigen_temp   = $p2_sc_urigen;                                                     // ���ץ�����»�׷׻���(temp)
    $p2_sl_urigen        = $p2_s_urigen - $p2_sc_urigen;                                      // ��˥��������帶���η׻�
    $p2_sl_urigen_temp   = $p2_sl_urigen;                                                     // ���ץ�����»�׷׻���(temp)
    $p2_s_urigen         = $p2_s_urigen + $p2_sc_metarial_sagaku;                             // ���ץ���������̣��sagaku�β� ��˥�����ޥ��ʥ����Ƥ��ޤ��١�
    if ($p2_ym == 200912) {
        $p2_s_urigen = $p2_s_urigen - 1409708;
    }
    if ($p2_ym == 200912) {
        $p2_sc_urigen = $p2_sc_urigen - 213810;
    }
    if ($p2_ym == 200912) {
        $p2_sl_urigen = $p2_sl_urigen - 1195898;
    }
    
    // �ѵס������׻�(�ѵ�st������ss)
    $p2_ss_urigen      = $p2_ss_invent + $p2_ss_metarial_temp + $p2_ss_roumu_temp + $p2_ss_expense_temp + $p2_ss_endinv;
    $p2_ss_urigen_temp = $p2_ss_urigen;
    $p2_st_urigen      = $p2_st_invent + $p2_st_metarial_temp + $p2_st_roumu_temp + $p2_st_expense_temp + $p2_st_endinv;
    $p2_st_urigen_temp = $p2_st_urigen;
        
    $p2_s_urigen       = number_format(($p2_s_urigen / $tani), $keta);
    $p2_ss_urigen      = number_format(($p2_ss_urigen / $tani), $keta);
    $p2_st_urigen      = number_format(($p2_st_urigen / $tani), $keta);
    ///// �����߷�
    ///// �������
    $rui_s_urigen        = $rui_s_invent + $rui_s_metarial_sagaku + $rui_s_roumu_sagaku + $rui_s_expense_sagaku + $s_endinv;
    $rui_s_urigen_sagaku = $rui_s_urigen;
    $rui_sc_urigen       = $rui_sc_metarial_temp + $rui_sc_roumu_temp + $rui_sc_expense_temp; // ���ץ�������帶���η׻�
    $rui_sc_urigen_temp  = $rui_sc_urigen;                                                    // ���ץ�����»�׷׻���(temp)
    $rui_sl_urigen       = $rui_s_urigen - $rui_sc_urigen;                                    // ��˥��������帶���η׻�
    $rui_sl_urigen_temp  = $rui_sl_urigen;                                                    // ���ץ�����»�׷׻���(temp)
    $rui_s_urigen        = $rui_s_urigen + $rui_sc_metarial_sagaku;                           // ���ץ���������̣��sagaku�β� ��˥�����ޥ��ʥ����Ƥ��ޤ��١�
    if ($yyyymm >= 200912 && $yyyymm <= 201003) {
        $rui_s_urigen = $rui_s_urigen - 1409708;
    }
    if ($yyyymm >= 200912 && $yyyymm <= 201003) {
        $rui_sc_urigen = $rui_sc_urigen - 213810;
    }
    if ($yyyymm >= 200912 && $yyyymm <= 201003) {
        $rui_sl_urigen = $rui_sl_urigen - 1195898;
    }
    
    // �ѵס������׻�(�ѵ�st������ss)
    $rui_ss_urigen      = $rui_ss_invent + $rui_ss_metarial_temp + $rui_ss_roumu_temp + $rui_ss_expense_temp + $ss_endinv;
    $rui_ss_urigen_temp = $rui_ss_urigen;
    $rui_st_urigen      = $rui_st_invent + $rui_st_metarial_temp + $rui_st_roumu_temp + $rui_st_expense_temp + $st_endinv;
    $rui_st_urigen_temp = $rui_st_urigen;
        
    $rui_s_urigen       = number_format(($rui_s_urigen / $tani), $keta);
    $rui_ss_urigen      = number_format(($rui_ss_urigen / $tani), $keta);
    $rui_st_urigen      = number_format(($rui_st_urigen / $tani), $keta);

/********** ��������� **********/
    ///// �������
$p2_s_gross_profit         = $p2_s_uri_sagaku - $p2_s_urigen_sagaku;
$p2_s_gross_profit_sagaku  = $p2_s_gross_profit;
$p2_sc_gross_profit        = $p2_sc_uri_temp - $p2_sc_urigen_temp;      // ���ץ���������׷׻�
$p2_sc_gross_profit_temp   = $p2_sc_gross_profit;                       // ���ץ����������»�׷׻���(temp)
$p2_sl_gross_profit        = $p2_sl_uri_temp - $p2_sl_urigen_temp;      // ��˥����������׷׻�
$p2_sl_gross_profit_temp   = $p2_sl_gross_profit;                       // ��˥�����������»�׷׻���(temp)
$p2_s_gross_profit         = $p2_s_gross_profit + $p2_sc_uri_sagaku - $p2_sc_metarial_sagaku;   // ���ץ�������̣��sagaku�β� ��˥�����ޥ��ʥ����Ƥ��ޤ��١�
if ($p2_ym == 200912) {
    $p2_s_gross_profit = $p2_s_gross_profit + 1409708;
}
if ($p2_ym == 200912) {
    $p2_sl_gross_profit = $p2_sl_gross_profit + 1195898;
}
if ($p2_ym == 200912) {
    $p2_sc_gross_profit = $p2_sc_gross_profit + 213810;
}

// �ѵס������׻�(�ѵ�st������ss)
$p2_ss_gross_profit      = $p2_ss_uri_temp - $p2_ss_urigen_temp;
$p2_ss_gross_profit_temp = $p2_ss_gross_profit;
$p2_st_gross_profit      = $p2_st_uri_temp - $p2_st_urigen_temp;
$p2_st_gross_profit_temp = $p2_st_gross_profit;

$p2_s_gross_profit            = number_format(($p2_s_gross_profit / $tani), $keta);
$p2_ss_gross_profit           = number_format(($p2_ss_gross_profit / $tani), $keta);
$p2_st_gross_profit           = number_format(($p2_st_gross_profit / $tani), $keta);

$p1_s_gross_profit         = $p1_s_uri_sagaku - $p1_s_urigen_sagaku;
$p1_s_gross_profit_sagaku  = $p1_s_gross_profit;
$p1_sc_gross_profit        = $p1_sc_uri_temp - $p1_sc_urigen_temp;      // ���ץ���������׷׻�
$p1_sc_gross_profit_temp   = $p1_sc_gross_profit;                       // ���ץ����������»�׷׻���(temp)
$p1_sl_gross_profit        = $p1_sl_uri_temp - $p1_sl_urigen_temp;      // ��˥����������׷׻�
$p1_sl_gross_profit_temp   = $p1_sl_gross_profit;                       // ��˥�����������»�׷׻���(temp)
$p1_s_gross_profit         = $p1_s_gross_profit + $p1_sc_uri_sagaku - $p1_sc_metarial_sagaku;   // ���ץ�������̣��sagaku�β� ��˥�����ޥ��ʥ����Ƥ��ޤ��١�
if ($p1_ym == 200912) {
    $p1_s_gross_profit = $p1_s_gross_profit + 1409708;
}
if ($p1_ym == 200912) {
    $p1_sl_gross_profit = $p1_sl_gross_profit + 1195898;
}
if ($p1_ym == 200912) {
    $p1_sc_gross_profit = $p1_sc_gross_profit + 213810;
}

// �ѵס������׻�(�ѵ�st������ss)
$p1_ss_gross_profit      = $p1_ss_uri_temp - $p1_ss_urigen_temp;
$p1_ss_gross_profit_temp = $p1_ss_gross_profit;
$p1_st_gross_profit      = $p1_st_uri_temp - $p1_st_urigen_temp;
$p1_st_gross_profit_temp = $p1_st_gross_profit;

$p1_s_gross_profit            = number_format(($p1_s_gross_profit / $tani), $keta);
$p1_ss_gross_profit           = number_format(($p1_ss_gross_profit / $tani), $keta);
$p1_st_gross_profit           = number_format(($p1_st_gross_profit / $tani), $keta);

$s_gross_profit            = $s_uri_sagaku - $s_urigen_sagaku;
$s_gross_profit_sagaku     = $s_gross_profit;
$sc_gross_profit           = $sc_uri_temp - $sc_urigen_temp;            // ���ץ���������׷׻�
$sc_gross_profit_temp      = $sc_gross_profit;                          // ���ץ����������»�׷׻���(temp)
$sl_gross_profit           = $sl_uri_temp - $sl_urigen_temp;            // ��˥����������׷׻�
$sl_gross_profit_temp      = $sl_gross_profit;                          // ��˥�����������»�׷׻���(temp)
$s_gross_profit            = $s_gross_profit + $sc_uri_sagaku - $sc_metarial_sagaku;   // ���ץ�������̣��sagaku�β� ��˥�����ޥ��ʥ����Ƥ��ޤ��١�
if ($yyyymm == 200912) {
    $s_gross_profit = $s_gross_profit + 1409708;
}
if ($yyyymm == 200912) {
    $sc_gross_profit = $sc_gross_profit + 213810;
}
if ($yyyymm == 200912) {
    $sl_gross_profit = $sl_gross_profit + 1195898;
}

// �ѵס������׻�(�ѵ�st������ss)
$ss_gross_profit      = $ss_uri_temp - $ss_urigen_temp;
$ss_gross_profit_temp = $ss_gross_profit;
$st_gross_profit      = $st_uri_temp - $st_urigen_temp;
$st_gross_profit_temp = $st_gross_profit;

$s_gross_profit            = number_format(($s_gross_profit / $tani), $keta);
$ss_gross_profit           = number_format(($ss_gross_profit / $tani), $keta);
$st_gross_profit           = number_format(($st_gross_profit / $tani), $keta);

$rui_s_gross_profit        = $rui_s_uri_sagaku - $rui_s_urigen_sagaku;
$rui_s_gross_profit_sagaku = $rui_s_gross_profit;
$rui_sc_gross_profit       = $rui_sc_uri_temp - $rui_sc_urigen_temp;    // ���ץ���������׷׻�
$rui_sc_gross_profit_temp  = $rui_sc_gross_profit;                      // ���ץ����������»�׷׻���(temp)
$rui_sl_gross_profit       = $rui_sl_uri_temp - $rui_sl_urigen_temp;    // ��˥����������׷׻�
$rui_sl_gross_profit_temp  = $rui_sl_gross_profit;                      // ��˥�����������»�׷׻���(temp)
$rui_s_gross_profit        = $rui_s_gross_profit + $rui_sc_uri_sagaku - $rui_sc_metarial_sagaku;   // ���ץ�������̣��sagaku�β� ��˥�����ޥ��ʥ����Ƥ��ޤ��١�
if ($yyyymm >= 200912 && $yyyymm <= 201003) {
    $rui_s_gross_profit = $rui_s_gross_profit + 1409708;
}
if ($yyyymm >= 200912 && $yyyymm <= 201003) {
    $rui_sc_gross_profit = $rui_sc_gross_profit + 213810;
}
if ($yyyymm >= 200912 && $yyyymm <= 201003) {
    $rui_sl_gross_profit = $rui_sl_gross_profit + 1195898;
}

// �ѵס������׻�(�ѵ�st������ss)
$rui_ss_gross_profit      = $rui_ss_uri_temp - $rui_ss_urigen_temp;
$rui_ss_gross_profit_temp = $rui_ss_gross_profit;
$rui_st_gross_profit      = $rui_st_uri_temp - $rui_st_urigen_temp;
$rui_st_gross_profit_temp = $rui_st_gross_profit;

$rui_s_gross_profit            = number_format(($rui_s_gross_profit / $tani), $keta);
$rui_ss_gross_profit           = number_format(($rui_ss_gross_profit / $tani), $keta);
$rui_st_gross_profit           = number_format(($rui_st_gross_profit / $tani), $keta);

/********** �δ���οͷ��� **********/
    ///// ����
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ��ͷ���'", $yyyymm);
if (getUniResult($query, $sc_han_jin) < 1) {
    $sc_han_jin        = 0;     // ��������
    $sc_han_jin_sagaku = 0;
    $sc_han_jin_temp   = 0;
} else {
    $sc_han_jin_temp   = $sc_han_jin;
    $sc_han_jin_sagaku = $sc_han_jin;
    $sc_han_jin        = number_format(($sc_han_jin / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��ͷ���'", $yyyymm);
if (getUniResult($query, $s_han_jin) < 1) {
    $s_han_jin         = 0;    // ��������
    $s_han_jin_sagaku  = 0;
    $sl_han_jin        = 0;
    $sl_han_jin_temp   = 0;
    $ss_han_jin        = 0;
    $ss_han_jin_temp   = 0;
    $st_han_jin        = 0;
    $st_han_jin_temp   = 0;
} else {
    $s_han_jin_sagaku  = $s_han_jin;
    $sl_han_jin        = $s_han_jin - $sc_han_jin_temp;               // ��˥�������ͷ����׻�
    $sl_han_jin_temp   = $sl_han_jin;                                 // ��˥������»�׷׻��ѡ�temp)
    
    // �ѵס������׻�(�ѵ�st������ss)
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�����ͷ���'", $yyyymm);
    if (getUniResult($query, $ss_han_jin) < 1) {
        $ss_han_jin        = 0;     // ��������
    }
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�ѵ׿ͷ���'", $yyyymm);
    if (getUniResult($query, $st_han_jin) < 1) {
        $st_han_jin        = 0;     // ��������
    }
    // ����ϫ̳�񺹳۷׻�
    $ss_han_jin_temp  = $ss_han_jin;
    $st_han_jin_temp  = $st_han_jin;
    $s_han_jin        = number_format(($s_han_jin / $tani), $keta);
    $ss_han_jin       = number_format(($ss_han_jin / $tani), $keta);
    $st_han_jin       = number_format(($st_han_jin / $tani), $keta);
}
    ///// ����
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ��ͷ���'", $p1_ym);
if (getUniResult($query, $p1_sc_han_jin) < 1) {
    $p1_sc_han_jin        = 0;     // ��������
    $p1_sc_han_jin_sagaku = 0;
    $p1_sc_han_jin_temp   = 0;
} else {
    $p1_sc_han_jin_temp   = $p1_sc_han_jin;
    $p1_sc_han_jin_sagaku = $p1_sc_han_jin;
    $p1_sc_han_jin        = number_format(($p1_sc_han_jin / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��ͷ���'", $p1_ym);
if (getUniResult($query, $p1_s_han_jin) < 1) {
    $p1_s_han_jin         = 0;    // ��������
    $p1_s_han_jin_sagaku  = 0;
    $p1_sl_han_jin        = 0;
    $p1_sl_han_jin_temp   = 0;
    $p1_ss_han_jin        = 0;
    $p1_ss_han_jin_temp   = 0;
    $p1_st_han_jin        = 0;
    $p1_st_han_jin_temp   = 0;
} else {
    $p1_s_han_jin_sagaku  = $p1_s_han_jin;
    $p1_sl_han_jin        = $p1_s_han_jin - $p1_sc_han_jin_temp;            // ��˥�������ͷ����׻�
    $p1_sl_han_jin_temp   = $p1_sl_han_jin;                                 // ��˥������»�׷׻��ѡ�temp)
    
    // �ѵס������׻�(�ѵ�st������ss)
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�����ͷ���'", $p1_ym);
    if (getUniResult($query, $p1_ss_han_jin) < 1) {
        $p1_ss_han_jin        = 0;     // ��������
    }
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�ѵ׿ͷ���'", $p1_ym);
    if (getUniResult($query, $p1_st_han_jin) < 1) {
        $p1_st_han_jin        = 0;     // ��������
    }
    // ����ϫ̳�񺹳۷׻�
    $p1_ss_han_jin_temp  = $p1_ss_han_jin;
    $p1_st_han_jin_temp  = $p1_st_han_jin;
    $p1_s_han_jin        = number_format(($p1_s_han_jin / $tani), $keta);
    $p1_ss_han_jin       = number_format(($p1_ss_han_jin / $tani), $keta);
    $p1_st_han_jin       = number_format(($p1_st_han_jin / $tani), $keta);
}
    ///// ������
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ��ͷ���'", $p2_ym);
if (getUniResult($query, $p2_sc_han_jin) < 1) {
    $p2_sc_han_jin        = 0;     // ��������
    $p2_sc_han_jin_sagaku = 0;
    $p2_sc_han_jin_temp   = 0;
} else {
    $p2_sc_han_jin_temp   = $p2_sc_han_jin;
    $p2_sc_han_jin_sagaku = $p2_sc_han_jin;
    $p2_sc_han_jin        = number_format(($p2_sc_han_jin / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��ͷ���'", $p2_ym);
if (getUniResult($query, $p2_s_han_jin) < 1) {
    $p2_s_han_jin         = 0;    // ��������
    $p2_s_han_jin_sagaku  = 0;
    $p2_sl_han_jin        = 0;
    $p2_sl_han_jin_temp   = 0;
    $p2_ss_han_jin        = 0;
    $p2_ss_han_jin_temp   = 0;
    $p2_st_han_jin        = 0;
    $p2_st_han_jin_temp   = 0;
} else {
    $p2_s_han_jin_sagaku  = $p2_s_han_jin;
    $p2_sl_han_jin        = $p2_s_han_jin - $p2_sc_han_jin_temp;            // ��˥�������ͷ����׻�
    $p2_sl_han_jin_temp   = $p2_sl_han_jin;                                 // ��˥������»�׷׻��ѡ�temp)
    
    // �ѵס������׻�(�ѵ�st������ss)
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�����ͷ���'", $p2_ym);
    if (getUniResult($query, $p2_ss_han_jin) < 1) {
        $p2_ss_han_jin        = 0;     // ��������
    }
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�ѵ׿ͷ���'", $p2_ym);
    if (getUniResult($query, $p2_st_han_jin) < 1) {
        $p2_st_han_jin        = 0;     // ��������
    }
    // ����ϫ̳�񺹳۷׻�
    $p2_ss_han_jin_temp  = $p2_ss_han_jin;
    $p2_st_han_jin_temp  = $p2_st_han_jin;
    $p2_s_han_jin        = number_format(($p2_s_han_jin / $tani), $keta);
    $p2_ss_han_jin       = number_format(($p2_ss_han_jin / $tani), $keta);
    $p2_st_han_jin       = number_format(($p2_st_han_jin / $tani), $keta);
}
    ///// �����߷�
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���ץ��ͷ���'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_sc_han_jin) < 1) {
    $rui_sc_han_jin        = 0;     // ��������
    $rui_sc_han_jin_sagaku = 0;
    $rui_sc_han_jin_temp   = 0;
} else {
    $rui_sc_han_jin_temp   = $rui_sc_han_jin;
    $rui_sc_han_jin_sagaku = $rui_sc_han_jin;
    $rui_sc_han_jin        = number_format(($rui_sc_han_jin / $tani), $keta);
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='��ͷ���'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_s_han_jin) < 1) {
    $rui_s_han_jin         = 0;    // ��������
    $rui_s_han_jin_sagaku  = 0;
    $rui_sl_han_jin        = 0;
    $rui_sl_han_jin_temp   = 0;
    $rui_ss_han_jin        = 0;
    $rui_ss_han_jin_temp   = 0;
    $rui_st_han_jin        = 0;
    $rui_st_han_jin_temp   = 0;
} else {
    $rui_s_han_jin_sagaku  = $rui_s_han_jin;
    $rui_sl_han_jin        = $rui_s_han_jin - $rui_sc_han_jin_temp;           // ��˥�������ͷ����׻�
    $rui_sl_han_jin_temp   = $rui_sl_han_jin;                                 // ��˥������»�׷׻��ѡ�temp)
    
    // �ѵס������׻�(�ѵ�st������ss)
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='�����ͷ���'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_ss_han_jin) < 1) {
        $rui_ss_han_jin        = 0;     // ��������
    }
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='�ѵ׿ͷ���'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_st_han_jin) < 1) {
        $rui_st_han_jin        = 0;     // ��������
    }
    // ����ϫ̳�񺹳۷׻�
    $rui_ss_han_jin_temp  = $rui_ss_han_jin;
    $rui_st_han_jin_temp  = $rui_st_han_jin;
    $rui_s_han_jin        = number_format(($rui_s_han_jin / $tani), $keta);
    $rui_ss_han_jin       = number_format(($rui_ss_han_jin / $tani), $keta);
    $rui_st_han_jin       = number_format(($rui_st_han_jin / $tani), $keta);
}

/********** �δ���η��� **********/
    ///// ����
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ��δ������'", $yyyymm);
if (getUniResult($query, $sc_han_kei) < 1) {
    $sc_han_kei        = 0;     // ��������
    $sc_han_kei_sagaku = 0;
    $sc_han_kei_temp   = 0;
} else {
    $sc_han_kei_temp   = $sc_han_kei;
    $sc_han_kei_sagaku = $sc_han_kei;
    $sc_han_kei        = number_format(($sc_han_kei / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��δ������'", $yyyymm);
if (getUniResult($query, $s_han_kei) < 1) {
    $s_han_kei        = 0;    // ��������
    $s_han_kei_sagaku = 0;
    $sl_han_kei       = 0;
    $sl_han_kei_temp  = 0;
    $ss_han_kei       = 0;
    $ss_han_kei_temp  = 0;
    $st_han_kei       = 0;
    $st_han_kei_temp  = 0;
} else {
    $s_han_kei_sagaku  = $s_han_kei;
    $sl_han_kei        = $s_han_kei - $sc_han_kei_temp;               // ��˥�������δ�������׻�
    $sl_han_kei_temp   = $sl_han_kei;                                 // ��˥������»�׷׻��ѡ�temp)
    
    // �ѵס������׻�(�ѵ�st������ss)
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�����δ������'", $yyyymm);
    if (getUniResult($query, $ss_han_kei) < 1) {
        $ss_han_kei        = 0;     // ��������
    }
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�ѵ��δ������'", $yyyymm);
    if (getUniResult($query, $st_han_kei) < 1) {
        $st_han_kei        = 0;     // ��������
    }
    // ����ϫ̳�񺹳۷׻�
    $ss_han_kei_temp  = $ss_han_kei;
    $st_han_kei_temp  = $st_han_kei;
    $s_han_kei        = number_format(($s_han_kei / $tani), $keta);
    $ss_han_kei       = number_format(($ss_han_kei / $tani), $keta);
    $st_han_kei       = number_format(($st_han_kei / $tani), $keta);
}
    ///// ����
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ��δ������'", $p1_ym);
if (getUniResult($query, $p1_sc_han_kei) < 1) {
    $p1_sc_han_kei        = 0;     // ��������
    $p1_sc_han_kei_sagaku = 0;
    $p1_sc_han_kei_temp   = 0;
} else {
    $p1_sc_han_kei_temp   = $p1_sc_han_kei;
    $p1_sc_han_kei_sagaku = $p1_sc_han_kei;
    $p1_sc_han_kei        = number_format(($p1_sc_han_kei / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��δ������'", $p1_ym);
if (getUniResult($query, $p1_s_han_kei) < 1) {
    $p1_s_han_kei         = 0;    // ��������
    $p1_s_han_kei_sagaku  = 0;
    $p1_sl_han_kei        = 0;
    $p1_sl_han_kei_temp   = 0;
    $p1_ss_han_kei        = 0;
    $p1_ss_han_kei_temp   = 0;
    $p1_st_han_kei        = 0;
    $p1_st_han_kei_temp   = 0;
} else {
    $p1_s_han_kei_sagaku  = $p1_s_han_kei;
    $p1_sl_han_kei        = $p1_s_han_kei - $p1_sc_han_kei_temp;            // ��˥�������δ�������׻�
    $p1_sl_han_kei_temp   = $p1_sl_han_kei;                                 // ��˥������»�׷׻��ѡ�temp)
    
    // �ѵס������׻�(�ѵ�st������ss)
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�����δ������'", $p1_ym);
    if (getUniResult($query, $p1_ss_han_kei) < 1) {
        $p1_ss_han_kei        = 0;     // ��������
    }
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�ѵ��δ������'", $p1_ym);
    if (getUniResult($query, $p1_st_han_kei) < 1) {
        $p1_st_han_kei        = 0;     // ��������
    }
    // ����ϫ̳�񺹳۷׻�
    $p1_ss_han_kei_temp  = $p1_ss_han_kei;
    $p1_st_han_kei_temp  = $p1_st_han_kei;
    $p1_s_han_kei        = number_format(($p1_s_han_kei / $tani), $keta);
    $p1_ss_han_kei       = number_format(($p1_ss_han_kei / $tani), $keta);
    $p1_st_han_kei       = number_format(($p1_st_han_kei / $tani), $keta);
}
    ///// ������
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ��δ������'", $p2_ym);
if (getUniResult($query, $p2_sc_han_kei) < 1) {
    $p2_sc_han_kei        = 0;     // ��������
    $p2_sc_han_kei_sagaku = 0;
    $p2_sc_han_kei_temp   = 0;
} else {
    $p2_sc_han_kei_temp   = $p2_sc_han_kei;
    $p2_sc_han_kei_sagaku = $p2_sc_han_kei;
    $p2_sc_han_kei        = number_format(($p2_sc_han_kei / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��δ������'", $p2_ym);
if (getUniResult($query, $p2_s_han_kei) < 1) {
    $p2_s_han_kei         = 0;    // ��������
    $p2_s_han_kei_sagaku  = 0;
    $p2_sl_han_kei        = 0;
    $p2_sl_han_kei_temp   = 0;
    $p2_ss_han_kei        = 0;
    $p2_ss_han_kei_temp   = 0;
    $p2_st_han_kei        = 0;
    $p2_st_han_kei_temp   = 0;
} else {
    $p2_s_han_kei_sagaku  = $p2_s_han_kei;
    $p2_sl_han_kei        = $p2_s_han_kei - $p2_sc_han_kei_temp;            // ��˥�������δ�������׻�
    $p2_sl_han_kei_temp   = $p2_sl_han_kei;                                 // ��˥������»�׷׻��ѡ�temp)
    
    // �ѵס������׻�(�ѵ�st������ss)
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�����δ������'", $p2_ym);
    if (getUniResult($query, $p2_ss_han_kei) < 1) {
        $p2_ss_han_kei        = 0;     // ��������
    }
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�ѵ��δ������'", $p2_ym);
    if (getUniResult($query, $p2_st_han_kei) < 1) {
        $p2_st_han_kei        = 0;     // ��������
    }
    // ����ϫ̳�񺹳۷׻�
    $p2_ss_han_kei_temp  = $p2_ss_han_kei;
    $p2_st_han_kei_temp  = $p2_st_han_kei;
    $p2_s_han_kei        = number_format(($p2_s_han_kei / $tani), $keta);
    $p2_ss_han_kei       = number_format(($p2_ss_han_kei / $tani), $keta);
    $p2_st_han_kei       = number_format(($p2_st_han_kei / $tani), $keta);
}
    ///// �����߷�
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���ץ��δ������'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_sc_han_kei) < 1) {
    $rui_sc_han_kei        = 0;     // ��������
    $rui_sc_han_kei_sagaku = 0;
    $rui_sc_han_kei_temp   = 0;
} else {
    $rui_sc_han_kei_temp   = $rui_sc_han_kei;
    $rui_sc_han_kei_sagaku = $rui_sc_han_kei;
    $rui_sc_han_kei        = number_format(($rui_sc_han_kei / $tani), $keta);
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='��δ������'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_s_han_kei) < 1) {
    $rui_s_han_kei         = 0;    // ��������
    $rui_s_han_kei_sagaku  = 0;
    $rui_sl_han_kei        = 0;
    $rui_sl_han_kei_temp   = 0;
    $rui_ss_han_kei        = 0;
    $rui_ss_han_kei_temp   = 0;
    $rui_st_han_kei        = 0;
    $rui_st_han_kei_temp   = 0;
} else {
    $rui_s_han_kei_sagaku  = $rui_s_han_kei;
    $rui_sl_han_kei        = $rui_s_han_kei - $rui_sc_han_kei_temp;           // ��˥�������δ�������׻�
    $rui_sl_han_kei_temp   = $rui_sl_han_kei;                                 // ��˥������»�׷׻��ѡ�temp)
    
    // �ѵס������׻�(�ѵ�st������ss)
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='�����δ������'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_ss_han_kei) < 1) {
        $rui_ss_han_kei        = 0;     // ��������
    }
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='�ѵ��δ������'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_st_han_kei) < 1) {
        $rui_st_han_kei        = 0;     // ��������
    }
    // ����ϫ̳�񺹳۷׻�
    $rui_ss_han_kei_temp  = $rui_ss_han_kei;
    $rui_st_han_kei_temp  = $rui_st_han_kei;
    $rui_s_han_kei        = number_format(($rui_s_han_kei / $tani), $keta);
    $rui_ss_han_kei       = number_format(($rui_ss_han_kei / $tani), $keta);
    $rui_st_han_kei       = number_format(($rui_st_han_kei / $tani), $keta);
}

/********** �δ���ι�� **********/
    ///// ����
    ///// �������
    $s_han_all            = $s_han_jin_sagaku + $s_han_kei_sagaku;
    $s_han_all_sagaku     = $s_han_all;
    $sc_han_all           = $sc_han_jin_temp + $sc_han_kei_temp;            // ���ץ��δ����פη׻�
    $sc_han_all_temp      = $sc_han_all;                                    // ���ץ�»�׷׻���(temp)
    $sl_han_all           = $sl_han_jin_temp + $sl_han_kei_temp;            // ��˥���δ����פη׻�
    $sl_han_all_temp      = $sl_han_all;                                    // ��˥��»�׷׻���(temp)
    
    // �ѵס������׻�(�ѵ�st������ss)
    $ss_han_all       = $ss_han_jin_temp + $ss_han_kei_temp;            // �����δ����פη׻�
    $ss_han_all_temp  = $ss_han_all;                                    // ����»�׷׻���(temp)
    $st_han_all       = $st_han_jin_temp + $st_han_kei_temp;            // �ѵ��δ����פη׻�
    $st_han_all_temp  = $st_han_all;                                    // �ѵ�»�׷׻���(temp)
    $s_han_all        = number_format(($s_han_all / $tani), $keta);
    $ss_han_all       = number_format(($ss_han_all / $tani), $keta);
    $st_han_all       = number_format(($st_han_all / $tani), $keta);
    
    ///// ����
    ///// �������
    $p1_s_han_all         = $p1_s_han_jin_sagaku + $p1_s_han_kei_sagaku;
    $p1_s_han_all_sagaku  = $p1_s_han_all;
    $p1_sc_han_all        = $p1_sc_han_jin_temp + $p1_sc_han_kei_temp;      // ���ץ��δ����פη׻�
    $p1_sc_han_all_temp   = $p1_sc_han_all;                                 // ���ץ�»�׷׻���(temp)
    $p1_sl_han_all        = $p1_sl_han_jin_temp + $p1_sl_han_kei_temp;      // ��˥���δ����פη׻�
    $p1_sl_han_all_temp   = $p1_sl_han_all;                                 // ��˥��»�׷׻���(temp)
    
    // �ѵס������׻�(�ѵ�st������ss)
    $p1_ss_han_all       = $p1_ss_han_jin_temp + $p1_ss_han_kei_temp;            // �����δ����פη׻�
    $p1_ss_han_all_temp  = $p1_ss_han_all;                                    // ����»�׷׻���(temp)
    $p1_st_han_all       = $p1_st_han_jin_temp + $p1_st_han_kei_temp;            // �ѵ��δ����פη׻�
    $p1_st_han_all_temp  = $p1_st_han_all;                                    // �ѵ�»�׷׻���(temp)
    $p1_s_han_all        = number_format(($p1_s_han_all / $tani), $keta);
    $p1_ss_han_all       = number_format(($p1_ss_han_all / $tani), $keta);
    $p1_st_han_all       = number_format(($p1_st_han_all / $tani), $keta);
    
    ///// ������
    ///// �������
    $p2_s_han_all         = $p2_s_han_jin_sagaku + $p2_s_han_kei_sagaku;
    $p2_s_han_all_sagaku  = $p2_s_han_all;
    $p2_sc_han_all        = $p2_sc_han_jin_temp + $p2_sc_han_kei_temp;      // ���ץ��δ����פη׻�
    $p2_sc_han_all_temp   = $p2_sc_han_all;                                 // ���ץ�»�׷׻���(temp)
    $p2_sl_han_all        = $p2_sl_han_jin_temp + $p2_sl_han_kei_temp;      // ��˥���δ����פη׻�
    $p2_sl_han_all_temp   = $p2_sl_han_all;                                 // ��˥��»�׷׻���(temp)
    
    // �ѵס������׻�(�ѵ�st������ss)
    $p2_ss_han_all       = $p2_ss_han_jin_temp + $p2_ss_han_kei_temp;            // �����δ����פη׻�
    $p2_ss_han_all_temp  = $p2_ss_han_all;                                    // ����»�׷׻���(temp)
    $p2_st_han_all       = $p2_st_han_jin_temp + $p2_st_han_kei_temp;            // �ѵ��δ����פη׻�
    $p2_st_han_all_temp  = $p2_st_han_all;                                    // �ѵ�»�׷׻���(temp)
    $p2_s_han_all        = number_format(($p2_s_han_all / $tani), $keta);
    $p2_ss_han_all       = number_format(($p2_ss_han_all / $tani), $keta);
    $p2_st_han_all       = number_format(($p2_st_han_all / $tani), $keta);
    
    ///// �����߷�
    ///// �������
    $rui_s_han_all        = $rui_s_han_jin_sagaku + $rui_s_han_kei_sagaku;
    $rui_s_han_all_sagaku = $rui_s_han_all;
    $rui_sc_han_all       = $rui_sc_han_jin_temp + $rui_sc_han_kei_temp;    // ���ץ��δ����פη׻�
    $rui_sc_han_all_temp  = $rui_sc_han_all;                                // ���ץ�»�׷׻���(temp)
    $rui_sl_han_all       = $rui_sl_han_jin_temp + $rui_sl_han_kei_temp;    // ��˥���δ����פη׻�
    $rui_sl_han_all_temp  = $rui_sl_han_all;                                // ��˥��»�׷׻���(temp)
    
    // �ѵס������׻�(�ѵ�st������ss)
    $rui_ss_han_all       = $rui_ss_han_jin_temp + $rui_ss_han_kei_temp;            // �����δ����פη׻�
    $rui_ss_han_all_temp  = $rui_ss_han_all;                                    // ����»�׷׻���(temp)
    $rui_st_han_all       = $rui_st_han_jin_temp + $rui_st_han_kei_temp;            // �ѵ��δ����פη׻�
    $rui_st_han_all_temp  = $rui_st_han_all;                                    // �ѵ�»�׷׻���(temp)
    $rui_s_han_all        = number_format(($rui_s_han_all / $tani), $keta);
    $rui_ss_han_all       = number_format(($rui_ss_han_all / $tani), $keta);
    $rui_st_han_all       = number_format(($rui_st_han_all / $tani), $keta);

/********** �Ķ����� **********/
    ///// �������
$p2_s_ope_profit         = $p2_s_gross_profit_sagaku - $p2_s_han_all_sagaku;
$p2_s_ope_profit_sagaku  = $p2_s_ope_profit;
$p2_sc_ope_profit        = $p2_sc_gross_profit_temp - $p2_sc_han_all_temp;      // ���ץ��Ķ����פη׻�
$p2_sc_ope_profit_temp   = $p2_sc_ope_profit;                                   // ���ץ�»�׷׻���(temp)
$p2_sl_ope_profit        = $p2_sl_gross_profit_temp - $p2_sl_han_all_temp;      // ��˥���Ķ����פη׻�
$p2_sl_ope_profit_temp   = $p2_sl_ope_profit;                                   // ��˥��»�׷׻���(temp)
$p2_s_ope_profit         = $p2_s_ope_profit + $p2_sc_uri_sagaku - $p2_sc_metarial_sagaku;   // ���ץ�������̣��sagaku�β� ��˥�����ޥ��ʥ����Ƥ��ޤ��١�
if ($p2_ym == 200912) {
    $p2_s_ope_profit = $p2_s_ope_profit + 1409708;
}
if ($p2_ym == 200912) {
    $p2_sl_ope_profit = $p2_sl_ope_profit + 1195898;
}
if ($p2_ym == 200912) {
    $p2_sc_ope_profit = $p2_sc_ope_profit + 213810;
}

// �ѵס������׻�(�ѵ�st������ss)
$p2_ss_ope_profit      = $p2_ss_gross_profit_temp - $p2_ss_han_all_temp;      // �����Ķ����פη׻�
$p2_ss_ope_profit_temp = $p2_ss_ope_profit;                                   // ����»�׷׻���(temp)
$p2_st_ope_profit      = $p2_st_gross_profit_temp - $p2_st_han_all_temp;      // �ѵױĶ����פη׻�
$p2_st_ope_profit_temp = $p2_st_ope_profit;                                   // �ѵ�»�׷׻���(temp)

$p2_s_ope_profit       = number_format(($p2_s_ope_profit / $tani), $keta);
$p2_ss_ope_profit      = number_format(($p2_ss_ope_profit / $tani), $keta);
$p2_st_ope_profit      = number_format(($p2_st_ope_profit / $tani), $keta);

$p1_s_ope_profit         = $p1_s_gross_profit_sagaku - $p1_s_han_all_sagaku;
$p1_s_ope_profit_sagaku  = $p1_s_ope_profit;
$p1_sc_ope_profit        = $p1_sc_gross_profit_temp - $p1_sc_han_all_temp;      // ���ץ��Ķ����פη׻�
$p1_sc_ope_profit_temp   = $p1_sc_ope_profit;                                   // ���ץ�»�׷׻���(temp)
$p1_sl_ope_profit        = $p1_sl_gross_profit_temp - $p1_sl_han_all_temp;      // ��˥���Ķ����פη׻�
$p1_sl_ope_profit_temp   = $p1_sl_ope_profit;                                   // ��˥��»�׷׻���(temp)
$p1_s_ope_profit         = $p1_s_ope_profit + $p1_sc_uri_sagaku - $p1_sc_metarial_sagaku;   // ���ץ�������̣��sagaku�β� ��˥�����ޥ��ʥ����Ƥ��ޤ��١�
if ($p1_ym == 200912) {
    $p1_s_ope_profit = $p1_s_ope_profit + 1409708;
}
if ($p1_ym == 200912) {
    $p1_sl_ope_profit = $p1_sl_ope_profit + 1195898;
}
if ($p1_ym == 200912) {
    $p1_sc_ope_profit = $p1_sc_ope_profit + 213810;
}

// �ѵס������׻�(�ѵ�st������ss)
$p1_ss_ope_profit      = $p1_ss_gross_profit_temp - $p1_ss_han_all_temp;      // �����Ķ����פη׻�
$p1_ss_ope_profit_temp = $p1_ss_ope_profit;                                   // ����»�׷׻���(temp)
$p1_st_ope_profit      = $p1_st_gross_profit_temp - $p1_st_han_all_temp;      // �ѵױĶ����פη׻�
$p1_st_ope_profit_temp = $p1_st_ope_profit;                                   // �ѵ�»�׷׻���(temp)

$p1_s_ope_profit       = number_format(($p1_s_ope_profit / $tani), $keta);
$p1_ss_ope_profit      = number_format(($p1_ss_ope_profit / $tani), $keta);
$p1_st_ope_profit      = number_format(($p1_st_ope_profit / $tani), $keta);

$s_ope_profit            = $s_gross_profit_sagaku - $s_han_all_sagaku;
$s_ope_profit_sagaku     = $s_ope_profit;
$sc_ope_profit           = $sc_gross_profit_temp - $sc_han_all_temp;            // ���ץ��Ķ����פη׻�
$sc_ope_profit_temp      = $sc_ope_profit;                                      // ���ץ�»�׷׻���(temp)
$sl_ope_profit           = $sl_gross_profit_temp - $sl_han_all_temp;            // ��˥���Ķ����פη׻�
$sl_ope_profit_temp      = $sl_ope_profit;                                      // ��˥��»�׷׻���(temp)
$s_ope_profit            = $s_ope_profit + $sc_uri_sagaku - $sc_metarial_sagaku;   // ���ץ�������̣��sagaku�β� ��˥�����ޥ��ʥ����Ƥ��ޤ��١�
if ($yyyymm == 200912) {
    $s_ope_profit = $s_ope_profit + 1409708;
}
if ($yyyymm == 200912) {
    $sc_ope_profit = $sc_ope_profit + 213810;
}
if ($yyyymm == 200912) {
    $sl_ope_profit = $sl_ope_profit + 1195898;
}

// �ѵס������׻�(�ѵ�st������ss)
$ss_ope_profit      = $ss_gross_profit_temp - $ss_han_all_temp;      // �����Ķ����פη׻�
$ss_ope_profit_temp = $ss_ope_profit;                                   // ����»�׷׻���(temp)
$st_ope_profit      = $st_gross_profit_temp - $st_han_all_temp;      // �ѵױĶ����פη׻�
$st_ope_profit_temp = $st_ope_profit;                                   // �ѵ�»�׷׻���(temp)

$s_ope_profit       = number_format(($s_ope_profit / $tani), $keta);
$ss_ope_profit      = number_format(($ss_ope_profit / $tani), $keta);
$st_ope_profit      = number_format(($st_ope_profit / $tani), $keta);

$rui_s_ope_profit        = $rui_s_gross_profit_sagaku - $rui_s_han_all_sagaku;
$rui_s_ope_profit_sagaku = $rui_s_ope_profit;
$rui_sc_ope_profit       = $rui_sc_gross_profit_temp - $rui_sc_han_all_temp;    // ���ץ��Ķ����פη׻�
$rui_sc_ope_profit_temp  = $rui_sc_ope_profit;                                  // ���ץ�»�׷׻���(temp)
$rui_sl_ope_profit       = $rui_sl_gross_profit_temp - $rui_sl_han_all_temp;    // ��˥���Ķ����פη׻�
$rui_sl_ope_profit_temp  = $rui_sl_ope_profit;                                  // ��˥��»�׷׻���(temp)
$rui_s_ope_profit        = $rui_s_ope_profit + $rui_sc_uri_sagaku - $rui_sc_metarial_sagaku;   // ���ץ�������̣��sagaku�β� ��˥�����ޥ��ʥ����Ƥ��ޤ��١�
if ($yyyymm >= 200912 && $yyyymm <= 201003) {
    $rui_s_ope_profit = $rui_s_ope_profit + 1409708;
}
if ($yyyymm >= 200912 && $yyyymm <= 201003) {
    $rui_sc_ope_profit = $rui_sc_ope_profit + 213810;
}
if ($yyyymm >= 200912 && $yyyymm <= 201003) {
    $rui_sl_ope_profit = $rui_sl_ope_profit + 1195898;
}

// �ѵס������׻�(�ѵ�st������ss)
$rui_ss_ope_profit      = $rui_ss_gross_profit_temp - $rui_ss_han_all_temp;      // �����Ķ����פη׻�
$rui_ss_ope_profit_temp = $rui_ss_ope_profit;                                   // ����»�׷׻���(temp)
$rui_st_ope_profit      = $rui_st_gross_profit_temp - $rui_st_han_all_temp;      // �ѵױĶ����פη׻�
$rui_st_ope_profit_temp = $rui_st_ope_profit;                                   // �ѵ�»�׷׻���(temp)

$rui_s_ope_profit       = number_format(($rui_s_ope_profit / $tani), $keta);
$rui_ss_ope_profit      = number_format(($rui_ss_ope_profit / $tani), $keta);
$rui_st_ope_profit      = number_format(($rui_st_ope_profit / $tani), $keta);

/********** �Ķȳ����פζ�̳�������� **********/
    ///// ����
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ���̳���������Ʒ׻�'", $yyyymm);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ���̳��������'", $yyyymm);
}
if (getUniResult($query, $sc_gyoumu) < 1) {
    $sc_gyoumu        = 0;     // ��������
    $sc_gyoumu_sagaku = 0;
    $sc_gyoumu_temp   = 0;
} else {
    if ($yyyymm == 200912) {
        $sc_gyoumu = $sc_gyoumu - 101;
    }
    if ($yyyymm == 201001) {
        $sc_gyoumu = $sc_gyoumu + 4855;
    }
    $sc_gyoumu_temp   = $sc_gyoumu;
    $sc_gyoumu_sagaku = $sc_gyoumu;
    $sc_gyoumu        = number_format(($sc_gyoumu / $tani), $keta);
}
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���̳���������Ʒ׻�'", $yyyymm);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���̳��������'", $yyyymm);
}
if (getUniResult($query, $s_gyoumu) < 1) {
    $s_gyoumu         = 0;    // ��������
    $s_gyoumu_sagaku  = 0;
    $sl_gyoumu        = 0;
    $sl_gyoumu_temp   = 0;
    $ss_gyoumu        = 0;
    $ss_gyoumu_temp   = 0;
    $st_gyoumu        = 0;
    $st_gyoumu_temp   = 0;
} else {
    if ($yyyymm == 200912) {
        $s_gyoumu = $s_gyoumu - 722;
    }
    if ($yyyymm == 201001) {
        $s_gyoumu = $s_gyoumu + 29125;
    }
    $s_gyoumu_sagaku  = $s_gyoumu;
    $sl_gyoumu        = $s_gyoumu - $sc_gyoumu_temp;                // ��˥��������̳����������׻�
    $sl_gyoumu_temp   = $sl_gyoumu;                                 // ��˥������»�׷׻��ѡ�temp)
    
    // �ѵס������׻�(�ѵ�st������ss)
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='������̳���������Ʒ׻�'", $yyyymm);
    if (getUniResult($query, $ss_gyoumu) < 1) {
        $ss_gyoumu        = 0;     // ��������
    }
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�ѵ׶�̳���������Ʒ׻�'", $yyyymm);
    if (getUniResult($query, $st_gyoumu) < 1) {
        $st_gyoumu        = 0;     // ��������
    }
    // ����ϫ̳�񺹳۷׻�
    $ss_gyoumu_temp  = $ss_gyoumu;
    $st_gyoumu_temp  = $st_gyoumu;
    $s_gyoumu        = number_format(($s_gyoumu / $tani), $keta);
    $ss_gyoumu       = number_format(($ss_gyoumu / $tani), $keta);
    $st_gyoumu       = number_format(($st_gyoumu / $tani), $keta);
}
    ///// ����
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ���̳���������Ʒ׻�'", $p1_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ���̳��������'", $p1_ym);
}
if (getUniResult($query, $p1_sc_gyoumu) < 1) {
    $p1_sc_gyoumu        = 0;     // ��������
    $p1_sc_gyoumu_sagaku = 0;
    $p1_sc_gyoumu_temp   = 0;
} else {
    if ($p1_ym == 200912) {
        $p1_sc_gyoumu = $p1_sc_gyoumu - 101;
    }
    if ($p1_ym == 201001) {
        $p1_sc_gyoumu = $p1_sc_gyoumu + 4855;
    }
    $p1_sc_gyoumu_temp   = $p1_sc_gyoumu;
    $p1_sc_gyoumu_sagaku = $p1_sc_gyoumu;
    $p1_sc_gyoumu        = number_format(($p1_sc_gyoumu / $tani), $keta);
}
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���̳���������Ʒ׻�'", $p1_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���̳��������'", $p1_ym);
}
if (getUniResult($query, $p1_s_gyoumu) < 1) {
    $p1_s_gyoumu         = 0;    // ��������
    $p1_s_gyoumu_sagaku  = 0;
    $p1_sl_gyoumu        = 0;
    $p1_sl_gyoumu_temp   = 0;
    $p1_ss_gyoumu        = 0;
    $p1_ss_gyoumu_temp   = 0;
    $p1_st_gyoumu        = 0;
    $p1_st_gyoumu_temp   = 0;
} else {
    if ($p1_ym == 200912) {
        $p1_s_gyoumu = $p1_s_gyoumu - 722;
    }
    if ($p1_ym == 201001) {
        $p1_s_gyoumu = $p1_s_gyoumu + 29125;
    }
    $p1_s_gyoumu_sagaku  = $p1_s_gyoumu;
    $p1_sl_gyoumu        = $p1_s_gyoumu - $p1_sc_gyoumu_temp;             // ��˥��������̳����������׻�
    $p1_sl_gyoumu_temp   = $p1_sl_gyoumu;                                 // ��˥������»�׷׻��ѡ�temp)
    
    // �ѵס������׻�(�ѵ�st������ss)
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='������̳���������Ʒ׻�'", $p1_ym);
    if (getUniResult($query, $p1_ss_gyoumu) < 1) {
        $p1_ss_gyoumu        = 0;     // ��������
    }
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�ѵ׶�̳���������Ʒ׻�'", $p1_ym);
    if (getUniResult($query, $p1_st_gyoumu) < 1) {
        $p1_st_gyoumu        = 0;     // ��������
    }
    // ����ϫ̳�񺹳۷׻�
    $p1_ss_gyoumu_temp  = $p1_ss_gyoumu;
    $p1_st_gyoumu_temp  = $p1_st_gyoumu;
    $p1_s_gyoumu        = number_format(($p1_s_gyoumu / $tani), $keta);
    $p1_ss_gyoumu       = number_format(($p1_ss_gyoumu / $tani), $keta);
    $p1_st_gyoumu       = number_format(($p1_st_gyoumu / $tani), $keta);
}
    ///// ������
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ���̳���������Ʒ׻�'", $p2_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ���̳��������'", $p2_ym);
}
if (getUniResult($query, $p2_sc_gyoumu) < 1) {
    $p2_sc_gyoumu        = 0;     // ��������
    $p2_sc_gyoumu_sagaku = 0;
    $p2_sc_gyoumu_temp   = 0;
} else {
    if ($p2_ym == 200912) {
        $p2_sc_gyoumu = $p2_sc_gyoumu - 101;
    }
    if ($p2_ym == 201001) {
        $p2_sc_gyoumu = $p2_sc_gyoumu + 4855;
    }
    $p2_sc_gyoumu_temp   = $p2_sc_gyoumu;
    $p2_sc_gyoumu_sagaku = $p2_sc_gyoumu;
    $p2_sc_gyoumu        = number_format(($p2_sc_gyoumu / $tani), $keta);
}
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���̳���������Ʒ׻�'", $p2_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���̳��������'", $p2_ym);
}
if (getUniResult($query, $p2_s_gyoumu) < 1) {
    $p2_s_gyoumu         = 0;    // ��������
    $p2_s_gyoumu_sagaku  = 0;
    $p2_sl_gyoumu        = 0;
    $p2_sl_gyoumu_temp   = 0;
    $p2_ss_gyoumu        = 0;
    $p2_ss_gyoumu_temp   = 0;
    $p2_st_gyoumu        = 0;
    $p2_st_gyoumu_temp   = 0;
} else {
    if ($p2_ym == 200912) {
        $p2_s_gyoumu = $p2_s_gyoumu - 722;
    }
    if ($p2_ym == 201001) {
        $p2_s_gyoumu = $p2_s_gyoumu + 29125;
    }
    $p2_s_gyoumu_sagaku  = $p2_s_gyoumu;
    $p2_sl_gyoumu        = $p2_s_gyoumu - $p2_sc_gyoumu_temp;             // ��˥��������̳����������׻�
    $p2_sl_gyoumu_temp   = $p2_sl_gyoumu;                                 // ��˥������»�׷׻��ѡ�temp)
    
    // �ѵס������׻�(�ѵ�st������ss)
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='������̳���������Ʒ׻�'", $p2_ym);
    if (getUniResult($query, $p2_ss_gyoumu) < 1) {
        $p2_ss_gyoumu        = 0;     // ��������
    }
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�ѵ׶�̳���������Ʒ׻�'", $p2_ym);
    if (getUniResult($query, $p2_st_gyoumu) < 1) {
        $p2_st_gyoumu        = 0;     // ��������
    }
    // ����ϫ̳�񺹳۷׻�
    $p2_ss_gyoumu_temp  = $p2_ss_gyoumu;
    $p2_st_gyoumu_temp  = $p2_st_gyoumu;
    $p2_s_gyoumu        = number_format(($p2_s_gyoumu / $tani), $keta);
    $p2_ss_gyoumu       = number_format(($p2_ss_gyoumu / $tani), $keta);
    $p2_st_gyoumu       = number_format(($p2_st_gyoumu / $tani), $keta);
}
    ///// �����߷�
if ($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���ץ���̳���������Ʒ׻�'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_sc_gyoumu) < 1) {
        $rui_sc_gyoumu_temp   = 0;
        $rui_sc_gyoumu_sagaku = 0;
        $rui_sc_gyoumu        = 0;                     // ��������
    } else {
        $rui_sc_gyoumu_temp   = $rui_sc_gyoumu;
        $rui_sc_gyoumu_sagaku = $rui_sc_gyoumu;
        $rui_sc_gyoumu = number_format(($rui_sc_gyoumu / $tani), $keta);
    }
} elseif ($yyyymm >= 201001 && $yyyymm <= 201003) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=200904 and pl_bs_ym<=200912 and note='���ץ���̳��������'");
    if (getUniResult($query, $rui_sc_gyoumu_a) < 1) {
        $rui_sc_gyoumu_a = 0;                          // ��������
    }
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=201001 and pl_bs_ym<=%d and note='���ץ���̳���������Ʒ׻�'", $yyyymm);
    if (getUniResult($query, $rui_sc_gyoumu_b) < 1) {
        $rui_sc_gyoumu_b = 0;                          // ��������
    }
    $rui_sc_gyoumu = $rui_sc_gyoumu_a + $rui_sc_gyoumu_b;
    
    if ($yyyymm >= 200912 && $yyyymm <= 201003) {
        $rui_sc_gyoumu = $rui_sc_gyoumu - 101;
    }
    if ($yyyymm >= 201001 && $yyyymm <= 201003) {
        $rui_sc_gyoumu = $rui_sc_gyoumu + 4855;
    }
    $rui_sc_gyoumu_temp   = $rui_sc_gyoumu;
    $rui_sc_gyoumu_sagaku = $rui_sc_gyoumu;
    $rui_sc_gyoumu        = number_format(($rui_sc_gyoumu / $tani), $keta);
} else {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���ץ���̳��������'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_sc_gyoumu) < 1) {
        $rui_sc_gyoumu        = 0;     // ��������
        $rui_sc_gyoumu_sagaku = 0;
        $rui_sc_gyoumu_temp   = 0;
    } else {
        if ($yyyymm >= 200912 && $yyyymm <= 201003) {
            $rui_sc_gyoumu = $rui_sc_gyoumu - 101;
        }
        if ($yyyymm >= 201001 && $yyyymm <= 201003) {
            $rui_sc_gyoumu = $rui_sc_gyoumu + 4855;
        }
        $rui_sc_gyoumu_temp   = $rui_sc_gyoumu;
        $rui_sc_gyoumu_sagaku = $rui_sc_gyoumu;
        $rui_sc_gyoumu        = number_format(($rui_sc_gyoumu / $tani), $keta);
    }
}
if ($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���̳���������Ʒ׻�'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_s_gyoumu) < 1) {
        $rui_s_gyoumu         = 0;                          // ��������
        $rui_s_gyoumu_sagaku  = 0;
        $rui_sl_gyoumu        = 0;
        $rui_sl_gyoumu_temp   = 0;
        $rui_ss_gyoumu        = 0;
        $rui_ss_gyoumu_temp   = 0;
        $rui_st_gyoumu        = 0;
        $rui_st_gyoumu_temp   = 0;
    } else {
        $rui_s_gyoumu_sagaku  = $rui_s_gyoumu;
        $rui_sl_gyoumu        = $rui_s_gyoumu - $rui_sc_gyoumu_temp;            // ��˥��������̳����������׻�
        $rui_sl_gyoumu_temp   = $rui_sl_gyoumu;                                 // ��˥������»�׷׻��ѡ�temp)
        
        // �ѵס������׻�(�ѵ�st������ss)
        $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='������̳���������Ʒ׻�'", $str_ym, $yyyymm);
        if (getUniResult($query, $rui_ss_gyoumu) < 1) {
            $rui_ss_gyoumu        = 0;     // ��������
        }
        $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='�ѵ׶�̳���������Ʒ׻�'", $str_ym, $yyyymm);
        if (getUniResult($query, $rui_st_gyoumu) < 1) {
            $rui_st_gyoumu        = 0;     // ��������
        }
        // ����ϫ̳�񺹳۷׻�
        $rui_ss_gyoumu_temp  = $rui_ss_gyoumu;
        $rui_st_gyoumu_temp  = $rui_st_gyoumu;
        $rui_s_gyoumu        = number_format(($rui_s_gyoumu / $tani), $keta);
        $rui_ss_gyoumu       = number_format(($rui_ss_gyoumu / $tani), $keta);
        $rui_st_gyoumu       = number_format(($rui_st_gyoumu / $tani), $keta);
    }
} elseif ($yyyymm >= 201001 && $yyyymm <= 201003) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=200904 and pl_bs_ym<=200912 and note='���̳��������'");
    if (getUniResult($query, $rui_s_gyoumu_a) < 1) {
        $rui_s_gyoumu_a = 0;                          // ��������
    }
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=201001 and pl_bs_ym<=%d and note='���̳���������Ʒ׻�'", $yyyymm);
    if (getUniResult($query, $rui_s_gyoumu_b) < 1) {
        $rui_s_gyoumu_b = 0;                          // ��������
    }
    $rui_s_gyoumu = $rui_s_gyoumu_a + $rui_s_gyoumu_b;
    
    if ($yyyymm >= 200912 && $yyyymm <= 201003) {
        $rui_s_gyoumu = $rui_s_gyoumu - 722;
    }
    if ($yyyymm >= 201001 && $yyyymm <= 201003) {
        $rui_s_gyoumu = $rui_s_gyoumu + 29125;
    }
    $rui_s_gyoumu_sagaku  = $rui_s_gyoumu;
    $rui_sl_gyoumu        = $rui_s_gyoumu - $rui_sc_gyoumu_temp;            // ��˥��������̳����������׻�
    $rui_sl_gyoumu_temp   = $rui_sl_gyoumu;                                 // ��˥������»�׷׻��ѡ�temp)
    $rui_s_gyoumu         = number_format(($rui_s_gyoumu / $tani), $keta);
    $rui_sl_gyoumu        = number_format(($rui_sl_gyoumu / $tani), $keta);
} else {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���̳��������'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_s_gyoumu) < 1) {
        $rui_s_gyoumu         = 0;    // ��������
        $rui_s_gyoumu_sagaku  = 0;
        $rui_sl_gyoumu        = 0;
        $rui_sl_gyoumu_temp   = 0;
    } else {
        if ($yyyymm >= 200912 && $yyyymm <= 201003) {
            $rui_s_gyoumu = $rui_s_gyoumu - 722;
        }
        if ($yyyymm >= 201001 && $yyyymm <= 201003) {
            $rui_s_gyoumu = $rui_s_gyoumu + 29125;
        }
        $rui_s_gyoumu_sagaku  = $rui_s_gyoumu;
        $rui_sl_gyoumu        = $rui_s_gyoumu - $rui_sc_gyoumu_temp;            // ��˥��������̳����������׻�
        $rui_sl_gyoumu_temp   = $rui_sl_gyoumu;                                 // ��˥������»�׷׻��ѡ�temp)
        $rui_s_gyoumu         = number_format(($rui_s_gyoumu / $tani), $keta);
        $rui_sl_gyoumu        = number_format(($rui_sl_gyoumu / $tani), $keta);
    }
}

/********** �Ķȳ����פλ������ **********/
    ///// ����
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ���������Ʒ׻�'", $yyyymm);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ��������'", $yyyymm);
}
if (getUniResult($query, $sc_swari) < 1) {
    $sc_swari        = 0;     // ��������
    $sc_swari_sagaku = 0;
    $sc_swari_temp   = 0;
} else {
    $sc_swari_temp   = $sc_swari;
    $sc_swari_sagaku = $sc_swari;
    $sc_swari        = number_format(($sc_swari / $tani), $keta);
}
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���������Ʒ׻�'", $yyyymm);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��������'", $yyyymm);
}
if (getUniResult($query, $s_swari) < 1) {
    $s_swari         = 0;    // ��������
    $s_swari_sagaku  = 0;
    $sl_swari        = 0;
    $sl_swari_temp   = 0;
    $ss_swari        = 0;
    $ss_swari_temp   = 0;
    $st_swari        = 0;
    $st_swari_temp   = 0;
} else {
    $s_swari_sagaku  = $s_swari;
    $sl_swari        = $s_swari - $sc_swari_temp;                 // ��˥���������������׻�
    $sl_swari_temp   = $sl_swari;                                 // ��˥������»�׷׻��ѡ�temp)
    
    // �ѵס������׻�(�ѵ�st������ss)
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='������������Ʒ׻�'", $yyyymm);
    if (getUniResult($query, $ss_swari) < 1) {
        $ss_swari        = 0;     // ��������
    }
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�ѵ׻�������Ʒ׻�'", $yyyymm);
    if (getUniResult($query, $st_swari) < 1) {
        $st_swari        = 0;     // ��������
    }
    // ����ϫ̳�񺹳۷׻�
    $ss_swari_temp  = $ss_swari;
    $st_swari_temp  = $st_swari;
    $s_swari        = number_format(($s_swari / $tani), $keta);
    $ss_swari       = number_format(($ss_swari / $tani), $keta);
    $st_swari       = number_format(($st_swari / $tani), $keta);
}
    ///// ����
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ���������Ʒ׻�'", $p1_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ��������'", $p1_ym);
}
if (getUniResult($query, $p1_sc_swari) < 1) {
    $p1_sc_swari        = 0;     // ��������
    $p1_sc_swari_sagaku = 0;
    $p1_sc_swari_temp   = 0;
} else {
    $p1_sc_swari_temp   = $p1_sc_swari;
    $p1_sc_swari_sagaku = $p1_sc_swari;
    $p1_sc_swari        = number_format(($p1_sc_swari / $tani), $keta);
}
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���������Ʒ׻�'", $p1_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��������'", $p1_ym);
}
if (getUniResult($query, $p1_s_swari) < 1) {
    $p1_s_swari         = 0;    // ��������
    $p1_s_swari_sagaku  = 0;
    $p1_sl_swari        = 0;
    $p1_sl_swari_temp   = 0;
    $p1_ss_swari        = 0;
    $p1_ss_swari_temp   = 0;
    $p1_st_swari        = 0;
    $p1_st_swari_temp   = 0;
} else {
    $p1_s_swari_sagaku  = $p1_s_swari;
    $p1_sl_swari        = $p1_s_swari - $p1_sc_swari_temp;              // ��˥���������������׻�
    $p1_sl_swari_temp   = $p1_sl_swari;                                 // ��˥������»�׷׻��ѡ�temp)
    
    // �ѵס������׻�(�ѵ�st������ss)
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='������������Ʒ׻�'", $p1_ym);
    if (getUniResult($query, $p1_ss_swari) < 1) {
        $p1_ss_swari        = 0;     // ��������
    }
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�ѵ׻�������Ʒ׻�'", $p1_ym);
    if (getUniResult($query, $p1_st_swari) < 1) {
        $p1_st_swari        = 0;     // ��������
    }
    // ����ϫ̳�񺹳۷׻�
    $p1_ss_swari_temp  = $p1_ss_swari;
    $p1_st_swari_temp  = $p1_st_swari;
    $p1_s_swari        = number_format(($p1_s_swari / $tani), $keta);
    $p1_ss_swari       = number_format(($p1_ss_swari / $tani), $keta);
    $p1_st_swari       = number_format(($p1_st_swari / $tani), $keta);
}
    ///// ������
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ���������Ʒ׻�'", $p2_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ��������'", $p2_ym);
}
if (getUniResult($query, $p2_sc_swari) < 1) {
    $p2_sc_swari        = 0;     // ��������
    $p2_sc_swari_sagaku = 0;
    $p2_sc_swari_temp   = 0;
} else {
    $p2_sc_swari_temp   = $p2_sc_swari;
    $p2_sc_swari_sagaku = $p2_sc_swari;
    $p2_sc_swari        = number_format(($p2_sc_swari / $tani), $keta);
}
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���������Ʒ׻�'", $p2_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��������'", $p2_ym);
}
if (getUniResult($query, $p2_s_swari) < 1) {
    $p2_s_swari         = 0;    // ��������
    $p2_s_swari_sagaku  = 0;
    $p2_sl_swari        = 0;
    $p2_sl_swari_temp   = 0;
    $p2_ss_swari        = 0;
    $p2_ss_swari_temp   = 0;
    $p2_st_swari        = 0;
    $p2_st_swari_temp   = 0;
} else {
    $p2_s_swari_sagaku  = $p2_s_swari;
    $p2_sl_swari        = $p2_s_swari - $p2_sc_swari_temp;              // ��˥���������������׻�
    $p2_sl_swari_temp   = $p2_sl_swari;                                 // ��˥������»�׷׻��ѡ�temp)
    
    // �ѵס������׻�(�ѵ�st������ss)
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='������������Ʒ׻�'", $p2_ym);
    if (getUniResult($query, $p2_ss_swari) < 1) {
        $p2_ss_swari        = 0;     // ��������
    }
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�ѵ׻�������Ʒ׻�'", $p2_ym);
    if (getUniResult($query, $p2_st_swari) < 1) {
        $p2_st_swari        = 0;     // ��������
    }
    // ����ϫ̳�񺹳۷׻�
    $p2_ss_swari_temp  = $p2_ss_swari;
    $p2_st_swari_temp  = $p2_st_swari;
    $p2_s_swari        = number_format(($p2_s_swari / $tani), $keta);
    $p2_ss_swari       = number_format(($p2_ss_swari / $tani), $keta);
    $p2_st_swari       = number_format(($p2_st_swari / $tani), $keta);
}
    ///// �����߷�
if ($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���ץ���������Ʒ׻�'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_sc_swari) < 1) {
        $rui_sc_swari        = 0;                           // ��������
        $rui_sc_swari_temp   = 0;
        $rui_sc_swari_sagaku = 0;
    } else {
        $rui_sc_swari_temp   = $rui_sc_swari;
        $rui_sc_swari_sagaku = $rui_sc_swari;
        $rui_sc_swari        = number_format(($rui_sc_swari / $tani), $keta);
    }
} elseif ($yyyymm >= 201001 && $yyyymm <= 201003) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=200904 and pl_bs_ym<=200912 and note='���ץ��������'");
    if (getUniResult($query, $rui_sc_swari_a) < 1) {
        $rui_sc_swari_a = 0;                          // ��������
    }
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=201001 and pl_bs_ym<=%d and note='���ץ���������Ʒ׻�'", $yyyymm);
    if (getUniResult($query, $rui_sc_swari_b) < 1) {
        $rui_sc_swari_b = 0;                          // ��������
    }
    $rui_sc_swari        = $rui_sc_swari_a + $rui_sc_swari_b;
    $rui_sc_swari_temp   = $rui_sc_swari;
    $rui_sc_swari_sagaku = $rui_sc_swari;
    $rui_sc_swari        = number_format(($rui_sc_swari / $tani), $keta);
} else {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���ץ��������'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_sc_swari) < 1) {
        $rui_sc_swari        = 0;     // ��������
        $rui_sc_swari_sagaku = 0;
        $rui_sc_swari_temp   = 0;
    } else {
        $rui_sc_swari_temp   = $rui_sc_swari;
        $rui_sc_swari_sagaku = $rui_sc_swari;
        $rui_sc_swari        = number_format(($rui_sc_swari / $tani), $keta);
    }
}
if ($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���������Ʒ׻�'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_s_swari) < 1) {
        $rui_s_swari         = 0;                           // ��������
        $rui_s_swari_sagaku  = 0;
        $rui_sl_swari        = 0;
        $rui_sl_swari_temp   = 0;
        $rui_ss_swari        = 0;
        $rui_ss_swari_temp   = 0;
        $rui_st_swari        = 0;
        $rui_st_swari_temp   = 0;
    } else {
        $rui_s_swari_sagaku  = $rui_s_swari;
        $rui_sl_swari        = $rui_s_swari - $rui_sc_swari_temp;             // ��˥���������������׻�
        $rui_sl_swari_temp   = $rui_sl_swari;                                 // ��˥������»�׷׻��ѡ�temp)
        
        // �ѵס������׻�(�ѵ�st������ss)
        $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='������������Ʒ׻�'", $str_ym, $yyyymm);
        if (getUniResult($query, $rui_ss_swari) < 1) {
            $rui_ss_swari        = 0;     // ��������
        }
        $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='�ѵ׻�������Ʒ׻�'", $str_ym, $yyyymm);
        if (getUniResult($query, $rui_st_swari) < 1) {
            $rui_st_swari        = 0;     // ��������
        }
        // ����ϫ̳�񺹳۷׻�
        $rui_ss_swari_temp  = $rui_ss_swari;
        $rui_st_swari_temp  = $rui_st_swari;
        $rui_s_swari        = number_format(($rui_s_swari / $tani), $keta);
        $rui_ss_swari       = number_format(($rui_ss_swari / $tani), $keta);
        $rui_st_swari       = number_format(($rui_st_swari / $tani), $keta);
    }
} elseif ($yyyymm >= 201001 && $yyyymm <= 201003) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=200904 and pl_bs_ym<=200912 and note='��������'");
    if (getUniResult($query, $rui_s_swari_a) < 1) {
        $rui_s_swari_a = 0;                          // ��������
    }
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=201001 and pl_bs_ym<=%d and note='���������Ʒ׻�'", $yyyymm);
    if (getUniResult($query, $rui_s_swari_b) < 1) {
        $rui_s_swari_b = 0;                          // ��������
    }
    $rui_s_swari         = $rui_s_swari_a + $rui_s_swari_b;
    $rui_s_swari_sagaku  = $rui_s_swari;
    $rui_sl_swari        = $rui_s_swari - $rui_sc_swari_temp;             // ��˥���������������׻�
    $rui_sl_swari_temp   = $rui_sl_swari;                                 // ��˥������»�׷׻��ѡ�temp)
    $rui_s_swari         = number_format(($rui_s_swari / $tani), $keta);
    $rui_sl_swari        = number_format(($rui_sl_swari / $tani), $keta);
} else {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='��������'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_s_swari) < 1) {
        $rui_s_swari         = 0;    // ��������
        $rui_s_swari_sagaku  = 0;
        $rui_sl_swari        = 0;
        $rui_sl_swari_temp   = 0;
    } else {
        $rui_s_swari_sagaku  = $rui_s_swari;
        $rui_sl_swari        = $rui_s_swari - $rui_sc_swari_temp;             // ��˥���������������׻�
        $rui_sl_swari_temp   = $rui_sl_swari;                                 // ��˥������»�׷׻��ѡ�temp)
        $rui_s_swari         = number_format(($rui_s_swari / $tani), $keta);
        $rui_sl_swari        = number_format(($rui_sl_swari / $tani), $keta);
    }
}
/********** �Ķȳ����פΤ���¾ **********/
    ///// ����
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ��Ķȳ����פ���¾�Ʒ׻�'", $yyyymm);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ��Ķȳ����פ���¾'", $yyyymm);
}
if (getUniResult($query, $sc_pother) < 1) {
    $sc_pother        = 0;     // ��������
    $sc_pother_sagaku = 0;
    $sc_pother_temp   = 0;
} else {
    if ($yyyymm == 200912) {
        $sc_pother = $sc_pother + 101;
    }
    if ($yyyymm == 201001) {
        $sc_pother = $sc_pother - 4855;
    }
    $sc_pother_temp   = $sc_pother;
    $sc_pother_sagaku = $sc_pother;
    $sc_pother        = number_format(($sc_pother / $tani), $keta);
}
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��Ķȳ����פ���¾�Ʒ׻�'", $yyyymm);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��Ķȳ����פ���¾'", $yyyymm);
}
if (getUniResult($query, $s_pother) < 1) {
    $s_pother         = 0;    // ��������
    $s_pother_sagaku  = 0;
    $sl_pother        = 0;
    $sl_pother_temp   = 0;
    $ss_pother        = 0;
    $ss_pother_temp   = 0;
    $st_pother        = 0;
    $st_pother_temp   = 0;
} else {
    if ($yyyymm == 200912) {
        $s_pother = $s_pother + 722;
    }
    if ($yyyymm == 201001) {
        $s_pother = $s_pother - 29125;
    }
    $s_pother_sagaku  = $s_pother;
    $sl_pother        = $s_pother - $sc_pother_temp;                // ��˥�������Ķȳ����פ���¾��׻�
    $sl_pother_temp   = $sl_pother;                                 // ��˥������»�׷׻��ѡ�temp)
    
    // �ѵס������׻�(�ѵ�st������ss)
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�����Ķȳ����פ���¾�Ʒ׻�'", $yyyymm);
    if (getUniResult($query, $ss_pother) < 1) {
        $ss_pother        = 0;     // ��������
    }
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�ѵױĶȳ����פ���¾�Ʒ׻�'", $yyyymm);
    if (getUniResult($query, $st_pother) < 1) {
        $st_pother        = 0;     // ��������
    }
    // ����ϫ̳�񺹳۷׻�
    $ss_pother_temp  = $ss_pother;
    $st_pother_temp  = $st_pother;
    $s_pother        = number_format(($s_pother / $tani), $keta);
    $ss_pother       = number_format(($ss_pother / $tani), $keta);
    $st_pother       = number_format(($st_pother / $tani), $keta);
}
    ///// ����
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ��Ķȳ����פ���¾�Ʒ׻�'", $p1_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ��Ķȳ����פ���¾'", $p1_ym);
}
if (getUniResult($query, $p1_sc_pother) < 1) {
    $p1_sc_pother        = 0;     // ��������
    $p1_sc_pother_sagaku = 0;
    $p1_sc_pother_temp   = 0;
} else {
    if ($p1_ym == 200912) {
        $p1_sc_pother = $p1_sc_pother + 101;
    }
    if ($p1_ym == 201001) {
        $p1_sc_pother = $p1_sc_pother - 4855;
    }
    $p1_sc_pother_temp   = $p1_sc_pother;
    $p1_sc_pother_sagaku = $p1_sc_pother;
    $p1_sc_pother        = number_format(($p1_sc_pother / $tani), $keta);
}
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��Ķȳ����פ���¾�Ʒ׻�'", $p1_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��Ķȳ����פ���¾'", $p1_ym);
}
if (getUniResult($query, $p1_s_pother) < 1) {
    $p1_s_pother         = 0;    // ��������
    $p1_s_pother_sagaku  = 0;
    $p1_sl_pother        = 0;
    $p1_sl_pother_temp   = 0;
    $p1_ss_pother        = 0;
    $p1_ss_pother_temp   = 0;
    $p1_st_pother        = 0;
    $p1_st_pother_temp   = 0;
} else {
    if ($p1_ym == 200912) {
        $p1_s_pother = $p1_s_pother + 722;
    }
    if ($p1_ym == 201001) {
        $p1_s_pother = $p1_s_pother - 29125;
    }
    $p1_s_pother_sagaku  = $p1_s_pother;
    $p1_sl_pother        = $p1_s_pother - $p1_sc_pother_temp;             // ��˥�������Ķȳ����פ���¾��׻�
    $p1_sl_pother_temp   = $p1_sl_pother;                                 // ��˥������»�׷׻��ѡ�temp)
    
    // �ѵס������׻�(�ѵ�st������ss)
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�����Ķȳ����פ���¾�Ʒ׻�'", $p1_ym);
    if (getUniResult($query, $p1_ss_pother) < 1) {
        $p1_ss_pother        = 0;     // ��������
    }
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�ѵױĶȳ����פ���¾�Ʒ׻�'", $p1_ym);
    if (getUniResult($query, $p1_st_pother) < 1) {
        $p1_st_pother        = 0;     // ��������
    }
    // ����ϫ̳�񺹳۷׻�
    $p1_ss_pother_temp  = $p1_ss_pother;
    $p1_st_pother_temp  = $p1_st_pother;
    $p1_s_pother        = number_format(($p1_s_pother / $tani), $keta);
    $p1_ss_pother       = number_format(($p1_ss_pother / $tani), $keta);
    $p1_st_pother       = number_format(($p1_st_pother / $tani), $keta);
}
    ///// ������
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ��Ķȳ����פ���¾�Ʒ׻�'", $p2_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ��Ķȳ����פ���¾'", $p2_ym);
}
if (getUniResult($query, $p2_sc_pother) < 1) {
    $p2_sc_pother        = 0;     // ��������
    $p2_sc_pother_sagaku = 0;
    $p2_sc_pother_temp   = 0;
} else {
    if ($p2_ym == 200912) {
        $p2_sc_pother = $p2_sc_pother + 101;
    }
    if ($p2_ym == 201001) {
        $p2_sc_pother = $p2_sc_pother - 4855;
    }
    $p2_sc_pother_temp   = $p2_sc_pother;
    $p2_sc_pother_sagaku = $p2_sc_pother;
    $p2_sc_pother        = number_format(($p2_sc_pother / $tani), $keta);
}
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��Ķȳ����פ���¾�Ʒ׻�'", $p2_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��Ķȳ����פ���¾'", $p2_ym);
}
if (getUniResult($query, $p2_s_pother) < 1) {
    $p2_s_pother         = 0;    // ��������
    $p2_s_pother_sagaku  = 0;
    $p2_sl_pother        = 0;
    $p2_sl_pother_temp   = 0;
    $p2_ss_pother        = 0;
    $p2_ss_pother_temp   = 0;
    $p2_st_pother        = 0;
    $p2_st_pother_temp   = 0;
} else {
    if ($p2_ym == 200912) {
        $p2_s_pother = $p2_s_pother + 722;
    }
    if ($p2_ym == 201001) {
        $p2_s_pother = $p2_s_pother - 29125;
    }
    $p2_s_pother_sagaku  = $p2_s_pother;
    $p2_sl_pother        = $p2_s_pother - $p2_sc_pother_temp;             // ��˥�������Ķȳ����פ���¾��׻�
    $p2_sl_pother_temp   = $p2_sl_pother;                                 // ��˥������»�׷׻��ѡ�temp)
    
    // �ѵס������׻�(�ѵ�st������ss)
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�����Ķȳ����פ���¾�Ʒ׻�'", $p2_ym);
    if (getUniResult($query, $p2_ss_pother) < 1) {
        $p2_ss_pother        = 0;     // ��������
    }
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�ѵױĶȳ����פ���¾�Ʒ׻�'", $p2_ym);
    if (getUniResult($query, $p2_st_pother) < 1) {
        $p2_st_pother        = 0;     // ��������
    }
    // ����ϫ̳�񺹳۷׻�
    $p2_ss_pother_temp  = $p2_ss_pother;
    $p2_st_pother_temp  = $p2_st_pother;
    $p2_s_pother        = number_format(($p2_s_pother / $tani), $keta);
    $p2_ss_pother       = number_format(($p2_ss_pother / $tani), $keta);
    $p2_st_pother       = number_format(($p2_st_pother / $tani), $keta);
}
    ///// �����߷�
if ($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���ץ��Ķȳ����פ���¾�Ʒ׻�'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_sc_pother) < 1) {
        $rui_sc_pother        = 0;                          // ��������
        $rui_sc_pother_temp   = 0;
        $rui_sc_pother_sagaku = 0;
    } else {
        $rui_sc_pother_temp   = $rui_sc_pother;
        $rui_sc_pother_sagaku = $rui_sc_pother;
        $rui_sc_pother        = number_format(($rui_sc_pother / $tani), $keta);
    }
} elseif ($yyyymm >= 201001 && $yyyymm <= 201003) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=200904 and pl_bs_ym<=200912 and note='���ץ��Ķȳ����פ���¾'");
    if (getUniResult($query, $rui_sc_pother_a) < 1) {
        $rui_sc_pother_a = 0;                          // ��������
    }
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=201001 and pl_bs_ym<=%d and note='���ץ��Ķȳ����פ���¾�Ʒ׻�'", $yyyymm);
    if (getUniResult($query, $rui_sc_pother_b) < 1) {
        $rui_sc_pother_b = 0;                          // ��������
    }
    $rui_sc_pother = $rui_sc_pother_a + $rui_sc_pother_b;
    
    if ($yyyymm >= 200912 && $yyyymm <= 201003) {
        $rui_sc_pother = $rui_sc_pother + 101;
    }
    if ($yyyymm >= 201001 && $yyyymm <= 201003) {
        $rui_sc_pother = $rui_sc_pother - 4855;
    }
    $rui_sc_pother_temp   = $rui_sc_pother;
    $rui_sc_pother_sagaku = $rui_sc_pother;
    $rui_sc_pother        = number_format(($rui_sc_pother / $tani), $keta);
} else {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���ץ��Ķȳ����פ���¾'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_sc_pother) < 1) {
        $rui_sc_pother        = 0;     // ��������
        $rui_sc_pother_sagaku = 0;
        $rui_sc_pother_temp   = 0;
    } else {
        if ($yyyymm >= 200912 && $yyyymm <= 201003) {
            $rui_sc_pother = $rui_sc_pother + 101;
        }
        if ($yyyymm >= 201001 && $yyyymm <= 201003) {
            $rui_sc_pother = $rui_sc_pother - 4855;
        }
        $rui_sc_pother_temp   = $rui_sc_pother;
        $rui_sc_pother_sagaku = $rui_sc_pother;
        $rui_sc_pother        = number_format(($rui_sc_pother / $tani), $keta);
    }
}
if ($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='��Ķȳ����פ���¾�Ʒ׻�'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_s_pother) < 1) {
        $rui_s_pother         = 0;                          // ��������
        $rui_s_pother_sagaku  = 0;
        $rui_sl_pother        = 0;
        $rui_sl_pother_temp   = 0;
        $rui_ss_pother        = 0;
        $rui_ss_pother_temp   = 0;
        $rui_st_pother        = 0;
        $rui_st_pother_temp   = 0;
    } else {
        $rui_s_pother_sagaku  = $rui_s_pother;
        $rui_sl_pother        = $rui_s_pother - $rui_sc_pother_temp;           // ��˥�������Ķȳ����פ���¾��׻�
        $rui_sl_pother_temp   = $rui_sl_pother;                                // ��˥������»�׷׻��ѡ�temp)
        
        // �ѵס������׻�(�ѵ�st������ss)
        $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='�����Ķȳ����פ���¾�Ʒ׻�'", $str_ym, $yyyymm);
        if (getUniResult($query, $rui_ss_pother) < 1) {
            $rui_ss_pother        = 0;     // ��������
        }
        $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='�ѵױĶȳ����פ���¾�Ʒ׻�'", $str_ym, $yyyymm);
        if (getUniResult($query, $rui_st_pother) < 1) {
            $rui_st_pother        = 0;     // ��������
        }
        // ����ϫ̳�񺹳۷׻�
        $rui_ss_pother_temp  = $rui_ss_pother;
        $rui_st_pother_temp  = $rui_st_pother;
        $rui_s_pother        = number_format(($rui_s_pother / $tani), $keta);
        $rui_ss_pother       = number_format(($rui_ss_pother / $tani), $keta);
        $rui_st_pother       = number_format(($rui_st_pother / $tani), $keta);
    }
} elseif ($yyyymm >= 201001 && $yyyymm <= 201003) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=200904 and pl_bs_ym<=200912 and note='��Ķȳ����פ���¾'");
    if (getUniResult($query, $rui_s_pother_a) < 1) {
        $rui_s_pother_a = 0;                          // ��������
    }
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=201001 and pl_bs_ym<=%d and note='��Ķȳ����פ���¾�Ʒ׻�'", $yyyymm);
    if (getUniResult($query, $rui_s_pother_b) < 1) {
        $rui_s_pother_b = 0;                          // ��������
    }
    $rui_s_pother = $rui_s_pother_a + $rui_s_pother_b;
    
    if ($yyyymm >= 200912 && $yyyymm <= 201003) {
        $rui_s_pother = $rui_s_pother + 722;
    }
    if ($yyyymm >= 201001 && $yyyymm <= 201003) {
        $rui_s_pother = $rui_s_pother - 29125;
    }
    $rui_s_pother_sagaku  = $rui_s_pother;
    $rui_sl_pother        = $rui_s_pother - $rui_sc_pother_temp;           // ��˥�������Ķȳ����פ���¾��׻�
    $rui_sl_pother_temp   = $rui_sl_pother;                                // ��˥������»�׷׻��ѡ�temp)
    $rui_s_pother         = number_format(($rui_s_pother / $tani), $keta);
    $rui_sl_pother        = number_format(($rui_sl_pother / $tani), $keta);
} else {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='��Ķȳ����פ���¾'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_s_pother) < 1) {
        $rui_s_pother         = 0;    // ��������
        $rui_s_pother_sagaku  = 0;
        $rui_sl_pother        = 0;
        $rui_sl_pother_temp   = 0;
    } else {
        if ($yyyymm >= 200912 && $yyyymm <= 201003) {
            $rui_s_pother = $rui_s_pother + 722;
        }
        if ($yyyymm >= 201001 && $yyyymm <= 201003) {
            $rui_s_pother = $rui_s_pother - 29125;
        }
        $rui_s_pother_sagaku  = $rui_s_pother;
        $rui_sl_pother        = $rui_s_pother - $rui_sc_pother_temp;           // ��˥�������Ķȳ����פ���¾��׻�
        $rui_sl_pother_temp   = $rui_sl_pother;                                // ��˥������»�׷׻��ѡ�temp)
        $rui_s_pother         = number_format(($rui_s_pother / $tani), $keta);
        $rui_sl_pother        = number_format(($rui_sl_pother / $tani), $keta);
    }
}
/********** �Ķȳ����פι�� **********/
    ///// �������
$p2_s_nonope_profit_sum         = $p2_s_gyoumu_sagaku + $p2_s_swari_sagaku + $p2_s_pother_sagaku;
$p2_s_nonope_profit_sum_sagaku  = $p2_s_nonope_profit_sum;
// �ѵס������׻�(�ѵ�st������ss)
$p2_ss_nonope_profit_sum        = $p2_ss_gyoumu_temp + $p2_ss_swari_temp + $p2_ss_pother_temp;      // �����Ķȳ����פι�׷׻�
$p2_ss_nonope_profit_sum_temp   = $p2_ss_nonope_profit_sum;                                         // ����»�׷׻���(temp)
$p2_st_nonope_profit_sum        = $p2_st_gyoumu_temp + $p2_st_swari_temp + $p2_st_pother_temp;      // �ѵױĶȳ����פι�׷׻�
$p2_st_nonope_profit_sum_temp   = $p2_st_nonope_profit_sum;                                         // �ѵ�»�׷׻���(temp)
$p2_s_nonope_profit_sum         = number_format(($p2_s_nonope_profit_sum / $tani), $keta);
$p2_ss_nonope_profit_sum        = number_format(($p2_ss_nonope_profit_sum / $tani), $keta);
$p2_st_nonope_profit_sum        = number_format(($p2_st_nonope_profit_sum / $tani), $keta);

$p1_s_nonope_profit_sum         = $p1_s_gyoumu_sagaku + $p1_s_swari_sagaku + $p1_s_pother_sagaku;
$p1_s_nonope_profit_sum_sagaku  = $p1_s_nonope_profit_sum;
// �ѵס������׻�(�ѵ�st������ss)
$p1_ss_nonope_profit_sum        = $p1_ss_gyoumu_temp + $p1_ss_swari_temp + $p1_ss_pother_temp;      // �����Ķȳ����פι�׷׻�
$p1_ss_nonope_profit_sum_temp   = $p1_ss_nonope_profit_sum;                                         // ����»�׷׻���(temp)
$p1_st_nonope_profit_sum        = $p1_st_gyoumu_temp + $p1_st_swari_temp + $p1_st_pother_temp;      // �ѵױĶȳ����פι�׷׻�
$p1_st_nonope_profit_sum_temp   = $p1_st_nonope_profit_sum;                                         // �ѵ�»�׷׻���(temp)
$p1_s_nonope_profit_sum         = number_format(($p1_s_nonope_profit_sum / $tani), $keta);
$p1_ss_nonope_profit_sum        = number_format(($p1_ss_nonope_profit_sum / $tani), $keta);
$p1_st_nonope_profit_sum        = number_format(($p1_st_nonope_profit_sum / $tani), $keta);

$s_nonope_profit_sum            = $s_gyoumu_sagaku + $s_swari_sagaku + $s_pother_sagaku;
$s_nonope_profit_sum_sagaku     = $s_nonope_profit_sum;
// �ѵס������׻�(�ѵ�st������ss)
$ss_nonope_profit_sum           = $ss_gyoumu_temp + $ss_swari_temp + $ss_pother_temp;               // �����Ķȳ����פι�׷׻�
$ss_nonope_profit_sum_temp      = $ss_nonope_profit_sum;                                            // ����»�׷׻���(temp)
$st_nonope_profit_sum           = $st_gyoumu_temp + $st_swari_temp + $st_pother_temp;               // �ѵױĶȳ����פι�׷׻�
$st_nonope_profit_sum_temp      = $st_nonope_profit_sum;                                            // �ѵ�»�׷׻���(temp)
$s_nonope_profit_sum            = number_format(($s_nonope_profit_sum / $tani), $keta);
$ss_nonope_profit_sum           = number_format(($ss_nonope_profit_sum / $tani), $keta);
$st_nonope_profit_sum           = number_format(($st_nonope_profit_sum / $tani), $keta);

$rui_s_nonope_profit_sum        = $rui_s_gyoumu_sagaku + $rui_s_swari_sagaku + $rui_s_pother_sagaku;
$rui_s_nonope_profit_sum_sagaku = $rui_s_nonope_profit_sum;
// �ѵס������׻�(�ѵ�st������ss)
$rui_ss_nonope_profit_sum       = $rui_ss_gyoumu_temp + $rui_ss_swari_temp + $rui_ss_pother_temp;   // �����Ķȳ����פι�׷׻�
$rui_ss_nonope_profit_sum_temp  = $rui_ss_nonope_profit_sum;                                        // ����»�׷׻���(temp)
$rui_st_nonope_profit_sum       = $rui_st_gyoumu_temp + $rui_st_swari_temp + $rui_st_pother_temp;   // �ѵױĶȳ����פι�׷׻�
$rui_st_nonope_profit_sum_temp  = $rui_st_nonope_profit_sum;                                        // �ѵ�»�׷׻���(temp)
$rui_s_nonope_profit_sum        = number_format(($rui_s_nonope_profit_sum / $tani), $keta);
$rui_ss_nonope_profit_sum       = number_format(($rui_ss_nonope_profit_sum / $tani), $keta);
$rui_st_nonope_profit_sum       = number_format(($rui_st_nonope_profit_sum / $tani), $keta);

/********** �Ķȳ����Ѥλ�ʧ��© **********/
    ///// ����
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ���ʧ��©�Ʒ׻�'", $yyyymm);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ���ʧ��©'", $yyyymm);
}
if (getUniResult($query, $sc_srisoku) < 1) {
    $sc_srisoku        = 0;     // ��������
    $sc_srisoku_sagaku = 0;
    $sc_srisoku_temp   = 0;
} else {
    $sc_srisoku_temp   = $sc_srisoku;
    $sc_srisoku_sagaku = $sc_srisoku;
    $sc_srisoku        = number_format(($sc_srisoku / $tani), $keta);
}
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ʧ��©�Ʒ׻�'", $yyyymm);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ʧ��©'", $yyyymm);
}
if (getUniResult($query, $s_srisoku) < 1) {
    $s_srisoku         = 0;    // ��������
    $s_srisoku_sagaku  = 0;
    $sl_srisoku        = 0;
    $sl_srisoku_temp   = 0;
    $ss_srisoku        = 0;
    $ss_srisoku_temp   = 0;
    $st_srisoku        = 0;
    $st_srisoku_temp   = 0;
} else {
    $s_srisoku_sagaku  = $s_srisoku;
    $sl_srisoku        = $s_srisoku - $sc_srisoku_temp;               // ��˥��������ʧ��©��׻�
    $sl_srisoku_temp   = $sl_srisoku;                                 // ��˥������»�׷׻��ѡ�temp)
    
    // �ѵס������׻�(�ѵ�st������ss)
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='������ʧ��©�Ʒ׻�'", $yyyymm);
    if (getUniResult($query, $ss_srisoku) < 1) {
        $ss_srisoku        = 0;     // ��������
    }
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�ѵ׻�ʧ��©�Ʒ׻�'", $yyyymm);
    if (getUniResult($query, $st_srisoku) < 1) {
        $st_srisoku        = 0;     // ��������
    }
    // ����ϫ̳�񺹳۷׻�
    $ss_srisoku_temp  = $ss_srisoku;
    $st_srisoku_temp  = $st_srisoku;
    $s_srisoku        = number_format(($s_srisoku / $tani), $keta);
    $ss_srisoku       = number_format(($ss_srisoku / $tani), $keta);
    $st_srisoku       = number_format(($st_srisoku / $tani), $keta);
}
    ///// ����
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ���ʧ��©�Ʒ׻�'", $p1_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ���ʧ��©'", $p1_ym);
}
if (getUniResult($query, $p1_sc_srisoku) < 1) {
    $p1_sc_srisoku        = 0;     // ��������
    $p1_sc_srisoku_sagaku = 0;
    $p1_sc_srisoku_temp   = 0;
} else {
    $p1_sc_srisoku_temp   = $p1_sc_srisoku;
    $p1_sc_srisoku_sagaku = $p1_sc_srisoku;
    $p1_sc_srisoku        = number_format(($p1_sc_srisoku / $tani), $keta);
}
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ʧ��©�Ʒ׻�'", $p1_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ʧ��©'", $p1_ym);
}
if (getUniResult($query, $p1_s_srisoku) < 1) {
    $p1_s_srisoku         = 0;    // ��������
    $p1_s_srisoku_sagaku  = 0;
    $p1_sl_srisoku        = 0;
    $p1_sl_srisoku_temp   = 0;
    $p1_ss_srisoku        = 0;
    $p1_ss_srisoku_temp   = 0;
    $p1_st_srisoku        = 0;
    $p1_st_srisoku_temp   = 0;
} else {
    $p1_s_srisoku_sagaku  = $p1_s_srisoku;
    $p1_sl_srisoku        = $p1_s_srisoku - $p1_sc_srisoku_temp;            // ��˥��������ʧ��©��׻�
    $p1_sl_srisoku_temp   = $p1_sl_srisoku;                                 // ��˥������»�׷׻��ѡ�temp)
    
    // �ѵס������׻�(�ѵ�st������ss)
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='������ʧ��©�Ʒ׻�'", $p1_ym);
    if (getUniResult($query, $p1_ss_srisoku) < 1) {
        $p1_ss_srisoku        = 0;     // ��������
    }
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�ѵ׻�ʧ��©�Ʒ׻�'", $p1_ym);
    if (getUniResult($query, $p1_st_srisoku) < 1) {
        $p1_st_srisoku        = 0;     // ��������
    }
    // ����ϫ̳�񺹳۷׻�
    $p1_ss_srisoku_temp  = $p1_ss_srisoku;
    $p1_st_srisoku_temp  = $p1_st_srisoku;
    $p1_s_srisoku        = number_format(($p1_s_srisoku / $tani), $keta);
    $p1_ss_srisoku       = number_format(($p1_ss_srisoku / $tani), $keta);
    $p1_st_srisoku       = number_format(($p1_st_srisoku / $tani), $keta);
}
    ///// ������
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ���ʧ��©�Ʒ׻�'", $p2_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ���ʧ��©'", $p2_ym);
}
if (getUniResult($query, $p2_sc_srisoku) < 1) {
    $p2_sc_srisoku        = 0;     // ��������
    $p2_sc_srisoku_sagaku = 0;
    $p2_sc_srisoku_temp   = 0;
} else {
    $p2_sc_srisoku_temp   = $p2_sc_srisoku;
    $p2_sc_srisoku_sagaku = $p2_sc_srisoku;
    $p2_sc_srisoku        = number_format(($p2_sc_srisoku / $tani), $keta);
}
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ʧ��©�Ʒ׻�'", $p2_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ʧ��©'", $p2_ym);
}
if (getUniResult($query, $p2_s_srisoku) < 1) {
    $p2_s_srisoku         = 0;    // ��������
    $p2_s_srisoku_sagaku  = 0;
    $p2_sl_srisoku        = 0;
    $p2_sl_srisoku_temp   = 0;
    $p2_ss_srisoku        = 0;
    $p2_ss_srisoku_temp   = 0;
    $p2_st_srisoku        = 0;
    $p2_st_srisoku_temp   = 0;
} else {
    $p2_s_srisoku_sagaku  = $p2_s_srisoku;
    $p2_sl_srisoku        = $p2_s_srisoku - $p2_sc_srisoku_temp;            // ��˥��������ʧ��©��׻�
    $p2_sl_srisoku_temp   = $p2_sl_srisoku;                                 // ��˥������»�׷׻��ѡ�temp)
    
    // �ѵס������׻�(�ѵ�st������ss)
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='������ʧ��©�Ʒ׻�'", $p2_ym);
    if (getUniResult($query, $p2_ss_srisoku) < 1) {
        $p2_ss_srisoku        = 0;     // ��������
    }
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�ѵ׻�ʧ��©�Ʒ׻�'", $p2_ym);
    if (getUniResult($query, $p2_st_srisoku) < 1) {
        $p2_st_srisoku        = 0;     // ��������
    }
    // ����ϫ̳�񺹳۷׻�
    $p2_ss_srisoku_temp  = $p2_ss_srisoku;
    $p2_st_srisoku_temp  = $p2_st_srisoku;
    $p2_s_srisoku        = number_format(($p2_s_srisoku / $tani), $keta);
    $p2_ss_srisoku       = number_format(($p2_ss_srisoku / $tani), $keta);
    $p2_st_srisoku       = number_format(($p2_st_srisoku / $tani), $keta);
}
    ///// �����߷�
if ($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���ץ���ʧ��©�Ʒ׻�'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_sc_srisoku) < 1) {
        $rui_sc_srisoku        = 0;                           // ��������
        $rui_sc_srisoku_temp   = 0;
        $rui_sc_srisoku_sagaku = 0;
    } else {
        $rui_sc_srisoku_temp   = $rui_sc_srisoku;
        $rui_sc_srisoku_sagaku = $rui_sc_srisoku;
        $rui_sc_srisoku        = number_format(($rui_sc_srisoku / $tani), $keta);
    }
} elseif ($yyyymm >= 201001 && $yyyymm <= 201003) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=200904 and pl_bs_ym<=200912 and note='���ץ���ʧ��©'");
    if (getUniResult($query, $rui_sc_srisoku_a) < 1) {
        $rui_sc_srisoku_a = 0;                          // ��������
    }
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=201001 and pl_bs_ym<=%d and note='���ץ���ʧ��©�Ʒ׻�'", $yyyymm);
    if (getUniResult($query, $rui_sc_srisoku_b) < 1) {
        $rui_sc_srisoku_b = 0;                          // ��������
    }
    $rui_sc_srisoku        = $rui_sc_srisoku_a + $rui_sc_srisoku_b;
    $rui_sc_srisoku_temp   = $rui_sc_srisoku;
    $rui_sc_srisoku_sagaku = $rui_sc_srisoku;
    $rui_sc_srisoku        = number_format(($rui_sc_srisoku / $tani), $keta);
} else {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���ץ���ʧ��©'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_sc_srisoku) < 1) {
        $rui_sc_srisoku        = 0;     // ��������
        $rui_sc_srisoku_sagaku = 0;
        $rui_sc_srisoku_temp   = 0;
    } else {
        $rui_sc_srisoku_temp   = $rui_sc_srisoku;
        $rui_sc_srisoku_sagaku = $rui_sc_srisoku;
        $rui_sc_srisoku        = number_format(($rui_sc_srisoku / $tani), $keta);
    }
}
if ($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���ʧ��©�Ʒ׻�'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_s_srisoku) < 1) {
        $rui_s_srisoku         = 0;                           // ��������
        $rui_s_srisoku_sagaku  = 0;
        $rui_sl_srisoku        = 0;
        $rui_sl_srisoku_temp   = 0;
        $rui_ss_srisoku        = 0;
        $rui_ss_srisoku_temp   = 0;
        $rui_st_srisoku        = 0;
        $rui_st_srisoku_temp   = 0;
    } else {
        $rui_s_srisoku_sagaku  = $rui_s_srisoku;
        $rui_sl_srisoku        = $rui_s_srisoku - $rui_sc_srisoku_temp;           // ��˥��������ʧ��©��׻�
        $rui_sl_srisoku_temp   = $rui_sl_srisoku;                                 // ��˥������»�׷׻��ѡ�temp)
        
        // �ѵס������׻�(�ѵ�st������ss)
        $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='������ʧ��©�Ʒ׻�'", $str_ym, $yyyymm);
        if (getUniResult($query, $rui_ss_srisoku) < 1) {
            $rui_ss_srisoku        = 0;     // ��������
        }
        $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='�ѵ׻�ʧ��©�Ʒ׻�'", $str_ym, $yyyymm);
        if (getUniResult($query, $rui_st_srisoku) < 1) {
            $rui_st_srisoku        = 0;     // ��������
        }
        // ����ϫ̳�񺹳۷׻�
        $rui_ss_srisoku_temp  = $rui_ss_srisoku;
        $rui_st_srisoku_temp  = $rui_st_srisoku;
        $rui_s_srisoku        = number_format(($rui_s_srisoku / $tani), $keta);
        $rui_ss_srisoku       = number_format(($rui_ss_srisoku / $tani), $keta);
        $rui_st_srisoku       = number_format(($rui_st_srisoku / $tani), $keta);
    }
} elseif ($yyyymm >= 201001 && $yyyymm <= 201003) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=200904 and pl_bs_ym<=200912 and note='���ʧ��©'");
    if (getUniResult($query, $rui_s_srisoku_a) < 1) {
        $rui_s_srisoku_a = 0;                          // ��������
    }
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=201001 and pl_bs_ym<=%d and note='���ʧ��©�Ʒ׻�'", $yyyymm);
    if (getUniResult($query, $rui_s_srisoku_b) < 1) {
        $rui_s_srisoku_b = 0;                          // ��������
    }
    $rui_s_srisoku = $rui_s_srisoku_a + $rui_s_srisoku_b;
    $rui_s_srisoku_sagaku  = $rui_s_srisoku;
    $rui_sl_srisoku        = $rui_s_srisoku - $rui_sc_srisoku_temp;           // ��˥��������ʧ��©��׻�
    $rui_sl_srisoku_temp   = $rui_sl_srisoku;                                 // ��˥������»�׷׻��ѡ�temp)
    $rui_s_srisoku         = number_format(($rui_s_srisoku / $tani), $keta);
    $rui_sl_srisoku        = number_format(($rui_sl_srisoku / $tani), $keta);
} else {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���ʧ��©'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_s_srisoku) < 1) {
        $rui_s_srisoku         = 0;    // ��������
        $rui_s_srisoku_sagaku  = 0;
        $rui_sl_srisoku        = 0;
        $rui_sl_srisoku_temp   = 0;
    } else {
        $rui_s_srisoku_sagaku  = $rui_s_srisoku;
        $rui_sl_srisoku        = $rui_s_srisoku - $rui_sc_srisoku_temp;           // ��˥��������ʧ��©��׻�
        $rui_sl_srisoku_temp   = $rui_sl_srisoku;                                 // ��˥������»�׷׻��ѡ�temp)
        $rui_s_srisoku         = number_format(($rui_s_srisoku / $tani), $keta);
        $rui_sl_srisoku        = number_format(($rui_sl_srisoku / $tani), $keta);
    }
}
/********** �Ķȳ����ѤΤ���¾ **********/
    ///// ����
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ��Ķȳ����Ѥ���¾�Ʒ׻�'", $yyyymm);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ��Ķȳ����Ѥ���¾'", $yyyymm);
}
if (getUniResult($query, $sc_lother) < 1) {
    $sc_lother        = 0;     // ��������
    $sc_lother_sagaku = 0;
    $sc_lother_temp   = 0;
} else {
    $sc_lother_temp   = $sc_lother;
    $sc_lother_sagaku = $sc_lother;
    $sc_lother        = number_format(($sc_lother / $tani), $keta);
}
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��Ķȳ����Ѥ���¾�Ʒ׻�'", $yyyymm);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��Ķȳ����Ѥ���¾'", $yyyymm);
}
if (getUniResult($query, $s_lother) < 1) {
    $s_lother         = 0;    // ��������
    $s_lother_sagaku  = 0;
    $sl_lother        = 0;
    $sl_lother_temp   = 0;
    $ss_lother        = 0;
    $ss_lother_temp   = 0;
    $st_lother        = 0;
    $st_lother_temp   = 0;
} else {
    $s_lother_sagaku  = $s_lother;
    $sl_lother        = $s_lother - $sc_lother_temp;                // ��˥�������Ķȳ����Ѥ���¾��׻�
    $sl_lother_temp   = $sl_lother;                                 // ��˥������»�׷׻��ѡ�temp)
    
    // �ѵס������׻�(�ѵ�st������ss)
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�����Ķȳ����Ѥ���¾�Ʒ׻�'", $yyyymm);
    if (getUniResult($query, $ss_lother) < 1) {
        $ss_lother        = 0;     // ��������
    }
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�ѵױĶȳ����Ѥ���¾�Ʒ׻�'", $yyyymm);
    if (getUniResult($query, $st_lother) < 1) {
        $st_lother        = 0;     // ��������
    }
    // ����ϫ̳�񺹳۷׻�
    $ss_lother_temp  = $ss_lother;
    $st_lother_temp  = $st_lother;
    $s_lother        = number_format(($s_lother / $tani), $keta);
    $ss_lother       = number_format(($ss_lother / $tani), $keta);
    $st_lother       = number_format(($st_lother / $tani), $keta);
}
    ///// ����
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ��Ķȳ����Ѥ���¾�Ʒ׻�'", $p1_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ��Ķȳ����Ѥ���¾'", $p1_ym);
}
if (getUniResult($query, $p1_sc_lother) < 1) {
    $p1_sc_lother        = 0;     // ��������
    $p1_sc_lother_sagaku = 0;
    $p1_sc_lother_temp   = 0;
} else {
    $p1_sc_lother_temp   = $p1_sc_lother;
    $p1_sc_lother_sagaku = $p1_sc_lother;
    $p1_sc_lother        = number_format(($p1_sc_lother / $tani), $keta);
}
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��Ķȳ����Ѥ���¾�Ʒ׻�'", $p1_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��Ķȳ����Ѥ���¾'", $p1_ym);
}
if (getUniResult($query, $p1_s_lother) < 1) {
    $p1_s_lother         = 0;    // ��������
    $p1_s_lother_sagaku  = 0;
    $p1_sl_lother        = 0;
    $p1_sl_lother_temp   = 0;
    $p1_ss_lother        = 0;
    $p1_ss_lother_temp   = 0;
    $p1_st_lother        = 0;
    $p1_st_lother_temp   = 0;
} else {
    $p1_s_lother_sagaku  = $p1_s_lother;
    $p1_sl_lother        = $p1_s_lother - $p1_sc_lother_temp;             // ��˥�������Ķȳ����Ѥ���¾��׻�
    $p1_sl_lother_temp   = $p1_sl_lother;                                 // ��˥������»�׷׻��ѡ�temp)
    
    // �ѵס������׻�(�ѵ�st������ss)
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�����Ķȳ����Ѥ���¾�Ʒ׻�'", $p1_ym);
    if (getUniResult($query, $p1_ss_lother) < 1) {
        $p1_ss_lother        = 0;     // ��������
    }
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�ѵױĶȳ����Ѥ���¾�Ʒ׻�'", $p1_ym);
    if (getUniResult($query, $p1_st_lother) < 1) {
        $p1_st_lother        = 0;     // ��������
    }
    // ����ϫ̳�񺹳۷׻�
    $p1_ss_lother_temp  = $p1_ss_lother;
    $p1_st_lother_temp  = $p1_st_lother;
    $p1_s_lother        = number_format(($p1_s_lother / $tani), $keta);
    $p1_ss_lother       = number_format(($p1_ss_lother / $tani), $keta);
    $p1_st_lother       = number_format(($p1_st_lother / $tani), $keta);
}
    ///// ������
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ��Ķȳ����Ѥ���¾�Ʒ׻�'", $p2_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ��Ķȳ����Ѥ���¾'", $p2_ym);
}
if (getUniResult($query, $p2_sc_lother) < 1) {
    $p2_sc_lother        = 0;     // ��������
    $p2_sc_lother_sagaku = 0;
    $p2_sc_lother_temp   = 0;
} else {
    $p2_sc_lother_temp   = $p2_sc_lother;
    $p2_sc_lother_sagaku = $p2_sc_lother;
    $p2_sc_lother        = number_format(($p2_sc_lother / $tani), $keta);
}
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��Ķȳ����Ѥ���¾�Ʒ׻�'", $p2_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��Ķȳ����Ѥ���¾'", $p2_ym);
}
if (getUniResult($query, $p2_s_lother) < 1) {
    $p2_s_lother         = 0;    // ��������
    $p2_s_lother_sagaku  = 0;
    $p2_sl_lother        = 0;
    $p2_sl_lother_temp   = 0;
    $p2_ss_lother        = 0;
    $p2_ss_lother_temp   = 0;
    $p2_st_lother        = 0;
    $p2_st_lother_temp   = 0;
} else {
    $p2_s_lother_sagaku  = $p2_s_lother;
    $p2_sl_lother        = $p2_s_lother - $p2_sc_lother_temp;             // ��˥�������Ķȳ����Ѥ���¾��׻�
    $p2_sl_lother_temp   = $p2_sl_lother;                                 // ��˥������»�׷׻��ѡ�temp)
    
    // �ѵס������׻�(�ѵ�st������ss)
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�����Ķȳ����Ѥ���¾�Ʒ׻�'", $p2_ym);
    if (getUniResult($query, $p2_ss_lother) < 1) {
        $p2_ss_lother        = 0;     // ��������
    }
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�ѵױĶȳ����Ѥ���¾�Ʒ׻�'", $p2_ym);
    if (getUniResult($query, $p2_st_lother) < 1) {
        $p2_st_lother        = 0;     // ��������
    }
    // ����ϫ̳�񺹳۷׻�
    $p2_ss_lother_temp  = $p2_ss_lother;
    $p2_st_lother_temp  = $p2_st_lother;
    $p2_s_lother        = number_format(($p2_s_lother / $tani), $keta);
    $p2_ss_lother       = number_format(($p2_ss_lother / $tani), $keta);
    $p2_st_lother       = number_format(($p2_st_lother / $tani), $keta);
}
    ///// �����߷�
if ($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���ץ��Ķȳ����Ѥ���¾�Ʒ׻�'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_sc_lother) < 1) {
        $rui_sc_lother        = 0;                           // ��������
        $rui_sc_lother_temp   = 0;
        $rui_sc_lother_sagaku = 0;
    } else {
        $rui_sc_lother_temp   = $rui_sc_lother;
        $rui_sc_lother_sagaku = $rui_sc_lother;
        $rui_sc_lother        = number_format(($rui_sc_lother / $tani), $keta);
    }
} elseif ($yyyymm >= 201001 && $yyyymm <= 201003) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=200904 and pl_bs_ym<=200912 and note='���ץ��Ķȳ����Ѥ���¾'");
    if (getUniResult($query, $rui_sc_lother_a) < 1) {
        $rui_sc_lother_a = 0;                          // ��������
    }
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=201001 and pl_bs_ym<=%d and note='���ץ��Ķȳ����Ѥ���¾�Ʒ׻�'", $yyyymm);
    if (getUniResult($query, $rui_sc_lother_b) < 1) {
        $rui_sc_lother_b = 0;                          // ��������
    }
    $rui_sc_lother        = $rui_sc_lother_a + $rui_sc_lother_b;
    $rui_sc_lother_temp   = $rui_sc_lother;
    $rui_sc_lother_sagaku = $rui_sc_lother;
    $rui_sc_lother        = number_format(($rui_sc_lother / $tani), $keta);
} else {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���ץ��Ķȳ����Ѥ���¾'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_sc_lother) < 1) {
        $rui_sc_lother        = 0;     // ��������
        $rui_sc_lother_sagaku = 0;
        $rui_sc_lother_temp   = 0;
    } else {
        $rui_sc_lother_temp   = $rui_sc_lother;
        $rui_sc_lother_sagaku = $rui_sc_lother;
        $rui_sc_lother        = number_format(($rui_sc_lother / $tani), $keta);
    }
}
if ($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='��Ķȳ����Ѥ���¾�Ʒ׻�'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_s_lother) < 1) {
        $rui_s_lother         = 0;                           // ��������
        $rui_s_lother_sagaku  = 0;
        $rui_sl_lother        = 0;
        $rui_sl_lother_temp   = 0;
        $rui_ss_lother        = 0;
        $rui_ss_lother_temp   = 0;
        $rui_st_lother        = 0;
        $rui_st_lother_temp   = 0;
    } else {
        $rui_s_lother_sagaku  = $rui_s_lother;
        $rui_sl_lother        = $rui_s_lother - $rui_sc_lother_temp;            // ��˥�������Ķȳ����Ѥ���¾��׻�
        $rui_sl_lother_temp   = $rui_sl_lother;                                 // ��˥������»�׷׻��ѡ�temp)
        
        // �ѵס������׻�(�ѵ�st������ss)
        $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='�����Ķȳ����Ѥ���¾�Ʒ׻�'", $str_ym, $yyyymm);
        if (getUniResult($query, $rui_ss_lother) < 1) {
            $rui_ss_lother        = 0;     // ��������
        }
        $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='�ѵױĶȳ����Ѥ���¾�Ʒ׻�'", $str_ym, $yyyymm);
        if (getUniResult($query, $rui_st_lother) < 1) {
            $rui_st_lother        = 0;     // ��������
        }
        // ����ϫ̳�񺹳۷׻�
        $rui_ss_lother_temp  = $rui_ss_lother;
        $rui_st_lother_temp  = $rui_st_lother;
        $rui_s_lother        = number_format(($rui_s_lother / $tani), $keta);
        $rui_ss_lother       = number_format(($rui_ss_lother / $tani), $keta);
        $rui_st_lother       = number_format(($rui_st_lother / $tani), $keta);
    }
} elseif ($yyyymm >= 201001 && $yyyymm <= 201003) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=200904 and pl_bs_ym<=200912 and note='��Ķȳ����Ѥ���¾'");
    if (getUniResult($query, $rui_s_lother_a) < 1) {
        $rui_s_lother_a = 0;                          // ��������
    }
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=201001 and pl_bs_ym<=%d and note='��Ķȳ����Ѥ���¾�Ʒ׻�'", $yyyymm);
    if (getUniResult($query, $rui_s_lother_b) < 1) {
        $rui_s_lother_b = 0;                          // ��������
    }
    $rui_s_lother = $rui_s_lother_a + $rui_s_lother_b;
    $rui_s_lother_sagaku  = $rui_s_lother;
    $rui_sl_lother        = $rui_s_lother - $rui_sc_lother_temp;            // ��˥�������Ķȳ����Ѥ���¾��׻�
    $rui_sl_lother_temp   = $rui_sl_lother;                                 // ��˥������»�׷׻��ѡ�temp)
    $rui_s_lother         = number_format(($rui_s_lother / $tani), $keta);
    $rui_sl_lother        = number_format(($rui_sl_lother / $tani), $keta);
} else {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='��Ķȳ����Ѥ���¾'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_s_lother) < 1) {
        $rui_s_lother         = 0;    // ��������
        $rui_s_lother_sagaku  = 0;
        $rui_sl_lother        = 0;
        $rui_sl_lother_temp   = 0;
    } else {
        $rui_s_lother_sagaku  = $rui_s_lother;
        $rui_sl_lother        = $rui_s_lother - $rui_sc_lother_temp;            // ��˥�������Ķȳ����Ѥ���¾��׻�
        $rui_sl_lother_temp   = $rui_sl_lother;                                 // ��˥������»�׷׻��ѡ�temp)
        $rui_s_lother         = number_format(($rui_s_lother / $tani), $keta);
        $rui_sl_lother        = number_format(($rui_sl_lother / $tani), $keta);
    }
}
/********** �Ķȳ����Ѥι�� **********/
    ///// �������
$p2_s_nonope_loss_sum          = $p2_s_srisoku_sagaku + $p2_s_lother_sagaku;
$p2_s_nonope_loss_sum_sagaku   = $p2_s_nonope_loss_sum;
// �ѵס������׻�(�ѵ�st������ss)
$p2_ss_nonope_loss_sum         = $p2_ss_srisoku_temp + $p2_ss_lother_temp;      // �����Ķȳ����ѹ�׷׻�
$p2_ss_nonope_loss_sum_temp    = $p2_ss_nonope_loss_sum;                        // ����»�׷׻���(temp)
$p2_st_nonope_loss_sum         = $p2_st_srisoku_temp + $p2_st_lother_temp;      // �ѵױĶȳ����ѹ�׷׻�
$p2_st_nonope_loss_sum_temp    = $p2_st_nonope_loss_sum;                        // �ѵ�»�׷׻���(temp)
$p2_s_nonope_loss_sum          = number_format(($p2_s_nonope_loss_sum / $tani), $keta);
$p2_ss_nonope_loss_sum         = number_format(($p2_ss_nonope_loss_sum / $tani), $keta);
$p2_st_nonope_loss_sum         = number_format(($p2_st_nonope_loss_sum / $tani), $keta);

$p1_s_nonope_loss_sum          = $p1_s_srisoku_sagaku + $p1_s_lother_sagaku;
$p1_s_nonope_loss_sum_sagaku   = $p1_s_nonope_loss_sum;
// �ѵס������׻�(�ѵ�st������ss)
$p1_ss_nonope_loss_sum         = $p1_ss_srisoku_temp + $p1_ss_lother_temp;      // �����Ķȳ����ѹ�׷׻�
$p1_ss_nonope_loss_sum_temp    = $p1_ss_nonope_loss_sum;                        // ����»�׷׻���(temp)
$p1_st_nonope_loss_sum         = $p1_st_srisoku_temp + $p1_st_lother_temp;      // �ѵױĶȳ����ѹ�׷׻�
$p1_st_nonope_loss_sum_temp    = $p1_st_nonope_loss_sum;                        // �ѵ�»�׷׻���(temp)
$p1_s_nonope_loss_sum          = number_format(($p1_s_nonope_loss_sum / $tani), $keta);
$p1_ss_nonope_loss_sum         = number_format(($p1_ss_nonope_loss_sum / $tani), $keta);
$p1_st_nonope_loss_sum         = number_format(($p1_st_nonope_loss_sum / $tani), $keta);

$s_nonope_loss_sum             = $s_srisoku_sagaku + $s_lother_sagaku;
$s_nonope_loss_sum_sagaku      = $s_nonope_loss_sum;
// �ѵס������׻�(�ѵ�st������ss)
$ss_nonope_loss_sum            = $ss_srisoku_temp + $ss_lother_temp;            // �����Ķȳ����ѹ�׷׻�
$ss_nonope_loss_sum_temp       = $ss_nonope_loss_sum;                           // ����»�׷׻���(temp)
$st_nonope_loss_sum            = $st_srisoku_temp + $st_lother_temp;            // �ѵױĶȳ����ѹ�׷׻�
$st_nonope_loss_sum_temp       = $st_nonope_loss_sum;                           // �ѵ�»�׷׻���(temp)
$s_nonope_loss_sum             = number_format(($s_nonope_loss_sum / $tani), $keta);
$ss_nonope_loss_sum            = number_format(($ss_nonope_loss_sum / $tani), $keta);
$st_nonope_loss_sum            = number_format(($st_nonope_loss_sum / $tani), $keta);

$rui_s_nonope_loss_sum         = $rui_s_srisoku_sagaku + $rui_s_lother_sagaku;
$rui_s_nonope_loss_sum_sagaku  = $rui_s_nonope_loss_sum;
// �ѵס������׻�(�ѵ�st������ss)
$rui_ss_nonope_loss_sum        = $rui_ss_srisoku_temp + $rui_ss_lother_temp;    // �����Ķȳ����ѹ�׷׻�
$rui_ss_nonope_loss_sum_temp   = $rui_ss_nonope_loss_sum;                       // ����»�׷׻���(temp)
$rui_st_nonope_loss_sum        = $rui_st_srisoku_temp + $rui_st_lother_temp;    // �ѵױĶȳ����ѹ�׷׻�
$rui_st_nonope_loss_sum_temp   = $rui_st_nonope_loss_sum;                       // �ѵ�»�׷׻���(temp)
$rui_s_nonope_loss_sum         = number_format(($rui_s_nonope_loss_sum / $tani), $keta);
$rui_ss_nonope_loss_sum        = number_format(($rui_ss_nonope_loss_sum / $tani), $keta);
$rui_st_nonope_loss_sum        = number_format(($rui_st_nonope_loss_sum / $tani), $keta);

/********** �о����� **********/
    ///// �������
$p2_s_current_profit         = $p2_s_ope_profit_sagaku + $p2_s_nonope_profit_sum_sagaku - $p2_s_nonope_loss_sum_sagaku;
$p2_s_current_profit_sagaku  = $p2_s_current_profit;
// �ѵס������׻�(�ѵ�st������ss)
$p2_ss_current_profit        = $p2_ss_ope_profit_temp + $p2_ss_nonope_profit_sum_temp - $p2_ss_nonope_loss_sum_temp; // �����о����׷׻�
$p2_st_current_profit        = $p2_st_ope_profit_temp + $p2_st_nonope_profit_sum_temp - $p2_st_nonope_loss_sum_temp; // �ѵ׷о����׷׻�
$p2_s_current_profit         = $p2_s_current_profit + $p2_sc_uri_sagaku - $p2_sc_metarial_sagaku;   // ���ץ�������̣��sagaku�β� ��˥�����ޥ��ʥ����Ƥ��ޤ��١�
if ($p2_ym == 200912) {
    $p2_s_current_profit = $p2_s_current_profit + 1409708;
}
if ($p2_ym == 200912) {
    $p2_sl_current_profit = $p2_sl_current_profit + 1195898;
}
if ($p2_ym == 200912) {
    $p2_sc_current_profit = $p2_sc_current_profit + 213810;
}
$p2_s_current_profit         = number_format(($p2_s_current_profit / $tani), $keta);
$p2_ss_current_profit        = number_format(($p2_ss_current_profit / $tani), $keta);
$p2_st_current_profit        = number_format(($p2_st_current_profit / $tani), $keta);

$p1_s_current_profit         = $p1_s_ope_profit_sagaku + $p1_s_nonope_profit_sum_sagaku - $p1_s_nonope_loss_sum_sagaku;
$p1_s_current_profit_sagaku  = $p1_s_current_profit;
// �ѵס������׻�(�ѵ�st������ss)
$p1_ss_current_profit        = $p1_ss_ope_profit_temp + $p1_ss_nonope_profit_sum_temp - $p1_ss_nonope_loss_sum_temp; // �����о����׷׻�
$p1_st_current_profit        = $p1_st_ope_profit_temp + $p1_st_nonope_profit_sum_temp - $p1_st_nonope_loss_sum_temp; // �ѵ׷о����׷׻�
$p1_s_current_profit         = $p1_s_current_profit + $p1_sc_uri_sagaku - $p1_sc_metarial_sagaku;   // ���ץ�������̣��sagaku�β� ��˥�����ޥ��ʥ����Ƥ��ޤ��١�
if ($p1_ym == 200912) {
    $p1_s_current_profit = $p1_s_current_profit + 1409708;
}
if ($p1_ym == 200912) {
    $p1_sl_current_profit = $p1_sl_current_profit + 1195898;
}
if ($p1_ym == 200912) {
    $p1_sc_current_profit = $p1_sc_current_profit + 213810;
}
$p1_s_current_profit         = number_format(($p1_s_current_profit / $tani), $keta);
$p1_ss_current_profit        = number_format(($p1_ss_current_profit / $tani), $keta);
$p1_st_current_profit        = number_format(($p1_st_current_profit / $tani), $keta);

$s_current_profit            = $s_ope_profit_sagaku + $s_nonope_profit_sum_sagaku - $s_nonope_loss_sum_sagaku;
$s_current_profit_sagaku     = $s_current_profit;
// �ѵס������׻�(�ѵ�st������ss)
$ss_current_profit           = $ss_ope_profit_temp + $ss_nonope_profit_sum_temp - $ss_nonope_loss_sum_temp; // �����о����׷׻�
$st_current_profit           = $st_ope_profit_temp + $st_nonope_profit_sum_temp - $st_nonope_loss_sum_temp; // �ѵ׷о����׷׻�
$s_current_profit            = $s_current_profit + $sc_uri_sagaku - $sc_metarial_sagaku;   // ���ץ�������̣��sagaku�β� ��˥�����ޥ��ʥ����Ƥ��ޤ��١�
if ($yyyymm == 200912) {
    $s_current_profit = $s_current_profit + 1409708;
}
if ($yyyymm == 200912) {
    $sc_current_profit = $sc_current_profit + 213810;
}
if ($yyyymm == 200912) {
    $sl_current_profit = $sl_current_profit + 1195898;
}
$s_current_profit            = number_format(($s_current_profit / $tani), $keta);
$ss_current_profit           = number_format(($ss_current_profit / $tani), $keta);
$st_current_profit           = number_format(($st_current_profit / $tani), $keta);

$rui_s_current_profit        = $rui_s_ope_profit_sagaku + $rui_s_nonope_profit_sum_sagaku - $rui_s_nonope_loss_sum_sagaku;
$rui_s_current_profit_sagaku = $rui_s_current_profit;
// �ѵס������׻�(�ѵ�st������ss)
$rui_ss_current_profit       = $rui_ss_ope_profit_temp + $rui_ss_nonope_profit_sum_temp - $rui_ss_nonope_loss_sum_temp; // �����о����׷׻�
$rui_st_current_profit       = $rui_st_ope_profit_temp + $rui_st_nonope_profit_sum_temp - $rui_st_nonope_loss_sum_temp; // �ѵ׷о����׷׻�
$rui_s_current_profit        = $rui_s_current_profit + $rui_sc_uri_sagaku - $rui_sc_metarial_sagaku;   // ���ץ�������̣��sagaku�β� ��˥�����ޥ��ʥ����Ƥ��ޤ��١�
if ($yyyymm >= 200912 && $yyyymm <= 201003) {
    $rui_s_current_profit = $rui_s_current_profit + 1409708;
}
if ($yyyymm >= 200912 && $yyyymm <= 201003) {
    $rui_sc_current_profit = $rui_sc_current_profit + 213810;
}
if ($yyyymm >= 200912 && $yyyymm <= 201003) {
    $rui_sl_current_profit = $rui_sl_current_profit + 1195898;
}
$rui_s_current_profit        = number_format(($rui_s_current_profit / $tani), $keta);
$rui_ss_current_profit       = number_format(($rui_ss_current_profit / $tani), $keta);
$rui_st_current_profit       = number_format(($rui_st_current_profit / $tani), $keta);

////////// �õ�����μ���
$query = sprintf("select comment from act_comment_history where pl_bs_ym=%d and item='����»�׷׻���'", $yyyymm);
if (getUniResult($query,$comment_ss) <= 0) {
    $comment_ss = "";
}
$query = sprintf("select comment from act_comment_history where pl_bs_ym=%d and item='�ѵ�»�׷׻���'", $yyyymm);
if (getUniResult($query,$comment_st) <= 0) {
    $comment_st = "";
}
$query = sprintf("select comment from act_comment_history where pl_bs_ym=%d and item='�����»�׷׻���'", $yyyymm);
if (getUniResult($query,$comment_s) <= 0) {
    $comment_s = "";
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
                    <td rowspan='2' colspan='3' width='200' align='center' class='pt10b' bgcolor='#ffffc6'>�ࡡ������</td>
                    <!-- <td colspan='4' align='center' class='pt10b' bgcolor='#ffffc6'>�ꡡ�ˡ���</td> -->
                    <td colspan='4' align='center' class='pt10b' bgcolor='#ffffc6'>����������</td>
                    <!-- <td colspan='4' align='center' class='pt10b' bgcolor='#ffffc6'>�� �� �� ��</td> -->
                    <td colspan='4' align='center' class='pt10b' bgcolor='#ffffc6'>�ѡ�������</td>
                    <td colspan='4' align='center' class='pt10b' bgcolor='#ffffc6'>�硡������</td>
                    <td rowspan='2' width='400' align='left' class='pt10b' bgcolor='#ffffc6'>��¤���ܷ����δ����������</td>
                </tr>
                <tr>
                    <td nowrap align='center' class='pt10b' bgcolor='#d6d3ce'><?php echo $p2_ym ?> </td>
                    <td nowrap align='center' class='pt10b' bgcolor='#d6d3ce'><?php echo $p1_ym ?> </td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'><?php echo $yyyymm ?></td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>�ߡ���</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#d6d3ce'><?php echo $p2_ym ?> </td>
                    <td nowrap align='center' class='pt10b' bgcolor='#d6d3ce'><?php echo $p1_ym ?> </td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'><?php echo $yyyymm ?></td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>�ߡ���</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#d6d3ce'><?php echo $p2_ym ?> </td>
                    <td nowrap align='center' class='pt10b' bgcolor='#d6d3ce'><?php echo $p1_ym ?> </td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'><?php echo $yyyymm ?></td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>�ߡ���</td>
                </tr>
                <tr>
                    <td rowspan='11' width='10' align='center' valign='middle' class='pt10b' bgcolor='#ceffce'>�ġ��ȡ�»����</td>
                    <td colspan='2' nowrap align='center' class='pt10b' bgcolor='#ceffce'>�䡡�塡��</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p2_ss_uri ?>   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_ss_uri ?>   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $ss_uri ?>      </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $rui_ss_uri ?>  </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p2_st_uri ?>   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_st_uri ?>   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $st_uri ?>      </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $rui_st_uri ?>  </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p2_s_uri ?>   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_s_uri ?>   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $s_uri ?>      </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $rui_s_uri ?>  </td>
                    <td nowrap align='left'  class='pt10' bgcolor='#d6d3ce'>�º�����</td>
                </tr>
                <tr>
                    <td rowspan='6' width='10' align='center' valign='middle' class='pt10b' bgcolor='#ffffc6'>��帶��</td> <!-- ��帶�� -->
                    <td nowrap align='left' class='pt10b' bgcolor='white'>����������ų���ê����</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_ss_invent ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_ss_invent ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $ss_invent ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_ss_invent ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_st_invent ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_st_invent ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $st_invent ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_st_invent ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_s_invent ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_s_invent ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $s_invent ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_s_invent ?></td>
                    <td nowrap align='left'  class='pt10'>��</td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='#e6e6e6'>��������(������)</td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_ss_metarial ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_ss_metarial ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $ss_metarial ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_ss_metarial ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_st_metarial ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_st_metarial ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $st_metarial ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_st_metarial ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_s_metarial ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_s_metarial ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $s_metarial ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_s_metarial ?></td>
                    <td nowrap align='left'  class='pt10'>ô���Խ��פκ�����</td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='white'>��ϫ����̳������</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_ss_roumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_ss_roumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $ss_roumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_ss_roumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_st_roumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_st_roumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $st_roumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_st_roumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_s_roumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_s_roumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $s_roumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_s_roumu ?></td>
                    <td nowrap align='left'  class='pt10'>����������</td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='#e6e6e6'>���С�����������</td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_ss_expense ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_ss_expense ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $ss_expense ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_ss_expense ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_st_expense ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_st_expense ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $st_expense ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_st_expense ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_s_expense ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_s_expense ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $s_expense ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_s_expense ?></td>
                    <td nowrap align='left'  class='pt10'>����������</td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='white'>�����������ų���ê����</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_ss_endinv ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_ss_endinv ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $ss_endinv ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $ss_endinv ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_st_endinv ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_st_endinv ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $st_endinv ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $st_endinv ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_s_endinv ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_s_endinv ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $s_endinv ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $s_endinv ?></td>
                    <td nowrap align='left'  class='pt10'>��</td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='#ffffc6'>���䡡�塡������</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p2_ss_urigen ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p1_ss_urigen ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $ss_urigen ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $rui_ss_urigen ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p2_st_urigen ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p1_st_urigen ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $st_urigen ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $rui_st_urigen ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p2_s_urigen ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p1_s_urigen ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $s_urigen ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $rui_s_urigen ?></td>
                    <td nowrap align='left'  class='pt10'>��</td>
                </tr>
                <tr>
                    <td colspan='2' nowrap align='center' class='pt10b' bgcolor='#ceffce'>�䡡�塡��������</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p2_ss_gross_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_ss_gross_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $ss_gross_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $rui_ss_gross_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p2_st_gross_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_st_gross_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $st_gross_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $rui_st_gross_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p2_s_gross_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_s_gross_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $s_gross_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $rui_s_gross_profit ?></td>
                    <td nowrap align='left'  class='pt10' bgcolor='#d6d3ce'>��</td>  <!-- ;�� -->
                </tr>
                <tr>
                    <td rowspan='3' width='10' align='center' valign='middle' class='pt10b' bgcolor='#ffffc6'></td> <!-- �δ��� -->
                    <td nowrap align='left' class='pt10b' bgcolor='#e6e6e6'>���͡��������</td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_ss_han_jin ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_ss_han_jin ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $ss_han_jin ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_ss_han_jin ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_st_han_jin ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_st_han_jin ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $st_han_jin ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_st_han_jin ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_s_han_jin ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_s_han_jin ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $s_han_jin ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_s_han_jin ?></td>
                    <td nowrap align='left'  class='pt10'>����������</td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='white'>���С�����������</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_ss_han_kei ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_ss_han_kei ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $ss_han_kei ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_ss_han_kei ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_st_han_kei ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_st_han_kei ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $st_han_kei ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_st_han_kei ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_s_han_kei ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_s_han_kei ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $s_han_kei ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_s_han_kei ?></td>
                    <td nowrap align='left'  class='pt10'>����������</td>
                </tr>
                <tr>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>�δ���ڤӰ��̴������</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p2_ss_han_all ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p1_ss_han_all ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $ss_han_all ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $rui_ss_han_all ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p2_st_han_all ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p1_st_han_all ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $st_han_all ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $rui_st_han_all ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p2_s_han_all ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p1_s_han_all ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $s_han_all ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $rui_s_han_all ?></td>
                    <td nowrap align='left'  class='pt10'>��</td>  <!-- ;�� -->
                </tr>
                <tr>
                    <td colspan='3' nowrap align='center' class='pt10b' bgcolor='#ceffce'>�ġ����ȡ�����������</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p2_ss_ope_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_ss_ope_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $ss_ope_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $rui_ss_ope_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p2_st_ope_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_st_ope_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $st_ope_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $rui_st_ope_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p2_s_ope_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_s_ope_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $s_ope_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $rui_s_ope_profit ?></td>
                    <td nowrap align='left'  class='pt10'>��</td>  <!-- ;�� -->
                </tr>
                <tr>
                    <td rowspan='7' align='center' valign='middle' class='pt10b' bgcolor='#ceffce'>�Ķȳ�»��</td>
                    <td rowspan='4' align='center' class='pt10' bgcolor='#ffffc6'></td> <!-- ;�� -->
                    <td nowrap align='left' class='pt10b' bgcolor='white'>����̳��������</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_ss_gyoumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_ss_gyoumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $ss_gyoumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_ss_gyoumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_st_gyoumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_st_gyoumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $st_gyoumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_st_gyoumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_s_gyoumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_s_gyoumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $s_gyoumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_s_gyoumu ?></td>
                    <td nowrap align='left'  class='pt10'>����Ͱ���</td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='#e6e6e6'>���š������䡡��</td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_ss_swari ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_ss_swari ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $ss_swari ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_ss_swari ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_st_swari ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_st_swari ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $st_swari ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_st_swari ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_s_swari ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_s_swari ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $s_swari ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_s_swari ?></td>
                    <td nowrap align='left'  class='pt10'>����Ͱ���</td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='white'>���������Ρ���¾</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_ss_pother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_ss_pother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $ss_pother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_ss_pother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_st_pother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_st_pother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $st_pother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_st_pother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_s_pother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_s_pother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $s_pother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_s_pother ?></td>
                    <td nowrap align='left'  class='pt10'>����Ͱ���</td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='#ffffc6'>���Ķȳ����� ��</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p2_ss_nonope_profit_sum ?>   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p1_ss_nonope_profit_sum ?>   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $ss_nonope_profit_sum ?>      </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $rui_ss_nonope_profit_sum ?>  </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p2_st_nonope_profit_sum ?>   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p1_st_nonope_profit_sum ?>   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $st_nonope_profit_sum ?>      </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $rui_st_nonope_profit_sum ?>  </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p2_s_nonope_profit_sum ?>   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p1_s_nonope_profit_sum ?>   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $s_nonope_profit_sum ?>      </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $rui_s_nonope_profit_sum ?>  </td>
                    <td nowrap align='left'  class='pt10'>��</td> <!-- ;�� -->
                </tr>
                <tr>
                    <td rowspan='3' align='center' class='pt10' bgcolor='#ffffc6'></td> <!-- ;�� -->
                    <td nowrap align='left' class='pt10b' bgcolor='#e6e6e6'>���١�ʧ������©</td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_ss_srisoku ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_ss_srisoku ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $ss_srisoku ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_ss_srisoku ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_st_srisoku ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_st_srisoku ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $st_srisoku ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_st_srisoku ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_s_srisoku ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_s_srisoku ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $s_srisoku ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_s_srisoku ?></td>
                    <td nowrap align='left'  class='pt10'>����Ͱ���</td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='white'>���������Ρ���¾</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_ss_lother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_ss_lother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $ss_lother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_ss_lother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_st_lother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_st_lother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $st_lother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_st_lother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_s_lother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_s_lother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $s_lother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_s_lother ?></td>
                    <td nowrap align='left'  class='pt10'>����Ͱ���</td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='#ffffc6'>���Ķȳ����� ��</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p2_ss_nonope_loss_sum ?>   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p1_ss_nonope_loss_sum ?>   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $ss_nonope_loss_sum ?>      </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $rui_ss_nonope_loss_sum ?>  </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p2_st_nonope_loss_sum ?>   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p1_st_nonope_loss_sum ?>   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $st_nonope_loss_sum ?>      </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $rui_st_nonope_loss_sum ?>  </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p2_s_nonope_loss_sum ?>   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p1_s_nonope_loss_sum ?>   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $s_nonope_loss_sum ?>      </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $rui_s_nonope_loss_sum ?>  </td>
                    <td nowrap align='left'  class='pt10'>��</td> <!-- ;�� -->
                </tr>
                <tr>
                    <td colspan='3' nowrap align='center' class='pt10b' bgcolor='#ceffce'>�С��������������</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p2_ss_current_profit ?>   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_ss_current_profit ?>   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $ss_current_profit ?>      </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $rui_ss_current_profit ?>  </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p2_st_current_profit ?>   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_st_current_profit ?>   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $st_current_profit ?>      </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $rui_st_current_profit ?>  </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p2_s_current_profit ?>   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_s_current_profit ?>   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $s_current_profit ?>      </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $rui_s_current_profit ?>  </td>
                    <td nowrap align='left'  class='pt10'>��</td>  <!-- ;�� -->
                </tr>
            </TBODY>
        </table>
        </td>
        </tr>
        <tr>
        <td>
        <table width='100%' bgcolor='#d6d3ce' align='center' cellspacing="0" cellpadding="3" border='1'>
            <tbody>
                <tr>
                    <td colspan='20' bgcolor='white' align='left' class='pt10b'><a href='<%=$menu->out_action('�õ���������')%>?<?php echo uniqid('menu') ?>' style='text-decoration:none; color:black;'>�������»���õ�����</a></td>
                </tr>
                <tr>
                    <td colspan='20' bgcolor='white' class='pt10'>
                        <ol>
                        <?php
                            if ($comment_ss != "") {
                                echo "<li><pre>$comment_ss</pre></li>\n";
                            }
                            if ($comment_st != "") {
                                echo "<li><pre>$comment_st</pre></li>\n";
                            }
                            if ($comment_s != "") {
                                echo "<li><pre>$comment_s</pre></li>\n";
                            }
                        ?>
                        </ol>
                    </td>
                </tr>
            </tbody>
        </table>
        </td>
        </tr>
    </table>
    </center>
</body>
</html>
