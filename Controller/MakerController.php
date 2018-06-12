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
use Plugin\Maker\Form\Type\MakerType;
use Plugin\Maker\Repository\MakerRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
    public function __construct(MakerRepository $makerRepository)
    {
        $this->makerRepository = $makerRepository;
    }

    /**
     * List, add, edit maker.
     *
     * @param Request $request
     * @param null $id
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response|array
     *
     * @Route("/%eccube_admin_route%/plugin/maker/{id}", name="admin_plugin_maker_index", requirements={"id":"\d+"})
     * @Template("@Maker/admin/maker.twig")
     */
    public function index(Request $request, $id = null)
    {
        $Maker = new Maker();

        if ($id) {
            $Maker = $this->makerRepository->find($id);
            if (!$Maker) {
                log_error('The Maker not found!', ['Maker id' => $id]);
                throw new NotFoundHttpException();
            }
        }

        $form = $this->formFactory
            ->createBuilder(MakerType::class, $Maker)
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            log_info('Maker add/edit start.');
            $status = $this->makerRepository->save($Maker);

            if ($status) {
                log_info('Maker add/edit success', ['Maker id' => $Maker->getId()]);
                $this->addSuccess('admin.plugin.maker.save.complete', 'admin');

                return $this->redirectToRoute('admin_plugin_maker_index');
            } else {
                log_info('Maker add/edit fail!', ['Maker id' => $Maker->getId()]);
                $this->addError('admin.plugin.maker.save.error', 'admin');
            }
        }

        /**
         * @var ArrayCollection $Makers
         */
        $Makers = $this->makerRepository->findBy([], ['sort_no' => 'DESC']);

        return [
            'form' => $form->createView(),
            'Makers' => $Makers,
            'Maker' => $Maker,
        ];
    }

    /**
     * Delete Maker.
     *
     * @param Maker $TargetMaker
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     *
     * @Method("DELETE")
     * @Route("/%eccube_admin_route%/plugin/maker/{id}/delete", name="admin_plugin_maker_delete", requirements={"id":"\d+"})
     */
    public function delete(Maker $Maker)
    {
        // Valid token
        $this->isTokenValid();

        try {
            $this->makerRepository->delete($Maker);
            log_info('The maker delete success!', ['Maker id' => $Maker->getId()]);
            $this->addSuccess('admin.plugin.maker.delete.complete', 'admin');
        } catch (\Exception $e) {
            log_info('The maker delete fail!', ['Maker id' => $Maker->getId()]);
            $this->addError('admin.plugin.maker.delete.error', 'admin');
        }

        return $this->redirectToRoute('admin_plugin_maker_index');
    }

    /**
     * Move sort no with ajax.
     *
     * @param Request $request
     *
     * @return Response
     *
     * @throws \Exception
     *
     * @Method("POST")
     * @Route("/%eccube_admin_route%/plugin/maker/move_sort_no", name="admin_plugin_maker_move_sort_no")
     */
    public function moveSortNo(Request $request)
    {
        if ($request->isXmlHttpRequest() && $this->isTokenValid()) {
            $sortNos = $request->request->all();
            $movedSortNos = $this->makerRepository->moveSortNo($sortNos);
            log_info('Maker move sort no', $movedSortNos);
        }

        return new Response('Successfully');
    }
}
