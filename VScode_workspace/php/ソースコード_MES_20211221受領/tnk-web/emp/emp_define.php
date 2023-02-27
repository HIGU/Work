<?php
//////////////////////////////////////////////////////////////////////////////
// Tnk Web site 全般のルールを定数で定義(Server/DB/Directory/menu)等        //
// Copyright (C) 2015-2015      Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp //
// Changed history                                                          //
// 2001/07/07 Created  emp_define.php                                       //
// 2002/08/07 社員情報管理のディレクトリ変更                                //
// 2003/04/21 AUTH_LEVEL が AUTH_LEBEL にミスタイプしていたのを訂正         //
// 2003/04/23 FUNC_STATISTIC=21 従業員の統計情報追加                        //
// 2003/10/16 define.php から emp_define.php へ分割                         //
// 2005/11/15 メールアドレス編集メニューを追加                              //
// 2007/02/07 FUNC_RECIDREGISTCHKを追加                                     //
// 2007/02/09 FUNC_CAPIDREGISTCHKを追加                                     //
// 2008/09/22 FUNC_WORKINGHOURSを追加                                  大谷 //
// 2015/06/18 計画有給の登録の為以下を追加                                  //
//            FUNC_ADDPHOLYDAY,HOLYDAYREGIST,HOLYDAYREGISTCHK          大谷 //
// 2019/09/13 有給管理台帳を追加                                       大谷 //
//////////////////////////////////////////////////////////////////////////////
    
    ///////////////// 社員メニュー
    /* 機能種別 */
    define('FUNC_MINEINFO',     '1');
    define('FUNC_NEWUSER',      '2');
    define('FUNC_CONFNEWUSER',  '3');
    define('FUNC_DBADMIN',      '4');
    define('FUNC_LOOKUP',       '5');
    define('FUNC_CHGUSERINFO',  '6');
    define('FUNC_CONFUSERINFO', '7');
    define('FUNC_ADMINUSERINFO','8');
    define('FUNC_RETIREINFO',   '9');
    define('FUNC_CHGRECEIVE',   '10');
    define('FUNC_CHGCAPACITY',  '11');
    define('FUNC_RECIDREGIST',  '12');
    define('FUNC_CAPIDREGIST',  '13');
    define('FUNC_CHGINDICATE',  '14');
    define('FUNC_ADDPHOLYDAY',  '15');
    define('FUNC_HOLYDAYREGIST', '16');
    define('FUNC_STATISTIC',    '21');      // 2003/04/23 追加
    define('FUNC_MAIL',         '22');      // 2005/11/15 追加
    define('FUNC_RECIDREGISTCHK', '23');    // 2007/02/07 追加
    define('FUNC_CAPIDREGISTCHK', '24');    // 2007/02/09 追加
    define('FUNC_HOLYDAYREGISTCHK', '25');    // 2015/06/18 追加
    define('FUNC_FIVE', '26');    // 2019/09/13 追加
    
    /* 権限種別 */
    define('AUTH_LEBEL0',   '0');   // low
    define('AUTH_LEBEL1',   '1');
    define('AUTH_LEBEL2',   '2');
    define('AUTH_LEBEL3',   '3');
    define('AUTH_LEBEL4',   '4');   // hi
    /* 権限種別 訂正 */
    define('AUTH_LEVEL0',   '0');   // low
    define('AUTH_LEVEL1',   '1');
    define('AUTH_LEVEL2',   '2');
    define('AUTH_LEVEL3',   '3');   // hi

    /* 検索条件 */
    define('KIND_USER',     '0');
    define('KIND_ADDRESS',  '1');
    define('KIND_TRAINING', '2');
    define('KIND_USERID',   '0');
    define('KIND_FULLNAME', '1');
    define('KIND_FASTNAME', '2');
    define('KIND_LASTNAME', '3');
    define('KIND_DISABLE',  '-1');  

    define('INS_IMG',   '../temp/ins');
    define('IND_IMG',   '../temp/ind');
    define('IND',       '../temp/');
?>
