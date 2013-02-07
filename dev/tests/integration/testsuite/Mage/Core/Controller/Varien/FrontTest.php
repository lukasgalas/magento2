<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Magento
 * @package     Mage_Core
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Core_Controller_Varien_FrontTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Magento_ObjectManager
     */
    protected $_objectManager;

    /**
     * @var Mage_Core_Controller_Varien_Front
     */
    protected $_model;

    protected function setUp()
    {
        $this->_objectManager = Mage::getObjectManager();
        $this->_model = $this->_objectManager->create('Mage_Core_Controller_Varien_Front');
    }

    public function testSetGetDefault()
    {
        $this->_model->setDefault('test', 'value');
        $this->assertEquals('value', $this->_model->getDefault('test'));

        $default = array('some_key' => 'some_value');
        $this->_model->setDefault($default);
        $this->assertEquals($default, $this->_model->getDefault());
    }

    public function testGetRequest()
    {
        $this->assertInstanceOf('Mage_Core_Controller_Request_Http', $this->_model->getRequest());
    }

    public function testGetResponse()
    {
        if (!Magento_Test_Helper_Bootstrap::canTestHeaders()) {
            $this->markTestSkipped('Can\'t test get response without sending headers');
        }
        $this->assertInstanceOf('Mage_Core_Controller_Response_Http', $this->_model->getResponse());
    }

    public function testAddGetRouter()
    {
        $router = Mage::getModel('Mage_Core_Controller_Varien_Router_Default');
        $this->assertNull($router->getFront());
        $this->_model->addRouter('test', $router);
        $this->assertSame($this->_model, $router->getFront());
        $this->assertSame($router, $this->_model->getRouter('test'));
        $this->assertEmpty($this->_model->getRouter('tt'));
    }

    public function testGetRouters()
    {
        $this->assertEmpty($this->_model->getRouters());
        $this->_model->addRouter('test', Mage::getModel('Mage_Core_Controller_Varien_Router_Default'));
        $this->assertNotEmpty($this->_model->getRouters());
    }

    public function testInit()
    {
        $this->assertEmpty($this->_model->getRouters());
        $this->_model->init();
        $this->assertNotEmpty($this->_model->getRouters());
    }

    public function testDispatch()
    {
        if (!Magento_Test_Helper_Bootstrap::canTestHeaders()) {
            $this->markTestSkipped('Cant\'t test dispatch process without sending headers');
        }
        $_SERVER['HTTP_HOST'] = 'localhost';
        $this->_model->init();
        /* empty action */
        $this->_model->getRequest()->setRequestUri('core/index/index');
        $this->_model->dispatch();
        $this->assertEmpty($this->_model->getResponse()->getBody());
    }

    public function testGetRouterByRoute()
    {
        $this->_model->init();
        $this->assertInstanceOf('Mage_Core_Controller_Varien_Router_Base', $this->_model->getRouterByRoute(''));
        $this->assertInstanceOf(
            'Mage_Core_Controller_Varien_Router_Base',
            $this->_model->getRouterByRoute('checkout')
        );
        $this->assertInstanceOf('Mage_Core_Controller_Varien_Router_Default', $this->_model->getRouterByRoute('test'));
    }

    public function testGetRouterByFrontName()
    {
        $this->_model->init();
        $this->assertInstanceOf(
            'Mage_Core_Controller_Varien_Router_Base',
            $this->_model->getRouterByFrontName('')
        );
        $this->assertInstanceOf(
            'Mage_Core_Controller_Varien_Router_Base',
            $this->_model->getRouterByFrontName('checkout')
        );
        $this->assertInstanceOf(
            'Mage_Core_Controller_Varien_Router_Default',
            $this->_model->getRouterByFrontName('test')
        );
    }

    /**
     * @param string $sourcePath
     * @param string $resultPath
     *
     * @dataProvider applyRewritesDataProvider
     * @magentoConfigFixture global/rewrite/test_url/from /test\/(\w*)/
     * @magentoConfigFixture global/rewrite/test_url/to   new_test/$1/subdirectory
     * @magentoDataFixture Mage/Core/_files/url_rewrite.php
     * @magentoDbIsolation enabled
     */
    public function testApplyRewrites($sourcePath, $resultPath)
    {
        /** @var $request Mage_Core_Controller_Request_Http */
        $request = $this->_objectManager->create('Mage_Core_Controller_Request_Http');
        $request->setPathInfo($sourcePath);

        $this->_model->applyRewrites($request);
        $this->assertEquals($resultPath, $request->getPathInfo());
    }

    /**
     * Data provider for testApplyRewrites
     *
     * @return array
     */
    public function applyRewritesDataProvider()
    {
        return array(
            'url rewrite' => array(
                '$sourcePath' => '/test_rewrite_path',      // data from fixture
                '$resultPath' => 'cms/page/view/page_id/1', // data from fixture
            ),
            'configuration rewrite' => array(
                '$sourcePath' => '/test/url/',
                '$resultPath' => '/new_test/url/subdirectory/',
            ),
        );
    }
}
