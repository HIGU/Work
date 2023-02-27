//////////////////////////////////////////////////////////////////////////////
// ������������ޥ��������ƥʥ�   �Ȳ�����ƥʥ�                    //
//                      ���ϥ����å� JavaScript                             //
// Copyright (C) 2007 Norihisa.Ohya           usoumu@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2007/07/24 Created   punchMark_shapeMasterMnt.js                         //
// 2007/10/20 ����γ�ǧ��å��������ɲ�                               ���� //
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
    if (obj.shape_code.value.length == 0) {
        alert('�����������ɤ����Ϥ���Ƥ��ޤ���');
        obj.shape_code.focus();
        obj.shape_code.select();
        return false;
    } else if ( !(isDigit(obj.shape_code.value)) ) {
        alert('�����������ɤϿ����ʳ����Ͻ���ޤ���');
        obj.shape_code.focus();
        obj.shape_code.select();
        return false;
    }
    
    if (obj.shape_name.value.length == 0) {
        alert('������̾�����Ϥ���Ƥ��ޤ���');
        obj.shape_name.focus();
        obj.shape_name.select();
        return false;
    }
    return true;
}
/* �ɲ����ϻ������ƥ����å��ؿ� */
function checkEdit(obj) {
    if (obj.shape_code.value.length == 0) {
        alert('�����������ɤ����Ϥ���Ƥ��ޤ���');
        obj.shape_code.focus();
        obj.shape_code.select();
        return false;
    } else if (obj.shape_code.value.length > 3) {
        alert('�����������ɤϣ���ޤǤǤ���');
        obj.shape_code.focus();
        obj.shape_code.select();
        return false;
    } else if ( !(isDigit(obj.shape_code.value)) ) {
        alert('�����������ɤϿ����ʳ����Ͻ���ޤ���');
        obj.shape_code.focus();
        obj.shape_code.select();
        return false;
    }
    
    if (obj.shape_name.value.length == 0) {
        alert('������̾�����Ϥ���Ƥ��ޤ���');
        obj.shape_name.focus();
        obj.shape_name.select();
        return false;
    }
    document.entry_form.entry.value = "�ɲ�";
    document.entry_form.submit();
    return true;
}
/* ����������ƥ����å��ؿ� */
function checkDelete(obj) {
    if (obj.shape_code.value.length == 0) {
        alert('�����������ɤ����Ϥ���Ƥ��ޤ���');
        obj.shape_code.focus();
        obj.shape_code.select();
        return false;
    } else if ( !(isDigit(obj.shape_code.value)) ) {
        alert('�����������ɤϿ����ʳ����Ͻ���ޤ���');
        obj.shape_code.focus();
        obj.shape_code.select();
        return false;
    }
    if (confirm("������ޤ���������Ǥ�����")) {
        document.entry_form.del.value = "���";
        document.entry_form.submit();
    }
    return true;
}
