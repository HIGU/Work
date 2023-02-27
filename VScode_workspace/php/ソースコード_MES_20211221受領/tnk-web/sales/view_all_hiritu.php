<?php
//////////////////////////////////////////////////////////////////////////////
// ���ץ顦��˥������ʡ����� ����ܥ����(�������)                      //
// Copyright (C) 2001-2005 2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp //
// Changed history                                                          //
// 2001/10/01 Created   view_all_hiritu.php                                 //
// 2002/07/04 val�����Ρ����ʤʤɤθ��դ��ѹ�                               //
// 2002/07/19 jpgraph 1.5��1.7��VersionUP��ȼ�����饹�λ��ͤ��ѹ��ˤʤä�   //
//            jpgraph 1.5->1.7 ShowValue()��value->Show()��                 //
//                             SetValueFormat��value->SetFormat()��         //
// 2002/08/08 ���å������������ؤ� & register global off �б�             //
// 2002/09/20 �����ȥ�˥塼�����б�                                        //
// 2002/12/20 ����դ����ܸ��б��� jpGraph.php 1.9.1 �ޥ���Х����б��ز�¤ //
//            ���ܸ��� ������ɲ�                                           //
//            $graph->title->SetFont(FF_GOTHIC,FS_NORMAL,14);               //
//            FF_GOTHIC 14 �ʾ� FF_MINCHO �� 17 �ʾ����ꤹ��              //
//            $graph->title->Set(mb_convert_encoding("���� ���ʡ����ʤ���� //
//            ñ�̡�ɴ����","UTF-8"));                                      //
//            $graph->legend->SetFont(FF_GOTHIC,FS_NORMAL,14);              //
//            FF_GOTHIC �� 14 �ʾ� FF_MINCHO �� 17 �ʾ�                     //
//            $b1plot->SetLegend(mb_convert_encoding("�� �� ","UTF-8"));    //
//            �����̾������                                                //
// 2003/05/01 jpGraph 1.12.1 UP �ˤ����Ĵ�� SetMargin() legend->Pos()      //
//       mark->SetWidth() SetLegend("���� ")��("����")��;ʬ�ʥ��ڡ�����  //
// 2003/09/05 ����եե�����ι������Υ����å����ɲ� ��®����ޤä�����     //
//            error_reporting = E_ALL �б��Τ��� �����ѿ��ν�����ɲ�       //
// 2003/11/04 $graph ->yaxis->scale->SetGrace(15)�ɲ� ����դ�ǯ���ϰϤ�  //
// 2003/12/12 define���줿����ǥǥ��쥯�ȥ�ȥ�˥塼����Ѥ��ƴ�������    //
//            ob_start('ob_gzhandler') ���ɲ�                               //
// 2004/12/29 ��������ǯ��� 200204 �� 200304 ���ѹ� (�ڡ���������ɲ�ͽ��) //
//            MenuHeader class ����Ѥ��ƶ��̥�˥塼���ڤ�ǧ���������ѹ�   //
// 2005/02/23 $menu->set_action()�򥳥��ȥ����� �����Ľ�ΤΥ����Ȼ���  //
// 2007/05/31 �ڡ���������ɲ�(����Ȳ�����ӤǤ���)������եǥ�������ѹ�//
// 2007/09/25 ���顼�����å���E_STRICT�� SQLʸ�Υ�����ɤ���ʸ����        //
// 2007/10/01 if ($str_ym < 200010) �� if ($str_ym <= 200010) ������        //
// 2007/10/31 ���ʤ����ʤȤ�ơ��׻����Ƥ��뤿��ͼθ����к��ѥ��å��ɲ�  //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL | E_STRICT);  // E_ALL='2047' debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
ob_start('ob_gzhandler');                   // ���ϥХåե���gzip����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��

require_once ('../../jpgraph.php'); 
require_once ('../../jpgraph_bar.php'); 
require_once ('../function.php');           // define.php �� pgsql.php �� require_once ���Ƥ���
require_once ('../tnk_func.php');           // TNK �˰�¸������ʬ�δؿ��� require_once ���Ƥ���
require_once ('../MenuHeader.php');         // TNK ������ menu class
access_log();                               // Script Name �ϼ�ư����

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader(0);                  // ǧ�ڥ����å�0=���̰ʾ� �����=TOP_MENU �����ȥ�̤����

////////////// ����������
$menu->set_site( 1,  5);                    // site_index=1(����˥塼) site_id=5(���ʡ����ʥ����)
////////////// �꥿���󥢥ɥ쥹����
// $menu->set_RetUrl(SYS_MENU);                // �̾�ϻ��ꤹ��ɬ�פϤʤ�
//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title('���Ρ����ץ顦��˥� ���ʡ����� ��奰���');
//////////// ɽ�������
$menu->set_caption('������������ۤ��Τꤿ������դΰ��֤˹�碌���ɽ������ޤ���');
//////////// �ƽ����action̾�ȥ��ɥ쥹����
// $menu->set_action('���Υ��ץ��˥������',   SALES . 'view_cl_graph.php');
// �嵭�����ꤹ������褬̵�¥롼�פˤʤ��ǽ�������뤿��(�ߤ�������Ȥʤ��)���ǥ�ƥ��ǻ���

///// �ڡ����ꥯ�����Ȥν���
if (isset($_REQUEST['pageNo'])) {
    $pageNo = $_REQUEST['pageNo'];
} else {
    $pageNo = 1;
}
if ($pageNo < 1) $pageNo = 1;
///// ��������ǯ��λ���
$today = date('Ymd');
$query = "
    SELECT to_char(date '{$today}' - interval '{$pageNo} year', 'FMYYYYMM')
";
getUniResult($query, $str_ym);
///// �����ǯ��λ���
if ($pageNo == 1) {
    $query = "
        SELECT to_char(date '{$today}' - interval '{$pageNo} month', 'FMYYYYMM')
    ";
} else {
    $endNo = ($pageNo - 1);
    $query = "
        SELECT to_char(date '{$today}' - interval '{$endNo} year', 'FMYYYYMM')
    ";
}
getUniResult($query, $end_ym);
if ($str_ym <= 200010) {
    // $pageNo -= 1;
    $backward = ' disabled';
} else {
    $backward = '';
}
if ($end_ym < 200010) {
    $end_ym = 200010;
}
if ($pageNo == 1) {
    $forward = ' disabled';
} else {
    $forward = '';
}
// $str_ym = '200604';
// $end_ym = '200704';
//////////// ����եե������¸�ߥ����å�
$graph_name1 = "graph/view_all_hiritu.png";     // ���Τ����ʡ����ʤ���Ψ
$graph_name2 = "graph/view_c_hiritu.png";       // ���ץ�����ʡ����ʤ���Ψ
$graph_name3 = "graph/view_l_hiritu.png";       // ��˥������ʡ����ʤ���Ψ ����� �ե�����̾
if (file_exists($graph_name1)) {
    //////////// ���ߤ�ǯ�����ȥ���եե�����ι������ǡ��������
    $current_date = date("Ymd");
    $file_date    = date("Ymd", filemtime($graph_name1) );
    //////////// ����եե�����ι����������å�
    if ($current_date == $file_date) {
        $create_flg = false;            // ����պ�������
    } else {
        $create_flg = true;             // ����պ���
    }
} else {
    $create_flg = true;                 // ����պ���
}
$create_flg = true;     // ImageMap�����뤿�����١���������ɬ�פ����롣

///////////////// ���Ƥ����ʡ����ʤη��Ψ�����
        // ����������γƷ�ζ�ۤϥ���ե�����򻲾Ȥ���
$query_wrk = "SELECT ǯ��, c����+l���� AS ��������, ����-(c����+l����) AS �������� FROM wrk_uriage WHERE ǯ��>={$str_ym} AND ǯ��<={$end_ym} ORDER BY ǯ�� ASC";
$res_wrk = array();
if ($rows_wrk = getResult($query_wrk, $res_wrk)) {
    $seihin_kin = array();
    $buhin_kin = array();
    $datax    = array();
    for ($cnt=0; $cnt<$rows_wrk; $cnt++) {      // cnt �������ѤΥ����󥿡����Ǥ�Ȥ�
        if (substr(date_offset(1), 0, 6) == $res_wrk[$cnt][0]) {  // ���˥���ե����빹�������к�
            break;
        }
        $datax[$cnt]    = $res_wrk[$cnt][0];
        $seihin_kin[$cnt] = $res_wrk[$cnt][1];
        $buhin_kin[$cnt] = $res_wrk[$cnt][2];
    }
    for ($i=0; $i<$cnt; $i++) {
        $seihin_kin[$i] = Uround($seihin_kin[$i] / 1000000, 1);   // ñ�̤�ɴ���ߤˤ���
        $buhin_kin[$i]  = Uround($buhin_kin[$i] / 1000000, 1);
    }
}

        // �����������٤򻲾Ȥ���
$temp_date = date_offset(1);
$temp_date = substr($temp_date, 0, 6);
$s_date = $temp_date . '01';
$e_date = $temp_date . '31';            // datatype=1=���� ����ʳ������ʤ�����ʬ����
$query = "SELECT �׾���, Uround(����*ñ��, 0) AS ���, datatype FROM hiuuri WHERE �׾���>={$s_date} AND �׾���<={$e_date} ORDER BY �׾��� ASC";
$res = array();
if ($rows = getResult($query,$res)) {
    $datax[$cnt] = substr($res[0][0],0,6);  // X���ι��ܤ�����
    $seihin_kin[$cnt] = 0;                  // �����
    $buhin_kin[$cnt]  = 0;                  // �����
    for ($r=0; $r<$rows; $r++) {                // ����ι�׶�ۤ򻻽�
        if ($res[$r][2] == '1') {
            $seihin_kin[$cnt] += $res[$r][1];
        } else {
            $buhin_kin[$cnt] += $res[$r][1];
        }
    }
    $seihin_kin[$cnt] = Uround($seihin_kin[$cnt] / 1000000, 1);   // ñ�̤�ɴ���ߤˤ���
    $buhin_kin[$cnt]  = Uround($buhin_kin[$cnt] / 1000000, 1);
}
$query = "SELECT Uround(sum(Uround(����*ñ��, 0))/1000000, 6) FROM hiuuri WHERE �׾���>={$s_date} AND �׾���<={$e_date}";
$tan_all = 0;
getUniResult($query, $tan_all);
$buhin_kin[$cnt] = $tan_all - $seihin_kin[$cnt];     // �ͼθ����к����ɲ�


// Create the graph. These two calls are always required 
$graph_all = new Graph(820, 360, 'auto');       // ����դ��礭�� X/Y
$graph_all->SetScale('textlin'); 
$graph_all->img->SetMargin(40, 120, 30, 70);    // ����հ��֤Υޡ����� �����岼
$graph_all->SetShadow(); 
$graph_all->title->SetFont(FF_GOTHIC, FS_NORMAL, 14);   // FF_GOTHIC 14 �ʾ� FF_MINCHO �� 17 �ʾ����ꤹ��
$graph_all->title->Set(mb_convert_encoding("���� ���ʡ����ʤ����   ñ�̡�ɴ����","UTF-8")); 
$graph_all->legend->Pos(0.015, 0.5, "right", "center"); // ����ΰ��ֻ���
$graph_all->legend->SetFont(FF_GOTHIC, FS_NORMAL, 14);  // FF_GOTHIC �� 14 �ʾ� FF_MINCHO �� 17 �ʾ�

// Setup X-scale 
$graph_all->xaxis->SetTickLabels($datax); // ��������
$graph_all->xaxis->SetFont(FF_GOTHIC, FS_NORMAL, 11);   // 2007/05/31 �ѹ�
$graph_all->xaxis->SetLabelAngle(65);


// Create the bar plots 1
$b1plot_all = new BarPlot($seihin_kin); 
$b1plot_all->SetFillColor("orange");
$b1plot_all->SetFillGradient("darkorange3","darkgoldenrod1",GRAD_WIDE_MIDVER);
$targ_all = array();
$alts_all = array();
for ($i=0; $i<=$cnt; $i++) {
    $targ_all[$i] = 'view_cl_graph.php';
    $alts_all[$i] = "���Ρ�����=%3.1f";
}
$b1plot_all->SetCSIMTargets($targ_all, $alts_all);
$b1plot_all->SetLegend(mb_convert_encoding("�� ��", "UTF-8"));  // �����̾������

// Create the bar plots 2
$b2plot_all = new BarPlot($buhin_kin);
$b2plot_all->SetFillColor("blue");
$b2plot_all->SetFillGradient("navy","lightsteelblue",GRAD_WIDE_MIDVER);
for ($i=0; $i<=$cnt; $i++) {
    $targ_all[$i] = 'view_cl_graph.php';
    $alts_all[$i] = "���Ρ�����=%3.1f";
}
$b2plot_all->SetCSIMTargets($targ_all,$alts_all); 
$b2plot_all->SetLegend(mb_convert_encoding("�� ��", "UTF-8"));  // �����̾������

// Create the grouped bar plot 
$abplot_all = new AccBarPlot(array($b1plot_all, $b2plot_all)); 

// $abplot_all->SetShadow(); 
$abplot_all->value->Show(); 
$abplot_all->value->SetFormat("%3.1f"); // ���������塢��������������
$abplot_all->value->SetFont(FF_GOTHIC, FS_NORMAL, 11);  // 2007/05/31 �ɲ�

// ...and add it to the graPH 
$graph_all->Add($abplot_all); 
$graph_all->yaxis->scale->SetGrace(15);        // 2003/11/04 �ɲ� ����դ�ǯ���ϰϤ򾮤�������

// Create and add a new text
$txt= new Text(mb_convert_encoding('������Ǥ�', 'UTF-8'));
$txt->SetPos(730, 300, 'center');
$txt->SetFont(FF_GOTHIC, FS_NORMAL, 11);
$txt->SetBox('darkseagreen1','navy','gray');
$txt->SetColor('red');
$graph_all->AddText($txt);

/*
$graph_all->title->Set("Image map barex2"); // �ƥ����ȥ���� ��ά��ǽ
$graph_all->xaxis->title->Set("X-title"); 
$graph_all->yaxis->title->Set("Y-title"); 
*/

// $graph_all->title->SetFont(FF_FONT1,FS_BOLD); 
// $graph_all->yaxis->title->SetFont(FF_FONT1,FS_BOLD); 
// $graph_all->xaxis->title->SetFont(FF_FONT1,FS_BOLD); 

// Display the graph 
$graph_all->Stroke($graph_name1); 


/////////////////////// ���ץ����ʡ����ʤη��Ψ�����
        // ����������γƷ�ζ�ۤϥ���ե�����򻲾Ȥ���
$query_wrk = "SELECT ǯ��,c����, ���ץ�-c���� as ���� FROM wrk_uriage WHERE ǯ��>={$str_ym} AND ǯ��<={$end_ym} ORDER BY ǯ�� ASC";
$res_wrk = array();
if ($rows_wrk = getResult($query_wrk, $res_wrk)) {
    $seihin_kin = array();
    $buhin_kin = array();
    $datax    = array();
    for ($cnt=0; $cnt<$rows_wrk; $cnt++) {      // cnt �������ѤΥ����󥿡����Ǥ�Ȥ�
        if (substr(date_offset(1),0,6) == $res_wrk[$cnt][0]) {  // ���˥���ե����빹�������к�
            break;
        }
        $datax[$cnt]    = $res_wrk[$cnt][0];
        $seihin_kin[$cnt] = $res_wrk[$cnt][1];
        $buhin_kin[$cnt] = $res_wrk[$cnt][2];
    }
    for ($i=0; $i<$cnt; $i++) {
        $seihin_kin[$i] = Uround($seihin_kin[$i] / 1000000,1);   // ñ�̤�ɴ���ߤˤ���
        $buhin_kin[$i]  = Uround($buhin_kin[$i] / 1000000,1);
    }
}

$temp_date = date_offset(1);
$temp_date = substr($temp_date, 0, 6);
$s_date = $temp_date . '01';
$e_date = $temp_date . '31';            // datatype=1=���� ����ʳ������ʤ�����ʬ����
$query = "SELECT �׾���, Uround(����*ñ��, 0) as ���, datatype FROM hiuuri WHERE �׾���>={$s_date} AND �׾���<={$e_date} AND ������='C' ORDER BY �׾��� ASC";
$res = array();
if ($rows = getResult($query, $res)) {
    $datax[$cnt] = substr($res[0][0], 0, 6);    // X���ι��ܤ�����
    $seihin_kin[$cnt] = 0;                      // �����
    $buhin_kin[$cnt]  = 0;                      // �����
    for ($r=0; $r<$rows; $r++) {                // ����ι�׶�ۤ򻻽�
        if ($res[$r][2] == '1') {
            $seihin_kin[$cnt] += $res[$r][1];
        } else {
            $buhin_kin[$cnt] += $res[$r][1];
        }
    }
    $seihin_kin[$cnt] = Uround($seihin_kin[$cnt] / 1000000, 1);   // ñ�̤�ɴ���ߤˤ���
    $buhin_kin[$cnt]  = Uround($buhin_kin[$cnt] / 1000000, 1);
}
$query = "SELECT Uround(sum(Uround(����*ñ��, 0))/1000000, 6) FROM hiuuri WHERE �׾���>={$s_date} AND �׾���<={$e_date} AND ������='C'";
$tan_coupler_all = 0;
getUniResult($query, $tan_coupler_all);
$buhin_kin[$cnt] = $tan_coupler_all - $seihin_kin[$cnt];     // �ͼθ����к����ɲ�


// Create the graph. These two calls are always required 
$graph_c = new Graph(820, 360, 'auto');         // ����դ��礭�� X/Y
$graph_c->SetScale("textlin"); 
$graph_c->img->SetMargin(40, 120, 30, 70);      // ����հ��֤Υޡ����� �����岼
$graph_c->SetShadow(); 
$graph_c->title->SetFont(FF_GOTHIC,FS_NORMAL,14); // FF_GOTHIC 14 �ʾ� FF_MINCHO �� 17 �ʾ����ꤹ��
$graph_c->title->Set(mb_convert_encoding("���ץ� ���ʡ����ʤ����   ñ�̡�ɴ����","UTF-8")); 
$graph_c->legend->Pos(0.015,0.5,"right","center"); // ����ΰ��ֻ���
$graph_c->legend->SetFont(FF_GOTHIC,FS_NORMAL,14); // FF_GOTHIC �� 14 �ʾ� FF_MINCHO �� 17 �ʾ�
$graph_c->yscale->SetGrace(30);     // Set 30% grace. ;͵��������

// Setup X-scale 
$graph_c->xaxis->SetTickLabels($datax); // ��������
$graph_c->xaxis->SetFont(FF_GOTHIC, FS_NORMAL, 11);   // 2007/05/31 �ѹ�
$graph_c->xaxis->SetLabelAngle(65);


// Create the bar plots 1
$b1plot_c = new BarPlot($seihin_kin);
$b1plot_c->SetFillColor("orange");
$b1plot_c->SetFillGradient("darkorange3","darkgoldenrod1",GRAD_WIDE_MIDVER);
$targ_c = array();
$alts_c = array();
for ($i=0; $i<=$cnt; $i++) {
    $targ_c[$i] = 'view_cl_graph.php';
    $alts_c[$i] = "���ץ顦����=%3.1f";
}
$b1plot_c->SetCSIMTargets($targ_c,$alts_c); 
$b1plot_c->SetLegend(mb_convert_encoding("�� ��","UTF-8"));    // �����̾������

// Create the bar plots 2
$b2plot_c = new BarPlot($buhin_kin);
$b2plot_c->SetFillColor("blue");
$b2plot_c->SetFillGradient("navy","lightsteelblue",GRAD_WIDE_MIDVER);
for ($i=0; $i<=$cnt; $i++) {
    $targ_c[$i] = 'view_cl_graph.php';
    $alts_c[$i] = "���ץ顦����=%3.1f";
}
$b2plot_c->SetCSIMTargets($targ_c,$alts_c); 
$b2plot_c->SetLegend(mb_convert_encoding("�� ��","UTF-8"));    // �����̾������

// Create the grouped bar plot 
$abplot_c = new AccBarPlot(array($b1plot_c,$b2plot_c)); 

// $abplot_c->SetShadow(); 
$abplot_c->value->Show(); 
$abplot_c->value->SetFormat("%3.1f"); // ���������塢��������������
$abplot_c->value->SetFont(FF_GOTHIC, FS_NORMAL, 11);  // 2007/05/31 �ɲ�

// ...and add it to the graPH 
$graph_c->Add($abplot_c); 
$graph_c->yaxis->scale->SetGrace(15);        // 2003/11/04 �ɲ� ����դ�ǯ���ϰϤ򾮤�������

// Create and add a new text
$graph_c->AddText($txt);

/*
$graph_c->title->Set("Image map barex2"); // �ƥ����ȥ���� ��ά��ǽ
$graph_c->xaxis->title->Set("X-title"); 
$graph_c->yaxis->title->Set("Y-title"); 
*/

// $graph_c->title->SetFont(FF_FONT1,FS_BOLD); 
// $graph_c->yaxis->title->SetFont(FF_FONT1,FS_BOLD); 
// $graph_c->xaxis->title->SetFont(FF_FONT1,FS_BOLD); 

// Display the graph 
$graph_c->Stroke($graph_name2); 


////////////////// ��˥����ʡ����ʤη��Ψ�����
    // ����������γƷ�ζ�ۤϥ���ե�����򻲾Ȥ���
$query_wrk = "SELECT ǯ��,l����, ��˥�-l���� as ���� FROM wrk_uriage WHERE ǯ��>={$str_ym} AND ǯ��<={$end_ym} ORDER BY ǯ�� ASC";
$res_wrk = array();
if ($rows_wrk = getResult($query_wrk,$res_wrk)) {
    $seihin_kin = array();
    $buhin_kin = array();
    $datax    = array();
    for ($cnt=0; $cnt<$rows_wrk; $cnt++) {      // cnt �������ѤΥ����󥿡����Ǥ�Ȥ�
        if (substr(date_offset(1),0,6) == $res_wrk[$cnt][0]) {  // ���˥���ե����빹�������к�
            break;
        }
        $datax[$cnt]    = $res_wrk[$cnt][0];
        $seihin_kin[$cnt] = $res_wrk[$cnt][1];
        $buhin_kin[$cnt] = $res_wrk[$cnt][2];
    }
    for ($i=0; $i<$cnt; $i++) {
        $seihin_kin[$i] = Uround($seihin_kin[$i] / 1000000, 1);   // ñ�̤�ɴ���ߤˤ���
        $buhin_kin[$i]  = Uround($buhin_kin[$i] / 1000000, 1);
    }
}

$temp_date = date_offset(1);
$temp_date = substr($temp_date, 0, 6);
$s_date = $temp_date . '01';
$e_date = $temp_date . '31';            // datatype=1=���� ����ʳ������ʤ�����ʬ����
$query = "SELECT �׾���, Uround(����*ñ��, 0) as ���, datatype FROM hiuuri WHERE �׾���>={$s_date} AND �׾���<={$e_date} AND ������='L' ORDER BY �׾��� ASC";
$res = array();
if ($rows = getResult($query, $res)) {
    $datax[$cnt] = substr($res[0][0], 0, 6);    // X���ι��ܤ�����
    $seihin_kin[$cnt] = 0;                      // �����
    $buhin_kin[$cnt]  = 0;                      // �����
    for ($r=0; $r<$rows; $r++) {                // ����ι�׶�ۤ򻻽�
        if ($res[$r][2] == '1') {
            $seihin_kin[$cnt] += $res[$r][1];
        } else {
            $buhin_kin[$cnt] += $res[$r][1];
        }
    }
    $seihin_kin[$cnt] = Uround($seihin_kin[$cnt] / 1000000, 1);   // ñ�̤�ɴ���ߤˤ���
    $buhin_kin[$cnt]  = Uround($buhin_kin[$cnt] / 1000000, 1);
}
$query = "SELECT Uround(sum(Uround(����*ñ��, 0))/1000000, 6) FROM hiuuri WHERE �׾���>={$s_date} AND �׾���<={$e_date} AND ������='L'";
$tan_linear_all = 0;
getUniResult($query, $tan_linear_all);
$buhin_kin[$cnt] = $tan_linear_all - $seihin_kin[$cnt];     // �ͼθ����к����ɲ�


// Create the graph. These two calls are always required 
$graph_l = new Graph(820, 360, 'auto');         // ����դ��礭�� X/Y
$graph_l->SetScale('textlin'); 
$graph_l->img->SetMargin(40, 120, 30, 70);      // ����հ��֤Υޡ����� �����岼
$graph_l->SetShadow(); 
$graph_l->title->SetFont(FF_GOTHIC,FS_NORMAL,14); // FF_GOTHIC 14 �ʾ� FF_MINCHO �� 17 �ʾ����ꤹ��
$graph_l->title->Set(mb_convert_encoding("��˥� ���ʡ����ʤ����   ñ�̡�ɴ����","UTF-8")); 
$graph_l->legend->Pos(0.015,0.5,"right","center"); // ����ΰ��ֻ���
$graph_l->legend->SetFont(FF_GOTHIC,FS_NORMAL,14); // FF_GOTHIC �� 14 �ʾ� FF_MINCHO �� 17 �ʾ�
$graph_l->yscale->SetGrace(90);     // Set 90% grace. ;͵��������

// Setup X-scale 
$graph_l->xaxis->SetTickLabels($datax); // ��������
$graph_l->xaxis->SetFont(FF_GOTHIC, FS_NORMAL, 11);   // 2007/05/31 �ѹ�
$graph_l->xaxis->SetLabelAngle(65);


// Create the bar plots 1
$b1plot_l = new BarPlot($seihin_kin);
$b1plot_l->SetFillColor("orange");
$b1plot_l->SetFillGradient("darkorange3","darkgoldenrod1",GRAD_WIDE_MIDVER);
$targ_l = array();
$alts_l = array();
for ($i=0; $i<=$cnt; $i++) {
    $targ_l[$i] = 'view_cl_graph.php';
    $alts_l[$i] = "��˥�������=%3.1f";
}
$b1plot_l->SetCSIMTargets($targ_l,$alts_l); 
$b1plot_l->SetLegend(mb_convert_encoding("�� ��","UTF-8"));    // �����̾������

// Create the bar plots 2
$b2plot_l = new BarPlot($buhin_kin);
$b2plot_l->SetFillColor("blue");
$b2plot_l->SetFillGradient("navy","lightsteelblue",GRAD_WIDE_MIDVER);
for ($i=0; $i<=$cnt; $i++) {
    $targ_l[$i] = 'view_cl_graph.php';
    $alts_l[$i] = "��˥�������=%3.1f";
}
$b2plot_l->SetCSIMTargets($targ_l,$alts_l); 
$b2plot_l->SetLegend(mb_convert_encoding("�� ��","UTF-8"));    // �����̾������

// Create the grouped bar plot 
$abplot_l = new AccBarPlot(array($b1plot_l,$b2plot_l)); 

// $abplot_l->SetShadow(); 
$abplot_l->value->Show(); 
$abplot_l->value->SetFormat("%3.1f"); // ���������塢��������������
$abplot_l->value->SetFont(FF_GOTHIC, FS_NORMAL, 11);  // 2007/05/31 �ɲ�

// ...and add it to the graPH 
$graph_l->Add($abplot_l); 
$graph_l->yaxis->scale->SetGrace(15);        // 2003/11/04 �ɲ� ����դ�ǯ���ϰϤ򾮤�������

// Create and add a new text
$graph_l->AddText($txt);

/*
$graph_l->title->Set("Image map barex2"); // �ƥ����ȥ���� ��ά��ǽ
$graph_l->xaxis->title->Set("X-title"); 
$graph_l->yaxis->title->Set("Y-title"); 
*/

// $graph_l->title->SetFont(FF_FONT1,FS_BOLD); 
// $graph_l->yaxis->title->SetFont(FF_FONT1,FS_BOLD); 
// $graph_l->xaxis->title->SetFont(FF_FONT1,FS_BOLD); 

// Display the graph 
$graph_l->Stroke($graph_name3); 


/////////// HTML Header ����Ϥ��ƥ���å��������
$menu->out_html_header();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" constent="text/javascript">
<title><?php echo $menu->out_title()?></title>
<?php echo $menu->out_site_java()?>
<?php echo $menu->out_css()?>
<style type='text/css'>
<!--
select      {background-color:teal; color:white;}
textarea        {background-color:black; color:white;}
input.sousin    {background-color:red;}
input.text      {background-color:black; color:white;}
.pt10b      {font-size:10pt; font-weight:bold;}
.pt11           {font-size:11pt;}
.pt12b      {font-size:12pt; font-weight:bold;}
.right      {text-align:right;}
.center     {text-align:center;}
.left           {text-align:left;}
.margin1        {margin:1%;}
.margin0        {margin:0%;}
.fc_red     {color:red;
             background-color:blue;}
.fc_orange      {color:orange;}
.fc_yellow      {color:yellow;
             background-color:blue;}
.fc_white       {color:white;
             background-color:blue;
             font-weight:bold;}
-->
</style>
</head>
<body>
    <center>
    <?php echo $menu->out_title_border()?>
        
        <!----------------- ������ ���� ���� �Υե����� ---------------->
        <table width='100%' border='0' cellspacing='0' cellpadding='0'>
            <tr>
                <form name='page_form' method='post' action='<?php echo $menu->out_self() ?>'>
                    <td align='left'>
                        <table align='left' border='3' cellspacing='0' cellpadding='0'>
                            <td align='left'>
                                <input class='pt10b' type='submit' name='backward' value='����'<?php echo $backward?>>
                                <input type='hidden' name='pageNo' value='<?php echo ($pageNo + 1) ?>'>
                            </td>
                        </table>
                    </td>
                </form>
                    <td nowrap align='center' width='80%' class='caption_font'>
                        <?php echo $menu->out_caption(), "\n" ?>
                    </td>
                <form name='page_form' method='post' action='<?php echo $menu->out_self() ?>'>
                    <td align='right'>
                        <table align='right' border='3' cellspacing='0' cellpadding='0'>
                            <td align='right'>
                                <input class='pt10b' type='submit' name='forward' value='����'<?php echo $forward?>>
                                <input type='hidden' name='pageNo' value='<?php echo ($pageNo - 1) ?>'>
                            </td>
                        </table>
                    </td>
                </form>
            </tr>
        </table>
        
        <!--------------- �������饰��դ�ɽ������ -------------------->
        <table width=100% border='0'>
            <tr><td align='center'>
                <?php
                echo $graph_all->GetHTMLImageMap("all_imagemap"); 
                echo "\n<img src='" . $graph_name1 . "?" . uniqid(rand(),1) . "' alt='���� ���ʡ����ʤ���奰���' ISMAP USEMAP=\"#all_imagemap\" border=0>"; 
                ?>
            </td></tr>
            <tr><td align='center'><?php echo $menu->out_caption() ?></td></tr>
        </table>

        <table align='center' width='70' bgcolor='blue' border='3' cellspacing='0' cellpadding='0'>
            <form method='get' action='<?php echo $menu->out_RetUrl() ?>'>
                <td align='center'><input class='pt12b' type='submit' name='return' value='���'></td>
            </form>
        </table>

        <table width=100% border='0'>
            <tr><td align='center'>
                <?php
                echo $graph_c->GetHTMLImageMap("���ץ�"); 
                echo "\n<img src='". $graph_name2 . "?" . uniqid(rand(),1) . "' alt='���ץ� ���ʡ����ʤ���奰���' ISMAP USEMAP=\"#���ץ�\" border=0>"; 
                ?>
            </td></tr>
            <tr><td align='center'><?php echo $menu->out_caption() ?></td></tr>
        </table>

        <table align='center' width='70' bgcolor='blue' border='3' cellspacing='0' cellpadding='0'>
            <form method='get' action='<?php echo $menu->out_RetUrl() ?>'>
                <td align='center'><input class='pt12b' type='submit' name='return' value='���'></td>
            </form>
        </table>

        <table width=100% border='0'>
            <tr><td align='center'>
                <?php
                echo $graph_l->GetHTMLImageMap("��˥�"); 
                echo "\n<img src='". $graph_name3 . "?" . uniqid(rand(),1) . "' alt='��˥� ���ʡ����ʤ���奰���' ISMAP USEMAP=\"#��˥�\" border=0>"; 
                ?>
            </td></tr>
            <tr><td align='center'><?php echo $menu->out_caption() ?></td></tr>
        </table>
    </center>
</body>
<?php echo $menu->out_alert_java()?>
</html>
<?php
ob_end_flush();                 // ���ϥХåե���gzip���� END
?>
