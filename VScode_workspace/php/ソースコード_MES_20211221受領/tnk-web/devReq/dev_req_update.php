<?php
//////////////////////////////////////////////////////////////////////////
// �ץ���೫ȯ����� �Ȳ�&�Խ�                                       //
// 2002/02/12 Copyright(C)2002-2003 Kobayashi tnksys@nitto-kohki.co.jp  //
// �ѹ�����                                                             //
// 2002/08/09 register_globals = Off �б�                               //
// 2003/12/12 define���줿����ǥǥ��쥯�ȥ�ȥ�˥塼̾����Ѥ���      //
//////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting',E_ALL);   // E_ALL='2047' debug ��
// ini_set('display_errors','1');      // Error ɽ�� ON debug �� ��꡼���女����
session_start();                    // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��
$_SESSION['s_rec_No'] = $_SESSION['s_dev_current_rec'];
require_once ("../function.php");
// include("../define.php");
access_log();       // Script Name �ϼ�ư����
if($_SESSION['Auth'] >= 3){
    if($_POST['yuusendo']=="") $_POST['yuusendo'] = NULL;
    if($_POST['sagyouku']=="") $_POST['sagyouku'] = NULL;
    if($_POST['sintyoku']=="") $_POST['sintyoku'] = NULL;
    if($_POST['kousuu']=="") $_POST['kousuu'] = 0;
    if($_POST['kanryou']=="") $_POST['kanryou'] = "1970-01-01";
    if($_POST['tantou']=="") $_POST['tantou'] = NULL;
    $_POST['tantou'] = ltrim($_POST['tantou']);
    $update_qry  = "update dev_req set ������='" . $_POST['iraibi'] . "', ��������=" . $_POST['iraibusho'] . ", �����='" . $_POST['iraisya'] . "',��Ū='" . $_POST['mokuteki'] . "',
        ����='" . $_POST['naiyou'] . "',ͥ����='" . $_POST['yuusendo'] . "',��ȶ�='". $_POST['sagyouku'] . "',��Ľ����='" . $_POST['sintyoku'] . "',��ȯ����=" . $_POST['kousuu'] . ",��λ��='" . $_POST['kanryou'] . "',
        ô����='" . $_POST['tantou'] . "' ";
    if($_POST['yosoukouka'] != "")
        $update_qry .= ",ͽ�۸���=" . $_POST['yosoukouka'] . " ";
    else
        $update_qry .= ",ͽ�۸���=NULL ";
    if($_POST['bikou'] != "")
        $update_qry .= ",����='" . $_POST['bikou'] . "' ";
    else
        $update_qry .= ",����=NULL ";
    $update_qry .= "where �ֹ�=" . $_POST['update_No'];
    if(funcConnect()){
        execQuery("begin");
        if(execQuery($update_qry)>=0){
            execQuery("commit");
            disConnectDB();
            header('Location: ' . H_WEB_HOST . DEV . 'edit_dev_req.php');
            exit();
        }else{
            execQuery("rollback");
            disConnectDB();
            $_SESSION['s_sysmsg'] = "�ǡ������ѹ��˼��Ԥ��ޤ�����<br>�ǡ����١������å���Ĵ�٤Ʋ�������";
            header('Location: ' . H_WEB_HOST . DEV . 'edit_dev_req.php');
        }
    }
}
$_SESSION['s_sysmsg'] = "�ǡ������ѹ��˼��Ԥ��ޤ�����<br>���¥��å���Ĵ�٤Ʋ�������";
header('Location: ' . H_WEB_HOST . DEV . 'edit_dev_req.php');
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<HTML>
<HEAD>
<META http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<META http-equiv="Content-Style-Type" content="text/css">
<TITLE>TNK ��ȯ�����UPDATE</TITLE>
</HEAD>
<BODY>

</BODY>
</HTML>
