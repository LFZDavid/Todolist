# Autentification
## Package
Le systeme d'autentification est basé sur sur le bundle `symfony/security-bundle`.

---
## Les utilisateurs 
Les utilisateurs sont stockés dans la base de données et représenté par une classe `App\Entity\User::class` qui comprend entre autres, les attributs `username` et `password` qui seront utilisés pour l'authentification.

---
## Le "User Provider"
Le user provider représente le service qui permet de récupérer l'utilisateur représntée par l'entité `User`, lors du processus d'authentification.

>### Configuration
>```
>security:
>    providers:
>        app_user_provider:
>            entity:
>                class: App\Entity\User
>                property: username
>``` 
>La configuration ci-dessus signifie que l'`app_user_provider` a pour responsabilité de récupérer une `entity` de la classe `App\Entity\User` à partir de l'attribut `username` ( il est possible de selectionner un autre attribut comme par exemle l'email. Veillez toutefois à ce que ce champ soi __unique__ )

<div style="page-break-after: always"></div>

## Encodage des mot de passe
>### Configuration
>Il est possible de selectionner l'encodage utilisé pour les mots de passe 
>```
># config/packages/security.yaml
>security:
>    # ...
>
>    encoders:
>        App\Entity\User:
>            
>            algorithm: auto
>``` 
>_La valeur `auto` selectionne le meilleur algorithme de hashage possible (i.e. Sodium si disponible)_.

>### Le service
>Il est maintenant possible d'utiliser le `UserPasswordEncoderInterface` pour encoder les mots passe des utilisateurs avant l'enregistrement en base de donnés. Ce service peut être récupérer par injection de dépendance.
>ex:
>```
># scr/Controller/UserController.php
>
>class UserController extends AbstractController
>{
>    // ...
>    public function createAction(Request $request, UserPasswordEncoderInterface $passwordEncoder)
>    {
>        // ...
>        if ($form->isSubmitted() && $form->isValid()) {
>            // ...
>            $password = $passwordEncoder->encodePassword($user, $user->getPassword());
>            $user->setPassword($password);
>            // ...
>        }
>        // ...
>    }
>}
>```
>Le mot de passe fournis dans le formulaire encodé par le `PasswordEncoder` avant d'être attribué comme valeur de l'attribut `password` de l'entité `User`.

<div style="page-break-after: always"></div>

## Processus d'authentification & Firewalls
>### Config
>
>```
># config/packages/security.yaml
>
>security:
>    # ...
>    firewalls:
>        dev:
>            pattern: ^/(_(profiler|wdt)|css|images|js)/
>            security: false
>        main:
>            anonymous: lazy
>            provider: app_user_provider
>            guard:
>                authenticators:
>                    - App\Security\AppAuthenticator
>            logout:
>                path: logout
>
>```

>### Les firewalls
>Les firewalls servent a sécuriser différentes parties de l'application.
>* `dev` : Correspond aux routes utilisés pendent le développement. Lorsque le développement se fait en local il n'est pas nécessaire de faire appel au composant de sécurité _( security: false )_
>* `main` : Correspond au reste de l'application

>### Guard Authenticator
>La section `guard` correspond à la classe `AppAuthenticator` qui est chargé du de vérifier les informations du formulaire de login _( sécurisé par un token )_ et d'appeler le UserProvider.

>### Logout
>La section `logout` comme sont nom l'indique, sert a configurer les options de déconexion. 
>- L'option `path` indique le nom de la route destiné au logout.Bien que la méthode existe au seing du SecurityController, celle-ci n'est pas appelée. Il est seulement nécessaire que la route existe.
>- L'option `target` correspond à la route vers laquelle l'utilisateur sera redirigé après la déconexion. _(Rien n'a été précisé pour l'instant)_

>### SecurityController
>Le SécurityController aura pour rôle d'afficher le formulaire de connexion _( `templates/security/login.html.twig` )_. Le traitement de la soumission du formulaire est lui géré par l'`AppAuthenticator` vu précédemment.

<div style="page-break-after: always"></div>

## Roles
La création de rôles permet de gérer l'accès à certaines parties de l'application uniquement aux utilisateur disposant d'un niveau d'acréditation défini.
Les utilisateurs disposant du `ROLE_ADMIN` ont la possibilité de : 
* Accéder aux pages de gestions des utilisateurs.
* Supprimer une tâche créer par un autre utilisateur.
* Changer le roles d'un autre utilisateur.

>### Sécuriser les routes
>```
># config/packages/security.yaml
>security:
>    # ...
>    firewalls:
>        # ...
>        main:
>            # ...
>    access_control:
>        - { path: ^/users$, roles: ROLE_ADMIN }
>        - { path: ^/tasks, roles: IS_AUTHENTICATED_FULLY }
>```

>### Sécuriser les controllers
>```
>/**
>* @Route("/users", name="user_list")
>* @Security("is_granted('ROLE_ADMIN')")
>*/
>public function listAction()
>{
>    return $this->render('user/list.html.twig', ['users' => $this->getDoctrine()->getRepository('App:User')->findAll()]);
>}
>
>```

>### Sécuriser les méthodes de controller
>```
>/**
> * Require ROLE_ADMIN for *every* controller method in this class.
> * @IsGranted("ROLE_ADMIN")
> */
>class AdminController extends AbstractController
>{
 >   // ...
>}
>```

---
## Plus d'informations
Pour en savoir plus sur comment modifier ou fair évoluer le systeme d'authentification rendez-vous sur la [documentation officelle Symfony](https://symfony.com/doc/4.4/security.html#learn-more)