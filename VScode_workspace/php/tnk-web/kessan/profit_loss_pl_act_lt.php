<?php
//////////////////////////////////////////////////////////////////////////////
// 月次損益関係 月次 リニア・機工 損益計算書                                //
// Copyright (C) 2015 - 2015 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp  //
// Changed history                                                          //
// 2015/06/04 Created   profit_loss_pl_act_lt.php                           //
// 2015/06/15 機工のデータを表示するように変更(バイモルの入替なので$b_～)   //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug 用
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
session_start();                            // ini_set()の次に指定すること Script 最上行

require_once ('../function.php');           // define.php と pgsql.php を require_once している
require_once ('../tnk_func.php');           // TNK に依存する部分の関数を require_once している
require_once ('../MenuHeader.php');         // TNK 全共通 menu class
access_log();                               // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader(0);                  // 認証チェック0=一般以上 戻り先=TOP_MENU タイトル未設定
    // 実際の認証はprofit_loss_submit.phpで行っているaccount_group_check()を使用

////////////// サイト設定
// $menu->set_site(10, 7);                  // site_index=10(損益メニュー) site_id=7(月次損益)
//////////// 表題の設定
$menu->set_caption('栃木日東工器(株)');
//////////// 呼出先のaction名とアドレス設定
$menu->set_action('特記事項入力',   PL . 'profit_loss_comment_put_lt.php');

///// 期・月の取得
$ki = Ym_to_tnk($_SESSION['pl_ym']);
$tuki = substr($_SESSION['pl_ym'],4,2);

//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title("第 {$ki} 期　{$tuki} 月度　Ｌ Ｔ 商 品 別 損 益 計 算 書");

///// 対象当月
$yyyymm = $_SESSION['pl_ym'];
///// 対象前月
if (substr($yyyymm,4,2)!=01) {
    $p1_ym = $yyyymm - 1;
} else {
    $p1_ym = $yyyymm - 100;
    $p1_ym = $p1_ym + 11;
}
///// 対象前々月
if (substr($p1_ym,4,2)!=01) {
    $p2_ym = $p1_ym - 1;
} else {
    $p2_ym = $p1_ym - 100;
    $p2_ym = $p2_ym + 11;
}
///// 期初年月の算出
$yyyy = substr($yyyymm, 0,4);
$mm   = substr($yyyymm, 4,2);
if (($mm >= 1) && ($mm <= 3)) {
    $yyyy = ($yyyy - 1);
}
$str_ym = $yyyy . "04";     // 期初年月

///// 表示単位を設定取得
if (isset($_POST['keihi_tani'])) {
    $_SESSION['keihi_tani'] = $_POST['keihi_tani'];
    $tani = $_SESSION['keihi_tani'];
} elseif (isset($_SESSION['keihi_tani'])) {
    $tani = $_SESSION['keihi_tani'];
} else {
    $tani = 1000;           // 初期値 表示単位 千円
    $_SESSION['keihi_tani'] = $tani;
}
///// 表示 小数部桁数 設定取得
if (isset($_POST['keihi_keta'])) {
    $_SESSION['keihi_keta'] = $_POST['keihi_keta'];
    $keta = $_SESSION['keihi_keta'];
} elseif (isset($_SESSION['keihi_keta'])) {
    $keta = $_SESSION['keihi_keta'];
} else {
    $keta = 0;              // 初期値 小数点以下桁数
    $_SESSION['keihi_keta'] = $keta;
}

/********** 売上高 **********/
    ///// 当月
if ($yyyymm >= 200909) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修売上高'", $yyyymm);
    if (getUniResult($query, $s_uri) < 1) {
        $s_uri        = 0;      // 検索失敗
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
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修売上調整額'", $yyyymm);
    if (getUniResult($query, $s_uri_cho) < 1) {
        // 検索失敗
        $s_uri = number_format(($s_uri / $tani), $keta);
    } else {
        $s_uri_sagaku = $s_uri_sagaku + $s_uri_cho;
        $s_uri        = number_format(($s_uri / $tani), $keta);
    }
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修売上高'", $yyyymm);
    if (getUniResult($query, $s_uri) < 1) {
        $s_uri        = 0;      // 検索失敗
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
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='機工売上高'", $yyyymm);
if (getUniResult($query, $b_uri) < 1) {
    $b_uri        = 0;          // 検索失敗
    $b_uri_sagaku = 0;
} else {
    $b_uri_sagaku = $b_uri;
    $b_uri        = number_format(($b_uri / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア売上高'", $yyyymm);
if (getUniResult($query, $l_uri) < 1) {
    $l_uri         = 0;         // 検索失敗
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
    $l_uri         = $l_uri - $s_uri_sagaku;                   // 試験修理売上高をリニアの売上よりマイナス
    $lh_uri        = number_format(($lh_uri / $tani), $keta);
    $l_uri         = number_format(($l_uri / $tani), $keta);
}
    ///// 前月
if ($yyyymm >= 200910) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修売上高'", $p1_ym);
    if (getUniResult($query, $p1_s_uri) < 1) {
        $p1_s_uri        = 0;     // 検索失敗
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
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修売上調整額'", $p1_ym);
    if (getUniResult($query, $p1_s_uri_cho) < 1) {
        // 検索失敗
        $p1_s_uri = number_format(($p1_s_uri / $tani), $keta);
    } else {
        $p1_s_uri_sagaku = $p1_s_uri_sagaku + $p1_s_uri_cho;
        $p1_s_uri        = number_format(($p1_s_uri / $tani), $keta);
    }
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修売上高'", $p1_ym);
    if (getUniResult($query, $p1_s_uri) < 1) {
        $p1_s_uri        = 0;     // 検索失敗
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
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='機工売上高'", $p1_ym);
if (getUniResult($query, $p1_b_uri) < 1) {
    $p1_b_uri        = 0;     // 検索失敗
    $p1_b_uri_sagaku = 0;
} else {
    $p1_b_uri_sagaku = $p1_b_uri;
    $p1_b_uri        = number_format(($p1_b_uri / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア売上高'", $p1_ym);
if (getUniResult($query, $p1_l_uri) < 1) {
    $p1_l_uri         = 0;    // 検索失敗
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
    $p1_l_uri         = $p1_l_uri - $p1_s_uri_sagaku;                   // 試験修理売上高をリニアの売上よりマイナス
    $p1_lh_uri        = number_format(($p1_lh_uri / $tani), $keta);
    $p1_l_uri         = number_format(($p1_l_uri / $tani), $keta);
}
    ///// 前前月
if ($yyyymm >= 200911) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修売上高'", $p2_ym);
    if (getUniResult($query, $p2_s_uri) < 1) {
        $p2_s_uri        = 0;     // 検索失敗
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
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修売上調整額'", $p2_ym);
    if (getUniResult($query, $p2_s_uri_cho) < 1) {
        // 検索失敗
        $p2_s_uri = number_format(($p2_s_uri / $tani), $keta);
    } else {
        $p2_s_uri_sagaku = $p2_s_uri_sagaku + $p2_s_uri_cho;
        $p2_s_uri        = number_format(($p2_s_uri / $tani), $keta);
    }
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修売上高'", $p2_ym);
    if (getUniResult($query, $p2_s_uri) < 1) {
        $p2_s_uri        = 0;     // 検索失敗
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
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='機工売上高'", $p2_ym);
if (getUniResult($query, $p2_b_uri) < 1) {
    $p2_b_uri        = 0;     // 検索失敗
    $p2_b_uri_sagaku = 0;
} else {
    $p2_b_uri_sagaku = $p2_b_uri;
    $p2_b_uri        = number_format(($p2_b_uri / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア売上高'", $p2_ym);
if (getUniResult($query, $p2_l_uri) < 1) {
    $p2_l_uri         = 0;    // 検索失敗
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
    $p2_l_uri         = $p2_l_uri - $p2_s_uri_sagaku;                   // 試験修理売上高をリニアの売上よりマイナス
    $p2_lh_uri        = number_format(($p2_lh_uri / $tani), $keta);
    $p2_l_uri         = number_format(($p2_l_uri / $tani), $keta);
}
    ///// 今期累計
if ($yyyymm >= 200909) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='試修売上高'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_s_uri) < 1) {
        $rui_s_uri        = 0;      // 検索失敗
        $rui_s_uri_sagaku = 0;
    } else {
        $rui_s_uri_sagaku = $rui_s_uri;
    }
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='試修売上調整額'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_s_uri_cho) < 1) {
        // 検索失敗
        $rui_s_uri        = number_format(($rui_s_uri / $tani), $keta);
    } else {
        $rui_s_uri_sagaku = $rui_s_uri_sagaku + $rui_s_uri_cho;
        $rui_s_uri        = number_format(($rui_s_uri / $tani), $keta);
    }
} else {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='試修売上高'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_s_uri) < 1) {
        $rui_s_uri        = 0;     // 検索失敗
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
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='機工売上高'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_b_uri) < 1) {
    $rui_b_uri        = 0;         // 検索失敗
    $rui_b_uri_sagaku = 0;
} else {
    $rui_b_uri_sagaku = $rui_b_uri;
    $rui_b_uri        = number_format(($rui_b_uri / $tani), $keta);
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='リニア売上高'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_l_uri) < 1) {
    $rui_l_uri         = 0;        // 検索失敗
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
    $rui_l_uri         = $rui_l_uri - $rui_s_uri_sagaku;                   // 試験修理売上高をリニアの売上よりマイナス
    $rui_lh_uri        = number_format(($rui_lh_uri / $tani), $keta);
    $rui_l_uri         = number_format(($rui_l_uri / $tani), $keta);
}

/********** 期首材料仕掛品棚卸高 **********/
    ///// 試験・修理
$p2_s_invent  = 0;
$p1_s_invent  = 0;
$s_invent     = 0;
$rui_s_invent = 0;
    ///// 当月
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='機工期首棚卸高'", $yyyymm);
if (getUniResult($query, $b_invent) < 1) {
    $b_invent = 0;              // 検索失敗
    $b_invent_sagaku = 0;
} else {
    $b_invent_sagaku = $b_invent;
    $b_invent        = number_format(($b_invent / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア期首棚卸高'", $yyyymm);
if (getUniResult($query, $l_invent) < 1) {
    $l_invent         = 0;      // 検索失敗
    $lh_invent        = 0;
    $lh_invent_sagaku = 0;
} else {
    $lh_invent        = $l_invent - $s_invent - $b_invent_sagaku;
    $lh_invent_sagaku = $lh_invent;
    $lh_invent        = number_format(($lh_invent / $tani), $keta);
    $l_invent         = number_format(($l_invent / $tani), $keta);
}
    ///// 前月
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='機工期首棚卸高'", $p1_ym);
if (getUniResult($query, $p1_b_invent) < 1) {
    $p1_b_invent        = 0;    // 検索失敗
    $p1_b_invent_sagaku = 0;
} else {
    $p1_b_invent_sagaku = $p1_b_invent;
    $p1_b_invent        = number_format(($p1_b_invent / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア期首棚卸高'", $p1_ym);
if (getUniResult($query, $p1_l_invent) < 1) {
    $p1_l_invent         = 0;   // 検索失敗
    $p1_lh_invent        = 0;
    $p1_lh_invent_sagaku = 0;
} else {
    $p1_lh_invent        = $p1_l_invent - $p1_s_invent - $p1_b_invent_sagaku;
    $p1_lh_invent_sagaku = $p1_lh_invent;
    $p1_lh_invent        = number_format(($p1_lh_invent / $tani), $keta);
    $p1_l_invent         = number_format(($p1_l_invent / $tani), $keta);
}
    ///// 前前月
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='機工期首棚卸高'", $p2_ym);
if (getUniResult($query, $p2_b_invent) < 1) {
    $p2_b_invent        = 0;    // 検索失敗
    $p2_b_invent_sagaku = 0;
} else {
    $p2_b_invent_sagaku = $p2_b_invent;
    $p2_b_invent        = number_format(($p2_b_invent / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア期首棚卸高'", $p2_ym);
if (getUniResult($query, $p2_l_invent) < 1) {
    $p2_l_invent         = 0;   // 検索失敗
    $p2_lh_invent        = 0;
    $p2_lh_invent_sagaku = 0;
} else {
    $p2_lh_invent        = $p2_l_invent - $p2_s_invent - $p2_b_invent_sagaku;
    $p2_lh_invent_sagaku = $p2_lh_invent;
    $p2_lh_invent        = number_format(($p2_lh_invent / $tani), $keta);
    $p2_l_invent         = number_format(($p2_l_invent / $tani), $keta);
}
    ///// 今期累計
    /////   期首棚卸高の累計は 期初年月の期首棚卸高になる
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym=%d and note='機工期首棚卸高'", $str_ym);
if (getUniResult($query, $rui_b_invent) < 1) {
    $rui_b_invent        = 0;   // 検索失敗
    $rui_b_invent_sagaku = 0;
} else {
    $rui_b_invent_sagaku = $rui_b_invent;
    $rui_b_invent        = number_format(($rui_b_invent / $tani), $keta);
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym=%d and note='リニア期首棚卸高'", $str_ym);
if (getUniResult($query, $rui_l_invent) < 1) {
    $rui_l_invent         = 0;  // 検索失敗
    $rui_lh_invent        = 0;
    $rui_lh_invent_sagaku = 0;
} else {
    $rui_lh_invent        = $rui_l_invent - $rui_s_invent - $rui_b_invent_sagaku;
    $rui_lh_invent_sagaku = $rui_lh_invent;
    $rui_lh_invent        = number_format(($rui_lh_invent / $tani), $keta);
    $rui_l_invent         = number_format(($rui_l_invent / $tani), $keta);
}

/********** 材料費(仕入高) **********/
    ///// 当月
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修材料費'", $yyyymm);
if (getUniResult($query, $s_metarial) < 1) {
    $s_metarial        = 0;          // 検索失敗
    $s_metarial_sagaku = 0;
} else {
    $s_metarial_sagaku = $s_metarial;
    $s_metarial        = number_format(($s_metarial / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='機工仕入高'", $yyyymm);
if (getUniResult($query, $b_metarial) < 1) {
    $b_metarial        = 0;          // 検索失敗
    $b_metarial_sagaku = 0;
} else {
    $b_metarial_sagaku = $b_metarial;
    $b_metarial        = number_format(($b_metarial / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア材料費'", $yyyymm);
if (getUniResult($query, $l_metarial) < 1) {
    $l_metarial         = 0 - $s_metarial_sagaku;     // 検索失敗
    $lh_metarial        = 0;
    $lh_metarial_sagaku = 0;
} else {
    $lh_metarial        = $l_metarial - $s_metarial_sagaku - $b_metarial_sagaku;
    $lh_metarial_sagaku = $lh_metarial;
    $l_metarial         = $l_metarial - $s_metarial_sagaku;        // 試験修理材料費をリニアの材料費よりマイナス
    $lh_metarial        = number_format(($lh_metarial / $tani), $keta);
    $l_metarial         = number_format(($l_metarial / $tani), $keta);
}
    ///// 前月
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修材料費'", $p1_ym);
if (getUniResult($query, $p1_s_metarial) < 1) {
    $p1_s_metarial        = 0;          // 検索失敗
    $p1_s_metarial_sagaku = 0;
} else {
    $p1_s_metarial_sagaku = $p1_s_metarial;
    $p1_s_metarial        = number_format(($p1_s_metarial / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='機工仕入高'", $p1_ym);
if (getUniResult($query, $p1_b_metarial) < 1) {
    $p1_b_metarial        = 0;          // 検索失敗
    $p1_b_metarial_sagaku = 0;
} else {
    $p1_b_metarial_sagaku = $p1_b_metarial;
    $p1_b_metarial        = number_format(($p1_b_metarial / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア材料費'", $p1_ym);
if (getUniResult($query, $p1_l_metarial) < 1) {
    $p1_l_metarial         = 0 - $p1_s_metarial_sagaku;     // 検索失敗
    $p1_lh_metarial        = 0;
    $p1_lh_metarial_sagaku = 0;
} else {
    $p1_lh_metarial        = $p1_l_metarial - $p1_s_metarial_sagaku - $p1_b_metarial_sagaku;
    $p1_lh_metarial_sagaku = $p1_lh_metarial;
    $p1_l_metarial         = $p1_l_metarial - $p1_s_metarial_sagaku;        // 試験修理材料費をリニアの材料費よりマイナス
    $p1_lh_metarial        = number_format(($p1_lh_metarial / $tani), $keta);
    $p1_l_metarial         = number_format(($p1_l_metarial / $tani), $keta);
}
    ///// 前前月
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修材料費'", $p2_ym);
if (getUniResult($query, $p2_s_metarial) < 1) {
    $p2_s_metarial        = 0;          // 検索失敗
    $p2_s_metarial_sagaku = 0;
} else {
    $p2_s_metarial_sagaku = $p2_s_metarial;
    $p2_s_metarial        = number_format(($p2_s_metarial / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='機工仕入高'", $p2_ym);
if (getUniResult($query, $p2_b_metarial) < 1) {
    $p2_b_metarial        = 0;          // 検索失敗
    $p2_b_metarial_sagaku = 0;
} else {
    $p2_b_metarial_sagaku = $p2_b_metarial;
    $p2_b_metarial        = number_format(($p2_b_metarial / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア材料費'", $p2_ym);
if (getUniResult($query, $p2_l_metarial) < 1) {
    $p2_l_metarial         = 0 - $p2_s_metarial_sagaku;     // 検索失敗
    $p2_lh_metarial        = 0;
    $p2_lh_metarial_sagaku = 0;
} else {
    $p2_lh_metarial        = $p2_l_metarial - $p2_s_metarial_sagaku - $p2_b_metarial_sagaku;
    $p2_lh_metarial_sagaku = $p2_lh_metarial;
    $p2_l_metarial         = $p2_l_metarial - $p2_s_metarial_sagaku;        // 試験修理材料費をリニアの材料費よりマイナス
    $p2_lh_metarial        = number_format(($p2_lh_metarial / $tani), $keta);
    $p2_l_metarial         = number_format(($p2_l_metarial / $tani), $keta);
}
    ///// 今期累計
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='試修材料費'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_s_metarial) < 1) {
    $rui_s_metarial        = 0;          // 検索失敗
    $rui_s_metarial_sagaku = 0;
} else {
    $rui_s_metarial_sagaku = $rui_s_metarial;
    $rui_s_metarial        = number_format(($rui_s_metarial / $tani), $keta);
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='機工仕入高'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_b_metarial) < 1) {
    $rui_b_metarial        = 0;          // 検索失敗
    $rui_b_metarial_sagaku = 0;
} else {
    $rui_b_metarial_sagaku = $rui_b_metarial;
    $rui_b_metarial        = number_format(($rui_b_metarial / $tani), $keta);
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='リニア材料費'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_l_metarial) < 1) {
    $rui_l_metarial         = 0 - $rui_s_metarial_sagaku;   // 検索失敗
    $rui_lh_metarial        = 0;
    $rui_lh_metarial_sagaku = 0;
} else {
    $rui_lh_metarial        = $rui_l_metarial - $rui_s_metarial_sagaku - $rui_b_metarial_sagaku;
    $rui_lh_metarial_sagaku = $rui_lh_metarial;
    $rui_l_metarial         = $rui_l_metarial - $rui_s_metarial_sagaku;        // 試験修理材料費をリニアの材料費よりマイナス
    $rui_lh_metarial        = number_format(($rui_lh_metarial / $tani), $keta);
    $rui_l_metarial         = number_format(($rui_l_metarial / $tani), $keta);
}

/********** 労務費 **********/
    ///// 当月
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修労務費'", $yyyymm);
if (getUniResult($query, $s_roumu) < 1) {
    $s_roumu        = 0;    // 検索失敗
    $s_roumu_sagaku = 0;
} else {
    $s_roumu_sagaku = $s_roumu;
    $s_roumu        = number_format(($s_roumu / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='機工労務費'", $yyyymm);
if (getUniResult($query, $b_roumu) < 1) {
    $b_roumu        = 0;    // 検索失敗
    $b_roumu_sagaku = 0;
} else {
    $b_roumu_sagaku = $b_roumu;
    $b_roumu        = number_format(($b_roumu / $tani), $keta);
}
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア給与配賦率'", $yyyymm);
    if (getUniResult($query, $l_kyu_kin) < 1) {
        $l_kyu_kin = 0;
    }
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア労務費'", $yyyymm);
if (getUniResult($query, $l_roumu) < 1) {
    $l_roumu         = 0 - $s_roumu_sagaku;     // 検索失敗]
    $lh_roumu        = 0;
    $lh_roumu_sagaku = 0;
} else {
    if ($yyyymm == 200912) {
        $l_roumu = $l_roumu + 182279;
    }
    if ($yyyymm >= 201001) {
        $l_roumu = $l_roumu + $l_kyu_kin;   // リニア配賦給与を加味(全て標準)
        //$l_roumu = $l_roumu + 151313;   // 添田さんの給与をC・Lは35%。試験修理に30%振分
    }
    if ($yyyymm == 201408) {
        $l_roumu = $l_roumu + 229464;
    }
    $lh_roumu        = $l_roumu - $s_roumu_sagaku - $b_roumu_sagaku;
    $lh_roumu_sagaku = $lh_roumu;
    $l_roumu         = $l_roumu - $s_roumu_sagaku;               // 試験修理労務費をリニアの労務費よりマイナス
    $lh_roumu        = number_format(($lh_roumu / $tani), $keta);
    $l_roumu         = number_format(($l_roumu / $tani), $keta);
}
    ///// 前月
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修労務費'", $p1_ym);
if (getUniResult($query, $p1_s_roumu) < 1) {
    $p1_s_roumu        = 0;    // 検索失敗
    $p1_s_roumu_sagaku = 0;
} else {
    $p1_s_roumu_sagaku = $p1_s_roumu;
    $p1_s_roumu        = number_format(($p1_s_roumu / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='機工労務費'", $p1_ym);
if (getUniResult($query, $p1_b_roumu) < 1) {
    $p1_b_roumu        = 0;    // 検索失敗
    $p1_b_roumu_sagaku = 0;
} else {
    $p1_b_roumu_sagaku = $p1_b_roumu;
    $p1_b_roumu        = number_format(($p1_b_roumu / $tani), $keta);
}
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア給与配賦率'", $p1_ym);
    if (getUniResult($query, $p1_l_kyu_kin) < 1) {
        $p1_l_kyu_kin = 0;
    }
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア労務費'", $p1_ym);
if (getUniResult($query, $p1_l_roumu) < 1) {
    $p1_l_roumu         = 0 - $p1_s_roumu_sagaku;     // 検索失敗
    $p1_lh_roumu        = 0;
    $p1_lh_roumu_sagaku = 0;
} else {
    if ($p1_ym == 200912) {
        $p1_l_roumu = $p1_l_roumu + 182279;
    }
    if ($p1_ym >= 201001) {
        $p1_l_roumu = $p1_l_roumu + $p1_l_kyu_kin;   // リニア配賦給与を加味(全て標準)
        //$p1_l_roumu = $p1_l_roumu + 151313; // 添田さんの給与をC・Lは35%。試験修理に30%振分
    }
    if ($p1_ym == 201408) {
        $p1_l_roumu = $p1_l_roumu + 229464;
    }
    $p1_lh_roumu        = $p1_l_roumu - $p1_s_roumu_sagaku - $p1_b_roumu_sagaku;
    $p1_lh_roumu_sagaku = $p1_lh_roumu;
    $p1_l_roumu         = $p1_l_roumu - $p1_s_roumu_sagaku;               // 試験修理労務費をリニアの労務費よりマイナス
    $p1_lh_roumu        = number_format(($p1_lh_roumu / $tani), $keta);
    $p1_l_roumu         = number_format(($p1_l_roumu / $tani), $keta);
}
    ///// 前前月
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修労務費'", $p2_ym);
if (getUniResult($query, $p2_s_roumu) < 1) {
    $p2_s_roumu        = 0;    // 検索失敗
    $p2_s_roumu_sagaku = 0;
} else {
    $p2_s_roumu_sagaku = $p2_s_roumu;
    $p2_s_roumu        = number_format(($p2_s_roumu / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='機工労務費'", $p2_ym);
if (getUniResult($query, $p2_b_roumu) < 1) {
    $p2_b_roumu        = 0;    // 検索失敗
    $p2_b_roumu_sagaku = 0;
} else {
    $p2_b_roumu_sagaku = $p2_b_roumu;
    $p2_b_roumu        = number_format(($p2_b_roumu / $tani), $keta);
}
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア給与配賦率'", $p2_ym);
    if (getUniResult($query, $p2_l_kyu_kin) < 1) {
        $p2_l_kyu_kin = 0;
    }
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア労務費'", $p2_ym);
if (getUniResult($query, $p2_l_roumu) < 1) {
    $p2_l_roumu         = 0 - $p2_s_roumu_sagaku;     // 検索失敗
    $p2_lh_roumu        = 0;
    $p2_lh_roumu_sagaku = 0;
} else {
    if ($p2_ym == 200912) {
        $p2_l_roumu = $p2_l_roumu + 182279;
    }
    if ($p2_ym >= 201001) {
        $p2_l_roumu = $p2_l_roumu + $p2_l_kyu_kin;   // リニア配賦給与を加味(全て標準)
        //$p2_l_roumu = $p2_l_roumu + 151313;     // 添田さんの給与をC・Lは35%。試験修理に30%振分
    }
    if ($p2_ym == 201408) {
        $p2_l_roumu = $p2_l_roumu + 229464;
    }
    $p2_lh_roumu        = $p2_l_roumu - $p2_s_roumu_sagaku - $p2_b_roumu_sagaku;
    $p2_lh_roumu_sagaku = $p2_lh_roumu;
    $p2_l_roumu         = $p2_l_roumu - $p2_s_roumu_sagaku;               // 試験修理労務費をリニアの労務費よりマイナス
    $p2_lh_roumu        = number_format(($p2_lh_roumu / $tani), $keta);
    $p2_l_roumu         = number_format(($p2_l_roumu / $tani), $keta);
}
    ///// 今期累計
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='試修労務費'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_s_roumu) < 1) {
    $rui_s_roumu        = 0;    // 検索失敗
    $rui_s_roumu_sagaku = 0;
} else {
    $rui_s_roumu_sagaku = $rui_s_roumu;
    $rui_s_roumu        = number_format(($rui_s_roumu / $tani), $keta);
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='機工労務費'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_b_roumu) < 1) {
    $rui_b_roumu        = 0;    // 検索失敗
    $rui_b_roumu_sagaku = 0;
} else {
    $rui_b_roumu_sagaku = $rui_b_roumu;
    $rui_b_roumu        = number_format(($rui_b_roumu / $tani), $keta);
}
if ($yyyymm >= 201001) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='リニア給与配賦率'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_l_kyu_kin) < 1) {
        $rui_l_kyu_kin = 0;
    }
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='リニア労務費'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_l_roumu) < 1) {
    $rui_l_roumu         = 0 - $rui_s_roumu_sagaku;   // 検索失敗
    $rui_lh_roumu        = 0;
    $rui_lh_roumu_sagaku = 0;
} else {
    if ($yyyymm >= 200912 && $yyyymm <= 201003) {
        $rui_l_roumu = $rui_l_roumu + 182279;
    }
    if ($yyyymm >= 201001) {
        $rui_l_roumu = $rui_l_roumu + $rui_l_kyu_kin;   // リニア配賦給与を加味(全て標準)
        //$rui_l_roumu = $rui_l_roumu + 151313;   // 添田さんの給与をC・Lは35%。試験修理に30%振分
    }
    if ($yyyymm >= 201408 && $yyyymm <= 201503) {
        $rui_l_roumu = $rui_l_roumu + 229464;
    }
    $rui_lh_roumu        = $rui_l_roumu - $rui_s_roumu_sagaku - $rui_b_roumu_sagaku;
    $rui_lh_roumu_sagaku = $rui_lh_roumu;
    $rui_l_roumu         = $rui_l_roumu - $rui_s_roumu_sagaku;               // 試験修理労務費をリニアの労務費よりマイナス
    $rui_lh_roumu        = number_format(($rui_lh_roumu / $tani), $keta);
    $rui_l_roumu         = number_format(($rui_l_roumu / $tani), $keta);
}

/********** 経費(製造経費) **********/
    ///// 当月
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修製造経費'", $yyyymm);
if (getUniResult($query, $s_expense) < 1) {
    $s_expense        = 0;    // 検索失敗
    $s_expense_sagaku = 0;
} else {
    $s_expense_sagaku = $s_expense;
    $s_expense        = number_format(($s_expense / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='機工製造経費'", $yyyymm);
if (getUniResult($query, $b_expense) < 1) {
    $b_expense        = 0;    // 検索失敗
    $b_expense_sagaku = 0;
} else {
    $b_expense_sagaku = $b_expense;
    $b_expense        = number_format(($b_expense / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア製造経費'", $yyyymm);
if (getUniResult($query, $l_expense) < 1) {
    $l_expense         = 0 - $s_expense_sagaku;     // 検索失敗
    $lh_expense        = 0;
    $lh_expense_sagaku = 0;
} else {
    // 2012/02/08 追加 2012年1月度 業務委託費（平出横川派遣料）調整
    if ($yyyymm == 201201) {
        $l_expense +=1156130;
    }
    // 2012/03/05 追加 2012年1月度 業務委託費（平出横川派遣料）調整 戻し
    if ($yyyymm == 201202) {
        $l_expense -=1156130;
    }
    $lh_expense        = $l_expense - $s_expense_sagaku - $b_expense_sagaku;
    $lh_expense_sagaku = $lh_expense;
    $l_expense         = $l_expense - $s_expense_sagaku;               // 試験修理製造経費をリニアの製造経費よりマイナス
    $lh_expense        = number_format(($lh_expense / $tani), $keta);
    $l_expense         = number_format(($l_expense / $tani), $keta);
}
    ///// 前月
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修製造経費'", $p1_ym);
if (getUniResult($query, $p1_s_expense) < 1) {
    $p1_s_expense        = 0;    // 検索失敗
    $p1_s_expense_sagaku = 0;
} else {
    $p1_s_expense_sagaku = $p1_s_expense;
    $p1_s_expense        = number_format(($p1_s_expense / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='機工製造経費'", $p1_ym);
if (getUniResult($query, $p1_b_expense) < 1) {
    $p1_b_expense        = 0;    // 検索失敗
    $p1_b_expense_sagaku = 0;
} else {
    $p1_b_expense_sagaku = $p1_b_expense;
    $p1_b_expense        = number_format(($p1_b_expense / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア製造経費'", $p1_ym);
if (getUniResult($query, $p1_l_expense) < 1) {
    $p1_l_expense         = 0 - $p1_s_expense_sagaku;     // 検索失敗
    $p1_lh_expense        = 0;
    $p1_lh_expense_sagaku = 0;
} else {
    // 2012/02/08 追加 2012年1月度 業務委託費（平出横川派遣料）調整
    if ($p1_ym == 201201) {
        $p1_l_expense +=1156130;
    }
    // 2012/03/05 追加 2012年1月度 業務委託費（平出横川派遣料）調整 戻し
    if ($p1_ym == 201202) {
        $p1_l_expense -=1156130;
    }
    $p1_lh_expense        = $p1_l_expense - $p1_s_expense_sagaku - $p1_b_expense_sagaku;
    $p1_lh_expense_sagaku = $p1_lh_expense;
    $p1_l_expense         = $p1_l_expense - $p1_s_expense_sagaku;               // 試験修理製造経費をリニアの製造経費よりマイナス
    $p1_lh_expense        = number_format(($p1_lh_expense / $tani), $keta);
    $p1_l_expense         = number_format(($p1_l_expense / $tani), $keta);
}
    ///// 前前月
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修製造経費'", $p2_ym);
if (getUniResult($query, $p2_s_expense) < 1) {
    $p2_s_expense        = 0;    // 検索失敗
    $p2_s_expense_sagaku = 0;
} else {
    $p2_s_expense_sagaku = $p2_s_expense;
    $p2_s_expense        = number_format(($p2_s_expense / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='機工製造経費'", $p2_ym);
if (getUniResult($query, $p2_b_expense) < 1) {
    $p2_b_expense        = 0;    // 検索失敗
    $p2_b_expense_sagaku = 0;
} else {
    $p2_b_expense_sagaku = $p2_b_expense;
    $p2_b_expense        = number_format(($p2_b_expense / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア製造経費'", $p2_ym);
if (getUniResult($query, $p2_l_expense) < 1) {
    $p2_l_expense         = 0 - $p2_s_expense_sagaku;     // 検索失敗
    $p2_lh_expense        = 0;
    $p2_lh_expense_sagaku = 0;
} else {
    // 2012/02/08 追加 2012年1月度 業務委託費（平出横川派遣料）調整
    if ($p2_ym == 201201) {
        $p2_l_expense +=1156130;
    }
    // 2012/03/05 追加 2012年1月度 業務委託費（平出横川派遣料）調整 戻し
    if ($p2_ym == 201202) {
        $p2_l_expense -=1156130;
    }
    $p2_lh_expense        = $p2_l_expense - $p2_s_expense_sagaku - $p2_b_expense_sagaku;
    $p2_lh_expense_sagaku = $p2_lh_expense;
    $p2_l_expense         = $p2_l_expense - $p2_s_expense_sagaku;               // 試験修理製造経費をリニアの製造経費よりマイナス
    $p2_lh_expense        = number_format(($p2_lh_expense / $tani), $keta);
    $p2_l_expense         = number_format(($p2_l_expense / $tani), $keta);
}
    ///// 今期累計
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='試修製造経費'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_s_expense) < 1) {
    $rui_s_expense        = 0;    // 検索失敗
    $rui_s_expense_sagaku = 0;
} else {
    $rui_s_expense_sagaku = $rui_s_expense;
    $rui_s_expense        = number_format(($rui_s_expense / $tani), $keta);
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='機工製造経費'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_b_expense) < 1) {
    $rui_b_expense        = 0;    // 検索失敗
    $rui_b_expense_sagaku = 0;
} else {
    $rui_b_expense_sagaku = $rui_b_expense;
    $rui_b_expense        = number_format(($rui_b_expense / $tani), $keta);
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='リニア製造経費'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_l_expense) < 1) {
    $rui_l_expense         = 0 - $rui_s_expense_sagaku;   // 検索失敗
    $rui_lh_expense        = 0;
    $rui_lh_expense_sagaku = 0;
} else {
    // 2012/02/08 追加 2012年1月度 業務委託費（平出横川派遣料）調整
    if ($yyyymm >= 201201 && $yyyymm <= 201203) {
        $rui_l_expense +=1156130;
    }
    // 2012/03/05 追加 2012年1月度 業務委託費（平出横川派遣料）調整 戻し
    if ($yyyymm >= 201202 && $yyyymm <= 201203) {
        $rui_l_expense -=1156130;
    }
    $rui_lh_expense        = $rui_l_expense - $rui_s_expense_sagaku - $rui_b_expense_sagaku;
    $rui_lh_expense_sagaku = $rui_lh_expense;
    $rui_l_expense         = $rui_l_expense - $rui_s_expense_sagaku;               // 試験修理製造経費をリニアの製造経費よりマイナス
    $rui_lh_expense        = number_format(($rui_lh_expense / $tani), $keta);
    $rui_l_expense         = number_format(($rui_l_expense / $tani), $keta);
}

/********** 期末材料仕掛品棚卸高 **********/
    ///// 試験・修理
$p2_s_endinv = 0;
$p1_s_endinv = 0;
$s_endinv    = 0;
    ///// 当月
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='機工期末棚卸高'", $yyyymm);
if (getUniResult($query, $b_endinv) < 1) {
    $b_endinv        = 0;                      // 検索失敗
    $b_endinv_sagaku = 0;
} else {
    $b_endinv_sagaku = $b_endinv;
    $b_endinv        = ($b_endinv * (-1));     // 符号反転
    $b_endinv        = number_format(($b_endinv / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア期末棚卸高'", $yyyymm);
if (getUniResult($query, $l_endinv) < 1) {
    $l_endinv         = 0;                     // 検索失敗
    $lh_endinv        = 0;
    $lh_endinv_sagaku = 0;
} else {
    $lh_endinv        = $l_endinv - $s_endinv - $b_endinv_sagaku;
    $lh_endinv        = ($lh_endinv * (-1));
    $l_endinv         = ($l_endinv * (-1));    // 符号反転
    $lh_endinv_sagaku = $lh_endinv;
    $lh_endinv        = number_format(($lh_endinv / $tani), $keta);
    $l_endinv         = number_format(($l_endinv / $tani), $keta);
}
    ///// 前月
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='機工期末棚卸高'", $p1_ym);
if (getUniResult($query, $p1_b_endinv) < 1) {
    $p1_b_endinv        = 0;                         // 検索失敗
    $p1_b_endinv_sagaku = 0;
} else {
    $p1_b_endinv_sagaku = $p1_b_endinv;
    $p1_b_endinv        = ($p1_b_endinv * (-1));     // 符号反転
    $p1_b_endinv        = number_format(($p1_b_endinv / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア期末棚卸高'", $p1_ym);
if (getUniResult($query, $p1_l_endinv) < 1) {
    $p1_l_endinv         = 0;                        // 検索失敗
    $p1_lh_endinv        = 0;
    $p1_lh_endinv_sagaku = 0;
} else {
    $p1_lh_endinv        = $p1_l_endinv - $p1_s_endinv - $p1_b_endinv_sagaku;
    $p1_lh_endinv        = ($p1_lh_endinv * (-1));
    $p1_l_endinv         = ($p1_l_endinv * (-1));    // 符号反転
    $p1_lh_endinv_sagaku = $p1_lh_endinv;
    $p1_lh_endinv        = number_format(($p1_lh_endinv / $tani), $keta);
    $p1_l_endinv         = number_format(($p1_l_endinv / $tani), $keta);
}
    ///// 前前月
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='機工期末棚卸高'", $p2_ym);
if (getUniResult($query, $p2_b_endinv) < 1) {
    $p2_b_endinv        = 0;                         // 検索失敗
    $p2_b_endinv_sagaku = 0;
} else {
    $p2_b_endinv_sagaku = $p2_b_endinv;
    $p2_b_endinv        = ($p2_b_endinv * (-1));     // 符号反転
    $p2_b_endinv        = number_format(($p2_b_endinv / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア期末棚卸高'", $p2_ym);
if (getUniResult($query, $p2_l_endinv) < 1) {
    $p2_l_endinv         = 0;                        // 検索失敗
    $p2_lh_endinv        = 0;
    $p2_lh_endinv_sagaku = 0;
} else {
    $p2_lh_endinv        = $p2_l_endinv - $p2_s_endinv - $p2_b_endinv_sagaku;
    $p2_lh_endinv        = ($p2_lh_endinv * (-1));
    $p2_l_endinv         = ($p2_l_endinv * (-1));    // 符号反転
    $p2_lh_endinv_sagaku = $p2_lh_endinv;
    $p2_lh_endinv        = number_format(($p2_lh_endinv / $tani), $keta);
    $p2_l_endinv         = number_format(($p2_l_endinv / $tani), $keta);
}
    ///// 今期累計
    ///// 期末棚卸高の累計は当月と同じ

/********** 売上原価 **********/
    ///// 当月
    ///// 試験・修理
    $s_urigen        = $s_invent + $s_metarial_sagaku + $s_roumu_sagaku + $s_expense_sagaku + $s_endinv;
    $s_urigen_sagaku = $s_urigen;
    $s_urigen        = number_format(($s_urigen / $tani), $keta);
    ///// 機工
    $b_urigen        = $b_invent_sagaku + $b_metarial_sagaku + $b_roumu_sagaku + $b_expense_sagaku - $b_endinv_sagaku;
    $b_urigen_sagaku = $b_urigen;
    $b_urigen        = number_format(($b_urigen / $tani), $keta);
    ///// CL
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア売上原価'", $yyyymm);
if (getUniResult($query, $l_urigen) < 1) {
    $l_urigen         = 0 - $s_urigen_sagaku;     // 検索失敗
    $lh_urigen        = 0;                        // 検索失敗
    $lh_urigen_sagaku = 0;                        // 検索失敗
} else {
    if ($yyyymm == 200912) {
        $l_urigen = $l_urigen + 182279;
    }
    if ($yyyymm >= 201001) {
        $l_urigen = $l_urigen + $l_kyu_kin;     // 添田さんの給与をC・Lは35%。試験修理に30%振分
        //$l_urigen = $l_urigen + 151313;     // 添田さんの給与をC・Lは35%。試験修理に30%振分
    }
    // 2012/02/08 追加 2012年1月度 業務委託費（平出横川派遣料）調整
    if ($yyyymm == 201201) {
        $l_urigen +=1156130;
    }
    // 2012/03/05 追加 2012年1月度 業務委託費（平出横川派遣料）調整 戻し
    if ($yyyymm == 201202) {
        $l_urigen -=1156130;
    }
    if ($yyyymm == 201408) {
        $l_urigen +=229464;
    }
    $lh_urigen        = $l_urigen - $s_urigen_sagaku - $b_urigen_sagaku;
    $lh_urigen_sagaku = $lh_urigen;
    $l_urigen         = $l_urigen - $s_urigen_sagaku;        // 試験修理売上原価をリニアの売上原価よりマイナス
    $lh_urigen        = number_format(($lh_urigen / $tani), $keta);
    $l_urigen         = number_format(($l_urigen / $tani), $keta);
}

    ///// 前月
    ///// 試験・修理
    $p1_s_urigen        = $p1_s_invent + $p1_s_metarial_sagaku + $p1_s_roumu_sagaku + $p1_s_expense_sagaku + $p1_s_endinv;
    $p1_s_urigen_sagaku = $p1_s_urigen;
    $p1_s_urigen        = number_format(($p1_s_urigen / $tani), $keta);
    ///// 機工
    $p1_b_urigen        = $p1_b_invent_sagaku + $p1_b_metarial_sagaku + $p1_b_roumu_sagaku + $p1_b_expense_sagaku - $p1_b_endinv_sagaku;
    $p1_b_urigen_sagaku = $p1_b_urigen;
    $p1_b_urigen        = number_format(($p1_b_urigen / $tani), $keta);
    ///// CL
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア売上原価'", $p1_ym);
if (getUniResult($query, $p1_l_urigen) < 1) {
    $p1_l_urigen         = 0 - $p1_s_urigen_sagaku;     // 検索失敗
    $p1_lh_urigen        = 0;                           // 検索失敗
    $p1_lh_urigen_sagaku = 0;                           // 検索失敗
} else {
    if ($p1_ym == 200912) {
        $p1_l_urigen = $p1_l_urigen + 182279;
    }
    if ($p1_ym >= 201001) {
        $p1_l_urigen = $p1_l_urigen + $p1_l_kyu_kin;   // 添田さんの給与をC・Lは35%。試験修理に30%振分
        //$p1_l_urigen = $p1_l_urigen + 151313;   // 添田さんの給与をC・Lは35%。試験修理に30%振分
    }
    // 2012/02/08 追加 2012年1月度 業務委託費（平出横川派遣料）調整
    if ($p1_ym == 201201) {
        $p1_l_urigen +=1156130;
    }
    // 2012/03/05 追加 2012年1月度 業務委託費（平出横川派遣料）調整 戻し
    if ($p1_ym == 201202) {
        $p1_l_urigen -=1156130;
    }
    if ($p1_ym == 201408) {
        $p1_l_urigen +=229464;
    }
    $p1_lh_urigen        = $p1_l_urigen - $p1_s_urigen_sagaku - $p1_b_urigen_sagaku;
    $p1_lh_urigen_sagaku = $p1_lh_urigen;
    $p1_l_urigen         = $p1_l_urigen - $p1_s_urigen_sagaku;        // 試験修理売上原価をリニアの売上原価よりマイナス
    $p1_lh_urigen        = number_format(($p1_lh_urigen / $tani), $keta);
    $p1_l_urigen         = number_format(($p1_l_urigen / $tani), $keta);
}

    ///// 前前月
    ///// 試験・修理
    $p2_s_urigen        = $p2_s_invent + $p2_s_metarial_sagaku + $p2_s_roumu_sagaku + $p2_s_expense_sagaku + $p2_s_endinv;
    $p2_s_urigen_sagaku = $p2_s_urigen;
    $p2_s_urigen        = number_format(($p2_s_urigen / $tani), $keta);
    ///// 機工
    $p2_b_urigen        = $p2_b_invent_sagaku + $p2_b_metarial_sagaku + $p2_b_roumu_sagaku + $p2_b_expense_sagaku - $p2_b_endinv_sagaku;
    $p2_b_urigen_sagaku = $p2_b_urigen;
    $p2_b_urigen        = number_format(($p2_b_urigen / $tani), $keta);
    ///// CL
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア売上原価'", $p2_ym);
if (getUniResult($query, $p2_l_urigen) < 1) {
    $p2_l_urigen         = 0 - $p2_s_urigen_sagaku;     // 検索失敗
    $p2_lh_urigen        = 0;                           // 検索失敗
    $p2_lh_urigen_sagaku = 0;                           // 検索失敗
} else {
    if ($p2_ym == 200912) {
        $p2_l_urigen = $p2_l_urigen + 182279;
    }
    if ($p2_ym >= 201001) {
        $p2_l_urigen = $p2_l_urigen + $p2_l_kyu_kin;   // 添田さんの給与をC・Lは35%。試験修理に30%振分
        //$p2_l_urigen = $p2_l_urigen + 151313;   // 添田さんの給与をC・Lは35%。試験修理に30%振分
    }
    // 2012/02/08 追加 2012年1月度 業務委託費（平出横川派遣料）調整
    if ($p2_ym == 201201) {
        $p2_l_urigen +=1156130;
    }
    // 2012/03/05 追加 2012年1月度 業務委託費（平出横川派遣料）調整 戻し
    if ($p2_ym == 201202) {
        $p2_l_urigen -=1156130;
    }
    if ($p2_ym == 201408) {
        $p2_l_urigen +=229464;
    }
    $p2_lh_urigen        = $p2_l_urigen - $p2_s_urigen_sagaku - $p2_b_urigen_sagaku;
    $p2_lh_urigen_sagaku = $p2_lh_urigen;
    $p2_l_urigen         = $p2_l_urigen - $p2_s_urigen_sagaku;        // 試験修理売上原価をリニアの売上原価よりマイナス
    $p2_lh_urigen        = number_format(($p2_lh_urigen / $tani), $keta);
    $p2_l_urigen = number_format(($p2_l_urigen / $tani), $keta);
}

    ///// 今期累計
    ///// 試験・修理
    $rui_s_urigen        = $rui_s_invent + $rui_s_metarial_sagaku + $rui_s_roumu_sagaku + $rui_s_expense_sagaku + $s_endinv;
    $rui_s_urigen_sagaku = $rui_s_urigen;
    $rui_s_urigen        = number_format(($rui_s_urigen / $tani), $keta);
    ///// 機工
    $rui_b_urigen        = $rui_b_invent_sagaku + $rui_b_metarial_sagaku + $rui_b_roumu_sagaku + $rui_b_expense_sagaku - $b_endinv_sagaku;
    $rui_b_urigen_sagaku = $rui_b_urigen;
    $rui_b_urigen        = number_format(($rui_b_urigen / $tani), $keta);
    ///// CL
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='全体売上原価'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_all_urigen) < 1) {
    $rui_all_urigen = 0;                                // 検索失敗
} else {
    $rui_all_urigen = number_format(($rui_all_urigen / $tani), $keta);
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='リニア売上原価'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_l_urigen) < 1) {
    $rui_l_urigen         = 0 - $rui_s_urigen_sagaku;   // 検索失敗
    $rui_lh_urigen        = 0;                          // 検索失敗
    $rui_lh_urigen_sagaku = 0;                          // 検索失敗
} else {
    if ($yyyymm >= 200912 && $yyyymm <= 201003) {
        $rui_l_urigen = $rui_l_urigen + 182279;
    }
    if ($yyyymm >= 201001) {
        $rui_l_urigen = $rui_l_urigen + $rui_l_kyu_kin;     // 添田さんの給与をC・Lは35%。試験修理に30%振分
        //$rui_l_urigen = $rui_l_urigen + 151313;     // 添田さんの給与をC・Lは35%。試験修理に30%振分
    }
    // 2012/02/08 追加 2012年1月度 業務委託費（平出横川派遣料）調整
    if ($yyyymm >= 201201 && $yyyymm <= 201203) {
        $rui_l_urigen +=1156130;
    }
    // 2012/03/05 追加 2012年1月度 業務委託費（平出横川派遣料）調整 戻し
    if ($yyyymm >= 201202 && $yyyymm <= 201203) {
        $rui_l_urigen -=1156130;
    }
    if ($yyyymm >= 201408 && $yyyymm <= 201503) {
        $rui_l_urigen = $rui_l_urigen + 229464;
    }
    $rui_lh_urigen        = $rui_l_urigen - $rui_s_urigen_sagaku - $rui_b_urigen_sagaku;
    $rui_lh_urigen_sagaku = $rui_lh_urigen;
    $rui_l_urigen         = $rui_l_urigen - $rui_s_urigen_sagaku;        // 試験修理売上原価をリニアの売上原価よりマイナス
    $rui_lh_urigen        = number_format(($rui_lh_urigen / $tani), $keta);
    $rui_l_urigen         = number_format(($rui_l_urigen / $tani), $keta);
}

/********** 売上総利益 **********/
    ///// 試験・修理
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
    ///// 機工
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

    ///// 当月
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア総利益'", $yyyymm);
if (getUniResult($query, $l_gross_profit) < 1) {
    $l_gross_profit         = 0 - $s_gross_profit_sagaku;     // 検索失敗
    $lh_gross_profit        = 0;                              // 検索失敗
    $lh_gross_profit_sagaku = 0;                              // 検索失敗
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
        $l_gross_profit = $l_gross_profit - $l_kyu_kin;     // 添田さんの給与をC・Lは35%。試験修理に30%振分
        //$l_gross_profit = $l_gross_profit - 151313;     // 添田さんの給与をC・Lは35%。試験修理に30%振分
    }
    if ($yyyymm == 201004) {
        $l_gross_profit = $l_gross_profit - 255240;
    }
    // 2012/02/08 追加 2012年1月度 業務委託費（平出横川派遣料）調整
    if ($yyyymm == 201201) {
        $l_gross_profit -=1156130;
    }
    // 2012/03/05 追加 2012年1月度 業務委託費（平出横川派遣料）調整 戻し
    if ($yyyymm == 201202) {
        $l_gross_profit +=1156130;
    }
    if ($yyyymm == 201408) {
        $l_gross_profit -=229464;
    }
    $lh_gross_profit        = $l_gross_profit - $s_gross_profit_sagaku - $b_gross_profit_sagaku;
    $lh_gross_profit_sagaku = $lh_gross_profit;
    $l_gross_profit         = $l_gross_profit - $s_gross_profit_sagaku;     // 試験修理売上総利益をリニアの売上総利益よりマイナス
    $lh_gross_profit        = number_format(($lh_gross_profit / $tani), $keta);
    $l_gross_profit         = number_format(($l_gross_profit / $tani), $keta);
}
    ///// 前月
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア総利益'", $p1_ym);
if (getUniResult($query, $p1_l_gross_profit) < 1) {
    $p1_l_gross_profit         = 0 - $p1_s_gross_profit_sagaku;     // 検索失敗
    $p1_lh_gross_profit        = 0;                                 // 検索失敗
    $p1_lh_gross_profit_sagaku = 0;                                 // 検索失敗
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
        $p1_l_gross_profit = $p1_l_gross_profit - $p1_l_kyu_kin;   // 添田さんの給与をC・Lは35%。試験修理に30%振分
        //$p1_l_gross_profit = $p1_l_gross_profit - 151313;   // 添田さんの給与をC・Lは35%。試験修理に30%振分
    }
    if ($p1_ym == 201004) {
        $p1_l_gross_profit = $p1_l_gross_profit - 255240;
    }
    // 2012/02/08 追加 2012年1月度 業務委託費（平出横川派遣料）調整
    if ($p1_ym == 201201) {
        $p1_l_gross_profit -=1156130;
    }
    // 2012/03/05 追加 2012年1月度 業務委託費（平出横川派遣料）調整 戻し
    if ($p1_ym == 201202) {
        $p1_l_gross_profit +=1156130;
    }
    if ($p1_ym == 201408) {
        $p1_l_gross_profit -=229464;
    }
    $p1_lh_gross_profit        = $p1_l_gross_profit - $p1_s_gross_profit_sagaku - $p1_b_gross_profit_sagaku;
    $p1_lh_gross_profit_sagaku = $p1_lh_gross_profit;
    $p1_l_gross_profit         = $p1_l_gross_profit - $p1_s_gross_profit_sagaku;     // 試験修理売上総利益をリニアの売上総利益よりマイナス
    $p1_lh_gross_profit        = number_format(($p1_lh_gross_profit / $tani), $keta);
    $p1_l_gross_profit         = number_format(($p1_l_gross_profit / $tani), $keta);
}
    ///// 前前月
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア総利益'", $p2_ym);
if (getUniResult($query, $p2_l_gross_profit) < 1) {
    $p2_l_gross_profit         = 0 - $p2_s_gross_profit_sagaku;     // 検索失敗
    $p2_lh_gross_profit        = 0;                                 // 検索失敗
    $p2_lh_gross_profit_sagaku = 0;                                 // 検索失敗
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
        $p2_l_gross_profit = $p2_l_gross_profit - $p2_l_kyu_kin;   // 添田さんの給与をC・Lは35%。試験修理に30%振分
        //$p2_l_gross_profit = $p2_l_gross_profit - 151313;   // 添田さんの給与をC・Lは35%。試験修理に30%振分
    }
    if ($p2_ym == 201004) {
        $p2_l_gross_profit = $p2_l_gross_profit - 255240;
    }
    // 2012/02/08 追加 2012年1月度 業務委託費（平出横川派遣料）調整
    if ($p2_ym == 201201) {
        $p2_l_gross_profit -=1156130;
    }
    // 2012/03/05 追加 2012年1月度 業務委託費（平出横川派遣料）調整 戻し
    if ($p2_ym == 201202) {
        $p2_l_gross_profit +=1156130;
    }
    if ($p2_ym == 201408) {
        $p2_l_gross_profit -=229464;
    }
    $p2_lh_gross_profit        = $p2_l_gross_profit - $p2_s_gross_profit_sagaku - $p2_b_gross_profit_sagaku;
    $p2_lh_gross_profit_sagaku = $p2_lh_gross_profit;
    $p2_l_gross_profit         = $p2_l_gross_profit - $p2_s_gross_profit_sagaku;     // 試験修理売上総利益をリニアの売上総利益よりマイナス
    $p2_lh_gross_profit        = number_format(($p2_lh_gross_profit / $tani), $keta);
    $p2_l_gross_profit         = number_format(($p2_l_gross_profit / $tani), $keta);
}
    ///// 今期累計
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='リニア総利益'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_l_gross_profit) < 1) {
    $rui_l_gross_profit         = 0 - $rui_s_gross_profit_sagaku;   // 検索失敗
    $rui_lh_gross_profit        = 0;                                // 検索失敗
    $rui_lh_gross_profit_sagaku = 0;                                // 検索失敗
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
    // 2012/02/08 追加 2012年1月度 業務委託費（平出横川派遣料）調整
    if ($yyyymm >= 201201 && $yyyymm <= 201203) {
        $rui_l_gross_profit -=1156130;
    }
    // 2012/03/05 追加 2012年1月度 業務委託費（平出横川派遣料）調整 戻し
    if ($yyyymm >= 201202 && $yyyymm <= 201203) {
        $rui_l_gross_profit +=1156130;
    }
    if ($yyyymm >= 201408 && $yyyymm <= 201503) {
        $rui_l_gross_profit = $rui_l_gross_profit - 229464;
    }
    $rui_lh_gross_profit        = $rui_l_gross_profit - $rui_s_gross_profit_sagaku - $rui_b_gross_profit_sagaku;
    $rui_lh_gross_profit_sagaku = $rui_lh_gross_profit;
    $rui_l_gross_profit         = $rui_l_gross_profit - $rui_s_gross_profit_sagaku;     // 試験修理売上総利益をリニアの売上総利益よりマイナス
    $rui_lh_gross_profit        = number_format(($rui_lh_gross_profit / $tani), $keta);
    $rui_l_gross_profit         = number_format(($rui_l_gross_profit / $tani), $keta);
}

/********** 販管費の人件費 **********/
    ///// 当月
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修人件費'", $yyyymm);
if (getUniResult($query, $s_han_jin) < 1) {
    $s_han_jin        = 0;    // 検索失敗
    $s_han_jin_sagaku = 0;
} else {
    $s_han_jin_sagaku = $s_han_jin;
    $s_han_jin        = number_format(($s_han_jin / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='機工人件費'", $yyyymm);
if (getUniResult($query, $b_han_jin) < 1) {
    $b_han_jin        = 0;    // 検索失敗
    $b_han_jin_sagaku = 0;
} else {
    $b_han_jin_sagaku = $b_han_jin;
    $b_han_jin        = number_format(($b_han_jin / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア商管社員按分給与'", $yyyymm);
if (getUniResult($query, $l_allo_kin) < 1) {
    $l_allo_kin       = 0;    // 検索失敗
} else {
    //$c_han_jin = number_format(($c_han_jin / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア人件費'", $yyyymm);
if (getUniResult($query, $l_han_jin) < 1) {
    $l_han_jin         = 0 - $s_han_jin_sagaku;     // 検索失敗
    $lh_han_jin        = 0;                         // 検索失敗
    $lh_han_jin_sagaku = 0;                         // 検索失敗
} else {
    $l_han_jin         = $l_han_jin - $l_allo_kin;
    $lh_han_jin        = $l_han_jin - $s_han_jin_sagaku - $b_han_jin_sagaku;
    $lh_han_jin_sagaku = $lh_han_jin;
    $l_han_jin         = $l_han_jin - $s_han_jin_sagaku;     // 試験修理人件費をリニアの人件費よりマイナス
    $lh_han_jin        = number_format(($lh_han_jin / $tani), $keta);
    $l_han_jin         = number_format(($l_han_jin / $tani), $keta);
}
    ///// 前月
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修人件費'", $p1_ym);
if (getUniResult($query, $p1_s_han_jin) < 1) {
    $p1_s_han_jin        = 0;    // 検索失敗
    $p1_s_han_jin_sagaku = 0;
} else {
    $p1_s_han_jin_sagaku = $p1_s_han_jin;
    $p1_s_han_jin        = number_format(($p1_s_han_jin / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='機工人件費'", $p1_ym);
if (getUniResult($query, $p1_b_han_jin) < 1) {
    $p1_b_han_jin        = 0;    // 検索失敗
    $p1_b_han_jin_sagaku = 0;
} else {
    $p1_b_han_jin_sagaku = $p1_b_han_jin;
    $p1_b_han_jin        = number_format(($p1_b_han_jin / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア商管社員按分給与'", $p1_ym);
if (getUniResult($query, $p1_l_allo_kin) < 1) {
    $p1_l_allo_kin       = 0;    // 検索失敗
} else {
    //$c_han_jin = number_format(($c_han_jin / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア人件費'", $p1_ym);
if (getUniResult($query, $p1_l_han_jin) < 1) {
    $p1_l_han_jin         = 0 - $p1_s_han_jin_sagaku;     // 検索失敗
    $p1_lh_han_jin        = 0;                            // 検索失敗
    $p1_lh_han_jin_sagaku = 0;                            // 検索失敗
} else {
    $p1_l_han_jin         = $p1_l_han_jin - $p1_l_allo_kin;
    $p1_lh_han_jin        = $p1_l_han_jin - $p1_s_han_jin_sagaku - $p1_b_han_jin_sagaku;
    $p1_lh_han_jin_sagaku = $p1_lh_han_jin;
    $p1_l_han_jin         = $p1_l_han_jin - $p1_s_han_jin_sagaku;     // 試験修理人件費をリニアの人件費よりマイナス
    $p1_lh_han_jin        = number_format(($p1_lh_han_jin / $tani), $keta);
    $p1_l_han_jin         = number_format(($p1_l_han_jin / $tani), $keta);
}
    ///// 前前月
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修人件費'", $p2_ym);
if (getUniResult($query, $p2_s_han_jin) < 1) {
    $p2_s_han_jin        = 0;    // 検索失敗
    $p2_s_han_jin_sagaku = 0;
} else {
    $p2_s_han_jin_sagaku = $p2_s_han_jin;
    $p2_s_han_jin        = number_format(($p2_s_han_jin / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='機工人件費'", $p2_ym);
if (getUniResult($query, $p2_b_han_jin) < 1) {
    $p2_b_han_jin        = 0;    // 検索失敗
    $p2_b_han_jin_sagaku = 0;
} else {
    $p2_b_han_jin_sagaku = $p2_b_han_jin;
    $p2_b_han_jin        = number_format(($p2_b_han_jin / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア商管社員按分給与'", $p2_ym);
if (getUniResult($query, $p2_l_allo_kin) < 1) {
    $p2_l_allo_kin       = 0;    // 検索失敗
} else {
    //$c_han_jin = number_format(($c_han_jin / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア人件費'", $p2_ym);
if (getUniResult($query, $p2_l_han_jin) < 1) {
    $p2_l_han_jin         = 0 - $p2_s_han_jin_sagaku;     // 検索失敗
    $p2_lh_han_jin        = 0;                            // 検索失敗
    $p2_lh_han_jin_sagaku = 0;                            // 検索失敗
} else {
    $p2_l_han_jin         = $p2_l_han_jin - $p2_l_allo_kin;
    $p2_lh_han_jin        = $p2_l_han_jin - $p2_s_han_jin_sagaku - $p2_b_han_jin_sagaku;
    $p2_lh_han_jin_sagaku = $p2_lh_han_jin;
    $p2_l_han_jin         = $p2_l_han_jin - $p2_s_han_jin_sagaku;     // 試験修理人件費をリニアの人件費よりマイナス
    $p2_lh_han_jin        = number_format(($p2_lh_han_jin / $tani), $keta);
    $p2_l_han_jin         = number_format(($p2_l_han_jin / $tani), $keta);
}
    ///// 今期累計
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='試修人件費'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_s_han_jin) < 1) {
    $rui_s_han_jin        = 0;    // 検索失敗
    $rui_s_han_jin_sagaku = 0;
} else {
    $rui_s_han_jin_sagaku = $rui_s_han_jin;
    $rui_s_han_jin        = number_format(($rui_s_han_jin / $tani), $keta);
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='機工人件費'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_b_han_jin) < 1) {
    $rui_b_han_jin        = 0;    // 検索失敗
    $rui_b_han_jin_sagaku = 0;
} else {
    $rui_b_han_jin_sagaku = $rui_b_han_jin;
    $rui_b_han_jin        = number_format(($rui_b_han_jin / $tani), $keta);
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='リニア商管社員按分給与'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_l_allo_kin) < 1) {
    $rui_l_allo_kin       = 0;    // 検索失敗
} else {
    //$c_han_jin = number_format(($c_han_jin / $tani), $keta);
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='リニア人件費'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_l_han_jin) < 1) {
    $rui_l_han_jin         = 0 - $rui_s_han_jin_sagaku;   // 検索失敗
    $rui_lh_han_jin        = 0;                           // 検索失敗
    $rui_lh_han_jin_sagaku = 0;                           // 検索失敗
} else {
    $rui_l_han_jin         = $rui_l_han_jin - $rui_l_allo_kin;
    $rui_lh_han_jin        = $rui_l_han_jin - $rui_s_han_jin_sagaku - $rui_b_han_jin_sagaku;
    $rui_lh_han_jin_sagaku = $rui_lh_han_jin;
    $rui_l_han_jin         = $rui_l_han_jin - $rui_s_han_jin_sagaku;     // 試験修理人件費をリニアの人件費よりマイナス
    $rui_lh_han_jin        = number_format(($rui_lh_han_jin / $tani), $keta);
    $rui_l_han_jin         = number_format(($rui_l_han_jin / $tani), $keta);
}

/********** 販管費の経費 **********/
    ///// 当月
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修販管費経費'", $yyyymm);
if (getUniResult($query, $s_han_kei) < 1) {
    $s_han_kei        = 0;    // 検索失敗
    $s_han_kei_sagaku = 0;
} else {
    $s_han_kei_sagaku = $s_han_kei;
    $s_han_kei        = number_format(($s_han_kei / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='機工販管費経費'", $yyyymm);
if (getUniResult($query, $b_han_kei) < 1) {
    $b_han_kei        = 0;    // 検索失敗
    $b_han_kei_sagaku = 0;
} else {
    $b_han_kei_sagaku = $b_han_kei;
    $b_han_kei        = number_format(($b_han_kei / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア経費'", $yyyymm);
if (getUniResult($query, $l_han_kei) < 1) {
    $l_han_kei         = 0 - $s_han_kei_sagaku;     // 検索失敗
    $lh_han_kei        = 0;                         // 検索失敗
    $lh_han_kei_sagaku = 0;                         // 検索失敗
} else {
    $lh_han_kei        = $l_han_kei - $s_han_kei_sagaku - $b_han_kei_sagaku;
    $lh_han_kei_sagaku = $lh_han_kei;
    $l_han_kei         = $l_han_kei - $s_han_kei_sagaku;     // 試験修理販管費経費をリニアの販管費経費よりマイナス
    $lh_han_kei        = number_format(($lh_han_kei / $tani), $keta);
    $l_han_kei         = number_format(($l_han_kei / $tani), $keta);
}
    ///// 前月
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修販管費経費'", $p1_ym);
if (getUniResult($query, $p1_s_han_kei) < 1) {
    $p1_s_han_kei        = 0;    // 検索失敗
    $p1_s_han_kei_sagaku = 0;
} else {
    $p1_s_han_kei_sagaku = $p1_s_han_kei;
    $p1_s_han_kei        = number_format(($p1_s_han_kei / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='機工販管費経費'", $p1_ym);
if (getUniResult($query, $p1_b_han_kei) < 1) {
    $p1_b_han_kei        = 0;    // 検索失敗
    $p1_b_han_kei_sagaku = 0;
} else {
    $p1_b_han_kei_sagaku = $p1_b_han_kei;
    $p1_b_han_kei        = number_format(($p1_b_han_kei / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア経費'", $p1_ym);
if (getUniResult($query, $p1_l_han_kei) < 1) {
    $p1_l_han_kei         = 0 - $p1_s_han_kei_sagaku;     // 検索失敗
    $p1_lh_han_kei        = 0;                            // 検索失敗
    $p1_lh_han_kei_sagaku = 0;                            // 検索失敗
} else {
    $p1_lh_han_kei        = $p1_l_han_kei - $p1_s_han_kei_sagaku - $p1_b_han_kei_sagaku;
    $p1_lh_han_kei_sagaku = $p1_lh_han_kei;
    $p1_l_han_kei         = $p1_l_han_kei - $p1_s_han_kei_sagaku;     // 試験修理販管費経費をリニアの販管費経費よりマイナス
    $p1_lh_han_kei        = number_format(($p1_lh_han_kei / $tani), $keta);
    $p1_l_han_kei         = number_format(($p1_l_han_kei / $tani), $keta);
}
    ///// 前前月
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修販管費経費'", $p2_ym);
if (getUniResult($query, $p2_s_han_kei) < 1) {
    $p2_s_han_kei        = 0;    // 検索失敗
    $p2_s_han_kei_sagaku = 0;
} else {
    $p2_s_han_kei_sagaku = $p2_s_han_kei;
    $p2_s_han_kei        = number_format(($p2_s_han_kei / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='機工販管費経費'", $p2_ym);
if (getUniResult($query, $p2_b_han_kei) < 1) {
    $p2_b_han_kei        = 0;    // 検索失敗
    $p2_b_han_kei_sagaku = 0;
} else {
    $p2_b_han_kei_sagaku = $p2_b_han_kei;
    $p2_b_han_kei        = number_format(($p2_b_han_kei / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア経費'", $p2_ym);
if (getUniResult($query, $p2_l_han_kei) < 1) {
    $p2_l_han_kei         = 0 - $p2_s_han_kei_sagaku;     // 検索失敗
    $p2_lh_han_kei        = 0;                            // 検索失敗
    $p2_lh_han_kei_sagaku = 0;                            // 検索失敗
} else {
    $p2_lh_han_kei        = $p2_l_han_kei - $p2_s_han_kei_sagaku - $p2_b_han_kei_sagaku;
    $p2_lh_han_kei_sagaku = $p2_lh_han_kei;
    $p2_l_han_kei         = $p2_l_han_kei - $p2_s_han_kei_sagaku;     // 試験修理販管費経費をリニアの販管費経費よりマイナス
    $p2_lh_han_kei        = number_format(($p2_lh_han_kei / $tani), $keta);
    $p2_l_han_kei         = number_format(($p2_l_han_kei / $tani), $keta);
}
    ///// 今期累計
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='試修販管費経費'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_s_han_kei) < 1) {
    $rui_s_han_kei        = 0;    // 検索失敗
    $rui_s_han_kei_sagaku = 0;
} else {
    $rui_s_han_kei_sagaku = $rui_s_han_kei;
    $rui_s_han_kei        = number_format(($rui_s_han_kei / $tani), $keta);
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='機工販管費経費'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_b_han_kei) < 1) {
    $rui_b_han_kei        = 0;    // 検索失敗
    $rui_b_han_kei_sagaku = 0;
} else {
    $rui_b_han_kei_sagaku = $rui_b_han_kei;
    $rui_b_han_kei        = number_format(($rui_b_han_kei / $tani), $keta);
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='リニア経費'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_l_han_kei) < 1) {
    $rui_l_han_kei         = 0 - $rui_s_han_kei_sagaku;   // 検索失敗
    $rui_lh_han_kei        = 0;                           // 検索失敗
    $rui_lh_han_kei_sagaku = 0;                           // 検索失敗
} else {
    $rui_lh_han_kei        = $rui_l_han_kei - $rui_s_han_kei_sagaku - $rui_b_han_kei_sagaku;
    $rui_lh_han_kei_sagaku = $rui_lh_han_kei;
    $rui_l_han_kei         = $rui_l_han_kei - $rui_s_han_kei_sagaku;     // 試験修理販管費経費をリニアの販管費経費よりマイナス
    $rui_lh_han_kei        = number_format(($rui_lh_han_kei / $tani), $keta);
    $rui_l_han_kei         = number_format(($rui_l_han_kei / $tani), $keta);
}

/********** 販管費の合計 **********/
    ///// 当月
    ///// 試験・修理
    $s_han_all        = $s_han_jin_sagaku + $s_han_kei_sagaku;
    $s_han_all_sagaku = $s_han_all;
    $s_han_all        = number_format(($s_han_all / $tani), $keta);
    ///// 当月
    ///// 機工
    $b_han_all        = $b_han_jin_sagaku + $b_han_kei_sagaku;
    $b_han_all_sagaku = $b_han_all;
    $b_han_all        = number_format(($b_han_all / $tani), $keta);
    ///// CL
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア販管費'", $yyyymm);
if (getUniResult($query, $l_han_all) < 1) {
    $l_han_all         = 0 - $s_han_all_sagaku;     // 検索失敗
    $lh_han_all        = 0;                         // 検索失敗
    $lh_han_all_sagaku = 0;                         // 検索失敗
} else {
    $l_han_all         = $l_han_all - $l_allo_kin;
    $lh_han_all        = $l_han_all - $s_han_all_sagaku - $b_han_all_sagaku;
    $lh_han_all_sagaku = $lh_han_all;
    $l_han_all         = $l_han_all - $s_han_all_sagaku;     // 試験修理販管費合計をリニアの販管費合計よりマイナス
    $lh_han_all        = number_format(($lh_han_all / $tani), $keta);
    $l_han_all         = number_format(($l_han_all / $tani), $keta);
}

    ///// 前月
    ///// 試験・修理
    $p1_s_han_all        = $p1_s_han_jin_sagaku + $p1_s_han_kei_sagaku;
    $p1_s_han_all_sagaku = $p1_s_han_all;
    $p1_s_han_all        = number_format(($p1_s_han_all / $tani), $keta);
    ///// 機工
    $p1_b_han_all        = $p1_b_han_jin_sagaku + $p1_b_han_kei_sagaku;
    $p1_b_han_all_sagaku = $p1_b_han_all;
    $p1_b_han_all        = number_format(($p1_b_han_all / $tani), $keta);
    ///// CL
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア販管費'", $p1_ym);
if (getUniResult($query, $p1_l_han_all) < 1) {
    $p1_l_han_all         = 0 - $p1_s_han_all_sagaku;     // 検索失敗
    $p1_lh_han_all        = 0;                            // 検索失敗
    $p1_lh_han_all_sagaku = 0;                            // 検索失敗
} else {
    $p1_l_han_all         = $p1_l_han_all - $p1_l_allo_kin;
    $p1_lh_han_all        = $p1_l_han_all - $p1_s_han_all_sagaku - $p1_b_han_all_sagaku;
    $p1_lh_han_all_sagaku = $p1_lh_han_all;
    $p1_l_han_all         = $p1_l_han_all - $p1_s_han_all_sagaku;     // 試験修理販管費合計をリニアの販管費合計よりマイナス
    $p1_lh_han_all        = number_format(($p1_lh_han_all / $tani), $keta);
    $p1_l_han_all         = number_format(($p1_l_han_all / $tani), $keta);
}

    ///// 前前月
    ///// 試験・修理
    $p2_s_han_all        = $p2_s_han_jin_sagaku + $p2_s_han_kei_sagaku;
    $p2_s_han_all_sagaku = $p2_s_han_all;
    $p2_s_han_all        = number_format(($p2_s_han_all / $tani), $keta);
    ///// 機工
    $p2_b_han_all        = $p2_b_han_jin_sagaku + $p2_b_han_kei_sagaku;
    $p2_b_han_all_sagaku = $p2_b_han_all;
    $p2_b_han_all        = number_format(($p2_b_han_all / $tani), $keta);
    ///// CL
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア販管費'", $p2_ym);
if (getUniResult($query, $p2_l_han_all) < 1) {
    $p2_l_han_all         = 0 - $p2_s_han_all_sagaku;     // 検索失敗
    $p2_lh_han_all        = 0;                            // 検索失敗
    $p2_lh_han_all_sagaku = 0;                            // 検索失敗
} else {
    $p2_l_han_all         = $p2_l_han_all - $p2_l_allo_kin;
    $p2_lh_han_all        = $p2_l_han_all - $p2_s_han_all_sagaku - $p2_b_han_all_sagaku;
    $p2_lh_han_all_sagaku = $p2_lh_han_all;
    $p2_l_han_all         = $p2_l_han_all - $p2_s_han_all_sagaku;     // 試験修理販管費合計をリニアの販管費合計よりマイナス
    $p2_lh_han_all        = number_format(($p2_lh_han_all / $tani), $keta);
    $p2_l_han_all         = number_format(($p2_l_han_all / $tani), $keta);
}

    ///// 今期累計
    ///// 試験・修理
    $rui_s_han_all        = $rui_s_han_jin_sagaku + $rui_s_han_kei_sagaku;
    $rui_s_han_all_sagaku = $rui_s_han_all;
    $rui_s_han_all        = number_format(($rui_s_han_all / $tani), $keta);
    ///// 機工
    $rui_b_han_all        = $rui_b_han_jin_sagaku + $rui_b_han_kei_sagaku;
    $rui_b_han_all_sagaku = $rui_b_han_all;
    $rui_b_han_all        = number_format(($rui_b_han_all / $tani), $keta);
    ///// CL
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='リニア販管費'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_l_han_all) < 1) {
    $rui_l_han_all         = 0 - $rui_s_han_all_sagaku;   // 検索失敗
    $rui_lh_han_all        = 0;                           // 検索失敗
    $rui_lh_han_all_sagaku = 0;                           // 検索失敗
} else {
    $rui_l_han_all         = $rui_l_han_all - $rui_l_allo_kin;
    $rui_lh_han_all        = $rui_l_han_all - $rui_s_han_all_sagaku - $rui_b_han_all_sagaku;
    $rui_lh_han_all_sagaku = $rui_lh_han_all;
    $rui_l_han_all         = $rui_l_han_all - $rui_s_han_all_sagaku;     // 試験修理販管費合計をリニアの販管費合計よりマイナス
    $rui_lh_han_all        = number_format(($rui_lh_han_all / $tani), $keta);
    $rui_l_han_all         = number_format(($rui_l_han_all / $tani), $keta);
}

/********** 営業利益 **********/
    ///// 試験・修理
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
    ///// 機工
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

    ///// 当月
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア営業利益'", $yyyymm);
if (getUniResult($query, $l_ope_profit) < 1) {
    $l_ope_profit         = 0 - $s_ope_profit_sagaku;     // 検索失敗
    $lh_ope_profit        = 0;                            // 検索失敗
    $lh_ope_profit_sagaku = 0;                            // 検索失敗
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
        $l_ope_profit = $l_ope_profit - $l_kyu_kin; // 添田さんの給与をC・Lは35%。試験修理に30%振分
        //$l_ope_profit = $l_ope_profit - 151313; // 添田さんの給与をC・Lは35%。試験修理に30%振分
    }
    if ($yyyymm == 201004) {
        $l_ope_profit = $l_ope_profit - 255240;
    }
    // 2012/02/08 追加 2012年1月度 業務委託費（平出横川派遣料）調整
    if ($yyyymm == 201201) {
        $l_ope_profit -=1156130;
    }
    // 2012/03/05 追加 2012年1月度 業務委託費（平出横川派遣料）調整 戻し
    if ($yyyymm == 201202) {
        $l_ope_profit +=1156130;
    }
    if ($yyyymm == 201408) {
        $l_ope_profit -=229464;
    }
    $lh_ope_profit        = $l_ope_profit - $s_ope_profit_sagaku - $b_ope_profit_sagaku;
    $lh_ope_profit_sagaku = $lh_ope_profit;
    $l_ope_profit         = $l_ope_profit - $s_ope_profit_sagaku;     // 試験修理営業利益をリニアの営業利益よりマイナス
    $lh_ope_profit        = number_format(($lh_ope_profit / $tani), $keta);
    $l_ope_profit         = number_format(($l_ope_profit / $tani), $keta);
}
    ///// 前月
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア営業利益'", $p1_ym);
if (getUniResult($query, $p1_l_ope_profit) < 1) {
    $p1_l_ope_profit         = 0 - $p1_s_ope_profit_sagaku;     // 検索失敗
    $p1_lh_ope_profit        = 0;                               // 検索失敗
    $p1_lh_ope_profit_sagaku = 0;                               // 検索失敗
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
        $p1_l_ope_profit = $p1_l_ope_profit - $p1_l_kyu_kin;   // 添田さんの給与をC・Lは35%。試験修理に30%振分
        //$p1_l_ope_profit = $p1_l_ope_profit - 151313;   // 添田さんの給与をC・Lは35%。試験修理に30%振分
    }
    if ($p1_ym == 201004) {
        $p1_l_ope_profit = $p1_l_ope_profit - 255240;
    }
    // 2012/02/08 追加 2012年1月度 業務委託費（平出横川派遣料）調整
    if ($p1_ym == 201201) {
        $p1_l_ope_profit -=1156130;
    }
    // 2012/03/05 追加 2012年1月度 業務委託費（平出横川派遣料）調整 戻し
    if ($p1_ym == 201202) {
        $p1_l_ope_profit +=1156130;
    }
    if ($p1_ym == 201408) {
        $p1_l_ope_profit -=229464;
    }
    $p1_l_ope_profit         = $p1_l_ope_profit  + $p1_l_allo_kin;
    $p1_lh_ope_profit        = $p1_l_ope_profit - $p1_s_ope_profit_sagaku - $p1_b_ope_profit_sagaku;
    $p1_lh_ope_profit_sagaku = $p1_lh_ope_profit;
    $p1_l_ope_profit         = $p1_l_ope_profit - $p1_s_ope_profit_sagaku;     // 試験修理営業利益をリニアの営業利益よりマイナス
    $p1_lh_ope_profit        = number_format(($p1_lh_ope_profit / $tani), $keta);
    $p1_l_ope_profit         = number_format(($p1_l_ope_profit / $tani), $keta);
}
    ///// 前前月
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア営業利益'", $p2_ym);
if (getUniResult($query, $p2_l_ope_profit) < 1) {
    $p2_l_ope_profit         = 0 - $p2_s_ope_profit_sagaku;     // 検索失敗
    $p2_lh_ope_profit        = 0;                               // 検索失敗
    $p2_lh_ope_profit_sagaku = 0;                               // 検索失敗
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
        $p2_l_ope_profit = $p2_l_ope_profit - $p2_l_kyu_kin;   // 添田さんの給与をC・Lは35%。試験修理に30%振分
        //$p2_l_ope_profit = $p2_l_ope_profit - 151313;   // 添田さんの給与をC・Lは35%。試験修理に30%振分
    }
    if ($p2_ym == 201004) {
        $p2_l_ope_profit = $p2_l_ope_profit - 255240;
    }
    // 2012/02/08 追加 2012年1月度 業務委託費（平出横川派遣料）調整
    if ($p2_ym == 201201) {
        $p2_l_ope_profit -=1156130;
    }
    // 2012/03/05 追加 2012年1月度 業務委託費（平出横川派遣料）調整 戻し
    if ($p2_ym == 201202) {
        $p2_l_ope_profit +=1156130;
    }
    if ($p2_ym == 201408) {
        $p2_l_ope_profit -=229464;
    }
    $p2_l_ope_profit         = $p2_l_ope_profit  + $p2_l_allo_kin;
    $p2_lh_ope_profit        = $p2_l_ope_profit - $p2_s_ope_profit_sagaku - $p2_b_ope_profit_sagaku;
    $p2_lh_ope_profit_sagaku = $p2_lh_ope_profit;
    $p2_l_ope_profit         = $p2_l_ope_profit - $p2_s_ope_profit_sagaku;     // 試験修理営業利益をリニアの営業利益よりマイナス
    $p2_lh_ope_profit        = number_format(($p2_lh_ope_profit / $tani), $keta);
    $p2_l_ope_profit         = number_format(($p2_l_ope_profit / $tani), $keta);
}
    ///// 今期累計
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='リニア営業利益'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_l_ope_profit) < 1) {
    $rui_l_ope_profit         = 0 - $rui_s_ope_profit_sagaku;   // 検索失敗
    $rui_lh_ope_profit        = 0;                              // 検索失敗
    $rui_lh_ope_profit_sagaku = 0;                              // 検索失敗
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
        $rui_l_ope_profit = $rui_l_ope_profit - $rui_l_kyu_kin; // 添田さんの給与をC・Lは35%。試験修理に30%振分
        //$rui_l_ope_profit = $rui_l_ope_profit - 151313; // 添田さんの給与をC・Lは35%。試験修理に30%振分
    }
    if ($yyyymm >= 201004 && $yyyymm <= 201103) {
        $rui_l_ope_profit = $rui_l_ope_profit - 255240;
    }
    // 2012/02/08 追加 2012年1月度 業務委託費（平出横川派遣料）調整
    if ($yyyymm >= 201201 && $yyyymm <= 201203) {
        $rui_l_ope_profit -=1156130;
    }
    // 2012/03/05 追加 2012年1月度 業務委託費（平出横川派遣料）調整 戻し
    if ($yyyymm >= 201202 && $yyyymm <= 201203) {
        $rui_l_ope_profit +=1156130;
    }
    if ($yyyymm >= 201408 && $yyyymm <= 201503) {
        $rui_l_ope_profit = $rui_l_ope_profit - 229464;
    }
    $rui_l_ope_profit         = $rui_l_ope_profit  + $rui_l_allo_kin;
    $rui_lh_ope_profit        = $rui_l_ope_profit - $rui_s_ope_profit_sagaku - $rui_b_ope_profit_sagaku;
    $rui_lh_ope_profit_sagaku = $rui_lh_ope_profit;
    $rui_l_ope_profit         = $rui_l_ope_profit - $rui_s_ope_profit_sagaku;     // 試験修理営業利益をリニアの営業利益よりマイナス
    $rui_l_ope_profit_temp = $rui_l_ope_profit;
    $rui_lh_ope_profit        = number_format(($rui_lh_ope_profit / $tani), $keta);
    $rui_l_ope_profit         = number_format(($rui_l_ope_profit / $tani), $keta);
}

/********** 営業外収益の業務委託収入 **********/
    ///// 当月
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修業務委託収入再計算'", $yyyymm);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修業務委託収入'", $yyyymm);
}
if (getUniResult($query, $s_gyoumu) < 1) {
    $s_gyoumu        = 0;                       // 検索失敗
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
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='機工業務委託収入再計算'", $yyyymm);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='機工業務委託収入'", $yyyymm);
}
if (getUniResult($query, $b_gyoumu) < 1) {
    $b_gyoumu = 0;    // 検索失敗
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
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア業務委託収入再計算'", $yyyymm);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア業務委託収入'", $yyyymm);
}
if (getUniResult($query, $l_gyoumu) < 1) {
    $l_gyoumu = 0 - $s_gyoumu_sagaku;     // 検索失敗
    $lh_gyoumu = 0;     // 検索失敗
    $lh_gyoumu_sagaku = 0;     // 検索失敗
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
    $l_gyoumu         = $l_gyoumu - $s_gyoumu_sagaku;     // 試験修理業務委託収入をリニアの業務委託収入よりマイナス
    $lh_gyoumu = number_format(($lh_gyoumu / $tani), $keta);
    $l_gyoumu = number_format(($l_gyoumu / $tani), $keta);
}
    ///// 前月
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修業務委託収入再計算'", $p1_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修業務委託収入'", $p1_ym);
}
if (getUniResult($query, $p1_s_gyoumu) < 1) {
    $p1_s_gyoumu        = 0;                       // 検索失敗
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
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='機工業務委託収入再計算'", $p1_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='機工業務委託収入'", $p1_ym);
}
if (getUniResult($query, $p1_b_gyoumu) < 1) {
    $p1_b_gyoumu = 0;    // 検索失敗
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
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア業務委託収入再計算'", $p1_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア業務委託収入'", $p1_ym);
}
if (getUniResult($query, $p1_l_gyoumu) < 1) {
    $p1_l_gyoumu = 0 - $p1_s_gyoumu_sagaku;     // 検索失敗
    $p1_lh_gyoumu = 0;     // 検索失敗
    $p1_lh_gyoumu_sagaku = 0;     // 検索失敗
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
    $p1_l_gyoumu         = $p1_l_gyoumu - $p1_s_gyoumu_sagaku;     // 試験修理業務委託収入をリニアの業務委託収入よりマイナス
    $p1_lh_gyoumu = number_format(($p1_lh_gyoumu / $tani), $keta);
    $p1_l_gyoumu = number_format(($p1_l_gyoumu / $tani), $keta);
}
    ///// 前前月
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修業務委託収入再計算'", $p2_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修業務委託収入'", $p2_ym);
}
if (getUniResult($query, $p2_s_gyoumu) < 1) {
    $p2_s_gyoumu        = 0;                       // 検索失敗
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
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='機工業務委託収入再計算'", $p2_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='機工業務委託収入'", $p2_ym);
}
if (getUniResult($query, $p2_b_gyoumu) < 1) {
    $p2_b_gyoumu = 0;    // 検索失敗
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
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア業務委託収入再計算'", $p2_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア業務委託収入'", $p2_ym);
}
if (getUniResult($query, $p2_l_gyoumu) < 1) {
    $p2_l_gyoumu = 0 - $p2_s_gyoumu_sagaku;     // 検索失敗
    $p2_lh_gyoumu = 0;     // 検索失敗
    $p2_lh_gyoumu_sagaku = 0;     // 検索失敗
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
    $p2_l_gyoumu         = $p2_l_gyoumu - $p2_s_gyoumu_sagaku;     // 試験修理業務委託収入をリニアの業務委託収入よりマイナス
    $p2_lh_gyoumu = number_format(($p2_lh_gyoumu / $tani), $keta);
    $p2_l_gyoumu = number_format(($p2_l_gyoumu / $tani), $keta);
}
    ///// 今期累計
if ($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='試修業務委託収入再計算'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_s_gyoumu) < 1) {
        $rui_s_gyoumu = 0;    // 検索失敗
        $rui_s_gyoumu_sagaku = 0;
    } else {
        $rui_s_gyoumu_sagaku = $rui_s_gyoumu;
        $rui_s_gyoumu = number_format(($rui_s_gyoumu / $tani), $keta);
    }
} elseif ($yyyymm >= 201001 && $yyyymm <= 201003) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=200904 and pl_bs_ym<=200912 and note='試修業務委託収入'");
    if (getUniResult($query, $rui_s_gyoumu_a) < 1) {
        $rui_s_gyoumu_a = 0;                          // 検索失敗
    }
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=201001 and pl_bs_ym<=%d and note='試修業務委託収入再計算'", $yyyymm);
    if (getUniResult($query, $rui_s_gyoumu_b) < 1) {
        $rui_s_gyoumu_b = 0;                          // 検索失敗
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
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='試修業務委託収入'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_s_gyoumu) < 1) {
        $rui_s_gyoumu        = 0;                   // 検索失敗
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
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='機工業務委託収入再計算'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_b_gyoumu) < 1) {
        $rui_b_gyoumu = 0;    // 検索失敗
        $rui_b_gyoumu_sagaku = 0;
    } else {
        $rui_b_gyoumu_sagaku = $rui_b_gyoumu;
        $rui_b_gyoumu = number_format(($rui_b_gyoumu / $tani), $keta);
    }
} elseif ($yyyymm >= 201001 && $yyyymm <= 201003) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=200904 and pl_bs_ym<=200912 and note='機工業務委託収入'");
    if (getUniResult($query, $rui_b_gyoumu_a) < 1) {
        $rui_b_gyoumu_a = 0;                          // 検索失敗
    }
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=201001 and pl_bs_ym<=%d and note='機工業務委託収入再計算'", $yyyymm);
    if (getUniResult($query, $rui_b_gyoumu_b) < 1) {
        $rui_b_gyoumu_b = 0;                          // 検索失敗
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
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='機工業務委託収入'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_b_gyoumu) < 1) {
        $rui_b_gyoumu = 0;    // 検索失敗
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
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='リニア業務委託収入再計算'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_l_gyoumu) < 1) {
        $rui_l_gyoumu = 0 - $rui_s_gyoumu_sagaku;   // 検索失敗
        $rui_lh_gyoumu = 0;     // 検索失敗
        $rui_lh_gyoumu_sagaku = 0;     // 検索失敗
    } else {
        $rui_l_gyoumu = $rui_l_gyoumu + $rui_s_gyoumu_sagaku;
        $rui_lh_gyoumu = $rui_l_gyoumu - $rui_s_gyoumu_sagaku - $rui_b_gyoumu_sagaku;
        $rui_lh_gyoumu_sagaku = $rui_lh_gyoumu;
        $rui_l_gyoumu         = $rui_l_gyoumu - $rui_s_gyoumu_sagaku;     // 試験修理業務委託収入をリニアの業務委託収入よりマイナス
        $rui_lh_gyoumu = number_format(($rui_lh_gyoumu / $tani), $keta);
        $rui_l_gyoumu = number_format(($rui_l_gyoumu / $tani), $keta);
    }
} elseif ($yyyymm >= 201001 && $yyyymm <= 201003) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=200904 and pl_bs_ym<=200912 and note='リニア業務委託収入'");
    if (getUniResult($query, $rui_l_gyoumu_a) < 1) {
        $rui_l_gyoumu_a = 0;                          // 検索失敗
    }
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=201001 and pl_bs_ym<=%d and note='リニア業務委託収入再計算'", $yyyymm);
    if (getUniResult($query, $rui_l_gyoumu_b) < 1) {
        $rui_l_gyoumu_b = 0;                          // 検索失敗
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
    $rui_l_gyoumu         = $rui_l_gyoumu - $rui_s_gyoumu_sagaku;     // 試験修理業務委託収入をリニアの業務委託収入よりマイナス
    $rui_lh_gyoumu = number_format(($rui_lh_gyoumu / $tani), $keta);
    $rui_l_gyoumu = number_format(($rui_l_gyoumu / $tani), $keta);
} else {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='リニア業務委託収入'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_l_gyoumu) < 1) {
        $rui_l_gyoumu = 0 - $rui_s_gyoumu_sagaku;   // 検索失敗
        $rui_lh_gyoumu = 0;     // 検索失敗
        $rui_lh_gyoumu_sagaku = 0;     // 検索失敗
    } else {
        if ($yyyymm >= 200912 && $yyyymm <= 201003) {
            $rui_l_gyoumu = $rui_l_gyoumu - 76191;
        }
        //$rui_l_gyoumu = $rui_l_gyoumu + $rui_s_gyoumu_sagaku;
        $rui_lh_gyoumu = $rui_l_gyoumu - $rui_s_gyoumu_sagaku - $rui_b_gyoumu_sagaku;
        $rui_lh_gyoumu_sagaku = $rui_lh_gyoumu;
        $rui_l_gyoumu         = $rui_l_gyoumu - $rui_s_gyoumu_sagaku;     // 試験修理業務委託収入をリニアの業務委託収入よりマイナス
        $rui_lh_gyoumu = number_format(($rui_lh_gyoumu / $tani), $keta);
        $rui_l_gyoumu = number_format(($rui_l_gyoumu / $tani), $keta);
    }
}
/********** 営業外収益の仕入割引 **********/
    ///// 当月
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修仕入割引再計算'", $yyyymm);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修仕入割引'", $yyyymm);
}
if (getUniResult($query, $s_swari) < 1) {
    $s_swari        = 0;                        // 検索失敗
    $s_swari_sagaku = 0;
} else {
    $s_swari_sagaku = $s_swari;
    $s_swari        = number_format(($s_swari / $tani), $keta);
}
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='機工仕入割引再計算'", $yyyymm);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='機工仕入割引'", $yyyymm);
}
if (getUniResult($query, $b_swari) < 1) {
    $b_swari        = 0;                        // 検索失敗
    $b_swari_sagaku = 0;
} else {
    $b_swari_sagaku = $b_swari;
    $b_swari        = number_format(($b_swari / $tani), $keta);
}
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア仕入割引再計算'", $yyyymm);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア仕入割引'", $yyyymm);
}
if (getUniResult($query, $l_swari) < 1) {
    $l_swari = 0 - $s_swari_sagaku;     // 検索失敗
    $lh_swari = 0;     // 検索失敗
    $lh_swari_sagaku = 0;     // 検索失敗
} else {
    if ($yyyymm >= 201001) {
        $l_swari = $l_swari + $s_swari_sagaku;
    }
    $lh_swari = $l_swari - $s_swari_sagaku - $b_swari_sagaku;
    $lh_swari_sagaku = $lh_swari;
    $l_swari         = $l_swari - $s_swari_sagaku;     // 試験修理仕入割引をリニアの仕入割引よりマイナス
    $lh_swari = number_format(($lh_swari / $tani), $keta);
    $l_swari = number_format(($l_swari / $tani), $keta);
}
    ///// 前月
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修仕入割引再計算'", $p1_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修仕入割引'", $p1_ym);
}
if (getUniResult($query, $p1_s_swari) < 1) {
    $p1_s_swari        = 0;                        // 検索失敗
    $p1_s_swari_sagaku = 0;
} else {
    $p1_s_swari_sagaku = $p1_s_swari;
    $p1_s_swari        = number_format(($p1_s_swari / $tani), $keta);
}
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='機工仕入割引再計算'", $p1_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='機工仕入割引'", $p1_ym);
}
if (getUniResult($query, $p1_b_swari) < 1) {
    $p1_b_swari        = 0;                        // 検索失敗
    $p1_b_swari_sagaku = 0;
} else {
    $p1_b_swari_sagaku = $p1_b_swari;
    $p1_b_swari        = number_format(($p1_b_swari / $tani), $keta);
}
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア仕入割引再計算'", $p1_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア仕入割引'", $p1_ym);
}
if (getUniResult($query, $p1_l_swari) < 1) {
    $p1_l_swari = 0 - $p1_s_swari_sagaku;     // 検索失敗
    $p1_lh_swari = 0;     // 検索失敗
    $p1_lh_swari_sagaku = 0;     // 検索失敗
} else {
    if ($p1_ym >= 201001) {
        $p1_l_swari = $p1_l_swari + $p1_s_swari_sagaku;
    }
    $p1_lh_swari = $p1_l_swari - $p1_s_swari_sagaku - $p1_b_swari_sagaku;
    $p1_lh_swari_sagaku = $p1_lh_swari;
    $p1_l_swari         = $p1_l_swari - $p1_s_swari_sagaku;     // 試験修理仕入割引をリニアの仕入割引よりマイナス
    $p1_lh_swari = number_format(($p1_lh_swari / $tani), $keta);
    $p1_l_swari = number_format(($p1_l_swari / $tani), $keta);
}
    ///// 前前月
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修仕入割引再計算'", $p2_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修仕入割引'", $p2_ym);
}
if (getUniResult($query, $p2_s_swari) < 1) {
    $p2_s_swari        = 0;                        // 検索失敗
    $p2_s_swari_sagaku = 0;
} else {
    $p2_s_swari_sagaku = $p2_s_swari;
    $p2_s_swari        = number_format(($p2_s_swari / $tani), $keta);
}
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='機工仕入割引再計算'", $p2_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='機工仕入割引'", $p2_ym);
}
if (getUniResult($query, $p2_b_swari) < 1) {
    $p2_b_swari        = 0;                        // 検索失敗
    $p2_b_swari_sagaku = 0;
} else {
    $p2_b_swari_sagaku = $p2_b_swari;
    $p2_b_swari        = number_format(($p2_b_swari / $tani), $keta);
}
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア仕入割引再計算'", $p2_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア仕入割引'", $p2_ym);
}
if (getUniResult($query, $p2_l_swari) < 1) {
    $p2_l_swari = 0 - $p2_s_swari_sagaku;     // 検索失敗
    $p2_lh_swari = 0;     // 検索失敗
    $p2_lh_swari_sagaku = 0;     // 検索失敗
} else {
    if ($p2_ym >= 201001) {
        $p2_l_swari = $p2_l_swari + $p2_s_swari_sagaku;
    }
    $p2_lh_swari = $p2_l_swari - $p2_s_swari_sagaku - $p2_b_swari_sagaku;
    $p2_lh_swari_sagaku = $p2_lh_swari;
    $p2_l_swari         = $p2_l_swari - $p2_s_swari_sagaku;     // 試験修理仕入割引をリニアの仕入割引よりマイナス
    $p2_lh_swari = number_format(($p2_lh_swari / $tani), $keta);
    $p2_l_swari = number_format(($p2_l_swari / $tani), $keta);
}
    ///// 今期累計
if ($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='試修仕入割引再計算'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_s_swari) < 1) {
        $rui_s_swari = 0;                           // 検索失敗
    } else {
        $rui_s_swari_sagaku = $rui_s_swari;
        $rui_s_swari = number_format(($rui_s_swari / $tani), $keta);
    }
} elseif ($yyyymm >= 201001 && $yyyymm <= 201003) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=200904 and pl_bs_ym<=200912 and note='試修仕入割引'");
    if (getUniResult($query, $rui_s_swari_a) < 1) {
        $rui_s_swari_a = 0;                          // 検索失敗
    }
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=201001 and pl_bs_ym<=%d and note='試修仕入割引再計算'", $yyyymm);
    if (getUniResult($query, $rui_s_swari_b) < 1) {
        $rui_s_swari_b = 0;                          // 検索失敗
    }
    $rui_s_swari = $rui_s_swari_a + $rui_s_swari_b;
    $rui_s_swari_sagaku = $rui_s_swari;
    $rui_s_swari = number_format(($rui_s_swari / $tani), $keta);
} else {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='試修仕入割引'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_s_swari) < 1) {
        $rui_s_swari        = 0;                    // 検索失敗
        $rui_s_swari_sagaku = 0;
    } else {
        $rui_s_swari_sagaku = $rui_s_swari;
        $rui_s_swari = number_format(($rui_s_swari / $tani), $keta);
    }
}
if ($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='機工仕入割引再計算'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_b_swari) < 1) {
        $rui_b_swari = 0;    // 検索失敗
        $rui_b_swari_sagaku = 0;
    } else {
        $rui_b_swari_sagaku = $rui_b_swari;
        $rui_b_swari = number_format(($rui_b_swari / $tani), $keta);
    }
} elseif ($yyyymm >= 201001 && $yyyymm <= 201003) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=200904 and pl_bs_ym<=200912 and note='機工仕入割引'");
    if (getUniResult($query, $rui_b_swari_a) < 1) {
        $rui_b_swari_a = 0;                          // 検索失敗
    }
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=201001 and pl_bs_ym<=%d and note='機工仕入割引再計算'", $yyyymm);
    if (getUniResult($query, $rui_b_swari_b) < 1) {
        $rui_b_swari_b = 0;                          // 検索失敗
    }
    $rui_b_swari = $rui_b_swari_a + $rui_b_swari_b;
    $rui_b_swari_sagaku = $rui_b_swari;
    $rui_b_swari = number_format(($rui_b_swari / $tani), $keta);
} else {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='機工仕入割引'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_b_swari) < 1) {
        $rui_b_swari = 0;    // 検索失敗
        $rui_b_swari_sagaku = 0;
    } else {
        $rui_b_swari_sagaku = $rui_b_swari;
        $rui_b_swari = number_format(($rui_b_swari / $tani), $keta);
    }
}
if ($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='リニア仕入割引再計算'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_l_swari) < 1) {
        $rui_l_swari = 0 - $rui_s_swari_sagaku;   // 検索失敗
        $rui_lh_swari = 0;     // 検索失敗
        $rui_lh_swari_sagaku = 0;     // 検索失敗
    } else {
        $rui_l_swari = $rui_l_swari + $rui_s_swari_sagaku;
        $rui_lh_swari = $rui_l_swari - $rui_s_swari_sagaku - $rui_b_swari_sagaku;
        $rui_lh_swari_sagaku = $rui_lh_swari;
        $rui_l_swari         = $rui_l_swari - $rui_s_swari_sagaku;     // 試験修理仕入割引をリニアの仕入割引よりマイナス
        $rui_lh_swari = number_format(($rui_lh_swari / $tani), $keta);
        $rui_l_swari = number_format(($rui_l_swari / $tani), $keta);
    }
} elseif ($yyyymm >= 201001 && $yyyymm <= 201003) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=200904 and pl_bs_ym<=200912 and note='リニア仕入割引'");
    if (getUniResult($query, $rui_l_swari_a) < 1) {
        $rui_l_swari_a = 0;                          // 検索失敗
    }
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=201001 and pl_bs_ym<=%d and note='リニア仕入割引再計算'", $yyyymm);
    if (getUniResult($query, $rui_l_swari_b) < 1) {
        $rui_l_swari_b = 0;                          // 検索失敗
    }
    $rui_l_swari = $rui_l_swari_a + $rui_l_swari_b;
    $rui_l_swari = $rui_l_swari - $rui_s_swari_a;
    $rui_l_swari = $rui_l_swari + $rui_s_swari_sagaku;
    $rui_lh_swari = $rui_l_swari - $rui_s_swari_sagaku - $rui_b_swari_sagaku;
    $rui_lh_swari_sagaku = $rui_lh_swari;
    $rui_l_swari         = $rui_l_swari - $rui_s_swari_sagaku;     // 試験修理仕入割引をリニアの仕入割引よりマイナス
    $rui_lh_swari = number_format(($rui_lh_swari / $tani), $keta);
    $rui_l_swari = number_format(($rui_l_swari / $tani), $keta);
} else {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='リニア仕入割引'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_l_swari) < 1) {
        $rui_l_swari = 0 - $rui_s_swari_sagaku;   // 検索失敗
        $rui_lh_swari = 0;     // 検索失敗
        $rui_lh_swari_sagaku = 0;     // 検索失敗
    } else {
        //$rui_l_swari = $rui_l_swari + $rui_s_swari_sagaku;
        $rui_lh_swari = $rui_l_swari - $rui_s_swari_sagaku - $rui_b_swari_sagaku;
        $rui_lh_swari_sagaku = $rui_lh_swari;
        $rui_l_swari         = $rui_l_swari - $rui_s_swari_sagaku;     // 試験修理仕入割引をリニアの仕入割引よりマイナス
        $rui_lh_swari = number_format(($rui_lh_swari / $tani), $keta);
        $rui_l_swari = number_format(($rui_l_swari / $tani), $keta);
    }
}
/********** 営業外収益のその他 **********/
    ///// 当月
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修営業外収益その他再計算'", $yyyymm);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修営業外収益その他'", $yyyymm);
}
if (getUniResult($query, $s_pother) < 1) {
    $s_pother        = 0;                       // 検索失敗
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
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='機工営業外収益その他再計算'", $yyyymm);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='機工営業外収益その他'", $yyyymm);
}
if (getUniResult($query, $b_pother) < 1) {
    $b_pother = 0;    // 検索失敗
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
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア営業外収益その他再計算'", $yyyymm);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア営業外収益その他'", $yyyymm);
}
if (getUniResult($query, $l_pother) < 1) {
    $l_pother = 0 - $s_pother_sagaku;     // 検索失敗
    $lh_pother = 0;     // 検索失敗
    $lh_pother_sagaku = 0;     // 検索失敗
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
    $l_pother         = $l_pother - $s_pother_sagaku;     // 試験修理営業外収益その他をリニアの営業外収益その他よりマイナス
    $lh_pother = number_format(($lh_pother / $tani), $keta);
    $l_pother = number_format(($l_pother / $tani), $keta);
}
    ///// 前月
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修営業外収益その他再計算'", $p1_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修営業外収益その他'", $p1_ym);
}
if (getUniResult($query, $p1_s_pother) < 1) {
    $p1_s_pother        = 0;                       // 検索失敗
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
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='機工営業外収益その他再計算'", $p1_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='機工営業外収益その他'", $p1_ym);
}
if (getUniResult($query, $p1_b_pother) < 1) {
    $p1_b_pother = 0;    // 検索失敗
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
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア営業外収益その他再計算'", $p1_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア営業外収益その他'", $p1_ym);
}
if (getUniResult($query, $p1_l_pother) < 1) {
    $p1_l_pother = 0 - $p1_s_pother_sagaku;     // 検索失敗
    $p1_lh_pother = 0;     // 検索失敗
    $p1_lh_pother_sagaku = 0;     // 検索失敗
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
    $p1_l_pother         = $p1_l_pother - $p1_s_pother_sagaku;     // 試験修理営業外収益その他をリニアの営業外収益その他よりマイナス
    $p1_lh_pother = number_format(($p1_lh_pother / $tani), $keta);
    $p1_l_pother = number_format(($p1_l_pother / $tani), $keta);
}
    ///// 前前月
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修営業外収益その他再計算'", $p2_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修営業外収益その他'", $p2_ym);
}
if (getUniResult($query, $p2_s_pother) < 1) {
    $p2_s_pother        = 0;                       // 検索失敗
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
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='機工営業外収益その他再計算'", $p2_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='機工営業外収益その他'", $p2_ym);
}
if (getUniResult($query, $p2_b_pother) < 1) {
    $p2_b_pother = 0;    // 検索失敗
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
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア営業外収益その他再計算'", $p2_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア営業外収益その他'", $p2_ym);
}
if (getUniResult($query, $p2_l_pother) < 1) {
    $p2_l_pother = 0 - $p2_s_pother_sagaku;     // 検索失敗
    $p2_lh_pother = 0;     // 検索失敗
    $p2_lh_pother_sagaku = 0;     // 検索失敗
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
    $p2_l_pother         = $p2_l_pother - $p2_s_pother_sagaku;     // 試験修理営業外収益その他をリニアの営業外収益その他よりマイナス
    $p2_lh_pother = number_format(($p2_lh_pother / $tani), $keta);
    $p2_l_pother = number_format(($p2_l_pother / $tani), $keta);
}
    ///// 今期累計
if ($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='試修営業外収益その他再計算'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_s_pother) < 1) {
        $rui_s_pother = 0;                          // 検索失敗
    } else {
        $rui_s_pother_sagaku = $rui_s_pother;
        $rui_s_pother = number_format(($rui_s_pother / $tani), $keta);
    }
} elseif ($yyyymm >= 201001 && $yyyymm <= 201003) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=200904 and pl_bs_ym<=200912 and note='試修営業外収益その他'");
    if (getUniResult($query, $rui_s_pother_a) < 1) {
        $rui_s_pother_a = 0;                          // 検索失敗
    }
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=201001 and pl_bs_ym<=%d and note='試修営業外収益その他再計算'", $yyyymm);
    if (getUniResult($query, $rui_s_pother_b) < 1) {
        $rui_s_pother_b = 0;                          // 検索失敗
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
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='試修営業外収益その他'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_s_pother) < 1) {
        $rui_s_pother        = 0;                   // 検索失敗
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
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='機工営業外収益その他再計算'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_b_pother) < 1) {
        $rui_b_pother = 0;    // 検索失敗
        $rui_b_pother_sagaku = 0;
    } else {
        $rui_b_pother_sagaku = $rui_b_pother;
        $rui_b_pother = number_format(($rui_b_pother / $tani), $keta);
    }
} elseif ($yyyymm >= 201001 && $yyyymm <= 201003) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=200904 and pl_bs_ym<=200912 and note='機工営業外収益その他'");
    if (getUniResult($query, $rui_b_pother_a) < 1) {
        $rui_b_pother_a = 0;                          // 検索失敗
    }
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=201001 and pl_bs_ym<=%d and note='機工営業外収益その他再計算'", $yyyymm);
    if (getUniResult($query, $rui_b_pother_b) < 1) {
        $rui_b_pother_b = 0;                          // 検索失敗
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
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='機工営業外収益その他'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_b_pother) < 1) {
        $rui_b_pother = 0;    // 検索失敗
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
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='リニア営業外収益その他再計算'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_l_pother) < 1) {
        $rui_l_pother = 0 - $rui_s_pother_sagaku;   // 検索失敗
        $rui_lh_pother = 0;     // 検索失敗
        $rui_lh_pother_sagaku = 0;     // 検索失敗
    } else {
        $rui_l_pother = $rui_l_pother + $rui_s_pother_sagaku;
        $rui_lh_pother = $rui_l_pother - $rui_s_pother_sagaku - $rui_b_pother_sagaku;
        $rui_lh_pother_sagaku = $rui_lh_pother;
        $rui_l_pother         = $rui_l_pother - $rui_s_pother_sagaku;     // 試験修理営業外収益その他をリニアの営業外収益その他よりマイナス
        $rui_lh_pother = number_format(($rui_lh_pother / $tani), $keta);
        $rui_l_pother = number_format(($rui_l_pother / $tani), $keta);
    }
} elseif ($yyyymm >= 201001 && $yyyymm <= 201003) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=200904 and pl_bs_ym<=200912 and note='リニア営業外収益その他'");
    if (getUniResult($query, $rui_l_pother_a) < 1) {
        $rui_l_pother_a = 0;                          // 検索失敗
    }
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=201001 and pl_bs_ym<=%d and note='リニア営業外収益その他再計算'", $yyyymm);
    if (getUniResult($query, $rui_l_pother_b) < 1) {
        $rui_l_pother_b = 0;                          // 検索失敗
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
    $rui_l_pother         = $rui_l_pother - $rui_s_pother_sagaku;     // 試験修理営業外収益その他をリニアの営業外収益その他よりマイナス
    $rui_lh_pother = number_format(($rui_lh_pother / $tani), $keta);
    $rui_l_pother = number_format(($rui_l_pother / $tani), $keta);
} else {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='リニア営業外収益その他'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_l_pother) < 1) {
        $rui_l_pother = 0 - $rui_s_pother_sagaku;   // 検索失敗
        $rui_lh_pother = 0;     // 検索失敗
        $rui_lh_pother_sagaku = 0;     // 検索失敗
    } else {
        if ($yyyymm >= 200912 && $yyyymm <= 201003) {
            $rui_l_pother = $rui_l_pother + 76191;
        }
        //$rui_l_pother = $rui_l_pother + $rui_s_pother_sagaku;
        $rui_lh_pother = $rui_l_pother - $rui_s_pother_sagaku - $rui_b_pother_sagaku;
        $rui_lh_pother_sagaku = $rui_lh_pother;
        $rui_l_pother         = $rui_l_pother - $rui_s_pother_sagaku;     // 試験修理営業外収益その他をリニアの営業外収益その他よりマイナス
        $rui_lh_pother = number_format(($rui_lh_pother / $tani), $keta);
        $rui_l_pother = number_format(($rui_l_pother / $tani), $keta);
    }
}
/********** 営業外収益の合計 **********/
    ///// 試験・修理
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
    ///// 機工
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

    ///// 当月
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア営業外収益計再計算'", $yyyymm);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア営業外収益計'", $yyyymm);
}
if (getUniResult($query, $l_nonope_profit_sum) < 1) {
    $l_nonope_profit_sum = 0 - $s_nonope_profit_sum_sagaku;     // 検索失敗
    $lh_nonope_profit_sum = 0;     // 検索失敗
    $lh_nonope_profit_sum_sagaku = 0;     // 検索失敗
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
    $l_nonope_profit_sum         = $l_nonope_profit_sum - $s_nonope_profit_sum_sagaku;     // 試験修理営業外収益計をリニアの営業外収益計よりマイナス
    $lh_nonope_profit_sum = number_format(($lh_nonope_profit_sum / $tani), $keta);
    $l_nonope_profit_sum = number_format(($l_nonope_profit_sum / $tani), $keta);
}
    ///// 前月
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア営業外収益計再計算'", $p1_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア営業外収益計'", $p1_ym);
}
if (getUniResult($query, $p1_l_nonope_profit_sum) < 1) {
    $p1_l_nonope_profit_sum = 0 - $p1_s_nonope_profit_sum_sagaku;     // 検索失敗
    $p1_lh_nonope_profit_sum = 0;     // 検索失敗
    $p1_lh_nonope_profit_sum_sagaku = 0;     // 検索失敗
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
    $p1_l_nonope_profit_sum         = $p1_l_nonope_profit_sum - $p1_s_nonope_profit_sum_sagaku;     // 試験修理営業外収益計をリニアの営業外収益計よりマイナス
    $p1_lh_nonope_profit_sum = number_format(($p1_lh_nonope_profit_sum / $tani), $keta);
    $p1_l_nonope_profit_sum = number_format(($p1_l_nonope_profit_sum / $tani), $keta);
}
    ///// 前前月
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア営業外収益計再計算'", $p2_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア営業外収益計'", $p2_ym);
}
if (getUniResult($query, $p2_l_nonope_profit_sum) < 1) {
    $p2_l_nonope_profit_sum = 0 - $p2_s_nonope_profit_sum_sagaku;     // 検索失敗
    $p2_lh_nonope_profit_sum = 0;     // 検索失敗
    $p2_lh_nonope_profit_sum_sagaku = 0;     // 検索失敗
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
    $p2_l_nonope_profit_sum         = $p2_l_nonope_profit_sum - $p2_s_nonope_profit_sum_sagaku;     // 試験修理営業外収益計をリニアの営業外収益計よりマイナス
    $p2_lh_nonope_profit_sum = number_format(($p2_lh_nonope_profit_sum / $tani), $keta);
    $p2_l_nonope_profit_sum = number_format(($p2_l_nonope_profit_sum / $tani), $keta);
}
    ///// 今期累計
if ($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='リニア営業外収益計再計算'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_l_nonope_profit_sum) < 1) {
        $rui_l_nonope_profit_sum = 0 - $rui_s_nonope_profit_sum_sagaku;   // 検索失敗
        $rui_lh_nonope_profit_sum = 0;     // 検索失敗
        $rui_lh_nonope_profit_sum_sagaku = 0;     // 検索失敗
    } else {
        //$rui_l_nonope_profit_sum      = $rui_l_nonope_profit_sum - $rui_s_nonope_profit_sum;
        $rui_lh_nonope_profit_sum = $rui_l_nonope_profit_sum - $rui_b_nonope_profit_sum_sagaku;// - $rui_s_nonope_profit_sum_sagaku - $rui_b_nonope_profit_sum_sagaku;
        $rui_lh_nonope_profit_sum_sagaku = $rui_lh_nonope_profit_sum;
        //$rui_l_nonope_profit_sum         = $rui_l_nonope_profit_sum - $rui_s_nonope_profit_sum_sagaku;     // 試験修理営業外収益計をリニアの営業外収益計よりマイナス
        $rui_l_nonope_profit_sum_temp = $rui_l_nonope_profit_sum;         // 経常利益計算用
        $rui_lh_nonope_profit_sum = number_format(($rui_lh_nonope_profit_sum / $tani), $keta);
        $rui_l_nonope_profit_sum      = number_format(($rui_l_nonope_profit_sum / $tani), $keta);
    }
} elseif ($yyyymm >= 201001 && $yyyymm <= 201003) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=200904 and pl_bs_ym<=200912 and note='リニア営業外収益計'");
    if (getUniResult($query, $rui_l_nonope_profit_sum_a) < 1) {
        $rui_l_nonope_profit_sum_a = 0;                          // 検索失敗
    }
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=201001 and pl_bs_ym<=%d and note='リニア営業外収益計再計算'", $yyyymm);
    if (getUniResult($query, $rui_l_nonope_profit_sum_b) < 1) {
        $rui_l_nonope_profit_sum_b = 0;                          // 検索失敗
    }
    $rui_l_nonope_profit_sum      = $rui_l_nonope_profit_sum_a + $rui_l_nonope_profit_sum_b;
    $rui_l_nonope_profit_sum      = $rui_l_nonope_profit_sum + $rui_s_nonope_profit_sum_sagaku;
    $rui_l_nonope_profit_sum      = $rui_l_nonope_profit_sum - $rui_s_gyoumu_a - $rui_s_swari_a - $rui_s_pother_a;
    $rui_lh_nonope_profit_sum = $rui_l_nonope_profit_sum - $rui_s_nonope_profit_sum_sagaku - $rui_b_nonope_profit_sum_sagaku;
    $rui_lh_nonope_profit_sum_sagaku = $rui_lh_nonope_profit_sum;
    $rui_l_nonope_profit_sum         = $rui_l_nonope_profit_sum - $rui_s_nonope_profit_sum_sagaku;     // 試験修理営業外収益計をリニアの営業外収益計よりマイナス
    $rui_l_nonope_profit_sum_temp = $rui_l_nonope_profit_sum;         // 経常利益計算用
    $rui_lh_nonope_profit_sum = number_format(($rui_lh_nonope_profit_sum / $tani), $keta);
    $rui_l_nonope_profit_sum      = number_format(($rui_l_nonope_profit_sum / $tani), $keta);
} else {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='リニア営業外収益計'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_l_nonope_profit_sum) < 1) {
        $rui_l_nonope_profit_sum = 0 - $rui_s_nonope_profit_sum_sagaku;   // 検索失敗
        $rui_lh_nonope_profit_sum = 0;     // 検索失敗
        $rui_lh_nonope_profit_sum_sagaku = 0;     // 検索失敗
    } else {
        //$rui_l_nonope_profit_sum = $rui_l_nonope_profit_sum - $rui_s_nonope_profit_sum_sagaku;
        $rui_lh_nonope_profit_sum = $rui_l_nonope_profit_sum - $rui_s_nonope_profit_sum_sagaku - $rui_b_nonope_profit_sum_sagaku;
        $rui_lh_nonope_profit_sum_sagaku = $rui_lh_nonope_profit_sum;
        $rui_l_nonope_profit_sum         = $rui_l_nonope_profit_sum - $rui_s_nonope_profit_sum_sagaku;     // 試験修理営業外収益計をリニアの営業外収益計よりマイナス
        $rui_lh_nonope_profit_sum = number_format(($rui_lh_nonope_profit_sum / $tani), $keta);
        $rui_l_nonope_profit_sum = number_format(($rui_l_nonope_profit_sum / $tani), $keta);
    }
}
/********** 営業外費用の支払利息 **********/
    ///// 当月
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修支払利息再計算'", $yyyymm);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修支払利息'", $yyyymm);
}
if (getUniResult($query, $s_srisoku) < 1) {
    $s_srisoku        = 0;                      // 検索失敗
    $s_srisoku_sagaku = 0;
} else {
    $s_srisoku_sagaku = $s_srisoku;
    $s_srisoku        = number_format(($s_srisoku / $tani), $keta);
}
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='機工支払利息再計算'", $yyyymm);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='機工支払利息'", $yyyymm);
}
if (getUniResult($query, $b_srisoku) < 1) {
    $b_srisoku        = 0;                      // 検索失敗
    $b_srisoku_sagaku = 0;
} else {
    $b_srisoku_sagaku = $b_srisoku;
    $b_srisoku        = number_format(($b_srisoku / $tani), $keta);
}
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア支払利息再計算'", $yyyymm);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア支払利息'", $yyyymm);
}
if (getUniResult($query, $l_srisoku) < 1) {
    $l_srisoku = 0 - $s_srisoku_sagaku;     // 検索失敗
    $lh_srisoku = 0;     // 検索失敗
    $lh_srisoku_sagaku = 0;     // 検索失敗
} else {
    if ($yyyymm >= 201001) {
        $l_srisoku = $l_srisoku + $s_srisoku_sagaku;
    }
    $lh_srisoku = $l_srisoku - $s_srisoku_sagaku - $b_srisoku_sagaku;
    $lh_srisoku_sagaku = $lh_srisoku;
    $l_srisoku         = $l_srisoku - $s_srisoku_sagaku;     // 試験修理支払利息をリニアの支払利息よりマイナス
    $lh_srisoku = number_format(($lh_srisoku / $tani), $keta);
    $l_srisoku = number_format(($l_srisoku / $tani), $keta);
}
    ///// 前月
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修支払利息再計算'", $p1_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修支払利息'", $p1_ym);
}
if (getUniResult($query, $p1_s_srisoku) < 1) {
    $p1_s_srisoku        = 0;                      // 検索失敗
    $p1_s_srisoku_sagaku = 0;
} else {
    $p1_s_srisoku_sagaku = $p1_s_srisoku;
    $p1_s_srisoku        = number_format(($p1_s_srisoku / $tani), $keta);
}
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='機工支払利息再計算'", $p1_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='機工支払利息'", $p1_ym);
}
if (getUniResult($query, $p1_b_srisoku) < 1) {
    $p1_b_srisoku        = 0;                      // 検索失敗
    $p1_b_srisoku_sagaku = 0;
} else {
    $p1_b_srisoku_sagaku = $p1_b_srisoku;
    $p1_b_srisoku        = number_format(($p1_b_srisoku / $tani), $keta);
}
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア支払利息再計算'", $p1_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア支払利息'", $p1_ym);
}
if (getUniResult($query, $p1_l_srisoku) < 1) {
    $p1_l_srisoku = 0 - $p1_s_srisoku_sagaku;     // 検索失敗
    $p1_lh_srisoku = 0;     // 検索失敗
    $p1_lh_srisoku_sagaku = 0;     // 検索失敗
} else {
    if ($p1_ym >= 201001) {
        $p1_l_srisoku = $p1_l_srisoku + $p1_s_srisoku_sagaku;
    }
    $p1_lh_srisoku = $p1_l_srisoku - $p1_s_srisoku_sagaku - $p1_b_srisoku_sagaku;
    $p1_lh_srisoku_sagaku = $p1_lh_srisoku;
    $p1_l_srisoku         = $p1_l_srisoku - $p1_s_srisoku_sagaku;     // 試験修理支払利息をリニアの支払利息よりマイナス
    $p1_lh_srisoku = number_format(($p1_lh_srisoku / $tani), $keta);
    $p1_l_srisoku = number_format(($p1_l_srisoku / $tani), $keta);
}
    ///// 前前月
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修支払利息再計算'", $p2_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修支払利息'", $p2_ym);
}
if (getUniResult($query, $p2_s_srisoku) < 1) {
    $p2_s_srisoku        = 0;                      // 検索失敗
    $p2_s_srisoku_sagaku = 0;
} else {
    $p2_s_srisoku_sagaku = $p2_s_srisoku;
    $p2_s_srisoku        = number_format(($p2_s_srisoku / $tani), $keta);
}
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='機工支払利息再計算'", $p2_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='機工支払利息'", $p2_ym);
}
if (getUniResult($query, $p2_b_srisoku) < 1) {
    $p2_b_srisoku        = 0;                      // 検索失敗
    $p2_b_srisoku_sagaku = 0;
} else {
    $p2_b_srisoku_sagaku = $p2_b_srisoku;
    $p2_b_srisoku        = number_format(($p2_b_srisoku / $tani), $keta);
}
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア支払利息再計算'", $p2_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア支払利息'", $p2_ym);
}
if (getUniResult($query, $p2_l_srisoku) < 1) {
    $p2_l_srisoku = 0 - $p2_s_srisoku_sagaku;     // 検索失敗
    $p2_lh_srisoku = 0;     // 検索失敗
    $p2_lh_srisoku_sagaku = 0;     // 検索失敗
} else {
    if ($p2_ym >= 201001) {
        $p2_l_srisoku = $p2_l_srisoku + $p2_s_srisoku_sagaku;
    }
    $p2_lh_srisoku = $p2_l_srisoku - $p2_s_srisoku_sagaku - $p2_b_srisoku_sagaku;
    $p2_lh_srisoku_sagaku = $p2_lh_srisoku;
    $p2_l_srisoku         = $p2_l_srisoku - $p2_s_srisoku_sagaku;     // 試験修理支払利息をリニアの支払利息よりマイナス
    $p2_lh_srisoku = number_format(($p2_lh_srisoku / $tani), $keta);
    $p2_l_srisoku = number_format(($p2_l_srisoku / $tani), $keta);
}
    ///// 今期累計
if ($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='試修支払利息再計算'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_s_srisoku) < 1) {
        $rui_s_srisoku = 0;                           // 検索失敗
        $rui_s_srisoku_sagaku = 0;
    } else {
        $rui_s_srisoku_sagaku = $rui_s_srisoku;
        $rui_s_srisoku = number_format(($rui_s_srisoku / $tani), $keta);
    }
} elseif ($yyyymm >= 201001 && $yyyymm <= 201003) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=200904 and pl_bs_ym<=200912 and note='試修支払利息'");
    if (getUniResult($query, $rui_s_srisoku_a) < 1) {
        $rui_s_srisoku_a = 0;                          // 検索失敗
    }
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=201001 and pl_bs_ym<=%d and note='試修支払利息再計算'", $yyyymm);
    if (getUniResult($query, $rui_s_srisoku_b) < 1) {
        $rui_s_srisoku_b = 0;                          // 検索失敗
    }
    $rui_s_srisoku = $rui_s_srisoku_a + $rui_s_srisoku_b;
    $rui_s_srisoku_sagaku = $rui_s_srisoku;
    $rui_s_srisoku = number_format(($rui_s_srisoku / $tani), $keta);
} else {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='試修支払利息'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_s_srisoku) < 1) {
        $rui_s_srisoku        = 0;                  // 検索失敗
        $rui_s_srisoku_sagaku = 0;
    } else {
        $rui_s_srisoku_sagaku = $rui_s_srisoku;
        $rui_s_srisoku = number_format(($rui_s_srisoku / $tani), $keta);
    }
}
if ($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='機工支払利息再計算'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_b_srisoku) < 1) {
        $rui_b_srisoku = 0;    // 検索失敗
        $rui_b_srisoku_sagaku = 0;
    } else {
        $rui_b_srisoku_sagaku = $rui_b_srisoku;
        $rui_b_srisoku = number_format(($rui_b_srisoku / $tani), $keta);
    }
} elseif ($yyyymm >= 201001 && $yyyymm <= 201003) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=200904 and pl_bs_ym<=200912 and note='機工支払利息'");
    if (getUniResult($query, $rui_b_srisoku_a) < 1) {
        $rui_b_srisoku_a = 0;                          // 検索失敗
    }
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=201001 and pl_bs_ym<=%d and note='機工支払利息再計算'", $yyyymm);
    if (getUniResult($query, $rui_b_srisoku_b) < 1) {
        $rui_b_srisoku_b = 0;                          // 検索失敗
    }
    $rui_b_srisoku = $rui_b_srisoku_a + $rui_b_srisoku_b;
    $rui_b_srisoku_sagaku = $rui_b_srisoku;
    $rui_b_srisoku = number_format(($rui_b_srisoku / $tani), $keta);
} else {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='機工支払利息'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_b_srisoku) < 1) {
        $rui_b_srisoku = 0;    // 検索失敗
        $rui_b_srisoku_sagaku = 0;
    } else {
        $rui_b_srisoku_sagaku = $rui_b_srisoku;
        $rui_b_srisoku = number_format(($rui_b_srisoku / $tani), $keta);
    }
}
if ($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='リニア支払利息再計算'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_l_srisoku) < 1) {
        $rui_l_srisoku = 0 - $rui_s_srisoku_sagaku;   // 検索失敗
        $rui_lh_srisoku = 0;     // 検索失敗
        $rui_lh_srisoku_sagaku = 0;     // 検索失敗
    } else {
        $rui_l_srisoku = $rui_l_srisoku + $rui_s_srisoku_sagaku;
        $rui_lh_srisoku = $rui_l_srisoku - $rui_s_srisoku_sagaku - $rui_b_srisoku_sagaku;
        $rui_lh_srisoku_sagaku = $rui_lh_srisoku;
        $rui_l_srisoku         = $rui_l_srisoku - $rui_s_srisoku_sagaku;     // 試験修理支払利息をリニアの支払利息よりマイナス
        $rui_lh_srisoku = number_format(($rui_lh_srisoku / $tani), $keta);
        $rui_l_srisoku = number_format(($rui_l_srisoku / $tani), $keta);
    }
} elseif ($yyyymm >= 201001 && $yyyymm <= 201003) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=200904 and pl_bs_ym<=200912 and note='リニア支払利息'");
    if (getUniResult($query, $rui_l_srisoku_a) < 1) {
        $rui_l_srisoku_a = 0;                          // 検索失敗
    }
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=201001 and pl_bs_ym<=%d and note='リニア支払利息再計算'", $yyyymm);
    if (getUniResult($query, $rui_l_srisoku_b) < 1) {
        $rui_l_srisoku_b = 0;                          // 検索失敗
    }
    $rui_l_srisoku = $rui_l_srisoku_a + $rui_l_srisoku_b;
    $rui_l_srisoku = $rui_l_srisoku - $rui_s_srisoku_a;
    $rui_l_srisoku = $rui_l_srisoku + $rui_s_srisoku_sagaku;
    $rui_lh_srisoku = $rui_l_srisoku - $rui_s_srisoku_sagaku - $rui_b_srisoku_sagaku;
    $rui_lh_srisoku_sagaku = $rui_lh_srisoku;
    $rui_l_srisoku         = $rui_l_srisoku - $rui_s_srisoku_sagaku;     // 試験修理支払利息をリニアの支払利息よりマイナス
    $rui_lh_srisoku = number_format(($rui_lh_srisoku / $tani), $keta);
    $rui_l_srisoku = number_format(($rui_l_srisoku / $tani), $keta);
} else {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='リニア支払利息'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_l_srisoku) < 1) {
        $rui_l_srisoku = 0 - $rui_s_srisoku_sagaku;   // 検索失敗
        $rui_lh_srisoku = 0;     // 検索失敗
        $rui_lh_srisoku_sagaku = 0;     // 検索失敗
    } else {
        //$rui_l_srisoku = $rui_l_srisoku + $rui_s_srisoku_sagaku;
        $rui_lh_srisoku = $rui_l_srisoku - $rui_s_srisoku_sagaku - $rui_b_srisoku_sagaku;
        $rui_lh_srisoku_sagaku = $rui_lh_srisoku;
        $rui_l_srisoku         = $rui_l_srisoku - $rui_s_srisoku_sagaku;     // 試験修理支払利息をリニアの支払利息よりマイナス
        $rui_lh_srisoku = number_format(($rui_lh_srisoku / $tani), $keta);
        $rui_l_srisoku = number_format(($rui_l_srisoku / $tani), $keta);
    }
}
/********** 営業外費用のその他 **********/
    ///// 当月
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修営業外費用その他再計算'", $yyyymm);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修営業外費用その他'", $yyyymm);
}
if (getUniResult($query, $s_lother) < 1) {
    $s_lother        = 0;                       // 検索失敗
    $s_lother_sagaku = 0;
} else {
    $s_lother_sagaku = $s_lother;
    $s_lother        = number_format(($s_lother / $tani), $keta);
}
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='機工営業外費用その他再計算'", $yyyymm);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='機工営業外費用その他'", $yyyymm);
}
if (getUniResult($query, $b_lother) < 1) {
    $b_lother        = 0;                       // 検索失敗
    $b_lother_sagaku = 0;
} else {
    $b_lother_sagaku = $b_lother;
    $b_lother        = number_format(($b_lother / $tani), $keta);
}
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア営業外費用その他再計算'", $yyyymm);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア営業外費用その他'", $yyyymm);
}
if (getUniResult($query, $l_lother) < 1) {
    $l_lother = 0 - $s_lother_sagaku;     // 検索失敗
    $lh_lother = 0;     // 検索失敗
    $lh_lother_sagaku = 0;     // 検索失敗
} else {
    if ($yyyymm >= 201001) {
        $l_lother = $l_lother + $s_lother_sagaku;
    }
    $lh_lother = $l_lother - $s_lother_sagaku - $b_lother_sagaku;
    $lh_lother_sagaku = $lh_lother;
    $l_lother         = $l_lother - $s_lother_sagaku;     // 試験修理営業外費用その他をリニアの営業外費用その他よりマイナス
    $lh_lother = number_format(($lh_lother / $tani), $keta);
    $l_lother = number_format(($l_lother / $tani), $keta);
}
    ///// 前月
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修営業外費用その他再計算'", $p1_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修営業外費用その他'", $p1_ym);
}
if (getUniResult($query, $p1_s_lother) < 1) {
    $p1_s_lother        = 0;                       // 検索失敗
    $p1_s_lother_sagaku = 0;
} else {
    $p1_s_lother_sagaku = $p1_s_lother;
    $p1_s_lother        = number_format(($p1_s_lother / $tani), $keta);
}
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='機工営業外費用その他再計算'", $p1_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='機工営業外費用その他'", $p1_ym);
}
if (getUniResult($query, $p1_b_lother) < 1) {
    $p1_b_lother        = 0;                       // 検索失敗
    $p1_b_lother_sagaku = 0;
} else {
    $p1_b_lother_sagaku = $p1_b_lother;
    $p1_b_lother        = number_format(($p1_b_lother / $tani), $keta);
}
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア営業外費用その他再計算'", $p1_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア営業外費用その他'", $p1_ym);
}
if (getUniResult($query, $p1_l_lother) < 1) {
    $p1_l_lother = 0 - $p1_s_lother_sagaku;     // 検索失敗
    $p1_lh_lother = 0;     // 検索失敗
    $p1_lh_lother_sagaku = 0;     // 検索失敗
} else {
    if ($p1_ym >= 201001) {
        $p1_l_lother = $p1_l_lother + $p1_s_lother_sagaku;
    }
    $p1_lh_lother = $p1_l_lother - $p1_s_lother_sagaku - $p1_b_lother_sagaku;
    $p1_lh_lother_sagaku = $p1_lh_lother;
    $p1_l_lother         = $p1_l_lother - $p1_s_lother_sagaku;     // 試験修理営業外費用その他をリニアの営業外費用その他よりマイナス
    $p1_lh_lother = number_format(($p1_lh_lother / $tani), $keta);
    $p1_l_lother = number_format(($p1_l_lother / $tani), $keta);
}
    ///// 前前月
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修営業外費用その他再計算'", $p2_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修営業外費用その他'", $p2_ym);
}
if (getUniResult($query, $p2_s_lother) < 1) {
    $p2_s_lother        = 0;                       // 検索失敗
    $p2_s_lother_sagaku = 0;
} else {
    $p2_s_lother_sagaku = $p2_s_lother;
    $p2_s_lother        = number_format(($p2_s_lother / $tani), $keta);
}
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='機工営業外費用その他再計算'", $p2_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='機工営業外費用その他'", $p2_ym);
}
if (getUniResult($query, $p2_b_lother) < 1) {
    $p2_b_lother        = 0;                       // 検索失敗
    $p2_b_lother_sagaku = 0;
} else {
    $p2_b_lother_sagaku = $p2_b_lother;
    $p2_b_lother        = number_format(($p2_b_lother / $tani), $keta);
}
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア営業外費用その他再計算'", $p2_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア営業外費用その他'", $p2_ym);
}
if (getUniResult($query, $p2_l_lother) < 1) {
    $p2_l_lother = 0 - $p1_s_lother_sagaku;     // 検索失敗
    $p2_lh_lother = 0;     // 検索失敗
    $p2_lh_lother_sagaku = 0;     // 検索失敗
} else {
    if ($p2_ym >= 201001) {
        $p2_l_lother = $p2_l_lother + $p2_s_lother_sagaku;
    }
    $p2_lh_lother = $p2_l_lother - $p2_s_lother_sagaku - $p2_b_lother_sagaku;
    $p2_lh_lother_sagaku = $p2_lh_lother;
    $p2_l_lother         = $p2_l_lother - $p2_s_lother_sagaku;     // 試験修理営業外費用その他をリニアの営業外費用その他よりマイナス
    $p2_lh_lother = number_format(($p2_lh_lother / $tani), $keta);
    $p2_l_lother = number_format(($p2_l_lother / $tani), $keta);
}
    ///// 今期累計
if ($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='試修営業外費用その他再計算'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_s_lother) < 1) {
        $rui_s_lother = 0;                           // 検索失敗
        $rui_s_lother_sagaku = 0;
    } else {
        $rui_s_lother_sagaku = $rui_s_lother;
        $rui_s_lother = number_format(($rui_s_lother / $tani), $keta);
    }
} elseif ($yyyymm >= 201001 && $yyyymm <= 201003) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=200904 and pl_bs_ym<=200912 and note='試修営業外費用その他'");
    if (getUniResult($query, $rui_s_lother_a) < 1) {
        $rui_s_lother_a = 0;                          // 検索失敗
    }
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=201001 and pl_bs_ym<=%d and note='試修営業外費用その他再計算'", $yyyymm);
    if (getUniResult($query, $rui_s_lother_b) < 1) {
        $rui_s_lother_b = 0;                          // 検索失敗
    }
    $rui_s_lother = $rui_s_lother_a + $rui_s_lother_b;
    $rui_s_lother_sagaku = $rui_s_lother;
    $rui_s_lother = number_format(($rui_s_lother / $tani), $keta);
} else {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='試修営業外費用その他'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_s_lother) < 1) {
        $rui_s_lother        = 0;                   // 検索失敗
        $rui_s_lother_sagaku = 0;
    } else {
        $rui_s_lother_sagaku = $rui_s_lother;
        $rui_s_lother        = number_format(($rui_s_lother / $tani), $keta);
    }
}
if ($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='機工営業外費用その他再計算'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_b_lother) < 1) {
        $rui_b_lother = 0;    // 検索失敗
        $rui_b_lother_sagaku = 0;
    } else {
        $rui_b_lother_sagaku = $rui_b_lother;
        $rui_b_lother = number_format(($rui_b_lother / $tani), $keta);
    }
} elseif ($yyyymm >= 201001 && $yyyymm <= 201003) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=200904 and pl_bs_ym<=200912 and note='機工営業外費用その他'");
    if (getUniResult($query, $rui_b_lother_a) < 1) {
        $rui_b_lother_a = 0;                          // 検索失敗
    }
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=201001 and pl_bs_ym<=%d and note='機工営業外費用その他再計算'", $yyyymm);
    if (getUniResult($query, $rui_b_lother_b) < 1) {
        $rui_b_lother_b = 0;                          // 検索失敗
    }
    $rui_b_lother = $rui_b_lother_a + $rui_b_lother_b;
    $rui_b_lother_sagaku = $rui_b_lother;
    $rui_b_lother = number_format(($rui_b_lother / $tani), $keta);
} else {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='機工営業外費用その他'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_b_lother) < 1) {
        $rui_b_lother = 0;    // 検索失敗
        $rui_b_lother_sagaku = 0;
    } else {
        $rui_b_lother_sagaku = $rui_b_lother;
        $rui_b_lother = number_format(($rui_b_lother / $tani), $keta);
    }
}
if ($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='リニア営業外費用その他再計算'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_l_lother) < 1) {
        $rui_l_lother = 0 - $rui_s_lother_sagaku;   // 検索失敗
        $rui_lh_lother = 0;     // 検索失敗
        $rui_lh_lother_sagaku = 0;     // 検索失敗
    } else {
        $rui_l_lother = $rui_l_lother + $rui_s_lother_sagaku;
        $rui_lh_lother = $rui_l_lother - $rui_s_lother_sagaku - $rui_b_lother_sagaku;
        $rui_lh_lother_sagaku = $rui_lh_lother;
        $rui_l_lother         = $rui_l_lother - $rui_s_lother_sagaku;     // 試験修理営業外費用その他をリニアの営業外費用その他よりマイナス
        $rui_lh_lother = number_format(($rui_lh_lother / $tani), $keta);
        $rui_l_lother = number_format(($rui_l_lother / $tani), $keta);
    }
} elseif ($yyyymm >= 201001 && $yyyymm <= 201003) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=200904 and pl_bs_ym<=200912 and note='リニア営業外費用その他'");
    if (getUniResult($query, $rui_l_lother_a) < 1) {
        $rui_l_lother_a = 0;                          // 検索失敗
    }
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=201001 and pl_bs_ym<=%d and note='リニア営業外費用その他再計算'", $yyyymm);
    if (getUniResult($query, $rui_l_lother_b) < 1) {
        $rui_l_lother_b = 0;                          // 検索失敗
    }
    $rui_l_lother = $rui_l_lother_a + $rui_l_lother_b;
    $rui_l_lother = $rui_l_lother - $rui_s_lother_a;
    $rui_l_lother = $rui_l_lother + $rui_s_lother_sagaku;
    $rui_lh_lother = $rui_l_lother - $rui_s_lother_sagaku - $rui_b_lother_sagaku;
    $rui_lh_lother_sagaku = $rui_lh_lother;
    $rui_l_lother         = $rui_l_lother - $rui_s_lother_sagaku;     // 試験修理営業外費用その他をリニアの営業外費用その他よりマイナス
    $rui_lh_lother = number_format(($rui_lh_lother / $tani), $keta);
    $rui_l_lother = number_format(($rui_l_lother / $tani), $keta);
} else {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='リニア営業外費用その他'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_l_lother) < 1) {
        $rui_l_lother = 0 - $rui_s_lother_sagaku;   // 検索失敗
        $rui_lh_lother = 0;     // 検索失敗
        $rui_lh_lother_sagaku = 0;     // 検索失敗
    } else {
        //$rui_l_lother = $rui_l_lother + $rui_s_lother_sagaku;
        $rui_lh_lother = $rui_l_lother - $rui_s_lother_sagaku - $rui_b_lother_sagaku;
        $rui_lh_lother_sagaku = $rui_lh_lother;
        $rui_l_lother         = $rui_l_lother - $rui_s_lother_sagaku;     // 試験修理営業外費用その他をリニアの営業外費用その他よりマイナス
        $rui_lh_lother = number_format(($rui_lh_lother / $tani), $keta);
        $rui_l_lother = number_format(($rui_l_lother / $tani), $keta);
    }
}
/********** 営業外費用の合計 **********/
    ///// 試験・修理
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
    ///// 機工
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

    ///// 当月
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア営業外費用計再計算'", $yyyymm);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア営業外費用計'", $yyyymm);
}
if (getUniResult($query, $l_nonope_loss_sum) < 1) {
    $l_nonope_loss_sum = 0 - $s_nonope_loss_sum_sagaku;     // 検索失敗
    $lh_nonope_loss_sum = 0;     // 検索失敗
    $lh_nonope_loss_sum_sagaku = 0;     // 検索失敗
} else {
    if ($yyyymm >= 201001) {
        $l_nonope_loss_sum = $l_nonope_loss_sum + $s_nonope_loss_sum_sagaku;
    }
    $lh_nonope_loss_sum = $l_nonope_loss_sum - $s_nonope_loss_sum_sagaku - $b_nonope_loss_sum_sagaku;
    $lh_nonope_loss_sum_sagaku = $lh_nonope_loss_sum;
    $l_nonope_loss_sum         = $l_nonope_loss_sum - $s_nonope_loss_sum_sagaku;     // 試験修理営業外費用計をリニアの営業外費用計よりマイナス
    $lh_nonope_loss_sum = number_format(($lh_nonope_loss_sum / $tani), $keta);
    $l_nonope_loss_sum = number_format(($l_nonope_loss_sum / $tani), $keta);
}
    ///// 前月
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア営業外費用計再計算'", $p1_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア営業外費用計'", $p1_ym);
}
if (getUniResult($query, $p1_l_nonope_loss_sum) < 1) {
    $p1_l_nonope_loss_sum = 0 - $p1_s_nonope_loss_sum_sagaku;     // 検索失敗
    $p1_lh_nonope_loss_sum = 0;     // 検索失敗
    $p1_lh_nonope_loss_sum_sagaku = 0;     // 検索失敗
} else {
    if ($p1_ym >= 201001) {
        $p1_l_nonope_loss_sum = $p1_l_nonope_loss_sum + $p1_s_nonope_loss_sum_sagaku;
    }
    $p1_lh_nonope_loss_sum = $p1_l_nonope_loss_sum - $p1_s_nonope_loss_sum_sagaku - $p1_b_nonope_loss_sum_sagaku;
    $p1_lh_nonope_loss_sum_sagaku = $p1_lh_nonope_loss_sum;
    $p1_l_nonope_loss_sum         = $p1_l_nonope_loss_sum - $p1_s_nonope_loss_sum_sagaku;     // 試験修理営業外費用計をリニアの営業外費用計よりマイナス
    $p1_lh_nonope_loss_sum = number_format(($p1_lh_nonope_loss_sum / $tani), $keta);
    $p1_l_nonope_loss_sum = number_format(($p1_l_nonope_loss_sum / $tani), $keta);
}
    ///// 前前月
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア営業外費用計再計算'", $p2_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア営業外費用計'", $p2_ym);
}
if (getUniResult($query, $p2_l_nonope_loss_sum) < 1) {
    $p2_l_nonope_loss_sum = 0 - $p2_s_nonope_loss_sum_sagaku;     // 検索失敗
    $p2_lh_nonope_loss_sum = 0;     // 検索失敗
    $p2_lh_nonope_loss_sum_sagaku = 0;     // 検索失敗
} else {
    if ($p2_ym >= 201001) {
        $p2_l_nonope_loss_sum = $p2_l_nonope_loss_sum + $p2_s_nonope_loss_sum_sagaku;
    }
    $p2_lh_nonope_loss_sum = $p2_l_nonope_loss_sum - $p2_s_nonope_loss_sum_sagaku - $p2_b_nonope_loss_sum_sagaku;
    $p2_lh_nonope_loss_sum_sagaku = $p2_lh_nonope_loss_sum;
    $p2_l_nonope_loss_sum         = $p2_l_nonope_loss_sum - $p2_s_nonope_loss_sum_sagaku;     // 試験修理営業外費用計をリニアの営業外費用計よりマイナス
    $p2_lh_nonope_loss_sum = number_format(($p2_lh_nonope_loss_sum / $tani), $keta);
    $p2_l_nonope_loss_sum = number_format(($p2_l_nonope_loss_sum / $tani), $keta);
}
    ///// 今期累計
if ($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='リニア営業外費用計再計算'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_l_nonope_loss_sum) < 1) {
        $rui_l_nonope_loss_sum = 0 - $rui_s_nonope_loss_sum_sagaku;   // 検索失敗
        $rui_lh_nonope_loss_sum = 0;     // 検索失敗
        $rui_lh_nonope_loss_sum_sagaku = 0;     // 検索失敗
    } else {
        $rui_l_nonope_loss_sum = $rui_l_nonope_loss_sum + $rui_s_nonope_loss_sum_sagaku;
        $rui_lh_nonope_loss_sum = $rui_l_nonope_loss_sum - $rui_s_nonope_loss_sum_sagaku - $rui_b_nonope_loss_sum_sagaku;
        $rui_lh_nonope_loss_sum_sagaku = $rui_lh_nonope_loss_sum;
        $rui_l_nonope_loss_sum         = $rui_l_nonope_loss_sum - $rui_s_nonope_loss_sum_sagaku;     // 試験修理営業外費用計をリニアの営業外費用計よりマイナス
        $rui_l_nonope_loss_sum_temp = $rui_l_nonope_loss_sum;         // 経常利益計算用
        $rui_lh_nonope_loss_sum = number_format(($rui_lh_nonope_loss_sum / $tani), $keta);
        $rui_l_nonope_loss_sum      = number_format(($rui_l_nonope_loss_sum / $tani), $keta);
    }
} elseif ($yyyymm >= 201001 && $yyyymm <= 201003) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=200904 and pl_bs_ym<=200912 and note='リニア営業外費用計'");
    if (getUniResult($query, $rui_l_nonope_loss_sum_a) < 1) {
        $rui_l_nonope_loss_sum_a = 0;                          // 検索失敗
    }
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=201001 and pl_bs_ym<=%d and note='リニア営業外費用計再計算'", $yyyymm);
    if (getUniResult($query, $rui_l_nonope_loss_sum_b) < 1) {
        $rui_l_nonope_loss_sum_b = 0;                          // 検索失敗
    }
    $rui_l_nonope_loss_sum      = $rui_l_nonope_loss_sum_a + $rui_l_nonope_loss_sum_b;
    $rui_l_nonope_loss_sum = $rui_l_nonope_loss_sum + $rui_s_nonope_loss_sum_sagaku;
    $rui_l_nonope_loss_sum      = $rui_l_nonope_loss_sum - $rui_s_srisoku_a - $rui_s_lother_a;
    $rui_lh_nonope_loss_sum = $rui_l_nonope_loss_sum - $rui_s_nonope_loss_sum_sagaku - $rui_b_nonope_loss_sum_sagaku;
    $rui_lh_nonope_loss_sum_sagaku = $rui_lh_nonope_loss_sum;
    $rui_l_nonope_loss_sum         = $rui_l_nonope_loss_sum - $rui_s_nonope_loss_sum_sagaku;     // 試験修理営業外費用計をリニアの営業外費用計よりマイナス
    $rui_l_nonope_loss_sum_temp = $rui_l_nonope_loss_sum;         // 経常利益計算用
    $rui_lh_nonope_loss_sum = number_format(($rui_lh_nonope_loss_sum / $tani), $keta);
    $rui_l_nonope_loss_sum      = number_format(($rui_l_nonope_loss_sum / $tani), $keta);
} else {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='リニア営業外費用計'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_l_nonope_loss_sum) < 1) {
        $rui_l_nonope_loss_sum = 0 - $rui_s_nonope_loss_sum_sagaku;   // 検索失敗
        $rui_lh_nonope_loss_sum = 0;     // 検索失敗
        $rui_lh_nonope_loss_sum_sagaku = 0;     // 検索失敗
    } else {
        //$rui_l_nonope_loss_sum = $rui_l_nonope_loss_sum + $rui_s_nonope_loss_sum_sagaku;
        $rui_lh_nonope_loss_sum = $rui_l_nonope_loss_sum - $rui_s_nonope_loss_sum_sagaku - $rui_b_nonope_loss_sum_sagaku;
        $rui_lh_nonope_loss_sum_sagaku = $rui_lh_nonope_loss_sum;
        $rui_l_nonope_loss_sum         = $rui_l_nonope_loss_sum - $rui_s_nonope_loss_sum_sagaku;     // 試験修理営業外費用計をリニアの営業外費用計よりマイナス
        $rui_lh_nonope_loss_sum = number_format(($rui_lh_nonope_loss_sum / $tani), $keta);
        $rui_l_nonope_loss_sum = number_format(($rui_l_nonope_loss_sum / $tani), $keta);
    }
}
/********** 経常利益 **********/
    ///// 試験・修理
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
    ///// 機工
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

    ///// 当月
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア経常利益再計算'", $yyyymm);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア経常利益'", $yyyymm);
}
if (getUniResult($query, $l_current_profit) < 1) {
    $l_current_profit  = 0;       // 検索失敗
    $lh_current_profit = 0;       // 検索失敗
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
        $l_current_profit = $l_current_profit - $l_kyu_kin; // 添田さんの給与をC・Lは35%。試験修理に30%振分
        //$l_current_profit = $l_current_profit - 151313; // 添田さんの給与をC・Lは35%。試験修理に30%振分
    }
    if ($yyyymm == 201004) {
        $l_current_profit = $l_current_profit - 255240;
    }
    // 2012/02/08 追加 2012年1月度 業務委託費（平出横川派遣料）調整
    if ($yyyymm == 201201) {
        $l_current_profit -=1156130;
    }
    // 2012/03/05 追加 2012年1月度 業務委託費（平出横川派遣料）調整 戻し
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
    ///// 前月
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア経常利益再計算'", $p1_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア経常利益'", $p1_ym);
}
if (getUniResult($query, $p1_l_current_profit) < 1) {
    $p1_l_current_profit  = 0;       // 検索失敗
    $p1_lh_current_profit = 0;       // 検索失敗
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
        $p1_l_current_profit = $p1_l_current_profit - $p1_l_kyu_kin;   // 添田さんの給与をC・Lは35%。試験修理に30%振分
        //$p1_c_current_profit = $p1_c_current_profit - 151313;   // 添田さんの給与をC・Lは35%。試験修理に30%振分
    }
    if ($p1_ym == 201004) {
        $p1_l_current_profit = $p1_l_current_profit - 255240;
    }
    // 2012/02/08 追加 2012年1月度 業務委託費（平出横川派遣料）調整
    if ($p1_ym == 201201) {
        $p1_l_current_profit -=1156130;
    }
    // 2012/03/05 追加 2012年1月度 業務委託費（平出横川派遣料）調整 戻し
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
    ///// 前前月
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア経常利益再計算'", $p2_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア経常利益'", $p2_ym);
}
if (getUniResult($query, $p2_l_current_profit) < 1) {
    $p2_l_current_profit  = 0;       // 検索失敗
    $p2_lh_current_profit = 0;       // 検索失敗
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
    // 2012/02/08 追加 2012年1月度 業務委託費（平出横川派遣料）調整
    if ($p2_ym == 201201) {
        $p2_l_current_profit -=1156130;
    }
    // 2012/03/05 追加 2012年1月度 業務委託費（平出横川派遣料）調整 戻し
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
    ///// 今期累計
if ($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='リニア経常利益再計算'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_l_current_profit) < 1) {
        $rui_l_current_profit = 0 - $rui_s_current_profit_sagaku;   // 検索失敗
        $rui_lh_current_profit = 0;     // 検索失敗
        $rui_lh_current_profit_sagaku = 0;     // 検索失敗
    } else {
        //$rui_l_current_profit = $rui_l_current_profit - $rui_s_current_profit_sagaku + $rui_l_allo_kin;
        // 2012/02/08 追加 2012年1月度 業務委託費（平出横川派遣料）調整
        if ($yyyymm >= 201201 && $yyyymm <= 201203) {
            $rui_l_current_profit -=1156130;
        }
        // 2012/03/05 追加 2012年1月度 業務委託費（平出横川派遣料）調整 戻し
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
    //$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=200904 and pl_bs_ym<=200911 and note='リニア経常利益'");
    //if (getUniResult($query, $rui_l_current_profit_a) < 1) {
    //    $rui_l_current_profit_a = 0;                          // 検索失敗
    //}
    //$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=200912 and pl_bs_ym<=%d and note='リニア経常利益再計算'", $yyyymm);
    //if (getUniResult($query, $rui_l_current_profit_b) < 1) {
    //    $rui_l_current_profit_b = 0;                          // 検索失敗
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
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='リニア経常利益'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_l_current_profit) < 1) {
        $rui_l_current_profit = 0 - $rui_s_current_profit_sagaku;   // 検索失敗
        $rui_lh_current_profit = 0;     // 検索失敗
        $rui_lh_current_profit_sagaku = 0;     // 検索失敗
    } else {
        $rui_l_current_profit = $rui_l_current_profit  + $rui_l_allo_kin;
        if ($yyyymm >= 200912 && $yyyymm <= 201003) {
            $rui_l_current_profit = $rui_l_current_profit - 182279;
        }
        $rui_lh_current_profit = $rui_l_current_profit - $rui_s_current_profit_sagaku - $rui_b_current_profit_sagaku;
        $rui_lh_current_profit_sagaku = $rui_lh_current_profit;
        //$rui_l_current_profit = $rui_l_current_profit + $rui_sc_uri_sagaku - $rui_sc_metarial_sagaku;     // カプラ試修売上高を加味（合計欄用）
        $rui_l_current_profit         = $rui_l_current_profit - $rui_s_current_profit_sagaku;     // 試験修理経常利益をリニアの経常利益よりマイナス
        $rui_lh_current_profit = number_format(($rui_lh_current_profit / $tani), $keta);
        $rui_l_current_profit = number_format(($rui_l_current_profit / $tani), $keta);
    }
}

////////// 特記事項の取得
$query = sprintf("select comment from act_comment_history where pl_bs_ym=%d and item='リニアbls損益計算書'", $yyyymm);
if (getUniResult($query,$comment_l) <= 0) {
    $comment_l = "";
}
$query = sprintf("select comment from act_comment_history where pl_bs_ym=%d and item='機工損益計算書'", $yyyymm);
if (getUniResult($query,$comment_b) <= 0) {
    $comment_b = "";
}

$query = sprintf("select comment from act_comment_history where pl_bs_ym=%d and item='全体bls損益計算書'", $yyyymm);
if (getUniResult($query,$comment_all) <= 0) {
    $comment_all = "";
}
$query = sprintf("select comment from act_comment_history where pl_bs_ym=%d and item='その他bls損益計算書'", $yyyymm);
if (getUniResult($query,$comment_other) <= 0) {
    $comment_other = "";
}
if (isset($_POST['input_data'])) {                        // 当月データの登録
    ///////// 項目とインデックスの関連付け
    $item = array();
    $item[0]   = "売上高";
    $item[1]   = "期首材料仕掛品棚卸高";
    $item[2]   = "材料費(仕入高)";
    $item[3]   = "労務費";
    $item[4]   = "製造経費";
    $item[5]   = "期末材料仕掛品棚卸高";
    $item[6]   = "売上原価";
    $item[7]   = "売上総利益";
    $item[8]   = "人件費";
    $item[9]   = "経費";
    $item[10]  = "販管費及び一般管理費計";
    $item[11]  = "営業利益";
    $item[12]  = "業務委託収入";
    $item[13]  = "仕入割引";
    $item[14]  = "営業外収益その他";
    $item[15]  = "営業外収益計";
    $item[16]  = "支払利息";
    $item[17]  = "営業外費用その他";
    $item[18]  = "営業外費用計";
    $item[19]  = "経常利益";
    ///////// 各データの保管 リニア標準=0 機工=1
    $input_data = array();
    for ($i = 0; $i < 20; $i++) {
        switch ($i) {
                case  0:                                            // 売上高
                    $input_data[$i][0] = $lh_uri;                   // リニア標準
                    $input_data[$i][1] = $b_uri;                    // 機工
                break;
                case  1:                                            // 期首材料仕掛品棚卸高
                    $input_data[$i][0] = $lh_invent;                // リニア標準
                    $input_data[$i][1] = $b_invent;                 // 機工
                break;
                case  2:                                            // 材料費(仕入高)
                    $input_data[$i][0] = $lh_metarial;              // リニア標準
                    $input_data[$i][1] = $b_metarial;               // 機工
                break;
                case  3:                                            // 労務費
                    $input_data[$i][0] = $lh_roumu;                 // リニア標準
                    $input_data[$i][1] = $b_roumu;                  // 機工
                break;
                case  4:                                            // 製造経費
                    $input_data[$i][0] = $lh_expense;               // リニア標準
                    $input_data[$i][1] = $b_expense;                // 機工
                break;
                case  5:                                            // 期末材料仕掛品棚卸高
                    $input_data[$i][0] = $lh_endinv;                // リニア標準
                    $input_data[$i][1] = $b_endinv;                 // 機工
                break;
                case  6:                                            // 売上原価
                    $input_data[$i][0] = $lh_urigen;                // リニア標準
                    $input_data[$i][1] = $b_urigen;                 // 機工
                break;
                case  7:                                            // 売上総利益
                    $input_data[$i][0] = $lh_gross_profit;          // リニア標準
                    $input_data[$i][1] = $b_gross_profit;           // 機工
                break;
                case  8:                                            // 人件費
                    $input_data[$i][0] = $lh_han_jin;               // リニア標準
                    $input_data[$i][1] = $b_han_jin;                // 機工
                break;
                case  9:                                            // 経費
                    $input_data[$i][0] = $lh_han_kei;               // リニア標準
                    $input_data[$i][1] = $b_han_kei;                // 機工
                break;
                case 10:                                            // 販管費及び一般管理費計
                    $input_data[$i][0] = $lh_han_all;               // リニア標準
                    $input_data[$i][1] = $b_han_all;                // 機工
                break;
                case 11:                                            // 営業利益
                    $input_data[$i][0] = $lh_ope_profit;            // リニア標準
                    $input_data[$i][1] = $b_ope_profit;             // 機工
                break;
                case 12:                                            // 業務委託収入
                    $input_data[$i][0] = $lh_gyoumu;                // リニア標準
                    $input_data[$i][1] = $b_gyoumu;                 // 機工
                break;
                case 13:                                            // 仕入割引
                    $input_data[$i][0] = $lh_swari;                 // リニア標準
                    $input_data[$i][1] = $b_swari;                  // 機工
                break;
                case 14:                                            // 営業外収益その他
                    $input_data[$i][0] = $lh_pother;                // リニア標準
                    $input_data[$i][1] = $b_pother;                 // 機工
                break;
                case 15:                                            // 営業外収益計
                    $input_data[$i][0] = $lh_nonope_profit_sum;     // リニア標準
                    $input_data[$i][1] = $b_nonope_profit_sum;      // 機工
                break;
                case 16:                                            // 支払利息
                    $input_data[$i][0] = $lh_srisoku;               // リニア標準
                    $input_data[$i][1] = $b_srisoku;                // 機工
                break;
                case 17:                                            // 営業外費用その他
                    $input_data[$i][0] = $lh_lother;                // リニア標準
                    $input_data[$i][1] = $b_lother;                 // 機工
                break;
                case 18:                                            // 営業外費用計
                    $input_data[$i][0] = $lh_nonope_loss_sum;       // リニア標準
                    $input_data[$i][1] = $b_nonope_loss_sum;        // 機工
                break;
                case 19:                                            // 経常利益
                    $input_data[$i][0] = $lh_current_profit;        // リニア標準
                    $input_data[$i][1] = $b_current_profit;         // 機工
                break;
                default:
                break;
            }
    }
    // リニア標準登録
    $head  = "リニア標準";
    $sec   = 0;
    insert_date($head,$item,$yyyymm,$input_data,$sec);
    // 機工登録
    $head  = "機工";
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
            /////////// begin トランザクション開始
            if ($con = db_connect()) {
                query_affected_trans($con, "begin");
            } else {
                $_SESSION["s_sysmsg"] .= "データベースに接続できません";
                exit();
            }
            ////////// Insert Start
            $query = sprintf("insert into profit_loss_pl_history (pl_bs_ym, kin, note, last_date, last_user) values (%d, %d, '%s', CURRENT_TIMESTAMP, '%s')", $yyyymm, $input_data[$i][$sec], $item_in[$i], $_SESSION['User_ID']);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION["s_sysmsg"] .= sprintf("%sの新規登録に失敗<br> %d", $item_in[$i], $yyyymm);
                query_affected_trans($con, "rollback");     // transaction rollback
                exit();
            }
            /////////// commit トランザクション終了
            query_affected_trans($con, "commit");
            $_SESSION["s_sysmsg"] = sprintf("<font color='yellow'>%d 損益データ 新規 登録完了</font>",$yyyymm);
        } else {
            /////////// begin トランザクション開始
            if ($con = db_connect()) {
                query_affected_trans($con, "begin");
            } else {
                $_SESSION["s_sysmsg"] .= "データベースに接続できません";
                exit();
            }
            ////////// UPDATE Start
            $query = sprintf("update profit_loss_pl_history set kin=%d, last_date=CURRENT_TIMESTAMP, last_user='%s' where pl_bs_ym=%d and note='%s'", $input_data[$i][$sec], $_SESSION['User_ID'], $yyyymm, $item_in[$i]);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION["s_sysmsg"] .= sprintf("%sのUPDATEに失敗<br> %d", $item_in[$i], $yyyymm);
                query_affected_trans($con, "rollback");     // transaction rollback
                exit();
            }
            /////////// commit トランザクション終了
            query_affected_trans($con, "commit");
            $_SESSION["s_sysmsg"] = sprintf("<font color='yellow'>%d 損益データ 変更 完了</font>",$yyyymm);
        }
    }
    $_SESSION["s_sysmsg"] .= "当月のデータを登録しました。";
}
/////////// HTML Header を出力してキャッシュを制御
$menu->out_html_header();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<title><?= $menu->out_title() ?></title>
<?= $menu->out_site_java() ?>
<?= $menu->out_css() ?>
<?= $menu->out_jsBaseClass() ?>
<script type=text/javascript language='JavaScript'>
<!--
/* 入力文字が数字かどうかチェック */
function isDigit(str) {
    var len=str.length;
    var c;
    for (i=0; i<len; i++) {
        c = str.charAt(i);
        if (("0" > c) || (c > "9")) {
            alert("数値以外は入力出来ません。");
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
            alert("数値以外は入力出来ません。");
            return false;
        }
    }
    return true;
}
/* 初期入力エレメントへフォーカスさせる */
function set_focus(){
    document.jin.jin_1.focus();
    document.jin.jin_1.select();
}
function data_input_click(obj) {
    return confirm("当月のデータを登録します。\n既にデータがある場合は上書きされます。");
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
                        単位
                        <select name='keihi_tani' class='pt10'>
                        <?php
                            if ($tani == 1000)
                                echo "<option value='1000' selected>　千円</option>\n";
                            else
                                echo "<option value='1000'>　千円</option>\n";
                            if ($tani == 1)
                                echo "<option value='1' selected>　　円</option>\n";
                            else
                                echo "<option value='1'>　　円</option>\n";
                            if ($tani == 1000000)
                                echo "<option value='1000000' selected>百万円</option>\n";
                            else
                                echo "<option value='1000000'>百万円</option>\n";
                            if ($tani == 10000)
                                echo "<option value='10000' selected>　万円</option>\n";
                            else
                                echo "<option value='10000'>　万円</option>\n";
                            if($tani == 100000)
                                echo "<option value='100000' selected>十万円</option>\n";
                            else
                                echo "<option value='100000'>十万円</option>\n";
                        ?>
                        </select>
                        少数桁
                        <select name='keihi_keta' class='pt10'>
                        <?php
                            if ($keta == 0)
                                echo "<option value='0' selected>０桁</option>\n";
                            else
                                echo "<option value='0'>０桁</option>\n";
                            if ($keta == 3)
                                echo "<option value='3' selected>３桁</option>\n";
                            else
                                echo "<option value='3'>３桁</option>\n";
                            if ($keta == 6)
                                echo "<option value='6' selected>６桁</option>\n";
                            else
                                echo "<option value='6'>６桁</option>\n";
                            if ($keta == 1)
                                echo "<option value='1' selected>１桁</option>\n";
                            else
                                echo "<option value='1'>１桁</option>\n";
                            if ($keta == 2)
                                echo "<option value='2' selected>２桁</option>\n";
                            else
                                echo "<option value='2'>２桁</option>\n";
                            if ($keta == 4)
                                echo "<option value='4' selected>４桁</option>\n";
                            else
                                echo "<option value='4'>４桁</option>\n";
                            if ($keta == 5)
                                echo "<option value='5' selected>５桁</option>\n";
                            else
                                echo "<option value='5'>５桁</option>\n";
                        ?>
                        </select>
                        <input class='pt10b' type='submit' name='return' value='単位変更'>
                        <?php
                        if ($_SESSION['User_ID'] == '300144') {
                            if ($keta == 0 && $tani == 1) {
                        ?>
                            &nbsp;
                            <input class='pt10b' type='submit' name='input_data' value='当月データ登録' onClick='return data_input_click(this)'>
                        <?php
                            } else {
                        ?>
                            <input class='pt10b' type='submit' name='input_data' value='当月データ登録' onClick='return data_input_click(this)' disabled>
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
                    <td rowspan='2' colspan='3' width='200' align='center' class='pt10b' bgcolor='#ffffc6'>項　　　目</td>
                    <td colspan='4' align='center' class='pt10b' bgcolor='#ffffc6'>リ　ニ　ア</td>
                    <td colspan='4' align='center' class='pt10b' bgcolor='#ffffc6'>ツ　ー　ル</td>
                    <td colspan='4' align='center' class='pt10b' bgcolor='#ffffc6'>合　　　計</td>
                    <td rowspan='2' width='400' align='left' class='pt10b' bgcolor='#ffffc6'>製造間接経費・販管費の配賦基準</td>
                </tr>
                <tr>
                    <td nowrap align='center' class='pt10b' bgcolor='#d6d3ce'><?php echo $p2_ym ?> </td>
                    <td nowrap align='center' class='pt10b' bgcolor='#d6d3ce'><?php echo $p1_ym ?> </td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'><?php echo $yyyymm ?></td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>累　計</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#d6d3ce'><?php echo $p2_ym ?> </td>
                    <td nowrap align='center' class='pt10b' bgcolor='#d6d3ce'><?php echo $p1_ym ?> </td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'><?php echo $yyyymm ?></td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>累　計</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#d6d3ce'><?php echo $p2_ym ?> </td>
                    <td nowrap align='center' class='pt10b' bgcolor='#d6d3ce'><?php echo $p1_ym ?> </td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'><?php echo $yyyymm ?></td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>累　計</td>
                </tr>
                <tr>
                    <td rowspan='11' width='10' align='center' valign='middle' class='pt10b' bgcolor='#ceffce'>営　業　損　益</td>
                    <td colspan='2' nowrap align='center' class='pt10b' bgcolor='#ceffce'>売　上　高</td>
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
                    <td nowrap align='left'  class='pt10' bgcolor='#d6d3ce'>実際売上高</td>
                </tr>
                <tr>
                    <td rowspan='6' width='10' align='center' valign='middle' class='pt10b' bgcolor='#ffffc6'>売上原価</td> <!-- 売上原価 -->
                    <td nowrap align='left' class='pt10b' bgcolor='white'>　期首材料仕掛品棚卸高</td>
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
                    <td nowrap align='left'  class='pt10'>標準原価による棚卸高</td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='#e6e6e6'>　材料費(仕入高)</td>
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
                    <td nowrap align='left'  class='pt10'>買掛購入高比</td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='white'>　労　　務　　費</td>
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
                    <td nowrap align='left'  class='pt10'>サービス割合比及び前半期売上高比</td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='#e6e6e6'>　経　　　　　費</td>
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
                    <td nowrap align='left'  class='pt10'>サービス割合比及び前半期売上高比</td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='white'>　期末材料仕掛品棚卸高</td>
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
                    <td nowrap align='left'  class='pt10'>標準原価による棚卸高</td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='#ffffc6'>　売　上　原　価</td>
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
                    <td nowrap align='left'  class='pt10'>　</td>
                </tr>
                <tr>
                    <td colspan='2' nowrap align='center' class='pt10b' bgcolor='#ceffce'>売　上　総　利　益</td>
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
                    <td nowrap align='left'  class='pt10' bgcolor='#d6d3ce'>　</td>  <!-- 余白 -->
                </tr>
                <tr>
                    <td rowspan='3' width='10' align='center' valign='middle' class='pt10b' bgcolor='#ffffc6'></td> <!-- 販管費 -->
                    <td nowrap align='left' class='pt10b' bgcolor='#e6e6e6'>　人　　件　　費</td>
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
                    <td nowrap align='left'  class='pt10'>部門人員比率</td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='white'>　経　　　　　費</td>
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
                    <td nowrap align='left'  class='pt10'>部門人員比率</td>
                </tr>
                <tr>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>販管費及び一般管理費計</td>
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
                    <td nowrap align='left'  class='pt10'>　</td>  <!-- 余白 -->
                </tr>
                <tr>
                    <td colspan='3' nowrap align='center' class='pt10b' bgcolor='#ceffce'>営　　業　　利　　益</td>
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
                    <td nowrap align='left'  class='pt10'>　</td>  <!-- 余白 -->
                </tr>
                <tr>
                    <td rowspan='7' align='center' valign='middle' class='pt10b' bgcolor='#ceffce'>営業外損益</td>
                    <td rowspan='4' align='center' class='pt10' bgcolor='#ffffc6'></td> <!-- 余白 -->
                    <td nowrap align='left' class='pt10b' bgcolor='white'>　業務委託収入</td>
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
                    <td nowrap align='left'  class='pt10'>前半期実績の売上高比</td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='#e6e6e6'>　仕　入　割　引</td>
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
                    <td nowrap align='left'  class='pt10'>前半期実績の売上高比</td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='white'>　そ　　の　　他</td>
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
                    <td nowrap align='left'  class='pt10'>前半期実績の売上高比</td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='#ffffc6'>　営業外収益 計</td>
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
                    <td nowrap align='left'  class='pt10'>　</td> <!-- 余白 -->
                </tr>
                <tr>
                    <td rowspan='3' align='center' class='pt10' bgcolor='#ffffc6'></td> <!-- 余白 -->
                    <td nowrap align='left' class='pt10b' bgcolor='#e6e6e6'>　支　払　利　息</td>
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
                    <td nowrap align='left'  class='pt10'>前半期実績の売上高比</td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='white'>　そ　　の　　他</td>
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
                    <td nowrap align='left'  class='pt10'>前半期実績の売上高比</td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='#ffffc6'>　営業外費用 計</td>
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
                    <td nowrap align='left'  class='pt10'>　</td> <!-- 余白 -->
                </tr>
                <tr>
                    <td colspan='3' nowrap align='center' class='pt10b' bgcolor='#ceffce'>経　　常　　利　　益</td>
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
                    <td nowrap align='left'  class='pt10'>　</td>  <!-- 余白 -->
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
                    <td colspan='20' bgcolor='white' align='left' class='pt10b'><a href='<?=$menu->out_action('特記事項入力')?>?<?php echo uniqid('menu') ?>' style='text-decoration:none; color:black;'>　※　月次損益特記事項</a></td>
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
