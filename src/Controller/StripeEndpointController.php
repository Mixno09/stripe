<?php

namespace App\Controller;

use Stripe\StripeClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class StripeEndpointController extends AbstractController
{
    #[Route('/stripe/endpoint', name: 'app_stripe_endpoint', methods: ['GET', 'POST'])]
    public function index(Request $request): Response
    {
        $stripe = new StripeClient($_ENV["STRIPE_SECRET_KEY"]);

        return $this->json('');
    }
}
