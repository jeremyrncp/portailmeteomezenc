<?php

namespace App\Controller\Admin;

use App\Entity\WeatherStation;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\{IdField, TextField};
use Symfony\Component\Form\Extension\Core\Type\UuidType;

class WeatherStationCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return WeatherStation::class;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield TextField::new('name', 'Nom');
        yield TextField::new('uuid', 'Uuid')->setFormType(UuidType::class);
    }
}
