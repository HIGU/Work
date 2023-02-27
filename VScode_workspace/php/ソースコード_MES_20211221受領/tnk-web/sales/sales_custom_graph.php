<?php
//////////////////////////////////////////////////////////////////////////////
// ��� ���� �����ץ����� �Ȳ�                                            //
// Copyright (C) 2005-2005 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2005/01/21 Created   sales_custom_graph.php                              //
// 2005/01/27 ��������Ǥʤ��������Υ���դ��ɲ�                        //
// 2005/06/05 �ǽ�����31�Υ�ƥ�뤫�� last_day()�Ǽ������ѹ�               //
// 2005/08/21 jpGraph2.0beta��UP�ˤ��valueɽ������SetValuePos('center')�ɲ�//
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
$menu->set_site( 1, 13);                    // site_index=01(����˥塼) site_id=11(�����ץ����)
////////////// �꥿���󥢥ɥ쥹����
// $menu->set_RetUrl(SALES_MENU);              // �̾�ϻ��ꤹ��ɬ�פϤʤ�(����˥塼)
//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title('��奰��� �����ץ����� �Ȳ�');
//////////// �ƽ����action̾�ȥ��ɥ쥹����
$menu->set_action('�������', SALES . 'sales_custom_view.php');
$menu->set_retGET('sum_exec', '���ɽ�Ȳ�');

//////////// JavaScript Stylesheet File ��ɬ���ɤ߹��ޤ���
$uniq = uniqid('target');

//////////// ������ʬ�Υǡ���ȴ�Ф�function
function getCustomSales($strYm)
{
    if ( ($strYm < 200310) || ($strYm > date('Ym')) ) {
        return FALSE;
    }
    ///// SQLʸ����Ω
    $last_day = last_day(substr($strYm, 0, 4), substr($strYm, 4, 2));
    $d_start = ($strYm . '01');
    $d_end   = ($strYm . $last_day);
    $where= "where
                    �׾���>={$d_start} and �׾���<={$d_end} and ������='C' and note15 like 'SC%' {$_SESSION['custom_where_assy_no']}
    ";
    //////// ��
    $query = ($_SESSION['costom_sql'] . $where . $_SESSION['custom_condition1']);
    if (getResult($query, $res_sum) <= 0) {
        $_SESSION['s_sysmsg'] .= '�ǡ����١����������Фǥ��顼��ȯ�����ޤ���������ô���Ԥ�Ϣ���Ʋ�������';
        return FALSE;
    }
    $data[0] = $res_sum[0];
    //////// ��
    $query = ($_SESSION['costom_sql'] . $where . $_SESSION['custom_condition2']);
    if (getResult($query, $res_sum) <= 0) {
        $_SESSION['s_sysmsg'] .= '�ǡ����١����������Фǥ��顼��ȯ�����ޤ���������ô���Ԥ�Ϣ���Ʋ�������';
        return FALSE;
    }
    $data[1] = $res_sum[0];
    //////// ��
    $query = ($_SESSION['costom_sql'] . $where . $_SESSION['custom_condition3']);
    if (getResult($query, $res_sum) <= 0) {
        $_SESSION['s_sysmsg'] .= '�ǡ����١����������Фǥ��顼��ȯ�����ޤ���������ô���Ԥ�Ϣ���Ʋ�������';
        return FALSE;
    }
    $data[2] = $res_sum[0];
    //////// ��
    $query = ($_SESSION['costom_sql'] . $where . $_SESSION['custom_condition4']);
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
    $_SESSION['custom_graph_exec'] = $_REQUEST['graph_exec'];
    $graph_exec = $_SESSION['custom_graph_exec'];
} else {
    $graph_exec = $_SESSION['custom_graph_exec'];
}

$uri_passwd = $_SESSION['s_uri_passwd'];
$div        = $_SESSION['custom_div'];
$d_start    = $_SESSION['custom_d_start'];
$d_end      = $_SESSION['custom_d_end'];
$kubun      = $_SESSION['custom_kubun'];
$uri_ritu   = 52;   // ��ƥ����ѹ�
$assy_no    = $_SESSION['custom_assy_no'];

////////////// �ѥ���ɥ����å�
if ($uri_passwd != date('Ymd')) {
    $_SESSION['s_sysmsg'] = "<font color='yellow'>�ѥ���ɤ��㤤�ޤ���</font>";
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // ľ���θƽи������
    exit();
}

//////////// ɽ�������
// $menu->set_caption('��=������ʤΡ������󤬻���ñ��<br>��=�������Σ������󤬻���ñ��<br>��=�������Σ������󤬻���ñ��<br>��=����¾�ʾ嵭�ʳ��ˡ���������');
$menu->set_caption('��=����¾�ʲ����ʳ��ˡ���������<br>��=�������Σ������󤬻���ñ��<br>��=�������Σ������󤬻���ñ��<br>��=������ʤΡ������󤬻���ñ��');

//////////// ������դ�ɽ�����
switch ($graph_exec) {
case 1:
    define('PAGE', 3);
    $graph_title  = '�����ץ� ������ ����� ��奰���';
    $graph2_title = '�����ץ� ������ ����� ������񥰥��';
    break;
case 2:
    define('PAGE', 6);
    $graph_title  = '�����ץ� ������ ����� ��奰���';
    $graph2_title = '�����ץ� ������ ����� ������񥰥��';
    break;
case 3:
    define('PAGE', 12);
    $graph_title  = '�����ץ� �������� ����� ��奰���';
    $graph2_title = '�����ץ� �������� ����� ������񥰥��';
    break;
default:
    define('PAGE', 12);
    $graph_title  = '�����ץ� �������� ����� ��奰���';
    $graph2_title = '�����ץ� �������� ����� ������񥰥��';
}

//////////// ��� ͭ����� ����
$ym = date('Ym');   // ���ߤ�ǯ�� �����
$i = 0;
while ($ym > 200310) {
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
if ( isset($_POST['forward']) ) {                       // ���Ǥ������줿
    $_SESSION['custom_graph_offset'] += PAGE;
    if ($_SESSION['custom_graph_offset'] >= $maxrows) {
        $_SESSION['custom_graph_offset'] -= PAGE;
        if ($_SESSION['s_sysmsg'] == '') {
            $_SESSION['s_sysmsg'] .= "<font color='yellow'>���ǤϤ���ޤ���</font>";
        } else {
            $_SESSION['s_sysmsg'] .= "<br><font color='yellow'>���ǤϤ���ޤ���</font>";
        }
    }
} elseif ( isset($_POST['backward']) ) {                // ���Ǥ������줿
    $_SESSION['custom_graph_offset'] -= PAGE;
    if ($_SESSION['custom_graph_offset'] < 0) {
        $_SESSION['custom_graph_offset'] = 0;
        if ($_SESSION['s_sysmsg'] == '') {
            $_SESSION['s_sysmsg'] .= "<font color='yellow'>���ǤϤ���ޤ���</font>";
        } else {
            $_SESSION['s_sysmsg'] .= "<br><font color='yellow'>���ǤϤ���ޤ���</font>";
        }
    }
} elseif ( isset($_GET['page_keep']) ) {                // ���ߤΥڡ�����ݻ����� GET�����
    $offset = $_SESSION['custom_graph_offset'];
} elseif ( isset($_POST['page_keep']) ) {               // ���ߤΥڡ�����ݻ�����
    $offset = $_SESSION['custom_graph_offset'];
} else {
    $_SESSION['custom_graph_offset'] = 0;               // ���ξ��ϣ��ǽ����
}
$offset = $_SESSION['custom_graph_offset'];

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
for ($i=0; $i<PAGE; $i++) {
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
        if ($i == 0) $graph_flg = FALSE;            // �����ܤǥǡ�����̵����Х���դϺ��ʤ�
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
    }
}
/////////// ���������
if ($graph_flg) {
    require_once ('../../jpgraph.php');
    require_once ('../../jpgraph_bar.php');
    
    /* ################################## ����Υ���պ��� ############################### */
    $graph = new Graph(820, 360);               // ����դ��礭�� X/Y
    $graph->SetScale('textlin'); 
    $graph->img->SetMargin(50, 160, 40, 50);    // ����հ��֤Υޡ����� �����岼
    $graph->SetShadow(); 
    // $graph->SetMarginColor('#d6d3ce');          // ɽ��ɸ�࿧�˹�碌��
    
    // Setup title
    $graph->title->SetFont(FF_GOTHIC, FS_NORMAL, 14);   // FF_GOTHIC 14 �ʾ� FF_MINCHO �� 17 �ʾ����ꤹ��
    $graph->title->Set(mb_convert_encoding($graph_title, 'UTF-8')); 
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
    $bplot0->SetLegend(mb_convert_encoding('��������(��)', 'UTF-8'));   // �����̾������ \n����Ѳ�
    for ($j=0; $j<PAGE; $j++) {
        $targ[$j] = $menu->out_action('�������') . "?ym_p={$ym_p[$j]}&custom_view1=on')";
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
    $bplot1->SetLegend(mb_convert_encoding('��������(��)', 'UTF-8'));   // �����̾������ \n����Ѳ�
    for ($j=0; $j<PAGE; $j++) {
        $targ[$j] = $menu->out_action('�������') . "?ym_p={$ym_p[$j]}&custom_view2=on')";
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
    $bplot2->SetLegend(mb_convert_encoding('��������(��)', 'UTF-8'));   // �����̾������ \n����Ѳ�
    for ($j=0; $j<PAGE; $j++) {
        $targ[$j] = $menu->out_action('�������') . "?ym_p={$ym_p[$j]}&custom_view3=on')";
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
    $bplot3->SetLegend(mb_convert_encoding('������¾(��)', 'UTF-8'));   // �����̾������ \n����Ѳ�
    for ($j=0; $j<PAGE; $j++) {
        $targ[$j] = $menu->out_action('�������') . "?ym_p={$ym_p[$j]}&custom_view4=on')";
        $alts[$j] = "{$ym_p[$j]}������ �� ��ۡ�%0.1f";
    }
    $bplot3->SetCSIMTargets($targ, $alts); 
    
    // Create the grouped bar plot
    $gbplot = new AccBarPlot(array($bplot0, $bplot1, $bplot2, $bplot3));
    $gbplot->value->SetFormat('%01.1f');    // �����ե����ޥå�
    $gbplot->value->Show();                 // ����ɽ��
    
    // Create the graph
    $graph->Add($gbplot);
    // $graph_name = ('graph/sales_custom' . session_id() . '.png');
    $graph_name = 'graph/sales_custom_graph.png';
    $graph->Stroke($graph_name);
    
    
    /* ################################## �������Υ���պ��� ############################### */
    $graph2 = new Graph(820, 360);               // ����դ��礭�� X/Y
    $graph2->SetScale('textlin'); 
    $graph2->img->SetMargin(50, 160, 40, 50);    // ����հ��֤Υޡ����� �����岼
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
    $bplotM0->SetLegend(mb_convert_encoding('��������(��)', 'UTF-8'));   // �����̾������ \n����Ѳ�
    for ($j=0; $j<PAGE; $j++) {
        $targ[$j] = $menu->out_action('�������') . "?ym_p={$ym_p[$j]}&custom_view1=on')";
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
    $bplotM1->SetLegend(mb_convert_encoding('��������(��)', 'UTF-8'));   // �����̾������ \n����Ѳ�
    for ($j=0; $j<PAGE; $j++) {
        $targ[$j] = $menu->out_action('�������') . "?ym_p={$ym_p[$j]}&custom_view2=on')";
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
    $bplotM2->SetLegend(mb_convert_encoding('��������(��)', 'UTF-8'));   // �����̾������ \n����Ѳ�
    for ($j=0; $j<PAGE; $j++) {
        $targ[$j] = $menu->out_action('�������') . "?ym_p={$ym_p[$j]}&custom_view3=on')";
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
    $bplotM3->SetLegend(mb_convert_encoding('������¾(��)', 'UTF-8'));   // �����̾������ \n����Ѳ�
    for ($j=0; $j<PAGE; $j++) {
        $targ[$j] = $menu->out_action('�������') . "?ym_p={$ym_p[$j]}&custom_view4=on')";
        $alts[$j] = "{$ym_p[$j]}��������� �� ��ۡ�%0.1f";
    }
    $bplotM3->SetCSIMTargets($targ, $alts); 
    
    // Create the grouped bar plot
    $gbplotM = new AccBarPlot(array($bplotM0, $bplotM1, $bplotM2, $bplotM3));
    $gbplotM->value->SetFormat('%01.1f');    // �����ե����ޥå�
    $gbplotM->value->Show();                 // ����ɽ��
    
    // Create the graph
    $graph2->Add($gbplotM);
    // $graph2_name = ('graph/sales_custom' . session_id() . '.png');
    $graph_name2 = 'graph/sales_custom_graph_material.png';
    $graph2->Stroke($graph_name2);
} else {
    if (isset($_REQUEST['forward'])) $_SESSION['s_sysmsg'] = '���ǤϤ���ޤ���';
    elseif (isset($_REQUEST['backward'])) $_SESSION['s_sysmsg'] = '���ǤϤ���ޤ���';
    else $_SESSION['s_sysmsg'] = '����եǡ���������ޤ���';
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
                    <td nowrap align='center' class='caption_font'>
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
                    echo $graph->GetHTMLImageMap('custom_graph_map');
                    echo "<img src='", $graph_name, "?id=", $uniq, "' ismap usemap='#custom_graph_map' alt='���ץ����� ����� ��奰���' border='0'>\n";
                }
                ?>
                </td>
            </tr>
            <tr>
                <td align='center'>
                <?php
                if ($graph_flg) {
                    echo $graph2->GetHTMLImageMap('custom_graph2_map');
                    echo "<img src='", $graph_name2, "?id=", $uniq, "' ismap usemap='#custom_graph2_map' alt='���ץ����� ����� ������񥰥��' border='0'>\n";
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
