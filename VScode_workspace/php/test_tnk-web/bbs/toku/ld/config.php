<?php

/**--------------------
 * デザインの設定
 */

/**
 * 全体の背景色
 */
define("APP_HTML_BGCOLOR","#FFFFFF");

/**
 * 枠の背景色
 */
define("APP_HTML_WAKUBGCOLOR","white");

/**
 * デザインをよりカスタマイズしたい場合は view/template/ ディレクトリにあるファイルを編集します
 * CSS記述ファイルは view/template/parts/header.php です
 */

// -----------------------

/**
 * 記事データ保存ディレクトリ
 */
define("APP_DATA_DIR","data/");

/**
 * 返信記事ディレクトリ
 */
define("APP_RES_DIR","res/");

/**
 * 記事データファイル名
 */
define("APP_DATA_FILE",APP_DATA_DIR."data.cgi");

/**
 * データ表示件数
 */
define("APP_DATA_VIEW_COUNT",10);

/**
 * データ保存最大件数
 */
define("APP_DATA_SAVE_MAX",2000);

/**
 * rss出力データ件数
 */
define("APP_RSS_VIEW_COUNT",10);

/**
 * javascript出力データ件数
 */
define("APP_JS_VIEW_COUNT",10);

/**
 * 記事返信時に記事ＵＰ(1:記事ＵＰする,0:記事ＵＰしない)
 */
define("APP_KIJI_UP",1);

/**
 * titleタグ内に記述するタイトル
 */
define("APP_TITLE", "カプラ特注課 掲示板");

/**
 * データ更新、削除 - 認証ユーザ
 * ! 必ず変更してください
 */
$_APP_AUTH_USER = array(
'k_kobayashi' => 'bbs',
'n_ooya' => 'bbs'
);

/**--------------------------------
 * メール投稿機能を使用する(0:使わない;1:使う)
 */
define("APP_MAIL_POST",0);

/**
 * メール投稿利用の際のメールサーバ名
 */
define("APP_MAIL_HOST","mx.server.jp");

/**
 * メール投稿利用の際のユーザＩＤ
 */
define("APP_MAIL_UID","userid");

/**
 * メール投稿利用の際のパスワード
 */
define("APP_MAIL_PASS","password");

/**
 * メール投稿用のメールアドレス
 */
define("APP_MAIL_ADDR","mail@mail.com");

// -----------------------------------

/** ---------------------
 * キャッシュページ接頭辞
 */
define("APP_PAGE_PREFIX","im");

?>