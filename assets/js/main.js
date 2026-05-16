document.addEventListener('DOMContentLoaded', function () {
    var root = document.documentElement;
    var storageKey = 'webpc-theme';

    function applyTheme(theme) {
        root.setAttribute('data-theme', theme);
        try {
            localStorage.setItem(storageKey, theme);
        } catch (error) {
            // Ignore storage write failures.
        }

        document.querySelectorAll('[data-theme-icon]').forEach(function (icon) {
            icon.classList.toggle('bi-moon-stars-fill', theme !== 'dark');
            icon.classList.toggle('bi-sun-fill', theme === 'dark');
        });
    }

    var currentTheme = root.getAttribute('data-theme') || 'light';
    applyTheme(currentTheme);

    document.querySelectorAll('[data-theme-toggle]').forEach(function (button) {
        button.addEventListener('click', function () {
            var nextTheme = root.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
            applyTheme(nextTheme);
        });
    });

    window.setTimeout(function () {
        document.querySelectorAll('.glass-alert').forEach(function (alert) {
            alert.classList.add('fade');
            window.setTimeout(function () {
                alert.remove();
            }, 400);
        });
    }, 3200);

    document.querySelectorAll('[data-qty-step]').forEach(function (button) {
        button.addEventListener('click', function () {
            var target = document.getElementById(button.getAttribute('data-qty-target'));
            if (!target) {
                return;
            }

            var step = parseInt(button.getAttribute('data-qty-step'), 10) || 0;
            var min = parseInt(target.getAttribute('min') || '1', 10);
            var max = parseInt(target.getAttribute('max') || '999', 10);
            var next = (parseInt(target.value || '1', 10) || 1) + step;

            next = Math.max(min, Math.min(max, next));
            target.value = String(next);
        });
    });

    document.querySelectorAll('[data-reveal-grid]').forEach(function (button) {
        button.addEventListener('click', function () {
            var targetId = button.getAttribute('data-reveal-grid');
            var hiddenItems = document.querySelectorAll('#' + targetId + ' .featured-extra');
            var expanded = button.getAttribute('data-expanded') === '1';
            var moreLabel = button.getAttribute('data-more-label') || 'Xem them';
            var lessLabel = button.getAttribute('data-less-label') || 'Thu gon';

            hiddenItems.forEach(function (item) {
                item.classList.toggle('d-none', expanded);
            });

            button.setAttribute('data-expanded', expanded ? '0' : '1');
            button.textContent = expanded ? moreLabel : lessLabel;
        });
    });

    document.querySelectorAll('[data-spec-builder]').forEach(function (builder) {
        var list = builder.querySelector('[data-spec-list]');
        var output = document.querySelector('[data-spec-output]');
        var addButton = builder.querySelector('[data-add-spec]');

        function addSpec(label, value) {
            var row = document.createElement('div');
            row.className = 'builder-row spec-builder-row';
            row.innerHTML = [
                '<input type="text" class="form-control glass-input" data-spec-label placeholder="Tên thông số">',
                '<input type="text" class="form-control glass-input" data-spec-value placeholder="Giá trị">',
                '<button class="btn btn-outline-danger btn-soft btn-sm" type="button" data-remove-row>Xóa</button>'
            ].join('');
            list.appendChild(row);
            row.querySelector('[data-spec-label]').value = label || '';
            row.querySelector('[data-spec-value]').value = value || '';
        }

        function syncSpecs() {
            var lines = [];
            list.querySelectorAll('.spec-builder-row').forEach(function (row) {
                var label = row.querySelector('[data-spec-label]').value.trim();
                var value = row.querySelector('[data-spec-value]').value.trim();
                if (label !== '' && value !== '') {
                    lines.push(label + ': ' + value);
                }
            });
            output.value = lines.join('\n');
        }

        (output.value || '').split(/\r?\n/).filter(Boolean).forEach(function (line) {
            var parts = line.split(':');
            var label = parts.shift() || '';
            addSpec(label.trim(), parts.join(':').trim());
        });

        if (!list.children.length) {
            addSpec('CPU', '');
            addSpec('RAM', '');
            addSpec('Ổ cứng', '');
        }

        addButton.addEventListener('click', function () {
            addSpec('', '');
        });

        builder.addEventListener('input', syncSpecs);
        builder.addEventListener('click', function (event) {
            if (event.target.matches('[data-remove-row]')) {
                event.target.closest('.builder-row').remove();
                syncSpecs();
            }
        });

        if (builder.closest('form')) {
            builder.closest('form').addEventListener('submit', syncSpecs);
        }
    });

    document.querySelectorAll('[data-feature-builder]').forEach(function (builder) {
        var list = builder.querySelector('[data-feature-list]');
        var output = document.querySelector('[data-feature-output]');
        var addButton = builder.querySelector('[data-add-feature]');

        function addFeature(value) {
            var row = document.createElement('div');
            row.className = 'builder-row feature-builder-row';
            row.innerHTML = [
                '<input type="text" class="form-control glass-input" data-feature-value placeholder="Nhập điểm nổi bật">',
                '<button class="btn btn-outline-danger btn-soft btn-sm" type="button" data-remove-row>Xóa</button>'
            ].join('');
            list.appendChild(row);
            row.querySelector('[data-feature-value]').value = value || '';
        }

        function syncFeatures() {
            var lines = [];
            list.querySelectorAll('[data-feature-value]').forEach(function (input) {
                var value = input.value.trim();
                if (value !== '') {
                    lines.push(value);
                }
            });
            output.value = lines.join('\n');
        }

        (output.value || '').split(/\r?\n/).filter(Boolean).forEach(function (line) {
            addFeature(line.trim());
        });

        if (!list.children.length) {
            addFeature('');
            addFeature('');
            addFeature('');
        }

        addButton.addEventListener('click', function () {
            addFeature('');
        });

        builder.addEventListener('input', syncFeatures);
        builder.addEventListener('click', function (event) {
            if (event.target.matches('[data-remove-row]')) {
                event.target.closest('.builder-row').remove();
                syncFeatures();
            }
        });

        if (builder.closest('form')) {
            builder.closest('form').addEventListener('submit', syncFeatures);
        }
    });
});
