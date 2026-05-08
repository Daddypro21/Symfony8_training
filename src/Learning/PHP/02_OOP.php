<?php

declare(strict_types=1);

/**
 * ============================================================
 * NOTION 02 — Object Oriented Programming (OOP)
 * Certification Symfony 8
 * ============================================================
 *
 * Symfony est entièrement basé sur l'OOP. Maîtriser ces concepts
 * est indispensable pour comprendre le framework en profondeur.
 */

namespace App\Learning\PHP;

// ─────────────────────────────────────────────────────────────
// 1. CLASSES, PROPRIÉTÉS ET MÉTHODES
// ─────────────────────────────────────────────────────────────
// Une classe est un modèle (blueprint) pour créer des objets.
// Chaque objet est une instance de cette classe.

class Animal
{
    // Propriétés : données de l'objet
    private string $nom;
    protected int  $age;        // accessible dans les classes enfants

    // Constructeur : appelé automatiquement à la création de l'objet
    public function __construct(string $nom, int $age)
    {
        $this->nom = $nom;
        $this->age = $age;
    }

    // Méthode publique : l'interface de l'objet
    public function getNom(): string
    {
        return $this->nom;
    }

    // Méthode "magique" : appelée quand on utilise echo $objet
    public function __toString(): string
    {
        return sprintf('%s (%d ans)', $this->nom, $this->age);
    }
}

$chat = new Animal('Mimi', 3);
echo $chat->getNom();  // 'Mimi'
echo $chat;            // 'Mimi (3 ans)'


// ─────────────────────────────────────────────────────────────
// 2. HÉRITAGE (extends)
// ─────────────────────────────────────────────────────────────
// Une classe enfant hérite de toutes les propriétés/méthodes
// publiques ET protégées de la classe parente.
// Elle peut les surcharger (override).

class Chien extends Animal
{
    private string $race;

    public function __construct(string $nom, int $age, string $race)
    {
        parent::__construct($nom, $age); // appel du constructeur parent
        $this->race = $race;
    }

    // Surcharge de la méthode parente
    public function __toString(): string
    {
        // parent:: pour appeler la version de la classe parente
        return parent::__toString() . " — Race : {$this->race}";
    }

    public function aboyer(): string
    {
        // $this->age accessible car 'protected' dans Animal
        return $this->age > 2 ? 'WOOF!' : 'waf waf';
    }
}

$rex = new Chien('Rex', 5, 'Berger Allemand');
echo $rex;           // 'Rex (5 ans) — Race : Berger Allemand'
echo $rex->aboyer(); // 'WOOF!'


// ─────────────────────────────────────────────────────────────
// 3. VISIBILITÉ : public, protected, private
// ─────────────────────────────────────────────────────────────
//
// public    → accessible depuis partout
// protected → accessible depuis la classe ET ses enfants
// private   → accessible UNIQUEMENT depuis la classe elle-même
//
// Règle d'or Symfony : propriétés = private ou protected
//                      méthodes publiques = l'interface du service

class Compte
{
    private float $solde;           // personne ne peut y accéder directement
    protected string $devise;       // les classes enfants peuvent y accéder

    public function __construct(float $soldeInitial, string $devise = 'EUR')
    {
        $this->solde  = $soldeInitial;
        $this->devise = $devise;
    }

    public function getSolde(): float       // getter public
    {
        return $this->solde;
    }

    private function validerMontant(float $montant): void  // helper interne
    {
        if ($montant <= 0) {
            throw new \InvalidArgumentException('Montant invalide.');
        }
    }

    public function deposer(float $montant): void
    {
        $this->validerMontant($montant); // appel méthode privée interne
        $this->solde += $montant;
    }

    public function retirer(float $montant): void
    {
        $this->validerMontant($montant);
        if ($montant > $this->solde) {
            throw new \RuntimeException('Solde insuffisant.');
        }
        $this->solde -= $montant;
    }
}


// ─────────────────────────────────────────────────────────────
// 4. MÉTHODES ET PROPRIÉTÉS STATIQUES
// ─────────────────────────────────────────────────────────────
// Les membres statiques appartiennent à la CLASSE, pas à une instance.
// On y accède avec :: (paamayim nekudotayim = double deux-points).

class Compteur
{
    private static int $instances = 0; // partagé entre toutes les instances

    public function __construct()
    {
        self::$instances++; // self:: = la classe actuelle
    }

    public static function getNombreInstances(): int
    {
        return self::$instances;
    }
}

new Compteur();
new Compteur();
new Compteur();
echo Compteur::getNombreInstances(); // 3 — sans créer d'instance

// self:: vs static:: (Late Static Binding)
class Base
{
    public static function creer(): static // 'static' = la classe appelante
    {
        return new static(); // crée une instance de la vraie classe appelée
    }

    public static function getNom(): string
    {
        return static::class; // le nom de la classe réelle au moment de l'appel
    }
}

class Enfant extends Base {}

$b = Base::creer();   // instance de Base
$e = Enfant::creer(); // instance de Enfant (grâce à 'static')


// ─────────────────────────────────────────────────────────────
// 5. CLASSES ABSTRAITES (abstract)
// ─────────────────────────────────────────────────────────────
// (Voir la notion 06 pour le détail complet)
// Une classe abstraite NE PEUT PAS être instanciée directement.
// Elle peut contenir des méthodes abstraites (sans corps) que
// les enfants DOIVENT implémenter.

abstract class FormeGeometrique
{
    abstract public function calculerAire(): float;     // DOIT être implémentée
    abstract public function calculerPerimetre(): float;

    // Méthode concrète partagée par tous les enfants
    public function decrire(): string
    {
        return sprintf(
            'Aire : %.2f | Périmètre : %.2f',
            $this->calculerAire(),
            $this->calculerPerimetre(),
        );
    }
}

class Cercle extends FormeGeometrique
{
    public function __construct(private float $rayon) {}

    public function calculerAire(): float
    {
        return M_PI * $this->rayon ** 2;
    }

    public function calculerPerimetre(): float
    {
        return 2 * M_PI * $this->rayon;
    }
}

$c = new Cercle(5);
echo $c->decrire(); // 'Aire : 78.54 | Périmètre : 31.42'
// new FormeGeometrique(); // Fatal Error : cannot instantiate abstract class


// ─────────────────────────────────────────────────────────────
// 6. INTERFACES
// ─────────────────────────────────────────────────────────────
// (Voir la notion 04 pour le détail complet)
// Une interface définit un CONTRAT : les méthodes que toute classe
// implémentant cette interface DOIT avoir.
// Différence clé avec abstract : une classe peut implémenter
// PLUSIEURS interfaces, mais n'hériter que d'UNE seule classe.

interface Exportable
{
    public function exporterJSON(): string;
    public function exporterCSV(): string;
}

interface Importable
{
    public function importer(string $data): void;
}

// Une classe peut implémenter plusieurs interfaces
class RapportVentes implements Exportable, Importable
{
    private array $lignes = [];

    public function exporterJSON(): string
    {
        return json_encode($this->lignes, JSON_THROW_ON_ERROR);
    }

    public function exporterCSV(): string
    {
        return implode("\n", array_map(
            fn(array $l) => implode(',', $l),
            $this->lignes
        ));
    }

    public function importer(string $data): void
    {
        $this->lignes = json_decode($data, true, 512, JSON_THROW_ON_ERROR);
    }
}

// Type-hint sur interface : on accepte TOUT objet exportable
function envoyerParEmail(Exportable $rapport, string $destinataire): void
{
    $json = $rapport->exporterJSON();
    // ... envoi email
}


// ─────────────────────────────────────────────────────────────
// 7. TRAITS
// ─────────────────────────────────────────────────────────────
// (Voir la notion 08 pour le détail complet)
// Un trait est un ensemble de méthodes réutilisables qu'on peut
// "injecter" dans n'importe quelle classe.
// Résout le problème d'héritage multiple.

trait Horodatable
{
    private \DateTimeImmutable $creeLe;
    private \DateTimeImmutable $modifieLe;

    public function initialiserHorodatage(): void
    {
        $this->creeLe    = new \DateTimeImmutable();
        $this->modifieLe = new \DateTimeImmutable();
    }

    public function mettreAJourHorodatage(): void
    {
        $this->modifieLe = new \DateTimeImmutable();
    }

    public function getCreeLe(): \DateTimeImmutable  { return $this->creeLe; }
    public function getModifieLe(): \DateTimeImmutable { return $this->modifieLe; }
}

trait SoftDeletable
{
    private ?\DateTimeImmutable $supprimeLe = null;

    public function supprimer(): void
    {
        $this->supprimeLe = new \DateTimeImmutable();
    }

    public function estSupprime(): bool
    {
        return $this->supprimeLe !== null;
    }
}

// La classe utilise les deux traits comme si les méthodes lui appartenaient
class Article
{
    use Horodatable, SoftDeletable; // injection des deux traits

    public function __construct(private string $titre)
    {
        $this->initialiserHorodatage();
    }
}

$article = new Article('Mon premier article');
$article->supprimer();
var_dump($article->estSupprime()); // true


// ─────────────────────────────────────────────────────────────
// 8. MÉTHODES MAGIQUES IMPORTANTES
// ─────────────────────────────────────────────────────────────
// PHP appelle ces méthodes automatiquement dans certaines situations.
// Symfony les utilise intensivement (ex: __invoke pour les callables).

class MaCollection
{
    private array $elements = [];

    // Appelé quand on utilise l'objet comme une fonction : $obj()
    public function __invoke(mixed $element): static
    {
        $this->elements[] = $element;
        return $this; // fluent interface
    }

    // Appelé lors de var_dump() ou dump() (Symfony)
    public function __debugInfo(): array
    {
        return ['count' => count($this->elements), 'elements' => $this->elements];
    }

    // Appelé lors du clone
    public function __clone(): void
    {
        // deep clone si nécessaire
    }
}

$collection = new MaCollection();
$collection('item1')('item2')('item3'); // fonctionne grâce à __invoke + fluent


// ─────────────────────────────────────────────────────────────
// 9. CONSTANTES DE CLASSE
// ─────────────────────────────────────────────────────────────
// Les constantes appartiennent à la classe, pas à l'instance.
// Elles ne peuvent pas être modifiées.
// En PHP 8.3, elles peuvent être typées.

class HttpStatus
{
    const int OK                = 200;
    const int CREATED           = 201;
    const int NOT_FOUND         = 404;
    const int INTERNAL_ERROR    = 500;

    // Constante utilisant d'autres constantes
    const array SUCCESS_CODES   = [self::OK, self::CREATED];
}

$code = HttpStatus::NOT_FOUND; // 404 — accès via ::

// Dans un enfant, on peut surcharger une constante
class HttpStatusEtendu extends HttpStatus
{
    const int TEAPOT = 418; // je suis une théière !
}


// ─────────────────────────────────────────────────────────────
// 10. LATE STATIC BINDING (LSB)
// ─────────────────────────────────────────────────────────────
// static:: résout au moment de l'APPEL (classe réelle).
// self::   résout au moment de la DÉFINITION (classe où c'est écrit).
// Crucial pour les patterns comme le Repository de Doctrine.

class Repository
{
    // self::class retournerait toujours 'Repository'
    // static::class retourne la classe réellement appelée
    public static function getTableName(): string
    {
        return strtolower(static::class); // Late Static Binding
    }
}

class UserRepository extends Repository {}
class ProductRepository extends Repository {}

echo UserRepository::getTableName();    // 'userrepository'
echo ProductRepository::getTableName(); // 'productrepository'


// ─────────────────────────────────────────────────────────────
// RÉSUMÉ — Points clés pour la certification
// ─────────────────────────────────────────────────────────────
//
// ✅ public/protected/private : connaître les règles d'accès
// ✅ extends : héritage simple uniquement, parent:: pour appeler le parent
// ✅ abstract : pas instanciable, méthodes abstraites = contrat forcé
// ✅ interface : contrat pur, héritage multiple possible
// ✅ trait : mixin de méthodes, résout l'héritage multiple
// ✅ static/self : static:: = classe appelante, self:: = classe écrite
// ✅ __invoke : rend un objet appelable comme une fonction
// ✅ const typées PHP 8.3 : const int X = 1;
