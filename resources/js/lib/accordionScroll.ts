import { nextTick } from 'vue';

/** Wait for the previous panel to close and browser scroll anchoring to settle. */
export async function scrollAccordionStart(target: HTMLElement, isOpen: () => boolean) {
    await nextTick();
    requestAnimationFrame(() => {
        requestAnimationFrame(() => {
            if (!target.isConnected || !isOpen()) return;
            target.style.scrollMarginTop = '1rem';
            target.scrollIntoView({
                block: 'start',
                inline: 'nearest',
                behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'instant' : 'smooth',
            });
        });
    });
}

/** Delegated click handler for native details, including keyboard activation. */
export function handleAccordionClick(event: MouseEvent) {
    if (event.defaultPrevented || !(event.target instanceof Element)) return;
    const summary = event.target.closest('summary');
    const card = summary?.parentElement;
    if (!(card instanceof HTMLDetailsElement) || card.open) return;
    void scrollAccordionStart(card, () => card.open);
}
