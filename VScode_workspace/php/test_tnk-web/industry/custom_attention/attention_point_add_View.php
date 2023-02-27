<?php
//////////////////////////////////////////////////////////////////////////////
// 特注カプラ冶具・作業注意点編集 View部 attention_point_add_View.php       //
// Copyright (C) 2013-2013 Norihisa.Ooya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2013/01/31 Created  attention_point_add_View.php                         //
// 2013/02/12 入力桁数を拡張                                                //
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
        <form name='entry_form' action='attention_point_add_Main.php' method='post' enctype='multipart/form-data' onSubmit='return chk_entry(this)'>
            <table bgcolor='#d6d3ce' width='300' align='center' border='1' cellspacing='0' cellpadding='3'>
                <tr><td> <!----------- ダミー(デザイン用) ------------>
            <table class='winbox_field' width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <THEAD>
                <!-- テーブル ヘッダーの表示 -->
                <tr>
                    <td width='700' bgcolor='#ffffc6' align='center' colspan='15'>
                    特注カプラ冶具・作業注意点
                    </td>
                </tr>
                <tr>
                    <th class='winbox' nowrap>作業内容</th>
                    <th class='winbox' nowrap>備考</th>
                    <th class='winbox' nowrap>ファイル</th>
                    
                    <th class='winbox' colspan='2' nowrap>追加・削除</th>
                </tr>
            </THEAD>
            <TFOOT>
                <!-- 現在はフッターは何もない -->
            </TFOOT>
            <TBODY>
                <tr>
                    <td class='winbox' align='center'><input type='text' class='price_font' name='point_name' value='<?php echo $request->get('point_name') ?>' size='30'></td>
                    <td class='winbox' align='center'><input type='text' class='price_font' name='point_note' value='<?php echo $request->get('point_note') ?>' size='30'></td>
                    <td class='winbox' align='center'><input type='file' class='price_font' name='file_name' value='<?php echo $request->get('file_name') ?>' size='50'></td>
                    <td class='winbox' align='center'>
                        <input type='submit' class='entry_font' name='entry' value='追加'>
                    </td>
                    <td class='winbox' align='center'>
                        <input type='submit' class='entry_font' name='del' value='削除'>
                        <input type='hidden' class='entry_font' name='file_name_cp' value='<?php echo $request->get('file_name_cp') ?>'>
                    </td>
                </tr>
            </TBODY>
            </table>
                </td></tr>
            </table> <!----------------- ダミーEnd ------------------>
        </form>
    </center>
    <br>
    <?php
    if ($request->get('view_flg') == '照会') {
        echo "<iframe hspace='0' vspace='0' frameborder='0' scrolling='yes' src='list/attention_point_add_List-{$_SESSION['User_ID']}.html' name='list' align='center' width='100%' height='70%' title='リスト'>\n";
        echo "    一覧を表示しています。\n";
        echo "</iframe>\n";
    }
    ?>
</body>
<?php echo $menu->out_alert_java() ?>
</html>
