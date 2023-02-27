<?php
//////////////////////////////////////////////////////////////////////////////
// ��Ŭ�����Ϣ���Ȳ�  claim_disposal_View.php                            //
// Copyright (C) 2013-2013 Norihisa.Ooya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2013/01/24 Created  claim_disposal_View.php                              //
// 2013/01/25 ɽ����������Ĵ��                                              //
// 2013/01/30 ��Ŭ����֤���������˥塼��ʬ�䤷��                        //
// 2013/05/09 �������׸��������ؤΰ١��ѹ���Ԥä���                        //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
ob_start('ob_gzhandler');                   // ���ϥХåե���gzip����
session_start();                                 // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��
/////////////// �����Ϥ��ѿ����ݴ�
if (isset($_GET['assy_no'])) {
    $_SESSION['assy_no'] = $_GET['assy_no'];                 // �����������ֹ�򥻥å�������¸
}if (isset($_GET['c_assy_no'])) {
    $_SESSION['c_assy_no'] = $_GET['c_assy_no'];             // ��Ŭ�������ֹ�򥻥å�������¸
}
if (isset($_GET['publish_no'])) {
    $_SESSION['publish_no'] = $_GET['publish_no'];           // ȯ���ֹ�򥻥å�������¸
}
if ( isset($_SESSION['assy_no']) ) {
    $assy_no = $_SESSION['assy_no'];
} else {
    $assy_no = '';
}
if ( isset($_SESSION['c_assy_no']) ) {
    $c_assy_no = $_SESSION['c_assy_no'];
} else {
    $c_assy_no = '';
}
if ( isset($_SESSION['publish_no']) ) {
    $publish_no = $_SESSION['publish_no'];
} else {
    $publish_no = '';
}
require_once ('../../function.php');             // define.php �� pgsql.php �� require_once ���Ƥ���
require_once ('../../tnk_func.php');             // TNK �˰�¸������ʬ�δؿ��� require_once ���Ƥ���
require_once ('../../MenuHeader.php');           // TNK ������ menu class
require_once ('../../ControllerHTTP_Class.php'); // TNK ������ MVC Controller Class
access_log();                                    // Script Name �ϼ�ư����
///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader(0);                  // ǧ�ڥ����å�0=���̰ʾ� �����=TOP_MENU �����ȥ�̤����
//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title('��Ŭ�����Ϣ���ξȲ�');
//////////// ����ؤ�GET�ǡ�������
$menu->set_retGET('page_keep', 'On');    
////////////// �꥿���󥢥ɥ쥹����
$menu->set_RetUrl('http:' . WEB_HOST . 'industry/custom_attention/claim_disposal_Main.php');             // �̾�ϻ��ꤹ��ɬ�פϤʤ�
$query = "
        SELECT  publish_no          AS ȯ���ֹ�                 -- 0
            ,   publish_date        AS ȯ����                   -- 1
            ,   claim_no            AS ���졼�����ɼNo         -- 2
            ,   claim_name          AS ��̾                     -- 3
            ,   assy_no             AS ASSY�ֹ�                 -- 4
            ,   parts_no            AS �����ֹ�                 -- 5
            ,   claim_explain1      AS ���ɲսꡦ����������     -- 6
            ,   claim_explain2      AS ���ɲսꡦ����������     -- 7
            ,   ans_hope_date       AS ������˾��               -- 8
            ,   delivery_date       AS Ǽ��������               -- 9
            ,   process_name        AS ����̾                   --10
            ,   claim_sec           AS ȯ������                 --11
            ,   product_no          AS ���ʵ���                 --12
            ,   delivery_num        AS Ǽ����                   --13
            ,   bad_num             AS ���ɿ�                   --14
            ,   bad_par             AS ���ɿ���                 --15
            ,   charge_no           AS �ʴ�ô��                 --16
            ,   occur_cause1        AS ȯ��������               --17
            ,   occur_cause2        AS ȯ��������               --18
            ,   outflow_cause1      AS ή�и�����               --19
            ,   outflow_cause2      AS ή�и�����               --20
            ,   occur_measures1     AS ȯ�������к���           --21
            ,   occur_measures2     AS ȯ�������к���           --22
            ,   outflow_measures1   AS ή�и����к���           --23
            ,   outflow_measures2   AS ή�и����к���           --24
        FROM
            claim_disposal_details
        WHERE assy_no = '{$c_assy_no}' and publish_no = '{$publish_no}'
        ORDER BY
            parts_no
    ";

$res = array();
if (($rows = getResultWithField2($query, $field, $res)) <= 0) {
    $_SESSION['s_sysmsg'] = "��Ŭ�����Ϣ������Ͽ������ޤ���{$c_assy_no} {$publish_no}";
} else {
    $publish_no     = $res[0][0];
    $publish_date   = $res[0][1];
    $claim_no       = $res[0][2];
    if ($res[0][3] == '') {
        $claim_name     = '��';
    } else {
        $claim_name     = $res[0][3];
    }
    $assy_no        = $res[0][4];
    if ($rows == 1) {
        if ($res[0][5] == '         ') {
            $parts_no1 = '��';
        } else {
            $parts_no1 = $res[0][5];
        }
        $parts_no2 = '��';
        $parts_no3 = '��';
    } elseif ($rows == 2) {
        if ($res[0][5] == '         ') {
            $parts_no1 = '��';
        } else {
            $parts_no1 = $res[0][5];
        }
        if ($res[1][5] == '         ') {
            $parts_no2 = '��';
        } else {
            $parts_no2 = $res[1][5];
        }
        $parts_no3 = '��';
    } elseif ($rows == 3) {
        if ($res[0][5] == '         ') {
            $parts_no1 = '��';
        } else {
            $parts_no1 = $res[0][5];
        }
        if ($res[1][5] == '         ') {
            $parts_no2 = '��';
        } else {
            $parts_no2 = $res[1][5];
        }
        if ($res[2][5] == '         ') {
            $parts_no3 = '��';
        } else {
            $parts_no3 = $res[2][5];
        }
    } else {
        $parts_no1 = '��';
        $parts_no2 = '��';
        $parts_no3 = '��';
    }
    if ($res[0][6] == '') {
        $claim_explain1 = '��';
    } else {
        $claim_explain1 = $res[0][6];
    }
    if ($res[0][7] == '') {
        $claim_explain2 = '��';
    } else {
        $claim_explain2 = $res[0][7];
    }
    $ans_hope_date  = $res[0][8];
    $delivery_date  = $res[0][9];
    if ($res[0][10] == '  ') {
        $process_name1  = '��';
    } else{
        $process_name1  = $res[0][10];
    }
    switch ($process_name1) {
        case '00':
            $process_name2  = '����';
        break;
        case '01':
            $process_name2  = '�߷�';
        break;
        case '02':
            $process_name2  = '�ù�';
        break;
        case '03':
            $process_name2  = '��Ω';
        break;
        case '04':
            $process_name2  = '����';
        break;
        case '05':
            $process_name2  = '����';
        break;
        case '06':
            $process_name2  = '�ݴ�';
        break;
        case '07':
            $process_name2  = '����';
        break;
        case '08':
            $process_name2  = '�ܵ�';
        break;
        case '09':
            $process_name2  = '����';
        break;
        default:
            $process_name2  = '��';
        break;
    }
    if ($res[0][11] == ' ') {
        $claim_sec1  = '��';
    } else{
        $claim_sec1  = $res[0][11];
    }
    switch ($claim_sec1) {
        case 1:
            $claim_sec2  = '�Ծ�';
        break;
        case 2:
            $claim_sec2  = '����';
        break;
        case 3:
            $claim_sec2  = '����¾';
        break;
        default:
            $claim_sec2  = '��';
        break;
    }
    if ($res[0][12] == '') {
        $product_no     = '��';
    } else {
        $product_no     = $res[0][12];
    }
    $delivery_num   = $res[0][13];
    $bad_num        = $res[0][14];
    $bad_par        = $res[0][15];
    if ($res[0][16] == '') {
        $charge_no     = '��';
    } else {
        $charge_no      = $res[0][16];
    }
    if ($res[0][17] == '') {
        $occur_cause1 = '��';
    } else {
        $occur_cause1 = $res[0][17];
    }
    if ($res[0][18] == '') {
        $occur_cause2 = '��';
    } else {
        $occur_cause2 = $res[0][18];
    }
    if ($res[0][19] == '') {
        $outflow_cause1 = '��';
    } else {
        $outflow_cause1 = $res[0][19];
    }
    if ($res[0][20] == '') {
        $outflow_cause2 = '��';
    } else {
        $outflow_cause2 = $res[0][20];
    }
    if ($res[0][21] == '') {
        $occur_measures1 = '��';
    } else {
        $occur_measures1 = $res[0][21];
    }
    if ($res[0][22] == '') {
        $occur_measures2 = '��';
    } else {
        $occur_measures2 = $res[0][22];
    }
    if ($res[0][23] == '') {
        $outflow_measures1 = '��';
    } else {
        $outflow_measures1 = $res[0][23];
    }
    if ($res[0][24] == '') {
        $outflow_measures2 = '��';
    } else {
        $outflow_measures2 = $res[0][24];
    }
}
$query = "
        SELECT  midsc          AS ����̾                 -- 0
        FROM
            miitem
        WHERE mipn = '{$assy_no}'
    ";

$res = array();
if (($rows = getResultWithField2($query, $field, $res)) <= 0) {
    $assy_name = '��';
} else {
    $assy_name = $res[0][0];
}

if ($parts_no1 != '��') {
    $query = "
            SELECT  midsc          AS ����̾                 -- 0
            FROM
                miitem
            WHERE mipn = '{$parts_no1}'
        ";
    
    $res = array();
    if (($rows = getResultWithField2($query, $field, $res)) <= 0) {
        $parts_name1 = '��';
    } else {
        $parts_name1 = $res[0][0];
    }
} else {
    $parts_name1 = '��';
}
if ($parts_no2 != '��') {
    $query = "
            SELECT  midsc          AS ����̾                 -- 0
            FROM
                miitem
            WHERE mipn = '{$parts_no2}'
        ";
    
    $res = array();
    if (($rows = getResultWithField2($query, $field, $res)) <= 0) {
        $parts_name2 = '��';
    } else {
        $parts_name2 = $res[0][0];
    }
} else {
    $parts_name2 = '��';
}
if ($parts_no3 != '��') {
    $query = "
            SELECT  midsc          AS ����̾                 -- 0
            FROM
                miitem
            WHERE mipn = '{$parts_no3}'
        ";
    
    $res = array();
    if (($rows = getResultWithField2($query, $field, $res)) <= 0) {
        $parts_name3 = '��';
    } else {
        $parts_name3 = $res[0][0];
    }
} else {
    $parts_name3 = '��';
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<title><?php echo  $menu->out_title() ?></title>
<?php echo  $menu->out_site_java() ?>
<?php echo  $menu->out_css() ?>
<link rel='stylesheet' href='custom_attention.css' type='text/css' media='screen'>
<script type='text/javascript' language='JavaScript' src='claim_disposal.js'></script>
</head>
<center>
<?php echo  $menu->out_title_border() ?>
    <div class='caption_font'></div>
    
    <table class='list' bgcolor='#e6e6e6' width='100%' align='center' border='1' cellspacing='0' cellpadding='3'>
        <caption>
            <?php echo $menu->out_caption(), "\n"?>
        </caption>
        <tr><td> <!-- ���ߡ� -->
    <table class='winbox_field' bgcolor='#e6e6e6' width='100%' align='center' border='1' cellspacing='0' cellpadding='3'>
        <tr>
            <th class='winbox' align='center' colspan='7'>
                ��Ŭ�����Ϣ���
            </th>
        </tr>
        <tr>
            <th class='winbox' nowrap>
                ȯ���ֹ�
            </th>
            <td class='winbox' nowrap>
                <?php echo $publish_no ?>
            </td>
            <th class='winbox' nowrap>
                ȯ����
            </th>
            <td class='winbox' nowrap>
                <?php echo format_date($publish_date) ?>
            </td>
            <th class='winbox' nowrap colspan='2'>
                ���졼�����ɼNo.
            </th>
            <td class='winbox' nowrap>
                <?php echo $claim_no ?>
            </td>
        </tr>
        <tr>
            <th class='winbox' nowrap>
                ��̾
            </th>
            <td class='winbox' nowrap colspan='6'>
                <?php echo $claim_name ?>
            </td>
        </tr>
        <tr>
            <th class='winbox' nowrap rowspan='3'>
                ASSY�ֹ�
            </th>
            <td class='winbox' nowrap rowspan='3'>
                <?php echo $assy_no ?>
            </td>
            
            <td class='winbox' rowspan='3'>
                <?php echo $assy_name ?>
            </td>
            <th class='winbox' nowrap>
                �����ֹ�
            </th>
            <td class='winbox' nowrap>
                <?php echo $parts_no1 ?>
            </td>
            <td class='winbox' colspan='2'>
                <?php echo $parts_name1 ?>
            </td>
        </tr>
        <tr>
            <th class='winbox' nowrap>
                �����ֹ�
            </th>
            <td class='winbox' nowrap>
                <?php echo $parts_no2 ?>
            </td>
            <td class='winbox' colspan='2'>
                <?php echo $parts_name2 ?>
            </td>
        </tr>
        <tr>
            <th class='winbox' nowrap>
                �����ֹ�
            </th>
            <td class='winbox' nowrap>
                <?php echo $parts_no3 ?>
            </td>
            <td class='winbox' colspan='2'>
                <?php echo $parts_name3 ?>
            </td>
        </tr>
        <tr>
            <th class='winbox' style='border-bottom:0' nowrap>
                ���ɲս�
            </th>
            <td class='winbox' style='border-bottom:0' nowrap colspan='6'>
                <?php echo $claim_explain1 ?>
            </td>
        </tr>
        <tr>
            <th class='winbox' style='border-top:0' nowrap>
                ��������
            </th>
            <td class='winbox' style='border-top:0' nowrap colspan='6'>
                <?php echo $claim_explain2 ?>
            </td>
        </tr>
        <tr>
            <th class='winbox' nowrap>
                ������˾��
            </th>
            <td class='winbox' nowrap colspan='2'>
                <?php echo format_date($ans_hope_date) ?>
            </td>
            <th class='winbox' nowrap colspan='2'>
                ȯ������
            </th>
            <td class='winbox' style='text-align:right' nowrap>
                <?php echo $claim_sec1 ?>
            </td>
            <td class='winbox' nowrap>
                <?php echo $claim_sec2 ?>
            </td>
        </tr>
        <tr>
            <th class='winbox' nowrap>
                Ǽ��������
            </th>
            <td class='winbox' nowrap colspan='2'>
                <?php echo format_date($delivery_date) ?>
            </td>
            <th class='winbox' nowrap colspan='2'>
                ���ʵ���
            </th>
            <td class='winbox' nowrap colspan='2'>
                <?php echo $product_no ?>
            </td>
        </tr>
        <tr>
            <th class='winbox' nowrap>
                ����̾
            </th>
            <td class='winbox' style='text-align:right' nowrap>
                <?php echo $process_name1 ?>
            </td>
            <td class='winbox' nowrap>
                <?php echo $process_name2 ?>
            </td>
            <th class='winbox' nowrap colspan='2'>
                Ǽ����
            </th>
            <td class='winbox' style='border-right:0; text-align:right' nowrap>
                <?php echo number_format($delivery_num) ?>
            </td>
            <td class='winbox' style='border-left:0' nowrap>
                ��
            </td>
        </tr>
        <tr>
            <th class='winbox' nowrap>
                ���ɿ�
            </th>
            <td class='winbox' style='border-right:0; text-align:right' nowrap>
                <?php echo number_format($bad_num) ?>
            </td>
            <td class='winbox' style='border-left:0' nowrap>
                ��
            </td>
            <td class='winbox' style='border-right:0; text-align:right' nowrap>
                <?php echo number_format($bad_par, 2) ?>
            </td>
            <td class='winbox' style='border-left:0' nowrap>
                ��
            </td>
            <th class='winbox' nowrap>
                �ʴ�ô��
            </th>
            <td class='winbox' nowrap>
                <?php echo $charge_no ?>
            </td>
        </tr>
        <tr>
            <th class='winbox' style='border-bottom:0' nowrap rowspan='2'>
                ȯ������
            </th>
            <td class='winbox' style='border-bottom:0' nowrap colspan='6'>
                <?php echo $occur_cause1 ?>
            </td>
        </tr>
        <tr>
            <td class='winbox' style='border-top:0' nowrap colspan='6'>
                <?php echo $occur_cause2 ?>
            </td>
        </tr>
        <tr>
            <th class='winbox' style='border-bottom:0' nowrap rowspan='2'>
                ή�и���
            </th>
            <td class='winbox' style='border-bottom:0' nowrap colspan='6'>
                <?php echo $outflow_cause1 ?>
            </td>
        </tr>
        <tr>
            <td class='winbox' style='border-top:0' nowrap colspan='6'>
                <?php echo $outflow_cause2 ?>
            </td>
        </tr>
        <tr>
            <th class='winbox' style='border-bottom:0' nowrap rowspan='2'>
                ȯ�������к�
            </th>
            <td class='winbox' style='border-bottom:0' nowrap colspan='6'>
                <?php echo $occur_measures1 ?>
            </td>
        </tr>
        <tr>
            <td class='winbox' style='border-top:0' nowrap colspan='6'>
                <?php echo $occur_measures2 ?>
            </td>
        </tr>
        <tr>
            <th class='winbox' style='border-bottom:0' nowrap rowspan='2'>
                ή�и����к�
            </th>
            <td class='winbox' style='border-bottom:0' nowrap colspan='6'>
                <?php echo $outflow_measures1 ?>
            </td>
        </tr>
        <tr>
            <td class='winbox' style='border-top:0' nowrap colspan='6'>
                <?php echo $outflow_measures2 ?>
            </td>
        </tr>
    </table>
        </td></tr> <!-- ���ߡ� -->
    </table>
</center>
</body>
<?php echo $menu->out_alert_java()?>
</html>
