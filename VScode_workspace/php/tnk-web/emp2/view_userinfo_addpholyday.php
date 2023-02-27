<?php
//////////////////////////////////////////////////////////////////////////////
// 社員情報管理の include file 計画有給の登録 選択フォーム                  //
// Copyright(C) 2015-2015 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp     //
// Changed history                                                          //
// 2015/06/18 Created  view_userinfo_addpholyday.php                        //
//////////////////////////////////////////////////////////////////////////////
//  access_log("view_userinfo_chgcapacity.php");        // Script Name 手動設定
access_log(substr(__FILE__, strlen($_SERVER['DOCUMENT_ROOT'])));
echo view_file_name(__FILE__);
?>
<table width="100%">
    <tr><td width="100%" bgcolor="#ff6600" align="center" colspan="2"><font color="#ffffff">計画有給登録</font></td></tr>
    <tr>
        <td width="100%" valign="top" colspan=2>
            <table width="100%">
                <form method="post" action="emp_menu.php?func=<?php echo(FUNC_HOLYDAYREGIST) ?>" onSubmit="return chkData(this)">
                    <tr><td>取得日を入力してください。</td></tr>
                    <tr>
                        <td><input type="text" name="begin_date_1" size=4 maxlength=4 value=<?php echo(substr(date("Y-m-d"),0,4)); ?>><font size=+1> - </font><input type="text" name="begin_date_2" size=2 maxlength=2 value=<?php echo(substr(date("Y-m-d"),5,2)); ?>><font size=+1> - </font><input type="text" name="begin_date_3" size=2 maxlength=2 value=<?php echo(substr(date("Y-m-d"),8,2)); ?>></td>
                    </tr>
                    <tr><td>登録人数を入力してください</td></tr>
                    <tr><td><input type="text" size=3 name="entry_num" maxlength=3 value=1>人</td></tr>
                    <tr><td align="right"><input type="submit" value="次へ"></td></tr>
                    <tr><td align="left">*半角で入力してください。</td></tr>
                    <tr><td><input type="hidden" name="end_date_1" size=4 maxlength=4 value=<?php echo(substr(date("Y-m-d"),0,4)); ?>><input type="hidden" name="end_date_2" size=2 maxlength=2 value=<?php echo(substr(date("Y-m-d"),5,2)); ?>><input type="hidden" name="end_date_3" size=2 maxlength=2 value=<?php echo(substr(date("Y-m-d"),8,2)); ?>></td>
                </form>
                </tr>
            </table>
        <hr>
</table>
