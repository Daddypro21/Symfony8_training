# Cours Symfony 8 — Préparation Certification

> Fichier mis à jour progressivement au fil de la formation.
> Référence officielle : https://certification.symfony.com/exams/symfony.html

---

## Progression

| # | Notion | Statut |
|---|--------|--------|
| 01 | PHP API up to PHP 8.4 | ✅ Terminé |
| 02 | Object Oriented Programming | ✅ Terminé |
| 03 | Attributes | ✅ En cours |
| 04 | Interfaces | ⬜ |
| 05 | Anonymous functions and closures | ⬜ |
| 06 | Abstract classes | ⬜ |
| 07 | Exception and error handling | ⬜ |
| 08 | Traits | ⬜ |
| 09 | Enums | ⬜ |
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

*Prochain chapitre : **3.1 — Interfaces***


