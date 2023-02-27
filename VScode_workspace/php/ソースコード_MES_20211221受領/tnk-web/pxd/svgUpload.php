<?php
//////////////////////////////////////////////////////////////////////////////
// ���饤����ȤǺ�������SVG�ե�����(template)����������                    //
// �ƥ�ץ졼�ȥ��󥸥��simplate, ���饤����Ȱ�����PXDoc �����           //
// Copyright (C) 2007      Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2007/06/04 Created  svgUpload.php                                        //
// 2007/12/06 Directory �� /test/print/ �� /pxd/ ���ѹ�                     //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL | E_STRICT);
ini_set('display_errors', '0');             // Error ɽ�� ON debug �� ��꡼���女����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��
require_once ('../function.php');           // define.php �� pgsql.php �� require_once ���Ƥ���
require_once ('../MenuHeader.php');         // TNK ������ menu class
///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader(0);                  // ǧ�ڥ����å�0=���̰ʾ� �����=TOP_MENU �����ȥ�̤����

$ok_flg = true;

if (!isset($_FILES['svgFile']['name'])) {
    $_SESSION['s_sysmsg'] = '�ե����뤬���ꤵ��Ƥ��ޤ���';
    $ok_flg = false;
} elseif (!preg_match('/\.svg$/i', $_FILES['svgFile']['name'])) {
    $_SESSION['s_sysmsg'] = '���ꤵ�줿�ե�����ϡ�SVG�ե�����ǤϤ���ޤ���';
    $ok_flg = false;
}
if (!isset($_FILES['svgFile']['tmp_name'])) {
    $_SESSION['s_sysmsg'] = '����ե����������Ǥ��ޤ���Ǥ�����';
    $ok_flg = false;
}
if ($ok_flg === false) {
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // ľ���θƽи������
    exit();
}

$ok_flg = true;

$currentFullPathName = realpath(dirname(__FILE__));
$file_name = "{$currentFullPathName}/template/" . $_FILES['svgFile']['name'];
if (file_exists($file_name)) {
    if (!unlink($file_name)) {
        $_SESSION['s_sysmsg'] = "����¸�ߤ���ե�����{$file_name}�����Ǥ��ޤ���Ǥ�����";
        $ok_flg = false;
    }
}
if ($ok_flg) {
    if (!rename($_FILES['svgFile']['tmp_name'], $file_name)) {
        $_SESSION['s_sysmsg'] = "����ե����뤫��{$file_name}������Ǥ��ޤ���Ǥ�����";
        $ok_flg = false;
    }
}
if ($ok_flg) {
    if (!chmod($file_name, 0666)) {
        $_SESSION['s_sysmsg'] = "{$file_name}�Υѡ��ߥå������ѹ��Ǥ��ޤ���Ǥ�������";
        $ok_flg = false;
    }
}
$msg = `./svg2template.php {$file_name}`;

/********** �ǥХå���
$filename = 'svgUpload-debug.txt';
$fp = fopen($filename, 'w');
fwrite($fp, '���饤����ȤΥե�����̾�� ' . $_FILES['svgFile']['name'] . "\n");
fwrite($fp, '�����С�¦�Υե�����̾�� ' . $_FILES['svgFile']['tmp_name'] . "\n");
fclose($fp);
chmod($filename, 0666);
**********/

if ($ok_flg) {
    $file_name = str_replace('.svg', '.tpl', $file_name);
    $_SESSION['s_sysmsg'] = "SVG���åץ��ɡ�template����С��Ƚ�������λ���ޤ�����\\n\\n�ե�����ϡ��֡�{$file_name}���ס��Ǻ������ޤ�����";
}
header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // ľ���θƽи������
exit();
?>
