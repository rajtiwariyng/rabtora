
(function() {

    //===== Preloader
	window.onload = function () {
		window.setTimeout(fadeout, 500);
	}

	function fadeout() {
		var preloader = document.querySelector('.preloader');
		if (preloader) {
			preloader.style.opacity = '0';
			preloader.style.display = 'none';
		}
	}


    /*=====================================
    Sticky
    ======================================= */
    window.onscroll = function () {
        var header_navbar = document.querySelector(".navbar-area");
        var sticky = header_navbar.offsetTop;

        if (window.pageYOffset > sticky) {
            header_navbar.classList.add("sticky");
        } else {
            header_navbar.classList.remove("sticky");
        }

        // show or hide the back-top-top button
        var backToTo = document.querySelector(".scroll-top");
        if (document.body.scrollTop > 50 || document.documentElement.scrollTop > 50) {
            backToTo.style.display = "flex";
        } else {
            backToTo.style.display = "none";
        }
    };

    // Get the navbar


    // for menu scroll 
    var pageLink = document.querySelectorAll('.page-scroll');
    
    pageLink.forEach(elem => {
        elem.addEventListener('click', e => {
            e.preventDefault();
            document.querySelector(elem.getAttribute('href')).scrollIntoView({
                behavior: 'smooth',
                offsetTop: 1 - 60,
            });
        });
    });

    // section menu active
	function onScroll() {
		var sections = document.querySelectorAll('.page-scroll');
		var scrollPos = window.pageYOffset || document.documentElement.scrollTop || document.body.scrollTop;

		for (var i = 0; i < sections.length; i++) {
			var currLink = sections[i];
			var val = currLink.getAttribute('href');
			var refElement = document.querySelector(val);
			var scrollTopMinus = scrollPos + 73;
			if (refElement.offsetTop <= scrollTopMinus && (refElement.offsetTop + refElement.offsetHeight > scrollTopMinus)) {
				document.querySelector('.page-scroll').classList.remove('active');
				currLink.classList.add('active');
			} else {
				currLink.classList.remove('active');
			}
		}
	};

	window.document.addEventListener('scroll', onScroll);


    //===== close navbar-collapse when a  clicked
    let navbarToggler = document.querySelector(".navbar-toggler");    
    var navbarCollapse = document.querySelector(".navbar-collapse");

    document.querySelectorAll(".page-scroll").forEach(e =>
        e.addEventListener("click", () => {
            navbarToggler.classList.remove("active");
            navbarCollapse.classList.remove('show')
        })
    );
    navbarToggler.addEventListener('click', function() {
        navbarToggler.classList.toggle("active");
    }) 

    // service slider

    $('.project-one__carousel').owlCarousel({
        loop:true,
        margin:30,
        nav:true,
        navText: [
            "<i class='bi bi-chevron-left'></i>", // Previous icon
            "<i class='bi bi-chevron-right'></i>" // Next icon
        ],
        dots: false,
        responsive:{
            0:{
                items:1
            },
            600:{
                items:3
            },
            1000:{
                items:4
            }
        }
    })
    if(window.innerWidth > 1200) {
        $('.service-carousel').owlCarousel({
            loop:true,
            margin:30,
            autoplay:true,
            nav:true,
            navText: [
                "<i class='bi bi-chevron-left'></i>", // Previous icon
                "<i class='bi bi-chevron-right'></i>" // Next icon
            ],
            dots: false,
            responsive:{
                0:{
                    items:1
                },
                600:{
                    items:3
                },
                1000:{
                    items:5
                }
            }
        })
    }

    $('.project-one__carousel').owlCarousel({
        loop:true,
        margin:30,
        nav:true,
        navText: [
            "<i class='bi bi-chevron-left'></i>", // Previous icon
            "<i class='bi bi-chevron-right'></i>" // Next icon
        ],
        dots: false,
        responsive:{
            0:{
                items:1
            },
            600:{
                items:3
            },
            1000:{
                items:4
            }
        }
    })

    $('.partner-slider').owlCarousel({
        loop:true,
        margin:50,
        nav:false,
        dots: false,
        autoplay:true,
        responsive:{
            0:{
                items:1
            },
            600:{
                items:3
            },
            1000:{
                items:6
            }
        }
    })

    $('.testimonials-slider').owlCarousel({
        loop:true,
        margin:50,
        nav:false,
        dots: true,
        autoplay:true,
        responsive:{
            0:{
                items:1
            },
            600:{
                items:1
            },
            1000:{
                items:3
            }
        }
    })

    function equalTestimonialHeight() {
        let maxHeight = 0;
        document.querySelectorAll('.testimonial-item').forEach(item => {
          item.style.height = 'auto';
          maxHeight = Math.max(maxHeight, item.offsetHeight);
        });
      
        document.querySelectorAll('.testimonial-item').forEach(item => {
          item.style.height = maxHeight + 'px';
        });
      }
      
      window.addEventListener('load', equalTestimonialHeight);
      window.addEventListener('resize', equalTestimonialHeight);


	// WOW active
    new WOW().init();

    // WhatsApp floating button (injected on all pages)
    (function() {
        var wa = document.createElement('a');
        wa.href = 'https://wa.me/97155471132';
        wa.target = '_blank';
        wa.rel = 'noopener noreferrer';
        wa.className = 'whatsapp-float';
        wa.setAttribute('aria-label', 'Chat with us on WhatsApp');
        wa.setAttribute('title', 'Chat with Rabtora on WhatsApp');
        wa.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path d="M380.9 97.1C339 55.1 283.2 32 223.9 32c-122.4 0-222 99.6-222 222 0 39.1 10.2 77.3 29.6 111L0 480l117.7-30.9c32.4 17.7 68.9 27 106.1 27h.1c122.3 0 224.1-99.6 224.1-222 0-59.3-25.2-115-67.1-157zm-157 341.6c-33.2 0-65.7-8.9-94-25.7l-6.7-4-69.8 18.3L72 359.2l-4.4-7c-18.5-29.4-28.2-63.3-28.2-98.2 0-101.7 82.8-184.5 184.6-184.5 49.3 0 95.6 19.2 130.4 54.1 34.8 34.9 56.2 81.2 56.1 130.5 0 101.8-84.9 184.6-186.6 184.6zm101.2-138.2c-5.5-2.8-32.8-16.2-37.9-18-5.1-1.9-8.8-2.8-12.5 2.8-3.7 5.6-14.3 18-17.6 21.8-3.2 3.7-6.5 4.2-12 1.4-32.6-16.3-54-29.1-75.5-66-5.7-9.8 5.7-9.1 16.3-30.3 1.8-3.7.9-6.9-.5-9.7-1.4-2.8-12.5-30.1-17.1-41.2-4.5-10.8-9.1-9.3-12.5-9.5-3.2-.2-6.9-.2-10.6-.2-3.7 0-9.7 1.4-14.8 6.9-5.1 5.6-19.4 19-19.4 46.3 0 27.3 19.9 53.7 22.6 57.4 2.8 3.7 39.1 59.7 94.8 83.8 35.2 15.2 49 16.5 66.6 13.9 10.7-1.6 32.8-13.4 37.4-26.4 4.6-13 4.6-24.1 3.2-26.4-1.3-2.5-5-3.9-10.5-6.6z"/></svg>';
        document.body.appendChild(wa);
    })();

})();