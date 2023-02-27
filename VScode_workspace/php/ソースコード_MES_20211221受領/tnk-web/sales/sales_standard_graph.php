<?php
//////////////////////////////////////////////////////////////////////////////
// ��� ���� ɸ�������� �Ȳ�                                                //
// Copyright (C) 2005-2006 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2005/06/03 Created   sales_custom_graph.php �� sales_standard_graph.php  //
//            �����ץ����ѥ���դ�ɸ�������ѥ���դ˥������ޥ���          //
// 2005/06/05 �ǽ�����31�Υ�ƥ�뤫�� last_day()�Ǽ������ѹ�               //
// 2005/06/07 ɸ�������Ѥ��ä��Τ�����������Ǥ���褦���ѹ�              //
// 2005/06/16 ���ʥ��롼�פ����Ρ����ץ����Τ��ɲ� �����ȼ�����å����ѹ� //
// 2005/08/21 jpGraph2.0beta��UP�ˤ��valueɽ������SetValuePos('center')�ɲ�//
// 2006/09/05 �̾�Υ���դΤ褦�˱�¦���ǿ��ˤʤ�褦�ˣؼ�(ǯ��)���ѹ�    //
// 2006/09/06 ����գ���������Ф������������Ψ�����(�ޤ���)���ѹ�      //
//            ������ ���� �б��Τ��� Graph(820, 360) �� Graph(750, 360)     //
// 2006/09/07 $datay['���Ψ'][$i] = Uround( $sum_sou / $sum_uri * 100, 1)  //
//            PHP Warning: Division by zero�б��Τ��� $sum_uri �����å��ɲ� //
// 2006/09/08 ǯ��Υ�ߥåȤ�200310��200010���ѹ�������¾��̤��Ͽ��̾���ѹ�//
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
// ini_set('zend.ze1_compatibility_mode', '1');    // zend 1.X ����ѥ� php4�θߴ��⡼��
// ini_set('implicit_flush', 'off');           // echo print �� flush �����ʤ�(�٤��ʤ뤿��) CLI CGI��
// ini_set('max_execution_time', 1200);        // ����¹Ի���=20ʬ CLI CGI��
ob_start('ob_gzhandler');                   // ���ϥХåե���gzip����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��

require_once ('../function.php');           // define.php �� pgsql.php �� require_once ���Ƥ���
require_once ('../tnk_func.php');           // TNK �˰�¸������ʬ�δؿ��� require_once ���Ƥ���
require_once ('../MenuHeader.php');         // TNK ������ menu class
access_log();                               // Script Name �ϼ�ư����

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader(0);                  // ǧ�ڥ����å�0=���̰ʾ� �����=TOP_MENU �����ȥ�̤����

////////////// ����������
$menu->set_site( 1, 14);                    // site_index=01(����˥塼) site_id=14(ɸ�����������)
////////////// �꥿���󥢥ɥ쥹����
// $menu->set_RetUrl(SALES_MENU);              // �̾�ϻ��ꤹ��ɬ�פϤʤ�(����˥塼)
//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$div = $_SESSION['standard_div'];
if ($div == 'A') {                  // ����
    $menu->set_title('��帶��Ψʬ�ϥ���� ����');
} elseif ($div == 'C') {            // ���ץ�����
    $menu->set_title('��帶��Ψʬ�ϥ���� ���ץ�����');
} elseif ($div == 'CH') {           // ���ץ�ɸ����
    $menu->set_title('��帶��Ψʬ�ϥ���� ���ץ�ɸ����');
} elseif ($div == 'CS') {           // ���ץ�����
    $menu->set_title('��帶��Ψʬ�ϥ���� �����������');
} elseif ($div == 'L') {            // ��˥�ɸ����
    $menu->set_title('��帶��Ψʬ�ϥ���� ��˥�����');
} elseif ($div == 'LL') {           // ���ץ�ɸ����
    $menu->set_title('��帶��Ψʬ�ϥ���� ��˥��Τ�');
} elseif ($div == 'LB') {           // ���ץ�����
    $menu->set_title('��帶��Ψʬ�ϥ���� �Х����');
} else {
    $menu->set_title('��奰��� ����Ψʬ������');
}
//////////// �ƽ����action̾�ȥ��ɥ쥹����
$menu->set_action('�������', SALES . 'sales_standard_view.php');
$menu->set_retGET('sum_exec', '���ɽ�Ȳ�');

//////////// JavaScript Stylesheet File ��ɬ���ɤ߹��ޤ���
$uniq = uniqid('target');

//////////// ������ʬ�Υǡ���ȴ�Ф�function
function getCustomSales($strYm)
{
    if ( ($strYm < 200010) || ($strYm > date('Ym')) ) {
        return FALSE;
    }
    ///// SQLʸ����Ω
    $last_day = last_day(substr($strYm, 0, 4), substr($strYm, 4, 2));
    $d_start = ($strYm . '01');
    $d_end   = ($strYm . $last_day);
    $where_div = $_SESSION['standard_where_div'];
    $kubun     = $_SESSION['standard_kubun'];
    $div       = $_SESSION['standard_div'];
    $where_assy_no = $_SESSION['standard_where_assy_no'];
    // $where = $_SESSION['standard_where'];
    if ($div == 'CH') { // ɸ���ʤʤ�
        $where = "
            where
            �׾���>={$d_start} and �׾���<={$d_end} and datatype={$kubun} and {$where_div}
            and
            note15 not like 'SC%' {$where_assy_no}
        ";
    } elseif ($div == 'CS') { // ������ʤ�
        $where = "
            where
            �׾���>={$d_start} and �׾���<={$d_end} and datatype={$kubun} and {$where_div}
            and
            note15 like 'SC%' {$where_assy_no}
        ";
    } else {            // ���Ρ���˥����Ρ���˥��Τߡ��Х����
        $where = "
            where
            �׾���>={$d_start} and �׾���<={$d_end} and datatype={$kubun} and {$where_div}
            {$where_assy_no}
        ";
    }
    //////// ��
    $query = ($_SESSION['costom_sql'] . $where . $_SESSION['standard_condition1']);
    if (getResult($query, $res_sum) <= 0) {
        $_SESSION['s_sysmsg'] .= '�ǡ����١����������Фǥ��顼��ȯ�����ޤ���������ô���Ԥ�Ϣ���Ʋ�������';
        return FALSE;
    }
    $data[0] = $res_sum[0];
    //////// ��
    $query = ($_SESSION['costom_sql'] . $where . $_SESSION['standard_condition2']);
    if (getResult($query, $res_sum) <= 0) {
        $_SESSION['s_sysmsg'] .= '�ǡ����١����������Фǥ��顼��ȯ�����ޤ���������ô���Ԥ�Ϣ���Ʋ�������';
        return FALSE;
    }
    $data[1] = $res_sum[0];
    //////// ��
    $query = ($_SESSION['costom_sql'] . $where . $_SESSION['standard_condition3']);
    if (getResult($query, $res_sum) <= 0) {
        $_SESSION['s_sysmsg'] .= '�ǡ����١����������Фǥ��顼��ȯ�����ޤ���������ô���Ԥ�Ϣ���Ʋ�������';
        return FALSE;
    }
    $data[2] = $res_sum[0];
    //////// ��
    $query = ($_SESSION['costom_sql'] . $where . $_SESSION['standard_condition4']);
    if (getResult($query, $res_sum) <= 0) {
        $_SESSION['s_sysmsg'] .= '�ǡ����١����������Фǥ��顼��ȯ�����ޤ���������ô���Ԥ�Ϣ���Ʋ�������';
        return FALSE;
    }
    $data[3] = $res_sum[0];
    return $data;
    // Ϣ��������֤�
    /*****************************
    $data[0]['�����']    +
    $data[0]['�������']    |   ��
    $data[0]['���']        |
    $data[0]['����']        +
    $data[1]                    ��
    *****************************/
}

//////////// ���Υ��å������Ͽ�ȥ��å��������
if (isset($_REQUEST['graph_exec'])) {
    $_SESSION['standard_graph_exec'] = $_REQUEST['graph_exec'];
    $graph_exec = $_SESSION['standard_graph_exec'];
} else {
    $graph_exec = $_SESSION['standard_graph_exec'];
}

$uri_passwd = $_SESSION['s_uri_passwd'];
$div        = $_SESSION['standard_div'];
$d_start    = $_SESSION['standard_d_start'];
$d_end      = $_SESSION['standard_d_end'];
$kubun      = $_SESSION['standard_kubun'];
$uri_ritu   = 52;   // ��ƥ����ѹ�
$assy_no    = $_SESSION['standard_assy_no'];

////////////// �ѥ���ɥ����å�
if ($uri_passwd != date('Ymd')) {
    $_SESSION['s_sysmsg'] = "<font color='yellow'>�ѥ���ɤ��㤤�ޤ���</font>";
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // ľ���θƽи������
    exit();
}

//////////// ɽ�������
// $menu->set_caption('��=̤��Ͽ�ʲ����ʳ��ˡ���������<br>��=�������Σ������󤬻���ñ��<br>��=�������Σ������󤬻���ñ��<br>��=������ʤΡ������󤬻���ñ��');
$menu->set_caption("
    ��=̤��Ͽ�ʲ����ʳ���<br>
    ��={$_SESSION['standard_lower_equal_ritu']}% �� {$_SESSION['standard_upper_equal_ritu']}%<br>
    ��={$_SESSION['standard_lower_mate_ritu']}% �� {$_SESSION['standard_upper_mate_ritu']}%<br>
    ��={$_SESSION['standard_lower_uri_ritu']}% �� {$_SESSION['standard_upper_uri_ritu']}%
");

//////////// ������դ�ɽ�����
switch ($graph_exec) {
case 1:
    define('PAGE', 3);
    $graph_title  = '������ ����� ���� ���';
    $graph2_title = '������ ����� ������� ���';
    break;
case 2:
    define('PAGE', 6);
    $graph_title  = '������ ����� ���� ���';
    $graph2_title = '������ ����� ������� ���';
    break;
case 3:
default:
    define('PAGE', 12);
    $graph_title  = '�������� ����� ���� ���';
    $graph2_title = '�������� ����� ������� ���';
    break;
}

//////////// ��� ͭ����� ����
$ym = date('Ym');   // ���ߤ�ǯ�� �����
$i = 0;
while ($ym > 200010) {
    $i++;
    $year  = substr($ym, 0, 4);
    $month = (substr($ym, 4, 2) - 1);
    if ($month < 1) {
        $year -= 1;
        $month = 12;
    }
    $ym = sprintf("%d%02d", $year, $month);
}
$maxrows = $i;

//////////// �ڡ������ե��å�����
// if ( isset($_POST['forward']) ) {                       // ���Ǥ������줿
if ( isset($_POST['backward']) ) {                      // ���Ǥ������줿
    $_SESSION['standard_graph_offset'] += PAGE;
    if ($_SESSION['standard_graph_offset'] >= $maxrows) {
        $_SESSION['standard_graph_offset'] -= PAGE;
        if ($_SESSION['s_sysmsg'] == '') {
            $_SESSION['s_sysmsg'] .= "<font color='yellow'>���ǤϤ���ޤ���</font>";
        } else {
            $_SESSION['s_sysmsg'] .= "<br><font color='yellow'>���ǤϤ���ޤ���</font>";
        }
    }
// } elseif ( isset($_POST['backward']) ) {                // ���Ǥ������줿
} elseif ( isset($_POST['forward']) ) {                 // ���Ǥ������줿
    $_SESSION['standard_graph_offset'] -= PAGE;
    if ($_SESSION['standard_graph_offset'] < 0) {
        $_SESSION['standard_graph_offset'] = 0;
        if ($_SESSION['s_sysmsg'] == '') {
            $_SESSION['s_sysmsg'] .= "<font color='yellow'>���ǤϤ���ޤ���</font>";
        } else {
            $_SESSION['s_sysmsg'] .= "<br><font color='yellow'>���ǤϤ���ޤ���</font>";
        }
    }
} elseif ( isset($_GET['page_keep']) ) {                // ���ߤΥڡ�����ݻ����� GET�����
    $offset = $_SESSION['standard_graph_offset'];
} elseif ( isset($_POST['page_keep']) ) {               // ���ߤΥڡ�����ݻ�����
    $offset = $_SESSION['standard_graph_offset'];
} else {
    $_SESSION['standard_graph_offset'] = 0;               // ���ξ��ϣ��ǽ����
}
$offset = $_SESSION['standard_graph_offset'];

/////////// ����եǡ����μ���
$graph_flg = TRUE;
$ym = date('Ym');                   // ���ߤ�ǯ�� �����
/////////// ���ե��åȽ���
for ($i=0; $i<$offset; $i++) {
    $year  = substr($ym, 0, 4);
    $month = (substr($ym, 4, 2) - 1);
    if ($month < 1) {
        $year -= 1;
        $month = 12;
    }
    $ym = sprintf("%d%02d", $year, $month);
}
// for ($i=0; $i<PAGE; $i++) {
for ($i=(PAGE-1); $i>=0; $i--) {
    $year  = substr($ym, 0, 4);
    $month = (substr($ym, 4, 2) - 1);
    if ($month < 1) {
        $year -= 1;
        $month = 12;
    }
    $datax[$i] = sprintf("%2\$02d\n%1\$d", $year, $month);     // X���ι������� 1\$ 2\$ ���ǰ����θ�
    $ym        = sprintf("%d%02d", $year, $month);      // �ؿ����Ϥ��ǡ��������� ����դǰ����Ȥ��Ƥ�Ȥ�
    $ym_p[$i]  = $ym;                                   // ����ե��᡼���ޥåפΰ����Ȥ��ƻȤ�
    if ( ($data = getCustomSales($ym)) == FALSE) {
        $datay['�����'][0][$i] = 0;
        $datay['�������'][0][$i] = 0;
        $datay['���'    ][0][$i] = 0;
        $datay['����'    ][0][$i] = 0;
        $datay['�����'][1][$i] = 0;
        $datay['�������'][1][$i] = 0;
        $datay['���'    ][1][$i] = 0;
        $datay['����'    ][1][$i] = 0;
        $datay['�����'][2][$i] = 0;
        $datay['�������'][2][$i] = 0;
        $datay['���'    ][2][$i] = 0;
        $datay['����'    ][2][$i] = 0;
        $datay['�����'][3][$i] = 0;
        $datay['�������'][3][$i] = 0;
        $datay['���'    ][3][$i] = 0;
        $datay['����'    ][3][$i] = 0;
        // if ($i == 0) $graph_flg = FALSE;            // �����ܤǥǡ�����̵����Х���դϺ��ʤ�
        if ($i == (PAGE-1)) $graph_flg = FALSE;            // �����ܤǥǡ�����̵����Х���դϺ��ʤ�
        $datay['���Ψ'][$i] = 0.0;
    } else {
        $datay['�����'][0][$i] = Uround($data[0]['�����'] / 1000000, 1);
        $datay['�������'][0][$i] = Uround($data[0]['�������'] / 1000000, 1);
        $datay['���'    ][0][$i] = $data[0]['���'    ];
        $datay['����'    ][0][$i] = $data[0]['����'    ];
        $datay['�����'][1][$i] = Uround($data[1]['�����'] / 1000000, 1);
        $datay['�������'][1][$i] = Uround($data[1]['�������'] / 1000000, 1);
        $datay['���'    ][1][$i] = $data[1]['���'    ];
        $datay['����'    ][1][$i] = $data[1]['����'    ];
        $datay['�����'][2][$i] = Uround($data[2]['�����'] / 1000000, 1);
        $datay['�������'][2][$i] = Uround($data[2]['�������'] / 1000000, 1);
        $datay['���'    ][2][$i] = $data[2]['���'    ];
        $datay['����'    ][2][$i] = $data[2]['����'    ];
        $datay['�����'][3][$i] = Uround($data[3]['�����'] / 1000000, 1);
        $datay['�������'][3][$i] = Uround($data[3]['�������'] / 1000000, 1);
        $datay['���'    ][3][$i] = $data[3]['���'    ];
        $datay['����'    ][3][$i] = $data[3]['����'    ];
        $sum_sou = 0; $sum_uri = 0;
        for ($j=0; $j<=3; $j++) {
            $sum_sou += $data[$j]['�������'];
            $sum_uri += $data[$j]['�����'];
        }
        if ($sum_uri) {
            $datay['���Ψ'][$i] = Uround( $sum_sou / $sum_uri * 100, 1);
        } else {
            $datay['���Ψ'][$i] = 0.0;
        }
    }
}
/////////// ���������
if ($graph_flg) {
    require_once ('../../jpgraph.php');
    require_once ('../../jpgraph_bar.php');
    require_once ('../../jpgraph_line.php');
    
    /* ################################## ����Υ���պ��� ############################### */
    $graph = new Graph(750, 360);               // ����դ��礭�� X/Y
    $graph->SetScale('textlin'); 
    $graph->img->SetMargin(50, 110, 40, 50);    // ����հ��֤Υޡ����� �����岼
    $graph->SetShadow(); 
    // $graph->SetMarginColor('#d6d3ce');          // ɽ��ɸ�࿧�˹�碌��
    
    // Setup title
    $graph->title->SetFont(FF_GOTHIC, FS_NORMAL, 14);   // FF_GOTHIC 14 �ʾ� FF_MINCHO �� 17 �ʾ����ꤹ��
    $graph->title->Set(mb_convert_encoding("{$menu->out_title()} $graph_title", 'UTF-8')); 
    // $graph->subtitle->SetFont(FF_GOTHIC, FS_NORMAL, 11);
    // $graph->subtitle->Set(mb_convert_encoding('ñ��:ɴ����', 'UTF-8'));
    // $graph->subtitle->SetAlign('right');
    $graph->yaxis->title->SetFont(FF_GOTHIC, FS_NORMAL, 11);
    $graph->yaxis->title->Set(mb_convert_encoding('���⡡��ñ��:ɴ����', 'UTF-8'));
    $graph->yaxis->title->SetMargin(10, 0, 0, 0);       // ����Y���ο��ͤ�Υ�� 10
    
    // Setup X-scale 
    $graph->xaxis->SetTickLabels($datax);               // ��������
    $graph->xaxis->SetFont(FF_GOTHIC, FS_NORMAL, 10);   // �ե���Ȥϥܡ���ɤ����Ǥ��롣
    $graph->xaxis->SetLabelAngle(0);                    // �Ф��60��
    
    // Setup format for legend
    $graph->legend->SetFillColor('antiquewhite');
    $graph->legend->SetShadow(true);
    $graph->legend->Pos(0.015, 0.5, 'right', 'center'); // ����ΰ��ֻ���
    $graph->legend->SetFont(FF_GOTHIC, FS_NORMAL, 11);  // FF_GOTHIC �� 14 �ʾ� FF_MINCHO �� 17 �ʾ�
    
    /****************** �� *********************/
    // Create the bar plots 
    $bplot0 = new BarPlot($datay['�����'][0]);
    $bplot0->SetWidth(0.6);
    // Setup color for gradient fill style 
    $bplot0->SetFillGradient('navy', 'lightsteelblue', GRAD_CENTER);
    // Set color for the frame of each bar
    $bplot0->SetColor('navy');
    $bplot0->value->SetFormat('%01.1f');    // �������̥ե����ޥå�
    $bplot0->SetValuePos('center');         // ��������
    $bplot0->value->Show();                 // ����ɽ��
    if (PAGE > 6) {
        $bplot0->value->SetColor('maroon', 'navy'); // ɽ��������
    } else {
        $bplot0->value->SetColor('white', 'navy');  // ɽ��������
    }
    $bplot0->SetLegend(mb_convert_encoding('��', 'UTF-8'));   // �����̾������ \n����Ѳ�
    for ($j=0; $j<PAGE; $j++) {
        $targ[$j] = $menu->out_action('�������') . "?ym_p={$ym_p[$j]}&standard_view1=on')";
        $alts[$j] = "{$ym_p[$j]}������ �� ��ۡ�%0.1f";
    }
    $bplot0->SetCSIMTargets($targ, $alts); 
    
    /****************** �� *********************/
    // Create the bar plots 
    $bplot1 = new BarPlot($datay['�����'][1]);
    $bplot1->SetWidth(0.6);
    // Setup color for gradient fill style 
    $bplot1->SetFillGradient('darkgreen', 'white', GRAD_CENTER);
    // Set color for the frame of each bar
    $bplot1->SetColor('navy');
    $bplot1->value->SetFormat('%01.1f');    // �������̥ե����ޥå�
    $bplot1->SetValuePos('center');         // ��������
    $bplot1->value->Show();                 // ����ɽ��
    $bplot1->SetLegend(mb_convert_encoding('��', 'UTF-8'));   // �����̾������ \n����Ѳ�
    for ($j=0; $j<PAGE; $j++) {
        $targ[$j] = $menu->out_action('�������') . "?ym_p={$ym_p[$j]}&standard_view2=on')";
        $alts[$j] = "{$ym_p[$j]}������ �� ��ۡ�%0.1f";
    }
    $bplot1->SetCSIMTargets($targ, $alts); 
    
    /****************** �� *********************/
    // Create the bar plots 
    $bplot2 = new BarPlot($datay['�����'][2]);
    $bplot2->SetWidth(0.6);
    // Setup color for gradient fill style 
    $bplot2->SetFillGradient('maroon', 'white', GRAD_CENTER);
    // Set color for the frame of each bar
    $bplot2->SetColor('navy');
    $bplot2->value->SetFormat('%01.1f');    // �������̥ե����ޥå�
    $bplot2->SetValuePos('center');         // ��������
    $bplot2->value->Show();                 // ����ɽ��
    $bplot2->SetLegend(mb_convert_encoding('��', 'UTF-8'));   // �����̾������ \n����Ѳ�
    for ($j=0; $j<PAGE; $j++) {
        $targ[$j] = $menu->out_action('�������') . "?ym_p={$ym_p[$j]}&standard_view3=on')";
        $alts[$j] = "{$ym_p[$j]}������ �� ��ۡ�%0.1f";
    }
    $bplot2->SetCSIMTargets($targ, $alts); 
    
    /****************** �� *********************/
    // Create the bar plots 
    $bplot3 = new BarPlot($datay['�����'][3]);
    $bplot3->SetWidth(0.6);
    // Setup color for gradient fill style 
    $bplot3->SetFillGradient('gray4', 'white', GRAD_CENTER);
    // Set color for the frame of each bar
    $bplot3->SetColor('navy');
    $bplot3->value->SetFormat('%01.1f');    // �������̥ե����ޥå�
    $bplot3->SetValuePos('center');         // ��������
    $bplot3->value->Show();                 // ����ɽ��
    $bplot3->SetLegend(mb_convert_encoding('̤��Ͽ', 'UTF-8'));   // �����̾������ \n����Ѳ�
    for ($j=0; $j<PAGE; $j++) {
        $targ[$j] = $menu->out_action('�������') . "?ym_p={$ym_p[$j]}&standard_view4=on')";
        $alts[$j] = "{$ym_p[$j]}������ �� ��ۡ�%0.1f";
    }
    $bplot3->SetCSIMTargets($targ, $alts); 
    
    // Create the grouped bar plot
    $gbplot = new AccBarPlot(array($bplot0, $bplot1, $bplot2, $bplot3));
    $gbplot->value->SetFormat('%01.1f');    // �����ե����ޥå�
    $gbplot->value->Show();                 // ����ɽ��
    
    // Create the graph
    $graph->Add($gbplot);
    // $graph_name = ('graph/sales_standard' . session_id() . '.png');
    $graph_name = 'graph/sales_standard_graph.png';
    $graph->Stroke($graph_name);
    
    
    /* ################################## �����Ф�������������Ψ�Υ���պ��� ############################### */
    // A nice graph with anti-aliasing 
    $graph2 = new Graph(750, 360, 'auto');          // ����դ��礭�� X/Y
    $graph2->img->SetMargin(52, 112, 30, 60);       // ����հ��֤Υޡ����� �����岼
    $graph2->SetScale('textlin'); 
    $graph2->SetShadow(); 
    // Slightly adjust the legend from it's default position in the 
    // top right corner. 
    $graph2->legend->Pos(0.015, 0.5, 'right', 'center');    // ����ΰ��ֻ���(�����ޡ�����,�岼�ޡ�����,"right","center")
    $graph2->legend->SetFont(FF_GOTHIC, FS_NORMAL, 14);
    
    $graph2->yscale->SetGrace(10);     // Set 10% grace. ;͵��������
    $graph2->yaxis->SetColor("blue");
    $graph2->yaxis->SetWeight(2);
    
    // Use built in font 
    $graph2->title->SetFont(FF_GOTHIC, FS_NORMAL, 14);
    $graph2->title->Set(mb_convert_encoding("{$menu->out_title()} ���������������� ��Ψ ���", 'UTF-8'));
    
    // Setup X-scale 
    $graph2->xaxis->SetTickLabels($datax); 
    // $graph2->xaxis->SetFont(FF_FONT1); 
    $graph2->xaxis->SetFont(FF_GOTHIC, FS_NORMAL, 11);
    $graph2->xaxis->SetLabelAngle(0); // �Ф��60
    
    // Setup Y-scale 
    $graph2->yaxis->title->SetFont(FF_GOTHIC, FS_NORMAL, 11);
    $graph2->yaxis->title->Set(mb_convert_encoding('����������Ψ����ñ��:��', 'UTF-8'));
    $graph2->yaxis->title->SetMargin(13, 0, 0, 0);       // ��������ʸ����Y���ο��ͤ�Υ�� 10
    
    // Create the first line 
    $p1 = new LinePlot($datay['���Ψ']); 
    $p1->mark->SetType(MARK_FILLEDCIRCLE); 
    $p1->mark->SetFillColor('blue'); 
    $p1->mark->SetWidth(3); 
    $p1->SetColor('blue'); 
    $p1->SetCenter(); 
    $p1->value->SetColor('black');
    $p1->value->SetFont(FF_GOTHIC, FS_NORMAL, 11);
    $p1->value->SetFormat('%01.1f');    // �����ե����ޥå�
    $p1->value->Show();                 // ����ɽ��
    $p1->SetLegend(mb_convert_encoding("�����\n��Ψ", 'UTF-8')); 
    $graph2->legend->SetFont(FF_GOTHIC, FS_NORMAL, 11);
    $graph2->Add($p1); 
    
    // Output line 
    $graph_name2 = 'graph/sales_standard_graph_material.png';
    $graph2->Stroke($graph_name2);
} else {
    if (isset($_REQUEST['forward'])) $_SESSION['s_sysmsg'] = '���ǤϤ���ޤ���';
    elseif (isset($_REQUEST['backward'])) $_SESSION['s_sysmsg'] = '���ǤϤ���ޤ���';
    else $_SESSION['s_sysmsg'] = '����եǡ���������ޤ���';
}
    
    
/////////// ���������2 (old�С������Υ����)
if (false) {
    /* ################################## �������Υ���պ��� ############################### */
    $graph2 = new Graph(750, 360);               // ����դ��礭�� X/Y
    $graph2->SetScale('textlin'); 
    $graph2->img->SetMargin(50, 110, 40, 50);    // ����հ��֤Υޡ����� �����岼
    $graph2->SetShadow(); 
    // $graph2->SetMarginColor('#d6d3ce');          // ɽ��ɸ�࿧�˹�碌��
    
    // Setup title
    $graph2->title->SetFont(FF_GOTHIC, FS_NORMAL, 14);   // FF_GOTHIC 14 �ʾ� FF_MINCHO �� 17 �ʾ����ꤹ��
    $graph2->title->Set(mb_convert_encoding($graph2_title, 'UTF-8')); 
    // $graph2->subtitle->SetFont(FF_GOTHIC, FS_NORMAL, 11);
    // $graph2->subtitle->Set(mb_convert_encoding('ñ��:ɴ����', 'UTF-8'));
    // $graph2->subtitle->SetAlign('right');
    $graph2->yaxis->title->SetFont(FF_GOTHIC, FS_NORMAL, 11);
    $graph2->yaxis->title->Set(mb_convert_encoding('������񡡡�ñ��:ɴ����', 'UTF-8'));
    $graph2->yaxis->title->SetMargin(10, 0, 0, 0);       // ����Y���ο��ͤ�Υ�� 10
    
    // Setup X-scale 
    $graph2->xaxis->SetTickLabels($datax);               // ��������
    $graph2->xaxis->SetFont(FF_GOTHIC, FS_NORMAL, 10);   // �ե���Ȥϥܡ���ɤ����Ǥ��롣
    $graph2->xaxis->SetLabelAngle(0);                    // �Ф��60��
    
    // Setup format for legend
    $graph2->legend->SetFillColor('antiquewhite');
    $graph2->legend->SetShadow(true);
    $graph2->legend->Pos(0.015, 0.5, 'right', 'center'); // ����ΰ��ֻ���
    $graph2->legend->SetFont(FF_GOTHIC, FS_NORMAL, 11);  // FF_GOTHIC �� 14 �ʾ� FF_MINCHO �� 17 �ʾ�
    
    /****************** �� *********************/
    // Create the bar plots 
    $bplotM0 = new BarPlot($datay['�������'][0]);
    $bplotM0->SetWidth(0.6);
    // Setup color for gradient fill style 
    $bplotM0->SetFillGradient('navy', 'lightsteelblue', GRAD_CENTER);
    // Set color for the frame of each bar
    $bplotM0->SetColor('navy');
    $bplotM0->value->SetFormat('%01.1f');    // �������̥ե����ޥå�
    $bplotM0->SetValuePos('center');         // ��������
    $bplotM0->value->Show();                 // ����ɽ��
    if (PAGE > 6) {
        $bplotM0->value->SetColor('maroon', 'navy');    // ɽ��������
    } else {
        $bplotM0->value->SetColor('white', 'navy');     // ɽ��������
    }
    $bplotM0->SetLegend(mb_convert_encoding('��', 'UTF-8'));   // �����̾������ \n����Ѳ�
    for ($j=0; $j<PAGE; $j++) {
        $targ[$j] = $menu->out_action('�������') . "?ym_p={$ym_p[$j]}&standard_view1=on')";
        $alts[$j] = "{$ym_p[$j]}��������� �� ��ۡ�%0.1f";
    }
    $bplotM0->SetCSIMTargets($targ, $alts); 
    
    /****************** �� *********************/
    // Create the bar plots 
    $bplotM1 = new BarPlot($datay['�������'][1]);
    $bplotM1->SetWidth(0.6);
    // Setup color for gradient fill style 
    $bplotM1->SetFillGradient('darkgreen', 'white', GRAD_CENTER);
    // Set color for the frame of each bar
    $bplotM1->SetColor('navy');
    $bplotM1->value->SetFormat('%01.1f');    // �������̥ե����ޥå�
    $bplotM1->SetValuePos('center');         // ��������
    $bplotM1->value->Show();                 // ����ɽ��
    $bplotM1->SetLegend(mb_convert_encoding('��', 'UTF-8'));   // �����̾������ \n����Ѳ�
    for ($j=0; $j<PAGE; $j++) {
        $targ[$j] = $menu->out_action('�������') . "?ym_p={$ym_p[$j]}&standard_view2=on')";
        $alts[$j] = "{$ym_p[$j]}��������� �� ��ۡ�%0.1f";
    }
    $bplotM1->SetCSIMTargets($targ, $alts); 
    
    /****************** �� *********************/
    // Create the bar plots 
    $bplotM2 = new BarPlot($datay['�������'][2]);
    $bplotM2->SetWidth(0.6);
    // Setup color for gradient fill style 
    $bplotM2->SetFillGradient('maroon', 'white', GRAD_CENTER);
    // Set color for the frame of each bar
    $bplotM2->SetColor('navy');
    $bplotM2->value->SetFormat('%01.1f');    // �������̥ե����ޥå�
    $bplotM2->SetValuePos('center');         // ��������
    $bplotM2->value->Show();                 // ����ɽ��
    $bplotM2->SetLegend(mb_convert_encoding('��', 'UTF-8'));   // �����̾������ \n����Ѳ�
    for ($j=0; $j<PAGE; $j++) {
        $targ[$j] = $menu->out_action('�������') . "?ym_p={$ym_p[$j]}&standard_view3=on')";
        $alts[$j] = "{$ym_p[$j]}��������� �� ��ۡ�%0.1f";
    }
    $bplotM2->SetCSIMTargets($targ, $alts); 
    
    /****************** �� *********************/
    // Create the bar plots 
    $bplotM3 = new BarPlot($datay['�������'][3]);
    $bplotM3->SetWidth(0.6);
    // Setup color for gradient fill style 
    $bplotM3->SetFillGradient('gray4', 'white', GRAD_CENTER);
    // Set color for the frame of each bar
    $bplotM3->SetColor('navy');
    $bplotM3->value->SetFormat('%01.1f');    // �������̥ե����ޥå�
    $bplotM3->SetValuePos('center');         // ��������
    $bplotM3->value->Show();                 // ����ɽ��
    $bplotM3->SetLegend(mb_convert_encoding('̤��Ͽ', 'UTF-8'));   // �����̾������ \n����Ѳ�
    for ($j=0; $j<PAGE; $j++) {
        $targ[$j] = $menu->out_action('�������') . "?ym_p={$ym_p[$j]}&standard_view4=on')";
        $alts[$j] = "{$ym_p[$j]}��������� �� ��ۡ�%0.1f";
    }
    $bplotM3->SetCSIMTargets($targ, $alts); 
    
    // Create the grouped bar plot
    $gbplotM = new AccBarPlot(array($bplotM0, $bplotM1, $bplotM2, $bplotM3));
    $gbplotM->value->SetFormat('%01.1f');    // �����ե����ޥå�
    $gbplotM->value->Show();                 // ����ɽ��
    
    // Create the graph
    $graph2->Add($gbplotM);
    // $graph2_name = ('graph/sales_standard' . session_id() . '.png');
    $graph_name2 = 'graph/sales_standard_graph_material.png';
    $graph2->Stroke($graph_name2);
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
<?=$menu->out_site_java()?>
<?=$menu->out_css()?>
<!--    �ե��������ξ��
<script language='JavaScript' src='template.js?<?= $uniq ?>'>
</script>
-->

<script language="JavaScript">
<!--
/* ����ʸ�����������ɤ��������å� */
function isDigit(str) {
    var len=str.length;
    var c;
    for (i=0; i<len; i++) {
        c = str.charAt(i);
        if ((c < "0") || (c > "9")) {
            return true;
        }
    }
    return false;
}
/* ������ϥե�����Υ�����Ȥ˥ե������������� */
function set_focus(){
    document.body.focus();                          // F2/F12��������뤿����б�
//    document.form_name.element_name.select();
}
// -->
</script>

<!-- �������륷���ȤΥե��������򥳥��� HTML���� �����Ȥ�����Ҥ˽���ʤ��������
<link rel='stylesheet' href='<?= MENU_FORM . '?' . $uniq ?>' type='text/css' media='screen'>
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
    background-image:url(<?= IMG ?>t_nitto_logo4.png);
    background-repeat:no-repeat;
    background-attachment:fixed;
    background-position:right bottom;
}
-->
</style>
</head>
<body onLoad='set_focus()' style='overflow:hidden-x;'>
    <center>
<?=$menu->out_title_border()?>
        
        <!----------------- ������ ���� ���� �Υե����� ---------------->
        <table width='100%' border='0' cellspacing='0' cellpadding='0'>
            <form name='page_form' method='post' action='<?= $menu->out_self() ?>'>
                <tr>
                    <td align='left'>
                        <table align='left' border='3' cellspacing='0' cellpadding='0'>
                            <td align='left'>
                                <input class='pt10b' type='submit' name='backward' value='����'>
                            </td>
                        </table>
                    </td>
                    <td nowrap align='left' width='250' class='caption_font'>
                        <?= $menu->out_caption(), "\n" ?>
                    </td>
                    <td align='right'>
                        <table align='right' border='3' cellspacing='0' cellpadding='0'>
                            <td align='right'>
                                <input class='pt10b' type='submit' name='forward' value='����'>
                            </td>
                        </table>
                    </td>
                </tr>
            </form>
        </table>
        
        <!--------------- �������饰��դ�ɽ������ -------------------->
        <table width='100%' border='0'>
            <tr>
                <td align='center'>
                <?php
                if ($graph_flg) {
                    echo $graph->GetHTMLImageMap('standard_graph_map');
                    echo "<img src='", $graph_name, "?id=", $uniq, "' ismap usemap='#standard_graph_map' alt='��帶��Ψʬ���� ����� ���� ���' border='0'>\n";
                }
                ?>
                </td>
            </tr>
            <tr>
                <td align='center'>
                <?php
                if ($graph_flg) {
                    echo $graph2->GetHTMLImageMap('standard_graph2_map');
                    echo "<img src='", $graph_name2, "?id=", $uniq, "' ismap usemap='#standard_graph2_map' alt='��帶��Ψʬ���� ������� ��Ψ ���' border='0'>\n";
                }
                ?>
                </td>
            </tr>
        </table>
    </center>
</body>
<?=$menu->out_alert_java()?>
</html>
<?php
ob_end_flush();                 // ���ϥХåե���gzip���� END
?>
