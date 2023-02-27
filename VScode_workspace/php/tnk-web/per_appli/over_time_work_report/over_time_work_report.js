////////////////////////////////////////////////////////////////////////////////
// 定時間外作業申告                                                           //
//                                            MVC View 部 (JavaScriptクラス)  //
// Copyright (C) 2021-2021 Ryota.waki ryota_waki@nitto-kohki.co.jp            //
// Changed history                                                            //
// 2021/10/20 Created over_time_work_report.js                                //
// 2021/11/01 Release.                                                        //
////////////////////////////////////////////////////////////////////////////////
//alert("TEST : ");
function CangeUID(str, name)   // ユーザー切替（テスト用）
{
    document.getElementsByName("login_uid")[0].value = str;
    document.getElementsByName(name)[0].submit();
}

function MsgView(str)   // メッセージ表示
{
    if( str ) alert(str);
}

// 共通 =======================================================================
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
function Youbi(obj, id)
{
    if( ! obj.value ) { // 日付がセットされてないとき、現在の日付をセット
        var now = new Date();
        obj.value = now.getFullYear() + ('0' + (now.getMonth() + 1)).slice(-2) + ('0' + now.getDate()).slice(-2);
    }

    var w_date = obj.value;
    var hiduke = new Date(w_date.substr(0,4),w_date.substr(4,2)-1,w_date.substr(6,2));

    var week = hiduke.getDay();
    var yobi = new Array(" (日)"," (月)"," (火)"," (水)"," (木)"," (金)"," (土)");
    var obj = document.getElementById(id);

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

// 正しい日付ですか？
function isDate(str)
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

// 作業日付コピー
function WorkDateCopy()
{
    var obj   = document.getElementById("id_w_date");
    var year  = document.getElementById("id_year").value;
    var month = document.getElementById("id_month").value;
    var day   = document.getElementById("id_day").value;

    obj.value = year + month + day;

    if( !isDate(obj.value) ) {
        var dt = new Date(year, month, 0);
        document.getElementById("id_day").value = dt.getDate();
        obj.value = year + month + document.getElementById("id_day").value;
    }

    Youbi(obj, 'id_w_youbi');
}

// 作業日付コピー2
function WorkDateCopy2()
{
    var obj   = document.getElementById("id_w_date2");
    var year  = document.getElementById("id_year2").value;
    var month = document.getElementById("id_month2").value;
    var day   = document.getElementById("id_day2").value;

    obj.value = year + month + day;

    if( !isDate(obj.value) ) {
        var dt = new Date(year, month, 0);
        document.getElementById("id_day2").value = dt.getDate();
        obj.value = year + month + document.getElementById("id_day2").value;
    }

    Youbi(obj, 'id_w_youbi2');
}

// 部署名が選択されていますか？
function DDBumon()
{
    if( document.getElementsByName("ddlist_bumon")[0].selectedIndex == 0 ) {
        document.getElementById("id_read").disabled = true;
    } else {
        document.getElementById("id_read").disabled = false;
    }
}

// 入力 =======================================================================

// 指定表示
function QuickView(w_date, bumon, type)
{
    //           0123456789
    // w_date = "yyyy-mm-dd（w）"
    var year  = ('0'+w_date.substr(0,4)).slice(-4);
    var month = ('0'+w_date.substr(5,2)).slice(-2);
    var day   = ('0'+w_date.substr(8,2)).slice(-2);
    
    document.getElementById('id_year').value = year;
    document.getElementById('id_month').value = month;
    document.getElementById('id_day').value = day;
    document.getElementById('id_w_date').value = year + month + day;
    document.getElementsByName("ddlist_bumon")[0].value = bumon;
    document.getElementsByName("ddlist_v_type")[0].value = type;
    
    document.getElementById("id_list_view").value = 'on';
    document.getElementsByName("form_appli")[0].submit();
}

// マウスカーソルを待機状態
function CursorWait(obj)// this.cursor = 'wait'
{
    obj.style.cursor = 'wait';
    document.body.style.cursor = 'wait';
}

// 社員名一覧を表示
function SetViewON(obj)
{
    CursorWait(obj);

    var obj = document.getElementsByName("ddlist_bumon")[0];
    if( obj.selectedIndex == 0 ) {
        obj.focus();
        obj.select();
        return;
    }

    document.getElementById("id_list_view").value = 'on';
    document.getElementsByName("form_appli")[0].submit();
}

// 社員名一覧を非表示
function SetViewOFF()
{
//    document.getElementsByName("ddlist_bumon")[0].selectedIndex = 0;

    document.getElementById("id_list_view").value = '';
    document.getElementsByName("ddlist_bumon")[0].value = '';
    document.getElementsByName("form_appli")[0].submit();
}

// コピー元ラジオボタン用
// ラジオボタンのチェック・非チェック ＋ コピー先チェックボックスの使用可否
var back_obj  = ''; // 前回のコピー元 保持用
var back_obj2 = ''; // 前回のコピー先 保持用
var back_idx  = -1; // 前回のインデックス 保持用
function RadioCheck(obj, idx)
{
    // ラジオボタンの処理
    if( ! back_obj ) back_obj = obj;        // ラジオボタンをバックアップ（初回のみ）
    
    if( obj.value == '' ) {     // 選択したラジオボタン
        back_obj.value = '';    // 前回ラジオボタンの表示フラグクリア
        obj.value = 'on';       // ラジオボタンへ表示フラグセット
    } else {
        obj.value = '';         // ラジオボタンの表示フラグクリア
        obj.checked = false;    // ラジオボタンのチェックを外す
    }
    
    if( back_obj != obj ) back_obj = obj;   // ラジオボタンをバックアップ（前回と違う時）
    
    // チェックボックスの処理
    var id_name = obj.id;
    var new_id_name = id_name.replace( 'radio', 'check' );
    var obj2 = document.getElementById(new_id_name);
    
    if( ! back_obj2 ) back_obj2 = obj2;     // チェックボックスをバックアップ（初回のみ）
    
    if( ! obj2 ) return;    // 対応するチェックボックスがない
    
    var w_check = obj2.checked; // チェックボックスのチェック状態を保存
    
    if( obj.checked ) {         // ラジオボタンにチェックがあるか？
        obj2.checked  = false;      // ある：チェックボックス チェッククリア
        obj2.disabled = true;       // ある：チェックボックス 禁止
    } else {
        obj2.disabled = false;              // ない：チェックボックス 使用可能
        obj2.checked = back_obj2.checked;   // ない：チ今回のチェックボックスへ前回のチェック状態をセット
    }
    
    var obj_yo = document.getElementById('id_y_s_h' + back_idx);
    var obj_ji = document.getElementById('id_j_s_h' + back_idx);
    if( (obj_yo && obj_yo.disabled && !obj_ji) || (obj_ji && (obj_ji.disabled || obj_yo.value!=-1)) ) {  // 時間変更 禁止状態ですか？
        back_obj2.disabled = true;  // 前回チェックボックス 禁止
    } else if( back_obj2 != obj2 ) {
        back_obj2.disabled = false; // 前回チェックボックス 使用可能
        back_obj2.checked = w_check;   // 前回のチェックボックスに今回のチェック状態をセット
    }
    
    if( back_idx != idx ) back_idx = idx; // インデックスをバックアップ（前回と違う時）

    if( back_obj2 != obj2 ) back_obj2 = obj2;   // チェックボックスをバックアップ（前回と違う時）
}

// コピー先チェックボックスのチェック可否
function CheckFlag(obj)
{
    if( obj.checked ) {
        obj.value = 'on';
    } else {
        obj.value = '';
    }
}

// コピー先チェックボックス一括制御
function AllCheck(obj,max)
{
    for(var i=0; i<max; i++) {
        var name = 'id_check' + i;
        var obj2 = document.getElementById(name);
        if( ! obj2 ) continue;
        if( obj2.disabled ) continue;
        if( obj.value == '先,' ) {
            obj2.checked = true;
            obj2.value = 'on';
        } else {
            obj2.checked = false;
            obj2.value = '';
        }
    }
    if( obj.value == '先,' ) {
        obj.value = '先.';
    } else {
        obj.value = '先,';
    }
}

// 選択元(１つ)から選択先(複数可)へのコピー処理
function RadioToCheck(max)
{
    var radio_no = -1;
    for(var i=0; i<max; i++) {
        if( document.getElementById('id_radio' + i).checked ) {
            radio_no = i;
            break;
        }
    }
    if( radio_no == -1 ) {
        alert("コピー元が指定されていません。");
        return;
    }

    var copy_counter = 0;
    for(var i=0; i<max; i++) {
        if( ! document.getElementById('id_check' + i) ) continue;
        if( ! document.getElementById('id_check' + i).checked ) continue;
        // コピー処理
        if( ! document.getElementById('id_y_s_h' + i).disabled ) {
            document.getElementById('id_y_s_h' + i).value = document.getElementById('id_y_s_h' + radio_no).value;
            document.getElementById('id_y_s_m' + i).value = document.getElementById('id_y_s_m' + radio_no).value;
            document.getElementById('id_y_e_h' + i).value = document.getElementById('id_y_e_h' + radio_no).value;
            document.getElementById('id_y_e_m' + i).value = document.getElementById('id_y_e_m' + radio_no).value;
            document.getElementById('id_z_j_r' + i).value = document.getElementById('id_z_j_r' + radio_no).value;
        } else {
            document.getElementById('id_j_s_h' + i).value = document.getElementById('id_j_s_h' + radio_no).value;
            document.getElementById('id_j_s_m' + i).value = document.getElementById('id_j_s_m' + radio_no).value;
            document.getElementById('id_j_e_h' + i).value = document.getElementById('id_j_e_h' + radio_no).value;
            document.getElementById('id_j_e_m' + i).value = document.getElementById('id_j_e_m' + radio_no).value;
            document.getElementById('id_j_g_n' + i).value = document.getElementById('id_j_g_n' + radio_no).value;
        }
        copy_counter++;
    }
    if( copy_counter == 0 ) {
        alert("コピー先が指定されていませんでした。");
        return;
    } else {
//        alert(copy_counter + " 件のコピーを実行しました。\n\n※注）[登録]をクリックしないと\n\n変更データは保存されません!!");
        if( confirm(copy_counter + " 件のコピーを実行しました。\n\nコピー結果を【登録】しますか？\n\n*** 登録 *** なら [OK] ボタン\n\nコピーだけなら [キャンセル]") ) {
            if( IsUpDate() ) {
                document.getElementById('id_appli').value = 'up';
                document.getElementsByName("form_appli")[0].submit();
            }
        }
    }
}

// 事前申請 から 残業結果報告 へ コピー
function YoteiToJisseki(str, idx)
{
    document.getElementById(str.replace('copy', 'j_s_h')).value = document.getElementById(str.replace('copy', 'y_s_h')).value;
    document.getElementById(str.replace('copy', 'j_s_m')).value = document.getElementById(str.replace('copy', 'y_s_m')).value;
    document.getElementById(str.replace('copy', 'j_e_h')).value = document.getElementById(str.replace('copy', 'y_e_h')).value;
    document.getElementById(str.replace('copy', 'j_e_m')).value = document.getElementById(str.replace('copy', 'y_e_m')).value;
    document.getElementById(str.replace('copy', 'j_g_n')).value = document.getElementById(str.replace('copy', 'z_j_r')).value;
    document.getElementById('2_' + idx).value = '－－';
}

// 事前申請 から 残業結果報告 へ コピー（全て）
function YoteiToJissekiAll(max)
{
    for( var i=0; i<max; i++ ) {
        if( document.getElementById('id_copy' + i).disabled ) continue;
        YoteiToJisseki('id_copy' + i, i);
    }
}

// 選択・入力のチェック
function SelectInputCheck(type, max)
{
    var msg = "";       // メッセージ格納用
    var n_t = new Date(); var year = n_t.getFullYear(); var month = n_t.getMonth()+1; var date = n_t.getDate();
    var content_name = "";
    if( type == 'y' ) content_name = "id_z_j_r"; else content_name = "id_j_g_n";

    for( var i=0; i<max; i++ ) {
        // 初期化
        var cnt = 0;
        var obj_sh = document.getElementById('id_' + type + '_s_h' + i);
        var obj_sm = document.getElementById('id_' + type + '_s_m' + i);
        var obj_eh = document.getElementById('id_' + type + '_e_h' + i);
        var obj_em = document.getElementById('id_' + type + '_e_m' + i);
        var time_ng = false; content_ng = false;
        
        if( ! obj_sh ) continue;    // 存在するオブジェクトかチェック
        
        // 選択されていればカウントアップ
        if( obj_sh.value != -1 ) cnt++;
        if( obj_sm.value != -1 ) cnt++;
        if( obj_eh.value != -1 ) cnt++;
        if( obj_em.value != -1 ) cnt++;
        
        // 開始終了時間の選択されている数により処理を分岐
        if( cnt == 0 ) {
            continue;   // 時間指定されてない為、次の人へ
        } else if( cnt < 4 ) {  // 時間指定あるが中途半端
                msg += document.getElementById('id_simei'+i).value + ' 様　開始終了時間の指定が中途半端です。\n';
        } else if( cnt >= 4 ) {
            // 時間指定あり為、開始終了のチェック
            var s_t = new Date(year, month, date, obj_sh.value, obj_sm.value, 0);
            var e_t = new Date(year, month, date, obj_eh.value, obj_em.value, 0);
            if( s_t >= e_t ) time_ng = true; // 指定時間がエラー
            if(type == 'j' && s_t <= e_t) time_ng = false; // 残業キャンセル扱い。
            // 残業実施理由チェック
            if( ! document.getElementById(content_name+i).value.match(/\S/g) ) content_ng = true;
            
            if( time_ng || content_ng ) {   // エラーメッセージ生成
                msg += document.getElementById('id_simei'+i).value + " 様\n";
                if( time_ng ) {
                    msg += "　" + s_t.getHours() + ':' + s_t.getMinutes() + ' ～ ' + e_t.getHours() + ':' + e_t.getMinutes() + " 開始終了時間が逆転してまいす。\n";
                }
                if( content_ng ) {
                    msg += "　残業実施理由が入力されていない。\n";
                }
            }
        }
    }
    return msg;
}

// 申告情報更新可能ですか？
function IsUpDate()
{
    var max = document.getElementById('id_rows').value;
    var msg = '';       // メッセージ格納
    
    msg = SelectInputCheck('y', max);   // 事前申請 内容 チェック
    
    if( msg ) {
        msg = '以下の方の指定に誤りがある為、登録できません。\n\n' + msg;
        alert(msg);
        return false;
    }
    
    msg = SelectInputCheck('j', max);   // 残業結果報告 内容 チェック
    
    if( msg ) {
        msg = '以下の方の指定に誤りがある為、登録できません。\n\n' + msg;
        alert(msg);
        return false;
    }
    
    document.body.style.cursor = 'wait';
    document.getElementById('id_appli').value = 'up';
    return true;
}

// 申告者追加フラグON
function AppliAdd()
{
    document.getElementById('id_appli').value = 'add';
    return true;
}

// コメントフラグON
function UpComment()
{
    document.getElementById('id_appli').value = 'comment';
    return true;
}

// 選択入力制御
function ReportEdit(obj, no, uid, uno)
{
    if( obj.value == '完了' || obj.value == '途中' ) {
        if( confirm("取り消し画面へ移行しますか？") ) {
            document.getElementById('id_showMenu').value = 'Cancel';
            document.getElementById('id_cancel_uid').value = document.getElementById('id_uid' + no).value;
            document.getElementById('id_cancel_uno').value = uno;
            if( obj.id == 1 ) {
                document.getElementById('id_type').value = 'yo';
            } else {
                document.getElementById('id_type').value = 'ji';
            }
            document.getElementsByName("form_appli")[0].submit();
        }
        return ;
    }

    var obj_0  = document.getElementById('id_check' + no);
    
    var obj_1  = document.getElementById('id_y_s_h' + no);
    var obj_2  = document.getElementById('id_y_s_m' + no);
    var obj_3  = document.getElementById('id_y_e_h' + no);
    var obj_4  = document.getElementById('id_y_e_m' + no);
    var obj_5  = document.getElementById('id_z_j_r' + no);
    
    var obj_6  = document.getElementById('id_j_s_h' + no);
    var obj_7  = document.getElementById('id_j_s_m' + no);
    var obj_8  = document.getElementById('id_j_e_h' + no);
    var obj_9  = document.getElementById('id_j_e_m' + no);
    var obj_10 = document.getElementById('id_j_g_n' + no);

    if( obj.value == '－－' ) {
        if( obj.id == 1 ) {
            obj_1.value = obj_2.value = obj_3.value = obj_4.value = '-1';   // 時間 初期化
            obj_5.value = '';   // 内容 初期化
        } else {
            if( obj_1.value == '-1' || obj_6.value != '-1' ) {
                obj_6.value = obj_7.value = obj_8.value = obj_9.value = '-1';   // 時間 初期化
                obj_10.value = '';  // 内容 初期化
                document.getElementById('id_zan_' + no).checked = '';
            } else {
                obj_6.value = obj_8.value = document.getElementById('id_limit_hh' + no).value;//obj_1.value;
                obj_7.value = obj_9.value = document.getElementById('id_limit_mm' + no).value;//obj_2.value;
                obj_10.value = "残業不要になった。";
                obj.value = "中止";
                document.getElementById('id_zan_' + no).checked = true;
            }
        }
    } else if(obj.value == '中止') {
        obj_6.value = obj_7.value = obj_8.value = obj_9.value = '-1';   // 時間 初期化
        obj_10.value = '';  // 内容 初期化
        obj.value = '－－';
        document.getElementById('id_zan_' + no).checked = "";
    } else {
        if( obj.id == 1 ) {
            obj_0.disabled = obj_1.disabled = obj_2.disabled = obj_3.disabled = obj_4.disabled = obj_5.disabled = false;
        } else {
            obj_0.disabled = obj_6.disabled = obj_7.disabled = obj_8.disabled = obj_9.disabled = obj_10.disabled = false;
        }
        obj.value = '－－';
    }
}

// 出勤時間をセット
function setStrTime(idx, limit_hh, limit_mm, str_time)
{
    if( str_time == "0000" ) return;
    var limit_time = ('0'+limit_hh).slice(-2)+('0'+limit_mm).slice(-2);
    if(limit_time < str_time) return;
    document.getElementById('id_j_e_h' + idx).value = ('0'+limit_hh).slice(-2);
//    limit_mm -= 10;
    document.getElementById('id_j_e_m' + idx).value = ('0'+limit_mm).slice(-2);
    str_time = ('0'+str_time).slice(-4);
    document.getElementById('id_j_s_h' + idx).value = str_time.substr(0,2);
    document.getElementById('id_j_s_m' + idx).value = str_time.substr(2,2);
    document.getElementById('id_j_g_n' + idx).focus();
    document.getElementById('id_j_g_n' + idx).select();
    document.getElementById('2_' + idx).value = '－－';
    document.getElementById('id_zan_' + idx).checked = '';
}

// 退勤時間をセット
function setEndTime(idx, limit_hh, limit_mm, end_time)
{
    if( end_time == "0000" ) return;
    if(limit_hh==17 && limit_mm==15) limit_mm=30;
    var limit_time = ('0'+limit_hh).slice(-2)+('0'+limit_mm).slice(-2);
    if(limit_time >= end_time) return;
    document.getElementById('id_j_s_h' + idx).value = ('0'+limit_hh).slice(-2);
    document.getElementById('id_j_s_m' + idx).value = ('0'+limit_mm).slice(-2);
    end_time = ('0'+end_time).slice(-4);
    document.getElementById('id_j_e_h' + idx).value = end_time.substr(0,2);
    document.getElementById('id_j_e_m' + idx).value = end_time.substr(2,2);
    document.getElementById('id_j_g_n' + idx).focus();
    document.getElementById('id_j_g_n' + idx).select();
    document.getElementById('2_' + idx).value = '－－';
    document.getElementById('id_zan_' + idx).checked = '';
}

// 延長及び残業無し制御
function ZanCheck(no, uid, hh, mm)
{
    var obj  = document.getElementById('2_' + no);

    var obj_0  = document.getElementById('id_check' + no);
    
    var obj_6  = document.getElementById('id_j_s_h' + no);
    var obj_7  = document.getElementById('id_j_s_m' + no);
    var obj_8  = document.getElementById('id_j_e_h' + no);
    var obj_9  = document.getElementById('id_j_e_m' + no);
    var obj_10 = document.getElementById('id_j_g_n' + no);

    if( obj.value == '－－' && document.getElementById('id_zan_' + no).checked ) {
        obj_6.value = obj_8.value = ('0'+hh).slice(-2);
        obj_7.value = obj_9.value = ('0'+mm).slice(-2);
        obj_10.value = "延長及び、残業なし。";
        obj.value = "中止";
    } else {
        obj_6.value = obj_7.value = obj_8.value = obj_9.value = '-1';   // 時間 初期化
        obj_10.value = '';  // 内容 初期化
        obj.value = '－－';
    }
}

// 前後１日移動
function setNextDate(obj)
{
    var obj_date = document.getElementById('id_w_date');
    var w_date = obj_date.value;
    var dt = new Date(w_date.substr(0,4), w_date.substr(4,2)-1, w_date.substr(6,2));
    
    if( obj.name == 'before' ) {
        dt.setDate(dt.getDate() - 1);   // １日減算
    } else {
        dt.setDate(dt.getDate() + 1);   // １日加算
    }
    
    var year  = ('0'+dt.getFullYear()).slice(-4);
    var month = ('0'+(dt.getMonth()+1)).slice(-2);
    var day   = ('0'+dt.getDate()).slice(-2);
    
    document.getElementById('id_year').value = year;
    document.getElementById('id_month').value = month;
    document.getElementById('id_day').value = day;
    obj_date.value = year + month + day;
    
    document.getElementsByName("form_appli")[0].submit();
}
//alert("TEST : ");

// ボタンに対応する固定時間をセット
function setFixedTime(obj, no)
{
    var obj_1  = document.getElementById('id_y_s_h' + no);
    var obj_2  = document.getElementById('id_y_s_m' + no);
    var obj_3  = document.getElementById('id_y_e_h' + no);
    var obj_4  = document.getElementById('id_y_e_m' + no);

    if( obj.id == 10 ) {// alert("TEST : 延長");
        obj_1.value = 16; obj_2.value = 15; obj_3.value = 17; obj_4.value = 15;
    } else if( obj.id == 11 ) {// alert("TEST : 延残１");
        obj_1.value = 16; obj_2.value = 15; obj_3.value = 18; obj_4.value = 30;
    } else if( obj.id == 12 ) {// alert("TEST : 延残２");
        obj_1.value = 16; obj_2.value = 15; obj_3.value = 19; obj_4.value = 30;
    } else if( obj.id == 13 ) {// alert("TEST : 残１");
        obj_1.value = 17; obj_2.value = 30; obj_3.value = 18; obj_4.value = 30;
    } else if( obj.id == 14 ) {// alert("TEST : 残２");
        obj_1.value = 17; obj_2.value = 30; obj_3.value = 19; obj_4.value = 30;
    } else if( obj.id == 20 ) {
        obj_1.value = '08'; obj_2.value = 30; obj_3.value = 12; obj_4.value = '00';
    } else if( obj.id == 21 ) {
        obj_1.value = '08'; obj_2.value = 30; obj_3.value = 16; obj_4.value = 15;
    } else if( obj.id == 22 ) {
        obj_1.value = '08'; obj_2.value = 30; obj_3.value = 17; obj_4.value = 15;
    } else if( obj.id == 23 ) {
        obj_1.value = 12; obj_2.value = 45; obj_3.value = 16; obj_4.value = 15;
    } else if( obj.id == 24 ) {
        obj_1.value = 12; obj_2.value = 45; obj_3.value = 17; obj_4.value = 15;
    }
}

var old_obj=''; // 前回選択オブジェクト記憶
// 製品名コピー
function PlanCopy(obj, str)
{
//alert(str);

    if( window.clipboardData ) {
//        window.clipboardData.setData("text",obj.value);
        window.clipboardData.setData("text", str);
    } else if( navigator.clipboard ) {  // https: のサイトじゃないと使用できない。
        navigator.clipboard.writeText(obj.value);
    } else {
        alert("このブラウザでは、コピー機能使用不可！\n\n製品名を選択しコピーを行い利用して下さい。");
//        console.log(navigator);
        return;
    }
    if(old_obj != '') {
        old_obj.style.backgroundColor='';
    }
    old_obj = obj;
    obj.style.backgroundColor='yellow';
}

// 取消 =======================================================================
// [取消実行]する為の準備
function CancelExec()
{
    var obj = document.getElementById('id_reason');  // 理由入力領域オブジェクト取得
    if( ! obj.value.match(/\S/g) ) {
        alert("取消理由が未入力です。");
        return false;
    }
    return true;
}

// 取消[キャンセル]する為の準備
function CancelCancel()
{
    document.getElementById('id_cancel_uid').value = "";
    document.getElementById('id_cancel_uno').value = "";
    return true;
}

// 承認 =======================================================================

// 承認表示切替
function AdmitDispSwitch()
{
    document.getElementsByName("admit")[0].value = "";
    document.getElementsByName("form_judge")[0].submit();
}

// 承認の使用許可禁止を制御
function IsRemarks(obj, no, max)
{
    var c_radio_obj = document.getElementById('id_c_radio' + no);
    var c_label_obj = document.getElementById('id_c_label' + no);
    
    c_label_obj.style.color='Black' // 承認 使用許可（文字：黒）
    c_radio_obj.disabled = false;   // 承認 使用許可にする
    document.getElementById('id_rem_msg' + no).innerHTML = "";
    
    for( var r=0; r<max; r++) {
        var rem_obj = document.getElementById('id_remarks' + no + '_' + r);
        if( ! rem_obj ) continue;   // 備考入力がなければスキップ
        if( ! rem_obj.value.match(/\S/g) ) {
            rem_obj.value = "";
            c_label_obj.style.color='DarkGray'  // 承認 使用禁止（文字：グレー）
            c_radio_obj.disabled = true;        // 承認 使用禁止にする
            c_radio_obj.checked = false;        // 承認 チェックを外す
            document.getElementById('id_rem_msg' + no).innerHTML ="※退勤時間 黄色の備考入力を行って下さい。";
            break;
        }
    }
/*
    if( ! obj.value.match(/\S/g) ) {
        obj.value = "";
        c_label_obj.style.color='DarkGray'
        c_radio_obj.disabled = true;    // 承認使用禁止にする
        c_radio_obj.checked = false;    // 承認使用禁止にする
    } else {
        c_label_obj.style.color='Black'
        c_radio_obj.disabled = false;    // 承認使用可能にする
    }
*/
}

// 承認・否認の制御
function AdmitSelect(obj, val, no)
{
    var admit_flag = 'on';  // 承認・否認 実行フラグ
    var obj_radio = document.getElementsByName('radio_yo' + no);        // 事前申請
    var obj_reason = document.getElementById('id_yo_ng_comme' + no);    // 否認理由
    
    if( obj_radio.length == 0 ) {   // 事前申請にないなら残業結果報告
        obj_radio = document.getElementsByName('radio_ji' + no);        // 残業結果報告
        obj_reason = document.getElementById('id_ji_ng_comme' + no);    // 否認理由
    }
    
    if( obj_radio[0].checked ) {        // 承認 チェックあり
        if( obj_radio[0].value == '' ) {    // 初回 チェック
            obj_radio[0].value = 's';           // 承認 選択状態
            obj_radio[1].value = '';            // 否認 クリア
        } else {                            // 既に チェック済み
            obj_radio[0].checked = false;       // 承認 チェックを外す
            obj_radio[0].value = '';            // 承認 未選択状態
            admit_flag = '';                    // 実行できないよう 実行フラグ 空にする
        }
    } else if( obj_radio[1].checked ) { // 否認 チェックあり
        if( obj_radio[1].value == '' ) {    // 初回 チェック
            obj_radio[1].value = 'h';           // 否認 選択状態
            obj_radio[0].value = '';            // 承認 クリア
        } else {                            // 既に チェック済み
            obj_radio[1].checked = false;       // 否認 チェックを外す
            obj_radio[1].value = '';            // 否認 未選択状態
            admit_flag = '';                    // 実行できないよう 実行フラグ 空にする
        }
    } else {    // どちらもチェック無し
        obj_radio[0].value = '';    // 承認 未選択状態
        obj_radio[1].value = '';    // 否認 未選択状態
        admit_flag = '';            // 実行できないよう 実行フラグ 空にする
    }

    var pos_na = document.getElementById('id_posts').value; // 'ka' or 'bu' or 'ko'
    var obj_comme = document.getElementById('id_comment_' + pos_na + no);  // コメント
    
    if( obj_radio[1].checked ) { // 否認
        obj_reason.disabled = false;    // 否認理由 許可
        if(obj_comme) obj_comme.disabled = true;      // コメント 禁止
    } else {
        obj_reason.disabled = true;     // 否認理由 禁止
        if(obj_comme) obj_comme.disabled = false;     // コメント 許可
    }
}

// 承認を一括制御
function AdmitAllSelect(obj, max)
{
    var flag = true;
    if( obj.value == "承認一括選択" ) {
        obj.value = "承認一括解除";
    } else {
        flag = false;
        obj.value = "承認一括選択";
    }
    
    var obj_radio = '';
    for( var i=0; i<max; i++ ) {
        obj_radio = GetRadioObj(i); // 承認・否認 ラジオ
        
        if( obj_radio[0].disabled ) continue;   // 無効化されていたらスキップ
        
        if( obj_radio[0].checked == flag) continue; // 指定条件とおなじならスキップ
        
        if( !obj_radio[0].checked ) obj_radio[0].checked = true;    // 未チェックなら付ける
        
        AdmitSelect(obj_radio, 'st', i);    // 承認・否認の制御を行う
    }
}

// 使用可能なラジオボタンオブジェクト取得
function GetRadioObj(idx)
{
    var obj = document.getElementsByName('radio_yo' + idx); // 事前申請
    
    if( obj.length == 0 ) obj = document.getElementsByName('radio_ji' + idx); // 残業結果報告
    
    return obj;
}

// 確定処理
function AdmitExec()
{
    var msg = '';   // メッセージ領域
    var max = document.getElementById('id_rows_max').value;
    var pos_na = document.getElementById('id_posts').value; // 'ka' or 'bu' or 'ko'
    var obj = "";
    var obj_radio = '';
    var obj_yo = "";    // 予定側 [0]承認:'t'、[1]否認:'h'
    var obj_ji = "";    // 実績側 [0]承認:'t'、[1]否認:'h'
    var no_check = 0;
    var conf = false;

    for( var i=0; i<max; i++ ) {
        obj_radio = GetRadioObj(i); // 承認・否認 ラジオ
        if( ! obj_radio[0].checked && ! obj_radio[1].checked ) {
            no_check++;
            continue; // 承認 or 否認 が選択されていません。
        }

        if( pos_na != 'ko' ) {
            obj = document.getElementById('id_comment_' + pos_na + i);  // コメント
            if( ! obj.readOnly && ! obj.value.match(/\S/g) ) {
                if( obj_radio[0].checked ) {
                    msg = 'コメントが入力されていないものがあります。\n\nコメントが必要となっているものは入力を行って下さい。';
                    break;
                }
            }
        }
        if( obj_radio[1].checked ) {
            conf = true;
        }
    }
    if( max == no_check ) {
        msg = '承認 or 否認 が選択されていません。\n\n選択後、再度クリックして下さい。';
    }
    
    if( msg ) { // エラーメッセージあり
        alert(msg);
        return false;
    }
    
    if( conf ) {    // 否認の選択あり
        if( ! confirm("否認を選択したものは\n\n否認理由を入力しましたか？") ) return false;
    }
    
    document.getElementById('id_admit').value = true; // 実行フラグセット
    return true;
}
//alert("TEST : ");

// 照会 =======================================================================
function DaysSelect(obj)
{
    if( obj.id == 'id_s_day' ) {
        obj.value = 1;
        document.getElementById("id_range").disabled = true;
        document.getElementById("id_e_day_area").disabled = true;
        document.getElementById("id_year2").disabled = true;
        document.getElementById("id_month2").disabled = true;
        document.getElementById("id_day2").disabled = true;
        document.getElementById("id_w_youbi2").disabled = true;
    } else {
        obj.value = 2;
        document.getElementById("id_range").disabled = false;
        document.getElementById("id_e_day_area").disabled = false;
        document.getElementById("id_year2").disabled = false;
        document.getElementById("id_month2").disabled = false;
        document.getElementById("id_day2").disabled = false;
        document.getElementById("id_w_youbi2").disabled = false;
    }
}

// 照会実行
function QuiryExec()
{
    document.getElementById('id_showMenu').value = 'Results';
}

// 社員番号入力チェック
function InputCheck(obj){
    var str1=obj.value;

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

var work_str = "";
/*
function TimeEdit(obj)
{
    if( obj.innerHTML == "修正" ) {
//        work_str = obj.parentNode.parentNode.children[9].innerText;
//        obj.parentNode.parentNode.children[9].innerHTML="<input type='text' value=" + work_str + ">";
        obj.innerHTML = "更新";
    } else {
//        obj.parentNode.parentNode.children[9].innerText = work_str;
        obj.innerHTML = "修正";
    }
    return false;
}
*/
function CreatNumberList(id, min, max, def){
    var selectElement = document.getElementById(id);
    var option;
    for(var i = min; i <= max; i ++){
        option = document.createElement("option");  // ここで<option>要素を作成
        option.value = i;                               // optionのvalue属性を設定
        option.innerText = i;                           // リストに表示するテキストを記述
        if( i == def ) option.selected = true;          // defで指定した数字を選択
        selectElement.appendChild(option);              // セレクトボックスにoptionを追加
    }
}

function TimeEditStr()
{
    var str = "j_time";
    var id = str + "_display";
    var CELL = document.getElementById(id);
    var TABLE = CELL.parentNode.parentNode.parentNode;
    var index = cell_header[str];
    var inner_text = "";
    for(var i=1;TABLE.rows[i];i++) {
        if(TABLE.rows[i].cells[index+2].innerText != "承認 済") continue;
        if(TABLE.rows[i].cells[index].innerText == "残業 キャンセル") continue;
        inner_text = TABLE.rows[i].cells[index].innerText;
        TABLE.rows[i].cells[index].innerHTML = "<select name='sh"+i+"' id='id_sh"+i+"'></select>:<select name='sm"+i+"' id='id_sm"+i+"'></select>"+"～<select name='eh"+i+"' id='id_eh"+i+"'></select>:<select name='em"+i+"' id='id_em"+i+"'></select>";
        CreatNumberList("id_sh"+i,0,23,inner_text.substr(0,2));
        CreatNumberList("id_sm"+i,0,59,inner_text.substr(3,2));
//        TABLE.rows[i].cells[index].innerHTML = inner_text.substr(0,6)+"<select name='eh"+i+"' id='id_eh"+i+"'></select>:<select name='em"+i+"' id='id_em"+i+"'></select>";
        CreatNumberList("id_eh"+i,0,23,inner_text.substr(6,2));
        CreatNumberList("id_em"+i,0,59,inner_text.substr(9,2));
    }
    document.getElementById(str+'_edit_str').disabled = true;
    document.getElementById(str+'_edit_end').disabled = false;
    return false;
}

function TimeEditEnd()
{
    var flag = confirm("修正内容を確定してよろしいですか？");
    
    if( flag ) {
        document.getElementById("id_time_edit").value = 'on';
        document.getElementById("id_showMenu").value  = 'Results';
    }
    
    return flag;
}

// ============================================================================
//alert("TEST : ");
// ============================================================================

// ページ読み込み時に毎回呼び出す初期処理（入力用）
function Init()
{
    var obj = document.getElementById("id_w_date");
    obj.value = document.getElementById("id_year").value+document.getElementById("id_month").value+document.getElementById("id_day").value;
    Youbi(obj, 'id_w_youbi');
    
    obj = document.getElementsByName("ddlist_bumon")[0];
    if( obj.length == 2 ) obj.selectedIndex = 1;
    if( obj.selectedIndex == 0 ) {
        document.getElementById("id_read").disabled = true;
    }
}

// ページ読み込み時に毎回呼び出す初期処理（照会用）
function InitCancel()
{
    document.getElementById("id_reason").focus();
}

// ページ読み込み時に毎回呼び出す初期処理（照会用）
function InitQuiry()
{
    var obj = document.getElementById("id_w_date");
    obj.value = document.getElementById("id_year").value+document.getElementById("id_month").value+document.getElementById("id_day").value;
    Youbi(obj, 'id_w_youbi');
    
    obj = document.getElementById("id_w_date2");
    obj.value = document.getElementById("id_year2").value+document.getElementById("id_month2").value+document.getElementById("id_day2").value;
    Youbi(obj, 'id_w_youbi2');
    
    obj = document.getElementsByName("ddlist_bumon")[0];
    if( obj.length == 2 ) obj.selectedIndex = 1;
}

// ページ読み込み時に毎回呼び出す初期処理（照会結果表示用）
var cell_header = {"date":0,"deploy":0,"name":0,"z_contents":0,"z_state":0,"j_time":0,"j_contents":0,"j_state":0,"remarks":0};
function InitResults()
{
    var array = ["date","deploy","z_contents","z_state","j_time","j_contents","j_state","remarks"];
    for(var j=0;j<array.length;j++){
        var id = array[j] + "_display";
        var obj = array[j] + "_check";
        var CELL = document.getElementById(id);
        cell_header[array[j]] = CELL.cellIndex;
        if( array[j] == "j_time" ) continue;
/*
        var TABLE = CELL.parentNode.parentNode.parentNode;
        for(var i=0;TABLE.rows[i];i++) {
            TABLE.rows[i].cells[CELL.cellIndex].style.display = (document.getElementById(obj).checked) ? '' : 'none';
        }
*/
    }
    for(var j=0;j<array.length;j++){
        var id = array[j] + "_display";
        var obj = array[j] + "_check";
        var CELL = document.getElementById(id);
        if( array[j] == "j_time" ) continue;
        var TABLE = CELL.parentNode.parentNode.parentNode;
        for(var i=0;TABLE.rows[i];i++) {
            TABLE.rows[i].cells[cell_header[array[j]]].style.display = (document.getElementById(obj).checked) ? '' : 'none';
        }
    }
}

// 列の表示・非表示切り替え
function checkbox_cell(obj, id)
{
    var CELL = document.getElementById(id);
    var TABLE = CELL.parentNode.parentNode.parentNode;
    var str = id.replace("_display", "");
    var index = cell_header[str];
    for(var i=0;TABLE.rows[i];i++) {
//        TABLE.rows[i].cells[CELL.cellIndex].style.display = (obj.checked) ? '' : 'none';
        TABLE.rows[i].cells[index].style.display = (obj.checked) ? '' : 'none';
//        TABLE.rows[i].cells[CELL.cellIndex].style.visibility = (obj.checked) ? '' : 'hidden';
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
