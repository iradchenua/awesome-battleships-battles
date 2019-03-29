<?php
/**
 * Created by PhpStorm.
 * User: хм
 * Date: 26.03.2019
 * Time: 17:31
 */

namespace App\Service;

use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class JsonSerializer
{
    private $serializer;

    public function __construct()
    {
        $encodes = [new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];
        $this->serializer = new Serializer($normalizers, $encodes);
    }
    public function serialize($entityOnMap)
    {
        return $this->serializer->serialize($entityOnMap, 'json', [
            'attributes' => [
                'name',
                'id',
                'hullPoints',
                'x',
                'y',
                'width',
                'height',
                'dirX',
                'dirY',
                'img',
                'phase'
            ]
        ]);
    }
}