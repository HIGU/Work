<?php
//////////////////////////////////////////////////////////////////////////////
// 指定検収日で指定保管場所の一覧(NKB入庫品)照会         MVC Controller 部  //
// Copyright (C) 2006-2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/06/21 Created   parts_storage_space_Controller.php                  //
// 2006/06/24 workingDayOffset('-0') → workingDayOffset(0, '-')            //
// 2007/03/24 parts/parts_stock_view.php →                                 //
//                   parts/parts_stock_history/parts_stock_history_Main.php //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug 用
require_once ('../../../tnk_func.php');     // workingDayOffset(-1), day_off(), date_offset() で使用

/****************************************************************************
*                       base class 基底クラスの定義                         *
****************************************************************************/
///// namespace Controller {} は現在使用しない 使用例：Controller::prefix_Controller → $obj = new Controller::prefix_Controller;
///// Common::$sum_page, Common::set_page_rec() (名前空間::リテラル)
class PartsStorageSpace_Controller
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
        $this->model = new PartsStorageSpace_Model($this->request);
    }
    
    ///// MVC View部の処理
    public function display()
    {
        //////////// ブラウザーのキャッシュ対策用
        $uniq = $this->menu->set_useNotCache('assyTimeComp');
        
        ////////// HTML Header を出力してキャッシュを制御
        $this->menu->out_html_header();
        
        ////////// MVC の Model部の View用データ生成 ＆ Viewの処理
        switch ($this->request->get('showMenu')) {
        case 'CondForm':                                    // 条件選択フォーム
            $this->CondFormExecute($this->menu, $this->request, $this->model, $uniq);
            break;
        case 'List':                                        // Ajax用 リスト表示
        case 'ListWin':                                     // Ajax用 別ウィンドウでList表示
            // $rows = $this->model->getViewListTable($this->request, $this->result);
            // $res  = $this->result->get_array();
            $this->menu->set_action('在庫経歴',   INDUST . 'parts/parts_stock_history/parts_stock_history_Main.php');
            $this->model->outViewListHTML($this->request, $this->menu);
            $this->ViewListExecute($this->menu, $this->request, $this->model, $uniq);
            break;
        case 'CommentSave':                                 // コメントの保存 ライン番号と年月日がキー
            $this->model->commentSave($this->request);
        case 'Comment':                                     // 別ウィンドウでコメントの照会・編集
            $this->model->getComment($this->request, $this->result);
            require_once ('parts_storage_space_ViewEditComment.php');
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
        // PageKeepの処理
        $this->InitPageKeep($this->request, $this->session);
        // showMenuの処理
        $this->InitShowMenu($this->request);
        // targetDateStrの処理
        $this->InitTargetDateStr($this->request, $this->session);
        // targetDateEndの処理
        $this->InitTargetDateEnd($this->request, $this->session);
        // targetLocateの処理
        $this->InitTargetLocate($this->request, $this->session);
        
        // エラー処理
        if ($this->error) {
            $_SESSION['s_sysmsg'] = $this->errorMsg;
            exit();
        }
    }
    
    /***************************************************************************
    *                               Private methods                            *
    ***************************************************************************/
    ////////// リクエスト・セッション等の初期化処理
    ///// リンク先を照会した場合の戻り値をチェック
    // page_keepを取得してrec_no 及びページ制御の処理
    private function InitPageKeep($request, $session)
    {
        if ($request->get('page_keep') != '') {
            $request->add('showMenu', 'CondForm');
            $request->add('AutoStart', 'on');
            // クリックした行にマーカー用
            if ($session->get_local('rec_no') != '') {
                $request->add('rec_no', $session->get_local('rec_no'));
            }
            // ページ制御用 (呼出した時のページに戻す)
            if ($session->get_local('viewPage') != '') {
                $request->add('CTM_viewPage', $session->get_local('viewPage'));
            }
            if ($session->get_local('pageRec') != '') {
                $request->add('CTM_pageRec', $session->get_local('pageRec'));
            }
        }
    }
    
    ///// メニュー切替用 showMenu のデータチェック ＆ 設定
    // showMenuの処理
    private function InitShowMenu($request)
    {
        $showMenu = $request->get('showMenu');
        if ($showMenu == '') {
            $showMenu = 'CondForm';         // 指定がない場合はCondition Form (条件設定)
        }
        $request->add('showMenu', $showMenu);
    }
    
    ///// 開始年月日の取得・初期化
    // targetDateStrの処理
    private function InitTargetDateStr($request, $session)
    {
        $targetDateStr = $request->get('targetDateStr');
        if ($targetDateStr == '') {
            if ($session->get_local('targetDateStr') == '') {
                $targetDateStr = workingDayOffset(0, '-');        // 指定がない場合は当日(営業日)
            } else {
                $targetDateStr = $session->get_local('targetDateStr');
            }
        }
        $session->add_local('targetDateStr', $targetDateStr);
        $request->add('targetDateStr', $targetDateStr);
        return;
        if (!is_numeric($targetDateStr)) {
            $this->error++;
            $this->errorMsg = '開始日付は数字で入力して下さい。';
        }
        if (strlen($targetDateStr) != 8) {
            $this->error++;
            $this->errorMsg = '開始日付は８桁です。';
        }
    }
    
    ///// 終了年月日の取得・初期化
    // targetDateEndの処理
    private function InitTargetDateEnd($request, $session)
    {
        $targetDateEnd = $request->get('targetDateEnd');
        if ($targetDateEnd == '') {
            if ($session->get_local('targetDateEnd') == '') {
                $targetDateEnd = workingDayOffset(0, '-');        // 指定がない場合は当日(営業日)
            } else {
                $targetDateEnd = $session->get_local('targetDateEnd');
            }
        }
        $session->add_local('targetDateEnd', $targetDateEnd);
        $request->add('targetDateEnd', $targetDateEnd);
        return;
        if (!is_numeric($targetDateEnd)) {
            $this->error++;
            $this->errorMsg = '終了日付は数字で入力して下さい。';
        }
        if (strlen($targetDateEnd) != 8) {
            $this->error++;
            $this->errorMsg = '終了日付は８桁です。';
        }
    }
    
    ///// 入庫場所の取得・初期化
    // targetLocateの処理
    private function InitTargetLocate($request, $session)
    {
        $targetLocate = $request->get('targetLocate');
        if ($targetLocate == '') {
            if ($session->get_local('targetLocate') == '') {
                $targetLocate = '14';        // 指定がない場合はNKB
            } else {
                $targetLocate = $session->get_local('targetLocate');
            }
        }
        $session->add_local('targetLocate', $targetLocate);
        $request->add('targetLocate', $targetLocate);
        if (!is_numeric($targetLocate)) {
            $this->error++;
            $this->errorMsg = '終了日付は数字で入力して下さい。';
        }
        if (strlen($targetLocate) != 2) {
            $this->error++;
            $this->errorMsg = '終了日付は２桁です。';
        }
        return;
    }
    
    
    /***** display()の Private methods 処理 *****/
    ///// 条件選択フォームの表示
    private function CondFormExecute($menu, $request, $model, $uniq)
    {
        require_once ('parts_storage_space_ViewCondForm.php');
        return true;
    }
    
    /***** display()の Private methods 処理 *****/
    ///// 自分のウィンドウにAjax表示か別ウィンドウに表示か切替
    private function ViewListExecute($menu, $request, $model, $uniq)
    {
        switch ($request->get('showMenu')) {
        case 'List':
            require_once ('parts_storage_space_ViewListAjax.php');
            break;
        case 'ListWin':
        default:
            require_once ('parts_storage_space_ViewListWin.php');
        }
        return true;
    }
    
} // class PartsStorageSpace_Controller End

?>
