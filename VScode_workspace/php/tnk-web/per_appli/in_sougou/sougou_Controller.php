<?php
////////////////////////////////////////////////////////////////////////////////
// 総合届（申請）                                                             //
//                                                         MVC Controller 部  //
// Copyright (C) 2020-2020 Ryota.waki ryota_waki@nitto-kohki.co.jp            //
// Changed history                                                            //
// 2020/11/18 Created sougou_Controller.php                                   //
// 2021/02/12 Release.                                                        //
////////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL);       // E_STRICT=2048(php5) E_ALL=2047 debug 用

/****************************************************************************
*                       base class 基底クラスの定義                         *
****************************************************************************/
///// namespace Controller {} は現在使用しない 使用例：Controller::equipController → $obj = new Controller::equipController;
///// Common::$sum_page, Common::set_page_rec() (名前空間::リテラル)
class Sougou_Controller
{
    ///// Private properties

    /************************************************************************
    *                               Public methods                          *
    ************************************************************************/
    ///// Constructer の定義 {php5 移行は __construct() に変更} {デストラクタ__destruct()}
    public function __construct($request, $model)
    {
        //////////// POST Data の初期化＆設定
        ///// メニュー切替用 リクエスト データ取得
        if ($request->get('showMenu') == '') {
            $request->add('showMenu', 'List');              // 指定がない場合は一覧表を表示(特に初回)
        }
        $syainbangou = $request->get('syainbangou');               // スケジュールの登録

        $request->add('syozoku', '---- 部 ---- 課');
        $request->add('simei', '----- -----');
    }
    
    ///// MVC View部の処理
    public function display($menu, $request, $result, $model)
    {
        //////////// ブラウザーのキャッシュ対策用
        $uniq = $menu->set_useNotCache('sougou');

        $sin_date = $request->get("sin_date");
        $str_date = $request->get("str_date");
        $end_date = $request->get("end_date");
        $str_time = $request->get("str_time");
        $end_time = $request->get("end_time");

        ///// メニュー切替 リクエスト データ取得
        $showMenu   = $request->get('showMenu');            // ターゲットメニューを取得

        $syainbangou =  $request->get('syainbangou');

        $pageControll = $model->out_pageCtlOpt_HTML($menu->out_self());

        if( $syainbangou != '' ) {
            if( !$model->IsSyain() ) {
                $_SESSION['s_sysmsg'] .= $syainbangou . '：社員番号をお確かめ下さい。';
            } else {
                if( !$model->IsApproval() ) {
                    $_SESSION['s_sysmsg'] .= "経理コード(". $request->get('act_id') . ") 承認 経路 登録なし。管理者へ連絡して下さい。";
                }
                if( $model->getViewDataList($result) > 0 ) {
                    $res = array();
                    $res = $result->get_array();

                    $request->add('simei', $res[0][1]);
                    $request->add('syozoku', $res[0][2]);
                }
            }
        }

//if( $request->get('syain_no') == '300667' ) {
    if( $request->get('check_flag') == "ok" ) {
        $model->add($request);
        if( $request->get("reappl") ) {
            ?>
            <script>alert("総合届の再申請が完了しました。"); window.open("about:blank","_self").close();</script>
            <?php
        }
        if( empty($_SESSION['s_sysmsg']) ) {
            $_SESSION['s_sysmsg'] .= "総合届の申請が完了しました。";
        }
    }
/*
} else {
        $content   = $request->get('r1');
        if( $content != '' )
            $model->add($request);
}
*/
        ////////// MVC の Model部の View部に渡すデータ生成
        switch ($showMenu) {

        case 'List':                                        // スケジュール 一覧表データ
        }
        
        ////////// HTML Header を出力してキャッシュを制御
        $menu->out_html_header();
        
        ////////// MVC の View 部の処理
        switch ($showMenu) {

        case 'List':                                        // スケジュールの一覧 画面
            require_once ('sougou_ViewList.php');
            break;
        case 'Check':                                       // 確認 画面
            require_once ('sougou_ViewCheck.php');
            break;
        case 'Re':                                          // 再申請 画面
            require_once ('sougou_ViewList.php');
            break;
        case 'Del':                                          // 再申請 画面
            require_once ('sougou_DelViewList.php');
            break;
        default:                // リクエストデータにエラーの場合は初期値の一覧を表示
            require_once ('sougou_ViewList.php');
            break;
        }
    }
    
    /***************************************************************************
    *                              Protected methods                           *
    ***************************************************************************/

    /***************************************************************************
    *                               Private methods                            *
    ***************************************************************************/

} // End off Class Sougou_Controller

?>
