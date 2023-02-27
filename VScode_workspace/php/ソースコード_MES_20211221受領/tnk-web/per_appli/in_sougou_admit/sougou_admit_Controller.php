<?php
////////////////////////////////////////////////////////////////////////////////
// 総合届（承認）                                                             //
//                                                         MVC Controller 部  //
// Copyright (C) 2020-2020 Ryota.waki ryota_waki@nitto-kohki.co.jp            //
// Changed history                                                            //
// 2020/11/18 Created sougou_admit_Controller.php                             //
// 2021/02/12 Release.                                                        //
////////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL);       // E_STRICT=2048(php5) E_ALL=2047 debug 用

/****************************************************************************
*                       base class 基底クラスの定義                         *
****************************************************************************/
///// namespace Controller {} は現在使用しない 使用例：Controller::equipController → $obj = new Controller::equipController;
///// Common::$sum_page, Common::set_page_rec() (名前空間::リテラル)
class Sougou_Admit_Controller
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

        if ($request->get('sougou_update') == 'on') {
            $model->SougouUpdate($request);
        }

        $model->JyudenUpdate($request);

        $model->admitUpdate($request);
        $model->getEditData($request);
    }
    
    ///// MVC View部の処理
    public function display($menu, $request, $result, $model)
    {
        //////////// ブラウザーのキャッシュ対策用
        $uniq = $menu->set_useNotCache('sougou_admit');

        ///// メニュー切替 リクエスト データ取得
        $showMenu   = $request->get('showMenu');            // ターゲットメニューを取得

        $uid =  $model->getUid();

        $pageControll = $model->out_pageCtlOpt_HTML($menu->out_self());

        $send_uid = $request->get('send_uid');    // 送り先
        if( $send_uid ) {
            $model->AdmitRequestMaile($send_uid);
        }

        ////////// MVC の Model部の View部に渡すデータ生成
        switch ($showMenu) {

        case 'List':                                        // スケジュール 一覧表データ
            if( $uid != '' ) {
                if( $model->getViewDataList($result) > 0 ) {
                    ;
                }
            }
            break;
        case 'Edit':                                        // スケジュール 一覧表データ
            break;
        default:                // リクエストデータにエラーの場合は初期値の一覧を表示
            break;
        }
        
        ////////// HTML Header を出力してキャッシュを制御
        $menu->out_html_header();
        
        ////////// MVC の View 部の処理
        switch ($showMenu) {

        case 'List':                                        // スケジュールの一覧 画面
            require_once ('sougou_admit_ViewList.php');
            break;
        case 'Edit':                                        // スケジュールの一覧 画面
            require_once ('sougou_admit_EditView.php');
            break;
        default:                // リクエストデータにエラーの場合は初期値の一覧を表示
            require_once ('sougou_admit_ViewList.php');
            break;
        }
    }
    
    /***************************************************************************
    *                              Protected methods                           *
    ***************************************************************************/

    /***************************************************************************
    *                               Private methods                            *
    ***************************************************************************/

} // End off Class Sougou_Admit_Controller

?>
