<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class AppExtension extends AbstractExtension
{

    public function getFilters(): array
    {
        return [
            new TwigFilter('min_to_hour', [$this, 'minutesToHours'])
        ];
    }

    // on crée une function minutesToHour car sinon dans le return on ne sait pas ce que c'est
    public function minutesToHours($value)
    {

        // si la valeur est inférieur à 60 on retourne la valeur
        if ($value < 60) {
            return $value;
        }

        $hours = floor($value / 60);
        // minutes est égal au reste de la valeur
        $minutes = $value % 60;

        if ($minutes < 10) {
            $minutes = '0' . $minutes;
        }

        // la méthode sprintf qd on a plusieurs arguments dans un string , on passe en ensuite les hours et les minutes
        $time = sprintf('%sh%s', $hours, $minutes);

        return $time;
    }
}
