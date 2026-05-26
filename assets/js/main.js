
(function() {

    //===== Preloader
	window.onload = function () {
		window.setTimeout(fadeout, 500);
	}

	function fadeout() {
		document.querySelector('.preloader').style.opacity = '0';
		document.querySelector('.preloader').style.display = 'none';
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
	function onScroll(event) {
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

})();