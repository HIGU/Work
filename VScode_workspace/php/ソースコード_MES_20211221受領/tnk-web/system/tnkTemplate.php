<?php
//////////////////////////////////////////////////////////////////////////
// ������������������������������������                                 //
// 2003/01/16 Copyright(C) 2003 K.Kobayashi tnksys@nitto-kohki.co.jp    //
// �ѹ�����                                                             //
// 2003/01/16 ��������  patTemplate.php                                 //
// 2003/06/30 ����Ū�˸�ľ��ɽ�����˴����֤�HTML�������������        //
//             ̾����template.php ���ѹ��� system �ǥ��쥯�ȥ�ذ�ư    //
//             �ե����륵���С��� ����.php ��Ϣư�������               //
// 2003/07/15 class patTemplate() �򿷵�Ƴ�� template.php�����Τ�ʬ���� //
//            getResultWithField2() �� getResultWithField3()���ѹ�����  //
//            ���ͥ���ǥå������Ѥδؿ��Ȥ�����                        //
// 2003/09/07 <!DOC... "http://www.w3.org/TR/html4/loose.dtd"> ���ɲ�   //
//////////////////////////////////////////////////////////////////////////
ini_set('error_reporting',E_ALL);       // E_ALL='2047' debug ��
ini_set('display_errors','1');          // Error ɽ�� ON debug �� ��꡼���女����
// ini_set('implicit_flush', 'off');       // echo print �� flush �����ʤ�(�٤��ʤ뤿��) CLI CGI��
// ini_set('max_execution_time', 1200);    // ����¹Ի���=20ʬ CLI CGI��
ob_start("ob_gzhandler");               // ���ϥХåե���gzip����
session_start();                        // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��
require_once ('../function.php');       // define.php �� pgsql.php �� require_once ���Ƥ���
require_once ('../tnk_func.php');       // TNK �˰�¸������ʬ�δؿ��� require_once ���Ƥ���
access_log();                           // Script Name �ϼ�ư����
$_SESSION['site_index'] = 99;           // �Ǹ�Υ�˥塼    = 99   �����ƥ�����Ѥϣ�����
$_SESSION['site_id'] = 60;              // ���̥�˥塼̵�� <= 0    �ƥ�ץ졼�ȥե�����ϣ�����
$current_script  = $_SERVER['PHP_SELF'];        // ���߼¹���Υ�����ץ�̾����¸
$url_referer     = $_SERVER["HTTP_REFERER"];    // �ƽФ�Ȥ�URL����¸ ���Υ�����ץȤ�ʬ�������򤷤Ƥ�����ϻ��Ѥ��ʤ�
// $url_referer     = $_SESSION['pl_referer'];     // ʬ������������¸����Ƥ���ƽи��򥻥åȤ���

//////////////// ǧ�ڥ����å�
if ( !isset($_SESSION['User_ID']) || !isset($_SESSION['Password']) || !isset($_SESSION['Auth']) ) {
// if ($_SESSION['Auth'] <= 2) {                // ���¥�٥뤬���ʲ��ϵ���
// if (account_group_check() == FALSE) {        // ����Υ��롼�װʳ��ϵ���
    $_SESSION['s_sysmsg'] = "ǧ�ڤ���Ƥ��ʤ���ǧ�ڴ��¤��ڤ�ޤ����������󤫤餪�ꤤ���ޤ���";
    // header("Location: http:" . WEB_HOST . "menu.php");   // ����ƽи������
    header("Location: $url_referer");                   // ľ���θƽи������
    exit();
}

/********** Logic Start **********/
//////////////// �����ȥ�˥塼�Σգң����� & JavaScript����
$menu_site_url = 'http:' . WEB_HOST . 'menu_site.php';
$menu_site_script =
"<script language='JavaScript'>
<!--
    parent.menu_site.location = '$menu_site_url';
// -->
</script>";

//////////// �����ȥ�����ա���������
$today = date("Y/m/d H:i:s");

//////////// JavaScript Stylesheet File ��ɬ���ɤ߹��ޤ���
$uniq = uniqid("target");

//////////// �����ƥ��å������ѿ������
// $_SESSION['s_sysmsg'] = "";      // menu_site.php �ǻ��Ѥ��뤿�ᤳ���ǽ�������Բ�

//////////// template �� ������
$reg_up_date = 20030624;
//////////// SQL ʸ�� where ��� ���Ѥ���
$search = sprintf("where madat=%d", $reg_up_date);

//////////// ���ǤιԿ�
define("PAGE", 10);

//////////// ����쥳���ɿ�����     (�оȥǡ����κ������ڡ�������˻���)
$query = sprintf("select count(*) from miitem %s", $search);
if ( getUniResult($query, $maxrows) <= 0) {         // $maxrows �μ���
    $_SESSION['s_sysmsg'] .= "����쥳���ɿ��μ����˼���";      // .= ��å��������ɲä���
}
//////////// �ڡ������ե��å�����
if ( isset($_POST['forward']) ) {                       // ���Ǥ������줿
    $_SESSION['offset'] += PAGE;
    if ($_SESSION['offset'] >= $maxrows) {
        $_SESSION['offset'] -= PAGE;
        if ($_SESSION['s_sysmsg'] == "") {
            $_SESSION['s_sysmsg'] .= "<font color='yellow'>���ǤϤ���ޤ���</font>";
        } else {
            $_SESSION['s_sysmsg'] .= "<br><font color='yellow'>���ǤϤ���ޤ���</font>";
        }
    }
} elseif ( isset($_POST['backward']) ) {                // ���Ǥ������줿
    $_SESSION['offset'] -= PAGE;
    if ($_SESSION['offset'] < 0) {
        $_SESSION['offset'] = 0;
        if ($_SESSION['s_sysmsg'] == "") {
            $_SESSION['s_sysmsg'] .= "<font color='yellow'>���ǤϤ���ޤ���</font>";
        } else {
            $_SESSION['s_sysmsg'] .= "<br><font color='yellow'>���ǤϤ���ޤ���</font>";
        }
    }
} elseif ( isset($_POST['page_keep']) ) {               // ���ߤΥڡ�����ݻ�����
    $offset = $_SESSION['offset'];
} else {
    $_SESSION['offset'] = 0;                            // ���ξ��ϣ��ǽ����
}
$offset = $_SESSION['offset'];

//////////// ɽ�����Υǡ���ɽ���ѤΥ���ץ� Query & �����
$query = sprintf("
        select
            mipn as �����ֹ�,
            midsc as ����̾
        from
            miitem
        %s offset %d limit %d
    ", $search, $offset, PAGE);       // ���� $search �Ǹ���
$res   = array();
$field = array();
if (($rows = getResultWithField3($query, $field, $res)) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("�����ƥ�ޥ������ι�����:%s ��<br>�ǡ���������ޤ���", format_date($reg_up_date) );
    header("Location: $url_referer");                   // ľ���θƽи������
    exit();
} else {
    $num = count($field);       // �ե�����ɿ�����
}
///////////// ɽ���ѹ��ֹ������
$dsp_num = array();
for ($i=0; $i<$rows; $i++) {
    $dsp_num[$i] = ($i + $offset + 1);
}
//////////// ɽ���ѤΥե���������������
for ($r=0; $r<$rows; $r++) {
    $field0[$r] = $res[$r][0];
    $field1[$r] = $res[$r][1];
}
/********** Logic End   **********/

//////////// ���̥إå����ν�Ф�
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");               // ���դ����
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");  // ��˽�������Ƥ���
header("Cache-Control: no-store, no-cache, must-revalidate");   // HTTP/1.1
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");                                     // HTTP/1.0

/********** patTemplate ��Ф� ************/
include_once ( "../../patTemplate/include/patTemplate.php" );
$tmpl = new patTemplate();

//  In diesem Verzeichnis liegen die Templates
$tmpl->setBasedir( "templates" );

$tmpl->readTemplatesFromFile( "tnkTemplate.tmpl.html" );

$tmpl->addVars( "page", array("PAGE_TITLE"         => "TNK ��ȯ�ѥƥ�ץ졼��") );
$tmpl->addVars( "page", array("PAGE_MENU_SITE_URL" => $menu_site_script) );
$tmpl->addVars( "page", array("PAGE_UNIQUE"        => $uniq) );
$tmpl->addVars( "page", array("PAGE_RETURN_URL"    => $url_referer) );
$tmpl->addVars( "page", array("PAGE_CURRENT_URL"   => $current_script) );
$tmpl->addVars( "page", array("PAGE_HEADER_TITLE"  => "�ԣΣ� ��ȯ�ѥƥ�ץ졼��") );
$tmpl->addVars( "page", array("PAGE_HEADER_TODAY"  => $today) );
$tmpl->addVars( "page", array("PAGE_BODY_TITLE"    => "�ƥ�ץ졼�Ȥǥ����ƥ�ޥ����� ����") );

$tmpl->addVars( "item", array("ITEM_FIELD" => $field) );

$tmpl->addVars( "tbody_rows", array("TBODY_DSP_NUM" => $dsp_num) );
$tmpl->addVars( "tbody_rows", array("TBODY_FIELD0"  => $field0) );
$tmpl->addVars( "tbody_rows", array("TBODY_FIELD1"  => $field1) );

//  Alle Templates ausgeben
$tmpl->displayParsedTemplate();
/************* patTemplate ��λ *****************/

/////// �ǥХå���
echo    "<br><br>--------------------------------------------&lt;DUMP INFOS&gt;--------------------------------------------<br><br>";
$tmpl->dump();
?>
