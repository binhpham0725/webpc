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
});
