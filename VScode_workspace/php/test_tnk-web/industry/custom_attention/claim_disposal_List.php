<?php
//////////////////////////////////////////////////////////////////////////////
// 不適合処置連絡書照会 一覧表示 View部                                     //
// Copyright (C) 2013-2013 Norihisa.Ooya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2013/01/24 Created  claim_disposal_List.php                              //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<title><?php echo $menu->out_title() ?></title>
<?php echo $menu->out_site_java() ?>
<?php echo $menu->out_css() ?>
<?php echo $menu->out_jsBaseClass() ?>

<script language="JavaScript">
<!--
/* 入力文字が数字かどうかチェック(ASCII code check) */
function isDigit(str) {
    var len = str.length;
    var c;
    for (i=0; i<len; i++) {
        c = str.charAt(i);
        if ((c < '0') || (c > '9')) {
            return false;
        }
    }
    return true;
}

/* 入力文字のチェック関数 */
function chk_entry(obj) {
    if (obj.group_no.value.length == 0) {
        alert('グループＮｏ.が入力されていません！');
        obj.group_no.focus();
        obj.group_no.select();
        return false;
    } else if ( !(isDigit(obj.group_no.value)) ) {
        alert('グループNo.は数字以外入力出来ません！');
        obj.group_no.focus();
        obj.group_no.select();
        return false;
    }
    
    if (obj.group_name.value.length == 0) {
        alert('グループ名が入力されていません！');
        obj.group_name.focus();
        obj.group_name.select();
        return false;
    }
    return true;
}
// -->
</script>

<style type="text/css">
<!--
th {
    background-color:   blue;
    color:              yellow;
    font-size:          10pt;
    font-weight:        bold;
    font-family:        monospace;
}
-->
</style>
</head>
<body scroll=no>
    <center>
    <?php echo $menu->out_title_border() ?>
    </center>
    <br>
    <?php
    if ($request->get('view_flg') == '照会') {
        echo "<iframe hspace='0' vspace='0' frameborder='0' scrolling='yes' src='list/claim_disposal_List-{$_SESSION['User_ID']}.html' name='list' align='center' width='100%' height='90%' title='リスト'>\n";
        echo "    一覧を表示しています。\n";
        echo "</iframe>\n";
    }
    ?>
</body>
<?php echo $menu->out_alert_java() ?>
</html>
