<?php
//////////////////////////////////////////////////////////////////////////////
// �Ұ���������� include file ���Ȱ� ���鷱����Ͽ �������                 //
// Copyright (C) 2001-2005 2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp //
// Changed history                                                          //
// 2001/07/07 Created  view_userinfo_training.php                           //
// 2002/08/07 ���å����������ɲ� & register_globals = Off �б�            //
//             $key �� ���Υ�����ץȤθƽи��ˤ��뎡view_userinfo.php       //
// 2003/04/08  �и��������Ƥ��ɲ� (��°�θ������)                          //
// 2005/01/17 access_log ���ѹ��� view_file_name(__FILE__) ���ɲ�           //
// 2007/03/07 ��ư���ư������ ��ë                                         //
// 2007/09/11 VIWE_LIMIT �� VIEW_LIMIT 34���� �ؽ���                        //
//////////////////////////////////////////////////////////////////////////////
//    access_log("view_userinfo_training.php");       // Script Name ��ư����
access_log(substr(__FILE__, strlen($_SERVER['DOCUMENT_ROOT'])));
echo view_file_name(__FILE__);
    if (isset($_POST['offset'])) {
        $offset = $_POST['offset'];
    } else {
        $offset = 0;
    }
?>
<table width="100%">
    <tr><td colspan=2 bgcolor="#003e7c" align="center">
        <font color="#ffffff">�桼�����θ������</font></td>
    </tr>
<?php
    /* �����Ȥؤ�ɽ����� */
    define("VIEW_LIMIT","1");
    if (isset($_POST['lookup_next'])){
        if ($_POST['resrows'] >= ($offset + VIEW_LIMIT))
            $offset+=VIEW_LIMIT;
    }elseif (isset($_POST['lookup_prev'])){
        if (0<=$offset-VIEW_LIMIT)
            $offset-=VIEW_LIMIT;
    } else
        $offset=0;
$_POST["offset"] = $offset;
    /* �����꡼������ */
    if ($_POST['lookupkeykind'] != KIND_DISABLE){
        if ($_POST['lookupkeykind'] == KIND_USERID){
            $query="select ud.uid,ud.name,ud.retire_date,sm.section_name,pm.position_name from user_detailes ud,section_master sm,position_master pm" .
                " where ud.uid='$key' and ud.retire_date is null and ud.sid=sm.sid and ud.pid=pm.pid";
        } elseif ($_POST['lookupkeykind'] == KIND_FULLNAME){
            $query="select ud.uid,ud.name,ud.retire_date,sm.section_name,pm.position_name from user_detailes ud,section_master sm,position_master pm" .
                " where (ud.name=$key or ud.kana=$key or ud.spell=$key) and ud.retire_date is null and ud.sid=sm.sid and ud.pid=pm.pid";
        } else {
            $query="select ud.uid,ud.name,ud.retire_date,sm.section_name,pm.position_name from user_detailes ud,section_master sm,position_master pm" .
                " where (ud.name like $key or ud.kana like $key or ud.spell like $key) and ud.retire_date is null and ud.sid=sm.sid and ud.pid=pm.pid";
        }
    } else {
        $query="select ud.uid,ud.name,ud.retire_date,sm.section_name,pm.position_name from user_detailes ud,section_master sm,position_master pm" .
            " where ud.retire_date is null and ud.sid=sm.sid and ud.pid=pm.pid";
    }
    /* ��°�ˤ���� */
    if ($_SESSION['lookupsection'] == (-2)) {
        $query .= " and ud.sid<>31";        // �и��Ұ����������
    } elseif ($_SESSION["lookupsection"]!=KIND_DISABLE) {
        $query .= " and ud.sid=" . $_SESSION["lookupsection"];
    }
//  if ($_POST['lookupsection'] != KIND_DISABLE)
//      $query .=" and ud.sid=" . $_POST['lookupsection'];
    /* ���̤ˤ���� */
    if ($_POST['lookupposition'] != KIND_DISABLE)
        $query .=" and ud.pid=" . $_POST['lookupposition'];
    /* ����ǯ�٤Ǥξ�� */
    if ($_POST['lookupentry'] != KIND_DISABLE)
        $query .=" and to_char(ud.enterdate,'YYYY')='" . $_POST['lookupentry'] . "'";
    /* ��ʤˤ���� */
    if ($_POST['lookupcapacity'] != KIND_DISABLE)
        $query .=" and exists (select * from user_capacity uc where ud.uid=uc.uid and uc.cid=" . $_POST['lookupcapacity'] . ")";
    /* ����ˤ���� */
    if ($_POST['lookupreceive'] != KIND_DISABLE)
        $query .=" and exists (select * from user_receive ur where ud.uid=ur.uid and ur.rid=" . $_POST['lookupreceive'] . ")";
    $query .=" order by sm.section_name,ud.uid";
    $res=array();
    $rows=getResult($query,$res);
    echo("<tr><td colspan=2>���Ȱ�����  ������� <b><font size=+1 color='#ff7e00'>$rows</font></b> ��</td></tr>");
    echo("<tr>\n");
        echo("<td><form method='post' action='emp_menu.php?func=" . FUNC_LOOKUP . "'><table width='100%'>\n");
        echo("<td width='100%' colspan=2><input type='hidden' name='lookupkind' value=" . $_POST['lookupkind'] . ">\n");
        echo("<input type='hidden' name='lookupkey' value='" . $_POST['lookupkey'] . "'>\n");
        echo("<input type='hidden' name='lookupkeykind' value=" . $_POST['lookupkeykind'] . ">\n");
        echo("<input type='hidden' name='lookupsection' value=" . $_POST['lookupsection'] . ">\n");
        echo("<input type='hidden' name='lookupposition' value=" . $_POST['lookupposition'] . ">\n");
        echo("<input type='hidden' name='lookupentry' value=" . $_POST['lookupentry'] . ">\n");
        echo("<input type='hidden' name='lookupcapacity' value=" . $_POST['lookupcapacity'] . ">\n");
        echo("<input type='hidden' name='lookupreceive' value=" . $_POST['lookupreceive'] . ">\n");
        echo("<input type='hidden' name='resrows' value=$rows>\n");
        echo("<input type='hidden' name='offset' value=$offset></td><tr>\n");
        if (0<=$offset-VIEW_LIMIT)
            echo("<td align='left'><input disable type='submit' name='lookup_prev' value='����'></td>\n");
        if ($rows>$offset+VIEW_LIMIT){
            if (0==$offset)
                echo("<td colspan=2 align='right'><input type='submit' name='lookup_next' value='����'></td>\n");
            else
                echo("<td align='right'><input type='submit' name='lookup_next' value='����'></td>\n");
        }
        echo("</tr></table></form>\n");
        echo("</tr>\n");
    if ($rows){
        for($r=$offset;$r<$rows&&$r<$offset+VIEW_LIMIT;$r++){
            /* ��ư�������� */
            $usr_no=$res[$r]['uid'];
            $query="select trans_date,section_name from user_transfer" .
                " where uid='$usr_no' order by trans_date asc";
            $res_ut=array();
            $rows_ut=getResult($query,$res_ut);
            /* ��ʰ�������� */
            $query="select uc.acq_date,cm.capacity_name from user_capacity uc,capacity_master cm" .
                " where uc.uid='$usr_no' and uc.cid=cm.cid order by uc.acq_date asc";
            $res_uc=array();
            $rows_uc=getResult($query,$res_uc);
            /* ������������ */
            $query="select ur.begin_date,ur.end_date,rm.receive_name from user_receive ur,receive_master rm" .
                " where ur.uid='$usr_no' and ur.rid=rm.rid order by ur.begin_date asc";
            $res_ur=array();
            $rows_ur=getResult($query,$res_ur);
            if ($res[$r]['retire_date']==""){
                $color="black";
            } else {
                $color="silver";
            }
?>
<form method="post" action="print_form.php" target="_blank">
    <tr><td valign="top">
        <table width="80%">
            <tr>
<script language="Javascript">
<!--
    str = navigator.appName.toUpperCase();
    if (str.indexOf('EXPLORER') >= 0)
        document.write("<input type='submit' name='subwin' value='�̥�����ɥ��ǳ���'>");
//-->
</script>
<?php
            $uid=$res[$r]['uid'];
            $name=trim($res[$r]['name']);
            $section_name=trim($res[$r]['section_name']);
            echo("<input type='hidden' name='uid' value='$uid'>\n");
            echo("<input type='hidden' name='name' value='$name'>\n");
            echo("<input type='hidden' name='section_name' value='$section_name'>\n");
            echo("<input type='hidden' name='lookupkind' value=" . $_POST['lookupkind'] . ">\n");
            echo("<input type='hidden' name='lookupkey' value='" . $_POST['lookupkey'] . "'>\n");
            echo("<input type='hidden' name='lookupkeykind' value=" . $_POST['lookupkeykind'] . ">\n");
            echo("<input type='hidden' name='lookupsection' value=" . $_POST['lookupsection'] . ">\n");
            echo("<input type='hidden' name='lookupposition' value=" . $_POST['lookupposition'] . ">\n");
            echo("<input type='hidden' name='lookupentry' value=" . $_POST['lookupentry'] . ">\n");
            echo("<input type='hidden' name='lookupcapacity' value=" . $_POST['lookupcapacity'] . ">\n");
            echo("<input type='hidden' name='lookupreceive' value=" . $_POST['lookupreceive'] . ">\n");
            echo("<input type='hidden' name='rows' value=$rows>\n");
            echo("<input type='hidden' name='offset' value=$offset>\n");    
?>
                <hr>
                <font color='#ff7e00'><b><?php echo($r+1); ?></b></font>
                <td width="25%"><font color="<?php echo($color); ?>"><u><?php echo("�Ұ�No. " . $res[$r]['uid']); ?></u></font></td>
                <td width="25%"><font color="<?php echo($color); ?>"><u><?php echo("��̾ : " . $res[$r]['name']); ?></u></font></td>
                <td width="30%"><font color="<?php echo($color); ?>"><u><?php echo("��° : " . $res[$r]['section_name']); ?></u></font></td>
            </tr>
        </table>
        </td>
    </tr>
</form>
    <tr><td valign="top">
        <table width="100%">
            <tr>
                <td width="50%" height="200" valign="top">
                    <table width="100%">
                        <tr><td colspan=3><font color="<?php echo($color); ?>">��ư����</font></td></tr>
<?php
            for($i=0;$i<$rows_ut;$i++){
?>
                        <tr><td><br></td>
                            <td><font color="<?php echo($color); ?>"><?php echo($res_ut[$i]['trans_date']); ?></font></td>
                                <td><font color="<?php echo($color); ?>"><?php echo($res_ut[$i]['section_name']); ?></font></td>
                        </tr>
<?php
            }
?>
                    </table>
                </td>
                <td rowspan=2 width="50%" valign="top">
                    <table width="100%">
                        <tr><td colspan=3><font color="<?php echo($color); ?>">��������</font></td></tr>
<?php
            for($i=0;$i<$rows_ur;$i++){
?>
                        <tr><td><br></td>
                            <td><font color="<?php echo($color); ?>"><?php echo($res_ur[$i]['begin_date'] . "��" . $res_ur[$i]['end_date']); ?></font></td>
                                <td><font color="<?php echo($color); ?>"><?php echo($res_ur[$i]['receive_name']); ?></font></td>
                        </tr>
<?php
            }
?>
                    </table>
                </td>
            </tr>
            <tr>
                <td width="40%" height="200" valign="top">
                    <table width="100%">
                        <tr><td colspan=3><font color="<?php echo($color); ?>">�������</font></td></tr>
<?php
            for($i=0;$i<$rows_uc;$i++){
?>
                        <tr><td><br></td>
                            <td><font color="<?php echo($color); ?>"><?php echo($res_uc[$i]['acq_date']); ?></font></td>
                                <td><font color="<?php echo($color); ?>"><?php echo($res_uc[$i]['capacity_name']); ?><font></td>
                        </tr>
<?php               
            }
?>
                    </table>
                </td>
            </tr>
        </td>
    </tr>
<?php
        }
        echo("<tr>\n");
        echo("<form method='post' action='emp_menu.php?func=" . FUNC_LOOKUP . "'><table width='100%'><tr>\n");
        echo("<input type='hidden' name='lookupkind' value=" . $_POST['lookupkind'] . ">\n");
        echo("<input type='hidden' name='lookupkey' value='" . $_POST['lookupkey'] . "'>\n");
        echo("<input type='hidden' name='lookupkeykind' value=" . $_POST['lookupkeykind'] . ">\n");
        echo("<input type='hidden' name='lookupsection' value=" . $_POST['lookupsection'] . ">\n");
        echo("<input type='hidden' name='lookupposition' value=" . $_POST['lookupposition'] . ">\n");
        echo("<input type='hidden' name='lookupentry' value=" . $_POST['lookupentry'] . ">\n");
        echo("<input type='hidden' name='lookupcapacity' value=" . $_POST['lookupcapacity'] . ">\n");
        echo("<input type='hidden' name='lookupreceive' value=" . $_POST['lookupreceive'] . ">\n");
        echo("<input type='hidden' name='resrows' value=$rows>\n");
        echo("<input type='hidden' name='offset' value=$offset>\n");

        if (0<=$offset-VIEW_LIMIT)
            echo("<td align='left'><input disable type='submit' name='lookup_prev' value='����'></td>\n");
        if ($rows>$offset+VIEW_LIMIT){
            if (0==$offset)
                echo("<td colspan=2 align='right'><input type='submit' name='lookup_next' value='����'></td>\n");
            else
                echo("<td align='right'><input type='submit' name='lookup_next' value='����'></td>\n");
        }
        echo("</tr></table></form>\n");
        echo("</tr>\n");
    }
?>
</table>
