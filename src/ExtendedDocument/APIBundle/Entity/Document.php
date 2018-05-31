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
class Document implements JsonSerializable, DoctrineEntity
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
     * @ORM\OneToOne(targetEntity="Metadata", inversedBy="document", fetch="EAGER", cascade={"persist"})
     */
    private $metadata;

    /**
     * @ORM\OneToOne(targetEntity="Visualization", inversedBy="document", fetch="EAGER", cascade={"persist"})
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

    public function initEntity($request, $controller)
    {

        $metadata = $controller->getManager()->getClassMetadata('ExtendedDocument\APIBundle\Entity\Document');

        foreach ($metadata->getFieldNames() as $key => $fieldName){
            //If the field is required and the field is not provided we return an error 400 : Bad Request
            if($fieldName != 'id' && !$metadata->isNullable($fieldName) && $request->get($fieldName,null) == null){
                return 'Error : Some parameters are missings : '.$fieldName;
            }
            if($fieldName != 'id'){
                $methodSet = 'set'.ucfirst($fieldName); //contains the name of the method to call for each field
                $this->$methodSet($request->get($fieldName,null));
            }
        }

        foreach ($metadata->associationMappings as $key => $value) {
            $methodGet = 'get'.ucfirst($key);
            $methodSet = 'set'.ucfirst($key); //contains the name of the method set
            if (gettype($this->$methodGet() == 'object')) {
                $newObject = new $value['targetEntity']();
                if(($response = $newObject->initEntity($request,$controller)) != 1){
                    return $response;
                }
                $this->$methodSet($newObject);
            }
        }

        return 1;
    }

    public function editEntity($request, $controller)
    {
        $metadata = $controller->getManager()->getClassMetadata('ExtendedDocument\APIBundle\Entity\Document');

        foreach ($metadata->getFieldNames() as $key => $fieldName){
            if($fieldName != 'id'){
                $methodSet = 'set'.ucfirst($fieldName); //contains the name of the method to call for each field
                $this->$methodSet($request->get($fieldName,null));
            }
        }

        foreach ($metadata->associationMappings as $key => $value) {
            $methodGet = 'get'.ucfirst($key);
            $methodSet = 'set'.ucfirst($key); //contains the name of the method set
            /**
             * @var $object DoctrineEntity
             */
            if (gettype($this->$methodGet() == 'object')) {
                $object = $this->$methodGet();
                $object->editEntity($request,$controller);
            }
        }
    }
}
