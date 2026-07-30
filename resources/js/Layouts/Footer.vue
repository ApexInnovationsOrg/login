<template>
	<footer>
		<div class="w-full bg-footer py-5 px-20 mx-auto grid">				
			<div class="grid grid-cols-1 md:grid-cols-1 w-5/6 mt-2 mx-auto text-black font-medium items-end">				
				<div class="grid grid-cols-1 text-center leading-relaxed pt-5 md:pt-0 order-2">					
					<div class="">Copyright 2002 - 2026 Apex Innovations, LLC, All Rights Reserved </div>
					<div class=""><a href="https://www.apexinnovations.com/verifyCertificate.php" class="footerLinks">Certification Verification</a> | <a href="https://www.apexinnovations.com/contactUs.html" class="footerLinks">Contact Us</a> | <a href="https://www.apexinnovations.com/PrivacyPolicy.html" class="footerLinks">Privacy Policy</a> | <a href="https://www.apexinnovations.com/TermsAndConditions.html" class="footerLinks">Terms & Conditions</a> | <a href="https://www.apexinnovations.com/EUA.html" class="footerLinks">EUA</a> | <a href="https://www.apexinnovations.com/Accessibility.html" class="footerLinks">Accessibility</a></div>
				</div>
			</div>
			
		</div>
	</footer>
</template>

<style scoped>
	.footerLinks {
		color: #b20000;
		text-decoration: none;
	}
	.footerLinks:hover {
		text-decoration: underline;
	}
</style>
<script>
import { Link } from '@inertiajs/vue3'

export default {
	name: "apexfooter",
	components: { Link },
	data() {
		return {
		}
	},
	methods: {

	},
}
</script>