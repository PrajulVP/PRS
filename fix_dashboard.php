<?php
$file = 'c:\\wamp64\\www\\prs\\resources\\views\\dashboard.blade.php';
$content = file_get_contents($file);

// Fix CSS
$cssToRemove1 = "                .data-table-container {\n                    background: #fff;\n                    border-radius: 12px;\n                    padding: 20px;\n                    box-shadow: 0 2px 12px rgba(0,0,0,0.04);\n                }";
$cssToRemove2 = "                .chart-container {\n                    background: #fff;\n                    border-radius: 12px;\n                    padding: 20px;\n                    box-shadow: 0 2px 12px rgba(0,0,0,0.04);\n                    margin-bottom: 30px;\n                }";

$content = str_replace($cssToRemove1, '', $content);
$content = str_replace($cssToRemove2, '', $content);

// Add SVG styling and adjust specific colors for feather icons
$content = str_replace('.med-widget-card i {', '.med-widget-card i, .med-widget-card svg {', $content);
$content = preg_replace('/(\.med-widget-card i\s*{\s*color:\s*#ffffff\s*!important;\s*})/', ".med-widget-card i, .med-widget-card svg {\n                    color: #ffffff !important;\n                }", $content);

// Replace containers with theme-aware cards
$content = str_replace('class="chart-container"', 'class="card p-4 mb-4"', $content);
$content = str_replace('class="data-table-container"', 'class="card p-4 mb-4"', $content);
$content = str_replace('class="data-table-container h-100"', 'class="card p-4 h-100 mb-4"', $content);
$content = str_replace('<thead class="bg-light">', '<thead class="thead-light">', $content);

// Chart fallback logic
$oldChartJS = <<<EOT
                        // Order Status Donut Chart (For specific roles)
                        if(document.querySelector("#orderStatusChart")) {
                            var statusOptions = {
                                series: [
                                    {{ \$retailerOrderStats['pending'] }},
                                    {{ \$retailerOrderStats['approved'] }},
                                    {{ \$retailerOrderStats['delivered'] }},
                                    {{ \$retailerOrderStats['cancelled'] }}
                                ],
                                labels: ['Pending', 'Approved', 'Delivered', 'Cancelled'],
                                chart: { type: 'donut', height: 320 },
                                colors: ['#f59e0b', '#3b82f6', '#10b981', '#ef4444'],
                                stroke: { width: 0 },
                                plotOptions: { pie: { donut: { size: '65%' } } },
                                dataLabels: { enabled: false }
                            };
                            new ApexCharts(document.querySelector("#orderStatusChart"), statusOptions).render();
                        }
EOT;

$newChartJS = <<<EOT
                        // Order Status Donut Chart (For specific roles)
                        if(document.querySelector("#orderStatusChart")) {
                            var orderTotal = {{ \$retailerOrderStats['total'] }};
                            var statusSeries = orderTotal > 0 ? [
                                    {{ \$retailerOrderStats['pending'] }},
                                    {{ \$retailerOrderStats['approved'] }},
                                    {{ \$retailerOrderStats['delivered'] }},
                                    {{ \$retailerOrderStats['cancelled'] }}
                                ] : [1];
                            var statusLabels = orderTotal > 0 ? ['Pending', 'Approved', 'Delivered', 'Cancelled'] : ['No Orders Yet'];
                            var statusColors = orderTotal > 0 ? ['#f59e0b', '#3b82f6', '#10b981', '#ef4444'] : ['#e2e8f0'];
                            
                            var statusOptions = {
                                series: statusSeries,
                                labels: statusLabels,
                                chart: { type: 'donut', height: 320 },
                                colors: statusColors,
                                stroke: { width: 0 },
                                plotOptions: { pie: { donut: { size: '65%' } } },
                                dataLabels: { enabled: false },
                                tooltip: { enabled: orderTotal > 0 }
                            };
                            new ApexCharts(document.querySelector("#orderStatusChart"), statusOptions).render();
                        }
EOT;

$content = str_replace($oldChartJS, $newChartJS, $content);

file_put_contents($file, $content);
echo "Modifications completed.";
