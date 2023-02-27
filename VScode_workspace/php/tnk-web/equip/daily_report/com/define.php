<?php
//////////////////////////////////////////////////////////////////////////////
// 設備稼働管理システムの機械運転日報 共通define                            //
// Copyright (C) 2004-2018 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Original by yamagishi@matehan.co.jp                                      //
// Changed history                                                          //
// 2004/07/15 Created   ReportList.php                                      //
// 2005/05/20 ルートのdefineを取込む hostを指定しないとlocal接続になるUnix  //
//            違うパラメーターで接続すると新規にコネクションが出来るため    //
// 2007/04/14 運転日報作成開始日を 20040902 → 2007/04/01 へ変更            //
//            インクルードファイルを擬似相対パス指定へ変更                  //
// 2007/05/21 運転日報作成開始日を 20070401 → 2007/05/01 へ変更            //
// 2007/06/28 $current...変数を中止。REPORT_START_DATE を工場別に(session)  //
// 2018/05/18 ７工場を追加。コード４の登録を強制的に7に変更            大谷 //
//////////////////////////////////////////////////////////////////////////////
// ルートのdefineを取込む 2005/05/20 ADD k_kobayashi tnksys@nitto-kohki.co.jp //
// require_once ('/var/www/html/define.php');
// $currentFullPathName = realpath(dirname(__FILE__));
require_once (realpath(dirname(__FILE__)) . '/../../../define.php');
    // ---------------------------------------------------------
    // データベース関連
    // ---------------------------------------------------------
if (!defined('DB_NAME'))
    define ('DB_NAME'           ,    'TnkSQL');
if (!defined('DB_USER'))
    define ('DB_USER'           ,    'nobody');
if (!defined('DB_PASSWD'))
    define ('DB_PASSWD'         ,    '');
    
    // ---------------------------------------------------------
    // ＵＲＬ関連
    // ---------------------------------------------------------
    // コンテンツパス
    define ('CONTEXT_PATH'      , '/equip/daily_report/');
    // 共通ＵＲＩ
    define ('COMMON_PATH'       , CONTEXT_PATH . 'com/');
    // マスターＵＲＩ
    define ('MASTER_PATH'       , CONTEXT_PATH . 'master/');
    // 検索ＵＲＩ
    define ('SEARCH_PATH'       , CONTEXT_PATH . 'search/');
    // ビジネスＵＲＩ
    define ('BUSINESS_PATH'     , CONTEXT_PATH . 'business/');
    // 検索ポップアップパス
    define ('SEARCH_JS'         , COMMON_PATH . 'search.js');
    // エラーページ
    define ('ERROR_PAGE'        , 'ErrorPage.php');
    
    // ドキュメントルート
    define ('DOCUMENT_ROOT'     , '/home/www/html/tnk-web');
    
    // ---------------------------------------------------------
    // 権限コード
    // ---------------------------------------------------------
    // アカウント機能コード
    define ('FNC_ACCOUNT'       , 'FNC_ACCOUNT');
    // マスタ機能コード
    define ('FNC_MASTER'        , 'FNC_MASTER');
    // 運転日報機能コード
    define ('FNC_REPORT'        , 'FNC_REPORT');
    // 運転日報機能コード
    define ('FNC_REPORT_ACCEPT' , 'FNC_REPORT_ACCEPT');
    
    // ---------------------------------------------------------
    // 日報関連
    // ---------------------------------------------------------
    // 営業日切替時刻
    define ('BUSINESS_DAY_CHANGE_TIME'  , '0830');
    // 運転日報作成開始日
    if (!isset($_SESSION)) session_start();
    if ($_SESSION['factory'] == '7') {
        define ('REPORT_START_DATE', '20070501');
    } elseif ($_SESSION['factory'] == '8') { {
        define ('REPORT_START_DATE', '20210601');
    }
    } else {
        define ('REPORT_START_DATE', date('Ymd'));  // 本日なので更新されない
    }
    // 特注品指示No.
    define ('CUSTOM_MADE_SIJI_NO'       , '00000');
?>
