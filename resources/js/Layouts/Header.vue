<template>
	<header>
		<nav class="flex items-center justify-between flex-wrap p-6">
			<div class="flex items-center flex-shrink-0 text-black">
				<inertia-link href="/">
					<img src="/assets/ApexLogo500.svg" alt="Apex Innovations, Education Healthcare Relies On" class="w-44 ml-3 hidden lg:inline-block">
					<img src="/assets/starIcon.svg" alt="Apex Innovations, Education Healthcare Relies On" class="h-12 w-12 ml-3 inline-block lg:hidden">
				</inertia-link>
			</div>
			<div class="block lg:hidden">
				<button class="flex items-center px-3 py-2 border rounded hover:text-gray-300 hover:text-gray-300" v-on:click="toggleNavbar()">
				<svg class="fill-current h-3 w-3" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><title>MENU</title><path d="M0 3h20v2H0V3zm0 6h20v2H0V9zm0 6h20v2H0v-2z"/></svg>
				</button>
			</div>
			<div class="w-full lg:flex lg:items-center lg:w-auto mr-5" :class="{ 'block' : showMenu , 'hidden' : !showMenu}">
				<div class="text-md font-semibold lg:flex-grow lg:flex lg:items-center">					
					
					<inertia-link href="https://www.apexinnovations.com/MyCurriculum.php" class="hover:text-gray-400 block mt-4 lg:inline-block lg:mt-0 mr-5" v-if="showMyCurriculum">
						MY CURRICULUM
					</inertia-link>
					
					<div>
						<button class="font-semibold outline-none focus:outline-none hover:text-gray-400 mt-4 mr-5 block lg:inline-block lg:mt-0" type="button" v-on:click="toggleDropdown('account')" ref="btnDropdownRefAccount">
							ACCOUNT <span class="caret" :class="{'rotate': dropdownPopoverAccountShow}"></span>
						</button>
						<div v-bind:class="{'hidden': !dropdownPopoverAccountShow, 'block': dropdownPopoverAccountShow}" class="bg-white text-base z-50 float-left py-2 list-none text-left rounded shadow-lg mt-1" style="min-width:12rem" ref="popoverDropdownAccountRef">
							<inertia-link href="#" class="hover:bg-gray-200 font-semibold py-2 px-4 font-normal block w-full whitespace-nowrap bg-transparent">
								LOGIN
							</inertia-link>
							<inertia-link href="https://www.apexinnovations.com/CreateAccountLanding.php" class="hover:bg-gray-200 font-semibold py-2 px-4 font-normal block w-full whitespace-nowrap bg-transparent">
								CREATE ACCOUNT
							</inertia-link>
						</div>
					</div>
					
					
					<div>
						<button class="font-semibold outline-none focus:outline-none hover:text-gray-400 mt-4 mr-5 block lg:inline-block lg:mt-0" type="button" v-on:click="toggleDropdown('education')" ref="btnDropdownRefEducation">
							EDUCATION <span class="caret" :class="{'rotate': dropdownPopoverEducationShow}"></span>
						</button>
						<div v-bind:class="{'hidden': !dropdownPopoverEducationShow, 'block': dropdownPopoverEducationShow}" class="bg-white text-base z-50 float-left py-2 list-none text-left rounded shadow-lg mt-1" style="min-width:12rem" ref="popoverDropdownEducationRef">							
							<inertia-link href="https://www.apexinnovations.com/products.html"  class="hover:bg-gray-200 py-2 px-4 block w-full whitespace-nowrap bg-transparent">
								ALL PRODUCTS
							</inertia-link>
							<inertia-link href="https://www.apexinnovations.com/products.html#cardiac"  class="hover:bg-gray-200 py-2 px-4 block w-full whitespace-nowrap bg-transparent">
								CARDIAC COURSEWARE
							</inertia-link>
							<inertia-link href="https://www.apexinnovations.com/products.html#sepsis"  class="hover:bg-gray-200 py-2 px-4 block w-full whitespace-nowrap bg-transparent">
								SEPSIS COURSEWARE
							</inertia-link>
							<inertia-link href="https://www.apexinnovations.com/products.html#neuro"  class="hover:bg-gray-200 py-2 px-4 block w-full whitespace-nowrap bg-transparent">
								NEURO COURSEWARE
							</inertia-link>
							<inertia-link href="https://www.apexinnovations.com/products.html#free"  class="hover:bg-gray-200 py-2 px-4 block w-full whitespace-nowrap bg-transparent">
								FREE COURSEWARE
							</inertia-link>
							<inertia-link href="https://www.apexinnovations.com/products.html#mirule"  class="hover:bg-gray-200 py-2 px-4 block w-full whitespace-nowrap bg-transparent">
								MI RULE VISIONS
							</inertia-link>
							<inertia-link href="https://www.apexinnovations.com/products.html#bundles"  class="hover:bg-gray-200 py-2 px-4 block w-full whitespace-nowrap bg-transparent">
								COURSEWARE BUNDLES
							</inertia-link>
						</div>
					</div>
					
					<inertia-link href="https://www.apexinnovations.com/op.html" class="hover:text-gray-400 block mt-4 lg:inline-block lg:mt-0 mr-5">
						COMPETENCY VALIDATION
					</inertia-link>
					
					<div>
						<button class="font-semibold outline-none focus:outline-none hover:text-gray-400 mt-4 mr-5 block lg:inline-block lg:mt-0" type="button" v-on:click="toggleDropdown('about')" ref="btnDropdownRefAbout">
							ABOUT <span class="caret" :class="{'rotate': dropdownPopoverAboutShow}"></span>
						</button>
						<div v-bind:class="{'hidden': !dropdownPopoverAboutShow, 'block': dropdownPopoverAboutShow}" class="bg-white text-base z-50 float-left py-2 list-none text-left rounded shadow-lg mt-1" style="min-width:12rem" ref="popoverDropdownAboutRef">
							<inertia-link href="https://www.apexinnovations.com/careers.html" class="hover:bg-gray-200 font-semibold py-2 px-4 font-normal block w-full whitespace-nowrap bg-transparent">
								CAREERS
							</inertia-link>
							<inertia-link href="https://www.apexinnovations.com/contactUs.html" class="hover:bg-gray-200 font-semibold py-2 px-4 font-normal block w-full whitespace-nowrap bg-transparent">
								CONTACT US
							</inertia-link>
							<inertia-link href="https://www.apexinnovations.com/news.html" class="hover:bg-gray-200 font-semibold py-2 px-4 font-normal block w-full whitespace-nowrap bg-transparent">
								NEWS
							</inertia-link>
							<inertia-link href="https://www.apexinnovations.com/team.html" class="hover:bg-gray-200 font-semibold py-2 px-4 font-normal block w-full whitespace-nowrap bg-transparent">
								TEAM
							</inertia-link>
						</div>
					</div>
				</div>
			</div>
		</nav>
	</header>
</template>

<style scoped>
	nav {
		background-color: #f8f8f8;
		border-color: #e7e7e7;
	}
	.caret {
		display: inline-block;
		width: 0;
		height: 0;
		margin-left: 2px;
		vertical-align: middle;
		border-top: 4px dashed;
		border-right: 4px solid transparent;
		border-left: 4px solid transparent;
	}
	.rotate {
		transition: 100ms linear all;
		transform: rotate(180deg);
	}
</style>
<script>
import { createPopper } from "@popperjs/core";


export default {
	name: "apexheader",
	data() {
		return {
			showMenu: false,
			dropdownPopoverAboutShow: false,
			dropdownPopoverEducationShow: false,
			dropdownPopoverAccountShow: false,
			showMyCurriculum: false,
		}
	},
	methods: {
		toggleNavbar: function(){
			this.showMenu = !this.showMenu;
		},
		toggleDropdown: function(menu){
			if(menu == "about"){
				if(this.dropdownPopoverAboutShow){
					this.dropdownPopoverAboutShow = false;
				} else {
					this.dropdownPopoverAboutShow = true;
					createPopper(this.$refs.btnDropdownRefAbout, this.$refs.popoverDropdownAboutRef, {
						placement: "bottom-start"
					});
					this.dropdownPopoverEducationShow = false;
					this.dropdownPopoverAccountShow = false;
				}
			}else if (menu == "education"){
				if(this.dropdownPopoverEducationShow){
					this.dropdownPopoverEducationShow = false;
				} else {
					this.dropdownPopoverEducationShow = true;
					createPopper(this.$refs.btnDropdownRefEducation, this.$refs.popoverDropdownEducationRef, {
						placement: "bottom-start"
					});
					this.dropdownPopoverAboutShow = false;
					this.dropdownPopoverAccountShow = false;
				}			
			}else if (menu == "account"){
				if(this.dropdownPopoverAccountShow){
					this.dropdownPopoverAccountShow = false;
				} else {
					this.dropdownPopoverAccountShow = true;
					createPopper(this.$refs.btnDropdownRefAccount, this.$refs.popoverDropdownAccountRef, {
						placement: "bottom-start"
					});
					this.dropdownPopoverEducationShow = false;
					this.dropdownPopoverAboutShow = false;
				}			
			}
		}
	},
}
</script>