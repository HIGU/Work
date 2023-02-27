<?php
//////////////////////////////////////////////////////////////////////////////
// ＴＮＫ(一般的な部分から会社独自の部分を抜出す) ファンクッション ファイル //
// Copyright (C) 2001-2011 Kazuhiro Kobayashi all rights reserved.          //
//                                                              2001/10/01  //
// Changed history                                                          //
// 2001/10/01 Created  tnk_func.php                                         //
// 2001/10/01 function corrc_round($var) round() の補正値対応版作成         //
// 2001/11/15 function Uround($var,少数桁) 負数対応版 作成                  //
// 2002/02/15 設備管理関係を追加                                            //
// 2002/03/07 設備関係は設備専用のファイルにまとめた。equip_function.php    //
// 2002/09/24 day_off() の CASE 文に関数が使えないため day_off2()を作成     //
// 2002/12/24 menu_bar()を追加 メニュー用アイコン(画像)自動生成             //
// 2003/01/16 account_group_check() 財務(損益)関係の許可ユーザーチェック    //
// 2003/01/21 Ym_to_tnk() 西暦の年月から栃木日東工器の対象期を返す          //
// 2003/06/02 menu_bar()でファイルが既にある場合は何もしない(時間短縮)      //
//                      そのため、変更した時は必ずファイルを削除すること。  //
// 2003/06/28 session_start() 4.3.3RC1 でNotice になるためコメント(二重宣言)//
// 2003/07/16 引数のタイムスタンプから漢字の曜日を返す mb_day_name() を追加 //
// 2003/08/05 account_group_check2() 月次損益照会(ノーツから)ユーザー認証   //
// 2003/11/27 format_date()を改良 引数が 0 の場合に ----/--/-- を返す       //
// 2003/11/29 menu_bar()に $create_flg 引数を追加 イメージの強制作成フラグ  //
// 2003/12/19 day_off()とdate_offset()を改良(土日をoffset対象から外した)    //
// 2004/04/05 account_group_check()にユーザー追加(久保治夫・大谷順久)       //
// 2005/04/26 第６期のカレンダーを追加 function day_off($timestamp)         //
// 2005/06/05 last_day()指定年月の最後の日を返す関数を追加                  //
// 2006/01/25 営業日で日付を前後(+-)にOffsetさせるworkingDayOffset()を追加  //
// 2006/02/16 workingDayOffset()のマイナスオフセットのロジック一部訂正      //
// 2006/02/23 第７期のカレンダーを追加                                      //
// 2006/04/03 last_day()の最終日抽出関数に最終日が営業日かチェック機能追加  //
// 2006/06/08 2006/07/20を海の日で登録されていたのを2006/07/17へ訂正        //
// 2006/06/24 workingDayOffset()php-5.1.4で'+0'も'-0'もswitch文で0解釈に対応//
//            workingDayOffset('-0')下位互換 workingDayOffset(0, '-')新構文 //
// 2006/06/26 １期と２期の休暇を day_off($timestamp) に追加                 //
// 2006/07/21 menu_bar()にファイル作成時のパーミッション変更追加            //
// 2006/09/28 組立ライン用の day_off_line() を 追加                         //
// 2006/09/29 環境が異なるmasterstとmasterst2のBAR_MBTTF_Fをロジックで統一  //
// 2006/10/05 account_group_check(), account_group_check2() のメンテナンス  //
// 2006/12/28 day_off()を新規に書換 company_calendarテーブルを使用          //
// 2007/01/09 account_group_check(),account_group_check2()共通権限functionへ//
// 2007/02/06 day_off()データが無い場合は強制的に休日にするを→営業日へ変更 //
// 2007/11/22 リトライ付ftpGetCheckAndExecute(),ftpPutCheckAndExecute()追加 //
// 2010/01/19 account_group_checkに千葉部長代理を追加                       //
//            getCheckAuthority(4)で引っ掛けていたのでそっちに追加     大谷 //
// 2011/06/15 各種6桁の日付のフォーマット関数を追加                    大谷 //
//////////////////////////////////////////////////////////////////////////////


/********** 引数で指定されたタイムスタンプから漢字の曜日を返す **********/
function mb_day_name($time_stamp)
{
    $day = date("w", $time_stamp);
    switch ($day) {
    case 0:
        return "日曜日";
        break;
    case 1:
        return "月曜日";
        break;
    case 2:
        return "火曜日";
        break;
    case 3:
        return "水曜日";
        break;
    case 4:
        return "木曜日";
        break;
    case 5:
        return "金曜日";
        break;
    case 6:
        return "土曜日";
        break;
    default:
        return "エラー";
    }
}

/********* 西暦の年月から栃木日東工器の対象期を返す ***********/
// default は 当月の期を返す(引数を指定しない場合)
function Ym_to_tnk( $ym )
{
    if ($ym == NULL) {
        $ym = $date("Ym");   // 引数の default 値は function()内で定義するが値を関数から取得することは出来ないため
    }
    $tmp = $ym - 200003;     // 第１期の前月
    $tmp = $tmp / 100;       // 年の部分を取り出す
    $ki  = ceil($tmp);       // roundup と同じ
    return $ki;              // 期を返す
}


/********* 損益関係のサイト 許可ユーザー ***********/
/********* 独立した月次損益照会メニューで使用する。ユーザー認証はIP ADDRESのみ *******/
function account_group_check2()
{
    require_once ('/home/www/html/tnk-web/function.php');
    if (getCheckAuthority(5)) {
        return true;
    } else {
        return false;
    }
    
    $addr = $_SERVER["REMOTE_ADDR"];
    switch ($addr) {
        case '10.1.3.136'; return TRUE; break;  // 小林一弘
        case '10.1.3.121'; return TRUE; break;  // 斉藤昭子
        case '10.1.3.105'; return TRUE; break;  // 上野具寛
        case '10.1.3.163'; return TRUE; break;  // 手塚靖博
        case '10.1.3.126'; return TRUE; break;  // 渡部三三夫
        case '10.1.1.246'; return TRUE; break;  // 駒木根裕
        case '10.1.3.123'; return TRUE; break;  // 駒木根裕 → 鷲尾俊一
        case '10.1.3.57' ; return TRUE; break;  // 小田原和雄 → 鷲尾俊一
        case '10.1.3.164'; return TRUE; break;  // 大谷順久         2004/04/05追加
        case '10.1.3.152'; return TRUE; break;  // 増山忠男
        // case '10.1.3.107'; return TRUE; break;  // 小田原和雄
        // case '10.1.3.113'; return TRUE; break;  // 菅谷三男
        // case '10.1.3.187'; return TRUE; break;  // 久保治夫         2004/04/05追加
        default;       return FALSE;
    }
    return FALSE;
}


/********* 損益関係のサイト 許可ユーザー ***********/
function account_group_check()
{
    require_once ('/home/www/html/tnk-web/function.php');
    if (getCheckAuthority(4)) {
        return true;
    } else {
        return false;
    }
    
    // session_start();             // 4.3.3RC1 でNotice になるためコメント(二重宣言)
    if ( isset($_SESSION["User_ID"]) || isset($_SESSION["Password"]) || isset($_SESSION["Auth"]) ) {
        $chk_usr = $_SESSION['User_ID'];
        switch ($chk_usr) {
            case '010561'; return TRUE; break;  // 小林一弘
            case '300055'; return TRUE; break;  // 斉藤昭子
            case '017850'; return TRUE; break;  // 上野具寛
            case '300071'; return TRUE; break;  // 手塚靖博
            // case '008699'; return TRUE; break;  // 安倍秀善
            // case '001406'; return TRUE; break;  // 菅谷三男
            case '010189'; return TRUE; break;  // 渡部三三夫
            case '004154'; return TRUE; break;  // 小田原和雄
            case '007340'; return TRUE; break;  // 千葉均
            // case '001899'; return TRUE; break;  // 駒木根裕
            // case '005487'; return TRUE; break;  // 久保治夫         2004/04/05追加
            case '300101'; return TRUE; break;  // 大谷順久         2004/04/05追加
            case '009504'; return TRUE; break;  // 増山忠男
            case '019321'; return TRUE; break;  // 鷲尾俊一
            default;       return FALSE;
        }
    }
    return FALSE;
}


/********* メニューアイコン 自動生成 戻り値＝ファイル名 *********/
// ライブラリとして GD & freetype それと Free の True Type Font が必要。
// 指定できるフォントサイズは 1〜8 14〜(gothic) 14,17〜(mincho)
// php-4.3.0 からのバンドル用GDを使用する事によりサイズは自由に設定できるようになった｡初期値は 14
// Default のファイル名は temp.png
// Default の Title(Menu Name) は Blank
// Default の Background Color は Sky Blue
// Default の String Color は Black
function menu_bar($file="temp.png", $title="", $font_size=14, $create_flg=0,
                  $r_bg=198, $g_bg=219, $b_bg=247, $r_str=0, $g_str=0, $b_str=0)
{
    if (file_exists($file)) {   ////// 既にファイルがある場合は何もしない 処理時間の短縮のため
        if ($create_flg == 0) {     // 既にファイルがあり作成フラグが 0 なら何もしない
            return $file;
        }
    }
    $file_masterst  = '/usr/share/fonts/ja/TrueType/kochi-mincho.ttf';
    $file_masterst2 = '/usr/share/fonts/japanese/TrueType/kochi-mincho.ttf';
    if (file_exists($file_masterst)) {
        $BAR_MBTTF_F = $file_masterst;
    } else {
        $BAR_MBTTF_F = $file_masterst2;
    }
    $im = imagecreate (200, 32);
    $bg_color = ImageColorAllocate ($im, $r_bg, $g_bg, $b_bg);    // バックグランドを指定
    $white = ImageColorAllocate ($im, 255, 255, 255);
    $gray  = ImageColorAllocate ($im, 132, 130, 132);
    $black = ImageColorAllocate ($im, 66, 65, 66);
    $win_gray = ImageColorAllocate ($im, 214, 211, 206);     // Windows Gray color
    ImageLine($im, 0, 0, 199, 0, $black);               // X 軸に 四角形の上辺
    ImageLine($im, 1, 1, 199, 1, $white);
    ImageLine($im, 0, 0, 0, 31, $black);                // Y 軸に 四角形の左辺
    ImageLine($im, 1, 1, 1, 30, $white);
    ImageLine     ($im,   1, 29, 197, 29, $gray);       // X 軸に
    ImageRectangle($im,   1, 30, 199, 31, $black);      //  2ピクセル単位で短形の作成
    ImageLine     ($im, 197, 1,  197, 28, $gray);       // Y 軸に
    ImageRectangle($im, 198, 0,  199, 31, $black);      //  2ピクセル単位で短形の作成
    if ($title != "") {
        $str_color = ImageColorAllocate ($im, $r_str, $g_str, $b_str);  // 文字色を指定
        $menu_title = mb_convert_encoding($title, "UTF-8");   //// 文字コード変換
        ImageTTFText ($im, $font_size, 0, 6, 22, $str_color, $BAR_MBTTF_F, $menu_title);  // X軸=6 Y軸=22 (文字の左下角)
    } else {
        ImageFill($im, 10, 20, $win_gray);
    }
    ImagePng ($im, $file);
    ImageDestroy ($im);
    chmod($file, 0666); // 2006/07/21 ADD
    return $file;
}


// ８桁の任意の日付を'/'フォーマットして返す。
// 似たような物に number_format() がある。
// ３桁毎のカンマは number_format() です。 default number_format($num)
// 他の引数は number_format($num,'小数部の桁数','format文字','3桁のformat文字')の様にする。
// number_format() の戻り値に注意 printf や sprintf 等では %d でなく %s を使用する｡
function format_date($date8)
{
    if (0 == $date8) {
        $date8 = '--------';    // ----/--/-- で値を返すため 2003/11/27 追加
    }
    if (8 == strlen($date8)) {
        $nen   = substr($date8,0,4);
        $tsuki = substr($date8,4,2);
        $hi    = substr($date8,6,2);
        return $nen . "/" . $tsuki . "/" . $hi;
    } else {
        return FALSE;
    }
}

// ６桁の任意の日付を'/'フォーマットして返す。
function format_date6($date6)
{
    if (0 == $date6) {
        $date6 = '--------';    
    }
    if (6 == strlen($date6)) {
        $nen   = substr($date6, 0, 4);
        $tsuki = substr($date6, 4, 2);
        return $nen . "/" . $tsuki;
    } else {
        return FALSE;
    }
}

// ６桁の任意の日付を'年月'フォーマットして返す。
function format_date6_kan($date6)
{
    if (0 == $date6) {
        $date6 = '--------';    
    }
    if (6 == strlen($date6)) {
        $nen   = substr($date6, 0, 4);
        $tsuki = substr($date6, 4, 2);
        return $nen . "年" . $tsuki . "月";
    } else {
        return FALSE;
    }
}
// ６桁の任意の日付を'期月'フォーマットして返す。
function format_date6_ki($date6)
{
    if (0 == $date6) {
        $date6 = '--------';    
    }
    if ($date6 < 200000) {
        $date6 = '--------';    
    }
    if (6 == strlen($date6)) {
        $nen   = substr($date6, 0, 4);
        $tsuki = substr($date6, 4, 2);
        $tsuki = $tsuki + 1 - 1;
        $ki    = substr($nen, 2, 2);
        if (0 < $tsuki && $tsuki < 4) {
            return "第" . $ki . "期" . $tsuki . "月";
        } else {
            $ki = $ki + 1;
            return "第" . $ki . "期" . $tsuki . "月";
        }
    } else {
        return FALSE;
    }
}
// ６桁の任意の日付を'期上期or下期'フォーマットして返す。
function format_date6_term($date6)
{
    if (0 == $date6) {
        $date6 = '--------';    
    }
    if (6 == strlen($date6)) {
        $nen   = substr($date6, 0, 4);
        $tsuki = substr($date6, 4, 2);
        if (3 < $tsuki && $tsuki < 10) {
            $term = '上期';
        } else {
            $term = '下期';
        }
    }
    if (6 == strlen($date6)) {
        $ki    = substr($nen, 2, 2);
        if (0 < $tsuki && $tsuki < 4) {
            return "第" . $ki . "期" . $term;
        } else {
            $ki = $ki + 1;
            return "第" . $ki . "期" . $term;
        }
    } else {
        return FALSE;
    }
}
// TNKの営業日(活動日)で日付を前後(+-)にOffsetさせる。
// $offset string型である事
// 例：+0=当日が営業日でなければ+側(未来)にoffsetさせる。-0又は0=当日が営業日でなければ-側(過去)にoffsetさせる。
//     -3=３営業日、過去にoffsetさせる。+2 OR 2=２営業日、未来にoffsetさせる。
// php-5.1.4で、'+0'も'-0'もswitch文では共に0と解釈されるため $optionFlgを追加して対応2006/06/24
function workingDayOffset($offset, $optionFlg='-')
{
    $year  = date('Y');
    $mon   = date('m');
    $day   = date('d');
    $timestamp = mktime(0, 0, 0, $mon, $day, $year);
    if (substr($offset, 0, 1) == '+') $optionFlg = '+';     // 過去の互換性のため追加
    if (substr($offset, 0, 1) == '-') $optionFlg = '-';     // 過去の互換性のため追加
    if ($offset != 0) $optionFlg = ' ';                     // 0以外は前方・後方オプションを無効にする
    switch ($optionFlg) {
    case '+':
        if (day_off($timestamp)) {
            $timestamp += 86400;            // １日後にする
            while (day_off($timestamp)) {   // 営業日になるまで繰返し
                $timestamp += 86400;        // １日後にする
            }
            return date('Ymd',$timestamp);  // 直近の未来の営業日を返す
        } else {
            return date('Ymd');             // 当日が営業日
        }
        break;
    case '-':
        if (day_off($timestamp)) {
            $timestamp -= 86400;            // １日前にする
            while (day_off($timestamp)) {   // 営業日になるまで繰返し
                $timestamp -= 86400;        // １日前にする
            }
            return date('Ymd',$timestamp);  // 直近の過去の営業日を返す
        } else {
            return date('Ymd');             // 当日が営業日
        }
        break;
    default:
        if ($offset <= 0) {              // 過去へのoffset
            while ($offset < 0) {
                $timestamp -= 86400;    // １日前にする
                if (day_off($timestamp)) {
                    continue;           // 休みなら繰り返す
                } else {
                    $offset++;          // 営業日ならoffset1カウント終了
                }
            }
        } else {
            while ($offset > 0) {       // 未来へのoffset
                $timestamp += 86400;    // １日後にする
                if (day_off($timestamp)) {
                    continue;           // 休みなら繰り返す
                } else {
                    $offset--;          // 営業日ならoffset1カウント終了
                }
            }
        }
        return date('Ymd',$timestamp);
    }
}

// 引数で指定された日分日付を後ろにずらす。ただし土日を除く。
// 栃木日東の休みを除く。switch case 文のメンテナンスが必要。
function date_offset($offset)
{
    // $today = date('Ymd');
    $year  = date('Y');
    $mon   = date('m');
    $day   = date('d');
    $timestamp = mktime(0, 0, 0, $mon, $day, $year);
    while ($offset > 0) {
        $timestamp -= 86400;    // １日前にする
        if (day_off($timestamp)) {
            continue;       // 休みなら繰り返す
        } else {
            $offset--;      // 営業日ならカウントダウン
        }
    }
    return date('Ymd',$timestamp);
}

/*** day_off()の旧版 ***/
function day_off_old($timestamp)
{
    if (date('w',$timestamp) == 0) return TRUE; // 日曜日
    if (date('w',$timestamp) == 6) return TRUE; // 土曜日
    switch ( date('Ymd',$timestamp) ) {
        /*** 第１期 ***/
        case '20000814'; return TRUE; break;    // 夏期休暇
        case '20000815'; return TRUE; break;    // 夏期休暇
        case '20000816'; return TRUE; break;    // 夏期休暇
        case '20000817'; return TRUE; break;    // 夏期休暇
        case '20000818'; return TRUE; break;    // 夏期休暇
        case '20010102'; return TRUE; break;    // 年始休暇
        case '20010103'; return TRUE; break;    // 年始休暇
        case '20010104'; return TRUE; break;    // 年始休暇
        case '20010105'; return TRUE; break;    // 年始休暇
        /*** 第２期 ***/
        case '20010813'; return TRUE; break;    // 夏期休暇
        case '20010814'; return TRUE; break;    // 夏期休暇
        case '20010815'; return TRUE; break;    // 夏期休暇
        case '20010816'; return TRUE; break;    // 夏期休暇
        case '20010817'; return TRUE; break;    // 夏期休暇
        case '20011231'; return TRUE; break;    // 年末休暇
        case '20020102'; return TRUE; break;    // 年始休暇
        case '20020103'; return TRUE; break;    // 年始休暇
        case '20020104'; return TRUE; break;    // 年始休暇
        case '20020321'; return TRUE; break;    // 春分の日
        /*** 第３期 ***/
        case '20020429'; return TRUE; break;    // みどりの日
        case '20020503'; return TRUE; break;    // 憲法記念日
        case '20020504'; return TRUE; break;    // 国民の休日
        case '20020506'; return TRUE; break;    // 振替休日
        case '20020720'; return TRUE; break;    // 海の日
        case '20020812'; return TRUE; break;    // 夏期休暇
        case '20020813'; return TRUE; break;    // 夏期休暇
        case '20020814'; return TRUE; break;    // 夏期休暇
        case '20020815'; return TRUE; break;    // 夏期休暇
        case '20020816'; return TRUE; break;    // 夏期休暇
        case '20020915'; return TRUE; break;    // 敬老の日
        case '20020916'; return TRUE; break;    // 振替休日
        case '20020923'; return TRUE; break;    // 秋分の日
        case '20021014'; return TRUE; break;    // 体育の日
        case '20021103'; return TRUE; break;    // 文化の日
        case '20021104'; return TRUE; break;    // 振替休日
        case '20021123'; return TRUE; break;    // 勤労感謝の日
        case '20021223'; return TRUE; break;    // 天皇誕生日
        case '20021230'; return TRUE; break;    // 年末休暇
        case '20021231'; return TRUE; break;    // 年末休暇
        case '20030101'; return TRUE; break;    // 年始休暇
        case '20030102'; return TRUE; break;    // 年始休暇
        case '20030103'; return TRUE; break;    // 年始休暇
        case '20030113'; return TRUE; break;    // 成人の日
        case '20030211'; return TRUE; break;    // 建国記念日
        case '20030321'; return TRUE; break;    // 春分の日
        /*** 第４期 ***/
        case '20030429'; return TRUE; break;    // みどり日
        case '20030503'; return TRUE; break;    // 憲法記念日
        case '20030505'; return TRUE; break;    // 子供の日
        case '20030721'; return TRUE; break;    // 海の日
        case '20030811'; return TRUE; break;    // 夏期休暇
        case '20030812'; return TRUE; break;    // 夏期休暇
        case '20030813'; return TRUE; break;    // 夏期休暇
        case '20030814'; return TRUE; break;    // 夏期休暇
        case '20030815'; return TRUE; break;    // 夏期休暇
        case '20030915'; return TRUE; break;    // 敬老の日
        case '20030923'; return TRUE; break;    // 秋分の日
        case '20031013'; return TRUE; break;    // 体育の日
        case '20031103'; return TRUE; break;    // 文化の日
        case '20031123'; return TRUE; break;    // 勤労感謝の日
        case '20031124'; return TRUE; break;    // 振替休日
        case '20031223'; return TRUE; break;    // 天皇誕生日
        case '20031226'; return TRUE; break;    // 年末休暇(掃除の日)
        case '20031229'; return TRUE; break;    // 年末休暇
        case '20031230'; return TRUE; break;    // 年末休暇
        case '20031231'; return TRUE; break;    // 年末休暇
        case '20040101'; return TRUE; break;    // 年始休暇
        case '20040102'; return TRUE; break;    // 年始休暇
        case '20040112'; return TRUE; break;    // 成人の日
        case '20040211'; return TRUE; break;    // 建国記念日
        case '20040320'; return TRUE; break;    // 春分の日
        /*** 第５期 ***/
        case '20040429'; return TRUE; break;    // みどり日
        case '20040503'; return TRUE; break;    // 憲法記念日
        case '20040504'; return TRUE; break;    // 国民の休日
        case '20040505'; return TRUE; break;    // 子供の日
        case '20040719'; return TRUE; break;    // 海の日
        case '20040809'; return TRUE; break;    // 夏期休暇
        case '20040810'; return TRUE; break;    // 夏期休暇
        case '20040811'; return TRUE; break;    // 夏期休暇
        case '20040812'; return TRUE; break;    // 夏期休暇
        case '20040813'; return TRUE; break;    // 夏期休暇
        case '20040816'; return TRUE; break;    // 夏期休暇
        case '20040920'; return TRUE; break;    // 敬老の日
        case '20040923'; return TRUE; break;    // 秋分の日
        case '20041011'; return TRUE; break;    // 体育の日
        case '20041103'; return TRUE; break;    // 文化の日
        case '20041123'; return TRUE; break;    // 勤労感謝の日
        case '20041223'; return TRUE; break;    // 天皇誕生日
        case '20041229'; return TRUE; break;    // 年末休暇(掃除の日)
        case '20041230'; return TRUE; break;    // 年末休暇
        case '20041231'; return TRUE; break;    // 年末休暇
        case '20050103'; return TRUE; break;    // 年始休暇
        case '20050104'; return TRUE; break;    // 年始休暇
        case '20050105'; return TRUE; break;    // 年始休暇
        case '20050110'; return TRUE; break;    // 成人の日
        case '20050211'; return TRUE; break;    // 建国記念日
        case '20050321'; return TRUE; break;    // 春分の日
        /*** 第６期 ***/
        case '20050429'; return TRUE; break;    // みどり日
        case '20050502'; return TRUE; break;    // NKグーループ休日
        case '20050503'; return TRUE; break;    // 憲法記念日
        case '20050504'; return TRUE; break;    // 国民の休日
        case '20050505'; return TRUE; break;    // 子供の日
        case '20050718'; return TRUE; break;    // 海の日
        case '20050810'; return TRUE; break;    // 夏期休暇
        case '20050811'; return TRUE; break;    // 夏期休暇
        case '20050812'; return TRUE; break;    // 夏期休暇
        case '20050815'; return TRUE; break;    // 夏期休暇
        case '20050816'; return TRUE; break;    // 夏期休暇
        case '20050919'; return TRUE; break;    // 敬老の日
        case '20050923'; return TRUE; break;    // 秋分の日
        case '20051010'; return TRUE; break;    // 体育の日
        case '20051103'; return TRUE; break;    // 文化の日
        case '20051123'; return TRUE; break;    // 勤労感謝の日
        case '20051223'; return TRUE; break;    // 天皇誕生日
        case '20051229'; return TRUE; break;    // 年末休暇(掃除の日)
        case '20051230'; return TRUE; break;    // 年末休暇
        case '20060102'; return TRUE; break;    // 年始休暇
        case '20060103'; return TRUE; break;    // 年始休暇
        case '20060104'; return TRUE; break;    // 年始休暇
        case '20060109'; return TRUE; break;    // 成人の日
        case '20060211'; return TRUE; break;    // 建国記念日
        case '20060321'; return TRUE; break;    // 春分の日
        /*** 第７期 ***/
        case '20060429'; return TRUE; break;    // みどり日
        case '20060503'; return TRUE; break;    // 憲法記念日
        case '20060504'; return TRUE; break;    // 国民の休日
        case '20060505'; return TRUE; break;    // 子供の日
        case '20060717'; return TRUE; break;    // 海の日
        case '20060814'; return TRUE; break;    // 夏期休暇
        case '20060815'; return TRUE; break;    // 夏期休暇
        case '20060816'; return TRUE; break;    // 夏期休暇
        case '20060817'; return TRUE; break;    // 夏期休暇
        case '20060818'; return TRUE; break;    // 夏期休暇
        case '20060918'; return TRUE; break;    // 敬老の日
        case '20060923'; return TRUE; break;    // 秋分の日
        case '20061009'; return TRUE; break;    // 体育の日
        case '20061103'; return TRUE; break;    // 文化の日
        case '20061123'; return TRUE; break;    // 勤労感謝の日
        case '20061223'; return TRUE; break;    // 天皇誕生日
        case '20061229'; return TRUE; break;    // 年末休暇
        case '20070102'; return TRUE; break;    // 年始休暇
        case '20070103'; return TRUE; break;    // 年始休暇
        case '20070108'; return TRUE; break;    // 成人の日
        case '20070211'; return TRUE; break;    // 建国記念日
        case '20070212'; return TRUE; break;    // 振替休日
        case '20070321'; return TRUE; break;    // 春分の日
        default; return FALSE;
    }
}

/*** day_off()の新版 ***/
/*** company_calendar テーブルを使用 ***/
function day_off($timestamp)
{
    require_once ('/home/www/html/tnk-web/function.php');
    $date = date('Y-m-d',$timestamp);
    $query = "
        SELECT bd_flg FROM company_calendar WHERE tdate='{$date}'
    ";
    if (getUniResult($query, $check) <= 0) {    // 会社カレンダーでチェック
        return false;           // データが無い場合は強制的に営業日にする
    } else {
        if ($check == 't') return false; else return true;  // 真偽値が逆なのに注意
    }
}

/*** day_off()の組立ライン版 ***/
/*** assembly_calendar テーブルを使用 ***/
function day_off_line($timestamp, $targetLine)
{
    require_once ('/home/www/html/tnk-web/function.php');
    $date = date('Y-m-d',$timestamp);
    $query = "
        SELECT bd_flg FROM assembly_calendar WHERE line='{$targetLine}' AND tdate='{$date}'
    ";
    if (getUniResult($query, $check) <= 0) {    // 指定ラインでチェック
        $query = "
            SELECT bd_flg FROM assembly_calendar WHERE line='0000' AND tdate='{$date}'
        ";
        if (getUniResult($query, $check) <= 0) {    // 共通ラインでチェック
            $query = "
                SELECT bd_flg FROM company_calendar WHERE tdate='{$date}'
            ";
            if (getUniResult($query, $check) <= 0) {    // 会社カレンダーでチェック
                return true;    // 最終的にデータが無い場合は強制的に休日にする
            } else {
                if ($check == 't') return false; else return true;  // 真偽値が逆なのに注意
            }
        } else {
            if ($check == 't') return false; else return true;
        }
    } else {
        if ($check == 't') return false; else return true;
    }
}

// 四捨五入の関数round()の２進数と１０進数の差異を補正するための
// 変数を登録。
// これは暫定的な対処でこの補正値を使った場合に確率は低いですが
// 問題が出る場合があります。例：1.49999999 等の場合に 2.0 になる。
// また、補正する相手がマイナスの場合も考慮する必要がある。
// 例
// if($var < 0) //四捨五入の時の補正処理
//  $var = var - $corrc_var; <---- マイナスの時は補正値もマイナス。
// else
//  $var = $var + $corrc_var; <--- 通常の時 補正値プラス。
// return round($var); <------------ 補正値を加味させて四捨五入をする。

function corrc_round($var)
{
    $corrc_var = 0.00000001;
    if($var < 0)
        $var = $var - $corrc_var;
    else
        $var = $var + $corrc_var;
    return round($var);
}

/********* 正数に加え負数対応版 *********/
    // 小数点以下の default 値は 0
function Uround($var, $num = 0)
{
    $corrc_var = 0.00000001;
    if($var < 0)
        $var = $var - $corrc_var;
    else
        $var = $var + $corrc_var;
    return round($var,$num);
}

/********* 指定年月の最終日を返す **********/
function last_day($year = 0, $month = 0)
{
    if ($year <= 0 || $year >= 2038) {
        $year = date('Y');      // 指定がない場合の年の初期値
    }
    if ($month <= 0 || $month >= 13) {
        $month = date('m');     // 指定がない場合の月の初期値
    }
    $targetYear = $year;
    $targetMonth = $month;
    if ($month <= 11) {
        $month += 1;
    } else {
        $month = 1;
        $year += 1;
    }
    ///// 次月の１日にセット
    $day = date('d', mktime(0, 0, 0, $month, 1, $year) - 1);    // 1秒前にして前日にする
    ///// 指定年月の最終日のタイムスタンプ
    $timestamp = mktime(0, 0, 0, $targetMonth, $day, $targetYear);
    ///// 最終日が営業日かチェック
    while (day_off($timestamp)) {
        $timestamp -= 86400;    // １日前にする
    }
    return date('d',$timestamp);
}

/********* AS/400とのFTP DOWNLOAD リトライ３回処理 ログファイルを変えれば汎用版 **********/
function ftpGetCheckAndExecute($stream, $local_file, $as400_file, $ftp)
{
    $errorLogFile = '/tmp/as400FTP_Error.log';
    $trySec = 10;
    
    $flg = @ftp_get($stream, $local_file, $as400_file, $ftp);
    if ($flg) return $flg;
    $log_date = date('Y-m-d H:i:s');
    $errorLog = fopen($errorLogFile, 'a');
    fwrite($errorLog, "$log_date ftp_get Error try1 File=>" . __FILE__ . "\n");
    fclose($errorLog);
    sleep($trySec);
    
    $flg = @ftp_get($stream, $local_file, $as400_file, $ftp);
    if ($flg) return $flg;
    $log_date = date('Y-m-d H:i:s');
    $errorLog = fopen($errorLogFile, 'a');
    fwrite($errorLog, "$log_date ftp_get Error try2 File=>" . __FILE__ . "\n");
    fclose($errorLog);
    sleep($trySec);
    
    $flg = @ftp_get($stream, $local_file, $as400_file, $ftp);
    if ($flg) return $flg;
    $log_date = date('Y-m-d H:i:s');
    $errorLog = fopen($errorLogFile, 'a');
    fwrite($errorLog, "$log_date ftp_get Error try3 File=>" . __FILE__ . "\n");
    fclose($errorLog);
    
    return $flg;
}

/********* AS/400とのFTP UPLOAD リトライ３回処理 ログファイルを変えれば汎用版 **********/
function ftpPutCheckAndExecute($stream, $as400_file, $local_file, $ftp)
{
    $errorLogFile = '/tmp/as400FTP_Error.log';
    $trySec = 10;
    
    $flg = @ftp_put($stream, $as400_file, $local_file, $ftp);
    if ($flg) return $flg;
    $log_date = date('Y-m-d H:i:s');
    $errorLog = fopen($errorLogFile, 'a');
    fwrite($errorLog, "$log_date ftp_put Error try1 File=>" . __FILE__ . "\n");
    fclose($errorLog);
    sleep($trySec);
    
    $flg = @ftp_put($stream, $as400_file, $local_file, $ftp);
    if ($flg) return $flg;
    $log_date = date('Y-m-d H:i:s');
    $errorLog = fopen($errorLogFile, 'a');
    fwrite($errorLog, "$log_date ftp_put Error try2 File=>" . __FILE__ . "\n");
    fclose($errorLog);
    sleep($trySec);
    
    $flg = @ftp_put($stream, $as400_file, $local_file, $ftp);
    if ($flg) return $flg;
    $log_date = date('Y-m-d H:i:s');
    $errorLog = fopen($errorLogFile, 'a');
    fwrite($errorLog, "$log_date ftp_put Error try3 File=>" . __FILE__ . "\n");
    fclose($errorLog);
    
    return $flg;
}
?>
