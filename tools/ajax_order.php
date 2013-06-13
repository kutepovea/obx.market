<?php

use OBX\Core\Tools;
use OBX\Market\Basket;
use OBX\Market\BasketList;
use OBX\Market\Price;
use OBX\Market\CurrencyFormatDBS;
use OBX\Market\Order;
use OBX\Market\OrderList;


//Заголовки для предотвращения кеширования и указания типа данных JSON
header('Cache-Control: no-cache, must-revalidate');

header('Content-type: application/json');

/*
 *************** ORDER FIELDS ***************
 *
 *
 * 'ID' => self::FLD_T_INT | self::FLD_NOT_NULL,
 * 'DATE_CREATED' => self::FLD_T_NO_CHECK,
 * 'TIMESTAMP_X' => self::FLD_T_NO_CHECK,
 * 'USER_ID' => self::FLD_T_USER_ID | self::FLD_NOT_NULL | self::FLD_DEFAULT | self::FLD_REQUIRED,
 * 'STATUS_ID' => self::FLD_T_INT | self::FLD_NOT_NULL | self::FLD_DEFAULT | self::FLD_REQUIRED,
 * 'DELIVERY_ID' => self::FLD_T_INT,
 * 'DELIVERY_COST' => self::FLD_T_FLOAT,
 * 'PAY_ID' => self::FLD_T_INT,
 * 'PAY_TAX_VALUE' => self::FLD_T_FLOAT,
 * 'DISCOUNT_ID' => self::FLD_T_INT,
 * 'DISCOUNT_VALUE' => self::FLD_T_FLOAT
 */


require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');
IncludeModuleLangFile(__FILE__);

$arJSON = array(
	'messages' => array()
);

if (!CModule::IncludeModule('obx.market')) {
	$arJSON['messages'][] = array(
		'TYPE' => 'E',
		'TEXT' => GetMessage('OBX_MARKET_MODULE_NOT_INSTALLED'),
		'CODE' => 1
	);
} else {

	if (!empty($_REQUEST["MAKE_ORDER"])) {

		$CurrentBasket = Basket::getCurrent();

		$newOrderID = OrderList::add(array("USER_ID" => $CurrentBasket->getFields("USER_ID")));
		if ($newOrderID <= 0) {
			$arError = OrderList::popLastError("ARRAY");
		}

		$OrderBasket = Basket::getByOrderID($newOrderID);

		$OrderBasket->mergeBasket($CurrentBasket, true);
		unset($CurrentBasket);

		if ($OrderBasket->popLastError() == null) {
			//$arJSON['success'] =
		}else{

		}

	};


}


//print_r($arJSON);
echo json_encode($arJSON);

require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_after.php');
