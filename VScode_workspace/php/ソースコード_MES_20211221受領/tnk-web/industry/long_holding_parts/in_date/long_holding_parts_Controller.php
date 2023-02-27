<?php
//////////////////////////////////////////////////////////////////////////////
// 長期滞留部品の照会 最終入庫日指定で現在在庫がある物   MVC Controller 部  //
// Copyright (C) 2006-2006 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/04/03 Created   long_holding_parts_Controller.php                   //
// 2006/04/06 集合出庫の範囲及び回数(物の動き)の条件オプションを実装        //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug 用
// require_once ('../../../tnk_func.php');     // workingDayOffset(-1), day_off(), date_offset() で使用

/****************************************************************************
*                       base class 基底クラスの定義                         *
****************************************************************************/
///// namespace Controller {} は現在使用しない 使用例：Controller::equipController → $obj = new Controller::equipController;
///// Common::$sum_page, Common::set_page_rec() (名前空間::リテラル)
class LongHoldingParts_Controller
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
        $this->model = new LongHoldingParts_Model($this->request);
    }
    
    ///// MVC View部の処理
    public function display()
    {
        //////////// ブラウザーのキャッシュ対策用
        $uniq = $this->menu->set_useNotCache('longHolding');
        
        ////////// HTML Header を出力してキャッシュを制御
        $this->menu->out_html_header();
        
        ////////// MVC の Model部の View用データ生成 ＆ Viewの処理
        switch ($this->request->get('showMenu')) {
        case 'CondForm':                                    // 条件選択フォーム
        case 'Both':                                        // フォームとAjax用List(ViewCondForm.phpで処理)
            require_once ('long_holding_parts_ViewCondForm.php');
            break;
        case 'CommentSave':                                 // コメントの保存
            $this->model->commentSave($this->request);
        case 'Comment':                                     // 別ウィンドウでコメントの照会・編集
            $this->model->getComment($this->request, $this->result);
            require_once ('long_holding_parts_ViewEditComment.php');
            break;
        case 'List':                                        // Ajax用 List表示
            // $rows = $this->model->getViewListTable($this->request, $this->result);
            // $res  = $this->result->get_array();
            $this->model->outViewListHTML($this->request, $this->menu);
            require_once ('long_holding_parts_ViewList.php');
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
        // targetDateの処理
        $this->InitTargetDate();
        // targetDateSpanの処理
        $this->InitTargetDateSpan();
        // targetDivisionの処理
        $this->InitTargetDivision();
        // targetSortItemの処理
        $this->InitTargetSortItem();
        // targetOutFlgの処理
        $this->InitTargetOutFlg();
        // targetOutDateの処理
        $this->InitTargetOutDate();
        // targetOutCountの処理
        $this->InitTargetOutCount();
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
    
    ///// 対象 最終 入庫日の年月日 取得・初期化
    // targetDateの処理
    private function InitTargetDate()
    {
        $targetDate = $this->request->get('targetDate');
        if ($targetDate == '') {
            if ($this->session->get_local('targetDate') == '') {
                $targetDate = 24;       // 指定がない場合は２４ヶ月前
            } else {
                $targetDate = $this->session->get_local('targetDate');
            }
        }
        $this->session->add_local('targetDate', $targetDate);
        $this->request->add('targetDate', $targetDate);
        if (!is_numeric($this->request->get('targetDate'))) {
            $this->error = 1;
            $this->errorMsg = '最終入庫日の指定は数字で入力して下さい。';
            return false;
        }
        if (strlen($this->request->get('targetDate')) != 2) {
            $this->error = 1;
            $this->errorMsg = '最終入庫日の月数は２桁です。';
            return false;
        }
        return true;
    }
    
    ///// 対象 最終 入庫日の年月日 取得・初期化
    // targetDateSpanの処理
    private function InitTargetDateSpan()
    {
        $targetDateSpan = $this->request->get('targetDateSpan');
        if ($targetDateSpan == '') {
            if ($this->session->get_local('targetDateSpan') == '') {
                $targetDateSpan = 120;      // 指定がない場合は120ヶ月分(最後まで)
            } else {
                $targetDateSpan = $this->session->get_local('targetDateSpan');
            }
        }
        $this->session->add_local('targetDateSpan', $targetDateSpan);
        $this->request->add('targetDateSpan', $targetDateSpan);
        if (!is_numeric($this->request->get('targetDateSpan'))) {
            $this->error = 1;
            $this->errorMsg = '最終入庫日からの範囲指定は数字で入力して下さい。';
            return false;
        }
        if (strlen($this->request->get('targetDateSpan')) > 3) {
            $this->error = 1;
            $this->errorMsg = '最終入庫日からの範囲月数は桁数は３桁までです。';
            return false;
        }
        return true;
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
                $targetSortItem = 'price';                     // 指定がない場合は金額
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
    
    ///// オプションで出庫日・出庫回数を条件に入れる場合のcheckbox用フラグ 取得・初期化
    // targetOutFlgの処理
    private function InitTargetOutFlg()
    {
        $targetOutFlg = $this->request->get('targetOutFlg');
        if ($targetOutFlg == '') {
            if ($this->session->get_local('targetOutFlg') == '') {
                $targetOutFlg = 'off';       // 指定がない場合はチェックなし
            } else {
                $targetOutFlg = $this->session->get_local('targetOutFlg');
            }
        }
        $this->session->add_local('targetOutFlg', $targetOutFlg);
        $this->request->add('targetOutFlg', $targetOutFlg);
        return true;
    }
    
    ///// 出庫日の何ヶ月前かの指定 取得・初期化
    // targetOutDateの処理
    private function InitTargetOutDate()
    {
        $targetOutDate = $this->request->get('targetOutDate');
        if ($targetOutDate == '') {
            if ($this->session->get_local('targetOutDate') == '') {
                $targetOutDate = '24';      // 指定がない場合は２４ヶ月前
            } else {
                $targetOutDate = $this->session->get_local('targetOutDate');
            }
        }
        $this->session->add_local('targetOutDate', $targetOutDate);
        $this->request->add('targetOutDate', $targetOutDate);
        if (!is_numeric($this->request->get('targetOutDate'))) {
            $this->error = 1;
            $this->errorMsg = '出庫日の月数指定は数字で入力して下さい。';
            return false;
        }
        if (strlen($this->request->get('targetOutDate')) != 2) {
            $this->error = 1;
            $this->errorMsg = '出庫日の月数は２桁です。';
            return false;
        }
        return true;
    }
    
    ///// 集合出庫の回数指定 取得・初期化
    // targetOutCountの処理
    private function InitTargetOutCount()
    {
        $targetOutCount = $this->request->get('targetOutCount');
        if ($targetOutCount == '') {
            if ($this->session->get_local('targetOutCount') == '') {
                $targetOutCount = '0';          // 指定がない場合は０回(動きが無いもの)
            } else {
                $targetOutCount = $this->session->get_local('targetOutCount');
            }
        }
        $this->session->add_local('targetOutCount', $targetOutCount);
        $this->request->add('targetOutCount', $targetOutCount);
        if (!is_numeric($this->request->get('targetOutCount'))) {
            $this->error = 1;
            $this->errorMsg = '集合出庫の回数指定は数字で入力して下さい。';
            return false;
        }
        if (strlen($this->request->get('targetOutCount')) != 1) {
            $this->error = 1;
            $this->errorMsg = '集合出庫の回数は１桁です。';
            return false;
        }
        return true;
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
    
} // class LongHoldingParts_Controller End

?>
