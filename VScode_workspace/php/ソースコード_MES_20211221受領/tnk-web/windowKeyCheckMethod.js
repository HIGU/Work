//////////////////////////////////////////////////////////////////////////////
// ���� ñ����(��˥塼����̵꤬��Window��)�Ѥ���ʸ���Ѵ�  JavaScript���饹//
// Copyright (C) 2007      Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2007/10/19 Created   windowKeyCheckMethod.js                             //
//////////////////////////////////////////////////////////////////////////////

var _KEYUPPERFLG = false;

/***** �����ܡ������ϥ��٥�Ƚ��� ���̥��������� *****/
/***** ñ���� *****/
function evt_key_chk(evt)
{
    // �����Х��ѿ��� backward_obj �������Υ���ȥ��륪�֥�������
    var browser = navigator.appName;
    if (browser.charAt(0) == 'M') {         // IE�ξ��
        var chk_key = event.keyCode;        // IE�Ǥϥ��������ɤ�Ĵ�٤�ˤ� event.keyCode ��Ȥ���
    } else {                                // NN�ξ�������
        var chk_key = evt.which;            // NN�Ǥ� evt.which ��Ȥ���(evt�ϥ��٥�Ȥˤ�äƸƤӽФ����ؿ��Υ��å���������)
    }
    switch (chk_key) {
    case 113:   // F2
    case 123:   // F12
        // ñ���ǤΤ��ᤳ��������submit()�Ͼ�ά���롣
    case 112:   // F1   �� �����̵���ˤ���ˤ�(onHelp='return false')IE�Τ�
    case 114:   // F3   ����
    case 116:   // F5   �����ܥ���
    case 117:   // F6   google
        if (browser.charAt(0) == 'M') {         // IE�ξ��
            event.keyCode = null;
        } else {                                // NN�ξ�������
            evt.which = null;
        }
        return false;
    default:
        if (chk_key >= 65 && chk_key <= 90) {   // A(a) �� Z(z)�ޤǡ���ʸ����ʸ���ζ��̤�����ʤ�
            _KEYUPPERFLG = true;
        } else {
            _KEYUPPERFLG = false;
        }
    }
    return true;
}
function keyInUpper(obj)
{
    if (_KEYUPPERFLG) obj.value = obj.value.toUpperCase();
    return true;
        // http://msdn.microsoft.com/library/default.asp?url=/workshop/author/dhtml/reference/methods/findtext.asp
        var rangeObj = obj.createTextRange();
        rangeObj.collapse(true);
        rangeObj.text = obj.value.toUpperCase();
}

document.onkeydown = evt_key_chk;

