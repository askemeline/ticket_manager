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
     * @Route("/", name="main")
     */
    public function index(ArticleRepository $repo,UserRepository $repuser)
        /*    public function index()*/
    {
        /*        $repo = $this->getDoctrine()->getRepository(Article::class);
                $repuser = $this->getDoctrine()->getRepository(User::class);*/


        if ($this->isGranted('ROLE_ADMIN')){
            $articles = $repo->findAll();
            $user = $repuser->findAll();
            dump($user);
            return $this->render('main/index.html.twig', [
                'controller_name' => 'MainController',
                'articles' => $articles,
                'user'=>$user,
                /* dump($articles),*/
                /*     dump($articles2)*/

            ]);
        }
        else{
            $user = $this -> getUser();
            $sess =  $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

            //TODO
            //pas tout a fais compris
            if(!$sess){
                $articles = $user->getArticles();
            }else{
                return $this->render('main/index.html.twig');
            }

            /* dump($articles);*/
            return $this->render('main/index.html.twig', [
                'controller_name' => 'MainController',
                'articles' => $articles,
                'user'=>$user,
                /* dump($articles),*/
                /*     dump($articles2)*/

            ]);

            /* dump($articles),*/
            /*     dump($articles2)*/


        }

    }
    /**
     * @Route("/{id}", name="post_show", requirements={"id":"\d+"})
     */
    public function  show(Article $article, Request $request,ObjectManager $manager){
        /*        $user = $repuser->findAll();*/
        /*     $sess =  $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
             if(!$sess){}*/
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

            /*   if($comment){
                   if($this -> isGranted('ROLE_USER') && $comment -> getUsers() -> getId() != $user -> getId() ){
                       return $this->redirectToRoute('show_article', array('id' => $id));
                   }else{
                       $em = $this->getDoctrine()->getEntityManager();
                       $em->remove($comment);
                       $em->flush();
                       return $this->redirectToRoute('show_article', array('id' => $article_id));
                   }
               }*/

        }


        return $this->render('main/show.html.twig',[
            'article'=>$article,
            'commentForm'=>$form->createView(),
            /*    'user'=>$user,*/
            dump($user),
            dump($article)

            /*dump($article)*/
            /*'user'=>$user,*/
        ]);
    }

    /**
     * @Route("/new", name="post_new")
     *
     * @Route("edit/{id}", name="edit_post")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function form(Article $article=null,Request $request, ObjectManager $manager){
        $user = $this -> getUser();
        /*$user = $this->getUser();
        $article =  $this->getDoctrine()->getRepository(Article::class) -> findById($id);*/

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
            /* throw $this->createNotFoundException('The product does not exist');*/
        }

    }
    /**
     *
     * @Security("has_role('ROLE_ADMIN')")
     * @Route("delete/{id}", name="delete_article")
     */
    public function deleteTicket(Article $article) {
        $manager = $this->getDoctrine()->getManager();
        /*
                foreach ($article->getComments() as $comment) {
                    $manager->remove($comment);
                }*/
        $manager->remove($article);
        $manager->flush();


        return $this->redirectToRoute('main',['id' => $article->getId()]);
    }



}