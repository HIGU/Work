<?php
//////////////////////////////////////////////////////////////////////////////
// 会議(打合せ)のスケジュール表：総合届承認待ち情報を表示                   //
// Copyright (C) 2021-2021 Ryota.Waki ryota_waki@nitto-kohki.co.jp          //
// Changed history                                                          //
// 2021/11/17 Created   meeting_schedule_sougou_admit_list.php              //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
ini_set('max_execution_time', 60);          // 最大実行時間=60秒 WEB CGI版
//ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮
//session_start();                            // ini_set()の次に指定すること Script 最上行
require_once ('../function.php');     // define.php と pgsql.php を require_once している
require_once ('../MenuHeader.php');   // TNK 全共通 menu class
require_once ('../ControllerHTTP_Class.php');       // TNK 全共通 MVC Controller Class
require_once ('meeting_schedule_Model.php');        // MVC の Model部
access_log();                               // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader(-1);                   // 認証チェック -1=認証なし

//////////// リクエストオブジェクトの取得
$request = new Request();

//////////// リザルトのインスタンス生成
$result = new Result();

//////////// セッション オブジェクトの取得
$session = new Session();

$menu->set_title('総合届承認待ち情報');     // タイトルを入れないとIEの一部のバージョンで表示できない不具合あり

///// カレントの年月日が設定されているかチェック
if ($request->get('year') == '' || $request->get('month') == '' || $request->get('day') == '') {
    // 初期値(本日)を設定
    $request->add('year', date('Y')); $request->add('month', date('m')); $request->add('day', date('d'));
}

///// 一覧表示時の期間(1日間,7日間,14,28...)
if ($request->get('listSpan') == '') {
    if ($session->get_local('listSpan') != '') {
        $request->add('listSpan', $session->get_local('listSpan'));
    } else {
        $request->add('listSpan', '0');             // 初期値(本日のみ)
    }
}
$session->add_local('listSpan', $request->get('listSpan')); // セッションデータも変更

//////////// ビジネスモデル部のインスタンス生成
$model = new MeetingSchedule_Model($request);

////////// 更新時間情報をセット
$up_time = 10000;   // 更新時間 10秒
//$up_time = 20000;   // 更新時間 20秒
//$up_time = 30000;   // 更新時間 30秒
//$up_time = 60000;   // 更新時間  1分
$up_info = $up_time/1000;
if( $up_info < 60 ) {
    $up_info .= "秒毎更新";
} else{
    $up_info = $up_info/60;
    $up_info .= "分毎更新";
}
$up_info .= "（変更可能）";
$now = date("Y/m/d　H:i:s");
$up_title = "{$up_info}<BR>【 {$now} 】";

////////// 総合届 承認待ちあり 承認者UID取得
$query = "
            SELECT DISTINCT admit_status
            FROM            sougou_deteils
            WHERE           admit_status!='END' AND admit_status!='DENY' AND admit_status!='CANCEL'
         ";
$admit_uid = array();
$admit_idx = getResult2($query, $admit_uid);

////////// ログインユーザーID、役職をセット
$login_uid = $_SESSION['User_ID'];  // リンク付き、赤文字で表示させるユーザー
if( $login_uid == '300667' ) $debug = true; else $debug = false;
if($debug){
//$login_uid = '015989';// 係長
//$login_uid = '300144';// 係長
//$login_uid = '017507';// 課長
//$login_uid = '016080';// 課長
//$login_uid = '016713';// 部長
//$login_uid = '300055';// 総務課長
//$login_uid = '017850';// 管理部長
//$login_uid = '011061';// 工場長
$debug = false;
}
$login_post = getPost($login_uid);

////////// pid 取得
function getPid($uid)
{
    $query = "SELECT pid FROM user_detailes WHERE uid = '$uid'";
    $res = array();
    if( getResult2($query, $res) <= 0 ) return '';
    return $res[0][0];
}

////////// act_id 取得
function getActid($uid)
{
    $query = "SELECT act_id FROM cd_table WHERE uid = '$uid'";
    $res = array();
    if( getResult2($query, $res) <= 0 ) return '';
    return $res[0][0];
}

////////// 役職は？
function getPost($uid)
{
    $pid    = getPid($uid);
    $act_id = getActid($uid);
    
    switch ($pid) {
        case 110:// 工場長
            $post = 'ko';
            break;
        case 47:// 部長代理
        case 70:// 部長
        case 95:// 副工場長
            if( $act_id == 610 ) {
                $post = 'kb';// 管理部長
            } else {
                $post = 'bu';
            }
            break;
        case 46:// 課長代理
        case 50:// 課長
            if( $act_id == 650 || $act_id == 651 || $act_id == 660 ) {
                $post = 'sk';// 総務課長
            } else {
                $post = 'ka';
            }
            break;
        case 31:// 係長Ｂ
        case 32:// 係長Ａ
            $post = 'kk';
            break;
        default:// 一般
            $post = '';
            break;
    }
    return $post;
}

////////// 部署名取得
function getDeploy($uid)
{
    $act_id   = getActid($uid);
    if( $uid == '012394') $act_id = 582;
    
    switch ($act_id) {
        case 600:
            return "工場長";
        case 610:   // 管理部
            return "管理部";
        case 605:   // ＩＳＯ事務局
        case 650:   // 管理部 総務課
        case 651:   // 管理部 総務課 総務
        case 660:   // 管理部 総務課 財務
            return "管理部 総務課";
        case 670:   // 管理部 商品管理課
            return "管理部 商品管理課";
        case 501:   // 技術部
            return "技術部";
        case 174:   // 技術部 品質管理課
        case 517:   // 技術部 品質管理課 カプラ検査担当
        case 537:   // 技術部 品質管理課 カプラ検査担当
        case 581:   // 技術部 品質管理課 カプラ検査担当
            return "技術部 品質管理課";
        case 173:   // 技術部 技術課
        case 515:   // 技術部 技術課
        case 535:   // 技術部 技術課
            return "技術部 技術課";
        case 582:   // 製造部
            return "製造部";
        case 518:   // 製造部 製造１課
        case 519:   // 製造部 製造１課
        case 556:   // 製造部 製造１課
        case 520:   // 製造部 製造１課
            return "製造部 製造１課";
        case 547:   // 製造部 製造２課
        case 528:   // 製造部 製造２課
        case 527:   // 製造部 製造２課
            return "製造部 製造２課";
        case 500:   // 生産部
            return "生産部";
        case 545:   // 生産部 生産管理課
        case 512:   // 生産部 生産管理課 計画係 Ｃ担当
        case 532:   // 生産部 生産管理課 計画係 Ｌ担当
        case 513:   // 生産部 生産管理課 購買係 Ｃ担当
        case 533:   // 生産部 生産管理課 購買係 Ｌ担当
        case 514:   // 生産部 生産管理課 資材係 カプラ資材
        case 534:   // 生産部 生産管理課 資材係 リニア資材
            return "生産部 生産管理課";
        case 176:   // 生産部 カプラ組立課
        case 522:   // 生産部 カプラ組立MA担当
        case 523:   // 生産部 カプラ組立HA担当
        case 525:   // 生産部 カプラ特注担当
            return "生産部 カプラ組立課";
        case 551:   // 生産部 リニア組立課
        case 175:   // 生産部 リニア組立担当
        case 572:   // 生産部 ピストン研磨担当
            return "生産部 リニア組立課";
        default:
            return "";
    }
}

////////// 表示する？
function IsView($l_post, $l_uid, $uid)
{
    switch ($l_post) {  // ログインユーザーの役職は？
        case 'kk':  // 係長
            if( $uid != $l_uid ) return false;// ログインユーザー 以外 非表示
//        case 'sk':  // 総務課長
//        case 'kb':  // 管理部長
        case 'ko':  // 工場長
            return true;    // 全員分表示
        default:    // その他
            break;
    }
    
    if( $l_post == 'sk' || $l_post == 'kb' ) {
        $post = getPost($uid);  // 承認者の役職をセット
        switch ($l_post) {  // ログインユーザーの役職は？
            case 'sk':  // 総務課長
                if( $post == 'kb' || $post == 'ko' ) return false;// 管理部長 工場長 非表示
            case 'kb':  // 管理部長
                if( $post == 'ko' ) return false;// 工場長 非表示
            default:    // その他
                break;
        }
        return true;    // 表示
    } else {
        if( ! strstr(getDeploy($uid), getDeploy($l_uid)) ) return false; // 部署が違うなら 非表示
    }
    
    $post = getPost($uid);  // 承認者の役職をセット
    switch ($l_post) {  // ログインユーザーの役職は？
        case 'ka':  // 課長
            if( $uid != $l_uid && $post != 'kk' ) return false;// ログインユーザー＋係長 以外 非表示
            break;
        case 'bu':  // 部長
            if( $uid != $l_uid && $post != 'kk' && $post != 'ka' ) return false;// ログインユーザー＋係長＋課長 以外 非表示
            break;
        default:    // 一般 その他
            return false;   // 表示しない
    }
    
    return true;
}
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<title><?php echo $menu->out_title() ?></title>
<?php echo $menu->out_site_java() ?>
<?php echo $menu->out_css() ?>
<?php echo $menu->out_jsBaseClass() ?>
<link rel='stylesheet' href='meeting_schedule.css' type='text/css' media='screen'>
<script type='text/javascript' src='meeting_schedule.js'></script>

<style>
</style>

</head>
<body onLoad="setTimeout('ControlForm.submit()', <?php echo $up_time; ?>);">
<center>

    <form name='ControlForm' action='<?php echo $menu->out_self(); ?>' method='post'>
        <?php if( $model->getSougouAdmitCnt($login_uid) > 0 ) { ?>
            <script>this.focus();</script>
        <?php } ?>
<!--
    <input type="submit" value="更新　TEST"       name="commit" onClick=''>　
    <input type="button" value="[×]閉じる" name="close"  onClick='window.parent.close();'>
-->
        <?php
        echo $up_title; // 更新情報表示
        ?>
        <BR>    <!-- 承認待ち情報 表示 -->
        <table class='pt10' border="1" cellspacing="0">
        <tr><td> <!----------- ダミー(デザイン用) ------------>
            <table width='100%' class='winbox_field' bgcolor='#D8D8D8' align='center' border='1' cellspacing='0' cellpadding='3'>
                <caption style='background-color:DarkCyan; color:White;'><div class='caption_font'>承認待ち情報</div></caption>
                <tr style='background-color:yellow; color:blue;'><!-- 項目表示 -->
                    <td nowrap align='center'>承 認 者</td>
                    <td nowrap align='center'>件　数</td>
                    <td align='center'>出勤状況</td>
<!--
                    <td align='center'>通　知</td>
-->
                </tr>
                <?php
if($debug){
                echo "ログイン者:{$model->getUidName($login_uid)}<BR>";
                $deploy = getDeploy($login_uid);
                echo "部署名:{$deploy}　役職:[{$login_post}]<BR>"; // post = ''/'kk'/'ka'/'bu'/'sk'/'kb'/'ko'
                echo "=============================<BR>";
}
                $view_on = false;// 初期値：表示してない
                for( $r=0; $r<$admit_idx; $r++ ) {  // 総合届 承認待ち 承認者分ループ
                    $uid  = $admit_uid[$r][0];      // 承認者のUIDをセット
                    $view = IsView( $login_post, $login_uid, $uid); // 表示するかしないか判断する
if($debug){
                    echo "{$model->getUidName($uid)}:";
                    if( ! $view ) {
                        echo "[表示しない]<BR>";
                    } else {
                        echo "[表示する]<BR>";
                    }
}
                    if( ! $view ) continue;
                ?>
                <tr><!-- 各情報の表示 -->
                    <td nowrap align='center'>
                    <?php
                    // ログインユーザー名は、リンク付きの表示にする
                    if( $login_uid == $uid ) {
//                        echo "<a href='http://masterst/per_appli/in_sougou_admit/sougou_admit_Main.php?calUid={$uid}' target='_blank' style='text-decoration:none;'><font style='color:red;'>{$model->getUidName($uid)}</font></a>";
                        echo "<a href='http://masterst/per_appli/in_sougou_admit/sougou_admit_Main.php?calUid={$uid}' target='_blank'><font style='color:red;'>{$model->getUidName($uid)}</font></a>";
                    } else {
                        echo $model->getUidName($uid);
                    }
                    ?>
                    </td>
                    <td align='center'><?php echo $model->getSougouAdmitCnt($uid); ?> 件</td>
                    <td align='center'><?php echo $model->getAbsence($uid); ?></td>
<!-- 承認するよう通知を送る
                    <td align='center'><input type='button' value='送信' onClick=''></td>
-->
                </tr>
                <?php
                    $view_on = true;// 表示した
                } // for() End.
                ?>
                
                <?php
                if( ! $view_on ) {// 表示してない
                    echo "<tr><td align='center' colspan='4'><BR>現在、承認待ちは<BR>ありません<BR>　</td></tr>";
                }
                ?>
            </table>
        </tr></td> <!----------- ダミー(デザイン用) ------------>
        </table>
    </form>
</center>
</body>

<?php echo $menu->out_alert_java()?>
</html>
