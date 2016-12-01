<?php

namespace its\controllers;
use Silex\Api\ControllerProviderInterface;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

class AdminController implements ControllerProviderInterface{

    public function index(Application $app, Request $request){
        $users = $app['db']->fetchAll('SELECT * FROM users');

        return $app['twig']->render('admin/index.html.twig', array(
            'users' => $users,
        ));
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

        $controllers->get('/', 'its\controllers\AdminController::index');

        return $controllers;
    }
}
