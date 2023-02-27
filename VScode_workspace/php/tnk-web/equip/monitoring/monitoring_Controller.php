<?php
////////////////////////////////////////////////////////////////////////////////
// 機械稼働管理指示メンテナンス                                               //
//                                                         MVC Controller 部  //
// Copyright (C) 2021-2021 Ryota.waki ryota_waki@nitto-kohki.co.jp            //
// Changed history                                                            //
// 2021/03/24 Created monitoring_Controller.php                               //
// 2021/03/24 Release.                                                        //
////////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL);       // E_STRICT=2048(php5) E_ALL=2047 debug 用

/****************************************************************************
*                       base class 基底クラスの定義                         *
****************************************************************************/
///// namespace Controller {} は現在使用しない 使用例：Controller::equipController → $obj = new Controller::equipController;
///// Common::$sum_page, Common::set_page_rec() (名前空間::リテラル)
class Monitoring_Controller
{
    ///// Private properties

    /************************************************************************
    *                               Public methods                          *
    ************************************************************************/
    ///// Constructer の定義 {php5 移行は __construct() に変更} {デストラクタ__destruct()}
    public function __construct($request, $model)
    {
        //////////// POST Data の初期化＆設定
        ///// メニュー切替用 リクエスト データ取得
    }
    
    ///// MVC View部の処理
    public function display($menu, $request, $result, $model)
    {
        //////////// ブラウザーのキャッシュ対策用
        $uniq = $menu->set_useNotCache('monitoring');
        
        ///// メニュー切替 リクエスト データ取得
        
        ////////// MVC の Model部の View部に渡すデータ生成
        
        ////////// HTML Header を出力してキャッシュを制御
        $menu->out_html_header();
        
        ////////// MVC の View 部の処理
        require_once ('monitoring_ViewMain.php');
    }
    
    /***************************************************************************
    *                              Protected methods                           *
    ***************************************************************************/

    /***************************************************************************
    *                               Private methods                            *
    ***************************************************************************/

} // End off Class Monitoring_Controller

?>
