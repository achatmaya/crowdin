<?php

namespace App\Entity;

use App\Repository\LanguageRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LanguageRepository::class)]
class Language
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $label = null;

    #[ORM\Column(length: 3)]
    private ?string $code = null;

    #[ORM\ManyToMany(targetEntity: User::class, mappedBy: 'languages')]
    private Collection $users;

    #[ORM\OneToMany(mappedBy: 'language', targetEntity: Project::class)]
    private Collection $projects;

    #[ORM\ManyToMany(targetEntity: Project::class, mappedBy: 'target_languages')]
    private Collection $projects_target;

    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->projects = new ArrayCollection();
        $this->projects_target = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(string $label): self
    {
        $this->label = $label;

        return $this;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
            $user->addLanguage($this);
        }

        return $this;
    }

    public function removeUser(User $user): self
    {
        if ($this->users->removeElement($user)) {
            $user->removeLanguage($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, Project>
     */
    public function getProjects(): Collection
    {
        return $this->projects;
    }

    public function addProject(Project $project): self
    {
        if (!$this->projects->contains($project)) {
            $this->projects->add($project);
            $project->setLanguage($this);
        }

        return $this;
    }

    public function removeProject(Project $project): self
    {
        if ($this->projects->removeElement($project)) {
            // set the owning side to null (unless already changed)
            if ($project->getLanguage() === $this) {
                $project->setLanguage(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Project>
     */
    public function getProjectsTarget(): Collection
    {
        return $this->projects_target;
    }

    public function addProjectsTarget(Project $projectsTarget): self
    {
        if (!$this->projects_target->contains($projectsTarget)) {
            $this->projects_target->add($projectsTarget);
            $projectsTarget->addTargetLanguage($this);
        }

        return $this;
    }

    public function removeProjectsTarget(Project $projectsTarget): self
    {
        if ($this->projects_target->removeElement($projectsTarget)) {
            $projectsTarget->removeTargetLanguage($this);
        }

        return $this;
    }
}
