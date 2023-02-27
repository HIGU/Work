<?php
//////////////////////////////////////////////////////////////////////////////
// 設備稼働管理 MVC の Controller 部に必要なクラス HTTP 用                  //
// Copyright (C) 2005-2018 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2005/07/28 Created   EquipControllerHTTP.php                             //
//            Request Class と Result Class はそのままSssion Classを拡張する//
// 2018/05/18 ７工場を追加。コード４の登録を強制的に7に変更            大谷 //
// 2018/12/25 ７工場を真鍮とSUSに分離。後々の為。                      大谷 //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
require_once ('/home/www/html/tnk-web/ControllerHTTP_Class.php');   // 共用HTTP用コントロールクラス


/***********
if (SESSION_VERSION != '1.00') {
    $_SESSION['s_sysmsg'] = 'セッションクラスのバージョンが違います。';
    return;
}
***********/
/****************************************************************************
*                       sub class 拡張クラスの定義                          *
****************************************************************************/
class equipSession extends Session
{
    ///// Private properties
    private $factory;
    private $factory_name;
    
    /************************************************************************
    *                               Public methods                          *
    ************************************************************************/
    ///// Constructer の定義 (php5へ移行時は __construct() へ変更予定) (デストラクタ__destruct())
    public function __construct()
    {
        $this->factory = $this->get('factory');
        $this->factory_name = $this->getFactoryName($this->factory);
    }
    /*************************** Get methods ************************/
    public function getFactoryName($factory)
    {
        switch ($factory) {
        case 1:
            $fact_name = '１工場';
            break;
        case 2:
            $fact_name = '２工場';
            break;
        case 4:
            $fact_name = '４工場';
            break;
        case 5:
            $fact_name = '５工場';
            break;
        case 6:
            $fact_name = '６工場';
            break;
        case 7:
            $fact_name = '７工場(真鍮)';
            break;
        case 8:
            $fact_name = '７工場(SUS)';
            break;
        default:
            $fact_name = '全工場';
            break;
        }
        return $fact_name;
    }
    public function getFactory()
    {
        return $this->factory;
    }
    public function getFactName()
    {
        return $this->factory_name;
    }
} // Class Session End

?>
