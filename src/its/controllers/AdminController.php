<?php

namespace its\controllers;
use Silex\Api\ControllerProviderInterface;
use Silex\Application;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class AdminController implements ControllerProviderInterface{

    public function index(Application $app, Request $request){
        $users = $app['db']->fetchAll('SELECT * FROM users');

        $data = array();

        $form = $app['form.factory']->createBuilder(FormType::class, $data)
            ->add('username', TextType::class, array(
                'constraints' => array(
                    new NotBlank(),
                    new Length(array('min' => 5))
                )
            ))
            ->add('mail', TextType::class, array(
                'constraints' => new Email()
            ))
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

        if ($form->isValid()) {
            $data = $form->getData();

            $app['db']->insert('users', array(
                'username' => $data['username'],
                'mail' => $data['mail'],
                'password' => password_hash($data['password'],PASSWORD_DEFAULT),
            ));

            $userdata = $app['db']->fetchAssoc('SELECT * FROM users WHERE username = ?',array($data['username']));
            $app['generateLink']->generateMailLink($userdata['uid']);

            return $app->redirect('/admin');
        }


        return $app['twig']->render('admin/index.html.twig', array(
            'users' => $users,
            'form' => $form->createView(),
        ));
    }
	
	public function editUser(Application $app, Request $request, $uid){
        //get userdata
		$userdata = $app['db']->fetchAssoc('SELECT * FROM users WHERE uid = ?',array($uid));

		if(!$userdata) $app->abort(404,"user not found");

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
		
		$form->handleRequest($request);
		
		if ($form->isValid()){
			$data = $form->getData();
			$updateData = array();
			if($data['mail'] != null){
				$updateData['mail'] = $data['mail'];
				$updateData['active'] = 0;
				$app['generateLink']->generateMailLink($uid);
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
    public function connect(Application $app)
    {
        $controllers = $app['controllers_factory'];

        $controllers->match('/', 'its\controllers\AdminController::index');
		
		$controllers->get('/{uid}/delete', 'its\controllers\AdminController::deleteUser');
		$controllers->match('/{uid}', 'its\controllers\AdminController::editUser');

        return $controllers;
    }
}
