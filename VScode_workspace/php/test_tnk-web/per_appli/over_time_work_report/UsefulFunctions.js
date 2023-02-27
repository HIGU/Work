////////////////////////////////////////////////////////////////////////////////
//                                            MVC View 部 (JavaScriptクラス)  //
// Copyright (C) 2021-2021 Ryota.waki ryota_waki@nitto-kohki.co.jp            //
// Changed history                                                            //
// 2021/10/20 Created UsefulFunctions.js                                      //
// 2021/11/01 Release.                                                        //
////////////////////////////////////////////////////////////////////////////////

// メッセージ表示
function MsgBox(str)
{
    if( str ) alert(str);
}

// メッセージ表示
function MsgBoxConf(str)
{
    if( str ) return confirm(str);
}

// 数字連番のドロップダウンリストを生成
// p_id     生成元の id
// c_name   生成する名前
// min      最小
// max      最大
// pos      初期表示値
function CreatNumberList(p_id, c_name, min, max, pos)
{
    // 親要素を取得
    var parent = document.getElementById(p_id);
    if( ! parent) return;   // 無ければ何もしない。

    // select 要素を作成
    var select = document.createElement("select");
    select.name = "na_" + c_name;
    select.id = "id_" + c_name;
    // 親に select 要素を追加
    parent.appendChild(select);

    var option;
    for(var i = min; i <= max; i ++){
        option = document.createElement("option");  // ここで<option>要素を作成
        option.value = i;                           // optionのvalue属性を設定
        option.innerText = i;                       // リストに表示するテキストを記述
        if( i == pos ) option.selected = true;      // posで指定した数字を選択
        select.appendChild(option);                 // セレクトボックスにoptionを追加
    }
}

function TEST()
{
var test = document.getElementById("id_ddl_num");
//MsgBox(test);
/**
    // 親要素を取得
    var parent = document.getElementById('test');
    if( ! parent) return;
    
    // 要素を作成
    var elem = document.createElement('div');
    // id
    elem.id = 'child';
    // クラス名
    elem.className = 'cls';
    // テキスト内容
    elem.innerHTML = '子要素';
    
    // 要素を追加
    parent.appendChild(elem);
/**/
}
