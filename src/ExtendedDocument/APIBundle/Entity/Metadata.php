<?php

namespace ExtendedDocument\APIBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Metadata
 *
 * @ORM\Table(name="metadata")
 * @ORM\Entity(repositoryClass="ExtendedDocument\APIBundle\Repository\MetadataRepository")
 */
class Metadata implements JsonSerializable, DoctrineEntity
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255)
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="subject", type="string", length=255)
     */
    private $subject;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=255)
     */
    private $description;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="refDate", type="date")
     */
    private $refDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="publicationDate", type="date")
     */
    private $publicationDate;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=255)
     */
    private $type;

    /**
     * @var string
     *
     * @ORM\Column(name="link", type="string", length=255, nullable=true)
     */
    private $link;

    /**
    * @ORM\OneToOne(targetEntity="Document", mappedBy="id")
    */
    private $document;

    /**
     * @var string
     *
     * @ORM\Column(name="originalName", type="string", length=255, nullable=true)
     */
    private $originalName;


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
     * Set title
     *
     * @param string $title
     *
     * @return Metadata
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set subject
     *
     * @param string $subject
     *
     * @return Metadata
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * Get subject
     *
     * @return string
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return Metadata
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set refDate
     *
     * @param \DateTime $refDate
     *
     * @return Metadata
     */
    public function setRefDate($refDate)
    {
        $this->refDate = \DateTime::createFromFormat('Y-m-j',$refDate);

        return $this;
    }

    /**
     * Get refDate
     *
     * @return \DateTime
     */
    public function getRefDate()
    {
        return $this->refDate;
    }

    /**
     * Set publicationDate
     *
     * @param \DateTime $publicationDate
     *
     * @return Metadata
     */
    public function setPublicationDate($publicationDate)
    {
        $this->publicationDate = \DateTime::createFromFormat('Y-m-j',$publicationDate);

        return $this;
    }

    /**
     * Get publicationDate
     *
     * @return \DateTime
     */
    public function getPublicationDate()
    {
        return $this->publicationDate;
    }

    /**
     * Set type
     *
     * @param string $type
     *
     * @return Metadata
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set link
     *
     * @param string $link
     *
     * @return Metadata
     */
    public function setLink($link)
    {
        $this->link = $link;

        return $this;
    }

    /**
     * Get link
     *
     * @return string
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * Set document
     *
     * @param \ExtendedDocument\APIBundle\Entity\Document $document
     *
     * @return Metadata
     */
    public function setDocument(\ExtendedDocument\APIBundle\Entity\Document $document = null)
    {
        $this->document = $document;

        return $this;
    }

    /**
     * Get document
     *
     * @return \ExtendedDocument\APIBundle\Entity\Document
     */
    public function getDocument()
    {
        return $this->document;
    }

    public function toStringForKeywordFilter(){
        return $this->title.' '
            .$this->description.' '
            .$this->subject.' '
            .$this->type;
    }


    /**
     * Set originalName
     *
     * @param string $originalName
     *
     * @return Metadata
     */
    public function setOriginalName($originalName)
    {
        $this->originalName = $originalName;

        return $this;
    }

    /**
     * Get originalName
     *
     * @return string
     */
    public function getOriginalName()
    {
        return $this->originalName;
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
        $arrayJson = array();
        foreach ($this as $key =>$value){
            /*$getter = 'get'.ucfirst($key);

            $arrayJson[$key] = $this->$getter();*/
            if($key != 'document'){
                $getter = 'get'.ucfirst($key);

                $arrayJson[$key] = $this->$getter();

                if(gettype($value) == 'object' && get_class($value) == \DateTime::class){
                    //If it's a DateTime object, we only want the date formated in the following format :
                    $arrayJson[$key] = $value->format('Y-m-d');
                }
            }
        }
        return $arrayJson;
    }

    /**
     * @param $request Request
     * @param $controller mixed
     * @return String|int
     */
    public function initEntity($request, $controller){
        if($request == null)
            return 'Error : request wasn\'t provided';

        //Copy the file on the server :

        //We retrieve the file
        /**
         * @var $file UploadedFile
         */
        $file = $request->files->get('link');

        if(!isset($file)){
            return new Response('Error : no file given', Response::HTTP_BAD_REQUEST);
        }
        if (!$file->isValid()){
            return new Response($file->getErrorMessage(), Response::HTTP_BAD_REQUEST);
        }else {
            $originalName = $file->getClientOriginalName();

            //On génére une clé unique pour le fichier
            $filekey = md5(uniqid(rand(), true));
            //On y ajoute l'extention
            $filename = $filekey . '.' . $file->getClientOriginalExtension();

            //Copie du fichier sur le serveur
            $file->move(__DIR__.'../../../../../web/documentsDirectory',$filename);
            //echo __DIR__.'../../web/documentsDirectory';
        }

        $metadata = $controller->getManager()->getClassMetadata('ExtendedDocument\APIBundle\Entity\Metadata');

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

        $this->setLink($filename);
        $this->setOriginalName($originalName);
        return 1;
    }

    public function editEntity($request, $controller)
    {
        $metadata = $controller->getManager()->getClassMetadata('ExtendedDocument\APIBundle\Entity\Metadata');

        foreach ($metadata->getFieldNames() as $key => $fieldName){
            if($fieldName != 'id' && $request->get($fieldName,null) != null){
                $methodSet = 'set'.ucfirst($fieldName); //contains the name of the method to call for each field
                $this->$methodSet($request->get($fieldName,null));
            }
        }
    }
}
