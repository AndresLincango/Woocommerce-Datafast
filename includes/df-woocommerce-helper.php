<?php

class WC_Datafast_Database_Helper
{
    public static function create_database()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'datafast_plugin';
        $table_name_hacking = $wpdb->prefix . 'hacking_warning';
        if ($wpdb->get_var('SHOW TABLES LIKES ' . $table_name) != $table_name) {
            $sql1 = 'CREATE TABLE ' . $table_name . ' (
               id integer(9) unsigned NOT NULL AUTO_INCREMENT,
               user_ip varchar (20),
               status varchar(10),
               code varchar(15),
               description text(250),
               descriptor varchar(200),
               request_id varchar(100),
               risk_fraud_status_code varchar(50),
               auth_code varchar(10),
               connector_tx varchar(50),
               reference_nbr varchar(20),               
               batch_no varchar(8),               
               external_system_link varchar(250),               
               total_amount varchar(10),
               datafast_order_id varchar(100),
               response varchar(10),
               interest varchar(4),
               risk_status_code varchar(20),
               extended_description varchar(200),         
               clearing_institute_name varchar(50),               
               risk_response_code varchar(10),              
               acquirer_code varchar(4),
               card_type varchar(10),
               acquirer_response varchar(10),
               action varchar(20),
               risk_order_id varchar(50),
               reference_no varchar(20),
                          
               order_id int(9) NOT NULL,
               transaction_id varchar(80),
               resource_path varchar(80),
               payment_brand varchar(20),
               amount varchar(20),
               currency varchar(10) default "USD",

               card_bin varchar(15),
               card_last_4 varchar(10),
               card_holder varchar(250),
               expiry_month varchar(4),
               expiry_year varchar(8),
               date_created datetime default "0000-00-00 00:00:00",
               date_updated datetime default "0000-00-00 00:00:00",
               date_payment_done varchar(40),
               PRIMARY KEY  (id)
               );';

            $sql2 = 'CREATE TABLE ' . $table_name_hacking . ' (
               id integer(9) unsigned NOT NULL AUTO_INCREMENT,
               user_ip varchar (20),
               description text(250),
               referer int(9) NOT NULL,
               date_created datetime default "0000-00-00 00:00:00",
               PRIMARY KEY  (id)
               );';
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql1);
            dbDelta($sql2);
        }
    }

    public static function delete_database()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'datafast_plugin';
        $table_name_hacking = $wpdb->prefix . 'hacking_warning';
        $sql = "DROP TABLE IF EXISTS $table_name";
        $sql2 = "DROP TABLE IF EXISTS $table_name_hacking";
        require_once(ABSPATH . '/wp-admin/includes/upgrade.php');
        $wpdb->query($sql);
        $wpdb->query($sql2);
    }

    public static function insert_data($status, $code, $description, $order_id, $transaction_id, $date_created = null, $date_updated = null)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'datafast_plugin';
        $wpdb->insert($table_name, array(
            'status' => $status,
            'code' => $code,
            'description' => $description,
            'order_id' => $order_id,
            'transaction_id' => $transaction_id,
            'date_created' => $date_created,
            'date_updated' => $date_updated
        ), array(
                "'" . $status . "'",
                "'" . $code . "'",
                "'" . $description . "'",
                "'" . $order_id . "'",
                "'" . $transaction_id . "'",
                "'" . $date_created . "'",
                "'" . $date_updated . "'",
            )
        );
    }

    public static function insert_hacking_warning($user_ip, $description, $referer, $date_created)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'hacking_warning';
        $wpdb->insert($table_name, array(
            'user_ip' => $user_ip,
            'description' => $description,
            'referer' => $referer,
            'date_created' => $date_created,
        ), array(
                "'" . $user_ip . "'",
                "'" . $description . "'",
                "'" . $referer . "'",
                "'" . $date_created . "'",
            )
        );
    }

    public static function update_order($order_id, $status, $transaction_id, $date_updated)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'datafast_plugin';
        $result = $wpdb->update($table_name, array('status' => $status, 'transaction_id' => $transaction_id,
            'date_updated' => $date_updated), array('order_id' => $order_id), array(
            "'" . $status . "'",
            "'" . $transaction_id . "'",
            "'" . $date_updated . "'",
        ), array("'" . $order_id . "'"));
        if ($result > 0) {
            echo "";
        } else {
            exit(var_dump($wpdb->last_query));
        }
        $wpdb->flush();
    }

    public static function update_transaction_order($transaction_id, $user_ip, $status, $code, $description, $descriptor, $request_id,
                                                    $risk_fraud_status_code, $auth_code, $connector_tx, $reference_nbr,
                                                    $batch_no, $external_system_link, $total_amount, $datafast_order_id,
                                                    $response, $interest, $risk_status_code, $extended_description,
                                                    $clearing_institute_name, $risk_response_code, $acquirer_code,
                                                    $card_type, $acquirer_response, $action, $risk_order_id,
                                                    $reference_no, $resource_path, $payment_brand, $amount, $card_bin,
                                                    $card_last_4, $card_holder, $expiry_month, $expiry_year,
                                                    $date_updated, $date_payment_done)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'datafast_plugin';
        $result = $wpdb->update($table_name, array('user_ip' => $user_ip, 'status' => $status, 'code' => $code, 'description' => $description,
            'descriptor' => $descriptor, 'request_id' => $request_id, 'risk_fraud_status_code' => $risk_fraud_status_code,
            'auth_code' => $auth_code, 'connector_tx' => $connector_tx, 'reference_nbr' => $reference_nbr,
            'batch_no' => $batch_no, 'external_system_link' => $external_system_link,
            'total_amount' => $total_amount, 'datafast_order_id' => $datafast_order_id, 'response' => $response,
            'interest' => $interest, 'risk_status_code' => $risk_status_code, 'extended_description' => $extended_description,
            'clearing_institute_name' => $clearing_institute_name, 'risk_response_code' => $risk_response_code,
            'acquirer_code' => $acquirer_code, 'card_type' => $card_type, 'acquirer_response' => $acquirer_response,
            'action' => $action, 'risk_order_id' => $risk_order_id, 'reference_no' => $reference_no,
            'resource_path' => $resource_path, 'payment_brand' => $payment_brand, 'amount' => $amount,
            'card_bin' => $card_bin, 'card_last_4' => $card_last_4, 'card_holder' => $card_holder,
            'expiry_month' => $expiry_month, 'expiry_year' => $expiry_year, 'date_updated' => $date_updated,
            'date_payment_done' => $date_payment_done),
            array('transaction_id' => $transaction_id), array(
                '"' . $user_ip . '"',
                '"' . $status . '"',
                "'" . $code . "'",
                '"' . $description . '"',
                '"' . $descriptor . '"',
                "'" . $request_id . "'",
                "'" . $risk_fraud_status_code . "'",
                "'" . $auth_code . "'",
                "'" . $connector_tx . "'",
                "'" . $reference_nbr . "'",
                "'" . $batch_no . "'",
                "'" . $external_system_link . "'",
                "'" . $total_amount . "'",
                "'" . $datafast_order_id . "'",
                "'" . $response . "'",
                "'" . $interest . "'",
                "'" . $risk_status_code . "'",
                "'" . $extended_description . "'",
                "'" . $clearing_institute_name . "'",
                "'" . $risk_response_code . "'",
                "'" . $acquirer_code . "'",
                "'" . $card_type . "'",
                "'" . $acquirer_response . "'",
                "'" . $action . "'",
                "'" . $risk_order_id . "'",
                "'" . $reference_no . "'",
                '"' . $resource_path . '"',
                "'" . $payment_brand . "'",
                "'" . $amount . "'",
                "'" . $card_bin . "'",
                "'" . $card_last_4 . "'",
                "'" . $card_holder . "'",
                "'" . $expiry_month . "'",
                "'" . $expiry_year . "'",
                "'" . $date_updated . "'",
                "'" . $date_payment_done . "'",
            ), array('"' . $transaction_id . '"'));

        if ($result == 0) {
            exit(var_dump($wpdb->last_query));
        }
        $wpdb->flush();
    }

    public static function select_order($requested_id, $return_type)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'datafast_plugin';
        $required_id = '';
        if ($return_type == 'order') {
            //Searches for saved order with given order_id so the plugin updates de order instead of create a new one.
            $rows = $wpdb->get_results("SELECT * FROM $table_name where order_id = '$requested_id' and status = 'checked'", OBJECT);
            if (count($rows) == 1) {
                foreach ($rows as $row) {
                    $required_id = $row->order_id;
                }
            }
        } else {
            if ($return_type == 'transaction') {
                //Searches for saved order with given transaction_id so the plugin updates de whole
                // transaction on APPROVE.
                $rows = $wpdb->get_results("SELECT * FROM $table_name where transaction_id = '$requested_id' and status = 'checked'", OBJECT);
                foreach ($rows as $row) {
                    $required_id = $row->transaction_id;
                }
            } else {
                //Returns order_id given a transaction_id
                $rows = $wpdb->get_results("SELECT * FROM $table_name where transaction_id = '$requested_id' and status = 'success'", OBJECT);
                foreach ($rows as $row) {
                    $required_id = $row->order_id;
                }
            }

        }

        return $required_id;
    }
}
