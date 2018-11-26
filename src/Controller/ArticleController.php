<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Form\CommentType;
use App\Service\FileUploader;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\ArticleType;
use App\Entity\Article;

/**
 * Class ArticleController
 * @package App\Controller
 *
 */
class ArticleController extends AbstractController
{
    /**
     * @Route("dashboard/new", name="new_article")
     * @Template("article/new.html.twig")
     */
    public function new(Request $request, FileUploader $fileUploader)
    {
        $article = new Article();
        $article->setUser($this->getUser());


        $form = $this->createForm(ArticleType::class, $article);
        $em = $this->getDoctrine()->getManager();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            if ($article->getImage()) {
                $image = $fileUploader->upload($form->get('image')->getData());
                $article->setImage($image);
            }
            $em->persist($article);
            $em->flush();

            return $this->redirectToRoute('dashboard');
        }

        return [ 'form' => $form->createView() ];
    }

    /**
     * @Route("/article/{id}", name="get_article")
     * @Template("article/solo.html.twig")
     */
    public function getArticle($id, Request $request)
    {
        $article = $this->getDoctrine()->getRepository(Article::class)->find($id);

        $comment = new Comment();
        $comment->setUser($this->getUser());

        $comment->setArticle($article);
        $commentForm = $this->createForm(CommentType::class, $comment);
        $em = $this->getDoctrine()->getManager();

        $commentForm->handleRequest($request);

        if ($commentForm->isSubmitted()) {
            $em->persist($comment);
            $em->flush();

            return $this->redirect($request->getUri());
        }
        return $article ? ['article' => $article, 'form' => $commentForm->createView() ] : $this->redirectToRoute('homepage');
    }
}
