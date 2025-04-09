<template>
    <el-dialog :visible.sync="visible" title="Add Candidate">
        <el-form ref="form" :model="form" :rules="rules" label-position="top">
            <el-form-item label="Name" prop="name">
                <el-input v-model="form.name"></el-input>
            </el-form-item>
            <el-form-item label="Email" prop="email">
                <el-input v-model="form.email"></el-input>
            </el-form-item>
            <el-form-item label="Phone Number" prop="phoneNumber">
                <el-input v-model="form.phoneNumber"></el-input>
            </el-form-item>
            <el-form-item label="Resume" prop="resume">
                <el-upload
                    ref="upload"
                    drag
                    :multiple="false"
                    :limit="1"
                    action="#"
                    :file-list="fileList"
                    :on-change="changeFileList"
                    :auto-upload="false"
                    :on-remove="removeFile"
                >
                    <i class="el-icon-upload" />
                    <div class="el-upload__text">
                        Drop file here or <em>click to upload</em>
                    </div>
                </el-upload>
            </el-form-item>
        </el-form>
        <span slot="footer" class="dialog-footer">
            <el-button @click="visible = false">Cancel</el-button>
            <el-button type="primary" @click="submit($refs.form)">Submit</el-button>
        </span>
    </el-dialog>
</template>

<script>
export default {
    data() {
        return {
            visible: false,
            form: {
                name: '',
                email: '',
                phoneNumber: '',
                resume: null,
            },
            fileList: [],
            rules: {
                name: [{ required: true, message: 'Please input name', trigger: 'blur' }],
                email: [{ required: true, message: 'Please input email', trigger: 'blur' }],
                phoneNumber: [{ required: true, message: 'Please input phone number', trigger: 'blur' }],
                resume: [{ required: true, message: 'Please upload resume', trigger: 'change' }],
            },
        };
    },
    props: {
        jobId: {
            type: Number,
            required: true,
        },
    },
    created() {
        console.log('dsadas', this.jobId);
    },
    methods: {
        changeFileList(file, fileList) {
            this.fileList = fileList;
            this.form.resume = file.raw;
        },
        removeFile() {
            this.form.resume = null;
        },
        async submit(formRef) {
            formRef.validate(async (valid) => {
                if (valid) {
                    try {
                        const formData = new FormData();
                        formData.append('name', this.form.name);
                        formData.append('email', this.form.email);
                        formData.append('phoneNumber', this.form.phoneNumber);
                        formData.append('resume', this.form.resume, this.form.resume.name);

                        await this.$axios.$post(`jobs/${this.jobId}/admin/candidates`, formData);

                        this.visible = false;
                    } catch (error) {
                        this.$handleError(error);
                    } finally {
                        window.location.reload();
                    }
                }
            });
        },
    },
};
</script>
