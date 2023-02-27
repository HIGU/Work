<?php
//////////////////////////////////////////////////////////////////////////////
// ���|�q�X�g���̏Ɖ� �� �`�F�b�N�p  �X�V�� UKWLIB/W#HIBCTR                 //
// Copyright (C) 2003-2005 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2003/11/18 Created   act_payable_view.php                                //
// 2003/11/19 �����d��m�F���X�g�Ɠˍ������o����l�Ɉȉ��̃��W�b�N��ǉ�    //
//            ���ޗ�(1)�ƕ��i�d�|�b(2-5) �Ȗ�(6)- �̍��v���z ���������O     //
//            ���j�A�̌��ޗ�1 �����O                                        //
// 2004/05/12 �T�C�g���j���[�\���E��\�� �{�^���ǉ� menu_OnOff($script)�ǉ� //
// 2005/02/15 MenuHeader class ���g�p���ċ��ʃ��j���[���y�єF�ؕ����֕ύX   //
// 2005/08/20 set_focus()�̋@�\�� MenuHeader �Ŏ������Ă���̂Ŗ���������   //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug �p
// ini_set('display_errors', '1');             // Error �\�� ON debug �p �����[�X��R�����g
// ini_set('zend.ze1_compatibility_mode', '1');    // zend 1.X �R���p�` php4�̌݊����[�h
// ini_set('implicit_flush', 'off');           // echo print �� flush �����Ȃ�(�x���Ȃ邽��) CLI��
// ini_set('max_execution_time', 1200);        // �ő���s����=20�� WEB CGI��
ob_start('ob_gzhandler');                   // �o�̓o�b�t�@��gzip���k
session_start();                            // ini_set()�̎��Ɏw�肷�邱�� Script �ŏ�s

require_once ('../function.php');           // define.php �� pgsql.php �� require_once ���Ă���
require_once ('../tnk_func.php');           // TNK �Ɉˑ����镔���̊֐��� require_once ���Ă���
require_once ('../MenuHeader.php');         // TNK �S���� menu class
access_log();                               // Script Name �͎����擾

///// TNK ���p���j���[�N���X�̃C���X�^���X���쐬
$menu = new MenuHeader(0);                  // �F�؃`�F�b�N0=��ʈȏ� �߂��=TOP_MENU �^�C�g�����ݒ�

////////////// �T�C�g�ݒ�
$menu->set_site(20, 10);                    // site_index=20(�o�����j���[) site_id=10(���|���̓���`�F�b�N���X�g)
////////////// ���^�[���A�h���X�ݒ�
// $menu->set_RetUrl(SYS_MENU);                // �ʏ�͎w�肷��K�v�͂Ȃ�
//////////// �^�C�g����(�\�[�X�̃^�C�g�����ƃt�H�[���̃^�C�g����)
$menu->set_title('���|���v�㏈�� �`�F�b�N���X�g');
//////////// �\��̐ݒ�
// $menu->set_caption('�T���v���ŃA�C�e���}�X�^�[��\�����Ă��܂�');
//////////// �ďo���action���ƃA�h���X�ݒ�
// $menu->set_action('test_template',   SYS . 'log_view/php_error_log.php');

//////////// JavaScript Stylesheet File ��K���ǂݍ��܂���
$uniq = uniqid('target');

//////////// �Ώ۔N�������擾
$act_ymd = $_SESSION['act_ymd'];
if ($act_ymd == '') {
    $act_ymd = date_offset(2);
}

//////////// ��ł̍s��
define('PAGE', '22');

//////////// SQL ���� where ��� ���p����
$search = sprintf('where act_date=%d', $act_ymd);

//////////// ���v���R�[�h���擾     (�Ώۃf�[�^�̍ő吔���y�[�W����Ɏg�p)
$query = sprintf('select count(*) from act_payable %s', $search);
if ( getUniResult($query, $maxrows) <= 0) {         // $maxrows �̎擾
    $_SESSION['s_sysmsg'] .= "���v���R�[�h���̎擾�Ɏ��s";      // .= ���b�Z�[�W��ǉ�����
}

//////////// SQL ���� where ��� ���p���� 01111=�Ȗؓ����H�� 00222=�����ۓ��� 99999=����
$search_kin = sprintf("where act_date=%d and vendor !='01111' and vendor !='00222' and vendor !='99999'", $act_ymd);

//////////// ������������v���z
$query = sprintf("select sum(Uround(order_price * siharai,0)) from act_payable %s", $search_kin);
if ( getUniResult($query, $sum_kin) <= 0) {
    $_SESSION['s_sysmsg'] .= '���v���z�̎擾�Ɏ��s';      // .= ���b�Z�[�W��ǉ�����
}

//////////// ������������v���z �Ȗ�1
$query = sprintf("select sum(Uround(order_price * siharai,0)) from act_payable %s and kamoku=1", $search_kin);
getUniResult($query, $kamoku1_kin);

//////////// ������������v���z �Ȗ�1�Ń��j�A  ������Ȗ�2-5�֐U������
$query = sprintf("select sum(Uround(order_price * siharai,0)) from act_payable %s and kamoku=1 and div='L'", $search_kin);
getUniResult($query, $kamoku1L_kin);
$kamoku1_kin = ($kamoku1_kin - $kamoku1L_kin);

//////////// ������������v���z �Ȗ�2-5
$query = sprintf("select sum(Uround(order_price * siharai,0)) from act_payable %s 
                  and kamoku>=2 and kamoku<=5", $search_kin);
getUniResult($query, $kamoku2_5_kin);
$kamoku2_5_kin = ($kamoku2_5_kin + $kamoku1L_kin);

//////////// ������������v���z �Ȗ�6�ȏ�
$query = sprintf("select sum(Uround(order_price * siharai,0)) from act_payable %s and kamoku>=6", $search_kin);
getUniResult($query, $kamoku6__kin);

//////////// �y�[�W�I�t�Z�b�g�ݒ�
if ( isset($_POST['forward']) ) {                       // ���ł������ꂽ
    $_SESSION['offset'] += PAGE;
    if ($_SESSION['offset'] >= $maxrows) {
        $_SESSION['offset'] -= PAGE;
        if ($_SESSION['s_sysmsg'] == "") {
            $_SESSION['s_sysmsg'] .= "<font color='yellow'>���ł͂���܂���B</font>";
        } else {
            $_SESSION['s_sysmsg'] .= "<br><font color='yellow'>���ł͂���܂���B</font>";
        }
    }
} elseif ( isset($_POST['backward']) ) {                // ���ł������ꂽ
    $_SESSION['offset'] -= PAGE;
    if ($_SESSION['offset'] < 0) {
        $_SESSION['offset'] = 0;
        if ($_SESSION['s_sysmsg'] == "") {
            $_SESSION['s_sysmsg'] .= "<font color='yellow'>�O�ł͂���܂���B</font>";
        } else {
            $_SESSION['s_sysmsg'] .= "<br><font color='yellow'>�O�ł͂���܂���B</font>";
        }
    }
} elseif ( isset($_GET['page_keep']) ) {               // ���݂̃y�[�W���ێ�����
    $offset = $_SESSION['offset'];
} else {
    $_SESSION['offset'] = 0;                            // ����̏ꍇ�͂O�ŏ�����
}
$offset = $_SESSION['offset'];

//////////// �\�`���̃f�[�^�\���p�̃T���v�� Query & ������
$query = sprintf("
        select
            act_date    as ������,
            type_no     as \"T\",
            uke_no      as ��t�ԍ�,
            uke_date    as ��t��,
            ken_date    as ������,
            vendor      as ������,
            name        as �����於,
            parts_no    as ���i�ԍ�,
            order_no    as �����ԍ�,
            koutei      as �H���L��,
            mtl_cond    as ����,
            order_price as �����P��,
            genpin      as ���i��,
            siharai     as �x����,
            Uround(order_price * siharai,0) as �������z,
            div         as ���ƕ�,
            kamoku      as �Ȗ�,
            sei_no      as �����ԍ�
        from
            act_payable left outer join vendor_master using(vendor)
        %s 
        ORDER BY vendor, uke_no, type_no, seq ASC
        offset %d limit %d
    ", $search, $offset, PAGE);       // ���p $search �Ō���
$res   = array();
$field = array();
if (($rows = getResultWithField2($query, $field, $res)) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("���|���̌v���:%s ��<br>�f�[�^������܂���B", format_date($act_ymd) );
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // ���O�̌ďo���֖߂�
    exit();
} else {
    $num = count($field);       // �t�B�[���h���擾
}

/////////// HTML Header ���o�͂��ăL���b�V���𐧌�
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

<!--    �t�@�C���w��̏ꍇ
<script language='JavaScript' src='template.js?<?= $uniq ?>'></script>
-->

<script language="JavaScript">
<!--
/* ���͕������������ǂ����`�F�b�N */
function isDigit(str) {
    var len=str.length;
    var c;
    for (i=1; i<len; i++) {
        c = str.charAt(i);
        if ((c < "0") || (c > "9")) {
            return true;
        }
    }
    return false;
}
/* �������̓t�H�[���̃G�������g�Ƀt�H�[�J�X������ */
function set_focus(){
    // document.body.focus();   // F2/F12�L�[��L��������Ή�
    // document.mhForm.backwardStack.focus();  // ��L��IE�݂̂̂���NN�Ή�
}
// -->
</script>

<!-- �X�^�C���V�[�g�̃t�@�C���w����R�����g HTML�^�O �R�����g�͓���q�ɏo���Ȃ����ɒ���
<link rel='stylesheet' href='template.css?<?= $uniq ?>' type='text/css' media='screen'>
-->

<style type="text/css">
<!--
.pt9 {
    font-size:      9pt;
    font-weight:    normal;
    font-family:    monospace;
}
.pt10b {
    font-size:      10pt;
    font-weight:    bold;
    font-family:    monospace;
}
.pt11b {
    font-size:      11pt;
    font-weight:    bold;
    font-family:    monospace;
}
th {
    background-color:   yellow;
    color:              blue;
    font-size:          10pt;
    font-weight:        bold;
    font-family:        monospace;
}
-->
</style>
</head>
<body onLoad='set_focus()' style='overflow-y:hidden;'>
    <center>
<?=$menu->out_title_border()?>
        
        <table width='250' border='0' cellspacing='0' cellpadding='0'>
            <tr>
                <td nowrap align='left' class='pt10b'>
                    ���@�v�@���@�z
                </td>
                <td nowrap align='right' class='pt11b'>
                    <?= number_format($sum_kin) . "\n" ?>
                </td>
            </tr>
            <tr>
                <td nowrap align='left' class='pt10b'>
                    ���i�d�|�b2�`5
                </td>
                <td nowrap align='right' class='pt11b'>
                    <?= number_format($kamoku2_5_kin) . "\n" ?>
                </td>
            </tr>
            <tr>
                <td nowrap align='left' class='pt10b'>
                    �� �� �� 1
                </td>
                <td nowrap align='right' class='pt11b'>
                    <?= number_format($kamoku1_kin) . "\n" ?>
                </td>
            </tr>
            <tr>
                <td nowrap align='left' class='pt10b'>
                    ���|�Ȗ� 6�`
                </td>
                <td nowrap align='right' class='pt11b'>
                    <?= number_format($kamoku6__kin) . "\n" ?>
                </td>
            </tr>
        </table>
        
        <!----------------- ������ �O�� ���� �̃t�H�[�� ---------------->
        <table width='100%' border='0' cellspacing='0' cellpadding='0'>
            <form name='page_form' method='post' action='<?= $menu->out_self() ?>'>
                <tr>
                    <td align='left'>
                        <table align='left' border='3' cellspacing='0' cellpadding='0'>
                            <td align='left'>
                                <input class='pt10b' type='submit' name='backward' value='�O��'>
                            </td>
                        </table>
                    </td>
                    <td nowrap align='center' class='pt11b'>
                        <?= format_date($act_ymd) . '�@' . $menu->out_title() . "\n" ?>
                    </td>
                    <td align='right'>
                        <table align='right' border='3' cellspacing='0' cellpadding='0'>
                            <td align='right'>
                                <input class='pt10b' type='submit' name='forward' value='����'>
                            </td>
                        </table>
                    </td>
                </tr>
            </form>
        </table>
        
        <!--------------- ��������{���̕\��\������ -------------------->
        <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <tr><td> <!----------- �_�~�[(�f�U�C���p) ------------>
        <table width='100%'class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <THEAD>
                <!-- �e�[�u�� �w�b�_�[�̕\�� -->
                <tr>
                    <th class='winbox' nowrap width='10'>No</th>        <!-- �s�i���o�[�̕\�� -->
                <?php
                for ($i=0; $i<$num; $i++) {             // �t�B�[���h�����J�Ԃ�
                ?>
                    <th class='winbox' nowrap><?= $field[$i] ?></th>
                <?php
                }
                ?>
                </tr>
            </THEAD>
            <TFOOT>
                <!-- ���݂̓t�b�^�[�͉����Ȃ� -->
            </TFOOT>
            <TBODY>
                        <!--  bgcolor='#ffffc6' �������F -->
                        <!-- �T���v��<td rowspan='2' colspan='3' width='200' align='center' class='pt10b' bgcolor='#ffffc6'>  </td> -->
                <?php
                for ($r=0; $r<$rows; $r++) {
                ?>
                    <tr>
                        <td class='winbox' nowrap align='right'><span class='pt10b'><?= ($r + $offset + 1) ?></span></td>    <!-- �s�i���o�[�̕\�� -->
                    <?php
                    for ($i=0; $i<$num; $i++) {         // ���R�[�h�����J�Ԃ�
                        switch ($i) {
                        case 6:
                            echo "<td class='winbox' nowrap align='left'><span class='pt9'>{$res[$r][$i]}</span></td>\n";
                            break;
                        case 11:
                        case 12:
                        case 13:
                            echo "<td class='winbox' nowrap align='right'><span class='pt9'>" . number_format($res[$r][$i], 2) . "</span></td>\n";
                            break;
                        case 14:
                            echo "<td class='winbox' nowrap align='right'><span class='pt9'>" . number_format($res[$r][$i]) . "</span></td>\n";
                            break;
                        default:
                            echo "<td class='winbox' nowrap align='center'><span class='pt9'>{$res[$r][$i]}</span></td>\n";
                        }
                    }
                    ?>
                    </tr>
                <?php
                }
                ?>
            </TBODY>
        </table>
            </td></tr>
        </table> <!----------------- �_�~�[End ------------------>
    </center>
</body>
<?=$menu->out_alert_java()?>
</html>
<?php
ob_end_flush();     // gzip���k END
?>
