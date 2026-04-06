// Basic UI helpers for beginner-friendly behavior.

document.addEventListener('DOMContentLoaded', function () {
    var themeToggle = document.querySelector('[data-theme-toggle="true"]');
    var savedTheme = localStorage.getItem('tracker_theme') || '';
    if (savedTheme) {
        document.body.setAttribute('data-theme', savedTheme);
    }

    if (themeToggle) {
        themeToggle.addEventListener('click', function () {
            var current = document.body.getAttribute('data-theme') || '';
            var next = current === 'mint' ? '' : 'mint';
            if (next) {
                document.body.setAttribute('data-theme', next);
            } else {
                document.body.removeAttribute('data-theme');
            }
            localStorage.setItem('tracker_theme', next);
        });
    }

    var menuToggleBtn = document.querySelector('[data-menu-toggle="true"]');
    var sidebar = document.querySelector('[data-sidebar="true"]');

    if (menuToggleBtn && sidebar) {
        menuToggleBtn.addEventListener('click', function () {
            sidebar.classList.toggle('open');
        });

        document.addEventListener('click', function (event) {
            var clickedInsideSidebar = sidebar.contains(event.target);
            var clickedToggle = menuToggleBtn.contains(event.target);
            if (!clickedInsideSidebar && !clickedToggle && window.innerWidth <= 900) {
                sidebar.classList.remove('open');
            }
        });
    }

    // Auto-hide alerts after a short delay for cleaner pages.
    var alerts = document.querySelectorAll('.alert');
    alerts.forEach(function (alert) {
        setTimeout(function () {
            alert.style.transition = 'opacity 0.3s ease';
            alert.style.opacity = '0';
            setTimeout(function () {
                if (alert.parentNode) {
                    alert.parentNode.removeChild(alert);
                }
            }, 320);
        }, 3500);
    });

    // Add instant search above each table.
    var tableWraps = document.querySelectorAll('.table-wrap');
    tableWraps.forEach(function (wrap) {
        var table = wrap.querySelector('table');
        if (!table) {
            return;
        }

        var toolBox = document.createElement('div');
        toolBox.className = 'table-tools';

        var input = document.createElement('input');
        input.type = 'text';
        input.className = 'table-search';
        input.placeholder = 'Search this table...';
        toolBox.appendChild(input);

        var exportBtn = document.createElement('button');
        exportBtn.type = 'button';
        exportBtn.className = 'table-export';
        exportBtn.textContent = 'Export CSV';
        toolBox.appendChild(exportBtn);

        wrap.parentNode.insertBefore(toolBox, wrap);

        input.addEventListener('input', function () {
            var keyword = input.value.toLowerCase().trim();
            var rows = table.querySelectorAll('tbody tr');

            rows.forEach(function (row) {
                var text = row.textContent.toLowerCase();
                row.style.display = text.indexOf(keyword) !== -1 ? '' : 'none';
            });
        });

        exportBtn.addEventListener('click', function () {
            var rows = table.querySelectorAll('tr');
            var csv = [];

            rows.forEach(function (row) {
                if (row.style.display === 'none') {
                    return;
                }

                var cols = row.querySelectorAll('th, td');
                var rowData = [];

                cols.forEach(function (col) {
                    var text = col.textContent.replace(/\s+/g, ' ').trim();
                    rowData.push('"' + text.replace(/"/g, '""') + '"');
                });

                csv.push(rowData.join(','));
            });

            var blob = new Blob([csv.join('\n')], { type: 'text/csv;charset=utf-8;' });
            var url = URL.createObjectURL(blob);
            var link = document.createElement('a');
            link.href = url;
            link.download = 'table_export.csv';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            URL.revokeObjectURL(url);
        });
    });

    var forms = document.querySelectorAll('form[data-validate="true"]');

    forms.forEach(function (form) {
        form.addEventListener('submit', function (event) {
            var requiredFields = form.querySelectorAll('[required]');
            var valid = true;

            requiredFields.forEach(function (field) {
                if (!field.value.trim()) {
                    valid = false;
                    field.style.borderColor = '#dc2626';
                    field.setAttribute('aria-invalid', 'true');
                } else {
                    field.style.borderColor = '#cbd5e1';
                    field.removeAttribute('aria-invalid');
                }
            });

            var emailField = form.querySelector('input[type="email"]');
            if (emailField && emailField.value.trim()) {
                var emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailPattern.test(emailField.value.trim())) {
                    valid = false;
                    emailField.style.borderColor = '#dc2626';
                    alert('Please enter a valid email address.');
                }
            }

            if (!valid) {
                event.preventDefault();
                alert('Please fill all required fields correctly.');
            }
        });
    });
});