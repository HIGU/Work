<?php
//////////////////////////////////////////////////////////////////////////////
// クライアント印刷のPXDocの自動ダウンロード                                //
// テンプレートエンジンはsimplate, クライアント印刷はPXDoc を使用           //
// Copyright (C) 2007      Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2007/05/31 Created  downloadPXDoc.php                                    //
// 2007/06/07 PXDocのバージョンアップ 1.1820 → 1.1821 へ                   //
// 2007/07/09 PXDocのバージョンアップ 1.1821-06 → 1.1821-09 へ             //
// 2007/08/24 PXDocのバージョンアップ 1.1821-09 → 1.1821-10 へ             //
// 2007/10/28 PXDocのバージョンアップ 1.1821-10 → 1.1821-正式版 へ         //
//////////////////////////////////////////////////////////////////////////////
require_once ('../function.php');       // define.php と pgsql.php を require_once している
$file_location = 'setup-pxd11821.exe';
$filename = 'setup-pxd11821.exe';

/* ファイルダウンロード処理 */
$filesize = filesize( $file_location );
header( "Accept-Ranges: none" );
header( "Content-Length: $filesize" );
header( "Content-Disposition: filename=\"$filename\"" );
header( "Content-Type: text/octet-stream" );// IE
$fp = fopen($file_location, 'rb');
@fpassthru($fp);
fclose($fp);
exit;
?>
