<?php
//////////////////////////////////////////////////////////////////////////////
// 社員情報管理の include file 所属･職位･教育･資格の選択設定                //
// Copyright (C) 2001-2005 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2001/07/07 Created  view_select_indicate.php                             //
// 2002/08/07 register_globals = Off 対応 & セッション管理                  //
// 2003/10/22 anchor(アンカー)を設定して追加変更時に元の場所に戻る          //
//            receive(教育)を追加ボタン → 追加変更ボタンへ                 //
// 2003/12/05 section(所属)を追加ボタン → 追加変更ボタンへ 修正できる      //
// 2005/01/17 access_log の変更と view_file_name(__FILE__) の追加           //
//////////////////////////////////////////////////////////////////////////////
// access_log("view_select_indicate.php");     // Script Name 手動設定
access_log(substr(__FILE__, strlen($_SERVER['DOCUMENT_ROOT'])));
echo view_file_name(__FILE__);
?>
<!-- section -->
          <table width="100%">
              <tr>
                <td align="middle" width="100%" bgcolor="#ff6600" colspan="2"><font color="#ffffff">所属設定</font></td>
              </tr>
              <tr>
                <td valign="top" width="100%" colspan="2">
                  <table width="70%">
<?php
    $query = "select * from section_master order by sid asc";
    $res   = array();
    if ($rows=getResult($query, $res)) {
        $anchor = ($rows - 20);         // アンカーに設定する場所を決める。終わりから20個前
        if ($anchor < 0) $anchor = 0;   // 0 以下の場合は 0 にする
        for ($i=0; $i<$rows; $i++) {
            $sid = ($res[$i]['sid']);
            $section_name = (trim($res[$i]['section_name']));
            $sflg = ($res[$i]['sflg']);
            if ($sflg == 1) {
                $name  = '無効にする';
                $color = 'black';
            } else {
                $name  = '有効にする';
                $color = 'silver';
            }
?>
                    <form method="post" action="select_section.php">
                        <tr><td width="5%">
                                <font color="<?php echo($color); ?>">
                                <?php 
                                if ($i == $anchor) {
                                    echo "<a name='section'>$sid</a>\n";    // section anchor SET
                                } else {
                                    echo "$sid \n";
                                }
                                ?>
                                </font>
                            </td>
                            <td width="60%"><font color="<?php echo($color); ?>"><?php echo($section_name); ?></font></td>
                            <td><input type="submit" value="<?php echo($name); ?>">
                            <input type="hidden" name="sid" value=<?php echo($sid); ?>></td>
                            <td><input type="hidden" name="sflg" value="<?php echo($sflg); ?>"></td>
                        </tr>
                    </form>
<?php
        }
    }
?>
                  </table>
                　<table>
                      <form method="post" action="addselect_section.php">
                        <tr><td>新規項目　sid</td>
                            <td><input type="text" name="sid" size=3 maxlength=3></td>
                            <td>所属名</td>
                            <td><input type="text" name="section_name" size=64 maxlength=64></td>
                            <td><input type="hidden" name="sflg" value=1></td>
                            <td><input type="submit" value="追加変更"></td>
                        </tr>
                      </form>
                  </table>
                  <hr>
              </tr>
          </table>
<!-- /section -->
<!-- receive -->
        　<table width="100%">
              <tr>
                <td align="middle" width="100%" bgcolor="#ff6600" colspan="2"><font color="#ffffff">教育設定</font></td>
              </tr>
              <tr>
                <td valign="top" width="100%" colspan="2">
                  <table width="70%">
<?php
    $query = "select * from receive_master order by rid asc";
    $res   = array();
    if ($rows=getResult($query, $res)) {
        $anchor = ($rows - 20);         // アンカーに設定する場所を決める。終わりから20個前
        if ($anchor < 0) $anchor = 0;   // 0 以下の場合は 0 にする
        for ($i=0; $i<$rows; $i++) {
            $rid = ($res[$i]['rid']);
            $receive_name = (trim($res[$i]['receive_name']));
            $rflg = ($res[$i]['rflg']);
            if ($rflg == 1) {
                $name  = '無効にする';
                $color = 'black';
            } else {
                $name  = '有効にする';
                $color = 'silver';
            }
?>
                    <form method="post" action="select_receive.php">
                        <tr><td width="5%">
                                <font color="<?php echo($color); ?>">
                                <?php 
                                if ($i == $anchor) {
                                    echo "<a name='receive'>$rid</a>\n";    // receive anchor SET
                                } else {
                                    echo "$rid \n";
                                }
                                ?>
                                </font>
                            </td>
                            <td width="60%"><font color="<?php echo($color); ?>"><?php echo($receive_name); ?></font></td>
                            <td><input type="submit" value="<?php echo($name); ?>">
                            <input type="hidden" name="rid" value=<?php echo($rid); ?>></td>
                            <td><input type="hidden" name="rflg" value="<?php echo($rflg); ?>"></td>
                        </tr>
                    </form>
<?php
        }
    }
?>
                  </table>
                  <table>
                      <form method="post" action="addselect_receive.php">
                        <tr><td nowrap>新規項目rid</td>
                            <td><input type="text" name="rid" size=5 maxlength=5></td>
                            <td nowrap>教育名</td>
                            <td><input type="text" name="receive_name" size=64 maxlength=64></td>
                            <td><input type="hidden" name="rflg" value=1></td>
                            <td><input type="submit" value="追加変更"></td>
                        </tr>
                      </form>
                  </table>
                  <hr>
              </tr>
          </table>
<!-- /receive -->
<!-- capacity -->
<table width="100%">
              <tr>
                <td align="middle" width="100%" bgcolor="#ff6600" colspan="2"><font color="#ffffff">資格設定</font></td>
              </tr>
              <tr>
                <td valign="top" width="100%" colspan="2">
                  <table width="70%">
<?php
    $query = "select * from capacity_master order by cid asc";
    $res   = array();
    if ($rows=getResult($query, $res)) {
        $anchor = ($rows - 20);         // アンカーに設定する場所を決める。終わりから20個前
        if ($anchor < 0) $anchor = 0;   // 0 以下の場合は 0 にする
        for ($i=0; $i<$rows; $i++) {
            $cid = ($res[$i]['cid']);
            $capacity_name = (trim($res[$i]['capacity_name']));
            $cflg = ($res[$i]['cflg']);
            if ($cflg == 1) {
                $name  = '無効にする';
                $color = 'black';
            }else{
                $name  = '有効にする';
                $color = 'silver';
            }
?>
                    <form method="post" action="select_capacity.php">
                        <tr><td width="5%">
                                <font color="<?php echo($color); ?>">
                                <?php 
                                if ($i == $anchor) {
                                    echo "<a name='capacity'>$cid</a>\n";    // capacity anchor SET
                                } else {
                                    echo "$cid \n";
                                }
                                ?>
                                </font>
                            </td>
                            <td width="60%"><font color="<?php echo($color); ?>"><?php echo($capacity_name); ?></font></td>
                            <td><input type="submit" value="<?php echo($name); ?>">
                            <input type="hidden" name="cid" value=<?php echo($cid); ?>></td>
                            <td><input type="hidden" name="cflg" value="<?php echo($cflg); ?>"></td>
                        </tr>
                    </form>
<?php
        }
    }
?>
                  </table>
                  <table>
                      <form method="post" action="addselect_capacity.php">
                        <tr><td>新規項目　cid</td>
                            <td><input type="text" name="cid" size=5 maxlength=5></td>
                            <td>資格名</td>
                            <td><input type="text" name="capacity_name" size=64 maxlength=64></td>
                            <td><input type="hidden" name="cflg" value=1></td>
                            <td><input type="submit" value="追加"></td>
                        </tr>
                      </form>
                  </table>
                  <hr>
              </tr>
          </table>
<!--/ capacity -->
<!-- position -->
<table width="100%">
              <tr>
                <td align="middle" width="100%" bgcolor="#ff6600" colspan="2"><font color="#ffffff">職位設定</font></td>
              </tr>
              <tr>
                <td valign="top" width="100%" colspan="2">
                  <table width="70%">
<?php
    $query = "select * from position_master order by pid asc";
    $res   = array();
    if ($rows=getResult($query, $res)) {
        $anchor = ($rows - 20);         // アンカーに設定する場所を決める。終わりから20個前
        if ($anchor < 0) $anchor = 0;   // 0 以下の場合は 0 にする
        for ($i=0; $i<$rows; $i++) {
            $pid = ($res[$i]['pid']);
            $position_name = (trim($res[$i]['position_name']));
            $pflg = ($res[$i]['pflg']);
            if ($pflg == 1) {
                $name  = '無効にする';
                $color = 'black';
            } else {
                $name  = '有効にする';
                $color = 'silver';
            }
?>
                    <form method="post" action="select_position.php">
                        <tr><td width="5%">
                                <font color="<?php echo($color); ?>">
                                <?php 
                                if ($i == $anchor) {
                                    echo "<a name='position'>$pid</a>\n";    // position anchor SET
                                } else {
                                    echo "$pid \n";
                                }
                                ?>
                                </font>
                            </td>
                            <td width="60%"><font color="<?php echo($color); ?>"><?php echo($position_name); ?></font></td>
                            <td><input type="submit" value="<?php echo($name); ?>">
                            <input type="hidden" name="pid" value=<?php echo($pid); ?>></td>
                            <td><input type="hidden" name="pflg" value="<?php echo($pflg); ?>"></td>
                        </tr>
                    </form>
<?php
        }
    }
?>
                  </table>
                  <table>
                      <form method="post" action="addselect_position.php">
                        <tr><td>新規項目　pid</td>
                            <td><input type="text" name="pid" size=3 maxlength=3></td>
                            <td>職位名</td>
                            <td><input type="text" name="position_name" size=64 maxlength=64></td>
                            <td><input type="hidden" name="pflg" value=1></td>
                            <td><input type="submit" value="追加"></td>
                        </tr>
                      </form>
                  </table>
                  <hr>
              </tr>
          </table>
<!--/ capacity -->
        </td>
      </tr>
  </table>
