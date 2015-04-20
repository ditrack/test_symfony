<?php
/**
 * Created by PhpStorm.
 * User: yury
 * Date: 18.04.15
 * Time: 18:41
 */

namespace Blogger\BlogBundle\Controller;

use Blogger\BlogBundle\Entity\Enquiry;
use Blogger\BlogBundle\Form\EnquiryType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class PageController extends Controller
{
    public function indexAction()
    {
        $em = $this->getDoctrine()
            ->getEntityManager();

        $posts = $em->getRepository('BlogBundle:Post')
            ->getLatestPosts();

        return $this->render('BlogBundle:Page:index.html.twig', array(
            'blogs' => $posts
        ));
    }

    public function aboutAction()
    {
        return $this->render('BlogBundle:Page:about.html.twig');
    }

    public function contactAction()
    {
        $enquiry = new Enquiry();
        $form = $this->createForm(new EnquiryType(), $enquiry);

        $request = $this->getRequest();
        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $message = \Swift_Message::newInstance()
                    ->setSubject('Contact enquiry from symblog')
                    ->setFrom('enquiries@symblog.co.uk')
                    ->setTo($this->container->getParameter('blogger_blog.emails.contact_email'))
                    ->setBody($this->renderView('BlogBundle:Page:contactEmail.txt.twig', array('enquiry' => $enquiry)));
                $this->get('mailer')->send($message);

                $this->get('session')->setFlash('blogger-notice', 'Your contact enquiry was successfully sent. Thank you!');

                // Redirect - This is important to prevent users re-posting
                // the form if they refresh the page
                return $this->redirect($this->generateUrl('blog_contact'));
            }
        }

        return $this->render('BlogBundle:Page:contact.html.twig', array(
            'form' => $form->createView()
        ));
    }

    public function sidebarAction()
    {
        $em = $this->getDoctrine()
            ->getEntityManager();

        $tags = $em->getRepository('BlogBundle:Post')
            ->getTags();

        $tagWeights = $em->getRepository('BlogBundle:Post')
            ->getTagWeights($tags);

        $commentLimit   = $this->container
            ->getParameter('blog.comments.latest_comment_limit');
        $latestComments = $em->getRepository('BlogBundle:Comment')
            ->getLatestComments($commentLimit);

        return $this->render('BlogBundle:Page:sidebar.html.twig', array(
            'latestComments'    => $latestComments,
            'tags'              => $tagWeights
        ));
    }
} 