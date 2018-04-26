<?php
namespace Plugin\Maker;

use Eccube\Common\EccubeNav;

class Nav implements EccubeNav
{
    /**
     * {@inheritdoc}
     *
     * @return array
     */
    public static function getNav()
    {
        return [
            'product' => [
                'id' => 'maker',
                'name' => 'メーカー管理',
                'url' => 'admin_plugin_maker_index'
            ]
        ];
    }
}