<?php
////////////////////////////////////////////////////////////////////////////////
// 定時間外作業申告                                                           //
//                                                              MVC Model 部  //
// Copyright (C) 2021-2021 Ryota.waki ryota_waki@nitto-kohki.co.jp            //
// Changed history                                                            //
// 2021/10/20 Created over_time_work_report_Model.php                         //
// 2021/11/01 Release.                                                        //
// 2021/11/25 表示部署を強制的に変更 970328                                   //
////////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
// ini_set('zend.ze1_compatibility_mode', '1');    // zend 1.X コンパチ php4の互換モード

require_once ('../../ComTableMntClass.php');   // TNK 全共通 テーブルメンテ&ページ制御Class

/******************************************************************************
*          総合届（申請）用 MVCのModel部 base class 基底クラスの定義          *
******************************************************************************/
class over_time_work_report_Model extends ComTableMnt
{
    ///// Private properties
    private $uid;       // ログインユーザーID
    private $act_id;    // 部署ID
    private $deploy;    // 部署名
    private $posts_na;  // 職位名
    private $posts_no;  // 職位No
    private $str_hour;  // 開始 時
    private $str_min;   // 開始 分
    private $end_hour;  // 終了 時
    private $end_min;   // 終了 分
    private $show_menu; // 表示モード
    private $hurry;     // 至急フラグ
    
    /************************************************************************
    *                               Public methods                          *
    ************************************************************************/
    ////////// Constructer の定義 (php5へ移行時は __construct() へ変更予定) (デストラクタ__destruct())
    public function __construct($request, $uid='')
    {
        // 以下のリクエストはcontrollerより先に取得しているため空の場合がある。
        if ($uid == '') {
            return;    // キーフィールドが設定されていなければ何もしない
        } else {
            $uid = sprintf('%06s', $uid);
            if( $uid == 0 ) return;
            $this->uid = $uid;    // Propertiesへの登録
            $this->setActID();
            $this->setPosts();
            $request->add('uid', $uid);
        }

        ///// メニュー切替 リクエスト データ取得
        $this->show_menu = $request->get('showMenu');   // ターゲットメニューを取得

        $sql_sum = "
            SELECT count(*) FROM user_detailes where uid like '%{$uid}'
        ";
        ///// Constructer を定義すると 基底クラスの Constructerが実行されない
        ///// 基底ClassのConstructerはプログラマーの責任で呼出す
        parent::__construct($sql_sum, $request, 'over_time_work_report.log');
    }
    
// ============================================================================
// 内部で使用する関数 =========================================================
// ============================================================================
    // ログインユーザーactIDセット
    public function setActID()
    {
        $query = "
            SELECT    act_id
            FROM      cd_table
            WHERE     uid = '$this->uid'
        ";
        $res = array();
        
        if ( $this->getResult2($query, $res) <= 0 ) {
            $this->act_id = "";
        } else {
            $this->act_id = $res[0][0];
        }
    }
    
    // ログインユーザー職位をセット
    public function setPosts()
    {
        $this->posts_na = '';
        $this->posts_no = 0;
        if( $this->IsKatyou() ) {
            $this->posts_na = 'ka';
            $this->posts_no = 1;
        } else if( $this->IsButyou() ) {
            $this->posts_na = 'bu';
            $this->posts_no = 2;
        } else if( $this->IsKoujyoutyou() ) {
            $this->posts_na = 'ko';
            $this->posts_no = 3;
        }
    }
    
    // ログインユーザーactIDを取得
    public function getActID()
    {
        return $this->act_id;
    }
    
    // 表示モードを取得
    public function getShowMenu()
    {
        return $this->show_menu;
    }
    
    // 表示する部門名リスト取得
    public function getBumonName()
    {
        return array("総務課", "商品管理課", "品質保証課", "技術課", "製造部 製造１課", "製造部 製造２課", "生産管理課 計画・購買係", "生産管理課 資材係", "カプラ組立課 標準係ＭＡ", "カプラ組立課 標準係ＨＡ", "カプラ組立課 特注係", "リニア組立課");
    }
    
    // 社員ですか？
    public function IsSyain($uid)
    {
        $query = "SELECT uid FROM user_detailes WHERE uid = '$uid' AND retire_date IS NULL";
        if( getResult2($query, $res) <= 0 ) {
            return false;
        }
        return true;
    }
    
    // 部門別のactID取得
    public function getBumonActID($name)
    {
        $where = "";
        
        if( $name == "総務課" ) {
            $where = "(ct.act_id=605 OR ct.act_id=610 OR ct.act_id=650 OR ct.act_id=651 OR ct.act_id=660) ";
        } else if( $name == "商品管理課" ) {
            $where = "(ct.act_id=670) ";
        } else if( $name == "品質保証課" ) {
            $where = "(ct.act_id=501 OR ct.act_id=174 OR ct.act_id=517 OR ct.act_id=537 OR ct.act_id=581) ";
        } else if( $name == "技術課" ) {
            $where = "(ct.act_id=173 OR ct.act_id=515 OR ct.act_id=535) ";
        } else if( $name == "製造部 製造１課" ) {
            $where = "(ct.act_id=518 OR ct.act_id=519 OR ct.act_id=556 OR ct.act_id=520) ";
        } else if( $name == "製造部 製造２課" ) { // 600 も含んでいる為、条件に、"AND ud.uid!=999999 AND ud.pid!=110" 追加
            $where = "(ct.act_id=600 OR ct.act_id=582 OR ct.act_id=547 OR ct.act_id=527 OR ct.act_id=528) AND ud.uid!=999999 AND ud.pid!=110";
        } else if( $name == "生産管理課 計画・購買係" ) {
            $where = "(ct.act_id=500 OR ct.act_id=545 OR ct.act_id=512 OR ct.act_id=532 OR ct.act_id=513 OR ct.act_id=533) ";
        } else if( $name == "生産管理課 資材係" ) {
            $where = "(ct.act_id=514 OR ct.act_id=534) ";
        } else if( $name == "カプラ組立課 標準係ＭＡ" ) {
//            $where = "(ct.act_id=522) ";
            $where = "((ct.act_id=522) OR (ct.act_id=523 AND uid='970328')) ";  // 「菅 純子さん」強制的に、ＭＡへ表示
        } else if( $name == "カプラ組立課 標準係ＨＡ" ) {
//            $where = "(ct.act_id=176 OR ct.act_id=523) ";
            $where = "(ct.act_id=176 OR (ct.act_id=523 AND uid!='970328')) ";   // 「菅 純子さん」強制的に、ＨＡから除外
        } else if( $name == "カプラ組立課 特注係" ) {
            $where = "(ct.act_id=525) ";
        } else if( $name == "リニア組立課" ) {
            $where = "(ct.act_id=551 OR ct.act_id=175 OR ct.act_id=572) ";
        }
        
        return $where;
    }
    
    // マスター？（工場長、管理部、総務課）
    public function IsMaster()
    {
        $flag = false;
        $show_menu = $this->getShowMenu();
        switch ($this->act_id) {
            case 610:   // 管理部
            case 650:   // 管理部 総務課
            case 651:   // 管理部 総務課 総務担当
            case 660:   // 管理部 総務課 財務担当
                if( $show_menu == 'Quiry' || $show_menu == 'Results' ) $flag = true;
                break;
            case 600:   // 工場長
                if( $this->uid == '012394') {
                    $this->act_id = 582;
                    break;
                }
                $flag = true;
                break;
            default:
                $flag = false;
                break;
        }
        return $flag;
    }

    // ＩＳＯ事務局？
    public function IsISO()
    {
        switch ($this->act_id) {
            case 610:   // 管理部
            case 605:   // ＩＳＯ事務局
                $flag = true;
                break;
            default:
                $flag = false;
                break;
        }
        return $flag;
    }

    // 総務？
    public function IsSoumu()
    {
        switch ($this->act_id) {
            case 605:   // ＩＳＯ事務局
            case 610:   // 管理部
            case 650:   // 管理部 総務課
            case 651:   // 管理部 総務課 総務
            case 660:   // 管理部 総務課 財務
                $flag = true;
                break;
            default:
                $flag = false;
                break;
        }
        return $flag;
    }

    // 管理部 商品管理課？
    public function IsKanriSyou()
    {
        switch ($this->act_id) {
            case 610:   // 管理部
            case 670:   // 管理部 商品管理課
                $flag = true;
                break;
            default:
                $flag = false;
                break;
        }
        return $flag;
    }

    // 技術部 品質保証課？
    public function IsGiHin()
    {
        switch ($this->act_id) {
            case 501:   // 技術部
            case 174:   // 技術部 品質保証課
            case 517:   // 技術部 品質保証課 カプラ検査担当
            case 537:   // 技術部 品質保証課 カプラ検査担当
            case 581:   // 技術部 品質保証課 カプラ検査担当
                $flag = true;
                break;
            default:
                $flag = false;
                break;
        }
        return $flag;
    }

    // 技術部 技術課？
    public function IsGiGi()
    {
        switch ($this->act_id) {
            case 501:   // 技術部
            case 173:   // 技術部 技術課
            case 515:   // 技術部 技術課
            case 535:   // 技術部 技術課
                $flag = true;
                break;
            default:
                $flag = false;
                break;
        }
        return $flag;
    }

    // 製造部 製造１課？
    public function IsSeizouOne()
    {
        switch ($this->act_id) {
            case 582:   // 製造部
            case 518:   // 製造部 製造１課
            case 519:   // 製造部 製造１課
            case 556:   // 製造部 製造１課
            case 520:   // 製造部 製造１課
                $flag = true;
                break;
            default:
                $flag = false;
                break;
        }
        return $flag;
    }

    // 製造部 製造２課？
    public function IsSeizouTow()
    {
        switch ($this->act_id) {
            case 582:   // 製造部
            case 547:   // 製造部 製造２課
            case 528:   // 製造部 製造２課
            case 527:   // 製造部 製造２課
                $flag = true;
                break;
            default:
                $flag = false;
                break;
        }
        return $flag;
    }

    // 生産部 生産管理課 計画・購買係？
    public function IsSeiKanKeiKou()
    {
        switch ($this->act_id) {
            case 500:   // 生産部
            case 545:   // 生産部 生産管理課
            case 512:   // 生産部 生産管理課 計画係 Ｃ担当
            case 532:   // 生産部 生産管理課 計画係 Ｌ担当
            case 513:   // 生産部 生産管理課 購買係 Ｃ担当
            case 533:   // 生産部 生産管理課 購買係 Ｌ担当
                $flag = true;
                break;
            default:
                $flag = false;
                break;
        }
        return $flag;
    }

    // 生産部 生産管理課 資材係？
    public function IsSeiKanSizai()
    {
        switch ($this->act_id) {
            case 500:   // 生産部
            case 545:   // 生産部 生産管理課
            case 514:   // 生産部 生産管理課 資材係 カプラ資材
            case 534:   // 生産部 生産管理課 資材係 リニア資材
                $flag = true;
                break;
            default:
                $flag = false;
                break;
        }
        return $flag;
    }

    // 生産部 カプラ組立課 標準係MA？
    public function IsSeiCapuraMA()
    {
        switch ($this->act_id) {
            case 500:   // 生産部
            case 176:   // 生産部 カプラ組立課
            case 522:   // 生産部 カプラ組立MA担当
                $flag = true;
                break;
            default:
                $flag = false;
                break;
        }
        return $flag;
    }

    // 生産部 カプラ組立課 標準係HA？
    public function IsSeiCapuraHA()
    {
        switch ($this->act_id) {
            case 500:   // 生産部
            case 176:   // 生産部 カプラ組立課
            case 523:   // 生産部 カプラ組立HA担当
                $flag = true;
                break;
            default:
                $flag = false;
                break;
        }
        return $flag;
    }

    // 生産部 カプラ組立課 特注係？
    public function IsSeiCapuraSC()
    {
        switch ($this->act_id) {
            case 500:   // 生産部
            case 176:   // 生産部 カプラ組立課
            case 525:   // 生産部 カプラ特注担当
                $flag = true;
                break;
            default:
                $flag = false;
                break;
        }
        return $flag;
    }

    // 生産部 リニア組立課？
    public function IsSeiLinia()
    {
        switch ($this->act_id) {
            case 500:   // 生産部
            case 551:   // 生産部 リニア組立課
            case 175:   // 生産部 リニア組立担当
            case 572:   // 生産部 ピストン研磨担当
                $flag = true;
                break;
            default:
                $flag = false;
                break;
        }
        return $flag;
    }

    // 表示可能項目ですか？
    public function IsDisp($no)
    {
        if( $this->IsMaster() ) return true;

        switch ($no) {
            case  0:    // ---- 選択して下さい ----
                $flag = true;
                break;
            case  1:    // 総務課
                $flag = $this->IsSoumu();
                break;
            case  2:    // 商品管理課
                $flag = $this->IsKanriSyou();
                break;
            case  3:    // 品質保証課
                $flag = $this->IsGiHin();
                break;
            case  4:    // 技術課
                $flag = $this->IsGiGi();
                break;
            case  5:    // 製造部 製造１課
                $flag = $this->IsSeizouOne();
                break;
            case  6:    // 製造部 製造２課
                $flag = $this->IsSeizouTow();
                break;
            case  7:    // 生産管理課 計画・購買係
                $flag = $this->IsSeiKanKeiKou();
                break;
            case  8:    // 生産管理課 資材係
                $flag = $this->IsSeiKanSizai();
                break;
            case  9:    // カプラ組立課 標準係ＭＡ
                $flag = $this->IsSeiCapuraMA();
                break;
            case 10:    // カプラ組立課 標準係ＨＡ
                $flag = $this->IsSeiCapuraHA();
                break;
            case 11:    // カプラ組立課 特注係
                $flag = $this->IsSeiCapuraSC();
                break;
            case 12:    // リニア組立課
                $flag = $this->IsSeiLinia();
                break;
            default:
                $flag = false;
                break;
        }
        return $flag;
    }

    // 指定UIDのメールアドレス取得
    public function getMailAddres($send_uid)
    {
        $query = "
                    SELECT          trim(name), trim(mailaddr)
                    FROM            user_detailes
                    LEFT OUTER JOIN user_master USING(uid)
                 ";
//        $search = "WHERE uid='300667'"; // 送信先 強制変更 ※リリース時は、コメント化
        $search = "WHERE uid='$send_uid'";    // 送信先
        $query = sprintf("$query %s", $search);     // SQL query 文の完成
        $res = array();
        if ($this->getResult2($query, $res) <= 0) {
            return "";
        }
        return trim($res[0][1]); // メールアドレス
    }
    
    // メール送信
    public function SendMail($mode, $send_uid, $date, $deploy, $uid, $type, $memo)
    {
        $send_uid = sprintf('%06s', $send_uid);
        
        $to_addres = $this->getMailAddres($send_uid);   // メールアドレス
        $to_name   = $this->getName($send_uid);         // 宛先者氏名
        $add_head  = "";
        $attenSubject = "宛先： {$to_name} 様 定時間外作業申告よりお知らせ";  // タイトル
        // 内容
        if( strlen($date) == 8 ) {
            $date = substr($date,0,4) . '-' . substr($date,4,2) . '-' . substr($date,6,2);
        }
        $date = $this->getTargetDateDay($date, 'on');   // YYYY-MM-DD (week)
        $name = $this->getName($uid);   // 対象者氏名
        if( $name == "" ) $name = $uid;
        // $mode より内容を変更する。
        switch ($mode) {
            case "Result":      // 残業結果入力完了
                $attenSubject = "{$to_name} 様 定時間外作業申告 【入力完了】 お知らせ";  // タイトル
                $message  = "{$to_name} 様\n\n";
                $message .= "以下の定時間外作業申告（残業結果報告）全て入力されました。\n\n";
                $message .= "作業日：{$date}　部署：$deploy\n\n";
                $message .= "承認処理をお願います。\n\n";
                $message .= "↓↓↓ URLはこちら ↓↓↓\n\n";
                $message .= "http://masterst/per_appli/over_time_work_report/over_time_work_report_Main.php?calUid={$send_uid}&showMenu=Judge&select_radio=3\n\n";
//$_SESSION['s_sysmsg'] .= "Mail()::宛先：{$to_name}／結果入力完了部署：$deploy\t";
                break;
            case "AfterReport": // 事後報告
                $attenSubject = "{$to_name} 様 定時間外作業申告 【事後報告】 お知らせ";  // タイトル
                $message  = "{$to_name} 様\n\n";
                $message .= "以下の定時間外作業申告（残業結果報告）事後報告が来ました。\n\n";
                $message .= "作業日：{$date}　部署：$deploy\t作業者：$name\n\n";
                $message .= "確認の上、承認処理をお願います。\n\n";
                $message .= "↓↓↓ URLはこちら ↓↓↓\n\n";
                $message .= "http://masterst/per_appli/over_time_work_report/over_time_work_report_Main.php?calUid={$send_uid}&showMenu=Judge\n\n";
//$_SESSION['s_sysmsg'] .= "Mail()::宛先：{$to_name}／事後報告の人：$name\t";
                break;
            case "Cancel":// 取り消し
                $attenSubject = "{$to_name} 様 定時間外作業申告 【取り消し】 お知らせ";  // タイトル
                $message  = "{$to_name} 様\n\n";
                $message .= "以下の定時間外作業申告（";
                if( $type == "yo" ) $message .= "事前申請";
                if( $type == "ji" ) $message .= "残業結果報告";
                $message .= "）が取り消されました。\n\n";
                $message .= "作業日：{$date}　部署：$deploy\t作業者：$name\n\n";
                $message .= "取消理由は、以下の通りです。\n\n";
                $message .= "　$memo\n\n";
//$_SESSION['s_sysmsg'] .= "Mail()::宛先：{$to_name}／取り消し者：$name\t";
                break;
            case "Hurry":// 至急
                $attenSubject = "{$to_name} 様 定時間外作業申告 【至急】 お知らせ";  // タイトル
                $message  = "{$to_name} 様\n\n";
                if( $type == "" ) {
                    $message .= "以下の定時間外作業申告（事前申請）が来ました。\n\n";
                } else {
                    $message .= "{$memo} 様 不在の為、以下の定時間外作業申告（事前申請）\n\n";
                }
                if( $name ) {
                    $message .= "作業日：{$date}　部署：$deploy\t作業者：$name\n\n";
                } else {
                    $message .= "作業日：{$date}　部署：$deploy\n\n";
                }
                if( $type == "" ) {
                    $message .= "至急、承認処理をお願います。\n\n";
                    $message .= "↓↓↓ URLはこちら ↓↓↓\n\n";
                    $message .= "http://masterst/per_appli/over_time_work_report/over_time_work_report_Main.php?calUid={$send_uid}&showMenu=Judge\n\n";
                } else {
                    $message .= "至急、承認処理をお願います。（※不在未承認より）\n\n";
                    $message .= "↓↓↓ URLはこちら ↓↓↓\n\n";
                    $message .= "http://masterst/per_appli/over_time_work_report/over_time_work_report_Main.php?calUid={$send_uid}&showMenu=Judge&select_radio=2\n\n";
                }
/**
if( $name ) {
$_SESSION['s_sysmsg'] .= "Mail()::宛先：{$to_name}／至急な人：$name\t";
}else {
$_SESSION['s_sysmsg'] .= "Mail()::宛先：{$to_name}／至急な部署：$deploy\t";
}
/**/
                break;
            case "Next":    // 事前申請 次の承認者へ知らせる
                $attenSubject = "{$to_name} 様 定時間外作業申告 【事前申請】 お知らせ";  // タイトル
                $message  = "{$to_name} 様\n\n";
                if( $type == "" ) {
                    $message .= "以下の定時間外作業申告（事前申請）が来ました。\n\n";
                } else {
                    $message .= "{$memo} 様 不在の為、以下の定時間外作業申告（事前申請）\n\n";
                }
                
                $message .= "作業日：{$date}　部署：$deploy\n\n";
                
                if( $type == "" ) {
                    $message .= "承認処理をお願います。\n\n";
                    $message .= "↓↓↓ URLはこちら ↓↓↓\n\n";
                    $message .= "http://masterst/per_appli/over_time_work_report/over_time_work_report_Main.php?calUid={$send_uid}&showMenu=Judge\n\n";
                } else {
                    $message .= "承認処理をお願います。（※不在未承認より）\n\n";
                    $message .= "↓↓↓ URLはこちら ↓↓↓\n\n";
                    $message .= "http://masterst/per_appli/over_time_work_report/over_time_work_report_Main.php?calUid={$send_uid}&showMenu=Judge&select_radio=2\n\n";
                }
//$_SESSION['s_sysmsg'] .= "Mail()::宛先：{$to_name}／申請部署：$deploy\t";
                break;
            case "Notice":  // 最終承認
                $attenSubject = "{$to_name} 様 定時間外作業申告 【承認】 お知らせ";  // タイトル
                $message  = "{$to_name} 様\n\n";
                $message .= "以下の定時間外作業申告（事前申請）が承認されました。\n\n";
                $message .= "作業日：{$date}　部署：$deploy\n\n作業者：$name\n\n";
                $message .= "作業者へお知らせ下さい。\n\n";
//$_SESSION['s_sysmsg'] .= "Mail()::宛先：{$to_name}／最終承認された人：$name\t";
                break;
            case "Deny":    // 否認
                $deny_name = $this->getName($this->getUID());    // 否認者氏名
                $attenSubject = "{$to_name} 様 定時間外作業申告 【否認】 お知らせ";  // タイトル
                $message  = "{$to_name} 様\n\n";
                $message .= "以下の定時間外作業申告（";
                if( $type == "yo" ) $message .= "事前申請";
                if( $type == "ji" ) $message .= "残業結果報告";
                $message .= "）が、否認されました。\n\n";
                $message .= "作業日：{$date}　部署：$deploy\n\n作業者：$name\n\n";
                $message .= "否認者：{$deny_name} 様\n\n";
                $message .= "否認理由は、以下の通りです。\n\n";
                $message .= "　$memo\n\n";
//$_SESSION['s_sysmsg'] .= "Mail()::宛先：{$to_name}／否認された人：$name\t";
                break;
            default:
                break;
        }
        $message .= "以上。";
        mb_send_mail($to_addres, $attenSubject, $message, $add_head);
    }

    // 残業結果報告 入力完了処理
    public function Result($request)
    {
        $date   = $request->get('w_date');
        $deploy = $request->get('ddlist_bumon');
        $query  = "SELECT ji_ad_st FROM over_time_report WHERE date='$date' AND deploy='$deploy' AND yo_ad_rt!='-1'";
        $res    = array();
        if( ($rows = $this->getResult2($query, $res)) <= 0 ) {
            return false;
        }
        $send = 3; // 初期値
        for( $r=0; $r<$rows; $r++ ) {
            if( $res[$r][0] == "" ) return false;   // 残業結果報告 なし
            if( $send > $res[$r][0] ) $send = $res[$r][0];
        }
        switch ($send) {
            case 0:   // 課長待ち
                $send = $this->getKatyouUID($deploy);
                break;
            case 1:   // 部長待ち
                $send = $this->getButyouUID($deploy);
                break;
            case 2:   // 工場長待ち
                $send = $this->getKoujyotyouUID();
                break;
            default:
                return false;
        }
        $this->SendMail("Result", $send, $date, $deploy, "", "", "");
    }

    // 残業結果報告 入力完了処理
    public function Result2($date, $deploy)
    {
        $query  = "SELECT ji_ad_rt, ji_ad_st FROM over_time_report WHERE date='$date' AND deploy='$deploy' AND yo_ad_rt!='-1' AND yo_ad_rt<=yo_ad_st AND ji_ad_rt>1";
        $res    = array();
        if( ($rows = $this->getResult2($query, $res)) <= 0 ) {
            return false;
        }
        $send = 3; // 初期値
        for( $r=0; $r<$rows; $r++ ) {
            if( $res[$r][1] == "" ) return false;   // 残業結果報告 なし
            if( $res[$r][0]>$res[$r][1] && $send > $res[$r][1] ) $send = $res[$r][1];
        }
        switch ($send) {
            case 0:     // 課長待ち メール送信の必要なし
                return false;
            case 1:     // 部長待ち
                $send = $this->getButyouUID($deploy);
                break;
            case 2:     // 工場長待ち
                $send = $this->getKoujyotyouUID();
                break;
            default:    // メール送信の必要なし
                return false;
        }
        $this->SendMail("Result", $send, $date, $deploy, "", "", "");
    }

    // 事後報告 入力完了処理
    public function AfterReport($date, $deploy, $uid)
    {
        $query  = "SELECT ji_ad_st FROM over_time_report WHERE date='$date' AND uid='$uid' AND yo_ad_rt IS NULL";
        $res    = array();
        if( ($rows = $this->getResult2($query, $res)) <= 0 ) {
            return false;
        }
        $send = 3; // 初期値
        for( $r=0; $r<$rows; $r++ ) {
            if( $res[$r][0] == "" ) return false;   // 残業結果報告 なし
            if( $send > $res[$r][0] ) $send = $res[$r][0];
        }
        switch ($send) {
            case 0:   // 課長待ち
                $send = $this->getKatyouUID($deploy);
                break;
            case 1:   // 部長待ち
                $send = $this->getButyouUID($deploy);
                break;
            case 2:   // 工場長待ち
                $send = $this->getKoujyotyouUID();
                break;
            default:
                return false;
        }
        $this->SendMail("AfterReport", $send, $date, $deploy, $uid, "", "");
    }

    // 至急処理
    public function Hurry($date, $deploy, $uid)
    {
        $time_hurry = '15:00';                          // 以降は至急メール
        $now_dt  = new DateTime();                      // 現在日時
        $hurr_dt = new DateTime("$date $time_hurry");   // 作業日の15:00
        if( $now_dt <= $hurr_dt ) return;   // 通常
        
        if( $uid == "" ) {
            $no = $this->getPostsNo();
        } else {
            $no = 0;
            if( $this->IsKatyouUID($uid) ) $no = 1;
            if( $this->IsButyouUID($uid) ) $no = 2;
            if( $this->IsKoujyoutyouUID($uid) ) $no = 3;
        }
        
        $type = $memo = "";
        for( ; $no<3; $no++ ) {
            switch ($no) {
                case  3:    // 工場長 何もしない
                    break;
                case  2:    // 部長 → 工場長へお知らせ
                    $send = $this->getKoujyotyouUID();
                    break;
                case  1:    // 課長 → 部長へお知らせ
                    $send = $this->getButyouUID($deploy);
                    break;
                default:    // 一般 → 課長へお知らせ
                    $send = $this->getKatyouUID($deploy);
                    break;
            }
            if( ! $this->IsAbsence(date('Ymd'), $send) ) break; // 出勤していれば、ループを抜ける
            $type = "absence";
            if($memo) $memo .= " / ";
            $memo .= $this->getName($send);
        }
        
        if( $no>2 ) return; // 何もしない。
        
        $this->SendMail("Hurry", $send, $date, $deploy, $uid, $type, $memo);
    }

    // 次の承認者へお知らせ処理
    public function NextMaile($date, $deploy)
    {
        $no = $this->getPostsNo();
        
        $type = $memo = "";
        for( ; $no<3; $no++ ) {
            switch ($no) {
                case  3:    // 工場長 何もしない
                    break;
                case  2:    // 部長 → 工場長へお知らせ
                    $send = $this->getKoujyotyouUID();
                    break;
                case  1:    // 課長 → 部長へお知らせ
                    $send = $this->getButyouUID($deploy);
                    break;
                default:    // 一般 → 課長へお知らせ
                    $send = $this->getKatyouUID($deploy);
                    break;
            }
            if( ! $this->IsAbsence(date('Ymd'), $send) ) break; // 出勤していれば、ループを抜ける
            $type = "absence";
            if($memo) $memo .= " / ";
            $memo .= $this->getName($send);
        }
        
        if( $no>2 ) return; // 何もしない。
        
        $this->SendMail("Next", $send, $date, $deploy, "", $type, $memo);
    }

    // 取り消し処理
    public function Cancel($request)
    {
        $date   = $request->get('w_date');          // 作業日
        $type   = $request->get('type');            // type = 'yo' or 'ji'
        $uid    = $request->get('cancel_uid');      // 取り消し対象者UID
        $deploy = $request->get('ddlist_bumon');    // 取り消し対象者部署名
        $memo   = $request->get('reason');          // 取り消し理由
        
        // 工場長 承認済み なら取り消しのお知らせ
        if( $this->IsPosAdmit($date, $type, 'ko', $uid) ) {
            $this->SendMail("Cancel", $this->getKoujyotyouUID(), $date, $deploy, $uid, $type, $memo);
        }
        
        // 部長 承認済み なら取り消しのお知らせ
        if( $this->IsPosAdmit($date, $type, 'bu', $uid) ) {
            $this->SendMail("Cancel", $this->getButyouUID($deploy), $date, $deploy, $uid, $type, $memo);
        }
        // 課長 承認済み なら取り消しのお知らせ
        if( $this->IsPosAdmit($date, $type, 'ka', $uid) ) {
            $this->SendMail("Cancel", $this->getKatyouUID($deploy), $date, $deploy, $uid, $type, $memo);
        }
        
        if( $type == 'yo' ) {
            $this->ReportDelete($date, $uid);               // 対象者 削除
            $no = $request->get('cancel_uno');
            $this->ReportInsert($date, $deploy, $no, $uid); // 対象者 初期状態で追加
        } else {
            $set   = "ji_str_h=NULL, ji_str_m=NULL, ji_end_h=NULL, ji_end_m=NULL, ji_content=NULL, ji_ad_rt=0, ji_ad_st=NULL, ji_ad_ka=NULL, ji_ad_bu=NULL, ji_ad_ko=NULL";
            $where = "date='$date' AND uid='$uid'";
            $this->ReportUpDate($set, $where); // 残業結果報告のみ削除する
        }
    }

    // 最終承認処理（個別）
    public function Notice($date, $deploy, $uid)
    {
        $no = $this->getPostsNo();
        switch ($no) {
            case  3:    // 工場長 承認 → 部長・課長へお知らせ
                if( $this->IsPosAdmit($date, 'yo', 'bu', $uid) ) {
                    $this->SendMail("Notice", $this->getButyouUID($deploy), $date, $deploy, $uid, "", "");
                }
            case  2:    // 部長   承認 → 課長へお知らせ
                if( $this->IsPosAdmit($date, 'yo', 'ka', $uid) ) {
                    $this->SendMail("Notice", $this->getKatyouUID($deploy), $date, $deploy, $uid, "", "");
                }
                break;
            default:    // 課長   承認 → 何もしない。
                break;
        }
    }

    // 最終承認処理（一括）
    public function Notice2($date, $deploy, $name_list)
    {
        $no = $this->getPostsNo();
        switch ($no) {
            case  3:    // 工場長 承認 → 部長・課長へお知らせ
                $this->SendMail("Notice", $this->getButyouUID($deploy), $date, $deploy, $name_list, "", "");
            case  2:    // 部長   承認 → 課長へお知らせ
                $this->SendMail("Notice", $this->getKatyouUID($deploy), $date, $deploy, $name_list, "", "");
                break;
            default:    // 課長   承認 → 何もしない。
                break;
        }
    }

    // 否認処理（個別）
    public function Deny($type, $date, $deploy, $uid, $memo)
    {
        $no = $this->getPostsNo();
        switch ($no) {
            case  3:    // 工場長 否認 → 部長・課長へお知らせ
                if( $this->IsPosAdmit($date, $type, 'bu', $uid) ) {
                    $this->SendMail("Deny", $this->getButyouUID($deploy), $date, $deploy, $uid, $type, $memo);
                }
            case  2:    // 部長   否認 → 課長へお知らせ
                if( $this->IsPosAdmit($date, $type, 'ka', $uid) ) {
                    $this->SendMail("Deny", $this->getKatyouUID($deploy), $date, $deploy, $uid, $type, $memo);
                }
                break;
            default:    // 課長   否認 → 何もしない。
                break;
        }
    }

    // 否認処理（一括）
    public function Deny2($type, $date, $deploy, $name_list, $memo)
    {
        $no = $this->getPostsNo();
        switch ($no) {
            case  3:    // 工場長 否認 → 部長・課長へお知らせ
                $this->SendMail("Deny", $this->getButyouUID($deploy), $date, $deploy, $name_list, $type, $memo);
            case  2:    // 部長   否認 → 課長へお知らせ
                $this->SendMail("Deny", $this->getKatyouUID($deploy), $date, $deploy, $name_list, $type, $memo);
                break;
            default:    // 課長   否認 → 何もしない。
                break;
        }
    }

// ============================================================================
// 共通 =======================================================================
// ============================================================================
    // ログインユーザーID 取得
    public function getUID()
    {
        return $this->uid;
    }
    
    // 年月日のドロップダウンリスト作成
    public function getSelectOptionDate($start, $end, $def)
    {
        for ($i = $start; $i <= $end ; $i++) {
            if ($i == $def) {
                echo "<option value='" . sprintf("%02d", $i) . "' selected>" . $i . "</option>";
            } else {
                echo "<option value='" . sprintf("%02d", $i) . "'>" . $i . "</option>";
            }
        }
    }
    
    // 選択可能な部門のドロップダウンリスト作成
    public function setSelectOptionBumon($request)
    {
        $b_name = $this->getBumonName();   // 部門名取得
        array_unshift($b_name, "---- 選択して下さい ----");
        
        $max = count($b_name);
        for( $i = 0; $i < $max ; $i++ ) {
            if( $this->IsDisp($i) ) {
                if( $request->get('ddlist_bumon') == $b_name[$i] ) {
                    echo "<option value='{$b_name[$i]}' selected>{$b_name[$i]}</option>";
                } else {
                    echo "<option value='{$b_name[$i]}'>{$b_name[$i]}</option>";
                }
            }
        }
    }
    
    // 指定期間の休日情報取得
    public function getHolidayRang($s_year, $e_year)
    {
        if( !$s_year || !$e_year ) return "";
        
        $query = "
                    SELECT  tdate           AS 日付
                    FROM    company_calendar
                    WHERE   tdate >= '{$s_year}0101' AND tdate <= '{$e_year}1231' AND bd_flg = 'f'
                 ";
        if( getResult2($query, $res) <= 0 ) {
            return "";
        }
        return $res;
    }
    
    // 休日ですか？
    public function IsHoliday($date)
    {
        if( !$date ) return false;
        
        $query = "
                    SELECT  tdate           AS 日付,     -- 0
                            bd_flg          AS 営業日,   -- 1
                            note            AS コメント  -- 2
                    FROM    company_calendar
                    WHERE   tdate = '{$date}' AND bd_flg = 'f'
                 ";
        if( getResult2($query, $res) <= 0 ) {
            return false;
        }
        return true;
    }
    
    // 指定年月日とその曜日を取得
    public function getTargetDateDay($target_date, $cap)
    {
        $week = array(' (日)',' (月)',' (火)',' (水)',' (木)',' (金)',' (土)');
        
        $day_no = date('w', strtotime($target_date));
        
        if( $cap != 'on') {
            if( $day_no == 0 ) {            // 日曜日（色：赤）
                return $target_date . "<font color='red'>$week[$day_no]</font>";
            } else if( $day_no == 6 ) {     // 土曜日（色：青）
                return $target_date . "<font color='blue'>$week[$day_no]</font>";
            } else if( $this->IsHoliday($target_date) ) {  // 会社カレンダー休日（色：赤）
                return $target_date . "<font color='red'>$week[$day_no]</font>";
            } else {
                return $target_date . $week[$day_no];         // その他 平日 営業日（色：デフォルト黒）
            }
        } else {
            return $target_date . $week[$day_no];   // 曜日の色を変更せず返す。
        }
    }
    
    // 社員名取得
    public function getName($str)
    {
        $query = "SELECT name FROM user_detailes WHERE uid='$str'";
        $res = array();
        if ( $this->getResult2($query, $res) <= 0 ) {
            return '';
        }
        return trim($res[0][0]);
    }

    // 職位名を返す
    public function getPostsName()
    {
        return $this->posts_na;
    }

    // 職位Noを返す 1 or 2 or 3
    public function getPostsNo()
    {
        return $this->posts_no;
    }

    // 承認状況を取得
    public function getAdmitStatus($root, $no)
    {
        if( $no == "" ) return "----";
        if( $root != '-1' && ($root == $no || $root < $no) ) return "承認 済";// return "承認 完了";

        $status = "";
        switch ($no) {
            case  0:    // 課長 承認待ち
                $status = "<font style='background-color:Cyan;'>課長 承認待ち</font>";
                break;
            case  1:    // 部長 承認待ち
                if( $root == "-1" ) {
                    $status = "<font style='color:red;'>課長 否認</font>";
                } else {
                    $status = "<font style='background-color:Lime;'>部長 承認待ち</font>";
                }
                break;
            case  2:    // 工場長 承認待ち
                if( $root == "-1" ) {
                    $status = "<font style='color:red;'>部長 否認</font>";
                } else {
                    $status = "<font style='background-color:GhostWhite;'>工場長 承認待ち</font>";
                }
                break;
            case  3:    // 承認 完了
                if( $root == "-1" ) {
                    $status = "<font style='color:red;'>工場長 否認</font>";
                } else {
                    $status = "承認 済";
                }
//                $status = "承認 完了";
                break;
            default:
                $status = "----";
                break;
        }
        return $status;
    }

// ============================================================================
// 申告 =======================================================================
// ============================================================================
    // 時刻のドロップダウンリスト作成
    public function setSelectOptionTime($start, $end, $def)
    {
        echo "<option value='-1'>--</option>";
        for ($i = $start; $i <= $end ; $i++) {
            if ($i == $def) {
                echo "<option value='" . sprintf("%02s",$i) . "' selected>" . $i . "</option>";
            } else {
                if( $end == 23 ) {
                    echo "<option value='" . sprintf("%02s",$i) . "'>" . $i . "</option>";
                }
                if( $end == 59 ) {
                    if( $i == 0 || $i%5 == 0 ) {
                        echo "<option value='" . sprintf("%02s",$i) . "'>" . $i . "</option>";
                    }
                }
            }
        }
    }
    
    // 指定日付と部署の表示用データ取得
    public function getViewData($day, $deploy, &$field, &$res)
    {
        $query = "SELECT * FROM over_time_report WHERE date='$day' AND deploy='$deploy' ORDER BY no";
        $res = $field = array();
        return getResultWithField2( $query, $field, $res );
    }

    // 指定部門の氏名を取得
    public function GetNameList($bumon, &$res)
    {
        $rows = 0;
        $where = $this->getBumonActID($bumon);
        if( $where != '' ) {
            $where = "WHERE " . $where . " AND ud.retire_date IS NULL ";
            if( $bumon == "生産管理課 計画・購買係" ) {
                $order = "ORDER BY ud.pid DESC, ud.sid DESC, ud.uid ASC";
            } else {
                $order = "ORDER BY ud.sid DESC, ud.pid DESC, ud.uid ASC";   // 通常
            }
            $query = "SELECT ud.uid, ud.name FROM user_detailes AS ud LEFT OUTER JOIN cd_table AS ct USING(uid) LEFT OUTER JOIN act_table AS at USING(act_id) $where $order";
//            $_SESSION['s_sysmsg'] .= 'GetNameList()::' . $query;
            $res = array();
            if ( ($rows=getResultWithField2($query, $field, $res)) <= 0 ) {
                ; //$_SESSION['s_sysmsg'] .= '登録がありません！';
            } else {
                ; //$_SESSION['s_sysmsg'] .= $rows . '件あります。';
            }

        }
        return $rows;
    }

    // 指定部門の氏名を取得
    public function NameListCheck($date, &$res, $max)
    {
        for( $i=0; $i<$max; $i++ ) {
            $uid = trim($res[$i][0]);
            $query = "SELECT deploy FROM over_time_report WHERE date='$date' AND uid='$uid'";
            $w_res = array();
            if( getResult2($query, $w_res) > 0 ) {
                unset($res[$i]);
            }
        }
        $res = array_values($res);
        return count($res);
    }

    // 指定日付・ユーザー追加
    public function ReportInsert($date, $deploy, $no, $uid)
    {
        $columns = " date,    deploy,     no,    uid";
        $values  = "'$date', '$deploy', '$no', '$uid'";
        $insert_qry = "INSERT INTO over_time_report ($columns) VALUES ($values);";
        return query_affected($insert_qry);
    }

    // 指定情報・条件で更新
    public function ReportUpDate($set, $where)
    {
        $update_qry = "UPDATE over_time_report SET $set WHERE $where";
        return query_affected($update_qry);
    }

    // 指定日付・ユーザー削除
    public function ReportDelete($date, $uid)
    {
        $delete_qry = "DELETE FROM over_time_report WHERE date='$date' AND uid='$uid'";
        return query_affected($delete_qry);
    }

    // 現在登録されているデータと読み込み時のデータを比較
    public function ReportDiff($date, $uid, $r, $request)
    {
        $query = "SELECT * FROM over_time_report WHERE date='$date' AND uid='$uid'";
        $res = array();
        if ( $this->getResult2($query, $res) <= 0 ) {
            return false;
        }
        
        $fiels = $request->get('fiels');
        for( $f=0; $f<$fiels; $f++ ) {
            if( $f==14 || $f==15 ) continue;  // 部課長コメントはスキップ。
            $data = $request->get("res{$r}_{$f}");
            if( $res[0][$f] != $data ) {
                return false;
            }
        }
        return true;
    }

    // 読み込み時のデータと選択・入力データを比較 更新していいですか？
    public function IsDataUp($request, $r)
    {
        if( $request->get('ddlist_y_s_h' . $r) ) {  // 予定の 開始 時 を取得
            if( $request->get("res{$r}_9") == '-1' ) return true; // 状態 否認
            if( ($b = $request->get("ddlist_y_s_h{$r}"))=='-1' ) $b = "";
            if( $request->get("res{$r}_4") != $b ) return true;
            if( ($b = $request->get("ddlist_y_s_m{$r}"))=='-1' ) $b = "";
            if( $request->get("res{$r}_5") != $b ) return true;
            if( ($b = $request->get("ddlist_y_e_h{$r}"))=='-1' ) $b = "";
            if( $request->get("res{$r}_6") != $b ) return true;
            if( ($b = $request->get("ddlist_y_e_m{$r}"))=='-1' ) $b = "";
            if( $request->get("res{$r}_7") != $b ) return true;
            if( $request->get("res{$r}_8") != $request->get("z_j_r{$r}") ) return true;
        } else if( $request->get('ddlist_j_s_h' . $r) ) {  // 実績の 開始 時 を取得
            if( $request->get("res{$r}_22") == '-1' ) return true; // 状態 否認
            if( ($b = $request->get("ddlist_j_s_h{$r}"))=='-1' ) $b = "";
            if( $request->get("res{$r}_16") != $b ) return true;
            if( ($b = $request->get("ddlist_j_s_m{$r}"))=='-1' ) $b = "";
            if( $request->get("res{$r}_17") != $b ) return true;
            if( ($b = $request->get("ddlist_j_e_h{$r}"))=='-1' ) $b = "";
            if( $request->get("res{$r}_18") != $b ) return true;
            if( ($b = $request->get("ddlist_j_e_m{$r}"))=='-1' ) $b = "";
            if( $request->get("res{$r}_19") != $b ) return true;
            if( $request->get("res{$r}_20") != $request->get("j_g_n{$r}") ) return true;
        }
        return false;
    }

    // 時間外 申告 レコード更新
    public function ReportRenewal($request, $type, $no)
    {
        if( $type == 'yo' ) {
            $flag = $request->get('ddlist_y_s_h' . $no);     // 予定の 開始 時 を取得
        } else if( $type == 'ji' ) {
            $flag = $request->get('ddlist_j_s_h' . $no);     // 実績の 開始 時 を取得
        } else {
            $_SESSION['s_sysmsg'] .= 'レコード更新 不可 type=[' . $type . ']';
            return;
        }

        if( $flag != '' ) { // 時間のドロップダウンリスト有効者なら通る
            $date   = $request->get('w_date');
            $deploy = $request->get('ddlist_bumon');
            $uid    = $request->get('uid' . $no);
            $where  = "date='$date' AND uid='$uid'";
            if( $flag == -1 ) { // 開始時 指定なし
                // 既存データのクリア
                $this->TimeInfoClear($type, $where);            // 時間情報
                $this->ConttentInfoClear($type, $no, $where);   // 内容＋備考
                $this->AdmitInfoClear($type, $where);           // 承認情報
            } else {
                // 選択した 開始 時 分 終了 時 分 を 変数へセット
                $this->setTimeInfo($request, $type, $no);
                $this->TimeInfoUpDate($type, $where);                   // 時間情報更新
                $this->ConttentInfoUpDate($request, $type, $no, $where);// 内容＋備考更新
                $this->AdmitInfoUpDate($request, $type, $uid, $where);  // 承認情報更新
                if( $type=='yo') {
                    $this->Hurry($date, $deploy, $uid); // 至急
                } else {
                    $this->AfterReport($date, $deploy, $uid); // 事後
                }
            }
        }
    }

    // 時間外 申告 時間情報取得
    public function getTimeInfo(&$s_h, &$s_m, &$e_h, &$e_m)
    {
        $s_h = $this->str_hour; $s_m = $this->str_min;
        $e_h = $this->end_hour; $e_m = $this->end_min;
    }

    // 時間外 申告 時間情報取得 $type = 'yo' or 'ji'
    public function setTimeInfo($request, $type, $no)
    {
        if( $type == 'yo' ) {
            $str = 'y';
        } else if( $type == 'ji' ) {
            $str = 'j';
        } else {
            $_SESSION['s_sysmsg'] .= '時間情報セット 不可 type=[' . $type . ']';
            return;
        }

        $str_h_name = 'ddlist_' . $str . '_s_h'; $str_m_name = 'ddlist_' . $str . '_s_m';
        $end_h_name = 'ddlist_' . $str . '_e_h'; $end_m_name = 'ddlist_' . $str . '_e_m';

        $this->str_hour = $request->get($str_h_name . $no);
        $this->str_min  = $request->get($str_m_name . $no);
        $this->end_hour = $request->get($end_h_name . $no);
        $this->end_min  = $request->get($end_m_name . $no);
    }

    // 時間外 申告 時間情報クリア $type = 'yo' or 'ji'
    public function TimeInfoClear($type, $where)
    {
        $set = "{$type}_str_h=NULL, {$type}_str_m=NULL, {$type}_end_h=NULL, {$type}_end_m=NULL";

        $this->ReportUpDate($set, $where);
    }

    // 時間外 申告 時間情報更新 $type = 'yo' or 'ji'
    public function TimeInfoUpDate($type, $where)
    {
        $this->getTimeInfo($s_h, $s_m, $e_h, $e_m);

        $set = "{$type}_str_h='$s_h', {$type}_str_m='$s_m', {$type}_end_h='$e_h', {$type}_end_m='$e_m'";

        $this->ReportUpDate($set, $where);
    }

    // 時間外 申告 内容＋備考クリア $type = 'yo' or 'ji'
    public function ConttentInfoClear($type, $no, $where)
    {
        $set = "{$type}_content=NULL, ji_remarks=NULL";

        $this->ReportUpDate($set, $where);
    }

    // 時間外 申告 内容＋備考更新 $type = 'yo' or 'ji'
    public function ConttentInfoUpDate($request, $type, $no, $where)
    {
        if( $type == 'yo' ) {
            $content_name = 'z_j_r';
        } else if( $type == 'ji' ) {
            $content_name = 'j_g_n';
        } else {
            $_SESSION['s_sysmsg'] .= '内容＋備考更新 不可 type=[' . $type . ']';
            return;
        }

        $content = $request->get($content_name . $no);
        $set = "{$type}_content='$content'";
        if( ($bikou = $request->get('bikou' . $no)) != "" ) {
            $set .= ", ji_remarks='$bikou'";
        }
        $this->ReportUpDate($set, $where);
    }

    // 曜日による承認ルート
    public function GetDayAdmitRoot($date)
    {
        $day_no = date('w', strtotime($date));
        if( $this->IsHoliday($date) ) {                // 会社カレンダー休日
            $root = 3;
        } else if ( $day_no == 3 || $day_no == 5 ) {    // 水、金 曜日
            $root = 3;
            if($this->IsProlong()) $root = 1;
        } else {    // その他 月、火、木 曜日
            $root = -1;
        }
        return $root;
    }

    // 延長ですか？
    public function IsProlong()
    {
        $this->getTimeInfo($s_h, $s_m, $e_h, $e_m);
        // 初期化
        $diffTime = array();
        // タイムスタンプの差を計算
        $difSeconds = strtotime($e_h . ':' . $e_m) - strtotime('17:30');
        if($difSeconds<=0) return true; // 17:15 までは延長
        return false;
    }

    // 時間による承認ルート
    public function GetTimeAdmitRoot()
    {
        $this->getTimeInfo($s_h, $s_m, $e_h, $e_m);
//        $_SESSION['s_sysmsg'] .= 'GetTimeAdmitRoot()::' . $s_h . ':' . $s_m . ' - ' . $e_h . ':' . $e_m;
        // 初期化
        $diffTime = array();
        // タイムスタンプの差を計算
//        $difSeconds = strtotime($e_h . ':' . $e_m) - strtotime($s_h . ':' . $s_m);
        $difSeconds = strtotime($e_h . ':' . $e_m) - strtotime('17:30');    // 17:30以降が残業扱い
        // 分の差を取得
        $difMinutes = $difSeconds / 60;
        $diffTime['minutes'] = $difMinutes % 60;
        // 時の差を取得
        $difHours = ($difMinutes - ($difMinutes % 60)) / 60;
        $diffTime['hours'] = $difHours;
//        $_SESSION['s_sysmsg'] .= 'GetTimeAdmitRoot()::' . $diffTime['hours'] . ':' . $diffTime['minutes'];
        // 1時間までならルート1
        if( $diffTime['hours'] < 1 ) return 1;
        if( $diffTime['hours'] == 1  && $diffTime['minutes'] == 0) return 1;
        
        // 1時間を超えるならルート2
        return 2;
    }

    // 承認ルート取得
    public function GetAdmitRoot($date)
    {
        $root = $this->GetDayAdmitRoot($date);
        if( $root < 0 ) {
            if($this->IsProlong()) {
                $root = 1;
            } else {
                $root = $this->GetTimeAdmitRoot();
            }
        }
        return $root;
    }

    // 時間外 申告 承認情報クリア $type = 'yo' or 'ji'
    public function AdmitInfoClear($type, $where)
    {
        if( $type == 'yo' ) {
            $set = "{$type}_ad_rt=NULL, {$type}_ad_st=NULL, {$type}_ad_ka=NULL, {$type}_ad_bu=NULL, {$type}_ad_ko=NULL, ji_ad_rt=NULL";
        } else if( $type == 'ji' ) {
            $set = "{$type}_ad_rt=NULL, {$type}_ad_st=NULL, {$type}_ad_ka=NULL, {$type}_ad_bu=NULL, {$type}_ad_ko=NULL";
        } else {
            $_SESSION['s_sysmsg'] .= '承認情報クリア 不可 type=[' . $type . ']';
            return;
        }
        $this->ReportUpDate($set, $where);
    }

    // 時間外 申告 承認情報更新 $type = 'yo' or 'ji'
    public function AdmitInfoUpDate($request, $type, $uid, $where)
    {
        $date = $request->get('w_date');
        
        $root = $this->GetAdmitRoot($date);
        
        $set = "{$type}_ad_rt='$root', {$type}_ad_st=0";
        switch ($root) {
            case 1:     // 課長の承認が必要
                $set .= ", {$type}_ad_ka='m', {$type}_ad_bu=NULL, {$type}_ad_ko=NULL";
                break;
            case 2:     // 課長、部長の承認が必要
                $set .= ", {$type}_ad_ka='m', {$type}_ad_bu='m', {$type}_ad_ko=NULL";
                break;
            case 3:     // 課長、部長、工場長の承認が必要
                $set .= ", {$type}_ad_ka='m', {$type}_ad_bu='m', {$type}_ad_ko='m'";
                break;
            default:    // 
                $_SESSION['s_sysmsg'] .= '承認情報更新 不可 root=[' . $root . ']';
                return;
        }

        if( $this->IsKoujyoutyouUID($uid) ) {
            $set = "{$type}_ad_rt='3', {$type}_ad_st=2, {$type}_ad_ka=NULL, {$type}_ad_bu=NULL, {$type}_ad_ko='m'";
        } else if( $this->IsButyouUID($uid) ) {
            $set = "{$type}_ad_rt='3', {$type}_ad_st=2, {$type}_ad_ka=NULL, {$type}_ad_bu=NULL, {$type}_ad_ko='m'";
        } else if( $this->IsKatyouUID($uid) ) {
            if( $root == 3 ) {
                $set = "{$type}_ad_rt='3', {$type}_ad_st=1, {$type}_ad_ka=NULL, {$type}_ad_bu='m', {$type}_ad_ko='m'";
            } else {
                $set = "{$type}_ad_rt='2', {$type}_ad_st=1, {$type}_ad_ka=NULL, {$type}_ad_bu='m', {$type}_ad_ko=NULL";
            }
        }
        
        $this->ReportUpDate($set, $where);
    }

    // 指定日付・部署の時間外 申告 はありますか？
    public function IsReport($request)
    {
        $date   = $request->get('w_date');
        $deploy = $request->get('ddlist_bumon');
        
        $query = "SELECT date FROM over_time_report WHERE date='$date' AND deploy='$deploy' LIMIT 1";
        if( getResult2($query, $res) <= 0 ) return false;
        
        return true;
    }

    // 時間外 申告 レポート作成
    public function ReportCreate($request)
    {
        if( $request->get('appli') == '' ) return true; // 初回は何もしない。
        
        if( $request->get('v_data') ) return true; // 前回表示時、レポートが取得できてるなら作成しない。
        
        if( $this->IsReport($request) ) {    // 現在データベース上に、レポートは存在する場合
            $_SESSION['s_sysmsg'] = "他の方が、既にデータを作成しています。※読込み直します。";
            return false;
        }
        
        $date   = $request->get('w_date');          // 指定した作業日
        $deploy = $request->get('ddlist_bumon');    // 指定した部署
        $max    = $request->get('rows');            // 部署内登録レコード数
        for( $r=0; $r<$max; $r++ ) {
            $uid = $request->get('uid' . $r);
            $this->ReportInsert($date, $deploy, $r+1, $uid);    // 基本情報新規登録
        }
        
        return true;
    }

    // 時間外 申告 テーブル作成
    public function AppliUp($request)
    {
        if( $request->get('appli') != 'up' ) return false;  // [登録]ボタンのクリック以外は抜ける。
        
        $max = $request->get('rows'); // 部署のレコード数
        $date = $request->get('w_date');
        $name = "";
        $up = false;
        for( $r=0; $r<$max; $r++ ) {
            $uid = $request->get('uid' . $r);
            if( ! $this->ReportDiff($date, $uid, $r, $request) ) {    // 前回表示データと再度データを読込み比較
                $name .= $this->getName($uid) . " / ";
                continue;
            }
            if( ! $this->IsDataUp($request, $r) ) { // 更新可能か？（時間、内容に変更があるか？）
                continue;
            }
            
            $this->ReportRenewal($request, 'yo', $r);  // 時間外 申告 レコード 事前申請 更新
            
            $this->ReportRenewal($request, 'ji', $r);  // 時間外 申告 レコード 残業結果報告 更新
            
            $up = true; // レコード更新完了フラグ
        }
        if( $name ) {
            $_SESSION['s_sysmsg'] .= "$name のデータは、登録できませんでした。※読込み直します。";
            return false;
        }
        if( ! $up ) {
            $_SESSION['s_sysmsg'] .= "登録できるデータはありませんでした。";
            return false;
        }
        
        $_SESSION['s_sysmsg'] .= 'データの登録が完了しました。';
        
        if( $this->Result($request) ) {
            echo "結果入力全て完了";
        }
        return true;
    }

    // 時間外 申告 テーブル行追加
    public function AppliAdd($request)
    {
        if( $request->get('appli') != 'add' ) return false; // [追加]ボタンのクリック以外は抜ける。
        
        $add_uid = $request->get('add_uid');
        if( ! $this->IsSyain($add_uid) ) {   // TNK社員か判断
            $_SESSION['s_sysmsg'] .= "[$add_uid] は社員でない為、登録できません。";
            return false;
        }
        
        $date   = $request->get('w_date');
        $deploy = $request->get('ddlist_bumon');
        $name   = trim($this->getName($add_uid));
        
        $query = "SELECT deploy FROM over_time_report WHERE date='$date' AND uid='$add_uid'";
        if( getResult2($query, $res) > 0 ) {
            $this->ReportDelete($date, $add_uid);
            $_SESSION['s_sysmsg'] = "$name 様を {$res[0][0]} より 削除。";
        }

        $query = "SELECT no FROM over_time_report WHERE date='$date' AND deploy='$deploy' ORDER BY no DESC LIMIT 1";
        if( getResult2($query, $res) <= 0 ) {
            $_SESSION['s_sysmsg'] .= "$name 様の追加に失敗しました。";
            return false;
        }
        $no = $res[0][0] + 1;
        $this->ReportInsert($date, $deploy, $no, $add_uid);    // 基本情報新規登録
        $_SESSION['s_sysmsg'] .= "$name 様の追加に成功しました。";
        return true;
    }

    // 時間外 申告 課長・部長コメント更新
    public function UpComment($request)
    {
        if( $request->get('appli') == '' ) return true; // 初回なら抜ける。
        
        $set = "";
        if( $com_ka = trim($request->get('comment_ka')) ) {
            $set .= "comment_ka='$com_ka'";
        } else {
            $set .= "comment_ka=NULL";
        }
        if( $set ) $set .= ", ";
        if( $com_bu = trim($request->get('comment_bu')) ) {
            $set .= "comment_bu='$com_bu'";
        } else {
            $set .= "comment_bu=NULL";
        }
        $date   = $request->get('w_date');
        $deploy = $request->get('ddlist_bumon');
        $where = "date='$date' AND deploy='$deploy'";
        
        // 書き込む前に、読み込み時と現在DBを比較し変更されていないことを確認する
        $query = "SELECT comment_ka, comment_bu, no FROM over_time_report WHERE $where ORDER BY no LIMIT 1";
        $res = array();
        if ( $this->getResult2($query, $res) <= 0 ) {
            return false;
        }
        if($request->get("res0_14")!=$res[0][0] || $request->get("res0_15")!=$res[0][1]) {
            if( $request->get('appli') == 'comment' ) {
                $_SESSION['s_sysmsg'] .= "部課長のコメントは別の所で更新されている為、更新できませんでした。";
                return false;
            }
        }
        
        $this->ReportUpDate($set, $where);
        if( $request->get('appli') == 'comment' ) {
            $_SESSION['s_sysmsg'] .= "部課長のコメントを更新しました。";
        }
        return true;
    }

    // 指定上長に承認されていますか？ type= 'yo' or 'ji' / pos = 'ka' or 'bu' or 'ko'
    public function IsPosAdmit($date, $type, $pos, $uid)
    {
        $column = $type . '_ad_' . $pos;

        $query = "SELECT $column FROM over_time_report WHERE date='$date' AND uid='$uid' AND $column='s' LIMIT 1";

        if( getResult2($query, $res) <= 0 ) return false;
        return true;
    }

    // 未承認はありますか？ type= 'yo' or 'ji'
    public function IsNoAdmit($type, $date, $uid)
    {
        $column1 = $type . '_ad_rt';
        $column2 = $type . '_ad_ka';
        $column3 = $type . '_ad_bu';
        $column4 = $type . '_ad_ko';

//        $query = "SELECT date FROM over_time_report WHERE date='$date' AND uid='$uid' AND $column1!='-1' AND ($column2='m' OR $column3='m' OR $column4='m')";
        $query = "SELECT date FROM over_time_report WHERE date='$date' AND uid='$uid' AND ($column1='-1' OR $column2='m' OR $column3='m' OR $column4='m')";
        if( getResult2($query, $res) <= 0 ) return false;
        return true;
    }

    // 申請（結果）の状態を取得
    public function getApplStatus($type, $view, $res, $idx)
    {
        if( $type == 'yo' ) {
            if( ! $view || $res[$idx][9] == "" || $res[$idx][9] == "0" || $res[$idx][10] == "0" || ($res[$idx][10] == "1" && $res[$idx][11] == "") || ($res[$idx][10] == "2" && $res[$idx][12] == "") ) {
                $status = '−−';
            } else if( $res[$idx][9] == "-1" ) {
                $status = "否認";
            } else if( $res[$idx][9] <= $res[$idx][10] ) {
                $status = "完了";
            } else {
                $status = "途中";
            }
        } else {
            if( ! $view || $res[$idx][22] == "" || $res[$idx][22] == "0" || $res[$idx][23] == "0" || ($res[$idx][23] == "1" && $res[$idx][24] == "") || ($res[$idx][23] == "2" && $res[$idx][25] == "") ) {
                $status = '−−';
            } else if( $res[$idx][22] == "-1" ) {
                $status = "否認";
            } else if( $res[$idx][22] <= $res[$idx][23] ) {
                $status = "完了";
            } else {
                $status = "途中";
            }
        }
        
        return $status;
    }

// ============================================================================
// 承認 =======================================================================
// ============================================================================
    // 検索部署名取得
    public function getWhereDeploy()
    {
        if( $this->IsMaster() ) return "(deploy IS NOT NULL)";

        switch ($this->act_id) {
            case 600:   // 工場長
                if( $this->uid == '012394' ) {
                    return "(deploy='製造部 製造１課' OR deploy='製造部 製造２課')";
                }
                return "(deploy IS NOT NULL)";
            case 610:   // 管理部
                return "(deploy='総務課' OR deploy='商品管理課')";
            case 605:   // ＩＳＯ事務局
            case 650:   // 管理部 総務課
            case 651:   // 管理部 総務課 総務
            case 660:   // 管理部 総務課 財務
                return "(deploy='総務課')";
            case 670:   // 管理部 商品管理課
                return "(deploy='商品管理課')";
            case 501:   // 技術部
                return "(deploy='品質保証課' OR deploy='技術課')";
            case 174:   // 技術部 品質管理課
            case 517:   // 技術部 品質管理課 カプラ検査担当
            case 537:   // 技術部 品質管理課 カプラ検査担当
            case 581:   // 技術部 品質管理課 カプラ検査担当
                return "(deploy='品質保証課')";
            case 173:   // 技術部 技術課
            case 515:   // 技術部 技術課
            case 535:   // 技術部 技術課
                return "(deploy='技術課')";
            case 582:   // 製造部
                return "(deploy='製造部 製造１課' OR deploy='製造部 製造２課')";
            case 518:   // 製造部 製造１課
            case 519:   // 製造部 製造１課
            case 556:   // 製造部 製造１課
            case 520:   // 製造部 製造１課
                return "(deploy='製造部 製造１課')";
            case 547:   // 製造部 製造２課
            case 528:   // 製造部 製造２課
            case 527:   // 製造部 製造２課
                return "(deploy='製造部 製造２課')";
            case 500:   // 生産部
                return "(deploy='生産管理課 計画・購買係' OR deploy='生産管理課 資材係' OR deploy='カプラ組立課 標準係ＭＡ' OR deploy='カプラ組立課 標準係ＨＡ' OR deploy='カプラ組立課 特注係' OR deploy='リニア組立課')";
            case 545:   // 生産部 生産管理課
            case 512:   // 生産部 生産管理課 計画係 Ｃ担当
            case 532:   // 生産部 生産管理課 計画係 Ｌ担当
            case 513:   // 生産部 生産管理課 購買係 Ｃ担当
            case 533:   // 生産部 生産管理課 購買係 Ｌ担当
            case 514:   // 生産部 生産管理課 資材係 カプラ資材
            case 534:   // 生産部 生産管理課 資材係 リニア資材
                return "(deploy='生産管理課 計画・購買係' OR deploy='生産管理課 資材係')";
            case 176:   // 生産部 カプラ組立課
            case 522:   // 生産部 カプラ組立MA担当
            case 523:   // 生産部 カプラ組立HA担当
            case 525:   // 生産部 カプラ特注担当
                return "(deploy='カプラ組立課 標準係ＭＡ' OR deploy='カプラ組立課 標準係ＨＡ' OR deploy='カプラ組立課 特注係')";
            case 551:   // 生産部 リニア組立課
            case 175:   // 生産部 リニア組立担当
            case 572:   // 生産部 ピストン研磨担当
                return "(deploy='リニア組立課')";
            default:
                return "(deploy IS NULL)";
        }
    }

    // 指定条件の部署名を取得
    public function GetDeployName($where, &$res)
    {
        $query = "SELECT DISTINCT deploy FROM over_time_report WHERE $where ORDER BY deploy";
        
        $res= array();
        
        if( ($rows=getResult2($query, $res)) <= 0 ) return -1;
        
        return $rows;
    }

    // 指定条件の日付と部署名を取得
    public function GetDateDeploy($where, &$res)
    {
        $query = "SELECT DISTINCT date, deploy FROM over_time_report WHERE $where ORDER BY date, deploy";
        
        $res= array();
        
        if( ($rows=getResult2($query, $res)) <= 0 ) return -1;
        
        return $rows;
    }

    // 承認状況取得・・・NULL=不要、's'=承認、'h'=否認、'm'=未承認、'f'=不在
    public function GetAdmitInfo($flag)
    {
        if( $flag == 's' ) {
            return "<font color='red'>OK</font>";
        } else if( $flag == 'h' ) {
            return "<font color='red'>否認</font>";
        } else if( $flag == 'm' ) {
            return '未';
        } else if( $flag == 'f' ) {
            return "<font color='red'>不在</font>";
        } else {
            return '----';
        }
    }

    // 指定条件の申告レコードを取得
    public function GetReport($where, &$res)
    {
        $query = "SELECT * FROM over_time_report WHERE $where ORDER BY date, deploy, no";
        if( ($rows=getResult2($query, $res)) <= 0 ) return -1;
        return $rows;
    }

    // 課長、課長代理 ですか？
    public function IsKatyou()
    {
        $query = "
                    SELECT          ct.act_id
                    FROM            user_detailes   AS ud
                    LEFT OUTER JOIN cd_table        AS ct   USING(uid)
                    WHERE           ud.uid = '$this->uid' AND (ud.pid=46 OR ud.pid=50 )
                 ";
        $res = array();
        if( ($rows=$this->getResult2($query, $res)) <= 0 ) return false;
        
        return true;
    }

    // 課長、課長代理 ユーザーIDですか？
    public function IsKatyouUID($uid)
    {
        $query = "
                    SELECT          ct.act_id
                    FROM            user_detailes   AS ud
                    LEFT OUTER JOIN cd_table        AS ct   USING(uid)
                    WHERE           ud.uid = '$uid' AND (ud.pid=46 OR ud.pid=50 )
                 ";
        $res = array();
        if( ($rows=$this->getResult2($query, $res)) <= 0 ) return false;
        
        return true;
    }

    // 部長、部長代理 ですか？（95=副工場長）
    public function IsButyou()
    {
        $query = "
                    SELECT          ct.act_id
                    FROM            user_detailes   AS ud
                    LEFT OUTER JOIN cd_table        AS ct   USING(uid)
                    WHERE           ud.uid = '$this->uid' AND (ud.pid=47 OR ud.pid=70 OR ud.pid=95 )
                 ";
        $res = array();
        if( $this->getResult2($query, $res) <= 0 ) return false;
        
        return true;
    }

    // 部長、部長代理 ユーザーIDですか？（95=副工場長）
    public function IsButyouUID($uid)
    {
        $query = "
                    SELECT          ct.act_id
                    FROM            user_detailes   AS ud
                    LEFT OUTER JOIN cd_table        AS ct   USING(uid)
                    WHERE           ud.uid = '$uid' AND (ud.pid=47 OR ud.pid=70 OR ud.pid=95 )
                 ";
        $res = array();
        if( $this->getResult2($query, $res) <= 0 ) return false;
        
        return true;
    }

    // 工場長 ですか？
    public function IsKoujyoutyou()
    {
        $query = "
                    SELECT          ct.act_id
                    FROM            user_detailes   AS ud
                    LEFT OUTER JOIN cd_table        AS ct   USING(uid)
                    WHERE           ud.uid = '$this->uid' AND ud.pid=110
                 ";
        $res = array();
        if( $this->getResult2($query, $res) <= 0 ) return false;
        
        return true;
    }

    // 工場長 ユーザーIDですか？
    public function IsKoujyoutyouUID($uid)
    {
        $query = "
                    SELECT          ct.act_id
                    FROM            user_detailes   AS ud
                    LEFT OUTER JOIN cd_table        AS ct   USING(uid)
                    WHERE           ud.uid = '$uid' AND ud.pid=110
                 ";
        $res = array();
        if ( $this->getResult2($query, $res) <= 0 ) return false;
        
        return true;
    }

    // 承認情報を更新
    public function AdmitUp($request)
    {
        $max     = $request->get('rows_max');
        $column  = $request->get('column');             // xx_ad_
        $column1 = $column . $request->get('posts');    // xx_ad_xx
        $column2 = $column . 'st';                      // xx_ad_st
        $column3 = $column . 'rt';                      // xx_ad_rt
        $pos_no  = $this->getPostsNo();

        for( $i=0; $i<$max; $i++ ) {
            if( $request->get('radio_yo' . $i) ) {
                $type = 'yo';
            } else if( $request->get('radio_ji' . $i) ) {
                $type = 'ji';
            } else {
                continue;
            }
            
            $flag   = $request->get('radio_' . $type . $i);
            $date   = $request->get('w_date' . $i);
            $deploy = $request->get('deploy' . $i);
            
            if( $flag ) {   // 承認 or 否認 選択されている
                $rows = $request->get('rows' . $i);
                $up_flag = false;       // 更新フラグ
                $hurry_maile = false;   // 至急フラグ
                $next_maile = false;    // 次の人へメール送信フラグ
                $deny_maile = false;    // 否認フラグ
                $notice_maile = false;  // 最終承認フラグ
                $name_list = "";        // 氏名リスト
                for( $r=0; $r<$rows; $r++ ) {
                    $up   = $request->get('up'  . $i . '_' . $r);
                    if( $up != 'on' ) continue; // 更新処理しない人はスキップ。
                    $uid  = $request->get('uid' . $i . '_' . $r);
                    $root = $request->get($type . '_root' . $i . '_' . $r);
                    $set  = "$column1='$flag', $column2=$pos_no";
                    // 不在時
                    $absence_ka = $request->get('absence_ka'  . $i . '_' . $r);
                    $absence_bu = $request->get('absence_bu'  . $i . '_' . $r);
                    if( $absence_ka == "on" ) { // 不在時承認
                        $set .= ", $column" . "ka" . "='f'";
                    }
                    if( $absence_bu == "on" ) { // 不在時承認
                        $set .= ", $column" . "bu" . "='f'";
                    }
                    if( $flag == 'h' ) {    // 否認
                        $set .= ", {$type}_ad_rt=-1";
                    } else {
                        if( $type == 'yo' ) {
                            if( $pos_no >= $root ) {
                                $set .= ", ji_ad_rt=0";
                            } else {
                                $hurry_maile = true;
                            }
                        }
                        if( $pos_no < $root ) {
                            $next_maile = true; // 次の承認者がいるならメール送信フラグON
                        }
                    }
                    
                    $where = "date='$date' AND uid='$uid' AND $column3='$root'";
                    if( $absence_ka || $absence_bu ) { // 不在時承認
                        if( $pos_no == 2) {
                            $where = "date='$date' AND uid='$uid' AND $column" . "ka" . "='m'";
                        } else { // $pos_no == 3
                            $where = "date='$date' AND uid='$uid' AND ($column" . "bu" . "='m' OR $column" . "ka" . "='m')";
                        }
                    }
                    
                    if( $this->ReportUpDate($set, $where) <= 0) {
                        $name = $this->getName($uid);
                        $_SESSION['s_sysmsg'] .= "$name の申請は取り消された可能性があります。";
                    } else {
                        if( $flag == 'h' ) {    // 否認
                            // 否認したことを承認者（課長、部長）へお知らせする処理
                            $memo = $request->get($type . '_ng_comme' . $i);
//                            $this->Deny($type, $date, $deploy, $uid, $memo);
                            $deny_maile = true;
                            if( $name_list ) $name_list .= " / ";
                            $name_list .= $this->getName($uid);
                        } else {
                            if( $type == 'yo' ) {
                                if( $pos_no >= $root ) {
                                    // 最終承認まで承認したことを承認者（課長、部長）へお知らせする処理
//                                    $this->Notice($date, $deploy, $uid);
                                    $notice_maile = true;
                                    if( $name_list ) $name_list .= " / ";
                                    $name_list .= $this->getName($uid);
                                }
                            }
                        }
                        $up_flag = true;
/* 事後報告のお知らせは、最初の承認者のみでよい。 *
                        if( $type == 'ji' && $request->get('yo_root' . $i . '_' . $r) == '' ) {
                            $this->AfterReport($date, $deploy, $uid); // 事後報告処理
                        }
/**/
                    }
                }
//                if( $up_flag && $hurry_maile ) $this->Hurry($date, $deploy, "");
                if( $up_flag && $next_maile ) $this->NextMaile($date, $deploy);
                if( $notice_maile ) $this->Notice2($date, $deploy, $name_list);
                if( $deny_maile )   $this->Deny2($type, $date, $deploy, $name_list, $memo);

                // コメントの登録
                $pos_na = $this->getPostsName();
                $name = 'comment_' . $pos_na . $i;
                if( $comment = $request->get($name) ) {
                    $set = "comment_$pos_na='$comment'";
                    $where = "date='$date' AND deploy='$deploy'";
                    $this->ReportUpDate($set, $where);
                }
            }
            if( $up_flag ) $this->Result2($date, $deploy); // 残業結果報告全て入力完了（ Result2）
        }
        $_SESSION['s_sysmsg'] .= "処理を実行しました。";
    }

    // 部署の不在者取得
    public function getDeployAbsence(&$res, &$ka, &$bu)
    {
        $where  = $this->getWhereDeploy();              // (deploy='xxx' OR deploy='xxx')
        $rows   = $this->GetDeployName($where, $res);   // 部署名を取得
        $pos_no = $this->getPostsNo();  // 1 or 2 or 3
        $now    = date('Ymd');  // 今の年月日
        for( $n=0; $n<$rows; $n++ ) {    // 不在者チェック
            switch ($pos_no) {
                case 3:   // 工場長なら部長（課長も含む）の出勤確認
                    $res[$n][2] = $this->IsAbsence($now, $this->getButyouUID($res[$n][0]));
//$res[$n][2] = true;   // TEST
                    if( $res[$n][2] ) {
                        $bu = true; // 部長不在
                        $res[$n][1] = $this->IsAbsence($now, $this->getKatyouUID($res[$n][0]));
//$res[$n][1] = true;   // TEST
//$res[$n][1] = false;   // TEST
                        if( $res[$n][1] ) $ka = true;    // 課長不在
                    }
                    break;
                case 2:   // 部長なら課長の出勤確認
                    $res[$n][1] = $this->IsAbsence($now, $this->getKatyouUID($res[$n][0]));
//$res[$n][1] = true;   // TEST
                    if( $res[$n][1] ) $ka = true;    // 課長不在
                    break;
            }
        }
        return $rows;
    }

    // 不在未承認データ
    public function GetUnapproved($d_res, $d_rows, &$where, &$res)
    {
        $column = "yo_ad_";
        $pos_no = $this->getPostsNo();  // 1 or 2 or 3
        $where1 = "";
        for( $n=0; $n<$d_rows; $n++ ) {
            if( $pos_no==3 && $d_res[$n][2] ) {  // 工場長のとき・指定部署の部長不在
                if( $where1 ) $where1 .= " OR ";
                $where1 .= "(deploy='{$d_res[$n][0]}' AND " . $column . "bu='m' AND (yo_ad_ka!='m' OR yo_ad_ka IS NULL) )";
                if( $d_res[$n][1] ) {  // 指定部署の課長不在
                    if( $where1 ) $where1 .= " OR ";
                    $where1 .= "(deploy='{$d_res[$n][0]}' AND " . $column . "ka='m')";
                }
            } else if( $pos_no==2 && $d_res[$n][1] ) {   // 部長のとき・指定部署の課長不在
                if( $where1 ) $where1 .= " OR ";
                $where1 .= "(deploy='{$d_res[$n][0]}' AND " . $column . "ka='m')";
            }
        }
        if( ! $where1 ) return -1;
        
        $where2 = $column . "st<=" . ($pos_no-2);    // xx_ad_st=(x-2)
        $where .= " AND (" . $where1 . ") AND " . $where2;   // xx_ad_xx='m' AND (deploy='xxx課' OR deploy='xxx課') AND xx_ad_st=(x-1)
        $rows = $this->GetDateDeploy($where, $res); // 未承認のある日付と部署を取得
        return $rows;
    }

    // 指定部門の部課長を含むactID取得
    public function getBuKatyouActID($b_name)
    {
        $where = "";
        
        if( $b_name == "総務課" ) {
            $where = "(ct.act_id=605 OR ct.act_id=610 OR ct.act_id=650 OR ct.act_id=651 OR ct.act_id=660) ";
        } else if( $b_name == "商品管理課" ) {
            $where = "(ct.act_id=610 OR ct.act_id=670) ";
        } else if( $b_name == "品質保証課" ) {
            $where = "(ct.act_id=501 OR ct.act_id=174 OR ct.act_id=517 OR ct.act_id=537 OR ct.act_id=581) ";
        } else if( $b_name == "技術課" ) {
            $where = "(ct.act_id=501 OR ct.act_id=173 OR ct.act_id=515 OR ct.act_id=535) ";
        } else if( $b_name == "製造部 製造１課" ) { // 600 も含んでいる為、条件に、"AND ud.uid!=999999 AND ud.pid!=110" 追加
            $where = "(ct.act_id=600 OR ct.act_id=582 OR ct.act_id=518 OR ct.act_id=519 OR ct.act_id=556 OR ct.act_id=520) AND ud.uid!=999999 AND ud.pid!=110";
        } else if( $b_name == "製造部 製造２課" ) { // 600 も含んでいる為、条件に、"AND ud.uid!=999999 AND ud.pid!=110" 追加
            $where = "(ct.act_id=600 OR ct.act_id=582 OR ct.act_id=547 OR ct.act_id=527 OR ct.act_id=528) AND ud.uid!=999999 AND ud.pid!=110";
        } else if( $b_name == "生産管理課 計画・購買係" ) {
            $where = "(ct.act_id=500 OR ct.act_id=545 OR ct.act_id=512 OR ct.act_id=532 OR ct.act_id=513 OR ct.act_id=533) ";
        } else if( $b_name == "生産管理課 資材係" ) {
            $where = "(ct.act_id=500 OR ct.act_id=545 OR ct.act_id=514 OR ct.act_id=534) ";
        } else if( $b_name == "カプラ組立課 標準係ＭＡ" ) {
            $where = "(ct.act_id=500 OR ct.act_id=176 OR ct.act_id=522) ";
        } else if( $b_name == "カプラ組立課 標準係ＨＡ" ) {
            $where = "(ct.act_id=500 OR ct.act_id=176 OR ct.act_id=523) ";
        } else if( $b_name == "カプラ組立課 特注係" ) {
            $where = "(ct.act_id=500 OR ct.act_id=176 OR ct.act_id=525) ";
        } else if( $b_name == "リニア組立課" ) {
            $where = "(ct.act_id=500 OR ct.act_id=551 OR ct.act_id=175 OR ct.act_id=572) ";
        }
        
        return $where;
    }
    
    // 指定UIDは不在ですか？
    public function IsAbsence($date, $uid)
    {
        // 8:30より前（0より小さい場合）は、強制的に居るとみなす。
        if( strtotime(date("H:i:s")) - strtotime('8:30:00') < 0 ) return false;

        $query = "
                    SELECT uid FROM working_hours_report_data_new
                    WHERE uid='$uid' AND working_date='$date' AND (absence!='00' OR str_time='0000' OR end_time!='0000')
                 ";
        $res = array();
        if( $this->getResult2($query, $res) <= 0 ) {
            return false;
        }
        return true;
    }

    // 指定部門の課長、課長代理 UID
    public function getKatyouUID($b_name)
    {
        $where_act = $this->getBuKatyouActID($b_name);
        
        $query = "
                    SELECT          ud.uid
                    FROM            user_detailes   AS ud
                    LEFT OUTER JOIN cd_table        AS ct   USING(uid)
                    WHERE           $where_act AND (ud.pid=46 OR ud.pid=50 )
                 ";
        $res = array();
        if( ($rows=$this->getResult2($query, $res)) <= 0 ) return false;
        
        return $res[0][0];
    }

    // 指定部門の部長、部長代理（95=副工場長）UID
    public function getButyouUID($b_name)
    {
        $where_act = $this->getBuKatyouActID($b_name);
        
        $query = "
                    SELECT          ud.uid
                    FROM            user_detailes   AS ud
                    LEFT OUTER JOIN cd_table        AS ct   USING(uid)
                    WHERE           $where_act AND (ud.pid=47 OR ud.pid=70 OR ud.pid=95 )
                 ";
        $res = array();
        if( $this->getResult2($query, $res) <= 0 ) return false;
        
        return $res[0][0];
    }

    // 工場長のUID
    public function getKoujyotyouUID()
    {
        $query = "
                    SELECT          ud.uid
                    FROM            user_detailes   AS ud
                    LEFT OUTER JOIN cd_table        AS ct   USING(uid)
                    WHERE           ct.act_id=600 AND ud.sid=99 AND ud.pid=110
                 ";
        $res = array();
        if( $this->getResult2($query, $res) <= 0 ) return false;
        
        return $res[0][0];
    }

// ============================================================================
// 照会 =======================================================================
// ============================================================================
    // 照会結果へ表示するデータを取得
    public function getResultsView($request, &$res)
    {
        $d_radio = $request->get("days_radio");
        $date    = $request->get("ddlist_year") . $request->get("ddlist_month"). $request->get("ddlist_day");
        $date2   = $request->get("ddlist_year2") . $request->get("ddlist_month2"). $request->get("ddlist_day2");
        $deploy  = $request->get("ddlist_bumon");
        $s_no    = $request->get("s_no");
        $m_radio = $request->get("mode_radio");
//        $_SESSION['s_sysmsg'] .= "getResultsView() select=$select, date=$date, date2=$date2, deploy=$deploy, s_no=$s_no";
        
        if( $d_radio == 1 ) {
            $where = "date='$date'";
        } else if( $d_radio == 2 ) {
            $where = "date>='$date' AND date<='$date2' ";
        } else {
            return -1;
        }
        if( $deploy != '---- 選択して下さい ----' ) {
            $where .= " AND deploy='$deploy' ";
        } else {
            $where .= " AND {$this->getWhereDeploy()} ";
        }
        if( $s_no ) {
            $where .= " AND uid='$s_no' ";
        }
        if( $m_radio == 2 ) {
            $where .= " AND yo_ad_rt!='-1' AND (ji_ad_rt='0' OR ji_ad_rt IS NULL) ";
        } else if( $m_radio == 3 ) {
            $where .= " AND ji_ad_rt!='0' ";
        }
        $where .= " AND (yo_ad_st IS NOT NULL OR ji_ad_st IS NOT NULL) ";
        
        $query = "SELECT * FROM over_time_report WHERE $where ORDER BY date, deploy, no";
        
        $res = array();
        if ( ($rows=$this->getResult2($query, $res)) <= 0 ) {
//            $_SESSION['s_sysmsg'] .= '検索（' . $query . '）';  // エラー
        }
//        $_SESSION['s_sysmsg'] .= "検索($rows) $query";  // エラー

        return $rows;
    }

    // 指定UID・日付の出勤時間取得
    public function getWorkingStrTime($uid, $date)
    {
        $date = substr($date, 0,4) . substr($date, 5,2) . substr($date, 8,2);
        $query = "SELECT str_time FROM working_hours_report_data_new WHERE uid='$uid' AND working_date='$date'";
        $res = array();
        if ( ($rows=$this->getResult2($query, $res)) <= 0 ) {
            return "----";
        }
        return $res[0][0];
    }

    // 指定UID・日付の退勤時間取得
    public function getWorkingEndTime($uid, $date)
    {
        $date = substr($date, 0,4) . substr($date, 5,2) . substr($date, 8,2);
        $query = "SELECT end_time FROM working_hours_report_data_new WHERE uid='$uid' AND working_date='$date'";
        $res = array();
        if ( ($rows=$this->getResult2($query, $res)) <= 0 ) {
            return "----";
        }
//        $res[0][0] = substr_replace($res[0][0], ":", 2, 0);
//        if($res[0][0] == "00:00") $res[0][0] = "<font style='background-color:yellow; color:blue;'>" . $res[0][0] . "</font>";
        return $res[0][0];
    }

// ============================================================================
// テスト =====================================================================
// ============================================================================
    // TEST 定時刻メール
    public function TEST()
    {
        // 工場長、部長、課長
        $where = "(ud.pid=110)";
        $where = "(ud.pid=47 OR ud.pid=70 OR ud.pid=95)";
        $where = "(ud.pid=46 OR ud.pid=50)";
        $where = "((ud.pid=110) OR (ud.pid=47 OR ud.pid=70 OR ud.pid=95) OR (ud.pid=46 OR ud.pid=50))";
        
        // 指定長の uid と act_id 取得
        $query = "
                    SELECT          uid, ct.act_id, ud.pid, trim(name)
                    FROM            user_detailes   AS ud
                    LEFT OUTER JOIN cd_table        AS ct   USING(uid)
                    WHERE           retire_date IS NULL AND $where
                 ";
        $res_list = array();
        if( ($rows_list = getResult($query, $res_list)) <= 0) exit(); // 取得不可なら終了。
        
        for( $r=0; $r<$rows_list; $r++ ) {
            $bu_act = 0;    // 初期化
            // 条件作成
            $where = "WHERE yo_ad_rt!='-1' AND ";
            if( $res_list[$r][1] == 600 ) {  // 工場長
                if( $res_list[$r][2] == 95 ) {  // 副工場長
                    $res_list[$r][1] = 582; // 製造部のact_idセット、後で判断する際に使用。
                    $where .= "yo_ad_st=1 AND yo_ad_bu='m' AND (deploy='製造部 製造１課' OR deploy='製造部 製造２課')";
                } else {
                    $where .= "yo_ad_st=2 AND yo_ad_ko='m' AND (deploy IS NOT NULL)";
                }
            } else if( $res_list[$r][1] == 610 ) {   // 管理部
                $where .= "yo_ad_st=1 AND yo_ad_bu='m' AND (deploy='総務課' OR deploy='商品管理課')";
            } else if( $res_list[$r][1] == 605 || $res_list[$r][1] == 650 || $res_list[$r][1] == 651 || $res_list[$r][1] == 660 ) { // ＩＳＯ事務局 管理部 総務課 総務 財務
                $where .= "yo_ad_st=0 AND yo_ad_ka='m' AND (deploy='総務課')";
                $bu_act = 610;
            } else if( $res_list[$r][1] == 670 ) {   // 管理部 商品管理課
                $where .= "yo_ad_st=0 AND yo_ad_ka='m' AND (deploy='商品管理課')";
                $bu_act = 610;
            } else if( $res_list[$r][1] == 501 ) {   // 技術部
                $where .= "yo_ad_st=1 AND yo_ad_bu='m' AND (deploy='品質保証課' OR deploy='技術課')";
            } else if( $res_list[$r][1] == 174 || $res_list[$r][1] == 517 || $res_list[$r][1] == 537 || $res_list[$r][1] == 581 ) { // 技術部 品質管理課
                $where .= "yo_ad_st=0 AND yo_ad_ka='m' AND (deploy='品質保証課')";
                $bu_act = 501;
            } else if( $res_list[$r][1] == 173 || $res_list[$r][1] == 515 || $res_list[$r][1] == 535 ) { // 技術部 技術課
                $where .= "yo_ad_st=0 AND yo_ad_ka='m' AND (deploy='技術課')";
                $bu_act = 501;
            } else if( $res_list[$r][1] == 582 ) { // 製造部
                $where .= "yo_ad_st=1 AND yo_ad_bu='m' AND (deploy='製造部 製造１課' OR deploy='製造部 製造２課')";
            } else if( $res_list[$r][1] == 518 || $res_list[$r][1] == 519 || $res_list[$r][1] == 556 || $res_list[$r][1] == 520 ) { // 製造部 製造１課
                $where .= "yo_ad_st=0 AND yo_ad_ka='m' AND (deploy='製造部 製造１課')";
                $bu_act = 582;
            } else if( $res_list[$r][1] == 547 || $res_list[$r][1] == 528 || $res_list[$r][1] == 527 ) { // 製造部 製造２課
                $where .= "yo_ad_st=0 AND yo_ad_ka='m' AND (deploy='製造部 製造２課')";
                $bu_act = 582;
            } else if( $res_list[$r][1] == 500 ) { // 生産部
                $where .= "yo_ad_st=1 AND yo_ad_bu='m' AND (deploy='生産管理課 計画・購買係' OR deploy='生産管理課 資材係' OR deploy='カプラ組立課 標準係ＭＡ' OR deploy='カプラ組立課 標準係ＨＡ' OR deploy='カプラ組立課 特注係' OR deploy='リニア組立課')";
            } else if( $res_list[$r][1] == 545 || $res_list[$r][1] == 512 || $res_list[$r][1] == 532 || $res_list[$r][1] == 513|| $res_list[$r][1] == 533 || $res_list[$r][1] == 514 || $res_list[$r][1] == 534 ) { // 生産部 生産管理課
                $where .= "yo_ad_st=0 AND yo_ad_ka='m' AND (deploy='生産管理課 計画・購買係' OR deploy='生産管理課 資材係')";
                $bu_act = 500;
            } else if( $res_list[$r][1] == 176 || $res_list[$r][1] == 522 || $res_list[$r][1] == 523 || $res_list[$r][1] == 525 ) { // 生産部 カプラ組立課
                $where .= "yo_ad_st=0 AND yo_ad_ka='m' AND (deploy='カプラ組立課 標準係ＭＡ' OR deploy='カプラ組立課 標準係ＨＡ' OR deploy='カプラ組立課 特注係')";
                $bu_act = 500;
            } else if( $res_list[$r][1] == 551 || $res_list[$r][1] == 175 || $res_list[$r][1] == 572 ) { // 生産部 リニア組立課
                $where .= "yo_ad_st=0 AND yo_ad_ka='m' AND (deploy='リニア組立課')";
                $bu_act = 500;
            } else {
                $where .= "(deploy IS NULL)";   // エラー
            }
            // 承認待ち件数取得
            $query = "SELECT DISTINCT date, deploy FROM over_time_report $where";
            $res_count = array();
            $rows_ken  = getResult($query, $res_count);
            
            if( $rows_ken <= 0 ) continue; // 承認待ち無しなら次へ
            

// 不在チェック処理
$superiors = false;         // 上長通知フラグ（初期化）
$date = date('Ymd');        // 今日の日付取得
$uid = $res_list[$r][0];    // 自身のUID
$query = "
            SELECT uid FROM working_hours_report_data_new
            WHERE  uid='$uid' AND working_date='$date' AND (absence!='00' OR str_time='0000' OR end_time!='0000')
         ";
$res = array();
if( getResult2($query, $res) > 0 && $res_list[$r][2] != 110 ) {
    $kojyo = false;     // 工場長通知フラグ（初期化）
    if( $res_list[$r][2]==46 || $res_list[$r][2]==50 ) {
        // 課長になるので、部長の確認、不在なら工場長まで
        for( $n=0; $n<$rows_list; $n++ ) {
            if( $res_list[$n][1] == $bu_act ) {
                $uid = $res_list[$n][0];
                break; // 自身の部長 まで
            }
        }
        $query = "
                    SELECT uid FROM working_hours_report_data_new
                    WHERE  uid='$uid' AND working_date='$date' AND (absence!='00' OR str_time='0000' OR end_time!='0000')
                 ";
        $res = array();
        if( getResult2($query, $res) <= 0 ) {
            $superiors = true;  // 上長通知フラグ（ON）
        } else {
            $kojyo = true;  // 工場長通知フラグ（ON）
        }
    } else {
        $kojyo = true;  // 工場長通知フラグ（ON）
    }
    // 工場長チェック
    if( $kojyo ) {
        for( $n=0; $n<$rows_list; $n++ ) {
            if( $res_list[$n][1] == 600 ) {
                $uid = $res_list[$n][0];
                break; // 工場長 まで
            }
        }
        $query = "
                    SELECT uid FROM working_hours_report_data_new
                    WHERE  uid='$uid' AND working_date='$date' AND (absence!='00' OR str_time='0000' OR end_time!='0000')
                 ";
        $res = array();
        if( getResult2($query, $res) <= 0 ) {
            $superiors = true;  // 上長通知フラグ（ON）
        }
    }
}

            // メースアドレス取得
            $query = "SELECT trim(name), trim(mailaddr) FROM user_detailes LEFT OUTER JOIN user_master USING(uid)";
            $where = "WHERE uid='{$uid}'";  // uid
            $where = "WHERE uid='300667'";  // uid 強制変更 ※リリース時は、コメント化
            $query .= $where;   // SQL query 文の完成
            $res_mail = array();
            if( getResult($query, $res_mail) <= 0 ) continue; // メールアドレス取得不可なら次へ
            
            // メール作成、送信
            $sendna = $res_mail[0][0];  // 名前
            $sendna = $res_list[$r][3]; // 名前 強制変更 ※リリース時は、コメント化
            $mailad = $res_mail[0][1];  // メールアドレス
            $_SESSION['u_mailad']  = $mailad;
            $to_addres = $mailad;
            $add_head = "";
if( $superiors ) $sendna = $res_list[$n][3];  // 名前 強制変更 ※リリース時は、コメント化
if( $superiors ) {
            $attenSubject = "{$sendna} 様 【不在未承認】 定時間外作業申告よりお知らせ"; // 宛先： 
} else {
            $attenSubject = "{$sendna} 様 【未承認】 定時間外作業申告よりお知らせ"; // 宛先： 
}
            $message  = "{$sendna} 様\n\n";
if( $superiors ) {
            $message .= "{$res_list[$r][3]} 様 不在の為、代わりに\n\n";
            $message .= "定時間外作業申告（事前申請）承認処理をお願いします。\n\n";
            $message .= "http://masterst/per_appli/over_time_work_report/over_time_work_report_Main.php?calUid={$uid}&showMenu=Judge&select_radio=2\n\n";
} else {
            if( $rows_ken <= 0 ) {
                $message .= "定時間外作業申告（事前申請）承認待ちはありません。\n\n";
            } else {
                $message .= "定時間外作業申告（事前申請）承認待ちが {$rows_ken} 件あります。\n\n";
                $message .= "承認処理をお願いします。\n\n";
                // 承認ページのアドレス(Uid)を表示、クリックで承認ページへ
                $message .= "http://masterst/per_appli/over_time_work_report/over_time_work_report_Main.php?calUid={$res_list[$r][0]}&showMenu=Judge\n\n";
            }
}
            $message .= "以上。";
            if (mb_send_mail($to_addres, $attenSubject, $message, $add_head)) {
                // 出席者へのメール送信履歴を保存
                //$this->setAttendanceMailHistory($serial_no, $atten[$i]);
            }
            ///// Debug
            //if ($cancel) {
            //    if ($i == 0) mb_send_mail('tnksys@nitto-kohki.co.jp', $attenSubject, $message, $add_head);
            //}
        }
    }
    
    // TEST 結果報告メール
    public function TEST2()
    {
        // 工場長、部長、課長
        $where = "(ud.pid=110)";
        $where = "(ud.pid=47 OR ud.pid=70 OR ud.pid=95)";
        $where = "(ud.pid=46 OR ud.pid=50)";
        $where = "((ud.pid=110) OR (ud.pid=47 OR ud.pid=70 OR ud.pid=95) OR (ud.pid=46 OR ud.pid=50))";
        
        // 指定長の uid と act_id 取得
        $query = "
                    SELECT          uid, ct.act_id, pid, trim(name)
                    FROM            user_detailes   AS ud
                    LEFT OUTER JOIN cd_table        AS ct   USING(uid)
                    WHERE           retire_date IS NULL AND $where
                 ";
        $res_list = array();
        if( ($rows_list = getResult($query, $res_list)) <= 0) exit(); // 取得不可なら終了。
        
        for( $r=0; $r<$rows_list; $r++ ) {
            // 条件作成
            if( $res_list[$r][1] == 600 ) {  // 工場長
                if( $res_list[$r][0] == '012394' ) {  // 副工場長
                    $deploy = "(deploy='製造部 製造１課' OR deploy='製造部 製造２課')";
                } else {
                    $deploy = "(deploy IS NOT NULL)";
                }
            } else if( $res_list[$r][1] == 610 ) {   // 管理部
                $deploy = "(deploy='総務課' OR deploy='商品管理課')";
            } else if( $res_list[$r][1] == 605 || $res_list[$r][1] == 650 || $res_list[$r][1] == 651 || $res_list[$r][1] == 660 ) { // ＩＳＯ事務局 管理部 総務課 総務 財務
                $deploy = "(deploy='総務課')";
            } else if( $res_list[$r][1] == 670 ) {   // 管理部 商品管理課
                $deploy = "(deploy='商品管理課')";
            } else if( $res_list[$r][1] == 501 ) {   // 技術部
                $deploy = "(deploy='品質保証課' OR deploy='技術課')";
            } else if( $res_list[$r][1] == 174 || $res_list[$r][1] == 517 || $res_list[$r][1] == 537 || $res_list[$r][1] == 581 ) { // 技術部 品質管理課
                $deploy = "(deploy='品質保証課')";
            } else if( $res_list[$r][1] == 173 || $res_list[$r][1] == 515 || $res_list[$r][1] == 535 ) { // 技術部 技術課
                $deploy = "(deploy='技術課')";
            } else if( $res_list[$r][1] == 582 ) { // 製造部
                $deploy = "(deploy='製造部 製造１課' OR deploy='製造部 製造２課')";
            } else if( $res_list[$r][1] == 518 || $res_list[$r][1] == 519 || $res_list[$r][1] == 556 || $res_list[$r][1] == 520 ) { // 製造部 製造１課
                $deploy = "(deploy='製造部 製造１課')";
            } else if( $res_list[$r][1] == 547 || $res_list[$r][1] == 528 || $res_list[$r][1] == 527 ) { // 製造部 製造２課
                $deploy = "(deploy='製造部 製造２課')";
            } else if( $res_list[$r][1] == 500 ) { // 生産部
                $deploy = "(deploy='生産管理課 計画・購買係' OR deploy='生産管理課 資材係' OR deploy='カプラ組立課 標準係ＭＡ' OR deploy='カプラ組立課 標準係ＨＡ' OR deploy='カプラ組立課 特注係' OR deploy='リニア組立課')";
            } else if( $res_list[$r][1] == 545 || $res_list[$r][1] == 512 || $res_list[$r][1] == 532 || $res_list[$r][1] == 513|| $res_list[$r][1] == 533 || $res_list[$r][1] == 514 || $res_list[$r][1] == 534 ) { // 生産部 生産管理課
                $deploy = "(deploy='生産管理課 計画・購買係' OR deploy='生産管理課 資材係')";
            } else if( $res_list[$r][1] == 176 || $res_list[$r][1] == 522 || $res_list[$r][1] == 523 || $res_list[$r][1] == 525 ) { // 生産部 カプラ組立課
                $deploy = "(deploy='カプラ組立課 標準係ＭＡ' OR deploy='カプラ組立課 標準係ＨＡ' OR deploy='カプラ組立課 特注係')";
            } else if( $res_list[$r][1] == 551 || $res_list[$r][1] == 175 || $res_list[$r][1] == 572 ) { // 生産部 リニア組立課
                $deploy = "(deploy='リニア組立課')";
            } else {
                $deploy = "(deploy IS NULL)";   // エラー
            }
            // 件数取得条件
            $noinput1 = "yo_ad_rt!='-1' AND yo_ad_rt<=yo_ad_st AND ji_ad_rt=0 AND date!=date('today')";
            if( $res_list[$r][2] == 110 ) {
                $noinput = "yo_ad_ka IS NULL AND yo_ad_bu IS NULL";
                $noadmit = "ji_ad_ko='m' AND (ji_ad_ka IS NULL OR ji_ad_ka!='m') AND (ji_ad_bu IS NULL OR ji_ad_bu!='m')";
            } else if( $res_list[$r][2] == 47 || $res_list[$r][2] == 70 || $res_list[$r][2] == 95 ) {
                $noinput = "yo_ad_ka IS NULL";
                $noadmit = "ji_ad_bu='m' AND (ji_ad_ka IS NULL OR ji_ad_ka!='m')";
            } else if( $res_list[$r][2] == 46 || $res_list[$r][2] == 50 ) {
                $noinput = "yo_ad_ka!=''";
                $noadmit = "ji_ad_ka='m'";
            } else {
                $noinput = $noadmit = $deploy;
            }
            $where_noinput = "WHERE {$noinput1} AND {$noinput} AND {$deploy}";
            $where_noadmit = "WHERE {$noadmit} AND {$deploy}";
            
            // 結果報告未入力取得
            $query = "SELECT DISTINCT date, deploy FROM over_time_report $where_noinput";
            $res_noinput  = array();
            $rows_noinput = getResult($query, $res_noinput);
/**            
            // 結果報告未承認取得
            $query = "SELECT DISTINCT date, deploy FROM over_time_report $where_noadmit";
            $res_noadmit  = array();
            $rows_noadmit = getResult($query, $res_noadmit);
/**/            
            // メースアドレス取得
            $query = "SELECT trim(name), trim(mailaddr) FROM user_detailes LEFT OUTER JOIN user_master USING(uid)";
            $where = "WHERE uid='{$res_list[$r][0]}'";   // uid
            $where = "WHERE uid='300667'";   // TEST 強制的に
            $query .= $where;   // SQL query 文の完成
            $res_mail = array();
            if( getResult($query, $res_mail) <= 0 ) continue; // メールアドレス取得不可なら次へ
            
            // メール作成、送信
            $sendna = $res_mail[0][0];  // 名前
            $sendna = $res_list[$r][3]; // TEST 強制的に 名前
            $mailad = $res_mail[0][1];  // メールアドレス
            $_SESSION['u_mailad']  = $mailad;
            $to_addres = $mailad;
            $add_head = "";
//            $attenSubject = "{$sendna} 様 【残業結果報告状況】 定時間外作業申告よりお知らせ"; // 宛先： 
            $attenSubject = "{$sendna} 様 【未入力】 定時間外作業申告よりお知らせ"; // 宛先： 
            $message = "{$sendna} 様\n\n";
            $message .= "定時間外作業申告（残業結果報告）";
            
            if( $rows_noinput <= 0 ) continue; // 未入力無しなら次へ
            
            if( $rows_noinput <= 0 ) {
//                $message .= "定時間外作業申告（残業結果報告）未入力はありません。\n\n";
                $message .= "　未 入 力　ありません。\n\n";
            } else {
//                $message .= "定時間外作業申告（残業結果報告）未入力が {$rows_noinput} 件あります。\n\n";
                $message .= "未入力が {$rows_noinput} 件あります。\n";
                $message .= "------------------------------------------------------------------\n";
                for( $n=0; $n<$rows_noinput; $n++ ) {
                    $week   = array(' (日)',' (月)',' (火)',' (水)',' (木)',' (金)',' (土)');
                    $date   = $res_noinput[$n][0];
                    $day_no = date('w', strtotime($date));
                    $date   = $res_noinput[$n][0] . $week[$day_no];
                    $message .= "　作業日：{$date}\t部署名：{$res_noinput[$n][1]}\n";
                }
//                $message .= "\n定時間外作業申告（残業結果報告）へ入力するよう連絡して下さい。\n\n";
                $message .= "------------------------------------------------------------------\n";
                $message .= "入力するよう連絡して下さい。\n\n";
            }
/**
            if( $rows_noadmit <= 0 ) {
//                $message .= "定時間外作業申告（残業結果報告）承認待ちはありません。\n\n";
                $message .= "　承認待ち　ありません。\n\n";
            } else {
//                $message .= "定時間外作業申告（残業結果報告）承認待ちが {$rows_noadmit} 件あります。\n\n";
                $message .= "　承認待ち　{$rows_noadmit} 件あります。承認処理をお願いします。\n\n";
//                $message .= "定時間外作業申告（残業結果報告）承認処理をお願いします。\n\n";
                // 承認ページのアドレス(Uid)を表示、クリックで承認ページへ
//                $message .= "入力済みの申告がないかは以下の URL より確認して下さい。\n\n";
//                $message .= "↓↓↓ 定時間外作業申告（残業結果報告）未承認ページ ↓↓↓\n\n";
                $message .= "http://masterst/per_appli/over_time_work_report/over_time_work_report_Main.php?calUid={$res_list[$r][0]}&showMenu=Judge&select_radio=3\n\n";
            }
/**/
            $message .= "以上。";
/**/
            if (mb_send_mail($to_addres, $attenSubject, $message, $add_head)) {
                // 出席者へのメール送信履歴を保存
                //$this->setAttendanceMailHistory($serial_no, $atten[$i]);
            }
            ///// Debug
            //if ($cancel) {
            //    if ($i == 0) mb_send_mail('tnksys@nitto-kohki.co.jp', $attenSubject, $message, $add_head);
            //}
/**/
        }
    }
    /***************************************************************************
    *                              Protected methods                           *
    ***************************************************************************/

    /***************************************************************************
    *                               Private methods                            *
    ***************************************************************************/
} // Class over_time_work_report_Model End

?>
