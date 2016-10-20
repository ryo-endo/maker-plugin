<?php
/*
 * This file is part of the Maker plugin
 *
 * Copyright (C) 2016 LOCKON CO.,LTD. All Rights Reserved.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Plugin\Maker;

use Eccube\Common\Constant;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

/**
 * Class Maker
 * @package Plugin\Maker
 */
class Maker
{
    private $app;

    private $legacyEvent;

    /**
     * Maker constructor.
     * @param \Eccube\Application $app
     */
    public function __construct($app)
    {
        $this->app = $app;
        $this->legacyEvent = new MakerLegacy($app);
    }

    /**
     * New event function on version >= 3.0.9 (new hook point)
     * Add/Edit product trigger
     *
     * @param FilterResponseEvent $event
     */
    public function onAdminProductManage(FilterResponseEvent $event)
    {
        $this->legacyEvent->onAdminProduct($event);
    }

    /**
     * New event function on version >= 3.0.9 (new hook point)
     * Product detail render (front)
     *
     * @param FilterResponseEvent $event
     */
    public function onRenderProductsDetail(FilterResponseEvent $event)
    {
        $this->legacyEvent->onRenderProductsDetailBefore($event);
    }

    /**
     * Add product trigger
     *
     * @param FilterResponseEvent $event
     * @deprecated for since v3.0.0, to be removed in 3.1.
     */
    public function onAdminProduct(FilterResponseEvent $event)
    {
        if ($this->supportNewHookPoint()) {
            return;
        }

        $this->legacyEvent->onAdminProduct($event);
    }

    /**
     * Product detail render (front)
     *
     * @param FilterResponseEvent $event
     * @deprecated for since v3.0.0, to be removed in 3.1.
     */
    public function onRenderProductsDetailBefore(FilterResponseEvent $event)
    {
        if ($this->supportNewHookPoint()) {
            return;
        }

        $this->legacyEvent->onRenderProductsDetailBefore($event);
    }

    /**
     * v3.0.9以降のフックポイントに対応しているのか
     *
     * @return bool
     */
    private function supportNewHookPoint()
    {
        return version_compare('3.0.9', Constant::VERSION, '<=');
    }
}
