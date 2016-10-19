<?php
/*
 * This file is part of the Maker plugin
 *
 * Copyright (C) 2016 LOCKON CO.,LTD. All Rights Reserved.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Plugin\Maker\Controller;

use Doctrine\Common\Collections\ArrayCollection;
use Eccube\Application;
use Plugin\Maker\Entity\Maker;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class MakerController
 * @package Plugin\Maker\Controller
 */
class MakerController
{
    /**
     * List, add, edit maker
     *
     * @param Application $app
     * @param Request     $request
     * @param null        $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function index(Application $app, Request $request, $id = null)
    {
        $repos = $app['eccube.plugin.maker.repository.maker'];

        $TargetMaker = new Maker();

        if ($id) {
            $TargetMaker = $repos->find($id);
            if (!$TargetMaker) {
                throw new NotFoundHttpException();
            }
        }

        $form = $app['form.factory']
            ->createBuilder('admin_maker', $TargetMaker)
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $status = $repos->save($TargetMaker);

            if ($status) {
                $app->addSuccess('admin.maker.save.complete', 'admin');
                return $app->redirect($app->url('admin_maker'));
            } else {
                $app->addError('admin.maker.save.error', 'admin');
            }
        }

        /**
         * @var ArrayCollection
         */
        $arrMaker = $app['eccube.plugin.maker.repository.maker']->findBy(array(), array('rank' => 'DESC'));

        return $app->render('Maker/Resource/template/admin/maker.twig', array(
        	'form'   		=> $form->createView(),
            'arrMaker' 		=> $arrMaker,
            'TargetMaker' 	=> $TargetMaker,
        ));
    }

    /**
     * Delete Maker
     *
     * @param Application $app
     * @param Request     $request
     * @param integer     $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
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
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $status = $repos->delete($TargetMaker);
        }

        if ($status === true) {
            $app->addSuccess('admin.maker.delete.complete', 'admin');
        } else {
            $app->addError('admin.maker.delete.error', 'admin');
        }

        return $app->redirect($app->url('admin_maker'));
    }

    /**
     * Up rank
     *
     * @param Application $app
     * @param Request     $request
     * @param integer     $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
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
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $status = $repos->up($TargetMaker);
        }

        if ($status === true) {
            $app->addSuccess('admin.maker.down.complete', 'admin');
        } else {
            $app->addError('admin.maker.down.error', 'admin');
        }

        return $app->redirect($app->url('admin_maker'));
    }

    /**
     * Down rank
     *
     * @param Application $app
     * @param Request     $request
     * @param integer     $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
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
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $status = $repos->down($TargetMaker);
        }

        if ($status === true) {
            $app->addSuccess('admin.maker.down.complete', 'admin');
        } else {
            $app->addError('admin.maker.down.error', 'admin');
        }

        return $app->redirect($app->url('admin_maker'));
    }
}
