<template>
    <div class="w-full">
        <div class="flex flex-wrap">
            <div class="w-full lg:w-9/12">
                <EditProfile />
            </div>
            <div class="w-full lg:w-3/12">
                <UploadAvatar />
            </div>
        </div>
    </div>
</template>

<script>
    import EditProfile from '~/components/Candidate/EditProfile/index.vue';
    import UploadAvatar from '~/components/Candidate/UploadAvatar/index.vue';

    export default {
        name: 'EditProfilePage',

        components: {
            EditProfile,
            UploadAvatar,
        },

        layout: 'candidate',
    };
</script>
