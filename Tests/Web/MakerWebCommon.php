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
use Eccube\Tests\Web\Admin\AbstractAdminWebTestCase;
use Faker\Generator;
use Plugin\Maker\Entity\Maker;

/**
 * Class MakerWebTestCase
 * @package Plugin\Maker\Tests\Web
 */
class MakerWebCommon extends AbstractAdminWebTestCase
{
    /**
     * Create product form to submit.
     *
     * @return array
     */
    protected function createFormData()
    {
        $faker = $this->getFaker();

        $price01 = $faker->randomNumber(5);
        if (mt_rand(0, 1)) {
            $price01 = number_format($price01);
        }

        $price02 = $faker->randomNumber(5);
        if (mt_rand(0, 1)) {
            $price02 = number_format($price02);
        }

        $form = [
            'class' => [
                'sale_type' => 1,
                'price01' => $price01,
                'price02' => $price02,
                'stock' => $faker->randomNumber(3),
                'stock_unlimited' => 0,
                'code' => $faker->word,
                'sale_limit' => null,
                'delivery_duration' => ''
            ],
            'name' => $faker->word,
            'product_image' => [],
            'description_detail' => $faker->realText,
            'description_list' => $faker->paragraph,
            'Category' => 1,
            'Tag' => 1,
            'search_word' => $faker->word,
            'free_area' => $faker->realText,
            'Status' => 1,
            'note' => $faker->realText,
            'tags' => null,
            'images' => null,
            'add_images' => null,
            'delete_images' => null,
            Constant::TOKEN_NAME => 'dummy'
        ];
        return $form;
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

        $this->entityManager->persist($Maker);
        $this->entityManager->flush();

        return $Maker;
    }
}
