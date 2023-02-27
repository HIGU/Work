<?php
//////////////////////////////////////////////////////////////////////////////
// 少額資産管理台帳追加・編集 View部 smallSum_assets_View.php               //
// Copyright (C) 2010 Norihisa.Ooya norihisa_ooya@nitto-kohki.co.jp         //
// Changed history                                                          //
// 2010/10/05 Created  smallSum_assets_View.php                             //
// 2010/10/15 画面がはみ出るので大きさを調整                                //
//////////////////////////////////////////////////////////////////////////////
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<title><?php echo $menu->out_title() ?></title>
<?php echo $menu->out_site_java() ?>
<?php echo $menu->out_css() ?>

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

/* 入力文字が数字かどうかチェック 小数点対応 */
function isDigitDot(str) {
    var len = str.length;
    var c;
    var cnt_dot = 0;
    for (i=0; i<len; i++) {
        c = str.charAt(i);
        if (c == '.') {
            if (cnt_dot == 0) {     // 1個目かチェック
                cnt_dot++;
            } else {
                return false;       // 2個目は false
            }
        } else {
            if (('0' > c) || (c > '9')) {
                return false;
            }
        }
    }
    return true;
}

/* 入力データのチェック */
function chk_entry(obj) {
    if (obj.act_name.value.length == 0) {
        alert('管理部門名を選択してください！');
        obj.act_name.focus();
        obj.act_name.select();
        return false;
    }
    if (obj.set_place.value.length == 0) {
        alert('設置場所を選択してください！');
        obj.set_place.focus();
        obj.set_place.select();
        return false;
    }
    if (obj.assets_name.value.length == 0) {
        alert('品目名が入力されていません！');
        obj.assets_name.focus();
        obj.assets_name.select();
        return false;
    }
    if (obj.assets_model.value.length == 0) {
        alert('メーカー・型式が入力されていません！');
        obj.assets_model.focus();
        obj.assets_model.select();
        return false;
    }
    
    if(!obj.buy_ym.value.length){
        alert("取得年月日が入力されていません。");
        obj.buy_ym.focus();
        obj.buy_ym.select();
        return false;
    } else if(!(isDigit(obj.buy_ym.value))){
        alert("取得年月日には数値以外の文字は入力出来ません｡");
        obj.buy_ym.focus();
        obj.buy_ym.select();
        return false;
    }
    
    if ( !(isDigitDot(obj.buy_price.value)) ) {
        alert('取得金額は数字以外入力出来ません！');
        obj.buy_price.focus();
        obj.buy_price.select();
        return false;
    } else {
        if (obj.buy_price.value < 0) {
            alert('取得金額は０より大きい数字を入力して下さい！');
            obj.buy_price.focus();
            obj.buy_price.select();
            return false;
        }
    }
    
    if (obj.buy_ym.value.length == 8) {
    } else {
        alert('取得年月日はYYYYMMDDの8桁で入力してください。');
        obj.buy_ym.focus();
        obj.buy_ym.select();
        return false;
    }
    
    if(obj.delete_ym.value.length == 0){
        return true;
    } else {
        if(!(isDigit(obj.delete_ym.value))){
            alert("除却年月には数値以外の文字は入力出来ません｡");
            obj.delete_ym.focus();
            obj.delete_ym.select();
            return false;
        }
        if (obj.delete_ym.value.length == 8) {
        } else {
            alert('除却年月はYYYYMMDDの８桁で入力してください。');
            obj.delete_ym.focus();
            obj.delete_ym.select();
            return false;
        }
        return true;
    }
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
    <form name='entry_form' method='post' action='smallSum_assets_Main.php' onSubmit='return chk_entry(this)'>
        <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
                <tr><td> <!----------- ダミー(デザイン用) ------------>
            <table class='winbox_field' width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
                <tr>
                    <th class='winbox' nowrap>管理部門名</th>
                    <th class='winbox' nowrap>設置場所</th>
                    <th class='winbox' colspan='3' nowrap>品目名</th>
                    <th class='winbox' nowrap><font size =2>メーカー・型式</font></th>
                    <th class='winbox' nowrap>購入年月日</th>
                </tr>
                <tr>
                    <td class='winbox' align='center'>
                        <select name='act_name' class='pt11b' size='1'>
                            <?php echo getActOptionsBody($request) ?>
                        </select>
                    </td>
                    <td class='winbox' align='center'>
                        <select name='set_place' class='pt11b' size='1'>
                            <?php echo getPlaceOptionsBody($request) ?>
                        </select>
                    </td>
                    <td class='winbox' colspan='3' align='center'><input type='text' class='price_font' name='assets_name' value='<?php echo $request->get('assets_name') ?>' size='50'></td>
                    <td class='winbox' align='center'><input type='text' class='price_font' name='assets_model' value='<?php echo $request->get('assets_model') ?>' size='30'></td>
                    <td class='winbox' align='center'><input type='text' class='price_font' name='buy_ym' value='<?php echo $request->get('buy_ym') ?>' size='9' maxlength='8'></td>
                </tr>
                <tr>
                    <th class='winbox' nowrap>購入価格</th>
                    <th class='winbox' nowrap><font size =2>除却年月日</font></th>
                    <th class='winbox' colspan='3' nowrap>備考</th>
                    <th class='winbox' colspan='2' nowrap>追加/変更/削除</th>
                </tr>
                <tr>
                    <td class='winbox' align='center'><input type='text' class='price_font' name='buy_price' value='<?php echo $request->get('buy_price') ?>' size='9' maxlength='8'></td>
                    <td class='winbox' align='center'><input type='text' class='price_font' name='delete_ym' value='<?php echo $request->get('delete_ym') ?>' size='9' maxlength='8'></td>
                    <td class='winbox' colspan='3' align='center'><input type='text' class='price_font' name='note' value='<?php echo $request->get('note') ?>' size='50'></td>
                    <td class='winbox' colspan='2' align='center'>
                        <input type='submit' class='entry_font' name='entry' value='追加'>
                    &nbsp;&nbsp;
                        <input type='submit' class='entry_font' name='change' value='変更'>
                    &nbsp;&nbsp;
                        <input type='submit' class='entry_font' name='del' value='削除'>
                    </td>
                </tr>
             </table>
                </td></tr>
        </table> <!----------------- ダミーEnd ------------------>
    </form>
    </center>
    <br>
    <?php
    if ($request->get('view_flg') == '照会') {
        echo "<iframe hspace='0' vspace='0' frameborder='0' scrolling='yes' src='list/smallSum_assets_List-{$_SESSION['User_ID']}.html' name='list' align='center' width='100%' height='60%' title='リスト'>\n";
        echo "    一覧を表示しています。\n";
        echo "</iframe>\n";
    }
    ?>
</body>
<?php echo $menu->out_alert_java() ?>
</html>
