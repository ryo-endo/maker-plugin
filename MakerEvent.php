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

use Eccube\Application;
use Eccube\Event\EventArgs;
use Eccube\Event\TemplateEvent;
use Plugin\Maker\Event\Maker;
use Plugin\Maker\Event\MakerLegacy;
use Plugin\Maker\Utils\Version;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

/**
 * Class MakerEvent.
 */
class MakerEvent
{
    /**
     * @var Application
     */
    private $app;

    /**
     * MakerEvent constructor.
     * @param Application $app
     */
    public function __construct($app)
    {
        $this->app = $app;
    }
    /**
     * New event function on version >= 3.0.9 (new hook point).
     * Add/Edit product render trigger.
     *
     * @param EventArgs $event
     */
    public function onAdminProductInit(EventArgs $event)
    {
        /* @var Maker $makerEvent */
        $makerEvent = $this->app['eccube.plugin.maker.event.maker'];
        $makerEvent->onAdminProductInit($event);
    }

    /**
     * New Event:function on version >= 3.0.9 (new hook point).
     * Save event.
     *
     * @param EventArgs $event
     */
    public function onAdminProductComplete(EventArgs $event)
    {
        /* @var Maker $makerEvent */
        $makerEvent = $this->app['eccube.plugin.maker.event.maker'];
        $makerEvent->onAdminProductComplete($event);
    }

    /**
     * New event function on version >= 3.0.9 (new hook point)
     * Product detail render (front).
     *
     * @param TemplateEvent $event
     */
    public function onRenderProductsDetail(TemplateEvent $event)
    {
        /* @var Maker $makerEvent */
        $makerEvent = $this->app['eccube.plugin.maker.event.maker'];
        $makerEvent->onRenderProductsDetail($event);
    }

    /**
     * Add product trigger.
     *
     * @param FilterResponseEvent $event
     *
     * @deprecated for since v3.0.0, to be removed in 3.1
     */
    public function onAdminProduct(FilterResponseEvent $event)
    {
        if ($this->supportNewHookPoint()) {
            return;
        }
        /* @var MakerLegacy $makerEvent */
        $makerEvent = $this->app['eccube.plugin.maker.event.maker_legancy'];
        $makerEvent->onAdminProduct($event);
    }

    /**
     * Product detail render (front).
     *
     * @param FilterResponseEvent $event
     *
     * @deprecated for since v3.0.0, to be removed in 3.1
     */
    public function onRenderProductsDetailBefore(FilterResponseEvent $event)
    {
        if ($this->supportNewHookPoint()) {
            return;
        }
        /* @var MakerLegacy $makerEvent */
        $makerEvent = $this->app['eccube.plugin.maker.event.maker_legancy'];
        $makerEvent->onRenderProductsDetailBefore($event);
    }

    /**
     * v3.0.9以降のフックポイントに対応しているのか.
     *
     * @return bool
     */
    private function supportNewHookPoint()
    {
        return Version::isSupportGetInstanceFunction();
    }
}
