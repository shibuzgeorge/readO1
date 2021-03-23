<template>

    <card v-if="isLoaded">
        <div class="card-header d-flex justify-content-between align-items-center">
        <h5 id="title">Library</h5>
            <button @click="$router.go(-1)" type="button" class="btn btn-sm btn-primary">Back</button>
        </div>
        <div class="form-inline d-flex justify-content-between align-items-center">
            <input type="search" v-model="searchQuery" class="form-control" placeholder="Search by title or description..."
                   aria-label="Search" />
            <select class="form-control col-lg-3 ml-2" v-model="filterByModule" v-on:change="resetExtensiveReadingCategoryFilter()">
                <option>Filter by module...</option>
                <option v-for="module in modules" :value="module.id" :key="module.id">
                    {{ module.name }}
                </option>
            </select>

            <select class="form-control col-lg-3 ml-2" v-model="filterByExtensiveReadingCategory"  v-on:change="resetModulesFilter()">
                <option>Filter by extensive reading category...</option>
                <option v-for="category in extensiveReadingCategories" :value="category.id" :key="category.id">
                    {{ category.name }}
                </option>
            </select>
        </div>
        <div class="wrapper">
            <div class="row justify-content-center">
                <div v-for="textbook in textbooksPaginated" class="card col-sm-5 ml-4 mt-4">
                    <div class="card-body" :class="{ 'row': textbook.thumbnail!==null }">
                        <div v-if="textbook.thumbnail!==null" class="col-sm-5 d-flex justify-content-center">
                        <img :src="'data:image/png;base64,'+textbook.thumbnail" width="150" height="200"/>
                        </div>
                        <div :class="{ 'col-sm-7': textbook.thumbnail!==null }">
                        <router-link :to="{ name: 'textbook.show', params: {id: textbook.id} }">
                            <h5 class="card-title">{{textbook.title}}</h5>
                            <h6 class="card-subtitle mb-2 text-muted">{{textbook.description }}</h6>
                        </router-link>
                        <router-link :to="{ name: 'textbook.edit', params: {id: textbook.id} }">
                            <a v-show="role==='Admin' || role==='Module Tutor'" href="edit" class="card-link">Edit</a>
                        </router-link>
                        <a @click="del(textbook.id)" v-show="role==='Admin' || role==='Module Tutor'" href="#" class="card-link">Delete</a><br/>
                        </div>
                    </div>
                </div>
            </div><br/>
            <paginate style="display: flex; justify-content: center;" :items="resultQuery" :pageSize="sizePage" @changePage="onChangePage"></paginate>
        </div>
    </card>
    <div v-else style="text-align: center;">
        <clip-loader :color="loadingColor"/>
    </div>

</template>

<script>
    import axios from 'axios'
    import { mapGetters } from 'vuex'
    import Swal from 'sweetalert2';
    export default {
        middleware: 'auth',
        data: () => ({
            isLoaded: false,
            textbooks: [],
            filterByModule: 'Filter by module...',
            filterByExtensiveReadingCategory: 'Filter by extensive reading category...',
            textbooksPaginated: [],
            extensiveReadingCategories: [],
            modules: [],
            loadingColor: "black",
            searchQuery: null,
            sizePage: 9,
        }),
        computed : {
            ...mapGetters({
                role: 'auth/role'
            }),
            resultQuery() {
                if (this.searchQuery && this.filterByModule !== 'Filter by module...') {
                    return this.textbooks.filter(item => item.pivot.module_id === this.filterByModule &&
                         this.searchQuery.toLowerCase().split(' ').every(
                            v => item.title.toLowerCase().includes(v) ||
                                item.description.toLowerCase().includes(v))
                    )
                }else if (this.searchQuery && this.filterByExtensiveReadingCategory !== 'Filter by extensive reading category...') {
                    return this.textbooks.filter(item => item.pivot.extensive_reading_category_id === this.filterByExtensiveReadingCategory &&
                        this.searchQuery.toLowerCase().split(' ').every(
                            v => item.title.toLowerCase().includes(v) ||
                                item.description.toLowerCase().includes(v))
                    )
                }else
                if (this.searchQuery) {
                    return this.textbooks.filter((item) => {
                        return this.searchQuery.toLowerCase().split(' ').every(
                            v => item.title.toLowerCase().includes(v) ||
                                item.description.toLowerCase().includes(v))
                    })
                }else if (this.filterByModule !== 'Filter by module...') {
                    return  this.textbooks.filter(item => item.pivot.module_id === this.filterByModule);
                }else if (this.filterByExtensiveReadingCategory !== 'Filter by extensive reading category...') {
                    return  this.textbooks.filter(item => item.pivot.extensive_reading_category_id === this.filterByExtensiveReadingCategory);
                } else{
                    return this.textbooks;
                }
            }
        },
        created() {

            axios.get('api/textbook/')
                .then(response => {
                    this.isLoaded = true;
                    this.textbooks = response.data.textbooks
                    this.modules = response.data.modules
                    this.extensiveReadingCategories = response.data.extensiveReadingCategories

                }).catch(error => {
                console.log(error.response)
            });
        },
        methods: {
            async del(data) {
                Swal.fire({
                    title: 'Are you sure?',
                    text: "Do you really want to delete the textbook?",
                    type: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!',
                }).then(function (result) {
                    if (result.value) {
                        Swal.fire(
                            'Deleted!',
                            'The textbook has been deleted.',
                            'success'
                        );
                        axios.delete(`/api/textbook/${data}`).then(response => {
                            window.location.reload();
                        })
                    }
                });

            },
            onChangePage(pageOfItems) {
                this.textbooksPaginated = pageOfItems;
            },
            resetExtensiveReadingCategoryFilter(){
                this.filterByExtensiveReadingCategory = 'Filter by extensive reading category...';
            },
            resetModulesFilter(){
                this.filterByModule = 'Filter by module...';
            }
        }

    }
</script>
