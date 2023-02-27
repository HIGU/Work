<?php
//////////////////////////////////////////////////////////////////////////////
// ������ư���������ƥ�� �ؼ��ѹ��ڤӥ��Խ�  �إå����������            //
// Copyright (C) 2004-2005 2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp //
// Changed history                                                          //
// 2004/07/28 Created  equip_edit_chartHeader.php                           //
// 2004/08/02 ���Υ�åȤ���������Ф�str_timestamp DESC, end_timestamp DESC//
//            ���ǡ���������Υ�åȤλؼ��ֹ�ȹ����ֹ��������ѹ�      //
// 2004/08/08 �ե졼���Ǥ�������application��_parent���ѹ�(FRAME̵���б�) //
// 2005/09/30 ���λؼ��ֹ椬�ʤ���Х֥�å���Break����ڤ�SQLʸ��style�ѹ� //
//            Created  equip_editCount_chartHeader.php                      //
//            work_cnt �Τߤ��ѹ��Ѥ˿�������                               //
// 2007/03/27 set_site()�᥽�åɤ� INDEX_EQUIP ���ѹ� �ڤ� ����������ɲ� //
// 2007/06/29 ���Υ�åȼ�����TIMESTAMP'{$str_timestamp1}'-INTERVAL'30 day' //
//            ���ɲá�SQLʸ�κ�Ŭ���ˤ�����Υ�åȤ�̵�����Ǥ��®����     //
// 2007/09/18 E_ALL | E_STRICT ���ѹ�                                       //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL | E_STRICT);
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
ob_start('ob_gzhandler');                   // ���ϥХåե���gzip����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��
require_once ('../../function.php');        // TNK ������ function
require_once ('../../MenuHeader.php');      // TNK ������ menu class
access_log();                               // Script Name �ϼ�ư����

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader();                   // ǧ�ڥ����å�0=���̰ʾ� �����=���å������ �����ȥ�̤����

////////////// ����������
$menu->set_site(INDEX_EQUIP, 11);           // site_index=40(������˥塼) site_id=11(�ؼ��ѹ�)
////////////// target����
// $menu->set_target('application');           // �ե졼���Ǥ�������target°����ɬ��
$menu->set_target('_parent');               // �ե졼���Ǥ�������target°����ɬ��

///// ���å������ǡ�������
$mac_no   = $_SESSION['mac_no'];
$siji_no1 = $_SESSION['siji_no'];
$koutei1  = $_SESSION['koutei'];
$_SESSION['siji_no1'] = $siji_no1;
$_SESSION['koutei1']  = $koutei1 ;

////////////// ����ϥ��ƥʥ󥹤λؼ��ѹ����� ����
$menu->set_retGET('equipment_select', 'init_data_edit');    // name value �ν������

////////////// ������Ϥ��ѥ�᡼��������
// $menu->set_retGET('page_keep', 'on');   // name value �ν������
// $menu->set_retGET('mac_no', $mac_no);   // name value �ν������
// $menu->set_retPOST('page_keep', 'on');   // name value �ν������
// $menu->set_retPOST('mac_no', $mac_no);   // name value �ν������

///// �������ѿ��ν����
$mac_name = '';
$parts_no1   = '��';
$parts_no2   = '��';
$parts_name1 = '��';
$parts_name2 = '��';
$parts_mate1 = '��';
$parts_mate2 = '��';
$plan_cnt1   = '��';
$plan_cnt2   = '��';
$jisseki1    = '��';
$jisseki2    = '��';
$siji_no2    = '��';
$koutei2     = '��';
$str_timestamp1 = '��';
$end_timestamp1 = '��';
$str_timestamp2 = '��';
$end_timestamp2 = '��';
$view = 'OK';

if (isset($_POST['sort'])) {
    $sort  = $_POST['sort'];
} else {
    $sort       = 'DESC';
}

while ($mac_no != '') {
    //////////////// �����ޥ��������鵡��̾�����
    $query = "
        select trim(mac_name) as mac_name from equip_machine_master2 where mac_no={$mac_no} limit 1
    ";
    if (getUniResult($query, $mac_name) <= 0) {
        $mac_name = '��';   // error���ϵ���̾��֥��
    }
    //////////// �إå�����긽�߲ù����Ƥ����åȤ����
    $query = "
        select  to_char(str_timestamp AT TIME ZONE 'JST', 'YYYY-MM-DD HH24:MI:SS') as str_timestamp
            , to_char(end_timestamp AT TIME ZONE 'JST', 'YYYY-MM-DD HH24:MI:SS') as end_timestamp
            , parts_no
            , plan_cnt
            , jisseki
        from
            equip_work_log2_header
        where
            mac_no={$mac_no} and siji_no={$siji_no1} and koutei={$koutei1}
    ";
    $res_head = array();
    if ( getResult($query, $res_head) <= 0) {
        $_SESSION['s_sysmsg'] .= "{$mac_no}��{$mac_name}�Ǥϱ�ž���Ϥ���Ƥ��ޤ���";
        $view = 'NG';
    } else {
        $str_timestamp1 = $res_head[0]['str_timestamp'];
        $end_timestamp1 = '��';
        // $end_timestamp1 = $res_head[0]['end_timestamp'];
        $parts_no1  = $res_head[0]['parts_no'];
        $plan_cnt1  = $res_head[0]['plan_cnt'];
        $jisseki1   = $res_head[0]['jisseki'];
        $query = "
            select substr(midsc, 1, 12) as midsc, mzist from miitem where mipn='{$parts_no1}'
        ";
        $res_mi = array();
        if ( getResult($query, $res_mi) <= 0) {
            $_SESSION['s_sysmsg'] .= "{$parts_no1}�����ʥޥ������μ����˼��ԡ�";
            $view = 'NG';
        } else {
            $parts_name1 = $res_mi[0]['midsc'];
            $parts_mate1 = $res_mi[0]['mzist'];
        }
    }
    /////// ���ǡ���������Υ�åȤλؼ��ֹ�ȹ����ֹ����� (��λ�ʤ����Ǥʤ������ʤ⤢��������)
    $query = "
        SELECT siji_no, koutei
        FROM
            equip_work_log2
        WHERE
            date_time < CAST('{$str_timestamp1}' AS TIMESTAMP) AND mac_no={$mac_no}
            AND
            date_time > (TIMESTAMP '{$str_timestamp1}' - INTERVAL '30 day') AND mac_no={$mac_no}
            AND
            (siji_no != {$siji_no1} or koutei != {$koutei1})
        ORDER BY date_time DESC, mac_no DESC, mac_state DESC
        LIMIT 1
    ";
    $res_head = array();
    if ( getResult($query, $res_head) <= 0) {
        $_SESSION['s_sysmsg'] .= "{$mac_no}��{$mac_name}�Ǥϼ��ӥǡ�������ޤ���";
        $view = 'NG';
        break;
    } else {
        $siji_no2   = $res_head[0]['siji_no'];
        $koutei2    = $res_head[0]['koutei'];
        $_SESSION['siji_no2'] = $siji_no2;           // equip_edit_chartList.php���Ϥ��������¸����
        $_SESSION['koutei2']  = $koutei2 ;           //  ��
    }
    //////////// �إå���������Υ�åȤ���� (��λ�ʤ����Ǥʤ������ʤ⤢��������)
    $query = "
        select  to_char(str_timestamp AT TIME ZONE 'JST', 'YYYY-MM-DD HH24:MI:SS') as str_timestamp
            , to_char(end_timestamp AT TIME ZONE 'JST', 'YYYY-MM-DD HH24:MI:SS') as end_timestamp
            , parts_no
            , plan_cnt
            , jisseki
        from
            equip_work_log2_header
        where
            mac_no={$mac_no} and siji_no={$siji_no2} and koutei={$koutei2}
        limit 1
    ";
    $res_head = array();
    if ( getResult($query, $res_head) <= 0) {
        $_SESSION['s_sysmsg'] .= "{$mac_no}��{$mac_name}�Ǥϼ��ӥǡ�������ޤ���";
        $view = 'NG';
    } else {
        $str_timestamp2 = $res_head[0]['str_timestamp'];
        $end_timestamp2 = $res_head[0]['end_timestamp'];
        if ($end_timestamp2 == NULL) {      // ������
            $end_timestamp2 = '��';
        }
        $parts_no2  = $res_head[0]['parts_no'];
        $plan_cnt2  = $res_head[0]['plan_cnt'];
        $jisseki2   = $res_head[0]['jisseki'];
        $query = "
            select substr(midsc, 1, 12) as midsc, mzist from miitem where mipn='{$parts_no2}'
        ";
        $res_mi = array();
        if ( getResult($query, $res_mi) <= 0) {
            $_SESSION['s_sysmsg'] .= "{$parts_no2}�����ʥޥ������μ����˼��ԡ�";
            $view = 'NG';
        } else {
            $parts_name2 = $res_mi[0]['midsc'];
            $parts_mate2 = $res_mi[0]['mzist'];
        }
    }
    break;
}

//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title("{$mac_no}��{$mac_name}���ؼ����Ƥ��Խ�");
//////////// ɽ�������
$menu->set_caption('�����ֹ������');

/////////// HTML Header ����Ϥ��ƥ���å��������
$menu->out_html_header();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<title><?= $menu->out_title() ?></title>
<?= $menu->out_css() ?>
<?= $menu->out_site_java() ?>
<style type='text/css'>
<!--
select {
    background-color:   teal;
    color:              white;
}
.cur_font {
    font-size:      11pt;
    font-family:    monospace;
    color:          blue;
}
.pre_font {
    font-size:      11pt;
    font-family:    monospace;
    color:          gray;
}
.pick_font {
    font-size:      9.5pt;
    font-weight:    bold;
    font-family: monospace;
}
th {
    background-color:yellow;
    color:          blue;
    font-size:      11pt;
    font-weight:    bold;
    font-family:    monospace;
}
.item {
    position: absolute;
    top:   120px;
    left:   40px;
}
-->
</style>
<form name='MainForm' method='post'>
    <input type='hidden' name='select'  value=''>
    <input type='hidden' name='mac_no'  value='<?= $mac_no ?>'>
    <input type='hidden' name='siji_no' value='<?= $siji_no2 ?>'>
    <input type='hidden' name='koutei'  value='<?= $koutei2 ?>'>
    <input type='hidden' name='sort'    value='<?= $sort ?>'>
</form>
</head>
<body>
    <center>
<?= $menu->out_title_border() ?>
        
        <!----------------- ���Ф���ɽ�� ------------------------>
        <table width='100%' bgcolor='#d6d3ce'  border='1' cellspacing='0' cellpadding='1'>
            <tr><td> <!-- ���ߡ�(�ǥ�������) -->
        <table width=100% bordercolordark='white' bordercolorlight='#bdaa90' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='1'>
            <th nowrap>������</th><th nowrap>�ؼ�No</th><th nowrap>�����ֹ�</th><th nowrap>����̾</th>
            <th nowrap>����</th><th nowrap>�ײ��</th><th nowrap>���ӿ�</th>
            <th nowrap>��������</th><th nowrap>��λ����</th>
            <!-- ���߲ù��� -->
            <tr class='cur_font'>
                <td align='center' rowspan='2'>
                    <form name='mac_form' method='post' action='equip_edit_chartList.php' target='List'>
                        <select name='sort' class='ret_font' onChange='document.mac_form.submit()'>
                            <option value='ASC' <?php if ($sort == 'ASC') echo 'selected' ?>>  ����</option>
                            <option value='DESC' <?php if ($sort == 'DESC') echo 'selected' ?>>�߽�</option>
                        </select>
                        <input type='hidden' name='mac_no'  value='<?= $mac_no ?>'>
                        <input type='hidden' name='siji_no' value='<?= $siji_no2 ?>'>
                        <input type='hidden' name='koutei'  value='<?= $koutei2 ?>'>
                        <input type='hidden' name='select'  value='<?= $view ?>'>
                    </form>
                </td>
                <td align='center' nowrap><?= $siji_no1 ?></td>
                <td align='center' nowrap><?= $parts_no1 ?></td>
                <td align='center' nowrap><?= $parts_name1 ?></td>
                <td align='center' nowrap><?= $koutei1 ?></td>
                <td align='right'  nowrap><?= number_format($plan_cnt1) ?></td>
                <td align='right'  nowrap><?= number_format($jisseki1) ?></td>
                <td align='center' nowrap><?= $str_timestamp1 ?></td>
                <td align='center' nowrap><?= $end_timestamp1 ?></td>
                <!--<td align='center' nowrap><?= $parts_mate1 ?></td>-->
            </tr>
            <!-- ����å�(��λ��) -->
            <tr class='pre_font'>
                <td align='center' nowrap><?= $siji_no2 ?></td>
                <td align='center' nowrap><?= $parts_no2 ?></td>
                <td align='center' nowrap><?= $parts_name2 ?></td>
                <td align='center' nowrap><?= $koutei2 ?></td>
                <td align='right'  nowrap><?= number_format($plan_cnt2) ?></td>
                <td align='right'  nowrap><?= number_format($jisseki2) ?></td>
                <td align='center' nowrap><?= $str_timestamp2 ?></td>
                <td align='center' nowrap><?= $end_timestamp2 ?></td>
                <!--<td align='center' nowrap><?= $parts_mate2 ?></td>-->
            </tr>
        </table>
            </td></tr>
        </table> <!-- ���ߡ�End -->
        
        <!-- <hr color='797979'> -->
        
        <table class='item' bgcolor='#d6d3ce'  border='1' cellspacing='0' cellpadding='2'>
           <tr><td> <!-- ���ߡ�(�ǥ�������) -->
        <table width=100% align='center' bordercolordark='white' bordercolorlight='#bdaa90' bgcolor='#d6d3ce' border='1' cellspacing='1' cellpadding='1'>
            <th nowrap width='50'>No</th>
            <th nowrap width='100'>ǯ����</th>
            <th nowrap width='100'>��ʬ��</th>
            <th nowrap width='100'>����</th>
            <th nowrap width='80'>�ù���</th>
            <th nowrap width='70'>�ؼ�No</th>
            <th nowrap width='150'>�������Ȱ����ѹ�</th>
            <th nowrap width='130'>���ꥻ�åȰ���</th>
        </table> <!----- ���ߡ� End ----->
            </td></tr>
        </table>
    </center>
</body>
</html>
<Script Language='JavaScript'>
document.MainForm.select.value = '<?=$view?>';
document.MainForm.target = 'List';
document.MainForm.action = 'equip_edit_chartList.php';
document.MainForm.submit();
</Script>
<?php ob_end_flush(); // ���ϥХåե���gzip���� END ?>
