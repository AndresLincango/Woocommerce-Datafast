<?php

class WC_Datafast_String_Checker
{
    public static function check_string($string_query)
    {
        $q_words = array('SELECT', 'UPDATE', 'INSERT', 'CREATE', 'DROP', 'DELETE', 'REMOVE', 'UNION',
            'WHERE', 'FUNCTION');
        $return_value = true;

        foreach ($q_words as $q_word){
            $injection_value = stripos($string_query, $q_word);
            if(is_int($injection_value)) {
                $return_value = false;
            }
        }
        return $return_value;
    }
}