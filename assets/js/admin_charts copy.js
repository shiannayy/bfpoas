const roleCharts = {
    "Admin_Assistant": [
        { 
            id: 'statusDistributionChart1', 
            table: 'view_inspection_schedule',
            type: 'doughnut', 
            title: 'Scheduled Inspections by Progress',
            group: 'progress',
            renderer: 'status',
            colSize: 'col-6'
        },
        { 
            id: 'statusDistributionChart2', 
            table: 'view_inspection_schedule',
            type: 'pie', 
            title: 'Scheduled Inspections by Status',
            group: 'status',
            renderer: 'status',
            colSize: 'col-6'
        },
        
        { 
            id: 'ScheduledInspectionByWeek', 
            table: 'view_uncomplete_inspection_schedule', 
            filter: 'Scheduled',
            group: 'week', 
            type: 'doughnut', 
            title: 'Scheduled Inspections per Week',
            renderer: 'status',
            colSize: 'col-6'
        },
        { 
            id: 'inspectionChartMonth', 
            table: 'view_uncomplete_inspections', 
            filter: 'In Progress',
            group: 'month', 
            type: 'line', 
            title: 'In Progress Inspections per Month',
            renderer: 'progress',
            colSize: 'col-6'
        },
        { 
            id: 'inspectionChartWeek', 
            table: 'view_uncomplete_inspections', 
            filter: 'In Progress',
            group: 'week', 
            type: 'bar', 
            title: 'In Progress Inspections per Week',
            renderer: 'progress',
            colSize: 'col-12'
        },
        { 
            id: 'ScheduledInspectionByMonth', 
            table: 'view_uncomplete_inspection_schedule', 
            filter: 'Scheduled',
            group: 'month', 
            type: 'line', 
            title: 'Scheduled Inspections per Month',
            renderer: 'progress',
            colSize: 'col-12'
        }
    ],
    "Recommending Approver": [
        { 
            id: 'ScheduledInspectionByMonth', 
            table: 'view_uncomplete_inspection_schedule', 
            filter: 'Scheduled',
            group: 'month', 
            type: 'line', 
            title: 'Scheduled Inspections per Month',
            renderer: 'progress',
            colSize: 'col-12'
        },
        { 
            id: 'ScheduledInspectionByWeek', 
            table: 'view_uncomplete_inspection_schedule', 
            filter: 'Scheduled',
            group: 'week', 
            type: 'pie', 
            title: 'Scheduled Inspections per Week',
            renderer: 'status',
            colSize: 'col-6'
        }
    ]
};

$(document).ready(function() {
    checkSession(function (user) {
        const userRoleLabel = getRoleLabel(user.role, user.subrole);

        if (!roleCharts[userRoleLabel]) {
            showNoChartsMessage();
            return;
        }

        createChartContainers(roleCharts[userRoleLabel]);
        
        roleCharts[userRoleLabel].forEach(chart => {
            createChart(
                chart.id,
                chart.table,
                chart.filter,
                chart.group,
                chart.type,
                chart.title,
                chart.renderer
            );
        });
    });
});

function createChartContainers(chartConfigs) {
    const chartsContainer = $('#chartsContainer');
    const row = $('<div>').addClass('row g-3');
    
    chartConfigs.forEach(config => {
        const colSize = config.colSize || 'col-6';
        
        const chartHtml = `
            <div class="${colSize} mb-3">
                <div class="card border-0 shadow shadow-lg h-100">
                    <div class="card-body chart-body">
                        <canvas id="${config.id}">
                            <div class="spinner-border"><span class="visually-hidden">Loading...</span></div>
                        </canvas>
                    </div>
                </div>
            </div>
        `;
        
        row.append(chartHtml);
    });
    
    chartsContainer.empty().append(row);
}

function showNoChartsMessage() {
    const chartsContainer = $('#chartsContainer');
    chartsContainer.html(`
        <div class="col-12 mb-3">
            <div class="alert alert-info text-center">
                <h4>No charts available for your role</h4>
                <p class="mb-0">Chart is not yet available for your role.</p>
            </div>
        </div>
    `);
}

function createChart(elementId, tableName, filter, groupBy, chartType, chartTitle, rendererType = 'progress') {
    const canvas = document.getElementById(elementId);
    if (!canvas) {
        console.warn(`Canvas element #${elementId} not found`);
        return;
    }

    showChartLoading(canvas);

    fetchData('../includes/_get_table_data.php', 'POST', { table: tableName })
        .then(response => {
            console.log(`📊 Raw API response for ${elementId}:`, response);
            
            let chartData;

            if (groupBy === 'status' || groupBy === 'progress') {
                chartData = groupByColumn(response.data, groupBy, filter);
            } else if (groupBy === 'month') {
                chartData = groupByTime(response.data, 'month', filter);
            } else if (groupBy === 'week') {
                chartData = groupByTime(response.data, 'week', filter);
            } else if (groupBy === 'day') {
                chartData = groupByTime(response.data, 'day', filter);
            } else {
                console.warn(`Unknown grouping type: ${groupBy}`);
                hideChartLoading(canvas);
                return;
            }

            console.log(`📈 Processed chart data for ${elementId}:`, chartData);

            if (rendererType === 'status') {
                renderStatusPieChart(
                    elementId,
                    chartData.labels,
                    chartData.values,
                    chartTitle,
                    chartType,
                    chartData.colors
                );
            } else {
                renderProgressChart(
                    elementId,
                    chartData.values,
                    chartData.labels,
                    chartTitle,
                    chartType
                );
            }
            
            hideChartLoading(canvas);
        })
        .catch(error => {
            console.warn(`Error creating chart ${elementId}:`, error);
            showChartError(canvas, error.message);
        });
}

function showChartLoading(canvas) {
    const container = $(canvas).parent();
    container.addClass('position-relative');
    
    const loadingOverlay = $('<div>')
        .addClass('chart-loading-overlay position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center bg-light bg-opacity-75')
        .html('<div class="spinner-border text-primary"><span class="visually-hidden">Loading...</span></div>');
    
    container.append(loadingOverlay);
}

function hideChartLoading(canvas) {
    $(canvas).siblings('.chart-loading-overlay').remove();
}

function showChartError(canvas, errorMessage) {
    const container = $(canvas).parent();
    container.addClass('position-relative');
    
    const errorOverlay = $('<div>')
        .addClass('chart-error-overlay position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center bg-light bg-opacity-90')
        .html(`
            <div class="text-center text-danger">
                <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                <p class="mb-0 small">Failed to load chart<br><small>${errorMessage}</small></p>
            </div>
        `);
    
    container.append(errorOverlay);
    hideChartLoading(canvas);
}

function showNoDataMessage(canvasId) {
    const canvas = document.getElementById(canvasId);
    if (!canvas) return;
    
    const container = $(canvas).parent();
    container.addClass('position-relative');
    
    const noDataOverlay = $('<div>')
        .addClass('chart-nodata-overlay position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center bg-light bg-opacity-90')
        .html(`
            <div class="text-center text-muted">
                <i class="fas fa-chart-line fa-2x mb-2"></i>
                <p class="mb-0 small">No data available<br><small>for the selected criteria</small></p>
            </div>
        `);
    
    container.append(noDataOverlay);
}

function renderProgressChart(canvasId, data, labels, title, chartType = 'line') {
    console.log(`🔍 renderProgressChart called:`, { canvasId, data, labels, title, chartType });

    const canvas = document.getElementById(canvasId);
    if (!canvas) {
        console.error(`❌ Canvas element #${canvasId} not found`);
        return;
    }

    const ctx = canvas.getContext('2d');
    if (!ctx) {
        console.error(`❌ Could not get 2D context for #${canvasId}`);
        return;
    }

    if (ctx.chartInstance) {
        ctx.chartInstance.destroy();
    }

    // Validate and clean data
    if (!Array.isArray(data)) {
        console.error(`❌ Data is not an array:`, data);
        data = [];
    }

    if (!Array.isArray(labels)) {
        console.error(`❌ Labels is not an array:`, labels);
        labels = [];
    }

    const cleanData = data.map(item => {
        if (typeof item === 'object' && item !== null) {
            console.warn(`⚠️ Data item is object, converting to number:`, item);
            return Number(item) || 0;
        }
        return Number(item) || 0;
    });

    const cleanLabels = labels.map(label => {
        if (typeof label === 'object' && label !== null) {
            console.warn(`⚠️ Label is object, converting to string:`, label);
            return String(label);
        }
        return String(label);
    });

    console.log(`✅ Cleaned data:`, cleanData);
    console.log(`✅ Cleaned labels:`, cleanLabels);

    if (cleanData.length === 0 || cleanLabels.length === 0) {
        console.warn(`⚠️ No data available for chart ${canvasId}`);
        showNoDataMessage(canvasId);
        return;
    }

    const finalData = cleanData.slice(0, Math.min(cleanData.length, cleanLabels.length));
    const finalLabels = cleanLabels.slice(0, Math.min(cleanData.length, cleanLabels.length));

    let backgroundColors;
    if (chartType === 'bar') {
        const maxVal = Math.max(...finalData);
        const minVal = Math.min(...finalData);
        backgroundColors = finalData.map(val => {
            const ratio = (val - minVal) / (maxVal - minVal || 1);
            return interpolateColor('#00296b', '#003f88', ratio);
        });
    } else {
        backgroundColors = '#003f88';
    }

    try {
        ctx.chartInstance = new Chart(ctx, {
            type: chartType,
            data: {
                labels: finalLabels,
                datasets: [{
                    label: title,
                    data: finalData,
                    borderWidth: chartType === 'line' ? 2 : 1,
                    borderColor: '#00296b',
                    backgroundColor: backgroundColors,
                    fill: chartType === 'line',
                    tension: chartType === 'line' ? 0.3 : 0,
                    pointBackgroundColor: chartType === 'line' ? '#00296b' : undefined,
                    pointBorderColor: chartType === 'line' ? '#ffffff' : undefined,
                    pointBorderWidth: chartType === 'line' ? 2 : undefined
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: {
                        display: true,
                        text: title,
                        font: { size: 16 }
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        callbacks: {
                            label: function(context) {
                                const value = context.parsed.y;
                                return `${context.dataset.label}: ${value}`;
                            },
                            title: function(context) {
                                return String(context[0].label);
                            }
                        }
                    },
                    legend: {
                        display: false
                    }
                },
                scales: {
                    x: { 
                        title: { 
                            display: true, 
                            text: getTimeframeText(finalLabels) 
                        },
                        ticks: {
                            callback: function(value, index) {
                                const label = this.getLabelForValue(value);
                                return typeof label === 'string' ? label : String(label);
                            }
                        }
                    },
                    y: { 
                        beginAtZero: true,
                        title: { 
                            display: true, 
                            text: 'Number of Inspections' 
                        },
                        ticks: {
                            callback: function(value) {
                                return Number.isInteger(value) ? value : '';
                            }
                        }
                    }
                },
                interaction: {
                    intersect: false,
                    mode: 'nearest'
                }
            }
        });

        console.log(`✅ Chart ${canvasId} rendered successfully`);
        
    } catch (error) {
        console.error(`❌ Error rendering chart ${canvasId}:`, error);
        showChartError(canvas, `Chart error: ${error.message}`);
    }
}

function renderStatusPieChart(canvasId, labels, data, title, chartType = 'pie', customColors = null) {
    const canvas = document.getElementById(canvasId);
    if (!canvas) {
        console.error(`❌ Canvas element #${canvasId} not found`);
        return;
    }

    const ctx = canvas.getContext('2d');
    if (!ctx) {
        console.error(`❌ Could not get 2D context for #${canvasId}`);
        return;
    }

    if (ctx.chartInstance) {
        ctx.chartInstance.destroy();
    }

    // Validate and clean data
    const cleanData = Array.isArray(data) ? data.map(item => Number(item) || 0) : [];
    const cleanLabels = Array.isArray(labels) ? labels.map(label => String(label)) : [];

    if (cleanData.length === 0 || cleanLabels.length === 0) {
        console.warn(`⚠️ No data available for chart ${canvasId}`);
        showNoDataMessage(canvasId);
        return;
    }

    const total = cleanData.reduce((a, b) => a + b, 0);
    const colors = customColors || generateColors(cleanLabels, 'status');

    try {
        ctx.chartInstance = new Chart(ctx, {
            type: chartType,
            data: {
                labels: cleanLabels,
                datasets: [{
                    data: cleanData,
                    backgroundColor: colors,
                    borderColor: '#ffffff',
                    borderWidth: 2,
                    hoverOffset: 15
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: {
                        display: true,
                        text: title,
                        font: { size: 16 }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const value = context.parsed;
                                const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : '0';
                                return `${context.label}: ${value} (${percentage}%)`;
                            }
                        }
                    },
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 20,
                            usePointStyle: true
                        }
                    }
                },
                cutout: chartType === 'doughnut' ? '50%' : 0
            }
        });

        console.log(`✅ Status chart ${canvasId} rendered successfully`);
        
    } catch (error) {
        console.error(`❌ Error rendering status chart ${canvasId}:`, error);
        showChartError(canvas, `Chart error: ${error.message}`);
    }
}

function groupByColumn(data, columnName, filter = '') {
    const counts = {};
    
    const filteredData = data.filter(item => {
        if (!filter) return true;
        return item.status === filter || item.progress === filter;
    });
    
    filteredData.forEach(item => {
        const value = item[columnName] || 'Unknown';
        counts[value] = (counts[value] || 0) + 1;
    });

    const labels = Object.keys(counts);
    const values = Object.values(counts);
    
    return {
        labels: labels,
        values: values,
        colors: generateColors(labels, columnName)
    };
}

function groupByTime(data, timeUnit, filter = '') {
    const counts = {};
    
    const filteredData = data.filter(item => {
        if (!filter) return true;
        return item.status === filter;
    });

    filteredData.forEach(item => {
        if (!item.created_at) return;
        
        const date = new Date(item.created_at);
        let key;

        switch (timeUnit) {
            case 'day':
                key = date.toISOString().split('T')[0];
                break;
            case 'week':
                const year = date.getFullYear();
                const week = getWeekNumber(date);
                key = `${year}-W${week}`;
                break;
            case 'month':
                const month = date.getMonth();
                const yearMonth = date.getFullYear();
                key = `${yearMonth}-${month}`;
                break;
            default:
                return;
        }

        counts[key] = (counts[key] || 0) + 1;
    });

    let labels = [], values = [];

    if (timeUnit === 'day') {
        const sorted = Object.entries(counts).sort(([a], [b]) => new Date(a) - new Date(b));
        labels = sorted.map(([date]) => formatDate(new Date(date), 'day'));
        values = sorted.map(([, count]) => count);
    } else if (timeUnit === 'week') {
        const result = generateWeeklyRange(counts);
        labels = result.labels;
        values = result.values;
    } else if (timeUnit === 'month') {
        const result = generateMonthlyRange(counts);
        labels = result.labels;
        values = result.values;
    }

    return { labels, values };
}

function generateColors(labels, columnType) {
    const colorMaps = {
        status: {
            'Completed': '#E4985',
            'In Progress': '#168BB0',
            'Scheduled': '#55CC00',
            'Pending': '#ffa00',
            'Cancelled': '#881F2B',
            'Approved': '#006d2c',
            'Rejected': '#FA4e79',
            'Unknown': '#8c5c47'
        },
        progress: {
            'Client Acknowledged': '#6c757d'
           ,'Inspector Acknowledged' : '#5c7aF5'
           , 'Recommended' : '#f55c7a'
           , 'Approved' : '#55d6c2'
           , 'Pending' : '#ff8c00'
        }
    };

    const colorMap = colorMaps[columnType] || {};
    return labels.map(label => colorMap[label] || getRandomColor());
}

function getRandomColor() {
    const colors = [
        '#3366cc', '#dc3912', '#ff9900', '#109618', '#990099', '#0099c6',
        '#dd4477', '#66aa00', '#b82e2e', '#316395', '#994499', '#22aa99',
        '#aaaa11', '#6633cc', '#e67300', '#8b0707', '#651067', '#329262'
    ];
    return colors[Math.floor(Math.random() * colors.length)];
}

function generateMonthlyRange(counts) {
    const months = Object.keys(counts).map(k => {
        const [y, m] = k.split('-').map(Number);
        return new Date(y, m);
    });

    if (months.length === 0) {
        return { labels: [], values: [] };
    }

    const minDate = new Date(Math.min(...months));
    const maxDate = new Date(Math.max(...months));

    const startDate = new Date(minDate);
    startDate.setMonth(startDate.getMonth() - 1);

    const labels = [];
    const values = [];

    const current = new Date(startDate);
    while (current <= maxDate) {
        const label = current.toLocaleString('default', { month: 'short', year: 'numeric' });
        const key = `${current.getFullYear()}-${current.getMonth()}`;
        labels.push(label);
        values.push(counts[key] || 0);
        current.setMonth(current.getMonth() + 1);
    }

    return { labels, values };
}

function generateWeeklyRange(counts) {
    const weeks = Object.keys(counts).map(k => {
        const [y, w] = k.replace('W', '').split('-').map(Number);
        return { year: y, week: w };
    });

    if (weeks.length === 0) {
        return { labels: [], values: [] };
    }

    const min = weeks.reduce((a, b) => (a.year < b.year || (a.year === b.year && a.week < b.week)) ? a : b);
    const max = weeks.reduce((a, b) => (a.year > b.year || (a.year === b.year && a.week > b.week)) ? a : b);

    let start = new Date(firstDateOfISOWeek(min.week, min.year));
    start.setDate(start.getDate() - 7);
    let end = new Date(firstDateOfISOWeek(max.week, max.year));

    const labels = [];
    const values = [];

    let current = new Date(start);
    while (current <= end) {
        const year = current.getFullYear();
        const week = getWeekNumber(current);
        const key = `${year}-W${week}`;
        const label = `W${week} ${year}`;
        labels.push(label);
        values.push(counts[key] || 0);
        current.setDate(current.getDate() + 7);
    }

    return { labels, values };
}

function getWeekNumber(date) {
    const d = new Date(Date.UTC(date.getFullYear(), date.getMonth(), date.getDate()));
    const dayNum = d.getUTCDay() || 7;
    d.setUTCDate(d.getUTCDate() + 4 - dayNum);
    const yearStart = new Date(Date.UTC(d.getUTCFullYear(), 0, 1));
    return Math.ceil((((d - yearStart) / 86400000) + 1) / 7);
}

function firstDateOfISOWeek(week, year) {
    const simple = new Date(year, 0, 1 + (week - 1) * 7);
    const dow = simple.getDay();
    const ISOweekStart = simple;
    if (dow <= 4)
        ISOweekStart.setDate(simple.getDate() - simple.getDay() + 1);
    else
        ISOweekStart.setDate(simple.getDate() + 8 - simple.getDay());
    return ISOweekStart;
}

function formatDate(date, format) {
    switch (format) {
        case 'day':
            return date.toLocaleDateString();
        case 'month':
            return date.toLocaleString('default', { month: 'short', year: 'numeric' });
        case 'week':
            const week = getWeekNumber(date);
            return `W${week} ${date.getFullYear()}`;
        default:
            return date.toLocaleDateString();
    }
}

function getTimeframeText(labels) {
    if (labels.length === 0) return 'Time Period';
    
    const firstLabel = labels[0] || '';
    
    if (firstLabel.includes('W') && /W\d+/.test(firstLabel)) return 'Weeks';
    if (firstLabel.match(/\d{4}-\d{2}-\d{2}/)) return 'Days';
    if (firstLabel.match(/(Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)/)) return 'Months';
    
    return 'Categories';
}

function interpolateColor(color1, color2, factor) {
    const c1 = hexToRgb(color1);
    const c2 = hexToRgb(color2);
    const result = {
        r: Math.round(c1.r + factor * (c2.r - c1.r)),
        g: Math.round(c1.g + factor * (c2.g - c1.g)),
        b: Math.round(c1.b + factor * (c2.b - c1.b))
    };
    return `rgb(${result.r}, ${result.g}, ${result.b})`;
}

function hexToRgb(hex) {
    hex = hex.replace('#', '');
    if (hex.length === 3) {
        hex = hex.split('').map(h => h + h).join('');
    }
    return {
        r: parseInt(hex.substring(0, 2), 16),
        g: parseInt(hex.substring(2, 4), 16),
        b: parseInt(hex.substring(4, 6), 16)
    };
}

// CSS for chart overlays (add this to your styles)
const chartStyles = `
.chart-loading-overlay,
.chart-error-overlay,
.chart-nodata-overlay {
    z-index: 10;
    border-radius: 0.375rem;
}
.chart-body {
    position: relative;
    min-height: 300px;
}
.chart-body canvas {
    width: 100% !important;
    height: 100% !important;
}
`;

// Inject styles
if (!$('#chart-styles').length) {
    $('head').append(`<style id="chart-styles">${chartStyles}</style>`);
}