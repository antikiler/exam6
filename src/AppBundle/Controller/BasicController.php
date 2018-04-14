<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;

class BasicController extends Controller
{
    /**
     * @Route("/", name="homePage")
     * @Method("GET")
     */
    public function indexAction()
    {
        return $this->render("@App/basic/index.html.twig", array(
            'user' => $this->getUser()
        ));
    }

    /**
     * @Route("/login")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function loginAction(Request $request)
    {
        $authUtils = $this->get('security.authentication_utils');
        $error = $authUtils->getLastAuthenticationError();
        $lastUsername = $authUtils->getLastUsername();

        return $this->render('@App/basic/login.html.twig', array(
            'last_username' => $lastUsername,
            'error'         => $error,
        ));
    }


}
