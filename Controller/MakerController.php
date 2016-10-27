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
use Eccube\Controller\AbstractController;
use Plugin\Maker\Entity\Maker;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class MakerController.
 */
class MakerController extends AbstractController
{
    /**
     * List, add, edit maker.
     *
     * @param Application $app
     * @param Request     $request
     * @param null        $id
     *
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
         * @var ArrayCollection $arrMaker
         */
        $arrMaker = $app['eccube.plugin.maker.repository.maker']->findBy(array(), array('rank' => 'DESC'));

        return $app->render('Maker/Resource/template/admin/maker.twig', array(
            'form' => $form->createView(),
            'arrMaker' => $arrMaker,
            'TargetMaker' => $TargetMaker,
        ));
    }

    /**
     * Delete Maker.
     *
     * @param Application $app
     * @param Request     $request
     * @param int         $id
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function delete(Application $app, Request $request, $id = null)
    {
        // Valid token
        $this->isTokenValid($app);

        // Check request
        if (!'POST' === $request->getMethod()) {
            throw new BadRequestHttpException();
        }

        // Id valid
        if (!$id) {
            $app->addError('admin.maker.not_found', 'admin');

            return $app->redirect($app->url('admin_maker'));
        }

        $repos = $app['eccube.plugin.maker.repository.maker'];

        $TargetMaker = $repos->find($id);

        if (!$TargetMaker) {
            throw new NotFoundHttpException();
        }

        $status = $repos->delete($TargetMaker);

        if ($status === true) {
            $app->addSuccess('admin.maker.delete.complete', 'admin');
        } else {
            $app->addError('admin.maker.delete.error', 'admin');
        }

        return $app->redirect($app->url('admin_maker'));
    }

    /**
     * Move rank with ajax.
     *
     * @param Application $app
     * @param Request     $request
     *
     * @return bool
     */
    public function moveRank(Application $app, Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            $arrRank = $request->request->all();
            $app['eccube.plugin.maker.repository.maker']->moveMakerRank($arrRank);
        }

        return true;
    }
}
