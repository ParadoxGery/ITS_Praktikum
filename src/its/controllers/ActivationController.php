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
use Symfony\Component\HttpFoundation\Request;

class ActivationController implements ControllerProviderInterface
{

    public function activateMail(Application $app, Request $request, $code, $uid){
        $activation = $app['db']->fetchAssoc('SELECT * FROM mailcodes WHERE link = ?',array($code));
        $userdata = $app['db']->fetchAssoc('SELECT * FROM users WHERE uid = ?',array($uid));

        if(!$activation||!$userdata) $app->abort(404);
        if($activation['used'] != 0) $app->abort(403); //TODO better error message

        $date = date("Y-m-d H:i:s");
        var_dump($date);
        var_dump($activation['expires']);
        if($date<date($activation['expires'])){
            $app['db']->update('users', array('active' => 1), array('uid'=>$uid));
            $app['db']->update('mailcodes', array('used' => 1), array('link'=>$code));

            $app->redirect('/user');
        }

        $app->abort(500);
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

        $controllers->get('/mail/{uid}/{code}', 'its\controllers\ActivationController::activateMail');

        return $controllers;
    }
}