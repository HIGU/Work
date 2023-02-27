<?php
//////////////////////////////////////////////////////////////////////////////
// �»�״ط� � �Х���롦��˥� »�׷׻���                            //
// Copyright (C) 2009 - 2013 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp  //
// Changed history                                                          //
// 2009/08/21 Created   profit_loss_pl_act_bl.php                           //
// 2009/10/06 $b_invent_sagaku=0��̵���٥��顼����                        //
// 2009/10/07 ��������Ĵ�����б�                                        //
// 2009/10/15 ���⡦��������ס��Ķ����ס��о����פ��������ѹ�            //
// 2009/11/02 ���������Ϳ�׻����б�                                        //
// 2009/11/12 ��˥���Ĵ���ۤ����ޤ������ʤ��ä��Τ���                  //
// 2009/12/10 �������ȴ���ƥХ�����˥���»�פ��ѹ�                    //
// 2010/01/15 200912ʬ�ϫ̳��Ĵ����������                              //
// 2010/01/19 200912�٤ζ�̳���������Ȥ���¾��Ĵ����1�����ᤷ��ʬ���       //
// 2010/01/26 profit_loss_comment_put_bls2.php����                          //
//            profit_loss_comment_put_bl.php���ѹ�                          //
// 2010/02/04 201001�٤���Ķȳ���Ͱ�������ꤹ��褦���ѹ�                //
//            ���Ʒ׻����������褦���ѹ�                                  //
// 2010/02/08 201001�٤������ꤷ��ϫ̳����̣����褦���ѹ�                //
// 2010/03/04 ����ʬ�ηо����פη׻������������ä��Τ���                  //
// 2010/04/08 $p2_l_kyu_kin��2��ȴ���Ƥ�����������                          //
// 2010/05/11 201004�٤Υ�˥����(�ġ���)ʬ255,240�ߤ�����ʬ���ä��Τ�     //
//            ����Ū�˥ޥ��ʥ�����                                          //
// 2010/10/08 ����պ����ѤΥǡ�����Ͽ���ɲ�                                //
// 2011/07/14 �ǡ�����Ͽ��ϫ̳��ȷ���Υǡ�����Ʊ�����ä��Τ���          //
// 2012/02/28 2012ǯ1�� ��̳������ Ĵ�� ��˥���¤���� +1,156,130��         //
//             �� ʿ�в����ɸ��� 2��˵�Ĵ����Ԥ�����                      //
// 2012/03/05 2012ǯ1�� ��̳������ Ĵ�� ��˥���¤���� -1,156,130�� �ᤷ    //
// 2013/01/28 �Х�������Υݥ�פ��ѹ���ɽ���Τߥǡ����ϥХ����Τޤޡ�  //
// 2014/09/04 ���ɤ���¤����ϫ̳���ƥ�����������ΰ�Ĵ��                //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug ��
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��

require_once ('../function.php');           // define.php �� pgsql.php �� require_once ���Ƥ���
require_once ('../tnk_func.php');           // TNK �˰�¸������ʬ�δؿ��� require_once ���Ƥ���
require_once ('../MenuHeader.php');         // TNK ������ menu class
access_log();                               // Script Name �ϼ�ư����

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader(0);                  // ǧ�ڥ����å�0=���̰ʾ� �����=TOP_MENU �����ȥ�̤����
    // �ºݤ�ǧ�ڤ�profit_loss_submit.php�ǹԤäƤ���account_group_check()�����

////////////// ����������
// $menu->set_site(10, 7);                  // site_index=10(»�ץ�˥塼) site_id=7(�»��)
//////////// ɽ�������
$menu->set_caption('�������칩��(��)');
//////////// �ƽ����action̾�ȥ��ɥ쥹����
$menu->set_action('�õ���������',   PL . 'profit_loss_comment_put_bl.php');

///// ������μ���
$ki = Ym_to_tnk($_SESSION['pl_ym']);
$tuki = substr($_SESSION['pl_ym'],4,2);

//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title("�� {$ki} ����{$tuki} ���١��� �� �� �� �� » �� �� �� ��");

///// �о�����
$yyyymm = $_SESSION['pl_ym'];
///// �о�����
if (substr($yyyymm,4,2)!=01) {
    $p1_ym = $yyyymm - 1;
} else {
    $p1_ym = $yyyymm - 100;
    $p1_ym = $p1_ym + 11;
}
///// �о�������
if (substr($p1_ym,4,2)!=01) {
    $p2_ym = $p1_ym - 1;
} else {
    $p2_ym = $p1_ym - 100;
    $p2_ym = $p2_ym + 11;
}
///// ����ǯ��λ���
$yyyy = substr($yyyymm, 0,4);
$mm   = substr($yyyymm, 4,2);
if (($mm >= 1) && ($mm <= 3)) {
    $yyyy = ($yyyy - 1);
}
$str_ym = $yyyy . "04";     // ����ǯ��

///// ɽ��ñ�̤��������
if (isset($_POST['keihi_tani'])) {
    $_SESSION['keihi_tani'] = $_POST['keihi_tani'];
    $tani = $_SESSION['keihi_tani'];
} elseif (isset($_SESSION['keihi_tani'])) {
    $tani = $_SESSION['keihi_tani'];
} else {
    $tani = 1000;           // ����� ɽ��ñ�� ���
    $_SESSION['keihi_tani'] = $tani;
}
///// ɽ�� ��������� �������
if (isset($_POST['keihi_keta'])) {
    $_SESSION['keihi_keta'] = $_POST['keihi_keta'];
    $keta = $_SESSION['keihi_keta'];
} elseif (isset($_SESSION['keihi_keta'])) {
    $keta = $_SESSION['keihi_keta'];
} else {
    $keta = 0;              // ����� �������ʲ����
    $_SESSION['keihi_keta'] = $keta;
}

/********** ���� **********/
    ///// ����
if ($yyyymm >= 200909) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�����'", $yyyymm);
    if (getUniResult($query, $s_uri) < 1) {
        $s_uri        = 0;      // ��������
        $s_uri_sagaku = 0;
    } else {
        if ($yyyymm == 200906) {
            $s_uri = $s_uri - 3100900;
        } elseif ($yyyymm == 200905) {
            $s_uri = $s_uri + 1550450;
        } elseif ($yyyymm == 200904) {
            $s_uri = $s_uri + 1550450;
        }
        $s_uri_sagaku = $s_uri;
    }
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='����Ĵ����'", $yyyymm);
    if (getUniResult($query, $s_uri_cho) < 1) {
        // ��������
        $s_uri = number_format(($s_uri / $tani), $keta);
    } else {
        $s_uri_sagaku = $s_uri_sagaku + $s_uri_cho;
        $s_uri        = number_format(($s_uri / $tani), $keta);
    }
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�����'", $yyyymm);
    if (getUniResult($query, $s_uri) < 1) {
        $s_uri        = 0;      // ��������
        $s_uri_sagaku = 0;
    } else {
        if ($yyyymm == 200906) {
            $s_uri = $s_uri - 3100900;
        } elseif ($yyyymm == 200905) {
            $s_uri = $s_uri + 1550450;
        } elseif ($yyyymm == 200904) {
            $s_uri = $s_uri + 1550450;
        }
        $s_uri_sagaku = $s_uri;
        $s_uri = number_format(($s_uri / $tani), $keta);
    }
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�Х��������'", $yyyymm);
if (getUniResult($query, $b_uri) < 1) {
    $b_uri        = 0;          // ��������
    $b_uri_sagaku = 0;
} else {
    $b_uri_sagaku = $b_uri;
    $b_uri        = number_format(($b_uri / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥�����'", $yyyymm);
if (getUniResult($query, $l_uri) < 1) {
    $l_uri         = 0;         // ��������
    $lh_uri        = 0;
    $lh_uri_sagaku = 0;
} else {
    if ($yyyymm == 200906) {
        $l_uri = $l_uri - 3100900;
    } elseif ($yyyymm == 200905) {
        $l_uri = $l_uri + 1550450;
    } elseif ($yyyymm == 200904) {
        $l_uri = $l_uri + 1550450;
    }
    if ($yyyymm == 201004) {
        $l_uri = $l_uri - 255240;
    }
    $lh_uri        = $l_uri - $s_uri_sagaku - $b_uri_sagaku;
    $lh_uri_sagaku = $lh_uri;
    $l_uri         = $l_uri - $s_uri_sagaku;                   // �����������˥��������ޥ��ʥ�
    $lh_uri        = number_format(($lh_uri / $tani), $keta);
    $l_uri         = number_format(($l_uri / $tani), $keta);
}
    ///// ����
if ($yyyymm >= 200910) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�����'", $p1_ym);
    if (getUniResult($query, $p1_s_uri) < 1) {
        $p1_s_uri        = 0;     // ��������
        $p1_s_uri_sagaku = 0;
    } else {
        if ($p1_ym == 200906) {
            $p1_s_uri = $p1_s_uri - 3100900;
        } elseif ($p1_ym == 200905) {
            $p1_s_uri = $p1_s_uri + 1550450;
        } elseif ($p1_ym == 200904) {
            $p1_s_uri = $p1_s_uri + 1550450;
        }
        $p1_s_uri_sagaku = $p1_s_uri;
    }
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='����Ĵ����'", $p1_ym);
    if (getUniResult($query, $p1_s_uri_cho) < 1) {
        // ��������
        $p1_s_uri = number_format(($p1_s_uri / $tani), $keta);
    } else {
        $p1_s_uri_sagaku = $p1_s_uri_sagaku + $p1_s_uri_cho;
        $p1_s_uri        = number_format(($p1_s_uri / $tani), $keta);
    }
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�����'", $p1_ym);
    if (getUniResult($query, $p1_s_uri) < 1) {
        $p1_s_uri        = 0;     // ��������
        $p1_s_uri_sagaku = 0;
    } else {
        if ($p1_ym == 200906) {
            $p1_s_uri = $p1_s_uri - 3100900;
        } elseif ($p1_ym == 200905) {
            $p1_s_uri = $p1_s_uri + 1550450;
        } elseif ($p1_ym == 200904) {
            $p1_s_uri = $p1_s_uri + 1550450;
        }
        $p1_s_uri_sagaku = $p1_s_uri;
        $p1_s_uri        = number_format(($p1_s_uri / $tani), $keta);
    }
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�Х��������'", $p1_ym);
if (getUniResult($query, $p1_b_uri) < 1) {
    $p1_b_uri        = 0;     // ��������
    $p1_b_uri_sagaku = 0;
} else {
    $p1_b_uri_sagaku = $p1_b_uri;
    $p1_b_uri        = number_format(($p1_b_uri / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥�����'", $p1_ym);
if (getUniResult($query, $p1_l_uri) < 1) {
    $p1_l_uri         = 0;    // ��������
    $p1_lh_uri        = 0;
    $p1_lh_uri_sagaku = 0;
} else {
    if ($p1_ym == 200906) {
        $p1_l_uri = $p1_l_uri - 3100900;
    } elseif ($p1_ym == 200905) {
        $p1_l_uri = $p1_l_uri + 1550450;
    } elseif ($p1_ym == 200904) {
        $p1_l_uri = $p1_l_uri + 1550450;
    }
    if ($p1_ym == 201004) {
        $p1_l_uri = $p1_l_uri - 255240;
    }
    $p1_lh_uri        = $p1_l_uri - $p1_s_uri_sagaku - $p1_b_uri_sagaku;
    $p1_lh_uri_sagaku = $p1_lh_uri;
    $p1_l_uri         = $p1_l_uri - $p1_s_uri_sagaku;                   // �����������˥��������ޥ��ʥ�
    $p1_lh_uri        = number_format(($p1_lh_uri / $tani), $keta);
    $p1_l_uri         = number_format(($p1_l_uri / $tani), $keta);
}
    ///// ������
if ($yyyymm >= 200911) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�����'", $p2_ym);
    if (getUniResult($query, $p2_s_uri) < 1) {
        $p2_s_uri        = 0;     // ��������
        $p2_s_uri_sagaku = 0;
    } else {
        if ($p2_ym == 200906) {
            $p2_s_uri = $p2_s_uri - 3100900;
        } elseif ($p2_ym == 200905) {
            $p2_s_uri = $p2_s_uri + 1550450;
        } elseif ($p2_ym == 200904) {
            $p2_s_uri = $p2_s_uri + 1550450;
        }
        $p2_s_uri_sagaku = $p2_s_uri;
    }
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='����Ĵ����'", $p2_ym);
    if (getUniResult($query, $p2_s_uri_cho) < 1) {
        // ��������
        $p2_s_uri = number_format(($p2_s_uri / $tani), $keta);
    } else {
        $p2_s_uri_sagaku = $p2_s_uri_sagaku + $p2_s_uri_cho;
        $p2_s_uri        = number_format(($p2_s_uri / $tani), $keta);
    }
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�����'", $p2_ym);
    if (getUniResult($query, $p2_s_uri) < 1) {
        $p2_s_uri        = 0;     // ��������
        $p2_s_uri_sagaku = 0;
    } else {
        if ($p2_ym == 200906) {
            $p2_s_uri = $p2_s_uri - 3100900;
        } elseif ($p2_ym == 200905) {
            $p2_s_uri = $p2_s_uri + 1550450;
        } elseif ($p2_ym == 200904) {
            $p2_s_uri = $p2_s_uri + 1550450;
        }
        $p2_s_uri_sagaku = $p2_s_uri;
        $p2_s_uri        = number_format(($p2_s_uri / $tani), $keta);
    }
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�Х��������'", $p2_ym);
if (getUniResult($query, $p2_b_uri) < 1) {
    $p2_b_uri        = 0;     // ��������
    $p2_b_uri_sagaku = 0;
} else {
    $p2_b_uri_sagaku = $p2_b_uri;
    $p2_b_uri        = number_format(($p2_b_uri / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥�����'", $p2_ym);
if (getUniResult($query, $p2_l_uri) < 1) {
    $p2_l_uri         = 0;    // ��������
    $p2_lh_uri        = 0;
    $p2_lh_uri_sagaku = 0;
} else {
    if ($p2_ym == 200906) {
        $p2_l_uri = $p2_l_uri - 3100900;
    } elseif ($p2_ym == 200905) {
        $p2_l_uri = $p2_l_uri + 1550450;
    } elseif ($p2_ym == 200904) {
        $p2_l_uri = $p2_l_uri + 1550450;
    }
    if ($p2_ym == 201004) {
        $p2_l_uri = $p2_l_uri - 255240;
    }
    $p2_lh_uri        = $p2_l_uri - $p2_s_uri_sagaku - $p2_b_uri_sagaku;
    $p2_lh_uri_sagaku = $p2_lh_uri;
    $p2_l_uri         = $p2_l_uri - $p2_s_uri_sagaku;                   // �����������˥��������ޥ��ʥ�
    $p2_lh_uri        = number_format(($p2_lh_uri / $tani), $keta);
    $p2_l_uri         = number_format(($p2_l_uri / $tani), $keta);
}
    ///// �����߷�
if ($yyyymm >= 200909) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='�����'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_s_uri) < 1) {
        $rui_s_uri        = 0;      // ��������
        $rui_s_uri_sagaku = 0;
    } else {
        $rui_s_uri_sagaku = $rui_s_uri;
    }
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='����Ĵ����'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_s_uri_cho) < 1) {
        // ��������
        $rui_s_uri        = number_format(($rui_s_uri / $tani), $keta);
    } else {
        $rui_s_uri_sagaku = $rui_s_uri_sagaku + $rui_s_uri_cho;
        $rui_s_uri        = number_format(($rui_s_uri / $tani), $keta);
    }
} else {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='�����'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_s_uri) < 1) {
        $rui_s_uri        = 0;     // ��������
        $rui_s_uri_sagaku = 0;
    } else {
        if ($yyyymm == 200905) {
            $rui_s_uri = $rui_s_uri + 3100900;
        } elseif ($yyyymm == 200904) {
            $rui_s_uri = $rui_s_uri + 1550450;
        }
        $rui_s_uri_sagaku = $rui_s_uri;
        $rui_s_uri        = number_format(($rui_s_uri / $tani), $keta);
    }
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='�Х��������'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_b_uri) < 1) {
    $rui_b_uri        = 0;         // ��������
    $rui_b_uri_sagaku = 0;
} else {
    $rui_b_uri_sagaku = $rui_b_uri;
    $rui_b_uri        = number_format(($rui_b_uri / $tani), $keta);
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='��˥�����'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_l_uri) < 1) {
    $rui_l_uri         = 0;        // ��������
    $rui_lh_uri        = 0;
    $rui_lh_uri_sagaku = 0;
} else {
    if ($yyyymm == 200905) {
        $rui_l_uri     = $rui_l_uri + 3100900;
    } elseif ($yyyymm == 200904) {
        $rui_l_uri     = $rui_l_uri + 1550450;
    }
    if ($yyyymm >= 201004 && $yyyymm <= 201103) {
        $rui_l_uri = $rui_l_uri - 255240;
    }
    $rui_lh_uri        = $rui_l_uri - $rui_s_uri_sagaku - $rui_b_uri_sagaku;
    $rui_lh_uri_sagaku = $rui_lh_uri;
    $rui_l_uri         = $rui_l_uri - $rui_s_uri_sagaku;                   // �����������˥��������ޥ��ʥ�
    $rui_lh_uri        = number_format(($rui_lh_uri / $tani), $keta);
    $rui_l_uri         = number_format(($rui_l_uri / $tani), $keta);
}

/********** ��������ų���ê���� **********/
    ///// �������
$p2_s_invent  = 0;
$p1_s_invent  = 0;
$s_invent     = 0;
$rui_s_invent = 0;
    ///// ����
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�Х�������ê����'", $yyyymm);
if (getUniResult($query, $b_invent) < 1) {
    $b_invent = 0;              // ��������
    $b_invent_sagaku = 0;
} else {
    $b_invent_sagaku = $b_invent;
    $b_invent        = number_format(($b_invent / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥�����ê����'", $yyyymm);
if (getUniResult($query, $l_invent) < 1) {
    $l_invent         = 0;      // ��������
    $lh_invent        = 0;
    $lh_invent_sagaku = 0;
} else {
    $lh_invent        = $l_invent - $s_invent - $b_invent_sagaku;
    $lh_invent_sagaku = $lh_invent;
    $lh_invent        = number_format(($lh_invent / $tani), $keta);
    $l_invent         = number_format(($l_invent / $tani), $keta);
}
    ///// ����
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�Х�������ê����'", $p1_ym);
if (getUniResult($query, $p1_b_invent) < 1) {
    $p1_b_invent        = 0;    // ��������
    $p1_b_invent_sagaku = 0;
} else {
    $p1_b_invent_sagaku = $p1_b_invent;
    $p1_b_invent        = number_format(($p1_b_invent / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥�����ê����'", $p1_ym);
if (getUniResult($query, $p1_l_invent) < 1) {
    $p1_l_invent         = 0;   // ��������
    $p1_lh_invent        = 0;
    $p1_lh_invent_sagaku = 0;
} else {
    $p1_lh_invent        = $p1_l_invent - $p1_s_invent - $p1_b_invent_sagaku;
    $p1_lh_invent_sagaku = $p1_lh_invent;
    $p1_lh_invent        = number_format(($p1_lh_invent / $tani), $keta);
    $p1_l_invent         = number_format(($p1_l_invent / $tani), $keta);
}
    ///// ������
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�Х�������ê����'", $p2_ym);
if (getUniResult($query, $p2_b_invent) < 1) {
    $p2_b_invent        = 0;    // ��������
    $p2_b_invent_sagaku = 0;
} else {
    $p2_b_invent_sagaku = $p2_b_invent;
    $p2_b_invent        = number_format(($p2_b_invent / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥�����ê����'", $p2_ym);
if (getUniResult($query, $p2_l_invent) < 1) {
    $p2_l_invent         = 0;   // ��������
    $p2_lh_invent        = 0;
    $p2_lh_invent_sagaku = 0;
} else {
    $p2_lh_invent        = $p2_l_invent - $p2_s_invent - $p2_b_invent_sagaku;
    $p2_lh_invent_sagaku = $p2_lh_invent;
    $p2_lh_invent        = number_format(($p2_lh_invent / $tani), $keta);
    $p2_l_invent         = number_format(($p2_l_invent / $tani), $keta);
}
    ///// �����߷�
    /////   ����ê������߷פ� ����ǯ��δ���ê����ˤʤ�
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym=%d and note='�Х�������ê����'", $str_ym);
if (getUniResult($query, $rui_b_invent) < 1) {
    $rui_b_invent        = 0;   // ��������
    $rui_b_invent_sagaku = 0;
} else {
    $rui_b_invent_sagaku = $rui_b_invent;
    $rui_b_invent        = number_format(($rui_b_invent / $tani), $keta);
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym=%d and note='��˥�����ê����'", $str_ym);
if (getUniResult($query, $rui_l_invent) < 1) {
    $rui_l_invent         = 0;  // ��������
    $rui_lh_invent        = 0;
    $rui_lh_invent_sagaku = 0;
} else {
    $rui_lh_invent        = $rui_l_invent - $rui_s_invent - $rui_b_invent_sagaku;
    $rui_lh_invent_sagaku = $rui_lh_invent;
    $rui_lh_invent        = number_format(($rui_lh_invent / $tani), $keta);
    $rui_l_invent         = number_format(($rui_l_invent / $tani), $keta);
}

/********** ������(������) **********/
    ///// ����
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�������'", $yyyymm);
if (getUniResult($query, $s_metarial) < 1) {
    $s_metarial        = 0;          // ��������
    $s_metarial_sagaku = 0;
} else {
    $s_metarial_sagaku = $s_metarial;
    $s_metarial        = number_format(($s_metarial / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�Х���������'", $yyyymm);
if (getUniResult($query, $b_metarial) < 1) {
    $b_metarial        = 0;          // ��������
    $b_metarial_sagaku = 0;
} else {
    $b_metarial_sagaku = $b_metarial;
    $b_metarial        = number_format(($b_metarial / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥�������'", $yyyymm);
if (getUniResult($query, $l_metarial) < 1) {
    $l_metarial         = 0 - $s_metarial_sagaku;     // ��������
    $lh_metarial        = 0;
    $lh_metarial_sagaku = 0;
} else {
    $lh_metarial        = $l_metarial - $s_metarial_sagaku - $b_metarial_sagaku;
    $lh_metarial_sagaku = $lh_metarial;
    $l_metarial         = $l_metarial - $s_metarial_sagaku;        // �������������˥��κ�������ޥ��ʥ�
    $lh_metarial        = number_format(($lh_metarial / $tani), $keta);
    $l_metarial         = number_format(($l_metarial / $tani), $keta);
}
    ///// ����
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�������'", $p1_ym);
if (getUniResult($query, $p1_s_metarial) < 1) {
    $p1_s_metarial        = 0;          // ��������
    $p1_s_metarial_sagaku = 0;
} else {
    $p1_s_metarial_sagaku = $p1_s_metarial;
    $p1_s_metarial        = number_format(($p1_s_metarial / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�Х���������'", $p1_ym);
if (getUniResult($query, $p1_b_metarial) < 1) {
    $p1_b_metarial        = 0;          // ��������
    $p1_b_metarial_sagaku = 0;
} else {
    $p1_b_metarial_sagaku = $p1_b_metarial;
    $p1_b_metarial        = number_format(($p1_b_metarial / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥�������'", $p1_ym);
if (getUniResult($query, $p1_l_metarial) < 1) {
    $p1_l_metarial         = 0 - $p1_s_metarial_sagaku;     // ��������
    $p1_lh_metarial        = 0;
    $p1_lh_metarial_sagaku = 0;
} else {
    $p1_lh_metarial        = $p1_l_metarial - $p1_s_metarial_sagaku - $p1_b_metarial_sagaku;
    $p1_lh_metarial_sagaku = $p1_lh_metarial;
    $p1_l_metarial         = $p1_l_metarial - $p1_s_metarial_sagaku;        // �������������˥��κ�������ޥ��ʥ�
    $p1_lh_metarial        = number_format(($p1_lh_metarial / $tani), $keta);
    $p1_l_metarial         = number_format(($p1_l_metarial / $tani), $keta);
}
    ///// ������
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�������'", $p2_ym);
if (getUniResult($query, $p2_s_metarial) < 1) {
    $p2_s_metarial        = 0;          // ��������
    $p2_s_metarial_sagaku = 0;
} else {
    $p2_s_metarial_sagaku = $p2_s_metarial;
    $p2_s_metarial        = number_format(($p2_s_metarial / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�Х���������'", $p2_ym);
if (getUniResult($query, $p2_b_metarial) < 1) {
    $p2_b_metarial        = 0;          // ��������
    $p2_b_metarial_sagaku = 0;
} else {
    $p2_b_metarial_sagaku = $p2_b_metarial;
    $p2_b_metarial        = number_format(($p2_b_metarial / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥�������'", $p2_ym);
if (getUniResult($query, $p2_l_metarial) < 1) {
    $p2_l_metarial         = 0 - $p2_s_metarial_sagaku;     // ��������
    $p2_lh_metarial        = 0;
    $p2_lh_metarial_sagaku = 0;
} else {
    $p2_lh_metarial        = $p2_l_metarial - $p2_s_metarial_sagaku - $p2_b_metarial_sagaku;
    $p2_lh_metarial_sagaku = $p2_lh_metarial;
    $p2_l_metarial         = $p2_l_metarial - $p2_s_metarial_sagaku;        // �������������˥��κ�������ޥ��ʥ�
    $p2_lh_metarial        = number_format(($p2_lh_metarial / $tani), $keta);
    $p2_l_metarial         = number_format(($p2_l_metarial / $tani), $keta);
}
    ///// �����߷�
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='�������'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_s_metarial) < 1) {
    $rui_s_metarial        = 0;          // ��������
    $rui_s_metarial_sagaku = 0;
} else {
    $rui_s_metarial_sagaku = $rui_s_metarial;
    $rui_s_metarial        = number_format(($rui_s_metarial / $tani), $keta);
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='�Х���������'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_b_metarial) < 1) {
    $rui_b_metarial        = 0;          // ��������
    $rui_b_metarial_sagaku = 0;
} else {
    $rui_b_metarial_sagaku = $rui_b_metarial;
    $rui_b_metarial        = number_format(($rui_b_metarial / $tani), $keta);
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='��˥�������'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_l_metarial) < 1) {
    $rui_l_metarial         = 0 - $rui_s_metarial_sagaku;   // ��������
    $rui_lh_metarial        = 0;
    $rui_lh_metarial_sagaku = 0;
} else {
    $rui_lh_metarial        = $rui_l_metarial - $rui_s_metarial_sagaku - $rui_b_metarial_sagaku;
    $rui_lh_metarial_sagaku = $rui_lh_metarial;
    $rui_l_metarial         = $rui_l_metarial - $rui_s_metarial_sagaku;        // �������������˥��κ�������ޥ��ʥ�
    $rui_lh_metarial        = number_format(($rui_lh_metarial / $tani), $keta);
    $rui_l_metarial         = number_format(($rui_l_metarial / $tani), $keta);
}

/********** ϫ̳�� **********/
    ///// ����
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�ϫ̳��'", $yyyymm);
if (getUniResult($query, $s_roumu) < 1) {
    $s_roumu        = 0;    // ��������
    $s_roumu_sagaku = 0;
} else {
    $s_roumu_sagaku = $s_roumu;
    $s_roumu        = number_format(($s_roumu / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�Х����ϫ̳��'", $yyyymm);
if (getUniResult($query, $b_roumu) < 1) {
    $b_roumu        = 0;    // ��������
    $b_roumu_sagaku = 0;
} else {
    $b_roumu_sagaku = $b_roumu;
    $b_roumu        = number_format(($b_roumu / $tani), $keta);
}
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥���Ϳ����Ψ'", $yyyymm);
    if (getUniResult($query, $l_kyu_kin) < 1) {
        $l_kyu_kin = 0;
    }
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥�ϫ̳��'", $yyyymm);
if (getUniResult($query, $l_roumu) < 1) {
    $l_roumu         = 0 - $s_roumu_sagaku;     // ��������]
    $lh_roumu        = 0;
    $lh_roumu_sagaku = 0;
} else {
    if ($yyyymm == 200912) {
        $l_roumu = $l_roumu + 182279;
    }
    if ($yyyymm >= 201001) {
        $l_roumu = $l_roumu + $l_kyu_kin;   // ��˥������Ϳ���̣(����ɸ��)
        //$l_roumu = $l_roumu + 151313;   // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
    }
    if ($yyyymm == 201408) {
        $l_roumu = $l_roumu + 229464;
    }
    $lh_roumu        = $l_roumu - $s_roumu_sagaku - $b_roumu_sagaku;
    $lh_roumu_sagaku = $lh_roumu;
    $l_roumu         = $l_roumu - $s_roumu_sagaku;               // �����ϫ̳����˥���ϫ̳����ޥ��ʥ�
    $lh_roumu        = number_format(($lh_roumu / $tani), $keta);
    $l_roumu         = number_format(($l_roumu / $tani), $keta);
}
    ///// ����
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�ϫ̳��'", $p1_ym);
if (getUniResult($query, $p1_s_roumu) < 1) {
    $p1_s_roumu        = 0;    // ��������
    $p1_s_roumu_sagaku = 0;
} else {
    $p1_s_roumu_sagaku = $p1_s_roumu;
    $p1_s_roumu        = number_format(($p1_s_roumu / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�Х����ϫ̳��'", $p1_ym);
if (getUniResult($query, $p1_b_roumu) < 1) {
    $p1_b_roumu        = 0;    // ��������
    $p1_b_roumu_sagaku = 0;
} else {
    $p1_b_roumu_sagaku = $p1_b_roumu;
    $p1_b_roumu        = number_format(($p1_b_roumu / $tani), $keta);
}
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥���Ϳ����Ψ'", $p1_ym);
    if (getUniResult($query, $p1_l_kyu_kin) < 1) {
        $p1_l_kyu_kin = 0;
    }
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥�ϫ̳��'", $p1_ym);
if (getUniResult($query, $p1_l_roumu) < 1) {
    $p1_l_roumu         = 0 - $p1_s_roumu_sagaku;     // ��������
    $p1_lh_roumu        = 0;
    $p1_lh_roumu_sagaku = 0;
} else {
    if ($p1_ym == 200912) {
        $p1_l_roumu = $p1_l_roumu + 182279;
    }
    if ($p1_ym >= 201001) {
        $p1_l_roumu = $p1_l_roumu + $p1_l_kyu_kin;   // ��˥������Ϳ���̣(����ɸ��)
        //$p1_l_roumu = $p1_l_roumu + 151313; // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
    }
    if ($p1_ym == 201408) {
        $p1_l_roumu = $p1_l_roumu + 229464;
    }
    $p1_lh_roumu        = $p1_l_roumu - $p1_s_roumu_sagaku - $p1_b_roumu_sagaku;
    $p1_lh_roumu_sagaku = $p1_lh_roumu;
    $p1_l_roumu         = $p1_l_roumu - $p1_s_roumu_sagaku;               // �����ϫ̳����˥���ϫ̳����ޥ��ʥ�
    $p1_lh_roumu        = number_format(($p1_lh_roumu / $tani), $keta);
    $p1_l_roumu         = number_format(($p1_l_roumu / $tani), $keta);
}
    ///// ������
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�ϫ̳��'", $p2_ym);
if (getUniResult($query, $p2_s_roumu) < 1) {
    $p2_s_roumu        = 0;    // ��������
    $p2_s_roumu_sagaku = 0;
} else {
    $p2_s_roumu_sagaku = $p2_s_roumu;
    $p2_s_roumu        = number_format(($p2_s_roumu / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�Х����ϫ̳��'", $p2_ym);
if (getUniResult($query, $p2_b_roumu) < 1) {
    $p2_b_roumu        = 0;    // ��������
    $p2_b_roumu_sagaku = 0;
} else {
    $p2_b_roumu_sagaku = $p2_b_roumu;
    $p2_b_roumu        = number_format(($p2_b_roumu / $tani), $keta);
}
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥���Ϳ����Ψ'", $p2_ym);
    if (getUniResult($query, $p2_l_kyu_kin) < 1) {
        $p2_l_kyu_kin = 0;
    }
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥�ϫ̳��'", $p2_ym);
if (getUniResult($query, $p2_l_roumu) < 1) {
    $p2_l_roumu         = 0 - $p2_s_roumu_sagaku;     // ��������
    $p2_lh_roumu        = 0;
    $p2_lh_roumu_sagaku = 0;
} else {
    if ($p2_ym == 200912) {
        $p2_l_roumu = $p2_l_roumu + 182279;
    }
    if ($p2_ym >= 201001) {
        $p2_l_roumu = $p2_l_roumu + $p2_l_kyu_kin;   // ��˥������Ϳ���̣(����ɸ��)
        //$p2_l_roumu = $p2_l_roumu + 151313;     // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
    }
    if ($p2_ym == 201408) {
        $p2_l_roumu = $p2_l_roumu + 229464;
    }
    $p2_lh_roumu        = $p2_l_roumu - $p2_s_roumu_sagaku - $p2_b_roumu_sagaku;
    $p2_lh_roumu_sagaku = $p2_lh_roumu;
    $p2_l_roumu         = $p2_l_roumu - $p2_s_roumu_sagaku;               // �����ϫ̳����˥���ϫ̳����ޥ��ʥ�
    $p2_lh_roumu        = number_format(($p2_lh_roumu / $tani), $keta);
    $p2_l_roumu         = number_format(($p2_l_roumu / $tani), $keta);
}
    ///// �����߷�
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='�ϫ̳��'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_s_roumu) < 1) {
    $rui_s_roumu        = 0;    // ��������
    $rui_s_roumu_sagaku = 0;
} else {
    $rui_s_roumu_sagaku = $rui_s_roumu;
    $rui_s_roumu        = number_format(($rui_s_roumu / $tani), $keta);
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='�Х����ϫ̳��'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_b_roumu) < 1) {
    $rui_b_roumu        = 0;    // ��������
    $rui_b_roumu_sagaku = 0;
} else {
    $rui_b_roumu_sagaku = $rui_b_roumu;
    $rui_b_roumu        = number_format(($rui_b_roumu / $tani), $keta);
}
if ($yyyymm >= 201001) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='��˥���Ϳ����Ψ'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_l_kyu_kin) < 1) {
        $rui_l_kyu_kin = 0;
    }
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='��˥�ϫ̳��'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_l_roumu) < 1) {
    $rui_l_roumu         = 0 - $rui_s_roumu_sagaku;   // ��������
    $rui_lh_roumu        = 0;
    $rui_lh_roumu_sagaku = 0;
} else {
    if ($yyyymm >= 200912 && $yyyymm <= 201003) {
        $rui_l_roumu = $rui_l_roumu + 182279;
    }
    if ($yyyymm >= 201001) {
        $rui_l_roumu = $rui_l_roumu + $rui_l_kyu_kin;   // ��˥������Ϳ���̣(����ɸ��)
        //$rui_l_roumu = $rui_l_roumu + 151313;   // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
    }
    if ($yyyymm >= 201408 && $yyyymm <= 201503) {
        $rui_l_roumu = $rui_l_roumu + 229464;
    }
    $rui_lh_roumu        = $rui_l_roumu - $rui_s_roumu_sagaku - $rui_b_roumu_sagaku;
    $rui_lh_roumu_sagaku = $rui_lh_roumu;
    $rui_l_roumu         = $rui_l_roumu - $rui_s_roumu_sagaku;               // �����ϫ̳����˥���ϫ̳����ޥ��ʥ�
    $rui_lh_roumu        = number_format(($rui_lh_roumu / $tani), $keta);
    $rui_l_roumu         = number_format(($rui_l_roumu / $tani), $keta);
}

/********** ����(��¤����) **********/
    ///// ����
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���¤����'", $yyyymm);
if (getUniResult($query, $s_expense) < 1) {
    $s_expense        = 0;    // ��������
    $s_expense_sagaku = 0;
} else {
    $s_expense_sagaku = $s_expense;
    $s_expense        = number_format(($s_expense / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�Х������¤����'", $yyyymm);
if (getUniResult($query, $b_expense) < 1) {
    $b_expense        = 0;    // ��������
    $b_expense_sagaku = 0;
} else {
    $b_expense_sagaku = $b_expense;
    $b_expense        = number_format(($b_expense / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥���¤����'", $yyyymm);
if (getUniResult($query, $l_expense) < 1) {
    $l_expense         = 0 - $s_expense_sagaku;     // ��������
    $lh_expense        = 0;
    $lh_expense_sagaku = 0;
} else {
    // 2012/02/08 �ɲ� 2012ǯ1���� ��̳�������ʿ�в����ɸ�����Ĵ��
    if ($yyyymm == 201201) {
        $l_expense +=1156130;
    }
    // 2012/03/05 �ɲ� 2012ǯ1���� ��̳�������ʿ�в����ɸ�����Ĵ�� �ᤷ
    if ($yyyymm == 201202) {
        $l_expense -=1156130;
    }
    $lh_expense        = $l_expense - $s_expense_sagaku - $b_expense_sagaku;
    $lh_expense_sagaku = $lh_expense;
    $l_expense         = $l_expense - $s_expense_sagaku;               // �������¤������˥�����¤������ޥ��ʥ�
    $lh_expense        = number_format(($lh_expense / $tani), $keta);
    $l_expense         = number_format(($l_expense / $tani), $keta);
}
    ///// ����
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���¤����'", $p1_ym);
if (getUniResult($query, $p1_s_expense) < 1) {
    $p1_s_expense        = 0;    // ��������
    $p1_s_expense_sagaku = 0;
} else {
    $p1_s_expense_sagaku = $p1_s_expense;
    $p1_s_expense        = number_format(($p1_s_expense / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�Х������¤����'", $p1_ym);
if (getUniResult($query, $p1_b_expense) < 1) {
    $p1_b_expense        = 0;    // ��������
    $p1_b_expense_sagaku = 0;
} else {
    $p1_b_expense_sagaku = $p1_b_expense;
    $p1_b_expense        = number_format(($p1_b_expense / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥���¤����'", $p1_ym);
if (getUniResult($query, $p1_l_expense) < 1) {
    $p1_l_expense         = 0 - $p1_s_expense_sagaku;     // ��������
    $p1_lh_expense        = 0;
    $p1_lh_expense_sagaku = 0;
} else {
    // 2012/02/08 �ɲ� 2012ǯ1���� ��̳�������ʿ�в����ɸ�����Ĵ��
    if ($p1_ym == 201201) {
        $p1_l_expense +=1156130;
    }
    // 2012/03/05 �ɲ� 2012ǯ1���� ��̳�������ʿ�в����ɸ�����Ĵ�� �ᤷ
    if ($p1_ym == 201202) {
        $p1_l_expense -=1156130;
    }
    $p1_lh_expense        = $p1_l_expense - $p1_s_expense_sagaku - $p1_b_expense_sagaku;
    $p1_lh_expense_sagaku = $p1_lh_expense;
    $p1_l_expense         = $p1_l_expense - $p1_s_expense_sagaku;               // �������¤������˥�����¤������ޥ��ʥ�
    $p1_lh_expense        = number_format(($p1_lh_expense / $tani), $keta);
    $p1_l_expense         = number_format(($p1_l_expense / $tani), $keta);
}
    ///// ������
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���¤����'", $p2_ym);
if (getUniResult($query, $p2_s_expense) < 1) {
    $p2_s_expense        = 0;    // ��������
    $p2_s_expense_sagaku = 0;
} else {
    $p2_s_expense_sagaku = $p2_s_expense;
    $p2_s_expense        = number_format(($p2_s_expense / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�Х������¤����'", $p2_ym);
if (getUniResult($query, $p2_b_expense) < 1) {
    $p2_b_expense        = 0;    // ��������
    $p2_b_expense_sagaku = 0;
} else {
    $p2_b_expense_sagaku = $p2_b_expense;
    $p2_b_expense        = number_format(($p2_b_expense / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥���¤����'", $p2_ym);
if (getUniResult($query, $p2_l_expense) < 1) {
    $p2_l_expense         = 0 - $p2_s_expense_sagaku;     // ��������
    $p2_lh_expense        = 0;
    $p2_lh_expense_sagaku = 0;
} else {
    // 2012/02/08 �ɲ� 2012ǯ1���� ��̳�������ʿ�в����ɸ�����Ĵ��
    if ($p2_ym == 201201) {
        $p2_l_expense +=1156130;
    }
    // 2012/03/05 �ɲ� 2012ǯ1���� ��̳�������ʿ�в����ɸ�����Ĵ�� �ᤷ
    if ($p2_ym == 201202) {
        $p2_l_expense -=1156130;
    }
    $p2_lh_expense        = $p2_l_expense - $p2_s_expense_sagaku - $p2_b_expense_sagaku;
    $p2_lh_expense_sagaku = $p2_lh_expense;
    $p2_l_expense         = $p2_l_expense - $p2_s_expense_sagaku;               // �������¤������˥�����¤������ޥ��ʥ�
    $p2_lh_expense        = number_format(($p2_lh_expense / $tani), $keta);
    $p2_l_expense         = number_format(($p2_l_expense / $tani), $keta);
}
    ///// �����߷�
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���¤����'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_s_expense) < 1) {
    $rui_s_expense        = 0;    // ��������
    $rui_s_expense_sagaku = 0;
} else {
    $rui_s_expense_sagaku = $rui_s_expense;
    $rui_s_expense        = number_format(($rui_s_expense / $tani), $keta);
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='�Х������¤����'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_b_expense) < 1) {
    $rui_b_expense        = 0;    // ��������
    $rui_b_expense_sagaku = 0;
} else {
    $rui_b_expense_sagaku = $rui_b_expense;
    $rui_b_expense        = number_format(($rui_b_expense / $tani), $keta);
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='��˥���¤����'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_l_expense) < 1) {
    $rui_l_expense         = 0 - $rui_s_expense_sagaku;   // ��������
    $rui_lh_expense        = 0;
    $rui_lh_expense_sagaku = 0;
} else {
    // 2012/02/08 �ɲ� 2012ǯ1���� ��̳�������ʿ�в����ɸ�����Ĵ��
    if ($yyyymm >= 201201 && $yyyymm <= 201203) {
        $rui_l_expense +=1156130;
    }
    // 2012/03/05 �ɲ� 2012ǯ1���� ��̳�������ʿ�в����ɸ�����Ĵ�� �ᤷ
    if ($yyyymm >= 201202 && $yyyymm <= 201203) {
        $rui_l_expense -=1156130;
    }
    $rui_lh_expense        = $rui_l_expense - $rui_s_expense_sagaku - $rui_b_expense_sagaku;
    $rui_lh_expense_sagaku = $rui_lh_expense;
    $rui_l_expense         = $rui_l_expense - $rui_s_expense_sagaku;               // �������¤������˥�����¤������ޥ��ʥ�
    $rui_lh_expense        = number_format(($rui_lh_expense / $tani), $keta);
    $rui_l_expense         = number_format(($rui_l_expense / $tani), $keta);
}

/********** ���������ų���ê���� **********/
    ///// �������
$p2_s_endinv = 0;
$p1_s_endinv = 0;
$s_endinv    = 0;
    ///// ����
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�Х�������ê����'", $yyyymm);
if (getUniResult($query, $b_endinv) < 1) {
    $b_endinv        = 0;                      // ��������
    $b_endinv_sagaku = 0;
} else {
    $b_endinv_sagaku = $b_endinv;
    $b_endinv        = ($b_endinv * (-1));     // ���ȿž
    $b_endinv        = number_format(($b_endinv / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥�����ê����'", $yyyymm);
if (getUniResult($query, $l_endinv) < 1) {
    $l_endinv         = 0;                     // ��������
    $lh_endinv        = 0;
    $lh_endinv_sagaku = 0;
} else {
    $lh_endinv        = $l_endinv - $s_endinv - $b_endinv_sagaku;
    $lh_endinv        = ($lh_endinv * (-1));
    $l_endinv         = ($l_endinv * (-1));    // ���ȿž
    $lh_endinv_sagaku = $lh_endinv;
    $lh_endinv        = number_format(($lh_endinv / $tani), $keta);
    $l_endinv         = number_format(($l_endinv / $tani), $keta);
}
    ///// ����
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�Х�������ê����'", $p1_ym);
if (getUniResult($query, $p1_b_endinv) < 1) {
    $p1_b_endinv        = 0;                         // ��������
    $p1_b_endinv_sagaku = 0;
} else {
    $p1_b_endinv_sagaku = $p1_b_endinv;
    $p1_b_endinv        = ($p1_b_endinv * (-1));     // ���ȿž
    $p1_b_endinv        = number_format(($p1_b_endinv / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥�����ê����'", $p1_ym);
if (getUniResult($query, $p1_l_endinv) < 1) {
    $p1_l_endinv         = 0;                        // ��������
    $p1_lh_endinv        = 0;
    $p1_lh_endinv_sagaku = 0;
} else {
    $p1_lh_endinv        = $p1_l_endinv - $p1_s_endinv - $p1_b_endinv_sagaku;
    $p1_lh_endinv        = ($p1_lh_endinv * (-1));
    $p1_l_endinv         = ($p1_l_endinv * (-1));    // ���ȿž
    $p1_lh_endinv_sagaku = $p1_lh_endinv;
    $p1_lh_endinv        = number_format(($p1_lh_endinv / $tani), $keta);
    $p1_l_endinv         = number_format(($p1_l_endinv / $tani), $keta);
}
    ///// ������
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�Х�������ê����'", $p2_ym);
if (getUniResult($query, $p2_b_endinv) < 1) {
    $p2_b_endinv        = 0;                         // ��������
    $p2_b_endinv_sagaku = 0;
} else {
    $p2_b_endinv_sagaku = $p2_b_endinv;
    $p2_b_endinv        = ($p2_b_endinv * (-1));     // ���ȿž
    $p2_b_endinv        = number_format(($p2_b_endinv / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥�����ê����'", $p2_ym);
if (getUniResult($query, $p2_l_endinv) < 1) {
    $p2_l_endinv         = 0;                        // ��������
    $p2_lh_endinv        = 0;
    $p2_lh_endinv_sagaku = 0;
} else {
    $p2_lh_endinv        = $p2_l_endinv - $p2_s_endinv - $p2_b_endinv_sagaku;
    $p2_lh_endinv        = ($p2_lh_endinv * (-1));
    $p2_l_endinv         = ($p2_l_endinv * (-1));    // ���ȿž
    $p2_lh_endinv_sagaku = $p2_lh_endinv;
    $p2_lh_endinv        = number_format(($p2_lh_endinv / $tani), $keta);
    $p2_l_endinv         = number_format(($p2_l_endinv / $tani), $keta);
}
    ///// �����߷�
    ///// ����ê������߷פ������Ʊ��

/********** ��帶�� **********/
    ///// ����
    ///// �������
    $s_urigen        = $s_invent + $s_metarial_sagaku + $s_roumu_sagaku + $s_expense_sagaku + $s_endinv;
    $s_urigen_sagaku = $s_urigen;
    $s_urigen        = number_format(($s_urigen / $tani), $keta);
    ///// �Х����
    $b_urigen        = $b_invent_sagaku + $b_metarial_sagaku + $b_roumu_sagaku + $b_expense_sagaku - $b_endinv_sagaku;
    $b_urigen_sagaku = $b_urigen;
    $b_urigen        = number_format(($b_urigen / $tani), $keta);
    ///// CL
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥���帶��'", $yyyymm);
if (getUniResult($query, $l_urigen) < 1) {
    $l_urigen         = 0 - $s_urigen_sagaku;     // ��������
    $lh_urigen        = 0;                        // ��������
    $lh_urigen_sagaku = 0;                        // ��������
} else {
    if ($yyyymm == 200912) {
        $l_urigen = $l_urigen + 182279;
    }
    if ($yyyymm >= 201001) {
        $l_urigen = $l_urigen + $l_kyu_kin;     // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
        //$l_urigen = $l_urigen + 151313;     // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
    }
    // 2012/02/08 �ɲ� 2012ǯ1���� ��̳�������ʿ�в����ɸ�����Ĵ��
    if ($yyyymm == 201201) {
        $l_urigen +=1156130;
    }
    // 2012/03/05 �ɲ� 2012ǯ1���� ��̳�������ʿ�в����ɸ�����Ĵ�� �ᤷ
    if ($yyyymm == 201202) {
        $l_urigen -=1156130;
    }
    if ($yyyymm == 201408) {
        $l_urigen +=229464;
    }
    $lh_urigen        = $l_urigen - $s_urigen_sagaku - $b_urigen_sagaku;
    $lh_urigen_sagaku = $lh_urigen;
    $l_urigen         = $l_urigen - $s_urigen_sagaku;        // �������帶�����˥�����帶�����ޥ��ʥ�
    $lh_urigen        = number_format(($lh_urigen / $tani), $keta);
    $l_urigen         = number_format(($l_urigen / $tani), $keta);
}

    ///// ����
    ///// �������
    $p1_s_urigen        = $p1_s_invent + $p1_s_metarial_sagaku + $p1_s_roumu_sagaku + $p1_s_expense_sagaku + $p1_s_endinv;
    $p1_s_urigen_sagaku = $p1_s_urigen;
    $p1_s_urigen        = number_format(($p1_s_urigen / $tani), $keta);
    ///// �Х����
    $p1_b_urigen        = $p1_b_invent_sagaku + $p1_b_metarial_sagaku + $p1_b_roumu_sagaku + $p1_b_expense_sagaku - $p1_b_endinv_sagaku;
    $p1_b_urigen_sagaku = $p1_b_urigen;
    $p1_b_urigen        = number_format(($p1_b_urigen / $tani), $keta);
    ///// CL
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥���帶��'", $p1_ym);
if (getUniResult($query, $p1_l_urigen) < 1) {
    $p1_l_urigen         = 0 - $p1_s_urigen_sagaku;     // ��������
    $p1_lh_urigen        = 0;                           // ��������
    $p1_lh_urigen_sagaku = 0;                           // ��������
} else {
    if ($p1_ym == 200912) {
        $p1_l_urigen = $p1_l_urigen + 182279;
    }
    if ($p1_ym >= 201001) {
        $p1_l_urigen = $p1_l_urigen + $p1_l_kyu_kin;   // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
        //$p1_l_urigen = $p1_l_urigen + 151313;   // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
    }
    // 2012/02/08 �ɲ� 2012ǯ1���� ��̳�������ʿ�в����ɸ�����Ĵ��
    if ($p1_ym == 201201) {
        $p1_l_urigen +=1156130;
    }
    // 2012/03/05 �ɲ� 2012ǯ1���� ��̳�������ʿ�в����ɸ�����Ĵ�� �ᤷ
    if ($p1_ym == 201202) {
        $p1_l_urigen -=1156130;
    }
    if ($p1_ym == 201408) {
        $p1_l_urigen +=229464;
    }
    $p1_lh_urigen        = $p1_l_urigen - $p1_s_urigen_sagaku - $p1_b_urigen_sagaku;
    $p1_lh_urigen_sagaku = $p1_lh_urigen;
    $p1_l_urigen         = $p1_l_urigen - $p1_s_urigen_sagaku;        // �������帶�����˥�����帶�����ޥ��ʥ�
    $p1_lh_urigen        = number_format(($p1_lh_urigen / $tani), $keta);
    $p1_l_urigen         = number_format(($p1_l_urigen / $tani), $keta);
}

    ///// ������
    ///// �������
    $p2_s_urigen        = $p2_s_invent + $p2_s_metarial_sagaku + $p2_s_roumu_sagaku + $p2_s_expense_sagaku + $p2_s_endinv;
    $p2_s_urigen_sagaku = $p2_s_urigen;
    $p2_s_urigen        = number_format(($p2_s_urigen / $tani), $keta);
    ///// �Х����
    $p2_b_urigen        = $p2_b_invent_sagaku + $p2_b_metarial_sagaku + $p2_b_roumu_sagaku + $p2_b_expense_sagaku - $p2_b_endinv_sagaku;
    $p2_b_urigen_sagaku = $p2_b_urigen;
    $p2_b_urigen        = number_format(($p2_b_urigen / $tani), $keta);
    ///// CL
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥���帶��'", $p2_ym);
if (getUniResult($query, $p2_l_urigen) < 1) {
    $p2_l_urigen         = 0 - $p2_s_urigen_sagaku;     // ��������
    $p2_lh_urigen        = 0;                           // ��������
    $p2_lh_urigen_sagaku = 0;                           // ��������
} else {
    if ($p2_ym == 200912) {
        $p2_l_urigen = $p2_l_urigen + 182279;
    }
    if ($p2_ym >= 201001) {
        $p2_l_urigen = $p2_l_urigen + $p2_l_kyu_kin;   // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
        //$p2_l_urigen = $p2_l_urigen + 151313;   // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
    }
    // 2012/02/08 �ɲ� 2012ǯ1���� ��̳�������ʿ�в����ɸ�����Ĵ��
    if ($p2_ym == 201201) {
        $p2_l_urigen +=1156130;
    }
    // 2012/03/05 �ɲ� 2012ǯ1���� ��̳�������ʿ�в����ɸ�����Ĵ�� �ᤷ
    if ($p2_ym == 201202) {
        $p2_l_urigen -=1156130;
    }
    if ($p2_ym == 201408) {
        $p2_l_urigen +=229464;
    }
    $p2_lh_urigen        = $p2_l_urigen - $p2_s_urigen_sagaku - $p2_b_urigen_sagaku;
    $p2_lh_urigen_sagaku = $p2_lh_urigen;
    $p2_l_urigen         = $p2_l_urigen - $p2_s_urigen_sagaku;        // �������帶�����˥�����帶�����ޥ��ʥ�
    $p2_lh_urigen        = number_format(($p2_lh_urigen / $tani), $keta);
    $p2_l_urigen = number_format(($p2_l_urigen / $tani), $keta);
}

    ///// �����߷�
    ///// �������
    $rui_s_urigen        = $rui_s_invent + $rui_s_metarial_sagaku + $rui_s_roumu_sagaku + $rui_s_expense_sagaku + $s_endinv;
    $rui_s_urigen_sagaku = $rui_s_urigen;
    $rui_s_urigen        = number_format(($rui_s_urigen / $tani), $keta);
    ///// �Х����
    $rui_b_urigen        = $rui_b_invent_sagaku + $rui_b_metarial_sagaku + $rui_b_roumu_sagaku + $rui_b_expense_sagaku - $b_endinv_sagaku;
    $rui_b_urigen_sagaku = $rui_b_urigen;
    $rui_b_urigen        = number_format(($rui_b_urigen / $tani), $keta);
    ///// CL
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='������帶��'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_all_urigen) < 1) {
    $rui_all_urigen = 0;                                // ��������
} else {
    $rui_all_urigen = number_format(($rui_all_urigen / $tani), $keta);
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='��˥���帶��'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_l_urigen) < 1) {
    $rui_l_urigen         = 0 - $rui_s_urigen_sagaku;   // ��������
    $rui_lh_urigen        = 0;                          // ��������
    $rui_lh_urigen_sagaku = 0;                          // ��������
} else {
    if ($yyyymm >= 200912 && $yyyymm <= 201003) {
        $rui_l_urigen = $rui_l_urigen + 182279;
    }
    if ($yyyymm >= 201001) {
        $rui_l_urigen = $rui_l_urigen + $rui_l_kyu_kin;     // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
        //$rui_l_urigen = $rui_l_urigen + 151313;     // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
    }
    // 2012/02/08 �ɲ� 2012ǯ1���� ��̳�������ʿ�в����ɸ�����Ĵ��
    if ($yyyymm >= 201201 && $yyyymm <= 201203) {
        $rui_l_urigen +=1156130;
    }
    // 2012/03/05 �ɲ� 2012ǯ1���� ��̳�������ʿ�в����ɸ�����Ĵ�� �ᤷ
    if ($yyyymm >= 201202 && $yyyymm <= 201203) {
        $rui_l_urigen -=1156130;
    }
    if ($yyyymm >= 201408 && $yyyymm <= 201503) {
        $rui_l_urigen = $rui_l_urigen + 229464;
    }
    $rui_lh_urigen        = $rui_l_urigen - $rui_s_urigen_sagaku - $rui_b_urigen_sagaku;
    $rui_lh_urigen_sagaku = $rui_lh_urigen;
    $rui_l_urigen         = $rui_l_urigen - $rui_s_urigen_sagaku;        // �������帶�����˥�����帶�����ޥ��ʥ�
    $rui_lh_urigen        = number_format(($rui_lh_urigen / $tani), $keta);
    $rui_l_urigen         = number_format(($rui_l_urigen / $tani), $keta);
}

/********** ��������� **********/
    ///// �������
$p2_s_gross_profit         = $p2_s_uri_sagaku - $p2_s_urigen_sagaku;
$p2_s_gross_profit_sagaku  = $p2_s_gross_profit;
$p2_s_gross_profit         = number_format(($p2_s_gross_profit / $tani), $keta);

$p1_s_gross_profit         = $p1_s_uri_sagaku - $p1_s_urigen_sagaku;
$p1_s_gross_profit_sagaku  = $p1_s_gross_profit;
$p1_s_gross_profit         = number_format(($p1_s_gross_profit / $tani), $keta);

$s_gross_profit            = $s_uri_sagaku - $s_urigen_sagaku;
$s_gross_profit_sagaku     = $s_gross_profit;
$s_gross_profit            = number_format(($s_gross_profit / $tani), $keta);

$rui_s_gross_profit        = $rui_s_uri_sagaku - $rui_s_urigen_sagaku;
$rui_s_gross_profit_sagaku = $rui_s_gross_profit;
$rui_s_gross_profit        = number_format(($rui_s_gross_profit / $tani), $keta);
    ///// �Х����
$p2_b_gross_profit         = $p2_b_uri_sagaku - $p2_b_urigen_sagaku;
$p2_b_gross_profit_sagaku  = $p2_b_gross_profit;
$p2_b_gross_profit         = number_format(($p2_b_gross_profit / $tani), $keta);

$p1_b_gross_profit         = $p1_b_uri_sagaku - $p1_b_urigen_sagaku;
$p1_b_gross_profit_sagaku  = $p1_b_gross_profit;
$p1_b_gross_profit         = number_format(($p1_b_gross_profit / $tani), $keta);

$b_gross_profit            = $b_uri_sagaku - $b_urigen_sagaku;
$b_gross_profit_sagaku     = $b_gross_profit;
$b_gross_profit            = number_format(($b_gross_profit / $tani), $keta);

$rui_b_gross_profit        = $rui_b_uri_sagaku - $rui_b_urigen_sagaku;
$rui_b_gross_profit_sagaku = $rui_b_gross_profit;
$rui_b_gross_profit        = number_format(($rui_b_gross_profit / $tani), $keta);

    ///// ����
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥�������'", $yyyymm);
if (getUniResult($query, $l_gross_profit) < 1) {
    $l_gross_profit         = 0 - $s_gross_profit_sagaku;     // ��������
    $lh_gross_profit        = 0;                              // ��������
    $lh_gross_profit_sagaku = 0;                              // ��������
} else {
    if ($yyyymm == 200906) {
        $l_gross_profit = $l_gross_profit - 3100900;
    } elseif ($yyyymm == 200905) {
        $l_gross_profit = $l_gross_profit + 1550450;
    } elseif ($yyyymm == 200904) {
        $l_gross_profit = $l_gross_profit + 1550450;
    }
    if ($yyyymm == 200912) {
        $l_gross_profit = $l_gross_profit - 182279;
    }
    if ($yyyymm >= 201001) {
        $l_gross_profit = $l_gross_profit - $l_kyu_kin;     // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
        //$l_gross_profit = $l_gross_profit - 151313;     // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
    }
    if ($yyyymm == 201004) {
        $l_gross_profit = $l_gross_profit - 255240;
    }
    // 2012/02/08 �ɲ� 2012ǯ1���� ��̳�������ʿ�в����ɸ�����Ĵ��
    if ($yyyymm == 201201) {
        $l_gross_profit -=1156130;
    }
    // 2012/03/05 �ɲ� 2012ǯ1���� ��̳�������ʿ�в����ɸ�����Ĵ�� �ᤷ
    if ($yyyymm == 201202) {
        $l_gross_profit +=1156130;
    }
    if ($yyyymm == 201408) {
        $l_gross_profit -=229464;
    }
    $lh_gross_profit        = $l_gross_profit - $s_gross_profit_sagaku - $b_gross_profit_sagaku;
    $lh_gross_profit_sagaku = $lh_gross_profit;
    $l_gross_profit         = $l_gross_profit - $s_gross_profit_sagaku;     // �������������פ��˥�����������פ��ޥ��ʥ�
    $lh_gross_profit        = number_format(($lh_gross_profit / $tani), $keta);
    $l_gross_profit         = number_format(($l_gross_profit / $tani), $keta);
}
    ///// ����
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥�������'", $p1_ym);
if (getUniResult($query, $p1_l_gross_profit) < 1) {
    $p1_l_gross_profit         = 0 - $p1_s_gross_profit_sagaku;     // ��������
    $p1_lh_gross_profit        = 0;                                 // ��������
    $p1_lh_gross_profit_sagaku = 0;                                 // ��������
} else {
    if ($p1_ym == 200906) {
        $p1_l_gross_profit = $p1_l_gross_profit - 3100900;
    } elseif ($p1_ym == 200905) {
        $p1_l_gross_profit = $p1_l_gross_profit + 1550450;
    } elseif ($p1_ym == 200904) {
        $p1_l_gross_profit = $p1_l_gross_profit + 1550450;
    }
    if ($p1_ym == 200912) {
        $p1_l_gross_profit = $p1_l_gross_profit - 182279;
    }
    if ($p1_ym >= 201001) {
        $p1_l_gross_profit = $p1_l_gross_profit - $p1_l_kyu_kin;   // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
        //$p1_l_gross_profit = $p1_l_gross_profit - 151313;   // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
    }
    if ($p1_ym == 201004) {
        $p1_l_gross_profit = $p1_l_gross_profit - 255240;
    }
    // 2012/02/08 �ɲ� 2012ǯ1���� ��̳�������ʿ�в����ɸ�����Ĵ��
    if ($p1_ym == 201201) {
        $p1_l_gross_profit -=1156130;
    }
    // 2012/03/05 �ɲ� 2012ǯ1���� ��̳�������ʿ�в����ɸ�����Ĵ�� �ᤷ
    if ($p1_ym == 201202) {
        $p1_l_gross_profit +=1156130;
    }
    if ($p1_ym == 201408) {
        $p1_l_gross_profit -=229464;
    }
    $p1_lh_gross_profit        = $p1_l_gross_profit - $p1_s_gross_profit_sagaku - $p1_b_gross_profit_sagaku;
    $p1_lh_gross_profit_sagaku = $p1_lh_gross_profit;
    $p1_l_gross_profit         = $p1_l_gross_profit - $p1_s_gross_profit_sagaku;     // �������������פ��˥�����������פ��ޥ��ʥ�
    $p1_lh_gross_profit        = number_format(($p1_lh_gross_profit / $tani), $keta);
    $p1_l_gross_profit         = number_format(($p1_l_gross_profit / $tani), $keta);
}
    ///// ������
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥�������'", $p2_ym);
if (getUniResult($query, $p2_l_gross_profit) < 1) {
    $p2_l_gross_profit         = 0 - $p2_s_gross_profit_sagaku;     // ��������
    $p2_lh_gross_profit        = 0;                                 // ��������
    $p2_lh_gross_profit_sagaku = 0;                                 // ��������
} else {
    if ($p2_ym == 200906) {
        $p2_l_gross_profit = $p2_l_gross_profit - 3100900;
    } elseif ($p2_ym == 200905) {
        $p2_l_gross_profit = $p2_l_gross_profit + 1550450;
    } elseif ($p2_ym == 200904) {
        $p2_l_gross_profit = $p2_l_gross_profit + 1550450;
    }
    if ($p2_ym == 200912) {
        $p2_l_gross_profit = $p2_l_gross_profit - 182279;
    }
    if ($p2_ym >= 201001) {
        $p2_l_gross_profit = $p2_l_gross_profit - $p2_l_kyu_kin;   // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
        //$p2_l_gross_profit = $p2_l_gross_profit - 151313;   // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
    }
    if ($p2_ym == 201004) {
        $p2_l_gross_profit = $p2_l_gross_profit - 255240;
    }
    // 2012/02/08 �ɲ� 2012ǯ1���� ��̳�������ʿ�в����ɸ�����Ĵ��
    if ($p2_ym == 201201) {
        $p2_l_gross_profit -=1156130;
    }
    // 2012/03/05 �ɲ� 2012ǯ1���� ��̳�������ʿ�в����ɸ�����Ĵ�� �ᤷ
    if ($p2_ym == 201202) {
        $p2_l_gross_profit +=1156130;
    }
    if ($p2_ym == 201408) {
        $p2_l_gross_profit -=229464;
    }
    $p2_lh_gross_profit        = $p2_l_gross_profit - $p2_s_gross_profit_sagaku - $p2_b_gross_profit_sagaku;
    $p2_lh_gross_profit_sagaku = $p2_lh_gross_profit;
    $p2_l_gross_profit         = $p2_l_gross_profit - $p2_s_gross_profit_sagaku;     // �������������פ��˥�����������פ��ޥ��ʥ�
    $p2_lh_gross_profit        = number_format(($p2_lh_gross_profit / $tani), $keta);
    $p2_l_gross_profit         = number_format(($p2_l_gross_profit / $tani), $keta);
}
    ///// �����߷�
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='��˥�������'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_l_gross_profit) < 1) {
    $rui_l_gross_profit         = 0 - $rui_s_gross_profit_sagaku;   // ��������
    $rui_lh_gross_profit        = 0;                                // ��������
    $rui_lh_gross_profit_sagaku = 0;                                // ��������
} else {
    if ($yyyymm == 200905) {
        $rui_l_gross_profit = $rui_l_gross_profit + 3100900;
    } elseif ($yyyymm == 200904) {
        $rui_l_gross_profit = $rui_l_gross_profit + 1550450;
    }
    if ($yyyymm >= 200912 && $yyyymm <= 201003) {
        $rui_l_gross_profit = $rui_l_gross_profit - 182279;
    }
    if ($yyyymm >= 201001) {
        $rui_l_gross_profit = $rui_l_gross_profit - $rui_l_kyu_kin;
        //$rui_l_gross_profit = $rui_l_gross_profit - 151313;
    }
    if ($yyyymm >= 201004 && $yyyymm <= 201103) {
        $rui_l_gross_profit = $rui_l_gross_profit - 255240;
    }
    // 2012/02/08 �ɲ� 2012ǯ1���� ��̳�������ʿ�в����ɸ�����Ĵ��
    if ($yyyymm >= 201201 && $yyyymm <= 201203) {
        $rui_l_gross_profit -=1156130;
    }
    // 2012/03/05 �ɲ� 2012ǯ1���� ��̳�������ʿ�в����ɸ�����Ĵ�� �ᤷ
    if ($yyyymm >= 201202 && $yyyymm <= 201203) {
        $rui_l_gross_profit +=1156130;
    }
    if ($yyyymm >= 201408 && $yyyymm <= 201503) {
        $rui_l_gross_profit = $rui_l_gross_profit - 229464;
    }
    $rui_lh_gross_profit        = $rui_l_gross_profit - $rui_s_gross_profit_sagaku - $rui_b_gross_profit_sagaku;
    $rui_lh_gross_profit_sagaku = $rui_lh_gross_profit;
    $rui_l_gross_profit         = $rui_l_gross_profit - $rui_s_gross_profit_sagaku;     // �������������פ��˥�����������פ��ޥ��ʥ�
    $rui_lh_gross_profit        = number_format(($rui_lh_gross_profit / $tani), $keta);
    $rui_l_gross_profit         = number_format(($rui_l_gross_profit / $tani), $keta);
}

/********** �δ���οͷ��� **********/
    ///// ����
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��ͷ���'", $yyyymm);
if (getUniResult($query, $s_han_jin) < 1) {
    $s_han_jin        = 0;    // ��������
    $s_han_jin_sagaku = 0;
} else {
    $s_han_jin_sagaku = $s_han_jin;
    $s_han_jin        = number_format(($s_han_jin / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�Х����ͷ���'", $yyyymm);
if (getUniResult($query, $b_han_jin) < 1) {
    $b_han_jin        = 0;    // ��������
    $b_han_jin_sagaku = 0;
} else {
    $b_han_jin_sagaku = $b_han_jin;
    $b_han_jin        = number_format(($b_han_jin / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥����ɼҰ���ʬ��Ϳ'", $yyyymm);
if (getUniResult($query, $l_allo_kin) < 1) {
    $l_allo_kin       = 0;    // ��������
} else {
    //$c_han_jin = number_format(($c_han_jin / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥��ͷ���'", $yyyymm);
if (getUniResult($query, $l_han_jin) < 1) {
    $l_han_jin         = 0 - $s_han_jin_sagaku;     // ��������
    $lh_han_jin        = 0;                         // ��������
    $lh_han_jin_sagaku = 0;                         // ��������
} else {
    $l_han_jin         = $l_han_jin - $l_allo_kin;
    $lh_han_jin        = $l_han_jin - $s_han_jin_sagaku - $b_han_jin_sagaku;
    $lh_han_jin_sagaku = $lh_han_jin;
    $l_han_jin         = $l_han_jin - $s_han_jin_sagaku;     // ������ͷ�����˥��οͷ�����ޥ��ʥ�
    $lh_han_jin        = number_format(($lh_han_jin / $tani), $keta);
    $l_han_jin         = number_format(($l_han_jin / $tani), $keta);
}
    ///// ����
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��ͷ���'", $p1_ym);
if (getUniResult($query, $p1_s_han_jin) < 1) {
    $p1_s_han_jin        = 0;    // ��������
    $p1_s_han_jin_sagaku = 0;
} else {
    $p1_s_han_jin_sagaku = $p1_s_han_jin;
    $p1_s_han_jin        = number_format(($p1_s_han_jin / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�Х����ͷ���'", $p1_ym);
if (getUniResult($query, $p1_b_han_jin) < 1) {
    $p1_b_han_jin        = 0;    // ��������
    $p1_b_han_jin_sagaku = 0;
} else {
    $p1_b_han_jin_sagaku = $p1_b_han_jin;
    $p1_b_han_jin        = number_format(($p1_b_han_jin / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥����ɼҰ���ʬ��Ϳ'", $p1_ym);
if (getUniResult($query, $p1_l_allo_kin) < 1) {
    $p1_l_allo_kin       = 0;    // ��������
} else {
    //$c_han_jin = number_format(($c_han_jin / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥��ͷ���'", $p1_ym);
if (getUniResult($query, $p1_l_han_jin) < 1) {
    $p1_l_han_jin         = 0 - $p1_s_han_jin_sagaku;     // ��������
    $p1_lh_han_jin        = 0;                            // ��������
    $p1_lh_han_jin_sagaku = 0;                            // ��������
} else {
    $p1_l_han_jin         = $p1_l_han_jin - $p1_l_allo_kin;
    $p1_lh_han_jin        = $p1_l_han_jin - $p1_s_han_jin_sagaku - $p1_b_han_jin_sagaku;
    $p1_lh_han_jin_sagaku = $p1_lh_han_jin;
    $p1_l_han_jin         = $p1_l_han_jin - $p1_s_han_jin_sagaku;     // ������ͷ�����˥��οͷ�����ޥ��ʥ�
    $p1_lh_han_jin        = number_format(($p1_lh_han_jin / $tani), $keta);
    $p1_l_han_jin         = number_format(($p1_l_han_jin / $tani), $keta);
}
    ///// ������
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��ͷ���'", $p2_ym);
if (getUniResult($query, $p2_s_han_jin) < 1) {
    $p2_s_han_jin        = 0;    // ��������
    $p2_s_han_jin_sagaku = 0;
} else {
    $p2_s_han_jin_sagaku = $p2_s_han_jin;
    $p2_s_han_jin        = number_format(($p2_s_han_jin / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�Х����ͷ���'", $p2_ym);
if (getUniResult($query, $p2_b_han_jin) < 1) {
    $p2_b_han_jin        = 0;    // ��������
    $p2_b_han_jin_sagaku = 0;
} else {
    $p2_b_han_jin_sagaku = $p2_b_han_jin;
    $p2_b_han_jin        = number_format(($p2_b_han_jin / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥����ɼҰ���ʬ��Ϳ'", $p2_ym);
if (getUniResult($query, $p2_l_allo_kin) < 1) {
    $p2_l_allo_kin       = 0;    // ��������
} else {
    //$c_han_jin = number_format(($c_han_jin / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥��ͷ���'", $p2_ym);
if (getUniResult($query, $p2_l_han_jin) < 1) {
    $p2_l_han_jin         = 0 - $p2_s_han_jin_sagaku;     // ��������
    $p2_lh_han_jin        = 0;                            // ��������
    $p2_lh_han_jin_sagaku = 0;                            // ��������
} else {
    $p2_l_han_jin         = $p2_l_han_jin - $p2_l_allo_kin;
    $p2_lh_han_jin        = $p2_l_han_jin - $p2_s_han_jin_sagaku - $p2_b_han_jin_sagaku;
    $p2_lh_han_jin_sagaku = $p2_lh_han_jin;
    $p2_l_han_jin         = $p2_l_han_jin - $p2_s_han_jin_sagaku;     // ������ͷ�����˥��οͷ�����ޥ��ʥ�
    $p2_lh_han_jin        = number_format(($p2_lh_han_jin / $tani), $keta);
    $p2_l_han_jin         = number_format(($p2_l_han_jin / $tani), $keta);
}
    ///// �����߷�
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='��ͷ���'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_s_han_jin) < 1) {
    $rui_s_han_jin        = 0;    // ��������
    $rui_s_han_jin_sagaku = 0;
} else {
    $rui_s_han_jin_sagaku = $rui_s_han_jin;
    $rui_s_han_jin        = number_format(($rui_s_han_jin / $tani), $keta);
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='�Х����ͷ���'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_b_han_jin) < 1) {
    $rui_b_han_jin        = 0;    // ��������
    $rui_b_han_jin_sagaku = 0;
} else {
    $rui_b_han_jin_sagaku = $rui_b_han_jin;
    $rui_b_han_jin        = number_format(($rui_b_han_jin / $tani), $keta);
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='��˥����ɼҰ���ʬ��Ϳ'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_l_allo_kin) < 1) {
    $rui_l_allo_kin       = 0;    // ��������
} else {
    //$c_han_jin = number_format(($c_han_jin / $tani), $keta);
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='��˥��ͷ���'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_l_han_jin) < 1) {
    $rui_l_han_jin         = 0 - $rui_s_han_jin_sagaku;   // ��������
    $rui_lh_han_jin        = 0;                           // ��������
    $rui_lh_han_jin_sagaku = 0;                           // ��������
} else {
    $rui_l_han_jin         = $rui_l_han_jin - $rui_l_allo_kin;
    $rui_lh_han_jin        = $rui_l_han_jin - $rui_s_han_jin_sagaku - $rui_b_han_jin_sagaku;
    $rui_lh_han_jin_sagaku = $rui_lh_han_jin;
    $rui_l_han_jin         = $rui_l_han_jin - $rui_s_han_jin_sagaku;     // ������ͷ�����˥��οͷ�����ޥ��ʥ�
    $rui_lh_han_jin        = number_format(($rui_lh_han_jin / $tani), $keta);
    $rui_l_han_jin         = number_format(($rui_l_han_jin / $tani), $keta);
}

/********** �δ���η��� **********/
    ///// ����
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��δ������'", $yyyymm);
if (getUniResult($query, $s_han_kei) < 1) {
    $s_han_kei        = 0;    // ��������
    $s_han_kei_sagaku = 0;
} else {
    $s_han_kei_sagaku = $s_han_kei;
    $s_han_kei        = number_format(($s_han_kei / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�Х�����δ������'", $yyyymm);
if (getUniResult($query, $b_han_kei) < 1) {
    $b_han_kei        = 0;    // ��������
    $b_han_kei_sagaku = 0;
} else {
    $b_han_kei_sagaku = $b_han_kei;
    $b_han_kei        = number_format(($b_han_kei / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥�����'", $yyyymm);
if (getUniResult($query, $l_han_kei) < 1) {
    $l_han_kei         = 0 - $s_han_kei_sagaku;     // ��������
    $lh_han_kei        = 0;                         // ��������
    $lh_han_kei_sagaku = 0;                         // ��������
} else {
    $lh_han_kei        = $l_han_kei - $s_han_kei_sagaku - $b_han_kei_sagaku;
    $lh_han_kei_sagaku = $lh_han_kei;
    $l_han_kei         = $l_han_kei - $s_han_kei_sagaku;     // ������δ��������˥����δ��������ޥ��ʥ�
    $lh_han_kei        = number_format(($lh_han_kei / $tani), $keta);
    $l_han_kei         = number_format(($l_han_kei / $tani), $keta);
}
    ///// ����
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��δ������'", $p1_ym);
if (getUniResult($query, $p1_s_han_kei) < 1) {
    $p1_s_han_kei        = 0;    // ��������
    $p1_s_han_kei_sagaku = 0;
} else {
    $p1_s_han_kei_sagaku = $p1_s_han_kei;
    $p1_s_han_kei        = number_format(($p1_s_han_kei / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�Х�����δ������'", $p1_ym);
if (getUniResult($query, $p1_b_han_kei) < 1) {
    $p1_b_han_kei        = 0;    // ��������
    $p1_b_han_kei_sagaku = 0;
} else {
    $p1_b_han_kei_sagaku = $p1_b_han_kei;
    $p1_b_han_kei        = number_format(($p1_b_han_kei / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥�����'", $p1_ym);
if (getUniResult($query, $p1_l_han_kei) < 1) {
    $p1_l_han_kei         = 0 - $p1_s_han_kei_sagaku;     // ��������
    $p1_lh_han_kei        = 0;                            // ��������
    $p1_lh_han_kei_sagaku = 0;                            // ��������
} else {
    $p1_lh_han_kei        = $p1_l_han_kei - $p1_s_han_kei_sagaku - $p1_b_han_kei_sagaku;
    $p1_lh_han_kei_sagaku = $p1_lh_han_kei;
    $p1_l_han_kei         = $p1_l_han_kei - $p1_s_han_kei_sagaku;     // ������δ��������˥����δ��������ޥ��ʥ�
    $p1_lh_han_kei        = number_format(($p1_lh_han_kei / $tani), $keta);
    $p1_l_han_kei         = number_format(($p1_l_han_kei / $tani), $keta);
}
    ///// ������
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��δ������'", $p2_ym);
if (getUniResult($query, $p2_s_han_kei) < 1) {
    $p2_s_han_kei        = 0;    // ��������
    $p2_s_han_kei_sagaku = 0;
} else {
    $p2_s_han_kei_sagaku = $p2_s_han_kei;
    $p2_s_han_kei        = number_format(($p2_s_han_kei / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�Х�����δ������'", $p2_ym);
if (getUniResult($query, $p2_b_han_kei) < 1) {
    $p2_b_han_kei        = 0;    // ��������
    $p2_b_han_kei_sagaku = 0;
} else {
    $p2_b_han_kei_sagaku = $p2_b_han_kei;
    $p2_b_han_kei        = number_format(($p2_b_han_kei / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥�����'", $p2_ym);
if (getUniResult($query, $p2_l_han_kei) < 1) {
    $p2_l_han_kei         = 0 - $p2_s_han_kei_sagaku;     // ��������
    $p2_lh_han_kei        = 0;                            // ��������
    $p2_lh_han_kei_sagaku = 0;                            // ��������
} else {
    $p2_lh_han_kei        = $p2_l_han_kei - $p2_s_han_kei_sagaku - $p2_b_han_kei_sagaku;
    $p2_lh_han_kei_sagaku = $p2_lh_han_kei;
    $p2_l_han_kei         = $p2_l_han_kei - $p2_s_han_kei_sagaku;     // ������δ��������˥����δ��������ޥ��ʥ�
    $p2_lh_han_kei        = number_format(($p2_lh_han_kei / $tani), $keta);
    $p2_l_han_kei         = number_format(($p2_l_han_kei / $tani), $keta);
}
    ///// �����߷�
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='��δ������'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_s_han_kei) < 1) {
    $rui_s_han_kei        = 0;    // ��������
    $rui_s_han_kei_sagaku = 0;
} else {
    $rui_s_han_kei_sagaku = $rui_s_han_kei;
    $rui_s_han_kei        = number_format(($rui_s_han_kei / $tani), $keta);
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='�Х�����δ������'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_b_han_kei) < 1) {
    $rui_b_han_kei        = 0;    // ��������
    $rui_b_han_kei_sagaku = 0;
} else {
    $rui_b_han_kei_sagaku = $rui_b_han_kei;
    $rui_b_han_kei        = number_format(($rui_b_han_kei / $tani), $keta);
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='��˥�����'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_l_han_kei) < 1) {
    $rui_l_han_kei         = 0 - $rui_s_han_kei_sagaku;   // ��������
    $rui_lh_han_kei        = 0;                           // ��������
    $rui_lh_han_kei_sagaku = 0;                           // ��������
} else {
    $rui_lh_han_kei        = $rui_l_han_kei - $rui_s_han_kei_sagaku - $rui_b_han_kei_sagaku;
    $rui_lh_han_kei_sagaku = $rui_lh_han_kei;
    $rui_l_han_kei         = $rui_l_han_kei - $rui_s_han_kei_sagaku;     // ������δ��������˥����δ��������ޥ��ʥ�
    $rui_lh_han_kei        = number_format(($rui_lh_han_kei / $tani), $keta);
    $rui_l_han_kei         = number_format(($rui_l_han_kei / $tani), $keta);
}

/********** �δ���ι�� **********/
    ///// ����
    ///// �������
    $s_han_all        = $s_han_jin_sagaku + $s_han_kei_sagaku;
    $s_han_all_sagaku = $s_han_all;
    $s_han_all        = number_format(($s_han_all / $tani), $keta);
    ///// ����
    ///// �Х����
    $b_han_all        = $b_han_jin_sagaku + $b_han_kei_sagaku;
    $b_han_all_sagaku = $b_han_all;
    $b_han_all        = number_format(($b_han_all / $tani), $keta);
    ///// CL
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥��δ���'", $yyyymm);
if (getUniResult($query, $l_han_all) < 1) {
    $l_han_all         = 0 - $s_han_all_sagaku;     // ��������
    $lh_han_all        = 0;                         // ��������
    $lh_han_all_sagaku = 0;                         // ��������
} else {
    $l_han_all         = $l_han_all - $l_allo_kin;
    $lh_han_all        = $l_han_all - $s_han_all_sagaku - $b_han_all_sagaku;
    $lh_han_all_sagaku = $lh_han_all;
    $l_han_all         = $l_han_all - $s_han_all_sagaku;     // ������δ����פ��˥����δ����פ��ޥ��ʥ�
    $lh_han_all        = number_format(($lh_han_all / $tani), $keta);
    $l_han_all         = number_format(($l_han_all / $tani), $keta);
}

    ///// ����
    ///// �������
    $p1_s_han_all        = $p1_s_han_jin_sagaku + $p1_s_han_kei_sagaku;
    $p1_s_han_all_sagaku = $p1_s_han_all;
    $p1_s_han_all        = number_format(($p1_s_han_all / $tani), $keta);
    ///// �Х����
    $p1_b_han_all        = $p1_b_han_jin_sagaku + $p1_b_han_kei_sagaku;
    $p1_b_han_all_sagaku = $p1_b_han_all;
    $p1_b_han_all        = number_format(($p1_b_han_all / $tani), $keta);
    ///// CL
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥��δ���'", $p1_ym);
if (getUniResult($query, $p1_l_han_all) < 1) {
    $p1_l_han_all         = 0 - $p1_s_han_all_sagaku;     // ��������
    $p1_lh_han_all        = 0;                            // ��������
    $p1_lh_han_all_sagaku = 0;                            // ��������
} else {
    $p1_l_han_all         = $p1_l_han_all - $p1_l_allo_kin;
    $p1_lh_han_all        = $p1_l_han_all - $p1_s_han_all_sagaku - $p1_b_han_all_sagaku;
    $p1_lh_han_all_sagaku = $p1_lh_han_all;
    $p1_l_han_all         = $p1_l_han_all - $p1_s_han_all_sagaku;     // ������δ����פ��˥����δ����פ��ޥ��ʥ�
    $p1_lh_han_all        = number_format(($p1_lh_han_all / $tani), $keta);
    $p1_l_han_all         = number_format(($p1_l_han_all / $tani), $keta);
}

    ///// ������
    ///// �������
    $p2_s_han_all        = $p2_s_han_jin_sagaku + $p2_s_han_kei_sagaku;
    $p2_s_han_all_sagaku = $p2_s_han_all;
    $p2_s_han_all        = number_format(($p2_s_han_all / $tani), $keta);
    ///// �Х����
    $p2_b_han_all        = $p2_b_han_jin_sagaku + $p2_b_han_kei_sagaku;
    $p2_b_han_all_sagaku = $p2_b_han_all;
    $p2_b_han_all        = number_format(($p2_b_han_all / $tani), $keta);
    ///// CL
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥��δ���'", $p2_ym);
if (getUniResult($query, $p2_l_han_all) < 1) {
    $p2_l_han_all         = 0 - $p2_s_han_all_sagaku;     // ��������
    $p2_lh_han_all        = 0;                            // ��������
    $p2_lh_han_all_sagaku = 0;                            // ��������
} else {
    $p2_l_han_all         = $p2_l_han_all - $p2_l_allo_kin;
    $p2_lh_han_all        = $p2_l_han_all - $p2_s_han_all_sagaku - $p2_b_han_all_sagaku;
    $p2_lh_han_all_sagaku = $p2_lh_han_all;
    $p2_l_han_all         = $p2_l_han_all - $p2_s_han_all_sagaku;     // ������δ����פ��˥����δ����פ��ޥ��ʥ�
    $p2_lh_han_all        = number_format(($p2_lh_han_all / $tani), $keta);
    $p2_l_han_all         = number_format(($p2_l_han_all / $tani), $keta);
}

    ///// �����߷�
    ///// �������
    $rui_s_han_all        = $rui_s_han_jin_sagaku + $rui_s_han_kei_sagaku;
    $rui_s_han_all_sagaku = $rui_s_han_all;
    $rui_s_han_all        = number_format(($rui_s_han_all / $tani), $keta);
    ///// �Х����
    $rui_b_han_all        = $rui_b_han_jin_sagaku + $rui_b_han_kei_sagaku;
    $rui_b_han_all_sagaku = $rui_b_han_all;
    $rui_b_han_all        = number_format(($rui_b_han_all / $tani), $keta);
    ///// CL
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='��˥��δ���'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_l_han_all) < 1) {
    $rui_l_han_all         = 0 - $rui_s_han_all_sagaku;   // ��������
    $rui_lh_han_all        = 0;                           // ��������
    $rui_lh_han_all_sagaku = 0;                           // ��������
} else {
    $rui_l_han_all         = $rui_l_han_all - $rui_l_allo_kin;
    $rui_lh_han_all        = $rui_l_han_all - $rui_s_han_all_sagaku - $rui_b_han_all_sagaku;
    $rui_lh_han_all_sagaku = $rui_lh_han_all;
    $rui_l_han_all         = $rui_l_han_all - $rui_s_han_all_sagaku;     // ������δ����פ��˥����δ����פ��ޥ��ʥ�
    $rui_lh_han_all        = number_format(($rui_lh_han_all / $tani), $keta);
    $rui_l_han_all         = number_format(($rui_l_han_all / $tani), $keta);
}

/********** �Ķ����� **********/
    ///// �������
$p2_s_ope_profit         = $p2_s_gross_profit_sagaku - $p2_s_han_all_sagaku;
$p2_s_ope_profit_sagaku  = $p2_s_ope_profit;
$p2_s_ope_profit         = number_format(($p2_s_ope_profit / $tani), $keta);

$p1_s_ope_profit         = $p1_s_gross_profit_sagaku - $p1_s_han_all_sagaku;
$p1_s_ope_profit_sagaku  = $p1_s_ope_profit;
$p1_s_ope_profit         = number_format(($p1_s_ope_profit / $tani), $keta);

$s_ope_profit            = $s_gross_profit_sagaku - $s_han_all_sagaku;
$s_ope_profit_sagaku     = $s_ope_profit;
$s_ope_profit            = number_format(($s_ope_profit / $tani), $keta);

$rui_s_ope_profit        = $rui_s_gross_profit_sagaku - $rui_s_han_all_sagaku;
$rui_s_ope_profit_sagaku = $rui_s_ope_profit;
$rui_s_ope_profit        = number_format(($rui_s_ope_profit / $tani), $keta);
    ///// �Х����
$p2_b_ope_profit         = $p2_b_gross_profit_sagaku - $p2_b_han_all_sagaku;
$p2_b_ope_profit_sagaku  = $p2_b_ope_profit;
$p2_b_ope_profit         = number_format(($p2_b_ope_profit / $tani), $keta);

$p1_b_ope_profit         = $p1_b_gross_profit_sagaku - $p1_b_han_all_sagaku;
$p1_b_ope_profit_sagaku  = $p1_b_ope_profit;
$p1_b_ope_profit         = number_format(($p1_b_ope_profit / $tani), $keta);

$b_ope_profit            = $b_gross_profit_sagaku - $b_han_all_sagaku;
$b_ope_profit_sagaku     = $b_ope_profit;
$b_ope_profit            = number_format(($b_ope_profit / $tani), $keta);

$rui_b_ope_profit        = $rui_b_gross_profit_sagaku - $rui_b_han_all_sagaku;
$rui_b_ope_profit_sagaku = $rui_b_ope_profit;
$rui_b_ope_profit        = number_format(($rui_b_ope_profit / $tani), $keta);

    ///// ����
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥��Ķ�����'", $yyyymm);
if (getUniResult($query, $l_ope_profit) < 1) {
    $l_ope_profit         = 0 - $s_ope_profit_sagaku;     // ��������
    $lh_ope_profit        = 0;                            // ��������
    $lh_ope_profit_sagaku = 0;                            // ��������
} else {
    if ($yyyymm == 200906) {
        $l_ope_profit = $l_ope_profit - 3100900;
    } elseif ($yyyymm == 200905) {
        $l_ope_profit = $l_ope_profit + 1550450;
    } elseif ($yyyymm == 200904) {
        $l_ope_profit = $l_ope_profit + 1550450;
    }
    $l_ope_profit         = $l_ope_profit  + $l_allo_kin;
    if ($yyyymm == 200912) {
        $l_ope_profit = $l_ope_profit - 182279;
    }
    if ($yyyymm >= 201001) {
        $l_ope_profit = $l_ope_profit - $l_kyu_kin; // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
        //$l_ope_profit = $l_ope_profit - 151313; // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
    }
    if ($yyyymm == 201004) {
        $l_ope_profit = $l_ope_profit - 255240;
    }
    // 2012/02/08 �ɲ� 2012ǯ1���� ��̳�������ʿ�в����ɸ�����Ĵ��
    if ($yyyymm == 201201) {
        $l_ope_profit -=1156130;
    }
    // 2012/03/05 �ɲ� 2012ǯ1���� ��̳�������ʿ�в����ɸ�����Ĵ�� �ᤷ
    if ($yyyymm == 201202) {
        $l_ope_profit +=1156130;
    }
    if ($yyyymm == 201408) {
        $l_ope_profit -=229464;
    }
    $lh_ope_profit        = $l_ope_profit - $s_ope_profit_sagaku - $b_ope_profit_sagaku;
    $lh_ope_profit_sagaku = $lh_ope_profit;
    $l_ope_profit         = $l_ope_profit - $s_ope_profit_sagaku;     // ������Ķ����פ��˥��αĶ����פ��ޥ��ʥ�
    $lh_ope_profit        = number_format(($lh_ope_profit / $tani), $keta);
    $l_ope_profit         = number_format(($l_ope_profit / $tani), $keta);
}
    ///// ����
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥��Ķ�����'", $p1_ym);
if (getUniResult($query, $p1_l_ope_profit) < 1) {
    $p1_l_ope_profit         = 0 - $p1_s_ope_profit_sagaku;     // ��������
    $p1_lh_ope_profit        = 0;                               // ��������
    $p1_lh_ope_profit_sagaku = 0;                               // ��������
} else {
    if ($p1_ym == 200906) {
        $p1_l_ope_profit = $p1_l_ope_profit - 3100900;
    } elseif ($p1_ym == 200905) {
        $p1_l_ope_profit = $p1_l_ope_profit + 1550450;
    } elseif ($p1_ym == 200904) {
        $p1_l_ope_profit = $p1_l_ope_profit + 1550450;
    }
    if ($p1_ym == 200912) {
        $p1_l_ope_profit = $p1_l_ope_profit - 182279;
    }
    if ($p1_ym >= 201001) {
        $p1_l_ope_profit = $p1_l_ope_profit - $p1_l_kyu_kin;   // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
        //$p1_l_ope_profit = $p1_l_ope_profit - 151313;   // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
    }
    if ($p1_ym == 201004) {
        $p1_l_ope_profit = $p1_l_ope_profit - 255240;
    }
    // 2012/02/08 �ɲ� 2012ǯ1���� ��̳�������ʿ�в����ɸ�����Ĵ��
    if ($p1_ym == 201201) {
        $p1_l_ope_profit -=1156130;
    }
    // 2012/03/05 �ɲ� 2012ǯ1���� ��̳�������ʿ�в����ɸ�����Ĵ�� �ᤷ
    if ($p1_ym == 201202) {
        $p1_l_ope_profit +=1156130;
    }
    if ($p1_ym == 201408) {
        $p1_l_ope_profit -=229464;
    }
    $p1_l_ope_profit         = $p1_l_ope_profit  + $p1_l_allo_kin;
    $p1_lh_ope_profit        = $p1_l_ope_profit - $p1_s_ope_profit_sagaku - $p1_b_ope_profit_sagaku;
    $p1_lh_ope_profit_sagaku = $p1_lh_ope_profit;
    $p1_l_ope_profit         = $p1_l_ope_profit - $p1_s_ope_profit_sagaku;     // ������Ķ����פ��˥��αĶ����פ��ޥ��ʥ�
    $p1_lh_ope_profit        = number_format(($p1_lh_ope_profit / $tani), $keta);
    $p1_l_ope_profit         = number_format(($p1_l_ope_profit / $tani), $keta);
}
    ///// ������
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥��Ķ�����'", $p2_ym);
if (getUniResult($query, $p2_l_ope_profit) < 1) {
    $p2_l_ope_profit         = 0 - $p2_s_ope_profit_sagaku;     // ��������
    $p2_lh_ope_profit        = 0;                               // ��������
    $p2_lh_ope_profit_sagaku = 0;                               // ��������
} else {
    if ($p2_ym == 200906) {
        $p2_l_ope_profit = $p2_l_ope_profit - 3100900;
    } elseif ($p2_ym == 200905) {
        $p2_l_ope_profit = $p2_l_ope_profit + 1550450;
    } elseif ($p2_ym == 200904) {
        $p2_l_ope_profit = $p2_l_ope_profit + 1550450;
    }
    if ($p2_ym == 200912) {
        $p2_l_ope_profit = $p2_l_ope_profit - 182279;
    }
    if ($p2_ym >= 201001) {
        $p2_l_ope_profit = $p2_l_ope_profit - $p2_l_kyu_kin;   // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
        //$p2_l_ope_profit = $p2_l_ope_profit - 151313;   // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
    }
    if ($p2_ym == 201004) {
        $p2_l_ope_profit = $p2_l_ope_profit - 255240;
    }
    // 2012/02/08 �ɲ� 2012ǯ1���� ��̳�������ʿ�в����ɸ�����Ĵ��
    if ($p2_ym == 201201) {
        $p2_l_ope_profit -=1156130;
    }
    // 2012/03/05 �ɲ� 2012ǯ1���� ��̳�������ʿ�в����ɸ�����Ĵ�� �ᤷ
    if ($p2_ym == 201202) {
        $p2_l_ope_profit +=1156130;
    }
    if ($p2_ym == 201408) {
        $p2_l_ope_profit -=229464;
    }
    $p2_l_ope_profit         = $p2_l_ope_profit  + $p2_l_allo_kin;
    $p2_lh_ope_profit        = $p2_l_ope_profit - $p2_s_ope_profit_sagaku - $p2_b_ope_profit_sagaku;
    $p2_lh_ope_profit_sagaku = $p2_lh_ope_profit;
    $p2_l_ope_profit         = $p2_l_ope_profit - $p2_s_ope_profit_sagaku;     // ������Ķ����פ��˥��αĶ����פ��ޥ��ʥ�
    $p2_lh_ope_profit        = number_format(($p2_lh_ope_profit / $tani), $keta);
    $p2_l_ope_profit         = number_format(($p2_l_ope_profit / $tani), $keta);
}
    ///// �����߷�
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='��˥��Ķ�����'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_l_ope_profit) < 1) {
    $rui_l_ope_profit         = 0 - $rui_s_ope_profit_sagaku;   // ��������
    $rui_lh_ope_profit        = 0;                              // ��������
    $rui_lh_ope_profit_sagaku = 0;                              // ��������
    $rui_l_ope_profit_temp = 0;
} else {
    if ($yyyymm == 200905) {
        $rui_l_ope_profit = $rui_l_ope_profit + 3100900;
    } elseif ($yyyymm == 200904) {
        $rui_l_ope_profit = $rui_l_ope_profit + 1550450;
    }
    if ($yyyymm >= 200912 && $yyyymm <= 201003) {
        $rui_l_ope_profit = $rui_l_ope_profit - 182279;
    }
    if ($yyyymm >= 201001) {
        $rui_l_ope_profit = $rui_l_ope_profit - $rui_l_kyu_kin; // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
        //$rui_l_ope_profit = $rui_l_ope_profit - 151313; // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
    }
    if ($yyyymm >= 201004 && $yyyymm <= 201103) {
        $rui_l_ope_profit = $rui_l_ope_profit - 255240;
    }
    // 2012/02/08 �ɲ� 2012ǯ1���� ��̳�������ʿ�в����ɸ�����Ĵ��
    if ($yyyymm >= 201201 && $yyyymm <= 201203) {
        $rui_l_ope_profit -=1156130;
    }
    // 2012/03/05 �ɲ� 2012ǯ1���� ��̳�������ʿ�в����ɸ�����Ĵ�� �ᤷ
    if ($yyyymm >= 201202 && $yyyymm <= 201203) {
        $rui_l_ope_profit +=1156130;
    }
    if ($yyyymm >= 201408 && $yyyymm <= 201503) {
        $rui_l_ope_profit = $rui_l_ope_profit - 229464;
    }
    $rui_l_ope_profit         = $rui_l_ope_profit  + $rui_l_allo_kin;
    $rui_lh_ope_profit        = $rui_l_ope_profit - $rui_s_ope_profit_sagaku - $rui_b_ope_profit_sagaku;
    $rui_lh_ope_profit_sagaku = $rui_lh_ope_profit;
    $rui_l_ope_profit         = $rui_l_ope_profit - $rui_s_ope_profit_sagaku;     // ������Ķ����פ��˥��αĶ����פ��ޥ��ʥ�
    $rui_l_ope_profit_temp = $rui_l_ope_profit;
    $rui_lh_ope_profit        = number_format(($rui_lh_ope_profit / $tani), $keta);
    $rui_l_ope_profit         = number_format(($rui_l_ope_profit / $tani), $keta);
}

/********** �Ķȳ����פζ�̳�������� **********/
    ///// ����
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���̳���������Ʒ׻�'", $yyyymm);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���̳��������'", $yyyymm);
}
if (getUniResult($query, $s_gyoumu) < 1) {
    $s_gyoumu        = 0;                       // ��������
    $s_gyoumu_sagaku = 0;
} else {
    if ($yyyymm == 200912) {
        $s_gyoumu = $s_gyoumu - 722;
    }
    if ($yyyymm == 201001) {
        $s_gyoumu = $s_gyoumu + 29125;
    }
    $s_gyoumu_sagaku = $s_gyoumu;
    $s_gyoumu        = number_format(($s_gyoumu / $tani), $keta);
}
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�Х�����̳���������Ʒ׻�'", $yyyymm);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�Х�����̳��������'", $yyyymm);
}
if (getUniResult($query, $b_gyoumu) < 1) {
    $b_gyoumu = 0;    // ��������
    $b_gyoumu_sagaku = 0;
} else {
    if ($yyyymm == 200912) {
        $b_gyoumu = $b_gyoumu - 4931;
    }
    if ($yyyymm == 201001) {
        $b_gyoumu = $b_gyoumu + 4852;
    }
    $b_gyoumu_sagaku = $b_gyoumu;
    $b_gyoumu = number_format(($b_gyoumu / $tani), $keta);
}
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥���̳���������Ʒ׻�'", $yyyymm);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥���̳��������'", $yyyymm);
}
if (getUniResult($query, $l_gyoumu) < 1) {
    $l_gyoumu = 0 - $s_gyoumu_sagaku;     // ��������
    $lh_gyoumu = 0;     // ��������
    $lh_gyoumu_sagaku = 0;     // ��������
} else {
    if ($yyyymm == 200906) {
        $l_gyoumu = $l_gyoumu + 3100900;
    } elseif ($yyyymm == 200905) {
        $l_gyoumu = $l_gyoumu - 1550450;
    } elseif ($yyyymm == 200904) {
        $l_gyoumu = $l_gyoumu - 1550450;
    }
    if ($yyyymm == 200912) {
        $l_gyoumu = $l_gyoumu - 76191;
    }
    if ($yyyymm == 201001) {
        $l_gyoumu = $l_gyoumu + 58250;
    }
    if ($yyyymm >= 201001) {
        $l_gyoumu  = $l_gyoumu + $s_gyoumu_sagaku;
    }
    $lh_gyoumu = $l_gyoumu - $s_gyoumu_sagaku - $b_gyoumu_sagaku;
    $lh_gyoumu_sagaku = $lh_gyoumu;
    $l_gyoumu         = $l_gyoumu - $s_gyoumu_sagaku;     // �������̳�����������˥��ζ�̳�����������ޥ��ʥ�
    $lh_gyoumu = number_format(($lh_gyoumu / $tani), $keta);
    $l_gyoumu = number_format(($l_gyoumu / $tani), $keta);
}
    ///// ����
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���̳���������Ʒ׻�'", $p1_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���̳��������'", $p1_ym);
}
if (getUniResult($query, $p1_s_gyoumu) < 1) {
    $p1_s_gyoumu        = 0;                       // ��������
    $p1_s_gyoumu_sagaku = 0;
} else {
    if ($p1_ym == 200912) {
        $p1_s_gyoumu = $p1_s_gyoumu - 722;
    }
    if ($p1_ym == 201001) {
        $p1_s_gyoumu = $p1_s_gyoumu + 29125;
    }
    $p1_s_gyoumu_sagaku = $p1_s_gyoumu;
    $p1_s_gyoumu        = number_format(($p1_s_gyoumu / $tani), $keta);
}
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�Х�����̳���������Ʒ׻�'", $p1_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�Х�����̳��������'", $p1_ym);
}
if (getUniResult($query, $p1_b_gyoumu) < 1) {
    $p1_b_gyoumu = 0;    // ��������
    $p1_b_gyoumu_sagaku = 0;
} else {
    if ($p1_ym == 200912) {
        $p1_b_gyoumu = $p1_b_gyoumu - 4931;
    }
    if ($p1_ym == 201001) {
        $p1_b_gyoumu = $p1_b_gyoumu + 4852;
    }
    $p1_b_gyoumu_sagaku = $p1_b_gyoumu;
    $p1_b_gyoumu = number_format(($p1_b_gyoumu / $tani), $keta);
}
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥���̳���������Ʒ׻�'", $p1_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥���̳��������'", $p1_ym);
}
if (getUniResult($query, $p1_l_gyoumu) < 1) {
    $p1_l_gyoumu = 0 - $p1_s_gyoumu_sagaku;     // ��������
    $p1_lh_gyoumu = 0;     // ��������
    $p1_lh_gyoumu_sagaku = 0;     // ��������
} else {
    if ($p1_ym == 200906) {
        $p1_l_gyoumu = $p1_l_gyoumu + 3100900;
    } elseif ($p1_ym == 200905) {
        $p1_l_gyoumu = $p1_l_gyoumu - 1550450;
    } elseif ($p1_ym == 200904) {
        $p1_l_gyoumu = $p1_l_gyoumu - 1550450;
    }
    if ($p1_ym == 200912) {
        $p1_l_gyoumu = $p1_l_gyoumu - 76191;
    }
    if ($p1_ym == 201001) {
        $p1_l_gyoumu = $p1_l_gyoumu + 58250;
    }
    if ($p1_ym >= 201001) {
        $p1_l_gyoumu  = $p1_l_gyoumu + $p1_s_gyoumu_sagaku;
    }
    $p1_lh_gyoumu = $p1_l_gyoumu - $p1_s_gyoumu_sagaku - $p1_b_gyoumu_sagaku;
    $p1_lh_gyoumu_sagaku = $p1_lh_gyoumu;
    $p1_l_gyoumu         = $p1_l_gyoumu - $p1_s_gyoumu_sagaku;     // �������̳�����������˥��ζ�̳�����������ޥ��ʥ�
    $p1_lh_gyoumu = number_format(($p1_lh_gyoumu / $tani), $keta);
    $p1_l_gyoumu = number_format(($p1_l_gyoumu / $tani), $keta);
}
    ///// ������
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���̳���������Ʒ׻�'", $p2_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���̳��������'", $p2_ym);
}
if (getUniResult($query, $p2_s_gyoumu) < 1) {
    $p2_s_gyoumu        = 0;                       // ��������
    $p2_s_gyoumu_sagaku = 0;
} else {
    if ($p2_ym == 200912) {
        $p2_s_gyoumu = $p2_s_gyoumu - 722;
    }
    if ($p2_ym == 201001) {
        $p2_s_gyoumu = $p2_s_gyoumu + 29125;
    }
    $p2_s_gyoumu_sagaku = $p2_s_gyoumu;
    $p2_s_gyoumu        = number_format(($p2_s_gyoumu / $tani), $keta);
}
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�Х�����̳���������Ʒ׻�'", $p2_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�Х�����̳��������'", $p2_ym);
}
if (getUniResult($query, $p2_b_gyoumu) < 1) {
    $p2_b_gyoumu = 0;    // ��������
    $p2_b_gyoumu_sagaku = 0;
} else {
    if ($p2_ym == 200912) {
        $p2_b_gyoumu = $p2_b_gyoumu - 4931;
    }
    if ($p2_ym == 201001) {
        $p2_b_gyoumu = $p2_b_gyoumu + 4852;
    }
    $p2_b_gyoumu_sagaku = $p2_b_gyoumu;
    $p2_b_gyoumu = number_format(($p2_b_gyoumu / $tani), $keta);
}
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥���̳���������Ʒ׻�'", $p2_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥���̳��������'", $p2_ym);
}
if (getUniResult($query, $p2_l_gyoumu) < 1) {
    $p2_l_gyoumu = 0 - $p2_s_gyoumu_sagaku;     // ��������
    $p2_lh_gyoumu = 0;     // ��������
    $p2_lh_gyoumu_sagaku = 0;     // ��������
} else {
    if ($p2_ym == 200906) {
        $p2_l_gyoumu = $p2_l_gyoumu + 3100900;
    } elseif ($p2_ym == 200905) {
        $p2_l_gyoumu = $p2_l_gyoumu - 1550450;
    } elseif ($p2_ym == 200904) {
        $p2_l_gyoumu = $p2_l_gyoumu - 1550450;
    }
    if ($p2_ym == 200912) {
        $p2_l_gyoumu = $p2_l_gyoumu - 76191;
    }
    if ($p2_ym == 201001) {
        $p2_l_gyoumu = $p2_l_gyoumu + 58250;
    }
    if ($p2_ym >= 201001) {
        $p2_l_gyoumu  = $p2_l_gyoumu + $p2_s_gyoumu_sagaku;
    }
    $p2_lh_gyoumu = $p2_l_gyoumu - $p2_s_gyoumu_sagaku - $p2_b_gyoumu_sagaku;
    $p2_lh_gyoumu_sagaku = $p2_lh_gyoumu;
    $p2_l_gyoumu         = $p2_l_gyoumu - $p2_s_gyoumu_sagaku;     // �������̳�����������˥��ζ�̳�����������ޥ��ʥ�
    $p2_lh_gyoumu = number_format(($p2_lh_gyoumu / $tani), $keta);
    $p2_l_gyoumu = number_format(($p2_l_gyoumu / $tani), $keta);
}
    ///// �����߷�
if ($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���̳���������Ʒ׻�'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_s_gyoumu) < 1) {
        $rui_s_gyoumu = 0;    // ��������
        $rui_s_gyoumu_sagaku = 0;
    } else {
        $rui_s_gyoumu_sagaku = $rui_s_gyoumu;
        $rui_s_gyoumu = number_format(($rui_s_gyoumu / $tani), $keta);
    }
} elseif ($yyyymm >= 201001 && $yyyymm <= 201003) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=200904 and pl_bs_ym<=200912 and note='���̳��������'");
    if (getUniResult($query, $rui_s_gyoumu_a) < 1) {
        $rui_s_gyoumu_a = 0;                          // ��������
    }
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=201001 and pl_bs_ym<=%d and note='���̳���������Ʒ׻�'", $yyyymm);
    if (getUniResult($query, $rui_s_gyoumu_b) < 1) {
        $rui_s_gyoumu_b = 0;                          // ��������
    }
    $rui_s_gyoumu = $rui_s_gyoumu_a + $rui_s_gyoumu_b;
    
    if ($yyyymm >= 200912 && $yyyymm <= 201003) {
        $rui_s_gyoumu = $rui_s_gyoumu - 722;
        //$rui_s_gyoumu_b = $rui_s_gyoumu_b - 722;
    }
    if ($yyyymm >= 201001 && $yyyymm <= 201003) {
        $rui_s_gyoumu = $rui_s_gyoumu + 29125;
    }
    $rui_s_gyoumu_sagaku = $rui_s_gyoumu;
    $rui_s_gyoumu = number_format(($rui_s_gyoumu / $tani), $keta);
} else {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���̳��������'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_s_gyoumu) < 1) {
        $rui_s_gyoumu        = 0;                   // ��������
        $rui_s_gyoumu_sagaku = 0;
    } else {
        if ($yyyymm >= 200912 && $yyyymm <= 201003) {
            $rui_s_gyoumu = $rui_s_gyoumu - 722;
        }
        if ($yyyymm >= 201001 && $yyyymm <= 201003) {
            $rui_s_gyoumu = $rui_s_gyoumu + 29125;
        }
        $rui_s_gyoumu_sagaku = $rui_s_gyoumu;
        $rui_s_gyoumu = number_format(($rui_s_gyoumu / $tani), $keta);
    }
}
if ($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='�Х�����̳���������Ʒ׻�'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_b_gyoumu) < 1) {
        $rui_b_gyoumu = 0;    // ��������
        $rui_b_gyoumu_sagaku = 0;
    } else {
        $rui_b_gyoumu_sagaku = $rui_b_gyoumu;
        $rui_b_gyoumu = number_format(($rui_b_gyoumu / $tani), $keta);
    }
} elseif ($yyyymm >= 201001 && $yyyymm <= 201003) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=200904 and pl_bs_ym<=200912 and note='�Х�����̳��������'");
    if (getUniResult($query, $rui_b_gyoumu_a) < 1) {
        $rui_b_gyoumu_a = 0;                          // ��������
    }
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=201001 and pl_bs_ym<=%d and note='�Х�����̳���������Ʒ׻�'", $yyyymm);
    if (getUniResult($query, $rui_b_gyoumu_b) < 1) {
        $rui_b_gyoumu_b = 0;                          // ��������
    }
    $rui_b_gyoumu = $rui_b_gyoumu_a + $rui_b_gyoumu_b;
    if ($yyyymm >= 200912 && $yyyymm <= 201003) {
        $rui_b_gyoumu = $rui_b_gyoumu - 4931;
    }
    if ($yyyymm >= 201001 && $yyyymm <= 201003) {
        $rui_b_gyoumu = $rui_b_gyoumu + 4852;
    }
    $rui_b_gyoumu_sagaku = $rui_b_gyoumu;
    $rui_b_gyoumu = number_format(($rui_b_gyoumu / $tani), $keta);
} else {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='�Х�����̳��������'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_b_gyoumu) < 1) {
        $rui_b_gyoumu = 0;    // ��������
        $rui_b_gyoumu_sagaku = 0;
    } else {
        if ($yyyymm >= 200912 && $yyyymm <= 201003) {
            $rui_b_gyoumu = $rui_b_gyoumu - 4931;
        }
        if ($yyyymm >= 201001 && $yyyymm <= 201003) {
            $rui_b_gyoumu = $rui_b_gyoumu + 4852;
        }
        $rui_b_gyoumu_sagaku = $rui_b_gyoumu;
        $rui_b_gyoumu = number_format(($rui_b_gyoumu / $tani), $keta);
    }
}
if ($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='��˥���̳���������Ʒ׻�'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_l_gyoumu) < 1) {
        $rui_l_gyoumu = 0 - $rui_s_gyoumu_sagaku;   // ��������
        $rui_lh_gyoumu = 0;     // ��������
        $rui_lh_gyoumu_sagaku = 0;     // ��������
    } else {
        $rui_l_gyoumu = $rui_l_gyoumu + $rui_s_gyoumu_sagaku;
        $rui_lh_gyoumu = $rui_l_gyoumu - $rui_s_gyoumu_sagaku - $rui_b_gyoumu_sagaku;
        $rui_lh_gyoumu_sagaku = $rui_lh_gyoumu;
        $rui_l_gyoumu         = $rui_l_gyoumu - $rui_s_gyoumu_sagaku;     // �������̳�����������˥��ζ�̳�����������ޥ��ʥ�
        $rui_lh_gyoumu = number_format(($rui_lh_gyoumu / $tani), $keta);
        $rui_l_gyoumu = number_format(($rui_l_gyoumu / $tani), $keta);
    }
} elseif ($yyyymm >= 201001 && $yyyymm <= 201003) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=200904 and pl_bs_ym<=200912 and note='��˥���̳��������'");
    if (getUniResult($query, $rui_l_gyoumu_a) < 1) {
        $rui_l_gyoumu_a = 0;                          // ��������
    }
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=201001 and pl_bs_ym<=%d and note='��˥���̳���������Ʒ׻�'", $yyyymm);
    if (getUniResult($query, $rui_l_gyoumu_b) < 1) {
        $rui_l_gyoumu_b = 0;                          // ��������
    }
    $rui_l_gyoumu = $rui_l_gyoumu_a + $rui_l_gyoumu_b;
    
    if ($yyyymm >= 200912 && $yyyymm <= 201003) {
        $rui_l_gyoumu = $rui_l_gyoumu - 76191;
    }
    if ($yyyymm >= 201001 && $yyyymm <= 201003) {
        $rui_l_gyoumu = $rui_l_gyoumu + 58250 + 29125;
    }
    $rui_l_gyoumu = $rui_l_gyoumu + $rui_s_gyoumu_b;
    $rui_lh_gyoumu = $rui_l_gyoumu - $rui_s_gyoumu_sagaku - $rui_b_gyoumu_sagaku;
    $rui_lh_gyoumu_sagaku = $rui_lh_gyoumu;
    $rui_l_gyoumu         = $rui_l_gyoumu - $rui_s_gyoumu_sagaku;     // �������̳�����������˥��ζ�̳�����������ޥ��ʥ�
    $rui_lh_gyoumu = number_format(($rui_lh_gyoumu / $tani), $keta);
    $rui_l_gyoumu = number_format(($rui_l_gyoumu / $tani), $keta);
} else {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='��˥���̳��������'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_l_gyoumu) < 1) {
        $rui_l_gyoumu = 0 - $rui_s_gyoumu_sagaku;   // ��������
        $rui_lh_gyoumu = 0;     // ��������
        $rui_lh_gyoumu_sagaku = 0;     // ��������
    } else {
        if ($yyyymm >= 200912 && $yyyymm <= 201003) {
            $rui_l_gyoumu = $rui_l_gyoumu - 76191;
        }
        //$rui_l_gyoumu = $rui_l_gyoumu + $rui_s_gyoumu_sagaku;
        $rui_lh_gyoumu = $rui_l_gyoumu - $rui_s_gyoumu_sagaku - $rui_b_gyoumu_sagaku;
        $rui_lh_gyoumu_sagaku = $rui_lh_gyoumu;
        $rui_l_gyoumu         = $rui_l_gyoumu - $rui_s_gyoumu_sagaku;     // �������̳�����������˥��ζ�̳�����������ޥ��ʥ�
        $rui_lh_gyoumu = number_format(($rui_lh_gyoumu / $tani), $keta);
        $rui_l_gyoumu = number_format(($rui_l_gyoumu / $tani), $keta);
    }
}
/********** �Ķȳ����פλ������ **********/
    ///// ����
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���������Ʒ׻�'", $yyyymm);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��������'", $yyyymm);
}
if (getUniResult($query, $s_swari) < 1) {
    $s_swari        = 0;                        // ��������
    $s_swari_sagaku = 0;
} else {
    $s_swari_sagaku = $s_swari;
    $s_swari        = number_format(($s_swari / $tani), $keta);
}
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�Х�����������Ʒ׻�'", $yyyymm);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�Х����������'", $yyyymm);
}
if (getUniResult($query, $b_swari) < 1) {
    $b_swari        = 0;                        // ��������
    $b_swari_sagaku = 0;
} else {
    $b_swari_sagaku = $b_swari;
    $b_swari        = number_format(($b_swari / $tani), $keta);
}
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥���������Ʒ׻�'", $yyyymm);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥��������'", $yyyymm);
}
if (getUniResult($query, $l_swari) < 1) {
    $l_swari = 0 - $s_swari_sagaku;     // ��������
    $lh_swari = 0;     // ��������
    $lh_swari_sagaku = 0;     // ��������
} else {
    if ($yyyymm >= 201001) {
        $l_swari = $l_swari + $s_swari_sagaku;
    }
    $lh_swari = $l_swari - $s_swari_sagaku - $b_swari_sagaku;
    $lh_swari_sagaku = $lh_swari;
    $l_swari         = $l_swari - $s_swari_sagaku;     // ���������������˥��λ���������ޥ��ʥ�
    $lh_swari = number_format(($lh_swari / $tani), $keta);
    $l_swari = number_format(($l_swari / $tani), $keta);
}
    ///// ����
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���������Ʒ׻�'", $p1_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��������'", $p1_ym);
}
if (getUniResult($query, $p1_s_swari) < 1) {
    $p1_s_swari        = 0;                        // ��������
    $p1_s_swari_sagaku = 0;
} else {
    $p1_s_swari_sagaku = $p1_s_swari;
    $p1_s_swari        = number_format(($p1_s_swari / $tani), $keta);
}
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�Х�����������Ʒ׻�'", $p1_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�Х����������'", $p1_ym);
}
if (getUniResult($query, $p1_b_swari) < 1) {
    $p1_b_swari        = 0;                        // ��������
    $p1_b_swari_sagaku = 0;
} else {
    $p1_b_swari_sagaku = $p1_b_swari;
    $p1_b_swari        = number_format(($p1_b_swari / $tani), $keta);
}
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥���������Ʒ׻�'", $p1_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥��������'", $p1_ym);
}
if (getUniResult($query, $p1_l_swari) < 1) {
    $p1_l_swari = 0 - $p1_s_swari_sagaku;     // ��������
    $p1_lh_swari = 0;     // ��������
    $p1_lh_swari_sagaku = 0;     // ��������
} else {
    if ($p1_ym >= 201001) {
        $p1_l_swari = $p1_l_swari + $p1_s_swari_sagaku;
    }
    $p1_lh_swari = $p1_l_swari - $p1_s_swari_sagaku - $p1_b_swari_sagaku;
    $p1_lh_swari_sagaku = $p1_lh_swari;
    $p1_l_swari         = $p1_l_swari - $p1_s_swari_sagaku;     // ���������������˥��λ���������ޥ��ʥ�
    $p1_lh_swari = number_format(($p1_lh_swari / $tani), $keta);
    $p1_l_swari = number_format(($p1_l_swari / $tani), $keta);
}
    ///// ������
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���������Ʒ׻�'", $p2_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��������'", $p2_ym);
}
if (getUniResult($query, $p2_s_swari) < 1) {
    $p2_s_swari        = 0;                        // ��������
    $p2_s_swari_sagaku = 0;
} else {
    $p2_s_swari_sagaku = $p2_s_swari;
    $p2_s_swari        = number_format(($p2_s_swari / $tani), $keta);
}
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�Х�����������Ʒ׻�'", $p2_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�Х����������'", $p2_ym);
}
if (getUniResult($query, $p2_b_swari) < 1) {
    $p2_b_swari        = 0;                        // ��������
    $p2_b_swari_sagaku = 0;
} else {
    $p2_b_swari_sagaku = $p2_b_swari;
    $p2_b_swari        = number_format(($p2_b_swari / $tani), $keta);
}
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥���������Ʒ׻�'", $p2_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥��������'", $p2_ym);
}
if (getUniResult($query, $p2_l_swari) < 1) {
    $p2_l_swari = 0 - $p2_s_swari_sagaku;     // ��������
    $p2_lh_swari = 0;     // ��������
    $p2_lh_swari_sagaku = 0;     // ��������
} else {
    if ($p2_ym >= 201001) {
        $p2_l_swari = $p2_l_swari + $p2_s_swari_sagaku;
    }
    $p2_lh_swari = $p2_l_swari - $p2_s_swari_sagaku - $p2_b_swari_sagaku;
    $p2_lh_swari_sagaku = $p2_lh_swari;
    $p2_l_swari         = $p2_l_swari - $p2_s_swari_sagaku;     // ���������������˥��λ���������ޥ��ʥ�
    $p2_lh_swari = number_format(($p2_lh_swari / $tani), $keta);
    $p2_l_swari = number_format(($p2_l_swari / $tani), $keta);
}
    ///// �����߷�
if ($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���������Ʒ׻�'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_s_swari) < 1) {
        $rui_s_swari = 0;                           // ��������
    } else {
        $rui_s_swari_sagaku = $rui_s_swari;
        $rui_s_swari = number_format(($rui_s_swari / $tani), $keta);
    }
} elseif ($yyyymm >= 201001 && $yyyymm <= 201003) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=200904 and pl_bs_ym<=200912 and note='��������'");
    if (getUniResult($query, $rui_s_swari_a) < 1) {
        $rui_s_swari_a = 0;                          // ��������
    }
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=201001 and pl_bs_ym<=%d and note='���������Ʒ׻�'", $yyyymm);
    if (getUniResult($query, $rui_s_swari_b) < 1) {
        $rui_s_swari_b = 0;                          // ��������
    }
    $rui_s_swari = $rui_s_swari_a + $rui_s_swari_b;
    $rui_s_swari_sagaku = $rui_s_swari;
    $rui_s_swari = number_format(($rui_s_swari / $tani), $keta);
} else {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='��������'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_s_swari) < 1) {
        $rui_s_swari        = 0;                    // ��������
        $rui_s_swari_sagaku = 0;
    } else {
        $rui_s_swari_sagaku = $rui_s_swari;
        $rui_s_swari = number_format(($rui_s_swari / $tani), $keta);
    }
}
if ($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='�Х�����������Ʒ׻�'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_b_swari) < 1) {
        $rui_b_swari = 0;    // ��������
        $rui_b_swari_sagaku = 0;
    } else {
        $rui_b_swari_sagaku = $rui_b_swari;
        $rui_b_swari = number_format(($rui_b_swari / $tani), $keta);
    }
} elseif ($yyyymm >= 201001 && $yyyymm <= 201003) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=200904 and pl_bs_ym<=200912 and note='�Х����������'");
    if (getUniResult($query, $rui_b_swari_a) < 1) {
        $rui_b_swari_a = 0;                          // ��������
    }
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=201001 and pl_bs_ym<=%d and note='�Х�����������Ʒ׻�'", $yyyymm);
    if (getUniResult($query, $rui_b_swari_b) < 1) {
        $rui_b_swari_b = 0;                          // ��������
    }
    $rui_b_swari = $rui_b_swari_a + $rui_b_swari_b;
    $rui_b_swari_sagaku = $rui_b_swari;
    $rui_b_swari = number_format(($rui_b_swari / $tani), $keta);
} else {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='�Х����������'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_b_swari) < 1) {
        $rui_b_swari = 0;    // ��������
        $rui_b_swari_sagaku = 0;
    } else {
        $rui_b_swari_sagaku = $rui_b_swari;
        $rui_b_swari = number_format(($rui_b_swari / $tani), $keta);
    }
}
if ($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='��˥���������Ʒ׻�'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_l_swari) < 1) {
        $rui_l_swari = 0 - $rui_s_swari_sagaku;   // ��������
        $rui_lh_swari = 0;     // ��������
        $rui_lh_swari_sagaku = 0;     // ��������
    } else {
        $rui_l_swari = $rui_l_swari + $rui_s_swari_sagaku;
        $rui_lh_swari = $rui_l_swari - $rui_s_swari_sagaku - $rui_b_swari_sagaku;
        $rui_lh_swari_sagaku = $rui_lh_swari;
        $rui_l_swari         = $rui_l_swari - $rui_s_swari_sagaku;     // ���������������˥��λ���������ޥ��ʥ�
        $rui_lh_swari = number_format(($rui_lh_swari / $tani), $keta);
        $rui_l_swari = number_format(($rui_l_swari / $tani), $keta);
    }
} elseif ($yyyymm >= 201001 && $yyyymm <= 201003) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=200904 and pl_bs_ym<=200912 and note='��˥��������'");
    if (getUniResult($query, $rui_l_swari_a) < 1) {
        $rui_l_swari_a = 0;                          // ��������
    }
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=201001 and pl_bs_ym<=%d and note='��˥���������Ʒ׻�'", $yyyymm);
    if (getUniResult($query, $rui_l_swari_b) < 1) {
        $rui_l_swari_b = 0;                          // ��������
    }
    $rui_l_swari = $rui_l_swari_a + $rui_l_swari_b;
    $rui_l_swari = $rui_l_swari - $rui_s_swari_a;
    $rui_l_swari = $rui_l_swari + $rui_s_swari_sagaku;
    $rui_lh_swari = $rui_l_swari - $rui_s_swari_sagaku - $rui_b_swari_sagaku;
    $rui_lh_swari_sagaku = $rui_lh_swari;
    $rui_l_swari         = $rui_l_swari - $rui_s_swari_sagaku;     // ���������������˥��λ���������ޥ��ʥ�
    $rui_lh_swari = number_format(($rui_lh_swari / $tani), $keta);
    $rui_l_swari = number_format(($rui_l_swari / $tani), $keta);
} else {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='��˥��������'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_l_swari) < 1) {
        $rui_l_swari = 0 - $rui_s_swari_sagaku;   // ��������
        $rui_lh_swari = 0;     // ��������
        $rui_lh_swari_sagaku = 0;     // ��������
    } else {
        //$rui_l_swari = $rui_l_swari + $rui_s_swari_sagaku;
        $rui_lh_swari = $rui_l_swari - $rui_s_swari_sagaku - $rui_b_swari_sagaku;
        $rui_lh_swari_sagaku = $rui_lh_swari;
        $rui_l_swari         = $rui_l_swari - $rui_s_swari_sagaku;     // ���������������˥��λ���������ޥ��ʥ�
        $rui_lh_swari = number_format(($rui_lh_swari / $tani), $keta);
        $rui_l_swari = number_format(($rui_l_swari / $tani), $keta);
    }
}
/********** �Ķȳ����פΤ���¾ **********/
    ///// ����
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��Ķȳ����פ���¾�Ʒ׻�'", $yyyymm);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��Ķȳ����פ���¾'", $yyyymm);
}
if (getUniResult($query, $s_pother) < 1) {
    $s_pother        = 0;                       // ��������
    $s_pother_sagaku = 0;
} else {
    if ($yyyymm == 200912) {
        $s_pother = $s_pother + 722;
    }
    if ($yyyymm == 201001) {
        $s_pother = $s_pother - 29125;
    }
    $s_pother_sagaku = $s_pother;
    $s_pother        = number_format(($s_pother / $tani), $keta);
}
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�Х����Ķȳ����פ���¾�Ʒ׻�'", $yyyymm);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�Х����Ķȳ����פ���¾'", $yyyymm);
}
if (getUniResult($query, $b_pother) < 1) {
    $b_pother = 0;    // ��������
    $b_pother_sagaku = 0;
} else {
    if ($yyyymm == 200912) {
        $b_pother = $b_pother + 4931;
    }
    if ($yyyymm == 201001) {
        $b_pother = $b_pother - 4852;
    }
    $b_pother_sagaku = $b_pother;
    $b_pother = number_format(($b_pother / $tani), $keta);
}
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥��Ķȳ����פ���¾�Ʒ׻�'", $yyyymm);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥��Ķȳ����פ���¾'", $yyyymm);
}
if (getUniResult($query, $l_pother) < 1) {
    $l_pother = 0 - $s_pother_sagaku;     // ��������
    $lh_pother = 0;     // ��������
    $lh_pother_sagaku = 0;     // ��������
} else {
    if ($yyyymm == 200912) {
        $l_pother = $l_pother + 76191;
    }
    if ($yyyymm == 201001) {
        $l_pother = $l_pother - 58250;
    }
    if ($yyyymm >= 201001) {
        $l_pother = $l_pother + $s_pother_sagaku;
    }
    $lh_pother = $l_pother - $s_pother_sagaku - $b_pother_sagaku;
    $lh_pother_sagaku = $lh_pother;
    $l_pother         = $l_pother - $s_pother_sagaku;     // ������Ķȳ����פ���¾���˥��αĶȳ����פ���¾���ޥ��ʥ�
    $lh_pother = number_format(($lh_pother / $tani), $keta);
    $l_pother = number_format(($l_pother / $tani), $keta);
}
    ///// ����
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��Ķȳ����פ���¾�Ʒ׻�'", $p1_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��Ķȳ����פ���¾'", $p1_ym);
}
if (getUniResult($query, $p1_s_pother) < 1) {
    $p1_s_pother        = 0;                       // ��������
    $p1_s_pother_sagaku = 0;
} else {
    if ($p1_ym == 200912) {
        $p1_s_pother = $p1_s_pother + 722;
    }
    if ($p1_ym == 201001) {
        $p1_s_pother = $p1_s_pother - 29125;
    }
    $p1_s_pother_sagaku = $p1_s_pother;
    $p1_s_pother        = number_format(($p1_s_pother / $tani), $keta);
}
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�Х����Ķȳ����פ���¾�Ʒ׻�'", $p1_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�Х����Ķȳ����פ���¾'", $p1_ym);
}
if (getUniResult($query, $p1_b_pother) < 1) {
    $p1_b_pother = 0;    // ��������
    $p1_b_pother_sagaku = 0;
} else {
    if ($p1_ym == 200912) {
        $p1_b_pother = $p1_b_pother + 4931;
    }
    if ($p1_ym == 201001) {
        $p1_b_pother = $p1_b_pother - 4852;
    }
    $p1_b_pother_sagaku = $p1_b_pother;
    $p1_b_pother = number_format(($p1_b_pother / $tani), $keta);
}
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥��Ķȳ����פ���¾�Ʒ׻�'", $p1_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥��Ķȳ����פ���¾'", $p1_ym);
}
if (getUniResult($query, $p1_l_pother) < 1) {
    $p1_l_pother = 0 - $p1_s_pother_sagaku;     // ��������
    $p1_lh_pother = 0;     // ��������
    $p1_lh_pother_sagaku = 0;     // ��������
} else {
    if ($p1_ym == 200912) {
        $p1_l_pother = $p1_l_pother + 76191;
    }
    if ($p1_ym == 201001) {
        $p1_l_pother = $p1_l_pother - 58250;
    }
    if ($p1_ym >= 201001) {
        $p1_l_pother = $p1_l_pother + $p1_s_pother_sagaku;
    }
    $p1_lh_pother = $p1_l_pother - $p1_s_pother_sagaku - $p1_b_pother_sagaku;
    $p1_lh_pother_sagaku = $p1_lh_pother;
    $p1_l_pother         = $p1_l_pother - $p1_s_pother_sagaku;     // ������Ķȳ����פ���¾���˥��αĶȳ����פ���¾���ޥ��ʥ�
    $p1_lh_pother = number_format(($p1_lh_pother / $tani), $keta);
    $p1_l_pother = number_format(($p1_l_pother / $tani), $keta);
}
    ///// ������
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��Ķȳ����פ���¾�Ʒ׻�'", $p2_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��Ķȳ����פ���¾'", $p2_ym);
}
if (getUniResult($query, $p2_s_pother) < 1) {
    $p2_s_pother        = 0;                       // ��������
    $p2_s_pother_sagaku = 0;
} else {
    if ($p2_ym == 200912) {
        $p2_s_pother = $p2_s_pother + 722;
    }
    if ($p2_ym == 201001) {
        $p2_s_pother = $p2_s_pother - 29125;
    }
    $p2_s_pother_sagaku = $p2_s_pother;
    $p2_s_pother        = number_format(($p2_s_pother / $tani), $keta);
}
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�Х����Ķȳ����פ���¾�Ʒ׻�'", $p2_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�Х����Ķȳ����פ���¾'", $p2_ym);
}
if (getUniResult($query, $p2_b_pother) < 1) {
    $p2_b_pother = 0;    // ��������
    $p2_b_pother_sagaku = 0;
} else {
    if ($p2_ym == 200912) {
        $p2_b_pother = $p2_b_pother + 4931;
    }
    if ($p2_ym == 201001) {
        $p2_b_pother = $p2_b_pother - 4852;
    }
    $p2_b_pother_sagaku = $p2_b_pother;
    $p2_b_pother = number_format(($p2_b_pother / $tani), $keta);
}
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥��Ķȳ����פ���¾�Ʒ׻�'", $p2_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥��Ķȳ����פ���¾'", $p2_ym);
}
if (getUniResult($query, $p2_l_pother) < 1) {
    $p2_l_pother = 0 - $p2_s_pother_sagaku;     // ��������
    $p2_lh_pother = 0;     // ��������
    $p2_lh_pother_sagaku = 0;     // ��������
} else {
    if ($p2_ym == 200912) {
        $p2_l_pother = $p2_l_pother + 76191;
    }
    if ($p2_ym == 201001) {
        $p2_l_pother = $p2_l_pother - 58250;
    }
    if ($p2_ym >= 201001) {
        $p2_l_pother = $p2_l_pother + $p2_s_pother_sagaku;
    }
    $p2_lh_pother = $p2_l_pother - $p2_s_pother_sagaku - $p2_b_pother_sagaku;
    $p2_lh_pother_sagaku = $p2_lh_pother;
    $p2_l_pother         = $p2_l_pother - $p2_s_pother_sagaku;     // ������Ķȳ����פ���¾���˥��αĶȳ����פ���¾���ޥ��ʥ�
    $p2_lh_pother = number_format(($p2_lh_pother / $tani), $keta);
    $p2_l_pother = number_format(($p2_l_pother / $tani), $keta);
}
    ///// �����߷�
if ($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='��Ķȳ����פ���¾�Ʒ׻�'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_s_pother) < 1) {
        $rui_s_pother = 0;                          // ��������
    } else {
        $rui_s_pother_sagaku = $rui_s_pother;
        $rui_s_pother = number_format(($rui_s_pother / $tani), $keta);
    }
} elseif ($yyyymm >= 201001 && $yyyymm <= 201003) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=200904 and pl_bs_ym<=200912 and note='��Ķȳ����פ���¾'");
    if (getUniResult($query, $rui_s_pother_a) < 1) {
        $rui_s_pother_a = 0;                          // ��������
    }
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=201001 and pl_bs_ym<=%d and note='��Ķȳ����פ���¾�Ʒ׻�'", $yyyymm);
    if (getUniResult($query, $rui_s_pother_b) < 1) {
        $rui_s_pother_b = 0;                          // ��������
    }
    $rui_s_pother = $rui_s_pother_a + $rui_s_pother_b;
    
    if ($yyyymm >= 200912 && $yyyymm <= 201003) {
        $rui_s_pother = $rui_s_pother + 722;
    }
    if ($yyyymm >= 201001 && $yyyymm <= 201003) {
        $rui_s_pother = $rui_s_pother - 29125;
    }
    $rui_s_pother_sagaku = $rui_s_pother;
    $rui_s_pother = number_format(($rui_s_pother / $tani), $keta);
} else {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='��Ķȳ����פ���¾'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_s_pother) < 1) {
        $rui_s_pother        = 0;                   // ��������
        $rui_s_pother_sagaku = 0;
    } else {
        if ($yyyymm >= 200912 && $yyyymm <= 201003) {
            $rui_s_pother = $rui_s_pother + 722;
        }
        if ($yyyymm >= 201001 && $yyyymm <= 201003) {
            $rui_s_pother = $rui_s_pother - 29125;
        }
        $rui_s_pother_sagaku = $rui_s_pother;
        $rui_s_pother = number_format(($rui_s_pother / $tani), $keta);
    }
}
if ($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='�Х����Ķȳ����פ���¾�Ʒ׻�'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_b_pother) < 1) {
        $rui_b_pother = 0;    // ��������
        $rui_b_pother_sagaku = 0;
    } else {
        $rui_b_pother_sagaku = $rui_b_pother;
        $rui_b_pother = number_format(($rui_b_pother / $tani), $keta);
    }
} elseif ($yyyymm >= 201001 && $yyyymm <= 201003) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=200904 and pl_bs_ym<=200912 and note='�Х����Ķȳ����פ���¾'");
    if (getUniResult($query, $rui_b_pother_a) < 1) {
        $rui_b_pother_a = 0;                          // ��������
    }
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=201001 and pl_bs_ym<=%d and note='�Х����Ķȳ����פ���¾�Ʒ׻�'", $yyyymm);
    if (getUniResult($query, $rui_b_pother_b) < 1) {
        $rui_b_pother_b = 0;                          // ��������
    }
    $rui_b_pother = $rui_b_pother_a + $rui_b_pother_b;
    if ($yyyymm >= 200912 && $yyyymm <= 201003) {
        $rui_b_pother = $rui_b_pother + 4931;
    }
    if ($yyyymm >= 201001 && $yyyymm <= 201003) {
        $rui_b_pother = $rui_b_pother - 4852;
    }
    $rui_b_pother_sagaku = $rui_b_pother;
    $rui_b_pother = number_format(($rui_b_pother / $tani), $keta);
} else {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='�Х����Ķȳ����פ���¾'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_b_pother) < 1) {
        $rui_b_pother = 0;    // ��������
        $rui_b_pother_sagaku = 0;
    } else {
        if ($yyyymm >= 200912 && $yyyymm <= 201003) {
            $rui_b_pother = $rui_b_pother + 4931;
        }
        if ($yyyymm >= 201001 && $yyyymm <= 201003) {
            $rui_b_pother = $rui_b_pother - 4852;
        }
        $rui_b_pother_sagaku = $rui_b_pother;
        $rui_b_pother = number_format(($rui_b_pother / $tani), $keta);
    }
}
if ($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='��˥��Ķȳ����פ���¾�Ʒ׻�'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_l_pother) < 1) {
        $rui_l_pother = 0 - $rui_s_pother_sagaku;   // ��������
        $rui_lh_pother = 0;     // ��������
        $rui_lh_pother_sagaku = 0;     // ��������
    } else {
        $rui_l_pother = $rui_l_pother + $rui_s_pother_sagaku;
        $rui_lh_pother = $rui_l_pother - $rui_s_pother_sagaku - $rui_b_pother_sagaku;
        $rui_lh_pother_sagaku = $rui_lh_pother;
        $rui_l_pother         = $rui_l_pother - $rui_s_pother_sagaku;     // ������Ķȳ����פ���¾���˥��αĶȳ����פ���¾���ޥ��ʥ�
        $rui_lh_pother = number_format(($rui_lh_pother / $tani), $keta);
        $rui_l_pother = number_format(($rui_l_pother / $tani), $keta);
    }
} elseif ($yyyymm >= 201001 && $yyyymm <= 201003) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=200904 and pl_bs_ym<=200912 and note='��˥��Ķȳ����פ���¾'");
    if (getUniResult($query, $rui_l_pother_a) < 1) {
        $rui_l_pother_a = 0;                          // ��������
    }
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=201001 and pl_bs_ym<=%d and note='��˥��Ķȳ����פ���¾�Ʒ׻�'", $yyyymm);
    if (getUniResult($query, $rui_l_pother_b) < 1) {
        $rui_l_pother_b = 0;                          // ��������
    }
    $rui_l_pother = $rui_l_pother_a + $rui_l_pother_b;
    
    if ($yyyymm >= 200912 && $yyyymm <= 201003) {
        $rui_l_pother = $rui_l_pother + 76191;
    }
    if ($yyyymm >= 201001 && $yyyymm <= 201003) {
        $rui_l_pother = $rui_l_pother - 58250 - 722;
    }
    $rui_l_pother = $rui_l_pother - $rui_s_pother_a;
    $rui_l_pother = $rui_l_pother + $rui_s_pother_sagaku;
    $rui_lh_pother = $rui_l_pother - $rui_s_pother_sagaku - $rui_b_pother_sagaku;
    $rui_lh_pother_sagaku = $rui_lh_pother;
    $rui_l_pother         = $rui_l_pother - $rui_s_pother_sagaku;     // ������Ķȳ����פ���¾���˥��αĶȳ����פ���¾���ޥ��ʥ�
    $rui_lh_pother = number_format(($rui_lh_pother / $tani), $keta);
    $rui_l_pother = number_format(($rui_l_pother / $tani), $keta);
} else {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='��˥��Ķȳ����פ���¾'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_l_pother) < 1) {
        $rui_l_pother = 0 - $rui_s_pother_sagaku;   // ��������
        $rui_lh_pother = 0;     // ��������
        $rui_lh_pother_sagaku = 0;     // ��������
    } else {
        if ($yyyymm >= 200912 && $yyyymm <= 201003) {
            $rui_l_pother = $rui_l_pother + 76191;
        }
        //$rui_l_pother = $rui_l_pother + $rui_s_pother_sagaku;
        $rui_lh_pother = $rui_l_pother - $rui_s_pother_sagaku - $rui_b_pother_sagaku;
        $rui_lh_pother_sagaku = $rui_lh_pother;
        $rui_l_pother         = $rui_l_pother - $rui_s_pother_sagaku;     // ������Ķȳ����פ���¾���˥��αĶȳ����פ���¾���ޥ��ʥ�
        $rui_lh_pother = number_format(($rui_lh_pother / $tani), $keta);
        $rui_l_pother = number_format(($rui_l_pother / $tani), $keta);
    }
}
/********** �Ķȳ����פι�� **********/
    ///// �������
$p2_s_nonope_profit_sum         = $p2_s_gyoumu_sagaku + $p2_s_swari_sagaku + $p2_s_pother_sagaku;
$p2_s_nonope_profit_sum_sagaku  = $p2_s_nonope_profit_sum;
$p2_s_nonope_profit_sum         = number_format(($p2_s_nonope_profit_sum / $tani), $keta);

$p1_s_nonope_profit_sum         = $p1_s_gyoumu_sagaku + $p1_s_swari_sagaku + $p1_s_pother_sagaku;
$p1_s_nonope_profit_sum_sagaku  = $p1_s_nonope_profit_sum;
$p1_s_nonope_profit_sum         = number_format(($p1_s_nonope_profit_sum / $tani), $keta);

$s_nonope_profit_sum            = $s_gyoumu_sagaku + $s_swari_sagaku + $s_pother_sagaku;
$s_nonope_profit_sum_sagaku     = $s_nonope_profit_sum;
$s_nonope_profit_sum            = number_format(($s_nonope_profit_sum / $tani), $keta);

$rui_s_nonope_profit_sum        = $rui_s_gyoumu_sagaku + $rui_s_swari_sagaku + $rui_s_pother_sagaku;
$rui_s_nonope_profit_sum_sagaku = $rui_s_nonope_profit_sum;
$rui_s_nonope_profit_sum        = number_format(($rui_s_nonope_profit_sum / $tani), $keta);
    ///// �Х����
$p2_b_nonope_profit_sum         = $p2_b_gyoumu_sagaku + $p2_b_swari_sagaku + $p2_b_pother_sagaku;
$p2_b_nonope_profit_sum_sagaku  = $p2_b_nonope_profit_sum;
$p2_b_nonope_profit_sum         = number_format(($p2_b_nonope_profit_sum / $tani), $keta);

$p1_b_nonope_profit_sum         = $p1_b_gyoumu_sagaku + $p1_b_swari_sagaku + $p1_b_pother_sagaku;
$p1_b_nonope_profit_sum_sagaku  = $p1_b_nonope_profit_sum;
$p1_b_nonope_profit_sum         = number_format(($p1_b_nonope_profit_sum / $tani), $keta);

$b_nonope_profit_sum            = $b_gyoumu_sagaku + $b_swari_sagaku + $b_pother_sagaku;
$b_nonope_profit_sum_sagaku     = $b_nonope_profit_sum;
$b_nonope_profit_sum            = number_format(($b_nonope_profit_sum / $tani), $keta);

$rui_b_nonope_profit_sum        = $rui_b_gyoumu_sagaku + $rui_b_swari_sagaku + $rui_b_pother_sagaku;
$rui_b_nonope_profit_sum_sagaku = $rui_b_nonope_profit_sum;
$rui_b_nonope_profit_sum        = number_format(($rui_b_nonope_profit_sum / $tani), $keta);

    ///// ����
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥��Ķȳ����׷׺Ʒ׻�'", $yyyymm);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥��Ķȳ����׷�'", $yyyymm);
}
if (getUniResult($query, $l_nonope_profit_sum) < 1) {
    $l_nonope_profit_sum = 0 - $s_nonope_profit_sum_sagaku;     // ��������
    $lh_nonope_profit_sum = 0;     // ��������
    $lh_nonope_profit_sum_sagaku = 0;     // ��������
} else {
    if ($yyyymm == 200906) {
        $l_nonope_profit_sum = $l_nonope_profit_sum + 3100900;
    } elseif ($yyyymm == 200905) {
        $l_nonope_profit_sum = $l_nonope_profit_sum - 1550450;
    } elseif ($yyyymm == 200904) {
        $l_nonope_profit_sum = $l_nonope_profit_sum - 1550450;
    }
    if ($yyyymm >= 201001) {
        $l_nonope_profit_sum = $l_nonope_profit_sum + $s_nonope_profit_sum_sagaku;
    }
    $lh_nonope_profit_sum = $l_nonope_profit_sum - $s_nonope_profit_sum_sagaku - $b_nonope_profit_sum_sagaku;
    $lh_nonope_profit_sum_sagaku = $lh_nonope_profit_sum;
    $l_nonope_profit_sum         = $l_nonope_profit_sum - $s_nonope_profit_sum_sagaku;     // ������Ķȳ����׷פ��˥��αĶȳ����׷פ��ޥ��ʥ�
    $lh_nonope_profit_sum = number_format(($lh_nonope_profit_sum / $tani), $keta);
    $l_nonope_profit_sum = number_format(($l_nonope_profit_sum / $tani), $keta);
}
    ///// ����
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥��Ķȳ����׷׺Ʒ׻�'", $p1_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥��Ķȳ����׷�'", $p1_ym);
}
if (getUniResult($query, $p1_l_nonope_profit_sum) < 1) {
    $p1_l_nonope_profit_sum = 0 - $p1_s_nonope_profit_sum_sagaku;     // ��������
    $p1_lh_nonope_profit_sum = 0;     // ��������
    $p1_lh_nonope_profit_sum_sagaku = 0;     // ��������
} else {    
    if ($p1_ym == 200906) {
        $p1_l_nonope_profit_sum = $p1_l_nonope_profit_sum + 3100900;
    } elseif ($p1_ym == 200905) {
        $p1_l_nonope_profit_sum = $p1_l_nonope_profit_sum - 1550450;
    } elseif ($p1_ym == 200904) {
        $p1_l_nonope_profit_sum = $p1_l_nonope_profit_sum - 1550450;
    }
    if ($p1_ym >= 201001) {
        $p1_l_nonope_profit_sum = $p1_l_nonope_profit_sum + $p1_s_nonope_profit_sum_sagaku;
    }
    $p1_lh_nonope_profit_sum = $p1_l_nonope_profit_sum - $p1_s_nonope_profit_sum_sagaku - $p1_b_nonope_profit_sum_sagaku;
    $p1_lh_nonope_profit_sum_sagaku = $p1_lh_nonope_profit_sum;
    $p1_l_nonope_profit_sum         = $p1_l_nonope_profit_sum - $p1_s_nonope_profit_sum_sagaku;     // ������Ķȳ����׷פ��˥��αĶȳ����׷פ��ޥ��ʥ�
    $p1_lh_nonope_profit_sum = number_format(($p1_lh_nonope_profit_sum / $tani), $keta);
    $p1_l_nonope_profit_sum = number_format(($p1_l_nonope_profit_sum / $tani), $keta);
}
    ///// ������
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥��Ķȳ����׷׺Ʒ׻�'", $p2_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥��Ķȳ����׷�'", $p2_ym);
}
if (getUniResult($query, $p2_l_nonope_profit_sum) < 1) {
    $p2_l_nonope_profit_sum = 0 - $p2_s_nonope_profit_sum_sagaku;     // ��������
    $p2_lh_nonope_profit_sum = 0;     // ��������
    $p2_lh_nonope_profit_sum_sagaku = 0;     // ��������
} else {
    if ($p2_ym == 200906) {
        $p2_l_nonope_profit_sum = $p2_l_nonope_profit_sum + 3100900;
    } elseif ($p2_ym == 200905) {
        $p2_l_nonope_profit_sum = $p2_l_nonope_profit_sum - 1550450;
    } elseif ($p2_ym == 200904) {
        $p2_l_nonope_profit_sum = $p2_l_nonope_profit_sum - 1550450;
    }
    if ($p2_ym >= 201001) {
        $p2_l_nonope_profit_sum = $p2_l_nonope_profit_sum + $p2_s_nonope_profit_sum_sagaku;
    }
    $p2_lh_nonope_profit_sum = $p2_l_nonope_profit_sum - $p2_s_nonope_profit_sum_sagaku - $p2_b_nonope_profit_sum_sagaku;
    $p2_lh_nonope_profit_sum_sagaku = $p2_lh_nonope_profit_sum;
    $p2_l_nonope_profit_sum         = $p2_l_nonope_profit_sum - $p2_s_nonope_profit_sum_sagaku;     // ������Ķȳ����׷פ��˥��αĶȳ����׷פ��ޥ��ʥ�
    $p2_lh_nonope_profit_sum = number_format(($p2_lh_nonope_profit_sum / $tani), $keta);
    $p2_l_nonope_profit_sum = number_format(($p2_l_nonope_profit_sum / $tani), $keta);
}
    ///// �����߷�
if ($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='��˥��Ķȳ����׷׺Ʒ׻�'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_l_nonope_profit_sum) < 1) {
        $rui_l_nonope_profit_sum = 0 - $rui_s_nonope_profit_sum_sagaku;   // ��������
        $rui_lh_nonope_profit_sum = 0;     // ��������
        $rui_lh_nonope_profit_sum_sagaku = 0;     // ��������
    } else {
        //$rui_l_nonope_profit_sum      = $rui_l_nonope_profit_sum - $rui_s_nonope_profit_sum;
        $rui_lh_nonope_profit_sum = $rui_l_nonope_profit_sum - $rui_b_nonope_profit_sum_sagaku;// - $rui_s_nonope_profit_sum_sagaku - $rui_b_nonope_profit_sum_sagaku;
        $rui_lh_nonope_profit_sum_sagaku = $rui_lh_nonope_profit_sum;
        //$rui_l_nonope_profit_sum         = $rui_l_nonope_profit_sum - $rui_s_nonope_profit_sum_sagaku;     // ������Ķȳ����׷פ��˥��αĶȳ����׷פ��ޥ��ʥ�
        $rui_l_nonope_profit_sum_temp = $rui_l_nonope_profit_sum;         // �о����׷׻���
        $rui_lh_nonope_profit_sum = number_format(($rui_lh_nonope_profit_sum / $tani), $keta);
        $rui_l_nonope_profit_sum      = number_format(($rui_l_nonope_profit_sum / $tani), $keta);
    }
} elseif ($yyyymm >= 201001 && $yyyymm <= 201003) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=200904 and pl_bs_ym<=200912 and note='��˥��Ķȳ����׷�'");
    if (getUniResult($query, $rui_l_nonope_profit_sum_a) < 1) {
        $rui_l_nonope_profit_sum_a = 0;                          // ��������
    }
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=201001 and pl_bs_ym<=%d and note='��˥��Ķȳ����׷׺Ʒ׻�'", $yyyymm);
    if (getUniResult($query, $rui_l_nonope_profit_sum_b) < 1) {
        $rui_l_nonope_profit_sum_b = 0;                          // ��������
    }
    $rui_l_nonope_profit_sum      = $rui_l_nonope_profit_sum_a + $rui_l_nonope_profit_sum_b;
    $rui_l_nonope_profit_sum      = $rui_l_nonope_profit_sum + $rui_s_nonope_profit_sum_sagaku;
    $rui_l_nonope_profit_sum      = $rui_l_nonope_profit_sum - $rui_s_gyoumu_a - $rui_s_swari_a - $rui_s_pother_a;
    $rui_lh_nonope_profit_sum = $rui_l_nonope_profit_sum - $rui_s_nonope_profit_sum_sagaku - $rui_b_nonope_profit_sum_sagaku;
    $rui_lh_nonope_profit_sum_sagaku = $rui_lh_nonope_profit_sum;
    $rui_l_nonope_profit_sum         = $rui_l_nonope_profit_sum - $rui_s_nonope_profit_sum_sagaku;     // ������Ķȳ����׷פ��˥��αĶȳ����׷פ��ޥ��ʥ�
    $rui_l_nonope_profit_sum_temp = $rui_l_nonope_profit_sum;         // �о����׷׻���
    $rui_lh_nonope_profit_sum = number_format(($rui_lh_nonope_profit_sum / $tani), $keta);
    $rui_l_nonope_profit_sum      = number_format(($rui_l_nonope_profit_sum / $tani), $keta);
} else {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='��˥��Ķȳ����׷�'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_l_nonope_profit_sum) < 1) {
        $rui_l_nonope_profit_sum = 0 - $rui_s_nonope_profit_sum_sagaku;   // ��������
        $rui_lh_nonope_profit_sum = 0;     // ��������
        $rui_lh_nonope_profit_sum_sagaku = 0;     // ��������
    } else {
        //$rui_l_nonope_profit_sum = $rui_l_nonope_profit_sum - $rui_s_nonope_profit_sum_sagaku;
        $rui_lh_nonope_profit_sum = $rui_l_nonope_profit_sum - $rui_s_nonope_profit_sum_sagaku - $rui_b_nonope_profit_sum_sagaku;
        $rui_lh_nonope_profit_sum_sagaku = $rui_lh_nonope_profit_sum;
        $rui_l_nonope_profit_sum         = $rui_l_nonope_profit_sum - $rui_s_nonope_profit_sum_sagaku;     // ������Ķȳ����׷פ��˥��αĶȳ����׷פ��ޥ��ʥ�
        $rui_lh_nonope_profit_sum = number_format(($rui_lh_nonope_profit_sum / $tani), $keta);
        $rui_l_nonope_profit_sum = number_format(($rui_l_nonope_profit_sum / $tani), $keta);
    }
}
/********** �Ķȳ����Ѥλ�ʧ��© **********/
    ///// ����
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ʧ��©�Ʒ׻�'", $yyyymm);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ʧ��©'", $yyyymm);
}
if (getUniResult($query, $s_srisoku) < 1) {
    $s_srisoku        = 0;                      // ��������
    $s_srisoku_sagaku = 0;
} else {
    $s_srisoku_sagaku = $s_srisoku;
    $s_srisoku        = number_format(($s_srisoku / $tani), $keta);
}
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�Х�����ʧ��©�Ʒ׻�'", $yyyymm);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�Х�����ʧ��©'", $yyyymm);
}
if (getUniResult($query, $b_srisoku) < 1) {
    $b_srisoku        = 0;                      // ��������
    $b_srisoku_sagaku = 0;
} else {
    $b_srisoku_sagaku = $b_srisoku;
    $b_srisoku        = number_format(($b_srisoku / $tani), $keta);
}
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥���ʧ��©�Ʒ׻�'", $yyyymm);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥���ʧ��©'", $yyyymm);
}
if (getUniResult($query, $l_srisoku) < 1) {
    $l_srisoku = 0 - $s_srisoku_sagaku;     // ��������
    $lh_srisoku = 0;     // ��������
    $lh_srisoku_sagaku = 0;     // ��������
} else {
    if ($yyyymm >= 201001) {
        $l_srisoku = $l_srisoku + $s_srisoku_sagaku;
    }
    $lh_srisoku = $l_srisoku - $s_srisoku_sagaku - $b_srisoku_sagaku;
    $lh_srisoku_sagaku = $lh_srisoku;
    $l_srisoku         = $l_srisoku - $s_srisoku_sagaku;     // �������ʧ��©���˥��λ�ʧ��©���ޥ��ʥ�
    $lh_srisoku = number_format(($lh_srisoku / $tani), $keta);
    $l_srisoku = number_format(($l_srisoku / $tani), $keta);
}
    ///// ����
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ʧ��©�Ʒ׻�'", $p1_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ʧ��©'", $p1_ym);
}
if (getUniResult($query, $p1_s_srisoku) < 1) {
    $p1_s_srisoku        = 0;                      // ��������
    $p1_s_srisoku_sagaku = 0;
} else {
    $p1_s_srisoku_sagaku = $p1_s_srisoku;
    $p1_s_srisoku        = number_format(($p1_s_srisoku / $tani), $keta);
}
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�Х�����ʧ��©�Ʒ׻�'", $p1_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�Х�����ʧ��©'", $p1_ym);
}
if (getUniResult($query, $p1_b_srisoku) < 1) {
    $p1_b_srisoku        = 0;                      // ��������
    $p1_b_srisoku_sagaku = 0;
} else {
    $p1_b_srisoku_sagaku = $p1_b_srisoku;
    $p1_b_srisoku        = number_format(($p1_b_srisoku / $tani), $keta);
}
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥���ʧ��©�Ʒ׻�'", $p1_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥���ʧ��©'", $p1_ym);
}
if (getUniResult($query, $p1_l_srisoku) < 1) {
    $p1_l_srisoku = 0 - $p1_s_srisoku_sagaku;     // ��������
    $p1_lh_srisoku = 0;     // ��������
    $p1_lh_srisoku_sagaku = 0;     // ��������
} else {
    if ($p1_ym >= 201001) {
        $p1_l_srisoku = $p1_l_srisoku + $p1_s_srisoku_sagaku;
    }
    $p1_lh_srisoku = $p1_l_srisoku - $p1_s_srisoku_sagaku - $p1_b_srisoku_sagaku;
    $p1_lh_srisoku_sagaku = $p1_lh_srisoku;
    $p1_l_srisoku         = $p1_l_srisoku - $p1_s_srisoku_sagaku;     // �������ʧ��©���˥��λ�ʧ��©���ޥ��ʥ�
    $p1_lh_srisoku = number_format(($p1_lh_srisoku / $tani), $keta);
    $p1_l_srisoku = number_format(($p1_l_srisoku / $tani), $keta);
}
    ///// ������
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ʧ��©�Ʒ׻�'", $p2_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ʧ��©'", $p2_ym);
}
if (getUniResult($query, $p2_s_srisoku) < 1) {
    $p2_s_srisoku        = 0;                      // ��������
    $p2_s_srisoku_sagaku = 0;
} else {
    $p2_s_srisoku_sagaku = $p2_s_srisoku;
    $p2_s_srisoku        = number_format(($p2_s_srisoku / $tani), $keta);
}
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�Х�����ʧ��©�Ʒ׻�'", $p2_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�Х�����ʧ��©'", $p2_ym);
}
if (getUniResult($query, $p2_b_srisoku) < 1) {
    $p2_b_srisoku        = 0;                      // ��������
    $p2_b_srisoku_sagaku = 0;
} else {
    $p2_b_srisoku_sagaku = $p2_b_srisoku;
    $p2_b_srisoku        = number_format(($p2_b_srisoku / $tani), $keta);
}
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥���ʧ��©�Ʒ׻�'", $p2_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥���ʧ��©'", $p2_ym);
}
if (getUniResult($query, $p2_l_srisoku) < 1) {
    $p2_l_srisoku = 0 - $p2_s_srisoku_sagaku;     // ��������
    $p2_lh_srisoku = 0;     // ��������
    $p2_lh_srisoku_sagaku = 0;     // ��������
} else {
    if ($p2_ym >= 201001) {
        $p2_l_srisoku = $p2_l_srisoku + $p2_s_srisoku_sagaku;
    }
    $p2_lh_srisoku = $p2_l_srisoku - $p2_s_srisoku_sagaku - $p2_b_srisoku_sagaku;
    $p2_lh_srisoku_sagaku = $p2_lh_srisoku;
    $p2_l_srisoku         = $p2_l_srisoku - $p2_s_srisoku_sagaku;     // �������ʧ��©���˥��λ�ʧ��©���ޥ��ʥ�
    $p2_lh_srisoku = number_format(($p2_lh_srisoku / $tani), $keta);
    $p2_l_srisoku = number_format(($p2_l_srisoku / $tani), $keta);
}
    ///// �����߷�
if ($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���ʧ��©�Ʒ׻�'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_s_srisoku) < 1) {
        $rui_s_srisoku = 0;                           // ��������
        $rui_s_srisoku_sagaku = 0;
    } else {
        $rui_s_srisoku_sagaku = $rui_s_srisoku;
        $rui_s_srisoku = number_format(($rui_s_srisoku / $tani), $keta);
    }
} elseif ($yyyymm >= 201001 && $yyyymm <= 201003) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=200904 and pl_bs_ym<=200912 and note='���ʧ��©'");
    if (getUniResult($query, $rui_s_srisoku_a) < 1) {
        $rui_s_srisoku_a = 0;                          // ��������
    }
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=201001 and pl_bs_ym<=%d and note='���ʧ��©�Ʒ׻�'", $yyyymm);
    if (getUniResult($query, $rui_s_srisoku_b) < 1) {
        $rui_s_srisoku_b = 0;                          // ��������
    }
    $rui_s_srisoku = $rui_s_srisoku_a + $rui_s_srisoku_b;
    $rui_s_srisoku_sagaku = $rui_s_srisoku;
    $rui_s_srisoku = number_format(($rui_s_srisoku / $tani), $keta);
} else {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='���ʧ��©'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_s_srisoku) < 1) {
        $rui_s_srisoku        = 0;                  // ��������
        $rui_s_srisoku_sagaku = 0;
    } else {
        $rui_s_srisoku_sagaku = $rui_s_srisoku;
        $rui_s_srisoku = number_format(($rui_s_srisoku / $tani), $keta);
    }
}
if ($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='�Х�����ʧ��©�Ʒ׻�'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_b_srisoku) < 1) {
        $rui_b_srisoku = 0;    // ��������
        $rui_b_srisoku_sagaku = 0;
    } else {
        $rui_b_srisoku_sagaku = $rui_b_srisoku;
        $rui_b_srisoku = number_format(($rui_b_srisoku / $tani), $keta);
    }
} elseif ($yyyymm >= 201001 && $yyyymm <= 201003) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=200904 and pl_bs_ym<=200912 and note='�Х�����ʧ��©'");
    if (getUniResult($query, $rui_b_srisoku_a) < 1) {
        $rui_b_srisoku_a = 0;                          // ��������
    }
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=201001 and pl_bs_ym<=%d and note='�Х�����ʧ��©�Ʒ׻�'", $yyyymm);
    if (getUniResult($query, $rui_b_srisoku_b) < 1) {
        $rui_b_srisoku_b = 0;                          // ��������
    }
    $rui_b_srisoku = $rui_b_srisoku_a + $rui_b_srisoku_b;
    $rui_b_srisoku_sagaku = $rui_b_srisoku;
    $rui_b_srisoku = number_format(($rui_b_srisoku / $tani), $keta);
} else {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='�Х�����ʧ��©'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_b_srisoku) < 1) {
        $rui_b_srisoku = 0;    // ��������
        $rui_b_srisoku_sagaku = 0;
    } else {
        $rui_b_srisoku_sagaku = $rui_b_srisoku;
        $rui_b_srisoku = number_format(($rui_b_srisoku / $tani), $keta);
    }
}
if ($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='��˥���ʧ��©�Ʒ׻�'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_l_srisoku) < 1) {
        $rui_l_srisoku = 0 - $rui_s_srisoku_sagaku;   // ��������
        $rui_lh_srisoku = 0;     // ��������
        $rui_lh_srisoku_sagaku = 0;     // ��������
    } else {
        $rui_l_srisoku = $rui_l_srisoku + $rui_s_srisoku_sagaku;
        $rui_lh_srisoku = $rui_l_srisoku - $rui_s_srisoku_sagaku - $rui_b_srisoku_sagaku;
        $rui_lh_srisoku_sagaku = $rui_lh_srisoku;
        $rui_l_srisoku         = $rui_l_srisoku - $rui_s_srisoku_sagaku;     // �������ʧ��©���˥��λ�ʧ��©���ޥ��ʥ�
        $rui_lh_srisoku = number_format(($rui_lh_srisoku / $tani), $keta);
        $rui_l_srisoku = number_format(($rui_l_srisoku / $tani), $keta);
    }
} elseif ($yyyymm >= 201001 && $yyyymm <= 201003) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=200904 and pl_bs_ym<=200912 and note='��˥���ʧ��©'");
    if (getUniResult($query, $rui_l_srisoku_a) < 1) {
        $rui_l_srisoku_a = 0;                          // ��������
    }
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=201001 and pl_bs_ym<=%d and note='��˥���ʧ��©�Ʒ׻�'", $yyyymm);
    if (getUniResult($query, $rui_l_srisoku_b) < 1) {
        $rui_l_srisoku_b = 0;                          // ��������
    }
    $rui_l_srisoku = $rui_l_srisoku_a + $rui_l_srisoku_b;
    $rui_l_srisoku = $rui_l_srisoku - $rui_s_srisoku_a;
    $rui_l_srisoku = $rui_l_srisoku + $rui_s_srisoku_sagaku;
    $rui_lh_srisoku = $rui_l_srisoku - $rui_s_srisoku_sagaku - $rui_b_srisoku_sagaku;
    $rui_lh_srisoku_sagaku = $rui_lh_srisoku;
    $rui_l_srisoku         = $rui_l_srisoku - $rui_s_srisoku_sagaku;     // �������ʧ��©���˥��λ�ʧ��©���ޥ��ʥ�
    $rui_lh_srisoku = number_format(($rui_lh_srisoku / $tani), $keta);
    $rui_l_srisoku = number_format(($rui_l_srisoku / $tani), $keta);
} else {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='��˥���ʧ��©'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_l_srisoku) < 1) {
        $rui_l_srisoku = 0 - $rui_s_srisoku_sagaku;   // ��������
        $rui_lh_srisoku = 0;     // ��������
        $rui_lh_srisoku_sagaku = 0;     // ��������
    } else {
        //$rui_l_srisoku = $rui_l_srisoku + $rui_s_srisoku_sagaku;
        $rui_lh_srisoku = $rui_l_srisoku - $rui_s_srisoku_sagaku - $rui_b_srisoku_sagaku;
        $rui_lh_srisoku_sagaku = $rui_lh_srisoku;
        $rui_l_srisoku         = $rui_l_srisoku - $rui_s_srisoku_sagaku;     // �������ʧ��©���˥��λ�ʧ��©���ޥ��ʥ�
        $rui_lh_srisoku = number_format(($rui_lh_srisoku / $tani), $keta);
        $rui_l_srisoku = number_format(($rui_l_srisoku / $tani), $keta);
    }
}
/********** �Ķȳ����ѤΤ���¾ **********/
    ///// ����
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��Ķȳ����Ѥ���¾�Ʒ׻�'", $yyyymm);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��Ķȳ����Ѥ���¾'", $yyyymm);
}
if (getUniResult($query, $s_lother) < 1) {
    $s_lother        = 0;                       // ��������
    $s_lother_sagaku = 0;
} else {
    $s_lother_sagaku = $s_lother;
    $s_lother        = number_format(($s_lother / $tani), $keta);
}
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�Х����Ķȳ����Ѥ���¾�Ʒ׻�'", $yyyymm);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�Х����Ķȳ����Ѥ���¾'", $yyyymm);
}
if (getUniResult($query, $b_lother) < 1) {
    $b_lother        = 0;                       // ��������
    $b_lother_sagaku = 0;
} else {
    $b_lother_sagaku = $b_lother;
    $b_lother        = number_format(($b_lother / $tani), $keta);
}
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥��Ķȳ����Ѥ���¾�Ʒ׻�'", $yyyymm);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥��Ķȳ����Ѥ���¾'", $yyyymm);
}
if (getUniResult($query, $l_lother) < 1) {
    $l_lother = 0 - $s_lother_sagaku;     // ��������
    $lh_lother = 0;     // ��������
    $lh_lother_sagaku = 0;     // ��������
} else {
    if ($yyyymm >= 201001) {
        $l_lother = $l_lother + $s_lother_sagaku;
    }
    $lh_lother = $l_lother - $s_lother_sagaku - $b_lother_sagaku;
    $lh_lother_sagaku = $lh_lother;
    $l_lother         = $l_lother - $s_lother_sagaku;     // ������Ķȳ����Ѥ���¾���˥��αĶȳ����Ѥ���¾���ޥ��ʥ�
    $lh_lother = number_format(($lh_lother / $tani), $keta);
    $l_lother = number_format(($l_lother / $tani), $keta);
}
    ///// ����
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��Ķȳ����Ѥ���¾�Ʒ׻�'", $p1_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��Ķȳ����Ѥ���¾'", $p1_ym);
}
if (getUniResult($query, $p1_s_lother) < 1) {
    $p1_s_lother        = 0;                       // ��������
    $p1_s_lother_sagaku = 0;
} else {
    $p1_s_lother_sagaku = $p1_s_lother;
    $p1_s_lother        = number_format(($p1_s_lother / $tani), $keta);
}
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�Х����Ķȳ����Ѥ���¾�Ʒ׻�'", $p1_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�Х����Ķȳ����Ѥ���¾'", $p1_ym);
}
if (getUniResult($query, $p1_b_lother) < 1) {
    $p1_b_lother        = 0;                       // ��������
    $p1_b_lother_sagaku = 0;
} else {
    $p1_b_lother_sagaku = $p1_b_lother;
    $p1_b_lother        = number_format(($p1_b_lother / $tani), $keta);
}
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥��Ķȳ����Ѥ���¾�Ʒ׻�'", $p1_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥��Ķȳ����Ѥ���¾'", $p1_ym);
}
if (getUniResult($query, $p1_l_lother) < 1) {
    $p1_l_lother = 0 - $p1_s_lother_sagaku;     // ��������
    $p1_lh_lother = 0;     // ��������
    $p1_lh_lother_sagaku = 0;     // ��������
} else {
    if ($p1_ym >= 201001) {
        $p1_l_lother = $p1_l_lother + $p1_s_lother_sagaku;
    }
    $p1_lh_lother = $p1_l_lother - $p1_s_lother_sagaku - $p1_b_lother_sagaku;
    $p1_lh_lother_sagaku = $p1_lh_lother;
    $p1_l_lother         = $p1_l_lother - $p1_s_lother_sagaku;     // ������Ķȳ����Ѥ���¾���˥��αĶȳ����Ѥ���¾���ޥ��ʥ�
    $p1_lh_lother = number_format(($p1_lh_lother / $tani), $keta);
    $p1_l_lother = number_format(($p1_l_lother / $tani), $keta);
}
    ///// ������
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��Ķȳ����Ѥ���¾�Ʒ׻�'", $p2_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��Ķȳ����Ѥ���¾'", $p2_ym);
}
if (getUniResult($query, $p2_s_lother) < 1) {
    $p2_s_lother        = 0;                       // ��������
    $p2_s_lother_sagaku = 0;
} else {
    $p2_s_lother_sagaku = $p2_s_lother;
    $p2_s_lother        = number_format(($p2_s_lother / $tani), $keta);
}
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�Х����Ķȳ����Ѥ���¾�Ʒ׻�'", $p2_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='�Х����Ķȳ����Ѥ���¾'", $p2_ym);
}
if (getUniResult($query, $p2_b_lother) < 1) {
    $p2_b_lother        = 0;                       // ��������
    $p2_b_lother_sagaku = 0;
} else {
    $p2_b_lother_sagaku = $p2_b_lother;
    $p2_b_lother        = number_format(($p2_b_lother / $tani), $keta);
}
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥��Ķȳ����Ѥ���¾�Ʒ׻�'", $p2_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥��Ķȳ����Ѥ���¾'", $p2_ym);
}
if (getUniResult($query, $p2_l_lother) < 1) {
    $p2_l_lother = 0 - $p1_s_lother_sagaku;     // ��������
    $p2_lh_lother = 0;     // ��������
    $p2_lh_lother_sagaku = 0;     // ��������
} else {
    if ($p2_ym >= 201001) {
        $p2_l_lother = $p2_l_lother + $p2_s_lother_sagaku;
    }
    $p2_lh_lother = $p2_l_lother - $p2_s_lother_sagaku - $p2_b_lother_sagaku;
    $p2_lh_lother_sagaku = $p2_lh_lother;
    $p2_l_lother         = $p2_l_lother - $p2_s_lother_sagaku;     // ������Ķȳ����Ѥ���¾���˥��αĶȳ����Ѥ���¾���ޥ��ʥ�
    $p2_lh_lother = number_format(($p2_lh_lother / $tani), $keta);
    $p2_l_lother = number_format(($p2_l_lother / $tani), $keta);
}
    ///// �����߷�
if ($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='��Ķȳ����Ѥ���¾�Ʒ׻�'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_s_lother) < 1) {
        $rui_s_lother = 0;                           // ��������
        $rui_s_lother_sagaku = 0;
    } else {
        $rui_s_lother_sagaku = $rui_s_lother;
        $rui_s_lother = number_format(($rui_s_lother / $tani), $keta);
    }
} elseif ($yyyymm >= 201001 && $yyyymm <= 201003) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=200904 and pl_bs_ym<=200912 and note='��Ķȳ����Ѥ���¾'");
    if (getUniResult($query, $rui_s_lother_a) < 1) {
        $rui_s_lother_a = 0;                          // ��������
    }
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=201001 and pl_bs_ym<=%d and note='��Ķȳ����Ѥ���¾�Ʒ׻�'", $yyyymm);
    if (getUniResult($query, $rui_s_lother_b) < 1) {
        $rui_s_lother_b = 0;                          // ��������
    }
    $rui_s_lother = $rui_s_lother_a + $rui_s_lother_b;
    $rui_s_lother_sagaku = $rui_s_lother;
    $rui_s_lother = number_format(($rui_s_lother / $tani), $keta);
} else {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='��Ķȳ����Ѥ���¾'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_s_lother) < 1) {
        $rui_s_lother        = 0;                   // ��������
        $rui_s_lother_sagaku = 0;
    } else {
        $rui_s_lother_sagaku = $rui_s_lother;
        $rui_s_lother        = number_format(($rui_s_lother / $tani), $keta);
    }
}
if ($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='�Х����Ķȳ����Ѥ���¾�Ʒ׻�'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_b_lother) < 1) {
        $rui_b_lother = 0;    // ��������
        $rui_b_lother_sagaku = 0;
    } else {
        $rui_b_lother_sagaku = $rui_b_lother;
        $rui_b_lother = number_format(($rui_b_lother / $tani), $keta);
    }
} elseif ($yyyymm >= 201001 && $yyyymm <= 201003) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=200904 and pl_bs_ym<=200912 and note='�Х����Ķȳ����Ѥ���¾'");
    if (getUniResult($query, $rui_b_lother_a) < 1) {
        $rui_b_lother_a = 0;                          // ��������
    }
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=201001 and pl_bs_ym<=%d and note='�Х����Ķȳ����Ѥ���¾�Ʒ׻�'", $yyyymm);
    if (getUniResult($query, $rui_b_lother_b) < 1) {
        $rui_b_lother_b = 0;                          // ��������
    }
    $rui_b_lother = $rui_b_lother_a + $rui_b_lother_b;
    $rui_b_lother_sagaku = $rui_b_lother;
    $rui_b_lother = number_format(($rui_b_lother / $tani), $keta);
} else {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='�Х����Ķȳ����Ѥ���¾'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_b_lother) < 1) {
        $rui_b_lother = 0;    // ��������
        $rui_b_lother_sagaku = 0;
    } else {
        $rui_b_lother_sagaku = $rui_b_lother;
        $rui_b_lother = number_format(($rui_b_lother / $tani), $keta);
    }
}
if ($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='��˥��Ķȳ����Ѥ���¾�Ʒ׻�'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_l_lother) < 1) {
        $rui_l_lother = 0 - $rui_s_lother_sagaku;   // ��������
        $rui_lh_lother = 0;     // ��������
        $rui_lh_lother_sagaku = 0;     // ��������
    } else {
        $rui_l_lother = $rui_l_lother + $rui_s_lother_sagaku;
        $rui_lh_lother = $rui_l_lother - $rui_s_lother_sagaku - $rui_b_lother_sagaku;
        $rui_lh_lother_sagaku = $rui_lh_lother;
        $rui_l_lother         = $rui_l_lother - $rui_s_lother_sagaku;     // ������Ķȳ����Ѥ���¾���˥��αĶȳ����Ѥ���¾���ޥ��ʥ�
        $rui_lh_lother = number_format(($rui_lh_lother / $tani), $keta);
        $rui_l_lother = number_format(($rui_l_lother / $tani), $keta);
    }
} elseif ($yyyymm >= 201001 && $yyyymm <= 201003) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=200904 and pl_bs_ym<=200912 and note='��˥��Ķȳ����Ѥ���¾'");
    if (getUniResult($query, $rui_l_lother_a) < 1) {
        $rui_l_lother_a = 0;                          // ��������
    }
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=201001 and pl_bs_ym<=%d and note='��˥��Ķȳ����Ѥ���¾�Ʒ׻�'", $yyyymm);
    if (getUniResult($query, $rui_l_lother_b) < 1) {
        $rui_l_lother_b = 0;                          // ��������
    }
    $rui_l_lother = $rui_l_lother_a + $rui_l_lother_b;
    $rui_l_lother = $rui_l_lother - $rui_s_lother_a;
    $rui_l_lother = $rui_l_lother + $rui_s_lother_sagaku;
    $rui_lh_lother = $rui_l_lother - $rui_s_lother_sagaku - $rui_b_lother_sagaku;
    $rui_lh_lother_sagaku = $rui_lh_lother;
    $rui_l_lother         = $rui_l_lother - $rui_s_lother_sagaku;     // ������Ķȳ����Ѥ���¾���˥��αĶȳ����Ѥ���¾���ޥ��ʥ�
    $rui_lh_lother = number_format(($rui_lh_lother / $tani), $keta);
    $rui_l_lother = number_format(($rui_l_lother / $tani), $keta);
} else {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='��˥��Ķȳ����Ѥ���¾'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_l_lother) < 1) {
        $rui_l_lother = 0 - $rui_s_lother_sagaku;   // ��������
        $rui_lh_lother = 0;     // ��������
        $rui_lh_lother_sagaku = 0;     // ��������
    } else {
        //$rui_l_lother = $rui_l_lother + $rui_s_lother_sagaku;
        $rui_lh_lother = $rui_l_lother - $rui_s_lother_sagaku - $rui_b_lother_sagaku;
        $rui_lh_lother_sagaku = $rui_lh_lother;
        $rui_l_lother         = $rui_l_lother - $rui_s_lother_sagaku;     // ������Ķȳ����Ѥ���¾���˥��αĶȳ����Ѥ���¾���ޥ��ʥ�
        $rui_lh_lother = number_format(($rui_lh_lother / $tani), $keta);
        $rui_l_lother = number_format(($rui_l_lother / $tani), $keta);
    }
}
/********** �Ķȳ����Ѥι�� **********/
    ///// �������
$p2_s_nonope_loss_sum         = $p2_s_srisoku_sagaku + $p2_s_lother_sagaku;
$p2_s_nonope_loss_sum_sagaku  = $p2_s_nonope_loss_sum;
$p2_s_nonope_loss_sum         = number_format(($p2_s_nonope_loss_sum / $tani), $keta);

$p1_s_nonope_loss_sum         = $p1_s_srisoku_sagaku + $p1_s_lother_sagaku;
$p1_s_nonope_loss_sum_sagaku  = $p1_s_nonope_loss_sum;
$p1_s_nonope_loss_sum         = number_format(($p1_s_nonope_loss_sum / $tani), $keta);

$s_nonope_loss_sum            = $s_srisoku_sagaku + $s_lother_sagaku;
$s_nonope_loss_sum_sagaku     = $s_nonope_loss_sum;
$s_nonope_loss_sum            = number_format(($s_nonope_loss_sum / $tani), $keta);

$rui_s_nonope_loss_sum        = $rui_s_srisoku_sagaku + $rui_s_lother_sagaku;
$rui_s_nonope_loss_sum_sagaku = $rui_s_nonope_loss_sum;
$rui_s_nonope_loss_sum        = number_format(($rui_s_nonope_loss_sum / $tani), $keta);
    ///// �Х����
$p2_b_nonope_loss_sum         = $p2_b_srisoku_sagaku + $p2_b_lother_sagaku;
$p2_b_nonope_loss_sum_sagaku  = $p2_b_nonope_loss_sum;
$p2_b_nonope_loss_sum         = number_format(($p2_b_nonope_loss_sum / $tani), $keta);

$p1_b_nonope_loss_sum         = $p1_b_srisoku_sagaku + $p1_b_lother_sagaku;
$p1_b_nonope_loss_sum_sagaku  = $p1_b_nonope_loss_sum;
$p1_b_nonope_loss_sum         = number_format(($p1_b_nonope_loss_sum / $tani), $keta);

$b_nonope_loss_sum            = $b_srisoku_sagaku + $b_lother_sagaku;
$b_nonope_loss_sum_sagaku     = $b_nonope_loss_sum;
$b_nonope_loss_sum            = number_format(($b_nonope_loss_sum / $tani), $keta);

$rui_b_nonope_loss_sum        = $rui_b_srisoku_sagaku + $rui_b_lother_sagaku;
$rui_b_nonope_loss_sum_sagaku = $rui_b_nonope_loss_sum;
$rui_b_nonope_loss_sum        = number_format(($rui_b_nonope_loss_sum / $tani), $keta);

    ///// ����
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥��Ķȳ����ѷ׺Ʒ׻�'", $yyyymm);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥��Ķȳ����ѷ�'", $yyyymm);
}
if (getUniResult($query, $l_nonope_loss_sum) < 1) {
    $l_nonope_loss_sum = 0 - $s_nonope_loss_sum_sagaku;     // ��������
    $lh_nonope_loss_sum = 0;     // ��������
    $lh_nonope_loss_sum_sagaku = 0;     // ��������
} else {
    if ($yyyymm >= 201001) {
        $l_nonope_loss_sum = $l_nonope_loss_sum + $s_nonope_loss_sum_sagaku;
    }
    $lh_nonope_loss_sum = $l_nonope_loss_sum - $s_nonope_loss_sum_sagaku - $b_nonope_loss_sum_sagaku;
    $lh_nonope_loss_sum_sagaku = $lh_nonope_loss_sum;
    $l_nonope_loss_sum         = $l_nonope_loss_sum - $s_nonope_loss_sum_sagaku;     // ������Ķȳ����ѷפ��˥��αĶȳ����ѷפ��ޥ��ʥ�
    $lh_nonope_loss_sum = number_format(($lh_nonope_loss_sum / $tani), $keta);
    $l_nonope_loss_sum = number_format(($l_nonope_loss_sum / $tani), $keta);
}
    ///// ����
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥��Ķȳ����ѷ׺Ʒ׻�'", $p1_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥��Ķȳ����ѷ�'", $p1_ym);
}
if (getUniResult($query, $p1_l_nonope_loss_sum) < 1) {
    $p1_l_nonope_loss_sum = 0 - $p1_s_nonope_loss_sum_sagaku;     // ��������
    $p1_lh_nonope_loss_sum = 0;     // ��������
    $p1_lh_nonope_loss_sum_sagaku = 0;     // ��������
} else {
    if ($p1_ym >= 201001) {
        $p1_l_nonope_loss_sum = $p1_l_nonope_loss_sum + $p1_s_nonope_loss_sum_sagaku;
    }
    $p1_lh_nonope_loss_sum = $p1_l_nonope_loss_sum - $p1_s_nonope_loss_sum_sagaku - $p1_b_nonope_loss_sum_sagaku;
    $p1_lh_nonope_loss_sum_sagaku = $p1_lh_nonope_loss_sum;
    $p1_l_nonope_loss_sum         = $p1_l_nonope_loss_sum - $p1_s_nonope_loss_sum_sagaku;     // ������Ķȳ����ѷפ��˥��αĶȳ����ѷפ��ޥ��ʥ�
    $p1_lh_nonope_loss_sum = number_format(($p1_lh_nonope_loss_sum / $tani), $keta);
    $p1_l_nonope_loss_sum = number_format(($p1_l_nonope_loss_sum / $tani), $keta);
}
    ///// ������
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥��Ķȳ����ѷ׺Ʒ׻�'", $p2_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥��Ķȳ����ѷ�'", $p2_ym);
}
if (getUniResult($query, $p2_l_nonope_loss_sum) < 1) {
    $p2_l_nonope_loss_sum = 0 - $p2_s_nonope_loss_sum_sagaku;     // ��������
    $p2_lh_nonope_loss_sum = 0;     // ��������
    $p2_lh_nonope_loss_sum_sagaku = 0;     // ��������
} else {
    if ($p2_ym >= 201001) {
        $p2_l_nonope_loss_sum = $p2_l_nonope_loss_sum + $p2_s_nonope_loss_sum_sagaku;
    }
    $p2_lh_nonope_loss_sum = $p2_l_nonope_loss_sum - $p2_s_nonope_loss_sum_sagaku - $p2_b_nonope_loss_sum_sagaku;
    $p2_lh_nonope_loss_sum_sagaku = $p2_lh_nonope_loss_sum;
    $p2_l_nonope_loss_sum         = $p2_l_nonope_loss_sum - $p2_s_nonope_loss_sum_sagaku;     // ������Ķȳ����ѷפ��˥��αĶȳ����ѷפ��ޥ��ʥ�
    $p2_lh_nonope_loss_sum = number_format(($p2_lh_nonope_loss_sum / $tani), $keta);
    $p2_l_nonope_loss_sum = number_format(($p2_l_nonope_loss_sum / $tani), $keta);
}
    ///// �����߷�
if ($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='��˥��Ķȳ����ѷ׺Ʒ׻�'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_l_nonope_loss_sum) < 1) {
        $rui_l_nonope_loss_sum = 0 - $rui_s_nonope_loss_sum_sagaku;   // ��������
        $rui_lh_nonope_loss_sum = 0;     // ��������
        $rui_lh_nonope_loss_sum_sagaku = 0;     // ��������
    } else {
        $rui_l_nonope_loss_sum = $rui_l_nonope_loss_sum + $rui_s_nonope_loss_sum_sagaku;
        $rui_lh_nonope_loss_sum = $rui_l_nonope_loss_sum - $rui_s_nonope_loss_sum_sagaku - $rui_b_nonope_loss_sum_sagaku;
        $rui_lh_nonope_loss_sum_sagaku = $rui_lh_nonope_loss_sum;
        $rui_l_nonope_loss_sum         = $rui_l_nonope_loss_sum - $rui_s_nonope_loss_sum_sagaku;     // ������Ķȳ����ѷפ��˥��αĶȳ����ѷפ��ޥ��ʥ�
        $rui_l_nonope_loss_sum_temp = $rui_l_nonope_loss_sum;         // �о����׷׻���
        $rui_lh_nonope_loss_sum = number_format(($rui_lh_nonope_loss_sum / $tani), $keta);
        $rui_l_nonope_loss_sum      = number_format(($rui_l_nonope_loss_sum / $tani), $keta);
    }
} elseif ($yyyymm >= 201001 && $yyyymm <= 201003) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=200904 and pl_bs_ym<=200912 and note='��˥��Ķȳ����ѷ�'");
    if (getUniResult($query, $rui_l_nonope_loss_sum_a) < 1) {
        $rui_l_nonope_loss_sum_a = 0;                          // ��������
    }
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=201001 and pl_bs_ym<=%d and note='��˥��Ķȳ����ѷ׺Ʒ׻�'", $yyyymm);
    if (getUniResult($query, $rui_l_nonope_loss_sum_b) < 1) {
        $rui_l_nonope_loss_sum_b = 0;                          // ��������
    }
    $rui_l_nonope_loss_sum      = $rui_l_nonope_loss_sum_a + $rui_l_nonope_loss_sum_b;
    $rui_l_nonope_loss_sum = $rui_l_nonope_loss_sum + $rui_s_nonope_loss_sum_sagaku;
    $rui_l_nonope_loss_sum      = $rui_l_nonope_loss_sum - $rui_s_srisoku_a - $rui_s_lother_a;
    $rui_lh_nonope_loss_sum = $rui_l_nonope_loss_sum - $rui_s_nonope_loss_sum_sagaku - $rui_b_nonope_loss_sum_sagaku;
    $rui_lh_nonope_loss_sum_sagaku = $rui_lh_nonope_loss_sum;
    $rui_l_nonope_loss_sum         = $rui_l_nonope_loss_sum - $rui_s_nonope_loss_sum_sagaku;     // ������Ķȳ����ѷפ��˥��αĶȳ����ѷפ��ޥ��ʥ�
    $rui_l_nonope_loss_sum_temp = $rui_l_nonope_loss_sum;         // �о����׷׻���
    $rui_lh_nonope_loss_sum = number_format(($rui_lh_nonope_loss_sum / $tani), $keta);
    $rui_l_nonope_loss_sum      = number_format(($rui_l_nonope_loss_sum / $tani), $keta);
} else {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='��˥��Ķȳ����ѷ�'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_l_nonope_loss_sum) < 1) {
        $rui_l_nonope_loss_sum = 0 - $rui_s_nonope_loss_sum_sagaku;   // ��������
        $rui_lh_nonope_loss_sum = 0;     // ��������
        $rui_lh_nonope_loss_sum_sagaku = 0;     // ��������
    } else {
        //$rui_l_nonope_loss_sum = $rui_l_nonope_loss_sum + $rui_s_nonope_loss_sum_sagaku;
        $rui_lh_nonope_loss_sum = $rui_l_nonope_loss_sum - $rui_s_nonope_loss_sum_sagaku - $rui_b_nonope_loss_sum_sagaku;
        $rui_lh_nonope_loss_sum_sagaku = $rui_lh_nonope_loss_sum;
        $rui_l_nonope_loss_sum         = $rui_l_nonope_loss_sum - $rui_s_nonope_loss_sum_sagaku;     // ������Ķȳ����ѷפ��˥��αĶȳ����ѷפ��ޥ��ʥ�
        $rui_lh_nonope_loss_sum = number_format(($rui_lh_nonope_loss_sum / $tani), $keta);
        $rui_l_nonope_loss_sum = number_format(($rui_l_nonope_loss_sum / $tani), $keta);
    }
}
/********** �о����� **********/
    ///// �������
$p2_s_current_profit         = $p2_s_ope_profit_sagaku + $p2_s_nonope_profit_sum_sagaku - $p2_s_nonope_loss_sum_sagaku;
$p2_s_current_profit_sagaku  = $p2_s_current_profit;
$p2_s_current_profit         = number_format(($p2_s_current_profit / $tani), $keta);

$p1_s_current_profit         = $p1_s_ope_profit_sagaku + $p1_s_nonope_profit_sum_sagaku - $p1_s_nonope_loss_sum_sagaku;
$p1_s_current_profit_sagaku  = $p1_s_current_profit;
$p1_s_current_profit         = number_format(($p1_s_current_profit / $tani), $keta);

$s_current_profit            = $s_ope_profit_sagaku + $s_nonope_profit_sum_sagaku - $s_nonope_loss_sum_sagaku;
$s_current_profit_sagaku     = $s_current_profit;
$s_current_profit            = number_format(($s_current_profit / $tani), $keta);

$rui_s_current_profit        = $rui_s_ope_profit_sagaku + $rui_s_nonope_profit_sum_sagaku - $rui_s_nonope_loss_sum_sagaku;
$rui_s_current_profit_sagaku = $rui_s_current_profit;
$rui_s_current_profit_temp   = $rui_s_current_profit;
$rui_s_current_profit        = number_format(($rui_s_current_profit / $tani), $keta);
    ///// �Х����
$p2_b_current_profit         = $p2_b_ope_profit_sagaku + $p2_b_nonope_profit_sum_sagaku - $p2_b_nonope_loss_sum_sagaku;
$p2_b_current_profit_sagaku  = $p2_b_current_profit;
$p2_b_current_profit         = number_format(($p2_b_current_profit / $tani), $keta);

$p1_b_current_profit         = $p1_b_ope_profit_sagaku + $p1_b_nonope_profit_sum_sagaku - $p1_b_nonope_loss_sum_sagaku;
$p1_b_current_profit_sagaku  = $p1_b_current_profit;
$p1_b_current_profit         = number_format(($p1_b_current_profit / $tani), $keta);

$b_current_profit            = $b_ope_profit_sagaku + $b_nonope_profit_sum_sagaku - $b_nonope_loss_sum_sagaku;
$b_current_profit_sagaku     = $b_current_profit;
$b_current_profit            = number_format(($b_current_profit / $tani), $keta);

$rui_b_current_profit        = $rui_b_ope_profit_sagaku + $rui_b_nonope_profit_sum_sagaku - $rui_b_nonope_loss_sum_sagaku;
$rui_b_current_profit_sagaku = $rui_b_current_profit;
$rui_b_current_profit        = number_format(($rui_b_current_profit / $tani), $keta);

    ///// ����
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥��о����׺Ʒ׻�'", $yyyymm);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥��о�����'", $yyyymm);
}
if (getUniResult($query, $l_current_profit) < 1) {
    $l_current_profit  = 0;       // ��������
    $lh_current_profit = 0;       // ��������
} else {
    if ($yyyymm < 201001) {
        $l_current_profit = $l_current_profit - $s_current_profit_sagaku + $l_allo_kin;
    } else {
        $l_current_profit = $l_current_profit - $s_ope_profit_sagaku + $l_allo_kin;
    }
    if ($yyyymm == 200912) {
        $l_current_profit = $l_current_profit - 182279;
    }
    if ($yyyymm >= 201001) {
        $l_current_profit = $l_current_profit - $l_kyu_kin; // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
        //$l_current_profit = $l_current_profit - 151313; // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
    }
    if ($yyyymm == 201004) {
        $l_current_profit = $l_current_profit - 255240;
    }
    // 2012/02/08 �ɲ� 2012ǯ1���� ��̳�������ʿ�в����ɸ�����Ĵ��
    if ($yyyymm == 201201) {
        $l_current_profit -=1156130;
    }
    // 2012/03/05 �ɲ� 2012ǯ1���� ��̳�������ʿ�в����ɸ�����Ĵ�� �ᤷ
    if ($yyyymm == 201202) {
        $l_current_profit +=1156130;
    }
    if ($yyyymm == 201408) {
        $l_current_profit -=229464;
    }
    $l_current_profit = $l_current_profit + $s_current_profit_sagaku;
    $lh_current_profit = $l_current_profit - $s_current_profit_sagaku - $b_current_profit_sagaku;
    $lh_current_profit_sagaku = $lh_current_profit;
    $l_current_profit = $l_current_profit - $s_current_profit_sagaku;
    $lh_current_profit = number_format(($lh_current_profit / $tani), $keta);
    $l_current_profit = number_format(($l_current_profit / $tani), $keta);
}
    ///// ����
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥��о����׺Ʒ׻�'", $p1_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥��о�����'", $p1_ym);
}
if (getUniResult($query, $p1_l_current_profit) < 1) {
    $p1_l_current_profit  = 0;       // ��������
    $p1_lh_current_profit = 0;       // ��������
} else {
    if ($p1_ym < 201001) {
        $p1_l_current_profit = $p1_l_current_profit - $p1_s_current_profit_sagaku + $p1_l_allo_kin;
    } else {
        $p1_l_current_profit = $p1_l_current_profit - $p1_s_ope_profit_sagaku + $p1_l_allo_kin;
    }
    if ($p1_ym == 200912) {
        $p1_l_current_profit = $p1_l_current_profit - 182279;
    }
    if ($p1_ym >= 201001) {
        $p1_l_current_profit = $p1_l_current_profit - $p1_l_kyu_kin;   // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
        //$p1_c_current_profit = $p1_c_current_profit - 151313;   // ź�Ĥ���ε�Ϳ��C��L��35%���������30%��ʬ
    }
    if ($p1_ym == 201004) {
        $p1_l_current_profit = $p1_l_current_profit - 255240;
    }
    // 2012/02/08 �ɲ� 2012ǯ1���� ��̳�������ʿ�в����ɸ�����Ĵ��
    if ($p1_ym == 201201) {
        $p1_l_current_profit -=1156130;
    }
    // 2012/03/05 �ɲ� 2012ǯ1���� ��̳�������ʿ�в����ɸ�����Ĵ�� �ᤷ
    if ($p1_ym == 201202) {
        $p1_l_current_profit +=1156130;
    }
    if ($p1_ym == 201408) {
        $p1_l_current_profit -=229464;
    }
    $p1_l_current_profit = $p1_l_current_profit + $p1_s_current_profit_sagaku;
    $p1_lh_current_profit = $p1_l_current_profit - $p1_s_current_profit_sagaku - $p1_b_current_profit_sagaku;
    $p1_lh_current_profit_sagaku = $p1_lh_current_profit;
    $p1_l_current_profit = $p1_l_current_profit - $p1_s_current_profit_sagaku;
    $p1_lh_current_profit = number_format(($p1_lh_current_profit / $tani), $keta);
    $p1_l_current_profit = number_format(($p1_l_current_profit / $tani), $keta);
}
    ///// ������
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥��о����׺Ʒ׻�'", $p2_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥��о�����'", $p2_ym);
}
if (getUniResult($query, $p2_l_current_profit) < 1) {
    $p2_l_current_profit  = 0;       // ��������
    $p2_lh_current_profit = 0;       // ��������
} else {
    if ($p2_ym < 201001) {
        $p2_l_current_profit = $p2_l_current_profit - $p2_s_current_profit_sagaku + $p2_l_allo_kin;
    } else {
        $p2_l_current_profit = $p2_l_current_profit - $p2_s_ope_profit_sagaku + $p2_l_allo_kin;
    }
    if ($p2_ym == 200912) {
        $p2_l_current_profit = $p2_l_current_profit - 182279;
    }
    if ($p2_ym >= 201001) {
        $p2_l_current_profit = $p2_l_current_profit - $p2_l_kyu_kin;
        //$p2_l_current_profit = $p2_l_current_profit - 151313;
    }
    if ($p2_ym == 201004) {
        $p2_l_current_profit = $p2_l_current_profit - 255240;
    }
    // 2012/02/08 �ɲ� 2012ǯ1���� ��̳�������ʿ�в����ɸ�����Ĵ��
    if ($p2_ym == 201201) {
        $p2_l_current_profit -=1156130;
    }
    // 2012/03/05 �ɲ� 2012ǯ1���� ��̳�������ʿ�в����ɸ�����Ĵ�� �ᤷ
    if ($p2_ym == 201202) {
        $p2_l_current_profit +=1156130;
    }
    if ($p2_ym == 201408) {
        $p2_l_current_profit -=229464;
    }
    $p2_l_current_profit = $p2_l_current_profit + $p2_s_current_profit_sagaku;
    $p2_lh_current_profit = $p2_l_current_profit - $p2_s_current_profit_sagaku - $p2_b_current_profit_sagaku;
    $p2_lh_current_profit_sagaku = $p2_lh_current_profit;
    $p2_l_current_profit = $p2_l_current_profit - $p2_s_current_profit_sagaku;
    $p2_lh_current_profit = number_format(($p2_lh_current_profit / $tani), $keta);
    $p2_l_current_profit = number_format(($p2_l_current_profit / $tani), $keta);
}
    ///// �����߷�
if ($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='��˥��о����׺Ʒ׻�'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_l_current_profit) < 1) {
        $rui_l_current_profit = 0 - $rui_s_current_profit_sagaku;   // ��������
        $rui_lh_current_profit = 0;     // ��������
        $rui_lh_current_profit_sagaku = 0;     // ��������
    } else {
        //$rui_l_current_profit = $rui_l_current_profit - $rui_s_current_profit_sagaku + $rui_l_allo_kin;
        // 2012/02/08 �ɲ� 2012ǯ1���� ��̳�������ʿ�в����ɸ�����Ĵ��
        if ($yyyymm >= 201201 && $yyyymm <= 201203) {
            $rui_l_current_profit -=1156130;
        }
        // 2012/03/05 �ɲ� 2012ǯ1���� ��̳�������ʿ�в����ɸ�����Ĵ�� �ᤷ
        if ($yyyymm >= 201202 && $yyyymm <= 201203) {
            $rui_l_current_profit +=1156130;
        }
        $rui_l_current_profit = $rui_l_ope_profit_temp + $rui_l_nonope_profit_sum_temp - $rui_l_nonope_loss_sum_temp;
        $rui_lh_current_profit = $rui_l_current_profit - $rui_b_current_profit_sagaku;
        $rui_lh_current_profit_sagaku = $rui_lh_current_profit;
        $rui_lh_current_profit = number_format(($rui_lh_current_profit / $tani), $keta);
        $rui_l_current_profit = number_format(($rui_l_current_profit / $tani), $keta);
    }
} elseif ($yyyymm >= 201001 && $yyyymm <= 201003) {
    //$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=200904 and pl_bs_ym<=200911 and note='��˥��о�����'");
    //if (getUniResult($query, $rui_l_current_profit_a) < 1) {
    //    $rui_l_current_profit_a = 0;                          // ��������
    //}
    //$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=200912 and pl_bs_ym<=%d and note='��˥��о����׺Ʒ׻�'", $yyyymm);
    //if (getUniResult($query, $rui_l_current_profit_b) < 1) {
    //    $rui_l_current_profit_b = 0;                          // ��������
    //}
    //$rui_l_current_profit = $rui_l_current_profit_a + $rui_l_current_profit_b;
    //if ($yyyymm >= 200912 && $yyyymm <= 201003) {
    //    $rui_l_current_profit = $rui_l_current_profit - 182279;
    //}
    //if ($yyyymm >= 201001 && $yyyymm <= 201003) {
    //    $rui_l_current_profit = $rui_l_current_profit - 151313;
    //}
    //$rui_l_current_profit = $rui_l_current_profit - $rui_s_current_profit_sagaku + $rui_l_allo_kin;
    //$rui_l_current_profit = $rui_l_current_profit - $rui_s_ope_profit_sagaku + $rui_l_allo_kin;
    
    $rui_l_current_profit = $rui_l_ope_profit_temp + $rui_l_nonope_profit_sum_temp - $rui_l_nonope_loss_sum_temp;
    $rui_lh_current_profit = $rui_l_current_profit - $rui_b_current_profit_sagaku;
    $rui_lh_current_profit_sagaku = $rui_lh_current_profit;
    $rui_lh_current_profit = number_format(($rui_lh_current_profit / $tani), $keta);
    $rui_l_current_profit = number_format(($rui_l_current_profit / $tani), $keta);
} else {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='��˥��о�����'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_l_current_profit) < 1) {
        $rui_l_current_profit = 0 - $rui_s_current_profit_sagaku;   // ��������
        $rui_lh_current_profit = 0;     // ��������
        $rui_lh_current_profit_sagaku = 0;     // ��������
    } else {
        $rui_l_current_profit = $rui_l_current_profit  + $rui_l_allo_kin;
        if ($yyyymm >= 200912 && $yyyymm <= 201003) {
            $rui_l_current_profit = $rui_l_current_profit - 182279;
        }
        $rui_lh_current_profit = $rui_l_current_profit - $rui_s_current_profit_sagaku - $rui_b_current_profit_sagaku;
        $rui_lh_current_profit_sagaku = $rui_lh_current_profit;
        //$rui_l_current_profit = $rui_l_current_profit + $rui_sc_uri_sagaku - $rui_sc_metarial_sagaku;     // ���ץ�������̣�ʹ�����ѡ�
        $rui_l_current_profit         = $rui_l_current_profit - $rui_s_current_profit_sagaku;     // ������о����פ��˥��ηо����פ��ޥ��ʥ�
        $rui_lh_current_profit = number_format(($rui_lh_current_profit / $tani), $keta);
        $rui_l_current_profit = number_format(($rui_l_current_profit / $tani), $keta);
    }
}

////////// �õ�����μ���
$query = sprintf("select comment from act_comment_history where pl_bs_ym=%d and item='��˥�bls»�׷׻���'", $yyyymm);
if (getUniResult($query,$comment_l) <= 0) {
    $comment_l = "";
}
$query = sprintf("select comment from act_comment_history where pl_bs_ym=%d and item='�Х����»�׷׻���'", $yyyymm);
if (getUniResult($query,$comment_b) <= 0) {
    $comment_b = "";
}

$query = sprintf("select comment from act_comment_history where pl_bs_ym=%d and item='����bls»�׷׻���'", $yyyymm);
if (getUniResult($query,$comment_all) <= 0) {
    $comment_all = "";
}
$query = sprintf("select comment from act_comment_history where pl_bs_ym=%d and item='����¾bls»�׷׻���'", $yyyymm);
if (getUniResult($query,$comment_other) <= 0) {
    $comment_other = "";
}
if (isset($_POST['input_data'])) {                        // ����ǡ�������Ͽ
    ///////// ���ܤȥ���ǥå����δ�Ϣ�դ�
    $item = array();
    $item[0]   = "����";
    $item[1]   = "��������ų���ê����";
    $item[2]   = "������(������)";
    $item[3]   = "ϫ̳��";
    $item[4]   = "��¤����";
    $item[5]   = "���������ų���ê����";
    $item[6]   = "��帶��";
    $item[7]   = "���������";
    $item[8]   = "�ͷ���";
    $item[9]   = "����";
    $item[10]  = "�δ���ڤӰ��̴������";
    $item[11]  = "�Ķ�����";
    $item[12]  = "��̳��������";
    $item[13]  = "�������";
    $item[14]  = "�Ķȳ����פ���¾";
    $item[15]  = "�Ķȳ����׷�";
    $item[16]  = "��ʧ��©";
    $item[17]  = "�Ķȳ����Ѥ���¾";
    $item[18]  = "�Ķȳ����ѷ�";
    $item[19]  = "�о�����";
    ///////// �ƥǡ������ݴ� ��˥�ɸ��=0 �Х����=1
    $input_data = array();
    for ($i = 0; $i < 20; $i++) {
        switch ($i) {
                case  0:                                            // ����
                    $input_data[$i][0] = $lh_uri;                   // ��˥�ɸ��
                    $input_data[$i][1] = $b_uri;                    // �Х����
                break;
                case  1:                                            // ��������ų���ê����
                    $input_data[$i][0] = $lh_invent;                // ��˥�ɸ��
                    $input_data[$i][1] = $b_invent;                 // �Х����
                break;
                case  2:                                            // ������(������)
                    $input_data[$i][0] = $lh_metarial;              // ��˥�ɸ��
                    $input_data[$i][1] = $b_metarial;               // �Х����
                break;
                case  3:                                            // ϫ̳��
                    $input_data[$i][0] = $lh_roumu;                 // ��˥�ɸ��
                    $input_data[$i][1] = $b_roumu;                  // �Х����
                break;
                case  4:                                            // ��¤����
                    $input_data[$i][0] = $lh_expense;               // ��˥�ɸ��
                    $input_data[$i][1] = $b_expense;                // �Х����
                break;
                case  5:                                            // ���������ų���ê����
                    $input_data[$i][0] = $lh_endinv;                // ��˥�ɸ��
                    $input_data[$i][1] = $b_endinv;                 // �Х����
                break;
                case  6:                                            // ��帶��
                    $input_data[$i][0] = $lh_urigen;                // ��˥�ɸ��
                    $input_data[$i][1] = $b_urigen;                 // �Х����
                break;
                case  7:                                            // ���������
                    $input_data[$i][0] = $lh_gross_profit;          // ��˥�ɸ��
                    $input_data[$i][1] = $b_gross_profit;           // �Х����
                break;
                case  8:                                            // �ͷ���
                    $input_data[$i][0] = $lh_han_jin;               // ��˥�ɸ��
                    $input_data[$i][1] = $b_han_jin;                // �Х����
                break;
                case  9:                                            // ����
                    $input_data[$i][0] = $lh_han_kei;               // ��˥�ɸ��
                    $input_data[$i][1] = $b_han_kei;                // �Х����
                break;
                case 10:                                            // �δ���ڤӰ��̴������
                    $input_data[$i][0] = $lh_han_all;               // ��˥�ɸ��
                    $input_data[$i][1] = $b_han_all;                // �Х����
                break;
                case 11:                                            // �Ķ�����
                    $input_data[$i][0] = $lh_ope_profit;            // ��˥�ɸ��
                    $input_data[$i][1] = $b_ope_profit;             // �Х����
                break;
                case 12:                                            // ��̳��������
                    $input_data[$i][0] = $lh_gyoumu;                // ��˥�ɸ��
                    $input_data[$i][1] = $b_gyoumu;                 // �Х����
                break;
                case 13:                                            // �������
                    $input_data[$i][0] = $lh_swari;                 // ��˥�ɸ��
                    $input_data[$i][1] = $b_swari;                  // �Х����
                break;
                case 14:                                            // �Ķȳ����פ���¾
                    $input_data[$i][0] = $lh_pother;                // ��˥�ɸ��
                    $input_data[$i][1] = $b_pother;                 // �Х����
                break;
                case 15:                                            // �Ķȳ����׷�
                    $input_data[$i][0] = $lh_nonope_profit_sum;     // ��˥�ɸ��
                    $input_data[$i][1] = $b_nonope_profit_sum;      // �Х����
                break;
                case 16:                                            // ��ʧ��©
                    $input_data[$i][0] = $lh_srisoku;               // ��˥�ɸ��
                    $input_data[$i][1] = $b_srisoku;                // �Х����
                break;
                case 17:                                            // �Ķȳ����Ѥ���¾
                    $input_data[$i][0] = $lh_lother;                // ��˥�ɸ��
                    $input_data[$i][1] = $b_lother;                 // �Х����
                break;
                case 18:                                            // �Ķȳ����ѷ�
                    $input_data[$i][0] = $lh_nonope_loss_sum;       // ��˥�ɸ��
                    $input_data[$i][1] = $b_nonope_loss_sum;        // �Х����
                break;
                case 19:                                            // �о�����
                    $input_data[$i][0] = $lh_current_profit;        // ��˥�ɸ��
                    $input_data[$i][1] = $b_current_profit;         // �Х����
                break;
                default:
                break;
            }
    }
    // ��˥�ɸ����Ͽ
    $head  = "��˥�ɸ��";
    $sec   = 0;
    insert_date($head,$item,$yyyymm,$input_data,$sec);
    // �Х������Ͽ
    $head  = "�Х����";
    $sec   = 1;
    insert_date($head,$item,$yyyymm,$input_data,$sec);
}
function insert_date($head,$item,$yyyymm,$input_data,$sec) 
{
    for ($i = 0; $i < 20; $i++) {
        $item_in     = array();
        $item_in[$i] = $head . $item[$i];
        $input_data[$i][$sec] = str_replace(',','',$input_data[$i][$sec]);
        $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='%s'", $yyyymm, $item_in[$i]);
        $res_in = array();
        if (getResult2($query,$res_in) <= 0) {
            /////////// begin �ȥ�󥶥�����󳫻�
            if ($con = db_connect()) {
                query_affected_trans($con, "begin");
            } else {
                $_SESSION["s_sysmsg"] .= "�ǡ����١�������³�Ǥ��ޤ���";
                exit();
            }
            ////////// Insert Start
            $query = sprintf("insert into profit_loss_pl_history (pl_bs_ym, kin, note, last_date, last_user) values (%d, %d, '%s', CURRENT_TIMESTAMP, '%s')", $yyyymm, $input_data[$i][$sec], $item_in[$i], $_SESSION['User_ID']);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION["s_sysmsg"] .= sprintf("%s�ο�����Ͽ�˼���<br> %d", $item_in[$i], $yyyymm);
                query_affected_trans($con, "rollback");     // transaction rollback
                exit();
            }
            /////////// commit �ȥ�󥶥������λ
            query_affected_trans($con, "commit");
            $_SESSION["s_sysmsg"] = sprintf("<font color='yellow'>%d »�ץǡ��� ���� ��Ͽ��λ</font>",$yyyymm);
        } else {
            /////////// begin �ȥ�󥶥�����󳫻�
            if ($con = db_connect()) {
                query_affected_trans($con, "begin");
            } else {
                $_SESSION["s_sysmsg"] .= "�ǡ����١�������³�Ǥ��ޤ���";
                exit();
            }
            ////////// UPDATE Start
            $query = sprintf("update profit_loss_pl_history set kin=%d, last_date=CURRENT_TIMESTAMP, last_user='%s' where pl_bs_ym=%d and note='%s'", $input_data[$i][$sec], $_SESSION['User_ID'], $yyyymm, $item_in[$i]);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION["s_sysmsg"] .= sprintf("%s��UPDATE�˼���<br> %d", $item_in[$i], $yyyymm);
                query_affected_trans($con, "rollback");     // transaction rollback
                exit();
            }
            /////////// commit �ȥ�󥶥������λ
            query_affected_trans($con, "commit");
            $_SESSION["s_sysmsg"] = sprintf("<font color='yellow'>%d »�ץǡ��� �ѹ� ��λ</font>",$yyyymm);
        }
    }
    $_SESSION["s_sysmsg"] .= "����Υǡ�������Ͽ���ޤ�����";
}
/////////// HTML Header ����Ϥ��ƥ���å��������
$menu->out_html_header();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<title><?= $menu->out_title() ?></title>
<?= $menu->out_site_java() ?>
<?= $menu->out_css() ?>
<?= $menu->out_jsBaseClass() ?>
<script type=text/javascript language='JavaScript'>
<!--
/* ����ʸ�����������ɤ��������å� */
function isDigit(str) {
    var len=str.length;
    var c;
    for (i=0; i<len; i++) {
        c = str.charAt(i);
        if (("0" > c) || (c > "9")) {
            alert("���Ͱʳ������Ͻ���ޤ���");
            return false;
        }
    }
    return true;
}
function isDigitcho(str) {
    var len=str.length;
    var c;
    for (i=0; i<len; i++) {
        c = str.charAt(i);
        if ((i == 0) && (c == "-")) {
            return true;
        }
        if (("0" > c) || (c > "9")) {
            alert("���Ͱʳ������Ͻ���ޤ���");
            return false;
        }
    }
    return true;
}
/* ������ϥ�����Ȥإե������������� */
function set_focus(){
    document.jin.jin_1.focus();
    document.jin.jin_1.select();
}
function data_input_click(obj) {
    return confirm("����Υǡ�������Ͽ���ޤ���\n���˥ǡ�����������Ͼ�񤭤���ޤ���");
}
// -->
</script>
<style type='text/css'>
<!--
select {
    background-color:teal;
    color:white;
}
textarea {
    background-color:black;
    color:white;
}
input.sousin {
    background-color:red;
}
input.text {
    background-color:black;
    color:white;
}
.pt8 {
    font:normal 8pt;
    font-family: monospace;
}
.pt10 {
    font: normal 10pt;
    font-family: monospace;
}
.pt10b {
    font:bold 10pt;
    font-family: monospace;
}
.pt12b {
    font:bold 12pt;
    font-family: monospace;
}
.title_font {
    font:bold 13.5pt;
    font-family: monospace;
}
.today_font {
    font-size: 10.5pt;
    font-family: monospace;
}
.corporate_name {
    font:bold 10pt;
    font-family: monospace;
}
.margin0 {
    margin:0%;
}
ol {
    line-height: normal;
}
pre {
    font-size: 10.0pt;
    font-family: monospace;
}
.OnOff_font {
    font-size:     8.5pt;
    font-family:   monospace;
}
-->
</style>
</head>
<!--  style='overflow-y:hidden;' -->
<body>
    <center>
<?= $menu->out_title_border() ?>
        <table width='100%' border='1' cellspacing='1' cellpadding='1'>
            <tr>
                <td colspan='2' bgcolor='#d6d3ce' align='center' class='corporate_name'>
                    <?=$menu->out_caption(), "\n"?>
                </td>
                <form method='post' action='<?php echo $menu->out_self() ?>'>
                    <td colspan='14' bgcolor='#d6d3ce' align='right' class='pt10'>
                        ñ��
                        <select name='keihi_tani' class='pt10'>
                        <?php
                            if ($tani == 1000)
                                echo "<option value='1000' selected>�����</option>\n";
                            else
                                echo "<option value='1000'>�����</option>\n";
                            if ($tani == 1)
                                echo "<option value='1' selected>������</option>\n";
                            else
                                echo "<option value='1'>������</option>\n";
                            if ($tani == 1000000)
                                echo "<option value='1000000' selected>ɴ����</option>\n";
                            else
                                echo "<option value='1000000'>ɴ����</option>\n";
                            if ($tani == 10000)
                                echo "<option value='10000' selected>������</option>\n";
                            else
                                echo "<option value='10000'>������</option>\n";
                            if($tani == 100000)
                                echo "<option value='100000' selected>������</option>\n";
                            else
                                echo "<option value='100000'>������</option>\n";
                        ?>
                        </select>
                        ������
                        <select name='keihi_keta' class='pt10'>
                        <?php
                            if ($keta == 0)
                                echo "<option value='0' selected>����</option>\n";
                            else
                                echo "<option value='0'>����</option>\n";
                            if ($keta == 3)
                                echo "<option value='3' selected>����</option>\n";
                            else
                                echo "<option value='3'>����</option>\n";
                            if ($keta == 6)
                                echo "<option value='6' selected>����</option>\n";
                            else
                                echo "<option value='6'>����</option>\n";
                            if ($keta == 1)
                                echo "<option value='1' selected>����</option>\n";
                            else
                                echo "<option value='1'>����</option>\n";
                            if ($keta == 2)
                                echo "<option value='2' selected>����</option>\n";
                            else
                                echo "<option value='2'>����</option>\n";
                            if ($keta == 4)
                                echo "<option value='4' selected>����</option>\n";
                            else
                                echo "<option value='4'>����</option>\n";
                            if ($keta == 5)
                                echo "<option value='5' selected>����</option>\n";
                            else
                                echo "<option value='5'>����</option>\n";
                        ?>
                        </select>
                        <input class='pt10b' type='submit' name='return' value='ñ���ѹ�'>
                        <?php
                        if ($_SESSION['User_ID'] == '300144') {
                            if ($keta == 0 && $tani == 1) {
                        ?>
                            &nbsp;
                            <input class='pt10b' type='submit' name='input_data' value='����ǡ�����Ͽ' onClick='return data_input_click(this)'>
                        <?php
                            } else {
                        ?>
                            <input class='pt10b' type='submit' name='input_data' value='����ǡ�����Ͽ' onClick='return data_input_click(this)' disabled>
                        <?php
                            }
                        }
                        ?>
                    </td>
                </form>
            </tr>
        </table>
    <table width='100%' bgcolor='#d6d3ce' align='center' cellspacing="0" cellpadding="3" border='1'>
        <tr>
        <td>
        <table width='100%' bgcolor='#d6d3ce' align='center' cellspacing="0" cellpadding="3" border='1'>
            <TBODY>
                <tr>
                    <td rowspan='2' colspan='3' width='200' align='center' class='pt10b' bgcolor='#ffffc6'>�ࡡ������</td>
                    <td colspan='4' align='center' class='pt10b' bgcolor='#ffffc6'>�ꡡ�ˡ���</td>
                    <td colspan='4' align='center' class='pt10b' bgcolor='#ffffc6'>���Υݥ��</td>
                    <td colspan='4' align='center' class='pt10b' bgcolor='#ffffc6'>�硡������</td>
                    <td rowspan='2' width='400' align='left' class='pt10b' bgcolor='#ffffc6'>��¤���ܷ����δ����������</td>
                </tr>
                <tr>
                    <td nowrap align='center' class='pt10b' bgcolor='#d6d3ce'><?php echo $p2_ym ?> </td>
                    <td nowrap align='center' class='pt10b' bgcolor='#d6d3ce'><?php echo $p1_ym ?> </td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'><?php echo $yyyymm ?></td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>�ߡ���</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#d6d3ce'><?php echo $p2_ym ?> </td>
                    <td nowrap align='center' class='pt10b' bgcolor='#d6d3ce'><?php echo $p1_ym ?> </td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'><?php echo $yyyymm ?></td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>�ߡ���</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#d6d3ce'><?php echo $p2_ym ?> </td>
                    <td nowrap align='center' class='pt10b' bgcolor='#d6d3ce'><?php echo $p1_ym ?> </td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'><?php echo $yyyymm ?></td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>�ߡ���</td>
                </tr>
                <tr>
                    <td rowspan='11' width='10' align='center' valign='middle' class='pt10b' bgcolor='#ceffce'>�ġ��ȡ�»����</td>
                    <td colspan='2' nowrap align='center' class='pt10b' bgcolor='#ceffce'>�䡡�塡��</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p2_lh_uri ?>   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_lh_uri ?>   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $lh_uri ?>      </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $rui_lh_uri ?>  </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p2_b_uri ?>   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_b_uri ?>   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $b_uri ?>      </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $rui_b_uri ?>  </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p2_l_uri ?>   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_l_uri ?>   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $l_uri ?>      </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $rui_l_uri ?>  </td>
                    <td nowrap align='left'  class='pt10' bgcolor='#d6d3ce'>�º�����</td>
                </tr>
                <tr>
                    <td rowspan='6' width='10' align='center' valign='middle' class='pt10b' bgcolor='#ffffc6'>��帶��</td> <!-- ��帶�� -->
                    <td nowrap align='left' class='pt10b' bgcolor='white'>����������ų���ê����</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_lh_invent ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_lh_invent ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $lh_invent ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_lh_invent ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_b_invent ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_b_invent ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $b_invent ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_b_invent ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_l_invent ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_l_invent ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $l_invent ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_l_invent ?></td>
                    <td nowrap align='left'  class='pt10'>ɸ�ึ���ˤ��ê����</td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='#e6e6e6'>��������(������)</td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_lh_metarial ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_lh_metarial ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $lh_metarial ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_lh_metarial ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_b_metarial ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_b_metarial ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $b_metarial ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_b_metarial ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_l_metarial ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_l_metarial ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $l_metarial ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_l_metarial ?></td>
                    <td nowrap align='left'  class='pt10'>��ݹ�������</td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='white'>��ϫ����̳������</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_lh_roumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_lh_roumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $lh_roumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_lh_roumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_b_roumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_b_roumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $b_roumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_b_roumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_l_roumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_l_roumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $l_roumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_l_roumu ?></td>
                    <td nowrap align='left'  class='pt10'>�����ӥ������ڤ���Ⱦ��������</td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='#e6e6e6'>���С�����������</td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_lh_expense ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_lh_expense ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $lh_expense ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_lh_expense ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_b_expense ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_b_expense ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $b_expense ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_b_expense ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_l_expense ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_l_expense ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $l_expense ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_l_expense ?></td>
                    <td nowrap align='left'  class='pt10'>�����ӥ������ڤ���Ⱦ��������</td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='white'>�����������ų���ê����</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_lh_endinv ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_lh_endinv ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $lh_endinv ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $lh_endinv ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_b_endinv ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_b_endinv ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $b_endinv ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $b_endinv ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_l_endinv ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_l_endinv ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $l_endinv ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $l_endinv ?></td>
                    <td nowrap align='left'  class='pt10'>ɸ�ึ���ˤ��ê����</td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='#ffffc6'>���䡡�塡������</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p2_lh_urigen ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p1_lh_urigen ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $lh_urigen ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $rui_lh_urigen ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p2_b_urigen ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p1_b_urigen ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $b_urigen ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $rui_b_urigen ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p2_l_urigen ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p1_l_urigen ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $l_urigen ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $rui_l_urigen ?></td>
                    <td nowrap align='left'  class='pt10'>��</td>
                </tr>
                <tr>
                    <td colspan='2' nowrap align='center' class='pt10b' bgcolor='#ceffce'>�䡡�塡��������</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p2_lh_gross_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_lh_gross_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $lh_gross_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $rui_lh_gross_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p2_b_gross_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_b_gross_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $b_gross_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $rui_b_gross_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p2_l_gross_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_l_gross_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $l_gross_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $rui_l_gross_profit ?></td>
                    <td nowrap align='left'  class='pt10' bgcolor='#d6d3ce'>��</td>  <!-- ;�� -->
                </tr>
                <tr>
                    <td rowspan='3' width='10' align='center' valign='middle' class='pt10b' bgcolor='#ffffc6'></td> <!-- �δ��� -->
                    <td nowrap align='left' class='pt10b' bgcolor='#e6e6e6'>���͡��������</td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_lh_han_jin ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_lh_han_jin ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $lh_han_jin ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_lh_han_jin ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_b_han_jin ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_b_han_jin ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $b_han_jin ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_b_han_jin ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_l_han_jin ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_l_han_jin ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $l_han_jin ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_l_han_jin ?></td>
                    <td nowrap align='left'  class='pt10'>����Ͱ���Ψ</td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='white'>���С�����������</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_lh_han_kei ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_lh_han_kei ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $lh_han_kei ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_lh_han_kei ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_b_han_kei ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_b_han_kei ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $b_han_kei ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_b_han_kei ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_l_han_kei ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_l_han_kei ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $l_han_kei ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_l_han_kei ?></td>
                    <td nowrap align='left'  class='pt10'>����Ͱ���Ψ</td>
                </tr>
                <tr>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>�δ���ڤӰ��̴������</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p2_lh_han_all ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p1_lh_han_all ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $lh_han_all ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $rui_lh_han_all ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p2_b_han_all ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p1_b_han_all ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $b_han_all ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $rui_b_han_all ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p2_l_han_all ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p1_l_han_all ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $l_han_all ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $rui_l_han_all ?></td>
                    <td nowrap align='left'  class='pt10'>��</td>  <!-- ;�� -->
                </tr>
                <tr>
                    <td colspan='3' nowrap align='center' class='pt10b' bgcolor='#ceffce'>�ġ����ȡ�����������</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p2_lh_ope_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_lh_ope_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $lh_ope_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $rui_lh_ope_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p2_b_ope_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_b_ope_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $b_ope_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $rui_b_ope_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p2_l_ope_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_l_ope_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $l_ope_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $rui_l_ope_profit ?></td>
                    <td nowrap align='left'  class='pt10'>��</td>  <!-- ;�� -->
                </tr>
                <tr>
                    <td rowspan='7' align='center' valign='middle' class='pt10b' bgcolor='#ceffce'>�Ķȳ�»��</td>
                    <td rowspan='4' align='center' class='pt10' bgcolor='#ffffc6'></td> <!-- ;�� -->
                    <td nowrap align='left' class='pt10b' bgcolor='white'>����̳��������</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_lh_gyoumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_lh_gyoumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $lh_gyoumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_lh_gyoumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_b_gyoumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_b_gyoumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $b_gyoumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_b_gyoumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_l_gyoumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_l_gyoumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $l_gyoumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_l_gyoumu ?></td>
                    <td nowrap align='left'  class='pt10'>��Ⱦ�����Ӥ�������</td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='#e6e6e6'>���š������䡡��</td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_lh_swari ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_lh_swari ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $lh_swari ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_lh_swari ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_b_swari ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_b_swari ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $b_swari ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_b_swari ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_l_swari ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_l_swari ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $l_swari ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_l_swari ?></td>
                    <td nowrap align='left'  class='pt10'>��Ⱦ�����Ӥ�������</td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='white'>���������Ρ���¾</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_lh_pother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_lh_pother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $lh_pother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_lh_pother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_b_pother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_b_pother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $b_pother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_b_pother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_l_pother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_l_pother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $l_pother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_l_pother ?></td>
                    <td nowrap align='left'  class='pt10'>��Ⱦ�����Ӥ�������</td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='#ffffc6'>���Ķȳ����� ��</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p2_lh_nonope_profit_sum ?>   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p1_lh_nonope_profit_sum ?>   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $lh_nonope_profit_sum ?>      </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $rui_lh_nonope_profit_sum ?>  </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p2_b_nonope_profit_sum ?>   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p1_b_nonope_profit_sum ?>   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $b_nonope_profit_sum ?>      </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $rui_b_nonope_profit_sum ?>  </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p2_l_nonope_profit_sum ?>   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p1_l_nonope_profit_sum ?>   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $l_nonope_profit_sum ?>      </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $rui_l_nonope_profit_sum ?>  </td>
                    <td nowrap align='left'  class='pt10'>��</td> <!-- ;�� -->
                </tr>
                <tr>
                    <td rowspan='3' align='center' class='pt10' bgcolor='#ffffc6'></td> <!-- ;�� -->
                    <td nowrap align='left' class='pt10b' bgcolor='#e6e6e6'>���١�ʧ������©</td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_lh_srisoku ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_lh_srisoku ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $lh_srisoku ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_lh_srisoku ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_b_srisoku ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_b_srisoku ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $b_srisoku ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_b_srisoku ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_l_srisoku ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_l_srisoku ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $l_srisoku ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_l_srisoku ?></td>
                    <td nowrap align='left'  class='pt10'>��Ⱦ�����Ӥ�������</td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='white'>���������Ρ���¾</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_lh_lother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_lh_lother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $lh_lother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_lh_lother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_b_lother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_b_lother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $b_lother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_b_lother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_l_lother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_l_lother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $l_lother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_l_lother ?></td>
                    <td nowrap align='left'  class='pt10'>��Ⱦ�����Ӥ�������</td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='#ffffc6'>���Ķȳ����� ��</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p2_lh_nonope_loss_sum ?>   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p1_lh_nonope_loss_sum ?>   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $lh_nonope_loss_sum ?>      </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $rui_lh_nonope_loss_sum ?>  </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p2_b_nonope_loss_sum ?>   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p1_b_nonope_loss_sum ?>   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $b_nonope_loss_sum ?>      </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $rui_b_nonope_loss_sum ?>  </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p2_l_nonope_loss_sum ?>   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p1_l_nonope_loss_sum ?>   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $l_nonope_loss_sum ?>      </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $rui_l_nonope_loss_sum ?>  </td>
                    <td nowrap align='left'  class='pt10'>��</td> <!-- ;�� -->
                </tr>
                <tr>
                    <td colspan='3' nowrap align='center' class='pt10b' bgcolor='#ceffce'>�С��������������</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p2_lh_current_profit ?>   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_lh_current_profit ?>   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $lh_current_profit ?>      </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $rui_lh_current_profit ?>  </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p2_b_current_profit ?>   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_b_current_profit ?>   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $b_current_profit ?>      </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $rui_b_current_profit ?>  </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p2_l_current_profit ?>   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_l_current_profit ?>   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $l_current_profit ?>      </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $rui_l_current_profit ?>  </td>
                    <td nowrap align='left'  class='pt10'>��</td>  <!-- ;�� -->
                </tr>
            </TBODY>
        </table>
        </td>
        </tr>
        <tr>
        <td>
        <table width='100%' bgcolor='#d6d3ce' align='center' cellspacing="0" cellpadding="3" border='1'>
            <tbody>
                <tr>
                    <td colspan='20' bgcolor='white' align='left' class='pt10b'><a href='<%=$menu->out_action('�õ���������')%>?<?php echo uniqid('menu') ?>' style='text-decoration:none; color:black;'>�������»���õ�����</a></td>
                </tr>
                <tr>
                    <td colspan='20' bgcolor='white' class='pt10'>
                        <ol>
                        <?php
                            if ($comment_l != "") {
                                echo "<li><pre>$comment_l</pre></li>\n";
                            }
                            if ($comment_b != "") {
                                echo "<li><pre>$comment_b</pre></li>\n";
                            }
                            if ($comment_all != "") {
                                echo "<li><pre>$comment_all</pre></li>\n";
                            }
                            if ($comment_other != "") {
                                echo "<li><pre>$comment_other</pre></li>\n";
                            }
                        ?>
                        </ol>
                    </td>
                </tr>
            </tbody>
        </table>
        </td>
        </tr>
    </table>
    </center>
</body>
</html>
