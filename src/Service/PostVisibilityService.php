<?php

namespace App\Service;

use App\Entity\Post;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;

class PostVisibilityService
{
    private $photoRepository;
    private $security;
    private $entityManager;

    public function __construct(PostRepository $postRepository, Security $security, EntityManagerInterface $entityManager)
    {
        $this->photoRepository = $postRepository;
        $this->security = $security;
        $this->entityManager = $entityManager;
    }

    public function makeVisible(int $id, bool $visibility)
    {
        $em = $this->entityManager;
        $post = $this->photoRepository->find($id);

        if ($this->isPostBelongToCurrentUser($post)) {
            $post->setIsPublic($visibility);
            $em->persist($post);
            $em->flush();
            return true;
        } else {
            return false;
        }
    }

    private function isPostBelongToCurrentUser(Post $post)
    {
        if ($post->getUser() === $this->security->getUser()) {
            return true;
        } else {
            return false;
        }
    }
}
