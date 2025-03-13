<?php

namespace App\Controller;

use App\Enum\Trial;
use App\Ffa\GetBreakpoints;
use App\Ffa\GetMetrics;
use App\Form\Filter\PerformanceFilterType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home_index')]
    public function index(
        GetMetrics $getMetrics,
        GetBreakpoints $getBreakpoints,
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
            'metrics' => $getMetrics($request->query->all($formSearch->getName()) ?? []),
            'evolution' => $getMetrics($request->query->all($formSearch->getName()) ?? [], 'year'),
            'breakpoints' => 1 === (int)$formSearch->get('breakpoints')->getData() ? $getBreakpoints($request->query->all($formSearch->getName()) ?? []) : [],
        ]);
    }
}
