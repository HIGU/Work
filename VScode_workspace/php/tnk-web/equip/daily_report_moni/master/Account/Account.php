<?php 
//////////////////////////////////////////////////////////////////////////////
// 設備稼働管理システムの権限マスター保守               Client interface 部 //
// Copyright (C) 2004-2006 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2004/07/01 Created   Account.php                                         //
// 2006/04/12 MenuHeader クラス対応                                         //
// 2006/04/14 iframe版へ変更 Ajax対応   style='overflow-y:hidden;' 追加     //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug 用
ini_set('error_reporting', E_ALL);          // E_STRICT=2048(php5) E_ALL=2047 debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮
session_start();                            // ini_set()の次に指定すること Script 最上行

require_once ('../../../../MenuHeader.php');// TNK 全共通 menu class
require_once ('../../../../function.php');  // access_log()等で使用
require_once ('../../com/define.php');
require_once ('../../com/function.php');
require_once ('../../com/PageControl.php');
access_log();                               // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader(0);                  // 認証チェック0=一般以上 戻り先=TOP_MENU タイトル未設定
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('権限マスターの保守');
////////////// target設定
// $menu->set_target('_parent');               // フレーム版の戻り先はtarget属性に_parentが必須
//////////// ブラウザーのキャッシュ対策用
$uniq = $menu->set_useNotCache('equipAccount');

// 共通ヘッダの出力
SetHttpHeader();

// メッセージのクリア
$Message = '';

// 管理者モード
$AdminUser = AdminUser( FNC_ACCOUNT );

if (@$_SESSION['User_ID'] == '$$$') $AdminUser = true;


// 処理コードを取得
$ProcCode = @$_REQUEST['ProcCode'];

// コネクションの取得
$con = getConnection();

if ($ProcCode == 'ADD') {
    // 追加モード
    AddUser();
} else if ($ProcCode == 'DEL') {
    // 削除モード
    DelUser();
}

// --------------------------------------------------
// 管理者の追加
// --------------------------------------------------
function AddUser()
{
    global $con,$Message;
    
    // パラメータの取得
    $fnc   = $_REQUEST['Function'];
    $staff = $_REQUEST['Staff'];
    $user  = $_SESSION['User_ID'];
    
    // 重複登録のチェック
    $rs = pg_query($con,"select * from equip_account where function='$fnc' and staff='$staff'");
    if ($row = pg_fetch_array ($rs)) {
        $Message .= "社員番号[$staff]はすでに登録されています。";
        return;
    }
    
    pg_query ($con , 'BEGIN');
    
    // ユーザの登録
    if (!pg_query($con,"insert into equip_account (function,staff,last_user) values('$fnc','$staff','$user')")) {
        pg_query ($con , 'ROLLBACK');
        $SYSTEM_MESSAGE = "データベースの更新に失敗しました\n$sql";
        require_once ('../com/' . ERROR_PAGE);
        exit();
    }
    
    pg_query ($con , 'COMMIT');
}
// --------------------------------------------------
// 管理者の削除
// --------------------------------------------------
function DelUser()
{
    global $con;
    
    // パラメータの取得
    $fnc   = @$_REQUEST['Function'];
    $staff = @$_REQUEST['Staff'];

    pg_query ($con , 'BEGIN');
    
    // ユーザの削除
    if (!pg_query($con,"delete from equip_account where function='$fnc' and staff='$staff'")) {
        pg_query ($con , 'ROLLBACK');
        $SYSTEM_MESSAGE = "データベースの更新に失敗しました\n$sql";
        require_once ('../com/' . ERROR_PAGE);
        exit();
    }
    
    pg_query ($con , 'COMMIT');
}

?>
<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.01 Transitional//EN'>
<html>
<head>
<?php require_once ('../../com/PageHeaderOnly.php'); ?>
<title><?php echo $menu->out_title() ?></title>
<?php echo $menu->out_css() ?>
<LINK rel='stylesheet' href='<?php echo CONTEXT_PATH?>com/cssConversion.css' type='text/css'>
<script type='text/javascript' src='Account.js?<?php echo $uniq ?>'></script>
<script language='JavaScript'>
function init() {
<?php if ($Message != '') { ?>
    alert('<?php echo $Message?>');
<?php } ?>
    AccountOBJ.AjaxLoadTable("List", "showAjax");
}
function add() {
    
    if (document.MainForm._Staff.value == '') {
        alert('社員コードを入力して下さい');
        return;
    }
    
    for (i = 0; i < document.MainForm._Function.length; i++) {
        if (document.MainForm._Function[i].checked) {
            break;
        }
    }
    document.MainForm.ProcCode.value = 'ADD';
    document.MainForm.Function.value = document.MainForm._Function[i].value;
    document.MainForm.Staff.value = document.MainForm._Staff.value;
    document.MainForm.submit();
}
function del(fnc,staff) {
    document.MainForm.ProcCode.value = 'DEL';
    document.MainForm.Function.value = fnc;
    document.MainForm.Staff.value = staff;
    document.MainForm.submit();
}
function doBack() {
    document.MainForm.action = '<?=@$_REQUEST['RetUrl']?>';
    document.MainForm.submit();
}
</script>
</head>
<body onLoad='init();' style='overflow-y:hidden;'>
<center>
<?php echo $menu->out_title_border() ?>

    <form name='MainForm' action='<?php echo $menu->out_self()?>' method='post'>
    <input type='hidden' name='RetUrl' value='<?=@$_REQUEST['RetUrl']?>'>
    <input type='hidden' name='ProcCode' value=''>
    <input type='hidden' name='Function' value=''>
    <input type='hidden' name='Staff' value=''>
        <?php if ($AdminUser) { ?>
            <table border='1' class='Conversion'>
                <tr>
                    <td class='HED Conversion'>
                        権限
                    </td>
                    <td class='Conversion'>
                        <table border='0' class='LAYOUT'>
                            <tr class='LAYOUT'>
                                <td class='LAYOUT'>
                                    <input type='radio' name='_Function' value='<?=FNC_MASTER?>' checked id='id1'><label for='id1'>マスター(<?=FNC_MASTER?>)</label>
                                </td>
                                <td class='LAYOUT'>
                                    <input type='radio' name='_Function' value='<?=FNC_REPORT?>' id='id2'><label for='id2'>運転日報メンテナンス(<?=FNC_REPORT?>)</label><br>
                                </td>
                            </tr>
                            <tr class='LAYOUT'>
                                <td class='LAYOUT'>
                                    <input type='radio' name='_Function' value='<?=FNC_REPORT_ACCEPT?>' id='id3'><label for='id3'>運転日報承認(<?=FNC_REPORT_ACCEPT?>)</label>
                                </td>
                                <td class='LAYOUT'>
                                    <input type='radio' name='_Function' value='<?=FNC_ACCOUNT?>' id='id4'><label for='id4'>権限設定(<?=FNC_ACCOUNT?>)</label>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr class='Conversion'>
                    <td class='HED Conversion'>
                        社員コード
                    </td>
                    <td class='Conversion'>
                        <input type='text' name='_Staff' value=''>
                        <input type='button' value='追加' onClick='add()'>
                        <input type='button' value='戻　る' style='width:80;' onClick='doBack()'>

                    </td>
                </tr>
            </table>
            <br>
        <?php } ?>
    </form>
    <div id='showAjax'>
    </div>
</center>
</body>
</html>
<?php ob_end_flush(); ?>
