<?php

namespace its\controllers;

use Silex\Api\ControllerProviderInterface;
use Silex\Application;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Regex;

class UserController implements ControllerProviderInterface
{

    public function index(Application $app, Request $request)
    {
        $token = $app['security.token_storage']->getToken();
        if (null !== $token) {
            $user = $token->getUser();
            var_dump($user->getIsActivated());

            if ($user->getIsActivated != 1) {
                $app['session']->getFlashBag()->add('not_activated', 'please activate your account');
                return $app->redirect('/');
            }

            $updateData = array(
                'lastlogin' => date("Y-m-d H:i:s")
            );
            $app['db']->update('users', $updateData, array('username'=>$user->getUsername()));

            $form = $app['form.factory']->createBuilder(FormType::class, array())
                ->add('mail', TextType::class, array(
                    'required' => false,
                    'constraints' => new Email()
                ))
                ->add('password', PasswordType::class, array(
                    'required' => false,
                    'constraints' => array(
                        new Regex(array(
                            'pattern' => "((?=.*\\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[@#$%?!]).{8,255})",
                            'message' => 'lenth:8,didgits:1,lower:1,upper:1,special:1| @ $ % # ? ! a-z A-Z 0-9'
                        ))
                    )
                ))
                ->getForm();
        }

        $form->handleRequest($request);

        if ($form->isValid()){
            $data = $form->getData();
            $updateData = array();
            if($data['mail'] != null){
                $updateData['mail'] = $data['mail'];
            }
            if($data['password'] != null){
                $updateData['password'] = password_hash($data['password'],PASSWORD_DEFAULT);
                $updateData['pwmodified'] = date("Y-m-d H:i:s");
            }

            $app['db']->update('users', $updateData, array('username'=>$user->getUsername()));

            return $app->redirect('/user');
        }

        return $app['twig']->render('user/index.html.twig', array(
            'form' => $form->createView(),
            'username' => $user->getUsername(),
            'mail' => $user->getMail(),
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

        $controllers->match('/', 'its\controllers\UserController::index');

        return $controllers;
    }
}
