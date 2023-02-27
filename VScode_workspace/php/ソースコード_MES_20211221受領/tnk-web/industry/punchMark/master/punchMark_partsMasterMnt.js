//////////////////////////////////////////////////////////////////////////////
// ������������ֹ�ޥ��������ƥʥ�   �Ȳ�����ƥʥ�                //
//                      ���ϥ����å� JavaScript                             //
// Copyright (C) 2007 Norihisa.Ohya           usoumu@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2007/07/30 Created   punchMark_partsMasterMnt.js                         //
// 2007/10/20 ����γ�ǧ��å�������punchMark_code�����ϥ����å����ɲ� ���� //
// 2007/11/10 �����ե�����ɺ�� function ���ɲ�                       ���� //
//////////////////////////////////////////////////////////////////////////////

/* ����ʸ�����������ɤ��������å� */
function isDigit(str) {
    var len = str.length;
    var c;
    for (i=0; i<len; i++) {
        c = str.charAt(i);
        if ((c < '0') || (c > '9')) {
            return false;
        }
    }
    return true;
}

/* ����ʸ��������ե��٥åȤ��ɤ��������å� isDigit()�ε� */
function isABC(str) {
    var len = str.length;
    var c;
    for (i=0; i<len; i++) {
        c = str.charAt(i);
        if ((c < 'A') || (c > 'Z')) {
            if (c == ' ') continue; // ���ڡ�����OK
            return false;
        }
    }
    return true;
}

/* ����ʸ�����������ɤ��������å� �������б� */
function isDigitDot(str) {
    var len = str.length;
    var c;
    var cnt_dot = 0;
    for (i=0; i<len; i++) {
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

/* �������ƤΥ����å��ؿ� */
function chk_entry(obj) {
    if (obj.parts_no.value.length == 0) {
        alert('�����ֹ椬���Ϥ���Ƥ��ޤ���');
        obj.parts_no.focus();
        obj.parts_no.select();
        return false;
    }
    
    if (obj.punchMark_code.value.length == 0) {
        alert('��������ɤ����Ϥ���Ƥ��ޤ���');
        obj.punchMark_code.focus();
        obj.punchMark_code.select();
        return false;
    }
    return true;
}
/* �ɲ����ϻ������ƥ����å��ؿ� */
function checkEdit(obj) {
    if (obj.parts_no.value.length == 0) {
        alert('�����ֹ椬���Ϥ���Ƥ��ޤ���');
        obj.parts_no.focus();
        obj.parts_no.select();
        return false;
    } else if (obj.parts_no.value.length > 9) {
        alert('�����ֹ�ϣ���ޤǤǤ���');
        obj.parts_no.focus();
        obj.parts_no.select();
        return false;
    }
    if (obj.punchMark_code.value.length == 0) {
        alert('��������ɤ����Ϥ���Ƥ��ޤ���');
        obj.punchMark_code.focus();
        obj.punchMark_code.select();
        return false;
    } else if (obj.punchMark_code.value.length > 6) {
        alert('�����ֹ�ϣ���ޤǤǤ���');
        obj.punchMark_code.focus();
        obj.punchMark_code.select();
        return false;
    }
    document.entry_form.entry.value = "�ɲ�";
    document.entry_form.submit();
    return true;
}
/* �ѹ����ϻ������ƥ����å��ؿ� */
function checkChange(obj) {
    if (obj.parts_no.value.length == 0) {
        alert('�����ֹ椬���Ϥ���Ƥ��ޤ���');
        obj.parts_no.focus();
        obj.parts_no.select();
        return false;
    } else if (obj.parts_no.value.length > 9) {
        alert('�����ֹ�ϣ���ޤǤǤ���');
        obj.parts_no.focus();
        obj.parts_no.select();
        return false;
    }
    if (obj.punchMark_code.value.length == 0) {
        alert('��������ɤ����Ϥ���Ƥ��ޤ���');
        obj.punchMark_code.focus();
        obj.punchMark_code.select();
        return false;
    } else if (obj.punchMark_code.value.length > 6) {
        alert('�����ֹ�ϣ���ޤǤǤ���');
        obj.punchMark_code.focus();
        obj.punchMark_code.select();
        return false;
    }
    document.entry_form.change.value = "�ѹ�";
    document.entry_form.submit();
    return true;
}
/* ����������ƥ����å��ؿ� */
function checkDelete(obj) {
    if (!obj.parts_no.value.length) {
        alert('�����ֹ椬���Ϥ���Ƥ��ޤ���');
        obj.parts_no.focus();
        obj.parts_no.select();
        return false;
    }
    if (!obj.punchMark_code.value.length) {
        alert('��������ɤ����Ϥ���Ƥ��ޤ���');
        obj.punchMark_code.focus();
        obj.punchMark_code.select();
        return false;
    }
    if (confirm("������ޤ���������Ǥ�����")) {
        document.entry_form.del.value = "���";
        document.entry_form.submit();
    }
    return true;
}

/* �����ե�����ɤ��� */
function clearKeyValue(obj) {
    obj.parts_no.value       = "";
    obj.punchMark_code.value = "";
    return true;
}

