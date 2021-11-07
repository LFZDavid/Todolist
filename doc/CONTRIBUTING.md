# Contribution

Si vous souhaitez contribuer à ce projet, vous êtes invités à suivre la procédure suivante afin de faciliter la validation et l'intégration de votre contribution.

## 1. Créer une Issue<br>
   Tout d'abord, [créez une nouvelle issue](https://github.com/LFZDavid/Todolist/issues) que vous pourrez vous attribuer.<br> _Il est recommandé de vérifier au préalable si un autre développeur n'aurait pas déjà soumis une issue identique._

## 2. Créer une branche<br>
   Chaque nouvelle fonctionnalité ou amélioration doit être déployée sur une nouvelle branche du projet. N'oubliez pas d'associer cette branche à l'Issue correspondante.

## 3. Créer une demande d'ajout<br>
   Pour soumettre votre contribution, il vous suffit de [créer une demande d'ajout](https://github.com/LFZDavid/Todolist/pulls). Cette demande doit comparer les modifications qu'aporte votre branche à la branhce "develop".

## 4. Validation
   La demande d'ajout sera soumis aux étapes de validations suivantes :
   * __[Travis-ci](https://app.travis-ci.com/github/LFZDavid/Todolist/branches)__ : Un build sera automatiquement lancé pour chaque push. A chaque build, les tests unitaires et fonctionnels seront exécutés. Un rapport de couverture de code sera généré a la fin du processus. Si l'un des tests ne passe pas, le build échouera et la demande d'ajout ne pourra donc pas être validé.
   
   * __Qualité du code__ sur [CodeClimate](https://codeclimate.com/github/LFZDavid/Todolist/branches) :  Veillez à ne pas faire trop baisser le taux de maintenabilité.
   
   * __Code coverage__ sur [Coveralls](https://coveralls.io/github/LFZDavid/Todolist) : Le taux de couverture des tests doit être maintenue au-dessus de 80% 
   
   * __Review__ : Une review globale sera effectuée afin de finaliser la validation de la demande d'ajout.

## 5. Bonnes pratiques
   * Pour garantir la qualité de votre code et le respect des strandards de programmation en PHP, vous deverez veillez à respecter les conventions [PSR-1](https://www.php-fig.org/psr/psr-1/), [PSR-4](https://www.php-fig.org/psr/psr-4/), [PSR-12](https://www.php-fig.org/psr/psr-12/).
   * Veillez à ce que votre code soit testable (nous vous recommandons d'adopter la pratique du TDD)
   * Veillez à bien documenter votre code.
   * N'oubliez pas les `Type-hint`
      ```
      function foo(string $bar):array
      {
         // ...
      }
      ```
   * Nous vous encourageons également à vous référer à la section [The Symfony Framework Best Practices de la documentation Symfony](https://symfony.com/doc/4.4/best_practices.html)

## Merci pour votre contribution !