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

namespace Plugin\Maker\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

class ProductMakerType extends AbstractType
{
    private $app;

    public function __construct(\Eccube\Application $app)
    {
        $this->app = $app;
    }
	
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('maker', 'entity', array(
            	'label' => 'メーカー',
                'class' => 'Plugin\Maker\Entity\Maker',
                'property' => 'name',
	            'required' => false,
                'empty_value' => "",
            ))
            ->add('maker_url', 'text', array(
                'label' => 'URL',
	            'required' => false,
                'constraints' => array(
                    new Assert\Url(),
                ),
            ))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'admin_product_maker';
    }
}