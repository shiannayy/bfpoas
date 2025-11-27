console.log('🔧 dynamic_charts.js loading...');

function ensureFetchData() {
    return new Promise((resolve) => {
        const checkFetchData = () => {
            if (typeof window.fetchData === 'function') {
                console.log('✅ fetchData is available');
                resolve();
            } else {
                console.log('🟡 Waiting for fetchData...');
                setTimeout(checkFetchData, 100);
            }
        };
        checkFetchData();
    });
}

class DynamicChartManager {
    constructor() {
        this.charts = new Map();
        this.initialized = false;
        
        // Ensure fetchData is available before allowing chart creation
        this.initPromise = ensureFetchData().then(() => {
            this.initialized = true;
            console.log('✅ DynamicChartManager fully initialized');
        });
    }

    async createChartInCanvas(canvasId, table, title = 'Data Overview', columnToUse = 'created_at', frequency = 'count') {
        // Wait for initialization before creating charts
        if (!this.initialized) {
            console.log('🟡 Waiting for DynamicChartManager initialization...');
            await this.initPromise;
        }

        try {
            console.log(`🟡 Creating chart: ${canvasId}, Table: ${table}`);

            // Check if canvas exists
            const canvas = document.getElementById(canvasId);
            if (!canvas) {
                throw new Error(`Canvas element with id '${canvasId}' not found`);
            }

            // Show loading state
            this.showLoadingState(canvasId);

            console.log(`🟡 Fetching data from table: ${table}`);
            
            // Fetch data from backend
            const response = await fetchData('../includes/_get_table_data.php', 'POST', { 
                table: table
            });

            console.log(`✅ Data fetched:`, response);

            if (!response || !response.data) {
                throw new Error('No data returned from server');
            }

            if (response.data.length === 0) {
                console.warn(`No data available for table: ${table}`);
                this.renderEmptyState(canvasId, title);
                return canvasId;
            }

            // Process data based on frequency
            let chartData;
            if (frequency === 'count') {
                chartData = this.processCountData(response.data, title);
            } else {
                chartData = this.processTimeSeriesData(response.data, columnToUse, frequency, title);
            }

            console.log(`✅ Chart data processed:`, chartData);

            // Render chart in existing canvas
            this.renderChartInCanvas(canvasId, chartData, frequency, title);

            console.log(`✅ Chart rendered successfully: ${canvasId}`);
            return canvasId;

        } catch (error) {
            console.error(`❌ Error creating chart in canvas ${canvasId}:`, error);
            this.renderErrorState(canvasId, error.message);
            throw error;
        }
    }

    processCountData(data, title) {
        return {
            type: 'count',
            value: data.length,
            title: title,
            data: data
        };
    }

    /**
     * Process data for time series charts
     */
    processTimeSeriesData(data, dateColumn, frequency, title) {
        const counts = {};
        
        data.forEach(item => {
            if (item[dateColumn]) {
                const date = new Date(item[dateColumn]);
                const key = this.getTimeframeKey(date, frequency);
                counts[key] = (counts[key] || 0) + 1;
            }
        });

        const series = this.generateTimeSeries(counts, frequency);
        
        return {
            type: 'timeseries',
            frequency: frequency,
            title: title,
            labels: series.labels,
            values: series.values
        };
    }

    /**
     * Generate timeframe key for grouping
     */
    getTimeframeKey(date, frequency) {
        switch (frequency) {
            case 'daily':
                return date.toISOString().split('T')[0]; // YYYY-MM-DD
                
            case 'weekly':
                const year = date.getFullYear();
                const week = this.getWeekNumber(date);
                return `${year}-W${week.toString().padStart(2, '0')}`;
                
            case 'monthly':
                return `${date.getFullYear()}-${(date.getMonth() + 1).toString().padStart(2, '0')}`;
                
            case 'yearly':
                return date.getFullYear().toString();
                
            default:
                return date.toISOString().split('T')[0];
        }
    }

    /**
     * Generate complete time series with filled gaps
     */
    generateTimeSeries(counts, frequency) {
        const timeframes = Object.keys(counts).sort();
        
        if (timeframes.length === 0) {
            return { labels: [], values: [] };
        }

        // Parse timeframe keys to dates
        const dates = timeframes.map(key => this.parseTimeframeKey(key, frequency));
        const minDate = new Date(Math.min(...dates));
        const maxDate = new Date(Math.max(...dates));

        // Extend range for better visualization
        const { startDate, endDate } = this.getDateRange(minDate, maxDate, frequency);

        // Generate complete series
        const labels = [];
        const values = [];
        let current = new Date(startDate);

        while (current <= endDate) {
            const key = this.getTimeframeKey(current, frequency);
            const label = this.formatTimeframeLabel(current, frequency);
            
            labels.push(label);
            values.push(counts[key] || 0);

            // Move to next timeframe
            this.incrementDate(current, frequency);
        }

        return { labels, values };
    }

    /**
     * Parse timeframe key back to date
     */
    parseTimeframeKey(key, frequency) {
        switch (frequency) {
            case 'daily':
                return new Date(key);
                
            case 'weekly':
                const [year, week] = key.replace('W', '').split('-').map(Number);
                return this.firstDateOfISOWeek(week, year);
                
            case 'monthly':
                const [y, m] = key.split('-').map(Number);
                return new Date(y, m - 1); // Month is 0-indexed in JavaScript
                
            case 'yearly':
                return new Date(parseInt(key), 0, 1);
                
            default:
                return new Date(key);
        }
    }

    /**
     * Get extended date range for chart
     */
    getDateRange(minDate, maxDate, frequency) {
        const startDate = new Date(minDate);
        const endDate = new Date(maxDate);

        switch (frequency) {
            case 'daily':
                startDate.setDate(startDate.getDate() - 1);
                endDate.setDate(endDate.getDate() + 1);
                break;
            case 'weekly':
                startDate.setDate(startDate.getDate() - 7);
                endDate.setDate(endDate.getDate() + 7);
                break;
            case 'monthly':
                startDate.setMonth(startDate.getMonth() - 1);
                endDate.setMonth(endDate.getMonth() + 1);
                break;
            case 'yearly':
                startDate.setFullYear(startDate.getFullYear() - 1);
                endDate.setFullYear(endDate.getFullYear() + 1);
                break;
        }

        return { startDate, endDate };
    }

    /**
     * Format timeframe label for display
     */
    formatTimeframeLabel(date, frequency) {
        switch (frequency) {
            case 'daily':
                return date.toLocaleDateString();
            case 'weekly':
                const week = this.getWeekNumber(date);
                return `W${week} ${date.getFullYear()}`;
            case 'monthly':
                return date.toLocaleString('default', { month: 'short', year: 'numeric' });
            case 'yearly':
                return date.getFullYear().toString();
            default:
                return date.toLocaleDateString();
        }
    }

    /**
     * Increment date based on frequency
     */
    incrementDate(date, frequency) {
        switch (frequency) {
            case 'daily':
                date.setDate(date.getDate() + 1);
                break;
            case 'weekly':
                date.setDate(date.getDate() + 7);
                break;
            case 'monthly':
                date.setMonth(date.getMonth() + 1);
                break;
            case 'yearly':
                date.setFullYear(date.getFullYear() + 1);
                break;
        }
    }

    showLoadingState(canvasId) {
        const canvas = document.getElementById(canvasId);
        const container = canvas.parentElement;
        
        // Store original canvas
        if (!canvas.dataset.originalHTML) {
            canvas.dataset.originalHTML = canvas.outerHTML;
        }
        
        // Show loading spinner
        container.innerHTML = `
            <div class="text-center py-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2 text-muted">Loading chart data...</p>
            </div>
        `;
    }

    /**
     * Render chart in existing canvas element
     */
    renderChartInCanvas(canvasId, chartData, frequency, title) {
        const canvas = document.getElementById(canvasId);
        if (!canvas) return;

        const container = canvas.parentElement;
        
        // Restore original canvas
        if (canvas.dataset.originalHTML) {
            container.innerHTML = canvas.dataset.originalHTML;
        }

        const ctx = document.getElementById(canvasId).getContext('2d');
        
        // Clear existing chart
        if (ctx.chartInstance) {
            ctx.chartInstance.destroy();
        }

        if (chartData.type === 'count') {
            this.renderCountDisplayInCanvas(ctx, chartData);
        } else {
            this.renderTimeSeriesChartInCanvas(ctx, chartData, frequency, title);
        }
    }

    /**
     * Render count display in existing canvas container
     */
    renderCountDisplayInCanvas(ctx, chartData) {
        const container = ctx.canvas.parentElement;
        container.innerHTML = `
            <div class="count-display text-center py-4">
                <h3 class="count-value text-primary">${chartData.value}</h3>
                <p class="count-title h5">${chartData.title}</p>
                <small class="text-muted">Total Records</small>
            </div>
        `;
    }

    /**
     * Render time series chart in existing canvas
     */
    renderTimeSeriesChartInCanvas(ctx, chartData, frequency, title) {
        const chartType = this.getChartType(frequency);
        
        console.log(`🟡 Rendering ${chartType} chart with ${chartData.labels.length} data points`);
        
        ctx.chartInstance = new Chart(ctx, {
            type: chartType,
            data: {
                labels: chartData.labels,
                datasets: [{
                    label: title,
                    data: chartData.values,
                    borderWidth: 2,
                    borderColor: '#00296b',
                    backgroundColor: this.getBackgroundColor(chartType, chartData.values),
                    fill: chartType === 'line', // FIXED: was 'pie', now 'line'
                    tension: 0.3
                }]
            },
            options: this.getChartOptions(chartType, title)
        });
    }

    /**
     * Determine chart type based on frequency
     */
    getChartType(frequency) {
        const typeMap = {
            'daily': 'line',
            'weekly': 'line', 
            'monthly': 'bar',
            'yearly': 'bar'
        };
        return typeMap[frequency] || 'bar';
    }

    /**
     * Get background color based on chart type
     */
    getBackgroundColor(chartType, values) {
        if (['bar', 'pie', 'doughnut'].includes(chartType)) {
            const maxVal = Math.max(...values);
            const minVal = Math.min(...values);
            
            return values.map(val => {
                const ratio = (val - minVal) / (maxVal - minVal || 1);
                return this.interpolateColor('#00296b', '#003f88', ratio);
            });
        }
        return 'rgba(0, 63, 136, 0.1)'; // FIXED: Use rgba for better transparency
    }

    getChartOptions(chartType, title) {
        const isPieLike = ['pie', 'doughnut'].includes(chartType);
        
        return {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                title: { 
                    display: true, 
                    text: title,
                    font: { size: 16 }
                },
                legend: {
                    display: isPieLike,
                    position: 'bottom'
                },
                tooltip: { 
                    mode: 'index', 
                    intersect: false 
                }
            },
            scales: isPieLike ? {} : {
                x: { 
                    title: { 
                        display: true, 
                        text: 'Time' 
                    },
                    ticks: {
                        maxTicksLimit: 10 // Limit number of labels to prevent overcrowding
                    }
                },
                y: { 
                    beginAtZero: true, 
                    title: { 
                        display: true, 
                        text: 'Count' 
                    },
                    ticks: {
                        stepSize: 1 // Ensure whole numbers for counts
                    }
                }
            },
            elements: {
                line: {
                    tension: 0.3 // Smooth lines for line charts
                }
            }
        };
    }

    /**
     * Render empty state
     */
    renderEmptyState(canvasId, title) {
        const container = document.getElementById(canvasId).parentElement;
        container.innerHTML = `
            <div class="text-center py-4">
                <div class="text-muted">
                    <i class="bi bi-inbox" style="font-size: 2rem;"></i>
                    <p class="mt-2">No data available for ${title}</p>
                </div>
            </div>
        `;
    }

    /**
     * Render error state
     */
    renderErrorState(canvasId, errorMessage) {
        const container = document.getElementById(canvasId).parentElement;
        container.innerHTML = `
            <div class="text-center py-4">
                <div class="text-danger">
                    <i class="bi bi-exclamation-triangle" style="font-size: 2rem;"></i>
                    <p class="mt-2">Failed to load chart</p>
                    <small class="text-muted">${errorMessage}</small>
                </div>
            </div>
        `;
    }

    // Helper methods
    getWeekNumber(date) {
        const d = new Date(Date.UTC(date.getFullYear(), date.getMonth(), date.getDate()));
        const dayNum = d.getUTCDay() || 7;
        d.setUTCDate(d.getUTCDate() + 4 - dayNum);
        const yearStart = new Date(Date.UTC(d.getUTCFullYear(), 0, 1));
        return Math.ceil((((d - yearStart) / 86400000) + 1) / 7);
    }

    firstDateOfISOWeek(week, year) {
        const simple = new Date(year, 0, 1 + (week - 1) * 7);
        const dow = simple.getDay();
        const ISOweekStart = simple;
        if (dow <= 4)
            ISOweekStart.setDate(simple.getDate() - simple.getDay() + 1);
        else
            ISOweekStart.setDate(simple.getDate() + 8 - simple.getDay());
        return ISOweekStart;
    }

    interpolateColor(color1, color2, factor) {
        const c1 = this.hexToRgb(color1);
        const c2 = this.hexToRgb(color2);
        const result = {
            r: Math.round(c1.r + factor * (c2.r - c1.r)),
            g: Math.round(c1.g + factor * (c2.g - c1.g)),
            b: Math.round(c1.b + factor * (c2.b - c1.b))
        };
        return `rgb(${result.r}, ${result.g}, ${result.b})`;
    }

    hexToRgb(hex) {
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
}

console.log('✅ dynamic_charts.js loaded successfully');