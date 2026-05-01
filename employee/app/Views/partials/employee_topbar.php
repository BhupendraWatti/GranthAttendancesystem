<?php
  $headerShiftClass = $headerShiftClass ?? '';
  $headerRight = $headerRight ?? '';
  $name = $name ?? 'Employee';
  $designation = $designation ?? '';
?>
<header class="employee-topbar">
  <div class="employee-topbar-content">
    <!-- Left Side -->
    <div class="employee-topbar-left">
      <button class="employee-menu-toggle" id="menu-toggle" aria-label="Toggle menu">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <line x1="3" y1="12" x2="21" y2="12"></line>
          <line x1="3" y1="6" x2="21" y2="6"></line>
          <line x1="3" y1="18" x2="21" y2="18"></line>
        </svg>
      </button>

      <div class="employee-topbar-brand">
        <h1 class="employee-topbar-title">Granth Infotech</h1>
        <p class="employee-topbar-subtitle">Attendance Management</p>
      </div>
    </div>

    <!-- Right Side -->
    <div class="employee-topbar-right">
      <!-- Notifications -->
      <button class="employee-topbar-action" aria-label="Notifications">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
          <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
        </svg>
        <span class="employee-notification-badge">3</span>
      </button>

      <!-- User Profile -->
      <div class="employee-user-profile">
        <div class="employee-user-avatar">
          <?= esc(strtoupper(substr($name, 0, 1))) ?>
        </div>
        <div class="employee-user-info">
          <span class="employee-user-name"><?= esc($name) ?></span>
          <span class="employee-user-role"><?= esc($designation) ?></span>
        </div>
        <button class="employee-user-menu-toggle" aria-label="User menu">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polyline points="6 9 12 15 18 9"></polyline>
          </svg>
        </button>
      </div>

      <!-- Mobile Logout -->
      <a href="<?= site_url('auth/logout') ?>" class="employee-logout-mobile" aria-label="Logout">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
          <polyline points="16 17 21 12 16 7"></polyline>
          <line x1="21" y1="12" x2="9" y2="12"></line>
        </svg>
      </a>
    </div>
  </div>

  <!-- User Dropdown Menu -->
  <div class="employee-user-dropdown" id="user-dropdown">
    <div class="employee-dropdown-header">
      <div class="employee-dropdown-avatar">
        <?= esc(strtoupper(substr($name, 0, 1))) ?>
      </div>
      <div class="employee-dropdown-info">
        <div class="employee-dropdown-name"><?= esc($name) ?></div>
        <div class="employee-dropdown-email"><?= esc($employee['email'] ?? 'employee@company.com') ?></div>
      </div>
    </div>
    <div class="employee-dropdown-body">
      <a href="<?= site_url('profile') ?>" class="employee-dropdown-item">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
          <circle cx="12" cy="7" r="4"></circle>
        </svg>
        <span>Profile</span>
      </a>
      <a href="<?= site_url('settings') ?>" class="employee-dropdown-item">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="12" cy="12" r="3"></circle>
          <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
        </svg>
        <span>Settings</span>
      </a>
      <div class="employee-dropdown-divider"></div>
      <a href="<?= site_url('auth/logout') ?>" class="employee-dropdown-item employee-dropdown-item--danger">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
          <polyline points="16 17 21 12 16 7"></polyline>
          <line x1="21" y1="12" x2="9" y2="12"></line>
        </svg>
        <span>Logout</span>
      </a>
    </div>
  </div>
</header>

<style>
.employee-topbar {
  background: white;
  border-bottom: 1px solid #e5e7eb;
  position: sticky;
  top: 0;
  z-index: 999;
  box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.employee-topbar-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem 2rem;
  max-width: 100%;
}

.employee-topbar-left {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.employee-menu-toggle {
  display: none;
  background: none;
  border: none;
  cursor: pointer;
  padding: 0.5rem;
  border-radius: 0.5rem;
  transition: background 0.2s ease;
  color: #1a1a2e;
}

.employee-menu-toggle:hover {
  background: #f8f9fa;
}

@media (max-width: 1024px) {
  .employee-menu-toggle {
    display: flex;
    align-items: center;
    justify-content: center;
  }
}

.employee-topbar-brand {
  display: none;
}

@media (min-width: 768px) {
  .employee-topbar-brand {
    display: block;
  }
}

.employee-topbar-title {
  font-family: 'Playfair Display', Georgia, serif;
  font-size: 1.25rem;
  font-weight: 700;
  color: #1a1a2e;
  margin-bottom: 0.25rem;
}

.employee-topbar-subtitle {
  font-size: 0.75rem;
  color: #6c757d;
}

.employee-topbar-right {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.employee-topbar-action {
  position: relative;
  background: none;
  border: none;
  cursor: pointer;
  padding: 0.5rem;
  border-radius: 0.5rem;
  transition: all 0.2s ease;
  color: #6c757d;
  display: flex;
  align-items: center;
  justify-content: center;
}

.employee-topbar-action:hover {
  background: #f8f9fa;
  color: #1a1a2e;
}

.employee-notification-badge {
  position: absolute;
  top: 0.25rem;
  right: 0.25rem;
  width: 18px;
  height: 18px;
  background: #d63031;
  color: white;
  border-radius: 50%;
  font-size: 0.625rem;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center;
  border: 2px solid white;
}

.employee-user-profile {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.5rem 1rem;
  border-radius: 9999px;
  background: #f8f9fa;
  cursor: pointer;
  transition: all 0.2s ease;
  border: 1px solid transparent;
}

.employee-user-profile:hover {
  background: #e9ecef;
  border-color: #e5e7eb;
}

.employee-user-avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-weight: 600;
  font-size: 1rem;
  box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3);
}

.employee-user-info {
  display: flex;
  flex-direction: column;
}

@media (max-width: 640px) {
  .employee-user-info {
    display: none;
  }
}

.employee-user-name {
  font-weight: 600;
  font-size: 0.875rem;
  color: #1a1a2e;
}

.employee-user-role {
  font-size: 0.75rem;
  color: #6c757d;
}

.employee-user-menu-toggle {
  background: none;
  border: none;
  cursor: pointer;
  padding: 0.25rem;
  color: #6c757d;
  transition: color 0.2s ease;
}

.employee-user-menu-toggle:hover {
  color: #1a1a2e;
}

.employee-logout-mobile {
  display: none;
  background: none;
  border: none;
  cursor: pointer;
  padding: 0.5rem;
  border-radius: 0.5rem;
  color: #6c757d;
  transition: all 0.2s ease;
}

.employee-logout-mobile:hover {
  background: #f8f9fa;
  color: #d63031;
}

@media (max-width: 1024px) {
  .employee-logout-mobile {
    display: flex;
    align-items: center;
    justify-content: center;
  }
}

/* User Dropdown */
.employee-user-dropdown {
  position: absolute;
  top: 100%;
  right: 2rem;
  width: 280px;
  background: white;
  border-radius: 0.75rem;
  box-shadow: 0 10px 40px rgba(0,0,0,0.15);
  border: 1px solid #e5e7eb;
  opacity: 0;
  visibility: hidden;
  transform: translateY(-10px);
  transition: all 0.2s ease;
  z-index: 1000;
}

.employee-user-profile.active + .employee-user-dropdown,
.employee-user-dropdown:hover {
  opacity: 1;
  visibility: visible;
  transform: translateY(0);
}

.employee-dropdown-header {
  padding: 1.25rem;
  border-bottom: 1px solid #e5e7eb;
  display: flex;
  align-items: center;
  gap: 1rem;
  background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
  border-radius: 0.75rem 0.75rem 0 0;
}

.employee-dropdown-avatar {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-weight: 700;
  font-size: 1.25rem;
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
}

.employee-dropdown-info {
  flex: 1;
}

.employee-dropdown-name {
  font-weight: 600;
  font-size: 0.875rem;
  color: #1a1a2e;
  margin-bottom: 0.25rem;
}

.employee-dropdown-email {
  font-size: 0.75rem;
  color: #6c757d;
}

.employee-dropdown-body {
  padding: 0.5rem;
}

.employee-dropdown-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem 1rem;
  border-radius: 0.5rem;
  color: #1a1a2e;
  text-decoration: none;
  font-size: 0.875rem;
  font-weight: 500;
  transition: all 0.2s ease;
}

.employee-dropdown-item:hover {
  background: #f8f9fa;
  color: #667eea;
}

.employee-dropdown-item--danger {
  color: #d63031;
}

.employee-dropdown-item--danger:hover {
  background: rgba(214, 48, 49, 0.1);
  color: #d63031;
}

.employee-dropdown-divider {
  height: 1px;
  background: #e5e7eb;
  margin: 0.5rem 0;
}

/* Responsive */
@media (max-width: 640px) {
  .employee-topbar-content {
    padding: 1rem;
  }

  .employee-topbar-brand {
    display: none;
  }

  .employee-notification-badge {
    width: 16px;
    height: 16px;
    font-size: 0.5rem;
  }
}
</style>

<script>
// User dropdown toggle
document.querySelector('.employee-user-profile')?.addEventListener('click', function() {
  this.classList.toggle('active');
});

// Close dropdown when clicking outside
document.addEventListener('click', function(e) {
  const userProfile = document.querySelector('.employee-user-profile');
  const dropdown = document.querySelector('.employee-user-dropdown');

  if (userProfile && !userProfile.contains(e.target) && !dropdown.contains(e.target)) {
    userProfile.classList.remove('active');
  }
});
</script>