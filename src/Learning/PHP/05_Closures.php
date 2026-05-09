<?php

declare(strict_types=1);

/**
 * ============================================================
 * NOTION 07 — Closures & Fonctions Anonymes
 * Certification Symfony 8
 * ============================================================
 *
 * Une closure est une fonction anonyme qui peut capturer des variables
 * de son contexte. Symfony les utilise dans les routes, les events,
 * les collections Doctrine, les middlewares Messenger, etc.
 */

namespace App\Learning\PHP;

// ─────────────────────────────────────────────────────────────
// 1. FONCTION ANONYME (closure classique)
// ─────────────────────────────────────────────────────────────
// Stockée dans une variable, passée en argument, retournée.

$multiplier = function(int $a, int $b): int {
    return $a * $b;
};

echo $multiplier(3, 4); // 12

// Passage en argument — très courant avec array_map, array_filter, usort
$nombres  = [1, 2, 3, 4, 5, 6];
$pairs    = array_filter($nombres, function(int $n): bool {
    return $n % 2 === 0;
});
// [2, 4, 6]

$doubles  = array_map(function(int $n): int {
    return $n * 2;
}, $nombres);
// [2, 4, 6, 8, 10, 12]


// ─────────────────────────────────────────────────────────────
// 2. CAPTURE DE VARIABLES : use
// ─────────────────────────────────────────────────────────────
// Une closure ne capture pas automatiquement les variables extérieures.
// On doit les déclarer avec use.

$tva   = 0.20;
$prixHT = 100.0;

$calculerTTC = function(float $ht) use ($tva): float {
    return $ht * (1 + $tva); // capture $tva par valeur (copie)
};

echo $calculerTTC($prixHT); // 120.0

$tva = 0.10;             // modifier $tva APRÈS la définition...
echo $calculerTTC($prixHT); // ...n'affecte PAS la closure (copie déjà faite) → 120.0

// Capture par RÉFÉRENCE avec &
$compteur = 0;
$incrementer = function() use (&$compteur): void {
    $compteur++; // modifie la variable originale
};
$incrementer();
$incrementer();
echo $compteur; // 2


// ─────────────────────────────────────────────────────────────
// 3. ARROW FUNCTION (fn) — PHP 7.4+
// ─────────────────────────────────────────────────────────────
// Syntaxe courte : fn(params) => expression
// Capture AUTOMATIQUEMENT les variables extérieures par valeur (pas de use).
// Idéale pour les callbacks courts.

$tva2 = 0.20;

// Équivalent au calcul ci-dessus, en une ligne
$ttc = fn(float $ht): float => $ht * (1 + $tva2); // $tva2 capturé auto
echo $ttc(100.0); // 120.0

// Utilisation avec array_map (arrow function = très lisible)
$produits = [10.0, 20.0, 50.0];
$avecTVA  = array_map(fn(float $p) => $p * 1.20, $produits);
// [12.0, 24.0, 60.0]

// Chaînage : array_filter + array_map + array_values
$resultats = array_values(
    array_map(
        fn(int $n) => $n ** 2,
        array_filter($nombres, fn(int $n) => $n > 3)
    )
);
// [16, 25, 36]


// ─────────────────────────────────────────────────────────────
// 4. CLOSURE RETOURNÉE PAR UNE FONCTION (currying / factory)
// ─────────────────────────────────────────────────────────────
// Une fonction qui retourne une closure = usine à comportements.

function multiplicateur(int $facteur): \Closure
{
    return fn(int $n) => $n * $facteur; // $facteur capturé automatiquement
}

$double = multiplicateur(2);
$triple = multiplicateur(3);

echo $double(5);  // 10
echo $triple(5);  // 15

// Utilisé pour créer des validators configurables
function creerValidateurLongueur(int $min, int $max): \Closure
{
    return function(string $valeur) use ($min, $max): bool {
        $len = strlen($valeur);
        return $len >= $min && $len <= $max;
    };
}

$validerMotDePasse = creerValidateurLongueur(8, 64);
var_dump($validerMotDePasse('court'));   // false
var_dump($validerMotDePasse('motdepasse_suffisant')); // true


// ─────────────────────────────────────────────────────────────
// 5. BINDING : Closure::bind et bindTo
// ─────────────────────────────────────────────────────────────
// On peut lier une closure à un objet pour accéder à ses membres privés.
// Doctrine et Symfony l'utilisent en interne pour hydrater les entités.

class Entite
{
    private int $id = 0;
    private string $nom = 'inconnu';
}

// Lier la closure à une instance de Entite pour accéder à ses privés
$hydrate = Closure::bind(
    function(array $data): void {
        $this->id  = $data['id'];
        $this->nom = $data['nom'];
    },
    $entite = new Entite(),
    Entite::class,  // scope : autorise l'accès aux privés
);

$hydrate(['id' => 42, 'nom' => 'Alice']);
// $entite->id === 42 (accessible grâce au binding)


// ─────────────────────────────────────────────────────────────
// 6. CALLABLE : les différentes formes acceptées
// ─────────────────────────────────────────────────────────────
// PHP accepte plusieurs formes de callable pour les callbacks.

// a) Fonction nommée
$cb1 = 'strtoupper';
echo $cb1('hello'); // 'HELLO'

// b) Méthode statique
class Formateur
{
    public static function majuscule(string $s): string
    {
        return strtoupper($s);
    }

    public function prefixer(string $s): string
    {
        return ">> {$s}";
    }
}

$cb2 = ['Formateur', 'majuscule'];          // tableau [classe, méthode]
$cb3 = 'App\Learning\PHP\Formateur::majuscule'; // string

// c) Méthode d'instance
$f   = new Formateur();
$cb4 = [$f, 'prefixer'];                    // tableau [instance, méthode]

// d) First Class Callable (PHP 8.1) — voir notion 01
$cb5 = $f->prefixer(...);                   // Closure liée à $f

echo $cb5('test'); // '>> test'

// e) Objet __invoke
class Doubleur
{
    public function __invoke(int $n): int
    {
        return $n * 2;
    }
}

$doubleur = new Doubleur();
echo $doubleur(7);  // 14
echo array_sum(array_map($doubleur, [1, 2, 3])); // 12


// ─────────────────────────────────────────────────────────────
// 7. CLOSURES DANS SYMFONY
// ─────────────────────────────────────────────────────────────

/*
// a) Event Listener léger (à la place d'une classe dédiée)
$dispatcher->addListener(
    KernelEvents::REQUEST,
    function(RequestEvent $event): void {
        $request = $event->getRequest();
        // ...
    }
);

// b) Critère de filtre Doctrine (Criteria)
use Doctrine\Common\Collections\Criteria;

$criteria = Criteria::create()
    ->andWhere(Criteria::expr()->gt('prix', 100));

// c) Route inline dans routes.yaml (rare, mais possible)
// Symfony permet de définir une closure comme contrôleur

// d) Messenger middleware
$middleware = function(Envelope $env, StackInterface $stack): Envelope {
    // avant
    $result = $stack->next()->handle($env, $stack);
    // après
    return $result;
};
*/


// ─────────────────────────────────────────────────────────────
// RÉSUMÉ — Points clés pour la certification
// ─────────────────────────────────────────────────────────────
//
// ✅ function(...) {} : closure classique, capture via use ($var)
// ✅ use ($var) : copie par valeur / use (&$var) : par référence
// ✅ fn(...) => expr : arrow function, capture auto par valeur, pas de use
// ✅ Arrow function : corps = une seule expression (pas de return explicite)
// ✅ Closure::bind / bindTo : lier une closure à un objet (accès aux privés)
// ✅ callable : string, tableau [instance, 'méthode'], closure, __invoke
// ✅ First Class Callable $obj->methode(...) : closure liée à l'instance
// ✅ __invoke : rend un objet directement appelable comme une fonction
