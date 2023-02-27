<?php
//////////////////////////////////////////////////////////////////////////////
// 刻印管理システム 貸出台帳メニュー                     MVC Controller 部  //
// Copyright (C) 2007      Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2007/11/16 Created   punchMark_lendList_Controller.php                   //
// 2007/11/26 LendRegist(貸出フォーム)追加。Init()に貸出先・担当者を追加    //
// 2007/12/03 貸出日targetLendDateを追加 $modelのsql関連メソッドをdisplayへ //
// 2007/12/05 貸出票の印刷 LendPrintExecute()メソッドを追加                 //
//////////////////////////////////////////////////////////////////////////////

/****************************************************************************
*                       base class 基底クラスの定義                         *
****************************************************************************/
///// namespace Controller {} は現在使用しない 使用例：Controller::prefix_Controller → $obj = new Controller::prefix_Controller;
///// Common::$sum_page, Common::set_page_rec() (名前空間::リテラル)
class PunchMarkLendList_Controller
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
        $this->model = new PunchMarkLendList_Model();
    }
    
    ///// MVC Control部 実行ロジック切替の処理
    public function execute()
    {
        //////////// リクエスト・セッション等の初期化処理
        ///// メニュー切替用 showMenu 等のデータチェック ＆ 設定 (Modelで使用する)
        $this->Init($this->request, $this->session);
        ///// リクエストのアクション処理
        switch ($this->request->get('Action')) {
        case 'MarkSearch':                                  // 検索実行(刻印)
            $this->model->setMarkWhere($this->session);
            $this->model->setMarkSQL($this->session);
            break;
        case 'LendSearch':                                  // 貸出台帳
            $this->model->setLendWhere($this->session);
            $this->model->setLendOrder($this->session);
            $this->model->setLendSQL($this->session);
            break;
        case 'LendRegist':                                  // 貸出フォームデータ取得
            $this->model->getLend($this->session, $this->result);
            break;
        case 'Lend':                                        // 貸出実行
            $this->model->setLend($this->session);
            break;
        case 'LendCancel':                                  // 貸出の取消
            $this->model->setLendCancel($this->session);
            break;
        case 'Return':                                      // 返却実行
            $this->model->setReturn($this->session);
            break;
        case 'ReturnCancel':                                // 返却の取消
            $this->model->setReturnCancel($this->session);
            break;
        }
    }
    
    ///// MVC Control部 View切替の処理
    public function display()
    {
        //////////// ブラウザーのキャッシュ対策用
        $uniq = $this->menu->set_useNotCache('punchMarkLendList');
        
        ////////// HTML Header を出力してキャッシュを制御
        $this->menu->out_html_header();
        
        ////////// MVC の Model部の View用データ生成 ＆ Viewの処理
        switch ($this->request->get('showMenu')) {
        case 'CondForm':                                    // 条件選択フォーム
            $this->CondFormExecute($this->menu, $this->session, $this->model, $this->request, $uniq);
            break;
        case 'MarkList':                                    // Ajax用 リスト表示
        case 'MarkListWin':                                 // Ajax用 別ウィンドウでList表示
            $this->model->setMarkWhere($this->session);
            $this->model->setMarkSQL($this->session);
            $this->model->outViewMarkListHTML($this->session, $this->menu);
            $this->ViewMarkListExecute($this->menu, $this->session, $this->model, $this->request, $uniq);
            break;
        case 'LendList':                                    // Ajax用 リスト表示
        case 'LendListWin':                                 // Ajax用 別ウィンドウでList表示
            $this->model->setLendWhere($this->session);
            $this->model->setLendOrder($this->session);
            $this->model->setLendSQL($this->session);
            $this->model->outViewLendListHTML($this->session, $this->menu);
            $this->ViewLendListExecute($this->menu, $this->session, $this->model, $this->request, $uniq);
            break;
        case 'LendRegistForm':                              // 貸出登録フォーム
            $this->LendRegistFormExecute($this->menu, $this->session, $this->result, $this->request, $uniq);
            break;
        case 'LendPrint':                                   // 刻印貸出票の印刷
            $this->LendPrintExecute($this->menu, $this->session, $this->model);
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
        
        // 部品番号の処理
        $this->InitTargetPartsNo($request, $session);
        // 刻印コードの処理
        $this->InitTargetMarkCode($request, $session);
        // 棚番の処理
        $this->InitTargetShelfNo($request, $session);
        // 備考の処理
        $this->InitTargetNote($request, $session);
        // 貸出先の処理(登録時に使用)
        $this->InitTargetVendor($request, $session);
        // 担当者の処理(登録時に使用)
        $this->InitTargetLendUser($request, $session);
        // 貸出日の処理(返却の登録時に使用)
        $this->InitTargetLendDate($request, $session);
        
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
        // 今回は処理が必要ないので何もしない
        return;
        if ($request->get('page_keep') != '') {
            $request->add('showMenu', 'List');  // ページ制御が必要なので強制的にListにする
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
    
    ///// 部品番号の取得・初期化
    private function InitTargetPartsNo($request, $session)
    {
        if ($request->get('targetPartsNo') == '') {
            if ($session->get_local('targetPartsNo') == '') {
                return;     // 指定が無い場合は何もしない
            } else {
                // リクエストがなくてページ復元以外ならセッションをクリアー
                if ($request->get('page_keep') == '') $session->add_local('targetPartsNo', '');
            }
        } else {
            // 全角入力防止
            $request->add('targetPartsNo', mb_convert_kana($request->get('targetPartsNo'), 'a'));
            $session->add_local('targetPartsNo', $request->get('targetPartsNo'));
        }
        // エラーチェックが必要なリクエストはここに記述
    }
    
    ///// 刻印コードの取得・初期化
    private function InitTargetMarkCode($request, $session)
    {
        if ($request->get('targetMarkCode') == '') {
            if ($session->get_local('targetMarkCode') == '') {
                return;     // 指定が無い場合は何もしない
            } else {
                // リクエストがなくてページ復元以外ならセッションをクリアー
                if ($request->get('page_keep') == '') $session->add_local('targetMarkCode', '');
            }
        } else {
            // 全角入力防止
            $request->add('targetMarkCode', mb_convert_kana($request->get('targetMarkCode'), 'a'));
            $session->add_local('targetMarkCode', $request->get('targetMarkCode'));
        }
        // エラーチェックが必要なリクエストはここに記述
    }
    
    ///// 棚番の取得・初期化
    private function InitTargetShelfNo($request, $session)
    {
        if ($request->get('targetShelfNo') == '') {
            if ($session->get_local('targetShelfNo') == '') {
                return;     // 指定が無い場合は何もしない
            } else {
                // リクエストがなくてページ復元以外ならセッションをクリアー
                if ($request->get('page_keep') == '') $session->add_local('targetShelfNo', '');
            }
        } else {
            // 全角入力防止
            $request->add('targetShelfNo', mb_convert_kana($request->get('targetShelfNo'), 'a'));
            $session->add_local('targetShelfNo', $request->get('targetShelfNo'));
        }
        // エラーチェックが必要なリクエストはここに記述
    }
    
    ///// 備考の取得・初期化
    private function InitTargetNote($request, $session)
    {
        if ($request->get('targetNote') == '') {
            if ($session->get_local('targetNote') == '') {
                return;     // 指定が無い場合は何もしない
            } else {
                // リクエストがなくてページ復元以外ならセッションをクリアー
                if ($request->get('page_keep') == '') $session->add_local('targetNote', '');
            }
        } else {
            // 全角入力防止
            // $request->add('targetNote', mb_convert_kana($request->get('targetNote'), 'a'));
            $session->add_local('targetNote', $request->get('targetNote'));
        }
        // エラーチェックが必要なリクエストはここに記述
    }
    
    ///// 貸出先の取得・初期化 (登録時)
    private function InitTargetVendor($request, $session)
    {
        if ($request->get('targetVendor') == '') {
            if ($session->get_local('targetVendor') == '') {
                return;     // 指定が無い場合は何もしない
            } else {
                // リクエストがなくてページ復元以外ならセッションをクリアー
                if ($request->get('page_keep') == '') $session->add_local('targetVendor', '');
            }
        } else {
            // 全角入力防止
            $request->add('targetVendor', mb_convert_kana($request->get('targetVendor'), 'a'));
            $session->add_local('targetVendor', $request->get('targetVendor'));
        }
        // エラーチェックが必要なリクエストはここに記述
    }
    
    ///// 担当者の取得・初期化 (登録時)
    private function InitTargetLendUser($request, $session)
    {
        if ($request->get('targetLendUser') == '') {
            if ($session->get_local('targetLendUser') == '') {
                return;     // 指定が無い場合は何もしない
            } else {
                // リクエストがなくてページ復元以外ならセッションをクリアー
                if ($request->get('page_keep') == '') $session->add_local('targetLendUser', '');
            }
        } else {
            // 全角入力防止
            $request->add('targetLendUser', mb_convert_kana($request->get('targetLendUser'), 'a'));
            $session->add_local('targetLendUser', $request->get('targetLendUser'));
        }
        // エラーチェックが必要なリクエストはここに記述
    }
    
    ///// 貸出日の取得・初期化 (登録時)
    private function InitTargetLendDate($request, $session)
    {
        if ($request->get('targetLendDate') == '') {
            if ($session->get_local('targetLendDate') == '') {
                return;     // 指定が無い場合は何もしない
            } else {
                // リクエストがなくてページ復元以外ならセッションをクリアー
                if ($request->get('page_keep') == '') $session->add_local('targetLendDate', '');
            }
        } else {
            // 全角入力防止 → クライアントからのデータ入力はないためコメントアウト
            // $request->add('targetLendDate', mb_convert_kana($request->get('targetLendDate'), 'a'));
            $session->add_local('targetLendDate', $request->get('targetLendDate'));
        }
        // エラーチェックが必要なリクエストはここに記述
    }
    
    
    /***** display()の Private methods 処理 *****/
    ///// 条件選択フォームの表示
    private function CondFormExecute($menu, $session, $model, $request, $uniq)
    {
        require_once ('punchMark_lendList_ViewCondForm.php');
        return true;
    }
    
    /***** display()の Private methods 処理 *****/
    ///// 自分のウィンドウにAjax表示か別ウィンドウに表示か切替
    ///// 刻印 検索結果 リスト
    private function ViewMarkListExecute($menu, $session, $model, $request, $uniq)
    {
        switch ($request->get('showMenu')) {
        case 'MarkList':
            require_once ('punchMark_markList_ViewListAjax.php');
            break;
        case 'MarkListWin':
        default:
            require_once ('punchMark_markList_ViewListWin.php');
        }
        return true;
    }
    
    ///// 自分のウィンドウにAjax表示か別ウィンドウに表示か切替
    ///// 貸出台帳
    private function ViewLendListExecute($menu, $session, $model, $request, $uniq)
    {
        switch ($request->get('showMenu')) {
        case 'LendList':
            require_once ('punchMark_lendList_ViewListAjax.php');
            break;
        case 'LendListWin':
        default:
            require_once ('punchMark_lendList_ViewListWin.php');
        }
        return true;
    }
    
    ///// 貸出実行(登録)フォーム
    private function LendRegistFormExecute($menu, $session, $result, $request, $uniq)
    {
        require_once ('punchMark_lendList_ViewLendRegist.php');
        return true;
    }
    
    ///// 刻印貸出票の印刷
    private function LendPrintExecute($menu, $session, $model)
    {
        $model->lendPrint($menu, $session);
        return true;
    }
    
} // class PunchMarkLendList_Controller End

?>
