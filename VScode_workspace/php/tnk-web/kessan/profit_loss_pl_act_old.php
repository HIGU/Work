<?php
//////////////////////////////////////////////////////////////////////////////
// 月次損益関係 月次 ＣＬ・商品管理・試験修理 損益計算書                    //
// Copyright (C) 2003-2013 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2003/02/12 Created   profit_loss_pl_act.php                              //
// 2003/02/23 date("Y/m/d H:m:s") → H:i:s のミス修正                       //
// 2003/03/04 文字サイズをブラウザーで変更できなくした title_font 等        //
//            特記事項をカプラ・リニア以外に全体とその他を追加              //
// 2003/03/06 title_font today_font を設定 少数以下の桁数６桁を追加         //
// 2003/03/11 Location: http → Location $url_referer に変更                //
//            メッセージを出力するため site_index site_id をコメントにし    //
//            parent.menu_site.を有効に変更                                 //
// 2003/05/01 工場長からの指示で認証をAccount_groupから通常へ変更           //
// 2003/08/05 $p1_c_srisoku → $p1_l_srisoku になっていたのを修正           //
// 2003/12/15 販管費及び  一般管理費 計 → 一般管理費計 (スペースを削除)    //
// 2004/05/11 左側のサイトメニューのオン・オフ ボタンを追加                 //
// 2005/10/26 MenuHeader class を使用して共通メニュー化及び認証方式へ変更   //
// 2005/11/08 $menu->out_action('特記事項入力')を<a href=に追加             //
// 2006/03/07 前回 style='overflow-y:hidden;' をうっかり付けたためコメント  //
// 2007/11/08 前月の仕入割引の表示が$p1_l_swari → $p1_c_swari へ訂正       //
// 2009/08/17 物流の損益表示を追加（暫定）                             大谷 //
// 2009/08/18 試験・修理部門の損益表示を追加（暫定）                   大谷 //
// 2009/08/19 物流を商品管理に名称変更                                 大谷 //
// 2009/08/20 コメントを編集                                           大谷 //
// 2009/08/21 損益をExcelにあわせて200904～200906に調整を入れた        大谷 //
// 2009/10/06 商管の売上高がASに登録されたのでその対応200909より       大谷 //
//            入力画面で調整金額を入力しこっちでマイナスする           大谷 //
// 2009/10/15 売上高・売上総利益・営業利益・経常利益を太字に変更       大谷 //
// 2009/10/29 商管への社員給与按分加算に対応$～_allo_kin               大谷 //
// 2009/11/09 商管の売上調整が前月・前々月に入っていなかったので修正   大谷 //
// 2009/11/12 リニアの調整金額がうまく取込めなかったのを修正           大谷 //
// 2009/12/07 カプラ試験修理の売上高を加味するよう変更                 大谷 //
// 2009/12/10 段落を調整                                               大谷 //
// 2010/01/15 200912度の添田さんの労務費を調整                         大谷 //
// 2010/01/19 200912度の業務委託収入とその他を調整（1月度戻しの分も）  大谷 //
// 2010/02/01 201001度より営業外を人員比率で再計算した値に置き換え     大谷 //
// 2010/02/04 201001度の添田さんの労務費を調整                              //
//            労務費を入力して配賦するようプログラムを作成予定         大谷 //
// 2010/02/08 201001度から配賦した労務費を加味するように変更           大谷 //
// 2010/03/04 201002度営業外収益その他の調整を追加。201003には戻し     大谷 //
// 2010/04/08 $p2_l_kyu_kinの2が抜けていたため訂正                     大谷 //
// 2010/04/12 当月のリニア経常利益で桁区切りがされていなかったのを訂正 大谷 //
// 2010/05/11 201004度リニアの売上(ツール)255,240円を商管に移動             //
//            また、累計が正しくとれていなかった点を修正               大谷 //
// 2010/10/08 グラフ作成用のデータ登録を追加                           大谷 //
// 2011/05/10 商管の売上調整が２重で入っていた為訂正                   大谷 //
// 2011/07/14 データ登録で労務費と経費のデータが同じだったのを修正     大谷 //
// 2011/10/08 経常利益以下(当期純利益等)を追加(データ登録はなし)       大谷 //
// 2012/01/16 経常利益以下のデータ登録を追加(２期比較表用)             大谷 //
// 2012/02/03 エラー発生部を修正（検索失敗時_tが指定されていなかった） 大谷 //
// 2012/02/28 2012年1月 業務委託費 調整 リニア製造経費 +1,156,130円    大谷 //
//             ※ 平出横川派遣料 2月に逆調整を行うこと                      //
// 2012/03/05 2012年1月 業務委託費 調整 リニア製造経費 -1,156,130円 戻 大谷 //
// 2012/07/07 2012年6月 営業外費用の調整をこっちでしようと思ったが          //
//            再計算の方で調整するため変更なし                         大谷 //
// 2013/11/07 2013年10月 商管業務委託費 調整                                //
//            カプラ材料費 -1,245,035円、商管製造経費 +1,245,035円     大谷 //
//             ※ 横川派遣料 11月に逆調整を行うこと                         //
// 2013/11/07 2013年11月 商管業務委託費 調整                                //
//            カプラ材料費 +1,245,035円、商管製造経費 -1,245,035円     大谷 //
// 2014/09/04 商管の製造経費労務費を各セグメント配賦の為調整           大谷 //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);    // E_STRICT=2048(php5) E_ALL=2047 debug 用
// ini_set('error_reporting', E_ALL);       // E_ALL='2047' debug 用
// ini_set('display_errors', '1');          // Error 表示 ON debug 用 リリース後コメント
session_start();                            // ini_set()の次に指定すること Script 最上行

require_once ('../function.php');           // define.php と pgsql.php を require_once している
require_once ('../tnk_func.php');           // TNK に依存する部分の関数を require_once している
require_once ('../MenuHeader.php');         // TNK 全共通 menu class
access_log();                               // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader(0);                  // 認証チェック0=一般以上 戻り先=TOP_MENU タイトル未設定
   // 実際の認証はprofit_loss_submit.phpで行っているaccount_group_check()を使用

///// サイト設定
// $menu->set_site(10, 7);                  // site_index=10(損益メニュー) site_id=7(月次損益)
///// 表題の設定
$menu->set_caption('栃木日東工器(株)');
///// 呼出先のaction名とアドレス設定
$menu->set_action('特記事項入力',   PL . 'profit_loss_comment_put.php');

///// 期・月の取得
$ki = Ym_to_tnk($_SESSION['pl_ym']);
$tuki = substr($_SESSION['pl_ym'],4,2);

///// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title("第 {$ki} 期　{$tuki} 月度　Ｃ Ｌ 試修 商管 商 品 別 損 益 計 算 書");

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
if ( $yyyymm >= 200909) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='商管売上高'", $yyyymm);
    if (getUniResult($query, $b_uri) < 1) {
        $b_uri        = 0;      // 検索失敗
        $b_uri_sagaku = 0;
    } else {
        if ($yyyymm == 201004) {
            $b_uri = $b_uri + 255240;
        }
        $b_uri_sagaku = 0;
    }
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='商管売上調整額'", $yyyymm);
    if (getUniResult($query, $b_uri_cho) < 1) {
        // 検索失敗 調整が無いので何もしない
        $b_uri_sagaku = 0;
    } else {
        $b_uri        = $b_uri + $b_uri_cho;
        $b_uri_sagaku = $b_uri_cho;
    }
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='商管売上高'", $yyyymm);
    if (getUniResult($query, $b_uri) < 1) {
        $b_uri        = 0;      // 検索失敗
        $b_uri_sagaku = 0;
    } else {
        $b_uri_sagaku = $b_uri;
    }
}
if ( $yyyymm >= 200911) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ試修売上高'", $yyyymm);
    if (getUniResult($query, $sc_uri) < 1) {
        $sc_uri        = 0;     // 検索失敗
        $sc_uri_sagaku = 0;
        $sc_uri_temp   = 0;
    } else {
        $sc_uri_temp   = $sc_uri;
        $sc_uri_sagaku = $sc_uri;
        $sc_uri        = number_format(($sc_uri / $tani), $keta);
    }
} else{
    $sc_uri        = 0;         // 検索失敗
    $sc_uri_sagaku = 0;
    $sc_uri_temp   = 0;
}
if ( $yyyymm >= 200909) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修売上高'", $yyyymm);
    if (getUniResult($query, $s_uri) < 1) {
        $s_uri        = 0;      // 検索失敗
        $s_uri_sagaku = 0;
        $s_uri_temp   = 0;
    } else {
        $s_uri_temp = $s_uri;
        if ($yyyymm == 200906) {
            $s_uri  = $s_uri - 3100900;
        } elseif ($yyyymm == 200905) {
            $s_uri  = $s_uri + 1550450;
        } elseif ($yyyymm == 200904) {
            $s_uri  = $s_uri + 1550450;
        }
        $s_uri_sagaku = $s_uri;
    }
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修売上調整額'", $yyyymm);
    if (getUniResult($query, $s_uri_cho) < 1) {
        // 検索失敗
        $s_uri = $s_uri + $sc_uri_sagaku;       // カプラ試験修理を加味
        $s_uri = number_format(($s_uri / $tani), $keta);
    } else {
        $s_uri_sagaku = $s_uri_sagaku + $s_uri_cho;
        $s_uri_temp   = $s_uri_sagaku;
        $s_uri        = $s_uri_sagaku + $sc_uri_sagaku;         // カプラ試験修理を加味（tempの後－リニアからマイナスしてしまう為）
        $s_uri        = number_format(($s_uri / $tani), $keta);
    }
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修売上高'", $yyyymm);
    if (getUniResult($query, $s_uri) < 1) {
        $s_uri        = 0;      // 検索失敗
        $s_uri_sagaku = 0;
        $s_uri_temp   = 0;
    } else {
        $s_uri_temp = $s_uri;
        if ($yyyymm == 200906) {
            $s_uri  = $s_uri - 3100900;
        } elseif ($yyyymm == 200905) {
            $s_uri  = $s_uri + 1550450;
        } elseif ($yyyymm == 200904) {
            $s_uri  = $s_uri + 1550450;
        }
        $s_uri_sagaku = $s_uri;
        $s_uri        = number_format(($s_uri / $tani), $keta);
    }
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='全体売上高'", $yyyymm);
if (getUniResult($query, $all_uri) < 1) {
    $all_uri = 0;               // 検索失敗
} else {
    if ($yyyymm == 200906) {
        $all_uri = $all_uri + $b_uri_sagaku - 3100900;
    } elseif ($yyyymm == 200905) {
        $all_uri = $all_uri + $b_uri_sagaku + 1550450;
    } elseif ($yyyymm == 200904) {
        $all_uri = $all_uri + $b_uri_sagaku + 1550450;
    } else {
        $all_uri = $all_uri + $b_uri_sagaku;
    }
    $all_uri = number_format(($all_uri / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ売上高'", $yyyymm);
if (getUniResult($query, $c_uri) < 1) {
    $c_uri = 0;                 // 検索失敗
} else {
    $c_uri = $c_uri - $sc_uri_sagaku;                   // カプラ試験修理を加味
    $c_uri = number_format(($c_uri / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア売上高'", $yyyymm);
if (getUniResult($query, $l_uri) < 1) {
    $l_uri = 0 - $s_uri_temp;   // 検索失敗
} else {
    $l_uri = $l_uri - $s_uri_temp;
    if ($yyyymm == 201004) {
        $l_uri = $l_uri - 255240;
    }
    $l_uri = number_format(($l_uri / $tani), $keta);
}
    ///// 前月
if ($yyyymm >= 200910) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='商管売上高'", $p1_ym);
    if (getUniResult($query, $p1_b_uri) < 1) {
        $p1_b_uri        = 0;   // 検索失敗
        $p1_b_uri_sagaku = 0;
    } else {
        if ($p1_ym == 201004) {
            $p1_b_uri = $p1_b_uri + 255240;
        }
        $p1_b_uri_sagaku = 0;
    }
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='商管売上調整額'", $p1_ym);
    if (getUniResult($query, $p1_b_uri_cho) < 1) {
        // 検索失敗 調整が無いので何もしない
    } else {
        $p1_b_uri        = $p1_b_uri + $p1_b_uri_cho;
        $p1_b_uri_sagaku = $p1_b_uri_cho;
    }
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='商管売上高'", $p1_ym);
    if (getUniResult($query, $p1_b_uri) < 1) {
        $p1_b_uri        = 0;   // 検索失敗
        $p1_b_uri_sagaku = 0;
    } else {
        $p1_b_uri_sagaku = $p1_b_uri;
    }
}
if ( $yyyymm >= 200911) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ試修売上高'", $p1_ym);
    if (getUniResult($query, $p1_sc_uri) < 1) {
        $p1_sc_uri        = 0;      // 検索失敗
        $p1_sc_uri_sagaku = 0;
        $p1_sc_uri_temp   = 0;
    } else {
        $p1_sc_uri_temp   = $p1_sc_uri;
        $p1_sc_uri_sagaku = $p1_sc_uri;
        $p1_sc_uri        = number_format(($p1_sc_uri / $tani), $keta);
    }
} else{
    $p1_sc_uri        = 0;          // 検索失敗
    $p1_sc_uri_sagaku = 0;
    $p1_sc_uri_temp   = 0;
}
if ($yyyymm >= 200910) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修売上高'", $p1_ym);
    if (getUniResult($query, $p1_s_uri) < 1) {
        $p1_s_uri        = 0;       // 検索失敗
        $p1_s_uri_sagaku = 0;
        $p1_s_uri_temp   = 0;
    } else {
        $p1_s_uri_temp = $p1_s_uri;
        if ($p1_ym == 200906) {
            $p1_s_uri  = $p1_s_uri - 3100900;
        } elseif ($p1_ym == 200905) {
            $p1_s_uri  = $p1_s_uri + 1550450;
        } elseif ($p1_ym == 200904) {
            $p1_s_uri  = $p1_s_uri + 1550450;
        }
        $p1_s_uri_sagaku = $p1_s_uri;
    }
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修売上調整額'", $p1_ym);
    if (getUniResult($query, $p1_s_uri_cho) < 1) {
        // 検索失敗
        $p1_s_uri = $p1_s_uri + $p1_sc_uri_sagaku;                  // カプラ試験修理を加味
        $p1_s_uri = number_format(($p1_s_uri / $tani), $keta);
    } else {
        $p1_s_uri_sagaku = $p1_s_uri_sagaku + $p1_s_uri_cho;
        $p1_s_uri_temp   = $p1_s_uri_sagaku;
        $p1_s_uri        = $p1_s_uri_sagaku + $p1_sc_uri_sagaku;    // カプラ試験修理を加味（tempの後－リニアからマイナスしてしまう為）
        $p1_s_uri        = number_format(($p1_s_uri / $tani), $keta);
    }
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修売上高'", $p1_ym);
    if (getUniResult($query, $p1_s_uri) < 1) {
        $p1_s_uri        = 0;           // 検索失敗
        $p1_s_uri_sagaku = 0;
        $p1_s_uri_temp   = 0;
    } else {
        $p1_s_uri_temp = $p1_s_uri;
        if ($p1_ym == 200906) {
            $p1_s_uri  = $p1_s_uri - 3100900;
        } elseif ($p1_ym == 200905) {
            $p1_s_uri  = $p1_s_uri + 1550450;
        } elseif ($p1_ym == 200904) {
            $p1_s_uri  = $p1_s_uri + 1550450;
        }
        $p1_s_uri_sagaku = $p1_s_uri;
        $p1_s_uri        = number_format(($p1_s_uri / $tani), $keta);
    }
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='全体売上高'", $p1_ym);
if (getUniResult($query, $p1_all_uri) < 1) {
    $p1_all_uri = 0;                    // 検索失敗
} else {
    if ($p1_ym == 200906) {
        $p1_all_uri = $p1_all_uri + $p1_b_uri_sagaku - 3100900;
    } elseif ($p1_ym == 200905) {
        $p1_all_uri = $p1_all_uri + $p1_b_uri_sagaku + 1550450;
    } elseif ($p1_ym == 200904) {
        $p1_all_uri = $p1_all_uri + $p1_b_uri_sagaku + 1550450;
    } else {
        $p1_all_uri = $p1_all_uri + $p1_b_uri_sagaku;
    }
    $p1_all_uri = number_format(($p1_all_uri / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ売上高'", $p1_ym);
if (getUniResult($query, $p1_c_uri) < 1) {
    $p1_c_uri = 0;                      // 検索失敗
} else {
    $p1_c_uri = $p1_c_uri - $p1_sc_uri_sagaku;                  // カプラ試験修理を加味
    $p1_c_uri = number_format(($p1_c_uri / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア売上高'", $p1_ym);
if (getUniResult($query, $p1_l_uri) < 1) {
    $p1_l_uri = 0 - $p1_s_uri_temp;     // 検索失敗
} else {
    $p1_l_uri = $p1_l_uri - $p1_s_uri_temp;
    if ($p1_ym == 201004) {
        $p1_l_uri = $p1_l_uri - 255240;
    }
    $p1_l_uri = number_format(($p1_l_uri / $tani), $keta);
}
    ///// 前前月
if ($yyyymm >= 200911) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='商管売上高'", $p2_ym);
    if (getUniResult($query, $p2_b_uri) < 1) {
        $p2_b_uri        = 0;           // 検索失敗
        $p2_b_uri_sagaku = 0;
    } else {
        if ($p2_ym == 201004) {
            $p2_b_uri = $p2_b_uri + 255240;
        }
        $p2_b_uri_sagaku = 0;
    }
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='商管売上調整額'", $p2_ym);
    if (getUniResult($query, $p2_b_uri_cho) < 1) {
        // 検索失敗 調整が無いので何もしない
    } else {
        $p2_b_uri        = $p2_b_uri + $p2_b_uri_cho;
        $p2_b_uri_sagaku = $p2_b_uri_cho;
    }
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='商管売上高'", $p2_ym);
    if (getUniResult($query, $p2_b_uri) < 1) {
        $p2_b_uri        = 0;           // 検索失敗
        $p2_b_uri_sagaku = 0;
    } else {
        $p2_b_uri_sagaku = $p2_b_uri;
    }
}
if ( $yyyymm >= 200911) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ試修売上高'", $p2_ym);
    if (getUniResult($query, $p2_sc_uri) < 1) {
        $p2_sc_uri        = 0;          // 検索失敗
        $p2_sc_uri_sagaku = 0;
        $p2_sc_uri_temp   = 0;
    } else {
        $p2_sc_uri_temp   = $p2_sc_uri;
        $p2_sc_uri_sagaku = $p2_sc_uri;
        $p2_sc_uri        = number_format(($p2_sc_uri / $tani), $keta);
    }
} else{
    $p2_sc_uri        = 0;              // 検索失敗
    $p2_sc_uri_sagaku = 0;
    $p2_sc_uri_temp   = 0;
}
if ($yyyymm >= 200911) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修売上高'", $p2_ym);
    if (getUniResult($query, $p2_s_uri) < 1) {
        $p2_s_uri        = 0;           // 検索失敗
        $p2_s_uri_sagaku = 0;
        $p2_s_uri_temp   = 0;
    } else {
        $p2_s_uri_temp = $p2_s_uri;
        if ($p2_ym == 200906) {
            $p2_s_uri  = $p2_s_uri - 3100900;
        } elseif ($p2_ym == 200905) {
            $p2_s_uri  = $p2_s_uri + 1550450;
        } elseif ($p2_ym == 200904) {
            $p2_s_uri  = $p2_s_uri + 1550450;
        }
        $p2_s_uri_sagaku = $p2_s_uri;
    }
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修売上調整額'", $p2_ym);
    if (getUniResult($query, $p2_s_uri_cho) < 1) {
        // 検索失敗
        $p2_s_uri = $p2_s_uri + $p2_sc_uri_sagaku;                  // カプラ試験修理を加味
        $p2_s_uri = number_format(($p2_s_uri / $tani), $keta);
    } else {
        $p2_s_uri_sagaku = $p2_s_uri_sagaku + $p2_s_uri_cho;
        $p2_s_uri_temp   = $p2_s_uri_sagaku;
        $p2_s_uri        = $p2_s_uri_sagaku + $p2_sc_uri_sagaku;    // カプラ試験修理を加味（tempの後－リニアからマイナスしてしまう為）
        $p2_s_uri        = number_format(($p2_s_uri / $tani), $keta);
    }
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修売上高'", $p2_ym);
    if (getUniResult($query, $p2_s_uri) < 1) {
        $p2_s_uri        = 0;           // 検索失敗
        $p2_s_uri_sagaku = 0;
        $p2_s_uri_temp   = 0;
    } else {
        $p2_s_uri_temp = $p2_s_uri;
        if ($p2_ym == 200906) {
            $p2_s_uri  = $p2_s_uri - 3100900;
        } elseif ($p2_ym == 200905) {
            $p2_s_uri  = $p2_s_uri + 1550450;
        } elseif ($p2_ym == 200904) {
            $p2_s_uri  = $p2_s_uri + 1550450;
        }
        $p2_s_uri_sagaku = $p2_s_uri;
        $p2_s_uri        = number_format(($p2_s_uri / $tani), $keta);
    }
}

$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='全体売上高'", $p2_ym);
if (getUniResult($query, $p2_all_uri) < 1) {
    $p2_all_uri = 0;                    // 検索失敗
} else {
    if ($p2_ym == 200906) {
        $p2_all_uri = $p2_all_uri + $p2_b_uri_sagaku - 3100900;
    } elseif ($p2_ym == 200905) {
        $p2_all_uri = $p2_all_uri + $p2_b_uri_sagaku + 1550450;
    } elseif ($p2_ym == 200904) {
        $p2_all_uri = $p2_all_uri + $p2_b_uri_sagaku + 1550450;
    } else {
        $p2_all_uri = $p2_all_uri + $p2_b_uri_sagaku;
    }
    $p2_all_uri = number_format(($p2_all_uri / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ売上高'", $p2_ym);
if (getUniResult($query, $p2_c_uri) < 1) {
    $p2_c_uri = 0;                      // 検索失敗
} else {
    $p2_c_uri = $p2_c_uri - $p2_sc_uri_sagaku;                  // カプラ試験修理を加味
    $p2_c_uri = number_format(($p2_c_uri / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア売上高'", $p2_ym);
if (getUniResult($query, $p2_l_uri) < 1) {
    $p2_l_uri = 0 - $p2_s_uri_temp;     // 検索失敗
} else {
    $p2_l_uri = $p2_l_uri - $p2_s_uri_temp;
    if ($p2_ym == 201004) {
        $p2_l_uri = $p2_l_uri - 255240;
    }
    $p2_l_uri = number_format(($p2_l_uri / $tani), $keta);
}
    ///// 今期累計
if($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='商管売上高'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_b_uri) < 1) {
        $rui_b_uri        = 0;          // 検索失敗
        $rui_b_uri_sagaku = 0;
    } else {
        if ($yyyymm >= 201004 && $yyyymm <= 201103) {
            $rui_b_uri = $rui_b_uri + 255240;
        }
        $rui_b_uri_sagaku = 0;
    }
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='商管売上調整額'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_b_uri_cho) < 1) {
        // 検索失敗 調整が無いので何もしない
        $rui_b_uri_sagaku = 0;
    } else {
        $rui_b_uri        = $rui_b_uri + $rui_b_uri_cho;
        $rui_b_uri_sagaku = $rui_b_uri_cho;
    }
} else if($yyyymm >= 200909 && $yyyymm <= 201003) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='商管売上高'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_b_uri) < 1) {
        $rui_b_uri        = 0;          // 検索失敗
        $rui_b_uri_sagaku = 0;
    } else {
        $rui_b_uri_sagaku = 0;
    }
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='商管売上調整額'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_b_uri_cho) < 1) {
        // 検索失敗 調整が無いので何もしない
        $rui_b_uri_sagaku = 0;
    } else {
        $rui_b_uri        = $rui_b_uri + $rui_b_uri_cho;
        $rui_b_uri_sagaku = $rui_b_uri_cho + 25354300;      // 7月8月分の調整を9月に入れた分の戻し
    }
} else {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='商管売上高'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_b_uri) < 1) {
        $rui_b_uri        = 0;          // 検索失敗
        $rui_b_uri_sagaku = 0;
    } else {
        $rui_b_uri_sagaku = $rui_b_uri;
    }
}
if ( $yyyymm >= 200911) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='カプラ試修売上高'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_sc_uri) < 1) {
        $rui_sc_uri        = 0;         // 検索失敗
        $rui_sc_uri_sagaku = 0;
        $rui_sc_uri_temp   = 0;
    } else {
        $rui_sc_uri_temp   = $rui_sc_uri;
        $rui_sc_uri_sagaku = $rui_sc_uri;
        $rui_sc_uri        = number_format(($rui_sc_uri / $tani), $keta);
    }
} else{
    $rui_sc_uri        = 0;             // 検索失敗
    $rui_sc_uri_sagaku = 0;
    $rui_sc_uri_temp   = 0;
}
if ($yyyymm >= 200909) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='試修売上高'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_s_uri) < 1) {
        $rui_s_uri        = 0;          // 検索失敗
        $rui_s_uri_sagaku = 0;
    } else {
        $rui_s_uri_sagaku = $rui_s_uri;
    }
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='試修売上調整額'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_s_uri_cho) < 1) {
        // 検索失敗
        $rui_s_uri = $rui_s_uri + $rui_sc_uri_sagaku;                   // カプラ試験修理を加味
        $rui_s_uri = number_format(($rui_s_uri / $tani), $keta);
    } else {
        $rui_s_uri_sagaku = $rui_s_uri_sagaku + $rui_s_uri_cho;
        $rui_s_uri        = $rui_s_uri_sagaku + $rui_sc_uri_sagaku;     // カプラ試験修理を加味（tempの後－リニアからマイナスしてしまう為）
        $rui_s_uri        = number_format(($rui_s_uri / $tani), $keta);
    }
} else {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='試修売上高'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_s_uri) < 1) {
        $rui_s_uri        = 0;          // 検索失敗
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
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='全体売上高'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_all_uri) < 1) {
    $rui_all_uri = 0;                   // 検索失敗
} else {
    if ($yyyymm == 200905) {
        $rui_all_uri = $rui_all_uri + 3100900;
    } elseif ($yyyymm == 200904) {
        $rui_all_uri = $rui_all_uri + 1550450;
    }
    $rui_all_uri = $rui_all_uri + $rui_b_uri_sagaku;
    $rui_all_uri = number_format(($rui_all_uri / $tani), $keta);
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='カプラ売上高'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_c_uri) < 1) {
    $rui_c_uri = 0;                     // 検索失敗
} else {
    $rui_c_uri = $rui_c_uri - $rui_sc_uri_sagaku;                   // カプラ試験修理を加味
    $rui_c_uri = number_format(($rui_c_uri / $tani), $keta);
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='リニア売上高'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_l_uri) < 1) {
    $rui_l_uri = 0 - $rui_s_uri_sagaku;     // 検索失敗
} else {
    $rui_l_uri = $rui_l_uri - $rui_s_uri_sagaku;
    if ($yyyymm == 200905) {
        $rui_l_uri = $rui_l_uri + 3100900;
    } elseif ($yyyymm == 200904) {
        $rui_l_uri = $rui_l_uri + 1550450;
    }
    if ($yyyymm >= 201004 && $yyyymm <= 201103) {
        $rui_l_uri = $rui_l_uri - 255240;
    }
    $rui_l_uri = number_format(($rui_l_uri / $tani), $keta);
}

/********** 期首材料仕掛品棚卸高 **********/
    ///// 商管
$p2_b_invent  = 0;
$p1_b_invent  = 0;
$b_invent     = 0;
$rui_b_invent = 0;
    ///// 試験・修理
$p2_s_invent  = 0;
$p1_s_invent  = 0;
$s_invent     = 0;
$rui_s_invent = 0;
    ///// 当月
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='全体期首棚卸高'", $yyyymm);
if (getUniResult($query, $all_invent) < 1) {
    $all_invent = 0;                        // 検索失敗
} else {
    $all_invent = number_format(($all_invent / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ期首棚卸高'", $yyyymm);
if (getUniResult($query, $c_invent) < 1) {
    $c_invent = 0;                          // 検索失敗
} else {
    $c_invent = number_format(($c_invent / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア期首棚卸高'", $yyyymm);
if (getUniResult($query, $l_invent) < 1) {
    $l_invent = 0;                          // 検索失敗
} else {
    $l_invent = number_format(($l_invent / $tani), $keta);
}
    ///// 前月
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='全体期首棚卸高'", $p1_ym);
if (getUniResult($query, $p1_all_invent) < 1) {
    $p1_all_invent = 0;                     // 検索失敗
} else {
    $p1_all_invent = number_format(($p1_all_invent / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ期首棚卸高'", $p1_ym);
if (getUniResult($query, $p1_c_invent) < 1) {
    $p1_c_invent = 0;                       // 検索失敗
} else {
    $p1_c_invent = number_format(($p1_c_invent / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア期首棚卸高'", $p1_ym);
if (getUniResult($query, $p1_l_invent) < 1) {
    $p1_l_invent = 0;                       // 検索失敗
} else {
    $p1_l_invent = number_format(($p1_l_invent / $tani), $keta);
}
    ///// 前前月
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='全体期首棚卸高'", $p2_ym);
if (getUniResult($query, $p2_all_invent) < 1) {
    $p2_all_invent = 0;                     // 検索失敗
} else {
    $p2_all_invent = number_format(($p2_all_invent / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ期首棚卸高'", $p2_ym);
if (getUniResult($query, $p2_c_invent) < 1) {
    $p2_c_invent = 0;                       // 検索失敗
} else {
    $p2_c_invent = number_format(($p2_c_invent / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア期首棚卸高'", $p2_ym);
if (getUniResult($query, $p2_l_invent) < 1) {
    $p2_l_invent = 0;                       // 検索失敗
} else {
    $p2_l_invent = number_format(($p2_l_invent / $tani), $keta);
}
    ///// 今期累計
    /////   期首棚卸高の累計は 期初年月の期首棚卸高になる
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='全体期首棚卸高'", $str_ym);
if (getUniResult($query, $rui_all_invent) < 1) {
    $rui_all_invent = 0;                    // 検索失敗
} else {
    $rui_all_invent = number_format(($rui_all_invent / $tani), $keta);
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym=%d and note='カプラ期首棚卸高'", $str_ym);
if (getUniResult($query, $rui_c_invent) < 1) {
    $rui_c_invent = 0;                      // 検索失敗
} else {
    $rui_c_invent = number_format(($rui_c_invent / $tani), $keta);
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym=%d and note='リニア期首棚卸高'", $str_ym);
if (getUniResult($query, $rui_l_invent) < 1) {
    $rui_l_invent = 0;                      // 検索失敗
} else {
    $rui_l_invent = number_format(($rui_l_invent / $tani), $keta);
}

/********** 材料費(仕入高) **********/
    ///// 商管
$p2_b_metarial  = 0;
$p1_b_metarial  = 0;
$b_metarial     = 0;
$rui_b_metarial = 0;
    ///// 当月
if ( $yyyymm >= 200911) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ試修材料費'", $yyyymm);
    if (getUniResult($query, $sc_metarial) < 1) {
        $sc_metarial        = 0;            // 検索失敗
        $sc_metarial_sagaku = 0;
        $sc_metarial_temp   = 0;
    } else {
        $sc_metarial_temp   = $sc_metarial;
        $sc_metarial_sagaku = $sc_metarial;
        $sc_metarial        = number_format(($sc_metarial / $tani), $keta);
    }
} else{
    $sc_metarial        = 0;                // 検索失敗
    $sc_metarial_sagaku = 0;
    $sc_metarial_temp   = 0;
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修材料費'", $yyyymm);
if (getUniResult($query, $s_metarial) < 1) {
    $s_metarial        = 0;                 // 検索失敗
    $s_metarial_sagaku = 0;
} else {
    $s_metarial_sagaku = $s_metarial;
    $s_metarial        = $s_metarial + $sc_metarial_sagaku;             // カプラ試修材料費を加味（sagakuの下 リニアからマイナスしてしまう為）
    $s_metarial        = number_format(($s_metarial / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='全体材料費'", $yyyymm);
if (getUniResult($query, $all_metarial) < 1) {
    $all_metarial = 0;                      // 検索失敗
} else {
    // 2013/11/07 追加 2013年10月度 業務委託費（横川派遣料）調整
    if ($yyyymm == 201310) {
        $all_metarial -= 1245035;
    }
    if ($yyyymm == 201311) {
        $all_metarial += 1245035;
    }
    $all_metarial = number_format(($all_metarial / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ材料費'", $yyyymm);
if (getUniResult($query, $c_metarial) < 1) {
    $c_metarial = 0;                        // 検索失敗
} else {
    $c_metarial = $c_metarial - $sc_metarial_sagaku;                    // カプラ試修材料費を加味
    // 2013/11/07 追加 2013年10月度 業務委託費（横川派遣料）調整
    if ($yyyymm == 201310) {
        $c_metarial -= 1245035;
    }
    if ($yyyymm == 201311) {
        $c_metarial += 1245035;
    }
    $c_metarial = number_format(($c_metarial / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア材料費'", $yyyymm);
if (getUniResult($query, $l_metarial) < 1) {
    $l_metarial = 0 - $s_metarial_sagaku;   // 検索失敗
} else {
    $l_metarial = $l_metarial - $s_metarial_sagaku;
    $l_metarial = number_format(($l_metarial / $tani), $keta);
}
    ///// 前月
if ( $yyyymm >= 200911) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ試修材料費'", $p1_ym);
    if (getUniResult($query, $p1_sc_metarial) < 1) {
        $p1_sc_metarial        = 0;         // 検索失敗
        $p1_sc_metarial_sagaku = 0;
        $p1_sc_metarial_temp   = 0;
    } else {
        $p1_sc_metarial_temp   = $p1_sc_metarial;
        $p1_sc_metarial_sagaku = $p1_sc_metarial;
        $p1_sc_metarial        = number_format(($p1_sc_metarial / $tani), $keta);
    }
} else{
    $p1_sc_metarial        = 0;             // 検索失敗
    $p1_sc_metarial_sagaku = 0;
    $p1_sc_metarial_temp   = 0;
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修材料費'", $p1_ym);
if (getUniResult($query, $p1_s_metarial) < 1) {
    $p1_s_metarial        = 0;              // 検索失敗
    $p1_s_metarial_sagaku = 0;
} else {
    $p1_s_metarial_sagaku = $p1_s_metarial;
    $p1_s_metarial        = $p1_s_metarial + $p1_sc_metarial_sagaku;            // カプラ試修材料費を加味（sagakuの下 リニアからマイナスしてしまう為）
    $p1_s_metarial        = number_format(($p1_s_metarial / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='全体材料費'", $p1_ym);
if (getUniResult($query, $p1_all_metarial) < 1) {
    $p1_all_metarial = 0;                   // 検索失敗
} else {
    $p1_all_metarial = number_format(($p1_all_metarial / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ材料費'", $p1_ym);
if (getUniResult($query, $p1_c_metarial) < 1) {
    $p1_c_metarial = 0;                     // 検索失敗
} else {
    $p1_c_metarial = $p1_c_metarial - $p1_sc_metarial_sagaku;                   // カプラ試修材料費を加味
    // 2013/11/07 追加 2013年10月度 業務委託費（横川派遣料）調整
    if ($p1_ym == 201310) {
        $p1_c_metarial -= 1245035;
    }
    if ($p1_ym == 201311) {
        $p1_c_metarial += 1245035;
    }
    $p1_c_metarial = number_format(($p1_c_metarial / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア材料費'", $p1_ym);
if (getUniResult($query, $p1_l_metarial) < 1) {
    $p1_l_metarial = 0 - $p1_s_metarial_sagaku;     // 検索失敗
} else {
    $p1_l_metarial = $p1_l_metarial - $p1_s_metarial_sagaku;
    $p1_l_metarial = number_format(($p1_l_metarial / $tani), $keta);
}
    ///// 前前月
if ( $yyyymm >= 200911) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ試修材料費'", $p2_ym);
    if (getUniResult($query, $p2_sc_metarial) < 1) {
        $p2_sc_metarial        = 0;         // 検索失敗
        $p2_sc_metarial_sagaku = 0;
        $p2_sc_metarial_temp   = 0;
    } else {
        $p2_sc_metarial_temp   = $p2_sc_metarial;
        $p2_sc_metarial_sagaku = $p2_sc_metarial;
        $p2_sc_metarial        = number_format(($p2_sc_metarial / $tani), $keta);
    }
} else{
    $p2_sc_metarial        = 0;             // 検索失敗
    $p2_sc_metarial_sagaku = 0;
    $p2_sc_metarial_temp   = 0;
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修材料費'", $p2_ym);
if (getUniResult($query, $p2_s_metarial) < 1) {
    $p2_s_metarial        = 0;              // 検索失敗
    $p2_s_metarial_sagaku = 0;
} else {
    $p2_s_metarial_sagaku = $p2_s_metarial;
    $p2_s_metarial        = $p2_s_metarial + $p2_sc_metarial_sagaku;        // カプラ試修材料費を加味（sagakuの下 リニアからマイナスしてしまう為）
    $p2_s_metarial        = number_format(($p2_s_metarial / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='全体材料費'", $p2_ym);
if (getUniResult($query, $p2_all_metarial) < 1) {
    $p2_all_metarial = 0;                   // 検索失敗
} else {
    $p2_all_metarial = number_format(($p2_all_metarial / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ材料費'", $p2_ym);
if (getUniResult($query, $p2_c_metarial) < 1) {
    $p2_c_metarial = 0;                     // 検索失敗
} else {
    $p2_c_metarial = $p2_c_metarial - $p2_sc_metarial_sagaku;               // カプラ試修材料費を加味
    // 2013/11/07 追加 2013年10月度 業務委託費（横川派遣料）調整
    if ($p2_ym == 201310) {
        $p2_c_metarial -= 1245035;
    }
    if ($p2_ym == 201311) {
        $p2_c_metarial += 1245035;
    }
    $p2_c_metarial = number_format(($p2_c_metarial / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア材料費'", $p2_ym);
if (getUniResult($query, $p2_l_metarial) < 1) {
    $p2_l_metarial = 0 - $p2_s_metarial_sagaku;     // 検索失敗
} else {
    $p2_l_metarial = $p2_l_metarial - $p2_s_metarial_sagaku;
    $p2_l_metarial = number_format(($p2_l_metarial / $tani), $keta);
}
    ///// 今期累計
if ( $yyyymm >= 200911) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='カプラ試修材料費'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_sc_metarial) < 1) {
        $rui_sc_metarial        = 0;        // 検索失敗
        $rui_sc_metarial_sagaku = 0;
        $rui_sc_metarial_temp   = 0;
    } else {
        $rui_sc_metarial_temp   = $rui_sc_metarial;
        $rui_sc_metarial_sagaku = $rui_sc_metarial;
        $rui_sc_metarial        = number_format(($rui_sc_metarial / $tani), $keta);
    }
} else{
    $rui_sc_metarial        = 0;            // 検索失敗
    $rui_sc_metarial_sagaku = 0;
    $rui_sc_metarial_temp   = 0;
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='試修材料費'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_s_metarial) < 1) {
    $rui_s_metarial        = 0;             // 検索失敗
    $rui_s_metarial_sagaku = 0;
} else {
    $rui_s_metarial_sagaku = $rui_s_metarial;
    $rui_s_metarial        = $rui_s_metarial + $rui_sc_metarial_sagaku;         // カプラ試修材料費を加味（sagakuの下 リニアからマイナスしてしまう為）
    $rui_s_metarial        = number_format(($rui_s_metarial / $tani), $keta);
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='全体材料費'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_all_metarial) < 1) {
    $rui_all_metarial = 0;                  // 検索失敗
} else {
    // 2013/11/07 追加 2013年10月度 業務委託費（横川派遣料）調整
    if ($yyyymm >= 201310 && $yyyymm <= 201403) {
        $rui_all_metarial -= 1245035;
    }
    if ($yyyymm >= 201311 && $yyyymm <= 201403) {
        $rui_all_metarial += 1245035;
    }
    $rui_all_metarial = number_format(($rui_all_metarial / $tani), $keta);
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='カプラ材料費'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_c_metarial) < 1) {
    $rui_c_metarial = 0;                    // 検索失敗
} else {
    // 2013/11/07 追加 2013年10月度 業務委託費（横川派遣料）調整
    if ($yyyymm >= 201310 && $yyyymm <= 201403) {
        $rui_c_metarial -= 1245035;
    }
    if ($yyyymm >= 201311 && $yyyymm <= 201403) {
        $rui_c_metarial += 1245035;
    }
    $rui_c_metarial = $rui_c_metarial - $rui_sc_metarial_sagaku;                // カプラ試修材料費を加味
    $rui_c_metarial = number_format(($rui_c_metarial / $tani), $keta);
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='リニア材料費'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_l_metarial) < 1) {
    $rui_l_metarial = 0 - $rui_s_metarial_sagaku;       // 検索失敗
} else {
    $rui_l_metarial = $rui_l_metarial - $rui_s_metarial_sagaku;
    $rui_l_metarial = number_format(($rui_l_metarial / $tani), $keta);
}

/********** 労務費 **********/
    ///// 当月
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修給与配賦額'", $yyyymm);
    if (getUniResult($query, $s_kyu_kei) < 1) {
        $s_kyu_kei = 0;                    // 検索失敗
        $s_kyu_kin = 0;
    } else {
        $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修給与配賦率'", $yyyymm);
        if (getUniResult($query, $s_kyu_kin) < 1) {
            $s_kyu_kin = 0;
        }
    }
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修労務費'", $yyyymm);
if (getUniResult($query, $s_roumu) < 1) {
    $s_roumu        = 0;                    // 検索失敗
    $s_roumu_sagaku = 0;
} else {
    $s_roumu_sagaku = $s_roumu;
    if ($yyyymm == 200912) {
        $s_roumu = $s_roumu - 1409708;
    }
    if ($yyyymm >= 201001) {
        $s_roumu = $s_roumu - $s_kyu_kei + $s_kyu_kin;    // 試修配賦給与を加味
        //$s_roumu = $s_roumu - 432323 + 129697;  // 添田さんの給与をC・Lは35%。試験修理に30%振分
    }
    $s_roumu        = number_format(($s_roumu / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='全体労務費'", $yyyymm);
if (getUniResult($query, $all_roumu) < 1) {
    $all_roumu = 0;                         // 検索失敗
} else {
    $all_roumu = number_format(($all_roumu / $tani), $keta);
}
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ給与配賦率'", $yyyymm);
    if (getUniResult($query, $c_kyu_kin) < 1) {
        $c_kyu_kin = 0;
    }
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ労務費'", $yyyymm);
if (getUniResult($query, $c_roumu) < 1) {
    $c_roumu = 0;                           // 検索失敗
} else {
    if ($yyyymm == 200912) {
        $c_roumu = $c_roumu + 1227429;
    }
    if ($yyyymm >= 201001) {
        $c_roumu = $c_roumu + $c_kyu_kin;   // カプラ配賦給与を加味
        //$c_roumu = $c_roumu + 151313;   // 添田さんの給与をC・Lは35%。試験修理に30%振分
    }
    //$c_roumu = number_format(($c_roumu / $tani), $keta);
}
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア給与配賦率'", $yyyymm);
    if (getUniResult($query, $l_kyu_kin) < 1) {
        $l_kyu_kin = 0;
    }
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア労務費'", $yyyymm);
if (getUniResult($query, $l_roumu) < 1) {
    $l_roumu = 0 - $s_roumu_sagaku;         // 検索失敗
} else {
    if ($yyyymm == 200912) {
        $l_roumu = $l_roumu + 182279;
    }
    if ($yyyymm >= 201001) {
        $l_roumu = $l_roumu + $l_kyu_kin;   // リニア配賦給与を加味
        //$l_roumu = $l_roumu + 151313;   // 添田さんの給与をC・Lは35%。試験修理に30%振分
    }
    if ($yyyymm == 201408) {
        $l_roumu = $l_roumu + 229464;
    }
    $l_roumu = $l_roumu - $s_roumu_sagaku;
    $l_roumu = number_format(($l_roumu / $tani), $keta);
}
// 下は7月未払い給与分
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='商管労務費'", $yyyymm);
if (getUniResult($query, $b_roumu_sagaku) < 1) {
    $b_roumu_sagaku = 0;                    // 検索失敗
}
    ///// 当月 商管
$query = sprintf("select sum(orign_kin) from act_allo_history where pl_bs_ym=%d and actcod>=8101 and orign_id=580", $yyyymm);
if (getUniResult($query, $b_roumu) < 1) {
    $b_roumu  = 0 + $b_roumu_sagaku;        // 検索失敗
    $b_urigen = $b_roumu;
    $b_sagaku = $b_roumu;                   // カプラ差額計算用
} else {
    $b_roumu  = $b_roumu + $b_roumu_sagaku;
    $b_urigen = $b_roumu;
    $c_roumu  = $c_roumu - $b_roumu;        // カプラ労務費－商管労務費
    if ($yyyymm == 201408) {
        $c_roumu = $c_roumu + 611904;
        $b_roumu = $b_roumu - 841368;
    }
    $b_sagaku = $b_roumu;                   // カプラ差額計算用
    $c_roumu  = number_format(($c_roumu / $tani), $keta);
    $b_roumu  = number_format(($b_roumu / $tani), $keta);
}

    ///// 前月
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修給与配賦額'", $p1_ym);
    if (getUniResult($query, $p1_s_kyu_kei) < 1) {
        $p1_s_kyu_kei = 0;                    // 検索失敗
        $p1_s_kyu_kin = 0;
    } else {
        $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修給与配賦率'", $p1_ym);
        if (getUniResult($query, $p1_s_kyu_kin) < 1) {
            $p1_s_kyu_kin = 0;
        }
    }
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修労務費'", $p1_ym);
if (getUniResult($query, $p1_s_roumu) < 1) {
    $p1_s_roumu        = 0;                 // 検索失敗
    $p1_s_roumu_sagaku = 0;
} else {
    $p1_s_roumu_sagaku = $p1_s_roumu;
    if ($p1_ym == 200912) {
        $p1_s_roumu = $p1_s_roumu - 1409708;
    }
    if ($p1_ym >= 201001) {
        $p1_s_roumu = $p1_s_roumu - $p1_s_kyu_kei + $p1_s_kyu_kin;    // 試修配賦給与を加味
        //$p1_s_roumu = $p1_s_roumu - 432323 + 129697;    // 添田さんの給与をC・Lは35%。試験修理に30%振分
    }
    $p1_s_roumu        = number_format(($p1_s_roumu / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='商管労務費'", $p1_ym);
if (getUniResult($query, $p1_b_roumu_sagaku) < 1) {
    $p1_b_roumu_sagaku = 0;                 // 検索失敗
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='全体労務費'", $p1_ym);
if (getUniResult($query, $p1_all_roumu) < 1) {
    $p1_all_roumu = 0;                      // 検索失敗
} else {
    $p1_all_roumu = number_format(($p1_all_roumu / $tani), $keta);
}
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ給与配賦率'", $p1_ym);
    if (getUniResult($query, $p1_c_kyu_kin) < 1) {
        $p1_c_kyu_kin = 0;
    }
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ労務費'", $p1_ym);
if (getUniResult($query, $p1_c_roumu) < 1) {
    $p1_c_roumu = 0;                        // 検索失敗
} else {
    if ($p1_ym == 200912) {
        $p1_c_roumu = $p1_c_roumu + 1227429;
    }
    if ($p1_ym >= 201001) {
        $p1_c_roumu = $p1_c_roumu + $p1_c_kyu_kin;   // カプラ配賦給与を加味
        //$p1_c_roumu = $p1_c_roumu + 151313; // 添田さんの給与をC・Lは35%。試験修理に30%振分
    }
    //$p1_c_roumu = number_format(($p1_c_roumu / $tani), $keta);
}
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア給与配賦率'", $p1_ym);
    if (getUniResult($query, $p1_l_kyu_kin) < 1) {
        $p1_l_kyu_kin = 0;
    }
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア労務費'", $p1_ym);
if (getUniResult($query, $p1_l_roumu) < 1) {
    $p1_l_roumu = 0 - $p1_s_roumu_sagaku;   // 検索失敗
} else {
    if ($p1_ym == 200912) {
        $p1_l_roumu = $p1_l_roumu + 182279;
    }
    if ($p1_ym >= 201001) {
        $p1_l_roumu = $p1_l_roumu + $p1_l_kyu_kin;   // リニア配賦給与を加味
        //$p1_l_roumu = $p1_l_roumu + 151313; // 添田さんの給与をC・Lは35%。試験修理に30%振分
    }
    if ($p1_ym == 201408) {
        $p1_l_roumu = $p1_l_roumu + 229464;
    }
    $p1_l_roumu = $p1_l_roumu - $p1_s_roumu_sagaku;
    $p1_l_roumu = number_format(($p1_l_roumu / $tani), $keta);
}
    ///// 前月 商管
$query = sprintf("select sum(orign_kin) from act_allo_history where pl_bs_ym=%d and actcod>=8101 and orign_id=580", $p1_ym);
if (getUniResult($query, $p1_b_roumu) < 1) {
    $p1_b_roumu  = 0 + $p1_b_roumu_sagaku;      // 検索失敗
    $p1_b_urigen = $p1_b_roumu;
    $p1_b_sagaku = $p1_b_roumu;                 // カプラ差額計算用
} else {
    $p1_b_roumu  = $p1_b_roumu + $p1_b_roumu_sagaku;
    $p1_b_urigen = $p1_b_roumu;
    $p1_c_roumu  = $p1_c_roumu - $p1_b_roumu;   // カプラ労務費－商管労務費
    if ($p1_ym == 201408) {
        $p1_c_roumu = $p1_c_roumu + 611904;
        $p1_b_roumu = $p1_b_roumu - 841368;
    }
    $p1_b_sagaku = $p1_b_roumu;                 // カプラ差額計算用
    $p1_c_roumu  = number_format(($p1_c_roumu / $tani), $keta);
    $p1_b_roumu  = number_format(($p1_b_roumu / $tani), $keta);
}

    ///// 前前月
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修給与配賦額'", $p2_ym);
    if (getUniResult($query, $p2_s_kyu_kei) < 1) {
        $p2_s_kyu_kei = 0;                    // 検索失敗
        $p2_s_kyu_kin = 0;
    } else {
        $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修給与配賦率'", $p2_ym);
        if (getUniResult($query, $p2_s_kyu_kin) < 1) {
            $p2_s_kyu_kin = 0;
        }
    }
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修労務費'", $p2_ym);
if (getUniResult($query, $p2_s_roumu) < 1) {
    $p2_s_roumu        = 0;                     // 検索失敗
    $p2_s_roumu_sagaku = 0;
} else {
    $p2_s_roumu_sagaku = $p2_s_roumu;
    if ($p2_ym == 200912) {
        $p2_s_roumu = $p2_s_roumu - 1409708;
    }
    if ($p2_ym >= 201001) {
        $p2_s_roumu = $p2_s_roumu - $p2_s_kyu_kei + $p2_s_kyu_kin;    // 試修配賦給与を加味
        //$p2_s_roumu = $p2_s_roumu - 432323 + 129697;    // 添田さんの給与をC・Lは35%。試験修理に30%振分
    }
    $p2_s_roumu        = number_format(($p2_s_roumu / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='商管労務費'", $p2_ym);
if (getUniResult($query, $p2_b_roumu_sagaku) < 1) {
    $p2_b_roumu_sagaku = 0;                     // 検索失敗
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='全体労務費'", $p2_ym);
if (getUniResult($query, $p2_all_roumu) < 1) {
    $p2_all_roumu = 0;                          // 検索失敗
} else {
    $p2_all_roumu = number_format(($p2_all_roumu / $tani), $keta);
}
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ給与配賦率'", $p2_ym);
    if (getUniResult($query, $p2_c_kyu_kin) < 1) {
        $p2_c_kyu_kin = 0;
    }
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ労務費'", $p2_ym);
if (getUniResult($query, $p2_c_roumu) < 1) {
    $p2_c_roumu = 0;                            // 検索失敗
} else {
    if ($p2_ym == 200912) {
        $p2_c_roumu = $p2_c_roumu + 1227429;
    }
    if ($p2_ym >= 201001) {
        $p2_c_roumu = $p2_c_roumu + $p2_c_kyu_kin;   // カプラ配賦給与を加味
        //$p2_c_roumu = $p2_c_roumu + 151313;    // 添田さんの給与をC・Lは35%。試験修理に30%振分
    }
    //$p2_c_roumu = number_format(($p2_c_roumu / $tani), $keta);
}
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア給与配賦率'", $p2_ym);
    if (getUniResult($query, $p2_l_kyu_kin) < 1) {
        $p2_l_kyu_kin = 0;
    }
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア労務費'", $p2_ym);
if (getUniResult($query, $p2_l_roumu) < 1) {
    $p2_l_roumu = 0 - $p2_s_roumu_sagaku;       // 検索失敗
} else {
    if ($p2_ym == 200912) {
        $p2_l_roumu = $p2_l_roumu + 182279;
    }
    if ($p2_ym >= 201001) {
        $p2_l_roumu = $p2_l_roumu + $p2_l_kyu_kin;   // リニア配賦給与を加味
        //$p2_l_roumu = $p2_l_roumu + 151313;     // 添田さんの給与をC・Lは35%。試験修理に30%振分
    }
    if ($p2_ym == 201408) {
        $p2_l_roumu = $p2_l_roumu + 229464;
    }
    $p2_l_roumu = $p2_l_roumu - $p2_s_roumu_sagaku;
    $p2_l_roumu = number_format(($p2_l_roumu / $tani), $keta);
}
    ///// 前前月 商管
$query = sprintf("select sum(orign_kin) from act_allo_history where pl_bs_ym=%d and actcod>=8101 and orign_id=580", $p2_ym);
if (getUniResult($query, $p2_b_roumu) < 1) {
    $p2_b_roumu  = 0 + $p2_b_roumu_sagaku;      // 検索失敗
    $p2_b_urigen = $p2_b_roumu;
    $p2_b_sagaku = $p2_b_roumu;                 // カプラ差額計算用
} else {
    $p2_b_roumu  = $p2_b_roumu + $p2_b_roumu_sagaku;
    $p2_b_urigen = $p2_b_roumu;
    $p2_c_roumu  = $p2_c_roumu - $p2_b_roumu;   // カプラ労務費－商管労務費
    if ($p2_ym == 201408) {
        $p2_c_roumu = $p2_c_roumu + 611904;
        $p2_b_roumu = $p2_b_roumu - 841368;
    }
    $p2_b_sagaku = $p2_b_roumu;                 // カプラ差額計算用
    $p2_c_roumu  = number_format(($p2_c_roumu / $tani), $keta);
    $p2_b_roumu  = number_format(($p2_b_roumu / $tani), $keta);
}

    ///// 今期累計
if ($yyyymm >= 201001) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='試修給与配賦額'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_s_kyu_kei) < 1) {
        $rui_s_kyu_kei = 0;                    // 検索失敗
        $rui_s_kyu_kin = 0;
    } else {
        $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='試修給与配賦率'", $str_ym, $yyyymm);
        if (getUniResult($query, $rui_s_kyu_kin) < 1) {
            $rui_s_kyu_kin = 0;
        }
    }
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='試修労務費'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_s_roumu) < 1) {
    $rui_s_roumu        = 0;                    // 検索失敗
    $rui_s_roumu_sagaku = 0;
} else {
    $rui_s_roumu_sagaku = $rui_s_roumu;
    if ($yyyymm >= 200912 && $yyyymm <= 201003) {
        $rui_s_roumu = $rui_s_roumu - 1409708;
    }
    if ($yyyymm >= 201001) {
        $rui_s_roumu = $rui_s_roumu - $rui_s_kyu_kei + $rui_s_kyu_kin;    // 試修配賦給与を加味
        //$rui_s_roumu = $rui_s_roumu - 432323 + 129697;  // 添田さんの給与をC・Lは35%。試験修理に30%振分
    }
    $rui_s_roumu        = number_format(($rui_s_roumu / $tani), $keta);
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='商管労務費'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_b_roumu_sagaku) < 1) {
    $rui_b_roumu_sagaku = 0;                    // 検索失敗
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='全体労務費'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_all_roumu) < 1) {
    $rui_all_roumu = 0;                         // 検索失敗
} else {
    $rui_all_roumu = number_format(($rui_all_roumu / $tani), $keta);
}
if ($yyyymm >= 201001) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='カプラ給与配賦率'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_c_kyu_kin) < 1) {
        $rui_c_kyu_kin = 0;
    }
}

$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='カプラ労務費'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_c_roumu) < 1) {
    $rui_c_roumu = 0;                           // 検索失敗
} else {
    if ($yyyymm >= 200912 && $yyyymm <= 201003) {
        $rui_c_roumu = $rui_c_roumu + 1227429;
    }
    if ($yyyymm >= 201001) {
        $rui_c_roumu = $rui_c_roumu + $rui_c_kyu_kin;   // カプラ配賦給与を加味
        //$rui_c_roumu = $rui_c_roumu + 151313;   // 添田さんの給与をC・Lは35%。試験修理に30%振分
    }
    //$rui_c_roumu = number_format(($rui_c_roumu / $tani), $keta);
}
if ($yyyymm >= 201001) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='リニア給与配賦率'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_l_kyu_kin) < 1) {
        $rui_l_kyu_kin = 0;
    }
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='リニア労務費'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_l_roumu) < 1) {
    $rui_l_roumu = 0 - $rui_s_roumu_sagaku;     // 検索失敗
} else {
    $rui_l_roumu = $rui_l_roumu - $rui_s_roumu_sagaku;
    if ($yyyymm >= 200912 && $yyyymm <= 201003) {
        $rui_l_roumu = $rui_l_roumu + 182279;
    }
    if ($yyyymm >= 201001) {
        $rui_l_roumu = $rui_l_roumu + $rui_l_kyu_kin;   // リニア配賦給与を加味
        //$rui_l_roumu = $rui_l_roumu + 151313;   // 添田さんの給与をC・Lは35%。試験修理に30%振分
    }
    if ($yyyymm >= 201408 && $yyyymm <= 201503) {
        $rui_l_roumu = $rui_l_roumu + 229464;
    }
    $rui_l_roumu = number_format(($rui_l_roumu / $tani), $keta);
}
    ///// 今期累計 商管
$query = sprintf("select sum(orign_kin) from act_allo_history where pl_bs_ym>=%d and pl_bs_ym<=%d and actcod>=8101 and orign_id=580", $str_ym, $yyyymm);
if (getUniResult($query, $rui_b_roumu) < 1) {
    $rui_b_roumu  = 0 + $rui_b_roumu_sagaku;        // 検索失敗
    $rui_b_urigen = $rui_b_roumu;
} else {
    // 下は7月未払い旧余分追加 テスト用
    $rui_b_roumu  = $rui_b_roumu + $rui_b_roumu_sagaku;
    $rui_b_urigen = $rui_b_roumu;
    $rui_c_roumu  = $rui_c_roumu - $rui_b_roumu;    // カプラ労務費－商管労務費
    if ($yyyymm >= 201408 && $yyyymm <= 201503) {
        $rui_c_roumu = $rui_c_roumu + 611904;
        $rui_b_roumu = $rui_b_roumu - 841368;
    }
    $rui_b_sagaku = $rui_b_roumu;                   // カプラ差額計算用
    $rui_c_roumu  = number_format(($rui_c_roumu / $tani), $keta);
    $rui_b_roumu  = number_format(($rui_b_roumu / $tani), $keta);
}

/********** 経費(製造経費) **********/
    ///// 当月
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修製造経費'", $yyyymm);
if (getUniResult($query, $s_expense) < 1) {
    $s_expense        = 0;                          // 検索失敗
    $s_expense_sagaku = 0;
} else {
    $s_expense_sagaku = $s_expense;
    $s_expense        = number_format(($s_expense / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='商管製造経費'", $yyyymm);
if (getUniResult($query, $b_expense_sagaku) < 1) {
    $b_expense_sagaku = 0;                          // 検索失敗
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='全体製造経費'", $yyyymm);
if (getUniResult($query, $all_expense) < 1) {
    $all_expense = 0;                               // 検索失敗
} else {
    // 2012/02/08 追加 2012年1月度 業務委託費（平出横川派遣料）調整
    if ($yyyymm == 201201) {
        $all_expense +=1156130;
    }
    // 2012/03/05 追加 2012年1月度 業務委託費（平出横川派遣料）調整 戻し
    if ($yyyymm == 201202) {
        $all_expense -=1156130;
    }
    // 2013/11/07 追加 2013年10月度 業務委託費（横川派遣料）調整
    if ($yyyymm == 201310) {
        $all_expense += 1245035;
    }
    if ($yyyymm == 201311) {
        $all_expense -= 1245035;
    }
    $all_expense = number_format(($all_expense / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ製造経費'", $yyyymm);
if (getUniResult($query, $c_expense) < 1) {
    $c_expense = 0;                                 // 検索失敗
} else {
    //$c_expense = number_format(($c_expense / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア製造経費'", $yyyymm);
if (getUniResult($query, $l_expense) < 1) {
    $l_expense = 0 - $s_expense_sagaku;             // 検索失敗
} else {
    // 2012/02/08 追加 2012年1月度 業務委託費（平出横川派遣料）調整
    if ($yyyymm == 201201) {
        $l_expense +=1156130;
    }
    // 2012/03/05 追加 2012年1月度 業務委託費（平出横川派遣料）調整 戻し
    if ($yyyymm == 201202) {
        $l_expense -=1156130;
    }
    $l_expense = $l_expense - $s_expense_sagaku;
    $l_expense = number_format(($l_expense / $tani), $keta);
}
    ///// 当月 商管
$query = sprintf("select sum(orign_kin) from act_allo_history where pl_bs_ym=%d and actcod>=7501 and actcod<=8000 and orign_id=580", $yyyymm);
if (getUniResult($query, $b_expense) < 1) {
    $b_expense = 0 + $b_roumu_sagaku;               // 検索失敗
    $b_urigen  = $b_urigen + $b_expense;
    $b_sagaku  = $b_sagaku + $b_expense;            // カプラ差額計算用
} else {
    $b_expense = $b_expense + $b_expense_sagaku;
    $b_urigen  = $b_urigen + $b_expense;
    $c_expense = $c_expense - $b_expense;           // カプラ製造経費－商管製造経費
    $b_sagaku  = $b_sagaku + $b_expense;            // カプラ差額計算用
    // 2013/11/07 追加 2013年10月度 業務委託費（横川派遣料）調整
    if ($yyyymm == 201310) {
        $b_expense += 1245035;
    }
    if ($yyyymm == 201311) {
        $b_expense -= 1245035;
    }
    $c_expense = number_format(($c_expense / $tani), $keta);
    $b_expense = number_format(($b_expense / $tani), $keta);
}

    ///// 前月
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修製造経費'", $p1_ym);
if (getUniResult($query, $p1_s_expense) < 1) {
    $p1_s_expense        = 0;                       // 検索失敗
    $p1_s_expense_sagaku = 0;
} else {
    $p1_s_expense_sagaku = $p1_s_expense;
    $p1_s_expense        = number_format(($p1_s_expense / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='商管製造経費'", $p1_ym);
if (getUniResult($query, $p1_b_expense_sagaku) < 1) {
    $p1_b_expense_sagaku = 0;                       // 検索失敗
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='全体製造経費'", $p1_ym);
if (getUniResult($query, $p1_all_expense) < 1) {
    $p1_all_expense = 0;                            // 検索失敗
} else {
    // 2012/02/08 追加 2012年1月度 業務委託費（平出横川派遣料）調整
    if ($p1_ym == 201201) {
        $p1_all_expense +=1156130;
    }
    // 2012/03/05 追加 2012年1月度 業務委託費（平出横川派遣料）調整 戻し
    if ($p1_ym == 201202) {
        $p1_all_expense -=1156130;
    }
    $p1_all_expense = number_format(($p1_all_expense / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ製造経費'", $p1_ym);
if (getUniResult($query, $p1_c_expense) < 1) {
    $p1_c_expense = 0;                              // 検索失敗
} else {
    //$p1_c_expense = number_format(($p1_c_expense / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア製造経費'", $p1_ym);
if (getUniResult($query, $p1_l_expense) < 1) {
    $p1_l_expense = 0 - $p1_s_expense_sagaku;       // 検索失敗
} else {
    // 2012/02/08 追加 2012年1月度 業務委託費（平出横川派遣料）調整
    if ($p1_ym == 201201) {
        $p1_l_expense +=1156130;
    }
    // 2012/03/05 追加 2012年1月度 業務委託費（平出横川派遣料）調整 戻し
    if ($p1_ym == 201202) {
        $p1_l_expense -=1156130;
    }
    $p1_l_expense = $p1_l_expense - $p1_s_expense_sagaku;
    $p1_l_expense = number_format(($p1_l_expense / $tani), $keta);
}
    ///// 前月 商管
$query = sprintf("select sum(orign_kin) from act_allo_history where pl_bs_ym=%d and actcod>=7501 and actcod<=8000 and orign_id=580", $p1_ym);
if (getUniResult($query, $p1_b_expense) < 1) {
    $p1_b_expense = 0 + $p1_b_expense_sagaku;       // 検索失敗
    $p1_b_urigen  = $p1_b_urigen + $p1_b_expense;
    $p1_b_sagaku  = $p1_b_sagaku + $p1_b_expense;   // カプラ差額計算用
} else {
    $p1_b_expense = $p1_b_expense + $p1_b_expense_sagaku;
    $p1_b_urigen  = $p1_b_urigen + $p1_b_expense;
    $p1_c_expense = $p1_c_expense - $p1_b_expense;  // カプラ製造経費－商管製造経費
    $p1_b_sagaku  = $p1_b_sagaku + $p1_b_expense;   // カプラ差額計算用
    // 2013/11/07 追加 2013年10月度 業務委託費（横川派遣料）調整
    if ($p1_ym == 201310) {
        $p1_b_expense += 1245035;
    }
    if ($p1_ym == 201311) {
        $p1_b_expense -= 1245035;
    }
    $p1_c_expense = number_format(($p1_c_expense / $tani), $keta);
    $p1_b_expense = number_format(($p1_b_expense / $tani), $keta);
}

    ///// 前前月
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修製造経費'", $p2_ym);
if (getUniResult($query, $p2_s_expense) < 1) {
    $p2_s_expense        = 0;                       // 検索失敗
    $p2_s_expense_sagaku = 0;
} else {
    $p2_s_expense_sagaku = $p2_s_expense;
    $p2_s_expense        = number_format(($p2_s_expense / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='商管製造経費'", $p2_ym);
if (getUniResult($query, $p2_b_expense_sagaku) < 1) {
    $p2_b_expense_sagaku = 0;                       // 検索失敗
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='全体製造経費'", $p2_ym);
if (getUniResult($query, $p2_all_expense) < 1) {
    $p2_all_expense = 0;                            // 検索失敗
} else {
    // 2012/02/08 追加 2012年1月度 業務委託費（平出横川派遣料）調整
    if ($p2_ym == 201201) {
        $p2_all_expense +=1156130;
    }
    // 2012/03/05 追加 2012年1月度 業務委託費（平出横川派遣料）調整 戻し
    if ($p2_ym == 201202) {
        $p2_all_expense -=1156130;
    }
    $p2_all_expense = number_format(($p2_all_expense / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ製造経費'", $p2_ym);
if (getUniResult($query, $p2_c_expense) < 1) {
    $p2_c_expense = 0;                              // 検索失敗
} else {
    //$p2_c_expense = number_format(($p2_c_expense / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア製造経費'", $p2_ym);
if (getUniResult($query, $p2_l_expense) < 1) {
    $p2_l_expense = 0 - $p2_s_expense_sagaku;       // 検索失敗
} else {
    // 2012/02/08 追加 2012年1月度 業務委託費（平出横川派遣料）調整
    if ($p2_ym == 201201) {
        $p2_l_expense +=1156130;
    }
    // 2012/03/05 追加 2012年1月度 業務委託費（平出横川派遣料）調整 戻し
    if ($p2_ym == 201202) {
        $p2_l_expense -=1156130;
    }
    $p2_l_expense = $p2_l_expense - $p2_s_expense_sagaku;
    $p2_l_expense = number_format(($p2_l_expense / $tani), $keta);
}
    ///// 前前月 商管
$query = sprintf("select sum(orign_kin) from act_allo_history where pl_bs_ym=%d and actcod>=7501 and actcod<=8000 and orign_id=580", $p2_ym);
if (getUniResult($query, $p2_b_expense) < 1) {
    $p2_b_expense = 0 + $p2_b_expense_sagaku;       // 検索失敗
    $p2_b_urigen  = $p2_b_urigen + $p2_b_expense;
    $p2_b_sagaku  = $p2_b_sagaku + $p2_b_expense;   // カプラ差額計算用
} else {
    $p2_b_expense = $p2_b_expense + $p2_b_expense_sagaku;
    $p2_b_urigen  = $p2_b_urigen + $p2_b_expense;
    $p2_c_expense = $p2_c_expense - $p2_b_expense;  // カプラ製造経費－商管製造経費
    $p2_b_sagaku  = $p2_b_sagaku + $p2_b_expense;   // カプラ差額計算用
    // 2013/11/07 追加 2013年10月度 業務委託費（横川派遣料）調整
    if ($p2_ym == 201310) {
        $p2_b_expense += 1245035;
    }
    if ($p2_ym == 201311) {
        $p2_b_expense -= 1245035;
    }
    $p2_c_expense = number_format(($p2_c_expense / $tani), $keta);
    $p2_b_expense = number_format(($p2_b_expense / $tani), $keta);
}

    ///// 今期累計
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='試修製造経費'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_s_expense) < 1) {
    $rui_s_expense        = 0;                      // 検索失敗
    $rui_s_expense_sagaku = 0;
} else {
    $rui_s_expense_sagaku = $rui_s_expense;
    $rui_s_expense        = number_format(($rui_s_expense / $tani), $keta);
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='商管製造経費'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_b_expense_sagaku) < 1) {
    $rui_b_expense_sagaku = 0;                      // 検索失敗
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='全体製造経費'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_all_expense) < 1) {
    $rui_all_expense = 0;                           // 検索失敗
} else {
    // 2012/02/08 追加 2012年1月度 業務委託費（平出横川派遣料）調整
    if ($yyyymm >= 201201 && $yyyymm <= 201203) {
        $rui_all_expense +=1156130;
    }
    // 2012/03/05 追加 2012年1月度 業務委託費（平出横川派遣料）調整 戻し
    if ($yyyymm >= 201202 && $yyyymm <= 201203) {
        $rui_all_expense -=1156130;
    }
    // 2013/11/07 追加 2013年10月度 業務委託費（横川派遣料）調整
    if ($yyyymm >= 201310 && $yyyymm <= 201403) {
        $rui_all_expense += 1245035;
    }
    if ($yyyymm >= 201311 && $yyyymm <= 201403) {
        $rui_all_expense -= 1245035;
    }
    $rui_all_expense = number_format(($rui_all_expense / $tani), $keta);
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='カプラ製造経費'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_c_expense) < 1) {
    $rui_c_expense = 0;                             // 検索失敗
} else {
    //$rui_c_expense = number_format(($rui_c_expense / $tani), $keta);
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='リニア製造経費'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_l_expense) < 1) {
    $rui_l_expense = 0 - $rui_s_expense_sagaku;     // 検索失敗
} else {
    // 2012/02/08 追加 2012年1月度 業務委託費（平出横川派遣料）調整
    if ($yyyymm >= 201201 && $yyyymm <= 201203) {
        $rui_l_expense +=1156130;
    }
    // 2012/03/05 追加 2012年1月度 業務委託費（平出横川派遣料）調整 戻し
    if ($yyyymm >= 201202 && $yyyymm <= 201203) {
        $rui_l_expense -=1156130;
    }
    $rui_l_expense = $rui_l_expense - $rui_s_expense_sagaku;
    $rui_l_expense = number_format(($rui_l_expense / $tani), $keta);
}
    ///// 今期累計 商管
$query = sprintf("select sum(orign_kin) from act_allo_history where pl_bs_ym>=%d and pl_bs_ym<=%d and actcod>=7501 and actcod<=8000 and orign_id=580", $str_ym, $yyyymm);
if (getUniResult($query, $rui_b_expense) < 1) {
    $rui_b_expense = 0 + $rui_b_expense_sagaku;     // 検索失敗
    $rui_b_urigen  = $rui_b_urigen + $rui_b_expense;
    $rui_b_sagaku  = $rui_b_sagaku + $rui_b_expense;    // カプラ差額計算用
} else {
    $rui_b_expense = $rui_b_expense + $rui_b_expense_sagaku;
    $rui_b_urigen  = $rui_b_urigen + $rui_b_expense;
    $rui_c_expense = $rui_c_expense - $rui_b_expense;   // カプラ製造経費－商管製造経費
    $rui_b_sagaku  = $rui_b_sagaku + $rui_b_expense;    // カプラ差額計算用
    // 2013/11/07 追加 2013年10月度 業務委託費（横川派遣料）調整
    if ($yyyymm >= 201310 && $yyyymm <= 201403) {
        $rui_b_expense += 1245035;
    }
    if ($yyyymm >= 201311 && $yyyymm <= 201403) {
        $rui_b_expense -= 1245035;
    }
    $rui_c_expense = number_format(($rui_c_expense / $tani), $keta);
    $rui_b_expense = number_format(($rui_b_expense / $tani), $keta);
}

/********** 期末材料仕掛品棚卸高 **********/
    ///// 商管
$p2_b_endinv = 0;
$p1_b_endinv = 0;
$b_endinv    = 0;
    ///// 試験・修理
$p2_s_endinv = 0;
$p1_s_endinv = 0;
$s_endinv    = 0;
    ///// 当月
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='全体期末棚卸高'", $yyyymm);
if (getUniResult($query, $all_endinv) < 1) {
    $all_endinv = 0;                                // 検索失敗
} else {
    $all_endinv = ($all_endinv * (-1));             // 符号反転
    $all_endinv = number_format(($all_endinv / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ期末棚卸高'", $yyyymm);
if (getUniResult($query, $c_endinv) < 1) {
    $c_endinv = 0;                                  // 検索失敗
} else {
    $c_endinv = ($c_endinv * (-1));                 // 符号反転
    $c_endinv = number_format(($c_endinv / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア期末棚卸高'", $yyyymm);
if (getUniResult($query, $l_endinv) < 1) {
    $l_endinv = 0;                                  // 検索失敗
} else {
    $l_endinv = ($l_endinv * (-1));                 // 符号反転
    $l_endinv = number_format(($l_endinv / $tani), $keta);
}
    ///// 前月
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='全体期末棚卸高'", $p1_ym);
if (getUniResult($query, $p1_all_endinv) < 1) {
    $p1_all_endinv = 0;                             // 検索失敗
} else {
    $p1_all_endinv = ($p1_all_endinv * (-1));       // 符号反転
    $p1_all_endinv = number_format(($p1_all_endinv / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ期末棚卸高'", $p1_ym);
if (getUniResult($query, $p1_c_endinv) < 1) {
    $p1_c_endinv = 0;                               // 検索失敗
} else {
    $p1_c_endinv = ($p1_c_endinv * (-1));           // 符号反転
    $p1_c_endinv = number_format(($p1_c_endinv / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア期末棚卸高'", $p1_ym);
if (getUniResult($query, $p1_l_endinv) < 1) {
    $p1_l_endinv = 0;                               // 検索失敗
} else {
    $p1_l_endinv = ($p1_l_endinv * (-1));           // 符号反転
    $p1_l_endinv = number_format(($p1_l_endinv / $tani), $keta);
}
    ///// 前前月
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='全体期末棚卸高'", $p2_ym);
if (getUniResult($query, $p2_all_endinv) < 1) {
    $p2_all_endinv = 0;                             // 検索失敗
} else {
    $p2_all_endinv = ($p2_all_endinv * (-1));       // 符号反転
    $p2_all_endinv = number_format(($p2_all_endinv / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ期末棚卸高'", $p2_ym);
if (getUniResult($query, $p2_c_endinv) < 1) {
    $p2_c_endinv = 0;                               // 検索失敗
} else {
    $p2_c_endinv = ($p2_c_endinv * (-1));           // 符号反転
    $p2_c_endinv = number_format(($p2_c_endinv / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア期末棚卸高'", $p2_ym);
if (getUniResult($query, $p2_l_endinv) < 1) {
    $p2_l_endinv = 0;                               // 検索失敗
} else {
    $p2_l_endinv = ($p2_l_endinv * (-1));           // 符号反転
    $p2_l_endinv = number_format(($p2_l_endinv / $tani), $keta);
}
    ///// 今期累計
    ///// 期末棚卸高の累計は当月と同じ

/********** 売上原価 **********/
    ///// 当月
    ///// 試験・修理
    $s_urigen        = $s_invent + $s_metarial_sagaku + $s_roumu_sagaku + $s_expense_sagaku + $s_endinv;
    $s_urigen_sagaku = $s_urigen;
    $s_urigen        = $s_urigen + $sc_metarial_sagaku;         // カプラ試修材料費を加味（sagakuの下 リニアからマイナスしてしまう為）
    if ($yyyymm == 200912) {
        $s_urigen = $s_urigen - 1409708;
    }
    if ($yyyymm >= 201001) {
        $s_urigen = $s_urigen - $s_kyu_kei + $s_kyu_kin;    // 添田さんの給与をC・Lは35%。試験修理に30%振分
        //$s_urigen = $s_urigen - 432323 + 129697;    // 添田さんの給与をC・Lは35%。試験修理に30%振分
    }
    $s_urigen        = number_format(($s_urigen / $tani), $keta);
    ///// CL
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='全体売上原価'", $yyyymm);
if (getUniResult($query, $all_urigen) < 1) {
    $all_urigen = 0;                                // 検索失敗
} else {
    // 2012/02/08 追加 2012年1月度 業務委託費（平出横川派遣料）調整
    if ($yyyymm == 201201) {
        $all_urigen +=1156130;
    }
    // 2012/03/05 追加 2012年1月度 業務委託費（平出横川派遣料）調整 戻し
    if ($yyyymm == 201202) {
        $all_urigen -=1156130;
    }
    $all_urigen = number_format(($all_urigen / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ売上原価'", $yyyymm);
if (getUniResult($query, $c_urigen) < 1) {
    $c_urigen = 0;                                  // 検索失敗
} else {
    $c_urigen = $c_urigen - $b_urigen - $sc_metarial_sagaku;    // カプラ試修材料費も加味
    if ($yyyymm == 200912) {
        $c_urigen = $c_urigen + 1227429;
    }
    if ($yyyymm >= 201001) {
        $c_urigen = $c_urigen + $c_kyu_kin;     // 添田さんの給与をC・Lは35%。試験修理に30%振分
        //$c_urigen = $c_urigen + 151313;     // 添田さんの給与をC・Lは35%。試験修理に30%振分
    }
    // 2013/11/07 追加 2013年10月度 業務委託費（横川派遣料）調整
    if ($yyyymm == 201310) {
        $c_urigen -= 1245035;
    }
    if ($yyyymm == 201311) {
        $c_urigen += 1245035;
    }
    if ($yyyymm == 201408) {
        $c_urigen += 611904;
    }
    $c_urigen = number_format(($c_urigen / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア売上原価'", $yyyymm);
if (getUniResult($query, $l_urigen) < 1) {
    $l_urigen = 0 - $s_urigen_sagaku;               // 検索失敗
} else {
    $l_urigen = $l_urigen - $s_urigen_sagaku;
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
    $l_urigen = number_format(($l_urigen / $tani), $keta);
}

    ///// 前月
    ///// 試験・修理
    $p1_s_urigen        = $p1_s_invent + $p1_s_metarial_sagaku + $p1_s_roumu_sagaku + $p1_s_expense_sagaku + $p1_s_endinv;
    $p1_s_urigen_sagaku = $p1_s_urigen;
    $p1_s_urigen        = $p1_s_urigen + $p1_sc_metarial_sagaku;    // カプラ試修材料費を加味（sagakuの下 リニアからマイナスしてしまう為）
    if ($p1_ym == 200912) {
        $p1_s_urigen = $p1_s_urigen - 1409708;
    }
    if ($p1_ym >= 201001) {
        $p1_s_urigen = $p1_s_urigen - $p1_s_kyu_kei + $p1_s_kyu_kin;  // 添田さんの給与をC・Lは35%。試験修理に30%振分
        //$p1_s_urigen = $p1_s_urigen - 432323 + 129697;  // 添田さんの給与をC・Lは35%。試験修理に30%振分
    }
    $p1_s_urigen        = number_format(($p1_s_urigen / $tani), $keta);
    ///// CL
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='全体売上原価'", $p1_ym);
if (getUniResult($query, $p1_all_urigen) < 1) {
    $p1_all_urigen = 0;                             // 検索失敗
} else {
    // 2012/02/08 追加 2012年1月度 業務委託費（平出横川派遣料）調整
    if ($p1_ym == 201201) {
        $p1_all_urigen +=1156130;
    }
    // 2012/03/05 追加 2012年1月度 業務委託費（平出横川派遣料）調整 戻し
    if ($p1_ym == 201202) {
        $p1_all_urigen -=1156130;
    }
    $p1_all_urigen = number_format(($p1_all_urigen / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ売上原価'", $p1_ym);
if (getUniResult($query, $p1_c_urigen) < 1) {
    $p1_c_urigen = 0;                               // 検索失敗
} else {
    $p1_c_urigen = $p1_c_urigen - $p1_b_urigen - $p1_sc_metarial_sagaku;    // カプラ試修材料費も加味
    if ($p1_ym == 200912) {
        $p1_c_urigen = $p1_c_urigen + 1227429;
    }
    if ($p1_ym >= 201001) {
        $p1_c_urigen = $p1_c_urigen + $p1_c_kyu_kin;   // 添田さんの給与をC・Lは35%。試験修理に30%振分
        //$p1_c_urigen = $p1_c_urigen + 151313;   // 添田さんの給与をC・Lは35%。試験修理に30%振分
    }
    // 2013/11/07 追加 2013年10月度 業務委託費（横川派遣料）調整
    if ($p1_ym == 201310) {
        $p1_c_urigen -= 1245035;
    }
    if ($p1_ym == 201311) {
        $p1_c_urigen += 1245035;
    }
    if ($p1_ym == 201408) {
        $p1_c_urigen += 611904;
    }
    $p1_c_urigen = number_format(($p1_c_urigen / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア売上原価'", $p1_ym);
if (getUniResult($query, $p1_l_urigen) < 1) {
    $p1_l_urigen = 0 - $p1_s_urigen_sagaku;         // 検索失敗
} else {
    $p1_l_urigen = $p1_l_urigen - $p1_s_urigen_sagaku;
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
    $p1_l_urigen = number_format(($p1_l_urigen / $tani), $keta);
}

    ///// 前前月
    ///// 試験・修理
    $p2_s_urigen        = $p2_s_invent + $p2_s_metarial_sagaku + $p2_s_roumu_sagaku + $p2_s_expense_sagaku + $p2_s_endinv;
    $p2_s_urigen_sagaku = $p2_s_urigen;
    $p2_s_urigen        = $p2_s_urigen + $p2_sc_metarial_sagaku;    // カプラ試修材料費を加味（sagakuの下 リニアからマイナスしてしまう為）
    if ($p2_ym == 200912) {
        $p2_s_urigen = $p2_s_urigen - 1409708;
    }
    if ($p2_ym >= 201001) {
        $p2_s_urigen = $p2_s_urigen - $p2_s_kyu_kei + $p2_s_kyu_kin;   // 添田さんの給与をC・Lは35%。試験修理に30%振分
        //$p2_s_urigen = $p2_s_urigen - 432323 + 129697;   // 添田さんの給与をC・Lは35%。試験修理に30%振分
    }
    $p2_s_urigen        = number_format(($p2_s_urigen / $tani), $keta);
    ///// CL
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='全体売上原価'", $p2_ym);
if (getUniResult($query, $p2_all_urigen) < 1) {
    $p2_all_urigen = 0;                             // 検索失敗
} else {
    // 2012/02/08 追加 2012年1月度 業務委託費（平出横川派遣料）調整
    if ($p2_ym == 201201) {
        $p2_all_urigen +=1156130;
    }
    // 2012/03/05 追加 2012年1月度 業務委託費（平出横川派遣料）調整 戻し
    if ($p2_ym == 201202) {
        $p2_all_urigen -=1156130;
    }
    $p2_all_urigen = number_format(($p2_all_urigen / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ売上原価'", $p2_ym);
if (getUniResult($query, $p2_c_urigen) < 1) {
    $p2_c_urigen = 0;                               // 検索失敗
} else {
    $p2_c_urigen = $p2_c_urigen - $p2_b_urigen - $p2_sc_metarial_sagaku;    // カプラ試修材料費も加味
    if ($p2_ym == 200912) {
        $p2_c_urigen = $p2_c_urigen + 1227429;
    }
    if ($p2_ym >= 201001) {
        $p2_c_urigen = $p2_c_urigen + $p2_c_kyu_kin;   // 添田さんの給与をC・Lは35%。試験修理に30%振分
        //$p2_c_urigen = $p2_c_urigen + 151313;   // 添田さんの給与をC・Lは35%。試験修理に30%振分
    }
    // 2013/11/07 追加 2013年10月度 業務委託費（横川派遣料）調整
    if ($p2_ym == 201310) {
        $p2_c_urigen -= 1245035;
    }
    if ($p2_ym == 201311) {
        $p2_c_urigen += 1245035;
    }
    if ($p2_ym == 201408) {
        $p2_c_urigen += 611904;
    }
    $p2_c_urigen = number_format(($p2_c_urigen / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア売上原価'", $p2_ym);
if (getUniResult($query, $p2_l_urigen) < 1) {
    $p2_l_urigen = 0 - $p2_s_urigen_sagaku;         // 検索失敗
} else {
    $p2_l_urigen = $p2_l_urigen - $p2_s_urigen_sagaku;
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
    $p2_l_urigen = number_format(($p2_l_urigen / $tani), $keta);
}

    ///// 今期累計
    ///// 試験・修理
    $rui_s_urigen        = $rui_s_invent + $rui_s_metarial_sagaku + $rui_s_roumu_sagaku + $rui_s_expense_sagaku + $s_endinv;
    $rui_s_urigen_sagaku = $rui_s_urigen;
    $rui_s_urigen        = $rui_s_urigen + $rui_sc_metarial_sagaku; // カプラ試修材料費を加味（sagakuの下 リニアからマイナスしてしまう為）
    if ($yyyymm >= 200912 && $yyyymm <= 201003) {
        $rui_s_urigen = $rui_s_urigen - 1409708;
    }
    if ($yyyymm >= 201001) {
        $rui_s_urigen = $rui_s_urigen - $rui_s_kyu_kei + $rui_s_kyu_kin;    // 添田さんの給与をC・Lは35%。試験修理に30%振分
        //$rui_s_urigen = $rui_s_urigen - 432323 + 129697;    // 添田さんの給与をC・Lは35%。試験修理に30%振分
    }
    $rui_s_urigen        = number_format(($rui_s_urigen / $tani), $keta);
    ///// CL
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='全体売上原価'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_all_urigen) < 1) {
    $rui_all_urigen = 0;                            // 検索失敗
} else {
    // 2012/02/08 追加 2012年1月度 業務委託費（平出横川派遣料）調整
    if ($yyyymm >= 201201 && $yyyymm <= 201203) {
        $rui_all_urigen +=1156130;
    }
    // 2012/03/05 追加 2012年1月度 業務委託費（平出横川派遣料）調整 戻し
    if ($yyyymm >= 201202 && $yyyymm <= 201203) {
        $rui_all_urigen -=1156130;
    }
    $rui_all_urigen = number_format(($rui_all_urigen / $tani), $keta);
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='カプラ売上原価'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_c_urigen) < 1) {
    $rui_c_urigen = 0;                              // 検索失敗
} else {
    $rui_c_urigen = $rui_c_urigen - $rui_b_urigen - $rui_sc_metarial_sagaku;    // カプラ試修材料費も加味
    if ($yyyymm >= 200912 && $yyyymm <= 201003) {
        $rui_c_urigen = $rui_c_urigen + 1227429;
    }
    if ($yyyymm >= 201001) {
        $rui_c_urigen = $rui_c_urigen + $rui_c_kyu_kin;     // 添田さんの給与をC・Lは35%。試験修理に30%振分
    }
    // 2013/11/07 追加 2013年10月度 業務委託費（横川派遣料）調整
    if ($yyyymm >= 201310 && $yyyymm <= 201403) {
        $rui_c_urigen -= 1245035;
    }
    if ($yyyymm >= 201311 && $yyyymm <= 201403) {
        $rui_c_urigen += 1245035;
    }
    if ($yyyymm >= 201408 && $yyyymm <= 201503) {
        $rui_c_urigen += 611904;
    }
    $rui_c_urigen = number_format(($rui_c_urigen / $tani), $keta);
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='リニア売上原価'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_l_urigen) < 1) {
    $rui_l_urigen = 0 - $rui_s_urigen_sagaku;       // 検索失敗
} else {
    $rui_l_urigen = $rui_l_urigen - $rui_s_urigen_sagaku;
    if ($yyyymm >= 200912 && $yyyymm <= 201003) {
        $rui_l_urigen = $rui_l_urigen + 182279;
    }
    if ($yyyymm >= 201001) {
        $rui_l_urigen = $rui_l_urigen + $rui_l_kyu_kin;     // 添田さんの給与をC・Lは35%。試験修理に30%振分
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
        $rui_l_urigen +=229464;
    }
    $rui_l_urigen = number_format(($rui_l_urigen / $tani), $keta);
}

/********** 売上総利益 **********/
    ///// 商管
$p2_b_gross_profit  = $p2_b_uri - $p2_b_urigen;
$p2_b_uri           = number_format(($p2_b_uri / $tani), $keta);
$p2_b_invent        = number_format(($p2_b_invent / $tani), $keta);
$p2_b_metarial      = number_format(($p2_b_metarial / $tani), $keta);
$p2_b_endinv        = number_format(($p2_b_endinv / $tani), $keta);

$p1_b_gross_profit  = $p1_b_uri - $p1_b_urigen;
$p1_b_uri           = number_format(($p1_b_uri / $tani), $keta);
$p1_b_invent        = number_format(($p1_b_invent / $tani), $keta);
$p1_b_metarial      = number_format(($p1_b_metarial / $tani), $keta);
$p1_b_endinv        = number_format(($p1_b_endinv / $tani), $keta);

$b_gross_profit     = $b_uri - $b_urigen;
$b_uri              = number_format(($b_uri / $tani), $keta);
$b_invent           = number_format(($b_invent / $tani), $keta);
$b_metarial         = number_format(($b_metarial / $tani), $keta);
$b_endinv           = number_format(($b_endinv / $tani), $keta);

$rui_b_gross_profit = $rui_b_uri - $rui_b_urigen;
$rui_b_uri          = number_format(($rui_b_uri / $tani), $keta);
$rui_b_invent       = number_format(($rui_b_invent / $tani), $keta);
$rui_b_metarial     = number_format(($rui_b_metarial / $tani), $keta);
    
    ///// 試験・修理
$p2_s_gross_profit         = $p2_s_uri_sagaku - $p2_s_urigen_sagaku;
$p2_s_gross_profit_sagaku  = $p2_s_gross_profit;
$p2_s_gross_profit         = $p2_s_gross_profit + $p2_sc_uri_sagaku - $p2_sc_metarial_sagaku;    // カプラ試験修理を加味（sagakuの後－リニアからマイナスしてしまう為）
if ($p2_ym == 200912) {
    $p2_s_gross_profit = $p2_s_gross_profit + 1409708;
}
if ($p2_ym >= 201001) {
    $p2_s_gross_profit = $p2_s_gross_profit + $p2_s_kyu_kei - $p2_s_kyu_kin;  // 添田さんの給与をC・Lは35%。試験修理に30%振分
    //$p2_s_gross_profit = $p2_s_gross_profit + 432323 - 129697;  // 添田さんの給与をC・Lは35%。試験修理に30%振分
}
$p2_s_gross_profit         = number_format(($p2_s_gross_profit / $tani), $keta);

$p1_s_gross_profit         = $p1_s_uri_sagaku - $p1_s_urigen_sagaku;
$p1_s_gross_profit_sagaku  = $p1_s_gross_profit;
$p1_s_gross_profit         = $p1_s_gross_profit + $p1_sc_uri_sagaku - $p1_sc_metarial_sagaku;    // カプラ試験修理を加味（sagakuの後－リニアからマイナスしてしまう為）
if ($p1_ym == 200912) {
    $p1_s_gross_profit = $p1_s_gross_profit + 1409708;
}
if ($p1_ym >= 201001) {
    $p1_s_gross_profit = $p1_s_gross_profit + $p1_s_kyu_kei - $p1_s_kyu_kin;  // 添田さんの給与をC・Lは35%。試験修理に30%振分
    //$p1_s_gross_profit = $p1_s_gross_profit + 432323 - 129697;  // 添田さんの給与をC・Lは35%。試験修理に30%振分
}
$p1_s_gross_profit         = number_format(($p1_s_gross_profit / $tani), $keta);

$s_gross_profit            = $s_uri_sagaku - $s_urigen_sagaku;
$s_gross_profit_sagaku     = $s_gross_profit;
$s_gross_profit            = $s_gross_profit + $sc_uri_sagaku - $sc_metarial_sagaku;             // カプラ試験修理を加味（sagakuの後－リニアからマイナスしてしまう為）
if ($yyyymm == 200912) {
    $s_gross_profit = $s_gross_profit + 1409708;
}
if ($yyyymm >= 201001) {
    $s_gross_profit = $s_gross_profit + $s_kyu_kei - $s_kyu_kin;  // 添田さんの給与をC・Lは35%。試験修理に30%振分
    //$s_gross_profit = $s_gross_profit + 432323 - 129697;  // 添田さんの給与をC・Lは35%。試験修理に30%振分
}
$s_gross_profit            = number_format(($s_gross_profit / $tani), $keta);

$rui_s_gross_profit        = $rui_s_uri_sagaku - $rui_s_urigen_sagaku;
$rui_s_gross_profit_sagaku = $rui_s_gross_profit;
$rui_s_gross_profit        = $rui_s_gross_profit + $rui_sc_uri_sagaku - $rui_sc_metarial_sagaku; // カプラ試験修理を加味（sagakuの後－リニアからマイナスしてしまう為）
if ($yyyymm >= 200912 && $yyyymm <= 201003) {
    $rui_s_gross_profit = $rui_s_gross_profit + 1409708;
}
if ($yyyymm >= 201001) {
    $rui_s_gross_profit = $rui_s_gross_profit + $rui_s_kyu_kei - $rui_s_kyu_kin;  // 添田さんの給与をC・Lは35%。試験修理に30%振分
    //$rui_s_gross_profit = $rui_s_gross_profit + 432323 - 129697;  // 添田さんの給与をC・Lは35%。試験修理に30%振分
}
$rui_s_gross_profit        = number_format(($rui_s_gross_profit / $tani), $keta);

    ///// 当月
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='全体総利益'", $yyyymm);
if (getUniResult($query, $all_gross_profit) < 1) {
    $all_gross_profit = 0;                      // 検索失敗
} else {
    if ($yyyymm == 200906) {
        $all_gross_profit = $all_gross_profit + $b_uri_sagaku - 3100900;
    } elseif ($yyyymm == 200905) {
        $all_gross_profit = $all_gross_profit + $b_uri_sagaku + 1550450;
    } elseif ($yyyymm == 200904) {
        $all_gross_profit = $all_gross_profit + $b_uri_sagaku + 1550450;
    } else {
        $all_gross_profit = $all_gross_profit + $b_uri_sagaku;
    }
    // 2012/02/08 追加 2012年1月度 業務委託費（平出横川派遣料）調整
    if ($yyyymm == 201201) {
        $all_gross_profit -=1156130;
    }
    // 2012/03/05 追加 2012年1月度 業務委託費（平出横川派遣料）調整 戻し
    if ($yyyymm == 201202) {
        $all_gross_profit +=1156130;
    }
    $all_gross_profit = number_format(($all_gross_profit / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ総利益'", $yyyymm);
if (getUniResult($query, $c_gross_profit) < 1) {
    $c_gross_profit = 0;                        // 検索失敗
} else {
    $c_gross_profit = $c_gross_profit + $b_urigen - $sc_uri_sagaku + $sc_metarial_sagaku;   // カプラ試験修理を加味
    if ($yyyymm == 200912) {
        $c_gross_profit = $c_gross_profit - 1227429;
    }
    if ($yyyymm >= 201001) {
        $c_gross_profit = $c_gross_profit - $c_kyu_kin;     // 添田さんの給与をC・Lは35%。試験修理に30%振分
        //$c_gross_profit = $c_gross_profit - 151313;     // 添田さんの給与をC・Lは35%。試験修理に30%振分
    }
    // 2013/11/07 追加 2013年10月度 業務委託費（横川派遣料）調整
    if ($yyyymm == 201310) {
        $c_gross_profit += 1245035;
    }
    if ($yyyymm == 201311) {
        $c_gross_profit -= 1245035;
    }
    if ($yyyymm == 201408) {
        $c_gross_profit -= 611904;
    }
    // 2013/11/07 追加 2013年10月度 業務委託費（横川派遣料）調整
    if ($yyyymm == 201310) {
        $b_urigen += 1245035;
    }
    if ($yyyymm == 201311) {
        $b_urigen -= 1245035;
    }
    if ($yyyymm == 201408) {
        $b_urigen -= 841368;
    }
    $b_urigen       = number_format(($b_urigen / $tani), $keta);
    $c_gross_profit = number_format(($c_gross_profit / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア総利益'", $yyyymm);
if (getUniResult($query, $l_gross_profit) < 1) {
    if ($yyyymm == 200906) {
        $l_gross_profit = 0 - $s_gross_profit_sagaku - 3100900;     // 検索失敗
    } elseif ($yyyymm == 200905) {
        $l_gross_profit = 0 - $s_gross_profit_sagaku - 1550450;     // 検索失敗
    } elseif ($yyyymm == 200904) {
        $l_gross_profit = 0 - $s_gross_profit_sagaku - 1550450;     // 検索失敗
    } else {
        $l_gross_profit = 0 - $s_gross_profit_sagaku;               // 検索失敗
    }
} else {
    if ($yyyymm == 200906) {
        $l_gross_profit = $l_gross_profit - $s_gross_profit_sagaku - 3100900;
    } elseif ($yyyymm == 200905) {
        $l_gross_profit = $l_gross_profit - $s_gross_profit_sagaku + 1550450;
    } elseif ($yyyymm == 200904) {
        $l_gross_profit = $l_gross_profit - $s_gross_profit_sagaku + 1550450;
    } else {
        $l_gross_profit = $l_gross_profit - $s_gross_profit_sagaku;
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
    $l_gross_profit = number_format(($l_gross_profit / $tani), $keta);
}
    ///// 前月
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='全体総利益'", $p1_ym);
if (getUniResult($query, $p1_all_gross_profit) < 1) {
    $p1_all_gross_profit = 0;                   // 検索失敗
} else {
    if ($p1_ym == 200906) {
        $p1_all_gross_profit = $p1_all_gross_profit + $p1_b_uri_sagaku - 3100900;
    } elseif ($p1_ym == 200905) {
        $p1_all_gross_profit = $p1_all_gross_profit + $p1_b_uri_sagaku + 1550450;
    } elseif ($p1_ym == 200904) {
        $p1_all_gross_profit = $p1_all_gross_profit + $p1_b_uri_sagaku + 1550450;
    } else {
        $p1_all_gross_profit = $p1_all_gross_profit + $p1_b_uri_sagaku;
    }
    // 2012/02/08 追加 2012年1月度 業務委託費（平出横川派遣料）調整
    if ($p1_ym == 201201) {
        $p1_all_gross_profit -=1156130;
    }
    // 2012/03/05 追加 2012年1月度 業務委託費（平出横川派遣料）調整 戻し
    if ($p1_ym == 201202) {
        $p1_all_gross_profit +=1156130;
    }
    $p1_all_gross_profit = number_format(($p1_all_gross_profit / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ総利益'", $p1_ym);
if (getUniResult($query, $p1_c_gross_profit) < 1) {
    $p1_c_gross_profit = 0;                     // 検索失敗
} else {
    $p1_c_gross_profit = $p1_c_gross_profit + $p1_b_urigen - $p1_sc_uri_sagaku + $p1_sc_metarial_sagaku;    // カプラ試験修理を加味
    if ($p1_ym == 200912) {
        $p1_c_gross_profit = $p1_c_gross_profit - 1227429;
    }
    if ($p1_ym >= 201001) {
        $p1_c_gross_profit = $p1_c_gross_profit - $p1_c_kyu_kin;   // 添田さんの給与をC・Lは35%。試験修理に30%振分
        //$p1_c_gross_profit = $p1_c_gross_profit - 151313;   // 添田さんの給与をC・Lは35%。試験修理に30%振分
    }
    // 2013/11/07 追加 2013年10月度 業務委託費（横川派遣料）調整
    if ($p1_ym == 201310) {
        $p1_c_gross_profit += 1245035;
    }
    if ($p1_ym == 201311) {
        $p1_c_gross_profit -= 1245035;
    }
    if ($p1_ym == 201408) {
        $p1_c_gross_profit -= 611904;
    }
    // 2013/11/07 追加 2013年10月度 業務委託費（横川派遣料）調整
    if ($p1_ym == 201310) {
        $p1_b_urigen += 1245035;
    }
    if ($p1_ym == 201311) {
        $p1_b_urigen -= 1245035;
    }
    if ($p1_ym == 201408) {
        $p1_b_urigen -= 841368;
    }
    $p1_b_urigen       = number_format(($p1_b_urigen / $tani), $keta);
    $p1_c_gross_profit = number_format(($p1_c_gross_profit / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア総利益'", $p1_ym);
if (getUniResult($query, $p1_l_gross_profit) < 1) {
    if ($p1_ym == 200906) {
        $p1_l_gross_profit = 0 - $p1_s_gross_profit_sagaku - 3100900;   // 検索失敗
    } elseif ($p1_ym == 200905) {
        $p1_l_gross_profit = 0 - $p1_s_gross_profit_sagaku - 1550450;   // 検索失敗
    } elseif ($p1_ym == 200904) {
        $p1_l_gross_profit = 0 - $p1_s_gross_profit_sagaku - 1550450;   // 検索失敗
    } else {
        $p1_l_gross_profit = 0 - $p1_s_gross_profit_sagaku;             // 検索失敗
    }
} else {
    if ($p1_ym == 200906) {
        $p1_l_gross_profit = $p1_l_gross_profit - $p1_s_gross_profit_sagaku - 3100900;
    } elseif ($p1_ym == 200905) {
        $p1_l_gross_profit = $p1_l_gross_profit - $p1_s_gross_profit_sagaku + 1550450;
    } elseif ($p1_ym == 200904) {
        $p1_l_gross_profit = $p1_l_gross_profit - $p1_s_gross_profit_sagaku + 1550450;
    } else {
        $p1_l_gross_profit = $p1_l_gross_profit - $p1_s_gross_profit_sagaku;
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
    $p1_l_gross_profit = number_format(($p1_l_gross_profit / $tani), $keta);
}
    ///// 前前月
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='全体総利益'", $p2_ym);
if (getUniResult($query, $p2_all_gross_profit) < 1) {
    $p2_all_gross_profit = 0;                   // 検索失敗
} else {
    if ($p2_ym == 200906) {
        $p2_all_gross_profit = $p2_all_gross_profit + $p2_b_uri_sagaku - 3100900;
    } elseif ($p2_ym == 200905) {
        $p2_all_gross_profit = $p2_all_gross_profit + $p2_b_uri_sagaku + 1550450;
    } elseif ($p2_ym == 200904) {
        $p2_all_gross_profit = $p2_all_gross_profit + $p2_b_uri_sagaku + 1550450;
    } else {
        $p2_all_gross_profit = $p2_all_gross_profit + $p2_b_uri_sagaku;
    }
    // 2012/02/08 追加 2012年1月度 業務委託費（平出横川派遣料）調整
    if ($p2_ym == 201201) {
        $p2_all_gross_profit -=1156130;
    }
    // 2012/03/05 追加 2012年1月度 業務委託費（平出横川派遣料）調整 戻し
    if ($p2_ym == 201202) {
        $p2_all_gross_profit +=1156130;
    }
    $p2_all_gross_profit = number_format(($p2_all_gross_profit / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ総利益'", $p2_ym);
if (getUniResult($query, $p2_c_gross_profit) < 1) {
    $p2_c_gross_profit = 0;                     // 検索失敗
} else {
    $p2_c_gross_profit = $p2_c_gross_profit + $p2_b_urigen - $p2_sc_uri_sagaku + $p2_sc_metarial_sagaku;    // カプラ試験修理を加味
    if ($p2_ym == 200912) {
        $p2_c_gross_profit = $p2_c_gross_profit - 1227429;
    }
    if ($p2_ym >= 201001) {
        $p2_c_gross_profit = $p2_c_gross_profit - $p2_c_kyu_kin;   // 添田さんの給与をC・Lは35%。試験修理に30%振分
        //$p2_c_gross_profit = $p2_c_gross_profit - 151313;   // 添田さんの給与をC・Lは35%。試験修理に30%振分
    }
    // 2013/11/07 追加 2013年10月度 業務委託費（横川派遣料）調整
    if ($p2_ym == 201310) {
        $p2_c_gross_profit += 1245035;
    }
    if ($p2_ym == 201311) {
        $p2_c_gross_profit -= 1245035;
    }
    if ($p2_ym == 201408) {
        $p2_c_gross_profit -= 611904;
    }
    // 2013/11/07 追加 2013年10月度 業務委託費（横川派遣料）調整
    if ($p2_ym == 201310) {
        $p2_b_urigen += 1245035;
    }
    if ($p2_ym == 201311) {
        $p2_b_urigen -= 1245035;
    }
    if ($p2_ym == 201408) {
        $p2_b_urigen -= 841368;
    }
    $p2_b_urigen       = number_format(($p2_b_urigen / $tani), $keta);
    $p2_c_gross_profit = number_format(($p2_c_gross_profit / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア総利益'", $p2_ym);
if (getUniResult($query, $p2_l_gross_profit) < 1) {
    if ($p2_ym == 200906) {
        $p2_l_gross_profit = 0 - $p2_s_gross_profit_sagaku - 3100900;   // 検索失敗
    } elseif ($p2_ym == 200905) {
        $p2_l_gross_profit = 0 - $p2_s_gross_profit_sagaku - 1550450;   // 検索失敗
    } elseif ($p2_ym == 200904) {
        $p2_l_gross_profit = 0 - $p2_s_gross_profit_sagaku - 1550450;   // 検索失敗
    } else {
        $p2_l_gross_profit = 0 - $p2_s_gross_profit_sagaku;             // 検索失敗
    }
} else {
    if ($p2_ym == 200906) {
        $p2_l_gross_profit = $p2_l_gross_profit - $p2_s_gross_profit_sagaku - 3100900;
    } elseif ($p2_ym == 200905) {
        $p2_l_gross_profit = $p2_l_gross_profit - $p2_s_gross_profit_sagaku + 1550450;
    } elseif ($p2_ym == 200904) {
        $p2_l_gross_profit = $p2_l_gross_profit - $p2_s_gross_profit_sagaku + 1550450;
    } else {
        $p2_l_gross_profit = $p2_l_gross_profit - $p2_s_gross_profit_sagaku;
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
    $p2_l_gross_profit = number_format(($p2_l_gross_profit / $tani), $keta);
}
    ///// 今期累計
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='全体総利益'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_all_gross_profit) < 1) {
    $rui_all_gross_profit = 0;                  // 検索失敗
} else {
    if ($yyyymm == 200905) {
        $rui_all_gross_profit = $rui_all_gross_profit + 3100900;
    } elseif ($yyyymm == 200904) {
        $rui_all_gross_profit = $rui_all_gross_profit + 1550450;
    }
    // 2012/02/08 追加 2012年1月度 業務委託費（平出横川派遣料）調整
    if ($yyyymm >= 201201 && $yyyymm <= 201203) {
        $rui_all_gross_profit -=1156130;
    }
    // 2012/03/05 追加 2012年1月度 業務委託費（平出横川派遣料）調整 戻し
    if ($yyyymm >= 201202 && $yyyymm <= 201203) {
        $rui_all_gross_profit +=1156130;
    }
    $rui_all_gross_profit = $rui_all_gross_profit + $rui_b_uri_sagaku;
    $rui_all_gross_profit = number_format(($rui_all_gross_profit / $tani), $keta);
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='カプラ総利益'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_c_gross_profit) < 1) {
    $rui_c_gross_profit = 0;                    // 検索失敗
} else {
    $rui_c_gross_profit = $rui_c_gross_profit + $rui_b_urigen - $rui_sc_uri_sagaku + $rui_sc_metarial_sagaku;   // カプラ試験修理を加味
    if ($yyyymm >= 200912 && $yyyymm <= 201003) {
        $rui_c_gross_profit = $rui_c_gross_profit - 1227429;
    }
    if ($yyyymm >= 201001) {
        $rui_c_gross_profit = $rui_c_gross_profit - $rui_c_kyu_kin;     // 添田さんの給与をC・Lは35%。試験修理に30%振分
        //$rui_c_gross_profit = $rui_c_gross_profit - 151313;     // 添田さんの給与をC・Lは35%。試験修理に30%振分
    }
    // 2013/11/07 追加 2013年10月度 業務委託費（横川派遣料）調整
    if ($yyyymm >= 201310 && $yyyymm <= 201403) {
        $rui_c_gross_profit += 1245035;
    }
    if ($yyyymm >= 201311 && $yyyymm <= 201403) {
        $rui_c_gross_profit -= 1245035;
    }
    if ($yyyymm >= 201408 && $yyyymm <= 201503) {
        $rui_c_gross_profit -= 611904;
    }
    // 2013/11/07 追加 2013年10月度 業務委託費（横川派遣料）調整
    if ($yyyymm >= 201310 && $yyyymm <= 201403) {
        $rui_b_urigen += 1245035;
    }
    if ($yyyymm >= 201311 && $yyyymm <= 201403) {
        $rui_b_urigen -= 1245035;
    }
    if ($yyyymm >= 201408 && $yyyymm <= 201503) {
        $rui_b_urigen -= 841368;
    }
    $rui_b_urigen       = number_format(($rui_b_urigen / $tani), $keta);
    $rui_c_gross_profit = number_format(($rui_c_gross_profit / $tani), $keta);
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='リニア総利益'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_l_gross_profit) < 1) {
    $rui_l_gross_profit = 0 - $rui_s_gross_profit_sagaku;   // 検索失敗
} else {
    $rui_l_gross_profit = $rui_l_gross_profit - $rui_s_gross_profit_sagaku;
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
        $rui_l_gross_profit -=229464;
    }
    $rui_l_gross_profit = number_format(($rui_l_gross_profit / $tani), $keta);
}

/********** 販管費の人件費 **********/
    ///// 当月
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修人件費'", $yyyymm);
if (getUniResult($query, $s_han_jin) < 1) {
    $s_han_jin        = 0;                      // 検索失敗
    $s_han_jin_sagaku = 0;
} else {
    $s_han_jin_sagaku = $s_han_jin;
    $s_han_jin        = number_format(($s_han_jin / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='商管人件費'", $yyyymm);
if (getUniResult($query, $b_han_jin_sagaku) < 1) {
    $b_han_jin_sagaku = 0;                      // 検索失敗
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='全体人件費'", $yyyymm);
if (getUniResult($query, $all_han_jin) < 1) {
    $all_han_jin = 0;                           // 検索失敗
} else {
    $all_han_jin = number_format(($all_han_jin / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ商管社員按分給与'", $yyyymm);
if (getUniResult($query, $c_allo_kin) < 1) {
    $c_allo_kin = 0;                            // 検索失敗
} else {
    //$c_han_jin = number_format(($c_han_jin / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ人件費'", $yyyymm);
if (getUniResult($query, $c_han_jin) < 1) {
    $c_han_jin = 0;                             // 検索失敗
} else {
    $c_han_jin = $c_han_jin - $c_allo_kin;
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア商管社員按分給与'", $yyyymm);
if (getUniResult($query, $l_allo_kin) < 1) {
    $l_allo_kin = 0;     // 検索失敗
} else {
    //$c_han_jin = number_format(($c_han_jin / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア人件費'", $yyyymm);
if (getUniResult($query, $l_han_jin) < 1) {
    $l_han_jin = 0 - $s_han_jin_sagaku;         // 検索失敗
} else {
    $l_han_jin = $l_han_jin - $s_han_jin_sagaku - $l_allo_kin;
    $l_han_jin = number_format(($l_han_jin / $tani), $keta);
}
    ///// 当月 商管
$query = sprintf("select sum(orign_kin) from act_allo_history where pl_bs_ym=%d and actcod>=8101 and orign_id=670", $yyyymm);
if (getUniResult($query, $b_han_jin) < 1) {
    $b_han_jin = 0 + $b_han_jin_sagaku;         // 検索失敗
    $b_han_all = $b_han_jin;
    $b_sagaku  = $b_sagaku + $b_han_jin;        // カプラ差額計算用
} else {
    // 下は7月未払い旧余分追加 テスト用
    $b_han_jin = $b_han_jin + $b_han_jin_sagaku;
    $c_han_jin = $c_han_jin - $b_han_jin;
    $b_sagaku  = $b_sagaku + $b_han_jin;        // カプラ差額計算用
    $b_han_jin = $b_han_jin + $c_allo_kin + $l_allo_kin;
    $b_han_all = $b_han_jin;
    $c_han_jin = number_format(($c_han_jin / $tani), $keta);
    $b_han_jin = number_format(($b_han_jin / $tani), $keta);
}

    ///// 前月
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修人件費'", $p1_ym);
if (getUniResult($query, $p1_s_han_jin) < 1) {
    $p1_s_han_jin        = 0;                   // 検索失敗
    $p1_s_han_jin_sagaku = 0;
} else {
    $p1_s_han_jin_sagaku = $p1_s_han_jin;
    $p1_s_han_jin        = number_format(($p1_s_han_jin / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='商管人件費'", $p1_ym);
if (getUniResult($query, $p1_b_han_jin_sagaku) < 1) {
    $p1_b_han_jin_sagaku = 0;                   // 検索失敗
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='全体人件費'", $p1_ym);
if (getUniResult($query, $p1_all_han_jin) < 1) {
    $p1_all_han_jin = 0;                        // 検索失敗
} else {
    $p1_all_han_jin = number_format(($p1_all_han_jin / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ商管社員按分給与'", $p1_ym);
if (getUniResult($query, $p1_c_allo_kin) < 1) {
    $p1_c_allo_kin = 0;                         // 検索失敗
} else {
    //$c_han_jin = number_format(($c_han_jin / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ人件費'", $p1_ym);
if (getUniResult($query, $p1_c_han_jin) < 1) {
    $p1_c_han_jin = 0 - $p1_c_allo_kin;         // 検索失敗
} else {
    $p1_c_han_jin = $p1_c_han_jin - $p1_c_allo_kin;
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア商管社員按分給与'", $p1_ym);
if (getUniResult($query, $p1_l_allo_kin) < 1) {
    $p1_l_allo_kin = 0;                         // 検索失敗
} else {
    //$c_han_jin = number_format(($c_han_jin / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア人件費'", $p1_ym);
if (getUniResult($query, $p1_l_han_jin) < 1) {
    $p1_l_han_jin = 0 - $p1_s_han_jin_sagaku - $p1_l_allo_kin;      // 検索失敗
} else {
    $p1_l_han_jin = $p1_l_han_jin - $p1_s_han_jin_sagaku - $p1_l_allo_kin;
    $p1_l_han_jin = number_format(($p1_l_han_jin / $tani), $keta);
}
    ///// 前月 商管
$query = sprintf("select sum(orign_kin) from act_allo_history where pl_bs_ym=%d and actcod>=8101 and orign_id=670", $p1_ym);
if (getUniResult($query, $p1_b_han_jin) < 1) {
    $p1_b_han_jin = 0 + $p1_b_han_jin_sagaku + $p1_c_allo_kin + $p1_l_allo_kin;     // 検索失敗
    $p1_b_han_all = $p1_b_han_jin;
    $p1_b_sagaku  = $p1_b_sagaku + $p1_b_han_jin;       // カプラ差額計算用
} else {
    $p1_b_han_jin = $p1_b_han_jin + $p1_b_han_jin_sagaku;
    $p1_c_han_jin = $p1_c_han_jin - $p1_b_han_jin;
    $p1_b_sagaku  = $p1_b_sagaku + $p1_b_han_jin;       // カプラ差額計算用
    $p1_b_han_jin = $p1_b_han_jin + $p1_c_allo_kin + $p1_l_allo_kin;
    $p1_b_han_all = $p1_b_han_jin;
    $p1_c_han_jin = number_format(($p1_c_han_jin / $tani), $keta);
    $p1_b_han_jin = number_format(($p1_b_han_jin / $tani), $keta);
}

    ///// 前前月
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修人件費'", $p2_ym);
if (getUniResult($query, $p2_s_han_jin) < 1) {
    $p2_s_han_jin        = 0;                   // 検索失敗
    $p2_s_han_jin_sagaku = 0;
} else {
    $p2_s_han_jin_sagaku = $p2_s_han_jin;
    $p2_s_han_jin        = number_format(($p2_s_han_jin / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='商管人件費'", $p2_ym);
if (getUniResult($query, $p2_b_han_jin_sagaku) < 1) {
    $p2_b_han_jin_sagaku = 0;                   // 検索失敗
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='全体人件費'", $p2_ym);
if (getUniResult($query, $p2_all_han_jin) < 1) {
    $p2_all_han_jin = 0;                        // 検索失敗
} else {
    $p2_all_han_jin = number_format(($p2_all_han_jin / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ商管社員按分給与'", $p2_ym);
if (getUniResult($query, $p2_c_allo_kin) < 1) {
    $p2_c_allo_kin = 0;                         // 検索失敗
} else {
    //$c_han_jin = number_format(($c_han_jin / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ人件費'", $p2_ym);
if (getUniResult($query, $p2_c_han_jin) < 1) {
    $p2_c_han_jin = 0 - $p2_c_allo_kin;         // 検索失敗
} else {
    $p2_c_han_jin = $p2_c_han_jin - $p2_c_allo_kin;
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア商管社員按分給与'", $p2_ym);
if (getUniResult($query, $p2_l_allo_kin) < 1) {
    $p2_l_allo_kin = 0;     // 検索失敗
} else {
    //$c_han_jin = number_format(($c_han_jin / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア人件費'", $p2_ym);
if (getUniResult($query, $p2_l_han_jin) < 1) {
    $p2_l_han_jin = 0 - $p2_s_han_jin_sagaku - $p2_l_allo_kin;      // 検索失敗
} else {
    $p2_l_han_jin = $p2_l_han_jin - $p2_s_han_jin_sagaku - $p2_l_allo_kin;
    $p2_l_han_jin = number_format(($p2_l_han_jin / $tani), $keta);
}
    ///// 前前月 商管
$query = sprintf("select sum(orign_kin) from act_allo_history where pl_bs_ym=%d and actcod>=8101 and orign_id=670", $p2_ym);
if (getUniResult($query, $p2_b_han_jin) < 1) {
    $p2_b_han_jin = 0 + $p2_b_han_jin_sagaku + $p2_c_allo_kin + $p2_l_allo_kin;     // 検索失敗
    $p2_b_han_all = $p2_b_han_jin;
    $p2_b_sagaku  = $p2_b_sagaku + $p2_b_han_jin;                   // カプラ差額計算用
} else {
    $p2_b_han_jin = $p2_b_han_jin + $p2_b_han_jin_sagaku;
    $p2_c_han_jin = $p2_c_han_jin - $p2_b_han_jin;
    $p2_b_sagaku  = $p2_b_sagaku + $p2_b_han_jin;                   // カプラ差額計算用
    $p2_b_han_jin = $p2_b_han_jin + $p2_c_allo_kin + $p2_l_allo_kin;
    $p2_b_han_all = $p2_b_han_jin;
    $p2_c_han_jin = number_format(($p2_c_han_jin / $tani), $keta);
    $p2_b_han_jin = number_format(($p2_b_han_jin / $tani), $keta);
}

    ///// 今期累計
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='試修人件費'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_s_han_jin) < 1) {
    $rui_s_han_jin        = 0;                  // 検索失敗
    $rui_s_han_jin_sagaku = 0;
} else {
    $rui_s_han_jin_sagaku = $rui_s_han_jin;
    $rui_s_han_jin        = number_format(($rui_s_han_jin / $tani), $keta);
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='商管人件費'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_b_han_jin_sagaku) < 1) {
    $rui_b_han_jin_sagaku = 0;                  // 検索失敗
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='全体人件費'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_all_han_jin) < 1) {
    $rui_all_han_jin = 0;                       // 検索失敗
} else {
    $rui_all_han_jin = number_format(($rui_all_han_jin / $tani), $keta);
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='カプラ商管社員按分給与'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_c_allo_kin) < 1) {
    $rui_c_allo_kin = 0;                        // 検索失敗
} else {
    //$c_han_jin = number_format(($c_han_jin / $tani), $keta);
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='カプラ人件費'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_c_han_jin) < 1) {
    $rui_c_han_jin = 0 - $rui_c_allo_kin;       // 検索失敗
} else {
    $rui_c_han_jin = $rui_c_han_jin - $rui_c_allo_kin;
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='リニア商管社員按分給与'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_l_allo_kin) < 1) {
    $rui_l_allo_kin = 0;     // 検索失敗
} else {
    //$c_han_jin = number_format(($c_han_jin / $tani), $keta);
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='リニア人件費'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_l_han_jin) < 1) {
    $rui_l_han_jin = 0 - $rui_s_han_jin_sagaku - $rui_l_allo_kin;   // 検索失敗
} else {
    $rui_l_han_jin = $rui_l_han_jin - $rui_s_han_jin_sagaku - $rui_l_allo_kin;
    $rui_l_han_jin = number_format(($rui_l_han_jin / $tani), $keta);
}
    ///// 今期累計 商管
$query = sprintf("select sum(orign_kin) from act_allo_history where pl_bs_ym>=%d and pl_bs_ym<=%d and actcod>=8101 and orign_id=670", $str_ym, $yyyymm);
if (getUniResult($query, $rui_b_han_jin) < 1) {
    $rui_b_han_jin = 0 + $rui_b_han_jin_sagaku + $rui_c_allo_kin + $rui_l_allo_kin;     // 検索失敗
    $rui_b_han_all = $rui_b_han_jin;
    $rui_b_sagaku  = $rui_b_sagaku + $rui_b_han_jin;                // カプラ差額計算用
} else {
    // 下は7月未払い旧余分追加 テスト用
    $rui_b_han_jin = $rui_b_han_jin + $rui_b_han_jin_sagaku;
    $rui_c_han_jin = $rui_c_han_jin - $rui_b_han_jin;
    $rui_b_sagaku  = $rui_b_sagaku + $rui_b_han_jin;                // カプラ差額計算用
    $rui_b_han_jin = $rui_b_han_jin + $rui_c_allo_kin + $rui_l_allo_kin;
    $rui_b_han_all = $rui_b_han_jin;
    $rui_c_han_jin = number_format(($rui_c_han_jin / $tani), $keta);
    $rui_b_han_jin = number_format(($rui_b_han_jin / $tani), $keta);
}

/********** 販管費の経費 **********/
    ///// 当月
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修販管費経費'", $yyyymm);
if (getUniResult($query, $s_han_kei) < 1) {
    $s_han_kei        = 0;                  // 検索失敗
    $s_han_kei_sagaku = 0;
} else {
    $s_han_kei_sagaku = $s_han_kei;
    $s_han_kei        = number_format(($s_han_kei / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='商管販管費経費'", $yyyymm);
if (getUniResult($query, $b_han_kei_sagaku) < 1) {
    $b_han_kei_sagaku = 0;                  // 検索失敗
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='全体経費'", $yyyymm);
if (getUniResult($query, $all_han_kei) < 1) {
    $all_han_kei = 0;                       // 検索失敗
} else {
    $all_han_kei = number_format(($all_han_kei / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ経費'", $yyyymm);
if (getUniResult($query, $c_han_kei) < 1) {
    $c_han_kei = 0;                         // 検索失敗
} else {
    //$c_han_kei = number_format(($c_han_kei / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア経費'", $yyyymm);
if (getUniResult($query, $l_han_kei) < 1) {
    $l_han_kei = 0 - $s_han_kei_sagaku;     // 検索失敗
} else {
    $l_han_kei = $l_han_kei - $s_han_kei_sagaku;
    $l_han_kei = number_format(($l_han_kei / $tani), $keta);
}
    ///// 当月 商管
$query = sprintf("select sum(orign_kin) from act_allo_history where pl_bs_ym=%d and actcod>=7501 and actcod<=8000 and orign_id=670", $yyyymm);
if (getUniResult($query, $b_han_kei) < 1) {
    $b_han_kei = 0 + $b_han_kei_sagaku;     // 検索失敗
    $b_han_all = $b_han_all + $b_han_kei;
    $b_sagaku  = $b_sagaku + $b_han_kei;    // カプラ差額計算用
} else {
    $b_han_kei = $b_han_kei + $b_han_kei_sagaku;
    $b_han_all = $b_han_all + $b_han_kei;
    $c_han_kei = $c_han_kei - $b_han_kei;
    $b_sagaku  = $b_sagaku + $b_han_kei;    // カプラ差額計算用
    $c_han_kei = number_format(($c_han_kei / $tani), $keta);
    $b_han_kei = number_format(($b_han_kei / $tani), $keta);
}

    ///// 前月
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修販管費経費'", $p1_ym);
if (getUniResult($query, $p1_s_han_kei) < 1) {
    $p1_s_han_kei        = 0;               // 検索失敗
    $p1_s_han_kei_sagaku = 0;
} else {
    $p1_s_han_kei_sagaku = $p1_s_han_kei;
    $p1_s_han_kei        = number_format(($p1_s_han_kei / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='商管販管費経費'", $p1_ym);
if (getUniResult($query, $p1_b_han_kei_sagaku) < 1) {
    $p1_b_han_kei_sagaku = 0;               // 検索失敗
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='全体経費'", $p1_ym);
if (getUniResult($query, $p1_all_han_kei) < 1) {
    $p1_all_han_kei = 0;                    // 検索失敗
} else {
    $p1_all_han_kei = number_format(($p1_all_han_kei / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ経費'", $p1_ym);
if (getUniResult($query, $p1_c_han_kei) < 1) {
    $p1_c_han_kei = 0;                      // 検索失敗
} else {
    //$p1_c_han_kei = number_format(($p1_c_han_kei / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア経費'", $p1_ym);
if (getUniResult($query, $p1_l_han_kei) < 1) {
    $p1_l_han_kei = 0 - $p1_s_han_kei_sagaku;       // 検索失敗
} else {
    $p1_l_han_kei = $p1_l_han_kei - $p1_s_han_kei_sagaku;
    $p1_l_han_kei = number_format(($p1_l_han_kei / $tani), $keta);
}
    ///// 前月 商管
$query = sprintf("select sum(orign_kin) from act_allo_history where pl_bs_ym=%d and actcod>=7501 and actcod<=8000 and orign_id=670", $p1_ym);
if (getUniResult($query, $p1_b_han_kei) < 1) {
    $p1_b_han_kei = 0 + $p1_b_han_kei_sagaku;       // 検索失敗
    $p1_b_han_all = $p1_b_han_all + $p1_b_han_kei;
    $p1_b_sagaku  = $p1_b_sagaku + $p1_b_han_kei;   // カプラ差額計算用
} else {
    $p1_b_han_kei = $p1_b_han_kei + $p1_b_han_kei_sagaku;
    $p1_b_han_all = $p1_b_han_all + $p1_b_han_kei;
    $p1_c_han_kei = $p1_c_han_kei - $p1_b_han_kei;
    $p1_b_sagaku  = $p1_b_sagaku + $p1_b_han_kei;   // カプラ差額計算用
    $p1_c_han_kei = number_format(($p1_c_han_kei / $tani), $keta);
    $p1_b_han_kei = number_format(($p1_b_han_kei / $tani), $keta);
}

    ///// 前前月
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修販管費経費'", $p2_ym);
if (getUniResult($query, $p2_s_han_kei) < 1) {
    $p2_s_han_kei        = 0;               // 検索失敗
    $p2_s_han_kei_sagaku = 0;
} else {
    $p2_s_han_kei_sagaku = $p2_s_han_kei;
    $p2_s_han_kei        = number_format(($p2_s_han_kei / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='商管販管費経費'", $p2_ym);
if (getUniResult($query, $p2_b_han_kei_sagaku) < 1) {
    $p2_b_han_kei_sagaku = 0;               // 検索失敗
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='全体経費'", $p2_ym);
if (getUniResult($query, $p2_all_han_kei) < 1) {
    $p2_all_han_kei = 0;                    // 検索失敗
} else {
    $p2_all_han_kei = number_format(($p2_all_han_kei / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ経費'", $p2_ym);
if (getUniResult($query, $p2_c_han_kei) < 1) {
    $p2_c_han_kei = 0;                      // 検索失敗
} else {
    //$p2_c_han_kei = number_format(($p2_c_han_kei / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア経費'", $p2_ym);
if (getUniResult($query, $p2_l_han_kei) < 1) {
    $p2_l_han_kei = 0 - $p2_s_han_kei_sagaku;       // 検索失敗
} else {
    $p2_l_han_kei = $p2_l_han_kei - $p2_s_han_kei_sagaku;
    $p2_l_han_kei = number_format(($p2_l_han_kei / $tani), $keta);
}
    ///// 前前月 商管
$query = sprintf("select sum(orign_kin) from act_allo_history where pl_bs_ym=%d and actcod>=7501 and actcod<=8000 and orign_id=670", $p2_ym);
if (getUniResult($query, $p2_b_han_kei) < 1) {
    $p2_b_han_kei = 0 + $p2_b_han_kei_sagaku;       // 検索失敗
    $p2_b_han_all = $p2_b_han_all + $p2_b_han_kei;
    $p2_b_sagaku  = $p2_b_sagaku + $p2_b_han_kei;   // カプラ差額計算用
} else {
    $p2_b_han_kei = $p2_b_han_kei + $p2_b_han_kei_sagaku;
    $p2_b_han_all = $p2_b_han_all + $p2_b_han_kei;
    $p2_c_han_kei = $p2_c_han_kei - $p2_b_han_kei;
    $p2_b_sagaku  = $p2_b_sagaku + $p2_b_han_kei;   // カプラ差額計算用
    $p2_c_han_kei = number_format(($p2_c_han_kei / $tani), $keta);
    $p2_b_han_kei = number_format(($p2_b_han_kei / $tani), $keta);
}

    ///// 今期累計
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='試修販管費経費'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_s_han_kei) < 1) {
    $rui_s_han_kei        = 0;                      // 検索失敗
    $rui_s_han_kei_sagaku = 0;
} else {
    $rui_s_han_kei_sagaku = $rui_s_han_kei;
    $rui_s_han_kei        = number_format(($rui_s_han_kei / $tani), $keta);
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='商管販管費経費'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_b_han_kei_sagaku) < 1) {
    $rui_b_han_kei_sagaku = 0;                      // 検索失敗
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='全体経費'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_all_han_kei) < 1) {
    $rui_all_han_kei = 0;                           // 検索失敗
} else {
    $rui_all_han_kei = number_format(($rui_all_han_kei / $tani), $keta);
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='カプラ経費'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_c_han_kei) < 1) {
    $rui_c_han_kei = 0;                             // 検索失敗
} else {
    //$rui_c_han_kei = number_format(($rui_c_han_kei / $tani), $keta);
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='リニア経費'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_l_han_kei) < 1) {
    $rui_l_han_kei = 0 - $rui_s_han_kei_sagaku;     // 検索失敗
} else {
    $rui_l_han_kei = $rui_l_han_kei - $rui_s_han_kei_sagaku;
    $rui_l_han_kei = number_format(($rui_l_han_kei / $tani), $keta);
}
    ///// 今期累計 商管
$query = sprintf("select sum(orign_kin) from act_allo_history where pl_bs_ym>=%d and pl_bs_ym<=%d and actcod>=7501 and actcod<=8000 and orign_id=670", $str_ym, $yyyymm);
if (getUniResult($query, $rui_b_han_kei) < 1) {
    $rui_b_han_kei = 0 + $rui_b_han_kei_sagaku;         // 検索失敗
    $rui_b_han_all = $rui_b_han_all + $rui_b_han_kei;
    $rui_b_sagaku  = $rui_b_sagaku + $rui_b_han_kei;    // カプラ差額計算用
} else {
    $rui_b_han_kei = $rui_b_han_kei + $rui_b_han_kei_sagaku;
    $rui_b_han_all = $rui_b_han_all + $rui_b_han_kei;
    $rui_c_han_kei = $rui_c_han_kei - $rui_b_han_kei;
    $rui_b_sagaku  = $rui_b_sagaku + $rui_b_han_kei;    // カプラ差額計算用
    $rui_c_han_kei = number_format(($rui_c_han_kei / $tani), $keta);
    $rui_b_han_kei = number_format(($rui_b_han_kei / $tani), $keta);
}

/********** 販管費の合計 **********/
    ///// 当月
    ///// 試験・修理
    $s_han_all        = $s_han_jin_sagaku + $s_han_kei_sagaku;
    $s_han_all_sagaku = $s_han_all;
    $s_han_all        = number_format(($s_han_all / $tani), $keta);
    ///// CL
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='全体販管費'", $yyyymm);
if (getUniResult($query, $all_han_all) < 1) {
    $all_han_all = 0;                           // 検索失敗
} else {
    $all_han_all = number_format(($all_han_all / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ販管費'", $yyyymm);
if (getUniResult($query, $c_han_all) < 1) {
    $c_han_all = 0;                             // 検索失敗
} else {
    $c_han_all = $c_han_all - $b_han_all + $c_allo_kin + $l_allo_kin - $c_allo_kin;
    $c_han_all = number_format(($c_han_all / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア販管費'", $yyyymm);
if (getUniResult($query, $l_han_all) < 1) {
    $l_han_all = 0 - $s_han_all_sagaku;         // 検索失敗
} else {
    $l_han_all = $l_han_all - $s_han_all_sagaku - $l_allo_kin;
    $l_han_all = number_format(($l_han_all / $tani), $keta);
}

    ///// 前月
    ///// 試験・修理
    $p1_s_han_all        = $p1_s_han_jin_sagaku + $p1_s_han_kei_sagaku;
    $p1_s_han_all_sagaku = $p1_s_han_all;
    $p1_s_han_all        = number_format(($p1_s_han_all / $tani), $keta);
    ///// CL
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='全体販管費'", $p1_ym);
if (getUniResult($query, $p1_all_han_all) < 1) {
    $p1_all_han_all = 0;                        // 検索失敗
} else {
    $p1_all_han_all = number_format(($p1_all_han_all / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ販管費'", $p1_ym);
if (getUniResult($query, $p1_c_han_all) < 1) {
    $p1_c_han_all = 0;                          // 検索失敗
} else {
    $p1_c_han_all = $p1_c_han_all - $p1_b_han_all + $p1_c_allo_kin + $p1_l_allo_kin - $p1_c_allo_kin;
    $p1_c_han_all = number_format(($p1_c_han_all / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア販管費'", $p1_ym);
if (getUniResult($query, $p1_l_han_all) < 1) {
    $p1_l_han_all = 0 - $p1_s_han_all_sagaku;   // 検索失敗
} else {
    $p1_l_han_all = $p1_l_han_all - $p1_s_han_all_sagaku - $p1_l_allo_kin;
    $p1_l_han_all = number_format(($p1_l_han_all / $tani), $keta);
}

    ///// 前前月
    ///// 試験・修理
    $p2_s_han_all        = $p2_s_han_jin_sagaku + $p2_s_han_kei_sagaku;
    $p2_s_han_all_sagaku = $p2_s_han_all;
    $p2_s_han_all        = number_format(($p2_s_han_all / $tani), $keta);
    ///// CL
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='全体販管費'", $p2_ym);
if (getUniResult($query, $p2_all_han_all) < 1) {
    $p2_all_han_all = 0;                        // 検索失敗
} else {
    $p2_all_han_all = number_format(($p2_all_han_all / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ販管費'", $p2_ym);
if (getUniResult($query, $p2_c_han_all) < 1) {
    $p2_c_han_all = 0;                          // 検索失敗
} else {
    $p2_c_han_all = $p2_c_han_all - $p2_b_han_all + $p2_c_allo_kin + $p2_l_allo_kin - $p2_c_allo_kin;
    $p2_c_han_all = number_format(($p2_c_han_all / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア販管費'", $p2_ym);
if (getUniResult($query, $p2_l_han_all) < 1) {
    $p2_l_han_all = 0 - $p2_s_han_all_sagaku;   // 検索失敗
} else {
    $p2_l_han_all = $p2_l_han_all - $p2_s_han_all_sagaku - $p2_l_allo_kin;
    $p2_l_han_all = number_format(($p2_l_han_all / $tani), $keta);
}

    ///// 今期累計
    ///// 試験・修理
    $rui_s_han_all        = $rui_s_han_jin_sagaku + $rui_s_han_kei_sagaku;
    $rui_s_han_all_sagaku = $rui_s_han_all;
    $rui_s_han_all        = number_format(($rui_s_han_all / $tani), $keta);
    ///// CL
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='全体販管費'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_all_han_all) < 1) {
    $rui_all_han_all = 0;                       // 検索失敗
} else {
    $rui_all_han_all = number_format(($rui_all_han_all / $tani), $keta);
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='カプラ販管費'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_c_han_all) < 1) {
    $rui_c_han_all = 0;                         // 検索失敗
} else {
    $rui_c_han_all = $rui_c_han_all - $rui_b_han_all + $rui_c_allo_kin + $rui_l_allo_kin - $rui_c_allo_kin;
    $rui_c_han_all = number_format(($rui_c_han_all / $tani), $keta);
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='リニア販管費'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_l_han_all) < 1) {
    $rui_l_han_all = 0 - $rui_s_han_all_sagaku; // 検索失敗
} else {
    $rui_l_han_all = $rui_l_han_all - $rui_s_han_all_sagaku - $rui_l_allo_kin;
    $rui_l_han_all = number_format(($rui_l_han_all / $tani), $keta);
}

/********** 営業利益 **********/
    ///// 商管
// 2013/11/07 追加 2013年10月度 業務委託費（横川派遣料）調整
if ($p2_ym == 201310) {
    $p2_b_gross_profit -= 1245035;
}
if ($p2_ym == 201311) {
    $p2_b_gross_profit += 1245035;
}
if ($p2_ym == 201408) {
    $p2_b_gross_profit += 841368;
}
$p2_b_ope_profit    = $p2_b_gross_profit - $p2_b_han_all;
$p2_b_han_all       = number_format(($p2_b_han_all / $tani), $keta);
$p2_b_gross_profit  = number_format(($p2_b_gross_profit / $tani), $keta);

// 2013/11/07 追加 2013年10月度 業務委託費（横川派遣料）調整
if ($p1_ym == 201310) {
    $p1_b_gross_profit -= 1245035;
}
if ($p1_ym == 201311) {
    $p1_b_gross_profit += 1245035;
}
if ($p1_ym == 201408) {
    $p1_b_gross_profit += 841368;
}
$p1_b_ope_profit    = $p1_b_gross_profit - $p1_b_han_all;
$p1_b_han_all       = number_format(($p1_b_han_all / $tani), $keta);
$p1_b_gross_profit  = number_format(($p1_b_gross_profit / $tani), $keta);

// 2013/11/07 追加 2013年10月度 業務委託費（横川派遣料）調整
if ($yyyymm == 201310) {
    $b_gross_profit -= 1245035;
}
if ($yyyymm == 201311) {
    $b_gross_profit += 1245035;
}
if ($yyyymm == 201408) {
    $b_gross_profit += 841368;
}
$b_ope_profit       = $b_gross_profit - $b_han_all;
$b_han_all          = number_format(($b_han_all / $tani), $keta);
$b_gross_profit     = number_format(($b_gross_profit / $tani), $keta);

// 2013/11/07 追加 2013年10月度 業務委託費（横川派遣料）調整
if ($yyyymm >= 201310 && $yyyymm <= 201403) {
    $rui_b_gross_profit -= 1245035;
}
if ($yyyymm >= 201311 && $yyyymm <= 201403) {
    $rui_b_gross_profit += 1245035;
}
if ($yyyymm >= 201408 && $yyyymm <= 201503) {
    $rui_b_gross_profit += 841368;
}
$rui_b_ope_profit   = $rui_b_gross_profit - $rui_b_han_all;
$rui_b_han_all      = number_format(($rui_b_han_all / $tani), $keta);
$rui_b_gross_profit = number_format(($rui_b_gross_profit / $tani), $keta);
    ///// 試験・修理
$p2_s_ope_profit         = $p2_s_gross_profit_sagaku - $p2_s_han_all_sagaku;
$p2_s_ope_profit_sagaku  = $p2_s_ope_profit;
$p2_s_ope_profit         = $p2_s_ope_profit + $p2_sc_uri_sagaku - $p2_sc_metarial_sagaku;       // カプラ試験修理を加味（sagakuの後－リニアからマイナスしてしまう為）
if ($p2_ym == 200912) {
    $p2_s_ope_profit = $p2_s_ope_profit + 1409708;
}
if ($p2_ym >= 201001) {
    $p2_s_ope_profit = $p2_s_ope_profit + $p2_s_kyu_kei - $p2_s_kyu_kin;  // 添田さんの給与をC・Lは35%。試験修理に30%振分
    //$p2_s_ope_profit = $p2_s_ope_profit + 432323 - 129697;  // 添田さんの給与をC・Lは35%。試験修理に30%振分
}
$p2_s_ope_profit         = number_format(($p2_s_ope_profit / $tani), $keta);

$p1_s_ope_profit         = $p1_s_gross_profit_sagaku - $p1_s_han_all_sagaku;
$p1_s_ope_profit_sagaku  = $p1_s_ope_profit;
$p1_s_ope_profit         = $p1_s_ope_profit + $p1_sc_uri_sagaku - $p1_sc_metarial_sagaku;       // カプラ試験修理を加味（sagakuの後－リニアからマイナスしてしまう為）
if ($p1_ym == 200912) {
    $p1_s_ope_profit = $p1_s_ope_profit + 1409708;
}
if ($p1_ym >= 201001) {
    $p1_s_ope_profit = $p1_s_ope_profit + $p1_s_kyu_kei - $p1_s_kyu_kin;  // 添田さんの給与をC・Lは35%。試験修理に30%振分
    //$p1_s_ope_profit = $p1_s_ope_profit + 432323 - 129697;  // 添田さんの給与をC・Lは35%。試験修理に30%振分
}
$p1_s_ope_profit         = number_format(($p1_s_ope_profit / $tani), $keta);

$s_ope_profit            = $s_gross_profit_sagaku - $s_han_all_sagaku;
$s_ope_profit_sagaku     = $s_ope_profit;
$s_ope_profit            = $s_ope_profit + $sc_uri_sagaku - $sc_metarial_sagaku;                // カプラ試験修理を加味（sagakuの後－リニアからマイナスしてしまう為）
if ($yyyymm == 200912) {
    $s_ope_profit = $s_ope_profit + 1409708;
}
if ($yyyymm >= 201001) {
    $s_ope_profit = $s_ope_profit + $s_kyu_kei - $s_kyu_kin;  // 添田さんの給与をC・Lは35%。試験修理に30%振分
    //$s_ope_profit = $s_ope_profit + 432323 - 129697;  // 添田さんの給与をC・Lは35%。試験修理に30%振分
}
$s_ope_profit            = number_format(($s_ope_profit / $tani), $keta);

$rui_s_ope_profit        = $rui_s_gross_profit_sagaku - $rui_s_han_all_sagaku;
$rui_s_ope_profit_sagaku = $rui_s_ope_profit;
$rui_s_ope_profit        = $rui_s_ope_profit + $rui_sc_uri_sagaku - $rui_sc_metarial_sagaku;    // カプラ試験修理を加味（sagakuの後－リニアからマイナスしてしまう為）
if ($yyyymm >= 200912 && $yyyymm <= 201003) {
    $rui_s_ope_profit = $rui_s_ope_profit + 1409708;
}
if ($yyyymm >= 201001) {
    $rui_s_ope_profit = $rui_s_ope_profit + $rui_s_kyu_kei - $rui_s_kyu_kin;  // 添田さんの給与をC・Lは35%。試験修理に30%振分
    //$rui_s_ope_profit = $rui_s_ope_profit + 432323 - 129697;  // 添田さんの給与をC・Lは35%。試験修理に30%振分
}
$rui_s_ope_profit        = number_format(($rui_s_ope_profit / $tani), $keta);

    ///// 当月
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='全体営業利益'", $yyyymm);
if (getUniResult($query, $all_ope_profit) < 1) {
    $all_ope_profit = 0;                        // 検索失敗
} else {
    if ($yyyymm == 200906) {
        $all_ope_profit = $all_ope_profit + $b_uri_sagaku - 3100900;
    } elseif ($yyyymm == 200905) {
        $all_ope_profit = $all_ope_profit + $b_uri_sagaku + 1550450;
    } elseif ($yyyymm == 200904) {
        $all_ope_profit = $all_ope_profit + $b_uri_sagaku + 1550450;
    } else {
        $all_ope_profit = $all_ope_profit + $b_uri_sagaku;
    }
    // 2012/02/08 追加 2012年1月度 業務委託費（平出横川派遣料）調整
    if ($yyyymm == 201201) {
        $all_ope_profit -=1156130;
    }
    // 2012/03/05 追加 2012年1月度 業務委託費（平出横川派遣料）調整 戻し
    if ($yyyymm == 201202) {
        $all_ope_profit +=1156130;
    }
    $all_ope_profit = number_format(($all_ope_profit / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ営業利益'", $yyyymm);
if (getUniResult($query, $c_ope_profit) < 1) {
    $c_ope_profit = 0;                          // 検索失敗
    $c_ope_profit_temp = 0;
} else {
    $c_ope_profit = $c_ope_profit + $b_sagaku + $c_allo_kin - $sc_uri_sagaku + $sc_metarial_sagaku; // カプラ試験修理を加味
    if ($yyyymm == 200912) {
        $c_ope_profit = $c_ope_profit - 1227429;
    }
    if ($yyyymm >= 201001) {
        $c_ope_profit = $c_ope_profit - $c_kyu_kin; // 添田さんの給与をC・Lは35%。試験修理に30%振分
        //$c_ope_profit = $c_ope_profit - 151313; // 添田さんの給与をC・Lは35%。試験修理に30%振分
    }
    // 2013/11/07 追加 2013年10月度 業務委託費（横川派遣料）調整
    if ($yyyymm == 201310) {
        $c_ope_profit += 1245035;
    }
    if ($yyyymm == 201311) {
        $c_ope_profit -= 1245035;
    }
    if ($yyyymm == 201408) {
        $c_ope_profit +=229464;
    }
    $c_ope_profit_temp = $c_ope_profit;
    $c_ope_profit      = number_format(($c_ope_profit / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア営業利益'", $yyyymm);
if (getUniResult($query, $l_ope_profit) < 1) {
    if ($yyyymm == 200906) {
        $l_ope_profit = 0 - $s_ope_profit_sagaku - 3100900;     // 検索失敗
    } elseif ($yyyymm == 200905) {
        $l_ope_profit = 0 - $s_ope_profit_sagaku + 1550450;     // 検索失敗
    } elseif ($yyyymm == 200904) {
        $l_ope_profit = 0 - $s_ope_profit_sagaku + 1550450;     // 検索失敗
    } else {
        $l_ope_profit = 0 - $s_ope_profit_sagaku;               // 検索失敗
    }
} else {
    if ($yyyymm == 200906) {
        $l_ope_profit = $l_ope_profit - $s_ope_profit_sagaku - 3100900 + $l_allo_kin;
    } elseif ($yyyymm == 200905) {
        $l_ope_profit = $l_ope_profit - $s_ope_profit_sagaku + 1550450 + $l_allo_kin;
    } elseif ($yyyymm == 200904) {
        $l_ope_profit = $l_ope_profit - $s_ope_profit_sagaku + 1550450 + $l_allo_kin;
    } else {
        $l_ope_profit = $l_ope_profit - $s_ope_profit_sagaku + $l_allo_kin;
    }
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
    $l_ope_profit = number_format(($l_ope_profit / $tani), $keta);
}
    ///// 前月
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='全体営業利益'", $p1_ym);
if (getUniResult($query, $p1_all_ope_profit) < 1) {
    $p1_all_ope_profit = 0;                     // 検索失敗
} else {
    if ($p1_ym == 200906) {
        $p1_all_ope_profit = $p1_all_ope_profit + $p1_b_uri_sagaku - 3100900;
    } elseif ($p1_ym == 200905) {
        $p1_all_ope_profit = $p1_all_ope_profit + $p1_b_uri_sagaku + 1550450;
    } elseif ($p1_ym == 200904) {
        $p1_all_ope_profit = $p1_all_ope_profit + $p1_b_uri_sagaku + 1550450;
    } else {
        $p1_all_ope_profit = $p1_all_ope_profit + $p1_b_uri_sagaku;
    }
    // 2012/02/08 追加 2012年1月度 業務委託費（平出横川派遣料）調整
    if ($p1_ym == 201201) {
        $p1_all_ope_profit -=1156130;
    }
    // 2012/03/05 追加 2012年1月度 業務委託費（平出横川派遣料）調整 戻し
    if ($p1_ym == 201202) {
        $p1_all_ope_profit +=1156130;
    }
    $p1_all_ope_profit = number_format(($p1_all_ope_profit / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ営業利益'", $p1_ym);
if (getUniResult($query, $p1_c_ope_profit) < 1) {
    $p1_c_ope_profit = 0;                       // 検索失敗
} else {
    $p1_c_ope_profit = $p1_c_ope_profit + $p1_b_sagaku + $p1_c_allo_kin - $p1_sc_uri_sagaku + $p1_sc_metarial_sagaku; // カプラ試験修理を加味
    if ($p1_ym == 200912) {
        $p1_c_ope_profit = $p1_c_ope_profit - 1227429;
    }
    if ($p1_ym >= 201001) {
        $p1_c_ope_profit = $p1_c_ope_profit - $p1_c_kyu_kin;   // 添田さんの給与をC・Lは35%。試験修理に30%振分
        //$p1_c_ope_profit = $p1_c_ope_profit - 151313;   // 添田さんの給与をC・Lは35%。試験修理に30%振分
    }
    // 2013/11/07 追加 2013年10月度 業務委託費（横川派遣料）調整
    if ($p1_ym == 201310) {
        $p1_c_ope_profit += 1245035;
    }
    if ($p1_ym == 201311) {
        $p1_c_ope_profit -= 1245035;
    }
    if ($p1_ym == 201408) {
        $p1_c_ope_profit +=229464;
    }
    $p1_c_ope_profit_temp = $p1_c_ope_profit;
    $p1_c_ope_profit = number_format(($p1_c_ope_profit / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア営業利益'", $p1_ym);
if (getUniResult($query, $p1_l_ope_profit) < 1) {
    if ($p1_ym == 200906) {
        $p1_l_ope_profit = 0 - $p1_s_ope_profit_sagaku - 3100900;   // 検索失敗
    } elseif ($p1_ym == 200905) {
        $p1_l_ope_profit = 0 - $p1_s_ope_profit_sagaku + 1550450;   // 検索失敗
    } elseif ($p1_ym == 200904) {
        $p1_l_ope_profit = 0 - $p1_s_ope_profit_sagaku + 1550450;   // 検索失敗
    } else {
        $p1_l_ope_profit = 0 - $p1_s_ope_profit_sagaku;             // 検索失敗
    }
} else {
    if ($p1_ym == 200906) {
        $p1_l_ope_profit = $p1_l_ope_profit - $p1_s_ope_profit_sagaku - 3100900 + $p1_l_allo_kin;
    } elseif ($p1_ym == 200905) {
        $p1_l_ope_profit = $p1_l_ope_profit - $p1_s_ope_profit_sagaku + 1550450 + $p1_l_allo_kin;
    } elseif ($p1_ym == 200904) {
        $p1_l_ope_profit = $p1_l_ope_profit - $p1_s_ope_profit_sagaku + 1550450 + $p1_l_allo_kin;
    } else {
        $p1_l_ope_profit = $p1_l_ope_profit - $p1_s_ope_profit_sagaku + $p1_l_allo_kin;
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
    $p1_l_ope_profit = number_format(($p1_l_ope_profit / $tani), $keta);
}
    ///// 前前月
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='全体営業利益'", $p2_ym);
if (getUniResult($query, $p2_all_ope_profit) < 1) {
    $p2_all_ope_profit = 0;                     // 検索失敗
} else {
    if ($p2_ym == 200906) {
        $p2_all_ope_profit = $p2_all_ope_profit + $p2_b_uri_sagaku - 3100900;
    } elseif ($p2_ym == 200905) {
        $p2_all_ope_profit = $p2_all_ope_profit + $p2_b_uri_sagaku + 1550450;
    } elseif ($p2_ym == 200904) {
        $p2_all_ope_profit = $p2_all_ope_profit + $p2_b_uri_sagaku + 1550450;
    } else {
        $p2_all_ope_profit = $p2_all_ope_profit + $p2_b_uri_sagaku;
    }
    // 2012/02/08 追加 2012年1月度 業務委託費（平出横川派遣料）調整
    if ($p2_ym == 201201) {
        $p2_all_ope_profit -=1156130;
    }
    // 2012/03/05 追加 2012年1月度 業務委託費（平出横川派遣料）調整 戻し
    if ($p2_ym == 201202) {
        $p2_all_ope_profit +=1156130;
    }
    $p2_all_ope_profit = number_format(($p2_all_ope_profit / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ営業利益'", $p2_ym);
if (getUniResult($query, $p2_c_ope_profit) < 1) {
    $p2_c_ope_profit = 0;                       // 検索失敗
} else {
    $p2_c_ope_profit = $p2_c_ope_profit + $p2_b_sagaku + $p2_c_allo_kin - $p2_sc_uri_sagaku + $p2_sc_metarial_sagaku; // カプラ試験修理を加味
    if ($p2_ym == 200912) {
        $p2_c_ope_profit = $p2_c_ope_profit - 1227429;
    }
    if ($p2_ym >= 201001) {
        $p2_c_ope_profit = $p2_c_ope_profit - $p2_c_kyu_kin;   // 添田さんの給与をC・Lは35%。試験修理に30%振分
        //$p2_c_ope_profit = $p2_c_ope_profit - 151313;   // 添田さんの給与をC・Lは35%。試験修理に30%振分
    }
    // 2013/11/07 追加 2013年10月度 業務委託費（横川派遣料）調整
    if ($p2_ym == 201310) {
        $p2_c_ope_profit += 1245035;
    }
    if ($p2_ym == 201311) {
        $p2_c_ope_profit -= 1245035;
    }
    if ($p2_ym == 201408) {
        $p2_c_ope_profit +=229464;
    }
    $p2_c_ope_profit_temp = $p2_c_ope_profit;
    $p2_c_ope_profit = number_format(($p2_c_ope_profit / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア営業利益'", $p2_ym);
if (getUniResult($query, $p2_l_ope_profit) < 1) {
    if ($p2_ym == 200906) {
        $p2_l_ope_profit = 0 - $p2_s_ope_profit_sagaku - 3100900;   // 検索失敗
    } elseif ($p2_ym == 200905) {
        $p2_l_ope_profit = 0 - $p2_s_ope_profit_sagaku + 1550450;   // 検索失敗
    } elseif ($p2_ym == 200904) {
        $p2_l_ope_profit = 0 - $p2_s_ope_profit_sagaku + 1550450;   // 検索失敗
    } else {
        $p2_l_ope_profit = 0 - $p2_s_ope_profit_sagaku;             // 検索失敗
    }
} else {
    if ($p2_ym == 200906) {
        $p2_l_ope_profit = $p2_l_ope_profit - $p2_s_ope_profit_sagaku - 3100900 + $p2_l_allo_kin;
    } elseif ($p2_ym == 200905) {
        $p2_l_ope_profit = $p2_l_ope_profit - $p2_s_ope_profit_sagaku + 1550450 + $p2_l_allo_kin;
    } elseif ($p2_ym == 200904) {
        $p2_l_ope_profit = $p2_l_ope_profit - $p2_s_ope_profit_sagaku + 1550450 + $p2_l_allo_kin;
    } else {
        $p2_l_ope_profit = $p2_l_ope_profit - $p2_s_ope_profit_sagaku + $p2_l_allo_kin;
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
    $p2_l_ope_profit = number_format(($p2_l_ope_profit / $tani), $keta);
}
    ///// 今期累計
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='全体営業利益'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_all_ope_profit) < 1) {
    $rui_all_ope_profit = 0;                    // 検索失敗
} else {
    if ($yyyymm == 200905) {
        $rui_all_ope_profit = $rui_all_ope_profit + 3100900;
    } elseif ($yyyymm == 200904) {
        $rui_all_ope_profit = $rui_all_ope_profit + 1550450;
    }
    // 2012/02/08 追加 2012年1月度 業務委託費（平出横川派遣料）調整
    if ($yyyymm >= 201201 && $yyyymm <= 201203) {
        $rui_all_ope_profit -=1156130;
    }
    // 2012/03/05 追加 2012年1月度 業務委託費（平出横川派遣料）調整 戻し
    if ($yyyymm >= 201202 && $yyyymm <= 201203) {
        $rui_all_ope_profit +=1156130;
    }
    $rui_all_ope_profit = $rui_all_ope_profit + $rui_b_uri_sagaku;
    $rui_all_ope_profit = number_format(($rui_all_ope_profit / $tani), $keta);
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='カプラ営業利益'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_c_ope_profit) < 1) {
    $rui_c_ope_profit = 0;                      // 検索失敗
} else {
    $rui_c_ope_profit = $rui_c_ope_profit + $rui_b_sagaku + $rui_c_allo_kin - $rui_sc_uri_sagaku + $rui_sc_metarial_sagaku; // カプラ試験修理を加味
    if ($yyyymm >= 200912 && $yyyymm <= 201003) {
        $rui_c_ope_profit = $rui_c_ope_profit - 1227429;
    }
    if ($yyyymm >= 201001) {
        $rui_c_ope_profit = $rui_c_ope_profit - $rui_c_kyu_kin; // 添田さんの給与をC・Lは35%。試験修理に30%振分
        //$rui_c_ope_profit = $rui_c_ope_profit - 151313; // 添田さんの給与をC・Lは35%。試験修理に30%振分
    }
    // 2013/11/07 追加 2013年10月度 業務委託費（横川派遣料）調整
    if ($yyyymm >= 201310 && $yyyymm <= 201403) {
        $rui_c_ope_profit += 1245035;
    }
    if ($yyyymm >= 201311 && $yyyymm <= 201403) {
        $rui_c_ope_profit -= 1245035;
    }
    if ($yyyymm >= 201408 && $yyyymm <= 201503) {
        $rui_c_ope_profit +=229464;
    }
    $rui_c_ope_profit = number_format(($rui_c_ope_profit / $tani), $keta);
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='リニア営業利益'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_l_ope_profit) < 1) {
    $rui_l_ope_profit = 0 - $rui_s_ope_profit_sagaku;   // 検索失敗
    $rui_l_ope_profit_temp = $rui_l_ope_profit;         // 経常利益計算用
} else {
    $rui_l_ope_profit = $rui_l_ope_profit - $rui_s_ope_profit_sagaku + $rui_l_allo_kin;
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
        $rui_l_ope_profit -=229464;
    }
    $rui_l_ope_profit_temp = $rui_l_ope_profit;         // 経常利益計算用
    $rui_l_ope_profit = number_format(($rui_l_ope_profit / $tani), $keta);
}

/********** 営業外収益の業務委託収入 **********/
    ///// 当月
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='商管業務委託収入再計算'", $yyyymm);
    if (getUniResult($query, $b_gyoumu) < 1) {
        $b_gyoumu      = 0;                       // 検索失敗
        $b_gyoumu_temp = 0;
    } else {
        if ($yyyymm == 201001) {
            $b_gyoumu = $b_gyoumu + 63096;
        }
        $b_gyoumu_temp = $b_gyoumu;
    }
} else {
    $b_gyoumu     = 0;
}
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修業務委託収入再計算'", $yyyymm);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修業務委託収入'", $yyyymm);
}
if (getUniResult($query, $s_gyoumu) < 1) {
    $s_gyoumu        = 0;                       // 検索失敗
    $s_gyoumu_sagaku = 0;
} else {
    $s_gyoumu_sagaku = $s_gyoumu;
    if ($yyyymm == 200912) {
        $s_gyoumu = $s_gyoumu - 722;
    }
    if ($yyyymm == 201001) {
        $s_gyoumu = $s_gyoumu + 29125;
    }
    $s_gyoumu        = number_format(($s_gyoumu / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='全体業務委託収入'", $yyyymm);
if (getUniResult($query, $all_gyoumu) < 1) {
    $all_gyoumu = 0;                            // 検索失敗
} else {
    if ($yyyymm == 200906) {
        $all_gyoumu = $all_gyoumu + 3100900;
    } elseif ($yyyymm == 200905) {
        $all_gyoumu = $all_gyoumu - 1550450;
    } elseif ($yyyymm == 200904) {
        $all_gyoumu = $all_gyoumu - 1550450;
    }
    if ($yyyymm == 200912) {
        $all_gyoumu = $all_gyoumu - 466000;
    }
    if ($yyyymm == 201001) {
        $all_gyoumu = $all_gyoumu + 466000;
    }
    $all_gyoumu = number_format(($all_gyoumu / $tani), $keta);
}
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ業務委託収入再計算'", $yyyymm);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ業務委託収入'", $yyyymm);
}
if (getUniResult($query, $c_gyoumu) < 1) {
    $c_gyoumu = 0;                              // 検索失敗
} else {
    if ($yyyymm == 200912) {
        $c_gyoumu = $c_gyoumu - 389809;
    }
    if ($yyyymm == 201001) {
        $c_gyoumu = $c_gyoumu + 315529;
    }
    $c_gyoumu = number_format(($c_gyoumu / $tani), $keta);
}
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア業務委託収入再計算'", $yyyymm);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア業務委託収入'", $yyyymm);
}
if (getUniResult($query, $l_gyoumu) < 1) {
    if ($yyyymm == 200906) {
        $l_gyoumu = 0 - $s_gyoumu_sagaku + 3100900;     // 検索失敗
    } elseif ($yyyymm == 200905) {
        $l_gyoumu = 0 - $s_gyoumu_sagaku - 1550450;     // 検索失敗
    } elseif ($yyyymm == 200904) {
        $l_gyoumu = 0 - $s_gyoumu_sagaku - 1550450;     // 検索失敗
    } else {
        $l_gyoumu = 0 - $s_gyoumu_sagaku;               // 検索失敗
    }
} else {
    if ($yyyymm == 200906) {
        $l_gyoumu = $l_gyoumu - $s_gyoumu_sagaku + 3100900;
    } elseif ($yyyymm == 200905) {
        $l_gyoumu = $l_gyoumu - $s_gyoumu_sagaku - 1550450;
    } elseif ($yyyymm == 200904) {
        $l_gyoumu = $l_gyoumu - $s_gyoumu_sagaku - 1550450;
    } elseif($yyyymm < 201001) {
        $l_gyoumu = $l_gyoumu - $s_gyoumu_sagaku;
    }
    if ($yyyymm == 200912) {
        $l_gyoumu = $l_gyoumu - 75469;
    }
    if ($yyyymm == 201001) {
        $l_gyoumu = $l_gyoumu + 58250;
    }
    $l_gyoumu = number_format(($l_gyoumu / $tani), $keta);
}
    ///// 前月
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='商管業務委託収入再計算'", $p1_ym);
    if (getUniResult($query, $p1_b_gyoumu) < 1) {
        $p1_b_gyoumu      = 0;                       // 検索失敗
        $p1_b_gyoumu_temp = 0;
    } else {
        if ($p1_ym == 201001) {
            $p1_b_gyoumu = $p1_b_gyoumu + 63096;
        }
        $p1_b_gyoumu_temp = $p1_b_gyoumu;
    }
} else {
    $p1_b_gyoumu     = 0;
}
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修業務委託収入再計算'", $p1_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修業務委託収入'", $p1_ym);
}
if (getUniResult($query, $p1_s_gyoumu) < 1) {
    $p1_s_gyoumu        = 0;                       // 検索失敗
    $p1_s_gyoumu_sagaku = 0;
} else {
    $p1_s_gyoumu_sagaku = $p1_s_gyoumu;
    if ($p1_ym == 200912) {
        $p1_s_gyoumu = $p1_s_gyoumu - 722;
    }
    if ($p1_ym == 201001) {
        $p1_s_gyoumu = $p1_s_gyoumu + 29125;
    }
    $p1_s_gyoumu        = number_format(($p1_s_gyoumu / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='全体業務委託収入'", $p1_ym);
if (getUniResult($query, $p1_all_gyoumu) < 1) {
    $p1_all_gyoumu = 0;                         // 検索失敗
} else {
    if ($p1_ym == 200906) {
        $p1_all_gyoumu = $p1_all_gyoumu + 3100900;
    } elseif ($p1_ym == 200905) {
        $p1_all_gyoumu = $p1_all_gyoumu - 1550450;
    } elseif ($p1_ym == 200904) {
        $p1_all_gyoumu = $p1_all_gyoumu - 1550450;
    }
    if ($p1_ym == 200912) {
        $p1_all_gyoumu = $p1_all_gyoumu - 466000;
    }
    if ($p1_ym == 201001) {
        $p1_all_gyoumu = $p1_all_gyoumu + 466000;
    }
    $p1_all_gyoumu = number_format(($p1_all_gyoumu / $tani), $keta);
}
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ業務委託収入再計算'", $p1_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ業務委託収入'", $p1_ym);
}
if (getUniResult($query, $p1_c_gyoumu) < 1) {
    $p1_c_gyoumu = 0;                              // 検索失敗
} else {
    if ($p1_ym == 200912) {
        $p1_c_gyoumu = $p1_c_gyoumu - 389809;
    }
    if ($p1_ym == 201001) {
        $p1_c_gyoumu = $p1_c_gyoumu + 315529;
    }
    $p1_c_gyoumu = number_format(($p1_c_gyoumu / $tani), $keta);
}
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア業務委託収入再計算'", $p1_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア業務委託収入'", $p1_ym);
}
if (getUniResult($query, $p1_l_gyoumu) < 1) {
    if ($p1_ym == 200906) {
        $p1_l_gyoumu = 0 - $p1_s_gyoumu_sagaku + 3100900;     // 検索失敗
    } elseif ($p1_ym == 200905) {
        $p1_l_gyoumu = 0 - $p1_s_gyoumu_sagaku - 1550450;     // 検索失敗
    } elseif ($p1_ym == 200904) {
        $p1_l_gyoumu = 0 - $p1_s_gyoumu_sagaku - 1550450;     // 検索失敗
    } else {
        $p1_l_gyoumu = 0 - $p1_s_gyoumu_sagaku;               // 検索失敗
    }
} else {
    if ($p1_ym == 200906) {
        $p1_l_gyoumu = $p1_l_gyoumu - $p1_s_gyoumu_sagaku + 3100900;
    } elseif ($p1_ym == 200905) {
        $p1_l_gyoumu = $p1_l_gyoumu - $p1_s_gyoumu_sagaku - 1550450;
    } elseif ($p1_ym == 200904) {
        $p1_l_gyoumu = $p1_l_gyoumu - $p1_s_gyoumu_sagaku - 1550450;
    } elseif($p1_ym < 201001) {
        $p1_l_gyoumu = $p1_l_gyoumu - $p1_s_gyoumu_sagaku;
    }
    if ($p1_ym == 200912) {
        $p1_l_gyoumu = $p1_l_gyoumu - 75469;
    }
    if ($p1_ym == 201001) {
        $p1_l_gyoumu = $p1_l_gyoumu + 58250;
    }
    $p1_l_gyoumu = number_format(($p1_l_gyoumu / $tani), $keta);
}
    ///// 前前月
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='商管業務委託収入再計算'", $p2_ym);
    if (getUniResult($query, $p2_b_gyoumu) < 1) {
        $p2_b_gyoumu      = 0;                       // 検索失敗
        $p2_b_gyoumu_temp = 0;
    } else {
        if ($p2_ym == 201001) {
            $p2_b_gyoumu = $p2_b_gyoumu + 63096;
        }
        $p2_b_gyoumu_temp = $p2_b_gyoumu;
    }
} else {
    $p2_b_gyoumu     = 0;
}
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修業務委託収入再計算'", $p2_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修業務委託収入'", $p2_ym);
}
if (getUniResult($query, $p2_s_gyoumu) < 1) {
    $p2_s_gyoumu        = 0;                       // 検索失敗
    $p2_s_gyoumu_sagaku = 0;
} else {
    $p2_s_gyoumu_sagaku = $p2_s_gyoumu;
    if ($p2_ym == 200912) {
        $p2_s_gyoumu = $p2_s_gyoumu - 722;
    }
    if ($p2_ym == 201001) {
        $p2_s_gyoumu = $p2_s_gyoumu + 29125;
    }
    $p2_s_gyoumu        = number_format(($p2_s_gyoumu / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='全体業務委託収入'", $p2_ym);
if (getUniResult($query, $p2_all_gyoumu) < 1) {
    $p2_all_gyoumu = 0;   // 検索失敗
} else {
    if ($p2_ym == 200906) {
        $p2_all_gyoumu = $p2_all_gyoumu + 3100900;
    } elseif ($p2_ym == 200905) {
        $p2_all_gyoumu = $p2_all_gyoumu - 1550450;
    } elseif ($p2_ym == 200904) {
        $p2_all_gyoumu = $p2_all_gyoumu - 1550450;
    }
    if ($p2_ym == 200912) {
        $p2_all_gyoumu = $p2_all_gyoumu - 466000;
    }
    if ($p2_ym == 201001) {
        $p2_all_gyoumu = $p2_all_gyoumu + 466000;
    }
    $p2_all_gyoumu = number_format(($p2_all_gyoumu / $tani), $keta);
}
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ業務委託収入再計算'", $p2_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ業務委託収入'", $p2_ym);
}
if (getUniResult($query, $p2_c_gyoumu) < 1) {
    $p2_c_gyoumu = 0;                              // 検索失敗
} else {
    if ($p2_ym == 200912) {
        $p2_c_gyoumu = $p2_c_gyoumu - 389809;
    }
    if ($p2_ym == 201001) {
        $p2_c_gyoumu = $p2_c_gyoumu + 315529;
    }
    $p2_c_gyoumu = number_format(($p2_c_gyoumu / $tani), $keta);
}
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア業務委託収入再計算'", $p2_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア業務委託収入'", $p2_ym);
}
if (getUniResult($query, $p2_l_gyoumu) < 1) {
    if ($p2_ym == 200906) {
        $p2_l_gyoumu = 0 - $p2_s_gyoumu_sagaku + 3100900;     // 検索失敗
    } elseif ($p2_ym == 200905) {
        $p2_l_gyoumu = 0 - $p2_s_gyoumu_sagaku - 1550450;     // 検索失敗
    } elseif ($p2_ym == 200904) {
        $p2_l_gyoumu = 0 - $p2_s_gyoumu_sagaku - 1550450;     // 検索失敗
    } else {
        $p2_l_gyoumu = 0 - $p2_s_gyoumu_sagaku;               // 検索失敗
    }
} else {
    if ($p2_ym == 200906) {
        $p2_l_gyoumu = $p2_l_gyoumu - $p2_s_gyoumu_sagaku + 3100900;
    } elseif ($p1_ym == 200905) {
        $p2_l_gyoumu = $p2_l_gyoumu - $p2_s_gyoumu_sagaku - 1550450;
    } elseif ($p1_ym == 200904) {
        $p2_l_gyoumu = $p2_l_gyoumu - $p2_s_gyoumu_sagaku - 1550450;
    } elseif($p2_ym < 201001) {
        $p2_l_gyoumu = $p2_l_gyoumu - $p2_s_gyoumu_sagaku;
    }
    if ($p2_ym == 200912) {
        $p2_l_gyoumu = $p2_l_gyoumu - 75469;
    }
    if ($p2_ym == 201001) {
        $p2_l_gyoumu = $p2_l_gyoumu + 58250;
    }
    $p2_l_gyoumu = number_format(($p2_l_gyoumu / $tani), $keta);
}
    ///// 今期累計
if ($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='商管業務委託収入再計算'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_b_gyoumu) < 1) {
        $rui_b_gyoumu = 0;                          // 検索失敗
    }
} elseif ($yyyymm >= 201001 && $yyyymm <= 201003) {
    $rui_b_gyoumu_a = 0;
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=201001 and pl_bs_ym<=%d and note='商管業務委託収入再計算'", $yyyymm);
    if (getUniResult($query, $rui_b_gyoumu_b) < 1) {
        $rui_b_gyoumu_b = 0;                          // 検索失敗
    }
    $rui_b_gyoumu = $rui_b_gyoumu_a + $rui_b_gyoumu_b;
    $rui_b_gyoumu = $rui_b_gyoumu + 63096;
} else {
    $rui_b_gyoumu = 0;
}
if ($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='試修業務委託収入再計算'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_s_gyoumu) < 1) {
        $rui_s_gyoumu = 0;                          // 検索失敗
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
        $rui_s_gyoumu_sagaku = $rui_s_gyoumu;
        if ($yyyymm >= 200912 && $yyyymm <= 201003) {
            $rui_s_gyoumu = $rui_s_gyoumu - 722;
        }
        if ($yyyymm >= 201001 && $yyyymm <= 201003) {
            $rui_s_gyoumu = $rui_s_gyoumu + 29125;
        }
        $rui_s_gyoumu = number_format(($rui_s_gyoumu / $tani), $keta);
    }
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='全体業務委託収入'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_all_gyoumu) < 1) {
    $rui_all_gyoumu = 0;                        // 検索失敗
} else {
    if ($yyyymm >= 200912 && $yyyymm <= 201003) {
        $rui_all_gyoumu = $rui_all_gyoumu - 466000;
    }
    if ($yyyymm >= 201001 && $yyyymm <= 201003) {
        $rui_all_gyoumu = $rui_all_gyoumu + 466000;
    }
    $rui_all_gyoumu = number_format(($rui_all_gyoumu / $tani), $keta);
}
if ($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='カプラ業務委託収入再計算'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_c_gyoumu) < 1) {
        $rui_c_gyoumu = 0;                          // 検索失敗
    } else {
        $rui_c_gyoumu = number_format(($rui_c_gyoumu / $tani), $keta);
    }
} elseif ($yyyymm >= 201001 && $yyyymm <= 201003) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=200904 and pl_bs_ym<=200912 and note='カプラ業務委託収入'");
    if (getUniResult($query, $rui_c_gyoumu_a) < 1) {
        $rui_c_gyoumu_a = 0;                          // 検索失敗
    }
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=201001 and pl_bs_ym<=%d and note='カプラ業務委託収入再計算'", $yyyymm);
    if (getUniResult($query, $rui_c_gyoumu_b) < 1) {
        $rui_c_gyoumu_b = 0;                          // 検索失敗
    }
    $rui_c_gyoumu = $rui_c_gyoumu_a + $rui_c_gyoumu_b;
    
    if ($yyyymm >= 200912 && $yyyymm <= 201003) {
        $rui_c_gyoumu = $rui_c_gyoumu - 389809;
    }
    if ($yyyymm >= 201001 && $yyyymm <= 201003) {
        $rui_c_gyoumu = $rui_c_gyoumu + 315529;
    }
    $rui_c_gyoumu = number_format(($rui_c_gyoumu / $tani), $keta);
} else {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='カプラ業務委託収入'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_c_gyoumu) < 1) {
        $rui_c_gyoumu = 0;                          // 検索失敗
    } else {
        if ($yyyymm >= 200912 && $yyyymm <= 201003) {
            $rui_c_gyoumu = $rui_c_gyoumu - 389809;
        }
        if ($yyyymm >= 201001 && $yyyymm <= 201003) {
            $rui_c_gyoumu = $rui_c_gyoumu + 315529;
        }
        $rui_c_gyoumu = number_format(($rui_c_gyoumu / $tani), $keta);
    }
}
if ($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='リニア業務委託収入再計算'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_l_gyoumu) < 1) {
        $rui_l_gyoumu = 0;                          // 検索失敗
    } else {
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
        $rui_l_gyoumu = $rui_l_gyoumu - 75469;
    }
    if ($yyyymm >= 201001 && $yyyymm <= 201003) {
        $rui_l_gyoumu = $rui_l_gyoumu + 58250;
    }
    $rui_l_gyoumu = $rui_l_gyoumu - $rui_s_gyoumu_a;
    $rui_l_gyoumu = number_format(($rui_l_gyoumu / $tani), $keta);
} else {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='リニア業務委託収入'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_l_gyoumu) < 1) {
        $rui_l_gyoumu = 0 - $rui_s_gyoumu_sagaku;   // 検索失敗
    } else {
        if ($yyyymm >= 200912 && $yyyymm <= 201003) {
            $rui_l_gyoumu = $rui_l_gyoumu - 75469;
        }
        if ($yyyymm >= 201001 && $yyyymm <= 201003) {
            $rui_l_gyoumu = $rui_l_gyoumu + 58250;
        }
        $rui_l_gyoumu = $rui_l_gyoumu - $rui_s_gyoumu_sagaku;
        $rui_l_gyoumu = number_format(($rui_l_gyoumu / $tani), $keta);
    }
}
/********** 営業外収益の仕入割引 **********/
    ///// 当月
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='商管仕入割引再計算'", $yyyymm);
    if (getUniResult($query, $b_swari) < 1) {
        $b_swari        = 0;                        // 検索失敗
        $b_swari_temp = 0;
    } else {
        $b_swari_temp = $b_swari;
    }
} else {
    $b_swari     = 0;
}
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
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='全体仕入割引'", $yyyymm);
if (getUniResult($query, $all_swari) < 1) {
    $all_swari = 0;                             // 検索失敗
} else {
    $all_swari = number_format(($all_swari / $tani), $keta);
}
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ仕入割引再計算'", $yyyymm);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ仕入割引'", $yyyymm);
}
if (getUniResult($query, $c_swari) < 1) {
    $c_swari = 0;                               // 検索失敗
} else {
    $c_swari = number_format(($c_swari / $tani), $keta);
}
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア仕入割引再計算'", $yyyymm);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア仕入割引'", $yyyymm);
}
if (getUniResult($query, $l_swari) < 1) {
    if ($yyyymm < 201001) {
        $l_swari = 0 - $s_swari_sagaku;             // 検索失敗
    } else {
        $l_swari = 0;             // 検索失敗
    }
} else {
    if ($yyyymm < 201001) {
        $l_swari = $l_swari - $s_swari_sagaku;
    }
    $l_swari = number_format(($l_swari / $tani), $keta);
}
    ///// 前月
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='商管仕入割引再計算'", $p1_ym);
    if (getUniResult($query, $p1_b_swari) < 1) {
        $p1_b_swari        = 0;                        // 検索失敗
        $p1_b_swari_temp = 0;
    } else {
        $p1_b_swari_temp = $p1_b_swari;
    }
} else {
    $p1_b_swari     = 0;
}
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
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='全体仕入割引'", $p1_ym);
if (getUniResult($query, $p1_all_swari) < 1) {
    $p1_all_swari = 0;                          // 検索失敗
} else {
    $p1_all_swari = number_format(($p1_all_swari / $tani), $keta);
}
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ仕入割引再計算'", $p1_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ仕入割引'", $p1_ym);
}
if (getUniResult($query, $p1_c_swari) < 1) {
    $p1_c_swari = 0;                               // 検索失敗
} else {
    $p1_c_swari = number_format(($p1_c_swari / $tani), $keta);
}
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア仕入割引再計算'", $p1_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア仕入割引'", $p1_ym);
}
if (getUniResult($query, $p1_l_swari) < 1) {
    if ($p1_ym < 201001) {
        $p1_l_swari = 0 - $p1_s_swari_sagaku;             // 検索失敗
    } else {
        $p1_l_swari = 0;             // 検索失敗
    }
} else {
    if ($p1_ym < 201001) {
        $p1_l_swari = $p1_l_swari - $p1_s_swari_sagaku;
    }
    $p1_l_swari = number_format(($p1_l_swari / $tani), $keta);
}
    ///// 前前月
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='商管仕入割引再計算'", $p2_ym);
    if (getUniResult($query, $p2_b_swari) < 1) {
        $p2_b_swari        = 0;                        // 検索失敗
        $p2_b_swari_temp = 0;
    } else {
        $p2_b_swari_temp = $p2_b_swari;
    }
} else {
    $p2_b_swari     = 0;
}
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
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='全体仕入割引'", $p2_ym);
if (getUniResult($query, $p2_all_swari) < 1) {
    $p2_all_swari = 0;                          // 検索失敗
} else {
    $p2_all_swari = number_format(($p2_all_swari / $tani), $keta);
}
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ仕入割引再計算'", $p2_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ仕入割引'", $p2_ym);
}
if (getUniResult($query, $p2_c_swari) < 1) {
    $p2_c_swari = 0;                               // 検索失敗
} else {
    $p2_c_swari = number_format(($p2_c_swari / $tani), $keta);
}
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア仕入割引再計算'", $p2_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア仕入割引'", $p2_ym);
}
if (getUniResult($query, $p2_l_swari) < 1) {
    if ($p2_ym < 201001) {
        $p2_l_swari = 0 - $p2_s_swari_sagaku;             // 検索失敗
    } else {
        $p2_l_swari = 0;             // 検索失敗
    }
} else {
    if ($p2_ym < 201001) {
        $p2_l_swari = $p2_l_swari - $p2_s_swari_sagaku;
    }
    $p2_l_swari = number_format(($p2_l_swari / $tani), $keta);
}
    ///// 今期累計
if ($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='商管仕入割引再計算'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_b_swari) < 1) {
        $rui_b_swari = 0;                           // 検索失敗
    }
} elseif ($yyyymm >= 201001 && $yyyymm <= 201003) {
    $rui_b_swari_a = 0;
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=201001 and pl_bs_ym<=%d and note='商管仕入割引再計算'", $yyyymm);
    if (getUniResult($query, $rui_b_swari_b) < 1) {
        $rui_b_swari_b = 0;                          // 検索失敗
    }
    $rui_b_swari = $rui_b_swari_a + $rui_b_swari_b;
} else {
    $rui_b_swari = 0;
}
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
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='全体仕入割引'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_all_swari) < 1) {
    $rui_all_swari = 0;                         // 検索失敗
} else {
    $rui_all_swari = number_format(($rui_all_swari / $tani), $keta);
}
if ($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='カプラ仕入割引再計算'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_c_swari) < 1) {
        $rui_c_swari = 0;                           // 検索失敗
    } else {
        $rui_c_swari = number_format(($rui_c_swari / $tani), $keta);
    }
} elseif ($yyyymm >= 201001 && $yyyymm <= 201003) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=200904 and pl_bs_ym<=200912 and note='カプラ仕入割引'");
    if (getUniResult($query, $rui_c_swari_a) < 1) {
        $rui_c_swari_a = 0;                          // 検索失敗
    }
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=201001 and pl_bs_ym<=%d and note='カプラ仕入割引再計算'", $yyyymm);
    if (getUniResult($query, $rui_c_swari_b) < 1) {
        $rui_c_swari_b = 0;                          // 検索失敗
    }
    $rui_c_swari = $rui_c_swari_a + $rui_c_swari_b;
    $rui_c_swari = number_format(($rui_c_swari / $tani), $keta);
} else {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='カプラ仕入割引'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_c_swari) < 1) {
        $rui_c_swari = 0;                           // 検索失敗
    } else {
        $rui_c_swari = number_format(($rui_c_swari / $tani), $keta);
    }
}
if ($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='リニア仕入割引再計算'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_l_swari) < 1) {
        $rui_l_swari = 0;                           // 検索失敗
    } else {
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
    $rui_l_swari = number_format(($rui_l_swari / $tani), $keta);
} else {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='リニア仕入割引'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_l_swari) < 1) {
        $rui_l_swari = 0;                           // 検索失敗
    } else {
        $rui_l_swari = $rui_l_swari - $rui_s_swari_sagaku;
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
    $s_pother_sagaku = $s_pother;
    if ($yyyymm == 200912) {
        $s_pother = $s_pother + 722;
    }
    if ($yyyymm == 201001) {
        $s_pother = $s_pother - 29125;
    }
    $s_pother        = number_format(($s_pother / $tani), $keta);
}
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='商管営業外収益その他再計算'", $yyyymm);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='商管営業外収益その他'", $yyyymm);
}
if (getUniResult($query, $b_pother) < 1) {
    $b_pother = 0;                              // 検索失敗
    $b_sagaku = $b_sagaku - $b_pother;          // カプラ差額計算用
} else {
    if ($yyyymm == 201001) {
        $b_pother = $b_pother - 63096;
    }
    $b_sagaku = $b_sagaku - $b_pother;          // カプラ差額計算用
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='全体営業外収益その他'", $yyyymm);
if (getUniResult($query, $all_pother) < 1) {
    $all_pother = 0;                            // 検索失敗
} else {
    if ($yyyymm == 200912) {
        $all_pother = $all_pother + 466000;
    }
    if ($yyyymm == 201001) {
        $all_pother = $all_pother - 466000;
    }
    if ($yyyymm == 201002) {
        $all_pother = $all_pother + 600000;
    }
    if ($yyyymm == 201003) {
        $all_pother = $all_pother - 600000;
    }
    $all_pother = number_format(($all_pother / $tani), $keta);
}
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ営業外収益その他再計算'", $yyyymm);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ営業外収益その他'", $yyyymm);
}
if (getUniResult($query, $c_pother) < 1) {
    $c_pother = 0;                              // 検索失敗
} else {
    if ($yyyymm < 201001) {
        $c_pother = $c_pother - $b_pother;
    }
    if ($yyyymm == 200912) {
        $c_pother = $c_pother + 389809;
    }
    if ($yyyymm == 201001) {
        $c_pother = $c_pother - 315529;
    }
    $c_pother = number_format(($c_pother / $tani), $keta);
}
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア営業外収益その他再計算'", $yyyymm);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア営業外収益その他'", $yyyymm);
}
if (getUniResult($query, $l_pother) < 1) {
    if ($yyyymm < 201001) {
        $l_pother = 0 - $s_pother_sagaku;           // 検索失敗
    } else {
        $l_pother = 0;           // 検索失敗
    }
} else {
    if ($yyyymm < 201001) {
        $l_pother = $l_pother - $s_pother_sagaku;
    }
    if ($yyyymm == 200912) {
        $l_pother = $l_pother + 75469;
    }
    if ($yyyymm == 201001) {
        $l_pother = $l_pother - 58250;
    }
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
    $p1_s_pother_sagaku = $p1_s_pother;
    if ($p1_ym == 200912) {
        $p1_s_pother = $p1_s_pother + 722;
    }
    if ($p1_ym == 201001) {
        $p1_s_pother = $p1_s_pother - 29125;
    }
    $p1_s_pother        = number_format(($p1_s_pother / $tani), $keta);
}
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='商管営業外収益その他再計算'", $p1_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='商管営業外収益その他'", $p1_ym);
}
if (getUniResult($query, $p1_b_pother) < 1) {
    $p1_b_pother = 0;                              // 検索失敗
    $p1_b_sagaku = $p1_b_sagaku - $p1_b_pother;          // カプラ差額計算用
} else {
    if ($p1_ym == 201001) {
        $p1_b_pother = $p1_b_pother - 63096;
    }
    $p1_b_sagaku = $p1_b_sagaku - $p1_b_pother;          // カプラ差額計算用
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='全体営業外収益その他'", $p1_ym);
if (getUniResult($query, $p1_all_pother) < 1) {
    $p1_all_pother = 0;                         // 検索失敗
} else {
    if ($p1_ym == 200912) {
        $p1_all_pother = $p1_all_pother + 466000;
    }
    if ($p1_ym == 201001) {
        $p1_all_pother = $p1_all_pother - 466000;
    }
    if ($p1_ym == 201002) {
        $p1_all_pother = $p1_all_pother + 600000;
    }
    if ($p1_ym == 201003) {
        $p1_all_pother = $p1_all_pother - 600000;
    }
    $p1_all_pother = number_format(($p1_all_pother / $tani), $keta);
}
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ営業外収益その他再計算'", $p1_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ営業外収益その他'", $p1_ym);
}
if (getUniResult($query, $p1_c_pother) < 1) {
    $p1_c_pother = 0;                              // 検索失敗
} else {
    if ($p1_ym < 201001) {
        $p1_c_pother = $p1_c_pother - $p1_b_pother;
    }
    if ($p1_ym == 200912) {
        $p1_c_pother = $p1_c_pother + 389809;
    }
    if ($p1_ym == 201001) {
        $p1_c_pother = $p1_c_pother - 315529;
    }
    $p1_c_pother = number_format(($p1_c_pother / $tani), $keta);
}
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア営業外収益その他再計算'", $p1_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア営業外収益その他'", $p1_ym);
}
if (getUniResult($query, $p1_l_pother) < 1) {
    if ($p1_ym < 201001) {
        $p1_l_pother = 0 - $p1_s_pother_sagaku;           // 検索失敗
    } else {
        $p1_l_pother = 0;           // 検索失敗
    }
} else {
    if ($p1_ym < 201001) {
        $p1_l_pother = $p1_l_pother - $p1_s_pother_sagaku;
    }
    if ($p1_ym == 200912) {
        $p1_l_pother = $p1_l_pother + 75469;
    }
    if ($p1_ym == 201001) {
        $p1_l_pother = $p1_l_pother - 58250;
    }
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
    $p2_s_pother_sagaku = $p2_s_pother;
    if ($p2_ym == 200912) {
        $p2_s_pother = $p2_s_pother + 722;
    }
    if ($p2_ym == 201001) {
        $p2_s_pother = $p2_s_pother - 29125;
    }
    $p2_s_pother        = number_format(($p2_s_pother / $tani), $keta);
}
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='商管営業外収益その他再計算'", $p2_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='商管営業外収益その他'", $p2_ym);
}
if (getUniResult($query, $p2_b_pother) < 1) {
    $p2_b_pother = 0;                              // 検索失敗
    $p2_b_sagaku = $p2_b_sagaku - $p2_b_pother;          // カプラ差額計算用
} else {
    if ($p2_ym == 201001) {
        $p2_b_pother = $p2_b_pother - 63096;
    }
    $p2_b_sagaku = $p2_b_sagaku - $p2_b_pother;          // カプラ差額計算用
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='全体営業外収益その他'", $p2_ym);
if (getUniResult($query, $p2_all_pother) < 1) {
    $p2_all_pother = 0;                         // 検索失敗
} else {
    if ($p2_ym == 200912) {
        $p2_all_pother = $p2_all_pother + 466000;
    }
    if ($p2_ym == 201001) {
        $p2_all_pother = $p2_all_pother - 466000;
    }
    if ($p2_ym == 201002) {
        $p2_all_pother = $p2_all_pother + 600000;
    }
    if ($p2_ym == 201003) {
        $p2_all_pother = $p2_all_pother - 600000;
    }
    $p2_all_pother = number_format(($p2_all_pother / $tani), $keta);
}
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ営業外収益その他再計算'", $p2_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ営業外収益その他'", $p2_ym);
}
if (getUniResult($query, $p2_c_pother) < 1) {
    $p2_c_pother = 0;                              // 検索失敗
} else {
    if ($p2_ym < 201001) {
        $p2_c_pother = $p2_c_pother - $p2_b_pother;
    }
    if ($p2_ym == 200912) {
        $p2_c_pother = $p2_c_pother + 389809;
    }
    if ($p2_ym == 201001) {
        $p2_c_pother = $p2_c_pother - 315529;
    }
    $p2_c_pother = number_format(($p2_c_pother / $tani), $keta);
}
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア営業外収益その他再計算'", $p2_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア営業外収益その他'", $p2_ym);
}
if (getUniResult($query, $p2_l_pother) < 1) {
    if ($p2_ym < 201001) {
        $p2_l_pother = 0 - $p2_s_pother_sagaku;           // 検索失敗
    } else {
        $p2_l_pother = 0;           // 検索失敗
    }
} else {
    if ($p2_ym < 201001) {
        $p2_l_pother = $p2_l_pother - $p2_s_pother_sagaku;
    }
    if ($p2_ym == 200912) {
        $p2_l_pother = $p2_l_pother + 75469;
    }
    if ($p2_ym == 201001) {
        $p2_l_pother = $p2_l_pother - 58250;
    }
    $p2_l_pother = number_format(($p2_l_pother / $tani), $keta);
}
    ///// 今期累計
if ($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='商管営業外収益その他再計算'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_b_pother) < 1) {
        $rui_b_pother = 0;                          // 検索失敗
    } else {
        //$rui_b_pother_sagaku = $rui_b_pother;
    }
} elseif ($yyyymm >= 201001 && $yyyymm <= 201003) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=200904 and pl_bs_ym<=200912 and note='商管営業外収益その他'");
    if (getUniResult($query, $rui_b_pother_a) < 1) {
        $rui_b_pother_a = 0;                          // 検索失敗
    }
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=201001 and pl_bs_ym<=%d and note='商管営業外収益その他再計算'", $yyyymm);
    if (getUniResult($query, $rui_b_pother_b) < 1) {
        $rui_b_pother_b = 0;                          // 検索失敗
    }
    $rui_b_pother = $rui_b_pother_a + $rui_b_pother_b;
    $rui_b_pother = $rui_b_pother - 63096;
} else {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='商管営業外収益その他'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_b_pother) < 1) {
        $rui_b_pother = 0;                          // 検索失敗
        $rui_b_sagaku = $rui_b_sagaku - $rui_b_pother;      // カプラ差額計算用
    } else {
        $rui_b_sagaku = $rui_b_sagaku - $rui_b_pother;      // カプラ差額計算用
    }
}
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
        $rui_s_pother_sagaku = $rui_s_pother;
        if ($yyyymm >= 200912 && $yyyymm <= 201003) {
            $rui_s_pother = $rui_s_pother + 722;
        }
        if ($yyyymm >= 201001 && $yyyymm <= 201003) {
            $rui_s_pother = $rui_s_pother - 29125;
        }
        $rui_s_pother = number_format(($rui_s_pother / $tani), $keta);
    }
}

$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='全体営業外収益その他'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_all_pother) < 1) {
    $rui_all_pother = 0;                        // 検索失敗
} else {
    if ($yyyymm >= 200912 && $yyyymm <= 201003) {
        $rui_all_pother = $rui_all_pother + 466000;
    }
    if ($yyyymm >= 201001 && $yyyymm <= 201003) {
        $rui_all_pother = $rui_all_pother - 466000;
    }
    if ($yyyymm >= 201002 && $yyyymm <= 201003) {
        $rui_all_pother = $rui_all_pother + 600000;
    }
    if ($yyyymm == 201003) {
        $rui_all_pother = $rui_all_pother - 600000;
    }
    $rui_all_pother = number_format(($rui_all_pother / $tani), $keta);
}
if ($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='カプラ営業外収益その他再計算'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_c_pother) < 1) {
        $rui_c_pother = 0;                          // 検索失敗
    } else {
        $rui_c_pother = number_format(($rui_c_pother / $tani), $keta);
    }
} elseif ($yyyymm >= 201001 && $yyyymm <= 201003) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=200904 and pl_bs_ym<=200912 and note='カプラ営業外収益その他'");
    if (getUniResult($query, $rui_c_pother_a) < 1) {
        $rui_c_pother_a = 0;                          // 検索失敗
    }
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=201001 and pl_bs_ym<=%d and note='カプラ営業外収益その他再計算'", $yyyymm);
    if (getUniResult($query, $rui_c_pother_b) < 1) {
        $rui_c_pother_b = 0;                          // 検索失敗
    }
    $rui_c_pother = $rui_c_pother_a + $rui_c_pother_b;
    
    if ($yyyymm >= 200912 && $yyyymm <= 201003) {
        $rui_c_pother = $rui_c_pother + 389809;
    }
    if ($yyyymm >= 201001 && $yyyymm <= 201003) {
        $rui_c_pother = $rui_c_pother - 315529;
    }
    $rui_c_pother = $rui_c_pother - $rui_b_pother_a;
    $rui_c_pother = number_format(($rui_c_pother / $tani), $keta);
} else {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='カプラ営業外収益その他'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_c_pother) < 1) {
        $rui_c_pother = 0;                          // 検索失敗
    } else {
        $rui_c_pother = $rui_c_pother - $rui_b_pother;
        if ($yyyymm >= 200912 && $yyyymm <= 201003) {
            $rui_c_pother = $rui_c_pother + 389809;
        }
        if ($yyyymm >= 201001 && $yyyymm <= 201003) {
            $rui_c_pother = $rui_c_pother - 315529;
        }
        $rui_c_pother = number_format(($rui_c_pother / $tani), $keta);
    }
}
if ($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='リニア営業外収益その他再計算'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_l_pother) < 1) {
        $rui_l_pother = 0;                          // 検索失敗
    } else {
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
        $rui_l_pother = $rui_l_pother + 75469;
    }
    if ($yyyymm >= 201001 && $yyyymm <= 201003) {
        $rui_l_pother = $rui_l_pother - 58250;
    }
    $rui_l_pother = $rui_l_pother - $rui_s_pother_a;
    $rui_l_pother = number_format(($rui_l_pother / $tani), $keta);
} else {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='リニア営業外収益その他'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_l_pother) < 1) {
        $rui_l_pother = 0 - $rui_s_pother_sagaku;   // 検索失敗
    } else {
        $rui_l_pother = $rui_l_pother - $rui_s_pother_sagaku;
        if ($yyyymm >= 200912 && $yyyymm <= 201003) {
            $rui_l_pother = $rui_l_pother + 75469;
        }
        if ($yyyymm >= 201001 && $yyyymm <= 201003) {
            $rui_l_pother = $rui_l_pother - 58250;
        }
        $rui_l_pother = number_format(($rui_l_pother / $tani), $keta);
    }
}
/********** 営業外収益の合計 **********/
    ///// 商管
$p2_b_nonope_profit_sum  = $p2_b_gyoumu + $p2_b_swari + $p2_b_pother;
$p2_b_gyoumu             = number_format(($p2_b_gyoumu / $tani), $keta);
$p2_b_swari              = number_format(($p2_b_swari / $tani), $keta);
$p2_b_pother             = number_format(($p2_b_pother / $tani), $keta);

$p1_b_nonope_profit_sum  = $p1_b_gyoumu + $p1_b_swari + $p1_b_pother;
$p1_b_gyoumu             = number_format(($p1_b_gyoumu / $tani), $keta);
$p1_b_swari              = number_format(($p1_b_swari / $tani), $keta);
$p1_b_pother             = number_format(($p1_b_pother / $tani), $keta);

$b_nonope_profit_sum     = $b_gyoumu + $b_swari + $b_pother;
$b_gyoumu                = number_format(($b_gyoumu / $tani), $keta);
$b_swari                 = number_format(($b_swari / $tani), $keta);
$b_pother                = number_format(($b_pother / $tani), $keta);

$rui_b_nonope_profit_sum = $rui_b_gyoumu + $rui_b_swari + $rui_b_pother;
$rui_b_gyoumu            = number_format(($rui_b_gyoumu / $tani), $keta);
$rui_b_swari             = number_format(($rui_b_swari / $tani), $keta);
$rui_b_pother            = number_format(($rui_b_pother / $tani), $keta);
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

    ///// 当月
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='全体営業外収益計'", $yyyymm);
if (getUniResult($query, $all_nonope_profit_sum) < 1) {
    $all_nonope_profit_sum = 0;                 // 検索失敗
} else {
    if ($yyyymm == 200906) {
        $all_nonope_profit_sum = $all_nonope_profit_sum + 3100900;
    } elseif ($yyyymm == 200905) {
        $all_nonope_profit_sum = $all_nonope_profit_sum - 1550450;
    } elseif ($yyyymm == 200904) {
        $all_nonope_profit_sum = $all_nonope_profit_sum - 1550450;
    }
    if ($yyyymm == 201002) {
        $all_nonope_profit_sum = $all_nonope_profit_sum + 600000;
    }
    if ($yyyymm == 201003) {
        $all_nonope_profit_sum = $all_nonope_profit_sum - 600000;
    }
    $all_nonope_profit_sum = number_format(($all_nonope_profit_sum / $tani), $keta);
}
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ営業外収益計再計算'", $yyyymm);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ営業外収益計'", $yyyymm);
}
if (getUniResult($query, $c_nonope_profit_sum) < 1) {
    $c_nonope_profit_sum = 0;                   // 検索失敗
    $c_nonope_profit_sum_temp = 0;
} else {
    if ($yyyymm < 201001) {
        $c_nonope_profit_sum = $c_nonope_profit_sum - $b_nonope_profit_sum;
    }
    $c_nonope_profit_sum_temp = $c_nonope_profit_sum;
    $c_nonope_profit_sum      = number_format(($c_nonope_profit_sum / $tani), $keta);
}
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア営業外収益計再計算'", $yyyymm);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア営業外収益計'", $yyyymm);
}
if (getUniResult($query, $l_nonope_profit_sum) < 1) {
    if ($yyyymm == 200906) {
        $l_nonope_profit_sum = 0 - $s_nonope_profit_sum_sagaku + 3100900;   // 検索失敗
    } elseif ($yyyymm == 200905) {
        $l_nonope_profit_sum = 0 - $s_nonope_profit_sum_sagaku - 1550450;   // 検索失敗
    } elseif ($yyyymm == 200904) {
        $l_nonope_profit_sum = 0 - $s_nonope_profit_sum_sagaku - 1550450;   // 検索失敗
    } else {
        $l_nonope_profit_sum = 0 - $s_nonope_profit_sum_sagaku;             // 検索失敗
    }
} else {
    if ($yyyymm == 200906) {
        $l_nonope_profit_sum = $l_nonope_profit_sum - $s_nonope_profit_sum_sagaku + 3100900;
    } elseif ($yyyymm == 200905) {
        $l_nonope_profit_sum = $l_nonope_profit_sum - $s_nonope_profit_sum_sagaku - 1550450;
    } elseif ($yyyymm == 200904) {
        $l_nonope_profit_sum = $l_nonope_profit_sum - $s_nonope_profit_sum_sagaku - 1550450;
    } elseif ($yyyymm < 201001) {
        $l_nonope_profit_sum = $l_nonope_profit_sum - $s_nonope_profit_sum_sagaku;
    }
    $l_nonope_profit_sum = number_format(($l_nonope_profit_sum / $tani), $keta);
}
    ///// 前月
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='全体営業外収益計'", $p1_ym);
if (getUniResult($query, $p1_all_nonope_profit_sum) < 1) {
    $p1_all_nonope_profit_sum = 0;              // 検索失敗
} else {
    if ($p1_ym == 200906) {
        $p1_all_nonope_profit_sum = $p1_all_nonope_profit_sum + 3100900;
    } elseif ($p1_ym == 200905) {
        $p1_all_nonope_profit_sum = $p1_all_nonope_profit_sum - 1550450;
    } elseif ($p1_ym == 200904) {
        $p1_all_nonope_profit_sum = $p1_all_nonope_profit_sum - 1550450;
    }
    if ($p1_ym == 201002) {
        $p1_all_nonope_profit_sum = $p1_all_nonope_profit_sum + 600000;
    }
    if ($p1_ym == 201003) {
        $p1_all_nonope_profit_sum = $p1_all_nonope_profit_sum - 600000;
    }
    $p1_all_nonope_profit_sum = number_format(($p1_all_nonope_profit_sum / $tani), $keta);
}
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ営業外収益計再計算'", $p1_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ営業外収益計'", $p1_ym);
}
if (getUniResult($query, $p1_c_nonope_profit_sum) < 1) {
    $p1_c_nonope_profit_sum = 0;                   // 検索失敗
    $p1_c_nonope_profit_sum_temp = 0;
} else {
    if ($p1_ym < 201001) {
        $p1_c_nonope_profit_sum = $p1_c_nonope_profit_sum - $p1_b_nonope_profit_sum;
    }
    $p1_c_nonope_profit_sum_temp = $p1_c_nonope_profit_sum;
    $p1_c_nonope_profit_sum      = number_format(($p1_c_nonope_profit_sum / $tani), $keta);
}
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア営業外収益計再計算'", $p1_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア営業外収益計'", $p1_ym);
}
if (getUniResult($query, $p1_l_nonope_profit_sum) < 1) {
    if ($p1_ym == 200906) {
        $p1_l_nonope_profit_sum = 0 - $p1_s_nonope_profit_sum_sagaku + 3100900;   // 検索失敗
    } elseif ($p1_ym == 200905) {
        $p1_l_nonope_profit_sum = 0 - $p1_s_nonope_profit_sum_sagaku - 1550450;   // 検索失敗
    } elseif ($p1_ym == 200904) {
        $p1_l_nonope_profit_sum = 0 - $p1_s_nonope_profit_sum_sagaku - 1550450;   // 検索失敗
    } else {
        $p1_l_nonope_profit_sum = 0 - $p1_s_nonope_profit_sum_sagaku;             // 検索失敗
    }
} else {
    if ($p1_ym == 200906) {
        $p1_l_nonope_profit_sum = $p1_l_nonope_profit_sum - $p1_s_nonope_profit_sum_sagaku + 3100900;
    } elseif ($p1_ym == 200905) {
        $p1_l_nonope_profit_sum = $p1_l_nonope_profit_sum - $p1_s_nonope_profit_sum_sagaku - 1550450;
    } elseif ($p1_ym == 200904) {
        $p1_l_nonope_profit_sum = $p1_l_nonope_profit_sum - $p1_s_nonope_profit_sum_sagaku - 1550450;
    } elseif ($p1_ym < 201001) {
        $p1_l_nonope_profit_sum = $p1_l_nonope_profit_sum - $p1_s_nonope_profit_sum_sagaku;
    }
    $p1_l_nonope_profit_sum = number_format(($p1_l_nonope_profit_sum / $tani), $keta);
}
    ///// 前前月
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='全体営業外収益計'", $p2_ym);
if (getUniResult($query, $p2_all_nonope_profit_sum) < 1) {
    $p2_all_nonope_profit_sum = 0;              // 検索失敗
} else {
    if ($p2_ym == 200906) {
        $p2_all_nonope_profit_sum = $p2_all_nonope_profit_sum + 3100900;
    } elseif ($p2_ym == 200905) {
        $p2_all_nonope_profit_sum = $p2_all_nonope_profit_sum - 1550450;
    } elseif ($p2_ym == 200904) {
        $p2_all_nonope_profit_sum = $p2_all_nonope_profit_sum - 1550450;
    }
    if ($p2_ym == 201002) {
        $p2_all_nonope_profit_sum = $p2_all_nonope_profit_sum + 600000;
    }
    if ($p2_ym == 201003) {
        $p2_all_nonope_profit_sum = $p2_all_nonope_profit_sum - 600000;
    }
    $p2_all_nonope_profit_sum = number_format(($p2_all_nonope_profit_sum / $tani), $keta);
}
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ営業外収益計再計算'", $p2_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ営業外収益計'", $p2_ym);
}
if (getUniResult($query, $p2_c_nonope_profit_sum) < 1) {
    $p2_c_nonope_profit_sum = 0;                   // 検索失敗
    $p2_c_nonope_profit_sum_temp = 0;
} else {
    if ($p2_ym < 201001) {
        $p2_c_nonope_profit_sum = $p2_c_nonope_profit_sum - $p2_b_nonope_profit_sum;
    }
    $p2_c_nonope_profit_sum_temp = $p2_c_nonope_profit_sum;
    $p2_c_nonope_profit_sum      = number_format(($p2_c_nonope_profit_sum / $tani), $keta);
}
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア営業外収益計再計算'", $p2_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア営業外収益計'", $p2_ym);
}
if (getUniResult($query, $p2_l_nonope_profit_sum) < 1) {
    if ($p2_ym == 200906) {
        $p2_l_nonope_profit_sum = 0 - $p2_s_nonope_profit_sum_sagaku + 3100900;   // 検索失敗
    } elseif ($p2_ym == 200905) {
        $p2_l_nonope_profit_sum = 0 - $p2_s_nonope_profit_sum_sagaku - 1550450;   // 検索失敗
    } elseif ($p2_ym == 200904) {
        $p2_l_nonope_profit_sum = 0 - $p2_s_nonope_profit_sum_sagaku - 1550450;   // 検索失敗
    } else {
        $p2_l_nonope_profit_sum = 0 - $p2_s_nonope_profit_sum_sagaku;             // 検索失敗
    }
} else {
    if ($p2_ym == 200906) {
        $p2_l_nonope_profit_sum = $p2_l_nonope_profit_sum - $p2_s_nonope_profit_sum_sagaku + 3100900;
    } elseif ($p2_ym == 200905) {
        $p2_l_nonope_profit_sum = $p2_l_nonope_profit_sum - $p2_s_nonope_profit_sum_sagaku - 1550450;
    } elseif ($p2_ym == 200904) {
        $p2_l_nonope_profit_sum = $p2_l_nonope_profit_sum - $p2_s_nonope_profit_sum_sagaku - 1550450;
    } elseif ($p2_ym < 201001) {
        $p2_l_nonope_profit_sum = $p2_l_nonope_profit_sum - $p2_s_nonope_profit_sum_sagaku;
    }
    $p2_l_nonope_profit_sum = number_format(($p2_l_nonope_profit_sum / $tani), $keta);
}
    ///// 今期累計
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='全体営業外収益計'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_all_nonope_profit_sum) < 1) {
    $rui_all_nonope_profit_sum = 0;             // 検索失敗
} else {
    if ($yyyymm >= 201002 && $yyyymm <= 201003) {
        $rui_all_nonope_profit_sum = $rui_all_nonope_profit_sum + 600000;
    }
    if ($yyyymm == 201003) {
        $rui_all_nonope_profit_sum = $rui_all_nonope_profit_sum - 600000;
    }
    $rui_all_nonope_profit_sum = number_format(($rui_all_nonope_profit_sum / $tani), $keta);
}
if ($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='カプラ営業外収益計再計算'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_c_nonope_profit_sum) < 1) {
        $rui_c_nonope_profit_sum = 0;                           // 検索失敗
    } else {
        //$rui_c_nonope_profit_sum = $rui_c_nonope_profit_sum - $rui_b_nonope_profit_sum;
        $rui_c_nonope_profit_sum = number_format(($rui_c_nonope_profit_sum / $tani), $keta);
    }
} elseif ($yyyymm >= 201001 && $yyyymm <= 201003) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=200904 and pl_bs_ym<=200912 and note='カプラ営業外収益計'");
    if (getUniResult($query, $rui_c_nonope_profit_sum_a) < 1) {
        $rui_c_nonope_profit_sum_a = 0;                          // 検索失敗
    }
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=201001 and pl_bs_ym<=%d and note='カプラ営業外収益計再計算'", $yyyymm);
    if (getUniResult($query, $rui_c_nonope_profit_sum_b) < 1) {
        $rui_c_nonope_profit_sum_b = 0;                          // 検索失敗
    }
    $rui_c_nonope_profit_sum = $rui_c_nonope_profit_sum_a + $rui_c_nonope_profit_sum_b;
    if ($yyyymm < 201001) {
        $rui_c_nonope_profit_sum = $rui_c_nonope_profit_sum - $rui_b_nonope_profit_sum;
    }
    $rui_c_nonope_profit_sum = $rui_c_nonope_profit_sum - $rui_b_pother_a;
    $rui_c_nonope_profit_sum = number_format(($rui_c_nonope_profit_sum / $tani), $keta);
} else {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='カプラ営業外収益計'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_c_nonope_profit_sum) < 1) {
        $rui_c_nonope_profit_sum = 0;                           // 検索失敗
    } else {
        $rui_c_nonope_profit_sum = $rui_c_nonope_profit_sum - $rui_b_nonope_profit_sum;
        $rui_c_nonope_profit_sum = number_format(($rui_c_nonope_profit_sum / $tani), $keta);
    }
}
if ($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='リニア営業外収益計再計算'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_l_nonope_profit_sum) < 1) {
        $rui_l_nonope_profit_sum = 0;                           // 検索失敗
    } else {
        //$rui_l_nonope_profit_sum      = $rui_l_nonope_profit_sum - $rui_s_nonope_profit_sum;
        $rui_l_nonope_profit_sum_temp = $rui_l_nonope_profit_sum;         // 経常利益計算用
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
    $rui_l_nonope_profit_sum      = $rui_l_nonope_profit_sum - $rui_s_gyoumu_a - $rui_s_swari_a - $rui_s_pother_a;
    $rui_l_nonope_profit_sum_temp = $rui_l_nonope_profit_sum;         // 経常利益計算用
    $rui_l_nonope_profit_sum      = number_format(($rui_l_nonope_profit_sum / $tani), $keta);
} else {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='リニア営業外収益計'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_l_nonope_profit_sum) < 1) {
        $rui_l_nonope_profit_sum = 0 - $rui_s_nonope_profit_sum_sagaku;     // 検索失敗
    } else {
        $rui_l_nonope_profit_sum = $rui_l_nonope_profit_sum - $rui_s_nonope_profit_sum_sagaku;
        $rui_l_nonope_profit_sum = number_format(($rui_l_nonope_profit_sum / $tani), $keta);
    }
}

/********** 営業外費用の支払利息 **********/
    ///// 当月
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='商管支払利息再計算'", $yyyymm);
    if (getUniResult($query, $b_srisoku) < 1) {
        $b_srisoku        = 0;                      // 検索失敗
        $b_srisoku_temp = 0;
    } else {
        $b_srisoku_temp = $b_srisoku;
    }
} else {
    $b_srisoku     = 0;
}
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
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='全体支払利息'", $yyyymm);
if (getUniResult($query, $all_srisoku) < 1) {
    $all_srisoku = 0;                           // 検索失敗
} else {
    $all_srisoku = number_format(($all_srisoku / $tani), $keta);
}
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ支払利息再計算'", $yyyymm);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ支払利息'", $yyyymm);
}
if (getUniResult($query, $c_srisoku) < 1) {
    $c_srisoku = 0;                             // 検索失敗
} else {
    $c_srisoku = number_format(($c_srisoku / $tani), $keta);
}
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア支払利息再計算'", $yyyymm);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア支払利息'", $yyyymm);
}
if (getUniResult($query, $l_srisoku) < 1) {
    $l_srisoku = 0 - $s_srisoku_sagaku;         // 検索失敗
} else {
    if ($yyyymm < 201001) {
        $l_srisoku = $l_srisoku - $s_srisoku_sagaku;
    }
    $l_srisoku = number_format(($l_srisoku / $tani), $keta);
}
    ///// 前月
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='商管支払利息再計算'", $p1_ym);
    if (getUniResult($query, $p1_b_srisoku) < 1) {
        $p1_b_srisoku        = 0;                      // 検索失敗
        $p1_b_srisoku_temp = 0;
    } else {
        $p1_b_srisoku_temp = $p1_b_srisoku;
    }
} else {
    $p1_b_srisoku     = 0;
}
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
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='全体支払利息'", $p1_ym);
if (getUniResult($query, $p1_all_srisoku) < 1) {
    $p1_all_srisoku = 0;                        // 検索失敗
} else {
    $p1_all_srisoku = number_format(($p1_all_srisoku / $tani), $keta);
}
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ支払利息再計算'", $p1_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ支払利息'", $p1_ym);
}
if (getUniResult($query, $p1_c_srisoku) < 1) {
    $p1_c_srisoku = 0;                             // 検索失敗
} else {
    $p1_c_srisoku = number_format(($p1_c_srisoku / $tani), $keta);
}
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア支払利息再計算'", $p1_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア支払利息'", $p1_ym);
}
if (getUniResult($query, $p1_l_srisoku) < 1) {
    $p1_l_srisoku = 0 - $p1_s_srisoku_sagaku;         // 検索失敗
} else {
    if ($p1_ym < 201001) {
        $p1_l_srisoku = $p1_l_srisoku - $p1_s_srisoku_sagaku;
    }
    $p1_l_srisoku = number_format(($p1_l_srisoku / $tani), $keta);
}
    ///// 前前月
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='商管支払利息再計算'", $p2_ym);
    if (getUniResult($query, $p2_b_srisoku) < 1) {
        $p2_b_srisoku        = 0;                      // 検索失敗
        $p2_b_srisoku_temp = 0;
    } else {
        $p2_b_srisoku_temp = $p2_b_srisoku;
    }
} else {
    $p2_b_srisoku     = 0;
}
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
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='全体支払利息'", $p2_ym);
if (getUniResult($query, $p2_all_srisoku) < 1) {
    $p2_all_srisoku = 0;                        // 検索失敗
} else {
    $p2_all_srisoku = number_format(($p2_all_srisoku / $tani), $keta);
}
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ支払利息再計算'", $p2_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ支払利息'", $p2_ym);
}
if (getUniResult($query, $p2_c_srisoku) < 1) {
    $p2_c_srisoku = 0;                             // 検索失敗
} else {
    $p2_c_srisoku = number_format(($p2_c_srisoku / $tani), $keta);
}
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア支払利息再計算'", $p2_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア支払利息'", $p2_ym);
}
if (getUniResult($query, $p2_l_srisoku) < 1) {
    $p2_l_srisoku = 0 - $p2_s_srisoku_sagaku;         // 検索失敗
} else {
    if ($p2_ym < 201001) {
        $p2_l_srisoku = $p2_l_srisoku - $p2_s_srisoku_sagaku;
    }
    $p2_l_srisoku = number_format(($p2_l_srisoku / $tani), $keta);
}
    ///// 今期累計
if ($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='商管支払利息再計算'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_b_srisoku) < 1) {
        $rui_b_srisoku = 0;                           // 検索失敗
    }
} elseif ($yyyymm >= 201001 && $yyyymm <= 201003) {
    $rui_b_srisoku_a = 0;
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=201001 and pl_bs_ym<=%d and note='商管支払利息再計算'", $yyyymm);
    if (getUniResult($query, $rui_b_srisoku_b) < 1) {
        $rui_b_srisoku_b = 0;                          // 検索失敗
    }
    $rui_b_srisoku = $rui_b_srisoku_a + $rui_b_srisoku_b;
} else {
    $rui_b_srisoku = 0;
}

if ($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='試修支払利息再計算'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_s_srisoku) < 1) {
        $rui_s_srisoku = 0;                           // 検索失敗
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

$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='全体支払利息'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_all_srisoku) < 1) {
    $rui_all_srisoku = 0;                       // 検索失敗
} else {
    $rui_all_srisoku = number_format(($rui_all_srisoku / $tani), $keta);
}
if ($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='カプラ支払利息再計算'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_c_srisoku) < 1) {
        $rui_c_srisoku = 0;                           // 検索失敗
    } else {
        $rui_c_srisoku = number_format(($rui_c_srisoku / $tani), $keta);
    }
} elseif ($yyyymm >= 201001 && $yyyymm <= 201003) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=200904 and pl_bs_ym<=200912 and note='カプラ支払利息'");
    if (getUniResult($query, $rui_c_srisoku_a) < 1) {
        $rui_c_srisoku_a = 0;                          // 検索失敗
    }
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=201001 and pl_bs_ym<=%d and note='カプラ支払利息再計算'", $yyyymm);
    if (getUniResult($query, $rui_c_srisoku_b) < 1) {
        $rui_c_srisoku_b = 0;                          // 検索失敗
    }
    $rui_c_srisoku = $rui_c_srisoku_a + $rui_c_srisoku_b;
    $rui_c_srisoku = number_format(($rui_c_srisoku / $tani), $keta);
} else {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='カプラ支払利息'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_c_srisoku) < 1) {
        $rui_c_srisoku = 0;                           // 検索失敗
    } else {
        $rui_c_srisoku = number_format(($rui_c_srisoku / $tani), $keta);
    }
}
if ($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='リニア支払利息再計算'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_l_srisoku) < 1) {
        $rui_l_srisoku = 0;                           // 検索失敗
    } else {
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
    $rui_l_srisoku = number_format(($rui_l_srisoku / $tani), $keta);
} else {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='リニア支払利息'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_l_srisoku) < 1) {
        $rui_l_srisoku = 0 - $rui_s_srisoku_sagaku; // 検索失敗
    } else {
        $rui_l_srisoku = $rui_l_srisoku - $rui_s_srisoku_sagaku;
        $rui_l_srisoku = number_format(($rui_l_srisoku / $tani), $keta);
    }
}

/********** 営業外費用のその他 **********/
    ///// 当月
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='商管営業外費用その他再計算'", $yyyymm);
    if (getUniResult($query, $b_lother) < 1) {
        $b_lother      = 0;                       // 検索失敗
        $b_lother_temp = 0;
    } else {
        $b_lother_temp = $b_lother;
    }
} else {
    $b_lother     = 0;
}
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
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='全体営業外費用その他'", $yyyymm);
if (getUniResult($query, $all_lother) < 1) {
    $all_lother = 0;                            // 検索失敗
} else {
    $all_lother = number_format(($all_lother / $tani), $keta);
}
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ営業外費用その他再計算'", $yyyymm);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ営業外費用その他'", $yyyymm);
}
if (getUniResult($query, $c_lother) < 1) {
    $c_lother = 0;                              // 検索失敗
} else {
    $c_lother = number_format(($c_lother / $tani), $keta);
}
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア営業外費用その他再計算'", $yyyymm);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア営業外費用その他'", $yyyymm);
}
if (getUniResult($query, $l_lother) < 1) {
    $l_lother = 0 - $s_lother_sagaku;           // 検索失敗
} else {
    if ($yyyymm < 201001) {
        $l_lother = $l_lother - $s_lother_sagaku;
    }
    $l_lother = number_format(($l_lother / $tani), $keta);
}
    ///// 前月
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='商管営業外費用その他再計算'", $p1_ym);
    if (getUniResult($query, $p1_b_lother) < 1) {
        $p1_b_lother      = 0;                       // 検索失敗
        $p1_b_lother_temp = 0;
    } else {
        $p1_b_lother_temp = $p1_b_lother;
    }
} else {
    $p1_b_lother     = 0;
}
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
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='全体営業外費用その他'", $p1_ym);
if (getUniResult($query, $p1_all_lother) < 1) {
    $p1_all_lother = 0;                         // 検索失敗
} else {
    $p1_all_lother = number_format(($p1_all_lother / $tani), $keta);
}
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ営業外費用その他再計算'", $p1_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ営業外費用その他'", $p1_ym);
}
if (getUniResult($query, $p1_c_lother) < 1) {
    $p1_c_lother = 0;                              // 検索失敗
} else {
    $p1_c_lother = number_format(($p1_c_lother / $tani), $keta);
}
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア営業外費用その他再計算'", $p1_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア営業外費用その他'", $p1_ym);
}
if (getUniResult($query, $p1_l_lother) < 1) {
    $p1_l_lother = 0 - $p1_s_lother_sagaku;           // 検索失敗
} else {
    if ($p1_ym < 201001) {
        $p1_l_lother = $p1_l_lother - $p1_s_lother_sagaku;
    }
    $p1_l_lother = number_format(($p1_l_lother / $tani), $keta);
}
    ///// 前前月
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='商管営業外費用その他再計算'", $p2_ym);
    if (getUniResult($query, $p2_b_lother) < 1) {
        $p2_b_lother      = 0;                       // 検索失敗
        $p2_b_lother_temp = 0;
    } else {
        $p2_b_lother_temp = $p2_b_lother;
    }
} else {
    $p2_b_lother     = 0;
}
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
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='全体営業外費用その他'", $p2_ym);
if (getUniResult($query, $p2_all_lother) < 1) {
    $p2_all_lother = 0;                         // 検索失敗
} else {
    $p2_all_lother = number_format(($p2_all_lother / $tani), $keta);
}
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ営業外費用その他再計算'", $p2_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ営業外費用その他'", $p2_ym);
}
if (getUniResult($query, $p2_c_lother) < 1) {
    $p2_c_lother = 0;                              // 検索失敗
} else {
    $p2_c_lother = number_format(($p2_c_lother / $tani), $keta);
}
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア営業外費用その他再計算'", $p2_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア営業外費用その他'", $p2_ym);
}
if (getUniResult($query, $p2_l_lother) < 1) {
    $p2_l_lother = 0 - $p2_s_lother_sagaku;           // 検索失敗
} else {
    if ($p2_ym < 201001) {
        $p2_l_lother = $p2_l_lother - $p2_s_lother_sagaku;
    }
    $p2_l_lother = number_format(($p2_l_lother / $tani), $keta);
}
    ///// 今期累計
if ($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='商管営業外費用その他再計算'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_b_lother) < 1) {
        $rui_b_lother = 0;                           // 検索失敗
    }
} elseif ($yyyymm >= 201001 && $yyyymm <= 201003) {
    $rui_b_lother_a = 0;
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=201001 and pl_bs_ym<=%d and note='商管営業外費用その他再計算'", $yyyymm);
    if (getUniResult($query, $rui_b_lother_b) < 1) {
        $rui_b_lother_b = 0;                          // 検索失敗
    }
    $rui_b_lother = $rui_b_lother_a + $rui_b_lother_b;
} else {
    $rui_b_lother = 0;
}
if ($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='試修営業外費用その他再計算'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_s_lother) < 1) {
        $rui_s_lother = 0;                           // 検索失敗
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
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='全体営業外費用その他'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_all_lother) < 1) {
    $rui_all_lother = 0;                        // 検索失敗
} else {
    $rui_all_lother = number_format(($rui_all_lother / $tani), $keta);
}
if ($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='カプラ営業外費用その他再計算'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_c_lother) < 1) {
        $rui_c_lother = 0;                           // 検索失敗
    } else {
        $rui_c_lother = number_format(($rui_c_lother / $tani), $keta);
    }
} elseif ($yyyymm >= 201001 && $yyyymm <= 201003) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=200904 and pl_bs_ym<=200912 and note='カプラ営業外費用その他'");
    if (getUniResult($query, $rui_c_lother_a) < 1) {
        $rui_c_lother_a = 0;                          // 検索失敗
    }
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=201001 and pl_bs_ym<=%d and note='カプラ営業外費用その他再計算'", $yyyymm);
    if (getUniResult($query, $rui_c_lother_b) < 1) {
        $rui_c_lother_b = 0;                          // 検索失敗
    }
    $rui_c_lother = $rui_c_lother_a + $rui_c_lother_b;
    $rui_c_lother = number_format(($rui_c_lother / $tani), $keta);
} else {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='カプラ営業外費用その他'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_c_lother) < 1) {
        $rui_c_lother = 0;                           // 検索失敗
    } else {
        $rui_c_lother = number_format(($rui_c_lother / $tani), $keta);
    }
}
if ($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='リニア営業外費用その他再計算'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_l_lother) < 1) {
        $rui_l_lother = 0 - $rui_s_lother_sagaku;   // 検索失敗
    } else {
        //$rui_l_lother = $rui_l_lother - $rui_s_lother_sagaku;
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
    $rui_l_lother = $rui_l_lother - $rui_s_lother_sagaku;
    $rui_l_lother = number_format(($rui_l_lother / $tani), $keta);
} else {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='リニア営業外費用その他'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_l_lother) < 1) {
        $rui_l_lother = 0 - $rui_s_lother_sagaku;   // 検索失敗
    } else {
        $rui_l_lother = $rui_l_lother - $rui_s_lother_sagaku;
        $rui_l_lother = number_format(($rui_l_lother / $tani), $keta);
    }
}
/********** 営業外費用の合計 **********/
    ///// 商管
$p2_b_nonope_loss_sum  = $p2_b_srisoku + $p2_b_lother;
$p2_b_srisoku          = number_format(($p2_b_srisoku / $tani), $keta);
$p2_b_lother           = number_format(($p2_b_lother / $tani), $keta);

$p1_b_nonope_loss_sum  = $p1_b_srisoku + $p1_b_lother;
$p1_b_srisoku          = number_format(($p1_b_srisoku / $tani), $keta);
$p1_b_lother           = number_format(($p1_b_lother / $tani), $keta);

$b_nonope_loss_sum     = $b_srisoku + $b_lother;
$b_srisoku             = number_format(($b_srisoku / $tani), $keta);
$b_lother              = number_format(($b_lother / $tani), $keta);

$rui_b_nonope_loss_sum = $rui_b_srisoku + $rui_b_lother;
$rui_b_srisoku         = number_format(($rui_b_srisoku / $tani), $keta);
$rui_b_lother          = number_format(($rui_b_lother / $tani), $keta);
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

    ///// 当月
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='全体営業外費用計'", $yyyymm);
if (getUniResult($query, $all_nonope_loss_sum) < 1) {
    $all_nonope_loss_sum = 0;                   // 検索失敗
} else {
    $all_nonope_loss_sum = number_format(($all_nonope_loss_sum / $tani), $keta);
}
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ営業外費用計再計算'", $yyyymm);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ営業外費用計'", $yyyymm);
}
if (getUniResult($query, $c_nonope_loss_sum) < 1) {
    $c_nonope_loss_sum = 0;                     // 検索失敗
    $c_nonope_loss_sum_temp = 0;
} else {
    $c_nonope_loss_sum_temp = $c_nonope_loss_sum;
    $c_nonope_loss_sum = number_format(($c_nonope_loss_sum / $tani), $keta);
}
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア営業外費用計再計算'", $yyyymm);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア営業外費用計'", $yyyymm);
}
if (getUniResult($query, $l_nonope_loss_sum) < 1) {
    $l_nonope_loss_sum = 0 - $s_nonope_loss_sum_sagaku;     // 検索失敗
} else {
    if ($yyyymm < 201001) {
        $l_nonope_loss_sum = $l_nonope_loss_sum - $s_nonope_loss_sum_sagaku;
    }
    $l_nonope_loss_sum = number_format(($l_nonope_loss_sum / $tani), $keta);
}
    ///// 前月
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='全体営業外費用計'", $p1_ym);
if (getUniResult($query, $p1_all_nonope_loss_sum) < 1) {
    $p1_all_nonope_loss_sum = 0;                // 検索失敗
} else {
    $p1_all_nonope_loss_sum = number_format(($p1_all_nonope_loss_sum / $tani), $keta);
}
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ営業外費用計再計算'", $p1_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ営業外費用計'", $p1_ym);
}
if (getUniResult($query, $p1_c_nonope_loss_sum) < 1) {
    $p1_c_nonope_loss_sum = 0;                     // 検索失敗
    $p1_c_nonope_loss_sum_temp = 0;
} else {
    $p1_c_nonope_loss_sum_temp = $p1_c_nonope_loss_sum;
    $p1_c_nonope_loss_sum = number_format(($p1_c_nonope_loss_sum / $tani), $keta);
}
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア営業外費用計再計算'", $p1_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア営業外費用計'", $p1_ym);
}
if (getUniResult($query, $p1_l_nonope_loss_sum) < 1) {
    $p1_l_nonope_loss_sum = 0 - $p1_s_nonope_loss_sum_sagaku;     // 検索失敗
} else {
    if ($p1_ym < 201001) {
        $p1_l_nonope_loss_sum = $p1_l_nonope_loss_sum - $p1_s_nonope_loss_sum_sagaku;
    }
    $p1_l_nonope_loss_sum = number_format(($p1_l_nonope_loss_sum / $tani), $keta);
}
    ///// 前前月
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='全体営業外費用計'", $p2_ym);
if (getUniResult($query, $p2_all_nonope_loss_sum) < 1) {
    $p2_all_nonope_loss_sum = 0;                // 検索失敗
} else {
    $p2_all_nonope_loss_sum = number_format(($p2_all_nonope_loss_sum / $tani), $keta);
}
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ営業外費用計再計算'", $p2_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ営業外費用計'", $p2_ym);
}
if (getUniResult($query, $p2_c_nonope_loss_sum) < 1) {
    $p2_c_nonope_loss_sum = 0;                     // 検索失敗
    $p2_c_nonope_loss_sum_temp = 0;
} else {
    $p2_c_nonope_loss_sum_temp = $p2_c_nonope_loss_sum;
    $p2_c_nonope_loss_sum = number_format(($p2_c_nonope_loss_sum / $tani), $keta);
}
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア営業外費用計再計算'", $p2_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア営業外費用計'", $p2_ym);
}
if (getUniResult($query, $p2_l_nonope_loss_sum) < 1) {
    $p2_l_nonope_loss_sum = 0 - $p2_s_nonope_loss_sum_sagaku;     // 検索失敗
} else {
    if ($p2_ym < 201001) {
        $p2_l_nonope_loss_sum = $p2_l_nonope_loss_sum - $p2_s_nonope_loss_sum_sagaku;
    }
    $p2_l_nonope_loss_sum = number_format(($p2_l_nonope_loss_sum / $tani), $keta);
}
    ///// 今期累計
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='全体営業外費用計'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_all_nonope_loss_sum) < 1) {
    $rui_all_nonope_loss_sum = 0;               // 検索失敗
} else {
    $rui_all_nonope_loss_sum = number_format(($rui_all_nonope_loss_sum / $tani), $keta);
}
if ($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='カプラ営業外費用計再計算'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_c_nonope_loss_sum) < 1) {
        $rui_c_nonope_loss_sum = 0;                           // 検索失敗
    } else {
        $rui_c_nonope_loss_sum = number_format(($rui_c_nonope_loss_sum / $tani), $keta);
    }
} elseif ($yyyymm >= 201001 && $yyyymm <= 201003) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=200904 and pl_bs_ym<=200912 and note='カプラ営業外費用計'");
    if (getUniResult($query, $rui_c_nonope_loss_sum_a) < 1) {
        $rui_c_nonope_loss_sum_a = 0;                          // 検索失敗
    }
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=201001 and pl_bs_ym<=%d and note='カプラ営業外費用計再計算'", $yyyymm);
    if (getUniResult($query, $rui_c_nonope_loss_sum_b) < 1) {
        $rui_c_nonope_loss_sum_b = 0;                          // 検索失敗
    }
    $rui_c_nonope_loss_sum = $rui_c_nonope_loss_sum_a + $rui_c_nonope_loss_sum_b;
    $rui_c_nonope_loss_sum = number_format(($rui_c_nonope_loss_sum / $tani), $keta);
} else {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='カプラ営業外費用計'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_c_nonope_loss_sum) < 1) {
        $rui_c_nonope_loss_sum = 0;                           // 検索失敗
    } else {
        $rui_c_nonope_loss_sum = number_format(($rui_c_nonope_loss_sum / $tani), $keta);
    }
}
if ($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='リニア営業外費用計再計算'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_l_nonope_loss_sum) < 1) {
        $rui_l_nonope_loss_sum = 0;                           // 検索失敗
    } else {
        $rui_l_nonope_loss_sum_temp = $rui_l_nonope_loss_sum;         // 経常利益計算用
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
    $rui_l_nonope_loss_sum      = $rui_l_nonope_loss_sum - $rui_s_srisoku_a - $rui_s_lother_a;
    $rui_l_nonope_loss_sum_temp = $rui_l_nonope_loss_sum;         // 経常利益計算用
    $rui_l_nonope_loss_sum      = number_format(($rui_l_nonope_loss_sum / $tani), $keta);
} else {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='リニア営業外費用計'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_l_nonope_loss_sum) < 1) {
        $rui_l_nonope_loss_sum = 0 - $rui_s_nonope_loss_sum_sagaku;     // 検索失敗
    } else {
        $rui_l_nonope_loss_sum = $rui_l_nonope_loss_sum - $rui_s_nonope_loss_sum_sagaku;
        $rui_l_nonope_loss_sum = number_format(($rui_l_nonope_loss_sum / $tani), $keta);
    }
}

/********** 経常利益 **********/
    ///// 商管
$p2_b_current_profit     = $p2_b_ope_profit + $p2_b_nonope_profit_sum - $p2_b_nonope_loss_sum;
$p2_b_ope_profit         = number_format(($p2_b_ope_profit / $tani), $keta);
$p2_b_nonope_profit_sum  = number_format(($p2_b_nonope_profit_sum / $tani), $keta);
$p2_b_nonope_loss_sum    = number_format(($p2_b_nonope_loss_sum / $tani), $keta);
$p2_b_current_profit     = number_format(($p2_b_current_profit / $tani), $keta);

$p1_b_current_profit     = $p1_b_ope_profit + $p1_b_nonope_profit_sum - $p1_b_nonope_loss_sum;
$p1_b_ope_profit         = number_format(($p1_b_ope_profit / $tani), $keta);
$p1_b_nonope_profit_sum  = number_format(($p1_b_nonope_profit_sum / $tani), $keta);
$p1_b_nonope_loss_sum    = number_format(($p1_b_nonope_loss_sum / $tani), $keta);
$p1_b_current_profit     = number_format(($p1_b_current_profit / $tani), $keta);

$b_current_profit        = $b_ope_profit + $b_nonope_profit_sum - $b_nonope_loss_sum;
$b_ope_profit            = number_format(($b_ope_profit / $tani), $keta);
$b_nonope_profit_sum     = number_format(($b_nonope_profit_sum / $tani), $keta);
$b_nonope_loss_sum       = number_format(($b_nonope_loss_sum / $tani), $keta);
$b_current_profit        = number_format(($b_current_profit / $tani), $keta);

$rui_b_current_profit    = $rui_b_ope_profit + $rui_b_nonope_profit_sum - $rui_b_nonope_loss_sum;
$rui_b_ope_profit        = number_format(($rui_b_ope_profit / $tani), $keta);
$rui_b_nonope_profit_sum = number_format(($rui_b_nonope_profit_sum / $tani), $keta);
$rui_b_nonope_loss_sum   = number_format(($rui_b_nonope_loss_sum / $tani), $keta);
$rui_b_current_profit    = number_format(($rui_b_current_profit / $tani), $keta);
    ///// 試験・修理
$p2_s_current_profit         = $p2_s_ope_profit_sagaku + $p2_s_nonope_profit_sum_sagaku - $p2_s_nonope_loss_sum_sagaku;
$p2_s_current_profit_sagaku  = $p2_s_current_profit;
$p2_s_current_profit         = $p2_s_current_profit + $p2_sc_uri_sagaku - $p2_sc_metarial_sagaku;      // カプラ試験修理を加味（sagakuの後－リニアからマイナスしてしまう為）
if ($p2_ym == 200912) {
    $p2_s_current_profit = $p2_s_current_profit + 1409708;
}
if ($p2_ym >= 201001) {
    $p2_s_current_profit = $p2_s_current_profit + $p2_s_kyu_kei - $p2_s_kyu_kin;  // 添田さんの給与をC・Lは35%。試験修理に30%振分
    //$p2_s_current_profit = $p2_s_current_profit + 432323 - 129697;  // 添田さんの給与をC・Lは35%。試験修理に30%振分
}
$p2_s_current_profit         = number_format(($p2_s_current_profit / $tani), $keta);

$p1_s_current_profit         = $p1_s_ope_profit_sagaku + $p1_s_nonope_profit_sum_sagaku - $p1_s_nonope_loss_sum_sagaku;
$p1_s_current_profit_sagaku  = $p1_s_current_profit;
$p1_s_current_profit         = $p1_s_current_profit + $p1_sc_uri_sagaku - $p1_sc_metarial_sagaku;      // カプラ試験修理を加味（sagakuの後－リニアからマイナスしてしまう為）
if ($p1_ym == 200912) {
    $p1_s_current_profit = $p1_s_current_profit + 1409708;
}
if ($p1_ym >= 201001) {
    $p1_s_current_profit = $p1_s_current_profit + $p1_s_kyu_kei - $p1_s_kyu_kin;  // 添田さんの給与をC・Lは35%。試験修理に30%振分
    //$p1_s_current_profit = $p1_s_current_profit + 432323 - 129697;  // 添田さんの給与をC・Lは35%。試験修理に30%振分
}
$p1_s_current_profit         = number_format(($p1_s_current_profit / $tani), $keta);

$s_current_profit            = $s_ope_profit_sagaku + $s_nonope_profit_sum_sagaku - $s_nonope_loss_sum_sagaku;
$s_current_profit_sagaku     = $s_current_profit;
$s_current_profit            = $s_current_profit + $sc_uri_sagaku - $sc_metarial_sagaku;      // カプラ試験修理を加味（sagakuの後－リニアからマイナスしてしまう為）
if ($yyyymm == 200912) {
    $s_current_profit = $s_current_profit + 1409708;
}
if ($yyyymm >= 201001) {
    $s_current_profit = $s_current_profit + $s_kyu_kei - $s_kyu_kin;  // 添田さんの給与をC・Lは35%。試験修理に30%振分
    //$s_current_profit = $s_current_profit + 432323 - 129697;  // 添田さんの給与をC・Lは35%。試験修理に30%振分
}
$s_current_profit            = number_format(($s_current_profit / $tani), $keta);

$rui_s_current_profit        = $rui_s_ope_profit_sagaku + $rui_s_nonope_profit_sum_sagaku - $rui_s_nonope_loss_sum_sagaku;
$rui_s_current_profit_sagaku = $rui_s_current_profit;
$rui_s_current_profit        = $rui_s_current_profit + $rui_sc_uri_sagaku - $rui_sc_metarial_sagaku;      // カプラ試験修理を加味（sagakuの後－リニアからマイナスしてしまう為）
if ($yyyymm >= 200912 && $yyyymm <= 201003) {
    $rui_s_current_profit = $rui_s_current_profit + 1409708;
}
if ($yyyymm >= 201001) {
    $rui_s_current_profit = $rui_s_current_profit + $rui_s_kyu_kei - $rui_s_kyu_kin;  // 添田さんの給与をC・Lは35%。試験修理に30%振分
    //$rui_s_current_profit = $rui_s_current_profit + 432323 - 129697;  // 添田さんの給与をC・Lは35%。試験修理に30%振分
}
$rui_s_current_profit        = number_format(($rui_s_current_profit / $tani), $keta);

    ///// 当月
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='全体経常利益'", $yyyymm);
if (getUniResult($query, $all_current_profit) < 1) {
    $all_current_profit   = 0;                // 検索失敗
    $all_current_profit_t = 0;                // 検索失敗
} else {
    if ($yyyymm == 201002) {
        $all_current_profit = $all_current_profit + 600000;
    }
    if ($yyyymm == 201003) {
        $all_current_profit = $all_current_profit - 600000;
    }
    // 2012/02/08 追加 2012年1月度 業務委託費（平出横川派遣料）調整
    if ($yyyymm == 201201) {
        $all_current_profit -=1156130;
    }
    // 2012/03/05 追加 2012年1月度 業務委託費（平出横川派遣料）調整 戻し
    if ($yyyymm == 201202) {
        $all_current_profit +=1156130;
    }
    $all_current_profit   = $all_current_profit + $b_uri_sagaku;
    $all_current_profit_t = $all_current_profit;
    $all_current_profit   = number_format(($all_current_profit / $tani), $keta);
}
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ経常利益再計算'", $yyyymm);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ経常利益'", $yyyymm);
}
if (getUniResult($query, $c_current_profit) < 1) {
    $c_current_profit = 0;                  // 検索失敗
} else {
    if ($yyyymm < 201001) {
        $c_current_profit = $c_current_profit + $b_sagaku + $c_allo_kin - $sc_uri_sagaku + $sc_metarial_sagaku; // カプラ試験修理を加味
    } else {
        $c_current_profit = $c_ope_profit_temp + $c_nonope_profit_sum_temp - $c_nonope_loss_sum_temp;
    }
    if ($yyyymm == 200912) {
        $c_current_profit = $c_current_profit - 1227429;
    }
    if ($yyyymm >= 201001) {
        //$c_current_profit = $c_current_profit - $c_kyu_kin;
        //$c_current_profit = $c_current_profit - 151313;
    }
    $c_current_profit = number_format(($c_current_profit / $tani), $keta);
}
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア経常利益再計算'", $yyyymm);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア経常利益'", $yyyymm);
}
if (getUniResult($query, $l_current_profit) < 1) {
    $l_current_profit = 0 - $s_current_profit_sagaku;       // 検索失敗
    $l_current_profit = number_format(($l_current_profit / $tani), $keta);
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
    $l_current_profit = number_format(($l_current_profit / $tani), $keta);
}
    ///// 前月
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='全体経常利益'", $p1_ym);
if (getUniResult($query, $p1_all_current_profit) < 1) {
    $p1_all_current_profit = 0;             // 検索失敗
} else {
    if ($p1_ym == 201002) {
        $p1_all_current_profit = $p1_all_current_profit + 600000;
    }
    if ($p1_ym == 201003) {
        $p1_all_current_profit = $p1_all_current_profit - 600000;
    }
    // 2012/02/08 追加 2012年1月度 業務委託費（平出横川派遣料）調整
    if ($p1_ym == 201201) {
        $p1_all_current_profit -=1156130;
    }
    // 2012/03/05 追加 2012年1月度 業務委託費（平出横川派遣料）調整 戻し
    if ($p1_ym == 201202) {
        $p1_all_current_profit +=1156130;
    }
    $p1_all_current_profit = $p1_all_current_profit + $p1_b_uri_sagaku;
    $p1_all_current_profit = number_format(($p1_all_current_profit / $tani), $keta);
}
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ経常利益再計算'", $p1_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ経常利益'", $p1_ym);
}
if (getUniResult($query, $p1_c_current_profit) < 1) {
    $p1_c_current_profit = 0;                  // 検索失敗
} else {
    if ($p1_ym < 201001) {
        $p1_c_current_profit = $p1_c_current_profit + $p1_b_sagaku + $p1_c_allo_kin - $p1_sc_uri_sagaku + $p1_sc_metarial_sagaku; // カプラ試験修理を加味
    } else {
        $p1_c_current_profit = $p1_c_ope_profit_temp + $p1_c_nonope_profit_sum_temp - $p1_c_nonope_loss_sum_temp;
    }
    if ($p1_ym == 200912) {
        $p1_c_current_profit = $p1_c_current_profit - 1227429;
    }
    if ($p1_ym >= 201001) {
        //$p1_c_current_profit = $p1_c_current_profit - $p1_c_kyu_kin;   // 添田さんの給与をC・Lは35%。試験修理に30%振分
        //$p1_c_current_profit = $p1_c_current_profit - 151313;   // 添田さんの給与をC・Lは35%。試験修理に30%振分
    }
    $p1_c_current_profit = number_format(($p1_c_current_profit / $tani), $keta);
}
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア経常利益再計算'", $p1_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア経常利益'", $p1_ym);
}
if (getUniResult($query, $p1_l_current_profit) < 1) {
    $p1_l_current_profit = 0 - $p1_s_current_profit_sagaku;       // 検索失敗
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
        //$p1_l_current_profit = $p1_l_current_profit - 151313;   // 添田さんの給与をC・Lは35%。試験修理に30%振分
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
    $p1_l_current_profit = number_format(($p1_l_current_profit / $tani), $keta);
}
    ///// 前前月
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='全体経常利益'", $p2_ym);
if (getUniResult($query, $p2_all_current_profit) < 1) {
    $p2_all_current_profit = 0;             // 検索失敗
} else {
    if ($p2_ym == 201002) {
        $p2_all_current_profit = $p2_all_current_profit + 600000;
    }
    if ($p2_ym == 201003) {
        $p2_all_current_profit = $p2_all_current_profit - 600000;
    }
    // 2012/02/08 追加 2012年1月度 業務委託費（平出横川派遣料）調整
    if ($p2_ym == 201201) {
        $p2_all_current_profit -=1156130;
    }
    // 2012/03/05 追加 2012年1月度 業務委託費（平出横川派遣料）調整 戻し
    if ($p2_ym == 201202) {
        $p2_all_current_profit +=1156130;
    }
    $p2_all_current_profit = $p2_all_current_profit + $p2_b_uri_sagaku;
    $p2_all_current_profit = number_format(($p2_all_current_profit / $tani), $keta);
}
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ経常利益再計算'", $p2_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ経常利益'", $p2_ym);
}
if (getUniResult($query, $p2_c_current_profit) < 1) {
    $p2_c_current_profit = 0;                  // 検索失敗
} else {
    if ($p2_ym < 201001) {
        $p2_c_current_profit = $p2_c_current_profit + $p2_b_sagaku + $p2_c_allo_kin - $p2_sc_uri_sagaku + $p2_sc_metarial_sagaku; // カプラ試験修理を加味
    } else {
        $p2_c_current_profit = $p2_c_ope_profit_temp + $p2_c_nonope_profit_sum_temp - $p2_c_nonope_loss_sum_temp;
    }
    if ($p2_ym == 200912) {
        $p2_c_current_profit = $p2_c_current_profit - 1227429;
    }
    if ($p2_ym >= 201001) {
        //$p2_c_current_profit = $p2_c_current_profit - $p2_c_kyu_kin;   // 添田さんの給与をC・Lは35%。試験修理に30%振分
        //$p2_c_current_profit = $p2_c_current_profit - 151313;   // 添田さんの給与をC・Lは35%。試験修理に30%振分
    }
    $p2_c_current_profit = number_format(($p2_c_current_profit / $tani), $keta);
}
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア経常利益再計算'", $p2_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア経常利益'", $p2_ym);
}
if (getUniResult($query, $p2_l_current_profit) < 1) {
    $p2_l_current_profit = 0 - $p2_s_current_profit_sagaku;       // 検索失敗
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
    $p2_l_current_profit = number_format(($p2_l_current_profit / $tani), $keta);
}
    ///// 今期累計
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='全体経常利益'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_all_current_profit) < 1) {
    $rui_all_current_profit = 0;            // 検索失敗
} else {
    if ($yyyymm >= 201002 && $yyyymm <= 201003) {
        $rui_all_current_profit = $rui_all_current_profit + 600000;
    }
    if ($yyyymm == 201003) {
        $rui_all_current_profit = $rui_all_current_profit - 600000;
    }
    // 2012/02/08 追加 2012年1月度 業務委託費（平出横川派遣料）調整
    if ($yyyymm >= 201201 && $yyyymm <= 201203) {
        $rui_all_current_profit -=1156130;
    }
    // 2012/03/05 追加 2012年1月度 業務委託費（平出横川派遣料）調整 戻し
    if ($yyyymm >= 201202 && $yyyymm <= 201203) {
        $rui_all_current_profit +=1156130;
    }
    $rui_all_current_profit   = $rui_all_current_profit + $rui_b_uri_sagaku;
    $rui_all_current_profit_t = $rui_all_current_profit;
    $rui_all_current_profit   = number_format(($rui_all_current_profit / $tani), $keta);
}
if ($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='カプラ経常利益再計算'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_c_current_profit) < 1) {
        $rui_c_current_profit = 0;                           // 検索失敗
    } else {
        $rui_c_current_profit = $rui_c_current_profit + $rui_b_sagaku + $rui_c_allo_kin - $rui_sc_uri_sagaku + $rui_sc_metarial_sagaku; // カプラ試験修理を加味
        if ($yyyymm >= 201001) {
            $rui_c_current_profit = $rui_c_current_profit - $rui_c_kyu_kin; // 添田さんの給与をC・Lは35%。試験修理に30%振分
            //$rui_c_current_profit = $rui_c_current_profit - 151313; // 添田さんの給与をC・Lは35%。試験修理に30%振分
        }
        // 2013/11/07 追加 2013年10月度 業務委託費（横川派遣料）調整
        if ($yyyymm >= 201310 && $yyyymm <= 201403) {
            $rui_c_current_profit += 1245035;
        }
        if ($yyyymm >= 201311 && $yyyymm <= 201403) {
            $rui_c_current_profit -= 1245035;
        }
        if ($yyyymm >= 201408 && $yyyymm <= 201503) {
            $rui_c_current_profit += 229464;
        }
        $rui_c_current_profit = number_format(($rui_c_current_profit / $tani), $keta);
    }
} elseif ($yyyymm >= 201001 && $yyyymm <= 201003) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=200904 and pl_bs_ym<=200912 and note='カプラ経常利益'");
    if (getUniResult($query, $rui_c_current_profit_a) < 1) {
        $rui_c_current_profit_a = 0;                          // 検索失敗
    }
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=201001 and pl_bs_ym<=%d and note='カプラ経常利益再計算'", $yyyymm);
    if (getUniResult($query, $rui_c_current_profit_b) < 1) {
        $rui_c_current_profit_b = 0;                          // 検索失敗
    }
    $rui_c_current_profit = $rui_c_current_profit_a + $rui_c_current_profit_b;
    if ($yyyymm >= 200912 && $yyyymm <= 201003) {
        $rui_c_current_profit = $rui_c_current_profit - 1227429;
    }
    if ($yyyymm >= 201001) {
        $rui_c_current_profit = $rui_c_current_profit - $rui_c_kyu_kin; // 添田さんの給与をC・Lは35%。試験修理に30%振分
        //$rui_c_current_profit = $rui_c_current_profit - 151313; // 添田さんの給与をC・Lは35%。試験修理に30%振分
    }
    $rui_c_current_profit = $rui_c_current_profit + $rui_b_sagaku + $rui_c_allo_kin - $rui_sc_uri_sagaku + $rui_sc_metarial_sagaku  - $rui_b_pother_a; // カプラ試験修理を加味
    $rui_c_current_profit = number_format(($rui_c_current_profit / $tani), $keta);
} else {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='カプラ経常利益'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_c_current_profit) < 1) {
        $rui_c_current_profit = 0;                           // 検索失敗
    } else {
        $rui_c_current_profit = $rui_c_current_profit + $rui_b_sagaku + $rui_c_allo_kin - $rui_sc_uri_sagaku + $rui_sc_metarial_sagaku; // カプラ試験修理を加味
        if ($yyyymm >= 200912 && $yyyymm <= 201003) {
            $rui_c_current_profit = $rui_c_current_profit - 1227429;
        }
        $rui_c_current_profit = number_format(($rui_c_current_profit / $tani), $keta);
    }
}
if ($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='リニア経常利益再計算'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_l_current_profit) < 1) {
        $rui_l_current_profit = 0;                           // 検索失敗
    } else {
        // 2012/02/08 追加 2012年1月度 業務委託費（平出横川派遣料）調整
        if ($yyyymm >= 201201 && $yyyymm <= 201203) {
            $rui_l_current_profit -=1156130;
        }
        // 2012/03/05 追加 2012年1月度 業務委託費（平出横川派遣料）調整 戻し
        if ($yyyymm >= 201202 && $yyyymm <= 201203) {
            $rui_l_current_profit +=1156130;
        }
        //$rui_l_current_profit = $rui_l_current_profit - $rui_s_current_profit_sagaku + $rui_l_allo_kin;
        $rui_l_current_profit = $rui_l_ope_profit_temp + $rui_l_nonope_profit_sum_temp - $rui_l_nonope_loss_sum_temp;
        $rui_l_current_profit = number_format(($rui_l_current_profit / $tani), $keta);
    }
} elseif ($yyyymm >= 201001 && $yyyymm <= 201003) {
    //$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=200904 and pl_bs_ym<=200912 and note='リニア経常利益'");
    //if (getUniResult($query, $rui_l_current_profit_a) < 1) {
    //    $rui_l_current_profit_a = 0;                          // 検索失敗
    //}
    //$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=201001 and pl_bs_ym<=%d and note='リニア経常利益再計算'", $yyyymm);
    //if (getUniResult($query, $rui_l_current_profit_b) < 1) {
    //    $rui_l_current_profit_b = 0;                          // 検索失敗
    //}
    //$rui_l_current_profit = $rui_l_current_profit_a + $rui_l_current_profit_b;
    //if ($yyyymm >= 200912 && $yyyymm <= 201003) {
    //    $rui_l_current_profit = $rui_l_current_profit - 182279;
    //}
    //$rui_l_current_profit = $rui_l_current_profit - $rui_s_current_profit_sagaku + $rui_l_allo_kin;
    //$rui_l_current_profit = $rui_l_current_profit - $rui_s_ope_profit_sagaku + $rui_l_allo_kin;
    
    $rui_l_current_profit = $rui_l_ope_profit_temp + $rui_l_nonope_profit_sum_temp - $rui_l_nonope_loss_sum_temp;
    $rui_l_current_profit = number_format(($rui_l_current_profit / $tani), $keta);
} else {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='リニア経常利益'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_l_current_profit) < 1) {
        $rui_l_current_profit = 0 - $rui_s_current_profit_sagaku;   // 検索失敗
    } else {
        $rui_l_current_profit = $rui_l_current_profit - $rui_s_current_profit_sagaku + $rui_l_allo_kin;
        if ($yyyymm >= 200912 && $yyyymm <= 201003) {
            $rui_l_current_profit = $rui_l_current_profit - 182279;
        }
        $rui_l_current_profit = number_format(($rui_l_current_profit / $tani), $keta);
    }
}

/********** 特別利益 **********/
    ///// 当月
$query = sprintf("select kin1 from pl_bs_summary where t_id='C' and t_row=1 and pl_bs_ym=%d", $yyyymm);
if (getUniResult($query, $all_special_profit) < 1) {
    $all_special_profit   = 0;            // 検索失敗
    $all_special_profit_t = 0;            // 検索失敗
} else {
    $all_special_profit_t = $all_special_profit;
    $all_special_profit = number_format(($all_special_profit / $tani), $keta);
}
    ///// 今期累計
$query = sprintf("select sum(kin1) from pl_bs_summary where t_id='C' and t_row=1 and pl_bs_ym>=%d and pl_bs_ym<=%d", $str_ym, $yyyymm);
if (getUniResult($query, $rui_all_special_profit) < 1) {
    $rui_all_special_profit   = 0;            // 検索失敗
    $rui_all_special_profit_t = 0;            // 検索失敗
} else {
    $rui_all_special_profit_t = $rui_all_special_profit;
    $rui_all_special_profit   = number_format(($rui_all_special_profit / $tani), $keta);
}

/********** 特別損失 **********/
    ///// 当月
$query = sprintf("select kin1 from pl_bs_summary where t_id='C' and t_row=2 and pl_bs_ym=%d", $yyyymm);
if (getUniResult($query, $all_special_loss) < 1) {
    $all_special_loss   = 0;            // 検索失敗
    $all_special_loss_t = 0;            // 検索失敗
} else {
    $all_special_loss_t = $all_special_loss;
    $all_special_loss   = number_format(($all_special_loss / $tani), $keta);
}
    ///// 今期累計
$query = sprintf("select sum(kin1) from pl_bs_summary where t_id='C' and t_row=2 and pl_bs_ym>=%d and pl_bs_ym<=%d", $str_ym, $yyyymm);
if (getUniResult($query, $rui_all_special_loss) < 1) {
    $rui_all_special_loss   = 0;            // 検索失敗
    $rui_all_special_loss_t = 0;            // 検索失敗
} else {
    $rui_all_special_loss_t = $rui_all_special_loss;
    $rui_all_special_loss   = number_format(($rui_all_special_loss / $tani), $keta);
}

/********** 税引前純利益金額 **********/
    ///// 当月
$all_before_tax_net_profit_t = $all_current_profit_t + $all_special_profit_t - $all_special_loss_t;
$all_before_tax_net_profit   = number_format(($all_before_tax_net_profit_t / $tani), $keta);
    ///// 今期累計
$rui_all_before_tax_net_profit_t = $rui_all_current_profit_t + $rui_all_special_profit_t - $rui_all_special_loss_t;
$rui_all_before_tax_net_profit   = number_format(($rui_all_before_tax_net_profit_t / $tani), $keta);
/********** 法人税等 **********/
    ///// 当月
$query = sprintf("SELECT SUM(kin1) FROM pl_bs_summary WHERE t_id='C' AND t_row>=3 AND pl_bs_ym=%d", $yyyymm);
if (getUniResult($query, $all_corporation_tax) < 1) {
    $all_corporation_tax   = 0;            // 検索失敗
    $all_corporation_tax_t = 0;            // 検索失敗
} else {
    $all_corporation_tax_t = $all_corporation_tax;
    $all_corporation_tax   = number_format(($all_corporation_tax / $tani), $keta);
}
    ///// 今期累計
$query = sprintf("select sum(kin1) from pl_bs_summary where t_id='C' and t_row>=3 and pl_bs_ym>=%d and pl_bs_ym<=%d", $str_ym, $yyyymm);
if (getUniResult($query, $rui_all_corporation_tax) < 1) {
    $rui_all_corporation_tax   = 0;            // 検索失敗
    $rui_all_corporation_tax_t = 0;            // 検索失敗
} else {
    $rui_all_corporation_tax_t = $rui_all_corporation_tax;
    $rui_all_corporation_tax   = number_format(($rui_all_corporation_tax / $tani), $keta);
}

/********** 当期純利益金額 **********/
    ///// 当月
$all_net_profit_t = $all_before_tax_net_profit_t - $all_corporation_tax_t;
$all_net_profit   = number_format(($all_net_profit_t / $tani), $keta);
    ///// 今期累計
$rui_all_net_profit_t = $rui_all_before_tax_net_profit_t - $rui_all_corporation_tax_t;
$rui_all_net_profit   = number_format(($rui_all_net_profit_t / $tani), $keta);

////////// 特記事項の取得
$query = sprintf("select comment from act_comment_history where pl_bs_ym=%d and item='カプラ損益計算書'", $yyyymm);
if (getUniResult($query,$comment_c) <= 0) {
    $comment_c = "";
}
$query = sprintf("select comment from act_comment_history where pl_bs_ym=%d and item='リニア損益計算書'", $yyyymm);
if (getUniResult($query,$comment_l) <= 0) {
    $comment_l = "";
}
$query = sprintf("select comment from act_comment_history where pl_bs_ym=%d and item='試験・修理損益計算書'", $yyyymm);
if (getUniResult($query,$comment_s) <= 0) {
    $comment_s = "";
}

$query = sprintf("select comment from act_comment_history where pl_bs_ym=%d and item='商品管理損益計算書'", $yyyymm);
if (getUniResult($query,$comment_b) <= 0) {
    $comment_b = "";
}
$query = sprintf("select comment from act_comment_history where pl_bs_ym=%d and item='全体損益計算書'", $yyyymm);
if (getUniResult($query,$comment_all) <= 0) {
    $comment_all = "";
}
$query = sprintf("select comment from act_comment_history where pl_bs_ym=%d and item='その他損益計算書'", $yyyymm);
if (getUniResult($query,$comment_other) <= 0) {
    $comment_other = "";
}
//$test  = "売上高";
//$test2 = "カプラ";
//$test  = $test2 . $test;
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
    // 2012/01/13 追加
    $item[20]  = "特別利益";
    $item[21]  = "特別損失";
    $item[22]  = "税引前純利益金額";
    $item[23]  = "法人税等";
    $item[24]  = "当期純利益金額";
    ///////// 各データの保管 カプラ=0 リニア=1 試修=2 商管=3 全体=4
    $input_data = array();
    for ($i = 0; $i < 25; $i++) {
        switch ($i) {
                case  0:                                            // 売上高
                    $input_data[$i][0] = $c_uri;                    // カプラ
                    $input_data[$i][1] = $l_uri;                    // リニア
                    $input_data[$i][2] = $s_uri;                    // 試修
                    $input_data[$i][3] = $b_uri;                    // 商管
                    $input_data[$i][4] = $all_uri;                  // 全体
                break;
                case  1:                                            // 期首材料仕掛品棚卸高
                    $input_data[$i][0] = $c_invent;                 // カプラ
                    $input_data[$i][1] = $l_invent;                 // リニア
                    $input_data[$i][2] = $s_invent;                 // 試修
                    $input_data[$i][3] = $b_invent;                 // 商管
                    $input_data[$i][4] = $all_invent;               // 全体
                break;
                case  2:                                            // 材料費(仕入高)
                    $input_data[$i][0] = $c_metarial;               // カプラ
                    $input_data[$i][1] = $l_metarial;               // リニア
                    $input_data[$i][2] = $s_metarial;               // 試修
                    $input_data[$i][3] = $b_metarial;               // 商管
                    $input_data[$i][4] = $all_metarial;             // 全体
                break;
                case  3:                                            // 労務費
                    $input_data[$i][0] = $c_roumu;                  // カプラ
                    $input_data[$i][1] = $l_roumu;                  // リニア
                    $input_data[$i][2] = $s_roumu;                  // 試修
                    $input_data[$i][3] = $b_roumu;                  // 商管
                    $input_data[$i][4] = $all_roumu;                // 全体
                break;
                case  4:                                            // 製造経費
                    $input_data[$i][0] = $c_expense;                // カプラ
                    $input_data[$i][1] = $l_expense;                // リニア
                    $input_data[$i][2] = $s_expense;                // 試修
                    $input_data[$i][3] = $b_expense;                // 商管
                    $input_data[$i][4] = $all_expense;              // 全体
                break;
                case  5:                                            // 期末材料仕掛品棚卸高
                    $input_data[$i][0] = $c_endinv;                 // カプラ
                    $input_data[$i][1] = $l_endinv;                 // リニア
                    $input_data[$i][2] = $s_endinv;                 // 試修
                    $input_data[$i][3] = $b_endinv;                 // 商管
                    $input_data[$i][4] = $all_endinv;               // 全体
                break;
                case  6:                                            // 売上原価
                    $input_data[$i][0] = $c_urigen;                 // カプラ
                    $input_data[$i][1] = $l_urigen;                 // リニア
                    $input_data[$i][2] = $s_urigen;                 // 試修
                    $input_data[$i][3] = $b_urigen;                 // 商管
                    $input_data[$i][4] = $all_urigen;               // 全体
                break;
                case  7:                                            // 売上総利益
                    $input_data[$i][0] = $c_gross_profit;           // カプラ
                    $input_data[$i][1] = $l_gross_profit;           // リニア
                    $input_data[$i][2] = $s_gross_profit;           // 試修
                    $input_data[$i][3] = $b_gross_profit;           // 商管
                    $input_data[$i][4] = $all_gross_profit;         // 全体
                break;
                case  8:                                            // 人件費
                    $input_data[$i][0] = $c_han_jin;                // カプラ
                    $input_data[$i][1] = $l_han_jin;                // リニア
                    $input_data[$i][2] = $s_han_jin;                // 試修
                    $input_data[$i][3] = $b_han_jin;                // 商管
                    $input_data[$i][4] = $all_han_jin;              // 全体
                break;
                case  9:                                            // 経費
                    $input_data[$i][0] = $c_han_kei;                // カプラ
                    $input_data[$i][1] = $l_han_kei;                // リニア
                    $input_data[$i][2] = $s_han_kei;                // 試修
                    $input_data[$i][3] = $b_han_kei;                // 商管
                    $input_data[$i][4] = $all_han_kei;              // 全体
                break;
                case 10:                                            // 販管費及び一般管理費計
                    $input_data[$i][0] = $c_han_all;                // カプラ
                    $input_data[$i][1] = $l_han_all;                // リニア
                    $input_data[$i][2] = $s_han_all;                // 試修
                    $input_data[$i][3] = $b_han_all;                // 商管
                    $input_data[$i][4] = $all_han_all;              // 全体
                break;
                case 11:                                            // 営業利益
                    $input_data[$i][0] = $c_ope_profit;             // カプラ
                    $input_data[$i][1] = $l_ope_profit;             // リニア
                    $input_data[$i][2] = $s_ope_profit;             // 試修
                    $input_data[$i][3] = $b_ope_profit;             // 商管
                    $input_data[$i][4] = $all_ope_profit;           // 全体
                break;
                case 12:                                            // 業務委託収入
                    $input_data[$i][0] = $c_gyoumu;                 // カプラ
                    $input_data[$i][1] = $l_gyoumu;                 // リニア
                    $input_data[$i][2] = $s_gyoumu;                 // 試修
                    $input_data[$i][3] = $b_gyoumu;                 // 商管
                    $input_data[$i][4] = $all_gyoumu;               // 全体
                break;
                case 13:                                            // 仕入割引
                    $input_data[$i][0] = $c_swari;                  // カプラ
                    $input_data[$i][1] = $l_swari;                  // リニア
                    $input_data[$i][2] = $s_swari;                  // 試修
                    $input_data[$i][3] = $b_swari;                  // 商管
                    $input_data[$i][4] = $all_swari;                // 全体
                break;
                case 14:                                            // 営業外収益その他
                    $input_data[$i][0] = $c_pother;                 // カプラ
                    $input_data[$i][1] = $l_pother;                 // リニア
                    $input_data[$i][2] = $s_pother;                 // 試修
                    $input_data[$i][3] = $b_pother;                 // 商管
                    $input_data[$i][4] = $all_pother;               // 全体
                break;
                case 15:                                            // 営業外収益計
                    $input_data[$i][0] = $c_nonope_profit_sum;      // カプラ
                    $input_data[$i][1] = $l_nonope_profit_sum;      // リニア
                    $input_data[$i][2] = $s_nonope_profit_sum;      // 試修
                    $input_data[$i][3] = $b_nonope_profit_sum;      // 商管
                    $input_data[$i][4] = $all_nonope_profit_sum;    // 全体
                break;
                case 16:                                            // 支払利息
                    $input_data[$i][0] = $c_srisoku;                // カプラ
                    $input_data[$i][1] = $l_srisoku;                // リニア
                    $input_data[$i][2] = $s_srisoku;                // 試修
                    $input_data[$i][3] = $b_srisoku;                // 商管
                    $input_data[$i][4] = $all_srisoku;              // 全体
                break;
                case 17:                                            // 営業外費用その他
                    $input_data[$i][0] = $c_lother;                 // カプラ
                    $input_data[$i][1] = $l_lother;                 // リニア
                    $input_data[$i][2] = $s_lother;                 // 試修
                    $input_data[$i][3] = $b_lother;                 // 商管
                    $input_data[$i][4] = $all_lother;               // 全体
                break;
                case 18:                                            // 営業外費用計
                    $input_data[$i][0] = $c_nonope_loss_sum;        // カプラ
                    $input_data[$i][1] = $l_nonope_loss_sum;        // リニア
                    $input_data[$i][2] = $s_nonope_loss_sum;        // 試修
                    $input_data[$i][3] = $b_nonope_loss_sum;        // 商管
                    $input_data[$i][4] = $all_nonope_loss_sum;      // 全体
                break;
                case 19:                                            // 経常利益
                    $input_data[$i][0] = $c_current_profit;         // カプラ
                    $input_data[$i][1] = $l_current_profit;         // リニア
                    $input_data[$i][2] = $s_current_profit;         // 試修
                    $input_data[$i][3] = $b_current_profit;         // 商管
                    $input_data[$i][4] = $all_current_profit;       // 全体
                break;
                // 2012/01/13 追加
                case 20:                                            // 特別利益
                    $input_data[$i][0] = 0;                         // カプラ
                    $input_data[$i][1] = 0;                         // リニア
                    $input_data[$i][2] = 0;                         // 試修
                    $input_data[$i][3] = 0;                         // 商管
                    $input_data[$i][4] = $all_special_profit;       // 全体
                break;
                case 21:                                            // 特別損失
                    $input_data[$i][0] = 0;                         // カプラ
                    $input_data[$i][1] = 0;                         // リニア
                    $input_data[$i][2] = 0;                         // 試修
                    $input_data[$i][3] = 0;                         // 商管
                    $input_data[$i][4] = $all_special_loss;         // 全体
                break;
                case 22:                                                    // 税引前純利益金額
                    $input_data[$i][0] = 0;                                 // カプラ
                    $input_data[$i][1] = 0;                                 // リニア
                    $input_data[$i][2] = 0;                                 // 試修
                    $input_data[$i][3] = 0;                                 // 商管
                    $input_data[$i][4] = $all_before_tax_net_profit;        // 全体
                break;
                case 23:                                            // 法人税等
                    $input_data[$i][0] = 0;                         // カプラ
                    $input_data[$i][1] = 0;                         // リニア
                    $input_data[$i][2] = 0;                         // 試修
                    $input_data[$i][3] = 0;                         // 商管
                    $input_data[$i][4] = $all_corporation_tax;      // 全体
                break;
                case 24:                                            // 当期純利益金額
                    $input_data[$i][0] = 0;                         // カプラ
                    $input_data[$i][1] = 0;                         // リニア
                    $input_data[$i][2] = 0;                         // 試修
                    $input_data[$i][3] = 0;                         // 商管
                    $input_data[$i][4] = $all_net_profit;           // 全体
                break;
                default:
                break;
            }
    }
    // カプラ登録
    $head  = "カプラ";
    $sec   = 0;
    insert_date($head,$item,$yyyymm,$input_data,$sec);
    // リニア登録
    $head  = "リニア";
    $sec   = 1;
    insert_date($head,$item,$yyyymm,$input_data,$sec);
    // 試験修理登録
    $head  = "試験修理";
    $sec   = 2;
    insert_date($head,$item,$yyyymm,$input_data,$sec);
    // 商品管理登録
    $head  = "商品管理";
    $sec   = 3;
    insert_date($head,$item,$yyyymm,$input_data,$sec);
    // 全体登録
    $head  = "全体";
    $sec   = 4;
    insert_date($head,$item,$yyyymm,$input_data,$sec);  
}
function insert_date($head,$item,$yyyymm,$input_data,$sec) 
{
    for ($i = 0; $i < 25; $i++) {
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
                    <td colspan='4' align='center' class='pt10b' bgcolor='#ffffc6'>カ　プ　ラ</td>
                    <td colspan='4' align='center' class='pt10b' bgcolor='#ffffc6'>リ　ニ　ア</td>
                    <td colspan='4' align='center' class='pt10b' bgcolor='#ffffc6'>試験・修理</td>
                    <td colspan='4' align='center' class='pt10b' bgcolor='#ffffc6'>商品管理</td>
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
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p2_c_uri ?>   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_c_uri ?>   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $c_uri ?>      </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $rui_c_uri ?>  </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p2_l_uri ?>   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_l_uri ?>   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $l_uri ?>      </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $rui_l_uri ?>  </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p2_s_uri ?>   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_s_uri ?>   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $s_uri ?>      </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $rui_s_uri ?>  </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p2_b_uri ?>   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_b_uri ?>   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $b_uri ?>      </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $rui_b_uri ?>  </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p2_all_uri ?> </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_all_uri ?> </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $all_uri ?>    </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $rui_all_uri ?></td>
                    <td nowrap align='left'  class='pt10' bgcolor='#d6d3ce'>実際売上高</td>
                </tr>
                <tr>
                    <td rowspan='6' width='10' align='center' valign='middle' class='pt10b' bgcolor='#ffffc6'>売上原価</td> <!-- 売上原価 -->
                    <td nowrap align='left' class='pt10b' bgcolor='white'>　期首材料仕掛品棚卸高</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_c_invent ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_c_invent ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $c_invent ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_c_invent ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_l_invent ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_l_invent ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $l_invent ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_l_invent ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_s_invent ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_s_invent ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $s_invent ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_s_invent ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_b_invent ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_b_invent ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $b_invent ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_b_invent ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_all_invent ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_all_invent ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $all_invent ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_all_invent ?></td>
                    <td nowrap align='left'  class='pt10'>総平均単価による棚卸高</td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='#e6e6e6'>　材料費(仕入高)</td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_c_metarial ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_c_metarial ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $c_metarial ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_c_metarial ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_l_metarial ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_l_metarial ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $l_metarial ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_l_metarial ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_s_metarial ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_s_metarial ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $s_metarial ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_s_metarial ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_b_metarial ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_b_metarial ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $b_metarial ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_b_metarial ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_all_metarial ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_all_metarial ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $all_metarial ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_all_metarial ?></td>
                    <td nowrap align='left'  class='pt10'>買掛購入高比</td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='white'>　労　　務　　費</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_c_roumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_c_roumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $c_roumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_c_roumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_l_roumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_l_roumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $l_roumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_l_roumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_s_roumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_s_roumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $s_roumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_s_roumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_b_roumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_b_roumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $b_roumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_b_roumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_all_roumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_all_roumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $all_roumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_all_roumu ?></td>
                    <td nowrap align='left'  class='pt10'>ＣＬサービス割合比</td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='#e6e6e6'>　経　　　　　費</td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_c_expense ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_c_expense ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $c_expense ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_c_expense ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_l_expense ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_l_expense ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $l_expense ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_l_expense ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_s_expense ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_s_expense ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $s_expense ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_s_expense ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_b_expense ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_b_expense ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $b_expense ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_b_expense ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_all_expense ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_all_expense ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $all_expense ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_all_expense ?></td>
                    <td nowrap align='left'  class='pt10'>ＣＬ直接経費合計比率</td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='white'>　期末材料仕掛品棚卸高</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_c_endinv ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_c_endinv ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $c_endinv ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $c_endinv ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_l_endinv ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_l_endinv ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $l_endinv ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $l_endinv ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_s_endinv ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_s_endinv ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $s_endinv ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $s_endinv ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_b_endinv ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_b_endinv ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $b_endinv ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $b_endinv ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_all_endinv ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_all_endinv ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $all_endinv ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $all_endinv ?></td>
                    <td nowrap align='left'  class='pt10'>総平均単価による棚卸高</td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='#ffffc6'>　売　上　原　価</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p2_c_urigen ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p1_c_urigen ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $c_urigen ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $rui_c_urigen ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p2_l_urigen ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p1_l_urigen ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $l_urigen ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $rui_l_urigen ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p2_s_urigen ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p1_s_urigen ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $s_urigen ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $rui_s_urigen ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p2_b_urigen ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p1_b_urigen ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $b_urigen ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $rui_b_urigen ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p2_all_urigen ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p1_all_urigen ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $all_urigen ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $rui_all_urigen ?></td>
                    <td nowrap align='left'  class='pt10'>ＣＬ直接経費合計比率</td>
                </tr>
                <tr>
                    <td colspan='2' nowrap align='center' class='pt10b' bgcolor='#ceffce'>売　上　総　利　益</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p2_c_gross_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_c_gross_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $c_gross_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $rui_c_gross_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p2_l_gross_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_l_gross_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $l_gross_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $rui_l_gross_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p2_s_gross_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_s_gross_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $s_gross_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $rui_s_gross_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p2_b_gross_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_b_gross_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $b_gross_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $rui_b_gross_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p2_all_gross_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_all_gross_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $all_gross_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $rui_all_gross_profit ?></td>
                    <td nowrap align='left'  class='pt10' bgcolor='#d6d3ce'>　</td>  <!-- 余白 -->
                </tr>
                <tr>
                    <td rowspan='3' width='10' align='center' valign='middle' class='pt10b' bgcolor='#ffffc6'></td> <!-- 販管費 -->
                    <td nowrap align='left' class='pt10b' bgcolor='#e6e6e6'>　人　　件　　費</td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_c_han_jin ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_c_han_jin ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $c_han_jin ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_c_han_jin ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_l_han_jin ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_l_han_jin ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $l_han_jin ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_l_han_jin ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_s_han_jin ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_s_han_jin ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $s_han_jin ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_s_han_jin ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_b_han_jin ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_b_han_jin ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $b_han_jin ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_b_han_jin ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_all_han_jin ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_all_han_jin ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $all_han_jin ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_all_han_jin ?></td>
                    <td nowrap align='left'  class='pt10'>ＣＬ直接給料比率</td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='white'>　経　　　　　費</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_c_han_kei ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_c_han_kei ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $c_han_kei ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_c_han_kei ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_l_han_kei ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_l_han_kei ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $l_han_kei ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_l_han_kei ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_s_han_kei ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_s_han_kei ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $s_han_kei ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_s_han_kei ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_b_han_kei ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_b_han_kei ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $b_han_kei ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_b_han_kei ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_all_han_kei ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_all_han_kei ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $all_han_kei ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_all_han_kei ?></td>
                    <td nowrap align='left'  class='pt10'>ＣＬ占有面積比・ＣＬ直接経費合計比率他</td>
                </tr>
                <tr>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>販管費及び一般管理費計</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p2_c_han_all ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p1_c_han_all ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $c_han_all ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $rui_c_han_all ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p2_l_han_all ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p1_l_han_all ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $l_han_all ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $rui_l_han_all ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p2_s_han_all ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p1_s_han_all ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $s_han_all ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $rui_s_han_all ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p2_b_han_all ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p1_b_han_all ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $b_han_all ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $rui_b_han_all ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p2_all_han_all ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p1_all_han_all ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $all_han_all ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $rui_all_han_all ?></td>
                    <td nowrap align='left'  class='pt10'>　</td>  <!-- 余白 -->
                </tr>
                <tr>
                    <td colspan='3' nowrap align='center' class='pt10b' bgcolor='#ceffce'>営　　業　　利　　益</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p2_c_ope_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_c_ope_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $c_ope_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $rui_c_ope_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p2_l_ope_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_l_ope_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $l_ope_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $rui_l_ope_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p2_s_ope_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_s_ope_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $s_ope_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $rui_s_ope_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p2_b_ope_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_b_ope_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $b_ope_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $rui_b_ope_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p2_all_ope_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_all_ope_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $all_ope_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $rui_all_ope_profit ?></td>
                    <td nowrap align='left'  class='pt10'>　</td>  <!-- 余白 -->
                </tr>
                <tr>
                    <td rowspan='7' align='center' valign='middle' class='pt10b' bgcolor='#ceffce'>営業外損益</td>
                    <td rowspan='4' align='center' class='pt10' bgcolor='#ffffc6'></td> <!-- 余白 -->
                    <td nowrap align='left' class='pt10b' bgcolor='white'>　業務委託収入</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_c_gyoumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_c_gyoumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $c_gyoumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_c_gyoumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_l_gyoumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_l_gyoumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $l_gyoumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_l_gyoumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_s_gyoumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_s_gyoumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $s_gyoumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_s_gyoumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_b_gyoumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_b_gyoumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $b_gyoumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_b_gyoumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_all_gyoumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_all_gyoumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $all_gyoumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_all_gyoumu ?></td>
                    <?php if ($yyyymm >= 201001) { ?>
                    <td nowrap align='left'  class='pt10'>当月の人員比</td>
                    <?php } else { ?>
                    <td nowrap align='left'  class='pt10'>前期実績の売上高比</td>
                    <?php } ?>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='#e6e6e6'>　仕　入　割　引</td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_c_swari ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_c_swari ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $c_swari ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_c_swari ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_l_swari ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_l_swari ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $l_swari ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_l_swari ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_s_swari ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_s_swari ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $s_swari ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_s_swari ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_b_swari ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_b_swari ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $b_swari ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_b_swari ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_all_swari ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_all_swari ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $all_swari ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_all_swari ?></td>
                    <?php if ($yyyymm >= 201001) { ?>
                    <td nowrap align='left'  class='pt10'>当月の人員比</td>
                    <?php } else { ?>
                    <td nowrap align='left'  class='pt10'>前期実績の売上高比</td>
                    <?php } ?>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='white'>　そ　　の　　他</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_c_pother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_c_pother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $c_pother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_c_pother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_l_pother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_l_pother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $l_pother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_l_pother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_s_pother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_s_pother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $s_pother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_s_pother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_b_pother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_b_pother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $b_pother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_b_pother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_all_pother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_all_pother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $all_pother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_all_pother ?></td>
                    <?php if ($yyyymm >= 201001) { ?>
                    <td nowrap align='left'  class='pt10'>当月の人員比</td>
                    <?php } else { ?>
                    <td nowrap align='left'  class='pt10'>前期実績の売上高比</td>
                    <?php } ?>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='#ffffc6'>　営業外収益 計</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p2_c_nonope_profit_sum ?>   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p1_c_nonope_profit_sum ?>   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $c_nonope_profit_sum ?>      </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $rui_c_nonope_profit_sum ?>  </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p2_l_nonope_profit_sum ?>   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p1_l_nonope_profit_sum ?>   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $l_nonope_profit_sum ?>      </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $rui_l_nonope_profit_sum ?>  </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p2_s_nonope_profit_sum ?>   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p1_s_nonope_profit_sum ?>   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $s_nonope_profit_sum ?>      </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $rui_s_nonope_profit_sum ?>  </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p2_b_nonope_profit_sum ?>   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p1_b_nonope_profit_sum ?>   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $b_nonope_profit_sum ?>      </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $rui_b_nonope_profit_sum ?>  </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p2_all_nonope_profit_sum ?> </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p1_all_nonope_profit_sum ?> </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $all_nonope_profit_sum ?>    </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $rui_all_nonope_profit_sum ?></td>
                    <td nowrap align='left'  class='pt10'>　</td> <!-- 余白 -->
                </tr>
                <tr>
                    <td rowspan='3' align='center' class='pt10' bgcolor='#ffffc6'></td> <!-- 余白 -->
                    <td nowrap align='left' class='pt10b' bgcolor='#e6e6e6'>　支　払　利　息</td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_c_srisoku ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_c_srisoku ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $c_srisoku ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_c_srisoku ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_l_srisoku ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_l_srisoku ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $l_srisoku ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_l_srisoku ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_s_srisoku ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_s_srisoku ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $s_srisoku ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_s_srisoku ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_b_srisoku ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_b_srisoku ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $b_srisoku ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_b_srisoku ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_all_srisoku ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_all_srisoku ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $all_srisoku ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_all_srisoku ?></td>
                    <?php if ($yyyymm >= 201001) { ?>
                    <td nowrap align='left'  class='pt10'>当月の人員比</td>
                    <?php } else { ?>
                    <td nowrap align='left'  class='pt10'>前期実績の売上高比</td>
                    <?php } ?>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='white'>　そ　　の　　他</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_c_lother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_c_lother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $c_lother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_c_lother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_l_lother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_l_lother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $l_lother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_l_lother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_s_lother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_s_lother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $s_lother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_s_lother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_b_lother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_b_lother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $b_lother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_b_lother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_all_lother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_all_lother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $all_lother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_all_lother ?></td>
                    <?php if ($yyyymm >= 201001) { ?>
                    <td nowrap align='left'  class='pt10'>当月の人員比</td>
                    <?php } else { ?>
                    <td nowrap align='left'  class='pt10'>前期実績の売上高比</td>
                    <?php } ?>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='#ffffc6'>　営業外費用 計</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p2_c_nonope_loss_sum ?>   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p1_c_nonope_loss_sum ?>   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $c_nonope_loss_sum ?>      </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $rui_c_nonope_loss_sum ?>  </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p2_l_nonope_loss_sum ?>   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p1_l_nonope_loss_sum ?>   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $l_nonope_loss_sum ?>      </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $rui_l_nonope_loss_sum ?>  </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p2_s_nonope_loss_sum ?>   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p1_s_nonope_loss_sum ?>   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $s_nonope_loss_sum ?>      </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $rui_s_nonope_loss_sum ?>  </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p2_b_nonope_loss_sum ?>   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p1_b_nonope_loss_sum ?>   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $b_nonope_loss_sum ?>      </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $rui_b_nonope_loss_sum ?>  </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p2_all_nonope_loss_sum ?> </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p1_all_nonope_loss_sum ?> </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $all_nonope_loss_sum ?>    </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $rui_all_nonope_loss_sum ?></td>
                    <td nowrap align='left'  class='pt10'>　</td> <!-- 余白 -->
                </tr>
                <tr>
                    <td colspan='3' nowrap align='center' class='pt10b' bgcolor='#ceffce'>経　　常　　利　　益</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p2_c_current_profit ?>   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_c_current_profit ?>   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $c_current_profit ?>      </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $rui_c_current_profit ?>  </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p2_l_current_profit ?>   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_l_current_profit ?>   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $l_current_profit ?>      </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $rui_l_current_profit ?>  </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p2_s_current_profit ?>   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_s_current_profit ?>   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $s_current_profit ?>      </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $rui_s_current_profit ?>  </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p2_b_current_profit ?>   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_b_current_profit ?>   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $b_current_profit ?>      </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $rui_b_current_profit ?>  </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p2_all_current_profit ?> </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_all_current_profit ?> </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $all_current_profit ?>    </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $rui_all_current_profit ?></td>
                    <td nowrap align='left'  class='pt10'>　</td>  <!-- 余白 -->
                </tr>
                <tr>
                    <td colspan='19' rowspan='5' bgcolor='white' nowrap align='center' class='pt10b'>　</td>
                    <td colspan='3' bgcolor='white' nowrap align='right' class='pt10b'>特別利益</td>
                    <td nowrap align='right' class='pt10b' bgcolor='white'><?php echo $rui_all_special_profit ?></td>
                    <td rowspan='5' bgcolor='white' nowrap align='center' class='pt10b'>　</td>
                </tr>
                <tr>
                    <td colspan='3' bgcolor='white' nowrap align='right' class='pt10b'>特別損失</td>
                    <td nowrap align='right' class='pt10b' bgcolor='white'><?php echo $rui_all_special_loss ?></td>
                </tr>
                <tr>
                    <td colspan='3' bgcolor='white' nowrap align='right' class='pt10b'>税引前純利益金額</td>
                    <td nowrap align='right' class='pt10b' bgcolor='white'><?php echo $rui_all_before_tax_net_profit ?></td>
                </tr>
                <tr>
                    <td colspan='3' bgcolor='white' nowrap align='right' class='pt10b'>法人税等</td>
                    <td nowrap align='right' class='pt10b' bgcolor='white'><?php echo $rui_all_corporation_tax ?></td>
                </tr>
                <tr>
                    <td colspan='3' bgcolor='white' nowrap align='right' class='pt10b'>当期純利益金額</td>
                    <td nowrap align='right' class='pt10b' bgcolor='white'><?php echo $rui_all_net_profit ?></td>
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
                            if ($comment_c != "") {
                                echo "<li><pre>$comment_c</pre></li>\n";
                            }
                            if ($comment_l != "") {
                                echo "<li><pre>$comment_l</pre></li>\n";
                            }
                            if ($comment_s != "") {
                                echo "<li><pre>$comment_s</pre></li>\n";
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
