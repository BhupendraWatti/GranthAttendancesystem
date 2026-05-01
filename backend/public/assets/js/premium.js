/**
 * Premium UI Interactions
 * Granth Infotech Attendance System
 */

(function() {
    'use strict';

    // === Utility Functions ===
    const $ = (selector) => document.querySelector(selector);
    const $$ = (selector) => document.querySelectorAll(selector);

    // Debounce function
    const debounce = (func, wait) => {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    };

    // Throttle function
    const throttle = (func, limit) => {
        let inThrottle;
        return function(...args) {
            if (!inThrottle) {
                func.apply(this, args);
                inThrottle = true;
                setTimeout(() => inThrottle = false, limit);
            }
        };
    };

    // === Animation System ===
    const AnimationSystem = {
        // Intersection Observer for scroll animations
        initScrollAnimations() {
            const observerOptions = {
                root: null,
                rootMargin: '0px',
                threshold: 0.1
            };

            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('animate-in');
                        observer.unobserve(entry.target);
                    }
                });
            }, observerOptions);

            // Observe elements with animation classes
            $$('.animate-slide-in-down, .animate-slide-in-up, .animate-slide-in-left, .animate-slide-in-right, .animate-fade-in').forEach(el => {
                el.style.opacity = '0';
                observer.observe(el);
            });
        },

        // Add animation classes
        addAnimationClass(element, animation) {
            element.classList.add(animation);
            element.addEventListener('animationend', () => {
                element.classList.remove(animation);
            }, { once: true });
        }
    };

    // === Form Enhancements ===
    const FormEnhancements = {
        init() {
            this.initFloatingLabels();
            this.initInputValidation();
            this.initFormAnimations();
        },

        initFloatingLabels() {
            $$('.form-group').forEach(group => {
                const input = group.querySelector('input, textarea, select');
                const label = group.querySelector('label');

                if (!input || !label) return;

                const updateLabel = () => {
                    if (input.value || input === document.activeElement) {
                        label.classList.add('floating');
                    } else {
                        label.classList.remove('floating');
                    }
                };

                input.addEventListener('focus', updateLabel);
                input.addEventListener('blur', updateLabel);
                input.addEventListener('input', updateLabel);

                // Initial state
                updateLabel();
            });
        },

        initInputValidation() {
            $$('form[data-validate]').forEach(form => {
                form.addEventListener('submit', (e) => {
                    let isValid = true;

                    form.querySelectorAll('input[required], select[required], textarea[required]').forEach(field => {
                        if (!field.value.trim()) {
                            isValid = false;
                            this.showFieldError(field, 'This field is required');
                        } else {
                            this.clearFieldError(field);
                        }
                    });

                    if (!isValid) {
                        e.preventDefault();
                        this.shakeForm(form);
                    }
                });
            });
        },

        showFieldError(field, message) {
            field.classList.add('error');
            let errorElement = field.parentElement.querySelector('.field-error');

            if (!errorElement) {
                errorElement = document.createElement('span');
                errorElement.className = 'field-error';
                field.parentElement.appendChild(errorElement);
            }

            errorElement.textContent = message;
        },

        clearFieldError(field) {
            field.classList.remove('error');
            const errorElement = field.parentElement.querySelector('.field-error');
            if (errorElement) {
                errorElement.remove();
            }
        },

        shakeForm(form) {
            form.style.animation = 'shake 0.5s ease';
            setTimeout(() => {
                form.style.animation = '';
            }, 500);
        },

        initFormAnimations() {
            $$('input, textarea, select').forEach(input => {
                input.addEventListener('focus', function() {
                    this.parentElement.classList.add('focused');
                });

                input.addEventListener('blur', function() {
                    this.parentElement.classList.remove('focused');
                });
            });
        }
    };

    // === Table Enhancements ===
    const TableEnhancements = {
        init() {
            this.initSearch();
            this.initSort();
            this.initRowActions();
        },

        initSearch() {
            const searchInputs = $$('.search-input, #table-search');

            searchInputs.forEach(input => {
                input.addEventListener('input', debounce((e) => {
                    const searchTerm = e.target.value.toLowerCase();
                    const table = input.closest('.card').querySelector('table');

                    if (!table) return;

                    table.querySelectorAll('tbody tr').forEach(row => {
                        const text = row.textContent.toLowerCase();
                        row.style.display = text.includes(searchTerm) ? '' : 'none';
                    });
                }, 300));
            });
        },

        initSort() {
            $$('.table th[data-sort]').forEach(th => {
                th.style.cursor = 'pointer';
                th.addEventListener('click', () => {
                    const table = th.closest('table');
                    const tbody = table.querySelector('tbody');
                    const rows = Array.from(tbody.querySelectorAll('tr'));
                    const sortKey = th.dataset.sort;
                    const isAsc = th.classList.toggle('sort-asc');

                    rows.sort((a, b) => {
                        const aVal = a.querySelector(`[data-${sortKey}]`)?.textContent || '';
                        const bVal = b.querySelector(`[data-${sortKey}]`)?.textContent || '';

                        if (isAsc) {
                            return aVal.localeCompare(bVal);
                        } else {
                            return bVal.localeCompare(aVal);
                        }
                    });

                    rows.forEach(row => tbody.appendChild(row));
                });
            });
        },

        initRowActions() {
            $$('.table tbody tr').forEach(row => {
                row.addEventListener('mouseenter', function() {
                    this.classList.add('row-hover');
                });

                row.addEventListener('mouseleave', function() {
                    this.classList.remove('row-hover');
                });
            });
        }
    };

    // === Card Enhancements ===
    const CardEnhancements = {
        init() {
            this.initHoverEffects();
            this.initClickActions();
        },

        initHoverEffects() {
            $$('.card').forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.classList.add('card-hover');
                });

                card.addEventListener('mouseleave', function() {
                    this.classList.remove('card-hover');
                });
            });
        },

        initClickActions() {
            $$('.card[data-action]').forEach(card => {
                card.addEventListener('click', function() {
                    const action = this.dataset.action;
                    if (action && window[action]) {
                        window[action](this);
                    }
                });
            });
        }
    };

    // === Notification System ===
    const NotificationSystem = {
        container: null,

        init() {
            this.createContainer();
            this.initAutoDismiss();
        },

        createContainer() {
            this.container = document.createElement('div');
            this.container.className = 'notification-container';
            this.container.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                z-index: 10000;
                display: flex;
                flex-direction: column;
                gap: 10px;
            `;
            document.body.appendChild(this.container);
        },

        show(message, type = 'info', duration = 5000) {
            const notification = document.createElement('div');
            notification.className = `notification notification--${type}`;
            notification.innerHTML = `
                <span class="notification-icon">${this.getIcon(type)}</span>
                <span class="notification-message">${message}</span>
                <button class="notification-close">&times;</button>
            `;

            this.container.appendChild(notification);

            // Animate in
            AnimationSystem.addAnimationClass(notification, 'slideInRight');

            // Auto dismiss
            if (duration > 0) {
                setTimeout(() => {
                    this.dismiss(notification);
                }, duration);
            }

            // Close button
            notification.querySelector('.notification-close').addEventListener('click', () => {
                this.dismiss(notification);
            });

            return notification;
        },

        dismiss(notification) {
            AnimationSystem.addAnimationClass(notification, 'slideOutRight');
            setTimeout(() => {
                notification.remove();
            }, 300);
        },

        getIcon(type) {
            const icons = {
                success: '✓',
                error: '✕',
                warning: '⚠',
                info: 'ℹ'
            };
            return icons[type] || icons.info;
        },

        initAutoDismiss() {
            // Auto-dismiss existing alerts
            $$('.alert').forEach(alert => {
                setTimeout(() => {
                    alert.style.opacity = '0';
                    setTimeout(() => alert.remove(), 400);
                }, 5000);

                const close = alert.querySelector('.alert-close');
                if (close) {
                    close.addEventListener('click', () => {
                        alert.style.opacity = '0';
                        setTimeout(() => alert.remove(), 300);
                    });
                }
            });
        }
    };

    // === Loading States ===
    const LoadingStates = {
        show(element, text = 'Loading...') {
            const originalContent = element.innerHTML;
            element.dataset.originalContent = originalContent;
            element.disabled = true;
            element.innerHTML = `<span class="spinner"></span> ${text}`;
        },

        hide(element) {
            const originalContent = element.dataset.originalContent;
            if (originalContent) {
                element.innerHTML = originalContent;
                delete element.dataset.originalContent;
            }
            element.disabled = false;
        }
    };

    // === Sidebar Toggle ===
    const SidebarToggle = {
        init() {
            const toggle = $('#menu-toggle');
            const sidebar = $('.employee-sidebar');

            if (!toggle || !sidebar) return;

            toggle.addEventListener('click', () => {
                sidebar.classList.toggle('active');
            });

            // Close on outside click
            document.addEventListener('click', (e) => {
                if (window.innerWidth <= 1024 &&
                    !sidebar.contains(e.target) &&
                    !toggle.contains(e.target) &&
                    sidebar.classList.contains('active')) {
                    sidebar.classList.toggle('active');
                }
            });
        }
    };

    // === Stats Counter Animation ===
    const StatsCounter = {
        init() {
            $$('.stat-value').forEach(stat => {
                const target = parseInt(stat.textContent) || 0;
                this.animate(stat, target);
            });
        },

        animate(element, target) {
            const duration = 1000;
            const start = 0;
            const startTime = performance.now();

            const update = (currentTime) => {
                const elapsed = currentTime - startTime;
                const progress = Math.min(elapsed / duration, 1);

                // Easing function
                const easeOutQuart = 1 - Math.pow(1 - progress, 4);
                const current = Math.floor(easeOutQuart * target);

                element.textContent = current;

                if (progress < 1) {
                    requestAnimationFrame(update);
                } else {
                    element.textContent = target;
                }
            };

            requestAnimationFrame(update);
        }
    };

    // === Live Feed Updates ===
    const LiveFeed = {
        init() {
            const feed = $('#live-feed');
            if (!feed) return;

            // Simulate live updates (replace with actual WebSocket/AJAX)
            setInterval(() => {
                this.simulateUpdate(feed);
            }, 30000);
        },

        simulateUpdate(feed) {
            // This would be replaced with actual data fetching
            const newItem = document.createElement('li');
            newItem.className = 'feed-item';
            newItem.innerHTML = `
                <div class="feed-avatar feed-avatar--in">AB</div>
                <div class="feed-info">
                    <div class="feed-name">New Employee</div>
                    <div class="feed-code">EMP001</div>
                </div>
                <div class="feed-time">
                    <span class="feed-time-text">Just now</span>
                </div>
            `;

            feed.insertBefore(newItem, feed.firstChild);

            // Keep only last 10 items
            while (feed.children.length > 10) {
                feed.removeChild(feed.lastChild);
            }
        }
    };

    // === Initialize Everything ===
    document.addEventListener('DOMContentLoaded', () => {
        // Initialize all systems
        AnimationSystem.initScrollAnimations();
        FormEnhancements.init();
        TableEnhancements.init();
        CardEnhancements.init();
        NotificationSystem.init();
        SidebarToggle.init();
        StatsCounter.init();
        LiveFeed.init();

        // Make utilities available globally
        window.PremiumUI = {
            notify: NotificationSystem.show.bind(NotificationSystem),
            loading: LoadingStates,
            animate: AnimationSystem.addAnimationClass.bind(AnimationSystem)
        };

        console.log('Premium UI initialized successfully');
    });

    // === CSS Animations ===
    const style = document.createElement('style');
    style.textContent = `
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
            20%, 40%, 60%, 80% { transform: translateX(5px); }
        }

        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(100%);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes slideOutRight {
            from {
                opacity: 1;
                transform: translateX(0);
            }
            to {
                opacity: 0;
                transform: translateX(100%);
            }
        }

        .spinner {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-top-color: currentColor;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .notification {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 16px 20px;
            border-radius: 12px;
            background: white;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            min-width: 300px;
            animation: slideInRight 0.3s ease;
        }

        .notification--success {
            border-left: 4px solid #00b894;
        }

        .notification--error {
            border-left: 4px solid #d63031;
        }

        .notification--warning {
            border-left: 4px solid #fdcb6e;
        }

        .notification--info {
            border-left: 4px solid #0984e3;
        }

        .notification-icon {
            font-size: 1.25rem;
            font-weight: bold;
        }

        .notification-message {
            flex: 1;
            font-size: 0.875rem;
        }

        .notification-close {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: #6c757d;
            line-height: 1;
        }

        .notification-close:hover {
            color: #1a1a2e;
        }

        .form-group.focused label {
            color: #667eea;
        }

        .form-group.error input,
        .form-group.error select,
        .form-group.error textarea {
            border-color: #d63031;
        }

        .field-error {
            display: block;
            color: #d63031;
            font-size: 0.75rem;
            margin-top: 4px;
        }

        .card-hover {
            transform: translateY(-4px);
        }

        .row-hover {
            background: #f8f9fa;
        }

        .animate-in {
            opacity: 1 !important;
        }
    `;
    document.head.appendChild(style);

})();