<?php
//////////////////////////////////////////////////////////////////////////////
// 部門別 製造経費及び販管費の照会                       MVC Controller 部  //
// Copyright (C) 2007      Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2007/10/13 Created   act_summary_Controller.php                          //
//////////////////////////////////////////////////////////////////////////////
require_once ('../../tnk_func.php');     // workingDayOffset(-1), day_off(), date_offset() で使用

/****************************************************************************
*                       base class 基底クラスの定義                         *
****************************************************************************/
///// namespace Controller {} は現在使用しない 使用例：Controller::prefix_Controller → $obj = new Controller::prefix_Controller;
///// Common::$sum_page, Common::set_page_rec() (名前空間::リテラル)
class ActSummary_Controller
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
        
        //////////// ビジネスモデル部のインスタンスを生成しプロパティへ登録
        $this->model = new ActSummary_Model();
    }
    
    ///// MVC Control部 実行ロジック切替の処理
    public function execute()
    {
        //////////// リクエスト・セッション等の初期化処理
        ///// メニュー切替用 showMenu 等のデータチェック ＆ 設定 (Modelで使用する)
        $this->Init($this->request, $this->session);
        ///// リクエストのアクション処理
        /***** 今回はアクションは無い
        switch ($this->request->get('Action')) {
        case 'ClearSort':                                   // ソートの解除
            $this->session->add_local('targetSortItem', '');
            $this->request->add('targetSortItem', '');
            break;
        case 'CommentSave':                                 // コメントの保存
            $this->model->setComment($this->request, $this->result, $this->session);
            break;
        case 'EditFactor':                                  // 要因マスターの編集
            $this->model->editFactor($this->request, $this->result, $this->session);
            break;
        case 'DeleteFactor':                                // 要因マスターの削除又は無効化
            $this->model->deleteFactor($this->request, $this->result, $this->session);
            break;
        case 'ActiveFactor':                                // 要因マスターの有効化
            $this->model->activeFactor($this->request, $this->result, $this->session);
            break;
        }
        *****/
        ///// SQL の WHERE区のセット
        if ($this->request->get('showMenu') != 'CondForm') {
            $this->model->setWhere($this->session);
        }
        // $this->model->setOrder($this->session);
        // $this->model->setOffset($this->session);
        // $this->model->setLimit($this->session);
        // $this->model->setSQL($this->session);
    }
    
    ///// MVC Control部 View切替の処理
    public function display()
    {
        //////////// ブラウザーのキャッシュ対策用
        $uniq = $this->menu->set_useNotCache('assyTimeComp');
        
        ////////// HTML Header を出力してキャッシュを制御
        $this->menu->out_html_header();
        
        ////////// MVC の Model部の View用データ生成 ＆ Viewの処理
        switch ($this->request->get('showMenu')) {
        case 'CondForm':                                    // 条件選択フォーム
            $this->CondFormExecute($this->menu, $this->session, $this->model, $this->request, $uniq);
            break;
        case 'List':                                        // Ajax用 リスト表示
        case 'ListWin':                                     // Ajax用 別ウィンドウでList表示
            // $rows = $this->model->getViewListTable($this->request, $this->result);
            // $res  = $this->result->get_array();
            $this->model->outViewListHTML($this->session, $this->menu);
            $this->ViewListExecute($this->menu, $this->session, $this->model, $this->request, $uniq);
            break;
        case 'Comment':                                     // 別ウィンドウでコメントの照会・編集
            $this->model->getComment($this->request, $this->result);
            require_once ('act_summary_ViewEditComment.php');
            break;
        }
    }
    
    /***************************************************************************
    *                              Protected methods                           *
    ***************************************************************************/
    ////////// リクエスト・セッション等の初期化処理
    protected function Init($request, $session)
    {
        ///// メニュー切替用 showMenu 等のデータチェック ＆ 設定
        // PageKeepの処理
        $this->InitPageKeep($request, $session);
        // showMenuの処理
        $this->InitShowMenu($request);
        // targetDateYMの処理
        $this->InitTargetDateYM($request, $session);
        // targetAct_idの処理
        $this->InitTargetAct_id($request, $session);
        
        //////////// 入力内容のエラー情報取得
        $this->errorCheck($request, $session);
    }
    
    ////////// エラー情報を取得してエラーの時は適切なレスポンスを返す
    protected function errorCheck($request, $session)
    {
        if ($this->error != 0) {
            // $request->add('showMenu', 'CondForm');
            $session->add('s_sysmsg', $this->errorMsg);
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
            $request->add('showMenu', 'List');  // 今回はGraphがベースだがページ制御はList時に必要
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
        if ($request->get('showMenu') == '') {
            $request->add('showMenu', 'CondForm');  // 指定がない場合はCondition Form (条件設定)
        }
    }
    
    ///// 対象年月の取得・初期化
    // targetDateYMの処理
    private function InitTargetDateYM($request, $session)
    {
        $targetDateYM = $request->get('targetDateYM');
        if ($targetDateYM == '') {
            if ($session->get_local('targetDateYM') == '') {
                return;     // 指定が無い場合は何もしない
                // $targetDateYM = date('Ym');         // 指定がない場合は当月
            } else {
                $targetDateYM = $session->get_local('targetDateYM');
            }
        }
        $session->add_local('targetDateYM', $targetDateYM);
        $request->add('targetDateYM', $targetDateYM);
        if (!is_numeric($targetDateYM)) {
            $this->error++;
            $this->errorMsg = '対象年月は数字で入力して下さい。';
        }
        if (strlen($targetDateYM) != 6) {
            $this->error++;
            $this->errorMsg = '対象年月は６桁です。';
        }
    }
    
    ///// 部門コードの取得・初期化
    // targetAct_idの処理
    private function InitTargetAct_id($request, $session)
    {
        $targetAct_id = $request->get('targetAct_id');
        if ($targetAct_id == '') {
            if ($session->get_local('targetAct_id') == '') {
                return;     // 指定が無い場合は何もしない
            } else {
                $targetAct_id = $session->get_local('targetAct_id');
            }
        }
        $session->add_local('targetAct_id', $targetAct_id);
        $request->add('targetAct_id', $targetAct_id);
        if (!is_numeric($targetAct_id)) {
            $this->error++;
            $this->errorMsg = '部門コードは数字で入力して下さい。';
        }
        if (strlen($targetAct_id) != 3) {
            $this->error++;
            $this->errorMsg = '部門コードは３桁です。';
        }
    }
    
    
    /***** display()の Private methods 処理 *****/
    ///// 条件選択フォームの表示
    private function CondFormExecute($menu, $session, $model, $request, $uniq)
    {
        require_once ('act_summary_ViewCondForm.php');
        return true;
    }
    
    /***** display()の Private methods 処理 *****/
    ///// 自分のウィンドウにAjax表示か別ウィンドウに表示か切替
    private function ViewListExecute($menu, $session, $model, $request, $uniq)
    {
        switch ($request->get('showMenu')) {
        case 'List':
            require_once ('act_summary_ViewListAjax.php');
            break;
        case 'ListWin':
        default:
            require_once ('act_summary_ViewListWin.php');
        }
        return true;
    }
    
} // class ActSummary_Controller End

?>
