(function ($) {
    'use strict';

    var browserWindow = $(window);

    // :: 1.0 Preloader Active Code
    browserWindow.on('load', function () {
        $('.preloader').fadeOut('slow', function () {
            $(this).remove();
        });
    });

    // :: 2.0 Nav Active Code
    if ($.fn.classyNav) {
        $('#alazeaNav').classyNav();
    }

    // :: 3.0 Search Active Code
    $('#searchIcon').on('click', function () {
        $('.search-form').toggleClass('active');
    });
    $('.closeIcon').on('click', function () {
        $('.search-form').removeClass('active');
    });

    // :: 4.0 Sliders Active Code
    if ($.fn.owlCarousel) {
        var welcomeSlide = $('.hero-post-slides');
        var testiSlides = $('.testimonials-slides');
        var portfolioSlides = $('.portfolio-slides');

        welcomeSlide.owlCarousel({
            items: 1,
            margin: 0,
            loop: true,
            nav: false,
            dots: false,
            autoplay: true,
            center: true,
            autoplayTimeout: 5000,
            smartSpeed: 1000
        });

        testiSlides.owlCarousel({
            items: 1,
            margin: 0,
            loop: true,
            nav: false,
            dots: true,
            autoplay: true,
            autoplayTimeout: 5000,
            smartSpeed: 700,
            animateIn: 'fadeIn',
            animateOut: 'fadeOut'
        });

        portfolioSlides.owlCarousel({
            items: 2,
            margin: 30,
            loop: true,
            nav: true,
            navText: ['<i class="fa fa-angle-left"></i>', '<i class="fa fa-angle-right"></i>'],
            dots: true,
            autoplay: false,
            autoplayTimeout: 5000,
            smartSpeed: 700,
            center: true
        });
    }

    // :: 5.0 Masonary Gallery Active Code
    if ($.fn.imagesLoaded) {
        $('.alazea-portfolio').imagesLoaded(function () {
            // filter items on button click
            $('.portfolio-filter').on('click', 'button', function () {
                var filterValue = $(this).attr('data-filter');
                $grid.isotope({
                    filter: filterValue
                });
            });
            // init Isotope
            var $grid = $('.alazea-portfolio').isotope({
                itemSelector: '.single_portfolio_item',
                percentPosition: true,
                masonry: {
                    columnWidth: '.single_portfolio_item'
                }
            });
        });
    }

    // :: 6.0 magnificPopup Active Code
    // Limit lightbox to portfolio only — disable on product cards to avoid
    // intercepting clicks on favorite/cart/eye actions
    if ($.fn.magnificPopup) {
        $('.portfolio-img').magnificPopup({
            gallery: { enabled: true },
            type: 'image'
        });
        $('.video-icon').magnificPopup({ type: 'iframe' });
    }

    // :: 7.0 Barfiller Active Code
    if ($.fn.barfiller) {
        $('#bar1').barfiller({
            tooltip: true,
            duration: 1000,
            barColor: '#70c745',
            animateOnResize: true
        });
        $('#bar2').barfiller({
            tooltip: true,
            duration: 1000,
            barColor: '#70c745',
            animateOnResize: true
        });
        $('#bar3').barfiller({
            tooltip: true,
            duration: 1000,
            barColor: '#70c745',
            animateOnResize: true
        });
        $('#bar4').barfiller({
            tooltip: true,
            duration: 1000,
            barColor: '#70c745',
            animateOnResize: true
        });
    }

    // :: 8.0 ScrollUp Active Code
    if ($.fn.scrollUp) {
        browserWindow.scrollUp({
            scrollSpeed: 1500,
            scrollText: '<i class="fa fa-angle-up"></i>'
        });
    }

    // :: 9.0 CounterUp Active Code
    if ($.fn.counterUp) {
        $('.counter').counterUp({
            delay: 10,
            time: 2000
        });
    }

    // :: 10.0 Sticky Active Code
    if ($.fn.sticky) {
        $(".alazea-main-menu").sticky({
            topSpacing: 0
        });
    }

    // :: 11.0 Tooltip Active Code
    if ($.fn.tooltip) {
        $('[data-toggle="tooltip"]').tooltip()
    }

    // :: 12.0 Price Range Active Code
    $('.slider-range-price').each(function () {
        var min = jQuery(this).data('min');
        var max = jQuery(this).data('max');
        var unit = jQuery(this).data('unit');
        var value_min = jQuery(this).data('value-min');
        var value_max = jQuery(this).data('value-max');
        var label_result = jQuery(this).data('label-result');
        var t = $(this);
        $(this).slider({
            range: true,
            min: min,
            max: max,
            values: [value_min, value_max],
            slide: function (event, ui) {
                var result = label_result + " " + unit + ui.values[0] + ' - ' + unit + ui.values[1];
                console.log(t);
                t.closest('.slider-range').find('.range-price').html(result);
            }
        });
    })

    // :: 13.0 prevent default a click
    $('a[href="#"]').on('click', function ($) {
        $.preventDefault();
    });

    // Ensure login button always points to Laravel admin login
    try { $('.login a').attr('href','/sign-in'); } catch(e) {}

    // UrbanGreen: replace main nav, title and footer credits
    try {
        var navUl = $('#alazeaNav .classynav > ul');
        if (false && navUl.length) {
            navUl.html('\
                <li><a href="index.html">Home</a></li>\
                <li><a href="event.html">Event</a></li>\
                <li><a href="shop.html">Shop</a></li>\
                <li><a href="blog.html">Blog</a></li>\
                <li><a href="maintenance.html">Maintenance</a></li>');
        }
        document.title = 'UrbanGreen';
        $('.footer-logo img, .nav-brand img').attr('alt','UrbanGreen');
        var copy = '© ' + new Date().getFullYear() + ' Webcore — UrbanGreen. All rights reserved.';
        $('.copywrite-text p').html(copy);
        var fnav = $('.footer-nav nav ul');
        if (false && fnav.length) {
            fnav.html('\
                <li><a href="index.html">Home</a></li>\
                <li><a href="event.html">Event</a></li>\
                <li><a href="shop.html">Shop</a></li>\
                <li><a href="blog.html">Blog</a></li>\
                <li><a href="maintenance.html">Maintenance</a></li>');
        }
    } catch(e) {}

    // UrbanGreen: brand text + footer cleanup and links
    try {
        // Replace image logo with two-tone text
        var brand = $('.nav-brand');
        if (brand.length) {
            brand.html('<span class="brand-text"><span class="brand-urban">URBAN</span><span class="brand-green">GREEN</span></span>');
        }
        var footerLogo = $('.footer-logo');
        if (footerLogo.length) {
            footerLogo.html('<span class="brand-text footer-brand"><span class="brand-urban">URBAN</span><span class="brand-green">GREEN</span></span>');
        }

        // Inject minimal CSS for brand text + footer rebalance
        var css = '' +
          '.brand-text{font-family:"Dosis",sans-serif;font-weight:700;letter-spacing:1px;color:#fff;font-size:28px;line-height:1;}\n' +
          '.brand-text .brand-green{color:#70c745;}\n' +
          '.footer-brand{font-size:26px;}\n' +
          '.footer-area .main-footer-area .row > [class*="col-lg-3"]{margin-bottom:20px;}\n';
        $('<style/>').text(css).appendTo('head');

        // Remove CONTACT column and rebalance remaining columns to span fully
        $('.single-footer-widget .widget-title h5').each(function(){
            if ($(this).text().trim().toUpperCase() === 'CONTACT') {
                $(this).closest('.col-12').remove();
            }
        });
        // Update lg span from 3 to 4 for remaining three columns
        $('.main-footer-area .row > div').each(function(){
            if ($(this).hasClass('col-lg-3')) {
                $(this).removeClass('col-lg-3').addClass('col-lg-4');
            }
        });

        // Replace Quick Link list with project pages
        $('.single-footer-widget .widget-title h5').each(function(){
            if ($(this).text().trim().toUpperCase() === 'QUICK LINK') {
                var ul = $(this).closest('.single-footer-widget').find('.widget-nav ul');
                if (false && ul.length) {
                    ul.html('<li><a href="index.html">Home</a></li>'+
                            '<li><a href="event.html">Event</a></li>'+
                            '<li><a href="shop.html">Shop</a></li>'+
                            '<li><a href="blog.html">Blog</a></li>'+
                            '<li><a href="maintenance.html">Maintenance</a></li>');
                }
            }
        });

        // Replace footer description with a concise UrbanGreen summary
        var desc = `UrbanGreen encourage la végétalisation des espaces urbains pour améliorer la qualité de vie.
Le projet fédère citoyens et associations afin de rendre les villes plus vertes, respirables et conviviales.`;
        var firstWidget = $('.footer-logo').closest('.single-footer-widget');
        if (firstWidget.length) {
            var p = firstWidget.find('p').first();
            if (p.length) { p.text(desc); }
        }
    } catch(e) {}

    // :: 14.0 wow Active Code
    if (browserWindow.width() > 767) {
        new WOW().init();
    }

    // Normalize any legacy .html links to Laravel routes
    try {
        var routeMap = {
            'index.html': '/',
            'event.html': '/event',
            'shop.html': '/shop',
            'shop-details.html': '/shop/details',
            'cart.html': '/cart',
            'checkout.html': '/checkout',
            'portfolio.html': '/portfolio',
            'single-portfolio.html': '/portfolio/single',
            'blog.html': '/blog',
            'post.html': '/blog/post',
            'maintenance.html': '/maintenance'
        };
        $('a[href$=".html"]').each(function(){
            var href = ($(this).attr('href')||'').trim();
            if (routeMap[href]) {
                $(this).attr('href', routeMap[href]);
            }
        });
    } catch(e) {}

})(jQuery);
