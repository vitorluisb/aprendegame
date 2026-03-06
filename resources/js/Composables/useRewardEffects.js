import { gsap } from 'gsap';
import lottie from 'lottie-web';
import confettiCelebration from '@/lottie/confetti/celebration.json';

let celebrationAnimation = null;

export function useRewardEffects() {
    function animateXpCounter(counterRef, targetValue) {
        gsap.killTweensOf(counterRef);

        gsap.fromTo(
            counterRef,
            { value: 0 },
            {
                value: targetValue,
                duration: 0.75,
                ease: 'power3.out',
                snap: { value: 1 },
            },
        );
    }

    function animateProgressBar(element, progressValue) {
        if (!element) {
            return;
        }

        gsap.to(element, {
            width: `${Math.max(0, Math.min(100, progressValue))}%`,
            duration: 0.42,
            ease: 'power2.out',
        });
    }

    function animateFeedbackCard(element, isCorrect) {
        if (!element) {
            return;
        }

        gsap.killTweensOf(element);

        const timeline = gsap.timeline();
        timeline.fromTo(
            element,
            { y: 10, autoAlpha: 0, scale: 0.98 },
            { y: 0, autoAlpha: 1, scale: 1, duration: 0.24, ease: 'back.out(1.2)' },
        );

        if (!isCorrect) {
            timeline.fromTo(
                element,
                { x: -4 },
                { x: 0, duration: 0.18, ease: 'power2.out' },
                0.04,
            );
        }
    }

    function playCelebration(containerElement) {
        if (!containerElement) {
            return false;
        }

        if (celebrationAnimation) {
            celebrationAnimation.destroy();
            celebrationAnimation = null;
        }

        try {
            celebrationAnimation = lottie.loadAnimation({
                container: containerElement,
                renderer: 'svg',
                loop: false,
                autoplay: true,
                animationData: confettiCelebration,
            });
        } catch (_error) {
            celebrationAnimation = null;

            return false;
        }

        celebrationAnimation.addEventListener('complete', () => {
            celebrationAnimation?.destroy();
            celebrationAnimation = null;
        });

        return true;
    }

    function cleanupCelebration() {
        if (!celebrationAnimation) {
            return;
        }

        celebrationAnimation.destroy();
        celebrationAnimation = null;
    }

    return {
        animateXpCounter,
        animateProgressBar,
        animateFeedbackCard,
        playCelebration,
        cleanupCelebration,
    };
}
