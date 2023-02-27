<?php
//////////////////////////////////////////////////////////////////////////////
// �������칩�� ���������������Ψɽ��                                  //
// Copyright (C) 2004-2013 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2004/01/27 Created  materialCost_sales_comp.php                          //
// 2004/05/12 �����ȥ�˥塼ɽ������ɽ�� �ܥ����ɲ� menu_OnOff($script)�ɲ� //
// 2004/11/02 ���ץ顦��˥��������Ρ�ɸ�ࡦ����(�Х����)���ɲä����β�    //
// 2005/01/17 ���ץ�ɸ�ब �� �����ɸ��ˤʤäƤ����������              //
//            industry/ �� industry/material��  MenuHeader���饹���ѹ�      //
// 2005/06/14 ʸ�������������礭�����Ʋ��̤򸫤䤹������(�ǥ�����)          //
// 2005/08/22 �������Ψ���ɲ� ���ܤ���Ω��(����) �����ס������ ���ɲ� //
//            �ǽ��������ؿ� set_last_day() ���ɲ�                      //
// 2005/08/24 �ѥ���ɴ����򥳥��ȥ�����                                //
// 2006/02/06 ������Ωʬ�򥫥ץ�ɸ�ࡦ���� ��˥�ɸ�ࡦ�Х�������Ψʬ��   //
// 2007/09/28 Uround(assy_time * assy_rate, 2) ��    ��ư����Ψ��׻����ɲ� //
//    Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) //
// 2013/01/28 ����̾��Ƭʸ����DPE�Τ�Τ���Υݥ��(�Х����)�ǽ��פ���褦 //
//            ���ѹ�                                                   ��ë //
//            �Х�������Υݥ�פ��ѹ� ɽ���Τߥǡ����ϥХ����Τޤ� ��ë//
// 2013/01/31 ��˥��Τߤ�DPEȴ��SQL������                             ��ë //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
ob_start('ob_gzhandler');                   // ���ϥХåե���gzip����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��

require_once ('../function.php');
require_once ('../tnk_func.php');
require_once ('../MenuHeader.php');      // TNK ������ menu class
access_log();                               // Script Name �ϼ�ư����

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader(0);                  // ǧ�ڥ����å�0=���̰ʾ� �����=TOP_MENU �����ȥ�̤����

////////////// ����������
$menu->set_site( 1, 15);                    // ������˥塼=30 ����˥塼=1 �Ǹ�Υ�˥塼= 99 �����ƥ�����Ѥϣ�����
                                            // �����������=12 �����������=19  ���̥�˥塼̵��<=0 �ƥ�ץ졼�ȥե�����ϣ�����

////////////// �꥿���󥢥ɥ쥹����
// $menu->set_RetUrl(SALES_MENU);                // �̾�ϻ��ꤹ��ɬ�פϤʤ�
//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title('����Ⱥ������(���)��Ψɽ');
//////////// ɽ�������
$menu->set_caption('�������������������򤷤Ʋ�������');

//////////// �֥饦�����Υ���å����к���
$uniq = $menu->set_useNotCache('target');

// �ǽ��������function
function set_last_day($date) {
    if (strlen($date) == 8) {
        $year  = substr($date, 0, 4);
        $month = substr($date, 4, 2);
        $day   = substr($date, 6, 2);
        if ($month == '02') {
            if ($day < 29) return $date;
        } else {
            if ($day < 31) return $date;
        }
    }
    return ($year . $month . last_day($year, $month) );
}

/////////// exec ��submit���줿��
while (isset($_POST['exec'])) {
    // $_SESSION['s_uri_passwd'] = $_POST['uri_passwd'];
    $_SESSION['s_div']        = $_POST['div'];
    $_SESSION['s_d_start']    = $_POST['d_start'];
    $_SESSION['s_d_end']      = $_POST['d_end'];
    $_SESSION['s_kubun']      = $_POST['kubun'];
    $_SESSION['uri_assy_no']  = $_POST['assy_no'];
    // $uri_passwd = $_SESSION['s_uri_passwd'];
    $div        = $_SESSION['s_div'];
    $d_start    = $_SESSION['s_d_start'];
    $d_end      = $_SESSION['s_d_end'];
    $kubun      = $_SESSION['s_kubun'];
    $assy_no    = $_SESSION['uri_assy_no'];
    $d_end = set_last_day($d_end);
    $_SESSION['s_d_end'] = $d_end;
    
    ////////////// �ѥ���ɥ����å�
    /**********************************
    if ($uri_passwd != date('Ymd')) {
        $_SESSION['s_sysmsg'] = "<font color='yellow'>�ѥ���ɤ��㤤�ޤ���</font>";
        unset($_POST['exec']);
        break;
    }
    **********************************/
    ///////////// ��礻��SQLʸ����
    $query = "select
                    -- count(����)                      AS �����,
                    -- sum(����)                        AS ������,
                    sum(Uround(���� * ñ��, 0))         AS ����,
                    sum(Uround(���� * ext_price, 0))    AS ��������,    -- ������(����)
                    sum(Uround(���� * int_price, 0) +
                    Uround(���� * Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2), 0))
                                                        AS �����,    -- ������(���)+��Ω��=�����
                    sum(Uround(���� * sum_price, 0) +
                    Uround(���� * Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2), 0))
                                                        AS �������,    -- ������(���)+��Ω��=�������
                    sum(Uround(���� * int_price, 0))    AS �����¤,
                    sum(Uround(���� * Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) ,0))
                                                        AS �����Ω,    -- ������Ψ�ˤ��
                    sum(Uround(���� * Uround(m_time*m_rate, 2), 0))
                                                        AS ������,  -- ɸ����Ψ�ˤ��
                    sum(Uround(���� * Uround(a_time*a_rate, 2), 0))
                                                        AS ��ư��,  -- ɸ����Ψ�ˤ��
                    sum(Uround(���� * Uround(g_time*g_rate, 2), 0))
                                                        AS ����,    -- ɸ����Ψ�ˤ��(�⿦)
                    count(*)                            AS ����,
                    count(*) - count(sum_price)         AS ̤��Ͽ
              from
                    hiuuri as u
              left outer join
                    assembly_schedule as a
              on u.�ײ��ֹ�=a.plan_no
              left outer join
                    material_cost_header as mate
              on u.�ײ��ֹ�=mate.plan_no
              left outer join
                    miitem as m
              on assyno=m.mipn";
    //////////// SQL where ��� ���Ѥ���
    $search = "where �׾���>=$d_start and �׾���<=$d_end";
    if ($assy_no != '') {       // �����ֹ椬���ꤵ�줿���
        $search .= " and assyno like '{$assy_no}%%'";
    } elseif ($div == 'H') {    // ��ɸ��ʤ�
        $search .= " and ������='C' and note15 not like 'SC%%'";
    } elseif ($div == 'S') {    // ������ʤ�
        $search .= " and ������='C' and note15 like 'SC%%'";
    } elseif ($div == "M") {    // ��˥�ɸ��ξ��ϻ������� assyno �ǥ����å�
        //$search .= " and ������='L' and (assyno NOT like 'LC%%' AND assyno NOT like 'LR%%')";
        $search .= " and ������='L' and (assyno NOT like 'LC%%' AND assyno NOT like 'LR%%') and CASE WHEN assyno = '' THEN ������='L' ELSE m.midsc not like 'DPE%%' END";
    } elseif ($div == "B") {    // �Х����ξ��� assyno �ǥ����å�
        //$search .= " and (assyno like 'LC%%' or assyno like 'LR%%')";
        $search .= " and (assyno like 'LC%%' or assyno like 'LR%%' or m.midsc like 'DPE%%')";
    } elseif ($div != " ") {
        $search .= " and ������='$div'";
    }
    if ($kubun != " ") {
        $search .= " and datatype='$kubun'";
    }
    $query = sprintf("$query %s", $search);     // SQL query ʸ�δ���
    $res = array();
    if (getResult($query, $res) <= 0) {
        $_SESSION['s_sysmsg'] = "<font color='yellow'>�ǡ���������ޤ���</font>";
        unset($_POST['exec']);
        break;
    }
    if ($res[0]['����'] <= 0) {
        $_SESSION['s_sysmsg'] = "<font color='yellow'>�о��ϰϤ����⤬����ޤ���</font>";
        unset($_POST['exec']);
        break;
    }
    ///////////// ��Ω��(����)��ۼ�����SQLʸ����
    $query =
    "
        select
            sum(Uround(siharai * order_price, 0))   AS ��Ω����
            , Uround(sum(Uround(siharai * order_price, 0)) * 0.7, 0)
                                                    AS ɸ�೰��
            , sum(Uround(siharai * order_price, 0)) - Uround(sum(Uround(siharai * order_price, 0)) * 0.7, 0)
                                                    AS ������
            , Uround(sum(Uround(siharai * order_price, 0)) * 0.48, 0)
                                                    AS ��˥�����
            , sum(Uround(siharai * order_price, 0)) - Uround(sum(Uround(siharai * order_price, 0)) * 0.48, 0)
                                                    AS �Х���볰��
        from
            act_payable
        where
            act_date >= $d_start and act_date <= $d_end
            and
            type_no = 2
            and
            (kamoku = 2 OR kamoku = 3 OR kamoku = 4 OR kamoku = 5)
    ";
    ///// ������(���롼��)�ϥ��ץ����Τȥ�˥����Τˤ���ʬ�����������ʤ�
    switch ($div) {
    case 'C':
    case 'H':
    case 'S':
        $query .= "and div = 'C'";
        break;
    case 'L':
    case 'M':
    case 'B':
        $query .= "and div = 'L'";
        break;
    default:
        // �����롼�פȤʤ�
    }
    $res_kumi = array();
    if (getResult($query, $res_kumi) <= 0) {
        $_SESSION['s_sysmsg'] = "<font color='yellow'>�ǡ���������ޤ���</font>";
        unset($_POST['exec']);
        break;
    } else {
        switch ($div) {
        case 'C':
            $res[0]['��Ω����'] = $res_kumi[0]['��Ω����'];
            break;
        case 'H':
            $res[0]['��Ω����'] = $res_kumi[0]['ɸ�೰��'];
            break;
        case 'S':
            $res[0]['��Ω����'] = $res_kumi[0]['������'];
            break;
        case 'L':
            $res[0]['��Ω����'] = $res_kumi[0]['��Ω����'];
            break;
        case 'M':
            $res[0]['��Ω����'] = $res_kumi[0]['��˥�����'];
            break;
        case 'B':
            $res[0]['��Ω����'] = $res_kumi[0]['�Х���볰��'];
            break;
        default:
            $res[0]['��Ω����'] = $res_kumi[0]['��Ω����'];
            // �����롼�פȤʤ�
        }
    }
    
    ///// ������η׻�
    $res[0]['������'] = $res[0]['��������'] + $res[0]['��Ω����'];     // ��Ω��(����)�ɲ� 2005/08/22
    $ext_bu_percent  = Uround($res[0]['��������'] / $res[0]['����'] * 100, 2);// �������ʤ�������
    $extkumi_percent = Uround($res[0]['��Ω����'] / $res[0]['����'] * 100, 2);// ��Ω�����������
    $res[0]['�������'] = ($res[0]['������'] + $res[0]['�����']);          // sum_price�ȸ����Ф뤿���ɲ�
    $ext_percent = Uround($res[0]['������'] / $res[0]['����'] * 100, 2);    // �����פ�������
    $int_percent = Uround($res[0]['�����'] / $res[0]['����'] * 100, 2);    // ����פ�������
    $sum_percent = Uround($res[0]['�������'] / $res[0]['����'] * 100, 2);    // ��������������
    $int_seizou  = Uround($res[0]['�����¤'] / $res[0]['����'] * 100, 2);    // �����¤��������
    $int_assy    = Uround($res[0]['�����Ω'] / $res[0]['����'] * 100, 2);    // �����Ω��������
    ///// �������η׻�
    $ext_bu_percent_zai  = Uround($res[0]['��������'] / $res[0]['�������'] * 100, 2);    // �������ʤ��������
    $extkumi_percent_zai = Uround($res[0]['��Ω����'] / $res[0]['�������'] * 100, 2);    // ��Ω������������
    $ext_percent_zai = Uround($res[0]['������'] / $res[0]['�������'] * 100, 2);      // �����פ��������
    $int_percent_zai = Uround($res[0]['�����'] / $res[0]['�������'] * 100, 2);      // ����פ��������
    $sum_percent_zai = Uround($res[0]['�������'] / $res[0]['�������'] * 100, 2);      // ���������������
    $int_seizou_zai  = Uround($res[0]['�����¤'] / $res[0]['�������'] * 100, 2);      // �����¤���������
    $int_assy_zai    = Uround($res[0]['�����Ω'] / $res[0]['�������'] * 100, 2);      // �����Ω���������
    
    ///// ������η׻�(ɸ����Ψ) �㤦�Τ������Ω������ס��������Σ���
    $res[0]['�����Ω2'] = ($res[0]['������'] + $res[0]['��ư��'] + $res[0]['����']);  // ɸ����Ψ�ˤ����Ω��
    $int_assy2           = Uround($res[0]['�����Ω2'] / $res[0]['����'] * 100, 2);   // �����Ω��������(ɸ����Ψ)
    $res[0]['�����2'] = ($res[0]['�����¤'] + $res[0]['�����Ω2']);
    $res[0]['�������2'] = ($res[0]['������'] + $res[0]['�����2']);
    $int_percent2        = Uround($res[0]['�����2'] / $res[0]['����'] * 100, 2);   // ����פ�������(ɸ����Ψ)
    $sum_percent2        = Uround($res[0]['�������2'] / $res[0]['����'] * 100, 2);   // ��������������(ɸ����Ψ)
    ///// �������η׻�(ɸ����Ψ) �㤦�Τ������Ω������ס��������Σ��� �ȳ������ʡ���Ω��������¤
    $int_assy2_zai    = Uround($res[0]['�����Ω2'] / $res[0]['�������2'] * 100, 2);   // �����Ω���������(ɸ����Ψ)
    $int_percent2_zai = Uround($res[0]['�����2'] / $res[0]['�������2'] * 100, 2);   // ����פ��������(ɸ����Ψ)
    $sum_percent2_zai = Uround($res[0]['�������2'] / $res[0]['�������2'] * 100, 2);   // ���������������(ɸ����Ψ)
    $ext_bu_percent2_zai  = Uround($res[0]['��������'] / $res[0]['�������2'] * 100, 2);    // �������ʤ��������
    $extkumi_percent2_zai = Uround($res[0]['��Ω����'] / $res[0]['�������2'] * 100, 2);    // ��Ω������������
    $int_seizou2_zai  = Uround($res[0]['�����¤'] / $res[0]['�������2'] * 100, 2);      // �����¤���������
    $ext_percent2_zai = Uround($res[0]['������'] / $res[0]['�������2'] * 100, 2);      // �����פ��������
    break;
}

/////////////// �����Ϥ��ѿ��ν����
/*****************************************
if ( isset($_SESSION['s_uri_passwd']) ) {
    $uri_passwd = $_SESSION['s_uri_passwd'];
} else {
    $uri_passwd = '';
}
*****************************************/
if ( isset($_SESSION['s_div']) ) {
    $div = $_SESSION['s_div'];
} else {
    $div = '';
}
if ( isset($_SESSION['s_d_start']) ) {
    $d_start = $_SESSION['s_d_start'];
} else {
    if ( isset($_POST['d_start']) ) {
        $d_start = $_POST['d_start'];
    } else {
        $d_start = date_offset(1);
    }
}
if ( isset($_SESSION['s_d_end']) ) {
    $d_end = $_SESSION['s_d_end'];
} else {
    if ( isset($_POST['d_end']) ) {
        $d_end = $_POST['d_end'];
    } else {
        $d_end = date_offset(1);
    }
}
if ( isset($_SESSION['s_kubun']) ) {
    // $kubun = $_SESSION['s_kubun'];
    $kubun = '1';
} else {
    $kubun = '1';
}
if ( isset($_SESSION['uri_assy_no']) ) {
    $assy_no = $_SESSION['uri_assy_no'];
} else {
    $assy_no = '';      // �����
}

/////////// HTML Header ����Ϥ��ƥ���å��������
$menu->out_html_header();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<title><?php echo $menu->out_title() ?></title>
<?php if ($_SESSION['s_sysmsg'] == '') echo $menu->out_site_java() ?>
<?php echo $menu->out_css() ?>

<!--    �ե��������ξ�� -->
<script language='JavaScript' src='./materialCost_sales_comp.js?<?php echo $uniq ?>'>
</script>

<script language="JavaScript">
<!--
/* ������ϥե�����Υ�����Ȥ˥ե������������� */
function set_focus(){
//    document.form_name.element_name.focus();      // ������ϥե����ब������ϥ����Ȥ򳰤�
//    document.form_name.element_name.select();
}
// -->
</script>

<!-- �������륷���ȤΥե��������򥳥��� HTML���� �����Ȥ�����Ҥ˽���ʤ��������
<link rel='stylesheet' href='<?php echo MENU_FORM . '?' . $uniq ?>' type='text/css' media='screen'>
-->

<style type="text/css">
<!--
.pt8 {
    font-size:      8pt;
    font-weight:    normal;
    font-family:    monospace;
}
.pt9 {
    font-size:      9pt;
    font-weight:    normal;
    font-family:    monospace;
}
.pt10 {
    font-size:      10pt;
    font-weight:    normal;
    font-family:    monospace;
}
.pt10b {
    font-size:      10pt;
    font-weight:    bold;
    font-family:    monospace;
}
.pt11b {
    font-size:      11pt;
    font-weight:    bold;
    font-family:    monospace;
}
.pt12b {
    font-size:      12pt;
    font-weight:    bold;
    font-family:    monospace;
}
.pt12b-dred {
    font-size:      12pt;
    font-weight:    bold;
    font-family:    monospace;
    color:          darkred;
}
.caption_font {
    font-size:          11pt;
    font-weight:        bold;
    font-family:        monospace;
    background-color:   blue;
    color:              white;
    border-style: solid;
    border-width: 1px;
    border-top-color:       #FFFFFF;
    border-left-color:      #FFFFFF;
    border-right-color:     #bdaa90;
    border-bottom-color:    #bdaa90;
}
.caption2_font {
    font-size:          11pt;
    font-weight:        bold;
    font-family:        monospace;
    background-color:   yellow;
    color:              blue;
    border-style: solid;
    border-width: 1px;
    border-top-color:       #FFFFFF;
    border-left-color:      #FFFFFF;
    border-right-color:     #bdaa90;
    border-bottom-color:    #bdaa90;
}
.note1_font {
    font-size:          10pt;
    font-weight:        bold;
    background-color:   yellow;
    color:              blue;
    border-style: solid;
    border-width: 1px;
    border-top-color:       #FFFFFF;
    border-left-color:      #FFFFFF;
    border-right-color:     #bdaa90;
    border-bottom-color:    #bdaa90;
}
.note2_font {
    font-size:          10pt;
    font-weight:        bold;
    background-color:   blue;
    color:              white;
    border-style: solid;
    border-width: 1px;
    border-top-color:       #FFFFFF;
    border-left-color:      #FFFFFF;
    border-right-color:     #bdaa90;
    border-bottom-color:    #bdaa90;
}
.margin0 {
    margin:0%;
}
td {
    font-size: 10pt;
}
.winbox {
    border-style: solid;
    border-width: 1px;
    border-top-color:       #FFFFFF;
    border-left-color:      #FFFFFF;
    border-right-color:     #bdaa90;
    border-bottom-color:    #bdaa90;
    background-color:#d6d3ce;
}
.winbox_field {
    border-style: solid;
    border-width: 1px;
    border-top-color:       #bdaa90;
    border-left-color:      #bdaa90;
    border-right-color:     #FFFFFF;
    border-bottom-color:    #FFFFFF;
    background-color:#d6d3ce;
}
-->
</style>
</head>
<body class='margin0' onLoad='document.uri_form.div.focus(); //document.uri_form.div.select()'>
    <center>
<?php echo $menu->out_title_border() ?>
        <form name='uri_form' action='<?php echo $menu->out_self() ?>' method='post' onSubmit='return chk_sales_form(this)'>
            <!----------------- ������ ��ʸ��ɽ������ ------------------->
            <table bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='5'>
                <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
            <table class='winbox_field' width='100%' border='1' cellspacing='0' cellpadding='3'>
                <tr>
                        <!--  bgcolor='#ffffc6' �������� --> 
                    <td colspan='2' align='center' class='caption_font'>
                        <?php echo $menu->out_caption() . "\n" ?>
                    </td>
                </tr>
                <!--------------------------------------------------------------
                <tr>
                    <td class='winbox' align='right'>
                        �ѥ���ɤ�����Ʋ�����
                    </td>
                    <td class='winbox' align='center'>
                        <input type='password' name='uri_passwd' size='16' value='$uri_passwd' maxlength='8'>
                    </td>
                </tr>
                --------------------------------------------------------------->
                <tr>
                    <td class='winbox' align='right'>
                        ��������򤷤Ʋ�����
                    </td>
                    <td class='winbox' align='center'>
                        <select name='div' class='pt12b'>
                            <option value=' '<?php if($div==' ') echo('selected'); ?>>�����롼��</option>
                            <option value='C'<?php if($div=='C') echo('selected'); ?>>���ץ�����</option>
                            <option value='H'<?php if($div=='H') echo('selected'); ?>>���ץ�ɸ��</option>
                            <option value='S'<?php if($div=='S') echo('selected'); ?>>���ץ�����</option>
                            <option value='L'<?php if($div=='L') echo('selected'); ?>>��˥�����</option>
                            <option value='M'<?php if($div=='M') echo('selected'); ?>>��˥�ɸ��</option>
                            <option value='B'<?php if($div=='B') echo('selected'); ?>>���Υݥ��</option>
                            <option value='T'<?php if($div=='T') echo('selected'); ?>>�ġ���</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='right'>
                        ���դ���ꤷ�Ʋ�����(ɬ��)
                    </td>
                    <td class='winbox' align='center'>
                        <input type='text' name='d_start' class='pt12b' size='8' value='<?php echo($d_start); ?>' maxlength='8'>
                        ��
                        <input type='text' name='d_end' class='pt12b' size='8' value='<?php echo($d_end); ?>' maxlength='8'>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='right'>
                        �����ֹ�λ���
                        (���ꤷ�ʤ����϶���)
                    </td>
                    <td class='winbox' align='center'>
                        <input type='text' name='assy_no' size='9' class='pt12b' value='<?php echo $assy_no ?>' maxlength='9'>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='right' width='400'>
                        ����ʬ=
                        �������� ��������(�̾�) �������� ����Ĵ�� ������ư ����ľǼ ������� 
                        ������ư���� �������ʼ���
                    </td>
                    <td class='winbox' align='center'>
                        <select name='kubun'>
                            <!-- <option value=' '<?php if($kubun==' ') echo('selected'); ?>>����</option> -->
                            <option value='1'<?php if($kubun=='1') echo('selected'); ?>>1����</option>
                            <!-- <option value='2'<?php if($kubun=='2') echo('selected'); ?>>2����</option>
                            <option value='3'<?php if($kubun=='3') echo('selected'); ?>>3����</option>
                            <option value='4'<?php if($kubun=='4') echo('selected'); ?>>4Ĵ��</option>
                            <option value='5'<?php if($kubun=='5') echo('selected'); ?>>5��ư</option>
                            <option value='6'<?php if($kubun=='6') echo('selected'); ?>>6ľǼ</option>
                            <option value='7'<?php if($kubun=='7') echo('selected'); ?>>7���</option>
                            <option value='8'<?php if($kubun=='8') echo('selected'); ?>>8����</option>
                            <option value='9'<?php if($kubun=='9') echo('selected'); ?>>9����</option> -->
                        <select>
                    </td>
                </tr>
        <?php if (!isset($_POST['exec'])) { ?>
                <tr>
                    <td class='winbox' colspan='2' align='center'>
                        <input type='submit' name='exec' value='�¹�' >
                    </td>
                </tr>
        <?php } else { ?>
                <tr>
                    <td class='winbox' colspan='2' align='right'>
                        <input type='submit' name='exec' value='�¹�' >����
                        <input type='submit' name='ret_ok' value='���'>����
                        <span class='pt12b'>
                        ������<?php echo number_format($res[0]['����']) ?>���
                        <?php if ($res[0]['̤��Ͽ'] <= 0) { ?>
                        ��������̤��Ͽ��<?php echo number_format($res[0]['̤��Ͽ']) ?>��
                        <?php } else { ?>
                        <span style='color:red;'>��������̤��Ͽ��<?php echo number_format($res[0]['̤��Ͽ']) ?>��</span>
                        <?php } ?>
                        </span>
                    </td>
                </tr>
        <?php } ?>
            </table>
                </td></tr>
            </table> <!----------------- ���ߡ�End ------------------>
        </form>
        <?php if (isset($_POST['exec'])) { ?>
        
        <br>
        
        <form name='query_form1' action='<?php echo $menu->out_self() ?>' method='post'>
            <!----------------- ������ ��ʸ��ɽ������ ------------------->
            <table bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='3'>
                <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
            <table class='winbox_field' width='100%' border='1' cellspacing='0' cellpadding='3'>
                <tr>
                    <td nowrap align='center' class='note1_font'>������Ψ����</td>
                    <td width='110' align='center' class='caption_font'>����</td>
                    <td width='110' align='center' class='caption_font'>������(����)</td>
                    <td width='110' align='center' class='caption_font'>��Ω��(����)</td>
                    <td width='110' align='center' class='caption_font'>(�����¤)</td>
                    <td width='110' align='center' class='caption_font'>(�����Ω)</td>
                    <td width='110' align='center' class='caption_font'>�������</td>
                </tr>
                <tr>
                    <td align='center' class='caption_font'>�⡡��</td>
                    <td class='winbox' align='right'><span class='pt12b'><?php echo number_format($res[0]['����'], 0) ?></span></td>
                    <td class='winbox' align='right'><span class='pt12b'><?php echo number_format($res[0]['��������'], 0) ?></span></td>
                    <td class='winbox' align='right'><span class='pt12b'><?php echo number_format($res[0]['��Ω����'], 0) ?></span></td>
                    <td class='winbox' align='right'><span class='pt12b'>��<?php echo number_format($res[0]['�����¤'], 0) ?>��</span></td>
                    <td class='winbox' align='right'><span class='pt12b'>��<?php echo number_format($res[0]['�����Ω'], 0) ?>��</span></td>
                    <td class='winbox' align='right'><span class='pt12b'><?php echo number_format($res[0]['�������'], 0) ?></span></td>
                </tr>
                <tr>
                    <td align='center' class='caption_font'>������</td>
                    <td class='winbox' align='right'><span class='pt12b'><?php echo number_format($sum_percent_zai, 2) ?>%</span></td>
                    <td class='winbox' align='right'><span class='pt12b'><?php echo number_format($ext_bu_percent, 2) ?>%</span></td>
                    <td class='winbox' align='right'><span class='pt12b'><?php echo number_format($extkumi_percent, 2) ?>%</span></td>
                    <td class='winbox' align='right'><span class='pt12b'>��<?php echo number_format($int_seizou, 2) ?>%��</span></td>
                    <td class='winbox' align='right'><span class='pt12b'>��<?php echo number_format($int_assy, 2) ?>%��</span></td>
                    <td class='winbox' align='right'><span class='pt12b'><?php echo number_format($sum_percent, 2) ?>%</span></td>
                </tr>
                <tr>
                    <td align='center' class='caption_font'>�������</td>
                    <td class='winbox' align='right'><span class='pt12b'><?php echo number_format($sum_percent, 2) ?>%</span></td>
                    <td class='winbox' align='right'><span class='pt12b'><?php echo number_format($ext_bu_percent_zai, 2) ?>%</span></td>
                    <td class='winbox' align='right'><span class='pt12b'><?php echo number_format($extkumi_percent_zai, 2) ?>%</span></td>
                    <td class='winbox' align='right'><span class='pt12b'>��<?php echo number_format($int_seizou_zai, 2) ?>%��</span></td>
                    <td class='winbox' align='right'><span class='pt12b'>��<?php echo number_format($int_assy_zai, 2) ?>%��</span></td>
                    <td class='winbox' align='right'><span class='pt12b'><?php echo number_format($sum_percent_zai, 2) ?>%</span></td>
                </tr>
                <tr>
                    <td align='center' class='caption_font'>���������</td>
                    <td class='winbox' align='center'>---</td>
                    <td class='winbox' align='right' colspan='2'><span class='pt12b-dred'><?php echo number_format($res[0]['������'], 0) ?></span></td>
                    <td class='winbox' align='right' colspan='2'><span class='pt12b'><?php echo number_format($res[0]['�����'], 0) ?></span></td>
                    <td class='winbox' align='right'><span class='pt12b'><?php echo number_format($res[0]['�������'], 0) ?></span></td>
                </tr>
                <tr>
                    <td align='center' class='caption_font'>������</td>
                    <td class='winbox' align='center'>---</td>
                    <td class='winbox' align='right' colspan='2'><span class='pt12b'><?php echo number_format($ext_percent, 2) ?>%</span></td>
                    <td class='winbox' align='right' colspan='2'><span class='pt12b'><?php echo number_format($int_percent, 2) ?>%</span></td>
                    <td class='winbox' align='right'><span class='pt12b'>---</span></td>
                </tr>
                <tr>
                    <td align='center' class='caption_font'>�������</td>
                    <td class='winbox' align='center'>---</td>
                    <td class='winbox' align='right' colspan='2'><span class='pt12b'><?php echo number_format($ext_percent_zai, 2) ?>%</span></td>
                    <td class='winbox' align='right' colspan='2'><span class='pt12b'><?php echo number_format($int_percent_zai, 2) ?>%</span></td>
                    <td class='winbox' align='right'><span class='pt12b'>---</span></td>
                </tr>
            </table>
                </td></tr>
            </table> <!----------------- ���ߡ�End ------------------>
        </form>
        
        <br>
        
        <form name='query_form2' action='<?php echo $menu->out_self() ?>' method='post'>
            <!----------------- ������ ��ʸ��ɽ������ ------------------->
            <table bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='3'>
                <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
            <table class='winbox_field' width='100%' border='1' cellspacing='0' cellpadding='3'>
                <tr>
                    <td nowrap align='center' class='note2_font'>ɸ����Ψ����</td>
                    <td width='110' align='center' class='caption2_font'>����</td>
                    <td width='110' align='center' class='caption2_font'>������(����)</td>
                    <td width='110' align='center' class='caption2_font'>��Ω��(����)</td>
                    <td width='110' align='center' class='caption2_font'>(�����¤)</td>
                    <td width='110' align='center' class='caption2_font'>(�����Ω)</td>
                    <td width='110' align='center' class='caption2_font'>�������</td>
                </tr>
                <tr>
                    <td align='center' class='caption2_font'>�⡡��</td>
                    <td class='winbox' align='right'><span class='pt12b'><?php echo number_format($res[0]['����'], 0) ?></span></td>
                    <td class='winbox' align='right'><span class='pt12b'><?php echo number_format($res[0]['��������'], 0) ?></span></td>
                    <td class='winbox' align='right'><span class='pt12b'><?php echo number_format($res[0]['��Ω����'], 0) ?></span></td>
                    <td class='winbox' align='right'><span class='pt12b'>��<?php echo number_format($res[0]['�����¤'], 0) ?>��</span></td>
                    <td class='winbox' align='right'><span class='pt12b'>��<?php echo number_format($res[0]['�����Ω2'], 0) ?>��</span></td>
                    <td class='winbox' align='right'><span class='pt12b'><?php echo number_format($res[0]['�������2'], 0) ?></span></td>
                </tr>
                <tr>
                    <td align='center' class='caption2_font'>������</td>
                    <td class='winbox' align='right'><span class='pt12b'><?php echo number_format($sum_percent2_zai, 2) ?>%</span></td>
                    <td class='winbox' align='right'><span class='pt12b'><?php echo number_format($ext_bu_percent, 2) ?>%</span></td>
                    <td class='winbox' align='right'><span class='pt12b'><?php echo number_format($extkumi_percent, 2) ?>%</span></td>
                    <td class='winbox' align='right'><span class='pt12b'>��<?php echo number_format($int_seizou, 2) ?>%��</span></td>
                    <td class='winbox' align='right'><span class='pt12b'>��<?php echo number_format($int_assy2, 2) ?>%��</span></td>
                    <td class='winbox' align='right'><span class='pt12b'><?php echo number_format($sum_percent2, 2) ?>%</span></td>
                </tr>
                <tr>
                    <td align='center' class='caption2_font'>�������</td>
                    <td class='winbox' align='right'><span class='pt12b'><?php echo number_format($sum_percent2, 2) ?>%</span></td>
                    <td class='winbox' align='right'><span class='pt12b'><?php echo number_format($ext_bu_percent2_zai, 2) ?>%</span></td>
                    <td class='winbox' align='right'><span class='pt12b'><?php echo number_format($extkumi_percent2_zai, 2) ?>%</span></td>
                    <td class='winbox' align='right'><span class='pt12b'>��<?php echo number_format($int_seizou2_zai, 2) ?>%��</span></td>
                    <td class='winbox' align='right'><span class='pt12b'>��<?php echo number_format($int_assy2_zai, 2) ?>%��</span></td>
                    <td class='winbox' align='right'><span class='pt12b'><?php echo number_format($sum_percent2_zai, 2) ?>%</span></td>
                </tr>
                <tr>
                    <td align='center' class='caption2_font'>���������</td>
                    <td class='winbox' align='center'>---</td>
                    <td class='winbox' align='right' colspan='2'><span class='pt12b-dred'><?php echo number_format($res[0]['������'], 0) ?></span></td>
                    <td class='winbox' align='right' colspan='2'><span class='pt12b'><?php echo number_format($res[0]['�����2'], 0) ?></span></td>
                    <td class='winbox' align='right'><span class='pt12b'><?php echo number_format($res[0]['�������2'], 0) ?></span></td>
                </tr>
                <tr>
                    <td align='center' class='caption2_font'>������</td>
                    <td class='winbox' align='center'>---</td>
                    <td class='winbox' align='right' colspan='2'><span class='pt12b'><?php echo number_format($ext_percent, 2) ?>%</span></td>
                    <td class='winbox' align='right' colspan='2'><span class='pt12b'><?php echo number_format($int_percent2, 2) ?>%</span></td>
                    <td class='winbox' align='right'><span class='pt12b'>---</span></td>
                </tr>
                <tr>
                    <td align='center' class='caption2_font'>�������</td>
                    <td class='winbox' align='center'>---</td>
                    <td class='winbox' align='right' colspan='2'><span class='pt12b'><?php echo number_format($ext_percent2_zai, 2) ?>%</span></td>
                    <td class='winbox' align='right' colspan='2'><span class='pt12b'><?php echo number_format($int_percent2_zai, 2) ?>%</span></td>
                    <td class='winbox' align='right'><span class='pt12b'>---</span></td>
                </tr>
            </table>
                </td></tr>
            </table> <!----------------- ���ߡ�End ------------------>
        </form>
        <?php } ?>
    </center>
</body>
</html>
<?php
echo $menu->out_alert_java();
ob_end_flush();                 // ���ϥХåե���gzip���� END
?>
