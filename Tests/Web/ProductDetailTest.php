<?php
/*
 * This file is part of the Maker plugin
 *
 * Copyright (C) 2016 LOCKON CO.,LTD. All Rights Reserved.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Plugin\Maker\Tests\Web;

use Eccube\Common\Constant;
use Eccube\Entity\Product;
use Eccube\Tests\Web\AbstractWebTestCase;
use Faker\Generator;
use Plugin\Maker\Entity\Maker;
use Plugin\Maker\Entity\ProductMaker;

/**
 * Class ProductDetailTest
 * Hook point test
 */
class ProductDetailTest extends AbstractWebTestCase
{
    /**
     * @var Maker $maker
     */
    private $maker;

    /**
     * @var ProductMaker $productMaker
     */
    private $productMaker;

    /**
     * Set up function.
     */
    public function setUp()
    {
        parent::setUp();
        $this->deleteAllRows(array('plg_product_maker', 'plg_maker'));
        $this->maker = $this->createMaker();

        $this->productMaker = $this->createProductMaker($this->maker);
    }

    /**
     * Product detail render test maker
     */
    public function testProductDetailWhenHasMakerButUnRegister()
    {
        $productId = $this->productMaker->getId();
        $this->app['orm.em']->remove($this->productMaker);
        $this->app['orm.em']->flush($this->productMaker);
        $crawler = $this->client->request('GET', $this->app->url('product_detail', array('id' => $productId)));
        $html = $crawler->filter('.item_detail')->html();
        $this->assertNotContains('メーカーコード', $html);
        $this->assertNotContains('メーカーURL', $html);
    }

    /**
     * Product detail render test maker
     */
    public function testProductDetailWhenRegisterMakerWithoutMakerUrl()
    {
        $productId = $this->productMaker->getId();
        $this->productMaker->setMakerUrl('');
        $this->app['orm.em']->persist($this->productMaker);
        $this->app['orm.em']->flush($this->productMaker);

        $crawler = $this->client->request('GET', $this->app->url('product_detail', array('id' => $productId)));

        $html = $crawler->filter('.item_detail')->html();
        $this->assertContains($this->productMaker->getMaker()->getName(), $html);
        $this->assertNotContains('メーカーURL', $html);
    }

    /**
     * Product detail render test maker
     */
    public function testProductDetailWhenRegisterMakerAndMakerUrl()
    {
        $productId = $this->productMaker->getId();

        $crawler = $this->client->request('GET', $this->app->url('product_detail', array('id' => $productId)));

        $html = $crawler->filter('.item_detail')->html();
        $this->assertContains($this->productMaker->getMaker()->getName(), $html);
        $this->assertContains($this->productMaker->getMakerUrl(), $html);
    }

    /**
     * Create maker
     *
     * @param int $rank
     *
     * @return Maker
     */
    protected function createMaker($rank = null)
    {
        /**
         * @var Generator $faker
         */
        $faker = $this->getFaker();

        if (!$rank) {
            $rank = $faker->randomNumber(3);
        }

        $Maker = new Maker();
        $Maker->setName($faker->word);
        $Maker->setRank($rank);
        $Maker->setDelFlg(Constant::DISABLED);

        $this->app['orm.em']->persist($Maker);
        $this->app['orm.em']->flush($Maker);

        return $Maker;
    }

    /**
     * Create maker
     *
     * @param Maker   $Maker
     * @param Product $Product
     *
     * @return ProductMaker
     */
    protected function createProductMaker(Maker $Maker, $Product = null)
    {
        /**
         * @var Generator $faker
         */
        $faker = $this->getFaker();

        if (!$Product) {
            $Product = $this->createProduct();
        }

        $ProductMaker = new ProductMaker();
        $ProductMaker->setMaker($Maker);
        $ProductMaker->setMakerUrl($faker->url);
        $ProductMaker->setDelFlg(Constant::DISABLED);
        $ProductMaker->setId($Product->getId());

        $this->app['orm.em']->persist($ProductMaker);
        $this->app['orm.em']->flush($ProductMaker);

        return $ProductMaker;
    }
}
