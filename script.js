// Initialize hamburger menu (safe, tolerant to different HTML patterns)
function initHamburgerMenu() {
    const hamburgerBtn = document.getElementById('hamburgerBtn') || document.getElementById('hamburger');
    const navMenu = document.querySelector('.nav-menu') || document.querySelector('.desktop-nav') || document.getElementById('navDrawer');
    const navLinks = document.querySelectorAll('.nav-links a');

    if (!hamburgerBtn || !navMenu) return;

    // If this is the slide-in drawer (#navDrawer), the main DOMContentLoaded handler
    // already wires up open/close. Skip here to avoid double-binding.
    if (navMenu.id === 'navDrawer') return;

    // Toggle menu on hamburger click
    hamburgerBtn.addEventListener('click', () => {
        navMenu.classList.toggle('active');
        hamburgerBtn.classList.toggle('active');
        hamburgerBtn.setAttribute('aria-expanded', navMenu.classList.contains('active'));
    });

    // Close when a nav link is clicked
    navLinks.forEach(link => {
        link.addEventListener('click', () => {
            navMenu.classList.remove('active');
            hamburgerBtn.classList.remove('active');
            hamburgerBtn.setAttribute('aria-expanded', 'false');
        });
    });

    // Close on outside click
    document.addEventListener('click', (e) => {
        if (!e.target.closest('header') && navMenu.classList.contains('active')) {
            navMenu.classList.remove('active');
            hamburgerBtn.classList.remove('active');
            hamburgerBtn.setAttribute('aria-expanded', 'false');
        }
    });
}

// Show more/less functionality
function toggleDetails(detailsId, button) {
    const details = document.getElementById(detailsId);
    const icon = button.querySelector('i');
    
    if (details.classList.contains('show')) {
        details.classList.remove('show');
        button.innerHTML = 'Show More <i class="fas fa-chevron-down"></i>';
        button.classList.remove('expanded');
    } else {
        details.classList.add('show');
        button.innerHTML = 'Show Less <i class="fas fa-chevron-up"></i>';
        button.classList.add('expanded');
    }
}

// Tab switching functionality
function switchTab(tabType, btn) {
    const tabButtons = document.querySelectorAll('.tab-btn');
    const forms = document.querySelectorAll('.signup-form');

    // Remove active class from all tabs and forms
    tabButtons.forEach(b => b.classList.remove('active'));
    forms.forEach(f => f.classList.remove('active'));

    // Determine which button to mark active
    let activeBtn = btn;
    if (!activeBtn) {
        // Try to find an inline onclick that references the tabType (fallback)
        activeBtn = Array.from(tabButtons).find(b => (b.getAttribute('onclick') || '').includes(tabType)) || tabButtons[0];
    }

    if (activeBtn) activeBtn.classList.add('active');
    const targetForm = document.getElementById(tabType + '-form');
    if (targetForm) targetForm.classList.add('active');
}

// Form handling
function initFormHandling() {
    const forms = document.querySelectorAll('.signup-form');
    const contactForm = document.querySelector('.contact-form');
    
    // Handle signup forms
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            handleFormSubmission(this);
        });
    });
    
    // Handle contact form
    if (contactForm) {
        contactForm.addEventListener('submit', function(e) {
            e.preventDefault();
            handleContactForm(this);
        });
    }
    
    // Form validation
    const inputs = document.querySelectorAll('input, select, textarea');
    inputs.forEach(input => {
        input.addEventListener('blur', validateField);
        input.addEventListener('input', clearValidation);
    });
}

// Handle form submission
function handleFormSubmission(form) {
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    // Show loading state
    submitBtn.innerHTML = '<span class="loading"></span> Submitting...';
    submitBtn.disabled = true;
    
    // Validate form
    if (!validateForm(form)) {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
        return;
    }
    
    // Create FormData object
    const formData = new FormData(form);
    
    // Submit form via AJAX
    fetch('process_application.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showMessage('Application submitted successfully! We will contact you soon.', 'success');
            form.reset();
        } else {
            showMessage(data.message || 'An error occurred. Please try again.', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showMessage('Network error. Please check your connection and try again.', 'error');
    })
    .finally(() => {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
}

// Handle contact form
function handleContactForm(form) {
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    submitBtn.innerHTML = '<span class="loading"></span> Sending...';
    submitBtn.disabled = true;
    
    // Simulate form submission (replace with actual endpoint)
    setTimeout(() => {
        showMessage('Message sent successfully! We will get back to you soon.', 'success');
        form.reset();
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    }, 2000);
}

// Form validation
function validateForm(form) {
    const requiredFields = form.querySelectorAll('[required]');
    let isValid = true;
    
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            showFieldError(field, 'This field is required');
            isValid = false;
        } else if (field.type === 'email' && !isValidEmail(field.value)) {
            showFieldError(field, 'Please enter a valid email address');
            isValid = false;
        }
    });
    
    return isValid;
}

// Validate individual field
function validateField() {
    const field = this;
    
    if (field.hasAttribute('required') && !field.value.trim()) {
        showFieldError(field, 'This field is required');
    } else if (field.type === 'email' && field.value && !isValidEmail(field.value)) {
        showFieldError(field, 'Please enter a valid email address');
    } else {
        clearFieldError(field);
    }
}

// Clear validation on input
function clearValidation() {
    clearFieldError(this);
}

// Show field error
function showFieldError(field, message) {
    clearFieldError(field);
    
    field.style.borderColor = '#e74c3c';
    const errorDiv = document.createElement('div');
    errorDiv.className = 'field-error';
    errorDiv.style.color = '#e74c3c';
    errorDiv.style.fontSize = '0.9rem';
    errorDiv.style.marginTop = '0.5rem';
    errorDiv.textContent = message;
    
    field.parentNode.appendChild(errorDiv);
}

// Clear field error
function clearFieldError(field) {
    field.style.borderColor = '';
    const existingError = field.parentNode.querySelector('.field-error');
    if (existingError) {
        existingError.remove();
    }
}

// Email validation
function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

// Show message
function showMessage(message, type) {
    // Remove existing messages
    const existingMessages = document.querySelectorAll('.message');
    existingMessages.forEach(msg => msg.remove());
    
    const messageDiv = document.createElement('div');
    messageDiv.className = `message ${type}`;
    messageDiv.textContent = message;
    
    // Insert message at the top of the active form
    const activeForm = document.querySelector('.signup-form.active') || document.querySelector('.contact-form');
    if (activeForm) {
        activeForm.insertBefore(messageDiv, activeForm.firstChild);
        
        // Auto-remove message after 5 seconds
        setTimeout(() => {
            messageDiv.remove();
        }, 5000);
        
        // Scroll to message
        messageDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
}
// Debounce helper (used for resize)
function debounce(func, wait, immediate) {
    let timeout;
    return function () {
        const context = this, args = arguments;
        const later = function () {
            timeout = null;
            if (!immediate) func.apply(context, args);
        };
        const callNow = immediate && !timeout;
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
        if (callNow) func.apply(context, args);
    };
}

// Responsive menu handling on window resize
window.addEventListener('resize', debounce(() => {
    const hamburger = document.querySelector('.hamburger');
    const navMenu = document.querySelector('.nav-menu');

    if (window.innerWidth > 768) {
        if (hamburger) hamburger.classList.remove('active');
        if (navMenu) navMenu.classList.remove('active');
    }
}, 250));

// Initialize behaviors on DOM ready
document.addEventListener('DOMContentLoaded', () => {
    initFormHandling();
    initHamburgerMenu();

    // Attach proper click handlers to tab buttons to avoid relying on inline event target
    const tabButtons = document.querySelectorAll('.tab-btn');
    tabButtons.forEach(btn => {
        btn.addEventListener('click', function (e) {
            // Determine target form from onclick attribute fallback or index
            const onclick = btn.getAttribute('onclick') || '';
            const match = onclick.match(/switchTab\('(.*?)'/);
            if (match && match[1]) {
                switchTab(match[1], btn);
            } else {
                // fallback: toggle based on presence of 'intern' or 'job' in text
                const txt = btn.textContent.toLowerCase();
                if (txt.includes('intern')) switchTab('intern', btn);
                else if (txt.includes('job')) switchTab('job', btn);
            }
        });
    });

    // Activate default tab if none active
    if (!document.querySelector('.tab-btn.active')) {
        const defaultBtn = document.querySelector('.tab-btn');
        if (defaultBtn) defaultBtn.click();
    }

    // Hamburger slide-in drawer
    const hamburger = document.getElementById('hamburger');
    const drawer    = document.getElementById('navDrawer');
    const overlay   = document.getElementById('navOverlay');
    const closeBtn  = document.getElementById('navCloseBtn');

    function openNav() {
        if (!hamburger || !drawer) return;
        hamburger.classList.add('active');
        drawer.classList.add('active');
        if (overlay) overlay.classList.add('active');
        hamburger.setAttribute('aria-expanded', 'true');
        document.body.style.overflow = 'hidden';
    }

    function closeNav() {
        if (!hamburger || !drawer) return;
        hamburger.classList.remove('active');
        drawer.classList.remove('active');
        if (overlay) overlay.classList.remove('active');
        hamburger.setAttribute('aria-expanded', 'false');
        document.body.style.overflow = '';
    }

    if (hamburger) hamburger.addEventListener('click', openNav);
    if (closeBtn)  closeBtn.addEventListener('click', closeNav);
    if (overlay)   overlay.addEventListener('click', closeNav);

    if (drawer) {
        drawer.querySelectorAll('.nav-links a').forEach(link => {
            link.addEventListener('click', closeNav);
        });
    }
});