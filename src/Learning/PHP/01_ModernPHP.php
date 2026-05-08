<?php

declare(strict_types=1);

/**
 * ============================================================
 * NOTION 01 — PHP API up to PHP 8.4
 * Certification Symfony 8
 * ============================================================
 *
 * Ce fichier illustre les fonctionnalités PHP 8.x importantes
 * pour la certification. Chaque section est un exemple autonome
 * et commenté.
 */

namespace App\Learning\PHP;

// ─────────────────────────────────────────────────────────────
// 1. NAMED ARGUMENTS (PHP 8.0)
// ─────────────────────────────────────────────────────────────
// On passe les arguments par leur nom, dans n'importe quel ordre.
// Très utile pour les fonctions avec beaucoup de paramètres optionnels.

$tableau = ['a', 'b', 'c', 'd', 'e'];

// Avant PHP 8.0 — on devait se souvenir de l'ordre exact
$resultat1 = array_slice($tableau, 1, 3, true);

// Avec named arguments — auto-documenté, ordre libre
$resultat2 = array_slice(
    array: $tableau,
    offset: 1,
    length: 3,
    preserve_keys: true,
);

// ─────────────────────────────────────────────────────────────
// 2. MATCH EXPRESSION (PHP 8.0)
// ─────────────────────────────────────────────────────────────
// Différences avec switch :
//   - Comparaison STRICTE (===)
//   - Retourne une valeur
//   - Pas de "fall-through" (pas besoin de break)
//   - Lève UnhandledMatchError si aucun cas ne correspond

$codeStatut = 200;

$libelle = match($codeStatut) {
    200, 201  => 'Succès',
    301, 302  => 'Redirection',
    400       => 'Mauvaise requête',
    404       => 'Non trouvé',
    500       => 'Erreur serveur',
    default   => 'Code inconnu',
};
// $libelle === 'Succès'

// ─────────────────────────────────────────────────────────────
// 3. NULLSAFE OPERATOR ?-> (PHP 8.0)
// ─────────────────────────────────────────────────────────────
// Si l'objet à gauche de ?-> est null, l'expression entière
// retourne null sans lancer d'erreur.

// Exemple sans nullsafe (verbeux)
// $ville = null;
// if ($user !== null) {
//     $adresse = $user->getAdresse();
//     if ($adresse !== null) {
//         $ville = $adresse->getVille();
//     }
// }

// Exemple avec nullsafe — compact et lisible
// $ville = $user?->getAdresse()?->getVille();

// ─────────────────────────────────────────────────────────────
// 4. PROMOTED CONSTRUCTOR PROPERTIES (PHP 8.0)
// ─────────────────────────────────────────────────────────────
// Déclare ET initialise les propriétés directement dans le
// constructeur. Très utilisé dans Symfony (services, entités).

class Produit
{
    // PHP 8.0 : visibilité dans le constructeur = propriété promue
    public function __construct(
        private readonly string $nom,    // readonly = ne peut être modifié après
        private float $prix,
        private int $stock = 0,          // valeur par défaut possible
    ) {}

    public function getNom(): string  { return $this->nom; }
    public function getPrix(): float  { return $this->prix; }
    public function getStock(): int   { return $this->stock; }
}

$produit = new Produit(nom: 'T-shirt', prix: 29.99, stock: 100);

// ─────────────────────────────────────────────────────────────
// 5. UNION TYPES (PHP 8.0)
// ─────────────────────────────────────────────────────────────
// Un paramètre peut être de plusieurs types différents.

function formaterValeur(int|float|string $valeur): string
{
    if (is_string($valeur)) {
        return strtoupper($valeur);
    }
    return number_format((float) $valeur, 2);
}

// ─────────────────────────────────────────────────────────────
// 6. READONLY PROPERTIES (PHP 8.1) & READONLY CLASS (PHP 8.2)
// ─────────────────────────────────────────────────────────────
// Une propriété readonly ne peut être assignée qu'une seule fois.
// Idéal pour les Value Objects immuables.

class Coordonnees
{
    public function __construct(
        public readonly float $latitude,
        public readonly float $longitude,
    ) {}
}

$paris = new Coordonnees(48.8566, 2.3522);
// $paris->latitude = 0.0; // Fatal error : Cannot modify readonly property

// PHP 8.2 : readonly class — TOUTES les propriétés sont readonly
readonly class MoneyValue
{
    public function __construct(
        public float $montant,
        public string $devise,
    ) {}
}

// ─────────────────────────────────────────────────────────────
// 7. INTERSECTION TYPES (PHP 8.1)
// ─────────────────────────────────────────────────────────────
// L'argument doit implémenter TOUS les types listés.
// Utile quand on veut une classe qui respecte plusieurs interfaces.

interface Serialisable
{
    public function serialize(): string;
}

interface Journalisable
{
    public function log(): void;
}

// $service DOIT implémenter les deux interfaces
function enregistrer(Serialisable&Journalisable $service): void
{
    $service->log();
    $data = $service->serialize();
}

// ─────────────────────────────────────────────────────────────
// 8. NEVER RETURN TYPE (PHP 8.1)
// ─────────────────────────────────────────────────────────────
// Indique que la fonction ne retourne JAMAIS (exception ou exit).
// Le moteur PHP vérifie que la fonction ne contient pas de return.

function lancerErreurCritique(string $message): never
{
    throw new \RuntimeException($message);
    // Toute ligne après throw est inaccessible
}

// ─────────────────────────────────────────────────────────────
// 9. FIRST CLASS CALLABLE SYNTAX (PHP 8.1)
// ─────────────────────────────────────────────────────────────
// Obtenir un callable depuis une fonction/méthode sans closure.

$longueur  = strlen(...);       // équivalent à Closure::fromCallable('strlen')
$majuscule = strtoupper(...);

$mots = ['hello', 'world'];
$majuscules = array_map($majuscule, $mots); // ['HELLO', 'WORLD']

// Méthode d'instance
$produit2   = new Produit('Pantalon', 49.99);
$getNom     = $produit2->getNom(...);       // callable lié à $produit2
$nom        = $getNom();                    // 'Pantalon'

// ─────────────────────────────────────────────────────────────
// 10. DNF TYPES — Disjunctive Normal Form (PHP 8.2)
// ─────────────────────────────────────────────────────────────
// Combinaison d'union ET d'intersection : (A&B)|C
// La règle : les intersections doivent être entre parenthèses.

function traiterDonnee((Serialisable&Journalisable)|null $data): void
{
    if ($data === null) {
        return;
    }
    $data->log();
}

// ─────────────────────────────────────────────────────────────
// 11. TYPED CLASS CONSTANTS (PHP 8.3)
// ─────────────────────────────────────────────────────────────
// Les constantes de classe peuvent avoir un type déclaré.

class Configuration
{
    const string  VERSION     = '8.0.0';
    const int     MAX_TENTATIVES = 3;
    const float   TIMEOUT     = 30.0;
    const bool    DEBUG       = false;
}

// ─────────────────────────────────────────────────────────────
// 12. PROPERTY HOOKS (PHP 8.4)
// ─────────────────────────────────────────────────────────────
// Logique get/set définie directement sur la propriété.
// Remplace les getters/setters dans de nombreux cas.

class Utilisateur
{
    // Propriété "virtuelle" : calculée à la volée, pas stockée
    public string $nomComplet
    {
        get => $this->prenom . ' ' . $this->nom;  // lecture = concaténation
        set(string $valeur) {                       // écriture = décomposition
            [$this->prenom, $this->nom] = explode(' ', $valeur, 2);
        }
    }

    // Propriété avec validation à l'écriture
    private float $_prix = 0.0;
    public float $prix
    {
        get => $this->_prix;
        set(float $valeur) {
            if ($valeur < 0) {
                throw new \InvalidArgumentException('Le prix ne peut pas être négatif.');
            }
            $this->_prix = $valeur;
        }
    }

    public function __construct(
        public string $prenom,
        public string $nom,
    ) {}
}

$u = new Utilisateur('Alice', 'Dupont');
echo $u->nomComplet;         // "Alice Dupont"
$u->nomComplet = 'Bob Martin'; // décompose en prenom + nom

// ─────────────────────────────────────────────────────────────
// 13. ASYMMETRIC VISIBILITY (PHP 8.4)
// ─────────────────────────────────────────────────────────────
// Visibilité différente pour la lecture (get) et l'écriture (set).
// public private(set) = lisible partout, modifiable seulement dans la classe.

class Commande
{
    // Lecture publique, écriture uniquement en interne
    public private(set) int $total = 0;

    // Lecture publique, écriture protégée (classe + enfants)
    public protected(set) string $statut = 'en_attente';

    public function ajouterLigne(int $montant): void
    {
        $this->total += $montant; // OK car on est dans la classe
    }
}

$cmd = new Commande();
echo $cmd->total;           // 0 — OK, lecture publique
$cmd->ajouterLigne(50);
echo $cmd->total;           // 50
// $cmd->total = 0;         // Fatal error : écriture privée depuis l'extérieur

// ─────────────────────────────────────────────────────────────
// RÉSUMÉ RAPIDE — Ce que la certification peut tester
// ─────────────────────────────────────────────────────────────
//
// ✅ match vs switch : match est strict (===), pas de fall-through
// ✅ ?-> : toute la chaîne retourne null si un maillon est null
// ✅ readonly : assignation unique (constructeur uniquement)
// ✅ never : fonction qui ne retourne JAMAIS
// ✅ Intersection A&B : doit satisfaire les DEUX types
// ✅ DNF (A&B)|null : intersection entre parenthèses obligatoire
// ✅ Property hooks (8.4) : get/set inline sur une propriété
// ✅ Asymmetric visibility (8.4) : public private(set)
