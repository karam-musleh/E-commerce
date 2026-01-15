<?php

return [
    'gateways' => [
        'fake' => [
            'class' => \App\Gateways\FakeGateway::class,
            'active' => true,
        ],
    ],
];
