<?php
//////////////////////////////////////////////////////////////////////////////
// �����ӥ���� ������ �Ȳ�                                                 //
// Copyright(C) 2003-2004 2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp  //
// Changed history                                                          //
// 2003/10/24 Created   service_percentage_view.php                         //
//            JavaScript�ǽ����ܥ����ɲ� locattion.replace(xx_input.php)    //
// 2004/04/19 �����μ��Ӥ򥰥졼���ѹ�                                      //
// 2004/05/12 �����ȥ�˥塼ɽ������ɽ�� �ܥ����ɲ� menu_OnOff($script)�ɲ� //
// 2007/01/24 MenuHeader���饹�б�                                          //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug ��
ini_set('error_reporting',E_ALL);           // E_ALL='2047' debug ��
// ini_set('display_errors','1');              // Error ɽ�� ON debug �� 
ob_start('ob_gzhandler');                   // ���ϥХåե���gzip����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��

require_once ('../../function.php');
require_once ('../../tnk_func.php');
require_once ('../../MenuHeader.php');      // TNK ������ menu class
access_log();                               // Script Name �ϼ�ư����

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader(0);                  // ǧ�ڥ����å�0=���̰ʾ� �����=TOP_MENU �����ȥ�̤����

////////////// ����������
$menu->set_site(10,  5);                    // site_index=10(»�ץ�˥塼) site_id=5(�����ӥ�����˥塼)

$current_script  = $_SERVER['PHP_SELF'];        // ���߼¹���Υ�����ץ�̾����¸

//////// ��ʬ���ȤθƽФ��λ��ϥ��å�������¸���ʤ�
if ( preg_match('/service_percentage_view.php/', $_SERVER['HTTP_REFERER']) ) {
    $url_referer = $_SESSION['service_view_referer'];
} else {
    $_SESSION['service_view_referer'] = $_SERVER['HTTP_REFERER'];       // �ƽФ�Ȥ�URL�򥻥å�������¸
    $url_referer = $_SESSION['service_view_referer'];
}
////////////// �꥿���󥢥ɥ쥹����(���л��ꤹ����)
$menu->set_RetUrl($url_referer);        // �嵭�η�̤򥻥å�
//////////// �ƽ����action̾�ȥ��ɥ쥹����
// $menu->set_action('test_template',   SYS . 'log_view/php_error_log.php');

///////////// �о�����μ���
if (isset($_POST['section_id'])) {
    $_SESSION['service_id']   = $_POST['section_id'];
    $_SESSION['section_name'] = $_POST['section_name'];
    $section_id   = $_POST['section_id'];
    $section_name = $_POST['section_name'];
} else {
    $section_id   = $_SESSION['service_id'];
    $section_name = $_SESSION['section_name'];
}

//////////// �о�ǯ��Υ��å����ǡ�������
if (isset($_SESSION['service_ym'])) {
    $service_ym = $_SESSION['service_ym']; 
} else {
    $service_ym = date('Ym');        // ���å����ǡ������ʤ����ν����(����)
    if (substr($service_ym,4,2) != 01) {
        $service_ym--;
    } else {
        $service_ym = $service_ym - 100;
        $service_ym = $service_ym + 11;   // ��ǯ��12��˥��å�
    }
}

//////////// �����ȥ�����ա���������
$today = date("Y/m/d H:i:s");
//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
if (substr($service_ym,6,2) == '32') {
    $view_ym = substr($service_ym,0,6) . '�軻';
} else {
    $view_ym = $service_ym;
}
$menu_title = "$view_ym �����ӥ���� $section_name ���� �Ȳ�";
//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title($menu_title);

///// ��Ⱦ���� ǯ��λ���
$yyyy = substr($service_ym, 0,4);
$mm   = substr($service_ym, 4,2);
if (($mm >= 1) && ($mm <= 3)) {
    $yyyy = ($yyyy - 1);
    $zenki_ym = $yyyy . '09';     // ����ǯ��
} elseif (($mm >= 10) && ($mm <= 12)) {
    $zenki_ym = $yyyy . '09';     // ����ǯ��
} else {
    $zenki_ym = $yyyy . '03';     // ����ǯ��
}

////////// �ǡ����١����ؤ���³
if ( !($con = db_connect()) ) {
    $_SESSION['s_sysmsg'] = '�ǡ����١�������³�Ǥ��ޤ���';
    header("Location: $url_referer");                   // ľ���θƽи������
    exit();
}

//////////// �������������(�Ŀ�)��ȴ�Ф�
$query = sprintf("select act.act_id as ������, s_name as ��������, cd.uid as �Ұ��ֹ�, d.name as ̾��
        from cate_allocation left outer join act_table as act
            on dest_id=act.act_id
        left outer join cd_table as cd
            on act.act_id=cd.act_id
        left outer join user_detailes as d
            on cd.uid=d.uid
        where orign_id=0 and
            group_id=%d and
            act_flg='f'
        order by act.act_id",
        $section_id);
$res = array();
if (($rows = getResWithFieldTrs($con, $query, $field, $res)) <= 0) {
    $_SESSION['s_sysmsg'] = '�����������٤������Ǥ��ޤ���';
    header("Location: $url_referer");                   // ľ���θƽи������
    exit();
} else {
    $num = count($field);       // �ե�����ɿ�����
    //////////// history ���Τ�����Ѥ���Ƥ���ե������̾��ȴ�Ф� group by�� item_no,item �����ݥ����
    $query = "select item, item_no, intext from service_percent_history
              where service_ym=$service_ym group by item_no, item, intext order by intext, item_no";
    if (($rows_item = getResultTrs($con, $query, $res_item)) <= 0) {
        $_SESSION['s_sysmsg'] = "�����ӥ���礬���Ϥ���Ƥ��ޤ���<br>ǯ���$service_ym";
        header("Location: $url_referer");                   // ľ���θƽи������
        exit();
    } else {
        for ($i=$num; $i<($rows_item+$num); $i++) {
            $field[$i]        = $res_item[$i-$num][0];
            $item_no[$i-$num] = $res_item[$i-$num][1];
            $intext[$i]       = $res_item[$i-$num][2];
        }
        $field[$i] = '�硡��';
        $num_p = count($field);     // �ե�����ɿ����� num_p = num+��ά
    }
    /********* �ʲ��� service_percentage_input.php �ǻ��Ѥ��Ƥ������å�
    $query = "select item, item_no from service_item_master order by intext, item_no";
    if ( ($rows_item=getResultTrs($con, $query, $res_item)) <= 0) {
        $_SESSION['s_sysmsg'] = 'ľ������Υޥ������������Ǥ��ޤ���';
        header("Location: $url_referer");                   // ľ���θƽи������
        exit();
    } else {
        for ($i=$num; $i<($rows_item+$num); $i++) {
            $field[$i] = $res_item[$i-$num][0];
            // $item[$i-$num]    = $res_item[$i-$num][0];
            $item_no[$i-$num] = $res_item[$i-$num][1];
        }
        $field[$i] = '�硡��';
        $num_p = count($field);     // �ե�����ɿ����� num_p = num+��ά
    }
    **********/
}

///////////// �������Ӥ����
for ($r=0; $r<$rows; $r++) {
    $zenki[$r]['���'] = 0;
    for ($f=0; $f<$rows_item; $f++) {
        $query = sprintf("select percent from service_percent_history
                where service_ym=%d and act_id=%d and uid='%s' and item_no=%d", $zenki_ym . '32', $res[$r][0], $res[$r][2], $item_no[$f]);
        if (getUniResult($query, $res_user) > 0) {
            $zenki[$r][$f]      = ($res_user * 100);    // ����Ѵ�
            $zenki[$r]['���'] += $zenki[$r][$f];
        } else {
            $zenki[$r][$f] = 0;
        }
    }
}

/////////////////////// �Ȳ��ѥե�����
for ($r=0; $r<$rows; $r++) {
    $percent[$r]['���'] = 0;   // �����
    for ($f=0; $f<$rows_item; $f++) {
        ///// ��Ͽ�ѤߤΥ����å�
        $query = sprintf("select percent from service_percent_history
                where service_ym=%d and act_id=%d and uid='%s' and item_no=%d", $service_ym, $res[$r][0], $res[$r][2], $item_no[$f]);
        if (getUniResTrs($con, $query, $res_pert) > 0) {
            $percent[$r][$f]      = ($res_pert * 100);      // ����Ѵ�
            $percent[$r]['���'] += $percent[$r][$f];
        } else {
            $percent[$r][$f] = '';
        }
    }
}

/////////// HTML Header ����Ϥ��ƥ���å��������
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
<?php echo $menu->out_jsBaseClass() ?>
<script language="JavaScript">
<!--
/* ����ʸ�����������ɤ��������å� */
function isDigit(str) {
    var len=str.length;
    var c;
    for (i=0; i<len; i++) {
        c = str.charAt(i);
        if (("0" > c) || (c > "9")) {
            alert("���Ͱʳ������Ͻ���ޤ���");
            return false;
        }
    }
    return true;
}
// -->
</script>
<style type="text/css">
<!--
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
.pt10 {
    font-size:  10pt;
}
.pt10b {
    font:bold 10pt;
}
.pt10b {
    font-size:   10pt;
    font-weight: bold;
}
.pt11bR {
    font-size:   11pt;
    font-weight: bold;
    color: red;
    font-family: monospace;
}
.pt11b {
    font-size:   11pt;
    font-weight: bold;
}
.pt12b {
    font-size:   12pt;
    font-weight: bold;
    font-family: monospace;
}
th {
    font-size:   9pt;
    font-weight: bold;
}
.title-font {
    font-size:   13.5pt;
    font-weight: bold;
    font-family: monospace;
    border-top:1.0pt solid windowtext;
    border-right:none;
    border-bottom:1.0pt solid windowtext;
    border-left:0.5pt solid windowtext;
}
.today-font {
    font-size: 10.5pt;
    font-family: monospace;
    border-top:1.0pt solid windowtext;
    border-right:1.0pt solid windowtext;
    border-bottom:1.0pt solid windowtext;
    border-left:0.5pt solid windowtext;
}
.explain_font {
    font-size: 8.5pt;
    font-family: monospace;
}
.margin0 {
    margin:0%;
}
.right{
    text-align:right;
    font:bold 12pt;
    font-family: monospace;
}
.zenki {
    font-size:  10pt;
    color:      gray;
}
-->
</style>
</head>
<body>
    <center>
<?php echo $menu->out_title_border() ?>
        
            <form name='page_form' method='post' action='<?= $url_referer ?>'>
        <!----------------- ������ ���� ���� �Υե����� ---------------->
        <table width='100%' cellspacing="0" cellpadding="0" border='0'>
            <tr>
                <!--
                <td align='left'>
                    <table align='left' border='3' cellspacing='0' cellpadding='0'>
                        <td align='left'>
                            <input class='pt10b' type='button' name='backward' value='����'>
                        </td>
                    </table>
                </td>
                -->
                <td align='center'>
                    <!-- <?= $menu_title . "��ñ�̡���\n" ?> -->
                    <table align='center' border='3' cellspacing='0' cellpadding='0'>
                        <td align='right' class='pt11b'>
                            <input class='pt11b' type='submit' name='save' value='�ϣ�'>��ñ�̡���
                            <input class='pt11b' type='button' name='repair' value='����' 
                            onClick="JavaScript:location.replace('service_percentage_input.php?view=ret')">
                        </td>
                    </table>
                </td>
                <!--
                <td align='right'>
                    <table align='right' border='3' cellspacing='0' cellpadding='0'>
                        <td align='right'>
                            <input class='pt10b' type='button' name='forward' value='����'>
                        </td>
                    </table>
                </td>
                -->
            </tr>
        </table>
        
        <!--------------- ����������ʸ��ɽ��ɽ������ -------------------->
        <table bgcolor='#d6d3ce' align='center' cellspacing="0" cellpadding="3" border='1'>
            <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
        <table bordercolordark='white' bordercolorlight='#bdaa90' bgcolor='#d6d3ce' align='center' cellspacing="0" cellpadding="3" border='1'>
            <THEAD>
                <!-- �ơ��֥� �إå�����ɽ�� -->
                <tr>
                    <th width='10' bgcolor='yellow'>No.</th>        <!-- �ԥʥ�С���ɽ�� -->
                <?php
                for ($i=0; $i<$num_p; $i++) {             // �ե�����ɿ�ʬ���֤�
                    if (isset($intext[$i])) {
                        if ($intext[$i] == 1) {            // �����(���������)
                            echo "<th bgcolor='#ffcf9c' nowrap>{$field[$i]}</th>\n";
                        } elseif ($intext[$i] == 2) {      // ������(Ĵã������)
                            echo "<th bgcolor='#ceceff' nowrap>{$field[$i]}</th>\n";
                        } else {                            // ������̾���θ��Ф�
                            echo "<th bgcolor='yellow' nowrap>{$field[$i]}</th>\n";
                        }
                    } else {
                        echo "<th bgcolor='yellow' nowrap>{$field[$i]}</th>\n";
                    }
                }
                ?>
                </tr>
            </THEAD>
            <TFOOT>
                <!-- ���ߤϥեå����ϲ���ʤ� -->
            </TFOOT>
            <TBODY>
                <?php
                for ($r=0; $r<$rows; $r++) {
                    echo "<tr>\n";
                        printf("<td class='pt10b' align='right'>%d</td>\n", $r + 1);    // ���ֹ��ɽ��
                    for ($i=0; $i<$num_p; $i++) {       // �쥳���ɿ�ʬ���֤�
                        if ($i == ($num_p - 1) ) {          // ��פʤ�
                            echo "    <td align='right' class='pt10b'>{$percent[$r]['���']}</td>\n";
                        } elseif ( $i >= $num ) {           // �����ѥե������
                            echo "<td align='right' class='pt10b'>{$percent[$r][$i-$num]}</td>\n";
                        } elseif ($res[$r][$i] != "") {     // ���ܤ������
                            echo "<td align='center' class='pt10b'>{$res[$r][$i]}</td>\n";
                        } else {                            // ���ܤ�̵�����
                            if ($i == 2) {
                                echo "<td align='center' class='pt10b'>����Τ�</td>\n";
                            } else {
                                echo "<td align='center' class='pt10b'>---</td>\n";
                            }
                        }
                    }
                    echo "</tr>\n";
                    echo "<tr>\n";
                    echo "    <td colspan='5' align='right' class='zenki'>\n";
                    echo "        ��������({$zenki_ym}�軻)\n";
                    echo "    </td>\n";
                    for ($j=0; $j<$rows_item; $j++) {
                        echo "    <td align='right' class='zenki'>{$zenki[$r][$j]}</td>\n";
                    }
                    echo "    <td align='right' class='zenki'>{$zenki[$r]['���']}</td>\n";
                    echo "</tr>\n";
                }
                ?>
            </TBODY>
        </table>
            </td></tr>
        </table> <!----------------- ���ߡ�End ------------------>
            </form>
    </center>
</body>
</html>
<?php ob_end_flush(); // ���ϥХåե���gzip���� END ?>
