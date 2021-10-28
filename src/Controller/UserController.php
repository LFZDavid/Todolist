<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserController extends AbstractController
{

    /**
     *
     * @Route("/users", name="user_list")
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function listAction(UserRepository $userRepo)
    {
        return $this->render('user/list.html.twig', ['users' => $userRepo->findAll()]);
    }

    /**
     * @Route("/users/create", name="user_create")
     */
    public function createAction(
        Request $request,
        UserPasswordEncoderInterface $passwordEncoder,
        EntityManagerInterface $em
    ){
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        /**Allow admin to manage roles */
        if (
            $this->getUser()
            && $this->getUser()->hasRole('ROLE_ADMIN')
        ){
            $form->add('roles', ChoiceType::class, [
                'choices' => [
                    'Admin' => 'ROLE_ADMIN',
                ],
                'expanded'=> true,
                'multiple'=> true,
            ]);
        }
        
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $password = $passwordEncoder->encodePassword($user, $user->getPassword());
            $user->setPassword($password);

            $em->persist($user);
            $em->flush();

            $this->addFlash('success', "L'utilisateur a bien été ajouté.");

            return $this->redirectToRoute('user_list');
        }

        return $this->render('user/create.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/users/{id}/edit", name="user_edit")
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function editAction(
        User $user,
        Request $request,
        UserPasswordEncoderInterface $passwordEncoder,
        EntityManagerInterface $em
    ){
        $form = $this->createForm(UserType::class, $user);

        $form->add('roles', ChoiceType::class, [
            'choices' => [
                'Admin' => 'ROLE_ADMIN',
            ],
            'expanded'=> true,
            'multiple'=> true,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $password = $passwordEncoder->encodePassword($user, $user->getPassword());
            $user->setPassword($password);

            $em->flush();

            $this->addFlash('success', "L'utilisateur a bien été modifié");

            return $this->redirectToRoute('user_list');
        }

        return $this->render('user/edit.html.twig', ['form' => $form->createView(), 'user' => $user]);
    }
}
