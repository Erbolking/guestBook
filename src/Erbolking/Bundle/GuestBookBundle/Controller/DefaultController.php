<?php

namespace Erbolking\Bundle\GuestBookBundle\Controller;

use Doctrine\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Erbolking\Bundle\GuestBookBundle\Entity\Entry;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use \DateTime;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="list")
     * @Template()
     */
    public function indexAction()
    {
        $entry = $this->getDoctrine()->getRepository('ErbolkingGuestBookBundle:Entry')->findBy(array('parent' => null), array('publicDate' => 'DESC'));
        $form = $this->getForm($entry);
        return array(
            'entries' => $entry,
            'form' => $form->createView(),
        );
    }

    /**
     * @Route("/post", name="post_entry")
     * @Method("POST")
     */
    public function addPostAction(Request $request)
    {
        //set extra values
        $entry = new Entry();
        $entry->setActive('1');
        $entry->setPublicDate(new DateTime());
        $entry->setIpAddress($request->getClientIp());
        $formPost = $request->get('form');
        if (isset($formPost['parent']) && $formPost['parent']) {
            $parent = $this->getDoctrine()->getRepository('ErbolkingGuestBookBundle:Entry')->find($formPost['parent']);
            if ($parent) {
                $entry->setParent($parent);
            } else {
                $request->query->remove('parent');
            }
        }

        $form = $this->getForm($entry);
        $form->handleRequest($request);
        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entry);
            $em->flush();
            if ($request->isXmlHttpRequest()) {
                return new JsonResponse('200');
            } else {
                return $this->redirect($this->generateUrl('list'));
            }
        } else {
            $errors = array();
            foreach ($form->getErrors() as $error) {
                $errors[$form->getName()][] = $error->getMessage();
            }
            return new JsonResponse($errors);
        }
    }

    private function getForm($entry) {
        $form = $this->createFormBuilder($entry)
            ->add('name', 'text', array(
                    'label' => 'Your name',
                    'label_attr' => array('class' => 'inline'),
                    'attr' => array('pattern' => '[a-zA-Z]+( [a-zA-Z]+)?', 'placeholder' => 'Albert Einstein'),
                )
            )
            ->add('email', 'email', array(
                    'label' => 'Your email',
                    'label_attr' => array('class' => 'inline'))
            )
            ->add('message', 'textarea')
            ->add('parent', 'hidden',
                array(
                    'data_class' => 'Erbolking\Bundle\GuestBookBundle\Entity\Entry',
                ))
            ->add('post', 'submit', array('label' => 'POST', 'attr' => array('class' => 'radius button')))
            ->getForm();
        return $form;
    }
}
