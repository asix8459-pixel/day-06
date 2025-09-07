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
        });
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
})();

