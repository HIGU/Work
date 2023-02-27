<?php
//////////////////////////////////////////////////////////////////////////////
// ���(�ǹ礻)�Υ������塼��ɽ������Ͼ�ǧ�Ԥ������ɽ��                   //
// Copyright (C) 2021-2021 Ryota.Waki ryota_waki@nitto-kohki.co.jp          //
// Changed history                                                          //
// 2021/11/17 Created   meeting_schedule_sougou_admit_list.php              //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
ini_set('max_execution_time', 60);          // ����¹Ի���=60�� WEB CGI��
//ob_start('ob_gzhandler');                   // ���ϥХåե���gzip����
//session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��
require_once ('../function.php');     // define.php �� pgsql.php �� require_once ���Ƥ���
require_once ('../MenuHeader.php');   // TNK ������ menu class
require_once ('../ControllerHTTP_Class.php');       // TNK ������ MVC Controller Class
require_once ('meeting_schedule_Model.php');        // MVC �� Model��
access_log();                               // Script Name �ϼ�ư����

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader(-1);                   // ǧ�ڥ����å� -1=ǧ�ڤʤ�

//////////// �ꥯ�����ȥ��֥������Ȥμ���
$request = new Request();

//////////// �ꥶ��ȤΥ��󥹥�������
$result = new Result();

//////////// ���å���� ���֥������Ȥμ���
$session = new Session();

$menu->set_title('����Ͼ�ǧ�Ԥ�����');     // �����ȥ������ʤ���IE�ΰ����ΥС�������ɽ���Ǥ��ʤ��Զ�礢��

///// �����Ȥ�ǯ���������ꤵ��Ƥ��뤫�����å�
if ($request->get('year') == '' || $request->get('month') == '' || $request->get('day') == '') {
    // �����(����)������
    $request->add('year', date('Y')); $request->add('month', date('m')); $request->add('day', date('d'));
}

///// ����ɽ�����δ���(1����,7����,14,28...)
if ($request->get('listSpan') == '') {
    if ($session->get_local('listSpan') != '') {
        $request->add('listSpan', $session->get_local('listSpan'));
    } else {
        $request->add('listSpan', '0');             // �����(�����Τ�)
    }
}
$session->add_local('listSpan', $request->get('listSpan')); // ���å����ǡ������ѹ�

//////////// �ӥ��ͥ���ǥ����Υ��󥹥�������
$model = new MeetingSchedule_Model($request);

////////// �������־���򥻥å�
$up_time = 10000;   // �������� 10��
//$up_time = 20000;   // �������� 20��
//$up_time = 30000;   // �������� 30��
//$up_time = 60000;   // ��������  1ʬ
$up_info = $up_time/1000;
if( $up_info < 60 ) {
    $up_info .= "���蹹��";
} else{
    $up_info = $up_info/60;
    $up_info .= "ʬ�蹹��";
}
$up_info .= "���ѹ���ǽ��";
$now = date("Y/m/d��H:i:s");
$up_title = "{$up_info}<BR>�� {$now} ��";

////////// ����� ��ǧ�Ԥ����� ��ǧ��UID����
$query = "
            SELECT DISTINCT admit_status
            FROM            sougou_deteils
            WHERE           admit_status!='END' AND admit_status!='DENY' AND admit_status!='CANCEL'
         ";
$admit_uid = array();
$admit_idx = getResult2($query, $admit_uid);

////////// ������桼����ID���򿦤򥻥å�
$login_uid = $_SESSION['User_ID'];  // ����դ�����ʸ����ɽ��������桼����
if( $login_uid == '300667' ) $debug = true; else $debug = false;
if($debug){
//$login_uid = '015989';// ��Ĺ
//$login_uid = '300144';// ��Ĺ
//$login_uid = '017507';// ��Ĺ
//$login_uid = '016080';// ��Ĺ
//$login_uid = '016713';// ��Ĺ
//$login_uid = '300055';// ��̳��Ĺ
//$login_uid = '017850';// ������Ĺ
//$login_uid = '011061';// ����Ĺ
$debug = false;
}
$login_post = getPost($login_uid);

////////// pid ����
function getPid($uid)
{
    $query = "SELECT pid FROM user_detailes WHERE uid = '$uid'";
    $res = array();
    if( getResult2($query, $res) <= 0 ) return '';
    return $res[0][0];
}

////////// act_id ����
function getActid($uid)
{
    $query = "SELECT act_id FROM cd_table WHERE uid = '$uid'";
    $res = array();
    if( getResult2($query, $res) <= 0 ) return '';
    return $res[0][0];
}

////////// �򿦤ϡ�
function getPost($uid)
{
    $pid    = getPid($uid);
    $act_id = getActid($uid);
    
    switch ($pid) {
        case 110:// ����Ĺ
            $post = 'ko';
            break;
        case 47:// ��Ĺ����
        case 70:// ��Ĺ
        case 95:// ������Ĺ
            if( $act_id == 610 ) {
                $post = 'kb';// ������Ĺ
            } else {
                $post = 'bu';
            }
            break;
        case 46:// ��Ĺ����
        case 50:// ��Ĺ
            if( $act_id == 650 || $act_id == 651 || $act_id == 660 ) {
                $post = 'sk';// ��̳��Ĺ
            } else {
                $post = 'ka';
            }
            break;
        case 31:// ��Ĺ��
        case 32:// ��Ĺ��
            $post = 'kk';
            break;
        default:// ����
            $post = '';
            break;
    }
    return $post;
}

////////// ����̾����
function getDeploy($uid)
{
    $act_id   = getActid($uid);
    if( $uid == '012394') $act_id = 582;
    
    switch ($act_id) {
        case 600:
            return "����Ĺ";
        case 610:   // ������
            return "������";
        case 605:   // �ɣӣϻ�̳��
        case 650:   // ������ ��̳��
        case 651:   // ������ ��̳�� ��̳
        case 660:   // ������ ��̳�� ��̳
            return "������ ��̳��";
        case 670:   // ������ ���ʴ�����
            return "������ ���ʴ�����";
        case 501:   // ������
            return "������";
        case 174:   // ������ �ʼ�������
        case 517:   // ������ �ʼ������� ���ץ鸡��ô��
        case 537:   // ������ �ʼ������� ���ץ鸡��ô��
        case 581:   // ������ �ʼ������� ���ץ鸡��ô��
            return "������ �ʼ�������";
        case 173:   // ������ ���Ѳ�
        case 515:   // ������ ���Ѳ�
        case 535:   // ������ ���Ѳ�
            return "������ ���Ѳ�";
        case 582:   // ��¤��
            return "��¤��";
        case 518:   // ��¤�� ��¤����
        case 519:   // ��¤�� ��¤����
        case 556:   // ��¤�� ��¤����
        case 520:   // ��¤�� ��¤����
            return "��¤�� ��¤����";
        case 547:   // ��¤�� ��¤����
        case 528:   // ��¤�� ��¤����
        case 527:   // ��¤�� ��¤����
            return "��¤�� ��¤����";
        case 500:   // ������
            return "������";
        case 545:   // ������ ����������
        case 512:   // ������ ���������� �ײ跸 ��ô��
        case 532:   // ������ ���������� �ײ跸 ��ô��
        case 513:   // ������ ���������� ���㷸 ��ô��
        case 533:   // ������ ���������� ���㷸 ��ô��
        case 514:   // ������ ���������� ��෸ ���ץ���
        case 534:   // ������ ���������� ��෸ ��˥����
            return "������ ����������";
        case 176:   // ������ ���ץ���Ω��
        case 522:   // ������ ���ץ���ΩMAô��
        case 523:   // ������ ���ץ���ΩHAô��
        case 525:   // ������ ���ץ�����ô��
            return "������ ���ץ���Ω��";
        case 551:   // ������ ��˥���Ω��
        case 175:   // ������ ��˥���Ωô��
        case 572:   // ������ �ԥ��ȥ���ô��
            return "������ ��˥���Ω��";
        default:
            return "";
    }
}

////////// ɽ�����롩
function IsView($l_post, $l_uid, $uid)
{
    switch ($l_post) {  // ������桼�������򿦤ϡ�
        case 'kk':  // ��Ĺ
            if( $uid != $l_uid ) return false;// ������桼���� �ʳ� ��ɽ��
//        case 'sk':  // ��̳��Ĺ
//        case 'kb':  // ������Ĺ
        case 'ko':  // ����Ĺ
            return true;    // ����ʬɽ��
        default:    // ����¾
            break;
    }
    
    if( $l_post == 'sk' || $l_post == 'kb' ) {
        $post = getPost($uid);  // ��ǧ�Ԥ��򿦤򥻥å�
        switch ($l_post) {  // ������桼�������򿦤ϡ�
            case 'sk':  // ��̳��Ĺ
                if( $post == 'kb' || $post == 'ko' ) return false;// ������Ĺ ����Ĺ ��ɽ��
            case 'kb':  // ������Ĺ
                if( $post == 'ko' ) return false;// ����Ĺ ��ɽ��
            default:    // ����¾
                break;
        }
        return true;    // ɽ��
    } else {
        if( ! strstr(getDeploy($uid), getDeploy($l_uid)) ) return false; // ���𤬰㤦�ʤ� ��ɽ��
    }
    
    $post = getPost($uid);  // ��ǧ�Ԥ��򿦤򥻥å�
    switch ($l_post) {  // ������桼�������򿦤ϡ�
        case 'ka':  // ��Ĺ
            if( $uid != $l_uid && $post != 'kk' ) return false;// ������桼�����ܷ�Ĺ �ʳ� ��ɽ��
            break;
        case 'bu':  // ��Ĺ
            if( $uid != $l_uid && $post != 'kk' && $post != 'ka' ) return false;// ������桼�����ܷ�Ĺ�ܲ�Ĺ �ʳ� ��ɽ��
            break;
        default:    // ���� ����¾
            return false;   // ɽ�����ʤ�
    }
    
    return true;
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
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
    <input type="submit" value="������TEST"       name="commit" onClick=''>��
    <input type="button" value="[��]�Ĥ���" name="close"  onClick='window.parent.close();'>
-->
        <?php
        echo $up_title; // ��������ɽ��
        ?>
        <BR>    <!-- ��ǧ�Ԥ����� ɽ�� -->
        <table class='pt10' border="1" cellspacing="0">
        <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
            <table width='100%' class='winbox_field' bgcolor='#D8D8D8' align='center' border='1' cellspacing='0' cellpadding='3'>
                <caption style='background-color:DarkCyan; color:White;'><div class='caption_font'>��ǧ�Ԥ�����</div></caption>
                <tr style='background-color:yellow; color:blue;'><!-- ����ɽ�� -->
                    <td nowrap align='center'>�� ǧ ��</td>
                    <td nowrap align='center'>���</td>
                    <td align='center'>�жо���</td>
<!--
                    <td align='center'>�̡���</td>
-->
                </tr>
                <?php
if($debug){
                echo "�������:{$model->getUidName($login_uid)}<BR>";
                $deploy = getDeploy($login_uid);
                echo "����̾:{$deploy}����:[{$login_post}]<BR>"; // post = ''/'kk'/'ka'/'bu'/'sk'/'kb'/'ko'
                echo "=============================<BR>";
}
                $view_on = false;// ����͡�ɽ�����Ƥʤ�
                for( $r=0; $r<$admit_idx; $r++ ) {  // ����� ��ǧ�Ԥ� ��ǧ��ʬ�롼��
                    $uid  = $admit_uid[$r][0];      // ��ǧ�Ԥ�UID�򥻥å�
                    $view = IsView( $login_post, $login_uid, $uid); // ɽ�����뤫���ʤ���Ƚ�Ǥ���
if($debug){
                    echo "{$model->getUidName($uid)}:";
                    if( ! $view ) {
                        echo "[ɽ�����ʤ�]<BR>";
                    } else {
                        echo "[ɽ������]<BR>";
                    }
}
                    if( ! $view ) continue;
                ?>
                <tr><!-- �ƾ����ɽ�� -->
                    <td nowrap align='center'>
                    <?php
                    // ������桼����̾�ϡ�����դ���ɽ���ˤ���
                    if( $login_uid == $uid ) {
//                        echo "<a href='http://masterst/per_appli/in_sougou_admit/sougou_admit_Main.php?calUid={$uid}' target='_blank' style='text-decoration:none;'><font style='color:red;'>{$model->getUidName($uid)}</font></a>";
                        echo "<a href='http://masterst/per_appli/in_sougou_admit/sougou_admit_Main.php?calUid={$uid}' target='_blank'><font style='color:red;'>{$model->getUidName($uid)}</font></a>";
                    } else {
                        echo $model->getUidName($uid);
                    }
                    ?>
                    </td>
                    <td align='center'><?php echo $model->getSougouAdmitCnt($uid); ?> ��</td>
                    <td align='center'><?php echo $model->getAbsence($uid); ?></td>
<!-- ��ǧ����褦���Τ�����
                    <td align='center'><input type='button' value='����' onClick=''></td>
-->
                </tr>
                <?php
                    $view_on = true;// ɽ������
                } // for() End.
                ?>
                
                <?php
                if( ! $view_on ) {// ɽ�����Ƥʤ�
                    echo "<tr><td align='center' colspan='4'><BR>���ߡ���ǧ�Ԥ���<BR>����ޤ���<BR>��</td></tr>";
                }
                ?>
            </table>
        </tr></td> <!----------- ���ߡ�(�ǥ�������) ------------>
        </table>
    </form>
</center>
</body>

<?php echo $menu->out_alert_java()?>
</html>
