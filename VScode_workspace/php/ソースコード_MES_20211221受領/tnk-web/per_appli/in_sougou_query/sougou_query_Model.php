<?php
////////////////////////////////////////////////////////////////////////////////
// 総合届（照会）                                                             //
//                                                              MVC Model 部  //
// Copyright (C) 2020-2020 Ryota.waki ryota_waki@nitto-kohki.co.jp            //
// Changed history                                                            //
// 2020/11/18 Created sougou_query_Model.php                                  //
// 2021/02/12 Release.                                                        //
////////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
// ini_set('zend.ze1_compatibility_mode', '1');    // zend 1.X コンパチ php4の互換モード

require_once ('../../ComTableMntClass.php');   // TNK 全共通 テーブルメンテ&ページ制御Class


/******************************************************************************
*          総合届（照会）用 MVCのModel部 base class 基底クラスの定義          *
******************************************************************************/
class Sougou_Query_Model extends ComTableMnt
{
    ///// Private properties
    private $indx;
    private $rows;
    private $res;
    private $uid;
    private $act_id;
    
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
            $request->add('uid', $uid);
            $this->setActID();    // Propertiesへの登録
        }

        switch ($request->get('showMenu')) {
        case 'List':
        case 'Results'  :

        default      :
            $sql_sum = "
                SELECT count(*) FROM sougou_deteils
            ";
            break;
        }

        ///// Constructer を定義すると 基底クラスの Constructerが実行されない
        ///// 基底ClassのConstructerはプログラマーの責任で呼出す
        parent::__construct($sql_sum, $request, 'sougou_query.log');
    }

    // 照会画面へ表示する総合届DB列数
    public function getIndx()
    {
        return $this->indx;
    }

    // 照会画面へ表示する総合届DB行数
    public function getRows()
    {
        return $this->rows;
    }

    // 照会画面へ表示する総合届DB
    public function getRes()
    {
        return $this->res;
    }

    // ログインユーザーID
    public function getUid()
    {
        return $this->uid;
    }

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

    // ログインユーザーactID取得
    public function getActID()
    {
        return $this->act_id;
    }

    // 表示可能項目ですか？
    public function IsDisp($no)
    {
        if( $this->IsMaster() ) return true;

        $flag = false;
        switch ($no) {
            case  0:    // 指定なし （すべて）
                break;
            case  1:    // 栃木日東工器
                break;
            case  2:    // ＩＳＯ事務局
                if( $this->IsISO() ) $flag = true;
                break;
            case  3:    // 管理部
                break;
            case  4:    // 管理部 (管理部)
                break;
            case  5:    // 管理部 総務課
                break;
            case  6:    // 管理部 商品管理課
                if( $this->IsKanriSyou() ) $flag = true;
                break;
            case  7:    // 技術部
                if( $this->IsGijyutsu() ) $flag = true;
                break;
            case  8:    // 技術部 (技術部)
                if( $this->IsGijyutsu() ) $flag = true;
                break;
            case  9:    // 技術部 品質保証課
                if( $this->IsGiHin() ) $flag = true;
                break;
            case 10:    // 技術部 技術課
                if( $this->IsGiGi() ) $flag = true;
                break;
            case 11:    // 製造部
                if( $this->IsSeizou() ) $flag = true;
                break;
            case 12:    // 製造部 (製造部)
                if( $this->IsSeizou() ) $flag = true;
                break;
            case 13:    // 製造部 製造１課
                if( $this->IsSeizouOne() ) $flag = true;
                break;
            case 14:    // 製造部 製造２課
                if( $this->IsSeizouTow() ) $flag = true;
                break;
            case 15:    // 生産部
                if( $this->IsSeisan() ) $flag = true;
                break;
            case 16:    // 生産部 (生産部)
                if( $this->IsSeisan() ) $flag = true;
                break;
            case 17:    // 生産部 生産管理課
                if( $this->IsSeiKanri() ) $flag = true;
                break;
            case 18:    // 生産部 カプラ組立課
                if( $this->IsSeiCapura() ) $flag = true;
                break;
            case 19:    // 生産部 リニア組立課
                if( $this->IsSeiLinia() ) $flag = true;
                break;
        }
        return $flag;
    }

    // マスター？（社員番号入力可能者（工場長、管理部、総務課））
    public function IsMaster()
    {
        $flag = false;
        if( getCheckAuthority(63) ) $flag = true;
/**
        switch ($this->act_id) {
            case 600:   // 工場長
            case 610:   // 管理部
            case 650:   // 管理部 総務課
            case 651:   // 管理部 総務課 総務担当
            case 660:   // 管理部 総務課 財務担当
                $flag = true;
        }
/**/
        return $flag;
    }

    // ＩＳＯ事務局？
    public function IsISO()
    {
        $flag = false;
        switch ($this->act_id) {
            case 605:   // ＩＳＯ事務局
                $flag = true;
        }
        return $flag;
    }

    // 管理部 商品管理課？
    public function IsKanriSyou()
    {
        $flag = false;
        switch ($this->act_id) {
            case 670:   // 商品管理課
                $flag = true;
        }
        return $flag;
    }

    // 技術部？
    public function IsGijyutsu()
    {
        $flag = false;
        switch ($this->act_id) {
            case 501:   // 技術部
                $flag = true;
        }
        return $flag;
    }

    // 技術部 品質管理課？
    public function IsGiHin()
    {
        $flag = false;
        switch ($this->act_id) {
            case 501:   // 技術部
            case 174:   // 技術部 品質管理課
            case 517:   // 技術部 品質管理課 カプラ検査担当
                $flag = true;
        }
        return $flag;
    }

    // 技術部 技術課？
    public function IsGiGi()
    {
        $flag = false;
        switch ($this->act_id) {
            case 501:   // 技術部
            case 173:   // 技術部 技術課
                $flag = true;
        }
        return $flag;
    }

    // 製造部？
    public function IsSeizou()
    {
        $flag = false;
        switch ($this->act_id) {
            case 582:   // 製造部
                $flag = true;
        }
        return $flag;
    }

    // 製造部 製造１課？
    public function IsSeizouOne()
    {
        $flag = false;
        switch ($this->act_id) {
            case 582:   // 製造部
            case 518:   // 製造部 製造１課
                $flag = true;
        }
        return $flag;
    }

    // 製造部 製造２課？
    public function IsSeizouTow()
    {
        $flag = false;
        switch ($this->act_id) {
            case 582:   // 製造部
            case 547:   // 製造部 製造２課
                $flag = true;
        }
        return $flag;
    }

    // 生産部？
    public function IsSeisan()
    {
        $flag = false;
        switch ($this->act_id) {
            case 500:   // 生産部
                $flag = true;
        }
        return $flag;
    }

    // 生産部 生産管理課？
    public function IsSeiKanri()
    {
        $flag = false;
        switch ($this->act_id) {
            case 500:   // 生産部
            case 545:   // 生産部 生産管理課
                $flag = true;
        }
        return $flag;
    }

    // 生産部 カプラ組立課？
    public function IsSeiCapura()
    {
        $flag = false;
        switch ($this->act_id) {
            case 500:   // 生産部
            case 176:   // 生産部 カプラ組立課
                $flag = true;
        }
        return $flag;
    }

    // 生産部 リニア組立課？
    public function IsSeiLinia()
    {
        $flag = false;
        switch ($this->act_id) {
            case 500:   // 生産部
            case 551:   // 生産部 リニア組立課
                $flag = true;
        }
        return $flag;
    }

    // 休日ですか？
    public function IsHoliday($date)
    {
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

    // アマノ入力 済 に更新する処理
    public function AmanoRun(&$result, $request)
    {
        if( $request->get('amano_run') != 'true') return true;

        $last_indx = $request->get('indx');
        $last_rows = $request->get('rows');

        if( $last_rows == '' ) return; // 初回は何もしない。

        // 取消する総合届を復元し、承認状況に取消をセット
        $last_res = array();
        for( $r=0; $r<$last_rows; $r++ ) {
            if( $request->get('amano' . $r) != '' ) {
                $res = $request->get(sprintf("res-%s", $r));
                for( $i=0; $i<$last_indx; $i++ ) {
                    $last_res[$r][$i] = $res[$i];
                }
                // アマノ入力を 済 へ変更
                $update_qry = sprintf("UPDATE sougou_deteils SET amano_input = 't' WHERE date='%s' AND uid='%s'", $last_res[$r][0], $last_res[$r][1]);
//                $_SESSION['s_sysmsg'] .= $update_qry;
                if( query_affected($update_qry) <= 0 ) {
                    return false;
                }
            }
        }

        return true;
    }

    // 取消処理の実行
    public function CancelRun(&$result, $request)
    {
        if( $request->get('cancel_run') != 'true') return true;

        $last_indx = $request->get('indx');
        $last_rows = $request->get('rows');

        if( $last_rows == '' ) return; // 初回は何もしない。

        // 取消する総合届を復元し、承認状況に取消をセット
        $last_res = array();
        for( $r=0; $r<$last_rows; $r++ ) {
            if( $request->get($r) != '' ) {
                $res = $request->get(sprintf("res-%s", $r));
                for( $i=0; $i<$last_indx; $i++ ) {
                    $last_res[$r][$i] = $res[$i];
                }
                // 承認状況をCANCELへ変更
                $update_qry = sprintf("UPDATE sougou_deteils SET admit_status = '%s' WHERE date='%s' AND uid='%s'", 'CANCEL', $last_res[$r][0], $last_res[$r][1]);
//                $_SESSION['s_sysmsg'] .= $update_qry;
                if( query_affected($update_qry) <= 0 ) {
                    return false;
                }

                $query = "SELECT date FROM admit_stop_reason WHERE date='{$last_res[$r][0]}' AND uid='{$last_res[$r][1]}'";
                if( getResult2($query, $res_chk) <= 0 ) { // まだ、登録されていない
                    $insert_qry = "
                        INSERT INTO admit_stop_reason (date, uid, state, reason) VALUES
                            ('{$last_res[$r][0]}', '{$last_res[$r][1]}', 'CANCEL', '照会より取消');
                    ";
                    if( query_affected($insert_qry) <= 0 ) {
                        $_SESSION['s_sysmsg'] .= "取消理由の登録に失敗しました。";
                    }
                } else {
                    // 否認理由があるなら書き換えない。
                }
            }
        }

        return true;
    }

    // 部門別のactID取得
    public function getBumonActID($name)
    {
        $where = "";

        if( $name == '栃木日東工器' ) {
            $where = "(at.act_id=600) ";
        } else if( $name == 'ＩＳＯ事務局' ) {
            $where = "(at.act_id=605) ";
        } else if( $name == '管理部' ) {
            $where = "(at.act_id=610 OR at.act_id=650 OR at.act_id=651 OR at.act_id=660 OR at.act_id=670) ";
        } else if( $name == '管理部 (管理部)' ) {
            $where = "(at.act_id=610) ";
        } else if( $name == '管理部 総務課' ) {
            $where = "(at.act_id=650 OR at.act_id=651 OR at.act_id=660) ";
        } else if( $name == '管理部 商品管理課' ) {
            $where = "(at.act_id=670) ";
        } else if( $name == '技術部' ) {
            $where = "(at.act_id=501 OR at.act_id=174 OR at.act_id=517 OR at.act_id=537 OR at.act_id=173 OR at.act_id=515 OR at.act_id=535 OR at.act_id=581) ";
        } else if( $name == '技術部 (技術部)' ) {
            $where = "(at.act_id=501) ";
        } else if( $name == '技術部 品質保証課' ) {
            $where = "(at.act_id=174 OR at.act_id=517 OR at.act_id=537 OR at.act_id=581) ";
        } else if( $name == '技術部 技術課' ) {
            $where = "(at.act_id=173 OR at.act_id=515 OR at.act_id=535) ";
        } else if( $name == '製造部' ) {
            $where = "(at.act_id=582 OR at.act_id=518 OR at.act_id=519 OR at.act_id=556 OR at.act_id=520 OR at.act_id=547 OR at.act_id=527 OR at.act_id=528) ";
        } else if( $name == '製造部 (製造部)' ) {
            $where = "(at.act_id=582) ";
        } else if( $name == '製造部 製造１課' ) {
            $where = "(at.act_id=518 OR at.act_id=519 OR at.act_id=556 OR at.act_id=520) ";
        } else if( $name == '製造部 製造２課' ) {
            $where = "(at.act_id=547 OR at.act_id=527 OR at.act_id=528) ";
        } else if( $name == '生産部' ) {
            $where = "(at.act_id=500 OR at.act_id=545 OR at.act_id=512 OR at.act_id=532 OR at.act_id=513 OR at.act_id=533 OR at.act_id=514 OR at.act_id=534 OR at.act_id=176 OR at.act_id=522 OR at.act_id=523 OR at.act_id=525 OR at.act_id=551 OR at.act_id=175 OR at.act_id=572) ";
        } else if( $name == '生産部 (生産部)' ) {
            $where = "(at.act_id=500) ";
        } else if( $name == '生産部 生産管理課' ) {
            $where = "(at.act_id=545 OR at.act_id=512 OR at.act_id=532 OR at.act_id=513 OR at.act_id=533 OR at.act_id=514 OR at.act_id=534) ";
        } else if( $name == '生産部 カプラ組立課' ) {
            $where = "(at.act_id=176 OR at.act_id=522 OR at.act_id=523 OR at.act_id=525) ";
        } else if( $name == '生産部 リニア組立課' ) {
            $where = "(at.act_id=551 OR at.act_id=175 OR at.act_id=572) ";
        }

        return $where;
    }

    // 課長代理以上？
    public function IsBukatyou()
    {
        $query = "
            SELECT          ct.act_id
            FROM            user_detailes   AS ud
            LEFT OUTER JOIN cd_table        AS ct   USING(uid)
            WHERE           ud.uid = '$this->uid' AND (ud.pid=46 OR ud.pid=47 OR ud.pid=50 OR ud.pid=70 )
        ";
        $res = array();

        if ( $this->getResult2($query, $res) <= 0 ) {
            return false;
        }

        return true;
    }

    // 照会画面へ表示する総合届DB取得
    public function getViewDataList(&$result, $request)
    {
        $si_s_date = $request->get('si_s_date');
        $si_e_date = $request->get('si_e_date');
        if( strlen($request->get('syainbangou')) == 0 ) {
            $syainbangou = '';
        } else {
            $syainbangou = sprintf('%06s', $request->get('syainbangou'));
        }
        $simei = $request->get('simei');
        $str_date = $request->get('str_date');
        $end_date = $request->get('end_date');
        $ddlist = $request->get('ddlist');
        if( $ddlist == "指定なし" ) $ddlist = '';
        $ddlist_bumon = $request->get('ddlist_bumon');
        if( $ddlist_bumon == "指定なし （すべて）" ) $ddlist_bumon = '';
        $r5 = $request->get('r5');
        if( $r5 == "指定なし" ) $r5 = '';
        $r6 = $request->get('r6');
        if( $r6 == "指定なし" ) $r6 = '';
        $r7 = $request->get('r7');
        if( $r7 == "指定なし" ) $r7 = '';
        $r8 = $request->get('r8');
        if( $r8 == "指定なし" ) $r8 = '';
        $r9 = $request->get('r9');
        if( $r9 == "指定なし" ) $r9 = '';

        $where = '';
        if( $si_s_date != '' ) {
            if( $si_e_date != '' ) {
                $where .= sprintf( "date >= '%s 00:00:00' AND date <= '%s 23:59:59' ", $si_s_date, $si_e_date );
            } else {
                $where .= sprintf( "date >= '%s 00:00:00' AND date <= '%s 23:59:59' ", $si_s_date, $si_s_date );
            }
        }
        if( $syainbangou != '' ) {
            if( $where != '' ) $where .= 'AND ';
            $where .= sprintf( "uid = '%s' ", $syainbangou );
        }

        if( $simei != '' ) {
            $query = sprintf( "SELECT uid FROM user_detailes WHERE retire_date IS NULL AND( name like '%%%s%%' OR kana like '%%%s%%' OR spell like '%%%s%%')", $simei, $simei, $simei );
            $res = array();
            if ( ($rows=getResultWithField2($query, $field, $res)) > 0 ) {
                if( $where != '' ) $where .= 'AND ';
                $where .= sprintf( "(uid = '%s' ", $res[0][0] );
                for( $i=1; $i < $rows; $i++ ) {
                    $where .= sprintf( "OR uid = '%s' ", $res[$i][0] );
                }
                $where .= ') ';
            } else {
                $_SESSION['s_sysmsg'] .= 'フルネームの場合は、姓名の間にスペースを入れてください。';
                return false;
            }
        }

        if( $str_date != '' ) {
            if( $where != '' ) $where .= 'AND ';
            if( $end_date != '' ) {
//                $where .= sprintf( "((start_date >= '%s' AND end_date <= '%s') OR (start_date >= '%s' AND start_date <= '%s') OR (end_date >= '%s' AND end_date <= '%s'))", $str_date, $end_date, $str_date, $end_date, $str_date, $end_date );
                $where .= sprintf( "((start_date >= '%s' AND end_date <= '%s') OR (start_date <= '%s' AND end_date >= '%s') OR (start_date <= '%s' AND end_date >= '%s') OR (start_date >= '%s' AND start_date <= '%s') OR (end_date >= '%s' AND end_date <= '%s'))", $str_date, $end_date, $str_date, $str_date, $end_date, $end_date, $str_date, $end_date, $str_date, $end_date );
            } else {
//                $where .= sprintf( "start_date = '%s' ", $str_date );
                $where .= sprintf( "(start_date = '%s' OR (start_date <= '%s' AND end_date >= '%s')) ", $str_date, $str_date, $str_date );
            }
        }

        if( $ddlist != '' ) {
            if( $where != '' ) $where .= 'AND ';
            $where .= sprintf( "content = '%s' ", $ddlist );
        }

        if( $r6 != '' ) {
            if( $where != '' ) $where .= 'AND ';
            if( $r6 == 't' ) {
//            if( $r6 ) {
                $where .= sprintf( "ticket = '%s' ", $r6 );
            } else {
//                $where .= "(ticket != 't' OR ticket IS NULL) ";
                $where .= "ticket = 'f' ";
//                $where .= "ticket IS NULL ";
            }
        }
        if( $r7 != '' ) {
            if( $where != '' ) $where .= 'AND ';
            if( $r7 == '受電者' ) {
                $where .= sprintf( "received_phone = '%s' ", $r7 );
            } else {
                $where .= "received_phone IS NULL ";
            }
        }
        if( $r8 != '' ) {
            if( $where != '' ) $where .= 'AND ';
            if( $r8 == '至急' ){
                $where .= sprintf( "hurry = '%s' ", $r8 );
            } else {
                $where .= "hurry IS NULL ";
            }
        }
        if( $r9 != '' ) {
            if( $where != '' ) $where .= 'AND ';
            if( $r9 == 'END' || $r9 == 'DENY' || $r9 == 'CANCEL' ) {
                $where .= sprintf( "admit_status = '%s' ", $r9 );
            } else {
                $where .= "(admit_status != 'END' AND admit_status != 'DENY' AND admit_status != 'CANCEL') ";
            }
        }

        if( $r5 != '' ) {
            if( $where != '' ) $where .= 'AND ';
            if( $r5 == 'パート以外' ) {
                $where .= "ud.pid!=5 AND ud.pid!=6 ";
            } else {
                $where .= "(ud.pid=5 OR ud.pid=6) ";
            }
        }

        if( $ddlist_bumon != '' ) {
            if( $where != '' ) $where .= 'AND ';
            $where .= $this->getBumonActID($ddlist_bumon);
        }

        if( $where != '' ) {
            $where = "WHERE " . $where;
        }

        $query = sprintf( "SELECT sd.* FROM sougou_deteils AS sd LEFT OUTER JOIN cd_table AS ct USING(uid) LEFT OUTER JOIN act_table AS at USING(act_id) LEFT OUTER JOIN user_detailes AS ud USING(uid) %s ORDER BY ct.orga_id ASC, ud.uid ASC, sd.start_date ASC", $where );
/*
        if( $ddlist_bumon != '' && $r5 != '' ) {
            $query = sprintf( "SELECT sd.* FROM sougou_deteils AS sd LEFT OUTER JOIN cd_table AS ct USING(uid) LEFT OUTER JOIN act_table AS at USING(act_id) LEFT OUTER JOIN user_detailes AS ud USING(uid) %s ORDER BY sd.uid ASC, sd.start_date ASC", $where );
        } else if( $ddlist_bumon != '' ) {
            $query = sprintf( "SELECT sd.* FROM sougou_deteils AS sd LEFT OUTER JOIN cd_table AS ct USING(uid) LEFT OUTER JOIN act_table AS at USING(act_id) %s ORDER BY sd.uid ASC, sd.start_date ASC", $where );
        } else if( $r5 != '' ) {
            $query = sprintf( "SELECT sd.* FROM sougou_deteils AS sd LEFT OUTER JOIN user_detailes AS ud USING(uid) %s ORDER BY sd.uid ASC, sd.start_date ASC", $where );
        } else {
            $query = sprintf( "SELECT * FROM sougou_deteils %s ORDER BY uid ASC, start_date ASC", $where );
        }
*/
//        $_SESSION['s_sysmsg'] .= '検索 : ' . $query;

        $res = array();
        if ( ($rows=getResultWithField2($query, $field, $res)) <= 0 ) {
            ; // $_SESSION['s_sysmsg'] .= '登録がありません！';
        } else {
            ; // $_SESSION['s_sysmsg'] .= $rows . '件あります。';
        }
        $result->add_array($res);

        $this->indx = count($field);
        $this->rows = $rows;
        $this->res = $res;

        return $rows;
    }

/**/
    // 照会画面へ表示する総合届DB取得
    public function getHuzaisyaDataList(&$result, $request)
    {
        $si_s_date = $request->get('si_s_date');
        $si_e_date = $request->get('si_e_date');
        if( strlen($request->get('syainbangou')) == 0 ) {
            $syainbangou = '';
        } else {
            $syainbangou = sprintf('%06s', $request->get('syainbangou'));
        }
        $str_date = $request->get('str_date');
        $end_date = $request->get('end_date');
        $r5 = $request->get('r5');
        if( $r5 == "指定なし" ) $r5 = '';

        $where = '';
        if( $si_s_date != '' ) {
            if( $si_e_date != '' ) {
                $where .= sprintf( "sd.date >= '%s 00:00:00' AND sd.date <= '%s 23:59:59' ", $si_s_date, $si_e_date );
            } else {
                $where .= sprintf( "sd.date >= '%s 00:00:00' AND sd.date <= '%s 23:59:59' ", $si_s_date, $si_s_date );
            }
        }
        if( $syainbangou != '' ) {
            if( $where != '' ) $where .= 'AND ';
            $where .= sprintf( "sd.uid = '%s' ", $syainbangou );
        }

        if( $str_date != '' ) {
            if( $where != '' ) $where .= 'AND ';
            if( $end_date != '' ) {
                $where .= sprintf( "((sd.start_date >= '%s' AND sd.end_date <= '%s') OR (sd.start_date <= '%s' AND sd.end_date >= '%s') OR (sd.start_date <= '%s' AND sd.end_date >= '%s') OR (sd.start_date >= '%s' AND sd.start_date <= '%s') OR (sd.end_date >= '%s' AND sd.end_date <= '%s'))", $str_date, $end_date, $str_date, $str_date, $end_date, $end_date, $str_date, $end_date, $str_date, $end_date );
            } else {
                $where .= sprintf( "(sd.start_date = '%s' OR (sd.start_date <= '%s' AND sd.end_date >= '%s')) ", $str_date, $str_date, $str_date );
            }
        }

        if( $where != '' ) $where .= 'AND ';
        $where .= "sd.admit_status != 'CANCEL' AND sd.admit_status != 'DENY' ";

        if( $r5 != '' ) {
            if( $where != '' ) $where .= 'AND ';
            if( $r5 == 'パート以外' ) {
                $where .= "ud.pid!=5 AND ud.pid!=6 ";
            } else {
                $where .= "(ud.pid=5 OR ud.pid=6) ";
            }
        }

        $ddlist_bumon = $request->get('ddlist_bumon');
        if( $ddlist_bumon == "指定なし （すべて）" ) $ddlist_bumon = '';
        if( $ddlist_bumon != '' ) {
            if( $where != '' ) $where .= 'AND ';
            $where .= $this->getBumonActID($ddlist_bumon);
        }

        if( $where != '' ) {
            $where = "WHERE " . $where . "AND ";
        }

        $query = sprintf( "
                SELECT ct.orga_id, sm.section_name, ud.uid, ud.name, sd.start_date, sd.end_date, sd.content, sd.start_time, sd.end_time
                FROM                sougou_deteils  AS sd
                LEFT OUTER JOIN     user_detailes   AS ud    USING(uid)
                LEFT OUTER JOIN     cd_table        AS ct    USING(uid)
                LEFT OUTER JOIN     section_master  AS sm    USING(sid)
                LEFT OUTER JOIN     act_table       AS at    USING(act_id)
                %s    ct.orga_id IS NOT NULL
                  AND sd.content!='IDカード通し忘れ（出勤） '
                  AND sd.content!='IDカード通し忘れ（退勤） '
                  AND sd.content!='時限承認忘れ（残業申告漏れ）'
                  AND sd.content!='IDカード通し忘れ（退勤）＋ 時限承認忘れ（残業申告漏れ）'
                ORDER BY ct.orga_id ASC, ud.uid ASC, sd.start_date ASC
        ", $where );
//$_SESSION['s_sysmsg'] .= '検索 : ' . $query;

        $res = array();
        if ( ($rows=getResultWithField2($query, $field, $res)) <= 0 ) {
            ; //$_SESSION['s_sysmsg'] .= '登録がありません！';
        } else {
            ; //$_SESSION['s_sysmsg'] .= $rows . '件あります。';
        }
        $result->add_array($res);

        $this->indx = count($field);
        $this->rows = $rows;
        $this->res = $res;

        return $rows;
    }
/**/

    // 所属名取得
    public function getSyozoku($uid)
    {
        $query = "
            SELECT      sm.section_name
            FROM        user_detailes  AS ud
            LEFT JOIN   section_master AS sm
            ON          ud.sid = sm.sid
            WHERE       uid = '$uid'
            ORDER BY    uid ASC
        ";
        $res = array();
        if ( ($rows=$this->getResult2($query, $res)) <= 0 ) {
//            $_SESSION['s_sysmsg'] = '登録がありません！';
            return "------";
        }
        return $res[0][0];
    }

    // 社員の名前を取得
    public function getSyainName($str)
    {
        $query = "
            SELECT      name
            FROM        user_detailes
            WHERE       uid = '$str'
        ";
        $res = array();
        if ( $this->getResult2($query, $res) <= 0 ) {
            return '';
        }
        return $res[0][0];
    }

    // 申請総合届に対する否認理由の取得
    public function getAdmitStopReason($date, $uid, $status)
    {
        $query = "
            SELECT      reason
            FROM        admit_stop_reason
            WHERE       date = '$date' AND uid = '$uid'
        ";
        $res = array();
        if ( $this->getResult2($query, $res) <= 0 ) {
            return '---';
        }
        if($status == '取消') return $res[0][0];

        $hito = $this->getAdmitStop($date, $uid);

        return '***** ' . $hito . ' 様 コメント *****<br>' . $res[0][0];
    }

    // 申請総合届に対する否認者の取得
    public function getAdmitStop($date, $uid)
    {
        $query = "
            SELECT      *
            FROM        approval_path
            WHERE       date = '$date' AND uid = '$uid'
        ";
        $res = array();
        if ( $this->getResult2($query, $res) <= 0 ) {
            return '';
        }
/**
        if( $res[0][13] != "------" ) if( $res[0][14] != '' ) return $res[0][13];   // 工場長
        if( $res[0][11] != "------" ) if( $res[0][12] != '' ) return $res[0][11];   // 管理部長
        if( $res[0][ 9] != "------" ) if( $res[0][10] != '' ) return $res[0][ 9];    // 総務課長
        if( $res[0][ 7] != "------" ) if( $res[0][ 8] != '' ) return $res[0][ 7];    // 所属部長
        if( $res[0][ 5] != "------" ) if( $res[0][ 6] != '' ) return $res[0][ 5];    // 所属課長
        if( $res[0][ 3] != "------" ) if( $res[0][ 4] != '' ) return $res[0][ 3];    // 所属係長
/**/
        if( $res[0][13] != "------" ) if( $res[0][14] != '' ) return $this->getSyainName($res[0][13]);   // 工場長
        if( $res[0][11] != "------" ) if( $res[0][12] != '' ) return $this->getSyainName($res[0][11]);   // 管理部長
        if( $res[0][ 9] != "------" ) if( $res[0][10] != '' ) return $this->getSyainName($res[0][ 9]);    // 総務課長
        if( $res[0][ 7] != "------" ) if( $res[0][ 8] != '' ) return $this->getSyainName($res[0][ 7]);    // 所属部長
        if( $res[0][ 5] != "------" ) if( $res[0][ 6] != '' ) return $this->getSyainName($res[0][ 5]);    // 所属課長
        if( $res[0][ 3] != "------" ) if( $res[0][ 4] != '' ) return $this->getSyainName($res[0][ 3]);    // 所属係長
/**/
        return '';

    }

    /***************************************************************************
    *                              Protected methods                           *
    ***************************************************************************/

    /***************************************************************************
    *                               Private methods                            *
    ***************************************************************************/

} // Class Sougou_Query_Model End

?>
