<?php

namespace ExtendedDocument\APIBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;

/**
 * Document
 *
 * @ORM\Table(name="document")
 * @ORM\Entity(repositoryClass="ExtendedDocument\APIBundle\Repository\DocumentRepository")
 */
class Document implements JsonSerializable
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @ORM\OneToOne(targetEntity="Metadata", inversedBy="document", fetch="EAGER")
     */
    private $metadata;

    /**
     * @ORM\OneToOne(targetEntity="Visualization", inversedBy="document", fetch="EAGER")
     */
    private $visualization;

    /**
     * Set metadata
     *
     * @param \ExtendedDocument\APIBundle\Entity\Metadata $metadata
     *
     * @return Document
     */
    public function setMetadata(\ExtendedDocument\APIBundle\Entity\Metadata $metadata = null)
    {
        $this->metadata = $metadata;

        return $this;
    }

    /**
     * Get metadata
     *
     * @return \ExtendedDocument\APIBundle\Entity\Metadata
     */
    public function getMetadata()
    {
        return $this->metadata;
    }

    /**
     * Set visualization
     *
     * @param \ExtendedDocument\APIBundle\Entity\Visualization $visualization
     *
     * @return Document
     */
    public function setVisualization(\ExtendedDocument\APIBundle\Entity\Visualization $visualization = null)
    {
        $this->visualization = $visualization;

        return $this;
    }

    /**
     * Get visualization
     *
     * @return \ExtendedDocument\APIBundle\Entity\Visualization
     */
    public function getVisualization()
    {
        return $this->visualization;
    }

    /**
     * Specify data which should be serialized to JSON
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        return [
            'idDocument' => $this->getId(),
          'metadata' => $this->getMetadata()->jsonSerialize(),
          'visualization' => $this->getVisualization()->jsonSerialize()
        ];
    }
}
