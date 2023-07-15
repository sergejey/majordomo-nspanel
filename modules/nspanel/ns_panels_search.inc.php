<?php

//dprint($this->getColorNum('ff0000'));

/*
* @version 0.1 (wizard)
*/
 global $session;
  if ($this->owner->name=='panel') {
   $out['CONTROLPANEL']=1;
  }
  $qry="1";
  // search filters
  // QUERY READY
  global $save_qry;
  if ($save_qry) {
   $qry=$session->data['ns_panels_qry'];
  } else {
   $session->data['ns_panels_qry']=$qry;
  }
  if (!$qry) $qry="1";
  $sortby_ns_panels="ID DESC";
  $out['SORTBY']=$sortby_ns_panels;
  // SEARCH RESULTS
  $res=SQLSelect("SELECT * FROM ns_panels WHERE $qry ORDER BY ".$sortby_ns_panels);
  if (isset($res[0])) {
   //paging($res, 100, $out); // search result paging
   $total=count($res);
   for($i=0;$i<$total;$i++) {
    // some action for every record if required
   }
   $out['RESULT']=$res;
  }
