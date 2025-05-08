<template>
    <div>
       
    <apexheader/>
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 apex-bg">
        <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">
            
            <slot />
        </div>
    </div>
    <apexfooter/>
    </div>
</template>

<script>
    import apexheader from '../Layouts/Header.vue'
    import apexfooter from '@/Layouts/Footer.vue'
    import BreezeApplicationLogo from '@/Components/ApplicationLogo'
    import BreezeDropdown from '@/Components/Dropdown'
    import BreezeDropdownLink from '@/Components/DropdownLink'
    import BreezeNavLink from '@/Components/NavLink'
    import BreezeResponsiveNavLink from '@/Components/ResponsiveNavLink'

    export default {
        components: {
            apexheader,
            apexfooter,
            BreezeApplicationLogo,
            BreezeDropdown,
            BreezeDropdownLink,
            BreezeNavLink,
            BreezeResponsiveNavLink,
        },
        data() { 
             return {
                showingNavigationDropdown: false,
            }
        }
    }
</script>

<style scoped>
.apex-bg{
    background-color: #f9fafa;
    /* background-image: url(data:image/svg+xml;base64,PHN2ZyB4bWxucz0naHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmcnIHZlcnNpb249JzEuMScgd2lkdGg9JzQwMCcgaGVpZ2h0PSc0MDAnPgoJPGRlZnMgaWQ9J2RlZnM0Jz4KCQk8ZmlsdGVyIGNvbG9yLWludGVycG9sYXRpb24tZmlsdGVycz0nc1JHQicgaWQ9J2ZpbHRlcjMxMTUnPgoJCQk8ZmVUdXJidWxlbmNlIHR5cGU9J2ZyYWN0YWxOb2lzZScgbnVtT2N0YXZlcz0nMScgYmFzZUZyZXF1ZW5jeT0nMC45JyBpZD0nZmVUdXJidWxlbmNlMzExNycgLz4KCQkJPGZlQ29sb3JNYXRyaXggcmVzdWx0PSdyZXN1bHQ1JyB2YWx1ZXM9JzEgMCAwIDAgMCAwIDEgMCAwIDAgMCAwIDEgMCAwIDAgMCAwIDYgLTQuMzUwMDAwMDAwMDAwMDAwNSAnIGlkPSdmZUNvbG9yTWF0cml4MzExOScgLz4KCQkJPGZlQ29tcG9zaXRlIGluMj0ncmVzdWx0NScgb3BlcmF0b3I9J2luJyBpbj0nU291cmNlR3JhcGhpYycgcmVzdWx0PSdyZXN1bHQ2JyBpZD0nZmVDb21wb3NpdGUzMTIxJyAvPgoJCQk8ZmVNb3JwaG9sb2d5IGluPSdyZXN1bHQ2JyBvcGVyYXRvcj0nZGlsYXRlJyByYWRpdXM9JzE1JyByZXN1bHQ9J3Jlc3VsdDMnIGlkPSdmZU1vcnBob2xvZ3kzMTIzJyAvPgoJCTwvZmlsdGVyPgoJPC9kZWZzPgoJPHJlY3Qgd2lkdGg9JzEwMCUnIGhlaWdodD0nMTAwJScgeD0nMCcgeT0nMCcgaWQ9J3JlY3QyOTg1JyBmaWxsPScjY2NjY2NjJy8+ICAgICAKCTxyZWN0IHdpZHRoPScxMDAlJyBoZWlnaHQ9JzEwMCUnIHg9JzAnIHk9JzAnIGlkPSdyZWN0Mjk4NScgc3R5bGU9J2ZpbGw6I2ZmZmZmZjtmaWx0ZXI6dXJsKCNmaWx0ZXIzMTE1KScgLz4KPC9zdmc+); */
}
</style>