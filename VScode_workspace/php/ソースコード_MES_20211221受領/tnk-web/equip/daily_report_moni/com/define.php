<?php
//////////////////////////////////////////////////////////////////////////////
// 組立設備稼働管理システムの機械運転日報 共通define                        //
// Copyright (C) 2021-2021 nirihisa_ooya@nitto-kohki.co.jp                  //
// Original by yamagishi@matehan.co.jp                                      //
// Changed history                                                          //
// 2021/03/26 Created   define.php                                          //
//////////////////////////////////////////////////////////////////////////////
// ルートのdefineを取込む 2005/05/20 ADD k_kobayashi tnksys@nitto-kohki.co.jp //
// require_once ('/home/www/html/tnk-web/define.php');
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
    define ('CONTEXT_PATH'      , '/equip/daily_report_moni/');
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
    define ('BUSINESS_DAY_CHANGE_TIME_KUMI'  , '0000');
    // 運転日報作成開始日
    if (!isset($_SESSION)) session_start();
    if ($_SESSION['factory'] == '7') {
        define ('REPORT_START_DATE', '20070501');
    } elseif ($_SESSION['factory'] == '6') {
        define ('REPORT_START_DATE', '20210301');
    } else {
        define ('REPORT_START_DATE', date('Ymd'));  // 本日なので更新されない
    }
    // 特注品指示No.
    define ('CUSTOM_MADE_SIJI_NO'       , '00000');
?>
