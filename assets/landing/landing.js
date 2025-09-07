/* README
   File: assets/landing/landing.js
   Purpose: Vanilla JS for landing page interactions (dark mode toggle, reveals, CTA, modals hooks)
   Usage: Include this script at the end of landing_page.php body.
*/

(function(){
    // Dark mode toggle with localStorage
    var modeBtn = document.getElementById('modeToggle');
    var stored = localStorage.getItem('neust_theme');
    if (stored === 'dark') document.body.classList.add('dark');
    if (modeBtn) {
        modeBtn.addEventListener('click', function(){
            document.body.classList.toggle('dark');
            localStorage.setItem('neust_theme', document.body.classList.contains('dark') ? 'dark' : 'light');
            // aria and icon swap
            var pressed = document.body.classList.contains('dark');
            modeBtn.setAttribute('aria-pressed', pressed ? 'true' : 'false');
            var icon = modeBtn.querySelector('i');
            if (icon){ icon.className = pressed ? 'fa-regular fa-sun' : 'fa-regular fa-moon'; }
        });
        // init aria/icon
        var initPressed = document.body.classList.contains('dark');
        modeBtn.setAttribute('aria-pressed', initPressed ? 'true' : 'false');
        var icon = modeBtn.querySelector('i');
        if (icon){ icon.className = initPressed ? 'fa-regular fa-sun' : 'fa-regular fa-moon'; }
    }

    // Reveal on scroll
    var els = Array.prototype.slice.call(document.querySelectorAll('.reveal'));
    function onScroll(){
        var wh = window.innerHeight;
        for (var i=0;i<els.length;i++){
            var r = els[i].getBoundingClientRect();
            if (r.top < wh - 60) els[i].classList.add('in');
        }
    }
    onScroll();
    document.addEventListener('scroll', onScroll, { passive:true });

    // Reduced motion: remove reveal if user prefers reduced motion
    try{
        if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches){
            document.querySelectorAll('.blob').forEach(function(b){ b.style.animation = 'none'; b.style.filter = 'blur(20px)'; });
            document.querySelectorAll('.reveal').forEach(function(el){ el.classList.add('in'); });
        }
    }catch(e){}
})();

