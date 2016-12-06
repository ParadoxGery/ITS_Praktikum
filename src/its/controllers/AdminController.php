<?php

namespace its\controllers;
use Silex\Api\ControllerProviderInterface;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

class AdminController implements ControllerProviderInterface{

    public function index(Application $app, Request $request){
        $users = $app['db']->fetchAll('SELECT * FROM users');


        // some default data for when the form is displayed the first time
        $data = array(
            
        );

        $form = $app['form.factory']->createBuilder(FormType::class, $data)
            ->add('username')
            ->add('mail')
            ->add('password', PasswordType::class)
            ->getForm();

        $form->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();

            // do something with the data
            $app['db']->insert('users', array(
                'username' => $data['username'],
                'mail' => $data['mail'],
                'password' => password_hash($data['password'],PASSWORD_DEFAULT),
            ));

            // redirect somewhere
            return $app->redirect('/admin');
        }


        return $app['twig']->render('admin/index.html.twig', array(
            'users' => $users,
            'form' => $form->createView(),
        ));
    }
	
	public function editUser(Application $app, Request $request, $uid){
		$userdata = $app['db']->fetchAssoc('SELECT * FROM users WHERE uid = ?',array($uid));
		
		$form = $app['form.factory']->createBuilder(FormType::class, array())
            ->add('mail')
            ->add('password', PasswordType::class)
            ->getForm();
		
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
			
			$app['db']->update('users', $updateData, array('uid'=>$uid));
			
			return $app->redirect('/admin');			
		}
			
		return $app['twig']->render('admin/userEdit.html.twig', array(
            'form' => $form->createView(),
			'username' => $userdata['username'],
        ));
	}
	
	public function deleteUser(Application $app, Request $request, $uid){
		$app['db']->delete('users', array('uid'=>$uid));
		return $app->redirect('/admin');
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

        $controllers->match('/', 'its\controllers\AdminController::index');
		
		$controllers->delete('/{uid}', 'its\controllers\AdminController::deleteUser');
		$controllers->match('/{uid}', 'its\controllers\AdminController::editUser');

        return $controllers;
    }
}
