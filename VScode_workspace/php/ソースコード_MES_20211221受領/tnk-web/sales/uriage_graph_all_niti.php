<?php
//////////////////////////////////////////////////////////////////////////////
// ��� ���� ����� (���Ρ����ץ顦��˥�)                                  //
// Copyright (C) 2001-2005 2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp //
// Changed history                                                          //
// 2001/11/01 Created   uriage_graph_all_niti.php                           //
// 2002/04/08 ǯ�������б�                                                //
// 2002/08/08 ���ܥ����table��with=100% �Υ��ڥ�ߥ�����               //
// 2002/08/08 ���å������������ؤ�                                        //
// 2002/09/21 ����դ��˺ǿ���ɽ�������뤿���uniqid(rand(),1)            //
// 2002/10/05 processing_msg.php ���ɲ�(�׻���)                             //
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
//            ����ΰ��ֻ���(�����ޡ�����,�岼�ޡ�����)                     //
//            $graph->legend->SetFont(FF_GOTHIC,FS_NORMAL,14);              //
//            FF_GOTHIC �� 14 �ʾ� FF_MINCHO �� 17 �ʾ�                     //
// 2003/05/01 jpGraph 1.12.1 UP �ˤ����Ĵ�� SetMargin() legend->Pos()      //
//            mark->SetWidth()                                              //
// 2003/09/05 ����եե�����ι������Υ����å����ɲ� ��®����ޤ��Ȥ����� //
//            error_reporting = E_ALL �б��Τ��� �����ѿ��ν�����ɲá�     //
// 2003/12/10 ���㤬��ˤʤäƤ���Τ����פ����� �����ȥ�����������פ�   //
// 2003/12/12 define���줿����ǥǥ��쥯�ȥ�ȥ�˥塼����Ѥ��ƴ�������    //
//            ob_start('ob_gzhandler') ���ɲ�                               //
// 2005/02/14 MenuHeader class ����Ѥ��ƶ��̥�˥塼���ڤ�ǧ���������ѹ�   //
// 2007/10/03 �ڡ���������ɲ�(��������Υڡ���ɽ��)  E_ALL | E_STRICT��    //
//            ľ��ǯ�������ɲá����׵ڤ��߷פθ��̤˶��ɽ������ɽ���ɲ�  //
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
require_once ('../ControllerHTTP_Class.php');// TNK ������ MVC Controller Class
require_once ('../../jpgraph.php'); 
require_once ('../../jpgraph_line.php'); 
access_log();                               // Script Name �ϼ�ư����

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader(0);                  // ǧ�ڥ����å�0=���̰ʾ� �����=TOP_MENU �����ȥ�̤����

////////////// ����������
$menu->set_site( 1,  3);                    // site_index=1(����˥塼) site_id=3(���ץ����)
////////////// �꥿���󥢥ɥ쥹����
// $menu->set_RetUrl(SALES_MENU);              // �̾�ϻ��ꤹ��ɬ�פϤʤ�
//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title('������ץ����');
//////////// �ƽ����action̾�ȥ��ɥ쥹����
// $menu->set_action('���ץ����',   SALES . 'uriage_graph_all_niti.php');

//////////// ���å����Υ��󥹥��󥹤�����
$session = new Session();

//////////// �����Х��ѿ� ǯ�������Ƚ�����Υ�å���������
if ( isset($_REQUEST['yyyymm']) ) {
    $session->add_local('yyyymm', $_REQUEST['yyyymm']);
    if ( isset($_REQUEST['dailyValue']) ) $session->add_local('dailyValue', $_REQUEST['dailyValue']);
    if ( isset($_REQUEST['totalValue']) ) $session->add_local('totalValue', $_REQUEST['totalValue']);
    // $yyyymm = $_REQUEST['yyyymm'];
    // header("Location: http:" . WEB_HOST . "processing_msg.php?script=". SALES ."uriage_graph_all_niti.php");
    header('Location: ' . H_WEB_HOST . '/processing_msg.php?script=' . $menu->out_self());
    exit(); ////////// ���줬�ʤ��ȥ�����ץȤ�Ǹ�ޤǥ����å�����Τǻ��֤������롣
} elseif ($session->get_local('yyyymm') != '') {
    $yyyymm = $session->get_local('yyyymm');
} else {
    $yyyymm = date('Ym');
}
//////////// �ƽФ�����ǯ��ǡ����ᤷ
$menu->set_retGET('yyyymm', $yyyymm);

//////////// �֥饦�����Υ���å����к���
$uniq = $menu->set_useNotCache('dailyGraph');

//////////// ����դ���ɽ��ON/OFF���󥫡�����������
if ($session->get_local('dailyValue') == 'on') {
    $dailyValue = "<a href='{$menu->out_self()}?yyyymm={$yyyymm}&dailyValue=off&{$uniq}'>���׶����ɽ��</a>����\n";
} else {
    $dailyValue = "<a href='{$menu->out_self()}?yyyymm={$yyyymm}&dailyValue=on&{$uniq}'>���׶��ɽ��</a>����\n";
}
if ($session->get_local('totalValue') == 'on') {
    $totalValue = "<a href='{$menu->out_self()}?yyyymm={$yyyymm}&totalValue=off&{$uniq}'>�߷׶����ɽ��</a>����\n";
} else {
    $totalValue = "<a href='{$menu->out_self()}?yyyymm={$yyyymm}&totalValue=on&{$uniq}'>�߷׶��ɽ��</a>����\n";
}

//////////// ɽ������ǯ�������ե��������
$ym_form = ymFormCreate($menu, $yyyymm);

//////////// ɽ�������
$yyyy = substr($yyyymm, 0, 4);
$mm   = substr($yyyymm, 4, 2);
$menu->set_caption("���ץ����({$yyyy}ǯ{$mm}��)");

//////////// �������Υǡ�������
if ($mm == 1) {
    $next_yyyymm = $yyyy . '02';
    $yyyy--;
    $pre_yyyymm = $yyyy . '12';
} elseif ($mm == 12) {
    $pre_yyyymm = $yyyy . '11';
    $yyyy++;
    $next_yyyymm = $yyyy . '01';
} else {
    $pre_yyyymm = $yyyymm - 1;
    $next_yyyymm = $yyyymm + 1;
}
//////////// ��������ͭ����̵��������
if ($yyyymm >= date('Ym')) {
    $forward = ' disabled';
} else {
    $forward = '';
}
if ($pre_yyyymm < 200010) {
    $backward = ' disabled';
} else {
    $backward = '';
}

//////////// ����եե������¸�ߥ����å�
$graph_name1 = "graph/uriage_graph_all_niti.png";       // ���Τ�������� ���������
$graph_name2 = "graph/uriage_graph_all_niti_c.png";     // ���ץ��������� ���������
$graph_name3 = "graph/uriage_graph_all_niti_l.png";     // ��˥���������� ���������

/////////////// ������ϰϤ�ǯ��������
$s_date = $yyyymm . '01';
$e_date = $yyyymm . '31';

/////////////////// ���Τ����������
$query = "SELECT �׾���, sum(Uround(����*ñ��, 0)) AS ��� FROM hiuuri WHERE �׾���>={$s_date} AND �׾���<={$e_date} GROUP BY �׾��� ORDER BY �׾��� ASC";
graphDataCreate($query, '����', $graph_name1);

/////////////////// ���ץ�����������
$query = "SELECT �׾���, sum(Uround(����*ñ��, 0)) AS ��� FROM hiuuri WHERE �׾���>={$s_date} AND �׾���<={$e_date} AND ������='C' GROUP BY �׾��� ORDER BY �׾��� ASC";
graphDataCreate($query, '���ץ�', $graph_name2);

////////////////////// ��˥������������
$query = "SELECT �׾���, sum(Uround(����*ñ��, 0)) AS ��� FROM hiuuri WHERE �׾���>={$s_date} AND �׾���<={$e_date} AND ������='L' GROUP BY �׾��� ORDER BY �׾��� ASC";
graphDataCreate($query, '��˥�', $graph_name3);

function graphDataCreate($query, $title, $graph_name)
{
    global $yyyymm;
    $res = array();
    if ($rows = getResult($query, $res)) {
        $start_flg = 0;             //�������Ȼ��Υե饰
        $niti_kin = array();
        $datax    = array();
        for ($r=0; $r<$rows; $r++) {                // ������ι�׶�ۤ򻻽�
            $datax[$r] = $res[$r][0];
            $niti_kin[$r] = $res[$r][1];
        }
        $rui_kin = array();
        for ($i=0; $i<$rows; $i++) {
            if ($i == 0) {
                $rui_kin[$i] = $niti_kin[$i];
            } else {
                $rui_kin[$i] = $niti_kin[$i] + $rui_kin[$i-1];
            }
        }
        for ($i=0; $i<$rows; $i++) {
            $niti_kin[$i] = Uround($niti_kin[$i] / 1000000, 1);
            $rui_kin[$i]  = Uround($rui_kin[$i]  / 1000000, 1);
        }
    } else {
        $datax = array("$yyyymm");
        $niti_kin = array(0.0);
        $rui_kin  = array(0.0);
    }
    ///// ����պ���
    graphCreate($datax, $rui_kin, $niti_kin, $title, $graph_name);
    return;
}

function userFormat($aLabel)
{
    return number_format($aLabel, 1);
}

function graphCreate($datax, $rui_kin, $niti_kin, $title, $graph_name)
{
    global $yyyymm, $session;
    $ym_format = substr($yyyymm, 0, 4) . 'ǯ' . substr($yyyymm, 4, 2) . '��';
    // Some data 
    //$datax = array("2001-04","2001-05","2001-06","2001-07","2001-08","2001-09"); 
    //$datay  = array(5,9,15,21,25,32); 
    //$data2y = array(5,4, 6,6,4,7); 
    
    // A nice graph with anti-aliasing 
    $graph = new Graph(740, 350, 'auto');       // ����դ��礭�� X/Y
    $graph->img->SetMargin(40, 110, 30, 60);    // ����հ��֤Υޡ����� �����岼
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
    $graph->title->Set(mb_convert_encoding("{$ym_format} ������ ���� {$title} �����   ñ�̡�ɴ����", 'UTF-8')); 
    // $graph->title->SetFont(FF_FONT1,FS_BOLD); 
    
    // Setup X-scale
    $graph->xaxis->SetTickLabels($datax);
    $graph->xaxis->SetFont(FF_GOTHIC, FS_NORMAL, 11);   // �ե���Ȥϥܡ���ɤ����Ǥ��롣
    $graph->xaxis->SetLabelAngle(35);
    
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
    if ($session->get_local('totalValue') == 'on') {
        $p1->value->Show();
    }
    $graph->Add($p1); 
    
    // ... and the second 
    $graph->SetY2Scale('lin');      // Y2����������ɲ�
    $graph->y2axis->SetColor('red');// Y2��������ο�
    $graph->y2axis->SetWeight(2);   // Y2�������������(���ɥå�)
    $graph->y2scale->SetGrace(10);  // Set 10% grace. ;͵��������
    $p2 = new LinePlot($niti_kin);  // ����ܤΥ饤��ץ�åȥ��饹�����
    $p2->mark->SetType(MARK_STAR);  // �ץ�åȥޡ����η�
    $p2->mark->SetFillColor("red"); // �ץ�åȥޡ����ο�
    $p2->mark->SetWidth(4);         // �ץ�åȥޡ������礭��
    $p2->SetColor('red');           // �ץ�å����ο�
    $p2->SetCenter();               // �ץ�åȤ������
    $p2->SetWeight(1);              // �ץ�å���������(���ɥå�)
    $p2->SetLegend(mb_convert_encoding('����', 'UTF-8')); 
    // $p2->value->SetFormat('%01.1f'); // ��������̵������0����������������
    $p2->value->SetFormatCallback('userFormat');    // �嵭�Ǥϣ���Υ���ޤ��б��Ǥ��ʤ�����
    $p2->value->SetFont(FF_GOTHIC, FS_NORMAL, 11);
    $p2->value->SetColor('red');                    // �ͤΤο�
    if ($session->get_local('dailyValue') == 'on') {
        $p2->value->Show();
    }
    //  $graph->Add($p2);           // ���̤Υ���դ���Y2����������ѹ��Τ��ᥳ����
    $graph->AddY2($p2);             // Y2���������ѤΥץ�åȣ����ɲ�
    
    // Output line 
    $graph->Stroke($graph_name); 
    // echo $graph->GetHTMLImageMap("myimagemap"); 
    // echo "<img src=\"".GenImgName()."\" ISMAP USEMAP=\"#myimagemap\" border=0>"; 
    return;
}

//////////// ɽ������ǯ�������ե���������
function ymFormCreate($menu, $yyyymm)
{
    $ym_form = "
        <form name='ym_form' action='{$menu->out_self()}' method='get'>
        <select name='yyyymm' onChange='document.ym_form.submit()'>
    ";
    $current_ym = date('Ym');
    if ($yyyymm == $current_ym) {
        $ym_form .= "    <option value='{$current_ym}' selected>{$current_ym}</option>\n";
    } else {
        $ym_form .= "    <option value='{$current_ym}'>{$current_ym}</option>\n";
    }
                    // ����������γ� ǯ��ϥ���ե�����򻲾Ȥ���
    $query_wrk = "SELECT ǯ�� FROM wrk_uriage WHERE ǯ��>=200010 ORDER BY ǯ�� DESC";
    $res_wrk = array();
    if ( ($rows_wrk=getResult2($query_wrk, $res_wrk)) > 0 ) {
        for ($i=0; $i<$rows_wrk; $i++) {
            if ($yyyymm == $res_wrk[$i][0]) {
                $ym_form .= "    <option value='{$res_wrk[$i][0]}' selected>{$res_wrk[$i][0]}</option>\n";
            } else {
                $ym_form .= "    <option value='{$res_wrk[$i][0]}'>{$res_wrk[$i][0]}</option>\n";
            }
        }
    }
    $ym_form .= "    </select>\n";
    $ym_form .= "    </form>\n";
    return $ym_form;
}


///////////// HTML Header ����Ϥ��ƥ֥饦�����Υ���å��������
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
select {
    background-color:   teal;
    color:              white;
    font-size:          1.00em;
    font-weight:        bold;
}
-->
</style>
<script language='JavaScript'>
<!--
/* ������ϥե�����Υ�����Ȥ˥ե������������� */
function set_focus() {
    // document.body.focus();   // F2/F12������ͭ���������б�
    // document.mhForm.backwardStack.focus();  // �嵭��IE�ΤߤΤ���NN�б�
}
// -->
</script>
</head>
<body onLoad='set_focus()'>
    <center>
<?php echo $menu->out_title_border()?>

        <!----------------- ������ ǯ��λ���ե����� ---------------->
        <table width='100%' border='0' cellspacing='0' cellpadding='0'>
            <tr>
                <td width='25%' nowrap style='text-align:left;' class='pt10b'>
                    <form name='page_form' method='post' action='<?php echo $menu->out_self() ?>'>
                        <table align='left' border='3' cellspacing='0' cellpadding='0'>
                            <td align='center'>
                                <input class='pt10b' type='submit' name='backward' value='����'<?php echo $backward?>>
                                <input type='hidden' name='yyyymm' value='<?php echo $pre_yyyymm ?>'>
                            </td>
                        </table>
                    </form>
                </td>
                <td width='25%' nowrap style='text-align:right;' class='pt12b'>
                    <?php echo $dailyValue ?>
                    <?php echo $totalValue ?>
                    ɽ������ǯ��
                </td>
                <td width='25%' nowrap style='text-align:left;' ><?php echo $ym_form ?></td>
                <td width='25%' nowrap style='text-align:right;' class='pt10b'>
                    <form name='page_form' method='post' action='<?php echo $menu->out_self() ?>'>
                        <table align='right' border='3' cellspacing='0' cellpadding='0'>
                            <td align='center'>
                                <input class='pt10b' type='submit' name='forward' value='����'<?php echo $forward?>>
                                <input type='hidden' name='yyyymm' value='<?php echo $next_yyyymm ?>'>
                            </td>
                        </table>
                    </form>
                </td>
            </tr>
        </table>
        
        <!--------------- �������饰��� ���� ��ɽ������ -------------------->
<table width='100%' align='center' border='0' cellspacing='0' cellpadding='0'>
    <tr>
        <td align='center'>
            <img src='<?php echo $graph_name1 . "?" . $uniq ?>' alt='������ ���� ���� �����' border='0'>
        </td>
    </tr>
</table>

<br>

<!--
<table align='center' width='60' bgcolor='blue' border='3' cellspacing='0' cellpadding='0'>
    <form method='post' action='<?php echo $menu->out_RetUrl()?>'>
        <td align='center'><input class='pt12b' type='submit' name='return' value='���'></td>
    </form>
</table>
-->
        <!--------------- �������饰��� ����� ��ɽ������ -------------------->
<table width='100%' align='center' border='0' cellspacing='0' cellpadding='0'>
    <tr>
        <td align='center'>
            <img src='<?php echo $graph_name2 . "?" . $uniq ?>' alt='������ ���� ���ץ� �����' border='0'>
        </td>
    </tr>
</table>

<br>

<!--
<table align='center' width='60' bgcolor='blue' border='3' cellspacing='0' cellpadding='0'>
    <form method='post' action='<?php echo $menu->out_RetUrl()?>'>
        <td align='center'><input class='pt12b' type='submit' name='return' value='���'></td>
    </form>
</table>
-->
        <!--------------- �������饰��� ��˥� ��ɽ������ -------------------->
<table width='100%' align='center' border='0' cellspacing='0' cellpadding='0'>
    <tr>
        <td align='center'>
            <img src='<?php echo $graph_name3 . "?" . $uniq ?>' alt='������ ���� ��˥� �����' border='0'>
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
