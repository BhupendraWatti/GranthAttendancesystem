<?php
  $activePage = $activePage ?? 'dashboard';
  $showLogoutButton = $showLogoutButton ?? false;
  $name = $name ?? 'Employee';
  $designation = $designation ?? '';
?>
<nav class="employee-sidebar">
  <!-- Sidebar Header -->
  <div class="employee-sidebar-header">
    <div class="employee-sidebar-brand">
      <div class="employee-sidebar-logo">G</div>
      <div class="employee-sidebar-title">
        <div class="employee-sidebar-name">Granth Infotech</div>
        <div class="employee-sidebar-subtitle">Employee Portal</div>
      </div>
    </div>
  </div>

  <!-- User Profile Mini -->
  <div class="employee-sidebar-user">
    <div class="employee-sidebar-user-avatar">
      <?= esc(strtoupper(substr($name, 0, 1))) ?>
    </div>
    <div class="employee-sidebar-user-info">
      <div class="employee-sidebar-user-name"><?= esc($name) ?></div>
      <div class="employee-sidebar-user-role"><?= esc($designation) ?></div>
    </div>
  </div>

  <!-- Navigation -->
  <div class="employee-sidebar-nav">
    <ul class="employee-nav-list">
      <li>
        <a class="employee-nav-item <?= $activePage === 'dashboard' ? 'active' : '' ?>" href="<?= site_url('/') ?>">
          <span class="employee-nav-icon">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect x="3" y="3" width="7" height="7"></rect>
              <rect x="14" y="3" width="7" height="7"></rect>
              <rect x="14" y="14" width="7" height="7"></rect>
              <rect x="3" y="14" width="7" height="7"></rect>
            </svg>
          </span>
          <span>Dashboard</span>
        </a>
      </li>
      <li>
        <a class="employee-nav-item <?= $activePage === 'attendance' ? 'active' : '' ?>" href="<?= site_url('attendance') ?>">
          <span class="employee-nav-icon">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
              <polyline points="14 2 14 8 20 8"></polyline>
              <line x1="16" y1="13" x2="8" y2="13"></line>
              <line x1="16" y1="17" x2="8" y2="17"></line>
              <polyline points="10 9 9 9 8 9"></polyline>
            </svg>
          </span>
          <span>Attendance</span>
        </a>
      </li>
      <li>
        <a class="employee-nav-item <?= $activePage === 'salary' ? 'active' : '' ?>" href="<?= site_url('salary') ?>">
          <span class="employee-nav-icon">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <line x1="12" y1="1" x2="12" y2="23"></line>
              <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
            </svg>
          </span>
          <span>Salary</span>
        </a>
      </li>
      <li>
        <a class="employee-nav-item <?= $activePage === 'leave' ? 'active' : '' ?>" href="<?= site_url('leave') ?>">
          <span class="employee-nav-icon">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
              <line x1="16" y1="2" x2="16" y2="6"></line>
              <line x1="8" y1="2" x2="8" y2="6"></line>
              <line x1="3" y1="10" x2="21" y2="10"></line>
            </svg>
          </span>
          <span>Leave</span>
        </a>
      </li>
      <li>
        <a class="employee-nav-item <?= $activePage === 'documents' ? 'active' : '' ?>" href="<?= site_url('documents') ?>">
          <span class="employee-nav-icon">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path>
            </svg>
          </span>
          <span>Documents</span>
        </a>
      </li>
      <li>
        <a class="employee-nav-item <?= $activePage === 'notifications' ? 'active' : '' ?>" href="<?= site_url('notifications') ?>">
          <span class="employee-nav-icon">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
              <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
            </svg>
          </span>
          <span>Notifications</span>
        </a>
      </li>
      <li>
        <a class="employee-nav-item <?= $activePage === 'profile' ? 'active' : '' ?>" href="<?= site_url('profile') ?>">
          <span class="employee-nav-icon">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
              <circle cx="12" cy="7" r="4"></circle>
            </svg>
          </span>
          <span>Profile</span>
        </a>
      </li>
    </ul>
  </div>

  <!-- Logout Button -->
  <?php if ($showLogoutButton): ?>
  <div class="employee-sidebar-footer">
    <a class="employee-logout-btn" href="<?= site_url('auth/logout') ?>">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
        <polyline points="16 17 21 12 16 7"></polyline>
        <line x1="21" y1="12" x2="9" y2="12"></line>
      </svg>
      <span>Logout</span>
    </a>
  </div>
  <?php endif; ?>
</nav>

<style>
.employee-sidebar {
  width: 280px;
  background: white;
  border-right: 1px solid #e5e7eb;
  position: fixed;
  height: 100vh;
  overflow-y: auto;
  z-index: 1000;
  transition: transform 0.3s ease;
  display: flex;
  flex-direction: column;
}

.employee-sidebar-header {
  padding: 1.5rem;
  border-bottom: 1px solid #e5e7eb;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
}

.employee-sidebar-brand {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.employee-sidebar-logo {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  background: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  color: #667eea;
  font-weight: 700;
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.employee-sidebar-title {
  flex: 1;
}

.employee-sidebar-name {
  font-size: 1.125rem;
  font-weight: 700;
  margin-bottom: 0.25rem;
}

.employee-sidebar-subtitle {
  font-size: 0.75rem;
  opacity: 0.8;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.employee-sidebar-user {
  padding: 1.5rem;
  border-bottom: 1px solid #e5e7eb;
  display: flex;
  align-items: center;
  gap: 1rem;
  background: #f8f9fa;
}

.employee-sidebar-user-avatar {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-weight: 600;
  font-size: 1.25rem;
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
}

.employee-sidebar-user-info {
  flex: 1;
}

.employee-sidebar-user-name {
  font-weight: 600;
  font-size: 0.875rem;
  color: #1a1a2e;
  margin-bottom: 0.25rem;
}

.employee-sidebar-user-role {
  font-size: 0.75rem;
  color: #6c757d;
}

.employee-sidebar-nav {
  flex: 1;
  padding: 1rem;
}

.employee-nav-list {
  list-style: none;
  padding: 0;
  margin: 0;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.employee-nav-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.875rem 1rem;
  border-radius: 0.5rem;
  color: #6c757d;
  text-decoration: none;
  transition: all 0.2s ease;
  font-weight: 500;
  font-size: 0.875rem;
}

.employee-nav-item:hover {
  background: #f8f9fa;
  color: #667eea;
  transform: translateX(4px);
}

.employee-nav-item.active {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
}

.employee-nav-icon {
  width: 20px;
  height: 20px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.employee-sidebar-footer {
  padding: 1rem;
  border-top: 1px solid #e5e7eb;
}

.employee-logout-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  width: 100%;
  padding: 0.875rem 1rem;
  background: #f8f9fa;
  color: #d63031;
  border: 1px solid #e5e7eb;
  border-radius: 0.5rem;
  text-decoration: none;
  font-weight: 600;
  font-size: 0.875rem;
  transition: all 0.2s ease;
}

.employee-logout-btn:hover {
  background: #d63031;
  color: white;
  border-color: #d63031;
}

/* Mobile Responsive */
@media (max-width: 1024px) {
  .employee-sidebar {
    transform: translateX(-100%);
  }

  .employee-sidebar.active {
    transform: translateX(0);
  }
}
</style>