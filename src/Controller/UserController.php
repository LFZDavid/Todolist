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

class UserController extends AbstractController
{

    /**
     *
     * @Route("/users", name="user_list")
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function list(UserRepository $userRepo)
    {
        return $this->render('user/list.html.twig', ['users' => $userRepo->findAll()]);
    }

    /**
     * @Route("/users/create", name="user_create")
     */
    public function create(
        Request $request,
        EntityManagerInterface $manager
    ) {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        /**Allow admin to manage roles */
        if ($this->getUser()&& $this->getUser()->hasRole('ROLE_ADMIN')) {
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

            $manager->persist($user);
            $manager->flush();

            $this->addFlash('success', "L'utilisateur a bien été ajouté.");

            return $this->redirectToRoute('user_list');
        }

        return $this->render('user/create.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/users/{id}/edit", name="user_edit")
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function edit(
        User $user,
        Request $request,
        EntityManagerInterface $manager
    ) {
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

            $manager->flush();

            $this->addFlash('success', "L'utilisateur a bien été modifié");

            return $this->redirectToRoute('user_list');
        }

        return $this->render('user/edit.html.twig', ['form' => $form->createView(), 'user' => $user]);
    }
}
