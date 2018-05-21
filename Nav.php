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
                'name' => 'admin.plugin.maker.sub_title',
                'url' => 'admin_plugin_maker_index'
            ]
        ];
    }
}