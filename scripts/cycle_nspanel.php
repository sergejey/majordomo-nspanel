<?php
chdir(dirname(__FILE__) . '/../');
include_once("./config.php");
include_once("./lib/loader.php");
include_once("./lib/threads.php");
set_time_limit(0);
// connecting to database
$db = new mysql(DB_HOST, '', DB_USER, DB_PASSWORD, DB_NAME);
include_once("./load_settings.php");
include_once(DIR_MODULES . "control_modules/control_modules.class.php");
$ctl = new control_modules();

include_once(ROOT . "3rdparty/phpmqtt/phpMQTT.php");
include_once(DIR_MODULES . 'nspanel/nspanel.class.php');
$nspanel_module = new nspanel();
$nspanel_module->getConfig();

echo date("H:i:s") . " running " . basename(__FILE__) . PHP_EOL;

$client_name = "MajorDoMo NSPanel";
$client_name = $client_name . ' (#' . uniqid() . ')';

if ($nspanel_module->config['MQTT_AUTH']) {
    $username = $nspanel_module->config['MQTT_USERNAME'];
    $password = $nspanel_module->config['MQTT_PASSWORD'];
}

$host = 'localhost';

if ($nspanel_module->config['MQTT_HOST']) {
    $host = $nspanel_module->config['MQTT_HOST'];
}

if ($nspanel_module->config['MQTT_PORT']) {
    $port = $nspanel_module->config['MQTT_PORT'];
} else {
    $port = 1883;
}

if ($nspanel_module->config['MQTT_QUERY']) {
    $query = $nspanel_module->config['MQTT_QUERY'];
} else {
    $query = '/var/now/#';
}

$mqtt_client = new Bluerhinos\phpMQTT($host, $port, $client_name);

if ($nspanel_module->config['MQTT_AUTH']) {
    $connect = $mqtt_client->connect(true, NULL, $username, $password);
    if (!$connect) {
        exit(1);
    }
} else {
    $connect = $mqtt_client->connect();
    if (!$connect) {
        exit(1);
    }
}


$query_list = explode(',', $query);
$total = count($query_list);
echo date('H:i:s') . " Topics to watch: $query (Total: $total)\n";
for ($i = 0; $i < $total; $i++) {
    $path = trim($query_list[$i]);
    echo date('H:i:s') . " Path: $path\n";
    $topics[$path] = array("qos" => 0, "function" => "procmsg");
}
foreach ($topics as $k => $v) {
    echo date('H:i:s') . " Subscribing to: $k  \n";
    $rec = array($k => $v);
    $mqtt_client->subscribe($rec, 0);
}
$previousMillis = 0;

$oldMinute = '';

while ($mqtt_client->proc()) {

    $newMinute = date('H:i');
    if ($newMinute != $oldMinute) {
        $oldMinute = $newMinute;
        callAPI('/api/module/nspanel', 'POST', array('new_minute' => 1));
    }

    $queue = checkOperationsQueue('nspanel_queue');
    foreach ($queue as $mqtt_data) {
        $topic = $mqtt_data['DATANAME'];
        $value = $mqtt_data['DATAVALUE'];
        $qos = 0;
        $retain = 0;
        if ($topic != '') {
            $mqtt_client->publish($topic, $value, $qos, $retain);
        }
    }

    $currentMillis = round(microtime(true) * 10000);

    if ($currentMillis - $previousMillis > 10000) {
        $previousMillis = $currentMillis;
        setGlobal((str_replace('.php', '', basename(__FILE__))) . 'Run', time(), 1);
        if (file_exists('./reboot') || isset($_GET['onetime'])) {
            $mqtt_client->close();
            $db->Disconnect();
            exit;
        }
    }
}

$mqtt_client->close();

function procmsg($topic, $msg)
{

    $from_hub = 0;
    $did = $topic;

    global $nspanel_module;

    if (!isset($topic) || !isset($msg)) return false;
    echo date("Y-m-d H:i:s") . " Received from {$topic} ($did, $from_hub): $msg\n";
    if (function_exists('callAPI')) {
        callAPI('/api/module/nspanel', 'POST', array('topic' => $topic, 'msg' => $msg));
    } else {
        $nspanel_module->processMessage($topic, $msg);
    }
}

DebMes("Unexpected close of cycle: " . basename(__FILE__));
