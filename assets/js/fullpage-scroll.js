/**
 * Fullpage Scroll - Vertical section snapping with smooth transitions
 */

document.addEventListener('DOMContentLoaded', function() {
    
    // Set up constants and variables
    const SCROLL_COOLDOWN = 1000; // ms to wait between scroll actions
    const ACTIVE_CLASS = 'active';
    const sections = document.querySelectorAll('.section');
    const navDots = document.querySelector('.section-nav');
    
    let isScrolling = false;
    let currentSection = 0;
    let touchStartY = 0;
    let touchEndY = 0;
    let scrollTimeout;
    
    // Initialize the page
    function init() {
        // Create navigation dots if they don't exist
        if (!navDots && sections.length > 1) {
            createNavDots();
        }
        
        // Set initial active section
        activateSection(currentSection);
        
        // Add event listeners
        addEventListeners();
        
        // Disable default scroll behavior
        document.body.style.overflow = 'hidden';
        
        // Add scroll arrows
        addScrollArrows();
        
        // Check URL hash for direct section navigation
        checkURLHash();
    }
    
    // Create navigation dots
    function createNavDots() {
        const nav = document.createElement('div');
        nav.className = 'section-nav';
        
        sections.forEach((section, index) => {
            const dot = document.createElement('div');
            dot.className = 'section-nav-dot';
            dot.dataset.section = index;
            
            dot.addEventListener('click', function() {
                navigateToSection(index);
            });
            
            nav.appendChild(dot);
        });
        
        document.body.appendChild(nav);
    }
    
    // Add scroll arrows
    function addScrollArrows() {
        sections.forEach((section, index) => {
            // Don't add arrow to last section
            if (index < sections.length - 1) {
                const arrow = document.createElement('div');
                arrow.className = 'scroll-arrow';
                arrow.innerHTML = '<i class="fas fa-chevron-down"></i>';
                
                arrow.addEventListener('click', function() {
                    navigateToSection(index + 1);
                });
                
                section.appendChild(arrow);
            }
        });
    }
    
    // Check URL hash for direct navigation
    function checkURLHash() {
        const hash = window.location.hash;
        if (hash) {
            const targetSection = document.querySelector(hash);
            if (targetSection) {
                const index = Array.from(sections).indexOf(targetSection);
                if (index !== -1) {
                    setTimeout(() => {
                        navigateToSection(index);
                    }, 500); // Short delay to ensure page is ready
                }
            }
        }
    }
    
    // Add event listeners
    function addEventListeners() {
        // Mouse wheel scroll
        window.addEventListener('wheel', handleMouseWheel, { passive: false });
        
        // Keyboard navigation
        document.addEventListener('keydown', handleKeyDown);
        
        // Touch events for mobile
        document.addEventListener('touchstart', handleTouchStart, { passive: true });
        document.addEventListener('touchmove', handleTouchMove, { passive: false });
        document.addEventListener('touchend', handleTouchEnd, { passive: true });
        
        // Window resize
        window.addEventListener('resize', handleResize);
        
        // Update on URL hash change
        window.addEventListener('hashchange', checkURLHash);
    }
    
    // Handle mouse wheel scrolling
    function handleMouseWheel(e) {
        e.preventDefault();
        
        if (isScrolling) return;
        
        const delta = e.deltaY;
        
        if (delta > 0) {
            // Scroll down
            navigateToSection(currentSection + 1);
        } else {
            // Scroll up
            navigateToSection(currentSection - 1);
        }
    }
    
    // Handle keyboard navigation
    function handleKeyDown(e) {
        // Arrow keys, Page Up/Down, Home/End
        switch (e.key) {
            case 'ArrowDown':
            case 'PageDown':
                e.preventDefault();
                navigateToSection(currentSection + 1);
                break;
            case 'ArrowUp':
            case 'PageUp':
                e.preventDefault();
                navigateToSection(currentSection - 1);
                break;
            case 'Home':
                e.preventDefault();
                navigateToSection(0);
                break;
            case 'End':
                e.preventDefault();
                navigateToSection(sections.length - 1);
                break;
        }
    }
    
    // Handle touch start
    function handleTouchStart(e) {
        touchStartY = e.touches[0].clientY;
    }
    
    // Handle touch move
    function handleTouchMove(e) {
        if (isScrolling) {
            e.preventDefault();
            return;
        }
        
        touchEndY = e.touches[0].clientY;
    }
    
    // Handle touch end
    function handleTouchEnd() {
        const touchDiff = touchStartY - touchEndY;
        
        // Check if it's a significant swipe (more than 50px)
        if (Math.abs(touchDiff) > 50) {
            if (touchDiff > 0) {
                // Swipe up = go down
                navigateToSection(currentSection + 1);
            } else {
                // Swipe down = go up
                navigateToSection(currentSection - 1);
            }
        }
    }
    
    // Handle window resize
    function handleResize() {
        // Immediately scroll to current section
        scrollToSection(currentSection, false);
    }
    
    // Navigate to a specific section
    function navigateToSection(index) {
        // Check if index is valid
        if (index < 0 || index >= sections.length || isScrolling) {
            return;
        }
        
        scrollToSection(index);
    }
    
    // Scroll to a section
    function scrollToSection(index, smooth = true) {
        isScrolling = true;
        currentSection = index;
        
        // Update active class on sections
        activateSection(index);
        
        // Scroll to section
        const targetSection = sections[index];
        const targetPosition = targetSection.offsetTop;
        
        // Update URL hash without triggering scroll
        const sectionId = targetSection.id;
        if (sectionId) {
            window.history.replaceState(null, null, `#${sectionId}`);
        }
        
        // Perform the scroll
        window.scrollTo({
            top: targetPosition,
            behavior: smooth ? 'smooth' : 'auto'
        });
        
        // Reset scrolling state after animation
        clearTimeout(scrollTimeout);
        scrollTimeout = setTimeout(() => {
            isScrolling = false;
        }, SCROLL_COOLDOWN);
    }
    
    // Activate a section and update navigation
    function activateSection(index) {
        // Update sections
        sections.forEach((section, i) => {
            if (i === index) {
                section.classList.add(ACTIVE_CLASS);
            } else {
                section.classList.remove(ACTIVE_CLASS);
            }
        });
        
        // Update navigation dots
        const dots = document.querySelectorAll('.section-nav-dot');
        dots.forEach((dot, i) => {
            if (i === index) {
                dot.classList.add(ACTIVE_CLASS);
            } else {
                dot.classList.remove(ACTIVE_CLASS);
            }
        });
        
        // Update scroll arrows visibility
        const arrows = document.querySelectorAll('.scroll-arrow');
        arrows.forEach((arrow, i) => {
            if (i === sections.length - 1) {
                arrow.style.display = 'none';
            }
        });
    }
    
    // Initialize on page load
    init();
}); 