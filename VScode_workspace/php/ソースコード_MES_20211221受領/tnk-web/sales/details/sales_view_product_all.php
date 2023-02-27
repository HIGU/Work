<?php
//////////////////////////////////////////////////////////////////////////////
// ��� ���� �Ȳ� ������  new version   sales_view_product.php              //
// Copyright (C) 2010 - 2015 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp  //
// Changed history                                                          //
// 2010/12/14 Created   sales_view_product_all.php                          //
// 2011/01/20 ���դμ����Ϥ������Զ�����������                          //
// 2011/05/16 ɽ�������������Ȥ������                                    //
// 2011/05/26 �٤������顼��ȯ�����Ƥ����١�����                            //
// 2011/05/31 ���롼�ץ������ѹ���ȼ��SQLʸ���ѹ�                           //
// 2014/05/23 ����ɽ�����ɲ�(Ȭ������Ĺ����)                           ��ë //
// 2015/03/06 ���������̤ξȲ���б�(���ʥ��롼����ǰ㤤�������)        //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
// ini_set('zend.ze1_compatibility_mode', '1');    // zend 1.X ����ѥ� php4�θߴ��⡼��
// ini_set('implicit_flush', 'off');           // echo print �� flush �����ʤ�(�٤��ʤ뤿��) CLI CGI��
// ini_set('max_execution_time', 1200);        // ����¹Ի���=20ʬ CLI CGI��
ob_start('ob_gzhandler');                   // ���ϥХåե���gzip����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��

require_once ('../../function.php');            // define.php �� pgsql.php �� require_once ���Ƥ���
require_once ('../../tnk_func.php');            // TNK �˰�¸������ʬ�δؿ��� require_once ���Ƥ���
require_once ('../../MenuHeader.php');          // TNK ������ menu class
require_once ('../../ControllerHTTP_Class.php');// TNK ������ MVC Controller Class
//////////// ���å����Υ��󥹥��󥹤���Ͽ
$session = new Session();
if (isset($_REQUEST['recNo'])) {
    $session->add_local('recNo', $_REQUEST['recNo']);
    exit();
}
access_log();                               // Script Name �ϼ�ư����

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader(0);                  // ǧ�ڥ����å�0=���̰ʾ� �����=TOP_MENU �����ȥ�̤����

$result  = new Result;

////////////// ����������
//$menu->set_site( 1, 11);                    // site_index=01(����˥塼) site_id=11(����������)
////////////// �꥿���󥢥ɥ쥹����
// $menu->set_RetUrl(SYS_MENU);                // �̾�ϻ��ꤹ��ɬ�פϤʤ�
//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title('���ʥ��롼���� ���Ȳ�');
//////////// �ƽ����action̾�ȥ��ɥ쥹����
$menu->set_action('�������Ȳ�',   INDUST . 'material/materialCost_view.php');
$menu->set_action('ñ����Ͽ�Ȳ�',   INDUST . 'parts/parts_cost_view.php');
$menu->set_action('�����������',   INDUST . 'material/materialCost_view_assy.php');
$menu->set_action('�������',   SALES . 'details/sales_view_product.php');

//////////// �֥饦�����Υ���å����к���
$uniq = $menu->set_useNotCache('target');

/////////////// �����Ϥ��ѿ��ν����
if ( isset($_SESSION['s_uri_passwd']) ) {
    $_REQUEST['uri_passwd'] = $_SESSION['s_uri_passwd'];
} else {
    $uri_passwd = '';
}
if ( isset($_SESSION['s_d_start']) ) {
    if ( !isset($_REQUEST['d_start']) ) {
        $_REQUEST['d_start'] = $_SESSION['s_d_start'];
    }
} else {
    if ( isset($_POST['d_start']) ) {
        $d_start = $_POST['d_start'];
    } else {
        $d_start = date_offset(1);
    }
}
if ( isset($_SESSION['s_d_end']) ) {
    if ( !isset($_REQUEST['d_end']) ) {
        $_REQUEST['d_end'] = $_SESSION['s_d_end'];
    }
} else {
    if ( isset($_POST['d_end']) ) {
        $d_end = $_POST['d_end'];
    } else {
        $d_end = date_offset(1);
    }
}
if ( isset($_SESSION['s_kubun']) ) {
    if ( !isset($_REQUEST['kubun']) ) {
        $_REQUEST['kubun'] = $_SESSION['s_kubun'];
    }
} else {
    if ( isset($_POST['kubun']) ) {
        $kubun = $_POST['kubun'];
    } else {
        $kubun = '';
    }
}
if ( isset($_SESSION['s_div']) ) {
    if ( !isset($_REQUEST['div']) ) {
        $_REQUEST['div'] = $_SESSION['s_div'];
    }
} else {
    if ( isset($_POST['div']) ) {
        $div = $_POST['div'];
    } else {
        $div = '';
    }
}
if ( isset($_SESSION['s_divg']) ) {
    if ( !isset($_REQUEST['divg']) ) {
        $_REQUEST['divg'] = $_SESSION['s_divg'];
    }
} else {
    if ( isset($_POST['divg']) ) {
        $divg = $_POST['divg'];
    } else {
        $divg = '';
    }
}
//////////// �����Υ��å����ǡ�����¸   ���ǡ����Ǥ�ڤ����뤿��
//if (! (isset($_REQUEST['forward']) || isset($_REQUEST['backward']) || isset($_REQUEST['page_keep'])) ) {
    $session->add_local('recNo', '-1');         // 0�쥳���ɤǥޡ�����ɽ�����Ƥ��ޤ�������б�
    $_SESSION['s_uri_passwd'] = $_REQUEST['uri_passwd'];
    $_SESSION['s_div']        = $_REQUEST['div'];
    $_SESSION['s_d_start']    = $_REQUEST['d_start'];
    $_SESSION['s_d_end']      = $_REQUEST['d_end'];
    $_SESSION['s_kubun']      = $_REQUEST['kubun'];
    $uri_passwd = $_SESSION['s_uri_passwd'];
    $div        = $_SESSION['s_div'];
    $d_start    = $_SESSION['s_d_start'];
    $d_end      = $_SESSION['s_d_end'];
    $kubun      = $_SESSION['s_kubun'];
        ///// day �Υ����å�
        if (substr($d_start, 6, 2) < 1) $d_start = substr($d_start, 0, 6) . '01';
        ///// �ǽ���������å����ƥ��åȤ���
        if (!checkdate(substr($d_start, 4, 2), substr($d_start, 6, 2), substr($d_start, 0, 4))) {
            $d_start = ( substr($d_start, 0, 6) . last_day(substr($d_start, 0, 4), substr($d_start, 4, 2)) );
            if (!checkdate(substr($d_start, 4, 2), substr($d_start, 6, 2), substr($d_start, 0, 4))) {
                $_SESSION['s_sysmsg'] = '���դλ��꤬�����Ǥ�z��';
                header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // ľ���θƽи������
                exit();
            }
        }
        ///// day �Υ����å�
        if (substr($d_end, 6, 2) < 1) $d_end = substr($d_end, 0, 6) . '01';
        ///// �ǽ���������å����ƥ��åȤ���
        if (!checkdate(substr($d_end, 4, 2), substr($d_end, 6, 2), substr($d_end, 0, 4))) {
            $d_end = ( substr($d_end, 0, 6) . last_day(substr($d_end, 0, 4), substr($d_end, 4, 2)) );
            if (!checkdate(substr($d_start, 4, 2), substr($d_start, 6, 2), substr($d_start, 0, 4))) {
                $_SESSION['s_sysmsg'] = '���դλ��꤬�����Ǥ�z��';
                header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // ľ���θƽи������
                exit();
            }
        }
    $_SESSION['s_d_start'] = $d_start;
    $_SESSION['s_d_end']   = $d_end  ;
    
    ////////////// �ѥ���ɥ����å�
    if ($uri_passwd != date('Ymd')) {
        $_SESSION['s_sysmsg'] = "<font color='yellow'>�ѥ���ɤ��㤤�ޤ���</font>";
        header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // ľ���θƽи������
        exit();
    }
    //////////// SQL where ��� ���Ѥ���
    $search = "where �׾���>=$d_start and �׾���<=$d_end";
    if ($kubun == '1') {
        $search .= " and datatype='1'";
    }
    if ($div == 'S') {    // ������ʤ�
        $search .= " and ������='C' and note15 like 'SC%%'";
        $search .= " and (assyno not like 'NKB%%')";
        $search .= " and (assyno not like 'SS%%')";
        //$search .= " and CASE WHEN �׾���<20130501 THEN groupm.support_group_code IS NULL ELSE ������='C' END";
        //$search .= " and groupm.support_group_code IS NULL";
    } elseif ($div == 'D') {    // ��ɸ��ʤ�
        $search .= " and ������='C' and (note15 NOT like 'SC%%')";    // ��������ɸ��ؤ���
        $search .= " and (assyno not like 'NKB%%')";
        $search .= " and (assyno not like 'SS%%')";
        //$search .= " and (CASE WHEN �׾���<20130501 THEN groupm.support_group_code IS NULL ELSE ������='C' END)";
        //$search .= " and groupm.support_group_code IS NULL";
    } elseif ($div == "_") {    // �������ʤ�
        $search .= " and ������=' '";
    } elseif ($div == "C") {
        $search .= " and ������='$div'";
        $search .= " and (assyno not like 'NKB%%')";
        $search .= " and (assyno not like 'SS%%')";
    } elseif ($div == "L") {
        $search .= " and ������='$div'";
        $search .= " and (assyno not like 'SS%%')";
    } elseif ($div != " ") {
        $search .= " and ������='$div'";
    }
    $_SESSION['sales_search'] = $search;        // SQL��where�����¸
//}

$uri_passwd = $_SESSION['s_uri_passwd'];
$div        = $_SESSION['s_div'];
$d_start    = $_SESSION['s_d_start'];
$d_end      = $_SESSION['s_d_end'];
$kubun      = $_SESSION['s_kubun'];
$search     = $_SESSION['sales_search'];

///////////// ��ʬ���ۡ�����������
$query_k = sprintf("select
                        sum(Uround(����*ñ��,0)) as ���,       -- 0
                        pts.top_no as ��ʬ��̾,                 -- 1
                        sum(����) as ����                       -- 2
                  from
                        hiuuri
                  left outer join
                        assembly_schedule as a
                  on �ײ��ֹ�=plan_no
                  left outer join
                        mshmas as p
                  on assyno=p.mipn
                  left outer join
                        -- mshgnm as gnm
                        msshg3 as gnm
                  -- on p.mhjcd=gnm.mhgcd
                  on p.mhshc=gnm.mhgcd
                  left outer join
                        product_serchGroup as psc
                  on gnm.mhggp=psc.group_no
                  left outer join
                        product_top_serchgroup as pts
                  on psc.top_code=pts.top_no
                  %s
                  group by pts.top_no
                  order by pts.top_no
                  ", $search);   // ���� $search �Ǹ���
$res_k   = array();
$field = array();
if (($rows_k = getResultWithField3($query_k, $field, $res_k)) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("<font color='yellow'>������٤Υǡ���������ޤ���<br>%s��%s</font>", format_date($d_start), format_date($d_end) );
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // ľ���θƽи������
    exit();
} else {
    $num = count($field);       // �ե�����ɿ�����
}
///////////// �Ȳ����¤��ؤ�
$query_o = sprintf("select
                        top_no as ��ʬ��No,                  -- 0
                        top_name as ��ʬ��̾,                -- 1
                        s_order as �ȹ��                    -- 2
                  from
                        product_top_serchgroup
                  order by s_order
                  ");   
$res_o   = array();
$field_o = array();
if (($rows_o = getResultWithField3($query_o, $field_o, $res_o)) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("<font color='yellow'>�ȹ���ʬ�ब��Ͽ����Ƥ��ޤ���");
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // ľ���θƽи������
    exit();
} else {
    $num_o = count($field_o);       // �ե�����ɿ�����
    $data_top_t  = 0;
    $data_top_nt = 0;
    $view_data = array();
    for ($i=0; $i<$rows_o; $i++) {
        $data_top[$i][0] = '';
        $data_top[$i][1] =  0;
        $data_top[$i][2] = '';
        $data_top[$i][3] =  0;
        for ($r=0; $r<$rows_k; $r++) {
            if ($res_o[$i][0] == $res_k[$r][1]) {
                $data_top[$i][0] = $res_o[$i][1];
                $data_top[$i][1] = $res_k[$r][0];
                $data_top[$i][2] = $res_k[$r][1];
                $data_top[$i][3] = $res_k[$r][2];
                $data_top_t      += $res_k[$r][0];
                $data_top_nt     += $res_k[$r][2];
            }
        }
    }
}

function get_middle_data($top_code, $search_middle, $result, $data_middle_t, $data_middle_nt) {
    $search_middle .= " and psc.top_code='$top_code'";
    $query_m = sprintf("select
                        sum(Uround(����*ñ��,0)) as ���,       -- 0
                        psc.group_no as ��ʬ��No,               -- 1
                        sum(����) as ����                       -- 2
                  from
                        hiuuri
                  left outer join
                        assembly_schedule as a
                  on �ײ��ֹ�=plan_no
                  left outer join
                        mshmas as p
                  on assyno=p.mipn
                  left outer join
                        -- mshgnm as gnm
                        msshg3 as gnm
                  -- on p.mhjcd=gnm.mhgcd
                  on p.mhshc=gnm.mhgcd
                  left outer join
                        product_serchGroup as psc
                  on gnm.mhggp=psc.group_no
                  %s
                  group by psc.group_no
                  order by psc.group_no
                  ", $search_middle);   // ���� $search �Ǹ���
    $field_m = array();
    if (($rows_m = getResultWithField3($query_m, $field_m, $res_m)) <= 0) {
        //$_SESSION['s_sysmsg'] .= sprintf("<font color='yellow'>��ʬ�ब��Ͽ����Ƥ��ޤ���</font>");
        //header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // ľ���θƽи������
        //exit();
    } else {
        $num_m = count($res_m);       // �ǡ���������
        for ($r=0; $r<$rows_m; $r++) {
            $group_no = $res_m[$r][1];
            $search_c = "where group_no='$group_no'";
            $query_c = sprintf("select
                            group_name as ��ʬ��̾                  -- 0
                    from
                            product_serchGroup
                    %s
                    LIMIT 1
                    ",  $search_c);   
            $res_c   = array();
            $field_c = array();
            if (($rows_c = getResultWithField3($query_c, $field_c, $res_c)) <= 0) {
                $group_name[$r] = '';
            } else {
                $group_name[$r] = $res_c[0][0];
            }
        }
        $data_middle_sum = 0;
        $data_middle_num = 0;
        for ($r=0; $r<$rows_m; $r++) {
            $res_m[$r][3]     = $group_name[$r];
            $data_middle_sum += $res_m[$r][0];
            $data_middle_num += $res_m[$r][2];
        }
        $data_middle_t  += $data_middle_sum;
        $data_middle_nt += $data_middle_num;
        $result->add_array2('data_middle', $res_m);
        $result->add('num_m', $num_m);
        $result->add('data_middle_sum', $data_middle_sum);
        $result->add('data_middle_t', $data_middle_t);
        $result->add('data_middle_num', $data_middle_num);
        $result->add('data_middle_nt', $data_middle_nt);
    }
}
//////////// ɽ�������
$ft_kingaku = number_format($data_top_t);                    // ���头�ȤΥ���ޤ��ղ�
$ft_suryo   = number_format($data_top_nt);                   // ���头�ȤΥ���ޤ��ղ�
//$ft_ken     = number_format($t_ken);
//$ft_kazu    = number_format($t_kazu);
$f_d_start  = format_date($d_start);                        // ���դ� / �ǥե����ޥå�
$f_d_end    = format_date($d_end);
$menu->set_caption("�о�ǯ�� {$f_d_start}��{$f_d_end}����׶��={$ft_kingaku}����׿���={$ft_suryo}");
//$menu->set_caption("�о�ǯ�� {$f_d_start}��{$f_d_end}����׷��={$ft_ken}����׶��={$ft_kingaku}����׿���={$ft_kazu}<u>");
/////////// HTML Header ����Ϥ��ƥ���å��������
$menu->out_html_header();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<title><?php echo $menu->out_title() ?></title>
<?php echo $menu->out_site_java()?>
<?php echo $menu->out_css()?>
<?php echo $menu->out_jsBaseClass() ?>

<script type='text/javascript' language='JavaScript'>
<!--
/* ������ϥե�����Υ�����Ȥ˥ե������������� */
function set_focus(){
    // document.body.focus();                          // F2/F12��������뤿����б�
    // document.form_name.element_name.select();
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
    font-family:    monospace;
}
.pt9 {
    font-size:      9pt;
    font-family:    monospace;
}
.pt9b {
    font-size:      9pt;
    font-weight:    bold;
    font-family:    monospace;
}
.pt10 {
    font-size:l     10pt;
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
th {
    background-color:   yellow;
    color:              blue;
    font-size:          10pt;
    font-weight:        bold;
    font-family:        monospace;
}
.winbox {
    border-style: solid;
    border-width: 1px;
    border-top-color:       #FFFFFF;
    border-left-color:      #FFFFFF;
    border-right-color:     #bdaa90;
    border-bottom-color:    #bdaa90;
    /* background-color:#d6d3ce; */
}
.winboxb {
    border-style: solid;
    border-width: 1px;
    border-top-color:       #FFFFFF;
    border-left-color:      #FFFFFF;
    border-right-color:     #bdaa90;
    border-bottom-color:    #bdaa90;
    background-color:       #ccffff;
}
.winboxg {
    border-style: solid;
    border-width: 1px;
    border-top-color:       #FFFFFF;
    border-left-color:      #FFFFFF;
    border-right-color:     #bdaa90;
    border-bottom-color:    #bdaa90;
    background-color:       #ccffcc;
}
.winboxy {
    border-style: solid;
    border-width: 1px;
    border-top-color:       #FFFFFF;
    border-left-color:      #FFFFFF;
    border-right-color:     #bdaa90;
    border-bottom-color:    #bdaa90;
    background-color:       yellow;
}
.winbox_field {
    border-style: solid;
    border-width: 1px;
    border-top-color:       #bdaa90;
    border-left-color:      #bdaa90;
    border-right-color:     #FFFFFF;
    border-bottom-color:    #FFFFFF;
    /* background-color:#d6d3ce; */
}
a:hover {
    background-color:   blue;
    color:              white;
}
a {
    color:   blue;
}
body {
    background-image:url(<?php echo IMG ?>t_nitto_logo4.png);
    background-repeat:no-repeat;
    background-attachment:fixed;
    background-position:right bottom;
}
-->
</style>
</head>
    <center>
<?php echo $menu->out_title_border()?>
        <!----------------- ������ ���� ���� �Υե����� ---------------->
        <table width='100%' border='0' cellspacing='0' cellpadding='0'>
            <form name='page_form' method='post' action='<?php echo $menu->out_self() ?>'>
                <tr>
                    <td nowrap align='center' class='caption_font'>
                        <?php echo $menu->out_caption(), "\n" ?>
                    </td>
                </tr>
            </form>
        </table>
        
        <!--------------- ����������ʸ��ɽ��ɽ������ -------------------->
        <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
        <table class='winbox_field' width='100%' bgcolor='#FFFFFF' align='center' border='1' cellspacing='0' cellpadding='3'>
            <thead>
                <!-- �ơ��֥� �إå�����ɽ�� -->
                <tr>
                    <!--
                    <th class='winbox' nowrap width='10'>No.</th>        <!-- �ԥʥ�С���ɽ�� -->
                    <th class='winbox' nowrap><div class='pt11b'><?php echo $field[1] ?></div></th>
                    <th class='winbox' nowrap><div class='pt11b'><?php echo $field[2] ?></div></th>
                    <th class='winbox' nowrap><div class='pt11b'><?php echo $field[0] ?></div></th>
                    <th class='winbox' nowrap><div class='pt11b'>��ʬ��̾</div></th>
                    <th class='winbox' nowrap><div class='pt11b'>����</div></th>
                    <th class='winbox' nowrap><div class='pt11b'>���</div></th>
                </tr>
            </thead>
            <tfoot>
                <!-- ���ߤϥեå����ϲ���ʤ� -->
            </tfoot>
            <tbody>
                <?php
                $data_middle_t  = 0;
                $data_middle_nt = 0;
                for ($r=0; $r<$rows_o; $r++) {
                    $flg_gu = ' ';
                    $check_gu = $r % 2;
                    if ($check_gu == 0) {
                        $flg_gu = '1';
                    }
                    if($data_top[$r][1] != 0) {
                        get_middle_data($data_top[$r][2], $search, $result, $data_middle_t, $data_middle_nt);
                        $data_middle_t = $result->get('data_middle_t');
                        $data_middle_nt = $result->get('data_middle_nt');
                        $num_m           = $result->get('num_m');
                        $data_middle     = $result->get_array2('data_middle');
                        $data_middle_sum = $result->get('data_middle_sum');
                        $data_middle_num = $result->get('data_middle_num');
                        $num_m2      = $num_m + 1;
                        $assy_no = '';
                        echo "<tr>\n";
                        //echo "  <td rowspan = '" . $num_m2 . "' class='winbox' nowrap align='right'><div class='pt10b'>" . ($r + 1) . "</div></td>    <!-- �ԥʥ�С���ɽ�� -->\n";
                        if ($flg_gu == '1') {
                            echo "  <td rowspan = '" . $num_m2 . "' class='winboxb' nowrap align='left'><div class='pt11b'>" . $data_top[$r][0] . "</div></td>\n";
                            echo "  <td rowspan = '" . $num_m2 . "' class='winboxb' nowrap align='right'><div class='pt11b'>" . number_format($data_top[$r][3], 0) . "</div></td>\n";
                            echo "  <td rowspan = '" . $num_m2 . "' class='winboxb' nowrap align='right'><div class='pt11b'>" . number_format($data_top[$r][1], 0) . "</div></td>\n";
                        } else {
                            echo "  <td rowspan = '" . $num_m2 . "' class='winboxg' nowrap align='left'><div class='pt11b'>" . $data_top[$r][0] . "</div></td>\n";
                            echo "  <td rowspan = '" . $num_m2 . "' class='winboxg' nowrap align='right'><div class='pt11b'>" . number_format($data_top[$r][3], 0) . "</div></td>\n";
                            echo "  <td rowspan = '" . $num_m2 . "' class='winboxg' nowrap align='right'><div class='pt11b'>" . number_format($data_top[$r][1], 0) . "</div></td>\n";
                        }
                        echo "  <td class='winbox' nowrap align='left'><div class='pt9'>" . $data_middle[0][3] . "</div></td>\n";
                        echo "  <td class='winbox' nowrap align='right'><div class='pt9'>" . number_format($data_middle[0][2], 0) . "</div></td>\n";
                        echo "  <td class='winbox' nowrap align='right'><div class='pt9'>
                                <a class='pt9' href='JavaScript:baseJS.Ajax(\"{$menu->out_self()}\");location.replace(\"", $menu->out_action('�������'), "?uri_passwd={$uri_passwd}&d_start={$d_start}&d_end={$d_end}&kubun={$kubun}&div={$div}&divg={$data_middle[0][1]}&uri_ritu=52&sales_page=9999&assy_no={$assy_no}\")' target='application' style='text-decoration:none;'>"
                                . number_format($data_middle[0][0], 0) . "</div></td>\n";
                        echo "</tr>\n";
                        for ($i=1; $i<$num_m; $i++) {
                            echo "<tr>\n";
                            echo "  <td class='winbox' nowrap align='left'><div class='pt9'>" . $data_middle[$i][3] . "</div></td>\n";
                            echo "  <td class='winbox' nowrap align='right'><div class='pt9'>" . number_format($data_middle[$i][2], 0) . "</div></td>\n";
                            echo "  <td class='winbox' nowrap align='right'><div class='pt9'>
                                    <a class='pt9' href='JavaScript:baseJS.Ajax(\"{$menu->out_self()}\");location.replace(\"", $menu->out_action('�������'), "?uri_passwd={$uri_passwd}&d_start={$d_start}&d_end={$d_end}&kubun={$kubun}&div={$div}&divg={$data_middle[$i][1]}&uri_ritu=52&sales_page=9999&assy_no={$assy_no}\")' target='application' style='text-decoration:none;'>"
                                    . number_format($data_middle[$i][0], 0) . "</div></td>\n";
                            echo "</tr>\n";
                        }
                        echo "<tr>\n";
                        if ($flg_gu == '1') {
                            echo "  <td class='winboxb' nowrap align='left'><div class='pt9b'>����</div></td>\n";
                            echo "  <td class='winboxb' nowrap align='right'><div class='pt9b'>" . number_format($data_middle_num, 0) . "</div></td>\n";
                            echo "  <td class='winboxb' nowrap align='right'><div class='pt9b'>" . number_format($data_middle_sum, 0) . "</div></td>\n";
                        } else {
                            echo "  <td class='winboxg' nowrap align='left'><div class='pt9b'>����</div></td>\n";
                            echo "  <td class='winboxg' nowrap align='right'><div class='pt9b'>" . number_format($data_middle_num, 0) . "</div></td>\n";
                            echo "  <td class='winboxg' nowrap align='right'><div class='pt9b'>" . number_format($data_middle_sum, 0) . "</div></td>\n";
                        }
                        echo "</tr>\n";
                    }
                }
                ?>
            </tbody>
            <tr>
                <td class='winboxy' nowrap align='left'><div class='pt11b'>��ʬ���</div></td>
                <td class='winboxy' nowrap align='right'><div class='pt11b'><?php echo number_format($data_top_nt, 0) ?></div></td>
                <td class='winboxy' nowrap align='right'><div class='pt11b'><?php echo number_format($data_top_t, 0) ?></div></td>
                <td class='winboxy' nowrap align='left'><div class='pt11b'>��ʬ���</div></td>
                <td class='winboxy' nowrap align='right'><div class='pt11b'><?php echo number_format($data_middle_nt, 0) ?></div></td>
                <td class='winboxy' nowrap align='right'><div class='pt11b'><?php echo number_format($data_middle_t, 0) ?></div></td>
             </tr>
        </table>
            </td></tr>
        </table> <!----------------- ���ߡ�End ------------------>
    </center>
</body>
<?php echo $menu->out_alert_java()?>
</html>
<?php
// ob_end_flush();                 // ���ϥХåե���gzip���� END
?>
