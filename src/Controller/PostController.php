<?php

namespace App\Controller;

use App\Entity\Post;
use App\Entity\Interaction;
use App\Form\PostType;
use App\Form\InteractionType;
use App\Repository\InteractionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

// #[Route('/post')]
class PostController extends AbstractController
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    #[Route('/', name: 'get_posts')]
    public function index(Request $request)
    {
        $posts = $this->em->getRepository(Post::class)->findAllPost($request);

        return $this->render('post/index.html.twig',compact('posts'));
    }

    #[Route('/post/details/{id}', name: 'postDetails', methods: ['GET', 'POST'])]
    public function show(Post $post, Request $request, InteractionRepository $interaction): Response
    {
        $comment_form = $this->createForm(InteractionType::class, new Interaction());
        $comment_form->handleRequest($request);

        if ($comment_form->isSubmitted() && $comment_form->isValid()) {
            $this->denyAccessUnlessGranted('ROLE_USER');
            $comment = $comment_form->getData();
            $comment->setPost($post);
            $comment->setUser($this->getUser());
            $interaction->save($comment, true);

            $this->addFlash('success','Your comment have been created');
            return $this->redirectToRoute('postDetails',['id' => $post->getId()]);
        }

        return $this->render(
            'post/show.html.twig',
            [
                'comment_form' => $comment_form->createView(),
                'post'         => $post
            ]
        );
        
        //return $this->render('post/show.html.twig',compact('post'));
    }


    #[Route('/post/edit/{id}', name: 'postEdit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_SUPER_ADMIN', message: 'You are not allowed to access the admin dashboard.')] //The #[IsGranted()] attribute was introduced in Symfony 6.2.
    public function edit(Request $request,$id, SluggerInterface $slugger): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $post = $this->em->getRepository(Post::class)->findOneBy(['id' => $id, 'user' => $this->getUser()]);
        
        $post_form = $this->createForm(PostType::class, $post);
        $post_form->handleRequest($request);

        if ($post_form->isSubmitted() && $post_form->isValid()) {

            $brochureFile = $post_form->get('file')->getData();

           if ($brochureFile) {
               $originalFilename = pathinfo($brochureFile->getClientOriginalName(), PATHINFO_FILENAME);
               // this is needed to safely include the file name as part of the URL
               $safeFilename = $slugger->slug($originalFilename);
               $newFilename = $safeFilename.'-'.uniqid().'.'.$brochureFile->guessExtension();

               try {
                   $brochureFile->move(
                       $this->getParameter('files_directory'),
                       $newFilename
                   );
               } catch (FileException $e) {
                   throw new \Exception("Error Processing Request ".$e->getMessage());
               }

               $post->setFile('uploads/files/'.$newFilename);
           }
           
           $post->setUrl($slugger->slug($post_form->get('title')->getData()));
           $post->setUser($this->getUser());

           $this->addFlash('success','Post has been edited');

           $this->em->persist($post);
           $this->em->flush();
           return $this->redirectToRoute('insert_post');
       }


        return $this->render(
            'post/form.html.twig',
            [
                'post_form' => $post_form->createView(),
            ]
        );
    }

    #[Route('/posts', name: 'insert_post', methods: ['GET', 'POST'])]
    public function insert(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        // $this->denyAccessUnlessGranted(new Expression(
        //     'is_granted("ROLE_ADMIN") or is_granted("ROLE_USER")'
        // ));

        $posts = $this->em->getRepository(Post::class)->findPostByUser($request, ['user' => $this->getUser()]);

        return $this->render('post/index.html.twig', ['posts' => $posts]);
    }

    #[Route('/posts/create', name: 'create_post', methods: ['GET', 'POST']), IsGranted('ROLE_USER')]
    public function create(Request $request, SluggerInterface $slugger)
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $post = new Post();
        $post_form = $this->createForm(PostType::class, $post);
        $post_form->handleRequest($request);

        if ($post_form->isSubmitted() && $post_form->isValid()) {

             /** @var UploadedFile $brochureFile */
             $brochureFile = $post_form->get('file')->getData();

             // this condition is needed because the 'brochure' field is not required
            // so the PDF file must be processed only when a file is uploaded
            if ($brochureFile) {
                $originalFilename = pathinfo($brochureFile->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$brochureFile->guessExtension();

                // Move the file to the directory where brochures are stored
                try {
                    $brochureFile->move(
                        $this->getParameter('files_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    throw new \Exception("Error Processing Request ".$e->getMessage());
                }

                $post->setFile('uploads/files/'.$newFilename);
            }
            
            $post->setUrl($slugger->slug($post_form->get('title')->getData()));
            $post->setUser($this->getUser());

            $this->addFlash('success','Post has been add');

            $this->em->persist($post);
            $this->em->flush();
            return $this->redirectToRoute('insert_post');
        }

        return $this->render(
            'post/form.html.twig',
            [
                'post_form' => $post_form->createView(),
            ]
        );
    }

    #[Route('/post/{id}/comment', name: 'postComment', methods: ['GET', 'POST'])]
    public function addComment(Post $post, Request $request, InteractionRepository $interaction): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        
        $comment_form = $this->createForm(InteractionType::class, new Interaction());
        $comment_form->handleRequest($request);

        if ($comment_form->isSubmitted() && $comment_form->isValid()) {
            $comment = $comment_form->getData();
            $comment->setPost($post);
            $comment->setUser($this->getUser());
            $interaction->add($comment, true);

           $this->addFlash('success','Your comment have been created');

          
           return $this->redirectToRoute('postDetails', ['post' => $post->getId()]);
       }

        return $this->render(
            'post/comment_form.html.twig',
            [
                'post_form' => $comment_form->createView(),
                'post'      => $post
            ]
        );
    }

}
