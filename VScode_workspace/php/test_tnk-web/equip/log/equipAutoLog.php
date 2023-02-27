#!/usr/local/bin/php
<?php
//////////////////////////////////////////////////////////////////////////////
// 設備稼動管理システム 自動ログ収集 クラス実行版        FWServer 1.31対応  //
// Copyright (C) 2007-2018 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2007/06/26 Created  equipAutoLog.php                                     //
// 2007/06/27 $currentDirに変更(グローバル変数なので他のinclude fileに注意) //
//            上記をmain()関数でローカル変数へ変更                          //
// 2007/06/30 工場区分設定の成功・失敗のチェック追加                        //
// 2007/07/01 ４工場を追加して１・４・５工場での本番開始                    //
// 2018/05/16 ４工場（７工場のみの運用に変更）                         大谷 //
// 2018/05/18 ４工場のコードを強制的に7にしたので７工場に変更          大谷 //
// 2018/12/25 ７工場を真鍮とSUSに分離。後々の為。                      大谷 //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug 用
ini_set('implicit_flush', 'off');           // echo print で flush させない(遅くなるため) CLI CGI版

main();

function main()
{
    $currentDir = realpath(dirname(__FILE__));
    require_once ("{$currentDir}/EquipAutoLogClass.php");
    
    $check_file    = "{$currentDir}/../check_file";
    $auto_log_stop = "{$currentDir}/../equip_auto_log_stop";
    if (file_exists($check_file)) {
        exit(); // 前のプロセスが終了していないのでキャンセル
    } elseif (file_exists($auto_log_stop)) {
        exit(); // 自動ログ収集のストップ指示のため終了 2007/06/15 ADD
    } else {
        $check_fp = fopen($check_file, 'a');    // チェック用ファイルを作成
    }
    // sleep(8);      // cronで実行なので他のプロセス負荷を考慮して１０秒遅延する。2007/06/15 10→8へ
    
    $equipAutoLog = new EquipAutoLog();
    
    if ($equipAutoLog->set_factory(7)) {        // 工場区分を7工場(真鍮)に限定
        $equipAutoLog->equip_logExec_once();
    }
    if ($equipAutoLog->set_factory(8)) {        // 工場区分を7工場(SUS)に限定
        $equipAutoLog->equip_logExec_once();
    }
    if ($equipAutoLog->set_factory(6)) {        // 工場区分を6工場に限定
        $equipAutoLog->equip_logExec_once_moni();   // 6工場はプログラムが違う
    }
    /*
    if ($equipAutoLog->set_factory(4)) {        // 工場区分を4工場に限定
        $equipAutoLog->equip_logExec_once();
    }
    if ($equipAutoLog->set_factory(5)) {        // 工場区分を5工場に限定
        $equipAutoLog->equip_logExec_once();
    }
    $equipAutoLog->equip_exit();
    */
    
    fclose($check_fp);
    unlink($check_file);    // チェック用ファイルを削除
    exit();
}
?>
