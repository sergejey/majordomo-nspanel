<?php
/**
 * NSPanel
 * @package project
 * @author Wizard <sergejey@gmail.com>
 * @copyright http://majordomo.smartliving.ru/ (c)
 * @version 0.1 (wizard, 15:07:33 [Jul 08, 2023])
 */
//
//

//Define('NSPANEL_USE_DEMO_CONFIG', DIR_MODULES . 'nspanel/sample_config.inc.php');

class nspanel extends module
{
    /**
     * nspanel
     *
     * Module class constructor
     *
     * @access private
     */
    function __construct()
    {
        $this->name = "nspanel";
        $this->title = "NSPanel";
        $this->module_category = "<#LANG_SECTION_DEVICES#>";
        $this->checkInstalled();
    }

    /**
     * saveParams
     *
     * Saving module parameters
     *
     * @access public
     */
    function saveParams($data = 1)
    {
        $p = array();
        if (isset($this->id)) {
            $p["id"] = $this->id;
        }
        if (isset($this->view_mode)) {
            $p["view_mode"] = $this->view_mode;
        }
        if (isset($this->edit_mode)) {
            $p["edit_mode"] = $this->edit_mode;
        }
        if (isset($this->tab)) {
            $p["tab"] = $this->tab;
        }
        return parent::saveParams($p);
    }

    /**
     * getParams
     *
     * Getting module parameters from query string
     *
     * @access public
     */
    function getParams()
    {
        global $id;
        global $mode;
        global $view_mode;
        global $edit_mode;
        global $tab;
        if (isset($id)) {
            $this->id = $id;
        }
        if (isset($mode)) {
            $this->mode = $mode;
        }
        if (isset($view_mode)) {
            $this->view_mode = $view_mode;
        }
        if (isset($edit_mode)) {
            $this->edit_mode = $edit_mode;
        }
        if (isset($tab)) {
            $this->tab = $tab;
        }
    }

    /**
     * Run
     *
     * Description
     *
     * @access public
     */
    function run()
    {
        global $session;
        $out = array();
        if ($this->action == 'admin') {
            $this->admin($out);
        } else {
            $this->usual($out);
        }
        if (isset($this->owner->action)) {
            $out['PARENT_ACTION'] = $this->owner->action;
        }
        if (isset($this->owner->name)) {
            $out['PARENT_NAME'] = $this->owner->name;
        }
        $out['VIEW_MODE'] = $this->view_mode;
        $out['EDIT_MODE'] = $this->edit_mode;
        $out['MODE'] = $this->mode;
        $out['ACTION'] = $this->action;
        $out['TAB'] = $this->tab;
        $this->data = $out;
        $p = new parser(DIR_TEMPLATES . $this->name . "/" . $this->name . ".html", $this->data, $this);
        $this->result = $p->result;
    }

    /**
     * BackEnd
     *
     * Module backend
     *
     * @access public
     */
    function admin(&$out)
    {
        $this->getConfig();

        $out['MQTT_HOST'] = $this->config['MQTT_HOST'];
        $out['MQTT_PORT'] = $this->config['MQTT_PORT'];
        $out['MQTT_QUERY'] = $this->config['MQTT_QUERY'];
        if (!$out['MQTT_HOST']) {
            $out['MQTT_HOST'] = 'localhost';
        }
        if (!$out['MQTT_PORT']) {
            $out['MQTT_PORT'] = '1883';
        }
        $out['MQTT_USERNAME'] = $this->config['MQTT_USERNAME'];
        $out['MQTT_PASSWORD'] = $this->config['MQTT_PASSWORD'];
        $out['MQTT_AUTH'] = $this->config['MQTT_AUTH'];
        $out['DEBUG_MODE'] = $this->config['DEBUG_MODE'];

        if ($this->view_mode == 'update_settings') {
            $this->config['MQTT_HOST'] = gr('mqtt_host', 'trim');
            $this->config['MQTT_USERNAME'] = gr('mqtt_username', 'trim');
            $this->config['MQTT_PASSWORD'] = gr('mqtt_password', 'trim');
            $this->config['MQTT_AUTH'] = gr('mqtt_auth', 'int');
            $this->config['MQTT_PORT'] = gr('mqtt_port', 'int');
            $this->config['MQTT_QUERY'] = gr('mqtt_query', 'trim');
            $this->saveConfig();
            setGlobal('cycle_nspanelControl', 'restart');
            $this->redirect("?");
        }

        if (isset($this->data_source) && !$_GET['data_source'] && !$_POST['data_source']) {
            $out['SET_DATASOURCE'] = 1;
        }
        if ($this->data_source == 'ns_panels' || $this->data_source == '') {
            if ($this->view_mode == '' || $this->view_mode == 'search_ns_panels') {
                $this->search_ns_panels($out);
            }
            if ($this->view_mode == 'edit_ns_panels') {
                $this->edit_ns_panels($out, $this->id);
            }
            if ($this->view_mode == 'delete_ns_panels') {
                $this->delete_ns_panels($this->id);
                $this->redirect("?");
            }
        }
    }

    function updateWeather($panel_path, $panel_config)
    {
        if (isset($panel_config['screensaver']['screenItems'])) {
            require(DIR_MODULES . 'nspanel/icons_map.inc.php');
            $data = array();
            for ($i = 0; $i < 6; $i++) {
                if (isset($panel_config['screensaver']['screenItems'][$i]) &&
                    isset($panel_config['screensaver']['screenItems'][$i]['title']) &&
                    $panel_config['screensaver']['screenItems'][$i]['title'] != '') {
                    $entity = $panel_config['screensaver']['screenItems'][$i];
                    $data[] = ''; // type, ignored
                    $data[] = ''; // name, ignored
                    if (!isset($entity['icon'])) {
                        $entity['icon'] = 'home';
                    }
                    if (!isset($entity['iconOn'])) {
                        $entity['iconOn'] = $entity['icon'];
                    }
                    if (!isset($entity['iconColor'])) {
                        $entity['iconColor'] = 'white';
                    }
                    if (!isset($entity['iconColorOn'])) {
                        $entity['iconColorOn'] = $entity['iconColor'];
                    }
                    if (isset($entity['linkedObject']) && isset($entity['linkedProperty'])) {
                        $status = getGlobal($entity['linkedObject'] . '.' . $entity['linkedProperty']);
                        if ($status) {
                            $entity['icon'] = $entity['iconOn'];
                            $entity['iconColor'] = $entity['iconColorOn'];
                        }
                    }

                    $data[] = $icon_map[$entity['icon']];
                    $data[] = $this->getColorNum($entity['iconColor']);
                    $data[] = $entity['title'] ? processTitle($entity['title']) : '';
                    $data[] = $entity['value'] ? processTitle($entity['value']) : '';
                } else {
                    $data[] = '';
                    $data[] = '';
                    $data[] = '';
                    $data[] = '';
                    $data[] = '';
                    $data[] = '';
                }
            }
            $this->sendCustomCommand($panel_path, 'weatherUpdate~' . implode('~', $data));
        }
    }

    function startScreensaver($panel_path, $panel_config)
    {
        $this->sendCustomCommand($panel_path, 'pageType~screensaver');
        //color~background~
        //tTime~timeAMPM~
        //tDate~tMainText~
        //tForecast1~//tForecast2~//tForecast3~//tForecast4~
        //tForecast1Val~tForecast2Val~tForecast3Val~tForecast4Val~
        //bar~tMainTextAlt2~tTimeAdd
        if (isset($panel_config['screensaver']['timeout'])) {
            $this->sendCustomCommand($panel_path, 'timeout~' . (int)$panel_config['screensaver']['timeout']);
        }
        if (isset($panel_config['screensaver']['brightness'])) {
            $this->sendCustomCommand($panel_path, 'dimmode~' . (int)$panel_config['screensaver']['brightness'] . '~100');
        }

        $this->updateWeather($panel_path, $panel_config);
    }

    function getColorNum($color)
    {
        if (is_integer($color)) return $color;
        $color = preg_replace('/^\#/', '', $color);
        if ($color == 'white') $color = 'ffffff';
        if ($color == 'red') $color = 'ff0000';
        if ($color == 'green') $color = '00ff00';
        if ($color == 'blue') $color = '0000ff';
        if ($color == 'yellow') $color = 'ffff00';
        list($r, $g, $b) = sscanf($color, "%02x%02x%02x");
        $value = (($r >> 3) << 11) | (($g >> 2) << 5) | (($b) >> 3);
        return $value;
    }

    function renderPage($panel_path, $panel_config, $page_num = 0)
    {
        $pageConfig = $panel_config['pages'][$page_num];
        SQLExec("UPDATE ns_panels SET CURRENT_PAGE='" . $pageConfig['name'] . "'");
        DebMes("Rendering page#$page_num : " . $pageConfig['name'], 'nspanel');

        require(DIR_MODULES . 'nspanel/icons_map.inc.php');

        // activate page type
        $this->sendCustomCommand($panel_path, 'pageType~' . $pageConfig['type']);

        // build page
        $data = array('entityUpd');
        // 1. navigation
        // title
        $data[] = processTitle($pageConfig['title']);
        // nav 1 (ignored, intNameEntity, icon, iconColor, ignored, ignored)
        $data[] = "button~navigate.prev~<~65535~~";
        // nav 2
        $data[] = "button~navigate.next~>~65535~~";
        // qr specific
        if ($pageConfig['type'] == 'cardQR') {
            if (isset($pageConfig['wifiSSID']) && isset($pageConfig['wifiPassword'])) {
                $pageConfig['text'] = 'WIFI:T:WPA;S:' . $pageConfig['wifiSSID'] . ';P:' . $pageConfig['wifiPassword'] . ';;';
            }
            $data[] = $pageConfig['text'];
        }

        // power specific
        if ($pageConfig['type'] == 'cardPower') {
            for ($i = 0; $i < 8; $i++) {
                if (isset($pageConfig['powerItems'][$i]) &&
                    isset($pageConfig['powerItems'][$i]['title']) &&
                    $pageConfig['powerItems'][$i]['title'] != ''
                ) {
                    $entity = $pageConfig['powerItems'][$i];
                    $data[] = ''; // type ignored
                    $data[] = ''; // intname ignored
                    if (!isset($entity['icon'])) {
                        $entity['icon'] = 'home';
                    }
                    $data[] = $icon_map[$entity['icon']];
                    $data[] = $this->getColorNum($entity['iconColor'] ?? 'blue');
                    $data[] = processTitle($entity['title']);
                    if (isset($entity['linkedObject']) && isset($entity['linkedProperty'])) {
                        $data[] = getGlobal($entity['linkedObject'] . '.' . $entity['linkedProperty']) . (isset($entity['unit']) ? ' ' . $entity['unit'] : '');
                    } else {
                        $data[] = '';
                    }
                    $data[] = $entity['speed'] ? processTitle($entity['speed']) : '';
                } else {
                    $data[] = 'delete';
                    $data[] = '';
                    $data[] = '';
                    $data[] = '';
                    $data[] = '';
                    $data[] = '';
                    $data[] = '';
                }
            }
        }

        // alarm specific
        if ($pageConfig['type'] == 'cardAlarm') {

            $data[] = $pageConfig['name'];

            if (isset($pageConfig['linkedObject']) && isset($pageConfig['linkedProperty'])) {
                $alarmStatus = getGlobal($pageConfig['linkedObject'] . '.' . $pageConfig['linkedProperty']);
            } else {
                $alarmStatus = 0;
            }

            for ($i = 0; $i < 4; $i++) {
                if (isset($pageConfig['armList'][$i])) {
                    $data[] = processTitle($pageConfig['armList'][$i]['title']);
                    $data[] = $pageConfig['armList'][$i]['actionName'];
                } else {
                    $data[] = '';
                    $data[] = '';
                }
            }


            if ($alarmStatus && isset($pageConfig['iconOn'])) {
                $pageConfig['icon'] = $pageConfig['iconOn'];
            }

            if (isset($pageConfig['icon'])) {
                $pageConfig['icon'] = $icon_map[$pageConfig['icon']];
            } else {
                $pageConfig['icon'] = $icon_map['security'];
            }
            $data[] = $pageConfig['icon'];

            if ($alarmStatus && isset($pageConfig['iconColorOn'])) {
                $pageConfig['iconColor'] = $pageConfig['iconColorOn'];
            }
            if (!isset($pageConfig['iconColor'])) {
                $pageConfig['iconColor'] = 'red';
            }
            $data[] = $this->getColorNum($pageConfig['iconColor']);

            $data[] = 'enable'; // numpad status
            $data[] = 'disable'; // flashing
        }

        // therm specific
        if ($pageConfig['type'] == 'cardThermo') {

            $tempUnit = $pageConfig['tempUnit'] ?? '°C';

            $data[] = $pageConfig['name']; // ?? intNameEntity

            if (isset($pageConfig['currentTempLinkedObject']) && isset($pageConfig['currentTempLinkedProperty'])) {
                $data[] = getGlobal($pageConfig['currentTempLinkedObject'] . '.' . $pageConfig['currentTempLinkedProperty']) . ' ' . $tempUnit; // current temperature label
            } else {
                $data[] = ''; // current temperature label
            }


            if (isset($pageConfig['targetTempLinkedObject']) && isset($pageConfig['targetTempLinkedProperty'])) {
                $data[] = (float)getGlobal($pageConfig['targetTempLinkedObject'] . '.' . $pageConfig['targetTempLinkedProperty']) * 10; // target temperature
            } else {
                $data[] = 22 * 10; // target temperature
            }

            $data[] = $pageConfig['currentTempLabel3rd'] ? processTitle($pageConfig['currentTempLabel3rd']) : '';
            $data[] = (float)($pageConfig['minTemp'] ?? 0) * 10; // 	Min Temp
            $data[] = (float)($pageConfig['maxTemp'] ?? 50) * 10; // 	Max Temp
            $data[] = (float)($pageConfig['stepTemp'] ?? 1) * 10; // 	Temperature Steps
            // mode buttons / icons
            for ($i = 0; $i < 8; $i++) {
                if (isset($pageConfig['actions'][$i]) && isset($pageConfig['actions'][$i]['name'])) {
                    $entity = $pageConfig['actions'][$i];
                    if (isset($entity['icon'])) {
                        $entity['icon'] = $icon_map[$entity['icon']];
                    } else {
                        $entity['icon'] = $icon_map['lightbulb'];
                    }
                    $data[] = $entity['icon'];
                    if (!isset($entity['iconColorOn'])) {
                        $entity['iconColorOn'] = 'yellow';
                    }
                    $data[] = $this->getColorNum($entity['iconColorOn']);
                    if (isset($entity['linkedObject']) && isset($entity['linkedProperty'])) {
                        $data[] = (int)getGlobal($entity['linkedObject'] . '.' . $entity['linkedProperty']);
                    } else {
                        $data[] = 0;
                    }
                    $data[] = $entity['name']; // name
                } else {
                    $data[] = "";
                    $data[] = "";
                    $data[] = "";
                    $data[] = "";
                }
            }
            $data[] = $pageConfig['currentTempLabel'] ? processTitle($pageConfig['currentTempLabel']) : ''; //Currently Label 1th Text Box
            $data[] = $pageConfig['currentTempLabel2nd'] ? processTitle($pageConfig['currentTempLabel2nd']) : ''; //State Label 3th Text Box
            $data[] = ''; //deprecated; ignored
            $data[] = $tempUnit; //Temperature Unit (Celcius/Farhenheit) ['°C', '°F', 'K']

            if (isset($pageConfig['targetTempLinkedObject2nd']) && isset($pageConfig['targetTempLinkedProperty2nd'])) {
                $data[] = (float)getGlobal($pageConfig['targetTempLinkedObject2nd'] . '.' . $pageConfig['targetTempLinkedProperty2nd']) * 10;  // 2nd target temperature
            } else {
                $data[] = ''; // 2nd target temperature
            }

            $data[] = 1; // additonal detail button to open another page
        }
        // Entities
        if ($pageConfig['type'] == 'cardEntities' || $pageConfig['type'] == 'cardGrid' || $pageConfig['type'] == 'cardQR') {
            for ($i = 0; $i < 4; $i++) {
                if (isset($pageConfig['entities'][$i])) {
                    $entity = $pageConfig['entities'][$i];
                    if (isset($icon_map[$entity['icon']])) {
                        $entity['icon'] = $icon_map[$entity['icon']];
                    }

                    if (isset($entity['linkedObject'])) {
                        $linkedObject = $entity['linkedObject'];
                        if (isset($entity['linkedProperty'])) {
                            $linkedProperty = $entity['linkedProperty'];
                        } else {
                            $linkedProperty = '';
                        }
                        if (isset($entity['linkedMethod'])) {
                            $linkedMethod = $entity['linkedMethod'];
                        } else {
                            $linkedMethod = '';
                        }
                    } else {
                        $linkedObject = '';
                        $linkedProperty = '';
                        $linkedMethod = '';
                    }

                    // light~light.ceiling_lights~B~52231~Ceiling Lights~1~
                    // type, name, icon, color, title, value
                    $data[] = $entity['type'];
                    $data[] = $entity['name'];
                    $data[] = $entity['icon'];

                    if (!isset($entity['iconColor'])) {
                        $entity['iconColor'] = 'white'; // blue (default)
                    }
                    $entity['iconColor'] = $this->getColorNum($entity['iconColor']);

                    if (!isset($entity['iconColorOn'])) {
                        $entity['iconColorOn'] = 'yellow'; // yellow (default)
                    }
                    $entity['iconColorOn'] = $this->getColorNum($entity['iconColorOn']);

                    if ($pageConfig['type'] == 'cardGrid' && $entity['type'] == 'switch' && $linkedObject && $linkedProperty) {
                        if (getGlobal($linkedObject . '.' . $linkedProperty)) {
                            $entity['iconColor'] = $entity['iconColorOn'];
                        }
                    }

                    $data[] = $entity['iconColor'];
                    $data[] = processTitle($entity['title']);
                    if ($entity['type'] == 'switch' && $linkedObject) {
                        $data[] = getGlobal($linkedObject . '.' . $linkedProperty);
                    } elseif ($entity['type'] == 'number' && $linkedObject) {
                        $min = $entity['min'] ?? 0;
                        $max = $entity['max'] ?? 100;
                        $data[] = getGlobal($linkedObject . '.' . $linkedProperty) . '|' . $min . '|' . $max;
                    } elseif ($entity['type'] == 'button') {
                        $data[] = $entity['text'];
                    } else {
                        $data[] = "";
                    }
                } else {
                    $data[] = "delete";
                    $data[] = "";
                    $data[] = "";
                    $data[] = "";
                    $data[] = "";
                    $data[] = "";
                }
            }
        }
        // render
        $result = implode('~', $data);
        $this->sendCustomCommand($panel_path, $result);

    }

    function processPanelMessage($panel_id, $topic, $msg)
    {
        $panel = SQLSelectOne("SELECT * FROM ns_panels WHERE ID=" . (int)$panel_id);
        if (!isset($panel['ID'])) return;

        DebMes("Processing (" . $panel['TITLE'] . ")  $topic :\n$msg", 'nspanel');

        $config = array();

        if (defined('NSPANEL_USE_DEMO_CONFIG')) {
            require NSPANEL_USE_DEMO_CONFIG;
        } else {
            $config = json_decode($panel['PANEL_CONFIG'], true);
        }


        $current_page_num = 0;
        $pageNumbers = array();

        if ($panel['CURRENT_PAGE'] != '') {
            $num = 0;
            foreach ($config['pages'] as $page) {
                $pageNumbers[$page['name']] = $num;
                if ($page['name'] == $panel['CURRENT_PAGE']) {
                    $current_page_num = $num;
                }
                $num++;
            }
        }
        $page = $config['pages'][$current_page_num];

        if (preg_match('/POWER1$/', $topic) && isset($config['power1'])) {
            if (isset($config['power1']['linkedObject']) && $msg == 'ON') {
                callMethod($config['power1']['linkedObject'] . '.turnOn');
            }
            if (isset($config['power1']['linkedObject']) && $msg == 'OFF') {
                callMethod($config['power1']['linkedObject'] . '.turnOff');
            }
        }

        if (preg_match('/RESULT$/', $topic)) {
            $result = json_decode($msg, true);
            if (isset($result['CustomRecv'])) {
                $data = explode(',', $result['CustomRecv']);

                //event,startup,51,eu
                if ($data[1] == 'startup') {
                    $this->startScreensaver($panel['MQTT_PATH'], $config);
                }

                //event,buttonPress2,screensaver,bExit,1
                if ($data[2] == 'screensaver' && $data[3] == 'bExit') {
                    $this->renderPage($panel['MQTT_PATH'], $config);
                }

                //event,sleepReached,cardEntities
                if ($data[1] == 'sleepReached') {
                    $this->startScreensaver($panel['MQTT_PATH'], $config);
                }

                //event,buttonPress2,navigate.next,button
                if ($data[2] == 'navigate.next' && $data[3] == 'button') {
                    DebMes("Current page num: " . $current_page_num, 'nspanel');
                    if (isset($config['pages'][$current_page_num + 1])) {
                        $this->renderPage($panel['MQTT_PATH'], $config, $current_page_num + 1);
                    } else {
                        $this->renderPage($panel['MQTT_PATH'], $config);
                    }
                }
                if ($data[2] == 'navigate.prev' && $data[3] == 'button') {
                    if (defined('NSPANEL_USE_DEMO_CONFIG')) {
                        $this->startScreensaver($panel['MQTT_PATH'], $config);
                    } else {
                        if ($current_page_num > 0) {
                            $this->renderPage($panel['MQTT_PATH'], $config, $current_page_num - 1);
                        } else {
                            $this->renderPage($panel['MQTT_PATH'], $config, count($config['pages']) - 1);
                        }
                    }
                }
                //event,buttonPress2,switch1,OnOff,0
                if ($data[1] == 'buttonPress2' && $data[3] == 'OnOff') {
                    foreach ($page['entities'] as $entity) {
                        if ($entity['name'] == $data[2] && $entity['linkedObject']) {
                            if ($data[4] == "1") {
                                callMethod($entity['linkedObject'] . '.turnOn');
                            } else {
                                callMethod($entity['linkedObject'] . '.turnOff');
                            }
                        }
                    }
                }
                //event,buttonPress2,switch2,button
                if ($data[1] == 'buttonPress2' && $data[3] == 'button') {
                    foreach ($page['entities'] as $entity) {
                        if ($entity['name'] == $data[2]) {
                            if (isset($entity['linkedObject']) && isset($entity['linkedMethod'])) {
                                callMethod($entity['linkedObject'] . '.' . $entity['linkedMethod']);
                            }
                            if (isset($entity['navigateTo']) && isset($pageNumbers[$entity['navigateTo']])) {
                                $current_page_num = $pageNumbers[$entity['navigateTo']];
                            }
                            $this->renderPage($panel['MQTT_PATH'], $config, $current_page_num);
                        }
                    }
                }
                if ($data[1] == 'buttonPress2' && $data[3] == 'number-set') {
                    $level = (int)$data[4];
                    foreach ($page['entities'] as $entity) {
                        if ($entity['name'] == $data[2]) {
                            if (isset($entity['linkedObject']) && isset($entity['linkedProperty'])) {
                                setGlobal($entity['linkedObject'] . '.' . $entity['linkedProperty'], $level);
                            }
                        }
                    }
                }

                if ($data[1] == 'buttonPress2' && $data[3] == 'hvac_action') {
                    $action_name = $data[4];
                    foreach ($page['actions'] as $entity) {
                        if ($entity['name'] == $action_name) {
                            if (isset($entity['linkedObject']) && isset($entity['linkedMethod'])) {
                                callMethod($entity['linkedObject'] . '.' . $entity['linkedMethod']);
                            }
                            if (isset($entity['navigateTo']) && isset($pageNumbers[$entity['navigateTo']])) {
                                $current_page_num = $pageNumbers[$entity['navigateTo']];
                                $this->renderPage($panel['MQTT_PATH'], $config, $current_page_num);
                            }
                        }
                    }
                }
                if ($data[1] == 'buttonPress2' && $data[3] == 'tempUpdHighLow') {
                    $values = explode('|', $data[4]);
                    if (isset($page['targetTempLinkedObject']) && isset($page['targetTempLinkedProperty'])) {
                        setGlobal($page['targetTempLinkedObject'] . '.' . $page['targetTempLinkedProperty'], round(((int)$values[0]) / 10, 1));
                    }
                    if (isset($values[1]) && isset($page['targetTempLinkedObject2nd']) && isset($page['targetTempLinkedProperty2nd'])) {
                        setGlobal($page['targetTempLinkedObject2nd'] . '.' . $page['targetTempLinkedProperty2nd'], round(((int)$values[1]) / 10, 1));
                    }
                }
                if ($data[1] == 'buttonPress2' && $data[3] == 'tempUpd') {
                    $value = $data[4];
                    if (isset($page['targetTempLinkedObject']) && isset($page['targetTempLinkedProperty'])) {
                        setGlobal($page['targetTempLinkedObject'] . '.' . $page['targetTempLinkedProperty'], round(((int)$value) / 10, 1));
                    }
                }
                //{"CustomRecv":"event,buttonPress2,pageAlarm,ArmAction1,1256"}
                if ($data[1] == 'buttonPress2' && $data[2] == $page['name'] && isset($page['armList'])) {
                    $pin = $data[4];
                    foreach ($page['armList'] as $armAction) {
                        if ($armAction['actionName'] == $data[3] &&
                            isset($armAction['linkedMethod']) &&
                            (!isset($armAction['pin']) || $armAction['pin'] == $pin)) {
                            callMethod($page['linkedObject'] . '.' . $armAction['linkedMethod'], array('pin' => $pin));
                            if (isset($armAction['navigateTo']) && isset($pageNumbers[$armAction['navigateTo']])) {
                                $current_page_num = $pageNumbers[$armAction['navigateTo']];
                            }
                            $this->renderPage($panel['MQTT_PATH'], $config, $current_page_num);
                        }
                    }
                }
            }
        }

    }

    function processMessage($topic, $msg)
    {
        if (preg_match('/cmnd\/CustomSend/', $topic)) return;
        $panels = SQLSelect("SELECT ID, MQTT_PATH FROM ns_panels");
        $total = count($panels);
        for ($i = 0; $i < $total; $i++) {
            if (is_integer(strpos($topic, $panels[$i]['MQTT_PATH']))) {
                $this->processPanelMessage($panels[$i]['ID'], $topic, $msg);
                return;
            }
        }
    }

    /**
     * FrontEnd
     *
     * Module frontend
     *
     * @access public
     */
    function usual(&$out)
    {
        $this->admin($out);
    }

    function api($params)
    {

        if ($_REQUEST['new_minute']) {
            $this->sendCurrentTime();
        }
        if ($_REQUEST['topic']) {
            $this->processMessage($_REQUEST['topic'], $_REQUEST['msg']);
        }
        if ($params['publish']) {
            $this->mqttPublish($params['publish'], $params['msg']);
        }
    }

    /**
     * ns_panels search
     *
     * @access public
     */
    function search_ns_panels(&$out)
    {
        require(dirname(__FILE__) . '/ns_panels_search.inc.php');
    }

    function sendCustomCommand($root_path, $command)
    {

        $topic = $root_path . '/cmnd/CustomSend';
        DebMes("Sending custom command to $topic: " . $command, 'nspanel');

        $this->getConfig();
        include_once(ROOT . "3rdparty/phpmqtt/phpMQTT.php");
        $client_name = "NSPanel module";
        if ($this->config['MQTT_AUTH']) {
            $username = $this->config['MQTT_USERNAME'];
            $password = $this->config['MQTT_PASSWORD'];
        }
        if ($this->config['MQTT_HOST']) {
            $host = $this->config['MQTT_HOST'];
        } else {
            $host = 'localhost';
        }
        if ($this->config['MQTT_PORT']) {
            $port = $this->config['MQTT_PORT'];
        } else {
            $port = 1883;
        }
        $mqtt_client = new Bluerhinos\phpMQTT($host, $port, $client_name);
        if (!$mqtt_client->connect(true, NULL, $username, $password)) {
            return 0;
        }
        $mqtt_client->publish($topic, $command, 0, 0);
        $mqtt_client->close();

    }

    function sendCurrentTime($device_id = 0)
    {
        $qry = "1";
        if ($device_id) {
            $qry .= " AND nspanels.ID=" . (int)$device_id;
        }
        $panels = SQLSelect("SELECT ID, MQTT_PATH, PANEL_CONFIG FROM ns_panels WHERE $qry");
        $total = count($panels);
        for ($i = 0; $i < $total; $i++) {
            // time
            $this->sendCustomCommand($panels[$i]['MQTT_PATH'], 'time~' . date('H:i'));

            if (defined('NSPANEL_USE_DEMO_CONFIG')) {
                require NSPANEL_USE_DEMO_CONFIG;
            } else {
                $config = json_decode($panels[$i]['PANEL_CONFIG'], true);
            }

            // date

            $date = date('l, d M, Y');
            if ($config['locale'] == 'ru_RU') {
                $weekdays = array('Воскресенье', 'Понедельник', 'Вторник', 'Среда', 'Четверг', 'Пятница', 'Суббота');
                $months = array('января', 'февраля', 'марта', 'апреля', 'мая', 'июня', 'июля', 'августа', 'сентября', 'октября', 'ноября', 'декабря');
                $date = $weekdays[date('w')] . ', ';
                $date .= date('d') . ' ';
                $date .= $months[date('m') - 1] . ', ';
                $date .= date('Y');
            }

            $this->sendCustomCommand($panels[$i]['MQTT_PATH'], 'date~' . $date);

            // weather
            $this->updateWeather($panels[$i]['MQTT_PATH'], $config);
            // remove notification
            // $this->sendCustomCommand($panels[$i]['MQTT_PATH'], 'notify~~');
        }
    }

    /**
     * ns_panels edit/add
     *
     * @access public
     */
    function edit_ns_panels(&$out, $id)
    {
        require(dirname(__FILE__) . '/ns_panels_edit.inc.php');
    }

    /**
     * ns_panels delete record
     *
     * @access public
     */
    function delete_ns_panels($id)
    {
        $rec = SQLSelectOne("SELECT * FROM ns_panels WHERE ID='$id'");
        // some action for related tables
        SQLExec("DELETE FROM ns_panels WHERE ID='" . $rec['ID'] . "'");
    }

    function processSubscription($event, $details = '')
    {
        $this->getConfig();
        if ($event == 'SAY') {
            $level = $details['level'];
            $message = $details['message'];
            //...
            $panels = SQLSelect("SELECT * FROM ns_panels");
            $total = count($panels);
            for ($i = 0; $i < $total; $i++) {

                if (defined('NSPANEL_USE_DEMO_CONFIG')) {
                    require NSPANEL_USE_DEMO_CONFIG;
                } else {
                    $config = json_decode($panels[$i]['PANEL_CONFIG'], true);
                }

                $header = date('H:i');
                if ($level >= (int)$config['notifications']['minMsgLevel']) {
                    $this->sendCustomCommand($panels[$i]['MQTT_PATH'], 'notify~' . $header . '~' . $message);
                }
            }
        }
    }

    /**
     * Install
     *
     * Module installation routine
     *
     * @access private
     */
    function install($data = '')
    {
        subscribeToEvent($this->name, 'SAY');
        parent::install();
    }

    /**
     * Uninstall
     *
     * Module uninstall routine
     *
     * @access public
     */
    function uninstall()
    {
        SQLExec('DROP TABLE IF EXISTS ns_panels');
        parent::uninstall();
    }

    /**
     * dbInstall
     *
     * Database installation routine
     *
     * @access private
     */
    function dbInstall($data)
    {
        /*
        ns_panels -
        */
        $data = <<<EOD
 ns_panels: ID int(10) unsigned NOT NULL auto_increment
 ns_panels: TITLE varchar(100) NOT NULL DEFAULT ''
 ns_panels: MQTT_PATH varchar(100) NOT NULL DEFAULT '' 
 ns_panels: CURRENT_PAGE varchar(100) NOT NULL DEFAULT ''
 ns_panels: PANEL_CONFIG text NOT NULL DEFAULT ''
EOD;
        parent::dbInstall($data);
    }
// --------------------------------------------------------------------
}
/*
*
* TW9kdWxlIGNyZWF0ZWQgSnVsIDA4LCAyMDIzIHVzaW5nIFNlcmdlIEouIHdpemFyZCAoQWN0aXZlVW5pdCBJbmMgd3d3LmFjdGl2ZXVuaXQuY29tKQ==
*
*/
