# Cours Symfony 8 — Préparation Certification

> Fichier mis à jour progressivement au fil de la formation.
> Référence officielle : https://certification.symfony.com/exams/symfony.html

---

## Progression

| # | Notion | Statut |
|---|--------|--------|
| 01 | PHP API up to PHP 8.4 | ✅ Terminé |
| 02 | Object Oriented Programming | ✅ Terminé |
| 03 | Attributes | ✅ Terminé |
| 04 | Interfaces | ✅ Terminé |
| 05 | Anonymous functions and closures | ✅ Terminé |
| 06 | Abstract classes | ✅ Terminé |
| 07 | Exception and error handling | ✅ Terminé |
| 08 | Traits | ✅ Terminé |
| 09 | Enums | ✅ Terminé |
| 10 | HTTP Specification (RFC 9110) | ⬜ |
| 11 | Status codes | ⬜ |
| 12 | HTTP request | ⬜ |
| 13 | HTTP response | ⬜ |
| 14 | HTTP methods | ⬜ |
| 15 | Cookies | ⬜ |
| 16 | Caching HTTP | ⬜ |
| 17 | Content negotiation | ⬜ |
| 18 | Language detection | ⬜ |
| 19 | Symfony HttpClient component | ⬜ |
| 20 | HttpFoundation component | ⬜ |
| 21 | Symfony Flex | ⬜ |
| 22 | License | ⬜ |
| 23 | Components and Bridges | ⬜ |
| 24 | Code organization | ⬜ |
| 25 | Request handling | ⬜ |
| 26 | Exception handling (Symfony) | ⬜ |
| 27 | Event dispatcher and kernel events | ⬜ |
| 28 | Official best practices | ⬜ |
| 29 | Backward compatibility promise | ⬜ |
| 30 | Deprecations best practices | ⬜ |
| 31 | Release management and roadmap | ⬜ |
| 32 | Framework interoperability and PSRs | ⬜ |
| 33 | Naming conventions | ⬜ |
| 34 | Controllers | ⬜ |
| 35 | Routing | ⬜ |
| 36 | Templating with Twig | ⬜ |
| 37 | Forms | ⬜ |
| 38 | Data Validation | ⬜ |
| 39 | Dependency Injection | ⬜ |
| 40 | Security | ⬜ |
| 41 | Messenger | ⬜ |
| 42 | Console | ⬜ |
| 43 | Automated Tests | ⬜ |
| 44 | Miscellaneous (Cache, Mailer, Serializer…) | ⬜ |

---

## Chapitre 1 — PHP Moderne (PHP 8.x jusqu'à 8.4)

### 1.1 PHP API up to PHP 8.4

PHP 8.x introduit de nombreuses fonctionnalités modernes indispensables à Symfony. Voici les plus importantes pour la certification.

---

#### Named Arguments (PHP 8.0)

Permet de passer des arguments par nom, dans n'importe quel ordre.

```php
// Avant PHP 8.0 — on devait respecter l'ordre
array_slice($array, 1, null, true);

// Avec les named arguments
array_slice(array: $array, offset: 1, preserve_keys: true);
```

---

#### Union Types (PHP 8.0)

Un paramètre peut accepter plusieurs types.

```php
function traiter(int|string $valeur): int|string
{
    return $valeur;
}
```

---

#### Match Expression (PHP 8.0)

Alternative plus stricte et plus puissante au `switch`. Utilise une comparaison stricte (`===`), retourne une valeur, et lève une `UnhandledMatchError` si aucun cas ne correspond.

```php
$statut = 2;

$libelle = match($statut) {
    1       => 'Actif',
    2       => 'Inactif',
    3, 4    => 'En attente',    // plusieurs valeurs possibles
    default => 'Inconnu',
};
// $libelle === 'Inactif'
```

---

#### Nullsafe Operator `?->` (PHP 8.0)

Arrête la chaîne d'appels si la valeur est `null`, sans lancer d'erreur.

```php
// Sans nullsafe — verbeux et fragile
$ville = null;
if ($user !== null) {
    $adresse = $user->getAdresse();
    if ($adresse !== null) {
        $ville = $adresse->getVille();
    }
}

// Avec le nullsafe operator
$ville = $user?->getAdresse()?->getVille(); // null si l'un est null
```

---

#### Fibers (PHP 8.1)

Fonctions pouvant être suspendues et reprises. Utilisées pour la programmation asynchrone (ex. Symfony Async avec ReactPHP).

```php
$fiber = new Fiber(function(): void {
    $valeur = Fiber::suspend('première suspension');
    echo "Valeur reçue : " . $valeur . "\n";
});

$retour = $fiber->start();       // "première suspension"
$fiber->resume('bonjour');       // "Valeur reçue : bonjour"
```

---

#### Intersection Types (PHP 8.1)

Un paramètre doit satisfaire **tous** les types (utile avec les interfaces).

```php
interface Serializable {}
interface Loggable {}

// $service doit implémenter les DEUX interfaces
function traiter(Serializable&Loggable $service): void {}
```

---

#### Enums (PHP 8.1)

Voir section dédiée (notion 09).

---

#### Readonly Properties (PHP 8.1) et Readonly Classes (PHP 8.2)

Une propriété `readonly` ne peut être assignée qu'une seule fois (dans le constructeur).

```php
class Utilisateur
{
    public function __construct(
        public readonly string $email,
        public readonly string $nom,
    ) {}
}

$u = new Utilisateur('alice@example.com', 'Alice');
// $u->email = 'autre@example.com'; // Erreur !
```

En PHP 8.2, `readonly class` rend toutes les propriétés readonly automatiquement.

```php
readonly class Point
{
    public function __construct(
        public float $x,
        public float $y,
    ) {}
}
```

---

#### First Class Callable Syntax (PHP 8.1)

Récupérer une fonction/méthode comme callable sans closure.

```php
$fn = strlen(...);       // équivalent à Closure::fromCallable('strlen')
$fn('hello');            // 5

$fn = $objet->methode(...);
$fn();
```

---

#### DNF Types — Disjunctive Normal Form (PHP 8.2)

Combinaison de types union et intersection.

```php
// (A&B)|C  — doit être (A ET B) OU C
function traiter((Serializable&Countable)|null $data): void {}
```

---

#### `never` Return Type (PHP 8.1)

Indique qu'une fonction ne retourne jamais (elle lance une exception ou appelle `exit()`).

```php
function redirectEtStop(): never
{
    header('Location: /accueil');
    exit();
}
```

---

#### Promoted Constructor Properties (PHP 8.0)

Déclarer ET initialiser les propriétés directement dans la signature du constructeur.

```php
// Avant PHP 8.0
class Produit
{
    private string $nom;
    private float $prix;

    public function __construct(string $nom, float $prix)
    {
        $this->nom  = $nom;
        $this->prix = $prix;
    }
}

// Avec PHP 8.0 — beaucoup plus concis
class Produit
{
    public function __construct(
        private string $nom,
        private float $prix,
    ) {}
}
```

> **Très utilisé dans Symfony** : services, entités Doctrine, DTOs, Value Objects.

---

#### `array_is_list()` (PHP 8.1)

Vérifie si un tableau est une liste (clés entières séquentielles à partir de 0).

```php
array_is_list([1, 2, 3]);           // true
array_is_list(['a' => 1, 'b' => 2]); // false
array_is_list([0 => 'a', 1 => 'b']); // true
```

---

#### New in Initializers (PHP 8.1)

Utiliser `new` dans les valeurs par défaut des paramètres, attributs, etc.

```php
class Service
{
    public function __construct(
        private Logger $logger = new NullLogger(), // possible en PHP 8.1
    ) {}
}
```

---

#### PHP 8.3 — Typed Class Constants

```php
class Config
{
    const string VERSION = '1.0.0';
    const int MAX_RETRY  = 3;
}
```

---

#### PHP 8.4 — Property Hooks

Les Property Hooks permettent de définir une logique get/set directement sur une propriété, sans méthodes séparées.

```php
class Utilisateur
{
    public string $nomComplet
    {
        get => $this->prenom . ' ' . $this->nom;
        set(string $valeur) {
            [$this->prenom, $this->nom] = explode(' ', $valeur, 2);
        }
    }
}
```

---

#### PHP 8.4 — Asymmetric Visibility

Visibilité différente pour la lecture et l'écriture d'une propriété.

```php
class Commande
{
    public private(set) int $total = 0; // lecture publique, écriture privée
}
```

---

### Points clés pour la certification

| Fonctionnalité | Version | À retenir |
|---|---|---|
| Named arguments | 8.0 | Ordre libre, lisibilité |
| Match expression | 8.0 | Stricte (`===`), retourne une valeur |
| Nullsafe `?->` | 8.0 | Chaîne null-safe |
| Promoted constructor | 8.0 | Propriétés dans le constructeur |
| Enums | 8.1 | Voir section dédiée |
| Readonly properties | 8.1 | Une seule assignation |
| `never` return type | 8.1 | Fonction qui ne retourne pas |
| Intersection types | 8.1 | `A&B` |
| DNF types | 8.2 | `(A&B)\|C` |
| Readonly class | 8.2 | Toutes props readonly |
| Typed constants | 8.3 | `const string X = '...'` |
| Property hooks | 8.4 | get/set inline |
| Asymmetric visibility | 8.4 | `public private(set)` |

---

*Chapitre terminé.*

---

## Chapitre 2 — Object Oriented Programming (OOP)

L'OOP est le cœur de Symfony. Chaque service, contrôleur, entité est un objet. Comprendre les piliers de l'OOP permet de lire et d'écrire du code Symfony fluide.

---

### 2.1 Les 4 piliers de l'OOP

| Pilier | Définition courte |
|---|---|
| **Encapsulation** | Cacher les données internes, exposer une interface contrôlée |
| **Héritage** | Une classe enfant réutilise et étend sa classe parente |
| **Polymorphisme** | Un même appel produit des comportements différents selon le type réel |
| **Abstraction** | Exposer uniquement ce qui est nécessaire, masquer la complexité |

---

### 2.2 Visibilité : public / protected / private

```
public    → Accessible depuis n'importe où
protected → Accessible depuis la classe et ses classes enfants
private   → Accessible uniquement depuis la classe elle-même
```

> **Règle Symfony :** propriétés = `private`/`protected`, setters/getters = `public`.

---

### 2.3 Héritage (`extends`)

- Une classe PHP ne peut hériter que d'**une seule** classe parente.
- `parent::` appelle la version de la méthode de la classe parente.
- `parent::__construct()` est **obligatoire** si le parent a un constructeur avec des arguments.

```php
class Chien extends Animal
{
    public function __construct(string $nom, int $age, string $race)
    {
        parent::__construct($nom, $age); // indispensable
        $this->race = $race;
    }
}
```

---

### 2.4 Méthodes et propriétés statiques

- Appartiennent à la **classe**, pas à une instance.
- Accessibles via `ClassName::method()` sans `new`.
- `self::` = la classe où la méthode est **écrite**.
- `static::` = la classe **réellement appelée** (Late Static Binding — LSB).

```php
class Base
{
    public static function creer(): static
    {
        return new static(); // crée l'instance de la classe appelante
    }
}
class Enfant extends Base {}

$e = Enfant::creer(); // instance de Enfant, pas de Base
```

> LSB est utilisé dans Doctrine (repositories, entités) et dans les design patterns.

---

### 2.5 Méthodes magiques essentielles

| Méthode | Déclencheur |
|---|---|
| `__construct()` | Création d'une instance (`new`) |
| `__toString()` | Conversion en chaîne (`echo $obj`) |
| `__invoke()` | Utilisation comme fonction (`$obj()`) |
| `__clone()` | Duplication (`clone $obj`) |
| `__debugInfo()` | `var_dump()` / `dump()` |

> `__invoke()` est très utilisé dans Symfony pour les **middleware**, **handlers Messenger**, et les **callable services**.

---

### 2.6 Constantes de classe

- Appartiennent à la classe (pas à l'instance).
- Accessibles via `ClassName::CONSTANTE`.
- Typables en PHP 8.3 : `const int MAX = 10;`
- Peuvent être surchargées dans les classes enfants.

---

### 2.7 Différences clés à retenir pour l'examen

| Concept | À retenir |
|---|---|
| `extends` | Héritage simple (une seule classe parente) |
| `implements` | Une classe peut implémenter **plusieurs** interfaces |
| `abstract` | Classe non instanciable, méthodes sans corps |
| `interface` | Contrat pur, pas de code (sauf constantes) |
| `trait` | Mixin de méthodes, résout l'héritage multiple |
| `self::` | Classe où le code est **écrit** |
| `static::` | Classe **appelante** (Late Static Binding) |
| `parent::` | Méthode/propriété de la classe **parente** |

---

*Chapitre terminé.*

---

## Chapitre 3 — Attributes PHP 8.x

### 3.1 Définition

Un **Attribute** est une classe PHP ordinaire décorée avec `#[Attribute]`. Il permet d'attacher des **métadonnées structurées** à une classe, méthode, propriété, paramètre ou constante — sans passer par des commentaires PHPDoc.

```php
// Ancien style (annotations Doctrine, avant PHP 8)
/** @Route("/chemin") */

// Nouveau style (Attribute natif PHP 8)
#[Route('/chemin')]
```

---

### 3.2 Créer un Attribute

```php
#[Attribute(Attribute::TARGET_METHOD)]
class RequiertAuthentification
{
    public function __construct(
        public readonly string $role = 'ROLE_USER',
    ) {}
}
```

- La classe doit être marquée `#[Attribute]`
- Le constructeur reçoit les arguments fournis lors de l'application
- Le flag `TARGET_*` contrôle où il peut être appliqué

---

### 3.3 Flags TARGET\_\*

| Flag | Cible |
|---|---|
| `TARGET_CLASS` | Classe |
| `TARGET_METHOD` | Méthode |
| `TARGET_PROPERTY` | Propriété |
| `TARGET_PARAMETER` | Paramètre de fonction |
| `TARGET_CLASS_CONST` | Constante de classe |
| `TARGET_FUNCTION` | Fonction |
| `TARGET_ALL` | Tout (valeur par défaut) |
| `IS_REPEATABLE` | Peut être appliqué plusieurs fois |

---

### 3.4 Lire les attributes (Reflection API)

Symfony lit vos attributes au démarrage grâce à la **Reflection API** :

```php
$ref = new ReflectionClass(MonControleur::class);

foreach ($ref->getAttributes(Route::class) as $attrRef) {
    $route = $attrRef->newInstance(); // instancie l'attribute
    echo $route->path;
}
```

---

### 3.5 Attributes Symfony essentiels

| Attribute | Usage |
|---|---|
| `#[Route]` | Définir une route sur un contrôleur/méthode |
| `#[ORM\Entity]`, `#[ORM\Column]` | Mapping Doctrine |
| `#[Assert\NotBlank]`, `#[Assert\Email]` | Contraintes de validation |
| `#[Autowire]` | Injecter un service ou paramètre spécifique |
| `#[AsCommand]` | Déclarer une commande Console |
| `#[AsMessageHandler]` | Déclarer un handler Messenger |
| `#[IsGranted]` | Contrôle d'accès sur contrôleur/méthode |

---

### 3.6 Règles de syntaxe importantes

```php
// Arguments positionnels
#[Route('/blog')]

// Arguments nommés (recommandé pour la lisibilité)
#[Route(path: '/blog', name: 'blog_index', methods: ['GET'])]

// Plusieurs attributes sur le même élément
#[Route('/admin')]
#[IsGranted('ROLE_ADMIN')]
class AdminController {}

// Attribute répétable (IS_REPEATABLE obligatoire)
#[Cache(cle: 'v1', ttl: 3600)]
#[Cache(cle: 'v2', ttl: 60)]
public function index(): void {}
```

---

*Chapitre terminé.*

---

## Chapitre 4 — Interfaces

### 4.1 Définition

Une interface définit un **contrat** : la liste des méthodes que toute classe implémentant cette interface doit fournir. Elle ne contient **aucune logique**.

```php
interface LoggerInterface
{
    public function log(string $niveau, string $message): void;
    public function info(string $message): void;
    public function error(string $message): void;
}
```

---

### 4.2 Implémenter une interface (`implements`)

- Toutes les méthodes déclarées doivent être implémentées — sinon : Fatal Error.
- Une classe peut implémenter **plusieurs** interfaces.

```php
class ConsoleLogger implements LoggerInterface
{
    public function log(string $niveau, string $message): void
    {
        echo "[{$niveau}] {$message}\n";
    }
    public function info(string $message): void  { $this->log('info', $message); }
    public function error(string $message): void { $this->log('error', $message); }
}
```

---

### 4.3 Type-hinting sur interface = découplage

```php
class CommandeService
{
    public function __construct(
        private LoggerInterface $logger, // accepte TOUTE implémentation
    ) {}
}

// On peut passer FileLogger, ConsoleLogger, NullLogger…
$service = new CommandeService(new ConsoleLogger());
```

> C'est le principe de **l'inversion de dépendance** (SOLID — lettre D). Symfony construit tous ses services sur ce principe.

---

### 4.4 Héritage d'interfaces

```php
interface ReadableRepositoryInterface
{
    public function find(int $id): ?object;
}

interface RepositoryInterface extends ReadableRepositoryInterface
{
    public function save(object $entite): void;
    public function findBy(array $criteres): array;
}
```

La classe qui implémente `RepositoryInterface` doit honorer **tout** le contrat cumulé.

---

### 4.5 Interfaces et Symfony

| Situation | Comportement |
|---|---|
| 1 seule classe implémente l'interface | Autowired automatiquement |
| Plusieurs classes implémentent l'interface | Alias dans `services.yaml` ou `#[Autowire(service: '...')]` |

---

### Points clés pour la certification

| Concept | À retenir |
|---|---|
| `implements` | Contrat obligatoire, plusieurs interfaces possibles |
| Type-hint sur interface | Découplage, substitution libre |
| `extends` entre interfaces | Héritage de contrats cumulatifs |
| `instanceof` | Fonctionne avec les interfaces |
| Constantes d'interface | Accessibles via l'interface ET la classe |

---

*Chapitre terminé.*

---

## Chapitre 5 — Anonymous Functions and Closures

### 5.1 Fonction anonyme (closure classique)

```php
$multiplier = function(int $a, int $b): int {
    return $a * $b;
};

echo $multiplier(3, 4); // 12
```

---

### 5.2 Capture de variables : `use`

Une closure ne capture **pas** automatiquement les variables extérieures — il faut les déclarer avec `use`.

```php
$tva = 0.20;

$calculerTTC = function(float $ht) use ($tva): float {
    return $ht * (1 + $tva); // $tva capturé par valeur (copie)
};

// Capture par référence
$compteur = 0;
$incrementer = function() use (&$compteur): void {
    $compteur++;
};
```

---

### 5.3 Arrow function `fn` (PHP 7.4+)

- Syntaxe courte : `fn(params) => expression`
- Capture **automatiquement** les variables extérieures par valeur (pas de `use`)
- Corps = une seule expression (pas de `return` explicite)

```php
$tva = 0.20;
$ttc = fn(float $ht): float => $ht * (1 + $tva); // $tva capturé auto

$doubles = array_map(fn(int $n) => $n * 2, [1, 2, 3]); // [2, 4, 6]
```

---

### 5.4 Différences closure vs arrow function

| | Closure `function` | Arrow function `fn` |
|---|---|---|
| Capture | Manuelle (`use`) | Automatique par valeur |
| Corps | Plusieurs lignes | Une seule expression |
| `return` | Obligatoire | Implicite |
| Capture par référence | `use (&$var)` | Impossible |

---

### 5.5 Formes de callable en PHP

```php
// Fonction nommée
$cb = 'strtoupper';

// Méthode statique
$cb = [MaClasse::class, 'methodeStatique'];

// Méthode d'instance
$cb = [$objet, 'methode'];

// First Class Callable (PHP 8.1)
$cb = $objet->methode(...);

// Objet __invoke
$cb = new MonObjetCallable();
```

---

### Points clés pour la certification

| Concept | À retenir |
|---|---|
| `use ($var)` | Copie par valeur |
| `use (&$var)` | Référence (modifie l'original) |
| Arrow `fn` | Capture auto, corps = 1 expression |
| `Closure::bind` | Lie une closure à un objet (accès aux privés) |
| `__invoke` | Rend un objet appelable comme fonction |

---

*Chapitre terminé.*

---

## Chapitre 6 — Abstract Classes

### 6.1 Définition

Une classe abstraite est un **intermédiaire** entre une interface (contrat pur) et une classe concrète (logique complète). Elle peut contenir des méthodes avec **et** sans corps.

```php
abstract class Notification
{
    abstract protected function envoyer(): bool; // sans corps — obligation pour l'enfant

    public function envoyerAvecLog(): bool       // avec corps — partagé par tous
    {
        echo "[LOG] Envoi via " . static::class . "\n";
        return $this->envoyer();
    }
}
```

> `new Notification()` → Fatal Error : cannot instantiate abstract class.

---

### 6.2 Règles importantes

- Une classe abstraite **ne peut pas** être instanciée directement.
- Si une méthode est `abstract`, la classe doit l'être aussi.
- Une classe enfant **doit** implémenter toutes les méthodes abstraites — sinon elle doit être abstraite elle-même.
- `final` sur une méthode concrète empêche la surcharge.

---

### 6.3 Abstract vs Interface

| | Interface | Classe abstraite |
|---|---|---|
| Logique | ✗ Aucune | ✓ Méthodes concrètes possibles |
| Propriétés | ✗ | ✓ |
| Constructeur | ✗ | ✓ |
| Héritage multiple | ✓ (`implements` N interfaces) | ✗ (1 seul `extends`) |
| Usage | "peut faire X" | "est un X, partage du code" |

---

### 6.4 Template Method Pattern

Pattern courant avec les classes abstraites : la méthode publique orchestre l'algorithme, les étapes sont déléguées aux sous-classes.

```php
abstract class ImportateurDonnees
{
    final public function importer(string $source): int  // algo fixé
    {
        $data = $this->lire($source);
        $data = $this->valider($data);
        return $this->persister($data);
    }

    abstract protected function lire(string $source): array;
    abstract protected function valider(array $data): array;
    abstract protected function persister(array $data): int;
}
```

> Symfony `Command` et `AbstractType` (formulaires) suivent ce pattern.

---

### Points clés pour la certification

| Concept | À retenir |
|---|---|
| `abstract class` | Non instanciable directement |
| `abstract method` | Pas de corps, implémentation obligatoire |
| `final` méthode | Empêche la surcharge |
| `parent::` | Appelle la version parente |
| Symfony | `AbstractController`, `Command`, `AbstractType` |

---

*Chapitre terminé.*

---

## Chapitre 7 — Exception and Error Handling

### 7.1 Hiérarchie Throwable

```
Throwable
├── Error          (erreurs moteur PHP)
│   ├── TypeError
│   ├── ValueError
│   ├── DivisionByZeroError
│   └── ParseError
└── Exception      (exceptions applicatives)
    ├── LogicException
    │   ├── InvalidArgumentException
    │   └── BadMethodCallException
    └── RuntimeException
        └── UnexpectedValueException
```

> `catch(\Throwable)` attrape **tout** — à utiliser uniquement en dernier recours.

---

### 7.2 try / catch / finally

```php
try {
    $resultat = diviser(10, 0);
} catch (\InvalidArgumentException $e) {
    echo $e->getMessage();
} catch (\RuntimeException | \LogicException $e) { // multi-catch PHP 8
    echo $e->getMessage();
} finally {
    // Toujours exécuté (libération de ressources)
}
```

---

### 7.3 Créer ses propres exceptions

```php
class ProduitNonTrouveException extends \RuntimeException
{
    public function __construct(int $id, ?\Throwable $previous = null)
    {
        parent::__construct("Produit #{$id} introuvable.", 404, $previous);
    }
}
```

- Hériter de `RuntimeException` pour les erreurs à l'exécution
- Hériter de `LogicException` pour les bugs du développeur

---

### 7.4 Exception chaining

```php
try {
    // erreur bas niveau
} catch (\RuntimeException $e) {
    throw new ServiceIndisponibleException("Service KO.", previous: $e);
}

// Plus tard
$e->getPrevious(); // retrouver la cause originale
```

---

### 7.5 Exceptions Symfony

```php
// Dans un contrôleur
throw $this->createNotFoundException("Produit introuvable."); // → HTTP 404
throw new AccessDeniedHttpException();                        // → HTTP 403
throw new HttpException(503, "Service indisponible.");        // → HTTP 503
```

Symfony convertit automatiquement les exceptions non catchées en réponse HTTP.
L'événement `KernelEvents::EXCEPTION` permet d'intercepter et personnaliser.

---

### Points clés pour la certification

| Concept | À retenir |
|---|---|
| `Throwable` | Interface commune Exception + Error |
| `finally` | Toujours exécuté, même après `return` |
| Multi-catch `\|` | Depuis PHP 8.0 |
| `$previous` | Chaîner les exceptions, `getPrevious()` |
| `TypeError` / `ValueError` | Attrapables avec `catch(\Error)` |
| Symfony | `NotFoundHttpException` → 404, `AccessDeniedHttpException` → 403 |

---

*Chapitre terminé.*

---

## Chapitre 8 — Traits

### 8.1 Définition

Un trait est un mécanisme de **réutilisation horizontale** : il injecte des méthodes dans une classe sans héritage. PHP ne supportant que l'héritage simple, les traits permettent de composer des comportements.

```php
trait Horodatable
{
    private \DateTimeImmutable $creeLe;

    public function initialiserHorodatage(): void
    {
        $this->creeLe = new \DateTimeImmutable();
    }

    public function getCreeLe(): \DateTimeImmutable { return $this->creeLe; }
}

class Article
{
    use Horodatable; // les méthodes du trait sont "copiées" ici
}
```

---

### 8.2 Utiliser plusieurs traits

```php
class Article
{
    use Horodatable, SoftDeletable; // séparés par une virgule
}
```

---

### 8.3 Résolution de conflits

Quand deux traits ont une méthode du même nom :

```php
class MonService
{
    use TraitA, TraitB {
        TraitA::log insteadof TraitB; // choisir TraitA
        TraitB::log as logB;          // garder TraitB sous un alias
        TraitA::init as private;      // changer la visibilité
    }
}
```

---

### 8.4 Méthodes abstraites dans un trait

```php
trait Validable
{
    abstract protected function getRegles(): array; // la classe doit l'implémenter

    public function valider(array $data): bool
    {
        foreach ($this->getRegles() as $champ => $regle) {
            if ($regle === 'required' && empty($data[$champ])) return false;
        }
        return true;
    }
}
```

---

### 8.5 Traits dans Symfony / Doctrine

```php
// Doctrine Extensions (gedmo/doctrine-extensions)
class Article
{
    use TimestampableEntity;  // createdAt / updatedAt gérés automatiquement
    use SoftDeleteableEntity; // deletedAt, soft delete
}
```

---

### Points clés pour la certification

| Concept | À retenir |
|---|---|
| `use TraitA, TraitB` | Plusieurs traits dans une classe |
| `insteadof` | Choisir une méthode en cas de conflit |
| `as` | Alias ou changement de visibilité |
| Méthode abstraite dans trait | La classe doit l'implémenter |
| Trait dans un trait | Possible via `use` |

---

*Chapitre terminé.*

---

## Chapitre 9 — Enums (PHP 8.1)

### 9.1 Pure Enum (sans valeur scalaire)

```php
enum Direction
{
    case Nord;
    case Sud;
    case Est;
    case Ouest;
}

$dir = Direction::Nord;
$dir === Direction::Nord; // true (comparaison ===)
```

---

### 9.2 Backed Enum (avec valeur `string` ou `int`)

```php
enum Statut: string
{
    case EnAttente = 'en_attente';
    case Termine   = 'termine';
}

Statut::Termine->value;          // 'termine'
Statut::Termine->name;           // 'Termine'
Statut::from('termine');         // Statut::Termine (ValueError si inconnu)
Statut::tryFrom('inconnu');      // null (pas d'exception)
Statut::cases();                 // tableau de toutes les cases
```

---

### 9.3 Méthodes et interfaces

```php
enum CouleurFeu: string
{
    case Rouge = 'rouge';
    case Vert  = 'vert';

    public function libelle(): string
    {
        return match($this) {
            self::Rouge => 'STOP',
            self::Vert  => 'GO',
        };
    }
}

// Un Enum peut aussi implémenter une interface
enum TypeContrat: string implements MonInterface { ... }
```

---

### 9.4 Enums et Doctrine

```php
#[ORM\Column(enumType: Statut::class)]
private Statut $statut = Statut::EnAttente;
// Doctrine stocke la valeur scalaire ('en_attente') et reconstruit l'Enum via ::from()
```

---

### Points clés pour la certification

| Concept | À retenir |
|---|---|
| Pure Enum | Pas de valeur scalaire, comparaison `===` |
| Backed Enum | `string` ou `int`, `->value`, `->name` |
| `::from()` | Lève `ValueError` si inconnu |
| `::tryFrom()` | Retourne `null` si inconnu |
| `::cases()` | Toutes les cases dans un tableau |
| Doctrine | `enumType:` stocke la valeur scalaire |

---

*Chapitre terminé.*

---

*Prochain chapitre : **HTTP — Specification RFC 9110***
