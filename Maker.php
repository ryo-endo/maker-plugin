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

namespace Plugin\Maker;

use Eccube\Common\Constant;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

class Maker
{
    private $app;

    public function __construct($app)
    {
        $this->app = $app;
    }

    public function onRenderAdminProductNewBefore(FilterResponseEvent $event)
    {
        $app = $this->app;
        if (!$this->app->isGranted('ROLE_ADMIN')) {
            return;
        }

        $request = $event->getRequest();
        $response = $event->getResponse();
        $id = $request->attributes->get('id');

        list($html, $form) = $this->getHtml($request, $response, $id);
        $response->setContent($html);

        if ('POST' === $request->getMethod()) {
            // RedirectResponseかどうかで判定する.

            if (!$response instanceof RedirectResponse) {
                return;
            }

            if (empty($id)) {
                $location = explode('/', $response->headers->get('location'));
                $url = explode('/', $this->app->url('admin_product_product_edit', array('id' => '0')));
                $diffs = array_values(array_diff($location, $url));
                $id = $diffs[0];
            }

            if ($form->isValid()) {
                // 登録
                $data = $form->getData();

                $Makers = $this->app['eccube.plugin.maker.repository.maker']->findAll();

                $Maker = $form->get('maker')->getData();
                $makerUrl = $form->get('maker_url')->getData();

                if (count($Makers) > 0 && !empty($Maker)) {

                    $ProductMaker = new \Plugin\Maker\Entity\ProductMaker();

                    $ProductMaker
                        ->setId($id)
                        ->setMakerUrl($makerUrl)
                        ->setDelFlg(Constant::DISABLED)
                        ->setMaker($Maker);

                    $app['orm.em']->persist($ProductMaker);

                    $app['orm.em']->flush($ProductMaker);
                }
            }
        }

        $event->setResponse($response);
    }

    public function onRenderAdminProductEditBefore(FilterResponseEvent $event)
    {
        $app = $this->app;
        if (!$app->isGranted('ROLE_ADMIN')) {
            return;
        }

        $request = $event->getRequest();
        $response = $event->getResponse();
        $id = $request->attributes->get('id');

        list($html, $form) = $this->getHtml($request, $response, $id);
        $response->setContent($html);

        $event->setResponse($response);
    }


    public function onAdminProductEditAfter()
    {
        $app = $this->app;
        if (!$app->isGranted('ROLE_ADMIN')) {
            return;
        }

        $id = $app['request']->attributes->get('id');

        $form = $app['form.factory']
            ->createBuilder('admin_product')
            ->getForm();

        $ProductMaker = $app['eccube.plugin.maker.repository.product_maker']->find($id);

        if (is_null($ProductMaker)) {
            $ProductMaker = new \Plugin\Maker\Entity\ProductMaker();
        }

        $form->get('maker')->setData($ProductMaker->getMaker());

        $form->handleRequest($app['request']);

        if ('POST' === $app['request']->getMethod()) {

            if ($form->isValid()) {

                $maker_id = $form->get('maker')->getData();
                if ($maker_id) {
                // 登録・更新
                    $Maker = $app['eccube.plugin.maker.repository.maker']->find($maker_id);
                // ※setIdはなんだか違う気がする
                    if ($id) {
                        $ProductMaker->setId($id);
                    }

                    $ProductMaker
                        ->setMakerUrl($form->get('maker_url')->getData())
                        ->setDelFlg(0)
                        ->setMaker($Maker);
                        $app['orm.em']->persist($ProductMaker);
                } else {
                // 削除
                // ※setIdはなんだか違う気がする
                    $ProductMaker->setId($id);
                    $app['orm.em']->remove($ProductMaker);
                }

                $app['orm.em']->flush();
            }
        }
    }

    private function getHtml($request, $response, $id)
    {

        // メーカーマスタから有効なメーカー情報を取得
        $Makers = $this->app['eccube.plugin.maker.repository.maker']->findAll();

        if (is_null($Makers)) {
            $Makers = new \Plugin\Maker\Entity\Maker();
        }

        $ProductMaker = null;

        if ($id) {
            // 商品メーカーマスタから設定されているなメーカー情報を取得
            $ProductMaker = $this->app['eccube.plugin.maker.repository.product_maker']->find($id);
        }

        // 商品登録・編集画面のHTMLを取得し、DOM化
        $crawler = new Crawler($response->getContent());

        $form = $this->app['form.factory']
            ->createBuilder('admin_product')
            ->getForm();

        if ($ProductMaker) {
            // 既に登録されている商品メーカー情報が設定されている場合、初期選択
            $form->get('maker')->setData($ProductMaker->getMaker());
            $form->get('maker_url')->setData($ProductMaker->getMakerUrl());
        }

        $form->handleRequest($request);

        $parts = $this->app->renderView(
            'Maker/View/admin/product_maker.twig',
            array('form' => $form->createView())
        );

        // form1の最終項目に追加(レイアウトに依存
        $html = $this->getHtmlFromCrawler($crawler);

        try {
            $oldHtml = $crawler->filter('#form1 .accordion')->last()->html();
            $newHtml = $oldHtml.$parts;
            $html = str_replace($oldHtml, $newHtml, $html);
        } catch (\InvalidArgumentException $e) {
            // no-op
        }

        return array($html, $form);

    }


    public function onRenderProductsDetailBefore(FilterResponseEvent $event)
    {
        $app = $this->app;
        $request = $event->getRequest();
        $response = $event->getResponse();
        $id = $request->attributes->get('id');

        $ProductMaker = null;

        if ($id) {
            // 商品メーカーマスタから設定されているなメーカー情報を取得
            $ProductMaker = $this->app['eccube.plugin.maker.repository.product_maker']->find($id);
        }
        if (!$ProductMaker) {
            return;
        }

        $Maker = $ProductMaker->getMaker();

        if (is_null($Maker)) {
            // 商品メーカーマスタにデータが存在しないまたは削除されていれば無視する
            return;
        }

        // HTMLを取得し、DOM化
        $crawler = new Crawler($response->getContent());
        $html = $this->getHtmlFromCrawler($crawler);

        if ($ProductMaker) {
            $parts = $this->app->renderView(
                'Maker/View/default/maker.twig',
                array(
                    'maker_name' => $ProductMaker->getMaker()->getName(),
                    'maker_url' => $ProductMaker->getMakerUrl(),
                )
            );

            try {
                // ※商品コードの下に追加
                $parts_item_code = $crawler->filter('.item_code')->html();
                $new_html = $parts_item_code.$parts;
                $html = str_replace($parts_item_code, $new_html, $html);
            } catch (\InvalidArgumentException $e) {
                // no-op
            }
        }

        $response->setContent($html);
        $event->setResponse($response);
    }

    /**
     * 解析用HTMLを取得.
     *
     * @param Crawler $crawler
     *
     * @return string
     */
    private function getHtmlFromCrawler(Crawler $crawler)
    {
        $html = '';
        foreach ($crawler as $domElement) {
            $domElement->ownerDocument->formatOutput = true;
            $html .= $domElement->ownerDocument->saveHTML();
        }

        return html_entity_decode($html, ENT_NOQUOTES, 'UTF-8');
    }
}
