<?php
/**
 * @package     Joomla.Test
 * @subpackage  Webdriver
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

require_once 'JoomlaWebdriverTestCase.php';

use SeleniumClient\By;
use SeleniumClient\SelectElement;
use SeleniumClient\WebDriver;
use SeleniumClient\WebDriverWait;
use SeleniumClient\DesiredCapabilities;

/**
 * This class tests the  Banner: Add / Edit  Screen.
 *
 * @package     Joomla.Test
 * @subpackage  Webdriver
 * @since       3.2
 */
class BannerManager0002Test extends JoomlaWebdriverTestCase
{

  /**
	 * The page class being tested.
	 *
	 * @var     bannerManagerPage
	 * @since   3.2
	 */
	protected $bannerManagerPage = null;

	/**
	 * Login to back end and navigate to menu Banner.
	 *
	 * @since   3.2
	 */
	public function setUp()
	{
		parent::setUp();
		$cpPage = $this->doAdminLogin();
		$this->bannerManagerPage = $cpPage->clickMenu('Banners', 'BannerManagerPage');
	}

	/**
	 * Logout and close test.
	 *
	 * @since   3.2
	 */
	public function tearDown()
	{
		$this->doAdminLogout();
		parent::tearDown();
	}

	/**
	 * @test
	 */
	public function getFilters_GetListOfFilters_ShouldMatchExpected()
	{
		$actualIds = $this->bannerManagerPage->getFilters();
		$expectedIds = array_values($this->bannerManagerPage->filters);
		$this->assertEquals($expectedIds, $actualIds, 'Filter ids should match expected');
	}

	/**
	 * @test
	 */
	public function setFilter_SetFilterValues_ShouldExecuteFilter()
	{
		$salt = rand();
		$bannerName = 'Test Filter' . $salt;
		$this->bannerManagerPage->addBanner($bannerName, false);
		$message = $this->bannerManagerPage->getAlertMessage();
		$this->assertTrue(strpos($message, 'Banner successfully saved') >= 0, 'Banner save should return success');
		$test = $this->bannerManagerPage->setFilter('filter_state', 'Unpublished');
		$this->assertFalse($this->bannerManagerPage->getRowNumber($bannerName), 'Banner should not show');
		$test = $this->bannerManagerPage->setFilter('filter_state', 'Published');
		$this->assertEquals(4, $this->bannerManagerPage->getRowNumber($bannerName), 'Banner should be in row 4');
		$this->bannerManagerPage->deleteItem($bannerName);
		$this->assertFalse($this->bannerManagerPage->getRowNumber($bannerName), 'Banner should not be present');
	}

	/**
	 * @test
	 */
	public function setFilter_TestFilters_ShouldFilterBanners()
	{
		$bannerName_1 = 'Test Filter 1';
		$bannerName_2 = 'Test Filter 2';

		$this->bannerManagerPage->addBanner($bannerName_1, false);
		$message = $this->bannerManagerPage->getAlertMessage();
		$this->assertTrue(strpos($message, 'Banner successfully saved') >= 0, 'Banner save should return success');
		$state = $this->bannerManagerPage->getState($bannerName_1);
		$this->assertEquals('published', $state, 'Initial state should be published');


		$this->bannerManagerPage->addBanner($bannerName_2, false);
		$message = $this->bannerManagerPage->getAlertMessage();
		$this->assertTrue(strpos($message, 'Banner successfully saved') >= 0, 'Banner save should return success');
		$state = $this->bannerManagerPage->getState($bannerName_2);
		$this->assertEquals('published', $state, 'Initial state should be published');
		$this->bannerManagerPage->changeBannerState($bannerName_2, 'unpublished');

		$test = $this->bannerManagerPage->setFilter('filter_state', 'Unpublished');
		$this->assertFalse($this->bannerManagerPage->getRowNumber($bannerName_1), 'Banner should not show');
		$this->assertEquals(1, $this->bannerManagerPage->getRowNumber($bannerName_2), 'Banner should be in row 1');

		$test = $this->bannerManagerPage->setFilter('filter_state', 'Published');
		$this->assertFalse($this->bannerManagerPage->getRowNumber($bannerName_2), 'Banner should not show');
		$this->assertEquals(4, $this->bannerManagerPage->getRowNumber($bannerName_1), 'Banner should be in row 4');

		$this->bannerManagerPage->setFilter('filter_state', 'Select Status');
		$this->bannerManagerPage->deleteItem($bannerName_1);
		$this->bannerManagerPage->deleteItem($bannerName_2);
	}

}