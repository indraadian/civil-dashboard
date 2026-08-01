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
    protected array $requiresPermissions = [];

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

    /**
     * Restrict this action to specific permission(s).
     */
    public function requiresPermission(string|array ...$permissions): static
    {
        foreach ($permissions as $permission) {
            if (is_array($permission)) {
                $this->requiresPermissions = array_merge($this->requiresPermissions, $permission);
            } else {
                $this->requiresPermissions[] = $permission;
            }
        }
        $this->requiresPermissions = array_unique($this->requiresPermissions);

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
        $user = auth()->user();

        if (!$user) {
            return false;
        }

        if (!empty($this->requiresPermissions)) {
            foreach ($this->requiresPermissions as $permission) {
                if ($user->can($permission)) {
                    return true;
                }
            }

            return false;
        }

        if (!empty($this->requiresRoles)) {
            return in_array($user->role, $this->requiresRoles, true) || $user->hasAnyRole($this->requiresRoles);
        }

        return true;
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
