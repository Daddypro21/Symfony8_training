<?php

declare(strict_types=1);

/**
 * ============================================================
 * NOTION 05 — Enums (PHP 8.1)
 * Certification Symfony 8
 * ============================================================
 *
 * Les Enums (énumérations) introduits en PHP 8.1 permettent de définir
 * un ensemble fini de valeurs nommées. Ils remplacent avantageusement
 * les constantes de classe et les chaînes magiques.
 *
 * Symfony les utilise dans les entités Doctrine, les formulaires,
 * les messages Messenger, etc.
 */

namespace App\Learning\PHP;

// ─────────────────────────────────────────────────────────────
// 1. PURE ENUM (sans type)
// ─────────────────────────────────────────────────────────────
// Un Pure Enum n'a pas de valeur scalaire associée.
// Les cases sont des singletons, comparables avec ===.

enum Direction
{
    case Nord;
    case Sud;
    case Est;
    case Ouest;
}

$dir = Direction::Nord;

var_dump($dir === Direction::Nord);  // true
var_dump($dir === Direction::Sud);   // false

// Utilisation dans un match
$libelle = match($dir) {
    Direction::Nord  => 'Vous allez vers le Nord',
    Direction::Sud   => 'Vous allez vers le Sud',
    Direction::Est   => 'Vous allez vers l\'Est',
    Direction::Ouest => 'Vous allez vers l\'Ouest',
};


// ─────────────────────────────────────────────────────────────
// 2. BACKED ENUM (avec type : string ou int)
// ─────────────────────────────────────────────────────────────
// Chaque case est associée à une valeur scalaire (string ou int).
// Permet la sérialisation / désérialisation (base de données, JSON…).
// Toutes les cases doivent avoir une valeur, toutes du même type.

enum Statut: string
{
    case EnAttente  = 'en_attente';
    case EnCours    = 'en_cours';
    case Termine    = 'termine';
    case Annule     = 'annule';
}

// Accéder à la valeur scalaire
echo Statut::EnCours->value;  // 'en_cours'

// Reconstruire un Enum depuis sa valeur scalaire
$statut = Statut::from('termine');          // Statut::Termine
echo $statut->name;                          // 'Termine' (nom de la case)

// tryFrom ne lève pas d'exception si la valeur est inconnue
$inconnu = Statut::tryFrom('inconnu');      // null (pas d'exception)
$connu   = Statut::tryFrom('en_attente');   // Statut::EnAttente


// ─────────────────────────────────────────────────────────────
// 3. BACKED ENUM : int
// ─────────────────────────────────────────────────────────────

enum Priorite: int
{
    case Basse    = 1;
    case Normale  = 2;
    case Haute    = 3;
    case Critique = 4;
}

echo Priorite::Haute->value;  // 3

// Tri, comparaison par valeur scalaire
$niveaux = Priorite::cases(); // tableau de toutes les cases
usort($niveaux, fn(Priorite $a, Priorite $b) => $a->value <=> $b->value);


// ─────────────────────────────────────────────────────────────
// 4. MÉTHODES DANS UN ENUM
// ─────────────────────────────────────────────────────────────
// Les Enums peuvent avoir des méthodes, comme une classe.

enum CouleurFeu: string
{
    case Rouge  = 'rouge';
    case Orange = 'orange';
    case Vert   = 'vert';

    // Méthode d'instance : appelée sur une case
    public function libelle(): string
    {
        return match($this) {
            self::Rouge  => 'STOP',
            self::Orange => 'ATTENTION',
            self::Vert   => 'GO',
        };
    }

    // Méthode statique : appelée sur l'enum lui-même
    public static function prochain(self $actuel): self
    {
        return match($actuel) {
            self::Rouge  => self::Vert,
            self::Vert   => self::Orange,
            self::Orange => self::Rouge,
        };
    }
}

echo CouleurFeu::Rouge->libelle();                    // 'STOP'
echo CouleurFeu::prochain(CouleurFeu::Vert)->value;   // 'orange'


// ─────────────────────────────────────────────────────────────
// 5. INTERFACES SUR LES ENUMS
// ─────────────────────────────────────────────────────────────
// Un Enum peut implémenter une interface — utile pour le type-hinting.

interface ALibelleInterface
{
    public function libelle(): string;
}

enum TypeContrat: string implements ALibelleInterface
{
    case CDI  = 'cdi';
    case CDD  = 'cdd';
    case Freelance = 'freelance';

    public function libelle(): string
    {
        return match($this) {
            self::CDI      => 'Contrat à Durée Indéterminée',
            self::CDD      => 'Contrat à Durée Déterminée',
            self::Freelance => 'Indépendant / Freelance',
        };
    }
}

function afficherLibelle(ALibelleInterface $element): void
{
    echo $element->libelle() . "\n";
}

afficherLibelle(TypeContrat::CDI);   // 'Contrat à Durée Indéterminée'


// ─────────────────────────────────────────────────────────────
// 6. CONSTANTES DANS LES ENUMS
// ─────────────────────────────────────────────────────────────
// Un Enum peut avoir des constantes. Elles peuvent référencer des cases.

enum Permission: string
{
    case Lire    = 'read';
    case Ecrire  = 'write';
    case Supprimer = 'delete';
    case Admin   = 'admin';

    // Constante groupant plusieurs cases
    const array LECTURE_SEULE = [self::Lire];
    const array EDITEUR       = [self::Lire, self::Ecrire];
}

$permissions = Permission::EDITEUR; // [Permission::Lire, Permission::Ecrire]


// ─────────────────────────────────────────────────────────────
// 7. ENUMS ET DOCTRINE ORM
// ─────────────────────────────────────────────────────────────
// Doctrine 3 supporte nativement les Backed Enums dans les entités.
// La valeur scalaire (string/int) est stockée en base.

/*
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Commande
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    private ?int $id = null;

    // Doctrine stocke la valeur scalaire 'en_attente', 'en_cours', etc.
    #[ORM\Column(enumType: Statut::class)]
    private Statut $statut = Statut::EnAttente;

    public function getStatut(): Statut { return $this->statut; }
    public function setStatut(Statut $statut): void { $this->statut = $statut; }
}

// Récupération : Doctrine reconstruit automatiquement l'Enum via ::from()
$commande = $repo->find(1);
$statut   = $commande->getStatut(); // instance de Statut, pas une string
*/


// ─────────────────────────────────────────────────────────────
// 8. ENUMS ET FORMULAIRES SYMFONY
// ─────────────────────────────────────────────────────────────
// EnumType (Symfony 6.1+) génère automatiquement les options depuis l'Enum.

/*
use Symfony\Component\Form\Extension\Core\Type\EnumType;

$builder->add('statut', EnumType::class, [
    'class'        => Statut::class,
    'choice_label' => fn(Statut $s) => ucfirst(str_replace('_', ' ', $s->value)),
]);
*/


// ─────────────────────────────────────────────────────────────
// 9. cases() — lister toutes les cases
// ─────────────────────────────────────────────────────────────
// La méthode statique cases() retourne un tableau de toutes les cases.

$tousStatuts = Statut::cases();
// [Statut::EnAttente, Statut::EnCours, Statut::Termine, Statut::Annule]

foreach ($tousStatuts as $case) {
    echo "{$case->name} => {$case->value}\n";
}
// EnAttente => en_attente
// EnCours => en_cours
// ...


// ─────────────────────────────────────────────────────────────
// RÉSUMÉ — Points clés pour la certification
// ─────────────────────────────────────────────────────────────
//
// ✅ Pure Enum : pas de valeur scalaire, comparaison avec ===
// ✅ Backed Enum : string ou int, accès via ->value et ->name
// ✅ ::from($val) : lève ValueError si inconnu
// ✅ ::tryFrom($val) : retourne null si inconnu (pas d'exception)
// ✅ cases() : retourne toutes les cases dans un tableau
// ✅ Les Enums peuvent avoir des méthodes et des constantes
// ✅ Les Enums peuvent implémenter des interfaces
// ✅ Doctrine : #[ORM\Column(enumType: MonEnum::class)] → stocke la valeur scalaire
// ✅ Symfony Form : EnumType génère les options automatiquement
