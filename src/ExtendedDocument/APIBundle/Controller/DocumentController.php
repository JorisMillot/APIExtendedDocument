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

        //Copy the file on the server :

        //We retrieve the file
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
            $file->move($this->getParameter('document_directory'),$filename);
        }

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

        $newMetadata->setLink($filename);
        $newMetadata->setOriginalName($originalName);

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

        //return new Response(json_encode($newDocument));

        return new Response($newDocument->getId());

        //return new Response(var_dump(array_keys((array)$newDocument->getMetadata())));
    }

    public function editDocumentAction(Request $request, $idDocument){
        $em = $this->getDoctrine()->getManager();
        $documentRepository = $em->getRepository('ExtendedDocument\APIBundle\Entity\Document');

        //We check if the document exists.
        if(($document = $documentRepository->find($idDocument,null)) == null){
            return new Response('Error : unknown document',Response::HTTP_NOT_FOUND);
        }

        //Metadata edition
        $metadata = $em->getClassMetadata('ExtendedDocument\APIBundle\Entity\Metadata');

        foreach ($metadata->getFieldNames() as $key => $fieldName){
            //If the field is provided by the request we edit it
            if($fieldName != 'id' && $request->get($fieldName,null) != null){
                $methodSet = 'set'.ucfirst($fieldName); //contains the name of the method to call for each field
                $document->getMetadata()->$methodSet($request->get($fieldName,null));
            }
        }

        //Visualization edition

        $metadata = $em->getClassMetadata('ExtendedDocument\APIBundle\Entity\Visualization');

        foreach ($metadata->getFieldNames() as $key => $fieldName){
            //If the field is provided by the request we edit it
            if($fieldName != 'id' && $request->get($fieldName,null) != null){
                $methodSet = 'set'.ucfirst($fieldName); //contains the name of the method to call for each field
                $document->getVisualization()->$methodSet($request->get($fieldName,null));
            }
        }
        $em->flush();


        return new Response('OK');
    }

    public function deleteDocumentAction(Request $request, $idDocument){
        $em = $this->getDoctrine()->getManager();
        $documentRepository = $em->getRepository('ExtendedDocument\APIBundle\Entity\Document');

        //We check if the document exists.
        if(($document = $documentRepository->find($idDocument,null)) == null){
            return new Response('Error : unknown document',Response::HTTP_NOT_FOUND);
        }
        $em->remove($document->getMetadata());
        $em->remove($document->getVisualization());
        $em->remove($document);
        $em->flush();


        return new Response('OK');
    }

    public function getDocumentAction(Request $request, $idDocument){
        $em = $this->getDoctrine()->getManager();
        $documentRepository = $em->getRepository('ExtendedDocument\APIBundle\Entity\Document');

        //We check if the document exists.
        if(($document = $documentRepository->find($idDocument,null)) == null){
            return new Response('Error : unknown document',Response::HTTP_NOT_FOUND);
        }else{
            $response = new Response(json_encode($document));
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        }
    }

    public function getDocumentsAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $documentRepository = $em->getRepository('ExtendedDocument\APIBundle\Entity\Document');

        if($request->get('refDate1','0000-01-01') != null ||
            $request->get('publicationDate1','0000-01-01') != null){
            //at least one date was provided
            $qb = $em->createQueryBuilder();

            //reference date
            $refDate1 = $request->get('refDate1','0001-01-01');
            if($request->get('refDate1',null) == null)
                //no publication date was provided
                $refDate2 = '9999-12-31';
            else
                $refDate2 = $request->get('refDate2',$refDate1);

            //publication date
            $publicationDate1 = $request->get('$publicationDate1','0001-01-01');
            if($request->get('$publicationDate1',null) == null)
                //no publication date was provided
                $publicationDate2 = '9999-12-31';
            else
                $publicationDate2 = $request->get('publicationDate2',$refDate1);

            $qb->select('d,m,v')
                ->from('ExtendedDocument\APIBundle\Entity\Document', 'd')
                ->innerJoin('d.metadata','m')
                ->innerJoin('d.visualization','v')
                ->where(
                    $qb->expr()->andX(
                        $qb->expr()->lte("m.refDate",'\''.$refDate2.'\''),
                        $qb->expr()->gte("m.refDate",'\''.$refDate1.'\''),
                        $qb->expr()->lte("m.publicationDate",'\''.$publicationDate2.'\''),
                        $qb->expr()->gte("m.publicationDate",'\''.$publicationDate1.'\'')
                    )
                )
                ->orderBy("m.refDate")
                ->setMaxResults( (int) $request->get('limit',99999) )
            ;
            $documents = $qb->getQuery()->getResult();
        }else{
            $documents = $documentRepository->findAll();
        }

        // Document type filter
        if(($documentType = $request->get('documentType',null))!=null){
            //A type of document was provided
            $documentsFiltered = array();
            foreach ($documents as $document){
                if($document->getMetadata()->getType() == $documentType){
                    array_push($documentsFiltered,$document);
                }
            }
            $documents = $documentsFiltered;
        }

        //Subject filter
        if(($documentSubject = $request->get('subject',null))!=null){
            //A subject was provided
            $documentsFiltered = array();
            foreach ($documents as $document){
                if($document->getMetadata()->getSubject() == $documentSubject){
                    array_push($documentsFiltered,$document);
                }
            }
            $documents = $documentsFiltered;
        }

        //Keyword filter //Work with only one keywork for now
        if(($keyword = $request->get('keyword',null))!=null){
            //A keyword was provided
            $documentsFiltered = array();
            foreach ($documents as $document){
                if(substr_count($document->getMetadata()->toStringForKeywordFilter(),$keyword)>0){
                    array_push($documentsFiltered,$document);
                }
            }
            $documents = $documentsFiltered;
        }

        /*if(($x = $request->get('x',null) != null)
        || ($y = $request->get('y',null) != null)
        || ($radius = $request->get('radius',null) != null)
        ){
            //The user has provided at least one of the parameter to fetch document with a radius
            //We check if all the parameter are provided
            if(($x = $request->get('x',null) == null)
                || ($y = $request->get('y',null) == null)
                || ($radius = $request->get('radius',null) == null)
            ){
                //At least one parameter is missing
                return new Response('You tried to fetch documents by providing a point and a radius but some parameters are missing.
                Check the documentation for more informations',Response::HTTP_BAD_REQUEST);
            }

            $documentsByDistance = array();

            //We check if the documents have x and y coordinates
            foreach ($documents as $document) {
                if ($document->getVisualization()->getPositionX() != null &&
                    $document->getVisualization()->getPositionY() != null &&
                    ($distance = $this->distance($x, $y, $document->getVisualization()->getPositionX(), $document->getVisualization()->getPositionY())) <= $radius) {
                    array_push($documentsByDistance, ['document' => $document, 'distance' => $distance]);
                }
            }

            //var_dump($documentsByDistance);

            //Sort the document by the distance from the provided point
            uasort($documentsByDistance, "ExtendedDocument\APIBundle\Controller\DocumentController::sortByDistance");

            //var_dump($documentsByDistance);

            $result = array();

            foreach ($documentsByDistance as $document){
                if (round($limit,0,PHP_ROUND_HALF_DOWN) == 0){
                    $response = new Response(json_encode($result));
                    $response->headers->set('Content-Type', 'application/json');
                    return $response;
                }
                array_push($result,$document['document']);
                $limit--;
            }

        }*/

        $response = new Response(json_encode($documents));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    public function getDocumentsByRadiusAction(Request $request, $radius, $x, $y, $limit){
        $em = $this->getDoctrine()->getManager();
        $documentRepository = $em->getRepository('ExtendedDocument\APIBundle\Entity\Document');

        $documents = $documentRepository->findAll();

        /*$sql = "SELECT ST_Distance(
		ST_GeomFromText('POINT(-72.1235 42.3521)'),
		ST_GeomFromText('POINT($x $y)')
	)";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->execute();
        var_dump($stmt->fetchAll());


        */

        $documentsByDistance = array();

        foreach ($documents as $document) {
            if ($document->getVisualization()->getPositionX() != null &&
                $document->getVisualization()->getPositionY() != null &&
                ($distance = $this->distance($x, $y, $document->getVisualization()->getPositionX(), $document->getVisualization()->getPositionY())) <= $radius) {
                array_push($documentsByDistance, ['document' => $document, 'distance' => $distance]);
            }
        }

        //var_dump($documentsByDistance);

        //Sort the document by the distance from the provided point
        uasort($documentsByDistance, "ExtendedDocument\APIBundle\Controller\DocumentController::sortByDistance");

        //var_dump($documentsByDistance);

        $result = array();

        foreach ($documentsByDistance as $document){
            if (round($limit,0,PHP_ROUND_HALF_DOWN) == 0){
                $response = new Response(json_encode($result));
                $response->headers->set('Content-Type', 'application/json');
                return $response;
            }
            array_push($result,$document['document']);
            $limit--;
        }

        $response = new Response(json_encode($result));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    public function sortByDistance($var1, $var2){
        if($var1['distance'] == $var2['distance'])
            return 0;
        return $var1['distance'] > $var2['distance'];
    }

    /**
     * @param $x1 double
     * @param $y1 double
     * @param $x2 double
     * @param $y2 double
     * @return double
     */
    public function distance($x1,$y1,$x2,$y2){ //prendre en compte la courbure de la terre mais quelles sont les unités utilisée ?
        $x = ( pow($x2,2) - pow($x1,2));
        $y = ( pow($y2,2) - pow($y1,2));

        return ( sqrt(abs($x + $y)) );
    }

    public function getDocumentsByDateAction(Request $request, $type, $date1, $date2){
        $em = $this->getDoctrine()->getManager();
        $metadata = $em->getClassMetadata('ExtendedDocument\APIBundle\Entity\Metadata');

        if(!array_search($type,$metadata->getFieldNames())){
            return new Response("Error : type provided doesn't exists", Response::HTTP_BAD_REQUEST);
        }else{
            if($metadata->getTypeOfField($type) != 'date'){
                return new Response("Error : type provided isn't a date", Response::HTTP_BAD_REQUEST);
            }

            if(!isset($date2)){
                $qb = $em->createQueryBuilder();
                $qb->select('d,m,v')
                    ->from('ExtendedDocument\APIBundle\Entity\Document', 'd')
                    ->innerJoin('d.metadata','m')
                    ->innerJoin('d.visualization','v')
                    ->where(
                        $qb->expr()->andX(
                            $qb->expr()->eq("m.".$type,'\''.$date1.'\'')
                        )
                    );
            }else{
                $qb = $em->createQueryBuilder();
                $qb->select('d,m,v')
                    ->from('ExtendedDocument\APIBundle\Entity\Document', 'd')
                    ->innerJoin('d.metadata','m')
                    ->innerJoin('d.visualization','v')
                    ->where(
                        $qb->expr()->andX(
                            $qb->expr()->lte("m.".$type,'\''.$date2.'\''),
                            $qb->expr()->gte("m.".$type,'\''.$date1.'\'')
                        )
                    )
                    ->orderBy("m.".$type);
            }

            $response = $qb->getQuery()->getResult();

            return new Response(json_encode($response));
        }
    }

    /*public function getAuthoritativeSportsRecords()
{
    $sql = "
        SELECT name,
               event_type,
               sport_type,
               level
          FROM vnn_sport
    ";

    $em = $this->getDoctrine()->getManager();
    $stmt = $em->getConnection()->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll();
}   */

    //Only for developpement : display the database
    public function displayDocumentsAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $documentRepository = $em->getRepository('ExtendedDocument\APIBundle\Entity\Document');

        $documents = $documentRepository->findAll();

        $html = '<style>table, th, td {
    border: 1px solid black;
}</style>';

        $html.='<table><thead><td>IdDocument</td>';

        $metadataMetadata = $em->getClassMetadata('ExtendedDocument\APIBundle\Entity\Metadata');

        foreach ($metadataMetadata->getFieldNames() as $key =>$value){
            if($value != 'id' && $value != 'document')
                $html.='<td>'.$value.'</td>';
        }

        $metadataVisualization = $em->getClassMetadata('ExtendedDocument\APIBundle\Entity\Visualization');

        foreach ($metadataVisualization->getFieldNames() as $key =>$value){
            if($value != 'id' && $value != 'document')
                $html.='<td>'.$value.'</td>';
        }

        $html.='</thead>';

        foreach ($documents as $document){
            $html.= "<tr>";
            $html.="<td>".$document->getId()."</td>";
            foreach ($document->getMetadata()->jsonSerialize() as $keyMD => $valueMD ){
                if($keyMD != 'id' && $keyMD != 'document'){
                    $html.= '<td>'.$valueMD.'</td>';
                }
            }
            foreach ($document->getVisualization()->jsonSerialize() as $keyMD => $valueMD ){
                if($keyMD != 'id' && $keyMD != 'document'){
                    $html.= '<td>'.$valueMD.'</td>';
                }
            }
            $html.= "</tr>";
        }

        $html.= '</table>';

        return new Response($html);
    }
}