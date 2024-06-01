<?php

namespace App\Controller;

use App\Entity\Project;
use App\Form\ProjectType;
use App\Entity\Source;
use App\Form\SourceType;
use App\Service\ProjectService;
use App\Repository\ProjectRepository;
use App\Service\TraductionService;
use App\Entity\Traduction;
use App\Form\TraductionType;
use App\Repository\TraductionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Psr\Log\LoggerInterface;

class ProjectController extends AbstractController
{
    private ProjectService $projectService;
    private ProjectRepository $projectRepository;
    private TraductionService $traductionService;
    private TraductionRepository $traductionRepository;
    private LoggerInterface $logger;

    public function __construct(ProjectService $projectService, ProjectRepository $projectRepository, TraductionService $traductionService, TraductionRepository $traductionRepository, LoggerInterface $logger)
    {
        $this->projectService = $projectService;
        $this->projectRepository = $projectRepository;
        $this->traductionService = $traductionService;
        $this->traductionRepository = $traductionRepository;
        $this->logger = $logger;
    }

    #[Route('/projects', name: 'app_projects')]
    public function index(): Response
    {
        $user = $this->getUser();
        $projects = $this->projectService->getProjectsByUser($user);

        return $this->render('project/index.html.twig', [
            'projects' => $projects,
        ]);
    }

    #[Route('/projects/new', name: 'app_projects_create')]
    public function new(Request $request, ProjectService $projectService): Response
    {
        $user = $this->getUser();
        $project = new Project();

        $project->setUser($user);
        $form = $this->createForm(ProjectType::class, $project, ['user' => $user]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $projectService->createProject($project);
            return $this->redirectToRoute('app_projects');
        }

        return $this->render('project/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }


    #[Route('/project/{id}/sources', name:'app_project_sources')]
    public function projectSources(Request $request, Project $project): Response
    {
        $source = new Source();
        $form = $this->createForm(SourceType::class, $source);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->projectService->createSource(['name' => $source->getName(), 'content' => $source->getContent(), 'project' => $project]);
            return $this->redirectToRoute('app_project_sources', ['id' => $project->getId()]);
        }

        $sources = $project->getSources();
        return $this->render('project/sources.html.twig', [
            'project' => $project,
            'sources' => $sources,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/project/{id}/sources/upload_csv', name: 'app_sources_upload_csv')]
    public function uploadCsv(Request $request, Project $project): Response
    {
        $csvFile = $request->files->get('csv_file');

        try {
            $this->projectService->importSourcesFromCsv($csvFile, $project);
        } catch (FileException $e) {
            // handle exception
        }

        return $this->redirectToRoute('app_project_sources', ['id' => $project->getId()]);
    }

//this function will display all projects which have a target language that the user knows
    #[Route('/projects/target-languages', name: 'app_projects_by_target_language')]
    public function projectsByLanguage(): Response
    {
        $user = $this->getUser();

        $userLanguages = $user->getLanguages()->toArray();

        $projects = $this->projectRepository->findByTargetLanguagesAndLanguage($userLanguages);

        return $this->render('project/projects_by_target_language.html.twig', [
            'projects' => $projects,
        ]);
    }

    #[Route('/project/{id}/subscribe', name: 'app_project_subscribe')]
    public function subscribe(Project $project): Response
    {
        $user = $this->getUser();
        $this->projectService->subscribeUserToProject($project, $user);

        // redirect the user back to the project page
        //return $this->redirectToRoute('app_project_show', ['id' => $project->getId()]);
        return $this->redirectToRoute('app_projects_by_target_language');
    }

    #[Route('/projects-to-translate', name: 'app_projects_to_translate')]
    public function projectsToTranslate(): Response
    {
        $user = $this->getUser();
        $projects = $user->getProjectTranslator();

        return $this->render('project/projects_to_translate.html.twig', [
            'projects' => $projects,
        ]);
    }

    #[Route('/project-to-translate/{id}/sources', name: 'app_project_to_translate_sources')]
    public function sourcesByProject(Project $project): Response
    {
        $user = $this->getUser();

        if (!$project->getTranslators()->contains($user)) {
            throw $this->createNotFoundException();
        }

        $sources = $project->getSources();

        return $this->render('project/sources_by_project.html.twig', [
            'project' => $project,
            'sources' => $sources,
        ]);
    }

    #[Route('/source/{id}/add-translation', name: 'app_add_translation')]
    public function addTranslation(Request $request, Source $source): Response
    {
        $translation = new Traduction();
        $translation->setSource($source);
        $translation->setUser($this->getUser());

        $form = $this->createForm(TraductionType::class, $translation, [
            'user' => $this->getUser(),
            'project' => $source->getProject(),
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $existingTranslation = $this->traductionRepository->findOneBy([
                'user' => $this->getUser(),
                'language' => $form->get('language')->getData(),
                'source' => $source,
            ]);
            if ($existingTranslation) {
                $existingTranslation->setContent($translation->getContent());
                $this->traductionService->updateTranslation($existingTranslation);
            } else {
                $this->traductionService->createTranslation($translation);
            }

            return $this->redirectToRoute('app_project_to_translate_sources', ['id' => $source->getProject()->getId()]);
        }

        return $this->render('translation/add_translation.html.twig', [
            'form' => $form->createView(),
            'source' => $source,
        ]);
    }


}
