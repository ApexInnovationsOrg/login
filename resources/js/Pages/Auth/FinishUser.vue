<template>
    <breeze-authenticated-layout>
       
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Finish Account Creation
            </h2>
        </template>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                         <breeze-validation-errors class="mb-4" />
                        <form @submit.prevent="submit">
                            <label v-if="departments.length > 0" class="block mt-4">
                                <span class="text-gray-700">Department</span>
                                <select required class="form-select mt-1 block w-full" v-model="form.departmentID">
                                    <option v-for="dept in departments" :key="dept.ID" :value="dept.ID">{{dept.Name}}</option>
                                </select>
                            </label>
                            <label class="block mt-4">
                                <span class="text-gray-700">Professional Role</span>
                                <select required class="form-select mt-1 block w-full" @change="filterCredentials" v-model="form.professionalRoleID">
                                    <option value="0" disabled selected>Please Select Role</option>
                                    <option v-for="role in roles" :key="role.ID" :value="role.ID">{{role.Name}}</option>
                                </select>
                            </label>
                            <label v-if="form.professionalRoleID !== 0" class="block mt-4">
                                <span class="text-gray-700">Credential</span>
                                <select required class="form-select mt-1 block w-full" v-model="form.credentialID" :disabled="form.professionalRoleID === 0">
                                    <option v-for="cred in filteredCredentials" :key="cred.ID" :value="cred.ID">{{cred.Name}}</option>
                                </select>
                            </label>

                            <div v-if="form.professionalRoleID === 3" class="bg-gray-50 mt-4 p-4">

                                <label class="block">
                                    <span class="text-gray-700">State of Licensure: </span>
                                        <select required class="form-select mt-1 block w-full" v-model="form.EMSData.stateID">
                                            <option v-for="state in states" :key="state.ID" :value="state.ID">{{state.Name}}</option>
                                        </select>
                                </label>
                                <label class="block mt-4">
                                   <span class="text-gray-700">State License #: </span>
                                   <input required type="text" class="form-input mt-1 block w-full" v-model="form.EMSData.licenseNo"/>
                                </label>
                                <label class="block mt-4">
                                    <span class="text-gray-700">State Lic. Exp. Date: <strong>Format: MM/DD/YYYY</strong></span>
                                    <!-- <Calendar required  class="form-input mt-1 block w-full" v-model="form.EMSData.stateExpDate" :showIcon="true" :yearNavigator="true" :yearRange="yearRange" /> -->
                                </label>
                                <label class="block mt-4">
                                    <span class="text-gray-700">NEMSID: </span>
                                        <input required type="text" class="form-input mt-1 block w-full" v-model="form.EMSData.NEMSID"/>
                                </label>
                                <label class="block mt-4">   
                                    <span class="text-gray-700">NREMT Cert. #</span>
                                     <input required  type="text" class="form-input mt-1 block w-full" v-model="form.EMSData.NREMT"/>
                                </label>
                                <label class="block mt-4">   
                                    <span class="text-gray-700">NREMT Rereg. Date: <strong>Format:MM/DD/YYYY</strong></span>
                                    <!-- <Calendar required  class="form-input mt-1 block w-full" v-model="form.EMSData.reregDate"  :showIcon="true" :yearNavigator="true" :yearRange="yearRange" /> -->
                                </label>
                                <label class="block mt-4">   
                                    <span class="text-gray-700">License Type: </span>
                                        <select required  class="form-select mt-1 block w-full" v-model="form.EMSData.licenseType">
                                            <option v-for="type in licenseTypes" :key="type.ID" :value="type.ID">{{type.Name}}</option>
                                    </select>
                                </label>
                            </div>
                            <div class="flex items-center justify-center mt-4">
                                <breeze-button class="ml-4" :class="{ 'opacity-25': form.processing }" :disabled="form.processing || (form.professionalRoleID === 0 || form.credentialID === 0)">
                                    Save
                                </breeze-button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </breeze-authenticated-layout>
</template>

<script>
    // import Calendar from 'primevue/calendar'
    import BreezeButton from '@/Components/Button'
    import BreezeAuthenticatedLayout from '@/Layouts/Authenticated'
    import BreezeInput from '@/Components/Input'
    import BreezeLabel from '@/Components/Label'
    import BreezeValidationErrors from '@/Components/ValidationErrors'
    import moment from 'moment';

    export default {
        props: {
            departments: Array,
            credentials: Array,
            roles: Array,
            credentialFilter: Array,
            licenseTypes: Array,
            states: Array
        },
        components: {
            Calendar,
            BreezeAuthenticatedLayout,
            BreezeButton,
            BreezeInput,
            BreezeLabel,
            BreezeValidationErrors,
        },

        data() {
            return {
                form: this.$inertia.form({
                    professionalRoleID:0,
                    departmentID:0,
                    credentialID:0,
                    EMSData:{
                        stateID:0,
                        licenseNo:null,
                    }
                }),
                filteredCredentials :[],
                yearRange : ''
            }
        },
        mounted: function(){
            console.log('created');
            
            this.yearRange = moment().subtract(1,'years').format('YYYY') + ':' + moment().add(8,'years').format('YYYY')
        },
        methods: {
            submit() {
                this.form.post(this.route('finishUser'), {
                    onFinish: () => this.form.reset('password', 'password_confirmation'),
                })
            },

            filterCredentials(){
                this.filteredCredentials = [];
                this.form.credentialID = 0;
                const tempCreds = [];

                this.credentialFilter.forEach(filter=>{
                    if(filter.ProfessionalRoleID === this.form.professionalRoleID)
                    {
                        this.credentials.forEach(cred=>{
                            if(cred.ID === filter.CredentialID)
                            {
                                this.filteredCredentials.push(cred);
                            }
                        })
                    }
                })

            }
        }
    }
</script>
