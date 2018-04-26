<?php
/*
 * This file is part of the Maker plugin
 *
 * Copyright (C) 2016 LOCKON CO.,LTD. All Rights Reserved.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Plugin\Maker\Event;

use Symfony\Component\Translation\TranslatorInterface;
use Plugin\Maker\Repository\MakerRepository;
use Plugin\Maker\Repository\ProductMakerRepository;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Class AbstractEvent.
 */
class CommonEvent
{
    /**
     * @var string target render on the front-end
     */
    protected $makerTag = '<!--# maker-plugin-tag #-->';

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var MakerRepository
     */
    protected $makerRepository;

    /**
     * @var ProductMakerRepository
     */
    protected $productMakerRepository;

    /**
     * @var \Twig_Environment
     */
    protected $twigEnvironment;

    /**
     * CommonEvent constructor.
     *
     * @param \Twig_Environment $twigEnvironment
     * @param TranslatorInterface $translator
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     * @param MakerRepository $makerRepository
     * @param ProductMakerRepository $productMakerRepository
     */
    public function __construct(
        \Twig_Environment $twigEnvironment,
        TranslatorInterface $translator,
        EntityManagerInterface $entityManager,
        MakerRepository $makerRepository,
        ProductMakerRepository $productMakerRepository
    ) {
        $this->twigEnvironment = $twigEnvironment;
        $this->translator = $translator;
        $this->entityManager = $entityManager;
        $this->makerRepository = $makerRepository;
        $this->productMakerRepository = $productMakerRepository;
    }

    /**
     * Render position
     *
     * @param string $html
     * @param string $part
     * @param string $markTag
     *
     * @return mixed
     */
    protected function renderPosition($html, $part, $markTag = '')
    {
        if (!$markTag) {
            $markTag = $this->makerTag;
        }
        // for plugin tag
        if (strpos($html, $markTag)) {
            $newHtml = $markTag.$part;
            $html = str_replace($markTag, $newHtml, $html);
        } else {
            // For old and new ec-cube version
            $search = '/(<div class="ec-productRole__category")/';
            $newHtml = $part.'<div class="ec-productRole__category")';
            $html = preg_replace($search, $newHtml, $html);
        }

        return $html;
    }
}
