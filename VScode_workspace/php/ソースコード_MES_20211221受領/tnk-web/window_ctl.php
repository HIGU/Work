<?php
//////////////////////////////////////////////////////////////////////////////
// �������칩�� Window Control                                              //
// Copyright (C) 2002-2005 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2002/08/30 Created   window_ctl.php                                      //
// 2002/08/30 ���å����������ɲ� & register_globals = Off �б�            //
// 2003/08/25 Window �Υ����ץ�� IE ���Ѥ�fullscreen=yes������ NG      //
// 2004/02/13 index1.php��index.php���ѹ�(index1��authenticate���ѹ��Τ���) //
// 2005/01/26 location �� index.php �� authenticate.php?background=on���ѹ� //
// 2005/08/31 Window Control������˻Ĥ��ʤ������location='http://??? ���� //
//            location.replace('http://???') ���ѹ�                         //
// 2005/08/31 base_class ����Ѥ����饤����ȤΥ�����ɥ����֤���������     //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��
require_once ('function.php');              // ������ function call
access_log();                               // Script Name �ϼ�ư����
///// �֥饦�����Υ���å����̵��
$uniq = uniqid('CTL');
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<title>Window Control</title>
<script type='text/javascript' src='base_class.js?id=<%=$uniq%>'></script>
<script type='text/javascript' src='window_ctl.js?id=<%=$uniq%>'></script>
</head>
<body>
<script type='text/javascript'>
<!--
///// ���󥹥��󥹤�����
var winCtl = new window_ctl();
// ��ʬ�Υ����������ѹ�
winCtl.chgLocation("<%=H_WEB_HOST%>/authenticate.php?background=on");
// -->
</script>
</body>
</html>
