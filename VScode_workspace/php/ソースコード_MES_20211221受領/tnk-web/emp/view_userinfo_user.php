<?php
//////////////////////////////////////////////////////////////////////////////
// �Ұ���������� include file ���Ȱ����󸡺����                           //
// Copyright (C) 2001-2021 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2001/07/07 Created   view_userinfo_user.php(include file)                //
// 2002/08/07 register_globals = Off �б�                                   //
//            $key �� ���Υ�����ץȤθƽи��ˤ��뎡view_userinfo.php        //
// 2002/08/29 ����ǯ�Ǹ����Ľ�� to_char �ȹ��ؿ���Var7.2.1��error��        //
//              �ʤ뤿�� like ʸ���ѹ�                                      //
// 2003/04/02 �и��������Ƥ��ɲ� (��°�θ������)                           //
// 2003/04/21 AUTH_LEVEL1(���)�ʾ�Υ桼�������Ф��Ƹ���ǯ����ɲ�         //
// 2003/11/11 getObject()�򥳥��ȥ����ȸ���pg_lo_export���б����Ƥʤ�     //
// 2003/12/22 and ud.uid!='000000' ���ɲ�(�ƥ����Ѥ�000000���ɲä�����)     //
// 2004/10/19 �¤ӽ������/�Ұ��ֹ梪����/����/�Ұ��ֹ���ѹ�(pm.pid DESC)  //
// 2005/01/17 access_log ���ѹ��� view_file_name(__FILE__) ���ɲ�           //
// 2005/01/26 background-image���ɲä��ƥǥ������ѹ�(AM/PM�����ؼ�)         //
// 2007/08/29 �����ν���Ф��ɲá�������ѹ��˥ڡ��������offset���ɲ�      //
// 2007/08/30 �̿�����å����̥�����ɥ��˥�����ɽ��(�ʰ�)�ɲ�              //
// 2007/09/11 uniqid(abcdef) �� $uniq �� ('')���̤��Ƥ���                   //
// 2007/09/11 VIWE_LIMIT �� VIEW_LIMIT 44���� �ؽ���                        //
// 2007/10/15 �Ŀ���ζ��顦��ʡ���ư��������ɽ��(����)�ܥ�����ɲ�        //
// 2010/03/11 ����Ū����޼�����970268�ˤ���Ͽ�Ǥ���褦���ѹ�         ��ë //
// 2014/07/29 �Ժ߼ԾȲ񤬤Ǥ���褦��˥塼���ɲ�(��ë������Ĺ����)   ��ë //
//         �����ޥ�¦��������и��ԤΥǡ�������Ƥ��ʤ�(�׳�ǧ2014/07/29)   //
// 2015/01/30 ͭ��Ŀ���ɽ�����ɲ�                                     ��ë //
//            ���桼�����Ǥ�ѥ���ɡ����Ƥ��ѹ���Ǥ��ʤ��褦���ѹ�    //
// 2015/02/12 1/30���ѹ��򸵤��ᤷ���ʥѥ�����ѹ���ɽ�����ʤ��ޤޡ� ��ë //
// 2015/03/12 time_pro�Υǡ������������ޤǤ�ͭ��׻����ɲ�                  //
//            ɽ�����Ť��ʤ�١���������Ȳ�                         ��ë //
// 2015/03/20 ͭ��Ĥ��̥ץ����Ƿ׻����������Ǥϼ����Τߤ��ѹ�     ��ë //
// 2015/03/27 ͭ�����������ɽ�������                                 ��ë //
// 2015/04/08 �оݴ��Υǡ������ʤ���������Υǡ�����ɽ������褦���ѹ�      //
//            �����ڤ��ؤ�����ͭ�빹�������Ԥ����б������           ��ë //
// 2015/05/27 ͭ��Υǡ������ʤ����ˡ�ͭ�븡����ȴ���Ф���Ƥ��ޤ��Τ�    //
//            ����                                                     ��ë //
// 2015/06/30 �Ժ߼Ԥθ�������޽�����䡢����Ĺ�����               ��ë //
// 2015/07/30 ������ˡ��ǯ���(�⤤��)���̤˼Ұ����ơ��ѡ������Ƥ��ɲ� ��ë //
// 2015/08/03 �ǥե���Ȥ�ɽ��������祳����ɽ�硢�򿦤ι⤤����ѹ�   ��ë //
// 2015/11/17 ������ѹ��ʤɤ�����ä��ݤˡ��������ä����Զ�������   ��ë //
// 2016/08/05 ����Ū����z�������300055�ˤ���Ͽ�Ǥ���褦���ѹ�              //
//            ���������桼������٥���б��Τ�����                   ��ë //
// 2019/01/31 ����Ū��ʿ�Ф����300551�ˤ���Ͽ�Ǥ���褦���ѹ�         ��ë //
// 2019/03/13 �Ժ߼�KIND_ABSENCE�ξ�､��������Ĺ�����                    //
//            ����Х��ȡ�����¾(����)�����칩������                 ��ë //
// 2019/07/25 ͭ��5�������ɽ���ɲ�                                    ��ë //
// 2019/09/17 �����ܰ�������ɽ�����ɲ�                                 ��ë //
// 2019/11/27 limit��500��(����Ĺ�ؼ� �ʤ��ˤ���������ս꤬������Τ�)��ë //
// 2019/12/06 ����ǯ��ȷ���ɽ����ɽ�����¤򹩾�Ĺ�ؼ����ѹ�           ��ë //
// 2019/12/17 �и��������٤ƤǤ���¾90�����칩��95��                        //
//            ɽ�����ʤ��褦���ѹ�                                     ��ë //
// 2020/03/10 ���̤˲�Ĺ�����ʾ���ɲ�                                 ��ë //
// 2020/05/22 ����������¤�ݤ��ޤޤ�Ƥ����Τǵ�������ʬΥ             ��ë //
// 2020/07/03 ���������ܰ¤ϴ�����������Τߤ�ɽ������褦�ѹ�              //
//            ������������ξ�硢�׻������������ʤ롣������������ξ��    //
//            �������绻�����١��ܰ¤Ϥ���ʤ���                     ��ë //
// 2021/03/30 ������κǽ�����׻��ǽФ��Ƥ����ΤǼ������ѹ�           ��ë //
// 2021/06/28 22�����ǯ6���ǿ����׻�����褦���ѹ�                    ��ë //
//////////////////////////////////////////////////////////////////////////////
// access_log("view_userinfo_user.php");       // Script Name ��ư����
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
        <font color='#ffffff'>�桼�����θ������</font>
        </td>
    </tr>
<?php
/* �����Ȥؤ�ɽ����� */
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
    /* �����꡼������ */
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
    /* ��°�ˤ���� */
    if ($_SESSION['lookupsection'] == (-2)) {
        $query .= " and ud.sid<>31 and ud.sid<>90 and ud.sid<>95";        // �и����������
    } elseif ($_SESSION["lookupsection"]!=KIND_DISABLE) {
        $query .= " and ud.sid=" . $_SESSION["lookupsection"];
    }
    /* ���̤ˤ���� */
    if($_SESSION["lookupposition"]==KIND_DISABLE) {
    } elseif($_SESSION["lookupposition"]==KIND_EMPLOYEE) {
        // 10:���� 31:��Ĺ�� 32:��Ĺ�� 33:�������ѡ��ȣ��� 34:�������ѡ��ȣ��� 35:�������ѡ��ȣ��� 46:��Ĺ���� 47:��Ĺ���� 50:��Ĺ 60:����Ĺ 70:��Ĺ 95:������Ĺ 110:�����򹩾�Ĺ
        $query .= " and (ud.pid=10 or ud.pid=31 or ud.pid=32 or ud.pid=33 or ud.pid=34 or ud.pid=35 or ud.pid=46 or ud.pid=47 or ud.pid=50 or ud.pid=60 or ud.pid=70 or ud.pid=95 or ud.pid=110)";
    } elseif($_SESSION["lookupposition"]==KIND_PARTTIME) {
        $query .= " and (ud.pid=5 or ud.pid=6)";    // 5:�ѡ��� 6:�ѡ��ȥ����å�
    } elseif($_SESSION["lookupposition"]==KIND_MANAGE) {
        // 46:��Ĺ���� 47:��Ĺ���� 50:��Ĺ 60:����Ĺ 70:��Ĺ 95:������Ĺ 110:�����򹩾�Ĺ
        $query .= " and (ud.pid=46 or ud.pid=47 or ud.pid=50 or ud.pid=60 or ud.pid=70 or ud.pid=95 or ud.pid=110)";
    } else {
        $query .= " and ud.pid=" . $_SESSION["lookupposition"];
    }
    /* ����ǯ�٤Ǥξ�� */
    if($_SESSION["lookupentry"]!=KIND_DISABLE)
        // $query .= " and to_char(ud.enterdate,'YYYY')='" . $_SESSION["lookupentry"] . "'";
        $query .= " and ud.enterdate like '" . $_SESSION["lookupentry"] . "%'";     // Var7.2.1��UP������to_char���Ȥ��ʤ��ʤä�����
    /* ��ʤˤ���� */
    if($_SESSION["lookupcapacity"]!=KIND_DISABLE)
        $query .=" and exists (select * from user_capacity uc where ud.uid=uc.uid and uc.cid=" . $_SESSION["lookupcapacity"] . ")";
    /* ����ˤ���� */
    if($_SESSION["lookupreceive"]!=KIND_DISABLE)
        $query .=" and exists (select * from user_receive ur where ud.uid=ur.uid and ur.rid=" . $_SESSION["lookupreceive"] . ")";
    if($_SESSION["lookupkeykind"]!=KIND_AGE) {
        //$query .=" order by sm.section_name ASC, pm.pid DESC, ud.uid ASC";
        // �ʲ��ǽ�°����¤��ؤ� ���祳����ɽ�ξ夫�� ��������Ҷ��̡��������칩��ˤμ�������
        // �����ܤ����̤ι⤤�硢�����������Ĺ�β��ˡ����θ�ϼҰ�No.��
        $query .=" order by CASE sm.sid WHEN 99 THEN 1 ELSE 2 END, CASE sm.sid WHEN 80 THEN 1 ELSE 2 END, CASE sm.sid WHEN 9 THEN 1 ELSE 2 END, CASE sm.sid WHEN 31 THEN 1 ELSE 2 END, CASE sm.sid WHEN 5 THEN 1 ELSE 2 END, CASE sm.sid WHEN 19 THEN 1 ELSE 2 END, CASE sm.sid WHEN 38 THEN 1 ELSE 2 END, CASE sm.sid WHEN 18 THEN 1 ELSE 2 END, CASE sm.sid WHEN 4 THEN 1 ELSE 2 END, CASE sm.sid WHEN 8 THEN 1 ELSE 2 END, CASE sm.sid WHEN 34 THEN 1 ELSE 2 END, CASE sm.sid WHEN 35 THEN 1 ELSE 2 END, CASE sm.sid WHEN 32 THEN 1 ELSE 2 END, CASE sm.sid WHEN 2 THEN 1 ELSE 2 END, CASE sm.sid WHEN 3 THEN 1 ELSE 2 END, sm.sid,";
        $query .=" CASE pm.pid WHEN 120 THEN 1 ELSE 2 END, CASE pm.pid WHEN 110 THEN 1 ELSE 2 END, CASE pm.pid WHEN 95 THEN 1 ELSE 2 END, CASE pm.pid WHEN 130 THEN 1 ELSE 2 END, CASE pm.pid WHEN 70 THEN 1 ELSE 2 END, CASE pm.pid WHEN 47 THEN 1 ELSE 2 END, CASE pm.pid WHEN 60 THEN 1 ELSE 2 END, CASE pm.pid WHEN 50 THEN 1 ELSE 2 END, CASE pm.pid WHEN 46 THEN 1 ELSE 2 END, CASE pm.pid WHEN 35 THEN 1 ELSE 2 END, CASE pm.pid WHEN 34 THEN 1 ELSE 2 END, CASE pm.pid WHEN 33 THEN 1 ELSE 2 END, CASE pm.pid WHEN 32 THEN 1 ELSE 2 END, CASE pm.pid WHEN 31 THEN 1 ELSE 2 END, CASE pm.pid WHEN 10 THEN 1 ELSE 2 END, CASE pm.pid WHEN 9 THEN 1 ELSE 2 END, CASE pm.pid WHEN 8 THEN 1 ELSE 2 END, CASE pm.pid WHEN 6 THEN 1 ELSE 2 END, CASE pm.pid WHEN 5 THEN 1 ELSE 2 END, pm.pid, ud.uid ASC";
    } else {
        $query .=" order by ud.birthday ASC, sm.section_name ASC, pm.pid DESC, ud.uid ASC";
    }
    $res=array();
    $rows=getResult($query,$res);
    
    if($_SESSION['lookupyukyufive'] != KIND_DISABLE) {
    // ͭ�븡�����������ʸ�������Υ�������ѡ�
    $timeDate  = date('Ym');
    $today_ym  = date('Ymd');
    $tmp       = $timeDate - 195603;     // ���׻�����195603
    $tmp       = $tmp / 100;             // ǯ����ʬ����Ф�
    $ki        = ceil($tmp);             // roundup ��Ʊ��
    $nk_ki = $ki + 44;
    $yyyy = substr($timeDate, 0,4);
    $mm   = substr($timeDate, 4,2);
    // ǯ�ٷ׻�
    if ($mm < 4) {              // 1��3��ξ��
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
                // ������Υǡ������ʤ����Ͻ���
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
                        SELECT   uid          AS �Ұ��ֹ� --00 
                                ,working_date AS ������   --01
                                ,working_day  AS ����     --02
                                ,absence      AS �Ժ���ͳ --03
                                ,str_mc       AS �жУͣ� --04
                                ,end_mc       AS ��Уͣ� --05
                        FROM working_hours_report_data_new WHERE uid='{$res[$r]['uid']}' and working_date >= {$res_c[0][1]} and
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
                        SELECT   reference_ym          AS ��೫���� --00
                                ,end_ref_ym            AS ��ཪλ�� --01
                                ,need_day              AS ɬ������   --02
                        FROM five_yukyu_master WHERE uid='{$res[$r]['uid']}' and business_year={$business_year}
                    ";
                    $rows_ne=getResult($query,$res_ne);
                    $s_yy       = substr($res_ne[0][0], 0,4);                   // ��೫������ǯ
                    $s_mm       = substr($res_ne[0][1], 4,2);                   // ��೫��������
                    $s_dd       = substr($res_ne[0][2], 6,2);                   // ��೫��������
                    $s_ref_date = $s_yy . "ǯ" . $s_mm . "��" . $s_dd . "��";   // ��೫������ǯ����
                    $e_yy       = substr($res_ne[0][0], 0,4);                   // ��ཪλ����ǯ
                    $e_mm       = substr($res_ne[0][1], 4,2);                   // ��ཪλ������
                    $e_dd       = substr($res_ne[0][2], 6,2);                   // ��ཪλ������
                    $e_ref_date = $e_yy . "ǯ" . $e_mm . "��" . $e_dd . "��";   // ��ཪλ����ǯ����
                    $need_day   = $res_ne[0][2];
                }
            }
        }
    }
    } else {
    // ͭ�븡�����������ʸ�������Υ�������ѡ�
    $timeDate  = date('Ym');
    $today_ym  = date('Ymd');
    $tmp       = $timeDate - 195603;     // ���׻�����195603
    $tmp       = $tmp / 100;             // ǯ����ʬ����Ф�
    $ki        = ceil($tmp);             // roundup ��Ʊ��
    // �оݴ��Υǡ������ʤ�����������Υǡ�����Ȳ�
    $query_chk = "
              SELECT
                     current_day    AS ����ͭ������     -- 0
                    ,holiday_rest   AS ����ͭ���       -- 1
                    ,half_holiday   AS Ⱦ��ͭ����     -- 2
                    ,time_holiday   AS ���ֵټ���ʬ     -- 3
                    ,time_limit     AS ����ͭ�����     -- 4
                    ,web_ymd        AS ����ǯ����       -- 5
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
                // ͭ��Υǡ������ʤ����Ͻ���
                $query = "
                            SELECT
                                 current_day    AS ����ͭ������     -- 0
                                ,holiday_rest   AS ����ͭ���       -- 1
                                ,half_holiday   AS Ⱦ��ͭ����     -- 2
                                ,time_holiday   AS ���ֵټ���ʬ     -- 3
                                ,time_limit     AS ����ͭ�����     -- 4
                                ,web_ymd        AS ����ǯ����       -- 5
                            FROM holiday_rest_master
                            WHERE uid='{$res[$r]['uid']}' and ki={$ki};
                        ";
                $rows_c=getResult($query,$res_c);
                if ($rows_c > 0) {
                    $query = "
                                SELECT
                                     current_day    AS ����ͭ������     -- 0
                                    ,holiday_rest   AS ����ͭ���       -- 1
                                    ,half_holiday   AS Ⱦ��ͭ����     -- 2
                                    ,time_holiday   AS ���ֵټ���ʬ     -- 3
                                    ,time_limit     AS ����ͭ�����     -- 4
                                    ,web_ymd        AS ����ǯ����       -- 5
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
    echo("<tr><td colspan=2>���Ȱ�����  ������� <font size=+1 color='#ff7e00'><b>$rows_y</b></font> ��</td></tr>");
        echo("<tr>\n");
        echo("<td><form method='post' action='emp_menu.php?func=" . FUNC_LOOKUP . "'><table width='100%'>\n");
        echo("<input type='hidden' name='func' value='" . FUNC_LOOKUP . "'>\n");
        echo("<input type='hidden' name='resrows' value=$rows>\n");
        echo("<input type='hidden' name='retireflg' value=0>\n");
        echo("<input type='hidden' name='offset' value=$offset></td><tr>\n");
        if(0<=$offset-VIEW_LIMIT)
            echo("<td align='left'><input disable type='submit' name='lookup_prev' value='����'></td>\n");
        if($rows>$offset+VIEW_LIMIT){
            if(0==$offset)
                echo("<td colspan=2 align='right'><input type='submit' name='lookup_next' value='����'></td>\n");
            else
                echo("<td align='right'><input type='submit' name='lookup_next' value='����'></td>\n");
        }
        echo("</tr></table></form>\n");
        echo("</tr>\n");

    if ($rows_y) {
        for ($r=$offset; $r<$rows_y && $r<$offset+VIEW_LIMIT; $r++) {
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
            // �оݴ��Υǡ������ʤ�����������Υǡ�����Ȳ�
            $query_chk = "
                          SELECT
                                 current_day    AS ����ͭ������     -- 0
                                ,holiday_rest   AS ����ͭ���       -- 1
                                ,half_holiday   AS Ⱦ��ͭ����     -- 2
                                ,time_holiday   AS ���ֵټ���ʬ     -- 3
                                ,time_limit     AS ����ͭ�����     -- 4
                                ,web_ymd        AS ����ǯ����       -- 5
                          FROM holiday_rest_master
                          WHERE ki={$ki};
                          ";
            $rows_chk=getResult($query_chk,$res_chk);
            if ($rows_chk <= 0) {
                $ki = $ki - 1;
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
                    WHERE uid='{$res_y[$r]['uid']}' and ki={$ki};
                ";
            getResult2($query, $yukyu);
            $kintai_ym         = substr($yukyu[0][5], 0, 4) . "ǯ" . substr($yukyu[0][5], 4, 2) . "��" . substr($yukyu[0][5], 6, 2) . "��";
            //if ($yukyu[0][0]-$yukyu[0][1] > 5) {
            //    continue;
            //}
            $query_chk = sprintf("SELECT uid,reference_ym,end_ref_ym FROM five_yukyu_master WHERE uid='%s' and business_year=%d", $res_y[$r]['uid'], $business_year);
            $five_num = 0;
            $indication_flg = 0;                                        // �ܰ�ɽ���ե饰
            if (getResult($query_chk,$res_chk) <= 0) {    // �ȥ�󥶥��������Ǥ� �Ȳ����ѥ����꡼ 
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
                    SELECT   uid          AS �Ұ��ֹ� --00 
                            ,working_date AS ������   --01
                            ,working_day  AS ����     --02
                            ,absence      AS �Ժ���ͳ --03
                            ,str_mc       AS �жУͣ� --04
                            ,end_mc       AS ��Уͣ� --05
                    FROM working_hours_report_data_new WHERE uid='{$res_y[$r]['uid']}' and working_date >= {$res_chk[0][1]} and
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
                    FROM working_hours_report_data_new WHERE uid='{$res_y[$r]['uid']}' and working_date >= {$res_chk[0][1]} and
                    working_date < {$end_rmd} and ( str_mc = '41' or end_mc = '42' );
                ";
                $h_yukyu=getResult2($query, $h_yukyu) * 0.5;
                $five_num = $f_yukyu + $h_yukyu;
                $query = "
                    SELECT   reference_ym          AS ��೫���� --00
                            ,end_ref_ym            AS ��ཪλ�� --01
                            ,need_day              AS ɬ������   --02
                    FROM five_yukyu_master WHERE uid='{$res_y[$r]['uid']}' and business_year={$business_year}
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
    <tr><td valign='top'>
        <form method='post' action='emp_menu.php?func=<?php echo(FUNC_CHGUSERINFO); ?>'>
            <input type='hidden' name='func' value='<?php echo(FUNC_CHGUSERINFO); ?>'>
        <table width='100%' border='0'>
            <hr>
            <font color='#ff7e00'><b><?php echo($r+1); ?></b></font>
            <tr>
                <td width='15%'>�Ұ�No.</td>
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
                // ͭ�����ɽ�� ����Ĺ�ξ�缫ʬ�������ݰ��ϸ��뤳�Ȥ��Ǥ��롣 
                // ��Ĺ������Ĺ��������Ĺ����̳��Ĺ���ͻ�ô���ԡ������ƥ�����Ԥ�����ʬ������ǽ
                // ���̸��°���
                // ���¡�40 ������ǽ���𡧤��٤�                 ������ǽ�ԡ���Ĺ������Ĺ��������Ĺ��������Ĺ����̳��Ĺ���ͻ�ô���ԡ������ƥ������
                // ���¡�41 ������ǽ���𡧴�����(5,9,19,31)      ������ǽ�ԡ�������Ĺ����������Ĺ   �ʲ��ܾ嵭����40��Ͽ��
                // ���¡�42 ������ǽ���𡧵�����(4,18,38)        ������ǽ�ԡ�������Ĺ����������Ĺ
                // ���¡�43 ������ǽ����������(2,3,8,32)       ������ǽ�ԡ�������Ĺ����������Ĺ
                // ���¡�44 ������ǽ������̳��(5)              ������ǽ�ԡ���̳��Ĺ
                // ���¡�45 ������ǽ���𡧾��ʴ�����(19)         ������ǽ�ԡ����ʴ�����Ĺ
                // ���¡�46 ������ǽ�����ʼ��ݾڲ�(18)         ������ǽ�ԡ��ʼ��ݾڲ�Ĺ
                // ���¡�47 ������ǽ���𡧵��Ѳ�(4)              ������ǽ�ԡ����Ѳ�Ĺ
                // ���¡�48 ������ǽ������¤����(34)           ������ǽ�ԡ���¤����Ĺ
                // ���¡�49 ������ǽ������¤����(35)           ������ǽ�ԡ���¤����Ĺ
                // ���¡�50 ������ǽ��������������(32)         ������ǽ�ԡ�����������Ĺ
                // ���¡�51 ������ǽ���𡧥��ץ���Ω��(2)        ������ǽ�ԡ����ץ���Ω��Ĺ
                // ���¡�52 ������ǽ���𡧥�˥���Ω��(3)        ������ǽ�ԡ���˥���Ω��Ĺ
                // ���¡�55 ������ǽ������¤��(17,34,35)       ������ǽ�ԡ���¤��Ĺ����¤����Ĺ
                // �����Ԥθ��³�ǧ
                if (getCheckAuthority(40)) {
                ?>
                    <td style='font-size:0.90em;'>
                        <?php echo "��{$kintai_ym}����<BR>��ͭ��� <font color='red'>{$yukyu[0][1]}</font>/{$yukyu[0][0]}��<BR>��Ⱦ�� <font color='red'>{$yukyu[0][2]}</font>/20 �󡡻��ֵ� <font color='red'>{$yukyu[0][3]}</font>/{$yukyu[0][4]} ����\n"; ?>
                    </td>
                <?php
                } elseif (getCheckAuthority(41)) {
                    if ($res_y[$r]['sid'] == 5 || $res_y[$r]['sid'] == 9 || $res_y[$r]['sid'] == 19 || $res_y[$r]['sid'] == 31) {
                    ?>
                        <td style='font-size:0.90em;'>
                            <?php echo "��{$kintai_ym}����<BR>��ͭ��� <font color='red'>{$yukyu[0][1]}</font>/{$yukyu[0][0]}��<BR>��Ⱦ�� <font color='red'>{$yukyu[0][2]}</font>/20 �󡡻��ֵ� <font color='red'>{$yukyu[0][3]}</font>/{$yukyu[0][4]} ����\n"; ?>
                        </td>
                    <?php
                    }
                } elseif (getCheckAuthority(42)) {
                    if ($res_y[$r]['sid'] == 4 || $res_y[$r]['sid'] == 18 || $res_y[$r]['sid'] == 38) {
                    ?>
                        <td style='font-size:0.90em;'>
                            <?php echo "��{$kintai_ym}����<BR>��ͭ��� <font color='red'>{$yukyu[0][1]}</font>/{$yukyu[0][0]}��<BR>��Ⱦ�� <font color='red'>{$yukyu[0][2]}</font>/20 �󡡻��ֵ� <font color='red'>{$yukyu[0][3]}</font>/{$yukyu[0][4]} ����\n"; ?>
                        </td>
                    <?php
                    }
                } elseif (getCheckAuthority(43)) {
                    if ($res_y[$r]['sid'] == 2 || $res_y[$r]['sid'] == 3 || $res_y[$r]['sid'] == 8 || $res_y[$r]['sid'] == 32) {
                    ?>
                        <td style='font-size:0.90em;'>
                            <?php echo "��{$kintai_ym}����<BR>��ͭ��� <font color='red'>{$yukyu[0][1]}</font>/{$yukyu[0][0]}��<BR>��Ⱦ�� <font color='red'>{$yukyu[0][2]}</font>/20 �󡡻��ֵ� <font color='red'>{$yukyu[0][3]}</font>/{$yukyu[0][4]} ����\n"; ?>
                        </td>
                    <?php
                    }
                } elseif (getCheckAuthority(44)) {
                    if ($res_y[$r]['sid'] == 5) {
                    ?>
                        <td style='font-size:0.90em;'>
                            <?php echo "��{$kintai_ym}����<BR>��ͭ��� <font color='red'>{$yukyu[0][1]}</font>/{$yukyu[0][0]}��<BR>��Ⱦ�� <font color='red'>{$yukyu[0][2]}</font>/20 �󡡻��ֵ� <font color='red'>{$yukyu[0][3]}</font>/{$yukyu[0][4]} ����\n"; ?>
                        </td>
                    <?php
                    }
                } elseif (getCheckAuthority(45)) {
                    if ($res_y[$r]['sid'] == 19) {
                    ?>
                        <td style='font-size:0.90em;'>
                            <?php echo "��{$kintai_ym}����<BR>��ͭ��� <font color='red'>{$yukyu[0][1]}</font>/{$yukyu[0][0]}��<BR>��Ⱦ�� <font color='red'>{$yukyu[0][2]}</font>/20 �󡡻��ֵ� <font color='red'>{$yukyu[0][3]}</font>/{$yukyu[0][4]} ����\n"; ?>
                        </td>
                    <?php
                    }
                } elseif (getCheckAuthority(46)) {
                    if ($res_y[$r]['sid'] == 18) {
                    ?>
                        <td style='font-size:0.90em;'>
                            <?php echo "��{$kintai_ym}����<BR>��ͭ��� <font color='red'>{$yukyu[0][1]}</font>/{$yukyu[0][0]}��<BR>��Ⱦ�� <font color='red'>{$yukyu[0][2]}</font>/20 �󡡻��ֵ� <font color='red'>{$yukyu[0][3]}</font>/{$yukyu[0][4]} ����\n"; ?>
                        </td>
                    <?php
                    }
                } elseif (getCheckAuthority(47)) {
                    if ($res_y[$r]['sid'] == 4) {
                    ?>
                        <td style='font-size:0.90em;'>
                            <?php echo "��{$kintai_ym}����<BR>��ͭ��� <font color='red'>{$yukyu[0][1]}</font>/{$yukyu[0][0]}��<BR>��Ⱦ�� <font color='red'>{$yukyu[0][2]}</font>/20 �󡡻��ֵ� <font color='red'>{$yukyu[0][3]}</font>/{$yukyu[0][4]} ����\n"; ?>
                        </td>
                    <?php
                    }
                } elseif (getCheckAuthority(48)) {
                    if ($res_y[$r]['sid'] == 34) {
                    ?>
                        <td style='font-size:0.90em;'>
                            <?php echo "��{$kintai_ym}����<BR>��ͭ��� <font color='red'>{$yukyu[0][1]}</font>/{$yukyu[0][0]}��<BR>��Ⱦ�� <font color='red'>{$yukyu[0][2]}</font>/20 �󡡻��ֵ� <font color='red'>{$yukyu[0][3]}</font>/{$yukyu[0][4]} ����\n"; ?>
                        </td>
                    <?php
                    }
                } elseif (getCheckAuthority(49)) {
                    if ($res_y[$r]['sid'] == 35) {
                    ?>
                        <td style='font-size:0.90em;'>
                            <?php echo "��{$kintai_ym}����<BR>��ͭ��� <font color='red'>{$yukyu[0][1]}</font>/{$yukyu[0][0]}��<BR>��Ⱦ�� <font color='red'>{$yukyu[0][2]}</font>/20 �󡡻��ֵ� <font color='red'>{$yukyu[0][3]}</font>/{$yukyu[0][4]} ����\n"; ?>
                        </td>
                    <?php
                    }
                } elseif (getCheckAuthority(50)) {
                    if ($res_y[$r]['sid'] == 32) {
                    ?>
                        <td style='font-size:0.90em;'>
                            <?php echo "��{$kintai_ym}����<BR>��ͭ��� <font color='red'>{$yukyu[0][1]}</font>/{$yukyu[0][0]}��<BR>��Ⱦ�� <font color='red'>{$yukyu[0][2]}</font>/20 �󡡻��ֵ� <font color='red'>{$yukyu[0][3]}</font>/{$yukyu[0][4]} ����\n"; ?>
                        </td>
                    <?php
                    }
                } elseif (getCheckAuthority(51)) {
                    if ($res_y[$r]['sid'] == 2) {
                    ?>
                        <td style='font-size:0.90em;'>
                            <?php echo "��{$kintai_ym}����<BR>��ͭ��� <font color='red'>{$yukyu[0][1]}</font>/{$yukyu[0][0]}��<BR>��Ⱦ�� <font color='red'>{$yukyu[0][2]}</font>/20 �󡡻��ֵ� <font color='red'>{$yukyu[0][3]}</font>/{$yukyu[0][4]} ����\n"; ?>
                        </td>
                    <?php
                    }
                } elseif (getCheckAuthority(52)) {
                    if ($res_y[$r]['sid'] == 3) {
                    ?>
                        <td style='font-size:0.90em;'>
                            <?php echo "��{$kintai_ym}����<BR>��ͭ��� <font color='red'>{$yukyu[0][1]}</font>/{$yukyu[0][0]}��<BR>��Ⱦ�� <font color='red'>{$yukyu[0][2]}</font>/20 �󡡻��ֵ� <font color='red'>{$yukyu[0][3]}</font>/{$yukyu[0][4]} ����\n"; ?>
                        </td>
                    <?php
                    }
                
                } elseif (getCheckAuthority(55)) {
                    if ($res_y[$r]['sid'] == 17 || $res_y[$r]['sid'] == 34 || $res_y[$r]['sid'] == 35) {
                    ?>
                        <td style='font-size:0.90em;'>
                            <?php echo "��{$kintai_ym}����<BR>��ͭ��� <font color='red'>{$yukyu[0][1]}</font>/{$yukyu[0][0]}��<BR>��Ⱦ�� <font color='red'>{$yukyu[0][2]}</font>/20 �󡡻��ֵ� <font color='red'>{$yukyu[0][3]}</font>/{$yukyu[0][4]} ����\n"; ?>
                        </td>
                    <?php
                    }
                }
                }
                /*
                // �ʲ������ˤ���ޤ�ޤǤλ����ǤȤ��ơ���̳�ݰ��Τ߾Ȳ�
                if ($_SESSION['User_ID'] == '300144' || $_SESSION['User_ID'] == '300055' || $_SESSION['User_ID'] == '010472' || $_SESSION['User_ID'] == '015806') {
                    
                ?>
                <td style='font-size:0.90em;'>
                    <?php echo "��{$kintai_ym}����<BR>��ͭ��� <font color='red'>{$yukyu[0][1]}</font>/{$yukyu[0][0]}��<BR>��Ⱦ�� <font color='red'>{$yukyu[0][2]}</font>/20 �󡡻��ֵ� <font color='red'>{$yukyu[0][3]}</font>/{$yukyu[0][4]} ����\n"; ?>
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
                    <td width='15%'>��</td>
                        <td>��</td>
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
                        <?
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
                        <?
                        }
                        }
                        ?>
                <?php
                } elseif (getCheckAuthority(41)) {
                    if ($res_y[$r]['sid'] == 5 || $res_y[$r]['sid'] == 9 || $res_y[$r]['sid'] == 19 || $res_y[$r]['sid'] == 31) {
                    ?>
                        <td width='15%'>��</td>
                        <td>��</td>
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
                    <?php
                    }
                } elseif (getCheckAuthority(42)) {
                    if ($res_y[$r]['sid'] == 4 || $res_y[$r]['sid'] == 18 || $res_y[$r]['sid'] == 38) {
                    ?>
                        <td width='15%'>��</td>
                        <td>��</td>
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
                    <?php
                    }
                } elseif (getCheckAuthority(43)) {
                    if ($res_y[$r]['sid'] == 2 || $res_y[$r]['sid'] == 3 || $res_y[$r]['sid'] == 8 || $res_y[$r]['sid'] == 32) {
                    ?>
                        <td width='15%'>��</td>
                        <td>��</td>
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
                    <?php
                    }
                } elseif (getCheckAuthority(44)) {
                    if ($res_y[$r]['sid'] == 5) {
                    ?>
                        <td width='15%'>��</td>
                        <td>��</td>
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
                    <?php
                    }
                } elseif (getCheckAuthority(45)) {
                    if ($res_y[$r]['sid'] == 19) {
                    ?>
                        <td width='15%'>��</td>
                        <td>��</td>
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
                    <?php
                    }
                } elseif (getCheckAuthority(46)) {
                    if ($res_y[$r]['sid'] == 18) {
                    ?>
                        <td width='15%'>��</td>
                        <td>��</td>
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
                    <?php
                    }
                } elseif (getCheckAuthority(47)) {
                    if ($res_y[$r]['sid'] == 4) {
                    ?>
                        <td width='15%'>��</td>
                        <td>��</td>
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
                    <?php
                    }
                } elseif (getCheckAuthority(48)) {
                    if ($res_y[$r]['sid'] == 34) {
                    ?>
                        <td width='15%'>��</td>
                        <td>��</td>
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
                    <?php
                    }
                } elseif (getCheckAuthority(49)) {
                    if ($res_y[$r]['sid'] == 35) {
                    ?>
                        <td width='15%'>��</td>
                        <td>��</td>
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
                    <?php
                    }
                } elseif (getCheckAuthority(50)) {
                    if ($res_y[$r]['sid'] == 32) {
                    ?>
                        <td width='15%'>��</td>
                        <td>��</td>
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
                    <?php
                    }
                } elseif (getCheckAuthority(51)) {
                    if ($res_y[$r]['sid'] == 2) {
                    ?>
                        <td width='15%'>��</td>
                        <td>��</td>
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
                    <?php
                    }
                } elseif (getCheckAuthority(52)) {
                    if ($res_y[$r]['sid'] == 3) {
                    ?>
                        <td width='15%'>��</td>
                        <td>��</td>
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
                    }
                } elseif (getCheckAuthority(55)) {
                    if ($res_y[$r]['sid'] == 17 || $res_y[$r]['sid'] == 34 || $res_y[$r]['sid'] == 35) {
                    ?>
                        <td width='15%'>��</td>
                        <td>��</td>
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
                    <?php
                    }
                }
                ?>
            </tr>
            <tr>
                <td width='15%'>̾��</td>
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
                    <?php echo "�������ν����<br>���ж� {$timePro[0][0]}����� {$timePro[0][1]}\n"; ?>
                </td>
                <?php
                }
                ?>
            </tr>
            <tr>
                <td width='15%'>��°</td>
                <td width='30%'><?php echo($res_y[$r]['section_name']); ?></td>
                <?php
                if (getCheckAuthority(41)) {
                    if ($res_y[$r]['sid'] == 5 || $res_y[$r]['sid'] == 9 || $res_y[$r]['sid'] == 19 || $res_y[$r]['sid'] == 31) {
                    ?>
                        <?php
                        if ($res_y[$r]['uid']!='002321') {
                            /*** 2003/04/21 ADD ***/
                            if ($_SESSION["Auth"] >= AUTH_LEVEL1) {
                                echo "<td>������ǯ��</td>\n";
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
                                echo "<td>������ǯ��</td>\n";
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
                                echo "<td>������ǯ��</td>\n";
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
                                echo "<td>������ǯ��</td>\n";
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
                                echo "<td>������ǯ��</td>\n";
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
                                echo "<td>������ǯ��</td>\n";
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
                                echo "<td>������ǯ��</td>\n";
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
                                echo "<td>������ǯ��</td>\n";
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
                                echo "<td>������ǯ��</td>\n";
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
                                echo "<td>������ǯ��</td>\n";
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
                                echo "<td>������ǯ��</td>\n";
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
                                echo "<td>������ǯ��</td>\n";
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
                                echo "<td>������ǯ��</td>\n";
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
                        echo "<td>������ǯ��</td>\n";
                    }
                }
                ?>
            </tr>
            <tr><td width='15%'>����</td>
                <td><?php echo($res_y[$r]["position_name"]); ?></td>
                <td>��</td>
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
                                    printf("<td><font color='red'><b>��%s��%s����%s��</b></font></td>\n", $res_age[0]['years'], $res_age[0]['mons'], $res_age[0]['days']);
                                }
                            }
                        } else {
                            echo "<td>��</td>\n";
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
                                    printf("<td><font color='red'><b>��%s��%s����%s��</b></font></td>\n", $res_age[0]['years'], $res_age[0]['mons'], $res_age[0]['days']);
                                }
                            }
                        } else {
                            echo "<td>��</td>\n";
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
                                    printf("<td><font color='red'><b>��%s��%s����%s��</b></font></td>\n", $res_age[0]['years'], $res_age[0]['mons'], $res_age[0]['days']);
                                }
                            }
                        } else {
                            echo "<td>��</td>\n";
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
                                    printf("<td><font color='red'><b>��%s��%s����%s��</b></font></td>\n", $res_age[0]['years'], $res_age[0]['mons'], $res_age[0]['days']);
                                }
                            }
                        } else {
                            echo "<td>��</td>\n";
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
                                    printf("<td><font color='red'><b>��%s��%s����%s��</b></font></td>\n", $res_age[0]['years'], $res_age[0]['mons'], $res_age[0]['days']);
                                }
                            }
                        } else {
                            echo "<td>��</td>\n";
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
                                    printf("<td><font color='red'><b>��%s��%s����%s��</b></font></td>\n", $res_age[0]['years'], $res_age[0]['mons'], $res_age[0]['days']);
                                }
                            }
                        } else {
                            echo "<td>��</td>\n";
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
                                    printf("<td><font color='red'><b>��%s��%s����%s��</b></font></td>\n", $res_age[0]['years'], $res_age[0]['mons'], $res_age[0]['days']);
                                }
                            }
                        } else {
                            echo "<td>��</td>\n";
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
                                    printf("<td><font color='red'><b>��%s��%s����%s��</b></font></td>\n", $res_age[0]['years'], $res_age[0]['mons'], $res_age[0]['days']);
                                }
                            }
                        } else {
                            echo "<td>��</td>\n";
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
                                    printf("<td><font color='red'><b>��%s��%s����%s��</b></font></td>\n", $res_age[0]['years'], $res_age[0]['mons'], $res_age[0]['days']);
                                }
                            }
                        } else {
                            echo "<td>��</td>\n";
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
                                    printf("<td><font color='red'><b>��%s��%s����%s��</b></font></td>\n", $res_age[0]['years'], $res_age[0]['mons'], $res_age[0]['days']);
                                }
                            }
                        } else {
                            echo "<td>��</td>\n";
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
                                    printf("<td><font color='red'><b>��%s��%s����%s��</b></font></td>\n", $res_age[0]['years'], $res_age[0]['mons'], $res_age[0]['days']);
                                }
                            }
                        } else {
                            echo "<td>��</td>\n";
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
                                    printf("<td><font color='red'><b>��%s��%s����%s��</b></font></td>\n", $res_age[0]['years'], $res_age[0]['mons'], $res_age[0]['days']);
                                }
                            }
                        } else {
                            echo "<td>��</td>\n";
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
                                    printf("<td><font color='red'><b>��%s��%s����%s��</b></font></td>\n", $res_age[0]['years'], $res_age[0]['mons'], $res_age[0]['days']);
                                }
                            }
                        } else {
                            echo "<td>��</td>\n";
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
                            printf("<td><font color='red'><b>��%s��%s����%s��</b></font></td>\n", $res_age[0]['years'], $res_age[0]['mons'], $res_age[0]['days']);
                        }
                    }
                } else {
                    echo "<td>��</td>\n";
                }
                ?>
            </tr>
<?php
            if($_SESSION["Auth"] >= AUTH_LEVEL3){   // ���ɥߥ˥��ȥ졼���� ���٤Ƥ�ɽ��
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
                <input type='button' name='historyDisp' value='����ɽ��' title='���顦��ʡ���ư��������ɽ����Ԥ��ޤ���'
                    onClick='win_open("print/print_emp_branch_user.php?targetUser=<?php echo $res_y[$r]['uid']?>", 800, 700);'
                >
                <input type="submit" name="inf" value="������ѹ�">
                <input type="submit" name="pwd" value="�ѥ���ɤ��ѹ�"></td>
            </tr>
            <?php
            } elseif ($_SESSION['User_ID'] == '300551') {   // ����ô���� ���٤Ƥ����� ����Ⱦ�����ѹ�ɽ��
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
                <input type='button' name='historyDisp' value='����ɽ��' title='���顦��ʡ���ư��������ɽ����Ԥ��ޤ���'
                    onClick='win_open("print/print_emp_branch_user.php?targetUser=<?php echo $res_y[$r]['uid']?>", 800, 700);'
                >
                <input type="submit" name="inf" value="������ѹ�">
            </tr>
            <?php
            } elseif (getCheckAuthority(60)) {  // ��Ĺ������Ĺ��������Ĺ����̳��Ĺ ���٤Ƥ��������ɽ��
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
                <input type='button' name='historyDisp' value='����ɽ��' title='���顦��ʡ���ư��������ɽ����Ԥ��ޤ���'
                    onClick='win_open("print/print_emp_branch_user.php?targetUser=<?php echo $res_y[$r]['uid']?>", 800, 700);'
                >
            </tr>
            <?php
            } elseif (getCheckAuthority(61)) {  // ��Ĺ�����ʾ� ������Τ߷���ɽ��
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
                        <input type='button' name='historyDisp' value='����ɽ��' title='���顦��ʡ���ư��������ɽ����Ԥ��ޤ���'
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
                        <input type='button' name='historyDisp' value='����ɽ��' title='���顦��ʡ���ư��������ɽ����Ԥ��ޤ���'
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
                        <input type='button' name='historyDisp' value='����ɽ��' title='���顦��ʡ���ư��������ɽ����Ԥ��ޤ���'
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
                        <input type='button' name='historyDisp' value='����ɽ��' title='���顦��ʡ���ư��������ɽ����Ԥ��ޤ���'
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
                        <input type='button' name='historyDisp' value='����ɽ��' title='���顦��ʡ���ư��������ɽ����Ԥ��ޤ���'
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
                        <input type='button' name='historyDisp' value='����ɽ��' title='���顦��ʡ���ư��������ɽ����Ԥ��ޤ���'
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
                        <input type='button' name='historyDisp' value='����ɽ��' title='���顦��ʡ���ư��������ɽ����Ԥ��ޤ���'
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
                        <input type='button' name='historyDisp' value='����ɽ��' title='���顦��ʡ���ư��������ɽ����Ԥ��ޤ���'
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
                        <input type='button' name='historyDisp' value='����ɽ��' title='���顦��ʡ���ư��������ɽ����Ԥ��ޤ���'
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
                        <input type='button' name='historyDisp' value='����ɽ��' title='���顦��ʡ���ư��������ɽ����Ԥ��ޤ���'
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
                        <input type='button' name='historyDisp' value='����ɽ��' title='���顦��ʡ���ư��������ɽ����Ԥ��ޤ���'
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
                        <input type='button' name='historyDisp' value='����ɽ��' title='���顦��ʡ���ư��������ɽ����Ԥ��ޤ���'
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
                        <input type='button' name='historyDisp' value='����ɽ��' title='���顦��ʡ���ư��������ɽ����Ԥ��ޤ���'
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
            echo("<td align='left'><input disable type='submit' name='lookup_prev' value='����'></td>\n");
        if($rows>$offset+VIEW_LIMIT){
            if(0==$offset)
                echo("<td colspan=2 align='right'><input type='submit' name='lookup_next' value='����'></td>\n");
            else
                echo("<td align='right'><input type='submit' name='lookup_next' value='����'></td>\n");
        }
        echo("</tr></table></form>\n");
        echo("</tr>\n");
    }
?>
</table>
