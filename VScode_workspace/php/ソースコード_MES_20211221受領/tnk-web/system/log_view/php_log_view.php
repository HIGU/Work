<?php
//////////////////////////////////////////////////////////////////////////////
// php のエラーログ表示     HTML部                                          //
// Copyright(C) 2004-2007  Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2004/04/23 Created  php_log_view_clear.php                               //
// 2004/06/07 /tmp/php_error ファイルが無かった場合の処理を追加             //
// 2004/07/25 MenuHeader class を使用して共通メニュー・認証方式へ変更       //
//            iframeでphp_errorとapache error_logとapache access_logを表示  //
// 2004/12/25 style='overflow:hidden;' (-xy両方)を追加                      //
// 2005/01/14 F2/F12キーで戻るための対応で document.body.focus() を追加     //
// 2005/01/25 clear_access_log ボタンを追加しログのメンテナンスをする       //
// 2005/12/10 E_ALL → E_STRICT へ変更 access_logのファイル名変更           //
// 2006/10/05 php5にUPのため =& new → = new へ & を削除                    //
// 2007/04/21 斎藤千尋さん用に認証チェックを追加                            //
// 2007/07/13 履歴表示ロジックの追加。グローバル表記から関数表記へ          //
// 2007/08/08 tac /tmp/access_log.tmp を追加して逆順表示とリロード追加      //
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
<style type="text/css">
<!--
.clear_font {
    font-size:      8pt;
    font-weight:    bold;
    font-family:    monospace;
}
.sub_caption {
    color:          black;
    font-size:      12pt;
    font-weight:    bold;
    font-family:    monospace;
    text-decoration:underline;
}
pre {
    color:          black;
    font-size:      10pt;
    /* font-weight:    bold; */
    font-family:    monospace;
    /* text-decoration:underline; */
}
iframe {
    margin: 0%
    color:          blue;
    font-size:      10pt;
    font-family:    monospace;
    background-color: #c8c8c8;
}
-->
</style>
<script type='text/javascript'>
/***** トグルスイッチ式の自動更新ON/OFF設定メソッド *****/
/***** グローバル変数の初期化 *****/
var AutoReLoad = "";
var AutoReLoadID = "";
function switchAutoReLoad(mSec)
{
    if (AutoReLoad == 'ON') {      // ON → OFF
        if (AutoReLoadID) {
            clearInterval(AutoReLoadID);
            AutoReLoad = "OFF";
            // document.getElementById("toggleView").innerHTML = "MAN";
            document.getElementById("reloadButton").value = "Auto Reload";
            alert("\n access_log 更新 を MAN(手動) にしました。\n");
        }
    } else {                            // OFF → ON
        if (mSec >= 5000 && mSec <= 300000) {  // 5秒以上で300秒(5分)以下
            AutoReLoadID = setInterval("logReload()", mSec);
            // document.getElementById("toggleView").innerHTML = "AUT";
            document.getElementById("reloadButton").value = "Manual Reload";
            if (AutoReLoad != "") {        // 初回の場合はMessageを表示しない
                alert("\n access_log 更新 を AUT(自動) にしました。\n");
            }
            AutoReLoad = "ON";
        }
    }
}
/***** 各種ログ リロード *****/
function logReload()
{
    php_error_log.location.reload(true);
    setTimeout("apache_error_log.location.reload(true)", 1000);
    setTimeout("apache_access_log.location.reload(true)", 2000);
}
</script>
</head>
<body style='overflow:hidden;'>
    <center>
<?php echo $menu->out_title_border() ?>
        <table border='0' width='100%'>
            <tr nowrap>
                <td align='center' width='15%'>
                    <table align='left' border='1' cellspacing='0' cellpadding='0'>
                        <form action='<?php echo $menu->out_self() ?>' method='post'>
                        <td>
                            <input class='clear_font' type='submit' name='current' value='Current' >
                        </td>
                        </form>
                    </table>
                </td>
                <td class='sub_caption' align='center' width='70%'>
                    php error log check
                </td>
                <td align='center' width='8%'>
                    <table align='center' border='1' cellspacing='0' cellpadding='0'>
                        <form action='<?php echo $menu->out_self() ?>' method='post'>
                        <td>
                            <input class='clear_font' type='submit' name='history_php' value='History' >
                        </td>
                        </form>
                    </table>
                </td>
                <td align='center' width='7%'>
                    <table align='center' border='1' cellspacing='0' cellpadding='0'>
                        <form action='<?php echo $menu->out_self() ?>' method='post'>
                        <td>
                            <input class='clear_font' type='submit' name='clear_php' value='Clear' >
                        </td>
                        </form>
                    </table>
                </td>
            </tr>
        </table>
    </center>
    <iframe scrolling='yes' src='php_error_log.php?<?php echo $request->get('history_php')?>&id=<?php echo $uniq?>' name='php_error_log' align='center' width='100%'
        height='<?php if ($result->get('php_flg')) echo '300'; else echo '150'; ?>' title='php_error_log'>
        php の php_error_log を表示しています。
    </iframe>
    
    <center>
        <table border='0' width='100%'>
            <tr nowrap>
                <td align='center' width='15%'>
                    <table align='left' border='1' cellspacing='0' cellpadding='0'>
                        <form action='<?php echo $menu->out_self() ?>' method='post'>
                        <td>
                            <input class='clear_font' type='submit' name='current' value='Current' >
                        </td>
                        </form>
                    </table>
                </td>
                <td class='sub_caption' align='center' width='70%'>
                    apache error log check
                </td>
                <td align='center' width='8%'>
                    <table align='center' border='1' cellspacing='0' cellpadding='0'>
                        <form action='<?php echo $menu->out_self() ?>' method='post'>
                        <td>
                            <input class='clear_font' type='submit' name='history_apache' value='History'>
                        </td>
                        </form>
                    </table>
                </td>
                <td align='center' width='7%'>
                    <table align='center' border='1' cellspacing='0' cellpadding='0'>
                        <form action='<?php echo $menu->out_self() ?>' method='post'>
                        <td>
                            <input class='clear_font' type='submit' name='clear_apache' value='Clear'>
                        </td>
                        </form>
                    </table>
                </td>
            </tr>
        </table>
    </center>
    <iframe hspace='0' vspace='0' scrolling='yes' src='apache_error_log.php?<?php echo $request->get('history_apache')?>&id=<?php echo $uniq?>' name='apache_error_log' align='center' width='100%' height='120' title='error_log'>
        apache の error_log を表示しています。
    </iframe>
    
    <center>
        <table border='0' width='100%'>
            <tr nowrap>
                <td width='15%'></td>
                <td class='sub_caption' align='center' width='70%'>
                    apache access log check
                </td>
                <td align='center' width='15%'>
                    <table align='left' border='1' cellspacing='0' cellpadding='0'>
                        <form action='<?php echo $menu->out_self() ?>' method='post'>
                            <td>
                                <!-- <input class='clear_font' type='submit' name='clear_access_log' value='Clear' > -->
                                <input id='reloadButton' class='clear_font' type='button' name='reload_access_log' value='Auto Reload'
                                    onClick='switchAutoReLoad(5000);' title='access_logの読み込みを自動・手動にトグル式に切替えます。'
                                >
                            </td>
                        </form>
                    </table>
                </td>
            </tr>
        </table>
    </center>
    <iframe hspace='0' vspace='0' scrolling='yes' src='apache_log.php?id=<?php echo $uniq?>' name='apache_access_log' align='center' width='100%'
        height='<?php if ($result->get('php_flg')) echo '150'; else echo '300'; ?>' title='access_log'>
        apache の access_log を表示しています。
    </iframe>
</body>
</html>
