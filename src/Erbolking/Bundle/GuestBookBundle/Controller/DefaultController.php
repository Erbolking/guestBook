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

        $currentPage = abs($this->getRequest()->get('page', 1));
        $entriesPerPage = abs($this->getRequest()->get('limit', 10));
        if (!$entriesPerPage || !is_numeric($entriesPerPage) || ($entriesPerPage % 10) !== 0) {
            $entriesPerPage = 10;
        }
        if (!is_numeric($currentPage) || !$currentPage) {
            $currentPage = 1;
        }
        $paginator = $this->getDoctrinePaginator($queryBuilder, $entriesPerPage, $currentPage);
        $pagination = array(
            'pagesCount' => (int) ceil($paginator->count() / $entriesPerPage),
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
     * @param Request $request
     * @Route("/post", name="post_entry")
     * @Method("POST")
     * @return JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function addPostAction(Request $request)
    {
        //set extra values
        $entry = new Entry();
        $entry->setActive('1');
        $entry->setPublicDate(new DateTime());
        $entry->setIpAddress($request->getClientIp());

        //get form
        $form = $this->getForm($entry);

        $formPost = $request->get('form');
        if (isset($formPost['parent']) && $formPost['parent']) {
            $parent = $this->getDoctrine()->getRepository('ErbolkingGuestBookBundle:Entry')->find($formPost['parent']);
            /* @var $parent \Erbolking\Bundle\GuestBookBundle\Entity\Entry */
            if ($parent) {
                $entry->setParent($parent);
                //remove unnecessary property
                $form->remove('parent');
            }
            unset($formPost['parent']);
            $request->request->set('form', $formPost);
        }

        $form->handleRequest($request);
        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $entry->uploadImage();
            $em->persist($entry);
            $em->flush();
            if ($request->isXmlHttpRequest()) {

                $entryExtraInfo = array_merge($formPost,
                    array(
                        'id' => $entry->getId(),
                        'image' => $entry->getImage(),
                        'publicDate' => $entry->getPublicDate()->format('Y M d H:i')
                    )
                );
                return new JsonResponse(array('status' => 'ok', 'entry' => $entryExtraInfo));
            }
            return $this->redirect($this->generateUrl('list'));
        } else {
            $errors = array();
            foreach ($form as $field) {
                foreach ($field->getErrors() as $error) {
                    $errors[$field->getName()][] = $error->getMessage();
                }
            }
            return new JsonResponse(array('status' => 'error', 'errors' => $errors));
        }
    }

    /**
     * @param $entry
     * @return \Symfony\Component\Form\Form
     */
    private function getForm($entry) {
        $form = $this->createFormBuilder($entry, array('attr' => array('id' => 'postForm')))
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
            ->add('image', 'file', array('required' => false, 'attr' => array('accept' =>'image/x-png, image/gif, image/jpeg')))
            ->add('parent', 'hidden')
            ->add('captcha', 'captcha', array('reload' => true, 'as_url' => true))
            ->add('post', 'submit', array('label' => 'POST', 'attr' => array('class' => 'radius button')))
            ->getForm();

        return $form;
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @param int $limit
     * @param $page
     * @return Paginator
     */
    private function getDoctrinePaginator(QueryBuilder $queryBuilder, $limit = 10, $page)
    {
        $queryBuilder->where('e.parent is NULL')
            ->setFirstResult(($page - 1) * $limit)
            ->orderBy('e.publicDate', 'DESC')
            ->setMaxResults($limit);

        return new Paginator($queryBuilder);
    }
}
