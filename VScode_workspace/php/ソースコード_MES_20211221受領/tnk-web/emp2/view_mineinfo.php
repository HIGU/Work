<?php
//////////////////////////////////////////////////////////////////////////////
// �Ұ���������� include file ���ʾ���ɽ��                                 //
// Copyright(C) 2001-2021 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp       //
// Changed history                                                          //
// 2001/07/07 Created  view_mineinfo.php                                    //
// 2002/08/07 ���å����������ɲ� & register_globals = Off �б�            //
// 2004/04/16 ɽ������ΰ������ѹ� ���֤��饹�ڡ����Ѵ�(������)             //
// 2005/01/17 access_log ���ѹ��� view_file_name(__FILE__) ���ɲ�           //
// 2005/01/26 background-image���ɲä��ƥǥ������ѹ�(AM/PM�����ؼ�)         //
// 2005/11/24 �ѥ���ɤ�Ź沽����Ƥ��뤿��ɽ�����ܤ�����(������)    //
//            ���ʤμ̿����礭���򥪥ꥸ�ʥ륵�������� 128 X 192 ���ѹ�     //
// 2007/08/30 �̿�����å����̥�����ɥ��˥�����ɽ��(�ʰ�)�ɲ�              //
// 2007/09/11 uniqid(abcdef) �� $uniq �� ('')���̤��Ƥ���                   //
// 2007/10/15 ���ʤη���(���顦��ʡ���ư��) ɽ��(����)��˥塼���ɲ�       //
// 2015/01/30 ͭ��Ŀ���ɽ�����ɲ�                                     ��ë //
// 2015/03/20 ͭ��Ĥ��̥ץ����Ƿ׻����������Ǥϼ����Τߤ��ѹ�     ��ë //
// 2015/03/27 ���ʾ���ɽ���Ϥ��ʤ��褦�ˤ���(�����Ȳ�)               ��ë //
// 2016/12/15 ���ʾ����ͭ��Ĥ�ɽ������褦���ѹ�                     ��ë //
// 2019/12/06 ���ʾ����ͭ��5�������ξ����ɽ������褦���ѹ�          ��ë //
// 2021/06/28 ���������ܰ¤ϴ�����������Τߤ�ɽ������褦�ѹ�              //
//            ������������ξ�硢�׻������������ʤ롣������������ξ��    //
//            �������绻�����١��ܰ¤Ϥ���ʤ���                          //
//            ��碌��22�����ǯ6���ǿ����׻�����褦�ѹ�             ��ë  //
//////////////////////////////////////////////////////////////////////////////
// access_log("view_mineinfo.php");        // Script Name ��ư����
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
            <font color='#ffffff'>�桼�����ξ���</font></td>
        </tr>
        <tr><td valign="top">
            <table width="100%">
                <tr><td width="30%">�Ұ�No.</td>
                    <td><?php echo($_SESSION["User_ID"]); ?></td>
                </tr>
                <tr><td width="30%">̾��</td>
                    <td><font size=1><?php echo(trim($res[0]["kana"])); ?></font><br><?php echo(trim($res[0]["name"])); ?></td>
                    <?php
                    //if ($_SESSION['User_ID'] == '300144') {
                        // ͭ��ķ׻�
                        $timeDate = date('Ym');
                        $today_ym = date('Ymd');
                        $tmp = $timeDate - 195603;     // ���׻�����195603
                        $tmp = $tmp / 100;             // ǯ����ʬ����Ф�
                        $ki  = ceil($tmp);             // roundup ��Ʊ��
                        $nk_ki = $ki + 44;
                        $yyyy = substr($timeDate, 0,4);
                        $mm   = substr($timeDate, 4,2);
                        // ǯ�ٷ׻�
                        if ($mm < 4) {              // 1��3��ξ��
                            $business_year = $yyyy - 1;
                        } else {
                            $business_year = $yyyy;
                        }
                        $query = "
                                SELECT
                                     current_day    AS ����ͭ������     -- 0
                                    ,holiday_rest   AS ����ͭ���       -- 1
                                    ,half_holiday   AS Ⱦ��ͭ����     -- 2
                                    ,time_holiday   AS ���ֵټ���ʬ     -- 3
                                    ,time_limit     AS ����ͭ�����     -- 4
                                    ,web_ymd        AS ����ǯ����       -- 5
                                FROM holiday_rest_master
                                WHERE uid='{$_SESSION['User_ID']}' and ki={$ki};
                            ";
                        getResult2($query, $yukyu);
                        $kintai_ym         = substr($yukyu[0][5], 0, 4) . "ǯ" . substr($yukyu[0][5], 4, 2) . "��" . substr($yukyu[0][5], 6, 2) . "��";
                        // ͭ��5������ɽ��
                        // ������Υǡ������ʤ����Ͻ���
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
                                SELECT   uid          AS �Ұ��ֹ� --00 
                                        ,working_date AS ������   --01
                                        ,working_day  AS ����     --02
                                        ,absence      AS �Ժ���ͳ --03
                                        ,str_mc       AS �жУͣ� --04
                                        ,end_mc       AS ��Уͣ� --05
                                FROM working_hours_report_data_new WHERE uid='{$_SESSION['User_ID']}' and working_date >= {$res_c[0][1]} and
                                working_date < {$end_rmd} and absence = '11';
                             ";
                            $f_yukyu=getResult2($query, $f_yukyu);
                            $query = "
                                SELECT   uid          AS �Ұ��ֹ� --00 
                                        ,working_date AS ������   --01
                                        ,working_day  AS ����     --02
                                        ,absence      AS �Ժ���ͳ --03
                                        ,str_mc       AS �жУͣ� --04
                                        ,end_mc       AS ��Уͣ� --05
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
                                SELECT   reference_ym          AS ��೫���� --00
                                        ,end_ref_ym            AS ��ཪλ�� --01
                                        ,need_day              AS ɬ������   --02
                                FROM five_yukyu_master WHERE uid='{$_SESSION['User_ID']}' and business_year={$business_year}
                            ";
                            $rows_ne=getResult($query,$res_ne);
                            $s_yy       = substr($res_ne[0][0], 0,4);                   // ��೫������ǯ
                            $s_mm       = substr($res_ne[0][0], 4,2);                   // ��೫��������
                            $s_dd       = substr($res_ne[0][0], 6,2);                   // ��೫��������
                            $s_ym       = substr($res_ne[0][0], 0,6);                   // ��೫������ǯ��
                            $s_md       = substr($res_ne[0][0], 4,4);                   // ��೫����������
                            $s_ref_date = $s_yy . "ǯ" . $s_mm . "��" . $s_dd . "��";   // ��೫������ǯ����
                            $e_yy       = substr($res_ne[0][1], 0,4);                   // ��ཪλ����ǯ
                            $e_mm       = substr($res_ne[0][1], 4,2);                   // ��ཪλ������
                            $e_dd       = substr($res_ne[0][1], 6,2);                   // ��ཪλ������
                            $e_ref_date = $e_yy . "ǯ" . $e_mm . "��" . $e_dd . "��";   // ��ཪλ����ǯ����
                            $need_day   = $res_ne[0][2];
                            $indication_flg = 0;            // �ե饰OFF
                            $ki_str_ym  = $business_year . '04';                        // ��������ǯ��
                            if($s_ym > $ki_str_ym) {        // �������������ξ���ܰ¤��פ�ʤ�
                                if($s_md != '0401') {           // �ܰ�ɽ��Ƚ�� ��������4/1�ǤϤʤ�����ܰ�ɽ��
                                    $indication_flg = 1;        // �ե饰ON
                                    $ind_mm      = 0;                               // �׻��Ѥη����ꥻ�å�
                                    $ki_end_yy       = $business_year + 1;
                                    $ki_end_ym       = $ki_end_yy . '03';    // ��������ǯ��
                                    $ki_end_ymd      = $ki_end_yy . '0331';  // ��������ǯ����
                                    if ($ki_first_ymd >= 20210401) {
                                        if ($s_mm < 3) {  // 1��3��
                                            $ind_mm  = $ki_end_ym - $s_ym + 1 + 12;     // �׻��ѷ��
                                            $ind_day = round($ind_mm / 12 * 6, 1);      // ����ࣱ���ߣ��������׻�
                                            $ind_day = ceil($ind_day * 2) / 2 - 6;      // 0.5ñ�̤��ڤ�夲 ���ʬ��6����ޥ��ʥ�
                                        } else {
                                            $ind_mm  = $ki_end_ym - $s_ym - 87 + 12;    // �׻��ѷ��
                                            $ind_day = round($ind_mm /12 * 6, 1);       // ����ࣱ���ߣ��������׻�
                                            $ind_day = ceil($ind_day * 2) / 2 - 6;      // 0.5ñ�̤��ڤ�夲 ���ʬ��6����ޥ��ʥ�
                                        }
                                    } else {
                                        if ($s_mm < 3) {  // 1��3��
                                            $ind_mm  = $ki_end_ym - $s_ym + 1 + 12;     // �׻��ѷ��
                                            $ind_day = round($ind_mm / 12 * 5, 1);      // ����ࣱ���ߣ��������׻�
                                            $ind_day = ceil($ind_day * 2) / 2 - 5;      // 0.5ñ�̤��ڤ�夲 ���ʬ��5����ޥ��ʥ�
                                        } else {
                                            $ind_mm  = $ki_end_ym - $s_ym - 87 + 12;    // �׻��ѷ��
                                            $ind_day = round($ind_mm /12 * 5, 1);       // ����ࣱ���ߣ��������׻�
                                            $ind_day = ceil($ind_day * 2) / 2 - 5;      // 0.5ñ�̤��ڤ�夲 ���ʬ��5����ޥ��ʥ�
                                        }
                                    }
                                }
                            }
                        }
                    ?>
                    <td style='font-size:0.90em;'>
                        <?php echo "��{$kintai_ym}����<BR>��ͭ��� <font color='red'>{$yukyu[0][1]}</font>/{$yukyu[0][0]}��<BR>��Ⱦ�� <font color='red'>{$yukyu[0][2]}</font>/20 �󡡻��ֵ� <font color='red'>{$yukyu[0][3]}</font>/{$yukyu[0][4]} ����\n"; ?>
                    </td>
                    
                    <?php
                    //}
                    ?>
                </tr>
                <tr>
                    <td width="30%">��°</td>
                    <td><?php echo(trim($res[0]["section_name"])); ?></td>
                    <?php
                    if ($indication_flg == 1) {
                    if ($today_ym >= 20210401) {
                    ?>
                        <td style='font-size:0.90em;'>
                            <?php echo "��ͭ��6���ʾ����<BR>��{$s_ref_date}<BR>������{$e_ref_date}<BR>��<font color='red'>{$five_num}</font>/{$need_day}����<font color='red'><B>�����������ܰ�{$ind_day}��</B></font>\n"; ?>
                        </td>
                    <?php
                    } else {
                    ?>
                        <td style='font-size:0.90em;'>
                            <?php echo "��ͭ��5���ʾ����<BR>��{$s_ref_date}<BR>������{$e_ref_date}<BR>��<font color='red'>{$five_num}</font>/{$need_day}����<font color='red'><B>�����������ܰ�{$ind_day}��</B></font>\n"; ?>
                        </td>
                    <?php
                    }
                    } else {
                    if ($today_ym >= 20210401) {
                    ?>
                        <td style='font-size:0.90em;'>
                            <?php echo "��ͭ��6���ʾ����<BR>��{$s_ref_date}<BR>������{$e_ref_date}<BR>��<font color='red'>{$five_num}</font>/{$need_day}��\n"; ?>
                        </td>
                    <?php
                    } else {
                    ?>
                        <td style='font-size:0.90em;'>
                            <?php echo "��ͭ��5���ʾ����<BR>��{$s_ref_date}<BR>������{$e_ref_date}<BR>��<font color='red'>{$five_num}</font>/{$need_day}��\n"; ?>
                        </td>
                    <?php
                    }
                    }
                    ?>
                </tr>
                <tr>
                    <td width='30%' title='���顦��ʡ���ư��������ɽ����Ԥ��ޤ���'>����</td>
                    <td>
                        <input type='button' name='historyDisp' value='ɽ��' title='���顦��ʡ���ư��������ɽ����Ԥ��ޤ���'
                            onClick='win_open("print/print_emp_branch_user.php?targetUser=<?php echo $_SESSION['User_ID']?>", 800, 700);'
                        >
                        <!--
                        <a href='javascript:void(0)'
                            onClick='win_open("print/print_emp_history_user.php?targetUser=<?php echo $_SESSION['User_ID']?>", 600, 700);'
                        >ɽ��</a>
                        -->
                    </td>
                </tr>
                <!--
                <tr>
                    <td width="30%">�ѥ����</td>
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
            <font color="#ffffff">�ѥ���ɤ��ѹ�</font></td>
        </tr>
        <tr><td>�������ѥ����</td>
            <td align="right"><input type="password" name="passwd" size=12 maxlength=8></td>
        </tr>
        <tr><td>��ǧ�ѥ����</td>
            <td align="right"><input type="password" name="repasswd" size=12 maxlength=8></td>
            <td align="right"><input type="hidden" name="acount" value="<?php echo $request->get('acount'); ?>"></td>
        </tr>
        <tr><td colspan=2 align="right"><input type="submit" value="�ѹ�"></td>
        </tr>
    </table>
    </form>
<?php   
    }else{
?>
    <table width="100%">
        <tr><td colspan=2 bgcolor="#003e7c" align="center">
            <font color="#ffffff">�桼�����ξ���</font></td>
        </tr>
    </table>
<script language="javascript">
    alert("�����ξ��󤬥ǡ����١�����¸�ߤ��ޤ��󡣴����Ԥˤ��䤤��碌����������");
</script>
<?php   
    }
?>