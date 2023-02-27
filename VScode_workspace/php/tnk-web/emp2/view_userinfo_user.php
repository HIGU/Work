<?php
//////////////////////////////////////////////////////////////////////////////
// 社員情報管理の include file 従業員情報検索結果                           //
// Copyright (C) 2001-2021 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2001/07/07 Created   view_userinfo_user.php(include file)                //
// 2002/08/07 register_globals = Off 対応                                   //
//            $key は このスクリプトの呼出元にある｡view_userinfo.php        //
// 2002/08/29 入社年で検索個所の to_char 組込関数がVar7.2.1でerrorに        //
//              なるため like 文に変更                                      //
// 2003/04/02 出向除く全てを追加 (所属の検索条件)                           //
// 2003/04/21 AUTH_LEVEL1(中級)以上のユーザーに対して現在年齢を追加         //
// 2003/11/11 getObject()をコメントアウト現在pg_lo_exportに対応してない     //
// 2003/12/22 and ud.uid!='000000' を追加(テスト用に000000を追加した為)     //
// 2004/10/19 並び順を部署/社員番号→部署/職位/社員番号へ変更(pm.pid DESC)  //
// 2005/01/17 access_log の変更と view_file_name(__FILE__) の追加           //
// 2005/01/26 background-imageを追加してデザイン変更(AM/PMで切替式)         //
// 2007/08/29 本日の出退勤を追加。情報の変更にページ制御のoffsetを追加      //
// 2007/08/30 写真クリックで別ウィンドウにズーム表示(簡易)追加              //
// 2007/09/11 uniqid(abcdef) → $uniq へ ('')がぬけている                   //
// 2007/09/11 VIWE_LIMIT → VIEW_LIMIT 44行目 へ修正                        //
// 2007/10/15 個人毎の教育・資格・移動経歴等の表示(印刷)ボタンを追加        //
// 2010/03/11 暫定的に大渕さん（970268）が登録できるように変更         大谷 //
// 2014/07/29 不在者照会ができるようメニューに追加(大谷、工場長限定)   大谷 //
//         ※アマノ側から受入出向者のデータが来ていない(要確認2014/07/29)   //
// 2015/01/30 有給残数の表示を追加                                     大谷 //
//            上級ユーザーでもパスワード・内容の変更をできないように変更    //
// 2015/02/12 1/30の変更を元に戻した（パスワード変更は表示しないまま） 大谷 //
// 2015/03/12 time_proのデータから当日までの有給計算を追加                  //
//            表示が重くなる為、一時コメント化                         大谷 //
// 2015/03/20 有給残を別プログラムで計算し、ここでは取得のみに変更     大谷 //
// 2015/03/27 有給取得状況の表示を公開                                 大谷 //
// 2015/04/08 対象期のデータがない場合前期のデータを表示するように変更      //
//            期の切り替わり時、有給更新情報待ちに対応する為           大谷 //
// 2015/05/27 有給のデータがない場合に、有給検索で抜き出されてしまうのを    //
//            修正                                                     大谷 //
// 2015/06/30 不在者の検索から五十嵐顧問、白井社長を除外               大谷 //
// 2015/07/30 検索方法に年齢順(高い順)職位に社員全て・パート全てを追加 大谷 //
// 2015/08/03 デフォルトの表示順を部門コード表順、役職の高い順に変更   大谷 //
// 2015/11/17 情報の変更などから戻った際に、検索が消える不具合を訂正   大谷 //
// 2016/08/05 暫定的に川zｱさん（300055）が登録できるように変更              //
//            したが、ユーザーレベルで対応のため削除                   大谷 //
// 2019/01/31 暫定的に平石さん（300551）が登録できるように変更         大谷 //
// 2019/03/13 不在者KIND_ABSENCEの条件修正。森社長を除外                    //
//            アルバイト・その他(横川)・日東工器を除外                 大谷 //
// 2019/07/25 有給5日情報の表示追加                                    大谷 //
// 2019/09/17 今期目安日数の表示を追加                                 大谷 //
// 2019/11/27 limitを500に(工場長指示 なしにすると訂正箇所が増えるので)大谷 //
// 2019/12/06 現在年齢と経歴表示の表示権限を工場長指示で変更           大谷 //
// 2019/12/17 出向除くすべてでその他90と日東工器95を                        //
//            表示しないように変更                                     大谷 //
// 2020/03/10 職位に課長代理以上を追加                                 大谷 //
// 2020/05/22 生産部に製造課が含まれていたので技術部に分離             大谷 //
// 2020/07/03 今期取得目安は基準日が今期のみに表示するよう変更              //
//            前期が基準日の場合、計算がおかしくなる。前期が基準日の場合    //
//            日数が合算される為、目安はいらない。                     大谷 //
// 2021/03/30 基準日の最終日を計算で出していたので取得へ変更           大谷 //
// 2021/06/28 22期より年6日で色々計算するように変更                    大谷 //
//////////////////////////////////////////////////////////////////////////////
// access_log("view_userinfo_user.php");       // Script Name 手動設定
access_log(substr(__FILE__, strlen($_SERVER['DOCUMENT_ROOT'])));
echo view_file_name(__FILE__);
if (isset($_POST['offset'])) {
    $offset = $_POST['offset'];
} else {
    $offset = 0;
}
?>
<table width='100%'>
    <tr><td colspan='2' bgcolor='#003e7c' align='center' class='nasiji'>
        <font color='#ffffff'>ユーザーの検索結果</font>
        </td>
    </tr>
<?php
/* サイトへの表示件数 */
    //define('VIEW_LIMIT', '10');
    define('VIEW_LIMIT', '500');
    if (isset($_POST['lookup_next'])) {
        if ($_POST['resrows'] >= ($offset + VIEW_LIMIT))
            $offset += VIEW_LIMIT;
    } elseif (isset($_POST['lookup_prev'])) {
        if (0 <= $offset - VIEW_LIMIT)
            $offset -= VIEW_LIMIT;
    }
$_POST["offset"] = $offset;
    /* クエリーを生成 */
    $timeDate = date('Ymd');
    if ($_SESSION["lookupkeykind"] == KIND_DISABLE) {
        $query="select ud.uid,ud.name,ud.kana,ud.retire_date,ud.photo,sm.section_name,pm.position_name,ud.pid,ud.sid from user_detailes ud,section_master sm,position_master pm" .
            " where ud.sid=sm.sid and ud.retire_date is null and ud.uid!='000000' and ud.pid=pm.pid";
    } else {
        if ($_SESSION["lookupkeykind"] == KIND_USERID) {
            $query="select ud.uid,ud.name,ud.kana,ud.retire_date,ud.photo,sm.section_name,pm.position_name,ud.pid,ud.sid from user_detailes ud,section_master sm,position_master pm" .
                " where ud.uid='$key' and ud.retire_date is null and ud.uid!='000000' and ud.sid=sm.sid and ud.pid=pm.pid";
        } elseif ($_SESSION["lookupkeykind"] == KIND_FULLNAME) {
            $query="select ud.uid,ud.name,ud.kana,ud.retire_date,ud.photo,sm.section_name,pm.position_name,ud.pid,ud.sid from user_detailes ud,section_master sm,position_master pm" .
                " where (ud.name=$key or ud.kana=$key or ud.spell=$key) and ud.sid=sm.sid and ud.retire_date is null and ud.uid!='000000' and ud.pid=pm.pid";
        } elseif ($_SESSION["lookupkeykind"] == KIND_ABSENCE) {
            $query="select ud.uid,ud.name,ud.kana,ud.retire_date,ud.photo,sm.section_name,pm.position_name,ud.pid,ud.sid from user_detailes ud left outer join timepro_daily_data on uid=substr(timepro, 3, 6) and $timeDate=substr(timepro, 17, 8),section_master sm,position_master pm" .
            " where (substr(timepro, 33, 4)='0000' or substr(timepro, 33, 4) IS NULL) and ud.sid=sm.sid and ud.sid!=90 and ud.sid!=95 and ud.retire_date is null and ud.uid!='000000' and ud.uid!='002321' and ud.uid!='010367' and ud.uid!='012866' and ud.pid=pm.pid and ud.pid!=15";
        } elseif ($_SESSION["lookupkeykind"] == KIND_AGE) {
            $query="select ud.uid,ud.name,ud.kana,ud.retire_date,ud.photo,sm.section_name,pm.position_name,ud.pid,ud.sid from user_detailes ud,section_master sm,position_master pm" .
            " where ud.sid=sm.sid and ud.retire_date is null and ud.uid!='000000' and ud.uid!='002321' and ud.uid!='010367' and ud.pid=pm.pid";
        } else {
            $query="select ud.uid,ud.name,ud.kana,ud.retire_date,ud.photo,sm.section_name,pm.position_name,ud.pid,ud.sid from user_detailes ud,section_master sm,position_master pm" .
                " where (ud.name like $key or ud.kana like $key or ud.spell like $key) and ud.sid=sm.sid  and ud.retire_date is null and ud.uid!='000000' and ud.pid=pm.pid";
        }
    }
    /* 所属による条件 */
    if ($_SESSION['lookupsection'] == (-2)) {
        $query .= " and ud.sid<>31 and ud.sid<>90 and ud.sid<>95";        // 出向を除く全て
    } elseif ($_SESSION["lookupsection"]!=KIND_DISABLE) {
        $query .= " and ud.sid=" . $_SESSION["lookupsection"];
    }
    /* 職位による条件 */
    if($_SESSION["lookupposition"]==KIND_DISABLE) {
    } elseif($_SESSION["lookupposition"]==KIND_EMPLOYEE) {
        // 10:一般 31:係長Ｂ 32:係長Ａ 33:エキスパート３級 34:エキスパート２級 35:エキスパート１級 46:課長代理 47:部長代理 50:課長 60:副部長 70:部長 95:副工場長 110:取締役工場長
        $query .= " and (ud.pid=10 or ud.pid=31 or ud.pid=32 or ud.pid=33 or ud.pid=34 or ud.pid=35 or ud.pid=46 or ud.pid=47 or ud.pid=50 or ud.pid=60 or ud.pid=70 or ud.pid=95 or ud.pid=110)";
    } elseif($_SESSION["lookupposition"]==KIND_PARTTIME) {
        $query .= " and (ud.pid=5 or ud.pid=6)";    // 5:パート 6:パートスタッフ
    } elseif($_SESSION["lookupposition"]==KIND_MANAGE) {
        // 46:課長代理 47:部長代理 50:課長 60:副部長 70:部長 95:副工場長 110:取締役工場長
        $query .= " and (ud.pid=46 or ud.pid=47 or ud.pid=50 or ud.pid=60 or ud.pid=70 or ud.pid=95 or ud.pid=110)";
    } else {
        $query .= " and ud.pid=" . $_SESSION["lookupposition"];
    }
    /* 入社年度での条件 */
    if($_SESSION["lookupentry"]!=KIND_DISABLE)
        // $query .= " and to_char(ud.enterdate,'YYYY')='" . $_SESSION["lookupentry"] . "'";
        $query .= " and ud.enterdate like '" . $_SESSION["lookupentry"] . "%'";     // Var7.2.1にUPしたらto_charが使えなくなったため
    /* 資格による条件 */
    if($_SESSION["lookupcapacity"]!=KIND_DISABLE)
        $query .=" and exists (select * from user_capacity uc where ud.uid=uc.uid and uc.cid=" . $_SESSION["lookupcapacity"] . ")";
    /* 教育による条件 */
    if($_SESSION["lookupreceive"]!=KIND_DISABLE)
        $query .=" and exists (select * from user_receive ur where ud.uid=ur.uid and ur.rid=" . $_SESSION["lookupreceive"] . ")";
    if($_SESSION["lookupkeykind"]!=KIND_AGE) {
        //$query .=" order by sm.section_name ASC, pm.pid DESC, ud.uid ASC";
        // 以下で所属順に並び替え 部門コード表の上から 顧問は全社共通（栃木日東工器）の次に配置
        // ２行目が職位の高い順、顧問は副工場長の下に。その後は社員No.順
        $query .=" order by CASE sm.sid WHEN 99 THEN 1 ELSE 2 END, CASE sm.sid WHEN 80 THEN 1 ELSE 2 END, CASE sm.sid WHEN 9 THEN 1 ELSE 2 END, CASE sm.sid WHEN 31 THEN 1 ELSE 2 END, CASE sm.sid WHEN 5 THEN 1 ELSE 2 END, CASE sm.sid WHEN 19 THEN 1 ELSE 2 END, CASE sm.sid WHEN 38 THEN 1 ELSE 2 END, CASE sm.sid WHEN 18 THEN 1 ELSE 2 END, CASE sm.sid WHEN 4 THEN 1 ELSE 2 END, CASE sm.sid WHEN 8 THEN 1 ELSE 2 END, CASE sm.sid WHEN 34 THEN 1 ELSE 2 END, CASE sm.sid WHEN 35 THEN 1 ELSE 2 END, CASE sm.sid WHEN 32 THEN 1 ELSE 2 END, CASE sm.sid WHEN 2 THEN 1 ELSE 2 END, CASE sm.sid WHEN 3 THEN 1 ELSE 2 END, sm.sid,";
        $query .=" CASE pm.pid WHEN 120 THEN 1 ELSE 2 END, CASE pm.pid WHEN 110 THEN 1 ELSE 2 END, CASE pm.pid WHEN 95 THEN 1 ELSE 2 END, CASE pm.pid WHEN 130 THEN 1 ELSE 2 END, CASE pm.pid WHEN 70 THEN 1 ELSE 2 END, CASE pm.pid WHEN 47 THEN 1 ELSE 2 END, CASE pm.pid WHEN 60 THEN 1 ELSE 2 END, CASE pm.pid WHEN 50 THEN 1 ELSE 2 END, CASE pm.pid WHEN 46 THEN 1 ELSE 2 END, CASE pm.pid WHEN 35 THEN 1 ELSE 2 END, CASE pm.pid WHEN 34 THEN 1 ELSE 2 END, CASE pm.pid WHEN 33 THEN 1 ELSE 2 END, CASE pm.pid WHEN 32 THEN 1 ELSE 2 END, CASE pm.pid WHEN 31 THEN 1 ELSE 2 END, CASE pm.pid WHEN 10 THEN 1 ELSE 2 END, CASE pm.pid WHEN 9 THEN 1 ELSE 2 END, CASE pm.pid WHEN 8 THEN 1 ELSE 2 END, CASE pm.pid WHEN 6 THEN 1 ELSE 2 END, CASE pm.pid WHEN 5 THEN 1 ELSE 2 END, pm.pid, ud.uid ASC";
    } else {
        $query .=" order by ud.birthday ASC, sm.section_name ASC, pm.pid DESC, ud.uid ASC";
    }
    $res=array();
    $rows=getResult($query,$res);
    
    if($_SESSION['lookupyukyufive'] != KIND_DISABLE) {
    // 有給検索除外処理（検索件数のカウント用）
    $timeDate  = date('Ym');
    $today_ym  = date('Ymd');
    $tmp       = $timeDate - 195603;     // 期計算係数195603
    $tmp       = $tmp / 100;             // 年の部分を取り出す
    $ki        = ceil($tmp);             // roundup と同じ
    $nk_ki = $ki + 44;
    $yyyy = substr($timeDate, 0,4);
    $mm   = substr($timeDate, 4,2);
    // 年度計算
    if ($mm < 4) {              // 1～3月の場合
        $business_year = $yyyy - 1;
    } else {
        $business_year = $yyyy;
    }
    $out_count = 0;
    $yukyu_c   = 0;
    $res_y     = array();
    if($_SESSION['lookupyukyufive'] == KIND_DISABLE){
        for ($r=0; $r<$rows; $r++) {
            $res_y[$yukyu_c] = $res[$r];
            $yukyu_c        += 1;
        }
    } else {
        for ($r=0; $r<$rows; $r++) {
            if ($res[$r]['uid'] == '015806' || $res[$r]['uid'] == '019984' || $res[$r]['uid'] == '010367' || $res[$r]['uid'] == '002321') {
                continue;
            } else {
                if (getCheckAuthority(41)) {
                    if ($res_y[$r]['sid'] == 5 && $res_y[$r]['sid'] == 9 && $res_y[$r]['sid'] == 19 && $res_y[$r]['sid'] == 31) {
                        continue;
                    }
                } elseif (getCheckAuthority(42)) {
                    if ($res[$r]['sid'] != 4 && $res[$r]['sid'] != 18 && $res[$r]['sid'] != 38) {
                        continue;
                    }
                } elseif (getCheckAuthority(43)) {
                    if ($res[$r]['sid'] != 2 && $res[$r]['sid'] != 3 && $res[$r]['sid'] != 8 && $res[$r]['sid'] != 32) {
                        continue;
                    }
                } elseif (getCheckAuthority(44)) {
                    if ($res[$r]['sid'] != 5) {
                        continue;
                    }
                } elseif (getCheckAuthority(45)) {
                    if ($res[$r]['sid'] != 19) {
                        continue;
                    }
                } elseif (getCheckAuthority(46)) {
                    if ($res[$r]['sid'] != 18) {
                        continue;
                    }
                } elseif (getCheckAuthority(47)) {
                    if ($res[$r]['sid'] != 4) {
                        continue;
                    }
                } elseif (getCheckAuthority(48)) {
                    if ($res[$r]['sid'] != 34) {
                        continue;
                    }
                } elseif (getCheckAuthority(49)) {
                    if ($res[$r]['sid'] != 35) {
                        continue;
                    }
                } elseif (getCheckAuthority(50)) {
                    if ($res[$r]['sid'] != 32) {
                        continue;
                    }
                } elseif (getCheckAuthority(51)) {
                    if ($res[$r]['sid'] != 2) {
                        continue;
                    }
                } elseif (getCheckAuthority(52)) {
                    if ($res[$r]['sid'] != 3) {
                        continue;
                    }
                } elseif (getCheckAuthority(55)) {
                    if ($res[$r]['sid'] != 17 && $res[$r]['sid'] != 34 && $res[$r]['sid'] != 35) {
                        continue;
                    }
                }
                // 基準日のデータがない場合は除外
                $query = sprintf("SELECT uid,reference_ym,end_ref_ym FROM five_yukyu_master WHERE uid='%s' and business_year=%d", $res[$r]['uid'], $business_year);
                $rows_c=getResult($query,$res_c);
                $r_ym   = substr($res_c[0][1], 0,6);
                $r_mm   = substr($res_c[0][1], 4,2);
                $r_dd   = substr($res_c[0][1], 6,2);
                if ($r_mm == 1) {
                    $r_ym = $r_ym + 11;
                } else {
                    $r_ym = $r_ym + 99;
                }
                if ($r_dd == 1) {
                    $end_rmd = $r_ym . '31';
                } else {
                    $end_rmd = $r_ym . $r_dd - 1;
                }
                //$end_rmd = $res_c[0][1] + 10000;
                $end_rmd = $res_c[0][2];
                if ($rows_c > 0) {
                    $query = "
                        SELECT   uid          AS 社員番号 --00 
                                ,working_date AS 取得日   --01
                                ,working_day  AS 曜日     --02
                                ,absence      AS 不在理由 --03
                                ,str_mc       AS 出勤ＭＣ --04
                                ,end_mc       AS 退勤ＭＣ --05
                        FROM working_hours_report_data_new WHERE uid='{$res[$r]['uid']}' and working_date >= {$res_c[0][1]} and
                        working_date < {$end_rmd} and absence = '11';
                    ";
                    $f_yukyu=getResult2($query, $f_yukyu);
                    $query = "
                        SELECT   uid          AS 社員番号 --00 
                                ,working_date AS 取得日   --01
                                ,working_day  AS 曜日     --02
                                ,absence      AS 不在理由 --03
                                ,str_mc       AS 出勤ＭＣ --04
                                ,end_mc       AS 退勤ＭＣ --05
                        FROM working_hours_report_data_new WHERE uid='{$res[$r]['uid']}' and working_date >= {$res_c[0][1]} and
                        working_date < {$end_rmd} and ( str_mc = '41' or end_mc = '42' );
                    ";
                    $h_yukyu=getResult2($query, $h_yukyu) * 0.5;
                    $five_num = $f_yukyu + $h_yukyu;
                    if($_SESSION['lookupyukyufive'] == KIND_DAYUP) {
                        if ($five_num >= $_SESSION["lookupyukyuf"]) {
                            $res_y[$yukyu_c] = $res[$r];
                            $yukyu_c        += 1;
                        }
                    } elseif($_SESSION['lookupyukyufive'] == KIND_DAYDOWN) {
                        if ($five_num < $_SESSION["lookupyukyuf"]) {
                            $res_y[$yukyu_c] = $res[$r];
                            $yukyu_c        += 1;
                        }
                    }
                    $query = "
                        SELECT   reference_ym          AS 基準開始日 --00
                                ,end_ref_ym            AS 基準終了日 --01
                                ,need_day              AS 必要日数   --02
                        FROM five_yukyu_master WHERE uid='{$res[$r]['uid']}' and business_year={$business_year}
                    ";
                    $rows_ne=getResult($query,$res_ne);
                    $s_yy       = substr($res_ne[0][0], 0,4);                   // 基準開始日：年
                    $s_mm       = substr($res_ne[0][1], 4,2);                   // 基準開始日：月
                    $s_dd       = substr($res_ne[0][2], 6,2);                   // 基準開始日：日
                    $s_ref_date = $s_yy . "年" . $s_mm . "月" . $s_dd . "日";   // 基準開始日：年月日
                    $e_yy       = substr($res_ne[0][0], 0,4);                   // 基準終了日：年
                    $e_mm       = substr($res_ne[0][1], 4,2);                   // 基準終了日：月
                    $e_dd       = substr($res_ne[0][2], 6,2);                   // 基準終了日：日
                    $e_ref_date = $e_yy . "年" . $e_mm . "月" . $e_dd . "日";   // 基準終了日：年月日
                    $need_day   = $res_ne[0][2];
                }
            }
        }
    }
    } else {
    // 有給検索除外処理（検索件数のカウント用）
    $timeDate  = date('Ym');
    $today_ym  = date('Ymd');
    $tmp       = $timeDate - 195603;     // 期計算係数195603
    $tmp       = $tmp / 100;             // 年の部分を取り出す
    $ki        = ceil($tmp);             // roundup と同じ
    // 対象期のデータがない場合前期末のデータを照会
    $query_chk = "
              SELECT
                     current_day    AS 当期有給日数     -- 0
                    ,holiday_rest   AS 当期有給残       -- 1
                    ,half_holiday   AS 半日有給回数     -- 2
                    ,time_holiday   AS 時間休取得分     -- 3
                    ,time_limit     AS 時間有給限度     -- 4
                    ,web_ymd        AS 更新年月日       -- 5
              FROM holiday_rest_master
              WHERE ki={$ki};
              ";
    $rows_chk=getResult($query_chk,$res_chk);
    if ($rows_chk <= 0) {
        $ki = $ki - 1;
    }
    $out_count = 0;
    $yukyu_c   = 0;
    $res_y     = array();
    if($_SESSION['lookupyukyukind'] == KIND_DAYUP) {
        $query_yukyu =" and (current_day-holiday_rest)<" . $_SESSION["lookupyukyu"];
    } elseif($_SESSION['lookupyukyukind'] == KIND_DAYDOWN) {
        $query_yukyu =" and (current_day-holiday_rest)>=" . $_SESSION["lookupyukyu"];
    } elseif($_SESSION['lookupyukyukind'] == KIND_PERUP) {
        $query_yukyu =" and (((current_day-holiday_rest)/current_day)*100)<" . $_SESSION["lookupyukyu"];
    } elseif($_SESSION['lookupyukyukind'] == KIND_PERDOWN) {
        $query_yukyu =" and (((current_day-holiday_rest)/current_day)*100)>=" . $_SESSION["lookupyukyu"];
    } else {
        $query_yukyu ="";
    }
    if($_SESSION['lookupyukyukind'] == KIND_DISABLE){
        for ($r=0; $r<$rows; $r++) {
            $res_y[$yukyu_c] = $res[$r];
            $yukyu_c        += 1;
        }
    } else {
        for ($r=0; $r<$rows; $r++) {
            if ($res[$r]['uid'] == '015806' || $res[$r]['uid'] == '019984' || $res[$r]['uid'] == '010367' || $res[$r]['uid'] == '002321') {
                continue;
            } else {
                if (getCheckAuthority(41)) {
                    if ($res_y[$r]['sid'] == 5 && $res_y[$r]['sid'] == 9 && $res_y[$r]['sid'] == 19 && $res_y[$r]['sid'] == 31) {
                        continue;
                    }
                } elseif (getCheckAuthority(42)) {
                    if ($res[$r]['sid'] != 4 && $res[$r]['sid'] != 18 && $res[$r]['sid'] != 38) {
                        continue;
                    }
                } elseif (getCheckAuthority(43)) {
                    if ($res[$r]['sid'] != 2 && $res[$r]['sid'] != 3 && $res[$r]['sid'] != 8 && $res[$r]['sid'] != 32) {
                        continue;
                    }
                } elseif (getCheckAuthority(44)) {
                    if ($res[$r]['sid'] != 5) {
                        continue;
                    }
                } elseif (getCheckAuthority(45)) {
                    if ($res[$r]['sid'] != 19) {
                        continue;
                    }
                } elseif (getCheckAuthority(46)) {
                    if ($res[$r]['sid'] != 18) {
                        continue;
                    }
                } elseif (getCheckAuthority(47)) {
                    if ($res[$r]['sid'] != 4) {
                        continue;
                    }
                } elseif (getCheckAuthority(48)) {
                    if ($res[$r]['sid'] != 34) {
                        continue;
                    }
                } elseif (getCheckAuthority(49)) {
                    if ($res[$r]['sid'] != 35) {
                        continue;
                    }
                } elseif (getCheckAuthority(50)) {
                    if ($res[$r]['sid'] != 32) {
                        continue;
                    }
                } elseif (getCheckAuthority(51)) {
                    if ($res[$r]['sid'] != 2) {
                        continue;
                    }
                } elseif (getCheckAuthority(52)) {
                    if ($res[$r]['sid'] != 3) {
                        continue;
                    }
                } elseif (getCheckAuthority(55)) {
                    if ($res[$r]['sid'] != 17 && $res[$r]['sid'] != 34 && $res[$r]['sid'] != 35) {
                        continue;
                    }
                }
                // 有給のデータがない場合は除外
                $query = "
                            SELECT
                                 current_day    AS 当期有給日数     -- 0
                                ,holiday_rest   AS 当期有給残       -- 1
                                ,half_holiday   AS 半日有給回数     -- 2
                                ,time_holiday   AS 時間休取得分     -- 3
                                ,time_limit     AS 時間有給限度     -- 4
                                ,web_ymd        AS 更新年月日       -- 5
                            FROM holiday_rest_master
                            WHERE uid='{$res[$r]['uid']}' and ki={$ki};
                        ";
                $rows_c=getResult($query,$res_c);
                if ($rows_c > 0) {
                    $query = "
                                SELECT
                                     current_day    AS 当期有給日数     -- 0
                                    ,holiday_rest   AS 当期有給残       -- 1
                                    ,half_holiday   AS 半日有給回数     -- 2
                                    ,time_holiday   AS 時間休取得分     -- 3
                                    ,time_limit     AS 時間有給限度     -- 4
                                    ,web_ymd        AS 更新年月日       -- 5
                                FROM holiday_rest_master
                                WHERE uid='{$res[$r]['uid']}' and ki={$ki}{$query_yukyu};
                            ";
                    $rows_c=getResult($query,$res_c);
                    if ($rows_c <= 0) {
                        $res_y[$yukyu_c] = $res[$r];
                        $yukyu_c        += 1;
                    }
                }
            }
        }
    }
    }
    $rows_y     = count($res_y);
    echo("<tr><td colspan=2>従業員情報  検索件数 <font size=+1 color='#ff7e00'><b>$rows_y</b></font> 件</td></tr>");
        echo("<tr>\n");
        echo("<td><form method='post' action='emp_menu.php?func=" . FUNC_LOOKUP . "'><table width='100%'>\n");
        echo("<input type='hidden' name='func' value='" . FUNC_LOOKUP . "'>\n");
        echo("<input type='hidden' name='resrows' value=$rows>\n");
        echo("<input type='hidden' name='retireflg' value=0>\n");
        echo("<input type='hidden' name='offset' value=$offset></td><tr>\n");
        if(0<=$offset-VIEW_LIMIT)
            echo("<td align='left'><input disable type='submit' name='lookup_prev' value='前へ'></td>\n");
        if($rows>$offset+VIEW_LIMIT){
            if(0==$offset)
                echo("<td colspan=2 align='right'><input type='submit' name='lookup_next' value='次へ'></td>\n");
            else
                echo("<td align='right'><input type='submit' name='lookup_next' value='次へ'></td>\n");
        }
        echo("</tr></table></form>\n");
        echo("</tr>\n");

    if ($rows_y) {
        for ($r=$offset; $r<$rows_y && $r<$offset+VIEW_LIMIT; $r++) {
            // 有給残計算
            $timeDate = date('Ym');
            $today_ym = date('Ymd');
            $tmp = $timeDate - 195603;     // 期計算係数195603
            $tmp = $tmp / 100;             // 年の部分を取り出す
            $ki  = ceil($tmp);             // roundup と同じ
            $nk_ki = $ki + 44;
            $yyyy = substr($timeDate, 0,4);
            $mm   = substr($timeDate, 4,2);
            // 年度計算
            if ($mm < 4) {              // 1～3月の場合
                $business_year = $yyyy - 1;
            } else {
                $business_year = $yyyy;
            }
            // 対象期のデータがない場合前期末のデータを照会
            $query_chk = "
                          SELECT
                                 current_day    AS 当期有給日数     -- 0
                                ,holiday_rest   AS 当期有給残       -- 1
                                ,half_holiday   AS 半日有給回数     -- 2
                                ,time_holiday   AS 時間休取得分     -- 3
                                ,time_limit     AS 時間有給限度     -- 4
                                ,web_ymd        AS 更新年月日       -- 5
                          FROM holiday_rest_master
                          WHERE ki={$ki};
                          ";
            $rows_chk=getResult($query_chk,$res_chk);
            if ($rows_chk <= 0) {
                $ki = $ki - 1;
            }
            $query = "
                    SELECT
                         current_day    AS 当期有給日数     -- 0
                        ,holiday_rest   AS 当期有給残       -- 1
                        ,half_holiday   AS 半日有給回数     -- 2
                        ,time_holiday   AS 時間休取得分     -- 3
                        ,time_limit     AS 時間有給限度     -- 4
                        ,web_ymd        AS 更新年月日       -- 5
                    FROM holiday_rest_master
                    WHERE uid='{$res_y[$r]['uid']}' and ki={$ki};
                ";
            getResult2($query, $yukyu);
            $kintai_ym         = substr($yukyu[0][5], 0, 4) . "年" . substr($yukyu[0][5], 4, 2) . "月" . substr($yukyu[0][5], 6, 2) . "日";
            //if ($yukyu[0][0]-$yukyu[0][1] > 5) {
            //    continue;
            //}
            $query_chk = sprintf("SELECT uid,reference_ym,end_ref_ym FROM five_yukyu_master WHERE uid='%s' and business_year=%d", $res_y[$r]['uid'], $business_year);
            $five_num = 0;
            $indication_flg = 0;                                        // 目安表示フラグ
            if (getResult($query_chk,$res_chk) <= 0) {    // トランザクション内での 照会専用クエリー 
                $five_num   = '--';
                $s_ref_date = '--';
                $e_ref_date = '--';
                $need_day   = '--';
            } else {
                $r_yy   = substr($res_chk[0][1], 0,4);
                $r_mm   = substr($res_chk[0][1], 4,2);
                $r_md   = substr($res_chk[0][1], 4,4);
                if ($r_md='0401') {
                    $end_rmd = $r_yy + 1;
                    $end_rmd = $end_rmd . '0331';
                } elseif($r_mm<4) {
                    $end_rmd = $r_yy + 1;
                    $end_rmd = $end_rmd . '0331';
                } elseif($r_mm>3) {
                    $end_rmd = $r_yy + 2;
                    $end_rmd = $end_rmd . '0331';
                }
                //$end_rmd = $res_chk[0][1] + 10000;
                $end_rmd = $res_chk[0][2];
                $query = "
                    SELECT   uid          AS 社員番号 --00 
                            ,working_date AS 取得日   --01
                            ,working_day  AS 曜日     --02
                            ,absence      AS 不在理由 --03
                            ,str_mc       AS 出勤ＭＣ --04
                            ,end_mc       AS 退勤ＭＣ --05
                    FROM working_hours_report_data_new WHERE uid='{$res_y[$r]['uid']}' and working_date >= {$res_chk[0][1]} and
                    working_date < {$end_rmd} and absence = '11';
                ";
                $f_yukyu=getResult2($query, $f_yukyu);
                $query = "
                    SELECT   uid          AS 社員番号 --00 
                            ,working_date AS 取得日   --01
                            ,working_day  AS 曜日     --02
                            ,absence      AS 不在理由 --03
                            ,str_mc       AS 出勤ＭＣ --04
                            ,end_mc       AS 退勤ＭＣ --05
                    FROM working_hours_report_data_new WHERE uid='{$res_y[$r]['uid']}' and working_date >= {$res_chk[0][1]} and
                    working_date < {$end_rmd} and ( str_mc = '41' or end_mc = '42' );
                ";
                $h_yukyu=getResult2($query, $h_yukyu) * 0.5;
                $five_num = $f_yukyu + $h_yukyu;
                $query = "
                    SELECT   reference_ym          AS 基準開始日 --00
                            ,end_ref_ym            AS 基準終了日 --01
                            ,need_day              AS 必要日数   --02
                    FROM five_yukyu_master WHERE uid='{$res_y[$r]['uid']}' and business_year={$business_year}
                ";
                $rows_ne=getResult($query,$res_ne);
                $s_yy       = substr($res_ne[0][0], 0,4);                   // 基準開始日：年
                $s_mm       = substr($res_ne[0][0], 4,2);                   // 基準開始日：月
                $s_dd       = substr($res_ne[0][0], 6,2);                   // 基準開始日：日
                $s_ym       = substr($res_ne[0][0], 0,6);                   // 基準開始日：年月
                $s_md       = substr($res_ne[0][0], 4,4);                   // 基準開始日：月日
                $s_ref_date = $s_yy . "年" . $s_mm . "月" . $s_dd . "日";   // 基準開始日：年月日
                $e_yy       = substr($res_ne[0][1], 0,4);                   // 基準終了日：年
                $e_mm       = substr($res_ne[0][1], 4,2);                   // 基準終了日：月
                $e_dd       = substr($res_ne[0][1], 6,2);                   // 基準終了日：日
                $e_ref_date = $e_yy . "年" . $e_mm . "月" . $e_dd . "日";   // 基準終了日：年月日
                $need_day   = $res_ne[0][2];
                $ki_str_ym  = $business_year . '04';                        // 当期期初年月
                if($s_ym > $ki_str_ym) {        // 開始日が前期の場合目安は要らない
                    if($s_md != '0401') {           // 目安表示判定 開始日が4/1ではなければ目安表示
                        $indication_flg = 1;        // フラグON
                        $ind_mm      = 0;                               // 計算用の月数をリセット
                        $ki_end_yy       = $business_year + 1;
                        $ki_end_ym       = $ki_end_yy . '03';    // 当期期末年月
                        $ki_end_ymd      = $ki_end_yy . '0331';  // 当期期末年月日
                        if ($ki_first_ymd >= 20210401) {
                            if ($s_mm < 3) {  // 1～3月
                                $ind_mm  = $ki_end_ym - $s_ym + 1 + 12;     // 計算用月数
                                $ind_day = round($ind_mm / 12 * 6, 1);      // 月数÷１２×６で日数計算
                                $ind_day = ceil($ind_day * 2) / 2 - 6;      // 0.5単位で切り上げ 来期分の6日をマイナス
                            } else {
                                $ind_mm  = $ki_end_ym - $s_ym - 87 + 12;    // 計算用月数
                                $ind_day = round($ind_mm /12 * 6, 1);       // 月数÷１２×６で日数計算
                                $ind_day = ceil($ind_day * 2) / 2 - 6;      // 0.5単位で切り上げ 来期分の6日をマイナス
                            }
                        } else {
                            if ($s_mm < 3) {  // 1～3月
                                $ind_mm  = $ki_end_ym - $s_ym + 1 + 12;     // 計算用月数
                                $ind_day = round($ind_mm / 12 * 5, 1);      // 月数÷１２×５で日数計算
                                $ind_day = ceil($ind_day * 2) / 2 - 5;      // 0.5単位で切り上げ 来期分の5日をマイナス
                            } else {
                                $ind_mm  = $ki_end_ym - $s_ym - 87 + 12;    // 計算用月数
                                $ind_day = round($ind_mm /12 * 5, 1);       // 月数÷１２×５で日数計算
                                $ind_day = ceil($ind_day * 2) / 2 - 5;      // 0.5単位で切り上げ 来期分の5日をマイナス
                            }
                        }
                    }
                }
            }
?>
    <tr><td valign='top'>
        <form method='post' action='emp_menu.php?func=<?php echo(FUNC_CHGUSERINFO); ?>'>
            <input type='hidden' name='func' value='<?php echo(FUNC_CHGUSERINFO); ?>'>
        <table width='100%' border='0'>
            <hr>
            <font color='#ff7e00'><b><?php echo($r+1); ?></b></font>
            <tr>
                <td width='15%'>社員No.</td>
                <td><?php echo($res_y[$r]['uid']); ?></td>
            <?php 
            if ($res_y[$r]['photo']) {
             ?>
                <?php $file = IND . $res_y[$r]['uid'] . '.gif?' . $uniq; ?>
                <td width='20%' rowspan='4' align='right'>
                    <img src='<?php echo $file ?>' width='76' height='112' border='0'
                        onClick='win_open("<?php echo $file ?>", 276, 412);'
                    >
                </td>
                <?php
                if ($res_y[$r]['uid']!='002321') {
                // 有給情報表示 部課長の場合自分の部・課員は見ることができる。 
                // 社長・工場長・管理部長・総務課長・人事担当者・システム管理者は全員分閲覧可能
                // 共通権限一覧
                // 権限：40 閲覧可能部署：すべて                 閲覧可能者：社長・工場長・副工場長・管理部長・総務課長・人事担当者・システム管理者
                // 権限：41 閲覧可能部署：管理部(5,9,19,31)      閲覧可能者：管理部長・管理副部長   以下＋上記権限40登録者
                // 権限：42 閲覧可能部署：技術部(4,18,38)        閲覧可能者：技術部長・技術副部長
                // 権限：43 閲覧可能部署：生産部(2,3,8,32)       閲覧可能者：生産部長・生産副部長
                // 権限：44 閲覧可能部署：総務課(5)              閲覧可能者：総務課長
                // 権限：45 閲覧可能部署：商品管理課(19)         閲覧可能者：商品管理課長
                // 権限：46 閲覧可能部署：品質保証課(18)         閲覧可能者：品質保証課長
                // 権限：47 閲覧可能部署：技術課(4)              閲覧可能者：技術課長
                // 権限：48 閲覧可能部署：製造１課(34)           閲覧可能者：製造１課長
                // 権限：49 閲覧可能部署：製造２課(35)           閲覧可能者：製造２課長
                // 権限：50 閲覧可能部署：生産管理課(32)         閲覧可能者：生産管理課長
                // 権限：51 閲覧可能部署：カプラ組立課(2)        閲覧可能者：カプラ組立課長
                // 権限：52 閲覧可能部署：リニア組立課(3)        閲覧可能者：リニア組立課長
                // 権限：55 閲覧可能部署：製造部(17,34,35)       閲覧可能者：製造部長・製造副部長
                // 閲覧者の権限確認
                if (getCheckAuthority(40)) {
                ?>
                    <td style='font-size:0.90em;'>
                        <?php echo "　{$kintai_ym}現在<BR>　有給残 <font color='red'>{$yukyu[0][1]}</font>/{$yukyu[0][0]}日<BR>　半休 <font color='red'>{$yukyu[0][2]}</font>/20 回　時間休 <font color='red'>{$yukyu[0][3]}</font>/{$yukyu[0][4]} 時間\n"; ?>
                    </td>
                <?php
                } elseif (getCheckAuthority(41)) {
                    if ($res_y[$r]['sid'] == 5 || $res_y[$r]['sid'] == 9 || $res_y[$r]['sid'] == 19 || $res_y[$r]['sid'] == 31) {
                    ?>
                        <td style='font-size:0.90em;'>
                            <?php echo "　{$kintai_ym}現在<BR>　有給残 <font color='red'>{$yukyu[0][1]}</font>/{$yukyu[0][0]}日<BR>　半休 <font color='red'>{$yukyu[0][2]}</font>/20 回　時間休 <font color='red'>{$yukyu[0][3]}</font>/{$yukyu[0][4]} 時間\n"; ?>
                        </td>
                    <?php
                    }
                } elseif (getCheckAuthority(42)) {
                    if ($res_y[$r]['sid'] == 4 || $res_y[$r]['sid'] == 18 || $res_y[$r]['sid'] == 38) {
                    ?>
                        <td style='font-size:0.90em;'>
                            <?php echo "　{$kintai_ym}現在<BR>　有給残 <font color='red'>{$yukyu[0][1]}</font>/{$yukyu[0][0]}日<BR>　半休 <font color='red'>{$yukyu[0][2]}</font>/20 回　時間休 <font color='red'>{$yukyu[0][3]}</font>/{$yukyu[0][4]} 時間\n"; ?>
                        </td>
                    <?php
                    }
                } elseif (getCheckAuthority(43)) {
                    if ($res_y[$r]['sid'] == 2 || $res_y[$r]['sid'] == 3 || $res_y[$r]['sid'] == 8 || $res_y[$r]['sid'] == 32) {
                    ?>
                        <td style='font-size:0.90em;'>
                            <?php echo "　{$kintai_ym}現在<BR>　有給残 <font color='red'>{$yukyu[0][1]}</font>/{$yukyu[0][0]}日<BR>　半休 <font color='red'>{$yukyu[0][2]}</font>/20 回　時間休 <font color='red'>{$yukyu[0][3]}</font>/{$yukyu[0][4]} 時間\n"; ?>
                        </td>
                    <?php
                    }
                } elseif (getCheckAuthority(44)) {
                    if ($res_y[$r]['sid'] == 5) {
                    ?>
                        <td style='font-size:0.90em;'>
                            <?php echo "　{$kintai_ym}現在<BR>　有給残 <font color='red'>{$yukyu[0][1]}</font>/{$yukyu[0][0]}日<BR>　半休 <font color='red'>{$yukyu[0][2]}</font>/20 回　時間休 <font color='red'>{$yukyu[0][3]}</font>/{$yukyu[0][4]} 時間\n"; ?>
                        </td>
                    <?php
                    }
                } elseif (getCheckAuthority(45)) {
                    if ($res_y[$r]['sid'] == 19) {
                    ?>
                        <td style='font-size:0.90em;'>
                            <?php echo "　{$kintai_ym}現在<BR>　有給残 <font color='red'>{$yukyu[0][1]}</font>/{$yukyu[0][0]}日<BR>　半休 <font color='red'>{$yukyu[0][2]}</font>/20 回　時間休 <font color='red'>{$yukyu[0][3]}</font>/{$yukyu[0][4]} 時間\n"; ?>
                        </td>
                    <?php
                    }
                } elseif (getCheckAuthority(46)) {
                    if ($res_y[$r]['sid'] == 18) {
                    ?>
                        <td style='font-size:0.90em;'>
                            <?php echo "　{$kintai_ym}現在<BR>　有給残 <font color='red'>{$yukyu[0][1]}</font>/{$yukyu[0][0]}日<BR>　半休 <font color='red'>{$yukyu[0][2]}</font>/20 回　時間休 <font color='red'>{$yukyu[0][3]}</font>/{$yukyu[0][4]} 時間\n"; ?>
                        </td>
                    <?php
                    }
                } elseif (getCheckAuthority(47)) {
                    if ($res_y[$r]['sid'] == 4) {
                    ?>
                        <td style='font-size:0.90em;'>
                            <?php echo "　{$kintai_ym}現在<BR>　有給残 <font color='red'>{$yukyu[0][1]}</font>/{$yukyu[0][0]}日<BR>　半休 <font color='red'>{$yukyu[0][2]}</font>/20 回　時間休 <font color='red'>{$yukyu[0][3]}</font>/{$yukyu[0][4]} 時間\n"; ?>
                        </td>
                    <?php
                    }
                } elseif (getCheckAuthority(48)) {
                    if ($res_y[$r]['sid'] == 34) {
                    ?>
                        <td style='font-size:0.90em;'>
                            <?php echo "　{$kintai_ym}現在<BR>　有給残 <font color='red'>{$yukyu[0][1]}</font>/{$yukyu[0][0]}日<BR>　半休 <font color='red'>{$yukyu[0][2]}</font>/20 回　時間休 <font color='red'>{$yukyu[0][3]}</font>/{$yukyu[0][4]} 時間\n"; ?>
                        </td>
                    <?php
                    }
                } elseif (getCheckAuthority(49)) {
                    if ($res_y[$r]['sid'] == 35) {
                    ?>
                        <td style='font-size:0.90em;'>
                            <?php echo "　{$kintai_ym}現在<BR>　有給残 <font color='red'>{$yukyu[0][1]}</font>/{$yukyu[0][0]}日<BR>　半休 <font color='red'>{$yukyu[0][2]}</font>/20 回　時間休 <font color='red'>{$yukyu[0][3]}</font>/{$yukyu[0][4]} 時間\n"; ?>
                        </td>
                    <?php
                    }
                } elseif (getCheckAuthority(50)) {
                    if ($res_y[$r]['sid'] == 32) {
                    ?>
                        <td style='font-size:0.90em;'>
                            <?php echo "　{$kintai_ym}現在<BR>　有給残 <font color='red'>{$yukyu[0][1]}</font>/{$yukyu[0][0]}日<BR>　半休 <font color='red'>{$yukyu[0][2]}</font>/20 回　時間休 <font color='red'>{$yukyu[0][3]}</font>/{$yukyu[0][4]} 時間\n"; ?>
                        </td>
                    <?php
                    }
                } elseif (getCheckAuthority(51)) {
                    if ($res_y[$r]['sid'] == 2) {
                    ?>
                        <td style='font-size:0.90em;'>
                            <?php echo "　{$kintai_ym}現在<BR>　有給残 <font color='red'>{$yukyu[0][1]}</font>/{$yukyu[0][0]}日<BR>　半休 <font color='red'>{$yukyu[0][2]}</font>/20 回　時間休 <font color='red'>{$yukyu[0][3]}</font>/{$yukyu[0][4]} 時間\n"; ?>
                        </td>
                    <?php
                    }
                } elseif (getCheckAuthority(52)) {
                    if ($res_y[$r]['sid'] == 3) {
                    ?>
                        <td style='font-size:0.90em;'>
                            <?php echo "　{$kintai_ym}現在<BR>　有給残 <font color='red'>{$yukyu[0][1]}</font>/{$yukyu[0][0]}日<BR>　半休 <font color='red'>{$yukyu[0][2]}</font>/20 回　時間休 <font color='red'>{$yukyu[0][3]}</font>/{$yukyu[0][4]} 時間\n"; ?>
                        </td>
                    <?php
                    }
                
                } elseif (getCheckAuthority(55)) {
                    if ($res_y[$r]['sid'] == 17 || $res_y[$r]['sid'] == 34 || $res_y[$r]['sid'] == 35) {
                    ?>
                        <td style='font-size:0.90em;'>
                            <?php echo "　{$kintai_ym}現在<BR>　有給残 <font color='red'>{$yukyu[0][1]}</font>/{$yukyu[0][0]}日<BR>　半休 <font color='red'>{$yukyu[0][2]}</font>/20 回　時間休 <font color='red'>{$yukyu[0][3]}</font>/{$yukyu[0][4]} 時間\n"; ?>
                        </td>
                    <?php
                    }
                }
                }
                /*
                // 以下は方針が決まるまでの暫定版として、総務課員のみ照会
                if ($_SESSION['User_ID'] == '300144' || $_SESSION['User_ID'] == '300055' || $_SESSION['User_ID'] == '010472' || $_SESSION['User_ID'] == '015806') {
                    
                ?>
                <td style='font-size:0.90em;'>
                    <?php echo "　{$kintai_ym}現在<BR>　有給残 <font color='red'>{$yukyu[0][1]}</font>/{$yukyu[0][0]}日<BR>　半休 <font color='red'>{$yukyu[0][2]}</font>/20 回　時間休 <font color='red'>{$yukyu[0][3]}</font>/{$yukyu[0][4]} 時間\n"; ?>
                </td>
                <?php
                }
                */
            }
            ?>
            </tr>
            <tr>
                <?php
                if (getCheckAuthority(40)) {
                ?>
                    <td width='15%'>　</td>
                        <td>　</td>
                        <?php
                        if ($indication_flg == 1) {
                        if ($today_ym >= 20210401) {
                        ?>
                        <td style='font-size:0.90em;'>
                            <?php echo "　有給6日以上取得<BR>　{$s_ref_date}<BR>　　～{$e_ref_date}<BR>　<font color='red'>{$five_num}</font>/{$need_day}日　<font color='red'><B>※今期取得目安{$ind_day}日</B></font>\n"; ?>
                        </td>
                        <?php
                        } else {
                        ?>
                        <td style='font-size:0.90em;'>
                            <?php echo "　有給5日以上取得<BR>　{$s_ref_date}<BR>　　～{$e_ref_date}<BR>　<font color='red'>{$five_num}</font>/{$need_day}日　<font color='red'><B>※今期取得目安{$ind_day}日</B></font>\n"; ?>
                        </td>
                        <?
                        }
                        } else {
                        if ($today_ym >= 20210401) {
                        ?>
                        <td style='font-size:0.90em;'>
                            <?php echo "　有給6日以上取得<BR>　{$s_ref_date}<BR>　　～{$e_ref_date}<BR>　<font color='red'>{$five_num}</font>/{$need_day}日\n"; ?>
                        </td>
                        <?php
                        } else {
                        ?>
                        <td style='font-size:0.90em;'>
                            <?php echo "　有給5日以上取得<BR>　{$s_ref_date}<BR>　　～{$e_ref_date}<BR>　<font color='red'>{$five_num}</font>/{$need_day}日\n"; ?>
                        </td>
                        <?
                        }
                        }
                        ?>
                <?php
                } elseif (getCheckAuthority(41)) {
                    if ($res_y[$r]['sid'] == 5 || $res_y[$r]['sid'] == 9 || $res_y[$r]['sid'] == 19 || $res_y[$r]['sid'] == 31) {
                    ?>
                        <td width='15%'>　</td>
                        <td>　</td>
                        <?php
                        if ($indication_flg == 1) {
                        if ($today_ym >= 20210401) {
                        ?>
                        <td style='font-size:0.90em;'>
                            <?php echo "　有給6日以上取得<BR>　{$s_ref_date}<BR>　　～{$e_ref_date}<BR>　<font color='red'>{$five_num}</font>/{$need_day}日　<font color='red'><B>※今期取得目安{$ind_day}日</B></font>\n"; ?>
                        </td>
                        <?php
                        } else {
                        ?>
                        <td style='font-size:0.90em;'>
                            <?php echo "　有給5日以上取得<BR>　{$s_ref_date}<BR>　　～{$e_ref_date}<BR>　<font color='red'>{$five_num}</font>/{$need_day}日　<font color='red'><B>※今期取得目安{$ind_day}日</B></font>\n"; ?>
                        </td>
                        <?php
                        }
                        } else {
                        if ($today_ym >= 20210401) {
                        ?>
                        <td style='font-size:0.90em;'>
                            <?php echo "　有給6日以上取得<BR>　{$s_ref_date}<BR>　　～{$e_ref_date}<BR>　<font color='red'>{$five_num}</font>/{$need_day}日\n"; ?>
                        </td>
                        <?php
                        } else {
                        ?>
                        <td style='font-size:0.90em;'>
                            <?php echo "　有給5日以上取得<BR>　{$s_ref_date}<BR>　　～{$e_ref_date}<BR>　<font color='red'>{$five_num}</font>/{$need_day}日\n"; ?>
                        </td>
                        <?php
                        }
                        }
                        ?>
                    <?php
                    }
                } elseif (getCheckAuthority(42)) {
                    if ($res_y[$r]['sid'] == 4 || $res_y[$r]['sid'] == 18 || $res_y[$r]['sid'] == 38) {
                    ?>
                        <td width='15%'>　</td>
                        <td>　</td>
                        <?php
                        if ($indication_flg == 1) {
                        if ($today_ym >= 20210401) {
                        ?>
                        <td style='font-size:0.90em;'>
                            <?php echo "　有給6日以上取得<BR>　{$s_ref_date}<BR>　　～{$e_ref_date}<BR>　<font color='red'>{$five_num}</font>/{$need_day}日　<font color='red'><B>※今期取得目安{$ind_day}日</B></font>\n"; ?>
                        </td>
                        <?php
                        } else {
                        ?>
                        <td style='font-size:0.90em;'>
                            <?php echo "　有給5日以上取得<BR>　{$s_ref_date}<BR>　　～{$e_ref_date}<BR>　<font color='red'>{$five_num}</font>/{$need_day}日　<font color='red'><B>※今期取得目安{$ind_day}日</B></font>\n"; ?>
                        </td>
                        <?php
                        }
                        } else {
                        if ($today_ym >= 20210401) {
                        ?>
                        <td style='font-size:0.90em;'>
                            <?php echo "　有給6日以上取得<BR>　{$s_ref_date}<BR>　　～{$e_ref_date}<BR>　<font color='red'>{$five_num}</font>/{$need_day}日\n"; ?>
                        </td>
                        <?php
                        } else {
                        ?>
                        <td style='font-size:0.90em;'>
                            <?php echo "　有給5日以上取得<BR>　{$s_ref_date}<BR>　　～{$e_ref_date}<BR>　<font color='red'>{$five_num}</font>/{$need_day}日\n"; ?>
                        </td>
                        <?php
                        }
                        }
                        ?>
                    <?php
                    }
                } elseif (getCheckAuthority(43)) {
                    if ($res_y[$r]['sid'] == 2 || $res_y[$r]['sid'] == 3 || $res_y[$r]['sid'] == 8 || $res_y[$r]['sid'] == 32) {
                    ?>
                        <td width='15%'>　</td>
                        <td>　</td>
                        <?php
                        if ($indication_flg == 1) {
                        if ($today_ym >= 20210401) {
                        ?>
                        <td style='font-size:0.90em;'>
                            <?php echo "　有給6日以上取得<BR>　{$s_ref_date}<BR>　　～{$e_ref_date}<BR>　<font color='red'>{$five_num}</font>/{$need_day}日　<font color='red'><B>※今期取得目安{$ind_day}日</B></font>\n"; ?>
                        </td>
                        <?php
                        } else {
                        ?>
                        <td style='font-size:0.90em;'>
                            <?php echo "　有給5日以上取得<BR>　{$s_ref_date}<BR>　　～{$e_ref_date}<BR>　<font color='red'>{$five_num}</font>/{$need_day}日　<font color='red'><B>※今期取得目安{$ind_day}日</B></font>\n"; ?>
                        </td>
                        <?php
                        }
                        } else {
                        if ($today_ym >= 20210401) {
                        ?>
                        <td style='font-size:0.90em;'>
                            <?php echo "　有給6日以上取得<BR>　{$s_ref_date}<BR>　　～{$e_ref_date}<BR>　<font color='red'>{$five_num}</font>/{$need_day}日\n"; ?>
                        </td>
                        <?php
                        } else {
                        ?>
                        <td style='font-size:0.90em;'>
                            <?php echo "　有給5日以上取得<BR>　{$s_ref_date}<BR>　　～{$e_ref_date}<BR>　<font color='red'>{$five_num}</font>/{$need_day}日\n"; ?>
                        </td>
                        <?php
                        }
                        }
                        ?>
                    <?php
                    }
                } elseif (getCheckAuthority(44)) {
                    if ($res_y[$r]['sid'] == 5) {
                    ?>
                        <td width='15%'>　</td>
                        <td>　</td>
                        <?php
                        if ($indication_flg == 1) {
                        if ($today_ym >= 20210401) {
                        ?>
                        <td style='font-size:0.90em;'>
                            <?php echo "　有給6日以上取得<BR>　{$s_ref_date}<BR>　　～{$e_ref_date}<BR>　<font color='red'>{$five_num}</font>/{$need_day}日　<font color='red'><B>※今期取得目安{$ind_day}日</B></font>\n"; ?>
                        </td>
                        <?php
                        } else {
                        ?>
                        <td style='font-size:0.90em;'>
                            <?php echo "　有給5日以上取得<BR>　{$s_ref_date}<BR>　　～{$e_ref_date}<BR>　<font color='red'>{$five_num}</font>/{$need_day}日　<font color='red'><B>※今期取得目安{$ind_day}日</B></font>\n"; ?>
                        </td>
                        <?php
                        }
                        } else {
                        if ($today_ym >= 20210401) {
                        ?>
                        <td style='font-size:0.90em;'>
                            <?php echo "　有給6日以上取得<BR>　{$s_ref_date}<BR>　　～{$e_ref_date}<BR>　<font color='red'>{$five_num}</font>/{$need_day}日\n"; ?>
                        </td>
                        <?php
                        } else {
                        ?>
                        <td style='font-size:0.90em;'>
                            <?php echo "　有給5日以上取得<BR>　{$s_ref_date}<BR>　　～{$e_ref_date}<BR>　<font color='red'>{$five_num}</font>/{$need_day}日\n"; ?>
                        </td>
                        <?php
                        }
                        }
                        ?>
                    <?php
                    }
                } elseif (getCheckAuthority(45)) {
                    if ($res_y[$r]['sid'] == 19) {
                    ?>
                        <td width='15%'>　</td>
                        <td>　</td>
                        <?php
                        if ($indication_flg == 1) {
                        if ($today_ym >= 20210401) {
                        ?>
                        <td style='font-size:0.90em;'>
                            <?php echo "　有給6日以上取得<BR>　{$s_ref_date}<BR>　　～{$e_ref_date}<BR>　<font color='red'>{$five_num}</font>/{$need_day}日　<font color='red'><B>※今期取得目安{$ind_day}日</B></font>\n"; ?>
                        </td>
                        <?php
                        } else {
                        ?>
                        <td style='font-size:0.90em;'>
                            <?php echo "　有給5日以上取得<BR>　{$s_ref_date}<BR>　　～{$e_ref_date}<BR>　<font color='red'>{$five_num}</font>/{$need_day}日　<font color='red'><B>※今期取得目安{$ind_day}日</B></font>\n"; ?>
                        </td>
                        <?php
                        }
                        } else {
                        if ($today_ym >= 20210401) {
                        ?>
                        <td style='font-size:0.90em;'>
                            <?php echo "　有給6日以上取得<BR>　{$s_ref_date}<BR>　　～{$e_ref_date}<BR>　<font color='red'>{$five_num}</font>/{$need_day}日\n"; ?>
                        </td>
                        <?php
                        } else {
                        ?>
                        <td style='font-size:0.90em;'>
                            <?php echo "　有給5日以上取得<BR>　{$s_ref_date}<BR>　　～{$e_ref_date}<BR>　<font color='red'>{$five_num}</font>/{$need_day}日\n"; ?>
                        </td>
                        <?php
                        }
                        }
                        ?>
                    <?php
                    }
                } elseif (getCheckAuthority(46)) {
                    if ($res_y[$r]['sid'] == 18) {
                    ?>
                        <td width='15%'>　</td>
                        <td>　</td>
                        <?php
                        if ($indication_flg == 1) {
                        if ($today_ym >= 20210401) {
                        ?>
                        <td style='font-size:0.90em;'>
                            <?php echo "　有給6日以上取得<BR>　{$s_ref_date}<BR>　　～{$e_ref_date}<BR>　<font color='red'>{$five_num}</font>/{$need_day}日　<font color='red'><B>※今期取得目安{$ind_day}日</B></font>\n"; ?>
                        </td>
                        <?php
                        } else {
                        ?>
                        <td style='font-size:0.90em;'>
                            <?php echo "　有給5日以上取得<BR>　{$s_ref_date}<BR>　　～{$e_ref_date}<BR>　<font color='red'>{$five_num}</font>/{$need_day}日　<font color='red'><B>※今期取得目安{$ind_day}日</B></font>\n"; ?>
                        </td>
                        <?php
                        }
                        } else {
                        if ($today_ym >= 20210401) {
                        ?>
                        <td style='font-size:0.90em;'>
                            <?php echo "　有給6日以上取得<BR>　{$s_ref_date}<BR>　　～{$e_ref_date}<BR>　<font color='red'>{$five_num}</font>/{$need_day}日\n"; ?>
                        </td>
                        <?php
                        } else {
                        ?>
                        <td style='font-size:0.90em;'>
                            <?php echo "　有給5日以上取得<BR>　{$s_ref_date}<BR>　　～{$e_ref_date}<BR>　<font color='red'>{$five_num}</font>/{$need_day}日\n"; ?>
                        </td>
                        <?php
                        }
                        }
                        ?>
                    <?php
                    }
                } elseif (getCheckAuthority(47)) {
                    if ($res_y[$r]['sid'] == 4) {
                    ?>
                        <td width='15%'>　</td>
                        <td>　</td>
                        <?php
                        if ($indication_flg == 1) {
                        if ($today_ym >= 20210401) {
                        ?>
                        <td style='font-size:0.90em;'>
                            <?php echo "　有給6日以上取得<BR>　{$s_ref_date}<BR>　　～{$e_ref_date}<BR>　<font color='red'>{$five_num}</font>/{$need_day}日　<font color='red'><B>※今期取得目安{$ind_day}日</B></font>\n"; ?>
                        </td>
                        <?php
                        } else {
                        ?>
                        <td style='font-size:0.90em;'>
                            <?php echo "　有給5日以上取得<BR>　{$s_ref_date}<BR>　　～{$e_ref_date}<BR>　<font color='red'>{$five_num}</font>/{$need_day}日　<font color='red'><B>※今期取得目安{$ind_day}日</B></font>\n"; ?>
                        </td>
                        <?php
                        }
                        } else {
                        if ($today_ym >= 20210401) {
                        ?>
                        <td style='font-size:0.90em;'>
                            <?php echo "　有給6日以上取得<BR>　{$s_ref_date}<BR>　　～{$e_ref_date}<BR>　<font color='red'>{$five_num}</font>/{$need_day}日\n"; ?>
                        </td>
                        <?php
                        } else {
                        ?>
                        <td style='font-size:0.90em;'>
                            <?php echo "　有給5日以上取得<BR>　{$s_ref_date}<BR>　　～{$e_ref_date}<BR>　<font color='red'>{$five_num}</font>/{$need_day}日\n"; ?>
                        </td>
                        <?php
                        }
                        }
                        ?>
                    <?php
                    }
                } elseif (getCheckAuthority(48)) {
                    if ($res_y[$r]['sid'] == 34) {
                    ?>
                        <td width='15%'>　</td>
                        <td>　</td>
                        <?php
                        if ($indication_flg == 1) {
                        if ($today_ym >= 20210401) {
                        ?>
                        <td style='font-size:0.90em;'>
                            <?php echo "　有給6日以上取得<BR>　{$s_ref_date}<BR>　　～{$e_ref_date}<BR>　<font color='red'>{$five_num}</font>/{$need_day}日　<font color='red'><B>※今期取得目安{$ind_day}日</B></font>\n"; ?>
                        </td>
                        <?php
                        } else {
                        ?>
                        <td style='font-size:0.90em;'>
                            <?php echo "　有給5日以上取得<BR>　{$s_ref_date}<BR>　　～{$e_ref_date}<BR>　<font color='red'>{$five_num}</font>/{$need_day}日　<font color='red'><B>※今期取得目安{$ind_day}日</B></font>\n"; ?>
                        </td>
                        <?php
                        }
                        } else {
                        if ($today_ym >= 20210401) {
                        ?>
                        <td style='font-size:0.90em;'>
                            <?php echo "　有給6日以上取得<BR>　{$s_ref_date}<BR>　　～{$e_ref_date}<BR>　<font color='red'>{$five_num}</font>/{$need_day}日\n"; ?>
                        </td>
                        <?php
                        } else {
                        ?>
                        <td style='font-size:0.90em;'>
                            <?php echo "　有給5日以上取得<BR>　{$s_ref_date}<BR>　　～{$e_ref_date}<BR>　<font color='red'>{$five_num}</font>/{$need_day}日\n"; ?>
                        </td>
                        <?php
                        }
                        }
                        ?>
                    <?php
                    }
                } elseif (getCheckAuthority(49)) {
                    if ($res_y[$r]['sid'] == 35) {
                    ?>
                        <td width='15%'>　</td>
                        <td>　</td>
                        <?php
                        if ($indication_flg == 1) {
                        if ($today_ym >= 20210401) {
                        ?>
                        <td style='font-size:0.90em;'>
                            <?php echo "　有給6日以上取得<BR>　{$s_ref_date}<BR>　　～{$e_ref_date}<BR>　<font color='red'>{$five_num}</font>/{$need_day}日　<font color='red'><B>※今期取得目安{$ind_day}日</B></font>\n"; ?>
                        </td>
                        <?php
                        } else {
                        ?>
                        <td style='font-size:0.90em;'>
                            <?php echo "　有給5日以上取得<BR>　{$s_ref_date}<BR>　　～{$e_ref_date}<BR>　<font color='red'>{$five_num}</font>/{$need_day}日　<font color='red'><B>※今期取得目安{$ind_day}日</B></font>\n"; ?>
                        </td>
                        <?php
                        }
                        } else {
                        if ($today_ym >= 20210401) {
                        ?>
                        <td style='font-size:0.90em;'>
                            <?php echo "　有給6日以上取得<BR>　{$s_ref_date}<BR>　　～{$e_ref_date}<BR>　<font color='red'>{$five_num}</font>/{$need_day}日\n"; ?>
                        </td>
                        <?php
                        } else {
                        ?>
                        <td style='font-size:0.90em;'>
                            <?php echo "　有給5日以上取得<BR>　{$s_ref_date}<BR>　　～{$e_ref_date}<BR>　<font color='red'>{$five_num}</font>/{$need_day}日\n"; ?>
                        </td>
                        <?php
                        }
                        }
                        ?>
                    <?php
                    }
                } elseif (getCheckAuthority(50)) {
                    if ($res_y[$r]['sid'] == 32) {
                    ?>
                        <td width='15%'>　</td>
                        <td>　</td>
                        <?php
                        if ($indication_flg == 1) {
                        if ($today_ym >= 20210401) {
                        ?>
                        <td style='font-size:0.90em;'>
                            <?php echo "　有給6日以上取得<BR>　{$s_ref_date}<BR>　　～{$e_ref_date}<BR>　<font color='red'>{$five_num}</font>/{$need_day}日　<font color='red'><B>※今期取得目安{$ind_day}日</B></font>\n"; ?>
                        </td>
                        <?php
                        } else {
                        ?>
                        <td style='font-size:0.90em;'>
                            <?php echo "　有給5日以上取得<BR>　{$s_ref_date}<BR>　　～{$e_ref_date}<BR>　<font color='red'>{$five_num}</font>/{$need_day}日　<font color='red'><B>※今期取得目安{$ind_day}日</B></font>\n"; ?>
                        </td>
                        <?php
                        }
                        } else {
                        if ($today_ym >= 20210401) {
                        ?>
                        <td style='font-size:0.90em;'>
                            <?php echo "　有給6日以上取得<BR>　{$s_ref_date}<BR>　　～{$e_ref_date}<BR>　<font color='red'>{$five_num}</font>/{$need_day}日\n"; ?>
                        </td>
                        <?php
                        } else {
                        ?>
                        <td style='font-size:0.90em;'>
                            <?php echo "　有給5日以上取得<BR>　{$s_ref_date}<BR>　　～{$e_ref_date}<BR>　<font color='red'>{$five_num}</font>/{$need_day}日\n"; ?>
                        </td>
                        <?php
                        }
                        }
                        ?>
                    <?php
                    }
                } elseif (getCheckAuthority(51)) {
                    if ($res_y[$r]['sid'] == 2) {
                    ?>
                        <td width='15%'>　</td>
                        <td>　</td>
                        <?php
                        if ($indication_flg == 1) {
                        if ($today_ym >= 20210401) {
                        ?>
                        <td style='font-size:0.90em;'>
                            <?php echo "　有給6日以上取得<BR>　{$s_ref_date}<BR>　　～{$e_ref_date}<BR>　<font color='red'>{$five_num}</font>/{$need_day}日　<font color='red'><B>※今期取得目安{$ind_day}日</B></font>\n"; ?>
                        </td>
                        <?php
                        } else {
                        ?>
                        <td style='font-size:0.90em;'>
                            <?php echo "　有給5日以上取得<BR>　{$s_ref_date}<BR>　　～{$e_ref_date}<BR>　<font color='red'>{$five_num}</font>/{$need_day}日　<font color='red'><B>※今期取得目安{$ind_day}日</B></font>\n"; ?>
                        </td>
                        <?php
                        }
                        } else {
                        if ($today_ym >= 20210401) {
                        ?>
                        <td style='font-size:0.90em;'>
                            <?php echo "　有給6日以上取得<BR>　{$s_ref_date}<BR>　　～{$e_ref_date}<BR>　<font color='red'>{$five_num}</font>/{$need_day}日\n"; ?>
                        </td>
                        <?php
                        } else {
                        ?>
                        <td style='font-size:0.90em;'>
                            <?php echo "　有給5日以上取得<BR>　{$s_ref_date}<BR>　　～{$e_ref_date}<BR>　<font color='red'>{$five_num}</font>/{$need_day}日\n"; ?>
                        </td>
                        <?php
                        }
                        }
                        ?>
                    <?php
                    }
                } elseif (getCheckAuthority(52)) {
                    if ($res_y[$r]['sid'] == 3) {
                    ?>
                        <td width='15%'>　</td>
                        <td>　</td>
                        <?php
                        if ($indication_flg == 1) {
                        if ($today_ym >= 20210401) {
                        ?>
                        <td style='font-size:0.90em;'>
                            <?php echo "　有給6日以上取得<BR>　{$s_ref_date}<BR>　　～{$e_ref_date}<BR>　<font color='red'>{$five_num}</font>/{$need_day}日　<font color='red'><B>※今期取得目安{$ind_day}日</B></font>\n"; ?>
                        </td>
                        <?php
                        } else {
                        ?>
                        <td style='font-size:0.90em;'>
                            <?php echo "　有給5日以上取得<BR>　{$s_ref_date}<BR>　　～{$e_ref_date}<BR>　<font color='red'>{$five_num}</font>/{$need_day}日　<font color='red'><B>※今期取得目安{$ind_day}日</B></font>\n"; ?>
                        </td>
                        <?php
                        }
                        } else {
                        if ($today_ym >= 20210401) {
                        ?>
                        <td style='font-size:0.90em;'>
                            <?php echo "　有給6日以上取得<BR>　{$s_ref_date}<BR>　　～{$e_ref_date}<BR>　<font color='red'>{$five_num}</font>/{$need_day}日\n"; ?>
                        </td>
                        <?php
                        } else {
                        ?>
                        <td style='font-size:0.90em;'>
                            <?php echo "　有給5日以上取得<BR>　{$s_ref_date}<BR>　　～{$e_ref_date}<BR>　<font color='red'>{$five_num}</font>/{$need_day}日\n"; ?>
                        </td>
                        <?php
                        }
                        }
                    }
                } elseif (getCheckAuthority(55)) {
                    if ($res_y[$r]['sid'] == 17 || $res_y[$r]['sid'] == 34 || $res_y[$r]['sid'] == 35) {
                    ?>
                        <td width='15%'>　</td>
                        <td>　</td>
                        <?php
                        if ($indication_flg == 1) {
                        if ($today_ym >= 20210401) {
                        ?>
                        <td style='font-size:0.90em;'>
                            <?php echo "　有給6日以上取得<BR>　{$s_ref_date}<BR>　　～{$e_ref_date}<BR>　<font color='red'>{$five_num}</font>/{$need_day}日　<font color='red'><B>※今期取得目安{$ind_day}日</B></font>\n"; ?>
                        </td>
                        <?php
                        } else {
                        ?>
                        <td style='font-size:0.90em;'>
                            <?php echo "　有給5日以上取得<BR>　{$s_ref_date}<BR>　　～{$e_ref_date}<BR>　<font color='red'>{$five_num}</font>/{$need_day}日　<font color='red'><B>※今期取得目安{$ind_day}日</B></font>\n"; ?>
                        </td>
                        <?php
                        }
                        } else {
                        if ($today_ym >= 20210401) {
                        ?>
                        <td style='font-size:0.90em;'>
                            <?php echo "　有給6日以上取得<BR>　{$s_ref_date}<BR>　　～{$e_ref_date}<BR>　<font color='red'>{$five_num}</font>/{$need_day}日\n"; ?>
                        </td>
                        <?php
                        } else {
                        ?>
                        <td style='font-size:0.90em;'>
                            <?php echo "　有給5日以上取得<BR>　{$s_ref_date}<BR>　　～{$e_ref_date}<BR>　<font color='red'>{$five_num}</font>/{$need_day}日\n"; ?>
                        </td>
                        <?php
                        }
                        }
                        ?>
                    <?php
                    }
                }
                ?>
            </tr>
            <tr>
                <td width='15%'>名前</td>
                <td>
                    <font size='1'><?php echo($res_y[$r]['kana']); ?></font><br><?php echo($res_y[$r]['name']); ?>
                </td>
                <?php
                    $timeDate = date('Ymd');
                    $query = "
                        SELECT
                            substr(start_time, 1, 2) || ':' || substr(start_time, 3, 2) AS start_time
                            ,
                            substr(end_time, 1, 2) || ':' || substr(end_time, 3, 2) AS end_time
                        FROM timepro_get_time(TEXT '{$res_y[$r]['uid']}', TEXT '{$timeDate}');
                    ";
                    getResult2($query, $timePro);
                    if ($timePro[0][0] == '') $timePro[0][0] = '-----';
                    if ($timePro[0][1] == '') $timePro[0][1] = '-----';
                if ($res_y[$r]['uid']!='002321') {
                ?>
                <td style='font-size:0.80em;'>
                    <?php echo "　本日の出退勤<br>　出勤 {$timePro[0][0]}　退勤 {$timePro[0][1]}\n"; ?>
                </td>
                <?php
                }
                ?>
            </tr>
            <tr>
                <td width='15%'>所属</td>
                <td width='30%'><?php echo($res_y[$r]['section_name']); ?></td>
                <?php
                if (getCheckAuthority(41)) {
                    if ($res_y[$r]['sid'] == 5 || $res_y[$r]['sid'] == 9 || $res_y[$r]['sid'] == 19 || $res_y[$r]['sid'] == 31) {
                    ?>
                        <?php
                        if ($res_y[$r]['uid']!='002321') {
                            /*** 2003/04/21 ADD ***/
                            if ($_SESSION["Auth"] >= AUTH_LEVEL1) {
                                echo "<td>　現在年齢</td>\n";
                            }
                        }
                        ?>
                    <?php
                    }
                } elseif (getCheckAuthority(42)) {
                    if ($res_y[$r]['sid'] == 4 || $res_y[$r]['sid'] == 18 || $res_y[$r]['sid'] == 38) {
                    ?>
                        <?php
                        if ($res_y[$r]['uid']!='002321') {
                            /*** 2003/04/21 ADD ***/
                            if ($_SESSION["Auth"] >= AUTH_LEVEL1) {
                                echo "<td>　現在年齢</td>\n";
                            }
                        }
                        ?>
                    <?php
                    }
                } elseif (getCheckAuthority(43)) {
                    if ($res_y[$r]['sid'] == 2 || $res_y[$r]['sid'] == 3 || $res_y[$r]['sid'] == 8 || $res_y[$r]['sid'] == 32) {
                    ?>
                        <?php
                        if ($res_y[$r]['uid']!='002321') {
                            /*** 2003/04/21 ADD ***/
                            if ($_SESSION["Auth"] >= AUTH_LEVEL1) {
                                echo "<td>　現在年齢</td>\n";
                            }
                        }
                        ?>
                    <?php
                    }
                } elseif (getCheckAuthority(44)) {
                    if ($res_y[$r]['sid'] == 5) {
                    ?>
                        <?php
                        if ($res_y[$r]['uid']!='002321') {
                            /*** 2003/04/21 ADD ***/
                            if ($_SESSION["Auth"] >= AUTH_LEVEL1) {
                                echo "<td>　現在年齢</td>\n";
                            }
                        }
                        ?>
                    <?php
                    }
                } elseif (getCheckAuthority(45)) {
                    if ($res_y[$r]['sid'] == 19) {
                    ?>
                        <?php
                        if ($res_y[$r]['uid']!='002321') {
                            /*** 2003/04/21 ADD ***/
                            if ($_SESSION["Auth"] >= AUTH_LEVEL1) {
                                echo "<td>　現在年齢</td>\n";
                            }
                        }
                        ?>
                    <?php
                    }
                } elseif (getCheckAuthority(46)) {
                    if ($res_y[$r]['sid'] == 18) {
                    ?>
                        <?php
                        if ($res_y[$r]['uid']!='002321') {
                            /*** 2003/04/21 ADD ***/
                            if ($_SESSION["Auth"] >= AUTH_LEVEL1) {
                                echo "<td>　現在年齢</td>\n";
                            }
                        }
                        ?>
                    <?php
                    }
                } elseif (getCheckAuthority(47)) {
                    if ($res_y[$r]['sid'] == 4) {
                    ?>
                        <?php
                        if ($res_y[$r]['uid']!='002321') {
                            /*** 2003/04/21 ADD ***/
                            if ($_SESSION["Auth"] >= AUTH_LEVEL1) {
                                echo "<td>　現在年齢</td>\n";
                            }
                        }
                        ?>
                    <?php
                    }
                } elseif (getCheckAuthority(48)) {
                    if ($res_y[$r]['sid'] == 34) {
                    ?>
                        <?php
                        if ($res_y[$r]['uid']!='002321') {
                            /*** 2003/04/21 ADD ***/
                            if ($_SESSION["Auth"] >= AUTH_LEVEL1) {
                                echo "<td>　現在年齢</td>\n";
                            }
                        }
                        ?>
                    <?php
                    }
                } elseif (getCheckAuthority(49)) {
                    if ($res_y[$r]['sid'] == 35) {
                    ?>
                        <?php
                        if ($res_y[$r]['uid']!='002321') {
                            /*** 2003/04/21 ADD ***/
                            if ($_SESSION["Auth"] >= AUTH_LEVEL1) {
                                echo "<td>　現在年齢</td>\n";
                            }
                        }
                        ?>
                    <?php
                    }
                } elseif (getCheckAuthority(50)) {
                    if ($res_y[$r]['sid'] == 32) {
                    ?>
                        <?php
                        if ($res_y[$r]['uid']!='002321') {
                            /*** 2003/04/21 ADD ***/
                            if ($_SESSION["Auth"] >= AUTH_LEVEL1) {
                                echo "<td>　現在年齢</td>\n";
                            }
                        }
                        ?>
                    <?php
                    }
                } elseif (getCheckAuthority(51)) {
                    if ($res_y[$r]['sid'] == 2) {
                    ?>
                        <?php
                        if ($res_y[$r]['uid']!='002321') {
                            /*** 2003/04/21 ADD ***/
                            if ($_SESSION["Auth"] >= AUTH_LEVEL1) {
                                echo "<td>　現在年齢</td>\n";
                            }
                        }
                        ?>
                    <?php
                    }
                } elseif (getCheckAuthority(52)) {
                    if ($res_y[$r]['sid'] == 3) {
                    ?>
                        <?php
                        if ($res_y[$r]['uid']!='002321') {
                            /*** 2003/04/21 ADD ***/
                            if ($_SESSION["Auth"] >= AUTH_LEVEL1) {
                                echo "<td>　現在年齢</td>\n";
                            }
                        }
                        ?>
                    <?php
                    }
                } elseif (getCheckAuthority(55)) {
                    if ($res_y[$r]['sid'] == 17 || $res_y[$r]['sid'] == 34 || $res_y[$r]['sid'] == 35) {
                    ?>
                        <?php
                        if ($res_y[$r]['uid']!='002321') {
                            /*** 2003/04/21 ADD ***/
                            if ($_SESSION["Auth"] >= AUTH_LEVEL1) {
                                echo "<td>　現在年齢</td>\n";
                            }
                        }
                        ?>
                    <?php
                    }
                }
                ?>
                <?php
                if ($res_y[$r]['uid']!='002321') {
                    /*** 2003/04/21 ADD ***/
                    if ($_SESSION["Auth"] >= AUTH_LEVEL3 || getCheckAuthority(60) || $_SESSION['User_ID'] == '300551') {
                        echo "<td>　現在年齢</td>\n";
                    }
                }
                ?>
            </tr>
            <tr><td width='15%'>職位</td>
                <td><?php echo($res_y[$r]["position_name"]); ?></td>
                <td>　</td>
                <?php
                if (getCheckAuthority(41)) {
                    if ($res_y[$r]['sid'] == 5 || $res_y[$r]['sid'] == 9 || $res_y[$r]['sid'] == 19 || $res_y[$r]['sid'] == 31) {
                    ?>
                        <?php
                        if ($res_y[$r]['uid']!='002321') {
                        /*** 2003/04/21 ***/
                            if ($_SESSION["Auth"] >= AUTH_LEVEL1 || $_SESSION['User_ID'] == '970268' || $_SESSION['User_ID'] == '300551') {
                                $query_b = sprintf("select birthday from user_detailes where uid='%s'", $res_y[$r]['uid']);
                                getUniResult($query_b, $birth_f);
                                $res_age = array();
                                $query_age = sprintf("select extract(years from age('%s'::TIMESTAMP)) as years, extract(mons from age('%s'::TIMESTAMP)) as mons, extract(days from age('%s'::TIMESTAMP)) as days",$birth_f,$birth_f,$birth_f);
                                if (($rows_age=getResult($query_age,$res_age)) > 0) {
                                    printf("<td><font color='red'><b>　%s歳%sヶ月%s日</b></font></td>\n", $res_age[0]['years'], $res_age[0]['mons'], $res_age[0]['days']);
                                }
                            }
                        } else {
                            echo "<td>　</td>\n";
                        }
                        ?>
                    <?php
                    }
                } elseif (getCheckAuthority(42)) {
                    if ($res_y[$r]['sid'] == 4 || $res_y[$r]['sid'] == 18 || $res_y[$r]['sid'] == 38) {
                    ?>
                        <?php
                        if ($res_y[$r]['uid']!='002321') {
                        /*** 2003/04/21 ***/
                            if ($_SESSION["Auth"] >= AUTH_LEVEL1 || $_SESSION['User_ID'] == '970268' || $_SESSION['User_ID'] == '300551') {
                                $query_b = sprintf("select birthday from user_detailes where uid='%s'", $res_y[$r]['uid']);
                                getUniResult($query_b, $birth_f);
                                $res_age = array();
                                $query_age = sprintf("select extract(years from age('%s'::TIMESTAMP)) as years, extract(mons from age('%s'::TIMESTAMP)) as mons, extract(days from age('%s'::TIMESTAMP)) as days",$birth_f,$birth_f,$birth_f);
                                if (($rows_age=getResult($query_age,$res_age)) > 0) {
                                    printf("<td><font color='red'><b>　%s歳%sヶ月%s日</b></font></td>\n", $res_age[0]['years'], $res_age[0]['mons'], $res_age[0]['days']);
                                }
                            }
                        } else {
                            echo "<td>　</td>\n";
                        }
                        ?>
                    <?php
                    }
                } elseif (getCheckAuthority(43)) {
                    if ($res_y[$r]['sid'] == 2 || $res_y[$r]['sid'] == 3 || $res_y[$r]['sid'] == 8 || $res_y[$r]['sid'] == 32) {
                    ?>
                        <?php
                        if ($res_y[$r]['uid']!='002321') {
                        /*** 2003/04/21 ***/
                            if ($_SESSION["Auth"] >= AUTH_LEVEL1 || $_SESSION['User_ID'] == '970268' || $_SESSION['User_ID'] == '300551') {
                                $query_b = sprintf("select birthday from user_detailes where uid='%s'", $res_y[$r]['uid']);
                                getUniResult($query_b, $birth_f);
                                $res_age = array();
                                $query_age = sprintf("select extract(years from age('%s'::TIMESTAMP)) as years, extract(mons from age('%s'::TIMESTAMP)) as mons, extract(days from age('%s'::TIMESTAMP)) as days",$birth_f,$birth_f,$birth_f);
                                if (($rows_age=getResult($query_age,$res_age)) > 0) {
                                    printf("<td><font color='red'><b>　%s歳%sヶ月%s日</b></font></td>\n", $res_age[0]['years'], $res_age[0]['mons'], $res_age[0]['days']);
                                }
                            }
                        } else {
                            echo "<td>　</td>\n";
                        }
                        ?>
                    <?php
                    }
                } elseif (getCheckAuthority(44)) {
                    if ($res_y[$r]['sid'] == 5) {
                    ?>
                        <?php
                        if ($res_y[$r]['uid']!='002321') {
                        /*** 2003/04/21 ***/
                            if ($_SESSION["Auth"] >= AUTH_LEVEL1 || $_SESSION['User_ID'] == '970268' || $_SESSION['User_ID'] == '300551') {
                                $query_b = sprintf("select birthday from user_detailes where uid='%s'", $res_y[$r]['uid']);
                                getUniResult($query_b, $birth_f);
                                $res_age = array();
                                $query_age = sprintf("select extract(years from age('%s'::TIMESTAMP)) as years, extract(mons from age('%s'::TIMESTAMP)) as mons, extract(days from age('%s'::TIMESTAMP)) as days",$birth_f,$birth_f,$birth_f);
                                if (($rows_age=getResult($query_age,$res_age)) > 0) {
                                    printf("<td><font color='red'><b>　%s歳%sヶ月%s日</b></font></td>\n", $res_age[0]['years'], $res_age[0]['mons'], $res_age[0]['days']);
                                }
                            }
                        } else {
                            echo "<td>　</td>\n";
                        }
                        ?>
                    <?php
                    }
                } elseif (getCheckAuthority(45)) {
                    if ($res_y[$r]['sid'] == 19) {
                    ?>
                        <?php
                        if ($res_y[$r]['uid']!='002321') {
                        /*** 2003/04/21 ***/
                            if ($_SESSION["Auth"] >= AUTH_LEVEL1 || $_SESSION['User_ID'] == '970268' || $_SESSION['User_ID'] == '300551') {
                                $query_b = sprintf("select birthday from user_detailes where uid='%s'", $res_y[$r]['uid']);
                                getUniResult($query_b, $birth_f);
                                $res_age = array();
                                $query_age = sprintf("select extract(years from age('%s'::TIMESTAMP)) as years, extract(mons from age('%s'::TIMESTAMP)) as mons, extract(days from age('%s'::TIMESTAMP)) as days",$birth_f,$birth_f,$birth_f);
                                if (($rows_age=getResult($query_age,$res_age)) > 0) {
                                    printf("<td><font color='red'><b>　%s歳%sヶ月%s日</b></font></td>\n", $res_age[0]['years'], $res_age[0]['mons'], $res_age[0]['days']);
                                }
                            }
                        } else {
                            echo "<td>　</td>\n";
                        }
                        ?>
                    <?php
                    }
                } elseif (getCheckAuthority(46)) {
                    if ($res_y[$r]['sid'] == 18) {
                    ?>
                        <?php
                        if ($res_y[$r]['uid']!='002321') {
                        /*** 2003/04/21 ***/
                            if ($_SESSION["Auth"] >= AUTH_LEVEL1 || $_SESSION['User_ID'] == '970268' || $_SESSION['User_ID'] == '300551') {
                                $query_b = sprintf("select birthday from user_detailes where uid='%s'", $res_y[$r]['uid']);
                                getUniResult($query_b, $birth_f);
                                $res_age = array();
                                $query_age = sprintf("select extract(years from age('%s'::TIMESTAMP)) as years, extract(mons from age('%s'::TIMESTAMP)) as mons, extract(days from age('%s'::TIMESTAMP)) as days",$birth_f,$birth_f,$birth_f);
                                if (($rows_age=getResult($query_age,$res_age)) > 0) {
                                    printf("<td><font color='red'><b>　%s歳%sヶ月%s日</b></font></td>\n", $res_age[0]['years'], $res_age[0]['mons'], $res_age[0]['days']);
                                }
                            }
                        } else {
                            echo "<td>　</td>\n";
                        }
                        ?>
                    <?php
                    }
                } elseif (getCheckAuthority(47)) {
                    if ($res_y[$r]['sid'] == 4) {
                    ?>
                        <?php
                        if ($res_y[$r]['uid']!='002321') {
                        /*** 2003/04/21 ***/
                            if ($_SESSION["Auth"] >= AUTH_LEVEL1 || $_SESSION['User_ID'] == '970268' || $_SESSION['User_ID'] == '300551') {
                                $query_b = sprintf("select birthday from user_detailes where uid='%s'", $res_y[$r]['uid']);
                                getUniResult($query_b, $birth_f);
                                $res_age = array();
                                $query_age = sprintf("select extract(years from age('%s'::TIMESTAMP)) as years, extract(mons from age('%s'::TIMESTAMP)) as mons, extract(days from age('%s'::TIMESTAMP)) as days",$birth_f,$birth_f,$birth_f);
                                if (($rows_age=getResult($query_age,$res_age)) > 0) {
                                    printf("<td><font color='red'><b>　%s歳%sヶ月%s日</b></font></td>\n", $res_age[0]['years'], $res_age[0]['mons'], $res_age[0]['days']);
                                }
                            }
                        } else {
                            echo "<td>　</td>\n";
                        }
                        ?>
                    <?php
                    }
                } elseif (getCheckAuthority(48)) {
                    if ($res_y[$r]['sid'] == 34) {
                    ?>
                        <?php
                        if ($res_y[$r]['uid']!='002321') {
                        /*** 2003/04/21 ***/
                            if ($_SESSION["Auth"] >= AUTH_LEVEL1 || $_SESSION['User_ID'] == '970268' || $_SESSION['User_ID'] == '300551') {
                                $query_b = sprintf("select birthday from user_detailes where uid='%s'", $res_y[$r]['uid']);
                                getUniResult($query_b, $birth_f);
                                $res_age = array();
                                $query_age = sprintf("select extract(years from age('%s'::TIMESTAMP)) as years, extract(mons from age('%s'::TIMESTAMP)) as mons, extract(days from age('%s'::TIMESTAMP)) as days",$birth_f,$birth_f,$birth_f);
                                if (($rows_age=getResult($query_age,$res_age)) > 0) {
                                    printf("<td><font color='red'><b>　%s歳%sヶ月%s日</b></font></td>\n", $res_age[0]['years'], $res_age[0]['mons'], $res_age[0]['days']);
                                }
                            }
                        } else {
                            echo "<td>　</td>\n";
                        }
                        ?>
                    <?php
                    }
                } elseif (getCheckAuthority(49)) {
                    if ($res_y[$r]['sid'] == 35) {
                    ?>
                        <?php
                        if ($res_y[$r]['uid']!='002321') {
                        /*** 2003/04/21 ***/
                            if ($_SESSION["Auth"] >= AUTH_LEVEL1 || $_SESSION['User_ID'] == '970268' || $_SESSION['User_ID'] == '300551') {
                                $query_b = sprintf("select birthday from user_detailes where uid='%s'", $res_y[$r]['uid']);
                                getUniResult($query_b, $birth_f);
                                $res_age = array();
                                $query_age = sprintf("select extract(years from age('%s'::TIMESTAMP)) as years, extract(mons from age('%s'::TIMESTAMP)) as mons, extract(days from age('%s'::TIMESTAMP)) as days",$birth_f,$birth_f,$birth_f);
                                if (($rows_age=getResult($query_age,$res_age)) > 0) {
                                    printf("<td><font color='red'><b>　%s歳%sヶ月%s日</b></font></td>\n", $res_age[0]['years'], $res_age[0]['mons'], $res_age[0]['days']);
                                }
                            }
                        } else {
                            echo "<td>　</td>\n";
                        }
                        ?>
                    <?php
                    }
                } elseif (getCheckAuthority(50)) {
                    if ($res_y[$r]['sid'] == 32) {
                    ?>
                        <?php
                        if ($res_y[$r]['uid']!='002321') {
                        /*** 2003/04/21 ***/
                            if ($_SESSION["Auth"] >= AUTH_LEVEL1 || $_SESSION['User_ID'] == '970268' || $_SESSION['User_ID'] == '300551') {
                                $query_b = sprintf("select birthday from user_detailes where uid='%s'", $res_y[$r]['uid']);
                                getUniResult($query_b, $birth_f);
                                $res_age = array();
                                $query_age = sprintf("select extract(years from age('%s'::TIMESTAMP)) as years, extract(mons from age('%s'::TIMESTAMP)) as mons, extract(days from age('%s'::TIMESTAMP)) as days",$birth_f,$birth_f,$birth_f);
                                if (($rows_age=getResult($query_age,$res_age)) > 0) {
                                    printf("<td><font color='red'><b>　%s歳%sヶ月%s日</b></font></td>\n", $res_age[0]['years'], $res_age[0]['mons'], $res_age[0]['days']);
                                }
                            }
                        } else {
                            echo "<td>　</td>\n";
                        }
                        ?>
                    <?php
                    }
                } elseif (getCheckAuthority(51)) {
                    if ($res_y[$r]['sid'] == 2) {
                    ?>
                        <?php
                        if ($res_y[$r]['uid']!='002321') {
                        /*** 2003/04/21 ***/
                            if ($_SESSION["Auth"] >= AUTH_LEVEL1 || $_SESSION['User_ID'] == '970268' || $_SESSION['User_ID'] == '300551') {
                                $query_b = sprintf("select birthday from user_detailes where uid='%s'", $res_y[$r]['uid']);
                                getUniResult($query_b, $birth_f);
                                $res_age = array();
                                $query_age = sprintf("select extract(years from age('%s'::TIMESTAMP)) as years, extract(mons from age('%s'::TIMESTAMP)) as mons, extract(days from age('%s'::TIMESTAMP)) as days",$birth_f,$birth_f,$birth_f);
                                if (($rows_age=getResult($query_age,$res_age)) > 0) {
                                    printf("<td><font color='red'><b>　%s歳%sヶ月%s日</b></font></td>\n", $res_age[0]['years'], $res_age[0]['mons'], $res_age[0]['days']);
                                }
                            }
                        } else {
                            echo "<td>　</td>\n";
                        }
                        ?>
                    <?php
                    }
                } elseif (getCheckAuthority(52)) {
                    if ($res_y[$r]['sid'] == 3) {
                    ?>
                        <?php
                        if ($res_y[$r]['uid']!='002321') {
                        /*** 2003/04/21 ***/
                            if ($_SESSION["Auth"] >= AUTH_LEVEL1 || $_SESSION['User_ID'] == '970268' || $_SESSION['User_ID'] == '300551') {
                                $query_b = sprintf("select birthday from user_detailes where uid='%s'", $res_y[$r]['uid']);
                                getUniResult($query_b, $birth_f);
                                $res_age = array();
                                $query_age = sprintf("select extract(years from age('%s'::TIMESTAMP)) as years, extract(mons from age('%s'::TIMESTAMP)) as mons, extract(days from age('%s'::TIMESTAMP)) as days",$birth_f,$birth_f,$birth_f);
                                if (($rows_age=getResult($query_age,$res_age)) > 0) {
                                    printf("<td><font color='red'><b>　%s歳%sヶ月%s日</b></font></td>\n", $res_age[0]['years'], $res_age[0]['mons'], $res_age[0]['days']);
                                }
                            }
                        } else {
                            echo "<td>　</td>\n";
                        }
                        ?>
                    <?php
                    }
                } elseif (getCheckAuthority(55)) {
                    if ($res_y[$r]['sid'] == 17 || $res_y[$r]['sid'] == 34 || $res_y[$r]['sid'] == 35) {
                    ?>
                        <?php
                        if ($res_y[$r]['uid']!='002321') {
                        /*** 2003/04/21 ***/
                            if ($_SESSION["Auth"] >= AUTH_LEVEL1 || $_SESSION['User_ID'] == '970268' || $_SESSION['User_ID'] == '300551') {
                                $query_b = sprintf("select birthday from user_detailes where uid='%s'", $res_y[$r]['uid']);
                                getUniResult($query_b, $birth_f);
                                $res_age = array();
                                $query_age = sprintf("select extract(years from age('%s'::TIMESTAMP)) as years, extract(mons from age('%s'::TIMESTAMP)) as mons, extract(days from age('%s'::TIMESTAMP)) as days",$birth_f,$birth_f,$birth_f);
                                if (($rows_age=getResult($query_age,$res_age)) > 0) {
                                    printf("<td><font color='red'><b>　%s歳%sヶ月%s日</b></font></td>\n", $res_age[0]['years'], $res_age[0]['mons'], $res_age[0]['days']);
                                }
                            }
                        } else {
                            echo "<td>　</td>\n";
                        }
                        ?>
                    <?php
                    }
                }
                ?>
                <?php
                if ($res_y[$r]['uid']!='002321') {
                /*** 2003/04/21 ***/
                    if ($_SESSION["Auth"] >= AUTH_LEVEL3 || getCheckAuthority(60) || $_SESSION['User_ID'] == '300551') {
                        $query_b = sprintf("select birthday from user_detailes where uid='%s'", $res_y[$r]['uid']);
                        getUniResult($query_b, $birth_f);
                        $res_age = array();
                        $query_age = sprintf("select extract(years from age('%s'::TIMESTAMP)) as years, extract(mons from age('%s'::TIMESTAMP)) as mons, extract(days from age('%s'::TIMESTAMP)) as days",$birth_f,$birth_f,$birth_f);
                        if (($rows_age=getResult($query_age,$res_age)) > 0) {
                            printf("<td><font color='red'><b>　%s歳%sヶ月%s日</b></font></td>\n", $res_age[0]['years'], $res_age[0]['mons'], $res_age[0]['days']);
                        }
                    }
                } else {
                    echo "<td>　</td>\n";
                }
                ?>
            </tr>
<?php
            if($_SESSION["Auth"] >= AUTH_LEVEL3){   // アドミニストレーター すべてを表示
?>
            <tr><td colspan=3 align="right">
                <input type="hidden" name="userid" value="<?php echo($res_y[$r]['uid']); ?>">
                <input type="hidden" name="kana" value="<?php echo(trim($res_y[$r]["kana"])); ?>">
                <input type="hidden" name="name" value="<?php echo(trim($res_y[$r]["name"])); ?>">
                <input type="hidden" name="section_name" value="<?php echo(trim($res_y[$r]["section_name"])); ?>">
                <input type="hidden" name="lookupkind" value=<?php echo($_SESSION["lookupkind"]); ?>>
                <input type="hidden" name="lookupkey" value="<?php echo($_SESSION["lookupkey"]); ?>">
                <input type="hidden" name="lookupkeykind" value=<?php echo($_SESSION["lookupkeykind"]); ?>>
                <input type="hidden" name="lookupsection" value=<?php echo($_SESSION["lookupsection"]); ?>>
                <input type="hidden" name="lookupposition" value=<?php echo($_SESSION["lookupposition"]); ?>>
                <input type="hidden" name="lookupentry" value=<?php echo($_SESSION["lookupentry"]); ?>>
                <input type="hidden" name="lookupcapacity" value=<?php echo($_SESSION["lookupcapacity"]) ?>>
                <input type="hidden" name="lookupreceive" value=<?php echo($_SESSION["lookupreceive"]) ?>>
                <input type="hidden" name="lookupreceive" value=<?php echo($_SESSION["lookupreceive"]) ?>>
                <input type="hidden" name="lookupyukyukind" value=<?php echo($_SESSION["lookupyukyukind"]) ?>>
                <input type="hidden" name="lookupyukyu" value=<?php echo($_SESSION["lookupyukyu"]) ?>>
                <input type="hidden" name="lookupyukyufive" value=<?php echo($_SESSION["lookupyukyufive"]) ?>>
                <input type="hidden" name="lookupyukyuf" value=<?php echo($_SESSION["lookupyukyuf"]) ?>>
                <input type="hidden" name="histnum" value=-1>
                <input type="hidden" name="retireflg" value=0>
                <input type='hidden' name='offset' value='<?php echo $offset?>'>
                <input type='button' name='historyDisp' value='経歴表示' title='教育・資格・移動経歴等の表示を行います。'
                    onClick='win_open("print/print_emp_branch_user.php?targetUser=<?php echo $res_y[$r]['uid']?>", 800, 700);'
                >
                <input type="submit" name="inf" value="情報の変更">
                <input type="submit" name="pwd" value="パスワードの変更"></td>
            </tr>
            <?php
            } elseif ($_SESSION['User_ID'] == '300551') {   // 入力担当者 すべての部門 経歴と情報の変更表示
            ?>
            <tr><td colspan=3 align="right">
                <input type="hidden" name="userid" value="<?php echo($res_y[$r]['uid']); ?>">
                <input type="hidden" name="kana" value="<?php echo(trim($res_y[$r]["kana"])); ?>">
                <input type="hidden" name="name" value="<?php echo(trim($res_y[$r]["name"])); ?>">
                <input type="hidden" name="section_name" value="<?php echo(trim($res_y[$r]["section_name"])); ?>">
                <input type="hidden" name="lookupkind" value=<?php echo($_SESSION["lookupkind"]); ?>>
                <input type="hidden" name="lookupkey" value="<?php echo($_SESSION["lookupkey"]); ?>">
                <input type="hidden" name="lookupkeykind" value=<?php echo($_SESSION["lookupkeykind"]); ?>>
                <input type="hidden" name="lookupsection" value=<?php echo($_SESSION["lookupsection"]); ?>>
                <input type="hidden" name="lookupposition" value=<?php echo($_SESSION["lookupposition"]); ?>>
                <input type="hidden" name="lookupentry" value=<?php echo($_SESSION["lookupentry"]); ?>>
                <input type="hidden" name="lookupcapacity" value=<?php echo($_SESSION["lookupcapacity"]) ?>>
                <input type="hidden" name="lookupreceive" value=<?php echo($_SESSION["lookupreceive"]) ?>>
                <input type="hidden" name="lookupreceive" value=<?php echo($_SESSION["lookupreceive"]) ?>>
                <input type="hidden" name="lookupyukyukind" value=<?php echo($_SESSION["lookupyukyukind"]) ?>>
                <input type="hidden" name="lookupyukyu" value=<?php echo($_SESSION["lookupyukyu"]) ?>>
                <input type="hidden" name="lookupyukyufive" value=<?php echo($_SESSION["lookupyukyufive"]) ?>>
                <input type="hidden" name="lookupyukyuf" value=<?php echo($_SESSION["lookupyukyuf"]) ?>>
                <input type="hidden" name="histnum" value=-1>
                <input type="hidden" name="retireflg" value=0>
                <input type='hidden' name='offset' value='<?php echo $offset?>'>
                <input type='button' name='historyDisp' value='経歴表示' title='教育・資格・移動経歴等の表示を行います。'
                    onClick='win_open("print/print_emp_branch_user.php?targetUser=<?php echo $res_y[$r]['uid']?>", 800, 700);'
                >
                <input type="submit" name="inf" value="情報の変更">
            </tr>
            <?php
            } elseif (getCheckAuthority(60)) {  // 社長・工場長・管理部長・総務課長 すべての部門経歴表示
            ?>
            <tr><td colspan=3 align="right">
                <input type="hidden" name="userid" value="<?php echo($res_y[$r]['uid']); ?>">
                <input type="hidden" name="kana" value="<?php echo(trim($res_y[$r]["kana"])); ?>">
                <input type="hidden" name="name" value="<?php echo(trim($res_y[$r]["name"])); ?>">
                <input type="hidden" name="section_name" value="<?php echo(trim($res_y[$r]["section_name"])); ?>">
                <input type="hidden" name="lookupkind" value=<?php echo($_SESSION["lookupkind"]); ?>>
                <input type="hidden" name="lookupkey" value="<?php echo($_SESSION["lookupkey"]); ?>">
                <input type="hidden" name="lookupkeykind" value=<?php echo($_SESSION["lookupkeykind"]); ?>>
                <input type="hidden" name="lookupsection" value=<?php echo($_SESSION["lookupsection"]); ?>>
                <input type="hidden" name="lookupposition" value=<?php echo($_SESSION["lookupposition"]); ?>>
                <input type="hidden" name="lookupentry" value=<?php echo($_SESSION["lookupentry"]); ?>>
                <input type="hidden" name="lookupcapacity" value=<?php echo($_SESSION["lookupcapacity"]) ?>>
                <input type="hidden" name="lookupreceive" value=<?php echo($_SESSION["lookupreceive"]) ?>>
                <input type="hidden" name="lookupyukyukind" value=<?php echo($_SESSION['lookupyukyukind']) ?>>
                <input type="hidden" name="lookupyukyu" value=<?php echo($_SESSION["lookupyukyu"]) ?>>
                <input type="hidden" name="lookupyukyufive" value=<?php echo($_SESSION["lookupyukyufive"]) ?>>
                <input type="hidden" name="lookupyukyuf" value=<?php echo($_SESSION["lookupyukyuf"]) ?>>
                <input type="hidden" name="histnum" value=-1>
                <input type="hidden" name="retireflg" value=0>
                <input type='hidden' name='offset' value='<?php echo $offset?>'>
                <input type='button' name='historyDisp' value='経歴表示' title='教育・資格・移動経歴等の表示を行います。'
                    onClick='win_open("print/print_emp_branch_user.php?targetUser=<?php echo $res_y[$r]['uid']?>", 800, 700);'
                >
            </tr>
            <?php
            } elseif (getCheckAuthority(61)) {  // 部長代理以上 自部門のみ経歴表示
            ?>
                <?php
                if (getCheckAuthority(41)) {
                    if ($res_y[$r]['sid'] == 5 || $res_y[$r]['sid'] == 9 || $res_y[$r]['sid'] == 19 || $res_y[$r]['sid'] == 31) {
                    ?>
                        <tr><td colspan=3 align="right">
                        <input type="hidden" name="userid" value="<?php echo($res_y[$r]['uid']); ?>">
                        <input type="hidden" name="kana" value="<?php echo(trim($res_y[$r]["kana"])); ?>">
                        <input type="hidden" name="name" value="<?php echo(trim($res_y[$r]["name"])); ?>">
                        <input type="hidden" name="section_name" value="<?php echo(trim($res_y[$r]["section_name"])); ?>">
                        <input type="hidden" name="lookupkind" value=<?php echo($_SESSION["lookupkind"]); ?>>
                        <input type="hidden" name="lookupkey" value="<?php echo($_SESSION["lookupkey"]); ?>">
                        <input type="hidden" name="lookupkeykind" value=<?php echo($_SESSION["lookupkeykind"]); ?>>
                        <input type="hidden" name="lookupsection" value=<?php echo($_SESSION["lookupsection"]); ?>>
                        <input type="hidden" name="lookupposition" value=<?php echo($_SESSION["lookupposition"]); ?>>
                        <input type="hidden" name="lookupentry" value=<?php echo($_SESSION["lookupentry"]); ?>>
                        <input type="hidden" name="lookupcapacity" value=<?php echo($_SESSION["lookupcapacity"]) ?>>
                        <input type="hidden" name="lookupreceive" value=<?php echo($_SESSION["lookupreceive"]) ?>>
                        <input type="hidden" name="lookupyukyukind" value=<?php echo($_SESSION['lookupyukyukind']) ?>>
                        <input type="hidden" name="lookupyukyu" value=<?php echo($_SESSION["lookupyukyu"]) ?>>
                        <input type="hidden" name="lookupyukyufive" value=<?php echo($_SESSION["lookupyukyufive"]) ?>>
                        <input type="hidden" name="lookupyukyuf" value=<?php echo($_SESSION["lookupyukyuf"]) ?>>
                        <input type="hidden" name="histnum" value=-1>
                        <input type="hidden" name="retireflg" value=0>
                        <input type='hidden' name='offset' value='<?php echo $offset?>'>
                        <input type='button' name='historyDisp' value='経歴表示' title='教育・資格・移動経歴等の表示を行います。'
                            onClick='win_open("print/print_emp_branch_user.php?targetUser=<?php echo $res_y[$r]['uid']?>", 800, 700);'
                        >
                        </tr>
                    <?php
                    }
                } elseif (getCheckAuthority(42)) {
                    if ($res_y[$r]['sid'] == 4 || $res_y[$r]['sid'] == 18 || $res_y[$r]['sid'] == 38) {
                    ?>
                        <tr><td colspan=3 align="right">
                        <input type="hidden" name="userid" value="<?php echo($res_y[$r]['uid']); ?>">
                        <input type="hidden" name="kana" value="<?php echo(trim($res_y[$r]["kana"])); ?>">
                        <input type="hidden" name="name" value="<?php echo(trim($res_y[$r]["name"])); ?>">
                        <input type="hidden" name="section_name" value="<?php echo(trim($res_y[$r]["section_name"])); ?>">
                        <input type="hidden" name="lookupkind" value=<?php echo($_SESSION["lookupkind"]); ?>>
                        <input type="hidden" name="lookupkey" value="<?php echo($_SESSION["lookupkey"]); ?>">
                        <input type="hidden" name="lookupkeykind" value=<?php echo($_SESSION["lookupkeykind"]); ?>>
                        <input type="hidden" name="lookupsection" value=<?php echo($_SESSION["lookupsection"]); ?>>
                        <input type="hidden" name="lookupposition" value=<?php echo($_SESSION["lookupposition"]); ?>>
                        <input type="hidden" name="lookupentry" value=<?php echo($_SESSION["lookupentry"]); ?>>
                        <input type="hidden" name="lookupcapacity" value=<?php echo($_SESSION["lookupcapacity"]) ?>>
                        <input type="hidden" name="lookupreceive" value=<?php echo($_SESSION["lookupreceive"]) ?>>
                        <input type="hidden" name="lookupyukyukind" value=<?php echo($_SESSION['lookupyukyukind']) ?>>
                        <input type="hidden" name="lookupyukyu" value=<?php echo($_SESSION["lookupyukyu"]) ?>>
                        <input type="hidden" name="lookupyukyufive" value=<?php echo($_SESSION["lookupyukyufive"]) ?>>
                        <input type="hidden" name="lookupyukyuf" value=<?php echo($_SESSION["lookupyukyuf"]) ?>>
                        <input type="hidden" name="histnum" value=-1>
                        <input type="hidden" name="retireflg" value=0>
                        <input type='hidden' name='offset' value='<?php echo $offset?>'>
                        <input type='button' name='historyDisp' value='経歴表示' title='教育・資格・移動経歴等の表示を行います。'
                            onClick='win_open("print/print_emp_branch_user.php?targetUser=<?php echo $res_y[$r]['uid']?>", 800, 700);'
                        >
                        </tr>
                    <?php
                    }
                } elseif (getCheckAuthority(43)) {
                    if ($res_y[$r]['sid'] == 2 || $res_y[$r]['sid'] == 3 || $res_y[$r]['sid'] == 8 || $res_y[$r]['sid'] == 32) {
                    ?>
                        <tr><td colspan=3 align="right">
                        <input type="hidden" name="userid" value="<?php echo($res_y[$r]['uid']); ?>">
                        <input type="hidden" name="kana" value="<?php echo(trim($res_y[$r]["kana"])); ?>">
                        <input type="hidden" name="name" value="<?php echo(trim($res_y[$r]["name"])); ?>">
                        <input type="hidden" name="section_name" value="<?php echo(trim($res_y[$r]["section_name"])); ?>">
                        <input type="hidden" name="lookupkind" value=<?php echo($_SESSION["lookupkind"]); ?>>
                        <input type="hidden" name="lookupkey" value="<?php echo($_SESSION["lookupkey"]); ?>">
                        <input type="hidden" name="lookupkeykind" value=<?php echo($_SESSION["lookupkeykind"]); ?>>
                        <input type="hidden" name="lookupsection" value=<?php echo($_SESSION["lookupsection"]); ?>>
                        <input type="hidden" name="lookupposition" value=<?php echo($_SESSION["lookupposition"]); ?>>
                        <input type="hidden" name="lookupentry" value=<?php echo($_SESSION["lookupentry"]); ?>>
                        <input type="hidden" name="lookupcapacity" value=<?php echo($_SESSION["lookupcapacity"]) ?>>
                        <input type="hidden" name="lookupreceive" value=<?php echo($_SESSION["lookupreceive"]) ?>>
                        <input type="hidden" name="lookupyukyukind" value=<?php echo($_SESSION['lookupyukyukind']) ?>>
                        <input type="hidden" name="lookupyukyu" value=<?php echo($_SESSION["lookupyukyu"]) ?>>
                        <input type="hidden" name="lookupyukyufive" value=<?php echo($_SESSION["lookupyukyufive"]) ?>>
                        <input type="hidden" name="lookupyukyuf" value=<?php echo($_SESSION["lookupyukyuf"]) ?>>
                        <input type="hidden" name="histnum" value=-1>
                        <input type="hidden" name="retireflg" value=0>
                        <input type='hidden' name='offset' value='<?php echo $offset?>'>
                        <input type='button' name='historyDisp' value='経歴表示' title='教育・資格・移動経歴等の表示を行います。'
                            onClick='win_open("print/print_emp_branch_user.php?targetUser=<?php echo $res_y[$r]['uid']?>", 800, 700);'
                        >
                        </tr>
                    <?php
                    }
                } elseif (getCheckAuthority(44)) {
                    if ($res_y[$r]['sid'] == 5) {
                    ?>
                        <tr><td colspan=3 align="right">
                        <input type="hidden" name="userid" value="<?php echo($res_y[$r]['uid']); ?>">
                        <input type="hidden" name="kana" value="<?php echo(trim($res_y[$r]["kana"])); ?>">
                        <input type="hidden" name="name" value="<?php echo(trim($res_y[$r]["name"])); ?>">
                        <input type="hidden" name="section_name" value="<?php echo(trim($res_y[$r]["section_name"])); ?>">
                        <input type="hidden" name="lookupkind" value=<?php echo($_SESSION["lookupkind"]); ?>>
                        <input type="hidden" name="lookupkey" value="<?php echo($_SESSION["lookupkey"]); ?>">
                        <input type="hidden" name="lookupkeykind" value=<?php echo($_SESSION["lookupkeykind"]); ?>>
                        <input type="hidden" name="lookupsection" value=<?php echo($_SESSION["lookupsection"]); ?>>
                        <input type="hidden" name="lookupposition" value=<?php echo($_SESSION["lookupposition"]); ?>>
                        <input type="hidden" name="lookupentry" value=<?php echo($_SESSION["lookupentry"]); ?>>
                        <input type="hidden" name="lookupcapacity" value=<?php echo($_SESSION["lookupcapacity"]) ?>>
                        <input type="hidden" name="lookupreceive" value=<?php echo($_SESSION["lookupreceive"]) ?>>
                        <input type="hidden" name="lookupyukyukind" value=<?php echo($_SESSION['lookupyukyukind']) ?>>
                        <input type="hidden" name="lookupyukyu" value=<?php echo($_SESSION["lookupyukyu"]) ?>>
                        <input type="hidden" name="lookupyukyufive" value=<?php echo($_SESSION["lookupyukyufive"]) ?>>
                        <input type="hidden" name="lookupyukyuf" value=<?php echo($_SESSION["lookupyukyuf"]) ?>>
                        <input type="hidden" name="histnum" value=-1>
                        <input type="hidden" name="retireflg" value=0>
                        <input type='hidden' name='offset' value='<?php echo $offset?>'>
                        <input type='button' name='historyDisp' value='経歴表示' title='教育・資格・移動経歴等の表示を行います。'
                            onClick='win_open("print/print_emp_branch_user.php?targetUser=<?php echo $res_y[$r]['uid']?>", 800, 700);'
                        >
                        </tr>
                    <?php
                    }
                } elseif (getCheckAuthority(45)) {
                    if ($res_y[$r]['sid'] == 19) {
                    ?>
                        <tr><td colspan=3 align="right">
                        <input type="hidden" name="userid" value="<?php echo($res_y[$r]['uid']); ?>">
                        <input type="hidden" name="kana" value="<?php echo(trim($res_y[$r]["kana"])); ?>">
                        <input type="hidden" name="name" value="<?php echo(trim($res_y[$r]["name"])); ?>">
                        <input type="hidden" name="section_name" value="<?php echo(trim($res_y[$r]["section_name"])); ?>">
                        <input type="hidden" name="lookupkind" value=<?php echo($_SESSION["lookupkind"]); ?>>
                        <input type="hidden" name="lookupkey" value="<?php echo($_SESSION["lookupkey"]); ?>">
                        <input type="hidden" name="lookupkeykind" value=<?php echo($_SESSION["lookupkeykind"]); ?>>
                        <input type="hidden" name="lookupsection" value=<?php echo($_SESSION["lookupsection"]); ?>>
                        <input type="hidden" name="lookupposition" value=<?php echo($_SESSION["lookupposition"]); ?>>
                        <input type="hidden" name="lookupentry" value=<?php echo($_SESSION["lookupentry"]); ?>>
                        <input type="hidden" name="lookupcapacity" value=<?php echo($_SESSION["lookupcapacity"]) ?>>
                        <input type="hidden" name="lookupreceive" value=<?php echo($_SESSION["lookupreceive"]) ?>>
                        <input type="hidden" name="lookupyukyukind" value=<?php echo($_SESSION['lookupyukyukind']) ?>>
                        <input type="hidden" name="lookupyukyu" value=<?php echo($_SESSION["lookupyukyu"]) ?>>
                        <input type="hidden" name="lookupyukyufive" value=<?php echo($_SESSION["lookupyukyufive"]) ?>>
                        <input type="hidden" name="lookupyukyuf" value=<?php echo($_SESSION["lookupyukyuf"]) ?>>
                        <input type="hidden" name="histnum" value=-1>
                        <input type="hidden" name="retireflg" value=0>
                        <input type='hidden' name='offset' value='<?php echo $offset?>'>
                        <input type='button' name='historyDisp' value='経歴表示' title='教育・資格・移動経歴等の表示を行います。'
                            onClick='win_open("print/print_emp_branch_user.php?targetUser=<?php echo $res_y[$r]['uid']?>", 800, 700);'
                        >
                        </tr>
                    <?php
                    }
                } elseif (getCheckAuthority(46)) {
                    if ($res_y[$r]['sid'] == 18) {
                    ?>
                        <tr><td colspan=3 align="right">
                        <input type="hidden" name="userid" value="<?php echo($res_y[$r]['uid']); ?>">
                        <input type="hidden" name="kana" value="<?php echo(trim($res_y[$r]["kana"])); ?>">
                        <input type="hidden" name="name" value="<?php echo(trim($res_y[$r]["name"])); ?>">
                        <input type="hidden" name="section_name" value="<?php echo(trim($res_y[$r]["section_name"])); ?>">
                        <input type="hidden" name="lookupkind" value=<?php echo($_SESSION["lookupkind"]); ?>>
                        <input type="hidden" name="lookupkey" value="<?php echo($_SESSION["lookupkey"]); ?>">
                        <input type="hidden" name="lookupkeykind" value=<?php echo($_SESSION["lookupkeykind"]); ?>>
                        <input type="hidden" name="lookupsection" value=<?php echo($_SESSION["lookupsection"]); ?>>
                        <input type="hidden" name="lookupposition" value=<?php echo($_SESSION["lookupposition"]); ?>>
                        <input type="hidden" name="lookupentry" value=<?php echo($_SESSION["lookupentry"]); ?>>
                        <input type="hidden" name="lookupcapacity" value=<?php echo($_SESSION["lookupcapacity"]) ?>>
                        <input type="hidden" name="lookupreceive" value=<?php echo($_SESSION["lookupreceive"]) ?>>
                        <input type="hidden" name="lookupyukyukind" value=<?php echo($_SESSION['lookupyukyukind']) ?>>
                        <input type="hidden" name="lookupyukyu" value=<?php echo($_SESSION["lookupyukyu"]) ?>>
                        <input type="hidden" name="lookupyukyufive" value=<?php echo($_SESSION["lookupyukyufive"]) ?>>
                        <input type="hidden" name="lookupyukyuf" value=<?php echo($_SESSION["lookupyukyuf"]) ?>>
                        <input type="hidden" name="histnum" value=-1>
                        <input type="hidden" name="retireflg" value=0>
                        <input type='hidden' name='offset' value='<?php echo $offset?>'>
                        <input type='button' name='historyDisp' value='経歴表示' title='教育・資格・移動経歴等の表示を行います。'
                            onClick='win_open("print/print_emp_branch_user.php?targetUser=<?php echo $res_y[$r]['uid']?>", 800, 700);'
                        >
                        </tr>
                    <?php
                    }
                } elseif (getCheckAuthority(47)) {
                    if ($res_y[$r]['sid'] == 4) {
                    ?>
                        <tr><td colspan=3 align="right">
                        <input type="hidden" name="userid" value="<?php echo($res_y[$r]['uid']); ?>">
                        <input type="hidden" name="kana" value="<?php echo(trim($res_y[$r]["kana"])); ?>">
                        <input type="hidden" name="name" value="<?php echo(trim($res_y[$r]["name"])); ?>">
                        <input type="hidden" name="section_name" value="<?php echo(trim($res_y[$r]["section_name"])); ?>">
                        <input type="hidden" name="lookupkind" value=<?php echo($_SESSION["lookupkind"]); ?>>
                        <input type="hidden" name="lookupkey" value="<?php echo($_SESSION["lookupkey"]); ?>">
                        <input type="hidden" name="lookupkeykind" value=<?php echo($_SESSION["lookupkeykind"]); ?>>
                        <input type="hidden" name="lookupsection" value=<?php echo($_SESSION["lookupsection"]); ?>>
                        <input type="hidden" name="lookupposition" value=<?php echo($_SESSION["lookupposition"]); ?>>
                        <input type="hidden" name="lookupentry" value=<?php echo($_SESSION["lookupentry"]); ?>>
                        <input type="hidden" name="lookupcapacity" value=<?php echo($_SESSION["lookupcapacity"]) ?>>
                        <input type="hidden" name="lookupreceive" value=<?php echo($_SESSION["lookupreceive"]) ?>>
                        <input type="hidden" name="lookupyukyukind" value=<?php echo($_SESSION['lookupyukyukind']) ?>>
                        <input type="hidden" name="lookupyukyu" value=<?php echo($_SESSION["lookupyukyu"]) ?>>
                        <input type="hidden" name="lookupyukyufive" value=<?php echo($_SESSION["lookupyukyufive"]) ?>>
                        <input type="hidden" name="lookupyukyuf" value=<?php echo($_SESSION["lookupyukyuf"]) ?>>
                        <input type="hidden" name="histnum" value=-1>
                        <input type="hidden" name="retireflg" value=0>
                        <input type='hidden' name='offset' value='<?php echo $offset?>'>
                        <input type='button' name='historyDisp' value='経歴表示' title='教育・資格・移動経歴等の表示を行います。'
                            onClick='win_open("print/print_emp_branch_user.php?targetUser=<?php echo $res_y[$r]['uid']?>", 800, 700);'
                        >
                        </tr>
                    <?php
                    }
                } elseif (getCheckAuthority(48)) {
                    if ($res_y[$r]['sid'] == 34) {
                    ?>
                        <tr><td colspan=3 align="right">
                        <input type="hidden" name="userid" value="<?php echo($res_y[$r]['uid']); ?>">
                        <input type="hidden" name="kana" value="<?php echo(trim($res_y[$r]["kana"])); ?>">
                        <input type="hidden" name="name" value="<?php echo(trim($res_y[$r]["name"])); ?>">
                        <input type="hidden" name="section_name" value="<?php echo(trim($res_y[$r]["section_name"])); ?>">
                        <input type="hidden" name="lookupkind" value=<?php echo($_SESSION["lookupkind"]); ?>>
                        <input type="hidden" name="lookupkey" value="<?php echo($_SESSION["lookupkey"]); ?>">
                        <input type="hidden" name="lookupkeykind" value=<?php echo($_SESSION["lookupkeykind"]); ?>>
                        <input type="hidden" name="lookupsection" value=<?php echo($_SESSION["lookupsection"]); ?>>
                        <input type="hidden" name="lookupposition" value=<?php echo($_SESSION["lookupposition"]); ?>>
                        <input type="hidden" name="lookupentry" value=<?php echo($_SESSION["lookupentry"]); ?>>
                        <input type="hidden" name="lookupcapacity" value=<?php echo($_SESSION["lookupcapacity"]) ?>>
                        <input type="hidden" name="lookupreceive" value=<?php echo($_SESSION["lookupreceive"]) ?>>
                        <input type="hidden" name="lookupyukyukind" value=<?php echo($_SESSION['lookupyukyukind']) ?>>
                        <input type="hidden" name="lookupyukyu" value=<?php echo($_SESSION["lookupyukyu"]) ?>>
                        <input type="hidden" name="lookupyukyufive" value=<?php echo($_SESSION["lookupyukyufive"]) ?>>
                        <input type="hidden" name="lookupyukyuf" value=<?php echo($_SESSION["lookupyukyuf"]) ?>>
                        <input type="hidden" name="histnum" value=-1>
                        <input type="hidden" name="retireflg" value=0>
                        <input type='hidden' name='offset' value='<?php echo $offset?>'>
                        <input type='button' name='historyDisp' value='経歴表示' title='教育・資格・移動経歴等の表示を行います。'
                            onClick='win_open("print/print_emp_branch_user.php?targetUser=<?php echo $res_y[$r]['uid']?>", 800, 700);'
                        >
                        </tr>
                    <?php
                    }
                } elseif (getCheckAuthority(49)) {
                    if ($res_y[$r]['sid'] == 35) {
                    ?>
                        <tr><td colspan=3 align="right">
                        <input type="hidden" name="userid" value="<?php echo($res_y[$r]['uid']); ?>">
                        <input type="hidden" name="kana" value="<?php echo(trim($res_y[$r]["kana"])); ?>">
                        <input type="hidden" name="name" value="<?php echo(trim($res_y[$r]["name"])); ?>">
                        <input type="hidden" name="section_name" value="<?php echo(trim($res_y[$r]["section_name"])); ?>">
                        <input type="hidden" name="lookupkind" value=<?php echo($_SESSION["lookupkind"]); ?>>
                        <input type="hidden" name="lookupkey" value="<?php echo($_SESSION["lookupkey"]); ?>">
                        <input type="hidden" name="lookupkeykind" value=<?php echo($_SESSION["lookupkeykind"]); ?>>
                        <input type="hidden" name="lookupsection" value=<?php echo($_SESSION["lookupsection"]); ?>>
                        <input type="hidden" name="lookupposition" value=<?php echo($_SESSION["lookupposition"]); ?>>
                        <input type="hidden" name="lookupentry" value=<?php echo($_SESSION["lookupentry"]); ?>>
                        <input type="hidden" name="lookupcapacity" value=<?php echo($_SESSION["lookupcapacity"]) ?>>
                        <input type="hidden" name="lookupreceive" value=<?php echo($_SESSION["lookupreceive"]) ?>>
                        <input type="hidden" name="lookupyukyukind" value=<?php echo($_SESSION['lookupyukyukind']) ?>>
                        <input type="hidden" name="lookupyukyu" value=<?php echo($_SESSION["lookupyukyu"]) ?>>
                        <input type="hidden" name="lookupyukyufive" value=<?php echo($_SESSION["lookupyukyufive"]) ?>>
                        <input type="hidden" name="lookupyukyuf" value=<?php echo($_SESSION["lookupyukyuf"]) ?>>
                        <input type="hidden" name="histnum" value=-1>
                        <input type="hidden" name="retireflg" value=0>
                        <input type='hidden' name='offset' value='<?php echo $offset?>'>
                        <input type='button' name='historyDisp' value='経歴表示' title='教育・資格・移動経歴等の表示を行います。'
                            onClick='win_open("print/print_emp_branch_user.php?targetUser=<?php echo $res_y[$r]['uid']?>", 800, 700);'
                        >
                        </tr>
                    <?php
                    }
                } elseif (getCheckAuthority(50)) {
                    if ($res_y[$r]['sid'] == 32) {
                    ?>
                        <tr><td colspan=3 align="right">
                        <input type="hidden" name="userid" value="<?php echo($res_y[$r]['uid']); ?>">
                        <input type="hidden" name="kana" value="<?php echo(trim($res_y[$r]["kana"])); ?>">
                        <input type="hidden" name="name" value="<?php echo(trim($res_y[$r]["name"])); ?>">
                        <input type="hidden" name="section_name" value="<?php echo(trim($res_y[$r]["section_name"])); ?>">
                        <input type="hidden" name="lookupkind" value=<?php echo($_SESSION["lookupkind"]); ?>>
                        <input type="hidden" name="lookupkey" value="<?php echo($_SESSION["lookupkey"]); ?>">
                        <input type="hidden" name="lookupkeykind" value=<?php echo($_SESSION["lookupkeykind"]); ?>>
                        <input type="hidden" name="lookupsection" value=<?php echo($_SESSION["lookupsection"]); ?>>
                        <input type="hidden" name="lookupposition" value=<?php echo($_SESSION["lookupposition"]); ?>>
                        <input type="hidden" name="lookupentry" value=<?php echo($_SESSION["lookupentry"]); ?>>
                        <input type="hidden" name="lookupcapacity" value=<?php echo($_SESSION["lookupcapacity"]) ?>>
                        <input type="hidden" name="lookupreceive" value=<?php echo($_SESSION["lookupreceive"]) ?>>
                        <input type="hidden" name="lookupyukyukind" value=<?php echo($_SESSION['lookupyukyukind']) ?>>
                        <input type="hidden" name="lookupyukyu" value=<?php echo($_SESSION["lookupyukyu"]) ?>>
                        <input type="hidden" name="lookupyukyufive" value=<?php echo($_SESSION["lookupyukyufive"]) ?>>
                        <input type="hidden" name="lookupyukyuf" value=<?php echo($_SESSION["lookupyukyuf"]) ?>>
                        <input type="hidden" name="histnum" value=-1>
                        <input type="hidden" name="retireflg" value=0>
                        <input type='hidden' name='offset' value='<?php echo $offset?>'>
                        <input type='button' name='historyDisp' value='経歴表示' title='教育・資格・移動経歴等の表示を行います。'
                            onClick='win_open("print/print_emp_branch_user.php?targetUser=<?php echo $res_y[$r]['uid']?>", 800, 700);'
                        >
                        </tr>
                    <?php
                    }
                } elseif (getCheckAuthority(51)) {
                    if ($res_y[$r]['sid'] == 2) {
                    ?>
                        <tr><td colspan=3 align="right">
                        <input type="hidden" name="userid" value="<?php echo($res_y[$r]['uid']); ?>">
                        <input type="hidden" name="kana" value="<?php echo(trim($res_y[$r]["kana"])); ?>">
                        <input type="hidden" name="name" value="<?php echo(trim($res_y[$r]["name"])); ?>">
                        <input type="hidden" name="section_name" value="<?php echo(trim($res_y[$r]["section_name"])); ?>">
                        <input type="hidden" name="lookupkind" value=<?php echo($_SESSION["lookupkind"]); ?>>
                        <input type="hidden" name="lookupkey" value="<?php echo($_SESSION["lookupkey"]); ?>">
                        <input type="hidden" name="lookupkeykind" value=<?php echo($_SESSION["lookupkeykind"]); ?>>
                        <input type="hidden" name="lookupsection" value=<?php echo($_SESSION["lookupsection"]); ?>>
                        <input type="hidden" name="lookupposition" value=<?php echo($_SESSION["lookupposition"]); ?>>
                        <input type="hidden" name="lookupentry" value=<?php echo($_SESSION["lookupentry"]); ?>>
                        <input type="hidden" name="lookupcapacity" value=<?php echo($_SESSION["lookupcapacity"]) ?>>
                        <input type="hidden" name="lookupreceive" value=<?php echo($_SESSION["lookupreceive"]) ?>>
                        <input type="hidden" name="lookupyukyukind" value=<?php echo($_SESSION['lookupyukyukind']) ?>>
                        <input type="hidden" name="lookupyukyu" value=<?php echo($_SESSION["lookupyukyu"]) ?>>
                        <input type="hidden" name="lookupyukyufive" value=<?php echo($_SESSION["lookupyukyufive"]) ?>>
                        <input type="hidden" name="lookupyukyuf" value=<?php echo($_SESSION["lookupyukyuf"]) ?>>
                        <input type="hidden" name="histnum" value=-1>
                        <input type="hidden" name="retireflg" value=0>
                        <input type='hidden' name='offset' value='<?php echo $offset?>'>
                        <input type='button' name='historyDisp' value='経歴表示' title='教育・資格・移動経歴等の表示を行います。'
                            onClick='win_open("print/print_emp_branch_user.php?targetUser=<?php echo $res_y[$r]['uid']?>", 800, 700);'
                        >
                        </tr>
                    <?php
                    }
                } elseif (getCheckAuthority(52)) {
                    if ($res_y[$r]['sid'] == 3) {
                    ?>
                        <tr><td colspan=3 align="right">
                        <input type="hidden" name="userid" value="<?php echo($res_y[$r]['uid']); ?>">
                        <input type="hidden" name="kana" value="<?php echo(trim($res_y[$r]["kana"])); ?>">
                        <input type="hidden" name="name" value="<?php echo(trim($res_y[$r]["name"])); ?>">
                        <input type="hidden" name="section_name" value="<?php echo(trim($res_y[$r]["section_name"])); ?>">
                        <input type="hidden" name="lookupkind" value=<?php echo($_SESSION["lookupkind"]); ?>>
                        <input type="hidden" name="lookupkey" value="<?php echo($_SESSION["lookupkey"]); ?>">
                        <input type="hidden" name="lookupkeykind" value=<?php echo($_SESSION["lookupkeykind"]); ?>>
                        <input type="hidden" name="lookupsection" value=<?php echo($_SESSION["lookupsection"]); ?>>
                        <input type="hidden" name="lookupposition" value=<?php echo($_SESSION["lookupposition"]); ?>>
                        <input type="hidden" name="lookupentry" value=<?php echo($_SESSION["lookupentry"]); ?>>
                        <input type="hidden" name="lookupcapacity" value=<?php echo($_SESSION["lookupcapacity"]) ?>>
                        <input type="hidden" name="lookupreceive" value=<?php echo($_SESSION["lookupreceive"]) ?>>
                        <input type="hidden" name="lookupyukyukind" value=<?php echo($_SESSION['lookupyukyukind']) ?>>
                        <input type="hidden" name="lookupyukyu" value=<?php echo($_SESSION["lookupyukyu"]) ?>>
                        <input type="hidden" name="lookupyukyufive" value=<?php echo($_SESSION["lookupyukyufive"]) ?>>
                        <input type="hidden" name="lookupyukyuf" value=<?php echo($_SESSION["lookupyukyuf"]) ?>>
                        <input type="hidden" name="histnum" value=-1>
                        <input type="hidden" name="retireflg" value=0>
                        <input type='hidden' name='offset' value='<?php echo $offset?>'>
                        <input type='button' name='historyDisp' value='経歴表示' title='教育・資格・移動経歴等の表示を行います。'
                            onClick='win_open("print/print_emp_branch_user.php?targetUser=<?php echo $res_y[$r]['uid']?>", 800, 700);'
                        >
                        </tr>
                    <?php
                    }
                } elseif (getCheckAuthority(55)) {
                    if ($res_y[$r]['sid'] == 17 || $res_y[$r]['sid'] == 34 || $res_y[$r]['sid'] == 35) {
                    ?>
                        <tr><td colspan=3 align="right">
                        <input type="hidden" name="userid" value="<?php echo($res_y[$r]['uid']); ?>">
                        <input type="hidden" name="kana" value="<?php echo(trim($res_y[$r]["kana"])); ?>">
                        <input type="hidden" name="name" value="<?php echo(trim($res_y[$r]["name"])); ?>">
                        <input type="hidden" name="section_name" value="<?php echo(trim($res_y[$r]["section_name"])); ?>">
                        <input type="hidden" name="lookupkind" value=<?php echo($_SESSION["lookupkind"]); ?>>
                        <input type="hidden" name="lookupkey" value="<?php echo($_SESSION["lookupkey"]); ?>">
                        <input type="hidden" name="lookupkeykind" value=<?php echo($_SESSION["lookupkeykind"]); ?>>
                        <input type="hidden" name="lookupsection" value=<?php echo($_SESSION["lookupsection"]); ?>>
                        <input type="hidden" name="lookupposition" value=<?php echo($_SESSION["lookupposition"]); ?>>
                        <input type="hidden" name="lookupentry" value=<?php echo($_SESSION["lookupentry"]); ?>>
                        <input type="hidden" name="lookupcapacity" value=<?php echo($_SESSION["lookupcapacity"]) ?>>
                        <input type="hidden" name="lookupreceive" value=<?php echo($_SESSION["lookupreceive"]) ?>>
                        <input type="hidden" name="lookupyukyukind" value=<?php echo($_SESSION['lookupyukyukind']) ?>>
                        <input type="hidden" name="lookupyukyu" value=<?php echo($_SESSION["lookupyukyu"]) ?>>
                        <input type="hidden" name="lookupyukyufive" value=<?php echo($_SESSION["lookupyukyufive"]) ?>>
                        <input type="hidden" name="lookupyukyuf" value=<?php echo($_SESSION["lookupyukyuf"]) ?>>
                        <input type="hidden" name="histnum" value=-1>
                        <input type="hidden" name="retireflg" value=0>
                        <input type='hidden' name='offset' value='<?php echo $offset?>'>
                        <input type='button' name='historyDisp' value='経歴表示' title='教育・資格・移動経歴等の表示を行います。'
                            onClick='win_open("print/print_emp_branch_user.php?targetUser=<?php echo $res_y[$r]['uid']?>", 800, 700);'
                        >
                        </tr>
                    <?php
                    }
                }
            }
            ?>
        </table>
        </form>
        </td>
    </tr>
<?php
        }
        echo("<tr>\n");
        echo("<td><form method='post' action='emp_menu.php?func=" . FUNC_LOOKUP . "'><table width='100%'>\n");
        echo("<input type='hidden' name='func' value='" . FUNC_LOOKUP . "'>\n");
        echo("<input type='hidden' name='resrows' value=$rows>\n");
        echo("<input type='hidden' name='offset' value=$offset></td><tr>\n");
        if(0<=$offset-VIEW_LIMIT)
            echo("<td align='left'><input disable type='submit' name='lookup_prev' value='前へ'></td>\n");
        if($rows>$offset+VIEW_LIMIT){
            if(0==$offset)
                echo("<td colspan=2 align='right'><input type='submit' name='lookup_next' value='次へ'></td>\n");
            else
                echo("<td align='right'><input type='submit' name='lookup_next' value='次へ'></td>\n");
        }
        echo("</tr></table></form>\n");
        echo("</tr>\n");
    }
?>
</table>
