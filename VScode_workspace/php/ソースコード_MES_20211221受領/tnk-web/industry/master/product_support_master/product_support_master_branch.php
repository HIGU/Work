<?php
//////////////////////////////////////////////////////////////////////////////
// �����ٱ��ʥޥ������Խ��� Branch (ʬ��)���� ��˥塼                      //
// Copyright (C) 2011-     Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2011/11/10 Created   product_support_master_branch.php                   //
//////////////////////////////////////////////////////////////////////////////
//ini_set('error_reporting', E_ALL || E_STRICT);
ob_start('ob_gzhandler');                   // ���ϥХåե���gzip����
require_once ('../../../function.php');        // define.php �� pgsql.php �� require_once ���Ƥ���
require_once ('../../../tnk_func.php');        // TNK �˰�¸������ʬ�δؿ��� require_once ���Ƥ���
require_once ('../../../MenuHeader.php');      // TNK ������ menu class
require_once ('../../../ControllerHTTP_Class.php'); // TNK ������ MVC Controller Class
access_log(); 
            
$request = new Request;
$session = new Session;

////// �ƽи�����¸
$product_master_referer = 'http:' . WEB_HOST . 'industry/master/product_support_master/product_support_master_menu.php';        // �ƽФ�Ȥ�URL�򥻥å�������¸
// $_SESSION['act_referer'] = $_SERVER['HTTP_REFERER'];     // �ƽФ�Ȥ�URL�򥻥å�������¸
$session->add('product_master_referer', $product_master_referer);

////////// �оݥ�����ץȤμ���
if ($request->get('product_master_name') != '') {
    $product_master_name = $request->get('product_master_name');
} else {
    $product_master_name = '';
}
switch ($request->get('product_master_name')) {
    case '�����ٱ��ʥޥ���������Ͽ' : $script_name = 'product_supportMaster_Main.php'; break;
    //case '���ʥ��롼�ץ����ɤ��Խ�' : $script_name = 'product_groupMaster_Main.php' ; break;
    case '�ٱ������Ͽ' : $script_name = 'product_support_groupMaster_Main.php'; break;
    case '���ʥ��롼�ץ����ɤ��Խ�' : $script_name = 'product_groupMaster_Main2.php' ; break;
    
    default: $script_name = 'product_support_master_menu.php';          // �ƽФ�Ȥص���
             $url_name    = $product_master_referer;        // �ƽФ�Ȥ�URL �̥�˥塼����ƤӽФ��줿�����б�
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<HTML>
<HEAD>
<META http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<META http-equiv="Content-Style-Type" content="text/css">
<TITLE>�����ٱ��ʥޥ��������Խ� ʬ������</TITLE>
<style type="text/css">
<!--
body        {margin:20%;font-size:24pt;}
-->
</style>
<form name='branch_form' method='post' action='<?php if (isset($url_name)) echo $url_name; else echo $script_name; ?>'>
</form>
</head>
<body onLoad='document.branch_form.submit()'>
    <center>
        ������Ǥ������Ԥ���������<br>
    </center>
</body>
</html>
<?php
ob_end_flush();                 // ���ϥХåե���gzip���� END
?>
