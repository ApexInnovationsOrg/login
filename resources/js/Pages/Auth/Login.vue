<template>
    <div>
        <div class="text-center" style="font-size:2.5em;">
            <h1>Login</h1>
            <div class="redBorderTop" style="width:30%;margin:0 auto;"></div>
        </div>

        <breeze-validation-errors class="mb-4" />

        <div v-if="status" class="mb-4 font-medium text-sm text-green-600">
            {{ status }}
        </div>

        <form @submit.prevent="submit" style="background-color: #edeff0;padding: 2em;border-radius: 0.5em;margin: 2em;">
            <div>

            </div>
            <div>
                <breeze-label for="email" value="Email" />
                <breeze-input id="email" type="email" class="mt-1 block w-full" v-model="form.email" required autofocus autocomplete="username" />
            </div>

            <div class="mt-4">
                <breeze-label for="password" value="Password" />
                <breeze-input id="password" type="password" class="mt-1 block w-full" v-model="form.password" required autocomplete="current-password" />
            </div>

            <div class="block mt-4">
                <label class="flex items-center">
                    <breeze-checkbox name="remember" v-model:checked="form.remember" />
                    <span class="ml-2 text-sm text-gray-600">Remember me</span>
                </label>
            </div>

            <div class="flex items-center justify-center mt-4">
                <breeze-button class="ml-4" :class="{ 'opacity-25': form.processing }" :disabled="form.processing" style="background-color: #d20000;">
                    Login
                </breeze-button>
            </div>
        </form>

        
        <div class="flex items-center justify-center mt-4">
            <inertia-link v-if="canResetPassword" :href="route('password.request')" class="" style="color:#d20000">
                Forgot your password?
            </inertia-link>
        </div>
        <div class="flex items-center justify-center">
            <div style="border-bottom:1px solid black; width:30%">&nbsp;</div><div class="mt-4">&nbsp;&nbsp;or&nbsp;&nbsp;</div><div style="border-bottom:1px solid black; width:30%">&nbsp;</div>
        </div>
        <div class="flex items-center justify-center mt-2">
            <div>Don't have an account yet?</div>
        </div>
        <div class="flex items-center justify-center">            
            <div><a href="https://www.apexinnovations.com/CreateAccountLanding.php" style="color:#d20000">Create an account</a></div>
        </div>


    </div>
    
</template>
<style scoped>
.redBorderTop{
	border-top:2px solid #D20000;
}
</style>
<script>
    import BreezeButton from '@/Components/Button'
    import BreezeGuestLayout from "@/Layouts/Guest"
    import BreezeInput from '@/Components/Input'
    import BreezeCheckbox from '@/Components/Checkbox'
    import BreezeLabel from '@/Components/Label'
    import BreezeValidationErrors from '@/Components/ValidationErrors'

    export default {
        layout: BreezeGuestLayout,

        components: {
            BreezeButton,
            BreezeInput,
            BreezeCheckbox,
            BreezeLabel,
            BreezeValidationErrors
        },

        props: {
            canResetPassword: Boolean,
            status: String,
        },

        data() {
            return {
                form: this.$inertia.form({
                    email: '',
                    password: '',
                    remember: false
                })
            }
        },

        methods: {
            submit() {
                this.form.post(this.route('login'), {
                    onFinish: () => this.form.reset('password'),
                })
            }
        }
    }
</script>
