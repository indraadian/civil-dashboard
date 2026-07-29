<?php

namespace App\DataTables\Actions;

class ToolbarAction
{
    protected string $name;
    protected string $label;
    protected string $icon = 'plus';
    protected ?string $emitEvent = null;
    protected ?string $url = null;
    protected string $method = 'GET';
    protected string $variant = 'primary';
    protected ?string $requiresRole = null;

    public function __construct(string $name)
    {
        $this->name  = $name;
        $this->label = ucfirst($name);
    }

    /**
     * Create a new toolbar action instance.
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

    /**
     * Set the icon name. Supported: 'plus', 'download', 'upload'.
     */
    public function icon(string $icon): static
    {
        $this->icon = $icon;

        return $this;
    }

    /**
     * Dispatch an Alpine.js custom event when clicked.
     */
    public function emitEvent(string $event): static
    {
        $this->emitEvent = $event;

        return $this;
    }

    /**
     * Navigate to a URL when clicked (shows a loading state).
     */
    public function url(string $url): static
    {
        $this->url = $url;

        return $this;
    }

    /**
     * HTTP method for url-based actions: 'GET' or 'POST'.
     */
    public function method(string $method): static
    {
        $this->method = strtoupper($method);

        return $this;
    }

    /**
     * Button style variant: 'primary', 'secondary', or 'danger'.
     */
    public function variant(string $variant): static
    {
        $this->variant = $variant;

        return $this;
    }

    /**
     * Restrict this action to users with the given role.
     */
    public function requiresRole(string $role): static
    {
        $this->requiresRole = $role;

        return $this;
    }

    /**
     * Check if the current user is authorized to see this action.
     */
    public function isAuthorized(): bool
    {
        if (! $this->requiresRole) {
            return true;
        }

        $user = auth()->user();

        return $user && $user->role === $this->requiresRole;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'name'      => $this->name,
            'label'     => $this->label,
            'icon'      => $this->icon,
            'emitEvent' => $this->emitEvent,
            'url'       => $this->url,
            'method'    => $this->method,
            'variant'   => $this->variant,
        ];
    }
}
