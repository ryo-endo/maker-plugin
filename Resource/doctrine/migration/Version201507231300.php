<?php
/*
 * This file is part of the Maker plugin
 *
 * Copyright (C) 2016 LOCKON CO.,LTD. All Rights Reserved.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\ORM\Tools\SchemaTool;
use Eccube\Application;
use Eccube\Common\Constant;

/**
 * Class Version201507231300.
 */
class Version201507231300 extends AbstractMigration
{
    /**
     * @var string table name
     */
    const NAME = 'plg_maker';

    const NAME2 = 'plg_product_maker';

    protected $entities = array(
        'Plugin\Maker\Entity\Maker',
        'Plugin\Maker\Entity\ProductMaker',
    );

    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        if (version_compare(Constant::VERSION, '3.0.9', '>=')) {
            $this->createPlgMaker($schema);
            $this->createPlgProductMaker($schema);
        } else {
            $this->createPlgMakerForOldVersion($schema);
            $this->createPlgProductMakerForOldVersion($schema);
        }
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        if (version_compare(Constant::VERSION, '3.0.9', '>=')) {
            $app = Application::getInstance();
            $meta = $this->getMetadata($app['orm.em']);
            $tool = new SchemaTool($app['orm.em']);
            $schemaFromMetadata = $tool->getSchemaFromMetadata($meta);
            // テーブル削除
            foreach ($schemaFromMetadata->getTables() as $table) {
                if ($schema->hasTable($table->getName())) {
                    $schema->dropTable($table->getName());
                }
            }
            // シーケンス削除
            foreach ($schemaFromMetadata->getSequences() as $sequence) {
                if ($schema->hasSequence($sequence->getName())) {
                    $schema->dropSequence($sequence->getName());
                }
            }
        } else {
            $schema->dropTable(self::NAME);
            $schema->dropTable(self::NAME2);
        }
    }

    /**
     * Create maker table.
     *
     * @param Schema $schema
     *
     * @return bool
     */
    protected function createPlgMaker(Schema $schema)
    {
        if ($schema->hasTable(self::NAME)) {
            return true;
        }

        $app = Application::getInstance();
        $em = $app['orm.em'];
        $classes = array(
            $em->getClassMetadata('Plugin\Maker\Entity\Maker'),
        );
        $tool = new SchemaTool($em);
        $tool->createSchema($classes);

        return true;
    }

    /**
     * Create product maker table.
     *
     * @param Schema $schema
     *
     * @return bool
     */
    protected function createPlgProductMaker(Schema $schema)
    {
        if ($schema->hasTable(self::NAME2)) {
            return true;
        }

        $app = Application::getInstance();
        $em = $app['orm.em'];
        $classes = array(
            $em->getClassMetadata('Plugin\Maker\Entity\ProductMaker'),
        );
        $tool = new SchemaTool($em);
        $tool->createSchema($classes);

        return true;
    }

    /**
     * @param Schema $schema
     */
    protected function createPlgMakerForOldVersion(Schema $schema)
    {
        $table = $schema->createTable('plg_maker');
        $table->addColumn('maker_id', 'integer', array(
            'autoincrement' => true,
        ));

        $table->addColumn('name', 'text', array(
            'notnull' => true,
        ));

        $table->addColumn('rank', 'integer', array(
            'notnull' => true,
            'unsigned' => false,
            'default' => 0,
        ));

        $table->addColumn('del_flg', 'smallint', array(
            'notnull' => true,
            'unsigned' => false,
            'default' => 0,
        ));

        $table->addColumn('create_date', 'datetime', array(
            'notnull' => true,
            'unsigned' => false,
        ));

        $table->addColumn('update_date', 'datetime', array(
            'notnull' => true,
            'unsigned' => false,
        ));

        $table->setPrimaryKey(array('maker_id'));
    }

    /**
     * @param Schema $schema
     */
    protected function createPlgProductMakerForOldVersion(Schema $schema)
    {
        $table = $schema->createTable('plg_product_maker');
        $table->addColumn('product_id', 'integer', array(
            'notnull' => true,
        ));

        $table->addColumn('maker_id', 'text', array(
            'notnull' => true,
        ));

        $table->addColumn('maker_url', 'text', array());

        $table->addColumn('del_flg', 'smallint', array(
            'notnull' => true,
            'unsigned' => false,
            'default' => 0,
        ));

        $table->addColumn('create_date', 'datetime', array(
            'notnull' => true,
            'unsigned' => false,
        ));

        $table->addColumn('update_date', 'datetime', array(
            'notnull' => true,
            'unsigned' => false,
        ));

        $table->setPrimaryKey(array('product_id'));
    }
}
