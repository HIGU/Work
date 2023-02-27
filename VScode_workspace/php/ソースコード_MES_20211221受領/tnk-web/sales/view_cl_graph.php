<?php
//////////////////////////////////////////////////////////////////////////////
// ���ץ顦��˥�������ܥ����(�������)                                 //
// Copyright (C)2001-2005 2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp  //
// Changed history                                                          //
// 2001/10/01 Created   view_cl_graph.php                                   //
// 2002/07/02 ����դ˹�碌��ɽ�򥫥ץ�ϥ���󥸡���˥��ϥ֥롼���ѹ�    //
// 2002/07/19 jpgraph 1.5��1.7��VersionUP��ȼ�����饹�λ��ͤ��ѹ��ˤʤä�   //
// 2002/08/08 ���å������������ؤ����������ܥ�����ɲ�                  //
// 2002/09/20 �����ȥ�˥塼�����б�         ��register global off �б�     //
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
//            mark->SetWidth()                                              //
// 2003/09/05 ����եե�����ι������Υ����å����ɲ� ��®����ޤä�����     //
//            error_reporting = E_ALL �б��Τ��� �����ѿ��ν�����ɲ�       //
// 2003/11/04 $graph ->yaxis->scale->SetGrace(15)�ɲ� ����դ�ǯ���ϰϤ�  //
// 2003/12/12 define���줿����ǥǥ��쥯�ȥ�ȥ�˥塼����Ѥ��ƴ�������    //
//            ob_start('ob_gzhandler') ���ɲ�                               //
// 2004/12/29 ��������ǯ��� 200204 �� 200304 ���ѹ� (�ڡ���������ɲ�ͽ��) //
//            MenuHeader class ����Ѥ��ƶ��̥�˥塼���ڤ�ǧ���������ѹ�   //
// 2005/02/23 $menu->set_action()�򥳥��ȥ����� �����Ľ�ΤΥ����Ȼ���  //
// 2007/09/25 �ڡ���������ɲ�(����Ȳ�����ӤǤ���)������եǥ�������ѹ�//
//            ���顼�����å���E_STRICT�� SQLʸ�Υ�����ɤ���ʸ����        //
// 2007/10/01 if ($str_ym < 200010) �� if ($str_ym <= 200010) ������        //
// 2007/10/31 php�����ɤ�SQLʸ��Uround()�ؿ����ɲ�                          //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL | E_STRICT);       // E_ALL='2047' debug ��
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
$menu->set_site( 1,  6);                    // site_index=1(����˥塼) site_id=6(CL�����)
////////////// �꥿���󥢥ɥ쥹����
// $menu->set_RetUrl(SYS_MENU);                // �̾�ϻ��ꤹ��ɬ�פϤʤ�
//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title('���ץ�ȥ�˥��������');
//////////// ɽ�������
$menu->set_caption('�����Ψ(%)');
//////////// �ƽ����action̾�ȥ��ɥ쥹����
// $menu->set_action('���Υ��ץ��˥������',   SALES . 'view_all_hiritu.php');
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
// $str_ym = '200504';
//////////// ����եե������¸�ߥ����å�
$graph_name = 'graph/view_cl_graph.png';
if (file_exists($graph_name)) {
    //////////// ���ߤ�ǯ�����ȥ���եե�����ι������ǡ��������
    $current_date = date("Ymd");
    $file_date    = date("Ymd", filemtime($graph_name) );
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

/////////////// ���������
        // ����������γƷ�ζ�ۤϥ���ե�����򻲾Ȥ���
$query_wrk = "SELECT ǯ��, ���ץ�, ��˥� FROM wrk_uriage WHERE ǯ��>={$str_ym} AND ǯ��<={$end_ym} ORDER BY ǯ�� ASC";
$res_wrk = array();
if ($rows_wrk = getResult($query_wrk,$res_wrk)) {
    $tuki_kin_c = array();
    $tuki_kin_l = array();
    $datax    = array();
    for ($cnt=0; $cnt<$rows_wrk; $cnt++) {      // cnt �������ѤΥ����󥿡����Ǥ�Ȥ�
        if (substr(date_offset(1), 0, 6) == $res_wrk[$cnt][0]) {  // ���˥���ե����빹�������к�
            break;
        }
        $datax[$cnt]    = $res_wrk[$cnt][0];
        $tuki_kin_c[$cnt] = $res_wrk[$cnt][1];
        $tuki_kin_l[$cnt] = $res_wrk[$cnt][2];
    }
    $rui_kin_c = array();
    $rui_kin_l = array();
    for ($i=0; $i<$cnt; $i++) {             // ����ե������ʬ�����Ѥ���롣���ץ�ȥ�˥�
        if ($i==0) {
            $rui_kin_c[$i] = $tuki_kin_c[$i];
            $rui_kin_l[$i] = $tuki_kin_l[$i];
        } else {
            $rui_kin_c[$i] = $tuki_kin_c[$i] + $rui_kin_c[$i-1];
            $rui_kin_l[$i] = $tuki_kin_l[$i] + $rui_kin_l[$i-1];
        }
    }
    for ($i=0; $i<$cnt; $i++) {
        $tuki_kin_c[$i] = Uround($tuki_kin_c[$i] / 1000000, 1);   // ñ�̤�ɴ���ߤˤ���
        $rui_kin_c[$i]  = Uround($rui_kin_c[$i] / 1000000, 1);
        $tuki_kin_l[$i] = Uround($tuki_kin_l[$i] / 1000000, 1);   // ñ�̤�ɴ���ߤˤ���
        $rui_kin_l[$i]  = Uround($rui_kin_l[$i] / 1000000, 1);
    }
}

$temp_date = date_offset(1);                    // ����ʬ�Υ��ץ��������٤��齦����
$temp_date = substr($temp_date, 0, 6);
$s_date = $temp_date . '01';
$e_date = $temp_date . '31';
$query = "SELECT �׾���, Uround(����*ñ��, 0) AS ��� FROM hiuuri WHERE �׾���>={$s_date} AND �׾���<={$e_date} AND ������='C' ORDER BY �׾��� ASC";
$res = array();
if ($rows = getResult($query,$res)) {
    $cnt_c = $cnt;                  // ���ץ�������ͤ�cnt�ͤˤ���
    $datax[$cnt] = $temp_date;          // X���ι��ܤ�����
    $tuki_kin_c[$cnt] = 0;              // ����� ����ʬ������
    for ($r=0; $r<$rows; $r++) {                // ����ι�׶�ۤ򻻽�
        $tuki_kin_c[$cnt] += $res[$r][1];       // ���ץ�
    }
    $rui_kin_c[$cnt] = $tuki_kin_c[$cnt] + $rui_kin_c[$cnt-1];      // ����ʬ�����Ѥ�������ɲä��롣
    
    $tuki_kin_c[$cnt] = Uround($tuki_kin_c[$cnt] / 1000000, 1);       // ñ�̤�ɴ���ߤˤ���
    $rui_kin_c[$cnt]  = Uround($rui_kin_c[$cnt] / 1000000, 1);
} else {
    $cnt_c = $cnt - 1;              // ����Υǡ�����̵����Х��ץ�������ͤ�-1����
}
$query = "SELECT �׾���, Uround(����*ñ��, 0) AS ��� FROM hiuuri WHERE �׾���>={$s_date} AND �׾���<={$e_date} AND ������='L' ORDER BY �׾��� ASC";
$res = array();
if ($rows = getResult($query,$res)) {
    $cnt_l = $cnt;                  // ��˥��������ͤ�cnt�ͤˤ���
    $datax[$cnt] = $temp_date;          // X���ι��ܤ�����
    $tuki_kin_l[$cnt] = 0;              // ����� ����ʬ
    for ($r=0; $r<$rows; $r++) {                  // ����ι�׶�ۤ򻻽�
        $tuki_kin_l[$cnt] += $res[$r][1];   // ��˥�
    }
    $rui_kin_l[$cnt] = $tuki_kin_l[$cnt] + $rui_kin_l[$cnt-1];      // ����ʬ�����Ѥ�������ɲä��롣
    
    $tuki_kin_l[$cnt] = Uround($tuki_kin_l[$cnt] / 1000000, 1);       // ñ�̤�ɴ���ߤˤ���
    $rui_kin_l[$cnt]  = Uround($rui_kin_l[$cnt] / 1000000, 1);
} else {
    $cnt_l = $cnt - 1;              // ����Υǡ�����̵����Х�˥��������ͤ�-1����
}

////////// ��������������å�
if ($create_flg) {
    //$datax =array("2001/04","2001/05","2001/06","2001/07","2001/08","2001/09","2001/10","2001/11","2001/12","2002/01","2002/02","2002/03");
    //$tuki_kin_c=array(   342.3 ,   347.1 ,   347.6 ,   338.7 ,   319.5 ,   378.5 ,   336.7 ,   321.8 ,   267.2 ,   241.3 ,       0 ,       0 ); // ���ץ�
    //$tuki_kin_l=array(   125.9 ,   129.2 ,   151.6 ,   126.0 ,   141.5 ,   113.0 ,    96.2 ,    86.1 ,   107.1 ,    91.4 ,       0 ,       0 ); // ��˥�
    
    // Create the graph. These two calls are always required 
    $graph = new Graph(820, 360, 'auto');       // ����դ��礭�� X/Y
    $graph->SetScale('textlin'); 
    $graph->img->SetMargin(40, 120, 30, 70);    // ����հ��֤Υޡ����� �����岼
    $graph->SetShadow(); 
    $graph->title->SetFont(FF_GOTHIC,FS_NORMAL, 14);    // FF_GOTHIC 14 �ʾ� FF_MINCHO �� 17 �ʾ����ꤹ��
    $graph->title->Set(mb_convert_encoding('���ץ顦��˥��������Ψ   ñ�̡�ɴ����', 'UTF-8')); 
    $graph->legend->Pos(0.015, 0.5, 'right', 'center'); // ����ΰ��ֻ���
    $graph->legend->SetFont(FF_GOTHIC,FS_NORMAL, 14);   // FF_GOTHIC �� 14 �ʾ� FF_MINCHO �� 17 �ʾ�
    
    // Setup X-scale 
    $graph->xaxis->SetTickLabels($datax); // ��������
    $graph->xaxis->SetFont(FF_GOTHIC, FS_NORMAL, 11);   // �ե���Ȥϥܡ���ɤ����Ǥ��롣
    $graph->xaxis->SetLabelAngle(65); 
    
    
    // Create the bar plots 
    $b1plot = new BarPlot($tuki_kin_c); 
    $b1plot->SetFillColor('orange'); 
    $b1plot->SetFillGradient('darkorange3', 'darkgoldenrod1', GRAD_WIDE_MIDVER);
    $targ = array();
    $alts = array();
    for ($i=0; $i<=$cnt_c; $i++) {
        $targ[$i] = 'view_all_hiritu.php';
        $alts[$i] = '���ץ�=%3.1f';
    }
    //$targ=array("view_c_hiritu.php","view_c_hiritu.php","view_c_hiritu.php","view_c_hiritu.php","view_c_hiritu.php","view_c_hiritu.php",
    //            "view_c_hiritu.php","view_c_hiritu.php","view_c_hiritu.php","view_c_hiritu.php","view_c_hiritu.php","view_c_hiritu.php"); 
    //$alts=array("val=%3.1f","val=%3.1f","val=%3.1f","val=%3.1f","val=%3.1f","val=%3.1f","val=%3.1f","val=%3.1f","val=%3.1f",
    //            "val=%3.1f","val=%3.1f","val=%3.1f"); 
    $b1plot->SetCSIMTargets($targ, $alts); 
    // $b1plot->SetLegend('CUPLA');    // �����̾������
    $b1plot->SetLegend(mb_convert_encoding('���ץ�', 'UTF-8'));    // �����̾������
    
    $b2plot = new BarPlot($tuki_kin_l); 
    $b2plot->SetFillColor('blue'); 
    $b2plot->SetFillGradient('navy', 'lightsteelblue', GRAD_WIDE_MIDVER);
    $targ = array();
    $alts = array();
    for ($i=0; $i<=$cnt_l; $i++) {
        $targ[$i] = 'view_all_hiritu.php';
        $alts[$i] = '��˥�=%3.1f';
    }
    //$targ=array("view_l_hiritu.php","view_l_hiritu.php","view_l_hiritu.php","view_l_hiritu.php","view_l_hiritu.php","view_l_hiritu.php",
    //            "view_l_hiritu.php","view_l_hiritu.php","view_l_hiritu.php","view_l_hiritu.php","view_l_hiritu.php","view_l_hiritu.php"); 
    //$alts=array("val=%3.1f","val=%3.1f","val=%3.1f","val=%3.1f","val=%3.1f","val=%3.1f","val=%3.1f","val=%3.1f","val=%3.1f",
    //            "val=%3.1f","val=%3.1f","val=%3.1f"); 
    $b2plot->SetCSIMTargets($targ, $alts); 
    // $b2plot->SetLegend("LINEAR");    // �����̾������
    $b2plot->SetLegend(mb_convert_encoding('��˥�', 'UTF-8'));    // �����̾������
    
    // Create the grouped bar plot 
    $abplot = new AccBarPlot(array($b1plot, $b2plot)); 
    
    // $abplot->SetShadow();    // 2007/09/25 �����ȥ�����
    // 2002/07/19 jpgraph 1.5->1.7 ShowValue()��value->Show()��
    $abplot->value->Show(); 
    $abplot->value->SetFormat('%3.1f'); // ���������塢��������������
    // 2002/07/19 end
    $abplot->value->SetFont(FF_GOTHIC, FS_NORMAL, 11);  // 2007/09/25 �ɲ�
    
    // ...and add it to the graPH 
    $graph->Add($abplot);
    $graph ->yaxis->scale->SetGrace(15);        // 2003/11/04 �ɲ� ����դ�ǯ���ϰϤ򾮤�������
    
    // Create and add a new text 2007/09/25 ADD
    $txt= new Text(mb_convert_encoding('������Ǥ�', 'UTF-8'));
    $txt->SetPos(730, 300, 'center');
    $txt->SetFont(FF_GOTHIC, FS_NORMAL, 11);
    $txt->SetBox('darkseagreen1','navy','gray');
    $txt->SetColor('red');
    $graph->AddText($txt);
    
    // Display the graph 
    $graph->Stroke($graph_name); 
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
<title><?php echo $menu->out_title() ?></title>
<?php echo $menu->out_site_java() ?>
<?php echo $menu->out_css() ?>
<style type="text/css">
<!--
select      {background-color:teal; color:white;}
textarea        {background-color:black; color:white;}
input.sousin    {background-color:red;}
input.text      {background-color:black; color:white;}
.pt10b      {font-size:0.80em; font-weight:bold;}
.pt11           {font-size:11pt;}
.pt12b      {font:bold 12pt;}
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
<body style='overflow:hidden;'>
    <center>
<?php echo $menu->out_title_border() ?>
        
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
        <table width='100%'>
            <tr><td align='center'>
                <?php
                echo $graph->GetHTMLImageMap("myimagemap"); 
                echo "\n<img src='" . $graph_name . "?" . uniqid(rand(),1) . "' ISMAP USEMAP='#myimagemap' border=0>"; 
                ?>
            </td></tr>
            <tr><td align='center'>������������ۤ��Τꤿ������դΰ��֤˹�碌���ɽ������ޤ���</td></tr>
            <tr><td align='center'>���ץ顦��˥��γƥ���դ򥯥�å���������ʡ����ʤ���Ψ����դ�ɽ�����ޤ���</td></tr>
        </table>
        <table width='400' align='center' border='1' bordercolor='teal' cellspacing='0' cellpadding='3'>
            <th >------</th>
            <th bgcolor='orange'>���ץ�</th>
            <th class='fc_white'>��˥�</th>
            <th>����</th>
            <tr>
                <td align='center'>������</td>
                <td align='right' bgcolor='orange'><?php echo number_format($tuki_kin_c[$cnt_c], 1) ?></td>
                <td align='right' class='fc_white'><?php echo number_format($tuki_kin_l[$cnt_l], 1) ?></td>
                <td align='right'><?php echo number_format($tuki_kin_c[$cnt_c]+$tuki_kin_l[$cnt_l], 1) ?></td>
            </tr>
            <tr>
                <td align='center'><?php echo $menu->out_caption()?></td>
                <td align='right' bgcolor='orange'><?php echo number_format(($tuki_kin_c[$cnt_c] / ($tuki_kin_c[$cnt_c] + $tuki_kin_l[$cnt_l])) * 100, 1) ?></td>
                <td align='right' class='fc_white'><?php echo number_format(100 - (($tuki_kin_c[$cnt_c] / ($tuki_kin_c[$cnt_c] + $tuki_kin_l[$cnt_l])) * 100), 1) ?></td>
                <td align='right'>100.0</td>
            </tr>
            <tr>
                <td align='center'>�߷�</td>
                <td align='right' bgcolor='orange'><?php echo number_format($rui_kin_c[$cnt_c]+$rui_kin_c[$cnt_c-1], 1) ?></td>
                <td align='right' class='fc_white'><?php echo number_format($rui_kin_l[$cnt_l]+$rui_kin_l[$cnt_l-1], 1) ?></td>
                <td align='right'><?php echo number_format($rui_kin_c[$cnt_c]+$rui_kin_l[$cnt_l]+$rui_kin_c[$cnt_c-1]+$rui_kin_l[$cnt_l-1], 1) ?></td>
            </tr>
            <tr>
                <td align='center'><?php echo $menu->out_caption()?></td>
                <td align='right' bgcolor='orange'><?php echo number_format(( ($rui_kin_c[$cnt_c]+$rui_kin_c[$cnt_c-1]) / ($rui_kin_c[$cnt_c]+$rui_kin_c[$cnt_c-1] + $rui_kin_l[$cnt_l]+$rui_kin_l[$cnt_l-1])) * 100, 1) ?></td>
                <td align='right' class='fc_white'><?php echo number_format(100 - (( ($rui_kin_c[$cnt_c]+$rui_kin_c[$cnt_c-1]) / ($rui_kin_c[$cnt_c]+$rui_kin_c[$cnt_c-1] + $rui_kin_l[$cnt_l]+$rui_kin_l[$cnt_l-1])) * 100), 1) ?></td>
                <td align='right'>100.0</td>
            </tr>
        </table>
    </center>
</body>
<?php echo $menu->out_alert_java()?>
</html>
<?php
ob_end_flush();                 // ���ϥХåե���gzip���� END
?>
