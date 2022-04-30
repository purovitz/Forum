<?php

namespace App\Controller;


use App\Entity\Comment;
use App\Entity\Post;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class MostCommentsController extends AbstractController
{
    #[Route('/mostcomments', name: 'most_comments_post')]
    public function index(PaginatorInterface $paginator, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $latestPost = $em->getRepository(Post::class)->findMostPopularr();

        return $this->render('mostComments/most_comments.html.twig', [
            'pagination' => $paginator->paginate(
                $latestPost, $request->query->getInt('page', 1), 5),
        ]);
    }
}
