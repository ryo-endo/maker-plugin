<?php

namespace Plugin\Maker\Entity;

use Doctrine\ORM\Mapping as ORM;
use Eccube\Annotation as Eccube;

/**
 * @Eccube\EntityExtension("Eccube\Entity\Product")
 */
trait ProductTrait
{
    /**
     * @var \Plugin\Maker\Entity\Maker
     *
     * @ORM\ManyToOne(targetEntity="Plugin\Maker\Entity\Maker")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="plg_maker_id", referencedColumnName="id")
     * })
     */
    private $Maker;

    /**
     * @var string
     *
     * @ORM\Column(name="plg_maker_url", type="string", length=1024, nullable=true)
     */
    private $maker_url;

    /**
     * @return Maker
     */
    public function getMaker()
    {
        return $this->Maker;
    }

    /**
     * @param Maker|null $Maker
     * @return $this
     */
    public function setMaker(Maker $Maker = null)
    {
        $this->Maker = $Maker;

        return $this;
    }

    /**
     * @return string
     */
    public function getMakerUrl()
    {
        return $this->maker_url;
    }

    /**
     * @param string $maker_url
     */
    public function setMakerUrl($maker_url)
    {
        $this->maker_url = $maker_url;
    }


}