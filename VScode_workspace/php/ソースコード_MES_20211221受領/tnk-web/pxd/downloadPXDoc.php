<?php
//////////////////////////////////////////////////////////////////////////////
// ���饤����Ȱ�����PXDoc�μ�ư���������                                //
// �ƥ�ץ졼�ȥ��󥸥��simplate, ���饤����Ȱ�����PXDoc �����           //
// Copyright (C) 2007      Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2007/05/31 Created  downloadPXDoc.php                                    //
// 2007/06/07 PXDoc�ΥС�����󥢥å� 1.1820 �� 1.1821 ��                   //
// 2007/07/09 PXDoc�ΥС�����󥢥å� 1.1821-06 �� 1.1821-09 ��             //
// 2007/08/24 PXDoc�ΥС�����󥢥å� 1.1821-09 �� 1.1821-10 ��             //
// 2007/10/28 PXDoc�ΥС�����󥢥å� 1.1821-10 �� 1.1821-������ ��         //
//////////////////////////////////////////////////////////////////////////////
require_once ('../function.php');       // define.php �� pgsql.php �� require_once ���Ƥ���
$file_location = 'setup-pxd11821.exe';
$filename = 'setup-pxd11821.exe';

/* �ե������������ɽ��� */
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
