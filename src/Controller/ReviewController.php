<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Review;
use App\Form\ReviewType;
use App\Repository\CompanyRepository;
use App\Repository\ReviewRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class ReviewController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ReviewRepository $reviewRepository,
        private readonly CompanyRepository $companyRepository,
        private readonly TranslatorInterface $translator,
    ) {
    }

    #[Route('/', name: 'app_review_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $limit = 10;
        $page = max(1, (int) $request->query->get('page', 1));
        $total = $this->reviewRepository->count([]);
        $lastPage = max(1, (int) ceil($total / $limit));
        $page = min($page, $lastPage);

        $reviews = $this->reviewRepository->findLatest($limit, ($page - 1) * $limit);

        return $this->render('review/index.html.twig', [
            'reviews' => $reviews,
            'pagination' => [
                'page' => $page,
                'lastPage' => $lastPage,
                'total' => $total,
            ],
        ]);
    }

    #[Route('/review/new', name: 'app_review_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $review = new Review();
        $form = $this->createForm(ReviewType::class, $review);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $companyName = trim((string) $form->get('companyName')->getData());
            $company = $this->companyRepository->findOrCreateByName($companyName);
            $review->setCompany($company);

            if ($this->reviewRepository->existsByCompanyAndAuthorEmail($company, $review->getAuthorEmail())) {
                $form->get('authorEmail')->addError(new FormError($this->translator->trans('review.form.duplicate')));
            } else {
                $this->entityManager->persist($review);
                $this->entityManager->flush();

                $this->addFlash('success', $this->translator->trans('review.flash.thanks'));

                return $this->redirectToRoute('app_review_index');
            }
        }

        return $this->render('review/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/review/{id}', name: 'app_review_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(Review $review): Response
    {
        return $this->render('review/show.html.twig', [
            'review' => $review,
        ]);
    }

    #[Route('/companies', name: 'app_companies', methods: ['GET'])]
    public function companies(): Response
    {
        $statistics = $this->companyRepository->findWithStats();

        return $this->render('companies.html.twig', [
            'statistics' => $statistics,
        ]);
    }

    #[Route('/companies/search', name: 'app_companies_search', methods: ['GET'])]
    public function search(Request $request): JsonResponse
    {
        $query = trim((string) $request->query->get('q', ''));

        return new JsonResponse('' === $query ? [] : $this->companyRepository->findNamesBySearch($query));
    }
}
