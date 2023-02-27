<?php
//////////////////////////////////////////////////////////////////////////////
// �»�״ط� � ���ץ�����ɸ�� »�׷׻���                            //
// Copyright (C) 2009-2018 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2009/08/21 Created   profit_loss_pl_act_ctoku.php                        //
// 2009/10/07 ���ɤ����Ĵ���κݥ��ץ����Τ���ޥ��ʥ�����褦�ѹ�          //
// 2009/10/15 ���⡦��������ס��Ķ����ס��о����פ��������ѹ�            //
// 2009/12/10 �����Ĵ��                                                    //
// 2010/01/15 200912ʬ���ϫ̳��Ĵ���Τ��ᤳ����ˤ�Ĵ��������          //
// 2010/01/19 200912�٤ζ�̳���������Ȥ���¾��Ĵ����1�����ᤷ��ʬ���       //
// 2010/02/04 2010/01�٤��Ķȳ��˺Ʒ׻������ͤ�Ŭ��                       //
// 2010/02/08 201001�٤������ꤷ��ϫ̳����̣����褦���ѹ�           ��ë //
// 2010/10/08 ����պ����ѤΥǡ�����Ͽ���ɲ�                           ��ë //
// 2011/07/14 �ǡ�����Ͽ��ϫ̳��ȷ���Υǡ�����Ʊ�����ä��Τ���     ��ë //
// 2013/11/07 2013ǯ10�� ���ɶ�̳������ Ĵ��                                //
//            ���ץ������ -1,245,035�ߡ�������¤���� +1,245,035��     ��ë //
//             �� �����ɸ��� 11��˵�Ĵ����Ԥ�����                         //
// 2013/11/07 2013ǯ11�� ���ɶ�̳������ Ĵ��                                //
//            ���ץ������ +1,245,035�ߡ�������¤���� -1,245,035��     ��ë //
// 2014/09/04 ���ɤ���¤����ϫ̳���ƥ�����������ΰ�Ĵ��           ��ë //
// 2018/10/10 2018/09���������ʬ�Ϥ��٤ƥ��ץ�ɸ��ʤΤ�Ĵ��        ��ë //
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

////////////// ����������
// $menu->set_site(10, 7);                     // site_index=10(»�ץ�˥塼) site_id=7(�»��)
//////////// ɽ�������
$menu->set_caption('�������칩��(��)');
//////////// �ƽ����action̾�ȥ��ɥ쥹����
$menu->set_action('�õ���������',   PL . 'profit_loss_comment_put_ctoku.php');

///// ������μ���
$ki = Ym_to_tnk($_SESSION['pl_ym']);
$tuki = substr($_SESSION['pl_ym'],4,2);

//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title("�� {$ki} ����{$tuki} ���١����ץ�����ɸ�� �� �� �� » �� �� �� ��");

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

///// ɽ��ñ�̤��������
if (isset($_POST['keihi_tani'])) {
    $_SESSION['keihi_tani'] = $_POST['keihi_tani'];
    $tani = $_SESSION['keihi_tani'];
} elseif (isset($_SESSION['keihi_tani'])) {
    $tani = $_SESSION['keihi_tani'];
} else {
    $tani = 1000;        // ����� ɽ��ñ�� ���
    $_SESSION['keihi_tani'] = $tani;
}
///// ɽ�� ��������� �������
if (isset($_POST['keihi_keta'])) {
    $_SESSION['keihi_keta'] = $_POST['keihi_keta'];
    $keta = $_SESSION['keihi_keta'];
} elseif (isset($_SESSION['keihi_keta'])) {
    $keta = $_SESSION['keihi_keta'];
} else {
    $keta = 0;          // ����� �������ʲ����
    $_SESSION['keihi_keta'] = $keta;
}

/********** ���� **********/
    ///// ����
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ���������'", $yyyymm);
if (getUniResult($query, $ctoku_uri) < 1) {
    $ctoku_uri        = 0;     // ��������
    $ctoku_uri_sagaku = 0;
} else {
    if ($yyyymm == 201801) {
        $ctoku_uri = $ctoku_uri - 7880000;
    }
    if ($yyyymm == 201802) {
        $ctoku_uri = $ctoku_uri + 7880000;
    }
    $ctoku_uri_sagaku = $ctoku_uri;
    $ctoku_uri        = number_format(($ctoku_uri / $tani), $keta);
}
if ( $yyyymm >= 200911) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�����'", $yyyymm);
    if (getUniResult($query, $sc_uri) < 1) {
        $sc_uri        = 0;    // ��������
        $sc_uri_sagaku = 0;
        $sc_uri_temp   = 0;
    } else {
        $sc_uri_temp   = $sc_uri;
        $sc_uri_sagaku = $sc_uri;
        $sc_uri        = number_format(($sc_uri / $tani), $keta);
    }
} else{
    $sc_uri            = 0;    // ��������
    $sc_uri_sagaku     = 0;
    $sc_uri_temp       = 0;
}
if ( $yyyymm >= 200910) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�����'", $yyyymm);
    if (getUniResult($query, $c_uri) < 1) {
        $c_uri         = 0;    // ��������
        $ch_uri        = 0;
        $ch_uri_sagaku = 0;
    }
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�������Ĵ����'", $yyyymm);
    if (getUniResult($query, $b_uri_cho) < 1) {
        $b_uri_cho     = 0;                                 // ��������
        $c_uri         = $c_uri - $sc_uri_sagaku;           //���ץ��������̣
        $ch_uri        = $c_uri - $ctoku_uri_sagaku;
        $ch_uri_sagaku = $ch_uri;
        $ch_uri        = number_format(($ch_uri / $tani), $keta);
        $c_uri         = number_format(($c_uri / $tani), $keta);
    } else {
        $c_uri         = $c_uri - $sc_uri_sagaku;
        $ch_uri        = $c_uri - $ctoku_uri_sagaku;
        $ch_uri_sagaku = $ch_uri;
        $ch_uri        = number_format(($ch_uri / $tani), $keta);
        $c_uri         = number_format(($c_uri / $tani), $keta);
    }
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�����'", $yyyymm);
    if (getUniResult($query, $c_uri) < 1) {
        $c_uri         = 0;     // ��������
        $ch_uri        = 0;
        $ch_uri_sagaku = 0;
    } else {
        $ch_uri        = $c_uri - $ctoku_uri_sagaku;
        $ch_uri_sagaku = $ch_uri;
        $ch_uri        = number_format(($ch_uri / $tani), $keta);
        $c_uri         = number_format(($c_uri / $tani), $keta);
    }
}
    ///// ����
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ���������'", $p1_ym);
if (getUniResult($query, $p1_ctoku_uri) < 1) {
    $p1_ctoku_uri         = 0;     // ��������
    $p1_ctoku_uri_sagaku  = 0;
} else {
    if ($p1_ym == 201801) {
        $p1_ctoku_uri = $p1_ctoku_uri - 7880000;
    }
    if ($p1_ym == 201802) {
        $p1_ctoku_uri = $p1_ctoku_uri + 7880000;
    }
    $p1_ctoku_uri_sagaku  = $p1_ctoku_uri;
    $p1_ctoku_uri         = number_format(($p1_ctoku_uri / $tani), $keta);
}
if ( $yyyymm >= 200911) {
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
} else{
    $p1_sc_uri            = 0;     // ��������
    $p1_sc_uri_sagaku     = 0;
    $p1_sc_uri_temp       = 0;
}
if ( $yyyymm >= 200911) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�����'", $p1_ym);
    if (getUniResult($query, $p1_c_uri) < 1) {
        $p1_c_uri         = 0;     // ��������
        $p1_ch_uri        = 0;
        $p1_ch_uri_sagaku = 0;
    }
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�������Ĵ����'", $p1_ym);
    if (getUniResult($query, $p1_b_uri_cho) < 1) {
        $p1_b_uri_cho     = 0; // ��������
        $p1_c_uri         = $p1_c_uri - $p1_sc_uri_sagaku;          //���ץ��������̣
        $p1_ch_uri        = $p1_c_uri - $p1_ctoku_uri_sagaku;
        $p1_ch_uri_sagaku = $p1_ch_uri;
        $p1_ch_uri        = number_format(($p1_ch_uri / $tani), $keta);
        $p1_c_uri         = number_format(($p1_c_uri / $tani), $keta);
    } else {
        $p1_c_uri         = $p1_c_uri - $p1_sc_uri_sagaku;
        $p1_ch_uri        = $p1_c_uri - $p1_ctoku_uri_sagaku;
        $p1_ch_uri_sagaku = $p1_ch_uri;
        $p1_ch_uri        = number_format(($p1_ch_uri / $tani), $keta);
        $p1_c_uri         = number_format(($p1_c_uri / $tani), $keta);
    }
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�����'", $p1_ym);
    if (getUniResult($query, $p1_c_uri) < 1) {
        $p1_c_uri         = 0;     // ��������
        $p1_ch_uri        = 0;
        $p1_ch_uri_sagaku = 0;
    } else {
        $p1_ch_uri        = $p1_c_uri - $p1_ctoku_uri_sagaku;
        $p1_ch_uri_sagaku = $p1_ch_uri;
        $p1_ch_uri        = number_format(($p1_ch_uri / $tani), $keta);
        $p1_c_uri         = number_format(($p1_c_uri / $tani), $keta);
    }
}
    ///// ������
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ���������'", $p2_ym);
if (getUniResult($query, $p2_ctoku_uri) < 1) {
    $p2_ctoku_uri         = 0;     // ��������
    $p2_ctoku_uri_sagaku  = 0;
} else {
    if ($p2_ym == 201801) {
        $p2_ctoku_uri = $p2_ctoku_uri - 7880000;
    }
    if ($p2_ym == 201802) {
        $p2_ctoku_uri = $p2_ctoku_uri + 7880000;
    }
    $p2_ctoku_uri_sagaku  = $p2_ctoku_uri;
    $p2_ctoku_uri         = number_format(($p2_ctoku_uri / $tani), $keta);
}
if ( $yyyymm >= 200911) {
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
} else{
    $p2_sc_uri            = 0;     // ��������
    $p2_sc_uri_sagaku     = 0;
    $p2_sc_uri_temp       = 0;
}
if ( $yyyymm >= 200912) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�����'", $p2_ym);
    if (getUniResult($query, $p2_c_uri) < 1) {
        $p2_c_uri         = 0;     // ��������
        $p2_ch_uri        = 0;
        $p2_ch_uri_sagaku = 0;
    }
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�������Ĵ����'", $p2_ym);
    if (getUniResult($query, $p2_b_uri_cho) < 1) {
        $p2_b_uri_cho     = 0; // ��������
        $p2_c_uri         = $p2_c_uri - $p2_sc_uri_sagaku;          //���ץ��������̣
        $p2_ch_uri        = $p2_c_uri - $p2_ctoku_uri_sagaku;
        $p2_ch_uri_sagaku = $p2_ch_uri;
        $p2_ch_uri        = number_format(($p1_ch_uri / $tani), $keta);
        $p2_c_uri         = number_format(($p1_c_uri / $tani), $keta);
    } else {
        $p2_c_uri         = $p2_c_uri - $p2_sc_uri_sagaku;
        $p2_ch_uri        = $p2_c_uri - $p2_ctoku_uri_sagaku;
        $p2_ch_uri_sagaku = $p2_ch_uri;
        $p2_ch_uri        = number_format(($p2_ch_uri / $tani), $keta);
        $p2_c_uri         = number_format(($p2_c_uri / $tani), $keta);
    }
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�����'", $p2_ym);
    if (getUniResult($query, $p2_c_uri) < 1) {
        $p2_c_uri         = 0;     // ��������
        $p2_ch_uri        = 0;
        $p2_ch_uri_sagaku = 0;
    } else {
        $p2_ch_uri        = $p2_c_uri - $p2_ctoku_uri_sagaku;
        $p2_ch_uri_sagaku = $p2_ch_uri;
        $p2_ch_uri        = number_format(($p2_ch_uri / $tani), $keta);
        $p2_c_uri         = number_format(($p2_c_uri / $tani), $keta);
    }
}
    ///// �����߷�
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���ץ���������'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_ctoku_uri) < 1) {
    $rui_ctoku_uri         = 0;     // ��������
    $rui_ctoku_uri_sagaku  = 0;
} else {
    $rui_ctoku_uri_sagaku  = $rui_ctoku_uri;
    $rui_ctoku_uri         = number_format(($rui_ctoku_uri / $tani), $keta);
}
if ( $yyyymm >= 200911) {
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
} else{
    $rui_sc_uri            = 0;     // ��������
    $rui_sc_uri_sagaku     = 0;
    $rui_sc_uri_temp       = 0;
}
if ( $yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���ץ�����'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_c_uri) < 1) {
        $rui_c_uri         = 0;     // ��������
        $rui_ch_uri        = 0;
        $rui_ch_uri_sagaku = 0;
    }
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='�������Ĵ����'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_b_uri_cho) < 1) {
        $rui_b_uri_cho     = 0;
        $rui_c_uri         = $rui_c_uri - $rui_sc_uri_sagaku;           //���ץ��������̣
        $rui_ch_uri        = $rui_c_uri - $rui_ctoku_uri_sagaku;
        $rui_ch_uri_sagaku = $rui_ch_uri;
        $rui_ch_uri        = number_format(($rui_ch_uri / $tani), $keta);
        $rui_c_uri         = number_format(($rui_c_uri / $tani), $keta);
    } else {
        $rui_c_uri         = $rui_c_uri - $rui_sc_uri_sagaku;
        $rui_ch_uri        = $rui_c_uri - $rui_ctoku_uri_sagaku;
        $rui_ch_uri_sagaku = $rui_ch_uri;
        $rui_ch_uri        = number_format(($rui_ch_uri / $tani), $keta);
        $rui_c_uri         = number_format(($rui_c_uri / $tani), $keta);
    }
} else if($yyyymm >= 200910 && $yyyymm <= 201003) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���ץ�����'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_c_uri) < 1) {
        $rui_c_uri         = 0;     // ��������
        $rui_ch_uri        = 0;
        $rui_ch_uri_sagaku = 0;
    }
    $str_ymb = 200910;
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='�������Ĵ����'", $str_ymb, $yyyymm);
    if (getUniResult($query, $rui_b_uri_cho) < 1) {
        $rui_b_uri_cho     = 0;
        $rui_c_uri         = $rui_c_uri - $rui_sc_uri_sagaku;           //���ץ��������̣
        $rui_ch_uri        = $rui_c_uri - $rui_ctoku_uri_sagaku;
        $rui_ch_uri_sagaku = $rui_ch_uri;
        $rui_ch_uri        = number_format(($rui_ch_uri / $tani), $keta);
        $rui_c_uri         = number_format(($rui_c_uri / $tani), $keta);
    } else {
        $rui_c_uri         = $rui_c_uri - $rui_sc_uri_sagaku;
        $rui_ch_uri        = $rui_c_uri - $rui_ctoku_uri_sagaku;
        $rui_ch_uri_sagaku = $rui_ch_uri;
        $rui_ch_uri        = number_format(($rui_ch_uri / $tani), $keta);
        $rui_c_uri         = number_format(($rui_c_uri / $tani), $keta);
    }
} else {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���ץ�����'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_c_uri) < 1) {
        $rui_c_uri         = 0;     // ��������
        $rui_ch_uri        = 0;
        $rui_ch_uri_sagaku = 0;
    } else {
        $rui_ch_uri        = $rui_c_uri - $rui_ctoku_uri_sagaku;
        $rui_ch_uri_sagaku = $rui_ch_uri;
        $rui_ch_uri        = number_format(($rui_ch_uri / $tani), $keta);
        $rui_c_uri         = number_format(($rui_c_uri / $tani), $keta);
    }
}

/********** ��������ų���ê���� **********/
    ///// ����
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ��������ê����'", $yyyymm);
if (getUniResult($query, $ctoku_invent) < 1) {
    $ctoku_invent        = 0;     // ��������
    $ctoku_invent_sagaku = 0;
} else {
    $ctoku_invent_sagaku = $ctoku_invent;
    $ctoku_invent = number_format(($ctoku_invent / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ����ê����'", $yyyymm);
if (getUniResult($query, $c_invent) < 1) {
    $c_invent            = 0;     // ��������
    $ch_invent           = 0;
    $ch_invent_sagaku    = 0;
} else {
    $ch_invent           = $c_invent - $ctoku_invent_sagaku;
    $ch_invent_sagaku    = $ch_invent;
    $ch_invent           = number_format(($ch_invent / $tani), $keta);
    $c_invent            = number_format(($c_invent / $tani), $keta);
}
    ///// ����
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ��������ê����'", $p1_ym);
if (getUniResult($query, $p1_ctoku_invent) < 1) {
    $p1_ctoku_invent        = 0;     // ��������
    $p1_ctoku_invent_sagaku = 0;
} else {
    $p1_ctoku_invent_sagaku = $p1_ctoku_invent;
    $p1_ctoku_invent        = number_format(($p1_ctoku_invent / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ����ê����'", $p1_ym);
if (getUniResult($query, $p1_c_invent) < 1) {
    $p1_c_invent            = 0;     // ��������
    $p1_ch_invent           = 0;
    $p1_ch_invent_sagaku    = 0;
} else {
    $p1_ch_invent           = $p1_c_invent - $p1_ctoku_invent_sagaku;
    $p1_ch_invent_sagaku    = $p1_ch_invent;
    $p1_ch_invent           = number_format(($p1_ch_invent / $tani), $keta);
    $p1_c_invent            = number_format(($p1_c_invent / $tani), $keta);
}
    ///// ������
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ��������ê����'", $p2_ym);
if (getUniResult($query, $p2_ctoku_invent) < 1) {
    $p2_ctoku_invent        = 0;     // ��������
    $p2_ctoku_invent_sagaku = 0;
} else {
    $p2_ctoku_invent_sagaku = $p2_ctoku_invent;
    $p2_ctoku_invent        = number_format(($p2_ctoku_invent / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ����ê����'", $p2_ym);
if (getUniResult($query, $p2_c_invent) < 1) {
    $p2_c_invent            = 0;     // ��������
    $p2_ch_invent           = 0;
    $p2_ch_invent_sagaku    = 0;
} else {
    $p2_ch_invent           = $p2_c_invent - $p2_ctoku_invent_sagaku;
    $p2_ch_invent_sagaku    = $p2_ch_invent;
    $p2_ch_invent           = number_format(($p2_ch_invent / $tani), $keta);
    $p2_c_invent            = number_format(($p2_c_invent / $tani), $keta);
}
    ///// �����߷�
    /////   ����ê������߷פ� ����ǯ��δ���ê����ˤʤ�
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ��������ê����'", $str_ym);
if (getUniResult($query, $rui_ctoku_invent) < 1) {
    $rui_ctoku_invent        = 0;     // ��������
    $rui_ctoku_invent_sagaku = 0;
} else {
    $rui_ctoku_invent_sagaku = $rui_ctoku_invent;
    $rui_ctoku_invent        = number_format(($rui_ctoku_invent / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ����ê����'", $str_ym);
if (getUniResult($query, $rui_c_invent) < 1) {
    $rui_c_invent            = 0;     // ��������
    $rui_ch_invent           = 0;
    $rui_ch_invent_sagaku    = 0;
} else {
    $rui_ch_invent           = $rui_c_invent - $rui_ctoku_invent_sagaku;
    $rui_ch_invent_sagaku    = $rui_ch_invent;
    $rui_ch_invent           = number_format(($rui_ch_invent / $tani), $keta);
    $rui_c_invent            = number_format(($rui_c_invent / $tani), $keta);
}

/********** ������(������) **********/
    ///// ����
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ����������'", $yyyymm);
if (getUniResult($query, $ctoku_metarial) < 1) {
    $ctoku_metarial          = 0;   // ��������
    $ctoku_metarial_sagaku   = 0;
} else {
    $ctoku_metarial_sagaku   = $ctoku_metarial;
    $ctoku_metarial          = number_format(($ctoku_metarial / $tani), $keta);
}
if ( $yyyymm >= 200911) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�������'", $yyyymm);
    if (getUniResult($query, $sc_metarial) < 1) {
        $sc_metarial         = 0;   // ��������
        $sc_metarial_sagaku  = 0;
        $sc_metarial_temp    = 0;
    } else {
        $sc_metarial_temp    = $sc_metarial;
        $sc_metarial_sagaku  = $sc_metarial;
        $sc_metarial         = number_format(($sc_metarial / $tani), $keta);
    }
} else{
    $sc_metarial             = 0;     // ��������
    $sc_metarial_sagaku      = 0;
    $sc_metarial_temp        = 0;
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ������'", $yyyymm);
if (getUniResult($query, $c_metarial) < 1) {
    $c_metarial              = 0;     // ��������
    $ch_metarial             = 0;
    $ch_metarial_sagaku      = 0;
} else {
    $c_metarial              = $c_metarial - $sc_metarial_sagaku;       //���ץ��������̣
    // 2013/11/07 �ɲ� 2013ǯ10���� ��̳������ʲ����ɸ�����Ĵ��
    if ($yyyymm == 201310) {
        $c_metarial -= 1245035;
    }
    if ($yyyymm == 201311) {
        $c_metarial += 1245035;
    }
    $ch_metarial             = $c_metarial - $ctoku_metarial_sagaku;
    $ch_metarial_sagaku      = $ch_metarial;
    // 2013/11/07 �ɲ� 2013ǯ10���� ��̳������ʲ����ɸ�����Ĵ��
    $ch_metarial             = number_format(($ch_metarial / $tani), $keta);
    $c_metarial              = number_format(($c_metarial / $tani), $keta);
}
    ///// ����
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ����������'", $p1_ym);
if (getUniResult($query, $p1_ctoku_metarial) < 1) {
    $p1_ctoku_metarial         = 0;   // ��������
    $p1_ctoku_metarial_sagaku  = 0;
} else {
    $p1_ctoku_metarial_sagaku  = $p1_ctoku_metarial;
    $p1_ctoku_metarial         = number_format(($p1_ctoku_metarial / $tani), $keta);
}
if ( $yyyymm >= 200911) {
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
} else{
    $p1_sc_metarial            = 0;     // ��������
    $p1_sc_metarial_sagaku     = 0;
    $p1_sc_metarial_temp       = 0;
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ������'", $p1_ym);
if (getUniResult($query, $p1_c_metarial) < 1) {
    $p1_c_metarial         = 0;         // ��������
    $p1_ch_metarial        = 0;
    $p1_ch_metarial_sagaku = 0;
} else {
    $p1_c_metarial         = $p1_c_metarial - $p1_sc_metarial_sagaku;       //���ץ��������̣
    // 2013/11/07 �ɲ� 2013ǯ10���� ��̳������ʲ����ɸ�����Ĵ��
    if ($p1_ym == 201310) {
        $p1_c_metarial -= 1245035;
    }
    if ($p1_ym == 201311) {
        $p1_c_metarial += 1245035;
    }
    $p1_ch_metarial        = $p1_c_metarial - $p1_ctoku_metarial_sagaku;
    $p1_ch_metarial_sagaku = $p1_ch_metarial;
    $p1_ch_metarial        = number_format(($p1_ch_metarial / $tani), $keta);
    $p1_c_metarial = number_format(($p1_c_metarial / $tani), $keta);
}
    ///// ������
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ����������'", $p2_ym);
if (getUniResult($query, $p2_ctoku_metarial) < 1) {
    $p2_ctoku_metarial        = 0;      // ��������
    $p2_ctoku_metarial_sagaku = 0;
} else {
    $p2_ctoku_metarial_sagaku = $p2_ctoku_metarial;
    $p2_ctoku_metarial        = number_format(($p2_ctoku_metarial / $tani), $keta);
}
if ( $yyyymm >= 200911) {
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
} else{
    $p2_sc_metarial            = 0;     // ��������
    $p2_sc_metarial_sagaku     = 0;
    $p2_sc_metarial_temp       = 0;
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ������'", $p2_ym);
if (getUniResult($query, $p2_c_metarial) < 1) {
    $p2_c_metarial         = 0;         // ��������
    $p2_ch_metarial        = 0;
    $p2_ch_metarial_sagaku = 0;
} else {
    $p2_c_metarial         = $p2_c_metarial - $p2_sc_metarial_sagaku;       //���ץ��������̣
    // 2013/11/07 �ɲ� 2013ǯ10���� ��̳������ʲ����ɸ�����Ĵ��
    if ($p2_ym == 201310) {
        $p2_c_metarial -= 1245035;
    }
    if ($p2_ym == 201311) {
        $p2_c_metarial += 1245035;
    }
    $p2_ch_metarial        = $p2_c_metarial - $p2_ctoku_metarial_sagaku;
    $p2_ch_metarial_sagaku = $p2_ch_metarial;
    $p2_ch_metarial        = number_format(($p2_ch_metarial / $tani), $keta);
    $p2_c_metarial         = number_format(($p2_c_metarial / $tani), $keta);
}
    ///// �����߷�
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���ץ����������'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_ctoku_metarial) < 1) {
    $rui_ctoku_metarial        = 0;     // ��������
    $rui_ctoku_metarial_sagaku = 0;
} else {
    $rui_ctoku_metarial_sagaku = $rui_ctoku_metarial;
    $rui_ctoku_metarial        = number_format(($rui_ctoku_metarial / $tani), $keta);
}
if ( $yyyymm >= 200911) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���ץ�������'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_sc_metarial) < 1) {
        $rui_sc_metarial        = 0;    // ��������
        $rui_sc_metarial_sagaku = 0;
        $rui_sc_metarial_temp   = 0;
    } else {
        $rui_sc_metarial_temp   = $rui_sc_metarial;
        $rui_sc_metarial_sagaku = $rui_sc_metarial;
        $rui_sc_metarial        = number_format(($rui_sc_metarial / $tani), $keta);
    }
} else{
    $rui_sc_metarial            = 0;    // ��������
    $rui_sc_metarial_sagaku     = 0;
    $rui_sc_metarial_temp       = 0;
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���ץ������'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_c_metarial) < 1) {
    $rui_c_metarial         = 0;        // ��������
    $rui_ch_metarial        = 0;
    $rui_ch_metarial_sagaku = 0;
} else {
    $rui_c_metarial         = $rui_c_metarial - $rui_sc_metarial_sagaku;       //���ץ��������̣
    // 2013/11/07 �ɲ� 2013ǯ10���� ��̳������ʲ����ɸ�����Ĵ��
    if ($yyyymm >= 201310 && $yyyymm <= 201403) {
        $rui_c_metarial -= 1245035;
    }
    if ($yyyymm >= 201311 && $yyyymm <= 201403) {
        $rui_c_metarial += 1245035;
    }
    $rui_ch_metarial        = $rui_c_metarial - $rui_ctoku_metarial_sagaku;
    $rui_ch_metarial_sagaku = $rui_ch_metarial;
    $rui_ch_metarial        = number_format(($rui_ch_metarial / $tani), $keta);
    $rui_c_metarial         = number_format(($rui_c_metarial / $tani), $keta);
}

/********** ϫ̳�� **********/
    ///// ����
    // ���ʴ����ʥ��ץ�˻���Ū������Ƥ��뤿���
    // ����7��̤ʧ����Ϳʬ
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='����ϫ̳��'", $yyyymm);
if (getUniResult($query, $b_roumu_sagaku) < 1) {
    $b_roumu_sagaku = 0;    // ��������
}
$query = sprintf("select sum(orign_kin) from act_allo_history where pl_bs_ym=%d and actcod>=8101 and orign_id=580", $yyyymm);
if (getUniResult($query, $b_roumu) < 1) {
    $b_roumu        = 0;    // ��������
} else {
    $b_roumu        = $b_roumu + $b_roumu_sagaku;
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�����ϫ̳��'", $yyyymm);
if (getUniResult($query, $ctoku_roumu) < 1) {
    $ctoku_roumu        = 0;    // ��������
    $ctoku_roumu_sagaku = 0;
} else {
    $ctoku_roumu_sagaku = $ctoku_roumu;
    $ctoku_roumu        = number_format(($ctoku_roumu / $tani), $keta);
}
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ��Ϳ����Ψ'", $yyyymm);
    if (getUniResult($query, $c_kyu_kin) < 1) {
        $c_kyu_kin = 0;
    }
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�ϫ̳��'", $yyyymm);
if (getUniResult($query, $c_roumu) < 1) {
    $c_roumu            = 0;    // ��������]
    $ch_roumu           = 0;
    $ch_roumu_sagaku    = 0;
} else {
    $c_roumu            = $c_roumu - $b_roumu;
    if ($yyyymm == 200912) {
        $c_roumu = $c_roumu + 1227429;
    }
    if ($yyyymm >= 201001) {
        $c_roumu = $c_roumu + $c_kyu_kin;   // ���ץ������Ϳ���̣(����ɸ��ˡ�
        //$c_roumu = $c_roumu + 151313;   // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
    }
    if ($yyyymm == 201408) {
        $c_roumu = $c_roumu + 611904;
    }
    $ch_roumu           = $c_roumu - $ctoku_roumu_sagaku;
    $ch_roumu_sagaku    = $ch_roumu;
    $ch_roumu           = number_format(($ch_roumu / $tani), $keta);
    $c_roumu            = number_format(($c_roumu / $tani), $keta);
}
    ///// ����
    // ���ʴ����ʥ��ץ�˻���Ū������Ƥ��뤿���
    // ����7��̤ʧ����Ϳʬ
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='����ϫ̳��'", $p1_ym);
if (getUniResult($query, $p1_b_roumu_sagaku) < 1) {
    $p1_b_roumu_sagaku = 0;    // ��������
}
$query = sprintf("select sum(orign_kin) from act_allo_history where pl_bs_ym=%d and actcod>=8101 and orign_id=580", $p1_ym);
if (getUniResult($query, $p1_b_roumu) < 1) {
    $p1_b_roumu        = 0;    // ��������
} else {
    $p1_b_roumu        = $p1_b_roumu + $p1_b_roumu_sagaku;
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�����ϫ̳��'", $p1_ym);
if (getUniResult($query, $p1_ctoku_roumu) < 1) {
    $p1_ctoku_roumu        = 0;    // ��������
    $p1_ctoku_roumu_sagaku = 0;
} else {
    $p1_ctoku_roumu_sagaku = $p1_ctoku_roumu;
    $p1_ctoku_roumu        = number_format(($p1_ctoku_roumu / $tani), $keta);
}
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ��Ϳ����Ψ'", $p1_ym);
    if (getUniResult($query, $p1_c_kyu_kin) < 1) {
        $p1_c_kyu_kin = 0;
    }
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�ϫ̳��'", $p1_ym);
if (getUniResult($query, $p1_c_roumu) < 1) {
    $p1_c_roumu         = 0;       // ��������]
    $p1_ch_roumu        = 0;
    $p1_ch_roumu_sagaku = 0;
} else {
    $p1_c_roumu         = $p1_c_roumu - $p1_b_roumu;
    if ($p1_ym == 200912) {
        $p1_c_roumu = $p1_c_roumu + 1227429;
    }
    if ($p1_ym >= 201001) {
        $p1_c_roumu = $p1_c_roumu + $p1_c_kyu_kin;   // ���ץ������Ϳ���̣������ɸ��ˡ�
        //$p1_c_roumu = $p1_c_roumu + 151313; // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
    }
    if ($p1_ym == 201408) {
        $p1_c_roumu = $p1_c_roumu + 611904;
    }
    $p1_ch_roumu        = $p1_c_roumu - $p1_ctoku_roumu_sagaku;
    $p1_ch_roumu_sagaku = $p1_ch_roumu;
    $p1_ch_roumu        = number_format(($p1_ch_roumu / $tani), $keta);
    $p1_c_roumu         = number_format(($p1_c_roumu / $tani), $keta);
}
    ///// ������
    // ���ʴ����ʥ��ץ�˻���Ū������Ƥ��뤿���
    // ����7��̤ʧ����Ϳʬ
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='����ϫ̳��'", $p2_ym);
if (getUniResult($query, $p2_b_roumu_sagaku) < 1) {
    $p2_b_roumu_sagaku  = 0;       // ��������
}
$query = sprintf("select sum(orign_kin) from act_allo_history where pl_bs_ym=%d and actcod>=8101 and orign_id=580", $p2_ym);
if (getUniResult($query, $p2_b_roumu) < 1) {
    $p2_b_roumu         = 0;      // ��������
} else {
    $p2_b_roumu         = $p2_b_roumu + $p2_b_roumu_sagaku;
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�����ϫ̳��'", $p2_ym);
if (getUniResult($query, $p2_ctoku_roumu) < 1) {
    $p2_ctoku_roumu        = 0;   // ��������
    $p2_ctoku_roumu_sagaku = 0;
} else {
    $p2_ctoku_roumu_sagaku = $p2_ctoku_roumu;
    $p2_ctoku_roumu        = number_format(($p2_ctoku_roumu / $tani), $keta);
}
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ��Ϳ����Ψ'", $p2_ym);
    if (getUniResult($query, $p2_c_kyu_kin) < 1) {
        $p2_c_kyu_kin = 0;
    }
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�ϫ̳��'", $p2_ym);
if (getUniResult($query, $p2_c_roumu) < 1) {
    $p2_c_roumu            = 0;   // ��������]
    $p2_ch_roumu           = 0;
    $p2_ch_roumu_sagaku    = 0;
} else {
    $p2_c_roumu            = $p2_c_roumu - $p2_b_roumu;
    if ($p2_ym == 200912) {
        $p2_c_roumu = $p2_c_roumu + 1227429;
    }
    if ($p2_ym >= 201001) {
        $p2_c_roumu = $p2_c_roumu + $p2_c_kyu_kin;   // ���ץ������Ϳ���̣������ɸ��ˡ�
        //$p2_c_roumu = $p2_c_roumu + 151313;    // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
    }
    if ($p2_ym == 201408) {
        $p2_c_roumu = $p2_c_roumu + 611904;
    }
    $p2_ch_roumu           = $p2_c_roumu - $p2_ctoku_roumu_sagaku;
    $p2_ch_roumu_sagaku    = $p2_ch_roumu;
    $p2_ch_roumu           = number_format(($p2_ch_roumu / $tani), $keta);
    $p2_c_roumu            = number_format(($p2_c_roumu / $tani), $keta);
}
    ///// �����߷�
    // ���ʴ����ʥ��ץ�˻���Ū������Ƥ��뤿���
    // ����7��̤ʧ����Ϳʬ
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='����ϫ̳��'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_b_roumu_sagaku) < 1) {
    $rui_b_roumu_sagaku = 0;    // ��������
}
$query = sprintf("select sum(orign_kin) from act_allo_history where pl_bs_ym>=%d and pl_bs_ym<=%d and actcod>=8101 and orign_id=580", $str_ym, $yyyymm);
if (getUniResult($query, $rui_b_roumu) < 1) {
    $rui_b_roumu        = 0;    // ��������
} else {
    $rui_b_roumu        = $rui_b_roumu + $rui_b_roumu_sagaku;
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���ץ�����ϫ̳��'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_ctoku_roumu) < 1) {
    $rui_ctoku_roumu        = 0;    // ��������
    $rui_ctoku_roumu_sagaku = 0;
} else {
    $rui_ctoku_roumu_sagaku = $rui_ctoku_roumu;
    $rui_ctoku_roumu        = number_format(($rui_ctoku_roumu / $tani), $keta);
}
if ($yyyymm >= 201001) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���ץ��Ϳ����Ψ'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_c_kyu_kin) < 1) {
        $rui_c_kyu_kin = 0;
    }
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���ץ�ϫ̳��'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_c_roumu) < 1) {
    $rui_c_roumu         = 0;       // ��������
    $rui_ch_roumu        = 0;
    $rui_ch_roumu_sagaku = 0;
} else {
    $rui_c_roumu         = $rui_c_roumu - $rui_b_roumu;
    if ($yyyymm >= 200912 && $yyyymm <= 201003) {
        $rui_c_roumu = $rui_c_roumu + 1227429;
    }
    if ($yyyymm >= 201001) {
        $rui_c_roumu = $rui_c_roumu + $rui_c_kyu_kin;   // ���ץ������Ϳ���̣������ɸ��ˡ�
        //$rui_c_roumu = $rui_c_roumu + 151313;   // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
    }
    if ($yyyymm >= 201408 && $yyyymm <= 201503) {
        $rui_c_roumu = $rui_c_roumu + 611904;
    }
    $rui_ch_roumu        = $rui_c_roumu - $rui_ctoku_roumu_sagaku;
    $rui_ch_roumu_sagaku = $rui_ch_roumu;
    $rui_ch_roumu        = number_format(($rui_ch_roumu / $tani), $keta);
    $rui_c_roumu         = number_format(($rui_c_roumu / $tani), $keta);
}

/********** ����(��¤����) **********/
    ///// ����
    // ���ʴ����ʥ��ץ�˻���Ū������Ƥ��뤿���
$query = sprintf("select sum(orign_kin) from act_allo_history where pl_bs_ym=%d and actcod>=7501 and actcod<=8000 and orign_id=580", $yyyymm);
if (getUniResult($query, $b_expense) < 1) {
    $b_expense            = 0;      // ��������
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�������¤����'", $yyyymm);
if (getUniResult($query, $ctoku_expense) < 1) {
    $ctoku_expense        = 0;      // ��������
    $ctoku_expense_sagaku = 0;
} else {
    $ctoku_expense_sagaku = $ctoku_expense;
    $ctoku_expense        = number_format(($ctoku_expense / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ���¤����'", $yyyymm);
if (getUniResult($query, $c_expense) < 1) {
    $c_expense         = 0;         // ��������
    $ch_expense        = 0;
    $ch_expense_sagaku = 0;
} else {
    $c_expense         = $c_expense - $b_expense;     // ���ץ���¤����ݾ�����¤����
    $ch_expense        = $c_expense - $ctoku_expense_sagaku;
    $ch_expense_sagaku = $ch_expense;
    $ch_expense        = number_format(($ch_expense / $tani), $keta);
    $c_expense         = number_format(($c_expense / $tani), $keta);
}
    ///// ����
    // ���ʴ����ʥ��ץ�˻���Ū������Ƥ��뤿���
$query = sprintf("select sum(orign_kin) from act_allo_history where pl_bs_ym=%d and actcod>=7501 and actcod<=8000 and orign_id=580", $p1_ym);
if (getUniResult($query, $p1_b_expense) < 1) {
    $p1_b_expense            = 0;   // ��������
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�������¤����'", $p1_ym);
if (getUniResult($query, $p1_ctoku_expense) < 1) {
    $p1_ctoku_expense        = 0;   // ��������
    $p1_ctoku_expense_sagaku = 0;
} else {
    $p1_ctoku_expense_sagaku = $p1_ctoku_expense;
    $p1_ctoku_expense        = number_format(($p1_ctoku_expense / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ���¤����'", $p1_ym);
if (getUniResult($query, $p1_c_expense) < 1) {
    $p1_c_expense         = 0;      // ��������
    $p1_ch_expense        = 0;
    $p1_ch_expense_sagaku = 0;
} else {
    $p1_c_expense         = $p1_c_expense - $p1_b_expense;      // ���ץ���¤����ݾ�����¤����
    $p1_ch_expense        = $p1_c_expense - $p1_ctoku_expense_sagaku;
    $p1_ch_expense_sagaku = $p1_ch_expense;
    $p1_ch_expense        = number_format(($p1_ch_expense / $tani), $keta);
    $p1_c_expense         = number_format(($p1_c_expense / $tani), $keta);
}
    ///// ������
    // ���ʴ����ʥ��ץ�˻���Ū������Ƥ��뤿���
$query = sprintf("select sum(orign_kin) from act_allo_history where pl_bs_ym=%d and actcod>=7501 and actcod<=8000 and orign_id=580", $p2_ym);
if (getUniResult($query, $p2_b_expense) < 1) {
    $p2_b_expense            = 0;      // ��������
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�������¤����'", $p2_ym);
if (getUniResult($query, $p2_ctoku_expense) < 1) {
    $p2_ctoku_expense        = 0;      // ��������
    $p2_ctoku_expense_sagaku = 0;
} else {
    $p2_ctoku_expense_sagaku = $p2_ctoku_expense;
    $p2_ctoku_expense        = number_format(($p2_ctoku_expense / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ���¤����'", $p2_ym);
if (getUniResult($query, $p2_c_expense) < 1) {
    $p2_c_expense         = 0;         // ��������
    $p2_ch_expense        = 0;
    $p2_ch_expense_sagaku = 0;
} else {
    $p2_c_expense         = $p2_c_expense - $p2_b_expense;     // ���ץ���¤����ݾ�����¤����
    $p2_ch_expense        = $p2_c_expense - $p2_ctoku_expense_sagaku;
    $p2_ch_expense_sagaku = $p2_ch_expense;
    $p2_ch_expense        = number_format(($p2_ch_expense / $tani), $keta);
    $p2_c_expense         = number_format(($p2_c_expense / $tani), $keta);
}
    ///// �����߷�
    // ���ʴ����ʥ��ץ�˻���Ū������Ƥ��뤿���
$query = sprintf("select sum(orign_kin) from act_allo_history where pl_bs_ym>=%d and pl_bs_ym<=%d and actcod>=7501 and actcod<=8000 and orign_id=580", $str_ym, $yyyymm);
if (getUniResult($query, $rui_b_expense) < 1) {
    $rui_b_expense            = 0;      // ��������
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���ץ�������¤����'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_ctoku_expense) < 1) {
    $rui_ctoku_expense        = 0;      // ��������
    $rui_ctoku_expense_sagaku = 0;
} else {
    $rui_ctoku_expense_sagaku = $rui_ctoku_expense;
    $rui_ctoku_expense        = number_format(($rui_ctoku_expense / $tani), $keta);
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���ץ���¤����'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_c_expense) < 1) {
    $rui_c_expense         = 0;         // ��������
    $rui_ch_expense        = 0;
    $rui_ch_expense_sagaku = 0;
} else {
    $rui_c_expense         = $rui_c_expense - $rui_b_expense;     // ���ץ���¤����ݾ�����¤����
    $rui_ch_expense        = $rui_c_expense - $rui_ctoku_expense_sagaku;
    $rui_ch_expense_sagaku = $rui_ch_expense;
    $rui_ch_expense        = number_format(($rui_ch_expense / $tani), $keta);
    $rui_c_expense         = number_format(($rui_c_expense / $tani), $keta);
}

/********** ���������ų���ê���� **********/
    ///// ����
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ��������ê����'", $yyyymm);
if (getUniResult($query, $ctoku_endinv) < 1) {
    $ctoku_endinv        = 0;                               // ��������
    $ctoku_endinv_sagaku = 0;
} else {
    $ctoku_endinv_sagaku = $ctoku_endinv;
    $ctoku_endinv        = ($ctoku_endinv * (-1));          // ���ȿž
    $ctoku_endinv        = number_format(($ctoku_endinv / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ����ê����'", $yyyymm);
if (getUniResult($query, $c_endinv) < 1) {
    $c_endinv            = 0;                               // ��������
    $ch_endinv           = 0;
    $ch_endinv_sagaku    = 0;
} else {
    $ch_endinv           = $c_endinv - $ctoku_endinv_sagaku;
    $ch_endinv           = ($ch_endinv * (-1));
    $c_endinv            = ($c_endinv * (-1));              // ���ȿž
    $ch_endinv_sagaku    = $ch_endinv;
    $ch_endinv           = number_format(($ch_endinv / $tani), $keta);
    $c_endinv            = number_format(($c_endinv / $tani), $keta);
}
    ///// ����
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ��������ê����'", $p1_ym);
if (getUniResult($query, $p1_ctoku_endinv) < 1) {
    $p1_ctoku_endinv        = 0;                            // ��������
    $p1_ctoku_endinv_sagaku = 0;
} else {
    $p1_ctoku_endinv_sagaku = $p1_ctoku_endinv;
    $p1_ctoku_endinv        = ($p1_ctoku_endinv * (-1));    // ���ȿž
    $p1_ctoku_endinv        = number_format(($p1_ctoku_endinv / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ����ê����'", $p1_ym);
if (getUniResult($query, $p1_c_endinv) < 1) {
    $p1_c_endinv            = 0;                            // ��������
    $p1_ch_endinv           = 0;
    $p1_ch_endinv_sagaku    = 0;
} else {
    $p1_ch_endinv           = $p1_c_endinv - $p1_ctoku_endinv_sagaku;
    $p1_ch_endinv           = ($p1_ch_endinv * (-1));
    $p1_c_endinv            = ($p1_c_endinv * (-1));        // ���ȿž
    $p1_ch_endinv_sagaku    = $p1_ch_endinv;
    $p1_ch_endinv           = number_format(($p1_ch_endinv / $tani), $keta);
    $p1_c_endinv            = number_format(($p1_c_endinv / $tani), $keta);
}
    ///// ������
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ��������ê����'", $p2_ym);
if (getUniResult($query, $p2_ctoku_endinv) < 1) {
    $p2_ctoku_endinv        = 0;                            // ��������
    $p2_ctoku_endinv_sagaku = 0;
} else {
    $p2_ctoku_endinv_sagaku = $p2_ctoku_endinv;
    $p2_ctoku_endinv        = ($p2_ctoku_endinv * (-1));    // ���ȿž
    $p2_ctoku_endinv        = number_format(($p2_ctoku_endinv / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ����ê����'", $p2_ym);
if (getUniResult($query, $p2_c_endinv) < 1) {
    $p2_c_endinv            = 0;                            // ��������
    $p2_ch_endinv           = 0;
    $p2_ch_endinv_sagaku    = 0;
} else {
    $p2_ch_endinv           = $p2_c_endinv - $p2_ctoku_endinv_sagaku;
    $p2_ch_endinv           = ($p2_ch_endinv * (-1));
    $p2_c_endinv            = ($p2_c_endinv * (-1));        // ���ȿž
    $p2_ch_endinv_sagaku    = $p2_ch_endinv;
    $p2_ch_endinv           = number_format(($p2_ch_endinv / $tani), $keta);
    $p2_c_endinv            = number_format(($p2_c_endinv / $tani), $keta);
}
    ///// �����߷�
    /////   ����ê������߷פ������Ʊ��

///////// ���ʴ���ʬ�κ��۷׻���ϫ̳�����¤�����
$b_sagaku     = $b_roumu + $b_expense;
$p1_b_sagaku  = $p1_b_roumu + $p1_b_expense;
$p2_b_sagaku  = $p2_b_roumu + $p2_b_expense;
$rui_b_sagaku = $rui_b_roumu + $rui_b_expense;
/********** ��帶�� **********/
    ///// ����
    ///// ���ץ�����
$ctoku_urigen        = $ctoku_invent_sagaku + $ctoku_metarial_sagaku + $ctoku_roumu_sagaku + $ctoku_expense_sagaku - $ctoku_endinv_sagaku;
$ctoku_urigen_sagaku = $ctoku_urigen;
$ctoku_urigen        = number_format(($ctoku_urigen / $tani), $keta);
    ///// CL
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ���帶��'", $yyyymm);
if (getUniResult($query, $c_urigen) < 1) {
    $c_urigen         = 0;     // ��������
    $ch_urigen        = 0;     // ��������
    $ch_urigen_sagaku = 0;     // ��������
} else {
    $c_urigen         = $c_urigen - $b_sagaku - $sc_metarial_sagaku;    //���ץ��������̣
    if ($yyyymm == 200912) {
        $c_urigen = $c_urigen + 1227429;
    }
    if ($yyyymm >= 201001) {
        $c_urigen = $c_urigen + $c_kyu_kin;     // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ(����ɸ��)
        //$c_urigen = $c_urigen + 151313;     // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
    }
    // 2013/11/07 �ɲ� 2013ǯ10���� ��̳������ʲ����ɸ�����Ĵ��
    if ($yyyymm == 201310) {
        $c_urigen -= 1245035;
    }
    if ($yyyymm == 201311) {
        $c_urigen += 1245035;
    }
    if ($yyyymm == 201408) {
        $c_urigen += 611904;
    }
    $ch_urigen        = $c_urigen - $ctoku_urigen_sagaku;
    $ch_urigen_sagaku = $ch_urigen;
    $ch_urigen        = number_format(($ch_urigen / $tani), $keta);
    $c_urigen         = number_format(($c_urigen / $tani), $keta);
}
    ///// ����
    ///// ���ץ�����
$p1_ctoku_urigen        = $p1_ctoku_invent_sagaku + $p1_ctoku_metarial_sagaku + $p1_ctoku_roumu_sagaku + $p1_ctoku_expense_sagaku - $p1_ctoku_endinv_sagaku;
$p1_ctoku_urigen_sagaku = $p1_ctoku_urigen;
$p1_ctoku_urigen        = number_format(($p1_ctoku_urigen / $tani), $keta);
    ///// C
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ���帶��'", $p1_ym);
if (getUniResult($query, $p1_c_urigen) < 1) {
    $p1_c_urigen         = 0;     // ��������
    $p1_ch_urigen        = 0;     // ��������
    $p1_ch_urigen_sagaku = 0;     // ��������
} else {
    $p1_c_urigen         = $p1_c_urigen - $p1_b_sagaku - $p1_sc_metarial_sagaku;    //���ץ��������̣
    if ($p1_ym == 200912) {
        $p1_c_urigen = $p1_c_urigen + 1227429;
    }
    if ($p1_ym >= 201001) {
        $p1_c_urigen = $p1_c_urigen + $p1_c_kyu_kin;   // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ(����ɸ��)
        //$p1_c_urigen = $p1_c_urigen + 151313;   // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
    }
    // 2013/11/07 �ɲ� 2013ǯ10���� ��̳������ʲ����ɸ�����Ĵ��
    if ($p1_ym == 201310) {
        $p1_c_urigen -= 1245035;
    }
    if ($p1_ym == 201311) {
        $p1_c_urigen += 1245035;
    }
    if ($p1_ym == 201408) {
        $p1_c_urigen += 611904;
    }
    $p1_ch_urigen        = $p1_c_urigen - $p1_ctoku_urigen_sagaku;
    $p1_ch_urigen_sagaku = $p1_ch_urigen;
    $p1_ch_urigen        = number_format(($p1_ch_urigen / $tani), $keta);
    $p1_c_urigen         = number_format(($p1_c_urigen / $tani), $keta);
}
    ///// ������
    ///// ���ץ�����
$p2_ctoku_urigen        = $p2_ctoku_invent_sagaku + $p2_ctoku_metarial_sagaku + $p2_ctoku_roumu_sagaku + $p2_ctoku_expense_sagaku - $p2_ctoku_endinv_sagaku;
$p2_ctoku_urigen_sagaku = $p2_ctoku_urigen;
$p2_ctoku_urigen        = number_format(($p2_ctoku_urigen / $tani), $keta);
    ///// C
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ���帶��'", $p2_ym);
if (getUniResult($query, $p2_c_urigen) < 1) {
    $p2_c_urigen         = 0;     // ��������
    $p2_ch_urigen        = 0;     // ��������
    $p2_ch_urigen_sagaku = 0;     // ��������
} else {
    $p2_c_urigen         = $p2_c_urigen - $p2_b_sagaku - $p2_sc_metarial_sagaku;    //���ץ��������̣
    if ($p2_ym == 200912) {
        $p2_c_urigen = $p2_c_urigen + 1227429;
    }
    if ($p2_ym >= 201001) {
        $p2_c_urigen = $p2_c_urigen + $p2_c_kyu_kin;   // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ(����ɸ��)
        //$p2_c_urigen = $p2_c_urigen + 151313;   // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
    }
    // 2013/11/07 �ɲ� 2013ǯ10���� ��̳������ʲ����ɸ�����Ĵ��
    if ($p2_ym == 201310) {
        $p2_c_urigen -= 1245035;
    }
    if ($p2_ym == 201311) {
        $p2_c_urigen += 1245035;
    }
    if ($p2_ym == 201408) {
        $p2_c_urigen += 611904;
    }
    $p2_ch_urigen        = $p2_c_urigen - $p2_ctoku_urigen_sagaku;
    $p2_ch_urigen_sagaku = $p2_ch_urigen;
    $p2_ch_urigen        = number_format(($p2_ch_urigen / $tani), $keta);
    $p2_c_urigen         = number_format(($p2_c_urigen / $tani), $keta);
}
    ///// �����߷�
    ///// ���ץ�����
$rui_ctoku_urigen        = $rui_ctoku_invent_sagaku + $rui_ctoku_metarial_sagaku + $rui_ctoku_roumu_sagaku + $rui_ctoku_expense_sagaku - $ctoku_endinv_sagaku;
$rui_ctoku_urigen_sagaku = $rui_ctoku_urigen;
$rui_ctoku_urigen        = number_format(($rui_ctoku_urigen / $tani), $keta);
    ///// C
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���ץ���帶��'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_c_urigen) < 1) {
    $rui_c_urigen         = 0;     // ��������
    $rui_ch_urigen        = 0;     // ��������
    $rui_ch_urigen_sagaku = 0;     // ��������
} else {
    $rui_c_urigen         = $rui_c_urigen - $rui_b_sagaku - $rui_sc_metarial_sagaku;    //���ץ��������̣
    if ($yyyymm >= 200912 && $yyyymm <= 201003) {
        $rui_c_urigen = $rui_c_urigen + 1227429;
    }
    if ($yyyymm >= 201001) {
        $rui_c_urigen = $rui_c_urigen + $rui_c_kyu_kin;     // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
        //$rui_c_urigen = $rui_c_urigen + 151313;    // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
    }
    // 2013/11/07 �ɲ� 2013ǯ10���� ��̳������ʲ����ɸ�����Ĵ��
    if ($yyyymm >= 201310 && $yyyymm <= 201403) {
        $rui_c_urigen -= 1245035;
    }
    if ($yyyymm >= 201311 && $yyyymm <= 201403) {
        $rui_c_urigen += 1245035;
    }
    if ($yyyymm >= 201408 && $yyyymm <= 201503) {
        $rui_c_urigen = $rui_c_urigen + 611904;
    }
    $rui_ch_urigen        = $rui_c_urigen - $rui_ctoku_urigen_sagaku;
    $rui_ch_urigen_sagaku = $rui_ch_urigen;
    $rui_ch_urigen        = number_format(($rui_ch_urigen / $tani), $keta);
    $rui_c_urigen         = number_format(($rui_c_urigen / $tani), $keta);
}

/********** ��������� **********/
    ///// ���ץ�����
$p2_ctoku_gross_profit         = $p2_ctoku_uri_sagaku - $p2_ctoku_urigen_sagaku;
$p2_ctoku_gross_profit_sagaku  = $p2_ctoku_gross_profit;
$p2_ctoku_gross_profit         = number_format(($p2_ctoku_gross_profit / $tani), $keta);

$p1_ctoku_gross_profit         = $p1_ctoku_uri_sagaku - $p1_ctoku_urigen_sagaku;
$p1_ctoku_gross_profit_sagaku  = $p1_ctoku_gross_profit;
$p1_ctoku_gross_profit         = number_format(($p1_ctoku_gross_profit / $tani), $keta);

$ctoku_gross_profit            = $ctoku_uri_sagaku - $ctoku_urigen_sagaku;
$ctoku_gross_profit_sagaku     = $ctoku_gross_profit;
$ctoku_gross_profit            = number_format(($ctoku_gross_profit / $tani), $keta);

$rui_ctoku_gross_profit        = $rui_ctoku_uri_sagaku - $rui_ctoku_urigen_sagaku;
$rui_ctoku_gross_profit_sagaku = $rui_ctoku_gross_profit;
$rui_ctoku_gross_profit        = number_format(($rui_ctoku_gross_profit / $tani), $keta);
    ///// ����
if ( $yyyymm >= 200910) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�������'", $yyyymm);
    if (getUniResult($query, $c_gross_profit) < 1) {
        $c_gross_profit         = 0;     // ��������
        $ch_gross_profit        = 0;     // ��������
        $ch_gross_profit_sagaku = 0;     // ��������
    } else {
        $c_gross_profit         = $c_gross_profit + $b_sagaku - $sc_uri_sagaku + $sc_metarial_sagaku;    //���ץ��������̣
        if ($yyyymm == 200912) {
            $c_gross_profit = $c_gross_profit - 1227429;
        }
        if ($yyyymm >= 201001) {
            $c_gross_profit = $c_gross_profit - $c_kyu_kin;     // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ(����ɸ��)
            //$c_gross_profit = $c_gross_profit - 151313;     // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
        }
        // 2013/11/07 �ɲ� 2013ǯ10���� ��̳������ʲ����ɸ�����Ĵ��
        if ($yyyymm == 201310) {
            $c_gross_profit += 1245035;
        }
        if ($yyyymm == 201311) {
            $c_gross_profit -= 1245035;
        }
        if ($yyyymm == 201408) {
            $c_gross_profit -= 611904;
        }
        $ch_gross_profit        = $c_gross_profit - $ctoku_gross_profit_sagaku;
        $ch_gross_profit_sagaku = $ch_gross_profit;
        $ch_gross_profit        = number_format(($ch_gross_profit / $tani), $keta);
        $c_gross_profit         = number_format(($c_gross_profit / $tani), $keta);
    }
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�������'", $yyyymm);
    if (getUniResult($query, $c_gross_profit) < 1) {
        $c_gross_profit         = 0;     // ��������
        $ch_gross_profit        = 0;     // ��������
        $ch_gross_profit_sagaku = 0;     // ��������
    } else {
        $c_gross_profit         = $c_gross_profit + $b_sagaku;
        $ch_gross_profit        = $c_gross_profit - $ctoku_gross_profit_sagaku;
        $ch_gross_profit_sagaku = $ch_gross_profit;
        $ch_gross_profit        = number_format(($ch_gross_profit / $tani), $keta);
        $c_gross_profit         = number_format(($c_gross_profit / $tani), $keta);
    }
}
    ///// ����
if ( $yyyymm >= 200911) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�������'", $p1_ym);
    if (getUniResult($query, $p1_c_gross_profit) < 1) {
        $p1_c_gross_profit         = 0;     // ��������
        $p1_ch_gross_profit        = 0;     // ��������
        $p1_ch_gross_profit_sagaku = 0;     // ��������
    } else {
        $p1_c_gross_profit         = $p1_c_gross_profit + $p1_b_sagaku - $p1_sc_uri_sagaku + $p1_sc_metarial_sagaku;    //���ץ��������̣
        if ($p1_ym == 200912) {
            $p1_c_gross_profit = $p1_c_gross_profit - 1227429;
        }
        if ($p1_ym >= 201001) {
            $p1_c_gross_profit = $p1_c_gross_profit - $p1_c_kyu_kin;   // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ(����ɸ��)
            //$p1_c_gross_profit = $p1_c_gross_profit - 151313;   // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
        }
        // 2013/11/07 �ɲ� 2013ǯ10���� ��̳������ʲ����ɸ�����Ĵ��
        if ($p1_ym == 201310) {
            $p1_c_gross_profit += 1245035;
        }
        if ($p1_ym == 201311) {
            $p1_c_gross_profit -= 1245035;
        }
        if ($p1_ym == 201408) {
            $p1_c_gross_profit -= 611904;
        }
        $p1_ch_gross_profit        = $p1_c_gross_profit - $p1_ctoku_gross_profit_sagaku;
        $p1_ch_gross_profit_sagaku = $p1_ch_gross_profit;
        $p1_ch_gross_profit        = number_format(($p1_ch_gross_profit / $tani), $keta);
        $p1_c_gross_profit         = number_format(($p1_c_gross_profit / $tani), $keta);
    }
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�������'", $p1_ym);
    if (getUniResult($query, $p1_c_gross_profit) < 1) {
        $p1_c_gross_profit         = 0;     // ��������
        $p1_ch_gross_profit        = 0;     // ��������
        $p1_ch_gross_profit_sagaku = 0;     // ��������
    } else {
        $p1_c_gross_profit         = $p1_c_gross_profit + $p1_b_sagaku;
        $p1_ch_gross_profit        = $p1_c_gross_profit - $p1_ctoku_gross_profit_sagaku;
        $p1_ch_gross_profit_sagaku = $p1_ch_gross_profit;
        $p1_ch_gross_profit        = number_format(($p1_ch_gross_profit / $tani), $keta);
        $p1_c_gross_profit         = number_format(($p1_c_gross_profit / $tani), $keta);
    }
}
    ///// ������
if ( $yyyymm >= 200912) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�������'", $p2_ym);
    if (getUniResult($query, $p2_c_gross_profit) < 1) {
        $p2_c_gross_profit         = 0;     // ��������
        $p2_ch_gross_profit        = 0;     // ��������
        $p2_ch_gross_profit_sagaku = 0;     // ��������
    } else {
        $p2_c_gross_profit         = $p2_c_gross_profit + $p2_b_sagaku - $p2_sc_uri_sagaku + $p2_sc_metarial_sagaku;    //���ץ��������̣
        if ($p2_ym == 200912) {
            $p2_c_gross_profit = $p2_c_gross_profit - 1227429;
        }
        if ($p2_ym >= 201001) {
            $p2_c_gross_profit = $p2_c_gross_profit - $p2_c_kyu_kin;   // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ(���٤�ɸ��)
            //$p2_c_gross_profit = $p2_c_gross_profit - 151313;   // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
        }
        // 2013/11/07 �ɲ� 2013ǯ10���� ��̳������ʲ����ɸ�����Ĵ��
        if ($p2_ym == 201310) {
            $p2_c_gross_profit += 1245035;
        }
        if ($p2_ym == 201311) {
            $p2_c_gross_profit -= 1245035;
        }
        if ($p2_ym == 201408) {
            $p2_c_gross_profit -= 611904;
        }
        $p2_ch_gross_profit        = $p2_c_gross_profit - $p2_ctoku_gross_profit_sagaku;
        $p2_ch_gross_profit_sagaku = $p2_ch_gross_profit;
        $p2_ch_gross_profit        = number_format(($p2_ch_gross_profit / $tani), $keta);
        $p2_c_gross_profit         = number_format(($p2_c_gross_profit / $tani), $keta);
    }
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�������'", $p2_ym);
    if (getUniResult($query, $p2_c_gross_profit) < 1) {
        $p2_c_gross_profit         = 0;     // ��������
        $p2_ch_gross_profit        = 0;     // ��������
        $p2_ch_gross_profit_sagaku = 0;     // ��������
    } else {
        $p2_c_gross_profit         = $p2_c_gross_profit + $p2_b_sagaku - $p2_sc_uri_sagaku + $p2_sc_metarial_sagaku;    //���ץ��������̣
        $p2_ch_gross_profit        = $p2_c_gross_profit - $p2_ctoku_gross_profit_sagaku;
        $p2_ch_gross_profit_sagaku = $p2_ch_gross_profit;
        $p2_ch_gross_profit        = number_format(($p2_ch_gross_profit / $tani), $keta);
        $p2_c_gross_profit         = number_format(($p2_c_gross_profit / $tani), $keta);
    }
}
    ///// �����߷�
if ( $yyyymm >= 200910) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���ץ�������'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_c_gross_profit) < 1) {
        $rui_c_gross_profit         = 0;    // ��������
        $rui_ch_gross_profit        = 0;    // ��������
        $rui_ch_gross_profit_sagaku = 0;    // ��������
    } else {
        $rui_c_gross_profit         = $rui_c_gross_profit + $rui_b_sagaku - $rui_sc_uri_sagaku + $rui_sc_metarial_sagaku;    //���ץ��������̣
        if ($yyyymm >= 200912 && $yyyymm <= 201003) {
            $rui_c_gross_profit = $rui_c_gross_profit - 1227429;
        }
        if ($yyyymm >= 201001) {
            $rui_c_gross_profit = $rui_c_gross_profit - $rui_c_kyu_kin;     // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
            //$rui_c_gross_profit = $rui_c_gross_profit - 151313;     // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
        }
        // 2013/11/07 �ɲ� 2013ǯ10���� ��̳������ʲ����ɸ�����Ĵ��
        if ($yyyymm >= 201310 && $yyyymm <= 201403) {
            $rui_c_gross_profit += 1245035;
        }
        if ($yyyymm >= 201311 && $yyyymm <= 201403) {
            $rui_c_gross_profit -= 1245035;
        }
        if ($yyyymm >= 201408 && $yyyymm <= 201503) {
            $rui_c_gross_profit = $rui_c_gross_profit - 611904;
        }
        $rui_ch_gross_profit        = $rui_c_gross_profit - $rui_ctoku_gross_profit_sagaku;
        $rui_ch_gross_profit_sagaku = $rui_ch_gross_profit;
        $rui_ch_gross_profit        = number_format(($rui_ch_gross_profit / $tani), $keta);
        $rui_c_gross_profit         = number_format(($rui_c_gross_profit / $tani), $keta);
    }
} else {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���ץ�������'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_c_gross_profit) < 1) {
        $rui_c_gross_profit         = 0;    // ��������
        $rui_ch_gross_profit        = 0;    // ��������
        $rui_ch_gross_profit_sagaku = 0;    // ��������
    } else {
        $rui_c_gross_profit         = $rui_c_gross_profit + $rui_b_sagaku;
        $rui_ch_gross_profit        = $rui_c_gross_profit - $rui_ctoku_gross_profit_sagaku;
        $rui_ch_gross_profit_sagaku = $rui_ch_gross_profit;
        $rui_ch_gross_profit        = number_format(($rui_ch_gross_profit / $tani), $keta);
        $rui_c_gross_profit         = number_format(($rui_c_gross_profit / $tani), $keta);
    }
}

/********** �δ���οͷ��� **********/
    ///// ����
    // ���ʴ����ʥ��ץ�˻���Ū������Ƥ��뤿���
    // ����7��̤ʧ����Ϳʬ
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ɿͷ���'", $yyyymm);
if (getUniResult($query, $b_han_jin_sagaku) < 1) {
    $b_han_jin_sagaku = 0;      // ��������
}
$query = sprintf("select sum(orign_kin) from act_allo_history where pl_bs_ym=%d and actcod>=8101 and orign_id=670", $yyyymm);
if (getUniResult($query, $b_han_jin) < 1) {
    $b_han_jin        = 0;      // ���ץ麹�۷׻���
} else {
    $b_han_jin        = $b_han_jin + $b_han_jin_sagaku;
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�����ͷ���'", $yyyymm);
if (getUniResult($query, $ctoku_han_jin) < 1) {
    $ctoku_han_jin        = 0;  // ��������
    $ctoku_han_jin_sagaku = 0;
} else {
    $ctoku_han_jin_sagaku = $ctoku_han_jin;
    $ctoku_han_jin        = number_format(($ctoku_han_jin / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ龦�ɼҰ���ʬ��Ϳ'", $yyyymm);
if (getUniResult($query, $c_allo_kin) < 1) {
    $c_allo_kin           = 0;  // ��������
} else {
    //$c_han_jin = number_format(($c_han_jin / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�ͷ���'", $yyyymm);
if (getUniResult($query, $c_han_jin) < 1) {
    $c_han_jin         = 0;     // ��������
    $ch_han_jin        = 0;     // ��������
    $ch_han_jin_sagaku = 0;     // ��������
} else {
    $c_han_jin         = $c_han_jin - $b_han_jin - $c_allo_kin;
    $ch_han_jin        = $c_han_jin - $ctoku_han_jin_sagaku;
    $ch_han_jin_sagaku = $ch_han_jin;
    $ch_han_jin        = number_format(($ch_han_jin / $tani), $keta);
    $c_han_jin         = number_format(($c_han_jin / $tani), $keta);
}
    ///// ����
    // ���ʴ����ʥ��ץ�˻���Ū������Ƥ��뤿���
    // ����7��̤ʧ����Ϳʬ
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ɿͷ���'", $p1_ym);
if (getUniResult($query, $p1_b_han_jin_sagaku) < 1) {
    $p1_b_han_jin_sagaku = 0;   // ��������
}
$query = sprintf("select sum(orign_kin) from act_allo_history where pl_bs_ym=%d and actcod>=8101 and orign_id=670", $p1_ym);
if (getUniResult($query, $p1_b_han_jin) < 1) {
    $p1_b_han_jin = 0;          // ���ץ麹�۷׻���
} else {
    $p1_b_han_jin = $p1_b_han_jin + $p1_b_han_jin_sagaku;
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�����ͷ���'", $p1_ym);
if (getUniResult($query, $p1_ctoku_han_jin) < 1) {
    $p1_ctoku_han_jin        = 0;   // ��������
    $p1_ctoku_han_jin_sagaku = 0;
} else {
    $p1_ctoku_han_jin_sagaku = $p1_ctoku_han_jin;
    $p1_ctoku_han_jin        = number_format(($p1_ctoku_han_jin / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ龦�ɼҰ���ʬ��Ϳ'", $p1_ym);
if (getUniResult($query, $p1_c_allo_kin) < 1) {
    $p1_c_allo_kin = 0;             // ��������
} else {
    //$c_han_jin = number_format(($c_han_jin / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�ͷ���'", $p1_ym);
if (getUniResult($query, $p1_c_han_jin) < 1) {
    $p1_c_han_jin         = 0;      // ��������
    $p1_ch_han_jin        = 0;      // ��������
    $p1_ch_han_jin_sagaku = 0;      // ��������
} else {
    $p1_c_han_jin         = $p1_c_han_jin - $p1_b_han_jin - $p1_c_allo_kin;
    $p1_ch_han_jin        = $p1_c_han_jin - $p1_ctoku_han_jin_sagaku;
    $p1_ch_han_jin_sagaku = $p1_ch_han_jin;
    $p1_ch_han_jin        = number_format(($p1_ch_han_jin / $tani), $keta);
    $p1_c_han_jin         = number_format(($p1_c_han_jin / $tani), $keta);
}
    ///// ������
    // ���ʴ����ʥ��ץ�˻���Ū������Ƥ��뤿���
    // ����7��̤ʧ����Ϳʬ
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ɿͷ���'", $p2_ym);
if (getUniResult($query, $p2_b_han_jin_sagaku) < 1) {
    $p2_b_han_jin_sagaku = 0;       // ��������
}
$query = sprintf("select sum(orign_kin) from act_allo_history where pl_bs_ym=%d and actcod>=8101 and orign_id=670", $p2_ym);
if (getUniResult($query, $p2_b_han_jin) < 1) {
    $p2_b_han_jin = 0;              // ���ץ麹�۷׻���
} else {
    $p2_b_han_jin = $p2_b_han_jin + $p2_b_han_jin_sagaku;
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�����ͷ���'", $p2_ym);
if (getUniResult($query, $p2_ctoku_han_jin) < 1) {
    $p2_ctoku_han_jin        = 0;   // ��������
    $p2_ctoku_han_jin_sagaku = 0;
} else {
    $p2_ctoku_han_jin_sagaku = $p2_ctoku_han_jin;
    $p2_ctoku_han_jin        = number_format(($p2_ctoku_han_jin / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ龦�ɼҰ���ʬ��Ϳ'", $p2_ym);
if (getUniResult($query, $p2_c_allo_kin) < 1) {
    $p2_c_allo_kin = 0;             // ��������
} else {
    //$c_han_jin = number_format(($c_han_jin / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�ͷ���'", $p2_ym);
if (getUniResult($query, $p2_c_han_jin) < 1) {
    $p2_c_han_jin         = 0;      // ��������
    $p2_ch_han_jin        = 0;      // ��������
    $p2_ch_han_jin_sagaku = 0;      // ��������
} else {
    $p2_c_han_jin         = $p2_c_han_jin - $p2_b_han_jin - $p2_c_allo_kin;
    $p2_ch_han_jin        = $p2_c_han_jin - $p2_ctoku_han_jin_sagaku;
    $p2_ch_han_jin_sagaku = $p2_ch_han_jin;
    $p2_ch_han_jin        = number_format(($p2_ch_han_jin / $tani), $keta);
    $p2_c_han_jin         = number_format(($p2_c_han_jin / $tani), $keta);
}
    ///// �����߷�
    // ���ʴ����ʥ��ץ�˻���Ū������Ƥ��뤿���
    // ����7��̤ʧ����Ϳʬ
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���ɿͷ���'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_b_han_jin_sagaku) < 1) {
    $rui_b_han_jin_sagaku = 0;      // ��������
}
$query = sprintf("select sum(orign_kin) from act_allo_history where pl_bs_ym>=%d and pl_bs_ym<=%d and actcod>=8101 and orign_id=670", $str_ym, $yyyymm);
if (getUniResult($query, $rui_b_han_jin) < 1) {
    $rui_b_han_jin = 0;             // ���ץ麹�۷׻���
} else {
    // ����7��̤ʧ����;ʬ�ɲ� �ƥ�����
    $rui_b_han_jin = $rui_b_han_jin + $rui_b_han_jin_sagaku;
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���ץ�����ͷ���'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_ctoku_han_jin) < 1) {
    $rui_ctoku_han_jin        = 0;  // ��������
    $rui_ctoku_han_jin_sagaku = 0;
} else {
    $rui_ctoku_han_jin_sagaku = $rui_ctoku_han_jin;
    $rui_ctoku_han_jin        = number_format(($rui_ctoku_han_jin / $tani), $keta);
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���ץ龦�ɼҰ���ʬ��Ϳ'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_c_allo_kin) < 1) {
    $rui_c_allo_kin = 0;            // ��������
} else {
    //$c_han_jin = number_format(($c_han_jin / $tani), $keta);
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���ץ�ͷ���'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_c_han_jin) < 1) {
    $rui_c_han_jin         = 0;     // ��������
    $rui_ch_han_jin        = 0;     // ��������
    $rui_ch_han_jin_sagaku = 0;     // ��������
} else {
    $rui_c_han_jin         = $rui_c_han_jin - $rui_b_han_jin - $rui_c_allo_kin;
    $rui_ch_han_jin        = $rui_c_han_jin - $rui_ctoku_han_jin_sagaku;
    $rui_ch_han_jin_sagaku = $rui_ch_han_jin;
    $rui_ch_han_jin        = number_format(($rui_ch_han_jin / $tani), $keta);
    $rui_c_han_jin         = number_format(($rui_c_han_jin / $tani), $keta);
}

/********** �δ���η��� **********/
    ///// ����
    // ���ʴ����ʥ��ץ�˻���Ū������Ƥ��뤿���
$query = sprintf("select sum(orign_kin) from act_allo_history where pl_bs_ym=%d and actcod>=7501 and actcod<=8000 and orign_id=670", $yyyymm);
if (getUniResult($query, $b_han_kei) < 1) {
    $b_han_kei =0;                  // ���ץ麹�۷׻���
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ������δ������'", $yyyymm);
if (getUniResult($query, $ctoku_han_kei) < 1) {
    $ctoku_han_kei        = 0;      // ��������
    $ctoku_han_kei_sagaku = 0;
} else {
    $ctoku_han_kei_sagaku = $ctoku_han_kei;
    $ctoku_han_kei        = number_format(($ctoku_han_kei / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ����'", $yyyymm);
if (getUniResult($query, $c_han_kei) < 1) {
    $c_han_kei         = 0;         // ��������
    $ch_han_kei        = 0;         // ��������
    $ch_han_kei_sagaku = 0;         // ��������
} else {
    $c_han_kei         = $c_han_kei - $b_han_kei;
    $ch_han_kei        = $c_han_kei - $ctoku_han_kei_sagaku;
    $ch_han_kei_sagaku = $ch_han_kei;
    $ch_han_kei        = number_format(($ch_han_kei / $tani), $keta);
    $c_han_kei         = number_format(($c_han_kei / $tani), $keta);
}
    ///// ����
    // ���ʴ����ʥ��ץ�˻���Ū������Ƥ��뤿���
$query = sprintf("select sum(orign_kin) from act_allo_history where pl_bs_ym=%d and actcod>=7501 and actcod<=8000 and orign_id=670", $p1_ym);
if (getUniResult($query, $p1_b_han_kei) < 1) {
    $p1_b_han_kei = 0;              // ���ץ麹�۷׻���
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ������δ������'", $p1_ym);
if (getUniResult($query, $p1_ctoku_han_kei) < 1) {
    $p1_ctoku_han_kei        = 0;   // ��������
    $p1_ctoku_han_kei_sagaku = 0;
} else {
    $p1_ctoku_han_kei_sagaku = $p1_ctoku_han_kei;
    $p1_ctoku_han_kei        = number_format(($p1_ctoku_han_kei / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ����'", $p1_ym);
if (getUniResult($query, $p1_c_han_kei) < 1) {
    $p1_c_han_kei         = 0;     // ��������
    $p1_ch_han_kei        = 0;     // ��������
    $p1_ch_han_kei_sagaku = 0;     // ��������
} else {
    $p1_c_han_kei         = $p1_c_han_kei - $p1_b_han_kei;
    $p1_ch_han_kei        = $p1_c_han_kei - $p1_ctoku_han_kei_sagaku;
    $p1_ch_han_kei_sagaku = $p1_ch_han_kei;
    $p1_ch_han_kei        = number_format(($p1_ch_han_kei / $tani), $keta);
    $p1_c_han_kei         = number_format(($p1_c_han_kei / $tani), $keta);
}
    ///// ������
    // ���ʴ����ʥ��ץ�˻���Ū������Ƥ��뤿���
$query = sprintf("select sum(orign_kin) from act_allo_history where pl_bs_ym=%d and actcod>=7501 and actcod<=8000 and orign_id=670", $p2_ym);
if (getUniResult($query, $p2_b_han_kei) < 1) {
    $p2_b_han_kei = 0;             // ���ץ麹�۷׻���
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ������δ������'", $p2_ym);
if (getUniResult($query, $p2_ctoku_han_kei) < 1) {
    $p2_ctoku_han_kei        = 0;  // ��������
    $p2_ctoku_han_kei_sagaku = 0;
} else {
    $p2_ctoku_han_kei_sagaku = $p2_ctoku_han_kei;
    $p2_ctoku_han_kei = number_format(($p2_ctoku_han_kei / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ����'", $p2_ym);
if (getUniResult($query, $p2_c_han_kei) < 1) {
    $p2_c_han_kei         = 0;     // ��������
    $p2_ch_han_kei        = 0;     // ��������
    $p2_ch_han_kei_sagaku = 0;     // ��������
} else {
    $p2_c_han_kei         = $p2_c_han_kei - $p2_b_han_kei;
    $p2_ch_han_kei        = $p2_c_han_kei - $p2_ctoku_han_kei_sagaku;
    $p2_ch_han_kei_sagaku = $p2_ch_han_kei;
    $p2_ch_han_kei        = number_format(($p2_ch_han_kei / $tani), $keta);
    $p2_c_han_kei         = number_format(($p2_c_han_kei / $tani), $keta);
}
    ///// �����߷�
    // ���ʴ����ʥ��ץ�˻���Ū������Ƥ��뤿���
$query = sprintf("select sum(orign_kin) from act_allo_history where pl_bs_ym>=%d and pl_bs_ym<=%d and actcod>=7501 and actcod<=8000 and orign_id=670", $str_ym, $yyyymm);
if (getUniResult($query, $rui_b_han_kei) < 1) {
    $rui_b_han_kei = 0;
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���ץ������δ������'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_ctoku_han_kei) < 1) {
    $rui_ctoku_han_kei        = 0; // ��������
    $rui_ctoku_han_kei_sagaku = 0;
} else {
    $rui_ctoku_han_kei_sagaku = $rui_ctoku_han_kei;
    $rui_ctoku_han_kei        = number_format(($rui_ctoku_han_kei / $tani), $keta);
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���ץ����'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_c_han_kei) < 1) {
    $rui_c_han_kei         = 0;    // ��������
    $rui_ch_han_kei        = 0;    // ��������
    $rui_ch_han_kei_sagaku = 0;    // ��������
} else {
    $rui_c_han_kei         = $rui_c_han_kei - $rui_b_han_kei;
    $rui_ch_han_kei        = $rui_c_han_kei - $rui_ctoku_han_kei_sagaku;
    $rui_ch_han_kei_sagaku = $rui_ch_han_kei;
    $rui_ch_han_kei        = number_format(($rui_ch_han_kei / $tani), $keta);
    $rui_c_han_kei         = number_format(($rui_c_han_kei / $tani), $keta);
}

/********** �δ���ι�� **********/
    ///// ����
    ///// ���ץ�����
$ctoku_han_all        = $ctoku_han_jin_sagaku + $ctoku_han_kei_sagaku;
$ctoku_han_all_sagaku = $ctoku_han_all;
$ctoku_han_all        = number_format(($ctoku_han_all / $tani), $keta);
    ///// C
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ��δ���'", $yyyymm);
if (getUniResult($query, $c_han_all) < 1) {
    $c_han_all         = 0;     // ��������
    $ch_han_all        = 0;     // ��������
    $ch_han_all_sagaku = 0;     // ��������
} else {
    $c_han_all         = $c_han_all - $b_han_jin - $b_han_kei - $c_allo_kin;
    $ch_han_all        = $c_han_all - $ctoku_han_all_sagaku;
    $ch_han_all_sagaku = $ch_han_all;
    $ch_han_all        = number_format(($ch_han_all / $tani), $keta);
    $c_han_all         = number_format(($c_han_all / $tani), $keta);
}
    ///// ����
    ///// ���ץ�����
$p1_ctoku_han_all        = $p1_ctoku_han_jin_sagaku + $p1_ctoku_han_kei_sagaku;
$p1_ctoku_han_all_sagaku = $p1_ctoku_han_all;
$p1_ctoku_han_all        = number_format(($p1_ctoku_han_all / $tani), $keta);
    ///// C
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ��δ���'", $p1_ym);
if (getUniResult($query, $p1_c_han_all) < 1) {
    $p1_c_han_all         = 0;  // ��������
    $p1_ch_han_all        = 0;  // ��������
    $p1_ch_han_all_sagaku = 0;  // ��������
} else {
    $p1_c_han_all         = $p1_c_han_all - $p1_b_han_jin - $p1_b_han_kei - $p1_c_allo_kin;
    $p1_ch_han_all        = $p1_c_han_all - $p1_ctoku_han_all_sagaku;
    $p1_ch_han_all_sagaku = $p1_ch_han_all;
    $p1_ch_han_all        = number_format(($p1_ch_han_all / $tani), $keta);
    $p1_c_han_all         = number_format(($p1_c_han_all / $tani), $keta);
}
    ///// ������
    ///// ���ץ�����
$p2_ctoku_han_all        = $p2_ctoku_han_jin_sagaku + $p2_ctoku_han_kei_sagaku;
$p2_ctoku_han_all_sagaku = $p2_ctoku_han_all;
$p2_ctoku_han_all        = number_format(($p2_ctoku_han_all / $tani), $keta);
    ///// C
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ��δ���'", $p2_ym);
if (getUniResult($query, $p2_c_han_all) < 1) {
    $p2_c_han_all         = 0;  // ��������
    $p2_ch_han_all        = 0;  // ��������
    $p2_ch_han_all_sagaku = 0;  // ��������
} else {
    $p2_c_han_all         = $p2_c_han_all - $p2_b_han_jin - $p2_b_han_kei - $p2_c_allo_kin;
    $p2_ch_han_all        = $p2_c_han_all - $p2_ctoku_han_all_sagaku;
    $p2_ch_han_all_sagaku = $p2_ch_han_all;
    $p2_ch_han_all        = number_format(($p2_ch_han_all / $tani), $keta);
    $p2_c_han_all         = number_format(($p2_c_han_all / $tani), $keta);
}
    ///// �����߷�
    ///// ���ץ�����
$rui_ctoku_han_all        = $rui_ctoku_han_jin_sagaku + $rui_ctoku_han_kei_sagaku;
$rui_ctoku_han_all_sagaku = $rui_ctoku_han_all;
$rui_ctoku_han_all        = number_format(($rui_ctoku_han_all / $tani), $keta);
    ///// CL
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���ץ��δ���'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_c_han_all) < 1) {
    $rui_c_han_all         = 0;     // ��������
    $rui_ch_han_all        = 0;     // ��������
    $rui_ch_han_all_sagaku = 0;     // ��������
} else {
    $rui_c_han_all         = $rui_c_han_all - $rui_b_han_jin - $rui_b_han_kei - $rui_c_allo_kin;
    $rui_ch_han_all        = $rui_c_han_all - $rui_ctoku_han_all_sagaku;
    $rui_ch_han_all_sagaku = $rui_ch_han_all;
    $rui_ch_han_all        = number_format(($rui_ch_han_all / $tani), $keta);
    $rui_c_han_all         = number_format(($rui_c_han_all / $tani), $keta);
}

///////// ���ʴ���ʬ�κ��۷׻���ϫ̳�����¤������δ���ͷ�����δ�������
$b_sagaku     = $b_sagaku + $b_han_jin + $b_han_kei;
$p1_b_sagaku  = $p1_b_sagaku + $p1_b_han_jin + $p1_b_han_kei;
$p2_b_sagaku  = $p2_b_sagaku + $p2_b_han_jin + $p2_b_han_kei;
$rui_b_sagaku = $rui_b_sagaku + $rui_b_han_jin + $rui_b_han_kei;
/********** �Ķ����� **********/
    ///// ���ץ�����
$p2_ctoku_ope_profit         = $p2_ctoku_gross_profit_sagaku - $p2_ctoku_han_all_sagaku;
$p2_ctoku_ope_profit_sagaku  = $p2_ctoku_ope_profit;
$p2_ctoku_ope_profit         = number_format(($p2_ctoku_ope_profit / $tani), $keta);

$p1_ctoku_ope_profit         = $p1_ctoku_gross_profit_sagaku - $p1_ctoku_han_all_sagaku;
$p1_ctoku_ope_profit_sagaku  = $p1_ctoku_ope_profit;
$p1_ctoku_ope_profit         = number_format(($p1_ctoku_ope_profit / $tani), $keta);

$ctoku_ope_profit            = $ctoku_gross_profit_sagaku - $ctoku_han_all_sagaku;
$ctoku_ope_profit_sagaku     = $ctoku_ope_profit;
$ctoku_ope_profit            = number_format(($ctoku_ope_profit / $tani), $keta);

$rui_ctoku_ope_profit        = $rui_ctoku_gross_profit_sagaku - $rui_ctoku_han_all_sagaku;
$rui_ctoku_ope_profit_sagaku = $rui_ctoku_ope_profit;
$rui_ctoku_ope_profit        = number_format(($rui_ctoku_ope_profit / $tani), $keta);

    ///// ����
if ( $yyyymm >= 200910) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�Ķ�����'", $yyyymm);
    if (getUniResult($query, $c_ope_profit) < 1) {
        $c_ope_profit         = 0;      // ��������
        $ch_ope_profit        = 0;      // ��������
        $ch_ope_profit_sagaku = 0;      // ��������
        $c_ope_profit_temp    = 0;
    } else {
        $c_ope_profit         = $c_ope_profit + $b_sagaku + $c_allo_kin - $sc_uri_sagaku + $sc_metarial_sagaku;    //���ץ��������̣
        if ($yyyymm == 200912) {
            $c_ope_profit = $c_ope_profit - 1227429;
        }
        if ($yyyymm >= 201001) {
            $c_ope_profit = $c_ope_profit - $c_kyu_kin; // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
            //$c_ope_profit = $c_ope_profit - 151313; // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
        }
        // 2013/11/07 �ɲ� 2013ǯ10���� ��̳������ʲ����ɸ�����Ĵ��
        if ($yyyymm == 201310) {
            $c_ope_profit += 1245035;
        }
        if ($yyyymm == 201311) {
            $c_ope_profit -= 1245035;
        }
        if ($yyyymm == 201408) {
            $c_ope_profit -=611904;
        }
        $c_ope_profit_temp    = $c_ope_profit;
        $ch_ope_profit        = $c_ope_profit - $ctoku_ope_profit_sagaku;
        $ch_ope_profit_sagaku = $ch_ope_profit;
        $ch_ope_profit        = number_format(($ch_ope_profit / $tani), $keta);
        $c_ope_profit         = number_format(($c_ope_profit / $tani), $keta);
    }
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�Ķ�����'", $yyyymm);
    if (getUniResult($query, $c_ope_profit) < 1) {
        $c_ope_profit         = 0;      // ��������
        $ch_ope_profit        = 0;      // ��������
        $ch_ope_profit_sagaku = 0;      // ��������
        $c_ope_profit_temp    = 0;
    } else {
        $c_ope_profit         = $c_ope_profit + $b_sagaku + $c_allo_kin;
        $c_ope_profit_temp    = $c_ope_profit;
        $ch_ope_profit        = $c_ope_profit - $ctoku_ope_profit_sagaku;
        $ch_ope_profit_sagaku = $ch_ope_profit;
        $ch_ope_profit        = number_format(($ch_ope_profit / $tani), $keta);
        $c_ope_profit         = number_format(($c_ope_profit / $tani), $keta);
    }
}
    ///// ����
if ( $yyyymm >= 200911) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�Ķ�����'", $p1_ym);
    if (getUniResult($query, $p1_c_ope_profit) < 1) {
        $p1_c_ope_profit         = 0;   // ��������
        $p1_ch_ope_profit        = 0;   // ��������
        $p1_ch_ope_profit_sagaku = 0;   // ��������
        $p1_c_ope_profit_temp    = 0;
    } else {
        $p1_c_ope_profit         = $p1_c_ope_profit + $p1_b_sagaku + $p1_c_allo_kin - $p1_sc_uri_sagaku + $p1_sc_metarial_sagaku;    //���ץ��������̣
        if ($p1_ym == 200912) {
            $p1_c_ope_profit = $p1_c_ope_profit - 1227429;
        }
        if ($p1_ym >= 201001) {
            $p1_c_ope_profit = $p1_c_ope_profit - $p1_c_kyu_kin;   // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
            //$p1_c_ope_profit = $p1_c_ope_profit - 151313;   // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
        }
        // 2013/11/07 �ɲ� 2013ǯ10���� ��̳������ʲ����ɸ�����Ĵ��
        if ($p1_ym == 201310) {
            $p1_c_ope_profit += 1245035;
        }
        if ($p1_ym == 201311) {
            $p1_c_ope_profit -= 1245035;
        }
        if ($p1_ym == 201408) {
            $p1_c_ope_profit -=611904;
        }
        $p1_c_ope_profit_temp    = $p1_c_ope_profit;
        $p1_ch_ope_profit        = $p1_c_ope_profit - $p1_ctoku_ope_profit_sagaku;
        $p1_ch_ope_profit_sagaku = $p1_ch_ope_profit;
        $p1_ch_ope_profit        = number_format(($p1_ch_ope_profit / $tani), $keta);
        $p1_c_ope_profit         = number_format(($p1_c_ope_profit / $tani), $keta);
    }
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�Ķ�����'", $p1_ym);
    if (getUniResult($query, $p1_c_ope_profit) < 1) {
        $p1_c_ope_profit         = 0;   // ��������
        $p1_ch_ope_profit        = 0;   // ��������
        $p1_ch_ope_profit_sagaku = 0;   // ��������
        $p1_c_ope_profit_temp    = 0;
    } else {
        $p1_c_ope_profit         = $p1_c_ope_profit + $p1_b_sagaku + $p1_c_allo_kin;
        $p1_c_ope_profit_temp    = $p1_c_ope_profit;
        $p1_ch_ope_profit        = $p1_c_ope_profit - $p1_ctoku_ope_profit_sagaku;
        $p1_ch_ope_profit_sagaku = $p1_ch_ope_profit;
        $p1_ch_ope_profit        = number_format(($p1_ch_ope_profit / $tani), $keta);
        $p1_c_ope_profit         = number_format(($p1_c_ope_profit / $tani), $keta);
    }
}
    ///// ������
if ( $yyyymm >= 200912) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�Ķ�����'", $p2_ym);
    if (getUniResult($query, $p2_c_ope_profit) < 1) {
        $p2_c_ope_profit         = 0;   // ��������
        $p2_ch_ope_profit        = 0;   // ��������
        $p2_ch_ope_profit_sagaku = 0;   // ��������
        $p2_c_ope_profit_temp     = 0;
    } else {
        $p2_c_ope_profit         = $p2_c_ope_profit + $p2_b_sagaku + $p2_c_allo_kin - $p2_sc_uri_sagaku + $p2_sc_metarial_sagaku;    //���ץ��������̣
        if ($p2_ym == 200912) {
            $p2_c_ope_profit = $p2_c_ope_profit - 1227429;
        }
        if ($p2_ym >= 201001) {
            $p2_c_ope_profit = $p2_c_ope_profit - $p2_c_kyu_kin;   // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
            //$p2_c_ope_profit = $p2_c_ope_profit - 151313;   // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
        }
        // 2013/11/07 �ɲ� 2013ǯ10���� ��̳������ʲ����ɸ�����Ĵ��
        if ($p2_ym == 201310) {
            $p2_c_ope_profit += 1245035;
        }
        if ($p2_ym == 201311) {
            $p2_c_ope_profit -= 1245035;
        }
        if ($p2_ym == 201408) {
            $p2_c_ope_profit -=611904;
        }
        $p2_c_ope_profit_temp    = $p2_c_ope_profit;
        $p2_ch_ope_profit        = $p2_c_ope_profit - $p2_ctoku_ope_profit_sagaku;
        $p2_ch_ope_profit_sagaku = $p2_ch_ope_profit;
        $p2_ch_ope_profit        = number_format(($p2_ch_ope_profit / $tani), $keta);
        $p2_c_ope_profit         = number_format(($p2_c_ope_profit / $tani), $keta);
    }
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�Ķ�����'", $p2_ym);
    if (getUniResult($query, $p2_c_ope_profit) < 1) {
        $p2_c_ope_profit         = 0;   // ��������
        $p2_ch_ope_profit        = 0;   // ��������
        $p2_ch_ope_profit_sagaku = 0;   // ��������
        $p2_c_ope_profit_temp    = 0;
    } else {
        $p2_c_ope_profit         = $p2_c_ope_profit + $p2_b_sagaku + $p2_c_allo_kin - $p2_sc_uri_sagaku;
        $p2_c_ope_profit_temp    = $p2_c_ope_profit;
        $p2_ch_ope_profit        = $p2_c_ope_profit - $p2_ctoku_ope_profit_sagaku;
        $p2_ch_ope_profit_sagaku = $p2_ch_ope_profit;
        $p2_ch_ope_profit        = number_format(($p2_ch_ope_profit / $tani), $keta);
        $p2_c_ope_profit         = number_format(($p2_c_ope_profit / $tani), $keta);
    }
}
    ///// �����߷�
if ( $yyyymm >= 200910) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���ץ�Ķ�����'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_c_ope_profit) < 1) {
        $rui_c_ope_profit         = 0;  // ��������
        $rui_ch_ope_profit        = 0;  // ��������
        $rui_ch_ope_profit_sagaku = 0;  // ��������
        $rui_c_ope_profit_temp    = 0;
    } else {
        $rui_c_ope_profit         = $rui_c_ope_profit + $rui_b_sagaku + $rui_c_allo_kin - $rui_sc_uri_sagaku + $rui_sc_metarial_sagaku;    //���ץ��������̣
        if ($yyyymm >= 200912 && $yyyymm <= 201003) {
            $rui_c_ope_profit = $rui_c_ope_profit - 1227429;
        }
        if ($yyyymm >= 201001) {
            $rui_c_ope_profit = $rui_c_ope_profit - $rui_c_kyu_kin; // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
            //$rui_c_ope_profit = $rui_c_ope_profit - 151313; // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
        }
        // 2013/11/07 �ɲ� 2013ǯ10���� ��̳������ʲ����ɸ�����Ĵ��
        if ($yyyymm >= 201310 && $yyyymm <= 201403) {
            $rui_c_ope_profit += 1245035;
        }
        if ($yyyymm >= 201311 && $yyyymm <= 201403) {
            $rui_c_ope_profit -= 1245035;
        }
        if ($yyyymm >= 201408 && $yyyymm <= 201503) {
            $rui_c_ope_profit = $rui_c_ope_profit - 611904;
        }
        $rui_c_ope_profit_temp    = $rui_c_ope_profit;
        $rui_ch_ope_profit        = $rui_c_ope_profit - $rui_ctoku_ope_profit_sagaku;
        $rui_ch_ope_profit_sagaku = $rui_ch_ope_profit;
        $rui_ch_ope_profit        = number_format(($rui_ch_ope_profit / $tani), $keta);
        $rui_c_ope_profit         = number_format(($rui_c_ope_profit / $tani), $keta);
    }
} else {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���ץ�Ķ�����'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_c_ope_profit) < 1) {
        $rui_c_ope_profit         = 0;  // ��������
        $rui_ch_ope_profit        = 0;  // ��������
        $rui_ch_ope_profit_sagaku = 0;  // ��������
        $rui_c_ope_profit_temp    = 0;
    } else {
        $rui_c_ope_profit         = $rui_c_ope_profit + $rui_b_sagaku + $rui_c_allo_kin;
        $rui_c_ope_profit_temp    = $rui_c_ope_profit;
        $rui_ch_ope_profit        = $rui_c_ope_profit - $rui_ctoku_ope_profit_sagaku;
        $rui_ch_ope_profit_sagaku = $rui_ch_ope_profit;
        $rui_ch_ope_profit        = number_format(($rui_ch_ope_profit / $tani), $keta);
        $rui_c_ope_profit         = number_format(($rui_c_ope_profit / $tani), $keta);
    }
}

/********** �Ķȳ����פζ�̳�������� **********/
    ///// ����
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ������̳���������Ʒ׻�'", $yyyymm);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ������̳��������'", $yyyymm);
}
if (getUniResult($query, $ctoku_gyoumu) < 1) {
    $ctoku_gyoumu         = 0;          // ��������
    $ctoku_gyoumu_sagaku  = 0;          // ��������
} else {
    if ($yyyymm == 200912) {
        $ctoku_gyoumu = $ctoku_gyoumu - 115715;
    }
    if ($yyyymm == 201001) {
        $ctoku_gyoumu = $ctoku_gyoumu + 58247;
    }
    $ctoku_gyoumu_sagaku = $ctoku_gyoumu;
    $ctoku_gyoumu        = number_format(($ctoku_gyoumu / $tani), $keta);
}
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ��̳���������Ʒ׻�'", $yyyymm);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ��̳��������'", $yyyymm);
}
if (getUniResult($query, $c_gyoumu) < 1) {
    $c_gyoumu         = 0;          // ��������
    $ch_gyoumu        = 0;          // ��������
    $ch_gyoumu_sagaku = 0;          // ��������
} else {
    if ($yyyymm == 200912) {
        $c_gyoumu = $c_gyoumu - 389809;
    }
    if ($yyyymm == 201001) {
        $c_gyoumu = $c_gyoumu + 315529;
    }
    $ch_gyoumu        = $c_gyoumu - $ctoku_gyoumu_sagaku;
    $ch_gyoumu_sagaku = $ch_gyoumu;
    $ch_gyoumu        = number_format(($ch_gyoumu / $tani), $keta);
    $c_gyoumu = number_format(($c_gyoumu / $tani), $keta);
}
    ///// ����
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ������̳���������Ʒ׻�'", $p1_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ������̳��������'", $p1_ym);
}
if (getUniResult($query, $p1_ctoku_gyoumu) < 1) {
    $p1_ctoku_gyoumu         = 0;          // ��������
    $p1_ctoku_gyoumu_sagaku  = 0;          // ��������
} else {
    if ($p1_ym == 200912) {
        $p1_ctoku_gyoumu = $p1_ctoku_gyoumu - 115715;
    }
    if ($p1_ym == 201001) {
        $p1_ctoku_gyoumu = $p1_ctoku_gyoumu + 58247;
    }
    $p1_ctoku_gyoumu_sagaku = $p1_ctoku_gyoumu;
    $p1_ctoku_gyoumu        = number_format(($p1_ctoku_gyoumu / $tani), $keta);
}
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ��̳���������Ʒ׻�'", $p1_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ��̳��������'", $p1_ym);
}
if (getUniResult($query, $p1_c_gyoumu) < 1) {
    $p1_c_gyoumu         = 0;          // ��������
    $p1_ch_gyoumu        = 0;          // ��������
    $p1_ch_gyoumu_sagaku = 0;          // ��������
} else {
    if ($p1_ym == 200912) {
        $p1_c_gyoumu = $p1_c_gyoumu - 389809;
    }
    if ($p1_ym == 201001) {
        $p1_c_gyoumu = $p1_c_gyoumu + 315529;
    }
    $p1_ch_gyoumu        = $p1_c_gyoumu - $p1_ctoku_gyoumu_sagaku;
    $p1_ch_gyoumu_sagaku = $p1_ch_gyoumu;
    $p1_ch_gyoumu        = number_format(($p1_ch_gyoumu / $tani), $keta);
    $p1_c_gyoumu         = number_format(($p1_c_gyoumu / $tani), $keta);
}
    ///// ������
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ������̳���������Ʒ׻�'", $p2_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ������̳��������'", $p2_ym);
}
if (getUniResult($query, $p2_ctoku_gyoumu) < 1) {
    $p2_ctoku_gyoumu         = 0;          // ��������
    $p2_ctoku_gyoumu_sagaku  = 0;          // ��������
} else {
    if ($p2_ym == 200912) {
        $p2_ctoku_gyoumu = $p2_ctoku_gyoumu - 115715;
    }
    if ($p2_ym == 201001) {
        $p2_ctoku_gyoumu = $p2_ctoku_gyoumu + 58247;
    }
    $p2_ctoku_gyoumu_sagaku = $p2_ctoku_gyoumu;
    $p2_ctoku_gyoumu        = number_format(($p2_ctoku_gyoumu / $tani), $keta);
}
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ��̳���������Ʒ׻�'", $p2_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ��̳��������'", $p2_ym);
}
if (getUniResult($query, $p2_c_gyoumu) < 1) {
    $p2_c_gyoumu         = 0;          // ��������
    $p2_ch_gyoumu        = 0;          // ��������
    $p2_ch_gyoumu_sagaku = 0;          // ��������
} else {
    if ($p2_ym == 200912) {
        $p2_c_gyoumu = $p2_c_gyoumu - 389809;
    }
    if ($p2_ym == 201001) {
        $p2_c_gyoumu = $p2_c_gyoumu + 315529;
    }
    $p2_ch_gyoumu        = $p2_c_gyoumu - $p2_ctoku_gyoumu_sagaku;
    $p2_ch_gyoumu_sagaku = $p2_ch_gyoumu;
    $p2_ch_gyoumu        = number_format(($p2_ch_gyoumu / $tani), $keta);
    $p2_c_gyoumu         = number_format(($p2_c_gyoumu / $tani), $keta);
}
    ///// �����߷�
if ($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���ץ������̳���������Ʒ׻�'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_ctoku_gyoumu) < 1) {
        $rui_ctoku_gyoumu        = 0;   // ��������
        $rui_ctoku_gyoumu_sagaku = 0;
    } else {
        $rui_ctoku_gyoumu_sagaku = $rui_ctoku_gyoumu;
        $rui_ctoku_gyoumu        = number_format(($rui_ctoku_gyoumu / $tani), $keta);
    }
} elseif ($yyyymm >= 201001 && $yyyymm <= 201003) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=200904 and pl_bs_ym<=200912 and note='���ץ������̳��������'");
    if (getUniResult($query, $rui_ctoku_gyoumu_a) < 1) {
        $rui_ctoku_gyoumu_a = 0;                          // ��������
    }
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=201001 and pl_bs_ym<=%d and note='���ץ������̳���������Ʒ׻�'", $yyyymm);
    if (getUniResult($query, $rui_ctoku_gyoumu_b) < 1) {
        $rui_ctoku_gyoumu_b = 0;                          // ��������
    }
    $rui_ctoku_gyoumu = $rui_ctoku_gyoumu_a + $rui_ctoku_gyoumu_b;
    
    if ($yyyymm >= 200912 && $yyyymm <= 201003) {
        $rui_ctoku_gyoumu = $rui_ctoku_gyoumu - 115715;
    }
    if ($yyyymm >= 201001 && $yyyymm <= 201003) {
        $rui_ctoku_gyoumu = $rui_ctoku_gyoumu + 58247;
    }
    $rui_ctoku_gyoumu_sagaku = $rui_ctoku_gyoumu;
    $rui_ctoku_gyoumu        = number_format(($rui_ctoku_gyoumu / $tani), $keta);
} else {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���ץ������̳��������'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_ctoku_gyoumu) < 1) {
        $rui_ctoku_gyoumu        = 0;   // ��������
        $rui_ctoku_gyoumu_sagaku = 0;
    } else {
        if ($yyyymm >= 200912 && $yyyymm <= 201003) {
            $rui_ctoku_gyoumu = $rui_ctoku_gyoumu - 115715;
        }
        if ($yyyymm >= 201001 && $yyyymm <= 201003) {
            $rui_ctoku_gyoumu = $rui_ctoku_gyoumu + 58247;
        }
        $rui_ctoku_gyoumu_sagaku = $rui_ctoku_gyoumu;
        $rui_ctoku_gyoumu        = number_format(($rui_ctoku_gyoumu / $tani), $keta);
    }
}
if ($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���ץ��̳���������Ʒ׻�'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_c_gyoumu) < 1) {
        $rui_c_gyoumu         = 0;      // ��������
        $rui_ch_gyoumu        = 0;      // ��������
        $rui_ch_gyoumu_sagaku = 0;      // ��������
    } else {
        $rui_ch_gyoumu        = $rui_c_gyoumu - $rui_ctoku_gyoumu_sagaku;
        $rui_ch_gyoumu_sagaku = $rui_ch_gyoumu;
        $rui_ch_gyoumu        = number_format(($rui_ch_gyoumu / $tani), $keta);
        $rui_c_gyoumu         = number_format(($rui_c_gyoumu / $tani), $keta);
    }
} elseif ($yyyymm >= 201001 && $yyyymm <= 201003) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=200904 and pl_bs_ym<=200912 and note='���ץ��̳��������'");
    if (getUniResult($query, $rui_c_gyoumu_a) < 1) {
        $rui_c_gyoumu_a = 0;                          // ��������
    }
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=201001 and pl_bs_ym<=%d and note='���ץ��̳���������Ʒ׻�'", $yyyymm);
    if (getUniResult($query, $rui_c_gyoumu_b) < 1) {
        $rui_c_gyoumu_b = 0;                          // ��������
    }
    $rui_c_gyoumu = $rui_c_gyoumu_a + $rui_c_gyoumu_b;
    
    if ($yyyymm >= 200912 && $yyyymm <= 201003) {
        $rui_c_gyoumu = $rui_c_gyoumu - 389809;
    }
    if ($yyyymm >= 201001 && $yyyymm <= 201003) {
        $rui_c_gyoumu = $rui_c_gyoumu + 315529;
    }
    $rui_ch_gyoumu        = $rui_c_gyoumu - $rui_ctoku_gyoumu_sagaku;
    $rui_ch_gyoumu_sagaku = $rui_ch_gyoumu;
    $rui_ch_gyoumu        = number_format(($rui_ch_gyoumu / $tani), $keta);
    $rui_c_gyoumu         = number_format(($rui_c_gyoumu / $tani), $keta);
} else {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���ץ��̳��������'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_c_gyoumu) < 1) {
        $rui_c_gyoumu         = 0;      // ��������
        $rui_ch_gyoumu        = 0;      // ��������
        $rui_ch_gyoumu_sagaku = 0;      // ��������
    } else {
        if ($yyyymm >= 200912 && $yyyymm <= 201003) {
            $rui_c_gyoumu = $rui_c_gyoumu - 389809;
        }
        if ($yyyymm >= 201001 && $yyyymm <= 201003) {
            $rui_c_gyoumu = $rui_c_gyoumu + 315529;
        }
        $rui_ch_gyoumu        = $rui_c_gyoumu - $rui_ctoku_gyoumu_sagaku;
        $rui_ch_gyoumu_sagaku = $rui_ch_gyoumu;
        $rui_ch_gyoumu        = number_format(($rui_ch_gyoumu / $tani), $keta);
        $rui_c_gyoumu         = number_format(($rui_c_gyoumu / $tani), $keta);
    }
}
/********** �Ķȳ����פλ������ **********/
    ///// ����
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ������������Ʒ׻�'", $yyyymm);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�����������'", $yyyymm);
}
if (getUniResult($query, $ctoku_swari) < 1) {
    $ctoku_swari         = 0;           // ��������
    $ctoku_swari_sagaku  = 0;           // ��������
} else {
    $ctoku_swari_sagaku = $ctoku_swari;
    $ctoku_swari        = number_format(($ctoku_swari / $tani), $keta);
}
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ��������Ʒ׻�'", $yyyymm);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�������'", $yyyymm);
}
if (getUniResult($query, $c_swari) < 1) {
    $c_swari         = 0;           // ��������
    $ch_swari        = 0;           // ��������
    $ch_swari_sagaku = 0;           // ��������
} else {
    $ch_swari        = $c_swari - $ctoku_swari_sagaku;
    $ch_swari_sagaku = $ch_swari;
    $ch_swari        = number_format(($ch_swari / $tani), $keta);
    $c_swari         = number_format(($c_swari / $tani), $keta);
}
    ///// ����
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ������������Ʒ׻�'", $p1_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�����������'", $p1_ym);
}
if (getUniResult($query, $p1_ctoku_swari) < 1) {
    $p1_ctoku_swari         = 0;           // ��������
    $p1_ctoku_swari_sagaku  = 0;           // ��������
} else {
    $p1_ctoku_swari_sagaku = $p1_ctoku_swari;
    $p1_ctoku_swari        = number_format(($p1_ctoku_swari / $tani), $keta);
}
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ��������Ʒ׻�'", $p1_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�������'", $p1_ym);
}
if (getUniResult($query, $p1_c_swari) < 1) {
    $p1_c_swari         = 0;           // ��������
    $p1_ch_swari        = 0;           // ��������
    $p1_ch_swari_sagaku = 0;           // ��������
} else {
    $p1_ch_swari        = $p1_c_swari - $p1_ctoku_swari_sagaku;
    $p1_ch_swari_sagaku = $p1_ch_swari;
    $p1_ch_swari        = number_format(($p1_ch_swari / $tani), $keta);
    $p1_c_swari         = number_format(($p1_c_swari / $tani), $keta);
}
    ///// ������
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ������������Ʒ׻�'", $p2_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�����������'", $p2_ym);
}
if (getUniResult($query, $p2_ctoku_swari) < 1) {
    $p2_ctoku_swari         = 0;           // ��������
    $p2_ctoku_swari_sagaku  = 0;           // ��������
} else {
    $p2_ctoku_swari_sagaku = $p2_ctoku_swari;
    $p2_ctoku_swari        = number_format(($p2_ctoku_swari / $tani), $keta);
}
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ��������Ʒ׻�'", $p2_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�������'", $p2_ym);
}
if (getUniResult($query, $p2_c_swari) < 1) {
    $p2_c_swari         = 0;           // ��������
    $p2_ch_swari        = 0;           // ��������
    $p2_ch_swari_sagaku = 0;           // ��������
} else {
    $p2_ch_swari        = $p2_c_swari - $p2_ctoku_swari_sagaku;
    $p2_ch_swari_sagaku = $p2_ch_swari;
    $p2_ch_swari        = number_format(($p2_ch_swari / $tani), $keta);
    $p2_c_swari         = number_format(($p2_c_swari / $tani), $keta);
}
    ///// �����߷�
if ($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���ץ������������Ʒ׻�'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_ctoku_swari) < 1) {
        $rui_ctoku_swari        = 0;    // ��������
        $rui_ctoku_swari_sagaku = 0;
    } else {
        $rui_ctoku_swari_sagaku = $rui_ctoku_swari;
        $rui_ctoku_swari        = number_format(($rui_ctoku_swari / $tani), $keta);
    }
} elseif ($yyyymm >= 201001 && $yyyymm <= 201003) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=200904 and pl_bs_ym<=200912 and note='���ץ�����������'");
    if (getUniResult($query, $rui_ctoku_swari_a) < 1) {
        $rui_ctoku_swari_a = 0;                          // ��������
    }
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=201001 and pl_bs_ym<=%d and note='���ץ������������Ʒ׻�'", $yyyymm);
    if (getUniResult($query, $rui_ctoku_swari_b) < 1) {
        $rui_ctoku_swari_b = 0;                          // ��������
    }
    $rui_ctoku_swari        = $rui_ctoku_swari_a + $rui_ctoku_swari_b;
    $rui_ctoku_swari_sagaku = $rui_ctoku_swari;
    $rui_ctoku_swari        = number_format(($rui_ctoku_swari / $tani), $keta);
} else {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���ץ�����������'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_ctoku_swari) < 1) {
        $rui_ctoku_swari        = 0;    // ��������
        $rui_ctoku_swari_sagaku = 0;
    } else {
        $rui_ctoku_swari_sagaku = $rui_ctoku_swari;
        $rui_ctoku_swari        = number_format(($rui_ctoku_swari / $tani), $keta);
    }
}
if ($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���ץ��������Ʒ׻�'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_c_swari) < 1) {
        $rui_c_swari         = 0;       // ��������
        $rui_ch_swari        = 0;       // ��������
        $rui_ch_swari_sagaku = 0;       // ��������
    } else {
        $rui_ch_swari        = $rui_c_swari - $rui_ctoku_swari_sagaku;
        $rui_ch_swari_sagaku = $rui_ch_swari;
        $rui_ch_swari        = number_format(($rui_ch_swari / $tani), $keta);
        $rui_c_swari         = number_format(($rui_c_swari / $tani), $keta);
    }
} elseif ($yyyymm >= 201001 && $yyyymm <= 201003) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=200904 and pl_bs_ym<=200912 and note='���ץ�������'");
    if (getUniResult($query, $rui_c_swari_a) < 1) {
        $rui_c_swari_a = 0;                          // ��������
    }
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=201001 and pl_bs_ym<=%d and note='���ץ��������Ʒ׻�'", $yyyymm);
    if (getUniResult($query, $rui_c_swari_b) < 1) {
        $rui_c_swari_b = 0;                          // ��������
    }
    $rui_c_swari         = $rui_c_swari_a + $rui_c_swari_b;
    $rui_ch_swari        = $rui_c_swari - $rui_ctoku_swari_sagaku;
    $rui_ch_swari_sagaku = $rui_ch_swari;
    $rui_ch_swari        = number_format(($rui_ch_swari / $tani), $keta);
    $rui_c_swari         = number_format(($rui_c_swari / $tani), $keta);
} else {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���ץ�������'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_c_swari) < 1) {
        $rui_c_swari         = 0;       // ��������
        $rui_ch_swari        = 0;       // ��������
        $rui_ch_swari_sagaku = 0;       // ��������
    } else {
        $rui_ch_swari        = $rui_c_swari - $rui_ctoku_swari_sagaku;
        $rui_ch_swari_sagaku = $rui_ch_swari;
        $rui_ch_swari        = number_format(($rui_ch_swari / $tani), $keta);
        $rui_c_swari         = number_format(($rui_c_swari / $tani), $keta);
    }
}
/********** �Ķȳ����פΤ���¾ **********/
    ///// ����
    // ���ʴ����ʥ��ץ�˻���Ū������Ƥ��뤿���
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ɱĶȳ����פ���¾'", $yyyymm);
if (getUniResult($query, $b_pother) < 1) {
    $b_pother = 0;                  // ��������
}
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�����Ķȳ����פ���¾�Ʒ׻�'", $yyyymm);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�����Ķȳ����פ���¾'", $yyyymm);
}
if (getUniResult($query, $ctoku_pother) < 1) {
    $ctoku_pother         = 0;          // ��������
    $ctoku_pother_sagaku  = 0;          // ��������
} else {
    if ($yyyymm == 200912) {
        $ctoku_pother = $ctoku_pother + 115715;
    }
    if ($yyyymm == 201001) {
        $ctoku_pother = $ctoku_pother - 58247;
    }
    $ctoku_pother_sagaku = $ctoku_pother;
    $ctoku_pother        = number_format(($ctoku_pother / $tani), $keta);
}
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�Ķȳ����פ���¾�Ʒ׻�'", $yyyymm);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�Ķȳ����פ���¾'", $yyyymm);
}
if (getUniResult($query, $c_pother) < 1) {
    $c_pother         = 0;          // ��������
    $ch_pother        = 0;          // ��������
    $ch_pother_sagaku = 0;          // ��������
} else {
    if ($yyyymm < 201001) {
        $c_pother = $c_pother - $b_pother;
    }
    if ($yyyymm == 200912) {
        $c_pother = $c_pother + 389809;
    }
    if ($yyyymm == 201001) {
        $c_pother = $c_pother - 315529;
    }
    $ch_pother        = $c_pother - $ctoku_pother_sagaku;
    $ch_pother_sagaku = $ch_pother;
    $ch_pother        = number_format(($ch_pother / $tani), $keta);
    $c_pother = number_format(($c_pother / $tani), $keta);
}
    ///// ����
    // ���ʴ����ʥ��ץ�˻���Ū������Ƥ��뤿���
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ɱĶȳ����פ���¾'", $p1_ym);
if (getUniResult($query, $p1_b_pother) < 1) {
    $p1_b_pother = 0;               // ��������
}
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�����Ķȳ����פ���¾�Ʒ׻�'", $p1_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�����Ķȳ����פ���¾'", $p1_ym);
}
if (getUniResult($query, $p1_ctoku_pother) < 1) {
    $p1_ctoku_pother         = 0;          // ��������
    $p1_ctoku_pother_sagaku  = 0;          // ��������
} else {
    if ($p1_ym == 200912) {
        $p1_ctoku_pother = $p1_ctoku_pother + 115715;
    }
    if ($p1_ym == 201001) {
        $p1_ctoku_pother = $p1_ctoku_pother - 58247;
    }
    $p1_ctoku_pother_sagaku = $p1_ctoku_pother;
    $p1_ctoku_pother        = number_format(($p1_ctoku_pother / $tani), $keta);
}
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�Ķȳ����פ���¾�Ʒ׻�'", $p1_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�Ķȳ����פ���¾'", $p1_ym);
}
if (getUniResult($query, $p1_c_pother) < 1) {
    $p1_c_pother         = 0;          // ��������
    $p1_ch_pother        = 0;          // ��������
    $p1_ch_pother_sagaku = 0;          // ��������
} else {
    if ($p1_ym < 201001) {
        $p1_c_pother = $p1_c_pother - $p1_b_pother;
    }
    if ($p1_ym == 200912) {
        $p1_c_pother = $p1_c_pother + 389809;
    }
    if ($p1_ym == 201001) {
        $p1_c_pother = $p1_c_pother - 315529;
    }
    $p1_ch_pother        = $p1_c_pother - $p1_ctoku_pother_sagaku;
    $p1_ch_pother_sagaku = $p1_ch_pother;
    $p1_ch_pother        = number_format(($p1_ch_pother / $tani), $keta);
    $p1_c_pother         = number_format(($p1_c_pother / $tani), $keta);
}
    ///// ������
    // ���ʴ����ʥ��ץ�˻���Ū������Ƥ��뤿���
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ɱĶȳ����פ���¾'", $p2_ym);
if (getUniResult($query, $p2_b_pother) < 1) {
    $p2_b_pother = 0;               // ��������
}
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�����Ķȳ����פ���¾�Ʒ׻�'", $p2_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�����Ķȳ����פ���¾'", $p2_ym);
}
if (getUniResult($query, $p2_ctoku_pother) < 1) {
    $p2_ctoku_pother         = 0;          // ��������
    $p2_ctoku_pother_sagaku  = 0;          // ��������
} else {
    if ($p2_ym == 200912) {
        $p2_ctoku_pother = $p2_ctoku_pother + 115715;
    }
    if ($p2_ym == 201001) {
        $p2_ctoku_pother = $p2_ctoku_pother - 58247;
    }
    $p2_ctoku_pother_sagaku = $p2_ctoku_pother;
    $p2_ctoku_pother        = number_format(($p2_ctoku_pother / $tani), $keta);
}
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�Ķȳ����פ���¾�Ʒ׻�'", $p2_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�Ķȳ����פ���¾'", $p2_ym);
}
if (getUniResult($query, $p2_c_pother) < 1) {
    $p2_c_pother         = 0;          // ��������
    $p2_ch_pother        = 0;          // ��������
    $p2_ch_pother_sagaku = 0;          // ��������
} else {
    if ($p2_ym < 201001) {
        $p2_c_pother = $p2_c_pother - $p2_b_pother;
    }
    if ($p2_ym == 200912) {
        $p2_c_pother = $p2_c_pother + 389809;
    }
    if ($p2_ym == 201001) {
        $p2_c_pother = $p2_c_pother - 315529;
    }
    $p2_ch_pother        = $p2_c_pother - $p2_ctoku_pother_sagaku;
    $p2_ch_pother_sagaku = $p2_ch_pother;
    $p2_ch_pother        = number_format(($p2_ch_pother / $tani), $keta);
    $p2_c_pother         = number_format(($p2_c_pother / $tani), $keta);
}
    ///// �����߷�
    // ���ʴ����ʥ��ץ�˻���Ū������Ƥ��뤿���
if ($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���ɱĶȳ����פ���¾�Ʒ׻�'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_b_pother) < 1) {
        $rui_b_pother = 0;                          // ��������
    } else {
        $rui_b_pother_sagaku = $rui_b_pother;
    }
} elseif ($yyyymm >= 201001 && $yyyymm <= 201003) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=200904 and pl_bs_ym<=200912 and note='���ɱĶȳ����פ���¾'");
    if (getUniResult($query, $rui_b_pother_a) < 1) {
        $rui_b_pother_a = 0;                          // ��������
    }
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=201001 and pl_bs_ym<=%d and note='���ɱĶȳ����פ���¾�Ʒ׻�'", $yyyymm);
    if (getUniResult($query, $rui_b_pother_b) < 1) {
        $rui_b_pother_b = 0;                          // ��������
    }
    $rui_b_pother = $rui_b_pother_a + $rui_b_pother_b;
} else {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���ɱĶȳ����פ���¾'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_b_pother) < 1) {
        $rui_b_pother = 0;                          // ��������
        $rui_b_sagaku = $rui_b_sagaku - $rui_b_pother;      // ���ץ麹�۷׻���
    } else {
        $rui_b_sagaku = $rui_b_sagaku - $rui_b_pother;      // ���ץ麹�۷׻���
    }
}
if ($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���ץ�����Ķȳ����פ���¾�Ʒ׻�'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_ctoku_pother) < 1) {
        $rui_ctoku_pother        = 0;   // ��������
        $rui_ctoku_pother_sagaku = 0;
    } else {
        $rui_ctoku_pother_sagaku = $rui_ctoku_pother;
        $rui_ctoku_pother        = number_format(($rui_ctoku_pother / $tani), $keta);
    }
} elseif ($yyyymm >= 201001 && $yyyymm <= 201003) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=200904 and pl_bs_ym<=200912 and note='���ץ�����Ķȳ����פ���¾'");
    if (getUniResult($query, $rui_ctoku_pother_a) < 1) {
        $rui_ctoku_pother_a = 0;                          // ��������
    }
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=201001 and pl_bs_ym<=%d and note='���ץ�����Ķȳ����פ���¾�Ʒ׻�'", $yyyymm);
    if (getUniResult($query, $rui_ctoku_pother_b) < 1) {
        $rui_ctoku_pother_b = 0;                          // ��������
    }
    $rui_ctoku_pother = $rui_ctoku_pother_a + $rui_ctoku_pother_b;
    
    if ($yyyymm >= 200912 && $yyyymm <= 201003) {
        $rui_ctoku_pother = $rui_ctoku_pother + 115715;
    }
    if ($yyyymm >= 201001 && $yyyymm <= 201003) {
        $rui_ctoku_pother = $rui_ctoku_pother - 58247;
    }
    $rui_ctoku_pother_sagaku = $rui_ctoku_pother;
    $rui_ctoku_pother        = number_format(($rui_ctoku_pother / $tani), $keta);
} else {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���ץ�����Ķȳ����פ���¾'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_ctoku_pother) < 1) {
        $rui_ctoku_pother        = 0;   // ��������
        $rui_ctoku_pother_sagaku = 0;
    } else {
        if ($yyyymm >= 200912 && $yyyymm <= 201003) {
           $rui_ctoku_pother = $rui_ctoku_pother + 115715;
        }
        if ($yyyymm >= 201001 && $yyyymm <= 201003) {
            $rui_ctoku_pother = $rui_ctoku_pother - 58247;
        }
        $rui_ctoku_pother_sagaku = $rui_ctoku_pother;
        $rui_ctoku_pother        = number_format(($rui_ctoku_pother / $tani), $keta);
    }
}
if ($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���ץ�Ķȳ����פ���¾�Ʒ׻�'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_c_pother) < 1) {
        $rui_c_pother         = 0;      // ��������
        $rui_ch_pother        = 0;      // ��������
        $rui_ch_pother_sagaku = 0;      // ��������
    } else {
        $rui_ch_pother        = $rui_c_pother - $rui_ctoku_pother_sagaku;
        $rui_ch_pother_sagaku = $rui_ch_pother;
        $rui_ch_pother        = number_format(($rui_ch_pother / $tani), $keta);
        $rui_c_pother = number_format(($rui_c_pother / $tani), $keta);
    }
} elseif ($yyyymm >= 201001 && $yyyymm <= 201003) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=200904 and pl_bs_ym<=200912 and note='���ץ�Ķȳ����פ���¾'");
    if (getUniResult($query, $rui_c_pother_a) < 1) {
        $rui_c_pother_a = 0;                          // ��������
    }
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=201001 and pl_bs_ym<=%d and note='���ץ�Ķȳ����פ���¾�Ʒ׻�'", $yyyymm);
    if (getUniResult($query, $rui_c_pother_b) < 1) {
        $rui_c_pother_b = 0;                          // ��������
    }
    $rui_c_pother = $rui_c_pother_a + $rui_c_pother_b;
    
    if ($yyyymm >= 200912 && $yyyymm <= 201003) {
        $rui_c_pother = $rui_c_pother + 389809;
    }
    if ($yyyymm >= 201001 && $yyyymm <= 201003) {
        $rui_c_pother = $rui_c_pother - 315529;
    }
    $rui_c_pother         = $rui_c_pother - $rui_b_pother_a;
    $rui_ch_pother        = $rui_c_pother - $rui_ctoku_pother_sagaku;
    $rui_ch_pother_sagaku = $rui_ch_pother;
    $rui_ch_pother        = number_format(($rui_ch_pother / $tani), $keta);
    $rui_c_pother         = number_format(($rui_c_pother / $tani), $keta);
} else {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���ץ�Ķȳ����פ���¾'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_c_pother) < 1) {
        $rui_c_pother         = 0;      // ��������
        $rui_ch_pother        = 0;      // ��������
        $rui_ch_pother_sagaku = 0;      // ��������
    } else {
        if ($yyyymm >= 200912 && $yyyymm <= 201003) {
            $rui_c_pother = $rui_c_pother + 389809;
        }
        if ($yyyymm >= 201001 && $yyyymm <= 201003) {
            $rui_c_pother = $rui_c_pother - 315529;
        }
        $rui_c_pother         = $rui_c_pother - $rui_b_pother;
        $rui_ch_pother        = $rui_c_pother - $rui_ctoku_pother_sagaku;
        $rui_ch_pother_sagaku = $rui_ch_pother;
        $rui_ch_pother        = number_format(($rui_ch_pother / $tani), $keta);
        $rui_c_pother         = number_format(($rui_c_pother / $tani), $keta);
    }
}
/********** �Ķȳ����פι�� **********/
    ///// ���ץ�����
$p2_ctoku_nonope_profit_sum         = $p2_ctoku_gyoumu_sagaku + $p2_ctoku_swari_sagaku + $p2_ctoku_pother_sagaku;
$p2_ctoku_nonope_profit_sum_sagaku  = $p2_ctoku_nonope_profit_sum;
$p2_ctoku_nonope_profit_sum         = number_format(($p2_ctoku_nonope_profit_sum / $tani), $keta);

$p1_ctoku_nonope_profit_sum         = $p1_ctoku_gyoumu_sagaku + $p1_ctoku_swari_sagaku + $p1_ctoku_pother_sagaku;
$p1_ctoku_nonope_profit_sum_sagaku  = $p1_ctoku_nonope_profit_sum;
$p1_ctoku_nonope_profit_sum         = number_format(($p1_ctoku_nonope_profit_sum / $tani), $keta);

$ctoku_nonope_profit_sum            = $ctoku_gyoumu_sagaku + $ctoku_swari_sagaku + $ctoku_pother_sagaku;
$ctoku_nonope_profit_sum_sagaku     = $ctoku_nonope_profit_sum;
$ctoku_nonope_profit_sum            = number_format(($ctoku_nonope_profit_sum / $tani), $keta);

$rui_ctoku_nonope_profit_sum        = $rui_ctoku_gyoumu_sagaku + $rui_ctoku_swari_sagaku + $rui_ctoku_pother_sagaku;
$rui_ctoku_nonope_profit_sum_sagaku = $rui_ctoku_nonope_profit_sum;
$rui_ctoku_nonope_profit_sum        = number_format(($rui_ctoku_nonope_profit_sum / $tani), $keta);

    ///// ����
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�Ķȳ����׷׺Ʒ׻�'", $yyyymm);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�Ķȳ����׷�'", $yyyymm);
}
if (getUniResult($query, $c_nonope_profit_sum) < 1) {
    $c_nonope_profit_sum         = 0;       // ��������
    $ch_nonope_profit_sum        = 0;       // ��������
    $ch_nonope_profit_sum_sagaku = 0;       // ��������
    $c_nonope_profit_sum_temp    = 0;
} else {
    if ($yyyymm < 201001) {
        $c_nonope_profit_sum = $c_nonope_profit_sum - $b_pother;
    }
    $c_nonope_profit_sum_temp    = $c_nonope_profit_sum;
    $ch_nonope_profit_sum        = $c_nonope_profit_sum - $ctoku_nonope_profit_sum_sagaku;
    $ch_nonope_profit_sum_sagaku = $ch_nonope_profit_sum;
    $ch_nonope_profit_sum        = number_format(($ch_nonope_profit_sum / $tani), $keta);
    $c_nonope_profit_sum         = number_format(($c_nonope_profit_sum / $tani), $keta);
}
    ///// ����
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�Ķȳ����׷׺Ʒ׻�'", $p1_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�Ķȳ����׷�'", $p1_ym);
}
if (getUniResult($query, $p1_c_nonope_profit_sum) < 1) {
    $p1_c_nonope_profit_sum         = 0;       // ��������
    $p1_ch_nonope_profit_sum        = 0;       // ��������
    $p1_ch_nonope_profit_sum_sagaku = 0;       // ��������
    $p1_c_nonope_profit_sum_temp    = 0;
} else {
    if ($p1_ym < 201001) {
        $p1_c_nonope_profit_sum = $p1_c_nonope_profit_sum - $p1_b_pother;
    }
    $p1_c_nonope_profit_sum_temp    = $p1_c_nonope_profit_sum;
    $p1_ch_nonope_profit_sum        = $p1_c_nonope_profit_sum - $p1_ctoku_nonope_profit_sum_sagaku;
    $p1_ch_nonope_profit_sum_sagaku = $p1_ch_nonope_profit_sum;
    $p1_ch_nonope_profit_sum        = number_format(($p1_ch_nonope_profit_sum / $tani), $keta);
    $p1_c_nonope_profit_sum         = number_format(($p1_c_nonope_profit_sum / $tani), $keta);
}
    ///// ������
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�Ķȳ����׷׺Ʒ׻�'", $p2_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�Ķȳ����׷�'", $p2_ym);
}
if (getUniResult($query, $p2_c_nonope_profit_sum) < 1) {
    $p2_c_nonope_profit_sum         = 0;       // ��������
    $p2_ch_nonope_profit_sum        = 0;       // ��������
    $p2_ch_nonope_profit_sum_sagaku = 0;       // ��������
    $p2_c_nonope_profit_sum_temp    = 0;
} else {
    if ($p2_ym < 201001) {
        $p2_c_nonope_profit_sum = $p2_c_nonope_profit_sum - $p2_b_pother;
    }
    $p2_c_nonope_profit_sum_temp    = $p2_c_nonope_profit_sum;
    $p2_ch_nonope_profit_sum        = $p2_c_nonope_profit_sum - $p2_ctoku_nonope_profit_sum_sagaku;
    $p2_ch_nonope_profit_sum_sagaku = $p2_ch_nonope_profit_sum;
    $p2_ch_nonope_profit_sum        = number_format(($p2_ch_nonope_profit_sum / $tani), $keta);
    $p2_c_nonope_profit_sum         = number_format(($p2_c_nonope_profit_sum / $tani), $keta);
}
    ///// �����߷�
if ($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���ץ�Ķȳ����׷׺Ʒ׻�'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_c_nonope_profit_sum) < 1) {
        $rui_c_nonope_profit_sum         = 0;   // ��������
        $rui_ch_nonope_profit_sum        = 0;   // ��������
        $rui_ch_nonope_profit_sum_sagaku = 0;   // ��������
    } else {
        //$rui_c_nonope_profit_sum       = $rui_c_nonope_profit_sum - $rui_b_nonope_profit_sum;
        $rui_ch_nonope_profit_sum        = $rui_c_nonope_profit_sum - $rui_ctoku_nonope_profit_sum_sagaku;
        $rui_ch_nonope_profit_sum_sagaku = $rui_ch_nonope_profit_sum;
        $rui_ch_nonope_profit_sum        = number_format(($rui_ch_nonope_profit_sum / $tani), $keta);
        $rui_c_nonope_profit_sum         = number_format(($rui_c_nonope_profit_sum / $tani), $keta);
    }
} elseif ($yyyymm >= 201001 && $yyyymm <= 201003) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=200904 and pl_bs_ym<=200912 and note='���ץ�Ķȳ����׷�'");
    if (getUniResult($query, $rui_c_nonope_profit_sum_a) < 1) {
        $rui_c_nonope_profit_sum_a = 0;                          // ��������
    }
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=201001 and pl_bs_ym<=%d and note='���ץ�Ķȳ����׷׺Ʒ׻�'", $yyyymm);
    if (getUniResult($query, $rui_c_nonope_profit_sum_b) < 1) {
        $rui_c_nonope_profit_sum_b = 0;                          // ��������
    }
    $rui_c_nonope_profit_sum = $rui_c_nonope_profit_sum_a + $rui_c_nonope_profit_sum_b;
    if ($yyyymm < 201001) {
        $rui_c_nonope_profit_sum = $rui_c_nonope_profit_sum - $rui_b_nonope_profit_sum;
    }
    $rui_c_nonope_profit_sum         = $rui_c_nonope_profit_sum - $rui_b_pother_a;
    $rui_ch_nonope_profit_sum        = $rui_c_nonope_profit_sum - $rui_ctoku_nonope_profit_sum_sagaku;
    $rui_ch_nonope_profit_sum_sagaku = $rui_ch_nonope_profit_sum;
    $rui_ch_nonope_profit_sum        = number_format(($rui_ch_nonope_profit_sum / $tani), $keta);
    $rui_c_nonope_profit_sum         = number_format(($rui_c_nonope_profit_sum / $tani), $keta);
} else {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���ץ�Ķȳ����׷�'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_c_nonope_profit_sum) < 1) {
        $rui_c_nonope_profit_sum         = 0;   // ��������
        $rui_ch_nonope_profit_sum        = 0;   // ��������
        $rui_ch_nonope_profit_sum_sagaku = 0;   // ��������
    } else {
        $rui_c_nonope_profit_sum         = $rui_c_nonope_profit_sum - $rui_b_pother;
        $rui_ch_nonope_profit_sum        = $rui_c_nonope_profit_sum - $rui_ctoku_nonope_profit_sum_sagaku;
        $rui_ch_nonope_profit_sum_sagaku = $rui_ch_nonope_profit_sum;
        $rui_ch_nonope_profit_sum        = number_format(($rui_ch_nonope_profit_sum / $tani), $keta);
        $rui_c_nonope_profit_sum         = number_format(($rui_c_nonope_profit_sum / $tani), $keta);
    }
}
/********** �Ķȳ����Ѥλ�ʧ��© **********/
    ///// ����
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ������ʧ��©�Ʒ׻�'", $yyyymm);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ������ʧ��©'", $yyyymm);
}
if (getUniResult($query, $ctoku_srisoku) < 1) {
    $ctoku_srisoku         = 0;                 // ��������
    $ctoku_srisoku_sagaku = 0;                 // ��������
} else {
    $ctoku_srisoku_sagaku = $ctoku_srisoku;
    $ctoku_srisoku        = number_format(($ctoku_srisoku / $tani), $keta);
}
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ��ʧ��©�Ʒ׻�'", $yyyymm);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ��ʧ��©'", $yyyymm);
}
if (getUniResult($query, $c_srisoku) < 1) {
    $c_srisoku         = 0;                 // ��������
    $ch_srisoku        = 0;                 // ��������
    $ch_srisoku_sagaku = 0;                 // ��������
} else {
    $ch_srisoku        = $c_srisoku - $ctoku_srisoku_sagaku;
    $ch_srisoku_sagaku = $ch_srisoku;
    $ch_srisoku        = number_format(($ch_srisoku / $tani), $keta);
    $c_srisoku         = number_format(($c_srisoku / $tani), $keta);
}
    ///// ����
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ������ʧ��©�Ʒ׻�'", $p1_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ������ʧ��©'", $p1_ym);
}
if (getUniResult($query, $p1_ctoku_srisoku) < 1) {
    $p1_ctoku_srisoku         = 0;                 // ��������
    $p1_ctoku_srisoku_sagaku = 0;                 // ��������
} else {
    $p1_ctoku_srisoku_sagaku = $p1_ctoku_srisoku;
    $p1_ctoku_srisoku        = number_format(($p1_ctoku_srisoku / $tani), $keta);
}
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ��ʧ��©�Ʒ׻�'", $p1_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ��ʧ��©'", $p1_ym);
}
if (getUniResult($query, $p1_c_srisoku) < 1) {
    $p1_c_srisoku         = 0;                 // ��������
    $p1_ch_srisoku        = 0;                 // ��������
    $p1_ch_srisoku_sagaku = 0;                 // ��������
} else {
    $p1_ch_srisoku        = $p1_c_srisoku - $p1_ctoku_srisoku_sagaku;
    $p1_ch_srisoku_sagaku = $p1_ch_srisoku;
    $p1_ch_srisoku        = number_format(($p1_ch_srisoku / $tani), $keta);
    $p1_c_srisoku         = number_format(($p1_c_srisoku / $tani), $keta);
}
    ///// ������
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ������ʧ��©�Ʒ׻�'", $p2_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ������ʧ��©'", $p2_ym);
}
if (getUniResult($query, $p2_ctoku_srisoku) < 1) {
    $p2_ctoku_srisoku         = 0;                 // ��������
    $p2_ctoku_srisoku_sagaku = 0;                 // ��������
} else {
    $p2_ctoku_srisoku_sagaku = $p2_ctoku_srisoku;
    $p2_ctoku_srisoku        = number_format(($p2_ctoku_srisoku / $tani), $keta);
}
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ��ʧ��©�Ʒ׻�'", $p2_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ��ʧ��©'", $p2_ym);
}
if (getUniResult($query, $p2_c_srisoku) < 1) {
    $p2_c_srisoku         = 0;                 // ��������
    $p2_ch_srisoku        = 0;                 // ��������
    $p2_ch_srisoku_sagaku = 0;                 // ��������
} else {
    $p2_ch_srisoku        = $p2_c_srisoku - $p2_ctoku_srisoku_sagaku;
    $p2_ch_srisoku_sagaku = $p2_ch_srisoku;
    $p2_ch_srisoku        = number_format(($p2_ch_srisoku / $tani), $keta);
    $p2_c_srisoku         = number_format(($p2_c_srisoku / $tani), $keta);
}
    ///// �����߷�
if ($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���ץ������ʧ��©�Ʒ׻�'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_ctoku_srisoku) < 1) {
        $rui_ctoku_srisoku        = 0;          // ��������
        $rui_ctoku_srisoku_sagaku = 0;
    } else {
        $rui_ctoku_srisoku_sagaku = $rui_ctoku_srisoku;
        $rui_ctoku_srisoku        = number_format(($rui_ctoku_srisoku / $tani), $keta);
    }
} elseif ($yyyymm >= 201001 && $yyyymm <= 201003) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=200904 and pl_bs_ym<=200912 and note='���ץ������ʧ��©'");
    if (getUniResult($query, $rui_ctoku_srisoku_a) < 1) {
        $rui_ctoku_srisoku_a = 0;                          // ��������
    }
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=201001 and pl_bs_ym<=%d and note='���ץ������ʧ��©�Ʒ׻�'", $yyyymm);
    if (getUniResult($query, $rui_ctoku_srisoku_b) < 1) {
        $rui_ctoku_srisoku_b = 0;                          // ��������
    }
    $rui_ctoku_srisoku        = $rui_ctoku_srisoku_a + $rui_ctoku_srisoku_b;
    $rui_ctoku_srisoku_sagaku = $rui_ctoku_srisoku;
    $rui_ctoku_srisoku        = number_format(($rui_ctoku_srisoku / $tani), $keta);
} else {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���ץ������ʧ��©'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_ctoku_srisoku) < 1) {
        $rui_ctoku_srisoku        = 0;          // ��������
        $rui_ctoku_srisoku_sagaku = 0;
    } else {
        $rui_ctoku_srisoku_sagaku = $rui_ctoku_srisoku;
        $rui_ctoku_srisoku        = number_format(($rui_ctoku_srisoku / $tani), $keta);
    }
}
if ($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���ץ��ʧ��©�Ʒ׻�'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_c_srisoku) < 1) {
        $rui_c_srisoku         = 0;             // ��������
        $rui_ch_srisoku        = 0;             // ��������
        $rui_ch_srisoku_sagaku = 0;             // ��������
    } else {
        $rui_ch_srisoku        = $rui_c_srisoku - $rui_ctoku_srisoku_sagaku;
        $rui_ch_srisoku_sagaku = $rui_ch_srisoku;
        $rui_ch_srisoku        = number_format(($rui_ch_srisoku / $tani), $keta);
        $rui_c_srisoku         = number_format(($rui_c_srisoku / $tani), $keta);
    }
} elseif ($yyyymm >= 201001 && $yyyymm <= 201003) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=200904 and pl_bs_ym<=200912 and note='���ץ��ʧ��©'");
    if (getUniResult($query, $rui_c_srisoku_a) < 1) {
        $rui_c_srisoku_a = 0;                          // ��������
    }
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=201001 and pl_bs_ym<=%d and note='���ץ��ʧ��©�Ʒ׻�'", $yyyymm);
    if (getUniResult($query, $rui_c_srisoku_b) < 1) {
        $rui_c_srisoku_b = 0;                          // ��������
    }
    $rui_c_srisoku         = $rui_c_srisoku_a + $rui_c_srisoku_b;
    $rui_ch_srisoku        = $rui_c_srisoku - $rui_ctoku_srisoku_sagaku;
    $rui_ch_srisoku_sagaku = $rui_ch_srisoku;
    $rui_ch_srisoku        = number_format(($rui_ch_srisoku / $tani), $keta);
    $rui_c_srisoku         = number_format(($rui_c_srisoku / $tani), $keta);
} else {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���ץ��ʧ��©'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_c_srisoku) < 1) {
        $rui_c_srisoku         = 0;             // ��������
        $rui_ch_srisoku        = 0;             // ��������
        $rui_ch_srisoku_sagaku = 0;             // ��������
    } else {
        $rui_ch_srisoku        = $rui_c_srisoku - $rui_ctoku_srisoku_sagaku;
        $rui_ch_srisoku_sagaku = $rui_ch_srisoku;
        $rui_ch_srisoku        = number_format(($rui_ch_srisoku / $tani), $keta);
        $rui_c_srisoku         = number_format(($rui_c_srisoku / $tani), $keta);
    }
}
/********** �Ķȳ����ѤΤ���¾ **********/
    ///// ����
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�����Ķȳ����Ѥ���¾�Ʒ׻�'", $yyyymm);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�����Ķȳ����Ѥ���¾'", $yyyymm);
}
if (getUniResult($query, $ctoku_lother) < 1) {
    $ctoku_lother         = 0;                  // ��������
    $ctoku_lother_sagaku = 0;                  // ��������
} else {
    $ctoku_lother_sagaku = $ctoku_lother;
    $ctoku_lother        = number_format(($ctoku_lother / $tani), $keta);
}
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�Ķȳ����Ѥ���¾�Ʒ׻�'", $yyyymm);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�Ķȳ����Ѥ���¾'", $yyyymm);
}
if (getUniResult($query, $c_lother) < 1) {
    $c_lother         = 0;                  // ��������
    $ch_lother        = 0;                  // ��������
    $ch_lother_sagaku = 0;                  // ��������
} else {
    $ch_lother        = $c_lother - $ctoku_lother_sagaku;
    $ch_lother_sagaku = $ch_lother;
    $ch_lother        = number_format(($ch_lother / $tani), $keta);
    $c_lother         = number_format(($c_lother / $tani), $keta);
}
    ///// ����
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�����Ķȳ����Ѥ���¾�Ʒ׻�'", $p1_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�����Ķȳ����Ѥ���¾'", $p1_ym);
}
if (getUniResult($query, $p1_ctoku_lother) < 1) {
    $p1_ctoku_lother         = 0;                  // ��������
    $p1_ctoku_lother_sagaku = 0;                  // ��������
} else {
    $p1_ctoku_lother_sagaku = $p1_ctoku_lother;
    $p1_ctoku_lother        = number_format(($p1_ctoku_lother / $tani), $keta);
}
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�Ķȳ����Ѥ���¾�Ʒ׻�'", $p1_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�Ķȳ����Ѥ���¾'", $p1_ym);
}
if (getUniResult($query, $p1_c_lother) < 1) {
    $p1_c_lother         = 0;                  // ��������
    $p1_ch_lother        = 0;                  // ��������
    $p1_ch_lother_sagaku = 0;                  // ��������
} else {
    $p1_ch_lother        = $p1_c_lother - $p1_ctoku_lother_sagaku;
    $p1_ch_lother_sagaku = $p1_ch_lother;
    $p1_ch_lother        = number_format(($p1_ch_lother / $tani), $keta);
    $p1_c_lother         = number_format(($p1_c_lother / $tani), $keta);
}
    ///// ������
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�����Ķȳ����Ѥ���¾�Ʒ׻�'", $p2_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�����Ķȳ����Ѥ���¾'", $p2_ym);
}
if (getUniResult($query, $p2_ctoku_lother) < 1) {
    $p2_ctoku_lother         = 0;                  // ��������
    $p2_ctoku_lother_sagaku = 0;                  // ��������
} else {
    $p2_ctoku_lother_sagaku = $p2_ctoku_lother;
    $p2_ctoku_lother        = number_format(($p2_ctoku_lother / $tani), $keta);
}
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�Ķȳ����Ѥ���¾�Ʒ׻�'", $p2_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�Ķȳ����Ѥ���¾'", $p2_ym);
}
if (getUniResult($query, $p2_c_lother) < 1) {
    $p2_c_lother         = 0;                  // ��������
    $p2_ch_lother        = 0;                  // ��������
    $p2_ch_lother_sagaku = 0;                  // ��������
} else {
    $p2_ch_lother        = $p2_c_lother - $p2_ctoku_lother_sagaku;
    $p2_ch_lother_sagaku = $p2_ch_lother;
    $p2_ch_lother        = number_format(($p2_ch_lother / $tani), $keta);
    $p2_c_lother         = number_format(($p2_c_lother / $tani), $keta);
}
    ///// �����߷�
if ($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���ץ�����Ķȳ����Ѥ���¾�Ʒ׻�'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_ctoku_lother) < 1) {
        $rui_ctoku_lother        = 0;           // ��������
        $rui_ctoku_lother_sagaku = 0;
    } else {
        $rui_ctoku_lother_sagaku = $rui_ctoku_lother;
        $rui_ctoku_lother        = number_format(($rui_ctoku_lother / $tani), $keta);
    }
} elseif ($yyyymm >= 201001 && $yyyymm <= 201003) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=200904 and pl_bs_ym<=200912 and note='���ץ�����Ķȳ����Ѥ���¾'");
    if (getUniResult($query, $rui_ctoku_lother_a) < 1) {
        $rui_ctoku_lother_a = 0;                          // ��������
    }
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=201001 and pl_bs_ym<=%d and note='���ץ�����Ķȳ����Ѥ���¾�Ʒ׻�'", $yyyymm);
    if (getUniResult($query, $rui_ctoku_lother_b) < 1) {
        $rui_ctoku_lother_b = 0;                          // ��������
    }
    $rui_ctoku_lother        = $rui_ctoku_lother_a + $rui_ctoku_lother_b;
    $rui_ctoku_lother_sagaku = $rui_ctoku_lother;
    $rui_ctoku_lother        = number_format(($rui_ctoku_lother / $tani), $keta);
} else {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���ץ�����Ķȳ����Ѥ���¾'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_ctoku_lother) < 1) {
        $rui_ctoku_lother        = 0;           // ��������
        $rui_ctoku_lother_sagaku = 0;
    } else {
        $rui_ctoku_lother_sagaku = $rui_ctoku_lother;
        $rui_ctoku_lother        = number_format(($rui_ctoku_lother / $tani), $keta);
    }
}
if ($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���ץ�Ķȳ����Ѥ���¾�Ʒ׻�'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_c_lother) < 1) {
        $rui_c_lother         = 0;              // ��������
        $rui_ch_lother        = 0;              // ��������
        $rui_ch_lother_sagaku = 0;              // ��������
    } else {
        $rui_ch_lother        = $rui_c_lother - $rui_ctoku_lother_sagaku;
        $rui_ch_lother_sagaku = $rui_ch_lother;
        $rui_ch_lother        = number_format(($rui_ch_lother / $tani), $keta);
        $rui_c_lother = number_format(($rui_c_lother / $tani), $keta);
    }
} elseif ($yyyymm >= 201001 && $yyyymm <= 201003) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=200904 and pl_bs_ym<=200912 and note='���ץ�Ķȳ����Ѥ���¾'");
    if (getUniResult($query, $rui_c_lother_a) < 1) {
        $rui_c_lother_a = 0;                          // ��������
    }
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=201001 and pl_bs_ym<=%d and note='���ץ�Ķȳ����Ѥ���¾�Ʒ׻�'", $yyyymm);
    if (getUniResult($query, $rui_c_lother_b) < 1) {
        $rui_c_lother_b = 0;                          // ��������
    }
    $rui_c_lother         = $rui_c_lother_a + $rui_c_lother_b;
    $rui_ch_lother        = $rui_c_lother - $rui_ctoku_lother_sagaku;
    $rui_ch_lother_sagaku = $rui_ch_lother;
    $rui_ch_lother        = number_format(($rui_ch_lother / $tani), $keta);
    $rui_c_lother         = number_format(($rui_c_lother / $tani), $keta);
} else {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���ץ�Ķȳ����Ѥ���¾'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_c_lother) < 1) {
        $rui_c_lother         = 0;              // ��������
        $rui_ch_lother        = 0;              // ��������
        $rui_ch_lother_sagaku = 0;              // ��������
    } else {
        $rui_ch_lother        = $rui_c_lother - $rui_ctoku_lother_sagaku;
        $rui_ch_lother_sagaku = $rui_ch_lother;
        $rui_ch_lother        = number_format(($rui_ch_lother / $tani), $keta);
        $rui_c_lother         = number_format(($rui_c_lother / $tani), $keta);
    }
}
/********** �Ķȳ����Ѥι�� **********/
    ///// ���ץ�����
$p2_ctoku_nonope_loss_sum         = $p2_ctoku_srisoku_sagaku + $p2_ctoku_lother_sagaku;
$p2_ctoku_nonope_loss_sum_sagaku  = $p2_ctoku_nonope_loss_sum;
$p2_ctoku_nonope_loss_sum         = number_format(($p2_ctoku_nonope_loss_sum / $tani), $keta);

$p1_ctoku_nonope_loss_sum         = $p1_ctoku_srisoku_sagaku + $p1_ctoku_lother_sagaku;
$p1_ctoku_nonope_loss_sum_sagaku  = $p1_ctoku_nonope_loss_sum;
$p1_ctoku_nonope_loss_sum         = number_format(($p1_ctoku_nonope_loss_sum / $tani), $keta);

$ctoku_nonope_loss_sum            = $ctoku_srisoku_sagaku + $ctoku_lother_sagaku;
$ctoku_nonope_loss_sum_sagaku     = $ctoku_nonope_loss_sum;
$ctoku_nonope_loss_sum            = number_format(($ctoku_nonope_loss_sum / $tani), $keta);

$rui_ctoku_nonope_loss_sum        = $rui_ctoku_srisoku_sagaku + $rui_ctoku_lother_sagaku;
$rui_ctoku_nonope_loss_sum_sagaku = $rui_ctoku_nonope_loss_sum;
$rui_ctoku_nonope_loss_sum        = number_format(($rui_ctoku_nonope_loss_sum / $tani), $keta);

    ///// ����
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�Ķȳ����ѷ׺Ʒ׻�'", $yyyymm);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�Ķȳ����ѷ�'", $yyyymm);
}
if (getUniResult($query, $c_nonope_loss_sum) < 1) {
    $c_nonope_loss_sum         = 0;     // ��������
    $ch_nonope_loss_sum        = 0;     // ��������
    $ch_nonope_loss_sum_sagaku = 0;     // ��������
    $c_nonope_loss_sum_temp    = 0;     // ��������
} else {
    $ch_nonope_loss_sum        = $c_nonope_loss_sum - $ctoku_nonope_loss_sum_sagaku;
    $ch_nonope_loss_sum_sagaku = $ch_nonope_loss_sum;
    $ch_nonope_loss_sum        = number_format(($ch_nonope_loss_sum / $tani), $keta);
    $c_nonope_loss_sum_temp    = $c_nonope_loss_sum;
    $c_nonope_loss_sum         = number_format(($c_nonope_loss_sum / $tani), $keta);
}
    ///// ����
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�Ķȳ����ѷ׺Ʒ׻�'", $p1_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�Ķȳ����ѷ�'", $p1_ym);
}
if (getUniResult($query, $p1_c_nonope_loss_sum) < 1) {
    $p1_c_nonope_loss_sum         = 0;     // ��������
    $p1_ch_nonope_loss_sum        = 0;     // ��������
    $p1_ch_nonope_loss_sum_sagaku = 0;     // ��������
    $p1_c_nonope_loss_sum_temp    = 0;     // ��������
} else {
    $p1_ch_nonope_loss_sum        = $p1_c_nonope_loss_sum - $p1_ctoku_nonope_loss_sum_sagaku;
    $p1_ch_nonope_loss_sum_sagaku = $p1_ch_nonope_loss_sum;
    $p1_ch_nonope_loss_sum        = number_format(($p1_ch_nonope_loss_sum / $tani), $keta);
    $p1_c_nonope_loss_sum_temp    = $p1_c_nonope_loss_sum;
    $p1_c_nonope_loss_sum         = number_format(($p1_c_nonope_loss_sum / $tani), $keta);
}
    ///// ������
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�Ķȳ����ѷ׺Ʒ׻�'", $p2_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�Ķȳ����ѷ�'", $p2_ym);
}
if (getUniResult($query, $p2_c_nonope_loss_sum) < 1) {
    $p2_c_nonope_loss_sum         = 0;     // ��������
    $p2_ch_nonope_loss_sum        = 0;     // ��������
    $p2_ch_nonope_loss_sum_sagaku = 0;     // ��������
    $p2_c_nonope_loss_sum_temp    = 0;     // ��������
} else {
    $p2_ch_nonope_loss_sum        = $p2_c_nonope_loss_sum - $p2_ctoku_nonope_loss_sum_sagaku;
    $p2_ch_nonope_loss_sum_sagaku = $p2_ch_nonope_loss_sum;
    $p2_ch_nonope_loss_sum        = number_format(($p2_ch_nonope_loss_sum / $tani), $keta);
    $p2_c_nonope_loss_sum_temp    = $p2_c_nonope_loss_sum;
    $p2_c_nonope_loss_sum         = number_format(($p2_c_nonope_loss_sum / $tani), $keta);
}
    ///// �����߷�
if ($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���ץ�Ķȳ����ѷ׺Ʒ׻�'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_c_nonope_loss_sum) < 1) {
        $rui_c_nonope_loss_sum         = 0; // ��������
        $rui_ch_nonope_loss_sum        = 0; // ��������
        $rui_ch_nonope_loss_sum_sagaku = 0; // ��������
    } else {
        $rui_ch_nonope_loss_sum        = $rui_c_nonope_loss_sum - $rui_ctoku_nonope_loss_sum_sagaku;
        $rui_ch_nonope_loss_sum_sagaku = $rui_ch_nonope_loss_sum;
        $rui_ch_nonope_loss_sum        = number_format(($rui_ch_nonope_loss_sum / $tani), $keta);
        $rui_c_nonope_loss_sum         = number_format(($rui_c_nonope_loss_sum / $tani), $keta);
    }
} elseif ($yyyymm >= 201001 && $yyyymm <= 201003) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=200904 and pl_bs_ym<=200912 and note='���ץ�Ķȳ����ѷ�'");
    if (getUniResult($query, $rui_c_nonope_loss_sum_a) < 1) {
        $rui_c_nonope_loss_sum_a = 0;                          // ��������
    }
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=201001 and pl_bs_ym<=%d and note='���ץ�Ķȳ����ѷ׺Ʒ׻�'", $yyyymm);
    if (getUniResult($query, $rui_c_nonope_loss_sum_b) < 1) {
        $rui_c_nonope_loss_sum_b = 0;                          // ��������
    }
    $rui_c_nonope_loss_sum         = $rui_c_nonope_loss_sum_a + $rui_c_nonope_loss_sum_b;
    $rui_ch_nonope_loss_sum        = $rui_c_nonope_loss_sum - $rui_ctoku_nonope_loss_sum_sagaku;
    $rui_ch_nonope_loss_sum_sagaku = $rui_ch_nonope_loss_sum;
    $rui_ch_nonope_loss_sum        = number_format(($rui_ch_nonope_loss_sum / $tani), $keta);
    $rui_c_nonope_loss_sum         = number_format(($rui_c_nonope_loss_sum / $tani), $keta);
} else {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���ץ�Ķȳ����ѷ�'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_c_nonope_loss_sum) < 1) {
        $rui_c_nonope_loss_sum         = 0; // ��������
        $rui_ch_nonope_loss_sum        = 0; // ��������
        $rui_ch_nonope_loss_sum_sagaku = 0; // ��������
    } else {
        $rui_ch_nonope_loss_sum        = $rui_c_nonope_loss_sum - $rui_ctoku_nonope_loss_sum_sagaku;
        $rui_ch_nonope_loss_sum_sagaku = $rui_ch_nonope_loss_sum;
        $rui_ch_nonope_loss_sum        = number_format(($rui_ch_nonope_loss_sum / $tani), $keta);
        $rui_c_nonope_loss_sum         = number_format(($rui_c_nonope_loss_sum / $tani), $keta);
    }
}
/********** �о����� **********/
///////// ���ʴ���ʬ�κ��۷׻���ϫ̳�����¤������δ���ͷ�����δ������-�Ķȳ����פ���¾��
$b_sagaku     = $b_sagaku - $b_pother;
$p1_b_sagaku  = $p1_b_sagaku - $p1_b_pother;
$p2_b_sagaku  = $p2_b_sagaku - $p2_b_pother;
//$rui_b_sagaku = $rui_b_sagaku - $rui_b_pother;
    ///// ���ץ�����
$p2_ctoku_current_profit         = $p2_ctoku_ope_profit_sagaku + $p2_ctoku_nonope_profit_sum_sagaku - $p2_ctoku_nonope_loss_sum_sagaku;
$p2_ctoku_current_profit_sagaku  = $p2_ctoku_current_profit;
$p2_ctoku_current_profit         = number_format(($p2_ctoku_current_profit / $tani), $keta);

$p1_ctoku_current_profit         = $p1_ctoku_ope_profit_sagaku + $p1_ctoku_nonope_profit_sum_sagaku - $p1_ctoku_nonope_loss_sum_sagaku;
$p1_ctoku_current_profit_sagaku  = $p1_ctoku_current_profit;
$p1_ctoku_current_profit         = number_format(($p1_ctoku_current_profit / $tani), $keta);

$ctoku_current_profit            = $ctoku_ope_profit_sagaku + $ctoku_nonope_profit_sum_sagaku - $ctoku_nonope_loss_sum_sagaku;
$ctoku_current_profit_sagaku     = $ctoku_current_profit;
$ctoku_current_profit            = number_format(($ctoku_current_profit / $tani), $keta);

$rui_ctoku_current_profit        = $rui_ctoku_ope_profit_sagaku + $rui_ctoku_nonope_profit_sum_sagaku - $rui_ctoku_nonope_loss_sum_sagaku;
$rui_ctoku_current_profit_sagaku = $rui_ctoku_current_profit;
$rui_ctoku_current_profit        = number_format(($rui_ctoku_current_profit / $tani), $keta);

    ///// ����
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�о����׺Ʒ׻�'", $yyyymm);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�о�����'", $yyyymm);
}
if (getUniResult($query, $c_current_profit) < 1) {
    $c_current_profit         = 0;      // ��������
    $ch_current_profit        = 0;      // ��������
    $ch_current_profit_sagaku = 0;      // ��������
} else {
    if ($yyyymm < 201001) {
        $c_current_profit = $c_current_profit + $b_sagaku + $c_allo_kin - $sc_uri_sagaku + $sc_metarial_sagaku; // ���ץ��������̣
    } else {
        $c_current_profit = $c_ope_profit_temp + $c_nonope_profit_sum_temp - $c_nonope_loss_sum_temp;
    }
    if ($yyyymm == 200912) {
        $c_current_profit = $c_current_profit - 1227429;
    }
    if ($yyyymm >= 201001) {
        //$c_current_profit = $c_current_profit - $c_kyu_kin;
        //$c_current_profit = $c_current_profit - 151313;
    }
    $ch_current_profit        = $c_current_profit - $ctoku_current_profit_sagaku;
    $ch_current_profit_sagaku = $ch_current_profit;
    $ch_current_profit        = number_format(($ch_current_profit / $tani), $keta);
    $c_current_profit         = number_format(($c_current_profit / $tani), $keta);
}
    ///// ����
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�о����׺Ʒ׻�'", $p1_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�о�����'", $p1_ym);
}
if (getUniResult($query, $p1_c_current_profit) < 1) {
    $p1_c_current_profit         = 0;      // ��������
    $p1_ch_current_profit        = 0;      // ��������
    $p1_ch_current_profit_sagaku = 0;      // ��������
} else {
    if ($p1_ym < 201001) {
        $p1_c_current_profit = $p1_c_current_profit + $p1_b_sagaku + $p1_c_allo_kin - $p1_sc_uri_sagaku + $p1_sc_metarial_sagaku; // ���ץ��������̣
    } else {
        $p1_c_current_profit = $p1_c_ope_profit_temp + $p1_c_nonope_profit_sum_temp - $p1_c_nonope_loss_sum_temp;
    }
    if ($p1_ym == 200912) {
        $p1_c_current_profit = $p1_c_current_profit - 1227429;
    }
    if ($p1_ym >= 201001) {
        //$p1_c_current_profit = $p1_c_current_profit - $p1_c_kyu_kin;   // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
        //$p1_c_current_profit = $p1_c_current_profit - 151313;   // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
    }
    $p1_ch_current_profit        = $p1_c_current_profit - $p1_ctoku_current_profit_sagaku;
    $p1_ch_current_profit_sagaku = $p1_ch_current_profit;
    $p1_ch_current_profit        = number_format(($p1_ch_current_profit / $tani), $keta);
    $p1_c_current_profit         = number_format(($p1_c_current_profit / $tani), $keta);
}
    ///// ������
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�о����׺Ʒ׻�'", $p2_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�о�����'", $p2_ym);
}
if (getUniResult($query, $p2_c_current_profit) < 1) {
    $p2_c_current_profit         = 0;      // ��������
    $p2_ch_current_profit        = 0;      // ��������
    $p2_ch_current_profit_sagaku = 0;      // ��������
} else {
    if ($p2_ym < 201001) {
        $p2_c_current_profit = $p2_c_current_profit + $p2_b_sagaku + $p2_c_allo_kin - $p2_sc_uri_sagaku + $p2_sc_metarial_sagaku; // ���ץ��������̣
    } else {
        $p2_c_current_profit = $p2_c_ope_profit_temp + $p2_c_nonope_profit_sum_temp - $p2_c_nonope_loss_sum_temp;
    }
    if ($p2_ym == 200912) {
        $p2_c_current_profit = $p2_c_current_profit - 1227429;
    }
    if ($p2_ym >= 201001) {
        //$p2_c_current_profit = $p2_c_current_profit - $p2_c_kyu_kin;   // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
        //$p2_c_current_profit = $p2_c_current_profit - 151313;   // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
    }
    $p2_ch_current_profit        = $p2_c_current_profit - $p2_ctoku_current_profit_sagaku;
    $p2_ch_current_profit_sagaku = $p2_ch_current_profit;
    $p2_ch_current_profit        = number_format(($p2_ch_current_profit / $tani), $keta);
    $p2_c_current_profit         = number_format(($p2_c_current_profit / $tani), $keta);
}
    ///// �����߷�
if ($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���ץ�о����׺Ʒ׻�'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_c_current_profit) < 1) {
        $rui_c_current_profit         = 0;  // ��������
        $rui_ch_current_profit        = 0;  // ��������
        $rui_ch_current_profit_sagaku = 0;  // ��������
    } else {
        $rui_c_current_profit         = $rui_c_current_profit + $rui_b_sagaku + $rui_c_allo_kin - $rui_sc_uri_sagaku + $rui_sc_metarial_sagaku; // ���ץ��������̣
        if ($yyyymm >= 201001) {
            $rui_c_current_profit = $rui_c_current_profit - $rui_c_kyu_kin; // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
            //$rui_c_current_profit = $rui_c_current_profit - 151313; // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
        }
        // 2013/11/07 �ɲ� 2013ǯ10���� ��̳������ʲ����ɸ�����Ĵ��
        if ($yyyymm >= 201310 && $yyyymm <= 201403) {
            $rui_c_current_profit += 1245035;
        }
        if ($yyyymm >= 201311 && $yyyymm <= 201403) {
            $rui_c_current_profit -= 1245035;
        }
        if ($yyyymm >= 201408 && $yyyymm <= 201503) {
            $rui_c_current_profit = $rui_c_current_profit - 611904;
        }
        $rui_ch_current_profit        = $rui_c_current_profit - $rui_ctoku_current_profit_sagaku;
        $rui_ch_current_profit_sagaku = $rui_ch_current_profit;
        $rui_ch_current_profit        = number_format(($rui_ch_current_profit / $tani), $keta);
        $rui_c_current_profit         = number_format(($rui_c_current_profit / $tani), $keta);
    }
} elseif ($yyyymm >= 201001 && $yyyymm <= 201003) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=200904 and pl_bs_ym<=200912 and note='���ץ�о�����'");
    if (getUniResult($query, $rui_c_current_profit_a) < 1) {
        $rui_c_current_profit_a = 0;                          // ��������
    }
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=201001 and pl_bs_ym<=%d and note='���ץ�о����׺Ʒ׻�'", $yyyymm);
    if (getUniResult($query, $rui_c_current_profit_b) < 1) {
        $rui_c_current_profit_b = 0;                          // ��������
    }
    $rui_c_current_profit = $rui_c_current_profit_a + $rui_c_current_profit_b;
    if ($yyyymm >= 200912 && $yyyymm <= 201003) {
        $rui_c_current_profit = $rui_c_current_profit - 1227429;
    }
    if ($yyyymm >= 201001) {
        $rui_c_current_profit = $rui_c_current_profit - $rui_c_kyu_kin; // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
        //$rui_c_current_profit = $rui_c_current_profit - 151313; // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
    }
    $rui_c_current_profit         = $rui_c_current_profit + $rui_b_sagaku + $rui_c_allo_kin - $rui_sc_uri_sagaku + $rui_sc_metarial_sagaku - $rui_b_pother_a; // ���ץ��������̣
    $rui_ch_current_profit        = $rui_c_current_profit - $rui_ctoku_current_profit_sagaku;
    $rui_ch_current_profit_sagaku = $rui_ch_current_profit;
    $rui_ch_current_profit        = number_format(($rui_ch_current_profit / $tani), $keta);
    $rui_c_current_profit         = number_format(($rui_c_current_profit / $tani), $keta);
} else {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���ץ�о�����'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_c_current_profit) < 1) {
        $rui_c_current_profit         = 0;  // ��������
        $rui_ch_current_profit        = 0;  // ��������
        $rui_ch_current_profit_sagaku = 0;  // ��������
    } else {
        $rui_c_current_profit         = $rui_c_current_profit + $rui_b_sagaku + $rui_c_allo_kin - $rui_sc_uri_sagaku + $rui_sc_metarial_sagaku; // ���ץ��������̣
        if ($yyyymm >= 200912 && $yyyymm <= 201003) {
            $rui_c_current_profit = $rui_c_current_profit - 1227429;
        }
        $rui_ch_current_profit        = $rui_c_current_profit - $rui_ctoku_current_profit_sagaku;
        $rui_ch_current_profit_sagaku = $rui_ch_current_profit;
        $rui_ch_current_profit        = number_format(($rui_ch_current_profit / $tani), $keta);
        $rui_c_current_profit         = number_format(($rui_c_current_profit / $tani), $keta);
    }
}
////////// �õ�����μ���
$query = sprintf("select comment from act_comment_history where pl_bs_ym=%d and item='���ץ�ɸ��»�׷׻���'", $yyyymm);
if (getUniResult($query,$comment_c) <= 0) {
    $comment_c = "";
}
$query = sprintf("select comment from act_comment_history where pl_bs_ym=%d and item='���ץ�����»�׷׻���'", $yyyymm);
if (getUniResult($query,$comment_ctoku) <= 0) {
    $comment_ctoku = "";
}
$query = sprintf("select comment from act_comment_history where pl_bs_ym=%d and item='����ctoku»�׷׻���'", $yyyymm);
if (getUniResult($query,$comment_all) <= 0) {
    $comment_all = "";
}
$query = sprintf("select comment from act_comment_history where pl_bs_ym=%d and item='����¾ctoku»�׷׻���'", $yyyymm);
if (getUniResult($query,$comment_other) <= 0) {
    $comment_other = "";
}
if (isset($_POST['input_data'])) {                        // ����ǡ�������Ͽ
    ///////// ���ܤȥ���ǥå����δ�Ϣ�դ�
    $item = array();
    $item[0]   = "����";
    $item[1]   = "��������ų���ê����";
    $item[2]   = "������(������)";
    $item[3]   = "ϫ̳��";
    $item[4]   = "��¤����";
    $item[5]   = "���������ų���ê����";
    $item[6]   = "��帶��";
    $item[7]   = "���������";
    $item[8]   = "�ͷ���";
    $item[9]   = "����";
    $item[10]  = "�δ���ڤӰ��̴������";
    $item[11]  = "�Ķ�����";
    $item[12]  = "��̳��������";
    $item[13]  = "�������";
    $item[14]  = "�Ķȳ����פ���¾";
    $item[15]  = "�Ķȳ����׷�";
    $item[16]  = "��ʧ��©";
    $item[17]  = "�Ķȳ����Ѥ���¾";
    $item[18]  = "�Ķȳ����ѷ�";
    $item[19]  = "�о�����";
    ///////// �ƥǡ������ݴ� ���ץ�����=0 ���ץ�ɸ��=1
    $input_data = array();
    for ($i = 0; $i < 20; $i++) {
        switch ($i) {
                case  0:                                            // ����
                    $input_data[$i][0] = $ctoku_uri;                // ���ץ�����
                    $input_data[$i][1] = $ch_uri;                   // ���ץ�ɸ��
                break;
                case  1:                                            // ��������ų���ê����
                    $input_data[$i][0] = $ctoku_invent;             // ���ץ�����
                    $input_data[$i][1] = $ch_invent;                // ���ץ�ɸ��
                break;
                case  2:                                            // ������(������)
                    $input_data[$i][0] = $ctoku_metarial;           // ���ץ�����
                    $input_data[$i][1] = $ch_metarial;              // ���ץ�ɸ��
                break;
                case  3:                                            // ϫ̳��
                    $input_data[$i][0] = $ctoku_roumu;              // ���ץ�����
                    $input_data[$i][1] = $ch_roumu;                 // ���ץ�ɸ��
                break;
                case  4:                                            // ��¤����
                    $input_data[$i][0] = $ctoku_expense;            // ���ץ�����
                    $input_data[$i][1] = $ch_expense;               // ���ץ�ɸ��
                break;
                case  5:                                            // ���������ų���ê����
                    $input_data[$i][0] = $ctoku_endinv;             // ���ץ�����
                    $input_data[$i][1] = $ch_endinv;                // ���ץ�ɸ��
                break;
                case  6:                                            // ��帶��
                    $input_data[$i][0] = $ctoku_urigen;             // ���ץ�����
                    $input_data[$i][1] = $ch_urigen;                // ���ץ�ɸ��
                break;
                case  7:                                            // ���������
                    $input_data[$i][0] = $ctoku_gross_profit;       // ���ץ�����
                    $input_data[$i][1] = $ch_gross_profit;          // ���ץ�ɸ��
                break;
                case  8:                                            // �ͷ���
                    $input_data[$i][0] = $ctoku_han_jin;            // ���ץ�����
                    $input_data[$i][1] = $ch_han_jin;               // ���ץ�ɸ��
                break;
                case  9:                                            // ����
                    $input_data[$i][0] = $ctoku_han_kei;            // ���ץ�����
                    $input_data[$i][1] = $ch_han_kei;               // ���ץ�ɸ��
                break;
                case 10:                                            // �δ���ڤӰ��̴������
                    $input_data[$i][0] = $ctoku_han_all;            // ���ץ�����
                    $input_data[$i][1] = $ch_han_all;               // ���ץ�ɸ��
                break;
                case 11:                                            // �Ķ�����
                    $input_data[$i][0] = $ctoku_ope_profit;         // ���ץ�����
                    $input_data[$i][1] = $ch_ope_profit;            // ���ץ�ɸ��
                break;
                case 12:                                            // ��̳��������
                    $input_data[$i][0] = $ctoku_gyoumu;             // ���ץ�����
                    $input_data[$i][1] = $ch_gyoumu;                // ���ץ�ɸ��
                break;
                case 13:                                            // �������
                    $input_data[$i][0] = $ctoku_swari;              // ���ץ�����
                    $input_data[$i][1] = $ch_swari;                 // ���ץ�ɸ��
                break;
                case 14:                                            // �Ķȳ����פ���¾
                    $input_data[$i][0] = $ctoku_pother;             // ���ץ�����
                    $input_data[$i][1] = $ch_pother;                // ���ץ�ɸ��
                break;
                case 15:                                            // �Ķȳ����׷�
                    $input_data[$i][0] = $ctoku_nonope_profit_sum;  // ���ץ�����
                    $input_data[$i][1] = $ch_nonope_profit_sum;     // ���ץ�ɸ��
                break;
                case 16:                                            // ��ʧ��©
                    $input_data[$i][0] = $ctoku_srisoku;            // ���ץ�����
                    $input_data[$i][1] = $ch_srisoku;               // ���ץ�ɸ��
                break;
                case 17:                                            // �Ķȳ����Ѥ���¾
                    $input_data[$i][0] = $ctoku_lother;             // ���ץ�����
                    $input_data[$i][1] = $ch_lother;                // ���ץ�ɸ��
                break;
                case 18:                                            // �Ķȳ����ѷ�
                    $input_data[$i][0] = $ctoku_nonope_loss_sum;    // ���ץ�����
                    $input_data[$i][1] = $ch_nonope_loss_sum;       // ���ץ�ɸ��
                break;
                case 19:                                            // �о�����
                    $input_data[$i][0] = $ctoku_current_profit;     // ���ץ�����
                    $input_data[$i][1] = $ch_current_profit;        // ���ץ�ɸ��
                break;
                default:
                break;
            }
    }
    // ���ץ�������Ͽ
    $head  = "���ץ�����";
    $sec   = 0;
    insert_date($head,$item,$yyyymm,$input_data,$sec);
    // ���ץ�ɸ����Ͽ
    $head  = "���ץ�ɸ��";
    $sec   = 1;
    insert_date($head,$item,$yyyymm,$input_data,$sec);
}
function insert_date($head,$item,$yyyymm,$input_data,$sec) 
{
    for ($i = 0; $i < 20; $i++) {
        $item_in     = array();
        $item_in[$i] = $head . $item[$i];
        $input_data[$i][$sec] = str_replace(',','',$input_data[$i][$sec]);
        $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='%s'", $yyyymm, $item_in[$i]);
        $res_in = array();
        if (getResult2($query,$res_in) <= 0) {
            /////////// begin �ȥ�󥶥�����󳫻�
            if ($con = db_connect()) {
                query_affected_trans($con, "begin");
            } else {
                $_SESSION["s_sysmsg"] .= "�ǡ����١�������³�Ǥ��ޤ���";
                exit();
            }
            ////////// Insert Start
            $query = sprintf("insert into profit_loss_pl_history (pl_bs_ym, kin, note, last_date, last_user) values (%d, %d, '%s', CURRENT_TIMESTAMP, '%s')", $yyyymm, $input_data[$i][$sec], $item_in[$i], $_SESSION['User_ID']);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION["s_sysmsg"] .= sprintf("%s�ο�����Ͽ�˼���<br> %d", $item_in[$i], $yyyymm);
                query_affected_trans($con, "rollback");     // transaction rollback
                exit();
            }
            /////////// commit �ȥ�󥶥������λ
            query_affected_trans($con, "commit");
            $_SESSION["s_sysmsg"] = sprintf("<font color='yellow'>%d »�ץǡ��� ���� ��Ͽ��λ</font>",$yyyymm);
        } else {
            /////////// begin �ȥ�󥶥�����󳫻�
            if ($con = db_connect()) {
                query_affected_trans($con, "begin");
            } else {
                $_SESSION["s_sysmsg"] .= "�ǡ����١�������³�Ǥ��ޤ���";
                exit();
            }
            ////////// UPDATE Start
            $query = sprintf("update profit_loss_pl_history set kin=%d, last_date=CURRENT_TIMESTAMP, last_user='%s' where pl_bs_ym=%d and note='%s'", $input_data[$i][$sec], $_SESSION['User_ID'], $yyyymm, $item_in[$i]);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION["s_sysmsg"] .= sprintf("%s��UPDATE�˼���<br> %d", $item_in[$i], $yyyymm);
                query_affected_trans($con, "rollback");     // transaction rollback
                exit();
            }
            /////////// commit �ȥ�󥶥������λ
            query_affected_trans($con, "commit");
            $_SESSION["s_sysmsg"] = sprintf("<font color='yellow'>%d »�ץǡ��� �ѹ� ��λ</font>",$yyyymm);
        }
    }
    $_SESSION["s_sysmsg"] .= "����Υǡ�������Ͽ���ޤ�����";
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
                        <?php
                        if ($_SESSION['User_ID'] == '300144') {
                            if ($keta == 0 && $tani == 1) {
                        ?>
                            &nbsp;
                            <input class='pt10b' type='submit' name='input_data' value='����ǡ�����Ͽ' onClick='return data_input_click(this)'>
                        <?php
                            } else {
                        ?>
                            <input class='pt10b' type='submit' name='input_data' value='����ǡ�����Ͽ' onClick='return data_input_click(this)' disabled>
                        <?php
                            }
                        }
                        ?>
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
                    <td colspan='4' align='center' class='pt10b' bgcolor='#ffffc6'>�á�������</td>
                    <td colspan='4' align='center' class='pt10b' bgcolor='#ffffc6'>ɸ��������</td>
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
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p2_ctoku_uri ?>   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_ctoku_uri ?>   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $ctoku_uri ?>      </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $rui_ctoku_uri ?>  </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p2_ch_uri ?>   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_ch_uri ?>   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $ch_uri ?>      </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $rui_ch_uri ?>  </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p2_c_uri ?>   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_c_uri ?>   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $c_uri ?>      </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $rui_c_uri ?>  </td>
                    <td nowrap align='left'  class='pt10' bgcolor='#d6d3ce'>�º�����</td>
                </tr>
                <tr>
                    <td rowspan='6' width='10' align='center' valign='middle' class='pt10b' bgcolor='#ffffc6'>��帶��</td> <!-- ��帶�� -->
                    <td nowrap align='left' class='pt10b' bgcolor='white'>����������ų���ê����</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_ctoku_invent ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_ctoku_invent ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $ctoku_invent ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_ctoku_invent ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_ch_invent ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_ch_invent ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $ch_invent ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_ch_invent ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_c_invent ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_c_invent ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $c_invent ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_c_invent ?></td>
                    <td nowrap align='left'  class='pt10'>ɸ�ึ���ˤ��ê����</td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='#e6e6e6'>��������(������)</td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_ctoku_metarial ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_ctoku_metarial ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $ctoku_metarial ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_ctoku_metarial ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_ch_metarial ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_ch_metarial ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $ch_metarial ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_ch_metarial ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_c_metarial ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_c_metarial ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $c_metarial ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_c_metarial ?></td>
                    <td nowrap align='left'  class='pt10'>��ݹ�������</td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='white'>��ϫ����̳������</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_ctoku_roumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_ctoku_roumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $ctoku_roumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_ctoku_roumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_ch_roumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_ch_roumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $ch_roumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_ch_roumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_c_roumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_c_roumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $c_roumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_c_roumu ?></td>
                    <td nowrap align='left'  class='pt10'>�����ӥ������ڤ���Ⱦ��������</td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='#e6e6e6'>���С�����������</td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_ctoku_expense ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_ctoku_expense ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $ctoku_expense ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_ctoku_expense ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_ch_expense ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_ch_expense ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $ch_expense ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_ch_expense ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_c_expense ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_c_expense ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $c_expense ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_c_expense ?></td>
                    <td nowrap align='left'  class='pt10'>�����ӥ������ڤ���Ⱦ��������</td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='white'>�����������ų���ê����</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_ctoku_endinv ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_ctoku_endinv ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $ctoku_endinv ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $ctoku_endinv ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_ch_endinv ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_ch_endinv ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $ch_endinv ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $ch_endinv ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_c_endinv ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_c_endinv ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $c_endinv ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $c_endinv ?></td>
                    <td nowrap align='left'  class='pt10'>ɸ�ึ���ˤ��ê����</td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='#ffffc6'>���䡡�塡������</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p2_ctoku_urigen ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p1_ctoku_urigen ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $ctoku_urigen ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $rui_ctoku_urigen ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p2_ch_urigen ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p1_ch_urigen ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $ch_urigen ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $rui_ch_urigen ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p2_c_urigen ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p1_c_urigen ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $c_urigen ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $rui_c_urigen ?></td>
                    <td nowrap align='left'  class='pt10'>��</td>
                </tr>
                <tr>
                    <td colspan='2' nowrap align='center' class='pt10b' bgcolor='#ceffce'>�䡡�塡��������</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p2_ctoku_gross_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_ctoku_gross_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $ctoku_gross_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $rui_ctoku_gross_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p2_ch_gross_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_ch_gross_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $ch_gross_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $rui_ch_gross_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p2_c_gross_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_c_gross_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $c_gross_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $rui_c_gross_profit ?></td>
                    <td nowrap align='left'  class='pt10' bgcolor='#d6d3ce'>��</td>  <!-- ;�� -->
                </tr>
                <tr>
                    <td rowspan='3' width='10' align='center' valign='middle' class='pt10b' bgcolor='#ffffc6'></td> <!-- �δ��� -->
                    <td nowrap align='left' class='pt10b' bgcolor='#e6e6e6'>���͡��������</td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_ctoku_han_jin ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_ctoku_han_jin ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $ctoku_han_jin ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_ctoku_han_jin ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_ch_han_jin ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_ch_han_jin ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $ch_han_jin ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_ch_han_jin ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_c_han_jin ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_c_han_jin ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $c_han_jin ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_c_han_jin ?></td>
                    <td nowrap align='left'  class='pt10'>����Ͱ���Ψ</td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='white'>���С�����������</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_ctoku_han_kei ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_ctoku_han_kei ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $ctoku_han_kei ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_ctoku_han_kei ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_ch_han_kei ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_ch_han_kei ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $ch_han_kei ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_ch_han_kei ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_c_han_kei ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_c_han_kei ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $c_han_kei ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_c_han_kei ?></td>
                    <td nowrap align='left'  class='pt10'>����Ͱ���Ψ</td>
                </tr>
                <tr>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>�δ���ڤӰ��̴������</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p2_ctoku_han_all ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p1_ctoku_han_all ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $ctoku_han_all ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $rui_ctoku_han_all ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p2_ch_han_all ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p1_ch_han_all ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $ch_han_all ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $rui_ch_han_all ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p2_c_han_all ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p1_c_han_all ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $c_han_all ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $rui_c_han_all ?></td>
                    <td nowrap align='left'  class='pt10'>��</td>  <!-- ;�� -->
                </tr>
                <tr>
                    <td colspan='3' nowrap align='center' class='pt10b' bgcolor='#ceffce'>�ġ����ȡ�����������</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p2_ctoku_ope_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_ctoku_ope_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $ctoku_ope_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $rui_ctoku_ope_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p2_ch_ope_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_ch_ope_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $ch_ope_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $rui_ch_ope_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p2_c_ope_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_c_ope_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $c_ope_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $rui_c_ope_profit ?></td>
                    <td nowrap align='left'  class='pt10'>��</td>  <!-- ;�� -->
                </tr>
                <tr>
                    <td rowspan='7' align='center' valign='middle' class='pt10b' bgcolor='#ceffce'>�Ķȳ�»��</td>
                    <td rowspan='4' align='center' class='pt10' bgcolor='#ffffc6'></td> <!-- ;�� -->
                    <td nowrap align='left' class='pt10b' bgcolor='white'>����̳��������</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_ctoku_gyoumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_ctoku_gyoumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $ctoku_gyoumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_ctoku_gyoumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_ch_gyoumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_ch_gyoumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $ch_gyoumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_ch_gyoumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_c_gyoumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_c_gyoumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $c_gyoumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_c_gyoumu ?></td>
                    <td nowrap align='left'  class='pt10'>��Ⱦ�����Ӥ�������</td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='#e6e6e6'>���š������䡡��</td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_ctoku_swari ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_ctoku_swari ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $ctoku_swari ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_ctoku_swari ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_ch_swari ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_ch_swari ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $ch_swari ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_ch_swari ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_c_swari ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_c_swari ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $c_swari ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_c_swari ?></td>
                    <td nowrap align='left'  class='pt10'>��Ⱦ�����Ӥ�������</td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='white'>���������Ρ���¾</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_ctoku_pother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_ctoku_pother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $ctoku_pother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_ctoku_pother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_ch_pother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_ch_pother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $ch_pother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_ch_pother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_c_pother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_c_pother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $c_pother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_c_pother ?></td>
                    <td nowrap align='left'  class='pt10'>��Ⱦ�����Ӥ�������</td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='#ffffc6'>���Ķȳ����� ��</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p2_ctoku_nonope_profit_sum ?>   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p1_ctoku_nonope_profit_sum ?>   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $ctoku_nonope_profit_sum ?>      </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $rui_ctoku_nonope_profit_sum ?>  </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p2_ch_nonope_profit_sum ?>   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p1_ch_nonope_profit_sum ?>   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $ch_nonope_profit_sum ?>      </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $rui_ch_nonope_profit_sum ?>  </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p2_c_nonope_profit_sum ?>   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p1_c_nonope_profit_sum ?>   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $c_nonope_profit_sum ?>      </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $rui_c_nonope_profit_sum ?>  </td>
                    <td nowrap align='left'  class='pt10'>��</td> <!-- ;�� -->
                </tr>
                <tr>
                    <td rowspan='3' align='center' class='pt10' bgcolor='#ffffc6'></td> <!-- ;�� -->
                    <td nowrap align='left' class='pt10b' bgcolor='#e6e6e6'>���١�ʧ������©</td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_ctoku_srisoku ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_ctoku_srisoku ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $ctoku_srisoku ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_ctoku_srisoku ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_ch_srisoku ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_ch_srisoku ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $ch_srisoku ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_ch_srisoku ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_c_srisoku ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_c_srisoku ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $c_srisoku ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_c_srisoku ?></td>
                    <td nowrap align='left'  class='pt10'>��Ⱦ�����Ӥ�������</td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='white'>���������Ρ���¾</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_ctoku_lother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_ctoku_lother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $ctoku_lother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_ctoku_lother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_ch_lother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_ch_lother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $ch_lother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_ch_lother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_c_lother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_c_lother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $c_lother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_c_lother ?></td>
                    <td nowrap align='left'  class='pt10'>��Ⱦ�����Ӥ�������</td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='#ffffc6'>���Ķȳ����� ��</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p2_ctoku_nonope_loss_sum ?>   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p1_ctoku_nonope_loss_sum ?>   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $ctoku_nonope_loss_sum ?>      </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $rui_ctoku_nonope_loss_sum ?>  </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p2_ch_nonope_loss_sum ?>   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p1_ch_nonope_loss_sum ?>   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $ch_nonope_loss_sum ?>      </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $rui_ch_nonope_loss_sum ?>  </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p2_c_nonope_loss_sum ?>   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p1_c_nonope_loss_sum ?>   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $c_nonope_loss_sum ?>      </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $rui_c_nonope_loss_sum ?>  </td>
                    <td nowrap align='left'  class='pt10'>��</td> <!-- ;�� -->
                </tr>
                <tr>
                    <td colspan='3' nowrap align='center' class='pt10b' bgcolor='#ceffce'>�С��������������</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p2_ctoku_current_profit ?>   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_ctoku_current_profit ?>   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $ctoku_current_profit ?>      </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $rui_ctoku_current_profit ?>  </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p2_ch_current_profit ?>   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_ch_current_profit ?>   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $ch_current_profit ?>      </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $rui_ch_current_profit ?>  </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p2_c_current_profit ?>   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_c_current_profit ?>   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $c_current_profit ?>      </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $rui_c_current_profit ?>  </td>
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
                            if ($comment_c != "") {
                                echo "<li><pre>$comment_c</pre></li>\n";
                            }
                            if ($comment_ctoku != "") {
                                echo "<li><pre>$comment_ctoku</pre></li>\n";
                            }
                            if ($comment_all != "") {
                                echo "<li><pre>$comment_all</pre></li>\n";
                            }
                            if ($comment_other != "") {
                                echo "<li><pre>$comment_other</pre></li>\n";
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
