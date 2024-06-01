<?php
namespace App\Service;

use App\Entity\Traduction;
use Doctrine\ORM\EntityManagerInterface;
class TraductionService
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function createTranslation(Traduction $translation): void
    {
        $this->entityManager->persist($translation);
        $this->entityManager->flush();
    }

    public function updateTranslation(Traduction $existingTranslation)
    {
        $this->entityManager->persist($existingTranslation);
        $this->entityManager->flush();
    }
}