<?php
//////////////////////////////////////////////////////////////////////////////
// �ץ���೫ȯ���� ���������                                            //
// Copyright(C) 2002-2004 2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp  //
// History                                                                  //
// 2002/02/01 Created dev_req_graph_jisseki.php                             //
// 2002/02/26 ����Τߤ򹩿����ɲ�                                          //
// 2002/08/09 register_globals = Off �б�                                   //
// 2002/12/20 ����դ����ܸ��б��� jpGraph.php 1.9.1 �ޥ���Х����б��ز�¤ //
//            ���ܸ��� ������ɲ�                                           //
//            $graph->title->SetFont(FF_GOTHIC,FS_NORMAL,14);               //
//            FF_GOTHIC 14 �ʾ� FF_MINCHO �� 17 �ʾ����ꤹ��              //
//            $graph->title->Set(mb_convert_encoding("???????","UTF-8"));   //
//            $graph->legend->SetFont(FF_GOTHIC,FS_NORMAL,14);              //
//            FF_GOTHIC �� 14 �ʾ� FF_MINCHO �� 17 �ʾ�                     //
//            $p1->SetLegend(mb_convert_encoding(""���շ��"","UTF-8"));    //
//            �����̾������                                                //
// 2003/12/12 define���줿����ǥǥ��쥯�ȥ�ȥ�˥塼̾����Ѥ���          //
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
$menu->set_site(4, 3);      // site_index=4(�ץ���೫ȯ) site_id=3(���ӥ����)
////////////// �꥿���󥢥ɥ쥹����
// $menu->set_RetUrl(DEV_MENU);     // ���å���������
//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title('�ץ���೫ȯ���� ��������������');
//////////// ɽ�������
$menu->set_caption('�ץ���೫ȯ���ӥ����');
//////////// �ƽ����action̾�ȥ��ɥ쥹����
// $menu->set_action('��ȯ����Ȳ�', DEV . 'dev_req_select.php');   // ���ߤϸƽ���ʤ�

///////////// �����������
$query = "select ��λ��,��ȯ���� from dev_req where (��λ��<>'1970-01-01' or ��λ��<>NULL) and ��ȶ�='1' order by ��λ�� asc";
$res = array();
if ($rows = getResult($query,$res)) {
    $start_flg = 0;             //�������Ȼ��Υե饰
    $cnt = 0;                   //�����ѤΥ����󥿡�
    $tuki_cnt = array();
    $tuki_kousuu = array();
    $datax    = array();
    for ($r=0; $r<$rows; $r++) {                // �Ʒ���ι�׷���򻻽�
        $yyyymm = substr($res[$r][0],0,7);
        if ($start_flg == 0) {
            $tuki_cnt[$cnt] = 1;
            $tuki_kousuu[$cnt] = $res[$r][1];
            $datax[$cnt]    = $yyyymm;
            $start_flg      = 1;
        } elseif ($datax[$cnt]==$yyyymm) {
            $tuki_cnt[$cnt] += 1;
            $tuki_kousuu[$cnt] += $res[$r][1];
        } else {
            $cnt += 1;
            $datax[$cnt]     = $yyyymm;
            if (isset($tuki_cnt[$cnt])) {
                $tuki_cnt[$cnt] += 1;
                $tuki_kousuu[$cnt] += $res[$r][1];
            } else {
                $tuki_cnt[$cnt] = 1;
                $tuki_kousuu[$cnt] = $res[$r][1];
            }
        }
    }
    $rui_cnt = array();
    $rui_kousuu = array();
    for ($i=0; $i<=$cnt; $i++) {
        if ($i == 0) {
            $rui_cnt[$i] = $tuki_cnt[$i];
            $rui_kousuu[$i] = $tuki_kousuu[$i];
        } else {
            $rui_cnt[$i] = $tuki_cnt[$i] + $rui_cnt[$i-1];
            $rui_kousuu[$i] = $tuki_kousuu[$i] + $rui_kousuu[$i-1];
        }
    }
}
// Some data 
//$datax = array("2001-04","2001-05","2001-06","2001-07","2001-08","2001-09"); 
//$datay  = array(5,9,15,21,25,32); 
//$data2y = array(5,4, 6,6,4,7); 


// A nice graph with anti-aliasing 
$graph = new Graph(770,350,"auto");           // ����դ��礭�� X/Y
$graph->img->SetMargin(30,110,30,60);  // ����հ��֤Υޡ����� �����岼
$graph->SetScale("textlin"); 
$graph->SetShadow(); 
//$graph->title->Set("Line plot with null values"); 
$graph->title->SetFont(FF_GOTHIC,FS_NORMAL,14); // FF_GOTHIC 14 �ʾ� FF_MINCHO �� 17 �ʾ����ꤹ��
$graph->title->Set(mb_convert_encoding("��ȯ���ӷ�������","UTF-8")); 
$graph->yscale->SetGrace(10);     // Set 10% grace. ;͵��������
$graph->yaxis->SetColor("blue");
$graph->yaxis->SetWeight(2);

// Use built in font 
// $graph->title->SetFont(FF_FONT1,FS_BOLD); 

// Slightly adjust the legend from it's default position in the 
// top right corner. 
$graph->legend->Pos(0.015,0.5,"right","center");  // ����ΰ��ֻ���
$graph->legend->SetFont(FF_GOTHIC,FS_NORMAL,14); // FF_GOTHIC �� 14 �ʾ� FF_MINCHO �� 17 �ʾ�

// Setup X-scale 
$graph->xaxis->SetTickLabels($datax); 
$graph->xaxis->SetFont(FF_FONT1); 
$graph->xaxis->SetLabelAngle(90); 

// Create the first line 
$p1 = new LinePlot($rui_cnt); 
$p1->mark->SetType(MARK_FILLEDCIRCLE); 
$p1->mark->SetFillColor("blue"); 
$p1->mark->SetWidth(4); 
$p1->SetColor("blue"); 
$p1->SetCenter(); 
//$p1->SetLegend(" ruiseki"); 
$p1->SetLegend(mb_convert_encoding("�߷�","UTF-8"));    // �����̾������
$graph->Add($p1); 

// ... and the second 
$graph->SetY2Scale("lin");      // Y2���������ɲ�
$graph->y2axis->SetWeight(2);       // Y2��������������ɥå�
$graph->y2axis->SetColor("black");  // Y2��������ο�
$graph->y2scale->SetGrace(10);  // Set 10% grace. ;͵��������
$p2 = new LinePlot($tuki_cnt); 
$p2->mark->SetType(MARK_STAR); 
$p2->mark->SetFillColor("red"); 
$p2->mark->SetWidth(4); 
$p2->SetColor("black");
$p2->SetCenter(); 
//$p2->SetLegend(" month"); 
$p2->SetLegend(mb_convert_encoding("�","UTF-8"));    // �����̾������
//  $graph->Add($p2); 
$graph->AddY2($p2);
//  $graph->SetColor("red");

// Output line 
$graph->Stroke("graph/dev_req_graph1.png"); 
// echo $graph->GetHTMLImageMap("myimagemap"); 
// echo "<img src=\"".GenImgName()."\" ISMAP USEMAP=\"#myimagemap\" border=0>"; 


//////////// �����������
// A nice graph with anti-aliasing 
$graph = new Graph(770,350,"auto");           // ����դ��礭�� X/Y
$graph->img->SetMargin(50,130,30,60);  // ����հ��֤Υޡ����� �����岼
$graph->SetScale("textlin"); 
$graph->SetShadow(); 
//$graph->title->Set("Line plot with null values"); 
$graph->title->SetFont(FF_GOTHIC,FS_NORMAL,14); // FF_GOTHIC 14 �ʾ� FF_MINCHO �� 17 �ʾ����ꤹ��
$graph->title->Set(mb_convert_encoding("��ȯ���ӹ��������    ñ��:ʬ","UTF-8")); 
$graph->yscale->SetGrace(10);     // Set 10% grace. ;͵��������
$graph->yaxis->SetColor("blue");
$graph->yaxis->SetWeight(2);

// Use built in font 
// $graph->title->SetFont(FF_FONT1,FS_BOLD); 

// Slightly adjust the legend from it's default position in the 
// top right corner. 
$graph->legend->Pos(0.015,0.5,"right","center");  // ����ΰ��ֻ���
$graph->legend->SetFont(FF_GOTHIC,FS_NORMAL,14); // FF_GOTHIC �� 14 �ʾ� FF_MINCHO �� 17 �ʾ�

// Setup X-scale 
$graph->xaxis->SetTickLabels($datax); 
$graph->xaxis->SetFont(FF_FONT1); 
$graph->xaxis->SetLabelAngle(90); 

// Create the first line 
$p1 = new LinePlot($rui_kousuu); 
$p1->mark->SetType(MARK_FILLEDCIRCLE); 
$p1->mark->SetFillColor("blue"); 
$p1->mark->SetWidth(4); 
$p1->SetColor("blue"); 
$p1->SetCenter(); 
//$p1->SetLegend(" ruiseki"); 
$p1->SetLegend(mb_convert_encoding("�߷�","UTF-8"));    // �����̾������
$graph->Add($p1); 

// ... and the second 
$graph->SetY2Scale("lin");      // Y2���������ɲ�
$graph->y2axis->SetWeight(2);       // Y2��������������ɥå�
$graph->y2axis->SetColor("purple");     // Y2��������ο�
$graph->y2scale->SetGrace(10);  // Set 10% grace. ;͵��������
$p2 = new LinePlot($tuki_kousuu); 
$p2->mark->SetType(MARK_STAR); 
$p2->mark->SetFillColor("red"); 
$p2->mark->SetWidth(4); 
$p2->SetColor("purple");
$p2->SetCenter(); 
//$p2->SetLegend(" month"); 
$p2->SetLegend(mb_convert_encoding("�","UTF-8"));    // �����̾������
//  $graph->Add($p2); 
$graph->AddY2($p2);
//  $graph->SetColor("red");

// Output line 
$graph->Stroke("graph/dev_req_graph_kousuu.png"); 
// echo $graph->GetHTMLImageMap("myimagemap"); 
// echo "<img src=\"".GenImgName()."\" ISMAP USEMAP=\"#myimagemap\" border=0>"; 

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
        <!--        <font size='5'><b>��ȯ���ӷ�������<b></font><br> -->
                    <img src='graph/dev_req_graph1.png?<?php echo uniqid(rand(),1) ?>' alt='��ȯ���ӷ�������' border='0'>
                </td>
        <!--    <td align='center' nowrap>
                    <img src='graph/graph1_legend1.png?<?php echo uniqid(rand(),1) ?>' alt='����' border='0'>
                </td>
        --> </tr>
        </table>
        
        <table align='center' with=100% border='1' bordercolor='navy'>
            <th>-</th>
            <?php
                for ($i=0; $i<=$cnt; $i++)
                    print("<th>$datax[$i]</th>\n");
            ?>
            <tr>
                <td align='center' nowrap><font color='black'>���</font></td>
                <?php
                for ($i=0; $i<=$cnt; $i++)
                    print("<td align='right'><font color='black'><b>$tuki_cnt[$i]</b></font></td>");
                ?>
            </tr>
            <tr>
                <td align='center' nowrap><font color='blue'>����</font></td>
                <?php
                for ($i=0; $i<=$cnt; $i++)
                    print("<td align='right'><font color='blue'>$rui_cnt[$i]</font></td>");
                ?>
            </tr>
        </table>
        
        <table align='center' with=100% border='2' cellspacing='0' cellpadding='0'>
            <form action='<?= $menu->out_RetUrl() ?>' method='post'>
                <td width='60' bgcolor='blue'align='center' valign='center'><input class='ret_font' type="submit" name="dev_req_graph1" value="���" ></td>
            </form>
        </table>
        
        <table align='center' with=100% border='0'>
            <tr>
                <td align='center'>
        <!--        <font size='5'><b>��ȯ ���� ���� ����� </b></font><font size='2'> ñ�̡�ʬ</font><br> -->
                    <img src='graph/dev_req_graph_kousuu.png?<?php echo uniqid(rand(),1) ?>' alt='��ȯ���ӹ��������' border='0'>
        <!--    </td>
                <td align='center' nowrap>
                    <img src='graph/graph1_legend1.png?<?php echo uniqid(rand(),1) ?>' alt='����' border='0'>
                </td>
        --> </tr>
        </table>
        
        <table align='center' with=100% border='1' bordercolor='navy'>
            <th>-</th>
            <?php
                for ($i=0; $i<=$cnt; $i++)
                    print("<th>$datax[$i]</th>\n");
            ?>
            <tr>
                <td align='center' nowrap><font color='purple'>����(ʬ)</font></td>
                <?php
                for ($i=0; $i<=$cnt; $i++)
                    print("<td align='right'><font color='purple'><b>" . number_format($tuki_kousuu[$i]) . "</b></font></td>");
                ?>
            </tr>
            <tr>
                <td align='center' nowrap><font color='blue'>����(ʬ)</font></td>
                <?php
                for ($i=0; $i<=$cnt; $i++)
                    print("<td align='right'><font color='blue'>" . number_format($rui_kousuu[$i]) . "</font></td>");
                ?>
            </tr>
        </table>
    </center>
</body>
</html>
 