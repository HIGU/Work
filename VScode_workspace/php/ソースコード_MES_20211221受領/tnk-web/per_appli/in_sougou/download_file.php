<?php
//////////////////////////////////////////////////////////////////////////////
// �ϽС��������˥塼 ���ܸ�ե�������������                          //
// Copyright (C) 2014-2014 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2014/09/19 Created  download_file.php                                    //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);      // E_ALL='2047' debug ��
// ini_set('display_errors', '1');         // Error ɽ�� ON debug �� ��꡼���女����
session_start();                        // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��
//ob_start('ob_gzhandler');               // ���ϥХåե���gzip����

require_once ('../../function.php');       // TNK ������ function
require_once ('../../MenuHeader.php');     // TNK ������ menu class
require_once ('../../tnk_func.php');
access_log();                           // Script Name �ϼ�ư����

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader(0);    // ǧ�ڥ�٥�=0, �꥿���󥢥ɥ쥹, �����ȥ�λ���ʤ�

////////////// ����������
$menu->set_site(97, 999);                // site_index=4(�ץ���೫ȯ) site_id=999(�ҥ�˥塼����)

// �ե�����������������
$filename_old = substr($_SERVER['PATH_INFO'], 1);
// �ʲ����ĤΥ��󥳡��ɤǻȤ�ʬ���ʤ��ȡ���������ɤǥ��顼��ȯ������
// �¥ե��������
$filename     = mb_convert_encoding($filename_old, "EUC", "UTF-8");
// ��������ɥե�����̾����
$filename2    = mb_convert_encoding($filename_old, "SJIS", "UTF-8");
header("Content-Type: application/octet-stream"); 
header("Content-Disposition: attachment; filename=".$filename2);
header("Content-Length:".filesize($filename));
readfile($filename);
?>
