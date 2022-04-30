<?php

namespace App\Controller;


use App\Entity\Comment;
use App\Entity\Post;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class MostLikedController extends AbstractController
{
    #[Route('/most-liked-posts', name: 'most_liked_post')]
    public function index(PaginatorInterface $paginator, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $latestPost = $em->getRepository(Post::class)->findMostLiked();

        return $this->render('mostLiked/most_liked.html.twig', [
            'pagination' => $paginator->paginate(
                $latestPost, $request->query->getInt('page', 1), 5),
        ]);
    }

    #[Route('/most-unliked-posts', name: 'most_unliked_post')]
    public function unliked(PaginatorInterface $paginator, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $latestPost = $em->getRepository(Post::class)->findMostUnliked();

        return $this->render('mostLiked/most_unliked.html.twig', [
            'pagination' => $paginator->paginate(
                $latestPost, $request->query->getInt('page', 1), 5),
        ]);
    }
}
