<?php
/* Copyright (C) 2007-2017 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2018      Jean-François Ferry  <hello+jf@librethic.io>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * \file    test/unit/SalesTest.php
 * \ingroup marketplace
 * \brief   PHPUnit test for Sales class.
 */

//namespace test\unit;

global $conf, $user, $langs, $db;
//define('TEST_DB_FORCE_TYPE','mysql');	// This is to force using mysql driver
//require_once 'PHPUnit/Autoload.php';
echo dirname(__FILE__);

require_once dirname(__FILE__) . '/../../../../htdocs/master.inc.php';
require_once dirname(__FILE__) . '/../../class/sales.class.php';


if (empty($user->id)) {
    print "Load permissions for admin user nb 1\n";
    $user->fetch(1);
    $user->getrights();
}
$conf->global->MAIN_DISABLE_ALL_MAILS = 1;


/**
 * Class SalesTest
 * @package Testmarketplace
 */
class SalesTest extends \PHPUnit_Framework_TestCase
{

    protected $savconf;
    protected $savuser;
    protected $savlangs;
    protected $savdb;

    /**
     * Constructor
     * We save global variables into local variables
     *
     * @return AccountingAccountTest
     */
    public function __construct()
    {
        parent::__construct();
        
        //$this->sharedFixture
        global $conf, $user, $langs, $db;
        $this->savconf = $conf;
        $this->savuser = $user;
        $this->savlangs = $langs;
        $this->savdb = $db;
        
        print __METHOD__ . " db->type=" . $db->type . " user->id=" . $user->id;
        //print " - db ".$db->db;
        print "\n";
    }
    
	/**
     * Global test setup
     * @return void
	 */
    public static function setUpBeforeClass()
	{
        fwrite(STDOUT, __METHOD__ . "\n");
	}
    
    /**
     * Init phpunit tests
     *
     * @return  void
     */
    protected function setUp()
    {
        global $conf, $user, $langs, $db;
        $conf = $this->savconf;
        $user = $this->savuser;
        $langs = $this->savlangs;
        $db = $this->savdb;
        
        fwrite(STDOUT, __METHOD__ . "\n");
    }
    
	/**
     * Verify pre conditions
     * @return void
	 */
    protected function assertPreConditions()
	{
        fwrite(STDOUT, __METHOD__ . "\n");
	}

    /**
     * Test care amount calculation
     * @return bool
     */
    public function testCalculateBrutAmount()
    {
        global $db;
        fwrite(STDOUT, __METHOD__ . "\n");

        $sales = new Sales($db);

        $priceNet = 8;
        $sales->discount_rate = 20;
        fwrite(STDOUT, __METHOD__ . " Calcul brut amount - net : $priceNet / discount rate=".$sales->discount_rate."\n");
        $totalHtBrut = $sales->calculatePriceBrut($priceNet);
        fwrite(STDOUT, __METHOD__ . " Result=$totalHtBrut \n");
        $this->assertEquals($totalHtBrut, 10);


        $priceNet = 5;
        $sales->discount_rate = 0;
        $sales->care_rate = 50;
        fwrite(STDOUT, __METHOD__ . " Calcul brut amount - net : $priceNet / seller rate=" . $sales->care_rate . "\n");
        $totalHtBrut = $sales->calculatePriceBrut($priceNet);
        fwrite(STDOUT, __METHOD__ . " Result=$totalHtBrut \n");
        $this->assertEquals($totalHtBrut, 10);

        $priceNet = 8;
        $sales->discount_rate = 0;
        $sales->care_rate = 20;
        fwrite(STDOUT, __METHOD__ . " Calcul brut amount - net : $priceNet / seller rate=" . $sales->care_rate . "\n");
        $totalHtBrut = $sales->calculatePriceBrut($priceNet);
        fwrite(STDOUT, __METHOD__ . " Result=$totalHtBrut \n");
        $this->assertEquals($totalHtBrut, 10);
    }
    
    /**
     * Test collection amount calculation
     * @return bool
     */
    public function testCalculateDiscountAmount()
    {
        global $db;
        fwrite(STDOUT, __METHOD__ . "\n");

        $sales = new Sales($db);

        $priceNet = 8;
        $discountRate = 20;
        $taxRate = 20;
        $sales->discount_rate = $discountRate;
        $sales->tax_rate = $taxRate;

        $totalHtBrut = $sales->calculatePriceBrut($priceNet);
        $discountAmount = $sales->calculateDiscountAmount($totalHtBrut);
        fwrite(STDOUT, __METHOD__ . " Calcul dicsount amount - net HT : $priceNet / tax rate=$taxRate / discount rate=$discountRate\n");
        fwrite(STDOUT, __METHOD__ . " Result=$discountAmount \n");
        $this->assertEquals($discountAmount, 2);
        
        $sales->discount_rate = $discountRate;
        $taxRate = 5.5;
        $sales->tax_rate = $taxRate;
        $totalHtBrut = $sales->calculatePriceBrut($priceNet);
        $discountAmount = $sales->calculateDiscountAmount($totalHtBrut);
    
        fwrite(STDOUT, __METHOD__ . "  Calcul discount amount - net HT : $priceNet / tax rate=$taxRate / discount rate=$discountRate\n");
        fwrite(STDOUT, __METHOD__ . " Result=$discountAmount \n");
        $this->assertEquals($discountAmount, 2);
    }

    /**
     * Test care amount calculation
     * @return bool
     */
    public function testCalculateCareAmount()
    {
        global $db;
        fwrite(STDOUT, __METHOD__ . "\n");

        $sales = new Sales($db);

        $priceNet = 8;
        $discountRate = 20;
        $sales->discount_rate = $discountRate;
        $sellerCareRate = 0;
        $sales->care_rate = $sellerCareRate;


        $totalHtBrut = $sales->calculatePriceBrut($priceNet);
        fwrite(STDOUT, __METHOD__ . " total brut : $totalHtBrut / seller rate=$sellerCareRate\n");
        $careAmount = $sales->calculateCareAmount($totalHtBrut);
        fwrite(STDOUT, __METHOD__ . " Result=$careAmount \n");
        $this->assertEquals($careAmount, 0);

        $sellerCareRate = 50;
        $sales->care_rate = $sellerCareRate;
        fwrite(STDOUT, __METHOD__ . " total brut : $totalHtBrut / seller rate=$sellerCareRate\n");
        $careAmount = $sales->calculateCareAmount($totalHtBrut);
        fwrite(STDOUT, __METHOD__ . " Result=$careAmount \n");
        $this->assertEquals($careAmount, 5);
    }

    /**
     * Test retrocession amount calculation
     * @return bool
     */
    public function testCalculateRetrocessionAmount()
    {
        global $db;
        fwrite(STDOUT, __METHOD__ . "\n");

        $sales = new Sales($db);

        $priceNet = 10;

        $retroRate = 0;
        $discountRate = 0;
        $sellerCareRate = 0;
        fwrite(STDOUT, __METHOD__ . " total : $priceNet / retro rate=$retroRate / discount=$discountRate / seller care rate=$sellerCareRate \n");
        $sales->discount_rate = $discountRate;
        $sales->collection_rate = $retroRate;
        $sales->care_rate = $sellerCareRate;
        $amount = $sales->calculateRetrocessionAmount($priceNet);
        fwrite(STDOUT, __METHOD__ . " Result=$amount \n");
        $this->assertEquals($amount, 0);
    
        $retroRate = 20;
        $discountRate = 0;
        $sellerCareRate = 0;

        fwrite(STDOUT, __METHOD__ . " total : $priceNet / retro rate=$retroRate / discount=$discountRate / seller care rate=$sellerCareRate \n");
        $sales->discount_rate = $discountRate;
        $sales->collection_rate = $retroRate;
        $sales->care_rate = $sellerCareRate;
        $amount = $sales->calculateRetrocessionAmount($priceNet);
        fwrite(STDOUT, __METHOD__ . " Result=$amount \n");
        $this->assertEquals($amount, 8);

        $priceNet = 8;
        $discountRate = 20;
        $sellerCareRate = 0;
        fwrite(STDOUT, __METHOD__ . " total : $priceNet / retro rate=$retroRate / discount=$discountRate / seller care rate=$sellerCareRate \n");
        $sales->discount_rate = $discountRate;
        $sales->collection_rate = $retroRate;
        $sales->care_rate = $sellerCareRate;
        $amount = $sales->calculateRetrocessionAmount($priceNet);
        fwrite(STDOUT, __METHOD__ . " Result=$amount \n");
        $this->assertEquals($amount, 8);

        $priceNet = 5;
        $discountRate = 0;
        $sellerCareRate = 50;
        fwrite(STDOUT, __METHOD__ . " total : $priceNet / retro rate=$retroRate / discount=$discountRate / seller care rate=$sellerCareRate \n");
        $sales->discount_rate = $discountRate;
        $sales->collection_rate = $retroRate;
        $sales->care_rate = $sellerCareRate;
        $amount = $sales->calculateRetrocessionAmount($priceNet);
        fwrite(STDOUT, __METHOD__ . " Result care=$amount \n");
        $this->assertEquals($amount, 3);

        $priceNet = 5;
        $discountRate = 50;
        $sellerCareRate = 0;
        fwrite(STDOUT, __METHOD__ . " total : $priceNet / retro rate=$retroRate / discount=$discountRate / seller care rate=$sellerCareRate \n");
        $sales->discount_rate = $discountRate;
        $sales->collection_rate = $retroRate;
        $sales->care_rate = $sellerCareRate;
        $amount = $sales->calculateRetrocessionAmount($priceNet);
        fwrite(STDOUT, __METHOD__ . " Result=$amount \n");
        $this->assertEquals($amount, 8);


        $this->assertTrue(true);
    }

    /**
     * Test collection amount calculation
     * @return bool
     */
    public function testCalculateCollectionAmount()
    {
        global $db;
        fwrite(STDOUT, __METHOD__ . "\n");

        $sales = new Sales($db);

        $priceNet = 10;

        $retroRate = 0;
        $discountRate = 0;
        $sellerCareRate = 0;
        fwrite(STDOUT, __METHOD__ . " total : $priceNet / retro rate=$retroRate / discount=$discountRate / seller care rate=$sellerCareRate \n");
        $sales->discount_rate = $discountRate;
        $sales->collection_rate = $retroRate;
        $sales->care_rate = $sellerCareRate;
        $amount = $sales->calculateCollectionAmount($priceNet, $sellerCareRate);
        fwrite(STDOUT, __METHOD__ . " Result=$amount \n");
        $this->assertEquals($amount, 0);

        $priceNet = 10;
        $retroRate = 20;
        $discountRate = 0;
        $sellerCareRate = 0;
        fwrite(STDOUT, __METHOD__ . " total : $priceNet / retro rate=$retroRate / discount=$discountRate / seller care rate=$sellerCareRate \n");
        $sales->discount_rate = $discountRate;
        $sales->collection_rate = $retroRate;
        $sales->care_rate = $sellerCareRate;
        $amount = $sales->calculateCollectionAmount($priceNet, $sellerCareRate);
        fwrite(STDOUT, __METHOD__ . " Result=$amount \n");
        $this->assertEquals($amount, 2);

        $priceNet = 9;
        $discountRate = 10;
        $sellerCareRate = 0;
        fwrite(STDOUT, __METHOD__ . " total : $priceNet / retro rate=$retroRate / discount=$discountRate / seller care rate=$sellerCareRate \n");
        $sales->discount_rate = $discountRate;
        $sales->collection_rate = $retroRate;
        $sales->care_rate = $sellerCareRate;
        $amount = $sales->calculateCollectionAmount($priceNet, $sellerCareRate);
        fwrite(STDOUT, __METHOD__ . " Result=$amount \n");
        $this->assertEquals($amount, 1);

        $priceNet = 5;
        $discountRate = 0;
        $sellerCareRate = 50;
        fwrite(STDOUT, __METHOD__ . " total : $priceNet / retro rate=$retroRate / discount=$discountRate / seller care rate=$sellerCareRate \n");
        $sales->discount_rate = $discountRate;
        $sales->collection_rate = $retroRate;
        $sales->care_rate = $sellerCareRate;
        $amount = $sales->calculateCollectionAmount($priceNet, $sellerCareRate);
        fwrite(STDOUT, __METHOD__ . " Result=$amount \n");
        $this->assertEquals($amount, 2);

        $this->assertTrue(true);
    }

	/**
	 * Verify post conditions
     * @return void
	 */
	protected function assertPostConditions()
	{
		fwrite(STDOUT, __METHOD__ . "\n");
	}

	/**
	 * Unit test teardown
     * @return void
	 */
	protected function tearDown()
	{
		fwrite(STDOUT, __METHOD__ . "\n");
	}

	/**
	 * Global test teardown
     * @return void
	 */
	public static function tearDownAfterClass()
	{
		fwrite(STDOUT, __METHOD__ . "\n");
	}

	/**
	 * Unsuccessful test
	 *
	 * @param  Exception $e    Exception
     * @return void
	 * @throws Exception
	 */
	protected function onNotSuccessfulTest(Exception $e)
	{
		fwrite(STDOUT, __METHOD__ . "\n");
		throw $e;
	}
}
