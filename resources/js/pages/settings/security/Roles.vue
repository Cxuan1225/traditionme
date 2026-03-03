<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed, onMounted, ref, watch } from 'vue';
import RoleController from '@/actions/App/Http/Controllers/Security/RoleController';
import RolePermissionController from '@/actions/App/Http/Controllers/Security/RolePermissionController';
import UserRoleController from '@/actions/App/Http/Controllers/Security/UserRoleController';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import AdminLayout from '@/layouts/admin/Layout.vue';
import { index as rolesIndexRoute } from '@/routes/security/roles';
import type { BreadcrumbItem } from '@/types';

type PermissionResource = {
    id: number;
    name: string;
    guard_name: string;
};

type RoleResource = {
    id: number;
    name: string;
    guard_name: string;
    permissions: PermissionResource[];
};

type UserSecurityResource = {
    id: number;
    name: string;
    email: string;
    roles: RoleResource[];
};

type PermissionGroup = {
    key: string;
    label: string;
    permissions: PermissionResource[];
};

type PermissionPreset = 'all' | 'read' | 'write' | 'manage';

type Capabilities = {
    canViewRoles?: boolean;
    canCreateRoles?: boolean;
    canManageRolePermissions?: boolean;
    canAssignUserRoles?: boolean;
};

type Props = {
    initialRoles?: RoleResource[];
    initialPermissions?: PermissionResource[];
    capabilities?: Capabilities;
};

const props = defineProps<Props>();

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Security roles',
        href: rolesIndexRoute(),
    },
];

const loading = ref<boolean>(false);
const submittingCreateRole = ref<boolean>(false);
const submittingSyncPermissions = ref<boolean>(false);
const submittingAssignUserRoles = ref<boolean>(false);
const roles = ref<RoleResource[]>(props.initialRoles ?? []);
const permissions = ref<PermissionResource[]>(props.initialPermissions ?? []);
const roleSearch = ref<string>('');
const pageError = ref<string | null>(null);
const pageSuccess = ref<string | null>(null);
const selectedRoleId = ref<number | null>(roles.value[0]?.id ?? null);

const createRoleName = ref<string>('');
const createRolePermissionNames = ref<string[]>([]);
const rolePermissionNames = ref<string[]>([]);
const assignUserId = ref<string>('');
const assignUserRoleNames = ref<string[]>([]);
const assignedUser = ref<UserSecurityResource | null>(null);
const syncRiskConfirmed = ref<boolean>(false);
const assignRiskConfirmed = ref<boolean>(false);

const abilities = computed<Required<Capabilities>>(() => ({
    canViewRoles: props.capabilities?.canViewRoles ?? true,
    canCreateRoles: props.capabilities?.canCreateRoles ?? true,
    canManageRolePermissions:
        props.capabilities?.canManageRolePermissions ?? true,
    canAssignUserRoles: props.capabilities?.canAssignUserRoles ?? true,
}));

const selectedRole = computed<RoleResource | null>(
    () => roles.value.find((role) => role.id === selectedRoleId.value) ?? null,
);

const visibleRoles = computed<RoleResource[]>(() => {
    const query = roleSearch.value.trim().toLowerCase();
    if (query === '') {
        return roles.value;
    }

    return roles.value.filter((role) =>
        [role.name, role.guard_name].join(' ').toLowerCase().includes(query),
    );
});

const availableRoleNames = computed<string[]>(() =>
    roles.value.map((role) => role.name),
);

const normalizePermissionDomain = (permissionName: string): string => {
    const [segment] = permissionName.split(/[:._\s-]/);
    const normalized = segment?.trim().toLowerCase() ?? '';

    return normalized === '' ? 'general' : normalized;
};

const formatDomainLabel = (domain: string): string =>
    domain
        .split(/[-_]/)
        .filter((part) => part !== '')
        .map((part) => part[0]?.toUpperCase() + part.slice(1))
        .join(' ');

const permissionGroups = computed<PermissionGroup[]>(() => {
    const buckets = new Map<string, PermissionResource[]>();

    permissions.value.forEach((permission) => {
        const key = normalizePermissionDomain(permission.name);
        const group = buckets.get(key) ?? [];
        group.push(permission);
        buckets.set(key, group);
    });

    return Array.from(buckets.entries())
        .map(([key, groupPermissions]) => ({
            key,
            label: formatDomainLabel(key),
            permissions: groupPermissions.sort((left, right) =>
                left.name.localeCompare(right.name),
            ),
        }))
        .sort((left, right) => left.label.localeCompare(right.label));
});

const roleDomainCount = (role: RoleResource): number =>
    new Set(
        parsePermissionCollection(
            (role as { permissions?: unknown }).permissions,
        ).map((permission) => normalizePermissionDomain(permission.name)),
    ).size;

const permissionPresetKeywords: Record<
    Exclude<PermissionPreset, 'all'>,
    string[]
> = {
    read: ['view', 'read', 'list', 'index', 'show', 'browse'],
    write: ['create', 'store', 'update', 'edit'],
    manage: ['delete', 'manage', 'assign', 'sync', 'publish', 'approve'],
};

const matchesPreset = (
    permissionName: string,
    preset: PermissionPreset,
): boolean => {
    if (preset === 'all') {
        return true;
    }

    const lowered = permissionName.toLowerCase();
    return permissionPresetKeywords[preset].some((keyword) =>
        lowered.includes(keyword),
    );
};

const selectedRolePermissionSet = computed<string[]>(() =>
    selectedRole.value
        ? parsePermissionCollection(
              (selectedRole.value as { permissions?: unknown }).permissions,
          )
              .map((permission) => permission.name)
              .sort((left, right) => left.localeCompare(right))
        : [],
);

const syncSummary = computed<{ added: string[]; removed: string[] }>(() => {
    const original = new Set(selectedRolePermissionSet.value);
    const staged = new Set(rolePermissionNames.value);

    const added = Array.from(staged).filter((name) => !original.has(name));
    const removed = Array.from(original).filter((name) => !staged.has(name));

    return {
        added: added.sort((left, right) => left.localeCompare(right)),
        removed: removed.sort((left, right) => left.localeCompare(right)),
    };
});

const syncHasChanges = computed<boolean>(
    () =>
        syncSummary.value.added.length > 0 ||
        syncSummary.value.removed.length > 0,
);

const syncNeedsRiskConfirm = computed<boolean>(
    () => syncSummary.value.removed.length > 0,
);

const assignTargetUserId = computed<number | null>(() => {
    const trimmed = assignUserId.value.trim();
    if (trimmed === '') {
        return null;
    }

    const parsed = Number.parseInt(trimmed, 10);
    return Number.isNaN(parsed) || parsed < 1 ? null : parsed;
});

const knownAssignedRoleNames = computed<string[]>(() => {
    if (
        assignedUser.value === null ||
        assignTargetUserId.value === null ||
        assignedUser.value.id !== assignTargetUserId.value
    ) {
        return [];
    }

    return assignedUser.value.roles.map((role) => role.name);
});

const assignSummary = computed<{ added: string[]; removed: string[] }>(() => {
    const current = new Set(knownAssignedRoleNames.value);
    const staged = new Set(assignUserRoleNames.value);

    const added = Array.from(staged).filter((name) => !current.has(name));
    const removed = Array.from(current).filter((name) => !staged.has(name));

    return {
        added: added.sort((left, right) => left.localeCompare(right)),
        removed: removed.sort((left, right) => left.localeCompare(right)),
    };
});

const assignIncludesAdmin = computed<boolean>(() =>
    assignUserRoleNames.value.some(
        (roleName) => roleName.toLowerCase() === 'admin',
    ),
);

const assignNeedsRiskConfirm = computed<boolean>(
    () =>
        assignIncludesAdmin.value ||
        assignSummary.value.removed.some(
            (roleName) => roleName.toLowerCase() === 'admin',
        ),
);

const isChecked = (name: string, list: string[]): boolean =>
    list.includes(name);

const parsePermissionCollection = (value: unknown): PermissionResource[] => {
    if (!value) {
        return [];
    }

    if (Array.isArray(value)) {
        return value.filter(
            (item): item is PermissionResource =>
                Boolean(item) &&
                typeof item === 'object' &&
                typeof (item as PermissionResource).id === 'number' &&
                typeof (item as PermissionResource).name === 'string' &&
                typeof (item as PermissionResource).guard_name === 'string',
        );
    }

    if (typeof value === 'object' && value !== null && 'data' in value) {
        return parsePermissionCollection((value as { data?: unknown }).data);
    }

    return [];
};

const parseRoleCollection = (value: unknown): RoleResource[] => {
    if (!Array.isArray(value)) {
        return [];
    }

    return value
        .filter(
            (
                item,
            ): item is Omit<RoleResource, 'permissions'> & {
                permissions?: unknown;
            } =>
                Boolean(item) &&
                typeof item === 'object' &&
                typeof (item as RoleResource).id === 'number' &&
                typeof (item as RoleResource).name === 'string' &&
                typeof (item as RoleResource).guard_name === 'string',
        )
        .map((role) => ({
            id: role.id,
            name: role.name,
            guard_name: role.guard_name,
            permissions: parsePermissionCollection(role.permissions),
        }));
};

const parseUserResource = (value: unknown): UserSecurityResource | null => {
    if (!value || typeof value !== 'object') {
        return null;
    }

    const data = 'data' in value ? (value as { data?: unknown }).data : value;
    if (!data || typeof data !== 'object') {
        return null;
    }

    const user = data as {
        id?: unknown;
        name?: unknown;
        email?: unknown;
        roles?: unknown;
    };

    if (
        typeof user.id !== 'number' ||
        typeof user.name !== 'string' ||
        typeof user.email !== 'string'
    ) {
        return null;
    }

    return {
        id: user.id,
        name: user.name,
        email: user.email,
        roles: parseRoleCollection(
            Array.isArray(user.roles)
                ? user.roles
                : (user.roles as { data?: unknown } | undefined)?.data,
        ),
    };
};

const readCookie = (name: string): string | null => {
    const encodedName = `${encodeURIComponent(name)}=`;
    const cookies = document.cookie.split(';');
    const cookie = cookies.find((part) => part.trim().startsWith(encodedName));
    if (!cookie) {
        return null;
    }

    return decodeURIComponent(cookie.trim().slice(encodedName.length));
};

const requestJson = async <T,>(
    endpoint: string,
    options: {
        method?: 'GET' | 'POST' | 'PUT';
        body?: Record<string, unknown>;
    } = {},
): Promise<T> => {
    const token = readCookie('XSRF-TOKEN');
    const response = await fetch(endpoint, {
        method: options.method ?? 'GET',
        credentials: 'same-origin',
        headers: {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            ...(token ? { 'X-XSRF-TOKEN': token } : {}),
        },
        body: options.body ? JSON.stringify(options.body) : undefined,
    });

    const payload = (await response.json().catch(() => ({}))) as {
        message?: string;
        errors?: Record<string, string[]>;
    };

    if (!response.ok) {
        const firstError = payload.errors
            ? Object.values(payload.errors)[0]?.[0]
            : null;

        throw new Error(firstError ?? payload.message ?? 'Request failed');
    }

    return payload as T;
};

const syncRolePermissionSelection = (): void => {
    rolePermissionNames.value = selectedRole.value
        ? selectedRole.value.permissions.map((permission) => permission.name)
        : [];
};

const loadRoleData = async (): Promise<void> => {
    if (!abilities.value.canViewRoles) {
        return;
    }

    loading.value = true;
    pageError.value = null;

    try {
        const payload = await requestJson<{
            data?: unknown;
            permissions?: unknown;
        }>(rolesIndexRoute.url());

        roles.value = parseRoleCollection(payload.data ?? []);
        permissions.value = parsePermissionCollection(payload.permissions);

        if (
            selectedRoleId.value === null ||
            !roles.value.some((role) => role.id === selectedRoleId.value)
        ) {
            selectedRoleId.value = roles.value[0]?.id ?? null;
        }

        syncRolePermissionSelection();
        syncRiskConfirmed.value = false;
    } catch (error) {
        pageError.value =
            error instanceof Error
                ? error.message
                : 'Unable to load RBAC data.';
    } finally {
        loading.value = false;
    }
};

const toggleArrayItem = (
    list: string[],
    item: string,
    checked: boolean,
): string[] => {
    if (checked) {
        return list.includes(item) ? list : [...list, item];
    }

    return list.filter((value) => value !== item);
};

const applyPresetToSelection = (
    baseList: string[],
    group: PermissionGroup,
    preset: PermissionPreset,
    checked: boolean,
): string[] => {
    const scopedNames = group.permissions
        .filter((permission) => matchesPreset(permission.name, preset))
        .map((permission) => permission.name);

    const set = new Set(baseList);
    if (checked) {
        scopedNames.forEach((name) => set.add(name));
    } else {
        scopedNames.forEach((name) => set.delete(name));
    }

    return Array.from(set).sort((left, right) => left.localeCompare(right));
};

const applyCreatePreset = (
    group: PermissionGroup,
    preset: PermissionPreset,
    checked: boolean,
): void => {
    createRolePermissionNames.value = applyPresetToSelection(
        createRolePermissionNames.value,
        group,
        preset,
        checked,
    );
};

const applySyncPreset = (
    group: PermissionGroup,
    preset: PermissionPreset,
    checked: boolean,
): void => {
    rolePermissionNames.value = applyPresetToSelection(
        rolePermissionNames.value,
        group,
        preset,
        checked,
    );
};

const createRole = async (): Promise<void> => {
    if (!abilities.value.canCreateRoles || createRoleName.value.trim() === '') {
        return;
    }

    submittingCreateRole.value = true;
    pageError.value = null;
    pageSuccess.value = null;

    try {
        await requestJson(RoleController.store.url(), {
            method: 'POST',
            body: {
                name: createRoleName.value.trim(),
                permissions: createRolePermissionNames.value,
            },
        });

        createRoleName.value = '';
        createRolePermissionNames.value = [];
        pageSuccess.value = 'Role created successfully.';
        await loadRoleData();
    } catch (error) {
        pageError.value =
            error instanceof Error ? error.message : 'Unable to create role.';
    } finally {
        submittingCreateRole.value = false;
    }
};

const syncRolePermissions = async (): Promise<void> => {
    if (
        !abilities.value.canManageRolePermissions ||
        selectedRoleId.value === null
    ) {
        return;
    }

    if (!syncHasChanges.value) {
        pageError.value = 'No permission changes to sync.';
        return;
    }

    if (syncNeedsRiskConfirm.value && !syncRiskConfirmed.value) {
        pageError.value =
            'Confirm permission removals before syncing this role.';
        return;
    }

    submittingSyncPermissions.value = true;
    pageError.value = null;
    pageSuccess.value = null;

    try {
        await requestJson(
            RolePermissionController.update.url({ role: selectedRoleId.value }),
            {
                method: 'PUT',
                body: {
                    permissions: rolePermissionNames.value,
                },
            },
        );

        pageSuccess.value = 'Role permissions updated.';
        syncRiskConfirmed.value = false;
        await loadRoleData();
    } catch (error) {
        pageError.value =
            error instanceof Error
                ? error.message
                : 'Unable to sync role permissions.';
    } finally {
        submittingSyncPermissions.value = false;
    }
};

const assignRolesToUser = async (): Promise<void> => {
    if (
        !abilities.value.canAssignUserRoles ||
        assignUserId.value.trim() === ''
    ) {
        return;
    }

    if (assignNeedsRiskConfirm.value && !assignRiskConfirmed.value) {
        pageError.value = 'Confirm high-privilege role assignment first.';
        return;
    }

    submittingAssignUserRoles.value = true;
    pageError.value = null;
    pageSuccess.value = null;

    try {
        const userId = Number.parseInt(assignUserId.value, 10);
        if (Number.isNaN(userId) || userId < 1) {
            throw new Error('User ID must be a positive integer.');
        }

        const payload = await requestJson(
            UserRoleController.update.url({ user: userId }),
            {
                method: 'PUT',
                body: {
                    roles: assignUserRoleNames.value,
                },
            },
        );

        assignedUser.value = parseUserResource(payload);
        pageSuccess.value = 'User roles updated.';
        assignRiskConfirmed.value = false;
    } catch (error) {
        pageError.value =
            error instanceof Error
                ? error.message
                : 'Unable to assign user roles.';
    } finally {
        submittingAssignUserRoles.value = false;
    }
};

watch(selectedRoleId, () => {
    syncRolePermissionSelection();
    syncRiskConfirmed.value = false;
});

watch(rolePermissionNames, () => {
    syncRiskConfirmed.value = false;
});

watch([assignUserId, assignUserRoleNames], () => {
    assignRiskConfirmed.value = false;
});

onMounted(async () => {
    if (roles.value.length > 0 && permissions.value.length > 0) {
        syncRolePermissionSelection();
        return;
    }

    await loadRoleData();
});
</script>

<template>
    <Head title="Security roles" />

    <AdminLayout :breadcrumbs="breadcrumbItems">
        <div class="space-y-4 p-4">
            <section class="tm-shell p-6">
                <p class="tm-kicker text-primary">Security Administration</p>
                <h2 class="tm-display text-foreground mt-2 text-3xl font-black">
                    Role and permission control
                </h2>
                <p class="text-muted-foreground mt-2 max-w-2xl text-sm">
                    Manage role bundles, permission synchronization, and user
                    assignments.
                </p>
            </section>

            <Alert v-if="pageError" variant="destructive">
                <AlertTitle>Request failed</AlertTitle>
                <AlertDescription>{{ pageError }}</AlertDescription>
            </Alert>

            <Alert v-if="pageSuccess">
                <AlertTitle>Saved</AlertTitle>
                <AlertDescription>{{ pageSuccess }}</AlertDescription>
            </Alert>

            <section class="tm-admin-toolbar space-y-3">
                <div
                    class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between"
                    role="region"
                    aria-label="Role actions toolbar"
                >
                    <div>
                        <p class="text-foreground text-sm font-semibold">
                            Roles and access controls
                        </p>
                        <p class="text-muted-foreground text-xs">
                            Use grouped permission views and change summaries to
                            avoid accidental access drift.
                        </p>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <Input
                            v-model="roleSearch"
                            class="tm-input-surface w-full min-w-52 lg:w-64"
                            placeholder="Search role name or guard..."
                        />
                        <Button
                            variant="outline"
                            :disabled="loading || !abilities.canViewRoles"
                            @click="loadRoleData"
                        >
                            <Spinner v-if="loading" />
                            Refresh
                        </Button>
                    </div>
                </div>
                <div class="tm-card p-3">
                    <p class="tm-form-hint">Active role context</p>
                    <div
                        class="mt-2 flex flex-col gap-2 text-sm lg:flex-row lg:items-center lg:justify-between"
                    >
                        <p class="text-foreground font-semibold">
                            {{
                                selectedRole
                                    ? `${selectedRole.name} (${selectedRole.guard_name})`
                                    : 'No role selected'
                            }}
                        </p>
                        <div class="flex flex-wrap items-center gap-2">
                            <Badge v-if="selectedRole" variant="secondary">
                                {{ selectedRole.permissions.length }}
                                permissions
                            </Badge>
                            <Badge v-if="selectedRole" variant="secondary">
                                {{ roleDomainCount(selectedRole) }} domains
                            </Badge>
                        </div>
                    </div>
                </div>
            </section>

            <div class="grid gap-4 lg:grid-cols-2">
                <Card class="tm-panel h-full">
                    <CardHeader>
                        <CardTitle>Role catalog</CardTitle>
                        <CardDescription>
                            Review role bundles before syncing permission
                            changes.
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-3">
                        <div
                            v-if="loading"
                            class="tm-state-note tm-state-note-warning flex items-center gap-2"
                            role="status"
                            aria-live="polite"
                        >
                            <Spinner />
                            Loading roles...
                        </div>

                        <p
                            v-else-if="!abilities.canViewRoles"
                            class="text-muted-foreground text-sm"
                        >
                            You do not have access to view roles.
                        </p>

                        <p
                            v-else-if="roles.length === 0"
                            class="tm-empty-state"
                        >
                            No roles available yet.
                        </p>

                        <p
                            v-else-if="visibleRoles.length === 0"
                            class="tm-empty-state"
                        >
                            No roles match your search.
                        </p>

                        <div v-else class="tm-table-wrap tm-table-roomy">
                            <table class="tm-table">
                                <thead>
                                    <tr>
                                        <th scope="col" class="tm-th">Role</th>
                                        <th scope="col" class="tm-th">Guard</th>
                                        <th scope="col" class="tm-th">
                                            Permissions
                                        </th>
                                        <th scope="col" class="tm-th">
                                            Domains
                                        </th>
                                        <th
                                            scope="col"
                                            class="tm-th text-right"
                                        >
                                            Context
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        v-for="role in visibleRoles"
                                        :key="role.id"
                                        class="tm-tr"
                                        :class="{
                                            'bg-primary/5':
                                                selectedRoleId === role.id,
                                        }"
                                        :aria-selected="
                                            selectedRoleId === role.id
                                        "
                                    >
                                        <td class="tm-td">
                                            <p class="font-medium">
                                                {{ role.name }}
                                            </p>
                                        </td>
                                        <td class="tm-td">
                                            {{ role.guard_name }}
                                        </td>
                                        <td class="tm-td">
                                            <Badge variant="secondary">
                                                {{ role.permissions.length }}
                                                permissions
                                            </Badge>
                                        </td>
                                        <td class="tm-td">
                                            <Badge variant="secondary">
                                                {{ roleDomainCount(role) }}
                                                groups
                                            </Badge>
                                        </td>
                                        <td class="tm-td text-right">
                                            <Button
                                                size="sm"
                                                :aria-label="`Select ${role.name} role`"
                                                :variant="
                                                    selectedRoleId === role.id
                                                        ? 'default'
                                                        : 'outline'
                                                "
                                                @click="
                                                    selectedRoleId = role.id
                                                "
                                            >
                                                {{
                                                    selectedRoleId === role.id
                                                        ? 'In context'
                                                        : 'Set context'
                                                }}
                                            </Button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </CardContent>
                    <CardFooter class="tm-sticky-actions">
                        <Button
                            variant="outline"
                            :disabled="loading || !abilities.canViewRoles"
                            @click="loadRoleData"
                        >
                            Refresh
                        </Button>
                    </CardFooter>
                </Card>

                <Card class="tm-panel h-full">
                    <CardHeader>
                        <CardTitle>Create role</CardTitle>
                        <CardDescription>
                            Add a new role with an optional initial permission
                            set.
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <p class="tm-form-hint">
                            Role keys should be stable and descriptive for
                            permission grouping.
                        </p>
                        <div class="tm-form-field">
                            <Label for="role-name" class="tm-label"
                                >Role name</Label
                            >
                            <Input
                                id="role-name"
                                v-model="createRoleName"
                                class="tm-input-surface"
                                placeholder="seller"
                                :disabled="
                                    !abilities.canCreateRoles ||
                                    submittingCreateRole
                                "
                            />
                        </div>

                        <div class="space-y-2">
                            <Label>Initial permissions</Label>
                            <div
                                class="tm-card max-h-72 space-y-3 overflow-y-auto p-3"
                            >
                                <p
                                    v-if="permissionGroups.length === 0"
                                    class="tm-empty-state"
                                >
                                    No permissions available.
                                </p>

                                <section
                                    v-for="group in permissionGroups"
                                    :key="group.key"
                                    class="tm-permission-group"
                                >
                                    <div
                                        class="flex flex-wrap items-center justify-between gap-2"
                                    >
                                        <p class="text-sm font-semibold">
                                            {{ group.label }}
                                        </p>
                                        <div class="flex flex-wrap gap-1">
                                            <button
                                                type="button"
                                                class="tm-mini-chip"
                                                :disabled="
                                                    !abilities.canCreateRoles ||
                                                    submittingCreateRole
                                                "
                                                @click="
                                                    applyCreatePreset(
                                                        group,
                                                        'all',
                                                        true,
                                                    )
                                                "
                                            >
                                                All
                                            </button>
                                            <button
                                                type="button"
                                                class="tm-mini-chip"
                                                :disabled="
                                                    !abilities.canCreateRoles ||
                                                    submittingCreateRole
                                                "
                                                @click="
                                                    applyCreatePreset(
                                                        group,
                                                        'read',
                                                        true,
                                                    )
                                                "
                                            >
                                                Read
                                            </button>
                                            <button
                                                type="button"
                                                class="tm-mini-chip"
                                                :disabled="
                                                    !abilities.canCreateRoles ||
                                                    submittingCreateRole
                                                "
                                                @click="
                                                    applyCreatePreset(
                                                        group,
                                                        'write',
                                                        true,
                                                    )
                                                "
                                            >
                                                Write
                                            </button>
                                            <button
                                                type="button"
                                                class="tm-mini-chip"
                                                :disabled="
                                                    !abilities.canCreateRoles ||
                                                    submittingCreateRole
                                                "
                                                @click="
                                                    applyCreatePreset(
                                                        group,
                                                        'manage',
                                                        true,
                                                    )
                                                "
                                            >
                                                Manage
                                            </button>
                                            <button
                                                type="button"
                                                class="tm-mini-chip"
                                                :disabled="
                                                    !abilities.canCreateRoles ||
                                                    submittingCreateRole
                                                "
                                                @click="
                                                    applyCreatePreset(
                                                        group,
                                                        'all',
                                                        false,
                                                    )
                                                "
                                            >
                                                Clear
                                            </button>
                                        </div>
                                    </div>

                                    <div class="mt-2 space-y-2">
                                        <Label
                                            v-for="permission in group.permissions"
                                            :key="permission.id"
                                            class="flex items-center gap-3"
                                        >
                                            <Checkbox
                                                :model-value="
                                                    isChecked(
                                                        permission.name,
                                                        createRolePermissionNames,
                                                    )
                                                "
                                                :disabled="
                                                    !abilities.canCreateRoles ||
                                                    submittingCreateRole
                                                "
                                                @update:model-value="
                                                    (value) =>
                                                        (createRolePermissionNames =
                                                            toggleArrayItem(
                                                                createRolePermissionNames,
                                                                permission.name,
                                                                value === true,
                                                            ))
                                                "
                                            />
                                            <span class="text-sm">{{
                                                permission.name
                                            }}</span>
                                        </Label>
                                    </div>
                                </section>
                            </div>
                        </div>
                    </CardContent>
                    <CardFooter class="tm-sticky-actions">
                        <Button
                            :disabled="
                                !abilities.canCreateRoles ||
                                submittingCreateRole ||
                                createRoleName.trim() === ''
                            "
                            @click="createRole"
                        >
                            <Spinner v-if="submittingCreateRole" />
                            Create role
                        </Button>
                    </CardFooter>
                </Card>
            </div>

            <div class="grid gap-4 lg:grid-cols-2">
                <Card class="tm-panel h-full">
                    <CardHeader>
                        <CardTitle>Sync role permissions</CardTitle>
                        <CardDescription>
                            Grouped permission matrix with staged sync summary.
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <p class="tm-form-hint">
                            Select a role context first, apply grouped presets,
                            then review additions/removals before sync.
                        </p>
                        <div class="tm-form-field">
                            <Label for="selected-role" class="tm-label"
                                >Selected role</Label
                            >
                            <Input
                                id="selected-role"
                                class="tm-input-surface"
                                :model-value="
                                    selectedRole?.name ?? 'No role selected'
                                "
                                disabled
                            />
                        </div>

                        <div class="tm-card p-3">
                            <p class="text-sm font-semibold">
                                Pending sync summary
                            </p>
                            <div class="mt-2 grid gap-2 sm:grid-cols-2">
                                <div
                                    class="tm-state-note tm-state-note-success"
                                >
                                    Additions:
                                    <strong>{{
                                        syncSummary.added.length
                                    }}</strong>
                                </div>
                                <div
                                    class="tm-state-note tm-state-note-warning"
                                >
                                    Removals:
                                    <strong>{{
                                        syncSummary.removed.length
                                    }}</strong>
                                </div>
                            </div>
                            <div
                                v-if="syncSummary.added.length > 0"
                                class="mt-2 flex flex-wrap gap-1"
                            >
                                <span
                                    v-for="name in syncSummary.added.slice(
                                        0,
                                        8,
                                    )"
                                    :key="`add-${name}`"
                                    class="tm-mini-chip tm-mini-chip-success"
                                >
                                    + {{ name }}
                                </span>
                            </div>
                            <div
                                v-if="syncSummary.removed.length > 0"
                                class="mt-2 flex flex-wrap gap-1"
                            >
                                <span
                                    v-for="name in syncSummary.removed.slice(
                                        0,
                                        8,
                                    )"
                                    :key="`remove-${name}`"
                                    class="tm-mini-chip tm-mini-chip-warning"
                                >
                                    - {{ name }}
                                </span>
                            </div>
                            <Label
                                v-if="syncNeedsRiskConfirm"
                                class="mt-3 flex items-center gap-2 text-sm"
                            >
                                <input
                                    v-model="syncRiskConfirmed"
                                    type="checkbox"
                                    class="h-4 w-4 rounded border-zinc-300"
                                />
                                I confirm the permission removals above.
                            </Label>
                        </div>

                        <div class="space-y-2">
                            <Label>Permissions by domain</Label>
                            <div
                                class="tm-card max-h-80 space-y-3 overflow-y-auto p-3"
                            >
                                <p
                                    v-if="permissionGroups.length === 0"
                                    class="tm-empty-state"
                                >
                                    No permissions available.
                                </p>

                                <section
                                    v-for="group in permissionGroups"
                                    :key="group.key"
                                    class="tm-permission-group"
                                >
                                    <div
                                        class="flex flex-wrap items-center justify-between gap-2"
                                    >
                                        <p class="text-sm font-semibold">
                                            {{ group.label }}
                                        </p>
                                        <div class="flex flex-wrap gap-1">
                                            <button
                                                type="button"
                                                class="tm-mini-chip"
                                                :disabled="
                                                    !abilities.canManageRolePermissions ||
                                                    submittingSyncPermissions ||
                                                    selectedRoleId === null
                                                "
                                                @click="
                                                    applySyncPreset(
                                                        group,
                                                        'all',
                                                        true,
                                                    )
                                                "
                                            >
                                                All
                                            </button>
                                            <button
                                                type="button"
                                                class="tm-mini-chip"
                                                :disabled="
                                                    !abilities.canManageRolePermissions ||
                                                    submittingSyncPermissions ||
                                                    selectedRoleId === null
                                                "
                                                @click="
                                                    applySyncPreset(
                                                        group,
                                                        'read',
                                                        true,
                                                    )
                                                "
                                            >
                                                Read
                                            </button>
                                            <button
                                                type="button"
                                                class="tm-mini-chip"
                                                :disabled="
                                                    !abilities.canManageRolePermissions ||
                                                    submittingSyncPermissions ||
                                                    selectedRoleId === null
                                                "
                                                @click="
                                                    applySyncPreset(
                                                        group,
                                                        'write',
                                                        true,
                                                    )
                                                "
                                            >
                                                Write
                                            </button>
                                            <button
                                                type="button"
                                                class="tm-mini-chip"
                                                :disabled="
                                                    !abilities.canManageRolePermissions ||
                                                    submittingSyncPermissions ||
                                                    selectedRoleId === null
                                                "
                                                @click="
                                                    applySyncPreset(
                                                        group,
                                                        'manage',
                                                        true,
                                                    )
                                                "
                                            >
                                                Manage
                                            </button>
                                            <button
                                                type="button"
                                                class="tm-mini-chip"
                                                :disabled="
                                                    !abilities.canManageRolePermissions ||
                                                    submittingSyncPermissions ||
                                                    selectedRoleId === null
                                                "
                                                @click="
                                                    applySyncPreset(
                                                        group,
                                                        'all',
                                                        false,
                                                    )
                                                "
                                            >
                                                Clear
                                            </button>
                                        </div>
                                    </div>

                                    <div class="mt-2 space-y-2">
                                        <Label
                                            v-for="permission in group.permissions"
                                            :key="permission.id"
                                            class="flex items-center gap-3"
                                        >
                                            <Checkbox
                                                :model-value="
                                                    isChecked(
                                                        permission.name,
                                                        rolePermissionNames,
                                                    )
                                                "
                                                :disabled="
                                                    !abilities.canManageRolePermissions ||
                                                    submittingSyncPermissions ||
                                                    selectedRoleId === null
                                                "
                                                @update:model-value="
                                                    (value) =>
                                                        (rolePermissionNames =
                                                            toggleArrayItem(
                                                                rolePermissionNames,
                                                                permission.name,
                                                                value === true,
                                                            ))
                                                "
                                            />
                                            <span class="text-sm">{{
                                                permission.name
                                            }}</span>
                                        </Label>
                                    </div>
                                </section>
                            </div>
                        </div>
                    </CardContent>
                    <CardFooter class="tm-sticky-actions">
                        <Button
                            :disabled="
                                !abilities.canManageRolePermissions ||
                                submittingSyncPermissions ||
                                selectedRoleId === null ||
                                !syncHasChanges ||
                                (syncNeedsRiskConfirm && !syncRiskConfirmed)
                            "
                            @click="syncRolePermissions"
                        >
                            <Spinner v-if="submittingSyncPermissions" />
                            Sync permissions
                        </Button>
                    </CardFooter>
                </Card>

                <Card class="tm-panel h-full">
                    <CardHeader>
                        <CardTitle>Assign roles to user</CardTitle>
                        <CardDescription>
                            Assign role bundles with high-privilege safety cues.
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <p class="tm-form-hint">
                            Use user ID as target context, then review summary
                            before applying role changes.
                        </p>
                        <div class="tm-form-field">
                            <Label for="assign-user-id" class="tm-label"
                                >User ID</Label
                            >
                            <Input
                                id="assign-user-id"
                                v-model="assignUserId"
                                class="tm-input-surface"
                                inputmode="numeric"
                                placeholder="1"
                                :disabled="
                                    !abilities.canAssignUserRoles ||
                                    submittingAssignUserRoles
                                "
                            />
                        </div>

                        <div class="tm-card p-3">
                            <p class="text-sm font-semibold">
                                Assignment summary
                            </p>
                            <div class="mt-2 grid gap-2 sm:grid-cols-2">
                                <div
                                    class="tm-state-note tm-state-note-success"
                                >
                                    Additions:
                                    <strong>{{
                                        assignSummary.added.length
                                    }}</strong>
                                </div>
                                <div
                                    class="tm-state-note tm-state-note-warning"
                                >
                                    Removals:
                                    <strong>{{
                                        assignSummary.removed.length
                                    }}</strong>
                                </div>
                            </div>
                            <p
                                v-if="knownAssignedRoleNames.length === 0"
                                class="tm-form-hint mt-2"
                            >
                                Baseline roles are unavailable until this target
                                user has been updated in this session.
                            </p>
                            <p
                                v-if="assignIncludesAdmin"
                                class="tm-state-note tm-state-note-warning mt-2"
                            >
                                High privilege detected: <strong>admin</strong>
                                role is included in staged assignment.
                            </p>
                            <Label
                                v-if="assignNeedsRiskConfirm"
                                class="mt-3 flex items-center gap-2 text-sm"
                            >
                                <input
                                    v-model="assignRiskConfirmed"
                                    type="checkbox"
                                    class="h-4 w-4 rounded border-zinc-300"
                                />
                                I confirm high-privilege role assignment.
                            </Label>
                        </div>

                        <div class="space-y-2">
                            <Label>Roles</Label>
                            <div
                                class="tm-card max-h-56 space-y-2 overflow-y-auto p-3"
                            >
                                <p
                                    v-if="availableRoleNames.length === 0"
                                    class="tm-empty-state"
                                >
                                    No roles available for assignment.
                                </p>

                                <Label
                                    v-for="roleName in availableRoleNames"
                                    :key="roleName"
                                    class="flex items-center gap-3"
                                >
                                    <Checkbox
                                        :model-value="
                                            isChecked(
                                                roleName,
                                                assignUserRoleNames,
                                            )
                                        "
                                        :disabled="
                                            !abilities.canAssignUserRoles ||
                                            submittingAssignUserRoles
                                        "
                                        @update:model-value="
                                            (value) =>
                                                (assignUserRoleNames =
                                                    toggleArrayItem(
                                                        assignUserRoleNames,
                                                        roleName,
                                                        value === true,
                                                    ))
                                        "
                                    />
                                    <span class="text-sm">{{ roleName }}</span>
                                </Label>
                            </div>
                        </div>

                        <div
                            v-if="assignedUser"
                            class="tm-state-note tm-state-note-success"
                        >
                            Updated user:
                            <span class="text-foreground font-medium">
                                {{ assignedUser.name }}
                            </span>
                            ({{ assignedUser.email }}) with
                            <span class="text-foreground font-medium">
                                {{ assignedUser.roles.length }}
                            </span>
                            role(s).
                        </div>
                    </CardContent>
                    <CardFooter class="tm-sticky-actions">
                        <Button
                            :disabled="
                                !abilities.canAssignUserRoles ||
                                submittingAssignUserRoles ||
                                assignUserId.trim() === '' ||
                                (assignNeedsRiskConfirm && !assignRiskConfirmed)
                            "
                            @click="assignRolesToUser"
                        >
                            <Spinner v-if="submittingAssignUserRoles" />
                            Assign roles
                        </Button>
                    </CardFooter>
                </Card>
            </div>
        </div>
    </AdminLayout>
</template>
