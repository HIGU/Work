<?php
//////////////////////////////////////////////////////////////////////////////
//�ץ���೫ȯ ���ա���λ��̤��λ ��� �����                             //
// Copyright(C) 2002-2004 2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp  //
// History                                                                  //
// 2002/02/10 Created dev_req_graph2.php                                    //
// 2002/02/12 ����դ�MARK���ѹ�                                            //
// 2002/07/04 �ǿ��η��λ���Ϥ�������Ƥ��Ƽ��դ���Ƥ��ʤ������к�      //
// 2002/07/05 $datax ��ǡ����١�������Ǥʤ��׻��ǵ��롣                 //
// 2002/08/09   register_globals = Off �б�                                 //
// 2002/12/20 ����դ����ܸ��б��� jpGraph.php 1.9.1 �ޥ���Х����б��ز�¤ //
//            ���ܸ��� ������ɲ�                                           //
//            $graph->title->SetFont(FF_GOTHIC,FS_NORMAL,14);               //
//            FF_GOTHIC 14 �ʾ� FF_MINCHO �� 17 �ʾ����ꤹ��              //
//            $graph->title->Set(mb_convert_encoding("???????","UTF-8"));   //
//            $graph->legend->SetFont(FF_GOTHIC,FS_NORMAL,14);              //
//            FF_GOTHIC �� 14 �ʾ� FF_MINCHO �� 17 �ʾ�                     //
//            $p1->SetLegend(mb_convert_encoding(""���շ��"","UTF-8"));    //
//            �����̾������                                                //
// 2004/07/20 MenuHeader Class ���ɲ�                                       //
// 2007/09/19 E_ALL | E_STRICT �б��إ��å��ѹ�                           //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��
require_once ('../function.php');           // TNK ������ function
require_once ('../MenuHeader.php');         // TNK ������ menu class
require_once ('../../jpgraph.php'); 
require_once ('../../jpgraph_line.php'); 
access_log();                               // Script Name �ϼ�ư����

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader(0);  // ǧ�ڥ�٥�=0, �꥿���󥢥ɥ쥹�ʤ�=���å������, �����ȥ�λ���ʤ�

////////////// ����������
$menu->set_site(4, 4);      // site_index=4(�ץ���೫ȯ) site_id=4(̤��λ��������)
////////////// �꥿���󥢥ɥ쥹����
// $menu->set_RetUrl(DEV_MENU);     // ���å���������
//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title('�ץ���೫ȯ ��λ��̤��λ ��������');
//////////// ɽ�������
$menu->set_caption('�ץ���೫ȯ���ӥ����');
//////////// �ƽ����action̾�ȥ��ɥ쥹����
// $menu->set_action('��ȯ����Ȳ�', DEV . 'dev_req_select.php');   // ���ߤϸƽ���ʤ�

/////////// ����եǡ�������
    // �Τΰ�������2001-01-01������Ϲ�׷���Τߡˤ���� ͥ���١�X�����
$query = "select ������,��ȯ���� from dev_req where ͥ����<>'X' and ��ȶ�='1' and ������<'2001-01-01' order by ������ asc";
$res = array();
if ($rows = getResult($query, $res)) {
    $cnt = 0;                   //�����ѤΥ����󥿡�
    $tuki_cnt = array();
    $datax    = array();
    $datax[$cnt] = "2001-01";
    for ($r=0; $r<$rows; $r++){             // 2001-01-01������ι�׷���򻻽�
        if (isset($tuki_cnt[$cnt])) {
            $tuki_cnt[$cnt] += 1;
        } else {
            $tuki_cnt[$cnt] = 1;
        }
    }
}
                            // �Ʒ�� ������ ����� ͥ���١�X�����
$query = "select ������,��ȯ���� from dev_req where ͥ����<>'X' and ��ȶ�='1' and ������>='2001-01-01' order by ������ asc";
$res = array();
if ($rows = getResult($query, $res)) {
    $start_flg = 0;             // �������Ȼ��Υե饰
    $cnt = 0;                   // �����ѤΥ����󥿡�
    //  $tuki_cnt = array();
    //  $datax    = array();
    for ($r=0; $r<$rows; $r++) {        // �Ʒ���ι�׷���򻻽�
        $yyyy_mm = substr($res[$r][0], 0, 7);
        if ($start_flg == 0) {
            $tuki_cnt[$cnt] += 1;
            $datax[$cnt]    = $yyyy_mm;
            $start_flg      = 1;
        } elseif ($datax[$cnt] == $yyyy_mm) {
            $tuki_cnt[$cnt] += 1;
        } else {
            $cnt += 1;
            if (isset($tuki_cnt[$cnt])) {
                $tuki_cnt[$cnt] += 1;
            } else {
                $tuki_cnt[$cnt]  = 1;
            }
            $datax[$cnt]     = $yyyy_mm;
        }
    }
    $rui_cnt = array();             // ���Ѥ򻻽�
    for($i=0;$i<=$cnt;$i++){
        if($i==0)
            $rui_cnt[$i] = $tuki_cnt[$i];
        else
            $rui_cnt[$i] = $tuki_cnt[$i] + $rui_cnt[$i-1];
    }
}
$tuki_uketuke = $tuki_cnt;
$rui_uketuke = $rui_cnt;


                            // �Ʒ�δ�λ�������� ��λ����2001-01-01���ȯ��
$query = "select ��λ��,��ȯ���� from dev_req where (��λ��<>'1970-01-01' or ��λ��<>NULL) and ��ȶ�='1' order by ��λ�� asc";
$res = array();
if ($rows = getResult($query, $res)) {
    $start_flg = 0;             //�������Ȼ��Υե饰
    $cnt = 0;                   //�����ѤΥ����󥿡�
    $tuki_cnt = array();
    for ($r=0; $r<$rows; $r++) {            // �Ʒ���ι�׷���򻻽�
        if ($start_flg == 0) {
            $tuki_cnt[$cnt] = 1;
            $start_flg      = 1;
        } elseif (substr($res[$r][0],0,7) == substr($res[$r-1][0],0,7)) {
            $tuki_cnt[$cnt] += 1;
        } else {
            $cnt += 1;
            if (isset($tuki_cnt[$cnt])) {
                $tuki_cnt[$cnt] += 1;
            } else {
                $tuki_cnt[$cnt]  = 1;
            }
        }
    }
    $rui_cnt = array();             // ���Ѥ򻻽�
    for ($i=0; $i<=$cnt; $i++) {
        if ($i == 0)
            $rui_cnt[$i] = $tuki_cnt[$i];
        else
            $rui_cnt[$i] = $tuki_cnt[$i] + $rui_cnt[$i-1];
    }
}
$tuki_kan = $tuki_cnt;
$rui_kan = $rui_cnt;


            // �Ʒ�� ̤��λ �����׻�
$tuki_mikan = array();
for ($i=0; $i<=$cnt; $i++) {
    if ( (!isset($rui_uketuke[$i])) || $rui_uketuke[$i] == 0)   // �ǿ��η��λ���Ϥ�������Ƥ��Ƽ��դ���Ƥ��ʤ������к�
        $rui_uketuke[$i] = $rui_uketuke[$i-1];
    $tuki_mikan[$i] = $rui_uketuke[$i] - $rui_kan[$i];
}


$yyyy = 2001;
$mm   = 01;
$yyyymm = $yyyy . $mm;
$j = 0;
while ( $yyyymm <= (int) date('Ym')) {
    $datax[$j] = $yyyy . '/' . sprintf('%02s', $mm);
    $j++;
    $mm++;
    if ($mm > 12) {
        $yyyy++;
        $mm = 01;
    }
    $yyyymm = $yyyy . sprintf('%02s', $mm);     // 20012 �� 200102 ���Ѵ�����
}


// A nice graph with anti-aliasing 
$graph = new Graph(770, 350, "auto");           // ����դ��礭�� X/Y
$graph->img->SetMargin(30, 160, 30, 70);        // ����հ��֤Υޡ����� �����岼
$graph->SetScale("textlin"); 
$graph->SetShadow(); 
//$graph->title->Set("Line plot with null values"); 
$graph->title->SetFont(FF_GOTHIC,FS_NORMAL,14); // FF_GOTHIC 14 �ʾ� FF_MINCHO �� 17 �ʾ����ꤹ��
$graph->title->Set(mb_convert_encoding("�ץ���೫ȯ �����߷ס����ա���λ��̤��λ ��������","UTF-8")); 
$graph->yscale->SetGrace(10);     // Set 10% grace. ;͵��������
$graph->yaxis->SetColor("blue");
$graph->yaxis->SetWeight(2);

// Use built in font 
// $graph->title->SetFont(FF_FONT1,FS_BOLD); 

// Slightly adjust the legend from it's default position in the 
// top right corner. 
$graph->legend->Pos(0.02,0.5,"right","center");  // ����ΰ��ֻ���
$graph->legend->SetFont(FF_GOTHIC,FS_NORMAL,14); // FF_GOTHIC �� 14 �ʾ� FF_MINCHO �� 17 �ʾ�

// Setup X-scale 
$graph->xaxis->SetTickLabels($datax); 
$graph->xaxis->SetFont(FF_FONT1,FS_BOLD); 
$graph->xaxis->SetLabelAngle(90); 

// Create the first line 
//  MARK_SQUARE, A filled square
//  MARK_UTRIANGLE, A upward pointing triangle
//  MARK_DTRIANGLE, A downward pointing triangle
//  MARK_DIAMOND, A diamond shape
//  MARK_CIRCLE, A non-filled circle.
//  MARK_FILLEDCIRCLE, A filled circle
//  MARK_STAR
$p1 = new LinePlot($tuki_uketuke); 
$p1->mark->SetType(MARK_FILLEDCIRCLE); 
$p1->mark->SetFillColor("blue"); 
$p1->mark->SetWidth(4); 
$p1->SetColor("blue"); 
$p1->SetCenter(); 
// $p1->SetLegend(" uketuke"); 
$p1->SetLegend(mb_convert_encoding("���շ��","UTF-8"));    // �����̾������
$graph->Add($p1); 

// ... and the second Y Scale
$graph->SetY2Scale("lin");      // Y2����������ɲ�
$graph->y2axis->SetColor("red");    // Y2��������ο�
$graph->y2axis->SetWeight(2);   // Y2�������������(���ɥå�)
$graph->y2scale->SetGrace(10);  // Set 10% grace. ;͵��������
$py2 = new LinePlot($rui_uketuke);  // ����ܤΥ饤��ץ�åȥ��饹�����
//  $py2->mark->SetType(MARK_STAR);     // �ץ�åȥޡ����η�
$py2->mark->SetType(MARK_SQUARE);   // �ץ�åȥޡ����η�
$py2->mark->SetFillColor("red");    // �ץ�åȥޡ����ο�
$py2->mark->SetWidth(4);        // �ץ�åȥޡ������礭��
$py2->SetColor("red");          // �ץ�å����ο�
$py2->SetCenter();          // �ץ�åȤ������
$py2->SetWeight(1);         // �ץ�å���������(���ɥå�)
// $py2->SetLegend(" uketuke-rui");     // ����Ϻ��
$py2->SetLegend(mb_convert_encoding("�����߷�","UTF-8"));    // �����̾������
$graph->AddY2($py2);            // Y2���������ѤΥץ�åȣ����ɲ�

// ... and the second
$p2 = new LinePlot($tuki_kan); 
$p2->mark->SetType(MARK_STAR); 
$p2->mark->SetFillColor("black"); 
$p2->mark->SetWidth(4); 
$p2->SetColor("black"); 
$p2->SetCenter(); 
$p2->SetWeight(2);
// $p2->SetLegend(" kanryou"); 
$p2->SetLegend(mb_convert_encoding("��λ���","UTF-8"));    // �����̾������
$graph->Add($p2); 

// ... and the third
$p3 = new LinePlot($tuki_mikan); 
//  $p3->mark->SetType(MARK_STAR); 
$p3->mark->SetType(MARK_UTRIANGLE); 
$p3->mark->SetFillColor("orange"); 
$p3->mark->SetWidth(4); 
$p3->SetColor("orange"); 
$p3->SetCenter(); 
$p3->SetWeight(2);
// $p3->SetLegend(" mikanryo"); 
$p3->SetLegend(mb_convert_encoding("̤��λ","UTF-8"));    // �����̾������
$graph->Add($p3); 

// Output line 
$graph->Stroke("graph/dev_req_graph2.png"); 


//////////////// ����եǡ���������
// A nice graph with anti-aliasing 
$graph = new Graph(770,350,"auto");         // ����դ��礭�� X/Y
$graph->img->SetMargin(30,130,30,70);       // ����հ��֤Υޡ����� �����岼
$graph->SetScale("textlin"); 
$graph->SetShadow(); 
//$graph->title->Set("Line plot with null values"); 
$graph->title->SetFont(FF_GOTHIC,FS_NORMAL,14); // FF_GOTHIC 14 �ʾ� FF_MINCHO �� 17 �ʾ����ꤹ��
$graph->title->Set(mb_convert_encoding("�ץ���೫ȯ ���ա���λ��̤��λ ��������","UTF-8")); 
$graph->yscale->SetGrace(10);     // Set 10% grace. ;͵��������
$graph->yaxis->SetColor("blue");
$graph->yaxis->SetWeight(2);

// Use built in font 
// $graph->title->SetFont(FF_FONT1,FS_BOLD); 

// Slightly adjust the legend from it's default position in the 
// top right corner. 
$graph->legend->Pos(0.02,0.5,"right","center");  // ����ΰ��ֻ���
$graph->legend->SetFont(FF_GOTHIC,FS_NORMAL,14); // FF_GOTHIC �� 14 �ʾ� FF_MINCHO �� 17 �ʾ�

// Setup X-scale 
$graph->xaxis->SetTickLabels($datax); 
$graph->xaxis->SetFont(FF_FONT1,FS_BOLD); 
$graph->xaxis->SetLabelAngle(90); 

// Create the first line 
$p1 = new LinePlot($tuki_uketuke); 
$p1->mark->SetType(MARK_FILLEDCIRCLE); 
$p1->mark->SetFillColor("blue"); 
$p1->mark->SetWidth(4); 
$p1->SetColor("blue"); 
$p1->SetCenter(); 
// $p1->SetLegend(" uketuke"); 
$p1->SetLegend(mb_convert_encoding("���շ��","UTF-8"));    // �����̾������
$graph->Add($p1); 

/*
// ... and the second Y Scale
$graph->SetY2Scale("lin");      // Y2����������ɲ�
$graph->y2axis->SetColor("red");    // Y2��������ο�
$graph->y2axis->SetWeight(2);   // Y2�������������(���ɥå�)
$graph->y2scale->SetGrace(10);  // Set 10% grace. ;͵��������
$py2 = new LinePlot($rui_uketuke);  // ����ܤΥ饤��ץ�åȥ��饹�����
$py2->mark->SetType(MARK_STAR);     // �ץ�åȥޡ����η�
$py2->mark->SetFillColor("red");    // �ץ�åȥޡ����ο�
$py2->mark->SetWidth(4);        // �ץ�åȥޡ������礭��
$py2->SetColor("red");          // �ץ�å����ο�
$py2->SetCenter();          // �ץ�åȤ������
$py2->SetWeight(1);         // �ץ�å���������(���ɥå�)
// $py2->SetLegend(" uketuke-rui");     // ����Ϻ��
$py2->SetLegend(mb_convert_encoding("�����߷�","UTF-8"));    // �����̾������
$graph->AddY2($py2);            // Y2���������ѤΥץ�åȣ����ɲ�
*/

// ... and the second
$p2 = new LinePlot($tuki_kan); 
$p2->mark->SetType(MARK_STAR); 
$p2->mark->SetFillColor("black"); 
$p2->mark->SetWidth(4); 
$p2->SetColor("black"); 
$p2->SetCenter(); 
$p2->SetWeight(2);
// $p2->SetLegend(" kanryou"); 
$p2->SetLegend(mb_convert_encoding("��λ���","UTF-8"));    // �����̾������
$graph->Add($p2); 

// ... and the third
$p3 = new LinePlot($tuki_mikan); 
$p3->mark->SetType(MARK_UTRIANGLE); 
$p3->mark->SetFillColor("orange"); 
$p3->mark->SetWidth(4); 
$p3->SetColor("orange"); 
$p3->SetCenter(); 
$p3->SetWeight(2);
// $p3->SetLegend(" mikanryo"); 
$p3->SetLegend(mb_convert_encoding("̤��λ","UTF-8"));    // �����̾������
$graph->Add($p3); 

// Output line 
$graph->Stroke("graph/dev_req_graph3.png"); 


////////////// ����եǡ���������
// A nice graph with anti-aliasing 
$graph = new Graph(770,350,"auto");         // ����դ��礭�� X/Y
$graph->img->SetMargin(30,130,30,70);       // ����հ��֤Υޡ����� �����岼
$graph->SetScale("textlin"); 
$graph->SetShadow(); 
//$graph->title->Set("Line plot with null values"); 
$graph->title->SetFont(FF_GOTHIC,FS_NORMAL,14); // FF_GOTHIC 14 �ʾ� FF_MINCHO �� 17 �ʾ����ꤹ��
$graph->title->Set(mb_convert_encoding("�ץ���೫ȯ ��λ��̤��λ ��������","UTF-8")); 
$graph->yscale->SetGrace(10);     // Set 10% grace. ;͵��������
$graph->yaxis->SetColor("blue");
$graph->yaxis->SetWeight(2);

// Use built in font 
// $graph->title->SetFont(FF_FONT1,FS_BOLD); 

// Slightly adjust the legend from it's default position in the 
// top right corner. 
$graph->legend->Pos(0.02,0.5,"right","center");  // ����ΰ��ֻ���
$graph->legend->SetFont(FF_GOTHIC,FS_NORMAL,14); // FF_GOTHIC �� 14 �ʾ� FF_MINCHO �� 17 �ʾ�

// Setup X-scale 
$graph->xaxis->SetTickLabels($datax); 
$graph->xaxis->SetFont(FF_FONT1,FS_BOLD); 
$graph->xaxis->SetLabelAngle(90); 

/*
// Create the first line 
$p1 = new LinePlot($tuki_uketuke); 
$p1->mark->SetType(MARK_FILLEDCIRCLE); 
$p1->mark->SetFillColor("blue"); 
$p1->mark->SetWidth(4); 
$p1->SetColor("blue"); 
$p1->SetCenter(); 
// $p1->SetLegend(" uketuke"); 
$p1->SetLegend(mb_convert_encoding("���շ��","UTF-8"));    // �����̾������
$graph->Add($p1); 
*?

/*
// ... and the second Y Scale
$graph->SetY2Scale("lin");      // Y2����������ɲ�
$graph->y2axis->SetColor("red");    // Y2��������ο�
$graph->y2axis->SetWeight(2);   // Y2�������������(���ɥå�)
$graph->y2scale->SetGrace(10);  // Set 10% grace. ;͵��������
$py2 = new LinePlot($rui_uketuke);  // ����ܤΥ饤��ץ�åȥ��饹�����
$py2->mark->SetType(MARK_STAR);     // �ץ�åȥޡ����η�
$py2->mark->SetFillColor("red");    // �ץ�åȥޡ����ο�
$py2->mark->SetWidth(4);        // �ץ�åȥޡ������礭��
$py2->SetColor("red");          // �ץ�å����ο�
$py2->SetCenter();          // �ץ�åȤ������
$py2->SetWeight(1);         // �ץ�å���������(���ɥå�)
// $py2->SetLegend(" uketuke-rui");     // ����Ϻ��
$py2->SetLegend(mb_convert_encoding("�����߷�","UTF-8"));    // �����̾������
$graph->AddY2($py2);            // Y2���������ѤΥץ�åȣ����ɲ�
*/

// ... and the second
$p2 = new LinePlot($tuki_kan); 
$p2->mark->SetType(MARK_STAR); 
$p2->mark->SetFillColor("black"); 
$p2->mark->SetWidth(4); 
$p2->SetColor("black"); 
$p2->SetCenter(); 
$p2->SetWeight(2);
// $p2->SetLegend(" kanryou"); 
$p2->SetLegend(mb_convert_encoding("��λ���","UTF-8"));    // �����̾������
$graph->Add($p2); 

// ... and the third
$p3 = new LinePlot($tuki_mikan); 
$p3->mark->SetType(MARK_UTRIANGLE); 
$p3->mark->SetFillColor("orange"); 
$p3->mark->SetWidth(4); 
$p3->SetColor("orange"); 
$p3->SetCenter(); 
$p3->SetWeight(2);
// $p3->SetLegend(" mikanryo"); 
$p3->SetLegend(mb_convert_encoding("̤��λ","UTF-8"));    // �����̾������
$graph->Add($p3); 

// Output line 
$graph->Stroke("graph/dev_req_graph4.png"); 

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
</head>
<body>
    <center>
<?= $menu->out_title_border() ?>
        
        <table align='center' with=100% border='0'>
            <tr>
                <td align='center'>
        <!--        <h3>�ץ���೫ȯ ��λ��̤��λ ��������<br> -->
                    <img src='graph/dev_req_graph4.png?<?php echo uniqid(rand(),1) ?>' alt='��ȯ���ա���λ��̤��λ ��������' border='0'>
                </td>
        <!--        <td align='center' nowrap>
                    <img src='graph/graph1_legend1.png?<?php echo uniqid(rand(),1) ?>' alt='����' border='0'>
                </td>
        --> </tr>
        </table>
        
        <table align='center' with=100% border='2' cellspacing='0' cellpadding='0'>
            <form action='<?= $menu->out_RetUrl() ?>' method='post'>
                <td width='60' bgcolor='blue'align='center' valign='center'><input class='ret_font' type='submit' name='dev_req_graph2.php' value='���' ></td>
            </form>
        </table>
        
        <table align='center' with=100% border='0'>
            <tr>
                <td align='center'>
        <!--        <h3>�ץ���೫ȯ ���ա���λ��̤��λ ��������<br> -->
                    <img src='graph/dev_req_graph3.png?<?php echo uniqid(rand(),1) ?>' alt='��ȯ���ա���λ��̤��λ ��������' border='0'>
                </td>
        <!--        <td align='center' nowrap>
                    <img src='graph/graph1_legend1.png?<?php echo uniqid(rand(),1) ?>' alt='����' border='0'>
                </td>
        --> </tr>
        </table>
        
        <table align='center' with=100% border='2' cellspacing='0' cellpadding='0'>
            <form action='<?= $menu->out_RetUrl() ?>' method='post'>
                <td width='60' bgcolor='blue'align='center' valign='center'><input class='ret_font' type="submit" name="dev_req_graph2.php" value="���" ></td>
            </form>
        </table>
        
        <table align='center' with=100% border='0'>
            <tr>
                <td align='center'>
        <!--        <h3>�ץ���೫ȯ �����߷ס����ա���λ��̤��λ ��������<br> -->
                    <img src='graph/dev_req_graph2.png?<?php echo uniqid(rand(),1) ?>' alt='��ȯ���ա���λ��̤��λ ��������' border='0'>
                </td>
        <!--        <td align='center' nowrap>
                    <img src='graph/graph1_legend1.png?<?php echo uniqid(rand(),1) ?>' alt='����' border='0'>
                </td>
        --> </tr>
        </table>
    </center>
</table>
</body>
</html>
 