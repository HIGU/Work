<?php
//////////////////////////////////////////////////////////////////////////////
// 社内規程メニュー   company regulation                                 　 //
// Copyright (C) 2005-2022 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2005/07/25 Created  regulation_menu.php                                  //
// 2006/02/21 社員会の要望により決済稟議規程以下10項目を追加。順番を体系順に//
// 2006/03/08 一般貸付規程・住宅融資規程・教育資金貸付規程の06/02/01版へ差替//
// 2006/06/01 ガソリン単価推移表を追加                                      //
// 2006/07/01 通勤交通費支給細則を06.06.01へ変更                            //
// 2007/03/27 決済稟議規定→決裁稟議規定に変更 大谷                         //
//            安全衛星委員会細則→安全衛生委員会細則に変更 大谷             //
// 2007/04/17 業務分掌規程・決裁稟議規程諸手当並びに健康管理金細則を        //
//            07.04.01へ変更                                                //
// 2008/04/08 賃金規定・業務分掌規定・従業員マイカー管理規定を              //
//            08.04.01へ変更                                           大谷 //
// 2008/09/01 一般貸付規程を08.09.01へ変更                             大谷 //
// 2009/01/13 就業規則を09.01.08へ変更。就業規則に別紙を追加           大谷 //
// 2009/04/07 賃金規程・諸手当並びに健康管理金細則を09.04.01へ変更。     桝 //
// 2009/07/07 業務分掌規程・就業規程を09.07.01へ変更。                   桝 //
// 2009/07/07 業務分掌規程・就業規程を09.10.01へ変更。                   桝 //
// 2010/03/31 資格等級規程を10.04.01へ変更。                             桝 //
// 2010/04/27 業務分掌規程・就業規程・賃金規程を10.04.01へ変更。         桝 //
//            社員所有車の業務使用に関する規程を10.04.01へ変更。         桝 //
// 2010/06/15 内部統制関連の規定を追加                                 大谷 //
// 2010/07/16 育児休業規定・介護休業規定を10.07.01へ変更               大谷 //
// 2010/08/19 経理規定10.08.06を追加                                   大谷 //
// 2010/08/20 決済稟議規定を10.08.06へ変更                             大谷 //
// 2010/09/22 社員所有車の業務使用に関する規程を                            //
//            マイカーの業務使用に関する規定に変更                     大谷 //
// 2010/10/11 就業規則を10.10.11へ変更。パート就業規則10.10.11を追加   大谷 //
// 2010/12/07 パート慶弔規程06.08.29を追加                             大谷 //
// 2011/01/06 定年後再雇用契約社員規程を11.01.01へ変更。                    //
//            情報管理規程及び細則10.12.01を新規追加                   大谷 //
// 2011/03/08 育児休業規定・介護休業規定を11.03.01へ変更               大谷 //
// 2011/04/02 パート就業規則・賃金規程・定年後再雇用契約社員規程を          //
//            11.04.01へ変更                                           大谷 //
// 2011/04/07 業務分掌規程を11.04.01へ変更                             大谷 //
// 2011/08/22 育児休業規定を11.09.01へ介護休業規定を11.03.01へ変更     大谷 //
// 2011/09/14 交代勤務運用細則10.04.01を追加                           大谷 //
// 2012/03/27 ４月１日より以下の規定を表示するように仕掛けを追加            //
//              従業員マイカー管理規程          12/4/1 改訂                 //
//              マイカーの業務使用に関する規程  12/4/1 改訂                 //
//              教育資金貸付規程                12/4/1 改訂                 //
//              海外旅費規程                    12/4/1 改訂                 //
//              ﾊﾟｰﾄ就業規則                    12/4/1 改訂                 //
//              賃金規程                        12/4/1 改訂                 //
//              諸手当並びに健康管理金細則      12/4/1 改訂                 //
//              育児休業規程                    12/4/1 改訂                 //
//              介護休業規程                    12/4/1 改訂                 //
//              駐車場管理細則                  12/4/1 廃止                 //
//              通勤交通費支給細則              12/4/1 廃止            大谷 //
// 2012/04/03 おかしな文字が入っていたのを削除                         大谷 //
// 2012/10/03 スクロールが隠れていたので表示するように変更             大谷 //
// 2012/11/27 就業規則を2012/12/01より12.12.01へ変更                   大谷 //
// 2013/03/21 13.04.01より就業規則（パートも）を13.04.01へ変更         大谷 //
// 2013/10/25 １０月１日付けの以下の規程を変更                              //
//              就業規則                                                    //
//              パート就業規則                                              //
//              慶弔規程                                                    //
//              国内旅費規程                                                //
//              交代勤務運用細則                                            //
//              賃金規程                                                    //
//              育児休業規程                                                //
//              介護休業規程                                                //
//              深夜業免除休業規程                                          //
//              永年勤続者表彰規程                                          //
//            但し、以下の規程は現状公開されていなかったものなのでコメント化//
//              ｾｸｼｭｱﾙ・ﾊﾗｽﾒﾝﾄ防止規程（新規）                              //
//              ﾊﾟﾜｰ・ﾊﾗｽﾒﾝﾄ防止規程（新規）                                //
//              業務上災害法廷外補償規程                                    //
//              母性健康管理の措置に関する規程                              //
//              出向規程                                                    //
//            また、改定日に合わせて規程を変更する不要な仕掛けを削除   大谷 //
// 2013/11/25 規程追加に伴い大きくレイアウトを変更                          //
//            規程管理規程の規程体系図に合わせて、上から順に表示            //
//            以下の規程を追加                                              //
//              定款                                    12/11/07            //
//              取締役会規程                            11/06/22            //
//              職務権限規程                            08/04/01            //
//              規程管理規程                            10/08/06            //
//              規程体系図                              12/04/01            //
//              業務引継規程                            00/09/07            //
//              印章規程                                00/08/03            //
//              印章取扱細則                            00/09/07            //
//              国内転勤・赴任者の取扱いに関する規程    00/08/23            //
//              業務上災害法定外補償規程                13/10/01            //
//              母性健康管理の措置に関する規定          13/10/01            //
//              嘱託規程                                00/09/04            //
//              出向規程                                13/10/01            //
//              海外駐在員規程                          00/09/05            //
//              社宅取扱規程                            10/04/01            //
//              海外出張時における災害補償規程          00/09/04            //
//              ｾｸｼｭｱﾙ・ﾊﾗｽﾒﾝﾄ防止規程                  13/10/01            //
//              ﾊﾟﾜｰ・ﾊﾗｽﾒﾝﾄ防止規程                    13/10/01            //
//              出納規程                                02/04/01            //
//              手形・有価証券管理規程                  00/08/24            //
//              原価計算規程                            10/12/01            //
//              棚卸資産管理規程                        10/10/01            //
//              固定資産管理規程                        10/10/01            //
//              生産管理規程                            00/08/22            //
//              購買管理規程                            00/08/22       大谷 //
// 2014/01/20 １月１日付けで以下の規定を変更                                //
//              決裁・稟議規程(決裁稟議規程より名称変更) 14/1/1 改訂        //
//              パートタイマー就業規則(規程)                                //
//                          (パート就業規則より名称変更) 14/1/1 改訂        //
//              従業員の慶弔見舞金に関する規程                              //
//                                (慶弔規程より名称変更) 14/1/1 改訂        //
//              パートタイマーの慶弔見舞金に関する規定                      //
//                          (パート慶弔規程より名称変更) 14/1/1 改訂        //
//              消防計画規程(消防計画より名称変更) 14/1/1 改訂・追加   大谷 //
// 2014/01/24 １月１日付けで以下の規定を変更・追加。合わせてレイアウト変更  //
//              ○ グループ共有規程(改訂)                                   //
//                  規程管理規程・体系図                 14/1/1 改訂        //
//                  予算管理規程                         14/1/1 改訂        //
//                  情報管理規程                         14/1/1 改訂        //
//                  情報管理規程細則                     14/1/1 改訂        //
//                  ｾｸｼｭｱﾙ・ﾊﾗｽﾒﾝﾄ防止規程               14/1/1 改訂        //
//                  ﾊﾟﾜｰ・ﾊﾗｽﾒﾝﾄ防止規程                 14/1/1 改訂        //
//              ○ グループ共有規程(新設)                                   //
//                  内部監査規程                         14/1/1 新設        //
//                  コンプライアンス規程                 14/1/1 新設        //
//                  内部通報規程                         14/1/1 新設        //
//                  個人情報保護規程                     14/1/1 新設        //
//                  日東工器ｸﾞﾙｰﾌﾟ内部者取引管理規程     14/1/1 新設        //
//                  グリーン調達委員会規則(細則)         14/1/1 新設        //
//                  日東工器従業員持株会規約             08/10/06 新設      //
//                  日東工器従業員持株会運営細則         08/10/06 新設      //
//                  規約型確定給付企業年金規約           10/12/07 新設      //
//              ○ 規程改訂                                                 //
//                  印章規程                             14/1/1 改訂   大谷 //
// 2014/01/31 規定管理規程が規程管理規程になっていたので訂正           大谷 //
// 2014/02/14 規定管理規程はグループ共有ではないので訂正               大谷 //
// 2014/03/11 業務分掌規程を13.08.11付に変更（変更漏れ）               大谷 //
// 2014/04/01 定年後再雇用の契約社員規程を14.04.01付けへ変更           大谷 //
// 2014/04/04 以下の規程を14.04.01付けへ変更                                //
//            育児休業規程・介護休業規程・業務分掌規程・諸手当～       大谷 //
// 2014/04/11 就業規則の誤字訂正                                       大谷 //
//            資格等級制度規程を13.04.01付に変更（変更漏れ）           大谷 //
// 2014/06/25 内部通報規程を14.06.23付けへ変更                         大谷 //
// 2014/09/22 以下の規程を14.09.01付けへ変更                                //
//            国内転勤・赴任者の取扱いに関する規程                          //
//            出向規程、社宅取扱規程                                   大谷 //
// 2014/10/10 海外旅費規程の別表３を非表示に変更                       大谷 //
// 2015/03/31 内部通報規程を15.04.01付に変更（4/1より公開）                 //
//            危機管理規程15.04.01付を追加（4/1より公開）              大谷 //
//            日東工器の体系図に合わせて色々組替え                     大谷 //
// 2015/09/18 以下の規程を15.09.01付けへ変更                                //
//            従業員の慶弔見舞金に関する規程                                //
//            パートタイマーの慶弔見舞金に関する規程                   大谷 //
// 2015/11/13 国内・海外旅費規程を15.11.04へ変更                       大谷 //
// 2015/12/10 文書規程別表１文書保存期間一覧表を新規掲載               大谷 //
// 2016/01/05 就業規則・パート就業規則・規定管理規程(図)を16.01.01付へ変更  //
//            特定個人情報取扱規程を新設                               大谷 //
// 2016/04/13 社宅取扱規程、業務分掌規程を16.04.01付へ変更             大谷 //
// 2016/05/20 パートタイマー就業規則を16.04.01付へ変更                 大谷 //
// 2016/08/05 発明取扱規程・細則を新規追加。規程体系図を更新           大谷 //
// 2016/10/31 定年後再雇用の契約社員規程、パートタイマー就業規則            //
//            就業規則を16.11.01付へ変更(11.01より変更となるよう仕掛)  大谷 //
// 2016/11/21 マイカー業務使用時のガソリン単価を掲載(2016.03.01付～)   大谷 //
// 2016/12/06 特定個人情報取扱規程を16.11.01付へ更新                   大谷 //
// 2017/03/22 決裁・稟議規程に日常業務と国内生産会社決裁基準を追加     大谷 //
// 2017/03/30 決裁・稟議規程の国内生産会社決裁基準を４月１日より表示変更    //
//            するよう仕掛けを追加。他昔の仕掛けを削除                 大谷 //
// 2017/05/18 国内転勤・赴任者の取扱いに関する規程を17.04.11付へ更新   大谷 //
// 2017/06/05 特定個人情報取扱規程を17.05.11付へ更新                   大谷 //
// 2017/07/14 決裁・稟議規程を17.07.01付へ更新                         大谷 //
// 2017/09/29 諸手当並びに健康管理金細則を17.10.01付へ更新             大谷 //
// 2017/11/22 以下の規程を17.10.01付けへ変更                                //
//            育児休業規程・介護休業規程                               大谷 //
// 2017/12/22 発明取扱規程を17.09.01付けへ変更                         大谷 //
// 2018/02/16 賃金規定、パート就業規則を18.02.06付けへ変更             大谷 //
// 2018/03/22 国内旅費規程、定年後再雇用の契約社員、諸手当並びに～          //
//            介護休業規程を18.04.01からファイル変更するよう                //
//            プログラムを修正                                         大谷 //
// 2018/04/02 国内旅費規程の別表を18.04.01付へ変更                     大谷 //
// 2018/04/06 各グループ共有規程を以下の通り変更                       大谷 //
//                  個人情報保護規程                     17/10/23付         //
//                  日東工器グループ内部者取引管理規程   17/03/07付         //
//                  内部通報規程                         18/04/01付         //
//                  規約型確定給付企業年金規約           16/10/01付         //
// 2018/04/19 資格等級制度規程を18.04.01付へ変更                       大谷 //
// 2018/05/24 中期経営計画規程を追加(準備) ※規程体系図も変わる        大谷 //
//            条件分岐させていたのを整理、acrobatの文言を削除               //
// 2018/06/06 規程管理規程を18.05.22付へ変更（体系図も）               大谷 //
//            中期経営計画規程は元が無いので要確認                          //
// 2018/06/11 中期経営計画規程を18.05.22付けで公開                     大谷 //
// 2018/06/22 定款を18.06.18付へ変更                                   大谷 //
// 2018/11/21 就業規則を18.10.16へ変更。パート就業規則を18.10.16へ変更 大谷 //
// 2019/01/17 決裁稟議規程を19.01.01付へ変更(国内・日常表も)           大谷 //
// 2019/04/18 諸手当並びに健康管理金細則を19.04.01へ変更               大谷 //
// 2019/09/30 各規程を19/10/01付けで更新(分岐)                         大谷 //
//            「慶弔見舞金規程」…「従業員の慶弔見舞金に関する規程」と      //
//                                「パートタイマーの慶弔見舞金に関する規程」//
//                                 を統合等                                 //
//            「国内転勤・赴任者の取扱に関する規程」…住宅補修手当の見直し  //
//            「国内旅費規程」…宿泊費の記載が曖昧なため文言の修正          //
//            「諸手当並びに健康管理金細則」…「諸手当細則」に名称変更      //
//            「購買管理規程」…実務に合わせた文言の修正                    //
//            「パートタイマーの慶弔見舞金に関する規程」…統合による廃止    //
//            「規程体系図」…上記統廃合による変更                          //
// 2019/10/24 日東工器従業員持株会規約、日東工器従業員持株会運営細則を      //
//            19.09.13付けへ変更                                       和氣 //
// 2020/04/21 各グループ共有規程を以下の通り変更                       大谷 //
//                  日東工器グループ内部者取引管理規程   20/03/10付         //
//                  発明取扱規程を20.02.07付けへ変更                   大谷 //
//                  内部通報規程を20.04.01付けへ変更                   大谷 //
//                  セクハラ・パワハラを統合して                            //
//                  ハラスメント防止規程を20.04.01付けとして表示       大谷 //
// 2020/05/25 内部統制規程を20.04.01へ変更                             大谷 //
// 2020/09/24 規程追加に伴いレイアウトを変更(10/01自動)                大谷 //
//            以下の規程を追加（2020/10/01付)                               //
//              規定管理規程（規程体系図）                                  //
//              決裁稟議規程                                                //
//              業務引継規程                                                //
//              文書規程-文書作成実務マニュアル                             //
//              印章規程                                                    //
//              国内旅費規程                                                //
//              国内研修旅費規程                                            //
//              海外旅費規程                                                //
//              マイカーの業務使用に関する規程                              //
//              従業員マイカー管理規程                                      //
//              安全衛生管理規程                                            //
//              安全衛生委員会細則                                          //
//              消防計画規程                                                //
//              嘱託規程                                                    //
//              出向規程                                                    //
//              国内転勤・赴任者の取扱いに関する規程                        //
//              諸手当細則                                                  //
//              住宅融資規程                                                //
//              一般貸付規程                                                //
//              教育資金貸付規程                                            //
//              社宅取扱規程                                                //
//              業務上災害法定外補償規程                                    //
//              育児休業規程                                                //
//              海外出張時における災害補償規程                              //
//              介護休業規程                                                //
//              深夜業免除規程                                              //
//              母性健康管理の措置に関する規定                              //
//              経理規程                                                    //
//              出納規程                                                    //
//              手形・有価証券管理規程                                      //
//              原価計算規程                                                //
//              棚卸資産管理規程                                            //
//              固定資産管理規程                                            //
//              生産管理規程                                                //
//              購買管理規程                                                //
//              職務権限規程                                                //
//              業務分掌規程                                                //
//              交代勤務運用細則                                            //
//            以下の規程を削除（2020/10/01付)                               //
//              海外駐在員規程                                              //
// 2021/01/12 内部統制規程を20.12.01へ変更                             大谷 //
// 2021/02/17 育児・介護休業規程規程を21.01.01へ変更                   大谷 //
// 2021/05/20 内部監査規程,内部通報規程,内部統制規程を                      //
//            21.04.01へ変更                                           大谷 //
// 2021/10/28 決裁・稟議規程を21.12.01に変更するようにセット           大谷 //
// 2022/04/04 内部通報規程,情報管理規程,情報管理規程細則を                  //
//            21.04.01へ変更                                           大谷 //
// 2022/05/19 各グループ共有規程を以下の通り変更                       大谷 //
//                  ハラスメント防止規程を20.04.01付けとして表示            //
//                  内部統制規程を22.04.01付けへ変更                        //
//                  発明取扱規程を22.04.01付けへ変更                        //
//                  発明取扱規程細則を22.04.01付けへ変更               大谷 //
// 2022/05/23 賃金規程を22.04.11,育児休業規程,介護休業規程を                //
//            22.04.01へ変更                                           大谷 //
// 2022/06/03 各グループ共有規程を以下の通り変更                       大谷 //
//                  内部統制規程を22.04.01付けへ変更                        //
//                  内部通報規程を22.06.01付けへ変更                        //
// 2022/06/20 各グループ共有規程を以下の通り変更                       大谷 //
//            コンプライアンス規程危機管理規程を22.04.01付けへ変更     大谷 //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮
session_start();                            // ini_set()の次に指定すること Script 最上行

require_once ('../function.php');           // define.php と pgsql.php を require_once している
// require_once ('../tnk_func.php');           // TNK に依存する部分の関数を require_once している
require_once ('../MenuHeader.php');         // TNK 全共通 menu class
access_log();                               // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
if (isset($_SESSION['REGU_Auth'])) {
    $menu = new MenuHeader(-1);             // 認証チェック0=一般以上 戻り先=TOP_MENU タイトル未設定
} else {
    $menu = new MenuHeader(0);              // 認証チェック0=一般以上 戻り先=TOP_MENU タイトル未設定
}

////////////// サイト設定
$menu->set_site(INDEX_REGU, 0);            // site_index=INDEX_REGU(社内規程メニュー) site_id=0(なし)
////////////// リターンアドレス設定(絶対指定する場合)
// $menu->set_RetUrl(SYS_MENU);                // 通常は指定する必要はない
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('社内規程 照会 メニュー');
//////////// 表題の設定
$menu->set_caption('以下の規程類は Acrobat Reader 5 以上で閲覧出来ます。');
$uniq = 'ID=' . uniqid('regu');

if ($_SESSION['User_ID'] == '300144') {
    //$today = 20201001;    // テスト用
    $today = date('Ymd');
} else {
    $today = date('Ymd');
}
///// 日付テスト用（テスト完了時はコメント化）
//$today = 20130401;

?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<title><?= $menu->out_title() ?></title>
<?= $menu->out_site_java() ?>
<?= $menu->out_css() ?>
<script type='text/javascript' language='JavaScript' src='regulation.js?id=<?= $uniq ?>'></script>
<link rel='stylesheet' href='regulation.css?id=<?= $uniq ?>' type='text/css' media='screen'>
</head>
<body onLoad='Regu.set_focus(document.getElementById("start", ""))'>
    <center>
<?= $menu->out_title_border() ?>
    <!--
    <div class='pt12b'><?php echo $menu->out_caption()?></div>
    -->
    <div class='pt12b'>&nbsp;</div>
    <B>
    　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　
    (※)はグループ共有規程
    </B>
    <!--
    2020/09/24 2020/10/01は規程改定が多いため一括で条件分岐
    -->
    <table class='layout'>
    <tr class='layout'>
        <td class='layout' id='start' colspan='2'>
        ●基本規程
        </td>
    </tr>
    <tr class='layout'>
        <td class='layout' id='start'>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("teikan18.06.18.pdf", "")'
                onMouseover="status='定款を表示します。';return true;"
                onMouseout="status=''"
                title='定款を表示します。'
            >定款</a>
        </td>
        <td class='layout'>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("torisimariyaku-kai21.04.01.pdf", "")'
                onMouseover="status='取締役会規程を表示します。';return true;"
                onMouseout="status=''"
                title='取締役会規程を表示します。'
            >取締役会規程</a>
        </td>
    </tr>
    <tr class='layout'>
        <td class='layout' id='start'>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("internal_control_kitei22.04.01.pdf", "")'
                onMouseover="status='内部統制規程を表示します。';return true;"
                onMouseout="status=''"
                title='内部統制規程を表示します。'
            >内部統制規程(※)</a>
        </td>
        <td class='layout'>
            　
        </td>
    </tr>
    <tr class='layout'>
        <td class='layout' id='start' colspan='2'>
        ●管理規程－組織規程
        </td>
    </tr>
    <tr class='layout'>
        <td class='layout' id='start'>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("shokumu-kengen20.10.01.pdf", "")'
                onMouseover="status='職務権限規程を表示します。';return true;"
                onMouseout="status=''"
                title='職務権限規程を表示します。'
            >職務権限規程</a>
        </td>
        <td class='layout' id='start'>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("gyoumu-bunshou21.04.01.pdf", "")'
                onMouseover="status='業務分掌規程を表示します。';return true;"
                onMouseout="status=''"
                title='業務分掌規程を表示します。'
            >業務分掌規程</a>
        </td>
    </tr>
    <tr class='layout'>
        <td class='layout' id='start' colspan='2'>
        ●管理規程－業務規程－全般管理の規程
        </td>
    </tr>
    <tr class='layout'>
        <?php
        //if ($_SESSION['User_ID'] == '300144') {
        if ($today >= 20211201) {
        ?>
        <td class='layout' id='start'>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("kessai-ringi21.12.01.pdf", "")'
                onMouseover="status='決裁・稟議規程を表示します。';return true;"
                onMouseout="status=''"
                title='決裁・稟議規程を表示します。'
            >決裁・稟議規程</a>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("kessai-kijyun-nitijyo19.01.01.pdf", "")'
                onMouseover="status='日常業務決裁基準を表示します。';return true;"
                onMouseout="status=''"
                title='日常業務決裁基準を表示します。'
            >日常業務</a>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("kessai-kijyun-kokunai21.12.01.pdf", "")'
                onMouseover="status='国内生産会社決裁基準を表示します。';return true;"
                onMouseout="status=''"
                title='国内生産会社決裁基準を表示します。'
            >国内生産会社</a>
        </td>
        <?php
        } else {
        ?>
        <td class='layout' id='start'>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("kessai-ringi21.04.01.pdf", "")'
                onMouseover="status='決裁・稟議規程を表示します。';return true;"
                onMouseout="status=''"
                title='決裁・稟議規程を表示します。'
            >決裁・稟議規程</a>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("kessai-kijyun-nitijyo19.01.01.pdf", "")'
                onMouseover="status='日常業務決裁基準を表示します。';return true;"
                onMouseout="status=''"
                title='日常業務決裁基準を表示します。'
            >日常業務</a>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("kessai-kijyun-kokunai21.04.01.pdf", "")'
                onMouseover="status='国内生産会社決裁基準を表示します。';return true;"
                onMouseout="status=''"
                title='国内生産会社決裁基準を表示します。'
            >国内生産会社</a>
        </td>
        <?php
        }
        ?>
        <td class='layout'>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("kitei-kanri21.04.01.pdf", "")'
                onMouseover="status='規定管理規程を表示します。';return true;"
                onMouseout="status=''"
                title='規定管理規程を表示します。'
            >規定管理規程</a>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("kitei-taikeizu21.04.01.pdf", "")'
                onMouseover="status='規程体系図を表示します。';return true;"
                onMouseout="status=''"
                title='規程体系図を表示します。'
            >規程体系図</a>
        </td>
    </tr>
    <tr class='layout'>
        <td class='layout' id='start'>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("gyoumu-hikitsugi20.10.01.pdf", "")'
                onMouseover="status='業務引継規程を表示します。';return true;"
                onMouseout="status=''"
                title='業務引継規程を表示します。'
            >業務引継規程</a>
        </td>
        <td class='layout'>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("naibu-kansa21.04.01.pdf", "")'
                onMouseover="status='内部監査規程を表示します。';return true;"
                onMouseout="status=''"
                title='内部監査規程を表示します。'
            >内部監査規程(※)</a>
        </td>
    </tr>
    <tr class='layout'>
        <td class='layout' id='start'>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("chuki-keikaku18.05.22.pdf", "")'
                onMouseover="status='中期経営計画規程を表示します。';return true;"
                onMouseout="status=''"
                title='中期経営計画規程を表示します。'
            >中期経営計画規程(※)</a>
        </td>
        <td class='layout' id='start'>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("yosan-kanri14.01.01.pdf", "")'
                onMouseover="status='予算管理規程を表示します。';return true;"
                onMouseout="status=''"
                title='予算管理規程を表示します。'
            >予算管理規程(※)</a>
        </td>
    </tr>
    <tr class='layout'>
        <td class='layout'>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("compliance-kitei22.04.01.pdf", "")'
                onMouseover="status='コンプライアンス規程を表示します。';return true;"
                onMouseout="status=''"
                title='コンプライアンス規程を表示します。'
            >コンプライアンス規程(※)</a>
        </td>
        <td class='layout' id='start'>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("naibu-tsuho22.06.01.pdf", "")'
                onMouseover="status='内部通報規程を表示します。';return true;"
                onMouseout="status=''"
                title='内部通報規程を表示します。'
            >内部通報規程(※)</a>
        </td>
    </tr>
    <tr class='layout'>
        <td class='layout' id='start' colspan='2'>
        ●管理規程－業務規程－総務関係の規程
        </td>
    </tr>
    <tr class='layout'>
        <td class='layout'>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("inshou20.10.01.pdf", "")'
                onMouseover="status='印章規程を表示します。';return true;"
                onMouseout="status=''"
                title='印章規程を表示します。'
            >印章規程</a>
        </td>
        <td class='layout'>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("keicho-mimai21.04.01.pdf", "")'
                onMouseover="status='従業員の慶弔見舞金に関する規程を表示します。';return true;"
                onMouseout="status=''"
                title='従業員の慶弔見舞金に関する規程を表示します。'
            >慶弔見舞金規程</a>
        </td>
        
    </tr>
    <tr class='layout'>
        <td class='layout'>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("kokunai-ryohi20.10.01.pdf", "")'
                onMouseover="status='国内旅費規程を表示します。';return true;"
                onMouseout="status=''"
                title='国内旅費規程を表示します。'
            >国内旅費規程</a>
            &nbsp;
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("kokunai-beppyou1.18.04.01.pdf", "")'
                onMouseover="status='国内旅費規程の別表１を表示します。';return true;"
                onMouseout="status=''"
                title='国内旅費規程の別表１を表示します。'
            >別表１</a>
        </td>
        <td class='layout'>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("kaigai-ryohi20.10.01.pdf", "")'
                onMouseover="status='海外旅費規程を表示します。';return true;"
                onMouseout="status=''"
                title='海外旅費規程を表示します。'
            >海外旅費規程</a>
        </td>
    </tr>
    <tr class='layout'>
        <td class='layout'>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("kokunaikensyu-ryohi20.10.01.pdf", "")'
                onMouseover="status='国内研修旅費規程を表示します。';return true;"
                onMouseout="status=''"
                title='国内研修旅費規程を表示します。'
            >国内研修旅費規程</a>
        </td>
        <td class='layout'>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("anzeneisei-kanri20.10.01.pdf", "")'
                onMouseover="status='安全衛生管理規程を表示します。';return true;"
                onMouseout="status=''"
                title='安全衛生管理規程を表示します。'
            >安全衛生管理規程</a>
        </td>
    </tr>
    <tr class='layout'>
        <td class='layout'>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("jyugyouin-maika21.04.01.pdf", "")'
                onMouseover="status='従業員マイカー管理規程を表示します。';return true;"
                onMouseout="status=''"
                title='従業員マイカー管理規程を表示します。'
            >従業員マイカー管理規程</a>
        </td>
        <td class='layout'>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("mycar-gyoumu20.10.01.pdf", "")'
                onMouseover="status='マイカーの業務使用に関する規程を表示します。';return true;"
                onMouseout="status=''"
                title='マイカーの業務使用に関する規程を表示します。'
            >マイカーの業務使用に関する規程</a>
        </td>
    </tr>
    <tr class='layout'>
        <td class='layout'>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("anzeneisei-iinkai20.10.01.pdf", "")'
                onMouseover="status='安全衛生委員会細則を表示します。';return true;"
                onMouseout="status=''"
                title='安全衛生委員会細則を表示します。'
            >安全衛生委員会細則</a>
        </td>
        <td class='layout'>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("bunsho-kitei20.10.01.pdf", "")'
                onMouseover="status='文書規程及び文書作成実務マニュアルを表示します。';return true;"
                onMouseout="status=''"
                title='文書規程及び文書作成実務マニュアルを表示します。'
            >文書規程－文書作成実務マニュアル</a>
            <BR>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("bunsho-beppyou1.pdf", "")'
                onMouseover="status='別表１：文書保存期間一覧表を表示します。';return true;"
                onMouseout="status=''"
                title='別表１：文書保存期間一覧表を表示します。'
            >別表１：文書保存期間一覧表</a>
        </td>
    </tr>
    <tr class='layout'>
        <td class='layout'>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("syobo20.10.01.pdf", "")'
                onMouseover="status='消防計画規程を表示します。';return true;"
                onMouseout="status=''"
                title='消防計画規程を表示します。'
            >消防計画規程</a>
        </td>
        <td class='layout'>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("jyoho-kanri22.04.01.pdf", "")'
                onMouseover="status='情報管理規程を表示します。';return true;"
                onMouseout="status=''"
                title='情報管理規程を表示します。'
            >情報管理規程(※)</a>
        </td>
    </tr>
    <tr class='layout'>
        <td class='layout'>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("jyoho-kanri-saisoku22.04.01.pdf", "")'
                onMouseover="status='情報管理規程細則を表示します。';return true;"
                onMouseout="status=''"
                title='情報管理規程細則を表示します。'
            >情報管理規程細則(※)</a>
        </td>
        <td class='layout'>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("kiki-kanri22.04.01.pdf", "")'
                onMouseover="status='危機管理規程を表示します。';return true;"
                onMouseout="status=''"
                title='危機管理規程を表示します。'
            >危機管理規程(※)</a>
        </td>
    </tr>
    <tr class='layout'>
        <td class='layout'>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("nitto-naibu-torihiki20.03.10.pdf", "")'
                onMouseover="status='日東工器グループ内部者取引管理規程を表示します。';return true;"
                onMouseout="status=''"
                title='日東工器グループ内部者取引管理規程を表示します。'
            >日東工器グループ内部者取引管理規程(※)</a>
        </td>
        <td class='layout'>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("kojin-jyoho-hogo17.10.23.pdf", "")'
                onMouseover="status='個人情報保護規程を表示します。';return true;"
                onMouseout="status=''"
                title='個人情報保護規程を表示します。'
            >個人情報保護規程(※)</a>
        </td>
    </tr>
    <tr class='layout'>
        <td class='layout'>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("tokutei-kojinjyoho-tori17.05.11.pdf", "")'
                onMouseover="status='特定個人情報取扱規程を表示します。';return true;"
                onMouseout="status=''"
                title='特定個人情報取扱規程を表示します。'
            >特定個人情報取扱規程(※)</a>
        </td>
        <td class='layout'>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("kenko-jyoho-tori21.04.01.pdf", "")'
                onMouseover="status='健康情報等の取扱規程を表示します。';return true;"
                onMouseout="status=''"
                title='健康情報等の取扱規程を表示します。'
            >健康情報等の取扱規程</a>
        </td>
    </tr>
    <tr class='layout'>
        <td class='layout' id='start' colspan='2'>
        ●管理規程－業務規程－人事労務関係の規程
        </td>
    </tr>
    <tr class='layout'>
        <td class='layout'>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("syuugyou21.04.01.pdf", "")'
                onMouseover="status='就業規則を表示します。';return true;"
                onMouseout="status=''"
                title='就業規則を表示します。'
            >就業規則</a>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("syuugyou-part21.04.01.pdf", "")'
                onMouseover="status='パートタイマー就業規則(規程)を表示します。';return true;"
                onMouseout="status=''"
                title='パートタイマー就業規則(規程)を表示します。'
            >パートタイマー就業規則(規程)</a>
            &nbsp;
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("syuugyou-besshi.pdf", "")'
                onMouseover="status='就業規則の別紙を表示します。';return true;"
                onMouseout="status=''"
                title='就業規則の別紙を表示します。'
            >別紙</a>
        </td>
        <td class='layout'>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("koutaikinmu-unyo-saisoku20.10.01.pdf", "")'
                onMouseover="status='交代勤務運用細則を表示します。';return true;"
                onMouseout="status=''"
                title='交代勤務運用細則を表示します。'
            >交代勤務運用細則</a>
        </td>
    </tr>
    <tr class='layout'>
        <td class='layout'>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("chingin22.04.11.pdf", "")'
                onMouseover="status='賃金規程を表示します。';return true;"
                onMouseout="status=''"
                title='賃金規程を表示します。'
            >賃金規程</a>
        </td>
        <td class='layout'>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("shoteate-saisoku21.04.01.pdf", "")'
                onMouseover="status='諸手当細則を表示します。';return true;"
                onMouseout="status=''"
                title='諸手当細則を表示します。'
            >諸手当細則</a>
        </td>
    </tr>
    <tr class='layout'>
        <td class='layout'>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("kakutei-nenkin16.10.01.pdf", "")'
                onMouseover="status='規約型確定給付企業年金規約を表示します。';return true;"
                onMouseout="status=''"
                title='規約型確定給付企業年金規約を表示します。'
            >規約型確定給付企業年金規約</a>
        </td>
        <td class='layout'>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("shikaku-toukyu21.04.01.pdf", "")'
                onMouseover="status='資格等級制度規程を表示します。';return true;"
                onMouseout="status=''"
                title='資格等級制度規程を表示します。'
            >資格等級制度規程</a>
        </td>
    </tr>
    <tr class='layout'>
        <td class='layout'>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("kokunaitenkin-funinsha20.10.01.pdf", "")'
                onMouseover="status='国内転勤・赴任者の取扱いに関する規程を表示します。';return true;"
                onMouseout="status=''"
                title='国内転勤・赴任者の取扱いに関する規程を表示します。'
            >国内転勤・赴任者の取扱いに関する規程</a>
        </td>
        <td class='layout'>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("jyuutaku-yuushi20.10.01.pdf", "")'
                onMouseover="status='住宅融資規程を表示します。';return true;"
                onMouseout="status=''"
                title='住宅融資規程を表示します。'
            >住宅融資規程</a>
        </td>
    </tr>
    <tr class='layout'>
        <td class='layout'>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("ippan-kashituke20.10.01.pdf", "")'
                onMouseover="status='一般貸付規程を表示します。';return true;"
                onMouseout="status=''"
                title='一般貸付規程を表示します。'
            >一般貸付規程</a>
        </td>
        <td class='layout'>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("kyouiku-shikin20.10.01.pdf", "")'
                onMouseover="status='教育資金貸付規程を表示します。';return true;"
                onMouseout="status=''"
                title='教育資金貸付規程を表示します。'
            >教育資金貸付規程</a>
        </td>
    </tr>
    <tr class='layout'>
        <td class='layout'>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("ikuji-kyuugyou22.04.01.pdf", "")'
                onMouseover="status='育児休業規程を表示します。';return true;"
                onMouseout="status=''"
                title='育児休業規程を表示します。'
            >育児休業規程</a>
        </td>
        <td class='layout'>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("kaigo-kyuugyou22.04.01.pdf", "")'
                onMouseover="status='介護休業規程を表示します。';return true;"
                onMouseout="status=''"
                title='介護休業規程を表示します。'
            >介護休業規程</a>
        </td>
    </tr>
    <tr class='layout'>
        <td class='layout'>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("sinnya-menjyo20.10.01.pdf", "")'
                onMouseover="status='深夜業免除規程を表示します。';return true;"
                onMouseout="status=''"
                title='深夜業免除規程を表示します。'
            >深夜業免除規程</a>
        </td>
        <td class='layout'>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("gyomujyosaigai-hosyo20.10.01.pdf", "")'
                onMouseover="status='業務上災害法定外補償規程を表示します。';return true;"
                onMouseout="status=''"
                title='業務上災害法定外補償規程を表示します。'
            >業務上災害法定外補償規程</a>
        </td>
    </tr>
    <tr class='layout'>
        <td class='layout'>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("bosei-kenkokanri21.04.01.pdf", "")'
                onMouseover="status='母性健康管理の措置に関する規定を表示します。';return true;"
                onMouseout="status=''"
                title='母性健康管理の措置に関する規定を表示します。'
            >母性健康管理の措置に関する規定</a>
        </td>
        <td class='layout'>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("einen-kinzoku21.04.01.pdf", "")'
                onMouseover="status='永年勤続者表彰規程を表示します。';return true;"
                onMouseout="status=''"
                title='永年勤続者表彰規程を表示します。'
            >永年勤続者表彰規程</a>
        </td>
    </tr>
    <tr class='layout'>
        <td class='layout'>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("shokutaku21.04.01.pdf", "")'
                onMouseover="status='嘱託規程を表示します。';return true;"
                onMouseout="status=''"
                title='嘱託規程を表示します。'
            >嘱託規程</a>
        </td>
        <td class='layout'>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("shukkou21.04.01.pdf", "")'
                onMouseover="status='出向規程を表示します。';return true;"
                onMouseout="status=''"
                title='出向規程を表示します。'
            >出向規程</a>
        </td>
    </tr>
    <tr class='layout'>
        <td class='layout'>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("shataku-toriatsukai20.10.01.pdf", "")'
                onMouseover="status='社宅取扱規程を表示します。';return true;"
                onMouseout="status=''"
                title='社宅取扱規程を表示します。'
            >社宅取扱規程</a>
        </td>
        <td class='layout'>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("kaigaishuchou-saigaihosyou20.10.01.pdf", "")'
                onMouseover="status='海外出張時における災害補償規程を表示します。';return true;"
                onMouseout="status=''"
                title='海外出張時における災害補償規程を表示します。'
            >海外出張時における災害補償規程</a>
        </td>
    </tr>
    <tr class='layout'>
        <td class='layout'>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("teinengo-saikoyou21.04.01.pdf", "")'
                onMouseover="status='定年後再雇用の契約社員規程を表示します。';return true;"
                onMouseout="status=''"
                title='定年後再雇用の契約社員規程を表示します。'
            >定年後再雇用の契約社員規程</a>
        </td>
        <td class='layout'>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("harasu-boushi22.04.01.pdf", "")'
                onMouseover="status='ハラスメント防止規程を表示します。';return true;"
                onMouseout="status=''"
                title='ハラスメント防止規程を表示します。'
            >ハラスメント防止規程(※)</a>
        </td>
    </tr>
    <tr class='layout'>
        <td class='layout' id='start' colspan='2'>
        ●管理規程－業務規程－経理関係の規程
        </td>
    </tr>
    <tr class='layout'>
        <td class='layout'>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("keiri20.10.01.pdf", "")'
                onMouseover="status='経理規程を表示します。';return true;"
                onMouseout="status=''"
                title='経理規程を表示します。'
            >経理規程</a>
        </td>
        <td class='layout'>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("suitou20.10.01.pdf", "")'
                onMouseover="status='出納規程を表示します。';return true;"
                onMouseout="status=''"
                title='出納規程を表示します。'
            >出納規程</a>
        </td>
    </tr>
    <tr class='layout'>
        <td class='layout'>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("tegata-yukashoken20.10.01.pdf", "")'
                onMouseover="status='手形・有価証券管理規程を表示します。';return true;"
                onMouseout="status=''"
                title='手形・有価証券管理規程を表示します。'
            >手形・有価証券管理規程</a>
        </td>
        <td class='layout'>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("genka-keisan20.10.01.pdf", "")'
                onMouseover="status='原価計算規程を表示します。';return true;"
                onMouseout="status=''"
                title='原価計算規程を表示します。'
            >原価計算規程</a>
        </td>
    </tr>
    <tr class='layout'>
        <td class='layout'>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("tanaoroshishisan-kannri20.10.01.pdf", "")'
                onMouseover="status='棚卸資産管理規程を表示します。';return true;"
                onMouseout="status=''"
                title='棚卸資産管理規程を表示します。'
            >棚卸資産管理規程</a>
        </td>
        <td class='layout'>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("koteishisan-kanri20.10.01.pdf", "")'
                onMouseover="status='固定資産管理規程を表示します。';return true;"
                onMouseout="status=''"
                title='固定資産管理規程を表示します。'
            >固定資産管理規程</a>
        </td>
    </tr>
    <tr class='layout'>
        <td class='layout' id='start' colspan='2'>
        ●管理規程－業務規程－資材関係の規程
        </td>
    </tr>
    <tr class='layout'>
        <td class='layout'>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("seisan-kanri20.10.01.pdf", "")'
                onMouseover="status='生産管理規程を表示します。';return true;"
                onMouseout="status=''"
                title='生産管理規程を表示します。'
            >生産管理規程</a>
        </td>
        <td class='layout'>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("koubai-kanri20.10.01.pdf", "")'
                onMouseover="status='購買管理規程を表示します。';return true;"
                onMouseout="status=''"
                title='購買管理規程を表示します。'
            >購買管理規程</a>
        </td>
    </tr>
    <tr class='layout'>
        <td class='layout'>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("green-chotatsu-iinkai14.01.01.pdf", "")'
                onMouseover="status='グリーン調達委員会規則(細則)を表示します。';return true;"
                onMouseout="status=''"
                title='グリーン調達委員会規則(細則)を表示します。'
            >グリーン調達委員会規則(細則)(※)</a>
        </td>
        <td class='layout'>
            　
        </td>
    </tr>
    <tr class='layout'>
        <td class='layout' id='start' colspan='2'>
        ●管理規程－業務規程－開発関係の規程
        </td>
    </tr>
    <tr class='layout'>
        <td class='layout'>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("hatsumei-toriatukai22.04.01.pdf", "")'
                onMouseover="status='発明取扱規程を表示します。';return true;"
                onMouseout="status=''"
                title='発明取扱規程を表示します。'
            >発明取扱規程(※)</a>
        </td>
        <td class='layout'>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("hatsumei-toriatukai-saisoku22.04.01.pdf", "")'
                onMouseover="status='発明取扱規程細則を表示します。';return true;"
                onMouseout="status=''"
                title='発明取扱規程細則を表示します。'
            >発明取扱規程細則(※)</a>
        </td>
    </tr>
    <tr class='layout'>
        <td class='layout' id='start' colspan='2'>
        ●隣接規程
        </td>
    </tr>
    <tr class='layout'>
        <td class='layout'>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("mochikabu-kiyaku19.09.13.pdf", "")'
                onMouseover="status='日東工器従業員持株会規約を表示します。';return true;"
                onMouseout="status=''"
                title='日東工器従業員持株会規約を表示します。'
            >日東工器従業員持株会規約(※)</a>
        </td>
        <td class='layout'>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("mochikabu-unei19.09.13.pdf", "")'
                onMouseover="status='日東工器従業員持株会運営細則を表示します。';return true;"
                onMouseout="status=''"
                title='日東工器従業員持株会運営細則を表示します。'
            >日東工器従業員持株会運営細則(※)</a>
        </td>
    </tr>
    <tr class='layout'>
        <td class='layout' id='start' colspan='2'>
        ●その他
        </td>
    </tr>
    <tr class='layout'>
        <td class='layout'>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("gasoline-suii.pdf", "")'
                onMouseover="status='ガソリン単価推移表を表示します。';return true;"
                onMouseout="status=''"
                title='ガソリン単価推移表を表示します。'
            >ガソリン単価推移表</a>
         <td class='layout'>
            <a href='internal_control/regulation_inter_menu.php'>内部統制関連</a>
        </td>
    </tr>
    <tr class='layout'>
        <td class='layout'>
            <a href='JavaScript:void(0)'
                onClick='Regu.win_open("mycar-gasoline-suii.pdf", "")'
                onMouseover="status='マイカー業務使用時のガソリン単価推移表を表示します。';return true;"
                onMouseout="status=''"
                title='マイカー業務使用時のガソリン単価推移表を表示します。'
            >マイカー業務使用時ガソリン単価推移表</a>
        </td>
         <td class='layout'>
            　
        </td>
    </tr>
    </table>
    </center>
</body>
<?=$menu->out_alert_java()?>
</html>
<?php
ob_end_flush();                 // 出力バッファをgzip圧縮 END
?>
