<?php
// src/Service/ProjectService.php

namespace App\Service;

use App\Entity\Project;
use App\Entity\Source;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ProjectService
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function getProjectsByUser($user)
    {
        return $this->entityManager->getRepository(Project::class)->findBy(['user' => $user]);
    }

    public function createProject($project)
    {
        $this->entityManager->persist($project);
        $this->entityManager->flush();

        return $project;
    }

    public function createSource(array $data)
    {
        $source = new Source();
        $source->setName(mb_convert_encoding($data['name'], 'UTF-8'));
        $source->setContent(mb_convert_encoding($data['content'], 'UTF-8'));
        $source->setProject($data['project']);

        $this->entityManager->persist($source);
        $this->entityManager->flush();

        return $source;
    }

    public function importSourcesFromCsv($csvFile, $project)
    {
        if (!$csvFile instanceof UploadedFile) {
            throw new FileException('Invalid file');
        }

        $csv = new \SplFileObject($csvFile->getPathname());
        $csv->setFlags(\SplFileObject::READ_CSV | \SplFileObject::SKIP_EMPTY | \SplFileObject::DROP_NEW_LINE);
        $csv->setCsvControl(',', '"', '\\');

        foreach ($csv as $row) {
            if ($row) {
                if($row[0] !== 'key;traduction') {
                    $sourceData = explode(';', $row[0]);
                    $sourceName = $sourceData[0] ?? '';
                    $sourceContent = $sourceData[1] ?? '';

                    $sourceContent .= implode('', array_slice($row, 1));

                    $source = new Source();
                    $source->setName(mb_convert_encoding($sourceName, 'UTF-8'));
                    $source->setContent(mb_convert_encoding($sourceContent, 'UTF-8'));
                    $source->setProject($project);

                    $this->entityManager->persist($source);
                }
            }
        }

        $this->entityManager->flush();
    }

    public function subscribeUserToProject(Project $project, User $user): void
    {
        $project->addTranslator($user);
        $this->entityManager->flush();
    }
}
