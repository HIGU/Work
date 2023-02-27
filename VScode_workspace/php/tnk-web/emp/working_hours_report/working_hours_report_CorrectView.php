<?php
//////////////////////////////////////////////////////////////////////////////
// 就業週報の集計 修正内容の入力                                   View 部  //
// Copyright (C) 2008 - 2017 Norihisa.Ohya usoumu@nitto-kohki.co.jp         //
// Changed history                                                          //
// 2008/11/21 Created   working_hours_report_CorrectView.php                //
// 2017/06/02 部課長説明 本格稼動                                           //
// 2017/06/29 エラー箇所等を訂正                                            //
//////////////////////////////////////////////////////////////////////////////
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
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
    if (obj.uid.value.length != 6) {
        alert('社員番号は６桁の数字を入力して下さい！');
        obj.uid.focus();
        obj.uid.select();
        return false;
    } else if ( !(isDigit(obj.uid.value)) ) {
        alert('社員番号は数字以外入力出来ません！');
        obj.uid.focus();
        obj.uid.select();
        return false;
    }
    if (obj.working_date.value.length != 8) {
        alert('就業年月日は８桁の数字を入力して下さい！(例：2008年4月1日→20080401)');
        obj.working_date.focus();
        obj.working_date.select();
        return false;
    } else if ( !(isDigit(obj.working_date.value)) ) {
        alert('就業年月日は数字以外入力出来ません！');
        obj.working_date.focus();
        obj.working_date.select();
        return false;
    }
    if (obj.correct_contents.value.length == 0) {
        alert('訂正内容が入力されていません！');
        obj.correct_contents.focus();
        obj.correct_contents.select();
        return false;
    }
    return true;
}
// -->
</script>

<link rel='stylesheet' href='/menu_form.css' type='text/css' media='screen'>
<link rel='stylesheet' href='working_hours_report.css' type='text/css' media='screen'>
<style type='text/css'>
<!--
body {
    background-image:none;
}
-->
</style>
</head>
<body scroll=no>
    <center>
       <form name='entry_form' action='working_hours_report_CorrectMain.php' method='post' onSubmit='return chk_entry(this)'>
            <table bgcolor='#d6d3ce' width='300' align='center' border='1' cellspacing='0' cellpadding='3'>
                <tr><td> <!----------- ダミー(デザイン用) ------------>
            <table class='winbox_field' width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <THEAD>
                <!-- テーブル ヘッダーの表示 -->
                <tr>
                    <th class='winbox' colspan='5' nowrap align='left'><font size='3'>※訂正したい方の社員番号と訂正したい就業年月日と訂正内容を入力してください。</font></th>
                </tr>
                <tr>
                    <th class='winbox' colspan='5' nowrap align='left'><font size='3'>　例）社員番号：300144(大谷)　就業年月日:20081201　訂正内容：残業時間を2時間から1時間にして下さい。</font></th>
                </tr>
                <tr>
                    <th class='winbox' colspan='5' nowrap align='left'><font size='3'>　注）同じ社員番号・就業年月日の登録は上書きされますので注意してください。</font></th>
                </tr>
                <tr>
                    <th class='winbox' nowrap>社員番号</th>
                    <th class='winbox' nowrap>就業年月日</th>
                    <th class='winbox' nowrap>訂正内容</th>
                    <th class='winbox' colspan='2' nowrap>追加・変更 / 削除</th>
                </tr>
            </THEAD>
            <TFOOT>
                <!-- 現在はフッターは何もない -->
            </TFOOT>
            <TBODY>
                <tr>
                    <td class='winbox' align='center'><input type='text' class='price_font' name='uid' value='<?php echo $field_g = $request->get('uid') ?>' size='8' maxlength='6'></td>
                    <td class='winbox' align='center'><input type='text' class='price_font' name='working_date' value='<?php echo $field_g = $request->get('working_date') ?>' size='10' maxlength='8'></td>
                    <td class='winbox' align='center'><input type='text' class='price_font' name='correct_contents' value='<?php echo $field_g = $request->get('correct_contents') ?>' size='80'></td>
                    <td class='winbox' align='center'>
                        <input type='submit' class='entry_font' name='entry' value='追加・変更' onClick='return confirm("この訂正内容を変更・追加してよろしいですか？");'>
                    </td>
                    <td class='winbox' align='center'>
                        <input type='submit' class='entry_font' name='del' value='削除' onClick='return confirm("この訂正内容を削除してよろしいですか？\n削除すると元には戻せませんが、よろしいですか？");'>
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
        echo "<iframe hspace='0' vspace='0' frameborder='0' scrolling='yes' src='list/working_hours_report_Correct_List-{$_SESSION['User_ID']}.html' name='list' align='center' width='100%' height='70%' title='リスト'>\n";
        echo "    一覧を表示しています。\n";
        echo "</iframe>\n";
    }
    ?>
    <div align='center'><input type='button' name='closeButton' id='closeID' value='&nbsp;&nbsp;閉じる&nbsp;&nbsp;' onClick='window.close();'></div>
</body>
<?php echo $menu->out_alert_java() ?>
</html>
