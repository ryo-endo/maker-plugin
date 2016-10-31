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
use Doctrine\ORM\EntityManager;

/**
 * Class Version201507231300.
 */
class Version201507231300 extends AbstractMigration
{
    /**
     * @var string table name
     */
    const MAKER = 'plg_maker';

    /**
     * @var string product maker table
     */
    const PRODUCTMAKER = 'plg_product_maker';

    /**
     * @var array plugin entity
     */
    protected $entities = array(
        'Plugin\Maker\Entity\Maker',
        'Plugin\Maker\Entity\ProductMaker',
    );

    /**
     * Up method
     *
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
     * Down method
     *
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
            $schema->dropTable(self::MAKER);
            $schema->dropTable(self::PRODUCTMAKER);
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
        if ($schema->hasTable(self::MAKER)) {
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
        if ($schema->hasTable(self::PRODUCTMAKER)) {
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
     * Create maker for old version
     *
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
     * Create product maker for old version.
     *
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

    /**
     * Get metadata.
     *
     * @param EntityManager $em
     *
     * @return array
     */
    protected function getMetadata(EntityManager $em)
    {
        $meta = array();
        foreach ($this->entities as $entity) {
            $meta[] = $em->getMetadataFactory()->getMetadataFor($entity);
        }

        return $meta;
    }
}
