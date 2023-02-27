<?php
//////////////////////////////////////////////////////////////////////////////
// � ���ê��ɽ �Ȳ�                                                     //
// Copyright (C) 2003-2021      Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp //
//        with patTemplate templates/getsuji_comp_invent.templ.html         //
// Changed history                                                          //
// 2003/07/29 Created  getsuji_comp_invent.php            php-4.3.3rc2      //
// 2003/09/29 �ơ��֥�� act_comp_invent_history �� query php-4.3.3         //
// 2003/10/16 patTemplate ����������ϥå�������� tbody?[]�ˤޤȤ᤿       //
// 2003/10/16 ñ���ѹ���������ͤ˥��å��ɲ� <option {selected}>�����    //
// 2004/01/08 �ͼθ��������ʤ��������������٤ι�פ˰ʲ��Υ��å����ɲ�//
//             $tbody2['tbody2_ckeip'] = $pmonth['���ץ�������'];��˥��� //
// 2004/05/11 ��¦�Υ����ȥ�˥塼�Υ��󡦥��� �ܥ���{PAGE_SITE_VIEW}���ɲ� //
// 2005/10/27 MenuHeader class ����Ѥ��ƶ��̥�˥塼���ڤ�ǧ���������ѹ�   //
// 2007/10/10 getsuji_comp_invent.php �� invent_comp/invent_comp_view.php�� //
// 2010/02/09 201001���getsuji_comp_invent_201001.templ.html�����    ��ë //
//            $rows�ο����Ѥ�ä��١��ƥǡ����γ�Ǽ����$r���ϰϤ��ѹ�       //
// 2015/06/01 201504��굡���ɲá��ºݤ�201505����������ǡ��������δط���  //
//            201504����ɲ�                                           ��ë //
// 2016/04/14 201604��굡������ɸ���ɲ�                               ��ë //
// 2020/02/06 202001����������٤�DP���ɲá�����¾�����ʬΥ��              //
//            201912�⹹�������Τǹ�碌��ɽ��������ʤ��ͤ��ѹ�       ��ë //
// 2020/04/07 ñ�̤ǻͼθ������Ƥ����١���פ�����Ƥ����Τǽ���       ��ë //
// 2021/05/07 202104��굡������                                       ��ë //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL || E_STRICT);
// ini_set('error_reporting',E_ALL);           // E_ALL='2047' debug ��
// ini_set('display_errors','1');              // Error ɽ�� ON debug �� ��꡼���女����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��

require_once ('../../function.php');        // define.php �� pgsql.php �� require_once ���Ƥ���
require_once ('../../tnk_func.php');        // TNK �˰�¸������ʬ�δؿ��� require_once ���Ƥ���
require_once ('../../MenuHeader.php');      // TNK ������ menu class
access_log();                               // Script Name �ϼ�ư����

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader(0);                  // ǧ�ڥ����å�0=���̰ʾ� �����=TOP_MENU �����ȥ�̤����
    // �ºݤ�ǧ�ڤ�profit_loss_submit.php�ǹԤäƤ���account_group_check()�����

////////////// ����������
// $menu->set_site(10, 7);                     // site_index=10(»�ץ�˥塼) site_id=7(�»��)
//////////// ɽ�������
$menu->set_caption('�������칩��');
//////////// �ƽ����action̾�ȥ��ɥ쥹����
// $menu->set_action('��ݲ�̾',   PL . 'address.php');

///// �ƽФ�Ȥ� URL �����
$url_referer     = $_SESSION['pl_referer'];
$current_script  = $_SERVER['PHP_SELF'];        // ���߼¹���Υ�����ץ�̾����¸

/********** Logic Start **********/
///////////// �����ȥ�˥塼 On / Off 
if ($_SESSION['site_view'] == 'on') {
    $site_view = 'MenuOFF';
} else {
    $site_view = 'MenuON';
}

//////////////// �����ȥ�˥塼�Σգң����� & JavaScript����
$menu_site_url = 'http:' . WEB_HOST . 'menu_site.php';
$menu_site_script =
"<script language='JavaScript'>
<!--
    parent.menu_site.location = '$menu_site_url';
// -->
</script>";
$menu_site_script = "";         // ���˥塼�Τ���Ȥ�ʤ�

//////////// �����ȥ�����ա���������
$today = date("Y/m/d H:i:s");

//////////// JavaScript Stylesheet File ��ɬ���ɤ߹��ޤ���
$uniq = uniqid("target");

///// ������μ���
$ki = Ym_to_tnk($_SESSION['pl_ym']);
$tuki = substr($_SESSION['pl_ym'],4,2);
$tuki = $tuki + 1 -1;   // ���ͥǡ������Ѵ�(09��9�ˤ���������)���㥹�ȤǤ⤤���Τ���

//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title("�� {$ki} ����{$tuki} �������� �� ê �� ɽ");

///// �о�����
$yyyymm = $_SESSION['pl_ym'];
///// �о�����
if (substr($yyyymm,4,2)!=01) {
    $p1_ym = $yyyymm - 1;
} else {
    $p1_ym = $yyyymm - 100;
    $p1_ym = $p1_ym + 11;
}
///// �о������� ����ϤȤꤢ�����Ȥ�ʤ�
if (substr($p1_ym,4,2)!=01) {
    $p2_ym = $p1_ym - 1;
} else {
    $p2_ym = $p1_ym - 100;
    $p2_ym = $p2_ym + 11;
}
///// ������ ǯ��λ���
$yyyy = substr($yyyymm, 0,4);
$mm   = substr($yyyymm, 4,2);
if (($mm >= 1) && ($mm <= 3)) {
    $yyyy = ($yyyy - 1);
}
$pre_end_ym = $yyyy . "03";     // ����ǯ��

///// ɽ��ñ�̤��������
if (isset($_POST['comp_tani'])) {
    $_SESSION['comp_tani'] = $_POST['comp_tani'];
    $tani = $_SESSION['comp_tani'];
} elseif (isset($_SESSION['comp_tani'])) {
    $tani = $_SESSION['comp_tani'];
} else {
    $tani = 1000000;        // ����� ɽ��ñ�� ɴ����
    $_SESSION['comp_tani'] = $tani;
}
///// ɽ�� ��������� �������
if (isset($_POST['comp_keta'])) {
    $_SESSION['comp_keta'] = $_POST['comp_keta'];
    $keta = $_SESSION['comp_keta'];
} elseif (isset($_SESSION['comp_keta'])) {
    $keta = $_SESSION['comp_keta'];
} else {
    $keta = 1;          // ����� �������ʲ����
    $_SESSION['comp_keta'] = $keta;
}
// $keta = 1;              // ���ê��ɽ�ǤϾ������ʲ���1�˸��ꤷ�褦�Ȼפä������ʤ���


///// act_comp_invent_history ���ǡ�������
    ///// ����
$month = array();
$query = "select item, kin from act_comp_invent_history where invent_ym=$yyyymm";
if (($rows = getResult2($query, $month)) <= 0) {
    $_SESSION['s_sysmsg'] = sprintf("���ê��ɽ�Υǡ����ʤ���<br>�� %d�� %d��",$ki,$tuki);
    header("Location: $url_referer");
    exit();
} else {
    ///// item ��̾���ȶ�ۤ�����ñ�̤Ⱦ�������ǥϥå��������
    for ($r=0; $r<$rows; $r++) {
        //$month["{$month[$r][0]}"] = Uround($month[$r][1] / $tani, $keta);
        $month["{$month[$r][0]}"] = $month[$r][1] / $tani;
    }
    ///// ���ʹ�פȺ߸˹�׵ڤӺ�̳���ɾ���ۤȺ߸˹�פȤκ���(��¤������¾)��׻�
    $buhin_c = $month['���ץ�������']+$month['���ץ鸶����']+$month['���ץ鹩��ų�']+
                $month['���ץ鸡���ų�']+$month['���ץ鳰��ų�']+$month['���ץ�ã�����'];
    $sum_c = $buhin_c + $month['���ץ���Ω�ų�'];
    $sag_c = $month['���ץ��̳���'] - $sum_c;
    
    /////////////////////////////////////////////////////////////////////// ���ץ� START
    ///// ��ê����ۤ򣳷奫��ޤǥϥå��������
    $tbody['tbody_kum_c'] = number_format($month['���ץ���Ω�ų�'], $keta);
    $tbody['tbody_siz_c'] = number_format($month['���ץ�������'], $keta);
    $tbody['tbody_gen_c'] = number_format($month['���ץ鸶����']  , $keta);
    $tbody['tbody_kou_c'] = number_format($month['���ץ鹩��ų�'], $keta);
    $tbody['tbody_ken_c'] = number_format($month['���ץ鸡���ų�'], $keta);
    $tbody['tbody_gai_c'] = number_format($month['���ץ鳰��ų�'], $keta);
    $tbody['tbody_cc_c']  = number_format($month['���ץ�ã�����'], $keta);
    $tbody['tbody_zai_c'] = number_format($month['���ץ��̳���'], $keta);
    ///// �׻���̤�ϥå��������
    //$tbody['tbody_buh_c'] = number_format($buhin_c, $keta);
    $tbody['tbody_buh_c'] = number_format($buhin_c, $keta);
    $tbody['tbody_gou_c'] = number_format($sum_c, $keta);
    $tbody['tbody_sag_c'] = number_format($sag_c, $keta);
    /////////////////////////////////////////////////////////////////////// ���ץ� END

    /////////////////////////////////////////////////////////////////////// ��˥� START
    ///// ��ê����ۤ򣳷奫��ޤǥϥå��������
    $tbody['tbody_kum_l'] = number_format($month['��˥���Ω�ų�'], $keta);
    $tbody['tbody_siz_l'] = number_format($month['��˥��������'], $keta);
    $tbody['tbody_gen_l'] = number_format($month['��˥�������']  , $keta);
    $tbody['tbody_kou_l'] = number_format($month['��˥�����ų�'], $keta);
    $tbody['tbody_ken_l'] = number_format($month['��˥������ų�'], $keta);
    $tbody['tbody_gai_l'] = number_format($month['��˥�����ų�'], $keta);
    $tbody['tbody_cc_l']  = number_format($month['��˥��ã�����'], $keta);
    $tbody['tbody_zai_l'] = number_format($month['��˥���̳���'], $keta);
    ///// ���ʹ�פȺ߸˹�׵ڤӺ�̳���ɾ���ۤȺ߸˹�פȤκ���(��¤������¾)��׻�
    $buhin_l = $month['��˥��������']+$month['��˥�������']+$month['��˥�����ų�']+
                $month['��˥������ų�']+$month['��˥�����ų�']+$month['��˥��ã�����'];
    $sum_l = $buhin_l + $month['��˥���Ω�ų�'];
    $sag_l = $month['��˥���̳���'] - $sum_l;
    ///// �׻���̤�ϥå��������
    $tbody['tbody_buh_l'] = number_format($buhin_l, $keta);
    $tbody['tbody_gou_l'] = number_format($sum_l, $keta);
    $tbody['tbody_sag_l'] = number_format($sag_l, $keta);
    /////////////////////////////////////////////////////////////////////// ��˥� END
    if ($yyyymm >= 201504 && $yyyymm <= 202103) {
        /////////////////////////////////////////////////////////////////////// �ġ��� START
        ///// ��ê����ۤ򣳷奫��ޤǥϥå��������
        $tbody['tbody_kum_t'] = number_format($month['�ġ�����Ω�ų�'], $keta);
        $tbody['tbody_siz_t'] = number_format($month['�ġ���������'], $keta);
        $tbody['tbody_gen_t'] = number_format($month['�ġ��븶����']  , $keta);
        $tbody['tbody_kou_t'] = number_format($month['�ġ��빩��ų�'], $keta);
        $tbody['tbody_ken_t'] = number_format($month['�ġ��븡���ų�'], $keta);
        $tbody['tbody_gai_t'] = number_format($month['�ġ��볰��ų�'], $keta);
        $tbody['tbody_cc_t']  = number_format($month['�ġ���ã�����'], $keta);
        $tbody['tbody_zai_t'] = number_format($month['�ġ����̳���'], $keta);
        ///// ���ʹ�פȺ߸˹�׵ڤӺ�̳���ɾ���ۤȺ߸˹�פȤκ���(��¤������¾)��׻�
        $buhin_t = $month['�ġ���������']+$month['�ġ��븶����']+$month['�ġ��빩��ų�']+
                    $month['�ġ��븡���ų�']+$month['�ġ��볰��ų�']+$month['�ġ���ã�����'];
        $sum_t = $buhin_t + $month['�ġ�����Ω�ų�'];
        $sag_t = $month['�ġ����̳���'] - $sum_t;
        ///// �׻���̤�ϥå��������
        $tbody['tbody_buh_t'] = number_format($buhin_t, $keta);
        $tbody['tbody_gou_t'] = number_format($sum_t, $keta);
        $tbody['tbody_sag_t'] = number_format($sag_t, $keta);
        /////////////////////////////////////////////////////////////////////// �ġ��� END
        
        /////////////////////////////////////////////////////////////////////// ���� START
        ///// ��ê����ۤ򣳷奫��ޤǥϥå��������
        $tbody['tbody_kum_a'] = number_format($month['���ץ���Ω�ų�'] + $month['��˥���Ω�ų�'] + $month['�ġ�����Ω�ų�'], $keta);
        $tbody['tbody_siz_a'] = number_format($month['���ץ�������'] + $month['��˥��������'] + $month['�ġ���������'], $keta);
        $tbody['tbody_gen_a'] = number_format($month['���ץ鸶����']   + $month['��˥�������']   + $month['�ġ��븶����']  , $keta);
        $tbody['tbody_kou_a'] = number_format($month['���ץ鹩��ų�'] + $month['��˥�����ų�'] + $month['�ġ��빩��ų�'], $keta);
        $tbody['tbody_ken_a'] = number_format($month['���ץ鸡���ų�'] + $month['��˥������ų�'] + $month['�ġ��븡���ų�'], $keta);
        $tbody['tbody_gai_a'] = number_format($month['���ץ鳰��ų�'] + $month['��˥�����ų�'] + $month['�ġ��볰��ų�'], $keta);
        $tbody['tbody_cc_a']  = number_format($month['���ץ�ã�����'] + $month['��˥��ã�����'] + $month['�ġ���ã�����'], $keta);
        $tbody['tbody_zai_a'] = number_format($month['���ץ��̳���'] + $month['��˥���̳���'] + $month['�ġ����̳���'], $keta);
        ///// ���ʹ�פȺ߸˹�׵ڤӺ�̳���ɾ���ۤȺ߸˹�פȤκ���(��¤������¾)��׻�
        $buhin_a = $buhin_c + $buhin_l + $buhin_t;
        $sum_a = $buhin_a + $month['���ץ���Ω�ų�'] + $month['��˥���Ω�ų�'] + $month['�ġ�����Ω�ų�'];
        $sag_a = ($month['���ץ��̳���'] + $month['��˥���̳���'] + $month['�ġ����̳���']) - $sum_a;
        ///// �׻���̤�ϥå��������
        $tbody['tbody_buh_a'] = number_format($buhin_a, $keta);
        $tbody['tbody_gou_a'] = number_format($sum_a, $keta);
        $tbody['tbody_sag_a'] = number_format($sag_a, $keta);
        /////////////////////////////////////////////////////////////////////// ���� END
    } else {
        /////////////////////////////////////////////////////////////////////// ���� START
        ///// ��ê����ۤ򣳷奫��ޤǥϥå��������
        $tbody['tbody_kum_a'] = number_format($month['���ץ���Ω�ų�'] + $month['��˥���Ω�ų�'], $keta);
        $tbody['tbody_siz_a'] = number_format($month['���ץ�������'] + $month['��˥��������'], $keta);
        $tbody['tbody_gen_a'] = number_format($month['���ץ鸶����']   + $month['��˥�������']  , $keta);
        $tbody['tbody_kou_a'] = number_format($month['���ץ鹩��ų�'] + $month['��˥�����ų�'], $keta);
        $tbody['tbody_ken_a'] = number_format($month['���ץ鸡���ų�'] + $month['��˥������ų�'], $keta);
        $tbody['tbody_gai_a'] = number_format($month['���ץ鳰��ų�'] + $month['��˥�����ų�'], $keta);
        $tbody['tbody_cc_a']  = number_format($month['���ץ�ã�����'] + $month['��˥��ã�����'], $keta);
        $tbody['tbody_zai_a'] = number_format($month['���ץ��̳���'] + $month['��˥���̳���'], $keta);
        ///// ���ʹ�פȺ߸˹�׵ڤӺ�̳���ɾ���ۤȺ߸˹�פȤκ���(��¤������¾)��׻�
        $buhin_a = $buhin_c + $buhin_l;
        $sum_a = $buhin_a + $month['���ץ���Ω�ų�'] + $month['��˥���Ω�ų�'];
        $sag_a = ($month['���ץ��̳���'] + $month['��˥���̳���']) - $sum_a;
        ///// �׻���̤�ϥå��������
        $tbody['tbody_buh_a'] = number_format($buhin_a, $keta);
        $tbody['tbody_gou_a'] = number_format($sum_a, $keta);
        $tbody['tbody_sag_a'] = number_format($sag_a, $keta);
        /////////////////////////////////////////////////////////////////////// ���� END
    }
}

    ///// ����
$pmonth = array();
$query = "select item, kin from act_comp_invent_history where invent_ym=$p1_ym";
if (($prows = getResult2($query, $pmonth)) <= 0) {
    $_SESSION['s_sysmsg'] = sprintf("���ê��ɽ������ǡ����ʤ���<br>%d", $p1_ym);
    header("Location: $url_referer");
    exit();
} else {
    ///// item ��̾���ȶ�ۤ�����ñ�̤Ⱦ�������ǥϥå��������
    for ($r=0; $r<$prows; $r++) {
        //$pmonth["{$pmonth[$r][0]}"] = Uround($pmonth[$r][1] / $tani, $keta);
        $pmonth["{$pmonth[$r][0]}"] = $pmonth[$r][1] / $tani;
    }
    /////////////////////////////////////////////////////////////////////// ���ץ� START
    ///// ��ê����ۤ򣳷奫��ޤǥϥå��������
    $tbody['tbody_kump_c'] = number_format($pmonth['���ץ���Ω�ų�'], $keta);
    $tbody['tbody_sizp_c'] = number_format($pmonth['���ץ�������'], $keta);
    $tbody['tbody_genp_c'] = number_format($pmonth['���ץ鸶����']  , $keta);
    $tbody['tbody_koup_c'] = number_format($pmonth['���ץ鹩��ų�'], $keta);
    $tbody['tbody_kenp_c'] = number_format($pmonth['���ץ鸡���ų�'], $keta);
    $tbody['tbody_gaip_c'] = number_format($pmonth['���ץ鳰��ų�'], $keta);
    $tbody['tbody_ccp_c']  = number_format($pmonth['���ץ�ã�����'], $keta);
    $tbody['tbody_zaip_c'] = number_format($pmonth['���ץ��̳���'], $keta);
    ///// ���ʹ�פȺ߸˹�׵ڤӺ�̳���ɾ���ۤȺ߸˹�פȤκ���(��¤������¾)��׻�
    $pbuhin_c = $pmonth['���ץ�������']+$pmonth['���ץ鸶����']+$pmonth['���ץ鹩��ų�']+
                $pmonth['���ץ鸡���ų�']+$pmonth['���ץ鳰��ų�']+$pmonth['���ץ�ã�����'];
    $psum_c = $pbuhin_c + $pmonth['���ץ���Ω�ų�'];
    $psag_c = $pmonth['���ץ��̳���'] - $psum_c;
    ///// �׻���̤�ϥå��������
    $tbody['tbody_buhp_c'] = number_format($pbuhin_c, $keta);
    $tbody['tbody_goup_c'] = number_format($psum_c, $keta);
    $tbody['tbody_sagp_c'] = number_format($psag_c, $keta);
    /////////////////////////////////////////////////////////////////////// ���ץ� END

    /////////////////////////////////////////////////////////////////////// ��˥� START
    ///// ��ê����ۤ򣳷奫��ޤǥϥå��������
    $tbody['tbody_kump_l'] = number_format($pmonth['��˥���Ω�ų�'], $keta);
    $tbody['tbody_sizp_l'] = number_format($pmonth['��˥��������'], $keta);
    $tbody['tbody_genp_l'] = number_format($pmonth['��˥�������']  , $keta);
    $tbody['tbody_koup_l'] = number_format($pmonth['��˥�����ų�'], $keta);
    $tbody['tbody_kenp_l'] = number_format($pmonth['��˥������ų�'], $keta);
    $tbody['tbody_gaip_l'] = number_format($pmonth['��˥�����ų�'], $keta);
    $tbody['tbody_ccp_l']  = number_format($pmonth['��˥��ã�����'], $keta);
    $tbody['tbody_zaip_l'] = number_format($pmonth['��˥���̳���'], $keta);
    ///// ���ʹ�פȺ߸˹�׵ڤӺ�̳���ɾ���ۤȺ߸˹�פȤκ���(��¤������¾)��׻�
    $pbuhin_l = $pmonth['��˥��������']+$pmonth['��˥�������']+$pmonth['��˥�����ų�']+
                $pmonth['��˥������ų�']+$pmonth['��˥�����ų�']+$pmonth['��˥��ã�����'];
    $psum_l = $pbuhin_l + $pmonth['��˥���Ω�ų�'];
    $psag_l = $pmonth['��˥���̳���'] - $psum_l;
    ///// �׻���̤�ϥå��������
    $tbody['tbody_buhp_l'] = number_format($pbuhin_l, $keta);
    $tbody['tbody_goup_l'] = number_format($psum_l, $keta);
    $tbody['tbody_sagp_l'] = number_format($psag_l, $keta);
    /////////////////////////////////////////////////////////////////////// ��˥� END
    if ($p1_ym >= 201504 || $p1_ym <= 202103) {
        /////////////////////////////////////////////////////////////////////// �ġ��� START
        ///// ��ê����ۤ򣳷奫��ޤǥϥå��������
        $tbody['tbody_kump_t'] = number_format($pmonth['�ġ�����Ω�ų�'], $keta);
        $tbody['tbody_sizp_t'] = number_format($pmonth['�ġ���������'], $keta);
        $tbody['tbody_genp_t'] = number_format($pmonth['�ġ��븶����']  , $keta);
        $tbody['tbody_koup_t'] = number_format($pmonth['�ġ��빩��ų�'], $keta);
        $tbody['tbody_kenp_t'] = number_format($pmonth['�ġ��븡���ų�'], $keta);
        $tbody['tbody_gaip_t'] = number_format($pmonth['�ġ��볰��ų�'], $keta);
        $tbody['tbody_ccp_t']  = number_format($pmonth['�ġ���ã�����'], $keta);
        $tbody['tbody_zaip_t'] = number_format($pmonth['�ġ����̳���'], $keta);
        ///// ���ʹ�פȺ߸˹�׵ڤӺ�̳���ɾ���ۤȺ߸˹�פȤκ���(��¤������¾)��׻�
        $pbuhin_t = $pmonth['�ġ���������']+$pmonth['�ġ��븶����']+$pmonth['�ġ��빩��ų�']+
                    $pmonth['�ġ��븡���ų�']+$pmonth['�ġ��볰��ų�']+$pmonth['�ġ���ã�����'];
        $psum_t = $pbuhin_t + $pmonth['�ġ�����Ω�ų�'];
        $psag_t = $pmonth['�ġ����̳���'] - $psum_t;
        ///// �׻���̤�ϥå��������
        $tbody['tbody_buhp_t'] = number_format($pbuhin_t, $keta);
        $tbody['tbody_goup_t'] = number_format($psum_t, $keta);
        $tbody['tbody_sagp_t'] = number_format($psag_t, $keta);
        /////////////////////////////////////////////////////////////////////// �ġ��� END
        
        /////////////////////////////////////////////////////////////////////// ���� START
        ///// ��ê����ۤ򣳷奫��ޤǥϥå��������
        $tbody['tbody_kump_a'] = number_format($pmonth['���ץ���Ω�ų�'] + $pmonth['��˥���Ω�ų�'] + $pmonth['�ġ�����Ω�ų�'], $keta);
        $tbody['tbody_sizp_a'] = number_format($pmonth['���ץ�������'] + $pmonth['��˥��������'] + $pmonth['�ġ���������'], $keta);
        $tbody['tbody_genp_a'] = number_format($pmonth['���ץ鸶����']   + $pmonth['��˥�������']   + $pmonth['�ġ��븶����']  , $keta);
        $tbody['tbody_koup_a'] = number_format($pmonth['���ץ鹩��ų�'] + $pmonth['��˥�����ų�'] + $pmonth['�ġ��빩��ų�'], $keta);
        $tbody['tbody_kenp_a'] = number_format($pmonth['���ץ鸡���ų�'] + $pmonth['��˥������ų�'] + $pmonth['�ġ��븡���ų�'], $keta);
        $tbody['tbody_gaip_a'] = number_format($pmonth['���ץ鳰��ų�'] + $pmonth['��˥�����ų�'] + $pmonth['�ġ��볰��ų�'], $keta);
        $tbody['tbody_ccp_a']  = number_format($pmonth['���ץ�ã�����'] + $pmonth['��˥��ã�����'] + $pmonth['�ġ���ã�����'], $keta);
        $tbody['tbody_zaip_a'] = number_format($pmonth['���ץ��̳���'] + $pmonth['��˥���̳���'] + $pmonth['�ġ����̳���'], $keta);
        ///// ���ʹ�פȺ߸˹�׵ڤӺ�̳���ɾ���ۤȺ߸˹�פȤκ���(��¤������¾)��׻�
        $pbuhin_a = $pbuhin_c + $pbuhin_l + $pbuhin_t;
        $psum_a = $pbuhin_a + $pmonth['���ץ���Ω�ų�'] + $pmonth['��˥���Ω�ų�'] + $pmonth['�ġ�����Ω�ų�'];
        $psag_a = ($pmonth['���ץ��̳���'] + $pmonth['��˥���̳���'] + $pmonth['�ġ����̳���']) - $psum_a;
        ///// �׻���̤�ϥå��������
        $tbody['tbody_buhp_a'] = number_format($pbuhin_a, $keta);
        $tbody['tbody_goup_a'] = number_format($psum_a, $keta);
        $tbody['tbody_sagp_a'] = number_format($psag_a, $keta);
        /////////////////////////////////////////////////////////////////////// ���� END
    } else {
        /////////////////////////////////////////////////////////////////////// ���� START
        ///// ��ê����ۤ򣳷奫��ޤǥϥå��������
        $tbody['tbody_kump_a'] = number_format($pmonth['���ץ���Ω�ų�'] + $pmonth['��˥���Ω�ų�'], $keta);
        $tbody['tbody_sizp_a'] = number_format($pmonth['���ץ�������'] + $pmonth['��˥��������'], $keta);
        $tbody['tbody_genp_a'] = number_format($pmonth['���ץ鸶����']   + $pmonth['��˥�������']  , $keta);
        $tbody['tbody_koup_a'] = number_format($pmonth['���ץ鹩��ų�'] + $pmonth['��˥�����ų�'], $keta);
        $tbody['tbody_kenp_a'] = number_format($pmonth['���ץ鸡���ų�'] + $pmonth['��˥������ų�'], $keta);
        $tbody['tbody_gaip_a'] = number_format($pmonth['���ץ鳰��ų�'] + $pmonth['��˥�����ų�'], $keta);
        $tbody['tbody_ccp_a']  = number_format($pmonth['���ץ�ã�����'] + $pmonth['��˥��ã�����'], $keta);
        $tbody['tbody_zaip_a'] = number_format($pmonth['���ץ��̳���'] + $pmonth['��˥���̳���'], $keta);
        ///// ���ʹ�פȺ߸˹�׵ڤӺ�̳���ɾ���ۤȺ߸˹�פȤκ���(��¤������¾)��׻�
        $pbuhin_a = $pbuhin_c + $pbuhin_l;
        $psum_a = $pbuhin_a + $pmonth['���ץ���Ω�ų�'] + $pmonth['��˥���Ω�ų�'];
        $psag_a = ($pmonth['���ץ��̳���'] + $pmonth['��˥���̳���']) - $psum_a;
        ///// �׻���̤�ϥå��������
        $tbody['tbody_buhp_a'] = number_format($pbuhin_a, $keta);
        $tbody['tbody_goup_a'] = number_format($psum_a, $keta);
        $tbody['tbody_sagp_a'] = number_format($psag_a, $keta);
        /////////////////////////////////////////////////////////////////////// ���� END
    }
}

    ///// ������    �Ȥꤢ�������ϻȤ�ʤ�
// $ppmonth = array();
// $query = "select item, kin from act_comp_invent_history where invent_ym=$p2_ym";
// $pprows = getResult2($query, $ppmonth);

    ///// ���� (�������η軻�ǡ���)
$kisyu_month = array();
$query = "select item, kin from act_comp_invent_history where invent_ym=$pre_end_ym";
if (($kisyu_rows = getResult2($query, $kisyu_month)) <= 0) {
    $_SESSION['s_sysmsg'] = sprintf("���ê��ɽ�δ���ǡ����ʤ���<br>%d", $pre_end_ym);
    header("Location: $url_referer");
    exit();
} else {
    ///// item ��̾���ȶ�ۤ�����ñ�̤Ⱦ�������ǥϥå��������
    for ($r=0; $r<$kisyu_rows; $r++) {
        //$kisyu_month["{$kisyu_month[$r][0]}"] = Uround($kisyu_month[$r][1] / $tani, $keta);
        $kisyu_month["{$kisyu_month[$r][0]}"] = $kisyu_month[$r][1] / $tani;
    }
    /////////////////////////////////////////////////////////////////////// ���ץ� START
    ///// ��ê����ۤ򣳷奫��ޤǥϥå��������
    $tbody['tbody_kum3_c'] = number_format($kisyu_month['���ץ���Ω�ų�'], $keta);
    $tbody['tbody_siz3_c'] = number_format($kisyu_month['���ץ�������'], $keta);
    $tbody['tbody_gen3_c'] = number_format($kisyu_month['���ץ鸶����']  , $keta);
    $tbody['tbody_kou3_c'] = number_format($kisyu_month['���ץ鹩��ų�'], $keta);
    $tbody['tbody_ken3_c'] = number_format($kisyu_month['���ץ鸡���ų�'], $keta);
    $tbody['tbody_gai3_c'] = number_format($kisyu_month['���ץ鳰��ų�'], $keta);
    $tbody['tbody_cc3_c']  = number_format($kisyu_month['���ץ�ã�����'], $keta);
    $tbody['tbody_zai3_c'] = number_format($kisyu_month['���ץ��̳���'], $keta);
    ///// ���ʹ�פȺ߸˹�׵ڤӺ�̳���ɾ���ۤȺ߸˹�פȤκ���(��¤������¾)��׻�
    $buhin3_c = $kisyu_month['���ץ�������']+$kisyu_month['���ץ鸶����']+$kisyu_month['���ץ鹩��ų�']+
                $kisyu_month['���ץ鸡���ų�']+$kisyu_month['���ץ鳰��ų�']+$kisyu_month['���ץ�ã�����'];
    $sum3_c = $buhin3_c + $kisyu_month['���ץ���Ω�ų�'];
    $sag3_c = $kisyu_month['���ץ��̳���'] - $sum3_c;
    ///// �׻���̤�ϥå��������
    $tbody['tbody_buh3_c'] = number_format($buhin3_c, $keta);
    $tbody['tbody_gou3_c'] = number_format($sum3_c, $keta);
    $tbody['tbody_sag3_c'] = number_format($sag3_c, $keta);
    /////////////////////////////////////////////////////////////////////// ���ץ� END

    /////////////////////////////////////////////////////////////////////// ��˥� START
    ///// ��ê����ۤ򣳷奫��ޤǥϥå��������
    $tbody['tbody_kum3_l'] = number_format($kisyu_month['��˥���Ω�ų�'], $keta);
    $tbody['tbody_siz3_l'] = number_format($kisyu_month['��˥��������'], $keta);
    $tbody['tbody_gen3_l'] = number_format($kisyu_month['��˥�������']  , $keta);
    $tbody['tbody_kou3_l'] = number_format($kisyu_month['��˥�����ų�'], $keta);
    $tbody['tbody_ken3_l'] = number_format($kisyu_month['��˥������ų�'], $keta);
    $tbody['tbody_gai3_l'] = number_format($kisyu_month['��˥�����ų�'], $keta);
    $tbody['tbody_cc3_l']  = number_format($kisyu_month['��˥��ã�����'], $keta);
    $tbody['tbody_zai3_l'] = number_format($kisyu_month['��˥���̳���'], $keta);
    ///// ���ʹ�פȺ߸˹�׵ڤӺ�̳���ɾ���ۤȺ߸˹�פȤκ���(��¤������¾)��׻�
    $buhin3_l = $kisyu_month['��˥��������']+$kisyu_month['��˥�������']+$kisyu_month['��˥�����ų�']+
                $kisyu_month['��˥������ų�']+$kisyu_month['��˥�����ų�']+$kisyu_month['��˥��ã�����'];
    $sum3_l = $buhin3_l + $kisyu_month['��˥���Ω�ų�'];
    $sag3_l = $kisyu_month['��˥���̳���'] - $sum3_l;
    ///// �׻���̤�ϥå��������
    $tbody['tbody_buh3_l'] = number_format($buhin3_l, $keta);
    $tbody['tbody_gou3_l'] = number_format($sum3_l, $keta);
    $tbody['tbody_sag3_l'] = number_format($sag3_l, $keta);
    /////////////////////////////////////////////////////////////////////// ��˥� END
    if ($pre_end_ym >= 201504 || $pre_end_ym <= 202103) {
        /////////////////////////////////////////////////////////////////////// �ġ��� START
        ///// ��ê����ۤ򣳷奫��ޤǥϥå��������
        $tbody['tbody_kum3_t'] = number_format($kisyu_month['�ġ�����Ω�ų�'], $keta);
        $tbody['tbody_siz3_t'] = number_format($kisyu_month['�ġ���������'], $keta);
        $tbody['tbody_gen3_t'] = number_format($kisyu_month['�ġ��븶����']  , $keta);
        $tbody['tbody_kou3_t'] = number_format($kisyu_month['�ġ��빩��ų�'], $keta);
        $tbody['tbody_ken3_t'] = number_format($kisyu_month['�ġ��븡���ų�'], $keta);
        $tbody['tbody_gai3_t'] = number_format($kisyu_month['�ġ��볰��ų�'], $keta);
        $tbody['tbody_cc3_t']  = number_format($kisyu_month['�ġ���ã�����'], $keta);
        $tbody['tbody_zai3_t'] = number_format($kisyu_month['�ġ����̳���'], $keta);
        ///// ���ʹ�פȺ߸˹�׵ڤӺ�̳���ɾ���ۤȺ߸˹�פȤκ���(��¤������¾)��׻�
        $buhin3_t = $kisyu_month['�ġ���������']+$kisyu_month['�ġ��븶����']+$kisyu_month['�ġ��빩��ų�']+
                    $kisyu_month['�ġ��븡���ų�']+$kisyu_month['�ġ��볰��ų�']+$kisyu_month['�ġ���ã�����'];
        $sum3_t = $buhin3_t + $kisyu_month['�ġ�����Ω�ų�'];
        $sag3_t = $kisyu_month['�ġ����̳���'] - $sum3_t;
        ///// �׻���̤�ϥå��������
        $tbody['tbody_buh3_t'] = number_format($buhin3_t, $keta);
        $tbody['tbody_gou3_t'] = number_format($sum3_t, $keta);
        $tbody['tbody_sag3_t'] = number_format($sag3_t, $keta);
        /////////////////////////////////////////////////////////////////////// �ġ��� END
        
        /////////////////////////////////////////////////////////////////////// ���� START
        ///// ��ê����ۤ򣳷奫��ޤǥϥå��������
        $tbody['tbody_kum3_a'] = number_format($kisyu_month['���ץ���Ω�ų�'] + $kisyu_month['��˥���Ω�ų�'] + $kisyu_month['�ġ�����Ω�ų�'], $keta);
        $tbody['tbody_siz3_a'] = number_format($kisyu_month['���ץ�������'] + $kisyu_month['��˥��������'] + $kisyu_month['�ġ���������'], $keta);
        $tbody['tbody_gen3_a'] = number_format($kisyu_month['���ץ鸶����']   + $kisyu_month['��˥�������']   + $kisyu_month['�ġ��븶����']  , $keta);
        $tbody['tbody_kou3_a'] = number_format($kisyu_month['���ץ鹩��ų�'] + $kisyu_month['��˥�����ų�'] + $kisyu_month['�ġ��빩��ų�'], $keta);
        $tbody['tbody_ken3_a'] = number_format($kisyu_month['���ץ鸡���ų�'] + $kisyu_month['��˥������ų�'] + $kisyu_month['�ġ��븡���ų�'], $keta);
        $tbody['tbody_gai3_a'] = number_format($kisyu_month['���ץ鳰��ų�'] + $kisyu_month['��˥�����ų�'] + $kisyu_month['�ġ��볰��ų�'], $keta);
        $tbody['tbody_cc3_a']  = number_format($kisyu_month['���ץ�ã�����'] + $kisyu_month['��˥��ã�����'] + $kisyu_month['�ġ���ã�����'], $keta);
        $tbody['tbody_zai3_a'] = number_format($kisyu_month['���ץ��̳���'] + $kisyu_month['��˥���̳���'] + $kisyu_month['�ġ����̳���'], $keta);
        ///// ���ʹ�פȺ߸˹�׵ڤӺ�̳���ɾ���ۤȺ߸˹�פȤκ���(��¤������¾)��׻�
        $buhin3_a = $buhin3_c + $buhin3_l + $buhin3_t;
        $sum3_a = $buhin3_a + $kisyu_month['���ץ���Ω�ų�'] + $kisyu_month['��˥���Ω�ų�'] + $kisyu_month['�ġ�����Ω�ų�'];
        $sag3_a = ($kisyu_month['���ץ��̳���'] + $kisyu_month['��˥���̳���'] + $kisyu_month['�ġ����̳���']) - $sum3_a;
        ///// �׻���̤�ϥå��������
        $tbody['tbody_buh3_a'] = number_format($buhin3_a, $keta);
        $tbody['tbody_gou3_a'] = number_format($sum3_a, $keta);
        $tbody['tbody_sag3_a'] = number_format($sag3_a, $keta);
        /////////////////////////////////////////////////////////////////////// ���� END
    } else {
        /////////////////////////////////////////////////////////////////////// ���� START
        ///// ��ê����ۤ򣳷奫��ޤǥϥå��������
        $tbody['tbody_kum3_a'] = number_format($kisyu_month['���ץ���Ω�ų�'] + $kisyu_month['��˥���Ω�ų�'], $keta);
        $tbody['tbody_siz3_a'] = number_format($kisyu_month['���ץ�������'] + $kisyu_month['��˥��������'], $keta);
        $tbody['tbody_gen3_a'] = number_format($kisyu_month['���ץ鸶����']   + $kisyu_month['��˥�������']  , $keta);
        $tbody['tbody_kou3_a'] = number_format($kisyu_month['���ץ鹩��ų�'] + $kisyu_month['��˥�����ų�'], $keta);
        $tbody['tbody_ken3_a'] = number_format($kisyu_month['���ץ鸡���ų�'] + $kisyu_month['��˥������ų�'], $keta);
        $tbody['tbody_gai3_a'] = number_format($kisyu_month['���ץ鳰��ų�'] + $kisyu_month['��˥�����ų�'], $keta);
        $tbody['tbody_cc3_a']  = number_format($kisyu_month['���ץ�ã�����'] + $kisyu_month['��˥��ã�����'], $keta);
        $tbody['tbody_zai3_a'] = number_format($kisyu_month['���ץ��̳���'] + $kisyu_month['��˥���̳���'], $keta);
        ///// ���ʹ�פȺ߸˹�׵ڤӺ�̳���ɾ���ۤȺ߸˹�פȤκ���(��¤������¾)��׻�
        $buhin3_a = $buhin3_c + $buhin3_l;
        $sum3_a = $buhin3_a + $kisyu_month['���ץ���Ω�ų�'] + $kisyu_month['��˥���Ω�ų�'];
        $sag3_a = ($kisyu_month['���ץ��̳���'] + $kisyu_month['��˥���̳���']) - $sum3_a;
        ///// �׻���̤�ϥå��������
        $tbody['tbody_buh3_a'] = number_format($buhin3_a, $keta);
        $tbody['tbody_gou3_a'] = number_format($sum3_a, $keta);
        $tbody['tbody_sag3_a'] = number_format($sag3_a, $keta);
        /////////////////////////////////////////////////////////////////////// ���� END
    }
}

////////// ������������׻����ϥå��������
    /////////////////////////////////////////////////////////////////////// ���ץ� START
$tbody['tbody_kumz_c'] = number_format($month['���ץ���Ω�ų�'] - $pmonth['���ץ���Ω�ų�'], $keta);
$tbody['tbody_sizz_c'] = number_format($month['���ץ�������'] - $pmonth['���ץ�������'], $keta);
$tbody['tbody_genz_c'] = number_format($month['���ץ鸶����']   - $pmonth['���ץ鸶����']  , $keta);
$tbody['tbody_kouz_c'] = number_format($month['���ץ鹩��ų�'] - $pmonth['���ץ鹩��ų�'], $keta);
$tbody['tbody_kenz_c'] = number_format($month['���ץ鸡���ų�'] - $pmonth['���ץ鸡���ų�'], $keta);
$tbody['tbody_gaiz_c'] = number_format($month['���ץ鳰��ų�'] - $pmonth['���ץ鳰��ų�'], $keta);
$tbody['tbody_ccz_c']  = number_format($month['���ץ�ã�����'] - $pmonth['���ץ�ã�����'], $keta);
$tbody['tbody_buhz_c'] = number_format($buhin_c - $pbuhin_c, $keta);    // ���ʹ��
$tbody['tbody_gouz_c'] = number_format($sum_c - $psum_c, $keta);        // �߸˹��
$tbody['tbody_sagz_c'] = number_format($sag_c - $psag_c, $keta);        // ����(��¤������¾)
$tbody['tbody_zaiz_c'] = number_format($month['���ץ��̳���'] - $pmonth['���ץ��̳���'], $keta);
    /////////////////////////////////////////////////////////////////////// ���ץ� END

    /////////////////////////////////////////////////////////////////////// ��˥� START
$tbody['tbody_kumz_l'] = number_format($month['��˥���Ω�ų�'] - $pmonth['��˥���Ω�ų�'], $keta);
$tbody['tbody_sizz_l'] = number_format($month['��˥��������'] - $pmonth['��˥��������'], $keta);
$tbody['tbody_genz_l'] = number_format($month['��˥�������']   - $pmonth['��˥�������']  , $keta);
$tbody['tbody_kouz_l'] = number_format($month['��˥�����ų�'] - $pmonth['��˥�����ų�'], $keta);
$tbody['tbody_kenz_l'] = number_format($month['��˥������ų�'] - $pmonth['��˥������ų�'], $keta);
$tbody['tbody_gaiz_l'] = number_format($month['��˥�����ų�'] - $pmonth['��˥�����ų�'], $keta);
$tbody['tbody_ccz_l']  = number_format($month['��˥��ã�����'] - $pmonth['��˥��ã�����'], $keta);
$tbody['tbody_buhz_l'] = number_format($buhin_l - $pbuhin_l, $keta);    // ���ʹ��
$tbody['tbody_gouz_l'] = number_format($sum_l - $psum_l, $keta);        // �߸˹��
$tbody['tbody_sagz_l'] = number_format($sag_l - $psag_l, $keta);        // ����(��¤������¾)
$tbody['tbody_zaiz_l'] = number_format($month['��˥���̳���'] - $pmonth['��˥���̳���'], $keta);
    /////////////////////////////////////////////////////////////////////// ��˥� END
if ($yyyymm >= 201504 && $yyyymm <= 202103) {
    /////////////////////////////////////////////////////////////////////// �ġ��� START
    $tbody['tbody_kumz_t'] = number_format($month['�ġ�����Ω�ų�'] - $pmonth['�ġ�����Ω�ų�'], $keta);
    $tbody['tbody_sizz_t'] = number_format($month['�ġ���������'] - $pmonth['�ġ���������'], $keta);
    $tbody['tbody_genz_t'] = number_format($month['�ġ��븶����']   - $pmonth['�ġ��븶����']  , $keta);
    $tbody['tbody_kouz_t'] = number_format($month['�ġ��빩��ų�'] - $pmonth['�ġ��빩��ų�'], $keta);
    $tbody['tbody_kenz_t'] = number_format($month['�ġ��븡���ų�'] - $pmonth['�ġ��븡���ų�'], $keta);
    $tbody['tbody_gaiz_t'] = number_format($month['�ġ��볰��ų�'] - $pmonth['�ġ��볰��ų�'], $keta);
    $tbody['tbody_ccz_t']  = number_format($month['�ġ���ã�����'] - $pmonth['�ġ���ã�����'], $keta);
    $tbody['tbody_buhz_t'] = number_format($buhin_t - $pbuhin_t, $keta);    // ���ʹ��
    $tbody['tbody_gouz_t'] = number_format($sum_t - $psum_t, $keta);        // �߸˹��
    $tbody['tbody_sagz_t'] = number_format($sag_t - $psag_t, $keta);        // ����(��¤������¾)
    $tbody['tbody_zaiz_t'] = number_format($month['�ġ����̳���'] - $pmonth['�ġ����̳���'], $keta);
    /////////////////////////////////////////////////////////////////////// �ġ��� END
    
    /////////////////////////////////////////////////////////////////////// ���� START
    $tbody['tbody_kumz_a'] = number_format( ($month['���ץ���Ω�ų�'] - $pmonth['���ץ���Ω�ų�']) + ($month['��˥���Ω�ų�'] - $pmonth['��˥���Ω�ų�']) + ($month['�ġ�����Ω�ų�'] - $pmonth['�ġ�����Ω�ų�']), $keta);
    $tbody['tbody_sizz_a'] = number_format( ($month['���ץ�������'] - $pmonth['���ץ�������']) + ($month['��˥��������'] - $pmonth['��˥��������']) + ($month['�ġ���������'] - $pmonth['�ġ���������']), $keta);
    $tbody['tbody_genz_a'] = number_format( ($month['���ץ鸶����']   - $pmonth['���ץ鸶����'])   + ($month['��˥�������']   - $pmonth['��˥�������'])   + ($month['�ġ��븶����']   - $pmonth['�ġ��븶����'])  , $keta);
    $tbody['tbody_kouz_a'] = number_format( ($month['���ץ鹩��ų�'] - $pmonth['���ץ鹩��ų�']) + ($month['��˥�����ų�'] - $pmonth['��˥�����ų�']) + ($month['�ġ��빩��ų�'] - $pmonth['�ġ��빩��ų�']), $keta);
    $tbody['tbody_kenz_a'] = number_format( ($month['���ץ鸡���ų�'] - $pmonth['���ץ鸡���ų�']) + ($month['��˥������ų�'] - $pmonth['��˥������ų�']) + ($month['�ġ��븡���ų�'] - $pmonth['�ġ��븡���ų�']), $keta);
    $tbody['tbody_gaiz_a'] = number_format( ($month['���ץ鳰��ų�'] - $pmonth['���ץ鳰��ų�']) + ($month['��˥�����ų�'] - $pmonth['��˥�����ų�']) + ($month['�ġ��볰��ų�'] - $pmonth['�ġ��볰��ų�']), $keta);
    $tbody['tbody_ccz_a']  = number_format( ($month['���ץ�ã�����'] - $pmonth['���ץ�ã�����']) + ($month['��˥��ã�����'] - $pmonth['��˥��ã�����']) + ($month['�ġ���ã�����'] - $pmonth['�ġ���ã�����']), $keta);
    $tbody['tbody_buhz_a'] = number_format( ($buhin_c - $pbuhin_c) + ($buhin_l - $pbuhin_l) + ($buhin_t - $pbuhin_t), $keta);    // ���ʹ��
    $tbody['tbody_gouz_a'] = number_format( ($sum_c - $psum_c)     + ($sum_l - $psum_l)     + ($sum_t - $psum_t), $keta);        // �߸˹��
    $tbody['tbody_sagz_a'] = number_format( ($sag_c - $psag_c)     + ($sag_l - $psag_l)     + ($sag_t - $psag_t), $keta);        // ����(��¤������¾)
    $tbody['tbody_zaiz_a'] = number_format( ($month['���ץ��̳���'] - $pmonth['���ץ��̳���']) + ($month['��˥���̳���'] - $pmonth['��˥���̳���']) + ($month['�ġ����̳���'] - $pmonth['�ġ����̳���']), $keta);
    /////////////////////////////////////////////////////////////////////// ���� END
} else {
    /////////////////////////////////////////////////////////////////////// ���� START
    $tbody['tbody_kumz_a'] = number_format( ($month['���ץ���Ω�ų�'] - $pmonth['���ץ���Ω�ų�']) + ($month['��˥���Ω�ų�'] - $pmonth['��˥���Ω�ų�']), $keta);
    $tbody['tbody_sizz_a'] = number_format( ($month['���ץ�������'] - $pmonth['���ץ�������']) + ($month['��˥��������'] - $pmonth['��˥��������']), $keta);
    $tbody['tbody_genz_a'] = number_format( ($month['���ץ鸶����']   - $pmonth['���ץ鸶����'])   + ($month['��˥�������']   - $pmonth['��˥�������'])  , $keta);
    $tbody['tbody_kouz_a'] = number_format( ($month['���ץ鹩��ų�'] - $pmonth['���ץ鹩��ų�']) + ($month['��˥�����ų�'] - $pmonth['��˥�����ų�']), $keta);
    $tbody['tbody_kenz_a'] = number_format( ($month['���ץ鸡���ų�'] - $pmonth['���ץ鸡���ų�']) + ($month['��˥������ų�'] - $pmonth['��˥������ų�']), $keta);
    $tbody['tbody_gaiz_a'] = number_format( ($month['���ץ鳰��ų�'] - $pmonth['���ץ鳰��ų�']) + ($month['��˥�����ų�'] - $pmonth['��˥�����ų�']), $keta);
    $tbody['tbody_ccz_a']  = number_format( ($month['���ץ�ã�����'] - $pmonth['���ץ�ã�����']) + ($month['��˥��ã�����'] - $pmonth['��˥��ã�����']), $keta);
    $tbody['tbody_buhz_a'] = number_format( ($buhin_c - $pbuhin_c) + ($buhin_l - $pbuhin_l), $keta);    // ���ʹ��
    $tbody['tbody_gouz_a'] = number_format( ($sum_c - $psum_c)     + ($sum_l - $psum_l), $keta);        // �߸˹��
    $tbody['tbody_sagz_a'] = number_format( ($sag_c - $psag_c)     + ($sag_l - $psag_l), $keta);        // ����(��¤������¾)
    $tbody['tbody_zaiz_a'] = number_format( ($month['���ץ��̳���'] - $pmonth['���ץ��̳���']) + ($month['��˥���̳���'] - $pmonth['��˥���̳���']), $keta);
    /////////////////////////////////////////////////////////////////////// ���� END
}

////////////////////////////////////////////// ���߸����� tbody2 �Υϥå�������
    ///// ����
$tbody2['tbody2_c1p']  = number_format($pmonth['���ץ飱']  , $keta);
$tbody2['tbody2_c2p']  = number_format($pmonth['���ץ飲']  , $keta);
$tbody2['tbody2_c3p']  = number_format($pmonth['���ץ飳']  , $keta);
$tbody2['tbody2_c4p']  = number_format($pmonth['���ץ飴']  , $keta);
$tbody2['tbody2_c5p']  = number_format($pmonth['���ץ飵']  , $keta);
$tbody2['tbody2_c6p']  = number_format($pmonth['���ץ飶']  , $keta);
$tbody2['tbody2_c7p']  = number_format($pmonth['���ץ飷']  , $keta);
$tbody2['tbody2_c8p']  = number_format($pmonth['���ץ飸']  , $keta);
$tbody2['tbody2_c9p']  = number_format($pmonth['���ץ飹']  , $keta);
$tbody2['tbody2_c10p'] = number_format($pmonth['���ץ飱��'], $keta);
$tbody2['tbody2_c11p'] = number_format($pmonth['���ץ飱��'], $keta);

$tbody2['tbody2_l1p'] = number_format($pmonth['��˥���'], $keta);
$tbody2['tbody2_l2p'] = number_format($pmonth['��˥���'], $keta);
$tbody2['tbody2_l3p'] = number_format($pmonth['��˥���'], $keta);
$tbody2['tbody2_l4p'] = number_format($pmonth['��˥���'], $keta);
$tbody2['tbody2_l5p'] = number_format($pmonth['��˥���'], $keta);
$tbody2['tbody2_l6p'] = number_format($pmonth['��˥���'], $keta);
$tbody2['tbody2_l7p'] = number_format($pmonth['��˥���'], $keta);
if ($yyyymm >= 202001) {
    $tbody2['tbody2_l8p'] = number_format($pmonth['��˥���'], $keta);
}

if ($yyyymm <= 200912) {
    $tbody2['tbody2_l8p'] = number_format($pmonth['��˥���'], $keta);
    $tbody2['tbody2_l9p'] = number_format($pmonth['��˥���'], $keta);
}

    ///// ����
$tbody2['tbody2_c1'] = number_format($month['���ץ飱'], $keta);
$tbody2['tbody2_c2'] = number_format($month['���ץ飲'], $keta);
$tbody2['tbody2_c3'] = number_format($month['���ץ飳'], $keta);
$tbody2['tbody2_c4'] = number_format($month['���ץ飴'], $keta);
$tbody2['tbody2_c5'] = number_format($month['���ץ飵'], $keta);
$tbody2['tbody2_c6'] = number_format($month['���ץ飶'], $keta);
$tbody2['tbody2_c7'] = number_format($month['���ץ飷'], $keta);
$tbody2['tbody2_c8'] = number_format($month['���ץ飸'], $keta);
$tbody2['tbody2_c9'] = number_format($month['���ץ飹'], $keta);
$tbody2['tbody2_c10'] = number_format($month['���ץ飱��'], $keta);
$tbody2['tbody2_c11'] = number_format($month['���ץ飱��'], $keta);

$tbody2['tbody2_l1'] = number_format($month['��˥���'], $keta);
$tbody2['tbody2_l2'] = number_format($month['��˥���'], $keta);
$tbody2['tbody2_l3'] = number_format($month['��˥���'], $keta);
$tbody2['tbody2_l4'] = number_format($month['��˥���'], $keta);
if ($yyyymm == 201912) {
    $tbody2['tbody2_l5'] = number_format($month['��˥���'], $keta);
    $tbody2['tbody2_l6'] = number_format($month['��˥���']+$month['��˥���'], $keta);
    $tbody2['tbody2_l7'] = number_format($month['��˥���'], $keta);
} else {
    $tbody2['tbody2_l5'] = number_format($month['��˥���'], $keta);
    $tbody2['tbody2_l6'] = number_format($month['��˥���'], $keta);
    $tbody2['tbody2_l7'] = number_format($month['��˥���'], $keta);
}
if ($yyyymm >= 202001) {
    $tbody2['tbody2_l8'] = number_format($month['��˥���'], $keta);
}
if ($yyyymm <= 200912) {
    $tbody2['tbody2_l8'] = number_format($month['��˥���'], $keta);
    $tbody2['tbody2_l9'] = number_format($month['��˥���'], $keta);
}
    ///// ��������
$tbody2['tbody2_c1_zou']  = number_format($month['���ץ飱']   - $pmonth['���ץ飱']  , $keta);
$tbody2['tbody2_c2_zou']  = number_format($month['���ץ飲']   - $pmonth['���ץ飲']  , $keta);
$tbody2['tbody2_c3_zou']  = number_format($month['���ץ飳']   - $pmonth['���ץ飳']  , $keta);
$tbody2['tbody2_c4_zou']  = number_format($month['���ץ飴']   - $pmonth['���ץ飴']  , $keta);
$tbody2['tbody2_c5_zou']  = number_format($month['���ץ飵']   - $pmonth['���ץ飵']  , $keta);
$tbody2['tbody2_c6_zou']  = number_format($month['���ץ飶']   - $pmonth['���ץ飶']  , $keta);
$tbody2['tbody2_c7_zou']  = number_format($month['���ץ飷']   - $pmonth['���ץ飷']  , $keta);
$tbody2['tbody2_c8_zou']  = number_format($month['���ץ飸']   - $pmonth['���ץ飸']  , $keta);
$tbody2['tbody2_c9_zou']  = number_format($month['���ץ飹']   - $pmonth['���ץ飹']  , $keta);
$tbody2['tbody2_c10_zou'] = number_format($month['���ץ飱��'] - $pmonth['���ץ飱��'], $keta);
$tbody2['tbody2_c11_zou'] = number_format($month['���ץ飱��'] - $pmonth['���ץ飱��'], $keta);

$tbody2['tbody2_l1_zou'] = number_format($month['��˥���'] - $pmonth['��˥���'], $keta);
$tbody2['tbody2_l2_zou'] = number_format($month['��˥���'] - $pmonth['��˥���'], $keta);
$tbody2['tbody2_l3_zou'] = number_format($month['��˥���'] - $pmonth['��˥���'], $keta);
$tbody2['tbody2_l4_zou'] = number_format($month['��˥���'] - $pmonth['��˥���'], $keta);
if ($yyyymm == 201912) {
    $tbody2['tbody2_l5_zou'] = number_format($month['��˥���'] - $pmonth['��˥���'], $keta);
    $tbody2['tbody2_l6_zou'] = number_format($month['��˥���'] + $month['��˥���'] - $pmonth['��˥���'], $keta);
    $tbody2['tbody2_l7_zou'] = number_format($month['��˥���'] - $pmonth['��˥���'], $keta);
} else {
    $tbody2['tbody2_l5_zou'] = number_format($month['��˥���'] - $pmonth['��˥���'], $keta);
    $tbody2['tbody2_l6_zou'] = number_format($month['��˥���'] - $pmonth['��˥���'], $keta);
    $tbody2['tbody2_l7_zou'] = number_format($month['��˥���'] - $pmonth['��˥���'], $keta);
}
if ($yyyymm >= 202001) {
    $tbody2['tbody2_l8_zou'] = number_format($month['��˥���'] - $pmonth['��˥���'], $keta);
}
if ($yyyymm <= 200912) {
    $tbody2['tbody2_l8_zou'] = number_format($month['��˥���'] - $pmonth['��˥���'], $keta);
    $tbody2['tbody2_l9_zou'] = number_format($month['��˥���'] - $pmonth['��˥���'], $keta);
}
//////////////////////////////////////////////////////////////////// ���߸����� tbody2 END

////////// ���߸����� ���
    ///// ����ץ�
$tbody2['tbody2_ckeip'] = 0;
$tbody2['tbody2_ckeip'] += $pmonth['���ץ飱']  ;
$tbody2['tbody2_ckeip'] += $pmonth['���ץ飲']  ;
$tbody2['tbody2_ckeip'] += $pmonth['���ץ飳']  ;
$tbody2['tbody2_ckeip'] += $pmonth['���ץ飴']  ;
$tbody2['tbody2_ckeip'] += $pmonth['���ץ飵']  ;
$tbody2['tbody2_ckeip'] += $pmonth['���ץ飶']  ;
$tbody2['tbody2_ckeip'] += $pmonth['���ץ飷']  ;
$tbody2['tbody2_ckeip'] += $pmonth['���ץ飸']  ;
$tbody2['tbody2_ckeip'] += $pmonth['���ץ飹']  ;
$tbody2['tbody2_ckeip'] += $pmonth['���ץ飱��'];
$tbody2['tbody2_ckeip'] += $pmonth['���ץ飱��'];
$tbody2['tbody2_ckeip'] = $pmonth['���ץ�������'];     // �ͼθ��������ʤ����ᤳ�����ɲ�
    ///// ����ץ�
$tbody2['tbody2_ckei'] = 0;
$tbody2['tbody2_ckei'] += $month['���ץ飱']  ;
$tbody2['tbody2_ckei'] += $month['���ץ飲']  ;
$tbody2['tbody2_ckei'] += $month['���ץ飳']  ;
$tbody2['tbody2_ckei'] += $month['���ץ飴']  ;
$tbody2['tbody2_ckei'] += $month['���ץ飵']  ;
$tbody2['tbody2_ckei'] += $month['���ץ飶']  ;
$tbody2['tbody2_ckei'] += $month['���ץ飷']  ;
$tbody2['tbody2_ckei'] += $month['���ץ飸']  ;
$tbody2['tbody2_ckei'] += $month['���ץ飹']  ;
$tbody2['tbody2_ckei'] += $month['���ץ飱��'];
$tbody2['tbody2_ckei'] += $month['���ץ飱��'];
$tbody2['tbody2_ckei'] = $month['���ץ�������'];     // �ͼθ��������ʤ����ᤳ�����ɲ�
    ///// ��������
$tbody2['tbody2_ckei_zou'] = number_format($tbody2['tbody2_ckei'] - $tbody2['tbody2_ckeip'], $keta);
    ///// ������� ��ץ��å�
$tbody2['tbody2_ckeip'] = number_format($tbody2['tbody2_ckeip'], $keta);
$tbody2['tbody2_ckei'] = number_format($tbody2['tbody2_ckei'], $keta);

    ///// �����˥�
$tbody2['tbody2_lkeip'] = 0;
$tbody2['tbody2_lkeip'] += $pmonth['��˥���']  ;
$tbody2['tbody2_lkeip'] += $pmonth['��˥���']  ;
$tbody2['tbody2_lkeip'] += $pmonth['��˥���']  ;
$tbody2['tbody2_lkeip'] += $pmonth['��˥���']  ;
$tbody2['tbody2_lkeip'] += $pmonth['��˥���']  ;
$tbody2['tbody2_lkeip'] += $pmonth['��˥���']  ;
$tbody2['tbody2_lkeip'] += $pmonth['��˥���']  ;
if ($yyyymm >= 202001) {
    $tbody2['tbody2_lkeip'] += $pmonth['��˥���']  ;
}
if ($yyyymm <= 200912) {
    $tbody2['tbody2_lkeip'] += $pmonth['��˥���']  ;
    $tbody2['tbody2_lkeip'] += $pmonth['��˥���']  ;
}
$tbody2['tbody2_lkeip'] = $pmonth['��˥��������'];     // �ͼθ��������ʤ����ᤳ�����ɲ�
    ///// �����˥�
$tbody2['tbody2_lkei'] = 0;
$tbody2['tbody2_lkei'] += $month['��˥���']  ;
$tbody2['tbody2_lkei'] += $month['��˥���']  ;
$tbody2['tbody2_lkei'] += $month['��˥���']  ;
$tbody2['tbody2_lkei'] += $month['��˥���']  ;
$tbody2['tbody2_lkei'] += $month['��˥���']  ;
$tbody2['tbody2_lkei'] += $month['��˥���']  ;
$tbody2['tbody2_lkei'] += $month['��˥���']  ;
if ($yyyymm >= 202001) {
    $tbody2['tbody2_lkei'] += $month['��˥���']  ;
}
if ($yyyymm <= 200912) {
    $tbody2['tbody2_lkei'] += $month['��˥���']  ;
    $tbody2['tbody2_lkei'] += $month['��˥���']  ;
}
$tbody2['tbody2_lkei'] = $month['��˥��������'];     // �ͼθ��������ʤ����ᤳ�����ɲ�
    ///// ��������
$tbody2['tbody2_lkei_zou'] = number_format($tbody2['tbody2_lkei'] - $tbody2['tbody2_lkeip'], $keta);
    ///// ������� ��ץ��å�
$tbody2['tbody2_lkeip'] = number_format($tbody2['tbody2_lkeip'], $keta);
$tbody2['tbody2_lkei'] = number_format($tbody2['tbody2_lkei'], $keta);


////////// ��ɸ�߸˶�� tbody3 �Υϥå�������
$tbody3['tbody3_moku_c'] = number_format($month['���ץ���ɸ'], $keta);
$tbody3['tbody3_moku_l'] = number_format($month['��˥���ɸ'], $keta);
if ($yyyymm >= 201504 && $yyyymm <= 202103) {
    $tbody3['tbody3_moku_t'] = number_format($month['�ġ�����ɸ'], $keta);
    $tbody3['tbody3_moku_a'] = number_format($month['���ץ���ɸ'] + $month['��˥���ɸ'] + $month['�ġ�����ɸ'], $keta);
} else {
    $tbody3['tbody3_moku_a'] = number_format($month['���ץ���ɸ'] + $month['��˥���ɸ'], $keta);
}
///// ����(�߸˹��)
$tbody3['tbody3_mon_c'] = $tbody['tbody_gou_c'];
$tbody3['tbody3_mon_l'] = $tbody['tbody_gou_l'];
if ($yyyymm >= 201504 && $yyyymm <= 202103) {
    $tbody3['tbody3_mon_t'] = $tbody['tbody_gou_t'];
    $tbody3['tbody3_mon_a'] = number_format($sum_c + $sum_l + $sum_t, $keta);
} else {
    $tbody3['tbody3_mon_a'] = number_format($sum_c + $sum_l, $keta);
}
///// ��ɸ���Ф�������
$tbody3['tbody3_zou_c'] = number_format($sum_c - $month['���ץ���ɸ'], $keta);
$tbody3['tbody3_zou_l'] = number_format($sum_l - $month['��˥���ɸ'], $keta);
if ($yyyymm >= 201504 && $yyyymm <= 202103) {
    $tbody3['tbody3_zou_t'] = number_format($sum_t - $month['�ġ�����ɸ'], $keta);
    $tbody3['tbody3_zou_a'] = number_format(($sum_c + $sum_l + $sum_t) - ($month['���ץ���ɸ'] + $month['��˥���ɸ'] + $month['�ġ�����ɸ']), $keta);
} else {
    $tbody3['tbody3_zou_a'] = number_format(($sum_c + $sum_l) - ($month['���ץ���ɸ'] + $month['��˥���ɸ']), $keta);
}

/********** patTemplate ��Ф� ************/
include_once ( '../../../patTemplate/include/patTemplate.php' );
$tmpl = new patTemplate();

//  In diesem Verzeichnis liegen die Templates
$tmpl->setBasedir( 'templates' );

if ($yyyymm >= 202104) {
    $tmpl->readTemplatesFromFile( 'getsuji_comp_invent_202104.templ.html' );
} elseif ($yyyymm >= 202001) {
    $tmpl->readTemplatesFromFile( 'getsuji_comp_invent_202001.templ.html' );
} elseif ($yyyymm >= 201604) {
    $tmpl->readTemplatesFromFile( 'getsuji_comp_invent_201604.templ.html' );
} elseif ($yyyymm >= 201504) {
    $tmpl->readTemplatesFromFile( 'getsuji_comp_invent_201504.templ.html' );
} elseif ($yyyymm >= 201001) {
    $tmpl->readTemplatesFromFile( 'getsuji_comp_invent_201001.templ.html' );
} else {
    $tmpl->readTemplatesFromFile( 'getsuji_comp_invent.templ.html' );
}


$tmpl->addVar('page', 'PAGE_TITLE'         , '���ê��ɽ');
$tmpl->addVar('page', 'PAGE_MENU_SITE_URL' , $menu_site_script);
$tmpl->addVar('page', 'PAGE_UNIQUE'        , $uniq);
$tmpl->addVar('page', 'PAGE_RETURN_URL'    , $url_referer);
$tmpl->addVar('page', 'PAGE_CURRENT_URL'   , $current_script);
$tmpl->addVar('page', 'PAGE_SITE_VIEW'     , $site_view);
$tmpl->addVar('page', 'PAGE_HEADER_TITLE'  , "��{$ki}�� {$tuki}�������ê��ɽ");
$tmpl->addVar('page', 'PAGE_HEADER_TODAY'  , $today);
$tmpl->addVar('page', 'OUT_CSS'            , $menu->out_css());
$tmpl->addVar('page', 'OUT_JSBASE'         , $menu->out_jsBaseClass());
$tmpl->addVar('page', 'OUT_TITLE_BORDER'   , $menu->out_title_border());

///// ɽ��ñ�̤�ƥ�ץ졼���ѿ��ؤ���Ͽ
if ($tani == 1) {
    $tmpl->addVar('page', 'en'       , 'selected');
    $tmpl->addVar('page', 'sen'      , '');
    $tmpl->addVar('page', 'jyuman'   , '');
    $tmpl->addVar('page', 'million'  , '');
} elseif ($tani == 1000) {
    $tmpl->addVar('page', 'en'       , '');
    $tmpl->addVar('page', 'sen'      , 'selected');
    $tmpl->addVar('page', 'jyuman'   , '');
    $tmpl->addVar('page', 'million'  , '');
} elseif ($tani == 100000) {
    $tmpl->addVar('page', 'en'       , '');
    $tmpl->addVar('page', 'sen'      , '');
    $tmpl->addVar('page', 'jyuman'   , 'selected');
    $tmpl->addVar('page', 'million'  , '');
} elseif ($tani == 1000000) {
    $tmpl->addVar('page', 'en'       , '');
    $tmpl->addVar('page', 'sen'      , '');
    $tmpl->addVar('page', 'jyuman'   , '');
    $tmpl->addVar('page', 'million'  , 'selected');
} else {
    $tmpl->addVar('page', 'en'       , '');
    $tmpl->addVar('page', 'sen'      , '');
    $tmpl->addVar('page', 'jyuman'   , '');
    $tmpl->addVar('page', 'million'  , 'selected');
}
///// �������ʲ��η��
if ($keta == 0) {
    $tmpl->addVar('page', 'zero' , 'selected');
    $tmpl->addVar('page', 'ichi' , '');
    $tmpl->addVar('page', 'san'  , '');
    $tmpl->addVar('page', 'roku' , '');
} elseif ($keta == 1) {
    $tmpl->addVar('page', 'zero' , '');
    $tmpl->addVar('page', 'ichi' , 'selected');
    $tmpl->addVar('page', 'san'  , '');
    $tmpl->addVar('page', 'roku' , '');
} elseif ($keta == 3) {
    $tmpl->addVar('page', 'zero' , '');
    $tmpl->addVar('page', 'ichi' , '');
    $tmpl->addVar('page', 'san'  , 'selected');
    $tmpl->addVar('page', 'roku' , '');
} elseif ($keta == 6) {
    $tmpl->addVar('page', 'zero' , '');
    $tmpl->addVar('page', 'ichi' , '');
    $tmpl->addVar('page', 'san'  , '');
    $tmpl->addVar('page', 'roku' , 'selected');
} else {
    $tmpl->addVar('page', 'zero' , '');
    $tmpl->addVar('page', 'ichi' , 'selected');
    $tmpl->addVar('page', 'san'  , '');
    $tmpl->addVar('page', 'roku' , '');
}


if ($yyyymm >= 201504 || $yyyymm <= 202103) {
    $tmpl->addVar('tbody', 'tbody_monthp_c'  , $p1_ym);
    $tmpl->addVar('tbody', 'tbody_monthp_l'  , $p1_ym);
    $tmpl->addVar('tbody', 'tbody_monthp_t'  , $p1_ym);
    $tmpl->addVar('tbody', 'tbody_monthp_a'  , $p1_ym);
    $tmpl->addVar('tbody2', 'tbody2_monp_c'  , $p1_ym);
    $tmpl->addVar('tbody2', 'tbody2_monp_l'  , $p1_ym);
    $tmpl->addVar('tbody2', 'tbody2_monp_t'  , $p1_ym);
    $tmpl->addVar('tbody', 'tbody_month_c'   , $yyyymm);
    $tmpl->addVar('tbody', 'tbody_month_l'   , $yyyymm);
    $tmpl->addVar('tbody', 'tbody_month_t'   , $yyyymm);
    $tmpl->addVar('tbody', 'tbody_month_a'   , $yyyymm);
    $tmpl->addVar('tbody2', 'tbody2_mon_c'   , $yyyymm);
    $tmpl->addVar('tbody2', 'tbody2_mon_l'   , $yyyymm);
    $tmpl->addVar('tbody2', 'tbody2_mon_t'   , $yyyymm);
} else {
    $tmpl->addVar('tbody', 'tbody_monthp_c'  , $p1_ym);
    $tmpl->addVar('tbody', 'tbody_monthp_l'  , $p1_ym);
    $tmpl->addVar('tbody', 'tbody_monthp_a'  , $p1_ym);
    $tmpl->addVar('tbody2', 'tbody2_monp_c'  , $p1_ym);
    $tmpl->addVar('tbody2', 'tbody2_monp_l'  , $p1_ym);
    $tmpl->addVar('tbody', 'tbody_month_c'   , $yyyymm);
    $tmpl->addVar('tbody', 'tbody_month_l'   , $yyyymm);
    $tmpl->addVar('tbody', 'tbody_month_a'   , $yyyymm);
    $tmpl->addVar('tbody2', 'tbody2_mon_c'   , $yyyymm);
    $tmpl->addVar('tbody2', 'tbody2_mon_l'   , $yyyymm);

}

///// �ϥå�������� patTemplate ��Ÿ�� ���ץ顦��˥������Τ� tbody[]����������Ƥ���
$tmpl->addVars('tbody', $tbody);
$tmpl->addVars('tbody2', $tbody2);
$tmpl->addVars('tbody3', $tbody3);

//$tmpl->addVars( 'tbody_rows', array('TBODY_DSP_NUM' => $dsp_num) );
//$tmpl->addVars( 'tbody_rows', array('TBODY_FIELD0'  => $field0) );
//$tmpl->addVars( 'tbody_rows', array('TBODY_FIELD1'  => $field1) );


/********** Logic End   **********/

/////////// HTML Header ����Ϥ��ƥ���å��������
$menu->out_html_header();

//  Alle Templates ausgeben
$tmpl->displayParsedTemplate();
/************* patTemplate ��λ *****************/

?>
