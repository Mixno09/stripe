<?php

namespace App\Controller;

use App\Entity\CheckoutSession;
use App\Entity\User;
use App\Repository\CheckoutSessionRepository;
use App\Services\CheckoutSessionService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class StripeController extends AbstractController
{
    #[Route('/', name: 'app_stripe', methods: 'GET')]
    public function index(CheckoutSessionRepository $checkoutSessionRepository): Response
    {
        $user = $this->getUser();
        $checkoutSessions = $checkoutSessionRepository->findBy(['user' => $user->getId()]);

        $statusSubscribe = null;
        $sessionId = null;
        foreach ($checkoutSessions as $checkoutSession) {
            if ($checkoutSession->getSubStatus() === 'active') {
                $statusSubscribe = $checkoutSession->getMode();
                $sessionId = $checkoutSession->getSessionId();
                break;
            }
        }

        return $this->render('stripe/index.html.twig', [
            'statusSubscribe' => $statusSubscribe,
            'sessionId' => $sessionId
        ]);
    }

    #[Route('/stripe/create/charge', name: 'app_stripe_charge', methods: ['POST'])]
    public function createCharge(
        Request $request,
        CheckoutSessionService $checkoutSessionService,
        CheckoutSessionRepository $checkoutSessionRepository,
        EntityManagerInterface $entityManager,
    ): Response {

        $price = $request->request->getInt('price');
        $cancelSubscribe = $request->request->get('cancel_subscribe');
        $subscribe = $request->request->getInt('subscribe');

        if ($subscribe === 0 && $cancelSubscribe === null) {
            $checkoutSession = $checkoutSessionService->createSession($price);
            return $this->redirect($checkoutSession->url, Response::HTTP_SEE_OTHER);
        }

        if ($cancelSubscribe === null) {
            $subscription = $checkoutSessionService->createSessionSubscriber($price);
            return $this->redirect($subscription->url, Response::HTTP_SEE_OTHER);
        }

        $checkoutSession = $checkoutSessionService->getSession($request->request->get('sessionId'));
        $subscription = $checkoutSessionService->cancelSubscription($checkoutSession->subscription);

        $checkoutSession = $checkoutSessionRepository->findOneBy(['sessionId' => $request->request->get('sessionId')]);
        $checkoutSession->setSubStatus($subscription->status);
        $entityManager->persist($checkoutSession);
        $entityManager->flush();

        return $this->render('stripe/subs_cancel.html.twig', ['status' => $subscription->status]);
    }

    #[Route('/stripe/cancel', name: 'app_cancel')]
    public function cancel(): Response
    {
        $this->addFlash(
            'cancel',
            'Operation aborted!'
        );

        return $this->render('stripe/cancel.html.twig');
    }

    #[Route('/stripe/success', name: 'app_success', methods: 'GET')]
    public function success(
        Request $request,
        CheckoutSessionService $checkoutSessionService,
        EntityManagerInterface $entityManager,
    ): Response {

        if (! $request->query->has('session_id')) {
            return $this->render('stripe/error.html.twig');
        }

        $session = $checkoutSessionService->getSession($request->query->getString('session_id'));
        $subscription = $checkoutSessionService->getSubscription($session->subscription);

        /** @var User $user */
        $user = $this->getUser();
        $checkoutSession = new CheckoutSession();
        $checkoutSession->setSessionId($session->id);
        $checkoutSession->setMode($session->mode);
        $checkoutSession->setSubStatus($subscription->status);
        $checkoutSession->setUser($user);
        $entityManager->persist($checkoutSession);
        $entityManager->flush();

        $this->addFlash(
            'success',
            'Payment Successful!'
        );

        return $this->render('stripe/success.html.twig', ['customer' => $session->customer_details->name]);
    }
}
