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
                    $order_id = WC_Datafast_Database_Helper::select_order($callback_id, 'order_meta');
                    $order_obj = new WC_Order($order_id);
                    $fields_meta_df = $order_obj->get_meta_data();

                    // Here we check if the field exists or not. If isn't set we add it to metadata.
                    // In this case, we are using a woocommerce module named admin field something to add metafields.
                    $billing_months_found = false;
                    $billing_months_is_set = false;
                    $billing_months = '';
                    $billing_credit_type_found = false;
                    $billing_credit_type_is_set = false;
                    $credit_type = '';
                    $billing_interests_found = false;
                    $billing_total_no_int = false; // Original total value

                    $payment_order_data = $order_obj->get_data();
                    $total_no_int = $payment_order_data['total'];

                    try{
                        $billing_months = $orderDataJSON->recurring->numberOfInstallments;
                        $billing_months_is_set = true;
                    }catch (Exception $e){
                        print $e;
                    }

                    try{
                        $credit_type = WC_Datafast_Card_Data::get_credit_type($orderDataJSON->customParameters->SHOPPER_TIPOCREDITO);
                        $billing_credit_type_is_set = true;
                    }catch (Exception $e){
                        print $e;
                    }


                    foreach ($fields_meta_df as $field_meta_df){
                        $field_current_data = $field_meta_df->get_data();

                        if ($field_current_data['key'] == 'billing_meses' and $billing_months_found == false){
                            $order_obj->update_meta_data($field_current_data['key'],
                                $orderDataJSON->recurring->numberOfInstallments, $field_current_data['id']);
                            $order_obj->save_meta_data();
                            $billing_months_found = true;
                        }

                        if ($field_current_data['key'] == 'billing_credito' and $billing_credit_type_found == false and
                            $billing_credit_type_is_set == true){
                            $order_obj->update_meta_data($field_current_data['key'],
                                $credit_type, $field_current_data['id']);
                            $order_obj->save_meta_data();
                            $billing_credit_type_found = true;
                        }

                        if ($field_current_data['key'] == 'billing_interest' and $billing_interests_found == false){
                            $order_obj->update_meta_data($field_current_data['key'], '$' . $orderDataJSON->resultDetails->Interest, $field_current_data['id']);
                            $order_obj->save_meta_data();
                            $billing_interests_found = true;
                        }

                        if ($field_current_data['key'] == 'billing_tot' and $billing_total_no_int == false){
                            $order_obj->update_meta_data($field_current_data['key'], '$' . $total_no_int, $field_current_data['id']);
                            $order_obj->save_meta_data();
                            $billing_total_no_int = true;
                        }
                    }
                    // Updating the metadata allows woocommerce to show this fields in the order details.
                    if($billing_months_found == false and $billing_months_is_set == true){
                        $order_obj->add_meta_data('billing_meses', $billing_months);
                    }
                    if($billing_credit_type_found == false and $billing_credit_type_is_set == true){
                        $order_obj->add_meta_data('billing_credito', $credit_type);
                    }
                    if($billing_interests_found == false){
                        $order_obj->add_meta_data('billing_interest', '$' . $orderDataJSON->resultDetails->Interest);
                    }
                    if($billing_total_no_int == false){
                        $order_obj->add_meta_data('billing_tot', '$' . $total_no_int);
                    }

                    $card_name = WC_Datafast_Card_Data::get_card_name($orderDataJSON->resultDetails->CardType);
                    try {
                        $order_obj->set_payment_method_title($card_name);
                    } catch (WC_Data_Exception $e) {
                        print $e;
                    }
                    try {
                        $order_obj->set_total($orderDataJSON->resultDetails->TotalAmount);
                    } catch (WC_Data_Exception $e) {
                        print $e;
                    }
                    $order_obj->save();

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
                        $orderDataJSON->card->expiryYear, $date, $orderDataJSON->timestamp, $billing_months, $credit_type);
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
