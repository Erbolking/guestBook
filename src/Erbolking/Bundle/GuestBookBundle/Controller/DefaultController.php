<?php

namespace Erbolking\Bundle\GuestBookBundle\Controller;

use Doctrine\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Erbolking\Bundle\GuestBookBundle\Entity\Entry;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class DefaultController extends Controller
{
    /**
     * @Route("/")
     * @Template()
     */
    public function indexAction()
    {
        $entry = $this->getDoctrine()->getRepository('ErbolkingGuestBookBundle:Entry')->findAll();
        return array(
            'entries' => $entry
        );
    }

    /**
     * @Route("/post")
     * @Method("POST")
     */
    public function addPostAction()
    {

    }
}
