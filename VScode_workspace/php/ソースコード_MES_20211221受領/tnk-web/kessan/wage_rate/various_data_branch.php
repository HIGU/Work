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

$request = new Request;
$session = new Session;

$wage_ym = $request->get('wage_ym');            // �о�ǯ�����¸

////// �ƽи�����¸
$various_referer = 'http:' . WEB_HOST . 'kessan/wage_rate/wage_various_data_input_menu.php';        // �ƽФ�Ȥ�URL�򥻥å�������¸
// $_SESSION['act_referer'] = $_SERVER['HTTP_REFERER'];     // �ƽФ�Ȥ�URL�򥻥å�������¸
$session->add('various_referer', $various_referer);

////////// �оݥ�����ץȤμ���
if ($request->get('service_name') != '') {
    $wage_name = $request->get('service_name');
} else {
    $wage_name = '';
}
switch ($request->get('service_name')) {
    case '���롼�ץޥ������Խ�' : $script_name = 'assemblyRate_groupMaster_Main.php'; break;
    case '�������Ģ�Խ�' : $script_name = 'assemblyRate_capitalAsset_Main.php' ; break;
    case '�꡼������Ģ�Խ�' : $script_name = 'assemblyRate_leasedAsset_Main.php'; break;
    case '��������ǡ����Խ�' : $script_name = 'assemblyRate_machineWork_Main.php' ; break;
    case '���ȥǡ����Խ�' : $script_name = 'assemblyRate_manRate_Main.php'; break;
    case '����Ψ�׻��ǡ����Խ�' : $script_name = 'assemblyRate_costAllocation_Main.php'; break;
    
    default: $script_name = 'wage_various_data_input_menu.php';          // �ƽФ�Ȥص���
              $url_name    = $various_referer;        // �ƽФ�Ȥ�URL �̥�˥塼����ƤӽФ��줿�����б�
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
<form name='branch_form' method='post' action='<?php if (isset($url_name)) echo $url_name; else echo $script_name; ?>'>
<input type='hidden' name='wage_ym' value='<?php echo $wage_ym ?>'>
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

