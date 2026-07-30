<?php

namespace App\DataTables\Actions;

class RowAction
{
    protected string $name;
    protected string $label;
    protected string $icon = '';
    protected string $method = 'GET';
    protected ?string $emitEvent = null;
    protected ?string $confirmMessage = null;
    protected array $requiresRoles = [];
    protected ?string $url = null;

    public function __construct(string $name)
    {
        $this->name = $name;
        $this->label = ucfirst($name);
    }

    /**
     * Create a new row action instance.
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

    public function icon(string $icon): static
    {
        $this->icon = $icon;

        return $this;
    }

    public function method(string $method): static
    {
        $this->method = strtoupper($method);

        return $this;
    }

    /**
     * Emit an Alpine.js event when clicked.
     */
    public function emitEvent(string $event): static
    {
        $this->emitEvent = $event;

        return $this;
    }

    /**
     * Show a confirmation dialog before executing.
     */
    public function confirmMessage(string $message): static
    {
        $this->confirmMessage = $message;

        return $this;
    }

    /**
     * Restrict this action to specific role(s).
     */
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
     * Set a dynamic URL pattern (use :id as placeholder).
     */
    public function url(string $url): static
    {
        $this->url = $url;

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
            'icon' => $this->icon,
            'method' => $this->method,
            'emitEvent' => $this->emitEvent,
            'confirmMessage' => $this->confirmMessage,
            'url' => $this->url,
        ];
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getRequiresRole(): ?string
    {
        return $this->requiresRole;
    }
}
