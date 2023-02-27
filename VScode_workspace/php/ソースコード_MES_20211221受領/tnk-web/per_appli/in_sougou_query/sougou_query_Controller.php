<?php
////////////////////////////////////////////////////////////////////////////////
// 総合届（照会）                                                             //
//                                                         MVC Controller 部  //
// Copyright (C) 2020-2020 Ryota.waki ryota_waki@nitto-kohki.co.jp            //
// Changed history                                                            //
// 2020/11/18 Created sougou_query_Controller.php                             //
// 2021/02/12 Release.                                                        //
////////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL);       // E_STRICT=2048(php5) E_ALL=2047 debug 用

/****************************************************************************
*                       base class 基底クラスの定義                         *
****************************************************************************/
///// namespace Controller {} は現在使用しない 使用例：Controller::equipController → $obj = new Controller::equipController;
///// Common::$sum_page, Common::set_page_rec() (名前空間::リテラル)
class Sougou_Query_Controller
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
        if ($request->get('showMenu') == '') {
            $request->add('showMenu', 'List');              // 指定がない場合は一覧表を表示(特に初回)
        }
    }
    
    ///// MVC View部の処理
    public function display($menu, $request, $result, $model)
    {
        //////////// ブラウザーのキャッシュ対策用
        $uniq = $menu->set_useNotCache('sougou');

        ///// メニュー切替 リクエスト データ取得
        $showMenu   = $request->get('showMenu');            // ターゲットメニューを取得

        if( $showMenu != 'List' ) {
            if( !$model->AmanoRun($result, $request) ) {
                $_SESSION['s_sysmsg'] .= '入力情報の更新に失敗しました。';
            }

            if( !$model->CancelRun($result, $request) ) {
                $_SESSION['s_sysmsg'] .= '取り消し処理に失敗しました。';
            }

            if( $request->get('c2') != '') {
                if( $model->getHuzaisyaDataList($result, $request) < 0 )
                $_SESSION['s_sysmsg'] .= '不在者リスト情報取得処理 失敗!!';
            } else {
                if( $model->getViewDataList($result, $request) > 0 ) {
                    ;//$_SESSION['s_sysmsg'] .= '検索ヒット';
                }
            }
        }
        
        $pageControll = $model->out_pageCtlOpt_HTML($menu->out_self());

        ////////// MVC の Model部の View部に渡すデータ生成
        switch ($showMenu) {

        case 'List':                                        // スケジュール 一覧表データ

        }
        
        ////////// HTML Header を出力してキャッシュを制御
        $menu->out_html_header();
        
        ////////// MVC の View 部の処理
        switch ($showMenu) {

        case 'List':                                        // 照会条件 画面
            require_once ('sougou_query_ViewList.php');
            break;
        case 'Results':                                     // 照会結果 画面
            require_once ('sougou_results_View.php');
            break;
        default:                // リクエストデータにエラーの場合は初期値の一覧を表示
            require_once ('sougou_query_ViewList.php');
        }
    }
    
    /***************************************************************************
    *                              Protected methods                           *
    ***************************************************************************/

    /***************************************************************************
    *                               Private methods                            *
    ***************************************************************************/

} // End off Class Sougou_Query_Controller

?>
