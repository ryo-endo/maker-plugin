<?php
/*
 * This file is part of the Maker plugin
 *
 * Copyright (C) 2016 LOCKON CO.,LTD. All Rights Reserved.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Plugin\Maker\ServiceProvider;

use Plugin\Maker\Form\Extension\Admin\ProductMakerTypeExtension;
use Plugin\Maker\Form\Type\MakerType;
use Silex\Application as BaseApplication;
use Silex\ServiceProviderInterface;

/**
 * Class MakerServiceProvider.
 */
class MakerServiceProvider implements ServiceProviderInterface
{
    /**
     * @param BaseApplication $app
     */
    public function register(BaseApplication $app)
    {
        // メーカーテーブル用リポジトリ
        $app['eccube.plugin.maker.repository.maker'] = $app->share(function () use ($app) {
            return $app['orm.em']->getRepository('Plugin\Maker\Entity\Maker');
        });

        $app['eccube.plugin.maker.repository.product_maker'] = $app->share(function () use ($app) {
            return $app['orm.em']->getRepository('Plugin\Maker\Entity\ProductMaker');
        });

        // 一覧・登録・修正
        $app->match('/'.$app['config']['admin_route'].'/product/maker/{id}', '\\Plugin\\Maker\\Controller\\MakerController::index')
            ->value('id', null)->assert('id', '\d+|')
            ->bind('admin_maker');

        // 削除
        $app->delete('/'.$app['config']['admin_route'].'/product/maker/{id}/delete', '\\Plugin\\Maker\\Controller\\MakerController::delete')
            ->value('id', null)->assert('id', '\d+|')
            ->bind('admin_maker_delete');

        $app->post('/'.$app['config']['admin_route'].'/product/maker/rank/move', '\\Plugin\\Maker\\Controller\\MakerController::moveRank')
            ->bind('admin_product_maker_rank_move');

        // 型登録
        $app['form.types'] = $app->share($app->extend('form.types', function ($types) use ($app) {
            $types[] = new MakerType($app);

            return $types;
        }));

        // Form Extension
        $app['form.type.extensions'] = $app->share($app->extend('form.type.extensions', function ($extensions) use ($app) {
            $extensions[] = new ProductMakerTypeExtension($app);

            return $extensions;
        }));

        // メッセージ登録
        $app['translator'] = $app->share($app->extend('translator', function ($translator, \Silex\Application $app) {
            $file = __DIR__.'/../Resource/locale/message.'.$app['locale'].'.yml';
            if (file_exists($file)) {
                $translator->addResource('yaml', $file, $app['locale']);
            }

            return $translator;
        }));

        // メニュー登録
        $app['config'] = $app->share($app->extend('config', function ($config) {
            $addNavi['id'] = 'maker';
            $addNavi['name'] = 'メーカー管理';
            $addNavi['url'] = 'admin_maker';

            $nav = $config['nav'];
            foreach ($nav as $key => $val) {
                if ('product' == $val['id']) {
                    $nav[$key]['child'][] = $addNavi;
                }
            }

            $config['nav'] = $nav;

            return $config;
        }));
    }

    /**
     * @param BaseApplication $app
     */
    public function boot(BaseApplication $app)
    {
    }
}
