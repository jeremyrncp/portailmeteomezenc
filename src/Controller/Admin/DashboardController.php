<?php

namespace App\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\{Dashboard, MenuItem};
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
#[IsGranted('ROLE_ADMIN')]
class DashboardController extends AbstractDashboardController
{
    public function index(): Response
    {
        return $this->redirect($this->container->get(AdminUrlGenerator::class)->setController(CityCrudController::class)->generateUrl());
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()->setTitle('Météo Mézenc — Administration');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkTo(CityCrudController::class, 'Villes', 'fa fa-city');
        yield MenuItem::linkTo(ForecastCrudController::class, 'Prévisions', 'fa fa-cloud-sun');
        yield MenuItem::linkTo(UserCrudController::class, 'Utilisateurs', 'fa fa-users');
        yield MenuItem::linkTo(WeatherStationCrudController::class, 'Stations météo', 'fa fa-users');
        yield MenuItem::linkTo(WeatherDataCrudController::class, 'Données météo', 'fa fa-users');
        yield MenuItem::linkToLogout('Déconnexion', 'fa fa-sign-out');
    }
}
