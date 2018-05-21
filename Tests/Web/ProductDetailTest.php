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

use Eccube\Entity\Product;
use Faker\Generator;
use Plugin\Maker\Entity\Maker;
use Plugin\Maker\Entity\ProductMaker;
use Symfony\Component\HttpKernel\Client;
use Eccube\Repository\ProductRepository;

/**
 * Class ProductDetailTest
 * Hook point test
 */
class ProductDetailTest extends MakerWebCommon
{
    /**
     * @var Maker $Maker
     */
    protected $Maker;

    /**
     * @var ProductMaker $ProductMaker
     */
    protected $ProductMaker;

    /**
     * @var ProductRepository
     */
    protected $productRepository;

    /**
     * Set up function.
     */
    public function setUp()
    {
        parent::setUp();
        $this->deleteAllRows(['plg_product_maker', 'plg_maker']);

        $this->Maker = $this->createMaker();
        $this->ProductMaker = $this->createProductMaker($this->Maker);
        $this->productRepository = $this->container->get(ProductRepository::class);

    }

    /**
     * Product detail render test maker
     */
    public function testProductDetailWhenHasMakerButUnRegister()
    {
        $productId = $this->ProductMaker->getId();
        $this->entityManager->remove($this->ProductMaker);
        $this->entityManager->flush();
        $crawler = $this->client->request('GET', $this->generateUrl('product_detail', ['id' => $productId]));
        $html = $crawler->filter('.ec-productRole__profile')->html();
        $this->assertNotContains('メーカーコード', $html);
        $this->assertNotContains('メーカーURL', $html);
    }

    /**
     * Product detail render test maker
     */
    public function testProductDetailWhenRegisterMakerWithoutMakerUrl()
    {
        $productId = $this->ProductMaker->getId();
        $this->ProductMaker->setMakerUrl('');
        $this->entityManager->persist($this->ProductMaker);
        $this->entityManager->flush();

        $crawler = $this->client->request('GET', $this->generateUrl('product_detail', ['id' => $productId]));

        $html = $crawler->filter('.ec-productRole__profile')->html();
        $this->assertContains($this->ProductMaker->getMaker()->getName(), $html);
        $this->assertNotContains('メーカーURL', $html);
    }

    /**
     * Product detail render test maker
     */
    public function testProductDetailWhenRegisterMakerAndMakerUrl()
    {
        $productId = $this->ProductMaker->getId();

        $crawler = $this->client->request('GET', $this->generateUrl('product_detail', ['id' => $productId]));

        $html = $crawler->filter('.ec-productRole__profile')->html();
        $this->assertContains($this->ProductMaker->getMaker()->getName(), $html);
        $this->assertContains($this->ProductMaker->getMakerUrl(), $html);
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
            // New product
            /**
             * @var Generator $faker
             */
            $faker = $this->getFaker();
            $formData = $this->createFormData();
            $formData['plg_maker'] = '';
            $formData['plg_maker_url'] = '';

            /**
             * @var Client $client
             */
            $client = $this->client;
            $client->request(
                'POST',
                $this->generateUrl('admin_product_product_new'),
                ['admin_product' => $formData]
            );

            $this->assertTrue($client->getResponse()->isRedirection());

            $arrTmp = explode('/', $client->getResponse()->getTargetUrl());
            $productId = $arrTmp[count($arrTmp)-2];

            $client->followRedirect();

            $this->productRepository = $this->container->get(ProductRepository::class);
            $Product = $this->productRepository->find($productId);
        }

        $ProductMaker = new ProductMaker();
        $ProductMaker->setMakerUrl($faker->url);
        $ProductMaker->setId($Product->getId());
        $ProductMaker->setMaker($Maker);
        $this->entityManager->persist($ProductMaker);
        $this->entityManager->flush();

        return $ProductMaker;
    }
}
