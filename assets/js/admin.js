document.addEventListener('DOMContentLoaded', () => {
    // ==========================================
    // Event Delegation (No Inline JS per SKILL.md)
    // ==========================================
    
    // 1. User Profile Dropdown
    document.addEventListener('click', (e) => {
        const userProfileBtn = e.target.closest('#userProfileBtn');
        const userDropdown = document.getElementById('userDropdown');
        
        if (userProfileBtn) {
            userDropdown.classList.toggle('show');
        } else if (userDropdown && !userDropdown.contains(e.target)) {
            userDropdown.classList.remove('show');
        }
    });
});
