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

    'locale' => 'ru_RU',

    'decouple_buttons' => false, // or true -- https://docs.nspanel.pky.eu/phys-btn/

    'notifications' => array(
        'minMsgLevel' => 0
    ),
    'power1' => array(
        'linkedObject' => 'dimmer12',
        'linkedProperty' => 'status', // property to control relay
        'actionMethods' => array( // actual if decouple buttons is true
            'SINGLE' => 'relay01.switch', // single click method
            //'DOUBLE' => '...', //2-click
            //'TRIPLE' => '...', //3-click
            //'QUAD' => '...',   //4-click
            //'PENTA' => '...',  //5-click
            //'HOLD' => '...', //long press
            //'CLEAR' => '...', //long press release
        ),
    ),
    'screensaver' => array(
        'timeout' => 60, // 0 to disable
        'brightness' => 10, // screensaver brightness
        'screenItems' => array(
            array(
                'title' => 'Icon 1',
                'icon' => 'sun-thermometer-outline',
                //'iconColor'=>'blue',
                'value' => '%Sensor_temphum04.value% °C',
            ),
            array(
                'title' => 'Зал',
                'icon' => 'home-thermometer',
                //'iconColor'=>'blue',
                'value' => '%Sensor_temphum02.value%',
            ),
            array(
                'title' => 'Спальня',
                'icon' => 'home-thermometer',
                //'iconColor'=>'blue',
                'value' => '%Sensor_temphum01.value%',
            ),
            array(
                'title' => 'Детск',
                'icon' => 'home-thermometer',
                //'iconColor'=>'blue',
                'value' => '%Sensor_temphum03.value%',
            ),
            array(
                'title' => 'Лампа',
                'icon' => 'lightbulb',
                'iconOn' => 'home',
                'iconColor' => 'blue',
                'iconColorOn' => 'yellow',
                'linkedObject' => 'dimmer12',
                'linkedProperty' => 'status',
                'value' => '-',
            ),
            array(
                'title' => '',
                'icon' => 'home',
                'iconColor' => 'blue',
                'value' => 'V6',
            ),
        ),
    ),
    'pages' => array(

        array(
            'type' => 'cardEntities',
            'name' => 'page1',
            'title' => 'Bedroom',
            'entities' => array(
                array(
                    'type' => 'switch',
                    'name' => 'switch1',
                    'title' => 'Desk lamp',
                    'icon' => 'lightbulb',
                    'iconColor' => 'blue',
                    'linkedObject' => 'dimmer12',
                    'linkedProperty' => 'status',
                ),
                array(
                    'type' => 'text',
                    'name' => 'sensor1',
                    'title' => 'Temperature: 22 C',
                    'icon' => 'thermometer',
                    'iconColor' => 'blue',
                ),
                array(
                    'type' => 'button',
                    'name' => 'button1',
                    'title' => 'My scenario',
                    'text' => 'Run it!',
                    'icon' => 'target',
                    'iconColor' => 'blue',
                ),
                array(
                    'type' => 'number',
                    'name' => 'number1',
                    'title' => 'Desk lamp',
                    'icon' => 'lightbulb',
                    'iconColor' => 'blue',
                    'min' => 0,
                    'max' => 100,
                    'linkedObject' => 'dimmer12',
                    'linkedProperty' => 'level',
                ),
            ),
        ),

        array(
            'type' => 'cardGrid',
            'name' => 'page2',
            'title' => 'Livingroom',
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
                    'iconColor' => 'blue',
                ),
                array(
                    'type' => 'button',
                    'name' => 'button1',
                    'title' => 'My scenario',
                    'text' => 'Run it!',
                    'icon' => 'target',
                    'iconColor' => 'blue',
                    'linkedObject' => 'dimmer12',
                    'linkedMethod' => 'switch',
                ),
            ),
        ),

        array(
            'type' => 'cardThermo',
            'name' => 'pageThermo',
            'title' => 'Thermo page',

            //'tempUnit' => '°C',
            'minTemp' => 18,
            'maxTemp' => 35,
            'stepTemp' => 0.5,

            'currentTempLabel' => 'Inside',
            'currentTempLabel2nd' => 'Outside',
            'currentTempLabel3rd' => '%Sensor_temphum04.value% °C',

            'currentTempLinkedObject' => 'Thermostat01',
            'currentTempLinkedProperty' => 'value',

            'targetTempLinkedObject' => 'Thermostat01',
            'targetTempLinkedProperty' => 'normalTargetValue',

            //'targetTempLinkedObject2nd' => 'Thermostat01',
            //'targetTempLinkedProperty2nd' => 'ecoTargetValue',

            'actions' => array(
                array(
                    'icon' => 'lightbulb',
                    'iconColorOn' => 'green',
                    'name' => 'mode1',
                    'linkedObject' => 'dimmer12',
                    'linkedProperty' => 'status',
                    'linkedMethod' => 'switch'
                ),
                array(
                    'icon' => 'lightbulb',
                    'iconColorOn' => 'white',
                    'name' => 'mode2',
                    'linkedObject' => 'dimmer12',
                    'linkedMethod' => 'switch'
                ),
            )
        ),

        array(
            'type' => 'cardAlarm',
            'name' => 'pageAlarm',
            'title' => 'Alarm page',

            'icon' => 'lock',
            'iconOn' => 'lock-open',

            'iconColor' => 'red',
            'iconColorOn' => 'green',

            'linkedObject' => 'dimmer12',
            'linkedProperty' => 'status',

            'armList' => array(
                array(
                    'title' => 'Arm 1',
                    'actionName' => 'ArmAction1',
                    'pin' => '123',
                    'linkedMethod' => 'switch',
                ),
                array(
                    'title' => 'Arm 2',
                    'actionName' => 'ArmAction2',
                    'linkedMethod' => 'switch',
                ),
                array(
                    'title' => 'Arm 3',
                    'actionName' => 'ArmAction3'
                ),
                array(
                    'title' => 'Arm 4',
                    'actionName' => 'ArmAction4'
                ),
            ),
        ),


        array(
            'type' => 'cardQR',
            'name' => 'page3',
            'title' => 'Guest Wi-Fi',
            'text' => 'QREncoded text',
            'wifiSSID' => 'GuestWiFi',
            'wifiPassword' => '12345',
            'entities' => array(
                array(
                    'type' => 'text',
                    'name' => 'text1',
                    'title' => 'SSID: GuestWiFi',
                    'icon' => 'information-outline',
                    'iconColor' => 'blue',
                ),
                array(
                    'type' => 'text',
                    'name' => 'text2',
                    'title' => 'Password: 12345',
                    'icon' => 'shield-lock-outline',
                    'iconColor' => 'blue',
                ),
            ),
        ),
        array(
            'type' => 'cardPower',
            'name' => 'pagePower',
            'title' => 'Home Power',
            //'backPage' => 'page1',
            //'hidden' => true,
            'powerItems' => array(
                array(
                    'title' => 'Home 1',
                    'icon' => 'home',
                    'iconColor' => 'white',
                    'unit' => 'kW',
                    'linkedObject' => 'dimmer12',
                    'linkedProperty' => 'status',
                    'speed' => 60
                ),
                array(
                    'title' => 'Home 2',
                    'icon' => 'home',
                    'iconColor' => 'white',
                    'unit' => 'kW',
                    'linkedObject' => 'dimmer12',
                    'linkedProperty' => 'status',
                    'speed' => 60
                ),
                array(
                    'title' => 'Home 3',
                    'icon' => 'wind-turbine',
                    'iconColor' => 'white',
                    'unit' => 'kW',
                    'linkedObject' => 'dimmer12',
                    'linkedProperty' => 'status',
                    'speed' => -40
                ),
                array(
                    'title' => 'Home 4',
                    'icon' => 'solar-power-variant',
                    'iconColor' => 'white',
                    'unit' => 'kW',
                    'linkedObject' => 'dimmer12',
                    'linkedProperty' => 'status',
                    'speed' => -20
                ),
                array(
                    'title' => 'Home 5',
                    'icon' => 'meter-electric',
                    'iconColor' => 'white',
                    'unit' => 'kW',
                    'linkedObject' => 'dimmer12',
                    'linkedProperty' => 'status',
                    'speed' => -60
                ),
                array(
                    'title' => 'Home 6',
                    'icon' => 'car',
                    'iconColor' => 'white',
                    'unit' => 'kW',
                    'linkedObject' => 'dimmer12',
                    'linkedProperty' => 'status',
                    'speed' => -60
                ),
                array(
                    'title' => 'Home 7',
                    'icon' => 'motorbike-electric',
                    'iconColor' => 'white',
                    'unit' => 'kW',
                    'linkedObject' => 'dimmer12',
                    'linkedProperty' => 'status',
                    'speed' => -30
                ),
                array(
                    'title' => 'Home 8',
                    'icon' => 'home',
                    'iconColor' => 'white',
                    'unit' => 'kW',
                    'linkedObject' => 'dimmer12',
                    'linkedProperty' => 'status',
                    'speed' => -60
                ),
            )
        ),
    ),
);