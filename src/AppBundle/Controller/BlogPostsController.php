<?php

namespace AppBundle\Controller;

use AppBundle\Entity\BlogPost;
use AppBundle\Form\Type\BlogPostType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class BlogPostsController extends Controller
{
    /**
     * @Route("/", name="list")
     */
    public function listAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $queryBuilder = $em->getRepository('AppBundle:BlogPost')->createQueryBuilder('bp');

        if ($request->query->getAlnum('filter')) {
            $queryBuilder->where('bp.title LIKE :title')
                ->setParameter('title', '%' . $request->query->getAlnum('filter') . '%');
        }

        $query = $queryBuilder->getQuery();

        $paginator  = $this->get('knp_paginator');
        $blogPosts = $paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1)/*page number*/,
            $request->query->getInt('limit', 10)/*limit per page*/
        );

        return $this->render('BlogPosts/list.html.twig', [
            'blog_posts' => $blogPosts,
        ]);
    }

    /**
     * @param Request $request
     * @Route("/create", name="create")
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function createAction(Request $request)
    {
        $form = $this->createForm(BlogPostType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            /**
             * @var $blogPost BlogPost
             */
            $blogPost = $form->getData();

            $em = $this->getDoctrine()->getManager();
            $em->persist($blogPost);
            $em->flush();

            // for now
            return $this->redirectToRoute('edit', [
                'blogPost' => $blogPost->getId(),
            ]);

        }

        return $this->render('BlogPosts/create.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @param Request  $request
     * @param BlogPost $blogPost
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     *
     * @Route("/edit/{blogPost}", name="edit")
     */
    public function editAction(Request $request, BlogPost $blogPost)
    {
        $form = $this->createForm(BlogPostType::class, $blogPost);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em = $this->getDoctrine()->getManager();
            $em->flush();

            // for now
            return $this->redirectToRoute('edit', [
                'blogPost' => $blogPost->getId(),
            ]);

        }

        return $this->render('BlogPosts/edit.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @param Request  $request
     * @param BlogPost $blogPost
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     *
     * @Route("/delete/{blogPost}", name="delete")
     */
    public function deleteAction(Request $request, BlogPost $blogPost)
    {
        if ($blogPost === null) {
            return $this->redirectToRoute('list');
        }

        $em = $this->getDoctrine()->getManager();
        $em->remove($blogPost);
        $em->flush();

        return $this->redirectToRoute('list');
    }
}