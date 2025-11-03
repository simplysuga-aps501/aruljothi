import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

window.copyPhone = function(event, number) {
    if (!number) return;

    const isMobile = /Mobi|Android|iPhone/i.test(navigator.userAgent);

    if (!isMobile) {
        // Prevent dialer on desktop
        event.preventDefault();

        navigator.clipboard.writeText(number)
            .then(() => {
                alert('📋 Phone number copied: ' + number);
            })
            .catch(() => {
                alert('❌ Failed to copy number.');
            });
    }
};

