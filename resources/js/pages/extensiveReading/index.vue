<template>
    <card class="container-fluid" v-if="isLoaded">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 id="title">Extensive Reading</h5>
            <button @click="$router.go(-1)" type="button" class="btn btn-sm btn-primary">Back</button>
        </div>
        <div class="wrapper mt-4">
            <div class="d-flex justify-content-between align-items-center">
                <div class="col-md-3">
                    <input type="search" v-model="searchQuery" class="form-control" placeholder="Search by category..."
                           aria-label="Search" />
                </div>
            </div>
            <h1 style="text-align: center;" class="mt-4" v-if="!categories || !categories.length">
                There are no categories!
            </h1>
                <div v-for="category in categoriesPaginated" class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                    <h5><router-link :to="{ name: 'extensiveReading.show', params: {id: category.id} }">
                        {{category.name}}
                    </router-link></h5>
                        <div>
                            <router-link v-show="role==='Admin' || role==='Module Tutor'" :to="{ name: 'textbook.create' }">
                                <button class="btn btn-sm btn-success" v-if="role!=='User'">+ Add textbook</button>
                            </router-link>
                            <router-link v-if="role==='Admin' || role==='Module Tutor'" :to="{ name: 'extensiveReading.edit', params: {id: category.id} }">
                                <button v-show="role==='Admin' || role==='Module Tutor'" type="button" class="btn btn-primary btn-sm">Edit</button>
                            </router-link>
                            <a v-if="role==='Admin' || role==='Module Tutor'" @click="del(category.id, category.name)"><button type="button" class="btn btn-warning btn-sm">Delete</button></a>
                        </div>

                    </div>

                    <hr>
                    <div v-if="category.textbooks.length > 0" v-for="textbook in category.textbooks" class="card">
                        <div class="card-body">
                        <router-link :to="{ name: 'textbook.show', params: {id: textbook.id} }">
                            <h5 class="card-title">{{textbook.title}}</h5>
                            <h6 class="card-subtitle mb-2 text-muted">{{textbook.description }}</h6>
                        </router-link>
                        <router-link :to="{ name: 'textbook.edit', params: {id: textbook.id} }">
                            <a v-show="role==='Admin' || role==='Module Tutor'" href="edit" class="card-link">Edit</a>
                        </router-link>
                        </div>
                    </div>
                    <div v-if="category.textbooks.length === 0">
                        No textbooks for this category
                    </div><hr>
            </div><br/>
            <paginate style="display: flex; justify-content: center;" :items="resultQuery" :pageSize="sizePage" @changePage="onChangePage"></paginate>
        </div>
            </card>
    <div v-else style="text-align: center;">
        <sweet-modal ref="error" v-on:close="$router.go(-1)" icon="error">
            {{errorMessage}}
        </sweet-modal>
        <clip-loader color="black"/>
    </div>
</template>

<script>
    import axios from 'axios'
    import { mapGetters } from 'vuex'
    import Swal from 'sweetalert2'
    export default {
        middleware: 'auth',
        data: () => ({
            isLoaded: false,
            categories: [],
            categoriesPaginated: [],
            errorMessage: '',
            searchQuery: null,
            sizePage: 9,
        }),
        computed : {
            ...mapGetters({
                role: 'auth/role'
            }),
            resultQuery() {
                if (this.searchQuery) {
                    return this.categories.filter((item) => {
                        return this.searchQuery.toLowerCase().split(' ').every(
                            v => item.name.toLowerCase().includes(v) ||
                                item.description.toLowerCase().includes(v))
                    })
                }else {
                    return this.categories.sort((a, b) => parseFloat(b.textbooks.length) - parseFloat(a.textbooks.length));
                }
            }
        },
        created() {
            axios.get(`/api/extensiveReading`)
                .then(response => {
                    this.categories = response.data;
                    this.isLoaded = true;
                }).catch(error => {
                this.$router.go(-1)
            });
        },
        methods: {
            onChangePage(pageOfItems) {
                this.categoriesPaginated = pageOfItems;
            },
        },
        metaInfo () {
            return { title: this.$t('home') }
        }


    }
</script>

<style scoped>
    .flexbox {
        display: flex;
        flex-direction: row;
        flex-wrap: wrap;
        justify-content: center;
        align-items: center;
    }
    .flex-item{
        margin: auto;
    }
</style>