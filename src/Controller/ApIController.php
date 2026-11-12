<?php

namespace App\Controller;

use App\Entity\WeatherData;
use App\Entity\WeatherStation;
use App\Repository\WeatherStationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ApIController
{
    #[Route('/webhook/datacake', name: 'app_webhook_datacake', methods: ['POST'])]
    public function index(Request $request, EntityManagerInterface $entityManager, WeatherStationRepository $weatherStationRepository): Response
    {
        $weatherStationsFiltered = $this->filterWeatherStationByUuid($weatherStationRepository->findAll());

        $weatherDataFiltered = [];

        $data = json_decode($request->getContent(), true);

        foreach ($data["data"] as $d) {
            if ($d["device"] === array_key_exists($d["device"], $weatherStationsFiltered)) {

                if (array_key_exists($d["timestamp"], $weatherDataFiltered) === false) {
                    $weatherDataFiltered[$d["timestamp"]] = [];
                }

                $weatherDataFiltered[$d["timestamp"]]["weatherStation"] = $weatherStationsFiltered[$d["device"]];

                if ($d["field"] === "TEMPERATURE") {
                    $weatherDataFiltered[$d["timestamp"]]["temperature"] = $d["value"];
                }

                if ($d["field"] === "HUMIDITY") {
                    $weatherDataFiltered[$d["timestamp"]]["humidity"] = $d["value"];
                }
            }
        }


        foreach($weatherDataFiltered as $timestamp => $weatherData) {
            $weatherData = new WeatherData();
            $weatherData->setWeatherStation($weatherData["weatherStation"]);
            $weatherData->setTemperature($weatherData["temperature"]);
            $weatherData->setHumidity($weatherData["humidity"]);
            $weatherData->setCreatedAt(new \DateTime($timestamp));

            $entityManager->persist($weatherData);
            $entityManager->flush();
        }
    }

    private function filterWeatherStationByUuid($weatherStations): array
    {
        $weatherStationsFiltered = [];

        /** @var WeatherStation $weatherStation */
        foreach ($weatherStations as $weatherStation) {
            $weatherStationsFiltered[$weatherStation->getUuid()->toString()] = $weatherStation;
        }

        return $weatherStationsFiltered;
    }
}
