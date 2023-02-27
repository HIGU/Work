<?php 
//////////////////////////////////////////////////////////////////////////////
// 組立設備稼働管理システムの機械運転日報 ロジックファイル                  //
// Copyright (C) 2021-2021 norihisa_ooya@nitto-kohki.co.jp                  //
// Original by yamagishi@matehan.co.jp                                      //
// Changed history                                                          //
// 2021/03/26 Created  ReportEntry.php                                      //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);

require_once ('../com/define.php');
require_once ('../com/function.php');
require_once ('../com/mu_date.php');

ob_start('ob_gzhandler');

// メッセージのクリア
$Message = '';
// 管理者モード
$AdminUser = AdminUser( FNC_REPORT );
$AcceptUser = AdminUser( FNC_REPORT_ACCEPT );
// 処理コードの取得
$ProcCode = @$_REQUEST['ProcCode'];
if (!isset($_REQUEST['ProcCode'])) $ProcCode = 'EDIT';

$con = getConnection();

// パラメータのセット
setParameter();

// --------------------------------------------------
// 処理の振り分け
// --------------------------------------------------

if ($ProcCode == 'EDIT') {
    // --------------------------------------------------
    // 編集処理
    // --------------------------------------------------
    
    // 新規作成モード
    $EDIT_MODE = 'INSERT';
    // 修正モード
    if (@$_REQUEST['EDIT_MODE'] == 'UPDATE') {
        // ＤＢから呼び出し
        ReadData();
        // ここで $Report['Type'] がバー材=B, 切断材=C が決まる
    } else {
        $Report['Type'] = '';   // ダミー
    }
    // 入力内容のチェック
    EntryDataCheck();
    // ログ入力バッファー
    $LogNum += 3;
    // Entry画面表示
    require_once('ReportEdit.php');
    
} else if ($ProcCode == 'WRITE') {
    // --------------------------------------------------
    // 保存処理
    // --------------------------------------------------

    // 編集モード
    $EDIT_MODE = @$_REQUEST['EDIT_MODE'];
    // 入力内容のチェック
    if (!EntryDataCheck()) {
        // エラーがあるので入力画面に戻る
        require_once('ReportEdit.php');
    } else {
        // データの保存
        SaveData();
        // 保存したデータの読み込み
        ReadData();
        // 登録完了メッセージ
        $Message = '登録しました。';
        // 表示画面
        require_once('ReportView.php');
    }
} else if ($ProcCode == 'DELETE') {
    // --------------------------------------------------
    // 削除処理
    // --------------------------------------------------
    
    // ＤＢ削除
    DeleteData();
    // 呼び出し元にリダイレクト
    header("Location: ".@$_REQUEST['RetUrl']);
    
} else if ($ProcCode == 'VIEW') {
    // --------------------------------------------------
    // 表示処理
    // --------------------------------------------------
    
    // 保存されているデータの読み込み
    ReadData();
    // 表示画面
    require_once('ReportView.php');
    
} else if ($ProcCode == 'DECISION') {
    // --------------------------------------------------
    // 日報確定処理
    // --------------------------------------------------
    
    // 保存したデータの読み込み
    ReadData();
    // 確定処理
    ExecuteDecision();
    // 確定完了メッセージ
    $Message = '日報確定しました。';
    // 表示画面
    require_once('ReportView.php');
    
} else {
    // --------------------------------------------------
    // 例外処理
    // --------------------------------------------------
    
    // どこにも引っかからず、ここまで来たらシステムエラー
    $SYSTEM_MESSAGE = "処理コードが正しくありません：[$ProcCode]";
    require_once('../com/' . ERROR_PAGE);
    exit();
}
// パラメータのセット
function setParameter()
{
    global $con,$Report,$LogNum,$CsvFlg;
    // 格納
    $Report = Array();
    $Report['SummaryType']      = trim (@$_REQUEST['SummaryType']);
    $Report['WorkDate']         = trim (@$_REQUEST['WorkDate']);
    $Report['WorkYear']         = trim (@$_REQUEST['WorkYear']);
    $Report['WorkMonth']        = trim (@$_REQUEST['WorkMonth']);
    $Report['WorkDay']          = trim (@$_REQUEST['WorkDay']);
    $Report['MacNo']            = trim (@$_REQUEST['MacNo']);
    $Report['PlanNo']           = trim (@$_REQUEST['PlanNo']);
    $Report['KouteiNo']         = trim (@$_REQUEST['KouteiNo']);
    //$Report['KouteiName']       = trim (@$_REQUEST['KouteiName']);
    $Report['Yesterday']        = trim (@$_REQUEST['Yesterday']);
    $Report['Today']            = trim (@$_REQUEST['Today']);
    $Report['Ng']               = trim (@$_REQUEST['Ng']);
    $Report['NgKbn']            = trim (@$_REQUEST['NgKbn']);
    $Report['Plan']             = trim (@$_REQUEST['Plan']);
    $Report['EndFlg']           = trim (@$_REQUEST['EndFlg']);
    $Report['NgKbn']            = trim (@$_REQUEST['NgKbn']);
    $Report['Memo']             = trim (@$_REQUEST['Memo']);
    $Report['Injection']        = trim (@$_REQUEST['Injection']);
    $Report['InjectionItem']    = trim (@$_REQUEST['InjectionItem']);
    $Report['Abandonment']      = trim (@$_REQUEST['Abandonment']);
    $Report['Type']             = trim (@$_REQUEST['Type']);
    
    $LogNum = @$_REQUEST['LogNum'];
    for ($i=0;$i<$LogNum;$i++) {
        $Report['MacState'][$i] = trim (@$_REQUEST['MacState'][$i]);
        $Report['FromDate'][$i] = trim (@$_REQUEST['FromDate'][$i]);
        $Report['FromHH'][$i]   = trim (@$_REQUEST['FromHH'][$i]);
        $Report['FromMM'][$i]   = trim (@$_REQUEST['FromMM'][$i]);
        $Report['ToDate'][$i]   = trim (@$_REQUEST['ToDate'][$i]);
        $Report['ToHH'][$i]     = trim (@$_REQUEST['ToHH'][$i]);
        $Report['ToMM'][$i]     = trim (@$_REQUEST['ToMM'][$i]);
        $Report['CutTime'][$i]  = trim (@$_REQUEST['CutTime'][$i]);
    }
    if ($Report['WorkDate'] != '') {
        // WorkDateが取得できればWorkDateを分解して格納する
        $Report['WorkYear']  = mu_Date::toString($Report['WorkDate'] ,'Y');
        $Report['WorkMonth'] = mu_Date::toString($Report['WorkDate'] ,'m');
        $Report['WorkDay']   = mu_Date::toString($Report['WorkDate'] ,'d');
    } else {
        // WorkDateが取得できなければ、運転日から生成
        $Date = $Report['WorkYear'] . '/' . $Report['WorkMonth'] . '/' . $Report['WorkDay'];
        if (mu_Date::chkDate ($Date)) {
            $Report['WorkDate'] = mu_Date::toString($Date,'Ymd');
        } else {
            $Report['WorkDate'] = '';
        }
    }
    
    // --------------------------------------------------
    // たりない情報はマスタから取得する
    // --------------------------------------------------
    
    $Report['MacName'] = '';
    $CsvFlg            = 0;
    if (is_numeric($Report['MacNo'])) {
        // 機械名称取得
        $rs = pg_query($con,"select mac_name,csv_flg from equip_machine_master2 where mac_no=" . pg_escape_string ($Report['MacNo']) );
        if ($row = pg_fetch_array ($rs)) {
            $Report['MacName'] = $row['mac_name'];
            $CsvFlg            = $row['csv_flg'];
        }
    }
    
    if (is_numeric($Report['MacNo']) && is_numeric($Report['PlanNo']) && is_numeric($Report['KouteiNo'])) {
        // 指示No.から 部品No. 部品名 部品材質 納期 指示数量を取得
        $sql = " select "
             . "    b.parts_no          as parts_no,        "
             . "    a.delivery          as delivery,        "
             . "    a.inst_qt           as inst_qt,         "
             . "    c.midsc             as midsc,           "
             . "    c.mzist             as mzist            "
             . " FROM equip_work_inst_header a "
             . " LEFT OUTER JOIN equip_work_instruction b USING(inst_no) "
             . " left outer join miitem c on c.mipn=b.parts_no "
             . " WHERE a.inst_no=" . pg_escape_string ($Report['PlanNo']) . " and b.koutei=" . pg_escape_string ($Report['KouteiNo']);

    
        $rs = pg_query($con,$sql);
        if ($row = pg_fetch_array ($rs)) {
            $Report['ItemCode']      = $row['parts_no'];
            $Report['ItemName']      = $row['midsc'];
            $Report['Mzist']         = $row['mzist'];
            //$Report['KouteiName']  = $row['KouteiName']);
            $Report['Delivery']      = $row['delivery'];
            $Report['DeliveryYYYY']  = mu_Date::toString($Report['Delivery'] ,'Y');
            $Report['DeliveryMM']    = mu_Date::toString($Report['Delivery'] ,'m');
            $Report['DeliveryDD']    = mu_Date::toString($Report['Delivery'] ,'d');
            $Report['PlanNum']       = $row['inst_qt'];
        }
    }
    
    // 行程記号の取得（仮）
    $Report['KouteiName'] = '';
    if (is_numeric($Report['PlanNo']) && is_numeric($Report['KouteiNo'])) {
        $sql = 'select pro_mark from equip_work_instruction where inst_no=' . pg_escape_string ($Report['PlanNo']) . ' and koutei=' . pg_escape_string ($Report['KouteiNo']);
        $rs = pg_query($con,$sql);
        if ($row = pg_fetch_array ($rs)) {
            $Report['KouteiName'] = $row['pro_mark'];
        }
    }
    
    // チョコ停,故障回数カウント
    $Report['Stop'] = $Report['Failure'] = 0;
    for ($i=0;$i<$LogNum;$i++) {
        if ( CheckCount( "Stop"     , $CsvFlg , $Report['MacState'][$i] )) $Report['Stop']++;
        if ( CheckCount( "Failure"  , $CsvFlg , $Report['MacState'][$i] )) $Report['Failure']++;
    }
    // 単位重量の取得
    $Report['AbandonmentWeight'] = 0;
    if (is_numeric($Report['Injection'])) {
        $sql = " select weight from equip_materials where mtcode='" . pg_escape_string ($Report['InjectionItem']) . "'";
        $rs = pg_query($con,$sql);
        if ($row = pg_fetch_array ($rs)) {
            $Report['AbandonmentWeight'] = $row['weight'] * $Report['Abandonment'];
        }
    }
}
// 入力内容のチェック
function EntryDataCheck()
{
    global $con,$Message,$Report,$LogNum;
    
    // 入力情報レベル(1:キー入力 2:明細入力)
    
    if (@$_REQUEST['ErrorCheckLevel'] == 0) {
        // 入力情報レベル(1:キー入力 2:明細入力)
        $Report['ENTRY_LEVEL'] = '1';
    }
    if (@$_REQUEST['ErrorCheckLevel'] == 1) {
        // 入力情報レベル(1:キー入力 2:明細入力)
        $Report['ENTRY_LEVEL'] = '2';
    
        // 運転日
        // WorkDateが取得できなければ、運転日から生成
        $Date = $Report['WorkYear'] . '/' . $Report['WorkMonth'] . '/' . $Report['WorkDay'];
        if (mu_Date::chkDate ($Date)) {
            $Report['WorkDate'] = mu_Date::toString($Date,'Ymd');
            if ($Report['WorkDate'] == '') {
                // 入力情報レベル１（キー入力レベル）
                $Report['ENTRY_LEVEL'] = '1';
                $Message .= '運転日が未入力です。\n\n';
            }
        } else {
            $Report['WorkDate'] = '';
            // 入力情報レベル１（キー入力レベル）
            $Report['ENTRY_LEVEL'] = '1';
            $Message .= '運転日が正しくありません。\n\n';
        }
        // 機械No.
        if ($Report['MacNo'] == '') {
            // 入力情報レベル１（キー入力レベル）
            $Report['ENTRY_LEVEL'] = '1';
            $Message .= '機械No.が未入力です。\n\n';
        } else {
            // 機械名称が取得できていなければマスタに存在しない
            if ($Report['MacName'] == '') {
                // 入力情報レベル１（キー入力レベル）
                $Report['ENTRY_LEVEL'] = '1';
                $Message .= '機械No.'.$Report['MacNo'].'はマスタに登録されていません。\n\n';
            }
        }
        // 指示No.
        if ($Report['PlanNo'] == '') {
            // 入力情報レベル１（キー入力レベル）
            $Report['ENTRY_LEVEL'] = '1';
            $Message .= '指示No.が未入力です。\n\n';
        } else {
            if (!is_numeric($Report['PlanNo'])) {
                $Report['ENTRY_LEVEL'] = '1';
                $Message .= '指示No.は数値で入力して下さい。\n\n';
            } else {
                // 部品No.が取得できていなければマスタに存在しない
                if (!isset ($Report['ItemCode']) && $Report['PlanNo'] != CUSTOM_MADE_SIJI_NO) {
                    // 入力情報レベル１（キー入力レベル）
                    $Report['ENTRY_LEVEL'] = '1';
                    $Message .= '指示No.'.$Report['PlanNo'].'はマスタに登録されていません。\n\n';
                }
            }
        }
        // 行程番号
        if ($Report['KouteiNo'] == '') {
            // 入力情報レベル１（キー入力レベル）
            $Report['ENTRY_LEVEL'] = '1';
            $Message .= '行程No.が未入力です。\n\n';
        } else {
            if (!is_numeric ($Report['KouteiNo'])) {
                // 入力情報レベル１（キー入力レベル）
                $Report['ENTRY_LEVEL'] = '1';
                $Message .= '行程は数値で入力して下さい\n\n';
            }
        }
        
        // 入力項目にエラーがなければ、同じ日報がすでに登録されていないかチェック
        if ($Message == '') {
            $sql = "select work_date from equip_work_report where work_date=" . pg_escape_string ($Report['WorkDate']) . " and mac_no=" . pg_escape_string ($Report['MacNo']) . " and plan_no=" . pg_escape_string ($Report['PlanNo']) . " and koutei=" . pg_escape_string ($Report['KouteiNo']);
            $rs  = pg_query ($con,$sql);
            if ($row = pg_fetch_array ($rs)) {
                // 入力情報レベル１（キー入力レベル）
                $Report['ENTRY_LEVEL'] = '1';
                $Message .= 'この運転日報はすでに登録されています。\n\n';
            }
        }
    }
    
    if (@$_REQUEST['ErrorCheckLevel'] == 2) {
        // 入力情報レベル(1:キー入力 2:明細入力)
        $Report['ENTRY_LEVEL'] = '2';
        // 前日良品数
        if ($Report['Yesterday'] == '') {
            $Report['Yesterday'] = 0;
        } else {
            if (!is_numeric ($Report['Yesterday'])) {
                $Message .= '前日良品数は数値で入力して下さい。\n\n';
            } else {
                if ($Report['Yesterday'] < 0) {
                    $Message .= '前日良品数はマイナスで入力できません。\n\n';
                }
            }
        }
        // 当日良品数
        if ($Report['Today'] == '') {
            $Report['Today'] = 0;
        } else {
            if (!is_numeric ($Report['Today'])) {
                $Message .= '当日良品数は数値で入力して下さい。\n\n';
            } else {
                if ($Report['Today'] < 0) {
                    $Message .= '当日良品数はマイナスで入力できません。\n\n';
                }
            }
        }
        // 不良数
        if ($Report['Ng'] == '') {
            $Report['Ng'] = 0;
        } else {
            if (!is_numeric ($Report['Ng'])) {
                $Message .= '不良数は数値で入力して下さい。\n\n';
            } else {
                if ($Report['Ng'] < 0) {
                    $Message .= '不良数はマイナスで入力できません。\n\n';
                }
            }
        }
        // 段取数
        if ($Report['Plan'] == '') {
            $Report['Plan'] = 0;
        } else {
            if (!is_numeric ($Report['Plan'])) {
                $Message .= '段取数は数値で入力して下さい。\n\n';
            } else {
                if ($Report['Plan'] < 0) {
                    $Message .= '段取数はマイナスで入力できません。\n\n';
                }
            }
        }
        // 当日良品累計数
        
        $gokei = 0;
        $gokei = $Report['Yesterday'] + $Report['Today'];
        /*
        if ($gokei > $Report['Plan']) {
            $Message .= '当日良品累計数が指示数を超えています。\n\n';
        }
        */
        // 運転ログのチェック
        for ($i=0;$i<$LogNum;$i++) {
            // ログ１行の入力項目数
            $isEntry = 0;
            if ($Report['MacState'][$i] != '')  $isEntry++;
            if ($Report['FromHH'][$i] != '')    $isEntry++;
            if ($Report['FromMM'][$i] != '')    $isEntry++;
            if ($Report['ToHH'][$i] != '')      $isEntry++;
            if ($Report['ToMM'][$i] != '')      $isEntry++;
            if ($Report['CutTime'][$i] == '')   $Report['CutTime'][$i] = 0;
            // 空白行か全て入力されていなかったらエラー 0:空白行 5:全て入力されている
            if ($isEntry != 0 && $isEntry != 5) {
                $Message .= $i+1 . '行目の運転日報ログが全て入力されていません。\n\n';
            }
            // 全て入力されていたら、各項目のチェック
            if ($isEntry == 5) {
                // 数字値チェック
                if (!is_numeric ($Report['FromHH'][$i]) || 
                    !is_numeric ($Report['FromMM'][$i]) || 
                    !is_numeric ($Report['ToHH'][$i])   || 
                    !is_numeric ($Report['ToMM'][$i])   ||
                    !is_numeric ($Report['CutTime'][$i])) {
                    $Message .= $i+1 . '行目の時刻は数値で入力して下さい。\n\n';
                } else {
                    // 時刻チェック
                    $CheckTime = true;
                    if ($Report['FromHH'][$i] < 0 || $Report['FromHH'][$i] >= 24) $CheckTime = false;
                    if ($Report['FromMM'][$i] < 0 || $Report['FromMM'][$i] >= 60) $CheckTime = false;
                    if ($Report['ToHH'][$i]   < 0 || $Report['ToHH'][$i]   >= 24) $CheckTime = false;
                    if ($Report['ToMM'][$i]   < 0 || $Report['ToMM'][$i]   >= 60) $CheckTime = false;
                    if ($CheckTime == false) {
                        $Message .= $i+1 . '行目の時刻が正しくありません\n\n';
                    }
                    // カット時間のチェック
                    if ($Report['CutTime'][$i] < 0) {
                        $Message .= $i+1 . 'カット時間はマイナス入力はできません\n\n';
                    }
                }
                // 作業時間のマイナスチェック
                if (!isset($Report['FromTime'][$i])) {
                    $Report['FromTime'][$i] = sprintf('%02d%02d',$Report['FromHH'][$i],$Report['FromMM'][$i]);
                    $Report['ToTime'][$i]   = sprintf('%02d%02d',$Report['ToHH'][$i]  ,$Report['ToMM'][$i]);
                }
                if ( (CalWorkTime($Report['FromDate'][$i], $Report['FromTime'][$i], $Report['ToDate'][$i], $Report['ToTime'][$i]) - $Report['CutTime'][$i]) < 1) {
                    $Message .= $i+1 . '行目の運転日報ログの日時設定が間違っています。\n\n';
                }
            }
        }
        // 材料コード
        if ($Report['InjectionItem'] == '' && ($Report['Injection'] != '' && $Report['Injection'] != '0')) {
            $Message .= '投入材料コードを入力して下さい。\n\n';
        }
        if ($Report['InjectionItem'] != '') {
            // マスタ存在チェック
            $rs = pg_query ($con,"select mtcode,length from equip_materials where mtcode='" . pg_escape_string ($Report['InjectionItem']) . "'");
            if (!$row = pg_fetch_array ($rs)) {
                $Message .= '材料コード['.$Report['InjectionItem'].']はマスタに登録されていません。\n\n';
            }
        }
        // 投入数
        if ($Report['Injection'] == '') {
            $Report['Injection'] = 0;
        }
        if ($Report['InjectionItem'] != '') {
            if ($Report['Injection'] == '') {
                $Message .= '投入数を入力して下さい。\n\n';
            } else {
                if (!is_numeric ($Report['Injection'])) {
                    $Message .= '投入数は数値で入力して下さい。\n\n';
                } else {
                    if ($Report['Injection'] < 0) {
                        $Message .= '投入数はマイナスで入力できません。\n\n';
                    }
                }
            }
        }
        // 端材長さ端
        if ($Report['Abandonment'] == '') {
            $Report['Abandonment'] = 0;
        }
        if ($Report['Abandonment'] != '') {
            if ($Report['Abandonment'] == '') {
                $Message .= '使用端材長さを入力して下さい。\n\n';
            } else {
                if (!is_numeric ($Report['Abandonment'])) {
                    $Message .= '使用端材長さは数値で入力して下さい。\n\n';
                } else {
                    if ($Report['Abandonment'] < 0) {
                        $Message .= '使用端材長さはマイナスで入力できません。\n\n';
                    } else {
                        // 材料マスタ読めない場合はチェックできない
                        if (isset($row)) {
                            if ($row["length"]  <= $Report['Abandonment']) {
                                $Message .= '投入端材長さが材料の標準長さを超えています\n\n';
                            }
                        }
                    }
                }
            }
        }
    }
    
    if ($Message == '') return true;
    else                return false;
}
function SaveData()
{
    global $con,$Report,$LogNum;
    // トランザクション開始
    pg_query ($con , "BEGIN");
    
    if (@$_REQUEST['EDIT_MODE'] == 'UPDATE') {
        // 日報ヘッダ削除
        $sql = "delete from equip_work_report_moni where work_date=" . pg_escape_string ($Report['WorkDate']) . " and mac_no=" . pg_escape_string ($Report['MacNo']) . " and plan_no='" . pg_escape_string ($Report['PlanNo']) . "' and koutei=" . pg_escape_string ($Report['KouteiNo']);
        if (!pg_query ($con , $sql)) {
            pg_query ($con , 'ROLLBACK');
            $SYSTEM_MESSAGE = "データベースの更新に失敗しました\n$sql";
            require_once ('../com/' . ERROR_PAGE);
            exit();
        }
        // 運転ログ削除
        $sql = "delete from equip_work_report_moni_log where work_date=" . pg_escape_string ($Report['WorkDate']) . " and mac_no=" . pg_escape_string ($Report['MacNo']) . " and plan_no='" . pg_escape_string ($Report['PlanNo']) . "' and koutei=" . pg_escape_string ($Report['KouteiNo']);
        if (!pg_query ($con , $sql)) {
            pg_query ($con , 'ROLLBACK');
            $SYSTEM_MESSAGE = "データベースの更新に失敗しました\n$sql";
            require_once ('../com/' . ERROR_PAGE);
            exit();
        }
    }
     
    // 日報ヘッダ作成
    $sql = "insert into equip_work_report_moni (work_date,mac_no,plan_no,koutei,yesterday,today,end_flg,ng,ng_kbn,plan,memo,injection_item,injection,abandonment,decision_flg,last_user) values ( "
         .    pg_escape_string ($Report['WorkDate'])        ." ,"
         .    pg_escape_string ($Report['MacNo'])           ." ,'"
         .    pg_escape_string ($Report['PlanNo'])          ."' ,"
         .    pg_escape_string ($Report['KouteiNo'])        ." ,"
         .    pg_escape_string ($Report['Yesterday'])       ." ,"
         .    pg_escape_string ($Report['Today'])           ." ,"
         ."'".pg_escape_string ($Report['EndFlg'])          ."',"
         .    pg_escape_string ($Report['Ng'])              ." ,"
         ."'".pg_escape_string ($Report['NgKbn'])           ."',"
         .    pg_escape_string ($Report['Plan'])            ." ,"
         ."'".pg_escape_string ($Report['Memo'])            ."',"
         ."'".pg_escape_string ($Report['InjectionItem'])   ."',"
         .    pg_escape_string ($Report['Injection'])       ." ,"
         .    pg_escape_string ($Report['Abandonment'])     ." ,"
         .    0                                             ." ,"          // 確定フラグ
         ."'".pg_escape_string ($_SESSION['User_ID'])       ." ')";
    
    if (!pg_query ($con , $sql)) {
        pg_query ($con , 'ROLLBACK');
        $SYSTEM_MESSAGE = "データベースの更新に失敗しました\n$sql";
        require_once ('../com/' . ERROR_PAGE);
        exit();
    }

    $sql    = "insert into equip_work_report_moni_log(work_date,mac_no,plan_no,koutei,mac_state,from_date,from_time,to_date,to_time,cut_time,last_user) ";
    for ($i=0;$i<$LogNum;$i++) {
        // データがないレコードは無視
        if ($Report['MacState'][$i] == '' ||
            $Report['FromHH'][$i]   == '' || $Report['FromMM'][$i]   == '' ||
            $Report['ToHH'][$i]     == '' || $Report['ToMM'][$i]     == '' ) {
            continue;
        }
        // 日時の計算
        $Report['FromTime'][$i] = sprintf('%02d%02d',$Report['FromHH'][$i],$Report['FromMM'][$i]);
        $Report['ToTime'][$i]   = sprintf('%02d%02d',$Report['ToHH'][$i]  ,$Report['ToMM'][$i]);
        // values句の生成
        $values = " values ( "
             .      pg_escape_string ($Report['WorkDate'])             . " ,"
             .      pg_escape_string ($Report['MacNo'])                . " ,'"
             .      pg_escape_string ($Report['PlanNo'])               . "' ,"
             .      pg_escape_string ($Report['KouteiNo'])             . " ,"
             . "'" .pg_escape_string ($Report['MacState'][$i])         . "',"
             .      pg_escape_string ($Report['FromDate'][$i])         . " ,"
             .      pg_escape_string ($Report['FromTime'][$i])         . " ,"
             .      pg_escape_string ($Report['ToDate'][$i])           . " ,"
             .      pg_escape_string ($Report['ToTime'][$i])           . " ,"
             .      pg_escape_string ($Report['CutTime'][$i])          . " ,"
             . "'" .pg_escape_string ($_SESSION['User_ID'])            . " ')";
        if (!pg_query ($con , $sql.$values)) {
            pg_query ($con , 'ROLLBACK');
            $SYSTEM_MESSAGE = "データベースの更新に失敗しました\n$sql";
            require_once ('../com/' . ERROR_PAGE);
            exit();
        }
    }
    
    pg_query ($con , 'COMMIT');
   
}
// --------------------------------------------------
// 日報データの読み込み
// --------------------------------------------------
function ReadData()
{
    global $con,$Report,$LogNum,$CsvFlg,$ProcCode;
    
    
    // --------------------------------------------------
    // 日報ヘッダ読み込み
    // --------------------------------------------------
    $sql = "select "
         . "    a.work_date         as work_date ,      "
         . "    a.mac_no            as mac_no ,         "
         . "    a.plan_no           as plan_no ,        "
         . "    a.koutei            as koutei,          "
         . "    a.yesterday         as yesterday,       "
         . "    a.today             as today,           "
         . "    a.ng                as ng,              "
         . "    a.plan              as plan,            "
         . "    a.ng_kbn            as ng_kbn,          "
         . "    a.end_flg           as end_flg,         "
         . "    a.memo              as memo,            "
         . "    a.injection_item    as injection_item,  "
         . "    m.type              as type,            "
         . "    m.length            as length,          "
         . "    m.weight            as weight,          "
         . "    a.injection         as injection,       "
         . "    a.abandonment       as abandonment,     "
         . "    a.decision_flg      as decision_flg,    "
         . "    b.mac_name          as mac_name ,       "
         . "    b.csv_flg           as csv_flg ,        "
         . "    c.kanryou           as delivery,        "
         . "    c.plan              as inst_qt,         "
         . "    c.parts_no          as parts_no,        "
         . "    c.plan - c.cut_plan              as plan_cnt,        "
         . "    e.midsc             as midsc,           "
         . "    e.mzist             as mzist            "
         . "from equip_work_report_moni a "
         . "left outer join equip_materials m on a.injection_item=m.mtcode "
         . "left outer join equip_machine_master2 b on a.mac_no=b.mac_no "
         . "left outer join assembly_schedule c on a.plan_no=c.plan_no "
         . "left outer join equip_work_log2_header_moni d on a.mac_no=d.mac_no and a.plan_no=d.plan_no and a.koutei=d.koutei " 
         . "left outer join miitem e on c.parts_no=e.mipn "





         . "where work_date=".pg_escape_string ($Report['WorkDate'])." and a.mac_no=".pg_escape_string ($Report['MacNo'])." and a.plan_no='".pg_escape_string ($Report['PlanNo'])."' and a.koutei=".pg_escape_string ($Report['KouteiNo']);

    $rs = pg_query ($con , $sql);
    
    if ($row = pg_fetch_array ($rs)) {

        $Report['WorkDate']      = trim ( $row['work_date'] );
        $Report['MacNo']         = trim ( $row['mac_no'] );
        $Report['MacName']       = trim ( $row['mac_name'] );
        $Report['PlanNo']        = trim ( $row['plan_no'] );
        $Report['ItemCode']      = trim ( $row['parts_no'] );
        $Report['ItemName']      = trim ( $row['midsc'] );
        $Report['Mzist']         = trim ( $row['mzist'] );
        $Report['KouteiNo']      = trim ( $row['koutei'] );
        //$Report['KouteiName']  = trim ( $row['KouteiName']) );
        $Report['Delivery']      = trim ( $row['delivery'] );
        $Report['DeliveryYYYY']  = trim ( mu_Date::toString($Report['Delivery'] ,'Y') );
        $Report['DeliveryMM']    = trim ( mu_Date::toString($Report['Delivery'] ,'m') );
        $Report['DeliveryDD']    = trim ( mu_Date::toString($Report['Delivery'] ,'d') );
        $Report['PlanNum']       = trim ( $row['plan_cnt'] );
        $Report['Yesterday']     = trim ( $row['yesterday'] );
        $Report['Today']         = trim ( $row['today'] );
        $Report['EndFlg']        = trim ( $row['end_flg'] );
        $Report['Ng']            = $row['ng'];
        $Report['NgKbn']         = trim ( $row['ng_kbn'] );
        $Report['Plan']          = $row['plan'];
        $Report['InjectionItem'] = trim ( $row['injection_item'] );
        $Report['Type']          = trim ( $row['type'] );
        $Report['Length']        = trim ( $row['length'] );
        if ($row['type'] == 'B') {
            $Report['inWeight'] = trim ( round($row['length'] * $row['weight'] * $row['injection'], 2) );
        }
        $Report['Injection']     = trim ( $row['injection'] );
        $query = "SELECT sum(injection) AS sum_injection FROM equip_work_report_moni WHERE work_date<={$row['work_date']} AND mac_no={$row['mac_no']} AND plan_no='{$row['plan_no']}' AND koutei={$row['koutei']}";
        $res = pg_query($con , $query);
        if ($sumRow = pg_fetch_array ($res)) {
            $Report['SUMinjection'] = $sumRow['sum_injection'];
            if ($row['type'] == 'B') {
                $Report['SUMinWeight'] = trim ( round($row['length'] * $row['weight'] * $sumRow['sum_injection'], 2) );
            }
        }
        $Report['Abandonment']   = trim ( $row['abandonment']);
        $Report['Memo']          = trim ( $row['memo'] );
        $Report['DecisionFlg']   = trim ( $row['decision_flg'] );
        
        $CsvFlg = $row['csv_flg'];
        
    } else {
        $SYSTEM_MESSAGE = "データの取得に失敗しました。\n$sql";
        require_once ('../com/'.ERROR_PAGE);
        exit();
    }
    
    // 単位重量の取得
    $Report['AbandonmentWeight'] = 0;
    if ($Report['Injection'] != '') {
        $sql = " select weight from equip_materials where mtcode='" . pg_escape_string ($Report['InjectionItem']) . "'";
        $rs = pg_query($con,$sql);
        if ($row = pg_fetch_array ($rs)) {
            $Report['AbandonmentWeight'] = $row['weight'] * $Report['Abandonment'];
        }
    }
    
    // --------------------------------------------------
    // 日報運転ログ読み込み
    // --------------------------------------------------
    $sql = " select work_date,mac_no,plan_no,koutei,mac_state,from_date,from_time,to_date,to_time,cut_time from equip_work_report_moni_log "
         . " where work_date=".pg_escape_string ($Report['WorkDate'])." and mac_no=".pg_escape_string ($Report['MacNo'])." and plan_no='".pg_escape_string ($Report['PlanNo'])."' and koutei=".pg_escape_string ($Report['KouteiNo'])
         . " order by from_date,from_time,to_date,to_time ";
    $rs = pg_query ($con , $sql);

    $Report['Stop']          = 0;   // チョコ停数
    $Report['Failure']       = 0;   // 故障回数
    // 通常モード
    if (@$_REQUEST['SummaryType'] == 1) {
        for ($i=0,$LogNum=0;;$i++,$LogNum++) {
            if (!$row = pg_fetch_array ($rs)) break;
            $Report['MacState'][$i]     = $row['mac_state'];
            $Report['MacStateName'][$i] = getMachineStateName($CsvFlg,$row['mac_state']);
            $Report['FromDate'][$i]     = $row['from_date'];
            $Report['FromTime'][$i]     = sprintf('%04d',$row['from_time']);
            $Report['FromHH'][$i]       = sprintf('%02d',(int)($row['from_time'] / 100));
            $Report['FromMM'][$i]       = sprintf('%02d',(int)($row['from_time'] - $Report['FromHH'][$i] * 100));
            $Report['ToDate'][$i]     = $row['to_date'];
            $Report['ToTime'][$i]       = sprintf('%04d',$row['to_time']);
            $Report['ToHH'][$i]         = sprintf('%02d',(int)($row['to_time'] / 100));
            $Report['ToMM'][$i]         = sprintf('%02d',(int)($row['to_time'] - $Report['ToHH'][$i] * 100));
            $Report['CutTime'][$i]      = $row['cut_time'];
            if ( CheckCount( "Stop"     , $CsvFlg , $Report['MacState'][$i] )) $Report['Stop']++;
            if ( CheckCount( "Failure"  , $CsvFlg , $Report['MacState'][$i] )) $Report['Failure']++;
        }
    } else {
        // 集計モード
        $MaxRec = 0;
        for ($i=0;;$i++) {
            if (!$row = pg_fetch_array ($rs)) break;
            // １レコード目を開始時刻とする
            if ($i == 0) {
                $StartDate = $row['from_date'];
                $StartTime = $row['from_time'];
            }
            for($k=0;$k<$MaxRec;$k++) {
                // すでに同じステータスコードがあった時は集計
                if ($Report['MacState'][$k] == $row['mac_state']) {
                    $Report['WorkTime'][$k] += CalWorkTime($row['from_date'],$row['from_time'],$row['to_date'],$row['to_time']);
                    $Report['CutTime'][$k]  += $row['cut_time'];
                    break;
                }
            }
            // 同じステータスコードが存在しないので新規レコード作成
            if ($k >= $MaxRec) {
                $Report['MacState'][$MaxRec]        = $row['mac_state'];
                $Report['MacStateName'][$MaxRec]    = getMachineStateName($CsvFlg,$row['mac_state']);
                $Report['WorkTime'][$k]             = CalWorkTime($row['from_date'],$row['from_time'],$row['to_date'],$row['to_time']);
                $Report['CutTime'][$k]              = $row['cut_time'];
                $MaxRec++;
            }
            if ( CheckCount( "Stop"     , $CsvFlg , $row['mac_state'] )) $Report['Stop']++;
            if ( CheckCount( "Failure"  , $CsvFlg , $row['mac_state'] )) $Report['Failure']++;
        }
        // 時間の集計終わったら、開始時刻，終了時刻を計算してセット
        for ($i=0;$i<$MaxRec;$i++) {
            if ($i ==0) {
                $Report['FromDate'][$i] = $StartDate;
                $Report['FromTime'][$i] = $StartTime;
            } else {
                $Report['FromDate'][$i] = $Report['ToDate'][$i-1];
                $Report['FromTime'][$i] = $Report['ToTime'][$i-1];
            }
            $Report['ToDate'][$i]   = CalAddDate($Report['FromDate'][$i] , $Report['FromTime'][$i] , $Report['WorkTime'][$i]);
            $Report['ToTime'][$i]   = CalAddTime($Report['FromTime'][$i] , $Report['WorkTime'][$i]);
            // 分解
            $Report['FromHH'][$i]       = sprintf('%02d',(int)($Report['FromTime'][$i] / 100));
            $Report['FromMM'][$i]       = sprintf('%02d',(int)($Report['FromTime'][$i] - $Report['FromHH'][$i] * 100));
            $Report['ToHH'][$i]         = sprintf('%02d',(int)($Report['ToTime'][$i] / 100));
            $Report['ToMM'][$i]         = sprintf('%02d',(int)($Report['ToTime'][$i] - $Report['ToHH'][$i] * 100));
            
        }
        // 表示ログ数セット
        $LogNum = $MaxRec;
    }
}
// --------------------------------------------------
// 日報削除処理
// --------------------------------------------------
function DeleteData()
{
    global $con,$Report;
    
    // トランザクション開始
    pg_query ($con , 'BEGIN');
    
    // --------------------------------------------------
    // 日報ヘッダの削除
    // --------------------------------------------------
    $sql = "delete from equip_work_report_moni where work_date=".pg_escape_string ($Report['WorkDate'])." and mac_no=".pg_escape_string ($Report['MacNo'])." and plan_no='".pg_escape_string ($Report['PlanNo'])."' and koutei=".pg_escape_string ($Report['KouteiNo']);
    if (!pg_query ($con , $sql)) {
        pg_query ($con , 'ROLLBACK');
        $SYSTEM_MESSAGE = "データベースの更新に失敗しました\n$sql";
        require_once ('../com/' . ERROR_PAGE);
        exit();
    }
    // --------------------------------------------------
    // 日報運転ログ削除
    // --------------------------------------------------
    $sql = "delete from equip_work_report_moni_log where work_date=".pg_escape_string ($Report['WorkDate'])." and mac_no=".pg_escape_string ($Report['MacNo'])." and plan_no='".pg_escape_string ($Report['PlanNo'])."' and koutei=".pg_escape_string ($Report['KouteiNo']);
    if (!pg_query ($con , $sql)) {
        pg_query ($con , 'ROLLBACK');
        $SYSTEM_MESSAGE = "データベースの更新に失敗しました\n$sql";
        require_once ('../com/' . ERROR_PAGE);
        exit();
    }
    
    // コミット
    pg_query ($con , 'COMMIT');
}
// --------------------------------------------------
// 日報確定処理
// --------------------------------------------------
function ExecuteDecision()
{
    global $con,$Report,$CsvFlg,$LogNum;
    
    // 処理日付
    $Year  = date('Y', time()); 
    $Month = date('m', time()); 
    $Day   = date('d', time()); 
    $ProcessingDate = date('Y', time()) . date('m', time()) . date('d', time());
    // トランザクション開始
    pg_query ($con,'BEGIN');
    
    // --------------------------------------------------
    // 機械運転日報に確定フラグセット
    // --------------------------------------------------
    $sql = " update equip_work_report_moni "
         . "        set decision_flg=1 "
         . " where  work_date=" . pg_escape_string ($Report['WorkDate']) . " and "
         . "        mac_no   =" . pg_escape_string ($Report['MacNo'])    . " and "
         . "        plan_no  ='" . pg_escape_string ($Report['PlanNo'])   . "' and "
         . "        koutei   =" . pg_escape_string ($Report['KouteiNo']);
    
    if (!pg_query ($con,$sql)) {
        pg_query ($con , 'ROLLBACK');
        $SYSTEM_MESSAGE = "データベースの更新に失敗しました\n$sql";
        require_once ('../com/' . ERROR_PAGE);
        exit();
    }
    
    // 初期化
    $Summary['stop_time']    = 0;
    $Summary['stop_count']   = 0;
    $Summary['idling_time']  = 0;
    $Summary['plan_time']    = 0;
    $Summary['running_time'] = 0;   // 本稼働時間(自動＋無人)2007/03/28 ADD
    $Summary['plan_count']   = 0;
    $Summary['repair_time']  = 0;
    $Summary['repair_count'] = 0;
    $Summary['edge_time']    = 0;
    $Summary['auto_time']    = 0;
    $Summary['others_time']  = 0;
    $Summary['plan_num']     = 0;
    for ($i=0;$i<$LogNum;$i++) {
        
        // 作業区分の変換
        $CMacState = ChangeMacState($Report['MacNo'],$Report['MacState'][$i]);
        
        // AS/400の時間表記に調整   8:30 → 32：30(次の日の8:30)
        if ($Report['FromTime'][$i] < 830) {
            $Report['FromTimeAS'][$i] = $Report['FromTime'][$i] + 2400;
        } else {
            $Report['FromTimeAS'][$i] = $Report['FromTime'][$i];
        }
        if ($Report['ToTime'][$i] <= 830) {
            $Report['ToTimeAS'][$i] = $Report['ToTime'][$i] + 2400;
        } else {
            $Report['ToTimeAS'][$i] = $Report['ToTime'][$i];
        }
        
        // -------------------------------------------------------
        // 日報 再確定 対応のため 初回のみ旧データを一括 強制 削除
        // -------------------------------------------------------
        /*
        if ($i == 0) {  // 初回のみ
            $sql = ' DELETE FROM equip_upload '
                 . ' WHERE  work_date=' . pg_escape_string ($Report['WorkDate']) . ' and '
                 . '        mac_no   =' . pg_escape_string ($Report['MacNo'])    . ' and '
                 . '        plan_no  =' . pg_escape_string ($Report['PlanNo'])   . ' and '
                 . '        koutei   =' . pg_escape_string ($Report['KouteiNo']);
            
            if (!pg_query($con, $sql)) {
                pg_query($con , 'ROLLBACK');
                $SYSTEM_MESSAGE = "equip_uploadの旧データ削除処理でエラーが発生しました。\n$sql";
                require_once ('../com/' . ERROR_PAGE);
                exit();
            }
        }
        */
        // --------------------------------------------------
        // equip_upload アップロードデータ書込
        // --------------------------------------------------
        /*
        $sql = ' insert into equip_upload '
             . ' values ('
             . pg_escape_string ($Report['WorkDate'])      . ','
             . pg_escape_string ($Report['MacNo'])         . ','
             . pg_escape_string ($Report['PlanNo'])        . ','
             . pg_escape_string ($Report['KouteiNo'])      . ','
             . pg_escape_string ($Report['FromTimeAS'][$i])  . ','
             . pg_escape_string ($Report['ToTimeAS'][$i])    . ','
             . pg_escape_string ($Report['CutTime'][$i])   . ','
        ."'" . pg_escape_string ($CMacState)  . "'            )";
        if (!pg_query ($con,$sql)) {
            pg_query ($con , 'ROLLBACK');
            $SYSTEM_MESSAGE = "データベースの更新に失敗しました\n$sql";
            require_once ('../com/' . ERROR_PAGE);
            exit();
        }
        */
        // --------------------------------------------------
        // サマリー用の集計
        // --------------------------------------------------
        if ($CsvFlg == 1) {
            switch ($Report['MacState'][$i]) {
                case '3':
                    // チョコ停[停止中]
                    $Summary['stop_time'] += CalWorkTime($Report['FromDate'][$i],$Report['FromTime'][$i],$Report['ToDate'][$i],$Report['ToTime'][$i]);
                    $Summary['stop_count']++;
                    break;
                case '10':
                    // アイドリング時間[暖機中]
                    $Summary['idling_time'] += CalWorkTime($Report['FromDate'][$i],$Report['FromTime'][$i],$Report['ToDate'][$i],$Report['ToTime'][$i]);
                    break;
                case '11':
                    // 段取時間[段取中]
                    $Summary['plan_time'] += CalWorkTime($Report['FromDate'][$i],$Report['FromTime'][$i],$Report['ToDate'][$i],$Report['ToTime'][$i]);
                    $Summary['plan_count']++;
                    break;
                case '12':
                    // 故障修理[故障修理]
                    $Summary['repair_time'] += CalWorkTime($Report['FromDate'][$i],$Report['FromTime'][$i],$Report['ToDate'][$i],$Report['ToTime'][$i]);
                    $Summary['repair_count']++;
                    break;
                case '13':
                    // 刃具交換[刃具交換]
                    $Summary['edge_time'] += CalWorkTime($Report['FromDate'][$i],$Report['FromTime'][$i],$Report['ToDate'][$i],$Report['ToTime'][$i]);
                    break;
                case '14':
                    // 無人稼働時間[無人運転]
                    $Summary['auto_time'] += CalWorkTime($Report['FromDate'][$i],$Report['FromTime'][$i],$Report['ToDate'][$i],$Report['ToTime'][$i]);
                case '1':
                    // 自動運転
                    // 以下のrunning_time は 自動＋無人 で本稼働時間
                    $Summary['running_time'] += CalWorkTime($Report['FromDate'][$i],$Report['FromTime'][$i],$Report['ToDate'][$i],$Report['ToTime'][$i]);
                    break;
                default :
                    // その他時間 (電源OFF等)
                    $Summary['others_time'] += CalWorkTime($Report['FromDate'][$i],$Report['FromTime'][$i],$Report['ToDate'][$i],$Report['ToTime'][$i]);
                    break;
            }
        } else {
            switch ($Report['MacState'][$i]) {
                case '3':
                    // チョコ停[停止中]
                    $Summary['stop_time'] += CalWorkTime($Report['FromDate'][$i],$Report['FromTime'][$i],$Report['ToDate'][$i],$Report['ToTime'][$i]);
                    $Summary['stop_count']++;
                    break;
                case '4':
                    // アイドリング時間[暖機中]
                    $Summary['idling_time'] += CalWorkTime($Report['FromDate'][$i],$Report['FromTime'][$i],$Report['ToDate'][$i],$Report['ToTime'][$i]);
                    break;
                case '5':
                    // 段取時間[段取中]
                    $Summary['plan_time'] += CalWorkTime($Report['FromDate'][$i],$Report['FromTime'][$i],$Report['ToDate'][$i],$Report['ToTime'][$i]);
                    $Summary['plan_count']++;
                    break;
                case '6':
                    // 故障修理[故障修理]
                    $Summary['repair_time'] += CalWorkTime($Report['FromDate'][$i],$Report['FromTime'][$i],$Report['ToDate'][$i],$Report['ToTime'][$i]);
                    $Summary['repair_count']++;
                    break;
                case '7':
                    // 刃具交換[刃具交換]
                    $Summary['edge_time'] += CalWorkTime($Report['FromDate'][$i],$Report['FromTime'][$i],$Report['ToDate'][$i],$Report['ToTime'][$i]);
                    break;
                case '8':
                    // 無人稼働時間[無人運転]
                    $Summary['auto_time'] += CalWorkTime($Report['FromDate'][$i],$Report['FromTime'][$i],$Report['ToDate'][$i],$Report['ToTime'][$i]);
                case '1':
                    // 自動運転
                    // 以下のrunning_time は 自動＋無人 で本稼働時間
                    $Summary['running_time'] += CalWorkTime($Report['FromDate'][$i],$Report['FromTime'][$i],$Report['ToDate'][$i],$Report['ToTime'][$i]);
                    break;
                default :
                    // その他時間 (電源OFF等)
                    $Summary['others_time'] += CalWorkTime($Report['FromDate'][$i],$Report['FromTime'][$i],$Report['ToDate'][$i],$Report['ToTime'][$i]);
                    break;
            }
        }
    }
    // -------------------------------------------------------
    // 日報 再確定 対応のため 旧データを一括 強制 削除
    // -------------------------------------------------------
    /*
    $sql = ' DELETE FROM equip_upload_summary '
         . ' WHERE  work_date=' . pg_escape_string ($Report['WorkDate']) . ' and '
         . '        mac_no   =' . pg_escape_string ($Report['MacNo'])    . ' and '
         . '        plan_no  =' . pg_escape_string ($Report['PlanNo'])   . ' and '
         . '        koutei   =' . pg_escape_string ($Report['KouteiNo']);
    
    if (!pg_query($con, $sql)) {
        pg_query($con , 'ROLLBACK');
        $SYSTEM_MESSAGE = "equip_upload_summaryの旧データ削除処理でエラーが発生しました。\n$sql";
        require_once ('../com/' . ERROR_PAGE);
        exit();
    }
    */
    // ---------------------------------------------------
    // equip_upload_summary アップロードサマリーデータ書込
    // ---------------------------------------------------
    // 投入材料のチェック
    if ($Report['Type'] == 'B') {
        $Report['injectionAS'] = $Report['inWeight'];           // バー材
    } else {
        $Report['injectionAS'] = $Report['Injection'] . '.00';  // 切断材 AS側は numeric(8, 2)
    }
    /*
    $sql = ' insert into equip_upload_summary values('
         . (int)$Report['WorkDate']                                 . ','
         . (int)$Report['MacNo']                                    . ','
         . (int)$Report['PlanNo']                                   . ','
         . (int)$Report['KouteiNo']                                 . ','
         . "'" .pg_escape_string ($Report['ItemCode'])              ."',"
         . (int)$Summary['plan_time']                               . ','
         . (int)$Summary['running_time']                            . ','   // 2007/03/28 ADD(自動＋無人)
         . (int)$Summary['repair_time']                             . ','
         . (int)$Summary['edge_time']                               . ','
         . (int)$Summary['stop_time']                               . ','
         . (int)$Summary['idling_time']                             . ','
         . (int)$Summary['auto_time']                               . ','   // 自動運転のみ
         . (int)$Summary['others_time']                             . ','
         . (int)$Report['Today']                                    . ','
         . (int)$Report['Ng']                                       . ','
         . (int)$Report['Plan']                                     . ','
         . "'" .pg_escape_string ($Report['EndFlg'])                ."',"
         . "'" .pg_escape_string ($Report['NgKbn'])                 ."',"
         . (int)$Summary['stop_count']                              . ','
         . (int)$Summary['plan_count']                              . ','
         . (int)$Summary['repair_count']                            . ','
         . "'" .pg_escape_string($Report['InjectionItem'])          ."',"   // 2007/03/28 ADD 投入材料コード
         .      $Report['injectionAS']                              . ','   // 2007/03/28 ADD 投入材料(重量又は個数)
         . (int)$ProcessingDate                                     . ')';
    if (!pg_query($con,$sql)) {
        MLog($sql);
        pg_query ($con , 'ROLLBACK');
        $SYSTEM_MESSAGE = "データベースの更新に失敗しました\n$sql".var_dump($Report);
        require_once ('../com/' . ERROR_PAGE);
        exit();
    }
    */
    // コミット
    pg_query($con,'COMMIT');
    
    // 確定フラグセット（画面表示向け
    $Report['DecisionFlg'] = 1;
}
function CheckCount($Type,$CsvFlg,$MacState)
{
    if ($CsvFlg == 1) {
        if ($Type == 'Stop'     && $MacState ==  3) return true;
        if ($Type == 'Failure'  && $MacState == 12) return true;
    } else {
        if ($Type == 'Stop'     && $MacState ==  3) return true;
        if ($Type == 'Failure'  && $MacState ==  6) return true;
    }
    
    return false;
}
function CalAddDate($Date,$Time,$AddMinutes)
{
    // 時刻分解
    $Hour       = (int)($Time/100);
    $Minutes    = (int)(($Time - $Hour * 100));
    // 分に変換
    $TimeSeconds = $Hour * 60 + $Minutes;
    // 分を加算
    $TimeSeconds += $AddMinutes;
    
    // ２４時超えたら０時から
    if ($TimeSeconds > 1440) {
        $RetVal = mu_Date::addDay($Date,1);
    } else {
        $RetVal = $Date;
    }
    
    return $RetVal;
}
function CalAddTime($Time,$AddMinutes)
{
    // 時刻分解
    $Hour       = (int)($Time/100);
    $Minutes    = (int)(($Time - $Hour * 100));
    // 分に変換
    $TimeSeconds = $Hour * 60 + $Minutes;
    // 分を加算
    $TimeSeconds += $AddMinutes;
    
    // ２４時超えたら０時から
    if ($TimeSeconds > 1440) $TimeSeconds -= 1440;
    
    // 時刻分解
    $Hour       = (int)($TimeSeconds / 60);
    $Minutes    = (int)(($TimeSeconds - $Hour * 60));
    
    $retVal = sprintf ("%02d%02d",$Hour,$Minutes);
    
    return $retVal;
    
}
function LogSelectDate($WorkDate,$FromTo,$Val) {
    
    $Select = "<select name='" . $FromTo . "[]'>";
    
    if ($WorkDate == $Val) {
        $Select .= "<option value='$WorkDate' selected>" . mu_Date::toString($WorkDate ,'m/d') . "</option>";
    } else {
        $Select .= "<option value='$WorkDate' >" . mu_Date::toString($WorkDate,'m/d') . "</option>";
    }
    
    $WorkDate = mu_date::addDay($WorkDate,1);
    if ($WorkDate == $Val) {
        $Select .= "<option value='$WorkDate' selected>" . mu_Date::toString($WorkDate ,'m/d') . "</option>";
    } else {
        $Select .= "<option value='$WorkDate' >" . mu_Date::toString($WorkDate ,'m/d') . "</option>";
    }
    
    $Select .= "</select>";
    
    return $Select;
}
function ChangeMacState($MacNo,$MacState) {
    
    global $con;
    
    // 機械マスタの取得
    $sql = "select csv_flg from equip_machine_master2 where mac_no=$MacNo";
    $rs = pg_query ($con , $sql);
    if (!$row = pg_fetch_array ($rs)) {
        $SYSTEM_MESSAGE = "データの取得に失敗しました。\n$sql";
        require_once ('../com/'.ERROR_PAGE);
        exit();
    }
    
    $CsvFlg = $row['csv_flg'];
    if ($CsvFlg == '1') {
        switch ($MacState) {
            case '0':
                // 電源OFF  -> 9:その他停止
                // 電源OFF  -> 日報に入れないためブランク
                $retVal = '9';
                //$retVal = '';
                break;
            case '1':
                // 自動運転 ->  2:本稼働
                $retVal = '2';
                break;
            case '2':
                // アラーム ->  9:その他停止
                // アラーム ->  6:材料、刃具待ち
                $retVal = '9';
                //$retVal = '6';
                break;
            case '3':
                // 停止中   ->  9:その他停止
                // 停止中   ->  7:チョコ停
                $retVal = '9';
                //$retVal = '7';
                break;
            case '4':
                // Net起動  ->  9:その他停止
                $retVal = '9';
                break;
            case '5':
                // Net終了  ->  9:その他停止
                $retVal = '9';
                break;
            case '10':
                // 暖機中   ->  0:アイドリング
                // 暖機中   ->  0:立上準備
                $retVal = '0';
                break;
            case '11':
                // 段取中   ->  1:段取り
                $retVal = '1';
                break;
            case '12':
                // 故障修理 ->  3:故障修理
                $retVal = '3';
                break;
            case '13':
                // 刃具交換 ->  8:刃具交換
                $retVal = '8';
                break;
            case '14':
                // 無人運転 ->  A:無人稼働
                $retVal = 'A';
                break;
            case '15':
                // 中断     ->  9:その他停止
                $retVal = '9';
                break;
            /*
            case '16':
                // 10予備段取待ち ->  4:段取待ち
                $retVal = '4';
                break;
            case '17':
                // 11予備修理待ち ->  5:修理待ち
                $retVal = '5';
                break;
            default :
                // 未定義   ->  9:その他停止
                $retVal = '9';
            */
                break;
        }
    } else {
        switch ($MacState) {
            case '0':
                // 電源OFF  -> 9:その他停止
                // 電源OFF  -> 日報に入れないためブランク
                $retVal = '9';
                //$retVal = '';
                break;
            case '1':
                // 自動運転 ->  2:本稼働
                $retVal = '2';
                break;
            case '2':
                // アラーム -> 9:その他停止
                // アラーム ->  6:材料、刃具待ち
                $retVal = '9';
                //$retVal = '6';
                break;
            case '3':
                // 停止中    -> 9:その他停止
                // 停止中   ->  7:チョコ停
                $retVal = '9';
                //$retVal = '7';
                break;
            case '4':
                // 暖機中   ->  0:アイドリング
                // 暖機中   ->  0:立上準備
                $retVal = '0';
                break;
            case '5':
                // 段取中   ->  1:段取り
                $retVal = '1';
                break;
            case '6':
                // 故障修理 ->  3:故障修理
                $retVal = '3';
                break;
            case '7':
                // 刃具交換 ->  8:刃具交換
                $retVal = '8';
                break;
            case '8':
                // 無人運転 ->  A:無人稼働
                $retVal = 'A';
                break;
            case '9':
                // 中断     ->  9:その他停止
                $retVal = '9';
                break;
            case '10':
                // 予備１   ->  9:その他停止
                // 予備１   ->  4:段取待ち
                $retVal = '9';
                //$retVal = '4';
                break;
            case '11':
                // 予備２   ->  9:その他停止
                // 予備２   ->  5:修理待ち
                $retVal = '9';
                //$retVal = '5';
                break;
            default :
                // 未定義   ->  9:その他停止
                $retVal = '9';
                break;
        }
    }
    
    return $retVal;
}

ob_end_flush();
