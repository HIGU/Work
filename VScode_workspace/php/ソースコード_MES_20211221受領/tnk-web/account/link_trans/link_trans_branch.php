<?php
//////////////////////////////////////////////////////////////////////////////
// �Ƽ�ǡ������Ϥ� Branch (ʬ��)���� ��˥塼                              //
// Copyright (C) 2006-2007 Norihisa.Ohya usoumu@nitto-kohki.co.jp           //
// Changed history                                                          //
// 2006/09/13 Created   various_data_branch.php                             //
// 2007/10/05 �ե����ooya���������٥��ɥ쥹���ѹ�                        //
// 2007/10/19 E_ALL��E_STRICT�آ������Ȳ�                                 //
// 2007/10/24 �ץ����κǸ�˲��Ԥ��ɲ�                                  //
// 2007/12/13 �о�ǯ��μ����Ϥ��Ѥ�$request������                          //
// 2007/12/29 �Ƽ��˥塼�򿷥ץ����إ���ѹ�                        //
// 2008/01/09 �ƤӽФ�������¸��$session���ѹ�                              //
//////////////////////////////////////////////////////////////////////////////
//ini_set('error_reporting', E_ALL || E_STRICT);
ob_start('ob_gzhandler');                   // ���ϥХåե���gzip����
require_once ('../../function.php');        // define.php �� pgsql.php �� require_once ���Ƥ���
require_once ('../../tnk_func.php');        // TNK �˰�¸������ʬ�δؿ��� require_once ���Ƥ���
require_once ('../../MenuHeader.php');      // TNK ������ menu class
require_once ('../../ControllerHTTP_Class.php'); // TNK ������ MVC Controller Class
access_log();             

if (isset($_POST['wage_ym'])) {
    $_SESSION['wage_ym'] = $_POST['wage_ym'];                   // �о�ǯ��򥻥å�������¸
}
if (isset($_POST['customer'])) {
    $_SESSION['customer'] = $_POST['customer'];                   // �о�ǯ��򥻥å�������¸
}

////// �ƽи�����¸
$_SESSION['link_referer'] = $_SERVER['HTTP_REFERER'];     // �ƽФ�Ȥ�URL�򥻥å�������¸

switch ($_POST['service_name']) {
    case '�ĸ���̳�Ȳ�' : $script_name = 'link_trans/link_trans_obligation_view.php'; break;
    case '�����Ȳ�' : $script_name = 'link_trans/link_trans_transaction_view.php' ; break;
    
    default: $script_name = 'link_trans_menu.php';          // �ƽФ�Ȥص���
              $url_name    = $_SESSION['link_referer'];        // �ƽФ�Ȥ�URL �̥�˥塼����ƤӽФ��줿�����б�
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<HTML>
<HEAD>
<META http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<META http-equiv="Content-Style-Type" content="text/css">
<TITLE>��Ω��Ψ ʬ������</TITLE>
<style type="text/css">
<!--
body        {margin:20%;font-size:24pt;}
-->
</style>
</head>
<body>
    <center>
        ������Ǥ������Ԥ���������<br>
        <img src='../../img/tnk-turbine.gif' width=68 height=72>
    </center>

    <script language="JavaScript">
    <!--
    <?php
        if (isset($url_name)) {
            echo "location = '$url_name'";
        } else {
            echo "location = '" . H_WEB_HOST . ACT . "$script_name'";
        }
    ?>
    // -->
    </script>
</body>
</html>
<?php
ob_end_flush();                 // ���ϥХåե���gzip���� END
?>

