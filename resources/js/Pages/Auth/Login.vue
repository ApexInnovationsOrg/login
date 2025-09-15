<template>
    <breeze-validation-errors class="mb-4" />

    <div v-if="status" class="mb-4 font-medium text-sm text-green-600">
        {{ status }}
    </div>

    <!-- Plain form submit -->
    <form method="POST" action="/login">
        <input type="hidden" name="_token" :value="csrfToken" />

        <div>
            <breeze-label for="email" value="Email" />
            <breeze-input id="email" type="email" class="mt-1 block w-full"
                          name="email" required autofocus autocomplete="username" />
        </div>

        <div class="mt-4">
            <breeze-label for="password" value="Password" />
            <breeze-input id="password" type="password" class="mt-1 block w-full"
                          name="password" required autocomplete="current-password" />
        </div>

        <div class="block mt-4">
            <label class="flex items-center">
                <breeze-checkbox name="remember" />
                <span class="ml-2 text-sm text-gray-600">Remember me</span>
            </label>
        </div>

        <div class="flex items-center justify-end mt-4">
            <inertia-link v-if="canResetPassword" :href="route('password.request')" 
                class="underline text-sm text-gray-600 hover:text-gray-900">
                Forgot your password?
            </inertia-link>

            <breeze-button class="ml-4">
                Log in
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
        csrfToken: String,   // provided by middleware
    }
}
</script>
