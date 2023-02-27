#!/usr/local/bin/php 
<?php
//////////////////////////////////////////////////////////////////////////////
// OpenOffice Draw �ǽ��Ϥ���SVG�ե������������template�����(*.tpl) CLI�� //
// Copyright (C) 2007      Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2007/05/29 Created  svg2template.php                                     //
// 2007/05/30 UTF-8N ������                                                 //
// 2007/06/01 <pxd>������simplate�Υ����Ȥ��������� EUC-JP������          //
//            X���ΰ��֤�ʸ��ñ�̤ǻ������Ƭ�Τ߻��������ɽ�����ִ���   //
// 2007/06/03 �إå����ȥեå�����������<svg�Τߤ���Ф��ѹ�(ʣ���Ǥ��б�)//
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
if ($argc != 2 || in_array($argv[1], array('--help', '-help', '-h', '-?'))) {
?>

����ϡ��ҤȤĤΰ���(SVG�ե�����)���ϥ��ץ�����Ȥ�SVG��template����С��ȤǤ���

  ����ˡ:
  <?php echo $argv[0]; ?> <option>

  <option> ������������SVG�Υե�����̾�Ǥ����㡧<?php echo $argv[0]; ?> test.svg
  --help, -help, -h, ���뤤�� -? ����ꤹ��ȡ�
  ���Υإ�פ�ɽ������ޤ���

<?php
    exit();
} else {
    $svgFile = $argv[1];
    $tmpFile = preg_replace('/(\.svg)$/i', '.tmp', $svgFile);
    $tplFile = preg_replace('/(\.svg)$/i', '.tpl', $svgFile);
}
$handle = fopen($svgFile, 'r');
if ($handle) {
    ///// ���������ؤ��ǲ��Ԥ�����
    $fp = fopen($tmpFile, 'w');
    while (!feof($handle)) {
        $buffer = fgets($handle, 1024);
        $data = str_replace('><', ">\n<", $buffer);
        fwrite($fp, $data);
    }
    fclose($fp);
    chmod($tmpFile, 0666);
    ///// �����ޤ줿�ѿ��� smarty�ѿ����ִ���
    $fpr = fopen($tmpFile, 'r');
    $fp  = fopen($tplFile, 'w');
    while (!feof($fpr)) {
        $buffer = fgets($fpr, 4096);
        if (!(strpos($buffer, '<?xml ') === false)) continue;
        if (!(strpos($buffer, '<!DOCTYPE ') === false)) continue;
        $buffer = str_replace('</svg>', "</svg>\n", $buffer);
        /**************************
        // xml version="1.0" encoding="UTF-8" �� xml version="1.0" encoding="EUC-JP"
        $buffer = str_replace('encoding="UTF-8"', 'encoding="EUC-JP"', $buffer);
        // <svg .....> �� <{$pxd}>\n<svg ......>
        $buffer = str_replace('<svg ', "{\$pxd}\n<page>\n<svg ", $buffer);
        // </svg> �� </svg>\n</page>\n</pxd>
        $buffer = str_replace('</svg>', "</svg>\n</page>\n</pxd>\n", $buffer);
        **************************/
        
        // <tspan x="3931 4196 4460 4725 4990 5254 5519 " y="3707">{$ �� <tspan x="3931" y="3707">{$
        $buffer = preg_replace('/(\<tspan\sx="[0-9]+)(.+)(" y=".+\>\{\$)/', '$1$3', $buffer);
        // {$var} �� <{$var}>
        $buffer = preg_replace('/(\{\$)(.+)(\})/', '<{$$2}>', $buffer);
        
        // UTF-8��EUC-JP���Ѵ�
        $buffer = mb_convert_encoding($buffer, 'EUC-JP', 'UTF-8');
        fwrite($fp, $buffer);
    }
    fclose($fpr);
    fclose($fp);
    fclose($handle);
    chmod($tplFile, 0666);
}

?>
