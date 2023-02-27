<?php
//////////////////////////////////////////////////////////////////////////////
// �Ұ���˥塼�μҰ�̾�����鷱����Ͽ ������ Branch (ʬ��)����            //
// Copyright(C) 2004-2019 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp       //
// Changed history                                                          //
// 2004/02/20 Created   print_emp_branch.php                                //
// 2007/10/15 E_ALL �� E_ALL | E_STRICT  ����¾                             //
// 2010/06/16 ����Ū����޼�����970268�ˤ������Ǥ���褦���ѹ�         ��ë //
// 2018/04/20 ����ʬ�Τߤζ��顦��ư������ɲ�                         ��ë //
// 2019/09/17 ͭ�������Ģ��ǯ�٤�����μ����Ϥ����ɲ�                 ��ë //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting',E_ALL);       // E_ALL='2047' debug ��
// ini_set('display_errors','1');          // Error ɽ�� ON debug �� ��꡼���女����
ob_start('ob_gzhandler');               // ���ϥХåե���gzip����
session_start();                        // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��
require_once ('../../function.php');
require_once ('../../tnk_func.php');
access_log();                           // Script Name �ϼ�ư����

////// �ƽи�����¸
$_SESSION['url_referer'] = H_WEB_HOST . EMP_MENU;           // �ƽФ�Ȥ�URL�򥻥å�������¸
// $_SESSION['act_referer'] = $_SERVER['HTTP_REFERER'];     // �ƽФ�Ȥ�URL�򥻥å�������¸
$url_referer = $_SESSION['url_referer'];

////////////// ǧ�ڥ����å�
if (!isset($_SESSION["User_ID"]) || !isset($_SESSION["Password"]) || !isset($_SESSION["Auth"])) {
// if (account_group_check2() == FALSE) {
// if (account_group_check() == FALSE) {
    if ($_SESSION['User_ID'] != '970268') {
        $_SESSION['s_sysmsg'] = "���ʤ��ϵ��Ĥ���Ƥ��ޤ���<br>�����Ԥ�Ϣ���Ʋ�������";
        header('Location: ' . $url_referer);
        // header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit();
    }
}

////////// �оݥ�����ץȤμ���
if (isset($_POST['emp_name'])) {
    $emp_name = $_POST['emp_name'];
} elseif (isset($_GET['emp_name'])) {
    $emp_name = $_GET['emp_name'];
    // $_SESSION['s_sysmsg'] = $_GET['emp_name'];   // Debug��
} else {
    $emp_name = '';
}

////////// ��Ģ�о�ǯ�٤μ���
if (isset($_POST['yukyulist'])) {
    $list_year = $_POST['yukyulist'];
    $_SESSION['yukyulist'] = $_POST['yukyulist'];
} elseif (isset($_GET['yukyulist'])) {
    $list_year = $_GET['yukyulist'];
    $_SESSION['yukyulist'] = $_GET['yukyulist'];
    // $_SESSION['s_sysmsg'] = $_GET['emp_name'];   // Debug��
} else {
    $ym    =  date("Ym");
    $tmp   = $ym - 200003;
    $tmp   = $tmp / 100;
    $ki    = ceil($tmp);
    $nk_ki = $ki + 44;
    $yyyy = substr($ym, 0,4);
    $mm   = substr($ym, 4,2);

    // ǯ�ٷ׻�
    if ($mm < 4) {              // 1��3��ξ��
        $business_year = $yyyy - 1;
    } else {
        $business_year = $yyyy;
    }
    $list_year = $business_year;
    $_SESSION['yukyulist'] = $list_year;
}
///////// �о�����μ���
if (isset($_POST['fivesection'])) {
    $fivesection = $_POST['fivesection'];
    $_SESSION['fivesection'] = $_POST['fivesection'];
} elseif (isset($_GET['fivesection'])) {
    $fivesection = $_GET['fivesection'];
    $_SESSION['fivesection'] = $_GET['fivesection'];
    // $_SESSION['s_sysmsg'] = $_GET['emp_name'];   // Debug��
} else {
    $fivesection = '-1';
    $_SESSION['fivesection'] = $fivesection;
}
////////// �оȥ�����ץȤ�ʬ��
switch ($emp_name) {
    
case '�Ұ�̾��(������)��ī'     :
case 'print_emp_section_ja'     :
    $script_name = EMP . 'print/print_emp_section_ja.php';
    ////////////// �����ȥ�˥塼����
    $_SESSION['site_index'] =  3;       // �Ұ���˥塼 = 3 �Ǹ�Υ�˥塼�� 99 �����
    $_SESSION['site_id']    =  1;       // �Ұ�̾��(������)��ī = 1  ���̥�˥塼̵�� (0 <=) ���̥�˥塼��ɽ���Τ� = 999
    break;
    
case '�Ұ�̾��(������)�����å�' :
case 'print_emp_section_mbfpdf' :
    $script_name = EMP . 'print/print_emp_section_mbfpdf.php';
    ////////////// �����ȥ�˥塼����
    $_SESSION['site_index'] =  3;       // �Ұ���˥塼 = 3 �Ǹ�Υ�˥塼�� 99 �����
    $_SESSION['site_id']    =  2;       // �Ұ�̾��(������)��ī = 1  ���̥�˥塼̵�� (0 <=) ���̥�˥塼��ɽ���Τ� = 999
    break;
    
case '�Ұ�̾��(������)��ī'     :
case 'print_emp_position_ja'    :
    $script_name = EMP . 'print/print_emp_position_ja.php';
    ////////////// �����ȥ�˥塼����
    $_SESSION['site_index'] =  3;       // �Ұ���˥塼 = 3 �Ǹ�Υ�˥塼�� 99 �����
    $_SESSION['site_id']    =  3;       // �Ұ�̾��(������)��ī = 1  ���̥�˥塼̵�� (0 <=) ���̥�˥塼��ɽ���Τ� = 999
    break;
    
case '�Ұ�̾��(������)�����å�' :
case 'print_emp_position_mbfpdf':
    $script_name = EMP . 'print/print_emp_position_mbfpdf.php';
    ////////////// �����ȥ�˥塼����
    $_SESSION['site_index'] =  3;       // �Ұ���˥塼 = 3 �Ǹ�Υ�˥塼�� 99 �����
    $_SESSION['site_id']    =  4;       // �Ұ�̾��(������)��ī = 1  ���̥�˥塼̵�� (0 <=) ���̥�˥塼��ɽ���Τ� = 999
    break;
    
case '���顦��ư����' :
case 'print_emp_history_mbfpdf':
    $script_name = EMP . 'print/print_emp_history_mbfpdf.php';
    ////////////// �����ȥ�˥塼����
    $_SESSION['site_index'] =  3;       // �Ұ���˥塼 = 3 �Ǹ�Υ�˥塼�� 99 �����
    $_SESSION['site_id']    =  5;       // �Ұ�̾��(������)��ī = 1  ���̥�˥塼̵�� (0 <=) ���̥�˥塼��ɽ���Τ� = 999
    break;

case '�������顦��ư����' :
case 'print_emp_history_z_mbfpdf':
    $script_name = EMP . 'print/print_emp_history_z_mbfpdf.php';
    ////////////// �����ȥ�˥塼����
    $_SESSION['site_index'] =  3;       // �Ұ���˥塼 = 3 �Ǹ�Υ�˥塼�� 99 �����
    $_SESSION['site_id']    =  6;       // �Ұ�̾��(������)��ī = 1  ���̥�˥塼̵�� (0 <=) ���̥�˥塼��ɽ���Τ� = 999
    break;

case 'ͭ�������Ģ' :
case 'print_yukyu_five_list':
    $script_name = EMP . 'print/print_yukyu_five_list.php?yukyulist=' . $list_year . '&fivesection=' . $fivesection;
    ////////////// �����ȥ�˥塼����
    $_SESSION['site_index'] =  3;       // �Ұ���˥塼 = 3 �Ǹ�Υ�˥塼�� 99 �����
    //$_SESSION['site_id']    =  5;       // �Ұ�̾��(������)��ī = 1  ���̥�˥塼̵�� (0 <=) ���̥�˥塼��ɽ���Τ� = 999
    break;

    
default:
    $script_name = EMP_MENU;           // �ƽФ�Ȥص���
    $url_name    = $url_referer;       // �ƽФ�Ȥ�URL �̥�˥塼����ƤӽФ��줿�����б�
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<title>�Ұ���˥塼���� ʬ������</title>
<script type='text/javascript' language='JavaScript'>
<!--
    parent.menu_site.location = '<?php echo H_WEB_HOST . SITE_MENU ?>';
// -->
</script>

<style type='text/css'>
<!--
body {
    margin:     20%;
    font-size:  24pt;
}
-->
</style>
</head>
<body>
    <center>
        �������᡼��(PDF)������Ǥ���<br>
        ���Ԥ���������<br>
        <img src='../../img/tnk-turbine.gif' width=68 height=72>
    </center>

    <script type='text/javascript' language='JavaScript'>
    <!--
    <?php
        if (isset($url_name)) {
            echo "location = '$url_name'";
        } else {
            echo "location = '" . H_WEB_HOST . "$script_name'";
        }
    ?>
    // -->
    </script>
</body>
</html>
<?php
ob_end_flush();                 // ���ϥХåե���gzip���� END
?>
