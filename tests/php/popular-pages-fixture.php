<?php
/** Renders real ranking markup for the isolated browser fixture; no WordPress database is used. */
namespace RRZE\Statistik;

define('ABSPATH', __DIR__ . '/');

/** @param string $text Source message. @param string $domain Text domain. @return string Source message. */
function __($text, $domain) { return $text; }
/** @param string $text Source message. @param string $domain Text domain. @return string Escaped message. */
function esc_html__($text, $domain) { return esc_html($text); }
/** @param mixed $text Plain text. @return string Escaped text. */
function esc_html($text) { return htmlspecialchars((string) $text, ENT_QUOTES, 'UTF-8'); }
/** @param string $url Resource URL. @param array $protocols Allowed schemes. @return string Safe fixture URL. */
function esc_url($url, $protocols = ['http', 'https'])
{
    $scheme = parse_url($url, PHP_URL_SCHEME);
    return $scheme && !in_array(strtolower($scheme), $protocols, true) ? '' : esc_html($url);
}
/** @param int $value Request count or rank. @return string Localized fixture number. */
function number_format_i18n($value) { return number_format($value, 0, '.', ','); }
/** @param string $key Cache name. @return array Resource groups with long paths, zero and escaped text. */
function get_transient($key)
{
    return [
        [[10013, '/'], [1240, '/study/courses/application-and-admission/'],
            [1183, '/research/projects/'], [767, '/news/2026/a-long-resource-path-that-wraps-in-a-narrow-dashboard-column/'],
            [422, '/about/contact/'], [0, '/search/?topic=research&year=2026']],
        [[1197, '/files/2026/11/campus-overview.jpg'], [707, '/files/2026/11/' . str_repeat('longfilename', 12) . '.png']],
        [[495, '/files/2026/11/annual-report.pdf']],
    ];
}

require __DIR__ . '/../../includes/Analytics.php';
echo Analytics::getUrlDatasetTable();
