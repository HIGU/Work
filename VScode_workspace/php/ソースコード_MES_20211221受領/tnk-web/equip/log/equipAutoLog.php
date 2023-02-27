#!/usr/local/bin/php
<?php
//////////////////////////////////////////////////////////////////////////////
// ������ư���������ƥ� ��ư������ ���饹�¹���        FWServer 1.31�б�  //
// Copyright (C) 2007-2018 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2007/06/26 Created  equipAutoLog.php                                     //
// 2007/06/27 $currentDir���ѹ�(�����Х��ѿ��ʤΤ�¾��include file�����) //
//            �嵭��main()�ؿ��ǥ������ѿ����ѹ�                          //
// 2007/06/30 �����ʬ��������������ԤΥ����å��ɲ�                        //
// 2007/07/01 ��������ɲä��ƣ�������������Ǥ����ֳ���                    //
// 2018/05/16 ������ʣ�����Τߤα��Ѥ��ѹ���                         ��ë //
// 2018/05/18 ������Υ����ɤ���Ū��7�ˤ����Τǣ�������ѹ�          ��ë //
// 2018/12/25 �������﫤�SUS��ʬΥ���塹�ΰ١�                      ��ë //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug ��
ini_set('implicit_flush', 'off');           // echo print �� flush �����ʤ�(�٤��ʤ뤿��) CLI CGI��

main();

function main()
{
    $currentDir = realpath(dirname(__FILE__));
    require_once ("{$currentDir}/EquipAutoLogClass.php");
    
    $check_file    = "{$currentDir}/../check_file";
    $auto_log_stop = "{$currentDir}/../equip_auto_log_stop";
    if (file_exists($check_file)) {
        exit(); // ���Υץ�������λ���Ƥ��ʤ��Τǥ���󥻥�
    } elseif (file_exists($auto_log_stop)) {
        exit(); // ��ư�������Υ��ȥå׻ؼ��Τ��Ὢλ 2007/06/15 ADD
    } else {
        $check_fp = fopen($check_file, 'a');    // �����å��ѥե���������
    }
    // sleep(8);      // cron�Ǽ¹ԤʤΤ�¾�Υץ�����٤��θ���ƣ������ٱ䤹�롣2007/06/15 10��8��
    
    $equipAutoLog = new EquipAutoLog();
    
    if ($equipAutoLog->set_factory(7)) {        // �����ʬ��7����(���)�˸���
        $equipAutoLog->equip_logExec_once();
    }
    if ($equipAutoLog->set_factory(8)) {        // �����ʬ��7����(SUS)�˸���
        $equipAutoLog->equip_logExec_once();
    }
    if ($equipAutoLog->set_factory(6)) {        // �����ʬ��6����˸���
        $equipAutoLog->equip_logExec_once_moni();   // 6����ϥץ���ब�㤦
    }
    /*
    if ($equipAutoLog->set_factory(4)) {        // �����ʬ��4����˸���
        $equipAutoLog->equip_logExec_once();
    }
    if ($equipAutoLog->set_factory(5)) {        // �����ʬ��5����˸���
        $equipAutoLog->equip_logExec_once();
    }
    $equipAutoLog->equip_exit();
    */
    
    fclose($check_fp);
    unlink($check_file);    // �����å��ѥե��������
    exit();
}
?>
