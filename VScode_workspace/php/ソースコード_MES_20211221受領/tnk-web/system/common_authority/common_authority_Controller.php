<?php
//////////////////////////////////////////////////////////////////////////////
// 共通 権限 関係テーブル メンテナンス                   MVC Controller 部  //
// Copyright (C) 2006-2006 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/07/15 Created   common_authority_Controller.php                     //
// 2006/09/06 権限名の修正機能追加に伴い edit/updateDivision  関係を追加    //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug 用
// require_once ('../../tnk_func.php');        // workingDayOffset(-1), day_off(), date_offset() で使用
if (_TNK_DEBUG) access_log(substr(__FILE__, strlen($_SERVER['DOCUMENT_ROOT'])));

/****************************************************************************
*                       base class 基底クラスの定義                         *
****************************************************************************/
///// namespace Controller {} は現在使用しない 使用例：Controller::prefix_Controller → $obj = new Controller::prefix_Controller;
///// Common::$sum_page, Common::set_page_rec() (名前空間::リテラル)
class CommonAuthority_Controller
{
    ///// Private properties
    private $menu;                              // TNK 共用メニュークラスのインスタンス
    private $request;                           // HTTP Controller部のリクエスト インスタンス
    private $result;                            // HTTP Controller部のリザルト   インスタンス
    private $session;                           // HTTP Controller部のセッション インスタンス
    private $model;                             // ビジネスモデル部のインスタンス
    private $uniq;                              // ブラウザーのキャッシュ対策用
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
        $this->model = new CommonAuthority_Model($this->request);
        
        //////////// ブラウザーのキャッシュ対策用
        $this->uniq = $this->menu->set_useNotCache('common_authority');
    }
    
    ///// MVC の Model部 実行ロジックの処理
    public function Execute()
    {
        switch ($this->request->get('Action')) {
        case 'ListDivision':                               // 権限区分リスト指示
            if ($this->request->get('showMenu') == '') $this->request->add('showMenu', 'ListDivision');
            break;
        case 'ListDivHeader':                              // 権限区分ヘッダーリスト指示
            if ($this->request->get('showMenu') == '') $this->request->add('showMenu', 'ListDivHeader');
            break;
        case 'ListDivBody':                                // 権限区分ボディリスト指示
            if ($this->request->get('showMenu') == '') $this->request->add('showMenu', 'ListDivBody');
            break;
        case 'ListID':                                     // 権限IDのリスト指示
            if ($this->request->get('showMenu') == '') $this->request->add('showMenu', 'ListID');
            break;
        case 'ListIDHeader':                               // 権限IDのヘッダーリスト指示
            if ($this->request->get('showMenu') == '') $this->request->add('showMenu', 'ListIDHeader');
            break;
        case 'ListIDBody':                                  // 権限IDのボディリスト指示
            if ($this->request->get('showMenu') == '') $this->request->add('showMenu', 'ListIDBody');
            break;
        case 'AddDivision':                                 // 新規 権限区分 追加
            $this->model->addDivision($this->request);
            if ($this->request->get('showMenu') == '') $this->request->add('showMenu', 'ListDivision');
            break;
        case 'DeleteDivision':                              // 新規 権限区分 削除
            $this->model->deleteDivision($this->request);
            if ($this->request->get('showMenu') == '') $this->request->add('showMenu', 'ListDivision');
            break;
        case 'EditDivision':                                // 権限名 修正
            $this->model->editDivision($this->request, $this->result);
            if ($this->request->get('showMenu') == '') $this->request->add('showMenu', 'ListDivision');
            break;
        case 'UpdateDivision':                              // 権限名 修正登録
            $this->model->updateDivision($this->request, $this->result);
            if ($this->request->get('showMenu') == '') $this->request->add('showMenu', 'ListDivision');
            break;
        case 'AddID':                                       // 新規 権限ID 追加
            $this->model->addID($this->request);
            if ($this->request->get('showMenu') == '') $this->request->add('showMenu', 'ListID');
            break;
        case 'DeleteID':                                    // 新規 権限ID 削除
            $this->model->deleteID($this->request);
            if ($this->request->get('showMenu') == '') $this->request->add('showMenu', 'ListID');
            break;
        case 'ConfirmID':                                   // 新規 権限ID 登録時の確認
            break;
        default:
            // showMenuの処理
            $this->InitShowMenu($this->request);
        }
    }
    
    ///// MVC View部の処理
    public function display()
    {
        ////////// HTML Header を出力してキャッシュを制御
        $this->menu->out_html_header();
        
        ////////// MVC の Model部の View用データ生成 ＆ Viewの処理
        switch ($this->request->get('showMenu')) {
        case 'StartForm':                                   // 基本ページの表示
            $this->viewStartFormExecute($this->menu, $this->request, $this->model, $this->result, $this->uniq);
            break;
        case 'ListDivision':                                // Ajax用 権限区分のリスト表示
            $this->viewListDivisionExecute($this->menu, $this->request, $this->model, $this->result, $this->uniq);
            break;
        case 'ListDivHeader':                               // 権限区分のヘッダーリスト表示
            $this->viewListDivHeaderExecute($this->menu, $this->request, $this->model, $this->result, $this->uniq);
            break;
        case 'ListDivBody':                                 // 権限区分のボディリスト表示
            $this->viewListDivBodyExecute($this->menu, $this->request, $this->model, $this->result, $this->uniq);
            break;
        case 'ListID':                                      // Ajax用 指定された区分でのIDリスト表示
            $this->viewListIDExecute($this->menu, $this->request, $this->model, $this->result, $this->uniq);
            break;
        case 'ListIDHeader':                                // 指定された区分でのIDヘッダーリスト表示
            $this->viewListIDHeaderExecute($this->menu, $this->request, $this->model, $this->result, $this->uniq);
            break;
        case 'ListIDBody':                                  // 指定された区分でのIDボディリスト表示
            $this->viewListIDBodyExecute($this->menu, $this->request, $this->model, $this->result, $this->uniq);
            break;
        case 'ListCategory':                                // 指定されたIDのCategoryリスト表示
            echo $this->model->categorySelectList($this->model->getCategory($this->request));
            break;
        case 'GetIDName':                                   // 指定されたIDとCategoryで内容を表示
            echo $this->model->getIDName($this->request);
            break;
        default:                                            // 不正なリクエストの対応予定
            echo '不正なリクエストです。';
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
        // Actionの処理
        $this->InitAction($this->request);
        
        // targetDivisionの処理
        $this->InitTargetDivision($this->request);
        // targetIDの処理
        $this->InitTargetID($this->request);
        // targetAuthNameの処理
        $this->InitTargetAuthName($this->request);
        
        // エラー処理
        if ($this->error) {
            $_SESSION['s_sysmsg'] = $this->errorMsg;
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
    
    ///// 実行処理用のデーター取得 ＆ 設定
    // Actionの処理
    private function InitAction($request)
    {
        $Action = $request->get('Action');
        if ($request->get('Action') == '') {
            $request->add('Action', 'StartForm');           // 指定がない場合は権限マスターの一覧
        }
    }
    
    ///// メニュー切替用 showMenu のデータチェック ＆ 設定
    // showMenuの処理
    private function InitShowMenu($request)
    {
        if ($request->get('showMenu') == '') {
            $request->add('showMenu', 'StartForm');         // 指定がない場合は権限マスターの一覧
        }
    }
    
    ///// 権限マスターの区分指定取得 ＆ 設定
    // targetDivisionの処理
    private function InitTargetDivision($request)
    {
        if ($request->get('targetDivision') == '') return true;
        if ($request->get('targetDivision') >= 1 && $request->get('targetDivision') <= 32000) {
            return true;
        }
        $this->error = 1;
        $this->errorMsg = '権限マスターの指定が不正です。';
        return false;
    }
    
    ///// 権限のメンバー指定取得 ＆ 設定
    // targetIDの処理
    private function InitTargetID($request)
    {
        if ($request->get('targetID') == '') return true;
        return true;    // targetID は何でも入力可能とする
        
        if ($request->get('targetID') >= 1 && $request->get('targetID') <= 32000) {
            return true;
        }
        $this->error = 1;
        $this->errorMsg = '権限メンバーの指定が不正です。';
        return false;
    }
    
    ///// 権限名の登録データの文字コード変換
    // targetAuthNameの処理
    private function InitTargetAuthName($request)
    {
        if ($request->get('targetAuthName') == '') return true;
        // $targetAuthName = mb_convert_encoding(stripslashes($_REQUEST['targetAuthName']), 'EUC-JP', 'SJIS');
        $request->add('targetAuthName', mb_convert_encoding($request->get('targetAuthName'), 'EUC-JP', 'SJIS'));
        return true;
    }
    
    
    /***** display()の Private methods 処理 *****/
    ///// 権限マスターの一覧表示
    private function viewStartFormExecute($menu, $request, $model, $result, $uniq)
    {
        require_once ('common_authority_ViewStartForm.php');
    }
    
    ///// 権限マスターの一覧表示
    private function viewListDivisionExecute($menu, $request, $model, $result, $uniq)
    {
        require_once ('common_authority_ViewListDivision.php');
    }
    
    ///// 権限マスターのヘッダー表示
    private function viewListDivHeaderExecute($menu, $request, $model, $result, $uniq)
    {
        require_once ('common_authority_ViewDivHeader.php');
    }
    
    ///// 権限マスターのボディ表示
    private function viewListDivBodyExecute($menu, $request, $model, $result, $uniq)
    {
        $rows = $model->getViewListDivision($this->request, $res);
        require_once ('common_authority_ViewDivBody.php');
    }
    
    /***** display()の Private methods 処理 *****/
    ///// 権限のメンバー一覧表示
    private function viewListIDExecute($menu, $request, $model, $result, $uniq)
    {
        require_once ('common_authority_ViewListID.php');
    }
    
    ///// 権限メンバーのヘッダー表示
    private function viewListIDHeaderExecute($menu, $request, $model, $result, $uniq)
    {
        require_once ('common_authority_ViewIDHeader.php');
    }
    
    ///// 権限メンバーのボディ表示
    private function viewListIDBodyExecute($menu, $request, $model, $result, $uniq)
    {
        $rows = $model->getViewListID($this->request, $res);
        require_once ('common_authority_ViewIDBody.php');
    }
    
} // class CommonAuthority_Controller End

?>
