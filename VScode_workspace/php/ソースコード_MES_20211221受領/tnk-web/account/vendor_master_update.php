<?php
//////////////////////////////////////////////////////////////////////////////
// #!/usr/local/bin/php-4.3.4-cgi -q                                        //
// �٥�����ޥ�����(ȯ����ޥ�����)�ι���  AS400 UKWLIB/W#MIWKCK            //
// AS/400 ----> Web Server (PHP) FTPž�����Բ� EBCDIC���Ѵ�������ʤ�����   //
// Copyright (C) 2003-2005 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2003/11/18 Created  vendor_master_update.php                             //
//                                  act_payable_get_ftp.php������˻���     //
// 2003/11/18 http �� cli�Ǥ��ѹ������褦�� requier_once �����л����     //
//            AS/400 �� UKPLIB/Q#MIWKCK RUNQRY �Ǽ¹Ԥ� Excel��TXT���Ѵ�    //
// 2003/11/28 ���򥳥��Ȥˤ��Ƥ����Τ� monthly_update.log �ˤ����ɲ�    //
// 2003/12/08 SJIS �� EUC �Ѵ����å��ɲ�   (NULL �� SPACE ���Ѵ�)         //
//                    (SJIS��EUC�ˤʤ�ʸ����NULL�Х��Ȥ��Ѵ������������) //
// 2004/01/07 ��ɽ�Ԥ����äƤ��ʤ������б� $data[6] = '' ���ɲ�           //
// 2004/04/05 header('Location: http:' . WEB_HOST . 'account/?????' -->     //
//                                  header('Location: ' . H_WEB_HOST . ACT  //
// 2004/12/02 mb_ereg_replace('��','�ʳ���',$data);�����¸ʸ���򵬳�ʸ���� //
// 2005/03/04 dir�ѹ� /home/www/html/weekly/ �� /home/guest/monthly/       //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
// ini_set('implicit_flush', '0');             // echo print �� flush �����ʤ�(�٤��ʤ뤿��)
ini_set('max_execution_time', 1200);        // ����¹Ի���=20ʬ
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��
require_once ('/home/www/html/tnk-web/function.php');
require_once ('/home/www/html/tnk-web/tnk_func.php');   // account_group_check()�ǻ���
access_log();                               // Script Name ��ư����
// $_SESSION['site_index'] = 20;               // �»�״ط�=10 �Ǹ�Υ�˥塼�� 99 �����
// $_SESSION['site_id']    = 10;               // ���̥�˥塼̵�� (0 <=)

//////////// �ƽи��μ���
$act_referer = $_SESSION['act_referer'];

//////////// ǧ�ڥ����å�
if (account_group_check() == FALSE) {
    // $_SESSION['s_sysmsg'] = '���ʤ��ϵ��Ĥ���Ƥ��ޤ���!<br>�����Ԥ�Ϣ���Ʋ�����!';
    $_SESSION['s_sysmsg'] = "Accounting Group �θ��¤�ɬ�פǤ���";
    header('Location: ' . $act_referer);
    exit();
}

///// ���Хѥ������
$realDir = realpath( dirname( __FILE__));
///// ʸ�������к��Τ��� cli�ǤθƽФ��ѹ� ���ꥸ�ʥ�� vendor_master_update_http.php ����¸
$_SESSION['s_sysmsg'] = `{$realDir}/vendor_master_update_cli.php`;

header('Location: ' . H_WEB_HOST . ACT . 'vendor_master_view.php');   // �����å��ꥹ�Ȥ�
// header('Location: http://masterst.tnk.co.jp/account/vendor_master_view.php');
// header('Location: ' . $act_referer);
exit();
?>
