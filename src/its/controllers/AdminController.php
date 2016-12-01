<?php

namespace its\controllers;
use Silex\Api\ControllerProviderInterface;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class AdminController implements ControllerProviderInterface{

    public function index(Application $app, Request $request){
        $users = $app['db']->fetchAll('SELECT * FROM users');


        // some default data for when the form is displayed the first time
        $data = array(
            'name' => 'name',
            'email' => 'email',
            'password' => 'password',
        );

        $form = $app['form.factory']->createBuilder(FormType::class, $data)
            ->add('name')
            ->add('email')
            ->add('password')
            ->getForm();

        $form->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();

            // do something with the data
            $app['db']->insert('users', array(
                'username' => $data['name'],
                'mail' => $data['email'],
                'password' => $data['password'],
            ));

            // redirect somewhere
            return $app->redirect('/admin');
        }


        return $app['twig']->render('admin/index.html.twig', array(
            'users' => $users,
            'form' => $form->createView(),
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
