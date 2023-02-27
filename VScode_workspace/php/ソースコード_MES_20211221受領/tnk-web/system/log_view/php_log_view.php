<?php
//////////////////////////////////////////////////////////////////////////////
// php �Υ��顼��ɽ��     HTML��                                          //
// Copyright(C) 2004-2007  Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2004/04/23 Created  php_log_view_clear.php                               //
// 2004/06/07 /tmp/php_error �ե����뤬̵���ä����ν������ɲ�             //
// 2004/07/25 MenuHeader class ����Ѥ��ƶ��̥�˥塼��ǧ���������ѹ�       //
//            iframe��php_error��apache error_log��apache access_log��ɽ��  //
// 2004/12/25 style='overflow:hidden;' (-xyξ��)���ɲ�                      //
// 2005/01/14 F2/F12��������뤿����б��� document.body.focus() ���ɲ�     //
// 2005/01/25 clear_access_log �ܥ�����ɲä����Υ��ƥʥ󥹤򤹤�       //
// 2005/12/10 E_ALL �� E_STRICT ���ѹ� access_log�Υե�����̾�ѹ�           //
// 2006/10/05 php5��UP�Τ��� =& new �� = new �� & ����                    //
// 2007/04/21 ��ƣ��Ҥ����Ѥ�ǧ�ڥ����å����ɲ�                            //
// 2007/07/13 ����ɽ�����å����ɲá������Х�ɽ������ؿ�ɽ����          //
// 2007/08/08 tac /tmp/access_log.tmp ���ɲä��Ƶս�ɽ���ȥ�����ɲ�      //
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
/***** �ȥ��륹���å����μ�ư����ON/OFF����᥽�å� *****/
/***** �����Х��ѿ��ν���� *****/
var AutoReLoad = "";
var AutoReLoadID = "";
function switchAutoReLoad(mSec)
{
    if (AutoReLoad == 'ON') {      // ON �� OFF
        if (AutoReLoadID) {
            clearInterval(AutoReLoadID);
            AutoReLoad = "OFF";
            // document.getElementById("toggleView").innerHTML = "MAN";
            document.getElementById("reloadButton").value = "Auto Reload";
            alert("\n access_log ���� �� MAN(��ư) �ˤ��ޤ�����\n");
        }
    } else {                            // OFF �� ON
        if (mSec >= 5000 && mSec <= 300000) {  // 5�ðʾ��300��(5ʬ)�ʲ�
            AutoReLoadID = setInterval("logReload()", mSec);
            // document.getElementById("toggleView").innerHTML = "AUT";
            document.getElementById("reloadButton").value = "Manual Reload";
            if (AutoReLoad != "") {        // ���ξ���Message��ɽ�����ʤ�
                alert("\n access_log ���� �� AUT(��ư) �ˤ��ޤ�����\n");
            }
            AutoReLoad = "ON";
        }
    }
}
/***** �Ƽ�� ����� *****/
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
        php �� php_error_log ��ɽ�����Ƥ��ޤ���
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
        apache �� error_log ��ɽ�����Ƥ��ޤ���
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
                                    onClick='switchAutoReLoad(5000);' title='access_log���ɤ߹��ߤ�ư����ư�˥ȥ��뼰�����ؤ��ޤ���'
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
        apache �� access_log ��ɽ�����Ƥ��ޤ���
    </iframe>
</body>
</html>
