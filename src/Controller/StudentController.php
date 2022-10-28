<?php

namespace App\Controller;

use App\Form\MarksType;
use App\Entity\SubjectGrade;

use App\Repository\UserRepository;
use App\Entity\User;

use App\Entity\Student;
use App\Repository\StudentRepository;
use App\Form\RegisterStudentType;

use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

use App\Repository\SubjectGradeRepository;


use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

use Symfony\Component\Validator\Validator\ValidatorInterface;

use Doctrine\Persistence\ManagerRegistry;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

 #[Route('/student', name: 'student.')]
class StudentController extends AbstractController
{
    #[Route('/view/{page?}', name: 'view')]
	#param Request $request
	#return Response
    public function view(Request $request, $page, StudentRepository $studentRepository): Response
    {
		$logged_in_user = $this->getUser();
		if($logged_in_user->getfirstLogin() == 1){
			if(isset($page)){
				$form = $this->createFormBuilder()
				->add('search_txt', TextType::class, [
					'attr' => [
						'placeholder' => 'Registration ID',
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
					$student = new Student();
					$totalPages = 0;
					$previousPage =0;
					$nextPage = 0;
					$students = $studentRepository->findBy(['registration_id' => $search_txt]);
					return $this->render('student/view.html.twig', [
						'currentPage' => $page,
						'students' => $students,
						'previousPage' =>  $previousPage,
						'nextPage' => $nextPage,
						'location' => 'student',
						'totalPages' => $totalPages,
						'searchForm' => $form->createView()
					]);
				}else{
					$student = new Student();
					$totalPages = $studentRepository->getTotalRegisteredStudents();
					$previousPage = ((($page - 10) < 0) ? 0 : ($page - 10));
					$nextPage = ((($page + 10) <= $totalPages) ? ($page + 10) : 0);
					$students = $studentRepository->getStudentsByPage($page);
					return $this->render('student/view.html.twig', [
						'currentPage' => $page,
						'students' => $students,
						'previousPage' =>  $previousPage,
						'nextPage' => $nextPage,
						'location' => 'student',
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
    #param Request $request
	#return Response
    public function register(Request $request, ManagerRegistry $doctrine,ValidatorInterface $validator, UserPasswordHasherInterface $passwordHasher): Response
    {
		$logged_in_user = $this->getUser();
		if($logged_in_user->getfirstLogin() == 1){
			$student = new Student();
			$form = $this->createForm(RegisterStudentType::class, $student);
			
			$form->handleRequest($request);
			if($form->isSubmitted()){
				$errors = $validator->validate($student);
				if (count($errors) > 0) {
					$this->addFlash(
						'danger',
						'student registerings failed'
					);
					return $this->render('student/register.html.twig', [
						'form' => $form->createView()
					]);
				}else{
					$roles = null;
					$roles[0] = "ROLE_STUDENT";
					$user = new User();
					$user->setUsername($student->getRegistrationId());
					$user->setPassword($student->getRegistrationId());
					$user->setRoles($roles);
					$user->setFirstLogin(0);
					$hashedPassword = $passwordHasher->hashPassword(
							$user,
							$user->getPassword()
					);
					$user->setPassword($hashedPassword);
					$entityManager = $doctrine->getManager();
					$entityManager->persist($user);
					$entityManager->flush($user);
					$entityManager = $doctrine->getManager();
					$entityManager->persist($student);
					$entityManager->flush();
					$this->addFlash(
						'success',
						'student registered successfully'
					);
					return $this->render('student/register.html.twig', [
						'form' => $form->createView()
					]);
				}
			}
			return $this->render('student/register.html.twig', [
				'form' => $form->createView()
			]);
		}else{
			return $this->redirect($this->generateUrl('user.check'));
		}
    }
	
    #[Route('/marks', name: 'marks')]
    #param Request $request
	#return Response
    public function modiflyStudentMarks(Request $request, ManagerRegistry $doctrine, ValidatorInterface $validator): Response
    {
		$logged_in_user = $this->getUser();
		if($logged_in_user->getfirstLogin() == 1){
			$subjectGrade = new SubjectGrade();
			$form = $this->createForm(MarksType::class, $subjectGrade);
			
			$form->handleRequest($request);
			if($form->isSubmitted()){
				$errors = $validator->validate($subjectGrade);
				if (count($errors) > 0) {
					$this->addFlash(
						'danger',
						'Operation failed'
					);
					return $this->render('student/marks.html.twig', [
						'form' => $form->createView()
					]);
				}else{
					$entityManager = $doctrine->getManager();
					$subjectGradeSpecific = $entityManager->getRepository(SubjectGrade::class)->findBy([
						'student' => $subjectGrade->getStudent()->getID(),
						'subject' => $subjectGrade->getSubject()->getID()
					]);
					if($subjectGradeSpecific == null){
						$entityManager->persist($subjectGrade);
						$entityManager->flush();
					}else{
						$mark = $subjectGrade->getMark();
						$student = $subjectGrade->getStudent()->getID();
						$subject = $subjectGrade->getSubject()->getID();
						$query = $entityManager->createQuery(
							'UPDATE App\Entity\SubjectGrade s SET
							s.mark = :mark
							WHERE s.student = :student AND s.subject = :subject'
						)->setParameter('mark', $mark)
						->setParameter('student', $student)
						->setParameter('subject', $subject);
						$query->execute();
					}
					$this->addFlash(
						'success',
						'Operation successfully'
					);
					return $this->render('student/marks.html.twig', [
						'form' => $form->createView()
					]);
				}
			}
			return $this->render('student/marks.html.twig', [
				'form' => $form->createView()
			]);
		}else{
			return $this->redirect($this->generateUrl('user.check'));
		}
    }

    #[Route('/results/{student?}', name: 'results')]
	#return Response
    public function displayStudentResults( $student, SubjectGradeRepository $subjectGradeRepository, StudentRepository $studentRepository): Response
    {
		$logged_in_user = $this->getUser();
		if($logged_in_user->getfirstLogin() == 1){
			if($logged_in_user->getRoles()[0] == "ROLE_STUDENT"){
				$student = $studentRepository->findBy(['registration_id' => $logged_in_user->getUsername()]);
				if(isset($student)){
					$previousPage = 0;
					$nextPage = 0;
					$totalPages = $subjectGradeRepository->getTotalStudentGrades();
					$results = $subjectGradeRepository->getStudentGrades($student[0]->getId());
					
					return $this->render('student/results.html.twig', [
						'results' => $results,
						'previousPage' =>  $previousPage,
						'nextPage' => $nextPage,
						'location' => 'student',
						'totalPages' => $totalPages
					]);
				}
				return $this->render('home/index.html.twig');
			}else{
				if(isset($student)){
					$previousPage = 0;
					$nextPage = 0;
					$totalPages = $subjectGradeRepository->getTotalStudentGrades();
					$results = $subjectGradeRepository->getStudentGrades($student);
					
					return $this->render('student/results.html.twig', [
						'results' => $results,
						'previousPage' =>  $previousPage,
						'nextPage' => $nextPage,
						'location' => 'student',
						'totalPages' => $totalPages
					]);
				}
				return $this->render('home/index.html.twig');
			}
		}else{
			return $this->redirect($this->generateUrl('user.check'));
		}
    }
}
