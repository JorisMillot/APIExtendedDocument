<?php
/**
 * Created by PhpStorm.
 * User: Jojo
 * Date: 07/05/2018
 * Time: 13:04
 */

namespace ExtendedDocument\APIBundle\Controller;


use ExtendedDocument\APIBundle\Entity\Document;
use ExtendedDocument\APIBundle\Entity\Metadata;
use ExtendedDocument\APIBundle\Entity\Visualization;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DocumentController extends Controller
{
    public function addDocumentAction(Request $request){
        //Verification que tous les champs obligatoires sont remplis à partir du schema doctrine :
        $em = $this->getDoctrine()->getManager();
        $metadata = $em->getClassMetadata('ExtendedDocument\APIBundle\Entity\Metadata');
        //$isRequired = !$metadata->isNullable("description");

        //METADATA CREATION
        $newMetadata = new Metadata();

        foreach ($metadata->getFieldNames() as $key => $fieldName){
            //If the field is required and the field is not provided we return an error 400 : Bad Request
            if($fieldName != 'id' && !$metadata->isNullable($fieldName) && $request->get($fieldName,null) == null){
                return new Response('Error : Some parameters are missings : '.$fieldName,Response::HTTP_BAD_REQUEST);
            }
            if($fieldName != 'id'){
                $methodSet = 'set'.ucfirst($fieldName); //contains the name of the method to call for each field
                $newMetadata->$methodSet($request->get($fieldName,null));
            }
        }

        //Visualization CREATION

        $newVisualization = new Visualization();
        $metadata = $em->getClassMetadata('ExtendedDocument\APIBundle\Entity\Visualization');

        foreach ($metadata->getFieldNames() as $key => $fieldName){
            //If the field is required and the field is not provided we return an error 400 : Bad Request
            if($fieldName != 'id' && !$metadata->isNullable($fieldName) && $request->get($fieldName,null) == null){
                return new Response('Error : Some parameters are missings : '.$fieldName,Response::HTTP_BAD_REQUEST);
            }
            if($fieldName != 'id'){
                $methodSet = 'set'.ucfirst($fieldName); //contains the name of the method to call for each field
                $newVisualization->$methodSet($request->get($fieldName,null));
            }
        }

        //DOCUMENTS CREATION

        $newDocument = new Document();
        $newDocument->setMetadata($newMetadata);
        $newDocument->setVisualization($newVisualization);

        $em->persist($newMetadata);
        $em->persist($newVisualization);
        $em->persist($newDocument);

        $em->flush();

        return new Response(json_encode($newDocument));

        //return new Response(var_dump(array_keys((array)$newDocument->getMetadata())));
    }
}