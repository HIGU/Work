////////////////////////////////////////////////////////////////////////////////
// 総合届（申請）                                                             //
//                                            MVC View 部 (JavaScriptクラス)  //
// Copyright (C) 2020-2020 Ryota.waki ryota_waki@nitto-kohki.co.jp            //
// Changed history                                                            //
// 2020/11/18 Created sougou.js                                               //
// 2021/02/12 Release.                                                        //
////////////////////////////////////////////////////////////////////////////////
// alert("TEST : ");

// 指定nameテキストボックスの使用可否 true：値クリア使用不可、false：使用可能
function NameTextReset( name, flag )
{
    if( flag ) {
        document.getElementsByName(name)[0].value = "";
    }
    document.getElementsByName(name)[0].disabled = flag;
}

// 行先系のテキストボックス true = 初期化
function IkisakiText( flag )
{
    NameTextReset( "ikisaki", flag );
    NameTextReset( "todouhuken", flag );
    NameTextReset( "mokuteki", flag );
    NameTextReset( "setto1", flag );
    NameTextReset( "setto2", flag );
    NameTextReset( "doukou", flag );
}

// テキストボックスの初期化
function TextInit()
{
    IkisakiText(true);

    NameTextReset( "hurikae", true );
    NameTextReset( "tokubetu_sonota", true );
    NameTextReset( "syousai_sonota", true );
    NameTextReset( "tel_sonota", true );
    NameTextReset( "tel_no", true );
    NameTextReset( "bikoutext", true );
//    NameTextReset( "outai", true );
}

// 指定nameのラジオボタンのチェックを外す
function NameRadioReset(name)
{
    var obj = document.getElementsByName(name);

    for(var i=0; i<obj.length; i++) {
        obj[i].checked = false;
    }
}

// 指定nameのラジオボタンの使用可否
function NameRadioDisabl(name, flag)
{
    var obj = document.getElementsByName(name);
    for(var i=0; i<obj.length; i++) {
        obj[i].disabled = flag;
    }
}

// ボタン系の初期化
function ButtonInit(button_array, syokai)
{
    for( var idx = 0; idx < button_array.length; idx++ ) {
        var button = document.getElementsByName(button_array[idx]);
        NameRadioReset(button_array[idx]);
        if( !syokai && (button_array[idx] == "r1" || button_array[idx] == "c2") ) continue;
        NameRadioDisabl(button_array[idx], true);
    }
}

// 指定nameのドロップダウンリスト使用可否
function NameDdlistDisabl(name, flag)
{
    var obj = document.getElementsByName(name);

    for( var idx = 0; idx < obj.length; idx++ ) {
        obj[idx].disabled = flag;
    }
}

// 指定IDの文字色を グレー or 黒 に設定
function setDisableStyle(id, flag)
{
    var obj = document.getElementById(id);

    if( flag ) {
        obj.style.color = 'DarkGray';   //文字色をグレーにする
    } else {
        obj.style.color = 'black';  //文字色を黒にする
    }
}

function setDisableStyleRed(id, flag)
{
    var obj = document.getElementById(id);

    if( obj ) {
        if( flag ) {
            obj.style.color = 'DarkGray';   //文字色をグレーにする
        } else {
            obj.style.color = 'red';  //文字色を赤にする
        }
    }
}

// ページ読み込み時に毎回呼び出す初期処理
function Init()
{
    TextInit();

    var flag = false;

    var obj = document.getElementsByName('syainbangou');

    if( obj[0] ) { // 社員番号空白時
        flag = true;
        obj[0].focus();
        obj[0].select();
    } else {
        SetWorkTime();
//        setDisableStyle('id_renraku', true);
/*
        var obj_kei = document.getElementById('id_keiyaku');
        if( obj_kei ) {
            alert("契約社員のため、終了時間の自動セットができません。\n\n申請の際は、時間をよくご確認下さい。" );
        }
*/
    }

    var button_array = new Array("r1","r2","r3","r4","r5","r6","c2");
    ButtonInit(button_array, flag);

    setDisableStyle('1000' , true);
    setDisableStyle('2000' , true);
    setDisableStyle('2500' , true);
    setDisableStyleRed('2550' , true);
    setDisableStyle('3000' , true);

    var obj2 = document.getElementsByName('approval'); // 承認ルート取得
    setDisableStyle('idc2l' , flag);
    if( !obj[0] && obj2[0].value=='' ) { // 承認ルート登録されてないとき
        document.getElementsByName("bikoutext")[0].disabled = !flag;
        document.getElementsByName("c2")[0].disabled = !flag;
        setDisableStyle('idc2l' , !flag);
        document.getElementsByName("submit")[0].disabled = !flag;
    } else {
        document.getElementsByName("bikoutext")[0].disabled = flag;
        document.getElementsByName("c0")[0].checked = !flag;
        document.getElementsByName("submit")[0].disabled = flag;
        document.getElementsByName("cancel")[0].disabled = flag;
    }

    OneDay(!flag);
    telno();

    SinseiDate(); StartDateCopy(); EndDateCopy(); StartTimeCopy(); EndTimeCopy();
    NameRadioDisabl("r6", flag);

    if( obj[0] ) { // 社員番号空白時
        OneDay(flag);
    }
}

// 承認より修正の為、表示する場合
function AdmitEdit()
{
    OneDay(document.getElementsByName("c0")[0].checked);
    syousai();
    telno();
    StartDateCopy(); EndDateCopy(); StartTimeCopy(); EndTimeCopy();
}

function SougouUpdate()
{
    document.getElementsByName("sougou_update")[0].value = 'on';
}

// IDカード通し忘れ（出勤）選択時、開始時間＋備考 のチェック
function IsWorkStartTime()
{
    var start = 11 - document.getElementById("id_s_work").value;
    if( start == 8 ) {
        start = ('0'+start).slice(-2) + ":30";
    } else {
        start = ('0'+start).slice(-2) + ":15";
    }

    var str_time = document.getElementsByName("str_time")[0].value;
    if( str_time == start ) {
        document.getElementsByName("end_time")[0].value = "";
        return true;
    }

    if( document.getElementsByName('bikoutext')[0].value.match(/\S/g) ) return true;

    alert("指定した開始時間(" + str_time + ")は、\n\n就業開始時間(" + start + ")ではありません。\n\n備考へ理由を入力して下さい。");

    return false;
//alert("（出勤）" + document.getElementsByName("str_time")[0].value + " = " + start);
}

// IDカード通し忘れ（退勤）選択時、終了時間＋備考 のチェック
function IsWorkEndTime()
{
    var end = 12 + parseInt(document.getElementById("id_e_work").value, 10);
    if( end == 16 || end == 17 ) {
        end += ":15";
    } else {
        end += ":00";
    }

    var end_time = document.getElementsByName("end_time")[0].value;
    if( end_time == end ) {
        document.getElementsByName("str_time")[0].value = "";
        return true;
    }
/*
    var obj_kei = document.getElementById('id_keiyaku');
    if( obj_kei ) {
        end = "16:15";
        if( end_time == end ) return true;
        end += " or 17:15";
    }
*/
    if( document.getElementsByName('bikoutext')[0].value.match(/\S/g) ) return true;

    alert("指定した終了時間(" + end_time + ")は、\n\n就業終了時間(" + end + ")ではありません。\n\n備考へ理由を入力して下さい。");

    return false;
//alert("（退勤）" + document.getElementsByName("end_time")[0].value + " = " + end);
}

var def_sh = 0;
var def_sm = 0;
var def_eh = 0;
var def_em = 0;
function SetDefTime()
{
    def_sh = 11 - document.getElementById("id_s_work").value;

    if( def_sh == 8 ) {
        def_sm = 30;
    } else {
        def_sm = 15;
    }

    def_eh = 12 + parseInt(document.getElementById("id_e_work").value, 10);

    if( def_eh == 16 || def_eh == 17 ) {
        def_em = 15;
    } else {
        def_em = 0;
    }
//alert("TEST : " + def_sh + ":" + def_sm + " - " + def_eh + ":" + def_em);
}

function SetWorkTime()
{
//alert(document.getElementById("id_t_work").value);
//alert(document.getElementById("id_s_work").value);
    var start = 11 - document.getElementById("id_s_work").value;

    document.getElementById("id_shh")[start].selected = true;
    if( start == 8 ) {
        document.getElementById("id_smm")[6].selected = true;
    } else {
        document.getElementById("id_smm")[3].selected = true;
    }

//alert(document.getElementById("id_e_work").value);
    var end = 12 + parseInt(document.getElementById("id_e_work").value, 10);

    document.getElementById("id_ehh")[end].selected = true;
    if( end == 16 || end == 17 ) {
        document.getElementById("id_emm")[3].selected = true;
    } else {
        document.getElementById("id_emm")[0].selected = true;
    }

    StartTimeCopy();
    EndTimeCopy();
}

/* waki 2021.06.04 --------------------------------------------------------> */
// 残業時間のチェック
function IsOverTime()
{
    var year   = document.getElementById("id_syear").value;
    var month  = document.getElementById("id_smonth").value-1;
    var day    = document.getElementById("id_sday").value;
/**
    // 残業開始可能時間 ------------------------------------------------------>
    var def_h = def_eh;
    var def_m = def_em;
    if( def_h == 17 ) {
        def_m = 30;
    }
    var d_d = new Date(year, month, day, def_h, def_m, 00);
    // <-----------------------------------------------------------------------
/**/
    // 選択した残業開始時間 -------------------------------------------------->
    var hour   = document.getElementById("id_shh").value;
    var minute = document.getElementById("id_smm").value;

    var s_d = new Date(year, month, day, hour, minute, 00);
    // <-----------------------------------------------------------------------
//alert("TEST : 開始 : " + hour + ":" + minute);

/**
    if( d_d > s_d ) {
        alert("開始時間を 残業開始時間(" + def_h + ":" + def_m + ")\n\n終了時間は 残業終了時間 にして下さい。");
        document.getElementById("id_ehh")[def_eh].selected = true;
        if( def_eh == 16 ) {
            document.getElementById("id_emm")[3].selected = true;
        } else if( def_eh == 17 ) {
            document.getElementById("id_emm")[6].selected = true;
        } else {
            document.getElementById("id_emm")[0].selected = true;
        }
        EndTimeCopy();
        return false;
    }
/**
    // 残業終了可能時間 ------------------------------------------------------>
    if( def_h == 16 ) {
        def_m = 44;
    } else if( def_h == 17 ) {
        def_m = 59;
    }
    d_d = new Date(year, month, day, def_h, def_m, 00);
    // <-----------------------------------------------------------------------
/**/
    // 選択した残業終了時間 -------------------------------------------------->
    hour   = document.getElementById("id_ehh").value;
    minute = document.getElementById("id_emm").value;

    var e_d = new Date(year, month, day, hour, minute, 00);
    // <-----------------------------------------------------------------------
/**
    if( d_d >= e_d ) {
        alert("終了時間を 残業終了時間 にして下さい。");
        return false;
    }
/**/
    if( s_d >= e_d ) {
        alert("開始は、残業開始時間\n\n終了は、残業終了時間 にして下さい。");
        return false;
    }
//alert("TEST : 終了 : " + hour + ":" + minute);

//    setStartTimeDisable(false);
    return true;
}
/* <------------------------------------------------------------------------ */

// 計画の処理
function Iskeikaku()
{
//alert("TEST 中 : ");
    if( !document.getElementById("209").checked ) return true;

    var first_half  = 3;   // 上期( 4/1〜9/30)計画数：22期 3日
    var second_half = 3;   // 下期(10/1〜3/31)計画数：22期 3日
    var ki_total = first_half + second_half;
/**/
    var jisseki = Number(document.getElementById("id_k_jisseki").value);
    var yotei_1 = Number(document.getElementById("id_k_yotei_1").value);
    var yotei_2 = Number(document.getElementById("id_k_yotei_2").value);
//alert("TEST 中 : " + jisseki + " : " + yotei_1 + " : " + yotei_2);
/**/
    var yotei = (jisseki + yotei_1 + yotei_2);
//alert("TEST 中 : " + yotei + " : " + PeriodDays);

    // 有休取得実績が、上期の上限未満なら取得可能。
    if( first_half > (yotei + PeriodDays) ) return true;

    // 有休取得日
    var year  = document.getElementById("id_syear").value;
    var month = document.getElementById("id_smonth").value;

    var sin_date = (document.getElementsByName("sin_date")[0].value).substr(0,4);
    var sin_year = (document.getElementsByName("sin_date")[0].value).substr(0,4);
//alert("TEST 中 : " + sin_date + " : " + sin_year + " : " + year + " : " + month);
    if( year > sin_year && month > 3 ) return true; // 来期分はスルーする。

    if( (yotei % 1) == 0.5 ) yotei -= 0.5;

    var msg = "現在取得有休は、" + yotei + "日分です。(※予定含む)\n\n今回 " + PeriodDays + "日分取得しようとしていますが、\n\n";
    // 上期 or 下期
    if( month > 3 && month < 10) {
//alert("TEST 中 : 上期" + jisseki + " : " + yotei_1);
        // 上期に取得する際、上期計画有休の上限未満なら取得可能
        if( (first_half +1) <= (jisseki + yotei_1 + PeriodDays) ) {
            alert(msg + "*** 上期 *** 計画有休の上限(" + first_half + "日)を超えるため、\n\n計画有休での申請はできません。");
            document.getElementById("209").checked = false;
            return false;
        }
    } else {
//alert("TEST 中 : 下期" + jisseki + " : " + yotei_1 + " : " + yotei_2);
        // 下期に取得する際、年間計画有休の上限未満なら取得可能
        if( (ki_total +1) <= (jisseki + yotei_1 + yotei_2 + PeriodDays) ) {
            alert(msg + "*** 年間 *** 計画有休の上限(" + ki_total + "日)を超えるため、\n\n計画有休での申請はできません。");
            document.getElementById("209").checked = false;
            return false;
        }
    }
//alert("TEST 中" + jisseki);
    return true;
}

// 休暇系の処理
function kyuuka( flag, no )
{
    if( no==0 || no==1 || no==2 ) {
        SetWorkTime();
    }

    if( no==1 ) { // AM半日休暇の為、終了時刻強制セット
        document.getElementById("id_ehh")[12].selected = true;
        document.getElementById("id_emm")[0].selected = true;
        EndTimeCopy();
    }

    if( no==2 ) { // PM半日休暇の為、開始時刻強制セット
        document.getElementById("id_shh")[12].selected = true;
        document.getElementById("id_smm")[9].selected = true;
        StartTimeCopy();
    }

    NameRadioDisabl("r2", flag);

    setDisableStyle('1000', flag);
    if( flag ){
        NameRadioReset("r2");
    }

    if( no==0 ) { // 有給休暇時、計画有休ＯＮ
        document.getElementById('209').disabled = false;
        setDisableStyle('keikaku', false);
    } else {    // それ以外、計画有休ＯＦＦ
        document.getElementById('209').checked = false;
        document.getElementById('209').disabled = true;
        setDisableStyle('keikaku', true);
    }
    if( no==0 || no==1 || no==2 || no==3  ) { // 有休、半日、時間休なら、特別計画ＯＮ
        document.getElementById('210').disabled = false;
        setDisableStyle('tokukei', false);
    } else {    // それ以外、特別計画ＯＦＦ
        document.getElementById('210').checked = false;
        document.getElementById('210').disabled = true;
        setDisableStyle('tokukei', true);
    }
}

/***** 点滅表示メソッド *****/
/***** blink_flg Private property 下の例は0.5秒毎に点滅 *****/
/***** <body onLoad='setInterval("obj.blink_disp(\"caption\")", 500)'> *****/
var blink_flag = 1;
var blink_msg  = "";
function blink_disp(id_name)
{
    if( blink_flag == 1 ) {
        // 初期値をプロパティで指定したため以下をコメント
        // this.blink_msg = document.getElementById(id_name).innerHTML;
        blink_msg = document.getElementById(id_name).innerHTML;
        document.getElementById(id_name).innerHTML = "&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;";    // [00/00] 更新
        blink_flag = 2;
    } else {
        document.getElementById(id_name).innerHTML = blink_msg;
        blink_flag = 1;
    }
}

// ワクチン接種
function vaccine()
{
    if( document.getElementsByName("r1")[11].disabled ) return;

    document.getElementsByName("r1")[11].checked=true;
    syousai();
    if( document.getElementById("506").disabled ) return;
    
    document.getElementsByName("tokubetu_sonota")[0].value="ワクチン接種";
}

// 回数券使用不可の対応
// suica の処理
function suica()
{
    var r3 = document.getElementsByName("r3");

    if( !r3[0].checked && r3[1].checked ) {
        document.getElementsByName('setto1')[0].value=1;
    } else {
        r3[0].checked = true;
        document.getElementsByName('setto1')[0].value="";
    }
}

// 回数券の処理
function setto()
{
    var r3 = document.getElementsByName("r3");
    if( !r3[0].value ) return;
    var r4 = document.getElementsByName("r4");
    if( !r4[0].value ) return;

    if( r3[1].checked || r3[2].checked ) {
        NameTextReset("setto1", false);
    } else {
        r3[0].checked = true;
        NameTextReset("setto1", true);
    }

    if( r4[1].checked || r4[2].checked ) {
        NameTextReset("setto2", false);
    } else {
        r4[0].checked = true;
        NameTextReset("setto2", true);
    }
}

// 行先系の処理
function ikisaki( flag, no )
{
    IkisakiText(flag);

    NameRadioDisabl("r3", flag);
    NameRadioDisabl("r4", flag);

    setDisableStyle('2000', flag);
    setDisableStyle('2500', flag);
    setDisableStyleRed('2550', flag);
    var obj = document.getElementById('2550');
    if( obj ) {
        if( !flag ) {
            obj.setAttribute("href", "../in_account_appli/download_file.php/切符購入依頼書(原).xls");   // href属性を付ける
        } else {
            obj.removeAttribute("href");    // href属性をなくす
        }
    }

    if( no == 8 || no == 9 ) {  // 直行 または、直帰
        setDisable( 'id_time_area', !flag );
        setDisable( 'id_time_sum_area', !flag );
        if( no == 8 ) {
            setEndTimeDisable(!flag);    // 直行 終了時間使用不可
        } else if( no == 9 ) {
            setStartTimeDisable(!flag);  // 直帰 開始時間使用不可
        }
    }

    if( flag ){
        NameRadioReset("r3");
        NameRadioReset("r4");
    }

    if( document.getElementById('id_suica') ) {
        suica();
    } else {
        setto();
    }

    if( flag ){
        NameRadioReset("r3");
        NameRadioReset("r4");
    }
}

// 特別休暇内のその他の処理
function toku()
{
    var flag = true;
    var r5 = document.getElementsByName("r5");
    if( r5[(r5.length-1)].checked ){
        flag = false;
    }
    NameTextReset("tokubetu_sonota", flag);
}

// 特別休暇の処理
function tokubetu( flag )
{
    NameRadioDisabl("r5", flag);
    setDisableStyle('3000', flag);

    if( flag ){
        NameRadioReset("r5");
    }

    toku();
}

// 振替休日の処理
function hurikae( flag )
{
    NameTextReset("hurikae", flag);
}

// 指定IDの使用可否
function setDisable( id, flag )
{
    document.getElementById(id).disabled = flag;
    setDisableStyle(id, flag);
}

// 期間：開始時間の使用可否
function setStartTimeDisable( flag )
{
    setDisable( 'id_start_time_area', flag );
    setDisable( 'id_shh', flag );
    setDisable( 'id_smm', flag );
    setDisable( '001', flag );
    setDisable( '002', flag );
    setDisable( 'id_sum_hour', flag );
    setDisable( 'id_sum', flag );
}

// 期間：終了時間の使用可否
function setEndTimeDisable( flag )
{
    setDisable( 'id_end_time_area', flag );
    setDisable( 'id_ehh', flag );
    setDisable( 'id_emm', flag );
    setDisable( '001', flag );
    setDisable( '002', flag );
    setDisable( 'id_sum_hour', flag );
    setDisable( 'id_sum', flag );
}

// 連絡先の処理 + 14.ID出勤 15.ID退勤 16.時限 18.ID退勤＋時限
function renraku( flag , no )
{
    NameRadioDisabl("r6", flag);
    setDisableStyle('id_renraku', flag);

    if( flag ){
        NameRadioReset("r6");
        if( no == 14 || no == 15 ) {    // ID忘れ(出勤) または、ID忘れ(退勤)
            setDisable( 'id_time_area', flag );
            setDisable( 'id_time_sum_area', flag );
            if( no == 14 ) {
                setEndTimeDisable(flag);    // ID忘れ(出勤) 終了時間使用不可
            } else if( no == 15 ) {
                setStartTimeDisable(flag);  // ID忘れ(退勤) 開始時間使用不可
            }
        } else {    // 時限承認忘れ（残業申告漏れ）関連の時は、残業開始〜終了時間を選択する
/* waki 2021.06.04 --------------------------------------------------------> */
            // 残業開始時間セット
            document.getElementById("id_shh")[def_eh].selected = true;
            if( def_eh == 16 ) {
                document.getElementById("id_smm")[3].selected = true;
            } else if( def_eh == 17 ) {
                document.getElementById("id_smm")[6].selected = true;
            } else {
                document.getElementById("id_smm")[0].selected = true;
            }
            StartTimeCopy();
/**
            // 残業終了時間セット
            document.getElementById("id_ehh")[def_eh].selected = true;
            if( def_eh == 16 ) {
                document.getElementById("id_emm")[3].selected = true;
            } else if( def_eh == 17 ) {
                document.getElementById("id_emm")[6].selected = true;
            } else {
                document.getElementById("id_emm")[0].selected = true;
            }
            EndTimeCopy();
/* <------------------------------------------------------------------------ */
        }
    }

    telno();
}

// その他の処理
function sonota( flag )
{
    NameTextReset("syousai_sonota", flag);
}

// ラジオボタン選択時の処理
function syousai()
{
    var kyu = iki = tok = hur = son = true;
    var ren = false;
    var r1 = document.getElementsByName("r1");

    // 開始・終了時間エリアを使用可能にする
    setStartTimeDisable(false);
    setEndTimeDisable(false);
    setDisable( 'id_time_area', false );
    setDisable( 'id_time_sum_area', false );

    for( var i=0; i<r1.length; i++ ) {
        if( r1[i].checked ) {
            document.getElementById('id_content_no').value = i;
            AfterReport();

            if( i>=0 && i<6 ) { // 休暇系
                kyu = false;
                break;
            }
            if( i>=6 && i<11 ) { // 行先系
                iki = false;
                break;
            }
            if( i == 11 ) { // 特別休暇
                tok = false;
                break;
            }
            if( i == 12 ) { // 振替休日
                hur = false;
                break;
            }
            if( i>=14 && i<18 ) { // ID通し、時限承認忘れ時
                ren = true;
                break;
            }
            if( i == 18 ) { // その他
                son = false;
                break;
            }
        }
    }

    kyuuka(kyu, i);
    ikisaki(iki, i);
    tokubetu(tok);
    hurikae(hur);
    renraku(ren, i);
    sonota(son);
}

// TELの処理
function telno()
{
    var r6 = document.getElementsByName("r6");
    if( r6[(r6.length-2)].checked || r6[(r6.length-1)].checked ){
        setDisableStyle('id_tel_no', false);
        NameTextReset("tel_no", false);
    } else {
        setDisableStyle('id_tel_no', true);
        NameTextReset("tel_no", true);
    }

    if( r6[(r6.length-1)].checked ){
        NameTextReset("tel_sonota", false);
    } else {
        NameTextReset("tel_sonota", true);
    }
}

// 受電者の処理
function jyudensya( ischeck )
{
    if( ischeck == false ) {
        document.getElementsByName("jyu_date")[0].value = "";
    } else {
        JyuDateCopy();
    }

    setDisableStyle('id_jyuden', !ischeck);
    NameTextReset("outai", !ischeck);
    NameDdlistDisabl("ddlist_jyu", !ischeck);
}


// 社員番号入力チェック
function check(){
    var str1=document.sinseisya.syainbangou.value;

    if(isDigit(str1)) {
//        alert("数値");
        return str1;
    }else{
//        alert("文字"+str);
        return getDigit(str1);
    }
}

function isDigit(str) {
    var len = str.length;
    var c;
    for (i=0; i<len; i++){
        c = str.charAt(i);
        if(("0" > c) || ("9" < c)) {
            return false;
        }
    }
    return true;
}

function getDigit(str) {
    var len = str.length;
    var c, str1="";
    for (i=0; i<len; i++){
        c = str.charAt(i);
        if(("0" > c) || ("9" < c)) {
            continue;
        }
        str1 += c;
    }
    return str1;
}

// 現在の日付取得
function sinseibi()
{
    var hiduke=new Date(); 

    var year = hiduke.getFullYear();
    var month = hiduke.getMonth()+1;
    var week = hiduke.getDay();
    var day = hiduke.getDate();

    var yobi= new Array("日","月","火","水","木","金","土");

    document.write(year+"年"+month+"月"+day+"日 "+yobi[week]+"曜日");

    var hour = hiduke.getHours();
    var minute = hiduke.getMinutes();
    var second = hiduke.getSeconds();

/*
    document.write("    "+hour+"時"+minute+"分"+second+"秒");
    document.write("    "+hour+"時"+minute+"分");
*/
}

// 指定nameのラジオボタンにチェックがあるか
function IsRadioSelect(name)
{
    var obj = document.getElementsByName(name);

    if( !obj ) return;

    for( var i=0; i<obj.length; i++ ) {
        if( obj[i].checked ) {
            return true;
        }
    }
    return false;
}

// 受電者欄の使用可否設定
function AfterReport()
{
    if(  document.getElementById('id_jyear') == null ) {
        return; // 受電者欄の要素ないならリターンする。
    }

    var no = document.getElementById('id_content_no').value;
/**/
    if( no>13 && no<18 ) {  // ID通し、時限承認忘れ時
        jyudensya(false);   // 受電者入力不要
        return;
    }
/**/
    var sin = new Date(document.getElementById("sin_year").value, document.getElementById("sin_month").value-1, document.getElementById("sin_day").value, document.getElementById("sin_hour").value, document.getElementById("sin_minute").value);
    var s_d = new Date(document.getElementById("id_syear").value, document.getElementById("id_smonth").value-1, document.getElementById("id_sday").value, document.getElementById("id_shh").value, document.getElementById("id_smm").value);
    var e_d = new Date(document.getElementById("id_eyear").value, document.getElementById("id_emonth").value-1, document.getElementById("id_eday").value, document.getElementById("id_ehh").value, document.getElementById("id_emm").value);

//alert( '申請日：' + sin.toLocaleDateString() + ' ' + sin.toLocaleTimeString() + '\n期    間：' + e_d.toLocaleDateString() + ' ' + e_d.toLocaleTimeString() +  "\n\n事後報告" );
    if( sin >= s_d && sin >= e_d ) {    // 2021.09.27
//        alert( '申請日：' + sin.toLocaleDateString() + ' ' + sin.toLocaleTimeString() + '\n期    間：' + s_d.toLocaleDateString() + ' ' + s_d.toLocaleTimeString() +  "\n\n事後報告" );
        jyudensya(true);
        return;
    }

    jyudensya(false);
    return;
}

// AM半日有給休暇 + 12:45〜の時間単位有給休暇の申請状況チェック。
function IsAMandTimeVacation()
{
    var obj = document.getElementsByName('r1');

    var indx = document.getElementsByName('indx')[0].value;
    var rows = document.getElementsByName('rows')[0].value;
    var res = new Array();

    var str = document.getElementsByName("str_date")[0].value;
    var checkday = str.substr(0, 4) + '-' + str.substr(4, 2) + '-' + str.substr(6, 2)
    var am_flag = time_flag = true;

    // 申請情報を復元 内容[0] 開始日[1] 終了日[2]
    for( var r=0; r<rows; r++ ) {
        var posname = "res-" + r + "[]";
        var res = document.getElementsByName(posname);

        for( var i=1; i<indx; i++ ) {
            if( checkday == res[i].value) {
                if( res[0].value == 'AM半日有給休暇' ) {
                    // alert(checkday + " は、既にAM半日有給休暇を申請している。");
                    time_flag = false;  // 12：45〜 の時間単位有給休暇 申請不可
                } else if( res[0].value == '時間単位有給休暇' ) {
                    // alert(checkday + " は、既に12：45〜 の時間単位有給休暇を申請している。");
                    am_flag = false;    // AM半日有給休暇 申請不可
                }
            }
            if( res[i].value == res[i+1].value) break;
        }
    }

    // 内容が AM半日有給休暇 or 時間単位有給休暇 選択時、既に申請されてないかチェック
    for( var i=0; i<obj.length; i++ ) {
        if( !obj[i].checked ) continue;

        if( obj[i].value == 'AM半日有給休暇' && !am_flag) {
            alert("指定された日は、既に12:45〜 の時間単位有給休暇が申請されています。\n\nAM半日有給休暇 ＋ 12:45〜 の時間単位有給休暇は取得できません。\n\nその申請を行うには、AMを含め全てを時間単位有給休暇で申請を行い、\n既に申請してあるAM半日有給休暇の取消を依頼して下さい。");
            return false;
        } else if( obj[i].value == '時間単位有給休暇' ) {
            if( document.getElementsByName("str_time")[0].value == '12:45' && !time_flag ) {
                alert("指定された日は、既にAM半日有給休暇が申請されています。\n\nAM半日有給休暇 ＋ 12:45〜 の時間単位有給休暇は取得できません。\n\nその申請を行うには、AMを含め全てを時間単位有給休暇で申請を行い、\n既に申請してあるAM半日有給休暇の取消を依頼して下さい。");
                return false;
            }
        }
        break;
    }

    return true;
}

function SpecialText(obj)
{
    if( obj.value.match(/��/) ) {
        alert("環境依存文字 �� が使用されています。\n\n半角カッコ ＋ 株 ＋ 半角カッコ ＝ (株)\n\n全角カッコ ＋ 株 ＋ 全角カッコ ＝ （株）\n\nに直してください。");
        obj.focus();
        obj.select();
    }
    return obj.value;
}

// 期間項目のチェック
function PeriodCheck()
{
    var sd = new Date(document.getElementById("id_syear").value, document.getElementById("id_smonth").value-1, document.getElementById("id_sday").value);
    var ed = new Date(document.getElementById("id_eyear").value, document.getElementById("id_emonth").value-1, document.getElementById("id_eday").value);
    if( sd > ed ) {
        alert(sd.toLocaleDateString() + '〜' + ed.toLocaleDateString() + "\n\n指定された期間に誤りがあります。");
        return false;
    }

    var sd = new Date(document.getElementById("id_syear").value, document.getElementById("id_smonth").value-1, document.getElementById("id_sday").value, document.getElementById("id_shh").value, document.getElementById("id_smm").value);
    var ed = new Date(document.getElementById("id_syear").value, document.getElementById("id_smonth").value-1, document.getElementById("id_sday").value, document.getElementById("id_ehh").value, document.getElementById("id_emm").value);
    if( sd > ed ) {
        alert(sd.getHours() + '時' + sd.getMinutes() + '分' + '〜' + ed.getHours() + '時' + ed.getMinutes() + '分' + "\n\n指定された時刻に誤りがあります。");
        return false;
    }

    return true;
}

// 備考項目のチェック
function BikouCheck()
{
    if( document.getElementsByName("r2")[7].checked ) {// 冠婚葬祭
        if( !document.getElementsByName('bikoutext')[0].value.match(/\S/g) ) {
            alert("備考 が入力されていません。\n\n例）組内葬儀のため、友人通夜参列のため、など");
            return false;
        }
    }

    return true;
}

// 連絡先項目のチェック
function ContactCheck()
{
    var r6 = document.getElementsByName("r6");
    for( var i=0; i<r6.length; i++ ) {
        if( r6[i].checked ) {
            switch (i) {
                case  0:
                case  1:
                    break;
                case  3:
                    if( !document.getElementsByName('tel_sonota')[0].value.match(/\S/g) ) {
                        alert("連絡先 その他 が入力されていません。");
                        return false;
                    }
                case  2:
                    if( !document.getElementsByName('tel_no')[0].value.match(/\S/g) ) {
                        alert("連絡先 TEL が入力されていません。");
                        return false;
                    }
                    break;
                default:
                    break;
            }
            break;
        }
    }

    if( i == r6.length ) {
        alert("連絡先が選択されていません。");
        return false;
    }

    return true;
}

// 受電者項目のチェック
function JyudensyaCheck()
{
    if(  document.getElementById('id_jyear') == null ) {
        return true; // 受電者欄の要素ないならリターンする。
    }

    if( document.getElementsByName("outai")[0].disabled ) return true;
/*
    if( document.getElementsByName("outai")[0].value == '' ) {
        alert("応対者が入力されていません。");
        return false;
    }
*/
    return true;
}

// 時間の確認（開始or終了時刻が、お昼or休憩時間になっていないか）
function TimeCheck( t_str, t_end )
{
    var t1200 = new Date(document.getElementById("id_syear").value, document.getElementById("id_smonth").value-1, document.getElementById("id_sday").value, 12, 00);
    var t1245 = new Date(document.getElementById("id_syear").value, document.getElementById("id_smonth").value-1, document.getElementById("id_sday").value, 12, 45);
    var t1500 = new Date(document.getElementById("id_syear").value, document.getElementById("id_smonth").value-1, document.getElementById("id_sday").value, 15, 00);
    var t1510 = new Date(document.getElementById("id_syear").value, document.getElementById("id_smonth").value-1, document.getElementById("id_sday").value, 15, 10);
//alert(t_str.toLocaleTimeString() + ' ***** ' + t_end.toLocaleTimeString());

    if( t1200 <= t_str && t1245 > t_str ) {
        return '開始時刻が、お昼休憩時間(12:00〜12:45)になっています。\n\n変更して下さい。';
    } else if( t1200 < t_end && t1245 >= t_end ) {
        return '終了時刻が、お昼休憩時間(12:00〜12:45)になっています。\n\n変更して下さい。';
    } else if( t1500 <= t_str && t1510 > t_str ) {
        return '開始時刻が、休憩時間(15:00〜15:10)になっています。\n\n変更して下さい。';
    } else if( t1500 < t_end && t1510 >= t_end ) {
        return '終了時刻が、休憩時間(15:00〜15:10)になっています。\n\n変更して下さい。';
    }
/**
    if( (t1200 > t_str && t1200 >= t_end) ) {
        alert('お昼前 時間関係なし');
    }
    if( (t1245 <= t_str && t1500 >= t_end) ) {
        alert('お昼後、休憩前 時間関係なし');
    }
    if( (t1510 <= t_str && t1510 < t_end) ) {
        alert('休憩後 時間関係なし');
    }
/**/
    var ttime = new Date();
    ttime.setHours(t_end.getHours() - t_str.getHours());
    ttime.setMinutes(t_end.getMinutes() - t_str.getMinutes());
    ttime.setSeconds(0);

    if( (t1200 > t_str && t1245 <= t_end) ) {
//        alert('お昼 時間 またぐ為調整必要');
        ttime.setMinutes(ttime.getMinutes() - t1245.getMinutes());
    }
    if( (t1500 > t_str && t1510 <= t_end) ) {
//        alert('休憩 時間 またぐ為調整必要');
        ttime.setMinutes(ttime.getMinutes() - t1510.getMinutes());
    }
    var msg ="";
    if( ttime.getHours() <= 0 || ttime.getMinutes() != 0 ) {
        msg = '時間単位になっていません。( ' + ttime.getHours() + '時間' + ('0'+ttime.getMinutes()).slice(-2)  + '分 )\n\n';
        msg += '以下の時間は除外されます。\n 12:00〜12:45 お昼休憩時間(45分)\n 15:00〜15:10 休憩時間(10分) \n\n再度、時刻を確認して下さい。';
    }
//alert('使用時間：' +  ttime.toLocaleTimeString());
    return msg;
}

// 送信前 全項目の最終チェック
function allcheck()
{
    if( !PeriodCheck() ) return false;

    var r1 = document.getElementsByName("r1");
    var flag = false;
    var msg = "";
/* 内容部チェック */
    for( var i=0; i<r1.length; i++ ) {
        if( r1[i].checked ) {
            switch (i) {
                case  0: // 有給休暇
/**/
//                    if( i == 0 && (document.getElementsByName("str_time")[0].value == '08:30' || document.getElementsByName("str_time")[0].value == '09:15')
//                               && (document.getElementsByName("end_time")[0].value == '16:15' || document.getElementsByName("end_time")[0].value == '17:15' || document.getElementsByName("end_time")[0].value == '18:00')) {
                    if( i == 0 &&
                         ( (document.getElementsByName("str_time")[0].value == '08:30' && document.getElementsByName("end_time")[0].value == '16:15')
                        || (document.getElementsByName("str_time")[0].value == '08:30' && document.getElementsByName("end_time")[0].value == '17:15')
                        || (document.getElementsByName("str_time")[0].value == '09:15' && document.getElementsByName("end_time")[0].value == '18:00') )
                    ) {
                       ;
                    } else {
                        msg = '有給休暇を選択していますが\n\n時間 ' + document.getElementsByName("str_time")[0].value + ' 〜 ' + document.getElementsByName("end_time")[0].value + ' は\n\n正しくありません!!';
                        break;
                    }
/**/
                case  1: // AM半日有給休暇
                    if( i == 1 && document.getElementsByName("end_time")[0].value != '12:00' ) {
                        msg = 'AM半日有給休暇 を選択しているので、\n\n終了時刻は 12：00 に変更して下さい。';
                        break;
                    }
                case  2: // PM半日有給休暇
                    if( i == 2 && document.getElementsByName("str_time")[0].value != '12:45' ) {
                        msg = 'PM半日有給休暇 を選択しているので、\n\n開始時刻は 12：45 に変更して下さい。';
                        break;
                    }
                case  3: // 時間単位有給休暇
                    if( i == 3 ) {
                        var t_str = new Date(document.getElementById("id_syear").value, document.getElementById("id_smonth").value-1, document.getElementById("id_sday").value, document.getElementById("id_shh").value, document.getElementById("id_smm").value);
                        var t_end = new Date(document.getElementById("id_syear").value, document.getElementById("id_smonth").value-1, document.getElementById("id_sday").value, document.getElementById("id_ehh").value, document.getElementById("id_emm").value);
                        msg = TimeCheck( t_str, t_end );
                        if( msg ) break;
                    }
                case  4: // 欠勤
                case  5: // 遅刻早退
//                    if( IsHoliday(document.getElementsByName("str_date")[0].value) || IsHoliday(document.getElementsByName("end_date")[0].value) ) {
//                        msg = "指定された期間（開始日 or 終了日）が 休日（会社カレンダー）です。\nそのため、「" +r1[i].value+"」は申請できません。\n\n期間（開始日 or 終了日） or 内容 を変更して下さい。";
                    if( IsHoliday(document.getElementsByName("str_date")[0].value) ) {
                        msg = "指定された期間（開始日）は 休日（会社カレンダー）です。\nそのため、「" +r1[i].value+"」は申請できません。\n\n期間（開始日） or 内容 を確認して下さい。";
                        if( document.getElementsByName("str_date")[0].value != document.getElementsByName("end_date")[0].value && IsHoliday(document.getElementsByName("end_date")[0].value) ) {
                            msg = "指定された期間（開始日 or 終了日）は 休日（会社カレンダー）です。\nそのため、「" +r1[i].value+"」は申請できません。\n\n期間（開始日 or 終了日） or 内容 を確認して下さい。";
                        }
                    } else if( IsHoliday(document.getElementsByName("end_date")[0].value) ) {
                        msg = "指定された期間（終了日）は 休日（会社カレンダー）です。\nそのため、「" +r1[i].value+"」は申請できません。\n\n期間（終了日） or 内容 を確認して下さい。";
                    } else {
                        flag = IsRadioSelect("r2");
                        msg = r1[i].value + " の理由が選択されていません。";
                    }
                    break;
                case  6: // 出張（日帰り）
                case  7: // 出張（宿泊）
                case  8: // 直行
                case  9: // 直帰
                case 10: // 直行/直帰
                    if( !document.getElementsByName('ikisaki')[0].value.match(/\S/g) ) {
                        msg = "行先が入力されていません。";
                        break;
                    }
                    if( document.getElementsByName('ikisaki')[0].value.match(/��/) ) {
                        msg = "環境依存文字 �� が使用されています。\n\n半角カッコ ＋ 株 ＋ 半角カッコ ＝ (株)\n\n全角カッコ ＋ 株 ＋ 全角カッコ ＝ （株）\n\nに直してください。";
                        break;
                    }
                    if( !document.getElementsByName('todouhuken')[0].value.match(/\S/g) ) {
                        msg = "都道府県が入力されていません。";
                        break;
                    }
                    if( !document.getElementsByName('mokuteki')[0].value.match(/\S/g) ) {
                        msg = "目的が入力されていません。";
                        break;
                    }
                    var r3 = document.getElementsByName("r3");
//                    if( !r3[0].checked ) {
                    if( !r3[0].checked && r3[0].value == "不要" ) {
                        if( !document.getElementsByName('setto1')[0].value.match(/\S/g) ) {
                            msg = "乗車券の必要セット数が入力されていません。";
                            break;
                        }
                    }
                    var r4 = document.getElementsByName("r4");
//                    if( !r4[0].checked ) {
                    if( !r4[0].checked && r4[0].value == "不要" ) {
                        if( !document.getElementsByName('setto2')[0].value.match(/\S/g) ) {
                            msg = "新幹線特急の必要セット数が入力されていません。";
                            break;
                        }
                    }
                    flag = true;
                    break;
                case 11: // 特別休暇
                    if( IsHoliday(document.getElementsByName("str_date")[0].value) ) {
                        msg = "指定された期間（開始日）は 休日（会社カレンダー）です。\nそのため、「" +r1[i].value+"」は申請できません。\n\n期間（開始日） or 内容 を確認して下さい。";
                        if( document.getElementsByName("str_date")[0].value != document.getElementsByName("end_date")[0].value && IsHoliday(document.getElementsByName("end_date")[0].value) ) {
                            msg = "指定された期間（開始日 or 終了日）は 休日（会社カレンダー）です。\nそのため、「" +r1[i].value+"」は申請できません。\n\n期間（開始日 or 終了日） or 内容 を確認して下さい。";
                        }
                    } else if( IsHoliday(document.getElementsByName("end_date")[0].value) ) {
                        msg = "指定された期間（終了日）は 休日（会社カレンダー）です。\nそのため、「" +r1[i].value+"」は申請できません。\n\n期間（終了日） or 内容 を確認して下さい。";
                    } else {
                        flag = IsRadioSelect("r5");
                        msg = r1[i].value + " の理由が入力されていません。";
                        var r5 = document.getElementsByName("r5");
                        if( r5[r5.length-1].checked ) {
                            if( !document.getElementsByName('tokubetu_sonota')[0].value.match(/\S/g) ) {
                                flag = false;
                                msg = r1[i].value + ' ' + r5[r5.length-1].value + " の理由が入力されていません。";
                            }
                        }
                    }
                    break;
                case 12: // 振替休日
                    if( IsHoliday(document.getElementsByName("str_date")[0].value) ) {
                        msg = "指定された期間（開始日）は 休日（会社カレンダー）です。\nそのため、「" +r1[i].value+"」は申請できません。\n\n期間（開始日） or 内容 を確認して下さい。";
                        if( document.getElementsByName("str_date")[0].value != document.getElementsByName("end_date")[0].value && IsHoliday(document.getElementsByName("end_date")[0].value) ) {
                            msg = "指定された期間（開始日 or 終了日）は 休日（会社カレンダー）です。\nそのため、「" +r1[i].value+"」は申請できません。\n\n期間（開始日 or 終了日） or 内容 を確認して下さい。";
                        }
                    } else if( IsHoliday(document.getElementsByName("end_date")[0].value) ) {
                        msg = "指定された期間（終了日）は 休日（会社カレンダー）です。\nそのため、「" +r1[i].value+"」は申請できません。\n\n期間（終了日） or 内容 を確認して下さい。";
                    } else {
                        if( document.getElementsByName('hurikae')[0].value.match(/\S/g) ) {
                            flag = true;
                        } else {
                            msg = r1[i].value + " いつ出勤分か入力されていません。";
                        }
                    }
                    break;
                case 13: // 生理休暇
                    if( IsHoliday(document.getElementsByName("str_date")[0].value) ) {
                        msg = "指定された期間（開始日）は 休日（会社カレンダー）です。\nそのため、「" +r1[i].value+"」は申請できません。\n\n期間（開始日） or 内容 を確認して下さい。";
                        if( document.getElementsByName("str_date")[0].value != document.getElementsByName("end_date")[0].value && IsHoliday(document.getElementsByName("end_date")[0].value) ) {
                            msg = "指定された期間（開始日 or 終了日）は 休日（会社カレンダー）です。\nそのため、「" +r1[i].value+"」は申請できません。\n\n期間（開始日 or 終了日） or 内容 を確認して下さい。";
                        }
                    } else if( IsHoliday(document.getElementsByName("end_date")[0].value) ) {
                        msg = "指定された期間（終了日）は 休日（会社カレンダー）です。\nそのため、「" +r1[i].value+"」は申請できません。\n\n期間（終了日） or 内容 を確認して下さい。";
                    } else {
                        flag = true;
                    }
                    break;
                case 18: // その他
                    if( document.getElementsByName('syousai_sonota')[0].value.match(/\S/g) ) {
                        flag = true;
                    } else {
                        msg = r1[i].value + " の理由が入力されていません。";
                    }
                    break;
                default: // それ以外（IDカード忘れ・時限承認忘れ etc）
                    flag = true;
                    break;
            }
            break;
        }
    }
    if( i == r1.length ) {
        msg = "内容が選択されていません。\n\n内容選択後、送信して下さい。";
    }
    if( !flag ) {
        alert(msg);
        return false;
    }

    if( i>=0 && i<3 ) {
        var s_t_d = ('0'+def_sh).slice(-2) + ':' + def_sm;
        var e_t_d = def_eh + ':' + ('0'+def_em).slice(-2);
        var s_t = document.getElementsByName("str_time")[0].value;
        var e_t = document.getElementsByName("end_time")[0].value;
        
        if( i == 0 ) {
            if( s_t_d != s_t || e_t_d != e_t ) {
                msg = '有給休暇を選択していますが、時間が\n\n' + s_t_d + ' 〜 ' + e_t_d + '\n\n↓↓↓↓↓↓\n\n' + s_t + ' 〜 ' + e_t + '\n\nに変更されています、よろしいですか？';
                if( ! confirm(msg) ) return false;
            }
        }
        if( i == 1 ) {
            if( s_t_d != s_t ) {
                msg = 'AM半日有給休暇を選択していますが、\n\n開始時間が ' + s_t_d + ' から ' + s_t + ' に変更されています。\n\nAM半日有給休暇で問題ありませんか？';
                if( ! confirm(msg) ) return false;
            }
        }
        if( i == 2 ) {
            if( e_t_d != e_t ) {
                msg = 'PM半日有給休暇を選択していますが、\n\n終了時間が ' + e_t_d + ' から ' + e_t + ' に変更されています。\n\nPM半日有給休暇で問題ありませんか？';
                if( ! confirm(msg) ) return false;
            }
        }
    }

    if( i>=0 && i<6 ) {  // 有給休暇〜遅刻早退 の 冠婚葬祭 選択時、備考をチェック
        if( !BikouCheck() ) return false;
    }

    if( i<=13 || i>17 ) {  // ID通し、時限承認忘れ 以外は連絡先をチェック
        if( !ContactCheck() ) return false;
    } else {
        if( i == 14 ) {         // IDカード通し忘れ（出勤）
            if( !IsWorkStartTime() ) return false;
        } else if( i == 15 ) {  // IDカード通し忘れ（退勤）
            if( !IsWorkEndTime() ) return false;
        } else {
            if( !IsOverTime() ) return false;
        }
    }

    if( !JyudensyaCheck() ) return false;

    var obj = document.getElementsByName("sougou_update");
    if( obj[0] && obj[0].value ) {
        return confirm("更新してもよろしいですか？\n更新後、元に戻すことはできません。");
    }

    return true;
}

// 時間単位の計算
function TimeCalculation()
{
    if( !document.getElementsByName('sum_hour')[0].value.match(/\S/g) ) {
        alert("計算に必要な 時間 が入力されていません。");
        return;
    }

    var base_s_time = document.getElementsByName("r0")[0].checked;
    var base_e_time = document.getElementsByName("r0")[1].checked;

    // 開始 or 終了時刻と変更時間を取得し、変更後の時刻を求める。
    var change_hour = befor_hour = after_hour = 0;
    if( base_s_time ) {
        change_hour = parseInt(document.getElementsByName("sum_hour")[0].value, 10);
        befor_hour = parseInt(document.getElementById("id_shh").value, 10);
        after_hour = befor_hour + change_hour;
        var t_str = new Date(document.getElementById("id_syear").value, document.getElementById("id_smonth").value-1, document.getElementById("id_sday").value, document.getElementById("id_shh").value, document.getElementById("id_smm").value);
        var t_end = new Date(document.getElementById("id_syear").value, document.getElementById("id_smonth").value-1, document.getElementById("id_sday").value, after_hour, document.getElementById("id_smm").value);
    } else if ( base_e_time ) {
        change_hour = parseInt(document.getElementsByName("sum_hour")[0].value, 10);
        befor_hour = parseInt(document.getElementById("id_ehh").value, 10);
        after_hour = befor_hour - change_hour;
        var t_str = new Date(document.getElementById("id_syear").value, document.getElementById("id_smonth").value-1, document.getElementById("id_sday").value, after_hour, document.getElementById("id_emm").value);
        var t_end = new Date(document.getElementById("id_syear").value, document.getElementById("id_smonth").value-1, document.getElementById("id_sday").value, document.getElementById("id_ehh").value, document.getElementById("id_emm").value);
    } else {
        alert("開始 or 終了 どちらも選択されていません。");
        return;
    }

    var t1200 = new Date(document.getElementById("id_syear").value, document.getElementById("id_smonth").value-1, document.getElementById("id_sday").value, 12, 00);
    var t1245 = new Date(document.getElementById("id_syear").value, document.getElementById("id_smonth").value-1, document.getElementById("id_sday").value, 12, 45);
    var t1500 = new Date(document.getElementById("id_syear").value, document.getElementById("id_smonth").value-1, document.getElementById("id_sday").value, 15, 00);
    var t1510 = new Date(document.getElementById("id_syear").value, document.getElementById("id_smonth").value-1, document.getElementById("id_sday").value, 15, 10);

    var lunch_time = break_time = false;

    if( t1200 <= t_str && t1245 > t_str ) {
        lunch_time = true;  // 開始時刻が、お昼休憩時間(12:00〜12:45)
    } else if( t1200 < t_end && t1245 >= t_end ) {
        lunch_time = true;  // 終了時刻が、お昼休憩時間(12:00〜12:45)
    } else if( t1500 <= t_str && t1510 > t_str ) {
        break_time = true;  // 開始時刻が、休憩時間(15:00〜15:10)
    } else if( t1500 < t_end && t1510 >= t_end ) {
        break_time = true; // 終了時刻が、休憩時間(15:00〜15:10)
    }

    if( (t1200 > t_str && t1245 <= t_end) ) {
        lunch_time = true;  // お昼 時間 またぐ為調整必要
    }
    // 変更後の時刻がお昼に関係する場合、45分づらす。
    if( lunch_time ) {
        if( base_s_time ) {
            t_end.setMinutes(t_end.getMinutes() + t1245.getMinutes());
/**
            if( t_str.getHours() == 12 ) {
                t_end.setMinutes(t_end.getMinutes() - t1245.getMinutes());
            }
/**/
        } else {
            t_str.setMinutes(t_str.getMinutes() - t1245.getMinutes());
/**
            if( t_end.getHours() == 12 ) {
                t_str.setMinutes(t_str.getMinutes() + t1245.getMinutes());
            }
/**/
        }
    }

    if( t1200 <= t_str && t1245 > t_str ) {
        lunch_time = true;  // 開始時刻が、お昼休憩時間(12:00〜12:45)
    } else if( t1200 < t_end && t1245 >= t_end ) {
        lunch_time = true;  // 終了時刻が、お昼休憩時間(12:00〜12:45)
    } else if( t1500 <= t_str && t1510 > t_str ) {
        break_time = true;  // 開始時刻が、休憩時間(15:00〜15:10)
    } else if( t1500 < t_end && t1510 >= t_end ) {
        break_time = true; // 終了時刻が、休憩時間(15:00〜15:10)
    }

    if( (t1500 > t_str && t1510 <= t_end) ) {
        break_time = true;  // 休憩 時間 またぐ為調整必要
    }
    // 変更後の時刻が休憩時間に関係する場合、10分づらす。
    if( break_time ) {
        if( base_s_time ) {
            t_end.setMinutes(t_end.getMinutes() + t1510.getMinutes());
/**
            if( t_str.getHours() == 15 ) {
                t_end.setMinutes(t_end.getMinutes() - t1510.getMinutes());
            }
/**/
        } else {
//alert('TEST 変更前＝' +  t_str.getHours() + '：' +  t_str.getMinutes());
            t_str.setMinutes(t_str.getMinutes() - t1510.getMinutes());
/**
            if( t_end.getHours() == 15 ) {
                t_str.setMinutes(t_str.getMinutes() + t1510.getMinutes());
            }
/**/
//alert('TEST 変更後＝' +  t_str.getHours() + '：' +  t_str.getMinutes());
        }
    }

    // 変更後の時刻をセットする。
    if( base_s_time ) {
        document.getElementById("id_ehh").value = ('0'+t_end.getHours()).slice(-2);
        document.getElementById("id_emm").value = ('0'+t_end.getMinutes()).slice(-2);
        EndTimeCopy();
//alert('開始時間：' +  t_end.getHours() + '\n\n終了時間：' +  t_end.getMinutes());
    } else {
        document.getElementById("id_shh").value = ('0'+t_str.getHours()).slice(-2);
        document.getElementById("id_smm").value = ('0'+t_str.getMinutes()).slice(-2);
        StartTimeCopy();
//alert('開始時間：' +  t_str.getHours() + '\n\n終了時間：' +  t_str.getMinutes());
    }

    // 時刻の最終チェックをする。
    msg = TimeCheck( t_str, t_end );
    if( msg ) alert(msg);

    return;
}

// 会社カレンダーの休日情報をセットしておく。
var holiday = "";
function SetHoliday(day)
{
    holiday = day;
}

// 休日ですか？
function IsHoliday(day)
{
    if( holiday.search((day.substr(0,4)+'-'+day.substr(4,2)+'-'+day.substr(6,2))) != -1 ) {
        return true;
    } else {
        return false;
    }
}

// 曜日を表示
function Youbi(w_date, id)
{
    var hiduke = new Date(w_date.substr(0,4),w_date.substr(4,2)-1,w_date.substr(6,2));
    var week = hiduke.getDay();
    var yobi = new Array(" (日)"," (月)"," (火)"," (水)"," (木)"," (金)"," (土)");
    var obj = document.getElementById(id);
//    obj.innerHTML = yobi[week];
    if( document.getElementById("0").checked && id == 'id_e_youbi') {
        obj.innerHTML = "<span style='color: DarkGray;'>" + yobi[week] + "</span>";
        return;
    }
    if( week == 0 ) {
        obj.innerHTML = "<span style='color: red;'>" + yobi[week] + "</span>";
    } else if( week == 6 ) {
        obj.innerHTML = "<span style='color: blue;'>" + yobi[week] + "</span>";
    } else if( holiday.search((w_date.substr(0,4)+'-'+w_date.substr(4,2)+'-'+w_date.substr(6,2))) != -1 ) {
        obj.innerHTML = "<span style='color: red;'>" + yobi[week] + "</span>";
    } else {
        obj.innerHTML = "<span style='color: black;'>" + yobi[week] + "</span>";
    }
}

var PeriodDays = 1;   // 期間日数（初期値：1）
// 休暇日数をセット
function SetPeriod()
{
    var sd = new Date(document.getElementById("id_syear").value, document.getElementById("id_smonth").value-1, document.getElementById("id_sday").value,0,0,0);
    var ed = new Date(document.getElementById("id_eyear").value, document.getElementById("id_emonth").value-1, document.getElementById("id_eday").value,0,0,0);
    if( (ed - sd ) > 0 ) {
        var difference = ed.getTime()-sd.getTime();
        PeriodDays = difference/(1000 * 3600 * 24)+1;
        for( var i=0; i<PeriodDays; i++ ) {
            if( IsHoliday(sd.getFullYear() + ('00'+(sd.getMonth()+1)).slice(-2) + ('00'+sd.getDate()).slice(-2)) ) {
                PeriodDays--;
            }
            sd.setDate(sd.getDate()+1);
        }
    } else {
        PeriodDays = 1;
    }
//alert(PeriodDays + ' 日');
}

// 開始日付コピー
function StartDateCopy()
{
    document.getElementsByName("str_date")[0].value = document.getElementById("id_syear").value + document.getElementById("id_smonth").value + document.getElementById("id_sday").value;

    if( !isDate(document.getElementsByName("str_date")[0].value) ) {
        var dt = new Date(document.getElementById("id_syear").value, document.getElementById("id_smonth").value, 0);
        document.getElementById("id_sday").value = dt.getDate();
        document.getElementsByName("str_date")[0].value = document.getElementById("id_syear").value + document.getElementById("id_smonth").value + document.getElementById("id_sday").value;
    }

    if( document.getElementById("0").checked ) {
        document.getElementById("id_eyear").value = document.getElementById("id_syear").value;
        document.getElementById("id_emonth").value = document.getElementById("id_smonth").value;
        document.getElementById("id_eday").value = document.getElementById("id_sday").value;
        EndDateCopy();
    }
    AfterReport();
    Youbi(document.getElementsByName("str_date")[0].value, 'id_s_youbi');

    SetPeriod();
    Iskeikaku();
}

// 終了日付コピー
function EndDateCopy()
{
    document.getElementsByName("end_date")[0].value = document.getElementById("id_eyear").value + document.getElementById("id_emonth").value + document.getElementById("id_eday").value;

    if( !isDate(document.getElementsByName("end_date")[0].value) ) {
        var dt = new Date(document.getElementById("id_eyear").value, document.getElementById("id_emonth").value, 0);
        document.getElementById("id_eday").value = dt.getDate();
        document.getElementsByName("end_date")[0].value = document.getElementById("id_eyear").value + document.getElementById("id_emonth").value + document.getElementById("id_eday").value;
    }
    Youbi(document.getElementsByName("end_date")[0].value, 'id_e_youbi');

    SetPeriod();
    Iskeikaku();
}

// 開始時刻コピー
function StartTimeCopy()
{
    document.getElementsByName("str_time")[0].value = document.getElementById("id_shh").value + ':' + ('0'+document.getElementById("id_smm").value).slice(-2);

    AfterReport();
}

// 終了時刻コピー
function EndTimeCopy()
{
    document.getElementsByName("end_time")[0].value = document.getElementById("id_ehh").value + ':' + ('0'+document.getElementById("id_emm").value).slice(-2);

    AfterReport();
}

// 応対日時コピー
function JyuDateCopy()
{
    document.getElementsByName("jyu_date")[0].value = document.getElementById("id_jyear").value + '-' + document.getElementById("id_jmonth").value + '-' + document.getElementById("id_jday").value + ' ' + document.getElementById("id_jhh").value + ':' + document.getElementById("id_jmm").value;

    if( !isDate(document.getElementById("id_jyear").value + document.getElementById("id_jmonth").value + document.getElementById("id_jday").value) ) {
        var dt = new Date(document.getElementById("id_jyear").value, document.getElementById("id_jmonth").value, 0);
        document.getElementById("id_jday").value = dt.getDate();
        document.getElementsByName("jyu_date")[0].value = document.getElementById("id_jyear").value + '-' + document.getElementById("id_jmonth").value + '-' + document.getElementById("id_jday").value + ' ' + document.getElementById("id_jhh").value + ':' + document.getElementById("id_jmm").value;
    }
    Youbi(document.getElementById("id_jyear").value + document.getElementById("id_jmonth").value + document.getElementById("id_jday").value, 'id_j_youbi');
}

// 申請日生成
function SinseiDate()
{
    var hiduke=new Date(); 

    var year = hiduke.getFullYear();
    var month =  ('00' + (hiduke.getMonth()+1)).slice( -2 );
    var week = hiduke.getDay();
    var day = ('00' + hiduke.getDate()).slice( -2 );

    var hour = ('00' + hiduke.getHours()).slice( -2 );
    var minute = ('00' + hiduke.getMinutes()).slice( -2 );
    var second = ('00' + hiduke.getSeconds()).slice( -2 );

    document.getElementsByName("sin_date")[0].value = year + '-' + month + '-' + day + ' ' + hour + ':' + minute + ':' + second;
    document.getElementsByName("sin_year")[0].value = year;
    document.getElementsByName("sin_month")[0].value = month;
    document.getElementsByName("sin_day")[0].value = day;
    document.getElementsByName("sin_hour")[0].value = hour;
    document.getElementsByName("sin_minute")[0].value = minute;
}

function OneDay( ischecked )
{
    setDisableStyle('id_1000' , ischecked);
    document.getElementById("id_eyear").disabled = ischecked;
    document.getElementById("id_emonth").disabled = ischecked;
    document.getElementById("id_eday").disabled = ischecked;
    StartDateCopy(); EndDateCopy();
}

function checkedday(obj)
{
        var yyymmdd = obj.value;
        if(yyymmdd.substr(6, 2) < 1) yyymmdd = yyymmdd.substr(0, 6) + '01';
        ///// 最終日をチェックしてセットする
        if (!isDate(yyymmdd)) {
            var dt = new Date(yyymmdd.substr(0, 4),  yyymmdd.substr(4, 2), 0);
            yyymmdd = ( yyymmdd.substr(0, 6) + dt.getDate() );
            if (!isDate(yyymmdd)) {
                alert(yyymmdd + '日付の指定が不正です！');
            }

        }
        obj.value = yyymmdd;
}

function isDate (str)
{
    var arr = (str.substr(0, 4) + '/' + str.substr(4, 2) + '/' + str.substr(6, 2)).split('/');

    if (arr.length !== 3) return false;
    var date = new Date(arr[0], arr[1] - 1, arr[2]);

    if (arr[0] !== String(date.getFullYear()) || arr[1] !== ('0' + (date.getMonth() + 1)).slice(-2) || arr[2] !== ('0' + date.getDate()).slice(-2)) {
        return false;
    } else {
        return true;
    }
}

// 最終確認のフラグをセット
function SetCheckFlag(str)
{
    if( str == "送信" ) {
//alert('送信');
        document.getElementsByName("check_flag")[0].value = "ok";
        document.getElementsByName("syainbangou")[0].value = "";    // 申請完了の為、空にする。
    } else {
//alert('戻る');
        document.getElementsByName("check_flag")[0].value = "replay";
    }
    return true;
}

// 再申請より、表示する場合
function ReInit()
{
    OneDay(document.getElementsByName("c0")[0].checked);
    syousai();
    telno();
    SinseiDate(); StartDateCopy(); EndDateCopy(); StartTimeCopy(); EndTimeCopy();
}


// 最終確認より、再表示する場合
function ReDisp()
{
    OneDay(document.getElementsByName("c0")[0].checked);
    syousai();
    telno();
    StartDateCopy(); EndDateCopy(); StartTimeCopy(); EndTimeCopy();
}

// 最終確認画面表示する際の、会社休日中の有給休暇などをエラーではじく。
function CheckDisp(sinseiNG)
{
    if( sinseiNG ) {
        alert("指定された期間（開始日 or 終了日）が 休日（会社カレンダー）です。\nそのため、この内容では申請できません。\n\n期間（開始日 or 終了日） or 内容 を変更して下さい。" );
    } else {
        document.getElementsByName("submit")[0].disabled = false;
    }
}

// 取消メール送信の確認画面
function MailSend()
{
    if( !document.getElementsByName('del_reason')[0].value.match(/\S/g) ) {
        alert("取消理由が入力されていません。\n\n入力後、再度[送信]ボタンをクリックして下さい。");
        return false;
    }

    return confirm("取消理由を送信してもよろしいですか？\n\n送信後、自動的にウィンドウを閉じます。");
}
