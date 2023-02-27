<?php
//////////////////////////////////////////////////////////////////////////////
// 社員情報管理の include file 初期画面（デフォルト）                       //
// Copyright (C) 2001-2005 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2001/07/07 Created  view_default.php                                     //
// 2002/08/07 register_globals = Off 対応                                   //
// 2003/02/14 売上関係ニュー のフォントを style で指定に変更                //
//                              ブラウザーによる変更が出来ない様にした      //
// 2004/06/10 view_user($_SESSION['User_ID']) をメニューヘッダーの下に追加  //
// 2004/12/23 ルートからの真実のスクリプト名からドキュメントルート分を削除  //
// 2005/01/17 view_user($_SESSION['User_ID'])→view_file_name(__FILE__)へ   //
//            emp_menu.phpをMenuHeader class へ移行したための変更           //
//////////////////////////////////////////////////////////////////////////////
// access_log("emp_menu_view_default.php");        // Script Name 手動設定
// Script Name 自動設定 ルートからの真実のスクリプト名からドキュメントルート分を削除
access_log(substr(__FILE__, strlen($_SERVER['DOCUMENT_ROOT'])));
echo view_file_name(__FILE__);
?>
<style type="text/css">
<!--
.top-font {
    font-size: 12pt;
    font-family: monospace;
    font-weight: bold;
    }
.p-font {
    font-size: 12pt;
    font-family: monospace;
    }
ol {
    font-size: 11pt;
    font-family: monospace;
    }
-->
</style>
<script language="Javascript">
<!--
str = navigator.appName.toUpperCase();
if(str.indexOf("NETSCAPE")>=0)
    document.write("<table width='100%' height=755 bgcolor='#ffffff' cellpadding=10>");
if(str.indexOf("EXPLORER")>=0)
    document.write("<table width='100%' height='100%' bgcolor='#ffffff' cellpadding=10>");
//-->
</script>
<noscript>
    <table width="100%" height="100%" bgcolor="#ffffff" cellpadding=10>
</noscript>
    <tr><td valign="top">
    <table>
        <tr>
            <td>
                <p><img src="../img/t_nitto_logo2.gif" width=348 height=83 border=0></p>
            </td>
        </tr>
        <tr>
            <td class='top-font'>
                社員情報管理
            </td>
        </tr>
    </table>
    <p class='p-font'>機能について</p>
    <ol>
    <li>自己情報表示
        <br>自分のユーザー情報を表示します。パスワードの変更も可能です。
<?php   if($_SESSION["Auth"] >= AUTH_LEBEL2){   ?>
    <li>従業員新規登録
        <br>入社・転籍・出向された従業員を登録します。
<?php   }
    if($_SESSION["Auth"] >= AUTH_LEBEL3){   ?>
    <li>データベース操作
        <br>データベースへ任意の問い合わせ、更新、削除を行います。
<?php   } ?>
    <li>検索
        <ul>
        <li>従業員情報
            <br>該当するすべての従業員の情報を表示します。
    <?php   if($_SESSION["Auth"] >= AUTH_LEBEL1){   ?>
        <li>住所情報
            <br>従業員情報より住所に対応する情報を表示します。
        <li>教育訓練記録
            <br>従業員に対して行われた教育記録、又その従業員が取得している資格などの
            情報を表示します。
    <?php   } ?>
        </ul>
        社員No、又は名前を検索キーとして、必要な情報を選択してください。検索キーにフルネームを
        指定する場合、姓と名の間にはスペースを入れてください。
    </ol>
    </td></tr>
    <!--
        <tr><td valign="bottom"><br>
            <img src="../img/php4.gif" width=64 height=32>
            <img src="../img/linux.gif" width=74 height=32>  
            <img src="../img/redhat.gif" width=96 height=32>   
            <img src="../img/apache.gif" width=259 height=32> 
            <img src="../img/pgsql.gif" width=160 height=32>
        </td></tr>
    -->
</table>
