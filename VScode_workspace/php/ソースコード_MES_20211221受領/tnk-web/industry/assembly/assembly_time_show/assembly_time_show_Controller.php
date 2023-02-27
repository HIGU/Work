<?php
//////////////////////////////////////////////////////////////////////////////
// 組立の登録工数と実績工数の比較 照会               MVC Controller 部      //
// Copyright (C) 2006-2006 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/03/01 Created   assembly_time_show_Controller.php                   //
// 2006/03/03 他のメニューからの遷移の場合の設定を追加 InitShowMenu()の中   //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug 用

/****************************************************************************
*                       base class 基底クラスの定義                         *
****************************************************************************/
///// namespace Controller {} は現在使用しない 使用例：Controller::equipController → $obj = new Controller::equipController;
///// Common::$sum_page, Common::set_page_rec() (名前空間::リテラル)
class AssemblyTimeShow_Controller
{
    ///// Private properties
    private $menu;                              // TNK 共用メニュークラスのインスタンス
    private $request;                           // HTTP Controller部のリクエスト インスタンス
    private $result;                            // HTTP Controller部のリザルト   インスタンス
    private $session;                           // HTTP Controller部のセッション インスタンス
    private $model;                             // ビジネスモデル部のインスタンス
    private $error = 0;                         // エラーコード又はフラグ
    private $errorMsg = '';                     // エラーメッセージ
    /************************************************************************
    *                               Public methods                          *
    ************************************************************************/
    ///// Constructer の定義 {php5 移行は __construct() に変更} {デストラクタ__destruct()}
    public function __construct($menu)
    {
        ///// MenuHeader クラスのインスタンスを properties に登録
        if (is_object($menu)) {
            $this->menu = $menu;
        } else {
            exit();
        }
        //////////// リクエストのインスタンスを登録
        $this->request = new Request();
        
        //////////// リザルトのインスタンスを登録
        $this->result = new Result();
        
        //////////// セッションのインスタンスを登録
        $this->session = new Session();
        
        //////////// リクエスト・セッション等の初期化処理
        ///// メニュー切替用 showMenu 等のデータチェック ＆ 設定 (Modelで使用する)
        $this->Init();
        
        //////////// ビジネスモデル部のインスタンスを生成しプロパティへ登録
        $this->model = new AssemblyTimeShow_Model($this->request);
    }
    
    ///// MVC View部の処理
    public function display()
    {
        //////////// ブラウザーのキャッシュ対策用
        $uniq = $this->menu->set_useNotCache('assyTimeShow');
        
        ////////// HTML Header を出力してキャッシュを制御
        $this->menu->out_html_header();
        
        ////////// MVC の Model部の View用データ生成 ＆ Viewの処理
        switch ($this->request->get('showMenu')) {
        case 'CondForm':                                    // 条件選択フォーム
            require_once ('assembly_time_show_ViewCondForm.php');
            break;
        case 'ListTable':                                   // Ajax用 List表示
            // $rows = $this->model->getViewListTable($this->request, $this->result);
            // $res  = $this->result->get_array();
            require_once ('assembly_time_show_ViewListTable.php');
            break;
        case 'ProcessTable':                                // Ajax用 工程明細表示
            require_once ('assembly_time_show_ViewProcessTable.php');
            break;
        }
    }
    
    /***************************************************************************
    *                              Protected methods                           *
    ***************************************************************************/
    ////////// リクエスト・セッション等の初期化処理
    protected function Init()
    {
        ///// メニュー切替用 showMenu 等のデータチェック ＆ 設定
        // showMenuの処理
        $this->InitShowMenu();
        if ($this->request->get('showMenu') == 'ListTable') {
            // targetPlanNoの処理
            $this->InitTargetPlanNo();
        }
        if ($this->request->get('showMenu') == 'ProcessTable') {
            // targetAssyNoの処理
            $this->InitTargetAssyNo();
            // targetRegNoの処理
            $this->InitTargetRegNo();
        }
        // PageKeepの処理
        $this->InitPageKeep();
    }
    
    /***************************************************************************
    *                               Private methods                            *
    ***************************************************************************/
    ////////// リクエスト・セッション等の初期化処理
    ///// メニュー切替用 showMenu のデータチェック ＆ 設定
    // showMenuの処理
    private function InitShowMenu()
    {
        $showMenu = $this->request->get('showMenu');
        if ($showMenu == '') {
            if ($this->session->get_local('showMenu') == '') {
                $showMenu = 'CondForm';         // 指定がない場合はCondition Form (条件設定)
            } else {
                $showMenu = $this->session->get_local('showMenu');
            }
        }
        // Ajaxの場合はセッションに保存しない
        if ($showMenu != 'ListTable' || $showMenu != 'ProcessTable') {
            // 今回は照会のみの単純フォームなのでセッションは使わない
            // $this->session->add_local('showMenu', $showMenu);
        }
        // 他のメニューからの遷移の場合に設定
        if ($showMenu == 'CondForm') {
            if ($this->request->get('targetPlanNo')) $this->menu->set_retGET('page_keep', 'on');
            $this->session->add('material_plan_no', $this->request->get('targetPlanNo'));
        }
        $this->request->add('showMenu', $showMenu);
    }
    
    ///// 計画番号の取得・初期化
    // targetPlanNoの処理
    private function InitTargetPlanNo()
    {
        $targetPlanNo = $this->request->get('targetPlanNo');
        // 桁数チェック
        if (strlen($targetPlanNo) != 8) {
            $this->error = 1;
            $this->errorMsg = '計画番号の桁数は８桁です。';
            return false;
        }
        if (!is_numeric(substr($targetPlanNo, 2, 6))) {
            $this->error = 1;
            $this->errorMsg = '計画番号の下６桁は数字で入力して下さい。';
            return false;
        }
        // $this->session->add_local('targetPlanNo', $targetPlanNo);
        // $this->request->add('targetPlanNo', $targetPlanNo);
        return true;
    }
    
    ///// 製品番号の取得・初期化
    // targetAssyNoの処理
    private function InitTargetAssyNo()
    {
        $targetAssyNo = $this->request->get('targetAssyNo');
        // 桁数チェック
        if (strlen($targetAssyNo) != 9) {
            $this->error = 1;
            $this->errorMsg = '製品番号の桁数は９桁です。';
            return false;
        }
        if (!is_numeric(substr($targetAssyNo, 2, 5))) {
            $this->error = 1;
            $this->errorMsg = '計画番号の３桁目から７桁までは数字です。';
            return false;
        }
        return true;
    }
    
    ///// 登録番号の取得・初期化
    // targetRegNoの処理
    private function InitTargetRegNo()
    {
        $targetRegNo = $this->request->get('targetRegNo');
        // 桁数チェック
        if (strlen($targetRegNo) <= 7) {
            $this->error = 1;
            $this->errorMsg = '登録番号の桁数は７桁以下です。';
            return false;
        }
        if (!is_numeric($targetRegNo)) {
            $this->error = 1;
            $this->errorMsg = '登録番号は数字です。';
            return false;
        }
        return true;
    }
    
    ///// リンク先を照会した場合の戻り値をチェック
    // page_keepを取得してmaterial_plan_no 及びページ制御の処理
    private function InitPageKeep()
    {
        if ($this->request->get('page_keep') != '') {
            // クリックした計画番号の行にマーカー用
            if ($this->session->get('material_plan_no') != '') {
                $this->request->add('material_plan_no', $this->session->get('material_plan_no'));
            }
            // ページ制御用 (呼出した時のページに戻す)
            if ($this->session->get_local('viewPage') != '') {
                $this->request->add('CTM_viewPage', $this->session->get_local('viewPage'));
            }
            if ($this->session->get_local('pageRec') != '') {
                $this->request->add('CTM_pageRec', $this->session->get_local('pageRec'));
            }
        }
    }
    
} // class AssemblyTimeShow_Controller End

?>
