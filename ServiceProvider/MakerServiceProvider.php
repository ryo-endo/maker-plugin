<?php
/*
* This file is part of EC-CUBE
*
* Copyright(c) 2000-2015 LOCKON CO.,LTD. All Rights Reserved.
* http://www.lockon.co.jp/
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Plugin\Maker\ServiceProvider;

use Silex\Application as BaseApplication;
use Silex\ServiceProviderInterface;

class MakerServiceProvider implements ServiceProviderInterface
{
    public function register(BaseApplication $app)
    {

        // 不要？
        $app['eccube.plugin.maker.repository.maker_plugin'] = $app->share(function () use ($app) {
            return $app['orm.em']->getRepository('Plugin\Maker\Entity\MakerPlugin');
        });

        // メーカーテーブル用リポジトリ
        $app['eccube.plugin.maker.repository.maker'] = $app->share(function () use ($app) {
            return $app['orm.em']->getRepository('Plugin\Maker\Entity\Maker');
        });

        $app['eccube.plugin.maker.repository.product_maker'] = $app->share(function () use ($app) {
            return $app['orm.em']->getRepository('Plugin\Maker\Entity\ProductMaker');
        });

        // 一覧・登録・修正
        $app->match('/' . $app["config"]["admin_route"] . '/product/maker/{id}', '\\Plugin\\Maker\\Controller\\MakerController::index')
            ->value('id', null)->assert('id', '\d+|')
            ->bind('admin_maker');

        // 削除
        $app->match('/' . $app["config"]["admin_route"] . '/product/maker/{id}/delete', '\\Plugin\\Maker\\Controller\\MakerController::delete')
            ->value('id', null)->assert('id', '\d+|')
            ->bind('admin_maker_delete');

        // 上
        $app->match('/' . $app["config"]["admin_route"] . '/product/maker/{id}/up', '\\Plugin\\Maker\\Controller\\MakerController::up')
            ->value('id', null)->assert('id', '\d+|')
            ->bind('admin_maker_up');

        // 下
        $app->match('/' . $app["config"]["admin_route"] . '/product/maker/{id}/down', '\\Plugin\\Maker\\Controller\\MakerController::down')
            ->value('id', null)->assert('id', '\d+|')
            ->bind('admin_maker_down');

        // 型登録
        $app['form.types'] = $app->share($app->extend('form.types', function ($types) use ($app) {
            $types[] = new \Plugin\Maker\Form\Type\MakerType($app);
            return $types;
        }));

        // Form Extension
        $app['form.type.extensions'] = $app->share($app->extend('form.type.extensions', function ($extensions) use ($app) {
            $extensions[] = new \Plugin\Maker\Form\Extension\Admin\ProductMakerTypeExtension($app);
            return $extensions;
        }));

        // メッセージ登録
        $app['translator'] = $app->share($app->extend('translator', function ($translator, \Silex\Application $app) {
            $translator->addLoader('yaml', new \Symfony\Component\Translation\Loader\YamlFileLoader());

            $file = __DIR__ . '/../Resource/locale/message.' . $app['locale'] . '.yml';
            if (file_exists($file)) {
                $translator->addResource('yaml', $file, $app['locale']);
            }

            return $translator;
        }));

        // メニュー登録
        $app['config'] = $app->share($app->extend('config', function ($config) {
            $addNavi['id'] = "maker";
            $addNavi['name'] = "メーカー管理";
            $addNavi['url'] = "admin_maker";

            $nav = $config['nav'];
            foreach ($nav as $key => $val) {
                if ("product" == $val["id"]) {
                    $nav[$key]['child'][] = $addNavi;
                }
            }

            $config['nav'] = $nav;
            return $config;
        }));
    }

    public function boot(BaseApplication $app)
    {
    }
}
