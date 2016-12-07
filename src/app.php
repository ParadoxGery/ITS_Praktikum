<?php

use Silex\Application;
use Silex\Provider\AssetServiceProvider;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\ServiceControllerServiceProvider;
use Silex\Provider\HttpFragmentServiceProvider;
use Silex\Provider\DoctrineServiceProvider;
use Silex\Provider\FormServiceProvider;
use Silex\Provider\TranslationServiceProvider;
use Silex\Provider\LocaleServiceProvider;
use Silex\Provider\SecurityServiceProvider;
use Silex\Provider\SessionServiceProvider;
use its\user\UserProvider;
use its\user\UserPasswordEncoder;

$app = new Application();
$app->register(new ServiceControllerServiceProvider());
$app->register(new AssetServiceProvider());
$app->register(new TwigServiceProvider());
$app->register(new FormServiceProvider());
$app->register(new LocaleServiceProvider());
$app->register(new TranslationServiceProvider(), array(
	'translator.domains' => array(),
	'locale_fallbacks' => array('en'),
));
$app->register(new DoctrineServiceProvider(), array(
    'db.options' => array(
        'driver'   => 'pdo_sqlite',
        'path'     => __DIR__.'/../rhino.db',
    ),
));
$app->register(new HttpFragmentServiceProvider());
$app['twig'] = $app->extend('twig', function ($twig, $app) {
    return $twig;
});
	
$app->register(new SessionServiceProvider());
$app->register(new SecurityServiceProvider(), array(
    'security.firewalls' => array(
		'admin' => array(
			'pattern' => '^/admin/',
			'form' => array('login_path' => '/login', 'check_path' => '/admin/login_check'),
			'logout' => array('logout_path' => '/admin/logout', 'invalidate_session' => true),
			'users' => array(
				'admin' => array('ROLE_ADMIN', '$2y$10$6KLCXtg/2pVYD0cNkUXjxODbnDYAJsI9cZPXfAxTFw46FYdJmy6Nu'),
			),
		),
		'users' => array(
			'pattern' => '^/user/',
			'form' => array('login_path' => '/login', 'check_path' => '/user/login_check'),
			'logout' => array('logout_path' => '/user/logout', 'invalidate_session' => true),
			'users' => function () use ($app) {
				return new UserProvider($app['db']);
			},
		),
	),
	'security.access_rules' => array(
		array('^/admin', 'ROLE_ADMIN', 'https'),
		array('^/user', 'ROLE_USER', 'https'),
	),
));
$app['security.default_encoder'] = function($app) {
	return new UserPasswordEncoder();
};

$app->get('/login', function(Symfony\Component\HttpFoundation\Request $request) use ($app) {
    return $app['twig']->render('login.html.twig', array(
        'error'         => $app['security.last_error']($request),
        'last_username' => $app['session']->get('_security.last_username'),
    ));
});

return $app;
