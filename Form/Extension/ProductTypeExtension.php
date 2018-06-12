<?php

/*
 * This file is part of EC-CUBE
 *
 * Copyright(c) LOCKON CO.,LTD. All Rights Reserved.
 *
 * http://www.lockon.co.jp/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Plugin\Maker\Form\Extension;

use Eccube\Form\Type\Admin\ProductType;
use Plugin\Maker\Entity\Maker;
use Plugin\Maker\Repository\MakerRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Url;

class ProductTypeExtension extends AbstractTypeExtension
{
    /**
     * @var MakerRepository
     */
    protected $makerRepository;

    /**
     * ProductTypeExtension constructor.
     *
     * @param MakerRepository $makerRepository
     */
    public function __construct(MakerRepository $makerRepository)
    {
        $this->makerRepository = $makerRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('Maker', EntityType::class, [
                'class' => Maker::class,
                'choice_label' => 'name',
                'choices' => $this->makerRepository->findBy([], ['sort_no' => 'DESC']),
                'required' => false,
                'eccube_form_options' => [
                    'auto_render' => true,
                    'form_theme' => ''
                ]
            ])
            ->add('maker_url', TextType::class, [
                'required' => false,
                'constraints' => [
                    new Url(),
                ],
                'attr' => [
                    'placeholder' => 'admin.plugin.maker.placeholder.url',
                ],
                'eccube_form_options' => [
                    'auto_render' => true,
                    'form_theme' => '@Maker/admin/product_maker.twig'
                ]
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getExtendedType()
    {
        return ProductType::class;
    }
}
