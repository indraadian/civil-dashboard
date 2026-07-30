<?php

namespace App\DataTables\Actions;

class BulkAction
{
    protected string $name;
    protected string $label;
    protected string $endpoint = '';
    protected string $method = 'POST';
    protected ?string $confirmMessage = null;
    protected array $requiresRoles = [];

    public function __construct(string $name)
    {
        $this->name = $name;
        $this->label = ucfirst($name);
    }

    /**
     * Create a new bulk action instance.
     */
    public static function make(string $name): static
    {
        return new static($name);
    }

    public function label(string $label): static
    {
        $this->label = $label;

        return $this;
    }

    public function endpoint(string $endpoint): static
    {
        $this->endpoint = $endpoint;

        return $this;
    }

    public function method(string $method): static
    {
        $this->method = strtoupper($method);

        return $this;
    }

    public function confirmMessage(string $message): static
    {
        $this->confirmMessage = $message;

        return $this;
    }

    public function requiresRole(string|array ...$roles): static
    {
        foreach ($roles as $role) {
            if (is_array($role)) {
                $this->requiresRoles = array_merge($this->requiresRoles, $role);
            } else {
                $this->requiresRoles[] = $role;
            }
        }
        $this->requiresRoles = array_unique($this->requiresRoles);

        return $this;
    }

    /**
     * Check if the current user can see this action.
     */
    public function isAuthorized(): bool
    {
        if (empty($this->requiresRoles)) {
            return true;
        }

        $user = auth()->user();

        return $user && in_array($user->role, $this->requiresRoles, true);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'label' => $this->label,
            'endpoint' => $this->endpoint,
            'method' => $this->method,
            'confirmMessage' => $this->confirmMessage,
        ];
    }
}
