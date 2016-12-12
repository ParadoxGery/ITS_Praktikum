<?php
/**
 * Created by PhpStorm.
 * User: Gery
 * Date: 10.12.2016
 * Time: 16:58
 */

namespace its\controllers;


use Silex\Api\ControllerProviderInterface;
use Silex\Application;
use Silex\ControllerCollection;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\Regex;

class LinkController implements ControllerProviderInterface
{

    public function activateMail(Application $app, Request $request, $code, $uid){
        $userdata = $app['db']->fetchAssoc('SELECT * FROM users WHERE uid = ? AND active = 0',array($uid));

        if(!$userdata) $app->abort(404,"no user found");

        $activation = $app['db']->fetchAssoc('SELECT * FROM mailcodes WHERE code = ? AND uid = ? AND used = 0',array($code,$userdata['uid']));

        if(!$activation) $app->abort(404, "no activation code found");

        $date = date("Y-m-d H:i:s");

        if(strtotime($date)<strtotime($activation['expires'])){
            $app['db']->update('users', array('active' => 1), array('uid'=>$uid));
            $app['db']->update('mailcodes', array('used' => 1), array('code'=>$code));

            return $app->redirect('/user');
        }

        return $app->abort(403,"expired");
    }

    public function recoverPassword(Application $app, Request $request, $uid, $code){
        $userdata = $app['db']->fetchAssoc('SELECT * FROM users WHERE uid = ?',array($uid));

        if(!$userdata) $app->abort(404,"no user found");

        $activation = $app['db']->fetchAssoc('SELECT * FROM recoverycodes WHERE code = ? AND uid = ? AND used = 0',array($code,$userdata['uid']));

        if(!$activation) $app->abort(404,"no recovery code found");

        $date = date("Y-m-d H:i:s");

        if(strtotime($date)<strtotime($activation['expires'])){
            $app['db']->update('recoverycodes', array('used' => 1), array('code'=>$code));

            $form = $app['form.factory']->createBuilder(FormType::class, array())
                ->add('password', PasswordType::class, array(
                    'constraints' => array(
                        new Regex(array(
                            'pattern' => "((?=.*\\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[@#$%?!]).{8,255})",
                            'message' => 'lenth:8,didgits:1,lower:1,upper:1,special:1| @ $ % # ? ! a-z A-Z 0-9'
                        ))
                    )
                ))
                ->getForm();

            $form->handleRequest($request);

            if ($form->isValid()){
                $data = $form->getData();
                $updateData = array(
                    'password' => password_hash($data['password'],PASSWORD_DEFAULT),
                    'pwmodified' => date("Y-m-d H:i:s"),
                );

                $app['db']->update('users', $updateData, array('uid'=>$uid));

                return $app->redirect('/user');
            }

            return $app['twig']->render('user/recovery.html.twig',array(
                'form' => $form->createView(),
            ));
        }

        return $app->abort(403, "expired");
    }

    /**
     * Returns routes to connect to the given application.
     *
     * @param Application $app An Application instance
     *
     * @return ControllerCollection A ControllerCollection instance
     */
    public function connect(Application $app)
    {
        $controllers = $app['controllers_factory'];

        $controllers->get('/mail/{uid}/{code}', 'its\controllers\LinkController::activateMail');
        $controllers->match('/recovery/{uid}/{code}', 'its\controllers\LinkController::recoverPassword');

        return $controllers;
    }
}