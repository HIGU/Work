<?php
//////////////////////////////////////////////////////////////////////////////
// �ץ���೫ȯ����� ��Ͽ�ե�����                                        //
// Copyright (C) 2002-2010 Kazuhiro.kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2002/02/12 Created   dev_req_insert.php                                  //
// 2002/08/09 register_globals = Off �б�                                   //
// 2002/11/18 �᡼�����������������Ĺ�����������Ĺ���ѹ�                //
// 2003/01/31 �����������Ԥ���ȯ�Ԥξ��˥᡼����������ѹ�              //
// 2003/12/12 define���줿����ǥǥ��쥯�ȥ�ȥ�˥塼̾����Ѥ���          //
// 2004/02/24 �᡼����ʸ��ʸ�������ɤ� EUC-JP ���� SJIS �� nkf -EsLw ���Ѵ� //
// 2005/05/17 nkf -EjLw �� nkf -Ej ʸ�������б� php4�Ǥ�����ʤ��ä���php5��//
//            �嵭�򹹤�mail()���ѹ�(From: Reply-To: ���ɲ� Return-Path: NG)//
//               ��     mb_send_mail()��(��ư�ǥ��󥳡��ɤ��ʤ��Ƥ�ϣ�)    //
// 2005/06/08 mb_send_mail��ʣ���Υإå�������ڤ�Τ�\r\n����Ѥ���        //
// 2010/01/26 �᡼�����������ͤ��󤫤���ë���ѹ�                     ��ë //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��
ob_start();  //Warning: Cannot add header ���к��Τ����ɲá�2002/01/21
require_once ('../function.php');
// include("../define.php");
// session_register("s_dev_touroku");
$sysmsg = $_SESSION['s_sysmsg'];
$_SESSION['s_sysmsg'] = NULL;
access_log();                               // Script Name �ϼ�ư����
// $_SESSION['dev_req_insert'] = date('H:i');
if (!isset($_SESSION['User_ID'])||!isset($_SESSION['Password'])||!isset($_SESSION['Auth'])) {
    $_SESSION['s_sysmsg'] = 'ǧ�ڤ���Ƥ��ʤ���ǧ�ڴ��¤��ڤ�ޤ�����Login ��ľ���Ʋ�������';
    header('Location: http:' . WEB_HOST . 'index1.php');
    exit();
}
$s_dev_iraibusho  = $_SESSION['s_dev_iraibusho'];
$s_dev_iraisya    = $_SESSION['s_dev_iraisya'];
$s_dev_mokuteki   = $_SESSION['s_dev_mokuteki'];
$s_dev_naiyou     = $_SESSION['s_dev_naiyou'];
$s_dev_yosoukouka = $_SESSION['s_dev_yosoukouka'];
$s_dev_bikou      = $_SESSION['s_dev_bikou'];

$query = "select name from user_detailes where uid='" . $_SESSION['s_dev_iraisya'] . "'";
$res_name = array();
$rows_name = getResult($query,$res_name);
$iraisya = $res_name[0][0];

$query = "select section_name from section_master where sid=" . $_SESSION['s_dev_iraibusho'];
$res_section = array();
$rows_name = getResult($query,$res_section);
$iraibusho = $res_section[0][0];

$bangou_qry = "select �ֹ� from dev_req";
$res_bangou=array();
if ($rows_bangou=getResult($bangou_qry,$res_bangou)) {
    $bangou = $rows_bangou + 1;
    $iraibi = date('Y-m-d');
    $insert_qry  = "insert into dev_req (�ֹ�, ������, ��������, �����, ��Ū, ����, ��λ��";
    $ins_qry_add = ") values($bangou, '$iraibi', " . $_SESSION["s_dev_iraibusho"] . ", '" . $_SESSION["s_dev_iraisya"] 
        . "', '" . $_SESSION["s_dev_mokuteki"] . "', '" . $_SESSION["s_dev_naiyou"] . "', '1970-01-01'";
    if ($_SESSION["s_dev_yosoukouka"] != '') {
        $insert_qry  .= ", ͽ�۸���";
        $ins_qry_add .= ", " . $_SESSION['s_dev_yosoukouka'];
    }
    if ($_SESSION['s_dev_bikou'] != '') {
        $insert_qry  .= ", ����";
        $ins_qry_add .= ", '" . $_SESSION['s_dev_bikou'] . "'";
    }
    $ins_qry_add .= ")";
    $insert_qry .= $ins_qry_add;
    if (funcConnect()) {
        execQuery('begin');
        if (execQuery($insert_qry) >=0 ) {
            execQuery('commit');
            disConnectDB();
            $_SESSION['s_dev_touroku'] = $bangou;
                            // �����Ԥ˥᡼�������
            `echo "�����ֹ桧 $bangou" > /tmp/dev_req_submit`;
            `echo >> /tmp/dev_req_submit`;
            `echo "�� �� ���� $iraibi" >> /tmp/dev_req_submit`;
            `echo >> /tmp/dev_req_submit`;
            `echo "�������� $s_dev_iraibusho:$iraibusho" >> /tmp/dev_req_submit`;
            `echo >> /tmp/dev_req_submit`;
            `echo "�� �� �ԡ� $s_dev_iraisya:$iraisya" >> /tmp/dev_req_submit`;
            `echo >> /tmp/dev_req_submit`;
            `echo "��    Ū�� $s_dev_mokuteki" >> /tmp/dev_req_submit`;
            `echo >> /tmp/dev_req_submit`;
            `echo "��    �ơ� " >> /tmp/dev_req_submit`;
            `echo "$s_dev_naiyou" >> /tmp/dev_req_submit`;
            `echo >> /tmp/dev_req_submit`;
            `echo "ͽ�۸��̡� $s_dev_yosoukouka" >> /tmp/dev_req_submit`;
            `echo >> /tmp/dev_req_submit`;
            `echo "��    �͡� $s_dev_bikou" >> /tmp/dev_req_submit`;
            `echo >> /tmp/dev_req_submit`;
            `echo >> /tmp/dev_req_submit`;
            `echo "��ա����Υ᡼��ϣף�⥵���С����鼫ư������줿��ΤǤ���" >> /tmp/dev_req_submit`;
            `echo "      ���Ф��ֿ����ʤ��ǲ��������ֿ�����ȥ��顼�ˤʤ�ޤ���" >> /tmp/dev_req_submit`;
            /***** 2005/05/17 ADD START *****/
            $to_addres = 'tnksys@nitto-kohki.co.jp';
            $subject = "�ץ���೫ȯ���� $bangou $iraibi $iraisya";
            $message = `/bin/cat /tmp/dev_req_submit`;
            // $message = mb_convert_encoding($message, 'JIS', 'EUC-JP');       // EUC-JP��JIS���Ѵ�
            // $add_head = mb_convert_encoding("From: tnksys@nitto-kohki.co.jp\r\nReply-To: tnksys@nitto-kohki.co.jp", 'JIS', 'EUC-JP');       // EUC-JP��JIS���Ѵ�
            $add_head = "From: tnksys@nitto-kohki.co.jp\r\nReply-To: tnksys@nitto-kohki.co.jp";
            /***** 2005/05/17 ADD END *****/
            if ($_SESSION['User_ID'] == '010561') {
                // mail($to_addres, $subject, $message, $add_head);
                mb_send_mail($to_addres, $subject, $message, $add_head);
                // `/bin/cat /tmp/dev_req_submit | /usr/bin/nkf -Ej | /bin/mail -s '�ץ���೫ȯ���� $bangou $iraibi $iraisya' tnksys@nitto-kohki.co.jp `;
            } else {
                $to_addres .= ',norihisa_ooya@nitto-kohki.co.jp';
                // mail($to_addres, $subject, $message, $add_head);
                mb_send_mail($to_addres, $subject, $message, $add_head);
                // `/bin/cat /tmp/dev_req_submit | /usr/bin/nkf -Ej | /bin/mail -s '�ץ���೫ȯ���� $bangou $iraibi $iraisya' tnksys@nitto-kohki.co.jp , ytetsuka@nitto-kohki.co.jp `;
            }
                            // �᡼��������λ
            header('Location: ' . H_WEB_HOST . DEV . 'dev_req_submit_dsp.php');
            exit();
        } else {
            execQuery('rollback');
            disConnectDB();
            $_SESSION['s_sysmsg'] = '�����������˼��Ԥ��ޤ�����<br>�����Ԥ�Ϣ���Ʋ�������';
            header('Location: ' . H_WEB_HOST . DEV_MENU);
            exit();
        }
    }
} else {
    $_SESSION['s_sysmsg'] = '�����ֹ�����˼��Ԥ��ޤ�����<br>�����Ԥ�Ϣ���Ʋ�������';
    header('Location: ' . H_WEB_HOST . DEV_MENU);
    exit();
}
ob_end_flush();  //Warning: Cannot add header ���к��Τ����ɲá�2002/01/21
?>
