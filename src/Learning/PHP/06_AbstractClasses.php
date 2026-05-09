<?php

declare(strict_types=1);

/**
 * ============================================================
 * NOTION 06 — Classes Abstraites
 * Certification Symfony 8
 * ============================================================
 *
 * Une classe abstraite est un intermédiaire entre une interface
 * (contrat pur, pas de logique) et une classe concrète (logique complète).
 * Elle peut contenir :
 *   - Des méthodes abstraites (contrat, sans corps)
 *   - Des méthodes concrètes (logique partagée par les enfants)
 *   - Des propriétés
 *
 * Dans Symfony : AbstractController, AbstractType (formulaires),
 * Command, AbstractFixture… sont tous des classes abstraites.
 */

namespace App\Learning\PHP;

// ─────────────────────────────────────────────────────────────
// 1. DÉCLARER UNE CLASSE ABSTRAITE
// ─────────────────────────────────────────────────────────────
// - Le mot-clé abstract interdit l'instanciation directe.
// - Les méthodes abstract définissent un contrat que les enfants DOIVENT honorer.
// - Les méthodes concrètes offrent un comportement par défaut réutilisable.

abstract class Notification
{
    // Propriétés partagées par toutes les implémentations
    protected string $destinataire;
    protected string $sujet;
    protected string $corps;

    public function __construct(string $destinataire, string $sujet, string $corps)
    {
        $this->destinataire = $destinataire;
        $this->sujet        = $sujet;
        $this->corps        = $corps;
    }

    // Méthode ABSTRAITE : chaque canal d'envoi implémente son propre envoi
    abstract protected function envoyer(): bool;

    // Méthode CONCRÈTE partagée : logique commune à tous les canaux
    public function envoyer_avec_log(): bool
    {
        echo "[LOG] Envoi à {$this->destinataire} via " . static::class . "\n";
        $resultat = $this->envoyer();
        echo "[LOG] " . ($resultat ? 'Succès' : 'Échec') . "\n";
        return $resultat;
    }

    // Méthode concrète avec comportement surchargeable
    public function getResume(): string
    {
        return "Notification → {$this->destinataire} : {$this->sujet}";
    }
}

// new Notification(...) // Fatal Error : cannot instantiate abstract class


// ─────────────────────────────────────────────────────────────
// 2. CLASSES CONCRÈTES : implémentent les méthodes abstraites
// ─────────────────────────────────────────────────────────────

class NotificationEmail extends Notification
{
    protected function envoyer(): bool
    {
        // Ici : appel à un mailer réel (Symfony Mailer par exemple)
        echo "Email envoyé à {$this->destinataire} : {$this->sujet}\n";
        return true;
    }
}

class NotificationSMS extends Notification
{
    private string $numeroTelephone;

    public function __construct(string $numero, string $sujet, string $corps)
    {
        parent::__construct($numero, $sujet, $corps); // constructeur parent obligatoire
        $this->numeroTelephone = $numero;
    }

    protected function envoyer(): bool
    {
        echo "SMS envoyé au {$this->numeroTelephone} : {$this->corps}\n";
        return true;
    }

    // Surcharge d'une méthode concrète parente
    public function getResume(): string
    {
        return parent::getResume() . " [SMS]";
    }
}

$email = new NotificationEmail('alice@example.com', 'Bienvenue', 'Bonjour Alice !');
$email->envoyer_avec_log();

$sms = new NotificationSMS('+33612345678', 'Alerte', 'Connexion suspecte détectée.');
$sms->envoyer_avec_log();
echo $sms->getResume(); // '... [SMS]'


// ─────────────────────────────────────────────────────────────
// 3. CLASSES ABSTRAITES ET HÉRITAGE EN CHAÎNE
// ─────────────────────────────────────────────────────────────
// Une classe abstraite peut étendre une autre classe abstraite
// sans implémenter toutes les méthodes abstraites (elle les délègue aux enfants).

abstract class NotificationPush extends Notification
{
    // Ajoute une méthode abstraite supplémentaire
    abstract protected function getToken(): string;

    // Implémente envoyer() avec la logique push commune
    protected function envoyer(): bool
    {
        $token = $this->getToken();
        echo "Push envoyé (token: {$token}) : {$this->corps}\n";
        return true;
    }
}

class NotificationAndroid extends NotificationPush
{
    public function __construct(
        private string $fcmToken,
        string $sujet,
        string $corps,
    ) {
        parent::__construct($fcmToken, $sujet, $corps);
    }

    protected function getToken(): string
    {
        return $this->fcmToken;
    }
}

$android = new NotificationAndroid('fcm_xyz_123', 'Promo', 'Offre spéciale !');
$android->envoyer_avec_log();


// ─────────────────────────────────────────────────────────────
// 4. ABSTRACT + INTERFACE : combinaison courante dans Symfony
// ─────────────────────────────────────────────────────────────
// Une classe abstraite peut implémenter une interface et déléguer
// certaines méthodes aux classes concrètes.

interface SerializableInterface
{
    public function serialiser(): string;
    public function deserialiser(string $data): static;
}

abstract class AbstractEntite implements SerializableInterface
{
    // Méthode concrète partagée : serialiser est toujours du JSON
    public function serialiser(): string
    {
        return json_encode($this->toArray(), JSON_THROW_ON_ERROR);
    }

    // Méthode abstraite : chaque entité expose ses propres données
    abstract protected function toArray(): array;

    // deserialiser reste abstraite (chaque entité sait comment se reconstruire)
    abstract public function deserialiser(string $data): static;
}

class ArticleEntite extends AbstractEntite
{
    public function __construct(
        public readonly string $titre,
        public readonly string $contenu,
    ) {}

    protected function toArray(): array
    {
        return ['titre' => $this->titre, 'contenu' => $this->contenu];
    }

    public function deserialiser(string $data): static
    {
        $d = json_decode($data, true, 512, JSON_THROW_ON_ERROR);
        return new static($d['titre'], $d['contenu']);
    }
}

$article = new ArticleEntite('Titre test', 'Corps du texte');
$json    = $article->serialiser();   // {"titre":"Titre test","contenu":"Corps du texte"}
$copie   = $article->deserialiser($json);


// ─────────────────────────────────────────────────────────────
// 5. ABSTRACT VS INTERFACE — QUAND CHOISIR QUOI ?
// ─────────────────────────────────────────────────────────────
//
//  Interface                          Classe Abstraite
//  ─────────────────────────────────  ─────────────────────────────────
//  Contrat pur, zéro logique          Logique partagée + contrat
//  Implémentation multiple possible   Héritage simple uniquement
//  Pas de propriétés d'instance       Propriétés d'instance autorisées
//  Pas de constructeur                Constructeur possible
//
//  Règle pratique :
//  → Interface  si tu veux définir un "peut faire X"
//  → Abstract   si tu veux partager du code ET forcer un contrat


// ─────────────────────────────────────────────────────────────
// 6. TEMPLATE METHOD PATTERN (patron fréquent avec abstract)
// ─────────────────────────────────────────────────────────────
// La méthode publique (le "template") orchestre les étapes,
// les étapes concrètes sont laissées aux sous-classes.
// Symfony Command en est un exemple typique.

abstract class ImportateurDonnees
{
    // Template method — l'algorithme est fixé ici
    final public function importer(string $source): int
    {
        $donnees   = $this->lire($source);          // étape 1
        $valides   = $this->valider($donnees);       // étape 2
        $transforme = $this->transformer($valides);  // étape 3
        $nb        = $this->persister($transforme);  // étape 4
        $this->notifier($nb);                        // étape 5
        return $nb;
    }

    abstract protected function lire(string $source): array;
    abstract protected function valider(array $donnees): array;
    abstract protected function transformer(array $donnees): array;
    abstract protected function persister(array $donnees): int;

    // Comportement par défaut surchargeable
    protected function notifier(int $nb): void
    {
        echo "{$nb} entrées importées.\n";
    }
}

class ImportateurCSV extends ImportateurDonnees
{
    protected function lire(string $source): array
    {
        return str_getcsv(file_get_contents($source) ?: '', "\n");
    }

    protected function valider(array $donnees): array
    {
        return array_filter($donnees, fn(string $l) => strlen($l) > 0);
    }

    protected function transformer(array $donnees): array
    {
        return array_map(fn(string $l) => str_getcsv($l), $donnees);
    }

    protected function persister(array $donnees): int
    {
        // Ici : INSERT en base
        return count($donnees);
    }
}


// ─────────────────────────────────────────────────────────────
// RÉSUMÉ — Points clés pour la certification
// ─────────────────────────────────────────────────────────────
//
// ✅ abstract class : ne peut pas être instanciée directement
// ✅ abstract method : sans corps, DOIT être implémentée par l'enfant
// ✅ Méthode concrète dans une abstract class = logique partagée héritée
// ✅ Une abstract class peut étendre une autre abstract class
// ✅ Une abstract class peut implémenter une interface partiellement
// ✅ final sur une méthode concrète = empêche la surcharge (Template Method)
// ✅ parent:: pour appeler la version de la méthode de la classe parente
// ✅ Symfony : AbstractController, Command, AbstractType suivent ce pattern
