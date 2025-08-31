/**
 * AnimationManager - Handles micro-interactions and confirmation animations
 */

class AnimationManager {
    constructor() {
        this.activeAnimations = new Map();
        this.observers = new Map();
        this.prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        
        this.init();
    }

    init() {
        this.setupIntersectionObserver();
        this.setupFormFieldAnimations();
        this.setupHoverEffects();
        this.setupClickEffects();
        
        // Listen for reduced motion preference changes
        window.matchMedia('(prefers-reduced-motion: reduce)').addEventListener('change', (e) => {
            this.prefersReducedMotion = e.matches;
        });
    }

    /**
     * Setup intersection observer for scroll animations
     */
    setupIntersectionObserver() {
        if (this.prefersReducedMotion) return;

        this.intersectionObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    this.animateElementEntry(entry.target);
                }
            });
        }, {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        });

        // Observe elements that should animate on scroll
        document.querySelectorAll('.animate-on-scroll').forEach(el => {
            this.intersectionObserver.observe(el);
        });
    }

    /**
     * Setup form field animations
     */
    setupFormFieldAnimations() {
        document.addEventListener('focusin', (e) => {
            if (e.target.matches('input, select, textarea')) {
                this.animateFieldFocus(e.target);
            }
        });

        document.addEventListener('input', (e) => {
            if (e.target.matches('input, select, textarea')) {
                this.animateFieldInput(e.target);
            }
        });
    }

    /**
     * Setup hover effects
     */
    setupHoverEffects() {
        if (this.prefersReducedMotion) return;

        // Add hover effects to buttons and interactive elements
        document.addEventListener('mouseenter', (e) => {
            if (e.target.matches('.wizard-btn, .suggestion-button, .wizard-step-indicator.available')) {
                this.animateHoverEnter(e.target);
            }
        }, true);

        document.addEventListener('mouseleave', (e) => {
            if (e.target.matches('.wizard-btn, .suggestion-button, .wizard-step-indicator.available')) {
                this.animateHoverLeave(e.target);
            }
        }, true);
    }

    /**
     * Setup click effects
     */
    setupClickEffects() {
        document.addEventListener('click', (e) => {
            if (e.target.matches('.wizard-btn, .suggestion-button')) {
                this.animateClick(e.target, e);
            }
        });
    }

    /**
     * Animate element entry (scroll into view)
     */
    animateElementEntry(element) {
        if (this.prefersReducedMotion) return;

        element.classList.add('form-field-enter');
        
        // Stagger animation for multiple elements
        const siblings = Array.from(element.parentNode.children);
        const index = siblings.indexOf(element);
        
        element.style.animationDelay = `${index * 0.1}s`;
        
        // Clean up after animation
        setTimeout(() => {
            element.classList.remove('form-field-enter');
            element.style.animationDelay = '';
        }, 600 + (index * 100));
    }

    /**
     * Animate field focus
     */
    animateFieldFocus(field) {
        if (this.prefersReducedMotion) return;

        field.classList.add('form-field-focus');
        
        // Add focus ring animation
        const focusRing = document.createElement('div');
        focusRing.className = 'absolute inset-0 rounded-md pointer-events-none';
        focusRing.style.animation = 'focusPulse 0.3s ease-out';
        
        // Make sure parent is relative
        if (getComputedStyle(field.parentNode).position === 'static') {
            field.parentNode.style.position = 'relative';
        }
        
        field.parentNode.appendChild(focusRing);
        
        // Clean up
        setTimeout(() => {
            field.classList.remove('form-field-focus');
            if (focusRing.parentNode) {
                focusRing.parentNode.removeChild(focusRing);
            }
        }, 300);
    }

    /**
     * Animate field input (typing)
     */
    animateFieldInput(field) {
        if (this.prefersReducedMotion) return;

        // Add subtle bounce on input
        field.style.transform = 'scale(1.02)';
        field.style.transition = 'transform 0.1s ease-out';
        
        setTimeout(() => {
            field.style.transform = '';
            setTimeout(() => {
                field.style.transition = '';
            }, 100);
        }, 100);
    }

    /**
     * Animate hover enter
     */
    animateHoverEnter(element) {
        if (this.prefersReducedMotion) return;

        element.classList.add('hover-lift');
        
        // Add glow effect for primary buttons
        if (element.classList.contains('wizard-btn-primary')) {
            element.classList.add('hover-glow');
        }
    }

    /**
     * Animate hover leave
     */
    animateHoverLeave(element) {
        if (this.prefersReducedMotion) return;

        element.classList.remove('hover-lift', 'hover-glow');
    }

    /**
     * Animate click with ripple effect
     */
    animateClick(element, event) {
        if (this.prefersReducedMotion) return;

        // Create ripple effect
        const ripple = document.createElement('span');
        const rect = element.getBoundingClientRect();
        const size = Math.max(rect.width, rect.height);
        const x = event.clientX - rect.left - size / 2;
        const y = event.clientY - rect.top - size / 2;
        
        ripple.style.cssText = `
            position: absolute;
            width: ${size}px;
            height: ${size}px;
            left: ${x}px;
            top: ${y}px;
            background: rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            transform: scale(0);
            animation: ripple 0.6s ease-out;
            pointer-events: none;
        `;
        
        // Make sure element has relative positioning
        const originalPosition = element.style.position;
        if (getComputedStyle(element).position === 'static') {
            element.style.position = 'relative';
        }
        
        element.appendChild(ripple);
        
        // Clean up ripple
        setTimeout(() => {
            if (ripple.parentNode) {
                ripple.parentNode.removeChild(ripple);
            }
            if (!originalPosition) {
                element.style.position = '';
            }
        }, 600);

        // Add click scale effect
        element.style.transform = 'scale(0.95)';
        element.style.transition = 'transform 0.1s ease-out';
        
        setTimeout(() => {
            element.style.transform = '';
            setTimeout(() => {
                element.style.transition = '';
            }, 100);
        }, 100);
    }

    /**
     * Animate step completion
     */
    animateStepCompletion(stepElement, stepIndex) {
        if (this.prefersReducedMotion) return;

        const indicator = stepElement.querySelector('.wizard-step-indicator');
        if (indicator) {
            // Add success animation
            indicator.classList.add('success-celebration');
            
            // Create checkmark animation
            const checkmark = document.createElement('div');
            checkmark.innerHTML = `
                <svg class="w-4 h-4 success-checkmark" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                </svg>
            `;
            
            indicator.innerHTML = '';
            indicator.appendChild(checkmark);
            
            // Clean up after animation
            setTimeout(() => {
                indicator.classList.remove('success-celebration');
            }, 1000);
        }

        // Animate progress bar update
        this.animateProgressUpdate(stepIndex);
    }

    /**
     * Animate progress bar update
     */
    animateProgressUpdate(completedSteps) {
        if (this.prefersReducedMotion) return;

        const progressBar = document.querySelector('.wizard-progress-bar');
        if (progressBar) {
            // Add pulse effect
            progressBar.style.animation = 'heartbeat 0.8s ease-out';
            
            setTimeout(() => {
                progressBar.style.animation = '';
            }, 800);
        }
    }

    /**
     * Animate form submission success
     */
    animateFormSuccess(formElement) {
        if (this.prefersReducedMotion) return;

        // Create success overlay
        const successOverlay = document.createElement('div');
        successOverlay.className = 'absolute inset-0 bg-green-50 border-2 border-green-200 rounded-lg flex items-center justify-center z-10';
        successOverlay.style.animation = 'fadeInUp 0.5s ease-out';
        
        successOverlay.innerHTML = `
            <div class="text-center">
                <div class="success-celebration mb-4">
                    <svg class="w-16 h-16 text-green-500 mx-auto success-checkmark" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-green-800 mb-2">¡Éxito!</h3>
                <p class="text-green-600">Formulario enviado correctamente</p>
            </div>
        `;

        // Make sure form has relative positioning
        if (getComputedStyle(formElement).position === 'static') {
            formElement.style.position = 'relative';
        }

        formElement.appendChild(successOverlay);

        // Auto-remove after delay
        setTimeout(() => {
            if (successOverlay.parentNode) {
                successOverlay.style.animation = 'fadeOut 0.3s ease-in forwards';
                setTimeout(() => {
                    if (successOverlay.parentNode) {
                        successOverlay.parentNode.removeChild(successOverlay);
                    }
                }, 300);
            }
        }, 3000);
    }

    /**
     * Animate validation error
     */
    animateValidationError(fieldElement) {
        if (this.prefersReducedMotion) return;

        fieldElement.style.animation = 'errorShake 0.5s ease-out';
        
        setTimeout(() => {
            fieldElement.style.animation = '';
        }, 500);
    }

    /**
     * Animate suggestion selection
     */
    animateSuggestionSelect(suggestionButton, targetField) {
        if (this.prefersReducedMotion) return;

        // Highlight the suggestion
        suggestionButton.style.animation = 'bounce 0.6s ease-out';
        
        // Create flying animation to target field
        const rect1 = suggestionButton.getBoundingClientRect();
        const rect2 = targetField.getBoundingClientRect();
        
        const flyingText = document.createElement('div');
        flyingText.textContent = suggestionButton.textContent;
        flyingText.className = 'fixed z-50 px-2 py-1 bg-blue-500 text-accent text-xs rounded pointer-events-none';
        flyingText.style.left = rect1.left + 'px';
        flyingText.style.top = rect1.top + 'px';
        
        document.body.appendChild(flyingText);
        
        // Animate to target
        flyingText.style.transition = 'all 0.8s cubic-bezier(0.25, 0.46, 0.45, 0.94)';
        flyingText.style.transform = `translate(${rect2.left - rect1.left}px, ${rect2.top - rect1.top}px) scale(0.8)`;
        flyingText.style.opacity = '0';
        
        // Clean up
        setTimeout(() => {
            if (flyingText.parentNode) {
                flyingText.parentNode.removeChild(flyingText);
            }
            suggestionButton.style.animation = '';
        }, 800);
    }

    /**
     * Animate wizard step transition
     */
    animateWizardTransition(fromStep, toStep, direction = 'forward') {
        if (this.prefersReducedMotion) return;

        const fromElement = document.querySelector(`[data-step="${fromStep}"]`);
        const toElement = document.querySelector(`[data-step="${toStep}"]`);
        
        if (!fromElement || !toElement) return;

        // Animate out current step
        fromElement.classList.add('wizard-step-exit', `wizard-step-exit-${direction}`);
        
        // Animate in new step
        toElement.classList.remove('hidden');
        toElement.classList.add('wizard-step-enter', `wizard-step-enter-${direction}`);
        
        // Clean up after animation
        setTimeout(() => {
            fromElement.classList.add('hidden');
            fromElement.classList.remove('wizard-step-exit', `wizard-step-exit-${direction}`);
            toElement.classList.remove('wizard-step-enter', `wizard-step-enter-${direction}`);
            toElement.classList.add('active');
        }, 500);
    }

    /**
     * Add scroll-triggered animations to new elements
     */
    observeElement(element) {
        if (this.intersectionObserver && !this.prefersReducedMotion) {
            this.intersectionObserver.observe(element);
        }
    }

    /**
     * Remove element from observation
     */
    unobserveElement(element) {
        if (this.intersectionObserver) {
            this.intersectionObserver.unobserve(element);
        }
    }

    /**
     * Clean up all animations and observers
     */
    destroy() {
        if (this.intersectionObserver) {
            this.intersectionObserver.disconnect();
        }
        
        this.activeAnimations.clear();
        this.observers.clear();
    }
}

// Export for use in other modules
window.AnimationManager = AnimationManager;

// Auto-initialize
document.addEventListener('DOMContentLoaded', () => {
    if (!window.animationManager) {
        window.animationManager = new AnimationManager();
    }
});