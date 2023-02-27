<?php
//////////////////////////////////////////////////////////////////////////////
// 資材在庫部品 全品種の月平均出庫数・保有月数等照会           MVC Model 部 //
// Copyright (C) 2007-2016 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2007/05/23 Created   inventory_average_Model.php                         //
// 2007/06/09 マスター未登録(赤文字表示)のメッセージを追加。最新単価にリンク//
//            mb_convert_kanaの'k'→'ksa'へ変更(部品名と親機種)             //
// 2007/06/10 table indexに(div, parts_no)を追加しSetInitWhere()メソッド変更//
// 2007/06/14 要因マスターの編集・コメント・要因の登録編集 関連 完了        //
//            editFactor()メソッドでSJIS→EUC-JPへ変換しないと文字化け調査中//
// 2007/07/03 保有月順に ORDER BY 保有月 DESC, 在庫金額 DESC を追加         //
// 2007/07/11 部品番号(searchPartsNo)のLIKE検索追加。                       //
// 2007/07/23 保有月の指定を追加(フィルター機能) targetHoldMonth            //
// 2016/06/24 CSV出力追加のためSQLのWHERE句を受け渡し。                大谷 //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
// ini_set('zend.ze1_compatibility_mode', '1');    // zend 1.X コンパチ php4の互換モード

require_once ('../../../ComTableMntClass.php');     // TNK 全共通 テーブルメンテ&ページ制御Class


/*****************************************************************************************
*       MVCのModel部 クラス定義 ComTableMnt(class) 共用テーブルメンテクラスを拡張        *
*****************************************************************************************/
class InventoryAverage_Model extends ComTableMnt
{
    ///// Private properties
    private $where;                             // 共用 SQLのWHERE句
    private $order;                             // 共用 SQLのORDER句
    private $totalMsg;                          // フッターに入れる合計件数・合計金額
    
    ///// public properties
    // public  $graph;                             // GanttChartのインスタンス
    
    /************************************************************************
    *                               Public methods                          *
    ************************************************************************/
    ////////// Constructer の定義 (php5へ移行時は __construct() へ変更予定) (デストラクタ__destruct())
    public function __construct($request)
    {
        ///// 合計レコード数取得SQL設定
        switch ($request->get('showMenu')) {
        case 'List':                                        // 資材部品の保有月等のリスト
            $this->where = $this->SetInitWhere($request);
            $this->order = $this->SetInitOrder($request);
            $sql_sum = "SELECT count(*) FROM inventory_average_summary {$this->where}";
            break;
        case 'Comment':                                     // コメント・要因テーブルの編集
            $this->where = '';
            $this->order = '';
            $sql_sum = "SELECT count(*) FROM inventory_average_comment";
            break;
        case 'FactorMnt':                                   // 要因マスターの照会・編集
            $this->where = '';
            $this->order = '';
            $sql_sum = "SELECT count(*) FROM inventory_average_factor";
            break;
        case 'CondForm':
        case 'Both':
        default:
            $this->where = '';
            return;
        }
        $log_file = 'inventory_average.log';
        ///// Constructer を定義すると 基底クラスの Constructerが実行されない
        ///// 基底ClassのConstructerはプログラマーの責任で呼出す
        parent::__construct($sql_sum, $request, $log_file, 1000);
    }
    
    ////////// MVC の Model 部の結果 表示用のデータ取得
    ///// List部    資材在庫品目全て対象で保有月等の指定条件での 一覧表
    public function outViewListHTML($request, $menu, $pageParameter, $session)
    {
        /************************* ヘッダー ***************************/
        // 固定のHTMLヘッダーソースを取得
        $listHTML  = $this->getViewHTMLconst('header');
        // 可変部のHTMLボディソースを取得
        $listHTML .= $this->getViewHTMLheader($request, $menu, $pageParameter);
        // 固定のHTMLフッターソースを取得
        $listHTML .= $this->getViewHTMLconst('footer');
        // HTMLファイル出力
        $file_name = "list/inventory_average_ViewListHeader-{$_SESSION['User_ID']}.html";
        $handle = fopen($file_name, 'w');
        fwrite($handle, $listHTML);
        fclose($handle);
        chmod($file_name, 0666);       // fileを全てrwモードにする
        /************************* ボディ ***************************/
        // 固定のHTMLヘッダーソースを取得
        $listHTML  = $this->getViewHTMLconst('header');
        // 可変部のHTMLボディソースを取得
        $listHTML .= $this->getViewHTMLbody($request, $menu, $session);
        // 固定のHTMLフッターソースを取得
        $listHTML .= $this->getViewHTMLconst('footer');
        // HTMLファイル出力
        $file_name = "list/inventory_average_ViewListBody-{$_SESSION['User_ID']}.html";
        $handle = fopen($file_name, 'w');
        fwrite($handle, $listHTML);
        fclose($handle);
        chmod($file_name, 0666);       // fileを全てrwモードにする
        /************************* フッター ***************************/
        // 固定のHTMLヘッダーソースを取得
        $listHTML  = $this->getViewHTMLconst('header');
        // 可変部のHTMLボディソースを取得
        $listHTML .= $this->getViewHTMLfooter();
        // 固定のHTMLフッターソースを取得
        $listHTML .= $this->getViewHTMLconst('footer');
        // HTMLファイル出力
        $file_name = "list/inventory_average_ViewListFooter-{$_SESSION['User_ID']}.html";
        $handle = fopen($file_name, 'w');
        fwrite($handle, $listHTML);
        fclose($handle);
        chmod($file_name, 0666);       // fileを全てrwモードにする
        return ;
    }
    
    ///// List部    要因項目マスターの 一覧表
    public function outViewFactorHTML($request, $menu, $session)
    {
        /************************* ボディ ***************************/
        // 固定のHTMLヘッダーソースを取得
        $listHTML  = $this->getViewHTMLconst('header');
        // 可変部のHTMLボディソースを取得
        $listHTML .= $this->getViewFactorHTMLbody($request, $menu, $session);
        // 固定のHTMLフッターソースを取得
        $listHTML .= $this->getViewHTMLconst('footer');
        // HTMLファイル出力
        $file_name = "factor/inventory_average_ViewFactorBody-{$_SESSION['User_ID']}.html";
        $handle = fopen($file_name, 'w');
        fwrite($handle, $listHTML);
        fclose($handle);
        chmod($file_name, 0666);       // fileを全てrwモードにする
        return ;
    }
    
    ///// 部品のコメントを保存
    public function commentSave($request, $result, $session)
    {
        // コメントのパラメーターチェック(内容はチェック済み)
        // if ($request->get('comment') == '') return;  // これを行うと削除できない
        if ($request->get('targetPartsNo') == '') return;
        $last_date = date('Y-m-d H:i:s');
        $last_user = $_SERVER['REMOTE_ADDR'] . ' ' . gethostbyaddr($_SERVER['REMOTE_ADDR']) . ' ' . $_SESSION['User_ID'];
        // 未設定又は設定解除をチェック
        if ($request->get('targetFactor') == '') {
            $reg_factor = 'NULL';
        } else {
            $reg_factor = $request->get('targetFactor');
        }
        // コメント中のブラウザーが付けたCRを取り払う
        $comment = str_replace("\r", '', $request->get('comment'));
        $query = "SELECT comment, factor FROM inventory_average_comment WHERE parts_no='{$request->get('targetPartsNo')}'";
        if ($this->getResult($query, $res) < 1) {
            if ($comment == '' && $request->get('targetFactor') == '') {
                // データ無し
                $result->add('AutoClose', 'G_reloadFlg=false; window.close();'); // 登録後 親のリロードはしないでWindow終了
                return;
            }
            $sql = "
                INSERT INTO inventory_average_comment (parts_no, comment, factor, last_date, last_user)
                VALUES ('{$request->get('targetPartsNo')}', '{$comment}', {$reg_factor}, '{$last_date}', '{$last_user}')
            ";
            if ($this->execute_Insert($sql) <= 0) {
                $_SESSION['s_sysmsg'] = "部品番号：{$request->get('targetPartsNo')}\\n\\n要因及びコメントの登録が出来ませんでした！　管理担当者へ連絡して下さい。";
            } else {
                $_SESSION['s_sysmsg'] = "部品番号：{$request->get('targetPartsNo')}\\n\\n要因及びコメントを登録しました。";
            }
        } else {
            $saveSQL = "SELECT * FROM inventory_average_comment WHERE parts_no='{$request->get('targetPartsNo')}'";
            if ($comment == '' && $request->get('targetFactor') == '') {
                // コメントの内容が削除されて更新の場合は、実レコードも削除
                $sql = "DELETE FROM inventory_average_comment WHERE parts_no='{$request->get('targetPartsNo')}'";
                if ($this->execute_Delete($sql, $saveSQL) <= 0) {
                    $_SESSION['s_sysmsg'] = "部品番号：{$request->get('targetPartsNo')}\\n\\n要因及びコメントの削除が出来ませんでした！　管理担当者へ連絡して下さい。";
                } else {
                    $_SESSION['s_sysmsg'] = "部品番号：{$request->get('targetPartsNo')}\\n\\n要因及びコメントを削除しました。";
                }
            } elseif ($res[0]['comment'] == $comment && $res[0]['factor'] == $request->get('targetFactor')) {
                // 変更無し
                $result->add('AutoClose', 'G_reloadFlg=false; window.close();'); // 登録後 親のリロードはしないでWindow終了
                return;
            } else {
                $sql = "
                    UPDATE inventory_average_comment SET comment='{$comment}', factor={$reg_factor},
                    last_date='{$last_date}', last_user='{$last_user}'
                    WHERE parts_no='{$request->get('targetPartsNo')}'
                ";
                if ($this->execute_Update($sql, $saveSQL) <= 0) {
                    $_SESSION['s_sysmsg'] = "部品番号：{$request->get('targetPartsNo')}\\n\\n要因及びコメントの変更が出来ませんでした！　管理担当者へ連絡して下さい。";
                } else {
                    $_SESSION['s_sysmsg'] = "部品番号：{$request->get('targetPartsNo')}\\n\\n要因及びコメントを変更しました。";
                }
            }
        }
        $session->add('regParts', $request->get('targetPartsNo'));  // マーカー及びジャンプ用に登録
        $result->add('AutoClose', 'window.close();'); // 登録後 Window終了
        return;
    }
    
    ///// 部品のコメントを取得
    public function getComment($request, $result)
    {
        // コメントのパラメーターチェック(内容はチェック済み)
        if ($request->get('targetPartsNo') == '') return '';
        $query = "
            SELECT  comment  ,
                    trim(substr(midsc, 1, 20))
            FROM miitem LEFT OUTER JOIN
            inventory_average_comment ON(mipn=parts_no)
            WHERE mipn='{$request->get('targetPartsNo')}'
        ";
        $res = array();
        if ($this->getResult2($query, $res) > 0) {
            $result->add('comment', $res[0][0]);
            $result->add('parts_name', $res[0][1]);
            $result->add('title', "{$request->get('targetPartsNo')}：{$res[0][1]}");
            return true;
        } else {
            return false;
        }
    }
    
    ///// 要因マスターの select options を取得
    public function getFactorOptions($request, $result)
    {
        // コメントのパラメーターチェック(内容はチェック済み)
        if ($request->get('targetPartsNo') == '') return '';
        $query = "
            SELECT factor FROM inventory_average_comment WHERE parts_no='{$request->get('targetPartsNo')}'
        ";
        $factor = '';
        $this->getUniResult($query, $factor);
        $query = "
            SELECT factor, factor_name, factor_explanation, active FROM inventory_average_factor ORDER BY factor ASC
        ";
        $res = array();
        if ( ($rows=$this->getResult2($query, $res)) < 1) {
            return;
        }
        $options1 = "\n";
        $options2 = "\n";
        for ($i=0; $i<$rows; $i++) {
            if ($res[$i][0] == $factor) {
                $options1 .= "<option value='{$res[$i][0]}' selected>{$res[$i][1]}</option>\n";
                $options2 .= "<option value='{$res[$i][0]}' selected>{$res[$i][2]}</option>\n";
            } elseif ($res[$i][3] == 'f') {
                continue;
            } else {
                $options1 .= "<option value='{$res[$i][0]}'>{$res[$i][1]}</option>\n";
                $options2 .= "<option value='{$res[$i][0]}'>{$res[$i][2]}</option>\n";
            }
        }
        $options1 .= "<option value='' style='color:red;'>登録しない</option>\n";
        $options2 .= "<option value='' style='color:red;'>登録してある場合は削除します。</option>\n";
        $result->add('factorNameOptions', $options1);
        $result->add('factorExplanationOptions', $options2);
    }
    
    /*************** ComTableMntClass の Out methods をオーバーライド ****************/
    public function out_pageRecOptions_HTML($default=20)
    {
        if (!is_numeric($default)) $default = 20;
        $Options = '';
        switch ($default) {
        case    5:
        case   10:
        case   15:
        case   20:
        case   30:
        case   50:
        case  100:
        case  500:
        case 1000:
        case 2000:
        case 4000:
        case 8000:
        case 12000:
            break;
        default:
            $Options .= "<option value='{$default}' selected>{$default}行</option>";
        }
        if ($default == 5) {
            $Options .= "<option value='5' selected>5行</option>";
        } else {
            $Options .= "<option value='5'>5行</option>";
        }
        if ($default == 10) {
            $Options .= "<option value='10' selected>10行</option>";
        } else {
            $Options .= "<option value='10'>10行</option>";
        }
        if ($default == 15) {
            $Options .= "<option value='15' selected>15行</option>";
        } else {
            $Options .= "<option value='15'>15行</option>";
        }
        if ($default == 20) {
            $Options .= "<option value='20' selected>20行</option>";
        } else {
            $Options .= "<option value='20'>20行</option>";
        }
        if ($default == 30) {
            $Options .= "<option value='30' selected>30行</option>";
        } else {
            $Options .= "<option value='30'>30行</option>";
        }
        if ($default == 50) {
            $Options .= "<option value='50' selected>50行</option>";
        } else {
            $Options .= "<option value='50'>50行</option>";
        }
        if ($default == 100) {
            $Options .= "<option value='100' selected>100行</option>";
        } else {
            $Options .= "<option value='100'>100行</option>";
        }
        if ($default == 500) {
            $Options .= "<option value='500' selected>500行</option>";
        } else {
            $Options .= "<option value='500'>500行</option>";
        }
        if ($default == 1000) {
            $Options .= "<option value='1000' selected>1000行</option>";
        } else {
            $Options .= "<option value='1000'>1000行</option>";
        }
        if ($default == 2000) {
            $Options .= "<option value='2000' selected>2000行</option>";
        } else {
            $Options .= "<option value='2000'>2000行</option>";
        }
        if ($default == 4000) {
            $Options .= "<option value='4000' selected>4000行</option>";
        } else {
            $Options .= "<option value='4000'>4000行</option>";
        }
        if ($default == 8000) {
            $Options .= "<option value='8000' selected>8000行</option>";
        } else {
            $Options .= "<option value='8000'>8000行</option>";
        }
        if ($default == 12000) {
            $Options .= "<option value='12000' selected>12000行</option>";
        } else {
            $Options .= "<option value='12000'>12000行</option>";
        }
        return $Options;
    }
    
    ///// 要因マスターの編集
    public function editFactor($request, $result, $session)
    {
        $request->add('targetFactorName', mb_convert_encoding($request->get('targetFactorName'), 'EUC-JP', 'SJIS'));
        $request->add('targetFactorExplanation', mb_convert_encoding($request->get('targetFactorExplanation'), 'EUC-JP', 'SJIS'));
        $request->add('targetFactorName', trim($request->get('targetFactorName')));
        $request->add('targetFactorExplanation', trim($request->get('targetFactorExplanation')));
        if ($request->get('targetFactorName') == '') return;
        if ($request->get('targetFactorExplanation') == '') return;
        $last_date = date('Y-m-d H:i:s');
        $last_user = $_SERVER['REMOTE_ADDR'] . ' ' . gethostbyaddr($_SERVER['REMOTE_ADDR']) . ' ' . $_SESSION['User_ID'];
        if ($request->get('targetFactor') == '') {
            ///// INSERT
            $query = "SELECT factor FROM inventory_average_factor WHERE factor_name='{$request->get('targetFactorName')}'";
            if ($this->getUniResult($query, $factor) > 0) {
                $_SESSION['s_sysmsg'] = "要因項目：{$request->get('targetFactorName')}\\n\\n入力された要因項目は既に 「{$factor}」 番で登録されています！";
                $session->add('regFactor', $factor);  // マーカー及びジャンプ用に登録
                return;
            }
            $query = "SELECT CASE WHEN max(factor) IS NULL THEN 1 ELSE max(factor)+1 END FROM inventory_average_factor";
            $factor = 0;
            $this->getUniResult($query, $factor);
            $sql = "
                INSERT INTO inventory_average_factor (factor, factor_name, factor_explanation, active, last_date, last_user)
                VALUES ({$factor}, '{$request->get('targetFactorName')}', '{$request->get('targetFactorExplanation')}',
                    TRUE, '{$last_date}', '{$last_user}')
            ";
            if ($this->execute_Insert($sql) <= 0) {
                $_SESSION['s_sysmsg'] = "要因番号：{$factor}\\n\\n要因マスターの登録が出来ませんでした！　管理担当者へ連絡して下さい。";
            } else {
                $_SESSION['s_sysmsg'] = "要因番号：{$factor}\\n\\n要因マスターを登録しました。";
                $session->add('regFactor', $factor);  // マーカー及びジャンプ用に登録
            }
        } else {
            ///// UPDATE
            $query = "SELECT factor_name, factor_explanation FROM inventory_average_factor WHERE factor={$request->get('targetFactor')}";
            if ($this->getResult2($query, $check) < 1) {
                $_SESSION['s_sysmsg'] = "要因番号：{$request->get('targetFactor')}\\n\\n指定された要因番号は登録されていません！　管理担当者へ連絡して下さい。";
            } elseif ($check[0][0] == $request->get('targetFactorName') && $check[0][1] == $request->get('targetFactorExplanation')) {
                // 変更無し
                return;
            } else {
                $query = "SELECT factor FROM inventory_average_factor WHERE factor_name='{$request->get('targetFactorName')}' AND factor != {$request->get('targetFactor')}";
                if ($this->getUniResult($query, $factor) > 0) {
                    $_SESSION['s_sysmsg'] = "要因項目：{$request->get('targetFactorName')}\\n\\n入力された要因項目は既に 「{$factor}」 番で登録されています！";
                    $session->add('regFactor', $factor);  // マーカー及びジャンプ用に登録
                    return;
                }
                $sql = "
                    UPDATE inventory_average_factor SET factor_name='{$request->get('targetFactorName')}', factor_explanation='{$request->get('targetFactorExplanation')}',
                        last_date='{$last_date}', last_user='{$last_user}'
                    WHERE factor={$request->get('targetFactor')}
                ";
                $save_sql = "SELECT * FROM inventory_average_factor WHERE factor={$request->get('targetFactor')}";
                if ($this->execute_Update($sql, $save_sql) <= 0) {
                    $_SESSION['s_sysmsg'] = "要因番号：{$request->get('targetFactor')}\\n\\n要因マスターの変更が出来ませんでした！　管理担当者へ連絡して下さい。";
                } else {
                    $_SESSION['s_sysmsg'] = "要因番号：{$request->get('targetFactor')}\\n\\n要因マスターを変更しました。";
                    $session->add('regFactor', $request->get('targetFactor'));  // マーカー及びジャンプ用に登録
                }
            }
        }
    }
    
    ///// 要因マスターの削除
    public function deleteFactor($request, $result, $session)
    {
        if ($request->get('targetFactor') == '') return;
        $sql = "DELETE FROM inventory_average_factor WHERE factor={$request->get('targetFactor')}";
        $save_sql = "SELECT * FROM inventory_average_factor WHERE factor={$request->get('targetFactor')}";
        if ($this->execute_Delete($sql, $save_sql) <= 0) {
            $_SESSION['s_sysmsg'] = "要因番号：{$request->get('targetFactor')}\\n\\n要因マスターの削除が出来ませんでした！　管理担当者へ連絡して下さい。";
        } else {
            $_SESSION['s_sysmsg'] = "要因番号：{$request->get('targetFactor')}\\n\\n要因マスターを削除しました。";
        }
    }
    
    ///// 要因マスターの有効・無効の切替
    public function activeFactor($request, $result, $session)
    {
        if ($request->get('targetFactor') == '') return;
        $query = "SELECT active FROM inventory_average_factor WHERE factor={$request->get('targetFactor')}";
        if ($this->getUniResult($query, $check) < 1) {
            $_SESSION['s_sysmsg'] = "要因番号：{$request->get('targetFactor')}\\n\\n指定された要因番号は登録されていません！　管理担当者へ連絡して下さい。";
        } else {
            if ($check == 't') {
                $active = 'FALSE';
                $message = '無効';
            } else {
                $active = 'TRUE';
                $message = '有効';
            }
            $last_date = date('Y-m-d H:i:s');
            $last_user = $_SERVER['REMOTE_ADDR'] . ' ' . gethostbyaddr($_SERVER['REMOTE_ADDR']) . ' ' . $_SESSION['User_ID'];
            $sql = "UPDATE inventory_average_factor SET active={$active}, last_date='{$last_date}', last_user='{$last_user}' WHERE factor={$request->get('targetFactor')}";
            $save_sql = "SELECT * FROM inventory_average_factor WHERE factor={$request->get('targetFactor')}";
            if ($this->execute_Update($sql, $save_sql) <= 0) {
                $_SESSION['s_sysmsg'] = "要因番号：{$request->get('targetFactor')}\\n\\n要因マスターを{$message}に出来ませんでした！　管理担当者へ連絡して下さい。";
            } else {
                $_SESSION['s_sysmsg'] = "要因番号：{$request->get('targetFactor')}\\n\\n要因マスターを{$message}にしました。";
            }
            $session->add('regFactor', $request->get('targetFactor'));  // マーカー及びジャンプ用に登録
        }
    }
    
    /***************************************************************************
    *                              Protected methods                           *
    ***************************************************************************/
    ////////// リクエストによりSQL文の基本WHERE区を設定
    protected function SetInitWhere($request)
    {
        ///// 基本WHERE区の設定
        if ($request->get('searchPartsNo') != '') {
            $where = "WHERE parts_no LIKE '%{$request->get('searchPartsNo')}%'";
        } else {
            $where = 'WHERE TRUE';
        }
        $hold = $request->get('targetHoldMonth');
        switch ($request->get('targetDivision')) {
        case 'AL':
            $where .= " AND hold_monthly_avr >= {$hold}";
            break;
        case 'CA':
            $where .= " AND hold_monthly_avr >= {$hold} AND div = 'C'";
            break;
        case 'CH':
            $where .= " AND hold_monthly_avr >= {$hold} AND div = 'C'";
            break;
        case 'CS':
            $where .= " AND hold_monthly_avr >= {$hold} AND div = 'C'";
            break;
        case 'LA':
            $where .= " AND hold_monthly_avr >= {$hold} AND div = 'L'";
            break;
        case 'LH':
            $where .= " AND hold_monthly_avr >= {$hold} AND div = 'L' AND parts_no NOT LIKE 'LR%' AND parts_no NOT LIKE 'LC%'";
            break;
        case 'LB':
            $where .= " AND hold_monthly_avr >= {$hold} AND (parts_no LIKE 'LR%' OR parts_no LIKE 'LC%')";
            break;
        case 'OT':
        default:
            $where .= " AND hold_monthly_avr >= {$hold} AND div != 'C' AND div != 'L'";
        }
        return $where;
    }
    
    ////////// リクエストによりSQL文の基本ORDER区を設定
    protected function SetInitOrder($request)
    {
        ///// targetSortItemで切替
        switch ($request->get('targetSortItem')) {
        case 'parts':
            $order = 'ORDER BY 部品番号 ASC';
            break;
        case 'name':
            $order = 'ORDER BY 部品名 ASC';
            break;
        case 'parent':
            $order = 'ORDER BY 親機種 DESC';
            break;
        case 'price':
            $order = 'ORDER BY 最新単価 DESC';
            break;
        case 'stock':
            $order = 'ORDER BY 前日在庫数 DESC';
            break;
        case 'money':
            $order = 'ORDER BY 在庫金額 DESC';
            break;
        case 'avrpcs':
            $order = 'ORDER BY 月平均出庫数 ASC';
            break;
        case 'month':
            $order = 'ORDER BY 保有月 DESC, 在庫金額 DESC';
            break;
        case 'factor':
            $order = 'ORDER BY 要因名 DESC';
            break;
        default:
            $order = 'ORDER BY 保有月 DESC';
        }
        return $order;
    }
    
    /***************************************************************************
    *                               Private methods                            *
    ***************************************************************************/
    ///// List部   一覧表の ヘッダー部 生成
    private function getViewHTMLheader($request, $menu, $pageParameter)
    {
        // ソート項目の配列取得
        $item = $this->getSortItemArray($request);
        // HTMLの項目生成
        $listTable = '';
        $listTable .= "<table width='100%' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='1'>\n";
        $listTable .= "    <tr><td> <!----------- ダミー(デザイン用) ------------>\n";
        $listTable .= "<table class='winbox_field' width='100%' border='1' cellspacing='0' cellpadding='3'>\n";
        $listTable .= "    <tr>\n";
        $listTable .= "        <th class='winbox' width=' 5%'>No</th>\n";
        $listTable .= "        <th class='winbox' width='12%'{$item['parts']} title='部品番号で、昇順にソートします。'>\n";
        $listTable .= "            <a href='{$menu->out_self()}?targetSortItem=parts&{$pageParameter}' target='_parent' onMouseover=\"status='部品番号で、昇順にソートします。'; return true;\" onMouseout=\"status='';\">部品番号</a></th>\n";
        $listTable .= "        <th class='winbox' width='19%'{$item['name']} title='部品名で、昇順にソートします。'>\n";
        $listTable .= "            <a href='{$menu->out_self()}?targetSortItem=name&{$pageParameter}' target='_parent' onMouseover=\"status='部品名で、昇順にソートします。'; return true;\" onMouseout=\"status='';\">部　品　名</a></th>\n";
        $listTable .= "        <th class='winbox' width='12%'{$item['parent']} title='親機種で、昇順にソートします。'>\n";
        $listTable .= "            <a href='{$menu->out_self()}?targetSortItem=parent&{$pageParameter}' target='_parent' onMouseover=\"status='親機種で、昇順にソートします。'; return true;\" onMouseout=\"status='';\">親機種</a></th>\n";
        $listTable .= "        <th class='winbox' width=' 9%'{$item['price']} title='最新単価で、降順にソートします。'>\n";
        $listTable .= "            <a href='{$menu->out_self()}?targetSortItem=price&{$pageParameter}' target='_parent' onMouseover=\"status='最新単価で、降順にソートします。'; return true;\" onMouseout=\"status='';\">最新単価</a></th>\n";
        $listTable .= "        <th class='winbox' width=' 9%'{$item['stock']} title='前日の在庫数で、降順にソートします。'>\n";
        $listTable .= "            <a href='{$menu->out_self()}?targetSortItem=stock&{$pageParameter}' target='_parent' onMouseover=\"status='前日の在庫数で、降順にソートします。'; return true;\" onMouseout=\"status='';\">前日在庫</a></th>\n";
        $listTable .= "        <th class='winbox' width='10%'{$item['money']} title='在庫金額で、降順にソートします。'>\n";
        $listTable .= "            <a href='{$menu->out_self()}?targetSortItem=money&{$pageParameter}' target='_parent' onMouseover=\"status='在庫金額で、降順にソートします。'; return true;\" onMouseout=\"status='';\">在庫金額</a></th>\n";
        $listTable .= "        <th class='winbox' width='10%'{$item['avrpcs']} title='月平均出庫数で、降順にソートします。'>\n";
        $listTable .= "            <a href='{$menu->out_self()}?targetSortItem=avrpcs&{$pageParameter}' target='_parent' onMouseover=\"status='月平均出庫数で、降順にソートします。'; return true;\" onMouseout=\"status='';\">平均出庫</a></th>\n";
        $listTable .= "        <th class='winbox' width=' 7%'{$item['month']} title='保有月で、降順にソートします。'>\n";
        $listTable .= "            <a href='{$menu->out_self()}?targetSortItem=month&{$pageParameter}' target='_parent' onMouseover=\"status='保有月で、降順にソートします。'; return true;\" onMouseout=\"status='';\">保有月</a></th>\n";
        $listTable .= "        <th class='winbox' width=' 7%'{$item['factor']} title='要因で、昇順にソートします。'>\n";
        $listTable .= "            <a href='{$menu->out_self()}?targetSortItem=factor&{$pageParameter}' target='_parent' onMouseover=\"status='要因で、昇順にソートします。'; return true;\" onMouseout=\"status='';\">要因</a></th>\n";
        $listTable .= "    </tr>\n";
        $listTable .= "</table>\n";
        $listTable .= "    </td></tr>\n";
        $listTable .= "</table> <!----------------- ダミーEnd ------------------>\n";
        // return mb_convert_encoding($listTable, 'UTF-8');
        return $listTable;
    }
    
    ///// List部   一覧生成 ボディ部
    private function getViewHTMLbody($request, $menu, $session)
    {
        // ソート項目の配列取得
        $item = $this->getSortItemArray($request);
        $query = "
            SELECT   invent.parts_no    AS 部品番号         -- 00
                    , trim(substr(midsc, 1, 14))
                                        AS 部品名           -- 01
                    , CASE
                        WHEN mepnt = '' THEN '&nbsp;'
                        WHEN mepnt IS NULL THEN '&nbsp;'
                        ELSE trim(substr(mepnt, 1, 9))
                      END               AS 親機種           -- 02
                    , CASE
                        WHEN latest_parts_cost(invent.parts_no) IS NULL THEN 0
                        ELSE latest_parts_cost(invent.parts_no)
                      END               AS 最新単価         -- 03
                    , invent_pcs
                                        AS 前日在庫数       -- 04
                    , CASE
                        WHEN latest_parts_cost(invent.parts_no) IS NULL THEN 0
                        ELSE Uround(latest_parts_cost(invent.parts_no) * invent_pcs, 0)
                      END               AS 在庫金額         -- 05
                    , month_pickup_avr
                                        AS 月平均出庫数     -- 06
                    , hold_monthly_avr
                                        AS 保有月           -- 07
                    , CASE
                        WHEN factor_name IS NULL THEN '&nbsp;'
                        ELSE factor_name
                      END               AS 要因名           -- 08
                    , factor_explanation
                                        AS 要因説明         -- 09
                    , comment
                                        AS コメント         -- 10
                    , CASE
                        WHEN latest_parts_cost_regno(invent.parts_no) IS NULL THEN 0
                        ELSE latest_parts_cost_regno(invent.parts_no)
                      END               AS 登録番号         -- 11
                    FROM
                        inventory_average_summary AS invent
                    LEFT OUTER JOIN
                        miitem ON (invent.parts_no = mipn)
                    LEFT OUTER JOIN
                        inventory_average_comment USING (parts_no)
                    LEFT OUTER JOIN
                        inventory_average_factor USING (factor)
                    {$this->where}
                    {$this->order}
        ";
        $session->add('csv_where', $this->where);
        $listTable = '';
        $listTable .= "<table width='100%' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='1'>\n";
        $listTable .= "    <tr><td> <!----------- ダミー(デザイン用) ------------>\n";
        $listTable .= "<table class='winbox_field' width='100%' border='1' cellspacing='0' cellpadding='3'>\n";
        $res = array();
        if ( ($rows=$this->execute_List($query, $res)) < 1 ) {
            $listTable .= "    <tr>\n";
            $listTable .= "        <td width='100%' align='center' class='winbox'>該当部品がありません。</td>\n";
            $listTable .= "    </tr>\n";
            $listTable .= "</table>\n";
            $listTable .= "    </td></tr>\n";
            $listTable .= "</table> <!----------------- ダミーEnd ------------------>\n";
            // $listTable .= "<script type='text/javascript'>\n";
            // $listTable .= "parent.document.ConditionForm.searchPartsNo.focus();\n";
            // $listTable .= "// parent.document.ConditionForm.searchPartsNo.select();\n";
            // $listTable .= "</script>\n";
        } else {
            $this->totalMsg = $this->getSumPrice($rows, $res);
            $date_low = (date('Ymd') - 100000);      // 10年前から(view_recで300に制限している)
            if ($session->get('regParts') != '') {
                $regParts = $session->get('regParts');
                $session->add('regParts', '');
            } else {
                $regParts = '';
            }
            for ($i=0; $i<$rows; $i++) {
                if ($regParts == $res[$i][0]) {
                    $listTable .= "    <tr onDblClick='InventoryAverage.win_open(\"" . $menu->out_self() . "?showMenu=Comment&targetPartsNo=" . urlencode($res[$i][0]) . "\", 600, 235)' title='{$res[$i][10]}' style='background-color:#ffffc6;'>\n";
                    $listTable .= "        <td class='winbox' width=' 5%' align='right' ><a name='Mark' style='color:black;'>" . ($i+1 + $this->get_offset()) . "</a></td>\n";                  // 行番号
                } elseif ($res[$i][10] == '') {
                    $listTable .= "    <tr onDblClick='InventoryAverage.win_open(\"" . $menu->out_self() . "?showMenu=Comment&targetPartsNo=" . urlencode($res[$i][0]) . "\", 600, 235)' title='ダブルクリックでコメントの照会・編集が出来ます。'>\n";
                    $listTable .= "        <td class='winbox' width=' 5%' align='right' >" . ($i+1 + $this->get_offset()) . "</td>\n";                  // 行番号
                } else {
                    $listTable .= "    <tr onDblClick='InventoryAverage.win_open(\"" . $menu->out_self() . "?showMenu=Comment&targetPartsNo=" . urlencode($res[$i][0]) . "\", 600, 235)' title='{$res[$i][10]}' style='background-color:#e6e6e6;'>\n";
                    $listTable .= "        <td class='winbox' width=' 5%' align='right' >" . ($i+1 + $this->get_offset()) . "</td>\n";                  // 行番号
                }
                $listTable .= "        <td class='winbox' width='12%' align='center'{$item['parts']} title='部品番号をクリックすれば在庫経歴を照会できます。'\n";
                $listTable .= "            onClick='InventoryAverage.win_open(\"" . $menu->out_action('在庫経歴') . "?parts_no=" . urlencode($res[$i][0]) . "&date_low={$date_low}&view_rec=300&noMenu=yes\", 900, 680)'\n";
                $listTable .= "        ><a href='javascript:void(0);'>{$res[$i][0]}</a></td>\n";                                                        // 部品番号
                if ($res[$i][1] != '') {                                                                                                                // 部品名
                    $listTable .= "        <td class='winbox' width='19%' align='left'{$item['name']}>" . mb_substr(mb_convert_kana($res[$i][1], 'ksa'), 0, 15) . "</td>\n";
                } else {
                    $listTable .= "        <td class='winbox' width='19%' align='left'{$item['name']}><span style='color:red;'>マスター未登録</span></td>\n";
                }
                $listTable .= "        <td class='winbox' width='12%' align='left'{$item['parent']}>" . mb_convert_kana($res[$i][2], 'ksa') . "</td>\n";// 親機種
                if ($res[$i][3] > 0) {                                                                                                                  // 最新単価
                    $listTable .= "        <td class='winbox' width=' 9%' align='right'{$item['price']} title='単価をクリックすれば単価登録を照会できます。'\n";
                    $listTable .= "            onClick='InventoryAverage.win_open(\"" . $menu->out_action('単価登録照会') . "?parts_no=" . urlencode($res[$i][0]) . "& reg_no={$res[$i][11]}&noMenu=yes\", 900, 680)'\n";
                    $listTable .= "        ><a href='javascript:void(0);'>" . number_format($res[$i][3], 2) . "</a></td>\n";                            // 最新単価
                } else {
                    $listTable .= "        <td class='winbox' width=' 9%' align='right'{$item['price']}>" . number_format($res[$i][3], 2) . "</td>\n";  // 最新単価
                }
                $listTable .= "        <td class='winbox' width=' 9%' align='right'{$item['stock']}>" . number_format($res[$i][4]) . "</td>\n";         // 前日の在庫数
                $listTable .= "        <td class='winbox' width='10%' align='right'{$item['money']}>" . number_format($res[$i][5]) . "</td>\n";         // 在庫金額
                $listTable .= "        <td class='winbox' width='10%' align='right'{$item['avrpcs']}>" . number_format($res[$i][6]) . "</td>\n";        // 月平均出庫数
                $listTable .= "        <td class='winbox' width=' 7%' align='right'{$item['month']}>" . number_format($res[$i][7], 1) . "</td>\n";      // 保有月
                $listTable .= "        <td class='winbox factorFont' width=' 7%' align='left'{$item['factor']} title='{$res[$i][9]}'>{$res[$i][8]}</td>\n";        // 要因名
                $listTable .= "    </tr>\n";
            }
            $listTable .= "</table>\n";
            $listTable .= "    </td></tr>\n";
            $listTable .= "</table> <!----------------- ダミーEnd ------------------>\n";
        }
        // return mb_convert_encoding($listTable, 'UTF-8');
        return $listTable;
    }
    
    ///// List部   一覧生成 フッター部
    private function getViewHTMLfooter()
    {
        // 初期化
        $listTable = '';
        $listTable .= "<table width='100%' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='1'>\n";
        $listTable .= "    <tr><td> <!----------- ダミー(デザイン用) ------------>\n";
        $listTable .= "<table class='winbox_field' width='100%' border='1' cellspacing='0' cellpadding='3'>\n";
        $listTable .= "    <tr>\n";
        $listTable .= "        <td class='winbox' align='right'>{$this->totalMsg}</td>\n";
        $listTable .= "    </tr>\n";
        $listTable .= "</table>\n";
        $listTable .= "    </td></tr>\n";
        $listTable .= "</table> <!----------------- ダミーEnd ------------------>\n";
        return $listTable;
    }
    
    ///// List部   要因項目マスター 一覧生成 ボディ部
    private function getViewFactorHTMLbody($request, $menu, $session)
    {
        // ソート項目の配列取得
        $item = $this->getSortItemArray($request);
        $query = "
            SELECT    factor            AS 要因番号         -- 00
                    , factor_name       AS 要因項目         -- 01
                    , factor_explanation
                                        AS 要因説明         -- 02
                    , CASE
                        WHEN active THEN '有効'
                        ELSE '無効'
                      END               AS 有効無効         -- 03
                    , regdate           AS 初回登録日       -- 04
                    , last_date         AS 最終変更日       -- 05
                    , last_user         AS 最終変更者       -- 06
                    , (SELECT parts_no FROM inventory_average_comment WHERE factor=invent.factor LIMIT 1)
                                        AS 使用部品         -- 07
                    FROM
                        inventory_average_factor AS invent
                    ORDER BY factor ASC
        ";
        $listTable = '';
        $listTable .= "<table width='100%' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='1'>\n";
        $listTable .= "    <tr><td> <!----------- ダミー(デザイン用) ------------>\n";
        $listTable .= "<table class='winbox_field' width='100%' border='1' cellspacing='0' cellpadding='3'>\n";
        $res = array();
        if ( ($rows=$this->getResult2($query, $res)) < 1 ) {
            $listTable .= "    <tr>\n";
            $listTable .= "        <td width='100%' align='center' class='winbox'>未登録です。</td>\n";
            $listTable .= "    </tr>\n";
            $listTable .= "</table>\n";
            $listTable .= "    </td></tr>\n";
            $listTable .= "</table> <!----------------- ダミーEnd ------------------>\n";
        } else {
            if ($session->get('regFactor') != '') {
                $regFactor = $session->get('regFactor');
                $session->add('regFactor', '');
            } else {
                $regFactor = '';
            }
            for ($i=0; $i<$rows; $i++) {
                if ($res[$i][3] == '無効') $activeColor = " color:gray;"; else $activeColor = '';
                if ($regFactor == $res[$i][0]) {
                    $listTable .= "    <tr style='background-color:#ffffc6;{$activeColor}'>\n";
                    $listTable .= "        <td class='winbox' width=' 5%' align='right' ><a name='Mark' style='color:black;'>{$res[$i][0]}</a></td>\n"; // 要因番号
                } else {
                    $listTable .= "    <tr style='{$activeColor}'>\n";
                    $listTable .= "        <td class='winbox' width=' 5%' align='right'>{$res[$i][0]}</td>\n";      // 要因番号
                }
                $listTable .= "        <td class='winbox' width='11%' align='left'>{$res[$i][1]}</td>\n";           // 要因項目
                $listTable .= "        <td class='winbox' width='60%' align='left' style='font-size:0.9em;'>{$res[$i][2]}</td>\n";           // 要因説明
                $listTable .= "        <td class='winbox' width=' 8%' align='center'>\n";
                $listTable .= "            <input type='button' name='editButton' value='修正' class='editButton'\n";
                $listTable .= "                onClick='parent.InventoryAverage.copyFactor(\"{$res[$i][0]}\", \"{$res[$i][1]}\", \"{$res[$i][2]}\");'\n";
                $listTable .= "            >\n";
                $listTable .= "        </td>\n";
                $listTable .= "        <td class='winbox' width=' 8%' align='center'>\n";
                if ($res[$i][3] == '有効') {
                    $listTable .= "            <input type='button' name='activeButton' value='無効' class='updateButton'\n";
                    $listTable .= "                onClick='parent.InventoryAverage.activeFactor(\"{$res[$i][0]}\", \"{$res[$i][1]}\", \"無効\");'\n";
                    $listTable .= "            >\n";
                } else {
                    $listTable .= "            <input type='button' name='activeButton' value='有効' class='updateButton'\n";
                    $listTable .= "                onClick='parent.InventoryAverage.activeFactor(\"{$res[$i][0]}\", \"{$res[$i][1]}\", \"有効\");'\n";
                    $listTable .= "            >\n";
                }
                $listTable .= "        </td>\n";
                $listTable .= "        <td class='winbox' width=' 8%' align='center'>\n";
                if ($res[$i][7] == '') {
                    $listTable .= "            <input type='button' name='delButton' value='削除' class='delButton'\n";
                    $listTable .= "                onClick='parent.InventoryAverage.deleteFactor(\"{$res[$i][0]}\", \"{$res[$i][1]}\");'\n";
                    $listTable .= "            >\n";
                } else {
                    $listTable .= "            <input type='button' name='delButton' value='削除' class='delButton' disabled>\n";
                }
                $listTable .= "        </td>\n";
                $listTable .= "    </tr>\n";
            }
            $listTable .= "</table>\n";
            $listTable .= "    </td></tr>\n";
            $listTable .= "</table> <!----------------- ダミーEnd ------------------>\n";
        }
        // return mb_convert_encoding($listTable, 'UTF-8');
        return $listTable;
    }
    
    ///// ソートアイテムの配列を返す
    private function getSortItemArray($request)
    {
        // 初期化
        $itemArray = array('parts' => '', 'name' => '', 'parent' => '', 'price' => '', 'stock' => '',
            'money' => '', 'avrpcs' => '', 'month' => '', 'factor' => '');
        // リクエストによりソート項目に色付け
        switch ($request->get('targetSortItem')) {
        case 'parts':
            $itemArray['parts'] = " style='background-color:#ffffc6;'";
            break;
        case 'name':
            $itemArray['name'] = " style='background-color:#ffffc6;'";
            break;
        case 'parent':
            $itemArray['parent'] = " style='background-color:#ffffc6;'";
            break;
        case 'price':
            $itemArray['price'] = " style='background-color:#ffffc6;'";
            break;
        case 'stock':
            $itemArray['stock'] = " style='background-color:#ffffc6;'";
            break;
        case 'money':
            $itemArray['money'] = " style='background-color:#ffffc6;'";
            break;
        case 'avrpcs':
            $itemArray['avrpcs'] = " style='background-color:#ffffc6;'";
            break;
        case 'month':
            $itemArray['month'] = " style='background-color:#ffffc6;'";
            break;
        case 'factor':
            $itemArray['factor'] = " style='background-color:#ffffc6;'";
            break;
        }
        return $itemArray;
    }
    
    ///// 固定のList部    HTMLファイル出力
    private function getViewHTMLconst($status)
    {
        if ($status == 'header') {
            $listHTML = 
"
<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.01 Transitional//EN'>
<html>
<head>
<meta http-equiv='Content-Type' content='text/html; charset=EUC-JP'>
<meta http-equiv='Content-Style-Type' content='text/css'>
<meta http-equiv='Content-Script-Type' content='text/javascript'>
<title>長期滞留部品List部</title>
<script type='text/javascript' src='/base_class.js'></script>
<link rel='stylesheet' href='/menu_form.css' type='text/css' media='screen'>
<link rel='stylesheet' href='../inventory_average.css' type='text/css' media='screen'>
<style type='text/css'>
<!--
body {
    background-image:   none;
    background-color:   #d6d3ce;
}
-->
</style>
<script type='text/javascript' src='../inventory_average.js'></script>
</head>
<body
    onLoad='if (parent.document.ConditionForm.searchPartsNo) parent.document.ConditionForm.searchPartsNo.focus();'
>
<center>
";
        } elseif ($status == 'footer') {
            $listHTML = 
"
</center>
</body>
</html>
";
        } else {
            $listHTML = '';
        }
        return $listHTML;
    }
    
    ///// 合計金額・件数を計算してメッセージを返す。
    private function getSumPrice($rows, $array)
    {
        $sumPrice = 0;     // 初期化
        for ($i=0; $i<$rows; $i++) {
            $sumPrice += $array[$i][5];
        }
        $sumPrice = number_format($sumPrice);
        return "合計件数 ： {$rows} 件 &nbsp;&nbsp;&nbsp&nbsp; 合計金額 ： {$sumPrice}";
    }
    

} // Class InventoryAverage_Model End

?>
