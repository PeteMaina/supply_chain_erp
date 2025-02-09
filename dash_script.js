// Donut Chart: Product Value by Category
const categoryCtx = document.getElementById('categoryChart').getContext('2d');
new Chart(categoryCtx, {
  type: 'doughnut',
  data: {
    labels: ['Engine Parts', 'Tires', 'Accessories'],
    datasets: [{
      data: [40, 30, 30], // Placeholder percentages
      backgroundColor: ['#4BC0C0', '#FF9F40', '#9966FF']
    }]
  },
  options: {
    responsive: true,
    plugins: {
      legend: {
        display: true,
        position: 'bottom'
      }
    }
  }
});


// Data for Top-Selling Products
const topSellingProductsData = {
  labels: ['Product A', 'Product B', 'Product C', 'Product D', 'Product E'],
  datasets: [{
      label: 'Units Sold',
      data: [120, 90, 70, 50, 30],
      backgroundColor: [
          'rgba(255, 99, 132, 0.7)',
          'rgba(54, 162, 235, 0.7)',
          'rgba(255, 206, 86, 0.7)',
          'rgba(75, 192, 192, 0.7)',
          'rgba(153, 102, 255, 0.7)'
      ],
      borderWidth: 1
  }]
};

// Config for Top-Selling Products Chart
const topSellingProductsConfig = {
  type: 'bar',
  data: topSellingProductsData,
  options: {
      responsive: true,
      plugins: {
          legend: {
              display: false,
          },
      },
      scales: {
          y: {
              beginAtZero: true
          }
      }
  }
};

// Render the Chart
const topSellingProductsChart = new Chart(
  document.getElementById('topSellingProductsChart'),
  topSellingProductsConfig
);

