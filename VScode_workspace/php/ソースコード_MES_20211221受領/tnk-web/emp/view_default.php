<?php
//////////////////////////////////////////////////////////////////////////////
// �Ұ���������� include file ������̡ʥǥե���ȡ�                       //
// Copyright (C) 2001-2005 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2001/07/07 Created  view_default.php                                     //
// 2002/08/07 register_globals = Off �б�                                   //
// 2003/02/14 ���ط��˥塼 �Υե���Ȥ� style �ǻ�����ѹ�                //
//                              �֥饦�����ˤ���ѹ�������ʤ��ͤˤ���      //
// 2004/06/10 view_user($_SESSION['User_ID']) ���˥塼�إå����β����ɲ�  //
// 2004/12/23 �롼�Ȥ���ο��¤Υ�����ץ�̾����ɥ�����ȥ롼��ʬ����  //
// 2005/01/17 view_user($_SESSION['User_ID'])��view_file_name(__FILE__)��   //
//            emp_menu.php��MenuHeader class �ذܹԤ���������ѹ�           //
//////////////////////////////////////////////////////////////////////////////
// access_log("emp_menu_view_default.php");        // Script Name ��ư����
// Script Name ��ư���� �롼�Ȥ���ο��¤Υ�����ץ�̾����ɥ�����ȥ롼��ʬ����
access_log(substr(__FILE__, strlen($_SERVER['DOCUMENT_ROOT'])));
echo view_file_name(__FILE__);
?>
<style type="text/css">
<!--
.top-font {
    font-size: 12pt;
    font-family: monospace;
    font-weight: bold;
    }
.p-font {
    font-size: 12pt;
    font-family: monospace;
    }
ol {
    font-size: 11pt;
    font-family: monospace;
    }
-->
</style>
<script language="Javascript">
<!--
str = navigator.appName.toUpperCase();
if(str.indexOf("NETSCAPE")>=0)
    document.write("<table width='100%' height=755 bgcolor='#ffffff' cellpadding=10>");
if(str.indexOf("EXPLORER")>=0)
    document.write("<table width='100%' height='100%' bgcolor='#ffffff' cellpadding=10>");
//-->
</script>
<noscript>
    <table width="100%" height="100%" bgcolor="#ffffff" cellpadding=10>
</noscript>
    <tr><td valign="top">
    <table>
        <tr>
            <td>
                <p><img src="../img/t_nitto_logo2.gif" width=348 height=83 border=0></p>
            </td>
        </tr>
        <tr>
            <td class='top-font'>
                �Ұ��������
            </td>
        </tr>
    </table>
    <p class='p-font'>��ǽ�ˤĤ���</p>
    <ol>
    <li>���ʾ���ɽ��
        <br>��ʬ�Υ桼���������ɽ�����ޤ����ѥ���ɤ��ѹ����ǽ�Ǥ���
<?php   if($_SESSION["Auth"] >= AUTH_LEBEL2){   ?>
    <li>���Ȱ�������Ͽ
        <br>���ҡ�ž�ҡ��и����줿���Ȱ�����Ͽ���ޤ���
<?php   }
    if($_SESSION["Auth"] >= AUTH_LEBEL3){   ?>
    <li>�ǡ����١������
        <br>�ǡ����١�����Ǥ�դ��䤤��碌�������������Ԥ��ޤ���
<?php   } ?>
    <li>����
        <ul>
        <li>���Ȱ�����
            <br>�������뤹�٤Ƥν��Ȱ��ξ����ɽ�����ޤ���
    <?php   if($_SESSION["Auth"] >= AUTH_LEBEL1){   ?>
        <li>�������
            <br>���Ȱ������꽻����б���������ɽ�����ޤ���
        <li>���鷱����Ͽ
            <br>���Ȱ����Ф��ƹԤ�줿���鵭Ͽ�������ν��Ȱ����������Ƥ����ʤʤɤ�
            �����ɽ�����ޤ���
    <?php   } ?>
        </ul>
        �Ұ�No������̾���򸡺������Ȥ��ơ�ɬ�פʾ�������򤷤Ƥ������������������˥ե�͡����
        ���ꤹ���硢����̾�δ֤ˤϥ��ڡ���������Ƥ���������
    </ol>
    </td></tr>
    <!--
        <tr><td valign="bottom"><br>
            <img src="../img/php4.gif" width=64 height=32>
            <img src="../img/linux.gif" width=74 height=32>  
            <img src="../img/redhat.gif" width=96 height=32>   
            <img src="../img/apache.gif" width=259 height=32> 
            <img src="../img/pgsql.gif" width=160 height=32>
        </td></tr>
    -->
</table>
