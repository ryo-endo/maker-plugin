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

use Eccube\Event\EventArgs;
use Eccube\Event\TemplateEvent;
use Plugin\Maker\Event\Maker;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class MakerEvent.
 */
class MakerEvent implements EventSubscriberInterface
{
    /**
     * @var Maker
     */
    protected $makerEvent;

    /**
     * MakerEvent constructor.
     *
     * @param Maker $makerEvent
     */
    public function __construct(
        Maker $makerEvent
    ) {
        $this->makerEvent = $makerEvent;
    }
    /**
     * New event function on version >= 3.0.9 (new hook point).
     * Add/Edit product render trigger.
     *
     * @param EventArgs $event
     */
    public function onAdminProductEditInitialize(EventArgs $event)
    {
        $this->makerEvent->onAdminProductEditInitialize($event);
    }

    /**
     * New Event:function on version >= 3.0.9 (new hook point).
     * Save event.
     *
     * @param EventArgs $event
     */
    public function onAdminProductEditComplete(EventArgs $event)
    {
        $this->makerEvent->onAdminProductEditComplete($event);
    }

    /**
     * New event function on version >= 3.0.9 (new hook point)
     * Product detail render (front).
     *
     * @param TemplateEvent $event
     */
    public function onRenderProductDetail(TemplateEvent $event)
    {
        $this->makerEvent->onRenderProductDetail($event);
    }

    /**
     * {@inheritdoc}
     *
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            'admin.product.edit.initialize' => [['onAdminProductEditInitialize', 10]],
            'admin.product.edit.complete' => [['onAdminProductEditComplete', 10]],
            'Product/detail.twig' => [['onRenderProductDetail', 10]],
        ];
    }
}
