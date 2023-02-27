<?php
//////////////////////////////////////////////////////////////////////////////
// 少額資産管理台帳追加・編集 View部 smallSum_assetsView_View.php           //
// Copyright (C) 2010 Norihisa.Ooya norihisa_ooya@nitto-kohki.co.jp         //
// Changed history                                                          //
// 2010/10/05 Created  smallSum_assetsView_View.php                         //
//////////////////////////////////////////////////////////////////////////////
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
        alert('管理部門名が入力されていません！');
        obj.act_name.focus();
        obj.act_name.select();
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
    if ( !(isDigitDot(obj.acquisition_money.value)) ) {
        alert('取得金額は数字以外入力出来ません！');
        obj.acquisition_money.focus();
        obj.acquisition_money.select();
        return false;
    } else {
        if (obj.acquisition_money.value <= 0) {
            alert('取得金額は０より大きい数字を入力して下さい！');
            obj.acquisition_money.focus();
            obj.acquisition_money.select();
            return false;
        }
    }
    
    if(!obj.acquisition_date.value.length){
        alert("取得年月が入力されていません。");
        obj.acquisition_date.focus();
        obj.acquisition_date.select();
        return false;
    } else if(!(isDigit(obj.acquisition_date.value))){
        alert("取得年月には数値以外の文字は入力出来ません｡");
        obj.acquisition_date.focus();
        obj.acquisition_date.select();
        return false;
    }
    
    if (obj.acquisition_date.value.length == 6) {
    } else {
        alert('取得年月はYYYYMMの６桁で入力してください。');
        obj.acquisition_date.focus();
        obj.acquisition_date.select();
        return false;
    }
    
    if ( !(isDigit(obj.durable_years.value)) ) {
        alert('耐用年数は数字以外入力出来ません！');
        obj.durable_years.focus();
        obj.durable_years.select();
        return false;
    } else {
        if (obj.durable_years.value <= 0) {
            alert('耐用年数は０より大きい数字を入力して下さい！');
            obj.durable_years.focus();
            obj.durable_years.select();
            return false;
        }
    }
    
    if ( !(isDigitDot(obj.annual_rate.value)) ) {
        alert('年間率は数字以外入力出来ません！');
        obj.annual_rate.focus();
        obj.annual_rate.select();
        return false;
    } else {
        if (obj.annual_rate.value <= 0) {
            alert('年間率は０より大きい数字を入力して下さい！');
            obj.annual_rate.focus();
            obj.annual_rate.select();
            return false;
        }
    }
    
    if(obj.end_date.value.length == 0){
        return true;
    } else {
        if(!(isDigit(obj.end_date.value))){
            alert("除却年月には数値以外の文字は入力出来ません｡");
            obj.end_date.focus();
            obj.end_date.select();
            return false;
        }
        if (obj.end_date.value.length == 6) {
        } else {
            alert('除却年月はYYYYMMの６桁で入力してください。');
            obj.end_date.focus();
            obj.end_date.select();
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
    <form name='entry_form' method='post' action='smallSum_assetsView_Main.php'>
        <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
                <tr><td> <!----------- ダミー(デザイン用) ------------>
            <table class='winbox_field' width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
                <tr>
                    <th class='winbox' nowrap>管理部門名</th>
                    <th class='winbox' nowrap>設置場所</th>
                    <th class='winbox' nowrap>品目名</th>
                    <th class='winbox' nowrap><font size =2>メーカー・型式</font></th>
                    <th class='winbox' nowrap><font size =2>除却を含む</font></th>
                    <td class='winbox' align='center' rowspan='2'>
                        <input type='submit' class='entry_font' name='search' value='検索'>
                    </td>
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
                    <td class='winbox' align='center'><input type='text' class='price_font' name='assets_name' value='<?php echo $request->get('assets_name') ?>' size='30'></td>
                    <td class='winbox' align='center'><input type='text' class='price_font' name='assets_model' value='<?php echo $request->get('assets_model') ?>' size='30'></td>
                    <?php
                    if ($request->get('delete_in') == 'IN') {
                    ?>
                    <td class='winbox' align='center'><input type='checkbox' class='price_font' name='delete_in' value='IN' checked></td>
                    <?php } else { ?>
                    <td class='winbox' align='center'><input type='checkbox' class='price_font' name='delete_in' value='IN'></td>
                    <?php } ?>
                </tr>
             </table>
                </td></tr>
        </table> <!----------------- ダミーEnd ------------------>
    </form>
    </center>
    <br>
    <?php
    if ($request->get('view_flg') == '照会') {
        echo "<iframe hspace='0' vspace='0' frameborder='0' scrolling='yes' src='list/smallSum_assetsView_List-{$_SESSION['User_ID']}.html' name='list' align='center' width='100%' height='80%' title='リスト'>\n";
        echo "    一覧を表示しています。\n";
        echo "</iframe>\n";
    }
    ?>
</body>
<?php echo $menu->out_alert_java() ?>
</html>
