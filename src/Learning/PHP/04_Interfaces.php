<?php

declare(strict_types=1);

/**
 * ============================================================
 * NOTION 04 — Interfaces
 * Certification Symfony 8
 * ============================================================
 *
 * Une interface définit un CONTRAT : la liste des méthodes qu'une
 * classe doit implémenter. Elle ne contient aucune logique.
 *
 * Pourquoi les interfaces sont essentielles dans Symfony ?
 *   - Le conteneur de services travaille avec des interfaces, pas des classes concrètes
 *   - On peut substituer une implémentation par une autre sans changer le code appelant
 *   - Les interfaces permettent de type-hinter sans se coupler à une classe précise
 */

namespace App\Learning\PHP;

// ─────────────────────────────────────────────────────────────
// 1. DÉCLARER UNE INTERFACE
// ─────────────────────────────────────────────────────────────
// Toutes les méthodes d'une interface sont implicitement publiques
// et ne peuvent pas avoir de corps (sauf les méthodes par défaut — PHP 8).

interface LoggerInterface
{
    public function log(string $niveau, string $message): void;
    public function info(string $message): void;
    public function error(string $message): void;
}

interface FormatterInterface
{
    public function format(string $message): string;
}


// ─────────────────────────────────────────────────────────────
// 2. IMPLÉMENTER UNE INTERFACE (implements)
// ─────────────────────────────────────────────────────────────
// La classe DOIT implémenter TOUTES les méthodes déclarées.
// Si une méthode manque → Fatal Error à la compilation.

class FileLogger implements LoggerInterface
{
    private string $fichier;

    public function __construct(string $fichier)
    {
        $this->fichier = $fichier;
    }

    public function log(string $niveau, string $message): void
    {
        file_put_contents(
            $this->fichier,
            sprintf("[%s] %s : %s\n", date('Y-m-d H:i:s'), strtoupper($niveau), $message),
            FILE_APPEND,
        );
    }

    public function info(string $message): void
    {
        $this->log('info', $message);
    }

    public function error(string $message): void
    {
        $this->log('error', $message);
    }
}

class ConsoleLogger implements LoggerInterface
{
    public function log(string $niveau, string $message): void
    {
        echo sprintf("[%s] %s\n", strtoupper($niveau), $message);
    }

    public function info(string $message): void
    {
        $this->log('info', $message);
    }

    public function error(string $message): void
    {
        $this->log('error', $message);
    }
}


// ─────────────────────────────────────────────────────────────
// 3. TYPE-HINTING SUR INTERFACE = découplage
// ─────────────────────────────────────────────────────────────
// La fonction ci-dessous accepte N'IMPORTE quelle implémentation
// de LoggerInterface. On n'est pas couplé à FileLogger ni ConsoleLogger.

class CommandeService
{
    public function __construct(
        private LoggerInterface $logger, // type-hint sur l'interface
    ) {}

    public function passerCommande(int $idProduit, int $quantite): void
    {
        // ... logique métier ...
        $this->logger->info("Commande passée : produit #{$idProduit}, qté {$quantite}");
    }
}

// On peut passer FileLogger ou ConsoleLogger — le service ne le sait pas
$service1 = new CommandeService(new ConsoleLogger());
$service2 = new CommandeService(new FileLogger('/tmp/app.log'));

$service1->passerCommande(42, 3); // affiche dans la console
// $service2->passerCommande(42, 3); // écrit dans le fichier


// ─────────────────────────────────────────────────────────────
// 4. IMPLÉMENTER PLUSIEURS INTERFACES
// ─────────────────────────────────────────────────────────────
// Une classe peut implémenter plusieurs interfaces (contrairement à l'héritage).
// C'est ce qui permet la composition de comportements.

interface Exportable
{
    public function exporterJSON(): string;
    public function exporterCSV(): string;
}

interface Importable
{
    public function importer(string $donnees): static;
}

interface Validable
{
    public function estValide(): bool;
    public function getErreurs(): array;
}

// La classe implémente les trois contrats
class ProfilUtilisateur implements Exportable, Importable, Validable
{
    private array $erreurs = [];

    public function __construct(
        private string $email = '',
        private string $nom   = '',
    ) {}

    public function exporterJSON(): string
    {
        return json_encode(['email' => $this->email, 'nom' => $this->nom], JSON_THROW_ON_ERROR);
    }

    public function exporterCSV(): string
    {
        return "{$this->email},{$this->nom}";
    }

    public function importer(string $donnees): static
    {
        $data        = json_decode($donnees, true, 512, JSON_THROW_ON_ERROR);
        $nouveau     = clone $this;
        $nouveau->email = $data['email'] ?? '';
        $nouveau->nom   = $data['nom']   ?? '';
        return $nouveau;
    }

    public function estValide(): bool
    {
        $this->erreurs = [];

        if (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            $this->erreurs[] = 'Email invalide.';
        }
        if (strlen($this->nom) < 2) {
            $this->erreurs[] = 'Nom trop court.';
        }

        return $this->erreurs === [];
    }

    public function getErreurs(): array
    {
        return $this->erreurs;
    }
}

$profil = new ProfilUtilisateur('alice@example.com', 'Alice');
var_dump($profil->estValide()); // true
echo $profil->exporterJSON();   // {"email":"alice@example.com","nom":"Alice"}


// ─────────────────────────────────────────────────────────────
// 5. HÉRITAGE D'INTERFACES (extends entre interfaces)
// ─────────────────────────────────────────────────────────────
// Une interface peut étendre une ou plusieurs autres interfaces.
// La classe implémentant l'interface fille doit couvrir TOUT le contrat.

interface ReadableRepositoryInterface
{
    public function find(int $id): ?object;
    public function findAll(): array;
}

interface WritableRepositoryInterface
{
    public function save(object $entite): void;
    public function delete(int $id): void;
}

// Interface composite : hérite des deux
interface RepositoryInterface extends ReadableRepositoryInterface, WritableRepositoryInterface
{
    public function findBy(array $criteres): array;
}

// La classe doit implémenter find, findAll, save, delete ET findBy
class InMemoryUserRepository implements RepositoryInterface
{
    private array $stockage = [];
    private int   $compteur = 0;

    public function find(int $id): ?object
    {
        return $this->stockage[$id] ?? null;
    }

    public function findAll(): array
    {
        return array_values($this->stockage);
    }

    public function save(object $entite): void
    {
        $this->compteur++;
        $this->stockage[$this->compteur] = $entite;
    }

    public function delete(int $id): void
    {
        unset($this->stockage[$id]);
    }

    public function findBy(array $criteres): array
    {
        return array_filter($this->stockage, function (object $e) use ($criteres) {
            foreach ($criteres as $prop => $valeur) {
                if (!property_exists($e, $prop) || $e->$prop !== $valeur) {
                    return false;
                }
            }
            return true;
        });
    }
}


// ─────────────────────────────────────────────────────────────
// 6. CONSTANTES DANS LES INTERFACES
// ─────────────────────────────────────────────────────────────
// Une interface peut déclarer des constantes. Elles sont accessibles
// depuis la classe qui l'implémente et via l'interface elle-même.

interface NiveauxPrioritesInterface
{
    const int BASSE    = 1;
    const int NORMALE  = 2;
    const int HAUTE    = 3;
    const int CRITIQUE = 4;
}

class Tache implements NiveauxPrioritesInterface
{
    public function __construct(
        public readonly string $titre,
        public readonly int    $priorite = self::NORMALE,
    ) {}
}

$t1 = new Tache('Rédiger la doc', NiveauxPrioritesInterface::HAUTE);
$t2 = new Tache('Réunion', Tache::CRITIQUE); // accessible via la classe aussi


// ─────────────────────────────────────────────────────────────
// 7. INSTANCEOF AVEC LES INTERFACES
// ─────────────────────────────────────────────────────────────
// instanceof fonctionne avec les interfaces, pas seulement les classes.
// Très utilisé pour les vérifications de type à l'exécution.

function traiter(object $objet): void
{
    if ($objet instanceof LoggerInterface) {
        $objet->info('Traitement en cours');
    }

    if ($objet instanceof Exportable) {
        $json = $objet->exporterJSON();
        echo "Export : {$json}\n";
    }

    if ($objet instanceof Validable) {
        if (!$objet->estValide()) {
            echo "Erreurs : " . implode(', ', $objet->getErreurs()) . "\n";
        }
    }
}


// ─────────────────────────────────────────────────────────────
// 8. INTERFACES ET INJECTION DE DÉPENDANCES SYMFONY
// ─────────────────────────────────────────────────────────────
// Dans Symfony, le conteneur de services résout les interfaces
// automatiquement grâce à l'autowiring.
// Si une seule classe implémente LoggerInterface → elle est injectée.
// Si plusieurs classes l'implémentent → on utilise #[Autowire] ou un alias.

/*
// config/services.yaml — alias explicite
services:
    App\Contract\LoggerInterface: '@App\Service\FileLogger'

// OU directement dans la classe via l'attribut
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class MonController
{
    public function __construct(
        #[Autowire(service: 'App\Service\ConsoleLogger')]
        private LoggerInterface $logger,
    ) {}
}
*/


// ─────────────────────────────────────────────────────────────
// RÉSUMÉ — Points clés pour la certification
// ─────────────────────────────────────────────────────────────
//
// ✅ interface = contrat pur, aucune logique, méthodes implicitement publiques
// ✅ implements : une classe peut implémenter N interfaces
// ✅ extends entre interfaces : héritage de contrats cumulatifs
// ✅ Type-hinter sur l'interface → couplage faible, substitution facile
// ✅ instanceof fonctionne avec les interfaces
// ✅ Constantes d'interface : accessibles via l'interface ET la classe
// ✅ Symfony autowire sur les interfaces → une seule implémentation = auto-résolution
// ✅ Plusieurs implémentations → alias dans services.yaml ou #[Autowire]
