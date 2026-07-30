# Refactor Plan: Migrate Hardcoded User, RW, and RT CRUD to Configuration-Driven Architecture

Migrate the hardcoded User, RW, and RT management modules to the **Configuration-Driven Architecture** standard established by the **Civil** reference module.

## User Review Required

> [!IMPORTANT]
> **No Breaking Changes to DB Schema**: The database tables and API endpoints for store/update/delete operations will remain intact to guarantee full backward compatibility.
> 
> **Data Grid Standard**: The hardcoded HTML tables in `settings/users`, `settings/rws`, and `settings/rts` will be replaced by the reusable `<x-datatable>` component backed by dedicated `DataTableDefinition` classes.

## Proposed Changes

---

### Component 1: CRUD Engine Enhancements (Reusable Core)

Enhance generic engine classes so they can seamlessly handle complex queries, relationship filters, and multi-role authorizations without hardcoded hacks.

#### [MODIFY] [Filter.php](file:///d:/Projects/Laravel/Apps/civil-dashboard/app/DataTables/Filters/Filter.php)
- Add `applyUsing(\Closure $callback)` support to allow custom filtering logic (e.g., relation queries or location scope filtering).

#### [MODIFY] [RowAction.php](file:///d:/Projects/Laravel/Apps/civil-dashboard/app/DataTables/Actions/RowAction.php)
#### [MODIFY] [BulkAction.php](file:///d:/Projects/Laravel/Apps/civil-dashboard/app/DataTables/Actions/BulkAction.php)
#### [MODIFY] [ToolbarAction.php](file:///d:/Projects/Laravel/Apps/civil-dashboard/app/DataTables/Actions/ToolbarAction.php)
- Support multiple roles in `requiresRole(string|array ...$roles)` so actions can be assigned to multiple roles (e.g. `admin`, `super_admin`) without overwriting previous roles.

---

### Component 2: DataTable Definitions

Create configuration-driven DataTable definition classes under `app/DataTables/Definitions/` following `CivilDataTable` patterns.

#### [NEW] [UserDataTable.php](file:///d:/Projects/Laravel/Apps/civil-dashboard/app/DataTables/Definitions/UserDataTable.php)
- Define query with eagerly loaded `locationScopes.rw` and `locationScopes.rt`.
- Columns: Name (AvatarColumn with initials and role styling), Email, Role (BadgeColumn), Location Scope (Computed/Formatter showing assigned RW/RT badges), Created At (DateColumn), ActionColumn.
- Filters: Name (TextFilter), Email (TextFilter), Role (SelectFilter).
- Actions: Edit (`open-edit-user-modal`), Delete (with confirmation).
- Bulk Actions: Delete Bulk.
- Toolbar Actions: Create User (`open-user-modal`).

#### [NEW] [RwDataTable.php](file:///d:/Projects/Laravel/Apps/civil-dashboard/app/DataTables/Definitions/RwDataTable.php)
- Define query with `withCount('rts')`.
- Columns: RW Code, RW Name, Total RT (Computed/Text), Status (BadgeColumn: Active/Inactive), ActionColumn.
- Filters: RW Code (TextFilter), RW Name (TextFilter), Status (SelectFilter).
- Actions: Edit (`open-edit-rw-modal`), Delete (with confirmation).
- Bulk Actions: Delete Bulk.
- Toolbar Actions: Create RW (`open-rw-modal`).

#### [NEW] [RtDataTable.php](file:///d:/Projects/Laravel/Apps/civil-dashboard/app/DataTables/Definitions/RtDataTable.php)
- Define query with eagerly loaded `rw`.
- Columns: RT Code, RT Name, Parent RW (TextColumn with prefix/formatting), Status (BadgeColumn: Active/Inactive), ActionColumn.
- Filters: RT Code (TextFilter), RT Name (TextFilter), Parent RW (SelectFilter/RelationFilter), Status (SelectFilter).
- Actions: Edit (`open-edit-rt-modal`), Delete (with confirmation).
- Bulk Actions: Delete Bulk.
- Toolbar Actions: Create RT (`open-rt-modal`).

---

### Component 3: Controllers & Routes

Refactor `SettingController`, `RwController`, and `RtController` to use `HasDataTable` trait and provide JSON endpoints for DataTables.

#### [MODIFY] [SettingController.php](file:///d:/Projects/Laravel/Apps/civil-dashboard/app/Http/Controllers/SettingController.php)
- Use `HasDataTable` trait.
- Refactor `users()` to generate `$config = $this->dataTableConfig(new UserDataTable())` and pass it to view.
- Add `usersData(Request $request)` JSON response via `$this->dataTableResponse($request, new UserDataTable())`.

#### [MODIFY] [RwController.php](file:///d:/Projects/Laravel/Apps/civil-dashboard/app/Http/Controllers/RwController.php)
- Use `HasDataTable` trait.
- Refactor `index()` to generate `$config = $this->dataTableConfig(new RwDataTable())` and pass it to view.
- Add `data(Request $request)` JSON response via `$this->dataTableResponse($request, new RwDataTable())`.
- Add `destroyBulk(Request $request)` for bulk deletion of RW records.

#### [MODIFY] [RtController.php](file:///d:/Projects/Laravel/Apps/civil-dashboard/app/Http/Controllers/RtController.php)
- Use `HasDataTable` trait.
- Refactor `index()` to generate `$config = $this->dataTableConfig(new RtDataTable())` and pass it to view.
- Add `data(Request $request)` JSON response via `$this->dataTableResponse($request, new RtDataTable())`.
- Add `destroyBulk(Request $request)` for bulk deletion of RT records.

#### [MODIFY] [web.php](file:///d:/Projects/Laravel/Apps/civil-dashboard/routes/web.php)
- Register `GET /settings/users/data` -> `settings.users.data`.
- Register `GET /settings/rws/data` -> `settings.rws.data`.
- Register `POST /settings/rws/delete-bulk` -> `settings.rws.destroyBulk`.
- Register `GET /settings/rts/data` -> `settings.rts.data`.
- Register `POST /settings/rts/delete-bulk` -> `settings.rts.destroyBulk`.

---

### Component 4: Blade Views

Update views to use the `<x-datatable>` component and remove hardcoded HTML table partials.

#### [MODIFY] [users.blade.php](file:///d:/Projects/Laravel/Apps/civil-dashboard/resources/views/pages/settings/users.blade.php)
- Replace hardcoded table and custom search JS with `<x-datatable :config="$config" data-url="{{ route('settings.users.data') }}" base-url="/settings/users" title="Pengaturan User" />`.
- Retain create/edit user modal components.

#### [MODIFY] [rws.blade.php](file:///d:/Projects/Laravel/Apps/civil-dashboard/resources/views/pages/settings/rws.blade.php)
- Replace hardcoded table with `<x-datatable :config="$config" data-url="{{ route('settings.rws.data') }}" base-url="/settings/rws" title="Master RW" />`.
- Retain create/edit RW modal components.

#### [MODIFY] [rts.blade.php](file:///d:/Projects/Laravel/Apps/civil-dashboard/resources/views/pages/settings/rts.blade.php)
- Replace hardcoded table with `<x-datatable :config="$config" data-url="{{ route('settings.rts.data') }}" base-url="/settings/rts" title="Master RT" />`.
- Retain create/edit RT modal components.

#### [DELETE] [user-table.blade.php](file:///d:/Projects/Laravel/Apps/civil-dashboard/resources/views/pages/settings/partials/user-table.blade.php)
#### [DELETE] [rw-table.blade.php](file:///d:/Projects/Laravel/Apps/civil-dashboard/resources/views/pages/settings/partials/rw-table.blade.php)
#### [DELETE] [rt-table.blade.php](file:///d:/Projects/Laravel/Apps/civil-dashboard/resources/views/pages/settings/partials/rt-table.blade.php)

---

## Verification Plan

### Automated Tests
- Run `php artisan test` or test scripts to verify routes, controllers, and models.

### Manual Verification
- Test User management page (`/settings/users`): search, filter, pagination, create, edit, delete, bulk delete.
- Test Master RW page (`/settings/rws`): search, filter, pagination, create, edit, delete, bulk delete.
- Test Master RT page (`/settings/rts`): search, filter by RW, pagination, create, edit, delete, bulk delete.
- Test Civil module (`/civils`) to ensure no regression was introduced.
