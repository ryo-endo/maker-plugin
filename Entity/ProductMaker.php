<?php
/*
 * This file is part of the Maker plugin
 *
 * Copyright (C) 2016 LOCKON CO.,LTD. All Rights Reserved.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Plugin\Maker\Entity;

use Eccube\Entity\AbstractEntity;
use Eccube\Util\EntityUtil;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class ProductMaker.
 *
 * @ORM\Table(name="plg_product_maker")
 * @ORM\Entity(repositoryClass="Plugin\Maker\Repository\ProductMakerRepository")
 */
class ProductMaker extends AbstractEntity
{
    /**
     * @var int
     *
     * @ORM\Column(name="product_id", type="integer", options={"unsigned":true})
     * @ORM\Id
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="maker_url", type="string", nullable=true)
     */
    private $maker_url;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="create_date", type="datetimetz")
     */
    private $create_date;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="update_date", type="datetimetz")
     */
    private $update_date;

    /**
     * @var Maker
     *
     * @ORM\ManyToOne(targetEntity="Maker", inversedBy="ProductMaker", cascade={"persist"})
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="maker_id", referencedColumnName="maker_id", onDelete="CASCADE")
     * })
     */
    protected $Maker;

    /**
     * Set Id.
     *
     * @param int $id
     *
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set maker url.
     *
     * @param string $makerUrl
     *
     * @return $this
     */
    public function setMakerUrl($makerUrl)
    {
        $this->maker_url = $makerUrl;

        return $this;
    }

    /**
     * Get maker url.
     *
     * @return mixed
     */
    public function getMakerUrl()
    {
        return $this->maker_url;
    }

    /**
     * @param \DateTime $createDate
     *
     * @return $this
     */
    public function setCreateDate(\DateTime $createDate)
    {
        $this->create_date = $createDate;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreateDate()
    {
        return $this->create_date;
    }

    /**
     * @param \DateTime $updateDate
     *
     * @return $this
     */
    public function setUpdateDate(\DateTime $updateDate)
    {
        $this->update_date = $updateDate;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getUpdateDate()
    {
        return $this->update_date;
    }

    /**
     * @param Maker $maker
     *
     * @return $this
     */
    public function setMaker(Maker $maker)
    {
        $this->Maker = $maker;

        return $this;
    }

    /**
     * @return null|Maker
     */
    public function getMaker()
    {
        if (EntityUtil::isEmpty($this->Maker)) {
            return null;
        }

        return $this->Maker;
    }
}
