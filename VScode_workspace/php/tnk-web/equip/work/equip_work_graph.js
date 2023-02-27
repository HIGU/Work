//////////////////////////////////////////////////////////////////////////////
// 組立機械稼動管理システムの 運転 グラフ 表示  Graph本体javascript         //
// Copyright (C) 2022-2022 ryota_waki@nitto-kohki.co.jp                     //
// Changed history                                                          //
// 2022/02/28 Created equip_work_graph.js                                   //
//////////////////////////////////////////////////////////////////////////////

//alert("TEST : ");
//console.log("TEST : ");

// 縦棒グラフデータ格納領域
var dataPoints = [];
// 積み上げ縦棒グラフデータ格納領域[201]
var stacked_data = [[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],];
// 日数別、組立数
var day_wrk_cnt = [];
// 日数別、グラフの色（※積み上げ専用）
var day_color = [];

// 年月日時分秒を分割したものを返す
//   target_data："YYYY-MM-DD hh:mm:ss"
function GetSplitDT(target_data)
{
    var split_dt = [];
    split_dt.push(target_data.substr( 0,4));    // [0] 年
    split_dt.push(target_data.substr( 5,2));    // [1] 月
    split_dt.push(target_data.substr( 8,2));    // [2] 日
    split_dt.push(target_data.substr(11,2));    // [3] 時
    split_dt.push(target_data.substr(14,2));    // [4] 分
    split_dt.push(target_data.substr(17,2));    // [5] 秒
    if( split_dt[3] == 99 && split_dt[4] == 99 && split_dt[5] == 99 ) {
        split_dt[3] = split_dt[4] = split_dt[5] = 0;
    }
    return split_dt;
}

// 年月日時分秒の分割データを元にData()を返す
//   split_dt：[0]年[1]月[2]日[3]時[4]分[5]秒
function GetDT(split_dt)
{
    return new Date(split_dt[0],split_dt[1]-1,split_dt[2],split_dt[3],split_dt[4],split_dt[5]);
}

// "YYYY/ M/ D"年月日を返す
function GetDTFormatDate(dt, year, month, days)
{
    var date = "";
    if( year ) date = dt.getFullYear();             // 年
    if( month ) {
        if( date ) date += "/";
        date += (" "+(dt.getMonth()+1)).slice(-2);  // 月
    }
    if( days ) {
        if( date ) date += "/";
        date += (" "+dt.getDate()).slice(-2);       // 日
    }
    return date;
}

// " h:mm:ss"時分秒を返す
function GetDTFormatTime(dt, hours, minutes, seconds)
{
    var time = "";
    if( hours ) time = (" "+dt.getHours()).slice(-2);   // 時
    if( minutes ) {
        if( time ) time += ":";
        time += ("0"+dt.getMinutes()).slice(-2);        // 分
    }
    if( seconds ) {
        if( time ) time += ":";
        time += ("0"+dt.getSeconds()).slice(-2);        // 秒
    }
    return time;
}

// ｘ軸：日のグラフデータ作成
//   stacked（積み上げ）：true or false
function CreateDayDP(target_data, stacked)
{
    var max = 365;  // X軸のメモリ数（仮）最大は、1年
    var split_dt = GetSplitDT(target_data[0][0]); // X軸のData()用情報を取得
    var dt_x;                                       // X軸のData()セット変数
    var dt_w = GetDT(split_dt);   // 取得データのData()を取得
    var work_cnt = bak_cnt = 0;
    
    for( var i=0, n=0; i < max; i++, split_dt[2]++ ) {
        day_wrk_cnt[i] = 0; // 組立数の初期値をセット
        if( work_cnt != 0 ) bak_cnt = work_cnt;
        dt_x = GetDT(split_dt);   // X軸をセット
        if( stacked ) {
            // 下段から前日までのデータをセットしていくループ
            for( var r=0; r<i; r++ ) {
                if( day_wrk_cnt[r] == 0 ) continue;
                stacked_data[r].push({ x : dt_x, y : day_wrk_cnt[r] });
            }
        }
        if( dt_x.getTime() == dt_w.getTime() ) {    // （年月日が同じ）
            work_cnt = parseInt(target_data[n][1],10);  // 組立数をセット
            day_wrk_cnt[i] = work_cnt - bak_cnt;        // 前日の組立数を減算した組立数をセット
            stacked_data[i].push({ x : dt_x, y : day_wrk_cnt[i] }); // 積み上げ用グラフデータをセット
            if( stacked ) {
                ;
            } else {
                dataPoints.push({ x : dt_x, y : work_cnt });        // グラフデータをセット
            }
            n++;
            if( n >= target_data.length ) break;    // 取得データの最後まで行ったらループを抜ける。
            dt_w = GetDT(GetSplitDT(target_data[n][0])); // 取得データのData()を取得
        } else {
            if( stacked ) {
                if( day_wrk_cnt[i] == 0 ) continue;
                stacked_data[i].push({ x : dt_x, y : day_wrk_cnt[i] }); // 積み上げ用グラフデータをセット
            } else {
                dataPoints.push({ x : dt_x, y : work_cnt });            // グラフデータをセット
            }
        }
    }
    if( i == max ) {
        alert( max + " 日分以降は表示できません。");
    }
}

// ｘ軸：時のグラフデータ作成
//   stacked（積み上げ）：true or false
//   g_type（グラフ）："column" = 縦棒／"area" = 面
function CreateHoursDP(target_data, stacked, g_type)
{
    var max_day = 365;
    if( g_type == "column" ) {
        max_day = 60;// 縦棒グラフでは処理に時間が掛かる為
        if( stacked ) max_day = 10;// 積み上げの縦棒グラフはさらに処理に時間が掛かる為
    }
    var max = 24 * max_day;// X軸のメモリ数 最大、1,440時間分（一本 1時間 × 24 × 60日 = 1,440）
    var split_dt = GetSplitDT(target_data[0][0]); // X軸のData()用情報を取得
    var dt_x;                                       // X軸のData()セット変数
    var dt_w = GetDT(split_dt);   // 取得データのData()を取得
    var work_cnt = bak_cnt = time_cnt = 0;
    var now_day = bak_day = -1;
    
    for( var i=0, n=0; i < max; i++, split_dt[3]++ ) {
        time_cnt = parseInt(split_dt[3],10);// 時を10進数へ変換
        if( bak_day == -1 ) max -= time_cnt;
        now_day  = parseInt(time_cnt/24);// 何日目かを計算しセット（24で割った整数部）
        if( bak_day < now_day ) {  // 日付変わったら
            bak_day = now_day;          // 何日目かを更新
            day_wrk_cnt[now_day] = 0; // 組立数の初期値をセット
            if( work_cnt != 0 ) bak_cnt = work_cnt;
        }
        dt_x = GetDT(split_dt);   // X軸をセット
        if( stacked ) {
            // 下段から前日までのデータをセットしていくループ
            for( var r=0; r<now_day; r++ ) {
                if( day_wrk_cnt[r] == 0 ) continue;
                stacked_data[r].push({ x : dt_x, y : day_wrk_cnt[r] });
            }
        }
        if( dt_x.getTime() == dt_w.getTime() ) {// （年月日時が同じ）
            work_cnt = parseInt(target_data[n][1],10);  // 対象の組立数を取得
            day_wrk_cnt[now_day] = work_cnt - bak_cnt;  // 前日の組立数を減算した組立数をセット
            stacked_data[now_day].push({ x : dt_x, y : day_wrk_cnt[now_day] });
            if( stacked ) {
                ;
            } else {
                dataPoints.push({ x : dt_x, y : work_cnt });            // グラフデータをセット
            }
            n++;
            if( n >= target_data.length ) break;// 取得データの最後まで行ったらループを抜ける。
            dt_w = GetDT(GetSplitDT(target_data[n][0])); // 取得データのData()を取得
        } else {
            if( stacked ) {
                if( day_wrk_cnt[now_day] == 0 ) continue;
                stacked_data[now_day].push({ x : dt_x, y : day_wrk_cnt[now_day] }); // 積み上げ用グラフデータをセット
            } else {
                dataPoints.push({ x : dt_x, y : work_cnt });            // グラフデータをセット
            }
        }
    }
    if( i == max ) {
        max = target_data.length;
        for( n; n<max; n++) {
            target_data.pop();
        }
        target_data[target_data.length-1][0] = GetDTFormatDate(dt_x, true, true, true) + " " + GetDTFormatTime(dt_x, true, true, true);
        alert( max_day + " 日分以降は表示できません。　" + dt_x.toLocaleDateString() + "までを表示。");
    }
}

/* TEST Start -----> */
// 時間別、組立数
var hou_wrk_cnt = [];
// ｘ軸：時の積み上げグラフデータ作成
function CreateOneDayHoursDP(target_data)
{
    var max = 24;// X軸のメモリ数 最大、24時間分（一本 1時間 × 24 = 24）
    var split_dt = GetSplitDT(target_data[0][0]); // X軸のData()用情報を取得
    var dt_x;                                       // X軸のData()セット変数
    var dt_w = GetDT(split_dt);   // 取得データのData()を取得
    
    var work_cnt = bak_cnt = 0;
    
    for( var i=0, n=0; i < max; i++, split_dt[3]++ ) {
        hou_wrk_cnt[i] = 0; // 組立数の初期値をセット
        if( work_cnt != 0 ) bak_cnt = work_cnt;
        dt_x = GetDT(split_dt);   // X軸をセット

        // 下段から前日までのデータをセットしていくループ
        for( var r=0; r<i; r++ ) {
            if( hou_wrk_cnt[r] == 0 ) continue;
            stacked_data[r].push({ x : dt_x, y : hou_wrk_cnt[r] });
        }

        if( dt_x.getTime() == dt_w.getTime() ) {// （年月日時が同じ）
            work_cnt = parseInt(target_data[n][1],10);  // 対象の組立数を取得
            hou_wrk_cnt[i] = work_cnt - bak_cnt;  // 前日の組立数を減算した組立数をセット
//if(target_data[n][2]=="dummy"){
//} else {
            stacked_data[i].push({ x : dt_x, y : hou_wrk_cnt[i] });
//}
            n++;
            if( n >= target_data.length ) break;// 取得データの最後まで行ったらループを抜ける。
            dt_w = GetDT(GetSplitDT(target_data[n][0])); // 取得データのData()を取得
        } else {
            if( hou_wrk_cnt[i] == 0 ) continue;
            stacked_data[i].push({ x : dt_x, y : hou_wrk_cnt[i] }); // 積み上げ用グラフデータをセット
        }
    }
//target_data.shift();
//hou_wrk_cnt[0]=0;
}
/* <----- TEST End. */

// 機械の状態データ
var mac_state = [//   機械状態     文字色     背景色
                    ["電源 OFF", "#FFFFFF", "#000000"], // White, Black
                    ["自動運転", "#FFFFFF", "#008000"], // White, Green
                    ["アラーム", "#FFFFFF", "#FF0000"], // White, Red
                    ["停 止 中", "#000000", "#FFFF00"], // Black, Yellow
                    ["暖 気 中", "#FFFFFF", "#800080"], // White, Purple
                    ["段 取 中", "#000000", "#00FFFF"], // Black, Aqua
                    ["故障修理", "#FFFFFF", "#808080"], // White, Gray
                    ["刃具交換", "#000000", "#C0C0C0"], // Black, Silver
                    ["無人運転", "#FFFFFF", "#0000FF"], // White, Blue
                    ["中　　断", "#000000", "#FF00FF"], // Black, Magenta
                    ["予 備 １", "#000000", "#FFA500"], // Black, Orange
                    ["予 備 ２", "#FFFFFF", "#800000"], // White, Maroon
                    ["設定なし", "#FFFFFF", "#00FF00"]  // White, Lime
                ];

// 各状態の累積時間格納領域
var state_time = [];

// ｘ軸：分の縦棒グラフデータ作成
function CreateMinColumeDP(target_data)
{
    var max = 60 * 24; // X軸のメモリ数 // 最大は、一本 1分 × 1時間（60分）× 1日（24時間）= 1,440分
    var split_dt = GetSplitDT(target_data[0][0]); // X軸のData()用情報を取得
    var dt_x = GetDT(split_dt);   // X軸のData()をセット
    var dt_w;       // 取得データのData()セット用
    
    state_time = []; // 初期化
    for( var n=0, i=0, cnt=0, state=0; i < max; i++ ) {
        if( n >= target_data.length ) break;// 取得データの最後まで行ったらループを抜ける。
        dt_w = GetDT(GetSplitDT(target_data[n][0])); // 取得データのData()を取得
        if( dt_x.getTime() == dt_w.getTime() ) {// （時分が同じ）
            cnt = parseInt(target_data[n][1],10);   // 組立数を取得
            state = parseInt(target_data[n][2],10); // 機械の状態を取得
            n++; // カウントアップ 次のデータの場所へ
        }
        if(! state_time[state]) {
            state_time[state] = 0;
        }
        state_time[state] += 60; // 60秒カウントアップ
        dataPoints.push({ x : dt_x, y : cnt, color: mac_state[state][2] });
        split_dt[4]++;            // X軸（分）カウントアップ
        dt_x = GetDT(split_dt);   // 次のX軸セット
    }
}

// 面グラフデータ格納領域[61]
var area_data = [[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],[],];
// 面グラフの色データ格納領域
var area_color = [];

// ｘ軸：分の面グラフデータ作成
function CreateMinAreaDP(target_data)
{
    var max = 60 * 24; // X軸のメモリ数 // 最大は、一本 1分 × 1時間（60分）× 1日（24時間）= 14,400分
    var split_dt = GetSplitDT(target_data[0][0]); // X軸のData()用情報を取得
    var dt_x = GetDT(split_dt);                   // X軸のData()をセット
    var dt_xb = dt_x;                               // 1つ前のX軸を保管
    var dt_w;       // 取得データのData()セット用
    var pos = 0, state_bak = -1;
    var cnt_bak = 0;

    state_time = []; // 初期化
    for( var n=0, i=0, cnt=0, state=0; i < max; i++ ) {
        if( n >= target_data.length ) break;// 取得データの最後まで行ったらループを抜ける。
        dt_w = GetDT(GetSplitDT(target_data[n][0])); // 取得データのData()を取得
        if( dt_x.getTime() == dt_w.getTime() ) {// （時分が同じ）
            cnt_bak = cnt;
            cnt = parseInt(target_data[n][1],10);   // 組立数を取得
            state = parseInt(target_data[n][2],10); // 機械の状態を取得
            if( state_bak == -1 ) state_bak = state;// 初回のみ
            n++; // カウントアップ 次のデータの場所へ
        }
        if(! state_time[state]) {
            state_time[state] = 0;
        }
        state_time[state] += 60; // 60秒カウントアップ
        area_color[pos] = mac_state[state_bak][2];
        area_data[pos].push({ x : dt_x, y : cnt, color: mac_state[state][2], markerColor: mac_state[state][2], lineColor: mac_state[state][2]}); // 面グラフデータをセット
        if( state_bak != state ) {
            area_data[pos].pop();
            state_bak = state;
            pos++;
            area_color[pos] = mac_state[state][2];
            area_data[pos].push({ x : dt_xb, y : cnt_bak, color: mac_state[state][2], markerColor: mac_state[state][2], lineColor: mac_state[state][2]}); // 面グラフデータをセット
            area_data[pos].push({ x : dt_x, y : cnt, color: mac_state[state][2], markerColor: mac_state[state][2], lineColor: mac_state[state][2]}); // 面グラフデータをセット
        }
        dt_xb = dt_x;// X軸のバックアップ
        split_dt[4]++;            // X軸（分）カウントアップ
        dt_x = GetDT(split_dt);   // 次のX軸セット
    }
}

// ｘ軸：秒の面グラフデータ作成
function CreateSecAreaDP(target_data)
{
    var max = 60 * 60 * 24; // X軸のメモリ数 // 最大は、一本 1秒 × 1分（60秒）× 1時間（60分）× 1日（24時間）= 86,400分
    var split_dt = GetSplitDT(target_data[0][0]); // X軸のData()用情報を取得
    var dt_x = GetDT(split_dt);                   // X軸のData()をセット
    var dt_xb = dt_x;                               // 1つ前のX軸を保管
    var dt_w;       // 取得データのData()セット用
    var pos = 0, state_bak = -1;
    var cnt_bak = 0;

    state_time = []; // 初期化
    for( var n=0, i=0, cnt=0, state=0; i < max; i++ ) {
        if( n >= target_data.length ) break;// 取得データの最後まで行ったらループを抜ける。
        dt_w = GetDT(GetSplitDT(target_data[n][0])); // 取得データのData()を取得
        if( dt_x.getTime() == dt_w.getTime() ) {// （時分が同じ）
            cnt_bak = cnt;
            cnt = parseInt(target_data[n][1],10);   // 組立数を取得
            state = parseInt(target_data[n][2],10); // 機械の状態を取得
            if( state_bak == -1 ) state_bak = state;// 初回のみ
            n++; // カウントアップ 次のデータの場所へ
        }
        if(! state_time[state]) {
            state_time[state] = 0;
        }
        state_time[state]++; // 秒カウントアップ
        area_color[pos] = mac_state[state_bak][2];
        area_data[pos].push({ x : dt_x, y : cnt, color: mac_state[state][2], markerColor: mac_state[state][2], lineColor: mac_state[state][2]}); // 面グラフデータをセット
        if( state_bak != state ) {
            area_data[pos].pop();
            state_bak = state;
            pos++;
            area_color[pos] = mac_state[state][2];
            area_data[pos].push({ x : dt_xb, y : cnt_bak, color: mac_state[state][2], markerColor: mac_state[state][2], lineColor: mac_state[state][2]}); // 面グラフデータをセット
            area_data[pos].push({ x : dt_x,  y : cnt,     color: mac_state[state][2], markerColor: mac_state[state][2], lineColor: mac_state[state][2]}); // 面グラフデータをセット
        }
        dt_xb = dt_x;// X軸のバックアップ
        split_dt[5]++;            // X軸（秒）カウントアップ
        dt_x = GetDT(split_dt);   // 次のX軸セット
    }
}

/*
 * 数字の書式設定（区切り）
 * @param {number|string} number 数字
 * @param {string} [delimiter=","] 区切り文字
 * @return {string} 書式設定された文字列を返す
 */
function numberFormat(number, delimiter)
{
	delimiter = delimiter || ',';

	if (isNaN(number)) return number;
	if (typeof delimiter !== 'string' || delimiter === '') return number;

	var reg = new RegExp(delimiter.replace(/\./, '\\.'), 'g');

	number = String(number).replace(reg, '');
	while (number !== (number = number.replace(/^(-?[0-9]+)([0-9]{3})/, '$1' + delimiter + '$2')));

	return number;
}

// 選択した種類のグラフを表示
//   v_type（表示）  ："lot" = ロット／"day" = 1日
//   x_type（X軸）   ："day" = 日／"hou" = 時／"min" = 分
//   g_type（グラフ）："column" = 縦棒／"area" = 面
function CreateSelectGraph(target_data, v_type, x_type, g_type)
{
    var split_dt = GetSplitDT(target_data[0][0]); // Data()用情報を取得
    var dt   = GetDT(split_dt);                                         // 開始のDate()をセット
    var dt_e = GetDT(GetSplitDT(target_data[target_data.length-1][0])); // 終了のDate()をセット
    var rang = GetDTFormatDate(dt, true, true, true);
    var axisXvalueFormatString = "H:mm";
    var xValueFormatString = "HH:mm";
    if( v_type == "day" ) {
        xValueFormatString = "M/D H:mm";
        if( target_data.length != 1 ) {
            rang += " ("+GetDTFormatTime(dt, true, true, false)+"～"+GetDTFormatTime(dt_e, true, true, false)+")";
        }
    } else {
        axisXvalueFormatString = "M/D";
        xValueFormatString = "YYYY/MM/DD";
        if( x_type == "day" ) {
            if( target_data.length != 1 ) {
                rang += " ～ "+GetDTFormatDate(dt_e, true, true, true);
            }
        } else {
            if( target_data.length != 1 ) {
                rang += " ("+GetDTFormatTime(dt, true, true, false)+") ～ "+GetDTFormatDate(dt_e, true, true, true)+" ("+GetDTFormatTime(dt_e, true, true, false)+")";
            }
            axisXvalueFormatString += " H:mm";
            xValueFormatString += " H:mm";
        }
    }
    var title = "組立数 グラフ " + rang;
    
    var interval = 0, intervalType = "";
    if( target_data.length > 1 && target_data.length < 8 ) {
/**
        interval = 1;
        intervalType = "hour";
        if( x_type == "day" ) {
            intervalType = "day";
        }
/**/
        if( x_type == "day" ) {
            dt.setDate( dt.getDate() + 7 );
            if(dt>dt_e) {
                interval = 1;
                intervalType = "day";
            }
        } else {
            dt.setHours( dt.getHours() + 7 );
            if(dt>dt_e) {
                interval = 1;
                intervalType = "hour";
            }
        }
    }
    
    var n_cnt = target_data[target_data.length-1][1];
    var z_cnt = p_cnt - n_cnt;
    var n_striplabel = "現在：" + numberFormat(n_cnt, ',');
    if( z_cnt > 0 ) {
        n_striplabel += "　計画まで：" + numberFormat(z_cnt, ',');
    } else if( z_cnt < 0 ) {
        n_striplabel += "　計画を超えています。";
    } else {
        n_striplabel += "　計画に到達しました。";
    }
    
    var chart = new CanvasJS.Chart("chartContainer",
    {
        zoomEnabled: true,
        zoomType: "xy",
        backgroundColor: "#D3D3D3", // LightGray
        animationEnabled: true,
        theme: "light2",
        title: {
            text: title
        },
        toolTip: {
            backgroundColor: "LightGray",
            borderThickness: 3,
            cornerRadius: 10,
            shared: true
        },
        axisX:{
            valueFormatString: axisXvalueFormatString,
            title: "timeline",
            interval: interval,
            intervalType: intervalType,
            crosshair: { 
                enabled: true,
                lineDashType: "dash",
                snapToDataPoint: true
            },
            gridThickness: 1
        },
        axisY: {
            title: "組立数",
            stripLines:[
                {
                    value: p_cnt,
                    showOnTop: true,
                    label: "計画："+numberFormat(p_cnt, ','),
                    labelPlacement: "outside",  //"inside", "outside"
                    labelAlign: "near",         //"far", "center", "near"
                    labelFontColor: "#FF0000",
                    labelBackgroundColor: "#FFFFFF",
                    thickness: 2,
                    color: "#FF0000"
                },
                {
                    value: n_cnt,
                    showOnTop: true,
                    label: n_striplabel,
                    labelPlacement: "inside",  //"inside", "outside"
                    labelAlign: "near",         //"far", "center", "near"
                    labelFontColor: "#000000",
                    labelBackgroundColor: "#FFFFFF",
                    thickness: 1,
                    color: "#000000"
                }
            ]
        },
        data: [
            {
                color: "#F4A460",
                type: g_type,
                markerType: "none",
                toolTipContent: "{x}<BR>組立数: {y}",
                fillOpacity: .8,
                name: "組立数",
                xValueFormatString: xValueFormatString,
                yValueFormatString: "#,##0 個",
                
                dataPoints: dataPoints
            }
        ]
    });
    chart.render(); // グラフ表示
}

// ｘ軸：分の面グラフを表示
function CreateMinAreaGraph(target_data)
{
    var dt_s = GetDT(GetSplitDT(target_data[0][0]));                    // 開始のDate()をセット
    var dt_e = GetDT(GetSplitDT(target_data[target_data.length-1][0])); // 終了のDate()をセット
    var rang = GetDTFormatDate(dt_s, true, true, true)+" ("+GetDTFormatTime(dt_s, true, true, false)+"～"+GetDTFormatTime(dt_e, true, true, false)+")";
    var title = "組立数 グラフ "+rang;
    var area = [];
    var n_cnt = target_data[target_data.length-1][1];
    var z_cnt = p_cnt - n_cnt;
    var n_striplabel = "現在：" + numberFormat(n_cnt, ',');
    if( z_cnt > 0 ) {
        n_striplabel += "　計画まで：" + numberFormat(z_cnt, ',');
    } else if( z_cnt < 0 ) {
        n_striplabel += "　計画を超えています。";
    } else {
        n_striplabel += "　計画に到達しました。";
    }
    
    for(var n=0; n<area_data.length; n++) {
        if(area_data[n]=="") break;
        area.push({
                    color: area_color[n],
                    type: "area",
                    markerType: "none",
                    toolTipContent: "{x}<BR>組立数: {y}",
                    fillOpacity: .7,
                    name: "組立数",
                    xValueFormatString: "M/D H:mm",
                    yValueFormatString: "#,##0 個",
                    dataPoints: area_data[n]
        });
    }
    
    var chart = new CanvasJS.Chart("chartContainer",
    {
        zoomEnabled: true,
        zoomType: "xy",
        backgroundColor: "#D3D3D3", // LightGray
        animationEnabled: true,
        theme: "light2",
        title: {
            text: title
        },
        toolTip: {
            backgroundColor: "LightGray",
            borderThickness: 3,
            cornerRadius: 10
        },
        axisX:{
            valueFormatString: "H:mm",
            title: "timeline",
            crosshair: { 
                enabled: true,
                lineDashType: "dash",
                snapToDataPoint: true
            },
            gridThickness: 1
        },
        axisY: {
            title: "組立数",
            stripLines:[
                {
                    value: p_cnt,
                    showOnTop: true,
                    label: "計画："+numberFormat(p_cnt, ','),
                    labelPlacement: "outside",  //"inside", "outside"
                    labelAlign: "near",         //"far", "center", "near"
                    labelFontColor: "#FF0000",
                    labelBackgroundColor: "#FFFFFF",
                    thickness: 2,
                    color: "#FF0000"
                },
                {
                    value: n_cnt,
                    showOnTop: true,
                    label: n_striplabel,
                    labelPlacement: "inside",  //"inside", "outside"
                    labelAlign: "near",         //"far", "center", "near"
                    labelFontColor: "#000000",
                    labelBackgroundColor: "#FFFFFF",
                    thickness: 1,
                    color: "#000000"
                }
            ]
        },
        data: area
    });
    chart.render(); // グラフ表示
}

// ｘ軸：秒の面グラフを表示
function CreateSecAreaGraph(target_data)
{
    var dt_s = GetDT(GetSplitDT(target_data[0][0]));                    // 開始のDate()をセット
    var dt_e = GetDT(GetSplitDT(target_data[target_data.length-1][0])); // 終了のDate()をセット
    var rang = GetDTFormatDate(dt_s, true, true, true)+" ("+GetDTFormatTime(dt_s, true, true, true)+"～"+GetDTFormatTime(dt_e, true, true, true)+")";
    var title = "組立数 グラフ "+rang;
    var area = [];
    var n_cnt = target_data[target_data.length-1][1];
    var z_cnt = p_cnt - n_cnt;
    var n_striplabel = "現在：" + numberFormat(n_cnt, ',');
    if( z_cnt > 0 ) {
        n_striplabel += "　計画まで：" + numberFormat(z_cnt, ',');
    } else if( z_cnt < 0 ) {
        n_striplabel += "　計画を超えています。";
    } else {
        n_striplabel += "　計画に到達しました。";
    }
    
    for(var n=0; n<area_data.length; n++) {
        if(area_data[n]=="") break;
        area.push({
                    color: area_color[n],
                    type: "area",
                    toolTipContent: "{x}<BR>組立数: {y}",
                    fillOpacity: .7,
                    name: "組立数",
                    xValueFormatString: "M/D H:mm:ss",
                    yValueFormatString: "#,##0 個",
                    dataPoints: area_data[n]
        });
    }
    
    var chart = new CanvasJS.Chart("chartContainer",
    {
        zoomEnabled: true,
        zoomType: "xy",
        backgroundColor: "#D3D3D3", // LightGray
        animationEnabled: true,
        theme: "light2",
        title: {
            text: title
        },
        toolTip: {
            backgroundColor: "LightGray",
            borderThickness: 3,
            cornerRadius: 10
        },
        axisX:{
            valueFormatString: "H:mm:ss",
            title: "timeline",
            crosshair: { 
                enabled: true,
                lineDashType: "dash",
                snapToDataPoint: true
            },
            gridThickness: 1
        },
        axisY: {
            title: "組立数",
            stripLines:[
                {
                    value: p_cnt,
                    showOnTop: true,
                    label: "計画："+numberFormat(p_cnt, ','),
                    labelPlacement: "outside",  //"inside", "outside"
                    labelAlign: "near",         //"far", "center", "near"
                    labelFontColor: "#FF0000",
                    labelBackgroundColor: "#FFFFFF",
                    thickness: 2,
                    color: "#FF0000"
                },
                {
                    value: n_cnt,
                    showOnTop: true,
                    label: n_striplabel,
                    labelPlacement: "inside",  //"inside", "outside"
                    labelAlign: "near",         //"far", "center", "near"
                    labelFontColor: "#000000",
                    labelBackgroundColor: "#FFFFFF",
                    thickness: 1,
                    color: "#000000"
                }
            ]
        },
        data: area
    });
    chart.render(); // グラフ表示
}

/*
 * HSLから16進数RGBへ変換
 *
 * ※ HSLはHue（色相）、Saturation（彩度）、Lightness（輝度）のそれぞれの頭文字を取った名称です。
 * 色相 = 0～360°
 * 彩度 = 0～100%
 * 輝度 = 0～100%
 *
 *                  引数名      型      説明
 * 第一引数【必須】 hue         number  色合い（hue）
 * 第二引数【必須】 saturation  number  彩度（saturation）
 * 第三引数【必須】 lightness   number  明度（lightness）
 */
function hslToRgb16( hue, saturation, lightness )
{
	var result = {
			red   : (255).toString(16),
			green : (255).toString(16),
			blue  : (255).toString(16)
		};

	if (((hue || hue === 0) && hue <= 360) && ((saturation || saturation === 0) && saturation <= 100) && ((lightness || lightness === 0) && lightness <= 100)) {
		var red   = 0,
		    green = 0,
		    blue  = 0,
		    q     = 0,
		    p     = 0,
		    hueToRgb;

		hue        = Number(hue)        / 360;
		saturation = Number(saturation) / 100;
		lightness  = Number(lightness)  / 100;

		if (saturation === 0) {
			red   = lightness;
			green = lightness;
			blue  = lightness;
		} else {
			hueToRgb = function(p, q, t) {
				if (t < 0) t += 1;
				if (t > 1) t -= 1;

				if (t < 1 / 6) {
					p += (q - p) * 6 * t;
				} else if (t < 1 / 2) {
					p = q;
				} else if (t < 2 / 3) {
					p += (q - p) * (2 / 3 - t) * 6;
				}

				return p;
			};

			if (lightness < 0.5) {
				q = lightness * (1 + saturation);
			} else {
				q = lightness + saturation - lightness * saturation;
			}
			p = 2 * lightness - q;

			red   = hueToRgb(p, q, hue + 1 / 3);
			green = hueToRgb(p, q, hue);
			blue  = hueToRgb(p, q, hue - 1 / 3);
		}

		result = {
			red   : ("0" + Math.round(red   * 255).toString(16)).slice(-2),
			green : ("0" + Math.round(green * 255).toString(16)).slice(-2),
			blue  : ("0" + Math.round(blue  * 255).toString(16)).slice(-2)
		};
	}

	return result;
}

// 積み上げグラフ表示
//   x_type（X軸）   ："day" = 日／"hou" = 時
//   g_type（グラフ）："column" = 縦棒／"area" = 面
function CreateStackedGraph(target_data, x_type, g_type)
{
    var split_dt = GetSplitDT(target_data[0][0]); // Data()用情報を取得
    var dt   = GetDT(split_dt);                                         // 開始のDate()をセット
    var dt_w = GetDT(split_dt);                                         // 開始のDate()をセット
    var dt_e = GetDT(GetSplitDT(target_data[target_data.length-1][0])); // 終了のDate()をセット
    var rang = GetDTFormatDate(dt, true, true, true);
    if( x_type == "day" ) {
        if( target_data.length != 1 ) {
            rang += " ～ "+GetDTFormatDate(dt_e, true, true, true);
        }
    } else {
        rang += " ("+GetDTFormatTime(dt, true, true, false)+")";
        if( target_data.length != 1 ) {
            rang += " ～ "+GetDTFormatDate(dt_e, true, true, true)+" ("+GetDTFormatTime(dt_e, true, true, false)+")";
        }
    }
    var title = "組立数 積み上げグラフ " + rang;
    
    var stacked = [];
    var hue = 0, color = "#FFFFFF";// white
    var base_color = [0,90,225,45,135,290,180];// HSLカラー
    var select = -1;
    var toolTipContent = GetDTFormatDate(dt, false, true, true).replace(' ', '0');
    toolTipContent = (toolTipContent).replace(' ', '0');
    var xValueFormatString = "YYYY/MM/DD";
    var valueFormatString = "M/D";
    if( x_type != "day" ) {
        xValueFormatString += " H:mm";
        valueFormatString += " H:mm";
    }
    toolTipContent += "：{y}<BR>-----------------------------<BR>累 計：#total";
    
    if( g_type == "column" ) {
        g_type = "stackedColumn";
    } else {
        g_type = "stackedArea";
    }
    
    for( var n = 0; n < day_wrk_cnt.length; n++, split_dt[2]++ ) {
        if( day_wrk_cnt[n] == 0 ) continue;// 日の組立数がない所はスキップ。
        select++;
        if( select >= base_color.length ) select = 0;
        hue = base_color[select];// 0～359
        color = "hsl(" + hue + ", 63%, 60%)";
        result = hslToRgb16(hue, 63, 60);
        day_color[n] = "#" + result.red + result.green + result.blue;
        if( stacked.length != 0 ) {
            dt = GetDT(split_dt);   // Date()をセット
            toolTipContent = GetDTFormatDate(dt, false, true, true).replace(' ', '0');
            toolTipContent = (toolTipContent).replace(' ', '0') + "：{y}";
        }
        stacked.push({
                color: color,
                type: g_type,
                markerType: "none",
                toolTipContent: toolTipContent,
                fillOpacity: .8,
                name: numberFormat(day_wrk_cnt[n], ',') + " 個",
                xValueFormatString: xValueFormatString,
                yValueFormatString: "#,##0 個",
                showInLegend: "true",
                dataPoints: stacked_data[n]
        });
    }
    
    var interval = 0, intervalType = "";
    if( target_data.length > 1 && target_data.length < 8 ) {
/**
        interval = 1;
        intervalType = "hour";
        if( x_type == "day" ) {
            intervalType = "day";
        }
/**/
        if( x_type == "day" ) {
            dt_w.setDate( dt_w.getDate() + 7 );
            if(dt_w>dt_e) {
                interval = 1;
                intervalType = "day";
            }
        } else {
            dt_w.setHours( dt_w.getHours() + 7 );
            if(dt_w>dt_e) {
                interval = 1;
                intervalType = "hour";
            }
        }
    }
    
    var n_cnt = target_data[target_data.length-1][1];
    var z_cnt = p_cnt - n_cnt;
    var n_striplabel = "現在：" + numberFormat(n_cnt, ',');
    if( z_cnt > 0 ) {
        n_striplabel += "　計画まで：" + numberFormat(z_cnt, ',');
    } else if( z_cnt < 0 ) {
        n_striplabel += "　計画を超えています。";
    } else {
        n_striplabel += "　計画に到達しました。";
    }
    
    var chart = new CanvasJS.Chart("chartContainer",
    {
        zoomEnabled: true,
        zoomType: "xy",
        backgroundColor: "#D3D3D3", // LightGray
        animationEnabled: true,
        theme: "light2",
        title:{
            text: title
        },
/* 凡例クリックで非表示・表示の切替え *
        legend: {
            cursor: "pointer",
            itemclick: function (e) {
                //console.log("legend click: " + e.dataPointIndex);
                //console.log(e);
                if (typeof (e.dataSeries.visible) === "undefined" || e.dataSeries.visible) {
                    e.dataSeries.visible = false;
                } else {
                    e.dataSeries.visible = true;
                }
                e.chart.render();
            }
        },
/**/
        toolTip: {
            borderThickness: 3,
            cornerRadius: 10,
            reversed: true,
            shared: true
        },
        axisX:{
            valueFormatString: valueFormatString,
            title: "timeline",
            interval: interval,
            intervalType: intervalType,
            crosshair: { 
                enabled: true,
                lineDashType: "dash",
                snapToDataPoint: true
            },
            gridThickness: 1
        },
        axisY: {
            title: "組立数",
            stripLines:[
                {
                    value: p_cnt,
                    showOnTop: true,
                    label: "計画："+numberFormat(p_cnt, ','),
                    labelPlacement: "outside",  //"inside", "outside"
                    labelAlign: "near",         //"far", "center", "near"
                    labelFontColor: "#FF0000",
                    labelBackgroundColor: "#FFFFFF",
                    thickness: 2,
                    color: "#FF0000"
                },
                {
                    value: n_cnt,
                    showOnTop: true,
                    label: n_striplabel,
                    labelPlacement: "inside",  //"inside", "outside"
                    labelAlign: "near",         //"far", "center", "near"
                    labelFontColor: "#000000",
                    labelBackgroundColor: "#FFFFFF",
                    thickness: 1,
                    color: "#000000"
                }
            ]
        },
        data: stacked
    });
    chart.render(); // グラフ表示
}

/* TEST Start -----> */
// 1日の積み上げグラフ表示
//   g_type（グラフ）："column" = 縦棒／"area" = 面
function CreateOneDayStackedGraph(target_data, g_type)
{
    var split_dt = GetSplitDT(target_data[0][0]); // Data()用情報を取得
    var dt   = GetDT(split_dt);                                         // 開始のDate()をセット
    var dt_e = GetDT(GetSplitDT(target_data[target_data.length-1][0])); // 終了のDate()をセット
    var rang = GetDTFormatDate(dt, true, true, true);
    if( target_data.length != 1 ) {
        rang += " ("+GetDTFormatTime(dt, true, true, false)+" ～ "+GetDTFormatTime(dt_e, true, true, false)+")";
    }
    var title = "組立数 積み上げグラフ " + rang;
    
    var stacked = [];
    var hue = 0, color = "#FFFFFF";// white
    var base_color = [0,90,225,45,135,290,180];// HSLカラー
    var select = -1;
//    var toolTipContent = GetDTFormatDate(dt, false, true, true)+" "+GetDTFormatTime(dt, true, true, false);
//    var toolTipContent = GetDTFormatTime(dt, true, true, false);
    var toolTipContent = GetDTFormatTime(dt, true, true, false).replace(' ', '0');
    toolTipContent = (toolTipContent).replace(' ', '0');

    toolTipContent += "／{y}<BR>-----------------------------<BR>累 計：#total";
    
    if( g_type == "column" ) {
        g_type = "stackedColumn";
    } else {
        g_type = "stackedArea";
    }
    
    for( var n = 0; n < hou_wrk_cnt.length; n++, split_dt[3]++ ) {
        if( hou_wrk_cnt[n] == 0 ) continue;// 日の組立数がない所はスキップ。
        select++;
        if( select >= base_color.length ) select = 0;
        hue = base_color[select];// 0～359
        color = "hsl(" + hue + ", 63%, 60%)";
        result = hslToRgb16(hue, 63, 60);
        day_color[n] = "#" + result.red + result.green + result.blue;
        if( stacked.length != 0 ) {
            dt = GetDT(split_dt);   // Date()をセット
//            toolTipContent = GetDTFormatDate(dt, false, true, true)+" "+GetDTFormatTime(dt, true, true, false)+"：{y}";
//            toolTipContent = GetDTFormatTime(dt, true, true, false).replace(' ', '0')+"／{y}";
            toolTipContent = GetDTFormatTime(dt, true, true, false).replace(' ', '0');
            toolTipContent = (toolTipContent).replace(' ', '0') + "／{y}";
        }
        stacked.push({
                color: color,
                type: g_type,
                markerType: "none",
                toolTipContent: toolTipContent,
                fillOpacity: .8,
                name: numberFormat(hou_wrk_cnt[n], ',') + " 個",
                xValueFormatString: " H:mm",
                yValueFormatString: "#,##0 個",
                showInLegend: "true",
                dataPoints: stacked_data[n]
        });
    }
    
    var interval = 0, intervalType = "";
    if( target_data.length > 1 && target_data.length < 8 ) {
        interval = 1;
        intervalType = "hour";
    }
    
    var n_cnt = target_data[target_data.length-1][1];
    var z_cnt = p_cnt - n_cnt;
    var n_striplabel = "現在：" + numberFormat(n_cnt, ',');
    if( z_cnt > 0 ) {
        n_striplabel += "　計画まで：" + numberFormat(z_cnt, ',');
    } else if( z_cnt < 0 ) {
        n_striplabel += "　計画を超えています。";
    } else {
        n_striplabel += "　計画に到達しました。";
    }
    
    var chart = new CanvasJS.Chart("chartContainer",
    {
        zoomEnabled: true,
        zoomType: "xy",
        backgroundColor: "#D3D3D3", // LightGray
        animationEnabled: true,
        theme: "light2",
        title:{
            text: title
        },
        toolTip: {
            borderThickness: 3,
            cornerRadius: 10,
            reversed: true,
            shared: true
        },
        axisX:{
            valueFormatString: " H:mm",
            title: "timeline",
            interval: interval,
            intervalType: intervalType,
            crosshair: { 
                enabled: true,
                lineDashType: "dash",
                snapToDataPoint: true
            },
            gridThickness: 1
        },
        axisY: {
            title: "組立数",
            stripLines:[
                {
                    value: p_cnt,
                    showOnTop: true,
                    label: "計画："+numberFormat(p_cnt, ','),
                    labelPlacement: "outside",  //"inside", "outside"
                    labelAlign: "near",         //"far", "center", "near"
                    labelFontColor: "#FF0000",
                    labelBackgroundColor: "#FFFFFF",
                    thickness: 2,
                    color: "#FF0000"
                },
                {
                    value: n_cnt,
                    showOnTop: true,
                    label: n_striplabel,
                    labelPlacement: "inside",  //"inside", "outside"
                    labelAlign: "near",         //"far", "center", "near"
                    labelFontColor: "#000000",
                    labelBackgroundColor: "#FFFFFF",
                    thickness: 1,
                    color: "#000000"
                }
            ]
        },
        data: stacked
    });
    chart.render(); // グラフ表示
}
/* <----- TEST End. */

// ロットのグラフを表示
//   target_data[]：[0]日時[1]組立数
//   x_type（X軸）："day" = 日／"hou" = 時間
//   g_type（グラフ）："column" = 縦棒／"area" = 面
function LotGraph(target_data, x_type, g_type)
{
    switch (x_type) {
        // X軸：日
        case "day": CreateDayDP(target_data, false); break;             // グラフデータ作成
        // X軸：時
        case "hou": CreateHoursDP(target_data, false, g_type); break;   // グラフデータ作成
        // それ以外はエラー
        default: alert("指定された ｘ軸（"+x_type+"）のグラフは存在しません。"); return;
    }
    CreateSelectGraph(target_data, "lot", x_type, g_type);              // グラフ表示
}

// 1日のグラフを表示
//   target_data[]：[0]日時[1]組立数[2]機械の状態
//   x_type（X軸）："hou" = 時間／"min" = 分／"sec" = 秒
//   g_type（グラフ）："column" = 縦棒／"area" = 面
function OneDayGraph(target_data, x_type, g_type)
{
    switch (x_type) {
        case "hou":// X軸：時
            CreateHoursDP(target_data, false , g_type);                 // グラフデータ作成
            CreateSelectGraph(target_data, "day", x_type, g_type);      // グラフ表示
            day_wrk_cnt = ""; // 1日の情報を表示したいのでリセットしておくこと。
            break;
        case "min":// X軸：分
            if( g_type == "column" ) {
                CreateMinColumeDP(target_data);                         // グラフデータ作成
                CreateSelectGraph(target_data, "day", x_type, g_type);  // グラフ表示
            } else {
                CreateMinAreaDP(target_data);       // グラフデータ作成
                CreateMinAreaGraph(target_data);    // グラフ表示
            }
            break;
        case "sec":// X軸：秒
            CreateSecAreaDP(target_data);       // グラフデータ作成
            CreateSecAreaGraph(target_data);    // グラフ表示
            break;
        default:// それ以外はエラー
            alert("指定された ｘ軸（"+x_type+"）のグラフは存在しません。");
            break;
    }
}

// 積み上げのグラフを表示
//   target_data[]：[0]日時[1]組立数
//   x_type（X軸）："day" = 日／"hou" = 時間
//   g_type（グラフ）："column" = 縦棒／"area" = 面
function StackedGraph(target_data, x_type, g_type)
{
    switch (x_type) {
        // X軸：日
        case "day": CreateDayDP(target_data, true); break;          // グラフデータ作成
        // X軸：時
        case "hou": CreateHoursDP(target_data, true, g_type); break;// グラフデータ作成
        // それ以外はエラー
        default: alert("指定された ｘ軸（"+x_type+"）のグラフは存在しません。"); return;
    }
    CreateStackedGraph(target_data, x_type, g_type);                // グラフ表示
}

/* TEST Start -----> */
function OneDayStackedGraph(target_data, g_type)
{
//    test = ["2022-03-03 06:00:00", "6059", "dummy"];
//    target_data.unshift(test);
    CreateOneDayHoursDP(target_data);// グラフデータ作成
    CreateOneDayStackedGraph(target_data, g_type);// グラフ表示
    alert("TEST中：1つ目に、前日の組立数が含まれてしまう。※含めないと積み上がらない。");
}
/* <----- TEST End. */

var p_cnt = 0; // 計画数
// グラフ表示
function GraphDisplay(target_data, no, plan_cnt)
{
    p_cnt = plan_cnt;
    switch (no) {
        // ロット
        case  9: LotGraph(target_data, "day", "column");        break;// X軸：日 縦棒
        case 10: LotGraph(target_data, "day", "area");          break;// X軸：日 面
        case 14: LotGraph(target_data, "hou", "column");        break;// X軸：時 縦棒
        case 15: LotGraph(target_data, "hou", "area");          break;// X軸：時 面
        // 1日
        case 19: OneDayGraph(target_data, "hou", "column");     break;// X軸：時 縦棒
        case 20: OneDayGraph(target_data, "hou", "area");       break;// X軸：時 面
        case 24: OneDayGraph(target_data, "min", "column");     break;// X軸：分 縦棒
        case 25: OneDayGraph(target_data, "min", "area");       break;// X軸：分 面
        case 30: OneDayGraph(target_data, "sec", "area");       break;// X軸：秒 面
        // 積み上げ
        case 34: StackedGraph(target_data, "day", "column");    break;// X軸：日 縦棒
        case 35: StackedGraph(target_data, "day", "area");      break;// X軸：日 面
        case 40: StackedGraph(target_data, "hou", "column");    break;// X軸：時 縦棒
        case 45: StackedGraph(target_data, "hou", "area");      break;// X軸：時 面
        // 1日 積み上げ
        case 50: OneDayStackedGraph(target_data, "column");     break;// X軸：時 縦棒
        // 設定なし不明は、エラーメッセージ表示
        default: alert("指定グラフ no（equip_xtime = " + no +"）は、不明です。"); break;
    }
}

// 日付別組立数の表をつくる
function StackedTable(divname, target_data, plan_cnt)
{
    if( day_wrk_cnt.length == 0 ) return;  // 日数別、組立数ないならスキップ。

    var line_idx = 0, line_max = 3; // [0]日付 [1]組立数 [2]累計数
    var c = column_idx = 0, column_max = day_wrk_cnt.length;// 日数を格納
    var split_dt = GetSplitDT(target_data[0][0]); // Data()用情報を取得
    var dt;
    var rows=[];// 行の領域
    var table = document.createElement("table");
    table.style.backgroundColor="Gray";
    table.style.borderStyle="groove";
    var cell_text = "";
    var hue = 0, color = "#FFFFFF";// White
    var total = 0;
    var line_name = ["【 日付 】","【 単日 】","【 累計 】"];
    
    // 日付の組立数、累計をセット
    for( line_idx = 0; line_idx < line_max; line_idx++ ) {
        rows.push(table.insertRow(line_idx));   // ★行の追加
        cell = rows[line_idx].insertCell(0);        // 列へセルを追加
        cell.style.fontSize = 15;                   // 文字サイズ
        cell.style.borderStyle = "groove";          // 枠線
        cell.style.backgroundColor = "LightGray";   // 背景色
        cell.style.fontWeight = "bold";             // 太字
        cell.style.whiteSpace = "nowrap";           // 折り返し禁止
        cell_text = line_name[line_idx];
        cell.appendChild(document.createTextNode(cell_text));   // セルへ書き込む
        for( column_idx=1, c=0; c < column_max; c++, split_dt[2]++ ) {
            if( stacked_data[c].length == 0 ) continue;// 日のデータがない所はスキップ。
            cell = rows[line_idx].insertCell(column_idx);   // 列へセルを追加
            column_idx++
            cell.align = "Right";                       // 文字水平位置
            cell.style.fontSize = 15;                   // 文字サイズ
            cell.style.borderStyle = "groove";          // 枠線
            cell.style.backgroundColor = "LightGray";   // 背景色
            cell.style.fontWeight = "bold";             // 太字
            cell.style.width = "50";                    // 列の幅
            cell.style.whiteSpace = "nowrap";           // 折り返し禁止
            if( line_idx == 0 ) {           // 1行目 日付
                dt = GetDT(split_dt);   // Date()をセット
                cell_text = GetDTFormatDate(dt, false, true, true);
            } else if( line_idx == 1 ) {    // 2行目 組立数
                color = day_color[c];
                cell.style.backgroundColor = color; // 背景色
                cell_text = numberFormat(day_wrk_cnt[c], ',');
            } else {    // 3行目 累計数
                total += day_wrk_cnt[c];
                if( total > plan_cnt ) {
                    cell.style.color = "red";       // 文字色
                }
                cell_text = numberFormat(total, ',');
            }
            cell.appendChild(document.createTextNode(cell_text));   // セルへ書き込む
        }
    }
    // 最後の列へ、計画数を追加
    for( line_idx = 1; line_idx < line_max; line_idx++ ) {
        cell = rows[line_idx].insertCell(column_idx);   // 列へセルを追加
        cell.style.fontSize = 15;                   // 文字サイズ
        cell.style.borderStyle = "groove";          // 枠線
        cell.style.backgroundColor = "LightGray";   // 背景色
        cell.style.fontWeight = "bold";             // 太字
        cell.style.whiteSpace = "nowrap";           // 折り返し禁止
        if( line_idx == 1 ) {
            cell_text = "【 計画 】";
        } else {
            cell.align = "Right";               // 文字水平位置
            cell_text = numberFormat(plan_cnt, ',');
        }
        cell.appendChild(document.createTextNode(cell_text));   // セルへ書き込む
    }

    document.getElementById(divname).appendChild(table);    // 指定したdiv要素に表を加える
}

// 累積時間の表をつくる
function StateTimeTable(divname, graph_cnt, total_cnt, plan_cnt)
{
    if( day_wrk_cnt.length != 0 ) return;  // 日数別、組立数があるならスキップ。
    
    if( graph_cnt == 0 ) graph_cnt = "---";
    
    var time_m = time_h = 0;
    var rows=[];// 行の領域
    var table = document.createElement("table");
    table.style.backgroundColor="Gray";
    table.style.borderStyle="groove";

    var line_idx = 0;// 行インデックス
    var line_max = 2;// 行数
    var column_idx = 0;// 列インデックス
    var column_max = 3;// 列数 [組立数（１日）][組立数（累計）][計画数] + 機械の状態

    var cell_text = "";
    
    // 組立数（１日）、組立数（累計）、計画数
    var cnt_name = ["【 組立数（１日）】","【 組立数（累計）】","【 計 画 数 】"];
    var cnt_data = ["　"+numberFormat(graph_cnt, ',')+" 個",
                    "　"+numberFormat(total_cnt, ',')+" 個",
                    "　"+numberFormat(plan_cnt, ',') +" 個"
                   ];
    for( line_idx = 0; line_idx < line_max; line_idx++ ) {
        rows.push(table.insertRow(line_idx));   // ★行の追加
        for( column_idx=0; column_idx < column_max; column_idx++ ) {
            cell = rows[line_idx].insertCell(column_idx);   // 列へセルを追加
            cell.style.fontSize = 15;                   // 文字サイズ
            cell.style.borderStyle = "groove";          // 枠線
            cell.style.backgroundColor = "LightGray";   // 背景色
            cell.style.whiteSpace = "nowrap";           // 折り返し禁止
            if( line_idx == 0) {    // 1行目 項目名
                cell_text = cnt_name[column_idx];
            } else {                // 2行目 個数
                cell.style.fontWeight = "bold";     // 太字
                cell.align = "Right";               // 文字水平位置
                cell_text = cnt_data[column_idx];
                if( column_idx == 1 && total_cnt > plan_cnt ) {
                    cell.style.color = "red";       // 文字色
                }
            }
            cell.appendChild(document.createTextNode(cell_text));   // セルへ書き込む
        }
    }
    
    if( state_time.length != 0 ) {  // 累積時間があれば、機械の状態を表示する。
        // 機械の状態
        var boundary_column = ["【 状 態 名 】","累積時間 Ｈ（Ｍ）"];
        for( line_idx = 0; line_idx < line_max; line_idx++ ) {
            column_idx = column_max;    // 列の初期値をセット
            cell = rows[line_idx].insertCell(column_idx);   // 列へセルを追加
            cell.align = "center";                  // 文字水平位置
            cell.style.fontSize = 15;               // 文字サイズ
            cell.style.fontWeight = "bold";         // 太字
            cell.style.borderStyle = "groove";      // 枠線
            cell.style.backgroundColor = "white";   // 背景色
            cell.style.whiteSpace = "nowrap";       // 折り返し禁止
            cell.appendChild(document.createTextNode(boundary_column[line_idx]));   // セルへ書き込む
            for( j=0, column_idx++; j < state_time.length; j++ ) {
                if( ! state_time[j] ) continue; // 空の所はスキップ
                cell = rows[line_idx].insertCell(column_idx);   // 列へセルを追加
                cell.align = "center";                      // 文字水平位置
                cell.style.fontSize = 15;                   // 文字サイズ
                cell.style.fontWeight = "bold";             // 太字
                cell.style.borderStyle = "groove";          // 枠線
                cell.style.backgroundColor = "LightGray";   // 背景色
                cell.style.whiteSpace = "nowrap";           // 折り返し禁止
                if( line_idx == 0 ) {   // 1行目：機械の状態
                    cell.style.color = mac_state[j][1];             // 文字の色
                    cell.style.backgroundColor = mac_state[j][2];   // 背景色
                    cell_text = "【 " + mac_state[j][0] + " 】";    // 機械の状態
                } else {                // 2行目：各累積時間
                    time_m = (state_time[j]/60).toFixed(2);
                    time_h = (Math.round((time_m/60)*100)/100).toFixed(2);
                    cell_text = numberFormat(time_h, ',') + "（" + numberFormat(time_m, ',') + "）"; // 累積時間
                }
                cell.appendChild(document.createTextNode(cell_text));   // セルへ書き込む
                column_idx++;// カウントアップ次のセルへ
            }
        }
    }
    document.getElementById(divname).appendChild(table);    // 指定したdiv要素に表を加える
}
