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

namespace Plugin\Maker\Controller;

use Plugin\Maker\Form\Type\MakerType;
use Eccube\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception as HttpException;

class MakerController
{
    private $main_title;
    private $sub_title;

    public function __construct()
    {
    }

    public function index(Application $app, Request $request, $id)
    {
    	$repos = $app['eccube.plugin.maker.repository.maker'];

		$TargetMaker = new \Plugin\Maker\Entity\Maker();

        if ($id) {
            $TargetMaker = $repos->find($id);
            if (!$TargetMaker) {
                throw new NotFoundHttpException();
            }
        }

        $form = $app['form.factory']
            ->createBuilder('admin_maker', $TargetMaker)
            ->getForm();

        if ('POST' === $request->getMethod()) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $status = $repos->save($TargetMaker);

                if ($status) {
                    $app->addSuccess('admin.maker.save.complete', 'admin');
                    return $app->redirect($app->url('admin_maker'));
                } else {
                    $app->addError('admin.maker.save.error', 'admin');
                }
            }
        }
    	
        $Makers = $app['eccube.plugin.maker.repository.maker']->findAll();

        return $app->render('Maker/View/admin/maker.twig', array(
        	'form'   		=> $form->createView(),
            'Makers' 		=> $Makers,
            'TargetMaker' 	=> $TargetMaker,
        ));
    }

    public function delete(Application $app, Request $request, $id)
    {
    	$repos = $app['eccube.plugin.maker.repository.maker'];

        $TargetMaker = $repos->find($id);
        
        if (!$TargetMaker) {
            throw new NotFoundHttpException();
        }

        $form = $app['form.factory']
            ->createNamedBuilder('admin_maker', 'form', null, array(
                'allow_extra_fields' => true,
            ))
            ->getForm();

        $status = false;
        if ('POST' === $request->getMethod()) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $status = $repos->delete($TargetMaker);
            }
        }

        if ($status === true) {
            $app->addSuccess('admin.maker.delete.complete', 'admin');
        } else {
            $app->addError('admin.maker.delete.error', 'admin');
        }

        return $app->redirect($app->url('admin_maker'));
    }

    public function up(Application $app, Request $request, $id)
    {
    	$repos = $app['eccube.plugin.maker.repository.maker'];
    	
        $TargetMaker = $repos->find($id);
        if (!$TargetMaker) {
            throw new NotFoundHttpException();
        }

        $form = $app['form.factory']
            ->createNamedBuilder('admin_maker', 'form', null, array(
                'allow_extra_fields' => true,
            ))
            ->getForm();

        $status = false;
        if ('POST' === $request->getMethod()) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $status = $repos->up($TargetMaker);
            }
        }

        if ($status === true) {
            $app->addSuccess('admin.maker.down.complete', 'admin');
        } else {
            $app->addError('admin.maker.down.error', 'admin');
        }

        return $app->redirect($app->url('admin_maker'));
    }

    public function down(Application $app, Request $request, $id)
    {
    	$repos = $app['eccube.plugin.maker.repository.maker'];
    	
        $TargetMaker = $repos->find($id);
        if (!$TargetMaker) {
            throw new NotFoundHttpException();
        }

        $form = $app['form.factory']
            ->createNamedBuilder('admin_maker', 'form', null, array(
                'allow_extra_fields' => true,
            ))
            ->getForm();

        $status = false;
        if ('POST' === $request->getMethod()) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $status = $repos->down($TargetMaker);
            }
        }

        if ($status === true) {
            $app->addSuccess('admin.maker.down.complete', 'admin');
        } else {
            $app->addError('admin.maker.down.error', 'admin');
        }

        return $app->redirect($app->url('admin_maker'));
    }

}
