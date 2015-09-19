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

use Eccube\Event\RenderEvent;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\CssSelector\CssSelector;
use Symfony\Component\DomCrawler\Crawler;

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
        $request = $event->getRequest();
        $response = $event->getResponse();
        $id = $request->attributes->get('id');

        $response->setContent($this->getHtml($request, $response, $id));
        $event->setResponse($response);
    }

    public function onRenderAdminProductEditBefore(FilterResponseEvent $event)
    {
        $app = $this->app;
        $request = $event->getRequest();
        $response = $event->getResponse();
        $id = $request->attributes->get('id');

        $response->setContent($this->getHtml($request, $response, $id));
        $event->setResponse($response);
    }

	private function getHtml($request, $response, $id) {

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
            ->createBuilder('admin_product_maker')
            ->getForm();

        $form->get('maker')->setData($Makers);
        
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
        
        // form1の最終項目に追加(レイアウトに依存（時間無いのでベタ）)
        $html  = $this->getHtmlFromCrawler($crawler);
        
        try {
        	// ※商品編集画面 idなりclassなりがきちんとつかないとDOMをいじるのは難しい
	        $form  = $crawler->filter('#form1 .accordion')->last()->html();
	        $new_form = $form . $parts;
	        $html = str_replace($form, $new_form, $html);
	    } catch (\InvalidArgumentException $e) {
	    	// no-op
	    }
	    
	    return $html;
	}

    public function onAdminProductEditAfter()
    {
        $app = $this->app;
        $id = $app['request']->attributes->get('id');

        $form = $app['form.factory']
            ->createBuilder('admin_product_maker')
            ->getForm();

        $ProductMaker = $app['eccube.plugin.maker.repository.product_maker']->find($id);

        if (is_null($ProductMaker)) {
            $ProductMaker = new \Plugin\Maker\Entity\ProductMaker();
        }

        $form->get('maker')->setData($ProductMaker->getMaker());

        $form->handleRequest($app['request']);

        if ('POST' === $app['request']->getMethod()) {
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

		// HTMLを取得し、DOM化
        $crawler = new Crawler($response->getContent());
        $html  = $this->getHtmlFromCrawler($crawler);
        
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
		        $parts_item_code  = $crawler->filter('.item_code')->html();
		        $new_html = $parts_item_code . $parts;
		        $html = str_replace($parts_item_code, $new_html, $html);
		    } catch (\InvalidArgumentException $e) {
		    	// no-op
		    }
		}

        $response->setContent($html);
        $event->setResponse($response);
    }

	/**
	 * 解析用HTMLを取得
	 *
	 * @param Crawler $crawler
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
