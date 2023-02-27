//  ========================================================
//  jkl-calendar.js ---- ポップアップカレンダー表示クラス
//  Copyright 2005-2006 Kawasaki Yusuke <u-suke [at] kawa.net>
//  Thanks to 2tak <info [at] code-hour.com>
//  http://www.kawa.net/works/js/jkl/calender.html
//  2005/04/06 - 最初のバージョン
//  2005/04/10 - 外部スタイルシートを使用しない、JKL.Opacity はオプション
//  2006/10/22 - typo修正、spliter/min_date/max_dateプロパティ、×ボタン追加
//  2006/10/23 - prototype.js併用時は、Event.observe()でイベント登録
//  2006/10/24 - max_date 範囲バグ修正
//  2006/10/25 - フォームに初期値があれば、カレンダーの初期値に採用する
//  2006/11/15 - MOM Update 週の初めの曜日を変更できるように修正
//  2006/11/23 - MOM Update 今日日付の文字色を指定できるように修正、あと枠線も描画してみる
//               邪魔な<select>への応急処置を書いてみた
//  2006/11/27 - MOM Update 邪魔な<select>への応急処置を修正、描画領域の高さを取得する
//  2006/11/30 - MOM Update 選択可能な曜日をプロパティに追加、今日日付と選択不可能な日付の背景色をスタイルに追加
//               カレンダーのz-indexをプロパティに追加
//  2006/12/04 - ksuzu Update 選択可能日がない月には移動できないように変更
//               カレンダーの表示月をクリックすると現在の月に移動できるよう変更
//               閉じるボタンにてカレンダーを閉じたとき、カレンダーの初期表示を戻すよう変更
//  2006/12/30 - MOM IFRAMEのSRC属性にdummy.htmlを挿入
//  2007/02/04 - MOM setDateYMDのバグを修正
//               TDタグのスタイルに背景色を指定するよう修正
//  2007/03/12 - 安達宏行 祝日自動判定追加
//                   紹介ブログ：http://www.adachi-shihosyoshi.com/archives/50727775.html
//               ・角田 桂一 さん祝日判定用ソースを使用
//                   ホームページ：http://www.h3.dion.ne.jp/~sakatsu/index.htm
//                   ダウンロード：http://www.h3.dion.ne.jp/~sakatsu/HolidayChk.js               
//               ・祝日セルの文字色を変更
//               ・マウスを当てると祝日名を表示
//  2007/03/25 - 安達宏行 固定表示、選択可能日指定設定機能を追加
//               ・joao さんのソースを利用させていただきましたm(__)m
//                   ホームページ：http://www.goigoipro.com/
//  2007/04/03 - 安達宏行 特別な休日設定機能を強化
//  ========================================================

/***********************************************************
//  （サンプル）ポップアップするカレンダー

  <html>
    <head>
      <script type="text/javascript" src="jkl-calendar_sp1.0.js" charset="Shift_JIS"></script>
      // 2007.03.12 安達宏行 角田さんの祝日判定用スクリプトを追加
      <script type="text/javascript" src="HolidayChk.js" charset="Shift_JIS"></script>
      <script>
        var cal1 = new JKL.Calendar("calid","formid","colname");
      </script>
    </head>
    <body>
      <form id="formid" action="">
        <input type="text" name="colname" onClick="cal1.write();" onChange="cal1.getFormValue(); cal1.hide();"><br>
        <div id="calid"></div>
      </form>
    </body>
  </html>

//  （サンプル）固定表示
// 2007.03.25 安達宏行 joaoさんの固定表示を利用

    <head>
      <script type="text/javascript" src="jkl-calendar_sp1.1.js" charset="Shift_JIS"></script>
      <script type="text/javascript" src="HolidayChk.js" charset="Shift_JIS"></script>
      <script>
        var cal1 = new JKL.Calendar("calid","formid","colname");
      </script>
    </head>
    //  固定表示：<body onload="cal1.write(1);">;
    <body onload="cal1.write(1);">
      <form id="formid" action="">
        <input type="text" name="colname"><br>
        <div id="calid"></div>
      </form>
    </body>
  </html>

 **********************************************************/

// 親クラス

if ( typeof(JKL) == 'undefined' ) JKL = function() {};

// JKL.Calendar コンストラクタの定義

JKL.Calendar = function ( eid, fid, valname ) {
    this.eid = eid;
    this.formid = fid;
    this.valname = valname;
    this.__dispelem = null;  // カレンダー表示欄エレメント
    this.__textelem = null;  // テキスト入力欄エレメント
    this.__opaciobj = null;  // JKL.Opacity オブジェクト
    this.style = new JKL.Calendar.Style();
    return this;
};

// バージョン番号

JKL.Calendar.VERSION = "0.13";

// デフォルトのプロパティ

JKL.Calendar.prototype.spliter = "/";
JKL.Calendar.prototype.date = null;
JKL.Calendar.prototype.min_date = null;
JKL.Calendar.prototype.max_date = null;
JKL.Calendar.prototype.show_cd  = null;

// 2006.11.15 MOM 表示開始曜日をプロパティに追加(デフォルトは日曜日=0)
JKL.Calendar.prototype.start_day = 0;

// 2006.11.23 MOM カレンダー内の日付を枠線で区切るかどうかのプロパティ(デフォルトはtrue)
JKL.Calendar.prototype.draw_border = true;

// 2007.04.03 安達宏行 特別な休日（土・日・祝日を除く）設定機能を強化（'月日'、'年月日'のいずれかで設定できますが、'年月日'の場合はその年に限定されます。）
//（記入例）'1/2','年始休暇',
//          '1/3','年始休暇',
//          '2007/8/15','創業10周年記念日',
//          '12/29','年末休暇',
//          '12/30','年末休暇',
//          '12/31','年末休暇'
//（記入上の注意）※1 休日を設定する場合は日付と休日名が必須
//                ※2 日付と休日名は半角 '' で囲み、区切りには半角 , を記入
//                ※3 最後の休日名の区切り , は不要
//                ※4 記入例にある左端の // は不要
//                ※5 設定しない場合は空白のまま
//                ※6 休日の文字の色はデフォルトでは日曜・祝日と同色　変更はスタイルへ↓↓
JKL.Calendar.prototype.kyuzitsu_days = new Array(
//_/ここから_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/
   //'1/2','年始休業日',
   //'1/3','年始休業日',
   //'12/29','年末休業日',
   //'12/30','年末休業日',
   //'12/31','年末休業日'
   // 第17期
    '2016/8/15','夏期休暇',
    '2016/8/16','夏期休暇',
    '2016/8/17','夏期休暇',
    '2016/8/18','夏期休暇',
    '2016/8/19','夏期休暇',
    '2016/12/29','年末休暇',
    '2016/12/30','年末休暇',
    '2017/1/2','年始休暇',
    '2017/1/3','年始休暇',
    '2017/1/4','年始休暇',
   // 第18期
    '2017/8/14','夏期休暇',
    '2017/8/15','夏期休暇',
    '2017/8/16','夏期休暇',
    '2017/8/17','夏期休暇',
    '2017/8/18','夏期休暇',
    '2017/12/29','年末休暇',
    '2018/1/2','年始休暇',
    '2018/1/3','年始休暇',
    '2018/1/4','年始休暇'
//_/ここまでに記入_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/
);

// 2006.11.30 MOM 各曜日の選択可否をプロパティに追加(デフォルトは全てtrue)
// 配列の添え字で曜日を指定(0～6 = 日曜～土曜)、選択可否をboolean値で代入する、という使い方
JKL.Calendar.prototype.selectable_days = new Array(true,true,true,true,true,true,true);

// 2007.03.25 安達宏行 祝日・休日の選択可否をプロパティに追加（選択可能日:true 、選択不能日:false、デフォルトは全てtrue）
// 祝日
JKL.Calendar.prototype.selectable_holiday = true;
// 休日
JKL.Calendar.prototype.selectable_kyuzitsu = true;

// 2006.11.30 MOM カレンダーのz-indexをプロパティに追加
JKL.Calendar.prototype.zindex = 10;

// JKL.Calendar.Style

JKL.Calendar.Style = function() {
    return this;
};

// デフォルトのスタイル

JKL.Calendar.Style.prototype.frame_width        = "150px";      // フレーム横幅
JKL.Calendar.Style.prototype.frame_color        = "#006000";    // フレーム枠の色
JKL.Calendar.Style.prototype.font_size          = "12px";       // 文字サイズ
JKL.Calendar.Style.prototype.day_bgcolor        = "#F0F0F0";    // カレンダーの背景色
JKL.Calendar.Style.prototype.month_color        = "#FFFFFF";    // ○年○月部分の背景色
JKL.Calendar.Style.prototype.month_hover_color  = "#009900";    // マウスオーバー時の≪≫文字色
JKL.Calendar.Style.prototype.month_hover_bgcolor= "#FFFFCC";    // マウスオーバー時の≪≫背景色
JKL.Calendar.Style.prototype.weekday_color      = "#404040";    // 月曜～金曜日セルの文字色
JKL.Calendar.Style.prototype.saturday_color     = "#0040D0";    // 土曜日セルの文字色
JKL.Calendar.Style.prototype.sunday_color       = "#D30000";    // 日曜日・祝日セルの文字色
// 2007.03.25 安達宏行 休日セルの文字色を追加
JKL.Calendar.Style.prototype.kyuzitsu_color     = "#D30000";    // 休日セルの文字色
JKL.Calendar.Style.prototype.others_color       = "#999999";    // 他の月の日セルの文字色
JKL.Calendar.Style.prototype.day_hover_bgcolor  = "#FFCC33";    // マウスオーバー時の日セルの背景
JKL.Calendar.Style.prototype.cursor             = "pointer";    // マウスオーバー時のカーソル形状

// 2006.11.23 MOM 今日日付の文字色をプロパティに追加
JKL.Calendar.Style.prototype.today_color        = "#008000";    // 今日日付セルの文字色
// 2006.11.23 MOM 枠線もつけてみる
JKL.Calendar.Style.prototype.today_border_color = "#00A000";    // 今日日付セルの枠線の色
JKL.Calendar.Style.prototype.others_border_color= "#E0E0E0";    // 他の日セルの枠線の色

// 2006.11.30 MOM 今日日付の背景色を忘れてたので追加してみる
JKL.Calendar.Style.prototype.today_bgcolor      = "#D0FFD0";    // 今日日付セルの背景色
// 2006.11.30 MOM 選択不能な日付の背景色を追加
JKL.Calendar.Style.prototype.unselectable_day_bgcolor = "#CECEDD";    // 選択不能な日付の背景色
// 2007.03.25 安達宏行 選択不能な今日日付セルの背景色を追加
JKL.Calendar.Style.prototype.unselectable_today_bgcolor      = "#E0E0E0";    // 選択不能な今日日付セルの背景色
// 2007.03.25 安達宏行 選択不能日付セルの枠線の色を追加
JKL.Calendar.Style.prototype.unselectable_day_border_color = "#0000CC";// 選択不能日付セルの枠線の色

//  メソッド

JKL.Calendar.Style.prototype.set = function(key,val) { this[key] = val; }
JKL.Calendar.Style.prototype.get = function(key) { return this[key]; }
JKL.Calendar.prototype.setStyle = function(key,val) { this.style.set(key,val); };
JKL.Calendar.prototype.getStyle = function(key) { return this.style.get(key); };

// 日付を初期化する

JKL.Calendar.prototype.initDate = function ( dd ) {
    if ( ! dd ) dd = new Date();
    var year = dd.getFullYear();
    var mon  = dd.getMonth();
    var date = dd.getDate();
    this.date = new Date( year, mon, date );
    this.getFormValue();
    return this.date;
}

// 透明度設定のオブジェクトを返す

JKL.Calendar.prototype.getOpacityObject = function () {
    if ( this.__opaciobj ) return this.__opaciobj;
    var cal = this.getCalendarElement();
    if ( ! JKL.Opacity ) return;
    this.__opaciobj = new JKL.Opacity( cal );
    return this.__opaciobj;
};

// カレンダー表示欄のエレメントを返す

JKL.Calendar.prototype.getCalendarElement = function () {
    if ( this.__dispelem ) return this.__dispelem;
    this.__dispelem = document.getElementById( this.eid )
    return this.__dispelem;
};

// テキスト入力欄のエレメントを返す

JKL.Calendar.prototype.getFormElement = function () {
    if ( this.__textelem ) return this.__textelem;
    var frmelms = document.getElementById( this.formid );
    if ( ! frmelms ) return;
    for( var i=0; i < frmelms.elements.length; i++ ) {
        if ( frmelms.elements[i].name == this.valname ) {
            this.__textelem = frmelms.elements[i];
        }
    }
    return this.__textelem;
};

// オブジェクトに日付を記憶する（YYYY/MM/DD形式で指定する）

JKL.Calendar.prototype.setDateYMD = function (ymd) {
    var form_yy  = "" + ymd.substr(0,4);
    var form_mm  = "" + ymd.substr(4,2);
    var form_dd  = "" + ymd.substr(6,2);
    var form_ymd = form_yy + this.spliter + form_mm + this.spliter + form_dd; // 画面表示時、既に日付がセットされている場合 初期表示が当日になってしまうバグを修正(YYYY/MM/DDをYYYYMMDDに変更した為発生)
    var splt    = form_ymd.split( this.spliter );
    //var splt = ymd.split( this.spliter );
    if ( splt[0]-0 > 0 &&
         splt[1]-0 >= 1 && splt[1]-0 <= 12 &&       // bug fix 2006/03/03 thanks to ucb
         splt[2]-0 >= 1 && splt[2]-0 <= 31 ) {
        if ( ! this.date ) this.initDate();
/* 2007.02.04 MOM 画面表示時、既に日付がセットされている場合に発生するバグを修正
            this.date.setFullYear( splt[0] );
            this.date.setMonth( splt[1]-1 );
            this.date.setDate( splt[2] );
*/
            this.date.setDate( splt[2] );
            this.date.setMonth( splt[1]-1 );
            this.date.setFullYear( splt[0] );
    } else {
        ymd = "";
    }
    return ymd;
};

// オブジェクトから日付を取り出す（YYYY/MM/DD形式で返る）
// 引数に Date オブジェクトの指定があれば、
// オブジェクトは無視して、引数の日付を使用する（単なるfprint機能）

JKL.Calendar.prototype.getDateYMD = function ( dd ) {
    if ( ! dd ) {
        if ( ! this.date ) this.initDate();
        dd = this.date;
    }
    var mm = "" + (dd.getMonth()+1);
    var aa = "" + dd.getDate();
    if ( mm.length == 1 ) mm = "" + "0" + mm;
    if ( aa.length == 1 ) aa = "" + "0" + aa;
    return dd.getFullYear() + this.spliter + mm + this.spliter + aa;
};

// 2007.04.03 安達宏行 休日判定関数を新たに設ける（プロパティで設定した休日名を返す）

JKL.Calendar.prototype.getKyuzitsu = function ( prmDate ){
    MyDate = new Date(prmDate);
    MyYear = MyDate.getFullYear();
    MyMonth = MyDate.getMonth() + 1;    // MyMonth:1～12
    MyDay = MyDate.getDate();
    MyYMD = MyYear + '/' + MyMonth + '/' + MyDay;
    Result = "";
    var kyuzitsuLastCnt = this.kyuzitsu_days.length / 2;

    for(var i=0; i<kyuzitsuLastCnt; i++){
        if (this.kyuzitsu_days[i*2].length>5){
            var test = this.kyuzitsu_days[i*2];
        }else {
            test = MyYear + '/' + this.kyuzitsu_days[i*2];
        }
        if (MyYMD == test){Result = this.kyuzitsu_days[i*2+1]}
    }
    return Result;
};

// テキスト入力欄の値を返す（ついでにオブジェクトも更新する）

JKL.Calendar.prototype.getFormValue = function () {
    var form1 = this.getFormElement();
    if ( ! form1 ) return "";
    var date1 = this.setDateYMD( form1.value );
    return date1;
};

// フォーム入力欄に指定した値を書き込む

JKL.Calendar.prototype.setFormValue = function (ymd) {
    if ( ! ymd ) ymd = this.getDateYMD();   // 無指定時はオブジェクトから？
    var form1 = this.getFormElement();
    var ret_yy  = "" + ymd.substr(0,4);
    var ret_mm  = "" + ymd.substr(5,2);
    var ret_dd  = "" + ymd.substr(8,2);
    var ret_ymd = ret_yy + ret_mm + ret_dd;
    if ( form1 ) form1.value = ret_ymd;
    //if ( form1 ) form1.value = ymd;
};

// カレンダー表示欄を表示する

JKL.Calendar.prototype.show = function () {
    this.getCalendarElement().style.display = "";
    this.show_cd = "1";
};

// カレンダー表示欄を即座に隠す
// 2007.03.25 安達宏行 joaoさんの固定表示を利用
JKL.Calendar.prototype.hide = function () {
    this.getCalendarElement().style.display = "none";
};

// カレンダー以外のクリック時の動作
JKL.Calendar.prototype.hide_nocd = function () {
    var clickElement = this.eid;
        if (clickElement != null ) {
            alert(clickElement);
            //cal1.hide();
            //cal2.hide();
        }
};

// カレンダー表示欄をフェードアウトする
// 2007.03.25 安達宏行 joaoさんの固定表示を利用
JKL.Calendar.prototype.fadeOut = function ( s,fix ) {
    if( fix ){return}

    if ( JKL.Opacity ) {
        this.getOpacityObject().fadeOut(s);
    } else {
        this.hide();
    }
};

// 月単位で移動する
// 2007.03.25 安達宏行 joaoさんの固定表示を利用
JKL.Calendar.prototype.moveMonth = function ( mon,fix ) {
    // 前へ移動
    if ( ! this.date ) this.initDate();
    for( ; mon<0; mon++ ) {
        this.date.setDate(1);   // 毎月1日の1日前は必ず前の月
        this.date.setTime( this.date.getTime() - (24*3600*1000) );
    }
    // 後へ移動
    for( ; mon>0; mon-- ) {
        this.date.setDate(1);   // 毎月1日の32日後は必ず次の月
        this.date.setTime( this.date.getTime() + (24*3600*1000)*32 );
    }
    this.date.setDate(1);       // 当月の1日に戻す
    this.write( fix );    // 描画する
};

// イベントを登録する

JKL.Calendar.prototype.addEvent = function ( elem, ev, func ) {
//  prototype.js があれば利用する(IEメモリリーク回避)
    if ( window.Event && Event.observe ) {
        Event.observe( elem, ev, func, false );
    } else {
        elem["on"+ev] = func;
    }
}

// カレンダーを描画する

// 2006.03.25 安達宏行 joaoさんの固定表示を利用
JKL.Calendar.prototype.write = function ( fix ) {
    var date = new Date();
    if ( ! this.date ) this.initDate();
    date.setTime( this.date.getTime() );

    var year = date.getFullYear();          // 指定年
    var mon  = date.getMonth();             // 指定月
    var today = date.getDate();             // 指定日
    var form1 = this.getFormElement();
    //var form1 = this.getFormElement();
    //var f_ymd = this.getFormElement();
    //var f_yy  = '2017';
    //var f_mm  = '06';
    //var f_dd  = '14';
    //var form1 = f_yy + this.spliter + f_mm + this.spliter + f_dd; 

    // 選択可能な日付範囲
    var min;
    if ( this.min_date ) {
        var tmp = new Date( this.min_date.getFullYear(), 
            this.min_date.getMonth(), this.min_date.getDate() );
        min = tmp.getTime();
    }
    var max;
    if ( this.max_date ) {
        var tmp = new Date( this.max_date.getFullYear(), 
            this.max_date.getMonth(), this.max_date.getDate() );
        max = tmp.getTime();
    }

    // 直前の月曜日まで戻す
    date.setDate(1);                        // 1日に戻す
    var wday = date.getDay();               // 曜日 日曜(0)～土曜(6)

// 2006.11.15 MOM 表示開始曜日を可変にしたので、ロジックちょっといじりますよ
    if ( wday != this.start_day ) {
        date.setTime( date.getTime() - (24*3600*1000)*((wday-this.start_day+7)%7) );
    }
/*
    if ( wday != 1 ) {
        if ( wday == 0 ) wday = 7;
        date.setTime( date.getTime() - (24*3600*1000)*(wday-1) );
    }
*/

    // 最大で7日×6週間＝42日分のループ
    var list = new Array();
    for( var i=0; i<42; i++ ) {
        var tmp = new Date();
        tmp.setTime( date.getTime() + (24*3600*1000)*i );
        if ( i && i%7==0 && tmp.getMonth() != mon ) break;
        list[list.length] = tmp;
    }

    // スタイルシートを生成する
    var month_table_style = 'width: 100%; ';
    month_table_style += 'background: '+this.style.frame_color+'; ';
    month_table_style += 'border: 1px solid '+this.style.frame_color+';';

    var week_table_style = 'width: 100%; ';
    week_table_style += 'background: '+this.style.day_bgcolor+'; ';
    week_table_style += 'border-left: 1px solid '+this.style.frame_color+'; ';
    week_table_style += 'border-right: 1px solid '+this.style.frame_color+'; ';

    var days_table_style = 'width: 100%; ';
    days_table_style += 'background: '+this.style.day_bgcolor+'; ';
    days_table_style += 'border: 1px solid '+this.style.frame_color+'; ';

    var month_td_style = "";
// 2007.02.04 MOM TDタグも背景色のスタイルを明示的に指定する
    month_td_style += 'background: '+this.style.frame_color+'; ';
    month_td_style += 'font-size: '+this.style.font_size+'; ';
    month_td_style += 'color: '+this.style.month_color+'; ';
    month_td_style += 'padding: 4px 0px 2px 0px; ';
    month_td_style += 'text-align: center; ';
    month_td_style += 'font-weight: bold;';

    var week_td_style = "";
// 2007.02.04 MOM TDタグも背景色のスタイルを明示的に指定する
    week_td_style += 'background: '+this.style.day_bgcolor+'; ';
    week_td_style += 'font-size: '+this.style.font_size+'; ';
    week_td_style += 'padding: 2px 0px 2px 0px; ';
    week_td_style += 'font-weight: bold;';
    week_td_style += 'text-align: center;';

    var days_td_style = "";
// 2007.02.04 MOM TDタグも背景色のスタイルを明示的に指定する
    days_td_style += 'background: '+this.style.day_bgcolor+'; ';
    days_td_style += 'font-size: '+this.style.font_size+'; ';
    days_td_style += 'padding: 1px; ';
    days_td_style += 'text-align: center; ';
    days_td_style += 'font-weight: bold;';

    var days_unselectable = "font-weight: normal;";

    // HTMLソースを生成する
    var src1 = "";

// 2006.11.23 MOM 邪魔な<select>への応急処置その１
// テーブルをdivで囲んで上位レイヤに設定(z-indexの値を大きくしておく)
// 2006.11.27 MOM 描画フィールドの高さを取得するため、idをセットしておく
    src1 += '<BR><BR>';
    src1 += '<div id="'+this.eid+'_screen" style="position:relative;z-index:'+(this.zindex+1)+';">\n';

    src1 += '<table border="0" cellpadding="0" cellspacing="0" style="'+month_table_style+'"><tr>';
    src1 += '<td id="__'+this.eid+'_btn_prev" title="前の月へ" style="'+month_td_style+'">≪</td>';
    src1 += '<td style="'+month_td_style+'">&nbsp;</td>';
// 2006.12.04 ksuzu 表示月をクリックすると現在の月に移動
    src1 += '<td id="__'+this.eid+'_btn_today" style="'+month_td_style+'">'+(year)+'年 '+(mon+1)+'月</td>';
//    src1 += '<td style="'+month_td_style+'">'+(year)+'年 '+(mon+1)+'月</td>';
// 2007.03.25 安達宏行 joaoさんの固定表示を利用
    src1 += '<td id="__'+this.eid+'_btn_close" title="閉じる" style="'+month_td_style+'">';
	if( ! fix ){src1+='×'}
	src1 += '</td>';
    src1 += '<td id="__'+this.eid+'_btn_next" title="次の月へ" style="'+month_td_style+'">≫</td>';
    src1 += "</tr></table>\n";
    src1 += '<table border="0" cellpadding="0" cellspacing="0" style="'+week_table_style+'"><tr>';

// 2006.11.15 MOM 表示開始曜日start_dayから順に一週間分表示する
    for(var i = this.start_day; i < this.start_day + 7; i++){
        var _wday = i%7;
        if(_wday == 0){
             src1 += '<td style="color: '+this.style.sunday_color+'; '+week_td_style+'">日</td>';
        }else if(_wday == 6){
             src1 += '<td style="color: '+this.style.saturday_color+'; '+week_td_style+'">土</td>';
        }else{
             src1 += '<td style="color: '+this.style.weekday_color+'; '+week_td_style+'">';
            if(_wday == 1)        src1 += '月</td>';
            else if(_wday == 2)    src1 += '火</td>';
            else if(_wday == 3)    src1 += '水</td>';
            else if(_wday == 4)    src1 += '木</td>';
            else if(_wday == 5)    src1 += '金</td>';
        }
    }
/*
    src1 += '<td style="color: '+this.style.weekday_color+'; '+week_td_style+'">月</td>';
    src1 += '<td style="color: '+this.style.weekday_color+'; '+week_td_style+'">火</td>';
    src1 += '<td style="color: '+this.style.weekday_color+'; '+week_td_style+'">水</td>';
    src1 += '<td style="color: '+this.style.weekday_color+'; '+week_td_style+'">木</td>';
    src1 += '<td style="color: '+this.style.weekday_color+'; '+week_td_style+'">金</td>';
    src1 += '<td style="color: '+this.style.saturday_color+'; '+week_td_style+'">土</td>';
    src1 += '<td style="color: '+this.style.sunday_color+'; '+week_td_style+'">日</td>';
*/

    src1 += "</tr></table>\n";
    src1 += '<table border="0" cellpadding="0" cellspacing="0" style="'+days_table_style+'">';

    var curutc;
    if ( form1 && form1.value ) {
        var form_yy  = "" + form1.value.substr(0,4);
        var form_mm  = "" + form1.value.substr(4,2);
        var form_dd  = "" + form1.value.substr(6,2);
        var form_ymd = form_yy + this.spliter + form_mm + this.spliter + form_dd;
        var splt    = form_ymd.split(this.spliter);
        //var splt    = form1.value.split(this.spliter);
        if ( splt[0] > 0 && splt[2] > 0 ) {
            var curdd = new Date( splt[0]-0, splt[1]-1, splt[2]-0 );
            curutc = curdd.getTime();                           // フォーム上の当日
        }
    }

// 2006.11.23 MOM 今日日付を取得し、時分秒を切り捨てる
    var realdd = new Date();
    var realutc = (new Date(realdd.getFullYear(),realdd.getMonth(),realdd.getDate())).getTime();

    for ( var i=0; i<list.length; i++ ) {
        var dd = list[i];
        var ww = dd.getDay();
        var mm = dd.getMonth();

        if ( ww == this.start_day ) {
            src1 += "<tr>";                                     // 表示開始曜日の前に行頭
        }
/*
        if ( ww == 1 ) {
            src1 += "<tr>";                                     // 月曜日の前に行頭
        }
*/

        var cc = days_td_style;
        var utc = dd.getTime();

// 2007.03.12 安達宏行 祝日判定追加
// 2007.03.25 安達宏行 1月と12月頁の当月外の祝日判定バグを修正
// 2007.04.03 安達宏行 休日判定関数化に伴い修正
        var ss = this.getDateYMD(dd);
        var getholiday = ktHolidayName(ss);
        var kyuzitsu = this.getKyuzitsu(ss);

        if ( mon == mm ) {

// 2006.11.23 MOM 最初に今日日付かどうかをチェックする
// ※当月でない場合にも色変えると選択できそうに見えて紛らわしいので、当月かつ今日日付の場合のみ色を変える
        if ( utc == realutc ){
                cc += "color: "+this.style.today_color+";";     // 今日日付
            }
// 2007.03.12 安達宏行 祝日の色を変更
// 2007.03.25 安達宏行 休日の色を追加
	if ( ww == 0 || getholiday != "" ){
                cc += "color: "+this.style.sunday_color+";";    // 当月の日曜日・祝日
            } else if ( kyuzitsu != "" ) {
                cc += "color: "+this.style.kyuzitsu_color+";";  // 当月の休日
            } else if ( ww == 6 ) {
                cc += "color: "+this.style.saturday_color+";";  // 当月の土曜日
            } else {
                cc += "color: "+this.style.weekday_color+";";   // 当月の平日
            }
        } else {
            cc += "color: "+this.style.others_color+";";        // 前月末と翌月初の日付
        }

// 2006.11.23 MOM utcの変数宣言を↑に移動
//      var utc = dd.getTime();

// 2006.11.30 MOM 選択可能な曜日指定の条件追加
// 2007.04.03 安達宏行 条件に今月を追加
        if ( mon == mm && utc == curutc ) {                                  // フォーム上の当日
            cc += "background: "+this.style.day_hover_bgcolor+";";
        }

// 2006.11.30 MOM 今日日付の背景色
// 2007.03.25 安達宏行 選択不能かつ今日日付の背景色を追加
        else if ( mon == mm && utc == realutc ) {
            if(!this.selectable_days[dd.getDay()] || (!this.selectable_holiday && getholiday != "") || (!this.selectable_kyuzitsu && kyuzitsu != "")){
                cc += "background: "+this.style.unselectable_today_bgcolor+";";
            } else {
                cc += "background: "+this.style.today_bgcolor+";";
            }
        }
// 2006.11.30 MOM 選択不可能な日付の背景色
// 2007.03.25 安達宏行 joaoさんの選択可能日指定を利用
        else if (( min && min > utc ) || ( max && max < utc ) || (mon == mm && !this.selectable_days[dd.getDay()]) || (mon == mm && !this.selectable_holiday && getholiday != "") || (mon == mm && !this.selectable_kyuzitsu && kyuzitsu != "")){
            cc += 'background: '+this.style.unselectable_day_bgcolor+';'
        }

// 2006.11.23 MOM 枠線描画を追加
// 2007.03.25 安達宏行 選択不能日付セルの枠線の色を追加
        if ( this.draw_border ){
   // 当月かつ今日日付
            if ( mon == mm && utc == realutc ){
                if(!this.selectable_days[dd.getDay()] || (!this.selectable_holiday && getholiday != "" ) || (!this.selectable_kyuzitsu && kyuzitsu != "")){
                    cc += "border:solid 1px "+this.style.unselectable_day_border_color+";";// 選択不能日付
                } else {
                    cc += "border:solid 1px "+this.style.today_border_color+";";  // その他
                } 
   // その他                   
            } else {
                cc += "border:solid 1px "+this.style.others_border_color+";"; 
            }
        }

        var ss = this.getDateYMD(dd);
        var tt = dd.getDate();

// 2007.03.12 安達宏行 祝日名をタイトルに追加
// 2007.03.25 安達宏行 休日名をタイトルに追加
        if (getholiday != ""){
            var Whatday = "「"+getholiday+"」";
        }else if (kyuzitsu != ""){
            Whatday = "「"+kyuzitsu+"」";
        }else {
            Whatday = "";
        }

        src1 += '<td style="'+cc+'" title="'+ss+''+Whatday+'" id="__'+this.eid+'_td_'+ss+'">'+tt+'</td>';

        if ( ww == (this.start_day+6)%7 ) {
            src1 += "</tr>\n";                                  // 表示開始曜日の１つ手前で行末
        }
/*
        if ( ww == 7 ) {
            src1 += "</tr>\n";                                  // 土曜日の後に行末
        }
*/
    }
    src1 += "</table>\n";

    src1 += '</div>\n';

    // カレンダーを書き換える
    var cal1 = this.getCalendarElement();
    if ( ! cal1 ) return;
    cal1.style.width = this.style.frame_width;
    cal1.style.position = "absolute";

// 2007.03.25 安達宏行 joaoさんの固定表示を利用
    if( fix ){cal1.style.position = ""}
    else{cal1.style.position = "absolute"}

    cal1.innerHTML = src1;


// 2006.11.23 MOM 邪魔な<select>への応急処置その２
// カレンダーと全く同じサイズのIFRAMEを生成し、座標を一致させて下位レイヤに描画する

// IFRAME対応が可能なバージョンのみ処置を施す
    var ua = navigator.userAgent;
// 2007.03.25 安達宏行 joaoさんの固定表示を利用
    if( ! fix && (ua.indexOf("MSIE 5.5") >= 0 || ua.indexOf("MSIE 6") >= 0 )){

// 2006.11.27 MOM 先にinnerHTMLにカレンダーの実体を渡しておいて、描画フィールドの高さを取得する
// ※hide()が呼ばれた直後だと、offsetHeightが0になってしまうので、一時的にshowを呼ぶ
        this.show();
        var screenHeight = cal1.document.getElementById(this.eid+"_screen").offsetHeight;
        this.hide();

        src1 += '<div style="position:absolute;z-index:'+this.zindex+';top:0px;left:0px;">';
        src1 += '<iframe src="dummy.html" frameborder=0 scrolling=no width='+this.style.frame_width+' height='+screenHeight+'></iframe>';
        src1 += '</div>\n';


//改めてinnerHTMLにセット
        cal1.innerHTML = src1;
    }


    // イベントを登録する
    var __this = this;
    var get_src = function (ev) {
        ev  = ev || window.event;
        var src = ev.target || ev.srcElement;
        return src;
    };
    var month_onmouseover = function (ev) {
        var src = get_src(ev);
        src.style.color = __this.style.month_hover_color;
        src.style.background = __this.style.month_hover_bgcolor;
    };
    var month_onmouseout = function (ev) {
        var src = get_src(ev);
        src.style.color = __this.style.month_color;
        src.style.background = __this.style.frame_color;
    };
    var day_onmouseover = function (ev) {
        var src = get_src(ev);
        src.style.background = __this.style.day_hover_bgcolor;
    };
    var day_onmouseout = function (ev) {
        var src = get_src(ev);
// 2006.11.30 MOM 当月かつ今日日付であれば、今日日付用の背景色を適用
        var today = new Date();
        if( today.getMonth() == __this.date.getMonth() && src.id == '__'+__this.eid+'_td_'+__this.getDateYMD(today) ){
            src.style.background = __this.style.today_bgcolor;
        }else{
            src.style.background = __this.style.day_bgcolor;
        }
    };
    var day_onclick = function (ev) {
        var src = get_src(ev);
        var srcday = src.id.substr(src.id.length-10);
        __this.setFormValue( srcday );
// 2007.03.25 安達宏行 joaoさんの固定表示を利用
        __this.fadeOut( 1.0,fix );
    };

//
// 2006.12.04 ksuzu 選択できない月へのリンクは作成しない
//
    // 前の月へボタン
    var tdprev = document.getElementById( "__"+this.eid+"_btn_prev" );
    //前の月の最終日
    var tmpDate = new Date(year,mon,1);
    tmpDate.setTime( tmpDate.getTime() - (24*3600*1000) );
    //選択可能な日がある？
    if ( !min || this.min_date <= tmpDate ){
        tdprev.style.cursor = this.style.cursor;
        this.addEvent( tdprev, "mouseover", month_onmouseover );
        this.addEvent( tdprev, "mouseout", month_onmouseout );
// 2007.03.25 安達宏行 joaoさんの固定表示を利用
        this.addEvent( tdprev, "click", function(){ __this.moveMonth( -1,fix ); });
    }
    //選択不可能
    else{
        tdprev.title = "前の月は選択できません";
    }
/*
    tdprev.style.cursor = this.style.cursor;
    this.addEvent( tdprev, "mouseover", month_onmouseover );
    this.addEvent( tdprev, "mouseout", month_onmouseout );
//  2007.03.25 安達宏行 joaoさんの固定表示を利用
    this.addEvent( tdprev, "click", function(){ __this.moveMonth( -1,fix ); });
2006.12.04 ksuzu */

//
// 2006.12.04 ksuzu 表示月をクリックすると現在の月に移動
//
    var nMov = (realdd.getFullYear() - year) * 12 + (realdd.getMonth() - mon);
    if ( nMov != 0 ){
        // 現在の月へボタン
        var tdtoday = document.getElementById( "__"+this.eid+"_btn_today" );
        tdtoday.style.cursor = this.style.cursor;
        tdtoday.title = "現在の月へ";
        this.addEvent( tdtoday, "mouseover", month_onmouseover );
        this.addEvent( tdtoday, "mouseout", month_onmouseout )
// 2007.03.25 安達宏行 joaoさんの固定表示を利用
        this.addEvent( tdtoday, "click", function(){ __this.moveMonth( nMov,fix ); });
    }

    // 閉じるボタン
    var tdclose = document.getElementById( "__"+this.eid+"_btn_close" );
    tdclose.style.cursor = this.style.cursor;
    this.addEvent( tdclose, "mouseover", month_onmouseover );
    this.addEvent( tdclose, "mouseout", month_onmouseout );

//
// 2006.12.04 ksuzu カレンダーの初期表示を戻す
//
    this.addEvent( tdclose, "click", function(){ __this.getFormValue(); __this.hide(); });
//    this.addEvent( tdclose, "click", function(){ __this.hide(); });

//
// 2006.12.04 ksuzu 選択できない月へのリンクは作成しない
//
    // 次の月へボタン
    var tdnext = document.getElementById( "__"+this.eid+"_btn_next" );
    //次の月の初日
    var tmpDate = new Date(year,mon,1);
    tmpDate.setTime( tmpDate.getTime() + (24*3600*1000)*32 );
    tmpDate.setDate(1);
    //選択可能な日がある？
    if ( !max || this.max_date >= tmpDate ){
        tdnext.style.cursor = this.style.cursor;
        this.addEvent( tdnext, "mouseover", month_onmouseover );
        this.addEvent( tdnext, "mouseout", month_onmouseout );
// 2007.03.25 安達宏行 joaoさんの固定表示を利用
        this.addEvent( tdnext, "click", function(){ __this.moveMonth( +1,fix ); });
    }
    //選択不可能
    else{
        tdnext.title = "次の月は選択できません";
    }
/*
    tdnext.style.cursor = this.style.cursor;
    this.addEvent( tdnext, "mouseover", month_onmouseover );
    this.addEvent( tdnext, "mouseout", month_onmouseout );
// 2007.03.25 安達宏行 joaoさんの固定表示を利用
    this.addEvent( tdnext, "click", function(){ __this.moveMonth( +1,fix ); });
2006.12.04 ksuzu */

    // セルごとのイベントを登録する
    for ( var i=0; i<list.length; i++ ) {
        var dd = list[i];
        if ( mon != dd.getMonth() ) continue;       // 今月のセルにのみ設定する

        var utc = dd.getTime();
// 2007.03.25 安達宏行 joaoさんの選択可能日指定を利用
	var tt = dd.getDate();

        if ( min && min > utc ) continue;           // 昔過ぎる
        if ( max && max < utc ) continue;           // 未来過ぎる
// 2007.04.03 安達宏行 固定表示に対応
        if ( ! fix ) {if ( utc == curutc ) continue;}   // フォーム上の当日
// 2006.11.30 MOM 選択可能な曜日指定対応
// 2007.03.25 安達宏行 joaoさんの選択可能日指定を利用
// 2007.04.03 安達宏行 休日判定関数化に伴い一部修正
        var ss = this.getDateYMD(dd);
        if (!this.selectable_days[dd.getDay()] || (!this.selectable_holiday && ktHolidayName(ss) != "") || (!this.selectable_kyuzitsu && this.getKyuzitsu(ss) != "")){continue}

        var cc = document.getElementById( "__"+this.eid+"_td_"+ss );
        if ( ! cc ) continue;

        cc.style.cursor = this.style.cursor;
        this.addEvent( cc, "mouseover", day_onmouseover );
        this.addEvent( cc, "mouseout", day_onmouseout );
        this.addEvent( cc, "click", day_onclick );
    }

    // 表示する
    this.show();
};

// 旧バージョン互換（typo）
JKL.Calendar.prototype.getCalenderElement = JKL.Calendar.prototype.getCalendarElement;
JKL.Calender = JKL.Calendar;
