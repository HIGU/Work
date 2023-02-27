<?php
//////////////////////////////////////////////////////////////////////////////
// 組立のライン別工数 各種グラフ                         MVC Controller 部  //
// Copyright (C) 2006-2006 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/05/12 Created   assembly_time_graph_Controller.php                  //
// 2006/06/15 明細を合計明細と工程明細にロジックで分けた(ListとDetaileList) //
// 2006/09/15 グラフの開始日・終了日のオフセット処理追加(１日単位の頁送り)  //
// 2006/09/27 グラフタイプ(工数計算方法)のオプション(工数日割り計算)追加    //
// 2006/09/28 tagetOffsetStr/Endメソッド内の day_off()→day_off_line()へ変更//
// 2006/11/02 グラフ画像の倍率指定の追加 targetScale                        //
// 2006/11/06 複数ライン指定に対応するため targetLine → targetLineArray追加//
// 2006/11/08 TargetLine()とTargetDateYM()の初期化順が逆だったのを修正      //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug 用
require_once ('../../../tnk_func.php');     // workingDayOffset(-1), day_off(), date_offset() で使用

/****************************************************************************
*                       base class 基底クラスの定義                         *
****************************************************************************/
///// namespace Controller {} は現在使用しない 使用例：Controller::equipController → $obj = new Controller::equipController;
///// Common::$sum_page, Common::set_page_rec() (名前空間::リテラル)
class AssemblyTimeGraph_Controller
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
        $this->model = new AssemblyTimeGraph_Model($this->request);
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
        case 'Graph':                                       // Ajax用 グラフ表示
            $this->model->outViewGraphHTML($this->request, $this->menu);
            require_once ('assembly_time_graph_ViewGraph.php');
            break;
        case 'List':                                        // Ajax用 別ウィンドウでList表示
            // $rows = $this->model->getViewListTable($this->request, $this->result);
            // $res  = $this->result->get_array();
            $this->model->outViewListHTML($this->request, $this->menu);
            $this->ViewListExecute($this->menu, $this->request, $this->model, $uniq);
            break;
        case 'DetaileList':                                 // Ajax用 別ウィンドウで明細 List表示
            $this->model->outViewDetaileListHTML($this->request, $this->menu);
            $this->ViewListExecute($this->menu, $this->request, $this->model, $uniq);
            break;
        case 'CommentSave':                                 // コメントの保存 ライン番号と年月日がキー
            $this->model->commentSave($this->request);
        case 'Comment':                                     // 別ウィンドウでコメントの照会・編集
            $this->model->getComment($this->request, $this->result);
            require_once ('assembly_time_graph_ViewEditComment.php');
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
        
        // targetLineの処理     注意:DateYMより先に行う事
        $this->InitTargetLine($this->request, $this->session);
        
        // targetDateYMの処理
        $this->InitTargetDateYM($this->request, $this->session);
        
        // targetSupportTimeの処理
        $this->InitTargetSupportTime($this->request, $this->session);
        
        // targetGraphTypeの処理
        $this->InitTargetGraphType($this->request, $this->session);
        
        // targetProcessの処理
        $this->InitTargetProcess($this->request, $this->session);
        
        // targetPlanNoの処理 コメントの照会・編集用
        $this->InitTargetPlanNo($this->request);
        
        // targetDateListの処理
        $this->InitTargetDateList($this->request);
        
        // targetScaleの処理
        $this->InitTargetScale($this->request, $this->session);
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
        $showMenu = $request->get('showMenu');
        if ($showMenu == '') {
            $showMenu = 'CondForm';         // 指定がない場合はCondition Form (条件設定)
        }
        $request->add('showMenu', $showMenu);
    }
    
    ///// 対象年月の取得・初期化
    // targetDateYMの処理
    private function InitTargetDateYM($request, $session)
    {
        $targetDateYM = $request->get('targetDateYM');
        if ($targetDateYM == '') {
            if ($session->get_local('targetDateYM') == '') {
                $targetDateYM = date('Ym');      // 指定がない場合は当月
            } else {
                $targetDateYM = $session->get_local('targetDateYM');
            }
        } else {
            ///// targetDateYMの指定があった場合はオフセット値を初期化する
            $session->add_local('targetOffsetStr', '');
            $session->add_local('targetOffsetEnd', '');
        }
        $session->add_local('targetDateYM', $targetDateYM);
        $request->add('targetDateYM', $targetDateYM);
        // targetDateStr と targetDateEnd に展開
        $request->add('targetDateStr', $targetDateYM . '01');   //指定年月の１日
        $YYYY = substr($targetDateYM, 0, 4);                    // リクエスト年
        $MM   = substr($targetDateYM, 4, 2);                    // リクエスト月
        $request->add('targetDateEnd', $targetDateYM . last_day($YYYY, $MM));   // 指定年月の最終日
        
        if ($request->get('targetOffsetStr') != '') {
            $session->add_local('targetOffsetStr', $request->get('targetOffsetStr')+$session->get_local('targetOffsetStr'));
        }
        if ($request->get('targetOffsetEnd') != '') {
            $session->add_local('targetOffsetEnd', $request->get('targetOffsetEnd')+$session->get_local('targetOffsetEnd'));
        }
        $this->targetOffsetStr($request, $session->get_local('targetOffsetStr'));
        $this->targetOffsetEnd($request, $session->get_local('targetOffsetEnd'));
    }
    
    ///// 組立ラインの取得・初期化      2006/11/06 配列へ変更
    // targetLineの処理
    private function InitTargetLine($request, $session)
    {
        $targetLine = $request->get('targetLine');
        if (!is_array($targetLine)) {
            if (!is_array($session->get_local('targetLine'))) { // 初回の判断は配列のチェックで行う
                $targetLine = array();                      // 配列の初期化
            } else {
                $targetLine = $session->get_local('targetLine');
            }
        }
        $session->add_local('targetLine', $targetLine);
        $request->add('targetLine', $targetLine);
    }
    
    ///// 持工数の取得・初期化
    // targetSupportTimeの処理
    private function InitTargetSupportTime($request, $session)
    {
        $targetSupportTime = $request->get('targetSupportTime');
        if ($targetSupportTime == '') {
            if ($session->get_local('targetSupportTime') == '') {
                $targetSupportTime = '440';             // 指定がない場合は１日の平均440分
            } else {
                $targetSupportTime = $session->get_local('targetSupportTime');
            }
        }
        $session->add_local('targetSupportTime', $targetSupportTime);
        $request->add('targetSupportTime', $targetSupportTime);
    }
    
    ///// 工数計算のグラフタイプの取得・初期化
    // targetGraphTypeの処理
    private function InitTargetGraphType($request, $session)
    {
        $targetGraphType = $request->get('targetGraphType');
        if ($targetGraphType == '') {
            if ($session->get_local('targetGraphType') == '') {
                $targetGraphType = 'avr';             // 指定がない場合は日割り(平均)グラフ
            } else {
                $targetGraphType = $session->get_local('targetGraphType');
            }
        }
        $session->add_local('targetGraphType', $targetGraphType);
        $request->add('targetGraphType', $targetGraphType);
    }
    
    ///// 工程区分の取得・初期化  現在の所は使用しない。
    // targetProcessの処理
    private function InitTargetProcess($request, $session)
    {
        $targetProcess = $request->get('targetProcess');
        if ($targetProcess == '') {
            if ($session->get_local('targetProcess') == '') {
                $targetProcess = 'H';                       // 指定がない場合は手作業工程
            } else {
                $targetProcess = $session->get_local('targetProcess');
            }
        }
        $session->add_local('targetProcess', $targetProcess);
        $request->add('targetProcess', $targetProcess);
    }
    
    ///// 計画番号毎のコメントの照会・編集用 計画番号パラメータ 取得・設定
    // targetPlanNoの処理
    private function InitTargetPlanNo($request)
    {
        if ($request->get('targetPlanNo') == '') {
            return true;          // 指定がない場合は何もしない。
        }
        if (!is_numeric(substr($request->get('targetPlanNo'), 1, 7))) {
            $this->error = 1;
            $this->errorMsg = '製品番号の２桁から８桁までは数字で入力して下さい。';
            return false;
        }
        if (strlen($request->get('targetPlanNo')) != 8) {
            $this->error = 1;
            $this->errorMsg = '製品番号は８桁です。';
            return false;
        }
        return true;
    }
    
    ///// グラフのバーをクリック時のList条件パラメータ 取得・設定
    // targetDateListの処理
    private function InitTargetDateList($request)
    {
        if ($request->get('targetDateList') == '') {
            return true;          // 指定がない場合は何もしない。
        }
        if (strlen($request->get('targetDateList')) != 8) {
            $this->error = 1;
            $this->errorMsg = '指定年月日は８桁です。';
            return false;
        }
        if ($request->get('showMenu') == 'List') {  // DetaileList 時は 通常の日付である 20060517
            // 06/05/17 → 20060517 へ変換
            $request->add('targetDateList', '20' . substr($request->get('targetDateList'), 0, 2) . substr($request->get('targetDateList'), 3, 2) . substr($request->get('targetDateList'), 6, 2));
        }
        return true;
    }
    
    ///// グラフ画像の倍率の指定
    // targetScaleの処理
    private function InitTargetScale($request, $session)
    {
        $targetScale = $request->get('targetScale');
        if ($targetScale == '') {
            if ($session->get_local('targetScale') == '') {
                $targetScale = '1.0';                  // 指定がない場合は100%
            } else {
                $targetScale = $session->get_local('targetScale');
            }
        }
        $session->add_local('targetScale', $targetScale);
        $request->add('targetScale', $targetScale);
    }
    
    ///// 開始日のオフセット処理
    private function targetOffsetStr($request, $offset)
    {
        $dateStr = $request->get('targetDateStr');
        $yyyy = substr($dateStr, 0, 4);
        $mm   = substr($dateStr, 4, 2);
        $dd   = substr($dateStr, 6, 2);
        $targetLineArray = $request->get('targetLine');
        // $dateStr = date('Ymd', mktime(0, 0, 0, $mm, $dd+($offset), $yyyy)); //これだと休日の判定が出来ない
        $i = 0;
        if ($offset > 0) {
            while ($offset != 0) {
                $i++;
                if (day_off_line(mktime(0, 0, 0, $mm, $dd+($i), $yyyy), $targetLineArray[0])) continue;
                $offset--;
            }
        } elseif ($offset < 0) {
            while ($offset != 0) {
                $i--;
                if (day_off_line(mktime(0, 0, 0, $mm, $dd+($i), $yyyy), $targetLineArray[0])) continue;
                $offset++;
            }
        }
        $dateStr = date('Ymd', mktime(0, 0, 0, $mm, $dd+($i), $yyyy));
        $request->add('targetDateStr', $dateStr);
    }
    
    ///// 終了日のオフセット処理
    private function targetOffsetEnd($request, $offset)
    {
        $dateEnd = $request->get('targetDateEnd');
        $yyyy = substr($dateEnd, 0, 4);
        $mm   = substr($dateEnd, 4, 2);
        $dd   = substr($dateEnd, 6, 2);
        $targetLineArray = $request->get('targetLine');
        // $dateEnd = date('Ymd', mktime(0, 0, 0, $mm, $dd+($offset), $yyyy)); //これだと休日の判定が出来ない
        $i = 0;
        if ($offset > 0) {
            while ($offset != 0) {
                $i++;
                if (day_off_line(mktime(0, 0, 0, $mm, $dd+($i), $yyyy), $targetLineArray[0])) continue;
                $offset--;
            }
        } elseif ($offset < 0) {
            while ($offset != 0) {
                $i--;
                if (day_off_line(mktime(0, 0, 0, $mm, $dd+($i), $yyyy), $targetLineArray[0])) continue;
                $offset++;
            }
        }
        $dateEnd = date('Ymd', mktime(0, 0, 0, $mm, $dd+($i), $yyyy));
        $request->add('targetDateEnd', $dateEnd);
    }
    
    
    /***** display()の Private methods 処理 *****/
    ///// 条件選択フォームの表示
    private function CondFormExecute($menu, $request, $model, $uniq)
    {
        require_once ('assembly_time_graph_ViewCondForm.php');
        return true;
    }
    
    /***** display()の Private methods 処理 *****/
    ///// グラフの個別バーの明細の表示
    private function ViewListExecute($menu, $request, $model, $uniq)
    {
        require_once ('assembly_time_graph_ViewList.php');
        return true;
    }
    
} // class AssemblyTimeGraph_Controller End

?>
