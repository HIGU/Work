//////////////////////////////////////////////////////////////////////////////
// ������ư�����ε����ȥ��󥿡��ե������Υ�졼����� �Ȳ�����ƥʥ�    //
//                      ���ϥ����å� JavaScript                             //
// Copyright (C) 2005-2005 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2005/07/29 Created   machineInterface.js                                 //
// 2005/08/03 interface �� JavaScript ��ͽ���(NN7.1)�ʤΤ� inter ���ѹ�    //
// 2005/08/04 fileNameEnable()�������form�˰�¸�����ʤ�����id='file_name'��//
//////////////////////////////////////////////////////////////////////////////

/* ����ʸ�����������ɤ��������å� */
function isDigit(str) {
    var len=str.length;
    var c;
    for(var i=0; i<len; i++) {
        c = str.charAt(i);
        if( ('0' > c) || (c > '9') ) return false;
    }
    return true;
}

/* ����ʸ�����������ɤ��������å� �������б� */
function isDigitDot(str) {
    var len = str.length;
    var c;
    var cnt_dot = 0;
    for (var i=0; i<len; i++) {
        c = str.charAt(i);
        if (c == '.') {
            if (cnt_dot == 0) {     // 1���ܤ������å�
                cnt_dot++;
            } else {
                return false;       // 2���ܤ� false
            }
        } else {
            if (('0' > c) || (c > '9')) {
                return false;
            }
        }
    }
    return true;
}

function isABC(str) {
    // var str = str.toUpperCase();    // ɬ�פ˱�������ʸ�����Ѵ�
    var len = str.length;
    var c;
    for (var i=0; i<len; i++) {
        c = str.charAt(i);
        if ((c < 'A') || (c > 'Z')) {
            if (c == ' ') continue; // ���ڡ�����OK
            return false;
        }
    }
    return true;
}


function chk_machineInterface(obj) {
    if (obj.mac_no.value.length == 0) {
        alert("�����ֹ椬���򤵤�Ƥ��ޤ���");
        obj.mac_no.focus();
        // obj.mac_no.select();
        return false;
    }
    if (!isDigit(obj.mac_no.value)) {
        alert("�����ֹ�˿����ʳ��Υǡ���������ޤ���");
        obj.mac_no.focus();
        // obj.mac_no.select();
        return false;
    }
    if ( (obj.mac_no.value < 1000) || (obj.mac_no.value > 9999) ) {
        alert("�����ֹ��1000��9999�ޤǤǤ���");
        obj.mac_no.focus();
        // obj.mac_no.select();
        return false;
    }
    if (obj.inter.value.length == 0) {
        alert("���󥿡��ե����������򤵤�Ƥ��ޤ���");
        obj.inter.focus();
        // obj.inter.select();
        return false;
    }
    if (!isDigit(obj.inter.value)) {
        alert("���󥿡��ե������˿����ʳ��Υǡ���������ޤ���");
        obj.inter.focus();
        // obj.inter.select();
        return false;
    }
    if (obj.csv.value.length == 0) {
        alert("���������꤬���򤵤�Ƥ��ޤ���");
        obj.csv.focus();
        // obj.csv.select();
        return false;
    }
    if (!isDigit(obj.csv.value)) {
        alert("����������˿����ʳ��Υǡ���������ޤ���");
        obj.csv.focus();
        // obj.csv.select();
        return false;
    }
    // file_name �ϥ����С��ǥ����å�
    return true;
}

function fileNameEnable(value) {
    if ( (value == '1') || (value == '2') ) {
        if (document.getElementById) {  // IE5.5-, NN6- NN7.1-
            document.getElementById('file_name').style.backgroundColor = 'white';
            document.getElementById('file_item').style.color = 'black';
            document.getElementById('file_name').disabled = false;
        } else if (document.all) {      // IE4-
            document.all['file_name'].style.backgroundColor = 'white';
            document.all['file_item'].style.color = 'black';
            document.all['file_name'].disabled = false;
        }
    } else {
        if (document.getElementById) {  // IE5.5-, NN6- NN7.1-
            document.getElementById('file_name').style.backgroundColor = '#d6d3ce';
            document.getElementById('file_item').style.color = 'gray';
            document.getElementById('file_name').disabled = true;
        } else if (document.all) {      // IE4-
            document.all['file_name'].style.backgroundColor = '#d6d3ce';
            document.all['file_item'].style.color = 'gray';
            document.all['file_name'].disabled = true;
        }
    }
    return true;
}

function apend_checkbox(value) {
    if (value) {
        document.apend_form.parts_no.value = '000000000';
    } else {
        document.apend_form.parts_no.value = '';
    }
}

function edit_checkbox(value) {
    if (value) {
        document.edit_form.parts_no.value = '000000000';
    } else {
        document.edit_form.parts_no.value = '';
    }
}


function chk_del_interfaceMaster(){
    var res = confirm("��������ǡ����ϸ����᤻�ޤ��󡣤�����Ǥ�����");
    return res;
}


function chk_end_inst(obj) {
    var mac_no   = obj.m_no.value;
    var name     = obj.m_name.value;
    var siji_no  = obj.s_no.value;
    var parts_no = obj.b_no.value;
    return confirm(   "�����ֹ桧" + mac_no + "\n\n"
                    + "�� �� ̾��" + name + "\n\n"
                    + "�ؼ��ֹ桧" + siji_no + "\n\n"
                    + "�����ֹ桧" + parts_no + "\n\n"
                    + "��λ���ޤ����������Ǥ�����");
}

function chk_cut_form(obj) {
    var mac_no   = obj.m_no.value;
    var name     = obj.m_name.value;
    var siji_no  = obj.s_no.value;
    var parts_no = obj.b_no.value;
    return confirm(   "�����ֹ桧" + mac_no + "\n\n"
                    + "�� �� ̾��" + name + "\n\n"
                    + "�ؼ��ֹ桧" + siji_no + "\n\n"
                    + "�����ֹ桧" + parts_no + "\n\n"
                    + "�����Ǥ��ޤ����������Ǥ�����");
}

function chk_break_del(mac_no, name, siji_no, parts_no) {
    return confirm(   "�����ֹ桧" + mac_no + "\n\n"
                    + "�� �� ̾��" + name + "\n\n"
                    + "�ؼ��ֹ桧" + siji_no + "\n\n"
                    + "�����ֹ桧" + parts_no + "\n\n"
                    + "����������ޤ����������Ǥ�����");
}

function chk_break_restart(mac_no, name, siji_no, parts_no) {
    return confirm(   "�����ֹ桧" + mac_no + "\n\n"
                    + "�� �� ̾��" + name + "\n\n"
                    + "�ؼ��ֹ桧" + siji_no + "\n\n"
                    + "�����ֹ桧" + parts_no + "\n\n"
                    + "��Ƴ����ޤ����������Ǥ�����");
}

