<?php
//////////////////////////////////////////////////////////////////////////////
// プログラム管理メニュー プログラムの検索               MVC Controller 部  //
// Copyright (C) 2010 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp         //
// Changed history                                                          //
// 2010/01/26 Created   progMaster_search_Controller.php                    //
//////////////////////////////////////////////////////////////////////////////

/****************************************************************************
*                       base class 基底クラスの定義                         *
****************************************************************************/
///// namespace Controller {} は現在使用しない 使用例：Controller::prefix_Controller → $obj = new Controller::prefix_Controller;
///// Common::$sum_page, Common::set_page_rec() (名前空間::リテラル)
class ProgMasterSearch_Controller
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
        $this->model = new ProgMasterSearch_Model();
    }
    
    ///// MVC Control部 実行ロジック切替の処理
    public function execute()
    {
        //////////// リクエスト・セッション等の初期化処理
        ///// メニュー切替用 showMenu 等のデータチェック ＆ 設定 (Modelで使用する)
        $this->Init($this->request, $this->session);
        ///// リクエストのアクション処理
        switch ($this->request->get('Action')) {
        case 'Search':                                      // 検索実行
            $this->model->setWhere($this->session);
            $this->model->setSQL($this->session);
            break;
        case 'Sort':                                        // ソート実行
            $this->model->setWhere($this->session);
            $this->model->setOrder($this->session);
            $this->model->setSQL($this->session);
            break;
        case 'SortClear':                                   // ソートの解除
            $this->session->add_local('targetSortItem', '');
            $this->request->add('targetSortItem', '');
            break;
        case 'CommentSave':                                 // コメントの保存
            $this->model->setComment($this->request, $this->result, $this->session);
            break;
        }
        // $this->model->setWhere($this->session);
        // $this->model->setOrder($this->session);
        // $this->model->setOffset($this->session);
        // $this->model->setLimit($this->session);
        // $this->model->setSQL($this->session);
    }
    
    ///// MVC Control部 View切替の処理
    public function display()
    {
        //////////// ブラウザーのキャッシュ対策用
        $uniq = $this->menu->set_useNotCache('progMasterSearch');
        
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
            require_once ('progMaster_search_ViewEditComment.php');
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
        $this->InitTargetProgId($request, $session);
        // 刻印コードの処理
        $this->InitTargetProgMaster_code($request, $session);
        // 棚番の処理
        $this->InitTargetShelf_no($request, $session);
        // 刻印内容の処理
        $this->InitTargetMark($request, $session);
        // 形状コードの処理
        $this->InitTargetDir($request, $session);
        // 客先コードの処理
        $this->InitTargetUser_code($request, $session);
        // サイズコードの処理
        $this->InitTargetSize_code($request, $session);
        // 製作状況の処理
        $this->InitTargetMake_flg($request, $session);
        // 部品マスター備考の処理
        $this->InitTargetNote_parts($request, $session);
        // 刻印マスター備考の処理
        $this->InitTargetNote_mark($request, $session);
        // 形状マスター備考の処理
        $this->InitTargetNote_shape($request, $session);
        // サイズマスター備考の処理
        $this->InitTargetNote_size($request, $session);
        
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
    private function InitTargetProgId($request, $session)
    {
        if ($request->get('pid') == '') {
            if ($session->get_local('pid') == '') {
                return;     // 指定が無い場合は何もしない
            } else {
                // リクエストがなくてページ復元以外ならセッションをクリアー
                if ($request->get('page_keep') == '') $session->add_local('pid', '');
            }
        } else {
            // 全角入力防止
            $request->add('pid', mb_convert_kana($request->get('pid'), 'a'));
            $session->add_local('pid', $request->get('pid'));
        }
        // エラーチェックが必要なリクエストはここに記述
    }
    
    ///// 刻印コードの取得・初期化
    private function InitTargetProgMaster_code($request, $session)
    {
        if ($request->get('db') == '') {
            if ($session->get_local('db') == '') {
                return;     // 指定が無い場合は何もしない
            } else {
                // リクエストがなくてページ復元以外ならセッションをクリアー
                if ($request->get('page_keep') == '') $session->add_local('db', '');
            }
        } else {
            // 全角入力防止
            $request->add('db', mb_convert_kana($request->get('db'), 'a'));
            $session->add_local('db', $request->get('db'));
        }
        // エラーチェックが必要なリクエストはここに記述
    }
    
    ///// 棚番の取得・初期化
    private function InitTargetShelf_no($request, $session)
    {
        if ($request->get('name_comm') == '') {
            if ($session->get_local('name_comm') == '') {
                return;     // 指定が無い場合は何もしない
            } else {
                // リクエストがなくてページ復元以外ならセッションをクリアー
                if ($request->get('page_keep') == '') $session->add_local('name_comm', '');
            }
        } else {
            // 全角入力防止
            //$request->add('name_comm', mb_convert_kana($request->get('name_comm'), 'a'));
            $session->add_local('name_comm', mb_convert_encoding($request->get('name_comm'), 'EUC-JP', 'auto'));
            //$session->add_local('name_comm', $request->get('name_comm'));
        }
        // エラーチェックが必要なリクエストはここに記述
    }
    
    ///// 刻印内容の取得・初期化
    private function InitTargetMark($request, $session)
    {
        if ($request->get('mark') == '') {
            if ($session->get_local('mark') == '') {
                return;     // 指定が無い場合は何もしない
            } else {
                // リクエストがなくてページ復元以外ならセッションをクリアー
                if ($request->get('page_keep') == '') $session->add_local('mark', '');
            }
        } else {
            // 全角入力防止をコメントアウト ∽マークを全角で入力する予定
            // $request->add('mark', mb_convert_kana($request->get('mark'), 'a'));
            ///// AjaxのGETメソッドは SJIS → EUC-JP  POSTメソッドは UTF-8 → EUC-JP へ変換
            $session->add_local('mark', mb_convert_encoding($request->get('mark'), 'EUC-JP', 'auto'));
        }
        // エラーチェックが必要なリクエストはここに記述
    }
    
    ///// 形状コードの取得・初期化
    private function InitTargetDir($request, $session)
    {
        if ($request->get('dir') == '') {
            if ($session->get_local('dir') == '') {
                return;     // 指定が無い場合は何もしない
            } else {
                // リクエストがなくてページ復元以外ならセッションをクリアー
                if ($request->get('page_keep') == '') $session->add_local('dir', '');
            }
        } else {
            // 全角入力防止 <select>で入力しているためコメントアウト
            // $request->add('dir', mb_convert_kana($request->get('dir'), 'a'));
            $session->add_local('dir', $request->get('dir'));
        }
        // エラーチェックが必要なリクエストはここに記述
    }
    
    ///// 客先コードの取得・初期化
    private function InitTargetUser_code($request, $session)
    {
        if ($request->get('user_code') == '') {
            if ($session->get_local('user_code') == '') {
                return;     // 指定が無い場合は何もしない
            } else {
                // リクエストがなくてページ復元以外ならセッションをクリアー
                if ($request->get('page_keep') == '') $session->add_local('user_code', '');
            }
        } else {
            // 全角入力防止
            $request->add('user_code', mb_convert_kana($request->get('user_code'), 'a'));
            $session->add_local('user_code', $request->get('user_code'));
        }
        // エラーチェックが必要なリクエストはここに記述
    }
    
    ///// サイズコードの取得・初期化
    private function InitTargetSize_code($request, $session)
    {
        if ($request->get('size_code') == '') {
            if ($session->get_local('size_code') == '') {
                return;     // 指定が無い場合は何もしない
            } else {
                // リクエストがなくてページ復元以外ならセッションをクリアー
                if ($request->get('page_keep') == '') $session->add_local('size_code', '');
            }
        } else {
            // 全角入力防止 <select>で入力しているためコメントアウト
            // $request->add('size_code', mb_convert_kana($request->get('size_code'), 'a'));
            $session->add_local('size_code', $request->get('size_code'));
        }
        // エラーチェックが必要なリクエストはここに記述
    }
    
    ///// 製作状況の取得・初期化
    private function InitTargetMake_flg($request, $session)
    {
        if ($request->get('make_flg') == '') {
            if ($session->get_local('make_flg') == '') {
                return;     // 指定が無い場合は何もしない
            } else {
                // リクエストがなくてページ復元以外ならセッションをクリアー
                if ($request->get('page_keep') == '') $session->add_local('make_flg', '');
            }
        } else {
            // 全角入力防止 <select>で入力しているためコメントアウト
            // $request->add('make_flg', mb_convert_kana($request->get('make_flg'), 'a'));
            $session->add_local('make_flg', $request->get('make_flg'));
        }
        // エラーチェックが必要なリクエストはここに記述
    }
    
    ///// 部品マスター備考の取得・初期化
    private function InitTargetNote_parts($request, $session)
    {
        if ($request->get('note_parts') == '') {
            if ($session->get_local('note_parts') == '') {
                return;     // 指定が無い場合は何もしない
            } else {
                // リクエストがなくてページ復元以外ならセッションをクリアー
                if ($request->get('page_keep') == '') $session->add_local('note_parts', '');
            }
        } else {
            ///// AjaxのGETメソッドは SJIS → EUC-JP  POSTメソッドは UTF-8 → EUC-JP へ変換
            $session->add_local('note_parts', mb_convert_encoding($request->get('note_parts'), 'EUC-JP', 'auto'));
        }
        // エラーチェックが必要なリクエストはここに記述
    }
    
    ///// 刻印マスター備考の取得・初期化
    private function InitTargetNote_mark($request, $session)
    {
        if ($request->get('note_mark') == '') {
            if ($session->get_local('note_mark') == '') {
                return;     // 指定が無い場合は何もしない
            } else {
                // リクエストがなくてページ復元以外ならセッションをクリアー
                if ($request->get('page_keep') == '') $session->add_local('note_mark', '');
            }
        } else {
            ///// AjaxのGETメソッドは SJIS → EUC-JP  POSTメソッドは UTF-8 → EUC-JP へ変換
            $session->add_local('note_mark', mb_convert_encoding($request->get('note_mark'), 'EUC-JP', 'auto'));
        }
        // エラーチェックが必要なリクエストはここに記述
    }
    
    ///// 形状マスター備考の取得・初期化
    private function InitTargetNote_shape($request, $session)
    {
        if ($request->get('note_shape') == '') {
            if ($session->get_local('note_shape') == '') {
                return;     // 指定が無い場合は何もしない
            } else {
                // リクエストがなくてページ復元以外ならセッションをクリアー
                if ($request->get('page_keep') == '') $session->add_local('note_shape', '');
            }
        } else {
            ///// AjaxのGETメソッドは SJIS → EUC-JP  POSTメソッドは UTF-8 → EUC-JP へ変換
            $session->add_local('note_shape', mb_convert_encoding($request->get('note_shape'), 'EUC-JP', 'auto'));
        }
        // エラーチェックが必要なリクエストはここに記述
    }
    
    ///// サイズマスター備考の取得・初期化
    private function InitTargetNote_size($request, $session)
    {
        if ($request->get('note_size') == '') {
            if ($session->get_local('note_size') == '') {
                return;     // 指定が無い場合は何もしない
            } else {
                // リクエストがなくてページ復元以外ならセッションをクリアー
                if ($request->get('page_keep') == '') $session->add_local('note_size', '');
            }
        } else {
            ///// AjaxのGETメソッドは SJIS → EUC-JP  POSTメソッドは UTF-8 → EUC-JP へ変換
            $session->add_local('note_size', mb_convert_encoding($request->get('note_size'), 'EUC-JP', 'auto'));
        }
        // エラーチェックが必要なリクエストはここに記述
    }
    
    
    /***** display()の Private methods 処理 *****/
    ///// 条件選択フォームの表示
    private function CondFormExecute($menu, $session, $model, $request, $uniq)
    {
        require_once ('progMaster_search_ViewCondForm.php');
        return true;
    }
    
    /***** display()の Private methods 処理 *****/
    ///// 自分のウィンドウにAjax表示か別ウィンドウに表示か切替
    private function ViewListExecute($menu, $session, $model, $request, $uniq)
    {
        switch ($request->get('showMenu')) {
        case 'List':
            require_once ('progMaster_search_ViewListAjax.php');
            break;
        case 'ListWin':
        default:
            require_once ('progMaster_search_ViewListWin.php');
        }
        return true;
    }
    
} // class ProgMasterSearch_Controller End

?>
