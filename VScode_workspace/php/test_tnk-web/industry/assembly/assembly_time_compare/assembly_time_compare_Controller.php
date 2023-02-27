<?php
//////////////////////////////////////////////////////////////////////////////
// 組立の完成一覧より実績工数と登録工数の比較            MVC Controller 部  //
// Copyright (C) 2006-2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/03/07 Created   assembly_time_compare_Controller.php                //
// 2006/03/13 製品区分の選択として targetDivision を追加                    //
// 2006/05/01 コメント照会・編集ロジックを追加                              //
// 2006/05/08 コメントの照会・編集用テーブルのキーを製品番号→計画番号へ変更//
// 2006/05/10 手作業・自動機・外注・全体 別に照会オプションを追加           //
// 2006/05/15 InitTargetPlanNo()等に ; だけの行があったのを削除             //
// 2006/08/31 項目ソート機能 追加による InitTargetSortItem() メソッドを実装 //
// 2007/06/12 コメント登録Windowから親ウィンドウの画面更新対応でcommentSave //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug 用
require_once ('../../../tnk_func.php');     // workingDayOffset(-1), day_off(), date_offset() で使用

/****************************************************************************
*                       base class 基底クラスの定義                         *
****************************************************************************/
///// namespace Controller {} は現在使用しない 使用例：Controller::equipController → $obj = new Controller::equipController;
///// Common::$sum_page, Common::set_page_rec() (名前空間::リテラル)
class AssemblyTimeCompare_Controller
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
        $this->model = new AssemblyTimeCompare_Model($this->request);
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
        case 'Both':                                        // フォームとAjax用List(ViewCondForm.phpで処理)
            require_once ('assembly_time_compare_ViewCondForm.php');
            break;
        case 'CommentSave':                                 // コメントの保存
            $this->model->commentSave($this->request, $this->result, $this->session);
        case 'Comment':                                     // 別ウィンドウでコメントの照会・編集
            $this->model->getComment($this->request, $this->result);
            require_once ('assembly_time_compare_ViewEditComment.php');
            break;
        case 'List':                                        // Ajax用 List表示
            // $rows = $this->model->getViewListTable($this->request, $this->result);
            // $res  = $this->result->get_array();
            $this->model->outViewListHTML($this->request, $this->menu, $this->session);
            require_once ('assembly_time_compare_ViewList.php');
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
        // targetDateStrの処理
        $this->InitTargetDateStr();
        // targetDateEndの処理
        $this->InitTargetDateEnd();
        // targetDivisionの処理
        $this->InitTargetDivision();
        // targetProcessの処理
        $this->InitTargetProcess();
        // targetPlanNoの処理 コメントの照会・編集用
        $this->InitTargetPlanNo();
        // targetAssyNoの処理 コメントの照会・編集用
        $this->InitTargetAssyNo();
        // targetSortItemの処理
        $this->InitTargetSortItem();
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
            $this->request->add('showMenu', 'List');
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
    
    ///// 開始年月日の取得・初期化
    // targetDateStrの処理
    private function InitTargetDateStr()
    {
        $targetDateStr = $this->request->get('targetDateStr');
        if ($targetDateStr == '') {
            if ($this->session->get_local('targetDateStr') == '') {
                $targetDateStr = workingDayOffset(-1);      // 指定がない場合は前営業日
            } else {
                $targetDateStr = $this->session->get_local('targetDateStr');
            }
        }
        $this->session->add_local('targetDateStr', $targetDateStr);
        $this->request->add('targetDateStr', $targetDateStr);
    }
    
    ///// 開始年月日の取得・初期化
    // targetDateEndの処理
    private function InitTargetDateEnd()
    {
        $targetDateEnd = $this->request->get('targetDateEnd');
        if ($targetDateEnd == '') {
            if ($this->session->get_local('targetDateEnd') == '') {
                $targetDateEnd = workingDayOffset(-1);      // 指定がない場合は前営業日
            } else {
                $targetDateEnd = $this->session->get_local('targetDateEnd');
            }
        }
        $this->session->add_local('targetDateEnd', $targetDateEnd);
        $this->request->add('targetDateEnd', $targetDateEnd);
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
    }
    
    ///// 工程区分の取得・初期化
    // targetProcessの処理
    private function InitTargetProcess()
    {
        $targetProcess = $this->request->get('targetProcess');
        if ($targetProcess == '') {
            if ($this->session->get_local('targetProcess') == '') {
                $targetProcess = 'H';                       // 指定がない場合は手作業工程
            } else {
                $targetProcess = $this->session->get_local('targetProcess');
            }
        }
        $this->session->add_local('targetProcess', $targetProcess);
        $this->request->add('targetProcess', $targetProcess);
    }
    
    ///// 計画番号毎のコメントの照会・編集用 計画番号パラメータ 取得・設定
    // targetPlanNoの処理
    private function InitTargetPlanNo()
    {
        if ($this->request->get('targetPlanNo') == '') {
            return true;          // 指定がない場合は何もしない。
        }
        if (!is_numeric(substr($this->request->get('targetPlanNo'), 1, 7))) {
            $this->error = 1;
            $this->errorMsg = '製品番号の２桁から８桁までは数字で入力して下さい。';
            return false;
        }
        if (strlen($this->request->get('targetPlanNo')) != 8) {
            $this->error = 1;
            $this->errorMsg = '製品番号は８桁です。';
            return false;
        }
        return true;
    }
    
    ///// 製品毎のコメントの照会・編集用 製品番号パラメータ 取得・設定
    // targetAssyNoの処理
    private function InitTargetAssyNo()
    {
        if ($this->request->get('targetAssyNo') == '') {
            return true;          // 指定がない場合は何もしない。
        }
        if (!is_numeric(substr($this->request->get('targetAssyNo'), 2, 5))) {
            $this->error = 1;
            $this->errorMsg = '製品番号の３桁から７桁までは数字で入力して下さい。';
            return false;
        }
        if (strlen($this->request->get('targetAssyNo')) != 9) {
            $this->error = 1;
            $this->errorMsg = '製品番号は９桁です。';
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
                $targetSortItem = 'line';                     // 指定がない場合はライングループ
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
    
} // class AssemblyTimeCompare_Controller End

?>
