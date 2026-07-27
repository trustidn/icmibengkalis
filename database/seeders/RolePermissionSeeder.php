<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Single source of truth untuk RBAC (lihat docs/01-srs.md §1.4).
 * Idempoten: aman dijalankan berulang, termasuk saat menambah permission baru.
 */
class RolePermissionSeeder extends Seeder
{
    /** @var array<string, string[]> permission per modul */
    private array $permissions = [
        'members' => ['view', 'create', 'update', 'delete', 'import', 'export'],
        'expertise' => ['view', 'manage-fields', 'verify'],
        'organization' => ['view', 'manage'],
        'publishing' => ['view', 'create', 'update', 'delete', 'review', 'publish'],
        'archive' => ['view', 'create', 'update', 'delete', 'manage-access'],
        'events' => ['view', 'create', 'update', 'delete', 'check-in', 'report', 'certificates'],
        'gallery' => ['view', 'manage', 'manage-any'],
        'media-center' => ['view', 'manage'],
        'dashboard' => ['view', 'view-division'],
        'pages' => ['manage'],
        'announcements' => ['view', 'manage', 'manage-any'],
        'agenda' => ['view', 'manage', 'manage-any'],
        'contact' => ['view', 'reply'],
        'settings' => ['manage'],
        'users' => ['manage'],
    ];

    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $all = [];
        foreach ($this->permissions as $module => $abilities) {
            foreach ($abilities as $ability) {
                $all[] = Permission::findOrCreate("{$module}.{$ability}");
            }
        }

        $find = fn (string ...$patterns) => collect($all)
            ->filter(fn ($p) => collect($patterns)->contains(
                fn ($pat) => Str::is($pat, $p->name)
            ));

        Role::findOrCreate('super-admin')->syncPermissions($all);

        Role::findOrCreate('ketua')->syncPermissions($find(
            'dashboard.*', 'members.view', 'publishing.view', 'publishing.review', 'publishing.publish',
            'archive.view', 'events.view', 'events.report', 'organization.view', 'expertise.view',
            'announcements.view', 'agenda.view', 'contact.view',
        ));

        Role::findOrCreate('sekretaris')->syncPermissions($find(
            'dashboard.*', 'members.*', 'expertise.*', 'organization.*', 'publishing.*',
            'archive.*', 'events.*', 'gallery.*', 'media-center.*', 'pages.manage',
            'announcements.*', 'agenda.*', 'contact.*',
        ));

        Role::findOrCreate('bendahara')->syncPermissions($find(
            'dashboard.view', 'members.view', 'events.view', 'events.report', 'archive.view',
        ));

        Role::findOrCreate('ketua-divisi')->syncPermissions($find(
            'dashboard.view-division', 'members.view', 'publishing.view', 'publishing.review',
            'archive.view', 'archive.create', 'events.view', 'events.create', 'events.report',
            'organization.view', 'expertise.view',
        ));

        Role::findOrCreate('admin-divisi')->syncPermissions($find(
            'dashboard.view-division', 'members.view', 'publishing.view', 'publishing.create', 'publishing.update',
            'archive.view', 'archive.create', 'archive.update',
            'events.view', 'events.create', 'events.update', 'events.check-in',
            'gallery.view', 'gallery.manage', 'organization.view', 'expertise.view',
            'announcements.view', 'announcements.manage', 'agenda.view', 'agenda.manage',
        ));

        // Anggota: tanpa permission admin — akses area /akun lewat auth + policy.
        Role::findOrCreate('anggota');
    }
}
