
/* -------------
Content
--------------------- */

"use strict";
!function () {

    window.Element.prototype.removeClass = function () {
        let className = arguments.length > 0 && void 0 !== arguments[0] ? arguments[0] : "",
            selectors = this;
        if (!(selectors instanceof HTMLElement) && selectors !== null) {
            selectors = document.querySelector(selectors);
        }
        if (this.isVariableDefined(selectors) && className) {
            selectors.classList.remove(className);
        }
        return this;
    }, window.Element.prototype.addClass = function () {
        let className = arguments.length > 0 && void 0 !== arguments[0] ? arguments[0] : "",
            selectors = this;
        if (!(selectors instanceof HTMLElement) && selectors !== null) {
            selectors = document.querySelector(selectors);
        }
        if (this.isVariableDefined(selectors) && className) {
            selectors.classList.add(className);
        }
        return this;
    }, window.Element.prototype.toggleClass = function () {
        let className = arguments.length > 0 && void 0 !== arguments[0] ? arguments[0] : "",
            selectors = this;
        if (!(selectors instanceof HTMLElement) && selectors !== null) {
            selectors = document.querySelector(selectors);
        }
        if (this.isVariableDefined(selectors) && className) {
            selectors.classList.toggle(className);
        }
        return this;
    }, window.Element.prototype.isVariableDefined = function () {
        return !!this && typeof (this) != 'undefined' && this != null;
    }
}();


var e = {
    init: function () {
        e.preLoader(),
        e.megaMenu(),
        e.stickyHeader(),
        e.tinySlider(),
        e.parallaxBG(),
        e.aosFunc(),
        e.stickyBar(),
        e.formValidation(),
        e.toolTipFunc(),
        e.popOverFunc(),
        e.backTotop(),
        e.lightBox(),
        e.typeText(),
        e.enableIsotope(),
        e.waveCanvas()
    },
    isVariableDefined: function (el) {
        return typeof !!el && (el) != 'undefined' && el != null;
    },
    getParents: function (el, selector, filter) {
        const result = [];
        const matchesSelector = el.matches || el.webkitMatchesSelector || el.mozMatchesSelector || el.msMatchesSelector;

        // match start from parent
        el = el.parentElement;
        while (el && !matchesSelector.call(el, selector)) {
            if (!filter) {
                if (selector) {
                    if (matchesSelector.call(el, selector)) {
                        return result.push(el);
                    }
                } else {
                    result.push(el);
                }
            } else {
                if (matchesSelector.call(el, filter)) {
                    result.push(el);
                }
            }
            el = el.parentElement;
            if (e.isVariableDefined(el)) {
                if (matchesSelector.call(el, selector)) {
                    return el;
                }
            }

        }
        return result;
    },
    getNextSiblings: function (el, selector, filter) {
        let sibs = [];
        let nextElem = el.parentNode.firstChild;
        const matchesSelector = el.matches || el.webkitMatchesSelector || el.mozMatchesSelector || el.msMatchesSelector;
        do {
            if (nextElem.nodeType === 3) continue; // ignore text nodes
            if (nextElem === el) continue; // ignore elem of target
            if (nextElem === el.nextElementSibling) {
                if ((!filter || filter(el))) {
                    if (selector) {
                        if (matchesSelector.call(nextElem, selector)) {
                            return nextElem;
                        }
                    } else {
                        sibs.push(nextElem);
                    }
                    el = nextElem;

                }
            }
        } while (nextElem = nextElem.nextSibling)
        return sibs;
    },
    on: function (selectors, type, listener) {
        document.addEventListener("DOMContentLoaded", () => {
            if (!(selectors instanceof HTMLElement) && selectors !== null) {
                selectors = document.querySelector(selectors);
            }
            selectors.addEventListener(type, listener);
        });
    },
    onAll: function (selectors, type, listener) {
        document.addEventListener("DOMContentLoaded", () => {
            document.querySelectorAll(selectors).forEach((element) => {
                if (type.indexOf(',') > -1) {
                    let types = type.split(',');
                    types.forEach((type) => {
                        element.addEventListener(type, listener);
                    });
                } else {
                    element.addEventListener(type, listener);
                }


            });
        });
    },
    removeClass: function (selectors, className) {
        if (!(selectors instanceof HTMLElement) && selectors !== null) {
            selectors = document.querySelector(selectors);
        }
        if (e.isVariableDefined(selectors)) {
            selectors.removeClass(className);
        }
    },
    removeAllClass: function (selectors, className) {
        if (e.isVariableDefined(selectors) && (selectors instanceof HTMLElement)) {
            document.querySelectorAll(selectors).forEach((element) => {
                element.removeClass(className);
            });
        }

    },
    toggleClass: function (selectors, className) {
        if (!(selectors instanceof HTMLElement) && selectors !== null) {
            selectors = document.querySelector(selectors);
        }
        if (e.isVariableDefined(selectors)) {
            selectors.toggleClass(className);
        }
    },
    toggleAllClass: function (selectors, className) {
        if (e.isVariableDefined(selectors)  && (selectors instanceof HTMLElement)) {
            document.querySelectorAll(selectors).forEach((element) => {
                element.toggleClass(className);
            });
        }
    },
    addClass: function (selectors, className) {
        if (!(selectors instanceof HTMLElement) && selectors !== null) {
            selectors = document.querySelector(selectors);
        }
        if (e.isVariableDefined(selectors)) {
            selectors.addClass(className);
        }
    },
    select: function (selectors) {
        return document.querySelector(selectors);
    },
    selectAll: function (selectors) {
        return document.querySelectorAll(selectors);
    },

    // START: 01 Preloader
    preLoader: function () {
        window.onload = function () {
            var preloader = e.select('.preloader');
            if (e.isVariableDefined(preloader)) {
                preloader.className += ' animate__animated animate__fadeOut';
                setTimeout(function(){
                    preloader.style.display = 'none';
                }, 200);
            }
        };
    },
    // END: Preloader

    // START: 02 Mega Menu
    megaMenu: function () {
        e.onAll('.dropdown-menu a.dropdown-item.dropdown-toggle', 'click', function (event) {
            var element = this;
            event.preventDefault();
            event.stopImmediatePropagation();
            if (e.isVariableDefined(element.nextElementSibling) && !element.nextElementSibling.classList.contains("show")) {
                const parents = e.getParents(element, '.dropdown-menu');
                e.removeClass(parents.querySelector('.show'), "show");
                if(e.isVariableDefined(parents.querySelector('.dropdown-opened'))){
                    e.removeClass(parents.querySelector('.dropdown-opened'), "dropdown-opened");
                }
            }
            var $subMenu = e.getNextSiblings(element, ".dropdown-menu");
            e.toggleClass($subMenu, "show");
            $subMenu.previousElementSibling.toggleClass('dropdown-opened');
            var parents = e.getParents(element, 'li.nav-item.dropdown.show');
            if (e.isVariableDefined(parents) && parents.length > 0) {
                e.on(parents, 'hidden.bs.dropdown', function (event) {
                    e.removeAllClass('.dropdown-submenu .show');
                });
            }
        });
    },
    // END: Mega Menu

    // START: 03 Sticky Header
    stickyHeader: function () {
        var stickyNav = e.select('.navbar-sticky');
        if (e.isVariableDefined(stickyNav)) {
            var stickyHeight = stickyNav.offsetHeight;
            stickyNav.insertAdjacentHTML('afterend', '<div id="sticky-space"></div>');
            var stickySpace = e.select('#sticky-space');
            if (e.isVariableDefined(stickySpace)) {
                document.addEventListener('scroll', function (event) {
                    var scTop = window.pageYOffset || document.documentElement.scrollTop;
                    if (scTop >= 400) {
                        stickySpace.addClass('active');
                        e.select("#sticky-space.active").style.height = stickyHeight + 'px';
                        stickyNav.addClass('navbar-sticky-on');
                    } else {
                        stickySpace.removeClass('active');
                        stickySpace.style.height = '0px';
                        stickyNav.removeClass("navbar-sticky-on");
                    }
                });
            }
        }
    },
    // END: Sticky Header

    // START: 04 Tiny Slider
    tinySlider: function () {
        var $carousel = e.select('.tiny-slider-inner');
        if (e.isVariableDefined($carousel)) {
          var tnsCarousel = e.selectAll('.tiny-slider-inner');
          tnsCarousel.forEach(slider => {
              var slider1 = slider;
              var sliderMode = slider1.getAttribute('data-mode') ? slider1.getAttribute('data-mode') : 'carousel';
              var sliderAxis = slider1.getAttribute('data-axis') ? slider1.getAttribute('data-axis') : 'horizontal';
              var sliderSpace = slider1.getAttribute('data-gutter') ? slider1.getAttribute('data-gutter') : 30;
              var sliderEdge = slider1.getAttribute('data-edge') ? slider1.getAttribute('data-edge') : 0;

              var sliderItems = slider1.getAttribute('data-items') ? slider1.getAttribute('data-items') : 4; //option: number (items in all device)
              var sliderItemsXl = slider1.getAttribute('data-items-xl') ? slider1.getAttribute('data-items-xl') : Number(sliderItems); //option: number (items in 1200 to end )
              var sliderItemsLg = slider1.getAttribute('data-items-lg') ? slider1.getAttribute('data-items-lg') : Number(sliderItemsXl); //option: number (items in 992 to 1199 )
              var sliderItemsMd = slider1.getAttribute('data-items-md') ? slider1.getAttribute('data-items-md') : Number(sliderItemsLg); //option: number (items in 768 to 991 )
              var sliderItemsSm = slider1.getAttribute('data-items-sm') ? slider1.getAttribute('data-items-sm') : Number(sliderItemsMd); //option: number (items in 576 to 767 )
              var sliderItemsXs = slider1.getAttribute('data-items-xs') ? slider1.getAttribute('data-items-xs') : Number(sliderItemsSm); //option: number (items in start to 575 )

              var sliderSpeed = slider1.getAttribute('data-speed') ? slider1.getAttribute('data-speed') : 500;
              var sliderautoWidth = slider1.getAttribute('data-autowidth') === 'true'; //option: true or false
              var sliderArrow = slider1.getAttribute('data-arrow') !== 'false'; //option: true or false
              var sliderDots = slider1.getAttribute('data-dots') !== 'false'; //option: true or false

              var sliderAutoPlay = slider1.getAttribute('data-autoplay') !== 'false'; //option: true or false
              var sliderAutoPlayTime = slider1.getAttribute('data-autoplaytime') ? slider1.getAttribute('data-autoplaytime') : 4000;
              var sliderHoverPause = slider1.getAttribute('data-hoverpause') === 'true'; //option: true or false
              var sliderLoop = slider1.getAttribute('data-loop') !== 'false'; //option: true or false
              var sliderRewind = slider1.getAttribute('data-rewind') === 'true'; //option: true or false
              var sliderAutoHeight = slider1.getAttribute('data-autoheight') === 'true'; //option: true or false
              var sliderfixedWidth = slider1.getAttribute('data-fixedwidth') === 'true'; //option: true or false
              var sliderTouch = slider1.getAttribute('data-touch') !== 'false'; //option: true or false
              var sliderDrag = slider1.getAttribute('data-drag') !== 'false'; //option: true or false
              // Check if document DIR is RTL
              var ifRtl = document.getElementsByTagName("html")[0].getAttribute("dir");
              var sliderDirection;
              if (ifRtl === 'rtl') {
                  sliderDirection = 'rtl';
              }

              var tnsSlider = tns({
                  container: slider,
                  mode: sliderMode,
                  axis: sliderAxis,
                  gutter: sliderSpace,
                  edgePadding: sliderEdge,
                  speed: sliderSpeed,
                  autoWidth: sliderautoWidth,
                  controls: sliderArrow,
                  nav: sliderDots,
                  autoplay: sliderAutoPlay,
                  autoplayTimeout: sliderAutoPlayTime,
                  autoplayHoverPause: sliderHoverPause,
                  autoplayButton: false,
                  autoplayButtonOutput: false,
                  controlsPosition: top,
                  navPosition: top,
                  autoplayPosition: top,
                  controlsText: [
                      '<i class="fas fa-chevron-left"></i>',
                      '<i class="fas fa-chevron-right"></i>'
                  ],
                  loop: sliderLoop,
                  rewind: sliderRewind,
                  autoHeight: sliderAutoHeight,
                  fixedWidth: sliderfixedWidth,
                  touch: sliderTouch,
                  mouseDrag: sliderDrag,
                  arrowKeys: true,
                  items: sliderItems,
                  textDirection: sliderDirection,
                  responsive: {
                      0: {
                          items: Number(sliderItemsXs)
                      },
                      576: {
                          items: Number(sliderItemsSm)
                      },
                      768: {
                          items: Number(sliderItemsMd)
                      },
                      992: {
                          items: Number(sliderItemsLg)
                      },
                      1200: {
                          items: Number(sliderItemsXl)
                      }
                  }
              });
          });
        }
    },
    // END: Tiny Slider

    // START: 05 Parallax Background
    parallaxBG: function () {
        var parBG = e.select('.bg-parallax');
        if (e.isVariableDefined(parBG)) {
            jarallax(e.selectAll('.bg-parallax'), {
                speed: 0.6
            });
        }
    },
    // END: Parallax Background

    // START: 06 AOS Animation
    aosFunc: function () {
        var aos = e.select('.aos');
        if (e.isVariableDefined(aos)) {
            AOS.init({
                duration: 500,
                easing: 'ease-out-quart',
                once: true
            });
        }
    },
    // END: AOS Animation

    // START: 07 Sticky Bar
    stickyBar: function () {
        var stickyBar = e.select('[data-sticky]');
        if (e.isVariableDefined(stickyBar)) {
            var sticky = new Sticky('[data-sticky]');
        }
    },
    // END: Sticky Bar

    // START: 08 Form Validation
    formValidation: function () {
        var formV = e.select('.needs-validation');
        if (e.isVariableDefined(formV)) {
            window.addEventListener('load', function() {
              // Fetch all the forms we want to apply custom Bootstrap validation styles to
              var forms = document.querySelectorAll('.needs-validation')

              // Loop over them and prevent submission
              Array.prototype.slice.call(forms)
                .forEach(function (form) {
                  form.addEventListener('submit', function (event) {
                    if (!form.checkValidity()) {
                      event.preventDefault()
                      event.stopPropagation()
                    }

                    form.classList.add('was-validated')
                  }, false)
                })
            }, false);
        }
    },
    // END: Form Validation

    // START: 09 Tooltip
    // Enable tooltips everywhere via data-toggle attribute
    toolTipFunc: function () {
        var tooltipTriggerList = [].slice.call(e.selectAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
          return new bootstrap.Tooltip(tooltipTriggerEl)
        })
    },
    // END: Tooltip

    // START: 10 Popover
    // Enable popover everywhere via data-toggle attribute
    popOverFunc: function () {
        var popoverTriggerList = [].slice.call(e.selectAll('[data-bs-toggle="popover"]'))
        var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
          return new bootstrap.Popover(popoverTriggerEl)
        })
    },
    // END: Popover

    // START: 11 Back to Top
    backTotop: function () {
        var scrollpos = window.scrollY;
        var backBtn = e.select('.back-top');
        if (e.isVariableDefined(backBtn)) {
            var add_class_on_scroll = () => backBtn.addClass("btn-show");
            var remove_class_on_scroll = () => backBtn.removeClass("btn-show");

            window.addEventListener('scroll', function () {
                scrollpos = window.scrollY;
                if (scrollpos >= 500) {
                    add_class_on_scroll()
                } else {
                    remove_class_on_scroll()
                }
            });

            backBtn.addEventListener('click', () => window.scrollTo({
                top: 0,
                behavior: 'smooth',
            }));
        }
    },
    // END: Back to Top

    // START: 12 GLightbox
    lightBox: function () {
        var light = e.select('[data-glightbox]');
        if (e.isVariableDefined(light)) {
            var lb = GLightbox({
                selector: '*[data-glightbox]',
                openEffect: 'fade',
                closeEffect: 'fade'
            });
        }
    },
    // END: GLightbox

    // START: 13 Typing Text Animation
    typeText: function () {
        var t = e.select('.typed');
        if (e.isVariableDefined(t)) {
            var type = e.selectAll('.typed');
            type.forEach(el => {
                var strings = el.getAttribute('data-type-text');
                var split_strings = strings.split("&&");
                var typespeed = el.getAttribute('data-speed') ? el.getAttribute('data-speed') : 200;
                var typeBackSpeed = el.getAttribute('data-back-speed') ? el.getAttribute('data-back-speed') : 50;

                ityped.init(el, {
                    strings: split_strings,
                    showCursor: true,
                    typeSpeed: typespeed,
                    backSpeed: typeBackSpeed
                });
            });
        }
    },
    // END: Typing Text Animation

    // START: 14 Isotope
    enableIsotope: function () {
        var isGridItem = e.select('.grid-item');
        if (e.isVariableDefined(isGridItem)) {

            // Code only for normal Grid
            var onlyGrid = e.select('[data-isotope]');
            if (e.isVariableDefined(onlyGrid)) {
                var allGrid = e.selectAll("[data-isotope]");
                allGrid.forEach(gridItem => {
                    var gridItemData = gridItem.getAttribute('data-isotope');
                    var gridItemDataObj = JSON.parse(gridItemData);
                    var iso = new Isotope(gridItem, {
                        itemSelector: '.grid-item',
                        layoutMode: gridItemDataObj.layoutMode
                    });

                    imagesLoaded(gridItem).on('progress', function () {
                        // layout Isotope after each image loads
                        iso.layout();
                    });
                });
            }

            // Code only for normal Grid
            var onlyGridFilter = e.select('.grid-menu');
            if (e.isVariableDefined(onlyGridFilter)) {
                var filterMenu = e.selectAll('.grid-menu');
                filterMenu.forEach(menu => {
                    var filterContainer = menu.getAttribute('data-target');
                    var a = menu.dataset.target;
                    var b = e.select(a);
                    var filterContainerItemData = b.getAttribute('data-isotope');
                    var filterContainerItemDataObj = JSON.parse(filterContainerItemData);
                    var filter = new Isotope(filterContainer, {
                        itemSelector: '.grid-item',
                        transitionDuration: '0.7s',
                        layoutMode: filterContainerItemDataObj.layoutMode
                    });

                    var menuItems = menu.querySelectorAll('li a');
                    menuItems.forEach(menuItem => {
                        menuItem.addEventListener('click', function (event) {
                            var filterValue = menuItem.getAttribute('data-filter');
                            filter.arrange({filter: filterValue});
                            menuItems.forEach((control) => control.removeClass('active'));
                            menuItem.addClass('active');
                        });
                    });

                    imagesLoaded(filterContainer).on('progress', function () {
                        filter.layout();
                    });
                });
            }
        }
    },
    // END: Isotope

    // START: 15 wave
    waveCanvas: function () {
    	var canvas = document.getElementById('waveCanvas')
        if (e.isVariableDefined(canvas)) {
            var ctx = canvas.getContext('2d')
            canvas.width = canvas.parentNode.offsetWidth
            canvas.height = canvas.parentNode.offsetHeight

            let step = 0
            const lines = 4

            function loop() {
                ctx.clearRect(0, 0, canvas.width, canvas.height)
                step++
                for (let i = 0; i < lines; i++) {
                    ctx.fillStyle = 'rgba(255,255,255,.8)'
                    var angle = (step + i * 180 / lines) * Math.PI / 180
                    var deltaHeight = Math.sin(angle) * 90
                    var deltaHeightRight = Math.cos(angle) * 50
                    ctx.beginPath()
                    ctx.moveTo(0, canvas.height / 2 + deltaHeight)
                    ctx.bezierCurveTo(canvas.width / 2, canvas.height / 2 + deltaHeight - 50, canvas.width / 2, canvas.height / 2 + deltaHeightRight - 50, canvas.width, canvas.height / 2 + deltaHeightRight)
                    ctx.lineTo(canvas.width, canvas.height)
                    ctx.lineTo(0, canvas.height)
                    ctx.lineTo(0, canvas.height / 2 + deltaHeight)
                    ctx.closePath()
                    ctx.fill()
                }

                requestAnimationFrame(loop)
            }
		    loop()
        }
    },
    // END: wave

};

// Data_Table
$(document).ready(function() {

  var dataSet = [
    [1 , "تايجر نيكسون" , "مهندس النظام" , "إدنبرة" , "5421" , "2011/04/25" , "320800 دولار"],
    [2 , "غاريت وينترز" , "محاسب" , "طوكيو" , "8422" , "2011/07/25" , "170750 دولارا"],
    [3 , "أشتون كوكس" , "المؤلف الفني المبتدئ" , "سان فرانسيسكو" , "1562" , "2009/01/12" , "86000 دولار"],
    [4 , "سيدريك كيلي" , "مطور جافا سكريبت أول" , "إدنبرة" , "6224" , "2012/03/29" , "433,060 دولارا"],
    [5 , "إيري ساتو" , "محاسب" , "طوكيو" , "5407" , "2008/11/28" , "162700 دولار"],
    [6 , "بريل ويليامسون" , "أخصائي الاندماج" , "نيويورك" , "4804" , "2012/12/02" , "372000 دولار"],
    [7 , "هيرود تشاندلر" , "مساعد مبيعات" , "سان فرانسيسكو" , "9608" , "2012/08/06" , "137500 دولار"],
    [8 , "رونا ديفيدسون" , "أخصائي الاندماج" , "طوكيو" , "6200" , "2010/10/14" , "327900 دولار"],
    [9 , "كولين هيرست" , "مطور جافا سكريبت" , "سان فرانسيسكو" , "2360" , "2009/09/15" , "205500 دولار"],
    [10 , "سونيا فروست" , "مهندس برمجيات" , "إدنبرة" , "1667" , "2008/12/13" , "103600 دولار"],
    [11 , "جينا جاينز" , "مدير المكتب" , "لندن" , "3814" , "2008/12/19" , "90560 دولارا"],
    [12 , "كوين فلين" , "دعم الرصاص" , "إدنبرة" , "9497" , "2013/03/03" , "342000 دولار"],
    [13 , "شارد مارشال" , "المدير الإقليمي" , "سان فرانسيسكو" , "6741" , "2008/10/16" ,"470600 دولار"],
    [14 ,"هالي كينيدي" , "مصمم تسويق أول" , "لندن" , "3597" , "2012/12/18" , "313500 دولار"],
    [15 , "تاتيانا فيتزباتريك" , "المدير الإقليمي" , "لندن" , "1965" , "2010/03/17" , "385750 دولارا"] ,
    [16 , "مايكل سيلفا" , "مصمم تسويق" , "لندن" , "1581" , "2012/11/27" , "198500 دولار"],
    [17 , "بول بيرد" , "المدير المالي (CFO)" , "نيويورك" , "3059" , "2010/06/09" , "725000 دولار"],
    [18 , "غلوريا ليتل" , "مسؤول الأنظمة" , "نيويورك" , "1721" , "2009/04/10" , "237500 دولار"],
    [19 , "برادلي جرير" , "مهندس برمجيات" , "لندن" , "2558" , "2012/10/13" , "132000 دولار"],
    [20 , "داي ريوس" , "قيادة الموظفين" , "إدنبرة" , "2290" , "2012/09/26" , "217500 دولار"],
    [21 , "جينيت كالدويل" , "قائد التنمية" , "نيويورك" , "1937" , "2011/09/03" , "345000 دولار"],
    [22 , "يوري بيري" , "كبير مسؤولي التسويق (CMO)" , "نيويورك" , "6154" , "2009/06/25" , "675000 دولار"],
    [23 , "قيصر فانس" , "دعم ما قبل البيع" , "نيويورك" , "8330" , "2011/12/12" , "106450 دولارا"],
    [24 , "دوريس وايلدر" , "مساعد مبيعات" , "سيدني" , "3023" , "2010/09/20" , "85600 دولار"],
    [25 , "أنجليكا راموس" , "الرئيس التنفيذي (CEO)" , "لندن" , "5797" , "2009/10/09" , "1,200,000 دولار"],
    [26 , "جافين جويس" , "مطور" , "إدنبرة" , "8822" , "2010/12/22" , "92575 دولارا"],
    [27, "جينيفر تشانغ", "المدير الإقليمي", "سنغافورة", "9239", "2010/11/14", "357,650 دولار"],
    [28 , "بريندن فاغنر" , "مهندس برمجيات" , "سان فرانسيسكو" , "1314" , "2011/06/07" , "206850 دولارا"],
    [29 , "فيونا جرين" , "الرئيس التنفيذي للعمليات (COO)" , "سان فرانسيسكو" , "2947" , "2010/03/11" , "850,000 دولار"],
    [30, "شو إيتو", "التسويق الإقليمي", "طوكيو", "8899", "2011/08/14", "163000 دولار"],
    [31 , "ميشيل هاوس" , "أخصائي الاندماج" , "سيدني" , "2769" , "2011/06/02" , "95400 دولار"],
    [32 , "سوكي بيركس" , "مطور" , "لندن" , "6832" , "2009/10/22" , "114500 دولار"],
    [33 , "بريسكوت بارتليت" , "المؤلف الفني" , "لندن" , "3606" , "2011/05/07" , "145000 دولار"],
    [34 , "جافين كورتيز" , "قائد الفريق" , "سان فرانسيسكو" , "2860" , "2008/10/26" , "235500 دولار"],
    [35 , "مارتينا ماكراي" , "دعم ما بعد البيع" , "إدنبرة" , "8240" , "2011/03/09" , "324050 دولارا"],
    [36 , "الوحدة بتلر" , "مصمم التسويق" , "سان فرانسيسكو" , "5384" , "2009/12/09" , "85675 دولارا"]
  ];

  var columnDefs = [{
    title: "Id",
    type: "readonly"
  }, {
    title: "الاسم",
    type: "text"
  }, {
    title: "المهنة",
    type: "textarea"
  }, {
    title: "المدينة"
    //no type = text
  }, {
    title: "تحويلة رقم",
    type: "text"
  }, {
    title: "تاريخ البدء",
    type: "readonly"
  }, {
    title: "الراتب",
    type: "text"
  }];

  var myTable;

  myTable = $('#example').DataTable({
    "sPaginationType": "full_numbers",
    data: dataSet,
    columns: columnDefs,
    dom: 'Bfrtip',        // Needs button container
    select: 'single',
    responsive: true,
    altEditor: true,     // Enable altEditor
    buttons: [
        {
        text: 'أضف',
        name: 'add'        // do not change name
        },
        {
        extend: 'selected', // Bind to Selected row
        text: 'تعديل',
        name: 'edit'        // do not change name
        },
        {
        extend: 'selected', // Bind to Selected row
        text: 'حذف',
        name: 'delete'      // do not change name
        }
    ]
  });


});
e.init();
