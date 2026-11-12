<?php

namespace App\Entity;

use App\Repository\WeatherStationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: WeatherStationRepository::class)]
class WeatherStation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $name = null;

    #[ORM\Column(type: 'uuid', nullable: true)]
    private ?Uuid $uuid = null;

    /**
     * @var Collection<int, WeatherData>
     */
    #[ORM\OneToMany(targetEntity: WeatherData::class, mappedBy: 'weatherStation')]
    private Collection $weatherData;

    public function __construct()
    {
        $this->weatherData = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getUuid(): ?Uuid
    {
        return $this->uuid;
    }

    public function setUuid(?Uuid $uuid): static
    {
        $this->uuid = $uuid;

        return $this;
    }

    /**
     * @return Collection<int, WeatherData>
     */
    public function getWeatherData(): Collection
    {
        return $this->weatherData;
    }

    public function addWeatherData(WeatherData $weatherData): static
    {
        if (!$this->weatherData->contains($weatherData)) {
            $this->weatherData->add($weatherData);
            $weatherData->setWeatherStation($this);
        }

        return $this;
    }

    public function removeWeatherData(WeatherData $weatherData): static
    {
        if ($this->weatherData->removeElement($weatherData)) {
            // set the owning side to null (unless already changed)
            if ($weatherData->getWeatherStation() === $this) {
                $weatherData->setWeatherStation(null);
            }
        }

        return $this;
    }


    public function __toString()
    {
        return $this->name;
    }
}
