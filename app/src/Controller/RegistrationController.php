<?php
namespace App\Controller;

use App\Entity\Goer;
use App\Entity\Employee;
use App\Entity\Admin;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'register')]
    public function register(Request $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager)
    {
        $role = $request->request->get('role');
        $user = null;
        $errors = [];

        if ($role === 'goer') {
            $email = $request->request->get('goer_email');
            $password = $request->request->get('goer_password');
            $name = $request->request->get('goer_name');
            $phone = $request->request->get('goer_phone');
        
            if (!$email) {
                $errors['goer_email'] = 'Email обязателен.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors['goer_email'] = 'Некорректный формат email.';
            } elseif ($entityManager->getRepository(Goer::class)->findOneBy(['goerEmail' => $email])) {
                $errors['goer_email'] = 'Этот email уже зарегистрирован.';
            }
        
            if (!$password) {
                $errors['goer_password'] = 'Пароль обязателен.';
            }
        
            if (!$name) {
                $errors['goer_name'] = 'Имя обязательно.';
            }
        
            if ($phone && !preg_match('/^\d{10}$/', $phone)) {
                $errors['goer_phone'] = 'Телефон должен содержать 10 цифр без пробелов.';
            }
        
            if (empty($errors)) {
                $user = new Goer();
                $user->setGoerEmail($email);
                $user->setGoerName($name);
                $user->setGoerPassword($passwordHasher->hashPassword($user, $password));
                $user->setGoerDate(new \DateTime());
        
                if ($phone) {
                    $user->setGoerPhone($phone);
                }
            }
        } elseif ($role === 'employee') {
            $login = $request->request->get('employee_login');
            $password = $request->request->get('employee_password');
            $position = $request->request->get('employee_position');
            $adminLogin = $request->request->get('admin_login_for_employee');
            $name = $request->request->get('employee_name');
        
            if (!$login) {
                $errors['employee_login'] = 'Логин обязателен.';
            } elseif ($entityManager->getRepository(Employee::class)->findOneBy(['employeeLogin' => $login])) {
                $errors['employee_login'] = 'Этот логин уже зарегистрирован.';
            }
        
            if (!$password) {
                $errors['employee_password'] = 'Пароль обязателен.';
            }
        
            if (!$name) {
                $errors['employee_name'] = 'Имя обязательно.';
            }
        
            if (!$position) {
                $errors['employee_position'] = 'Должность обязательна.';
            }
        
            if (!$adminLogin) {
                $errors['admin_login_for_employee'] = 'Необходимо выбрать администратора.';
            } elseif (!$entityManager->getRepository(Admin::class)->findOneBy(['adminLogin' => $adminLogin])) {
                $errors['admin_login_for_employee'] = 'Администратор не найден.';
            }
        
            if (empty($errors)) {
                $user = new Employee();
                $user->setEmployeeLogin($login);
                $user->setEmployeePassword($passwordHasher->hashPassword($user, $password));
                $user->setEmployeePosition($position);
                $user->setEmployeeName($name);
        
                $admin = $entityManager->getRepository(Admin::class)->findOneBy(['adminLogin' => $adminLogin]);
                $user->setAdmin($admin);
            }
        } elseif ($role === 'admin') {
            $login = $request->request->get('admin_login');
            $password = $request->request->get('admin_password');
            $name = $request->request->get('admin_name');
        
            if (!$login) {
                $errors['admin_login'] = 'Логин обязателен.';
            } elseif ($entityManager->getRepository(Admin::class)->findOneBy(['adminLogin' => $login])) {
                $errors['admin_login'] = 'Этот логин уже зарегистрирован.';
            }
        
            if (!$password) {
                $errors['admin_password'] = 'Пароль обязателен.';
            }
        
            if (!$name) {
                $errors['admin_name'] = 'Имя обязательно.';
            }
        
            if (empty($errors)) {
                $user = new Admin();
                $user->setAdminLogin($login);
                $user->setAdminPassword($passwordHasher->hashPassword($user, $password));
                $user->setAdminName($name);
            }
        } else {
            $errors[] = 'Неверная роль.';
        }

        if ($errors) {
            return $this->render('security/register.html.twig', [
                'admins' => $entityManager->getRepository(Admin::class)->findAll(),
                'errors' => $errors,
            ]);
        }

        if ($user) {
            try {
                $entityManager->persist($user);
                $entityManager->flush();
                $this->addFlash('success', 'Регистрация прошла успешно!');
                return $this->redirectToRoute('login');
            } catch (\Exception $e) {
                $errors[] = 'Ошибка при сохранении: ' . $e->getMessage();
            }
        }

        return $this->render('security/register.html.twig', [
            'admins' => $entityManager->getRepository(Admin::class)->findAll(),
            'errors' => $errors,
        ]);
    }
}