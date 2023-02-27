<?php
//////////////////////////////////////////////////////////////////////////////
// アイテムマスターの品名による前方検索・部分検索        MVC Controller 部  //
// Copyright (C) 2006-2006 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/04/10 Created   item_name_search_Controller.php                     //
// 2006/05/22 材質によるマスター検索を追加 targetItemMaterial               //
// 2006/05/23 在庫チェックオプションを追加 targetStockOption                //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug 用
// require_once ('../../../tnk_func.php');     // workingDayOffset(-1), day_off(), date_offset() で使用

/****************************************************************************
*                       base class 基底クラスの定義                         *
****************************************************************************/
///// namespace Controller {} は現在使用しない 使用例：Controller::equipController → $obj = new Controller::equipController;
///// Common::$sum_page, Common::set_page_rec() (名前空間::リテラル)
class ItemNameSearch_Controller
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
        $this->model = new ItemNameSearch_Model($this->request);
    }
    
    ///// MVC View部の処理
    public function display()
    {
        //////////// ブラウザーのキャッシュ対策用
        $uniq = $this->menu->set_useNotCache('itemName');
        
        ////////// HTML Header を出力してキャッシュを制御
        $this->menu->out_html_header();
        
        ////////// MVC の Model部の View用データ生成 ＆ Viewの処理
        switch ($this->request->get('showMenu')) {
        case 'CondForm':                                    // 条件選択フォーム
        case 'Both':                                        // フォームとAjax用List(ViewCondForm.phpで処理)
            require_once ('item_name_search_ViewCondForm.php');
            break;
        case 'List':                                        // Ajax用 List表示
            // $rows = $this->model->getViewListTable($this->request, $this->result);
            // $res  = $this->result->get_array();
            $this->model->outViewListHTML($this->request, $this->menu);
            require_once ('item_name_search_ViewList.php');
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
        // targetDivisionの処理
        $this->InitTargetDivision();
        // targetSortItemの処理
        $this->InitTargetSortItem();
        // targetItemNameの処理
        $this->InitTargetItemName();
        // targetItemMaterialの処理
        $this->InitTargetItemMaterial();
        // targetStockOptionの処理
        $this->InitTargetStockOption();
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
    
    ///// ソート対象項目の取得・初期化
    // targetSortItemの処理
    private function InitTargetSortItem()
    {
        $targetSortItem = $this->request->get('targetSortItem');
        if ($targetSortItem == '') {
            if ($this->session->get_local('targetSortItem') == '') {
                $targetSortItem = 'noData';                     // 指定がない場合は金額
            } else {
                $targetSortItem = $this->session->get_local('targetSortItem');
            }
        } else {
            ///// 強制的にリストにする。
            $this->request->add('showMenu', 'Both');
            if ($targetSortItem == $this->session->get_local('targetSortItem')) {
                $targetSortItem = 'noData';     // 同じならトグルにして打消す
            }
        }
        $this->session->add_local('targetSortItem', $targetSortItem);
        // noDataならブランクにしてリクエストに書き込む
        if ($targetSortItem == 'noData') $targetSortItem = '';
        $this->request->add('targetSortItem', $targetSortItem);
    }
    
    ///// 品名による検索データ 取得・初期化
    // targetItemNameの処理
    private function InitTargetItemName()
    {
        // Ajaxで送信された２バイト文字のSJISをEUC-JPへ変換
        $targetItemName = mb_convert_encoding($this->request->get('targetItemName'), 'EUC-JP', 'SJIS');
        // 半角カナを全角カナ(濁点を１文字)へ変換
        $targetItemName = mb_convert_kana($targetItemName, 'KV');
        // リクエスト有り無しチェック
        if ($targetItemName == '') {
            // ジャンプ先からの戻り時のチェック
            if ($this->request->get('showMenu') == 'Both') {
                // セッションデータの有り無しチェック
                if ($this->session->get_local('targetItemName') == 'noData') {
                    $targetItemName = 'noData';       // 指定がない場合ブランクの代わりにnoDataを書き込む
                } else {
                    $targetItemName = $this->session->get_local('targetItemName');
                }
            }
        }
        $this->session->add_local('targetItemName', $targetItemName);
        // noDataならブランクにしてリクエストデータへ戻す
        if ($targetItemName == 'noData') $targetItemName = '';
        $this->request->add('targetItemName', $targetItemName);
        return true;
    }
    
    ///// 材質による検索データ 取得・初期化
    // targetItemMaterialの処理
    private function InitTargetItemMaterial()
    {
        // Ajaxで送信された２バイト文字のSJISをEUC-JPへ変換
        $targetItemMaterial = mb_convert_encoding($this->request->get('targetItemMaterial'), 'EUC-JP', 'SJIS');
        // 半角カナを全角カナ(濁点を１文字)へ変換
        $targetItemMaterial = mb_convert_kana($targetItemMaterial, 'KV');
        // リクエスト有り無しチェック
        if ($targetItemMaterial == '') {
            // ジャンプ先からの戻り時のチェック
            if ($this->request->get('showMenu') == 'Both') {
                // セッションデータの有り無しチェック
                if ($this->session->get_local('targetItemMaterial') == 'noData') {
                    $targetItemMaterial = 'noData';       // 指定がない場合ブランクの代わりにnoDataを書き込む
                } else {
                    $targetItemMaterial = $this->session->get_local('targetItemMaterial');
                }
            }
        }
        $this->session->add_local('targetItemMaterial', $targetItemMaterial);
        // noDataならブランクにしてリクエストデータへ戻す
        if ($targetItemMaterial == 'noData') $targetItemMaterial = '';
        $this->request->add('targetItemMaterial', $targetItemMaterial);
        return true;
    }
    
    ///// 在庫チェックオプションの取得・初期化
    // targetStockOptionの処理
    private function InitTargetStockOption()
    {
        ///// targetStockOptionの適正チェック
        switch ($this->request->get('targetStockOption')) {
        case '3':     // 現在在庫あり
        case '2':     // 在庫経歴あり
        case '1':     // 在庫マスターあり
        case '0':     // 在庫を無視する
            break;
        default:
            if ($this->session->get_local('targetStockOption') != '') {
                // セッションデータで復元
                $this->request->add('targetStockOption', $this->session->get_local('targetStockOption'));
            } else {
                $this->request->add('targetStockOption', 0);  // 初期値
            }
        }
        $this->session->add_local('targetStockOption', $this->request->get('targetStockOption'));
        return true;
    }
    
} // class ItemNameSearch_Controller End

?>
