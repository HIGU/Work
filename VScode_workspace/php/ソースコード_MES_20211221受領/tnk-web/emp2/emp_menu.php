<?php
//////////////////////////////////////////////////////////////////////////////
// �Ұ��������(�ͻ��ط�) & �ɣӣ϶���/��ʷ���                             //
// Copyright (C) 2001-2020 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
//                                      2001/10/15 all rights reserved.     //
// Changed history                                                          //
// 2001/10/15 Created   emp_menu.php                                        //
// 2002/04/23 ��ʤȶ����leftɽ��������24ʸ���ޤǤ�����(substr����)        //
//            capacity_master(���)  receive_master(����)                   //
// 2002/08/07 ���å����������ɲ� & register_globals = Off �б�            //
// 2002/08/27 �ե졼�� �б�                                                 //
// 2003/01/31 ��¦�θ�����˥塼�Υǥ������ѹ�                              //
// 2003/02/14 ���ط��˥塼 �Υե���Ȥ� style �ǻ�����ѹ�                //
//                              �֥饦�����ˤ���ѹ�������ʤ��ͤˤ���      //
// 2003/04/02 �������ν�°�˽и���������Ƥ��ɲ�                          //
// 2003/04/23 ���׾���ܥ�����ɲ�(���Ȱ���ʿ��ǯ��ľ����Ψ��)            //
// 2003/12/05 mb_substr(trim($res[$i]['section_name']), -10)���ѹ�          //
// 2003/12/12 define���줿����ǥǥ��쥯�ȥ�ȥ�˥塼����Ѥ��ƴ�������    //
//            ob_start('ob_gzhandler') ���ɲ�  /confirm.js��/emp/confirm.js //
// 2004/02/13 index1.php��index.php���ѹ�(index1��authenticate���ѹ��Τ���) //
// 2004/06/10 �嵭�� H_WEB_HOST . EMP_MENU ���ѹ�                           //
// 2005/01/17 MenuHeader class ����Ѥ��ƶ��̥�˥塼���ڤ�ǧ���������ѹ�   //
//            left menu �� bgcolor='#003e7c'��'#7777bb'���ѹ����������     //
// 2005/01/26 background-image���ɲä��ƥǥ������ѹ�(AM/PM�����ؼ�)         //
// 2005/02/21 <hr>��<hr style='border-color:white;'>NN7.1�б� �����ܥ���˿�//
// 2005/11/15 �᡼�륢�ɥ쥹�Խ���˥塼���ɲ� FUNC_MAIL=22 mailAddress_Main//
// 2006/01/12 �ǥե���Ȥ�ɽ����Ұ����׾�����ѹ�                          //
// 2007/02/07 FUNC_RECIDREGISTCHK(�����Ͽ��ǧ����)���ɲ� ��ë              //
// 2007/02/09 FUNC_CAPIDREGISTCHK(�����Ͽ��ǧ����)���ɲ� ��ë              //
// 2007/07/24 �ǡ����١����������� view_admindb.php FUNC_DBADMIN          //
// 2007/08/30 php�Υ��硼�ȥ��åȥ�����ɸ�ॿ�����ѹ�(�侩������)           //
// 2007/09/11 ControllerHTTP_Class���Ѥˤ��E_ALL�б�(���Υ�����ץȤΤ�)   //
// 2008/09/22 FUNC_WORKINGHOURS(���Ƚ���Ȳ�)���ɲ�                    ��ë //
// 2008/09/25 ���Ƚ���ξȲ�򥵥��ɥ�˥塼�˰�ư                     ��ë //
// 2010/03/11 ����Ū����޼�����970268�ˤ���Ͽ�Ǥ���褦���ѹ�         ��ë //
// 2014/07/29 �Ժ߼ԾȲ񤬤Ǥ���褦��˥塼���ɲ�(��ë������Ĺ����)   ��ë //
//         �����ޥ�¦��������и��ԤΥǡ�������Ƥ��ʤ�(�׳�ǧ2014/07/29)   //
// 2015/01/30 �࿦�԰ʹߤ�������ܤ򥢥ɥߥ�Τߤ��ѹ�                 ��ë //
// 2015/03/27 ͭ�븡�������ɲ�                                         ��ë //
// 2015/06/18 �ײ�ͭ�����Ͽ���ɲ�                                     ��ë //
// 2015/06/19 �ײ�ͭ�����Ͽ���̤�ɽ�����ݻ֤������߷������ɲ�       ��ë //
// 2015/06/22 ���¥��顼����                                         ��ë //
// 2015/06/30 �Ժ߼ԤξȲ��getCheckAuthority(54)��������ѹ�          ��ë //
// 2015/07/30 ������ˡ��ǯ���(�⤤��)���̤˼Ұ����ơ��ѡ������Ƥ��ɲ� ��ë //
// 2019/01/31 ����Ū��ʿ�Ф����300551�ˤ���Ͽ�Ǥ���褦���ѹ�         ��ë //
// 2019/09/13 ͭ�������Ģ���ɲ�                                       ��ë //
// 2019/09/17 ͭ�������Ģ��ǯ�����ϥ����å���java��                   ��ë //
// 2020/03/10 ���̤˲�Ĺ�����ʾ���ɲ�                                 ��ë //
//            ͭ�������Ģ�ν�°�˽и����ɲáʤ��ä���ľ������PGMŪ�ˡ���ë //
// 2020/04/01 �ͻ���ư�ˤ�븢�¤��ѹ�                                 ��ë //
// 2020/05/22 ͭ�븡���ξ�����¤������55��ȴ���Ƥ����Τ��ɲ�         ��ë //
//////////////////////////////////////////////////////////////////////////////
// E_STRICT=2048(php5), E_ALL=2047(php4�ޤ�), E_ALL | E_STRICT=8191(�ǹ��٥������)
// ini_set('error_reporting', E_ALL | E_STRICT);
// ini_set('error_reporting', E_ALL & ~E_NOTICE & ~E_STRICT);
ini_set('error_reporting', E_ALL & ~E_NOTICE | E_STRICT);
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��
// ob_start();  //Warning: Cannot add header ���к��Τ����ɲá�2002/01/21
ob_start('ob_gzhandler');                   // ���ϥХåե���gzip����

require_once ('../function.php');           // ���� �ؿ�
require_once ('emp_function.php');          // �Ұ���˥塼����
require_once ('../MenuHeader.php');         // TNK ������ menu class
require_once ('../ControllerHTTP_Class.php');   // TNK ������ MVC Controller Class
// access_log();                            // include file�ǻ��Ѥ��뤿�ᡢ�����Ǥϻ��ꤷ�ʤ�

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader(0);                  // ǧ�ڥ����å�0=���̰ʾ� �����=TOP_MENU �����ȥ�̤����

////////////// ����������
$menu->set_site( 3, 99);                    // site_index=3(�Ұ���˥塼) site_id=99(�����ȥ�˥塼�򳫤�)
////////////// �꥿���󥢥ɥ쥹����
// $menu->set_RetUrl(MENU);                 // �̾�ϻ��ꤹ��ɬ�פϤʤ�(MENU=�ȥåץ�˥塼)
//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title('�� �� ��˥塼');
//////////// ɽ�������
$menu->set_caption('�Ұ��������(ISO)�����ƥ�');
//////////// �ƽ����action̾�ȥ��ɥ쥹����
// $menu->set_action('test_template',   EMP . 'emp_menu.php');
//////////// �֥饦�����Υ���å����к���
$uniq = $menu->set_useNotCache('empTarget');

///// �ꥯ�����ȥ��饹�Υ��󥹥��󥹤�����
$request = new Request();

/////////////// �桼�����ǡ����Υ��å�����ѿ���Ͽ
if ($request->get('lookupkind') != '') {
    $_SESSION['lookupkind']       = $request->get('lookupkind');
    $_SESSION['lookupkey']        = $request->get('lookupkey');
    $_SESSION['lookupkeykind']    = $request->get('lookupkeykind');
    $_SESSION['lookupsection']    = $request->get('lookupsection');
    $_SESSION['lookupposition']   = $request->get('lookupposition');
    $_SESSION['lookupentry']      = $request->get('lookupentry');
    $_SESSION['lookupcapacity']   = $request->get('lookupcapacity');
    $_SESSION['lookupreceive']    = $request->get('lookupreceive');
    $_SESSION['retireflg']        = $request->get('retireflg');
    $_SESSION['lookupyukyu']      = $request->get('lookupyukyu');
    $_SESSION['lookupyukyukind']  = $request->get('lookupyukyukind');
    $_SESSION['lookupyukyuf']     = $request->get('lookupyukyuf');
    $_SESSION['lookupyukyufive']  = $request->get('lookupyukyufive');
    $_SESSION['yukyulist']        = $request->get('yukyulist');
    $_SESSION['fivesection']      = $request->get('fivesection');
}

switch ($request->get('func')) {      // 2
case FUNC_NEWUSER;
    if ($_SESSION['Auth'] < AUTH_LEBEL2) {
        if ($_SESSION['User_ID'] != '970268' && $_SESSION['User_ID'] != '300551') {
            $_SESSION["s_sysmsg"] = "���¤Τʤ������Ǥ���<br>ͷ�Фʤ��ǲ�������";
            header('Location: ' . H_WEB_HOST . EMP_MENU);
            exit();
        }
    }
    break;
case FUNC_DBADMIN;  // 4
    if ($_SESSION['Auth'] < AUTH_LEBEL3) {
        if ($_SESSION['User_ID'] != '970268' && $_SESSION['User_ID'] != '300551') {
            $_SESSION["s_sysmsg"] = "���¤Τʤ������Ǥ���<br>ͷ�Фʤ��ǲ�������";
            header('Location: ' . H_WEB_HOST . EMP_MENU);
            exit();
        }
    }
    break;
case FUNC_CHGUSERINFO;  // 6
    if ($_SESSION['Auth'] < AUTH_LEBEL2) {
        if ($_SESSION['User_ID'] != '970268' && $_SESSION['User_ID'] != '300551') {
            $_SESSION["s_sysmsg"] = "���¤Τʤ������Ǥ���<br>ͷ�Фʤ��ǲ�������";
            header('Location: ' . H_WEB_HOST . EMP_MENU);
            exit();
        }
    }
    break;
case FUNC_CONFUSERINFO; // 7
    if ($_SESSION['Auth'] < AUTH_LEBEL2) {
        if ($_SESSION['User_ID'] != '970268' && $_SESSION['User_ID'] != '300551') {
            $_SESSION["s_sysmsg"] = "���¤Τʤ������Ǥ���<br>ͷ�Фʤ��ǲ�������";
            header('Location: ' . H_WEB_HOST . EMP_MENU);
            exit();
        }
    }
    break;
case FUNC_ADMINUSERINFO;    // 8
    if ($_SESSION['Auth'] < AUTH_LEBEL2) {
        if ($_SESSION['User_ID'] != '970268' && $_SESSION['User_ID'] != '300551') {
            $_SESSION["s_sysmsg"] = "���¤Τʤ������Ǥ���<br>ͷ�Фʤ��ǲ�������";
            header('Location: ' . H_WEB_HOST . EMP_MENU);
            exit();
        }
    }
    break;
case FUNC_CHGRECEIVE;   // 10
    if ($_SESSION['Auth'] < AUTH_LEBEL2) {
        if ($_SESSION['User_ID'] != '970268' && $_SESSION['User_ID'] != '300551') {
            $_SESSION["s_sysmsg"] = "���¤Τʤ������Ǥ���<br>ͷ�Фʤ��ǲ�������";
            header('Location: ' . H_WEB_HOST . EMP_MENU);
            exit();
        }
    }
    break;
case FUNC_CHGCAPACITY;  // 11
    if ($_SESSION['Auth'] < AUTH_LEBEL2) {
        if ($_SESSION['User_ID'] != '970268' && $_SESSION['User_ID'] != '300551') {
            $_SESSION["s_sysmsg"] = "���¤Τʤ������Ǥ���<br>ͷ�Фʤ��ǲ�������";
            header('Location: ' . H_WEB_HOST . EMP_MENU);
            exit();
        }
    }
    break;
case FUNC_ADDPHOLYDAY;  // 15
    if ($_SESSION['Auth'] < AUTH_LEBEL2) {
        if ($_SESSION['User_ID'] != '970227' && $_SESSION['User_ID'] != '015806') {
            $_SESSION["s_sysmsg"] = "���¤Τʤ������Ǥ���<br>ͷ�Фʤ��ǲ�������";
            header('Location: ' . H_WEB_HOST . EMP_MENU);
            exit();
        }
    }
    break;
case FUNC_HOLYDAYREGIST;  // 16
    if ($_SESSION['Auth'] < AUTH_LEBEL2) {
        if ($_SESSION['User_ID'] != '970227' && $_SESSION['User_ID'] != '015806') {
            $_SESSION["s_sysmsg"] = "���¤Τʤ������Ǥ���<br>ͷ�Фʤ��ǲ�������";
            header('Location: ' . H_WEB_HOST . EMP_MENU);
            exit();
        }
    }
case FUNC_RECIDREGIST;  // 12
    if ($_SESSION['Auth'] < AUTH_LEBEL2) {
        if ($_SESSION['User_ID'] != '970268' && $_SESSION['User_ID'] != '970227' && $_SESSION['User_ID'] != '015806' && $_SESSION['User_ID'] != '300551') {
            $_SESSION["s_sysmsg"] = "���¤Τʤ������Ǥ���<br>ͷ�Фʤ��ǲ�������";
            header('Location: ' . H_WEB_HOST . EMP_MENU);
            exit();
        }
    }
    break;
case FUNC_CAPIDREGIST;  // 13
    if ($_SESSION['Auth'] < AUTH_LEBEL2) {
        if ($_SESSION['User_ID'] != '970268' && $_SESSION['User_ID'] != '300551') {
            $_SESSION["s_sysmsg"] = "���¤Τʤ������Ǥ���<br>ͷ�Фʤ��ǲ�������";
            header('Location: ' . H_WEB_HOST . EMP_MENU);
            exit();
        }
    }
    break;
case FUNC_CHGINDICATE;  // 14
    if ($_SESSION['Auth'] < AUTH_LEBEL2) {
        if ($_SESSION['User_ID'] != '970268' && $_SESSION['User_ID'] != '300551') {
            $_SESSION["s_sysmsg"] = "���¤Τʤ������Ǥ���<br>ͷ�Фʤ��ǲ�������";
            header('Location: ' . H_WEB_HOST . EMP_MENU);
            exit();
        }
    }
    break;
}

/*  $file = IND_IMG . $ckUserid . ".gif";
if(file_exists($file))
    unlink($file); */

/////////// HTML Header ����Ϥ��ƥ���å��������
$menu->out_html_header();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv='expires' content='0'>
<meta http-equiv='Pragma' content='no-cache'>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<title><?php echo $menu->out_title()?></title>
<?php echo $menu->out_site_java()?>
<?php echo $menu->out_css()?>

<style type='text/css'>
<!--
.listScroll{
    /*�ơ��֥�β�����250px�ʾ�ξ�祹������*/
    //width: 100%;
    /*�ơ��֥�ν�����100px�ʾ�ξ�祹������*/
    height: 100%;
    /*�ĥ�������*/
    overflow: auto;
    /*����������*/
    overflow-x: hidden;
}
.listScroll2{
    /*�ơ��֥�β�����250px�ʾ�ξ�祹������*/
    /*
    width: 90vw;
    max-width: 600px;
    */
    width: 100%;
    /*�ơ��֥�ν�����100px�ʾ�ξ�祹������*/
    height: 100%;
    /*�ĥ�������*/
    overflow: auto;
    /*����������*/
    overflow-x: auto;
    width: calc(100vw - 100px);
}
.vertical-scroll-table{
    color: #5e5e5e;
    max-height: 120px;
    overflow: auto;
    overflow-x: hidden;
}
.pt9 {
    font-size: 10pt;
    /*font-family: monospace;*/
    /*color: black;*/
    }
.left-font {
    font-size: 7.5pt;
    font-family: monospace;
    color: blue;
    }
.left-font-bla {
    font-size: 7.5pt;
    font-family: monospace;
    color: black;
    }
.left-font-m {
    font-size: 9pt;
    font-family: monospace;
    color: blue;
    }
.left-font-m-bla {
    font-size: 9pt;
    font-family: monospace;
    color: black;
    }
.nasiji {
    <?php if (date('Hi') < '1200') {    // ������ʤ� ?>
    background-image: url(<?php echo IMG?>nasiji_apple.gif);
    <?php } else {  // ���ʤ� ?>
    background-image: url(<?php echo IMG?>nasiji_silver.gif);
    <?php } ?>
    background-repeat: repeat;
}
input.bg {
    background-image: url(<?php echo IMG?>border_silver_button.gif);
    background-repeat: repeat;
    color: blue;
}
select {
    background-color: #003e7c;
    color: white;
}}
-->
</style>
<script type='text/javascript' src='confirm.js'></script>
<script type='text/javascript' language='JavaScript'>
<!--
/* ������ϥե�����Υ�����Ȥ˥ե������������� */
function set_focus() {
    document.stati_form.stati.focus();      // ������ϥե����ब������ϥ����Ȥ򳰤�
    // document.stati_form.stati.select();
}
function win_open(url, w, h)
{
    if (!w) w = 256;
    if (!h) h = 382;
    var left = (screen.availWidth  - w) / 2;
    var top  = (screen.availHeight - h) / 2;
    window.open(url, '', 'width='+w+',height='+h+',scrollbars=no,status=no,toolbar=no,location=no,menubar=no,resizable=yes,top='+top+',left='+left);
}
// -->
</script>
</head>
<body bgcolor='#ffffff' text='#000000' onLoad='set_focus()' style='overflow: hidden;'>

<!--
<body bgcolor='#ffffff' text='#000000' onLoad='set_focus()'>
-->
<?php echo $menu->out_title_border()?>
<table width='100%' height='90%' border='0' cellspacing='0' cellpadding='0' style='border-left:2.0pt solid #ffffff;'><tr>

<!-- left view  -->
<td valign='top' width='20%'>
<script type='text/javascript' language='Javascript'>
<!--
    str=navigator.appName.toUpperCase();
    if(str.indexOf("NETSCAPE")>=0) document.write("<table width='100%' height=1950 bgcolor='#003e7c' cellpadding='10' class='nasiji'>");
    if(str.indexOf("EXPLORER")>=0) document.write("<table width='100%' height='100%' bgcolor='#003e7c' cellpadding='10' class='nasiji'>");
//-->
</script>
<?php
    echo("<noscript><table width='100%' height='100%' bgcolor='#003e7c' cellpadding='10' class='nasiji'></noscript>");
    $_SESSION['lookupkey']=StripSlashes($_SESSION['lookupkey']);
?>
    
    <tr><td valign='top'>
    <div class='listScroll'>
    <p align='center'><img width=190 height=34 src='<?php echo IMG?>t_nitto_logo1.gif' border=0></p>

    <!-- function -->

    <center>
    <table width='100%'>
    <form method="post" action="emp_menu.php?func=<?php echo(FUNC_MINEINFO) ?>">
    <tr><td align='center'>
        <input type='hidden' name='func' value='<?php echo(FUNC_MINEINFO) ?>'>
<?php   
        if ($request->get('func') == FUNC_MINEINFO) {
            echo "    <input class='bg' type='submit' name='func_button' value='���ʾ���ɽ��' style='color:red;'>\n";
        } else {
            echo "    <input class='bg' type='submit' name='func_button' value='���ʾ���ɽ��'>\n";
        }
?>
    </td></tr>
    </form>

<?php   
    if ($_SESSION['Auth'] >= AUTH_LEBEL2 || $_SESSION['User_ID'] == '970268' || $_SESSION['User_ID'] == '300551') {
?>
        <form method="post" action="emp_menu.php?func=<?php echo(FUNC_NEWUSER) ?>">
        <tr><td align='center'>
            <input type='hidden' name='func' value='<?php echo(FUNC_NEWUSER) ?>'>
<?php
            if ($request->get('func') == FUNC_NEWUSER || $request->get('func') == FUNC_CONFNEWUSER) {
                echo "    <input class='bg' type='submit' value='�Ұ�������Ͽ' style='color:red;'>\n";
            } else {
                echo "    <input class='bg' type='submit' value='�Ұ�������Ͽ'>\n";
            }
?>
        </td></tr>
        </form>
<?php
    }
?>
    <form name='stati_form' method='post' action='emp_menu.php?func=<?php echo(FUNC_STATISTIC) ?>'>
        <tr>
        <td align='center'>
            <input type='hidden' name='func' value='<?php echo(FUNC_STATISTIC) ?>'>
<?php
            if ($request->get('func') == FUNC_STATISTIC) {
                echo "    <input class='bg' type='submit' name='stati' value='�Ұ����׾���' style='color:red;'>\n";
            } else {
                echo "    <input class='bg' type='submit' name='stati' value='�Ұ����׾���'>\n";
            }
?>
        </td>
        </tr>
    </form>
    </table>
        </center>
        <noscript><p><font size=-1 color="#ff7e00">JavaScript��̵���ˤʤäƤ��ޤ���ͭ���ˤ��Ƥ����ܥ����ƥ�����Ѥ���������</font></p></noscript>
        <hr style='border-color:white;'>

    <!-- lookup func -->

        <table width="100%">
        <?php
        //if ($_SESSION['User_ID'] == '300144' || $_SESSION['User_ID'] == '300055' || $_SESSION['User_ID'] == '010472' || $_SESSION['User_ID'] == '015806') {
        if (getCheckAuthority(40) || getCheckAuthority(41) || getCheckAuthority(42) || getCheckAuthority(43) || getCheckAuthority(44) || getCheckAuthority(45) || getCheckAuthority(46) || getCheckAuthority(47) || getCheckAuthority(48) || getCheckAuthority(49) || getCheckAuthority(50) || getCheckAuthority(51) || getCheckAuthority(52) || getCheckAuthority(55)) {
        ?>
        <form method="post" action="emp_menu.php?func=<?php echo(FUNC_LOOKUP) ?>" onSubmit="return chkLookupTermsY(this)">
        <?php
        } else {
        ?>
        <form method="post" action="emp_menu.php?func=<?php echo(FUNC_LOOKUP) ?>" onSubmit="return chkLookupTerms(this)">
        <?php
        }
        ?>
        <input type='hidden' name='func' value='<?php echo(FUNC_LOOKUP) ?>'>
            <tr>
                <td colspan='2' align='center'>
                    <?php if ($request->get('func') == FUNC_LOOKUP) { ?>
                    <input class='bg' type='submit' name='lookup' value='�����μ¹�' style='color:red;'>
                    <?php } else { ?>
                    <input class='bg' type='submit' name='lookup' value='�����μ¹�'>
                    <?php } ?>
                </td>
            </tr>
            <tr>
                <td colspan='2' align='center' class='left-font-m-bla'>���ܾ���</td>
            </tr>
            <tr><td align="left" nowrap class='left-font'>��������</td>
                <td align="right"><select name="lookupkind">
                    <option <?php if($_SESSION['lookupkind']==KIND_USER) echo("selected"); ?>
                         value=<?php echo(KIND_USER); ?>>���Ȱ�����
<?php
    if($_SESSION['Auth'] >= AUTH_LEBEL1 || $_SESSION['User_ID'] == '970268' || $_SESSION['User_ID'] == '300551'){
?>
                    <option <?php if($_SESSION['lookupkind']==KIND_TRAINING) echo("selected"); ?>
                         value=<?php echo(KIND_TRAINING); ?>>���鷱����Ͽ
<?php
    }
?>
<?php
    if($_SESSION['Auth'] >= AUTH_LEBEL2 || $_SESSION['User_ID'] == '970268' || $_SESSION['User_ID'] == '300551'){
?>
                    <option <?php if($_SESSION['lookupkind']==KIND_ADDRESS) echo("selected"); ?>
                         value=<?php echo(KIND_ADDRESS); ?>>�������
<?php
    }
?>
                    </select>
                </td>
            </tr>
            <tr><td align="left" nowrap class='left-font'>������ˡ</td>
                <td align="right"><select name="lookupkeykind">
                <option <?php if($_SESSION['lookupkeykind'] == KIND_DISABLE) echo("selected"); ?> value=<?php echo(KIND_DISABLE); ?>>����̵��
                <option <?php if($_SESSION['lookupkeykind'] == KIND_USERID) echo("selected"); ?> value=<?php echo(KIND_USERID); ?>>�Ұ�No
                <option <?php if($_SESSION['lookupkeykind'] == KIND_FULLNAME) echo("selected"); ?> value=<?php echo(KIND_FULLNAME); ?>>�ե�͡���
                <option <?php if($_SESSION['lookupkeykind'] == KIND_LASTNAME) echo("selected"); ?> value=<?php echo(KIND_LASTNAME); ?>>��
                <option <?php if($_SESSION['lookupkeykind'] == KIND_FASTNAME) echo("selected"); ?> value=<?php echo(KIND_FASTNAME); ?>>̾
                <?php
                    if(getCheckAuthority(54)){
                ?>
                        <option <?php if($_SESSION['lookupkeykind'] == KIND_ABSENCE) echo("selected"); ?> value=<?php echo(KIND_ABSENCE); ?>>�Ժ߼�
                <?php
                    }
                ?>
                <option <?php if($_SESSION['lookupkeykind'] == KIND_AGE) echo("selected"); ?> value=<?php echo(KIND_AGE); ?>>ǯ���
                </select>
                </td>
            </tr>
            <tr>
                <td align="left" class='left-font'>��������</td>
                <td align="right">
                <input type="text" name="lookupkey" size=18 value='<?php echo($_SESSION['lookupkey']); ?>'>
                </td>
            </tr>
            <tr>
                <td align="left" class='left-font'>��°</td>
                <td align="right">
                <select name="lookupsection">
                <option <?php if ($_SESSION['lookupsection'] == '-2') echo 'selected '; ?>value='-2'>�и���������
                <option <?php if ($_SESSION['lookupsection'] == KIND_DISABLE) echo 'selected '; echo "value='" . KIND_DISABLE ."'"; ?>>���٤�
<?php
    $query="select * from section_master where sflg=1 order by sid asc";
    $res=array();
    if($rows=getResult($query,$res)){
        for($i=0;$i<$rows;$i++){
            echo("<option ");
            if($_SESSION['lookupsection']==$res[$i]["sid"])
                echo("selected ");
            echo("value=" . $res[$i]["sid"] . ">" . mb_substr(trim($res[$i]['section_name']), -10) . "\n");
        }
    }
?>
                </select>
                </td>
            </tr>
            <tr>
                <td align="left" class='left-font'>����</td>
                <td align="right"><select name="lookupposition">
                <option value=<?php echo(KIND_DISABLE); ?>>���٤�
                <option <?php if($_SESSION['lookupposition'] == KIND_EMPLOYEE) echo("selected"); ?> value=<?php echo(KIND_EMPLOYEE); ?>>�Ұ�����
                <option <?php if($_SESSION['lookupposition'] == KIND_PARTTIME) echo("selected"); ?> value=<?php echo(KIND_PARTTIME); ?>>�ѡ�������
                <option <?php if($_SESSION['lookupposition'] == KIND_MANAGE) echo("selected"); ?> value=<?php echo(KIND_MANAGE); ?>>��Ĺ�����ʾ�
<?php
    $query="select * from position_master where pflg=1 order by pid asc";
    $res=array();
    if($rows=getResult($query,$res)){
        for($i=0;$i<$rows;$i++){
            echo("<option ");
            if($_SESSION['lookupposition']==$res[$i]["pid"])
                echo("selected ");
            echo("value=" . $res[$i]["pid"] . ">" . $res[$i]["position_name"] . "\n");
        }
    }
?>
                </select></td>
            </tr>
            <tr>
                <td align="left" class='left-font'>����ǯ��</td>
                <td align="right"><select name="lookupentry">
                <option value=<?php echo(KIND_DISABLE); ?>>���٤�
<?php
    $now=getdate(time());
    $thisyear=$now["year"];
    for($i=1960;$i<=$thisyear;$i++){
        echo("<option ");
        if($_SESSION['lookupentry'] == $i)
            echo("selected ");
        echo("value=" . $i . ">" . $i . "ǯ��\n");
    }
?>
    </table>
    <table width='100%'>
    <hr style='border-color:white;'>
<?php
    //if ($_SESSION['User_ID'] == '300144' || $_SESSION['User_ID'] == '300055' || $_SESSION['User_ID'] == '010472' || $_SESSION['User_ID'] == '015806') {
    if (getCheckAuthority(40) || getCheckAuthority(41) || getCheckAuthority(42) || getCheckAuthority(43) || getCheckAuthority(44) || getCheckAuthority(45) || getCheckAuthority(46) || getCheckAuthority(47) || getCheckAuthority(48) || getCheckAuthority(49) || getCheckAuthority(50) || getCheckAuthority(51) || getCheckAuthority(52) || getCheckAuthority(55)) {
?>
            <tr>
                <td colspan='2' align='center' class='left-font-m-bla'>ͭ�����</td>
            </tr>
            <tr><td align="left" nowrap class='left-font'>������ˡ</td>
                <td align="right"><select name="lookupyukyukind">
                <option <?php if($_SESSION['lookupyukyukind'] == KIND_DISABLE) echo("selected"); ?> value=<?php echo(KIND_DISABLE); ?>>����̵��
                <option <?php if($_SESSION['lookupyukyukind'] == KIND_DAYUP) echo("selected"); ?> value=<?php echo(KIND_DAYUP); ?>>���������ʾ����
                <option <?php if($_SESSION['lookupyukyukind'] == KIND_DAYDOWN) echo("selected"); ?> value=<?php echo(KIND_DAYDOWN); ?>>��������̤������
                <option <?php if($_SESSION['lookupyukyukind'] == KIND_PERUP) echo("selected"); ?> value=<?php echo(KIND_PERUP); ?>>�����ʾ����
                <option <?php if($_SESSION['lookupyukyukind'] == KIND_PERDOWN) echo("selected"); ?> value=<?php echo(KIND_PERDOWN); ?>>�����ʲ�����
                </select>
                </td>
            </tr>
            <tr>
                <td align="left" class='left-font'>��������</td>
                <td align="right">
                <input type="text" name="lookupyukyu" size=18 value='<?php echo($_SESSION['lookupyukyu']); ?>'>
                </td>
            </tr>
<?php
    } else {
        $_SESSION['lookupyukyukind'] = KIND_DISABLE;
    }
?>
                </select></td>
            </tr>
            </table>
            <table width='100%'>
            <hr style='border-color:white;'>
<?php
    //if ($_SESSION['User_ID'] == '300144' || $_SESSION['User_ID'] == '300055' || $_SESSION['User_ID'] == '010472' || $_SESSION['User_ID'] == '015806') {
    if (getCheckAuthority(40) || getCheckAuthority(41) || getCheckAuthority(42) || getCheckAuthority(43) || getCheckAuthority(44) || getCheckAuthority(45) || getCheckAuthority(46) || getCheckAuthority(47) || getCheckAuthority(48) || getCheckAuthority(49) || getCheckAuthority(50) || getCheckAuthority(51) || getCheckAuthority(52) || getCheckAuthority(55)) {
?>
            <tr>
                <td colspan='2' align='center' class='left-font-m-bla'>ͭ��5������<font color='red'>��ͥ��</font></td>
            </tr>
            <tr><td align="left" nowrap class='left-font'>������ˡ</td>
                <td align="right"><select name="lookupyukyufive">
                <option <?php if($_SESSION['lookupyukyufive'] == KIND_DISABLE) echo("selected"); ?> value=<?php echo(KIND_DISABLE); ?>>����̵��
                <option <?php if($_SESSION['lookupyukyufive'] == KIND_DAYUP) echo("selected"); ?> value=<?php echo(KIND_DAYUP); ?>>���������ʾ����
                <option <?php if($_SESSION['lookupyukyufive'] == KIND_DAYDOWN) echo("selected"); ?> value=<?php echo(KIND_DAYDOWN); ?>>��������̤������
                </select>
                </td>
            </tr>
            <tr>
                <td align="left" class='left-font'>��������</td>
                <td align="right">
                <input type="text" name="lookupyukyuf" size=18 value='<?php echo($_SESSION['lookupyukyuf']); ?>'>
                </td>
            </tr>
<?php
    } else {
        $_SESSION['lookupyukyufive'] = KIND_DISABLE;
    }
?>
                </select></td>
            </tr>
            </table>
            <table width='100%'>
            <hr style='border-color:white;'>
            <tr>
                <td colspan='2' align='center' class='left-font-m-bla'>�񡡳�</td>
            </tr>
            <tr>
                <td colspan=2 align="right"><select name="lookupcapacity">
                <option value=<?php echo(KIND_DISABLE); ?>>�������̵��
<?php
    $query="select * from capacity_master where cflg=1 order by cid asc";
    $res=array();
    if($rows=getResult($query,$res)){
        for($i=0;$i<$rows;$i++){
            echo("<option ");
            if($_SESSION['lookupcapacity'] == $res[$i]["cid"])
                echo("selected ");
            echo("value=" . $res[$i]["cid"] . ">" . substr($res[$i]["capacity_name"],0,24) . "\n");
        }
    }
?>
                </select>
                </td>
            </tr>
            <tr>
                <td colspan='2' align='center' class='left-font-m-bla'>������</td>
            </tr>
            <tr><td colspan=2 align="right"><select name="lookupreceive">
                <option value=<?php echo(KIND_DISABLE); ?>>�������̵��
<?php
    $query="select * from receive_master where rflg=1 order by rid asc";
    $res=array();
    if($rows=getResult($query,$res)){
        for($i=0;$i<$rows;$i++){
            echo("<option ");
            if($_SESSION['lookupreceive']==$res[$i]["rid"])
                echo("selected ");
            echo("value=" . $res[$i]["rid"] . ">" . substr($res[$i]["receive_name"],0,24) . "\n");
        }
    }
?>
                </select>
                </td>
            </tr>
            <tr>
                <td colspan='2' align='center'>
                    <?php if ($request->get('func') == FUNC_LOOKUP) { ?>
                    <input class='bg' type='submit' name='lookup' value='�����μ¹�' style='color:red;'>
                    <?php } else { ?>
                    <input class='bg' type='submit' name='lookup' value='�����μ¹�'>
                    <?php } ?>
                </td>
            </tr>
        </form>
        </table>
        <hr style='border-color:white;'>

        <center>
        <table width='100%'>
        <form method='post' action='emp_menu.php?func=<?php echo(FUNC_RETIREINFO) ?>'>
        <tr><td align='center'>
<?php
    if ($request->get('func') == FUNC_RETIREINFO) {
        echo "<input class='bg' type='submit' value='�࿦�԰���ɽ' style='color:red;'>\n";
    } else {
        echo "<input class='bg' type='submit' value='�࿦�԰���ɽ'>\n";
    }
?>
        </td></tr>
        </form>
<?php   
    if ($_SESSION['Auth'] >= AUTH_LEBEL3 || $_SESSION['User_ID'] == '970268' || $_SESSION['User_ID'] == '300551'){
?>
        <form method='post' action='emp_menu.php?func=<?php echo(FUNC_CHGRECEIVE) ?>'>
        <tr><td align='center'>
<?php   
        if ($request->get('func') == FUNC_CHGRECEIVE || $request->get('func') == FUNC_RECIDREGIST) {
            echo "<input class='bg' type='submit' value='��������Ͽ' style='color:red;'>\n";
        } else {
            echo "<input class='bg' type='submit' value='��������Ͽ'>\n";
        }
?>  
        </td></tr>
        </form>
        <form method='post' action='emp_menu.php?func=<?php echo(FUNC_CHGCAPACITY) ?>'>
        <tr><td align='center'>
<?php
        if ($request->get('func') == FUNC_CHGCAPACITY || $request->get('func') == FUNC_CAPIDREGIST) {
            echo "<input class='bg' type='submit' value='��ʰ����Ͽ' style='color:red;'>\n";
        } else {
            echo "<input class='bg' type='submit' value='��ʰ����Ͽ'>\n";
        }
?>
        </td></tr>
        </form>
<?php   
    }
    if ($_SESSION['Auth'] >= AUTH_LEBEL3 || $_SESSION['User_ID'] == '970227' || $_SESSION['User_ID'] == '015806'){
?>
        <form method='post' action='emp_menu.php?func=<?php echo(FUNC_ADDPHOLYDAY) ?>'>
        <tr><td align='center'>
<?php
        if ($request->get('func') == FUNC_ADDPHOLYDAY || $request->get('func') == FUNC_HOLYDAYREGIST) {
            echo "<input class='bg' type='submit' value='�ײ�ͭ����Ͽ' style='color:red;'>\n";
        } else {
            echo "<input class='bg' type='submit' value='�ײ�ͭ����Ͽ'>\n";
        }
?>
        </td></tr>
        </form>
<?php
    }
    if ($_SESSION['Auth'] >= AUTH_LEBEL2 || $_SESSION['User_ID'] == '970268' || $_SESSION['User_ID'] == '300551') {
?>
        <form method='post' action='emp_menu.php?func=<?php echo(FUNC_CHGINDICATE) ?>'>
        <tr><td align='center'>
<?php
        if ($request->get('func') == FUNC_CHGINDICATE) {
            echo "<input class='bg' type='submit' value='ɽ����������' style='color:red;'>\n";
        } else {
            echo "<input class='bg' type='submit' value='ɽ����������'>\n";
        }
?>
        </td></tr>
        </form>
<?php
    }
?>
        <form method='post' action='emp_menu.php?func=<?php echo(FUNC_MAIL) ?>'>
        <tr><td align='center'>
<?php
        if ($request->get('func') == FUNC_MAIL) {
            echo "<input class='bg' type='submit' value='�᡼�륢�ɥ쥹' style='color:red;'>\n";
        } else {
            echo "<input class='bg' type='submit' value='�᡼�륢�ɥ쥹'>\n";
        }
?>
        </td></tr>
        </form>
        </table>
        <table width='100%'>
        <hr style='border-color:white;'>
<?php
        if (getCheckAuthority(40) || getCheckAuthority(41) || getCheckAuthority(42) || getCheckAuthority(43) || getCheckAuthority(44) || getCheckAuthority(45) || getCheckAuthority(46) || getCheckAuthority(47) || getCheckAuthority(48) || getCheckAuthority(49) || getCheckAuthority(50) || getCheckAuthority(51) || getCheckAuthority(52) || getCheckAuthority(55)) {
?>
        <form method='post' target='_blank' action='print/print_emp_branch.php?emp_name=print_yukyu_five_list&yukyulist=<?php echo($_SESSION['yukyulist']) ?>&fivesection=<?php echo($_SESSION['fivesection']) ?>' onSubmit="return chkLookupFive(this)">
        <tr>
            <td colspan='2' align='center' class='left-font-m-bla'>ͭ�������Ģ</td>
        </tr>
        <tr>
            <td align="center" class='left-font'>ǯ��(�㡧2019)</td>
        </tr>
        <tr>
            <td align="center">
            <input type="text" name="yukyulist" size=18 value='<?php echo($_SESSION['yukyulist']); ?>'>
            </td>
        </tr>
        <tr>
            <td align="center" class='left-font-m'>��°<BR>
            <select name='fivesection'>
                    <?php 
                    if (getCheckAuthority(28)) {
                    ?>
                        <option <?php if ($fivesection == '-1') echo 'selected '; ?>value='-1'>����
                        <option <?php if ($fivesection == '31') echo 'selected '; ?>value='31'>�и�
                    <?php
                    }
                    if (getCheckAuthority(29)) {
                    ?>
                        <option <?php if ($fivesection == '-1') echo 'selected '; ?>value='-1'>����
                        <option <?php if ($fivesection == '31') echo 'selected '; ?>value='31'>�и�
                    <?php
                    }
                    ?>
                    <?php echo getTargetSectionvalues($fivesection) ?>
            </select>
            </td>
        </tr>
        <tr>
            <td align='center'>
            <?php
            if ($request->get('func') == FUNC_FIVE) {
                echo "<input class='bg' type='submit' value='ͭ�������Ģ' style='color:red;'>\n";
            } else {
                echo "<input class='bg' type='submit' value='ͭ�������Ģ'>\n";
            }
?>
            </td>
        </tr>
        </form>
<?php
        }
?>
        </table>
        </center>
        </td></tr>
    </table>
    </td>
</div>
<!-- right view -->

<td valign="top">
<div class='listScroll2'>
<?php
    if ($request->get('func') == '')
        // include("view_default.php");
        include("view_user_statistic.php");         // 21
    elseif ($request->get('func') == FUNC_MINEINFO)
        include("view_mineinfo.php");               // 1
    elseif ($request->get('func') == FUNC_NEWUSER)
        include("view_userinfo_get.php");           // 2
    elseif ($request->get('func')==FUNC_CONFNEWUSER)
        include("view_userinfo_conf.php");          // 3
    // elseif ($request->get('func')==FUNC_DBADMIN)
    //     include("view_admindb.php");                // 4
    elseif ($request->get('func')==FUNC_LOOKUP)
        include("view_userinfo.php");               // 5
    elseif ($request->get('func')==FUNC_CHGUSERINFO)
        include("view_userinfo_chg.php");           // 6
    elseif ($request->get('func')==FUNC_CONFUSERINFO)
        include("view_userinfo_chgconf.php");       // 7
    elseif ($request->get('func')==FUNC_ADMINUSERINFO)
        include("view_userinfo_chgadmin.php");      // 8
    elseif ($request->get('func')==FUNC_RETIREINFO)
        include("view_userinfo_retire.php");        // 9
    elseif ($request->get('func')==FUNC_CHGRECEIVE)
        include("view_userinfo_chgreceive.php");    // 10
    elseif ($request->get('func')==FUNC_CHGCAPACITY)
        include("view_userinfo_chgcapacity.php");   // 11
    elseif ($request->get('func')==FUNC_ADDPHOLYDAY)
        include("view_userinfo_addpholyday.php");   // 15
    elseif ($request->get('func')==FUNC_RECIDREGIST)
        include("view_recid_regist.php");           // 12
    elseif ($request->get('func')==FUNC_CAPIDREGIST)
        include("view_capid_regist.php");           // 13
    elseif ($request->get('func')==FUNC_HOLYDAYREGIST)
        include("view_holyday_regist.php");         // 16
    elseif ($request->get('func')==FUNC_CHGINDICATE)
        include("view_select_indicate.php");        // 14
    elseif ($request->get('func')==FUNC_STATISTIC)
        include("view_user_statistic.php");         // 21
    elseif ($request->get('func')==FUNC_MAIL)
        include("mail/mailAddress_Main.php");       // 22
    elseif ($request->get('func')==FUNC_RECIDREGISTCHK)
        include("view_recid_regist_check.php");     // 23
    elseif ($request->get('func')==FUNC_CAPIDREGISTCHK)
        include("view_capid_regist_check.php");     // 24
    elseif ($request->get('func')==FUNC_HOLYDAYREGISTCHK)
        include("view_holyday_regist_check.php");   // 25
    elseif ($request->get('func')==FUNC_FIVE)
        include("print/print_yukyu_five_list.php");   // 26
    else
        include("view_default.php");
?>
</div>
</td>
</tr></table>
</body>
<?php
if ($request->get('func')==FUNC_CHGRECEIVE || $request->get('func')==FUNC_CHGCAPACITY || $request->get('func')==FUNC_ADDPHOLYDAY) {
?>
    <?php echo $menu->out_alert_java(false)?>
<?php
} else {
?>
    <?php echo $menu->out_alert_java()?>
<?php
}
?>
</html>
<?php
ob_end_flush();                 // ���ϥХåե���gzip���� END
?>
<?php
////// �о������HTML <select> option �ν���
function getTargetSectionvalues($fivesection)
    {
        // �����
        $option = "\n";
        // ��������
        if (getCheckAuthority(28)) {
            //$query="select * from section_master where sflg=1 and sid<>99 and sid<>90 and sid<>80 and sid<>38 and sid<>31 and sid<>17 and sid<>9 and sid<>8 order by sid asc";
            $query="select * from section_master where sflg=1 and sid<>90 and sid<>95 and sid<>80 and sid<>31 order by sid asc";
            $res=array();
            if($rows=getResult($query,$res)){
                for($i=0;$i<$rows;$i++){
                    if($fivesection == $res[$i]["sid"]) {
                        $option .= "<option value='{$res[$i]['sid']}' selected>" . mb_substr(trim($res[$i]['section_name']), -10). "</option>\n";
                    } else {
                        $option .= "<option value='{$res[$i]['sid']}'>" . mb_substr(trim($res[$i]['section_name']), -10) . "</option>\n";
                    }
                }
            }
        } else if(getCheckAuthority(29)) {    // ����Ĺ��������Ĺ�����Ƥ�����Ǥ���
            $query="select * from section_master where sflg=1 and sid<>90 and sid<>95 and sid<>80 and sid<>31 order by sid asc";
            $res=array();
            if($rows=getResult($query,$res)){
                for($i=0;$i<$rows;$i++){
                    if($fivesection == $res[$i]["sid"]) {
                        $option .= "<option value='{$res[$i]['sid']}' selected>" . mb_substr(trim($res[$i]['section_name']), -10). "</option>\n";
                    } else {
                        $option .= "<option value='{$res[$i]['sid']}'>" . mb_substr(trim($res[$i]['section_name']), -10) . "</option>\n";
                    }
                }
            }
        } else if(getCheckAuthority(42)) {    // �������ϵ������Τ߱����Ǥ���
            //$query="select * from section_master where sflg=1 and sid<>99 and sid<>90 and sid<>80 and sid<>38 and sid<>31 and sid<>17 and sid<>9 and sid<>8 order by sid asc";
            $sid_where="(sid='38' or sid='18' or sid='4')";
            $query="select * from section_master where sflg=1 and {$sid_where} order by sid asc";
            $res=array();
            if($rows=getResult($query,$res)){
                for($i=0;$i<$rows;$i++){
                    if($fivesection == $res[$i]["sid"]) {
                        $option .= "<option value='{$res[$i]['sid']}' selected>" . mb_substr(trim($res[$i]['section_name']), -10). "</option>\n";
                    } else {
                        $option .= "<option value='{$res[$i]['sid']}'>" . mb_substr(trim($res[$i]['section_name']), -10) . "</option>\n";
                    }
                }
            }
        } else if(getCheckAuthority(43)) {    // ���������������Τ߱����Ǥ���
            //$query="select * from section_master where sflg=1 and sid<>99 and sid<>90 and sid<>80 and sid<>38 and sid<>31 and sid<>17 and sid<>9 and sid<>8 order by sid asc";
            $sid_where="(sid='8' or sid='32' or sid='2' or sid='3')";
            $query="select * from section_master where sflg=1 and {$sid_where} order by sid asc";
            $res=array();
            if($rows=getResult($query,$res)){
                for($i=0;$i<$rows;$i++){
                    if($fivesection == $res[$i]["sid"]) {
                        $option .= "<option value='{$res[$i]['sid']}' selected>" . mb_substr(trim($res[$i]['section_name']), -10). "</option>\n";
                    } else {
                        $option .= "<option value='{$res[$i]['sid']}'>" . mb_substr(trim($res[$i]['section_name']), -10) . "</option>\n";
                    }
                }
            }
        } else if(getCheckAuthority(55)) {    // ��¤������¤���Τ߱����Ǥ���
            //$query="select * from section_master where sflg=1 and sid<>99 and sid<>90 and sid<>80 and sid<>38 and sid<>31 and sid<>17 and sid<>9 and sid<>8 order by sid asc";
            $sid_where="(sid='17' or sid='34' or sid='35')";
            $query="select * from section_master where sflg=1 and {$sid_where} order by sid asc";
            $res=array();
            if($rows=getResult($query,$res)){
                for($i=0;$i<$rows;$i++){
                    if($fivesection == $res[$i]["sid"]) {
                        $option .= "<option value='{$res[$i]['sid']}' selected>" . mb_substr(trim($res[$i]['section_name']), -10). "</option>\n";
                    } else {
                        $option .= "<option value='{$res[$i]['sid']}'>" . mb_substr(trim($res[$i]['section_name']), -10) . "</option>\n";
                    }
                }
            }
        } else {
        // ������Τ߾Ȳ� �Ʋݤβ�Ĺ�μҰ��ֹ�������
            if ($_SESSION['User_ID'] == '300349') {    // ���ʴ�����   ¼���Ĺ����
                $sid=19;    
            } else if ($_SESSION['User_ID'] == '012980') {    // �ʾڲ�   ���ܲ�Ĺ
                $sid=18;
            } else if ($_SESSION['User_ID'] == '018040') {    // ��¤���� �����Ų�Ĺ
                $sid=34;
            } else if ($_SESSION['User_ID'] == '015202') {    // ��¤���� �ⶶ��Ĺ
                $sid=35;
            } else if ($_SESSION['User_ID'] == '016713' || $_SESSION['User_ID'] == '016080') {    // ���ɲ�   �滳��Ĺ���� ������Ĺ
                $sid=32;
            } else if ($_SESSION['User_ID'] == '017850' || $_SESSION['User_ID'] == '300055') {    // ��̳��   ������Ĺ���� ��z����Ĺ����
                $sid=5;
            } else if ($_SESSION['User_ID'] == '017728') {    // ��˥���Ω��  ���Ĳ�Ĺ
                $sid=3;
            } else if ($_SESSION['User_ID'] == '017507') {    // ���ץ���Ω�� ������Ĺ
                $sid=2;
            } else if ($_SESSION['User_ID'] == '014524') {    // ���Ѳ� �����Ĺ
                $sid=4;
            }
            $query="select * from section_master where sflg=1 and sid={$sid} order by sid asc";
            $res=array();
            if($rows=getResult($query,$res)){
                $option .= "<option value='{$res[0]['sid']}' selected>" . trim($res[0]['section_name']). "</option>\n";
            }
        }
        return $option;
    }
?>
