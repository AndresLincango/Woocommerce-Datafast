<?php
add_action('parse_request', 'callback_datafast_url_handler');
function callback_datafast_url_handler()
{
    $url = strtok($_SERVER["REQUEST_URI"], '?');
    $qStrings = $_SERVER['QUERY_STRING'];
    $order_url = $_SERVER['HTTP_REFERER'];
    if ($url == '/datafast_action/') {
        if (WC_Datafast_String_Checker::check_string($qStrings)) {
            parse_str($qStrings, $queryValues);
            $callback_response = WC_Datafast_Requester::get_payment_status($queryValues['id'], $queryValues['resourcePath']);
            if (isset($callback_response->result)) {
                if ($callback_response->result->code == '000.100.112') {
                    $current_order = WC_Datafast_Database_Helper::select_order($queryValues['id'], 'order_transaction');
                    $order = new WC_Order($current_order);
                    if (!$order->has_status(array('processing', 'completed'))) {
                        $order->add_order_note('-');
                        $order->payment_complete($queryValues['id']);
                        $redirect_url_purchase = WC_Payment_Gateway::get_return_url($order);
                        WC()->cart->empty_cart();
                        wp_safe_redirect($redirect_url_purchase);
                        exit();
                    } else {
                        wc_add_notice('No se pudo procesar tu pago: Se presentó un error. 
                    Por favor, contáctanos para poder ayudarte.', 'error');
                        wp_safe_redirect($order_url);
                        exit();
                    }
                } else {
                    if ($callback_response->resultDetails->Response == '05'){
                        wc_add_notice('No se pudo procesar tu pago: ' . $callback_response->resultDetails->ExtendedDescription .
                            ' No se pudo recibir respuesta del banco a Datafast. Si tu problema persiste, contácta tu emisor de tarjeta de crédito', 'error');
                        wp_safe_redirect($order_url);
                        exit();
                    }else {
                        wc_add_notice('No se pudo procesar tu pago: ' . $callback_response->resultDetails->ExtendedDescription .
                            '. Si tu problema persiste, contácta tu emisor de tarjeta de crédito', 'error');
                        wp_safe_redirect($order_url);
                        exit();
                    }
                }
            }else{
                wc_add_notice('No se pudo procesar tu pago: Tu orden por medio de Datafast ha caducado. Intenta nuevamente.', 'error');
                wp_safe_redirect($order_url);
                exit();
            }
        } else {
            $server_datetime = current_datetime();
            $date = date_format($server_datetime, 'Y-m-d H:i:s');
            WC_Datafast_Database_Helper::insert_hacking_warning($_SERVER['REMOTE_ADDR'], $qStrings,
                $_SERVER['HTTP_REFERER'],$date);
            wc_add_notice('No se pudo procesar tu pago: Por motivos de auditoría hemos guardado tus datos.',
                'error');
            wp_safe_redirect($order_url);
            exit();
        }
    }
}
