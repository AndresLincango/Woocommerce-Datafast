<?php

class WC_Datafast_Data_Dump
{
    //        $data = "entityId=".$datafastObj->app_identity_client."&amount=".$order_data['total']."&currency=USD&paymentType=DB";
    public static function get_request_data($entity_id, $company_name, $mid, $tid, $order_id)
    {
        $order_obj = new WC_Order($order_id);
        $order = $order_obj->get_data();
        $middle_name = 'Un solo nombre';
        $id_number = '0999999999';
        $fields_meta_df = $order_obj->get_meta_data();

        $BASE0 = 0.0;
        $BASEIMP = $order['total'] - $order['total_tax'];
        $IVA = $order['total_tax'];

        foreach ($fields_meta_df as $field_meta_df){
            $field_current_data = $field_meta_df->get_data();
            if ($field_current_data['key'] == 'billing_middle_name'){
                $middle_name = $field_current_data['value'];
            }elseif ($field_current_data['key'] == 'billing_ci'){
                $id_number = $field_current_data['value'];
            }
        }

        $data = "entityId=" . $entity_id .
            "&amount=" . $order['total'] .
            "&currency=USD&paymentType=DB".
            "&customer.givenName=" . $order['billing']['first_name'] .
            "&customer.middleName=" . $middle_name .
            "&customer.surname=" . $order['billing']['last_name'] .
            "&customer.ip=" . $_SERVER['REMOTE_ADDR'] .
            "&customer.merchantCustomerId=" . $order['customer_id'] .
            "&merchantTransactionId=transaction_" . $order['id'] .
            "&customer.email=" . $order['billing']['email'] .
            "&customer.identificationDocType=IDCARD" .
            "&customer.identificationDocId=" . $id_number .
            "&customer.phone=" . $order['billing']['phone'] .
            "&billing.street1=" . $order['billing']['address_1'] .
            "&billing.country=" . $order['billing']['country'] .
            "&billing.postcode=" . $order['total'] .
            "&shipping.street1=" . $order['shipping']['last_name'] .
            "&shipping.country=" . $order['shipping']['country'] .
            "&risk.parameters[USER_DATA2]=" . $company_name .
            "&customParameters[SHOPPER_MID]=" . $mid .
            "&customParameters[SHOPPER_TID]=" . $tid .
            "&customParameters[SHOPPER_ECI]=0103910" .
            "&customParameters[SHOPPER_PSERV]=17913101" .
            "&customParameters[SHOPPER_VAL_BASE0]=" . $BASE0 .
            "&customParameters[SHOPPER_VAL_BASEIMP]=" . $BASEIMP .
            "&customParameters[SHOPPER_VAL_IVA]=" . $IVA;
        $i = 0;
        foreach ($order["line_items"] as $item){
            $product = $item->get_product();
            $data .= "&cart.items[".$i."].name=".$product->get_name();
            if (strlen($product->get_description()) > 0){
                $data .= "&cart.items[".$i."].description=".$product->get_description();
            }else{
                $data .= "&cart.items[".$i."].description=No Hay";
            }
            $data .= "&cart.items[".$i."].price=".$product->get_price();
            $data .= "&cart.items[".$i."].quantity=".$item->get_quantity();
            $i++;
        }
        $data .= "&customParameters[SHOPPER_VERSIONDF]=2";
        $data .= "&testMode=EXTERNAL";
        return $data;
    }
}