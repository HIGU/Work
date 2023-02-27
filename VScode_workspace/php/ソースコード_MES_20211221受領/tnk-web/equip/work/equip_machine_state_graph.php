<?php
//////////////////////////////////////////////////////////////////////////////
// ������ž(��¤��) ������ˤ��ù��������֥����                          //
// Copyright(C) 2002-2004 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp       //
// Changed history                                                          //
// 2002/03/04 Created   equip_machine_state_graph.php                       //
// 2004/08/07 �������ذܹ�   jpGraph-1.9.1��1.16��UP                        //
// 2004/08/09 $graph->xaxis->SetTextLabelInterval(2);   function SetTextLabelInterval($aStep)
//            $graph->SetTextTickInterval(1,2);         function SetTextTickInterval($aStep,$aStart)
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��
ob_start('ob_gzhandler');                   // ���ϥХåե���gzip����
require_once ('equip_function.php');
require_once ('../tnk_func.php');
access_log();                               // Script Name �ϼ�ư����
// require_once ('../../jpGraph-1.9.1-bak/src/jpgraph.php'); 
// require_once ('../../jpGraph-1.9.1-bak/src/jpgraph_line.php'); 
require_once ('../../jpgraph.php'); 
require_once ('../../jpgraph_line.php'); 

$current_script  = $_SERVER['PHP_SELF'];        // ���߼¹���Υ�����ץ�̾����¸
// $url_referer     = $_SERVER['HTTP_REFERER'];    // �ƽФ�Ȥ�URL����¸ ���Υ�����ץȤ�ʬ�������򤷤Ƥ�����ϻ��Ѥ��ʤ�
$url_referer     = $_SESSION['equip_referer'];     // ʬ������������¸����Ƥ���ƽи��򥻥åȤ���
if ( !isset($_SESSION['User_ID']) || !isset($_SESSION['Password']) || !isset($_SESSION['Auth']) ) {
    $_SESSION['s_sysmsg'] = 'ǧ�ڤ���Ƥ��ʤ���ǧ�ڴ��¤��ڤ�ޤ�����Login���ʤ����Ʋ�������';
    header('Location: http:' . WEB_HOST . 'index1.php');
    exit();
}
$disp_rows = 2;         // ���ؼ�No�������ɽ���Կ�
if (!isset($_GET['mac_no'])) {
    if (isset($_SESSION['mac_no'])) {
        $mac_no = $_SESSION['mac_no'];
    } else {
        $mac_no = '';
        $_SESSION['s_sysmsg'] = '����No�����ꤵ��Ƥ��ޤ���!';
        header('Location: http:' . WEB_HOST . $url_referer);
        exit();
    }
} else {
    $_SESSION['mac_no'] = $_GET['mac_no'];
    $mac_no = $_GET['mac_no'];
}

if ($mac_no == '') {
    $_SESSION['s_sysmsg'] = '����No���ޥ�����̤��Ͽ�Ǥ�!';
    header('Location: http:' . WEB_HOST . $url_referer);
    exit();
}

if (equip_header_field($mac_no, 1) == NULL) {
    $_SESSION['s_sysmsg'] = '����No��$mac_no ����ž��Ͽ����Ƥ��ޤ���!';
    header('Location: http:' . WEB_HOST . $url_referer);
    exit();
}

/********** Logic Start **********/
//////////// �����ȥ�����ա���������
$today = date('Y/m/d H:i:s');

//////////// CSV File ���� �ƥե�����ɤ����
$siji_no  = equip_header_field($mac_no,1);
$parts_no = equip_header_field($mac_no,2);
$koutei   = equip_header_field($mac_no,3);
$keikaku  = equip_header_field($mac_no,4);

//////////// �����ƥ�ޥ�������������̾����
$query = "select midsc,mzist from miitem where mipn='$parts_no' limit 1";
$res=array();
if ( ($rows=getResult($query,$res)) >= 1) {      // ����̾����
    $parts_name = mb_substr($res[0][0],0,10);
    $parts_zai  = mb_substr($res[0][1],0,7);
} else {
    $parts_name = '';
    $parts_zai  = '';
}

/*************  equip_machine_state()���ѹ��Τ���ʲ�������
/////////// �����ޥ�����������֥ơ��֥������μ���
$query = "select csv_flg from equip_machine_master where mac_no='$mac_no'";
if (getUniResult($query, $state_type) <= 0) {
    $_SESSION['s_sysmsg'] .= '�����ޥ�����������֥����פμ����˼���';
    header("Location: $url_referer");                   // ľ���θƽи������
    exit();             ///// $state_type �ϰʲ��� Netmoni or �����꡼�����å������������ؤǻ���
}
*************/

//////////// ���߲ù���Υ���պ���
$query = 'select date_time,work_cnt,mac_state from equip_work_log ';
$query .= "where mac_no='$mac_no' and siji_no='$siji_no' and koutei='$koutei' order by date_time ASC ";
$res = array();
if ($rows = getResult($query,$res) ) {
    $log_cnt   = $rows;                 // ���������¸ $rows �ϲ���¿�Ѥ��뤿��
    $samp_data = sampling($rows);       // ����դλ������ѥ���ץ�󥰥���������
    $cnt = 0;                           // �����ѤΥ����󥿡�
    $t_cnt = 0;                         // 1ʬ������ѻ��ֺ����������ѤΥ����󥿡�
    $rui_time = array();                // ���� �ù�����
    $worked_time = array();             // ���Ĥ�����βù�����
    $work_cnt    = array();             // �ù��� �ƾ��֤��Ȥ� $work_cnt[����][$t_cnt]
    $rui_state   = array();             // �ƾ��֤��Ȥ����ѻ���
    $max_qry = "select max(work_cnt) from equip_work_log where mac_no='$mac_no' and siji_no='$siji_no' and koutei='$koutei'";
    $max_res = array();
    if ( ($max_rows = getResult($max_qry,$max_res)) >= 1) {
        $max_data = $max_res[0][0];
    }
    $yaxis_min_data = yaxis_min($max_data);     // work_counter �κ���ù������饰��դκǾ��ͤ򻻽�
    for ($r=0; $r<$rows; $r++) {                // ������ι�׶�ۤ򻻽�
        if ($r == 0) {
            $str_date_time = $res[$r][0];       // ����timestamp
            $worked_time[$cnt] = 0;             // ���βù�����
            $rui_time[$t_cnt] = 0;              // ���βù�����(������)
            for ($i=0; $i<=M_STAT_MAX_NO; $i++) {
                $work_cnt[$i][$r]    = $yaxis_min_data; //$res[$r][1];  // ���βù���(���̤ʤ飰�ĤΤϤ�)
            }
            $start_flag = 1;
            $cnt++;
            $t_cnt++;
            $next_time = ($str_date_time + $samp_data);     // ���Υǡ����ϥ���ץ�󥰥������ø�
        } else {
            if ($res[$r][0] < $next_time) {                 // ����ץ���ø����Ƥʤ�������Ф�
                continue;
            } else {
                $next_time = ($res[$r][0] + $samp_data);    // ���Υ���ץ���ø�˥��å�
            }
            for ($j=$res[$r-1][0]; $j<$res[$r][0]; $j += $samp_data) {  // 10ʬ����ä��Τ�log�η���ˤ�äƲ��Ѥˤ������ѻ��ֺ���
                for ($i=0; $i<=M_STAT_MAX_NO; $i++) {
                    if ($res[$r-1][2] == $i) {              // ���֤�Ʊ�����
                        if ($res[$r-1][2] == 0) {               // �Ÿ�OFF�ʤ�2�������ǧ
                            if ($r >= 2) {
                                $work_cnt[$i][$t_cnt] = $res[$r-2][1];      // �ù����������
                            } else {
                                if ($r <= 1){                   // ����Ÿ�Off���ä���
                                    $work_cnt[$i][$t_cnt] = 0;  // �ù��� 0 �������
                                } else {
                                    $work_cnt[$i][$t_cnt] = $res[$r-1][1];      // �ù����������
                                }
                            }
                        } else {
                            $work_cnt[$i][$t_cnt] = $res[$r-1][1];      // �ù����������
                        }
                    } else {
                        $work_cnt[$i][$t_cnt] = $yaxis_min_data;        // ���֤��㤦��Τϲù����򥯥ꥢ
                    }
                }
                $rui_time[$t_cnt] = Uround((($j - $str_date_time)/60),0); // ���� �ù����ַ׻�(ʬ)
                $t_cnt++;
            }
        }
    }
    ///// ���ߤκǿ�������ư�ǤΤ���
    $saisin = mktime();
    for ($j=$res[$r-1][0]; $j<$saisin; $j+=$samp_data){     // 10ʬ������ѻ��ֺ��� ����ץ�󥰤��ѹ�
        for ($i=0; $i<=M_STAT_MAX_NO; $i++) {
            if ($res[$r-1][2] == $i) {              // ���֤�Ʊ�����
                if ($res[$r-1][2] == 0) {               // �Ÿ�OFF
                    if ($r >= 2) {
                        $work_cnt[$i][$t_cnt] = $res[$r-2][1];      // �ù����������
                    } else {
                        $work_cnt[$i][$t_cnt] = $res[$r-1][1];      // �ù����������
                    }
                } else {
                    $work_cnt[$i][$t_cnt] = $res[$r-1][1];      // �ù����������
                }
            } else {
                $work_cnt[$i][$t_cnt] = $yaxis_min_data;        // ���֤��㤦��Τϲù����򥯥ꥢ
            }
        }
        $rui_time[$t_cnt] = Uround((($j - $str_date_time)/60),0); // ���� �ù����ַ׻�(ʬ)
        $t_cnt++;
    }
    ///// �ǿ����� END
} else {
    $_SESSION['s_sysmsg'] = "����No��$mac_no �ؼ�No��$siji_no ������$koutei �Υǡ���������ޤ���";
    header('Location: http:' . WEB_HOST . '/equipment/equipment_working_graph_select.php');
    exit();
}
for ($i=0; $i<=M_STAT_MAX_NO; $i++) {
    $rui_state[$i] = 0;                 // ����ν����
}
for ($r=1; $r<$rows; $r++) {                // �ƾ���������ѻ��֤򻻽�
    for ($i=0; $i<=M_STAT_MAX_NO; $i++) {
        if ($res[$r-1][2] == $i) {     // ���֤��Ѳ��������Υ쥳���ɤΰ�����Υ쥳���ɤ����
            $rui_state[$i] += ($res[$r][0]-$res[$r-1][0]);
        }
    }
}
for ($i=0; $i<=M_STAT_MAX_NO; $i++) {
    if ($res[$r-1][2] == $i) {    // �ǿ��λ��֤ȺǸ�Υ쥳���ɤκ��Ǻǿ��ǡ������ɲ�
        $rui_state[$i] += (mktime()-$res[$r-1][0]);
    }
}
for ($i=0; $i<=M_STAT_MAX_NO; $i++) {
    if ($rui_state[$i] <= 0) {
        continue;
    }
    $rui_state[$i] = Uround($rui_state[$i]/60,0);
}

$graph = new Graph(670,350,'auto');           // ����դ��礭�� X/Y
$graph->img->SetMargin(40,40,20,70);  // ����հ��֤Υޡ����� �����岼
$graph->SetScale('linlin');         // X / Y LinearX LinearY (�̾��textlin TextX LinearY)
$graph->SetShadow(); 
$graph->yscale->SetGrace(10);     // Set 10% grace. ;͵��������
$graph->yaxis->SetColor('blue');
$graph->yaxis->SetWeight(2);
$graph->yaxis->scale->ticks->SupressFirst();        // Y���κǽ�Υ����٥��ɽ�����ʤ�
$graph->yscale->SetAutoMin($yaxis_min_data);            // Y���Υ������Ȥ��ѹ�
$graph->xaxis->SetPos('min');               // X���Υץ�åȥ��ꥢ����ֲ���

// Setup X-scale 
$graph->xaxis->SetTickLabels($rui_time); 
$graph->xaxis->SetFont(FF_FONT1); 
$graph->xaxis->SetLabelAngle(90); 

// Create the first line 
$p1 = array();
for ($i=0; $i<=M_STAT_MAX_NO; $i++) {           // 0��3 �ޤǤ����� 0��15���ѹ�
    if ($rui_state[$i] <= 0) {
        continue;
    }
    equip_machine_state($mac_no, $i, $bg_color, $txt_color);
    $p1[$i] = new LinePlot($work_cnt[$i]); 
    $p1[$i]->SetFillColor($bg_color); 
    $p1[$i]->SetFillFromYMin($yaxis_min_data);  // 2004/08/06 ADD 1.10�ʾ���ѹ������ɲ�
    $p1[$i]->SetColor($bg_color); 
    $p1[$i]->SetCenter(); 
    $p1[$i]->SetStepStyle();
    $graph->Add($p1[$i]); 
}

// Output line 
$graph_name = 'graph/equip_machine_state_graph.png';
$graph->Stroke($graph_name); 

/////////////// ����ɽ(�ܺ�ɽ��)�����Τ���Υǡ�������
$query = 'select mac_no,date_time,mac_name,mac_state,work_cnt,
        macro1,macro2,macro3,macro4,macro5 from equip_work_log ';
$query .= "where mac_no='$mac_no' and siji_no='$siji_no' and koutei='$koutei' order by date_time DESC limit $disp_rows";
$res = array();
if ( ($rows=getResult2($query,$res)) <= 0) {
    $_SESSION['s_sysmsg'] = "����No��$mac_no �ؼ�No��$siji_no ������$koutei �����٤�����ޤ���";
    header('Location: http:' . WEB_HOST . $url_referer);
    exit();
} else {
    $num = count($res[0]);          // �ե�����ɿ��ʤ���28�ˤʤ� getResult2()�ʤ�OK
    // $num = 14;
}

/********** Logic End   **********/
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<HTML>
<HEAD>
<META http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<META http-equiv="Content-Style-Type" content="text/css">
<TITLE>������ž �ù��������֥����(��¤��)</TITLE>
<script language="JavaScript">
<!--
    parent.menu_site.location = 'http:<?php echo(WEB_HOST) ?>menu_site.php';
// -->
</script>
<style type="text/css">
<!--
.title_font {
    font:bold 13.5pt;
    font-family: monospace;
}
.today_font {
    font-size: 10.5pt;
    font-family: monospace;
}
.sub_font {
    font: 8.5pt;
    font-family: monospace;
}
.table_font {
    font: 11.5pt;
    font-family: monospace;
}
.pick_font {
    font: 12.0pt;
    font-family: monospace;
}
th {
    font:bold 12.0pt;
    font-family: monospace;
}
select {
    background-color:teal;
    color:white;
}
textarea {
    background-color:black;
    color:white;
}
input.sousin {
    background-color:red;
}
input.text {
    background-color:black;
    color:white;
}
.pt12b {
    font:bold 12pt;
}
.pt11b {
    font:bold 11pt;
}
.margin0 {
    margin:0%;
}
-->
</style>
</HEAD>
<BODY class='margin0'>
    <center>
        <table width='100%' bgcolor='#d6d3ce'  border='1' cellspacing='0' cellpadding='2'>
            <tr><td> <!-- ���ߡ�(�ǥ�������) -->
        <table width='100%' bordercolordark='white' bordercolorlight='#bdaa90' bgcolor='#d6d3ce'  border='1' cellspacing='0' cellpadding='1'>
            <tr>
                <form method='post' action='<?php echo $url_referer ?>'>
                    <td width='60' bgcolor='blue' align='center' valign='center'>
                        <input class='pt12b' type='submit' name='return' value='���'>
                    </td>
                </form>
                <td colspan='1' bgcolor='#d6d3ce' align='center' class='title_font'>
                    <?php
                        print("���� ��ž �ù�������Ư�������ץ����\n");
                    ?>
                </td>
                <td colspan='1' bgcolor='#d6d3ce' align='center' width='140' class='today_font'>
                    <?php echo $today ?>
                </td>
            </tr>
        </table>
            </td></tr>
        </table> <!-- ���ߡ�End -->
        <table width=100%>
            <hr color='797979'>
        </table>

        <!-- //////////// ���Ф���ɽ�� -->
        <table width='100%' bgcolor='#d6d3ce'  border='1' cellspacing='0' cellpadding='1'>
            <tr><td> <!-- ���ߡ�(�ǥ�������) -->
        <table width=100% bordercolordark='white' bordercolorlight='#bdaa90' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='1'>
            <tr class='pt11b'>
                <td align='center' nowrap>����No</td>
                <td align='center' nowrap><?php echo $parts_no ?></td>
                <td align='center' nowrap>����̾</td>
                <td align='center' nowrap><?php echo $parts_name ?></td>
                <td align='center' nowrap>���</td>
                <td align='center' nowrap><?php echo $parts_zai ?></td>
                <td align='center' nowrap>�ؼ�No</td>
                <td align='center' nowrap><?php echo $siji_no ?></td>
                <td align='center' nowrap>����</td>
                <td align='center' nowrap><?php echo $koutei ?></td>
                <td align='center' nowrap>�ײ��</td>
                <td align='center' nowrap><?php echo $keikaku ?></td>
            </tr>
        </table>
            </td></tr>
        </table> <!-- ���ߡ�End -->
        <hr color='797979'>

        <!-- // �ܺ٥ǡ���ɽ���Τ����ɽ����� -->
        <table width='100%' bgcolor='#d6d3ce'  border='1' cellspacing='0' cellpadding='1'>
            <tr><td> <!-- ���ߡ�(�ǥ�������) -->
        <table width=100% bordercolordark='white' bordercolorlight='#bdaa90' bgcolor='#d6d3ce' align='center' border='1' cellspacing='1' cellpadding='2'>
            <tr>
                <th nowrap>No</th>
                <th nowrap>����No</th><th nowrap>ǯ����</th><th nowrap>��ʬ��</th><th nowrap>�� ��</th>
                <th nowrap>����</th><th nowrap>�ù���</th><th nowrap>�ѿ�1</th><th nowrap>�ѿ�2</th>
                <th nowrap>�ѿ�3</th><th nowrap>�ѿ�4</th><th nowrap>�ѿ�5</th>
            </tr>
<?php
/////// ���߻���κǿ�������ư�ǤΤ���
print("<tr class='pick_font'>\n");
print(" <td align='center' nowrap bgcolor='blue'><font color='yellow'><b>�ǿ�</b></font></td>\n");
print(" <td align='center' nowrap bgcolor='#d6d3ce'>" . $res[0][0] . "</td>\n");
print(" <td align='center' nowrap bgcolor='#d6d3ce'>" . date("Y/m/d",mktime()) . "</td>\n");
print(" <td align='center' nowrap bgcolor='#d6d3ce'>" . date("H:i:s",mktime()) . "</td>\n");
print(" <td align='left' nowrap bgcolor='#d6d3ce'>" . $res[0][2] . "</td>\n");
    $mac_state_txt = equip_machine_state($mac_no, $res[0][3], $bg_color, $txt_color);
print(" <td align='center' nowrap bgcolor='$bg_color'><font color='$txt_color'>" . $mac_state_txt . "</font></td>\n");
print(" <td align='right' nowrap bgcolor='#d6d3ce'>" . $res[0][4] . "</td>\n");
for ($a=5; $a<=9; $a++) {
    if ($res[0][$a] == "") {
        print(" <td align='center' nowrap bgcolor='#d6d3ce'>-</td>\n");
    } else {
        print(" <td align='center' nowrap bgcolor='#d6d3ce'>" . $res[0][$a] . "</td>\n");   //�ޥ����ѿ�
    }
}
print("</tr>\n");
/////// �ǿ����� END
for ($i=0; $i<$rows; $i++) {
    print("<tr class='table_font'>\n");
    print("<td align='center' nowrap bgcolor='#d6d3ce'>" . ($i+1) . "</td>\n");
    for ($j=0; $j<$num; $j++) {
        switch ($j) {
        case 1:
            print(" <td align='center' nowrap bgcolor='#d6d3ce'>" . date("Y/m/d",$res[$i][$j]) . "</td>\n");
            print(" <td align='center' nowrap bgcolor='#d6d3ce'>" . date("H:i:s",$res[$i][$j]) . "</td>\n");
            break;
        case 2:
            print(" <td align='left' nowrap bgcolor='#d6d3ce'>" . $res[$i][$j] . "</td>\n");
            break;
        case 3:
            $mac_state_txt = equip_machine_state($mac_no, $res[$i][$j], $bg_color, $txt_color);
            print(" <td align='center' nowrap bgcolor='$bg_color'><font color='$txt_color'>" . $mac_state_txt . "</font></td>\n");
            break;
        case 4:
            print(" <td align='right' nowrap bgcolor='#d6d3ce'>" . $res[$i][$j] . "</td>\n");
            break;
        default:
            if ($res[$i][$j] == "") {
                print(" <td align='center' nowrap bgcolor='#d6d3ce'>-</td>\n");
            } else {
                print(" <td align='center' nowrap bgcolor='#d6d3ce'>" . $res[$i][$j] . "</td>\n");
            }
        }
    }
    print("</tr>\n");
}
print("</table>\n");
echo "    </td></tr>\n";
echo "</table> <!-- ���ߡ�End -->\n";


echo "<table align='center' width='100%' border='0'>\n";
echo "  <tr>\n";
echo "      <td align='center'>\n";
echo "          <font class='title_font'>������ž �ù��� ���� ����ա�</font><font class='sub_font'>�ļ�:�ù���/����:����(H)</font><br>\n";
echo "          <img src='" . $graph_name . "?" . uniqid(rand(),1) . "' alt='������ž �ù��� �����' border='0'>\n";
echo "      </td>\n";
echo "  </tr>\n";
echo "</table>\n";

echo "<table bgcolor='#d6d3ce'  border='1' cellspacing='0' cellpadding='1'>\n";
echo "    <tr><td> <!-- ���ߡ�(�ǥ�������) -->\n";
echo "<table bordercolordark='white' bordercolorlight='#bdaa90' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='1'>\n";
for ($i=0; $i<=M_STAT_MAX_NO; $i++) {
    if ($i == 0) {
        echo "  <th>-----</th>\n";
    }
    if ($rui_state[$i] <= 0) {
        continue;
    }
    $name = equip_machine_state($mac_no, $i,$bg_color,$txt_color);
    echo "  <th bgcolor='$bg_color'><font color='$txt_color'>" . $name . "</font></th>\n";
}
echo "<th>log���</th>\n";                      // debug ��
echo "<tr>\n";
echo "  <td align='center'>���ѻ���(ʬ)</td>\n";
for ($i=0; $i<=M_STAT_MAX_NO; $i++) {
    if ($rui_state[$i] <= 0) {
        continue;
    }
    $name = equip_machine_state($mac_no, $i, $bg_color, $txt_color);
    echo "      <td align='center'>" . number_format($rui_state[$i]) . "</td>\n";
}
echo "<td align='center'>$log_cnt</td> \n";      // debug ��
?>
        </tr>
        </table>
            </td></tr>
        </table> <!-- ���ߡ�End -->
        <table align='center' border='2' cellspacing='0' cellpadding='0'>
            <form method='post' action='<?php echo $url_referer ?>'>
                <td><input type='submit' name='return' value='���'></td>
            </form>
        </table>
    </center>
</BODY>
</HTML>
<?php
ob_end_flush();  //Warning: Cannot add header ���к��Τ����ɲá�
?>
