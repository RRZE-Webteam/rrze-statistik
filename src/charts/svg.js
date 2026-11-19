/**
 * Create an SVG element without interpreting supplied text as markup.
 *
 * @param {string} tag SVG element name.
 * @param {object} attributes SVG attributes.
 * @param {string|null} text Optional plain text content.
 * @returns {SVGElement} Newly created SVG element.
 */
export function svgElement(tag, attributes = {}, text = null) {
    const element = document.createElementNS('http://www.w3.org/2000/svg', tag);
    for (const [name, value] of Object.entries(attributes)) {
        element.setAttribute(name, String(value));
    }
    if (text !== null) element.textContent = text;
    return element;
}

/**
 * Choose month ticks that fit their slots, giving the selected month priority.
 * @param {string[]} labels Localized abbreviations.
 * @param {number} span Available axis width in pixels.
 * @param {number} [selected] Active month, omitted for export.
 * @returns {number[]} One-based month numbers in calendar order.
 */
export function monthTicks(labels, span, selected) {
    const step = span / 12;
    const gap = Math.max(...labels.map((label) => label.length)) * 6.8 + 5;
    const stride = Math.max(1, Math.ceil(gap / step));
    const ticks = labels.map((_, i) => i + 1).filter((month) => (month - 1) % stride === 0);
    if (!selected || ticks.includes(selected)) return ticks;
    return [...ticks.filter((month) => Math.abs(month - selected) * step >= gap), selected].sort((a, b) => a - b);
}
