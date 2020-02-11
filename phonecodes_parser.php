<?php

// Парсер базы кодов

include 'db.php';
include 'simple_html_dom.php';

set_time_limit(300);

class PhoneCodesParser
{
    private $url = 'https://www.mtt.ru/defcodes/getDefcodes/';
    private $base = null;

    public function parse()
    {
        $curl = curl_init($this->url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION , false);
        $res = curl_exec($curl);
        curl_close($curl);
        $html = htmlspecialchars_decode(json_decode($res)->template);

        $html = str_get_html($html);

        $codes = $html->find('div.number-row');
        unset($codes[0]);

        $phone_codes = [];
        $i = 0;
        foreach($codes as $code) {
            $cols = $code->find('div[class=col]');
            $phone_codes[$i]['def_code'] = $cols[0]->innertext;

            $numbers_range = explode('-', $cols[1]->innertext);
            $phone_codes[$i]['numbers_range_f'] = $numbers_range[0];
            $phone_codes[$i]['numbers_range_t'] = $numbers_range[1];

            $phone_codes[$i]['region'] = $cols[2]->innertext;
            //$phone_codes[$i]['operator'] = $cols[3]->innertext;

            $i++;
        }

        $this->base = $phone_codes;

        return $this;
    }

    public function saveToDatabase()
    {
        $DB = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME, DB_USER, DB_PASS);

        foreach($this->base as $phone_code) {
            $STH = $DB->prepare("INSERT INTO `phone_codes` (`def_code`, `numbers_range_f`, `numbers_range_t`, `region`) 
                                    VALUES (:def_code, :numbers_range_f, :numbers_range_t, :region)");
            $STH->execute($phone_code);
        }

        return $this;
    }
}

$parser = new PhoneCodesParser();
$parser->parse()->saveToDatabase();