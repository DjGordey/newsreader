<?php

namespace App\Controller\Admin;

use App\Entity\News;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;

class NewsCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return News::class;
    }


    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setSearchFields(['dateAt', 'title', 'preview'])
            ->setDefaultSort(['createdAt' => 'DESC']);
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(EntityFilter::new('source'));
    }

    public function configureFields(string $pageName): iterable
    {
        yield AssociationField::new('source');
        yield TextField::new('title');
        yield TextareaField::new('preview');
        yield TextareaField::new('text');
        yield TextField::new('image');
        yield DateTimeField::new('dateAt');

        $createdAt = DateTimeField::new('createdAt');

        if (Crud::PAGE_EDIT === $pageName) {
            yield $createdAt->setFormTypeOption('disabled', true);
        } else {
            yield $createdAt;
        }
    }

}
