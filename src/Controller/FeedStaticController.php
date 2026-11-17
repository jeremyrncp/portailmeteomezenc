<?php

namespace App\Controller;

use App\Entity\WeatherData;
use App\Entity\WeatherStation;
use App\Repository\WeatherDataRepository;
use App\Repository\WeatherStationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Twig\Environment;

class FeedStaticController
{
    #[Route('/feed/static/{id}', name: 'app_feed_static', methods: ['GET'])]
    public function index(WeatherStation $weatherStation, Environment $twig, WeatherDataRepository $weatherDataRepository): Response
    {
        $datas = $weatherDataRepository->findBy(['weatherStation' => $weatherStation], ["createdAt" => "DESC"]);

        $minus24H = (new \DateTime())->modify('-24 hours');

        $dataFiltered = [];
        $tempFiltered = [];
        $humidityFiltered = [];

        /** @var WeatherData $data */
        foreach($datas as $data) {
            if ($data->getCreatedAt() > $minus24H) {
                $dataFiltered[] = $data;
                $tempFiltered[] = $data->getTemperature();
                $humidityFiltered[] = $data->getHumidity();
            }
        }


        return new Response($twig->render('feed/static.html.twig', [
            'data' => array_first($dataFiltered),
            'tx' => max($tempFiltered),
            'tn' => min($tempFiltered),
            'hrx' => max($humidityFiltered),
            'hrn' => min($humidityFiltered),
        ]));

    }

}
