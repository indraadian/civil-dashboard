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
    protected array $requiresRoles = [];
    protected array $requiresPermissions = [];

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
     * Restrict this action to users with the given permission(s).
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

    /**
     * Restrict this action to users with the given role(s).
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
     * Check if the current user is authorized to see this action.
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
