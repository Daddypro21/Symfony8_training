<?php

declare(strict_types=1);

/**
 * ============================================================
 * NOTION 07 — Exception and Error Handling
 * Certification Symfony 8
 * ============================================================
 *
 * PHP distingue deux familles de problèmes :
 *   - Exceptions : situations anormales gérables (métier, validation…)
 *   - Errors     : problèmes graves du moteur (TypeError, ParseError…)
 *
 * Depuis PHP 7, les deux héritent de Throwable.
 * Symfony s'appuie sur ce mécanisme pour son HttpExceptionInterface,
 * ses error handlers et le composant ErrorHandler.
 */

namespace App\Learning\PHP;

// ─────────────────────────────────────────────────────────────
// 1. HIÉRARCHIE THROWABLE
// ─────────────────────────────────────────────────────────────
//
//  Throwable (interface)
//  ├── Error (erreurs moteur PHP)
//  │   ├── TypeError
//  │   ├── ValueError
//  │   ├── ArithmeticError
//  │   │   └── DivisionByZeroError
//  │   ├── ParseError
//  │   └── AssertionError
//  └── Exception (exceptions applicatives)
//      ├── LogicException
//      │   ├── BadMethodCallException
//      │   ├── DomainException
//      │   ├── InvalidArgumentException
//      │   ├── LengthException
//      │   └── OutOfRangeException
//      └── RuntimeException
//          ├── OutOfBoundsException
//          ├── OverflowException
//          ├── RangeException
//          ├── UnderflowException
//          └── UnexpectedValueException


// ─────────────────────────────────────────────────────────────
// 2. LANCER ET ATTRAPER UNE EXCEPTION
// ─────────────────────────────────────────────────────────────

function diviser(int $a, int $b): float
{
    if ($b === 0) {
        throw new \InvalidArgumentException('Division par zéro interdite.');
    }
    return $a / $b;
}

try {
    $resultat = diviser(10, 0);
} catch (\InvalidArgumentException $e) {
    echo "Erreur métier : " . $e->getMessage() . "\n"; // 'Division par zéro interdite.'
}

// Informations disponibles sur une exception
try {
    diviser(10, 0);
} catch (\Exception $e) {
    echo $e->getMessage();   // message
    echo $e->getCode();      // code numérique (0 par défaut)
    echo $e->getFile();      // fichier où throw a été appelé
    echo $e->getLine();      // ligne du throw
    echo $e->getTraceAsString(); // stack trace complète
}


// ─────────────────────────────────────────────────────────────
// 3. ATTRAPER PLUSIEURS TYPES (multi-catch)
// ─────────────────────────────────────────────────────────────

function lireConfiguration(string $chemin): array
{
    if (!file_exists($chemin)) {
        throw new \RuntimeException("Fichier introuvable : {$chemin}");
    }
    $contenu = file_get_contents($chemin);
    if ($contenu === false) {
        throw new \RuntimeException("Impossible de lire le fichier.");
    }
    $data = json_decode($contenu, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new \InvalidArgumentException("JSON invalide : " . json_last_error_msg());
    }
    return $data;
}

try {
    $config = lireConfiguration('/chemin/inexistant.json');
} catch (\InvalidArgumentException $e) {
    // Problème de format
    echo "Format invalide : " . $e->getMessage() . "\n";
} catch (\RuntimeException $e) {
    // Problème d'accès
    echo "Accès impossible : " . $e->getMessage() . "\n";
} catch (\Exception $e) {
    // Filet de sécurité pour tout autre Exception
    echo "Erreur inattendue : " . $e->getMessage() . "\n";
} finally {
    // TOUJOURS exécuté, qu'il y ait exception ou non
    echo "Tentative de lecture terminée.\n";
}

// Catch multi-type avec | (PHP 8.0+)
try {
    lireConfiguration('/mauvais.json');
} catch (\InvalidArgumentException | \RuntimeException $e) {
    echo "Erreur de config : " . $e->getMessage() . "\n";
}


// ─────────────────────────────────────────────────────────────
// 4. CRÉER SES PROPRES EXCEPTIONS
// ─────────────────────────────────────────────────────────────
// Convention : une exception par cas métier, dans un namespace dédié.
// On choisit la classe parente selon la nature du problème :
//   - LogicException  → bug du développeur (contrat non respecté)
//   - RuntimeException → problème à l'exécution (ressource indisponible)

class ProduitNonTrouveException extends \RuntimeException
{
    public function __construct(int $id, ?\Throwable $previous = null)
    {
        parent::__construct(
            message: "Produit #{$id} introuvable.",
            code: 404,
            previous: $previous,
        );
    }
}

class StockInsuffisantException extends \DomainException
{
    public function __construct(
        public readonly int $demande,
        public readonly int $disponible,
    ) {
        parent::__construct(
            "Stock insuffisant : {$demande} demandés, {$disponible} disponibles."
        );
    }
}

// Utilisation
function acheter(int $idProduit, int $quantite): void
{
    $stock = 5; // simulé

    if ($idProduit <= 0) {
        throw new ProduitNonTrouveException($idProduit);
    }
    if ($quantite > $stock) {
        throw new StockInsuffisantException($quantite, $stock);
    }
}

try {
    acheter(1, 10);
} catch (StockInsuffisantException $e) {
    echo "Demandé : {$e->demande}, dispo : {$e->disponible}\n";
} catch (ProduitNonTrouveException $e) {
    echo $e->getMessage() . " (code {$e->getCode()})\n";
}


// ─────────────────────────────────────────────────────────────
// 5. CHAÎNER LES EXCEPTIONS (exception chaining)
// ─────────────────────────────────────────────────────────────
// Le paramètre $previous permet de conserver la cause originale
// tout en levant une exception de plus haut niveau.

function envoyerEmail(string $to, string $corps): void
{
    try {
        // Simulation d'un échec réseau bas niveau
        throw new \RuntimeException("Connexion SMTP refusée (code EHLO).");
    } catch (\RuntimeException $e) {
        // On enveloppe dans une exception métier, en conservant la cause
        throw new \RuntimeException(
            "Impossible d'envoyer l'email à {$to}.",
            previous: $e, // cause originale
        );
    }
}

try {
    envoyerEmail('alice@example.com', 'Bonjour');
} catch (\RuntimeException $e) {
    echo $e->getMessage() . "\n";          // message de haut niveau
    echo $e->getPrevious()?->getMessage(); // cause originale (SMTP)
}


// ─────────────────────────────────────────────────────────────
// 6. ATTRAPER LES ERRORS PHP (TypeError, ValueError…)
// ─────────────────────────────────────────────────────────────
// En PHP 7+, les erreurs moteur sont aussi des objets Throwable.
// On peut les attraper avec catch(\Error) ou catch(\Throwable).

function calculer(int $n): int
{
    return $n * 2;
}

try {
    // @phpstan-ignore-next-line
    $resultat = calculer('pas un entier'); // TypeError si declare(strict_types=1)
} catch (\TypeError $e) {
    echo "Mauvais type : " . $e->getMessage() . "\n";
}

// Attraper TOUT avec Throwable (Exception ET Error)
try {
    $x = intdiv(1, 0); // DivisionByZeroError
} catch (\Throwable $e) {
    echo get_class($e) . " : " . $e->getMessage() . "\n";
    // DivisionByZeroError : Division by zero
}


// ─────────────────────────────────────────────────────────────
// 7. FINALLY
// ─────────────────────────────────────────────────────────────
// finally est TOUJOURS exécuté (sauf exit/die).
// Utile pour libérer des ressources (connexion BDD, fichier ouvert…).

function traiterFichier(string $chemin): string
{
    $handle = fopen($chemin, 'r');
    if ($handle === false) {
        throw new \RuntimeException("Impossible d'ouvrir {$chemin}");
    }

    try {
        return fread($handle, 1024) ?: '';
    } finally {
        fclose($handle); // exécuté même si une exception est levée dans try
    }
}

// Note : si try contient un return ET que finally aussi contient un return,
// c'est la valeur de finally qui est retournée (comportement subtil, à éviter).


// ─────────────────────────────────────────────────────────────
// 8. EXCEPTIONS DANS SYMFONY
// ─────────────────────────────────────────────────────────────
// Symfony transforme les exceptions non catchées en réponses HTTP.
// L'interface HttpExceptionInterface permet de contrôler le statut HTTP.

/*
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;

// Dans un contrôleur (AbstractController fournit createNotFoundException)
throw $this->createNotFoundException("Produit introuvable.");
// → réponse HTTP 404 en prod, page d'erreur Symfony en dev

// Ou directement
throw new NotFoundHttpException("Produit introuvable.");
throw new AccessDeniedHttpException("Accès refusé.");
throw new HttpException(statusCode: 503, message: "Service temporairement indisponible.");

// Créer sa propre HttpException
class ProduitNonDisponibleException extends HttpException
{
    public function __construct(string $message = '', ?\Throwable $previous = null)
    {
        parent::__construct(statusCode: 503, message: $message, previous: $previous);
    }
}

// Écouter les exceptions non catchées
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(event: KernelEvents::EXCEPTION)]
class MonExceptionListener
{
    public function __invoke(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        if ($exception instanceof StockInsuffisantException) {
            $event->setResponse(new JsonResponse(
                ['erreur' => $exception->getMessage()],
                Response::HTTP_UNPROCESSABLE_ENTITY,
            ));
        }
    }
}
*/


// ─────────────────────────────────────────────────────────────
// RÉSUMÉ — Points clés pour la certification
// ─────────────────────────────────────────────────────────────
//
// ✅ Throwable : interface commune à Exception et Error
// ✅ throw new Exception(...) / try / catch / finally
// ✅ Multi-catch : catch(TypeA | TypeB $e)
// ✅ finally : toujours exécuté, libération de ressources
// ✅ Exception chaining : 3e param $previous → getPrevious()
// ✅ Exceptions personnalisées : hériter de RuntimeException ou LogicException
// ✅ TypeError, ValueError, DivisionByZeroError : attrapables avec catch(\Error)
// ✅ catch(\Throwable) : attrape TOUT (Exception + Error)
// ✅ Symfony : NotFoundHttpException, AccessDeniedHttpException → réponse HTTP auto
// ✅ KernelEvents::EXCEPTION : écouter et transformer les exceptions non catchées
