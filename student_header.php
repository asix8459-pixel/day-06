<?php if (defined('STUDENT_HEADER_INCLUDED')) { return; } define('STUDENT_HEADER_INCLUDED', true); ?>
<!-- Student Header Partial -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/student_theme.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<nav class="navbar">
	<div class="logo">NEUST Gabaldon</div>
	<div class="nav-container">
		<ul class="nav-links">
			<li><a href="student_dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
			<li><a href="student_announcement.php"><i class="fas fa-bullhorn"></i> Announcements</a></li>
			<li class="dropdown">
				<a href="#" aria-haspopup="true" aria-expanded="false"><i class="fas fa-list"></i> Services <span class="caret"><i class="fas fa-chevron-down"></i></span></a>
				<div class="dropdown-content">
					<div class="sub-dropdown">
						<a href="#" aria-haspopup="true" aria-expanded="false">🏠 Dormitory <span class="caret"><i class="fas fa-chevron-right"></i></span></a>
						<div class="sub-dropdown-content">
							<a href="rooms.php">🏠 Apply</a>
							<a href="check_applications_status.php">✅ Check Status</a>
							<a href="student_payments.php">💳 Dormitory Payments</a>
							<a href="dormitory_rules.php">📜 Rules</a>
						</div>
					</div>
					<div class="sub-dropdown">
						<a href="#" aria-haspopup="true" aria-expanded="false">🎓 Scholarship <span class="caret"><i class="fas fa-chevron-right"></i></span></a>
						<div class="sub-dropdown-content">
							<a href="scholarships.php">📝 Apply</a>
							<a href="track_applications.php">📊 Status</a>
							<a href="scholarship_resources.php">📚 Resources</a>
						</div>
					</div>
					<div class="sub-dropdown">
						<a href="#" aria-haspopup="true" aria-expanded="false">🗣️ Guidance <span class="caret"><i class="fas fa-chevron-right"></i></span></a>
						<div class="sub-dropdown-content">
							<a href="guidance_request.php">📅 Book Appointment</a>
							<a href="student_status_appointments.php">📋 Appointment Status</a>
							<a href="guidance_counseling.php">🗣️ Counseling</a>
							<a href="guidance_resources.php">📖 Resources</a>
						</div>
					</div>
					<div class="sub-dropdown">
						<a href="#" aria-haspopup="true" aria-expanded="false">⚖️ Grievance <span class="caret"><i class="fas fa-chevron-right"></i></span></a>
						<div class="sub-dropdown-content">
							<a href="grievance_filing.php">📢 File Complaint</a>
							<a href="grievance_appointment.php">📅 Set Appointment</a>
						</div>
					</div>
				</div>
			</li>
		</ul>
		<div class="user-profile">
			<button class="theme-toggle" id="themeToggle" title="Toggle theme">
				<i class="fas fa-moon"></i>
			</button>
			<div class="user-dropdown">
				<div class="user-icon" onclick="toggleUserDropdown()">
					<i class="fas fa-user"></i>
				</div>
				<div class="user-dropdown-content">
					<a href="student_profile.php"><i class="fas fa-user-circle"></i> My Profile</a>
					<a href="student_dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
					<a href="student_announcement.php"><i class="fas fa-bell"></i> Notifications</a>
					<a href="login.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
				</div>
			</div>
		</div>
	</div>
</nav>
<script>
// Theme initialization and toggle
(function(){
	const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
	const savedTheme = localStorage.getItem('theme');
	if (savedTheme === 'dark' || (!savedTheme && prefersDark)) document.body.classList.add('theme-dark');
	const themeToggleBtn = document.getElementById('themeToggle');
	const setIcon = () => {
		const isDark = document.body.classList.contains('theme-dark');
		themeToggleBtn.innerHTML = isDark ? '<i class="fas fa-sun"></i>' : '<i class="fas fa-moon"></i>';
	};
	if (themeToggleBtn) {
		setIcon();
		themeToggleBtn.addEventListener('click', function(){
			document.body.classList.toggle('theme-dark');
			localStorage.setItem('theme', document.body.classList.contains('theme-dark') ? 'dark' : 'light');
			setIcon();
		});
	}
})();
// Dropdown interactions
(function(){
	const dropdownLinks = document.querySelectorAll('.dropdown > a');
	dropdownLinks.forEach(function(link){
		link.addEventListener('click', function(e){
			e.preventDefault();
			const parent = this.parentElement;
			const expanded = parent.classList.contains('active');
			// close other open dropdowns
			document.querySelectorAll('.dropdown.active').forEach(function(d){ if (d !== parent) d.classList.remove('active'); });
			parent.classList.toggle('active');
			this.setAttribute('aria-expanded', String(!expanded));
			// align dropdown if near right edge
			const menu = parent.querySelector('.dropdown-content');
			if (menu) {
				menu.classList.remove('align-right');
				const rect = menu.getBoundingClientRect();
				if (rect.right > window.innerWidth - 8) menu.classList.add('align-right');
			}
		});
	});
	const subLinks = document.querySelectorAll('.sub-dropdown > a');
	subLinks.forEach(function(link){
		link.addEventListener('click', function(e){
			e.preventDefault();
			e.stopPropagation();
			const parent = this.parentElement;
			const expanded = parent.classList.contains('active');
			// close sibling submenus
			parent.parentElement.querySelectorAll('.sub-dropdown.active').forEach(function(s){ if (s !== parent) s.classList.remove('active'); });
			parent.classList.toggle('active');
			this.setAttribute('aria-expanded', String(!expanded));
			const submenu = parent.querySelector('.sub-dropdown-content');
			if (submenu) {
				submenu.classList.remove('align-left');
				const rect = submenu.getBoundingClientRect();
				if (rect.right > window.innerWidth - 8) submenu.classList.add('align-left');
			}
		});
	});
	document.addEventListener('click', function(e){
		if (!e.target.closest('.navbar')) {
			document.querySelectorAll('.dropdown.active, .sub-dropdown.active, .user-dropdown.active').forEach(function(el){
				el.classList.remove('active');
			});
		}
	});
	// Defensive: remove duplicate Services buttons if any
	const svc = document.querySelectorAll('.nav-links > li.dropdown');
	if (svc.length > 1) {
		for (let i = 1; i < svc.length; i++) { svc[i].parentElement.removeChild(svc[i]); }
	}
})();
function toggleUserDropdown(){
	const dd = document.querySelector('.user-dropdown');
	if (dd) dd.classList.toggle('active');
}
</script>