<?php

namespace KreeLabs\WSR;

/**
 * Displays success message with WordPress default theme.
 *
 * @param string $message
 *
 * @return void
 */
function success_notice($message)
{
    echo "<div class='updated wsr-updated'>";
    echo "<p>" . translate($message) . "</p>";
    echo "</div>";
}

/**
 * Displays error message with WordPress default theme.
 *
 * @param string $message
 *
 * @return void
 */
function error_notice($message)
{
    echo "<div class='error wsr-error'>";
    echo "<p>" . translate($message) . "</p>";
    echo "</div>";
}

/**
 * Localize text strings.
 *
 * @param string $string
 *
 * @return string
 */
function translate($string)
{
    return __($string, WSR_TEXT_DOMAIN);
}

/**
 * Format number in 'k' notation up to billions.
 *
 * @param int $num
 *
 * @return string
 */
function format_number($num)
{
    $num = floatval($num);

    if ($num < 1000) {
        return sprintf('%d', $num);
    }

    switch ($num) {
        case $num >= 1000000000:
            $format = ceil($num % 1000000000) < 100 ? '%d%s' : '%.1f%s';

            return sprintf($format, $num / 1000000000, 'b');

        case $num >= 1000000:
            $format = ceil($num % 1000000) < 100 ? '%d%s' : '%.1f%s';

            return sprintf($format, $num / 1000000, 'm');

        case $num >= 1000:
            $format = ceil($num % 1000) < 100 ? '%d%s' : '%.1f%s';

            return sprintf($format, $num / 1000, 'k');
    }

    return 0;
}

/**
 * Get date taking timezone in consideration.
 *
 * @param string $date
 *
 * @return \DateTime
 */
function get_date_with_timezone($date)
{
    $tz = get_option('timezone_string', 'UTC');

    if (empty($tz)) {
        $tz = 'UTC';
    }

    return new \DateTime($date, new \DateTimeZone($tz));
}

/**
 * Format date in easily readable format.
 *
 * @param string $date
 * @param string $format
 *
 * @return string
 */
function format_date($date, $format = 'M j, Y g:i:s A')
{
    if (empty($date)) {
        return '';
    }

    $tz = get_option('timezone_string', 'UTC');
    if (empty($tz)) {
        $tz = 'UTC';
    }

    $dt    = new \WC_DateTime($date);
    $today = new \DateTime('now', new \DateTimeZone($tz));

    if ($dt->format('Y-m-d') === $today->format('Y-m-d')) {
        return translate('Today') . ', ' . $dt->format('g:i:s A');
    }

    return $dt->format($format);
}

/**
 * Get country full name from country code.
 *
 * @param string $countryCode
 *
 * @return string
 */
function get_country_name($countryCode)
{
    $country = $countryCode;

    if (function_exists('locale_get_display_region')) {
        $country = locale_get_display_region('-' . $countryCode, 'en');
        $country = ! empty($country) ? $country . ' (' . $countryCode . ')' : $countryCode;
    }

    return $country;
}

/**
 * Get display name for the order.
 *
 * @param int $postID
 *
 * @return string
 */
function get_display_name($postID)
{
    $name = trim(sprintf(
        '%s %s',
        get_post_meta($postID, '_billing_first_name', true),
        get_post_meta($postID, '_billing_last_name', true)
    ));

    if (empty($name)) {
        $buyer = get_post_meta($postID, '_customer_user', true);
        $name  = get_the_author_meta('display_name', $buyer);
    }

    return empty($name) ? 'Unknown' : $name;
}
