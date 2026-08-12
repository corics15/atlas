document.addEventListener('DOMContentLoaded', () => {

  const salesTrendCanvas = document.getElementById('salesTrendChart');

  if (salesTrendCanvas && window.dashboardSalesTrend) {
    new Chart(salesTrendCanvas, {
      type: 'line',
      data: {
        labels: window.dashboardSalesTrend.map(
          item => item.month
        ),
        datasets: [{
          label: 'Sales',
          data: window.dashboardSalesTrend.map(
            item => Number(item.total_amount)
          ),
          fill: false,
          tension: 0.3,
          pointRadius: 4,
          pointHoverRadius: 6
        }]
      },

      options: {
        responsive: true,
        maintainAspectRatio: false,
        legend: {
          display: false
        },
        scales: {
          yAxes: [{
            ticks: {
              beginAtZero: true,
              callback: function (value) {
                return Number(value).toLocaleString(
                  'en-PH',
                  {
                    maximumFractionDigits: 0
                  }
                );
              }
            }
          }],
          xAxes: [{
            gridLines: {
              display: false
            }
          }]
        },

        tooltips: {
          callbacks: {
            label: function (tooltipItem, data) {
              const value =
                data.datasets[
                  tooltipItem.datasetIndex
                ].data[tooltipItem.index];

              return Number(value).toLocaleString(
                'en-PH',
                {
                  minimumFractionDigits: 2,
                  maximumFractionDigits: 2
                }
              );
            }
          }
        }
      }
    });
  }

});