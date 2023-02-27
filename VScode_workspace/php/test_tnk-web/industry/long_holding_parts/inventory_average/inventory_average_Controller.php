<?php
//////////////////////////////////////////////////////////////////////////////
// 資材在庫部品 全品種の月平均出庫数・保有月数等照会      MVC Controller 部 //
// Copyright (C) 2007-2016 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2007/05/23 Created   inventory_average_Controller.php                    //
// 2007/06/08 デフォルトのソートアイテムを在庫金額に変更                    //
// 2007/06/11 publicメソッドExecute()を追加                                 //
// 2007/06/14 要因マスターの編集・コメント・要因の登録編集 関連 完了        //
// 2007/07/11 部品番号(searchPartsNo)のLIKE検索追加。                       //
// 2007/07/24 保有月の指定をセッションに追加(フィルター機能)targetHoldMonth //
// 2016/06/24 CSV出力のためSQLのWhere句を取得。                        大谷 //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug 用
// require_once ('../../../tnk_func.php');     // workingDayOffset(-1), day_off(), date_offset() で使用

/****************************************************************************
*                       base class 基底クラスの定義                         *
****************************************************************************/
///// namespace Controller {} は現在使用しない 使用例：Controller::equipController → $obj = new Controller::equipController;
///// Common::$sum_page, Common::set_page_rec() (名前空間::リテラル)
class InventoryAverage_Controller
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
        $this->model = new InventoryAverage_Model($this->request);
    }
    
    ///// MVC の Model部 実行ロジック切替の処理
    public function Execute()
    {
        switch ($this->request->get('Action')) {
        case 'CommentSave':                                 // コメントの保存
            $this->model->commentSave($this->request, $this->result, $this->session);
            break;
        case 'EditFactor':                                  // 要因マスターの編集
            $this->model->editFactor($this->request, $this->result, $this->session);
            break;
        case 'DeleteFactor':                                // 要因マスターの削除
            $this->model->deleteFactor($this->request, $this->result, $this->session);
            break;
        case 'ActiveFactor':                                // 要因マスターの削除
            $this->model->activeFactor($this->request, $this->result, $this->session);
            break;
        }
    }
    
    ///// MVC View部の切替処理
    public function display()
    {
        //////////// ブラウザーのキャッシュ対策用
        $uniq = $this->menu->set_useNotCache('inventaverage');
        
        ////////// HTML Header を出力してキャッシュを制御
        $this->menu->out_html_header();
        
        ////////// MVC の Model部の View用データ生成 ＆ Viewの処理
        switch ($this->request->get('showMenu')) {
        case 'CondForm':                                    // 条件選択フォーム
        case 'Both':                                        // フォームとAjax用List(ViewCondForm.phpで処理)
            $this->viewCondFormExecute($this->menu, $this->request, $uniq);
            break;
        case 'List':                                        // Ajax用 List表示
            $this->viewListExecute($this->menu, $this->request, $this->model, $this->session, $uniq);
            break;
        case 'Comment':                                     // 別ウィンドウでコメントの照会・編集
            $this->viewEditCommentExecute($this->menu, $this->request, $this->model, $this->result, $uniq);
            break;
        case 'FactorMnt':                                   // 要因マスターの照会・編集
            $this->viewEditFactorExecute($this->menu, $this->request, $this->model, $this->session, $uniq);
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
        $this->InitPageKeep();
        // showMenuの処理
        $this->InitShowMenu();
        // searchPartsNoの処理
        $this->InitSearchPartsNo();
        // targetDivisionの処理
        $this->InitTargetDivision();
        // targetHoldMonthの処理
        $this->InitTargetHoldMonth();
        // targetSortItemの処理
        $this->InitTargetSortItem();
        // targetPartsNoの処理 コメントの照会・編集用
        $this->InitTargetPartsNo();
    }
    
    /***************************************************************************
    *                               Private methods                            *
    ***************************************************************************/
    ////////// リクエスト・セッション等の初期化処理
    ///// リンク先を照会した場合の戻り値をチェック
    // page_keepを取得してrec_no 及びページ制御の処理
    private function InitPageKeep()
    {
        if ($this->request->get('page_keep') != '') {
            $this->request->add('showMenu', 'Both');
            // クリックした行にマーカー用
            if ($this->session->get_local('rec_no') != '') {
                $this->request->add('rec_no', $this->session->get_local('rec_no'));
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
    
    ///// メニュー切替用 showMenu のデータチェック ＆ 設定
    // showMenuの処理
    private function InitShowMenu()
    {
        $showMenu = $this->request->get('showMenu');
        if ($showMenu == '') {
            $showMenu = 'CondForm';         // 指定がない場合はCondition Form (条件設定)
        }
        $this->request->add('showMenu', $showMenu);
    }
    
    ///// 部品番号の取得・初期化
    // searchPartsNoの処理
    private function InitSearchPartsNo()
    {
        if ($this->request->get('searchPartsNo') == '') {
            return true;
        }
        if (strlen($this->request->get('searchPartsNo')) > 9) return false; else return true;
    }
    
    ///// 製品区分の取得・初期化
    // targetDivisionの処理
    private function InitTargetDivision()
    {
        $targetDivision = $this->request->get('targetDivision');
        if ($targetDivision == '') {
            if ($this->session->get_local('targetDivision') == '') {
                $targetDivision = 'AL';                     // 指定がない場合は全体(全て)
            } else {
                $targetDivision = $this->session->get_local('targetDivision');
            }
        }
        $this->session->add_local('targetDivision', $targetDivision);
        $this->request->add('targetDivision', $targetDivision);
        if (is_numeric($this->request->get('targetDivision'))) {
            $this->error = 1;
            $this->errorMsg = '製品グループの指定はアルファベット大文字２文字です。';
            return false;
        }
        if (strlen($this->request->get('targetDivision')) != 2) {
            $this->error = 1;
            $this->errorMsg = '製品グループの指定はアルファベット大文字２文字です。';
            return false;
        }
        return true;
    }
    
    ///// 保有月の指定
    // targetHoldMonthの処理
    private function InitTargetHoldMonth()
    {
        $targetHoldMonth = $this->request->get('targetHoldMonth');
        if ($targetHoldMonth == '') {
            if ($this->session->get_local('targetHoldMonth') == '') {
                $targetHoldMonth = '0';             // 指定がない場合は保有月を指定しない
            } else {
                $targetHoldMonth = $this->session->get_local('targetHoldMonth');
            }
        }
        $this->session->add_local('targetHoldMonth', $targetHoldMonth);
        $this->request->add('targetHoldMonth', $targetHoldMonth);
    }
    
    ///// ソート対象項目の取得・初期化
    // targetSortItemの処理
    private function InitTargetSortItem()
    {
        $targetSortItem = $this->request->get('targetSortItem');
        if ($targetSortItem == '') {
            if ($this->session->get_local('targetSortItem') == '') {
                $targetSortItem = 'money';                     // 指定がない場合は在庫金額
            } else {
                $targetSortItem = $this->session->get_local('targetSortItem');
            }
        } else {
            ///// 強制的にリストにする。
            $this->request->add('showMenu', 'Both');
        }
        $this->session->add_local('targetSortItem', $targetSortItem);
        $this->request->add('targetSortItem', $targetSortItem);
    }
    
    ///// 部品毎のコメントの照会・編集用 部品番号パラメータ 取得・設定
    // targetPartsNoの処理
    private function InitTargetPartsNo()
    {
        ;
        if ($this->request->get('targetPartsNo') == '') {
            return true;          // 指定がない場合は何もしない。
        }
        if (!is_numeric(substr($this->request->get('targetPartsNo'), 2, 5))) {
            $this->error = 1;
            $this->errorMsg = '部品番号の３桁から７桁までは数字で入力して下さい。';
            return false;
        }
        if (strlen($this->request->get('targetPartsNo')) != 9) {
            $this->error = 1;
            $this->errorMsg = '部品番号は９桁です。';
            return false;
        }
        return true;
    }
    
    /***** display()の Private methods 処理 *****/
    ///// Condition Form の表示 と Both(Ajaxの両方実行)を兼用
    private function viewCondFormExecute($menu, $request, $uniq)
    {
        require_once ('inventory_average_ViewCondForm.php');
    }
    
    ///// 一覧表示
    private function viewListExecute($menu, $request, $model, $session, $uniq)
    {
        $pageControl = $model->out_pageCtlOpt_HTML($menu->out_self()."?id={$uniq}");
        $pageParameter = $model->get_htmlGETparm() ."&id={$uniq}";
        $model->outViewListHTML($request, $menu, $pageParameter, $session);
        $csv_where   = $session->get('csv_where');
        $pageControl = mb_convert_encoding($pageControl, 'UTF-8');
        // $pageParameter = mb_convert_encoding($pageParameter, 'UTF-8');
        require_once ('inventory_average_ViewList.php');
    }
    
    ///// コメントの編集ウィンドウ表示
    private function viewEditCommentExecute($menu, $request, $model, $result, $uniq)
    {
        $model->getComment($this->request, $this->result);
        $model->getFactorOptions($this->request, $this->result);
        require_once ('inventory_average_ViewEditComment.php');
    }
    
    ///// 要因マスターの編集ウィンドウ表示
    private function viewEditFactorExecute($menu, $request, $model, $session, $uniq)
    {
        $model->outViewFactorHTML($request, $menu, $session);
        require_once ('inventory_average_ViewEditFactor.php');
    }
    
} // class InventoryAverage_Controller End

?>
