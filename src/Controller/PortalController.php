<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\ForecastRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class PortalController extends AbstractController
{
    #[Route('/', name: 'app_portal', methods: ['GET'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function index(ForecastRepository $forecasts): Response
    {
        $user = $this->getUser();
        if (!$user instanceof User && $this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('admin');
        }

        $city = $user instanceof User ? $user->getCity() : null;
        $response = $this->render('portal/index.html.twig', [
            'city' => $city,
            'forecasts' => null !== $city ? $forecasts->findLatestForCity($city) : [],
        ]);
        $response->headers->set('Cache-Control', 'private, no-store');

        return $response;
    }
}
