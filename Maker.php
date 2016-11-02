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

use Doctrine\ORM\EntityRepository;
use Eccube\Entity\Product;
use Eccube\Event\EventArgs;
use Eccube\Common\Constant;
use Eccube\Event\TemplateEvent;
use Plugin\Maker\Entity\ProductMaker;
use Plugin\Maker\Repository\ProductMakerRepository;
use Plugin\Maker\Utils\Version;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class Maker.
 * New event on version EC-CUBE version >= 3.0.9 (new hook point).
 */
class Maker
{
    private $app;

    /**
     * @var MakerLegacy old event
     */
    private $legacyEvent;

    /**
     * @var string target render on the front-end
     */
    private $makerTag = '<!--# MakerPlugin-Tag #-->';

    /**
     * Maker constructor.
     *
     * @param \Eccube\Application $app
     */
    public function __construct($app)
    {
        $this->app = $app;
        $this->legacyEvent = new MakerLegacy($app);
    }

    /**
     * New event function on version >= 3.0.9 (new hook point)
     * Add/Edit product trigger.
     *
     * @param EventArgs $event
     */
    public function onAdminProductInit(EventArgs $event)
    {
        /**
         * @var FormBuilder $builder
         */
        $builder = $event->getArgument('builder');

        // Remove old extension
        $builder->remove('maker')
            ->remove('maker_url');

        // Add new extension
        $builder
            ->add('plg_maker', 'entity', array(
                'label' => 'メーカー',
                'class' => 'Plugin\Maker\Entity\Maker',
                'query_builder' => function (EntityRepository $repository) {
                    return $repository->createQueryBuilder('m')->orderBy('m.rank', 'DESC');
                },
                'property' => 'name',
                'required' => false,
                'empty_value' => '',
                'mapped' => false,
            ))
            ->add('plg_maker_url', 'text', array(
                'label' => 'URL',
                'required' => false,
                'constraints' => array(
                    new Assert\Url(),
                ),
                'mapped' => false,
                'attr' => array(
                    'placeholder' => $this->app->trans('admin.maker.placeholder.url'),
                ),
            ));

        /**
         * @var Product $Product
         */
        $Product = $event->getArgument('Product');
        $id = $Product->getId();

        /**
         * @var ProductMaker $ProductMaker
         */
        $ProductMaker = null;

        if ($id) {
            /**
             * @var ProductMakerRepository $repository
             */
            $repository = $this->app['eccube.plugin.maker.repository.product_maker'];
            $ProductMaker = $repository->find($id);
        }

        if (!$ProductMaker) {
            return;
        }

        $builder->get('plg_maker')->setData($ProductMaker->getMaker());
        $builder->get('plg_maker_url')->setData($ProductMaker->getMakerUrl());
    }

    /**
     * New event function on version >= 3.0.9 (new hook point)
     * Save event.
     *
     * @param EventArgs $eventArgs
     */
    public function onAdminProductComplete(EventArgs $eventArgs)
    {
        /**
         * @var Form $form
         */
        $form = $eventArgs->getArgument('form');

        /**
         * @var Product $Product
         */
        $Product = $eventArgs->getArgument('Product');

        /**
         * @var ProductMakerRepository $repository
         */
        $repository = $this->app['eccube.plugin.maker.repository.product_maker'];
        /**
         * @var ProductMaker $ProductMaker
         */
        $ProductMaker = $repository->find($Product);
        if (!$ProductMaker) {
            $ProductMaker = new ProductMaker();
        }

        $maker = $form->get('plg_maker')->getData();
        if (!$maker) {
            if ($ProductMaker->getId()) {
                $this->app['orm.em']->remove($ProductMaker);
                $this->app['orm.em']->flush($ProductMaker);
            }

            return;
        }

        $makerUrl = $form->get('plg_maker_url')->getData();

        $ProductMaker
            ->setId($Product->getId())
            ->setMakerUrl($makerUrl)
            ->setDelFlg(Constant::DISABLED)
            ->setMaker($maker);
        /**
         * @var EntityRepository $this->app['orm.em']
         */
        $this->app['orm.em']->persist($ProductMaker);
        $this->app['orm.em']->flush($ProductMaker);
    }

    /**
     * New event function on version >= 3.0.9 (new hook point)
     * Product detail render (front).
     *
     * @param TemplateEvent $event
     */
    public function onRenderProductsDetail(TemplateEvent $event)
    {
        $parameters = $event->getParameters();
        /**
         * @var Product $Product
         */
        $Product = $parameters['Product'];

        if (!$Product) {
            return;
        }

        /**
         * @var ProductMakerRepository $repository
         */
        $repository = $this->app['eccube.plugin.maker.repository.product_maker'];
        /**
         * @var ProductMaker $ProductMaker
         */
        $ProductMaker = $repository->find($Product);
        if (!$ProductMaker) {
            return;
        }

        $Maker = $ProductMaker->getMaker();

        if (!$Maker) {
            // 商品メーカーマスタにデータが存在しないまたは削除されていれば無視する
            return;
        }

        /**
         * @var \Twig_Environment $twig
         */
        $twig = $this->app['twig'];

        $twigAppend = $twig->getLoader()->getSource('Maker/Resource/template/default/maker.twig');

        /**
         * @var string $twigSource twig template.
         */
        $twigSource = $event->getSource();

        $twigSource = $this->legacyEvent->renderPosition($twigSource, $twigAppend, $this->makerTag);

        $event->setSource($twigSource);

        $parameters['maker_name'] = $ProductMaker->getMaker()->getName();
        $parameters['maker_url'] = $ProductMaker->getMakerUrl();
        $event->setParameters($parameters);
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

        $this->legacyEvent->onAdminProduct($event);
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

        $this->legacyEvent->onRenderProductsDetailBefore($event);
    }

    /**
     * v3.0.9以降のフックポイントに対応しているのか.
     *
     * @return bool
     */
    private function supportNewHookPoint()
    {
        return Version::isSupport();
    }
}
