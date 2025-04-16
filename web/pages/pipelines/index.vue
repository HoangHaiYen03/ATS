<template>
    <div class="w-full">
        <div class="flex justify-end items-center">
            <CandidateFilter
                :jobs="$get(pipeline, 'jobs', [])"
                :job-id="jobId"
                :on-change-job="onChangeJob"
                :pipeline="pipeline"
                :pipelines="pipelines"
                :on-change-pipeline="onChangePipeline"
                :query="query"
                :update-query="updateQuery"
            />
            <PipelineSetting
                :pipeline="pipeline"
            />
        </div>
        <div v-if="pipeline" class="mt-5 w-full flex flex-row overflow-x-auto">
            <StageKanban
                v-for="stage in $get(pipeline, 'stages')"
                :key="$get(stage, 'id')"
                class="kanban mr-3"
                group="stage"
                :stage="stage"
                :pipeline="pipeline"
                :job-id="jobId"
                :staffs="staffs"
                :rooms="rooms"
                :mail-templates="mailTemplates"
                :assessment-forms="assessmentForms"
                :query="query"
                :open-edit-stage-action="openEditStageAction"
                :trigger-actions="triggerActions"
            />
        </div>
        <div v-else class="mt-6 text-center">
            <p>{{ $t('no data') }}</p>
        </div>
        <FormStageAction
            ref="formStageAction"
            :actions="actions"
            :actions-added="actionsAdded"
            :add-action="addAction"
            :remove-action="removeAction"
        />
        <FormCandidateRejection ref="formCandidateRejection" />
    </div>
</template>

<script>
    import _find from 'lodash/find';
    import _findIndex from 'lodash/findIndex';
    import _each from 'lodash/each';
    import StageKanban from '~/components/Stage/Kanban/index.vue';
    import PipelineSetting from '~/components/Pipeline/List/PipelineSetting.vue';
    import CandidateFilter from '~/components/Pipeline/List/Filter/index.vue';
    import { MANAGE_PIPELINE, VIEW_PIPELINE } from '~/enums/permission';
    import FormStageAction from '~/components/Stage/FormStageAction.vue';
    import { REJECT_CANDIDATE } from '~/enums/action/action-type';
    import FormCandidateRejection from '~/components/Rejection/Form/index.vue';

    export default {
        name: 'PipelinesPage',

        components: {
            StageKanban,
            PipelineSetting,
            CandidateFilter,
            FormStageAction,
            FormCandidateRejection,
        },

        middleware: 'permission',

        meta: {
            permissions: [MANAGE_PIPELINE, VIEW_PIPELINE],
        },

        async asyncData({ $axios, query }) {
            const { data: pipelines } = await $axios.$get('pipelines');
            const pipeline = query.id ? pipelines.find(({ id }) => id === Number(query.id)) : pipelines[0];

            return {
                pipelines,
                pipeline,
            };
        },

        data() {
            return {
                jobId: 0,
                staffs: [],
                rooms: [],
                mailTemplates: [],
                assessmentForms: [],
                actions: [],
                actionsAdded: [],
                query: {},
            };
        },

        async fetch() {
            const [
                { data: staffs },
                { data: rooms },
                { data: mailTemplates },
                { data: assessmentForms },
                { data: actions },
            ] = await Promise.all([
                await this.$axios.$get('staffs'),
                await this.$axios.$get('rooms'),
                await this.$axios.$get('mail-templates'),
                await this.$axios.$get('assessment-forms'),
                await this.$axios.$get('actions'),
            ]);

            this.staffs = staffs;
            this.rooms = rooms;
            this.mailTemplates = mailTemplates;
            this.assessmentForms = assessmentForms;
            this.actions = actions;
        },

        methods: {
            onChangePipeline(value) {
                this.$router.push('/pipelines');
                this.pipeline = _find(this.pipelines, ['id', value]);
                this.jobId = 0;
            },
            updateQuery(value) {
                this.query = value;
            },
            onChangeJob(value) {
                this.jobId = value;
            },
            async openEditStageAction(stage) {
                const { data: actions } = await this.$axios.$get(`pipeline-stages/${stage.pipelineStageId}/actions`);
                this.actionsAdded = actions;
                this.$refs.formStageAction.open(stage);
            },
            async addAction(actionId, pipelineStageId, data) {
                const { data: action } = await this.$axios.$post(`pipeline-stages/${pipelineStageId}/actions/${actionId}`, {
                    options: { ...data },
                });
                const index = _findIndex(this.actionsAdded, ['id', action.id]);
                if (index === -1) {
                    this.actionsAdded.push(action);
                }
                this.$message.success(this.$t('update successfully'));
            },
            async removeAction(actionId, pipelineStageId) {
                this.$confirm(this.$t('do you want to delete?'), this.$t('delete action from stage'), {
                    confirmButtonText: this.$t('confirm'),
                    cancelButtonText: this.$t('cancel'),
                    type: 'warning',
                }).then(async () => {
                    await this.$axios.$delete(`pipeline-stages/${pipelineStageId}/actions/${actionId}`);
                    const index = _findIndex(this.actionsAdded, ['id', actionId]);
                    if (index !== -1) {
                        this.actionsAdded.splice(index, 1);
                    }
                    this.$message.success(this.$t('delete successfully'));
                });
            },
            triggerActions(stage, candidate) {
                const actions = this.$get(stage, 'actions', []);

                _each(actions, (action) => {
                    if (action.type === REJECT_CANDIDATE) {
                        this.$refs.formCandidateRejection.open(candidate, this.pipeline.id, stage.id);
                    }
                });
            },
        },
    };
</script>
