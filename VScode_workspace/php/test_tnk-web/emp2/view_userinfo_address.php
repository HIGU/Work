<?php
//////////////////////////////////////////////////////////////////////////////
// 社員情報管理の include file 従業員 住所 検索結果                         //
// Copyright (C) 2001-2005 2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp //
// Changed history                                                          //
// 2001/07/07 Created  view_userinfo_address.php                            //
// 2002/08/07 セッション管理を追加 & register_globals = Off 対応            //
//             $key は このスクリプトの呼出元にある｡view_userinfo.php       //
// 2003/04/08 出向除く全てを追加 (所属の検索条件)                           //
// 2005/01/17 access_log の変更と view_file_name(__FILE__) の追加           //
// 2007/09/11 $res[$r][retire_date] → $res[$r]['retire_date'] へ修正       //
//////////////////////////////////////////////////////////////////////////////
//    access_log("view_userinfo_address.php");        // Script Name 手動設定
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
        <font color="#ffffff">ユーザーの検索結果</font></td>
    </tr>
<?php
    /* サイトへの表示件数 */
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
    /* クエリーを生成 */
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
    /* 所属による条件 */
    if ($_SESSION['lookupsection'] == (-2)) {
        $query .= " and ud.sid<>31";        // 出向社員を除く全て
    } elseif ($_SESSION["lookupsection"]!=KIND_DISABLE) {
        $query .= " and ud.sid=" . $_SESSION["lookupsection"];
    }
//  if ($_POST['lookupsection'] != KIND_DISABLE)
//      $query .=" and ud.sid=" . $_POST['lookupsection'];
    /* 職位による条件 */
    if ($_POST['lookupposition'] != KIND_DISABLE)
        $query .=" and ud.pid=" . $_POST['lookupposition'];
    /* 入社年度での条件 */
    if ($_POST['lookupentry'] != KIND_DISABLE)
        $query .=" and to_char(ud.enterdate,'YYYY')='" . $_POST['lookupentry'] . "'";
    /* 資格による条件 */
    if ($_POST['lookupcapacity'] != KIND_DISABLE)
        $query .=" and exists (select * from user_capacity uc where ud.uid=uc.uid and uc.cid=" . $_POST['lookupcapacity'] . ")";
    /* 教育による条件 */
    if ($_POST['lookupreceive'] != KIND_DISABLE)
        $query .=" and exists (select * from user_receive ur where ud.uid=ur.uid and ur.rid=" . $_POST['lookupreceive'] . ")";
    $query .=" order by sm.section_name,ud.uid";
    $res=array();
    $rows=getResult($query,$res);
    echo("<tr><td colspan=2>住所情報  検索件数 <b><font size=+1 color='#ff7e00'>$rows</font></b> 件</td></tr>");
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
            echo("<td align='left'><input disable type='submit' name='lookup_prev' value='前へ'></td>\n");
        if ($rows>$offset+VIEW_LIMIT){
            if (0==$offset)
                echo("<td colspan=2 align='right'><input type='submit' name='lookup_next' value='次へ'></td>\n");
            else
                echo("<td align='right'><input type='submit' name='lookup_next' value='次へ'></td>\n");
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
            <tr><td width="10%"><font color="<?php echo($color); ?>">社員No</font></td>
                <td colspan=3><font color="<?php echo($color); ?>"><?php echo($res[$r]['uid']); ?></font></td>
            </tr>
            <tr>
                <td width="10%"><font color="<?php echo($color); ?>">名前</font></td>
                <td width="30%"><font size=1 color="<?php echo($color); ?>"><?php echo($res[$r]['kana']); ?></font><br><font color="<?php echo($color); ?>"><?php echo($res[$r]['name']); ?></font></td>
                <td width="10%"><font color="<?php echo($color); ?>">所属</font></td>
                <td><font color="<?php echo($color); ?>"><?php echo($res[$r]['section_name']); ?></font></td>
            </tr>
            <tr>
                <td width="10%"><font color="<?php echo($color); ?>">住所</font></td>
                <td colspan=3><font color="<?php echo($color); ?>"><?php echo("〒" . substr($res[$r]['zipcode'],0,3) . "-" . substr($res[$r]['zipcode'],3,4) . "  " . $res[$r]['address']) ?></font></td>
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
            echo("<td align='left'><input disable type='submit' name='lookup_prev' value='前へ'></td>\n");
        if ($rows>$offset+VIEW_LIMIT){
            if (0==$offset)
                echo("<td colspan=2 align='right'><input type='submit' name='lookup_next' value='次へ'></td>\n");
            else
                echo("<td align='right'><input type='submit' name='lookup_next' value='次へ'></td>\n");
        }
        echo("</tr></table></form>\n");
        echo("</tr>\n");
    }
?>
</table>
