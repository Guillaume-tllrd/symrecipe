<?php

namespace App\Service;

use Exception;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class PictureService
{
    private $params;

    public function __construct(ParameterBagInterface $params)
    {
        $this->params = $params;
        // de cette facons avec ma propriété params je vais pouvoir avoir la possibilité d'aller chercher les infos des parameters  directement dans service.yml
    }
    public function add(UploadedFile $picture, ?string $folder = '', ?int $width = 250, ?int $height = 250)
    {
        //On donne un nouveau nom à l'image:
        $fichier = md5(uniqid(rand(), true)) . '.webp';

        // on récupère les infos de l'image:
        $picture_infos = getimagesize($picture);

        if ($picture_infos === false) {
            throw new Exception('Format d\'image incorrect');
        }
        // j'ai du intérer une nouvelle config dans mon dockerfile car l'extension n'était pas activé et je ne pouvais pas installer des images
        if (!extension_loaded('gd')) {
            throw new \RuntimeException('L\'extension GD n\'est pas activée.');
        }
        // on vérifie le format de l'image à partir du "mime":
        switch ($picture_infos['mime']) {
                // je récupère les images dans des var poyur pouvoir les manipuler
            case 'image/png':
                $picture_source = imagecreatefrompng($picture);
                break;
            case 'image/jpeg':
                $picture_source = imagecreatefromjpeg($picture);
                break;
            case 'image/webp':
                $picture_source = imagecreatefromwebp($picture);
                break;
            default:
                throw new Exception('Format d\'image incorrect');
        }

        // on recadre l'image:
        // On récupère les dimensions
        $imageWidth = $picture_infos[0];
        $imageHeight = $picture_infos[1];

        // on vérifie l'orientation de l'image:
        // on utilise le spaceship <=> pour faire une triple comparaison < inférieur / = égal  / >suppérieur
        switch ($imageWidth <=> $imageHeight) {
            case -1: //portrait: width inférieur à heigth
                $squareSize = $imageWidth;
                $src_x = 0;
                $src_y = ($imageHeight - $squareSize) / 2;
                break;
            case 0: //carré: width égal à heigth
                $squareSize = $imageWidth;
                $src_x = 0;
                $src_y =  0;
                break;
            case 1: //paysage: width suppérieur à heigth
                $squareSize = $imageHeight;
                $src_x = ($imageWidth - $squareSize) / 2;
                $src_y = 0;
                break;
        }

        // On crée une nouvelle image "vierge"
        $resized_picture = imagecreatetruecolor($width, $height);
        // on va venir collé la découpe de notre image source:
        imagecopyresampled($resized_picture, $picture_source, 0, 0, $src_x, $src_y, $width, $height, $squareSize, $squareSize);

        // on va chercher notre chemin à partir du parameters du construct que l'on concatène avec $folder qui peut être product par ex, c'est le nom du dossier après upload:
        $path = $this->params->get('images_directory') . $folder;

        // on crée le dossier de destination s'il n'exsite pas
        if (!file_exists($path . '/mini/')) {
            mkdir($path . '/mini/', 0755, true);
        }

        // on stocke l'image recadrée:
        imagewebp($resized_picture, $path . '/mini/' . $width . 'x' . $height . '-' . $fichier);

        $picture->move($path . '/', $fichier);

        return $fichier;
    }

    public function delete(string $fichier, ?string $folder = '', ?int $width = 250, ?int $height = 250)
    {
        if ($fichier !== 'default.webp') {
            $success = false;
            $path = $this->params->get('images_directory') . $folder;

            $mini = $path . '/mini/' . $width . 'x' . $height . '-' . $fichier;

            if (file_exists($mini)) {
                unlink($mini);
                $success = true;
            }

            // on supprime l'original
            $original = $path . '/' . $fichier;

            if (file_exists($original)) {
                unlink($original);
                $success = true;
            }
            return $success;
        }
        return false;
    }
}
