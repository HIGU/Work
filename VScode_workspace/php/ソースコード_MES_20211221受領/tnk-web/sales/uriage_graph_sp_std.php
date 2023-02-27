<?php
//////////////////////////////////////////////////////////////////////////////
// ���ץ�ɸ���ʡ��������� ��� ��ӥ����                                   //
// Copyright (C) 2001-2005 2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp //
// Changed history                                                          //
// 2001/11/01 Created   uriage_graph_sp_std.php                             //
// 2002/05/01 ����ղ���ǯ��ɽ�����ƥ�뤫��ץ������å����ѹ�      //
// 2002/08/08 ���å������������ؤ����������ܥ�����ɲ�                  //
// 2003/02/13 ����դ����ܸ��б��� jpGraph.php 1.9.1 �ޥ���Х����б���     //
//              ��¤ ���ܸ��� ������ɲ� ����¾�����                     //
// 2003/05/01 jpGraph 1.12.1 UP �ˤ����Ĵ�� legend->Pos mark->SetWidth     //
//            ˺��Ƥ����Τ��� Graph(780��840  SetMargin(40,120��140 ��   //
// 2003/09/05 ����եե�����ι������Υ����å����ɲ� ��®����ޤä�����     //
//            error_reporting = E_ALL �б��Τ��� �����ѿ��ν�����ɲ�       //
// 2003/10/31 ���ץ����������ʬ�������� SQLʸ��(assembly_schedule)���ѹ� //
//            ����ɽ�Υե����ޥåȤ򲣿��Ӥ���Ŀ��Ӥ��ѹ��ʥǥ�����ޤ��  //
// 2003/12/12 define���줿����ǥǥ��쥯�ȥ�ȥ�˥塼����Ѥ��ƴ�������    //
//            ob_start('ob_gzhandler') ���ɲ�                               //
// 2004/06/07 ����ʬ��ɸ���ʤλ�����ˡ�ѹ� (�������Ρݴ�������)�ᴰ��ɸ��   //
//            ɽ�������ѹ� ɸ�ࡦ��������(ɸ��+����)�����ʡ���פ�ɽ������//
// 2004/11/05 Startǯ����ѹ������褦��$str_ym�˽��� �����200104->200304 //
// 2005/02/14 MenuHeader class ����Ѥ��ƶ��̥�˥塼���ڤ�ǧ���������ѹ�   //
// 2007/10/01 �ڡ���������ɲ�(��ǯ��Υ����ɽ��)  E_ALL | E_STRICT��      //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
// ini_set('zend.ze1_compatibility_mode', '1');    // zend 1.X ����ѥ� php4�θߴ��⡼��
// ini_set('implicit_flush', 'off');           // echo print �� flush �����ʤ�(�٤��ʤ뤿��) CLI��
// ini_set('max_execution_time', 1200);        // ����¹Ի���=20ʬ WEB CGI��
ob_start('ob_gzhandler');                   // ���ϥХåե���gzip����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��

require_once ('../function.php');           // ������define.php pgsql.php �� require()���Ƥ��롣
require_once ('../tnk_func.php');
require_once ('../MenuHeader.php');         // TNK ������ menu class
require_once ('../../jpgraph.php'); 
require_once ('../../jpgraph_line.php'); 
access_log();                               // Script Name �ϼ�ư����

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader(0);                  // ǧ�ڥ����å�0=���̰ʾ� �����=TOP_MENU �����ȥ�̤����

////////////// ����������
$menu->set_site( 1,  9);                    // site_index=1(����˥塼) site_id=9(���������ɸ�॰���)
////////////// �꥿���󥢥ɥ쥹����
// $menu->set_RetUrl(SALES_MENU);              // �̾�ϻ��ꤹ��ɬ�פϤʤ�
//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title('���ץ� �����ʡ�ɸ���� ����� �����');

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
//////////// ����դ������ϰϤ򥭥�ץ�������Ͽ
$menu->set_caption("{$str_ym}����{$end_ym}�ޤǤΥ���դ�ɽ��");

//////////// ����եե������¸�ߥ����å�
$graph_name = 'graph/uriage_graph_sp_std.png';
if (file_exists($graph_name)) {
    //////////// ���ߤ�ǯ�����ȥ���եե�����ι������ǡ��������
    $current_date = date('Ymd');
    $file_date    = date('Ymd', filemtime($graph_name) );
    //////////// ����եե�����ι����������å�
    if ($current_date == $file_date) {
        $create_flg = false;            // ����պ�������
    } else {
        $create_flg = true;             // ����պ���
    }
} else {
    $create_flg = true;                 // ����պ���
}
$create_flg = true;     // �Ʒ�ζ��ɽ���Τ������١��������롣��Ƿ���Υǡ�����ơ��֥����¸�����߷פ��Ѥ���
/////////// ��������ǯ��ν����
// $str_ym = 200304;

///////////////////// ����ɸ�� �����
                   // ����������γƷ�ζ�ۤϥ���ե�����򻲾Ȥ���
$query_wrk = "SELECT ǯ��,c����,cɸ��,c����,���ץ� FROM wrk_uriage WHERE ǯ��>={$str_ym} AND ǯ��<={$end_ym} ORDER BY ǯ�� ASC";
$res_wrk = array();
if ($rows_wrk = getResult($query_wrk,$res_wrk)) {
    $sp_kin  = array();
    $std_kin = array();
    $sei_kin = array();     // ����(����)���� 2004/06/07 add
    $f_sp_kin  = array();
    $f_std_kin = array();
    $f_sei_kin = array();   // ����(����)���� 2004/06/07 add
    $f_par_kin = array();   // ���ץ����� 2004/06/07 add
    $f_all_kin = array();   // ���ץ����� 2004/06/07 add
    $datax   = array();
    for($cnt=0;$cnt<$rows_wrk;$cnt++){      // cnt �������ѤΥ����󥿡����Ǥ�Ȥ�
        if(substr(date_offset(1),0,6)==$res_wrk[$cnt][0])   // ���˥���ե����빹�������к�
            break;
        $datax[$cnt]   = $res_wrk[$cnt][0];
        $sp_kin[$cnt]  = $res_wrk[$cnt][1];
        $std_kin[$cnt] = $res_wrk[$cnt][2];
    }
    for($i=0;$i<$cnt;$i++){
        $f_sp_kin[$i]  = Uround($sp_kin[$i] / 1000,0);      // ñ�̤���ߤˤ���ɽ��
        $f_std_kin[$i] = Uround($std_kin[$i] / 1000,0);
        ///// ɽ�����Υե����ޥåȤ����� 2004/06/07 �ɲ�ʬ
        $f_sei_kin[$i] = Uround($res_wrk[$i][3] / 1000,0);      // ����(����)���� 2004/06/07 add
        $f_all_kin[$i] = Uround($res_wrk[$i][4] / 1000,0);      // ��������� 2004/06/07 add
        $f_par_kin[$i] = Uround(($res_wrk[$i][4]-$res_wrk[$i][3]) / 1000,0);// ���ʡ����� 2004/06/07 add
        ///// ������ѥǡ�������
        $sp_kin[$i]  = Uround($sp_kin[$i] / 1000000,1);     // ñ�̤�ɴ���ߤˤ���
        $std_kin[$i] = Uround($std_kin[$i] / 1000000,1);
    }
}

if ($pageNo == 1) {
    $temp_date = date_offset(1);
    $temp_date = substr($temp_date, 0, 6);
    $s_date = $temp_date . '01';
    $e_date = $temp_date . '31';
    
    // ������ ����ʬ�׻�
    $query = "SELECT sum(Uround(����*ñ��,0)) as ��׶�� FROM hiuuri left outer join assembly_schedule on �ײ��ֹ�=plan_no WHERE �׾���>=$s_date AND �׾���<=$e_date AND ������='C' AND note15 like 'SC%'";
    // $query = "SELECT �׾���,����*ñ�� as ��� FROM hiuuri h, mipmst m WHERE h.assyno=m.seihin AND h.�׾���>=$s_date AND h.�׾���<=$e_date AND h.������='C' AND m.kubun='3' ORDER BY h.�׾��� asc";
    if (getUniResult($query,$res_toku) > 0) {
        $datax[$cnt] = substr($s_date, 0, 6);  // X���ι��ܤ�����
        $sp_kin[$cnt] = $res_toku;             // ����ι�׶�ۤ򻻽�
        $f_sp_kin[$cnt] = Uround($sp_kin[$cnt] / 1000,0);    // ñ�̤������ߤˤ���
        $sp_kin[$cnt] = Uround($sp_kin[$cnt] / 1000000,1);   // ñ�̤�ɴ���ߤˤ���
    }
        
    // ɸ���� ����ʬ�׻�
    $query = "SELECT sum(Uround(����*ñ��,0)) as ��׶�� FROM hiuuri WHERE �׾���>=$s_date AND �׾���<=$e_date AND ������='C' AND datatype='1'";
    // $query = "SELECT sum(Uround(����*ñ��,0)) as ��׶�� FROM hiuuri left outer join assembly_schedule on �ײ��ֹ�=plan_no WHERE �׾���>=$s_date AND �׾���<=$e_date AND ������='C' AND sei_kubun='1'";
    // $query = "SELECT �׾���,����*ñ�� as ��� FROM hiuuri h, mipmst m WHERE h.assyno=m.seihin AND h.�׾���>=$s_date AND h.�׾���<=$e_date AND h.������='C' AND m.kubun='1' ORDER BY h.�׾��� asc";
    if (getUniResult($query,$res_sei) > 0) {
        $datax[$cnt] = substr($s_date, 0, 6);       // X���ι��ܤ�����
        $std_kin[$cnt] = ($res_sei - $res_toku);    // ����ι�׶�ۤ򻻽� (�������� �� ��������)
        $f_std_kin[$cnt] = Uround($std_kin[$cnt] / 1000,0);  // ñ�̤������ߤˤ���
        $std_kin[$cnt] = Uround($std_kin[$cnt] / 1000000,1); // ñ�̤�ɴ���ߤˤ���
    }
    
    // ���ʡ����ǵڤ� ����(����)���Τ�����ʬ�׻�
    $f_sei_kin[$cnt] = Uround($res_sei / 1000, 0);
    
    // ��������� ����ʬ�׻�
    $query = "SELECT sum(Uround(����*ñ��,0)) as ��׶�� FROM hiuuri WHERE �׾���>=$s_date AND �׾���<=$e_date AND ������='C'";
    if (getUniResult($query,$res_all) > 0) {
        $f_par_kin[$cnt] = Uround(($res_all - $res_sei) / 1000, 0);    // ��������ʡ����Ǥζ�ۤ򻻽� (���ץ����� �� ��������)
        $f_all_kin[$cnt] = Uround($res_all / 1000, 0);  // ���ץ����Τζ�ۥ��å� ñ�̤������ߤˤ���
    }
} else {
    $cnt--;
}


// A nice graph with anti-aliasing 
$graph = new Graph(840, 380, 'auto');       // ����դ��礭�� X/Y
$graph->img->SetMargin(40,140,30,60);       // ����հ��֤Υޡ����� �����岼
$graph->SetScale("textlin"); 
$graph->SetShadow(); 
$graph->title->SetFont(FF_GOTHIC,FS_NORMAL,14); // FF_GOTHIC 14 �ʾ� FF_MINCHO �� 17 �ʾ����ꤹ��
$graph->title->Set(mb_convert_encoding("���ץ� �����ʡ�ɸ���� �����   ñ�̡�ɴ����","UTF-8")); 
$graph->legend->Pos(0.015,0.5,"right","center"); // ����ΰ��ֻ��� X Y(0.5�Ͻİ������)
$graph->legend->SetFont(FF_GOTHIC,FS_NORMAL,14); // FF_GOTHIC �� 14 �ʾ� FF_MINCHO �� 17 �ʾ�
//$graph->title->Set("Line plot with null values"); 
$graph->yscale->SetGrace(10);               // Set 10% grace. ;͵��������
$graph->yaxis->SetColor("black");
$graph->yaxis->SetWeight(2);

// Use built in font 
// $graph->title->SetFont(FF_FONT1,FS_BOLD); 

// Slightly adjust the legend from it's default position in the 
// top right corner. 
$graph->legend->Pos(0.03,0.5,"right","center");  // ����ΰ��ֻ���

// Setup X-scale 
$graph->xaxis->SetTickLabels($datax); 
$graph->xaxis->SetFont(FF_GOTHIC, FS_NORMAL, 11);   // �ե���Ȥϥܡ���ɤ����Ǥ��롣
$graph->xaxis->SetLabelAngle(45); 

// Create the first line 
$p1 = new LinePlot($std_kin); 
// $p1->mark->SetType(MARK_FILLEDCIRCLE);   // �ץ�åȥޡ����η�
$p1->mark->SetType(MARK_DIAMOND);   // �ץ�åȥޡ����η�
$p1->mark->SetFillColor("red"); 
$p1->mark->SetWidth(4); 
$p1->SetColor("red"); 
$p1->SetCenter(); 
$p1->SetLegend(mb_convert_encoding("ɸ����","UTF-8"));    // �����̾������
// $p1->SetLegend("Custom Order"); 
$graph->Add($p1); 

// ... and the second 
//  $graph->SetY2Scale("lin");      // Y2����������ɲ�
//  $graph->y2axis->SetColor("red");    // Y2��������ο�
//  $graph->y2axis->SetWeight(2);   // Y2�������������(���ɥå�)
//  $graph->y2scale->SetGrace(10);  // Set 10% grace. ;͵��������
$p2 = new LinePlot($sp_kin);   // ����ܤΥ饤��ץ�åȥ��饹�����
// $p2->mark->SetType(MARK_STAR);      // �ץ�åȥޡ����η�
// $p2->mark->SetType(MARK_DIAMOND);   // �ץ�åȥޡ����η�
$p2->mark->SetType(MARK_FILLEDCIRCLE);    // �ץ�åȥޡ����η�
$p2->mark->SetFillColor("blue");     // �ץ�åȥޡ����ο�
$p2->mark->SetWidth(2);         // �ץ�åȥޡ������礭��
$p2->SetColor("blue");           // �ץ�å����ο�
$p2->SetCenter();           // �ץ�åȤ������
$p2->SetWeight(1);          // �ץ�å���������(���ɥå�)
$p2->SetLegend(mb_convert_encoding("������","UTF-8"));    // �����̾������
// $p2->SetLegend("Standard");  // ����Ϻ��
$graph->Add($p2);       // ���̤Υ���դ���Y2����������ѹ��Τ��ᥳ����
//  $graph->AddY2($p2);             // Y2���������ѤΥץ�åȣ����ɲ�

// Output line 
$graph->Stroke($graph_name); 
// echo $graph->GetHTMLImageMap("myimagemap"); 
// echo "<img src=\"".GenImgName()."\" ISMAP USEMAP=\"#myimagemap\" border=0>"; 


for($i=0;$i<=$cnt;$i++){
    $f_std_kin[$i] = number_format($f_std_kin[$i]);     // ���头�ȤΥ���ޤ��ղ�
    $f_sp_kin[$i]  = number_format($f_sp_kin[$i]);      // ���头�ȤΥ���ޤ��ղ�
    $f_sei_kin[$i] = number_format($f_sei_kin[$i]);     // ���头�ȤΥ���ޤ��ղ� 2004/06/07 add
    $f_par_kin[$i] = number_format($f_par_kin[$i]);     // ���头�ȤΥ���ޤ��ղ� 2004/06/07 add
    $f_all_kin[$i] = number_format($f_all_kin[$i]);     // ���头�ȤΥ���ޤ��ղ� 2004/06/07 add
}

///////////// HTML Header ����Ϥ��ƥ֥饦�����Υ���å��������
$menu->out_html_header();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<title><?php echo $menu->out_title() ?></title>
<?php echo $menu->out_site_java() ?>
<?php echo $menu->out_css() ?>

<style type='text/css'>
<!--
.pt10b {
    font-size:      0.80em;
    font-weight:    bold;
}
.pt12b {
    font-size:      12pt;
    font-weight:    bold;
}
-->
</style>
<script language='JavaScript'>
<!--
/* ������ϥե�����Υ�����Ȥ˥ե������������� */
function set_focus() {
    // document.body.focus();   // F2/F12������ͭ���������б�
    document.mhForm.backwardStack.focus();  // �嵭��IE�ΤߤΤ���NN�б�
}
// -->
</script>
</head>
<body onLoad='set_focus()'>
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
<table align='center' with='100%' border='0'>
    <tr>
        <td align='center'>
            <img src='<?php echo $graph_name . "?" . uniqid(rand(),1) ?>' alt='���ץ�����ɸ�� ����� �����' border='0'>
        </td>
    </tr>
</table>

<table align='center' bgcolor='#d6d3ce'  border='1' cellspacing='0' cellpadding='2'>
    <caption style='text-align:right;'><font size='2'>ñ�̡����</font></caption>
    <tr><td> <!-- ���ߡ�(�ǥ�������) -->
  <table bgcolor='#d6d3ce' cellspacing='0' cellpadding='3' border='1' bordercolordark='white' bordercolorlight='#bdaa90'>
    <th nowrap>ǯ��</th> <th nowrap>ɸ����(A)</th> <th nowrap>������(B)</th><th nowrap>��������(A+B)</th><th nowrap>���ʡ�����(D)</th><th nowrap>�ù��(A+B+D)</th>
    <?php
    for ($i=$cnt; $i>=0; $i--) {
        echo "<tr>\n";
        echo "    <td nowrap width='100' align='center'>" . substr($datax[$i],0,4) . "/" . substr($datax[$i],4,2) . "</td>\n";
        echo "    <td nowrap width='100' align='right' style='color:red;'>{$f_std_kin[$i]}</td>\n";
        echo "    <td nowrap width='100' align='right' style='color:blue;'>{$f_sp_kin[$i]}</td>\n";
        echo "    <td nowrap width='100' align='right'>{$f_sei_kin[$i]}</td>\n";
        echo "    <td nowrap width='100' align='right'>{$f_par_kin[$i]}</td>\n";
        echo "    <td nowrap align='right'>{$f_all_kin[$i]}</td>\n";
        echo "</tr>\n";
    }
    ?>
  </table>
    </td></tr>
</table> <!-- ���ߡ�End -->


    </center>
</body>
<?php echo $menu->out_alert_java()?>
</html>
<?php
ob_end_flush();                 // ���ϥХåե���gzip���� END
?>
