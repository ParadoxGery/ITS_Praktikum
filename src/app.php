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
$app->register(new SecurityServiceProvider(), array(
    'security.firewalls' => array(
		'admin' => array(
			'pattern' => '^/admin/',
			'form' => array('login_path' => '/login', 'check_path' => '/admin/login_check'),
			'users' => array(
				'admin' => array('ROLE_ADMIN', '$2y$10$6KLCXtg/2pVYD0cNkUXjxODbnDYAJsI9cZPXfAxTFw46FYdJmy6Nu'),
			),
		),
	)
));

$app->get('/login', function(Symfony\Component\HttpFoundation\Request $request) use ($app) {
    return $app['twig']->render('login.html', array(
        'error'         => $app['security.last_error']($request),
        'last_username' => $app['session']->get('_security.last_username'),
    ));
});

return $app;
