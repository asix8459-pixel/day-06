/* README
   File: assets/js/landing.js
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

    // Hero CTAs hook to existing modal triggers if present
    var ctaLogin = document.getElementById('ctaLogin');
    var ctaRegister = document.getElementById('ctaRegister');

    // Overlay controls (vanilla JS)
    function openOverlay(id, src){
        var overlay = document.getElementById(id);
        if (!overlay) return;
        overlay.style.display = 'flex';
        requestAnimationFrame(function(){ overlay.classList.add('open'); });
        if (src) {
            var frame = overlay.querySelector('iframe');
            if (frame && frame.getAttribute('src') !== src) frame.setAttribute('src', src);
        }
        document.body.style.overflow = 'hidden';
        document.body.classList.add('modal-blur');
    }
    function closeOverlay(id){
        var overlay = document.getElementById(id);
        if (!overlay) return;
        overlay.classList.remove('open');
        setTimeout(function(){ overlay.style.display = 'none'; }, 220);
        document.body.style.overflow = 'auto';
        document.body.classList.remove('modal-blur');
    }
    function bindOpen(btnId, overlayId, src){
        var el = document.getElementById(btnId);
        if (el) el.addEventListener('click', function(e){ e.preventDefault(); openOverlay(overlayId, src); });
    }
    bindOpen('openLogin', 'loginOverlay', 'login.php');
    bindOpen('openRegister', 'registerOverlay', 'register.php');
    bindOpen('openLoginFooter', 'loginOverlay', 'login.php');
    bindOpen('openRegisterFooter', 'registerOverlay', 'register.php');
    bindOpen('openRegisterBottom', 'registerOverlay', 'register.php');
    if (ctaLogin) ctaLogin.addEventListener('click', function(){ openOverlay('loginOverlay', 'login.php'); });
    if (ctaRegister) ctaRegister.addEventListener('click', function(){ openOverlay('registerOverlay', 'register.php'); });

    // Backdrop and ESC close
    Array.prototype.forEach.call(document.querySelectorAll('.overlay-backdrop'), function(ov){
        ov.addEventListener('click', function(e){ if (e.target === ov) closeOverlay(ov.id); });
    });
    Array.prototype.forEach.call(document.querySelectorAll('[data-close]'), function(btn){
        btn.addEventListener('click', function(){ closeOverlay(btn.getAttribute('data-close')); });
    });
    Array.prototype.forEach.call(document.querySelectorAll('.overlay-modal'), function(modal){
        modal.addEventListener('click', function(e){ e.stopPropagation(); });
    });
    document.addEventListener('keydown', function(e){ if (e.key === 'Escape'){ closeOverlay('loginOverlay'); closeOverlay('registerOverlay'); } });

    // Redirect top-level when iframe logs in
    var dashboards = [
        'admin_dashboard.php','student_dashboard.php','faculty_dashboard.php','scholarship_admin_dashboard.php','guidance_admin_dashboard.php','admin_dormitory_dashboard.php','registrar_dashboard.php'
    ];
    var loginFrame = document.getElementById('loginFrame');
    if (loginFrame) loginFrame.addEventListener('load', function(){
        try {
            var href = this.contentWindow.location.href;
            for (var i=0;i<dashboards.length;i++){
                if (href.indexOf(dashboards[i]) !== -1){ window.location.href = href; return; }
            }
        } catch(err) {}
    });

    // postMessage from registration -> open login
    window.addEventListener('message', function(ev){
        var data = ev.data || {};
        if (data.type === 'openLogin'){
            closeOverlay('registerOverlay');
            openOverlay('loginOverlay', 'login.php');
            var prefill = data.payload && data.payload.prefill;
            if (prefill && loginFrame){
                var tryPrefill = function(){
                    try{
                        var doc = loginFrame.contentWindow.document;
                        var input = doc.querySelector('input[name="user_id"]');
                        if (input){ input.value = prefill; return true; }
                    }catch(e){}
                    return false;
                };
                if (!tryPrefill()) loginFrame.addEventListener('load', tryPrefill, { once:true });
            }
        }
    });
})();

