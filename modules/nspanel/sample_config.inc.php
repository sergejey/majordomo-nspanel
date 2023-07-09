<?php

// pages types: screensaver, pageStartup, cardEntities, cardGrid, cardThermo, cardMedia,
// popupLight, popupNotify

/*
 * Possible entities
~light~light.entityName~1~17299~Light1~0
~shutter~cover.entityName~0~17299~Shutter2~iconUp|iconStop|iconDown
~delete~~~~~
~text~sensor.entityName~3~17299~Temperature~content
~button~button.entityName~3~17299~bt-name~bt-text
~switch~switch.entityName~4~17299~Switch1~0
~number~input_number.entityName~4~17299~Number123~value|min|max
~input_sel~input_select.entityName~3~17299~sel-name~sel-text
 */

$config = array(
    'notifications' =>array(
        'minMsgLevel' => 0
    ),
    'power1' => array(
        'linkedObject'=>'dimmer12',
    ),
    'screensaver' => array(
        'timeout' => 60, // 0 to disable
        'brightness' => 10, // screensaver brightness
    ),
    'pages' => array(
        array(
            'name' => 'page1',
            'title' => 'Bedroom',
            'type' => 'cardEntities',
            'entities' => array(
                array(
                    'type' => 'switch',
                    'name' => 'switch1',
                    'title' => 'Desk lamp',
                    'icon' => 'lightbulb',
                    'iconColor' => 17299,
                    'linkedObject' => 'dimmer12',
                    'linkedProperty' => 'status',
                ),
                array(
                    'type' => 'text',
                    'name' => 'sensor1',
                    'title' => 'Temperature: 22 C',
                    'icon' => 'thermometer',
                    'iconColor' => 17299,
                ),
                array(
                    'type' => 'button',
                    'name' => 'button1',
                    'title' => 'My scenario',
                    'text' => 'Run it!',
                    'icon' => 'target',
                    'iconColor' => 17299,
                ),
            ),
        ),
        array(
            'name' => 'page2',
            'title' => 'Livingroom',
            'type' => 'cardGrid',
            'entities' => array(
                array(
                    'type' => 'switch',
                    'name' => 'switch2',
                    'title' => 'Main light',
                    'icon' => 'lightbulb',
                    'linkedObject' => 'dimmer12',
                    'linkedProperty' => 'status',
                    'navigateTo' => 'page1',
                ),
                array(
                    'type' => 'text',
                    'name' => 'sensor1',
                    'title' => 'Living room: 22 C',
                    'icon' => 'thermometer',
                    'iconColor' => 17299,
                ),
                array(
                    'type' => 'button',
                    'name' => 'button1',
                    'title' => 'My scenario',
                    'text' => 'Run it!',
                    'icon' => 'target',
                    'iconColor' => 17299,
                    'linkedObject' => 'dimmer12',
                    'linkedMethod' => 'switch',
                ),
            ),
        ),
        array(
            'name' => 'page3',
            'title' => 'Guest Wi-Fi',
            'type' => 'cardQR',
            'text' => 'QREncoded text',
            'wifiSSID' => 'GuestWiFi',
            'wifiPassword' => '12345',
            'entities' => array(
                array(
                    'type' => 'text',
                    'name' => 'text1',
                    'title' => 'SSID: GuestWiFi',
                    'icon' => 'information-outline',
                    'iconColor' => 17299,
                ),
                array(
                    'type' => 'text',
                    'name' => 'text2',
                    'title' => 'Password: 12345',
                    'icon' => 'shield-lock-outline',
                    'iconColor' => 17299,
                ),
            ),
        ),
    ),
);