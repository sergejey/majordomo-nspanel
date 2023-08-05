<?php

$go_linked_object = gr('go_linked_object');
$go_linked_property = gr('go_linked_property');
if ($go_linked_object && $go_linked_property) {
    $panels = SQLSelect("SELECT * FROM ns_panels");
    $total = count($panels);
    for ($i = 0; $i < $total; $i++) {
        $config = $this->getPanelConfig($panels[$i]['PANEL_CONFIG']);
        if (isset($config['power1']['linkedObject']) &&
            strtolower($config['power1']['linkedObject']) == strtolower($go_linked_object) &&
            isset($config['power1']['linkedProperty']) &&
            strtolower($config['power1']['linkedProperty']) == strtolower($go_linked_property)) {
            $this->redirect("?view_mode=edit_ns_panels&id=" . $panels[$i]['ID'] . "&tab=config");
        }
        if (isset($config['power2']['linkedObject']) &&
            strtolower($config['power2']['linkedObject']) == strtolower($go_linked_object) &&
            isset($config['power2']['linkedProperty']) &&
            strtolower($config['power2']['linkedProperty']) == strtolower($go_linked_property)) {
            $this->redirect("?view_mode=edit_ns_panels&id=" . $panels[$i]['ID'] . "&tab=config");
        }
    }
    removeLinkedProperty($go_linked_object, $go_linked_property, $this->name);
}


global $session;
if ($this->owner->name == 'panel') {
    $out['CONTROLPANEL'] = 1;
}
$qry = "1";
// search filters
// QUERY READY
global $save_qry;
if ($save_qry) {
    $qry = $session->data['ns_panels_qry'];
} else {
    $session->data['ns_panels_qry'] = $qry;
}
if (!$qry) $qry = "1";
$sortby_ns_panels = "ID DESC";
$out['SORTBY'] = $sortby_ns_panels;
// SEARCH RESULTS
$res = SQLSelect("SELECT * FROM ns_panels WHERE $qry ORDER BY " . $sortby_ns_panels);
if (isset($res[0])) {
    //paging($res, 100, $out); // search result paging
    $total = count($res);
    for ($i = 0; $i < $total; $i++) {
        // some action for every record if required
    }
    $out['RESULT'] = $res;
}
