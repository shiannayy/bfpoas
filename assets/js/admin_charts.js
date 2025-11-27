$(document).ready(function() {

    checkSession(function (user) {
       // For view_uncomplete_inspections charts
        if (document.getElementById('inspectionChartMonth') || document.getElementById('inspectionChartWeek')) {
            fetchData('../includes/_get_table_data.php', 'POST', { table: 'view_uncomplete_inspections' })
                .then(response => {
                    let grouped = groupInspectionsByMonth(response.data, 'In Progress');

                    // Only render if canvas exists
                    if (document.getElementById('inspectionChartMonth')) {
                        renderThresholdGraph(
                            'inspectionChartMonth',
                            grouped.values,
                            grouped.labels,
                            'In Progress Inspections per Month',
                            'doughnut'
                        );
                    }

                    grouped = groupInspectionsByWeek(response.data, 'In Progress');

                    if (document.getElementById('inspectionChartWeek')) {
                        renderThresholdGraph(
                            'inspectionChartWeek',
                            grouped.values,
                            grouped.labels,
                            'Scheduled Inspections per Week',
                            'bar'
                        );
                    }
                })
                .catch(error => {
                    console.warn("Error fetching data:", error);
                });
        }

        // For view_uncomplete_inspection_schedule charts
        if (document.getElementById('ScheduledInspectionByMonth') || document.getElementById('ScheduledInspectionByWeek')) {
            fetchData('../includes/_get_table_data.php', 'POST', { table: 'view_uncomplete_inspection_schedule' })
                .then(response => {
                    let grouped = groupInspectionsByMonth(response.data, 'Scheduled');

                    if (document.getElementById('ScheduledInspectionByMonth')) {
                        renderThresholdGraph(
                            'ScheduledInspectionByMonth',
                            grouped.values,
                            grouped.labels,
                            'Scheduled Inspections per Month',
                            'line'
                        );
                    }

                    grouped = groupInspectionsByWeek(response.data, 'Scheduled');

                    if (document.getElementById('ScheduledInspectionByWeek')) {
                        renderThresholdGraph(
                            'ScheduledInspectionByWeek',
                            grouped.values,
                            grouped.labels,
                            'Scheduled Inspections per Week',
                            'pie'
                        );
                    }
                })
                .catch(error => {
                    console.warn("Error fetching data:", error);
                });
        }
    });
});

function groupInspectionsByMonth(data, status = 'completed') {
    const counts = {};
    let item = data;
        
    
    // 1️⃣ Collect counts for completed inspections
    data.forEach(item => {
        if (item.status.toLowerCase() === status.toLowerCase() && item.created_at) {
            const date = new Date(item.created_at);
            const year = date.getFullYear();
            const month = date.getMonth(); // 0-11
            const key = `${year}-${month}`; // internal key format (e.g., 2025-9)

            counts[key] = (counts[key] || 0) + 1;
        }
    });

    // 2️⃣ Get all valid months and determine range
    const months = Object.keys(counts).map(k => {
        const [y, m] = k.split('-').map(Number);
        return new Date(y, m);
    });

    if (months.length === 0) {
        return { labels: [], values: [] };
    }

    const minDate = new Date(Math.min(...months));
    const maxDate = new Date(Math.max(...months));

    // 3️⃣ Add one month before the minimum
    const startDate = new Date(minDate);
    startDate.setMonth(startDate.getMonth() - 1);

    // 4️⃣ Generate full month range (start → end)
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



function groupInspectionsByWeek(data, status = 'completed') {
    const counts = {};

    // 1️⃣ Collect counts for completed inspections
    data.forEach(item => {
        if (item.status?.toLowerCase() === status.toLowerCase() && item.created_at) {
            const date = new Date(item.created_at);

            // Compute ISO week and year
            const year = date.getFullYear();
            const week = getWeekNumber(date);

            const key = `${year}-W${week}`;
            counts[key] = (counts[key] || 0) + 1;
        }
    });

    // 2️⃣ Get all weeks and determine range
    const weeks = Object.keys(counts).map(k => {
        const [y, w] = k.replace('W', '').split('-').map(Number);
        return { year: y, week: w };
    });

    if (weeks.length === 0) {
        return { labels: [], values: [] };
    }

    const min = weeks.reduce((a, b) => (a.year < b.year || (a.year === b.year && a.week < b.week)) ? a : b);
    const max = weeks.reduce((a, b) => (a.year > b.year || (a.year === b.year && a.week > b.week)) ? a : b);

    // 3️⃣ Add one week before the minimum
    let start = new Date(firstDateOfISOWeek(min.week, min.year));
    start.setDate(start.getDate() - 7);
    let end = new Date(firstDateOfISOWeek(max.week, max.year));

    // 4️⃣ Generate continuous weekly range
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

/** 🔹 Helper: Get ISO week number */
function getWeekNumber(date) {
    const d = new Date(Date.UTC(date.getFullYear(), date.getMonth(), date.getDate()));
    const dayNum = d.getUTCDay() || 7; // ISO week starts on Monday
    d.setUTCDate(d.getUTCDate() + 4 - dayNum);
    const yearStart = new Date(Date.UTC(d.getUTCFullYear(), 0, 1));
    return Math.ceil((((d - yearStart) / 86400000) + 1) / 7);
}

/** 🔹 Helper: Get the first day (Monday) of ISO week */
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


function renderThresholdGraph(canvasId, xData, yData, label = 'Threshold Measurement', type = 'line') {
    const ctx = document.getElementById(canvasId).getContext('2d');

    if (ctx.chartInstance) ctx.chartInstance.destroy();

    // 🔹 Use palette gradient from dark to light
    let backgroundColors;
    if (['bar', 'pie', 'doughnut'].includes(type)) {
        const maxVal = Math.max(...xData);
        const minVal = Math.min(...xData);

        backgroundColors = xData.map(val => {
            const ratio = (val - minVal) / (maxVal - minVal || 1); // normalize 0-1
            return interpolateColor('#00296b', '#003f88', ratio); // dark → light
        });
    } else {
        backgroundColors = type === 'line' ? '#003f88' : '#003f8855';
    }

    ctx.chartInstance = new Chart(ctx, {
        type: type,
        data: {
            labels: yData,
            datasets: [{
                label: label,
                data: xData,
                borderWidth: 1,
                borderColor: '#00296b',
                backgroundColor: backgroundColors,
                fill: type === 'line' ? false : true,
                tension: type === 'line' ? 0.3 : 0
            }]
        },
        options: {
            responsive: true,
            plugins: {
                title: {
                    display: true,
                    text: `${label} vs Time`
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const value = context.parsed !== undefined ? context.parsed : context.raw;
                            return `${label}: ${value}`;
                        },
                        title: function(context) {
                            return context[0].label || '';
                        }
                    }
                }
            },
            scales: (type === 'pie' || type === 'doughnut') ? {} : {
                x: { title: { display: true, text: 'Time' } },
                y: { beginAtZero: true }
            }
        }
    });
}

/** 🔹 Helper: interpolate between two hex colors */
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