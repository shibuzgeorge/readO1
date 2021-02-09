<template>
    <card v-if="isLoaded">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 id="title">Edit Category</h5>
            <button @click="$router.go(-1)" type="button" class="btn btn-sm btn-primary">Back</button>
        </div>
        <form @submit.prevent="submit" @keydown="form.onKeydown($event)">
            <div class="form-check mt-4">
                <label>Category Name:           </label> <input class="form-control" v-model="form.name" :class="{ 'is-invalid': form.errors.has('name') }" type="text" value="" required/><has-error :form="form" field="name" /><br/>
                <label>Category Description:     </label> <input class="form-control" v-model="form.description" :class="{ 'is-invalid': form.errors.has('description') }" type="text" value="" required/><has-error :form="form" field="description" /><br/>
                <label>Current Textbooks:     </label>
                <ul>
                    <li v-for="textbook in form.textbooks">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="exampleCheck1" :value="textbook.id" v-model="form.checked">
                            <label class="form-check-label" for="exampleCheck1">{{textbook.title}} - {{textbook.description}}</label>
                        </div>
                    </li>
                </ul>
                <label>Unassigned Textbooks:     </label>
                <ul>
                    <li v-for="textbook in form.unassigned">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" :value="textbook.id" v-model="form.checked">
                            <label class="form-check-label" for="exampleCheck1">{{textbook.title}} - {{textbook.description}}</label>
                        </div>
                    </li>
                </ul>
                <br/>
                <br/>
            </div>
            <sweet-modal ref="success" v-on:close="$router.go(-1)" icon="success">
                {{successMessage}}
            </sweet-modal>
            <v-button class="form-control" :loading="form.busy" type="success">
                Edit
            </v-button>
        </form>
    </card>
    <div v-else>
        <sweet-modal ref="error" v-on:close="$router.go(-1)" icon="error">
            {{errorMessage}}
        </sweet-modal>
        <clip-loader color="black"/>
    </div>
</template>

<script>
    import axios from 'axios'
    import Form from 'vform'
    export default {
        name: "edit",
        middleware: 'admin_plus_module_tutor',
        data: () => ({
            isLoaded: false,
            form: new Form({
                name: '',
                description: '',
                textbooks: '',
                checked: [],
                unassigned: '',
            }),
            errorMessage: '',
            successMessage: '',

        }),
        created() {
            let self = this
            axios.get(`/api/extensiveReading/${this.$route.params.id}/edit`)
                .then(response => {
                    if(response.data.Error !== undefined){
                        this.errorMessage = response.data.Error;
                        this.$refs.error.open();
                    }else {
                        this.isLoaded = true;
                        this.form.name = response.data.name;
                        this.form.description = response.data.description;
                        this.form.textbooks = response.data.textbooks;
                        this.form.unassigned = response.data.unassigned;
                        this.form.textbooks.forEach(function (textbook) {
                                self.form.checked.push(textbook.id);
                        });
                    }
                }).catch(function (response) {
                //handle error
                //self.$router.push({name: 'extensiveReading.index'})

                console.log(response);
            });

        },
        methods:{
            async submit(){
                await this.form.patch(`/api/extensiveReading/${this.$route.params.id}`)
                    .then(response => {
                        this.successMessage = response.data.Success;
                        this.$refs.success.open();
                    }).catch(error => {
                        console.log(error.response)
                    });

            }
        }


    }
</script>

<style scoped>

</style>