(function() {
  "use strict";

  /**
   * Easy selector helper function
   */
  const select = (el, all = false) => {
    el = el.trim()
    if (all) {
      return [...document.querySelectorAll(el)]
    } else {
      return document.querySelector(el)
    }
  }

  /**
   * Easy event listener function
   */
  const on = (type, el, listener, all = false) => {
    if (all) {
      select(el, all).forEach(e => e.addEventListener(type, listener))
    } else {
      select(el, all).addEventListener(type, listener)
    }
  }

  /**
   * Easy on scroll event listener 
   */
  const onscroll = (el, listener) => {
    el.addEventListener('scroll', listener)
  }

  /**
   * Sidebar toggle
   */
  if (select('.toggle-sidebar-btn')) {
    on('click', '.toggle-sidebar-btn', function(e) {
      select('body').classList.toggle('toggle-sidebar')
    })
  }
  if (select('.toggle-sidebar')) {
    on('click', '.toggle-sidebar', function(e) {
      select('body').classList.toggle('toggle-sidebar')
    })
  }

  /**
   * Search bar toggle
   */
  if (select('.search-bar-toggle')) {
    on('click', '.search-bar-toggle', function(e) {
      select('.search-bar').classList.toggle('search-bar-show')
    })
  }

  /**
   * Navbar links active state on scroll
   */
  let navbarlinks = select('#navbar .scrollto', true)
  const navbarlinksActive = () => {
    let position = window.scrollY + 200
    navbarlinks.forEach(navbarlink => {
      if (!navbarlink.hash) return
      let section = select(navbarlink.hash)
      if (!section) return
      if (position >= section.offsetTop && position <= (section.offsetTop + section.offsetHeight)) {
        navbarlink.classList.add('active')
      } else {
        navbarlink.classList.remove('active')
      }
    })
  }
  window.addEventListener('load', navbarlinksActive)
  onscroll(document, navbarlinksActive)

  /**
   * Toggle .header-scrolled class to #header when page is scrolled
   */
  let selectHeader = select('#header')
  if (selectHeader) {
    const headerScrolled = () => {
      if (window.scrollY > 100) {
        selectHeader.classList.add('header-scrolled')
      } else {
        selectHeader.classList.remove('header-scrolled')
      }
    }
    window.addEventListener('load', headerScrolled)
    onscroll(document, headerScrolled)
  }

  /**
   * Back to top button
   */
  let backtotop = select('.back-to-top')
  if (backtotop) {
    const toggleBacktotop = () => {
      if (window.scrollY > 100) {
        backtotop.classList.add('active')
      } else {
        backtotop.classList.remove('active')
      }
    }
    window.addEventListener('load', toggleBacktotop)
    onscroll(document, toggleBacktotop)
  }

  /**
   * Initiate tooltips
   */
  var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
  var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl)
  })

  /**
   * Initiate Bootstrap validation check
   */
  var needsValidation = document.querySelectorAll('.needs-validation')

  Array.prototype.slice.call(needsValidation)
    .forEach(function(form) {
      form.addEventListener('submit', function(event) {
        if (!form.checkValidity()) {
          event.preventDefault()
          event.stopPropagation()
        }

        form.classList.add('was-validated')
      }, false)
    })

})();

  /**
   * Metamask style Jazzicon
   */

   //a hash value based on the username could be used as the seed value
var hash = function(str) {
        var hash = 0, i, chr;
        if (str.length === 0) return hash;
        for (i = 0; i < str.length; i++) {
                chr = str.charCodeAt(i);
                hash= ((hash << 5) - hash) + chr;
                hash |= 0; // Convert to 32bit integer
        }
        return hash;
};

function getJazzicon(seed, diameter) {
        diameter = diameter || 36;
        seed = seed || Math.random()*Number.MAX_SAFE_INTEGER;
        let colors = [
                '#01888C', // teal
                '#FC7500', // bright orange
                '#034F5D', // dark teal
                '#F73F01', // orangered
                '#FC1960', // magenta
                '#C7144C', // raspberry
                '#F3C100', // goldenrod
                '#1598F2', // lightning blue
                '#2465E1', // sail blue
                '#F19E02' // gold
        ];

function mulberry32(a) {
        return function() {
                var t = a += 0x6D2B79F5;
                t = Math.imul(t ^ t >>> 15, t | 1);
                t ^= t + Math.imul(t ^ t >>> 7, t | 61);
                return ((t ^ t >>> 14) >>> 0) / 4294967296;
        }
}
let random = mulberry32(seed);

        let genColor = (colors) => {
                const idx = Math.floor(colors.length * random());
                const color = colors.splice(idx, 1)[0];
                return color;
        }

        function getRectagnle(remainingColors, total, i) {
                const center = diameter / 2;
                const firstRot = random();
                const angle = Math.PI * 2 * firstRot;
                const velocity = diameter / total * random() + (i * diameter / total);
                const tx = (Math.cos(angle) * velocity);
                const ty = (Math.sin(angle) * velocity);
                const translate = 'translate(' + tx + ' ' +ty + ')';

                const secondRot = random();
                const rot = (firstRot * 360) + secondRot * 180;
                const rotate = 'rotate(' + rot.toFixed(1) + ' ' + center + ' ' + center + ')';
                const transform = translate + ' ' + rotate;
                const fill = genColor(remainingColors);

                return `<rect x="0" y="0" width="${diameter}" height="${diameter}" transform="${transform}" fill="${fill}"></rect>`;
        }

        var jazzicon = `<div style="border-radius: ${diameter/2}px; overflow: hidden;width: ${diameter}px;height: ${diameter}px;display: inline-block;background: ${genColor(colors)};">
                <svg x="0" y="0" width="${diameter}" height="${diameter}">
                        ${getRectagnle(colors, 3, 0)}
                        ${getRectagnle(colors, 3, 1)}
                        ${getRectagnle(colors, 3, 2)}
                </svg>
        </div>`;
        return jazzicon;
}
function sleep(ms) {
  return new Promise(resolve => setTimeout(resolve, ms));
}

function updateSelector(selector, content) {
	$(selector).html(content);
}

function updateAndShowSelector(selector, content) {
	$(selector).html(content);
	$(selector).removeClass("d-none").show();
}
function showSelectors() {
	for (var i=0; i<arguments.length; i++){
		$(arguments[i]).removeClass("d-none").show();
	}
}
function hideSelectors() {
	for (var i=0; i<arguments.length; i++){
		$(arguments[i]).addClass("d-none").hide();
	}
}
function clearAndHideSelectors() {
	for (var i=0; i<arguments.length; i++){
		$(arguments[i]).html('');
		//document.getElementById('zwiURLToUpload').reset;
		$(arguments[i]).addClass("d-none").hide();
	}
}
function clearSelectors() {
	for (var i=0; i<arguments.length; i++){
		$(arguments[i]).html('');
	}
}
function disableButtons() {
	for (var i=0; i<arguments.length; i++){
		$(arguments[i]).attr('disabled','disabled');
		$(arguments[i]).addClass("button-disabled");
	}
}
function enableButtons() {
	for (var i=0; i<arguments.length; i++){
		$(arguments[i]).removeAttr('disabled');
		$(arguments[i]).removeClass("button-disabled");
	}
}