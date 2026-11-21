<?php

namespace App\Controller;

use App\Entity\WeatherData;
use App\Entity\WeatherStation;
use App\Repository\WeatherDataRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Twig\Environment;

class FeedStaticController
{
    #[Route('/feed/static/{id}', name: 'app_feed_static', methods: ['GET'])]
    public function index(WeatherStation $weatherStation, Environment $twig, WeatherDataRepository $weatherDataRepository): Response
    {
        $minus24H = (new \DateTime())->modify('-24 hours');
        $datas = $weatherDataRepository->findSinceWeatherDataByStation($weatherStation, $minus24H);

        $dataFiltered = [];
        $temp = [];
        $humidity = [];

        /** @var WeatherData $data */
        foreach($datas as $data) {
                $dataFiltered[] = $data;
                $temp[] = $data->getTemperature();
                $humidity[] = $data->getHumidity();
        }


        return new Response($twig->render('feed/static.html.twig', [
            'data' => array_first($dataFiltered),
            'tx' => max($temp),
            'tn' => min($temp),
            'hrx' => max($humidity),
            'hrn' => min($humidity),
        ]));

    }

}
