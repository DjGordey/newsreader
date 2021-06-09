<?php

namespace App\Controller\Admin;

use App\Entity\News;
use App\Entity\Source;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractDashboardController
{
    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        return parent::index();
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Newsreader');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linktoRoute('Home', 'fas fa-home', 'homepage');
        yield MenuItem::linkToCrud('Sources', 'fas fa-map-marker-alt', Source::class);
        yield MenuItem::linkToCrud('News', 'fas fa-comments', News::class);
    }
}
