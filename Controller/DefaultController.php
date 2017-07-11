<?php

namespace Codibly\QueuesBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('CodiblyQueuesBundle:Default:index.html.twig');
    }
}
