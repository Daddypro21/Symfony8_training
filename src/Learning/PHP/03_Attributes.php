<?php

declare(strict_types=1);

/**
 * ============================================================
 * NOTION 03 — Attributes (PHP 8.x)
 * Certification Symfony 8
 * ============================================================
 *
 * Les Attributes PHP 8 remplacent les annotations Doctrine/PHPDoc.
 * Dans Symfony 8, ils sont utilisés PARTOUT :
 *   - Routing          #[Route('/chemin')]
 *   - Entités Doctrine #[ORM\Entity], #[ORM\Column]
 *   - Validation       #[Assert\NotBlank]
 *   - DI               #[Autowire], #[AsService]
 *   - Console          #[AsCommand]
 *   - Sécurité         #[IsGranted]
 */

namespace App\Learning\PHP;

use Attribute;

// ─────────────────────────────────────────────────────────────
// 1. QU'EST-CE QU'UN ATTRIBUTE ?
// ─────────────────────────────────────────────────────────────
// Un Attribute est une classe PHP ordinaire marquée avec #[Attribute].
// On l'applique ensuite sur une classe, méthode, propriété, etc.
// via la syntaxe #[NomDeLAttribute(args...)].
//
// Avant PHP 8, on utilisait les annotations en commentaires PHPDoc :
//   /** @Route("/chemin") */     ← ancien style (Doctrine Annotations)
//
// Depuis PHP 8 :
//   #[Route('/chemin')]          ← natif PHP, analysé par le moteur


// ─────────────────────────────────────────────────────────────
// 2. CRÉER SON PROPRE ATTRIBUTE
// ─────────────────────────────────────────────────────────────

// Étape 1 : Déclarer la classe avec #[Attribute]
// Le flag indique OÙ cet attribute peut être appliqué.

#[Attribute(Attribute::TARGET_CLASS)]                // uniquement sur les classes
class EntiteMetier {}

#[Attribute(Attribute::TARGET_METHOD)]               // uniquement sur les méthodes
class RequiertAuthentification {}

#[Attribute(Attribute::TARGET_PROPERTY)]             // uniquement sur les propriétés
class Masquer {}

#[Attribute(Attribute::TARGET_PARAMETER)]            // uniquement sur les paramètres
class ValiderFormat {}

// On peut combiner les flags avec |
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
class Loggable {}

// IS_REPEATABLE : permet d'appliquer l'attribute plusieurs fois au même endroit
#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class Cache
{
    public function __construct(
        public readonly string $cle,
        public readonly int    $ttl = 3600,
    ) {}
}


// ─────────────────────────────────────────────────────────────
// 3. ATTRIBUTE AVEC ARGUMENTS
// ─────────────────────────────────────────────────────────────
// Un attribute est une classe normale : son constructeur reçoit les arguments.

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
class Route
{
    public function __construct(
        public readonly string $path,
        public readonly string $name    = '',
        public readonly array  $methods = ['GET'],
    ) {}
}

#[Attribute(Attribute::TARGET_CLASS)]
class Controller {}


// ─────────────────────────────────────────────────────────────
// 4. APPLIQUER DES ATTRIBUTES
// ─────────────────────────────────────────────────────────────

// Sur une classe
#[Controller]
#[Route('/produits', name: 'produit_')]   // arguments nommés possibles
class ProduitController
{
    // Sur une méthode — avec IS_REPEATABLE on peut en mettre plusieurs
    #[Route('/liste', name: 'liste', methods: ['GET'])]
    #[Cache(cle: 'produits_liste', ttl: 600)]
    #[Cache(cle: 'produits_backup', ttl: 60)]   // possible car IS_REPEATABLE
    public function liste(): string
    {
        return 'liste des produits';
    }

    #[Route('/nouveau', name: 'nouveau', methods: ['GET', 'POST'])]
    #[RequiertAuthentification]
    public function nouveau(): string
    {
        return 'formulaire produit';
    }
}

// Sur les propriétés d'une classe
class UtilisateurDTO
{
    #[Masquer]
    private string $motDePasse = '';

    public string $email = '';
}

// Sur les paramètres d'une fonction
function creerUtilisateur(
    #[ValiderFormat] string $email,
    string $nom,
): void {}


// ─────────────────────────────────────────────────────────────
// 5. LIRE LES ATTRIBUTES VIA RÉFLEXION (ReflectionClass)
// ─────────────────────────────────────────────────────────────
// C'est ainsi que Symfony lit vos #[Route], #[ORM\Column], etc.
// à l'exécution. Le framework utilise la Reflection API de PHP.

$reflection = new \ReflectionClass(ProduitController::class);

// Lire les attributes sur la classe
$classAttributes = $reflection->getAttributes(Route::class);
foreach ($classAttributes as $attributeRef) {
    // newInstance() crée une vraie instance de l'attribute
    $route = $attributeRef->newInstance();
    echo $route->path;  // '/produits'
    echo $route->name;  // 'produit_'
}

// Lire les attributes sur une méthode
$methode = $reflection->getMethod('liste');
$methodeAttributes = $methode->getAttributes(Route::class);
foreach ($methodeAttributes as $attributeRef) {
    $route = $attributeRef->newInstance();
    echo $route->path; // '/liste'
}

// Lire les attributes répétables
$cacheAttributes = $methode->getAttributes(Cache::class);
// count($cacheAttributes) === 2  (car IS_REPEATABLE et deux #[Cache])
foreach ($cacheAttributes as $attributeRef) {
    $cache = $attributeRef->newInstance();
    echo "{$cache->cle} : {$cache->ttl}s\n";
}


// ─────────────────────────────────────────────────────────────
// 6. ATTRIBUTES SYMFONY LES PLUS IMPORTANTS
// ─────────────────────────────────────────────────────────────
// Ces attributes sont définis par Symfony — vous les UTILISEZ,
// vous ne les créez pas. Voici les plus fréquents à l'examen.

/*
// --- ROUTING ---
use Symfony\Component\Routing\Attribute\Route;

#[Route('/blog/{slug}', name: 'blog_show', methods: ['GET'])]
#[Route('/admin', name: 'admin_', host: 'admin.example.com')]

// --- DOCTRINE ORM ---
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ArticleRepository::class)]
#[ORM\Table(name: 'articles')]
class Article
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private string $titre = '';

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $publieLe = null;
}

// --- VALIDATION ---
use Symfony\Component\Validator\Constraints as Assert;

class RegistrationDTO
{
    #[Assert\NotBlank]
    #[Assert\Email]
    public string $email = '';

    #[Assert\Length(min: 8, max: 64)]
    public string $motDePasse = '';
}

// --- DEPENDENCY INJECTION ---
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

class MonService
{
    public function __construct(
        #[Autowire('%kernel.project_dir%/uploads')] // injecte un paramètre
        private string $uploadDir,

        #[Autowire(service: 'monautre.service')] // injecte un service par ID
        private AutreService $autreService,
    ) {}
}

// --- CONSOLE ---
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'app:import-produits', description: 'Importe les produits')]
class ImportProduitsCommand extends Command {}

// --- SÉCURITÉ ---
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
class AdminController extends AbstractController
{
    #[IsGranted('ROLE_SUPER_ADMIN')]
    public function supprimerUtilisateur(): Response {}
}

// --- MESSENGER ---
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class EnvoyerEmailHandler
{
    public function __invoke(EnvoyerEmailMessage $message): void {}
}
*/


// ─────────────────────────────────────────────────────────────
// 7. FLAGS D'ATTRIBUTE — RÉCAPITULATIF
// ─────────────────────────────────────────────────────────────
//
// Attribute::TARGET_CLASS       → sur une classe
// Attribute::TARGET_FUNCTION    → sur une fonction
// Attribute::TARGET_METHOD      → sur une méthode
// Attribute::TARGET_PROPERTY    → sur une propriété
// Attribute::TARGET_CLASS_CONST → sur une constante de classe
// Attribute::TARGET_PARAMETER   → sur un paramètre
// Attribute::TARGET_ALL         → partout (valeur par défaut)
// Attribute::IS_REPEATABLE      → peut être appliqué plusieurs fois


// ─────────────────────────────────────────────────────────────
// RÉSUMÉ — Points clés pour la certification
// ─────────────────────────────────────────────────────────────
//
// ✅ Un attribute = une classe PHP ordinaire marquée avec #[Attribute]
// ✅ Syntaxe : #[NomAttribute(arg1, arg2)]  (pas de guillemets autour)
// ✅ Arguments passés au constructeur de la classe attribute
// ✅ Named arguments supportés : #[Route(path: '/x', name: 'y')]
// ✅ IS_REPEATABLE pour appliquer plusieurs fois le même attribute
// ✅ ReflectionClass::getAttributes() pour lire les attributes
// ✅ $attributeRef->newInstance() pour instancier et accéder aux valeurs
// ✅ Symfony utilise les attributes pour Route, ORM, Assert, IsGranted…
// ✅ TARGET_* contrôle où l'attribute peut être appliqué
