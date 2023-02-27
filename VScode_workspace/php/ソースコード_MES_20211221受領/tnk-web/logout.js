//////////////////////////////////////////////////////////////////////////////
// logout.php �� JavaScript���饹                                           //
// Copyright (C) 2005-2006 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2005/09/02 Created    logout.js                                          //
// 2005/09/07 ��λ���ν�����JavaScript�ǥ��饤����Ȥβ��̥���������¸����  //
//            Cookie��winW/winH �����פˤʤä�                              //
// 2005/09/09 setCookie()/getCookie��setArrayCookie()/getArrayCookie()���ѹ�//
// 2006/02/21 SaveSize()�᥽�åɤ� h -= 19; �� h -= 20; ���ѹ�(Ĵ�����ѹ�)  //
// 2006/02/24 ���饹�᥽�åɤε��ҥ�������򵼻�(̾���ʤ�)function()�����ѹ�//
//////////////////////////////////////////////////////////////////////////////

///// �����Х��ѿ� _GDEBUG �ν���ͤ򥻥å�(��꡼������false�˥��åȤ���)
var _GDEBUG = false;

/****************************************************************************
/*               logout class   base_class�γ�ĥ���饹���                  *
/****************************************************************************
class logout extends base_class
*/
///// �����ѡ����饹�ηѾ�
logout.prototype = new base_class();   // base_class �ηѾ�
///// Class & Constructer �����
function logout()
{
    /***********************************************************************
    *                           Private properties                         *
    ***********************************************************************/
    this.win_name = "win";              // ���֥�����ɥ���window̾
    this.winX     = 0;                  // X����Window���� ����� window.screenLeft;�ϻȤ��ʤ�
    this.winY     = 0;                  // Y����Window���� ����� window.screenTop; �ϻȤ��ʤ�
    this.offX     = 0;                  // Window��ScreenOffset�� X��
    this.offY     = 0;                  // Window��ScreenOffset�� Y��
    this.winW     = 0;                  // Window����
    this.winH     = 0;                  // Window�ι⤵
    
    /************************************************************************
    *                           Public methods                              *
    ************************************************************************/
    /***** ���֥�����ɥ�̾��������� *****/

    logout.prototype.getWinName = function ()
    {
        if (window.name) {
            // window.defaultStatus ����¸���Ƥ����Τ�Ȥ����ºݤˤ�frame(iframe)��unLoad�����������ͤϤʤ��ʤ�
            // this.win_name = window.defaultStatus;
            this.win_name = window.name;
        } else {
            this.win_name = "win";
        }
    }
    /***** ������ɥ����֤�Offset�ͤ�������� *****/
    logout.prototype.getWinOffset = function ()
    {
        ///// �׻��ǻ��Ѥ��뤿����ͷ����Ѵ�
        this.offX = (this.getArrayCookie(this.win_name, "offX") - 0);
        this.offY = (this.getArrayCookie(this.win_name, "offY") - 0);
        this.winW = (this.getArrayCookie(this.win_name, "winW") - 0);
        this.winH = (this.getArrayCookie(this.win_name, "winH") - 0);
    }
    
    /***** Window ���֤���¸���� *****/
    logout.prototype.SaveLocate = function ()
    {
        if (document.all) {                         // IE4-
            // IE��frame�ˤ��Ƥ����10000���ͤ��֤����ᡢ���Υ��å�����¸����
            this.winX = window.screenLeft;          // ���߰��֤���¸
            this.winY = window.screenTop;
                // var msg = '';
                // for (var i in window) {
                //     msg += i + "=>" + window[i] + "\n";
                // }
                // alert(msg);
                // alert("�֥쥤��\n\n this.winX=" + this.winX + "\n\n this.winY=" + this.winY);
            // IE�ξ���ɬ��offset�ͤ�ɬ��
            this.winX -= this.offX;
            this.winY -= this.offY;
                // alert("�֥쥤��\n\n this.winX=" + this.winX + "\n\n this.winY=" + this.winY);
        } else if (document.getElementById) {               // NN6-
            this.winX = window.screenX;            // ���߰��֤���¸
            this.winY = window.screenY;
            // NN�ξ��Ϻ��ν�Offset�ͤ�ɬ�פʤ�������Τ���
            this.winX -= this.offX;
            this.winY -= this.offY;
        }
        ///// X,Y���Υ��顼�����å�������
        var w = screen.availWidth;        // ���饤����Ȥβ�����
        var h = screen.availHeight;       // ���饤����Ȥβ��̹⤵
            // �����ͤ˷׻��ǻ��Ѥ����������äƿ��ͷ����Ѵ������
            /***** menu_frame.js(menu_window.js)�Ǥ��������˰ʲ���Ʊ�������å��򤷤Ƥ���(Double check) *****/
        var maxX = w - this.winW - (this.offX * 2);
        var maxY = h - this.winH - this.offY;
            // alert("maxX=" + maxX + " w=" + w + " winW=" + this.winW + " offX=" + this.offX);
            // alert("maxY=" + maxY + " h=" + h + " winH=" + this.winH + " offY=" + this.offY);
        if (this.winX > maxX) this.winX = maxX;
        if (this.winY > maxY) this.winY = maxY;
        if (this.winX < 0) this.winX = 0;
        if (this.winY < 0) this.winY = 0;
        ///// X,Y���ΰ��� ��¸
        this.setArrayCookie(this.win_name, "winX", this.winX);    // X����Window���֤���¸
        this.setArrayCookie(this.win_name, "winY", this.winY);    // Y����Window���֤���¸
    }
    
    /***** Window �Υ���������¸���� *****/
    logout.prototype.SaveSize = function ()
    {
        if (document.all) {                         // IE4-
            // var w  = document.body.clientWidth  + (this.offX * 2);
            // var h  = document.body.clientHeight + (this.offY + this.offX);
            var w  = document.body.clientWidth;
            var h  = document.body.clientHeight;
            h -= 20;     // window.statusbar.visible(NN)���ơ������С���ɽ������ɽ���Υ����å�������ʤ����ᶯ��
            // �嵭��h�Ͼ�¦�Υ�˥塼ɽ����offY�Ȳ�¦�Υܡ�����ʬ��offX(Y��Ʊ���Ȳ��ꤷ��)��­���Ƥ���
        } else if (document.getElementById) {       // NN6-
            var w = window.outerWidth - 8;
            var h = window.outerHeight - 50;
        }
        ///// X,Y���ΰ��� ��¸
        this.winW = w;  // ���С��ե�����ɤ���¸
        this.winH = h;
        this.setArrayCookie(this.win_name, "winW", w);     // Window��������¸
        this.setArrayCookie(this.win_name, "winH", h);     // Window�ι⤵����¸
        
    }
    
    /***** Window �ν�λ���� *****/
    logout.prototype.win_close = function ()
    {
        this.setArrayCookie(this.win_name, this.win_name, "0");
        if (top.menu_site) {
            //parent.close();     // �ե졼��Τ���ƥե졼��򥯥������ʤ��ȥ�����ɥ�����λ���ʤ�
            (window.open('','_self').opener=window).close();  

        } else {
            //window.close();     // �ե졼�ब�ʤ���м�ʬ�Υ�����ɥ����Ĥ���
            (window.open('','_self').opener=window).close();  
        }
    }
    
    ///// Constructer �μ¹���
    /***** ����� *****/
    this.getWinName();
    this.getWinOffset();
    
    return this;    // Object Return
}   // class logout END


///// ���󥹥��󥹤�����
var menu = new logout();

///// Window����������¸(���֤�������¸�����)

menu.SaveSize();

///// Window���֤���¸

menu.SaveLocate();

///// Window���Ĥ���

menu.win_close();


