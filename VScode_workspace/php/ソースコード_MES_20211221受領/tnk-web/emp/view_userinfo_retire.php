<?php
//////////////////////////////////////////////////////////////////////////////
// �Ұ���������� include file �࿦�԰���                                   //
// Copyright (C) 2001-2019 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2001/07/07 Created  view_userinfo_retire.php                             //
// 2002/08/07 register_globals = Off �б� & ���å�������                  //
// 2003/04/02 ɽ�� ��� �ѹ� order by ud.retire_date DESC ���ɲ�            //
// 2005/01/17 access_log ���ѹ��� view_file_name(__FILE__) ���ɲ�           //
// 2007/08/29 ������ѹ��˥ڡ��������offset���ɲ�                          //
// 2019/11/27 limit��500��(����Ĺ�ؼ� �ʤ��ˤ���������ս꤬������Τ�)��ë //
//////////////////////////////////////////////////////////////////////////////
//    access_log("view_userinfo_retire.php");     // Script Name ��ư����
access_log(substr(__FILE__, strlen($_SERVER['DOCUMENT_ROOT'])));
echo view_file_name(__FILE__);
    if (isset($_POST['offset'])) {
        $offset = $_POST['offset'];
    } else {
        $offset = 0;
    }
?>
<table width="100%">
    <tr><td colspan=2 bgcolor="#ff6600" align="center">
        <font color="#ffffff">�࿦�Ծ���</font>
        </td>
    </tr>
<?php
/* �����Ȥؤ�ɽ����� */
    //define("VIEW_LIMIT", "10");
    define("VIEW_LIMIT", "500");
    if (isset($_POST['lookup_next'])) {
        if ($_POST['resrows'] >= $offset+VIEW_LIMIT)
            $offset += VIEW_LIMIT;
    } elseif (isset($_POST['lookup_prev'])) {
        if (0 <= $offset-VIWE_LIMIT)
            $offset -= VIEW_LIMIT;
    }
$_POST['offset'] = $offset;
    /* �����꡼������ */
        $query="select ud.uid,ud.name,ud.kana,ud.retire_date,ud.retire_info,ud.photo,sm.section_name,pm.position_name from user_detailes ud,section_master sm,position_master pm" .
            " where ud.sid=sm.sid and ud.retire_date is not null and ud.pid=pm.pid";

    $query .=" order by ud.retire_date DESC, sm.section_name ASC, ud.uid ASC";
    $res=array();
    $rows=getResult($query,$res);
    echo("<tr><td colspan=2>�࿦�� <font size=+1 color='#ff7e00'><b>$rows</b></font> ̾</td></tr>");
        echo("<tr>\n");
        echo("<td><form method='post' action='emp_menu.php?func=" . FUNC_RETIREINFO . "'><table width='100%'>\n");
        echo("<td width='100%' colspan=2><input type='hidden' name='lookupkind' value=" . $_POST['lookupkind'] . ">\n");
        echo("<input type='hidden' name='lookupkey' value='" . $_POST['lookupkey'] . "'>\n");
        echo("<input type='hidden' name='lookupkeykind' value=" . $_POST['lookupkeykind'] . ">\n");
        echo("<input type='hidden' name='lookupsection' value=" . $_POST['lookupsection'] . ">\n");
        echo("<input type='hidden' name='lookupposition' value=" . $_POST['lookupposition'] . ">\n");
        echo("<input type='hidden' name='lookupentry' value=" . $_POST['lookupentry'] . ">\n");
        echo("<input type='hidden' name='lookupcapacity' value=" . $_POST['lookupcapacity'] . ">\n");
        echo("<input type='hidden' name='lookupreceive' value=" . $_POST['lookupreceive'] . ">\n");
        echo("<input type='hidden' name='resrows' value=$rows>\n");
        echo("<input type='hidden' name='retireflg' value=1>\n");
        echo("<input type='hidden' name='offset' value=$offset></td><tr>\n");
        if(0<=$offset-VIEW_LIMIT)
            echo("<td align='left'><input disable type='submit' name='lookup_prev' value='����'></td>\n");
        if($rows>$offset+VIEW_LIMIT){
            if(0==$offset)
                echo("<td colspan=2 align='right'><input type='submit' name='lookup_next' value='����'></td>\n");
            else
                echo("<td align='right'><input type='submit' name='lookup_next' value='����'></td>\n");
        }
        echo("</tr></table></form>\n");
        echo("</tr>\n");

    if($rows){
        for($r=$offset;$r<$rows&&$r<$offset+VIEW_LIMIT;$r++){

    if($res[$r]['retire_date']==""){
        $color="black";
    }else{
        $color="silver";
    }
?>
    <tr><td valign="top">
        <form method="post" action="emp_menu.php?func=<?php echo(FUNC_CHGUSERINFO); ?>">
        <table width="100%">
            <hr><font color='#ff7e00'><b><?php echo($r+1); ?></b></font>
            <tr><td width="15%">�Ұ�No.</td>
                <td><?php echo($res[$r]['uid']); ?></td>
<?php
            if($res[$r]['photo']){
                $file=IND . $res[$r]['uid'] . ".gif" ;
                getObject($res[$r]['photo'],$file);
                echo("<td rowspan=4 width=76 align='right'><img src='$file?" . uniqid(abcdef) . "' width=76 height=112 border=0></td>");
            }
?>
            </tr>
            <tr><td width="15%">̾��</td>
                <td><font size=1><?php echo($res[$r]['kana']); ?></font><br><?php echo($res[$r]['name']); ?></td>
            </tr>
            <tr><td width="15%">�࿦��</td>
                <td><?php echo($res[$r]['retire_date']); ?></td>
            </tr>
            <tr><td width="15%">�࿦��ͳ</td>
                <td><?php echo($res[$r]['retire_info']); ?></td>
            </tr>
<?php
            if($_SESSION['Auth'] >= AUTH_LEBEL2){
?>
            <tr><td colspan=3 align="right">
                <input type="hidden" name="userid" value="<?php echo($res[$r]['uid']); ?>">
                <input type="hidden" name="name" value="<?php echo(trim($res[$r]['name'])); ?>">
                <input type="hidden" name="section_name" value="<?php echo(trim($res[$r]['section_name'])); ?>">
                <input type="hidden" name="lookupkind" value=<?php echo($_POST['lookupkind']); ?>>
                <input type="hidden" name="lookupkey" value="<?php echo($_POST['lookupkey']); ?>">
                <input type="hidden" name="lookupkeykind" value=<?php echo($_POST['lookupkeykind']); ?>>
                <input type="hidden" name="lookupsection" value=<?php echo($_POST['lookupsection']); ?>>
                <input type="hidden" name="lookupposition" value=<?php echo($_POST['lookupposition']); ?>>
                <input type="hidden" name="lookupentry" value=<?php echo($_POST['lookupentry']); ?>>
                <input type="hidden" name="lookupcapacity" value=<?php echo($_POST['lookupcapacity']) ?>>
                <input type="hidden" name="lookupreceive" value=<?php echo($_POST['lookupreceive']) ?>>
                <input type="hidden" name="histnum" value=-1>
                <input type="hidden" name="retireflg" value=1>
                <input type='hidden' name='offset' value='<?php echo $offset?>'>
                <input type="submit" name="inf" value="������ѹ�"></td>
                <!-- <input type="submit" name="pwd" value="�ѥ���ɤ��ѹ�"> --></td>
            </tr>
<?php
            }
?>
        </table>
        </form>
        </td>
    </tr>
<?php
        }
        echo("<tr>\n");
        echo("<td><form method='post' action='emp_menu.php?func=" . FUNC_RETIREINFO . "'><table width='100%'>\n");
        echo("<td colspan=2><input type='hidden' name='lookupkind' value=" . $_POST['lookupkind'] . ">\n");
        echo("<input type='hidden' name='lookupkey' value='" . $_POST['lookupkey'] . "'>\n");
        echo("<input type='hidden' name='lookupkeykind' value=" . $_POST['lookupkeykind'] . ">\n");
        echo("<input type='hidden' name='lookupsection' value=" . $_POST['lookupsection'] . ">\n");
        echo("<input type='hidden' name='lookupposition' value=" . $_POST['lookupposition'] . ">\n");
        echo("<input type='hidden' name='lookupentry' value=" . $_POST['lookupentry'] . ">\n");
        echo("<input type='hidden' name='lookupcapacity' value=" . $_POST['lookupcapacity'] . ">\n");
        echo("<input type='hidden' name='lookupreceive' value=" . $_POST['lookupreceive'] . ">\n");
        echo("<input type='hidden' name='resrows' value=$rows>\n");
        echo("<input type='hidden' name='retireflg' value=1>\n");
        echo("<input type='hidden' name='offset' value=$offset></td><tr>\n");
        if(0<=$offset-VIEW_LIMIT)
            echo("<td align='left'><input disable type='submit' name='lookup_prev' value='����'></td>\n");
        if($rows>$offset+VIEW_LIMIT){
            if(0==$offset)
                echo("<td colspan=2 align='right'><input type='submit' name='lookup_next' value='����'></td>\n");
            else
                echo("<td align='right'><input type='submit' name='lookup_next' value='����'></td>\n");
        }
        echo("</tr></table></form>\n");
        echo("</tr>\n");
    }
?>
</table>
