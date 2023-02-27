////////////////////////////////////////////////////////////////////////////////
// ����ֳ���ȿ���                                                           //
//                                            MVC View �� (JavaScript���饹)  //
// Copyright (C) 2021-2021 Ryota.waki ryota_waki@nitto-kohki.co.jp            //
// Changed history                                                            //
// 2021/10/20 Created over_time_work_report.js                                //
// 2021/11/01 Release.                                                        //
////////////////////////////////////////////////////////////////////////////////
//alert("TEST : ");
function CangeUID(str, name)   // �桼�������ءʥƥ����ѡ�
{
    document.getElementsByName("login_uid")[0].value = str;
    document.getElementsByName(name)[0].submit();
}

// ���� =======================================================================

// ��ҥ��������ε�������򥻥åȤ��Ƥ�����
var holiday = "";
function SetHoliday(day)
{
    holiday = day;
}

// �����Ǥ�����
function IsHoliday(day)
{
    if( holiday.search((day.substr(0,4)+'-'+day.substr(4,2)+'-'+day.substr(6,2))) != -1 ) {
        return true;
    } else {
        return false;
    }
}

// ������ɽ��
function Youbi(obj, id)
{
    if( ! obj.value ) { // ���դ����åȤ���Ƥʤ��Ȥ������ߤ����դ򥻥å�
        var now = new Date();
        obj.value = now.getFullYear() + ('0' + (now.getMonth() + 1)).slice(-2) + ('0' + now.getDate()).slice(-2);
    }

    var w_date = obj.value;
    var hiduke = new Date(w_date.substr(0,4),w_date.substr(4,2)-1,w_date.substr(6,2));

    var week = hiduke.getDay();
    var yobi = new Array(" (��)"," (��)"," (��)"," (��)"," (��)"," (��)"," (��)");
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

// ���������դǤ�����
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

// ������ե��ԡ�
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

// ������ե��ԡ�2
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

// ����̾�����򤵤�Ƥ��ޤ�����
function DDBumon()
{
    if( document.getElementsByName("ddlist_bumon")[0].selectedIndex == 0 ) {
        document.getElementById("id_read").disabled = true;
    } else {
        document.getElementById("id_read").disabled = false;
    }
}

// ���� =======================================================================

// �Ұ�̾������ɽ��
function SetViewON()
{
    var obj = document.getElementsByName("ddlist_bumon")[0];
    if( obj.selectedIndex == 0 ) {
        obj.focus();
        obj.select();
        return;
    }

    document.getElementById("id_list_view").value = 'on';
    document.getElementsByName("form_appli")[0].submit();
}

// �Ұ�̾��������ɽ��
function SetViewOFF()
{
//    document.getElementsByName("ddlist_bumon")[0].selectedIndex = 0;

    document.getElementById("id_list_view").value = '';
    document.getElementsByName("ddlist_bumon")[0].value = '';
    document.getElementsByName("form_appli")[0].submit();
}

// ���ԡ����饸���ܥ�����
// �饸���ܥ���Υ����å���������å� �� ���ԡ�������å��ܥå����λ��Ѳ���
var back_obj  = ''; // ����Υ��ԡ��� �ݻ���
var back_obj2 = ''; // ����Υ��ԡ��� �ݻ���
var back_idx  = -1; // ����Υ���ǥå��� �ݻ���
function RadioCheck(obj, idx)
{
    // �饸���ܥ���ν���
    if( ! back_obj ) back_obj = obj;        // �饸���ܥ����Хå����åסʽ��Τߡ�
    
    if( obj.value == '' ) {     // ���򤷤��饸���ܥ���
        back_obj.value = '';    // ����饸���ܥ����ɽ���ե饰���ꥢ
        obj.value = 'on';       // �饸���ܥ����ɽ���ե饰���å�
    } else {
        obj.value = '';         // �饸���ܥ����ɽ���ե饰���ꥢ
        obj.checked = false;    // �饸���ܥ���Υ����å��򳰤�
    }
    
    if( back_obj != obj ) back_obj = obj;   // �饸���ܥ����Хå����åס�����Ȱ㤦����
    
    // �����å��ܥå����ν���
    var id_name = obj.id;
    var new_id_name = id_name.replace( 'radio', 'check' );
    var obj2 = document.getElementById(new_id_name);
    
    if( ! back_obj2 ) back_obj2 = obj2;     // �����å��ܥå�����Хå����åסʽ��Τߡ�
    
    if( ! obj2 ) return;    // �б���������å��ܥå������ʤ�
    
    var w_check = obj2.checked; // �����å��ܥå����Υ����å����֤���¸
    
    if( obj.checked ) {         // �饸���ܥ���˥����å������뤫��
        obj2.checked  = false;      // ���롧�����å��ܥå��� �����å����ꥢ
        obj2.disabled = true;       // ���롧�����å��ܥå��� �ػ�
    } else {
        obj2.disabled = false;              // �ʤ��������å��ܥå��� ���Ѳ�ǽ
        obj2.checked = back_obj2.checked;   // �ʤ���������Υ����å��ܥå���������Υ����å����֤򥻥å�
    }
    
    var obj_yo = document.getElementById('id_y_s_h' + back_idx);
    var obj_ji = document.getElementById('id_j_s_h' + back_idx);
    if( (obj_yo && obj_yo.disabled && !obj_ji) || (obj_ji && (obj_ji.disabled || obj_yo.value!=-1)) ) {  // �����ѹ� �ػ߾��֤Ǥ�����
        back_obj2.disabled = true;  // ��������å��ܥå��� �ػ�
    } else if( back_obj2 != obj2 ) {
        back_obj2.disabled = false; // ��������å��ܥå��� ���Ѳ�ǽ
        back_obj2.checked = w_check;   // ����Υ����å��ܥå����˺���Υ����å����֤򥻥å�
    }
    
    if( back_idx != idx ) back_idx = idx; // ����ǥå�����Хå����åס�����Ȱ㤦����

    if( back_obj2 != obj2 ) back_obj2 = obj2;   // �����å��ܥå�����Хå����åס�����Ȱ㤦����
}

// ���ԡ�������å��ܥå����Υ����å�����
function CheckFlag(obj)
{
    if( obj.checked ) {
        obj.value = 'on';
    } else {
        obj.value = '';
    }
}

// ���ԡ�������å��ܥå����������
function AllCheck(obj,max)
{
    for(var i=0; i<max; i++) {
        var name = 'id_check' + i;
        var obj2 = document.getElementById(name);
        if( ! obj2 ) continue;
        if( obj2.disabled ) continue;
        if( obj.value == '��,' ) {
            obj2.checked = true;
            obj2.value = 'on';
        } else {
            obj2.checked = false;
            obj2.value = '';
        }
    }
    if( obj.value == '��,' ) {
        obj.value = '��.';
    } else {
        obj.value = '��,';
    }
}

// ����(����)����������(ʣ����)�ؤΥ��ԡ�����
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
        alert("���ԡ��������ꤵ��Ƥ��ޤ���");
        return;
    }

    var copy_counter = 0;
    for(var i=0; i<max; i++) {
        if( ! document.getElementById('id_check' + i) ) continue;
        if( ! document.getElementById('id_check' + i).checked ) continue;
        // ���ԡ�����
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
        alert("���ԡ��褬���ꤵ��Ƥ��ޤ���Ǥ�����");
        return;
    } else {
//        alert(copy_counter + " ��Υ��ԡ���¹Ԥ��ޤ�����\n\n�����[��Ͽ]�򥯥�å����ʤ���\n\n�ѹ��ǡ�������¸����ޤ���!!");
        if( confirm(copy_counter + " ��Υ��ԡ���¹Ԥ��ޤ�����\n\n���ԡ���̤����Ͽ�ۤ��ޤ�����\n\n*** ��Ͽ *** �ʤ� [OK] �ܥ���\n\n���ԡ������ʤ� [����󥻥�]") ) {
            if( IsUpDate() ) {
                document.getElementById('id_appli').value = 'up';
                document.getElementsByName("form_appli")[0].submit();
            }
        }
    }
}

// �������� ���� �Ķȷ����� �� ���ԡ�
function YoteiToJisseki(str, idx)
{
    document.getElementById(str.replace('copy', 'j_s_h')).value = document.getElementById(str.replace('copy', 'y_s_h')).value;
    document.getElementById(str.replace('copy', 'j_s_m')).value = document.getElementById(str.replace('copy', 'y_s_m')).value;
    document.getElementById(str.replace('copy', 'j_e_h')).value = document.getElementById(str.replace('copy', 'y_e_h')).value;
    document.getElementById(str.replace('copy', 'j_e_m')).value = document.getElementById(str.replace('copy', 'y_e_m')).value;
    document.getElementById(str.replace('copy', 'j_g_n')).value = document.getElementById(str.replace('copy', 'z_j_r')).value;
    document.getElementById('2_' + idx).value = '�ݡ�';
}

// �������� ���� �Ķȷ����� �� ���ԡ������ơ�
function YoteiToJissekiAll(max)
{
    for( var i=0; i<max; i++ ) {
        if( document.getElementById('id_copy' + i).disabled ) continue;
        YoteiToJisseki('id_copy' + i, i);
    }
}

// �������ϤΥ����å�
function SelectInputCheck(type, max)
{
    var msg = "";       // ��å�������Ǽ��
    var n_t = new Date(); var year = n_t.getFullYear(); var month = n_t.getMonth()+1; var date = n_t.getDate();
    var content_name = "";
    if( type == 'y' ) content_name = "id_z_j_r"; else content_name = "id_j_g_n";

    for( var i=0; i<max; i++ ) {
        // �����
        var cnt = 0;
        var obj_sh = document.getElementById('id_' + type + '_s_h' + i);
        var obj_sm = document.getElementById('id_' + type + '_s_m' + i);
        var obj_eh = document.getElementById('id_' + type + '_e_h' + i);
        var obj_em = document.getElementById('id_' + type + '_e_m' + i);
        var time_ng = false; content_ng = false;
        
        if( ! obj_sh ) continue;    // ¸�ߤ��륪�֥������Ȥ������å�
        
        // ���򤵤�Ƥ���Х�����ȥ��å�
        if( obj_sh.value != -1 ) cnt++;
        if( obj_sm.value != -1 ) cnt++;
        if( obj_eh.value != -1 ) cnt++;
        if( obj_em.value != -1 ) cnt++;
        
        // ���Ͻ�λ���֤����򤵤�Ƥ�����ˤ�������ʬ��
        if( cnt == 0 ) {
            continue;   // ���ֻ��ꤵ��Ƥʤ��١����οͤ�
        } else if( cnt < 4 ) {  // ���ֻ��ꤢ�뤬����Ⱦü
                msg += document.getElementById('id_simei'+i).value + ' �͡����Ͻ�λ���֤λ��꤬����Ⱦü�Ǥ���\n';
        } else if( cnt >= 4 ) {
            // ���ֻ��ꤢ��١����Ͻ�λ�Υ����å�
            var s_t = new Date(year, month, date, obj_sh.value, obj_sm.value, 0);
            var e_t = new Date(year, month, date, obj_eh.value, obj_em.value, 0);
            if( s_t >= e_t ) time_ng = true; // ������֤����顼
            if(type == 'j' && s_t <= e_t) time_ng = false; // �Ķȥ���󥻥밷����
            // �Ķȼ»���ͳ�����å�
            if( ! document.getElementById(content_name+i).value.match(/\S/g) ) content_ng = true;
            
            if( time_ng || content_ng ) {   // ���顼��å���������
                msg += document.getElementById('id_simei'+i).value + " ��\n";
                if( time_ng ) {
                    msg += "��" + s_t.getHours() + ':' + s_t.getMinutes() + ' �� ' + e_t.getHours() + ':' + e_t.getMinutes() + " ���Ͻ�λ���֤���ž���Ƥޤ�����\n";
                }
                if( content_ng ) {
                    msg += "���Ķȼ»���ͳ�����Ϥ���Ƥ��ʤ���\n";
                }
            }
        }
    }
    return msg;
}

// ������󹹿���ǽ�Ǥ�����
function IsUpDate()
{
    var max = document.getElementById('id_rows').value;
    var msg = '';       // ��å�������Ǽ
    
    msg = SelectInputCheck('y', max);   // �������� ���� �����å�
    
    if( msg ) {
        msg = '�ʲ������λ���˸�꤬����١���Ͽ�Ǥ��ޤ���\n\n' + msg;
        alert(msg);
        return false;
    }
    
    msg = SelectInputCheck('j', max);   // �Ķȷ����� ���� �����å�
    
    if( msg ) {
        msg = '�ʲ������λ���˸�꤬����١���Ͽ�Ǥ��ޤ���\n\n' + msg;
        alert(msg);
        return false;
    }
    
    document.getElementById('id_appli').value = 'up';
    return true;
}

// ������ɲåե饰ON
function AppliAdd()
{
    document.getElementById('id_appli').value = 'add';
    return true;
}

// �����ȥե饰ON
function UpComment()
{
    document.getElementById('id_appli').value = 'comment';
    return true;
}

// ������������
function ReportEdit(obj, no, uid, uno)
{
    if( obj.value == '��λ' || obj.value == '����' ) {
        if( confirm("���ä����̤ذܹԤ��ޤ�����") ) {
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

    if( obj.value == '�ݡ�' ) {
        if( obj.id == 1 ) {
            obj_1.value = obj_2.value = obj_3.value = obj_4.value = '-1';   // ���� �����
            obj_5.value = '';   // ���� �����
        } else {
            if( obj_1.value == '-1' || obj_6.value != '-1' ) {
                obj_6.value = obj_7.value = obj_8.value = obj_9.value = '-1';   // ���� �����
                obj_10.value = '';  // ���� �����
            } else {
                obj_6.value = obj_8.value = obj_1.value;
                obj_7.value = obj_9.value = obj_2.value;
                obj_10.value = "�Ķ����פˤʤä���";
                obj.value = "���";
            }
        }
    } else if(obj.value == '���') {
        obj_6.value = obj_7.value = obj_8.value = obj_9.value = '-1';   // ���� �����
        obj_10.value = '';  // ���� �����
        obj.value = '�ݡ�';
    } else {
        if( obj.id == 1 ) {
            obj_0.disabled = obj_1.disabled = obj_2.disabled = obj_3.disabled = obj_4.disabled = obj_5.disabled = false;
        } else {
            obj_0.disabled = obj_6.disabled = obj_7.disabled = obj_8.disabled = obj_9.disabled = obj_10.disabled = false;
        }
        obj.value = '�ݡ�';
    }
}

// ���壱����ư
function setNextDate(obj)
{
    var obj_date = document.getElementById('id_w_date');
    var w_date = obj_date.value;
    var dt = new Date(w_date.substr(0,4), w_date.substr(4,2)-1, w_date.substr(6,2));
    
    if( obj.name == 'before' ) {
        dt.setDate(dt.getDate() - 1);   // ��������
    } else {
        dt.setDate(dt.getDate() + 1);   // �����û�
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

// �ܥ�����б����������֤򥻥å�
function setFixedTime(obj, no)
{
    var obj_1  = document.getElementById('id_y_s_h' + no);
    var obj_2  = document.getElementById('id_y_s_m' + no);
    var obj_3  = document.getElementById('id_y_e_h' + no);
    var obj_4  = document.getElementById('id_y_e_m' + no);

    if( obj.id == 10 ) {// alert("TEST : ��Ĺ");
        obj_1.value = 16; obj_2.value = 15; obj_3.value = 17; obj_4.value = 15;
    } else if( obj.id == 11 ) {// alert("TEST : ��ģ�");
        obj_1.value = 16; obj_2.value = 15; obj_3.value = 18; obj_4.value = 30;
    } else if( obj.id == 12 ) {// alert("TEST : ��ģ�");
        obj_1.value = 16; obj_2.value = 15; obj_3.value = 19; obj_4.value = 30;
    } else if( obj.id == 13 ) {// alert("TEST : �ģ�");
        obj_1.value = 17; obj_2.value = 30; obj_3.value = 18; obj_4.value = 30;
    } else if( obj.id == 14 ) {// alert("TEST : �ģ�");
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

var old_obj=''; // �������򥪥֥������ȵ���
// ����̾���ԡ�
function PlanCopy(obj, str)
{
//alert(str);

    if( window.clipboardData ) {
//        window.clipboardData.setData("text",obj.value);
        window.clipboardData.setData("text", str);
    } else if( navigator.clipboard ) {  // https: �Υ����Ȥ���ʤ��Ȼ��ѤǤ��ʤ���
        navigator.clipboard.writeText(obj.value);
    } else {
        alert("���Υ֥饦���Ǥϡ����ԡ���ǽ�����Բġ�\n\n���ԡ��������Ѥ��Ʋ�������");
//        console.log(navigator);
        return;
    }
    if(old_obj != '') {
        old_obj.style.backgroundColor='';
    }
    old_obj = obj;
    obj.style.backgroundColor='yellow';
}

// ��� =======================================================================
// [��ü¹�]����٤ν���
function CancelExec()
{
    var obj = document.getElementById('id_reason');  // ��ͳ�����ΰ襪�֥������ȼ���
    if( ! obj.value.match(/\S/g) ) {
        alert("�����ͳ��̤���ϤǤ���");
        return false;
    }
    return true;
}

// ���[����󥻥�]����٤ν���
function CancelCancel()
{
    document.getElementById('id_cancel_uid').value = "";
    document.getElementById('id_cancel_uno').value = "";
    return true;
}

// ��ǧ =======================================================================

// ��ǧɽ������
function AdmitDispSwitch()
{
    document.getElementsByName("admit")[0].value = "";
    document.getElementsByName("form_judge")[0].submit();
}


// ��ǧ����ǧ������
function AdmitSelect(obj, val, no)
{
    var admit_flag = 'on';  // ��ǧ����ǧ �¹ԥե饰
    var obj_radio = document.getElementsByName('radio_yo' + no);        // ��������
    var obj_reason = document.getElementById('id_yo_ng_comme' + no);    // ��ǧ��ͳ
    
    if( obj_radio.length == 0 ) {   // ���������ˤʤ��ʤ�Ķȷ�����
        obj_radio = document.getElementsByName('radio_ji' + no);        // �Ķȷ�����
        obj_reason = document.getElementById('id_ji_ng_comme' + no);    // ��ǧ��ͳ
    }
    
    if( obj_radio[0].checked ) {        // ��ǧ �����å�����
        if( obj_radio[0].value == '' ) {    // ��� �����å�
            obj_radio[0].value = 's';           // ��ǧ �������
            obj_radio[1].value = '';            // ��ǧ ���ꥢ
        } else {                            // ���� �����å��Ѥ�
            obj_radio[0].checked = false;       // ��ǧ �����å��򳰤�
            obj_radio[0].value = '';            // ��ǧ ̤�������
            admit_flag = '';                    // �¹ԤǤ��ʤ��褦 �¹ԥե饰 ���ˤ���
        }
    } else if( obj_radio[1].checked ) { // ��ǧ �����å�����
        if( obj_radio[1].value == '' ) {    // ��� �����å�
            obj_radio[1].value = 'h';           // ��ǧ �������
            obj_radio[0].value = '';            // ��ǧ ���ꥢ
        } else {                            // ���� �����å��Ѥ�
            obj_radio[1].checked = false;       // ��ǧ �����å��򳰤�
            obj_radio[1].value = '';            // ��ǧ ̤�������
            admit_flag = '';                    // �¹ԤǤ��ʤ��褦 �¹ԥե饰 ���ˤ���
        }
    } else {    // �ɤ��������å�̵��
        obj_radio[0].value = '';    // ��ǧ ̤�������
        obj_radio[1].value = '';    // ��ǧ ̤�������
        admit_flag = '';            // �¹ԤǤ��ʤ��褦 �¹ԥե饰 ���ˤ���
    }

    var pos_na = document.getElementById('id_posts').value; // 'ka' or 'bu' or 'ko'
    var obj_comme = document.getElementById('id_comment_' + pos_na + no);  // ������
    
    if( obj_radio[1].checked ) { // ��ǧ
        obj_reason.disabled = false;    // ��ǧ��ͳ ����
        if(obj_comme) obj_comme.disabled = true;      // ������ �ػ�
    } else {
        obj_reason.disabled = true;     // ��ǧ��ͳ �ػ�
        if(obj_comme) obj_comme.disabled = false;     // ������ ����
    }
}

// ��ǧ��������
function AdmitAllSelect(obj, max)
{
    var flag = true;
    if( obj.value == "��ǧ�������" ) {
        obj.value = "��ǧ�����";
    } else {
        flag = false;
        obj.value = "��ǧ�������";
    }
    
    var obj_radio = '';
    for( var i=0; i<max; i++ ) {
        obj_radio = GetRadioObj(i); // ��ǧ����ǧ �饸��
        
        if( obj_radio[0].disabled ) continue;   // ̵��������Ƥ����饹���å�
        
        if( obj_radio[0].checked == flag) continue; // ������Ȥ��ʤ��ʤ饹���å�
        
        if( !obj_radio[0].checked ) obj_radio[0].checked = true;    // ̤�����å��ʤ��դ���
        
        AdmitSelect(obj_radio, 'st', i);    // ��ǧ����ǧ�������Ԥ�
    }
}

// ���Ѳ�ǽ�ʥ饸���ܥ��󥪥֥������ȼ���
function GetRadioObj(idx)
{
    var obj = document.getElementsByName('radio_yo' + idx); // ��������
    
    if( obj.length == 0 ) obj = document.getElementsByName('radio_ji' + idx); // �Ķȷ�����
    
    return obj;
}

// �������
function AdmitExec()
{
    var msg = '';   // ��å������ΰ�
    var max = document.getElementById('id_rows_max').value;
    var pos_na = document.getElementById('id_posts').value; // 'ka' or 'bu' or 'ko'
    var obj = "";
    var obj_radio = '';
    var obj_yo = "";    // ͽ��¦ [0]��ǧ:'t'��[1]��ǧ:'h'
    var obj_ji = "";    // ����¦ [0]��ǧ:'t'��[1]��ǧ:'h'
    var no_check = 0;
    var conf = false;

    for( var i=0; i<max; i++ ) {
        obj_radio = GetRadioObj(i); // ��ǧ����ǧ �饸��
        if( ! obj_radio[0].checked && ! obj_radio[1].checked ) {
            no_check++;
            continue; // ��ǧ or ��ǧ �����򤵤�Ƥ��ޤ���
        }

        if( pos_na != 'ko' ) {
            obj = document.getElementById('id_comment_' + pos_na + i);  // ������
            if( ! obj.readOnly && ! obj.value.match(/\S/g) ) {
                if( obj_radio[0].checked ) {
                    msg = '�����Ȥ����Ϥ���Ƥ��ʤ���Τ�����ޤ���\n\n�����Ȥ�ɬ�פȤʤäƤ����Τ����Ϥ�ԤäƲ�������';
                    break;
                }
            }
        }
        if( obj_radio[1].checked ) {
            conf = true;
        }
    }
    if( max == no_check ) {
        msg = '��ǧ or ��ǧ �����򤵤�Ƥ��ޤ���\n\n����塢���٥���å����Ʋ�������';
    }
    
    if( msg ) { // ���顼��å���������
        alert(msg);
        return false;
    }
    
    if( conf ) {    // ��ǧ�����򤢤�
        if( ! confirm("��ǧ�����򤷤���Τ�\n\n��ǧ��ͳ�����Ϥ��ޤ�������") ) return false;
    }
    
    document.getElementById('id_admit').value = true; // �¹ԥե饰���å�
    return true;
}
//alert("TEST : ");

// �Ȳ� =======================================================================
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

// �Ȳ�¹�
function QuiryExec()
{
    document.getElementById('id_showMenu').value = 'Results';
}

// ============================================================================
//alert("TEST : ");
// ============================================================================

// �ڡ����ɤ߹��߻������ƤӽФ���������������ѡ�
function Init()
{
    var obj = document.getElementById("id_w_date");
    obj.value = document.getElementById("id_year").value+document.getElementById("id_month").value+document.getElementById("id_day").value;
    Youbi(obj, 'id_w_youbi');
    
    obj = document.getElementsByName("ddlist_bumon")[0];
    if( obj.length < 3 ) obj.selectedIndex = 1;
    if( obj.selectedIndex == 0 ) {
        document.getElementById("id_read").disabled = true;
    }
}

// �ڡ����ɤ߹��߻������ƤӽФ���������ʾȲ��ѡ�
function InitCancel()
{
    document.getElementById("id_reason").focus();
}

// �ڡ����ɤ߹��߻������ƤӽФ���������ʾȲ��ѡ�
function InitQuiry()
{
    var obj = document.getElementById("id_w_date");
    obj.value = document.getElementById("id_year").value+document.getElementById("id_month").value+document.getElementById("id_day").value;
    Youbi(obj, 'id_w_youbi');
    
    obj = document.getElementById("id_w_date2");
    obj.value = document.getElementById("id_year2").value+document.getElementById("id_month2").value+document.getElementById("id_day2").value;
    Youbi(obj, 'id_w_youbi2');
    
    obj = document.getElementsByName("ddlist_bumon")[0];
    if( obj.length < 3 ) obj.selectedIndex = 1;
}

/***** ����ɽ���᥽�å� *****/
/***** blink_flg Private property �������0.5��������� *****/
/***** <body onLoad='setInterval("obj.blink_disp(\"caption\")", 500)'> *****/
var blink_flag = 1;
var blink_msg  = "";
function blink_disp(id_name)
{
    if( blink_flag == 1 ) {
        // ����ͤ�ץ�ѥƥ��ǻ��ꤷ������ʲ��򥳥���
        // this.blink_msg = document.getElementById(id_name).innerHTML;
        blink_msg = document.getElementById(id_name).innerHTML;
        document.getElementById(id_name).innerHTML = "&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;";    // [00/00] ����
        blink_flag = 2;
    } else {
        document.getElementById(id_name).innerHTML = blink_msg;
        blink_flag = 1;
    }
}
