<?php

namespace ExtendedDocument\APIBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('@ExtendedDocumentAPI/Default/index.html.twig');
    }
}
