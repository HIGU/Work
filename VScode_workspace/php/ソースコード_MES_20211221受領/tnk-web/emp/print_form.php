<?php
//////////////////////////////////////////////////////////////////////////////
// �Ұ���������� include file ���Ȱ� ���鷱����Ͽ �����ե�����             //
// Copyright (C) 2001-2005 2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp //
// Changed history                                                          //
// 2001/07/07 Created chg_passwd.php                                        //
// 2002/08/07 ���å����������ɲ� & register_globals = Off �б�            //
// 2005/01/17 access_log ���ѹ��� view_file_name(__FILE__) ���ɲ�           //
// 2007/03/07 ��ư���ư������                                              //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��
require_once ('../function.php');           // ���� �ؿ�
access_log();                               // Script Name ��ư����
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html;charset=euc-jp">
</head>
<body>
    <table width="80%">
        <tr>
            <td width="25%"><u><?php echo("�Ұ�No. " . $_POST['uid']); ?></u></td>
            <td width="25%"><u><?php echo("��̾ : " . $_POST['name']); ?></u></td>
            <td width="30%"><u><?php echo("��° : " . $_POST['section_name']); ?></u></td>
        </tr>
    </table>
<br>
<tr><td valign="top">
    <table width="100%">
        <tr>
            <td width="50%" height="200" valign="top">
                <table width="100%">
                    <tr><td colspan=3>��ư����</td></tr>
<?php
        $query="select trans_date,section_name from user_transfer" . 
            " where uid='" . $_POST['uid'] . "' order by trans_date";
        $res=array();
        $rows=getResult($query,$res);
        for($r=0;$r<$rows;$r++){
            $trans_date = $res[$r]['trans_date'];
            $_POST['section_name'] = $res[$r]['section_name'];
            echo("<tr><td><br></td>\n");
            echo("<td width='50%'>" . $trans_date . "</td>\n");
            echo("<td width='50%'>" . $_POST['section_name'] . "</td></tr>\n");
        }
?>
    </table>
    </td>
    <td rowspan=2 width="50%" valign="top">
    <table width="100%">
                        <tr><td colspan=3>��������</td></tr>
<?php
        $query="select ur.begin_date,ur.end_date,rm.receive_name from user_receive ur,receive_master rm" . 
            " where ur.uid='" . $_POST['uid'] . "' and ur.rid=rm.rid order by ur.begin_date";
        $res=array();
        $rows=getResult($query,$res);
        for($r=0;$r<$rows;$r++){
            $begin_date = $res[$r]['begin_date'];
            $end_date = $res[$r]['end_date'];
            $receive_name = $res[$r]['receive_name'];
            echo("<tr><td><br></td>\n");
            echo("<td width='50%'>" . $begin_date . "��" . $end_date . "</td>\n");
            echo("<td width='50%'>" . $receive_name . "</td></tr>\n");
        }
?>
    </table>
    </td>
    </tr>
<tr>
    <td width="40%" height="200" valign="top">
        <table width="100%">
            <tr><td colspan=3>�������</td></tr>
<?php
        $query="select uc.acq_date,cm.capacity_name from user_capacity uc,capacity_master cm" . 
            " where uc.uid='" . $_POST['uid'] . "' and uc.cid=cm.cid order by uc.acq_date";
        $res=array();
        $rows=getResult($query,$res);
        for($r=0;$r<$rows;$r++){
            $acq_date = $res[$r]['acq_date'];
            $capacity_name = $res[$r]['capacity_name'];
            echo("<tr><td><br></td>\n");
            echo("<td width='50%'>" . $acq_date . "</td>\n");
            echo("<td width='50%'>" . $capacity_name . "</td></tr>\n");
        }
?>
        </table>
    </td>
        </tr>
    </td>
</tr>
</body>
</html>
