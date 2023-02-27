<?php
//////////////////////////////////////////////////////////////////////////////
// ��JIS�о����ʥޥ������Խ��� Branch (ʬ��)���� ��˥塼                   //
// Copyright (C) 2014-2017 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2014/11/17 Created   new_jis_master_branch.php                           //
// 2014/12/08 ���ܢ��������ѹ�                                              //
// 2014/12/22 �������������ѹ�                                              //
// 2017/04/27 �ƥ�˥塼��ɽ�����ؿ�JIS�٤���                      ��ë //
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
$newjis_master_referer = 'http:' . WEB_HOST . 'industry/new_jis/new_jis_master/new_jis_master_menu.php';        // �ƽФ�Ȥ�URL�򥻥å�������¸
// $_SESSION['act_referer'] = $_SERVER['HTTP_REFERER'];     // �ƽФ�Ȥ�URL�򥻥å�������¸
$session->add('newjis_master_referer', $newjis_master_referer);

////////// �оݥ�����ץȤμ���
if ($request->get('newjis_master_name') != '') {
    $newjis_master_name = $request->get('newjis_master_name');
} else {
    $newjis_master_name = '';
}
switch ($request->get('newjis_master_name')) {
    case '�о����ʤ���Ͽ' : $script_name = 'newjis_itemMaster_Main.php'; break;
    case '��������Ͽ' : $script_name = 'newjis_groupMaster_Main.php'; break;
    
    default: $script_name = 'new_jis_master_menu.php';          // �ƽФ�Ȥص���
             $url_name    = $newjis_master_referer;        // �ƽФ�Ȥ�URL �̥�˥塼����ƤӽФ��줿�����б�
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<HTML>
<HEAD>
<META http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<META http-equiv="Content-Style-Type" content="text/css">
<TITLE>�о����ʥޥ��������Խ� ʬ������</TITLE>
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
