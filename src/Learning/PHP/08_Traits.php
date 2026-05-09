<?php

declare(strict_types=1);

/**
 * ============================================================
 * NOTION 08 — Traits
 * Certification Symfony 8
 * ============================================================
 *
 * Un trait est un mécanisme de réutilisation de code horizontal.
 * Il permet d'injecter des méthodes (et propriétés) dans une classe
 * sans héritage. PHP ne supporte que l'héritage simple (une seule classe
 * parente) — les traits contournent cette limitation.
 *
 * Symfony en fait un usage intensif :
 *   - TimestampableTrait (Doctrine Extensions)
 *   - SoftDeleteableTrait
 *   - TranslatableTrait
 *   - ControllerTrait (dans AbstractController)
 */

namespace App\Learning\PHP;

// ─────────────────────────────────────────────────────────────
// 1. DÉCLARER ET UTILISER UN TRAIT
// ─────────────────────────────────────────────────────────────

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

    public function getCreeLe(): \DateTimeImmutable   { return $this->creeLe; }
    public function getModifieLe(): \DateTimeImmutable { return $this->modifieLe; }
}

trait SoftDeletable
{
    private ?\DateTimeImmutable $supprimeLe = null;

    public function supprimer(): void
    {
        $this->supprimeLe = new \DateTimeImmutable();
    }

    public function restaurer(): void
    {
        $this->supprimeLe = null;
    }

    public function estSupprime(): bool
    {
        return $this->supprimeLe !== null;
    }

    public function getSupprimeLe(): ?\DateTimeImmutable
    {
        return $this->supprimeLe;
    }
}

// La classe utilise les deux traits : les méthodes sont "copiées" dedans
class Article
{
    use Horodatable, SoftDeletable;

    public function __construct(private string $titre)
    {
        $this->initialiserHorodatage();
    }
}

$article = new Article('Mon article');
sleep(0); // juste pour illustrer
$article->supprimer();

var_dump($article->estSupprime());   // true
var_dump($article->getCreeLe());     // DateTimeImmutable


// ─────────────────────────────────────────────────────────────
// 2. TRAITS ET PROPRIÉTÉS
// ─────────────────────────────────────────────────────────────
// Un trait peut déclarer des propriétés.
// Si la classe déclare la même propriété avec la même visibilité
// et le même type → pas de conflit.
// Si les types ou visibilités diffèrent → Fatal Error.

trait AvecIdentifiant
{
    private ?int $id = null;

    public function getId(): ?int   { return $this->id; }
    public function setId(int $id): void { $this->id = $id; }
}

class Utilisateur
{
    use AvecIdentifiant; // $id vient du trait

    public function __construct(public string $email) {}
}

$u = new Utilisateur('bob@example.com');
$u->setId(1);
echo $u->getId(); // 1


// ─────────────────────────────────────────────────────────────
// 3. RÉSOLUTION DE CONFLITS
// ─────────────────────────────────────────────────────────────
// Quand deux traits ont une méthode du même nom → conflit.
// On résout avec insteadof et as.

trait LoggerA
{
    public function log(string $msg): void
    {
        echo "[A] {$msg}\n";
    }

    public function demarrer(): void
    {
        echo "LoggerA démarre\n";
    }
}

trait LoggerB
{
    public function log(string $msg): void
    {
        echo "[B] {$msg}\n";
    }

    public function demarrer(): void
    {
        echo "LoggerB démarre\n";
    }
}

class ServiceAvecDeuxLoggers
{
    use LoggerA, LoggerB {
        LoggerA::log      insteadof LoggerB; // LoggerA::log est retenu
        LoggerB::log      as logB;           // LoggerB::log accessible via logB()
        LoggerA::demarrer insteadof LoggerB;
        LoggerB::demarrer as demarrerB;      // alias pour demarrer de B
    }
}

$s = new ServiceAvecDeuxLoggers();
$s->log('message principal'); // [A] message principal
$s->logB('message B');        // [B] message B


// ─────────────────────────────────────────────────────────────
// 4. CHANGER LA VISIBILITÉ VIA as
// ─────────────────────────────────────────────────────────────
// as peut aussi changer la visibilité d'une méthode importée.

trait Connecteur
{
    public function connecter(): void
    {
        echo "Connexion établie\n";
    }
}

class ServiceInterne
{
    use Connecteur {
        connecter as private; // rendu privé dans cette classe
    }

    public function initialiser(): void
    {
        $this->connecter(); // OK car appelé en interne
    }
}

$si = new ServiceInterne();
$si->initialiser();
// $si->connecter(); // Fatal Error : méthode privée


// ─────────────────────────────────────────────────────────────
// 5. MÉTHODES ABSTRAITES DANS UN TRAIT
// ─────────────────────────────────────────────────────────────
// Un trait peut déclarer des méthodes abstraites.
// La classe qui l'utilise DOIT les implémenter.

trait Validable
{
    // La classe doit fournir cette méthode
    abstract protected function getRegles(): array;

    public function valider(array $donnees): bool
    {
        foreach ($this->getRegles() as $champ => $regle) {
            if ($regle === 'required' && empty($donnees[$champ])) {
                return false;
            }
        }
        return true;
    }
}

class FormulaireContact
{
    use Validable;

    protected function getRegles(): array
    {
        return [
            'email'   => 'required',
            'message' => 'required',
        ];
    }
}

$form = new FormulaireContact();
var_dump($form->valider(['email' => 'x@y.com', 'message' => 'Bonjour'])); // true
var_dump($form->valider(['email' => '', 'message' => 'Bonjour']));         // false


// ─────────────────────────────────────────────────────────────
// 6. TRAITS UTILISANT D'AUTRES TRAITS
// ─────────────────────────────────────────────────────────────
// Un trait peut utiliser un autre trait.

trait AvecUUID
{
    private string $uuid;

    public function genererUUID(): void
    {
        $this->uuid = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            random_int(0, 0xffff), random_int(0, 0xffff),
            random_int(0, 0xffff),
            random_int(0, 0x0fff) | 0x4000,
            random_int(0, 0x3fff) | 0x8000,
            random_int(0, 0xffff), random_int(0, 0xffff), random_int(0, 0xffff)
        );
    }

    public function getUUID(): string { return $this->uuid; }
}

trait EntiteComplete
{
    use AvecIdentifiant; // trait dans un trait
    use AvecUUID;
    use Horodatable;
}

class Produit
{
    use EntiteComplete;

    public function __construct(public string $nom, public float $prix)
    {
        $this->initialiserHorodatage();
        $this->genererUUID();
    }
}

$p = new Produit('T-shirt', 29.99);
echo $p->getUUID();   // ex: a3f4b2c1-...
echo $p->getCreeLe()->format('Y-m-d');


// ─────────────────────────────────────────────────────────────
// 7. TRAITS DANS SYMFONY / DOCTRINE
// ─────────────────────────────────────────────────────────────
// Doctrine Extensions (gedmo/doctrine-extensions) fournit des traits
// prêts à l'emploi pour les entités courantes.

/*
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Gedmo\Sluggable\Traits\SluggableEntity;

#[ORM\Entity]
class Article
{
    use TimestampableEntity;  // createdAt / updatedAt auto
    use SoftDeleteableEntity; // deletedAt, ne supprime pas en base
    use SluggableEntity;      // slug généré depuis le titre

    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Gedmo\Slug(fields: ['titre'])]
    private string $slug = '';

    #[ORM\Column(length: 255)]
    private string $titre = '';
}
*/


// ─────────────────────────────────────────────────────────────
// RÉSUMÉ — Points clés pour la certification
// ─────────────────────────────────────────────────────────────
//
// ✅ trait : réutilisation horizontale, pas un type, pas instanciable
// ✅ use TraitA, TraitB : plusieurs traits dans une classe
// ✅ Conflit de méthodes : résolu avec insteadof (choisir) et as (alias)
// ✅ as private/protected : changer la visibilité d'une méthode importée
// ✅ Méthodes abstraites dans un trait : la classe doit les implémenter
// ✅ Un trait peut lui-même utiliser d'autres traits
// ✅ Propriétés dans les traits : conflit si même nom + type/visibilité différents
// ✅ Symfony/Doctrine : TimestampableEntity, SoftDeleteableEntity sont des traits
