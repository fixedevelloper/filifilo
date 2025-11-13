<?php


namespace App\Helpers;


class TarifCourseService
{
    // Tarif de base fixe en unité monétaire
    protected float $prixDeBase = 100;

    // Tarif au kilomètre
    protected float $prixParKm = 40;

    // Tarif au minute (temps d'attente ou ralenti)
    protected float $prixParMinute = 10;
    protected $price_type_course=0.0;

    /**
     * Calculer le prix total du trajet
     *
     * @param float $distance Distance du trajet en kilomètres
     * @param float $duree    Durée du trajet en minutes
     * @param string|null $periode Période tarifaire : 'jour', 'soir', 'nuit'
     * @return float Prix total calculé
     */
    public function calculerPrix(float $distance, float $duree,$typeCourse): float
    {
        $periode=$this->getPeriodeActuelle();
        // Ajustement des tarifs selon la période
        switch ($periode) {
            case 'jour':
                $this->prixDeBase = 100;
                $this->prixParKm = 40;
                $this->prixParMinute = 10;
                break;
            case 'soir':
                $this->prixDeBase = 150;
                $this->prixParKm = 50;
                $this->prixParMinute = 15;
                break;
            case 'nuit':
                $this->prixDeBase = 200;
                $this->prixParKm = 60;
                $this->prixParMinute = 20;
                break;
            default:
                // Valeurs par défaut ou erreur
                break;
        }
        switch ($typeCourse){
            case 'classic':
                $this->price_type_course=100.0;
                break;
            case 'moto':
                $this->price_type_course=0.0;
                break;
            case 'vip':
                $this->price_type_course=200.0;
                break;
            case 'confort':
                $this->price_type_course=150.0;
                break;
            default:
                break;
        }

        // Calcul prix total
        $prixTotal = $this->prixDeBase + ($this->prixParKm * $distance) + ($this->prixParMinute * $duree)+$this->price_type_course;

        return $prixTotal;
    }
    function getPeriodeActuelle(): string
    {
        // Heure actuelle en heure (0-23)
        $heure = (int) date('H');

        if ($heure >= 6 && $heure < 18) {
            return 'jour';
        } elseif ($heure >= 18 && $heure < 22) {
            return 'soir';
        } else {
            return 'nuit';
        }
    }

}
