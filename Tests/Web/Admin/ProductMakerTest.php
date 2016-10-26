<?php
/*
 * This file is part of the Maker plugin
 *
 * Copyright (C) 2016 LOCKON CO.,LTD. All Rights Reserved.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Plugin\Maker\Tests\Web\Admin;

use Faker\Generator;
use Plugin\Maker\Entity\ProductMaker;
use Plugin\Maker\Tests\Web\MakerWebCommon;
use Symfony\Component\HttpKernel\Client;

/**
 * Class ProductMakerTest.
 */
class ProductMakerTest extends MakerWebCommon
{
    const MAKER = 'plg_maker';
    const MAKER_URL = 'plg_maker_url';

    /**
     * Set up function.
     */
    public function setUp()
    {
        parent::setUp();
        $this->deleteAllRows(array('plg_product_maker', 'plg_maker'));
    }

    /**
     * Test render
     */
    public function testProductNewRender()
    {
        $crawler = $this->client->request('GET', $this->app->url('admin_product_product_new'));
        $this->assertContains('メーカー', $crawler->filter('body .container-fluid')->html());
    }

    /**
     * Test new
     */
    public function testProductNewWithoutMaker()
    {
        $crawler = $this->client->request('GET', $this->app->url('admin_product_product_new'));
        $this->assertContains('メーカー', $crawler->filter('body .container-fluid')->html());
    }

    /**
     * Test new
     */
    public function testProductNewWithMakerWithoutMakerSelect()
    {
        $Maker = $this->createMaker();

        $crawler = $this->client->request('GET', $this->app->url('admin_product_product_new'));
        $this->assertContains($Maker->getName(), $crawler->filter('body .container-fluid')->html());
    }

    /**
     * Test new
     */
    public function testProductNewWithAddMakerURLWithoutMakerSelect()
    {
        /**
         * @var Generator $faker
         */
        $faker = $this->getFaker();
        $formData = $this->createFormData();
        $formData[self::MAKER] = '';
        $formData[self::MAKER_URL] = $faker->url;

        /**
         * @var Client $client
         */
        $client = $this->client;
        $client->request(
            'POST',
            $this->app->url('admin_product_product_new'),
            array('admin_product' => $formData)
        );

        $this->assertTrue($client->getResponse()->isRedirection());

        $crawler = $client->followRedirect();

        // Check message
        $this->assertContains('登録が完了しました。', $crawler->filter('.alert')->html());

        // Check layout
        $this->assertNotContains($formData[self::MAKER_URL], $crawler->filter('body .container-fluid')->html());

        // Check database
        $arrProductMaker = $this->app['eccube.plugin.maker.repository.product_maker']->findAll();

        $this->actual = count($arrProductMaker);
        $this->expected = 0;
        $this->verify();
    }

    /**
     * Test new
     */
    public function testProductNewWithAddMakerInvalid()
    {
        $Maker = $this->createMaker();
        /**
         * @var Generator $faker
         */
        $faker = $this->getFaker();
        $formData = $this->createFormData();
        $formData[self::MAKER] = $Maker->getId() + 1;
        $formData[self::MAKER_URL] = $faker->url;

        /**
         * @var Client $client
         */
        $client = $this->client;
        $crawler = $client->request(
            'POST',
            $this->app->url('admin_product_product_new'),
            array('admin_product' => $formData)
        );

        // Check message
        $this->assertContains('登録できませんでした。', $crawler->filter('.alert')->html());
        $this->assertContains('有効な値ではありません。', $crawler->filter('.errormsg')->html());

        // Check database
        $arrProductMaker = $this->app['eccube.plugin.maker.repository.product_maker']->findAll();

        $this->actual = count($arrProductMaker);
        $this->expected = 0;
        $this->verify();
    }

    /**
     * Test new
     */
    public function testProductNewWithAddMakerWithoutMakerUrl()
    {
        $Maker = $this->createMaker();

        /**
         * @var Generator $faker
         */
        $faker = $this->getFaker();
        $formData = $this->createFormData();
        $formData[self::MAKER] = $Maker->getId();
        $formData[self::MAKER_URL] = '';

        /**
         * @var Client $client
         */
        $client = $this->client;
        $client->request(
            'POST',
            $this->app->url('admin_product_product_new'),
            array('admin_product' => $formData)
        );

        $this->assertTrue($client->getResponse()->isRedirection());

        $arrTmp = explode('/', $client->getResponse()->getTargetUrl());
        $productId = $arrTmp[count($arrTmp)-2];

        $crawler = $client->followRedirect();

        // Check message
        $this->assertContains('登録が完了しました。', $crawler->filter('.alert')->html());

        // Check database
        /**
         * @var ProductMaker $ProductMaker
         */
        $ProductMaker = $this->app['eccube.plugin.maker.repository.product_maker']->find($productId);

        $this->actual = $ProductMaker->getMaker()->getId();
        $this->expected = $formData[self::MAKER];
        $this->verify();
    }

    /**
     * Test render
     */
    public function testProductNewWithAddMakerAndMakerUrlInValid()
    {
        $Maker = $this->createMaker();

        /**
         * @var Generator $faker
         */
        $faker = $this->getFaker();
        $formData = $this->createFormData();
        $formData[self::MAKER] = $Maker->getId();
        $formData[self::MAKER_URL] = $faker->word;

        /**
         * @var Client $client
         */
        $client = $this->client;
        $crawler = $client->request(
            'POST',
            $this->app->url('admin_product_product_new'),
            array('admin_product' => $formData)
        );

        // Check message
        $this->assertContains('登録できませんでした。', $crawler->filter('.alert')->html());
        $this->assertContains('有効なURLではありません。', $crawler->filter('.errormsg')->html());

        // Check database
        $arrProductMaker = $this->app['eccube.plugin.maker.repository.product_maker']->findAll();

        $this->actual = count($arrProductMaker);
        $this->expected = 0;
        $this->verify();
    }

    /**
     * Test new
     */
    public function testProductNewWithAddMakerAndMakerUrlSuccess()
    {
        $Maker = $this->createMaker();

        /**
         * @var Generator $faker
         */
        $faker = $this->getFaker();
        $formData = $this->createFormData();
        $formData[self::MAKER] = $Maker->getId();
        $formData[self::MAKER_URL] = $faker->url;

        /**
         * @var Client $client
         */
        $client = $this->client;
        $client->request(
            'POST',
            $this->app->url('admin_product_product_new'),
            array('admin_product' => $formData)
        );

        $this->assertTrue($client->getResponse()->isRedirection());

        $arrTmp = explode('/', $client->getResponse()->getTargetUrl());
        $productId = $arrTmp[count($arrTmp)-2];

        $crawler = $client->followRedirect();

        // Check message
        $this->assertContains('登録が完了しました。', $crawler->filter('.alert')->html());

        // Check database
        /**
         * @var ProductMaker $ProductMaker
         */
        $ProductMaker = $this->app['eccube.plugin.maker.repository.product_maker']->find($productId);

        $this->actual = array($ProductMaker->getMaker()->getId(), $ProductMaker->getMakerUrl());
        $this->expected = array($formData[self::MAKER], $formData[self::MAKER_URL]);
        $this->verify();
    }

    /**
     * Test render
     */
    public function testProductEditRender()
    {
        $Product = $this->createProduct();

        $crawler = $this->client->request('GET', $this->app->url('admin_product_product_edit', array('id' => $Product->getId())));
        $this->assertContains('メーカー', $crawler->filter('body .container-fluid')->html());
    }

    /**
     * Test render
     */
    public function testProductEditWithMaker()
    {
        $Product = $this->createProduct();
        $Maker = $this->createMaker();

        $crawler = $this->client->request('GET', $this->app->url('admin_product_product_edit', array('id' => $Product->getId())));
        $this->assertContains($Maker->getName(), $crawler->filter('body .container-fluid')->html());
    }

    /**
     * Test new
     */
    public function testProductEditWithAddMakerURLWithoutMakerSelect()
    {
        $Product = $this->createProduct(null, 1);
        $ProductType = $this->app['eccube.repository.master.product_type']->find(1);
        $Product->getProductClasses()->first()->setProductType($ProductType);
        $this->app['orm.em']->persist($Product);
        $this->app['orm.em']->flush($Product);

        $this->createMaker();

        /**
         * @var Generator $faker
         */
        $faker = $this->getFaker();
        $formData = $this->createFormData();
        unset($formData['class']);
        $formData[self::MAKER] = '';
        $formData[self::MAKER_URL] = $faker->url;

        /**
         * @var Client $client
         */
        $client = $this->client;
        $client->request(
            'POST',
            $this->app->url('admin_product_product_edit', array('id' => $Product->getId())),
            array('admin_product' => $formData)
        );

        $this->assertTrue($client->getResponse()->isRedirection());
        $crawler = $client->followRedirect();

        // Check message
        $this->assertContains('登録が完了しました。', $crawler->filter('.alert')->html());

        // Check database
        $arrProductMaker = $this->app['eccube.plugin.maker.repository.product_maker']->findAll();

        $this->actual = count($arrProductMaker);
        $this->expected = 0;
        $this->verify();
    }

    /**
     * Test Edit
     */
    public function testProductEditWithAddMakerInvalid()
    {
        $Product = $this->createProduct(null, 1);
        $ProductType = $this->app['eccube.repository.master.product_type']->find(1);
        $Product->getProductClasses()->first()->setProductType($ProductType);
        $this->app['orm.em']->persist($Product);
        $this->app['orm.em']->flush($Product);
        $this->createMaker();

        /**
         * @var Generator $faker
         */
        $faker = $this->getFaker();
        $formData = $this->createFormData();
        unset($formData['class']);
        $formData[self::MAKER] = 99999;
        $formData[self::MAKER_URL] = $faker->url;

        /**
         * @var Client $client
         */
        $client = $this->client;
        $crawler = $client->request(
            'POST',
            $this->app->url('admin_product_product_edit', array('id' => $Product->getId())),
            array('admin_product' => $formData)
        );

        // Check message
        $this->assertContains('登録できませんでした。', $crawler->filter('.alert')->html());
        $this->assertContains('有効な値ではありません。', $crawler->filter('.errormsg')->html());

        // Check database
        $arrProductMaker = $this->app['eccube.plugin.maker.repository.product_maker']->findAll();

        $this->actual = count($arrProductMaker);
        $this->expected = 0;
        $this->verify();
    }

    /**
     * Test Edit
     */
    public function testProductEditWithAddMakerWithoutMakerUrl()
    {
        $Product = $this->createProduct(null, 1);
        $ProductType = $this->app['eccube.repository.master.product_type']->find(1);
        $Product->getProductClasses()->first()->setProductType($ProductType);
        $this->app['orm.em']->persist($Product);
        $this->app['orm.em']->flush($Product);
        $Maker = $this->createMaker();

        /**
         * @var Generator $faker
         */
        $faker = $this->getFaker();
        $formData = $this->createFormData();
        unset($formData['class']);
        $formData[self::MAKER] = $Maker->getId();
        $formData[self::MAKER_URL] = '';

        /**
         * @var Client $client
         */
        $client = $this->client;
        $client->request(
            'POST',
            $this->app->url('admin_product_product_edit', array('id' => $Product->getId())),
            array('admin_product' => $formData)
        );

        $this->assertTrue($client->getResponse()->isRedirection());
        $crawler = $client->followRedirect();

        // Check message
        $this->assertContains('登録が完了しました。', $crawler->filter('.alert')->html());

        // Check database
        $ProductMaker = $this->app['eccube.plugin.maker.repository.product_maker']->find($Product);

        $this->actual = array($ProductMaker->getMaker()->getId(), $ProductMaker->getMakerUrl());
        $this->expected = array($Maker->getId(), $formData[self::MAKER_URL]);
        $this->verify();
    }

    /**
     * Test render
     */
    public function testProductEditWithAddMakerAndMakerUrlInValid()
    {
        $Product = $this->createProduct(null, 1);
        $ProductType = $this->app['eccube.repository.master.product_type']->find(1);
        $Product->getProductClasses()->first()->setProductType($ProductType);
        $this->app['orm.em']->persist($Product);
        $this->app['orm.em']->flush($Product);
        $Maker = $this->createMaker();

        /**
         * @var Generator $faker
         */
        $faker = $this->getFaker();
        $formData = $this->createFormData();
        unset($formData['class']);
        $formData[self::MAKER] = $Maker->getId();
        $formData[self::MAKER_URL] = $faker->word;

        /**
         * @var Client $client
         */
        $client = $this->client;
        $crawler = $client->request(
            'POST',
            $this->app->url('admin_product_product_edit', array('id' => $Product->getId())),
            array('admin_product' => $formData)
        );

        // Check message
        $this->assertContains('登録できませんでした。', $crawler->filter('.alert')->html());
        $this->assertContains('有効なURLではありません。', $crawler->filter('.errormsg')->html());

        // Check database
        $arrProductMaker = $this->app['eccube.plugin.maker.repository.product_maker']->findAll();

        $this->actual = count($arrProductMaker);
        $this->expected = 0;
        $this->verify();
    }

    /**
     * Test Edit
     */
    public function testProductEditWithAddMakerAndMakerUrlSuccess()
    {
        $Product = $this->createProduct(null, 1);
        $ProductType = $this->app['eccube.repository.master.product_type']->find(1);
        $ProductClass = $Product->getProductClasses()->first();
        $ProductClass->setProductType($ProductType);
        $this->app['orm.em']->persist($ProductClass);
        $this->app['orm.em']->persist($Product);
        $this->app['orm.em']->flush();
        $Maker = $this->createMaker();

        /**
         * @var Generator $faker
         */
        $faker = $this->getFaker();
        $formData = $this->createFormData();
        unset($formData['class']);
        $formData[self::MAKER] = $Maker->getId();
        $formData[self::MAKER_URL] = '';

        /**
         * @var Client $client
         */
        $client = $this->client;
        $client->request(
            'POST',
            $this->app->url('admin_product_product_edit', array('id' => $Product->getId())),
            array('admin_product' => $formData)
        );

        $this->assertTrue($client->getResponse()->isRedirection());
        $crawler = $client->followRedirect();

        // Check message
        $this->assertContains('登録が完了しました。', $crawler->filter('.alert')->html());

        // Check database
        $ProductMaker = $this->app['eccube.plugin.maker.repository.product_maker']->find($Product);

        $this->actual = array($ProductMaker->getMaker()->getId(), $ProductMaker->getMakerUrl());
        $this->expected = array($Maker->getId(), $formData[self::MAKER_URL]);
        $this->verify();
    }

    /**
     * Create product form to submit.
     *
     * @return array
     */
    private function createFormData()
    {
        /**
         * @var Generator $faker
         */
        $faker = $this->getFaker();
        $form = array(
            'class' => array(
                'product_type' => 1,
                'price01' => $faker->randomNumber(5),
                'price02' => $faker->randomNumber(5),
                'stock' => $faker->randomNumber(3),
                'stock_unlimited' => 0,
                'code' => $faker->word,
                'sale_limit' => null,
                'delivery_date' => '',
            ),
            'name' => $faker->word,
            'product_image' => null,
            'description_detail' => $faker->text,
            'description_list' => $faker->paragraph,
            'Category' => null,
            'Tag' => 1,
            'search_word' => $faker->word,
            'free_area' => $faker->text,
            'Status' => 1,
            'note' => $faker->text,
            'tags' => null,
            'images' => null,
            'add_images' => null,
            'delete_images' => null,
            '_token' => 'dummy',
        );

        return $form;
    }
}
