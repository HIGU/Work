<?php
//////////////////////////////////////////////////////////////////////////////
// 社員情報管理の include file 自己情報表示                                 //
// Copyright(C) 2001-2021 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp       //
// Changed history                                                          //
// 2001/07/07 Created  view_mineinfo.php                                    //
// 2002/08/07 セッション管理を追加 & register_globals = Off 対応            //
// 2004/04/16 表示情報の一部を変更 タブからスペース変換(ソース)             //
// 2005/01/17 access_log の変更と view_file_name(__FILE__) の追加           //
// 2005/01/26 background-imageを追加してデザイン変更(AM/PMで切替式)         //
// 2005/11/24 パスワードを暗号化されているため表示項目から削除(コメント)    //
//            自己の写真の大きさをオリジナルサイズから 128 X 192 へ変更     //
// 2007/08/30 写真クリックで別ウィンドウにズーム表示(簡易)追加              //
// 2007/09/11 uniqid(abcdef) → $uniq へ ('')がぬけている                   //
// 2007/10/15 自己の経歴(教育・資格・移動等) 表示(印刷)メニューを追加       //
// 2015/01/30 有給残数の表示を追加                                     大谷 //
// 2015/03/20 有給残を別プログラムで計算し、ここでは取得のみに変更     大谷 //
// 2015/03/27 自己情報表示はしないようにした(コメント化)               大谷 //
// 2016/12/15 自己情報に有給残を表示するように変更                     大谷 //
// 2019/12/06 自己情報に有給5日取得の情報を表示するように変更          大谷 //
// 2021/06/28 今期取得目安は基準日が今期のみに表示するよう変更              //
//            前期が基準日の場合、計算がおかしくなる。前期が基準日の場合    //
//            日数が合算される為、目安はいらない。                          //
//            合わせて22期より年6日で色々計算するよう変更             大谷  //
//////////////////////////////////////////////////////////////////////////////
// access_log("view_mineinfo.php");        // Script Name 手動設定
access_log(substr(__FILE__, strlen($_SERVER['DOCUMENT_ROOT'])));
echo view_file_name(__FILE__);
$query = "select * from user_master where uid='" . $_SESSION["User_ID"] . "'";
$res = array();
if (getResult($query,$res)) {
    $mailaddr=$res[0]["mailaddr"];
    $mailaddr_pos=strpos(trim($mailaddr),'@');
    $acount=trim(substr($mailaddr,0,$mailaddr_pos));
    $passwd = str_repeat('*', strlen(trim($res[0]['passwd'])));
}
$query = "select ud.name,ud.kana,ud.photo,sm.section_name,pm.position_name,ud.pid from user_detailes ud,section_master sm,position_master pm" .
     " where uid='" . $_SESSION["User_ID"] . "' and ud.sid=sm.sid and ud.pid=pm.pid";
$res = array();
if (getResult($query,$res)) {

?>
    <table width='100%'>
        <tr><td colspan='2' bgcolor='#003e7c' align='center' class='nasiji'>
            <font color='#ffffff'>ユーザーの情報</font></td>
        </tr>
        <tr><td valign="top">
            <table width="100%">
                <tr><td width="30%">社員No.</td>
                    <td><?php echo($_SESSION["User_ID"]); ?></td>
                </tr>
                <tr><td width="30%">名前</td>
                    <td><font size=1><?php echo(trim($res[0]["kana"])); ?></font><br><?php echo(trim($res[0]["name"])); ?></td>
                    <?php
                    //if ($_SESSION['User_ID'] == '300144') {
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
                        $query = "
                                SELECT
                                     current_day    AS 当期有給日数     -- 0
                                    ,holiday_rest   AS 当期有給残       -- 1
                                    ,half_holiday   AS 半日有給回数     -- 2
                                    ,time_holiday   AS 時間休取得分     -- 3
                                    ,time_limit     AS 時間有給限度     -- 4
                                    ,web_ymd        AS 更新年月日       -- 5
                                FROM holiday_rest_master
                                WHERE uid='{$_SESSION['User_ID']}' and ki={$ki};
                            ";
                        getResult2($query, $yukyu);
                        $kintai_ym         = substr($yukyu[0][5], 0, 4) . "年" . substr($yukyu[0][5], 4, 2) . "月" . substr($yukyu[0][5], 6, 2) . "日";
                        // 有給5日情報表示
                        // 基準日のデータがない場合は除外
                        $query = sprintf("SELECT uid,reference_ym FROM five_yukyu_master WHERE uid='%s' and business_year=%d", $_SESSION['User_ID'], $business_year);
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
                        $end_rmd = $res_c[0][1] + 10000;
                        if ($rows_c > 0) {
                            $query = "
                                SELECT   uid          AS 社員番号 --00 
                                        ,working_date AS 取得日   --01
                                        ,working_day  AS 曜日     --02
                                        ,absence      AS 不在理由 --03
                                        ,str_mc       AS 出勤ＭＣ --04
                                        ,end_mc       AS 退勤ＭＣ --05
                                FROM working_hours_report_data_new WHERE uid='{$_SESSION['User_ID']}' and working_date >= {$res_c[0][1]} and
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
                                FROM working_hours_report_data_new WHERE uid='{$_SESSION['User_ID']}' and working_date >= {$res_c[0][1]} and
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
                                FROM five_yukyu_master WHERE uid='{$_SESSION['User_ID']}' and business_year={$business_year}
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
                            $indication_flg = 0;            // フラグOFF
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
                    <td style='font-size:0.90em;'>
                        <?php echo "　{$kintai_ym}現在<BR>　有給残 <font color='red'>{$yukyu[0][1]}</font>/{$yukyu[0][0]}日<BR>　半休 <font color='red'>{$yukyu[0][2]}</font>/20 回　時間休 <font color='red'>{$yukyu[0][3]}</font>/{$yukyu[0][4]} 時間\n"; ?>
                    </td>
                    
                    <?php
                    //}
                    ?>
                </tr>
                <tr>
                    <td width="30%">所属</td>
                    <td><?php echo(trim($res[0]["section_name"])); ?></td>
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
                </tr>
                <tr>
                    <td width='30%' title='教育・資格・移動経歴等の表示を行います。'>経歴</td>
                    <td>
                        <input type='button' name='historyDisp' value='表示' title='教育・資格・移動経歴等の表示を行います。'
                            onClick='win_open("print/print_emp_branch_user.php?targetUser=<?php echo $_SESSION['User_ID']?>", 800, 700);'
                        >
                        <!--
                        <a href='javascript:void(0)'
                            onClick='win_open("print/print_emp_history_user.php?targetUser=<?php echo $_SESSION['User_ID']?>", 600, 700);'
                        >表示</a>
                        -->
                    </td>
                </tr>
                <!--
                <tr>
                    <td width="30%">パスワード</td>
                    <td><?php echo($passwd); ?></td>
                </tr>
                -->
            </table>
            </td>
<?php
        if ($res[0]['photo']) {
            $file = IND . $_SESSION['User_ID'] . '.gif?' . $uniq ;
            getObject($res[0]['photo'], $file);
            echo "<td align='right'><img src='{$file}'onClick='win_open(\"{$file}\", 276, 412);' width='128' height='192' border='0'></td>\n";
        }
?>
        </tr>
    </table>
    <form method="post" action="chg_passwd.php?func=<?php echo $request->get('func'); ?>" onSubmit="return chkPasswd(this)">
    <input type="hidden" name="userid" value="<?php echo($_SESSION["User_ID"]); ?>">
    <input type="hidden" name="func" value=<?php echo $request->get('func'); ?>>
    <table width="40%" align="right">
        <tr><td colspan=2 bgcolor="ff6600" align="center">
            <font color="#ffffff">パスワードの変更</font></td>
        </tr>
        <tr><td>新しいパスワード</td>
            <td align="right"><input type="password" name="passwd" size=12 maxlength=8></td>
        </tr>
        <tr><td>確認パスワード</td>
            <td align="right"><input type="password" name="repasswd" size=12 maxlength=8></td>
            <td align="right"><input type="hidden" name="acount" value="<?php echo $request->get('acount'); ?>"></td>
        </tr>
        <tr><td colspan=2 align="right"><input type="submit" value="変更"></td>
        </tr>
    </table>
    </form>
<?php   
    }else{
?>
    <table width="100%">
        <tr><td colspan=2 bgcolor="#003e7c" align="center">
            <font color="#ffffff">ユーザーの情報</font></td>
        </tr>
    </table>
<script language="javascript">
    alert("貴方の情報がデータベースに存在しません。管理者にお問い合わせください。");
</script>
<?php   
    }
?>