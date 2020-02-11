<?php
error_reporting(0);

include 'db.php';

function identifyRegionByPhone($phone)
{
    $message = '';

    if (preg_match("^((8|\+7)[\- ]?)?(\(?\d{3}\)?[\- ]?)?[\d\- ]{7,10}$", $phone)) {
        $message = 'Некорректный формат номера';
    }

    $phone = str_replace('+', '', $phone);
    $phone = str_replace('-', '', $phone);
    $phone = str_replace(' ', '', $phone);
    $phone = str_replace('(', '', $phone);
    $phone = str_replace(')', '', $phone);

    $code = $phone[1].$phone[2].$phone[3];
    $number = '';

    for($i = 4; $i <= 10; $i++) {
        $number .= $phone[$i];
    }

    //var_dump($code, $number);

    $DB = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME, DB_USER, DB_PASS);
    $STH = $DB->prepare("SELECT * FROM `phone_codes` WHERE `def_code` = :code");
    $STH->execute(['code' => $code]);
    $region = '';
    if ($STH->rowCount() > 0) {
        while ($row = $STH->fetch()) {
            if ( ((int)$row['numbers_range_f'] < (int)$number)
                && ((int)$row['numbers_range_t'] > (int)$number) ) {
                $region = $row['region'];
                break;
            }
        }
    }

    if ($region != '') {
        $message = $row['region'];
    } else {
        $message = 'Не удалось определить номер';
    }

    return $message;
}

if (isset($_GET['phone'])) {
    $phone = trim($_GET['phone']);

    // Проверяем номер
}

$phone = '+7 (992) 231-21-93';

echo identifyRegionByPhone($phone);