<?php

namespace App\Controller;

use FourLabs\GampBundle\FourLabsGampBundle;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use TheIconic\Tracking\GoogleAnalytics\Analytics;

class GampController extends AbstractController
{
    public function index(): Response
    {
        $response = new Response();
        $response->setContent('ok');
        $response->setStatusCode(200);

        return $response;
    }
}