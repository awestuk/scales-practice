// Keyboard shortcuts handler
document.addEventListener('DOMContentLoaded', function() {
    let isWaitingForResponse = false;
    
    document.addEventListener('keydown', function(e) {
        // Ignore if typing in input field
        if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') {
            return;
        }
        
        // Prevent multiple requests
        if (isWaitingForResponse) {
            return;
        }
        
        const key = e.key.toLowerCase();
        
        // Space - advance (show next scale or click main button)
        if (key === ' ') {
            e.preventDefault();
            
            // Check if we're showing a scale (outcome buttons visible)
            const outcomeButtons = document.querySelector('.outcome-buttons');
            if (outcomeButtons && outcomeButtons.style.display !== 'none') {
                // Don't do anything when outcome buttons are shown
                return;
            }
            
            // Otherwise, click the main action button
            const mainButton = document.querySelector('[hx-post="/next-scale"]');
            if (mainButton) {
                isWaitingForResponse = true;
                mainButton.click();
                setTimeout(() => { isWaitingForResponse = false; }, 500);
            }
        }
        
        // Y - success
        if (key === 'y') {
            const successBtn = document.querySelector('button[hx-vals*="success"]');
            if (successBtn) {
                isWaitingForResponse = true;
                successBtn.click();
                setTimeout(() => { isWaitingForResponse = false; }, 500);
            }
        }
        
        // N - fail
        if (key === 'n') {
            const failBtn = document.querySelector('button[hx-vals*="fail"]');
            if (failBtn) {
                isWaitingForResponse = true;
                failBtn.click();
                setTimeout(() => { isWaitingForResponse = false; }, 500);
            }
        }
    });
    
    // Reset waiting state on HTMX events
    document.body.addEventListener('htmx:afterSwap', function() {
        isWaitingForResponse = false;
    });
    
    // Add visual feedback for button presses without size change
    document.addEventListener('mousedown', function(e) {
        if (e.target.matches('.btn') || e.target.closest('.btn')) {
            const btn = e.target.matches('.btn') ? e.target : e.target.closest('.btn');
            btn.style.opacity = '0.9';
        }
    });
    
    document.addEventListener('mouseup', function(e) {
        if (e.target.matches('.btn') || e.target.closest('.btn')) {
            const btn = e.target.matches('.btn') ? e.target : e.target.closest('.btn');
            btn.style.opacity = '';
        }
    });
    
    // Clean up on mouse leave to prevent stuck states
    document.addEventListener('mouseleave', function(e) {
        if (e.target.matches('.btn')) {
            e.target.style.opacity = '';
            e.target.style.transform = '';
        }
    });
});