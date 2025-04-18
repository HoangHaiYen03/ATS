<template>
    <div :class="`fixed left-0 top-0 overflow-x-hidden ${sidebarCollapsed ? 'sidebar' : 'sidebar--resize' }`">
        <el-menu
            class="h-screen"
            :default-active="activeMenu"
            :collapse="sidebarCollapsed"
            text-color="#625f6e"
            :unique-opened="false"
            active-text-color="#ffffff"
            :collapse-transition="true"
            mode="vertical"
        >
            <nuxt-link to="/" class="flex justify-center mt-4 mb-3">
                <img
                    v-if="sidebarCollapsed"
                    src="@/assets/images/logo-2.png"
                    alt="Logo"
                    title="Atlantic Careers"
                    class="logo-icon"
                >
                <img
                    v-else
                    src="@/assets/images/logo-2.png"
                    alt="Logo"
                    title="Atlantic Careers"
                    class="logo"
                >
            </nuxt-link>
            <nuxt-link
                v-for="item in menu"
                :key="item.url"
                v-permission="item.permission"
                :to="item.url"
            >
                <el-menu-item :index="item.url">
                    <span class="material-icons-outlined mr-2">{{ item.icon }}</span>
                    <span slot="title" class="text-base">{{ $t(item.label) }}</span>
                </el-menu-item>
            </nuxt-link>
        </el-menu>
    </div>
</template>

<script>
    import { mapState, mapActions } from 'vuex';
    import * as permission from '~/enums/permission';

    export default {
        name: 'SideBar',

        data() {
            return {
                menu: [
                    {
                        url: '/',
                        icon: 'home',
                        label: 'dashboard',
                        permission: [],
                    },
                    {
                        url: '/calendar',
                        icon: 'calendar_month',
                        label: 'calendar',
                        permission: [permission.MANAGE_INTERVIEW_SCHEDULE, permission.VIEW_INTERVIEW_SCHEDULE],
                    },
                    {
                        url: '/pipelines',
                        icon: 'view_week',
                        label: 'pipelines',
                        permission: [permission.MANAGE_PIPELINE, permission.VIEW_PIPELINE],
                    },
                    {
                        url: '/candidates',
                        icon: 'groups',
                        label: 'candidates',
                        permission: [permission.MANAGE_CANDIDATE],
                    },
                    {
                        url: '/jobs',
                        icon: 'business_center',
                        label: 'jobs',
                        permission: [permission.MANAGE_JOB],
                    },
                    {
                        url: '/stages',
                        icon: 'view_week',
                        label: 'stages',
                        permission: [permission.MANAGE_STAGE],
                    },
                    {
                        url: '/assessment-forms',
                        icon: 'assessment',
                        label: 'assessment forms',
                        permission: [permission.MANAGE_ASSESSMENT_FORM],
                    },
                    {
                        url: '/permissions',
                        icon: 'lock',
                        label: 'permissions',
                        permission: [permission.MANAGE_PERMISSION],
                    },
                ],
            };
        },

        computed: {
            ...mapState(['sidebarCollapsed']),
            activeMenu() {
                const route = this.$route;
                const { meta, path } = route;

                if (meta.activeMenu) {
                    return meta.activeMenu;
                }

                return path;
            },
        },

        methods: {
            ...mapActions(['toggleSidebar']),
        },
    };
</script>

<style lang="scss" scoped>
    .sidebar {
        width: 63px;
        &--resize {
            width: 250px;
        }
    }

    .logo {
        height: 70px;
    }

    .logo-icon {
        width: 40px;
        height: 40px;
    }
</style>
