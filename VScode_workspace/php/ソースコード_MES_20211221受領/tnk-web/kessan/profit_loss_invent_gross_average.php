<?php
//////////////////////////////////////////////////////////////////////////////
// �軻��ʿ��ñ���ˤ�����ê������Ͽ�������ڤӾȲ����                     //
// Copyright (C) 2003-2005 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2003/03/13 Created   profit_loss_invent_gross_average.php                //
// 2003/03/14 �Թ���(��������)�ι�פ��ɲ�                                  //
// 2005/10/27 MenuHeader class ����Ѥ��ƶ��̥�˥塼���ڤ�ǧ���������ѹ�   //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug ��
// ini_set('error_reporting',E_ALL);           // E_ALL='2047' debug ��
// ini_set('display_errors','1');              // Error ɽ�� ON debug �� ��꡼���女����
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
// $menu->set_caption('�������칩��(��)');
//////////// �ƽ����action̾�ȥ��ɥ쥹����
// $menu->set_action('��ݲ�̾',   PL . 'address.php');

$current_script  = $_SERVER['PHP_SELF'];        // ���߼¹���Υ�����ץ�̾����¸
$url_referer     = $_SESSION['pl_referer'];     // ʬ������������¸����Ƥ���ƽи��򥻥åȤ���

//////////// �����ȥ�����ա���������
$today = date("Y/m/d H:i:s");

///// ������μ���
$ki = Ym_to_tnk($_SESSION['pl_ym']);
$tuki = substr($_SESSION['pl_ym'],4,2);

//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title("��{$ki}����{$tuki}���١��軻 ��ʿ�� ����ê�������Ͽ");

///// �о�����
$yyyymm = $_SESSION['pl_ym'];
///// �о�����
if (substr($yyyymm,4,2)!=01) {
    $p1_ym = $yyyymm - 1;
} else {
    $p1_ym = $yyyymm - 100;
    $p1_ym = $p1_ym + 11;
}
///////// ���ܤȥ���ǥå����δ�Ϣ�դ�
$item = array();
$item[0]  = "���ץ鸶����������";
$item[1]  = "���ץ鸶������Ω��";
$item[2]  = "���ץ鸶����������";
$item[3]  = "���ץ鸶����������";
$item[4]  = "���ץ������ʺ�����";
$item[5]  = "���ץ���������Ω��";
$item[6]  = "���ץ������ʹ�����";
$item[7]  = "���ץ������ʴ�����";
$item[8]  = "���ץ鹩��ųݺ�����";
$item[9]  = "���ץ鹩��ų���Ω��";
$item[10] = "���ץ鹩��ųݹ�����";
$item[11] = "���ץ鹩��ųݴ�����";
$item[12] = "���ץ鳰��ųݺ�����";
$item[13] = "���ץ鳰��ų���Ω��";
$item[14] = "���ץ鳰��ųݹ�����";
$item[15] = "���ץ鳰��ųݴ�����";
$item[16] = "���ץ鸡���ųݺ�����";
$item[17] = "���ץ鸡���ų���Ω��";
$item[18] = "���ץ鸡���ųݹ�����";
$item[19] = "���ץ鸡���ųݴ�����";
$item[20] = "���ץ�ã����ʺ�����";
$item[21] = "���ץ�ã�������Ω��";
$item[22] = "���ץ�ã����ʹ�����";
$item[23] = "���ץ�ã����ʴ�����";
$item[24] = "���ץ���Ω�ųݺ�����";
$item[25] = "���ץ���Ω�ų���Ω��";
$item[26] = "���ץ���Ω�ųݹ�����";
$item[27] = "���ץ���Ω�ųݴ�����";
$item[28]  = "��˥�������������";
$item[29]  = "��˥���������Ω��";
$item[30]  = "��˥�������������";
$item[31]  = "��˥�������������";
$item[32]  = "��˥�������ʺ�����";
$item[33]  = "��˥����������Ω��";
$item[34]  = "��˥�������ʹ�����";
$item[35]  = "��˥�������ʴ�����";
$item[36]  = "��˥�����ųݺ�����";
$item[37]  = "��˥�����ų���Ω��";
$item[38] = "��˥�����ųݹ�����";
$item[39] = "��˥�����ųݴ�����";
$item[40] = "��˥�����ųݺ�����";
$item[41] = "��˥�����ų���Ω��";
$item[42] = "��˥�����ųݹ�����";
$item[43] = "��˥�����ųݴ�����";
$item[44] = "��˥������ųݺ�����";
$item[45] = "��˥������ų���Ω��";
$item[46] = "��˥������ųݹ�����";
$item[47] = "��˥������ųݴ�����";
$item[48] = "��˥��ã����ʺ�����";
$item[49] = "��˥��ã�������Ω��";
$item[50] = "��˥��ã����ʹ�����";
$item[51] = "��˥��ã����ʴ�����";
$item[52] = "��˥���Ω�ųݺ�����";
$item[53] = "��˥���Ω�ų���Ω��";
$item[54] = "��˥���Ω�ųݹ�����";
$item[55] = "��˥���Ω�ųݴ�����";
///////// ����text �ѿ� �����
$invent = array();
for ($i = 0; $i < 56; $i++) {
    if (isset($_POST['invent'][$i])) {
        $invent[$i] = $_POST['invent'][$i];
    } else {
        $invent[$i] = "";
    }
}
if (!isset($_POST['entry'])) {     // �ǡ�������
    ////////// ��Ͽ�Ѥߤʤ��ê����ۼ���
    $query = sprintf("select kin from act_invent_gross_average_history where pl_bs_ym=%d order by id ASC", $yyyymm);
    $res = array();
    if (getResult2($query,$res) > 0) {
        for ($i = 0; $i < 56; $i++) {
            $invent[$i] = $res[$i][0];
        }
    }
    ////////// ���ʷפμ���
    $query = sprintf("select sum(kin) from act_invent_gross_average_history where pl_bs_ym=%d and 
        note like '���ץ�%%������' and note<>'���ץ���Ω�ųݺ�����'", $yyyymm);
    getUniResult($query, $d_zai_c);
    $zai_c = number_format($d_zai_c);
    $query = sprintf("select sum(kin) from act_invent_gross_average_history where pl_bs_ym=%d and 
        note like '���ץ�%%��Ω��' and note<>'���ץ���Ω�ų���Ω��'", $yyyymm);
    getUniResult($query, $d_kumi_c);
    $kumi_c = number_format($d_kumi_c);
    $query = sprintf("select sum(kin) from act_invent_gross_average_history where pl_bs_ym=%d and 
        note like '���ץ�%%������' and note<>'���ץ���Ω�ųݹ�����'", $yyyymm);
    getUniResult($query, $d_kou_c);
    $kou_c = number_format($d_kou_c);
    $query = sprintf("select sum(kin) from act_invent_gross_average_history where pl_bs_ym=%d and 
        note like '���ץ�%%������' and note<>'���ץ���Ω�ųݴ�����'", $yyyymm);
    getUniResult($query, $d_kan_c);
    $kan_c = number_format($d_kan_c);
    $query = sprintf("select sum(kin) from act_invent_gross_average_history where pl_bs_ym=%d and 
        note like '��˥�%%������' and note<>'��˥���Ω�ųݺ�����'", $yyyymm);
    getUniResult($query, $d_zai_l);
    $zai_l = number_format($d_zai_l);
    $query = sprintf("select sum(kin) from act_invent_gross_average_history where pl_bs_ym=%d and 
        note like '��˥�%%��Ω��' and note<>'��˥���Ω�ų���Ω��'", $yyyymm);
    getUniResult($query, $d_kumi_l);
    $kumi_l = number_format($d_kumi_l);
    $query = sprintf("select sum(kin) from act_invent_gross_average_history where pl_bs_ym=%d and 
        note like '��˥�%%������' and note<>'��˥���Ω�ųݹ�����'", $yyyymm);
    getUniResult($query, $d_kou_l);
    $kou_l = number_format($d_kou_l);
    $query = sprintf("select sum(kin) from act_invent_gross_average_history where pl_bs_ym=%d and 
        note like '��˥�%%������' and note<>'��˥���Ω�ųݴ�����'", $yyyymm);
    getUniResult($query, $d_kan_l);
    $kan_l = number_format($d_kan_l);
    ////////// �߸˷פμ���
    $query = sprintf("select sum(kin) from act_invent_gross_average_history where pl_bs_ym=%d and 
        note like '���ץ�%%������'", $yyyymm);
    getUniResult($query, $d_zai_all_c);
    $zai_all_c = number_format($d_zai_all_c);
    $query = sprintf("select sum(kin) from act_invent_gross_average_history where pl_bs_ym=%d and 
        note like '���ץ�%%��Ω��'", $yyyymm);
    getUniResult($query, $d_kumi_all_c);
    $kumi_all_c = number_format($d_kumi_all_c);
    $query = sprintf("select sum(kin) from act_invent_gross_average_history where pl_bs_ym=%d and 
        note like '���ץ�%%������'", $yyyymm);
    getUniResult($query, $d_kou_all_c);
    $kou_all_c = number_format($d_kou_all_c);
    $query = sprintf("select sum(kin) from act_invent_gross_average_history where pl_bs_ym=%d and 
        note like '���ץ�%%������'", $yyyymm);
    getUniResult($query, $d_kan_all_c);
    $kan_all_c = number_format($d_kan_all_c);
    /********** �������� ��˥� *********/
    $query = sprintf("select sum(kin) from act_invent_gross_average_history where pl_bs_ym=%d and 
        note like '��˥�%%������'", $yyyymm);
    getUniResult($query, $d_zai_all_l);
    $zai_all_l = number_format($d_zai_all_l);
    $query = sprintf("select sum(kin) from act_invent_gross_average_history where pl_bs_ym=%d and 
        note like '��˥�%%��Ω��'", $yyyymm);
    getUniResult($query, $d_kumi_all_l);
    $kumi_all_l = number_format($d_kumi_all_l);
    $query = sprintf("select sum(kin) from act_invent_gross_average_history where pl_bs_ym=%d and 
        note like '��˥�%%������'", $yyyymm);
    getUniResult($query, $d_kou_all_l);
    $kou_all_l = number_format($d_kou_all_l);
    $query = sprintf("select sum(kin) from act_invent_gross_average_history where pl_bs_ym=%d and 
        note like '��˥�%%������'", $yyyymm);
    getUniResult($query, $d_kan_all_l);
    $kan_all_l = number_format($d_kan_all_l);
    ////////// ���ξ��� �ų�������
        // ���ץ鸶����
    $query = sprintf("select sum(kin) from act_invent_gross_average_history where pl_bs_ym=%d and 
        note like '���ץ鸶����%%'", $yyyymm);
    getUniResult($query, $gen_c);
    $gen_c = number_format($gen_c);
        // ���ץ�������
    $query = sprintf("select sum(kin) from act_invent_gross_average_history where pl_bs_ym=%d and 
        note like '���ץ�������%%'", $yyyymm);
    getUniResult($query, $shi_c);
    $shi_c = number_format($shi_c);
        // ���ץ鹩��ų�
    $query = sprintf("select sum(kin) from act_invent_gross_average_history where pl_bs_ym=%d and 
        note like '���ץ鹩��ų�%%'", $yyyymm);
    getUniResult($query, $kshi_c);
    $kshi_c = number_format($kshi_c);
        // ���ץ鳰��ų�
    $query = sprintf("select sum(kin) from act_invent_gross_average_history where pl_bs_ym=%d and 
        note like '���ץ鳰��ų�%%'", $yyyymm);
    getUniResult($query, $gai_c);
    $gai_c = number_format($gai_c);
        // ���ץ鸡���ų�
    $query = sprintf("select sum(kin) from act_invent_gross_average_history where pl_bs_ym=%d and 
        note like '���ץ鸡���ų�%%'", $yyyymm);
    getUniResult($query, $ken_c);
    $ken_c = number_format($ken_c);
        // ���ץ�ã�����
    $query = sprintf("select sum(kin) from act_invent_gross_average_history where pl_bs_ym=%d and 
        note like '���ץ�ã�����%%'", $yyyymm);
    getUniResult($query, $cc_c);
    $cc_c = number_format($cc_c);
        // ���ץ�������
    $query = sprintf("select sum(kin) from act_invent_gross_average_history where pl_bs_ym=%d and 
        note like '���ץ�������%%'", $yyyymm);
    getUniResult($query, $shi_c);
    $shi_c = number_format($shi_c);
        // ���ץ���Ω�ų�
    $query = sprintf("select sum(kin) from act_invent_gross_average_history where pl_bs_ym=%d and 
        note like '���ץ���Ω�ų�%%'", $yyyymm);
    getUniResult($query, $kushi_c);
    $kushi_c = number_format($kushi_c);
        // ���ץ����ʷ�
    $buhin_c = number_format($d_zai_c + $d_kumi_c + $d_kou_c + $d_kan_c);
        // ���ץ�߸˷�
    $zaiko_c = number_format($d_zai_all_c + $d_kumi_all_c + $d_kou_all_c + $d_kan_all_c);
    /*********** ���������˥� **********/
        // ��˥�������
    $query = sprintf("select sum(kin) from act_invent_gross_average_history where pl_bs_ym=%d and 
        note like '��˥�������%%'", $yyyymm);
    getUniResult($query, $gen_l);
    $gen_l = number_format($gen_l);
        // ��˥��������
    $query = sprintf("select sum(kin) from act_invent_gross_average_history where pl_bs_ym=%d and 
        note like '��˥��������%%'", $yyyymm);
    getUniResult($query, $shi_l);
    $shi_l = number_format($shi_c);
        // ��˥�����ų�
    $query = sprintf("select sum(kin) from act_invent_gross_average_history where pl_bs_ym=%d and 
        note like '��˥�����ų�%%'", $yyyymm);
    getUniResult($query, $kshi_l);
    $kshi_l = number_format($kshi_l);
        // ��˥�����ų�
    $query = sprintf("select sum(kin) from act_invent_gross_average_history where pl_bs_ym=%d and 
        note like '��˥�����ų�%%'", $yyyymm);
    getUniResult($query, $gai_l);
    $gai_l = number_format($gai_l);
        // ��˥������ų�
    $query = sprintf("select sum(kin) from act_invent_gross_average_history where pl_bs_ym=%d and 
        note like '��˥������ų�%%'", $yyyymm);
    getUniResult($query, $ken_l);
    $ken_l = number_format($ken_l);
        // ��˥��ã�����
    $query = sprintf("select sum(kin) from act_invent_gross_average_history where pl_bs_ym=%d and 
        note like '��˥��ã�����%%'", $yyyymm);
    getUniResult($query, $cc_l);
    $cc_l = number_format($cc_l);
        // ��˥��������
    $query = sprintf("select sum(kin) from act_invent_gross_average_history where pl_bs_ym=%d and 
        note like '��˥��������%%'", $yyyymm);
    getUniResult($query, $shi_l);
    $shi_l = number_format($shi_l);
        // ��˥���Ω�ų�
    $query = sprintf("select sum(kin) from act_invent_gross_average_history where pl_bs_ym=%d and 
        note like '��˥���Ω�ų�%%'", $yyyymm);
    getUniResult($query, $kushi_l);
    $kushi_l = number_format($kushi_l);
        // ��˥����ʷ�
    $buhin_l = number_format($d_zai_l + $d_kumi_l + $d_kou_l + $d_kan_l);
        // ��˥��߸˷�
    $zaiko_l = number_format($d_zai_all_l + $d_kumi_all_l + $d_kou_all_l + $d_kan_all_l);
    ////////// ���κ߸˶�۹��
    $zaiko = number_format($d_zai_all_c + $d_kumi_all_c + $d_kou_all_c + $d_kan_all_c + $d_zai_all_l + $d_kumi_all_l + $d_kou_all_l + $d_kan_all_l);
} else {                            // ��Ͽ����  �ȥ�󥶥������ǹ������Ƥ��뤿��쥳����ͭ��̵���Υ����å��Τ�
    $query = sprintf("select kin from act_invent_gross_average_history where pl_bs_ym=%d order by id ASC", $yyyymm);
    $res = array();
    if (getResult2($query,$res) <= 0) {
        /////////// begin �ȥ�󥶥�����󳫻�
        if ($con = db_connect()) {
            query_affected_trans($con, "begin");
        } else {
            $_SESSION["s_sysmsg"] .= "�ǡ����١�������³�Ǥ��ޤ���";
            header("Location: $current_script");
            exit();
        }
        ////////// Insert Start
        for ($i = 0; $i < 56; $i++) {
            $query = sprintf("insert into act_invent_gross_average_history (pl_bs_ym, kin, note, id) values (%d, %d, '%s', %d)", $yyyymm, $invent[$i], $item[$i], $i);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION["s_sysmsg"] .= sprintf("%s�ο�����Ͽ�˼���<br>�� %d�� %d��", $item[$i], $ki, $tuki);
                query_affected_trans($con, "rollback");     // transaction rollback
                header("Location: $current_script");
                exit();
            }
        }
        /////////// commit �ȥ�󥶥������λ
        query_affected_trans($con, "commit");
        $_SESSION["s_sysmsg"] .= sprintf("<font color='yellow'>��%d�� %d�� ��ʿ�� ����ê���� ���� ��Ͽ��λ</font>",$ki,$tuki);
    } else {
        /////////// begin �ȥ�󥶥�����󳫻�
        if ($con = db_connect()) {
            query_affected_trans($con, "begin");
        } else {
            $_SESSION["s_sysmsg"] .= "�ǡ����١�������³�Ǥ��ޤ���";
            header("Location: $current_script");
            exit();
        }
        ////////// UPDATE Start
        for ($i = 0; $i < 56; $i++) {
            $query = sprintf("update act_invent_gross_average_history set kin=%d where pl_bs_ym=%d and note='%s'", $invent[$i], $yyyymm, $item[$i]);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION["s_sysmsg"] .= sprintf("%s��UPDATE�˼���<br>�� %d�� %d��", $item[$i], $ki, $tuki);
                query_affected_trans($con, "rollback");     // transaction rollback
                header("Location: $current_script");
                exit();
            }
        }
        /////////// commit �ȥ�󥶥������λ
        query_affected_trans($con, "commit");
        $_SESSION["s_sysmsg"] .= sprintf("<font color='yellow'>��%d�� %d�� ��ʿ�� ����ê���� �ѹ� ��λ</font>",$ki,$tuki);
    }
    ////////// ���ʷפμ���
    $query = sprintf("select sum(kin) from act_invent_gross_average_history where pl_bs_ym=%d and 
        note like '���ץ�%%������' and note<>'���ץ���Ω�ųݺ�����'", $yyyymm);
    getUniResult($query, $d_zai_c);
    $zai_c = number_format($d_zai_c);
    $query = sprintf("select sum(kin) from act_invent_gross_average_history where pl_bs_ym=%d and 
        note like '���ץ�%%��Ω��' and note<>'���ץ���Ω�ų���Ω��'", $yyyymm);
    getUniResult($query, $d_kumi_c);
    $kumi_c = number_format($d_kumi_c);
    $query = sprintf("select sum(kin) from act_invent_gross_average_history where pl_bs_ym=%d and 
        note like '���ץ�%%������' and note<>'���ץ���Ω�ųݹ�����'", $yyyymm);
    getUniResult($query, $d_kou_c);
    $kou_c = number_format($d_kou_c);
    $query = sprintf("select sum(kin) from act_invent_gross_average_history where pl_bs_ym=%d and 
        note like '���ץ�%%������' and note<>'���ץ���Ω�ųݴ�����'", $yyyymm);
    getUniResult($query, $d_kan_c);
    $kan_c = number_format($d_kan_c);
    /********** ���������˥� **********/
    $query = sprintf("select sum(kin) from act_invent_gross_average_history where pl_bs_ym=%d and 
        note like '��˥�%%������' and note<>'��˥���Ω�ųݺ�����'", $yyyymm);
    getUniResult($query, $d_zai_l);
    $zai_l = number_format($d_zai_l);
    $query = sprintf("select sum(kin) from act_invent_gross_average_history where pl_bs_ym=%d and 
        note like '��˥�%%��Ω��' and note<>'��˥���Ω�ų���Ω��'", $yyyymm);
    getUniResult($query, $d_kumi_l);
    $kumi_l = number_format($d_kumi_l);
    $query = sprintf("select sum(kin) from act_invent_gross_average_history where pl_bs_ym=%d and 
        note like '��˥�%%������' and note<>'��˥���Ω�ųݹ�����'", $yyyymm);
    getUniResult($query, $d_kou_l);
    $kou_l = number_format($d_kou_l);
    $query = sprintf("select sum(kin) from act_invent_gross_average_history where pl_bs_ym=%d and 
        note like '��˥�%%������' and note<>'��˥���Ω�ųݴ�����'", $yyyymm);
    getUniResult($query, $d_kan_l);
    $kan_l = number_format($d_kan_l);
    ////////// �߸˷פμ���
    $query = sprintf("select sum(kin) from act_invent_gross_average_history where pl_bs_ym=%d and 
        note like '���ץ�%%������'", $yyyymm);
    getUniResult($query, $d_zai_all_c);
    $zai_all_c = number_format($d_zai_all_c);
    $query = sprintf("select sum(kin) from act_invent_gross_average_history where pl_bs_ym=%d and 
        note like '���ץ�%%��Ω��'", $yyyymm);
    getUniResult($query, $d_kumi_all_c);
    $kumi_all_c = number_format($d_kumi_all_c);
    $query = sprintf("select sum(kin) from act_invent_gross_average_history where pl_bs_ym=%d and 
        note like '���ץ�%%������'", $yyyymm);
    getUniResult($query, $d_kou_all_c);
    $kou_all_c = number_format($d_kou_all_c);
    $query = sprintf("select sum(kin) from act_invent_gross_average_history where pl_bs_ym=%d and 
        note like '���ץ�%%������'", $yyyymm);
    getUniResult($query, $d_kan_all_c);
    $kan_all_c = number_format($d_kan_all_c);
    /********** ���������˥� *********/
    $query = sprintf("select sum(kin) from act_invent_gross_average_history where pl_bs_ym=%d and 
        note like '��˥�%%������'", $yyyymm);
    getUniResult($query, $d_zai_all_l);
    $zai_all_l = number_format($d_zai_all_l);
    $query = sprintf("select sum(kin) from act_invent_gross_average_history where pl_bs_ym=%d and 
        note like '��˥�%%��Ω��'", $yyyymm);
    getUniResult($query, $d_kumi_all_l);
    $kumi_all_l = number_format($d_kumi_all_l);
    $query = sprintf("select sum(kin) from act_invent_gross_average_history where pl_bs_ym=%d and 
        note like '��˥�%%������'", $yyyymm);
    getUniResult($query, $d_kou_all_l);
    $kou_all_l = number_format($d_kou_all_l);
    $query = sprintf("select sum(kin) from act_invent_gross_average_history where pl_bs_ym=%d and 
        note like '��˥�%%������'", $yyyymm);
    getUniResult($query, $d_kan_all_l);
    $kan_all_l = number_format($d_kan_all_l);
    ////////// ���ξ��� �ų�������
        // ���ץ鸶����
    $query = sprintf("select sum(kin) from act_invent_gross_average_history where pl_bs_ym=%d and 
        note like '���ץ鸶����%%'", $yyyymm);
    getUniResult($query, $gen_c);
    $gen_c = number_format($gen_c);
        // ���ץ�������
    $query = sprintf("select sum(kin) from act_invent_gross_average_history where pl_bs_ym=%d and 
        note like '���ץ�������%%'", $yyyymm);
    getUniResult($query, $shi_c);
    $shi_c = number_format($shi_c);
        // ���ץ鹩��ų�
    $query = sprintf("select sum(kin) from act_invent_gross_average_history where pl_bs_ym=%d and 
        note like '���ץ鹩��ų�%%'", $yyyymm);
    getUniResult($query, $kshi_c);
    $kshi_c = number_format($kshi_c);
        // ���ץ鳰��ų�
    $query = sprintf("select sum(kin) from act_invent_gross_average_history where pl_bs_ym=%d and 
        note like '���ץ鳰��ų�%%'", $yyyymm);
    getUniResult($query, $gai_c);
    $gai_c = number_format($gai_c);
        // ���ץ鸡���ų�
    $query = sprintf("select sum(kin) from act_invent_gross_average_history where pl_bs_ym=%d and 
        note like '���ץ鸡���ų�%%'", $yyyymm);
    getUniResult($query, $ken_c);
    $ken_c = number_format($ken_c);
        // ���ץ�ã�����
    $query = sprintf("select sum(kin) from act_invent_gross_average_history where pl_bs_ym=%d and 
        note like '���ץ�ã�����%%'", $yyyymm);
    getUniResult($query, $cc_c);
    $cc_c = number_format($cc_c);
        // ���ץ�������
    $query = sprintf("select sum(kin) from act_invent_gross_average_history where pl_bs_ym=%d and 
        note like '���ץ�������%%'", $yyyymm);
    getUniResult($query, $shi_c);
    $shi_c = number_format($shi_c);
        // ���ץ���Ω�ų�
    $query = sprintf("select sum(kin) from act_invent_gross_average_history where pl_bs_ym=%d and 
        note like '���ץ���Ω�ų�%%'", $yyyymm);
    getUniResult($query, $kushi_c);
    $kushi_c = number_format($kushi_c);
        // ���ץ����ʷ�
    $buhin_c = number_format($d_zai_c + $d_kumi_c + $d_kou_c + $d_kan_c);
        // ���ץ�߸˷�
    $zaiko_c = number_format($d_zai_all_c + $d_kumi_all_c + $d_kou_all_c + $d_kan_all_c);
    /*********** ���������˥� **********/
        // ��˥�������
    $query = sprintf("select sum(kin) from act_invent_gross_average_history where pl_bs_ym=%d and 
        note like '��˥�������%%'", $yyyymm);
    getUniResult($query, $gen_l);
    $gen_l = number_format($gen_l);
        // ��˥��������
    $query = sprintf("select sum(kin) from act_invent_gross_average_history where pl_bs_ym=%d and 
        note like '��˥��������%%'", $yyyymm);
    getUniResult($query, $shi_l);
    $shi_l = number_format($shi_c);
        // ��˥�����ų�
    $query = sprintf("select sum(kin) from act_invent_gross_average_history where pl_bs_ym=%d and 
        note like '��˥�����ų�%%'", $yyyymm);
    getUniResult($query, $kshi_l);
    $kshi_l = number_format($kshi_l);
        // ��˥�����ų�
    $query = sprintf("select sum(kin) from act_invent_gross_average_history where pl_bs_ym=%d and 
        note like '��˥�����ų�%%'", $yyyymm);
    getUniResult($query, $gai_l);
    $gai_l = number_format($gai_l);
        // ��˥������ų�
    $query = sprintf("select sum(kin) from act_invent_gross_average_history where pl_bs_ym=%d and 
        note like '��˥������ų�%%'", $yyyymm);
    getUniResult($query, $ken_l);
    $ken_l = number_format($ken_l);
        // ��˥��ã�����
    $query = sprintf("select sum(kin) from act_invent_gross_average_history where pl_bs_ym=%d and 
        note like '��˥��ã�����%%'", $yyyymm);
    getUniResult($query, $cc_l);
    $cc_l = number_format($cc_l);
        // ��˥��������
    $query = sprintf("select sum(kin) from act_invent_gross_average_history where pl_bs_ym=%d and 
        note like '��˥��������%%'", $yyyymm);
    getUniResult($query, $shi_l);
    $shi_l = number_format($shi_l);
        // ��˥���Ω�ų�
    $query = sprintf("select sum(kin) from act_invent_gross_average_history where pl_bs_ym=%d and 
        note like '��˥���Ω�ų�%%'", $yyyymm);
    getUniResult($query, $kushi_l);
    $kushi_l = number_format($kushi_l);
        // ��˥����ʷ�
    $buhin_l = number_format($d_zai_l + $d_kumi_l + $d_kou_l + $d_kan_l);
        // ��˥��߸˷�
    $zaiko_l = number_format($d_zai_all_l + $d_kumi_all_l + $d_kou_all_l + $d_kan_all_l);
    ////////// ���κ߸˶�۹��
    $zaiko = number_format($d_zai_all_c + $d_kumi_all_c + $d_kou_all_c + $d_kan_all_c + $d_zai_all_l + $d_kumi_all_l + $d_kou_all_l + $d_kan_all_l);
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
/* ������ϥ�����Ȥإե������������� */
function set_focus(){
    document.invent.invent_1.focus();
    document.invent.invent_1.select();
}
// -->
</script>
<style type="text/css">
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
.pt11 {
    font-size:11pt;
    font-family: monospace;
}
.pt11b {
    font:bold 11pt;
    font-family: monospace;
}
.pt12b {
    font:bold 12pt;
    font-family: monospace;
}
th {
    font:bold 11pt;
    font-family: monospace;
}
.title_font {
    font:bold 14pt;
    font-family: monospace;
}
.today_font {
    font-size: 10.5pt;
    font-family: monospace;
}
.right{
    text-align:right;
    font:bold 12pt;
    font-family: monospace;
}
.rightb{
    text-align:right;
    font:bold 12pt;
    font-family: monospace;
    background-color: '#e6e6e6';
}
.margin0 {
    margin:0%;
}
-->
</style>
</head>
<body>
    <center>
<?= $menu->out_title_border() ?>
        <form name='invent' action='<?php echo $menu->out_self() ?>' method='post'>
        <table bgcolor='#d6d3ce' align='center' cellspacing='0' cellpadding='3' border='1'>
            <tr><td> <!-- ���ߡ�(�ǥ�������) -->
            <table bgcolor='#d6d3ce' cellspacing='0' cellpadding='2' border='1'>
                <th colspan='2'>��</th><th bgcolor='#ffff94'>������</th><th bgcolor='#ffff94'>��Ω��</th>
                <th bgcolor='#ffff94'>������</th><th bgcolor='#ffff94'>������</th><th bgcolor='#ffff94'>�硡��</th>
                <tr>
                    <td align='center' width='10' rowspan='9' class='pt12b'>���ץ�</td>
                    <td align='center' bgcolor='#e6e6e6' class='pt11b' width='110'>������</td>
                    <td align='center' bgcolor='#e6e6e6' width='110'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[0] ?>' class='rightb' onChange='return isDigit(value);'>
                    </td>
                    <td align='center' bgcolor='#e6e6e6' width='110'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[1] ?>' class='rightb' onChange='return isDigit(value);'>
                    </td>
                    <td align='center' bgcolor='#e6e6e6' width='110'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[2] ?>' class='rightb' onChange='return isDigit(value);'>
                    </td>
                    <td align='center' bgcolor='#e6e6e6' width='110'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[3] ?>' class='rightb' onChange='return isDigit(value);'>
                    </td>
                    <td align='right' bgcolor='#ceffce' class='pt12b' width='110'><?php echo $gen_c ?></td>
                </tr>
                <tr>
                    <td align='center' bgcolor='white' class='pt11b'>�������</td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[4] ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[5] ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[6] ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[7] ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                    <td align='right' bgcolor='#ceffce' class='pt12b' width='110'><?php echo $shi_c ?></td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#e6e6e6' class='pt11b'>����ų�</td>
                    <td align='center' bgcolor='#e6e6e6'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[8] ?>' class='rightb' onChange='return isDigit(value);'>
                    </td>
                    <td align='center' bgcolor='#e6e6e6'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[9] ?>' class='rightb' onChange='return isDigit(value);'>
                    </td>
                    <td align='center' bgcolor='#e6e6e6'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[10] ?>' class='rightb' onChange='return isDigit(value);'>
                    </td>
                    <td align='center' bgcolor='#e6e6e6'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[11] ?>' class='rightb' onChange='return isDigit(value);'>
                    </td>
                    <td align='right' bgcolor='#ceffce' class='pt12b' width='110'><?php echo $kshi_c ?></td>
                </tr>
                <tr>
                    <td align='center' bgcolor='white' class='pt11b'>����ų�</td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[12] ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[13] ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[14] ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[15] ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                    <td align='right' bgcolor='#ceffce' class='pt12b' width='110'><?php echo $gai_c ?></td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#e6e6e6' class='pt11b'>�����ų�</td>
                    <td align='center' bgcolor='#e6e6e6'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[16] ?>' class='rightb' onChange='return isDigit(value);'>
                    </td>
                    <td align='center' bgcolor='#e6e6e6'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[17] ?>' class='rightb' onChange='return isDigit(value);'>
                    </td>
                    <td align='center' bgcolor='#e6e6e6'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[18] ?>' class='rightb' onChange='return isDigit(value);'>
                    </td>
                    <td align='center' bgcolor='#e6e6e6'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[19] ?>' class='rightb' onChange='return isDigit(value);'>
                    </td>
                    <td align='right' bgcolor='#ceffce' class='pt12b' width='110'><?php echo $ken_c ?></td>
                </tr>
                <tr>
                    <td align='center' bgcolor='white' class='pt11b'>�ã�����</td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[20] ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[21] ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[22] ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[23] ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                    <td align='right' bgcolor='#ceffce' class='pt12b' width='110'><?php echo $cc_c ?></td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ceffce' class='pt11b'>���ʷ�</td>
                    <td align='right' bgcolor='#ceffce' class= 'pt12b'><?php echo $zai_c ?></td>
                    <td align='right' bgcolor='#ceffce' class= 'pt12b'><?php echo $kumi_c ?></td>
                    <td align='right' bgcolor='#ceffce' class= 'pt12b'><?php echo $kou_c ?></td>
                    <td align='right' bgcolor='#ceffce' class= 'pt12b'><?php echo $kan_c ?></td>
                    <td align='right' bgcolor='#ceffce' class='pt12b' width='110'><?php echo $buhin_c ?></td>
                </tr>
                <tr>
                    <td align='center' bgcolor='white' class='pt11b'>��Ω�ų�</td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[24] ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[25] ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[26] ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[27] ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                    <td align='right' bgcolor='#ceffce' class='pt12b' width='110'><?php echo $kushi_c ?></td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ceffce' class='pt11b'>�߸˷�</td>
                    <td align='right' bgcolor='#ceffce' class='pt12b'><?php echo $zai_all_c ?></td>
                    <td align='right' bgcolor='#ceffce' class='pt12b'><?php echo $kumi_all_c ?></td>
                    <td align='right' bgcolor='#ceffce' class='pt12b'><?php echo $kou_all_c ?></td>
                    <td align='right' bgcolor='#ceffce' class='pt12b'><?php echo $kan_all_c ?></td>
                    <td align='right' bgcolor='#ceffce' class='pt12b' width='110'><?php echo $zaiko_c ?></td>
                </tr>
                <tr>
                    <td align='center' width='10' rowspan='9' class='pt12b'>��˥�</td>
                    <td align='center' bgcolor='#e6e6e6' class='pt11b'>������</td>
                    <td align='center' bgcolor='#e6e6e6'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[28] ?>' class='rightb' onChange='return isDigit(value);'>
                    </td>
                    <td align='center' bgcolor='#e6e6e6'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[29] ?>' class='rightb' onChange='return isDigit(value);'>
                    </td>
                    <td align='center' bgcolor='#e6e6e6'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[30] ?>' class='rightb' onChange='return isDigit(value);'>
                    </td>
                    <td align='center' bgcolor='#e6e6e6'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[31] ?>' class='rightb' onChange='return isDigit(value);'>
                    </td>
                    <td align='right' bgcolor='#ceffce' class='pt12b' width='110'><?php echo $gen_l ?></td>
                </tr>
                <tr>
                    <td align='center' bgcolor='white' class='pt11b'>�������</td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[32] ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[33] ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[34] ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[35] ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                    <td align='right' bgcolor='#ceffce' class='pt12b' width='110'><?php echo $shi_l ?></td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#e6e6e6' class='pt11b'>����ų�</td>
                    <td align='center' bgcolor='#e6e6e6'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[36] ?>' class='rightb' onChange='return isDigit(value);'>
                    </td>
                    <td align='center' bgcolor='#e6e6e6'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[37] ?>' class='rightb' onChange='return isDigit(value);'>
                    </td>
                    <td align='center' bgcolor='#e6e6e6'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[38] ?>' class='rightb' onChange='return isDigit(value);'>
                    </td>
                    <td align='center' bgcolor='#e6e6e6'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[39] ?>' class='rightb' onChange='return isDigit(value);'>
                    </td>
                    <td align='right' bgcolor='#ceffce' class='pt12b' width='110'><?php echo $kshi_l ?></td>
                </tr>
                <tr>
                    <td align='center' bgcolor='white' class='pt11b'>����ų�</td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[40] ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[41] ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[42] ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[43] ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                    <td align='right' bgcolor='#ceffce' class='pt12b' width='110'><?php echo $gai_l ?></td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#e6e6e6' class='pt11b'>�����ų�</td>
                    <td align='center' bgcolor='#e6e6e6'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[44] ?>' class='rightb' onChange='return isDigit(value);'>
                    </td>
                    <td align='center' bgcolor='#e6e6e6'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[45] ?>' class='rightb' onChange='return isDigit(value);'>
                    </td>
                    <td align='center' bgcolor='#e6e6e6'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[46] ?>' class='rightb' onChange='return isDigit(value);'>
                    </td>
                    <td align='center' bgcolor='#e6e6e6'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[47] ?>' class='rightb' onChange='return isDigit(value);'>
                    </td>
                    <td align='right' bgcolor='#ceffce' class='pt12b' width='110'><?php echo $ken_l ?></td>
                </tr>
                <tr>
                    <td align='center' bgcolor='white' class='pt11b'>�ã�����</td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[48] ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[49] ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[50] ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[51] ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                    <td align='right' bgcolor='#ceffce' class='pt12b' width='110'><?php echo $cc_l ?></td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ceffce' class='pt11b'>���ʷ�</td>
                    <td align='right' bgcolor='#ceffce' class='pt12b'><?php echo $zai_l ?></td>
                    <td align='right' bgcolor='#ceffce' class='pt12b'><?php echo $kumi_l ?></td>
                    <td align='right' bgcolor='#ceffce' class='pt12b'><?php echo $kou_l ?></td>
                    <td align='right' bgcolor='#ceffce' class='pt12b'><?php echo $kan_l ?></td>
                    <td align='right' bgcolor='#ceffce' class='pt12b' width='110'><?php echo $buhin_l ?></td>
                </tr>
                <tr>
                    <td align='center' bgcolor='white' class='pt11b'>��Ω�ų�</td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[52] ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[53] ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[54] ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[55] ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                    <td align='right' bgcolor='#ceffce' class='pt12b' width='110'><?php echo $kushi_l ?></td>
                </tr>
                <tr>
                    <td align='center'  bgcolor='#ceffce' class='pt11b'>�߸˷�</td>
                    <td align='right' bgcolor='#ceffce' class='pt12b'><?php echo $zai_all_l ?></td>
                    <td align='right' bgcolor='#ceffce' class='pt12b'><?php echo $kumi_all_l ?></td>
                    <td align='right' bgcolor='#ceffce' class='pt12b'><?php echo $kou_all_l ?></td>
                    <td align='right' bgcolor='#ceffce' class='pt12b'><?php echo $kan_all_l ?></td>
                    <td align='right' bgcolor='#ceffce' class='pt12b' width='110'><?php echo $zaiko_l ?></td>
                </tr>
                <tr>
                    <td colspan='6' align='center'>
                        <input type='submit' name='entry' value='�¹�' >
                    </td>
                    <td align='right' bgcolor='#ceffce' class='pt12b'><?php echo $zaiko ?></td>
                </tr>
            </table>
            </td></tr>
        </table> <!-- ���ߡ�End -->
        </form>
    </center>
</body>
</html>
