import test from 'node:test';
import assert from 'node:assert/strict';
import {createChartModel, createInitialState, describePoint, normalizeValue, normalizeLocale} from '../src/charts/model.js';

/** Build a deterministic metric model from raw rows. @param {Object[]} rows Raw records. @returns {Object} Model. */
function model(rows) {
    return createChartModel({currentYear: 2026, locale: 'de_DE_formal', linechartDataset: rows}, 'visits');
}

test('normalization retains zero, null gaps, decimal values and last duplicate', () => {
    const chart = model([
        {year: '2026', month: '3', visits: '12'}, {year: '2024', month: '1', visits: '0'},
        {year: '2026', month: '3', visits: '15.5'}, {year: '2025', month: 2, visits: '12bad'},
        {year: '2026', month: 13, visits: '8'}, {year: '2023', month: 1, visits: '5'},
    ]);
    assert.deepEqual(chart.years, [2024, 2025, 2026]);
    assert.equal(chart.series[0].values[0], 0);
    assert.equal(chart.series[2].values[2], 15.5);
    assert.equal(chart.series[1].values[1], null);
    assert.equal(chart.series.flatMap((series) => series.values).length, 36);
    assert.deepEqual(createInitialState(chart), {visibleYears: [2024, 2025, 2026], selectedYear: 2026, selectedMonth: 3});
});

test('invalid numbers cannot become measured zeros', () => {
    for (const value of [null, undefined, '', ' ', true, false, '1abc', '-1', Infinity, NaN]) {
        assert.equal(normalizeValue(value), null);
    }
    assert.equal(normalizeValue('0'), 0);
});

test('missing payloads, invalid chart types and unsupported metrics', () => {
    const chart = createChartModel({currentYear: 2027, linechartDataset: 'undefined', displayType: 'unknown'}, 'hits');
    assert.deepEqual(chart.years, [2025, 2026, 2027]);
    assert.equal(chart.displayType, 'spline');
    assert.ok(chart.series.every((series) => series.values.every((value) => value === null)));
    assert.deepEqual(createInitialState(chart), {visibleYears: [2025, 2026, 2027], selectedYear: 2027, selectedMonth: 1});
    assert.throws(() => createChartModel({}, 'unknown'), RangeError);
});

test('latest measured zero can be selected and locale variants work', () => {
    const chart = model([{year: 2025, month: 12, visits: 100}, {year: 2026, month: 1, visits: 0}]);
    assert.equal(createInitialState(chart).selectedMonth, 1);
    assert.match(describePoint(chart, 2026, 1), /Januar 2026: 0/);
    assert.match(describePoint(chart, 2026, 2), /No data/);
    assert.equal(normalizeLocale('de_DE_formal'), 'de-DE');
});
