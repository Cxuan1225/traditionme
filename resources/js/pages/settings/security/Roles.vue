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
import AppLayout from '@/layouts/AppLayout.vue';
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
const pageError = ref<string | null>(null);
const pageSuccess = ref<string | null>(null);
const selectedRoleId = ref<number | null>(roles.value[0]?.id ?? null);

const createRoleName = ref<string>('');
const createRolePermissionNames = ref<string[]>([]);
const rolePermissionNames = ref<string[]>([]);
const assignUserId = ref<string>('');
const assignUserRoleNames = ref<string[]>([]);
const assignedUser = ref<UserSecurityResource | null>(null);

const abilities = computed<Required<Capabilities>>(() => ({
    canViewRoles: props.capabilities?.canViewRoles ?? true,
    canCreateRoles: props.capabilities?.canCreateRoles ?? true,
    canManageRolePermissions: props.capabilities?.canManageRolePermissions ?? true,
    canAssignUserRoles: props.capabilities?.canAssignUserRoles ?? true,
}));

const selectedRole = computed<RoleResource | null>(() =>
    roles.value.find((role) => role.id === selectedRoleId.value) ?? null,
);

const availableRoleNames = computed<string[]>(() =>
    roles.value.map((role) => role.name),
);

const isChecked = (name: string, list: string[]): boolean => list.includes(name);

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
            (item): item is Omit<RoleResource, 'permissions'> & { permissions?: unknown } =>
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

const requestJson = async <T>(
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
    } catch (error) {
        pageError.value =
            error instanceof Error ? error.message : 'Unable to load RBAC data.';
    } finally {
        loading.value = false;
    }
};

const toggleArrayItem = (list: string[], item: string, checked: boolean): string[] => {
    if (checked) {
        return list.includes(item) ? list : [...list, item];
    }

    return list.filter((value) => value !== item);
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
    if (!abilities.value.canManageRolePermissions || selectedRoleId.value === null) {
        return;
    }

    submittingSyncPermissions.value = true;
    pageError.value = null;
    pageSuccess.value = null;

    try {
        await requestJson(RolePermissionController.update.url({ role: selectedRoleId.value }), {
            method: 'PUT',
            body: {
                permissions: rolePermissionNames.value,
            },
        });

        pageSuccess.value = 'Role permissions updated.';
        await loadRoleData();
    } catch (error) {
        pageError.value =
            error instanceof Error ? error.message : 'Unable to sync role permissions.';
    } finally {
        submittingSyncPermissions.value = false;
    }
};

const assignRolesToUser = async (): Promise<void> => {
    if (!abilities.value.canAssignUserRoles || assignUserId.value.trim() === '') {
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

        const payload = await requestJson(UserRoleController.update.url({ user: userId }), {
            method: 'PUT',
            body: {
                roles: assignUserRoleNames.value,
            },
        });

        assignedUser.value = parseUserResource(payload);
        pageSuccess.value = 'User roles updated.';
    } catch (error) {
        pageError.value =
            error instanceof Error ? error.message : 'Unable to assign user roles.';
    } finally {
        submittingAssignUserRoles.value = false;
    }
};

watch(selectedRoleId, () => {
    syncRolePermissionSelection();
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

    <AppLayout :breadcrumbs="breadcrumbItems">
        <div class="flex flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <Alert v-if="pageError" variant="destructive">
                <AlertTitle>Request failed</AlertTitle>
                <AlertDescription>{{ pageError }}</AlertDescription>
            </Alert>

            <Alert v-if="pageSuccess">
                <AlertTitle>Saved</AlertTitle>
                <AlertDescription>{{ pageSuccess }}</AlertDescription>
            </Alert>

            <div class="grid gap-4 lg:grid-cols-2">
                <Card class="h-full">
                    <CardHeader>
                        <CardTitle>Role catalog</CardTitle>
                        <CardDescription>
                            View roles and current permission bundles.
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-3">
                        <div v-if="loading" class="flex items-center gap-2 text-sm">
                            <Spinner />
                            Loading roles...
                        </div>

                        <p
                            v-else-if="!abilities.canViewRoles"
                            class="text-sm text-muted-foreground"
                        >
                            You do not have access to view roles.
                        </p>

                        <p
                            v-else-if="roles.length === 0"
                            class="text-sm text-muted-foreground"
                        >
                            No roles available yet.
                        </p>

                        <div v-else class="space-y-3">
                            <button
                                v-for="role in roles"
                                :key="role.id"
                                type="button"
                                class="w-full rounded-lg border p-3 text-left transition hover:bg-muted/50"
                                :class="{ 'border-primary bg-muted/70': selectedRoleId === role.id }"
                                @click="selectedRoleId = role.id"
                            >
                                <div class="flex flex-wrap items-center justify-between gap-2">
                                    <p class="font-medium">{{ role.name }}</p>
                                    <Badge variant="secondary">
                                        {{ role.permissions.length }} permissions
                                    </Badge>
                                </div>
                                <p class="mt-1 text-xs text-muted-foreground">
                                    Guard: {{ role.guard_name }}
                                </p>
                            </button>
                        </div>
                    </CardContent>
                    <CardFooter>
                        <Button
                            variant="outline"
                            :disabled="loading || !abilities.canViewRoles"
                            @click="loadRoleData"
                        >
                            Refresh
                        </Button>
                    </CardFooter>
                </Card>

                <Card class="h-full">
                    <CardHeader>
                        <CardTitle>Create role</CardTitle>
                        <CardDescription>
                            Add a new role with an optional initial permission set.
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="grid gap-2">
                            <Label for="role-name">Role name</Label>
                            <Input
                                id="role-name"
                                v-model="createRoleName"
                                placeholder="seller"
                                :disabled="!abilities.canCreateRoles || submittingCreateRole"
                            />
                        </div>

                        <div class="space-y-2">
                            <Label>Initial permissions</Label>
                            <div
                                class="max-h-56 space-y-2 overflow-y-auto rounded-md border p-3"
                            >
                                <p
                                    v-if="permissions.length === 0"
                                    class="text-sm text-muted-foreground"
                                >
                                    No permissions available.
                                </p>

                                <Label
                                    v-for="permission in permissions"
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
                                    <span class="text-sm">{{ permission.name }}</span>
                                </Label>
                            </div>
                        </div>
                    </CardContent>
                    <CardFooter>
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
                <Card class="h-full">
                    <CardHeader>
                        <CardTitle>Sync role permissions</CardTitle>
                        <CardDescription>
                            Update the permission bundle for the selected role.
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="grid gap-2">
                            <Label for="selected-role">Selected role</Label>
                            <Input
                                id="selected-role"
                                :model-value="selectedRole?.name ?? 'No role selected'"
                                disabled
                            />
                        </div>

                        <div class="space-y-2">
                            <Label>Permissions</Label>
                            <div
                                class="max-h-56 space-y-2 overflow-y-auto rounded-md border p-3"
                            >
                                <p
                                    v-if="permissions.length === 0"
                                    class="text-sm text-muted-foreground"
                                >
                                    No permissions available.
                                </p>

                                <Label
                                    v-for="permission in permissions"
                                    :key="permission.id"
                                    class="flex items-center gap-3"
                                >
                                    <Checkbox
                                        :model-value="
                                            isChecked(permission.name, rolePermissionNames)
                                        "
                                        :disabled="
                                            !abilities.canManageRolePermissions ||
                                            submittingSyncPermissions ||
                                            selectedRoleId === null
                                        "
                                        @update:model-value="
                                            (value) =>
                                                (rolePermissionNames = toggleArrayItem(
                                                    rolePermissionNames,
                                                    permission.name,
                                                    value === true,
                                                ))
                                        "
                                    />
                                    <span class="text-sm">{{ permission.name }}</span>
                                </Label>
                            </div>
                        </div>
                    </CardContent>
                    <CardFooter>
                        <Button
                            :disabled="
                                !abilities.canManageRolePermissions ||
                                submittingSyncPermissions ||
                                selectedRoleId === null
                            "
                            @click="syncRolePermissions"
                        >
                            <Spinner v-if="submittingSyncPermissions" />
                            Sync permissions
                        </Button>
                    </CardFooter>
                </Card>

                <Card class="h-full">
                    <CardHeader>
                        <CardTitle>Assign roles to user</CardTitle>
                        <CardDescription>
                            Assign one or more roles to a user by user ID.
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="grid gap-2">
                            <Label for="assign-user-id">User ID</Label>
                            <Input
                                id="assign-user-id"
                                v-model="assignUserId"
                                inputmode="numeric"
                                placeholder="1"
                                :disabled="
                                    !abilities.canAssignUserRoles ||
                                    submittingAssignUserRoles
                                "
                            />
                        </div>

                        <div class="space-y-2">
                            <Label>Roles</Label>
                            <div
                                class="max-h-56 space-y-2 overflow-y-auto rounded-md border p-3"
                            >
                                <p
                                    v-if="availableRoleNames.length === 0"
                                    class="text-sm text-muted-foreground"
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
                                            isChecked(roleName, assignUserRoleNames)
                                        "
                                        :disabled="
                                            !abilities.canAssignUserRoles ||
                                            submittingAssignUserRoles
                                        "
                                        @update:model-value="
                                            (value) =>
                                                (assignUserRoleNames = toggleArrayItem(
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
                            class="rounded-md border p-3 text-sm text-muted-foreground"
                        >
                            Updated user:
                            <span class="font-medium text-foreground">
                                {{ assignedUser.name }}
                            </span>
                            ({{ assignedUser.email }}) with
                            <span class="font-medium text-foreground">
                                {{ assignedUser.roles.length }}
                            </span>
                            role(s).
                        </div>
                    </CardContent>
                    <CardFooter>
                        <Button
                            :disabled="
                                !abilities.canAssignUserRoles ||
                                submittingAssignUserRoles ||
                                assignUserId.trim() === ''
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
    </AppLayout>
</template>
