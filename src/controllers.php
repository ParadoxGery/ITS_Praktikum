<?php

use its\controllers\LinkController;
use its\controllers\UserController;
use its\controllers\AdminController;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;

Request::setTrustedProxies(array('127.0.0.1'));


$app->get('/', function () use ($app) {
    $recoverycodes = $app['db']->fetchAll('SELECT * FROM recoverycodes WHERE used = 0');

    $mailactivations = $app['db']->fetchAll('SELECT * FROM mailcodes WHERE used = 0');
    return $app['twig']->render('index.html.twig', array(
        'mailcodes' => $mailactivations,
        'recoverycodes' =>$recoverycodes,
    ));
});

$app->get('/login/{role}', function(Request $request,$role) use ($app) {
    if(!($role=="admin"||$role=="user")) $app->abort(404);
    $params = array(
        'error'         => $app['security.last_error']($request),
        'last_username' => $app['session']->get('_security.last_username'),
    );
    if($role == "user"){
        $form = $app['form.factory']->createBuilder(FormType::class, array())
            ->add('username', TextType::class, array(
                'constraints' => array(
                    new NotBlank()
                )
            ))
            ->add('mail', TextType::class, array(
                'constraints' => array(
                    new Email(),
                    new NotBlank()
                )
            ))
            ->getForm();

        $form->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();
            $user = $this->connection->fetchAssoc('SELECT * FROM users WHERE username = ? AND mail = ?', array($data['username'],$data['mail']));
            if(!$user) $app->abort(403); //TODO better error message
            $app['generateLink']->generatePasswordRecoveryLink($user['uid']);
            return $app->redirect('/');
        }
        $params['form'] = $form->createView();
    }

    return $app['twig']->render($role.'/login.html.twig', array(
        'error'         => $app['security.last_error']($request),
        'last_username' => $app['session']->get('_security.last_username'),
    ));
});

$app->mount('/user', new UserController());
$app->mount('/admin', new AdminController());
$app->mount('/activate', new LinkController());

$app->error(function (\Exception $e, Request $request, $code) use ($app) {
    if ($app['debug']) {
        return;
    }

    // 404.html, or 40x.html, or 4xx.html, or error.html
    $templates = array(
        'errors/'.$code.'.html.twig',
        'errors/'.substr($code, 0, 2).'x.html.twig',
        'errors/'.substr($code, 0, 1).'xx.html.twig',
        'errors/default.html.twig',
    );

    return new Response($app['twig']->resolveTemplate($templates)->render(array('code' => $code)), $code);
});
