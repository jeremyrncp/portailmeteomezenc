<?php

namespace App\Controller\Admin;

use App\Entity\Forecast;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\{AssociationField, DateTimeField, IdField, TextareaField};

class ForecastCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Forecast::class;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield DateTimeField::new('createdAt', 'Date')->setFormTypeOption('input', 'datetime');
        yield TextareaField::new('foreca', 'Prévision');
        yield AssociationField::new('city', 'Ville');
    }
}
