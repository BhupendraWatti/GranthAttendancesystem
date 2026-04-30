/**
 * AttendPro — Minimal JavaScript
 * Progressive enhancement only. No SPA behavior.
 */

document.addEventListener('DOMContentLoaded', function () {

    // ============================================================
    // 1. Sidebar Toggle (Mobile)
    // ============================================================
    const sidebar      = document.getElementById('sidebar');
    const toggleBtn    = document.getElementById('sidebar-toggle');
    const overlay      = document.getElementById('sidebar-overlay');

    function openSidebar()  {
        if (sidebar) sidebar.classList.add('open');
        if (overlay) overlay.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeSidebar() {
        if (sidebar) sidebar.classList.remove('open');
        if (overlay) overlay.classList.remove('active');
        document.body.style.overflow = '';
    }

    if (toggleBtn) toggleBtn.addEventListener('click', openSidebar);
    if (overlay)   overlay.addEventListener('click', closeSidebar);

    // ============================================================
    // 2. Flash Alert Auto-dismiss
    // ============================================================
    document.querySelectorAll('.alert').forEach(function (el) {
        // Auto-dismiss after 5 seconds
        setTimeout(function () {
            el.style.transition = 'opacity 0.4s, transform 0.4s';
            el.style.opacity = '0';
            el.style.transform = 'translateY(-10px)';
            setTimeout(function () { el.remove(); }, 400);
        }, 5000);

        // Manual close button
        var closeBtn = el.querySelector('.alert-close');
        if (closeBtn) {
            closeBtn.addEventListener('click', function () {
                el.style.opacity = '0';
                setTimeout(function () { el.remove(); }, 300);
            });
        }
    });

    // ============================================================
    // 3. Table Search / Filter
    // ============================================================
    var searchInput = document.getElementById('table-search');
    if (searchInput) {
        searchInput.addEventListener('input', function () {
            var query = this.value.toLowerCase();
            var rows  = document.querySelectorAll('#data-table tbody tr');
            rows.forEach(function (row) {
                var text = row.textContent.toLowerCase();
                row.style.display = text.indexOf(query) !== -1 ? '' : 'none';
            });
        });
    }

    // ============================================================
    // 4. Form Components (TomSelect)
    // ============================================================
    document.querySelectorAll('.tom-select').forEach(function(el) {
        if (typeof TomSelect !== 'undefined') {
            new TomSelect(el, {
                plugins: ['remove_button'],
                maxOptions: 50,
                allowEmptyOption: true,
            });
        }
    });

    // ============================================================
    // 5. Tabs
    // ============================================================
    document.querySelectorAll('.tab-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var targetId = this.getAttribute('data-tab');
            var tabGroup = this.closest('.card') || document;

            // Deactivate all tabs in group
            tabGroup.querySelectorAll('.tab-btn').forEach(function (b) { b.classList.remove('active'); });
            tabGroup.querySelectorAll('.tab-content').forEach(function (c) { c.classList.remove('active'); });

            // Activate clicked tab
            this.classList.add('active');
            var target = document.getElementById(targetId);
            if (target) target.classList.add('active');
        });
    });

    // ============================================================
    // 6. Dashboard Dynamic Refresh (30 seconds)
    // ============================================================
    var feedContainer = document.getElementById('live-feed');
    var attendanceBody = document.getElementById('attendance-table-body');
    var hasDashboardWidgets = !!(feedContainer || attendanceBody || document.getElementById('dashboard-stats'));
    if (hasDashboardWidgets) {
        var refreshInterval = 30000; // 30 seconds
        var queryDate = new URLSearchParams(window.location.search).get('date');
        var ctxEl = document.getElementById('dashboard-context');
        var serverDashDate = ctxEl && ctxEl.getAttribute('data-dashboard-date');
        function localCalendarYmd(d) {
            var y = d.getFullYear();
            var m = String(d.getMonth() + 1).padStart(2, '0');
            var day = String(d.getDate()).padStart(2, '0');
            return y + '-' + m + '-' + day;
        }
        var dashboardDate = queryDate || serverDashDate || localCalendarYmd(new Date());

        function fetchJson(url) {
            return fetch(url, { headers: { 'Accept': 'application/json' } })
                .then(function (res) {
                    if (!res.ok) {
                        throw new Error('HTTP ' + res.status + ' for ' + url);
                    }
                    return res.json();
                });
        }

        function refreshDashboard() {
            Promise.all([
                fetchJson('/api/dashboard/summary?date=' + encodeURIComponent(dashboardDate)),
                fetchJson('/api/dashboard/attendance?date=' + encodeURIComponent(dashboardDate)),
                fetchJson('/api/dashboard/live-punches?date=' + encodeURIComponent(dashboardDate) + '&limit=15')
            ])
            .then(function (responses) {
                renderSummary(responses[0].data || {});
                renderAttendance(responses[1].data || []);
                renderFeed(responses[2].data || []);
            })
            .catch(function (err) {
                console.error('[Dashboard Refresh] ' + err.message);
            });
        }

        function renderSummary(summary) {
            setText('stat-total-employees', summary.total_employees || 0);
            setText('stat-present-today', summary.present_today || 0);
            setText('stat-absent-today', summary.absent_today || 0);
            setText('stat-late-today', summary.late_today || 0);

            var rate = (summary.attendance_rate || 0) + '% attendance rate';
            setText('stat-attendance-rate', rate);

            var halfDay = summary.half_day_today || 0;
            var note = halfDay > 0 ? (halfDay + ' half-day') : 'Full absences';
            setText('stat-halfday-note', note);
        }

        function renderAttendance(rows) {
            if (!attendanceBody) return;

            if (!rows.length) {
                attendanceBody.innerHTML = '<tr><td colspan="7"><div class="empty-state"><div class="empty-icon">📊</div><h4>No attendance data</h4><p>Run a sync to populate attendance records.</p></div></td></tr>';
                return;
            }

            var html = '';
            rows.forEach(function (row) {
                var firstIn = row.first_in ? formatTime(row.first_in) : '<span class="text-muted">—</span>';
                var lastOut = row.last_out ? formatTime(row.last_out) : '<span class="text-muted">—</span>';
                var workMins = parseInt(row.work_minutes || 0, 10);
                var hrs = Math.floor(workMins / 60);
                var mins = workMins % 60;
                var lateMins = parseInt(row.late_minutes || 0, 10);
                var lateCell = lateMins > 0
                    ? '<span class="badge badge--late">' + lateMins + ' min</span>'
                    : '<span class="text-muted">—</span>';

                html += '<tr>'
                    + '<td><a href="/employees/' + encodeURIComponent(row.emp_code) + '" style="font-weight:600;">'
                    + escapeHtml(row.name || row.emp_code) + '</a></td>'
                    + '<td class="font-mono">' + escapeHtml(row.emp_code || '') + '</td>'
                    + '<td>' + firstIn + '</td>'
                    + '<td>' + lastOut + '</td>'
                    + '<td class="font-mono">' + hrs + 'h ' + mins + 'm</td>'
                    + '<td><span class="badge badge--' + escapeHtml(row.status || 'absent') + '">'
                    + escapeHtml(String(row.status || 'absent').replace('_', ' ')) + '</span></td>'
                    + '<td>' + lateCell + '</td>'
                    + '</tr>';
            });
            attendanceBody.innerHTML = html;
        }

        function renderFeed(punches) {
            if (!feedContainer) return;
            if (!punches.length) {
                feedContainer.innerHTML = '<li class="feed-item"><div class="text-muted" style="padding:12px 0;text-align:center;width:100%;">No punches for this day.</div></li>';
                return;
            }

            var html = '';
            punches.forEach(function (p) {
                var initials = (p.name || p.emp_code || '??').substring(0, 2).toUpperCase();
                var time = formatTime(p.punch_time);
                var date = new Date(p.punch_time).toLocaleDateString('en-IN', { day: 'numeric', month: 'short' });

                html += '<li class="feed-item">'
                     +    '<div class="feed-avatar feed-avatar--in">' + initials + '</div>'
                     +    '<div class="feed-info">'
                     +      '<div class="feed-name">' + escapeHtml(p.name || p.emp_code) + '</div>'
                     +      '<div class="feed-code">' + escapeHtml(p.emp_code) + '</div>'
                     +    '</div>'
                     +    '<div class="feed-time">' + time + '<small>' + (p.time_ago || date) + '</small></div>'
                     +  '</li>';
            });

            feedContainer.innerHTML = html;
        }

        function formatTime(value) {
            return new Date(value).toLocaleTimeString('en-IN', { hour: '2-digit', minute: '2-digit' });
        }

        function setText(id, value) {
            var el = document.getElementById(id);
            if (el) el.textContent = value;
        }

        setTimeout(refreshDashboard, 2000);
        setInterval(refreshDashboard, refreshInterval);
    }

    // ============================================================
    // 7. Date Picker Form Auto-submit
    // ============================================================
    document.querySelectorAll('.auto-submit-date').forEach(function (input) {
        input.addEventListener('change', function () {
            this.closest('form').submit();
        });
    });

    // ============================================================
    // Utility: HTML escaping
    // ============================================================
    function escapeHtml(str) {
        if (!str) return '';
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(str));
        return div.innerHTML;
    }

});
