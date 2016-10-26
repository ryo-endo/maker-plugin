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
}
