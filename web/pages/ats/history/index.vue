<template>
    <div>
        <el-carousel height="300px">
            <el-carousel-item v-for="item in 2" :key="item">
                <el-image
                    style="height: 300px"
                    :src="require(`~/assets/images/banner${item}.png`)"
                    fit="fill"
                />
            </el-carousel-item>
        </el-carousel>
        <div class="px-4 md:px-8">
            <h1 class="text-2xl font-bold my-6">{{ $t('your_application_history') }}</h1>
            <el-card
                v-for="job in jobs"
                :key="job['job-info'].id"
                shadow="hover"
                class="cursor-pointer mb-4"
                @click.native="$router.push(`/ats/jobs/${job['job-info'].id}`)"
            >
                <div class="flex justify-start">
                    <el-avatar
                        :size="100"
                        :src="job['job-info'].photo_url || require('~/assets/images/logo-2.png')"
                    />
                    <div class="info ml-5">
                        <div class="font-semibold text-xl uppercase leading-relaxed pb-2">
                            {{ job['job-info'].name }}
                        </div>
                        <div class="flex items-center pb-2">
                            <span class="material-icons-outlined text-xl mr-2">monetization_on</span>
                            {{ formatSalary(
                                job['job-info'].min_offer,
                                job['job-info'].max_offer,
                                job['job-info'].currency
                            ) }}
                        </div>
                        <div class="flex items-center pb-2">
                            <span class="material-icons-outlined text-xl mr-2">location_on</span>
                            {{ job['job-info'].city }}, {{ job['job-info'].country }}
                        </div>
                        <div class="flex items-center pb-2">
                            <span class="material-icons-outlined text-xl mr-2">schedule</span>
                            {{ $t('deadline') }}: {{ job['job-info'].deadline }}
                        </div>
                        <div class="flex items-center pb-2">
                            <span class="material-icons-outlined text-xl mr-2">work</span>
                            {{ $t('stage2') }}: {{ job.stage }}
                        </div>
                    </div>
                </div>
            </el-card>
        </div>
    </div>
</template>

<script>

    export default {
        name: 'AppliedHistory',

        layout: 'candidate',

        middleware: 'auth',

        async asyncData({ $axios, query, $auth }) {
            const { data: jobs, meta } = await $axios.$get(`jobs/${$auth.user.id}/applied`, {
                params: query,
            });
            return {
                jobs,
                meta,
            };
        },

        watchQuery: true,

        methods: {
            formatSalary(min, max, currency) {
                if (!min || !max) return 'Negotiable';
                return `${this.formatNumber(min)} - ${this.formatNumber(max)} ${currency}`;
            },

            formatNumber(number) {
                return new Intl.NumberFormat().format(number);
            },
        },
    };
</script>

<style>
    .el-carousel__item,
    .el-image__inner {
        background-color: white;
        height: 300px;
        width: 100vw;
    }
    .el-carousel__indicators {
        display: none;
    }

    .px-4 {
        padding-right: 1rem;
    }

    .md\:px-8 {
        @media (min-width: 768px) {
            padding-left: 2rem;
            padding-right: 2rem;
        }
    }
</style>
