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

use Eccube\Common\EccubeConfig;
use Eccube\Form\Type\Admin\ProductType;
use Plugin\Maker\Entity\Maker;
use Plugin\Maker\Repository\MakerRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

class ProductTypeExtension extends AbstractTypeExtension
{
    /**
     * @var EccubeConfig
     */
    private $eccubeConfig;

    /**
     * @var MakerRepository
     */
    protected $makerRepository;

    /**
     * ProductTypeExtension constructor.
     *
     * @param EccubeConfig $eccubeConfig
     * @param MakerRepository $makerRepository
     */
    public function __construct(EccubeConfig $eccubeConfig, MakerRepository $makerRepository)
    {
        $this->eccubeConfig = $eccubeConfig;
        $this->makerRepository = $makerRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('Maker', EntityType::class, [
                'label' => 'maker.admin.product_maker.maker',
                'class' => Maker::class,
                'choice_label' => 'name',
                'choices' => $this->makerRepository->findBy([], ['sort_no' => 'DESC']),
                'required' => false,
                'eccube_form_options' => [
                    'auto_render' => true,
                ],
            ])
            ->add('maker_url', TextType::class, [
                'label' => 'maker.admin.product_maker.maker_url',
                'required' => false,
                'attr' => [
                    'maxlength' => $this->eccubeConfig['eccube_url_len'],
                    'placeholder' => 'maker.admin.placeholder.url',
                ],
                'eccube_form_options' => [
                    'auto_render' => true,
                ],
                'constraints' => [
                    new Assert\Url(),
                    new Assert\Length(['max' => $this->eccubeConfig['eccube_url_len']]),
                ],
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
