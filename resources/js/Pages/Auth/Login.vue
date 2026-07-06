<template>
    <breeze-validation-errors class="mb-4" />

    <div v-if="status" class="mb-4 font-medium text-sm text-green-600">
        {{ status }}
    </div>

    <form @submit.prevent="showPassword ? submit() : lookupSso()">
        <div>
            <breeze-label for="email" value="Email" />
            <breeze-input id="email" type="email" class="mt-1 block w-full" v-model="form.email" required autofocus autocomplete="username" />
        </div>

        <div v-show="showPassword" class="mt-4">
            <breeze-label for="password" value="Password" />
            <breeze-input id="password" ref="password" type="password" class="mt-1 block w-full" v-model="form.password" :required="showPassword" autocomplete="current-password" />
        </div>

        <div v-show="showPassword" class="block mt-4">
            <label class="flex items-center">
                <breeze-checkbox name="remember" v-model:checked="form.remember" />
                <span class="ml-2 text-sm text-gray-600">Remember me</span>
            </label>
        </div>

        <div class="flex items-center justify-end mt-4">
            <Link v-if="showPassword && canResetPassword" :href="route('password.request')" class="underline text-sm text-gray-600 hover:text-gray-900">
                Forgot your password?
            </Link>

            <breeze-button class="ml-4" :class="{ 'opacity-25': busy }" :disabled="busy">
                {{ showPassword ? 'Log in' : 'Continue' }}
            </breeze-button>
        </div>
    </form>
</template>

<script>
    import BreezeButton from '@/Components/Button'
    import BreezeGuestLayout from "@/Layouts/Guest"
    import BreezeInput from '@/Components/Input'
    import BreezeCheckbox from '@/Components/Checkbox'
    import BreezeLabel from '@/Components/Label'
    import BreezeValidationErrors from '@/Components/ValidationErrors'
    import { Link, useForm } from '@inertiajs/vue3'

    export default {
        layout: BreezeGuestLayout,

        components: {
            BreezeButton,
            BreezeInput,
            BreezeCheckbox,
            BreezeLabel,
            BreezeValidationErrors,
            Link,
        },

        props: {
            canResetPassword: Boolean,
            status: String,
        },

        data() {
            return {
                form: useForm({
                    email: '',
                    password: '',
                    remember: false
                }),
                showPassword: false,
                lookingUp: false,
            }
        },

        computed: {
            busy() {
                return this.lookingUp || this.form.processing
            }
        },

        methods: {
            lookupSso() {
                this.lookingUp = true

                window.axios.post(this.route('sso.lookup'), { email: this.form.email })
                    .then(({ data }) => {
                        if (data.sso) {
                            window.location.assign(data.sso)
                            return
                        }

                        this.showPassword = true
                        this.$nextTick(() => document.getElementById('password')?.focus())
                    })
                    .catch(() => {
                        // Lookup failing (throttle, network) must never lock
                        // anyone out — fall through to the password form.
                        this.showPassword = true
                    })
                    .finally(() => {
                        this.lookingUp = false
                    })
            },

            submit() {
                this.form.post(this.route('login'), {
                    onFinish: () => this.form.reset('password'),
                })
            }
        }
    }
</script>
