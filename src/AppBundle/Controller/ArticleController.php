<?php
namespace AppBundle\Controller;

use AppBundle\Entity\Article;
use AppBundle\Entity\Comment;
use AppBundle\Form\ArticleType;
use AppBundle\Form\CommentType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class ArticleController
 * @package AppBundle\Controller
 * @Route("/article")
 */
class ArticleController extends Controller
{
    /**
     * @Route("/", name="articleList")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request)
    {
        $articles = $this->getDoctrine()
            ->getRepository(Article::class)
            ->findAll();
        $articleDeleteForm = $this->createFormBuilder()
            ->add('delete', SubmitType::class, ['label' => 'Удалить'])
            ->setMethod('DELETE')
            ->getForm();
        return $this->render("@App/article/index.html.twig",[
            'articles'=>$articles,
            'articleDeleteForm' => $articleDeleteForm
        ]);
    }

    /**
     * @Route("/new", name="articleNew")
     * @Method({"GET","POST","HEAD"})
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function newAction(Request $request)
    {
        $article = new Article();
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $article->setUser($this->getUser());
            $em = $this->getDoctrine()->getManager();
            $em->persist($article);
            $em->flush();

            return $this->redirectToRoute("articleList");
        }

        return $this->render('@App/article/new.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/{id}/show", requirements={"id": "\d+"},name="articleShow")
     * @Method({"GET","POST","HEAD"})
     * @param Request $request
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showAction(Request $request, int $id)
    {
        $article = $this->getDoctrine()
            ->getRepository(Article::class)
            ->find($id);

        $commentDeleteForm = $this->createFormBuilder()
            ->add('delete', SubmitType::class, ['label' => 'Удалить'])
            ->setMethod('DELETE')
            ->getForm();

        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $comment->setArticle($article);
            $comment->setUser($this->getUser());
            $em = $this->getDoctrine()->getManager();
            $em->persist($comment);
            $em->flush();

            return $this->redirectToRoute("articleShow",['id'=>$id]);
        }
        return $this->render('@App/article/show.html.twig',[
            'article'=>$article,
            'formComment' => $form->createView(),
            'commentDeleteForm' => $commentDeleteForm
        ]);
    }

    /**
     * @Route("/{id}/delete", requirements={"id": "\d+"},name="articleDelete")
     * @Method({"DELETE"})
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteAction(int $id)
    {
        $article = $this->getDoctrine()
            ->getRepository(Article::class)
            ->find($id);

        if ($article->getUser()->getId() == $this->getUser()->getId()){

            $repositoryComment = $this->getDoctrine()->getRepository(Comment::class);
            $comments = $repositoryComment->findBy(
                ['article' => $article]
            );
            $em = $this->getDoctrine()->getManager();
            foreach ($comments as $comment){
                $em->remove($comment);
            }
            $em->remove($article);
            $em->flush();
        }

        return $this->redirectToRoute("articleList");
    }

    /**
     * @Route("/comment/{id}/{id_article}/delete", requirements={"id": "\d+"},name="commentDelete")
     * @Method({"DELETE"})
     * @param int $id
     * @param int $id_article
     * @return \Symfony\Component\HttpFoundation\Response
     * @internal param int $idArticle
     */
    public function deleteCommentAction(int $id, int $id_article)
    {
        $comment = $this->getDoctrine()
            ->getRepository(Comment::class)
            ->find($id);

        if ($comment->getUser()->getId() == $this->getUser()->getId()){

            $em = $this->getDoctrine()->getManager();
            $em->remove($comment);
            $em->flush();
        }

        return $this->redirectToRoute("articleShow",['id'=>$id_article]);
    }
}