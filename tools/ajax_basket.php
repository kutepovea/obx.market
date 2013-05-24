<?php

use OBX\Core\Tools;
use OBX\Market\Basket;
use OBX\Market\Price;
use OBX\Market\CurrencyFormatDBS;


//Заголовки для предотвращения кеширования и указания типа данных JSON
header('Cache-Control: no-cache, must-revalidate');

header('Content-type: application/json');

require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');
IncludeModuleLangFile(__FILE__);

$arJSON = array(
	'messages' => array()
);
for($oneCycle = 0; $oneCycle < 1; $oneCycle++)
{
	if( !CModule::IncludeModule('obx.market') ) {
		$arJSON['messages'][] = array(
			'TYPE' => 'E',
			'TEXT' => GetMessage('OBX_MARKET_MODULE_NOT_INSTALLED'),
			'CODE' => 1
		);
		break;
	}

	$Basket = Basket::getCurrent();

	if( is_array($_REQUEST['add']) && count($_REQUEST['add'])>0 ) {
		foreach($_REQUEST['add'] as $productID => $quantity) {
			$productID = intval($productID);
			$quantity = intval($quantity);

			if( $Basket->isEmpty($productID) ) {
				$bSuccess = $Basket->addProduct($productID, $quantity);
			}
			else {
				$bSuccess = $Basket->setProductQuantity($productID, $quantity);
			}
			if(!$bSuccess) {
				$arJSON['messages'][] = $Basket->popLastError('ARRAY');
			}
		}
	}
	if( isset($_REQUEST['update'])
		&& isset($_REQUEST['update']['id'])
		&& isset($_REQUEST['update']['qty'])
	) {
		$productID = intval($_REQUEST['update']['id']);
		$quantity = intval($_REQUEST['update']['qty']);
		if($productID>0) {
			if( $Basket->isEmpty($productID) ) {
				$bSuccess = $Basket->addProduct($productID, $quantity);
			}
			else {
				$bSuccess = $Basket->setProductQuantity($productID, $quantity);
			}
			if(!$bSuccess) {
				$arJSON['messages'][] = $Basket->popLastError('ARRAY');
			}
		}
	}
	if( isset($_REQUEST['remove']) ) {
		$bSuccess = $Basket->removeProduct(intval($_REQUEST['remove']));
		if(!$bSuccess) {
			$arJSON['messages'][] = $Basket->popLastError('ARRAY');
		}
	}

	$arJSON['basket_cost'] = $Basket->getCost();
	$arJSON['products_count'] = $Basket->getProductsCount();
	$arJSON['product_list'] = array();

	$arProductList = $Basket->getProductsList(true);
	foreach($arProductList as &$arBasketItem) {

		$arProperties = $Basket->getProductIBlockPropertyValues($arBasketItem['PRODUCT_ID']);
		$arJsonProduct = array(
			'id' => $arBasketItem['ID'],
			'href' => $arBasketItem['IB_ELEMENT']['DETAIL_PAGE_URL'],
			'name' => $arBasketItem['IB_ELEMENT']['NAME'],
			'value' => '1',
			'price_type' => '',
			'price_value' => '',
			'section_id' => $arBasketItem['IB_ELEMENT']['SECTION_ID']
		);
		foreach($arProperties as &$arProperty) {
			if($arProperty['PROPERTY_TYPE']=='L') {
				$arJsonProduct['prop_'.$arProperty['ID']] = $arProperty['VALUE_ENUM_ID'];
			}
			else {
				$arJsonProduct['prop_'.$arProperty['ID']] = $arProperty['VALUE'];
			}
		}
		$arJSON['products_list'][] = $arJsonProduct;
	}


}
print_r($arJSON);
echo json_encode($arJSON);

require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_after.php');
?>