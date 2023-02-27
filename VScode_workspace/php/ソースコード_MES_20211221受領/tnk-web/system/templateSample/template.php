<?php
//////////////////////////////////////////////////////////////////////////////
// ������������������������������������                                     //
// Copyright (C) 2004-2005 2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp //
// Changed history                                                          //
// 2004/07/15 Created  template.php                                         //
// 2003/06/30 ����Ū�˸�ľ��ɽ�����˴����֤�HTML�������������            //
//             ̾����template.php ���ѹ��� system �ǥ��쥯�ȥ�ذ�ư        //
//             �ե����륵���С��� ����.php ��Ϣư�������                   //
// 2003/09/07 <!DOC... "http://www.w3.org/TR/html4/loose.dtd"> ���ɲ�       //
// 2003/10/01 �嵭����(ɽ���������)                                      //
// 2003/10/20 <title></title>�ȥե�����Υ����ȥ�� $menu_title ������      //
// 2003/11/18 <th> pt11 �� pt10   <td> pt10b �� pt9 ���ѹ�                  //
// 2003/11/26 ��ɽ���� switch case ʸ�� center left �������ؤ����ɲ�        //
// 2003/12/12 define���줿����ǥǥ��쥯�ȥ�ȥ�˥塼����Ѥ��ƴ�������    //
//            ob_start('ob_gzhandler') ���ɲ�                               //
// 2003/12/20 $_SESSION['offset']�ϡ����Ѥ������sales_offset���ͤ��ѹ����� //
// 2004/05/12 �����ȥ�˥塼ɽ������ɽ�� �ܥ����ɲ� menu_OnOff($script)�ɲ� //
//                              GET�ˤ��page_keep�����                    //
// 2004/05/24 style sheet ��/* */��Excel�ǥ��顼�ˤʤ뤿�� php���������    //
// 2004/06/07 �꥿���󥢥ɥ쥹������������ѹ�(�ƽи������ꤷ�Ƥ���)      //
// 2004/06/10 view_user($_SESSION['User_ID']) ���˥塼�إå����β����ɲ�  //
// 2004/07/26 MenuHeader class ����Ѥ��ƶ��̥�˥塼���ڤ�ǧ���������ѹ�   //
// 2004/12/25 style='overflow:hidden;' (-xyξ��)���ɲ�                      //
// 2005/01/14 F2/F12��������뤿����б��� document.body.focus()IE�Τߤ��ɲ�//
// 2005/04/26 <div></div>��<span></span>�֥�å����Ǥ��饤��饤�����Ǥ��ѹ�//
// 2005/08/01 <script language= ��HTML4.01�Ǻ��Ѥ���Ƥ��ʤ�<script type=�� //
// 2005/08/30 template.js(extends base_class.js),template.cssʬΥ����ɸ�ಽ //
// 2005/11/07 ����ץ��SQLʸ���ѹ���E_STRICT�����顼����Ϥ��ʤ�����E_ALL��//
// 2005/11/24 <link rel='shortcut icon' href='/favicon.ico'>�ɲ�            //
// 2007/01/23 php�Υ��硼�ȥ������ѻ�(�㡩�� �� �㡩php echo)�侩�����     //
// 2007/04/21 �ǥ��쥯�ȥ��templateSampl�ذ�ư��SQLʸ��SQL98�ߴ��˽񴹤�   //
// 2007/09/07 $_POST/$_GET��$_REQUEST ���ѹ� $session->add_local�ǥڡ�������//
//            �ƽФ����Υڡ����ݻ�(̵���)���ɲ�                            //
// 2007/09/11 error_reporting �� master������ѹ�                           //
// 2007/09/18 E_ALL | E_STRICT ���ѹ�                                       //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL | E_STRICT);
// ini_set('error_reporting', E_ALL);          // E_STRICT=2048(php5) E_ALL=6143 debug ��
ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
// ini_set('zend.ze1_compatibility_mode', '1');    // zend 1.X ����ѥ� php4�θߴ��⡼��
// ini_set('implicit_flush', '0');             // echo print �� flush �����ʤ�(�٤��ʤ뤿��) CLI��
    // ���ߤ�CLI�Ǥ�default='1', SAPI�Ǥ�default='0'�ˤʤäƤ��롣CLI�ǤΤߥ�����ץȤ����ѹ�����롣
// ini_set('max_execution_time', 120);         // ����¹Ի���=120�� SAPI CGI��
ob_start('ob_gzhandler');                   // ���ϥХåե���gzip����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��

require_once ('../../function.php');        // define.php �� pgsql.php �� require_once ���Ƥ���
require_once ('../../tnk_func.php');        // TNK �˰�¸������ʬ�δؿ��� require_once ���Ƥ���
require_once ('../../MenuHeader.php');      // TNK ������ menu class
require_once ('../../ControllerHTTP_Class.php');// TNK ������ MVC Controller Class
//////////// ���å����Υ��󥹥��󥹤�����
$session = new Session();
if (isset($_REQUEST['recNo'])) {
    $session->add_local('recNo', $_REQUEST['recNo']);
    exit();
}
access_log();                               // Script Name �ϼ�ư����

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader(0);                  // ǧ�ڥ����å�0=���̰ʾ� �����=TOP_MENU �����ȥ�̤����

////////////// ����������
$menu->set_site(99, 60);                    // site_index=99(�����ƥ��˥塼) site_id=60(�ƥ�ץ졼��)
////////////// �꥿���󥢥ɥ쥹����(���л��ꤹ����)
// $menu->set_RetUrl(SYS_MENU);                // �̾�ϻ��ꤹ��ɬ�פϤʤ�
//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title('�ƥ�ץ졼�� �����ȥ�');
//////////// ɽ�������
$menu->set_caption('����ץ�ǥ����ƥ�ޥ�������ɽ�����Ƥ��ޤ�');
//////////// �ƽ����action̾�ȥ��ɥ쥹����
$menu->set_action('�����ƥ�ޥ������Խ�',   INDUST . 'master/parts_item/parts_item_Main.php');
//////////// �ƽФ����Υڡ�����ݻ�
$menu->set_retGET('page_keep', 'on');

/**********************
////////////// �꥿���󥢥ɥ쥹����(������)
// ���å�����ѿ�̾�ϥ�����ץ�̾�����ĥ����ʬ���������Τ�'_ret'���ղä������UNIQUE����������롣
if (isset($_SESSION['template_ret'])) {
    $url_referer = $_SESSION['template_ret'];   // �ƽи�����¸���Ƥ���꥿���󥢥ɥ쥹�����
} else {
    $url_referer = $_SERVER['HTTP_REFERER'];    // error ����Τ���
    $_SESSION['template_ret'] = $url_referer;
}
**********************/

//////////// �֥饦�����Υ���å����к���
$uniq = $menu->set_useNotCache('target');

//////////// template �� ������
$reg_up_date = date_offset(1);
$reg_up_date2 = date_offset(3);
//////////// SQL ʸ�� where ��� ���Ѥ���
$search = sprintf('WHERE madat>=%d AND madat<=%d', $reg_up_date2, $reg_up_date);

//////////// ���ǤιԿ�
define('PAGE', '20');   // IE��25��OK����NN��23�Τ��� 20������

//////////// ��ץ쥳���ɿ�����     (�оݥơ��֥�κ������ڡ�������˻���)
$query = sprintf('SELECT count(*) FROM miitem %s', $search);
if ( getUniResult($query, $maxrows) <= 0) {         // $maxrows �μ���
    $_SESSION['s_sysmsg'] .= '��ץ쥳���ɿ��μ����˼���<br>DB����³���ǧ��';  // .= ��å��������ɲä���
}
//////////// �ڡ������ե��å�����(offset�ϻ��Ѥ������̾�����ѹ� �㡧sales_offset)
$offset = $session->get_local('offset');
if ($offset == '') $offset = 0;         // �����
if ( isset($_REQUEST['forward']) ) {                       // ���Ǥ������줿
    $offset += PAGE;
    if ($offset >= $maxrows) {
        $offset -= PAGE;
        if ($_SESSION['s_sysmsg'] == '') {
            $_SESSION['s_sysmsg'] .= "<font color='yellow'>���ǤϤ���ޤ���</font>";
        } else {
            $_SESSION['s_sysmsg'] .= "<br><font color='yellow'>���ǤϤ���ޤ���</font>";
        }
    }
} elseif ( isset($_REQUEST['backward']) ) {                // ���Ǥ������줿
    $offset -= PAGE;
    if ($offset < 0) {
        $offset = 0;
        if ($_SESSION['s_sysmsg'] == '') {
            $_SESSION['s_sysmsg'] .= "<font color='yellow'>���ǤϤ���ޤ���</font>";
        } else {
            $_SESSION['s_sysmsg'] .= "<br><font color='yellow'>���ǤϤ���ޤ���</font>";
        }
    }
} elseif ( isset($_REQUEST['page_keep']) ) {                // ���ߤΥڡ�����ݻ����� GET�����
    $offset = $offset;
} else {
    $offset = 0;                            // ���ξ��ϣ��ǽ����
    $session->add_local('recNo', '-1');     // 0�쥳���ɤǥޡ�����ɽ�����Ƥ��ޤ�������б�
}
$session->add_local('offset', $offset);

//////////// ɽ�����Υǡ���ɽ���ѤΥ���ץ� Query & �����
$query = sprintf("
        SELECT
            mipn                        AS �����ֹ�,                -- 0
            substr(midsc, 1, 26)        AS ����̾,                  -- 1
            -- mzist                    AS ���,
            -- COALESCE(mzist, '---')   AS ���,    --mzist��NULL�ʤ�'---'
            CASE
                WHEN trim(mzist) = '' THEN '---'         --NULL�Ǥʤ��ƥ��ڡ�������ޤäƤ�����Ϥ��졪
                ELSE mzist
            END                         AS ���,                    -- 2
            CASE
                WHEN trim(mepnt) = '' THEN '---'
                ELSE trim(mepnt)
            END                         AS �Ƶ���,                  -- 3
            madat                       AS ������Ͽ��,              -- 4
            CAST(last_date AS date)     AS \"Web��Ͽ��\",           -- 5
            CAST(last_date AS time(2))  AS \"Web��Ͽ����\"          -- 6
        FROM
            miitem
        %s      -- ������ where��� and �������Ǥ���
        ORDER BY madat DESC
        OFFSET %d LIMIT %d
    ", $search, $offset, PAGE);       // ���� $search �Ǹ���
$res   = array();
$field = array();
if (($rows = getResultWithField2($query, $field, $res)) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("����ץ�ǡ����ι�����:%s ��<br>�ǡ���������ޤ���", format_date($reg_up_date) );
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // ľ���θƽи������
    exit();
} else {
    $num = count($field);       // �ե�����ɿ�����
}

/////////// HTML Header ����Ϥ��ƥ���å��������
$menu->out_html_header();
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
<?php echo $menu->out_jsBaseClass() ?>

<!-- JavaScript�Υե���������body�κǸ�ˤ��롣 HTML�����Υ����Ȥ�����Ҥ˽���ʤ��������  -->
<script type='text/javascript' src='template.js?<?php echo $uniq ?>'></script>

<!-- �������륷���ȤΥե��������򥳥��� HTML�����Υ����Ȥ�����Ҥ˽���ʤ��������  -->
<link rel='stylesheet' href='template.css?<?php echo $uniq ?>' type='text/css' media='screen'>

<link rel='shortcut icon' href='/favicon.ico?=<?php echo $uniq ?>'>

<style type='text/css'><!-- --></style>
</head>

<body onLoad='setInterval("templ.blink_disp(\"caption\")", 500); templ.set_focus()' style='overflow-y:hidden;'>
    <center>
<?php echo $menu->out_title_border() ?>
        <!--
            <div style='position: absolute; top: 80; left: 7; width: 185; height: 31'>
                �����ͤǰ��ֻ���
            </div>
        -->
        
        <!----------------- ������ ���� ���� �Υե����� ---------------->
        <table width='100%' cellspacing='0' cellpadding='0' border='0'>
            <form name='page_form' method='post' action='<?php echo $menu->out_self() ?>'>
                <tr>
                    <td align='left'>
                        <table align='left' border='3' cellspacing='0' cellpadding='0'>
                            <td align='left'>
                                <input class='pt10b' type='submit' name='backward' value='����'>
                            </td>
                        </table>
                    </td>
                    <td align='center' class='caption_font' id='caption'>
                        <?php echo $menu->out_caption() . "\n" ?>
                    </td>
                    <td align='right'>
                        <table align='right' border='3' cellspacing='0' cellpadding='0'>
                            <td align='right'>
                                <input class='pt10b' type='submit' name='forward' value='����'>
                            </td>
                        </table>
                    </td>
                </tr>
            </form>
        </table>
        
        <!--------------- ����������ʸ��ɽ��ɽ������ -------------------->
        <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
        <table width='100%'class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <thead>
                <!-- �ơ��֥� �إå�����ɽ�� -->
                <tr>
                    <th class='winbox' nowrap width='10'>No.</th>        <!-- �ԥʥ�С���ɽ�� -->
                <?php
                for ($i=0; $i<$num; $i++) {             // �ե�����ɿ�ʬ���֤�
                ?>
                    <th class='winbox' nowrap><?php echo $field[$i] ?></th>
                <?php
                }
                ?>
                </tr>
            </thead>
            <tfoot>
                <!-- ���ߤϥեå����ϲ���ʤ� -->
            </tfoot>
            <tbody>
                <?php
                for ($r=0; $r<$rows; $r++) {
                    $recNo = ($offset + $r);
                    if ($session->get_local('recNo') == $recNo) {
                        echo "<tr style='background-color:#ffffc6;'>\n";
                    } else {
                        echo "<tr>\n";
                    }
                    echo "    <td class='winbox' nowrap align='right'><span class='pt10b'>", ($r + $offset + 1), "</span></td>    <!-- �ԥʥ�С���ɽ�� -->\n";
                    for ($i=0; $i<$num; $i++) {         // �쥳���ɿ�ʬ���֤�
                        // <!--  bgcolor='#ffffc6' �������� --> 
                        switch ($i) {
                        case 0:
                            echo "<td class='winbox pt9' nowrap align='center'>\n";
                            echo "    <a href='JavaScript:baseJS.Ajax(\"{$menu->out_self()}?recNo={$recNo}\");location.replace(\"{$menu->out_action('�����ƥ�ޥ������Խ�')}?partsKey=", urlencode($res[$r][$i]), "\")' target='_self' style='text-decoration:none;'>{$res[$r][$i]}</a>\n";
                            echo "</td>\n";
                            break;
                        case 1:
                        case 2:
                        case 3:
                            echo "<td class='winbox pt9' nowrap align='left'>{$res[$r][$i]}</span></td>\n";
                            break;
                        case 4:
                            echo "<td class='winbox pt9' nowrap align='center'>", format_date($res[$r][$i]), "</td>\n";
                            break;
                        default:
                            echo "<td class='winbox pt9' nowrap align='center'>{$res[$r][$i]}</td>\n";
                        }
                    }
                    echo "</tr>\n";
                }
                ?>
            </tbody>
        </table>
            </td></tr>
        </table> <!----------------- ���ߡ�End ------------------>
        <div>
            <input type='button' name='test_opne' value='Windowɽ��' onClick='templ.win_open("template.php", 1024, 768)'>
            <a href='template.php' target='subwin'>Windowɽ��</a>
            <input type='button' name='test_show' value='Windowɽ��' onClick='templ.win_show("template.php", 1024, 768)'>
        </div>
        <div style='text-align: left;'>
            <?php echo 'E_STRICT ', E_STRICT ?>
            <br>
            <?php echo 'E_ALL ', E_ALL ?>
            <br>
            <?php echo 'E_ALL | E_STRICT ', E_ALL | E_STRICT ?>
        </div>
    </center>
</body>
<?php echo $menu->out_alert_java()?>
</html>
<?php
ob_end_flush();                 // ���ϥХåե���gzip���� END
?>
