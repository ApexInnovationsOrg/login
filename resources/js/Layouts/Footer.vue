<template>
	<footer style="background-color:#f9fafa;font-size:0.7em;">
		<div class="bg-footer py-4 mx-auto grid container">
			
			<div class="flex items-center flex-shrink-0 mx-auto md:w-5/6">
				<inertia-link href="/">
					<img width="45" height="50" src="/assets/starIcon.svg" alt="Apex Innovations, Education Healthcare Relies On" >
				</inertia-link>
				<div class="pl-1">
					Copyright 2002-2025<br/>
					Apex Innovations, LLC,<br/>
					All Rights Reserved
				</div>
				<div class="pl-6"><a href="https://www.apexinnovations.com/verifyCertificate.php">Certification Verification</a> | <a href="https://www.apexinnovations.com/contactUs.html">Contact Us</a> | <a href="https://www.apexinnovations.com/PrivacyPolicy.html">Privacy Policy</a> | <a
					href="https://www.apexinnovations.com/TermsAndConditions.html">Terms &amp; Conditions</a> | <a href="https://www.apexinnovations.com/EUA.html">EUA</a></div>
			</div>
			
			
		</div>
	</footer>
</template>

<style scoped>
	.footerLinks:hover {
		font-weight: bold;
	}
	.bg-footer {
		background-color:#e6e7e6;
	}
	a {
		color: #d20000;
	}
</style>
<script>
export default {
	name: "apexfooter",
	data() {
		return {
		}
	},
	methods: {
		
	},
}
</script>