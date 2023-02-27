<?php
//////////////////////////////////////////////////////////////////////////////
// �»�״ط��θ���Ψ�׻�ɽ(������Ψ�Ż� ��ܴƻ�)                        //
// Copyright (C) 2003-2005 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2003/03/12 Created   profit_loss_cost_rate.php                           //
// 2003/03/13 StyleSheet��<link ������ ��󥯥ե�����Υ����Ȥ�           //
//                                      /* ... */ �Σ��ԤˤΤ��б�(NN6.1)   //
// 2003/03/27 ��Ⱦ������Ⱦ���Υ��å��ѹ� ����������ʿ�Ѥ���å���     //
// 2004/05/11 ��¦�Υ����ȥ�˥塼�Υ��󡦥��� �ܥ�����ɲ�                 //
// 2004/09/07 ��פκ�����ϫ̳������Excel�η׻�ˡ��ˡ�˹�碌�뤿���ѹ� //
// 2005/06/15 MenuHeader class ����Ѥ��ƶ��̥�˥塼���ڤ�ǧ���������ѹ�   //
// 2005/08/29 set_focus()����Ȥ����ƥ����ȥ����� MenuHeader�˰ܹԤΤ���  //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��
require_once ('../function.php');           // define.php �� pgsql.php �� require_once ���Ƥ���
require_once ('../tnk_func.php');           // TNK �˰�¸������ʬ�δؿ��� require_once ���Ƥ���
require_once ('../MenuHeader.php');         // TNK ������ menu class
access_log();                               // Script Name �ϼ�ư����

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader(0);                  // ǧ�ڥ����å�0=���̰ʾ� �����=TOP_MENU �����ȥ�̤����

////////////// ����������
// $menu->set_site(10, 7);                     // site_index=99(�����ƥ��˥塼) site_id=60(�ƥ�ץ졼��)
                                            // �����֡��軻 = 10 �Ǹ�Υ�˥塼 = 99 �����
                                            // �»�״ط� = 7  ���̥�˥塼̵�� <= 0

$current_script  = $menu->out_self();       // ���߼¹���Υ�����ץ�̾����¸
$url_referer     = H_WEB_HOST . $menu->out_RetUrl();    // �ƽи��򥻥åȤ���

///// ������μ���
$ki = Ym_to_tnk($_SESSION['pl_ym']);
$tuki = substr($_SESSION['pl_ym'],4,2);
///// �о�����
$yyyymm = (int)$_SESSION['pl_ym'];

//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title("�� {$ki} ����{$tuki} ���١��� �� Ψ �� �� ɽ ");

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

/****** ����إ��åȤ���ؿ� *****/
function forward_ym($ym) {
    if ($ym == "") {
        return FALSE;
    } else {
        $ym++;
        $yyyy = substr($ym, 0, 4);
        $mm   = substr($ym, 4, 2);
        if ($mm > 12) {
            $mm = "01";
            $yyyy++;
        }
        return (int)($yyyy . $mm);
    }
}
/***** ��֡������軻ǯ��μ�������ؿ� Backward *****/
function act_settl_ym($y4m2) {
    $yyyy = substr($y4m2, 0,4);
    $mm   = substr($y4m2, 4,2);
    if (($mm >= 1) && ($mm <= 3)) {
        $yyyy = ($yyyy - 1);                // ��ǯ�˥��å�
        return ($yyyy . "09");              // ���ǯ��
    } elseif (($mm >= 10) && ($mm <=12)) {
        return ($yyyy . "09");              // ���ǯ��
    } else {
        return ($yyyy . "03");              // ����ǯ��
    }
}
/***** ��֡������軻ǯ��μ�������ؿ� Forward *****/
function act_settl_ym_forward($y4m2) {
    $yyyy = substr($y4m2, 0,4);
    $mm   = substr($y4m2, 4,2);
    if (($mm >= 1) && ($mm <= 3)) {
        return ($yyyy . "09");              // ���ǯ��
    } elseif (($mm >= 10) && ($mm <=12)) {
        $yyyy = ($yyyy + 1);                // ��ǯ�˥��å�
        return ($yyyy . "03");              // ���ǯ��
    } else {
        $yyyy = ($yyyy + 1);                // ��ǯ�˥��å�
        return ($yyyy . "03");              // ����ǯ��
    }
}
///// �嵭�δؿ���¹�
$str_ym = act_settl_ym($yyyymm);

///// pl_str_ym �ν����
if ((!isset($_POST['backward_ki'])) && (!isset($_POST['forward_ki'])) ) {
    unset($_SESSION['pl_str_ym']);
}
///// �����ܥ��󤬲����줿���ν���
if (isset($_POST['backward_ki'])) {
    if (isset($_SESSION['pl_str_ym'])) {
        $str_ym = act_settl_ym($_SESSION['pl_str_ym']);
        if ($str_ym >= 200103) {
            $_SESSION['pl_str_ym'] = $str_ym;
        } else {
            $_SESSION['s_sysmsg'] .= sprintf("<font color='yellow'>%d �δ����ǡ����Ϥ���ޤ���</font>", $str_ym);
            $str_ym = $_SESSION['pl_str_ym'];
        }
    } else {
        $str_ym = act_settl_ym($str_ym);
        if ($str_ym >= 200103) {
            $_SESSION['pl_str_ym'] = $str_ym;
        } else {
            $_SESSION['s_sysmsg'] .= sprintf("<font color='yellow'>%d �δ����ǡ����Ϥ���ޤ���</font>", $str_ym);
            $str_ym = act_settl_ym($yyyymm);
        }
    }
}
///// �����ܥ��󤬲����줿���ν���
$today_ym = date("Ym");
if (isset($_POST['forward_ki'])) {
    if (isset($_SESSION['pl_str_ym'])) {
        $str_ym = act_settl_ym_forward($_SESSION['pl_str_ym']);
        if ($str_ym < $today_ym) {
            $_SESSION['pl_str_ym'] = $str_ym;
        } else {
            $_SESSION['s_sysmsg'] .= sprintf("<font color='yellow'>%d �δ����ǡ����Ϥ���ޤ���</font>", $str_ym);
            $str_ym = $_SESSION['pl_str_ym'];
        }
    } else {
        $str_ym = act_settl_ym_forward($str_ym);
        if ($str_ym < $today_ym) {
            $_SESSION['pl_str_ym'] = $str_ym;
        } else {
            $_SESSION['s_sysmsg'] .= sprintf("<font color='yellow'>%d �δ����ǡ����Ϥ���ޤ���</font>", $str_ym);
            $str_ym = act_settl_ym($yyyymm);
        }
    }
}

///// ɽ��ñ�̤��������
if (isset($_POST['costrate_tani'])) {
    $_SESSION['costrate_tani'] = $_POST['costrate_tani'];
    $tani = $_SESSION['costrate_tani'];
} elseif (isset($_SESSION['costrate_tani'])) {
    $tani = $_SESSION['costrate_tani'];
} else {
    $tani = 1000;        // ����� ɽ��ñ�� ���
    $_SESSION['costrate_tani'] = $tani;
}
///// ɽ�� ��������� �������
if (isset($_POST['costrate_keta'])) {
    $_SESSION['costrate_keta'] = $_POST['costrate_keta'];
    $keta = $_SESSION['costrate_keta'];
} elseif (isset($_SESSION['costrate_keta'])) {
    $keta = $_SESSION['costrate_keta'];
} else {
    $keta = 0;          // ����� �������ʲ����
    $_SESSION['costrate_keta'] = $keta;
}

//################################################################################################
///// ����������(��帶��Ψ)��ʿ���ͤλ���
$pre_str_ym = act_settl_ym($str_ym);        // �������δ����˥��å�
if ($pre_str_ym <= 200009) {
    $pre_str_ym = $str_ym;                  // �����Ω�Υ����å�
}
    // ���ץ�
$query = sprintf("select sum(kin) from act_invent_gross_average_history where pl_bs_ym=%d and note like '���ץ�%%������'", $pre_str_ym);
getUniResult($query, $invent_zai_c);
if ($invent_zai_c == 0) {
    $_SESSION['s_sysmsg'] .= sprintf("��ʿ�Ѵ���ê���� ̤��Ͽ<br>�軻ǯ��=%d", $pre_str_ym);
    header("Location: $url_referer");
    exit();
}
$query = sprintf("select sum(kin) from act_invent_gross_average_history where pl_bs_ym=%d and note like '���ץ�%%'", $pre_str_ym);
getUniResult($query, $invent_sum_c);
$invent_kei_c = ($invent_sum_c - $invent_zai_c);      // ��פ��鳰����κ�����򺹤��������Τ�ϫ̳�񡦷���
    // ��˥�
$query = sprintf("select sum(kin) from act_invent_gross_average_history where pl_bs_ym=%d and note like '��˥�%%������'", $pre_str_ym);
getUniResult($query, $invent_zai_l);
$query = sprintf("select sum(kin) from act_invent_gross_average_history where pl_bs_ym=%d and note like '��˥�%%'", $pre_str_ym);
getUniResult($query, $invent_sum_l);
$invent_kei_l = ($invent_sum_l - $invent_zai_l);      // ��פ��鳰����κ�����򺹤��������Τ�ϫ̳�񡦷���
    // ���
$invent_zai_all = ($invent_zai_c + $invent_zai_l);
$invent_kei_all = ($invent_kei_c + $invent_kei_l);
$invent_sum_all = ($invent_sum_c + $invent_sum_l);
///// �������ϫ̳�񡦷���γ��׻�
$percent_zai_c   = number_format(($invent_zai_c / $invent_sum_c * 100), 1);
$p_zai_c         = ($percent_zai_c / 100);      // �׻���
$percent_kei_c   = number_format((100 - $percent_zai_c), 1);
$percent_sum_c   = number_format(($percent_zai_c + $percent_kei_c), 1);
$percent_zai_l   = number_format(($invent_zai_l / $invent_sum_l * 100), 1);
$p_zai_l         = ($percent_zai_l / 100);
$percent_kei_l   = number_format((100 - $percent_zai_l), 1);
$percent_sum_l   = number_format(($percent_zai_l + $percent_kei_l), 1);
$percent_zai_all = number_format(($invent_zai_all / $invent_sum_all * 100), 1);
$p_zai_a         = ($percent_zai_all / 100);
$percent_kei_all = number_format((100 - $percent_zai_all), 1);
$percent_sum_all = number_format(($percent_zai_all + $percent_kei_all), 1);
///// ����ǡ���������֤˽���
$data      = array();
$view_data = array();
$tmp_ym    = $pre_str_ym;
for ($cnt=0; $cnt < 6; $cnt++) {
    $data[$cnt]['ym']      = forward_ym($tmp_ym);       // Ⱦ����ǯ��鼡������
    $tmp_ym = $data[$cnt]['ym'];
    ///// ����μ���
    $query = sprintf("select ���ץ�, ��˥�, ���� from wrk_uriage where ǯ��=%d", $data[$cnt]['ym']);
    $res_uri = array();
    if (getResult($query, $res_uri) <= 0) {
        $_SESSION['s_sysmsg'] .= sprintf("������̤��Ͽ<br>ǯ��=%d", $data[$cnt]['ym']);
        header("Location: $url_referer");
        exit();
    } else {
        $data[$cnt]['uri_c'] = $res_uri[0]['���ץ�'];
        $data[$cnt]['uri_l'] = $res_uri[0]['��˥�'];
        $data[$cnt]['uri_a'] = $res_uri[0]['����'];
    }
    if ($cnt == 0) {        ///// ����ν���
        ///// ����ê����
        $data[$cnt]['s_tana_zai_c'] = $invent_zai_c;
        $data[$cnt]['s_tana_kei_c'] = $invent_kei_c;
        $data[$cnt]['s_tana_sum_c'] = $invent_sum_c;
        $data[$cnt]['s_tana_zai_l'] = $invent_zai_l;
        $data[$cnt]['s_tana_kei_l'] = $invent_kei_l;
        $data[$cnt]['s_tana_sum_l'] = $invent_sum_l;
        $data[$cnt]['s_tana_zai_a'] = $invent_zai_all;
        $data[$cnt]['s_tana_kei_a'] = $invent_kei_all;
        $data[$cnt]['s_tana_sum_a'] = $invent_sum_all;
    } else {            ///// �̾��ν���
        ///// ����ê����
        $data[$cnt]['s_tana_zai_c'] = ($data[$cnt-1]['e_tana_zai_c'] * (-1));
        $data[$cnt]['s_tana_kei_c'] = ($data[$cnt-1]['e_tana_kei_c'] * (-1));
        $data[$cnt]['s_tana_sum_c'] = ($data[$cnt-1]['e_tana_sum_c'] * (-1));
        $data[$cnt]['s_tana_zai_l'] = ($data[$cnt-1]['e_tana_zai_l'] * (-1));
        $data[$cnt]['s_tana_kei_l'] = ($data[$cnt-1]['e_tana_kei_l'] * (-1));
        $data[$cnt]['s_tana_sum_l'] = ($data[$cnt-1]['e_tana_sum_l'] * (-1));
        $data[$cnt]['s_tana_zai_a'] = ($data[$cnt-1]['e_tana_zai_a'] * (-1));
        $data[$cnt]['s_tana_kei_a'] = ($data[$cnt-1]['e_tana_kei_a'] * (-1));
        $data[$cnt]['s_tana_sum_a'] = ($data[$cnt-1]['e_tana_sum_a'] * (-1));
    }
    ///// �������ȯ����
        // ������
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ������'", $data[$cnt]['ym']);
    if (getUniResult($query, $metarial_c) < 1) {
        $_SESSION['s_sysmsg'] .= sprintf("���ץ������̤��Ͽ<br>ǯ��=%d", $data[$cnt]['ym']);
        header("Location: $url_referer");
        exit();
    } else {
        $data[$cnt]['metarial_c']      = $metarial_c;
    }
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥�������'", $data[$cnt]['ym']);
    if (getUniResult($query, $metarial_l) < 1) {
        $_SESSION['s_sysmsg'] .= sprintf("��˥�������̤��Ͽ<br>ǯ��=%d", $data[$cnt]['ym']);
        header("Location: $url_referer");
        exit();
    } else {
        $data[$cnt]['metarial_l']      = $metarial_l;
    }
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���κ�����'", $data[$cnt]['ym']);
    if (getUniResult($query, $metarial_a) < 1) {
        $_SESSION['s_sysmsg'] .= sprintf("���κ�����̤��Ͽ<br>ǯ��=%d", $data[$cnt]['ym']);
        header("Location: $url_referer");
        exit();
    } else {
        $data[$cnt]['metarial_a']      = $metarial_a;
    }
    
        // ϫ̳�񡦷���
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�ϫ̳��'", $data[$cnt]['ym']);
    if (getUniResult($query, $roumu_c) < 1) {
        $_SESSION['s_sysmsg'] .= sprintf("���ץ�ϫ̳��̤��Ͽ<br>ǯ��=%d", $data[$cnt]['ym']);
        header("Location: $url_referer");
        exit();
    }
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥�ϫ̳��'", $data[$cnt]['ym']);
    if (getUniResult($query, $roumu_l) < 1) {
        $_SESSION['s_sysmsg'] .= sprintf("���ץ�ϫ̳��̤��Ͽ<br>ǯ��=%d", $data[$cnt]['ym']);
        header("Location: $url_referer");
        exit();
    }
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='����ϫ̳��'", $data[$cnt]['ym']);
    if (getUniResult($query, $roumu_a) < 1) {
        $_SESSION['s_sysmsg'] .= sprintf("����ϫ̳��̤��Ͽ<br>ǯ��=%d", $data[$cnt]['ym']);
        header("Location: $url_referer");
        exit();
    }
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ���¤����'", $data[$cnt]['ym']);
    if (getUniResult($query, $keihi_c) < 1) {
        $_SESSION['s_sysmsg'] .= sprintf("���ץ���¤����̤��Ͽ<br>ǯ��=%d", $data[$cnt]['ym']);
        header("Location: $url_referer");
        exit();
    }
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥���¤����'", $data[$cnt]['ym']);
    if (getUniResult($query, $keihi_l) < 1) {
        $_SESSION['s_sysmsg'] .= sprintf("��˥���¤����̤��Ͽ<br>ǯ��=%d", $data[$cnt]['ym']);
        header("Location: $url_referer");
        exit();
    }
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='������¤����'", $data[$cnt]['ym']);
    if (getUniResult($query, $keihi_a) < 1) {
        $_SESSION['s_sysmsg'] .= sprintf("������¤����̤��Ͽ<br>ǯ��=%d", $data[$cnt]['ym']);
        header("Location: $url_referer");
        exit();
    }
    $data[$cnt]['expense_c'] = ($roumu_c + $keihi_c);
    $data[$cnt]['expense_l'] = ($roumu_l + $keihi_l);
    $data[$cnt]['expense_a'] = ($roumu_a + $keihi_a);
    
    $data[$cnt]['shi_c'] = $data[$cnt]['metarial_c'] + $data[$cnt]['expense_c'];
    $data[$cnt]['shi_l'] = $data[$cnt]['metarial_l'] + $data[$cnt]['expense_l'];
    $data[$cnt]['shi_a'] = $data[$cnt]['metarial_a'] + $data[$cnt]['expense_a'];
    ///// ����ê����
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ����ê����'", $data[$cnt]['ym']);
    if (getUniResult($query, $e_tana_c) < 1) {
        $_SESSION['s_sysmsg'] .= sprintf("���ץ����ê���⤬̤��Ͽ<br>ǯ��=%d", $data[$cnt]['ym']);
        header("Location: $url_referer");
        exit();
    } else {
        $data[$cnt]['e_tana_sum_c'] = $e_tana_c;
        $data[$cnt]['e_tana_zai_c'] = Uround(($p_zai_c * $data[$cnt]['e_tana_sum_c']),0);
        $data[$cnt]['e_tana_kei_c'] = $data[$cnt]['e_tana_sum_c'] - $data[$cnt]['e_tana_zai_c'];
    }
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥�����ê����'", $data[$cnt]['ym']);
    if (getUniResult($query, $e_tana_l) < 1) {
        $_SESSION['s_sysmsg'] .= sprintf("��˥�����ê���⤬̤��Ͽ<br>ǯ��=%d", $data[$cnt]['ym']);
        header("Location: $url_referer");
        exit();
    } else {
        $data[$cnt]['e_tana_sum_l'] = $e_tana_l;
        $data[$cnt]['e_tana_zai_l'] = Uround(($p_zai_l * $data[$cnt]['e_tana_sum_l']),0);
        $data[$cnt]['e_tana_kei_l'] = $data[$cnt]['e_tana_sum_l'] - $data[$cnt]['e_tana_zai_l'];
    }
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���δ���ê����'", $data[$cnt]['ym']);
    if (getUniResult($query, $e_tana_a) < 1) {
        $_SESSION['s_sysmsg'] .= sprintf("���δ���ê���⤬̤��Ͽ<br>ǯ��=%d", $data[$cnt]['ym']);
        header("Location: $url_referer");
        exit();
    } else {
        $data[$cnt]['e_tana_sum_a'] = $e_tana_a;
        $data[$cnt]['e_tana_zai_a'] = Uround(($p_zai_a * $data[$cnt]['e_tana_sum_a']),0);
        $data[$cnt]['e_tana_kei_a'] = $data[$cnt]['e_tana_sum_a'] - $data[$cnt]['e_tana_zai_a'];
    }
    $data[$cnt]['e_tana_zai_c'] = ($data[$cnt]['e_tana_zai_c'] * (-1));     // ���ȿž
    $data[$cnt]['e_tana_kei_c'] = ($data[$cnt]['e_tana_kei_c'] * (-1));
    $data[$cnt]['e_tana_sum_c'] = ($data[$cnt]['e_tana_sum_c'] * (-1));
    $data[$cnt]['e_tana_zai_l'] = ($data[$cnt]['e_tana_zai_l'] * (-1));
    $data[$cnt]['e_tana_kei_l'] = ($data[$cnt]['e_tana_kei_l'] * (-1));
    $data[$cnt]['e_tana_sum_l'] = ($data[$cnt]['e_tana_sum_l'] * (-1));
    $data[$cnt]['e_tana_zai_a'] = ($data[$cnt]['e_tana_zai_a'] * (-1));
    $data[$cnt]['e_tana_kei_a'] = ($data[$cnt]['e_tana_kei_a'] * (-1));
    $data[$cnt]['e_tana_sum_a'] = ($data[$cnt]['e_tana_sum_a'] * (-1));
    ///// ��帶���η׻�
        // ����ê���� �� �������(����)ȯ��(����) �� �ʡݴ���ê����) ��ƹ�����˷׻�����
    $data[$cnt]['gen_zai_c'] = $data[$cnt]['s_tana_zai_c'] + $data[$cnt]['metarial_c'] + ($data[$cnt]['e_tana_zai_c']);
    $data[$cnt]['gen_kei_c'] = $data[$cnt]['s_tana_kei_c'] + $data[$cnt]['expense_c']  + ($data[$cnt]['e_tana_kei_c']);
    $data[$cnt]['gen_sum_c'] = $data[$cnt]['s_tana_sum_c'] + $data[$cnt]['shi_c']      + ($data[$cnt]['e_tana_sum_c']);
    $data[$cnt]['gen_zai_l'] = $data[$cnt]['s_tana_zai_l'] + $data[$cnt]['metarial_l'] + ($data[$cnt]['e_tana_zai_l']);
    $data[$cnt]['gen_kei_l'] = $data[$cnt]['s_tana_kei_l'] + $data[$cnt]['expense_l']  + ($data[$cnt]['e_tana_kei_l']);
    $data[$cnt]['gen_sum_l'] = $data[$cnt]['s_tana_sum_l'] + $data[$cnt]['shi_l']      + ($data[$cnt]['e_tana_sum_l']);
    $data[$cnt]['gen_zai_a'] = $data[$cnt]['s_tana_zai_a'] + $data[$cnt]['metarial_a'] + ($data[$cnt]['e_tana_zai_a']);
    $data[$cnt]['gen_kei_a'] = $data[$cnt]['s_tana_kei_a'] + $data[$cnt]['expense_a']  + ($data[$cnt]['e_tana_kei_a']);
    $data[$cnt]['gen_sum_a'] = $data[$cnt]['s_tana_sum_a'] + $data[$cnt]['shi_a']      + ($data[$cnt]['e_tana_sum_a']);
    ///// ������(��帶��Ψ)
        // ��帶�� �� ���� �� ��帶��Ψ(������)  �ƹ�����˷׻�����
    $data[$cnt]['ritu_zai_c'] = Uround(($data[$cnt]['gen_zai_c'] / $data[$cnt]['uri_c']) * 100, 1);
    $data[$cnt]['ritu_kei_c'] = Uround(($data[$cnt]['gen_kei_c'] / $data[$cnt]['uri_c']) * 100, 1);
    $data[$cnt]['ritu_sum_c'] = Uround(($data[$cnt]['gen_sum_c'] / $data[$cnt]['uri_c']) * 100, 1);
    $data[$cnt]['ritu_zai_l'] = Uround(($data[$cnt]['gen_zai_l'] / $data[$cnt]['uri_l']) * 100, 1);
    $data[$cnt]['ritu_kei_l'] = Uround(($data[$cnt]['gen_kei_l'] / $data[$cnt]['uri_l']) * 100, 1);
    $data[$cnt]['ritu_sum_l'] = Uround(($data[$cnt]['gen_sum_l'] / $data[$cnt]['uri_l']) * 100, 1);
    $data[$cnt]['ritu_zai_a'] = Uround(($data[$cnt]['gen_zai_a'] / $data[$cnt]['uri_a']) * 100, 1);
    $data[$cnt]['ritu_kei_a'] = Uround(($data[$cnt]['gen_kei_a'] / $data[$cnt]['uri_a']) * 100, 1);
    $data[$cnt]['ritu_sum_a'] = Uround(($data[$cnt]['gen_sum_a'] / $data[$cnt]['uri_a']) * 100, 1);
    
    $view_data[$cnt]['ritu_zai_c'] = number_format($data[$cnt]['ritu_zai_c'], 1);
    $view_data[$cnt]['ritu_kei_c'] = number_format($data[$cnt]['ritu_kei_c'], 1);
    $view_data[$cnt]['ritu_sum_c'] = number_format($data[$cnt]['ritu_sum_c'], 1);
    $view_data[$cnt]['ritu_zai_l'] = number_format($data[$cnt]['ritu_zai_l'], 1);
    $view_data[$cnt]['ritu_kei_l'] = number_format($data[$cnt]['ritu_kei_l'], 1);
    $view_data[$cnt]['ritu_sum_l'] = number_format($data[$cnt]['ritu_sum_l'], 1);
    $view_data[$cnt]['ritu_zai_a'] = number_format($data[$cnt]['ritu_zai_a'], 1);
    $view_data[$cnt]['ritu_kei_a'] = number_format($data[$cnt]['ritu_kei_a'], 1);
    $view_data[$cnt]['ritu_sum_a'] = number_format($data[$cnt]['ritu_sum_a'], 1);
    
}
$pre_ritu_zai_c = 0;        // �����
$pre_ritu_kei_c = 0;
$pre_ritu_sum_c = 0;
$pre_ritu_zai_l = 0;
$pre_ritu_kei_l = 0;
$pre_ritu_sum_l = 0;
$pre_ritu_zai_a = 0;
$pre_ritu_kei_a = 0;
$pre_ritu_sum_a = 0;
for ($cnt = 0; $cnt < 6; $cnt++) {
    $pre_ritu_zai_c += $data[$cnt]['ritu_zai_c'];
    $pre_ritu_kei_c += $data[$cnt]['ritu_kei_c'];
    $pre_ritu_sum_c += $data[$cnt]['ritu_sum_c'];
    $pre_ritu_zai_l += $data[$cnt]['ritu_zai_l'];
    $pre_ritu_kei_l += $data[$cnt]['ritu_kei_l'];
    $pre_ritu_sum_l += $data[$cnt]['ritu_sum_l'];
    $pre_ritu_zai_a += $data[$cnt]['ritu_zai_a'];
    $pre_ritu_kei_a += $data[$cnt]['ritu_kei_a'];
    $pre_ritu_sum_a += $data[$cnt]['ritu_sum_a'];
}
$view_pre_ritu_zai_c = number_format($pre_ritu_zai_c / 6, 1);
$view_pre_ritu_kei_c = number_format($pre_ritu_kei_c / 6, 1);
$view_pre_ritu_sum_c = number_format($pre_ritu_sum_c / 6, 1);
$view_pre_ritu_zai_l = number_format($pre_ritu_zai_l / 6, 1);
$view_pre_ritu_kei_l = number_format($pre_ritu_kei_l / 6, 1);
$view_pre_ritu_sum_l = number_format($pre_ritu_sum_l / 6, 1);
$view_pre_ritu_zai_a = number_format($pre_ritu_zai_a / 6, 1);
$view_pre_ritu_kei_a = number_format($pre_ritu_kei_a / 6, 1);
$view_pre_ritu_sum_a = number_format($pre_ritu_sum_a / 6, 1);
//################################################################################################

///// �軻�����ʿ��ñ���ˤ�����ê��������ټ���
    // ���ץ�
$query = sprintf("select sum(kin) from act_invent_gross_average_history where pl_bs_ym=%d and note like '���ץ�%%������'", $str_ym);
getUniResult($query, $invent_zai_c);
if ($invent_zai_c == 0) {
    $_SESSION['s_sysmsg'] .= sprintf("��ʿ�Ѵ���ê���� ̤��Ͽ<br>�軻ǯ��=%d", $str_ym);
    header("Location: $url_referer");
    exit();
}
$query = sprintf("select sum(kin) from act_invent_gross_average_history where pl_bs_ym=%d and note like '���ץ�%%'", $str_ym);
getUniResult($query, $invent_sum_c);
$invent_kei_c = ($invent_sum_c - $invent_zai_c);      // ��פ��鳰����κ�����򺹤��������Τ�ϫ̳�񡦷���
    // ��˥�
$query = sprintf("select sum(kin) from act_invent_gross_average_history where pl_bs_ym=%d and note like '��˥�%%������'", $str_ym);
getUniResult($query, $invent_zai_l);
$query = sprintf("select sum(kin) from act_invent_gross_average_history where pl_bs_ym=%d and note like '��˥�%%'", $str_ym);
getUniResult($query, $invent_sum_l);
$invent_kei_l = ($invent_sum_l - $invent_zai_l);      // ��פ��鳰����κ�����򺹤��������Τ�ϫ̳�񡦷���
    // ���
$invent_zai_all = ($invent_zai_c + $invent_zai_l);
$invent_kei_all = ($invent_kei_c + $invent_kei_l);
$invent_sum_all = ($invent_sum_c + $invent_sum_l);
///// �������ϫ̳�񡦷���γ��׻�
$percent_zai_c   = number_format(($invent_zai_c / $invent_sum_c * 100), 1);
$p_zai_c         = ($percent_zai_c / 100);      // �׻���
$percent_kei_c   = number_format((100 - $percent_zai_c), 1);
$percent_sum_c   = number_format(($percent_zai_c + $percent_kei_c), 1);
$percent_zai_l   = number_format(($invent_zai_l / $invent_sum_l * 100), 1);
$p_zai_l         = ($percent_zai_l / 100);
$percent_kei_l   = number_format((100 - $percent_zai_l), 1);
$percent_sum_l   = number_format(($percent_zai_l + $percent_kei_l), 1);
$percent_zai_all = number_format(($invent_zai_all / $invent_sum_all * 100), 1);
$p_zai_a         = ($percent_zai_all / 100);
$percent_kei_all = number_format((100 - $percent_zai_all), 1);
$percent_sum_all = number_format(($percent_zai_all + $percent_kei_all), 1);
    // view data ����
$view_i_zai_c   = number_format($invent_zai_c / $tani, $keta);
$view_i_kei_c   = number_format($invent_kei_c / $tani, $keta);
$view_i_sum_c   = number_format($invent_sum_c / $tani, $keta);
$view_i_zai_l   = number_format($invent_zai_l / $tani, $keta);
$view_i_kei_l   = number_format($invent_kei_l / $tani, $keta);
$view_i_sum_l   = number_format($invent_sum_l / $tani, $keta);
$view_i_zai_all = number_format($invent_zai_all / $tani, $keta);
$view_i_kei_all = number_format($invent_kei_all / $tani, $keta);
$view_i_sum_all = number_format($invent_sum_all / $tani, $keta);
///// ����ǡ���������֤˽���
$data      = array();
$view_data = array();
$tmp_ym    = $str_ym;
for ($cnt=0; $tmp_ym < $yyyymm; $cnt++) {
    $data[$cnt]['ym']      = forward_ym($tmp_ym);       // Ⱦ����ǯ��鼡������
    $view_data[$cnt]['ym'] = (substr($data[$cnt]['ym'], 0, 4) . "/" . substr($data[$cnt]['ym'], 4, 2));
    $tmp_ym = $data[$cnt]['ym'];
    ///// ����μ���
    $query = sprintf("select ���ץ�, ��˥�, ���� from wrk_uriage where ǯ��=%d", $data[$cnt]['ym']);
    $res_uri = array();
    if (getResult($query, $res_uri) <= 0) {
        $_SESSION['s_sysmsg'] .= sprintf("������̤��Ͽ<br>ǯ��=%d", $data[$cnt]['ym']);
        header("Location: $url_referer");
        exit();
    } else {
        $data[$cnt]['uri_c'] = $res_uri[0]['���ץ�'];
        $data[$cnt]['uri_l'] = $res_uri[0]['��˥�'];
        $data[$cnt]['uri_a'] = $res_uri[0]['����'];
        $view_data[$cnt]['uri_c'] = number_format($data[$cnt]['uri_c'] / $tani, $keta);
        $view_data[$cnt]['uri_l'] = number_format($data[$cnt]['uri_l'] / $tani, $keta);
        $view_data[$cnt]['uri_a'] = number_format($data[$cnt]['uri_a'] / $tani, $keta);
    }
    if ($cnt == 0) {        ///// ����ν���
        ///// ����ê����
        $data[$cnt]['s_tana_zai_c'] = $invent_zai_c;
        $data[$cnt]['s_tana_kei_c'] = $invent_kei_c;
        $data[$cnt]['s_tana_sum_c'] = $invent_sum_c;
        $data[$cnt]['s_tana_zai_l'] = $invent_zai_l;
        $data[$cnt]['s_tana_kei_l'] = $invent_kei_l;
        $data[$cnt]['s_tana_sum_l'] = $invent_sum_l;
        $data[$cnt]['s_tana_zai_a'] = $invent_zai_all;
        $data[$cnt]['s_tana_kei_a'] = $invent_kei_all;
        $data[$cnt]['s_tana_sum_a'] = $invent_sum_all;
        $view_data[$cnt]['s_tana_zai_c'] = $view_i_zai_c  ;
        $view_data[$cnt]['s_tana_kei_c'] = $view_i_kei_c  ;
        $view_data[$cnt]['s_tana_sum_c'] = $view_i_sum_c  ;
        $view_data[$cnt]['s_tana_zai_l'] = $view_i_zai_l  ;
        $view_data[$cnt]['s_tana_kei_l'] = $view_i_kei_l  ;
        $view_data[$cnt]['s_tana_sum_l'] = $view_i_sum_l  ;
        $view_data[$cnt]['s_tana_zai_a'] = $view_i_zai_all;
        $view_data[$cnt]['s_tana_kei_a'] = $view_i_kei_all;
        $view_data[$cnt]['s_tana_sum_a'] = $view_i_sum_all;
    } else {            ///// �̾��ν���
        ///// ����ê����
        $data[$cnt]['s_tana_zai_c'] = ($data[$cnt-1]['e_tana_zai_c'] * (-1));
        $data[$cnt]['s_tana_kei_c'] = ($data[$cnt-1]['e_tana_kei_c'] * (-1));
        $data[$cnt]['s_tana_sum_c'] = ($data[$cnt-1]['e_tana_sum_c'] * (-1));
        $data[$cnt]['s_tana_zai_l'] = ($data[$cnt-1]['e_tana_zai_l'] * (-1));
        $data[$cnt]['s_tana_kei_l'] = ($data[$cnt-1]['e_tana_kei_l'] * (-1));
        $data[$cnt]['s_tana_sum_l'] = ($data[$cnt-1]['e_tana_sum_l'] * (-1));
        $data[$cnt]['s_tana_zai_a'] = ($data[$cnt-1]['e_tana_zai_a'] * (-1));
        $data[$cnt]['s_tana_kei_a'] = ($data[$cnt-1]['e_tana_kei_a'] * (-1));
        $data[$cnt]['s_tana_sum_a'] = ($data[$cnt-1]['e_tana_sum_a'] * (-1));
        
        $view_data[$cnt]['s_tana_zai_c'] = number_format($data[$cnt]['s_tana_zai_c'] / $tani, $keta);
        $view_data[$cnt]['s_tana_kei_c'] = number_format($data[$cnt]['s_tana_kei_c'] / $tani, $keta);
        $view_data[$cnt]['s_tana_sum_c'] = number_format($data[$cnt]['s_tana_sum_c'] / $tani, $keta);
        $view_data[$cnt]['s_tana_zai_l'] = number_format($data[$cnt]['s_tana_zai_l'] / $tani, $keta);
        $view_data[$cnt]['s_tana_kei_l'] = number_format($data[$cnt]['s_tana_kei_l'] / $tani, $keta);
        $view_data[$cnt]['s_tana_sum_l'] = number_format($data[$cnt]['s_tana_sum_l'] / $tani, $keta);
        $view_data[$cnt]['s_tana_zai_a'] = number_format($data[$cnt]['s_tana_zai_a'] / $tani, $keta);
        $view_data[$cnt]['s_tana_kei_a'] = number_format($data[$cnt]['s_tana_kei_a'] / $tani, $keta);
        $view_data[$cnt]['s_tana_sum_a'] = number_format($data[$cnt]['s_tana_sum_a'] / $tani, $keta);
    }
    ///// �������ȯ����
        // ������
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ������'", $data[$cnt]['ym']);
    if (getUniResult($query, $metarial_c) < 1) {
        $_SESSION['s_sysmsg'] .= sprintf("���ץ������̤��Ͽ<br>ǯ��=%d", $data[$cnt]['ym']);
        header("Location: $url_referer");
        exit();
    } else {
        $data[$cnt]['metarial_c']      = $metarial_c;
        $view_data[$cnt]['metarial_c'] = number_format(($metarial_c / $tani), $keta);
    }
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥�������'", $data[$cnt]['ym']);
    if (getUniResult($query, $metarial_l) < 1) {
        $_SESSION['s_sysmsg'] .= sprintf("��˥�������̤��Ͽ<br>ǯ��=%d", $data[$cnt]['ym']);
        header("Location: $url_referer");
        exit();
    } else {
        $data[$cnt]['metarial_l']      = $metarial_l;
        $view_data[$cnt]['metarial_l'] = number_format(($metarial_l / $tani), $keta);
    }
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���κ�����'", $data[$cnt]['ym']);
    if (getUniResult($query, $metarial_a) < 1) {
        $_SESSION['s_sysmsg'] .= sprintf("���κ�����̤��Ͽ<br>ǯ��=%d", $data[$cnt]['ym']);
        header("Location: $url_referer");
        exit();
    } else {
        $data[$cnt]['metarial_a']      = $metarial_a;
        $view_data[$cnt]['metarial_a'] = number_format(($metarial_a / $tani), $keta);
    }
    
        // ϫ̳�񡦷���
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ�ϫ̳��'", $data[$cnt]['ym']);
    if (getUniResult($query, $roumu_c) < 1) {
        $_SESSION['s_sysmsg'] .= sprintf("���ץ�ϫ̳��̤��Ͽ<br>ǯ��=%d", $data[$cnt]['ym']);
        header("Location: $url_referer");
        exit();
    }
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥�ϫ̳��'", $data[$cnt]['ym']);
    if (getUniResult($query, $roumu_l) < 1) {
        $_SESSION['s_sysmsg'] .= sprintf("���ץ�ϫ̳��̤��Ͽ<br>ǯ��=%d", $data[$cnt]['ym']);
        header("Location: $url_referer");
        exit();
    }
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='����ϫ̳��'", $data[$cnt]['ym']);
    if (getUniResult($query, $roumu_a) < 1) {
        $_SESSION['s_sysmsg'] .= sprintf("����ϫ̳��̤��Ͽ<br>ǯ��=%d", $data[$cnt]['ym']);
        header("Location: $url_referer");
        exit();
    }
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ���¤����'", $data[$cnt]['ym']);
    if (getUniResult($query, $keihi_c) < 1) {
        $_SESSION['s_sysmsg'] .= sprintf("���ץ���¤����̤��Ͽ<br>ǯ��=%d", $data[$cnt]['ym']);
        header("Location: $url_referer");
        exit();
    }
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥���¤����'", $data[$cnt]['ym']);
    if (getUniResult($query, $keihi_l) < 1) {
        $_SESSION['s_sysmsg'] .= sprintf("��˥���¤����̤��Ͽ<br>ǯ��=%d", $data[$cnt]['ym']);
        header("Location: $url_referer");
        exit();
    }
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='������¤����'", $data[$cnt]['ym']);
    if (getUniResult($query, $keihi_a) < 1) {
        $_SESSION['s_sysmsg'] .= sprintf("������¤����̤��Ͽ<br>ǯ��=%d", $data[$cnt]['ym']);
        header("Location: $url_referer");
        exit();
    }
    $data[$cnt]['expense_c'] = ($roumu_c + $keihi_c);
    $data[$cnt]['expense_l'] = ($roumu_l + $keihi_l);
    $data[$cnt]['expense_a'] = ($roumu_a + $keihi_a);
    $view_data[$cnt]['expense_c'] = number_format($data[$cnt]['expense_c'] / $tani, $keta);
    $view_data[$cnt]['expense_l'] = number_format($data[$cnt]['expense_l'] / $tani, $keta);
    $view_data[$cnt]['expense_a'] = number_format($data[$cnt]['expense_a'] / $tani, $keta);
    
    $data[$cnt]['shi_c'] = $data[$cnt]['metarial_c'] + $data[$cnt]['expense_c'];
    $data[$cnt]['shi_l'] = $data[$cnt]['metarial_l'] + $data[$cnt]['expense_l'];
    $data[$cnt]['shi_a'] = $data[$cnt]['metarial_a'] + $data[$cnt]['expense_a'];
    $view_data[$cnt]['shi_c'] = number_format($data[$cnt]['shi_c'] / $tani, $keta);
    $view_data[$cnt]['shi_l'] = number_format($data[$cnt]['shi_l'] / $tani, $keta);
    $view_data[$cnt]['shi_a'] = number_format($data[$cnt]['shi_a'] / $tani, $keta);
    ///// ����ê����
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ����ê����'", $data[$cnt]['ym']);
    if (getUniResult($query, $e_tana_c) < 1) {
        $_SESSION['s_sysmsg'] .= sprintf("���ץ����ê���⤬̤��Ͽ<br>ǯ��=%d", $data[$cnt]['ym']);
        header("Location: $url_referer");
        exit();
    } else {
        $data[$cnt]['e_tana_sum_c'] = $e_tana_c;
        $data[$cnt]['e_tana_zai_c'] = Uround(($p_zai_c * $data[$cnt]['e_tana_sum_c']),0);
        $data[$cnt]['e_tana_kei_c'] = $data[$cnt]['e_tana_sum_c'] - $data[$cnt]['e_tana_zai_c'];
    }
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥�����ê����'", $data[$cnt]['ym']);
    if (getUniResult($query, $e_tana_l) < 1) {
        $_SESSION['s_sysmsg'] .= sprintf("��˥�����ê���⤬̤��Ͽ<br>ǯ��=%d", $data[$cnt]['ym']);
        header("Location: $url_referer");
        exit();
    } else {
        $data[$cnt]['e_tana_sum_l'] = $e_tana_l;
        $data[$cnt]['e_tana_zai_l'] = Uround(($p_zai_l * $data[$cnt]['e_tana_sum_l']),0);
        $data[$cnt]['e_tana_kei_l'] = $data[$cnt]['e_tana_sum_l'] - $data[$cnt]['e_tana_zai_l'];
    }
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���δ���ê����'", $data[$cnt]['ym']);
    if (getUniResult($query, $e_tana_a) < 1) {
        $_SESSION['s_sysmsg'] .= sprintf("���δ���ê���⤬̤��Ͽ<br>ǯ��=%d", $data[$cnt]['ym']);
        header("Location: $url_referer");
        exit();
    } else {
        $data[$cnt]['e_tana_sum_a'] = $e_tana_a;
        // $data[$cnt]['e_tana_zai_a'] = Uround(($p_zai_a * $data[$cnt]['e_tana_sum_a']),0);
        // $data[$cnt]['e_tana_kei_a'] = $data[$cnt]['e_tana_sum_a'] - $data[$cnt]['e_tana_zai_a'];
        // Excel�η׻�ˡ��ˡ�˹�碌�뤿���ѹ� 2004/09/07
        $data[$cnt]['e_tana_zai_a'] = $data[$cnt]['e_tana_zai_c'] + $data[$cnt]['e_tana_zai_l'];
        $data[$cnt]['e_tana_kei_a'] = $data[$cnt]['e_tana_kei_c'] + $data[$cnt]['e_tana_kei_l'];
        // e_tana_sum_a �ϸ����Ѥ˥ơ��֥�Υǡ�����ȤäƤ���(��פ��礦�������å��Ǥ���)
    }
    $data[$cnt]['e_tana_zai_c'] = ($data[$cnt]['e_tana_zai_c'] * (-1));     // ���ȿž
    $data[$cnt]['e_tana_kei_c'] = ($data[$cnt]['e_tana_kei_c'] * (-1));
    $data[$cnt]['e_tana_sum_c'] = ($data[$cnt]['e_tana_sum_c'] * (-1));
    $data[$cnt]['e_tana_zai_l'] = ($data[$cnt]['e_tana_zai_l'] * (-1));
    $data[$cnt]['e_tana_kei_l'] = ($data[$cnt]['e_tana_kei_l'] * (-1));
    $data[$cnt]['e_tana_sum_l'] = ($data[$cnt]['e_tana_sum_l'] * (-1));
    $data[$cnt]['e_tana_zai_a'] = ($data[$cnt]['e_tana_zai_a'] * (-1));
    $data[$cnt]['e_tana_kei_a'] = ($data[$cnt]['e_tana_kei_a'] * (-1));
    $data[$cnt]['e_tana_sum_a'] = ($data[$cnt]['e_tana_sum_a'] * (-1));
    
    $view_data[$cnt]['e_tana_zai_c'] = number_format($data[$cnt]['e_tana_zai_c'] / $tani, $keta);
    $view_data[$cnt]['e_tana_kei_c'] = number_format($data[$cnt]['e_tana_kei_c'] / $tani, $keta);
    $view_data[$cnt]['e_tana_sum_c'] = number_format($data[$cnt]['e_tana_sum_c'] / $tani, $keta);
    $view_data[$cnt]['e_tana_zai_l'] = number_format($data[$cnt]['e_tana_zai_l'] / $tani, $keta);
    $view_data[$cnt]['e_tana_kei_l'] = number_format($data[$cnt]['e_tana_kei_l'] / $tani, $keta);
    $view_data[$cnt]['e_tana_sum_l'] = number_format($data[$cnt]['e_tana_sum_l'] / $tani, $keta);
    $view_data[$cnt]['e_tana_zai_a'] = number_format($data[$cnt]['e_tana_zai_a'] / $tani, $keta);
    $view_data[$cnt]['e_tana_kei_a'] = number_format($data[$cnt]['e_tana_kei_a'] / $tani, $keta);
    $view_data[$cnt]['e_tana_sum_a'] = number_format($data[$cnt]['e_tana_sum_a'] / $tani, $keta);
    ///// ��帶���η׻�
        // ����ê���� �� �������(����)ȯ��(����) �� �ʡݴ���ê����) ��ƹ�����˷׻�����
    $data[$cnt]['gen_zai_c'] = $data[$cnt]['s_tana_zai_c'] + $data[$cnt]['metarial_c'] + ($data[$cnt]['e_tana_zai_c']);
    $data[$cnt]['gen_kei_c'] = $data[$cnt]['s_tana_kei_c'] + $data[$cnt]['expense_c']  + ($data[$cnt]['e_tana_kei_c']);
    $data[$cnt]['gen_sum_c'] = $data[$cnt]['s_tana_sum_c'] + $data[$cnt]['shi_c']      + ($data[$cnt]['e_tana_sum_c']);
    $data[$cnt]['gen_zai_l'] = $data[$cnt]['s_tana_zai_l'] + $data[$cnt]['metarial_l'] + ($data[$cnt]['e_tana_zai_l']);
    $data[$cnt]['gen_kei_l'] = $data[$cnt]['s_tana_kei_l'] + $data[$cnt]['expense_l']  + ($data[$cnt]['e_tana_kei_l']);
    $data[$cnt]['gen_sum_l'] = $data[$cnt]['s_tana_sum_l'] + $data[$cnt]['shi_l']      + ($data[$cnt]['e_tana_sum_l']);
    $data[$cnt]['gen_zai_a'] = $data[$cnt]['s_tana_zai_a'] + $data[$cnt]['metarial_a'] + ($data[$cnt]['e_tana_zai_a']);
    $data[$cnt]['gen_kei_a'] = $data[$cnt]['s_tana_kei_a'] + $data[$cnt]['expense_a']  + ($data[$cnt]['e_tana_kei_a']);
    $data[$cnt]['gen_sum_a'] = $data[$cnt]['s_tana_sum_a'] + $data[$cnt]['shi_a']      + ($data[$cnt]['e_tana_sum_a']);
    
    $view_data[$cnt]['gen_zai_c'] = number_format($data[$cnt]['gen_zai_c'] / $tani, $keta);
    $view_data[$cnt]['gen_kei_c'] = number_format($data[$cnt]['gen_kei_c'] / $tani, $keta);
    $view_data[$cnt]['gen_sum_c'] = number_format($data[$cnt]['gen_sum_c'] / $tani, $keta);
    $view_data[$cnt]['gen_zai_l'] = number_format($data[$cnt]['gen_zai_l'] / $tani, $keta);
    $view_data[$cnt]['gen_kei_l'] = number_format($data[$cnt]['gen_kei_l'] / $tani, $keta);
    $view_data[$cnt]['gen_sum_l'] = number_format($data[$cnt]['gen_sum_l'] / $tani, $keta);
    $view_data[$cnt]['gen_zai_a'] = number_format($data[$cnt]['gen_zai_a'] / $tani, $keta);
    $view_data[$cnt]['gen_kei_a'] = number_format($data[$cnt]['gen_kei_a'] / $tani, $keta);
    $view_data[$cnt]['gen_sum_a'] = number_format($data[$cnt]['gen_sum_a'] / $tani, $keta);
    ///// ������
        // ��帶�� �� ���� �� ��帶��Ψ(������)  �ƹ�����˷׻�����
    $data[$cnt]['ritu_zai_c'] = Uround(($data[$cnt]['gen_zai_c'] / $data[$cnt]['uri_c']) * 100, 1);
    $data[$cnt]['ritu_kei_c'] = Uround(($data[$cnt]['gen_kei_c'] / $data[$cnt]['uri_c']) * 100, 1);
    $data[$cnt]['ritu_sum_c'] = Uround(($data[$cnt]['gen_sum_c'] / $data[$cnt]['uri_c']) * 100, 1);
    $data[$cnt]['ritu_zai_l'] = Uround(($data[$cnt]['gen_zai_l'] / $data[$cnt]['uri_l']) * 100, 1);
    $data[$cnt]['ritu_kei_l'] = Uround(($data[$cnt]['gen_kei_l'] / $data[$cnt]['uri_l']) * 100, 1);
    $data[$cnt]['ritu_sum_l'] = Uround(($data[$cnt]['gen_sum_l'] / $data[$cnt]['uri_l']) * 100, 1);
    $data[$cnt]['ritu_zai_a'] = Uround(($data[$cnt]['gen_zai_a'] / $data[$cnt]['uri_a']) * 100, 1);
    $data[$cnt]['ritu_kei_a'] = Uround(($data[$cnt]['gen_kei_a'] / $data[$cnt]['uri_a']) * 100, 1);
    $data[$cnt]['ritu_sum_a'] = Uround(($data[$cnt]['gen_sum_a'] / $data[$cnt]['uri_a']) * 100, 1);
    
    $view_data[$cnt]['ritu_zai_c'] = number_format($data[$cnt]['ritu_zai_c'], 1);
    $view_data[$cnt]['ritu_kei_c'] = number_format($data[$cnt]['ritu_kei_c'], 1);
    $view_data[$cnt]['ritu_sum_c'] = number_format($data[$cnt]['ritu_sum_c'], 1);
    $view_data[$cnt]['ritu_zai_l'] = number_format($data[$cnt]['ritu_zai_l'], 1);
    $view_data[$cnt]['ritu_kei_l'] = number_format($data[$cnt]['ritu_kei_l'], 1);
    $view_data[$cnt]['ritu_sum_l'] = number_format($data[$cnt]['ritu_sum_l'], 1);
    $view_data[$cnt]['ritu_zai_a'] = number_format($data[$cnt]['ritu_zai_a'], 1);
    $view_data[$cnt]['ritu_kei_a'] = number_format($data[$cnt]['ritu_kei_a'], 1);
    $view_data[$cnt]['ritu_sum_a'] = number_format($data[$cnt]['ritu_sum_a'], 1);
    
    ///// �����������Υܥ��󤬲����줿�����㳰���� (ɽ�����֤κ���ϣ������)
    if ($cnt >= 6) {
        break;
    }
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
<?= $menu->out_site_java() ?>
<?= $menu->out_css() ?>

<script language="JavaScript">
<!--
/* ����ʸ�����������ɤ��������å� */
function isDigit(str) {
    var len=str.length;
    var c;
    for (i=1; i<len; i++) {
        c = str.charAt(i);
        if((c < "0") || (c > "9")) {
            return true;
        }
    }
    return false;
}
/* ������ϥե�����Υ�����Ȥ˥ե������������� */
function set_focus(){
    // document.body.focus();   // F2/F12������ͭ���������б�
    // document.mhForm.backwardStack.focus();  // �嵭��IE�ΤߤΤ��ᡢ��������ѹ���NN�б�
    // document.form_name.element_name.focus();      // ������ϥե����ब������ϥ����Ȥ򳰤�
    // document.form_name.element_name.select();
}
// -->
</script>
<link rel='stylesheet' href='account_settlement.css' type='text/css'> <!-- �ե��������ξ�� -->
<style type="text/css">
<!--
-->
</style>
</head>
<body onLoad='set_focus()'>
    <center>
<?= $menu->out_title_border() ?>
        <table width='100%' border='1' cellspacing='0' cellpadding='0'>
            <tr>
                <td colspan='1' width='130' bgcolor='#d6d3ce' align='center' class='corporate_name'>
                    �������칩��(��)
                </td>
                <form method='post' action='<?php echo $current_script ?>'>
                    <td bgcolor='green' width='70'align='center' class='pt10'>
                        <input class='pt10' type='submit' name='backward_ki' value='��Ⱦ��'>
                    </td>
                    <td bgcolor='green' width='70'align='center' class='pt10'>
                        <input class='pt10' type='submit' name='forward_ki' value='��Ⱦ��'>
                    </td>
                    <td colspan='7' bgcolor='#d6d3ce' align='right' class='pt10'>
                        ñ��
                        <select name='costrate_tani' class='pt10'>
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
                        <select name='costrate_keta' class='pt10'>
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
                        <input class='pt10b' type='submit' name='chg_measure' value='ñ���ѹ�'>
                    </td>
                </form>
            </tr>
        </table>
        <table width='100%' bgcolor='#d6d3ce' align='center' cellspacing="0" cellpadding="3" border='1'>
            <tr><td> <!-- ���ߡ�(�ǥ�������) -->
        <table width='100%' bgcolor='#d6d3ce' align='center' cellspacing="0" cellpadding="3" border='1'>
            <tbody>
                <tr>
                    <td rowspan='2' align='center' class='pt11b'>��</td>
                    <td colspan='3' align='center' class='pt11b' bgcolor='#ffffc6'>�����ס���</td>
                    <td colspan='3' align='center' class='pt11b' bgcolor='#ffffc6'>�ꡡ�ˡ���</td>
                    <td colspan='3' align='center' class='pt11b' bgcolor='#ffffc6'>�硡������</td>
                </tr>
                <tr>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>�ࡡ������<br>(������)</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>ϫ̳�����<br>(�����)</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>��</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>�ࡡ������<br>(������)</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>ϫ̳�����<br>(�����)</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>��</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>�ࡡ������<br>(������)</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>ϫ̳�����<br>(�����)</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>��</td>
                </tr>
                <tr>
                    <td nowrap align='center' class='pt11' bgcolor='white'>����������</td>
                    <td nowrap align='right' class='pt11b' bgcolor='white'><?php echo $view_pre_ritu_zai_c ?>%</td>
                    <td nowrap align='right' class='pt11b' bgcolor='white'><?php echo $view_pre_ritu_kei_c ?>%</td>
                    <td nowrap align='right' class='pt11b' bgcolor='white'><?php echo $view_pre_ritu_sum_c ?>%</td>
                    <td nowrap align='right' class='pt11b' bgcolor='white'><?php echo $view_pre_ritu_zai_l ?>%</td>
                    <td nowrap align='right' class='pt11b' bgcolor='white'><?php echo $view_pre_ritu_kei_l ?>%</td>
                    <td nowrap align='right' class='pt11b' bgcolor='white'><?php echo $view_pre_ritu_sum_c ?>%</td>
                    <td nowrap align='right' class='pt11b' bgcolor='white'><?php echo $view_pre_ritu_zai_a ?>%</td>
                    <td nowrap align='right' class='pt11b' bgcolor='white'><?php echo $view_pre_ritu_kei_a ?>%</td>
                    <td nowrap align='right' class='pt11b' bgcolor='white'><?php echo $view_pre_ritu_sum_a ?>%</td>
                </tr>
                <tr>
                    <td nowrap align='center' class='pt11' bgcolor='#ceffce'>����ê����<br>(���)</td>
                    <td nowrap align='right' class='pt11b' bgcolor='#e6e6e6'><?php echo $view_i_zai_c . "<br>(" . $percent_zai_c ?>%)</td>
                    <td nowrap align='right' class='pt11b' bgcolor='#e6e6e6'><?php echo $view_i_kei_c . "<br>(" . $percent_kei_c ?>%)</td>
                    <td nowrap align='right' class='pt11b' bgcolor='#e6e6e6'><?php echo $view_i_sum_c . "<br>(" . $percent_sum_c ?>%)</td>
                    <td nowrap align='right' class='pt11b' bgcolor='#e6e6e6'><?php echo $view_i_zai_l . "<br>(" . $percent_zai_l ?>%)</td>
                    <td nowrap align='right' class='pt11b' bgcolor='#e6e6e6'><?php echo $view_i_kei_l . "<br>(" . $percent_kei_l ?>%)</td>
                    <td nowrap align='right' class='pt11b' bgcolor='#e6e6e6'><?php echo $view_i_sum_l . "<br>(" . $percent_sum_l ?>%)</td>
                    <td nowrap align='right' class='pt11b' bgcolor='#e6e6e6'><?php echo $view_i_zai_all . "<br>(" . $percent_zai_all ?>%)</td>
                    <td nowrap align='right' class='pt11b' bgcolor='#e6e6e6'><?php echo $view_i_kei_all . "<br>(" . $percent_kei_all ?>%)</td>
                    <td nowrap align='right' class='pt11b' bgcolor='#e6e6e6'><?php echo $view_i_sum_all . "<br>(" . $percent_sum_all ?>%)</td>
                </tr>
                <?php for ($j = 0; $j < $cnt; $j++) { ?>
                <tr>
                    <td nowrap align='right' class='pt11b' bgcolor='white'><?php echo $view_data[$j]['ym'] ?></td>
                    <td nowrap align='center' class='pt11' bgcolor='white'>�䡡�塡��</td>
                    <td colspan='2' nowrap align='center' class='pt11' bgcolor='white'><?php echo $view_data[$j]['uri_c'] ?></td>
                    <td colspan='3' nowrap align='center' class='pt11' bgcolor='white'><?php echo $view_data[$j]['uri_l'] ?></td>
                    <td colspan='3' nowrap align='center' class='pt11' bgcolor='white'><?php echo $view_data[$j]['uri_a'] ?></td>
                </tr>
                <tr>
                    <td nowrap align='center' class='pt11' bgcolor='#ceffce'>����ê����</td>
                    <td nowrap align='right' class='pt11' bgcolor='#e6e6e6'><?php echo $view_data[$j]['s_tana_zai_c'] ?></td>
                    <td nowrap align='right' class='pt11' bgcolor='#e6e6e6'><?php echo $view_data[$j]['s_tana_kei_c'] ?></td>
                    <td nowrap align='right' class='pt11' bgcolor='#e6e6e6'><?php echo $view_data[$j]['s_tana_sum_c'] ?></td>
                    <td nowrap align='right' class='pt11' bgcolor='#e6e6e6'><?php echo $view_data[$j]['s_tana_zai_l'] ?></td>
                    <td nowrap align='right' class='pt11' bgcolor='#e6e6e6'><?php echo $view_data[$j]['s_tana_kei_l'] ?></td>
                    <td nowrap align='right' class='pt11' bgcolor='#e6e6e6'><?php echo $view_data[$j]['s_tana_sum_l'] ?></td>
                    <td nowrap align='right' class='pt11' bgcolor='#e6e6e6'><?php echo $view_data[$j]['s_tana_zai_a'] ?></td>
                    <td nowrap align='right' class='pt11' bgcolor='#e6e6e6'><?php echo $view_data[$j]['s_tana_kei_a'] ?></td>
                    <td nowrap align='right' class='pt11' bgcolor='#e6e6e6'><?php echo $view_data[$j]['s_tana_sum_a'] ?></td>
                </tr>
                <tr>
                    <td nowrap align='center' class='pt11' bgcolor='white'>����ȯ����</td>
                    <td nowrap align='right' class='pt11' bgcolor='white'><?php echo $view_data[$j]['metarial_c'] ?></td>
                    <td nowrap align='right' class='pt11' bgcolor='white'><?php echo $view_data[$j]['expense_c'] ?> </td>
                    <td nowrap align='right' class='pt11' bgcolor='white'><?php echo $view_data[$j]['shi_c'] ?>     </td>
                    <td nowrap align='right' class='pt11' bgcolor='white'><?php echo $view_data[$j]['metarial_l'] ?></td>
                    <td nowrap align='right' class='pt11' bgcolor='white'><?php echo $view_data[$j]['expense_l'] ?> </td>
                    <td nowrap align='right' class='pt11' bgcolor='white'><?php echo $view_data[$j]['shi_l'] ?>     </td>
                    <td nowrap align='right' class='pt11' bgcolor='white'><?php echo $view_data[$j]['metarial_a'] ?></td>
                    <td nowrap align='right' class='pt11' bgcolor='white'><?php echo $view_data[$j]['expense_a'] ?> </td>
                    <td nowrap align='right' class='pt11' bgcolor='white'><?php echo $view_data[$j]['shi_a'] ?>     </td>
                </tr>
                <tr>
                    <td nowrap align='center' class='pt11' bgcolor='#ceffce'>����ê����</td>
                    <td nowrap align='right' class='pt11' bgcolor='#e6e6e6'><?php echo $view_data[$j]['e_tana_zai_c'] ?></td>
                    <td nowrap align='right' class='pt11' bgcolor='#e6e6e6'><?php echo $view_data[$j]['e_tana_kei_c'] ?></td>
                    <td nowrap align='right' class='pt11' bgcolor='#e6e6e6'><?php echo $view_data[$j]['e_tana_sum_c'] ?></td>
                    <td nowrap align='right' class='pt11' bgcolor='#e6e6e6'><?php echo $view_data[$j]['e_tana_zai_l'] ?></td>
                    <td nowrap align='right' class='pt11' bgcolor='#e6e6e6'><?php echo $view_data[$j]['e_tana_kei_l'] ?></td>
                    <td nowrap align='right' class='pt11' bgcolor='#e6e6e6'><?php echo $view_data[$j]['e_tana_sum_l'] ?></td>
                    <td nowrap align='right' class='pt11' bgcolor='#e6e6e6'><?php echo $view_data[$j]['e_tana_zai_a'] ?></td>
                    <td nowrap align='right' class='pt11' bgcolor='#e6e6e6'><?php echo $view_data[$j]['e_tana_kei_a'] ?></td>
                    <td nowrap align='right' class='pt11' bgcolor='#e6e6e6'><?php echo $view_data[$j]['e_tana_sum_a'] ?></td>
                </tr>
                <tr>
                    <td nowrap align='center' class='pt11' bgcolor='white'>��帶��</td>
                    <td nowrap align='right' class='pt11' bgcolor='white'><?php echo $view_data[$j]['gen_zai_c'] ?></td>
                    <td nowrap align='right' class='pt11' bgcolor='white'><?php echo $view_data[$j]['gen_kei_c'] ?></td>
                    <td nowrap align='right' class='pt11' bgcolor='white'><?php echo $view_data[$j]['gen_sum_c'] ?></td>
                    <td nowrap align='right' class='pt11' bgcolor='white'><?php echo $view_data[$j]['gen_zai_l'] ?></td>
                    <td nowrap align='right' class='pt11' bgcolor='white'><?php echo $view_data[$j]['gen_kei_l'] ?></td>
                    <td nowrap align='right' class='pt11' bgcolor='white'><?php echo $view_data[$j]['gen_sum_l'] ?></td>
                    <td nowrap align='right' class='pt11' bgcolor='white'><?php echo $view_data[$j]['gen_zai_a'] ?></td>
                    <td nowrap align='right' class='pt11' bgcolor='white'><?php echo $view_data[$j]['gen_kei_a'] ?></td>
                    <td nowrap align='right' class='pt11' bgcolor='white'><?php echo $view_data[$j]['gen_sum_a'] ?></td>
                </tr>
                <tr>
                    <td nowrap align='center' class='pt11' bgcolor='#ceffce'>������</td>
                    <td nowrap align='right' class='pt11b' bgcolor='#e6e6e6'><?php echo $view_data[$j]['ritu_zai_c'] ?>%</td>
                    <td nowrap align='right' class='pt11b' bgcolor='#e6e6e6'><?php echo $view_data[$j]['ritu_kei_c'] ?>%</td>
                    <td nowrap align='right' class='pt11b' bgcolor='#e6e6e6'><?php echo $view_data[$j]['ritu_sum_c'] ?>%</td>
                    <td nowrap align='right' class='pt11b' bgcolor='#e6e6e6'><?php echo $view_data[$j]['ritu_zai_l'] ?>%</td>
                    <td nowrap align='right' class='pt11b' bgcolor='#e6e6e6'><?php echo $view_data[$j]['ritu_kei_l'] ?>%</td>
                    <td nowrap align='right' class='pt11b' bgcolor='#e6e6e6'><?php echo $view_data[$j]['ritu_sum_l'] ?>%</td>
                    <td nowrap align='right' class='pt11b' bgcolor='#e6e6e6'><?php echo $view_data[$j]['ritu_zai_a'] ?>%</td>
                    <td nowrap align='right' class='pt11b' bgcolor='#e6e6e6'><?php echo $view_data[$j]['ritu_kei_a'] ?>%</td>
                    <td nowrap align='right' class='pt11b' bgcolor='#e6e6e6'><?php echo $view_data[$j]['ritu_sum_a'] ?>%</td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
            </td></tr>
        </table> <!-- ���ߡ�End -->
    </center>
</body>
</html>
