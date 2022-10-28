<?php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Entity\User;
use App\Form\UserRegistrationType;
use App\Form\NewUserPasswordChangeType;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Doctrine\Persistence\ManagerRegistry;

use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/user', name: 'user.')]
class UserController extends AbstractController
{
    #[Route('/view/{page?}', name: 'view')]
	#param Request $request
	#return Response
    public function view(Request $request, $page, UserRepository $userRepository): Response
    {
		$logged_in_user = $this->getUser();
		if($logged_in_user->getfirstLogin() == 1){
			if(isset($page)){
				$form = $this->createFormBuilder()
				->add('search_txt', TextType::class, [
					'attr' => [
						'placeholder' => 'Username',
					],
				])
				->add('search', SubmitType::class, [
					'label' => 'search',
					'attr' => [
						'class' => 'btn btn-primary w-100'
					]
				])
				->getForm();
	
				$form->handleRequest($request);
				if ($form->isSubmitted()) {
					$search_txt = str_replace('/','-',$form->getData()['search_txt']);
					$user = new User();
					$totalPages = 0;
					$previousPage =0;
					$nextPage = 0;
					$users = $userRepository->findBy(['username' => $search_txt]);
					return $this->render('user/view.html.twig', [
						'currentPage' => $page,
						'users' => $users,
						'previousPage' =>  $previousPage,
						'nextPage' => $nextPage,
						'location' => 'user',
						'totalPages' => $totalPages,
						'searchForm' => $form->createView()
					]);
				}else{
					$user = new User();
					$totalPages = $userRepository->getTotalUser();
					$previousPage = ((($page - 10) < 0) ? 0 : ($page - 10));
					$nextPage = ((($page + 10) <= $totalPages) ? ($page + 10) : 0);
					$users = $userRepository->getUsersByPage($page);
					return $this->render('user/view.html.twig', [
						'currentPage' => $page,
						'users' => $users,
						'previousPage' =>  $previousPage,
						'nextPage' => $nextPage,
						'location' => 'user',
						'totalPages' => $totalPages,
						'searchForm' => $form->createView()
					]);
				}
			}
			return $this->render('home/index.html.twig');
		}else{
			return $this->redirect($this->generateUrl('user.check'));
		}
    }
	
    #[Route('/register', name: 'register')]
    public function register(Request $request, ManagerRegistry $doctrine, UserPasswordHasherInterface $passwordHasher, ValidatorInterface $validator): Response
    {
		$logged_in_user = $this->getUser();
		if($logged_in_user->getfirstLogin() == 1){
			$user = new User();
			$form = $this->createForm(UserRegistrationType::class, $user);
			
			$form->handleRequest($request);
			if($form->isSubmitted()){
				$errors = $validator->validate($user);
				if (count($errors) > 0) {
					$this->addFlash(
						'danger',
						'operation failed'
					);
					
					return $this->render('user/register.html.twig', [
						'form' => $form->createView()
					]);
				}else{
					$hashedPassword = $passwordHasher->hashPassword(
						$user,
						$user->getPassword()
					);
					$user->setPassword($hashedPassword);
					$entityManager = $doctrine->getManager();
					$entityManager->persist($user);
					$entityManager->flush($user);
					
					$this->addFlash(
						'success',
						'user registered succesfully'
					);
					
					return $this->render('user/register.html.twig', [
						'form' => $form->createView()
					]);
				}
			}
			return $this->render('user/register.html.twig', [
				'form' => $form->createView()
			]);
		}else{
			return $this->redirect($this->generateUrl('user.check'));
		}
    }
	
    #[Route('/check', name: 'check')]
    public function check(Request $request, UserRepository $userRepository, ManagerRegistry $doctrine, UserPasswordHasherInterface $passwordHasher, ValidatorInterface $validator): Response
    {
		$logged_in_user = $this->getUser();
		if($logged_in_user->getFirstLogin() == 0){
			$user = new User();
			$user = $logged_in_user;
			$form = $this->createForm(NewUserPasswordChangeType::class, $user);
			
			$form->handleRequest($request);
			if($form->isSubmitted()){
				$errors = $validator->validate($user);
				if (count($errors) > 0) {
					$this->addFlash(
						'danger',
						'operation failed'
					);
					
					return $this->render('user/update_new_user_password.html.twig', [
						'form' => $form->createView()
					]);
				}else{
					$hashedPassword = $passwordHasher->hashPassword(
						$user,
						$user->getPassword()
					);
					$user->setPassword($hashedPassword);
					$user->setFirstLogin(1);
					$entityManager = $doctrine->getManager();
					$entityManager->persist($user);
					$entityManager->flush($user);
					
					$this->addFlash(
						'success',
						'password changed succesfully'
					);
					return $this->redirect($this->generateUrl('app_logout'));
				}
			}
			return $this->render('user/update_new_user_password.html.twig', [
				'form' => $form->createView()
			]);
		}
		if($logged_in_user->getRoles()[0] == "ROLE_STUDENT"){
			return $this->redirect($this->generateUrl('student.results'));
		}else{
			return $this->redirect($this->generateUrl('home'));
		}
	}
}
