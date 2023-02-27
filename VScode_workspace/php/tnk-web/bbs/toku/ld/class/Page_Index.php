<?php  class Page_Index{var $_offset,$_pagecount,$_allcount,$_max;var $_nextpage,$_prevpage,$_pages;function Page_Index($offset,$pagecount,$allcount,$max=""){$this->_offset =$offset;$this->_pagecount =$pagecount;$this->_allcount =$allcount;$this->_max =$max;$this->_nextpage =intval($offset)+ intval($pagecount);$this->_prevpage =intval($offset)- intval($pagecount);$this->_pages =intval($this->_allcount / $this->_pagecount);}function isFirstPage(){if ($this->_offset ==0){return TRUE;}else {return FALSE;}}function isLastPage(){if (($this->_offset + $this->_pagecount)>=$this->_allcount){return TRUE;}else {return FALSE;}}function draw($template){if ($this->_pages ==0){return ;}elseif (is_readable($template)&& is_file($template)){$max =$this->_max;$allcount =$this->_allcount;$pagecount=$this->_pagecount;$offset =$this->_offset;$current =$offset;$nextpage =$this->_nextpage;$prevpage =$this->_prevpage;if ($max !=""){$nowpage =($offset / $pagecount)+ 1;$start =$nowpage - ($max / 2);if ($start < 0){$start =0 ;}$end =$start + $max;}$n =0;$start =$start."";$pageindex =array();$prevsign =1;$nextsign =1;for($i=0;$i<$allcount;$i+=$pagecount){$n+=1;if ($start !="" && $end !=""){if ($n < $start || $n > $end){continue;}}$pageindex[$n] =$i;if ($n ==1){$prevsign =0;}if ($n ==$this->_pages){$nextsign =0;}}if (count($pageindex)==1){$pageindex =array();}require( $template );}else {trigger_error("Page_Index: テンプレートが開けません");}}function get($name){$tmp ="_".$name;return $this->$tmp;}function set($name, $value){$tmp ="_".$name;$this->$tmp =$value;}}?>