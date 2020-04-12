<?php

use KreeLabs\WSR\Admin\Dashboard;

use function KreeLabs\WSR\format_number;

?>

<div id="wsr-sales-dashboard" class="wrap content-wrapper-before gradient-45deg-indigo-purple">
    <h1 class="title"><?= translate('Sales Dashboard') ?></h1>
    <div id="card-stats">
        <div class="row ecommerce-stats">
            <div class="col s12 m3">
                <?php
                Dashboard::salesCard([
                    'color' => 'light-blue-cyan',
                    'icon' => 'schedule',
                    'total' => wc_price($totalEarningsAndOrdersToday['total_earnings']),
                    'count' => $totalEarningsAndOrdersToday['total_orders'],
                    'title' => 'Earnings today',
                    'count_title' => 'sales',
                ]);
                ?>
            </div>
            <div class="col s12 m3">
                <?php
                Dashboard::salesCard([
                    'color' => 'amber-amber',
                    'icon' => 'today',
                    'total' => wc_price($totalEarningsAndOrdersThisMonth['total_earnings']),
                    'count' => $totalEarningsAndOrdersThisMonth['total_orders'],
                    'title' => 'Earnings this month',
                    'count_title' => 'sales',
                ]);
                ?>
            </div>
            <div class="col s12 m3">
                <?php
                Dashboard::salesCard([
                    'color' => 'red-pink',
                    'icon' => 'date_range',
                    'total' => wc_price($totalEarningsAndOrdersLastMonth['total_earnings']),
                    'count' => $totalEarningsAndOrdersLastMonth['total_orders'],
                    'title' => 'Earnings last month',
                    'count_title' => 'sales',
                ]);
                ?>
            </div>
            <div class="col s12 m3">
                <?php
                Dashboard::salesCard([
                    'color' => 'green-teal',
                    'icon' => 'account_balance',
                    'total' => wc_price($totalEarningsAndOrders['total_earnings']),
                    'count' => $totalEarningsAndOrders['total_orders'],
                    'title' => 'Total earnings',
                    'count_title' => 'sales',
                ]);
                ?>
            </div>
        </div>
        <div class="row">
            <div class="col s12 m6 l3 card-width">
                <?php
                $thisMonth = $avgEarningsAndOrdersThisMonth['avg_earnings'];
                $lastMonth = $avgEarningsAndOrdersLastMonth['avg_earnings'];

                $changeRatio = $thisMonth * 100;
                if ($lastMonth > 0) {
                    $changeRatio = ($thisMonth - $lastMonth) / $lastMonth * 100;
                }

                Dashboard::statBox([
                    'icon' => 'attach_money',
                    'count_today' => wc_price($thisMonth),
                    'count_yesterday' => wc_price($lastMonth),
                    'title_today' => 'Average earnings this month',
                    'title_yesterday' => 'last month',
                    'comparision' => [
                        'color' => $changeRatio < 0 ? 'red-text' : 'green-text',
                        'icon' => $changeRatio < 0 ? 'arrow_drop_down' : 'arrow_drop_up',
                        'value' => sprintf('%.2f', $changeRatio) . '%',
                    ],
                ]);
                ?>
            </div>

            <div class="col s12 m6 l3 card-width">
                <?php
                $thisMonth = $avgEarningsAndOrdersThisMonth['avg_sales'];
                $lastMonth = $avgEarningsAndOrdersLastMonth['avg_sales'];

                $changeRatio = $thisMonth * 100;
                if ($lastMonth > 0) {
                    $changeRatio = ($thisMonth - $lastMonth) / $lastMonth * 100;
                }

                Dashboard::statBox([
                    'icon' => 'show_chart',
                    'count_today' => sprintf('%.2f', $thisMonth),
                    'count_yesterday' => sprintf('%.2f', $lastMonth),
                    'title_today' => 'Average sales this month',
                    'title_yesterday' => 'last month',
                    'comparision' => [
                        'color' => $changeRatio < 0 ? 'red-text' : 'green-text',
                        'icon' => $changeRatio < 0 ? 'arrow_drop_down' : 'arrow_drop_up',
                        'value' => sprintf('%.2f', $changeRatio) . '%',
                    ],
                ]);
                ?>
            </div>

            <div class="col s12 m6 l3 card-width">
                <?php
                $thisYear = $avgEarningsAndOrdersThisYear['avg_earnings'];
                $lastYear = $avgEarningsAndOrdersLastYear['avg_earnings'];

                $changeRatio = $thisYear * 100;
                if ($lastYear > 0) {
                    $changeRatio = ($thisYear - $lastYear) / $lastYear * 100;
                }

                Dashboard::statBox([
                    'icon' => 'favorite_border',
                    'count_today' => wc_price($thisYear),
                    'count_yesterday' => wc_price($lastYear),
                    'title_today' => 'Average earnings this year',
                    'title_yesterday' => 'last year',
                    'comparision' => [
                        'color' => $changeRatio < 0 ? 'red-text' : 'green-text',
                        'icon' => $changeRatio < 0 ? 'arrow_drop_down' : 'arrow_drop_up',
                        'value' => sprintf('%.2f', $changeRatio) . '%',
                    ],
                ]);
                ?>
            </div>

            <div class="col s12 m6 l3 card-width">
                <?php
                $thisYear = $avgEarningsAndOrdersThisYear['avg_sales'];
                $lastYear = $avgEarningsAndOrdersLastYear['avg_sales'];

                $changeRatio = $thisYear * 100;
                if ($lastYear > 0) {
                    $changeRatio = ($thisYear - $lastYear) / $lastYear * 100;
                }

                Dashboard::statBox([
                    'icon' => 'check',
                    'count_today' => sprintf('%.2f', $thisYear),
                    'count_yesterday' => sprintf('%.2f', $lastYear),
                    'title_today' => 'Average sales this year',
                    'title_yesterday' => 'last year',
                    'comparision' => [
                        'color' => $changeRatio < 0 ? 'red-text' : 'green-text',
                        'icon' => $changeRatio < 0 ? 'arrow_drop_down' : 'arrow_drop_up',
                        'value' => sprintf('%.2f', $changeRatio) . '%',
                    ],
                ]);
                ?>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col s12 m12 l12">
            <div class="col s12 m6 l6">
                <?php
                Dashboard::chart([
                    'meta' => [
                        'id' => 'category',
                        'animate' => 'animate fadeUp',
                    ],
                    'title' => 'Top 5 products',
                    'subtitle' => 'Top 5 products by earnings.',
                    'chart' => [
                        'type' => 'pie',
                        'labels' => $topProductsChartData['labels'],
                        'data' => [
                            [
                                'data' => $topProductsChartData['data'],
                                'backgroundColor' => $topProductsChartData['backgroundColors'],
                            ],
                        ],
                        'options' => [
                            'legend' => [
                                'display' => true,
                                'position' => 'left',
                            ],
                        ],
                    ],
                ]);
                ?>
            </div>
            <div class="col s12 m6 16">
                <?php
                Dashboard::chart([
                    'meta' => [
                        'id' => 'category',
                        'animate' => 'animate fadeUp',
                    ],
                    'title' => 'Top 10 categories',
                    'subtitle' => 'Total 10 categories by earnings.',
                    'chart' => [
                        'type' => 'bar',
                        'labels' => $topCategoriesChartData['labels'],
                        'data' => [
                            [
                                'label' => 'Total earnings',
                                'backgroundColor' => "rgb(97, 189, 249)",
                                'data' => $topCategoriesChartData['data']['total'],
                            ],
                            [
                                'label' => 'Total sales',
                                'data' => $topCategoriesChartData['data']['qty'],
                                'backgroundColor' => "rgb(36, 202, 187, 0.7)",
                                'borderColor' => "rgb(36, 202, 187)",
                            ],
                        ],
                        'options' => [
                            'scales' => [
                                'xAxes' => [['stacked' => true]],
                                'yAxes' => [['stacked' => true]],
                            ],
                            'tooltips' => [
                                'mode' => 'index',
                                'intersect' => false,
                            ],
                        ],
                    ],
                ]);
                ?>
            </div>
        </div>
    </div>
</div>
