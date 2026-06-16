// Notification Badge Module - Simulates OS count on frontend
// This updates the notification badge (bolinha) on all pages

const NOTIFICATION_COUNT = 2;

function updateNotificationBadge() {
    const badges = document.querySelectorAll('.icon-btn[data-badge]');
    badges.forEach(badge => {
        badge.setAttribute('data-badge', NOTIFICATION_COUNT);
    });
}

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', updateNotificationBadge);

// Export for manual updates if needed
window.NotificationBadge = {
    update: updateNotificationBadge,
    setCount: (count) => {
        NOTIFICATION_COUNT = count;
        updateNotificationBadge();
    },
    getCount: () => NOTIFICATION_COUNT
};