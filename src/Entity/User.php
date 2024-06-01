<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    private ?string $email = null;

    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 50)]
    private ?string $username = null;

    #[ORM\ManyToMany(targetEntity: Language::class, inversedBy: 'users')]
    private Collection $languages;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Project::class)]
    private Collection $projects;

    #[ORM\ManyToMany(targetEntity: Project::class, inversedBy: 'translators')]
    private Collection $project_translator;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Traduction::class)]
    private Collection $traductions;

    public function __construct()
    {
        $this->languages = new ArrayCollection();
        $this->projects = new ArrayCollection();
        $this->project_translator = new ArrayCollection();
        $this->traductions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    /**
     * @return Collection<int, Language>
     */
    public function getLanguages(): Collection
    {
        return $this->languages;
    }

    public function addLanguage(Language $language): self
    {
        if (!$this->languages->contains($language)) {
            $this->languages->add($language);
        }

        return $this;
    }

    public function removeLanguage(Language $language): self
    {
        $this->languages->removeElement($language);

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
            $project->setUser($this);
        }

        return $this;
    }

    public function removeProject(Project $project): self
    {
        if ($this->projects->removeElement($project)) {
            // set the owning side to null (unless already changed)
            if ($project->getUser() === $this) {
                $project->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Project>
     */
    public function getProjectTranslator(): Collection
    {
        return $this->project_translator;
    }

    public function addProjectTranslator(Project $projectTranslator): self
    {
        if (!$this->project_translator->contains($projectTranslator)) {
            $this->project_translator->add($projectTranslator);
        }

        return $this;
    }

    public function removeProjectTranslator(Project $projectTranslator): self
    {
        $this->project_translator->removeElement($projectTranslator);

        return $this;
    }

    /**
     * @return Collection<int, Traduction>
     */
    public function getTraductions(): Collection
    {
        return $this->traductions;
    }

    public function addTraduction(Traduction $traduction): self
    {
        if (!$this->traductions->contains($traduction)) {
            $this->traductions->add($traduction);
            $traduction->setUser($this);
        }

        return $this;
    }

    public function removeTraduction(Traduction $traduction): self
    {
        if ($this->traductions->removeElement($traduction)) {
            // set the owning side to null (unless already changed)
            if ($traduction->getUser() === $this) {
                $traduction->setUser(null);
            }
        }

        return $this;
    }
}
