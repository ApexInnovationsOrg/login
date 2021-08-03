<template>
	<footer>
		<div class="w-full bg-footer py-5 px-20 mx-auto grid">
			
			<div class="flex items-center flex-shrink-0 mx-auto md:w-5/6">
				<inertia-link href="/">
					<img src="/assets/ApexLogo500.svg" alt="Apex Innovations, Education Healthcare Relies On" class="w-44">
				</inertia-link>
			</div>
			
			<div class="grid gap-2 grid-cols-2 md:grid-cols-4 mx-auto py-5 text-gray-500 font-medium w-5/6 text-sm">
				<div class="ml-10 md:ml-20">Education</div>
				<div class="ml-10 md:ml-20">About</div>
				<div class="ml-10 md:ml-20">Team</div>
				<div class="ml-10 md:ml-20">Login</div>
				<div class="ml-10 md:ml-20">Tools</div>
				<div class="ml-10 md:ml-20">Mission</div>
				<div class="ml-10 md:ml-20">
					<inertia-link href="/careers" class="footerLinks">
						Careers
					</inertia-link>
				</div>
				<div class="ml-10 md:ml-20">Create Account</div>
				<div class="ml-10 md:ml-20">Quantum Competency</div>
				<div class="ml-10 md:ml-20">News</div>
				<div class="ml-10 md:ml-20">Contact Us</div>				
			</div>			
			
			<div class="grid grid-cols-1 md:grid-cols-3 w-5/6 mt-2 mx-auto text-gray-500 font-medium text-sm items-end">
				<div class="grid grid-cols-1 mx-auto md:mx-0 order-3 md:order-1 pt-5 md:pt-0">
					<div>All rights reserved © 2019<br/>
					Apex Innovations, LLC</div>
				</div>
				<div class="grid grid-cols-1 text-center leading-relaxed pt-5 md:pt-0 order-2">
					<div class="flex items-stretch mx-auto">
						<a href="https://www.facebook.com/Apex.online.continuing.education" target="_blank"><img src="/assets/facebook_logo.png" alt="Apex Innovation's Facebook" class="w-2 mr-2"></a>
						<a href="https://twitter.com/ApexInnovations" target="_blank"><img src="/assets/twitter_logo.png" alt="Apex Innovation's Twitter" class="w-4 ml-2"></a>	
					</div>
					<div>866-294-4599</div>
					<div>Privacy Policy | Terms & Conditions | EULA | Contact Us</div>
				</div>
				<div class="grid grid-cols-1 text-right mx-auto md:mx-0 order-1 md:order-3">
					<div>NEED HELP</div>
				</div>
			</div>
			
		</div>
	</footer>
</template>

<style scoped>
	.footerLinks:hover {
		font-weight: bold;
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