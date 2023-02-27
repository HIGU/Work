<?php
//////////////////////////////////////////////////////////////////////////
// � �ǡ���(�ã�����Ψ������»�׷׻���)�Υ��ꥢ��                  //
//      ��١�����軻�١��������ִ�������䡢���ľ�����˻���      //
// 2003/10/10 Copyright(C) 2003 K.Kobayashi tnksys@nitto-kohki.co.jp    //
// �ѹ�����                                                             //
// 2003/10/10 ��������  profit_loss_clear.php?pl_table=allo_history...  //
//////////////////////////////////////////////////////////////////////////
ini_set('error_reporting',E_ALL);       // E_ALL='2047' debug ��
ini_set('display_errors','1');          // Error ɽ�� ON debug �� ��꡼���女����
// ini_set('implicit_flush', 'off');       // echo print �� flush �����ʤ�(�٤��ʤ뤿��) CLI CGI��
// ini_set('max_execution_time', 1200);    // ����¹Ի���=20ʬ CLI CGI��
ob_start("ob_gzhandler");               // ���ϥХåե���gzip����
session_start();                        // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��
require_once ('../function.php');       // define.php �� pgsql.php �� require_once ���Ƥ���
require_once ('../tnk_func.php');       // TNK �˰�¸������ʬ�δؿ��� require_once ���Ƥ���
access_log();                           // Script Name �ϼ�ư����
$_SESSION['site_index'] = 10;           // �Ǹ�Υ�˥塼    = 99   �����ƥ�����Ѥϣ�����
$_SESSION['site_id'] = 7;               // ���̥�˥塼̵�� <= 0    �ƥ�ץ졼�ȥե�����ϣ�����
$current_script  = $_SERVER['PHP_SELF'];        // ���߼¹���Υ�����ץ�̾����¸
// $url_referer     = $_SERVER["HTTP_REFERER"];    // �ƽФ�Ȥ�URL����¸ ���Υ�����ץȤ�ʬ�������򤷤Ƥ�����ϻ��Ѥ��ʤ�
$url_referer     = $_SESSION['pl_referer'];     // ʬ������������¸����Ƥ���ƽи��򥻥åȤ���

//////////////// ǧ�ڥ����å�
// if ( !isset($_SESSION['User_ID']) || !isset($_SESSION['Password']) || !isset($_SESSION['Auth']) ) {
// if ($_SESSION['Auth'] <= 2) {                // ���¥�٥뤬���ʲ��ϵ���
if (account_group_check() == FALSE) {        // ����Υ��롼�װʳ��ϵ���
    $_SESSION['s_sysmsg'] = "Account Group �ε��Ĥ�ɬ�פǤ���";
    // header("Location: http:" . WEB_HOST . "menu.php");   // ����ƽи������
    header("Location: $url_referer");                   // ľ���θƽи������
    exit();
}

/********** Logic Start **********/

//////////// �ǯ��μ���
if (isset($_SESSION['pl_ym'])) {
    $yyyymm = $_SESSION['pl_ym'];
} else {
    $yyyymm = '';
}

//////////// �оݥơ��֥�μ���
if (isset($_GET['pl_table'])) {
    if ($_GET['pl_table'] == 'allo_history') {
        $table_name = 'act_allo_history';
    } elseif ($_GET['pl_table'] == 'cl_history') {
        $table_name = 'act_cl_history';
    } elseif ($_GET['pl_table'] == 'pl_history') {
        $table_name = 'act_pl_history';
    } else {
        $_SESSION['s_sysmsg'] = '�оݥơ��֥�λ��̵꤬���Ǥ���';
        header("Location: $url_referer");               // ľ���θƽи������
        exit();
    }
} else {
    $_SESSION['s_sysmsg'] = '�оݥơ��֥뤬���ꤵ��Ƥ��ޤ���';
    header("Location: $url_referer");                   // ľ���θƽи������
    exit();
}

//////////// ����ǯ��λ���ơ��֥뤫��ǡ������(�ºݤˤ�ǯ����Ѥ���UPDATE����)
    ///// �ǡ����١����ȥ��ͥ���������
if ( ($con = db_connect()) ) {
    ///// begin �ȥ�󥶥�����󳫻�
    query_affected_trans($con, 'begin');
    ///// �оݥǡ��������뤫�����å�
    $query_chk = "select pl_bs_ym from $table_name where pl_bs_ym = $yyyymm limit 1";
    if (getUniResTrs($con, $query_chk, $res_chk) > 0) {     // �ȥ�󥶥��������Ǥ� �Ȳ����ѥ����꡼
        for ($i=1; $i<=100; $i++) {     // ���˥Хå����åפ����뤫�����å�
            $query_chk = "select pl_bs_ym from $table_name where pl_bs_ym = $yyyymm" . $i . " limit 1";
            if (getUniResTrs($con, $query_chk, $res_chk) <= 0) {     // �ȥ�󥶥��������Ǥ� �Ȳ����ѥ����꡼
                break;
            }
        }
        $query = "update $table_name set pl_bs_ym = $yyyymm" . $i . " where pl_bs_ym = $yyyymm";
        if (query_affected_trans($con, $query) > 0) {      // �����ѥ����꡼�μ¹�
            query_affected_trans($con, 'commit');           // transaction commit
            $_SESSION['s_sysmsg'] = "<font color='yellow'>�ǡ�������(UPDATE)���ޤ�����<br>�ǯ�$yyyymm</font>";
            header("Location: $url_referer");               // ľ���θƽи������
            exit();
        } else {
            query_affected_trans($con, 'rollback');         // transaction rollback
            $_SESSION['s_sysmsg'] = "�ǡ����κ��(UPDATE)�˼��ԡ�<br>�ǯ�$yyyymm";
            header("Location: $url_referer");               // ľ���θƽи������
            exit();
        }
    } else {
        query_affected_trans($con, 'rollback');         // transaction rollback
        $_SESSION['s_sysmsg'] = '�оݥǡ���������ޤ���';
        header("Location: $url_referer");               // ľ���θƽи������
        exit();
    }
} else {
    $_SESSION['s_sysmsg'] = '�ǡ����١�������³�Ǥ��ޤ���';
    header("Location: $url_referer");                   // ľ���θƽи������
    exit();
}

?>
