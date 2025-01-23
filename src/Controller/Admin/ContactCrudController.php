<?php

namespace App\Controller\Admin;

use App\Entity\Contact;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use FOS\CKEditorBundle\Form\Type\CKEditorType;

class ContactCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Contact::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud->setEntityLabelInSingular('Demande de contact')
            ->setEntityLabelInPlural('Demandes de contact')
            ->setPageTitle("index", 'Symrecipe - Administration des demandes de contact')
            ->setPaginatorPageSize(20)
            // ->addFormTheme('@FOSCKEditor/Form/ckeditor_widget.html.twig')
        ;
        // pour addFormTheme il faut rajouter ce qu'on trouve dans configure twig: https://symfony.com/bundles/FOSCKEditorBundle/current/installation.html
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnIndex()->setFormTypeOption('disabled', 'disabled'),
            TextField::new('fullName'),
            TextField::new('email')
                ->setFormTypeOption('disabled', 'disabled'),
            TextEditorField::new('message'), // j'ai supprimé ckeditor car ne marchait pas avec la version 7.2 de symfony sinon demandait la version sous licence ->setFormType(CKEditorType::class)->setFormTypeOptions([
            //     'config_name' => 'main_config', // Utilise votre configuration CKEditor
            // ]),
            DateTimeField::new('createdAt')
                ->hideOnForm()
        ];
    }
}
