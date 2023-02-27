<?php
//////////////////////////////////////////////////////////////////////////////
// ��Ω������Ư���������ƥ�ε�����ž���� ����define                        //
// Copyright (C) 2021-2021 nirihisa_ooya@nitto-kohki.co.jp                  //
// Original by yamagishi@matehan.co.jp                                      //
// Changed history                                                          //
// 2021/03/26 Created   define.php                                          //
//////////////////////////////////////////////////////////////////////////////
// �롼�Ȥ�define������ 2005/05/20 ADD k_kobayashi tnksys@nitto-kohki.co.jp //
// require_once ('/home/www/html/tnk-web/define.php');
// $currentFullPathName = realpath(dirname(__FILE__));
require_once (realpath(dirname(__FILE__)) . '/../../../define.php');
    // ---------------------------------------------------------
    // �ǡ����١�����Ϣ
    // ---------------------------------------------------------
if (!defined('DB_NAME'))
    define ('DB_NAME'           ,    'TnkSQL');
if (!defined('DB_USER'))
    define ('DB_USER'           ,    'nobody');
if (!defined('DB_PASSWD'))
    define ('DB_PASSWD'         ,    '');
    
    // ---------------------------------------------------------
    // �գң̴�Ϣ
    // ---------------------------------------------------------
    // ����ƥ�ĥѥ�
    define ('CONTEXT_PATH'      , '/equip/daily_report_moni/');
    // ���̣գң�
    define ('COMMON_PATH'       , CONTEXT_PATH . 'com/');
    // �ޥ������գң�
    define ('MASTER_PATH'       , CONTEXT_PATH . 'master/');
    // �����գң�
    define ('SEARCH_PATH'       , CONTEXT_PATH . 'search/');
    // �ӥ��ͥ��գң�
    define ('BUSINESS_PATH'     , CONTEXT_PATH . 'business/');
    // �����ݥåץ��åץѥ�
    define ('SEARCH_JS'         , COMMON_PATH . 'search.js');
    // ���顼�ڡ���
    define ('ERROR_PAGE'        , 'ErrorPage.php');
    
    // �ɥ�����ȥ롼��
    define ('DOCUMENT_ROOT'     , '/home/www/html/tnk-web');
    
    // ---------------------------------------------------------
    // ���¥�����
    // ---------------------------------------------------------
    // ��������ȵ�ǽ������
    define ('FNC_ACCOUNT'       , 'FNC_ACCOUNT');
    // �ޥ�����ǽ������
    define ('FNC_MASTER'        , 'FNC_MASTER');
    // ��ž����ǽ������
    define ('FNC_REPORT'        , 'FNC_REPORT');
    // ��ž����ǽ������
    define ('FNC_REPORT_ACCEPT' , 'FNC_REPORT_ACCEPT');
    
    // ---------------------------------------------------------
    // �����Ϣ
    // ---------------------------------------------------------
    // �Ķ������ػ���
    define ('BUSINESS_DAY_CHANGE_TIME'  , '0830');
    define ('BUSINESS_DAY_CHANGE_TIME_KUMI'  , '0000');
    // ��ž�������������
    if (!isset($_SESSION)) session_start();
    if ($_SESSION['factory'] == '7') {
        define ('REPORT_START_DATE', '20070501');
    } elseif ($_SESSION['factory'] == '6') {
        define ('REPORT_START_DATE', '20210301');
    } else {
        define ('REPORT_START_DATE', date('Ymd'));  // �����ʤΤǹ�������ʤ�
    }
    // �����ʻؼ�No.
    define ('CUSTOM_MADE_SIJI_NO'       , '00000');
?>
