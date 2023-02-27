<?php
//////////////////////////////////////////////////////////////////////////////
// 社員情報管理の include file 退職者一覧                                   //
// Copyright (C) 2001-2019 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2001/07/07 Created  view_userinfo_retire.php                             //
// 2002/08/07 register_globals = Off 対応 & セッション管理                  //
// 2003/04/02 表示 順序 変更 order by ud.retire_date DESC を追加            //
// 2005/01/17 access_log の変更と view_file_name(__FILE__) の追加           //
// 2007/08/29 情報の変更にページ制御のoffsetを追加                          //
// 2019/11/27 limitを500に(工場長指示 なしにすると訂正箇所が増えるので)大谷 //
//////////////////////////////////////////////////////////////////////////////
//    access_log("view_userinfo_retire.php");     // Script Name 手動設定
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
        <font color="#ffffff">退職者情報</font>
        </td>
    </tr>
<?php
/* サイトへの表示件数 */
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
    /* クエリーを生成 */
        $query="select ud.uid,ud.name,ud.kana,ud.retire_date,ud.retire_info,ud.photo,sm.section_name,pm.position_name from user_detailes ud,section_master sm,position_master pm" .
            " where ud.sid=sm.sid and ud.retire_date is not null and ud.pid=pm.pid";

    $query .=" order by ud.retire_date DESC, sm.section_name ASC, ud.uid ASC";
    $res=array();
    $rows=getResult($query,$res);
    echo("<tr><td colspan=2>退職者 <font size=+1 color='#ff7e00'><b>$rows</b></font> 名</td></tr>");
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
            echo("<td align='left'><input disable type='submit' name='lookup_prev' value='前へ'></td>\n");
        if($rows>$offset+VIEW_LIMIT){
            if(0==$offset)
                echo("<td colspan=2 align='right'><input type='submit' name='lookup_next' value='次へ'></td>\n");
            else
                echo("<td align='right'><input type='submit' name='lookup_next' value='次へ'></td>\n");
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
            <tr><td width="15%">社員No.</td>
                <td><?php echo($res[$r]['uid']); ?></td>
<?php
            if($res[$r]['photo']){
                $file=IND . $res[$r]['uid'] . ".gif" ;
                getObject($res[$r]['photo'],$file);
                echo("<td rowspan=4 width=76 align='right'><img src='$file?" . uniqid(abcdef) . "' width=76 height=112 border=0></td>");
            }
?>
            </tr>
            <tr><td width="15%">名前</td>
                <td><font size=1><?php echo($res[$r]['kana']); ?></font><br><?php echo($res[$r]['name']); ?></td>
            </tr>
            <tr><td width="15%">退職日</td>
                <td><?php echo($res[$r]['retire_date']); ?></td>
            </tr>
            <tr><td width="15%">退職理由</td>
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
                <input type="submit" name="inf" value="情報の変更"></td>
                <!-- <input type="submit" name="pwd" value="パスワードの変更"> --></td>
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
            echo("<td align='left'><input disable type='submit' name='lookup_prev' value='前へ'></td>\n");
        if($rows>$offset+VIEW_LIMIT){
            if(0==$offset)
                echo("<td colspan=2 align='right'><input type='submit' name='lookup_next' value='次へ'></td>\n");
            else
                echo("<td align='right'><input type='submit' name='lookup_next' value='次へ'></td>\n");
        }
        echo("</tr></table></form>\n");
        echo("</tr>\n");
    }
?>
</table>
