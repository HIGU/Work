<?php
//////////////////////////////////////////////////////////////////////////////
// �»�״ط� �����ǿ���� ���������׻�ɽ                                 //
// Copyright(C) 2021-2021 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp     //
// Changed history                                                          //
// 2021/04/23 Created   sales_tax_syozei_allo_view.php                      //
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
$menu->set_caption('�������칩��(��)');
//////////// �ƽ����action̾�ȥ��ɥ쥹����
// $menu->set_action('��ݲ�̾',   PL . 'address.php');

$url_referer     = $_SESSION['pl_referer'];     // �ƽФ�Ȥ� URL �����

$menu->set_action('���ʻųݣ�', PL . 'cost_parts_widget_view.php');
$menu->set_action('������', PL . 'cost_material_view.php');
$menu->set_action('����', PL . 'cost_parts_view.php');
$menu->set_action('��ʴ', PL . 'cost_kiriko_view.php');

///// �о�����
$ki2_ym   = $_SESSION['2ki_ym'];
$yyyymm   = $_SESSION['2ki_ym'];
$ki       = Ym_to_tnk($_SESSION['2ki_ym']);
$b_yyyymm = $yyyymm - 100;
$p1_ki    = Ym_to_tnk($b_yyyymm);

///// ������ ǯ��λ���
$yyyy = substr($yyyymm, 0,4);
$mm   = substr($yyyymm, 4,2);
if (($mm >= 1) && ($mm <= 3)) {
    $yyyy = ($yyyy - 1);
}
$pre_end_ym = $yyyy . "03";     // ������ǯ��

///// ����Ⱦ���μ���
$tuki_chk = substr($_SESSION['2ki_ym'],4,2);
if ($tuki_chk >= 1 && $tuki_chk <= 3) {           //�裴��Ⱦ��
    $hanki = '��';
} elseif ($tuki_chk >= 4 && $tuki_chk <= 6) {     //�裱��Ⱦ��
    $hanki = '��';
} elseif ($tuki_chk >= 7 && $tuki_chk <= 9) {     //�裲��Ⱦ��
    $hanki = '��';
} elseif ($tuki_chk >= 10) {    //�裳��Ⱦ��
    $hanki = '��';
}

///// ǯ���ϰϤμ���
if ($tuki_chk >= 1 && $tuki_chk <= 3) {           //�裴��Ⱦ��
    $str_ym = $yyyy . '04';
    $end_ym = $yyyymm;
} elseif ($tuki_chk >= 4 && $tuki_chk <= 6) {     //�裱��Ⱦ��
    $str_ym = $yyyy . '04';
    $end_ym = $yyyymm;
} elseif ($tuki_chk >= 7 && $tuki_chk <= 9) {     //�裲��Ⱦ��
    $str_ym = $yyyy . '04';
    $end_ym = $yyyymm;
} elseif ($tuki_chk >= 10) {    //�裳��Ⱦ��
    $str_ym = $yyyy . '04';
    $end_ym = $yyyymm;
}
///// TNK�� �� NK�����Ѵ�
$nk_ki   = $ki + 44;
$nk_p1ki = $p1_ki + 44;

//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
if ($tuki_chk == 3) {
    $menu->set_title("�� {$ki} �����ܷ軻���á����ǡ������ס�����ɽ");
} else {
    $menu->set_title("�� {$ki} ������{$hanki}��Ⱦ�����á����ǡ������ס�����ɽ");
}

$cost_ym = array();
$tuki_chk = substr($_SESSION['2ki_ym'],4,2);
if ($tuki_chk >= 1 && $tuki_chk <= 3) {           //�裴��Ⱦ��
    $hanki = '��';
    $yyyy_tou = $yyyy + 1;
    $cost_ym[0]  = $yyyy . '04';
    $cost_ym[1]  = $yyyy . '05';
    $cost_ym[2]  = $yyyy . '06';
    $cost_ym[3]  = $yyyy . '07';
    $cost_ym[4]  = $yyyy . '08';
    $cost_ym[5]  = $yyyy . '09';
    $cost_ym[6]  = $yyyy . '10';
    $cost_ym[7]  = $yyyy . '11';
    $cost_ym[8]  = $yyyy . '12';
    $cost_ym[9]  = $yyyy_tou . '01';
    $cost_ym[10] = $yyyy_tou . '02';
    $cost_ym[11] = $yyyy_tou . '03';
    $cnum        = 12;
} elseif ($tuki_chk >= 4 && $tuki_chk <= 6) {     //�裱��Ⱦ��
    $hanki = '��';
    $cost_ym[0]  = $yyyy . '04';
    $cost_ym[1]  = $yyyy . '05';
    $cost_ym[2]  = $yyyy . '06';
    $cnum        = 3;
} elseif ($tuki_chk >= 7 && $tuki_chk <= 9) {     //�裲��Ⱦ��
    $hanki = '��';
    $cost_ym[0] = $yyyy . '04';
    $cost_ym[1] = $yyyy . '05';
    $cost_ym[2] = $yyyy . '06';
    $cost_ym[3] = $yyyy . '07';
    $cost_ym[4]  = $yyyy . '08';
    $cost_ym[5]  = $yyyy . '09';
    $cnum        = 6;
} elseif ($tuki_chk >= 10) {    //�裳��Ⱦ��
    $hanki = '��';
    $cost_ym[0]  = $yyyy . '04';
    $cost_ym[1]  = $yyyy . '05';
    $cost_ym[2]  = $yyyy . '06';
    $cost_ym[3]  = $yyyy . '07';
    $cost_ym[4]  = $yyyy . '08';
    $cost_ym[5]  = $yyyy . '09';
    $cost_ym[6]  = $yyyy . '10';
    $cost_ym[7]  = $yyyy . '11';
    $cost_ym[8]  = $yyyy . '12';
    $cnum        = 9;
}

// ���4��ʬ
$cost_ym_next = $yyyy + 1 . '04';

// ���칩������񻺴ط�
if ($nk_ki == 65) {
    $nk_kotei             = 76600469;
    $nk_kotei_kei         = 598519;
    $nk_kotei_zei         = floor($nk_kotei * 0.1*pow(10,0))/pow(10,0);
    $nk_kotei_kei_zei     = floor($nk_kotei_kei * 0.1*pow(10,0))/pow(10,0);
    $nk_kotei_zei_edp     = 7660047;
    $nk_kotei_kei_zei_edp = 59852;
}

// �̥�˥塼�Ǻ��������ǡ����μ���

///////////// �ǡ���������ˤ�� ��¦��ɽ����ǡ�������
///////////// ̤ʧ��������̾����ǳ۷׻�ɽ ��׶�ۤ����
// query���϶���
$query = "select
                SUM(rep_kin) as t_kin
          from
                sales_tax_calculate_list";

// ����ι�׶�ۤ����
$t_kou8_kin   = 0;     // ��ȴ����(��8��)
$t_kou10_kin  = 0;     // ��ȴ����(10��)
$t_sumi10_kin = 0;     // �Ƕ�׾��(10��)
$t_zeigai_kin = 0;     // �����оݳ�
$t_kari10_kin = 0;     // ��ʧ������(10��)
$t_jido8_kin  = 0;     // ��ư�׻���(��8��)
$t_jido10_kin = 0;     // ��ư�׻���(10��)

// �Ƕ�׾��(10��)
for ($r=0; $r<$cnum; $r++) {
    // ���դ�����
    $d_ym = $cost_ym[$r];
    $search = "where rep_ki=$nk_ki and rep_ymd=$d_ym and rep_code='3333'";
    $query_s = sprintf("$query %s", $search);     // SQL query ʸ�δ���
    $res_sum = array();
    if ($rows=getResult($query_s, $res_sum) <= 0) {
        $m_sumi10_kin[$r] = 0;
    } else {
        $m_sumi10_kin[$r] = $res_sum[0][0];
        $t_sumi10_kin += $m_sumi10_kin[$r];
    }
}

// �����оݳ�
for ($r=0; $r<$cnum; $r++) {
    // ���դ�����
    $d_ym = $cost_ym[$r];
    $search = "where rep_ki=$nk_ki and rep_ymd=$d_ym and rep_kubun='X'";
    $query_s = sprintf("$query %s", $search);     // SQL query ʸ�δ���
    $res_sum = array();
    if ($rows=getResult($query_s, $res_sum) <= 0) {
        $m_zeigai_kin[$r] = 0;
    } else {
        $m_zeigai_kin[$r] = $res_sum[0][0];
        $t_zeigai_kin += $m_zeigai_kin[$r];
    }
}

// ��ʧ������(10��)
for ($r=0; $r<$cnum; $r++) {
    // ���դ�����
    $d_ym = $cost_ym[$r];
    $search = "where rep_ki=$nk_ki and rep_ymd=$d_ym and rep_kubun='3'";
    $query_s = sprintf("$query %s", $search);     // SQL query ʸ�δ���
    $res_sum = array();
    if ($rows=getResult($query_s, $res_sum) <= 0) {
        $m_kari10_kin[$r] = 0;
    } else {
        $m_kari10_kin[$r] = $res_sum[0][0];
        $t_kari10_kin += $m_kari10_kin[$r];
    }
}

// ��ȴ����(��8��)
for ($r=0; $r<$cnum; $r++) {
    // ���դ�����
    $d_ym = $cost_ym[$r];
    $search = "where rep_ki=$nk_ki and rep_ymd=$d_ym and rep_code='A108'";
    $query_s = sprintf("$query %s", $search);     // SQL query ʸ�δ���
    $res_sum = array();
    if ($rows=getResult($query_s, $res_sum) <= 0) {
        $m_kou8_kin[$r] = 0;
        $m_jido8_kin[$r] = 0;
    } else {
        $m_kou8_kin[$r]  = $res_sum[0][0];
        $m_jido8_kin[$r] = round($m_kou8_kin[$r] * 0.08, 0);
        $t_kou8_kin     += $m_kou8_kin[$r];
        $t_jido8_kin    += $m_jido8_kin[$r];
    }
}

// ��ȴ����(10��)
for ($r=0; $r<$cnum; $r++) {
    // ���դ�����
    $d_ym = $cost_ym[$r];
    $search = "where rep_ki=$nk_ki and rep_ymd=$d_ym";
    $query_s = sprintf("$query %s", $search);     // SQL query ʸ�δ���
    $res_sum = array();
    if ($rows=getResult($query_s, $res_sum) <= 0) {
        $m_kou10_kin[$r] = 0 - $m_kari10_kin[$r] - $m_kou8_kin[$r] - $m_sumi10_kin[$r] - $m_zeigai_kin[$r];
    } else {
        $m_kou10_kin[$r] = $res_sum[0][0] - $m_kari10_kin[$r] - $m_kou8_kin[$r] - $m_sumi10_kin[$r] - $m_zeigai_kin[$r];
        $t_kou10_kin += $m_kou10_kin[$r];
    }
}


///////////// ̤ʧ���ʧ����ɽ ��׶�ۤ����
// query���϶���
$query = "select
                SUM(rep_buy) as t_buy,
                SUM(rep_tax) as t_tax
          from
                sales_tax_payment_list";

// �������ʴ�ι�׶�ۤ����
$t_buy_kin = 0;
$t_tax_kin = 0;
for ($r=0; $r<$cnum; $r++) {
    // ���դ�����
    $d_ym = $cost_ym[$r];
    $search = "where rep_ki=$nk_ki and rep_ymd=$d_ym";
    $query_s = sprintf("$query %s", $search);     // SQL query ʸ�δ���
    $res_sum = array();
    if ($rows=getResult($query_s, $res_sum) <= 0) {
        $m_buy_kin[$r]    = 0 - $m_kou8_kin[$r];
        $m_tax_kin[$r]    = 0 - $m_jido8_kin[$r];
        $m_jido10_kin[$r] = 0 - $m_tax_kin[$r] - $m_kari10_kin[$r];
    } else {
        $m_buy_kin[$r] = $res_sum[0][0] - $m_kou8_kin[$r];
        $t_buy_kin += $m_buy_kin[$r];
        $m_tax_kin[$r] = $res_sum[0][1] - $m_jido8_kin[$r];
        $m_jido10_kin[$r] = $m_tax_kin[$r] - $m_kari10_kin[$r];
        $t_tax_kin += $m_tax_kin[$r];
        $t_jido10_kin += $m_jido10_kin[$r];
    }
}

///////// ���ܤȥ���ǥå����δ�Ϣ�դ�
$gitem = array();
$gitem[0]   = "����ȯ�������۷�8";
$gitem[1]   = "����ȯ�������ǳ۷�8";
$gitem[2]   = "����ȯ��������10";
$gitem[3]   = "����ȯ�������ǳ�10";
$gitem[4]   = "̤ʧ��ɼ��ȴ������8";
$gitem[5]   = "̤ʧ��ɼ��ȴ����10";
$gitem[6]   = "̤ʧ��ɼ�Ƕ�׾��10";
$gitem[7]   = "̤ʧ��ɼ�����оݳ�";
$gitem[8]   = "̤ʧ��ɼ��ʧ������10";
$gitem[9]   = "��ʧ�����Ǽ�ư�׻��۷�8";
$gitem[10]  = "��ʧ�����Ǽ�ư�׻���10"; 
$gitem[11]  = "��ʧ��������͢��"; 
$gitem[12]  = "̤ʧ�����������Ǽ��"; 
$gitem[13]  = $cost_ym_next . "���Ǽ���ǳ�"; 
$gitem[14]  = "��ʧ��������"; 
///////// �ƥǡ������ݴ�
$view_data = array();

$num_input = count($gitem);
for ($i = 0; $i < $num_input; $i++) {
    $query = sprintf("select rep_kin from sales_tax_create_data where rep_ki=%d and rep_note='%s'", $nk_ki, $gitem[$i]);
    $res_in = array();
    if (getResult2($query,$res_in) <= 0) {
        /////////// begin �ȥ�󥶥�����󳫻�
        if ($con = db_connect()) {
            query_affected_trans($con, "begin");
        } else {
            $_SESSION["s_sysmsg"] .= "�ǡ����١�������³�Ǥ��ޤ���";
            exit();
        }
        /////////// commit �ȥ�󥶥������λ
        query_affected_trans($con, "commit");
        $view_data[0][$i] = 0;
    } else {
        /////////// begin �ȥ�󥶥�����󳫻�
        if ($con = db_connect()) {
            query_affected_trans($con, "begin");
        } else {
            $_SESSION["s_sysmsg"] .= "�ǡ����١�������³�Ǥ��ޤ���";
            exit();
        }
        /////////// commit �ȥ�󥶥������λ
        query_affected_trans($con, "commit");
        $view_data[0][$i] = $res_in[0][0];
    }
}

// ̤ʧ������Ƿ׻�(�ڤ�Τơ�
$miha_siire_zei10  = floor($view_data[0][5] * 0.1*pow(10,0))/pow(10,0);
$miha_siire_zei8k  = floor($view_data[0][4] * 0.08*pow(10,0))/pow(10,0);
$miha_siire_zei10d = floor($view_data[0][6] * 0.1*pow(10,0))/pow(10,0);

// �ƥ��� ���ȤǺ��
$view_data[0][13] = 9019600;
// �����Ǽ���ǳۡ˷׻�
$view_data[0][13] = $view_data[0][12] + $view_data[0][13];

// 21���Τ�����
if ($nk_ki==65) {
    //$miha_siire_zei10d = $miha_siire_zei10d + 15344000;
}

/// ��ݥǡ�������
$str_ymd = $str_ym . '00';
$end_ymd = $end_ym . '99';
$query = sprintf("SELECT SUM(round(order_price*siharai,0)) FROM act_payable WHERE act_date>=%d and act_date<=%d and vendor<>'00222' and vendor<>'01111' and vendor<>'00948' and vendor<>'05001' and vendor<>'99999' and (vendor <'03000' or vendor> '03999') ", $str_ymd, $end_ymd);
$res_kai = array();
$kai_siire = 0;
if (getResult2($query,$res_kai) <= 0) {
    $kai_siire = 0;    
} else {
    $kai_siire = $res_kai[0][0];
}
// �����Ƿ׻�(�ڤ�Τơ�
$kai_siire_zei = floor($kai_siire * 0.1*pow(10,0))/pow(10,0);

/// �����ǡ������� 8% BK
$query = sprintf("SELECT SUM(rep_kin) - SUM(ROUND(rep_kin/1.08)) as kin FROM sales_tax_koujyo_siwake WHERE rep_ki=%d and rep_kamoku > '7501' and rep_kamoku <= '8123' and rep_kubun='BK'", $nk_ki);
$res_siwa8bk = array();
$siwa8bk_siire = 0;
if (getResult2($query,$res_siwa8bk) <= 0) {
    $siwa8bk_siire = 0;    
} else {
    $siwa8bk_siire = $res_siwa8bk[0][0];
}

/// �����ǡ������� 8% ZK
$query = sprintf("SELECT SUM(ROUND(rep_kin*1.08)) - SUM(rep_kin) FROM sales_tax_koujyo_siwake WHERE rep_ki=%d and rep_kamoku > '7501' and rep_kamoku <= '8123' and rep_kubun='ZK' and rep_teki='A008'", $nk_ki);
$res_siwa8zk = array();
$siwa8zk_siire = 0;
if (getResult2($query,$res_siwa8zk) <= 0) {
    $siwa8zk_siire = 0;    
} else {
    $siwa8zk_siire = $res_siwa8zk[0][0];
}
// �����Ƿ׻�(�ͼθ�����
$siwa8_siire     = $siwa8bk_siire + $siwa8zk_siire;
$siwa8_siire_zei = floor($siwa8_siire / 0.08*pow(10,0))/pow(10,0);

/// �����ǡ������� 8%�� �֥�󥯤�A108
$query = sprintf("SELECT SUM(rep_kin) as kin FROM sales_tax_koujyo_siwake WHERE rep_ki=%d and rep_kubun='' and rep_teki='A108'", $nk_ki);
$res_siwa8d = array();
$siwa8d_siire = 0;
if (getResult2($query,$res_siwa8d) <= 0) {
    $siwa8d_siire = 0;    
} else {
    $siwa8d_siire = $res_siwa8d[0][0];
}
// �����Ƿ׻�(�ͼθ�����
$siwa8d_siire_zei = round($siwa8d_siire / 0.08,0);

//�� ������10��� �׻�
$syo10_9_total = $view_data[0][14] + $view_data[0][11] + $view_data[0][12] - $siwa8_siire - $siwa8d_siire;

//�� ������ɼ������ ������10�� �׻�
$siwa10_siire = $syo10_9_total - $kai_siire_zei - $miha_siire_zei10 - $miha_siire_zei10d - $view_data[0][11] - $nk_kotei_zei - $nk_kotei_kei_zei - $view_data[0][12];

// ��ȴ����۷׻�
$siwa10_siire_zei = round($siwa10_siire / 0.1,0);

//�� ��ȴ��۷� �׻�
$zeinuki_16_total = $kai_siire + $view_data[0][5] + $view_data[0][4] + $view_data[0][6] + $siwa10_siire_zei + $siwa8d_siire_zei + $siwa8_siire_zei + $nk_kotei + $nk_kotei_kei;

//�� �����Ƿڣ���� �׻�
$syo8_kei_total = $siwa8d_siire + $miha_siire_zei8k;

//�� EDP NK������
$edp_nk_kotei = $kai_siire_zei + $view_data[0][10] + $view_data[0][9] + $view_data[0][8] + $siwa10_siire + $siwa8d_siire + $siwa8_siire;

//EDP�����Ƿ׾�� �� �׻�
$edp_syozei_kotei = $edp_nk_kotei + $view_data[0][11] + $nk_kotei_zei_edp + $nk_kotei_kei_zei_edp + $view_data[0][12];

//��ȴ��ۡʲ����оݡ˹�׶��
// ��10��
$zeinuki_kazei_kei10 = $kai_siire + $view_data[0][5] + $view_data[0][6] + $siwa10_siire_zei + $nk_kotei + $nk_kotei_kei;
// ��8���
$zeinuki_kazei_kei8d = $view_data[0][4] + $siwa8d_siire_zei;
// ��8��
$zeinuki_kazei_kei8  = $siwa8_siire_zei;

// Ĵ���׻�
// ��ݶ�׻��ۺ���
$kai_siire_sai = 0;

// ̤ʧ�������Ĵ��
// 10% ���ܭ�-A-C
$miha_zei_sai10 = $miha_siire_zei10 + $miha_siire_zei10d - $view_data[0][10] - $view_data[0][8];
// 8%�� ��-B
$miha_zei_sai8d = $miha_siire_zei8k - $view_data[0][9];

// ̤ʧ�������Ĵ��
// �����Ĵ�� (d - ���ˡܡ�e - ����
$kotei_cho_sai = ($nk_kotei_zei - $nk_kotei_zei_edp) + ($nk_kotei_kei_zei - $nk_kotei_kei_zei_edp);

// ��׷׻�
// 10��
$zeinuki_total_10 = $zeinuki_kazei_kei10;    // 10% ��ȴ��ۡʲ����оݡ�
$zei10_total_10   = $syo10_9_total;          // 10% �����ǣ����� ����ϭ���Ĵ���ط��ι��
$edp_total_10     = $kai_siire_zei + $view_data[0][10] + $view_data[0][8] + $siwa10_siire + $view_data[0][11] + $nk_kotei_zei_edp + $nk_kotei_kei_zei_edp + $view_data[0][12] + $miha_zei_sai10 + $kotei_cho_sai; // 10% �ţģо����Ƿ׾��
$zei4_total_10    = floor($zeinuki_total_10 * 0.078*pow(10,0))/pow(10,0); // 10% �����ǣ��� 10% ��ȴ��ۡʲ����оݡˤ�0.078�� �ڤ�Τ�
$zeikomi_total_10 = floor($zeinuki_total_10 * 1.1*pow(10,0))/pow(10,0); // 10% �ǹ���� 10% ��ȴ��ۡʲ����оݡˤ�1.1�� �ڤ�Τ�

// 8���
$zeinuki_total_8d = $zeinuki_kazei_kei8d; // 8%�� ��ȴ��ۡʲ����оݡ�
$zei8d_total_8d   = $syo8_kei_total;      // 8%�� �����Ƿڣ��� ����ϭ���Ĵ���ط��ι��
$edp_total_8d     = $view_data[0][9] + $siwa8d_siire + $miha_zei_sai8d; // 8%�� �ţģо����Ƿ׾��
$zei4_total_8d    = floor($zeinuki_total_8d * 0.0624*pow(10,0))/pow(10,0); // 8%�� �����ǣ��� 8%�� ��ȴ��ۡʲ����оݡˤ�0.0624�� �ڤ�Τ�
$zeikomi_total_8d = floor($zeinuki_total_8d * 1.08*pow(10,0))/pow(10,0); // 8%�� �ǹ���� 8%�� ��ȴ��ۡʲ����оݡˤ�1.08�� �ڤ�Τ�

// 8��
$zeinuki_total_8  = $siwa8_siire_zei;   // 8% ��ȴ��ۡʲ����оݡ� ����ϭ���Ĵ���ط��ι��
$zei8_total_8     = $siwa8_siire;       // 8% �����ǣ��� ����ϭ���Ĵ���ط��ι��
$edp_total_8      = $siwa8_siire;       // 8% �ţģо����Ƿ׾��
$zei4_total_8     = floor($zeinuki_total_8 * 0.063*pow(10,0))/pow(10,0); // 8% �����ǣ��� 8% ��ȴ��ۡʲ����оݡˤ�0.063�� �ڤ�Τ�
$zeikomi_total_8  = floor($zeinuki_total_8 * 1.08*pow(10,0))/pow(10,0); // 8% �ǹ���� 8% ��ȴ��ۡʲ����оݡˤ�1.08�� �ڤ�Τ�

// ���׷׻�
$zeinuki_total_all = $zeinuki_total_10 + $zeinuki_total_8d + $zeinuki_total_8;
$zei8_total_all    = $zei8_total_8;
$zei8d_total_all   = $zei8d_total_8d;
$zei10_total_all   = $zei10_total_10;
$edp_total_all     = $edp_total_10 + $edp_total_8d + $edp_total_8;
$zei4_total_all    = $zei4_total_10 + $zei4_total_8d + $zei4_total_8;
$zeikomi_total_all = $zeikomi_total_10 + $zeikomi_total_8d + $zeikomi_total_8;


if (isset($_POST['input_data'])) {                        // ����ǡ�������Ͽ
    ///////// ���ܤȥ���ǥå����δ�Ϣ�դ�
    ///////// ���ܤȥ���ǥå����δ�Ϣ�դ�
    $item = array();
    $item[0]   = "�����ǳ���ȴ��۲���10";
    $item[1]   = "�����ǳ���ȴ��۲��Ƿ�8";
    $item[2]   = "�����ǳ���ȴ��۲���8";
    $item[3]   = "�����ǳ���ȴ��۲��ǹ��";
    $item[4]   = "�����ǳ���ȴ�����8";
    $item[5]   = "�����ǳ���ȴ�����8���";
    $item[6]   = "�����ǳ���ȴ����Ƿ�8";
    $item[7]   = "�����ǳ���ȴ����Ƿ�8���";
    $item[8]   = "�����ǳ���ȴ�����10";
    $item[9]   = "�����ǳ���ȴ�����10���";
    $item[10]  = "�����ǳ���ȴ���EDP10"; 
    $item[11]  = "�����ǳ���ȴ���EDP��8"; 
    $item[12]  = "�����ǳ���ȴ���EDP8"; 
    $item[13]  = "�����ǳ���ȴ���EDP���"; 
    $item[14]  = "�����ǳ���ȴ�����410";
    $item[15]  = "�����ǳ���ȴ�����4��8";
    $item[16]  = "�����ǳ���ȴ�����48";
    $item[17]  = "�����ǳ���ȴ�����4���";
    $item[18]  = "�����ǳ���ȴ����ǹ�10";
    $item[19]  = "�����ǳ���ȴ����ǹ���8";
    $item[20]  = "�����ǳ���ȴ����ǹ�8";
    $item[21]  = "�����ǳ���ȴ����ǹ����";
    ///////// �ƥǡ������ݴ�
    $input_data = array();
    $input_data[0]   = $zeinuki_total_10;
    $input_data[1]   = $zeinuki_total_8d;
    $input_data[2]   = $zeinuki_total_8;
    $input_data[3]   = $zeinuki_total_all;
    $input_data[4]   = $zei8_total_8;
    $input_data[5]   = $zei8_total_all;
    $input_data[6]   = $zei8d_total_8d;
    $input_data[7]   = $zei8d_total_all;
    $input_data[8]   = $zei10_total_10;
    $input_data[9]   = $zei10_total_all;
    $input_data[10]  = $edp_total_10;
    $input_data[11]  = $edp_total_8d;
    $input_data[12]  = $edp_total_8;
    $input_data[13]  = $edp_total_all;
    $input_data[14]  = $zei4_total_10;
    $input_data[15]  = $zei4_total_8d;
    $input_data[16]  = $zei4_total_8;
    $input_data[17]  = $zei4_total_all;
    $input_data[18]  = $zeikomi_total_10;
    $input_data[19]  = $zeikomi_total_8d;
    $input_data[20]  = $zeikomi_total_8;
    $input_data[21]  = $zeikomi_total_all;
    ///////// �ƥǡ�������Ͽ
    //insert_date($item,$nk_ki,$input_data);
}

function insert_date($item,$nk_ki,$input_data) 
{
    $num_input = count($input_data);
    for ($i = 0; $i < $num_input; $i++) {
        $query = sprintf("select rep_kin from sales_tax_create_data where rep_ki=%d and rep_note='%s'", $nk_ki, $item[$i]);
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
            $query = sprintf("insert into sales_tax_create_data (rep_ki, rep_kin, rep_note, last_date, last_user) values (%d, %d, '%s', CURRENT_TIMESTAMP, '%s')", $nk_ki, $input_data[$i], $item[$i], $_SESSION['User_ID']);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION["s_sysmsg"] .= sprintf("%s�ο�����Ͽ�˼���<br> %d", $item[$i], $yyyymm);
                query_affected_trans($con, "rollback");     // transaction rollback
                exit();
            }
            /////////// commit �ȥ�󥶥������λ
            query_affected_trans($con, "commit");
            $_SESSION["s_sysmsg"] = sprintf("<font color='yellow'>%d ���������׻�ɽ�ǡ��� ���� ��Ͽ��λ</font>",$yyyymm);
        } else {
            /////////// begin �ȥ�󥶥�����󳫻�
            if ($con = db_connect()) {
                query_affected_trans($con, "begin");
            } else {
                $_SESSION["s_sysmsg"] .= "�ǡ����١�������³�Ǥ��ޤ���";
                exit();
            }
            ////////// UPDATE Start
            $query = sprintf("update sales_tax_create_data set rep_kin=%d, last_date=CURRENT_TIMESTAMP, last_user='%s' where rep_ki=%d and rep_note='%s'", $input_data[$i], $_SESSION['User_ID'], $nk_ki, $item[$i]);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION["s_sysmsg"] .= sprintf("%s��UPDATE�˼���<br> %d", $item[$i], $yyyymm);
                query_affected_trans($con, "rollback");     // transaction rollback
                exit();
            }
            /////////// commit �ȥ�󥶥������λ
            query_affected_trans($con, "commit");
            $_SESSION["s_sysmsg"] = sprintf("<font color='yellow'>%d ���������׻�ɽ�ǡ��� �ѹ� ��λ</font>",$yyyymm);
        }
    }
    $_SESSION["s_sysmsg"] .= "���������׻�ɽ�Υǡ�������Ͽ���ޤ�����";
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
<?= $menu->out_jsBaseClass() ?>
<script type=text/javascript language='JavaScript'>
<!--
function data_input_click(obj) {
    return confirm("����Υǡ�������Ͽ���ޤ���\n���˥ǡ�����������Ͼ�񤭤���ޤ���");
}
// -->
</script>
<style type='text/css'>
<!--
.pt10b {
    font-size:      11pt;
    font-weight:    bold;
    font-family:    monospace;
    color:          black;
}
.pt11b {
    font-size:      12pt;
    font-weight:    bold;
    font-family:    monospace;
}
th {
    background-color:   #ffffff;
    color:              blue;
    font:bold           12pt;
    font-family:        monospace;
}
-->
</style>
</head>
<body>
<?= $menu->out_title_border() ?>
        <?php
            //  bgcolor='#ceffce' ����
            //  bgcolor='#ffffc6' ��������
            //  bgcolor='#d6d3ce' Win ���쥤
        ?>
        <!--------------- ����������ʸ��ɽ��ɽ������ -------------------->
        <BR><BR>
        <left>
        ��������ɸ��۷׻�ɽ
        </left>
        <center>
        <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
        <table class='winbox_field' width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <thead>
                <!-- �ơ��֥� �إå�����ɽ�� -->
                <tr>
                    <th class='winbox' nowrap>����</th>
                    <th class='winbox' nowrap>�ǹ����</th>
                    <th class='winbox' nowrap>��ȴ���</th>
                    <th class='winbox' nowrap>�����ǣ���</th>
                    <th class='winbox' nowrap>�����ǣ���</th>
                    <th class='winbox' nowrap>�����ǣ�����</th>
                    <th class='winbox' nowrap>EDP�����Ƿ׾��</th>
                    <th class='winbox' nowrap>����</th>
                </tr>
            </thead>
            <tfoot>
                <!-- ���ߤϥեå����ϲ���ʤ� -->
            </tfoot>
            <tbody>
            <?php
            // �ţģ���ݶ�׾������ 2����ɽ���ʤ�
            echo "<tr>\n";
            // �ǹ����
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'><div class='pt10b'>�ţģ�������</div></span></td>\n";
            // ��ȴ��ۡʲ����оݡ�
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
            // �����ǣ���
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format(5072504131) . "</span></td>\n";
            // �����Ƿڣ���
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
            // �����ǣ�����
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
            // �����ǣ���
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
            // �ǹ����
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
            // ����
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
            echo "</tr>\n";
            
            // �ţģ���ݶ�׾������ 1����ɽ���ʤ�
            echo "<tr>\n";
            // �����ȥ�
            echo "<td class='winbox' nowrap bgcolor='white' align='center'><div class='pt10b'>��</div></td>\n";
            // �ǹ����
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
            // �����Ƿڣ���
            echo "  <th class='winbox' nowrap align='right'<span class='pt9'>��</span></td>\n";
            // �����ǣ�����
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
            // EDP�����Ƿ׾��
            echo "  <th class='winbox' nowrap align='right'<span class='pt9'>��</span></td>\n";
            // �����ǣ���
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
            // �ǹ����
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
            // ����
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
            echo "</tr>\n";
            
            // �ţģ���ݶ�׾������ 3���ܿ�������
            echo "<tr>\n";
            // �ǹ����
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'><div class='pt10b'>�嵭������������</div></span></td>\n";
            // ��ȴ��ۡʲ����оݡ�
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
            // ��ȴ��ۡʲ����оݡ�
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format(71276267) . "</span></td>\n";
            // ��ȴ��ۡʲ����оݡ�
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
            // �����ǣ���
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
            // �����Ƿڣ���
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
            // �����ǣ�����
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
            // �����ǣ�����
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
            echo "</tr>\n";
            
            // �ţģ�̤ʧ��׾������ 1����
            echo "<tr>\n";
            // �����ȥ�
            echo "<td class='winbox' nowrap bgcolor='white' align='center'><div class='pt10b'>��</div></td>\n";
            // �ǹ����
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
            // ��ȴ��ۡʲ����оݡ�
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
            // ��ȴ��ۡʲ����оݡ�
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
            // ��ȴ��ۡʲ����оݡ�
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
            // �����ǣ���
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
            // �����Ƿڣ���
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
            // �����ǣ���
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
            echo "</tr>\n";
            
            // �ţģ�̤ʧ��׾������ 2����
            echo "<tr>\n";
            // �����ȥ�
            echo "<td class='winbox' nowrap bgcolor='white' align='center'><div class='pt10b'>�� ������������</div></td>\n";
            // ��ȴ��ۡʲ����оݡ�
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
            // ��ȴ��ۡʲ����оݡ�
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format(5001227864) . "</span></td>\n";
            // ��ȴ��ۡʲ����оݡ�
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
            // �����ǣ���
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
            // �����Ƿڣ���
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format(500122786) . "</span></td>\n";
            // �����Ƿڣ���
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format(500122786) . "</span></td>\n";
            // �����ǣ�����
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format(0) . "</span></td>\n";
            echo "</tr>\n";
            
            // �ţģ�̤ʧ��׾������ 3����
            echo "<tr>\n";
            // �����ȥ�
            echo "<td class='winbox' nowrap bgcolor='white' align='center'><div class='pt10b'>�� ͭ���ٵ������</div></td>\n";
            // ��ȴ��ۡʲ����оݡ�
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
            // ��ȴ��ۡʲ����оݡ�
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format(51148811) . "</span></td>\n";
            // ��ȴ��ۡʲ����оݡ�
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
            // �����ǣ���
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
            // �����Ƿڣ���
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format(5114881) . "</span></td>\n";
            // �����ǣ�����
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format(5114656) . "</span></td>\n";
            // �����ǣ�����
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format(-225) . "</span></td>\n";
            echo "</tr>\n";
            
            // ������ɼ������ 1����
            echo "<tr>\n";
            // �����ȥ�
            echo "<td class='winbox' nowrap bgcolor='white' align='center'><div class='pt10b'>�� ��ʴ�����</div></td>\n";
            // �ǹ����
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
            // ��ȴ��ۡʲ����оݡ�
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format(93350079) . "</span></td>\n";
            // ��ȴ��ۡʲ����оݡ�
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
            // ��ȴ��ۡʲ����оݡ�
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
            // �����ǣ���
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format(9335008) . "</span></td>\n";
            // �����Ƿڣ���
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format(9335010) . "</span></td>\n";
            // �����ǣ�����
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format(2) . "</span></td>\n";
            echo "</tr>\n";
            
            // ������ɼ������ 2����
            echo "<tr>\n";
            // �����ȥ�
            echo "<td class='winbox' nowrap bgcolor='white' align='center'><div class='pt10b'>�� ����������(�����)</div></td>\n";
            // ��ȴ��ۡʲ����оݡ�
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
            // ��ȴ��ۡʲ����оݡ�
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format(0) . "</span></td>\n";
            // ��ȴ��ۡʲ����оݡ�
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
            // �����ǣ���
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format(0) . "</span></td>\n";
            // �����Ƿڣ���
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format(0) . "</span></td>\n";
            // �����Ƿڣ���
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format(0) . "</span></td>\n";
            // �����ǣ�����
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format(0) . "</span></td>\n";
            echo "</tr>\n";
            
            // ������ɼ������ 3����
            echo "<tr>\n";
            // �����ȥ�
            echo "<td class='winbox' nowrap bgcolor='white' align='center'><div class='pt10b'>�� �������칩��</div></td>\n";
            // ��ȴ��ۡʲ����оݡ�
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
            // ��ȴ��ۡʲ����оݡ�
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
            // ��ȴ��ۡʲ����оݡ�
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
            // �����ǣ���
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
            // �����ǣ���
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
            // �����Ƿڣ���
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
            // EDP�����Ƿ׾��
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
            echo "</tr>\n";
            
            // �������
            echo "<tr>\n";
            // �����ȥ�
            echo "<td class='winbox' nowrap bgcolor='white' align='center'><div class='pt10b'>����������(NK)</div></td>\n";
            // �ǹ����
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
            // ��ȴ��ۡʲ����оݡ�
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format(0) . "</span></td>\n";
            // ��ȴ��ۡʲ����оݡ�
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
            // �����ǣ���
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format(0) . "</span></td>\n";
            // �����ǣ���
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
            // �����Ƿڣ���
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format(0) . "</span></td>\n";
            // �����ǣ�����
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
            echo "</tr>\n";
            
            // ͢������˷����������
            echo "<tr>\n";
            // �����ȥ�
            echo "<td class='winbox' nowrap bgcolor='white' align='center'><div class='pt10b'>����������(SNK)</div></td>\n";
            // �ǹ����
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
            // ��ȴ��ۡʲ����оݡ�
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format(350238) . "</span></td>\n";
            // �����ǣ���
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
            // �����Ƿڣ���
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
            // �����ǣ�����
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format(35024) . "</span></td>\n";
            // �����ǣ�����
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format(35024) . "</span></td>\n";
            // EDP�����Ƿ׾��
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
            echo "</tr>\n";
            
            // ���칩������񻺴ط�
            echo "<tr>\n";
            // �����ȥ�
            echo "<td class='winbox' nowrap bgcolor='white' align='center'><div class='pt10b'>����¾</div></td>\n";
            // �ǹ����
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
            // ��ȴ��ۡʲ����оݡ�
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format(0) . "</span></td>\n";
            // �����ǣ���
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
            // �����Ƿڣ���
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format(0) . "</span></td>\n";
            // �����ǣ�����
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format(0) . "</span></td>\n";
            // EDP�����Ƿ׾��
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format(0) . "</span></td>\n";
            // �ǹ����
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
            echo "</tr>\n";
            
            // �����
            echo "<tr>\n";
            // �����ȥ�
            echo "<td class='winbox' nowrap bgcolor='white' align='center'><div class='pt10b'>��</div></td>\n";
            // �ǹ����
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
            // ��ȴ��ۡʲ����оݡ�
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
            // ��ȴ��ۡʲ����оݡ�
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
            // ��ȴ��ۡʲ����оݡ�
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
            // �����ǣ���
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
            // �����ǣ���
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
            // �����Ƿڣ���
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
            echo "</tr>\n";
            
            // ����񻺷���ʬ
            echo "<tr>\n";
            // �����ȥ�
            echo "<td class='winbox' nowrap bgcolor='white' align='center'><div class='pt10b'>�� ������</div></td>\n";
            // �ǹ����
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
            // ��ȴ��ۡʲ����оݡ�
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format(13357105) . "</span></td>\n";
            // ��ȴ��ۡʲ����оݡ�
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
            // ��ȴ��ۡʲ����оݡ�
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
            // �����ǣ���
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format(1335711) . "</span></td>\n";
            // �����ǣ���
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format(1335722) . "</span></td>\n";
            // �����Ƿڣ���
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format(12) . "</span></td>\n";
            echo "</tr>\n";
            
            // ê����
            echo "<tr>\n";
            // �����ȥ�
            echo "<td class='winbox' nowrap bgcolor='white' align='center'><div class='pt10b'>��</div></td>\n";
            // �ǹ����
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
            // ��ȴ��ۡʲ����оݡ�
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
            // ��ȴ��ۡʲ����оݡ�
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
            // ��ȴ��ۡʲ����оݡ�
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
            // �����ǣ���
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
            // �����ǣ���
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
            // �����Ƿڣ���
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
            echo "</tr>\n";
            
            // ���Ǽ�շ׾��
            echo "<tr>\n";
            // �����ȥ�
            echo "<td class='winbox' nowrap bgcolor='white' align='center'><div class='pt10b'>��</div></td>\n";
            // �ǹ����
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
            // ��ȴ��ۡʲ����оݡ�
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
            // �����ǣ���
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
            // �����Ƿڣ���
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
            // �����ǣ�����
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
            // �����ǣ�����
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
            // EDP�����Ƿ׾��
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
            echo "</tr>\n";
            
            // �����Ǽ�ճۡ�
            echo "<tr>\n";
            // �����ȥ�
            echo "<td class='winbox' nowrap bgcolor='white' align='center'><div class='pt10b'>��</div></td>\n";
            // �ǹ����
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
            // ��ȴ��ۡʲ����оݡ�
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
            // �����ǣ���
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
            // �����Ƿڣ���
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>Ĵ��</span></td>\n";
            // �����ǣ�����
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>Ĵ��</span></td>\n";
            // �����ǣ�����
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>Ĵ��</span></td>\n";
            // EDP�����Ƿ׾��
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
            echo "</tr>\n";
            
            // ��
            echo "<tr>\n";
            // �����ȥ�
            echo "<td class='winbox' nowrap bgcolor='white' align='center'><div class='pt10b'>��</div></td>\n";
            // �ǹ����
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
            // ��ȴ��ۡʲ����оݡ�
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
            // ��ȴ��ۡʲ����оݡ�
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
            // ��ȴ��ۡʲ����оݡ�
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
            // �����ǣ���
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format(-212) . "</span></td>\n";
            // �����ǣ���
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format(0) . "</span></td>\n";
            // �����Ƿڣ���
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>�������������̷׾�</span></td>\n";
            echo "</tr>\n";
            
            // �����ȥ�ʤ�
            echo "<tr>\n";
            // �����ȥ�
            echo "<td class='winbox' nowrap bgcolor='white' align='center'><div class='pt10b'>�� �����Ĵ��������������</div></td>\n";
            // �ǹ����
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
            // ��ȴ��ۡʲ����оݡ�
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
            // ��ȴ��ۡʲ����оݡ�
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
            // ��ȴ��ۡʲ����оݡ�
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
            // �����ǣ���
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
            // �����Ƿڣ���
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
            // �����ǣ�����
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
            echo "</tr>\n";
            
            // �����ȥ�ʤ�
            echo "<tr>\n";
            // �����ȥ�
            echo "<td class='winbox' nowrap bgcolor='white' align='center'><div class='pt10b'>�ţģв������������׾��</div></td>\n";
            // �ǹ����
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
            // ��ȴ��ۡʲ����оݡ�
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format(5159434097) . "</span></td>\n";
            // ��ȴ��ۡʲ����оݡ�
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format(0) . "</span></td>\n";
            // �����ǣ���
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format(0) . "</span></td>\n";
            // �����Ƿڣ���
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format(515943198) . "</span></td>\n";
            // �����ǣ�����
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format(515943198) . "</span></td>\n";
            // �����ǣ���
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
            echo "</tr>\n";
            
            // �����ȥ�ʤ�
            echo "<tr>\n";
            // �����ȥ�
            echo "<td class='winbox' nowrap bgcolor='white' align='center'><div class='pt10b'>��</div></td>\n";
            // �ǹ����
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
            // ��ȴ��ۡʲ����оݡ�
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
            // ��ȴ��ۡʲ����оݡ�
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
            // �����ǣ���
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
            // �����Ƿڣ���
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
            // �����ǣ�����
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
            // �����ǣ���
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
            echo "</tr>\n";
            
            // �����ȥ�ʤ�
            echo "<tr>\n";
            // �����ȥ�
            echo "<td class='winbox' nowrap bgcolor='white' align='center' colspan='3'><div class='pt10b'>�� ����ɸ���</div></td>\n";
            // �ǹ����
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>�ɽ�Ĺ�</span></td>\n";
            // ��ȴ��ۡʲ����оݡ�
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
            // ��ȴ��ۡʲ����оݡ�
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>�ɽ�Ĺ�</span></td>\n";
            // ��ȴ��ۡʲ����оݡ�
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format(515943182) . "</span></td>\n";
            // �ǹ����
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
            echo "</tr>\n";
            
            // �����ȥ�ʤ�
            echo "<tr>\n";
            // �����ȥ�
            echo "<td class='winbox' nowrap bgcolor='white' align='center' colspan='3'><div class='pt10b'>���ܭ��ܭ��ܭ��ܭ��ܭ��ܭ�</div></td>\n";
            // �ǹ����
            echo "  <th class='winbox' nowrap align='right' rowspan='6'><span class='pt9'>Ĵ��</span></td>\n";
            // ��ȴ��ۡʲ����оݡ�
            echo "  <th class='winbox' nowrap align='right' colspan='2'><span class='pt9'>���������Ǥؿ���</span></td>\n";
            // ��ȴ��ۡʲ����оݡ�
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
            // �ǹ����
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
            echo "</tr>\n";
            
            // �����ȥ�ʤ�
            echo "<tr>\n";
            // �����ȥ�
            echo "<td class='winbox' nowrap bgcolor='white' align='center' colspan='3'><div class='pt10b'>��</div></td>\n";
            // ��ȴ��ۡʲ����оݡ�
            echo "  <th class='winbox' nowrap align='right' colspan='2'><span class='pt9'>(�����)</span></td>\n";
            // ��ȴ��ۡʲ����оݡ�
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
            // �ǹ����
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
            echo "</tr>\n";
            
            // �����ȥ�ʤ�
            echo "<tr>\n";
            // �����ȥ�
            echo "<td class='winbox' nowrap bgcolor='white' align='center' colspan='3'><div class='pt10b'>��</div></td>\n";
            // ��ȴ��ۡʲ����оݡ�
            echo "  <th class='winbox' nowrap align='right' colspan='2'><span class='pt9'>��ʧ�����Ǥؿ���</span></td>\n";
            // ��ȴ��ۡʲ����оݡ�
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
            // �ǹ����
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
            echo "</tr>\n";
            
            // �����ȥ�ʤ�
            echo "<tr>\n";
            // �����ȥ�
            echo "<td class='winbox' nowrap bgcolor='white' align='center' colspan='3'><div class='pt10b'>��</div></td>\n";
            // ��ȴ��ۡʲ����оݡ�
            echo "  <th class='winbox' nowrap align='right' colspan='2'><span class='pt9'>(�����)</span></td>\n";
            // ��ȴ��ۡʲ����оݡ�
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
            // �ǹ����
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
            echo "</tr>\n";
            
            // �����ȥ�ʤ�
            echo "<tr>\n";
            // �����ȥ�
            echo "<td class='winbox' nowrap bgcolor='white' align='center' colspan='3'><div class='pt10b'>��</div></td>\n";
            // ��ȴ��ۡʲ����оݡ�
            echo "  <th class='winbox' nowrap align='right' colspan='2'><span class='pt9'>����¾</span></td>\n";
            // ��ȴ��ۡʲ����оݡ�
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format(16) . "</span></td>\n";
            // �ǹ����
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
            echo "</tr>\n";
            
            // �����ȥ�ʤ�
            echo "<tr>\n";
            // �����ȥ�
            echo "<td class='winbox' nowrap bgcolor='white' align='center' colspan='3'><div class='pt10b'>��</div></td>\n";
            // ��ȴ��ۡʲ����оݡ�
            echo "  <th class='winbox' nowrap align='right' colspan='2'><span class='pt9'>��</span></td>\n";
            // ��ȴ��ۡʲ����оݡ�
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
            // �ǹ����
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
            echo "</tr>\n";
            
            // �����ȥ�ʤ�
            echo "<tr>\n";
            // �����ȥ�
            echo "<td class='winbox' nowrap bgcolor='white' align='center' colspan='3'><div class='pt10b'>��</div></td>\n";
            // �ǹ����
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>�����Ĺ�</span></td>\n";
            // ��ȴ��ۡʲ����оݡ�
            echo "  <th class='winbox' nowrap align='right' colspan='2'><span class='pt9'>����</span></td>\n";
            // ��ȴ��ۡʲ����оݡ�
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format(515943198) . "</span></td>\n";
            // �ǹ����
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
            echo "</tr>\n";
            ?>
            </tbody>
        </table>
            </td></tr>
        </table> <!----------------- ���ߡ�End ------------------>
        </center>    
        <!--------------- ����������ʸ��ɽ��ɽ������ -------------------->
        <BR><BR>
        <left>
        ��������������γ�ǧ
        </left>
        <center>
        <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
        <table class='winbox_field' width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <thead>
                <!-- �ơ��֥� �إå�����ɽ�� -->
                <tr>
                    <th class='winbox' nowrap colspan='7'>���ǻ�ξ��������в��γۤη׻�</th>
                </tr>
                <tr>
                    <th class='winbox' nowrap>����</th>
                    <th class='winbox' nowrap>���</th>
                    <th class='winbox' nowrap colspan='2'>����</th>
                    <th class='winbox' nowrap>���</th>
                    <th class='winbox' nowrap colspan='2'>��</th>
                </tr>
            </thead>
            <tfoot>
                <!-- ���ߤϥեå����ϲ���ʤ� -->
            </tfoot>
            <tbody>
            <?php
            // Ĵ��
            echo "<tr>\n";
            // �����ȥ�
            echo "<td class='winbox' nowrap bgcolor='white' align='center'><div class='pt10b'>����������</div></td>\n";
            // �ǹ����
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format(5159434097) . "</span></td>\n";
            // ��ȴ��ۡʲ����оݡ�
            echo "  <th class='winbox' nowrap align='right' colspan='2'><span class='pt9'>����������������� ��</span></td>\n";
            // �����ǣ���
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format(5230710364) . "</span></td>\n";
            // �����Ƿڣ���
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
            // �����ǣ�����
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>�׻���</span></td>\n";
            echo "</tr>\n";
            
            // Ĵ��
            echo "<tr>\n";
            // �����ȥ�
            echo "<td class='winbox' nowrap bgcolor='white' align='center'><div class='pt10b'>��������</div></td>\n";
            // �����ǣ���
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format(71276267) . "</span></td>\n";
            // �����Ƿڣ���
            echo "  <th class='winbox' nowrap align='right' rowspan='5'><span class='pt9'>���������</span></td>\n";
            // �����ǣ�����
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>������©</span></td>\n";
            // EDP�����Ƿ׾��
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format(166225) . "</span></td>\n";
            // �����ǣ���
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>�ɽ�Ĺ�</span></td>\n";
            // �ǹ����
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>����¡�</span></td>\n";
            echo "</tr>\n";
            
            // Ĵ��
            echo "<tr>\n";
            // �����ȥ�
            echo "<td class='winbox' nowrap bgcolor='white' align='center'><div class='pt10b'>��</div></td>\n";
            // ��ȴ��ۡʲ����оݡ�
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
            // �����ǣ���
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>������</span></td>\n";
            // �����Ƿڣ���
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format(118798) . "</span></td>\n";
            // �����ǣ�����
            echo "  <th class='winbox' nowrap align='right'<span class='pt9'>�����Ƿ׻�ɽ</span></td>\n";
            // EDP�����Ƿ׾��
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>99.99</span></td>\n";
            echo "</tr>\n";
            
            // Ĵ��
            echo "<tr>\n";
            // �����ȥ�
            echo "<td class='winbox' nowrap bgcolor='white' align='center'><div class='pt10b'>��</div></td>\n";
            // ��ȴ��ۡʲ����оݡ�
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
            // �����ǣ���
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>ͭ���ڷ���©</span></td>\n";
            // �����Ƿڣ���
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format(0) . "</span></td>\n";
            // �����ǣ�����
            echo "  <th class='winbox' nowrap align='right'<span class='pt9'>�ɽ�Ĺ�</span></td>\n";
            // EDP�����Ƿ׾��
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
            echo "</tr>\n";
            
            // Ĵ��
            echo "<tr>\n";
            // �����ȥ�
            echo "<td class='winbox' nowrap bgcolor='white' align='center'><div class='pt10b'>��</div></td>\n";
            // ��ȴ��ۡʲ����оݡ�
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
            // �����ǣ���
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>����������</span></td>\n";
            // �����Ƿڣ���
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format(0) . "</span></td>\n";
            // �����ǣ�����
            echo "  <th class='winbox' nowrap align='right'<span class='pt9'>��</span></td>\n";
            // EDP�����Ƿ׾��
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
            echo "</tr>\n";
            
            // Ĵ��
            echo "<tr>\n";
            // �����ȥ�
            echo "<td class='winbox' nowrap bgcolor='white' align='center'><div class='pt10b'>��</div></td>\n";
            // ��ȴ��ۡʲ����оݡ�
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
            // �����ǣ���
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
            // �����Ƿڣ���
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
            // �����ǣ�����
            echo "  <th class='winbox' nowrap align='right'<span class='pt9'>��</span></td>\n";
            // EDP�����Ƿ׾��
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
            echo "</tr>\n";
            
            // ����Ĵ��ʬ
            echo "<tr>\n";
            // �����ȥ�
            echo "<td class='winbox' nowrap bgcolor='white' align='center' rowspan='2'><div class='pt10b'>��        ��</div></td>\n";
            // �ǹ����
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format(5230710364) . "</span></td>\n";
            // ��ȴ��ۡʲ����оݡ�
            echo "  <th class='winbox' nowrap align='right' colspan='2'><span class='pt9'>��        ��</span></td>\n";
            // �����ǣ���
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format(5230995387) . "</span></td>\n";
            // �����Ƿڣ���
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
            // �����ǣ�����
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
            
            /*
            // ���ɽ��
            
            echo "<tr>\n";
            // ǯ��
            echo "<td class='winbox' nowrap bgcolor='white' align='center'><div class='pt10b'>���</div></td>\n";
            // ������(��8��)
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($t_kou8_kin) . "</span></td>\n";
            // �����ǳ�(��8��)
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($t_jido8_kin) . "</span></td>\n";
            // ������(10%)
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($t_buy_kin) . "</span></td>\n";
            // �����ǳ�(10%)
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($t_tax_kin) . "</span></td>\n";
            // ��ȴ����(��8��)
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($t_kou8_kin) . "</span></td>\n";
            // ��ȴ����(10%)
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($t_kou10_kin) . "</span></td>\n";
            // ��ȴ�׾��(10%)
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($t_sumi10_kin) . "</span></td>\n";
            // �����оݳ�
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($t_zeigai_kin) . "</span></td>\n";
            // ��ʧ������(10%)
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($t_kari10_kin) . "</span></td>\n";
            // ��ư�׻���(��8��)
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($t_jido8_kin) . "</span></td>\n";
            // ��ư�׻���(10%)
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($t_jido10_kin) . "</span></td>\n";
            echo "</tr>\n";
            */
            ?>
            </tbody>
        </table>
            </td></tr>
        </table> <!----------------- ���ߡ�End ------------------>
        
        <form method='post' action='<?php echo $menu->out_self() ?>'>
            <input class='pt10b' type='submit' name='input_data' value='��Ͽ' onClick='return data_input_click(this)'>
        </form>
    </center>
</body>
</html>
