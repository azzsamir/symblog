<?php

namespace App\Controller;

use App\Form\UserType;
use App\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use App\Service\FileUploader;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 * Class UserController
 * @package App\Controller
 *
 */
class UserController extends AbstractController
{

    /**
     * @Route("/register", methods={"GET", "POST"}, name="register")
     * @Template("user/register.html.twig")
     */
    public function register(Request $request, 
    UserPasswordEncoderInterface $passwordEncoder, FileUploader $fileUploader)
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $password = $passwordEncoder->encodePassword($user,
             $user->getPlainPassword());
            $user->setPassword($password);

            if ($user->getAvatar()) {
                $uploadedAvatar = $fileUploader->upload(
                    $form->get('avatar')->getData());
                $user->setAvatar($uploadedAvatar);
            }

            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            return $this->redirectToRoute('homepage');
        }

        return [ 'form' => $form->createView() ];
    }

    /**
     * @Route("/login", name="login")
     * @Template("user/login.html.twig")
     * @param Request $request
     */
    public function login(AuthenticationUtils $authenticationUtils)
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        $username = $authenticationUtils->getLastUsername();

        return [
            "error" => $error,
            "last_username" => $username
        ];
    }

    /**
     * @Route("/dashboard", name="dashboard")
     * @Template("user/dashboard.html.twig")
     */
    public function dashboard()
    {
    }
}
