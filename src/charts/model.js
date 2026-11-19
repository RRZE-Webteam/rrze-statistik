/** @typedef {{year: number, values: Array<number|null>}} ChartSeries */
/**
 * A normalized view shared by rendering, navigation, tables, and export.
 * @typedef {Object} ChartModel
 * @property {string} metric
 * @property {string} title
 * @property {string} unit
 * @property {string} description
 * @property {string} sourceUrl
 * @property {string} sourceText
 * @property {string} locale
 * @property {number[]} years
 * @property {string[]} months
 * @property {ChartSeries[]} series
 * @property {string} displayType
 * @property {Object<string, string>} labels
 */

export const METRICS = ['visits', 'hits', 'hosts', 'files', 'kbytes'];
export const DISPLAY_TYPES = ['spline', 'areaspline', 'column', 'bar'];

export const DEFAULT_LABELS = Object.freeze({
    month: 'Month', year: 'Year', chooseYear: 'Choose a year', chooseMonth: 'Choose a month',
    visibleYears: 'Visible years', showTable: 'Show data table',
    tableCaption: '{title}: monthly values', noData: 'No data available',
    emptyChart: 'No data available for this period.',
    renderError: 'The chart could not be displayed. You can still use the data table and CSV export.',
    loading: 'Loading chart…', copyCsv: 'Copy CSV', downloadCsv: 'Download CSV',
    downloadSvg: 'Download SVG', downloadPng: 'Download PNG', copied: 'CSV copied to clipboard.',
    copyError: 'Copying failed. Please use the CSV download.',
    exportError: 'The export failed. Please try again.', exporting: 'Preparing download…',
    exported: 'Download prepared.', lastVisibleYear: 'At least one year must remain visible.',
    summary: '{range}. Minimum: {min} ({minDate}). Maximum: {max} ({maxDate}).',
    range: '{first}–{last}', minimum: 'Minimum', maximum: 'Maximum',
    navigationHelp: 'Choose a year and use the month slider to explore the values.',
    missingValue: 'No data available', source: 'Source',
    preferencesError: 'Your selection could not be saved. It remains active until this page is reloaded.',
    preferencesSaving: 'Saving selection…', preferencesSaved: 'Selection saved.',
});

/**
 * Substitute named placeholders without interpreting translations as markup.
 * @param {string} template Translated text with {name} placeholders.
 * @param {Object<string, string|number>} values Replacement values.
 * @returns {string} Plain text.
 */
export function interpolate(template, values) {
    return String(template).replace(/\{(\w+)\}/g, (match, key) =>
        Object.prototype.hasOwnProperty.call(values, key) ? String(values[key]) : match);
}

/**
 * Resolve WordPress locales, including informal/formal variants, for Intl.
 * @param {unknown} input Locale supplied by PHP.
 * @returns {string} Supported BCP 47 locale.
 */
export function normalizeLocale(input) {
    const candidate = typeof input === 'string' ? input.replace(/_/g, '-') : 'en';
    for (const locale of [candidate, candidate.split('-').slice(0, 2).join('-'), 'en']) {
        try {
            if (Intl.NumberFormat.supportedLocalesOf([locale]).length) {
                return new Intl.NumberFormat(locale).resolvedOptions().locale;
            }
        } catch (_error) {
            // WordPress variants need not be valid BCP 47 tags.
        }
    }
    return 'en';
}

/**
 * Preserve measured zero while rejecting missing, negative, or malformed values.
 * @param {unknown} value Raw Webalizer value.
 * @returns {number|null} Finite, nonnegative value or an explicit gap.
 */
export function normalizeValue(value) {
    if (typeof value !== 'number' && typeof value !== 'string') return null;
    if (typeof value === 'string' && value.trim() === '') return null;
    const number = Number(value);
    return Number.isFinite(number) && number >= 0 && number <= Number.MAX_SAFE_INTEGER ? number : null;
}

/**
 * Normalize one metric into three calendar years, retaining all twelve months.
 * Later records for the same month replace earlier records, matching the old UI.
 * @param {Object} transfer PHP's RRZESTATISTIKTRANSFER payload.
 * @param {string} metric Supported metric key.
 * @returns {ChartModel} Library-independent chart model.
 * @throws {RangeError} When the metric is unsupported.
 */
export function createChartModel(transfer = {}, metric) {
    if (!METRICS.includes(metric)) throw new RangeError(`Unsupported statistic: ${metric}`);
    transfer = transfer && typeof transfer === 'object' ? transfer : {};
    const suppliedYear = Number(transfer.currentYear);
    const currentYear = Number.isInteger(suppliedYear) && suppliedYear >= 1970 && suppliedYear <= 9998
        ? suppliedYear : new Date().getFullYear();
    const locale = normalizeLocale(transfer.locale);
    const years = [currentYear - 2, currentYear - 1, currentYear];
    const series = years.map((year) => ({year, values: Array(12).fill(null)}));
    const rows = Array.isArray(transfer.linechartDataset) ? transfer.linechartDataset : [];
    for (const row of rows) {
        if (!row || typeof row !== 'object') continue;
        const year = Number(row.year);
        const month = Number(row.month);
        if (!Number.isInteger(month) || month < 1 || month > 12) continue;
        const target = series.find((entry) => entry.year === year);
        if (target) target.values[month - 1] = normalizeValue(row[metric]);
    }
    const monthFormatter = new Intl.DateTimeFormat(locale, {month: 'short', timeZone: 'UTC'});
    const providedMonths = transfer.abscissaDescriptiontext;
    const months = Array.from({length: 12}, (_, index) =>
        Array.isArray(providedMonths) && typeof providedMonths[index] === 'string'
            ? providedMonths[index] : monthFormatter.format(new Date(Date.UTC(2020, index, 1))));
    const metadata = transfer.languagePackage?.[metric] || {};
    const labels = {...DEFAULT_LABELS};
    for (const key of Object.keys(labels)) {
        if (typeof transfer.labels?.[key] === 'string') labels[key] = transfer.labels[key];
    }
    return {
        metric, title: String(metadata.headline_chart || metric), unit: String(metadata.ordinate_desc || metric),
        description: String(transfer.descriptions?.[metric] || ''),
        sourceUrl: String(transfer.sourceUrl || ''), sourceText: String(transfer.sourceText || labels.source),
        locale, years, months, series, labels,
        displayType: DISPLAY_TYPES.includes(transfer.displayType) ? transfer.displayType : 'spline',
    };
}

/**
 * Select the latest recorded month; keep an empty model navigable and predictable.
 * @param {ChartModel} model Normalized model.
 * @returns {{visibleYears:number[], selectedYear:number, selectedMonth:number}} Initial UI state.
 */
export function createInitialState(model) {
    for (const series of [...model.series].reverse()) {
        for (let index = 11; index >= 0; index--) {
            if (series.values[index] !== null) {
                return {visibleYears: [...model.years], selectedYear: series.year, selectedMonth: index + 1};
            }
        }
    }
    return {visibleYears: [...model.years], selectedYear: model.years[2], selectedMonth: 1};
}

/**
 * Format display values without changing the raw value used in exports.
 * @param {ChartModel} model Chart labels and locale.
 * @param {number|null} value Value to display.
 * @returns {string} Localized value or missing-data message.
 */
export function formatValue(model, value) {
    return value === null || value === undefined
        ? model.labels.missingValue
        : new Intl.NumberFormat(model.locale, {maximumFractionDigits: 20}).format(value);
}

/**
 * Describe only selected model years, without implying that hidden years are present.
 * @param {ChartModel} model Normalized model with localized labels.
 * @param {number[]} visibleYears Selected series identifiers.
 * @returns {string} A single year, consecutive range, localized list, or no-data label.
 */
export function formatVisibleYears(model, visibleYears) {
    const years = model.years.filter((year) => visibleYears.includes(year));
    if (!years.length) return model.labels.noData;
    if (years.length === 1) return String(years[0]);
    if (years.every((year, index) => index === 0 || year === years[index - 1] + 1)) {
        return interpolate(model.labels.range, {first: years[0], last: years[years.length - 1]});
    }
    return new Intl.ListFormat(model.locale, {style: 'long', type: 'conjunction'}).format(years.map(String));
}

/**
 * Describe a selected point consistently for visible output and assistive tools.
 * @param {ChartModel} model Normalized model.
 * @param {number} year Selected calendar year.
 * @param {number} month One-based month.
 * @returns {string} Complete date/value description.
 */
export function describePoint(model, year, month) {
    const value = model.series.find((series) => series.year === year)?.values[month - 1] ?? null;
    const date = new Intl.DateTimeFormat(model.locale, {month: 'long', year: 'numeric', timeZone: 'UTC'})
        .format(new Date(Date.UTC(year, month - 1, 1)));
    return `${date}: ${formatValue(model, value)}${value === null ? '' : ` ${model.unit}`}`;
}
