<?php
//////////////////////////////////////////////////////////////////////////////
// �Ұ���������� include file ���Ȱ� ���� �������                         //
// Copyright (C) 2001-2005 2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp //
// Changed history                                                          //
// 2001/07/07 Created  view_userinfo_address.php                            //
// 2002/08/07 ���å����������ɲ� & register_globals = Off �б�            //
//             $key �� ���Υ�����ץȤθƽи��ˤ��뎡view_userinfo.php       //
// 2003/04/08 �и��������Ƥ��ɲ� (��°�θ������)                           //
// 2005/01/17 access_log ���ѹ��� view_file_name(__FILE__) ���ɲ�           //
// 2007/09/11 $res[$r][retire_date] �� $res[$r]['retire_date'] �ؽ���       //
//////////////////////////////////////////////////////////////////////////////
//    access_log("view_userinfo_address.php");        // Script Name ��ư����
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
    define("VIEW_LIMIT","15");
    if (isset($_POST['lookup_next'])){
        if ($_POST['resrows']>=$offset+VIEW_LIMIT)
            $offset+=VIEW_LIMIT;
    } elseif (isset($_POST['lookup_prev'])){
        if (0<=$offset-VIWE_LIMIT)
            $offset-=VIEW_LIMIT;
    } else
        $offset=0;
$_POST["offset"] = $offset;
    /* �����꡼������ */
    if ($_POST['lookupkeykind'] != KIND_DISABLE){
        if ($_POST['lookupkeykind'] == KIND_USERID){
            $query="select ud.uid,ud.name,ud.kana,ud.retire_date,ud.zipcode,ud.address,sm.section_name,pm.position_name from user_detailes ud,section_master sm,position_master pm" .
                " where ud.uid='$key' and ud.retire_date is null and ud.sid=sm.sid and ud.pid=pm.pid";
        } elseif ($_POST['lookupkeykind'] == KIND_FULLNAME){
            $query="select ud.uid,ud.name,ud.kana,ud.retire_date,ud.zipcode,ud.address,sm.section_name,pm.position_name from user_detailes ud,section_master sm,position_master pm" .
                " where (ud.name=$key or ud.kana=$key or ud.spell=$key) and ud.retire_date is null and ud.sid=sm.sid and ud.pid=pm.pid";
        } else {
            $query="select ud.uid,ud.name,ud.kana,ud.retire_date,ud.zipcode,ud.address,sm.section_name,pm.position_name from user_detailes ud,section_master sm,position_master pm" .
                " where (ud.name like $key or ud.kana like $key or ud.spell like $key) and ud.retire_date is null and ud.sid=sm.sid and ud.pid=pm.pid";
        }
    } else {
        $query="select ud.uid,ud.name,ud.kana,ud.retire_date,ud.zipcode,ud.address,sm.section_name,pm.position_name from user_detailes ud,section_master sm,position_master pm" .
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
    echo("<tr><td colspan=2>�������  ������� <b><font size=+1 color='#ff7e00'>$rows</font></b> ��</td></tr>");
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

    if ($res[$r]['retire_date']==""){
        $color="black";
    } else {
        $color="silver";
    }
?>
    <tr><td valign="top">
        <table width="100%">
            <hr><font color='#ff7e00'><b><?php echo($r+1); ?></b></font>
            <tr><td width="10%"><font color="<?php echo($color); ?>">�Ұ�No</font></td>
                <td colspan=3><font color="<?php echo($color); ?>"><?php echo($res[$r]['uid']); ?></font></td>
            </tr>
            <tr>
                <td width="10%"><font color="<?php echo($color); ?>">̾��</font></td>
                <td width="30%"><font size=1 color="<?php echo($color); ?>"><?php echo($res[$r]['kana']); ?></font><br><font color="<?php echo($color); ?>"><?php echo($res[$r]['name']); ?></font></td>
                <td width="10%"><font color="<?php echo($color); ?>">��°</font></td>
                <td><font color="<?php echo($color); ?>"><?php echo($res[$r]['section_name']); ?></font></td>
            </tr>
            <tr>
                <td width="10%"><font color="<?php echo($color); ?>">����</font></td>
                <td colspan=3><font color="<?php echo($color); ?>"><?php echo("��" . substr($res[$r]['zipcode'],0,3) . "-" . substr($res[$r]['zipcode'],3,4) . "  " . $res[$r]['address']) ?></font></td>
            </tr>
        </table>
        </td>
    </tr>
<?php
        }
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
    }
?>
</table>
