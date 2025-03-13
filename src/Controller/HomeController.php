<?php

namespace App\Controller;

use App\Entity\Performance;
use App\Enum\Trial;
use App\Form\Filter\PerformanceFilterType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home_index')]
    public function index(
        EntityManagerInterface $em,
        Request $request,
    ): Response
    {
        $formSearch = $this->createForm(PerformanceFilterType::class)->handleRequest($request);
        if (null === ($params = $request->query->all($formName = $formSearch->getName())) || !isset($params['trial'])) {
            return $this->redirectToRoute('app_home_index', [$formName => ['trial' => Trial::D_42K->value]]);
        }

        return $this->render('pages/home/index.html.twig', [
            'formSearch' => $formSearch->createView(),
            'group' => $formSearch->get('group')->getData(),
            'metrics' => $em->getRepository(Performance::class)->getMetrics($request->query->all($formSearch->getName()) ?? []),
            'evolution' => $em->getRepository(Performance::class)->getMetrics($request->query->all($formSearch->getName()) ?? [], 'year'),
            'breakpoints' => '1' === $formSearch->get('breakpoints')->getData() ? $em->getRepository(Performance::class)->getBreakpoints($request->query->all($formSearch->getName()) ?? [], $formSearch->get('group')->getData()) : [],
        ]);
    }
}
