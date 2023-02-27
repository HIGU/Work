<?php
//////////////////////////////////////////////////////////////////////////////
// 固定資産追加・編集 View部                                                //
// Copyright (C) 2007-2011 Norihisa.Ooya usoumu@nitto-kohki.co.jp           //
// Changed history                                                          //
// 2007/12/07 Created  assemblyRate_capitalAsset_View.php                   //
// 2007/12/13 上記コメントに拡張子.phpが入っていないのを追加                //
// 2008/01/09 追加・削除時に日付データの受け渡しを追加                      //
// 2008/02/05 固定資産No.の桁数を9桁から11桁に変更XX-XX-XXX-X               //
//            (枝番の固定資産対応の為)                                      //
//            固定資産No.のMaxlengthを11に変更。フォームのサイズを調整      //
// 2011/06/22 format_date系をtnk_funcに移動のためこちらを削除               //
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
    if (obj.asset_no.value.length == 0) {
        alert('固定資産Ｎｏ.が入力されていません！');
        obj.asset_no.focus();
        obj.asset_no.select();
        return false;
    }
    
    if (obj.asset_no.value.length == 9) {
    } else {
        if (obj.asset_no.value.length == 11) {
        } else {
            alert('固定資産Ｎｏ.はXX-XX-XXXの９桁かXX-XX-XXX-Xの１１桁で入力してください。');
            obj.asset_no.focus();
            obj.asset_no.select();
            return false;
        }
    }
    
    if (obj.asset_name.value.length == 0) {
        alert('固定資産名称が入力されていません！');
        obj.asset_name.focus();
        obj.asset_name.select();
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
    <form name='entry_form' method='post' action='assemblyRate_capitalAsset_Main.php' onSubmit='return chk_entry(this)'>
        <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
                <tr><td> <!----------- ダミー(デザイン用) ------------>
            <table class='winbox_field' width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
                <tr>
                    <th class='winbox' nowrap>グループ名</th>
                    <th class='winbox' nowrap><font size =2>資産No.</font><BR><font size=1>(XX-XX-XXX)</font></th>
                    <th class='winbox' nowrap>資産名称</th>
                    <th class='winbox' nowrap>取得金額</th>
                    <th class='winbox' nowrap>取得年月</th>
                    <th class='winbox' nowrap>耐用年数</th>
                    <th class='winbox' nowrap>年間率</th>
                    <th class='winbox' nowrap>除却年月</th>
                    <th class='winbox' colspan='2' nowrap>追加・削除</th>
                </tr>
                <tr>
                    <td class='winbox' align='center'>
                        <span class='caption_font'>
                            <select name='group_no' size='1'>
                            <?php
                            $res_g = $result->get_array2('res_g');
                            for ($i=0; $i<$result->get('rows_g'); $i++) {
                                if ( $res_g[$i][0] == $request->get('group_no')) {
                                    printf("<option value='%s' selected>%s</option>\n", $res_g[$i][0], $res_g[$i][1]);
                                } else {
                                    printf("<option value='%s'>%s</option>\n", $res_g[$i][0], $res_g[$i][1]);
                                }
                            }
                            ?>
                            </select>
                        </span>
                    </td>
                    <td class='winbox' align='center'><input type='text' class='price_font' name='asset_no' value='<?php echo $request->get('asset_no') ?>' size='14' maxlength='11'></td>
                    <td class='winbox' align='center'><input type='text' class='price_font' name='asset_name' value='<?php echo $request->get('asset_name') ?>' size='35'></td>
                    <td class='winbox' align='center'><input type='text' class='price_font' name='acquisition_money' value='<?php echo $request->get('acquisition_money') ?>' size='15'></td>
                    <td class='winbox' align='center'><input type='text' class='price_font' name='acquisition_date' value='<?php echo $request->get('acquisition_date') ?>' size='7' maxlength='6'></td>
                    <td class='winbox' align='center'><input type='text' class='price_font' name='durable_years' value='<?php echo $request->get('durable_years') ?>' size='3' maxlength='3'></td>
                    <td class='winbox' align='center'><input type='text' class='price_font' name='annual_rate' value='<?php echo $request->get('annual_rate') ?>' size='6' maxlength='5'></td>
                    <td class='winbox' align='center'><input type='text' class='price_font' name='end_date' value='<?php echo $request->get('end_date') ?>' size='7' maxlength='6'></td>
                    <td class='winbox' align='center'>
                        <input type='submit' class='entry_font' name='entry' value='追加・変更'>
                        <input type='hidden' class='entry_font' name='wage_ym' value='<?php echo $request->get('wage_ym') ?>'>
                    </td>
                    <td class='winbox' align='center'>
                        <input type='submit' class='entry_font' name='del' value='削除'>
                        <input type='hidden' class='entry_font' name='wage_ym' value='<?php echo $request->get('wage_ym') ?>'>
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
        echo "<iframe hspace='0' vspace='0' frameborder='0' scrolling='yes' src='list/assemblyRate_capitalAsset_List-{$_SESSION['User_ID']}.html' name='list' align='center' width='100%' height='80%' title='リスト'>\n";
        echo "    一覧を表示しています。\n";
        echo "</iframe>\n";
    }
    ?>
</body>
<?php echo $menu->out_alert_java() ?>
</html>
