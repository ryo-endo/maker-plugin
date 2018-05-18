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
use Eccube\Controller\AbstractController;
use Plugin\Maker\Entity\Maker;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Plugin\Maker\Repository\MakerRepository;
use Plugin\Maker\Form\Type\MakerType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Eccube\Common\Constant;

/**
 * Class MakerController.
 */
class MakerController extends AbstractController
{
    /**
     * @var MakerRepository
     */
    protected $makerRepository;

    /**
     * MakerController constructor.
     *
     * @param MakerRepository $makerRepository
     */
    public function __construct(
        MakerRepository $makerRepository
    ) {
        $this->makerRepository = $makerRepository;
    }

    /**
     * List, add, edit maker.
     *
     * @param Request     $request
     * @param null        $id
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     *
     * @Route("/%eccube_admin_route%/plugin/maker/{id}", name="admin_plugin_maker_index", requirements={"id":"\d+"}, defaults={"index":0})
     */
    public function index(Request $request, $id = null)
    {
        $TargetMaker = new Maker();

        if ($id) {
            $TargetMaker = $this->makerRepository->find($id);
            if (!$TargetMaker) {
                log_error('The Maker not found!', ['Maker id' => $id]);
                throw new NotFoundHttpException();
            }
        }

        $form = $this->formFactory
            ->createBuilder(MakerType::class, $TargetMaker)
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            log_info('Maker add/edit start.');
            $status = $this->makerRepository->save($TargetMaker);

            if ($status) {
                log_info('Maker add/edit success', ['Maker id' => $TargetMaker->getId()]);
                $this->addSuccess('admin.plugin.maker.save.complete', 'admin');

                return $this->redirectToRoute('admin_plugin_maker_index');
            } else {
                log_info('Maker add/edit fail!', ['Maker id' => $TargetMaker->getId()]);
                $this->addError('admin.plugin.maker.save.error', 'admin');
            }
        }

        /**
         * @var ArrayCollection $arrMaker
         */
        $arrMaker = $this->makerRepository->findBy(['del_flg' => Constant::DISABLED], ['rank' => 'DESC']);

        return $this->render('Maker/Resource/template/admin/maker.twig', [
            'form' => $form->createView(),
            'arrMaker' => $arrMaker,
            'TargetMaker' => $TargetMaker,
        ]);
    }

    /**
     * Delete Maker.
     *
     * @param Maker       $TargetMaker
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     *
     * @Method("POST")
     * @Route("/%eccube_admin_route%/plugin/maker/{id}/delete", name="admin_plugin_maker_delete", requirements={"id":"\d+"})
     */
    public function delete(Maker $TargetMaker)
    {
        // Valid token
        $this->isTokenValid();

        $status = $this->makerRepository->delete($TargetMaker);

        if ($status === true) {
            log_info('The maker delete success!', ['Maker id' => $TargetMaker->getId()]);
            $this->addSuccess('admin.plugin.maker.delete.complete', 'admin');
        } else {
            log_info('The maker delete fail!', ['Maker id' => $TargetMaker->getId()]);
            $this->addError('admin.plugin.maker.delete.error', 'admin');
        }

        return $this->redirectToRoute('admin_plugin_maker_index');
    }

    /**
     * Move rank with ajax.
     *
     * @param Request     $request
     *
     * @return bool
     *
     * @throws \Exception
     *
     * @Method("POST")
     * @Route("admin_plugin_maker_move_rank", name="admin_plugin_maker_move_rank")
     */
    public function moveRank(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            $arrRank = $request->request->all();
            $arrMoved = $this->makerRepository->moveMakerRank($arrRank);
            log_info('Maker move rank', $arrMoved);
        }

        return true;
    }
}
