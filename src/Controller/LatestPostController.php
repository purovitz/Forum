<?php

namespace App\Controller;


use App\Entity\Post;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class LatestPostController extends AbstractController
{
    #[Route('/latest', name: 'latest_post')]
    public function index(PaginatorInterface $paginator, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $latestPost = $em->getRepository(Post::class)->findAllPublic();

        return $this->render('latest_photos/index.html.twig', [
            'pagination' => $paginator->paginate(
                $latestPost, $request->query->getInt('page', 1), 5)
        ]);
    }
}

