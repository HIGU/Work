<?php
//////////////////////////////////////////////////////////////////////////////
// ��� ��� ����դ����Ρ����ץ顦��˥� (�ޤ��������)                    //
// Copyright (C) 2001-2010 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2001/10/01 Created  uriage_graph_all_tuki.php                            //
// 2002/08/08 ���å������������ؤ����������ܥ�����ɲ�                  //
// 2002/12/20 ����դ����ܸ��б����ѹ�(�޷�����)                            //
// 2002/12/20 ����դ����ܸ��б��� jpGraph.php 1.9.1 �ޥ���Х����б��ز�¤ //
//              $graph->title->SetFont(FF_GOTHIC,FS_NORMAL,14);             //
//               FF_GOTHIC 14 �ʾ� FF_MINCHO �� 17 �ʾ����ꤹ��           //
//            title �� �ϣˤ��� ����� ���ޤ������ʤ� jpgraph_bar.php ��OK  //
//            $graph->title->SetFont(FF_GOTHIC); // FF_GOTHIC �Τ�          //
//                     ��                                                   //
//            $graph->title->SetFont(FF_GOTHIC,FS_NORMAL,14);               //
//            FF_GOTHIC 14 �ʾ� FF_MINCHO �� 17 �ʾ����ꤹ��              //
//            Legend �� ���ޤ����ä������ѹ�                                //
//            $graph->legend->Pos(0.02,0.5,"right","center");               //
//            ����ΰ��ֻ���(�����ޡ�����,�岼�ޡ�����,"right","center")    //
//            $graph->legend->SetFont(FF_GOTHIC,FS_NORMAL,14);              //
//            FF_GOTHIC �� 14 �ʾ� FF_MINCHO �� 17 �ʾ�                     //
// 2003/05/01 jpGraph 1.12.1 UP �ˤ����Ĵ�� SetMargin() legend->Pos()      //
//            mark->SetWidth()                                              //
// 2003/09/05 ����եե�����ι������Υ����å����ɲ� �����ι�®����ޤä��� //
//            error_reporting = E_ALL �б��Τ��� �����ѿ��ν�����ɲá�     //
// 2003/12/10 �����ȥ�η����դ��ץ���դ��ѹ�                        //
// 2003/12/12 define���줿����ǥǥ��쥯�ȥ�ȥ�˥塼����Ѥ��ƴ�������    //
//            ob_start('ob_gzhandler') ���ɲ�                               //
// 2004/04/27 3�Ĥ����ܥ������1�Ĥ� SALES_MENU ���ѹ�ϳ�줬���ä��Τ��ɲ�//
// 2005/02/14 MenuHeader class ����Ѥ��ƶ��̥�˥塼���ڤ�ǧ���������ѹ�   //
// 2006/09/01 ����ǯ��� $str_ym ������                                     //
// 2007/10/02 �ڡ���������ɲ�(��ǯ��Υ����ɽ��)  E_ALL | E_STRICT��      //
// 2009/04/14 �����Ѥ�ä���(4��)�˥ǡ�����̵�����顼���ФƤ��ޤ���         //
//            4��˾Ȳ񤹤������ʬ��ɽ������褦���ѹ�                ��ë //
// 2010/05/06 �ǡ������ʤ����Υ��顼�б�������ǥǡ���������ޤ줿�Τ�      //
//            ̤��ǧ��                                                 ��ë //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
// ini_set('memory_limit', '64M');
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
$menu->set_site( 1,  4);                    // site_index=1(����˥塼) site_id=4(��ץ����)
////////////// �꥿���󥢥ɥ쥹����
// $menu->set_RetUrl(SALES_MENU);              // �̾�ϻ��ꤹ��ɬ�פϤʤ�
//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title('����ץ����');
//////////// �ƽ����action̾�ȥ��ɥ쥹����
// $menu->set_action('���ץ����',   SALES . 'uriage_graph_all_niti.php');

///// �ڡ����ꥯ�����Ȥν���
if (isset($_REQUEST['pageNo'])) {
    $pageNo = $_REQUEST['pageNo'];
} else {
    $pageNo = 1;
}
if ($pageNo < 1) $pageNo = 1;
///// ��������ǯ��λ���
if (date('m') < 4) {
    $str_ym = date('Y') - $pageNo . '04';
} else {
    $str_ym = date('Y') - $pageNo + 1 . '04';
}
if (date('m') == 4) {
    $str_ym = date('Y') - $pageNo . '04';
} else {
    $str_ym = date('Y') - $pageNo + 1 . '04';
}
///// �����ǯ��λ���
$end_ym = substr($str_ym, 0, 4) + 1 . '03';
if ($end_ym > date('Ym')) {
    $end_ym = date('Ym');
}
///// ��ߥå�������
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
///// ��������ǯ��λ���
// $str_ym = '200704';
//////////// ����եե������¸�ߥ����å�
$graph_name1 = "graph/uriage_graph_all_tuki.png";       // ���Τη��� ���������
$graph_name2 = "graph/uriage_graph_all_tuki_c.png";     // ���ץ�η��� ���������
$graph_name3 = "graph/uriage_graph_all_tuki_l.png";     // ��˥��η��� ���������
if (file_exists($graph_name1)) {
    //////////// ���ߤ�ǯ�����ȥ���եե�����ι������ǡ��������
    $current_date = date("Ymd");
    $file_date    = date("Ymd", filemtime($graph_name1) );
    //////////// ����եե�����ι����������å�
    if ($current_date == $file_date) {
        $create_flg1 = false;           // ����պ�������
    } else {
        $create_flg1 = true;            // ����պ���
    }
} else {
    $create_flg1 = true;                // ����պ���
}
if (file_exists($graph_name2)) {
    //////////// ���ߤ�ǯ�����ȥ���եե�����ι������ǡ��������
    $current_date = date("Ymd");
    $file_date    = date("Ymd", filemtime($graph_name2) );
    //////////// ����եե�����ι����������å�
    if ($current_date == $file_date) {
        $create_flg2 = false;           // ����պ�������
    } else {
        $create_flg2 = true;            // ����պ���
    }
} else {
    $create_flg2 = true;                // ����պ���
}
if (file_exists($graph_name3)) {
    //////////// ���ߤ�ǯ�����ȥ���եե�����ι������ǡ��������
    $current_date = date("Ymd");
    $file_date    = date("Ymd", filemtime($graph_name3) );
    //////////// ����եե�����ι����������å�
    if ($current_date == $file_date) {
        $create_flg3 = false;           // ����պ�������
    } else {
        $create_flg3 = true;            // ����պ���
    }
} else {
    $create_flg3 = true;                // ����պ���
}
///// �ƥ�����
$create_flg1 = true;
$create_flg2 = true;
$create_flg3 = true;


if ($create_flg1) {
    $query_wrk = "SELECT ǯ��,���� FROM wrk_uriage WHERE ǯ��>={$str_ym} AND ǯ��<={$end_ym} ORDER BY ǯ�� ASC";
    $query = "SELECT sum(Uround(����*ñ��, 0)) AS ��� FROM hiuuri WHERE �׾���>=%s AND �׾���<=%s";
    graphDataCreate($query_wrk, $query, '����', $graph_name1);
}
if ($create_flg2) {
    $query_wrk = "SELECT ǯ��,���ץ� FROM wrk_uriage WHERE ǯ��>={$str_ym} AND ǯ��<={$end_ym} ORDER BY ǯ�� ASC";
    $query = "SELECT sum(Uround(����*ñ��, 0)) AS ��� FROM hiuuri WHERE �׾���>=%s AND �׾���<=%s AND ������='C'";
    graphDataCreate($query_wrk, $query, '���ץ�', $graph_name2);
}
if ($create_flg3) {
    $query_wrk = "SELECT ǯ��,��˥� FROM wrk_uriage WHERE ǯ��>={$str_ym} AND ǯ��<={$end_ym} ORDER BY ǯ�� ASC";
    $query = "SELECT sum(Uround(����*ñ��, 0)) AS ��� FROM hiuuri WHERE �׾���>=%s AND �׾���<=%s AND ������='L'";
    graphDataCreate($query_wrk, $query, '��˥�', $graph_name3);
}

function graphDataCreate($query_wrk, $query, $title, $graph_name)
{
    global $pageNo;
    ////////////////////////// ���� ����
                            // ����������γƷ�ζ�ۤϥ���ե�����򻲾Ȥ���
    $cnt = 0;
    $res_wrk = array();
    if ($rows_wrk = getResult($query_wrk, $res_wrk)) {
        $tuki_kin = array();
        $datax    = array();
        for ($cnt=0; $cnt<$rows_wrk; $cnt++) {      // cnt �������ѤΥ����󥿡����Ǥ�Ȥ�
            if (substr(date_offset(1), 0, 6) == $res_wrk[$cnt][0]) {  // ���˥���ե����빹�������к�
                break;
            }
            $datax[$cnt]    = $res_wrk[$cnt][0];
            $tuki_kin[$cnt] = $res_wrk[$cnt][1];
        }
    }
    if ($pageNo == 1) {     // �ڡ����ֹ椬���λ���������Υǡ������ɹ���
        $temp_date = date_offset(1);
        $temp_date = substr($temp_date, 0, 6);
        $s_date = $temp_date . '01';
        $e_date = $temp_date . '31';
        $query = sprintf($query, $s_date, $e_date);
        getUniResult($query, $tuki_kin[$cnt]);
        $datax[$cnt] = $temp_date;              // X���ι��ܤ�����
        $cnt++;
    }
    $rui_kin = array();
    for ($i=0; $i<$cnt; $i++) {
        if ($i == 0) {
            $rui_kin[$i] = $tuki_kin[$i];
        } else {
            $rui_kin[$i] = $tuki_kin[$i] + $rui_kin[$i-1];
        }
    }
    for ($i=0; $i<$cnt; $i++) {
        $tuki_kin[$i] = Uround($tuki_kin[$i] / 1000000,1);   // ñ�̤�ɴ���ߤˤ���
        $rui_kin[$i]  = Uround($rui_kin[$i]  / 1000000,1);
    }
    ///// ����պ���
    graphCreate($datax, $rui_kin, $tuki_kin, $title, $graph_name);
    return;
}


function userFormat($aLabel)
{
    return number_format($aLabel, 1);
}

function graphCreate($datax, $rui_kin, $tuki_kin, $title, $graph_name)
{
    // Some data 
    //$datax = array("2001-04","2001-05","2001-06","2001-07","2001-08","2001-09"); 
    //$datay  = array(5,9,15,21,25,32); 
    //$data2y = array(5,4, 6,6,4,7); 
    
    // A nice graph with anti-aliasing 
    $graph = new Graph(740, 350, 'auto');       // ����դ��礭�� X/Y
    $graph->img->SetMargin(40, 130, 30, 60);    // ����հ��֤Υޡ����� �����岼
    $graph->SetScale('textlin'); 
    $graph->SetShadow(); 
    // Slightly adjust the legend from it's default position in the 
    // top right corner. 
    $graph->legend->Pos(0.015, 0.5, 'right', 'center'); // ����ΰ��ֻ���(�����ޡ�����,�岼�ޡ�����,"right","center")
    $graph->legend->SetFont(FF_GOTHIC, FS_NORMAL, 14);  // FF_GOTHIC �� 14 �ʾ� FF_MINCHO �� 17 �ʾ�
    
    // $graph->title->Set("Line plot with null values"); 
    $graph->yscale->SetGrace(10);     // Set 10% grace. ;͵��������
    $graph->yaxis->SetColor('blue');
    $graph->yaxis->SetWeight(2);
    
    // Use built in font 
    $graph->title->SetFont(FF_GOTHIC, FS_NORMAL, 14);   // FF_GOTHIC 14 �ʾ� FF_MINCHO �� 17 �ʾ����ꤹ��
    $graph->title->Set(mb_convert_encoding("������ ��� {$title} �����   ñ�̡�ɴ����", 'UTF-8')); 
    // $graph->title->SetFont(FF_FONT1,FS_BOLD); 
    
    // Setup X-scale
    $graph->xaxis->SetTickLabels($datax);
    $graph->xaxis->SetFont(FF_GOTHIC, FS_NORMAL, 11);   // �ե���Ȥϥܡ���ɤ����Ǥ��롣
    $graph->xaxis->SetLabelAngle(45);
    
    // Create the first line
    $p1 = new LinePlot($rui_kin);
    $p1->mark->SetType(MARK_FILLEDCIRCLE);
    $p1->mark->SetFillColor('blue');
    $p1->mark->SetWidth(2);
    $p1->SetColor('blue');
    $p1->SetCenter(); 
    $p1->SetLegend(mb_convert_encoding('�߷�', 'UTF-8'));
    // $p1->value->SetFormat('%01.1f'); // ��������̵������0����������������
    $p1->value->SetFormatCallback('userFormat');    // �嵭�Ǥϣ���Υ���ޤ��б��Ǥ��ʤ�����
    $p1->value->SetFont(FF_GOTHIC, FS_NORMAL, 11);
    $p1->value->Show();
    $graph->Add($p1); 
    
    // ... and the second 
    $graph->SetY2Scale('lin');      // Y2����������ɲ�
    $graph->y2axis->SetColor('red');// Y2��������ο�
    $graph->y2axis->SetWeight(2);   // Y2�������������(���ɥå�)
    $graph->y2scale->SetGrace(10);  // Set 10% grace. ;͵��������
    $p2 = new LinePlot($tuki_kin);  // ����ܤΥ饤��ץ�åȥ��饹�����
    $p2->mark->SetType(MARK_STAR);  // �ץ�åȥޡ����η�
    $p2->mark->SetFillColor("red"); // �ץ�åȥޡ����ο�
    $p2->mark->SetWidth(4);         // �ץ�åȥޡ������礭��
    $p2->SetColor('red');           // �ץ�å����ο�
    $p2->SetCenter();               // �ץ�åȤ������
    $p2->SetWeight(1);              // �ץ�å���������(���ɥå�)
    $p2->SetLegend(mb_convert_encoding('���', 'UTF-8')); 
    // $p2->value->SetFormat('%01.1f'); // ��������̵������0����������������
    $p2->value->SetFormatCallback('userFormat');    // �嵭�Ǥϣ���Υ���ޤ��б��Ǥ��ʤ�����
    $p2->value->SetFont(FF_GOTHIC, FS_NORMAL, 11);
    $p2->value->SetColor('red');                    // �ͤΤο�
    $p2->value->Show();
    //  $graph->Add($p2);           // ���̤Υ���դ���Y2����������ѹ��Τ��ᥳ����
    $graph->AddY2($p2);             // Y2���������ѤΥץ�åȣ����ɲ�
    
    // Output line 
    $graph->Stroke($graph_name); 
    // echo $graph->GetHTMLImageMap("myimagemap"); 
    // echo "<img src=\"".GenImgName()."\" ISMAP USEMAP=\"#myimagemap\" border=0>"; 
    return;
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
    font-size:      1.00em;
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
<table width='100%' border='0'>
    <tr>
        <td align='center'>
           <img src='<?php echo  $graph_name1 . "?" . uniqid(rand(),1) ?>' alt='������ � ���� �����' border='0'>
        </td>
    </tr>
</table>


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
                    <!--
                    <table align='center' width='60' bgcolor='blue' border='3' cellspacing='0' cellpadding='0'>
                        <form method='post' action='<?php echo $menu->out_RetUrl() ?>'>
                            <td align='center'><input class='pt12b' type='submit' name='return' value='���'></td>
                        </form>
                    </table>
                    -->
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
<table width='100%' border='0'>
    <tr>
        <td align='center'>
            <img src='<?php echo $graph_name2 . "?" . uniqid(rand(),1) ?>' alt='������ � ���ץ� �����' border='0'>
        </td>
    </tr>
</table>


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
                    <!--
                    <table align='center' width='60' bgcolor='blue' border='3' cellspacing='0' cellpadding='0'>
                        <form method='post' action='<?php echo $menu->out_RetUrl() ?>'>
                            <td align='center'><input class='pt12b' type='submit' name='return' value='���'></td>
                        </form>
                    </table>
                    -->
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
<table width='100%' border='0'>
    <tr>
        <td align='center'>
            <img src='<?php echo $graph_name3 . "?" . uniqid(rand(),1) ?>' alt='������ � ��˥� �����' border='0'>
        </td>
    </tr>
</table>


    </center>
</body>
<?php echo $menu->out_alert_java()?>
</html>
<?php
ob_end_flush();                 // ���ϥХåե���gzip���� END
?>
