<?php

class WC_Datafast_Card_Data
{
    public static function get_card_name($card_used)
    {
        $credit_cards = [
            "Diners" => "DC",
            "Mastercard Pacífico" => "PM",
            "Mastercard Internacional" => "MI",
            "Visa Pacífico" => "PV",
            "Visa Internacional" => "VI",
            "Visa Pichincha" => "VP",
            "American Express" => "BG",
            "Visa Banco Guayaquil" => "VG",
            "Mastercard Banco Guayaquil" => "MG",
            "Débito Banco Guayaquil" => "DG",
            "Débito Pacificard" => "DP",
            "Mastercard Pichincha" => "MP",
            "Discover" => "DI",
            "Coop. 29 de Octubre" => "CO",
            "Visa Medianet" => "VM",
            "Visa Banco del Austro" => "VA",
            "Mastercard Medianet" => "MM",
            "Mastercard Banco del Austro" => "MA",
            "Crédito Solidario" => "CS",
            "Débito Solidario" => "DS",
            "Union Pay" => "UP",
        ];
        $card_name = array_search($card_used, $credit_cards, true);
        return $card_name;
    }

    public static function get_credit_type($credit_used)
    {
        $credit_type = [
            "Transacción Corriente" => "00",
            "Diferido Corriente " => "01",
            "Diferido con Interés" => "02"
            ];
        return array_search($credit_used, $credit_type, true);
    }

}