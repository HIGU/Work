<?php
//////////////////////////////////////////////////////////////////////////////
// ������������ƥ��� index��˥塼                                         //
// Copyright(C) 2007      Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp       //
// Changed history                                                          //
// 2007/09/25 Created   punchMark/index.php                                 //
// 2007/09/26 site_index �� INDEX_INDUST ���ѹ�                             //
// 2007/10/20 �����ֹ��̹���ޥ����� ��̾�Τ� ���ʥޥ��������ѹ�            //
// 2007/11/14 ���ޥ������θ�����˥塼���ɲ�                                //
// 2007/11/15 �ޥ������ι������� �Ȳ���ɲ�                                 //
// 2007/11/19 ����߽���Ģ���ɲ�                                            //
// 2007/12/04 ����߽���Ģ�ι���������ɲ�                                  //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL | E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug ��
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
// ini_set('implicit_flush', 'off');           // echo print �� flush �����ʤ�(�٤��ʤ뤿��) CLI��
// ini_set('max_execution_time', 1200);        // ����¹Ի���=20ʬ HTTP CGI��
ob_start('ob_gzhandler');                   // ���ϥХåե���gzip����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��

require_once ('../../function.php');        // define.php �� pgsql.php �� require_once ���Ƥ���
require_once ('../../tnk_func.php');        // TNK �˰�¸������ʬ�δؿ��� require_once ���Ƥ���
require_once ('../../MenuHeader.php');      // TNK ������ menu class
access_log();                               // Script Name �ϼ�ư����
uriIndexCheck();                            // index.php�λ�������å�

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader(0);                  // ǧ�ڥ����å� -1=ǧ��̵�� 0=���̰ʾ� �����=TOP_MENU �����ȥ�̤����

////////////// ����������
$menu->set_site(INDEX_INDUST, 999);         // site_index=99(�ƥ��ȥ�˥塼) site_id=999(�����ȥ�˥塼�򳫤�)
////////////// �꥿���󥢥ɥ쥹����
// $menu->set_RetUrl(INDUST_MENU);             // �̾�ϻ��ꤹ��ɬ�פϤʤ�(���������˥塼)
//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title('������������ƥ� �ȥå� ��˥塼');
//////////// ɽ�������
$menu->set_caption('������������ƥ� ��˥塼');

//////////// �ƽ����action̾�ȥ��ɥ쥹����
    /************ left view *************/
$menu->set_action('lend_list',          INDUST .  'punchMark/lend_list/punchMark_lendList_Main.php');
$menu->set_action('lend_edit_history',  INDUST .  'punchMark/lend_edit_history/punchMark_lendEditHistory_Main.php');
// $menu->set_action('size_master',        INDUST .  'punchMark/master/punchMark_sizeMasterMnt_Main.php');
// $menu->set_action('shape_master',       INDUST .  'punchMark/master/punchMark_shapeMasterMnt_Main.php');
// $menu->set_action('punchMark_master',   INDUST .  'punchMark/master/punchMark_MasterMnt_Main.php');
// $menu->set_action('parts_master',       INDUST .  'punchMark/master/punchMark_partsMasterMnt_Main.php');
    /************ right view *************/
$menu->set_action('mark_search',        INDUST .  'punchMark/search/punchMark_search_Main.php');
$menu->set_action('edit_history',       INDUST .  'punchMark/edit_history/punchMark_editHistory_Main.php');
$menu->set_action('size_master',        INDUST .  'punchMark/master/punchMark_sizeMasterMnt_Main.php');
$menu->set_action('shape_master',       INDUST .  'punchMark/master/punchMark_shapeMasterMnt_Main.php');
$menu->set_action('punchMark_master',   INDUST .  'punchMark/master/punchMark_MasterMnt_Main.php');
$menu->set_action('parts_master',       INDUST .  'punchMark/master/punchMark_partsMasterMnt_Main.php');

//////////// �֥饦�����Υ���å����к���
$uniq = $menu->set_useNotCache('punchMark');

/////////// HTML Header ����Ϥ��ƥ���å��������
$menu->out_html_header();
?>
<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.01 Transitional//EN'>
<html>
<head>
<meta http-equiv='Content-Type' content='text/html; charset=EUC-JP'>
<meta http-equiv='Content-Style-Type' content='text/css'>
<meta http-equiv='Content-Script-Type' content='text/javascript'>
<title><?php echo $menu->out_title() ?></title>
<?php echo $menu->out_site_java() ?>
<?php echo $menu->out_css() ?>

<!-- �������륷���ȤΥե��������򥳥��� HTML���� �����Ȥ�����Ҥ˽���ʤ�������� 
<link rel='stylesheet' href='<?php echo 'punchMark.css?', $uniq ?>' type='text/css' media='screen'>
-->

<!-- ���ߤϥ�����
<script type='text/javascript' src='../punchMark.js'></script>
-->
<script type='text/javascript'>
<!--
function set_focus()
{
    // ��������̵��
}
// -->
</script>

<style type='text/css'>
<!--
-->
</style>

</head>
<body style='overflow:hidden;' onLoad='set_focus()'>
    <center>
<?php echo $menu->out_title_border() ?>
        <table width='100%'>
            <tr>
                <td align='center'><img src='<?php echo IMG ?>t_nitto_logo2.gif' width=348 height=83></td>
            </tr>
            <tr>
                <td align='center' class='caption_font'>
                    <?php echo $menu->out_caption() . "\n" ?>
                </td>
            </tr>
            <!-------------------------------------------------------------------------------------------
            <tr>
                <td align='center' class='caption_font'>
                    <?php echo $_SERVER['REQUEST_URI'], ' �� ', basename($_SERVER['REQUEST_URI']), "<br>\n" ?>
                    <?php echo $_SERVER['SCRIPT_NAME'], ' �� ', basename($_SERVER['SCRIPT_NAME']), "\n" ?>
                </td>
            </tr>
            -------------------------------------------------------------------------------------------->
        </table>
        
        <br>
        
        <table width='70%' border='0'> <!-- width�Ǵֳ֤�Ĵ�� -->
        <tr>
            <!-- /////////////// left view ////////////// -->
        <td align='center' valign='top'>
            <table border='0'>
                <tr>
                    <form method='post' action='<?php echo $menu->out_action('lend_list') ?>'>
                        <td align='center'>
                            <input type='image' border='0'
                                alt='������߽���Ģ��˥塼��¹Ԥ��ޤ���'
                                src='<?php echo menu_bar('menu_tmp/menu_item_punchMark_lendList.png', '���߽���Ģ��˥塼', 14, 0), "?{$uniq}" ?>'
                            >
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
                
                <tr>
                    <form method='post' action='<?php echo $menu->out_action('lend_edit_history') ?>'>
                        <td align='center'>
                            <input type='image' border='0'
                                alt='�߽���Ģ�ι��������Ȳ񤷤ޤ���'
                                src='<?php echo menu_bar('menu_tmp/menu_item_punchMark_lendEditHistory.png', '���߽���Ģ��������', 14, 0), "?{$uniq}" ?>'
                            >
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
                
                <tr>
                    <form method='post' action='<?php echo $menu->out_self() ?>'>
                        <td align='center'>
                            <input type='image' border='0'
                                alt='���ߡ����Υ�˥塼�����ƥ�Ǥ���'
                                src='<?php echo menu_bar('menu_tmp/menu_item_punchMark_empty.png', '', 14, 0), "?{$uniq}" ?>'
                            >
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
                
                <tr>
                    <form method='post' action='<?php echo $menu->out_self() ?>'>
                        <td align='center'>
                            <input type='image' border='0'
                                alt='���ߡ����Υ�˥塼�����ƥ�Ǥ���'
                                src='<?php echo menu_bar('menu_tmp/menu_item_punchMark_empty.png', '', 14, 0), "?{$uniq}" ?>'
                            >
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
                
                <tr>
                    <form method='post' action='<?php echo $menu->out_self() ?>'>
                        <td align='center'>
                            <input type='image' border='0'
                                alt='���ߡ����Υ�˥塼�����ƥ�Ǥ���'
                                src='<?php echo menu_bar('menu_tmp/menu_item_punchMark_empty.png', '', 14, 0), "?{$uniq}" ?>'
                            >
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
                
                <tr>
                    <form method='post' action='<?php echo $menu->out_self() ?>'>
                        <td align='center'>
                            <input type='image' border='0'
                                alt='���ߡ����Υ�˥塼�����ƥ�Ǥ���'
                                src='<?php echo menu_bar('menu_tmp/menu_item_punchMark_empty.png', '', 14, 0), "?{$uniq}" ?>'
                            >
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
                
                <tr>
                    <form method='post' action='<?php echo $menu->out_self() ?>'>
                        <td align='center'>
                            <input type='image' border='0'
                                alt='���ߡ����Υ�˥塼�����ƥ�Ǥ���'
                                src='<?php echo menu_bar('menu_tmp/menu_item_punchMark_empty.png', '', 14, 0), "?{$uniq}" ?>'
                            >
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
                
                <tr>
                    <form method='post' action='<?php echo $menu->out_self() ?>'>
                        <td align='center'>
                            <input type='image' border='0'
                                alt='���ߡ����Υ�˥塼�����ƥ�Ǥ���'
                                src='<?php echo menu_bar('menu_tmp/menu_item_punchMark_empty.png', '', 14, 0), "?{$uniq}" ?>'
                            >
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
                
                <!--------------------------
                <tr>
                    <form method='post' action='<?php echo $menu->out_self() ?>'>
                        <td align='center'>
                            <input type='image' name='testEmpty' border=0
                                alt='���Υ����ƥ�'
                                src='<?php echo IMG ?>menu_item.gif'
                            >
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
                --------------------------->
                
            </table>
        </td>
            <!-- /////////////// right view ////////////// -->
        <td align='center' valign='top'>
            <table border='0'>
                <tr>
                    <form method='post' action='<?php echo $menu->out_action('mark_search') ?>'>
                        <td align='center'>
                            <input type='image' border='0'
                                alt='���ƤΥޥ������θ�����Ԥ��ޤ���'
                                src='<?php echo menu_bar('menu_tmp/menu_item_punchMark_mark_search.png', '�����ޥ���������', 14, 0), "?{$uniq}" ?>'
                            >
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
                
                <tr>
                    <form method='post' action='<?php echo $menu->out_action('edit_history') ?>'>
                        <td align='center'>
                            <input type='image' border='0'
                                alt='�ޥ������ι�������ξȲ��Ԥ��ޤ���'
                                src='<?php echo menu_bar('menu_tmp/menu_item_punchMark_mark_editHistory.png', '���ޥ�������������', 14, 0), "?{$uniq}" ?>'
                            >
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
                
                <tr>
                    <form method='post' action='<?php echo $menu->out_action('size_master') ?>'>
                        <td align='center'>
                            <input type='image' border='0'
                                alt='�������ޥ����� ���Խ���Ԥ��ޤ���'
                                src='<?php echo menu_bar('menu_tmp/menu_item_punchMark_size_master.png', '���������ޥ�����', 14, 0), "?{$uniq}" ?>'
                            >
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
                
                <tr>
                    <form method='post' action='<?php echo $menu->out_action('shape_master') ?>'>
                        <td align='center'>
                            <input type='image' border='0'
                                alt='�����ޥ����� ���Խ���Ԥ��ޤ���'
                                src='<?php echo menu_bar('menu_tmp/menu_item_punchMark_shape_master.png', '�������ޥ�����', 14, 0), "?{$uniq}" ?>'
                            >
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
                
                <tr>
                    <form method='post' action='<?php echo $menu->out_action('punchMark_master') ?>'>
                        <td align='center'>
                            <input type='image' border='0'
                                alt='����ޥ����� ���Խ���Ԥ��ޤ���'
                                src='<?php echo menu_bar('menu_tmp/menu_item_punchMark_punchMark_master.png', '������ޥ�����', 14, 0), "?{$uniq}" ?>'
                            >
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
                
                <tr>
                    <form method='post' action='<?php echo $menu->out_action('parts_master') ?>'>
                        <td align='center'>
                            <input type='image' border='0'
                                alt='���ʥޥ����� ���Խ���Ԥ��ޤ���'
                                src='<?php echo menu_bar('menu_tmp/menu_item_punchMark_parts_master.png', '�����ʥޥ�����', 14, 0), "?{$uniq}" ?>'
                            >
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
                
                <tr>
                    <form method='post' action='<?php echo $menu->out_self() ?>'>
                        <td align='center'>
                            <input type='image' border='0'
                                alt='���ߡ����Υ�˥塼�����ƥ�Ǥ���'
                                src='<?php echo menu_bar('menu_tmp/menu_item_punchMark_empty.png', '', 14, 0), "?{$uniq}" ?>'
                            >
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
                
                <tr>
                    <form method='post' action='<?php echo $menu->out_self() ?>'>
                        <td align='center'>
                            <input type='image' border='0'
                                alt='���ߡ����Υ�˥塼�����ƥ�Ǥ���'
                                src='<?php echo menu_bar('menu_tmp/menu_item_punchMark_empty.png', '', 14, 0), "?{$uniq}" ?>'
                            >
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
                
                <!--------------------------
                <tr>
                    <form method='post' action='<?php echo $menu->out_self() ?>'>
                        <td align='center'>
                            <input type='image' name='testEmpty' border=0
                                alt='���Υ����ƥ�'
                                src='<?php echo IMG ?>menu_item.gif'
                            >
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
                --------------------------->
                
            </table>
        </td>
        </tr>
        </table>
    </center>
</body>
<?php echo $menu->out_alert_java()?>
</html>
<?php
ob_end_flush();                 // ���ϥХåե���gzip���� END
?>
