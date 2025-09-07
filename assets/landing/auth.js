/* README
   File: assets/landing/auth.js
   Purpose: Controls split-layout login/register overlay inside landing_page.php
   Usage: Include after landing.js. Exposes AuthUI.init().
*/

var AuthUI=(function(){
  var overlay, card, paneLogin, paneRegister;
  function qs(id){ return document.getElementById(id); }
  function open(which){
    if(!overlay) return; overlay.style.display='flex';
    requestAnimationFrame(function(){ overlay.classList.add('open'); });
    document.body.style.overflow='hidden'; document.body.classList.add('modal-blur');
    switchPane(which||'login');
  }
  function close(){ if(!overlay) return; overlay.classList.remove('open'); setTimeout(function(){ overlay.style.display='none'; }, 220); document.body.style.overflow='auto'; document.body.classList.remove('modal-blur'); }
  function switchPane(which){
    if(which==='login'){ paneLogin.classList.remove('hidden'); paneLogin.classList.add('visible'); paneRegister.classList.remove('visible'); paneRegister.classList.add('hidden'); }
    else { paneRegister.classList.remove('hidden'); paneRegister.classList.add('visible'); paneLogin.classList.remove('visible'); paneLogin.classList.add('hidden'); }
  }
  function init(){
    overlay = qs('authOverlay');
    card = qs('authCard');
    paneLogin = qs('authPaneLogin');
    paneRegister = qs('authPaneRegister');
    // Bind triggers
    var btnsOpenLogin=['openLogin','openLoginFooter','ctaLogin'];
    var btnsOpenReg=['openRegister','openRegisterFooter','openRegisterBottom','ctaRegister'];
    btnsOpenLogin.forEach(function(id){ var el=qs(id); if(el) el.addEventListener('click', function(e){ e.preventDefault(); open('login'); }); });
    btnsOpenReg.forEach(function(id){ var el=qs(id); if(el) el.addEventListener('click', function(e){ e.preventDefault(); open('register'); }); });
    // Close handlers
    var closeBtn = qs('authClose'); if (closeBtn) closeBtn.addEventListener('click', close);
    overlay.addEventListener('click', function(e){ if (e.target===overlay) close(); });
    document.addEventListener('keydown', function(e){ if (e.key==='Escape') close(); });
    // Switch links
    var toRegister = qs('toRegister'); if (toRegister) toRegister.addEventListener('click', function(e){ e.preventDefault(); switchPane('register'); });
    var toLogin = qs('toLogin'); if (toLogin) toLogin.addEventListener('click', function(e){ e.preventDefault(); switchPane('login'); });

    // Intercept lightweight register to full form page
    var regForm = qs('registerLite');
    if (regForm) {
      regForm.addEventListener('submit', function(e){
        e.preventDefault(); window.location.href = 'register.php';
      });
    }

    // Show/Hide password toggles
    Array.prototype.forEach.call(document.querySelectorAll('[data-eye]'), function(btn){
      btn.addEventListener('click', function(){
        var target = document.getElementById(btn.getAttribute('data-eye'));
        if (!target) return;
        target.type = target.type === 'password' ? 'text' : 'password';
        btn.classList.toggle('fa-eye-slash');
      });
    });

    // Ripple effect on buttons
    Array.prototype.forEach.call(document.querySelectorAll('.auth-btn, .auth-btn-alt'), function(b){
      b.addEventListener('click', function(e){
        var circle = document.createElement('span');
        var d = Math.max(b.clientWidth, b.clientHeight);
        circle.style.width = circle.style.height = d + 'px';
        circle.style.left = (e.clientX - b.getBoundingClientRect().left - d/2) + 'px';
        circle.style.top = (e.clientY - b.getBoundingClientRect().top - d/2) + 'px';
        circle.classList.add('ripple');
        b.appendChild(circle);
        setTimeout(function(){ circle.remove(); }, 600);
      });
    });
    // Focus trap inside overlay
    try{
      var focusable = overlay.querySelectorAll('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])');
      var first = focusable[0];
      var last = focusable[focusable.length - 1];
      if (first) first.focus();
      overlay.addEventListener('keydown', function(e){
        if (e.key !== 'Tab') return;
        if (e.shiftKey){
          if (document.activeElement === first){ e.preventDefault(); last.focus(); }
        } else {
          if (document.activeElement === last){ e.preventDefault(); first.focus(); }
        }
      });
    }catch(e){}
  }
  return { init:init, open:open, close:close };
})();

document.addEventListener('DOMContentLoaded', function(){ if (document.getElementById('authOverlay')) AuthUI.init(); });

