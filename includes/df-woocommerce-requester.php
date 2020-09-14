<?php

class WC_Datafast_Requester
{
    public static function get_params_post($order_id)
    {
        $datafastObj = new WC_Gateway_Datafast();
        $data = WC_Datafast_Data_Dump::get_request_data($datafastObj->app_identity_client, $datafastObj->company_name
            , $datafastObj->app_mid, $datafastObj->app_tid, $order_id);
        $ch = curl_init();
        $server_url = $datafastObj->server_test_url . "/v1/checkouts";
        $server_live_or_test = false;
        if ($datafastObj->enviroment == 'yes') {
            $server_url = $datafastObj->server_live_url . "/v1/checkouts";
            $server_live_or_test = true;
        }
        curl_setopt($ch, CURLOPT_URL, $server_url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization:' . $datafastObj->app_auth_client));
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $server_live_or_test);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $responseData = curl_exec($ch);
        if (curl_errno($ch)) {
            return curl_error($ch);
        }
        curl_close($ch);
        $orderDataJSON = json_decode($responseData);
        $current_order = WC_Datafast_Database_Helper::select_order($order_id, 'order');
        $server_datetime = current_datetime();
        $date = date_format($server_datetime, 'Y-m-d H:i:s');
        if (strlen($current_order) == 0) {
            WC_Datafast_Database_Helper::insert_data('checked', $orderDataJSON->result->code,
                $orderDataJSON->result->description, $order_id, $orderDataJSON->id, $date, $date);
        } else {
            WC_Datafast_Database_Helper::update_order($order_id, 'checked', $orderDataJSON->id, $date);
        }
        return $orderDataJSON->id;
    }

    public static function get_payment_status($callback_id, $resource_path)
    {
        $datafastObj = new WC_Gateway_Datafast();

        $ch = curl_init();
        $server_url = $datafastObj->server_test_url;
        $server_url .= $resource_path . "?entityId=" . $datafastObj->app_identity_client;
        $server_live_or_test = false;
        if ($datafastObj->enviroment == 'yes') {
            $server_url = $datafastObj->server_live_url;
            $server_live_or_test = true;
        }
        curl_setopt($ch, CURLOPT_URL, $server_url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization:' . $datafastObj->app_auth_client));
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $server_live_or_test);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $responseData = curl_exec($ch);
        if (curl_errno($ch)) {
            return curl_error($ch);
        }
        curl_close($ch);
        $orderDataJSON = json_decode($responseData);

        if ($callback_id == $orderDataJSON->ndc) {
            $server_datetime = current_datetime();
            $date = date_format($server_datetime, 'Y-m-d H:i:s');
            $current_transaction = WC_Datafast_Database_Helper::select_order($orderDataJSON->ndc, 'transaction');
            if (strlen($current_transaction) > 0) {
                if ($orderDataJSON->result->code == '000.100.112') {
                    WC_Datafast_Database_Helper::update_transaction_order($current_transaction, $_SERVER['REMOTE_ADDR'], 'success',
                        $orderDataJSON->result->code, $orderDataJSON->result->description, $orderDataJSON->descriptor,
                        $orderDataJSON->resultDetails->RequestId, $orderDataJSON->resultDetails->RiskFraudStatusCode,
                        $orderDataJSON->resultDetails->AuthCode, $orderDataJSON->resultDetails->ConnectorTxID1,
                        $orderDataJSON->resultDetails->ReferenceNbr, $orderDataJSON->resultDetails->BatchNo,
                        $orderDataJSON->resultDetails->EXTERNAL_SYSTEM_LINK, $orderDataJSON->resultDetails->TotalAmount,
                        $orderDataJSON->resultDetails->OrderId, $orderDataJSON->resultDetails->Response,
                        $orderDataJSON->resultDetails->Interest, $orderDataJSON->resultDetails->RiskStatusCode,
                        $orderDataJSON->resultDetails->ExtendedDescription, $orderDataJSON->resultDetails->clearingInstituteName,
                        $orderDataJSON->resultDetails->RiskResponseCode, $orderDataJSON->resultDetails->AcquirerCode,
                        $orderDataJSON->resultDetails->CardType, $orderDataJSON->resultDetails->AcquirerResponse,
                        $orderDataJSON->resultDetails->action, $orderDataJSON->resultDetails->RiskOrderId,
                        $orderDataJSON->resultDetails->ReferenceNo, $resource_path, $orderDataJSON->paymentBrand,
                        $orderDataJSON->amount, $orderDataJSON->card->bin, $orderDataJSON->card->last4Digits,
                        $orderDataJSON->card->holder, $orderDataJSON->card->expiryMonth,
                        $orderDataJSON->card->expiryYear, $date, $orderDataJSON->timestamp);
                } else {
                    WC_Datafast_Database_Helper::update_transaction_order($current_transaction, $_SERVER['REMOTE_ADDR'], 'failure',
                        $orderDataJSON->result->code, $orderDataJSON->result->description, $orderDataJSON->descriptor,
                        $orderDataJSON->resultDetails->RequestId, $orderDataJSON->resultDetails->RiskFraudStatusCode,
                        $orderDataJSON->resultDetails->AuthCode, $orderDataJSON->resultDetails->ConnectorTxID1,
                        $orderDataJSON->resultDetails->ReferenceNbr, $orderDataJSON->resultDetails->BatchNo,
                        $orderDataJSON->resultDetails->EXTERNAL_SYSTEM_LINK, $orderDataJSON->resultDetails->TotalAmount,
                        $orderDataJSON->resultDetails->OrderId, $orderDataJSON->resultDetails->Response,
                        $orderDataJSON->resultDetails->Interest, $orderDataJSON->resultDetails->RiskStatusCode,
                        $orderDataJSON->resultDetails->ExtendedDescription, $orderDataJSON->resultDetails->clearingInstituteName,
                        $orderDataJSON->resultDetails->RiskResponseCode, $orderDataJSON->resultDetails->AcquirerCode,
                        $orderDataJSON->resultDetails->CardType, $orderDataJSON->resultDetails->AcquirerResponse,
                        $orderDataJSON->resultDetails->action, $orderDataJSON->resultDetails->RiskOrderId,
                        $orderDataJSON->resultDetails->ReferenceNo, $resource_path, $orderDataJSON->paymentBrand,
                        $orderDataJSON->amount, $orderDataJSON->card->bin, $orderDataJSON->card->last4Digits,
                        $orderDataJSON->card->holder, $orderDataJSON->card->expiryMonth,
                        $orderDataJSON->card->expiryYear, $date, $orderDataJSON->timestamp);
                }
                return $orderDataJSON;
            }else{
                return false;
            }
        }
    }

    public function process_payment($orderId)
    {
        $order = new WC_Order($orderId);
        return array(
            'result' => 'success',
            'redirect' => $order->get_checkout_payment_url(true)
        );
    }
}


//WC_Datafast_Database_Helper::update_transaction_order($current_transaction, 'success',
//    $orderDataJSON->result->code, $orderDataJSON->result->description, $resource_path,
//    $orderDataJSON->buildNumber, $orderDataJSON->id, $orderDataJSON->paymentBrand, $orderDataJSON->amount,
//    $orderDataJSON->currency, $orderDataJSON->resultDetails->RiskStatusCode,
//    $orderDataJSON->resultDetails->RequestId, $orderDataJSON->resultDetails->OrderId,
//    $orderDataJSON->card->bin, $orderDataJSON->card->last4Digits, $orderDataJSON->card->holder,
//    $orderDataJSON->card->expiryMonth, $orderDataJSON->card->expiryYear, $date, $orderDataJSON->timestamp);