<?php
//////////////////////////////////////////////////////////////////////////////
// Tnk Web site 全般のルールを定数で定義(Server/DB/Directory/menu)等        //
// Copyright (C) 2001-2014 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2001/07/07 Created   define.php                                          //
// 2002/07/25 WEB_HOST と DB_HOST 複数サーバー環境に対応                    //
// 2002/08/07 社員情報管理のディレクトリ変更                                //
// 2003/04/21 AUTH_LEVEL が AUTH_LEBEL にミスタイプしていたのを訂正         //
// 2003/04/23 FUNC_STATISTIC=21 従業員の統計情報追加                        //
// 2003/11/17 AS400FTP のアドレスを追加 各ロジックを一元管理へ              //
// 2003/12/11 ディレクトリ定義とルートメニュー定義を追加                    //
// 2003/12/22 SITE_INDEXとSITE_IDの定義を追加  社員定義をemp_define.phpへ   //
// 2004/03/04 CLI版で$_SERVER['HTTP_HOST']のIndexが存在しないためcheck追加  //
// 2004/07/20 Error Page 用のディレクトリ定義を追加                         //
// 2005/01/18 SITE_ICON ディレクトリ・ファイル・V=3 名定義を追加            //
// 2005/07/15 INDEX_REGU → 社内規定メニュー 追加                           //
// 2005/08/28 JS_BASE_CLASS → サイト共通JavaScriptベースクラスファイル追加 //
// 2005/09/23 DBを masterDB(10.1.3.247) へ変更(DBサーバー分離)              //
// 2007/03/27 define('INDEX_EQIP', 2) → define('INDEX_EQUIP', 40) へ変更   //
// 2008/08/29 INDEX_QUALITY → 品質メニュー 追加                            //
//            define('INDEX_QUALITY',  70)                             大谷 //
// 2010/03/13 AS400災害対策テストのためAS400_HOSTを一時10.1.3.99に変更 大谷 //
// 2010/09/30 INDEX_ASSET → 資産管理メニュー 追加                          //
//            define('INDEX_ASSET',  80)                               大谷 //
// 2012/03/10 AS400災害対策テストのためAS400_HOSTを一時10.1.3.99に変更 大谷 //
// 2012/11/17 AS400災害対策テストのためAS400_HOSTを一時10.1.3.99に変更 大谷 //
// 2013/05/06 AS400VerUPのため、AS400_HOSTを一時10.1.3.99に変更 大谷        //
// 2013/11/11 INDEX_PER_APPLI → 届出・申請書メニュー（人事） 追加          //
//            define('INDEX_PER_APPLI',  97)                                //
//            INDEX_PER_APPLI → 届出・申請書メニュー（経理） 追加          //
//            define('INDEX_PER_APPLI',  98)                           大谷 //
// 2013/11/16 AS400災害対策テストのためAS400_HOSTを一時10.1.3.99に変更 大谷 //
// 2013/12/04 ASSET_MENUが間違っていたので修正                         大谷 //
// 2014/08/02 新AS切替テストのためAS400_HOSTを一時10.1.100.170に変更   大谷 //
// 2014/09/19 経理の届出メニューを経理他へ変更                         大谷 //
// 2014/11/15 AS400災害対策テストのためAS400_HOSTを一時10.1.2.76に変更 大谷 //
//////////////////////////////////////////////////////////////////////////////
    /* サーバー情報 */
    if (isset($_SERVER['HTTP_HOST'])) {     // CLI版の対応のため追加
        define('WEB_HOST',   '//' . $_SERVER['HTTP_HOST'] . '/');   // 汎用ホスト名
        define('H_WEB_HOST', 'http://' . $_SERVER['HTTP_HOST']);    // header()用ホスト名
        define('LH_WEB_HOST', 'Location: http://' . $_SERVER['HTTP_HOST']);// header()用ロケーション付ホスト名
    }
    define('WEB_DOMAIN', 'tnk.co.jp');
    if (@$_SERVER['SERVER_ADDR'] == '10.1.1.252') {         // CLI版の対応のため@でerror抑止
        define('DB_HOST',    '10.1.3.247');
    } elseif (file_exists('/usr/local/masterst_cli_flg')) { // CLI版の対応のため追加
        define('DB_HOST',    '10.1.3.247');
    } else {
        define('DB_HOST',    '127.0.0.1');  // 開発用とbackup用のマシーンは自分のDBを使う
    }
    /*****
    define('DB_HOST',    '127.0.0.1');
    *****/
    define('DB_PORT',    '5432');
    define('DB_NAME',    'TnkSQL');
    define('DB_USER',    'nobody');
    define('DB_PASSWD',  '');
    define('TEMP_DIR',   '/tmp/');
    define('AS400_HOST', '10.1.1.252');           // 災害時に10.1.2.76に変更（TNKバックアップ機）元は10.1.1.252
    //define('AS400_HOST', '10.1.2.76');           // 災害時に10.1.2.76に変更（TNKバックアップ機）元は10.1.1.252
    //define('AS400_HOST', '10.1.1.102');           // 切り替えテスト用
    define('AS400_USER', 'FTPUSR');
    define('AS400_PASS', 'AS400FTP');
    
    ///////////////// ディレクトリ定義とメニュー定義
    /* apache の home からのパス */
    define('ROOT', '/');                // 基点
    
    /* トップメニュー */
    define('TOP',       ROOT);                  define('TOP_MENU',      TOP.     'menu.php');
    /* サイトメニュー */
    define('SITE',      ROOT);                  define('SITE_MENU',     SITE.    'menu_site.php');
    /* 生産メニュー */
    define('INDUST',    ROOT.'industry/');      define('INDUST_MENU',   INDUST.  'industry_menu.php');
    /* 売上メニュー */
    define('SALES',     ROOT.'sales/');         define('SALES_MENU',    SALES.   'sales_menu.php');
    /* 設備メニュー */
    define('EQUIP',     ROOT.'equipment/');     define('EQUIP_MENU',    EQUIP.   'equipment_menu.php');
    define('EQUIP2',    ROOT.'equip/');         define('EQUIP_MENU2',   EQUIP2.  'equip_menu.php');
    define('EQUIP3',    ROOT.'equip/');         define('EQUIP_MENU3',   EQUIP2.  'equip_menu_moni.php');
    /* 社員メニュー */
    define('EMP',       ROOT.'emp/');           define('EMP_MENU',      EMP.     'emp_menu.php');
    define('EMP2',      ROOT.'emp2/');          define('EMP_MENU2',     EMP2.     'emp_menu.php');
    /* 損益メニュー */
    define('PL',        ROOT.'kessan/');        define('PL_MENU',       PL.      'pl_menu.php');
    /* 経理メニュー */
    define('ACT',       ROOT.'account/');       define('ACT_MENU',      ACT.     'act_menu.php');
    /* 原価メニュー */
    define('COST',      ROOT.'costAct/');       define('COST_MENU',     COST.    'costAct_menu.php');
    /* 開発メニュー */
    define('DEV',       ROOT.'devReq/');        define('DEV_MENU',      DEV.     'dev_req_menu.php');
    /* 管理メニュー */
    define('SYS',       ROOT.'system/');        define('SYS_MENU',      SYS.     'system_menu.php');
    /* 社内規定メニュー */
    define('REGU',      ROOT.'regulation/');    define('REGU_MENU',     REGU.    'regulation_menu.php');
    /* 品質メニュー */
    define('QUALITY',   ROOT.'quality/');       define('QUALITY_MENU',  QUALITY. 'quality_menu.php');
    /* 品質メニュー */
    define('ASSET',   ROOT.'asset/');           define('ASSET_MENU',  ASSET. 'assets_menu.php');
    /* 届出・申請書メニュー(人事) */
    define('PER_APPLI',     ROOT.'per_appli/'); define('PER_APPLI_MENU',  PER_APPLI. 'per_appli_menu.php');
    /* 届出・申請書メニュー(経理他) */
    define('ACT_APPLI',     ROOT.'act_appli/'); define('ACT_APPLI_MENU',  ACT_APPLI. 'act_appli_menu.php');
    
    /* Error Page ディレクトリ */
    define('ERROR',     ROOT.'error/');
    /* TESTディレクトリ */
    define('TEST',      ROOT.'test/');
    /* メニュー用CSSファイル */
    define('MENU_FORM', ROOT.'menu_form.css');
    /* サイト共通JavaScript ベースクラス ファイル */
    define('JS_BASE_CLASS', ROOT.'base_class.js');
    /* 画像ファイル */
    define('IMG',       ROOT.'img/');
    /* サイトメニューのアイコン ファイル */
    // define('SITE_ICON_ON',  IMG.'site_icon1_on.gif?v=2');  // 旧タイプ
    // define('SITE_ICON_OFF', IMG.'site_icon1_off.gif?v=2'); // 旧タイプ
    define('SITE_ICON_ON',  IMG.'tnk_icon_on.gif?v=3');
    define('SITE_ICON_OFF', IMG.'tnk_icon_off.gif?v=3');
    
    
    ///////////////// SITE_INDEX定義とSITE_ID定義
    /* SITE_INDEX */
    define('INDEX_TOP',          0);        // トップメニュー
    define('INDEX_SALES',        1);        // 売上メニュー
    define('INDEX_EMP',          3);        // 社員メニュー
    define('INDEX_DEV',          4);        // 開発メニュー
    define('INDEX_PL',          10);        // 損益メニュー
    define('INDEX_ACT',         20);        // 経理メニュー
    define('INDEX_INDUST',      30);        // 生産メニュー
    define('INDEX_EQUIP',       40);        // 設備メニュー
    define('INDEX_COST',        50);        // 原価メニュー
    define('INDEX_REGU',        60);        // 社内規定メニュー
    define('INDEX_QUALITY',     70);        // 品質メニュー
    define('INDEX_ASSET',       80);        // 資産管理メニュー
    define('INDEX_PER_APPLI',   97);        // 届出・申請書メニュー（人事）
    define('INDEX_ACT_APPLI',   98);        // 届出・申請書メニュー（経理他）
    define('INDEX_SYS',         99);        // 管理メニュー
    define('INDEX_LOGOUT',     999);        // 終了(logout)
    /* SITE_ID */
        // トップメニュー
    define('ID_TOP_TEL',                  1);       // テスト的なTNK内線表
        // 生産メニュー
    define('ID_INDUST_MATE_VIEW',        20);       // 総材料費の照会
    define('ID_INDUST_MATE_ENTRY',       21);       // 総材料費の登録
    define('ID_INDUST_A_DEN',            13);       // Ａ伝情報照会
    define('ID_INDUST_PAYABLE',          10);       // 買掛実績の照会
    define('ID_INDUST_PROVIDE',          11);       // 支給表の照会
    define('ID_INDUST_OPLAN',            12);       // 発注計画の照会
    define('ID_INDUST_C_INVENT',         35);       // カプラ棚卸の照会
    define('ID_INDUST_L_INVENT',         36);       // リニア棚卸の照会
    define('ID_INDUST_VENDOR',           22);       // 発注先マスターの照会
    define('ID_INDUST_PURCHASE',         31);       // 仕入金額の照会
    define('ID_INDUST_C_TOKU',           32);       // カプラ特注棚卸の照会
    define('ID_INDUST_BIMOR_INV',        34);       // バイモル棚卸の照会
        // 売上メニュー
    define('ID_SALES_VIEW',              11);       // 売上明細照会(new)
    define('ID_SALES_URIAGE',             1);       // 売上実績照会
    define('ID_SALES_URI_TMP',            2);       // 売上明細照会(指定利益率以下)
    define('ID_SALES_GRAPH_DAILY',        3);       // 売上グラフ日計
    define('ID_SALES_GRAPH_MONTHLY',      4);       // 売上グラフ月計
    define('ID_SALES_GRAPH_PRO_PAT',      5);       // 売上グラフ製品・部品の比率
    define('ID_SALES_GRAPH_CL',           6);       // 売上グラフ カプラ・リニア
    define('ID_SALES_GRAPH_C_TOKU',       9);       // 売上グラフ カプラ・Ｃ特注
    define('ID_SALES_PL_QUERY',           8);       // 月次損益照会
        // 設備メニュー
    
    
?>
