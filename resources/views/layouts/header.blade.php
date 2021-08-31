<nav class="flex items-center justify-between flex-wrap p-6 navbg">
			<div class="flex items-center flex-shrink-0 text-black">
                <img src="/assets/ApexLogo500.svg" alt="Apex Innovations, Education Healthcare Relies On" class="w-44 ml-3 hidden lg:inline-block">
				<a href="https://www.apexinnovations.com/">
					<img src="/assets/starIcon.svg" alt="Apex Innovations, Education Healthcare Relies On" class="h-12 w-12 ml-3 inline-block lg:hidden">
				</a>
			</div>
			<div class="block lg:hidden">
				<button class="flex items-center px-3 py-2 border rounded hover:text-gray-300 hover:text-gray-300">
				    <svg class="fill-current h-3 w-3" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><title>MENU</title><path d="M0 3h20v2H0V3zm0 6h20v2H0V9zm0 6h20v2H0v-2z"/></svg>
				</button>
			</div>
			<div class="w-full lg:flex lg:items-center lg:w-auto mr-5">
				<div class="text-md font-semibold lg:flex-grow lg:flex lg:items-center">					
					<x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button class="flex items-center font-semibold outline-none focus:outline-none hover:text-gray-400 mt-4 mr-5 block lg:mt-0" type="button" ref="btnDropdownRefAccount">
                                <div>ACCOUNT</div>
                                <div class="ml-1">
                                    <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </button>
                        </x-slot>
                        <x-slot name="content">
                                <x-dropdown-link href="/">
                                    LOGIN
                                </x-dropdown-link>
                                <x-dropdown-link href="https://www.apexinnovations.com/CreateAccountLanding.php">
                                    CREATE ACCOUNT
                                </x-dropdown-link>
                        </x-slot>
                    </x-dropdown>
					
					
					<x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                        <button class="flex items-center font-semibold outline-none focus:outline-none hover:text-gray-400 mt-4 mr-5 block lg:mt-0" type="button" ref="btnDropdownRefAccount">
                                <div>EDUCATION</div>
                                <div class="ml-1">
                                    <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </button>
                        </x-slot>
                        <x-slot name="content">
                            <div class="bg-white text-base z-50 float-left py-2 list-none text-left rounded shadow-lg mt-1" style="min-width:12rem" ref="popoverDropdownEducationRef">							
                                <x-dropdown-link href="https://www.apexinnovations.com/products.html">    
                                        ALL PRODUCTS
                                </x-dropdown-link>
                                <x-dropdown-link href="https://www.apexinnovations.com/products.html#cardiac">
                                   
                                        CARDIAC COURSEWARE
                                </x-dropdown-link>
                                <x-dropdown-link href="https://www.apexinnovations.com/products.html#sepsis">
                                   
                                        SEPSIS COURSEWARE
                                </x-dropdown-link>
                                <x-dropdown-link href="https://www.apexinnovations.com/products.html#neuro">
                                   
                                        NEURO COURSEWARE

                                </x-dropdown-link>                                    
                                <x-dropdown-link  href="https://www.apexinnovations.com/products.html#free">
                                    
                                        FREE COURSEWARE
                                </x-dropdown-link>
                                <x-dropdown-link href="https://www.apexinnovations.com/products.html#mirule">
                                   
                                        MI RULE VISIONS
                                </x-dropdown-link>
                                <x-dropdown-link href="https://www.apexinnovations.com/products.html#bundles" >
                                        COURSEWARE BUNDLES
                                </x-dropdown-link>
                            </div>
                        </x-slot>
                    </x-dropdown>
					
					<a href="https://www.apexinnovations.com/op.html" class="hover:text-gray-400 block mt-4 lg:inline-block lg:mt-0 mr-5">
						COMPETENCY VALIDATION
					</a>
					
					<x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                        <button class="flex items-center font-semibold outline-none focus:outline-none hover:text-gray-400 mt-4 mr-5 block lg:mt-0" type="button" ref="btnDropdownRefAccount">
                                <div>ABOUT</div>
                                <div class="ml-1">
                                    <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </button>
                        </x-slot>
                        <x-slot name="content">
                            <div class="bg-white text-base z-50 float-left py-2 list-none text-left rounded shadow-lg mt-1" style="min-width:12rem" ref="popoverDropdownAboutRef">
                                <x-dropdown-link href="https://www.apexinnovations.com/careers.html">
                                        CAREERS
                                </x-dropdown-link>
                                <x-dropdown-link href="https://www.apexinnovations.com/contactUs.html">
                                        CONTACT US
                                </x-dropdown-link>
                                <x-dropdown-link href="https://www.apexinnovations.com/news.html">
                                        NEWS
                                </x-dropdown-link>
                                <x-dropdown-link href="https://www.apexinnovations.com/team.html">
                                        TEAM
                                </x-dropdown-link>
                            </div>
                        </x-slot>
					</x-dropdown>
				</div>
			</div>
		</nav>