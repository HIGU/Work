<?php
//////////////////////////////////////////////////////////////////////////
// �����ӥ���� ��������  �� ������                               //
// 2003/11/05 Copyright(C) 2003 K.Kobayashi tnksys@nitto-kohki.co.jp    //
// �ѹ�����                                                             //
// 2003/11/05 ��������  service_final_set.php?para  para = set || unset //
//////////////////////////////////////////////////////////////////////////
ini_set('error_reporting',E_ALL);   // E_ALL='2047' debug ��
// ini_set('display_errors','1');      // Error ɽ�� ON debug �� 
session_start();                    // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��
require_once ('../../function.php');
require_once ('../../tnk_func.php');
access_log();                       // Script Name �ϼ�ư����
$_SESSION['site_index'] = 10;       // �����֡��軻��˥塼 = 10 �Ǹ�Υ�˥塼�� 99 �����
$_SESSION['site_id']    =  5;       // �����֤���� = 5  ���̥�˥塼̵�� (0 <=)
$current_script  = $_SERVER['PHP_SELF'];        // ���߼¹���Υ�����ץ�̾����¸
// $url_referer     = $_SERVER['HTTP_REFERER'];    // �ƽФ�Ȥ�URL����¸ ���Υ�����ץȤ�ʬ�������򤷤Ƥ�����ϻ��Ѥ��ʤ�
$url_referer     = $_SESSION['service_referer'];    // ʬ������������¸����Ƥ���ƽи��򥻥åȤ���

////////////// ǧ�ڥ����å�
if (account_group_check() == FALSE) {
    $_SESSION["s_sysmsg"] = "���ʤ��ϵ��Ĥ���Ƥ��ޤ���<br>�����Ԥ�Ϣ���Ʋ�������";
    header("Location: $url_referer");                   // ľ���θƽи������
// if (!isset($_SESSION["User_ID"]) || !isset($_SESSION["Password"]) || !isset($_SESSION["Auth"])) {
//    $_SESSION["s_sysmsg"] = "ǧ�ڤ���Ƥ��ʤ���ǧ�ڴ��¤��ڤ�ޤ����������󤫤餪�ꤤ���ޤ���";
//    header("Location: http:" . WEB_HOST . "menu.php");
    exit();
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

//////////// ���������Υ��åȡ����󥻥å�
if (isset($_GET['set'])) {
    if (`/bin/touch final/{$service_ym}` == 0) {
        $_SESSION['s_sysmsg'] = "<font color='yellow'>{$service_ym}������ꤷ�ޤ�����</font>";
        header("Location: $url_referer");                   // ľ���θƽи������
        exit();
    } else {
        $_SESSION['s_sysmsg'] = "{$service_ym}�����������˼��Ԥ��ޤ�����";
        header("Location: $url_referer");                   // ľ���θƽи������
        exit();
    }
} elseif (isset($_GET['unset'])) {
    if (`/bin/rm -f final/{$service_ym}` == 0) {
        $_SESSION['s_sysmsg'] = "<font color='yellow'>{$service_ym}������������ޤ�����</font>";
        header("Location: $url_referer");                   // ľ���θƽи������
        exit();
    } else {
        $_SESSION['s_sysmsg'] = "{$service_ym}����������˼��Ԥ��ޤ�����";
        header("Location: $url_referer");                   // ľ���θƽи������
        exit();
    }
} else {
    $_SESSION['s_sysmsg'] = '�ѥ�᡼�����������Ǥ���';
    header("Location: $url_referer");                   // ľ���θƽи������
    exit();
}

?>
