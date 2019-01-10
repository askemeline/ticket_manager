<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Comment;
use App\Entity\User;
use App\Form\CommentType;
use App\Repository\ArticleRepository;
use App\Repository\UserRepository;
use Doctrine\Common\Persistence\ObjectManager;
/*use Doctrine\DBAL\Types\TextType;*/
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;


class MainController extends AbstractController
{

    /**
     *
     * @Security("has_role('ROLE_ADMIN')")
     * @Route("share/{id}/{user}", name="share", requirements={"id":"\d+"})
     */
    public function share(Article $article, User $user) {

        $manager = $this->getDoctrine()->getManager();
        $user=$this->getUser();
       /* dump($user);*/
        $article =$user->getId();
        $collabore = $user->setUser();
        dump($collabore);
        $id=$user->getId();
        $manager->persist($collabore);
        $manager->flush();
        return $this->redirectToRoute('main',[

                'user'=>$id,
               'id' =>$article,
               ]
        );
    }

    /**
     * @Route("/", name="main")
     */
    public function index(ArticleRepository $repo,UserRepository $repuser)
    {
        if ($this->isGranted('ROLE_ADMIN')){
            $articles = $repo->findAll();
            $user = $repuser->findAll();
            dump($user);
            return $this->render('main/index.html.twig', [
                'articles' => $articles,
                'user'=>$user,
            ]);
        }
        else{
            $user = $this -> getUser();
            $sess =  $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
            if(!$sess){
                $articles = $user->getArticles();
            }else{
                return $this->render('main/index.html.twig');
            }

            return $this->render('main/index.html.twig', [
                'controller_name' => 'MainController',
                'articles' => $articles,
                'user'=>$user,
            ]);
        }
    }
    /**
     * @Route("/{id}", name="post_show", requirements={"id":"\d+"})
     *
     */
    public function  show(Article $article, Request $request,ObjectManager $manager){
        $comment = new Comment();
        $user = $this -> getUser();
        $form =$this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);
        if($form->isSubmitted()&& $form->isValid()){
            $comment->setCreatedAt(new \DateTime())
                ->setArticle($article)
                ->setUser($user);
            dump($user);
            $manager->persist($comment);
            $manager->flush();
            dump($comment);
            return $this->redirectToRoute('post_show',['id'=>$article->getId()]);
        }

        return $this->render('main/show.html.twig',[
            'article'=>$article,
            'commentForm'=>$form->createView(),
        ]);
    }

    /**
     * @Route("/new", name="post_new")
     *
     * @Route("edit/{id}", name="edit_post")
     */
    public function form(Article $article=null,Request $request, ObjectManager $manager){
        $user = $this -> getUser();

        if(!$article){
            $article = new Article();
        }
        $form=$this->createFormBuilder($article)
            ->add('title')
            ->add('content')
            ->getForm();
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            if(!$article->getId()){
                $article->setCreatedAt(new \DateTime());
                $article -> setUser($user);
            }
            $manager->persist($article);
            $manager->flush();
            return $this->redirectToRoute('post_show',['id' => $article->getId()]);
        }
        if ($this->isGranted('ROLE_USER')){
            return $this->render('main/create.html.twig',[
                'formPost'=>$form->createView(),
                'editMode'=>$article->getId() !==null
            ]);
        }else{
            return $this->render('main/create.html.twig',[
                'formPost'=>$form->createView()
            ]);
        }
    }
    /**
     *
     * @Security("has_role('ROLE_ADMIN')")
     * @Route("delete/{id}", name="delete_article")
     */
    public function deleteTicket(Article $article) {
        $manager = $this->getDoctrine()->getManager();
        $manager->remove($article);
        $manager->flush();
        return $this->redirectToRoute('main',['id' => $article->getId()]);
    }
    /**
     *
     * @Security("has_role('ROLE_ADMIN')")
     * @Route("deleteComment/{id}", name="delete_comment")
     */
    public function deleteComment(Comment $comment) {
        $manager = $this->getDoctrine()->getManager();
        $manager->remove($comment);
        $manager->flush();
        return $this->redirectToRoute('main',['id' => $comment->getId()]);
    }
}