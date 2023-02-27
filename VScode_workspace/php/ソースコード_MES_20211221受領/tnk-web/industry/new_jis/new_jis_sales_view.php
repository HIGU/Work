<?php
//////////////////////////////////////////////////////////////////////////////
// ��JIS�о����� �������ӾȲ� new_jis_sales_view.php                        //
// Copyright (C) 2014 - 2017 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp  //
// Changed history                                                          //
// 2014/12/01 Created   new_jis_sales_view.php                              //
// 2014/12/02 �ǥ��������Ĵ��                                              //
// 2014/12/08 ���ԡ���Ž�դ������ˡ��Ԥ������Τ���                      //
// 2014/12/22 �������������ѹ�                                              //
// 2017/04/27 �ƥ�˥塼��ɽ�����ؿ�JIS�٤���                      ��ë //
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
$menu->set_title('�о����� �������ӾȲ�');
//////////// �ƽ����action̾�ȥ��ɥ쥹����
$menu->set_action('�������Ȳ�',   INDUST . 'material/materialCost_view.php');
$menu->set_action('ñ����Ͽ�Ȳ�',   INDUST . 'parts/parts_cost_view.php');
$menu->set_action('�����������',   INDUST . 'material/materialCost_view_assy.php');
$menu->set_action('�������',   SALES . 'details/sales_view.php');
$menu->set_action('��ݼ���ɽ��',     INDUST . 'payable/act_payable_view.php');

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
    $_REQUEST['kubun'] = $_SESSION['s_kubun'];
} else {
    $kubun = '';
}

//////////// �����Υ��å����ǡ�����¸   ���ǡ����Ǥ�ڤ����뤿��
//if (! (isset($_REQUEST['forward']) || isset($_REQUEST['backward']) || isset($_REQUEST['page_keep'])) ) {
    $session->add_local('recNo', '-1');         // 0�쥳���ɤǥޡ�����ɽ�����Ƥ��ޤ�������б�
    $_SESSION['s_uri_passwd'] = $_REQUEST['uri_passwd'];
    $_SESSION['s_d_start']    = $_REQUEST['d_start'];
    $_SESSION['s_d_end']      = $_REQUEST['d_end'];
    $_SESSION['s_kubun']      = $_REQUEST['kubun'];
    $uri_passwd = $_SESSION['s_uri_passwd'];
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
    $search .= " and datatype='1'";
    $_SESSION['sales_search'] = $search;        // SQL��where�����¸
//}

$uri_passwd = $_SESSION['s_uri_passwd'];
$div        = " ";
$d_start    = $_SESSION['s_d_start'];
$d_end      = $_SESSION['s_d_end'];
$kubun      = '1';
$search     = $_SESSION['sales_search'];
$customer   = " ";

///////////// �����ȷ��������ɤ����
$query_g = sprintf("select
                        s.newjis_group_name         as ����,            -- 0
                        s.newjis_apply_code         as ����������,      -- 1
                        s.newjis_kind_name          as ����,            -- 2
                        s.newjis_certification_code as ����ǧ���ֹ�,    -- 3
                        s.newjis_period_ym          as ͭ������,        -- 4
                        s.newjis_group_code         as ����������       -- 5
                  from
                        new_jis_select_master as s
                  order by newjis_group_code
                  ");
$res_g   = array();
$field_g = array();
if (($rows_g = getResultWithField3($query_g, $field_g, $res_g)) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("<font color='yellow'>��������Ͽ������ޤ���<br>%s��%s</font>", format_date($d_start), format_date($d_end) );
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // ľ���θƽи������
    exit();
} else {
    $num_g = count($res_g);     // �ǡ���������
}

function get_assy_no($top_code, $search_middle, $result) {
    $query_ga = sprintf("select
                        assy_no as �����ֹ�       -- 0
                  from
                        new_jis_item_master
                  WHERE newjis_group_code=%d
                  order by assy_no
                  ", $top_code);   // ���� $search �Ǹ���
    $field_ga = array();
    if (($rows_ga = getResultWithField3($query_ga, $field_ga, $res_ga)) <= 0) {
        //$_SESSION['s_sysmsg'] .= sprintf("<font color='yellow'>�����ֹ椬��Ͽ����Ƥ��ޤ���</font>");
        //header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // ľ���θƽи������
        //exit();
        $num_ga = count($res_ga);       // �ǡ���������
        $result->add_array2('data_assy', $res_ga);
        $result->add('num_ga', $num_ga);
        
        $group_assy = array();
        $data_middle_num = 0;
        $group_assy[0][0] = '��';
        $group_assy[0][1] = '��';
        $num_m = 1;       // �ǡ���������
        $result->add_array2('data_middle', $group_assy);
        $result->add('num_m', $num_m);
        $result->add('data_middle_num', $data_middle_num);
    } else {
        $num_ga = count($res_ga);       // �ǡ���������
        $assy_num = 0;
        $group_assy = array();
        for ($r=0; $r<$rows_ga; $r++) {
            $group_name = trim($res_ga[$r][0]);
            //$group_name = 'CB02189';
            $search_a = "where mipn like '{$group_name}%%'";
            $query_a = sprintf("
                    select
                            mipn  as �����ֹ�  -- 0
                    from
                            miitem
                    %s
                    ", $search_a);   // ���� $search �Ǹ���
            $res_a   = array();
            $field_a = array();
            if (($rows_a = getResultWithField3($query_a, $field_a, $res_a)) <= 0) {
                //$group_assy[$assy_num][0]='CP22066-E';
                //$group_assy[$assy_num][1]=10;
            } else {
                $num_a = count($res_a);       // �ǡ���������
                for ($s=0; $s<$rows_a; $s++) {
                    //$search_middle .= " and assyno='$res_a[$s][0]'";
                    $query_m = sprintf("select
                            sum(����) as ����                       -- 0
                      from
                            hiuuri
                      left outer join
                            assembly_schedule as a
                      on �ײ��ֹ�=plan_no
                      %s
                      and assyno='%s'
                      ", $search_middle, $res_a[$s][0]);   // ���� $search �Ǹ���
                    $field_m = array();
                    if (($rows_m = getResultWithField3($query_m, $field_m, $res_m)) <= 0) {
                        //$_SESSION['s_sysmsg'] .= sprintf("<font color='yellow'>��ʬ�ब��Ͽ����Ƥ��ޤ���</font>");
                        //header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // ľ���θƽи������
                        //exit();
                        $group_assy[$assy_num][0] = $res_a[$s][0];
                        $group_assy[$assy_num][1] = 0;
                        $assy_num = $assy_num + 1;
                    } else {
                        $group_assy[$assy_num][0] = $res_a[$s][0];
                        $group_assy[$assy_num][1] = $res_m[0][0];
                        $assy_num = $assy_num + 1;
                    }
                }
            }
            
        }
        $data_middle_num = 0;
        for ($r=0; $r<$assy_num; $r++) {
            $data_middle_num += $group_assy[$r][1];
        }
        $num_m = count($group_assy);       // �ǡ���������
        $result->add_array2('data_middle', $group_assy);
        $result->add('num_m', $num_m);
        $result->add('data_middle_num', $data_middle_num);
    }
}
//////////// ɽ�������
//$ft_kingaku = number_format($data_top_t);                    // ���头�ȤΥ���ޤ��ղ�
//$ft_suryo   = number_format($data_top_nt);                   // ���头�ȤΥ���ޤ��ղ�
//$ft_ken     = number_format($t_ken);
//$ft_kazu    = number_format($t_kazu);
$f_d_start  = format_date($d_start);                        // ���դ� / �ǥե����ޥå�
$f_d_end    = format_date($d_end);
$menu->set_caption("�о�ǯ�� {$f_d_start}��{$f_d_end}");
//$menu->set_caption("�о�ǯ�� {$f_d_start}��{$f_d_end}����׶��={$ft_kingaku}����׿���={$ft_suryo}");
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
<body>
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
                    <th class='winbox' nowrap><div class='pt11b'>����������</div></th>
                    <th class='winbox' nowrap><div class='pt11b'>��̾(����)</div></th>
                    <th class='winbox' nowrap><div class='pt11b'>����<BR>ǧ���ֹ�</div></th>
                    <th class='winbox' nowrap><div class='pt11b'>ͭ������</div></th>
                    <th class='winbox' nowrap><div class='pt11b'>����</div></th>
                    <th class='winbox' nowrap><div class='pt11b'>����<BR>�ֹ�</div></th>
                    <th class='winbox' nowrap><div class='pt11b'>����<BR>����</div></th>
                    
                </tr>
            </thead>
            <tfoot>
                <!-- ���ߤϥեå����ϲ���ʤ� -->
            </tfoot>
            <tbody>
                <?php
                $data_middle_t  = 0;
                $data_middle_nt = 0;
                for ($r=0; $r<$rows_g; $r++) {
                    $flg_gu = ' ';
                    $check_gu = $r % 2;
                    if ($check_gu == 0) {
                        $flg_gu = '1';
                    }
                    get_assy_no($res_g[$r][5], $search, $result);
                    $num_m           = $result->get('num_m');
                    $data_middle     = $result->get_array2('data_middle');
                    $data_middle_num = $result->get('data_middle_num');
                    //if($num_m != 0) {
                        $num_m2      = $num_m + 1;
                        $assy_no = '';
                        echo "<tr>\n";
                        if ($flg_gu == '1') {
                            echo "  <td rowspan = '" . $num_m2 . "' class='winboxb' nowrap align='center'><div class='pt11b'>" . $res_g[$r][1] . "</div></td>\n";
                        } else {
                            echo "  <td rowspan = '" . $num_m2 . "' class='winboxg' nowrap align='center'><div class='pt11b'>" . $res_g[$r][1] . "</div></td>\n";
                        }
                        if ($flg_gu == '1') {
                            echo "  <td rowspan = '" . $num_m2 . "' class='winboxb' align='center'><div class='pt11b'>" . $res_g[$r][2] . "</div></td>\n";
                        } else {
                            echo "  <td rowspan = '" . $num_m2 . "' class='winboxg' align='center'><div class='pt11b'>" . $res_g[$r][2] . "</div></td>\n";
                        }
                        if ($flg_gu == '1') {
                            echo "  <td rowspan = '" . $num_m2 . "' class='winboxb' nowrap align='center'><div class='pt11b'>" . $res_g[$r][3] . "</div></td>\n";
                        } else {
                            echo "  <td rowspan = '" . $num_m2 . "' class='winboxg' nowrap align='center'><div class='pt11b'>" . $res_g[$r][3] . "</div></td>\n";
                        }
                        if ($flg_gu == '1') {
                            echo "  <td rowspan = '" . $num_m2 . "' class='winboxb' nowrap align='center'><div class='pt11b'>" . $res_g[$r][4] . "</div></td>\n";
                        } else {
                            echo "  <td rowspan = '" . $num_m2 . "' class='winboxg' nowrap align='center'><div class='pt11b'>" . $res_g[$r][4] . "</div></td>\n";
                        }
                        if ($flg_gu == '1') {
                            echo "  <td rowspan = '" . $num_m2 . "' class='winboxb' nowrap align='center'><div class='pt11b'>" . $res_g[$r][0] . "</div></td>\n";
                        } else {
                            echo "  <td rowspan = '" . $num_m2 . "' class='winboxg' nowrap align='center'><div class='pt11b'>" . $res_g[$r][0] . "</div></td>\n";
                        }
                        echo "  <td class='winbox' nowrap align='left'><div class='pt9'>" . $data_middle[0][0] . "</div></td>\n";
                        if ($data_middle[0][1] == 0) {
                            echo "  <td class='winbox' nowrap align='right'><div class='pt9'>��</div></td>\n";
                        } else {
                            echo "  <td class='winbox' nowrap align='right'><div class='pt9'>
                                    <a class='pt9' href='JavaScript:baseJS.Ajax(\"{$menu->out_self()}\");location.replace(\"", $menu->out_action('�������'), "?uri_passwd={$uri_passwd}&d_start={$d_start}&d_end={$d_end}&kubun={$kubun}&div={$div}&customer=$customer&uri_ritu=52&sales_page=25&assy_no={$data_middle[0][0]}\")' target='application' style='text-decoration:none;'>"
                                    . number_format($data_middle[0][1], 0) . "</div></td>\n";
                        }
                        echo "</tr>\n";
                        for ($i=1; $i<$num_m; $i++) {
                            echo "<tr>\n";
                            echo "  <td class='winbox' nowrap align='left'><div class='pt9'>" . $data_middle[$i][0] . "</div></td>\n";
                            if ($data_middle[$i][1] == 0) {
                                echo "  <td class='winbox' nowrap align='right'><div class='pt9'>��</div></td>\n";
                            } else {
                            echo "  <td class='winbox' nowrap align='right'><div class='pt9'>
                                    <a class='pt9' href='JavaScript:baseJS.Ajax(\"{$menu->out_self()}\");location.replace(\"", $menu->out_action('�������'), "?uri_passwd={$uri_passwd}&d_start={$d_start}&d_end={$d_end}&kubun={$kubun}&div={$div}&customer=$customer&uri_ritu=52&sales_page=25&assy_no={$data_middle[$i][0]}\")' target='application' style='text-decoration:none;'>"
                                    . number_format($data_middle[$i][1], 0) . "</div></td>\n";
                            }
                            echo "</tr>\n";
                        }
                        echo "<tr>\n";
                        if ($flg_gu == '1') {
                            echo "  <td class='winboxb' nowrap align='center'><div class='pt9b'>��</div></td>\n";
                            echo "  <td class='winboxb' nowrap align='right'><div class='pt9b'>" . number_format($data_middle_num, 0) . "</div></td>\n";
                        } else {
                            echo "  <td class='winboxg' nowrap align='center'><div class='pt9b'>��</div></td>\n";
                            echo "  <td class='winboxg' nowrap align='right'><div class='pt9b'>" . number_format($data_middle_num, 0) . "</div></td>\n";
                        }
                        echo "</tr>\n";
                   // }
                }
                ?>
            </tbody>
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
