<?php
//////////////////////////////////////////////////////////////////////////////////////////
// Ǽ��ͽ�ꥰ��ա������ų����٤ξȲ�(�����λŻ����İ�)  List�ե졼��                   //
// Copyright (C) 2004-2017 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp                  //
// Changed history                                                                      //
// 2021/07/07 Created  order_schedule_List.php -> copy_pepar_List.php                   //
//////////////////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL | E_STRICT);  // E_STRICT=2048(php5) E_ALL=2047 debug ��
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
ob_start('ob_gzhandler');                   // ���ϥХåե���gzip����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��
require_once ('../../function.php');        // TNK ������ function (define.php��ޤ�)
require_once ('../../MenuHeader.php');      // TNK ������ menu class
require_once ('../../ControllerHTTP_Class.php');// TNK ������ MVC Controller Class
require_once ('copy_pepar_function.php');   // copy_pepar �ط��ζ��� function
require_once ('../../tnk_func.php');        // TNK date_offset()�ǻ���
//////////// ���å����Υ��󥹥��󥹤���Ͽ
$session = new Session();
access_log();                               // Script Name �ϼ�ư����

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader();                   // ǧ�ڥ����å�0=���̰ʾ� �����=���å������ �����ȥ�̤����

////////////// ����������
$menu->set_site(70, 72);                   // site_index=70(�ʼ����Ķ���˥塼) site_id=72(�����̥��ԡ��ѻ������)
////////////// target����
$menu->set_target('_parent');               // �ե졼���Ǥ�������target°����ɬ��

//////////// ��ʬ��ե졼��������Ѥ���
// $menu->set_self(INDUST . 'copy_pepar/copy_pepar.php');
//////////// �ƽ����action̾�ȥ��ɥ쥹����

//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title('�����̥��ԡ��ѻ������');     // �����ȥ������ʤ���IE�ΰ����ΥС�������ɽ���Ǥ��ʤ��Զ�礢��

///////// �ѥ�᡼���������å�������
if (isset($_REQUEST['tnk_ki'])) {
    $div = $_REQUEST['tnk_ki'];                // ������
    $_SESSION['tnk_ki'] = $_REQUEST['tnk_ki'];    // ���å�������¸
} else {
    if (isset($_SESSION['tnk_ki'])) {
        $div = $_SESSION['tnk_ki'];            // Default(���å���󤫤�)
    } else {
        $div = getTnkKi();                         // �����
    }
}

if (isset($_REQUEST['input_mode'])) {
    $select = 'input_mode';                      // ̤�����ꥹ��
    $_SESSION['select'] = 'input_mode';          // ���å�������¸
} elseif (isset($_REQUEST['graph'])) {
    $select = 'graph';                      // Ǽ��ͽ�ꥰ���
    $_SESSION['select'] = 'graph';          // ���å�������¸
} else {
    if (isset($_SESSION['select'])) {
        $select = $_SESSION['select'];      // Default(���å���󤫤�)
    } else {
        $select = 'graph';                  // �����(Ǽ��ͽ�ꥰ���)���ޤ��̣��̵��
    }
}

if( isset($_REQUEST['update']) ) {
    $request = new Request;
    updateKiInfo($request, $div);   // ��������
}

if( isset($_REQUEST['rec_add']) ) {
    addRecord($div);                // �쥳�����ɲý���
}

if( isset($_REQUEST['busyo_copy']) ) {
    setBusyoRec($div);              // ����������̾�򥳥ԡ�
}

/////////// 
$uniq = 'id=' . uniqid('copy_paper');    // ����å����ɻ��ѥ�ˡ���ID

/////////// ���饤����ȤΥۥ���̾(����IP Address)�μ���
$hostName = gethostbyaddr($_SERVER['REMOTE_ADDR']);

////////// SQL Statement �����
$tbl_rows = getKiInfo($div, $tbl);
if( $tbl_rows <= 0 ) {
    $view = 'NG';
} else {
    $view = 'OK'; // ������� ���顼�б��ΰ�
}

if( $select == 'input_mode' ) {
    ;
} elseif ($select == 'graph') {
    $tbl_bef_rows = getKiInfo($div-1, $tbl_bef);

    $c_rows = getColumn($column);   // x����ɽ���������

    require_once ('../../../jpgraph.php');
    require_once ('../../../jpgraph_bar.php');

    for( $v_cnt=0; $v_cnt<$tbl_rows; $v_cnt++ ) {
        $graph_title = $tbl[$v_cnt][2] . ' ���ԡ��ѻ������';

        $datax = array(); $datay = array();
        $datax[0] = mb_convert_encoding('����', 'UTF-8');
        $datax_color[0] = 'blue';

        $datay[0] = 0;  // ����� ���������
        for( $cnt=0; $cnt<$tbl_bef_rows; $cnt++ ) {
            if( $tbl[$v_cnt][2] != $tbl_bef[$cnt][2]) continue;
            $datay[0] = $tbl_bef[$cnt][3];// ���������
            break;
        }

        $datax[1] = mb_convert_encoding('����', 'UTF-8');
//        $datay[1] = mb_convert_encoding(number_format($tbl[$v_cnt][3], 0), 'UTF-8');    // �����ι��
        $datay[1] = $tbl[$v_cnt][3];    // �����ι��
        $datax_color[1] = 'darkred';

        for ($r=0, $c=1; $r<$c_rows-1; $r++, $c++) {
            $datax[$r+2] = mb_convert_encoding($column[$c][0], 'UTF-8'); // ���������򥻥å�
            $datax_color[$r+2] = 'black';
        }

        for( $r=0, $f=4; $r<12; $r++, $f++ ) {
            if( $tbl[0][$f] != 0 ) {
                $datay[$r+2] = $tbl[$v_cnt][$f];    // �Ʒ�λ�������򥻥å�
            } else {
                $datay[$r+2] = '';    // �Ʒ�λ�������򥻥å�
            }
        }
/**
        require_once ('../../../jpgraph.php');
        require_once ('../../../jpgraph_bar.php');
/**/
//        $graph = new Graph(820, 360);               // ����դ��礭�� X/Y
        $graph[$v_cnt] = new Graph(820, 360);               // ����դ��礭�� X/Y
        $graph[$v_cnt]->SetScale('textlin'); 
        $graph[$v_cnt]->img->SetMargin(50, 30, 40, 70);    // ����հ��֤Υޡ����� �����岼
        $graph[$v_cnt]->SetShadow(); 
        $graph[$v_cnt]->title->SetFont(FF_GOTHIC, FS_NORMAL, 14); // FF_GOTHIC 14 �ʾ� FF_MINCHO �� 17 �ʾ����ꤹ��
        $graph[$v_cnt]->title->Set(mb_convert_encoding($graph_title, 'UTF-8')); 
        $graph[$v_cnt]->yaxis->title->SetFont(FF_GOTHIC, FS_NORMAL, 9);
        $graph[$v_cnt]->yaxis->title->Set(mb_convert_encoding('�������', 'UTF-8'));
        $graph[$v_cnt]->yaxis->title->SetMargin(10, 0, 0, 0);
        // Setup X-scale 
        $graph[$v_cnt]->xaxis->SetTickLabels($datax, $datax_color); // ��������
        // $graph[$v_cnt]->xaxis->SetFont(FF_FONT1);     // �ե���Ȥϥܡ���ɤ����Ǥ��롣
        $graph[$v_cnt]->xaxis->SetFont(FF_GOTHIC, FS_NORMAL, 10);     // �ե���Ȥϥܡ���ɤ����Ǥ��롣
        $graph[$v_cnt]->xaxis->SetLabelAngle(60); 
        // Create the bar plots 
        $bplot = new BarPlot($datay); 
//        $bplot[$v_cnt] = new BarPlot($datay);
        $bplot->SetWidth(0.6);
        // Setup color for gradient fill style 
        $bplot->SetFillGradient('navy', 'lightsteelblue', GRAD_CENTER);
        // Set color for the frame of each bar
        $bplot->SetColor('navy');
        $bplot->value->SetFormat('%d');     // �����ե����ޥå�
        $bplot->value->SetFont(FF_GOTHIC, FS_NORMAL, 11);  // 2007/09/26 �ɲ�
        $bplot->value->Show();              // ����ɽ��
        $targ = array();
        $alts = array();

        $targ[0] = "JavaScript:dummy()";
//        $alts[0] = '�������߷׻��������%d';
        if( $cnt >= $tbl_bef_rows ) {
            $alts[0] = '�������߷׻��������%d';
        } else {
            $work = number_format($tbl_bef[$cnt][3], 0);
            $alts[0] = "�������߷׻��������{$work} ��";
        }
        $targ[1] = "JavaScript:dummy()";
//        $alts[1] = '�������߷׻��������%d';
        $work = number_format($tbl[$v_cnt][3], 0);
        $alts[1] = "�������߷׻��������{$work} ��";
        for ($r=0; $r<$c_rows-1; $r++) {
            $targ[$r+2] = "JavaScript:dummy()";
//            $alts[$r+2] = "{$tbl[$v_cnt][2]}��{$column[$r+1][0]}�λ��������%d ��";
            $work = number_format($tbl[$v_cnt][$r+4], 0);
            $alts[$r+2] = "{$tbl[$v_cnt][2]}��{$column[$r+1][0]}�λ��������{$work} ��";
        }
        $bplot->SetCSIMTargets($targ, $alts); 
        $graph[$v_cnt]->Add($bplot);
        $graph_name = "graph/copy_paper_{$_SESSION['User_ID']}_{$v_cnt}_.png";
        $graph[$v_cnt]->Stroke($graph_name);
        chmod($graph_name, 0666);                   // file������rw�⡼�ɤˤ���
    }
}

/////////// ��ư�����ȼ�ư�����ξ���ڴ���
$auto_reload = 'off';

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
<?php // if ($_SESSION['s_sysmsg'] != '') echo $menu->out_site_java() ?>
<?php echo $menu->out_css() ?>
<style type='text/css'>
<!--
.pt10b {
    font-size:   0.8em;
    font-weight: bold;
    font-family: monospace;
}
th {
    font-size:      11.5pt;
    font-weight:    bold;
    font-family:    monospace;
}
.item {
    position: absolute;
    /* top: 100px; */
    left:    20px;
}
.msg {
    position: absolute;
    top:  100px;
    left: 350px;
}
.winbox {
    border-style: solid;
    border-width: 1px;
    border-top-color:       #FFFFFF;
    border-left-color:      #FFFFFF;
    border-right-color:     #999999;
    border-bottom-color:    #999999;
    background-color:#d6d3ce;
}
.winbox_gray {
    border-style: solid;
    border-width: 1px;
    border-top-color:       #FFFFFF;
    border-left-color:      #FFFFFF;
    border-right-color:     #999999;
    border-bottom-color:    #999999;
    background-color:#d6d3ce;
    color: gray;
}
.winbox_mark {
    border-style: solid;
    border-width: 1px;
    border-top-color:       #FFFFFF;
    border-left-color:      #FFFFFF;
    border-right-color:     #999999;
    border-bottom-color:    #999999;
    background-color:#eaeaee;
}
.winbox_field {
    border-style: solid;
    border-width: 1px;
    border-top-color:       #999999;
    border-left-color:      #999999;
    border-right-color:     #FFFFFF;
    border-bottom-color:    #FFFFFF;
    background-color:#d6d3ce;
}
a.link {
    color: blue;
}
a:hover {
    background-color: blue;
    color: white;
}
-->
</style>
<script type='text/javascript' language='JavaScript'>
<!--
function init() {
     setInterval('document.reload_form.submit()', 60000);   // 60��
     //  onLoad='init()' ������� <body>������������OK
}
function win_open(url) {
    var w = 820;
    var h = 620;
    var left = (screen.availWidth  - w) / 2;
    var top  = (screen.availHeight - h) / 2;
    window.open(url, 'view_win', 'width='+w+',height='+h+',scrollbars=no,status=no,toolbar=no,location=no,menubar=no,resizable=yes,top='+top+',left='+left);
}
function win_open2(url) {
    var w = 900;
    var h = 680;
    var left = (screen.availWidth  - w) / 2;
    var top  = (screen.availHeight - h) / 2;
    window.open(url, 'view_win2', 'width='+w+',height='+h+',scrollbars=no,status=no,toolbar=no,location=no,menubar=no,resizable=yes,top='+top+',left='+left);
}
function win_open3(url) {
    var w = 1100;
    var h = 620;
    var left = (screen.availWidth  - w) / 2;
    var top  = (screen.availHeight - h) / 2;
    window.open(url, 'view_win3', 'width='+w+',height='+h+',scrollbars=no,status=no,toolbar=no,location=no,menubar=no,resizable=yes,top='+top+',left='+left);
}
function win_open4(url) {
    var w = 900;
    var h = 620;
    var left = (screen.availWidth  - w) / 2;
    var top  = (screen.availHeight - h) / 2;
    window.open(url, 'view_win3', 'width='+w+',height='+h+',scrollbars=no,status=no,toolbar=no,location=no,menubar=no,resizable=yes,top='+top+',left='+left);
}
function input_mode() {
    document.input_mode_form.submit();
}
function dummy() {
    ;
}
// ���������å�
function num_check(obj) {
//    alert('TEST' + obj.value.replace(/[^0-9]+/i,''));
    return obj.value.replace(/[^0-9]+/i,'');
}
// �����ι�פ�׻�
function month_sum(row, month) {
    var total = 0;
    var name = "";
    for( var r=1; r<row; r++ ) {
        name = r + '-' + month;
        total = total + Number(document.getElementsByName(name)[0].value);
    }
    var id = "0_0-" + month;    // span��
    var obj = document.getElementById(id);

    total = total.toLocaleString().split('.')[0];   // 3��(,)���ڤ�塢(.)��ʬ�䤷�������������
    if( obj.innerHTML != total ) {
        obj.style.color = 'white';          // ʸ�����򡻿��ˤ��롣
        obj.style.backgroundColor = 'red';  // �طʿ��򡻿��ˤ��롣
    }

    obj.innerHTML = total;
    return ;
}
// Enter ����������������ΰ�ư
function enter_key( obj, row, month ) {
    if( event.keyCode == 13 ) { // Enter �����������줿
        var max = document.getElementsByName('tbl_rows')[0].value;
        if( event.shiftKey ) {  // Shift ���� ������Ƥ�ʤ��ι�
            var name = (row-=1) + '-' + month;
            if( row == 0 ) {
                name = (max-1) + '-' + month;    // �ǽ��Ԥʤ� ��Ƭ�Ԥ�
            }
        } else {
            var name = (row+=1) + '-' + month;
            if( row >= max ) {  // Shift ���� ������Ƥʤ��ʤ鲼�ι�
                name = '1-' + month;    // �ǽ��Ԥʤ� ��Ƭ�Ԥ�
            }
        }

        document.getElementsByName(name)[0].focus();
        document.getElementsByName(name)[0].select();
    }
}
//alert('TEST:');
// -->
</script>
<form name='reload_form' action='copy_pepar_List.php' method='get' target='_self'>
</form>
<form name='rec_add_form' action='copy_pepar_List.php' method='get' target='_self'>
    <input type='hidden' name='rec_add' value='on'>
</form>
<form name='busyo_copy_form' action='copy_pepar_List.php' method='get' target='_self'>
    <input type='hidden' name='busyo_copy' value='on'>
</form>
<form name='input_mode_form' action='<?php echo $menu->out_parent() ?>' method='get' target='_parent'>
    <input type='hidden' name='input_mode' value='����'>
    <input type='hidden' name='tnk_ki' value='<?php echo $div?>'>
</form>
</head>

<body <?php if ($auto_reload == 'on') echo "onLoad='init()'"; ?>>
    <center>
        <?php if ($view != 'OK') { ?>
        <table border='0' class='msg'>
            <tr>
                <td>
                    <b style='color: teal;'>���ǡ���������ޤ���</b>
                </td>
            </tr>
        </table>
        <?php if( $select == 'input_mode' ) { ?>
        <BR><BR><BR><BR><BR><BR><BR><BR><BR>
        <input type="button" value="�ǡ��������Ϥ��롣" name="rec_add" onClick='document.rec_add_form.submit()'>��
        <?php } ?>
        <?php } elseif ($select == 'input_mode') { ?>
<form name='update_form' action='copy_pepar_List.php' method='get' target='_self'>
    <input type='hidden' name='update' value='on'>
        <!-------------- �ܺ٥ǡ���ɽ���Τ����ɽ����� -------------->
        <table class='item' bgcolor='#d6d3ce'  border='1' cellspacing='0' cellpadding='2'>
           <tr><td> <!-- ���ߡ�(�ǥ�������) -->
        <table class='winbox_field' width=100% align='center' border='1' cellspacing='0' cellpadding='1'>
        <?php
        for( $r=1; $r<$tbl_rows; $r++ ) {
        echo "<tr>";
        echo "  <td width='88' align='center' nowrap>";
                    if( $r<$tbl_rows ) {
                        if( $tbl[$r][2] != "" ) {
        echo "      <input type='text' size='14' maxlength='7' name='{$r}-2' value='{$tbl[$r][2]}' onkeyup='enter_key(this, {$r}, 2);'>";
                        } else {
        echo "      <input type='text' size='14' maxlength='7' name='{$r}-2' onkeyup='enter_key(this, {$r}, 2);'>>";
                        }
                    } else {
        echo "��";
                    }
        echo "  </td>";
                for( $f=4; $f<16; $f++ ) {
        echo "  <td width='61' align='right' nowrap>";
                    if( $r<$tbl_rows ) {
                        if( $tbl[0][$f] != 0 ) {
        echo "      <input type='text' style='text-align: right;' size='9' maxlength='7' name='{$r}-{$f}' value='{$tbl[$r][$f]}' onkeyup='value = num_check(this); month_sum($tbl_rows, $f); enter_key(this, {$r}, {$f});'>";
                        } else {
        echo "      <input type='text' style='text-align: right;' size='9' maxlength='7' name='{$r}-{$f}' value='' onkeyup='value = num_check(this); month_sum($tbl_rows, $f); enter_key(this, {$r}, {$f});'>";
                        }
                    } else {
        echo "      <input type='text' style='text-align: right;' size='9' maxlength='7' name='{$r}-{$f}' value='' onkeyup='value = num_check(this); month_sum($tbl_rows, $f); enter_key(this, {$r}, {$f});'>";
                    }
        echo "  </td>";
                }
        echo "</tr>";
        }
        // ��׹Ԥν���
        echo "<tr>";
        echo "  <td width='88' align='center' nowrap>";
        echo "      <input type='hidden' name='0-2' value='{$tbl[0][2]}'>";
        echo        $tbl[0][2]; // ���
        echo "  </td>";
        for( $f=4; $f<16; $f++ ) {
        echo "  <td width='61' align='right' nowrap>";
        echo "      <input type='hidden' name='0-{$f}' value='{$tbl[0][$f]}'>";
                        if( $tbl[0][$f] != 0 ) {
        echo "      <span id='0_0-{$f}'>" . number_format($tbl[0][$f], 0) . "</span>";  // �Ʒ�ι����
                        } else {
        echo "      <span id='0_0-{$f}'>---</span>";  // �Ʒ�ι����
                        }
        echo "  </td>";
        }
        echo "</tr>";

        echo "<input type='hidden' name='tbl_rows' value='$tbl_rows'>";
        ?>
        </table> <!----- ���ߡ� End ----->
<!--
        <BR>��<input type="button" value="�ԡ��ɲ�" name="rec_add" onClick='document.rec_add_form.submit()'>��
-->
        <BR>��<input type="button" value="���Ϲ��ɲ�" name="rec_add" onClick='document.rec_add_form.submit()'>��
            �� �����ѤιԤ��ɲä��ޤ����ʹ������˼¹Ԥ�����硢�ѹ����Ƥ���¸����ޤ��󡣡�
<!--
        <BR><BR>��<input type="button" value="���� ����" name="update" onClick='document.update_form.submit()'>��
-->
        <BR><BR>��<input type="button" value="��������¸��" name="update" onClick='document.update_form.submit()'>��
            �� �������Ƥ���¸���ޤ��������𤬶���ιԤϡ��������ޤ�����
        <?php if( $tbl_rows == 1 && $tbl[0][2] == "�硡��" ) { ?>
<!--
        <BR><BR>��<input type="button" value="��������" name="busyo_copy" onClick='document.busyo_copy_form.submit()'>��
-->
        <BR><BR>��<input type="button" value="���𡡥��ԡ�" name="busyo_copy" onClick='document.busyo_copy_form.submit()'>��
            �� ����������̾�򥳥ԡ����ޤ��������ϹԤ��ʤ����Τ߻��Ѳġ�
        <?php } ?>
<!-- -->
        <BR><BR><font color='red'>�����������<a href="download_file.php/�����̥��ԡ��ѻ������_�ޥ˥奢��(����).pdf" align='center'>�ޥ˥奢��</a>��</font>
<!-- -->
            </td></tr>
        </table>
</form>
        <?php } elseif ($select == 'graph') { ?>
        <?php 
        // ����� ɽ���ν���
        for( $v_cnt=1; $v_cnt<$tbl_rows; $v_cnt++ ) {
            if( $tbl[$v_cnt][2] != "" ) {
                echo "<table width='100%' border='0'>";
                echo "  <tr>";
                echo "      <td align='center'>";
                                $name = 'copy_pepar_map' . $v_cnt;
                echo            $graph[$v_cnt]->GetHTMLImageMap($name);
                                $graph_name = "graph/copy_paper_{$_SESSION['User_ID']}_{$v_cnt}_.png";
                echo "          <img src='{$graph_name}?{$uniq}' ismap usemap='#{$name}' alt='{$tbl[$v_cnt][2]} ���ԡ��ѻ��������ӥ����' border='0'>\n";
                echo "      </td>";
                echo "  </tr>";
                echo "</table>";
            }
        }
        echo "<table width='100%' border='0'>";
        echo "  <tr>";
        echo "      <td align='center'>";
                        $name = 'copy_pepar_map' . '0';
        echo            $graph[0]->GetHTMLImageMap($name);
                        $graph_name = "graph/copy_paper_{$_SESSION['User_ID']}_0_.png";
        echo "          <img src='{$graph_name}?{$uniq}' ismap usemap='#{$name}' alt='{$tbl[0][2]} ���ԡ��ѻ��������ӥ����' border='0'>\n";
        echo "      </td>";
        echo "  </tr>";
        echo "</table>";
        ?>
        <?php } ?>
    </center>
</body>
<script language='JavaScript'>
<!--
// setTimeout('location.reload(true)',10000);      // ������ѣ�����
// -->
</script>
<?php echo $menu->out_alert_java()?>
</html>
<?php ob_end_flush(); // ���ϥХåե���gzip���� END ?>
