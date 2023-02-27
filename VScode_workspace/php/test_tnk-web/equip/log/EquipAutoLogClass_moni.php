<?php
//////////////////////////////////////////////////////////////////////////////
// 設備稼働管理用 自動ログデータ収集用 Class                                //
// Copyright (C) 2005-2021 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2005/02/17 Created   EquipAutoLogClass.php                               //
//                               equip_auto_log2data_ftp.php からの移行     //
// 2005/06/22 equip_mac_state_log 物理信号とロータリースイッチの組合せ状態  //
//            データの保存用テーブルを追加によるロジック変更                //
//            equip_mac_state_log2は従来同様 物理信号を保存する(過去と互換) //
// 2005/10/07 NetmoniでFTP上の一時ファイルが前回何らかのトラブルで削除できず//
//            に残っている不具合の水平展開でFTPロジックに前回temp削除追加   //
// 2006/03/03 指示番号が変わった時の初回書込み条件を変更(確実に0にするSQL文)//
// 2007/06/26 ent_time→end_time誤字訂正。equip_log_state, equip_log_workCnt//
//            method分割 factoryWhere, macNoWhere メンバーをデバッグ用に追加//
// 2007/06/27 $currentFullPathNameに変更。工場区分等の条件をFTPにも追加。   //
//          上記のグローバル変数を中止,ftp_close()時に各接続ごとにログを記録//
//            カウンターマスター・停止の定義マスターの取得追加              //
// 2007/06/28 checkStopTime()メソッドを追加して停止の有効・無効をチェック   //
//            getLogicalState()・getPhysicalState()メソッドで状態取得を統一 //
// 2007/06/29 equip_index2の引数に/:が抜けていたのでto_char()で書式設定     //
// 2007/06/30 set_factory()set_macNo()に設定結果のメッセージ出力追加        //
// 2007/07/01 equip_ftpConnectRetry()に実行前にmsg ftp_connect/ftp_login@ADD//
// 2007/07/02 /home/fws/{$mac_no}-bcd1/8の2箇所でエラーになる。@使用log追加 //
//            getRotaryStateRetry()メソッドを追加して様子を見る             //
//            getFTP_Retry()メソッド(状態とカウンター共通)を追加し様子を見る//
// 2007/07/03 FTPの再接続処理は３回までリトライするにロジック追加           //
//            getRotaryStateHistory()を追加して強制的に自動運転にしていた所 //
//            ロータリースイッチで自動か無人かを判断。getRotaryState()の実装//
//            は現在の所、最新データのみなので一つの機械で一回のみの実行へ  //
// 2007/07/04 カウンターの書込みをgetSQLworkCntInsert()で1本化し重複チェック//
//   Ver 1.00 この時点でデバッグ完了エラー無し                              //
// 2007/07/06 ロータリースイッチの履歴保存・取得メソッド実装により以下変更  //
//   Ver 1.10 getRotaryState()→getRotaryStateCurrentへgetRotaryStateHistory//
// 2007/07/09 equip_log_state_rotaryHistory_write()空行チェック処理を変更   //
//   Ver 1.11 空行で continue → ログ書込み後、return 処理                  //
//            上記で空行はないのでequip_log_state_rotaryHistoryDebug()追加  //
// 2007/07/10 setRotaryStateCurrentBCD()ロータリースイッチの最新BCD 個別設定//
//   Ver 1.12 メソッド追加 getRotaryStateCurrentBCD()一括取得メソッドの追加 //
// 2007/07/11 2006/06/12に物理信号が停止中でも暖機中の場合は暖機をとる変更が//
//   Ver 1.13 外れていたため equip_state_check()に追加                      //
// 2007/10/05 通信エラーと思われる障害でレコードが無い状態になったため      //
//   Ver 1.14 if (isset($preState)) のチェックを追加                        //
// 2018/01/18 FWS1の時だけフォルダ構造を変更（新しいFWSのテスト        大谷 //
// 2018/11/15 FWS2の切替準備。FWS2で検索しPGM入替                      大谷 //
// 2018/11/19 FWS2の切替完了。FWS1と2でフォルダ構成が違うので注意      大谷 //
// 2018/12/25 Insert時にエラーが発生する為、テスト                     大谷 //
//            同じ時間に違うstateが発生しエラーな為、対応                   //
// 2020/03/13 $ftp_noで検索してkv用の追加。フォルダ構成を確認          大谷 //
// 2021/08/02 テストで加工数を先にしてみる。                           大谷 //
// 2021/08/23 加工数を先にしたのを元に戻した。                         大谷 //
// 2021/08/24 データなしの時、最新時刻ではなくロジカルの時間を取得     大谷 //
// 2021/08/26 equip_work_log2でキーが重複するデータの書き込みエラーを       //
//            起こさないように修正                                     大谷 //
// 2021/10/12 取り込みデータをテスト的にすべて保管するよう変更         大谷 //
//            各データを工場毎に分けて保管するよう変更(7SUSと6のみ)    大谷 //
// 2021/10/27 cnt書き込み時cnt数字の場合は数字を抜き出しカウント数とするよう//
//            変更                                                     大谷 //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
// ini_set('implicit_flush', '0');             // echo print で flush させない(遅くなるため) CLI版
//$currentFullPathName = realpath(dirname(__FILE__));
require_once (realpath(dirname(__FILE__)) . "/../equip_function.php");

if (class_exists('EquipAutoLog')) {
    return;
}
define('EAL_VERSION', '1.14');

/****************************************************************************
*                       base class 基底クラスの定義                         *
****************************************************************************/
///// namespace Common {} は現在使用しない 使用例：Common::ComTableMnt → $obj = new Common::ComTableMnt;
///// Common::$sum_page, Common::set_page_rec() (名前空間::リテラル)
class EquipAutoLog
{
    ///// Private properties
    private $log_name;                  // このクラス共通のログファイル名
    private $str_time;                  // 稼動ログ取得の開始時間(000000)時分秒 から
    private $end_time;                  // 稼動ログ取得の終了時間(235959)時分秒 まで
    private $interface = array();       // (配列)インターフェース番号 csv_flgと対
                                        // 1=netmoni4, 2=fws1, 3=fws2, 4=fws3, ... 101=netmoni4+fws1(複合)
    private $ftp_ip = array();          // (配列)インターフェースのIPアドレス
    private $ftp_host = array();        // (配列)インターフェースのホスト名 監視方式(別名)
    private $ftp_user = array();        // (配列)FTPユーザー名
    private $ftp_pass = array();        // (配列)FTPパスワード
    private $ftp_stream = array();      // (配列)FTPストリーム
    // debug用に限定する場合
    private $factoryWhere = '';         // 工場区分で限定する場合
    private $macNoWhere = '';           // 機械番号で限定する場合
    private $mac_no = '';               // カレントの機械番号
    private $ftp_no = '';               // 接続するインターフェースのftp_no
    private $rotaryState = '';          // カレント機械のロータリースイッチの状態
    
    /************************************************************************
    *                               Public methods                          *
    ************************************************************************/
    ///// Constructer の定義 (php5へ移行時は __construct() へ変更予定)
    public function __construct($str_time = '00:55:00', $end_time = '23:59:00', $log_name = '/tmp/EquipAutoLogClassMoni.log')
    {
        ///// 指定されたログファイル名のチェック & 登録
        $this->equip_log_openChk($log_name);
        ///// パラメータの時間チェックとプロパティへの登録
        if ($this->equip_chkErrorTime($str_time, $end_time) == FALSE) {
            $msg = "開始時間：{$str_time} 終了時間：{$end_time} のErrorです。終了します。";
            $this->equip_log_writer($msg);      // クラス内共用logerの書込み
            $this->equip_exit();                // クラス内共用終了メソッド
        } else {
            $msg = "開始時間：{$str_time} 終了時間：{$end_time} の設定で自動稼動ログ収集の準備が出来ました。";
            $this->equip_log_writer($msg);
        }
    }
    ///// 主にdebug用の工場区分限定用 設定メソッド
    public function set_factory($factory='')
    {
        if ($factory == '') {
            $this->factoryWhere = '';
            $this->equip_log_writer("工場区分を 全て に設定しました。");
            return true;
        } else if ($this->checkGroupMaster($factory, $group_name)) {
            $this->factoryWhere = "AND factory = '{$factory}'";
            $this->equip_log_writer("工場区分を {$group_name} に設定しました。");
            return true;
        } else {
            $this->equip_log_writer("工場区分 {$factory} はマスターにないか無効です！");
            return false;
        }
    }
    ///// 主にdebug用の機械番号限定用 設定メソッド
    public function set_macNo($mac_no='')
    {
        if ($mac_no == '') {
            $this->macNoWhere = '';
            $this->equip_log_writer("機械番号を 全て に設定しました。");
            return true;
        } else if ($this->checkMachineMaster($mac_no, $mac_name)) {
            $this->macNoWhere = "AND mac_no = {$mac_no}";
            $this->equip_log_writer("機械番号を {$mac_no} {$mac_name} に設定しました。");
            return true;
        } else {
            $this->equip_log_writer("機械番号 {$mac_no} はマスターにないか無効です！");
            return false;
        }
    }
    ////////// ログ収集の実行(１サイクル)
    public function equip_logExec_once()
    {
        ///// 設定時間内かのチェック
        if (date('His') < $this->str_time) {
            $this->equip_log_writer('指定時間外ですので実行を中止します。');
            return FALSE;
        }
        if (date('His') > $this->end_time) {
            $this->equip_log_writer('指定時間外ですので実行を中止します。');
            return FALSE;
        }
        ///// 全インターフェースのFTPコネクション確立
        $this->equip_all_ftpConnect();
        ///// 対象インターフェースのFTP ストリームのチェック & ログ収集
        $num = count($this->interface);
        for ($i=0; $i<$num; $i++) {
            if (!($this->ftp_stream[$i])) {
                ///// 再接続の処理
                $this->equip_ftpConnectRetry($i);
                if (!($this->ftp_stream[$i])) $this->equip_ftpConnectRetry($i); // ３回までリトライする
            } else {
                ///// 現在のストリームが有効かチェック
                /*
                if (!ftp_systype($this->ftp_stream[$i])) {
                    $this->equip_log_writer("{$this->ftp_host[$i]}：のFTP接続が切断されました！再接続します。");
                    ///// 再接続の処理
                    $this->equip_ftpConnectRetry($i);
                }
                */
            }
            ///// ログ収集 処理
            if ($this->ftp_stream[$i]) {
                // テストで加工数を先にしてみる
                $this->equip_log_workCnt($this->interface[$i], $this->ftp_stream[$i]);
                ///// ログ収集 機械の状態 処理
                $this->equip_log_state($this->interface[$i], $this->ftp_stream[$i]);
            }
        }
        ///// FTPコネクションをクローズ
        $this->equip_ftp_close();
        return TRUE;
    }
    ////////// ログ収集の実行(１サイクル)
    public function equip_logExec_once_moni()
    {
        ///// 設定時間内かのチェック
        if (date('His') < $this->str_time) {
            $this->equip_log_writer('指定時間外ですので実行を中止します。');
            return FALSE;
        }
        if (date('His') > $this->end_time) {
            $this->equip_log_writer('指定時間外ですので実行を中止します。');
            return FALSE;
        }
        ///// 全インターフェースのFTPコネクション確立
        $this->equip_all_ftpConnect();
        ///// 対象インターフェースのFTP ストリームのチェック & ログ収集
        $num = count($this->interface);
        for ($i=0; $i<$num; $i++) {
            if (!($this->ftp_stream[$i])) {
                ///// 再接続の処理
                $this->equip_ftpConnectRetry($i);
                if (!($this->ftp_stream[$i])) $this->equip_ftpConnectRetry($i); // ３回までリトライする
            } else {
                ///// 現在のストリームが有効かチェック
                /*
                if (!ftp_systype($this->ftp_stream[$i])) {
                    $this->equip_log_writer("{$this->ftp_host[$i]}：のFTP接続が切断されました！再接続します。");
                    ///// 再接続の処理
                    $this->equip_ftpConnectRetry($i);
                }
                */
            }
            ///// ログ収集 処理
            if ($this->ftp_stream[$i]) {
                // テストで加工数を先にしてみる
                $this->equip_log_workCnt_moni($this->interface[$i], $this->ftp_stream[$i]);
                ///// ログ収集 機械の状態 処理
                $this->equip_log_state($this->interface[$i], $this->ftp_stream[$i]);
            }
        }
        ///// FTPコネクションをクローズ
        $this->equip_ftp_close();
        return TRUE;
    }
    ///// クラス内共用ログ書込みメソッド
    public function equip_log_writer($msg)
    {
        $msg = date('Y-m-d H:i:s ') . "{$msg}\n";
        if ( ($fp_log = fopen($this->log_name, 'a')) ) {
            fwrite($fp_log, $msg);
        } else {
            ///// 一度だけ再試行する
            sleep(3);
            if ( ($fp_log = fopen($this->log_name, 'a')) ) {
                fwrite($fp_log, $msg);
            }
        }
        fclose($fp_log);
        return;
    }
    ///// クラス全体 終了メソッド
    public function equip_exit()
    {
        ///// FTP コネクションの終了処理
        $this->equip_ftp_close();
        $this->factoryWhere = '';
        $this->macNoWhere   = '';  
        $this->ftp_no       = '';  
        $this->equip_log_writer('自動ログ収集を終了します。');
        ///// インスタンス化したスクリプト側で終了処理するため以下はコメントアウト
        // exit();
    }
    
    /***************************************************************************
    *                              Protected methods                           *
    ***************************************************************************/
    /*************************** Set & Check methods ************************/
    ///// 開始と終了の時間チェックとプロパティへの登録
    protected function equip_chkErrorTime($str_time, $end_time)
    {
        ////////// 時間フォーマットを数字に変換(:)を取除く(なければ何もしない)
        $str_time = str_replace(':', '', $str_time);
        $end_time = str_replace(':', '', $end_time);
        ////////// 時間が数値であるかチェック
        if (!is_numeric($str_time)) return FALSE;
        if (!is_numeric($end_time)) return FALSE;
        ////////// 時間が６桁であるかチェック
        if (strlen($str_time) != 6) return FALSE;
        if (strlen($end_time) != 6) return FALSE;
        ////////// 時間を時・分・秒に分ける
        $str_hour   = substr($str_time, 0, 2);
        $str_minute = substr($str_time, 2, 2);
        $str_second = substr($str_time, 4, 2);
        $end_hour   = substr($end_time, 0, 2);
        $end_minute = substr($end_time, 2, 2);
        $end_second = substr($end_time, 4, 2);
        ////////// 時・分・秒の適正チェック
        if ($str_hour   < 0 || $str_hour   > 23) return FALSE;  // 23:59:59の次は00:00:00と指定する
        if ($str_minute < 0 || $str_minute > 59) return FALSE;
        if ($str_second < 0 || $str_second > 59) return FALSE;
        if ($end_hour   < 0 || $end_hour   > 23) return FALSE;
        if ($end_minute < 0 || $end_minute > 59) return FALSE;
        if ($end_second < 0 || $end_second > 59) return FALSE;
        ////////// 時間のチェック
        $str_timestamp = mktime($str_hour, $str_minute, $str_second, date('m'), date('d'), date('Y'));
        $end_timestamp = mktime($end_hour, $end_minute, $end_second, date('m'), date('d'), date('Y'));
        if ( ($end_timestamp - $str_timestamp) < 300 && ($end_timestamp - $str_timestamp) > 0 ) {
            $msg = "開始時間：{$str_time} と 終了時間：{$end_time} の間隔は最低５分以上に設定して下さい。";
            $this->equip_log_writer($msg);      // クラス内共用logerの書込み
            $this->equip_exit();                // クラス内共用終了メソッド
        }                           // 上記がマイナスの場合は次の日のend_timeで終了となる
        ////////// プロパティへ登録
        $this->str_time = $str_time;
        $this->end_time = $end_time;
        ////////// 設定完了 戻る
        return TRUE;
    }
    ///// 工場区分(グループ)マスターのチェック
    protected function checkGroupMaster($factory, &$name)
    {
        $query = "
            SELECT group_name FROM equip_group_master WHERE group_no = {$factory}
        ";
        if (getUniResult($query, $name) > 0) {
            return true;
        } else {
            $name = '';
            return false;
        }
    }
    ///// 機械マスターのチェック
    protected function checkMachineMaster($mac_no, &$name)
    {
        $query = "
            SELECT mac_name FROM equip_machine_master2 WHERE mac_no = {$mac_no}
        ";
        if (getUniResult($query, $name) > 0) {
            return true;
        } else {
            $name = '';
            return false;
        }
    }
    
    /*************************** Procedure Type methods ************************/
    ///// 各インターフェースのFTPのコネクションを取得するメソッド
    protected function equip_all_ftpConnect()
    {
        ///// 各インターフェースのFTP接続用の情報を取得
        $res = array();
        if ( ($res = $this->equip_ftpInfo()) ) {
            ///// 各インターフェースとFTPのコネクションを取りログインする
            $i = 0; $this->ftp_stream = array();
            foreach ($res as $r) {
                $interface = $r[0];
                $ftp_host  = $r[1];
                $ftp_ip    = $r[2];
                $ftp_user  = $r[3];
                $ftp_pass  = $r[4];
                $this->ftp_stream[$i] = $this->equip_ftpConnect($ftp_host, $ftp_ip, $ftp_user, $ftp_pass);
                $i++;
            }
        } else {
            $this->equip_log_writer('対象インターフェースがありません！');  // クラス内共用logerの書込み
        }
        return;
    }
    ///// 各インターフェースのFTP接続用の情報を取得。取得できた場合、情報の配列を返す
    protected function equip_ftpInfo()
    {
        ///// プロパティの初期化
        $this->interface = array();
        $this->ftp_host  = array();
        $this->ftp_ip    = array();
        $this->ftp_user  = array();
        $this->ftp_pass  = array();
        ///// テーブルより取得
        $query = "
            SELECT
                interface, host, ip_address, ftp_user, ftp_pass
            FROM
                equip_interface_master
            LEFT OUTER JOIN
                equip_machine_master2 ON (interface = csv_flg)
            WHERE
                ftp_active IS TRUE {$this->factoryWhere} {$this->macNoWhere}
            GROUP BY
                interface, host, ip_address, ftp_user, ftp_pass
            ORDER BY
                interface ASC
        ";
        $res = array();
        if ( ($rows=getResult2($query, $res)) < 0) {
            $this->equip_log_writer('DBからインターフェースの情報取得に失敗しました。');
            $this->equip_exit();
        } else {
            if ($rows == 0) return FALSE;
        }
        for ($r=0; $r<$rows; $r++) {
            $this->interface[$r] = $res[$r][0];
            $this->ftp_host[$r]  = $res[$r][1];
            $this->ftp_ip[$r]    = $res[$r][2];
            $this->ftp_user[$r]  = $res[$r][3];
            $this->ftp_pass[$r]  = $res[$r][4];
        }
        return $res;
    }
    ///// 各インターフェースのFTP接続  FTPストリームを返す(FALSEの場合は接続していない)
    protected function equip_ftpConnect($ftp_host = '', $ftp_ip = '', $ftp_user = '', $ftp_pass = '')
    {
        ////////// FTP CONNECT
        if ( !($ftp_stream = @ftp_connect($ftp_ip)) ) {
            $this->equip_log_writer("{$ftp_host}のFTPの接続に失敗しました。");
            return FALSE;
        } else {
            $this->equip_log_writer("{$ftp_host}のFTPに接続しました。");
            ////////// FTP LOGIN
            if (!@ftp_login($ftp_stream, $ftp_user, $ftp_pass)) {
                $this->equip_log_writer("{$ftp_host}のFTPのloginに失敗しました。");
                ftp_close($ftp_stream);     // この処理は絶対に必要 (要注意)
                return FALSE;
            }
            $this->equip_log_writer("{$ftp_host}のFTPにloginしました。");
        }
        return $ftp_stream;
    }
    ///// 各インターフェースのFTP接続 再試行 処理 (引数は配列のインデックス)
    protected function equip_ftpConnectRetry($i)
    {
        // $this->equip_log_writer("{$this->ftp_host[$i]}：FTPの接続及びloginを再試行します。");
        $this->ftp_stream[$i] = $this->equip_ftpConnect($this->ftp_host[$i], $this->ftp_ip[$i], $this->ftp_user[$i], $this->ftp_pass[$i]);
        if (!($this->ftp_stream[$i])) {
            $this->equip_log_writer("{$this->ftp_host[$i]}：FTPの接続及びloginの再試行で失敗しました。");
        } else {
            $this->equip_log_writer("{$this->ftp_host[$i]}：FTPの接続及びloginの再試行で成功しました。");
        }
    }
    
    /******************************* Out methods ****************************/
    ///// ログファイルオープンのチェック & 登録 methods
    protected function equip_log_openChk($log_name)
    {
        if ( !($fp_log = fopen($log_name, 'a')) ) {
            echo "ログファイル：{$log_name} をオープンできません！\n";
            exit;   // 強制終了
        } else {
            fclose($fp_log);
            $this->log_name = $log_name;
        }
        return;
    }
    ///// FTPのコネクション 終了メソッド
    protected function equip_ftp_close()
    {
        foreach ($this->ftp_stream as $key => $ftp_stream) {
            if ($ftp_stream) {
                if (ftp_close($ftp_stream)) {
                    $this->equip_log_writer("{$this->ftp_host[$key]}のFTPを切断しました。");
                } else {
                    $this->equip_log_writer("{$this->ftp_host[$key]}のFTPを切断に失敗しました。");
                }
            } else {
                $this->equip_log_writer("{$this->ftp_host[$key]}のFTPは接続されていません。");
            }
        }
        $this->ftp_stream = array();    // 初期化して終了
        $this->ftp_ip     = array();
        $this->ftp_host   = array();
        $this->ftp_user   = array();
        $this->ftp_pass   = array();
        $this->interface  = array();
        return;
    }
    
    ///// ログ収集 機械の状態 処理メソッド
    protected function equip_log_state($interface, $ftp_con)
    {
        ///// 稼動中に関係なく 機械マスター監視する機械番号を取得 (物理状態を24時間監視するため)
        $query = "
            SELECT
                mac_no, csv_flg
            FROM
                equip_machine_master2
            WHERE
                csv_flg = {$interface}
                AND
                survey = 'Y'
                {$this->factoryWhere} {$this->macNoWhere}
        ";
            // $interface == Netmoni=1 fwserver1=2 fwserver2=3 fwserver3=4 fwserver4=5 ... , Net&fws1=101
        $res_key = array();
        if ( ($rows_key = getResult($query, $res_key)) < 1) {
            return;
        }
        for ($i=0; $i<$rows_key; $i++) {                        // １レコードずつ処理
            ///// insert 用 変数 初期化
            $mac_no   = $res_key[$i]['mac_no'];
            $csv_flg  = $res_key[$i]['csv_flg'];
            $this->equip_log_state_rotary($mac_no, $csv_flg, $ftp_con);
            $this->equip_log_state_body($mac_no, $csv_flg, $ftp_con);
        }
    }
    ///// ログ収集 ロータリースイッチの状態 処理 メソッド
    protected function equip_log_state_rotary($mac_no, $csv_flg, $ftp_con)
    {
        $query = "
            SELECT
                interface
            FROM
                equip_machine_interface
            WHERE
                mac_no = {$mac_no}
        ";
        $ftp_no = 0;
        $this->ftp_no = 0;
        getUniResult($query, $ftp_no);
        $this->ftp_no = $ftp_no;
        /*
        if ($ftp_no == 2) {
            $fws_rotary_log = "/MMC/Plc_Work/{$mac_no}_bcd_state.log";
            $fws_rotary_tmp = "/MMC/Plc_Work/{$mac_no}_bcd_state.tmp";
            $local_rotary = "/home/fws/{$mac_no}_bcd_state.log";
        } else {
            $fws_rotary_log = "/home/fws/usr/{$mac_no}_bcd_state.log";
            $fws_rotary_tmp = "/home/fws/usr/{$mac_no}_bcd_state.tmp";
            $local_rotary = "/home/fws/{$mac_no}_bcd_state.log";
        }
        */
        // FWS2も切替、上をこちらと入れ替える
        if ($ftp_no == 2) {
            $fws_rotary_log = "/MMC/Plc_Work/{$mac_no}_bcd_state.log";
            $fws_rotary_tmp = "/MMC/Plc_Work/{$mac_no}_bcd_state.tmp";
            $local_rotary = "/home/fws/{$mac_no}_bcd_state.log";
        } elseif ($ftp_no == 3) {
            $fws_rotary_log = "/0_CARD/Plc_Work/{$mac_no}_bcd_state.log";
            $fws_rotary_tmp = "/0_CARD/Plc_Work/{$mac_no}_bcd_state.tmp";
            $local_rotary = "/home/fws/{$mac_no}_bcd_state.log";
        } elseif ($ftp_no == 4) {
            $fws_rotary_log = "/0_CARD/Plc_Work/{$mac_no}_bcd_state.log";
            $fws_rotary_tmp = "/0_CARD/Plc_Work/{$mac_no}_bcd_state.tmp";
            $local_rotary = "/home/fws/{$mac_no}_bcd_state.log";
        } elseif ($ftp_no == 7 || $ftp_no == 8) {
            $fws_rotary_log = "/0_CARD/Plc_Work/{$mac_no}_bcd_state.log";
            $fws_rotary_tmp = "/0_CARD/Plc_Work/{$mac_no}_bcd_state.tmp";
            $local_rotary = "/home/fws/{$mac_no}_bcd_state.log";
        } else {
            $fws_rotary_log = "/home/fws/usr/{$mac_no}_bcd_state.log";
            $fws_rotary_tmp = "/home/fws/usr/{$mac_no}_bcd_state.tmp";
            $local_rotary = "/home/fws/{$mac_no}_bcd_state.log";
        }
        ///// 共通 FTP Download 処理
        $this->equip_FTP_Download($fws_rotary_log, $fws_rotary_tmp, $local_rotary, $ftp_con, $mac_no);
        ///// Rotary Log File Check        ロータリースイッチの履歴
        // テスト テスト解除時はelse側のみ残し
        if ($mac_no=='1346' || $mac_no=='1347' || $mac_no=='1348' || $mac_no=='1349' || $mac_no=='1364' || $mac_no=='1365' || $mac_no=='1366' || $mac_no=='1367' || $mac_no=='1368' || $mac_no=='1369' || $mac_no=='1372' || $mac_no=='1373' || $mac_no=='1374') {
            $timestamp = time();
            $rotary_temp = "/home/fws/第7工場真鍮/{$mac_no}_bcd_state" . $timestamp . ".tmp" ;     // Rename 用ディレクトリ・ファイル名生成
        } elseif ($mac_no=='1224' || $mac_no=='1228' || $mac_no=='1258' || $mac_no=='1225' || $mac_no=='1226' || $mac_no=='1227' || $mac_no=='1228' || $mac_no=='1229' || $mac_no=='1230' || $mac_no=='1233' || $mac_no=='1234' || $mac_no=='1235' || $mac_no=='1257' || $mac_no=='1259') {
            $timestamp = time();
            $rotary_temp = "/home/fws/第7工場SUS/{$mac_no}_bcd_state" . $timestamp . ".tmp" ;     // Rename 用ディレクトリ・ファイル名生成
        } elseif ($mac_no=='2101' || $mac_no=='2103' || $mac_no=='2106' || $mac_no=='2901' || $mac_no=='2902' || $mac_no=='2903' || $mac_no=='6000' || $mac_no=='6001' || $mac_no=='6002' || $mac_no=='6003' || $mac_no=='6004' || $mac_no=='6005') {
            $timestamp = time();
            $rotary_temp = "/home/fws/第6工場/{$mac_no}_bcd_state" . $timestamp . ".tmp" ;     // Rename 用ディレクトリ・ファイル名生成
        } else {
            $rotary_temp = "/home/fws/{$mac_no}_bcd_state.tmp";     // Rename 用ファイル名生成
        }
        
        if (file_exists($local_rotary)) {                       // Rotary Log File があれば
            if (rename($local_rotary, $rotary_temp)) {
                $this->equip_log_state_rotaryHistory_write($mac_no, $csv_flg, $rotary_temp);
            } else {
                $msg = "ロータリースイッチの rename({$local_rotary}) に失敗";
                $this->equip_log_writer($msg);
            }
        } else {
            ///// ロータリースイッチの履歴データが無い場合は最新データのみ取得
            $this->equip_log_state_rotaryCurrent_write($mac_no, $csv_flg, $ftp_con);
        }
        ///// State Log 処理終了
    }
    ///// ログ収集 共通 FTP Download 処理 メソッド
    protected function equip_FTP_Download($fws_log, $fws_tmp, $local_log, $ftp_con, $mac_no)
    {
        $query = "
            SELECT
                interface
            FROM
                equip_machine_interface
            WHERE
                mac_no = {$mac_no}
        ";
        $ftp_no = 0;
        getUniResult($query, $ftp_no);
        if ($ftp_no == 2) {
            // データ収集ファイルのlocalとfws側の設定
            $stop_local = "/home/fws/write_protect.mes";
            $stop_fws = "/MMC/Plc_Work/write_protect.mes";
            //chmod($stop_local, 0666);
            $fp = fopen($stop_local, 'a');
            flock($fp, LOCK_EX);
            ftruncate($fp,0);
            flock($fp, LOCK_UN);
            $date_time = date('H:i:s');
            fwrite($fp, "{$date_time}\n");
            fclose($fp);
            //chmod($stop_local, 0666);
            // 新FWSの注意点
            // ftp_sizeが効かないのでファイル存在のチェックが出来ない
            // そのためエラーを吐き出し続けてしまう
            
            // 新プログラム
            // Stopファイルを転送、データ収集が止まった後にコピーして削除
            
            if (@ftp_put($ftp_con, $stop_fws, $stop_local, FTP_ASCII) == false) {
                // stopファイル転送エラーの為、何もしない
            } else {
                // 2秒ディレイ 3秒に1回ファイルを確認する為
                sleep(2);
                if (@ftp_get($ftp_con, $local_log, $fws_log, FTP_ASCII) == false) {
                    // ファイルが無かった可能性もあり
                    //$this->equip_log_writer("{$fws_tmp}：FTPのDownloadに失敗しました。");
                    $this->getFTP_Retry($ftp_con, $local_log, $fws_log, $mac_no);
                } else if (@ftp_delete($ftp_con, $fws_log) == false) {
                    // 旧ファイルの削除失敗失敗時に特に何もしてないので
                    // 問題ないかと
                } else {
                    // FTP Download OK
                }
                ftp_delete($ftp_con, $stop_fws);    // ストップファイルは削除
            }
            
            // FTP上の一時ファイルの削除
            //if (file_exists($fws_tmp) == true) {
            /* 元プログラム
            if (ftp_size($ftp_con, $fws_tmp) != -1) {
               //$this->equip_log_writer("テスト1 {$fws_log}");
               ftp_delete($ftp_con, $fws_tmp);
            }
            /////////// FTP上のファイルの存在チェック
            //if (file_exists($fws_log) == false) {
            if (ftp_size($ftp_con, $fws_log) == -1) {
                //$this->equip_log_writer("テスト2 {$fws_log}");
                // ファイルが無いので何もしない
            } else if (ftp_rename($ftp_con, $fws_log, $fws_tmp) == false) {
                $this->equip_log_writer("FTP rename() 失敗 {$fws_log}");
                if (file_exists($local_log)) unlink($local_log);
            } else if (@ftp_get($ftp_con, $local_log, $fws_tmp, FTP_ASCII) == false) {
                $this->equip_log_writer("{$fws_tmp}：FTPのDownloadに失敗しました。");
                $this->getFTP_Retry($ftp_con, $local_log, $fws_tmp, $mac_no);
            } else {
                // FTP Download OK
                ftp_delete($ftp_con, $fws_tmp);  // 旧ファイルは削除
            }
            */
        // FWS2の切替 コメント解除
        } elseif ($ftp_no == 3) {
            // データ収集ファイルのlocalとfws側の設定
            $stop_local = "/home/fws/write_protect.mes";
            $stop_fws = "/0_CARD/Plc_Work/write_protect.mes";
            //chmod($stop_local, 0666);
            $fp = fopen($stop_local, 'a');
            flock($fp, LOCK_EX);
            ftruncate($fp,0);
            flock($fp, LOCK_UN);
            $date_time = date('H:i:s');
            fwrite($fp, "{$date_time}\n");
            fclose($fp);
            //chmod($stop_local, 0666);
            // 新FWSの注意点
            // ftp_sizeが効かないのでファイル存在のチェックが出来ない
            // そのためエラーを吐き出し続けてしまう
            
            // 新プログラム
            // Stopファイルを転送、データ収集が止まった後にコピーして削除
            
            if (@ftp_put($ftp_con, $stop_fws, $stop_local, FTP_ASCII) == false) {
                // stopファイル転送エラーの為、何もしない
            } else {
                // 2秒ディレイ 3秒に1回ファイルを確認する為
                sleep(2);
                if (@ftp_get($ftp_con, $local_log, $fws_log, FTP_ASCII) == false) {
                    // ファイルが無かった可能性もあり
                    //$this->equip_log_writer("{$fws_tmp}：FTPのDownloadに失敗しました。");
                    $this->getFTP_Retry($ftp_con, $local_log, $fws_log, $mac_no);
                } else if (@ftp_delete($ftp_con, $fws_log) == false) {
                    // 旧ファイルの削除失敗失敗時に特に何もしてないので
                    // 問題ないかと
                } else {
                    // FTP Download OK
                }
                ftp_delete($ftp_con, $stop_fws);    // ストップファイルは削除
            }
        // FWS3の切替 コメント解除
        } elseif ($ftp_no == 4) {
            // データ収集ファイルのlocalとfws側の設定
            $stop_local = "/home/fws/write_protect.mes";
            $stop_fws = "/0_CARD/Plc_Work/write_protect.mes";
            //chmod($stop_local, 0666);
            $fp = fopen($stop_local, 'a');
            flock($fp, LOCK_EX);
            ftruncate($fp,0);
            flock($fp, LOCK_UN);
            $date_time = date('H:i:s');
            fwrite($fp, "{$date_time}\n");
            fclose($fp);
            //chmod($stop_local, 0666);
            // 新FWSの注意点
            // ftp_sizeが効かないのでファイル存在のチェックが出来ない
            // そのためエラーを吐き出し続けてしまう
            
            // 新プログラム
            // Stopファイルを転送、データ収集が止まった後にコピーして削除
            
            if (@ftp_put($ftp_con, $stop_fws, $stop_local, FTP_ASCII) == false) {
                // stopファイル転送エラーの為、何もしない
            } else {
                // 2秒ディレイ 3秒に1回ファイルを確認する為
                sleep(2);
                if (@ftp_get($ftp_con, $local_log, $fws_log, FTP_ASCII) == false) {
                    // ファイルが無かった可能性もあり
                    //$this->equip_log_writer("{$fws_tmp}：FTPのDownloadに失敗しました。");
                    $this->getFTP_Retry($ftp_con, $local_log, $fws_log, $mac_no);
                } else if (@ftp_delete($ftp_con, $fws_log) == false) {
                    // 旧ファイルの削除失敗失敗時に特に何もしてないので
                    // 問題ないかと
                } else {
                    // FTP Download OK
                }
                ftp_delete($ftp_con, $stop_fws);    // ストップファイルは削除
            }
        } elseif ($ftp_no ==7 || $ftp_no == 8) {
            // データ収集ファイルのlocalとfws側の設定
            $stop_local = "/home/fws/write_protect.mes";
            $stop_fws = "/0_CARD/Plc_Work/write_protect.mes";
            //chmod($stop_local, 0666);
            $fp = fopen($stop_local, 'a');
            flock($fp, LOCK_EX);
            ftruncate($fp,0);
            flock($fp, LOCK_UN);
            $date_time = date('H:i:s');
            fwrite($fp, "{$date_time}\n");
            fclose($fp);
            //chmod($stop_local, 0666);
            // 新FWSの注意点
            // ftp_sizeが効かないのでファイル存在のチェックが出来ない
            // そのためエラーを吐き出し続けてしまう
            
            // 新プログラム
            // Stopファイルを転送、データ収集が止まった後にコピーして削除
            
            if (@ftp_put($ftp_con, $stop_fws, $stop_local, FTP_ASCII) == false) {
                // stopファイル転送エラーの為、何もしない
            } else {
                // 2秒ディレイ 3秒に1回ファイルを確認する為
                sleep(2);
                if (@ftp_get($ftp_con, $local_log, $fws_log, FTP_ASCII) == false) {
                    // ファイルが無かった可能性もあり
                    //$this->equip_log_writer("{$fws_tmp}：FTPのDownloadに失敗しました。");
                    $this->getFTP_Retry($ftp_con, $local_log, $fws_log, $mac_no);
                } else if (@ftp_delete($ftp_con, $fws_log) == false) {
                    // 旧ファイルの削除失敗失敗時に特に何もしてないので
                    // 問題ないかと
                } else {
                    // FTP Download OK
                }
                ftp_delete($ftp_con, $stop_fws);    // ストップファイルは削除
            }
        } else {
            // FTP上の一時ファイルの存在チェック(前回トラブルで削除できず残っている場合の対応)
            if (ftp_size($ftp_con, $fws_tmp) != -1) {
                ftp_delete($ftp_con, $fws_tmp);
            }
            /////////// FTP上のファイルの存在チェック
            if (ftp_size($ftp_con, $fws_log) == -1) {
                // ファイルが無いので何もしない
            } else if (ftp_rename($ftp_con, $fws_log, $fws_tmp) == false) {
                $this->equip_log_writer("FTP rename() 失敗 {$fws_log}");
                if (file_exists($local_log)) unlink($local_log);
            } else if (@ftp_get($ftp_con, $local_log, $fws_tmp, FTP_ASCII) == false) {
                $this->equip_log_writer("{$fws_tmp}：FTPのDownloadに失敗しました。");
                $this->getFTP_Retry($ftp_con, $local_log, $fws_tmp, $mac_no);
            } else {
                // FTP Download OK
                ftp_delete($ftp_con, $fws_tmp);  // 旧ファイルは削除
            }
        }
    }
    ///// ログ収集 ローカルの一時ファイルでBCD演算 処理 メソッド
    protected function equip_log_state_rotaryHistory_write($mac_no, $csv_flg, $rotary_temp)
    {
        $fp = fopen($rotary_temp, 'r');
        // $bcd1 = 0; $bcd2 = 0; $bcd4 = 0; $bcd8 = 0;
        $this->getRotaryStateCurrentBCD($mac_no, $bcd1, $bcd2, $bcd4, $bcd8);
        $i = 0;
        while ( ($data = fgetcsv($fp, '50', ',')) !== false) {
            if (!$data[0]) {
                $this->equip_log_writer("{$mac_no}のロータリースイッチの履歴書込みは空行があるため中止します。");
                return; // continue;    // 空行チェック
            }
            $date_time = "{$data[0]} {$data[1]}";
            switch ($data[2]) {
            case 'bcd1':
                if ($data[3] == 'on') {
                    $bcd1 = 1;
                    $this->setRotaryStateCurrentBCD($mac_no, 'BCD1', 1, $date_time);
                } else {
                    $bcd1 = 0;
                    $this->setRotaryStateCurrentBCD($mac_no, 'BCD1', 0, $date_time);
                }
                break;
            case 'bcd2':
                if ($data[3] == 'on') {
                    $bcd2 = 2;
                    $this->setRotaryStateCurrentBCD($mac_no, 'BCD2', 2, $date_time);
                } else {
                    $bcd2 = 0;
                    $this->setRotaryStateCurrentBCD($mac_no, 'BCD2', 0, $date_time);
                }
                break;
            case 'bcd4':
                if ($data[3] == 'on') {
                    $bcd4 = 4;
                    $this->setRotaryStateCurrentBCD($mac_no, 'BCD4', 4, $date_time);
                } else {
                    $bcd4 = 0;
                    $this->setRotaryStateCurrentBCD($mac_no, 'BCD4', 0, $date_time);
                }
                break;
            case 'bcd8':
                if ($data[3] == 'on') {
                    $bcd8 = 8;
                    $this->setRotaryStateCurrentBCD($mac_no, 'BCD8', 8, $date_time);
                } else {
                    $bcd8 = 0;
                    $this->setRotaryStateCurrentBCD($mac_no, 'BCD8', 0, $date_time);
                }
                break;
            }
            $state = $bcd1 + $bcd2 + $bcd4 + $bcd8;
            if ($i > 0) {
                $query = "SELECT (TIMESTAMP '{$data[0]} {$data[1]}' - TIMESTAMP '{$preData[0]} {$preData[1]}') > INTERVAL '3 second' AS check_flg";
                getUniResult($query, $check_flg);
                if ($check_flg == 't') {
                    // 途中での３秒を超える場合は履歴確定 書込み
                    $this->equip_log_state_rotaryHistory_write_body($mac_no, $preState, $preData, $csv_flg);
                }
            }
            $this->equip_log_state_rotaryHistoryDebug($mac_no, $data, $state, $i);  // デバッグ用
            $preData = $data;
            $preState = $state;
            $i++;
        }
        // file end での履歴データ 書込み
        if (isset($preState)) { // 2007/10/05 通信エラーと思われる障害でレコードが無い状態になったためチェックを追加
            $this->equip_log_state_rotaryHistory_write_body($mac_no, $preState, $preData, $csv_flg);
        } else {
            $msg = "ロータリースイッチの履歴ファイルにレコードが無い：{$rotary_temp}\n";
            $this->equip_log_writer($msg);
        }
        fclose($fp);
        
        // テスト テスト解除時はelse側のみ残し
        if ($mac_no=='1224' || $mac_no=='1228' || $mac_no=='1258' || $mac_no=='1225' || $mac_no=='1226' || $mac_no=='1227' || $mac_no=='1228' || $mac_no=='1229' || $mac_no=='1230' || $mac_no=='1233' || $mac_no=='1234' || $mac_no=='1235' || $mac_no=='1257' || $mac_no=='1259' || $mac_no=='2101' || $mac_no=='2103' || $mac_no=='2106' || $mac_no=='2901' || $mac_no=='2902' || $mac_no=='2903' || $mac_no=='6000' || $mac_no=='6001' || $mac_no=='6002' || $mac_no=='6003' || $mac_no=='6004' || $mac_no=='6005' || $mac_no=='1346' || $mac_no=='1347' || $mac_no=='1348' || $mac_no=='1349' || $mac_no=='1364' || $mac_no=='1365' || $mac_no=='1366' || $mac_no=='1367' || $mac_no=='1368' || $mac_no=='1369' || $mac_no=='1372' || $mac_no=='1373' || $mac_no=='1374') {
        } else {
        unlink($rotary_temp);        // 一時ファイルの削除
        }
    }
    ///// ログ収集 ロータリースイッチの履歴書込み前のデバッグデータ 処理 メソッド
    protected function equip_log_state_rotaryHistoryDebug($mac_no, $data, $state, $i)
    {
        $currentDir = realpath(dirname(__FILE__));
        $out_file = "{$currentDir}/rotary_BCD_debug.txt";
        $fp = fopen($out_file, 'a');
        $state_name = equip_machine_state($mac_no, $state, $txt_color, $bg_color);
        if ($i == 0) $mac_no = "\n{$mac_no}";   // データの切れ目を明確にする
        if ($data[3] == 'on') {
            fwrite($fp, "{$mac_no},{$data[0]},{$data[1]},{$data[2]},{$data[3]} ,{$state},{$state_name}\n");
        } else {
            fwrite($fp, "{$mac_no},{$data[0]},{$data[1]},{$data[2]},{$data[3]},{$state},{$state_name}\n");
        }
        fclose($fp);
        chmod($out_file, 0666);
    }
    ///// ログ収集 ロータリースイッチの履歴書込み 処理 メソッド
    protected function equip_log_state_rotaryHistory_write_body($mac_no, $state, $data, $csv_flg)
    {
        $state_name = equip_machine_state($mac_no, $state, $txt_color, $bg_color);
        $time_temp = $data[0] . " " . $data[1];     // timestamp型の変数生成 注意
        //if ($this->equip_log_require($mac_no, $state, $time_temp, 1)) {
            $sql = "
                INSERT INTO equip_mac_state_log3 (mac_no, state, date_time, state_name, state_type)
                VALUES
                    ({$mac_no}, {$state}, '{$data[0]} {$data[1]}', '$state_name', $csv_flg)
            ";
            if (query_affected($sql) <= 0) {
                $msg = "ロータリースイッチの履歴 insert error{$sql}\n";
                $this->equip_log_writer($msg);
            }
        //}
    }
    ///// ログ収集 ロータリースイッチの最新状態のチェック＆書込み 処理 メソッド
    protected function equip_log_state_rotaryCurrent_write($mac_no, $csv_flg, $ftp_con)
    {
        $state = $this->getRotaryStateCurrent($ftp_con, $mac_no);
        $date_time = date('Y-m-d H:i:s');
        if ($state == $this->getRotaryStateHistory($mac_no, $date_time)) return ;
        $state_name = equip_machine_state($mac_no, $state, $txt_color, $bg_color);
        //if ($this->equip_log_require($mac_no, $state, $date_time, 1)) {
            $sql = "
                INSERT INTO equip_mac_state_log3 (mac_no, state, date_time, state_name, state_type)
                VALUES
                    ({$mac_no}, {$state}, '{$date_time}', '$state_name', $csv_flg)
            ";
            if (query_affected($sql) <= 0) {
                $msg = "ロータリースイッチの最新データを履歴へ insert error{$sql}\n";
                $this->equip_log_writer($msg);
            } else {
                $this->equip_log_writer("ロータリースイッチの履歴最終とカレントデータが矛盾しているため書込みました{$sql}");
            }
        //}
    }
    ///// ログ収集 機械の状態 (物理データ→論理データ) 処理 本体部 メソッド
    protected function equip_log_state_body($mac_no, $csv_flg, $ftp_con)
    {
        $query = "
            SELECT
                interface
            FROM
                equip_machine_interface
            WHERE
                mac_no = {$mac_no}
        ";
        $ftp_no = 0;
        getUniResult($query, $ftp_no);
        /*
        if ($ftp_no == 2) {
            $fws_state_log = "/MMC/Plc_Work/{$mac_no}_work_state.log";
            $fws_state_tmp = "/MMC/Plc_Work/{$mac_no}_work_state.tmp";
            $local_state = "/home/fws/{$mac_no}_work_state.log";
        } else {
            $fws_state_log = "/home/fws/usr/{$mac_no}_work_state.log";
            $fws_state_tmp = "/home/fws/usr/{$mac_no}_work_state.tmp";
            $local_state = "/home/fws/{$mac_no}_work_state.log";
        }
        */
        // FWS2 切替準備。 上のPGMと入替
        if ($ftp_no == 2) {
            $fws_state_log = "/MMC/Plc_Work/{$mac_no}_work_state.log";
            $fws_state_tmp = "/MMC/Plc_Work/{$mac_no}_work_state.tmp";
            $local_state = "/home/fws/{$mac_no}_work_state.log";
        } elseif ($ftp_no == 3) {
            $fws_state_log = "/0_CARD/Plc_Work/{$mac_no}_work_state.log";
            $fws_state_tmp = "/0_CARD/Plc_Work/{$mac_no}_work_state.tmp";
            $local_state = "/home/fws/{$mac_no}_work_state.log";
        } elseif ($ftp_no == 4) {
            $fws_state_log = "/0_CARD/Plc_Work/{$mac_no}_work_state.log";
            $fws_state_tmp = "/0_CARD/Plc_Work/{$mac_no}_work_state.tmp";
            $local_state = "/home/fws/{$mac_no}_work_state.log";
        } elseif ($ftp_no == 7 || $ftp_no == 8) {
            $fws_state_log = "/0_CARD/Plc_Work/{$mac_no}_work_state.log";
            $fws_state_tmp = "/0_CARD/Plc_Work/{$mac_no}_work_state.tmp";
            $local_state = "/home/fws/{$mac_no}_work_state.log";
        } else {
            $fws_state_log = "/home/fws/usr/{$mac_no}_work_state.log";
            $fws_state_tmp = "/home/fws/usr/{$mac_no}_work_state.tmp";
            $local_state = "/home/fws/{$mac_no}_work_state.log";
        }
        ///// 共通 FTP Download 処理
        $this->equip_FTP_Download($fws_state_log, $fws_state_tmp, $local_state, $ftp_con, $mac_no);
        ///// State Log File Check        現在の状態(運転中・停止中)・日時を取得 後日に電源OFFも取得予定
        // テスト 1225は正しいものとして取得 テスト解除時はelse側のみ残し
        if ($mac_no=='1346' || $mac_no=='1347' || $mac_no=='1348' || $mac_no=='1349' || $mac_no=='1364' || $mac_no=='1365' || $mac_no=='1366' || $mac_no=='1367' || $mac_no=='1368' || $mac_no=='1369' || $mac_no=='1372' || $mac_no=='1373' || $mac_no=='1374') {
            $timestamp = time();
            $state_temp = "/home/fws/第7工場真鍮/{$mac_no}_work_state" . $timestamp . ".tmp" ;     // Rename 用ディレクトリ・ファイル名生成
        } elseif ($mac_no=='1224' || $mac_no=='1228' || $mac_no=='1258' || $mac_no=='1225' || $mac_no=='1226' || $mac_no=='1227' || $mac_no=='1228' || $mac_no=='1229' || $mac_no=='1230' || $mac_no=='1233' || $mac_no=='1234' || $mac_no=='1235' || $mac_no=='1257' || $mac_no=='1259') {
            $timestamp = time();
            $state_temp = "/home/fws/第7工場SUS/{$mac_no}_work_state" . $timestamp . ".tmp" ;     // Rename 用ディレクトリ・ファイル名生成
        } elseif ($mac_no=='2101' || $mac_no=='2103' || $mac_no=='2106' || $mac_no=='2901' || $mac_no=='2902' || $mac_no=='2903' || $mac_no=='6000' || $mac_no=='6001' || $mac_no=='6002' || $mac_no=='6003' || $mac_no=='6004' || $mac_no=='6005') {
            $timestamp = time();
            $state_temp = "/home/fws/第6工場/{$mac_no}_work_state" . $timestamp . ".tmp" ;     // Rename 用ディレクトリ・ファイル名生成
        } else {
            $state_temp = "/home/fws/{$mac_no}_work_state.tmp";     // Rename 用ディレクトリ・ファイル名生成
        }
        if (file_exists($local_state)) {                         // State Log File があれば
            if (rename($local_state, $state_temp)) {
                $this->equip_log_state_ftp_write($mac_no, $csv_flg, $state_temp, $ftp_con);
            } else {
                $msg = "ステータスファイルの rename({$local_state}) に失敗";
                $this->equip_log_writer($msg);
            }
        } else {
            ///// State Log file がない場合はロータリースイッチのみチェックする
            // 物理信号をDBより取得
            $this->equip_log_state_db_write($mac_no, $csv_flg, $ftp_con);
        }
        ///// State Log 処理終了
    }
    ///// 物理状態ファイルのFTPダウンロード 再試行 処理メソッド
    protected function getFTP_Retry($ftp_con, $local_file, $fws_file, $mac_no)
    {
        if (@ftp_get($ftp_con, $local_file, $fws_file, FTP_ASCII)) {
            $this->equip_log_writer("{$fws_file}：Downloadの再試行で成功しました。");
            ftp_delete($ftp_con, $fws_file);  // 旧ファイルは削除
            return true;
        } else {
            $query = "
                SELECT
                interface
                FROM
                equip_machine_interface
                WHERE
                mac_no = {$mac_no}
            ";
            $ftp_no = 0;
            getUniResult($query, $ftp_no);
            if ($ftp_no == 2) {
            } elseif ($ftp_no == 3) {
            } elseif ($ftp_no == 4) {
            } elseif ($ftp_no ==7 || $ftp_no ==8) {
            } else {
                $this->equip_log_writer("{$fws_file}：Downloadの再試行で失敗しました。");
            }
            if (file_exists($local_file)) {
                unlink($local_file);
            }
            return false;
        }
    }
    
    ///// ログ収集 状態データあり書込み 処理 メソッド
    protected function equip_log_state_ftp_write($mac_no, $csv_flg, $state_temp, $ftp_con)
    {
        $fp   = fopen($state_temp, 'r');
        $row  = 0;                                  // 全レコード
        $data = array();                            // 年月日,時間,加工数
        while ($data[$row] = fgetcsv($fp, 50, ',')) {
            if ($data[$row][0] == '') continue;     // 先頭フィールドでレコードチェック
            $row++;
        }
        for ($j=0; $j<$row; $j++) {         // Status File にレコードがあれば状態と日時を書込む
            if ($data[$j][2] == 'auto') {   // ファイルから物理状態番号を取得
                $state_p = 1;               // 運転中(自動運転)
            } elseif ($data[$j][2] == 'stop') {
                $state_p = 3;               // 停止中
            } elseif ($data[$j][2] == 'on') {
                $state_p = 3;               // 電源ONの場合のDefault値は停止中=3
            } else {
                $state_p = 0;               // 電源OFF "off"
            }

            $date_time = $data[$j][0] . " " . $data[$j][1];     // timestamp型の変数生成 注意

            // エラーが出るのでテスト 上ではなく、現在の最新時間を最新状態として保管
            //$date_time = date('Ymd His');   // 現在の時間を使う PostgreSQLのTIMESTAMP型に変更
            
            $state_r = $this->getRotaryStateHistory($mac_no, $date_time);// ロータリースイッチの履歴からデータ取得
            $state = $this->equip_state_check($state_p, $state_r);  // 物理状態信号とスイッチの状態で適正値をチェック
            $state_name = equip_machine_state($mac_no, $state, $txt_color, $bg_color);
            ///// 物理信号とロータリースイッチでの状態書込み
            // 前回のデータと違えば書込む
            if ($this->equip_log_require($mac_no, $state, $date_time, 1)) {
                $query = "
                    INSERT INTO
                        equip_mac_state_log
                        (mac_no, state, date_time, state_name, state_type)
                    VALUES
                        ($mac_no, $state, '$date_time', '$state_name', $csv_flg)
                ";
                if (query_affected($query) <= 0) {
                    $msg = "insert error{$query}\n date_time:{$date_time} mac_no:{$mac_no} state:{$state_p} j={$j}";
                    $this->equip_log_writer($msg);
                }
            }
            ///// 物理信号のみの状態書込み(過去と互換性あり)
            // 前回のデータと違えば書込む
            if ($this->equip_log_require($mac_no, $state_p, $date_time, 2)) {
                $state_name = equip_machine_state($mac_no, $state_p, $txt_color, $bg_color);
                $query = "
                    INSERT INTO
                        equip_mac_state_log2
                        (mac_no, state, date_time, state_name, state_type)
                    VALUES
                        ($mac_no, $state_p, '$date_time', '$state_name', $csv_flg)
                ";
                if (query_affected($query) <= 0) {
                    $msg = "insert error{$query}\n date_time:{$date_time} mac_no:{$mac_no} state:{$state_p} j={$j}";
                    $this->equip_log_writer($msg);
                }
            }
        }
        fclose($fp);
        // テスト 1225は正しいものとして取得 テスト解除時はelse側のみ残し
        if ($mac_no=='1224' || $mac_no=='1228' || $mac_no=='1258' || $mac_no=='1225' || $mac_no=='1226' || $mac_no=='1227' || $mac_no=='1228' || $mac_no=='1229' || $mac_no=='1230' || $mac_no=='1233' || $mac_no=='1234' || $mac_no=='1235' || $mac_no=='1257' || $mac_no=='1259' || $mac_no=='2101' || $mac_no=='2103' || $mac_no=='2106' || $mac_no=='2901' || $mac_no=='2902' || $mac_no=='2903' || $mac_no=='6000' || $mac_no=='6001' || $mac_no=='6002' || $mac_no=='6003' || $mac_no=='6004' || $mac_no=='6005' || $mac_no=='1346' || $mac_no=='1347' || $mac_no=='1348' || $mac_no=='1349' || $mac_no=='1364' || $mac_no=='1365' || $mac_no=='1366' || $mac_no=='1367' || $mac_no=='1368' || $mac_no=='1369' || $mac_no=='1372' || $mac_no=='1373' || $mac_no=='1374') {
        } else {
            unlink($state_temp);        // 一時ファイルの削除
        }
    }
    
    ///// ログ収集 状態データなしDBより物理信号取得 書込み 処理 メソッド
    protected function equip_log_state_db_write($mac_no, $csv_flg, $ftp_con)
    {
        ///// State Log file がない場合はロータリースイッチのみチェックする
        // 物理信号をDBより取得
        $state_p = $this->getPhysicalState($mac_no);
        
        $date_time = date('Ymd His');   // 現在の時間を使う PostgreSQLのTIMESTAMP型に変更
        $state_r = $this->getRotaryStateHistory($mac_no, $date_time);// ロータリースイッチの履歴からデータ取得
        $state = $this->equip_state_check($state_p, $state_r);  // 物理状態信号とスイッチの状態で適正値をチェック
        $state_name = equip_machine_state($mac_no, $state, $txt_color, $bg_color);
        ///// 物理信号とロータリースイッチでの状態書込み
        // 前回のデータと違えば書込む
        if ($this->equip_log_require($mac_no, $state, $date_time, 1)) {
            $query = "
                INSERT INTO
                    equip_mac_state_log
                    (mac_no, state, date_time, state_name, state_type)
                VALUES
                    ($mac_no, $state, '$date_time', '$state_name', $csv_flg)
            ";
            if (query_affected($query) <= 0) {
                $msg = "insert error{$query}\n date_time:{$date_time} mac_no:{$mac_no} state:{$state_p} j={$j}";
                $this->equip_log_writer($msg);
            }
        }
    }
    
    ///// ログの収集 加工数の取得 処理メソッド
    protected function equip_log_workCnt($interface, $ftp_con)
    {
        ////////// 稼動中の機械№をヘッダーファイルから取得 & 機械マスターから機械名を取得
        $query = "
            SELECT
                mac_no, siji_no, koutei, parts_no --2007/06/26コメントアウト, plan_cnt, mac_name, csv_flg
            FROM
                equip_work_log2_header
            LEFT OUTER JOIN
                equip_machine_master2
                USING (mac_no)
            WHERE
                work_flg is TRUE
                AND
                csv_flg = {$interface}
                AND
                survey = 'Y'
                {$this->factoryWhere} {$this->macNoWhere}
        ";
            // $interface == Netmoni=1 fwserver1=2 fwserver2=3 fwserver3=4 fwserver4=5 ... , Net&fws1=101
        $res_key = array();
        if ( ($rows_key = getResult($query, $res_key)) >= 1) {      // レコードが１以上あれば
            for ($i=0; $i<$rows_key; $i++) {                        // １レコードずつ処理
                $this->equip_log_workCnt_body($res_key[$i], $ftp_con);
            }
        }
    }
    
    ///// ログの収集 加工数の取得 処理メソッド
    protected function equip_log_workCnt_moni($interface, $ftp_con)
    {
        ////////// 稼動中の機械№をヘッダーファイルから取得 & 機械マスターから機械名を取得
        $query = "
            SELECT
                mac_no, plan_no, koutei, parts_no --2007/06/26コメントアウト, plan_cnt, mac_name, csv_flg
            FROM
                equip_work_log2_header_moni
            LEFT OUTER JOIN
                equip_machine_master2
                USING (mac_no)
            WHERE
                work_flg is TRUE
                AND
                csv_flg = {$interface}
                AND
                survey = 'Y'
                {$this->factoryWhere} {$this->macNoWhere}
        ";
            // $interface == Netmoni=1 fwserver1=2 fwserver2=3 fwserver3=4 fwserver4=5 ... , Net&fws1=101
        $res_key = array();
        if ( ($rows_key = getResult($query, $res_key)) >= 1) {      // レコードが１以上あれば
            for ($i=0; $i<$rows_key; $i++) {                        // １レコードずつ処理
                $this->equip_log_workCnt_body_moni($res_key[$i], $ftp_con);
            }
        }
    }
    
    ///// ログの収集 加工数の取得 本体部 処理メソッド
    protected function equip_log_workCnt_body($res_key, $ftp_con)
    {
        ///// insert 用 変数 初期化
        $mac_no   = $res_key['mac_no'];
        $siji_no  = $res_key['siji_no'];
        $koutei   = $res_key['koutei'];
        ///// ファイル名生成
        $query = "
            SELECT
                interface
            FROM
                equip_machine_interface
            WHERE
                mac_no = {$mac_no}
        ";
        $ftp_no = 0;
        getUniResult($query, $ftp_no);
        /*
        if ($ftp_no == 2) {
            $fws_cnt_log = "/MMC/Plc_Work/{$mac_no}_work_cnt.log";
            $fws_cnt_tmp = "/MMC/Plc_Work/{$mac_no}_work_cnt.tmp";
            $local_cnt = "/home/fws/{$mac_no}_work_cnt.log";
            $cnt_temp  = "/home/fws/{$mac_no}_work_cnt.tmp";    // Rename用 一時ファイル名生成
        } else {
            $fws_cnt_log = "/home/fws/usr/{$mac_no}_work_cnt.log";
            $fws_cnt_tmp = "/home/fws/usr/{$mac_no}_work_cnt.tmp";
            $local_cnt = "/home/fws/{$mac_no}_work_cnt.log";
            $cnt_temp  = "/home/fws/{$mac_no}_work_cnt.tmp";    // Rename用 一時ファイル名生成
        }
        */
        // FWS2 切替準備 上のPGMと入れ替える
        if ($ftp_no == 2) {
            $fws_cnt_log = "/MMC/Plc_Work/{$mac_no}_work_cnt.log";
            $fws_cnt_tmp = "/MMC/Plc_Work/{$mac_no}_work_cnt.tmp";
            $local_cnt = "/home/fws/{$mac_no}_work_cnt.log";
            $cnt_temp  = "/home/fws/{$mac_no}_work_cnt.tmp";    // Rename用 一時ファイル名生成
        } elseif ($ftp_no == 3) {
            $fws_cnt_log = "/0_CARD/Plc_Work/{$mac_no}_work_cnt.log";
            $fws_cnt_tmp = "/0_CARD/Plc_Work/{$mac_no}_work_cnt.tmp";
            $local_cnt = "/home/fws/{$mac_no}_work_cnt.log";
            $cnt_temp  = "/home/fws/{$mac_no}_work_cnt.tmp";    // Rename用 一時ファイル名生成
        } elseif ($ftp_no == 4) {
            $fws_cnt_log = "/0_CARD/Plc_Work/{$mac_no}_work_cnt.log";
            $fws_cnt_tmp = "/0_CARD/Plc_Work/{$mac_no}_work_cnt.tmp";
            $local_cnt = "/home/fws/{$mac_no}_work_cnt.log";
            $cnt_temp  = "/home/fws/{$mac_no}_work_cnt.tmp";    // Rename用 一時ファイル名生成
        } elseif ($ftp_no == 7 || $ftp_no == 8) {
            $fws_cnt_log = "/0_CARD/Plc_Work/{$mac_no}_work_cnt.log";
            $fws_cnt_tmp = "/0_CARD/Plc_Work/{$mac_no}_work_cnt.tmp";
            $local_cnt = "/home/fws/{$mac_no}_work_cnt.log";
            $cnt_temp  = "/home/fws/{$mac_no}_work_cnt.tmp";    // Rename用 一時ファイル名生成
        } else {
            $fws_cnt_log = "/home/fws/usr/{$mac_no}_work_cnt.log";
            $fws_cnt_tmp = "/home/fws/usr/{$mac_no}_work_cnt.tmp";
            $local_cnt = "/home/fws/{$mac_no}_work_cnt.log";
            $cnt_temp  = "/home/fws/{$mac_no}_work_cnt.tmp";    // Rename用 一時ファイル名生成
        }
        ///// 共通 FTP Download 処理
        $this->equip_FTP_Download($fws_cnt_log, $fws_cnt_tmp, $local_cnt, $ftp_con, $mac_no);
        ///// Counter File Check        現在の加工数・日時を取得
        // テスト テスト解除時はelse側のみ残し
        if ($mac_no=='1346' || $mac_no=='1347' || $mac_no=='1348' || $mac_no=='1349' || $mac_no=='1364' || $mac_no=='1365' || $mac_no=='1366' || $mac_no=='1367' || $mac_no=='1368' || $mac_no=='1369' || $mac_no=='1372' || $mac_no=='1373' || $mac_no=='1374') {
            $timestamp = time();
            $cnt_temp  = "/home/fws/第7工場真鍮/{$mac_no}_work_cnt" . $timestamp . ".tmp" ;     // Rename 用ディレクトリ・ファイル名生成
        } elseif ($mac_no=='1224' || $mac_no=='1228' || $mac_no=='1258' || $mac_no=='1225' || $mac_no=='1226' || $mac_no=='1227' || $mac_no=='1228' || $mac_no=='1229' || $mac_no=='1230' || $mac_no=='1233' || $mac_no=='1234' || $mac_no=='1235' || $mac_no=='1257' || $mac_no=='1259') {
            $timestamp = time();
            $cnt_temp  = "/home/fws/第7工場SUS/{$mac_no}_work_cnt" . $timestamp . ".tmp" ;     // Rename 用ディレクトリ・ファイル名生成
        } else {
            $cnt_temp  = "/home/fws/{$mac_no}_work_cnt.tmp";    // Rename用 一時ファイル名生成
        }
        if (file_exists($local_cnt)) {                       // Counter File があれば
            if (rename($local_cnt, $cnt_temp)) {
                $this->equip_log_workCnt_ftp_write($res_key, $cnt_temp);
            } else {
                $this->equip_log_writer("カウンターファイルの rename({$local_cnt}) に失敗");
            }
        } else {                    // Counter File がないので状態のみ書込み
            ///// 状態データは指定日時でDBより抽出する
            $this->equip_log_workCnt_db_write($res_key);
        }
    }
    
    ///// ログの収集 加工数の取得 本体部 処理メソッド
    protected function equip_log_workCnt_body_moni($res_key, $ftp_con)
    {
        ///// insert 用 変数 初期化
        $mac_no   = $res_key['mac_no'];
        $plan_no  = $res_key['plan_no'];
        $koutei   = $res_key['koutei'];
        ///// ファイル名生成
        $query = "
            SELECT
                interface
            FROM
                equip_machine_interface
            WHERE
                mac_no = {$mac_no}
        ";
        $ftp_no = 0;
        getUniResult($query, $ftp_no);
        /*
        if ($ftp_no == 2) {
            $fws_cnt_log = "/MMC/Plc_Work/{$mac_no}_work_cnt.log";
            $fws_cnt_tmp = "/MMC/Plc_Work/{$mac_no}_work_cnt.tmp";
            $local_cnt = "/home/fws/{$mac_no}_work_cnt.log";
            $cnt_temp  = "/home/fws/{$mac_no}_work_cnt.tmp";    // Rename用 一時ファイル名生成
        } else {
            $fws_cnt_log = "/home/fws/usr/{$mac_no}_work_cnt.log";
            $fws_cnt_tmp = "/home/fws/usr/{$mac_no}_work_cnt.tmp";
            $local_cnt = "/home/fws/{$mac_no}_work_cnt.log";
            $cnt_temp  = "/home/fws/{$mac_no}_work_cnt.tmp";    // Rename用 一時ファイル名生成
        }
        */
        // FWS2 切替準備 上のPGMと入れ替える
        if ($ftp_no == 2) {
            $fws_cnt_log = "/MMC/Plc_Work/{$mac_no}_work_cnt.log";
            $fws_cnt_tmp = "/MMC/Plc_Work/{$mac_no}_work_cnt.tmp";
            $local_cnt = "/home/fws/{$mac_no}_work_cnt.log";
            $cnt_temp  = "/home/fws/{$mac_no}_work_cnt.tmp";    // Rename用 一時ファイル名生成
        } elseif ($ftp_no == 3) {
            $fws_cnt_log = "/0_CARD/Plc_Work/{$mac_no}_work_cnt.log";
            $fws_cnt_tmp = "/0_CARD/Plc_Work/{$mac_no}_work_cnt.tmp";
            $local_cnt = "/home/fws/{$mac_no}_work_cnt.log";
            $cnt_temp  = "/home/fws/{$mac_no}_work_cnt.tmp";    // Rename用 一時ファイル名生成
        } elseif ($ftp_no == 4) {
            $fws_cnt_log = "/0_CARD/Plc_Work/{$mac_no}_work_cnt.log";
            $fws_cnt_tmp = "/0_CARD/Plc_Work/{$mac_no}_work_cnt.tmp";
            $local_cnt = "/home/fws/{$mac_no}_work_cnt.log";
            $cnt_temp  = "/home/fws/{$mac_no}_work_cnt.tmp";    // Rename用 一時ファイル名生成
        } elseif ($ftp_no == 7 || $ftp_no == 8) {
            $fws_cnt_log = "/0_CARD/Plc_Work/{$mac_no}_work_cnt.log";
            $fws_cnt_tmp = "/0_CARD/Plc_Work/{$mac_no}_work_cnt.tmp";
            $local_cnt = "/home/fws/{$mac_no}_work_cnt.log";
            $cnt_temp  = "/home/fws/{$mac_no}_work_cnt.tmp";    // Rename用 一時ファイル名生成
        } else {
            $fws_cnt_log = "/home/fws/usr/{$mac_no}_work_cnt.log";
            $fws_cnt_tmp = "/home/fws/usr/{$mac_no}_work_cnt.tmp";
            $local_cnt = "/home/fws/{$mac_no}_work_cnt.log";
            $cnt_temp  = "/home/fws/{$mac_no}_work_cnt.tmp";    // Rename用 一時ファイル名生成
        }
        ///// 共通 FTP Download 処理
        $this->equip_FTP_Download($fws_cnt_log, $fws_cnt_tmp, $local_cnt, $ftp_con, $mac_no);
        // テスト テスト解除時はelse側のみ残し
        if ($mac_no=='2101' || $mac_no=='2103' || $mac_no=='2106' || $mac_no=='2901' || $mac_no=='2902' || $mac_no=='2903' || $mac_no=='6000' || $mac_no=='6001' || $mac_no=='6002' || $mac_no=='6003' || $mac_no=='6004' || $mac_no=='6005') {
            $timestamp = time();
            $cnt_temp  = "/home/fws/第6工場/{$mac_no}_work_cnt" . $timestamp . ".tmp" ;     // Rename 用ディレクトリ・ファイル名生成
        } else {
            $cnt_temp  = "/home/fws/{$mac_no}_work_cnt.tmp";    // Rename用 一時ファイル名生成
        }
        
        ///// Counter File Check        現在の加工数・日時を取得
        if (file_exists($local_cnt)) {                       // Counter File があれば
            if (rename($local_cnt, $cnt_temp)) {
                $this->equip_log_workCnt_ftp_write_moni($res_key, $cnt_temp);
            } else {
                $this->equip_log_writer("カウンターファイルの rename({$local_cnt}) に失敗");
            }
        } else {                    // Counter File がないので状態のみ書込み
            ///// 状態データは指定日時でDBより抽出する
            $this->equip_log_workCnt_db_write_moni($res_key);
        }
    }
    ///// ログの収集 加工数の書込み FTP上にデータありの場合 処理メソッド
    protected function equip_log_workCnt_ftp_write($res_key, $cnt_temp)
    {
        ///// insert 用 変数 初期化
        $mac_no   = $res_key['mac_no'];
        $siji_no  = $res_key['siji_no'];
        $koutei   = $res_key['koutei'];
        $parts_no = $res_key['parts_no'];
        ///// カウンターマスター読込み
        $cntMulti = $this->getCounterMaster($mac_no, $parts_no); // Counter Multiple
        ///// リネームしたカウンターファイル
        $fp = fopen ($cnt_temp,'r');
        $row  = 0;                                  // 全レコード
        $data = array();                            // 年月日,時間,加工数
        while ($data[$row] = fgetcsv ($fp, 50, ',')) {
            if ($data[$row][0] == '') continue;     // 先頭フィールドでレコードチェック
            $row++;
        }
        if ($row >= 1) {            // Counter File にレコードがあれば状態と加工数書込み
            ///// 現在のデータベースの最新レコードを取り込む
            $query = "
                SELECT mac_state, work_cnt FROM equip_work_log2
                WHERE
                    equip_index(mac_no, siji_no, koutei, date_time) < '{$mac_no}{$siji_no}{$koutei}99999999999999'
                    AND
                    equip_index(mac_no, siji_no, koutei, date_time) > '{$mac_no}{$siji_no}{$koutei}00000000000000'
                ORDER BY equip_index(mac_no, siji_no, koutei, date_time) DESC LIMIT 1
            ";
            $res = array();
            $multi_t_num = 0;
            $multi_t_c   = 0;
            if ( ($rows = getResult($query, $res)) >= 1) {  // 前回の加工数にプラスして書込み
                for ($j=0; $j<$row; $j++) {
                    ///// 状態データは指定日時でDBより抽出する
                    $state = $this->getLogicalState($mac_no, $data[$j][0], $data[$j][1]);
                    if ($data[$j][2] == 'cnt') {
                        $work_cnt  = $res[0]['work_cnt'] + (($j + 1 - $multi_t_c) * $cntMulti) + $multi_t_num;  // Counter UP
                    } else {
                        $cntMulti_t = str_replace('cnt','',$data[$j][2]) * 1;
                        $multi_t_num = $multi_t_num + $cntMulti_t;
                        $multi_t_c   = $multi_t_c + 1;
                        $work_cnt  = $res[0]['work_cnt'] + (($j + 1 - $multi_t_c) * $cntMulti) + $multi_t_num;  // Counter UP
                    }
                    $date_time = $data[$j][0] . ' ' . $data[$j][1];     // PostgreSQLのTIMESTAMP型に変更
                    $query = $this->getSQLworkCntInsert($mac_no, $date_time, $state, $work_cnt, $siji_no, $koutei);
                    if (query_affected($query) <= 0) {
                        $this->equip_log_writer("更新 error{$query}");
                    }
                }
            } else {                    // データベースが初回のため無条件に書込み
                for ($j=0; $j<$row; $j++) {
                    ///// 状態データは指定日時でDBより抽出する
                    $state = $this->getLogicalState($mac_no, $data[$j][0], $data[$j][1]);
                    if ($data[$j][2] == 'cnt') {
                        $work_cnt  =  (($j + 1) * $cntMulti - $multi_t_c) + $multi_t_num;   // 初回の場合はここが違う
                    } else {
                        $cntMulti_t = str_replace('cnt','',$data[$j][2]) * 1;
                        $multi_t_num = $multi_t_num + $cntMulti_t;
                        $multi_t_c   = $multi_t_c + 1;
                        $work_cnt  =  (($j + 1) * $cntMulti - $multi_t_c) + $multi_t_num;   // 初回の場合はここが違う
                    }
                    $date_time = $data[$j][0] . ' ' . $data[$j][1];     // PostgreSQLのTIMESTAMP型に変更
                    ///// 初回の場合は過去のデータを取込む可能性が高いため以下は必要
                    $query = $this->getSQLworkCntInsert($mac_no, $date_time, $state, $work_cnt, $siji_no, $koutei);
                    if (query_affected($query) <= 0) {
                        $this->equip_log_writer("初回更新 error{$query}");
                    } else {
                        ///// ログの収集 初回の加工数の書込みにヘッダーとログの日時をチェックして更新
                        $msg = '初回データの日時比較で';
                        $this->equip_log_workCnt_header_write($res_key, $date_time, $msg);
                    }
                }
            }
            // Counter File があるのにレコードがない現象は３年間の実績で１度も無いので、対応ロジックは省略する
        }
        // テスト テスト解除時はelse側のみ残し
        if ($mac_no=='1224' || $mac_no=='1228' || $mac_no=='1258' || $mac_no=='1225' || $mac_no=='1226' || $mac_no=='1227' || $mac_no=='1228' || $mac_no=='1229' || $mac_no=='1230' || $mac_no=='1233' || $mac_no=='1234' || $mac_no=='1235' || $mac_no=='1257' || $mac_no=='1259' || $mac_no=='2101' || $mac_no=='2103' || $mac_no=='2106' || $mac_no=='2901' || $mac_no=='2902' || $mac_no=='2903' || $mac_no=='6000' || $mac_no=='6001' || $mac_no=='6002' || $mac_no=='6003' || $mac_no=='6004' || $mac_no=='6005' || $mac_no=='1346' || $mac_no=='1347' || $mac_no=='1348' || $mac_no=='1349' || $mac_no=='1364' || $mac_no=='1365' || $mac_no=='1366' || $mac_no=='1367' || $mac_no=='1368' || $mac_no=='1369' || $mac_no=='1372' || $mac_no=='1373' || $mac_no=='1374') {
        } else {
        unlink($cnt_temp);      // 一時ファイルの削除
        }
    }
    ///// ログの収集 加工数の書込み FTP上にデータありの場合 処理メソッド
    protected function equip_log_workCnt_ftp_write_moni($res_key, $cnt_temp)
    {
        ///// insert 用 変数 初期化
        $mac_no   = $res_key['mac_no'];
        $plan_no  = $res_key['plan_no'];
        $koutei   = $res_key['koutei'];
        $parts_no = $res_key['parts_no'];
        ///// カウンターマスター読込み
        $cntMulti = $this->getCounterMaster($mac_no, $parts_no); // Counter Multiple
        ///// リネームしたカウンターファイル
        $fp = fopen ($cnt_temp,'r');
        $row  = 0;                                  // 全レコード
        $data = array();                            // 年月日,時間,加工数
        while ($data[$row] = fgetcsv ($fp, 50, ',')) {
            if ($data[$row][0] == '') continue;     // 先頭フィールドでレコードチェック
            $row++;
        }
        if ($row >= 1) {            // Counter File にレコードがあれば状態と加工数書込み
            ///// 現在のデータベースの最新レコードを取り込む
            $query = "
                SELECT mac_state, work_cnt FROM equip_work_log2_moni
                WHERE
                    equip_index_moni(mac_no, plan_no, koutei, date_time) < '{$mac_no}{$plan_no}{$koutei}99999999999999'
                    AND
                    equip_index_moni(mac_no, plan_no, koutei, date_time) > '{$mac_no}{$plan_no}{$koutei}00000000000000'
                ORDER BY equip_index_moni(mac_no, plan_no, koutei, date_time) DESC LIMIT 1
            ";
            $res = array();
            $multi_t_num = 0;
            $multi_t_c   = 0;
            if ( ($rows = getResult($query, $res)) >= 1) {  // 前回の加工数にプラスして書込み
                for ($j=0; $j<$row; $j++) {
                    ///// 状態データは指定日時でDBより抽出する
                    $state = $this->getLogicalState($mac_no, $data[$j][0], $data[$j][1]);
                    if ($data[$j][2] == 'cnt') {
                        $work_cnt  = $res[0]['work_cnt'] + (($j + 1 - $multi_t_c) * $cntMulti) + $multi_t_num;  // Counter UP
                    } else {
                        $cntMulti_t = str_replace('cnt','',$data[$j][2]) * 1;
                        $multi_t_num = $multi_t_num + $cntMulti_t;
                        $multi_t_c   = $multi_t_c + 1;
                        $work_cnt  = $res[0]['work_cnt'] + (($j + 1 - $multi_t_c) * $cntMulti) + $multi_t_num;  // Counter UP
                    }
                    $date_time = $data[$j][0] . ' ' . $data[$j][1];     // PostgreSQLのTIMESTAMP型に変更
                    $query = $this->getSQLworkCntInsert_moni($mac_no, $date_time, $state, $work_cnt, $plan_no, $koutei);
                    if (query_affected($query) <= 0) {
                        $this->equip_log_writer("更新 error{$query}");
                    }
                }
            } else {                    // データベースが初回のため無条件に書込み
                for ($j=0; $j<$row; $j++) {
                    ///// 状態データは指定日時でDBより抽出する
                    $state = $this->getLogicalState($mac_no, $data[$j][0], $data[$j][1]);
                    if ($data[$j][2] == 'cnt') {
                        $work_cnt  =  (($j + 1 - $multi_t_c) * $cntMulti) + $multi_t_num;   // 初回の場合はここが違う
                    } else {
                        $cntMulti_t = str_replace('cnt','',$data[$j][2]) * 1;
                        $multi_t_num = $multi_t_num + $cntMulti_t;
                        $multi_t_c   = $multi_t_c + 1;
                        $work_cnt  =  (($j + 1 - $multi_t_c) * $cntMulti) + $multi_t_num;   // 初回の場合はここが違う
                    }
                    $date_time = $data[$j][0] . ' ' . $data[$j][1];     // PostgreSQLのTIMESTAMP型に変更
                    ///// 初回の場合は過去のデータを取込む可能性が高いため以下は必要
                    $query = $this->getSQLworkCntInsert_moni($mac_no, $date_time, $state, $work_cnt, $plan_no, $koutei);
                    if (query_affected($query) <= 0) {
                        $this->equip_log_writer("初回更新 error{$query}");
                    } else {
                        ///// ログの収集 初回の加工数の書込みにヘッダーとログの日時をチェックして更新
                        $msg = '初回データの日時比較で';
                        $this->equip_log_workCnt_header_write_moni($res_key, $date_time, $msg);
                    }
                }
            }
            // Counter File があるのにレコードがない現象は３年間の実績で１度も無いので、対応ロジックは省略する
        }
        if ($mac_no=='2101' || $mac_no=='2103' || $mac_no=='2106' || $mac_no=='2901' || $mac_no=='2902' || $mac_no=='2903' || $mac_no=='6000' || $mac_no=='6001' || $mac_no=='6002' || $mac_no=='6003' || $mac_no=='6004' || $mac_no=='6005') {
        } else {
        unlink($cnt_temp);      // 一時ファイルの削除
        }
    }
    ///// 加工数の書込み SQL文の生成 FTP上にデータありの場合 処理メソッド
    protected function getSQLworkCntInsert($mac_no, $date_time, $state, $work_cnt, $siji_no, $koutei)
    {
        if ( ($state == 1) || ($state == 8) || ($state == 5) ) {    // 自動運転か無人運転又は段取中のはずなのでチェックして必要なら訂正
            $mac_state = $state;
        } else {
            // Counterが進んでいるので自動又は無人に強制的に設定する
            if ($this->getRotaryStateHistory($mac_no, $date_time) == 8) {
                $mac_state = 8;
            } else {
                $mac_state = 1;
            }
        }
        // 重複チェック
        $query = "
            SELECT work_cnt FROM equip_work_log2 WHERE date_time='{$date_time}' AND mac_no={$mac_no} AND mac_state={$mac_state}
        ";
        if (getUniResult($query, $check) < 1) {
            $sql = "
                INSERT INTO
                    equip_work_log2
                (mac_no, date_time, mac_state, work_cnt, siji_no, koutei)
                VALUES($mac_no, '$date_time', $mac_state, $work_cnt, $siji_no, $koutei)
            ";
        } else {
            $sql = "
                UPDATE equip_work_log2 SET work_cnt={$work_cnt}
                WHERE date_time='{$date_time}' AND mac_no={$mac_no} AND mac_state={$mac_state}
            ";
            $this->equip_log_writer("キーが重複しているためUPDATEします。{$query}{$sql}");
        }
        return $sql;
    }
    
    ///// 加工数の書込み SQL文の生成 FTP上にデータありの場合 処理メソッド
    protected function getSQLworkCntInsert_moni($mac_no, $date_time, $state, $work_cnt, $plan_no, $koutei)
    {
        if ( ($state == 1) || ($state == 8) || ($state == 5) ) {    // 自動運転か無人運転又は段取中のはずなのでチェックして必要なら訂正
            $mac_state = $state;
        } else {
            // Counterが進んでいるので自動又は無人に強制的に設定する
            if ($this->getRotaryStateHistory($mac_no, $date_time) == 8) {
                $mac_state = 8;
            } else {
                $mac_state = 1;
            }
        }
        // 重複チェック
        $query = "
            SELECT work_cnt FROM equip_work_log2_moni WHERE date_time='{$date_time}' AND mac_no={$mac_no} AND mac_state={$mac_state}
        ";
        if (getUniResult($query, $check) < 1) {
            $sql = "
                INSERT INTO
                    equip_work_log2_moni
                (mac_no, date_time, mac_state, work_cnt, plan_no, koutei)
                VALUES($mac_no, '$date_time', $mac_state, $work_cnt, '$plan_no', $koutei)
            ";
        } else {
            $sql = "
                UPDATE equip_work_log2_moni SET work_cnt={$work_cnt}
                WHERE date_time='{$date_time}' AND mac_no={$mac_no} AND mac_state={$mac_state}
            ";
            $this->equip_log_writer("キーが重複しているためUPDATEします。{$query}{$sql}");
        }
        return $sql;
    }
    ///// ログの収集 加工数の書込み FTP上にデータなしの場合 処理メソッド
    protected function equip_log_workCnt_db_write($res_key)
    {
        ///// insert 用 変数 初期化
        $mac_no   = $res_key['mac_no'];
        $siji_no  = $res_key['siji_no'];
        $koutei   = $res_key['koutei'];
        $date_t1  = date('Ymd');
        $date_t2  = date('His');
        ///// 状態データは指定日時でDBより抽出する
        $state = $this->getLogicalState($mac_no, $date_t1, $date_t2);
        ///// 時間は指定日時でDBより抽出する
        $date_time = $this->getLogicalTime($mac_no, $date_t1, $date_t2);
        ///// 現在のデータベースの最新レコードを取り込む
        $query = "
            SELECT mac_state, work_cnt, date_time FROM equip_work_log2
            WHERE
                equip_index(mac_no, siji_no, koutei, date_time) < '{$mac_no}{$siji_no}{$koutei}99999999999999'
                AND
                equip_index(mac_no, siji_no, koutei, date_time) > '{$mac_no}{$siji_no}{$koutei}00000000000000'
            ORDER BY equip_index(mac_no, siji_no, koutei, date_time) DESC LIMIT 1
        ";
        $res = array();
        if ($date_time != '') {
            if ( ($rows = getResult($query, $res)) >= 1) {  // レコードがあれば状態をチェックする
                ///// 停止の定義マスターをチェックして状態が違えば書込む
                if ($this->checkStopTime($res[0]['mac_state'], $res[0]['date_time'], $state, $res_key, $date_time)) {
                    $work_cnt  = $res[0]['work_cnt'];       // 前回の加工数をそのまま使う
                    //$date_time  = date('Ymd His');          // 現在の時間を使う PostgreSQLのTIMESTAMP型に変更
                    $mac_state = $state;
                    // 重複チェック
                    $query = "
                        SELECT work_cnt FROM equip_work_log2 WHERE date_time='{$date_time}' AND mac_no={$mac_no} AND mac_state={$mac_state}
                    ";
                    if (getUniResult($query, $check) < 1) {
                        $query = "
                            INSERT INTO
                                equip_work_log2
                            (mac_no, date_time, mac_state, work_cnt, siji_no, koutei)
                            VALUES($mac_no, '$date_time', $mac_state, $work_cnt, $siji_no, $koutei)
                        ";
                        if (query_affected($query) <= 0) {
                            $this->equip_log_writer("状態変化時のinsert error{$query}");
                        }
                    }
                }
            } else {        // 初回のため無条件に書込む
                $work_cnt  = 0;             // 初回の場合は０
                //$date_time = date('Ymd His');       // 現在の時間を使う PostgreSQLのTIMESTAMP型に変更
                $mac_state = $state;
                $query = "
                    SELECT work_cnt FROM equip_work_log2 WHERE date_time='{$date_time}' AND mac_no={$mac_no} AND mac_state={$mac_state}
                ";
                $res = array();
                if (getUniResult($query, $check) < 1) {
                    $query = "
                        INSERT INTO
                            equip_work_log2
                        (mac_no, date_time, mac_state, work_cnt, siji_no, koutei)
                        VALUES($mac_no, '$date_time', $mac_state, $work_cnt, $siji_no, $koutei)
                    ";
                    if (query_affected($query) <= 0) {
                        $this->equip_log_writer("1初回の状態変化時のinsert error{$query}");
                    } else {
                        ///// ログの収集 初回の加工数の書込みにヘッダーとログの日時をチェックして更新
                        $msg = 'CounterFileがない場合の初回データの日時比較で';
                        $this->equip_log_workCnt_header_write($res_key, $date_time, $msg);
                    }
                } else {
                    $query = "
                        UPDATE
                            equip_work_log2
                        SET work_cnt={$work_cnt}, siji_no={$siji_no}, koutei={$koutei}
                        WHERE
                        mac_no={$mac_no} and date_time='{$date_time}' and mac_state={$mac_state}
                    ";
                    if (query_affected($query) <= 0) {
                        $this->equip_log_writer("初回の状態変化時のupdate error{$query}");
                    } else {
                        ///// ログの収集 初回の加工数の書込みにヘッダーとログの日時をチェックして更新
                        $msg = 'CounterFileがない場合の初回データの日時比較で';
                        $this->equip_log_workCnt_header_write($res_key, $date_time, $msg);
                    }
                }
            }
        }
    }
    
    ///// ログの収集 加工数の書込み FTP上にデータなしの場合 処理メソッド
    protected function equip_log_workCnt_db_write_moni($res_key)
    {
        ///// insert 用 変数 初期化
        $mac_no   = $res_key['mac_no'];
        $plan_no  = $res_key['plan_no'];
        $koutei   = $res_key['koutei'];
        $date_t1  = date('Ymd');
        $date_t2  = date('His');
        ///// 状態データは指定日時でDBより抽出する
        $state = $this->getLogicalState($mac_no, $date_t1, $date_t2);
        ///// 時間は指定日時でDBより抽出する
        $date_time = $this->getLogicalTime($mac_no, $date_t1, $date_t2);
        ///// 現在のデータベースの最新レコードを取り込む
        $query = "
            SELECT mac_state, work_cnt, date_time FROM equip_work_log2_moni
            WHERE
                equip_index_moni(mac_no, plan_no, koutei, date_time) < '{$mac_no}{$plan_no}{$koutei}99999999999999'
                AND
                equip_index_moni(mac_no, plan_no, koutei, date_time) > '{$mac_no}{$plan_no}{$koutei}00000000000000'
            ORDER BY equip_index_moni(mac_no, plan_no, koutei, date_time) DESC LIMIT 1
        ";
        $res = array();
        if ($date_time != '') {
            if ( ($rows = getResult($query, $res)) >= 1) {  // レコードがあれば状態をチェックする
                ///// 停止の定義マスターをチェックして状態が違えば書込む
                if ($this->checkStopTime($res[0]['mac_state'], $res[0]['date_time'], $state, $res_key, $date_time)) {
                    $work_cnt  = $res[0]['work_cnt'];       // 前回の加工数をそのまま使う
                    //$date_time  = date('Ymd His');          // 現在の時間を使う PostgreSQLのTIMESTAMP型に変更
                    $mac_state = $state;
                    // 重複チェック
                    $query = "
                        SELECT work_cnt FROM equip_work_log2_moni WHERE date_time='{$date_time}' AND mac_no={$mac_no} AND mac_state={$mac_state}
                    ";
                    if (getUniResult($query, $check) < 1) {
                        $query = "
                           INSERT INTO
                                equip_work_log2_moni
                            (mac_no, date_time, mac_state, work_cnt, plan_no, koutei)
                            VALUES($mac_no, '$date_time', $mac_state, $work_cnt, '$plan_no', $koutei)
                        ";
                        if (query_affected($query) <= 0) {
                            $this->equip_log_writer("状態変化時のinsert error{$query}");
                        }
                    }
                }
            } else {        // 初回のため無条件に書込む
                $work_cnt  = 0;             // 初回の場合は０
                //$date_time = date('Ymd His');       // 現在の時間を使う PostgreSQLのTIMESTAMP型に変更
                $mac_state = $state;
                $query = "
                    INSERT INTO
                        equip_work_log2_moni
                    (mac_no, date_time, mac_state, work_cnt, plan_no, koutei)
                    VALUES($mac_no, '$date_time', $mac_state, $work_cnt, '$plan_no', $koutei)
                ";
                if (query_affected($query) <= 0) {
                    $this->equip_log_writer("2初回の状態変化時のinsert error{$query}");
                } else {
                    ///// ログの収集 初回の加工数の書込みにヘッダーとログの日時をチェックして更新
                    $msg = 'CounterFileがない場合の初回データの日時比較で';
                    $this->equip_log_workCnt_header_write_moni($res_key, $date_time, $msg);
                }
            }
        }
    }
    ///// ログの収集 初回の加工数の書込みにヘッダーとログの日時をチェックして更新 処理メソッド
    protected function equip_log_workCnt_header_write($res_key, $date_time, $msg)
    {
        ///// UPDATE 用 変数 初期化
        $mac_no   = $res_key['mac_no'];
        $siji_no  = $res_key['siji_no'];
        $koutei   = $res_key['koutei'];
        $query = "
            SELECT
                str_timestamp
            FROM
                equip_work_log2_header
            WHERE
                mac_no={$mac_no} AND siji_no={$siji_no} AND koutei={$koutei}
        ";
        if (getUniResult($query, $str_timestamp) > 0) {
            $query = "
                SELECT
                    CASE
                        WHEN CAST('$date_time' AS TIMESTAMP) < CAST('$str_timestamp' AS TIMESTAMP)
                        THEN 1
                        ELSE 0
                    END
            ";
            if (getUniResult($query, $check_time) > 0 && $check_time == 1) {
                $query = "
                    UPDATE
                        equip_work_log2_header
                    SET
                        str_timestamp='{$date_time}'
                    WHERE
                    mac_no={$mac_no} AND siji_no={$siji_no} AND koutei={$koutei}
                ";
                if (query_affected($query) <= 0) {
                    $this->equip_log_writer("{$msg}HeaderのUPDATE error{$query}");
                }
            }
        }
    }
    
    ///// ログの収集 初回の加工数の書込みにヘッダーとログの日時をチェックして更新 処理メソッド
    protected function equip_log_workCnt_header_write_moni($res_key, $date_time, $msg)
    {
        ///// UPDATE 用 変数 初期化
        $mac_no   = $res_key['mac_no'];
        $plan_no  = $res_key['plan_no'];
        $koutei   = $res_key['koutei'];
        $query = "
            SELECT
                str_timestamp
            FROM
                equip_work_log2_header_moni
            WHERE
                mac_no={$mac_no} AND plan_no='{$plan_no}' AND koutei={$koutei}
        ";
        if (getUniResult($query, $str_timestamp) > 0) {
            $query = "
                SELECT
                    CASE
                        WHEN CAST('$date_time' AS TIMESTAMP) < CAST('$str_timestamp' AS TIMESTAMP)
                        THEN 1
                        ELSE 0
                    END
            ";
            if (getUniResult($query, $check_time) > 0 && $check_time == 1) {
                $query = "
                    UPDATE
                        equip_work_log2_header_moni
                    SET
                        str_timestamp='{$date_time}'
                    WHERE
                    mac_no={$mac_no} AND plan_no='{$plan_no}' AND koutei={$koutei}
                ";
                if (query_affected($query) <= 0) {
                    $this->equip_log_writer("{$msg}HeaderのUPDATE error{$query}");
                }
            }
        }
    }
    ///// ロータリースイッチのデータ取得メソッド
    protected function getRotaryStateCurrent($ftp_con, $mac_no)
    {
        ///// 既に実行しているか
        if ($this->mac_no == $mac_no) {
            return $this->rotaryState;
        }
        ///// ファイル名の生成
        $query = "
            SELECT
                interface
            FROM
                equip_machine_interface
            WHERE
                mac_no = {$mac_no}
        ";
        $ftp_no = 0;
        getUniResult($query, $ftp_no);
        /*
        if ($ftp_no == 2) {
            $fws_bcd1 = "/MMC/Plc_Work/{$mac_no}-bcd1";
            $fws_bcd2 = "/MMC/Plc_Work/{$mac_no}-bcd2";
            $fws_bcd4 = "/MMC/Plc_Work/{$mac_no}-bcd4";
            $fws_bcd8 = "/MMC/Plc_Work/{$mac_no}-bcd8";
            $local_bcd1 = "/home/fws/{$mac_no}-bcd1";
            $local_bcd2 = "/home/fws/{$mac_no}-bcd2";
            $local_bcd4 = "/home/fws/{$mac_no}-bcd4";
            $local_bcd8 = "/home/fws/{$mac_no}-bcd8";
        } else {
            $fws_bcd1 = "/home/fws/usr/{$mac_no}-bcd1";
            $fws_bcd2 = "/home/fws/usr/{$mac_no}-bcd2";
            $fws_bcd4 = "/home/fws/usr/{$mac_no}-bcd4";
            $fws_bcd8 = "/home/fws/usr/{$mac_no}-bcd8";
            $local_bcd1 = "/home/fws/{$mac_no}-bcd1";
            $local_bcd2 = "/home/fws/{$mac_no}-bcd2";
            $local_bcd4 = "/home/fws/{$mac_no}-bcd4";
            $local_bcd8 = "/home/fws/{$mac_no}-bcd8";
        }
        */
        // FWS2切替準備 上のPGMと入れ替え
        if ($ftp_no == 2) {
            $fws_bcd1 = "/MMC/Plc_Work/{$mac_no}-bcd1";
            $fws_bcd2 = "/MMC/Plc_Work/{$mac_no}-bcd2";
            $fws_bcd4 = "/MMC/Plc_Work/{$mac_no}-bcd4";
            $fws_bcd8 = "/MMC/Plc_Work/{$mac_no}-bcd8";
            $local_bcd1 = "/home/fws/{$mac_no}-bcd1";
            $local_bcd2 = "/home/fws/{$mac_no}-bcd2";
            $local_bcd4 = "/home/fws/{$mac_no}-bcd4";
            $local_bcd8 = "/home/fws/{$mac_no}-bcd8";
        } elseif ($ftp_no == 3) {
            $fws_bcd1 = "/0_CARD/Plc_Work/{$mac_no}-bcd1";
            $fws_bcd2 = "/0_CARD/Plc_Work/{$mac_no}-bcd2";
            $fws_bcd4 = "/0_CARD/Plc_Work/{$mac_no}-bcd4";
            $fws_bcd8 = "/0_CARD/Plc_Work/{$mac_no}-bcd8";
            $local_bcd1 = "/home/fws/{$mac_no}-bcd1";
            $local_bcd2 = "/home/fws/{$mac_no}-bcd2";
            $local_bcd4 = "/home/fws/{$mac_no}-bcd4";
            $local_bcd8 = "/home/fws/{$mac_no}-bcd8";
        } elseif ($ftp_no == 4) {
            $fws_bcd1 = "/0_CARD/Plc_Work/{$mac_no}-bcd1";
            $fws_bcd2 = "/0_CARD/Plc_Work/{$mac_no}-bcd2";
            $fws_bcd4 = "/0_CARD/Plc_Work/{$mac_no}-bcd4";
            $fws_bcd8 = "/0_CARD/Plc_Work/{$mac_no}-bcd8";
            $local_bcd1 = "/home/fws/{$mac_no}-bcd1";
            $local_bcd2 = "/home/fws/{$mac_no}-bcd2";
            $local_bcd4 = "/home/fws/{$mac_no}-bcd4";
            $local_bcd8 = "/home/fws/{$mac_no}-bcd8";
        } elseif ($ftp_no ==7 || $ftp_no ==8) {
            $fws_bcd1 = "/0_CARD/Plc_Work/{$mac_no}-bcd1";
            $fws_bcd2 = "/0_CARD/Plc_Work/{$mac_no}-bcd2";
            $fws_bcd4 = "/0_CARD/Plc_Work/{$mac_no}-bcd4";
            $fws_bcd8 = "/0_CARD/Plc_Work/{$mac_no}-bcd8";
            $local_bcd1 = "/home/fws/{$mac_no}-bcd1";
            $local_bcd2 = "/home/fws/{$mac_no}-bcd2";
            $local_bcd4 = "/home/fws/{$mac_no}-bcd4";
            $local_bcd8 = "/home/fws/{$mac_no}-bcd8";
        } else {
            $fws_bcd1 = "/home/fws/usr/{$mac_no}-bcd1";
            $fws_bcd2 = "/home/fws/usr/{$mac_no}-bcd2";
            $fws_bcd4 = "/home/fws/usr/{$mac_no}-bcd4";
            $fws_bcd8 = "/home/fws/usr/{$mac_no}-bcd8";
            $local_bcd1 = "/home/fws/{$mac_no}-bcd1";
            $local_bcd2 = "/home/fws/{$mac_no}-bcd2";
            $local_bcd4 = "/home/fws/{$mac_no}-bcd4";
            $local_bcd8 = "/home/fws/{$mac_no}-bcd8";
        }
        $this->getRotaryStateBody($ftp_con, $mac_no, $fws_bcd1, $local_bcd1);
        $this->getRotaryStateBody($ftp_con, $mac_no, $fws_bcd2, $local_bcd2);
        $this->getRotaryStateBody($ftp_con, $mac_no, $fws_bcd4, $local_bcd4);
        $this->getRotaryStateBody($ftp_con, $mac_no, $fws_bcd8, $local_bcd8);
        
        ///// State File Check BCD演算  現在の状態を取得
        $state_bcd = 0;                                     // 初期化
        if (file_exists($local_bcd1)) {
            $state_bcd += 1;
            $this->setRotaryStateCurrentBCD($mac_no, 'BCD1', 1);
        } else {
            $this->setRotaryStateCurrentBCD($mac_no, 'BCD1', 0);
        }
        if (file_exists($local_bcd2)) {
            $state_bcd += 2;
            $this->setRotaryStateCurrentBCD($mac_no, 'BCD2', 2);
        } else {
            $this->setRotaryStateCurrentBCD($mac_no, 'BCD2', 0);
        }
        if (file_exists($local_bcd4)) {
            $state_bcd += 4;
            $this->setRotaryStateCurrentBCD($mac_no, 'BCD4', 4);
        } else {
            $this->setRotaryStateCurrentBCD($mac_no, 'BCD4', 0);
        }
        if (file_exists($local_bcd8)) {
            $state_bcd += 8;
            $this->setRotaryStateCurrentBCD($mac_no, 'BCD8', 8);
        } else {
            $this->setRotaryStateCurrentBCD($mac_no, 'BCD8', 0);
        }
        ///// 繰返し呼出される場合の対応
        $this->mac_no = $mac_no;
        $this->rotaryState = $state_bcd;
        return $state_bcd;
    }
    ///// ロータリースイッチのデータ取得 本体 メソッド
    protected function getRotaryStateBody($ftp_con, $mac_no, $fws_file, $local_file)
    {
        ///// ファイル名の生成
        $query = "
            SELECT
                interface
            FROM
                equip_machine_interface
            WHERE
                mac_no = {$mac_no}
        ";
        $ftp_no = 0;
        getUniResult($query, $ftp_no);
        if ($ftp_no == 2) {
            if (!@ftp_get($ftp_con, $local_file, $fws_file, FTP_ASCII)) {
                if (file_exists($local_file)) {   // 旧ファイルがあれば削除
                    unlink($local_file);
                }
            }
        // FWS2 切替準備 コメント解除してPGM追加
        } elseif ($ftp_no == 3) {
            if (!@ftp_get($ftp_con, $local_file, $fws_file, FTP_ASCII)) {
                if (file_exists($local_file)) {   // 旧ファイルがあれば削除
                    unlink($local_file);
                }
            }
        // FWS2 切替準備 コメント解除してPGM追加
        } elseif ($ftp_no == 4) {
            if (!@ftp_get($ftp_con, $local_file, $fws_file, FTP_ASCII)) {
                if (file_exists($local_file)) {   // 旧ファイルがあれば削除
                    unlink($local_file);
                }
            }
        } elseif ($ftp_no ==7 || $ftp_no ==8) {
            if (!@ftp_get($ftp_con, $local_file, $fws_file, FTP_ASCII)) {
                if (file_exists($local_file)) {   // 旧ファイルがあれば削除
                    unlink($local_file);
                }
            }
        } else {
            /////////// FTP上のファイルの存在チェック
            if (ftp_size($ftp_con, $fws_file) != -1) {
                /////////// FTP Download
                if (!@ftp_get($ftp_con, $local_file, $fws_file, FTP_ASCII)) {
                    $this->equip_log_writer("{$fws_file}：FTPのDownloadに失敗しました。");
                    $this->getRotaryStateRetry($ftp_con, $mac_no, $fws_file, $local_file);
                }
            } else {
                if (file_exists($local_file)) {   // 旧ファイルがあれば削除
                    unlink($local_file);
                }
            }
        }
    }
    ///// ロータリースイッチのデータ取得 再試行 メソッド
    protected function getRotaryStateRetry($ftp_con, $mac_no, $fws_file, $local_file)
    {
        // $this->equip_log_writer("{$fws_file}：FTPのDownloadを再試行します。");
        if (!@ftp_get($ftp_con, $local_file, $fws_file, FTP_ASCII)) {
            $query = "
                SELECT
                interface
                FROM
                equip_machine_interface
                WHERE
                mac_no = {$mac_no}
            ";
            $ftp_no = 0;
            getUniResult($query, $ftp_no);
            if ($ftp_no == 2) {
            } elseif ($ftp_no == 3) {
            } elseif ($ftp_no == 4) {
            } elseif ($ftp_no == 7 || $ftp_no == 8) {
            } else {
                $this->equip_log_writer("{$fws_file}：FTPのDownloadの再試行で失敗しました。");
            }   
        } else {
            $this->equip_log_writer("{$fws_file}：FTPのDownloadの再試行で成功しました。");
        }
    }
    
    ///// ロータリースイッチの最新データ設定メソッド
    protected function setRotaryStateCurrentBCD($mac_no, $bcd, $state, $date_time='')
    {
        $bcd = strtoupper($bcd);
        if ($date_time == '') {
            $date_time = date('Y-m-d H:i:s');
            $msg = 'チェック時';
        } else {
            $msg = '履歴書込み時';
        }
        $query = "SELECT state FROM equip_mac_state_bcd WHERE mac_no={$mac_no} AND bcd='{$bcd}'";
        if (getUniResult($query, $res) < 1) {
            $sql = "
                INSERT INTO equip_mac_state_bcd (mac_no, bcd, state, date_time)
                VALUES({$mac_no}, '{$bcd}', {$state}, '{$date_time}')
            ";
            if (query_affected($sql) <= 0) {
                $this->equip_log_writer("{$mac_no} {$msg}のカレントBCDのinsert error{$sql}");
            } else {
                $this->equip_log_writer("{$mac_no} {$msg}にカレント {$bcd} を {$state} に設定しました。");
            }
        } else {
            if ($state != $res) {
                $sql = "
                    UPDATE equip_mac_state_bcd SET state={$state}, date_time='{$date_time}'
                    WHERE mac_no={$mac_no} AND bcd='{$bcd}'
                ";
                if (query_affected($sql) <= 0) {
                    $this->equip_log_writer("{$mac_no} {$msg}のカレントBCDのupdate error{$sql}");
                } else {
                    $this->equip_log_writer("{$mac_no} {$msg}にカレント {$bcd} を {$state} に変更しました。");
                }
            }
        }
    }
    ///// ロータリースイッチ 各BCDの現在データ取得メソッド
    protected function getRotaryStateCurrentBCD($mac_no, &$bcd1, &$bcd2, &$bcd4, &$bcd8)
    {
        $query = "SELECT state FROM equip_mac_state_bcd WHERE mac_no={$mac_no} ORDER BY mac_no ASC, bcd ASC";
        if (getResult2($query, $res) < 1) {
            $bcd1 = 0; $bcd2 = 0; $bcd4 = 0; $bcd8 = 0;
            $this->equip_log_writer("{$mac_no} のカレントBCDのデータが無いため 0 で初期化しました。");
        } else {
            $bcd1 = $res[0][0]; $bcd2 = $res[1][0]; $bcd4 = $res[2][0]; $bcd8 = $res[3][0];
        }
    }
    
    ///// ロータリースイッチの履歴データ取得メソッド
    protected function getRotaryStateHistory($mac_no, $date_time)
    {
        $query = "
            SELECT
                state
            FROM
                equip_mac_state_log3
            WHERE
                mac_no = {$mac_no} AND date_time <= TIMESTAMP '{$date_time}'
                AND
                mac_no = {$mac_no} AND date_time >= TIMESTAMP '2000-10-01 08:30:00'
            ORDER BY
                mac_no DESC, date_time DESC
            LIMIT 1
        ";
        if (getUniResult($query, $state_r) < 1) {
            // 初回はロータリースイッチが回されていないため自動運転と見なす
            $state_r = 1;
        }
        return $state_r;
    }
    
    /////// 物理信号でロータリースイッチの適正を判断し、状態番号を返す
    /////// 以下はハード信号が正確に出力されている事を前提とする
    protected function equip_state_check($state_p, $state_bcd)
    {
        if ($state_p == 1) {            // 運転中(物理信号)
            switch ($state_bcd) {
            case (1):                   // 自動運転
                return(1);
                break;
            case (4):                   // 暖機中
                return(4);
                break;
            case (5):                   // 段取中
                return(5);
                break;
            case (8):                   // 無人運転
                return(8);
                break;
            default:                    // その他は自動運転
                return(1);
            }
        } elseif ($state_p == 3) {      // 停止中(物理信号)
            switch ($state_bcd) {
            case (3):                   // 停止中
                return(3);
                break;
            case (2):                   // アラーム(物理信号が無いためロータリースイッチで取る)
                return(2);
                break;
            case (4):                   // 暖機中 2007/07/11 (過去2006/06/12追加したのが外れていた)
                return(4);
                break;
            case (5):                   // 段取中
                return(5);
                break;
            case (6):                   // 故障修理
                return(6);
                break;
            case (7):                   // 刃具交換
                return(7);
                break;
            case (9):                   // 中断
                return(9);
                break;
            default:                    // その他は停止中
                return(3);
            }
        } elseif ($state_p == 2) {      // アラーム(物理信号)現在まだ信号はない(予定)
            switch ($state_bcd) {
            case (2):                   // アラーム
                return(2);
                break;
            case (5):                   // 段取中
                return(5);
                break;
            case (6):                   // 故障修理
                return(6);
                break;
            case (7):                   // 刃具交換
                return(7);
                break;
            default:
                return(3);
            }
        } else {                        // 電源OFF(物理信号)
            switch ($state_bcd) {
            case (5):                   // 段取中
                return(5);
                break;
            case (6):                   // 故障修理
                return(6);
                break;
            default:                    // その他は電源OFF
                return(0);
            }
        }
    }
    ///// 前回のデータと違うかチェック equip_mac_state_log equip_mac_state_log2 が対象
    protected function equip_log_require($mac_no, $state, $date_time, $flg)
    {
        if ($flg == 1) {
            if ($this->getLogicalSame($mac_no, $state, $date_time)) {
                return FALSE;
            }
            $state_pre = $this->getLogicalState($mac_no);
            if ($state_pre == $state) {
                return FALSE;
            } else {
                $time_pre = $this->getLogicalTime($mac_no);
                if ($time_pre == $date_time) {
                    return FALSE;
                } else {
                    return TRUE;
                }
                return TRUE;
            }
        } elseif ($flg == 2) {
            if ($this->getPhysicalSame($mac_no, $state, $date_time)) {
                return FALSE;
            }
            $state_pre = $this->getPhysicalState($mac_no);
            if ($state_pre == $state) {
                return FALSE;
            } else {
                $time_pre = $this->getPhysicalTime($mac_no);
                if ($time_pre == $date_time) {
                    return FALSE;
                } else {
                    return TRUE;
                }
                return TRUE;
            }
        } else {
            return FALSE;
        }
    }
    ///// カウンターマスターの取得 カウンター倍率を返す
    protected function getCounterMaster($mac_no, $parts_no)
    {
        $query = "
            SELECT count FROM equip_count_master WHERE mac_no={$mac_no} AND parts_no='{$parts_no}'
        ";
        if (getUniResult($query, $count) > 0) {
            return $count;
        }
        $query = "
            SELECT count FROM equip_count_master WHERE mac_no={$mac_no} AND parts_no='000000000'
        ";
        if (getUniResult($query, $count) > 0) {
            return $count;
        } else {
            return 1;
        }
    }
    ///// 停止の定義マスターの取得 停止と判断する秒数を返す
    protected function getStopMaster($mac_no, $parts_no)
    {
        $query = "
            SELECT stop FROM equip_stop_master WHERE mac_no={$mac_no} AND parts_no='{$parts_no}'
        ";
        if (getUniResult($query, $stop) > 0) {
            return $stop;
        }
        $query = "
            SELECT stop FROM equip_stop_master WHERE mac_no={$mac_no} AND parts_no='000000000'
        ";
        if (getUniResult($query, $stop) > 0) {
            return $stop;
        } else {
            return 1;
        }
    }
    ///// 前回の状態と違えばtrue 違っていても停止=3の時はマスターのチェックを行う
    protected function checkStopTime($db_state, $date_time, $state, $res_key, $in_time)
    {
        if ($db_state == $state) {
            return false;   // 状態を書込まない
        } elseif ($state != 3 && $db_state != $state) {
            return true;    // 状態を書込む
        }
        $mac_no   = $res_key['mac_no'];
        $parts_no = $res_key['parts_no'];
        $stop = $this->getStopMaster($mac_no, $parts_no);
        $query = "
            SELECT (TIMESTAMP '{$in_time}' - TIMESTAMP '{$date_time}') >= INTERVAL '{$stop} second' AS stop_flg
        ";
        $check = 't';
        getUniResult($query, $check);
        if ($check == 't') return true; else return false;
    }
    ///// 機械の物理状態データをDBより取得 メソッド
    protected function getPhysicalState($mac_no, $date='99999999', $time='999999')
    {
        $date = str_replace('-', '', $date);
        $time = str_replace(':', '', $time);
        // 物理信号をDBより取得
        $query = "
            SELECT
                state
            FROM
                equip_mac_state_log2
            WHERE
                equip_index2(mac_no, date_time) <= to_char({$mac_no}{$date}{$time}, 'FM99999999/99/99 99:99:99')
                AND
                equip_index2(mac_no, date_time) >= to_char({$mac_no}00000000000000, 'FM99999999/99/99 99:99:99')
            ORDER BY
                equip_index2(mac_no, date_time) DESC
            LIMIT 1
        ";
        if (getUniResult($query, $state_p) < 1) {
            // 初回のため電源OFFとみなす
            $state_p = 0;
        }
        return $state_p;
    }
    ///// 機械の物理状態データをDBより取得 メソッド
    protected function getPhysicalTime($mac_no, $date='99999999', $time='999999')
    {
        $date = str_replace('-', '', $date);
        $time = str_replace(':', '', $time);
        // 物理信号をDBより取得
        $query = "
            SELECT
                date_time
            FROM
                equip_mac_state_log2
            WHERE
                equip_index2(mac_no, date_time) <= to_char({$mac_no}{$date}{$time}, 'FM99999999/99/99 99:99:99')
                AND
                equip_index2(mac_no, date_time) >= to_char({$mac_no}00000000000000, 'FM99999999/99/99 99:99:99')
            ORDER BY
                equip_index2(mac_no, date_time) DESC
            LIMIT 1
        ";
        if (getUniResult($query, $state_p) < 1) {
            // 初回のため電源OFFとみなす
            $state_p = 0;
        }
        return $state_p;
    }
    ///// 機械の物理状態データをDBより取得 メソッド
    protected function getPhysicalSame($mac_no, $state, $date_time)
    {
        // 物理信号をDBより取得
        $query = "
            SELECT
                mac_no
            FROM
                equip_mac_state_log2
            WHERE
                mac_no = {$mac_no}
                AND
                state = {$state}
                AND
                date_time = TIMESTAMP '{$date_time}'
        ";
        if (getUniResult($query, $mac) < 1) {
            // 初回のため電源OFFとみなす
            return FALSE;
        }
        return TRUE;
    }
    ///// 論理状態データを取得 (物理信号とロータリースイッチで適正チェックを行ったデータを対象とする)
    protected function getLogicalState($mac_no, $date='99999999', $time='999999')
    {
        $date = str_replace('-', '', $date);
        $time = str_replace(':', '', $time);
        $query = "
            SELECT
                state
            FROM
                equip_mac_state_log
            WHERE
                equip_index2(mac_no, date_time) <= to_char({$mac_no}{$date}{$time}, 'FM99999999/99/99 99:99:99')
                AND
                equip_index2(mac_no, date_time) >= to_char({$mac_no}00000000000000, 'FM99999999/99/99 99:99:99')
            ORDER BY
                equip_index2(mac_no, date_time) DESC
            LIMIT 1
        ";
        if (getUniResult($query, $state) < 1) {
            $state = 0;     // 状態データが無いので無条件に電源off=0
        }
        return $state;
    }
    ///// 論理状態データを取得 (物理信号とロータリースイッチで適正チェックを行ったデータを対象とする)
    protected function getLogicalTime($mac_no, $date='99999999', $time='999999')
    {
        $date = str_replace('-', '', $date);
        $time = str_replace(':', '', $time);
        $query = "
            SELECT
                date_time
            FROM
                equip_mac_state_log
            WHERE
                equip_index2(mac_no, date_time) <= to_char({$mac_no}{$date}{$time}, 'FM99999999/99/99 99:99:99')
                AND
                equip_index2(mac_no, date_time) >= to_char({$mac_no}00000000000000, 'FM99999999/99/99 99:99:99')
            ORDER BY
                equip_index2(mac_no, date_time) DESC
            LIMIT 1
        ";
        if (getUniResult($query, $state) < 1) {
            $state = 0;     // 状態データが無いので無条件に電源off=0
        }
        return $state;
    }
    ///// 機械の物理状態データをDBより取得 メソッド
    protected function getLogicalSame($mac_no, $state, $date_time)
    {
        // 物理信号をDBより取得
        $query = "
            SELECT
                mac_no
            FROM
                equip_mac_state_log
            WHERE
                mac_no = {$mac_no}
                AND
                state = {$state}
                AND
                date_time = TIMESTAMP '{$date_time}'
        ";
        if (getUniResult($query, $mac) < 1) {
            // 初回のため電源OFFとみなす
            return FALSE;
        }
        return TRUE;
    }
} // class EquipAutoLog End

?>
