<?php

return array(
    'company_name' => array(
        'title' => 'Nombre de la compañía afiliada a Datafast',
        'type' => 'text',
        'label' => 'Razón social registrada en Datafast.',
        'default' => 'no'
    ),
    'staging' => array(
        'title' => 'Ambiente de producción',
        'type' => 'checkbox',
        'label' => 'Usar el Gateway de Datafast en ambiente de producción.',
        'default' => 'no'
    ),
    'title' => array(
        'title' => 'Título',
        'type' => 'text',
        'description' => 'Esto controla el título que el usuario ve durante el proceso de pago.',
        'default' => 'Tarjeta de crédito (Datafast)',
    ),
    'description' => array(
        'title' => 'Mensaje para el usuario',
        'type' => 'textarea',
        'default' => 'Datafast es una solución completa para pagos en línea.'
    ),
    'app_identity_client' => array(
        'title' => 'Identificación única de Cliente (entityId)',
        'type' => 'text',
        'description' => 'Identificador de cliente en Datafast.'
    ),
    'app_auth_client' => array(
        'title' => 'Código de autorización (Authorization)',
        'type' => 'text',
        'description' => 'Código de autorización de Datafast.'
    ),
    'app_mid' => array(
        'title' => 'Número MID',
        'type' => 'text',
        'description' => ''
    ),
    'app_tid' => array(
        'title' => 'Número TID',
        'type' => 'text',
        'description' => ''
    ),
    'server_test_url' => array(
        'title' => 'Url de pruebas',
        'type' => 'text',
        'description' => 'Url del servidor de pruebas.'
    ),
    'server_live_url' => array(
        'title' => 'Url de producción',
        'type' => 'text',
        'description' => 'Url del servidor de producción de Datafast.'
    ),
    'datafast_woocommerce_url_success' => array(
        'title' => 'Url de redirección',
        'type' => 'text',
        'description' => 'Url de redirección de respuesta de Datafast.'
    )
);
