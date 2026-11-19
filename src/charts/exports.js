import { renderChartSvg } from './render.js';

/** @typedef {import('./model.js').ChartModel} ChartModel */

const SVG_NAMESPACE = 'http://www.w3.org/2000/svg';
const EXPORT_WIDTH = 1200;
const PNG_SCALE = 2;

/**
 * Escape one CSV field without formatting the underlying numeric value.
 *
 * @param {string|number|null|undefined} value Field value.
 * @returns {string} RFC 4180-compatible CSV field.
 */
function csvField(value) {
    const text = value == null ? '' : String(value);
    return /[",\r\n]/u.test(text) ? `"${text.replace(/"/gu, '""')}"` : text;
}

/**
 * Select visible series in the same chronological order as the data model.
 *
 * @param {ChartModel} model Normalized chart model.
 * @param {number[]} visibleYears Currently visible years.
 * @returns {Array<{year:number, values:Array<number|null>}>} Exported series.
 */
function exportSeries(model, visibleYears) {
    const selected = new Set(visibleYears);
    return model.series.filter((series) => selected.has(series.year));
}

/**
 * Create a CSV document from the same monthly values used by the chart/table.
 * Missing values are empty fields; zero is a real exported measurement.
 *
 * @param {ChartModel} model Normalized chart model.
 * @param {number[]} visibleYears Currently visible years.
 * @returns {string} CSV text with CRLF endings and no byte-order mark.
 */
export function createCsv(model, visibleYears) {
    const series = exportSeries(model, visibleYears);
    const rows = [[model.labels.month, ...series.map((item) => item.year)]];

    for (let month = 0; month < 12; month += 1) {
        rows.push([
            model.months[month],
            ...series.map((item) => {
                const value = item.values[month];
                return typeof value === 'number' && Number.isFinite(value) ? value : '';
            }),
        ]);
    }

    return `${rows.map((row) => row.map(csvField).join(',')).join('\r\n')}\r\n`;
}

/**
 * Render a complete, standalone SVG using the shared chart renderer.
 * Export mode includes metadata but excludes temporary interaction highlights.
 *
 * @param {ChartModel} model Normalized chart model.
 * @param {number[]} visibleYears Currently visible years.
 * @returns {string} Serialized SVG document at a width of 1,200 pixels.
 */
export function serializeChartSvg(model, visibleYears) {
    const svg = renderChartSvg(model, visibleYears, {
        width: EXPORT_WIDTH,
        exportMode: true,
    });

    if (svg.namespaceURI !== SVG_NAMESPACE || svg.localName !== 'svg') {
        throw new TypeError('The chart renderer did not return an SVG element.');
    }

    return new XMLSerializer().serializeToString(svg);
}

/**
 * Generate a stable, filesystem-safe filename for the exported metric/years.
 *
 * @param {ChartModel} model Normalized chart model.
 * @param {number[]} visibleYears Currently visible years.
 * @param {'csv'|'svg'|'png'} extension File extension.
 * @returns {string} Export filename.
 */
function exportFilename(model, visibleYears, extension) {
    const years = exportSeries(model, visibleYears).map((series) => series.year);
    const metric = String(model.metric).replace(/[^a-z0-9_-]/giu, '-');
    const first = years[0] ?? model.years[0];
    const last = years[years.length - 1] ?? model.years[model.years.length - 1];
    return `rrze-statistik-${metric}-${first}-${last}.${extension}`;
}

/**
 * Decode an SVG object URL as a browser image, supporting widget disposal.
 *
 * @param {string} url SVG object URL.
 * @param {AbortSignal} signal Widget lifetime signal.
 * @returns {Promise<HTMLImageElement>} Decoded image with intrinsic dimensions.
 */
function loadSvgImage(url, signal) {
    return new Promise((resolve, reject) => {
        const image = new Image();

        /**
         * Release image callbacks after loading, failure or cancellation.
         *
         * @returns {void}
         */
        function detach() {
            image.onload = null;
            image.onerror = null;
            signal.removeEventListener('abort', abort);
        }

        /**
         * Stop an image load when its originating widget is disposed.
         *
         * @returns {void}
         */
        function abort() {
            detach();
            image.removeAttribute('src');
            reject(new DOMException('Export cancelled.', 'AbortError'));
        }

        /**
         * Resolve a decoded SVG image.
         *
         * @returns {void}
         */
        function loaded() {
            detach();
            resolve(image);
        }

        /**
         * Reject a malformed or unsupported SVG image.
         *
         * @returns {void}
         */
        function failed() {
            detach();
            reject(new Error('The SVG image could not be decoded.'));
        }

        image.onload = loaded;
        image.onerror = failed;
        signal.addEventListener('abort', abort, { once: true });
        if (signal.aborted) {
            abort();
        } else {
            image.src = url;
        }
    });
}

/**
 * Convert the export SVG into an opaque PNG without contacting a server.
 *
 * @param {string} markup Complete SVG document.
 * @param {AbortSignal} signal Widget lifetime signal.
 * @returns {Promise<Blob>} PNG at twice the SVG dimensions.
 */
async function createPngBlob(markup, signal) {
    const url = URL.createObjectURL(new Blob([markup], { type: 'image/svg+xml;charset=utf-8' }));
    try {
        const image = await loadSvgImage(url, signal);
        if (signal.aborted) {
            throw new DOMException('Export cancelled.', 'AbortError');
        }
        if (!image.naturalWidth || !image.naturalHeight) {
            throw new Error('The exported chart has invalid dimensions.');
        }

        const canvas = document.createElement('canvas');
        canvas.width = EXPORT_WIDTH * PNG_SCALE;
        canvas.height = Math.ceil(image.naturalHeight * canvas.width / image.naturalWidth);
        const context = canvas.getContext('2d');
        if (!context) {
            throw new Error('Canvas rendering is unavailable.');
        }

        context.fillStyle = '#ffffff';
        context.fillRect(0, 0, canvas.width, canvas.height);
        context.drawImage(image, 0, 0, canvas.width, canvas.height);
        return await new Promise((resolve, reject) => {
            canvas.toBlob((blob) => {
                if (blob) {
                    resolve(blob);
                } else {
                    reject(new Error('The PNG image could not be encoded.'));
                }
            }, 'image/png');
        });
    } finally {
        URL.revokeObjectURL(url);
    }
}

/**
 * Mount local chart export controls and their accessible status messages.
 * The callback is read when an action starts, so it uses the latest selection.
 *
 * @param {HTMLElement} toolbar Parent element for export controls.
 * @param {ChartModel} model Normalized chart model.
 * @param {() => number[]} getVisibleYears Read the currently visible years.
 * @returns {() => void} Dispose listeners, pending loads and download URLs.
 */
export function mountExportControls(toolbar, model, getVisibleYears) {
    const controller = new AbortController();
    const urls = new Map();
    const wrapper = document.createElement('div');
    wrapper.className = 'rrze-statistik-export-controls';
    const status = document.createElement('p');
    status.className = 'rrze-statistik-export-status';
    status.setAttribute('role', 'status');
    status.setAttribute('aria-live', 'polite');
    status.setAttribute('aria-atomic', 'true');
    const buttons = [];
    let busy = false;

    /**
     * Release a tracked download URL after the browser has consumed its link.
     *
     * @param {string} url Object URL to release.
     * @returns {void}
     */
    function releaseUrl(url) {
        const timer = urls.get(url);
        if (timer !== undefined) {
            clearTimeout(timer);
            URL.revokeObjectURL(url);
            urls.delete(url);
        }
    }

    /**
     * Offer a generated file for download and schedule resource cleanup.
     *
     * @param {Blob} blob Generated file content.
     * @param {string} filename Suggested download name.
     * @returns {void}
     */
    function download(blob, filename) {
        const url = URL.createObjectURL(blob);
        const anchor = document.createElement('a');
        anchor.href = url;
        anchor.download = filename;
        anchor.hidden = true;
        document.body.append(anchor);
        // Deferred revocation allows browsers to begin reading the download.
        const timer = setTimeout(() => releaseUrl(url), 1000);
        urls.set(url, timer);
        try {
            anchor.click();
        } catch (error) {
            releaseUrl(url);
            throw error;
        } finally {
            anchor.remove();
        }
    }

    /**
     * Execute one export action and announce its outcome without moving focus.
     *
     * @param {'copy'|'csv'|'svg'|'png'} action Requested export action.
     * @returns {Promise<void>} Completion after export or error reporting.
     */
    async function execute(action) {
        if (busy || controller.signal.aborted) {
            return;
        }
        busy = true;
        buttons.forEach((button) => button.setAttribute('aria-disabled', 'true'));
        status.textContent = model.labels.exporting;
        const years = [...getVisibleYears()];

        try {
            if (action === 'copy') {
                if (!navigator.clipboard?.writeText) {
                    throw new Error('Clipboard access is unavailable.');
                }
                await navigator.clipboard.writeText(createCsv(model, years));
            } else if (action === 'csv') {
                download(new Blob(['\uFEFF', createCsv(model, years)], {
                    type: 'text/csv;charset=utf-8',
                }), exportFilename(model, years, 'csv'));
            } else {
                const markup = serializeChartSvg(model, years);
                const blob = action === 'png'
                    ? await createPngBlob(markup, controller.signal)
                    : new Blob([markup], { type: 'image/svg+xml;charset=utf-8' });
                if (!controller.signal.aborted) {
                    download(blob, exportFilename(model, years, action));
                }
            }
            if (!controller.signal.aborted) {
                status.textContent = action === 'copy' ? model.labels.copied : model.labels.exported;
            }
        } catch (error) {
            if (!controller.signal.aborted) {
                status.textContent = action === 'copy' ? model.labels.copyError : model.labels.exportError;
            }
        } finally {
            busy = false;
            buttons.forEach((button) => button.removeAttribute('aria-disabled'));
        }
    }

    const actions = [
        ['copy', model.labels.copyCsv],
        ['csv', model.labels.downloadCsv],
        ['svg', model.labels.downloadSvg],
        ['png', model.labels.downloadPng],
    ];
    actions.forEach(([action, label]) => {
        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'button';
        button.dataset.export = action;
        button.textContent = label;
        button.addEventListener('click', () => execute(action), { signal: controller.signal });
        buttons.push(button);
        wrapper.append(button);
    });
    wrapper.append(status);
    toolbar.append(wrapper);

    /**
     * Dispose all export controls and any pending browser resources.
     *
     * @returns {void}
     */
    return function disposeExports() {
        controller.abort();
        [...urls.keys()].forEach(releaseUrl);
        wrapper.remove();
    };
}
