<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Post;
use App\Form\CommentType;
use App\Form\PostType;
use App\Form\UploadPostType;
use App\Repository\CommentRepository;
use App\Repository\PostRepository;
use App\Service\PostVisibilityService;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class PostController extends AbstractController
{

    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }


    /**
     * @param Request $request
     * @return Response
     */
    #[Route('/dodaj_post', name: 'nowy_post')]
    public function newPost(Request $request, SluggerInterface $slugger): Response
    {
        $form = $this->createForm(UploadPostType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid())
        {
            $em = $this->getDoctrine()->getManager();
            if ($this->getUser()) {
                /** @var  UploadedFile $pictureFileName */

                $pictureFileName = $form->get('image')->getData();
                if ($pictureFileName) {
                    try {
                        $originalFileName = pathinfo($pictureFileName->getClientOriginalName(), PATHINFO_FILENAME);
                        $safeFileName = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', $originalFileName);
                        $newFileName = $safeFileName.'-'.uniqid().'.'.$pictureFileName->guessExtension();
                        $pictureFileName->move('images/hosting', $newFileName);

                        $entityPost = new Post();
                        $entityPost->setTitle($form->get('title')->getData());
                        $entityPost->setImage($newFileName);
                        $entityPost->setContent($form->get('content')->getData());
                        $entityPost->setSlug($slugger->slug($form->get('title')->getData(), '-', 'pl')->lower());
                        $entityPost->setUploadedAt(new \DateTime());
                        $entityPost->setUser($this->getUser());

                        $em->persist($entityPost);
                        $em->flush();
                        $this->addFlash('success', 'Dodano nowy post!');
                    } catch(\Exception $e) {
                        $this->addFlash('error', 'Błąd!');
                    }

                    return $this->redirectToRoute('nowy_post');
                }


            }
        }
        return $this->render('newPost/addPost.html.twig', [
            'form' => $form->createView(),
        ]);
    }




    /**
     * @Route("/post/edit/{id}", name="post.edit")
     * @param Request $request
     * @return Response
     */
    public function edit(PostRepository $postRepository, Request $request, SluggerInterface $slugger, int $id)
    {
        $em = $this->getDoctrine()->getManager();
        $entityPost = $em->getRepository(Post::class)->find($id);
        $form = $this->createForm(UploadPostType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($this->getUser()) {
                /** @var  UploadedFile $pictureFileName */

                $pictureFileName = $form->get('image')->getData();
                if ($pictureFileName) {
                    try {
                        $originalFileName = pathinfo($pictureFileName->getClientOriginalName(), PATHINFO_FILENAME);
                        $safeFileName = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', $originalFileName);
                        $newFileName = $safeFileName . '-' . uniqid() . '.' . $pictureFileName->guessExtension();
                        $pictureFileName->move('images/hosting', $newFileName);


                        $entityPost->setTitle($form->get('title')->getData());
                        $entityPost->setImage($newFileName);
                        $entityPost->setContent($form->get('content')->getData());
                        $entityPost->setSlug($slugger->slug($form->get('title')->getData(), '-', 'pl')->lower());
                        $entityPost->setUploadedAt(new \DateTime());
                        $entityPost->setUser($this->getUser());

                        $em->persist($entityPost);
                        $em->flush();

                        $this->addFlash('success', 'Post has been updated successfully');

                    } catch (\Exception $e) {
                        $this->addFlash('error', 'Błąd!');
                    }

                    return $this->redirectToRoute('post.edit', ['id' => $entityPost->getId()]);
                }


            }
        }
        return $this->render('dashboard/edit.html.twig', [
            'form' => $form->createView(),
            'post' => $entityPost
            ]);
    }



    /**
     * @param int $id
     * @IsGranted("ROLE_USER")
     * @return RedirectResponse
     */
    #[Route('/my/post/remove/{id}', name: 'my_post_remove')]
    public function postRemove(int $id)
    {
        $em = $this->getDoctrine()->getManager();
        $myPost = $em->getRepository(Post::class)->find($id);


        if ($this->getUser() == $myPost->getUser())
        {
            $fileManager = new Filesystem();
            $fileManager->remove('images/hosting'.$myPost->getImage());
            if ($fileManager->exists('images/hosting'.$myPost->getImage())) {
                $this->addFlash('error', "Nie udało się usunąć zdjęcia");
            } else {
                $em->remove($myPost);
                $em->flush();
                $this->addFlash('succes', "Usunięto zdjęcie");
            }
        } else {
            $this->addFlash('error', 'Nie usunięto postu, ponieważ nie jesteś jego autorem');
        }

        return $this->redirectToRoute('my_posts');
    }

    #[Route('/my/posts', name: 'my_posts')]
    public function myPosts(PaginatorInterface $paginator, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $myPosts = $em->getRepository(Post::class)->findBy(['user' => $this->getUser()]);
        return $this->render('my_posts/myPosts.html.twig', [
            'pagination' => $paginator->paginate(
                $myPosts, $request->query->getInt('page', 1), 5)
        ]);
    }

    #[Route('/my/posts/public', name: 'my_posts_public')]
    public function myPostsPublic(PaginatorInterface $paginator, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $myPosts = $em->getRepository(Post::class)->findBy(['user' => $this->getUser(), 'is_public' => 1]);
        return $this->render('my_posts/myPostsPublic.html.twig', [
            'pagination' => $paginator->paginate(
                $myPosts, $request->query->getInt('page', 1), 5)
        ]);
    }

    #[Route('/my/posts/private', name: 'my_posts_private')]
    public function myPostsPrivate(PaginatorInterface $paginator, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $myPosts = $em->getRepository(Post::class)->findBy(['user' => $this->getUser(), 'is_public' => 0]);
        return $this->render('my_posts/myPostsPrivate.html.twig', [
            'pagination' => $paginator->paginate(
                $myPosts, $request->query->getInt('page', 1), 5)
        ]);
    }





    /**
     * @Route("/posts/{slug}", name="blog_post")
     *
     * NOTE: The $post controller argument is automatically injected by Symfony
     * after performing a database query looking for a Post with the 'slug'
     * value given in the route.
     * See https://symfony.com/doc/current/bundles/SensioFrameworkExtraBundle/annotations/converters.html
     */
    public function postShow(Post $post, CommentRepository $commentRepository, Request $request, PostRepository $postRepository, string $slug): Response
    {
        // Symfony's 'dump()' function is an improved version of PHP's 'var_dump()' but
        // it's not available in the 'prod' environment to prevent leaking sensitive information.
        // It can be used both in PHP files and Twig templates, but it requires to
        // have enabled the DebugBundle. Uncomment the following line to see it in action:
        //
        // dump($post, $this->getUser(), new \DateTime());

        $post = $postRepository->findOneBySlug($slug);

        $comments = $post->getComments();
        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $comment->setPost($post);
            $comment->setCreatedAt(new \DateTime());
            $comment->setAuthor($form->get('author')->getData());
            $comment->setContent($form->get('content')->getData());
            $post->setComment_Count($post->getComment_Count() + 1);

            $em = $this->getDoctrine()->getManager();
            $em->persist($comment);
            $em->flush();

            $this->addFlash('success', 'Comment has been created successfully');

            return $this->redirectToRoute('blog_post', ['slug' => $post->getSlug()]);
        }


        return $this->render('blog/post_show.html.twig', [
            'post' => $post,
            'comments' => $comments,
            'form' => $form->createView()
        ]);
    }


    /**
     * @Route("/questions/{slug}/vote", name="post_vote", methods="POST")
     */
    public function questionVote(Post $post, Request $request, EntityManagerInterface $entityManager)
    {
        $direction = $request->request->get('direction');

        if ($direction === 'up') {
            $post->upVote();
        } elseif ($direction === 'down') {
            $post->downVote();
        }

        $entityManager->flush();

        return $this->redirectToRoute('blog_post', [
            'slug' => $post->getSlug()
        ]);
    }

    /**
     * @Route("/my/posts/set_visibility/{id}/{visibility}", name="my_post_set_visibility")
     * @param PostVisibilityService $postVisibilityService
     * @param int $id
     * @param bool $visibility
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function myPostChangeVisibility(PostVisibilityService $postVisibilityService, int $id, bool $visibility)
    {
        $messages = [
            '1' => 'publiczne',
            '0' => 'prywatne'
        ];
        if ($postVisibilityService->makeVisible($id, $visibility)) {
            $this->addFlash('success', 'Ustawiono jako '.$messages[$visibility].'.');
        } else {
            $this->addFlash('error', 'Wystąpił problem przy ustawianiu jako '.$messages[$visibility].'.');
        }

        return $this->redirectToRoute('my_posts');
    }


}


