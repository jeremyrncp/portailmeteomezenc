<?php

namespace App\Controller;

use App\Entity\WeatherData;
use App\Entity\WeatherStation;
use App\Repository\WeatherStationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class ApIController
{
    #[Route('/webhook/datacake', name: 'app_webhook_datacake', methods: ['POST'])]
    public function index(Request $request, EntityManagerInterface $entityManager, WeatherStationRepository $weatherStationRepository): JsonResponse
    {
        $weatherStationsFiltered = $this->filterWeatherStationByUuid($weatherStationRepository->findAll());

        $weatherDataFiltered = [];

        $data = json_decode($request->getContent(), true);

        file_put_contents(__DIR__ . "/../../public/webhook/" . time() . ".json", $request->getContent());

        foreach ($data["data"] as $d) {
            if (array_key_exists($d["device"], $weatherStationsFiltered)) {

                if (array_key_exists($d["timestamp"], $weatherDataFiltered) === false) {
                    $weatherDataFiltered[$d["timestamp"]] = [];
                }

                $weatherDataFiltered[$d["timestamp"]]["weatherStation"] = $weatherStationsFiltered[$d["device"]];

                if ($d["field"] === "SHTTEMP") {
                    $weatherDataFiltered[$d["timestamp"]]["temperature"] = $d["value"];
                }

                if ($d["field"] === "SHTHUM") {
                    $weatherDataFiltered[$d["timestamp"]]["humidity"] = $d["value"];
                }
            }
        }


        foreach($weatherDataFiltered as $timestamp => $weatherData) {
            $weatherDataEntity = new WeatherData();
            $weatherDataEntity->setWeatherStation($weatherData["weatherStation"]);
            $weatherDataEntity->setTemperature($weatherData["temperature"]);
            $weatherDataEntity->setHumidity($weatherData["humidity"]);
            $weatherDataEntity->setCreatedAt(new \DateTime($timestamp));

            $entityManager->persist($weatherDataEntity);
            $entityManager->flush();
        }

        return new JsonResponse(["status" => "ok"]);
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
