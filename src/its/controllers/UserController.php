<?php

namespace its\controllers;
use Silex\Api\ControllerProviderInterface;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

class UserController implements ControllerProviderInterface{

    public function index(Application $app, Request $request){
		//TODO fetch user data display form
		
        return $app['twig']->render('user/index.html.twig');
    }

    /**
     * Returns routes to connect to the given application.
     *
     * @param \Silex\Application $app An Application instance
     *
     * @return \Silex\ControllerCollection A ControllerCollection instance
     */
    public function connect(\Silex\Application $app)
    {
        $controllers = $app['controllers_factory'];

        $controllers->get('/', 'its\controllers\UserController::index');

        return $controllers;
    }
}
