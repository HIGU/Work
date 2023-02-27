<?php
//////////////////////////////////////////////////////////////////////////////
// 組立の作業管理 着手・実績データ 照会         MVC Controller 部           //
// Copyright (C) 2006-2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/01/19 Created   assembly_process_show_Controller.php                //
// 2006/01/20 Mainの showGroup showMenuのチェック・設定をControllerへ移動   //
// 2007/03/26 Init()メソッドを追加し、その中にPageKeep処理を追加            //
//            計画番号クリック時の行番号保存処理を追加(後日計画番号へ変更)  //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug 用

/****************************************************************************
*                       base class 基底クラスの定義                         *
****************************************************************************/
///// namespace Controller {} は現在使用しない 使用例：Controller::equipController → $obj = new Controller::equipController;
///// Common::$sum_page, Common::set_page_rec() (名前空間::リテラル)
class AssemblyProcessShow_Controller
{
    ///// Private properties
    private $model;                             // ビジネスモデル部のインスタンス
    
    /************************************************************************
    *                               Public methods                          *
    ************************************************************************/
    ///// Constructer の定義 {php5 移行は __construct() に変更} {デストラクタ__destruct()}
    public function __construct($request, $session)
    {
        //////////// リクエスト・セッション等の初期化処理
        ///// メニュー切替用 showGroupとshowMenu のデータチェック ＆ 設定 (Modelで使用する)
        $this->Init($request, $session);
        
        //////////// ビジネスモデル部のインスタンスを生成しプロパティへ登録
        $this->model = new AssemblyProcessShow_Model($request);
        $session->add_local('viewPage', $this->model->get_viewPage());
        $session->add_local('pageRec' , $this->model->get_pageRec());
    }
    
    ///// MVC View部の処理
    public function display($menu, $request, $result, $session)
    {
        //////////// ブラウザーのキャッシュ対策用
        $uniq = $menu->set_useNotCache('processShow');
        
        ////////// HTML Header を出力してキャッシュを制御
        $menu->out_html_header();
        
        ////////// MVC の Model部の View用データ生成 ＆ Viewの処理
        $rowsGroup = $this->model->getViewGroupList($result);
        $resGroup  = $result->get_array();
        switch ($request->get('showMenu')) {
        case 'StartList':                                   // 組立着手 一覧表 表示
            $rows = $this->model->getViewStartList($result);
            $res  = $result->get_array();
            // $pageControl = $this->model->out_pageControll_HTML($menu->out_self()."?id={$uniq}");
            $pageControl = $this->model->out_pageCtlOpt_HTML($menu->out_self()."?id={$uniq}");
            $pageParameter = $this->model->get_htmlGETparm() ."&id={$uniq}";
            require_once ('assembly_process_show_ViewStartList.php');
            break;
        case 'StartTable':                                  // 上記のAjax用 表示
            $rows = $this->model->getViewStartList($result);
            $res  = $result->get_array();
            // $pageControl = $this->model->out_pageControll_HTML($menu->out_self()."?id={$uniq}");
            $pageControl = $this->model->out_pageCtlOpt_HTML($menu->out_self()."?id={$uniq}");
            $pageParameter = $this->model->get_htmlGETparm() ."&id={$uniq}";
            require_once ('assembly_process_show_ViewStartTable.php');
            break;
        case 'EndList':                                     // 組立実績 一覧表 表示
            $rows = $this->model->getViewEndList($result);
            $res  = $result->get_array();
            // $pageControl = $this->model->out_pageControll_HTML($menu->out_self()."?id={$uniq}");
            $pageControl = $this->model->out_pageCtlOpt_HTML($menu->out_self()."?id={$uniq}");
            $pageParameter = $this->model->get_htmlGETparm() ."&id={$uniq}";
            require_once ('assembly_process_show_ViewEndList.php');
            break;
        }
    }
    
    /***************************************************************************
    *                              Protected methods                           *
    ***************************************************************************/
    ////////// リクエスト・セッション等の初期化処理
    protected function Init($request, $session)
    {
        ///// メニュー切替用 showMenuとshowLine のデータチェック ＆ 設定
        // showGroupの処理
        $this->InitShowGroup($request, $session);
        // showMenuの処理
        $this->InitShowMenu($request, $session);
        // PageKeepの処理
        $this->InitPageKeep($request, $session);
    }
    
    /***************************************************************************
    *                               Private methods                            *
    ***************************************************************************/
    ////////// リクエスト・セッション等の初期化処理
    ///// メニュー切替用 showGroupとshowMenu のデータチェック ＆ 設定 (ModelとControllerで使用する)
    // showGroupの処理
    private function InitShowGroup($request, $session)
    {
        $showGroup = $request->get('showGroup');
        if ($showGroup == '') {
            if ($session->get_local('showGroup') == '') {
                $showGroup = '';                // 指定がない場合は全てのラインを指定したものと見なす
            } else {
                $showGroup = $session->get_local('showGroup');
            }
        }
        if ($showGroup == '0') $showGroup = ''; // 0 は全体を意味する。
        $session->add_local('showGroup', $showGroup);
        $request->add('showGroup', $showGroup);
    }
    
    ///// メニュー切替用 showGroupとshowMenu のデータチェック ＆ 設定 (ModelとControllerで使用する)
    // showMenuの処理
    private function InitShowMenu($request, $session)
    {
        $showMenu = $request->get('showMenu');
        if ($showMenu == '') {
            if ($session->get_local('showMenu') == '') {
                $showMenu = 'StartList';        // 指定がない場合は着手一覧
            } else {
                $showMenu = $session->get_local('showMenu');
            }
        }
        if ($showMenu != 'StartTable') {    // Ajaxの場合はセッションに保存しない
            $session->add_local('showMenu', $showMenu);
        }
        $request->add('showMenu', $showMenu);
    }
    
    ///// 計画番号で引当構成部品表を照会した場合の戻り値をチェック
    // page_keepを取得してmaterial_plan_no 及びページ制御の処理
    private function InitPageKeep($request, $session)
    {
        if ($request->get('page_keep') != '') {
            // クリックした計画番号の行にマーカー用
            if ($session->get('material_plan_no') != '') {
                $request->add('material_plan_no', $session->get('material_plan_no'));
            }
            // ページ制御用 (呼出した時のページに戻す)
            if ($session->get_local('viewPage') != '') {
                $request->add('CTM_viewPage', $session->get_local('viewPage'));
            }
            if ($session->get_local('pageRec') != '') {
                $request->add('CTM_pageRec', $session->get_local('pageRec'));
            }
        } else {
            $session->add_local('recNo', '-1'); // 初期化
        }
    }
    
} // class AssemblyProcessShow_Controller End

?>
