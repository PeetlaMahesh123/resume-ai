// Simple form validation
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('resumeForm');
    
    if (form) {
        form.addEventListener('submit', function(e) {
            const loading = document.getElementById('loading');
            if (loading) {
                loading.style.display = 'inline';
            }
        });
    }
});
