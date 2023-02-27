#!/usr/local/bin/php 
<?php
//////////////////////////////////////////////////////////////////////////////
// OpenOffice Draw で出力したSVGファイルを整形しtemplateを出力(*.tpl) CLI版 //
// Copyright (C) 2007      Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2007/05/29 Created  svg2template.php                                     //
// 2007/05/30 UTF-8N で統一                                                 //
// 2007/06/01 <pxd>タグをsimplateのタグとして埋め込み EUC-JPで統一          //
//            X軸の位置を１文字単位で指定を先頭のみ指定に正規表現で置換え   //
// 2007/06/03 ヘッダーとフッターを削除して<svgのみを抽出へ変更(複数頁に対応)//
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
if ($argc != 2 || in_array($argv[1], array('--help', '-help', '-h', '-?'))) {
?>

これは、ひとつの引数(SVGファイル)又はオプションをとるSVG→templateコンバートです。

  使用法:
  <?php echo $argv[0]; ?> <option>

  <option> は整形したいSVGのファイル名です。例：<?php echo $argv[0]; ?> test.svg
  --help, -help, -h, あるいは -? を指定すると、
  このヘルプが表示されます。

<?php
    exit();
} else {
    $svgFile = $argv[1];
    $tmpFile = preg_replace('/(\.svg)$/i', '.tmp', $svgFile);
    $tplFile = preg_replace('/(\.svg)$/i', '.tpl', $svgFile);
}
$handle = fopen($svgFile, 'r');
if ($handle) {
    ///// タグの切替わりで改行を挿入
    $fp = fopen($tmpFile, 'w');
    while (!feof($handle)) {
        $buffer = fgets($handle, 1024);
        $data = str_replace('><', ">\n<", $buffer);
        fwrite($fp, $data);
    }
    fclose($fp);
    chmod($tmpFile, 0666);
    ///// 埋め込まれた変数を smarty変数に置換え
    $fpr = fopen($tmpFile, 'r');
    $fp  = fopen($tplFile, 'w');
    while (!feof($fpr)) {
        $buffer = fgets($fpr, 4096);
        if (!(strpos($buffer, '<?xml ') === false)) continue;
        if (!(strpos($buffer, '<!DOCTYPE ') === false)) continue;
        $buffer = str_replace('</svg>', "</svg>\n", $buffer);
        /**************************
        // xml version="1.0" encoding="UTF-8" → xml version="1.0" encoding="UTF-8"
        $buffer = str_replace('encoding="UTF-8"', 'encoding="UTF-8"', $buffer);
        // <svg .....> → <{$pxd}>\n<svg ......>
        $buffer = str_replace('<svg ', "{\$pxd}\n<page>\n<svg ", $buffer);
        // </svg> → </svg>\n</page>\n</pxd>
        $buffer = str_replace('</svg>', "</svg>\n</page>\n</pxd>\n", $buffer);
        **************************/
        
        // <tspan x="3931 4196 4460 4725 4990 5254 5519 " y="3707">{$ → <tspan x="3931" y="3707">{$
        $buffer = preg_replace('/(\<tspan\sx="[0-9]+)(.+)(" y=".+\>\{\$)/', '$1$3', $buffer);
        // {$var} → <{$var}>
        $buffer = preg_replace('/(\{\$)(.+)(\})/', '<{$$2}>', $buffer);
        
        // UTF-8をEUC-JPへ変換
        $buffer = mb_convert_encoding($buffer, 'UTF-8', 'UTF-8');
        fwrite($fp, $buffer);
    }
    fclose($fpr);
    fclose($fp);
    fclose($handle);
    chmod($tplFile, 0666);
}

?>
