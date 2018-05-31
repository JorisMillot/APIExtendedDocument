<?php
/**
 * Created by PhpStorm.
 * User: Jojo
 * Date: 31/05/2018
 * Time: 13:40
 */

namespace ExtendedDocument\APIBundle\Entity;


interface DoctrineEntity
{
    public function initEntity($request, $controller);
    public function editEntity($request, $controller);
}