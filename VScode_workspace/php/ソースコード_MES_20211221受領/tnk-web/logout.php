<?php
//////////////////////////////////////////////////////////////////////////////
// �������칩�� �������Ƚ��� ǧ�ڤ���                                   //
// Copyright (C) 2001-2017 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2001/07/07 Created   logout.php                                          //
// 2002/08/07 ���å����������ɲ� & register_globals = Off �б�            //
// 2002/08/27 �ե졼�� �б� (JavaScript & form target='_top')               //
// 2003/03/08 $_SESSION['User_ID']=NULL �� unset($_SESSION['User_ID'])      //
// 2004/02/13 index1.php��index.php���ѹ�(index1��authenticate���ѹ��Τ���) //
// 2005/07/17 �ե졼������å����ɲ� WEB_HOST.index.php �� H_WEB_HOST���ѹ� //
// 2005/09/02 ��λ���ν�����JavaScript�ǥ��饤����Ȥβ��̰��֤���¸����    //
// 2005/09/07 ��λ���ν�����JavaScript�ǥ��饤����Ȥβ��̥���������¸����  //
// 2005/09/11 onUnload=''�ǥ��֥�����ɥ����ƥ�����ɥ��ξ�硢��˽�λ���� //
//            ����ȥ��顼�ˤʤ뤿�� try{}catch(){}���ɲ� e=[object Error]  //
//            ǧ�ڽ�λ������ArrayCookie()�Ǥ��б�(preg_match()�����)       //
// 2006/07/07 ���硼�ȥ��åȥ�����JSP/ASP������ɸ�ॿ�����ѹ�               //
// 2017/06/12 ��˿ƥ�����ɥ����Ĥ��Ƥ���ȥ��顼�ˤʤ�Τ�onUnload��      //
//            ���Ū�˥����Ȳ�                                       ��ë //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��
require_once ('function.php');              // ���̥ե��󥯥����(access_log�Τ�������ˤ���)
access_log();                               // Script Name �ϼ�ư����
$_SESSION['logout'] = date('H:i:s');
$_SESSION['site_index'] = 999;

// ���å����ν�λ�����å�
///// �ʲ���for�롼��'WINMAX'�ͤ�window_ctl.js��Ʊ�����Ǥ����
define('WINMAX', 15);
$session_end = true;    $count = 0;
// ��ʬ�����¾�Υ�����ɥ�������н�λ���ʤ�
for ($i=1; $i<=WINMAX; $i++) {
    $key = '/win' . $i . '=1/i';
    $cookie = 'win' . $i;
    if (preg_match($key, @$_COOKIE[$cookie])) $count++;
}
if ($count > 1) $session_end = false;
/*
if ($session_end) {
    unset($_SESSION['User_ID']);
    unset($_SESSION['Password']);
    unset($_SESSION['Auth']);
}
*/
// header('Location: http:' . WEB_HOST . 'index.php');
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<title></title>
<script type='text/javascript' src='base_class.js?time=<?php echo date('YmdHis') ?>'></script>
</head>
<!--
<body onUnload='try{window.opener.location.href="<?php echo H_WEB_HOST ?>"}catch(e){baseJS.Debug(e,"logout.php->onUnload->window.opener.location.href",63)}'>
-->
<body>
</body>
<script type='text/javascript' src='logout.js?time=<?php echo date('YmdHis') ?>'></script>
</html>
