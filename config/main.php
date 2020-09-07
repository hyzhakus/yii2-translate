<?php
return [
    'id' => 'translate',
    'class' => hyzhak\translate\Module::class,
    'urlManagerRules' => [
        'translate/<action>' => 'translate/default/<action>',
    ],
];
