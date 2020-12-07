<template>
    <card title="Create Module">
        <form @submit.prevent="submit" @keydown="form.onKeydown($event)">
            <div class="form-check">
                <label>Module Name:           </label> <input class="form-control" v-model="form.module_name" type="text" value="" required/><br/>
                <label>Module Code:     </label> <input class="form-control" v-model="form.module_code" type="text" value="" required/><br/>
                <label>Module Year Group:     </label> <input class="form-control" v-model="form.module_year" type="text" value="" required/><br/>
                <br/>
            </div>

            <v-button class="form-control" :loading="form.busy" type="success">
                Add
            </v-button>
        </form>
    </card>
</template>

<script>
    import axios from 'axios'
    import Form from 'vform'
    export default {
        name: "create",
        middleware: 'admin_plus_module_tutor',
        data: () => ({
            isLoaded: false,
            form: new Form({
                module_name: '',
                module_code: '',
                module_year: ''
            }),

        }),
        methods:{
            async submit() {
                await axios.post('/api/module/v', {
                    module_name: this.form.module_name,
                    module_code: this.form.module_code,
                    module_year: this.form.module_year,
                })
                    .then(response => {
                        console.log(response)
                    })
                    .catch(error => {
                        console.log(error.response)
                    });


                this.$router.push({name: 'module.v.index'})
            }
        }
    }
</script>

<style scoped>

</style>