<?php

namespace App\Controller\Admin;

use App\Entity\WeatherData;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\{AssociationField, DateTimeField, IdField, NumberField, TextField};

class WeatherDataCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return WeatherData::class;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield AssociationField::new('weatherStation', 'Station météo');
        yield DatetimeField::new('createdAt', 'Date')->setFormTypeOption('input', 'datetime');
        yield NumberField::new('temperature', 'Température');
        yield NumberField::new('humidity', 'Humidité');

    }
}
