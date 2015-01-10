<?php

namespace Erbolking\Bundle\GuestBookBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\ORM\QueryBuilder;
use Erbolking\Bundle\GuestBookBundle\Entity\Entry;
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
        $repository = $this->getDoctrine()->getRepository('ErbolkingGuestBookBundle:Entry');
        /* @var $repository \Doctrine\ORM\EntityRepository */
        $queryBuilder = $repository->createQueryBuilder('e');

        $currentPage = $this->getRequest()->get('page', 1);
        $entriesPerPage = $this->getRequest()->get('limit', 10);
        if (!$entriesPerPage || !is_numeric($entriesPerPage) || ($entriesPerPage % 10) !== 0) {
            $entriesPerPage = 10;
        }
        if (!is_numeric($currentPage) || !$currentPage) {
            $currentPage = 1;
        }
        $paginator = $this->getDoctrinePaginator($queryBuilder, $entriesPerPage, $currentPage);
        $pagination = array(
            'pagesCount' => (int) ceil(count($paginator) / $entriesPerPage),
            'currentPage' => $currentPage,
            'perPage' => $entriesPerPage,
        );
        $form = $this->getForm(new Entry());

        return array(
            'entries' => $paginator,
            'pagination' => $pagination,
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

    private function getDoctrinePaginator(QueryBuilder $queryBuilder, $limit = 10, $page)
    {
        $queryBuilder->where('e.parent is NULL')
            ->setFirstResult(($page - 1) * $limit)
            ->orderBy('e.publicDate', 'DESC')
            ->setMaxResults($limit);

        return new Paginator($queryBuilder);
    }
}
