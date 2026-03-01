<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { Boxes, ClipboardCheck, Shield, Sparkles, Users } from 'lucide-vue-next';
import { computed } from 'vue';
import { Badge } from '@/components/ui/badge';
import {
    Card,
    CardAction,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import AdminLayout from '@/layouts/admin/Layout.vue';
import { dashboard } from '@/routes';
import { index as productsIndex } from '@/routes/products';
import { edit as editProfile } from '@/routes/profile';
import { index as rolesIndex } from '@/routes/security/roles';
import type { Auth, BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
    },
];

const page = usePage<{ auth?: Auth }>();
const permissions = computed(() => page.props.auth?.permissions ?? []);
const userName = computed(() => page.props.auth?.user?.name ?? 'Admin');

const canManageProducts = computed(() =>
    ['products.view', 'products.create', 'products.update', 'products.delete'].some((permission) =>
        permissions.value.includes(permission),
    ),
);

const canManageRoles = computed(() =>
    ['roles.view', 'roles.create', 'roles.manage_permissions', 'users.assign_roles'].some((permission) =>
        permissions.value.includes(permission),
    ),
);

const permissionCount = computed(() => permissions.value.length);
</script>

<template>
    <Head title="Dashboard" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-5">
            <section class="rounded-3xl border border-border bg-gradient-to-r from-amber-100/70 via-card to-rose-100/50 p-6 dark:from-amber-950/35 dark:via-card dark:to-rose-950/20">
                <p class="text-xs font-semibold tracking-[0.2em] text-amber-700 uppercase dark:text-amber-300">Admin dashboard</p>
                <h2 class="mt-2 text-3xl font-black text-foreground">Welcome, {{ userName }}</h2>
                <p class="mt-2 max-w-2xl text-sm text-muted-foreground">
                    Manage store products, role permissions, and account security in a dedicated administration workspace.
                </p>
            </section>

            <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <Card class="border-border bg-card/80">
                    <CardHeader>
                        <CardDescription>Granted permissions</CardDescription>
                        <CardTitle class="text-3xl">{{ permissionCount }}</CardTitle>
                    </CardHeader>
                    <CardFooter class="text-xs text-muted-foreground">
                        Access inherited from your assigned role.
                    </CardFooter>
                </Card>

                <Card class="border-border bg-card/80">
                    <CardHeader>
                        <CardDescription>Product capability</CardDescription>
                        <CardTitle>
                            {{ canManageProducts ? 'Enabled' : 'Limited' }}
                        </CardTitle>
                        <CardAction>
                            <Boxes class="size-5 text-amber-700 dark:text-amber-300" />
                        </CardAction>
                    </CardHeader>
                </Card>

                <Card class="border-border bg-card/80">
                    <CardHeader>
                        <CardDescription>Security role management</CardDescription>
                        <CardTitle>
                            {{ canManageRoles ? 'Enabled' : 'Limited' }}
                        </CardTitle>
                        <CardAction>
                            <Users class="size-5 text-amber-700 dark:text-amber-300" />
                        </CardAction>
                    </CardHeader>
                </Card>

                <Card class="border-border bg-card/80">
                    <CardHeader>
                        <CardDescription>Admin mode status</CardDescription>
                        <CardTitle>Active</CardTitle>
                        <CardAction>
                            <Sparkles class="size-5 text-amber-700 dark:text-amber-300" />
                        </CardAction>
                    </CardHeader>
                </Card>
            </section>

            <section class="grid gap-4 lg:grid-cols-3">
                <Card class="border-border bg-card/80 lg:col-span-2">
                    <CardHeader>
                        <CardTitle>Quick actions</CardTitle>
                        <CardDescription>Jump directly into your most frequent admin tasks.</CardDescription>
                    </CardHeader>
                    <CardContent class="grid gap-3 sm:grid-cols-2">
                        <Link
                            :href="productsIndex()"
                            class="rounded-2xl border border-border bg-background/70 p-4 transition hover:border-amber-500"
                        >
                            <p class="flex items-center gap-2 text-sm font-bold text-foreground">
                                <Boxes class="size-4 text-amber-700 dark:text-amber-300" />
                                Manage products
                            </p>
                            <p class="mt-1 text-xs text-muted-foreground">Update pricing, status, and storefront cards.</p>
                        </Link>

                        <Link
                            :href="rolesIndex()"
                            class="rounded-2xl border border-border bg-background/70 p-4 transition hover:border-amber-500"
                        >
                            <p class="flex items-center gap-2 text-sm font-bold text-foreground">
                                <Shield class="size-4 text-amber-700 dark:text-amber-300" />
                                Manage role access
                            </p>
                            <p class="mt-1 text-xs text-muted-foreground">Assign role bundles and permission coverage.</p>
                        </Link>

                        <Link
                            :href="editProfile()"
                            class="rounded-2xl border border-border bg-background/70 p-4 transition hover:border-amber-500 sm:col-span-2"
                        >
                            <p class="flex items-center gap-2 text-sm font-bold text-foreground">
                                <ClipboardCheck class="size-4 text-amber-700 dark:text-amber-300" />
                                Review admin profile settings
                            </p>
                            <p class="mt-1 text-xs text-muted-foreground">Keep credentials and account details up to date.</p>
                        </Link>
                    </CardContent>
                </Card>

                <Card class="border-border bg-card/80">
                    <CardHeader>
                        <CardTitle>Current access</CardTitle>
                        <CardDescription>Permission snapshot from your active role.</CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-2">
                        <Badge class="bg-amber-500 text-amber-950 hover:bg-amber-500">Admin</Badge>
                        <p class="text-xs text-muted-foreground">Top permissions:</p>
                        <ul class="space-y-1 text-xs text-foreground/80">
                            <li v-for="permission in permissions.slice(0, 6)" :key="permission">
                                {{ permission }}
                            </li>
                        </ul>
                    </CardContent>
                </Card>
            </section>
        </div>
    </AdminLayout>
</template>
