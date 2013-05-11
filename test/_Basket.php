<?php
/***********************************************
 ** @product OBX:Market Bitrix Module         **
 ** @authors                                  **
 **         Maksim S. Makarov aka pr0n1x      **
 ** @License GPLv3                            **
 ** @mailto rootfavell@gmail.com              **
 ** @copyright 2013 DevTop                    **
 ***********************************************/

abstract class OBX_Test_Lib_Basket extends OBX_Market_TestCase
{
	/**
	 * Список товаров
	 * @var array
	 * @static
	 * @access protected
	 */
	static protected $_arPoductList = array();

	/**
	 * Тестовый инфоблок с товарами
	 * @var array
	 * @static
	 * @access protected
	 */
	static protected $_arTestIBlock = array();

	/**
	 * Содержит инфоблок не являющийся торговым
	 * @var array
	 * @static
	 * @access protected
	 */
	static protected $_arTestNotEComIBlock = array();

	/**
	 * Собъект сущности-БД корзины
	 * @var OBX_BasketDBS
	 * @static
	 * @access protected
	 */
	static protected $_BasketDBS = null;

	/**
	 * Объект сущности-БД списка товаров корзины
	 * @var OBX_BasketItemDBS
	 * @static
	 * @access protected
	 */
	static protected $_BasketItemDBS = null;

	/**
	 * Объект сущности корзины
	 * @var Array OBX_Basket
	 * @static
	 * @access private
	 */
	static protected $_BasketArray = array();


	/**
	 * Тестовый идентификатор cookie посетителя
	 * @var string
	 * @static
	 * @access protected
	 */
	static protected $_cookieID = null;

	/**
	 * Объект сущности БД цен
	 * @var OBX_PriceDBS
	 * @static
	 * @access protected
	 */
	static protected $_PriceDBS = null;

	/**
	 * Цена
	 * @var array
	 * @static
	 * @access protected
	 */
	static protected $_arPrice = 0;

	/**
	 * Объект сущности заказов БД
	 * @var OBX_OrderDBS
	 * @static
	 * @access protected
	 */
	static protected $_OrderDBS = null;

	/**
	 * Идентификатор тестового пользователя
	 * @var int
	 * @static
	 * @access protected
	 */
	static protected $_arTestUser = 0;
	/**
	 * Идентификатор ещё одого тестового пользователя
	 * @var int
	 * @static
	 * @access protected
	 */
	static protected $_arSomeOtherTestUser = 0;

	/**
	 * Заказ
	 * @var array
	 * @static
	 * @access protected
	 */
	static protected $_arTestOrder = array();

	/**
	 * Заказ ещё одного пользвтаеля
	 * @var array
	 * @static
	 * @access protected
	 */
	static protected $_arSomeOthTestOrder = array();

	/**
	 * @var OBX_ECommerceIBlockDBS
	 * @static
	 * @access protected
	 */
	static protected $_ECommerceIBlockDBS = null;

	static public function setUpBeforeClass() {
		global $USER, $_COOKIE;
		$USER->Logout();
		self::$_cookieID = md5('__TEST_COOKIE_ID__');
		// +++ cookie hack [pr0n1x:2013-05-01]
		// простой hack для эмулирвоания наличия кукисов.
		// Если задать хотя бы одно значение в $_COOKIE в cli-режиме,
		// массив так и сотается суперглобальным
		// $APPLICATION->set_cookie() не поможет ибо использует встроенную ф-ию php setcookie(),
		// а та в свою очередь не модифицирует $_COOKIE, а просто формирует header http-ответа
		// потому нужно вручную модифицировать $_COOKIE в cli-режиме, что бы отработала ф-ия $APPLICATION->get_cookie()
		$_COOKIE[COption::GetOptionString("main", "cookie_name", "BITRIX_SM")."_".OBX_Basket::COOKIE_NAME] = self::$_cookieID;
		// ^^^ cookie hack
		self::$_BasketDBS = OBX_BasketDBS::getInstance();
		self::$_BasketItemDBS = OBX_BasketItemDBS::getInstance();
		self::$_PriceDBS = OBX_PriceDBS::getInstance();
		self::$_OrderDBS = OBX_OrderDBS::getInstance();
		self::$_ECommerceIBlockDBS = OBX_ECommerceIBlockDBS::getInstance();
	}

	public function _getTestUser() {
		global $USER;
		$arFields = Array(
			'NAME'              => 'тестовый',
			'LAST_NAME'         => 'пользователь',
			'EMAIL'             => 'test@test.loc',
			'LID'               => 'ru',
			'ACTIVE'            => 'Y',
			'GROUP_ID'          => array(1,2),
			'PASSWORD'          => '123456',
			'CONFIRM_PASSWORD'  => '123456',
		);
		$rsUser1 = CUser::GetByLogin('__test_basket_user_1');
		$rsUser2 = CUser::GetByLogin('__test_basket_user_2');
		if( $arUser1 = $rsUser1->Fetch() ) {
			self::$_arTestUser = $arUser1;
		}
		else {
			$user = new CUser;
			$arFields['LOGIN'] = '__test_basket_user_1';
			$ID = $user->Add($arFields);
			$this->assertGreaterThan(0, $ID, 'Error: can\'t create test user 1. text: '.$user->LAST_ERROR);
			$rsUser1 = CUser::GetByLogin('__test_basket_user_1');
			if( $arUser1 = $rsUser1->Fetch() ) {
				$this->assertEquals('__test_basket_user_1', $arUser1['LOGIN']);
				self::$_arTestUser = $arUser1;
			}
			else {
				$this->fail('Error: can\'t get test user 1');
			}
		}
		if( $arUser2 = $rsUser2->Fetch() ) {
			self::$_arSomeOtherTestUser = $arUser2;
		}
		else {
			$user = new CUser;
			$arFields['LOGIN'] = '__test_basket_user_2';
			$ID = $user->Add($arFields);
			$this->assertGreaterThan(0, $ID, 'Error: can\'t create test user 2. text: '.$user->LAST_ERROR);
			$rsUser1 = CUser::GetByLogin('__test_basket_user_2');
			if( $arUser2 = $rsUser1->Fetch() ) {
				$this->assertEquals('__test_basket_user_2', $arUser2['LOGIN']);
				self::$_arSomeOtherTestUser = $arUser2;
			}
			else {
				$this->fail('Error: can\'t get test user 2');
			}
		}
	}

	/**
	 * @depends _getTestUser
	 */
	public function _getTestBasket() {
		self::$_BasketArray['TEST_BASKET'] = OBX_Basket::getByHash(self::$_cookieID);
		if( self::$_BasketArray['TEST_BASKET']->getFields('ID') == null) {
			$this->fail('Error: '.self::$_BasketArray['TEST_BASKET']->popLastError());
		}
	}

	protected function _addTestPrice() {
		$newPriceID = self::$_PriceDBS->add(array(
			'CURRENCY' => 'RUB',
			'NAME' => '__TEST_PRICE',
			'CODE' => '__TEST_PRICE'
		));
		if($newPriceID<1) {
			$arError = self::$_PriceDBS->popLastError('ARRAY');
			// error code = 3 - уже существует
			if($arError['CODE'] != 3) {
				$this->fail('Error: '.$arError['TEXT'].'; code: '.$arError['CODE']);
			}
		}
		$arPriceList = self::$_PriceDBS->getListArray(null, array('CODE' => '__TEST_PRICE'));
		if( empty($arPriceList) ) {
			$this->fail('test price not found.');
		}
		self::$_arPrice = $arPriceList[0];

		// Делаем цену доступной для всех пользователей
		self::$_PriceDBS->addGroup(self::$_arPrice['ID'], 2);
	}

	protected function _getTestIBlockData() {
		$rsTestIB = CIBlock::GetList(array('SORT' => 'ASC'), array('CODE' => self::OBX_TEST_IB_1));
		if( $arTestIB = $rsTestIB->GetNext() ) {

			// Делаем инфоблок торговым
			OBX_ECommerceIBlockDBS::getInstance()->add(array(
				'IBLOCK_ID' => $arTestIB['ID']
			));
			// Проверяем что инфоблок стал торговым
			$arTestEComIB = OBX_ECommerceIBlock::getByID($arTestIB['ID']);
			if( empty($arTestEComIB) ) {
				$this->fail('test iblock isn\'t an e-commerce catalog');
			}
			self::$_arTestIBlock = $arTestIB;

			// Получаем идентификор свойства являющегося содержащего цену товаров
			$rsPricePropList = CIBlockProperty::GetList(array('ID' => 'ASC'), array('IBLOCK_ID' => $arTestIB['ID'], 'CODE' => 'PRICE'));
			if( !($arPriceIBProp = $rsPricePropList->GetNext()) ) {
				$this->fail('test iblock price property not found');
			}

			// Удаляем все привязки свойств инфоблока к ценам
			OBX_CIBlockPropertyPrice::deleteByFilter(array('IBLOCK_ID' => $arTestIB['ID']));
			// Добавляем привязку свойства инфоблока к тестовой цене
			$bSuccess = OBX_CIBlockPropertyPrice::add(array(
				'IBLOCK_ID' => $arTestIB['ID'],
				'IBLOCK_PROP_ID' => $arPriceIBProp['ID'],
				'PRICE_ID' => self::$_arPrice['ID']
			));
			if(!$bSuccess) {
				$this->fail('adding price iblock property link failed');
			}
			$rsProducts = CIBlockElement::GetList(array('SORT' => 'ASC'), array(
				'IBLOCK_ID' => self::$_arTestIBlock['ID']
			), false, array('nTopCount' => '20'));
			while($arProduct = $rsProducts->GetNext()) {
				self::$_arPoductList[] = $arProduct;
			}
			$this->assertEquals(20, count(self::$_arPoductList));
		}
		else {
			$this->fail('test iblock not found');
		}
	}

	protected function _createNotECommerceIBlock() {
		$rsNotEComExists = CIBlock::GetList(array('ID' => 'ASC'), array(
			'CODE' => '__TEST_AND_DELETE',
			'IBLOCK_TYPE_ID' => self::$_arTestIBlockType['ID']
		));
		if( !($arNotEComExists = $rsNotEComExists->GetNext()) ) {
			$obIB = new CIBlock;
			$newIBlockID = $obIB->add(array(
				'ACTIVE' => 'Y',
				'NAME' => '__TEST_AND_DELETE',
				'CODE' => '__TEST_AND_DELETE',
				'LIST_PAGE_URL' => '/__TEST_AND_DELETE/',
				'DETAIL_PAGE_URL' => '/__TEST_AND_DELETE/',
				'IBLOCK_TYPE_ID' => self::$_arTestIBlockType['ID'],
				'SITE_ID' => $this->getBXSitesList(),
				'SORT' => '1',
				'DESCRIPTION' => 'TEST_DESCRIPTION',
				'DESCRIPTION_TYPE' => 'text',
				'GROUP_ID' => Array('2'=>'R', '1'=>'W')
			));
			$newIBlockID = intval($newIBlockID);
			$this->assertGreaterThan(0, $newIBlockID);
			$rsNewIBlock = CIBlock::GetByID($newIBlockID);
			if($arNewIBlock = $rsNewIBlock->GetNext()) {
				self::$_arTestNotEComIBlock = $arNewIBlock;
			}
		}
		else {
			self::$_arTestNotEComIBlock = $arNotEComExists;
		}

		$this->assertNotEmpty(self::$_arTestNotEComIBlock, 'Error: test not ecommerce iblock not created');
		self::$_arTestNotEComIBlock['__ELEMENTS_ID_LIST'] = array();

		$countTestElements = 1;
		for($iTestElement=0; $iTestElement < $countTestElements; $iTestElement++) {
			$obIBElement = new CIBlockElement;
			$newElementID = $obIBElement->add(array(
				'IBLOCK_ID' => self::$_arTestNotEComIBlock['ID'],
				'NAME' => 'test element '.$iTestElement,
				'CODE' => 'test_element_'.$iTestElement
			));
			self::$_arTestNotEComIBlock['__ELEMENTS_ID_LIST'][] = $newElementID;
		}
		$this->assertEquals($countTestElements, count(self::$_arTestNotEComIBlock['__ELEMENTS_ID_LIST']));
	}

	protected function _moveNotECommerceIBlock2ECommerceState() {
		$iblockID = self::$_ECommerceIBlockDBS->add(array('IBLOCK_ID' => self::$_arTestNotEComIBlock['ID']));
		if( $iblockID < 1 ) {
			$arError = self::$_ECommerceIBlockDBS->popLastError('ARRAY');
			$this->fail('Error: '.$arError['TEXT'].'; code: '.$arError['CODE']);
		}
	}
	protected function _moveNotECommerceIBlockStateBack() {
		$bSuccess = self::$_ECommerceIBlockDBS->delete(self::$_arTestNotEComIBlock['ID']);
		if( ! $bSuccess ) {
			$arError = self::$_ECommerceIBlockDBS->popLastError('ARRAY');
			echo ('Warning: '.$arError['TEXT'].'; code: '.$arError['CODE']."\n");
		}
	}

	protected function _addTestOrder() {
		$newOrderID = self::$_OrderDBS->add(array('USER_ID' => self::$_arTestUser['ID']));
		if($newOrderID<1) {
			$arError = self::$_OrderDBS->popLastError('ARRAY');
			$this->fail('Error: '.$arError['TEXT'].'; code: '.$arError['CODE']);
		}
		$arOrder = self::$_OrderDBS->getByID($newOrderID);
		$this->assertNotEmpty($arOrder, 'test order not found.');
		self::$_arTestOrder = $arOrder;
	}

	protected function _addSomeOtherTestOrder() {
		$newOrderID = self::$_OrderDBS->add(array('USER_ID' => self::$_arSomeOtherTestUser['ID']));
		if($newOrderID<1) {
			$arError = self::$_OrderDBS->popLastError('ARRAY');
			$this->fail('Error: '.$arError['TEXT'].'; code: '.$arError['CODE']);
		}
		$arOrder = self::$_OrderDBS->getByID($newOrderID);
		$this->assertNotEmpty($arOrder, 'test order not found.');
		self::$_arSomeOthTestOrder = $arOrder;
	}

	protected function _deleteTestOrder() {
		$bSuccess = self::$_OrderDBS->delete(self::$_arTestOrder['ID']);
		if( !$bSuccess ) {
			$arError = self::$_OrderDBS->popLastError('ARRAY');
			$this->fail('Error: '.$arError['TEXT'].'; code: '.$arError['CODE']);
		}
	}
	protected function _deleteSomeOtherTestOrder() {
		$bSuccess = self::$_OrderDBS->delete(self::$_arSomeOthTestOrder['ID']);
		if( !$bSuccess ) {
			$arError = self::$_OrderDBS->popLastError('ARRAY');
			$this->fail('Error: '.$arError['TEXT'].'; code: '.$arError['CODE']);
		}
	}
}